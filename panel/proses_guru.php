<?php 
session_start();

if($_SESSION['role'] != 'admin') {
    echo "<script>
            alert('AKSES ILEGAL: Hanya Administrator yang diizinkan untuk menambah, mengubah, atau menghapus data Guru!');
            window.location.href = 'kelola_guru.php';
          </script>";
    exit();
}

include '../config/koneksi.php';

$aksi = $_GET['aksi'];
$id_admin = $_SESSION['id_user'];

if($aksi == "tambah"){
    $nama = $_POST['nama_lengkap'];
    $user = $_POST['username'];
    $pass = md5($_POST['password']);

    $query = mysqli_query($koneksi, "INSERT INTO users (username, password, nama_lengkap, role) VALUES ('$user', '$pass', '$nama', 'guru')");

    if($query){
        $ket = "Menambahkan guru baru: $nama";
        mysqli_query($koneksi, "INSERT INTO log_aktivitas (id_user, aksi, keterangan) VALUES ('$id_admin', 'TAMBAH_GURU', '$ket')");
        
        header("location:kelola_guru.php?pesan=sukses");
    } else {
        header("location:kelola_guru.php?pesan=error");
    }

} else if($aksi == "edit"){
    $id   = $_POST['id_user'];
    $nama = $_POST['nama_lengkap'];
    $user = $_POST['username'];
    $pass = $_POST['password'];

    if($pass == ""){
        $query = mysqli_query($koneksi, "UPDATE users SET nama_lengkap='$nama', username='$user' WHERE id_user='$id'");
    } else {
        $pass_hash = md5($pass);
        $query = mysqli_query($koneksi, "UPDATE users SET nama_lengkap='$nama', username='$user', password='$pass_hash' WHERE id_user='$id'");
    }

    if($query){
        $ket = "Mengedit data guru $nama";
        mysqli_query($koneksi, "INSERT INTO log_aktivitas (id_user, aksi, keterangan) VALUES ('$id_admin', 'EDIT_GURU', '$ket')");

        header("location:kelola_guru.php?pesan=update");
    } else {
        header("location:kelola_guru.php?pesan=error");
    }

} else if($aksi == "hapus"){
    $id = $_GET['id'];
    $nama_guru = $_GET['nama'];

    $query = mysqli_query($koneksi, "DELETE FROM users WHERE id_user='$id'");

    if($query){
        $ket = "Menghapus guru: $nama_guru";
        mysqli_query($koneksi, "INSERT INTO log_aktivitas (id_user, aksi, keterangan) VALUES ('$id_admin', 'HAPUS_GURU', '$ket')");

        header("location:kelola_guru.php?pesan=hapus");
    } else {
        header("location:kelola_guru.php?pesan=error");
    }
}
?>