<?php
/**
 * controllers/LaporanController.php
 *
 * Khusus Owner — semua akses laporan dan export:
 *   index      — halaman rekap utama + grafik + filter waktu
 *   detail     — tabel detail transaksi dengan filter + pagination
 *   exportPdf  — generate & stream laporan PDF (tanpa library eksternal)
 *   exportExcel— generate & stream file Excel (.xls HTML-based)
 *   exportCsv  — generate & stream file CSV
 */

require_once __DIR__ . '/../models/Transaksi.php';
require_once __DIR__ . '/../models/Area.php';
require_once __DIR__ . '/../helpers/functions.php';

class LaporanController
{
    private Transaksi $transaksiModel;
    private Area      $areaModel;

    public function __construct()
    {
        requireLogin();
        requireRole(ROLE_OWNER);
        $this->transaksiModel = new Transaksi();
        $this->areaModel      = new Area();
    }

    // ──────────────────────────────────────────────────────────
    //  Helper — ambil & validasi filter tanggal dari request
    // ──────────────────────────────────────────────────────────
    private function getFilter(): array
    {
        $preset = sanitize($_GET['preset'] ?? '');

        // Preset pintas
        $presets = [
            'hari_ini'    => [date('Y-m-d'),                         date('Y-m-d')],
            'minggu_ini'  => [date('Y-m-d', strtotime('monday this week')), date('Y-m-d')],
            'bulan_ini'   => [date('Y-m-01'),                        date('Y-m-d')],
            'bulan_lalu'  => [date('Y-m-01', strtotime('first day of last month')),
                              date('Y-m-t',  strtotime('last month'))],
            '30_hari'     => [date('Y-m-d', strtotime('-29 days')),  date('Y-m-d')],
            '90_hari'     => [date('Y-m-d', strtotime('-89 days')),  date('Y-m-d')],
        ];

        if ($preset && isset($presets[$preset])) {
            [$dari, $sampai] = $presets[$preset];
        } else {
            $dari   = sanitize($_GET['dari']   ?? date('Y-m-01'));
            $sampai = sanitize($_GET['sampai'] ?? date('Y-m-d'));
        }

        // Pastikan dari <= sampai
        if ($dari > $sampai) [$dari, $sampai] = [$sampai, $dari];

        return [
            'dari'   => $dari,
            'sampai' => $sampai,
            'preset' => $preset,
        ];
    }

    // ──────────────────────────────────────────────────────────
    //  INDEX — Halaman rekap utama
    // ──────────────────────────────────────────────────────────
    public function index(): void
    {
        $pageTitle = 'Rekap Laporan';
        $filter    = $this->getFilter();
        $dari      = $filter['dari'];
        $sampai    = $filter['sampai'];

        // Data rekap periode terpilih
        $rekap = $this->transaksiModel->rekap($dari, $sampai);

        // Perbandingan dengan periode sebelumnya (durasi sama)
        $durasi     = (strtotime($sampai) - strtotime($dari)) / 86400;
        $dariLama   = date('Y-m-d', strtotime($dari)   - ($durasi + 1) * 86400);
        $sampaiLama = date('Y-m-d', strtotime($dari)   - 86400);
        $rekapLama  = $this->transaksiModel->rekap($dariLama, $sampaiLama);

        // Tren
        $trendPendapatan = $this->hitungTrend(
            (float)($rekapLama['total_pendapatan'] ?? 0),
            (float)($rekap['total_pendapatan']     ?? 0)
        );
        $trendTransaksi  = $this->hitungTrend(
            (float)($rekapLama['total_transaksi']  ?? 0),
            (float)($rekap['total_transaksi']      ?? 0)
        );

        // Grafik harian untuk periode terpilih
        $grafikRaw   = $this->transaksiModel->grafikPendapatan((int)$durasi + 1);
        $grafikLabel = $this->buildDateLabels($dari, $sampai);
        $grafikData  = $this->mergeGrafikData($grafikLabel, $grafikRaw);

        // Pendapatan per jenis kendaraan (query khusus periode)
        $perJenis = $this->pendapatanPerJenis($dari, $sampai);

        // Pendapatan per area
        $perArea  = $this->pendapatanPerArea($dari, $sampai);

        // Top 5 jam tersibuk
        $jamSibuk = $this->jamTersibuk($dari, $sampai);

        ob_start();
        require VIEW_PATH . '/laporan/index.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layouts/app.php';
    }

