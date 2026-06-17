<?php
require_once 'config/koneksi.php';
include 'template/header.php';

// Ambil semua data alternatif
$query = "SELECT * FROM alternatif ORDER BY id_alternatif ASC";
$result = mysqli_query($koneksi, $query);

// Tentukan apakah dalam mode edit
$is_edit = false;
$data_edit = [];
if (isset($_GET['aksi']) && $_GET['aksi'] == 'edit' && isset($_GET['id'])) {
    $id_alternatif = (int)$_GET['id'];
    $query_edit = "SELECT * FROM alternatif WHERE id_alternatif = '$id_alternatif'";
    $result_edit = mysqli_query($koneksi, $query_edit);
    $data_edit = mysqli_fetch_assoc($result_edit);
    
    if ($data_edit) {
        $is_edit = true;
    } else {
        $_SESSION['pesan'] = ['tipe' => 'danger', 'isi' => 'Data alternatif tidak ditemukan.'];
        redirect('alternatif.php');
    }
}
?>

<div class="card">
    <div class="card-header">
        <?php echo $is_edit ? 'Edit Alternatif' : 'Tambah Alternatif'; ?>
    </div>
    <form action="alternatif_aksi.php" method="POST">
        <?php if ($is_edit): ?>
            <input type="hidden" name="id_alternatif" value="<?php echo $data_edit['id_alternatif']; ?>">
            <input type="hidden" name="aksi" value="update">
        <?php else: ?>
            <input type="hidden" name="aksi" value="tambah">
        <?php endif; ?>
        
        <div class="form-group">
            <label for="nama_alternatif">Nama Alternatif (Calon Kepala Gudang)</label>
            <input type="text" id="nama_alternatif" name="nama_alternatif" class="form-control" required
                   value="<?php echo $is_edit ? $data_edit['nama_alternatif'] : ''; ?>">
        </div>
        
        <button type="submit" class="btn btn-primary"><?php echo $is_edit ? 'Update' : 'Simpan'; ?></button>
        <?php if ($is_edit): ?>
             <a href="alternatif.php" class="btn btn-warning">Batal Edit</a>
        <?php endif; ?>
    </form>
</div>

<div class="card mt-3">
    <div class="card-header">
        Daftar Alternatif
    </div>
    <table class="table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th>Nama Alternatif</th>
                <th style="width: 25%;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            while ($row = mysqli_fetch_assoc($result)): 
            ?>
                <tr>
                    <td class="text-center"><?php echo $no++; ?></td>
                    <td><?php echo $row['nama_alternatif']; ?></td>
                    <td class="btn-group">
                        <a href="nilai.php?id=<?php echo $row['id_alternatif']; ?>" class="btn btn-success">Input Nilai</a>
                        <a href="alternatif.php?aksi=edit&id=<?php echo $row['id_alternatif']; ?>" class="btn btn-warning">Edit</a>
                        <a href="alternatif_aksi.php?aksi=hapus&id=<?php echo $row['id_alternatif']; ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus alternatif ini? Menghapus alternatif akan menghapus semua nilai yang terkait.')">Hapus</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'template/footer.php'; ?>
