<?php
// reset_password.php
// Akses: http://localhost/parkir/reset_password.php
// HAPUS file ini setelah berhasil!

$host = '127.0.0.1:3307';
$db   = 'parkir';
$user = 'root';
$pass = ''; // Laragon default kosong

try {
    $pdo  = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $password    = 'Admin@123';
    $hash        = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("UPDATE tb_user SET password = ?");
    $stmt->execute([$hash]);
    $affected = $stmt->rowCount();

    echo "<h2>✅ Berhasil update $affected akun</h2>";
    echo "<p>Password semua akun sekarang: <strong>Admin@123</strong></p>";
    echo "<p>Hash yang dipakai: <code>$hash</code></p>";
    echo "<hr>";

    // Verifikasi langsung
    $rows = $pdo->query("SELECT username, role, password FROM tb_user")->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' cellpadding='8'>";
    echo "<tr><th>Username</th><th>Role</th><th>Verifikasi Hash</th></tr>";
    foreach ($rows as $row) {
        $ok = password_verify($password, $row['password']) ? '✅ Valid' : '❌ Gagal';
        echo "<tr><td>{$row['username']}</td><td>{$row['role']}</td><td>$ok</td></tr>";
    }
    echo "</table>";
    echo "<br><strong style='color:red'>⚠️ Hapus file ini sekarang!</strong>";

} catch (PDOException $e) {
    echo "<h2>❌ Error koneksi database</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Cek: host, nama database, username, dan password MySQL di baris atas file ini.</p>";
}