    // ──────────────────────────────────────────────────────────
    //  DETAIL — Tabel transaksi dengan filter + pagination
    // ──────────────────────────────────────────────────────────
    public function detail(): void
    {
        $pageTitle = 'Detail Transaksi';
        $filter    = $this->getFilter();

        $search  = sanitize($_GET['search']         ?? '');
        $jenis   = sanitize($_GET['jenis_kendaraan'] ?? '');
        $idArea  = (int)($_GET['id_area']            ?? 0) ?: null;
        $perPage = 20;
        $hal     = max(1, (int)($_GET['hal'] ?? 1));
        $offset  = ($hal - 1) * $perPage;

        $filterQuery = [
            'search'         => $search,
            'tanggal_dari'   => $filter['dari'],
            'tanggal_sampai' => $filter['sampai'],
            'status'         => 'keluar',
            'id_area'        => $idArea,
            'jenis_kendaraan'=> $jenis,
        ];

        $total    = $this->transaksiModel->count($filterQuery);
        $totalHal = (int)ceil($total / $perPage);
        $halaman  = $hal;
        $transaksi= $this->transaksiModel->getAll($filterQuery, $perPage, $offset);
        $areas    = $this->areaModel->getAll();

        // Subtotal halaman ini
        $subtotal = array_sum(array_column($transaksi, 'biaya_total'));

        ob_start();
        require VIEW_PATH . '/laporan/detail.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layouts/app.php';
    }

    // ──────────────────────────────────────────────────────────
    //  EXPORT PDF — Laporan PDF print-ready (HTML→Print)
    // ──────────────────────────────────────────────────────────
    public function exportPdf(): void
    {
        $filter    = $this->getFilter();
        $dari      = $filter['dari'];
        $sampai    = $filter['sampai'];
        $rekap     = $this->transaksiModel->rekap($dari, $sampai);
        $transaksi = $this->transaksiModel->getForExport($dari, $sampai);
        $perJenis  = $this->pendapatanPerJenis($dari, $sampai);
        $perArea   = $this->pendapatanPerArea($dari, $sampai);

        // Render view PDF standalone (auto-print)
        require VIEW_PATH . '/laporan/pdf.php';
        exit;
    }

    // ──────────────────────────────────────────────────────────
    //  EXPORT EXCEL — File .xls (XML SpreadsheetML)
    // ──────────────────────────────────────────────────────────
    public function exportExcel(): void
    {
        $filter    = $this->getFilter();
        $dari      = $filter['dari'];
        $sampai    = $filter['sampai'];
        $rekap     = $this->transaksiModel->rekap($dari, $sampai);
        $transaksi = $this->transaksiModel->getForExport($dari, $sampai);

        $filename  = 'laporan_parkir_' . $dari . '_sd_' . $sampai . '.xls';

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        require VIEW_PATH . '/laporan/excel.php';
        exit;
    }

