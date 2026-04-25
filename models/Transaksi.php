<?php
/**
 * models/Transaksi.php
 * Model inti sistem parkir — mencatat masuk, keluar, dan laporan
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/functions.php';

class Transaksi
{
    private PDO $db;
    public function __construct() { $this->db = getDB(); }

    // ──────────────────────────────────────────────────────────
    //  READ
    // ──────────────────────────────────────────────────────────

    /**
     * Ambil daftar transaksi dengan filter lengkap
     *
     * @param array $filter [search, status, tanggal_dari, tanggal_sampai, id_area]
     * @param int   $limit
     * @param int   $offset
     * @return array
     */
    public function getAll(array $filter = [], int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT t.*,
                       k.plat_nomor, k.jenis_kendaraan, k.pemilik, k.warna,
                       u.nama_lengkap AS nama_petugas,
                       a.nama_area,
                       tf.tarif_per_jam, tf.tarif_masuk
                FROM tb_transaksi t
                JOIN tb_kendaraan k  ON t.id_kendaraan = k.id_kendaraan
                JOIN tb_user u       ON t.id_user = u.id_user
                JOIN tb_area_parkir a ON t.id_area = a.id_area
                JOIN tb_tarif tf     ON t.id_tarif = tf.id_tarif
                WHERE 1=1";
        $params = [];

        if (!empty($filter['search'])) {
            $sql    .= " AND (k.plat_nomor LIKE ? OR k.pemilik LIKE ? OR t.kode_parkir LIKE ?)";
            $params[] = "%{$filter['search']}%";
            $params[] = "%{$filter['search']}%";
            $params[] = "%{$filter['search']}%";
        }
        if (!empty($filter['status'])) {
            $sql    .= " AND t.status = ?";
            $params[] = $filter['status'];
        }
        if (!empty($filter['tanggal_dari'])) {
            $sql    .= " AND DATE(t.waktu_masuk) >= ?";
            $params[] = $filter['tanggal_dari'];
        }
        if (!empty($filter['tanggal_sampai'])) {
            $sql    .= " AND DATE(t.waktu_masuk) <= ?";
            $params[] = $filter['tanggal_sampai'];
        }
        if (!empty($filter['id_area'])) {
            $sql    .= " AND t.id_area = ?";
            $params[] = $filter['id_area'];
        }
        if (!empty($filter['jenis_kendaraan'])) {
            $sql    .= " AND k.jenis_kendaraan = ?";
            $params[] = $filter['jenis_kendaraan'];
        }

        $sql .= " ORDER BY t.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Hitung total transaksi untuk pagination */
    public function count(array $filter = []): int
    {
        $sql = "SELECT COUNT(*) FROM tb_transaksi t
                JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
                WHERE 1=1";
        $params = [];

        if (!empty($filter['search'])) {
            $sql    .= " AND (k.plat_nomor LIKE ? OR k.pemilik LIKE ? OR t.kode_parkir LIKE ?)";
            $params[] = "%{$filter['search']}%";
            $params[] = "%{$filter['search']}%";
            $params[] = "%{$filter['search']}%";
        }
        if (!empty($filter['status'])) {
            $sql    .= " AND t.status = ?";
            $params[] = $filter['status'];
        }
        if (!empty($filter['tanggal_dari'])) {
            $sql    .= " AND DATE(t.waktu_masuk) >= ?";
            $params[] = $filter['tanggal_dari'];
        }
        if (!empty($filter['tanggal_sampai'])) {
            $sql    .= " AND DATE(t.waktu_masuk) <= ?";
            $params[] = $filter['tanggal_sampai'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Ambil satu transaksi lengkap berdasarkan ID
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT t.*,
                    k.plat_nomor, k.jenis_kendaraan, k.pemilik, k.warna, k.no_hp,
                    u.nama_lengkap AS nama_petugas,
                    a.nama_area,
                    tf.tarif_per_jam, tf.tarif_masuk, tf.jenis_kendaraan AS jenis_tarif
             FROM tb_transaksi t
             JOIN tb_kendaraan k   ON t.id_kendaraan = k.id_kendaraan
             JOIN tb_user u        ON t.id_user = u.id_user
             JOIN tb_area_parkir a ON t.id_area = a.id_area
             JOIN tb_tarif tf      ON t.id_tarif = tf.id_tarif
             WHERE t.id_parkir = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Cek apakah kendaraan sedang parkir (belum keluar)
     */
    public function getAktifByKendaraan(int $idKendaraan): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM tb_transaksi
             WHERE id_kendaraan = ? AND status = 'masuk'
             ORDER BY waktu_masuk DESC LIMIT 1"
        );
        $stmt->execute([$idKendaraan]);
        return $stmt->fetch() ?: null;
    }

    // ──────────────────────────────────────────────────────────
    //  TRANSAKSI MASUK
    // ──────────────────────────────────────────────────────────

    /**
     * Proses kendaraan masuk parkir
     *
     * @param array $data [id_kendaraan, id_tarif, id_area, id_user]
     * @return int  ID transaksi yang baru dibuat, 0 jika gagal
     */
    public function masuk(array $data): int
    {
        $kode = generateKodeParkir();

        $stmt = $this->db->prepare(
            "INSERT INTO tb_transaksi
            (kode_parkir, id_kendaraan, waktu_masuk, id_tarif, id_area, id_user, status)
            VALUES (?, ?, ?, ?, ?, ?, 'masuk')"

        );
        $ok = $stmt->execute([
            $kode,
            $data['id_kendaraan'],
            nowWIB(),
            $data['id_tarif'],
            $data['id_area'],
            $data['id_user'],
        ]);

        return $ok ? (int)$this->db->lastInsertId() : 0;
    }

    // ──────────────────────────────────────────────────────────
    //  TRANSAKSI KELUAR
    // ──────────────────────────────────────────────────────────

    /**
     * Proses kendaraan keluar parkir + hitung biaya
     *
     * @param int   $idParkir
     * @param array $tarif     [tarif_per_jam, tarif_masuk]
     * @param float $bayar     Nominal uang yang dibayar
     * @return array [durasi_jam, biaya_total, kembalian]
     */
    public function keluar(int $idParkir, array $tarif, float $bayar): array
    {
        $waktuKeluar = nowWIB();

        // Ambil waktu masuk
        $stmt = $this->db->prepare("SELECT waktu_masuk FROM tb_transaksi WHERE id_parkir = ?");
        $stmt->execute([$idParkir]);
        $row = $stmt->fetch();

        $durasi    = hitungDurasi($row['waktu_masuk'], $waktuKeluar);
        $biaya     = hitungBiaya($durasi, $tarif['tarif_per_jam'], $tarif['tarif_masuk']);
        $kembalian = max(0, $bayar - $biaya);

        $stmt = $this->db->prepare(
            "UPDATE tb_transaksi
             SET waktu_keluar = ?, durasi_jam = ?, biaya_total = ?,
                 bayar = ?, kembalian = ?, status = 'keluar'
             WHERE id_parkir = ?"
        );
        $stmt->execute([$waktuKeluar, $durasi, $biaya, $bayar, $kembalian, $idParkir]);

        return [
            'durasi_jam'  => $durasi,
            'biaya_total' => $biaya,
            'kembalian'   => $kembalian,
        ];
    }

    // ──────────────────────────────────────────────────────────
    //  STATISTIK DASHBOARD
    // ──────────────────────────────────────────────────────────

    /** Total kendaraan yang sedang parkir sekarang */
    public function countAktif(): int
    {
        return (int)$this->db->query(
            "SELECT COUNT(*) FROM tb_transaksi WHERE status = 'masuk'"
        )->fetchColumn();
    }

    /** Total pendapatan hari ini */
    public function pendapatanHariIni(): int
    {
        return (int)$this->db->query(
            "SELECT COALESCE(SUM(biaya_total), 0) FROM tb_transaksi
            WHERE status = 'keluar' 
            AND DATE(CONVERT_TZ(waktu_keluar, '+00:00', '+07:00')) = CURDATE()"

        )->fetchColumn();
    }

    /** Total transaksi keluar hari ini */
    public function countHariIni(): int
    {
        return (int)$this->db->query(
            "SELECT COUNT(*) FROM tb_transaksi
            WHERE DATE(CONVERT_TZ(created_at, '+00:00', '+07:00')) = CURDATE()"

        )->fetchColumn();
    }

    /**
     * Pendapatan per hari dalam N hari terakhir (untuk grafik)
     *
     * @param int $hari Jumlah hari ke belakang
     * @return array [['tanggal' => ..., 'total' => ...], ...]
     */
    public function grafikPendapatan(int $hari = 7): array
    {
        $stmt = $this->db->prepare(
        "SELECT DATE(CONVERT_TZ(waktu_keluar, '+00:00', '+07:00')) AS tanggal,
                COALESCE(SUM(biaya_total), 0) AS total
        FROM tb_transaksi
        WHERE status = 'keluar'
        AND waktu_keluar >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
        GROUP BY DATE(CONVERT_TZ(waktu_keluar, '+00:00', '+07:00'))
        ORDER BY tanggal ASC"
        );
        $stmt->execute([$hari]);
        return $stmt->fetchAll();
    }

    /**
     * Volume kendaraan per jenis hari ini (untuk pie chart)
     */
    public function grafikJenisKendaraan(): array
    {
        return $this->db->query(
            "SELECT k.jenis_kendaraan, COUNT(*) AS total
             FROM tb_transaksi t
             JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
             WHERE DATE(t.created_at) = CURDATE()
             GROUP BY k.jenis_kendaraan"
        )->fetchAll();
    }

    /**
     * Rekap transaksi berdasarkan rentang waktu (untuk owner)
     *
     * @param string $dari   Format Y-m-d
     * @param string $sampai Format Y-m-d
     * @return array
     */
    public function rekap(string $dari, string $sampai): array
    {
        $stmt = $this->db->prepare(
            "SELECT
               COUNT(*) AS total_transaksi,
               COALESCE(SUM(biaya_total), 0) AS total_pendapatan,
               COALESCE(AVG(biaya_total), 0) AS rata_pendapatan,
               COALESCE(AVG(durasi_jam), 0)  AS rata_durasi,
               SUM(CASE WHEN k.jenis_kendaraan = 'motor' THEN 1 ELSE 0 END) AS total_motor,
               SUM(CASE WHEN k.jenis_kendaraan = 'mobil' THEN 1 ELSE 0 END) AS total_mobil,
               SUM(CASE WHEN k.jenis_kendaraan = 'lainnya' THEN 1 ELSE 0 END) AS total_lainnya
             FROM tb_transaksi t
             JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
             WHERE t.status = 'keluar'
               AND DATE(t.waktu_keluar) BETWEEN ? AND ?"
        );
        $stmt->execute([$dari, $sampai]);
        return $stmt->fetch();
    }

    /**
     * Detail transaksi untuk export (owner)
     */
    public function getForExport(string $dari, string $sampai): array
    {
        $stmt = $this->db->prepare(
            "SELECT t.kode_parkir, k.plat_nomor, k.jenis_kendaraan, k.pemilik,
                    a.nama_area, u.nama_lengkap AS petugas,
                    t.waktu_masuk, t.waktu_keluar,
                    t.durasi_jam, t.biaya_total, t.bayar, t.kembalian, t.status
             FROM tb_transaksi t
             JOIN tb_kendaraan k   ON t.id_kendaraan = k.id_kendaraan
             JOIN tb_area_parkir a ON t.id_area = a.id_area
             JOIN tb_user u        ON t.id_user = u.id_user
             WHERE DATE(t.waktu_masuk) BETWEEN ? AND ?
             ORDER BY t.waktu_masuk ASC"
        );
        $stmt->execute([$dari, $sampai]);
        return $stmt->fetchAll();
    }
}