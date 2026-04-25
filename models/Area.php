<?php
/**
 * models/Area.php
 */
require_once __DIR__ . '/../config/database.php';

class Area
{
    private PDO $db;
    public function __construct() { $this->db = getDB(); }

    public function getAll(): array
    {
        return $this->db->query(
            "SELECT *, (kapasitas - terisi) AS tersedia FROM tb_area_parkir ORDER BY nama_area"
        )->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT *, (kapasitas - terisi) AS tersedia FROM tb_area_parkir WHERE id_area = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Ambil area yang masih tersedia (belum penuh) */
    public function getAvailable(): array
    {
        return $this->db->query(
            "SELECT *, (kapasitas - terisi) AS tersedia
             FROM tb_area_parkir
             WHERE terisi < kapasitas
             ORDER BY nama_area"
        )->fetchAll();
    }

    /** Area yang hampir penuh atau penuh (untuk notifikasi) */
    public function getHampirPenuh(): array
    {
        $threshold = KAPASITAS_NOTIF_PERSEN;
        $stmt = $this->db->prepare(
            "SELECT *, ROUND(terisi/kapasitas*100) AS persen_terisi
             FROM tb_area_parkir
             WHERE kapasitas > 0 AND (terisi/kapasitas*100) >= ?
             ORDER BY persen_terisi DESC"
        );
        $stmt->execute([$threshold]);
        return $stmt->fetchAll();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO tb_area_parkir (nama_area, kapasitas, terisi, keterangan)
             VALUES (?, ?, 0, ?)"
        );
        return $stmt->execute([
            $data['nama_area'],
            $data['kapasitas'],
            $data['keterangan'] ?? null,
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE tb_area_parkir
             SET nama_area = ?, kapasitas = ?, keterangan = ?
             WHERE id_area = ?"
        );
        return $stmt->execute([
            $data['nama_area'],
            $data['kapasitas'],
            $data['keterangan'] ?? null,
            $id,
        ]);
    }

    public function delete(int $id): bool
    {
        // Cek apakah ada kendaraan di area ini
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM tb_transaksi WHERE id_area = ? AND status = 'masuk'"
        );
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) return false;

        $stmt = $this->db->prepare("DELETE FROM tb_area_parkir WHERE id_area = ?");
        return $stmt->execute([$id]);
    }

    /** Tambah jumlah terisi saat kendaraan masuk */
    public function tambahTerisi(int $id): void
    {
        $this->db->prepare(
            "UPDATE tb_area_parkir SET terisi = LEAST(terisi + 1, kapasitas) WHERE id_area = ?"
        )->execute([$id]);
    }

    /** Kurangi jumlah terisi saat kendaraan keluar */
    public function kurangiTerisi(int $id): void
    {
        $this->db->prepare(
            "UPDATE tb_area_parkir SET terisi = GREATEST(terisi - 1, 0) WHERE id_area = ?"
        )->execute([$id]);
    }
}