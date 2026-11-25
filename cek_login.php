<?php 
session_start();

include 'config/koneksi.php';

$username = mysqli_real_escape_string($koneksi, $_POST['username']);
$password = md5($_POST['password']);

$login = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username' AND password='$password'");

$cek = mysqli_num_rows($login);

if($cek > 0){
    $data = mysqli_fetch_assoc($login);
    if($data['role'] == "admin" || $data['role'] == "guru"){
        
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $data['role'];
        $_SESSION['nama_lengkap'] = $data['nama_lengkap'];
        $_SESSION['id_user'] = $data['id_user'];
        
        header("location:panel/index.php");
    } else {
        header("location:login.php?pesan=gagal");
    }
} else {
    header("location:login.php?pesan=gagal");
}
?>