<?php
/**
 * config/app.php
 * Konstanta dan konfigurasi global aplikasi
 * Aplikasi Parkir — UKK RPL 2025/2026
 */

// ── Informasi Aplikasi ────────────────────────────────────────
define('APP_NAME',    'Parkindo');
define('APP_TAGLINE', 'Sistem Manajemen Parkir');
define('APP_VERSION', '1.0.0');
define('APP_URL', $_ENV['APP_URL'] ?? getenv('APP_URL') ?? 'http://localhost/parkir');

// ── Pengaturan Session ────────────────────────────────────────
define('SESSION_LIFETIME', 3600);       // 1 jam (detik)
define('SESSION_NAME',     'parkir_sess');

// ── Aturan Bisnis ─────────────────────────────────────────────
define('DURASI_MINIMUM_JAM', 1);        // Minimal 1 jam ditagih
define('KAPASITAS_NOTIF_PERSEN', 80);   // Notif jika area terisi >= 80%

// ── Role Pengguna ─────────────────────────────────────────────
define('ROLE_ADMIN',   'admin');
define('ROLE_PETUGAS', 'petugas');
define('ROLE_OWNER',   'owner');

// ── Path Direktori ────────────────────────────────────────────
define('BASE_PATH',   dirname(__DIR__));
define('VIEW_PATH',   BASE_PATH . '/views');
define('HELPER_PATH', BASE_PATH . '/helpers');

// ── Zona Waktu ────────────────────────────────────────────────
date_default_timezone_set('Asia/Jakarta');

// ── Error Reporting (matikan di production) ───────────────────
$isProduction = !empty($_ENV['APP_URL']) || !empty(getenv('APP_URL'));
ini_set('display_errors', $isProduction ? 0 : 1);
error_reporting($isProduction ? 0 : E_ALL);