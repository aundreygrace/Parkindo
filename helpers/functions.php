<?php
/**
 * helpers/functions.php
 * Fungsi-fungsi global reusable
 * Aplikasi Parkir — UKK RPL 2025/2026
 *
 * Daftar fungsi:
 *  — Session & Auth   : startSession, isLoggedIn, requireLogin, requireRole, getCurrentUser
 *  — Flash Message    : setFlash, getFlash, hasFlash
 *  — Format & Output  : formatRupiah, formatTanggal, formatDurasi, e, redirect
 *  — Bisnis Parkir    : generateKodeParkir, hitungBiaya, hitungDurasi
 *  — Log Aktivitas    : logAktivitas
 *  — Validasi         : sanitize, isValidPlat
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

// ══════════════════════════════════════════════════════════════
//  SESSION & AUTH
// ══════════════════════════════════════════════════════════════

// ══════════════════════════════════════════════════════════════
//  SESSION HANDLER — Database-backed (untuk Vercel Serverless)
// ══════════════════════════════════════════════════════════════

class DbSessionHandler implements SessionHandlerInterface
{
    private PDO $db;

    public function open(string $path, string $name): bool
    {
        try {
            $this->db = getDB();
            $this->db->exec(
                "CREATE TABLE IF NOT EXISTS `tb_sessions` (
                    `id`            VARCHAR(128)    NOT NULL,
                    `payload`       MEDIUMTEXT      NOT NULL,
                    `last_activity` INT UNSIGNED    NOT NULL DEFAULT 0,
                    PRIMARY KEY (`id`),
                    INDEX `idx_last_activity` (`last_activity`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
            return true;
        } catch (PDOException $e) {
            error_log('Session open error: ' . $e->getMessage());
            return false;
        }
    }

    public function close(): bool { return true; }

    public function read(string $id): string|false
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT payload FROM tb_sessions WHERE id = ? AND last_activity > ?"
            );
            $stmt->execute([$id, time() - SESSION_LIFETIME]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $row['payload'] : '';
        } catch (PDOException $e) {
            return '';
        }
    }

    public function write(string $id, string $data): bool
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO tb_sessions (id, payload, last_activity)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE 
                 payload = VALUES(payload), 
                 last_activity = VALUES(last_activity)"
            );
            return $stmt->execute([$id, $data, time()]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function destroy(string $id): bool
    {
        try {
            $this->db->prepare(
                "DELETE FROM tb_sessions WHERE id = ?"
            )->execute([$id]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function gc(int $max_lifetime): int|false
    {
        try {
            $stmt = $this->db->prepare(
                "DELETE FROM tb_sessions WHERE last_activity < ?"
            );
            $stmt->execute([time() - $max_lifetime]);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            return false;
        }
    }
}

function startSession(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        if (isset($_SESSION['last_activity'])) {
            if (time() - $_SESSION['last_activity'] > SESSION_LIFETIME) {
                session_unset();
                session_destroy();
                redirect('?page=login&msg=timeout');
            }
        }
        $_SESSION['last_activity'] = time();
        return;
    }

    $handler = new DbSessionHandler();
    session_set_save_handler($handler, true);
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > SESSION_LIFETIME) {
            session_unset();
            session_destroy();
            redirect('?page=login&msg=timeout');
        }
    }
    $_SESSION['last_activity'] = time();
}

/**
 * Cek apakah user sudah login
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Paksa login — redirect ke halaman login jika belum login
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        redirect('?page=login');
    }
}

/**
 * Cek role user — redirect jika role tidak sesuai
 *
 * @param string|array $roles Role yang diizinkan
 */
function requireRole(string|array $roles): void
{
    requireLogin();
    $allowed = is_array($roles) ? $roles : [$roles];
    if (!in_array($_SESSION['user_role'], $allowed, true)) {
        redirect('?page=403');
    }
}

/**
 * Ambil data user yang sedang login dari session
 *
 * @return array
 */
function getCurrentUser(): array
{
    return [
        'id_user'      => $_SESSION['user_id']       ?? 0,
        'nama_lengkap' => $_SESSION['user_nama']     ?? '',
        'username'     => $_SESSION['user_username'] ?? '',
        'role'         => $_SESSION['user_role']     ?? '',
    ];
}

// ══════════════════════════════════════════════════════════════
//  FLASH MESSAGE
// ══════════════════════════════════════════════════════════════

/**
 * Simpan pesan flash ke session
 *
 * @param string $type    Tipe: success | error | warning | info
 * @param string $message Isi pesan
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Ambil dan hapus pesan flash dari session
 *
 * @return array|null
 */
function getFlash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

/**
 * Cek apakah ada flash message
 */
function hasFlash(): bool
{
    return isset($_SESSION['flash']);
}

// ══════════════════════════════════════════════════════════════
//  FORMAT & OUTPUT
// ══════════════════════════════════════════════════════════════

/**
 * Format angka ke format Rupiah
 * Contoh: 15000 → "Rp 15.000"
 *
 * @param int|float $nominal
 * @return string
 */
function formatRupiah(int|float $nominal): string
{
    return 'Rp ' . number_format($nominal, 0, ',', '.');
}

/**
 * Format datetime ke tampilan Indonesia
 * Contoh: "2025-06-01 08:30:00" → "01 Juni 2025, 08:30"
 *
 * @param string|null $datetime
 * @return string
 */
function formatTanggal(?string $datetime, bool $withTime = true): string
{
    if (empty($datetime)) return '-';

    $bulan = [
        1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
        5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
        9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
    ];

    $ts  = strtotime($datetime);
    $tgl = date('d', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);

    return $withTime ? $tgl . ', ' . date('H:i', $ts) : $tgl;
}

