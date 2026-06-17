<?php
require_once 'config/koneksi.php';

if (isset($_POST['simpan_nilai'])) {
    $id_alternatif = (int)$_POST['id_alternatif'];
    $nilai_input = $_POST['nilai']; // Array [id_kriteria => nilai]
    $success_count = 0;
    $error_occurred = false;

    foreach ($nilai_input as $id_kriteria => $nilai) {
        $id_kriteria = (int)$id_kriteria;
        $nilai = (float)$nilai;
        
        // Cek apakah nilai untuk kombinasi ini sudah ada (INSERT or UPDATE / UPSERT)
        $query_cek = "SELECT id_nilai FROM nilai_alternatif WHERE id_alternatif = '$id_alternatif' AND id_kriteria = '$id_kriteria'";
        $result_cek = mysqli_query($koneksi, $query_cek);
        
        if (mysqli_num_rows($result_cek) > 0) {
            // Update jika sudah ada
            $query_aksi = "UPDATE nilai_alternatif SET nilai='$nilai' WHERE id_alternatif = '$id_alternatif' AND id_kriteria = '$id_kriteria'";
        } else {
            // Insert jika belum ada
            $query_aksi = "INSERT INTO nilai_alternatif (id_alternatif, id_kriteria, nilai) VALUES ('$id_alternatif', '$id_kriteria', '$nilai')";
        }
        
        if (mysqli_query($koneksi, $query_aksi)) {
            $success_count++;
        } else {
            $error_occurred = true;
            break; // Hentikan proses jika terjadi error
        }
    }

    if (!$error_occurred) {
        $_SESSION['pesan'] = ['tipe' => 'success', 'isi' => "Berhasil menyimpan **$success_count** nilai kriteria untuk alternatif ini."];
    } else {
        $_SESSION['pesan'] = ['tipe' => 'danger', 'isi' => 'Gagal menyimpan nilai: ' . mysqli_error($koneksi)];
    }
} else {
    $_SESSION['pesan'] = ['tipe' => 'danger', 'isi' => 'Aksi tidak valid.'];
}

redirect('nilai.php?id=' . $id_alternatif);
?>
