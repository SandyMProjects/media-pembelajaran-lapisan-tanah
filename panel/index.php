<?php include 'header.php'; ?>

<h3 class="mb-4">Dashboard <?php echo ucfirst($_SESSION['role']); ?></h3>

<?php if($_SESSION['role'] == 'admin'): ?>
    <div class="alert alert-info mt-4 shadow-sm border-left-info">
        <h4 class="alert-heading"><i class="fas fa-user-shield me-2"></i>Selamat Datang, <?php echo $_SESSION['nama_lengkap']; ?>!</h4>
        <p class="mb-0">Anda berada di halaman Administrator. Gunakan menu di sebelah kiri untuk mengelola Data Guru, Kelas, Siswa, dan memantau riwayat aktivitas sistem pembelajaran.</p>
    </div>
<?php else: ?>
    <div class="alert alert-success mt-4 shadow-sm border-left-success">
        <h4 class="alert-heading"><i class="fas fa-chalkboard-teacher me-2"></i>Selamat Datang, <?php echo $_SESSION['nama_lengkap']; ?>!</h4>
        <p class="mb-0">Silahkan gunakan menu di sebelah kiri untuk mengelola kelas, data siswa, serta menyusun bank soal untuk Media Pembelajaran Lapisan Tanah.</p>
    </div>
<?php endif; ?>

<div class="row">
    <?php 
    $siswa = mysqli_query($koneksi, "SELECT * FROM siswa");
    $jml_siswa = mysqli_num_rows($siswa);
    ?>
    <div class="col-md-4 mb-3">
        <div class="card bg-primary text-white h-100 shadow">
            <div class="card-body">
                <h3><?php echo $jml_siswa; ?></h3>
                <p>Total Siswa</p>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="kelola_siswa.php" class="text-white text-decoration-none">Kelola Siswa <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>

    <?php 
    $soal = mysqli_query($koneksi, "SELECT * FROM soal");
    $jml_soal = mysqli_num_rows($soal);
    ?>
    <div class="col-md-4 mb-3">
        <div class="card bg-success text-white h-100 shadow">
            <div class="card-body">
                <h3><?php echo $jml_soal; ?></h3>
                <p>Bank Soal</p>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="bank_soal.php" class="text-white text-decoration-none">Kelola Soal <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>

    <?php 
    $hari_ini = date('Y-m-d');
    $log = mysqli_query($koneksi, "SELECT * FROM log_sesi WHERE DATE(tanggal) = '$hari_ini'");
    $jml_log = mysqli_num_rows($log);
    ?>
    <div class="col-md-4 mb-3">
        <div class="card bg-warning text-white h-100 shadow">
            <div class="card-body">
                <h3><?php echo $jml_log; ?></h3>
                <p>Sesi Belajar Hari Ini</p>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="log_belajar.php" class="text-white text-decoration-none">Lihat Detail <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4 mb-4 shadow-sm">
    <div class="card-header bg-dark text-white">
        <strong><i class="fas fa-clipboard-list me-2"></i>Log Aktivitas Terbaru Sistem</strong>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" width="5%">No</th>
                        <th width="15%">Waktu</th>
                        <th width="20%">Pengguna (Role)</th>
                        <th width="15%" class="text-center">Aksi</th>
                        <th width="45%">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query_log = "SELECT l.*, u.nama_lengkap, u.role 
                                  FROM log_aktivitas l 
                                  LEFT JOIN users u ON l.id_user = u.id_user 
                                  ORDER BY l.waktu DESC 
                                  LIMIT 10";
                    
                    $result_log = mysqli_query($koneksi, $query_log);
                    
                    if(mysqli_num_rows($result_log) > 0) {
                        $no = 1;
                        while($row = mysqli_fetch_assoc($result_log)) {
                            $waktu_format = date('d-m-Y H:i:s', strtotime($row['waktu']));
                            
                            echo "<tr>";
                            echo "<td class='text-center'>".$no++."</td>";
                            echo "<td>".$waktu_format."</td>";
                            echo "<td><strong>".$row['nama_lengkap']."</strong> <br><small class='text-muted'>(".strtoupper($row['role']).")</small></td>";
                            
                            echo "<td class='text-center'><strong class='text-primary'>".$row['aksi']."</strong></td>";
                            
                            echo "<td>".$row['keterangan']."</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center text-muted'>Belum ada rekaman log aktivitas.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>