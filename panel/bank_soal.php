<?php include 'header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Bank Soal</h3>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="fas fa-plus me-2"></i>Tambah Soal Baru
    </button>
</div>

<?php 
if(isset($_GET['pesan'])){
    if($_GET['pesan']=="sukses") echo "<div class='alert alert-success'>Soal berhasil disimpan & Audio berhasil dibuat!</div>";
    if($_GET['pesan']=="update") echo "<div class='alert alert-success'>Soal berhasil diupdate!</div>";
    if($_GET['pesan']=="hapus") echo "<div class='alert alert-success'>Soal berhasil dihapus!</div>";
    if($_GET['pesan']=="gagal_audio") echo "<div class='alert alert-warning'>Data tersimpan, tapi Gagal download Audio.</div>";
}
?>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th width="5%">No</th>
                        <th>Pertanyaan</th>
                        <th>Level</th>
                        <th>Input</th>
                        <th>Kunci</th>
                        <th>Audio</th>
                        <th>Pembuat</th> 
                        <th width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    $query = mysqli_query($koneksi, "SELECT soal.*, users.nama_lengkap 
                                                     FROM soal 
                                                     LEFT JOIN users ON soal.id_guru = users.id_user 
                                                     ORDER BY soal.id_soal DESC");
                    
                    while($d = mysqli_fetch_array($query)){
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo $d['pertanyaan_teks']; ?></td>
                        <td>
                            <?php 
                            if($d['level']=='mudah') echo '<span class="badge bg-success">Mudah</span>';
                            elseif($d['level']=='sedang') echo '<span class="badge bg-warning text-dark">Sedang</span>';
                            else echo '<span class="badge bg-danger">Susah</span>';
                            ?>
                        </td>
                        <td><?php echo strtoupper($d['jenis_input']); ?></td>
                        <td>
                            <?php 
                            $kunci_tampil = $d['kunci_jawaban'];
                            $kamus_tampil = [
                                'sensor_0' => 'Horizon O', 'sensor_1' => 'Horizon A', 
                                'sensor_2' => 'Horizon E', 'sensor_3' => 'Horizon B', 
                                'sensor_4' => 'Horizon C', 'sensor_5' => 'Horizon R'
                            ];
                            echo isset($kamus_tampil[$kunci_tampil]) ? $kamus_tampil[$kunci_tampil] : $kunci_tampil;
                            ?>
                        </td>
                        <td>
                            <audio controls class="w-100" style="height: 30px; min-width: 250px;">
                                <source src="../assets/audio/soal_<?php echo $d['id_soal']; ?>.mp3?v=<?php echo time(); ?>" type="audio/mpeg">
                                Browser tidak support.
                            </audio>
                        </td>
                        <td><small class="text-muted"><?php echo $d['nama_lengkap']; ?></small></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEdit<?php echo $d['id_soal']; ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="proses_soal.php?aksi=hapus&id=<?php echo $d['id_soal']; ?>" 
                               class="btn btn-sm btn-danger" onclick="return confirm('Hapus soal ini?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    
                    <div class="modal fade" id="modalEdit<?php echo $d['id_soal']; ?>">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-warning">
                                    <h5 class="modal-title text-dark">Edit Soal & Re-Generate Audio</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="proses_soal.php?aksi=edit" method="POST">
                                    <div class="modal-body text-start">
                                        <input type="hidden" name="id_soal" value="<?php echo $d['id_soal']; ?>">
                                        <div class="mb-3">
                                            <label class="fw-bold">Teks Pertanyaan (Akan dibacakan sistem)</label>
                                            <textarea name="pertanyaan_teks" class="form-control" rows="3" required><?php echo $d['pertanyaan_teks']; ?></textarea>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label>Tingkat Kesulitan</label>
                                                <select name="level" class="form-select" required>
                                                    <option value="mudah" <?php if($d['level']=='mudah') echo 'selected'; ?>>Mudah</option>
                                                    <option value="sedang" <?php if($d['level']=='sedang') echo 'selected'; ?>>Sedang</option>
                                                    <option value="susah" <?php if($d['level']=='susah') echo 'selected'; ?>>Susah</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label>Jenis Input Siswa</label>
                                                <select name="jenis_input" class="form-select jenis-input" onchange="toggleKunci(this)" required>
                                                    <option value="sentuh" <?php if($d['jenis_input']=='sentuh') echo 'selected'; ?>>Sentuh (Sensor)</option>
                                                    <option value="suara" <?php if($d['jenis_input']=='suara') echo 'selected'; ?>>Suara (Mic)</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label>Kunci Jawaban</label>
                                                <select class="form-select kunci-sentuh">
                                                    <option value="">-- Pilih Lapisan --</option>
                                                    <option value="sensor_0" <?php echo ($d['kunci_jawaban']=='sensor_0')?'selected':''; ?>>Horizon O (Lapisan Organik)</option>
                                                    <option value="sensor_1" <?php echo ($d['kunci_jawaban']=='sensor_1')?'selected':''; ?>>Horizon A (Topsoil)</option>
                                                    <option value="sensor_2" <?php echo ($d['kunci_jawaban']=='sensor_2')?'selected':''; ?>>Horizon E (Eluviasi)</option>
                                                    <option value="sensor_3" <?php echo ($d['kunci_jawaban']=='sensor_3')?'selected':''; ?>>Horizon B (Subsoil)</option>
                                                    <option value="sensor_4" <?php echo ($d['kunci_jawaban']=='sensor_4')?'selected':''; ?>>Horizon C (Bahan Induk)</option>
                                                    <option value="sensor_5" <?php echo ($d['kunci_jawaban']=='sensor_5')?'selected':''; ?>>Horizon R (Batuan Dasar)</option>
                                                </select>
                                                <input type="text" class="form-control mt-2 kunci-suara" value="<?php echo ($d['jenis_input']=='suara') ? $d['kunci_jawaban'] : ''; ?>" placeholder="Ketik kata kunci suara..." style="display: none;">
                                                <small class="text-muted hint-jawaban">Pilih lapisan tanah yang benar.</small>
                                            </div>
                                            <div class="mb-3">
                                                <label class="fw-bold">Penjelasan Jawaban (Akan dibacakan jika siswa salah 3x)</label>
                                                <textarea name="penjelasan" class="form-control" rows="2" required><?php echo isset($d['penjelasan']) ? $d['penjelasan'] : ''; ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-warning text-dark"><i class="fas fa-sync me-1"></i> Update & Generate Audio</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambah">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Soal & Generate Audio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="proses_soal.php?aksi=tambah" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="fw-bold">Teks Pertanyaan (Akan dibacakan sistem)</label>
                        <textarea name="pertanyaan_teks" class="form-control" rows="3" required placeholder="Contoh: Manakah yang merupakan lapisan Humus?"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Tingkat Kesulitan</label>
                            <select name="level" class="form-select" required>
                                <option value="mudah">Mudah</option>
                                <option value="sedang">Sedang</option>
                                <option value="susah">Susah</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Jenis Input Siswa</label>
                            <select name="jenis_input" class="form-select jenis-input" onchange="toggleKunci(this)" required>
                                <option value="sentuh">Sentuh (Sensor)</option>
                                <option value="suara">Suara (Mic)</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Kunci Jawaban</label>
                            <select class="form-select kunci-sentuh">
                                <option value="">-- Pilih Lapisan --</option>
                                <option value="sensor_0">Horizon O (Lapisan Organik)</option>
                                <option value="sensor_1">Horizon A (Topsoil)</option>
                                <option value="sensor_2">Horizon E (Eluviasi)</option>
                                <option value="sensor_3">Horizon B (Subsoil)</option>
                                <option value="sensor_4">Horizon C (Bahan Induk)</option>
                                <option value="sensor_5">Horizon R (Batuan Dasar)</option>
                            </select>
                            <input type="text" class="form-control mt-2 kunci-suara" placeholder="Ketik kata kunci suara..." style="display: none;">
                            <small class="text-muted hint-jawaban">Pilih lapisan tanah yang benar.</small>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Penjelasan Jawaban</label>
                            <textarea name="penjelasan" class="form-control" rows="2" required placeholder="Contoh: Jawaban yang benar adalah Lapisan Humus karena mengandung banyak bahan organik."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan & Generate Audio</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleKunci(selectElement) {
    var container = selectElement.closest('.modal-body');
    var sentuh = container.querySelector('.kunci-sentuh');
    var suara = container.querySelector('.kunci-suara');
    var hint = container.querySelector('.hint-jawaban');

    if (selectElement.value === "sentuh") {
        sentuh.style.display = "block";
        sentuh.setAttribute("required", "required");
        sentuh.setAttribute("name", "kunci_jawaban");
        
        suara.style.display = "none";
        suara.removeAttribute("required");
        suara.removeAttribute("name");
        
        hint.innerHTML = "Pilih lapisan tanah yang benar dari pilihan.";
    } else {
        suara.style.display = "block";
        suara.setAttribute("required", "required");
        suara.setAttribute("name", "kunci_jawaban");
        
        sentuh.style.display = "none";
        sentuh.removeAttribute("required");
        sentuh.removeAttribute("name");
        
        hint.innerHTML = "Ketik satu kata kunci (contoh: humus, kuarsa).";
    }
}

window.onload = function() {
    var allSelects = document.querySelectorAll('.jenis-input');
    allSelects.forEach(function(select) {
        toggleKunci(select);
    });
};
</script>

<?php include 'footer.php'; ?>