<?php
require_once 'config/koneksi.php';
require_once 'core/spk_fungsi.php';
include 'template/header.php';

$data_spk = get_spk_data();
$jumlah_kriteria = count($data_spk['kriteria']);
$jumlah_alternatif = count($data_spk['alternatif']);
$is_data_lengkap = true;

// Cek kelengkapan nilai (setiap alternatif harus punya nilai untuk setiap kriteria)
if ($jumlah_kriteria > 0 && $jumlah_alternatif > 0) {
    foreach ($data_spk['alternatif'] as $alt) {
        foreach ($data_spk['kriteria'] as $crit) {
            if ($data_spk['nilai_matriks'][$alt['id_alternatif']][$crit['id_kriteria']] == 0) {
                $is_data_lengkap = false;
                break 2; // Keluar dari kedua loop
            }
        }
    }
} else {
    $is_data_lengkap = false;
}

?>

<div class="card">
    <div class="card-header">
        Dashboard SPK COPRAS
    </div>
    <div class="text-center" style="margin-bottom: 30px;">
        <h2 style="color: #007bff; margin-bottom: 10px;">Selamat Datang, <?php echo $_SESSION['username']; ?>!</h2>
        <p style="font-size: 1.1rem; color: #555;">Sistem Pendukung Keputusan Pemilihan Kepala Gudang menggunakan Metode COPRAS.</p>
        <p style="font-size: 0.9rem; color: #777;">PT. INOVASI ALCO PANEL</p>
    </div>

    <div class="d-flex justify-content-between">
        <!-- Card Kriteria -->
        <div class="card" style="flex: 1; margin-right: 10px; background-color: #e9f7ef;">
            <div class="card-header" style="border-color: #28a745;">Data Kriteria</div>
            <h1 class="text-center" style="color: #28a745;"><?php echo $jumlah_kriteria; ?></h1>
            <p class="text-center">Kriteria Aktif</p>
            <a href="kriteria.php" class="btn btn-success mt-3 text-center" style="display: block;">Kelola Kriteria</a>
        </div>
        
        <!-- Card Alternatif -->
        <div class="card" style="flex: 1; margin-right: 10px; background-color: #e9f7fd;">
            <div class="card-header" style="border-color: #007bff;">Data Alternatif</div>
            <h1 class="text-center" style="color: #007bff;"><?php echo $jumlah_alternatif; ?></h1>
            <p class="text-center">Kandidat Kepala Gudang</p>
            <a href="alternatif.php" class="btn btn-primary mt-3 text-center" style="display: block;">Kelola Alternatif</a>
        </div>
        
        <!-- Card Hasil -->
        <div class="card" style="flex: 1; background-color: #f7f3e9;">
            <div class="card-header" style="border-color: #ffc107;">Status Perhitungan</div>
            <?php if ($is_data_lengkap && $jumlah_kriteria > 0 && $jumlah_alternatif > 0): ?>
                <h1 class="text-center" style="color: #ffc107;">Siap</h1>
                <p class="text-center">Data lengkap untuk dihitung.</p>
                <a href="hasil.php" class="btn btn-warning mt-3 text-center" style="display: block;">Lihat Hasil COPRAS</a>
            <?php else: ?>
                <h1 class="text-center" style="color: #dc3545;">Belum Lengkap</h1>
                <p class="text-center">Lengkapi Kriteria/Alternatif/Nilai.</p>
                <a href="nilai.php" class="btn btn-danger mt-3 text-center" style="display: block;">Lengkapi Nilai</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'template/footer.php'; ?>
