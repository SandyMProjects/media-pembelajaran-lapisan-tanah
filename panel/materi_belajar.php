<?php include 'header.php'; ?>

<div class="mb-4">
    <h3>Kelola Materi Belajar</h3>
    <p class="text-muted">Edit penjelasan suara untuk setiap lapisan tanah pada alat.</p>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th width="10%">Sensor</th>
                        <th width="25%">Nama Lapisan</th>
                        <th>Isi Penjelasan Suara</th>
                        <th width="15%">Audio</th>
                        <th width="8%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $query = mysqli_query($koneksi, "SELECT * FROM materi ORDER BY sensor_ke ASC");
                    while($d = mysqli_fetch_array($query)){
                    ?>
                    <tr>
                        <td class="text-center fw-bold"><?php echo $d['sensor_ke']; ?></td>
                        <td><?php echo $d['judul_lapisan']; ?></td>
                        <td><?php echo $d['isi_penjelasan']; ?></td>
                        <td>
                            <audio controls class="w-100" style="height: 30px;">
                                <source src="../<?php echo $d['file_audio']; ?>?v=<?php echo time(); ?>" type="audio/mpeg">
                            </audio>
                        </td>
                        <td>
                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editMateri<?php echo $d['id_materi']; ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>

                    <div class="modal fade" id="editMateri<?php echo $d['id_materi']; ?>">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-warning">
                                    <h5 class="modal-title">Edit Penjelasan Lapisan <?php echo $d['sensor_ke']; ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="proses_materi.php" method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="id_materi" value="<?php echo $d['id_materi']; ?>">
                                        <input type="hidden" name="sensor_ke" value="<?php echo $d['sensor_ke']; ?>">
                                        
                                        <div class="mb-3">
                                            <label>Nama Lapisan</label>
                                            <input type="text" name="judul_lapisan" class="form-control" value="<?php echo $d['judul_lapisan']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Teks Penjelasan (TTS)</label>
                                            <textarea name="isi_penjelasan" class="form-control" rows="4" required><?php echo $d['isi_penjelasan']; ?></textarea>
                                            <small class="text-muted">Teks ini akan otomatis diubah menjadi suara MP3.</small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-warning">Update & Generate Audio</button>
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

<?php include 'footer.php'; ?>
