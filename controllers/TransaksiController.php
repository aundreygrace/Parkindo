<?php
/**
 * controllers/TransaksiController.php
 *
 * Method yang tersedia (dipanggil via ?page=transaksi&action=X):
 *   index   — daftar semua transaksi + filter + pagination
 *   masuk   — GET: form masuk | POST: proses kendaraan masuk
 *   keluar  — GET: form keluar | POST: proses kendaraan keluar
 *   struk   — tampil struk cetak (print-friendly)
 *   cari    — AJAX: cari kendaraan by plat nomor
 *   detail  — detail satu transaksi
 */

require_once __DIR__ . '/../models/Transaksi.php';
require_once __DIR__ . '/../models/Kendaraan.php';
require_once __DIR__ . '/../models/Tarif.php';
require_once __DIR__ . '/../models/Area.php';
require_once __DIR__ . '/../helpers/functions.php';

class TransaksiController
{
    private Transaksi $transaksiModel;
    private Kendaraan $kendaraanModel;
    private Tarif     $tarifModel;
    private Area      $areaModel;

    public function __construct()
    {
        requireLogin();
        requireRole([ROLE_PETUGAS, ROLE_ADMIN]);

        $this->transaksiModel = new Transaksi();
        $this->kendaraanModel = new Kendaraan();
        $this->tarifModel     = new Tarif();
        $this->areaModel      = new Area();
    }

    // ──────────────────────────────────────────────────────────
    //  INDEX — Tab: Sedang Parkir | Sudah Keluar
    // ──────────────────────────────────────────────────────────
    public function index(): void
    {
        $pageTitle = 'Transaksi Parkir';

        // Tab aktif: masuk (default) | keluar
        $tab    = sanitize($_GET['tab'] ?? 'masuk');
        $search = sanitize($_GET['search']         ?? '');
        $idArea = (int)($_GET['id_area']            ?? 0) ?: null;
        $dari   = sanitize($_GET['tanggal_dari']   ?? '');
        $sampai = sanitize($_GET['tanggal_sampai'] ?? '');

        $filter = [
            'search'         => $search,
            'status'         => ($tab === 'keluar') ? 'keluar' : 'masuk',
            'tanggal_dari'   => $dari,
            'tanggal_sampai' => $sampai,
            'id_area'        => $idArea,
        ];

        $perPage  = 15;
        $halaman  = max(1, (int)($_GET['hal'] ?? 1));
        $offset   = ($halaman - 1) * $perPage;
        $total    = $this->transaksiModel->count($filter);
        $totalHal = (int)ceil($total / $perPage);

        $transaksi = $this->transaksiModel->getAll($filter, $perPage, $offset);
        $areas     = $this->areaModel->getAll();

        // Badge count untuk kedua tab
        $countMasuk  = $this->transaksiModel->countAktif();
        $countKeluar = $this->transaksiModel->count(['status' => 'keluar',
                                                     'tanggal_dari' => date('Y-m-d'),
                                                     'tanggal_sampai' => date('Y-m-d')]);

        ob_start();
        require VIEW_PATH . '/transaksi/index.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layouts/app.php';
    }

    // ──────────────────────────────────────────────────────────
    //  MASUK — Form + proses kendaraan masuk
    // ──────────────────────────────────────────────────────────
    public function masuk(): void
    {
        $pageTitle = 'Kendaraan Masuk';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->prosesMasuk();
            return;
        }

        // GET — tampilkan form
        $areas  = $this->areaModel->getAvailable();
        $tarifs = $this->tarifModel->getAll();

        // Jika ada plat dari URL (dari tombol dashboard), pre-fill
        $platPrefill = strtoupper(sanitize($_GET['plat'] ?? ''));

