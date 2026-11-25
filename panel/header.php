<?php 
session_start();

if($_SESSION['role'] != "guru" && $_SESSION['role'] != "admin"){
    header("location:../login.php?pesan=belum_login");
    exit();
}

include '../config/koneksi.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($_SESSION['role']); ?> Panel - Media Pembelajaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { min-height: 100vh; display: flex; flex-direction: column; }
        .main-container { flex: 1; display: flex; }
        .sidebar { width: 250px; min-height: 100vh; background: #212529; color: white; }
        .sidebar a { color: #adb5bd; text-decoration: none; display: block; padding: 10px 20px; }
        
        .sidebar a:hover, .sidebar a.active { 
            background: #343a40; 
            color: white; 
            border-left: 4px solid <?php echo $_SESSION['role'] == 'admin' ? '#0d6efd' : '#198754'; ?>; 
        } 
        
        .content { flex: 1; padding: 20px; background: #f8f9fa; }
        .nav-header { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: #6c757d; padding: 10px 20px; margin-top: 10px; }
    </style>
</head>
<body>

    <nav class="navbar navbar-dark bg-dark shadow-sm border-bottom border-secondary">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1 ms-2">
                <i class="fas <?php echo $_SESSION['role'] == 'admin' ? 'fa-user-shield' : 'fa-seedling'; ?> me-2"></i>
                <?php echo ucfirst($_SESSION['role']); ?> Panel
            </span>
            <span class="text-white me-3">Halo, <b><?php echo $_SESSION['nama_lengkap']; ?></b></span>
        </div>
    </nav>

    <div class="main-container">
        <div class="sidebar py-3">
            
            <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-home me-2"></i> Dashboard
            </a>
            
            <div class="nav-header">Data Master</div>
            <a href="kelola_kelas.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'kelola_kelas.php' ? 'active' : ''; ?>">
                <i class="fas fa-chalkboard me-2"></i> Data Kelas
            </a>
            <a href="kelola_siswa.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'kelola_siswa.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-graduate me-2"></i> Data Siswa
            </a>
            
            <a href="kelola_guru.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'kelola_guru.php' ? 'active' : ''; ?>">
                <i class="fas fa-users me-2"></i> Daftar Guru
            </a>

            <div class="nav-header">Pembelajaran</div>
            <a href="bank_soal.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'bank_soal.php' ? 'active' : ''; ?>">
                <i class="fas fa-database me-2"></i> Bank Soal
            </a>
            
            <a href="materi_belajar.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'materi_belajar.php' ? 'active' : ''; ?>">
                <i class="fas fa-book-open me-2"></i> Materi Belajar
            </a>

            <div class="nav-header">Laporan & Log</div>
            <a href="log_belajar.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'log_belajar.php' ? 'active' : ''; ?>">
                <i class="fas fa-history me-2"></i> Log Belajar
            </a>
            <a href="log_kuis.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'log_kuis.php' || basename($_SERVER['PHP_SELF']) == 'detail_kuis.php' ? 'active' : ''; ?>">
                <i class="fas fa-star me-2"></i> Log Soal
            </a>

            <hr class="border-secondary mx-3">
            <a href="../logout.php" class="text-danger">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a>
        </div>

        <div class="content">