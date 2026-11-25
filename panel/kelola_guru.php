<?php include 'header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Data Guru</h3>
    
    <?php if($_SESSION['role'] == 'admin') { ?>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="fas fa-plus me-2"></i>Tambah Guru
    </button>
    <?php } ?>
</div>

<?php 
if(isset($_GET['pesan'])){
    if($_GET['pesan'] == "sukses"){
        echo "<div class='alert alert-success'>Data berhasil disimpan!</div>";
    } else if($_GET['pesan'] == "update"){
        echo "<div class='alert alert-success'>Data berhasil diupdate!</div>";
    } else if($_GET['pesan'] == "hapus"){
        echo "<div class='alert alert-success'>Data berhasil dihapus!</div>";
    } else if($_GET['pesan'] == "error"){
        echo "<div class='alert alert-danger'>Terjadi kesalahan!</div>";
    }
}
?>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th width="5%">No</th>
                        <th>Nama Lengkap</th>
                        <th>Username</th>
                        
                        <?php if($_SESSION['role'] == 'admin') { ?>
                        <th width="15%" class="text-center">Aksi</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    $data = mysqli_query($koneksi, "SELECT * FROM users WHERE role='guru' ORDER BY id_user DESC");
                    while($d = mysqli_fetch_array($data)){
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo $d['nama_lengkap']; ?></td>
                        <td><?php echo $d['username']; ?></td>
                        
                        <?php if($_SESSION['role'] == 'admin') { ?>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-warning text-white" 
                                data-bs-toggle="modal" 
                                data-bs-target="#modalEdit"
                                data-id="<?php echo $d['id_user']; ?>"
                                data-nama="<?php echo $d['nama_lengkap']; ?>"
                                data-user="<?php echo $d['username']; ?>">
                                <i class="fas fa-edit"></i>
                            </button>

                            <a href="proses_guru.php?aksi=hapus&id=<?php echo $d['id_user']; ?>&nama=<?php echo $d['nama_lengkap']; ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Yakin ingin menghapus guru ini?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                        <?php } ?>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        
        <?php if($_SESSION['role'] == 'guru') { ?>
            <div class="alert alert-light border small mt-3 text-muted mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Catatan: Pengelolaan data guru (Tambah/Edit/Hapus) hanya dapat dilakukan oleh <b>Administrator</b>.
            </div>
        <?php } ?>
        
    </div>
</div>

<?php if($_SESSION['role'] == 'admin') { ?>

    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Guru Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="proses_guru.php?aksi=tambah" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" name="nama_lengkap" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEdit" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Data Guru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="proses_guru.php?aksi=edit" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id_user" id="edit_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" name="nama_lengkap" id="edit_nama" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" id="edit_user" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password Baru <small class="text-muted">(Kosongkan jika tidak diganti)</small></label>
                            <input type="password" class="form-control" name="password">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const modalEdit = document.getElementById('modalEdit');
        modalEdit.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            
            const id = button.getAttribute('data-id');
            const nama = button.getAttribute('data-nama');
            const user = button.getAttribute('data-user');

            modalEdit.querySelector('#edit_id').value = id;
            modalEdit.querySelector('#edit_nama').value = nama;
            modalEdit.querySelector('#edit_user').value = user;
        });
    </script>

<?php } ?>

<?php include 'footer.php'; ?>