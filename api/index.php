<?php
/**
 * index.php
 * Entry point & Front Controller Aplikasi Parkir
 *
 * Semua request masuk ke sini, kemudian diarahkan
 * ke controller yang sesuai berdasarkan parameter ?page=
 */

 require_once __DIR__ . '/../config/app.php';
 require_once __DIR__ . '/../config/database.php';
 require_once __DIR__ . '/../helpers/functions.php';

// Mulai session di setiap request
startSession();

// ── Routing ──────────────────────────────────────────────────
$page   = sanitize($_GET['page'] ?? 'dashboard');
$action = sanitize($_GET['action'] ?? 'index');

// Halaman publik (tidak perlu login)
$publicPages = ['login', 'logout', '403', '404'];

if (!in_array($page, $publicPages, true)) {
    requireLogin();
}

// Map halaman ke controller
$routes = [
    // Auth
    'login'          => ['controller' => 'AuthController',       'method' => 'login'],
    'logout'         => ['controller' => 'AuthController',       'method' => 'logout'],

    // Dashboard (semua role, konten berbeda per role)
    'dashboard'      => ['controller' => 'DashboardController',  'method' => 'index'],

    // Transaksi — Petugas
    'transaksi'      => ['controller' => 'TransaksiController',  'method' => $action],

    // CRUD — Admin
    'user'           => ['controller' => 'AdminController',      'method' => 'user_' . $action],
    'kendaraan'      => ['controller' => 'AdminController',      'method' => 'kendaraan_' . $action],
    'tarif'          => ['controller' => 'AdminController',      'method' => 'tarif_' . $action],
    'area'           => ['controller' => 'AdminController',      'method' => 'area_' . $action],
    'log'            => ['controller' => 'AdminController',      'method' => 'log_index'],

    // Laporan — Owner
    'laporan'        => ['controller' => 'LaporanController',    'method' => $action],

    // Error pages
    '403'            => ['controller' => null, 'view' => 'errors/403'],
    '404'            => ['controller' => null, 'view' => 'errors/404'],
];

// Require semua controller
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/DashboardController.php';
require_once __DIR__ . '/../controllers/TransaksiController.php';
require_once __DIR__ . '/../controllers/AdminController.php';
require_once __DIR__ . '/../controllers/LaporanController.php';

// Dispatch request
if (!isset($routes[$page])) {
    $page = '404';
}

$route = $routes[$page];

if ($route['controller'] !== null) {
    $controllerClass = $route['controller'];
    $method          = $route['method'];
    $controller      = new $controllerClass();

    if (method_exists($controller, $method)) {
        $controller->$method();
    } else {
        // Method tidak ditemukan → 404
        header('Location: ?page=404');
        exit;
    }
} else {
    // Render view error langsung
    require_once VIEW_PATH . '/' . $route['view'] . '.php';
}