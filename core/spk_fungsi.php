<?php
// File ini berisi fungsi-fungsi inti untuk perhitungan SPK COPRAS
require_once 'config/koneksi.php';

/**
 * Mengambil semua data yang dibutuhkan untuk perhitungan COPRAS.
 * @return array Berisi data alternatif, kriteria, dan nilai.
 */
function get_spk_data() {
    global $koneksi;

    // 1. Ambil data Kriteria (Cj)
    $query_kriteria = "SELECT id_kriteria, nama_kriteria, bobot, jenis FROM kriteria ORDER BY id_kriteria";
    $result_kriteria = mysqli_query($koneksi, $query_kriteria);
    $kriteria = [];
    $total_bobot = 0; // Tambahkan inisialisasi total bobot
    while ($row = mysqli_fetch_assoc($result_kriteria)) {
        $kriteria[] = $row;
        $total_bobot += (float)$row['bobot']; // Hitung total bobot
    }

    // 2. Ambil data Alternatif (Ai)
    $query_alternatif = "SELECT id_alternatif, nama_alternatif FROM alternatif ORDER BY id_alternatif";
    $result_alternatif = mysqli_query($koneksi, $query_alternatif);
    $alternatif = [];
    while ($row = mysqli_fetch_assoc($result_alternatif)) {
        $alternatif[] = $row;
    }

    // 3. Ambil data Nilai Matriks Keputusan (Xij)
    // Map nilai berdasarkan id_alternatif dan id_kriteria
    $nilai_matriks = [];
    foreach ($alternatif as $alt) {
        $id_alt = $alt['id_alternatif'];
        $nilai_matriks[$id_alt] = [];
        foreach ($kriteria as $crit) {
            $id_crit = $crit['id_kriteria'];
            $query_nilai = "SELECT nilai FROM nilai_alternatif WHERE id_alternatif = '$id_alt' AND id_kriteria = '$id_crit'";
            $result_nilai = mysqli_query($koneksi, $query_nilai);
            $nilai_row = mysqli_fetch_assoc($result_nilai);
            
            // Simpan nilai atau 0 jika tidak ada 
            $nilai_matriks[$id_alt][$id_crit] = $nilai_row ? (float)$nilai_row['nilai'] : 0;
        }
    }

    return [
        'alternatif' => $alternatif,
        'kriteria' => $kriteria,
        'nilai_matriks' => $nilai_matriks,
        'total_bobot' => $total_bobot // Kembalikan total bobot
    ];
}

/**
 * Melakukan semua langkah perhitungan COPRAS.
 * @param array $data Data SPK dari get_spk_data().
 * @return array Hasil peringkat dan semua matriks langkah.
 */
