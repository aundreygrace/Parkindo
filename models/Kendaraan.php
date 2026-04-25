<?php
/**
 * models/Kendaraan.php
 * Model untuk tabel tb_kendaraan
 */

require_once __DIR__ . '/../config/database.php';

class Kendaraan
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    /**
     * Ambil semua kendaraan beserta nama petugas pendaftar
     *
     * @param string $search  Plat nomor / pemilik
     * @param string $jenis   Filter jenis kendaraan
     * @param int    $limit
     * @param int    $offset
     * @return array
     */
    public function getAll(string $search = '', string $jenis = '', int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT k.*, u.nama_lengkap AS nama_petugas
                FROM tb_kendaraan k
                JOIN tb_user u ON k.id_user = u.id_user
                WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql    .= " AND (k.plat_nomor LIKE ? OR k.pemilik LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($jenis !== '') {
            $sql    .= " AND k.jenis_kendaraan = ?";
            $params[] = $jenis;
        }

        $sql .= " ORDER BY k.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Hitung total kendaraan (untuk pagination)
     */
    public function count(string $search = '', string $jenis = ''): int
    {
        $sql    = "SELECT COUNT(*) FROM tb_kendaraan WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql    .= " AND (plat_nomor LIKE ? OR pemilik LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        if ($jenis !== '') {
            $sql    .= " AND jenis_kendaraan = ?";
            $params[] = $jenis;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Ambil kendaraan berdasarkan ID
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT k.*, u.nama_lengkap AS nama_petugas
             FROM tb_kendaraan k
             JOIN tb_user u ON k.id_user = u.id_user
             WHERE k.id_kendaraan = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Cari kendaraan berdasarkan plat nomor (untuk transaksi masuk)
     */
    public function getByPlat(string $plat): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM tb_kendaraan WHERE plat_nomor = ? LIMIT 1"
        );
        $stmt->execute([strtoupper(trim($plat))]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Tambah kendaraan baru
     */
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO tb_kendaraan
             (plat_nomor, jenis_kendaraan, warna, pemilik, no_hp, id_user)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            strtoupper(trim($data['plat_nomor'])),
            $data['jenis_kendaraan'],
            $data['warna'],
            $data['pemilik'],
            $data['no_hp'] ?? null,
            $data['id_user'],
        ]);
    }

    /**
     * Update data kendaraan
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE tb_kendaraan
             SET plat_nomor = ?, jenis_kendaraan = ?, warna = ?, pemilik = ?, no_hp = ?
             WHERE id_kendaraan = ?"
        );
        return $stmt->execute([
            strtoupper(trim($data['plat_nomor'])),
            $data['jenis_kendaraan'],
            $data['warna'],
            $data['pemilik'],
            $data['no_hp'] ?? null,
            $id,
        ]);
    }

    /**
     * Hapus kendaraan (hanya jika tidak ada transaksi aktif)
     */
    public function delete(int $id): bool
    {
        // Cek transaksi aktif
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM tb_transaksi
             WHERE id_kendaraan = ? AND status = 'masuk'"
        );
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            return false; // Tidak bisa dihapus
        }

        $stmt = $this->db->prepare(
            "DELETE FROM tb_kendaraan WHERE id_kendaraan = ?"
        );
        return $stmt->execute([$id]);
    }

    /**
     * Cek apakah plat nomor sudah terdaftar
     */
    public function isPlatTaken(string $plat, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM tb_kendaraan
             WHERE plat_nomor = ? AND id_kendaraan != ?"
        );
        $stmt->execute([strtoupper(trim($plat)), $excludeId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}