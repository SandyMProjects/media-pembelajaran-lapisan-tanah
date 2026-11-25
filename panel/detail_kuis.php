<?php include 'header.php'; 

$id_sesi = $_GET['id'];

$query_header = mysqli_query($koneksi, "SELECT log_sesi.*, siswa.nama_siswa, kelas.nama_kelas 
                                        FROM log_sesi 
                                        JOIN siswa ON log_sesi.id_siswa = siswa.id_siswa
                                        JOIN kelas ON siswa.id_kelas = kelas.id_kelas
                                        WHERE log_sesi.id_sesi='$id_sesi'");
$header = mysqli_fetch_assoc($query_header);

$query_level = mysqli_query($koneksi, "SELECT level_saat_itu FROM detail_log WHERE id_sesi='$id_sesi' ORDER BY id_detail DESC LIMIT 1");
$data_level  = mysqli_fetch_assoc($query_level);

$level_akhir = isset($data_level['level_saat_itu']) ? $data_level['level_saat_itu'] : '-';

$kamus_sensor = [
    'sensor_0' => 'Horizon O (Lapisan Organik)',
    'sensor_1' => 'Horizon A (Topsoil)',
    'sensor_2' => 'Horizon E (Eluviasi)',
    'sensor_3' => 'Horizon B (Subsoil)',
    'sensor_4' => 'Horizon C (Bahan Induk)',
    'sensor_5' => 'Horizon R (Batuan Dasar)'
];
?>

<div class="mb-3">
    <a href="log_kuis.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Identitas Siswa</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $header['nama_siswa']; ?> 
                        </div>
                        <span class="badge bg-secondary"><?php echo $header['nama_kelas']; ?></span>
                        <div class="mt-2 small text-muted">
                            <i class="fas fa-calendar me-1"></i> <?php echo date('d F Y, H:i', strtotime($header['tanggal'])); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Hasil Akhir</div>
                        <div class="row">
                            <div class="col-6">
                                <small>Skor Total</small>
                                <div class="h2 mb-0 font-weight-bold text-gray-800"><?php echo $header['skor_akhir']; ?></div>
                            </div>
                            <div class="col-6 border-start ps-3">
                                <small>Level Kompetensi</small>
                                <div class="h4 mb-0 font-weight-bold">
                                    <?php 
                                    if($level_akhir == 'mudah') echo '<span class="text-success">DASAR (Mudah)</span>';
                                    elseif($level_akhir == 'sedang') echo '<span class="text-warning">MENENGAH (Sedang)</span>';
                                    elseif($level_akhir == 'susah') echo '<span class="text-danger">MAHIR (Susah)</span>';
                                    else echo '-';
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow">
    <div class="card-header py-3 bg-dark text-white d-flex justify-content-between">
        <h6 class="m-0 font-weight-bold">Analisis Pergerakan Level (Adaptive Flow)</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="5%">Urutan</th>
                        <th>Level Soal</th>
                        <th>Pertanyaan</th>
                        <th>Jawaban Siswa</th>
                        <th>Status</th>
                        <th>Analisis Sistem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    $q_detail = mysqli_query($koneksi, "SELECT detail_log.*, soal.pertanyaan_teks, soal.kunci_jawaban 
                                                        FROM detail_log 
                                                        LEFT JOIN soal ON detail_log.id_soal = soal.id_soal 
                                                        WHERE detail_log.id_sesi='$id_sesi' 
                                                        ORDER BY detail_log.id_detail ASC");
                    
                    while($row = mysqli_fetch_array($q_detail)){
                        $jawaban_siswa = isset($kamus_sensor[$row['jawaban_siswa']]) ? $kamus_sensor[$row['jawaban_siswa']] : $row['jawaban_siswa'];
                        $kunci_jawaban = isset($kamus_sensor[$row['kunci_jawaban']]) ? $kamus_sensor[$row['kunci_jawaban']] : $row['kunci_jawaban'];
                    ?>
                    <tr>
                        <td class="text-center fw-bold"><?php echo $no++; ?></td>
                        <td>
                            <?php 
                            if($row['level_saat_itu'] == 'mudah') echo '<span class="badge bg-success">MUDAH</span>';
                            elseif($row['level_saat_itu'] == 'sedang') echo '<span class="badge bg-warning text-dark">SEDANG</span>';
                            else echo '<span class="badge bg-danger">SUSAH</span>';
                            ?>
                        </td>
                        <td>
                            <?php echo $row['pertanyaan_teks']; ?><br>
                            <small class="text-muted">Kunci: <?php echo $kunci_jawaban; ?></small>
                        </td>
                        <td><?php echo $jawaban_siswa; ?></td>
                        <td>
                            <?php if($row['status_jawaban'] == 'benar'): ?>
                                <i class="fas fa-check-circle text-success fa-2x"></i>
                            <?php else: ?>
                                <i class="fas fa-times-circle text-danger fa-2x"></i>
                            <?php endif; ?>
                        </td>
                        <td class="small">
                            <?php 
                            if($row['status_jawaban'] == 'benar'){
                                echo "Jawaban Benar. <br><span class='text-success'>&uarr; Level Naik / Bertahan</span>";
                            } else {
                                echo "Jawaban Salah. <br><span class='text-danger'>&darr; Level Turun</span>";
                            }
                            ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>