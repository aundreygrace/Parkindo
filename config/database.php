<?php
/**
 * config/database.php
 * Koneksi database menggunakan PDO + SSL untuk Aiven
 * Support environment variables untuk deployment Vercel
 */

define('DB_HOST',    $_ENV['DB_HOST']    ?? getenv('DB_HOST')    ?? '127.0.0.1');
define('DB_PORT',    $_ENV['DB_PORT']    ?? getenv('DB_PORT')    ?? '3307');
define('DB_NAME',    $_ENV['DB_NAME']    ?? getenv('DB_NAME')    ?? 'db_webparkir');
define('DB_USER',    $_ENV['DB_USER']    ?? getenv('DB_USER')    ?? 'root');
define('DB_PASS',    $_ENV['DB_PASS']    ?? getenv('DB_PASS')    ?? '');
define('DB_SSL_CA',  $_ENV['DB_SSL_CA']  ?? getenv('DB_SSL_CA')  ?? '');
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 10,
        ];

        // Aktifkan SSL jika CA cert tersedia (untuk Aiven di production)
        if (!empty(DB_SSL_CA)) {
            $options[PDO::MYSQL_ATTR_SSL_CA]                  = DB_SSL_CA;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]  = false;
        }

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            http_response_code(500);
            die(json_encode([
                'status'  => 'error',
                'message' => 'Koneksi database gagal. Hubungi administrator.'
            ]));
        }
    }

    return $pdo;
}