function hitung_copras($data) {
    $alternatif = $data['alternatif'];
    $kriteria = $data['kriteria'];
    $matriks_X = $data['nilai_matriks'];
    $jumlah_alt = count($alternatif);
    $jumlah_crit = count($kriteria);

    // Pastikan data cukup
    if ($jumlah_alt == 0 || $jumlah_crit == 0) {
        return ['error' => 'Data Kriteria atau Alternatif belum lengkap.'];
    }

    // ===============================================
    // LANGKAH 1: Matriks Keputusan X (sudah ada di $matriks_X)
    // ===============================================

    // ===============================================
    // LANGKAH 2: Matriks Normalisasi Xij
    // Xij = Xij / SUM(Xij) per kolom (kriteria)
    // ===============================================
    $matriks_normalisasi = [];
    $sum_per_kriteria = [];

    // Hitung total nilai per kriteria
    foreach ($kriteria as $crit) {
        $id_crit = $crit['id_kriteria'];
        $sum_per_kriteria[$id_crit] = 0;
        foreach ($alternatif as $alt) {
            $sum_per_kriteria[$id_crit] += $matriks_X[$alt['id_alternatif']][$id_crit];
        }
    }

    // Lakukan normalisasi
    foreach ($alternatif as $alt) {
        $id_alt = $alt['id_alternatif'];
        foreach ($kriteria as $crit) {
            $id_crit = $crit['id_kriteria'];
            $nilai_X = $matriks_X[$id_alt][$id_crit];
            
            // Handle pembagian dengan nol jika total kriteria 0
            if ($sum_per_kriteria[$id_crit] > 0) {
                $matriks_normalisasi[$id_alt][$id_crit] = $nilai_X / $sum_per_kriteria[$id_crit];
            } else {
                $matriks_normalisasi[$id_alt][$id_crit] = 0; 
            }
        }
    }

    // ===============================================
    // LANGKAH 3: Matriks Normalisasi Berbobot Dij
    // Dij = Xij * Wj
    // ===============================================
    $matriks_berbobot = [];
    foreach ($alternatif as $alt) {
        $id_alt = $alt['id_alternatif'];
        foreach ($kriteria as $crit) {
            $id_crit = $crit['id_kriteria'];
            $bobot = $crit['bobot'];
            $matriks_berbobot[$id_alt][$id_crit] = $matriks_normalisasi[$id_alt][$id_crit] * $bobot;
        }
    }

    // ===============================================
    // LANGKAH 4: Menghitung S+i (Benefit) dan S-i (Cost)
    // ===============================================
    $S_plus = [];
    $S_minus = [];
    $sum_S_minus = 0; // Total dari semua S-i
    $sum_S_minus_inverse = 0; // Total dari 1/S-i
    $S_minus_min = PHP_FLOAT_MAX; // Nilai S-i minimum

    foreach ($alternatif as $alt) {
        $id_alt = $alt['id_alternatif'];
        $S_plus[$id_alt] = 0;
        $S_minus[$id_alt] = 0;

        foreach ($kriteria as $crit) {
            $id_crit = $crit['id_kriteria'];
            $nilai_Dij = $matriks_berbobot[$id_alt][$id_crit];

            if ($crit['jenis'] == 'benefit') {
                $S_plus[$id_alt] += $nilai_Dij;
            } else { // cost
                $S_minus[$id_alt] += $nilai_Dij;
            }
        }
        
        // Cek S-i minimum
        if ($S_minus[$id_alt] > 0 && $S_minus[$id_alt] < $S_minus_min) {
            $S_minus_min = $S_minus[$id_alt];
        }
    }

    // Hitung total S-i dan total 1/S-i
    foreach ($alternatif as $alt) {
        $id_alt = $alt['id_alternatif'];
        $sum_S_minus += $S_minus[$id_alt];
        
        // Pastikan S-i tidak nol sebelum invers
        if ($S_minus[$id_alt] > 0) {
            $sum_S_minus_inverse += (1 / $S_minus[$id_alt]);
        }
    }
    
    // Penanganan kasus jika tidak ada kriteria cost atau S-i nol (untuk mencegah Div/0)
    if ($sum_S_minus_inverse == 0) {
        $sum_S_minus_inverse = 1; 
        $sum_S_minus = 1;
        // Ini hanya untuk mencegah error, Qi akan didominasi S+
    }
    
    // ===============================================
    // LANGKAH 5: Menghitung Bobot Relatif Qi
    // Qi = S+i + ( SUM(S-i) / (S-i * SUM(1/S-i)) )
    // ===============================================
    
    $matriks_Q = [];
    $Q_max = 0;
    
    foreach ($alternatif as $alt) {
        $id_alt = $alt['id_alternatif'];
        $S_i_plus = $S_plus[$id_alt];
        $S_i_minus = $S_minus[$id_alt];

        $Q_i = $S_i_plus;
        
        if ($S_i_minus > 0) {
            // Rumus COPRAS Standar (versi yang aman dari dokumen):
            $S_minus_term = $sum_S_minus / ($S_i_minus * $sum_S_minus_inverse);
            $Q_i = $S_i_plus + $S_minus_term;
        }

        $matriks_Q[$id_alt] = $Q_i;
        if ($Q_i > $Q_max) {
            $Q_max = $Q_i;
        }
    }

    // ===============================================
    // LANGKAH 6: Menghitung Utilitas Kuantitatif Ui
    // Ui = (Qi / Qmax) * 100%
    // ===============================================
    $hasil_ranking = [];
    foreach ($alternatif as $alt) {
        $id_alt = $alt['id_alternatif'];
        $Q_i = $matriks_Q[$id_alt];
        
        $U_i = 0;
        if ($Q_max > 0) {
            $U_i = ($Q_i / $Q_max) * 100;
        }
        
        $hasil_ranking[] = [
            'id_alternatif' => $id_alt,
            'nama_alternatif' => $alt['nama_alternatif'],
            'S_plus' => $S_plus[$id_alt],
            'S_minus' => $S_minus[$id_alt],
            'Qi' => $Q_i,
            'Ui' => $U_i
        ];
    }
    
    // ===============================================
    // LANGKAH 7: Perangkingan (Ui tertinggi adalah rank 1)
    // ===============================================
    usort($hasil_ranking, function($a, $b) {
        return $b['Ui'] <=> $a['Ui'];
    });

    return [
        'matriks_X' => $matriks_X,
        'sum_per_kriteria' => $sum_per_kriteria,
        'matriks_normalisasi' => $matriks_normalisasi,
        'matriks_berbobot' => $matriks_berbobot,
        'S_plus' => $S_plus,
        'S_minus' => $S_minus,
        'Q_max' => $Q_max,
        'hasil_ranking' => $hasil_ranking,
    ];
}

// Fungsi pembulatan 4 desimal
function round_format($number) {
    return number_format((float)$number, 4, '.', '');
}

// Fungsi untuk mendapatkan nilai kriteria alternatif tertentu
function get_nilai_alternatif($id_alt, $id_crit) {
    global $koneksi;
    $query = "SELECT nilai FROM nilai_alternatif WHERE id_alternatif = '$id_alt' AND id_kriteria = '$id_crit'";
    $result = mysqli_query($koneksi, $query);
    $row = mysqli_fetch_assoc($result);
    return $row ? $row['nilai'] : '';
}
?>
