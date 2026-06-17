<?php
session_start();
require_once 'config/koneksi.php';

// Pastikan fungsi redirect tersedia
if (!function_exists('redirect')) {
    function redirect($url) {
        header("Location: $url");
        exit();
    }
}

// Cek status login di semua halaman (kecuali login.php)
if (!isset($_SESSION['is_login']) && basename($_SERVER['PHP_SELF']) != 'login.php') {
    $_SESSION['pesan'] = ['tipe' => 'danger', 'isi' => 'Anda harus login untuk mengakses halaman ini.'];
    redirect('login.php');
}

// Menentukan halaman aktif untuk sidebar
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPK COPRAS - PT. Inovasi Alco Panel</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Menggunakan ikon FontAwesome untuk efek visual -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="container-nav">
        <a href="index.php" class="brand">
            <i class="fas fa-cubes"></i> SPK COPRAS
        </a>
        <div class="nav-links">
            <?php if (isset($_SESSION['is_login'])): ?>
                <span style="margin-right: 20px;">Halo, <?php echo $_SESSION['username']; ?></span>
                <a href="login.php?aksi=logout" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<?php if (isset($_SESSION['is_login'])): ?>
<!-- Struktur Konten Utama (Sidebar + Main Content) -->
<div class="container-app">
    <!-- Sidebar -->
    <aside class="sidebar">
        <ul class="sidebar-menu">
            <li><a href="index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="kriteria.php" class="<?php echo ($current_page == 'kriteria.php') ? 'active' : ''; ?>"><i class="fas fa-clipboard-list"></i> Kelola Kriteria</a></li>
            <li><a href="alternatif.php" class="<?php echo ($current_page == 'alternatif.php' || $current_page == 'nilai.php') ? 'active' : ''; ?>"><i class="fas fa-users"></i> Kelola Alternatif</a></li>
            <li><a href="hasil.php" class="<?php echo ($current_page == 'hasil.php') ? 'active' : ''; ?>"><i class="fas fa-chart-line"></i> Hasil Perhitungan</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <?php 
        // Tampilkan pesan success/error/warning
        if (isset($_SESSION['pesan'])): 
        ?>
            <div class="alert alert-<?php echo $_SESSION['pesan']['tipe']; ?> mb-3">
                <?php echo $_SESSION['pesan']['isi']; ?>
            </div>
        <?php 
            unset($_SESSION['pesan']); 
        endif; 
        ?>
<?php else: ?>
<!-- Jika tidak login, tidak ada sidebar. Konten dimulai di sini. -->
<main class="main-content">
<?php endif; ?>
