<?php
// Salin file ini menjadi "koneksi.php" dan sesuaikan kredensial database Anda
$server   = "localhost";
$username = "root";
$password = "";
$database = "db_skripsi_tanah";

$koneksi = mysqli_connect($server, $username, $password, $database);

if (!$koneksi) {
    die("Gagal terhubung ke database: " . mysqli_connect_error());
}
?>