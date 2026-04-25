<?php
/**
 * controllers/AuthController.php
 * Menangani proses autentikasi: login dan logout
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/functions.php';

class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Halaman login — GET: tampilkan form, POST: proses login
     */
    public function login(): void
    {
        // Jika sudah login, langsung ke dashboard
        if (isLoggedIn()) {
            redirect('?page=dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processLogin();
            return;
        }

        // GET — tampilkan halaman login
        $flash = getFlash();
        require_once VIEW_PATH . '/auth/login.php';
    }

    /**
     * Proses form login (POST)
     */
    private function processLogin(): void
    {
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validasi input kosong
        if (empty($username) || empty($password)) {
            setFlash('error', 'Username dan password wajib diisi.');
            redirect('?page=login');
        }

        // Cari user di database
        $user = $this->userModel->getByUsername($username);

        // Verifikasi user dan password
        if (!$user || !$this->userModel->verifyPassword($password, $user['password'])) {
            // Log percobaan login gagal (tanpa id_user karena belum login)
            error_log("Login gagal untuk username: {$username} dari IP: " . ($_SERVER['REMOTE_ADDR'] ?? '-'));
            setFlash('error', 'Username atau password salah.');
            redirect('?page=login');
        }

        // Cek status akun
        if (!$user['status_aktif']) {
            setFlash('error', 'Akun Anda dinonaktifkan. Hubungi administrator.');
            redirect('?page=login');
        }

        // Login berhasil — buat session
        session_regenerate_id(true); // Cegah session fixation attack
        $_SESSION['user_id']       = $user['id_user'];
        $_SESSION['user_nama']     = $user['nama_lengkap'];
        $_SESSION['user_username'] = $user['username'];
        $_SESSION['user_role']     = $user['role'];
        $_SESSION['last_activity'] = time();

        // Catat log aktivitas
        logAktivitas('Login ke sistem', 'auth');

        // Redirect sesuai role
        redirect('?page=dashboard');
    }

    /**
     * Proses logout
     */
    public function logout(): void
    {
        if (isLoggedIn()) {
            logAktivitas('Logout dari sistem', 'auth');
        }

        // Hapus semua data session
        session_unset();
        session_destroy();

        // Hapus cookie session
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        setFlash('success', 'Anda berhasil logout.');
        redirect('?page=login');
    }
}