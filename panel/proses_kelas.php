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
    $nama_kelas = $_POST['nama_kelas'];
    
    $query = mysqli_query($koneksi, "INSERT INTO kelas (nama_kelas, id_guru) VALUES ('$nama_kelas', '$id_guru')");
    
    $ket = "Telah mendaftarkan kelas $nama_kelas ke dalam sistem.";
    mysqli_query($koneksi, "INSERT INTO log_aktivitas (id_user, aksi, keterangan) VALUES ('$id_guru', 'TAMBAH_KELAS', '$ket')");

    header("location:kelola_kelas.php?pesan=sukses");

} else if($aksi == "hapus"){
    $id = $_GET['id'];
    $nama = $_GET['nama'];

    $query = mysqli_query($koneksi, "DELETE FROM kelas WHERE id_kelas='$id'");

    $ket = "Telah menghapus seluruh data untuk kelas $nama.";
    mysqli_query($koneksi, "INSERT INTO log_aktivitas (id_user, aksi, keterangan) VALUES ('$id_guru', 'HAPUS_KELAS', '$ket')");

    header("location:kelola_kelas.php?pesan=hapus");
}
?>