        ob_start();
        require VIEW_PATH . '/transaksi/masuk.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layouts/app.php';
    }

    private function prosesMasuk(): void
    {
        $plat    = strtoupper(sanitize($_POST['plat_nomor'] ?? ''));
        $idArea  = (int)($_POST['id_area']  ?? 0);
        $catatan = sanitize($_POST['catatan'] ?? '');

        // Validasi input
        if (empty($plat) || $idArea <= 0) {
            setFlash('error', 'Plat nomor dan area parkir wajib diisi.');
            redirect('?page=transaksi&action=masuk');
        }

        // Cari atau daftarkan kendaraan
        $kendaraan = $this->kendaraanModel->getByPlat($plat);

        if (!$kendaraan) {
            // Kendaraan belum terdaftar — daftarkan otomatis
            $jenis = sanitize($_POST['jenis_kendaraan'] ?? 'motor');
            $this->kendaraanModel->create([
                'plat_nomor'      => $plat,
                'jenis_kendaraan' => $jenis,
                'warna'           => sanitize($_POST['warna'] ?? '-'),
                'pemilik'         => sanitize($_POST['pemilik'] ?? 'Umum'),
                'no_hp'           => sanitize($_POST['no_hp'] ?? ''),
                'id_user'         => $_SESSION['user_id'],
            ]);
            $kendaraan = $this->kendaraanModel->getByPlat($plat);
        }

        // Cek apakah kendaraan sedang parkir
        $aktif = $this->transaksiModel->getAktifByKendaraan($kendaraan['id_kendaraan']);
        if ($aktif) {
            setFlash('error', "Kendaraan {$plat} masih tercatat sedang parkir (tiket: {$aktif['kode_parkir']}).");
            redirect('?page=transaksi&action=masuk');
        }

        // Ambil tarif sesuai jenis kendaraan
        $tarif = $this->tarifModel->getByJenis($kendaraan['jenis_kendaraan']);
        if (!$tarif) {
            setFlash('error', 'Tarif untuk jenis kendaraan ini belum dikonfigurasi.');
            redirect('?page=transaksi&action=masuk');
        }

        // Cek kapasitas area
        $area = $this->areaModel->getById($idArea);
        if (!$area || $area['terisi'] >= $area['kapasitas']) {
            setFlash('error', 'Area parkir yang dipilih sudah penuh.');
            redirect('?page=transaksi&action=masuk');
        }

        // Simpan transaksi
        $idParkir = $this->transaksiModel->masuk([
            'id_kendaraan' => $kendaraan['id_kendaraan'],
            'id_tarif'     => $tarif['id_tarif'],
            'id_area'      => $idArea,
            'id_user'      => $_SESSION['user_id'],
        ]);

        if (!$idParkir) {
            setFlash('error', 'Gagal menyimpan transaksi. Coba lagi.');
            redirect('?page=transaksi&action=masuk');
        }

        // Update slot area
        $this->areaModel->tambahTerisi($idArea);

        // Simpan catatan jika ada
        if ($catatan) {
            getDB()->prepare("UPDATE tb_transaksi SET catatan = ? WHERE id_parkir = ?")
                   ->execute([$catatan, $idParkir]);
        }

        logAktivitas("Kendaraan masuk: {$plat}", 'transaksi');
        setFlash('success', "Kendaraan {$plat} berhasil dicatat masuk.");

        // Redirect ke struk
        redirect("?page=transaksi&action=struk&id={$idParkir}&type=masuk");
    }

    // ──────────────────────────────────────────────────────────
    //  KELUAR — Form + proses kendaraan keluar
    // ──────────────────────────────────────────────────────────
    public function keluar(): void
    {
        $pageTitle = 'Kendaraan Keluar';

        // Jika ada id dari URL (dari tombol dashboard), langsung load data
        $idDariUrl = (int)($_GET['id'] ?? 0);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->prosesKeluar();
            return;
        }

        $transaksiAktif = null;
        if ($idDariUrl > 0) {
            $transaksiAktif = $this->transaksiModel->getById($idDariUrl);
            // Pastikan masih berstatus masuk
            if ($transaksiAktif && $transaksiAktif['status'] !== 'masuk') {
                $transaksiAktif = null;
            }
        }

        ob_start();
        require VIEW_PATH . '/transaksi/keluar.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layouts/app.php';
    }

    private function prosesKeluar(): void
    {
        $idParkir = (int)($_POST['id_parkir'] ?? 0);
        $bayar    = (float)str_replace(['.', ','], ['', '.'], $_POST['bayar'] ?? '0');

        if ($idParkir <= 0) {
            setFlash('error', 'Data transaksi tidak valid.');
            redirect('?page=transaksi&action=keluar');
        }

        $transaksi = $this->transaksiModel->getById($idParkir);

        if (!$transaksi || $transaksi['status'] !== 'masuk') {
            setFlash('error', 'Transaksi tidak ditemukan atau sudah diproses keluar.');
            redirect('?page=transaksi&action=keluar');
        }

        // Hitung biaya dulu untuk validasi pembayaran
        $durasi = hitungDurasi($transaksi['waktu_masuk'], date('Y-m-d H:i:s'));
        $biaya  = hitungBiaya($durasi, $transaksi['tarif_per_jam'], $transaksi['tarif_masuk']);

        if ($bayar < $biaya) {
            setFlash('error', 'Pembayaran kurang dari biaya parkir (' . formatRupiah($biaya) . ').');
            redirect("?page=transaksi&action=keluar&id={$idParkir}");
        }

        // Proses keluar
        $hasil = $this->transaksiModel->keluar($idParkir, [
            'tarif_per_jam' => $transaksi['tarif_per_jam'],
            'tarif_masuk'   => $transaksi['tarif_masuk'],
        ], $bayar);

        // Kurangi slot area
        $this->areaModel->kurangiTerisi($transaksi['id_area']);

        logAktivitas("Kendaraan keluar: {$transaksi['plat_nomor']} — " . formatRupiah($hasil['biaya_total']), 'transaksi');
        setFlash('success', "Kendaraan {$transaksi['plat_nomor']} berhasil keluar. Kembalian: " . formatRupiah($hasil['kembalian']));

        redirect("?page=transaksi&action=struk&id={$idParkir}&type=keluar");
    }

    // ──────────────────────────────────────────────────────────
    //  STRUK — Tampil struk (masuk & keluar)
    // ──────────────────────────────────────────────────────────
    public function struk(): void
    {
        $id   = (int)($_GET['id']   ?? 0);
        $type = sanitize($_GET['type'] ?? 'keluar'); // masuk | keluar

        if ($id <= 0) {
            redirect('?page=transaksi&action=index');
        }

        $transaksi = $this->transaksiModel->getById($id);
        if (!$transaksi) {
            setFlash('error', 'Data transaksi tidak ditemukan.');
            redirect('?page=transaksi&action=index');
        }

        // Struk adalah halaman standalone (bisa di-print)
        require VIEW_PATH . '/transaksi/struk.php';
    }

    // ──────────────────────────────────────────────────────────
    //  DETAIL — Detail satu transaksi (dalam layout app)
    // ──────────────────────────────────────────────────────────
    public function detail(): void
    {
        $pageTitle = 'Detail Transaksi';
        $id        = (int)($_GET['id'] ?? 0);

        $transaksi = $id > 0 ? $this->transaksiModel->getById($id) : null;

        if (!$transaksi) {
            setFlash('error', 'Transaksi tidak ditemukan.');
            redirect('?page=transaksi&action=index');
        }

        ob_start();
        require VIEW_PATH . '/transaksi/detail.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layouts/app.php';
    }

    // ──────────────────────────────────────────────────────────
    //  CARI AKTIF — AJAX: ambil transaksi aktif by plat
    // ──────────────────────────────────────────────────────────
    public function cariAktif(): void
    {
        header('Content-Type: application/json');

        $plat = strtoupper(sanitize($_GET['plat'] ?? ''));
        if (empty($plat)) {
            echo json_encode(['found' => false]);
            exit;
        }

        $kendaraan = $this->kendaraanModel->getByPlat($plat);
        if (!$kendaraan) {
            echo json_encode(['found' => false]);
            exit;
        }

        $aktif = $this->transaksiModel->getAktifByKendaraan($kendaraan['id_kendaraan']);
        if (!$aktif) {
            echo json_encode(['found' => false]);
            exit;
        }

        $transaksi = $this->transaksiModel->getById((int)$aktif['id_parkir']);
        echo json_encode(['found' => true, 'transaksi' => $transaksi]);
        exit;
    }

    // ──────────────────────────────────────────────────────────
    //  CARI — AJAX endpoint cari kendaraan by plat
    // ──────────────────────────────────────────────────────────
    public function cari(): void
    {
        header('Content-Type: application/json');

        $plat = strtoupper(sanitize($_GET['plat'] ?? ''));

        if (strlen($plat) < 2) {
            echo json_encode(['found' => false]);
            exit;
        }

        $kendaraan = $this->kendaraanModel->getByPlat($plat);

        if (!$kendaraan) {
            echo json_encode(['found' => false, 'plat' => $plat]);
            exit;
        }

        // Cek apakah sedang parkir
        $aktif = $this->transaksiModel->getAktifByKendaraan($kendaraan['id_kendaraan']);

        echo json_encode([
            'found'           => true,
            'id_kendaraan'    => $kendaraan['id_kendaraan'],
            'plat_nomor'      => $kendaraan['plat_nomor'],
            'jenis_kendaraan' => $kendaraan['jenis_kendaraan'],
            'warna'           => $kendaraan['warna'],
            'pemilik'         => $kendaraan['pemilik'],
            'no_hp'           => $kendaraan['no_hp'] ?? '',
            'sedang_parkir'   => $aktif !== null,
            'kode_aktif'      => $aktif['kode_parkir'] ?? null,
        ]);
        exit;
    }
}