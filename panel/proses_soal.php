<?php 
session_start();
include '../config/koneksi.php';

if(!isset($_SESSION['role']) || ($_SESSION['role'] != "guru" && $_SESSION['role'] != "admin")){
    header("location:../login.php");
    exit();
}

$aksi = $_GET['aksi'];
$id_guru = $_SESSION['id_user'];

function downloadTTS($text, $filename){
    $text_bersih = preg_replace('/[^a-zA-Z0-9 .,?!]/', '', $text);
    $text_encoded = urlencode($text_bersih);
    $url = "https://translate.google.com/translate_tts?ie=UTF-8&client=tw-ob&q=$text_encoded&tl=id";
    
    $audio_content = file_get_contents($url);
    if($audio_content){
        file_put_contents("../assets/audio/" . $filename, $audio_content);
        return true;
    }
    return false;
}

if($aksi == "tambah"){
    $pertanyaan = $_POST['pertanyaan_teks'];
    $level      = $_POST['level'];
    $jenis      = $_POST['jenis_input'];
    $kunci      = $_POST['kunci_jawaban'];
    $penjelasan = $_POST['penjelasan'];

    $query = mysqli_query($koneksi, "INSERT INTO soal (pertanyaan_teks, file_audio, level, jenis_input, kunci_jawaban, penjelasan, id_guru) 
            VALUES ('$pertanyaan', '-', '$level', '$jenis', '$kunci', '$penjelasan', '$id_guru')");

    if($query){
        $id_baru = mysqli_insert_id($koneksi);
        $nama_file_soal  = "soal_" . $id_baru . ".mp3";
        $nama_file_kunci = "kunci_" . $id_baru . ".mp3";

        downloadTTS($pertanyaan, $nama_file_soal);
        downloadTTS($penjelasan, $nama_file_kunci);

        $path_db = "assets/audio/" . $nama_file_soal;
        mysqli_query($koneksi, "UPDATE soal SET file_audio='$path_db' WHERE id_soal='$id_baru'");
        
        $q_guru = mysqli_query($koneksi, "SELECT nama_lengkap FROM users WHERE id_user='$id_guru'");
        $nama_guru = mysqli_fetch_assoc($q_guru)['nama_lengkap'];
        
        $cuplikan = substr($pertanyaan, 0, 35) . "...";
        
        $ket = "Guru $nama_guru telah menambahkan soal baru: \"$cuplikan\"";
        mysqli_query($koneksi, "INSERT INTO log_aktivitas (id_user, aksi, keterangan) VALUES ('$id_guru', 'TAMBAH_SOAL', '$ket')");

        header("location:bank_soal.php?pesan=sukses");
    }

} else if($aksi == "edit"){
    $id_soal    = $_POST['id_soal'];
    $pertanyaan = $_POST['pertanyaan_teks'];
    $level      = $_POST['level'];
    $jenis      = $_POST['jenis_input'];
    $kunci      = $_POST['kunci_jawaban'];
    $penjelasan = trim($_POST['penjelasan']);
    
    if($penjelasan == ""){
        $penjelasan = "Jawaban yang benar adalah " . $kunci;
    }

    $query = mysqli_query($koneksi, "UPDATE soal SET 
                            pertanyaan_teks = '$pertanyaan', 
                            level = '$level', 
                            jenis_input = '$jenis', 
                            kunci_jawaban = '$kunci',
                            penjelasan = '$penjelasan' 
                            WHERE id_soal = '$id_soal'");

    if($query){
        $nama_file_soal  = "soal_" . $id_soal . ".mp3";
        $nama_file_kunci = "kunci_" . $id_soal . ".mp3";
        
        $path_soal_lokal = "../assets/audio/" . $nama_file_soal;
        $path_kunci_lokal = "../assets/audio/" . $nama_file_kunci;
        
        if(file_exists($path_soal_lokal)) { unlink($path_soal_lokal); }
        if(file_exists($path_kunci_lokal)) { unlink($path_kunci_lokal); }

        downloadTTS($pertanyaan, $nama_file_soal);
        downloadTTS($penjelasan, $nama_file_kunci);

        $q_guru = mysqli_query($koneksi, "SELECT nama_lengkap FROM users WHERE id_user='$id_guru'");
        $nama_guru = mysqli_fetch_assoc($q_guru)['nama_lengkap'];
        
        $cuplikan = substr($pertanyaan, 0, 35) . "...";
        
        $ket = "Guru $nama_guru melakukan perubahan pada soal: \"$cuplikan\" (Mengubah teks, kunci, atau level).";
        mysqli_query($koneksi, "INSERT INTO log_aktivitas (id_user, aksi, keterangan) VALUES ('$id_guru', 'EDIT_SOAL', '$ket')");

        header("location:bank_soal.php?pesan=update");
    }

} else if($aksi == "hapus"){
    $id = $_GET['id'];
    $cek = mysqli_query($koneksi, "SELECT file_audio, pertanyaan_teks FROM soal WHERE id_soal='$id'");
    $data = mysqli_fetch_assoc($cek);
    
    $pertanyaan_lama = $data['pertanyaan_teks'];
    $query = mysqli_query($koneksi, "DELETE FROM soal WHERE id_soal='$id'");

    if($query){
        $file_soal = "../assets/audio/soal_" . $id . ".mp3";
        if(file_exists($file_soal)) unlink($file_soal);

        $file_kunci = "../assets/audio/kunci_" . $id . ".mp3";
        if(file_exists($file_kunci)) unlink($file_kunci);

        if(file_exists("../" . $data['file_audio'])) unlink("../" . $data['file_audio']);

        $q_guru = mysqli_query($koneksi, "SELECT nama_lengkap FROM users WHERE id_user='$id_guru'");
        $nama_guru = mysqli_fetch_assoc($q_guru)['nama_lengkap'];
        
        $cuplikan = substr($pertanyaan_lama, 0, 35) . "...";
        
        $ket = "Guru $nama_guru telah menghapus soal: \"$cuplikan\" dari Bank Soal.";
        mysqli_query($koneksi, "INSERT INTO log_aktivitas (id_user, aksi, keterangan) VALUES ('$id_guru', 'HAPUS_SOAL', '$ket')");

        header("location:bank_soal.php?pesan=hapus");
    } else {
        header("location:bank_soal.php?pesan=error");
    }
}
?>