/**
 * Format durasi jam ke string yang mudah dibaca
 * Contoh: 1.5 → "1 jam 30 menit"
 *
 * @param float $jam
 * @return string
 */
function formatDurasi(float $jam): string
{
    if ($jam <= 0) return '0 menit';
    $j = (int) floor($jam);
    $m = (int) round(($jam - $j) * 60);
    $hasil = '';
    if ($j > 0) $hasil .= $j . ' jam ';
    if ($m > 0) $hasil .= $m . ' menit';
    return trim($hasil);
}

/**
 * Escape HTML untuk mencegah XSS
 *
 * @param string|null $str
 * @return string
 */
function e(?string $str): string
{
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect ke URL / halaman tertentu
 *
 * @param string $url
 */
function redirect(string $url): never
{
    // Jika URL relatif (tidak diawali http), tambahkan base path
    if (!str_starts_with($url, 'http') && !str_starts_with($url, '/') && !str_starts_with($url, '?')) {
        $url = APP_URL . '/' . $url;
    }
    header('Location: ' . $url);
    exit;
}

// ══════════════════════════════════════════════════════════════
//  BISNIS PARKIR
// ══════════════════════════════════════════════════════════════

/**
 * Generate kode tiket parkir unik
 * Format: PRK-YYYYMMDD-XXXX (contoh: PRK-20250601-0023)
 *
 * @return string
 */
function generateKodeParkir(): string
{
    $db     = getDB();
    $prefix = 'PRK-' . date('Ymd') . '-';

    // Ambil nomor urut terakhir hari ini
    $stmt = $db->prepare(
        "SELECT COUNT(*) FROM tb_transaksi
         WHERE DATE(created_at) = CURDATE()"
    );
    $stmt->execute();
    $urut = (int)$stmt->fetchColumn() + 1;

    return $prefix . str_pad($urut, 4, '0', STR_PAD_LEFT);
}

/**
 * Hitung durasi parkir dalam jam (desimal)
 * Minimal ditagih DURASI_MINIMUM_JAM
 *
 * @param string $waktuMasuk  Format datetime
 * @param string $waktuKeluar Format datetime
 * @return float
 */
function hitungDurasi(string $waktuMasuk, string $waktuKeluar): float
{
    $selisihDetik = strtotime($waktuKeluar) - strtotime($waktuMasuk);
    $jam          = $selisihDetik / 3600;

    // Pembulatan ke atas per jam
    $jamBulat = (int) ceil($jam);

    return max($jamBulat, DURASI_MINIMUM_JAM);
}

/**
 * Hitung total biaya parkir
 *
 * @param float       $durasiJam
 * @param int|float   $tarifPerJam
 * @param int|float   $tarifMasuk   Biaya flat saat masuk
 * @return int
 */
function hitungBiaya(float $durasiJam, int|float $tarifPerJam, int|float $tarifMasuk = 0): int
{
    $biaya = $tarifMasuk + ($durasiJam * $tarifPerJam);
    return (int) $biaya;
}

// ══════════════════════════════════════════════════════════════
//  LOG AKTIVITAS
// ══════════════════════════════════════════════════════════════

/**
 * Catat aktivitas user ke tb_log_aktivitas
 * Dipanggil otomatis di setiap aksi penting
 *
 * @param string $aktivitas Deskripsi aksi (contoh: "Login berhasil")
 * @param string $modul     Nama modul (contoh: "auth", "transaksi")
 */
function logAktivitas(string $aktivitas, string $modul = 'system'): void
{
    if (!isLoggedIn()) return;

    try {
        $db   = getDB();
        $stmt = $db->prepare(
            "INSERT INTO tb_log_aktivitas
             (id_user, aktivitas, modul, ip_address, waktu_aktivitas)
             VALUES (?, ?, ?, ?, NOW())"
        );
        $stmt->execute([
            $_SESSION['user_id'],
            $aktivitas,
            $modul,
            $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
        ]);
    } catch (PDOException) {
        // Log aktivitas tidak boleh menghentikan eksekusi utama
    }
}

// ══════════════════════════════════════════════════════════════
//  VALIDASI
// ══════════════════════════════════════════════════════════════

/**
 * Sanitasi input string
 *
 * @param string|null $input
 * @return string
 */
function sanitize(?string $input): string
{
    return trim(strip_tags((string)$input));
}

/**
 * Validasi format plat nomor Indonesia
 * Contoh valid: AE 1234 AB, B 1234 CD
 *
 * @param string $plat
 * @return bool
 */
function isValidPlat(string $plat): bool
{
    $plat = strtoupper(trim($plat));
    return (bool) preg_match('/^[A-Z]{1,2}\s?\d{1,4}\s?[A-Z]{0,3}$/', $plat);
}

/**
 * Cek apakah area parkir hampir penuh (>= threshold %)
 *
 * @param int $kapasitas
 * @param int $terisi
 * @return bool
 */
function isAreaHampirPenuh(int $kapasitas, int $terisi): bool
{
    if ($kapasitas <= 0) return false;
    return ($terisi / $kapasitas * 100) >= KAPASITAS_NOTIF_PERSEN;
}

/**
 * Persentase ketersediaan area parkir
 *
 * @param int $kapasitas
 * @param int $terisi
 * @return int
 */
function persenTerisi(int $kapasitas, int $terisi): int
{
    if ($kapasitas <= 0) return 0;
    return (int) min(round($terisi / $kapasitas * 100), 100);
}
