<?php 
session_start();
include '../config/koneksi.php';

if(!isset($_SESSION['role']) || ($_SESSION['role'] != "guru" && $_SESSION['role'] != "admin")){
    header("location:../login.php");
    exit();
}

$id_guru = $_SESSION['id_user'];

function downloadTTS($text, $filename){
    $text_bersih = preg_replace('/[^a-zA-Z0-9 .,?!-]/', ' ', $text);
    $text_terpotong = wordwrap($text_bersih, 150, "|||"); 
    $kalimat_array = explode('|||', $text_terpotong);
    $audio_gabungan = ""; 
    
    $options = [
        "http" => [
            "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n"
        ]
    ];
    $context = stream_context_create($options);

    foreach($kalimat_array as $kalimat){
        $kalimat = trim($kalimat);
        if(strlen($kalimat) > 0){
            $text_encoded = urlencode($kalimat); 
            $url = "https://translate.google.com/translate_tts?ie=UTF-8&client=tw-ob&q=$text_encoded&tl=id";
            $audio_chunk = @file_get_contents($url, false, $context);
            if($audio_chunk){
                $audio_gabungan .= $audio_chunk; 
            }
        }
    }
    
    if($audio_gabungan != ""){
        file_put_contents("../assets/audio/" . $filename, $audio_gabungan);
        return true;
    }
    return false;
}

$id_materi      = $_POST['id_materi'];
$sensor_ke      = $_POST['sensor_ke'];
$judul_lapisan  = $_POST['judul_lapisan'];
$isi_penjelasan = $_POST['isi_penjelasan'];

$update = mysqli_query($koneksi, "UPDATE materi SET 
                                    judul_lapisan = '$judul_lapisan', 
                                    isi_penjelasan = '$isi_penjelasan' 
                                    WHERE id_materi = '$id_materi'");

if($update){
    $teks_gabungan = $judul_lapisan . ". . . " . $isi_penjelasan;
    $nama_file = "materi_" . $sensor_ke . ".mp3";
    $path_lokal = "../assets/audio/" . $nama_file;

    if(file_exists($path_lokal)) { unlink($path_lokal); }

    downloadTTS($teks_gabungan, $nama_file);

    $q_guru = mysqli_query($koneksi, "SELECT nama_lengkap FROM users WHERE id_user='$id_guru'");
    $nama_guru = mysqli_fetch_assoc($q_guru)['nama_lengkap'];
    
    $ket = "Guru $nama_guru telah memperbarui isi materi dan audio untuk $judul_lapisan.";
    mysqli_query($koneksi, "INSERT INTO log_aktivitas (id_user, aksi, keterangan) VALUES ('$id_guru', 'EDIT_MATERI', '$ket')");

    header("location:materi_belajar.php?pesan=sukses");
} else {
    header("location:materi_belajar.php?pesan=gagal");
}
?>