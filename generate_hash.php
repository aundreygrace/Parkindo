<?php
/**
 * generate_hash.php
 * Jalankan SEKALI di browser: http://localhost/parkir/generate_hash.php
 * Lalu HAPUS file ini setelah selesai!
 */

$password = 'Admin@123';
$hash     = password_hash($password, PASSWORD_BCRYPT);

echo "<pre>";
echo "Password : {$password}\n";
echo "Hash     : {$hash}\n\n";
echo "-- Salin query ini ke phpMyAdmin:\n\n";
echo "USE parkir;\n";
echo "UPDATE tb_user SET password = '{$hash}' WHERE 1=1;\n";
echo "</pre>";
echo "<hr>";
echo "<strong>Verifikasi:</strong> ";
echo password_verify($password, $hash) ? "✅ Hash valid" : "❌ Hash tidak valid";