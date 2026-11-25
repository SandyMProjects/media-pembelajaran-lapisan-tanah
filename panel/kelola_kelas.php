<?php include 'header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Data Kelas</h3>
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="fas fa-plus me-2"></i>Tambah Kelas
    </button>
</div>

<?php 
if(isset($_GET['pesan'])){
    if($_GET['pesan']=="sukses") echo "<div class='alert alert-success'>Kelas berhasil ditambah!</div>";
    if($_GET['pesan']=="hapus") echo "<div class='alert alert-success'>Kelas berhasil dihapus!</div>";
}
?>

<div class="card shadow">
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th width="5%">No</th>
                    <th>Nama Kelas</th>
                    <th>Wali Kelas (Pembuat)</th>
                    <th width="15%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                $query = mysqli_query($koneksi, "SELECT kelas.*, users.nama_lengkap FROM kelas LEFT JOIN users ON kelas.id_guru = users.id_user ORDER BY nama_kelas ASC");
                while($d = mysqli_fetch_array($query)){
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo $d['nama_kelas']; ?></td>
                    <td><?php echo $d['nama_lengkap'] ? $d['nama_lengkap'] : '<span class="text-danger">Guru Terhapus</span>'; ?></td>
                    <td>
                         <a href="proses_kelas.php?aksi=hapus&id=<?php echo $d['id_kelas']; ?>&nama=<?php echo $d['nama_kelas']; ?>" 
                           class="btn btn-sm btn-danger" onclick="return confirm('Hapus kelas ini?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalTambah">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Kelas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="proses_kelas.php?aksi=tambah" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nama Kelas</label>
                        <input type="text" name="nama_kelas" class="form-control" placeholder="Contoh: 4A, 5B" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>