    // ──────────────────────────────────────────────────────────
    //  EXPORT CSV
    // ──────────────────────────────────────────────────────────
    public function exportCsv(): void
    {
        $filter    = $this->getFilter();
        $dari      = $filter['dari'];
        $sampai    = $filter['sampai'];
        $transaksi = $this->transaksiModel->getForExport($dari, $sampai);
        $filename  = 'laporan_parkir_' . $dari . '_sd_' . $sampai . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache');

        $out = fopen('php://output', 'w');

        // BOM untuk Excel agar bisa baca UTF-8
        fputs($out, "\xEF\xBB\xBF");

        // Header kolom
        fputcsv($out, [
            'Kode Tiket', 'Plat Nomor', 'Jenis Kendaraan', 'Pemilik',
            'Area Parkir', 'Petugas',
            'Waktu Masuk', 'Waktu Keluar',
            'Durasi (Jam)', 'Biaya Total', 'Bayar', 'Kembalian', 'Status',
        ]);

        // Baris data
        foreach ($transaksi as $row) {
            fputcsv($out, [
                $row['kode_parkir'],
                $row['plat_nomor'],
                ucfirst($row['jenis_kendaraan']),
                $row['pemilik'],
                $row['nama_area'],
                $row['petugas'],
                $row['waktu_masuk'],
                $row['waktu_keluar'] ?? '-',
                number_format((float)$row['durasi_jam'], 1),
                $row['biaya_total'],
                $row['bayar'],
                $row['kembalian'],
                $row['status'],
            ]);
        }

        fclose($out);
        exit;
    }

    // ──────────────────────────────────────────────────────────
    //  Query tambahan untuk laporan
    // ──────────────────────────────────────────────────────────

    /** Pendapatan & jumlah transaksi per jenis kendaraan dalam periode */
    private function pendapatanPerJenis(string $dari, string $sampai): array
    {
        $stmt = getDB()->prepare(
            "SELECT k.jenis_kendaraan,
                    COUNT(*) AS jumlah,
                    COALESCE(SUM(t.biaya_total), 0) AS pendapatan
             FROM tb_transaksi t
             JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
             WHERE t.status = 'keluar'
               AND DATE(t.waktu_keluar) BETWEEN ? AND ?
             GROUP BY k.jenis_kendaraan
             ORDER BY pendapatan DESC"
        );
        $stmt->execute([$dari, $sampai]);
        return $stmt->fetchAll();
    }

    /** Pendapatan per area dalam periode */
    private function pendapatanPerArea(string $dari, string $sampai): array
    {
        $stmt = getDB()->prepare(
            "SELECT a.nama_area,
                    COUNT(*) AS jumlah,
                    COALESCE(SUM(t.biaya_total), 0) AS pendapatan
             FROM tb_transaksi t
             JOIN tb_area_parkir a ON t.id_area = a.id_area
             WHERE t.status = 'keluar'
               AND DATE(t.waktu_keluar) BETWEEN ? AND ?
             GROUP BY a.id_area, a.nama_area
             ORDER BY pendapatan DESC"
        );
        $stmt->execute([$dari, $sampai]);
        return $stmt->fetchAll();
    }

    /** Distribusi jam tersibuk (0–23) */
    private function jamTersibuk(string $dari, string $sampai): array
    {
        $stmt = getDB()->prepare(
            "SELECT HOUR(waktu_masuk) AS jam, COUNT(*) AS jumlah
             FROM tb_transaksi
             WHERE DATE(waktu_masuk) BETWEEN ? AND ?
             GROUP BY HOUR(waktu_masuk)
             ORDER BY jumlah DESC
             LIMIT 5"
        );
        $stmt->execute([$dari, $sampai]);
        return $stmt->fetchAll();
    }

    // ──────────────────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────────────────

    /** Bangun array label tanggal dari $dari s/d $sampai */
    private function buildDateLabels(string $dari, string $sampai): array
    {
        $labels = [];
        $cur    = strtotime($dari);
        $end    = strtotime($sampai);
        while ($cur <= $end) {
            $labels[] = date('Y-m-d', $cur);
            $cur      = strtotime('+1 day', $cur);
        }
        return $labels;
    }

    private function mergeGrafikData(array $labels, array $raw): array
    {
        $indexed = array_column($raw, 'total', 'tanggal');
        return array_map(fn($tgl) => (int)($indexed[$tgl] ?? 0), $labels);
    }

    private function hitungTrend(float $lama, float $baru): array
    {
        if ($lama <= 0) return ['persen' => 100.0, 'naik' => $baru >= 0];
        $persen = round((($baru - $lama) / $lama) * 100, 1);
        return ['persen' => abs($persen), 'naik' => $persen >= 0];
    }
}