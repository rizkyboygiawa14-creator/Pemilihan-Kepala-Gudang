<?php
require_once 'config/koneksi.php';
require_once 'core/spk_fungsi.php';
include 'template/header.php';

// Pastikan ID Alternatif ada di URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['pesan'] = ['tipe' => 'danger', 'isi' => 'ID Alternatif tidak ditemukan.'];
    redirect('alternatif.php');
}

$id_alternatif = (int)$_GET['id'];

// Ambil data Alternatif
$query_alt = "SELECT nama_alternatif FROM alternatif WHERE id_alternatif = '$id_alternatif'";
$result_alt = mysqli_query($koneksi, $query_alt);
$data_alt = mysqli_fetch_assoc($result_alt);

if (!$data_alt) {
    $_SESSION['pesan'] = ['tipe' => 'danger', 'isi' => 'Alternatif tidak valid.'];
    redirect('alternatif.php');
}

$nama_alternatif = $data_alt['nama_alternatif'];

// Ambil semua data Kriteria
$query_crit = "SELECT * FROM kriteria ORDER BY id_kriteria ASC";
$result_crit = mysqli_query($koneksi, $query_crit);

// Cek jika tidak ada kriteria
if (mysqli_num_rows($result_crit) == 0) {
    echo '<div class="card"><div class="alert alert-warning">Belum ada Kriteria. Harap tambahkan Kriteria terlebih dahulu.</div></div>';
    include 'template/footer.php';
    exit();
}
?>

<div class="card">
    <div class="card-header">
        Input Nilai Alternatif - **<?php echo htmlspecialchars($nama_alternatif); ?>**
    </div>
    
    <p class="mb-3" style="color: #6c757d;">Masukkan nilai (rating kecocokan) untuk setiap kriteria. Nilai disarankan dalam skala 1-100 atau skala rating 1-5.</p>

    <form action="nilai_aksi.php" method="POST">
        <input type="hidden" name="id_alternatif" value="<?php echo $id_alternatif; ?>">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 35%;">Kriteria</th>
                    <th style="width: 15%;">Jenis</th>
                    <th style="width: 15%;">Bobot (Wj)</th>
                    <th style="width: 35%;">Nilai (Rating)</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($crit = mysqli_fetch_assoc($result_crit)): 
                    $id_kriteria = $crit['id_kriteria'];
                    // Ambil nilai yang sudah tersimpan
                    $nilai_sekarang = get_nilai_alternatif($id_alternatif, $id_kriteria);
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($crit['nama_kriteria']); ?></td>
                        <td><?php echo ucfirst($crit['jenis']); ?></td>
                        <td><?php echo number_format($crit['bobot'], 2); ?></td>
                        <td>
                            <div class="form-group">
                                <input type="number" step="0.1" min="0" name="nilai[<?php echo $id_kriteria; ?>]" 
                                       class="form-control" required placeholder="Masukkan Nilai..." 
                                       value="<?php echo htmlspecialchars($nilai_sekarang); ?>">
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <button type="submit" name="simpan_nilai" class="btn btn-primary">Simpan Nilai</button>
        <a href="alternatif.php" class="btn btn-warning">Kembali ke Daftar Alternatif</a>
    </form>
</div>

<?php include 'template/footer.php'; ?>
