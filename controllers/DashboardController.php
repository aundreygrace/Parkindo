<?php
/**
 * controllers/DashboardController.php
 *
 * Menampilkan dashboard berbeda per role:
 *  - Admin   : ringkasan sistem + log terbaru + status area
 *  - Petugas : kendaraan sedang parkir + transaksi hari ini
 *  - Owner   : grafik pendapatan + rekap + perbandingan tren
 */

require_once __DIR__ . '/../models/Transaksi.php';
require_once __DIR__ . '/../models/Area.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Kendaraan.php';
require_once __DIR__ . '/../models/LogAktivitas.php';
require_once __DIR__ . '/../helpers/functions.php';

class DashboardController
{
    private Transaksi    $transaksiModel;
    private Area         $areaModel;
    private User         $userModel;
    private Kendaraan    $kendaraanModel;
    private LogAktivitas $logModel;

    public function __construct()
    {
        $this->transaksiModel = new Transaksi();
        $this->areaModel      = new Area();
        $this->userModel      = new User();
        $this->kendaraanModel = new Kendaraan();
        $this->logModel       = new LogAktivitas();
    }

    // ──────────────────────────────────────────────────────────
    //  Entry point — pilih view berdasarkan role
    // ──────────────────────────────────────────────────────────
    public function index(): void
    {
        requireLogin();

        match ($_SESSION['user_role']) {
            ROLE_ADMIN   => $this->renderAdmin(),
            ROLE_PETUGAS => $this->renderPetugas(),
            ROLE_OWNER   => $this->renderOwner(),
            default      => redirect('?page=403'),
        };
    }

    // ──────────────────────────────────────────────────────────
    //  ADMIN
    // ──────────────────────────────────────────────────────────
    private function renderAdmin(): void
    {
        $pageTitle = 'Dashboard Admin';

        $stats = [
            'total_user'       => $this->userModel->countAktif(),
            'kendaraan_parkir' => $this->transaksiModel->countAktif(),
            'transaksi_hari'   => $this->transaksiModel->countHariIni(),
            'pendapatan_hari'  => $this->transaksiModel->pendapatanHariIni(),
        ];

        $areas            = $this->areaModel->getAll();
        $logs             = $this->logModel->getAll([], 10, 0);
        $grafikLabel      = $this->buildDateLabels(7);
        $grafikData       = $this->mergeGrafikData(
                                $grafikLabel,
                                $this->transaksiModel->grafikPendapatan(7)
                            );
        $jenisData        = $this->normalizeJenisData(
                                $this->transaksiModel->grafikJenisKendaraan()
                            );

        ob_start();
        require VIEW_PATH . '/dashboard/admin.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layouts/app.php';
    }

    // ──────────────────────────────────────────────────────────
    //  PETUGAS
    // ──────────────────────────────────────────────────────────
    private function renderPetugas(): void
    {
        $pageTitle = 'Dashboard Petugas';

        $stats = [
            'kendaraan_parkir' => $this->transaksiModel->countAktif(),
            'transaksi_hari'   => $this->transaksiModel->countHariIni(),
            'pendapatan_hari'  => $this->transaksiModel->pendapatanHariIni(),
        ];

        $sedangParkir     = $this->transaksiModel->getAll(['status' => 'masuk'], 10, 0);
        $transaksiHariIni = $this->transaksiModel->getAll([
            'status'       => 'keluar',
            'tanggal_dari' => date('Y-m-d'),
        ], 8, 0);
        $areas            = $this->areaModel->getAvailable();

        ob_start();
        require VIEW_PATH . '/dashboard/petugas.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layouts/app.php';
    }

    // ──────────────────────────────────────────────────────────
    //  OWNER
    // ──────────────────────────────────────────────────────────
    private function renderOwner(): void
    {
        $pageTitle = 'Dashboard Owner';

        $dari   = date('Y-m-d', strtotime('-29 days'));
        $sampai = date('Y-m-d');
        $rekap  = $this->transaksiModel->rekap($dari, $sampai);

        $stats = [
            'total_pendapatan' => $rekap['total_pendapatan'] ?? 0,
            'total_transaksi'  => $rekap['total_transaksi']  ?? 0,
            'rata_pendapatan'  => $rekap['rata_pendapatan']  ?? 0,
            'pendapatan_hari'  => $this->transaksiModel->pendapatanHariIni(),
        ];

        $grafikLabel = $this->buildDateLabels(30);
        $grafikData  = $this->mergeGrafikData(
                           $grafikLabel,
                           $this->transaksiModel->grafikPendapatan(30)
                       );
        $jenisData   = $this->normalizeJenisData(
                           $this->transaksiModel->grafikJenisKendaraan()
                       );

        $mingguIni  = $this->transaksiModel->rekap(
            date('Y-m-d', strtotime('monday this week')), date('Y-m-d')
        );
        $mingguLalu = $this->transaksiModel->rekap(
            date('Y-m-d', strtotime('monday last week')),
            date('Y-m-d', strtotime('sunday last week'))
        );
        $trendPersen = $this->hitungTrend(
            (float)($mingguLalu['total_pendapatan'] ?? 0),
            (float)($mingguIni['total_pendapatan']  ?? 0)
        );

        $areas = $this->areaModel->getAll();

        ob_start();
        require VIEW_PATH . '/dashboard/owner.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layouts/app.php';
    }

    // ──────────────────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────────────────

    /** Bangun array tanggal N hari terakhir format Y-m-d */
    private function buildDateLabels(int $hari): array
    {
        $labels = [];
        for ($i = $hari - 1; $i >= 0; $i--) {
            $labels[] = date('Y-m-d', strtotime("-{$i} days"));
        }
        return $labels;
    }

    /**
     * Gabungkan data DB ke label penuh, isi 0 untuk hari tanpa transaksi
     *
     * @param array $labels  ['2025-06-01', '2025-06-02', ...]
     * @param array $raw     [['tanggal'=>'2025-06-01','total'=>15000], ...]
     * @return array         [0, 15000, 0, ...]
     */
    private function mergeGrafikData(array $labels, array $raw): array
    {
        $indexed = array_column($raw, 'total', 'tanggal');
        return array_map(fn($tgl) => (int)($indexed[$tgl] ?? 0), $labels);
    }

    /**
     * Normalisasi jenis kendaraan ke array tetap
     * @return array ['motor'=>N, 'mobil'=>N, 'lainnya'=>N]
     */
    private function normalizeJenisData(array $raw): array
    {
        $data = ['motor' => 0, 'mobil' => 0, 'lainnya' => 0];
        foreach ($raw as $row) {
            if (isset($data[$row['jenis_kendaraan']])) {
                $data[$row['jenis_kendaraan']] = (int)$row['total'];
            }
        }
        return $data;
    }

    /**
     * Hitung tren perubahan pendapatan (%)
     * @return array ['persen' => float, 'naik' => bool]
     */
    private function hitungTrend(float $lama, float $baru): array
    {
        if ($lama <= 0) {
            return ['persen' => 100.0, 'naik' => true];
        }
        $persen = round((($baru - $lama) / $lama) * 100, 1);
        return ['persen' => abs($persen), 'naik' => $persen >= 0];
    }
}