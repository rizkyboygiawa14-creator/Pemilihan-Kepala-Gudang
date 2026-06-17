<?php
require_once 'config/koneksi.php';

if (isset($_POST['aksi'])) {
    $aksi = $_POST['aksi'];
    $nama_alternatif = mysqli_real_escape_string($koneksi, $_POST['nama_alternatif']);

    if ($aksi == 'tambah') {
        $query = "INSERT INTO alternatif (nama_alternatif) VALUES ('$nama_alternatif')";
        if (mysqli_query($koneksi, $query)) {
            $_SESSION['pesan'] = ['tipe' => 'success', 'isi' => 'Alternatif berhasil ditambahkan.'];
        } else {
            $_SESSION['pesan'] = ['tipe' => 'danger', 'isi' => 'Gagal menambahkan alternatif: ' . mysqli_error($koneksi)];
        }
    } elseif ($aksi == 'update') {
        $id_alternatif = (int)$_POST['id_alternatif'];
        $query = "UPDATE alternatif SET nama_alternatif='$nama_alternatif' WHERE id_alternatif='$id_alternatif'";
        if (mysqli_query($koneksi, $query)) {
            $_SESSION['pesan'] = ['tipe' => 'success', 'isi' => 'Alternatif berhasil diupdate.'];
        } else {
            $_SESSION['pesan'] = ['tipe' => 'danger', 'isi' => 'Gagal mengupdate alternatif: ' . mysqli_error($koneksi)];
        }
    }
} elseif (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus' && isset($_GET['id'])) {
    $id_alternatif = (int)$_GET['id'];
    
    // Hapus nilai_alternatif yang terkait (DELETE CASCADE)
    mysqli_query($koneksi, "DELETE FROM nilai_alternatif WHERE id_alternatif='$id_alternatif'");
    
    $query = "DELETE FROM alternatif WHERE id_alternatif='$id_alternatif'";
    if (mysqli_query($koneksi, $query)) {
        $_SESSION['pesan'] = ['tipe' => 'success', 'isi' => 'Alternatif berhasil dihapus.'];
    } else {
        $_SESSION['pesan'] = ['tipe' => 'danger', 'isi' => 'Gagal menghapus alternatif: ' . mysqli_error($koneksi)];
    }
}

redirect('alternatif.php');
?>
