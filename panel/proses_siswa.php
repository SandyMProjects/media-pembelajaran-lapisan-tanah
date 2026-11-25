<?php 
session_start();
include '../config/koneksi.php';

if(!isset($_SESSION['role']) || ($_SESSION['role'] != "guru" && $_SESSION['role'] != "admin")){
    header("location:../login.php");
    exit();
}

$aksi = $_GET['aksi'];
$id_guru = $_SESSION['id_user'];

if($aksi == "tambah"){
    $rfid  = $_POST['rfid_uid'];
    $nis   = $_POST['nis'];
    $nama  = $_POST['nama_siswa'];
    $jk    = $_POST['jenis_kelamin'];
    $kelas = $_POST['id_kelas'];

    $cek = mysqli_query($koneksi, "SELECT * FROM siswa WHERE rfid_uid='$rfid'");
    if(mysqli_num_rows($cek) > 0){
        header("location:kelola_siswa.php?pesan=error");
        exit();
    }

    $query = mysqli_query($koneksi, "INSERT INTO siswa (rfid_uid, nis, nama_siswa, id_kelas, jenis_kelamin) VALUES ('$rfid', '$nis', '$nama', '$kelas', '$jk')");
    
    if($query){
        $q_kelas = mysqli_query($koneksi, "SELECT nama_kelas FROM kelas WHERE id_kelas='$kelas'");
        $nama_kelas_asli = mysqli_fetch_assoc($q_kelas)['nama_kelas'];
        
        $ket = "Mendaftarkan siswa baru atas nama $nama ke kelas $nama_kelas_asli.";
        mysqli_query($koneksi, "INSERT INTO log_aktivitas (id_user, aksi, keterangan) VALUES ('$id_guru', 'TAMBAH_SISWA', '$ket')");
        
        header("location:kelola_siswa.php?pesan=sukses");
    } else {
        header("location:kelola_siswa.php?pesan=error");
    }

} else if($aksi == "edit"){
    $id    = $_POST['id_siswa'];
    $rfid  = $_POST['rfid_uid'];
    $nis   = $_POST['nis'];
    $nama  = $_POST['nama_siswa'];
    $jk    = $_POST['jenis_kelamin'];
    $kelas = $_POST['id_kelas'];

    $query = mysqli_query($koneksi, "UPDATE siswa SET rfid_uid='$rfid', nis='$nis', nama_siswa='$nama', jenis_kelamin='$jk', id_kelas='$kelas' WHERE id_siswa='$id'");

    if($query){
        $q_kelas = mysqli_query($koneksi, "SELECT nama_kelas FROM kelas WHERE id_kelas='$kelas'");
        $nama_kelas_asli = mysqli_fetch_assoc($q_kelas)['nama_kelas'];

        $ket = "Memperbarui data profil siswa atas nama $nama (menyesuaikan rincian NIS / RFID / masuk ke kelas $nama_kelas_asli).";
        mysqli_query($koneksi, "INSERT INTO log_aktivitas (id_user, aksi, keterangan) VALUES ('$id_guru', 'EDIT_SISWA', '$ket')");
        
        header("location:kelola_siswa.php?pesan=update");
    } else {
        header("location:kelola_siswa.php?pesan=error");
    }

} else if($aksi == "hapus"){
    $id = $_GET['id'];
    $nama = $_GET['nama'];

    mysqli_query($koneksi, "DELETE FROM log_eksplorasi WHERE id_siswa='$id'");
    mysqli_query($koneksi, "DELETE FROM log_sesi WHERE id_siswa='$id'");
    $query = mysqli_query($koneksi, "DELETE FROM siswa WHERE id_siswa='$id'");

    if($query){
        $ket = "Menghapus siswa: $nama";
        mysqli_query($koneksi, "INSERT INTO log_aktivitas (id_user, aksi, keterangan) VALUES ('$id_guru', 'HAPUS_SISWA', '$ket')");
        header("location:kelola_siswa.php?pesan=hapus");
    } else {
        header("location:kelola_siswa.php?pesan=error");
    }
}
?>