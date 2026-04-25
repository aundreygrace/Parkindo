<?php
/**
 * models/Tarif.php
 */
require_once __DIR__ . '/../config/database.php';

class Tarif
{
    private PDO $db;
    public function __construct() { $this->db = getDB(); }

    public function getAll(): array
    {
        return $this->db->query(
            "SELECT * FROM tb_tarif ORDER BY jenis_kendaraan"
        )->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM tb_tarif WHERE id_tarif = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getByJenis(string $jenis): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM tb_tarif WHERE jenis_kendaraan = ? LIMIT 1");
        $stmt->execute([$jenis]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO tb_tarif (jenis_kendaraan, tarif_per_jam, tarif_masuk, keterangan)
             VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([
            $data['jenis_kendaraan'],
            $data['tarif_per_jam'],
            $data['tarif_masuk'] ?? 0,
            $data['keterangan'] ?? null,
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE tb_tarif
             SET jenis_kendaraan = ?, tarif_per_jam = ?, tarif_masuk = ?, keterangan = ?
             WHERE id_tarif = ?"
        );
        return $stmt->execute([
            $data['jenis_kendaraan'],
            $data['tarif_per_jam'],
            $data['tarif_masuk'] ?? 0,
            $data['keterangan'] ?? null,
            $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM tb_tarif WHERE id_tarif = ?");
        return $stmt->execute([$id]);
    }

    /** Cek apakah tarif masih dipakai pada transaksi dengan status 'masuk' */
    public function isUsedInActiveTransaction(int $id): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM tb_transaksi WHERE id_tarif = ? AND status = 'masuk'"
        );
        $stmt->execute([$id]);
        return (int)$stmt->fetchColumn() > 0;
    }
}