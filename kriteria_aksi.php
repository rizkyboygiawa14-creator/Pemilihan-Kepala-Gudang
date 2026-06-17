<?php
require_once 'config/koneksi.php';

if (isset($_POST['aksi'])) {
    $aksi = $_POST['aksi'];
    $nama_kriteria = mysqli_real_escape_string($koneksi, $_POST['nama_kriteria']);
    $bobot = (float)$_POST['bobot'];
    $jenis = mysqli_real_escape_string($koneksi, $_POST['jenis']);

    if ($aksi == 'tambah') {
        $query = "INSERT INTO kriteria (nama_kriteria, bobot, jenis) VALUES ('$nama_kriteria', '$bobot', '$jenis')";
        if (mysqli_query($koneksi, $query)) {
            $_SESSION['pesan'] = ['tipe' => 'success', 'isi' => 'Kriteria berhasil ditambahkan.'];
        } else {
            $_SESSION['pesan'] = ['tipe' => 'danger', 'isi' => 'Gagal menambahkan kriteria: ' . mysqli_error($koneksi)];
        }
    } elseif ($aksi == 'update') {
        $id_kriteria = (int)$_POST['id_kriteria'];
        $query = "UPDATE kriteria SET nama_kriteria='$nama_kriteria', bobot='$bobot', jenis='$jenis' WHERE id_kriteria='$id_kriteria'";
        if (mysqli_query($koneksi, $query)) {
            $_SESSION['pesan'] = ['tipe' => 'success', 'isi' => 'Kriteria berhasil diupdate.'];
        } else {
            $_SESSION['pesan'] = ['tipe' => 'danger', 'isi' => 'Gagal mengupdate kriteria: ' . mysqli_error($koneksi)];
        }
    }
} elseif (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus' && isset($_GET['id'])) {
    $id_kriteria = (int)$_GET['id'];
    
    // Perintah DELETE CASCADE seharusnya sudah ada di MySQL, tapi kita pastikan.
    // Hapus nilai_alternatif yang terkait
    mysqli_query($koneksi, "DELETE FROM nilai_alternatif WHERE id_kriteria='$id_kriteria'");

    $query = "DELETE FROM kriteria WHERE id_kriteria='$id_kriteria'";
    if (mysqli_query($koneksi, $query)) {
        $_SESSION['pesan'] = ['tipe' => 'success', 'isi' => 'Kriteria berhasil dihapus.'];
    } else {
        $_SESSION['pesan'] = ['tipe' => 'danger', 'isi' => 'Gagal menghapus kriteria: ' . mysqli_error($koneksi)];
    }
}

redirect('kriteria.php');
?>
