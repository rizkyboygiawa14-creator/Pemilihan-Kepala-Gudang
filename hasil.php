<?php
require_once 'config/koneksi.php';
require_once 'core/spk_fungsi.php';
include 'template/header.php';

$data_spk = get_spk_data();

// Cek kelengkapan data sebelum dihitung
if (count($data_spk['kriteria']) < 1 || count($data_spk['alternatif']) < 1) {
    echo '<div class="card"><div class="alert alert-danger">Data Kriteria atau Alternatif belum lengkap.</div></div>';
    include 'template/footer.php';
    exit();
}

// Cek apakah semua nilai sudah terisi
$is_data_lengkap = true;
foreach ($data_spk['alternatif'] as $alt) {
    foreach ($data_spk['kriteria'] as $crit) {
        if ($data_spk['nilai_matriks'][$alt['id_alternatif']][$crit['id_kriteria']] == 0) {
            $is_data_lengkap = false;
            break 2;
        }
    }
}

// Cek Total Bobot Kriteria
$total_bobot = round($data_spk['total_bobot'], 2);
if ($total_bobot != 1.00) {
    echo '<div class="card"><div class="alert alert-danger">VALIDASI GAGAL! Total Bobot Kriteria harus 1.00. Saat ini: ' . $total_bobot . '. Harap sesuaikan di menu Kriteria.</div></div>';
    include 'template/footer.php';
    exit();
}


if (!$is_data_lengkap) {
    echo '<div class="card"><div class="alert alert-warning">Data nilai alternatif belum lengkap. Harap lengkapi semua nilai di menu Input Nilai.</div></div>';
    include 'template/footer.php';
    exit();
}

// Lakukan Perhitungan COPRAS
$hasil_copras = hitung_copras($data_spk);

$alternatif = $data_spk['alternatif'];
$kriteria = $data_spk['kriteria'];
?>

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <span>Hasil Perhitungan Metode COPRAS</span>
        <div class="btn-group">
            <button onclick="window.print()" class="btn btn-success">Cetak Hasil (PDF)</button>
        </div>
    </div>

    <!-- Tampilan Ranking Akhir -->
    <h3 class="mt-3" style="color: #007bff;">1. Ranking Akhir (Ui)</h3>
    <table class="table">
        <thead>
            <tr>
                <th style="width: 5%;">Rank</th>
                <th>Alternatif (Kandidat)</th>
                <th style="width: 15%;">S+i (Benefit)</th>
                <th style="width: 15%;">S-i (Cost)</th>
                <th style="width: 15%;">Qi (Bobot Relatif)</th>
                <th style="width: 15%;">Ui (Utilitas %)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($hasil_copras['hasil_ranking'] as $i => $data_rank): ?>
            <tr style="<?php echo ($i == 0) ? 'font-weight: bold; background-color: #fff3cd;' : ''; ?>">
                <td class="text-center"><?php echo $i + 1; ?></td>
                <td><?php echo $data_rank['nama_alternatif']; ?></td>
                <td><?php echo round_format($data_rank['S_plus']); ?></td>
                <td><?php echo round_format($data_rank['S_minus']); ?></td>
                <td><?php echo round_format($data_rank['Qi']); ?></td>
                <td style="color: <?php echo ($i == 0) ? '#dc3545' : '#007bff'; ?>;"><?php echo round_format($data_rank['Ui']); ?>%</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="alert alert-success mt-3">
        **REKOMENDASI TERBAIK:** Kandidat **<?php echo $hasil_copras['hasil_ranking'][0]['nama_alternatif']; ?>** terpilih sebagai Kepala Gudang dengan nilai utilitas tertinggi: **<?php echo round_format($hasil_copras['hasil_ranking'][0]['Ui']); ?>%**.
    </div>
    
    <!-- Matriks Keputusan Awal -->
    <h3 class="mt-4">2. Matriks Keputusan Awal (Xij)</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Alternatif</th>
                <?php foreach ($kriteria as $crit): ?>
                    <th class="text-center"><?php echo $crit['nama_kriteria']; ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($alternatif as $alt): ?>
            <tr>
                <td><?php echo $alt['nama_alternatif']; ?></td>
                <?php foreach ($kriteria as $crit): ?>
                    <td class="text-center"><?php echo round_format($hasil_copras['matriks_X'][$alt['id_alternatif']][$crit['id_kriteria']]); ?></td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Matriks Normalisasi (Xij) -->
    <h3 class="mt-4">3. Matriks Normalisasi (Xij) - Langkah 2 COPRAS</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Alternatif</th>
                <?php foreach ($kriteria as $crit): ?>
                    <th class="text-center"><?php echo $crit['nama_kriteria']; ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($alternatif as $alt): ?>
            <tr>
                <td><?php echo $alt['nama_alternatif']; ?></td>
                <?php foreach ($kriteria as $crit): ?>
                    <td class="text-center">
                        <?php echo round_format($hasil_copras['matriks_normalisasi'][$alt['id_alternatif']][$crit['id_kriteria']]); ?>
                    </td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <!-- Matriks Normalisasi Berbobot -->
    <h3 class="mt-4">4. Matriks Normalisasi Berbobot (Dij) - Langkah 3 COPRAS</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Alternatif</th>
                <?php foreach ($kriteria as $crit): ?>
                    <?php 
                        // Warna header berdasarkan jenis kriteria
                        $header_style = ($crit['jenis'] == 'benefit') ? 'background-color: #d4edda;' : 'background-color: #f8d7da;'; 
                    ?>
                    <th class="text-center" style="<?php echo $header_style; ?>">
                        <?php echo $crit['nama_kriteria']; ?><br>(W: <?php echo number_format($crit['bobot'], 2); ?> | T: <?php echo $crit['jenis']; ?>)
                    </th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($alternatif as $alt): ?>
            <tr>
                <td><?php echo $alt['nama_alternatif']; ?></td>
                <?php foreach ($kriteria as $crit): ?>
                    <?php 
                        // Warna sel berdasarkan jenis kriteria
                        $cell_style = ($crit['jenis'] == 'benefit') ? 'color: #155724;' : 'color: #721c24;'; 
                    ?>
                    <td class="text-center" style="<?php echo $cell_style; ?>">
                        <?php echo round_format($hasil_copras['matriks_berbobot'][$alt['id_alternatif']][$crit['id_kriteria']]); ?>
                    </td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>

<?php include 'template/footer.php'; ?>
