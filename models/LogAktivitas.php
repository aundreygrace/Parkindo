<?php
/**
 * models/LogAktivitas.php
 */
require_once __DIR__ . '/../config/database.php';

class LogAktivitas
{
    private PDO $db;
    public function __construct() { $this->db = getDB(); }

    /**
     * Ambil log aktivitas dengan filter
     *
     * @param array $filter [search, id_user, modul, tanggal_dari, tanggal_sampai]
     * @param int   $limit
     * @param int   $offset
     * @return array
     */
    public function getAll(array $filter = [], int $limit = 30, int $offset = 0): array
    {
        $sql = "SELECT l.*, u.nama_lengkap, u.username, u.role
                FROM tb_log_aktivitas l
                JOIN tb_user u ON l.id_user = u.id_user
                WHERE 1=1";
        $params = [];

        if (!empty($filter['search'])) {
            $sql    .= " AND (l.aktivitas LIKE ? OR u.nama_lengkap LIKE ?)";
            $params[] = "%{$filter['search']}%";
            $params[] = "%{$filter['search']}%";
        }
        if (!empty($filter['id_user'])) {
            $sql    .= " AND l.id_user = ?";
            $params[] = $filter['id_user'];
        }
        if (!empty($filter['modul'])) {
            $sql    .= " AND l.modul = ?";
            $params[] = $filter['modul'];
        }
        if (!empty($filter['tanggal_dari'])) {
            $sql    .= " AND DATE(l.waktu_aktivitas) >= ?";
            $params[] = $filter['tanggal_dari'];
        }
        if (!empty($filter['tanggal_sampai'])) {
            $sql    .= " AND DATE(l.waktu_aktivitas) <= ?";
            $params[] = $filter['tanggal_sampai'];
        }

        $sql .= " ORDER BY l.waktu_aktivitas DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function count(array $filter = []): int
    {
        $sql    = "SELECT COUNT(*) FROM tb_log_aktivitas l JOIN tb_user u ON l.id_user = u.id_user WHERE 1=1";
        $params = [];

        if (!empty($filter['search'])) {
            $sql    .= " AND (l.aktivitas LIKE ? OR u.nama_lengkap LIKE ?)";
            $params[] = "%{$filter['search']}%";
            $params[] = "%{$filter['search']}%";
        }
        if (!empty($filter['modul'])) {
            $sql    .= " AND l.modul = ?";
            $params[] = $filter['modul'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /** Daftar modul unik (untuk filter dropdown) */
    public function getModuls(): array
    {
        return $this->db->query(
            "SELECT DISTINCT modul FROM tb_log_aktivitas ORDER BY modul"
        )->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Hapus log lebih dari N hari (maintenance) */
    public function clearOld(int $hari = 90): int
    {
        $stmt = $this->db->prepare(
            "DELETE FROM tb_log_aktivitas
             WHERE waktu_aktivitas < DATE_SUB(NOW(), INTERVAL ? DAY)"
        );
        $stmt->execute([$hari]);
        return $stmt->rowCount();
    }
}