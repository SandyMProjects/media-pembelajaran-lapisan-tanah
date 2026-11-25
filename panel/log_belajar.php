<?php include 'header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Log Mode Belajar</h3>
</div>

<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>
    Halaman ini mencatat aktivitas siswa saat melakukan eksplorasi (menyentuh lapisan tanah untuk mendengarkan penjelasan).
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th width="5%">No</th>
                        <th>Tanggal & Waktu</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Lama Belajar (Durasi)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    $query = mysqli_query($koneksi, "SELECT log_eksplorasi.*, siswa.nama_siswa, kelas.nama_kelas 
                                                     FROM log_eksplorasi 
                                                     JOIN siswa ON log_eksplorasi.id_siswa = siswa.id_siswa
                                                     JOIN kelas ON siswa.id_kelas = kelas.id_kelas
                                                     ORDER BY log_eksplorasi.id_log DESC");
                    
                    while($d = mysqli_fetch_array($query)){
                        $total_detik = $d['durasi_detik'];
                        $menit = floor($total_detik / 60);
                        $detik = $total_detik % 60;
                        $durasi_teks = $menit . " menit " . $detik . " detik";
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td>
                            <?php echo date('d-m-Y H:i', strtotime($d['waktu_mulai'])); ?> WIB
                        </td>
                        <td>
                            <b><?php echo $d['nama_siswa']; ?></b>
                        </td>
                        <td><?php echo $d['nama_kelas']; ?></td>
                        <td>
                            <span class="badge bg-primary" style="font-size: 1em;">
                                <i class="fas fa-clock me-1"></i> <?php echo $durasi_teks; ?>
                            </span>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            
            <?php if(mysqli_num_rows($query) == 0): ?>
                <div class="text-center p-3 text-muted">Belum ada riwayat mode belajar.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>