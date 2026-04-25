<?php
/**
 * controllers/AdminController.php
 *
 * Semua operasi CRUD khusus Admin:
 *   user_index    | user_create    | user_store
 *   user_edit     | user_update    | user_toggle
 *
 *   kendaraan_index  | kendaraan_create | kendaraan_store
 *   kendaraan_edit   | kendaraan_update | kendaraan_delete
 *
 *   tarif_index   | tarif_create  | tarif_store
 *   tarif_edit    | tarif_update  | tarif_delete
 *
 *   area_index    | area_create   | area_store
 *   area_edit     | area_update   | area_delete
 *
 *   log_index
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Kendaraan.php';
require_once __DIR__ . '/../models/Tarif.php';
require_once __DIR__ . '/../models/Area.php';
require_once __DIR__ . '/../models/LogAktivitas.php';
require_once __DIR__ . '/../helpers/functions.php';

class AdminController
{
    private User         $userModel;
    private Kendaraan    $kendaraanModel;
    private Tarif        $tarifModel;
    private Area         $areaModel;
    private LogAktivitas $logModel;

    public function __construct()
    {
        requireLogin();
        requireRole(ROLE_ADMIN);

        $this->userModel      = new User();
        $this->kendaraanModel = new Kendaraan();
        $this->tarifModel     = new Tarif();
        $this->areaModel      = new Area();
        $this->logModel       = new LogAktivitas();
    }

    // ══════════════════════════════════════════════════════════
    //  HELPER — render view dalam layout
    // ══════════════════════════════════════════════════════════
    private function render(string $view, array $data = []): void
    {
        extract($data);
        ob_start();
        require VIEW_PATH . "/{$view}.php";
        $content = ob_get_clean();
        require VIEW_PATH . '/layouts/app.php';
    }

    // ══════════════════════════════════════════════════════════
    //  USER
    // ══════════════════════════════════════════════════════════

    public function user_index(): void
    {
        $search = sanitize($_GET['search'] ?? '');
        $role   = sanitize($_GET['role']   ?? '');
        $users  = $this->userModel->getAll($search, $role);

        $this->render('user/index', [
            'pageTitle' => 'Kelola User',
            'users'     => $users,
            'search'    => $search,
            'role'      => $role,
        ]);
    }

    public function user_create(): void
    {
        $this->render('user/form', [
            'pageTitle' => 'Tambah User',
            'user'      => null,
            'mode'      => 'create',
        ]);
    }

    public function user_store(): void
    {
        $data = [
            'nama_lengkap' => sanitize($_POST['nama_lengkap'] ?? ''),
            'username'     => sanitize($_POST['username']     ?? ''),
            'password'     => $_POST['password']              ?? '',
            'role'         => sanitize($_POST['role']         ?? 'petugas'),
        ];

        // Validasi
        if (empty($data['nama_lengkap']) || empty($data['username']) || empty($data['password'])) {
            setFlash('error', 'Semua field wajib diisi.');
            redirect('?page=user&action=create');
        }
        if (strlen($data['password']) < 6) {
            setFlash('error', 'Password minimal 6 karakter.');
            redirect('?page=user&action=create');
        }
        if ($this->userModel->isUsernameTaken($data['username'])) {
            setFlash('error', "Username '{$data['username']}' sudah digunakan.");
            redirect('?page=user&action=create');
        }

        $this->userModel->create($data);
        logAktivitas("Tambah user: {$data['username']} ({$data['role']})", 'user');
        setFlash('success', "User '{$data['username']}' berhasil ditambahkan.");
        redirect('?page=user&action=index');
    }

    public function user_edit(): void
    {
        $id   = (int)($_GET['id'] ?? 0);
        $user = $this->userModel->getById($id);

        if (!$user) {
            setFlash('error', 'User tidak ditemukan.');
            redirect('?page=user&action=index');
        }

        $this->render('user/form', [
            'pageTitle' => 'Edit User',
            'user'      => $user,
            'mode'      => 'edit',
        ]);
    }

    public function user_update(): void
    {
        $id   = (int)($_POST['id_user'] ?? 0);
        $data = [
            'nama_lengkap' => sanitize($_POST['nama_lengkap'] ?? ''),
            'username'     => sanitize($_POST['username']     ?? ''),
            'password'     => $_POST['password']              ?? '',
            'role'         => sanitize($_POST['role']         ?? 'petugas'),
            'status_aktif' => (int)($_POST['status_aktif']   ?? 1),
        ];

        if (empty($data['nama_lengkap']) || empty($data['username'])) {
            setFlash('error', 'Nama dan username wajib diisi.');
            redirect("?page=user&action=edit&id={$id}");
        }
        if (!empty($data['password']) && strlen($data['password']) < 6) {
            setFlash('error', 'Password minimal 6 karakter.');
            redirect("?page=user&action=edit&id={$id}");
        }
        if ($this->userModel->isUsernameTaken($data['username'], $id)) {
            setFlash('error', "Username '{$data['username']}' sudah digunakan.");
            redirect("?page=user&action=edit&id={$id}");
        }
        // Cegah admin nonaktifkan dirinya sendiri
        if ($id === (int)$_SESSION['user_id'] && !$data['status_aktif']) {
            setFlash('error', 'Tidak dapat menonaktifkan akun sendiri.');
            redirect("?page=user&action=edit&id={$id}");
        }

        $this->userModel->update($id, $data);
        logAktivitas("Update user ID {$id}: {$data['username']}", 'user');
        setFlash('success', "User '{$data['username']}' berhasil diperbarui.");
        redirect('?page=user&action=index');
    }

    public function user_toggle(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id === (int)$_SESSION['user_id']) {
            setFlash('error', 'Tidak dapat menonaktifkan akun sendiri.');
            redirect('?page=user&action=index');
        }
        $user = $this->userModel->getById($id);
        if ($user) {
            $this->userModel->toggleStatus($id);
            $status = $user['status_aktif'] ? 'dinonaktifkan' : 'diaktifkan';
            logAktivitas("User {$user['username']} {$status}", 'user');
            setFlash('success', "User '{$user['username']}' berhasil {$status}.");
        }
        redirect('?page=user&action=index');
    }

    public function user_delete(): void
    {
        $id = (int)($_GET['id'] ?? 0);

        // Tidak bisa hapus diri sendiri
        if ($id === (int)$_SESSION['user_id']) {
            setFlash('error', 'Tidak dapat menghapus akun yang sedang digunakan.');
            redirect('?page=user&action=index');
        }

        $user = $this->userModel->getById($id);
        if (!$user) {
            setFlash('error', 'User tidak ditemukan.');
            redirect('?page=user&action=index');
        }

        $hasil = $this->userModel->hardDelete($id);

        if (!$hasil['ok']) {
            setFlash('error', $hasil['reason']);
            redirect('?page=user&action=index');
        }

        logAktivitas("Hapus permanen user: {$user['username']}", 'user');
        setFlash('success', "Akun '{$user['username']}' berhasil dihapus permanen.");
        redirect('?page=user&action=index');
    }

    // ══════════════════════════════════════════════════════════
    //  KENDARAAN
    // ══════════════════════════════════════════════════════════

    public function kendaraan_index(): void
    {
        $search = sanitize($_GET['search'] ?? '');
        $jenis  = sanitize($_GET['jenis']  ?? '');
        $perPage= 15;
        $hal    = max(1, (int)($_GET['hal'] ?? 1));
        $offset = ($hal - 1) * $perPage;
        $total  = $this->kendaraanModel->count($search, $jenis);

        $this->render('kendaraan/index', [
            'pageTitle'   => 'Kelola Kendaraan',
            'kendaraan'   => $this->kendaraanModel->getAll($search, $jenis, $perPage, $offset),
            'search'      => $search,
            'jenis'       => $jenis,
            'total'       => $total,
            'halaman'     => $hal,
            'totalHal'    => (int)ceil($total / $perPage),
            'perPage'     => $perPage,
        ]);
    }

    public function kendaraan_create(): void
    {
        $this->render('kendaraan/form', [
            'pageTitle'  => 'Tambah Kendaraan',
            'kendaraan'  => null,
            'mode'       => 'create',
        ]);
    }

    public function kendaraan_store(): void
    {
        $plat = strtoupper(sanitize($_POST['plat_nomor'] ?? ''));
        $data = [
            'plat_nomor'      => $plat,
            'jenis_kendaraan' => sanitize($_POST['jenis_kendaraan'] ?? 'motor'),
            'warna'           => sanitize($_POST['warna']           ?? ''),
            'pemilik'         => sanitize($_POST['pemilik']         ?? ''),
            'no_hp'           => sanitize($_POST['no_hp']           ?? ''),
            'id_user'         => (int)$_SESSION['user_id'],
        ];

        if (empty($data['plat_nomor']) || empty($data['pemilik'])) {
            setFlash('error', 'Plat nomor dan nama pemilik wajib diisi.');
            redirect('?page=kendaraan&action=create');
        }
        if ($this->kendaraanModel->isPlatTaken($plat)) {
            setFlash('error', "Plat nomor '{$plat}' sudah terdaftar.");
            redirect('?page=kendaraan&action=create');
        }

        $this->kendaraanModel->create($data);
        logAktivitas("Tambah kendaraan: {$plat}", 'kendaraan');
        setFlash('success', "Kendaraan '{$plat}' berhasil didaftarkan.");
        redirect('?page=kendaraan&action=index');
    }

    public function kendaraan_edit(): void
    {
        $id        = (int)($_GET['id'] ?? 0);
        $kendaraan = $this->kendaraanModel->getById($id);

        if (!$kendaraan) {
            setFlash('error', 'Kendaraan tidak ditemukan.');
            redirect('?page=kendaraan&action=index');
        }

        $this->render('kendaraan/form', [
            'pageTitle'  => 'Edit Kendaraan',
            'kendaraan'  => $kendaraan,
            'mode'       => 'edit',
        ]);
    }

    public function kendaraan_update(): void
    {
        $id   = (int)($_POST['id_kendaraan'] ?? 0);
        $plat = strtoupper(sanitize($_POST['plat_nomor'] ?? ''));
        $data = [
            'plat_nomor'      => $plat,
            'jenis_kendaraan' => sanitize($_POST['jenis_kendaraan'] ?? 'motor'),
            'warna'           => sanitize($_POST['warna']           ?? ''),
            'pemilik'         => sanitize($_POST['pemilik']         ?? ''),
            'no_hp'           => sanitize($_POST['no_hp']           ?? ''),
        ];

        if (empty($plat) || empty($data['pemilik'])) {
            setFlash('error', 'Plat nomor dan nama pemilik wajib diisi.');
            redirect("?page=kendaraan&action=edit&id={$id}");
        }
        if ($this->kendaraanModel->isPlatTaken($plat, $id)) {
            setFlash('error', "Plat nomor '{$plat}' sudah digunakan kendaraan lain.");
            redirect("?page=kendaraan&action=edit&id={$id}");
        }

        $this->kendaraanModel->update($id, $data);
        logAktivitas("Update kendaraan: {$plat}", 'kendaraan');
        setFlash('success', "Kendaraan '{$plat}' berhasil diperbarui.");
        redirect('?page=kendaraan&action=index');
    }

    public function kendaraan_delete(): void
    {
        $id        = (int)($_GET['id'] ?? 0);
        $kendaraan = $this->kendaraanModel->getById($id);

        if (!$kendaraan) {
            setFlash('error', 'Kendaraan tidak ditemukan.');
            redirect('?page=kendaraan&action=index');
        }

        $ok = $this->kendaraanModel->delete($id);
        if (!$ok) {
            setFlash('error', "Kendaraan '{$kendaraan['plat_nomor']}' tidak dapat dihapus karena masih tercatat sedang parkir.");
            redirect('?page=kendaraan&action=index');
        }

        logAktivitas("Hapus kendaraan: {$kendaraan['plat_nomor']}", 'kendaraan');
        setFlash('success', "Kendaraan '{$kendaraan['plat_nomor']}' berhasil dihapus.");
        redirect('?page=kendaraan&action=index');
    }

    // ══════════════════════════════════════════════════════════
    //  TARIF
    // ══════════════════════════════════════════════════════════

    public function tarif_index(): void
    {
        $this->render('tarif/index', [
            'pageTitle' => 'Kelola Tarif Parkir',
            'tarifs'    => $this->tarifModel->getAll(),
        ]);
    }

    public function tarif_edit(): void
    {
        $id    = (int)($_GET['id'] ?? 0);
        $tarif = $this->tarifModel->getById($id);

        if (!$tarif) {
            setFlash('error', 'Tarif tidak ditemukan.');
            redirect('?page=tarif&action=index');
        }

        $this->render('tarif/form', [
            'pageTitle' => 'Edit Tarif',
            'tarif'     => $tarif,
        ]);
    }

    public function tarif_update(): void
    {
        $id   = (int)($_POST['id_tarif'] ?? 0);
        $data = [
            'jenis_kendaraan' => sanitize($_POST['jenis_kendaraan'] ?? ''),
            'tarif_per_jam'   => (int)str_replace(['.', ','], '', $_POST['tarif_per_jam'] ?? '0'),
            'tarif_masuk'     => (int)str_replace(['.', ','], '', $_POST['tarif_masuk']   ?? '0'),
            'keterangan'      => sanitize($_POST['keterangan'] ?? ''),
        ];

        if ($data['tarif_per_jam'] <= 0) {
            setFlash('error', 'Tarif per jam harus lebih dari 0.');
            redirect("?page=tarif&action=edit&id={$id}");
        }

        $this->tarifModel->update($id, $data);
        logAktivitas("Update tarif {$data['jenis_kendaraan']}: Rp {$data['tarif_per_jam']}/jam", 'tarif');
        setFlash('success', 'Tarif berhasil diperbarui.');
        redirect('?page=tarif&action=index');
    }

    public function tarif_create(): void
    {
        // Cek jenis yang sudah ada agar tidak ditampilkan di dropdown
        $existing = array_column($this->tarifModel->getAll(), 'jenis_kendaraan');

        $this->render('tarif/form', [
            'pageTitle'      => 'Tambah Tarif',
            'tarif'          => null,
            'mode'           => 'create',
            'existingJenis'  => $existing,
        ]);
    }

    public function tarif_store(): void
    {
        $data = [
            'jenis_kendaraan' => sanitize($_POST['jenis_kendaraan'] ?? ''),
            'tarif_per_jam'   => (int)str_replace(['.', ','], '', $_POST['tarif_per_jam'] ?? '0'),
            'tarif_masuk'     => (int)str_replace(['.', ','], '', $_POST['tarif_masuk']   ?? '0'),
            'keterangan'      => sanitize($_POST['keterangan'] ?? ''),
        ];

        $allowedJenis = ['motor', 'mobil', 'lainnya'];
        if (!in_array($data['jenis_kendaraan'], $allowedJenis)) {
            setFlash('error', 'Jenis kendaraan tidak valid.');
            redirect('?page=tarif&action=create');
        }
        if ($data['tarif_per_jam'] <= 0) {
            setFlash('error', 'Tarif per jam harus lebih dari 0.');
            redirect('?page=tarif&action=create');
        }

        // Cek duplikat jenis
        if ($this->tarifModel->getByJenis($data['jenis_kendaraan'])) {
            setFlash('error', "Tarif untuk jenis '" . ucfirst($data['jenis_kendaraan']) . "' sudah ada. Gunakan menu Edit.");
            redirect('?page=tarif&action=create');
        }

        $this->tarifModel->create($data);
        logAktivitas("Tambah tarif {$data['jenis_kendaraan']}: Rp {$data['tarif_per_jam']}/jam", 'tarif');
        setFlash('success', "Tarif " . ucfirst($data['jenis_kendaraan']) . " berhasil ditambahkan.");
        redirect('?page=tarif&action=index');
    }

    public function tarif_delete(): void
    {
        $id    = (int)($_GET['id'] ?? 0);
        $tarif = $this->tarifModel->getById($id);

        if (!$tarif) {
            setFlash('error', 'Tarif tidak ditemukan.');
            redirect('?page=tarif&action=index');
        }

        // Cegah hapus jika masih ada transaksi aktif menggunakan tarif ini
        if ($this->tarifModel->isUsedInActiveTransaction($id)) {
            setFlash('error', "Tarif " . ucfirst($tarif['jenis_kendaraan']) . " tidak dapat dihapus karena masih digunakan pada transaksi yang sedang berjalan.");
            redirect('?page=tarif&action=index');
        }

        $this->tarifModel->delete($id);
        logAktivitas("Hapus tarif: {$tarif['jenis_kendaraan']}", 'tarif');
        setFlash('success', "Tarif " . ucfirst($tarif['jenis_kendaraan']) . " berhasil dihapus.");
        redirect('?page=tarif&action=index');
    }

    // ══════════════════════════════════════════════════════════
    //  AREA PARKIR
    // ══════════════════════════════════════════════════════════

    public function area_index(): void
    {
        $this->render('area/index', [
            'pageTitle' => 'Kelola Area Parkir',
            'areas'     => $this->areaModel->getAll(),
        ]);
    }

    public function area_create(): void
    {
        $this->render('area/form', [
            'pageTitle' => 'Tambah Area Parkir',
            'area'      => null,
            'mode'      => 'create',
        ]);
    }

    public function area_store(): void
    {
        $data = [
            'nama_area'  => sanitize($_POST['nama_area']  ?? ''),
            'kapasitas'  => (int)($_POST['kapasitas']     ?? 0),
            'keterangan' => sanitize($_POST['keterangan'] ?? ''),
        ];

        if (empty($data['nama_area']) || $data['kapasitas'] <= 0) {
            setFlash('error', 'Nama area dan kapasitas (> 0) wajib diisi.');
            redirect('?page=area&action=create');
        }

        $this->areaModel->create($data);
        logAktivitas("Tambah area: {$data['nama_area']} (kapasitas {$data['kapasitas']})", 'area');
        setFlash('success', "Area '{$data['nama_area']}' berhasil ditambahkan.");
        redirect('?page=area&action=index');
    }

    public function area_edit(): void
    {
        $id   = (int)($_GET['id'] ?? 0);
        $area = $this->areaModel->getById($id);

        if (!$area) {
            setFlash('error', 'Area tidak ditemukan.');
            redirect('?page=area&action=index');
        }

        $this->render('area/form', [
            'pageTitle' => 'Edit Area Parkir',
            'area'      => $area,
            'mode'      => 'edit',
        ]);
    }

    public function area_update(): void
    {
        $id   = (int)($_POST['id_area'] ?? 0);
        $data = [
            'nama_area'  => sanitize($_POST['nama_area']  ?? ''),
            'kapasitas'  => (int)($_POST['kapasitas']     ?? 0),
            'keterangan' => sanitize($_POST['keterangan'] ?? ''),
        ];

        if (empty($data['nama_area']) || $data['kapasitas'] <= 0) {
            setFlash('error', 'Nama area dan kapasitas (> 0) wajib diisi.');
            redirect("?page=area&action=edit&id={$id}");
        }

        // Kapasitas baru tidak boleh kurang dari jumlah yang sudah terisi
        $area = $this->areaModel->getById($id);
        if ($area && $data['kapasitas'] < $area['terisi']) {
            setFlash('error', "Kapasitas tidak boleh kurang dari slot yang sudah terisi ({$area['terisi']}).");
            redirect("?page=area&action=edit&id={$id}");
        }

        $this->areaModel->update($id, $data);
        logAktivitas("Update area ID {$id}: {$data['nama_area']}", 'area');
        setFlash('success', "Area '{$data['nama_area']}' berhasil diperbarui.");
        redirect('?page=area&action=index');
    }

    public function area_delete(): void
    {
        $id   = (int)($_GET['id'] ?? 0);
        $area = $this->areaModel->getById($id);

        if (!$area) {
            setFlash('error', 'Area tidak ditemukan.');
            redirect('?page=area&action=index');
        }

        $ok = $this->areaModel->delete($id);
        if (!$ok) {
            setFlash('error', "Area '{$area['nama_area']}' tidak dapat dihapus karena masih ada kendaraan parkir di sana.");
            redirect('?page=area&action=index');
        }

        logAktivitas("Hapus area: {$area['nama_area']}", 'area');
        setFlash('success', "Area '{$area['nama_area']}' berhasil dihapus.");
        redirect('?page=area&action=index');
    }

    // ══════════════════════════════════════════════════════════
    //  LOG AKTIVITAS
    // ══════════════════════════════════════════════════════════

    public function log_index(): void
    {
        $filter = [
            'search'         => sanitize($_GET['search']         ?? ''),
            'modul'          => sanitize($_GET['modul']          ?? ''),
            'tanggal_dari'   => sanitize($_GET['tanggal_dari']   ?? ''),
            'tanggal_sampai' => sanitize($_GET['tanggal_sampai'] ?? ''),
        ];

        $perPage  = 20;
        $hal      = max(1, (int)($_GET['hal'] ?? 1));
        $offset   = ($hal - 1) * $perPage;
        $total    = $this->logModel->count($filter);

        $this->render('log/index', [
            'pageTitle' => 'Log Aktivitas',
            'logs'      => $this->logModel->getAll($filter, $perPage, $offset),
            'moduls'    => $this->logModel->getModuls(),
            'filter'    => $filter,
            'total'     => $total,
            'halaman'   => $hal,
            'totalHal'  => (int)ceil($total / $perPage),
        ]);
    }
}