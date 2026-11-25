<?php
session_start();

if (isset($_SESSION['role'])) {
    header("location:admin/index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Media Pembelajaran Tanah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .card-login {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background-color: #4a90e2;
            border: none;
        }
        .btn-primary:hover {
            background-color: #357abd;
        }
    </style>
</head>
<body>

    <div class="card card-login bg-white">
        <div class="text-center mb-4">
            <h4 class="mt-3 fw-bold">Login</h4>
            <p class="text-muted">Media Pembelajaran Lapisan Tanah</p>
        </div>

        <?php 
        if(isset($_GET['pesan'])){
            if($_GET['pesan'] == "gagal"){
                echo "<div class='alert alert-danger small text-center'>Username atau Password salah!</div>";
            } else if($_GET['pesan'] == "logout"){
                echo "<div class='alert alert-success small text-center'>Anda telah berhasil logout.</div>";
            } else if($_GET['pesan'] == "belum_login"){
                echo "<div class='alert alert-warning small text-center'>Silakan login terlebih dahulu.</div>";
            }
        }
        ?>

        <form action="cek_login.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" required autofocus>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary fw-bold">Masuk</button>
            </div>
        </form>
        
        <div class="text-center mt-4 text-muted">
            <small>Skripsi &copy; <?php echo date('Y'); ?> - Sandy Maulana Wibawa</small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>