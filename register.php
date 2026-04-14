<?php
session_start();
require_once 'config/db.php';

if (isset($_SESSION['login'])) {
    header("Location: " . ($_SESSION['role'] == 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php')); exit;
}

$error = null;
if (isset($_POST['register'])) {
    $nama  = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass  = $_POST['password'];
    $conf  = $_POST['confirm_password'];

    // Cek email sudah ada atau belum
    $cek = mysqli_query($conn, "SELECT email FROM users WHERE email = '$email'");
    if (mysqli_num_rows($cek) > 0) {
        $error = "Email sudah terdaftar!";
    } elseif ($pass !== $conf) {
        $error = "Konfirmasi password tidak sesuai!";
    } else {
        // Simpan user baru (Role default: user)
        $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (nama, email, password, role) VALUES ('$nama', '$email', '$hashed_pass', 'user')";
        if (mysqli_query($conn, $query)) {
            header("Location: login.php?registered=1"); exit;
        } else {
            $error = "Gagal mendaftar, coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register — EventTiket</title>
    <?php include 'includes/head.php'; ?>
    <style>
        body { background: var(--bg-base); display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .auth-card { background: var(--bg-surface); border: 1px solid var(--border); border-radius: 30px; width: 100%; max-width: 450px; padding: 2.5rem; box-shadow: var(--shadow-lg); }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="text-center mb-4">
        <div style="width:50px; height:50px; background:var(--gradient-primary); border-radius:15px; display:inline-flex; align-items:center; justify-content:center; font-size:1.5rem; color:white; margin-bottom:1rem;">🎟️</div>
        <h3 class="fw-bold">Create Account</h3>
        <p class="text-muted small">Daftar untuk mulai memesan tiket event</p>
    </div>

    <?php if ($error): ?><div class="alert alert-danger py-2 small"><?= $error ?></div><?php endif; ?>

    <form action="" method="post">
        <div class="mb-3">
            <label class="form-label small fw-600">Nama Lengkap</label>
            <input type="text" name="nama" class="form-control rounded-3" placeholder="Masukkan nama" required>
        </div>
        <div class="mb-3">
            <label class="form-label small fw-600">Email Address</label>
            <input type="email" name="email" class="form-control rounded-3" placeholder="nama@email.com" required>
        </div>
        <div class="mb-3">
            <label class="form-label small fw-600">Password</label>
            <input type="password" name="password" class="form-control rounded-3" placeholder="••••••••" required>
        </div>
        <div class="mb-4">
            <label class="form-label small fw-600">Konfirmasi Password</label>
            <input type="password" name="confirm_password" class="form-control rounded-3" placeholder="••••••••" required>
        </div>
        <button type="submit" name="register" class="btn btn-primary w-100 py-2 fw-600 rounded-3 mb-3">Daftar Sekarang</button>
        <div class="text-center small">
            <span class="text-muted">Sudah punya akun?</span> 
            <a href="login.php" class="text-primary fw-600 text-decoration-none">Login</a>
        </div>
    </form>
</div>
</body>
</html>
