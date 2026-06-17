<?php
require_once 'config/koneksi.php';

// Logika Logout
if (isset($_GET['aksi']) && $_GET['aksi'] == 'logout') {
    session_destroy();
    redirect('login.php');
}

// Logika Login
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = SHA1($_POST['password']); 

    $query = "SELECT * FROM user WHERE username='$username' AND password='$password'";
    $result = mysqli_query($koneksi, $query);
    $data_user = mysqli_fetch_assoc($result);

    if ($data_user) {
        // Login Berhasil
        $_SESSION['is_login'] = true;
        $_SESSION['id_user'] = $data_user['id_user'];
        $_SESSION['username'] = $data_user['username'];
        $_SESSION['pesan'] = ['tipe' => 'success', 'isi' => 'Selamat datang, ' . $data_user['nama_lengkap'] . '!'];
        redirect('index.php');
    } else {
        // Login Gagal
        $_SESSION['pesan'] = ['tipe' => 'danger', 'isi' => 'Username atau Password salah.'];
        redirect('login.php');
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SPK COPRAS</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Menggunakan ikon FontAwesome untuk efek visual -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<div class="login-page">
    <div class="login-card">
        <div class="login-header">
            <i class="fas fa-warehouse login-icon"></i> <!-- Ikon Gudang -->
            <h2>Login SPK COPRAS</h2>
            <p>Pemilihan Kepala Gudang | PT. Inovasi Alco Panel</p>
        </div>

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

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan Username" required>
            </div>
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan Password" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary mt-3">MASUK KE SISTEM</button>
        </form>
        <p class="mt-3 text-center" style="font-size: 0.85rem; color: #aaa;">Default: Username: **admin**, Password: **admin123**</p>
    </div>
</div>

<script>
    // Sederhana: hapus pesan alert setelah beberapa detik
    const alertDiv = document.querySelector('.alert');
    if (alertDiv) {
        setTimeout(() => {
            alertDiv.remove();
        }, 5000); // Hapus setelah 5 detik
    }
</script>

</body>
</html>
