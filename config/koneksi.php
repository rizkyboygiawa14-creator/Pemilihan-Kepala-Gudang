<?php
// Konfigurasi koneksi ke database MySQL
$host = "localhost";
$user = "root"; // Sesuaikan dengan user MySQL Anda
$pass = ""; // Sesuaikan dengan password MySQL Anda
$db = "db_copras"; // Nama database yang kita buat tadi

$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set timezone untuk fungsi waktu
date_default_timezone_set('Asia/Jakarta');

// Start session untuk manajemen login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fungsi redirect untuk keamanan dan kemudahan
function redirect($url) {
    header("Location: " . $url);
    exit();
}
?>
