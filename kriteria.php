<?php
require_once 'config/koneksi.php';
include 'template/header.php';

// Ambil semua data kriteria
$query = "SELECT * FROM kriteria ORDER BY id_kriteria ASC";
$result = mysqli_query($koneksi, $query);

// Tentukan apakah dalam mode edit
$is_edit = false;
$data_edit = [];
if (isset($_GET['aksi']) && $_GET['aksi'] == 'edit' && isset($_GET['id'])) {
    $id_kriteria = (int)$_GET['id'];
    $query_edit = "SELECT * FROM kriteria WHERE id_kriteria = '$id_kriteria'";
    $result_edit = mysqli_query($koneksi, $query_edit);
    $data_edit = mysqli_fetch_assoc($result_edit);
    
    if ($data_edit) {
        $is_edit = true;
    } else {
        $_SESSION['pesan'] = ['tipe' => 'danger', 'isi' => 'Data kriteria tidak ditemukan.'];
        redirect('kriteria.php');
    }
}
?>

<div class="card">
    <div class="card-header">
        <?php echo $is_edit ? 'Edit Kriteria' : 'Tambah Kriteria'; ?>
    </div>
    <form action="kriteria_aksi.php" method="POST">
        <?php if ($is_edit): ?>
            <input type="hidden" name="id_kriteria" value="<?php echo $data_edit['id_kriteria']; ?>">
            <input type="hidden" name="aksi" value="update">
        <?php else: ?>
            <input type="hidden" name="aksi" value="tambah">
        <?php endif; ?>
        
        <div class="form-group">
            <label for="nama_kriteria">Nama Kriteria</label>
            <input type="text" id="nama_kriteria" name="nama_kriteria" class="form-control" required
                   value="<?php echo $is_edit ? $data_edit['nama_kriteria'] : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="bobot">Bobot (Wj - Total Bobot harus 1.0)</label>
            <input type="number" step="0.01" min="0" max="1" id="bobot" name="bobot" class="form-control" required
                   value="<?php echo $is_edit ? $data_edit['bobot'] : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="jenis">Jenis</label>
            <select id="jenis" name="jenis" class="form-control" required>
                <option value="benefit" <?php echo ($is_edit && $data_edit['jenis'] == 'benefit') ? 'selected' : ''; ?>>Benefit (Makin Besar Makin Baik)</option>
                <option value="cost" <?php echo ($is_edit && $data_edit['jenis'] == 'cost') ? 'selected' : ''; ?>>Cost (Makin Kecil Makin Baik)</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary"><?php echo $is_edit ? 'Update' : 'Simpan'; ?></button>
        <?php if ($is_edit): ?>
             <a href="kriteria.php" class="btn btn-warning">Batal Edit</a>
        <?php endif; ?>
    </form>
</div>

<div class="card mt-3">
    <div class="card-header">
        Daftar Kriteria
    </div>
    <table class="table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th>Nama Kriteria</th>
                <th style="width: 15%;">Bobot (Wj)</th>
                <th style="width: 15%;">Jenis</th>
                <th style="width: 20%;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            $total_bobot = 0;
            while ($row = mysqli_fetch_assoc($result)): 
                $total_bobot += $row['bobot'];
            ?>
                <tr>
                    <td class="text-center"><?php echo $no++; ?></td>
                    <td><?php echo $row['nama_kriteria']; ?></td>
                    <td class="text-center"><?php echo number_format($row['bobot'], 2); ?></td>
                    <td><?php echo ucfirst($row['jenis']); ?></td>
                    <td class="btn-group">
                        <a href="kriteria.php?aksi=edit&id=<?php echo $row['id_kriteria']; ?>" class="btn btn-warning">Edit</a>
                        <a href="kriteria_aksi.php?aksi=hapus&id=<?php echo $row['id_kriteria']; ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus kriteria ini? Menghapus kriteria akan menghapus semua nilai yang terkait.')">Hapus</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" class="text-center">TOTAL BOBOT</th>
                <th class="text-center"><?php echo number_format($total_bobot, 2); ?></th>
                <th></th>
                <th></th>
            </tr>
            <?php if ($total_bobot != 1.00): ?>
            <tr>
                <td colspan="5">
                    <div class="alert alert-danger text-center">
                        Total Bobot **TIDAK SAMA DENGAN 1.00**! Harap sesuaikan bobot agar totalnya 1.00.
                    </div>
                </td>
            </tr>
            <?php endif; ?>
        </tfoot>
    </table>
</div>

<?php include 'template/footer.php'; ?>
