<?php
/**
 * models/User.php
 * Model untuk tabel tb_user
 * Menangani semua operasi data user
 */

require_once __DIR__ . '/../config/database.php';

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    // ──────────────────────────────────────────────────────────
    //  READ
    // ──────────────────────────────────────────────────────────

    /**
     * Ambil semua user dengan filter opsional
     *
     * @param string $search Kata kunci pencarian (nama/username)
     * @param string $role   Filter berdasarkan role
     * @return array
     */
    public function getAll(string $search = '', string $role = ''): array
    {
        $sql    = "SELECT * FROM tb_user WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql    .= " AND (nama_lengkap LIKE ? OR username LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($role !== '') {
            $sql    .= " AND role = ?";
            $params[] = $role;
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Ambil satu user berdasarkan ID
     *
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM tb_user WHERE id_user = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Ambil user berdasarkan username (untuk login)
     *
     * @param string $username
     * @return array|null
     */
    public function getByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM tb_user WHERE username = ? AND status_aktif = 1 LIMIT 1"
        );
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Hitung total user aktif
     */
    public function countAktif(): int
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) FROM tb_user WHERE status_aktif = 1"
        );
        return (int) $stmt->fetchColumn();
    }

    // ──────────────────────────────────────────────────────────
    //  CREATE
    // ──────────────────────────────────────────────────────────

    /**
     * Tambah user baru
     *
     * @param array $data [nama_lengkap, username, password, role]
     * @return bool
     */
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO tb_user (nama_lengkap, username, password, role, status_aktif)
             VALUES (?, ?, ?, ?, 1)"
        );
        return $stmt->execute([
            $data['nama_lengkap'],
            $data['username'],
            password_hash($data['password'], PASSWORD_BCRYPT),
            $data['role'],
        ]);
    }

    // ──────────────────────────────────────────────────────────
    //  UPDATE
    // ──────────────────────────────────────────────────────────

    /**
     * Update data user
     * Jika password dikosongkan, password lama tidak berubah
     *
     * @param int   $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        if (!empty($data['password'])) {
            $stmt = $this->db->prepare(
                "UPDATE tb_user
                 SET nama_lengkap = ?, username = ?, password = ?, role = ?, status_aktif = ?
                 WHERE id_user = ?"
            );
            return $stmt->execute([
                $data['nama_lengkap'],
                $data['username'],
                password_hash($data['password'], PASSWORD_BCRYPT),
                $data['role'],
                $data['status_aktif'] ?? 1,
                $id,
            ]);
        }

        $stmt = $this->db->prepare(
            "UPDATE tb_user
             SET nama_lengkap = ?, username = ?, role = ?, status_aktif = ?
             WHERE id_user = ?"
        );
        return $stmt->execute([
            $data['nama_lengkap'],
            $data['username'],
            $data['role'],
            $data['status_aktif'] ?? 1,
            $id,
        ]);
    }

    /**
     * Toggle status aktif user
     *
     * @param int $id
     * @return bool
     */
    public function toggleStatus(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE tb_user SET status_aktif = IF(status_aktif = 1, 0, 1)
             WHERE id_user = ?"
        );
        return $stmt->execute([$id]);
    }

    // ──────────────────────────────────────────────────────────
    //  DELETE
    // ──────────────────────────────────────────────────────────

    /**
     * Hapus user permanen dari database (hard delete)
     * Cek dulu apakah user punya transaksi — jika ada, tidak bisa dihapus
     *
     * @param int $id
     * @return array ['ok' => bool, 'reason' => string]
     */
    public function hardDelete(int $id): array
    {
        // Cek transaksi yang dilayani user ini
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM tb_transaksi WHERE id_user = ?"
        );
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            return [
                'ok'     => false,
                'reason' => 'User memiliki riwayat transaksi. Gunakan Nonaktifkan saja.',
            ];
        }

        // Hapus log aktivitas user dulu (FK constraint)
        $this->db->prepare("DELETE FROM tb_log_aktivitas WHERE id_user = ?")->execute([$id]);

        // Hapus kendaraan yang didaftarkan user ini (set ke admin id=1 dulu jika ada)
        // Lebih aman: set id_user ke null / admin daripada hapus kendaraan
        $this->db->prepare(
            "UPDATE tb_kendaraan SET id_user = 1 WHERE id_user = ?"
        )->execute([$id]);

        // Hapus user
        $stmt = $this->db->prepare("DELETE FROM tb_user WHERE id_user = ?");
        $stmt->execute([$id]);

        return ['ok' => $stmt->rowCount() > 0, 'reason' => ''];
    }

    // ──────────────────────────────────────────────────────────
    //  VALIDASI
    // ──────────────────────────────────────────────────────────

    /**
     * Cek apakah username sudah digunakan (exclude ID tertentu untuk update)
     *
     * @param string $username
     * @param int    $excludeId ID user yang dikecualikan (edit)
     * @return bool
     */
    public function isUsernameTaken(string $username, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM tb_user
             WHERE username = ? AND id_user != ?"
        );
        $stmt->execute([$username, $excludeId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Verifikasi password user
     *
     * @param string $plainPassword
     * @param string $hashedPassword
     * @return bool
     */
    public function verifyPassword(string $plainPassword, string $hashedPassword): bool
    {
        return password_verify($plainPassword, $hashedPassword);
    }
}