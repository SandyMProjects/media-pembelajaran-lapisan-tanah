<?php include 'header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Data Siswa</h3>
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="fas fa-user-plus me-2"></i>Tambah Siswa
    </button>
</div>

<?php 
if(isset($_GET['pesan'])){
    if($_GET['pesan']=="sukses") echo "<div class='alert alert-success'>Data siswa berhasil disimpan!</div>";
    if($_GET['pesan']=="update") echo "<div class='alert alert-success'>Data siswa berhasil diupdate!</div>";
    if($_GET['pesan']=="hapus") echo "<div class='alert alert-success'>Data siswa berhasil dihapus!</div>";
    if($_GET['pesan']=="error") echo "<div class='alert alert-danger'>Gagal! RFID mungkin sudah terdaftar.</div>";
}
?>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th width="5%">No</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>L/P</th>
                        <th>Kelas</th>
                        <th>RFID UID</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    $query = mysqli_query($koneksi, "SELECT siswa.*, kelas.nama_kelas FROM siswa LEFT JOIN kelas ON siswa.id_kelas = kelas.id_kelas ORDER BY siswa.id_siswa DESC");
                    
                    while($d = mysqli_fetch_array($query)){
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo $d['nis']; ?></td>
                        <td><?php echo $d['nama_siswa']; ?></td>
                        <td><?php echo $d['jenis_kelamin']; ?></td>
                        <td><?php echo $d['nama_kelas']; ?></td>
                        <td><span class="badge bg-secondary"><?php echo $d['rfid_uid']; ?></span></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-warning text-white" 
                                data-bs-toggle="modal" 
                                data-bs-target="#modalEdit"
                                data-id="<?php echo $d['id_siswa']; ?>"
                                data-nis="<?php echo $d['nis']; ?>"
                                data-nama="<?php echo $d['nama_siswa']; ?>"
                                data-jk="<?php echo $d['jenis_kelamin']; ?>"
                                data-kelas="<?php echo $d['id_kelas']; ?>"
                                data-rfid="<?php echo $d['rfid_uid']; ?>">
                                <i class="fas fa-edit"></i>
                            </button>

                            <a href="proses_siswa.php?aksi=hapus&id=<?php echo $d['id_siswa']; ?>&nama=<?php echo $d['nama_siswa']; ?>" 
                               class="btn btn-sm btn-danger" onclick="return confirm('Hapus siswa ini?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambah">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Siswa Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="proses_siswa.php?aksi=tambah" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nomor RFID</label>
                        <input type="text" name="rfid_uid" id="rfid_input_otomatis" class="form-control" placeholder="Tempelkan kartu ke alat (Otomatis terisi)..." readonly required>
                        <small class="text-success fw-bold" id="status_scan"><i class="fas fa-spinner fa-spin"></i> Menunggu kartu ditempel di alat...</small>
                    </div>
                    <div class="mb-3">
                        <label>NIS</label>
                        <input type="text" name="nis" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Nama Siswa</label>
                        <input type="text" name="nama_siswa" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select" required>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Kelas</label>
                            <select name="id_kelas" class="form-select" required>
                                <option value="">Pilih Kelas</option>
                                <?php 
                                $kelas = mysqli_query($koneksi, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
                                while($k = mysqli_fetch_array($kelas)){
                                    echo "<option value='".$k['id_kelas']."'>".$k['nama_kelas']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdit">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="proses_siswa.php?aksi=edit" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_siswa" id="edit_id">
                    
                    <div class="mb-3">
                        <label>Nomor RFID (Ubah jika ganti kartu)</label>
                        <div class="input-group">
                            <input type="text" name="rfid_uid" id="edit_rfid" class="form-control" required>
                            <button class="btn btn-outline-primary" type="button" onclick="tarikKartuBaru('edit_rfid')">
                                <i class="fas fa-sync-alt"></i> Tarik Kartu Baru
                            </button>
                        </div>
                        <small class="text-muted">Cara ganti kartu: Tempel kartu baru ke alat, biarkan alat bilang "Belum terdaftar", lalu klik tombol Tarik.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label>NIS</label>
                        <input type="text" name="nis" id="edit_nis" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Nama Siswa</label>
                        <input type="text" name="nama_siswa" id="edit_nama" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Jenis Kelamin</label>
                            <select name="jenis_kelamin" id="edit_jk" class="form-select" required>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Kelas</label>
                            <select name="id_kelas" id="edit_kelas" class="form-select" required>
                                <?php 
                                mysqli_data_seek($kelas, 0); 
                                while($k = mysqli_fetch_array($kelas)){
                                    echo "<option value='".$k['id_kelas']."'>".$k['nama_kelas']."</option>";
                                }
                                ?>
                            </select>
                        </div>
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
        
        modalEdit.querySelector('#edit_id').value = button.getAttribute('data-id');
        modalEdit.querySelector('#edit_nis').value = button.getAttribute('data-nis');
        modalEdit.querySelector('#edit_nama').value = button.getAttribute('data-nama');
        modalEdit.querySelector('#edit_rfid').value = button.getAttribute('data-rfid');
        modalEdit.querySelector('#edit_jk').value = button.getAttribute('data-jk');
        modalEdit.querySelector('#edit_kelas').value = button.getAttribute('data-kelas');
    });

    setInterval(function() {
    fetch('api_cek_kartu.php')
    .then(response => response.json())
    .then(data => {
        var inputField = document.getElementById('rfid_input_otomatis');
        var statusText = document.getElementById('status_scan');
        
        if(data.status === 'success') {
            if(inputField.value !== data.rfid) {
                inputField.value = data.rfid;
                inputField.classList.add("is-valid");
                statusText.innerHTML = "<i class='fas fa-check-circle'></i> Kartu terdeteksi!";
                statusText.classList.remove("text-success");
                statusText.classList.add("text-primary");
            }
        } else {
            inputField.value = "";
            inputField.classList.remove("is-valid");
            statusText.innerHTML = "<i class='fas fa-spinner fa-spin'></i> Menunggu kartu ditempel di alat...";
            statusText.classList.remove("text-primary");
            statusText.classList.add("text-success");
        }
    })
    .catch(error => console.error('Error:', error));
}, 1500);

function tarikKartuBaru(inputId) {
    fetch('api_cek_kartu.php')
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            var inputField = document.getElementById(inputId);
            inputField.value = data.rfid;
            inputField.classList.add("is-valid");
            alert("Berhasil menarik ID Kartu Baru: " + data.rfid);
        } else {
            alert("Belum ada kartu baru yang terdeteksi. Silakan tempelkan kartu baru ke alat terlebih dahulu.");
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>

<?php include 'footer.php'; ?>
