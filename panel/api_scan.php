<?php
include '../config/koneksi.php';

$command = "sudo /usr/bin/python3 /var/www/html/skripsi_tanah/hardware/tools/scan_single.py 2>&1";
$output = shell_exec($command);
$rfid_hasil = trim($output);

if($rfid_hasil == "timeout"){
    echo json_encode([
        "status" => "error", 
        "pesan" => "Waktu habis. Tidak ada kartu yang ditempel."
    ]);

} else if (is_numeric($rfid_hasil)) {
    $cek = mysqli_query($koneksi, "SELECT * FROM siswa WHERE rfid_uid = '$rfid_hasil'");
    
    if(mysqli_num_rows($cek) > 0){
        $data_siswa = mysqli_fetch_assoc($cek);
        echo json_encode([
            "status" => "duplicate",
            "rfid" => $rfid_hasil,
            "pemilik" => $data_siswa['nama_siswa'],
            "pesan" => "Kartu ini sudah dipakai oleh: " . $data_siswa['nama_siswa']
        ]);
    } else {
        echo json_encode([
            "status" => "success", 
            "rfid" => $rfid_hasil,
            "pesan" => "Kartu Baru Berhasil Ditemukan!"
        ]);
    }

} else {
    echo json_encode([
        "status" => "error", 
        "pesan" => "Error Alat: " . $rfid_hasil
    ]);
}
?>
