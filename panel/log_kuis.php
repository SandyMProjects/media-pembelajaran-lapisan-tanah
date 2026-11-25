<?php include 'header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Riwayat Belajar Siswa</h3>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th width="5%">No</th>
                        <th>Waktu</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Skor Akhir</th>
                        <th>Status</th>
                        <th width="10%">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    $query = mysqli_query($koneksi, "SELECT log_sesi.*, siswa.nama_siswa, siswa.nis, kelas.nama_kelas 
                                                     FROM log_sesi 
                                                     JOIN siswa ON log_sesi.id_siswa = siswa.id_siswa
                                                     JOIN kelas ON siswa.id_kelas = kelas.id_kelas
                                                     ORDER BY log_sesi.id_sesi DESC");
                    
                    while($d = mysqli_fetch_array($query)){
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td>
                            <?php echo date('d-m-Y H:i', strtotime($d['tanggal'])); ?> WIB
                        </td>
                        <td>
                            <b><?php echo $d['nama_siswa']; ?></b><br>
                            <small class="text-muted">NIS: <?php echo $d['nis']; ?></small>
                        </td>
                        <td><?php echo $d['nama_kelas']; ?></td>
                        <td>
                            <span class="fw-bold text-primary" style="font-size: 1.2em;">
                                <?php echo $d['skor_akhir']; ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                            if($d['status'] == 'selesai'){
                                echo '<span class="badge bg-success">Selesai</span>';
                            } else {
                                echo '<span class="badge bg-warning text-dark">Sedang Berjalan</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <a href="detail_kuis.php?id=<?php echo $d['id_sesi']; ?>" class="btn btn-sm btn-info text-white">
                                <i class="fas fa-eye me-1"></i> Detail
                            </a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            
            <?php if(mysqli_num_rows($query) == 0): ?>
                <div class="text-center p-3 text-muted">Belum ada data riwayat belajar.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>