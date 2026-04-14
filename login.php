<?php
session_start();
require_once 'config/db.php';

if (isset($_SESSION['login'])) {
    header("Location: " . ($_SESSION['role'] == 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'));
    exit;
}

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $result = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password'])) {
            $_SESSION['login'] = true;
            $_SESSION['id_user'] = $row['id_user'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['role'] = $row['role'];
            header("Location: " . ($row['role'] == 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'));
            exit;
        }
    }
    $error = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — EventTiket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<div class="login-page">
    <!-- LEFT - Branding -->
    <div class="login-left">
        <div class="text-center text-white" style="position: relative; z-index: 1;">
            <div style="width: 80px; height: 80px; background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border-radius: 24px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 2rem; border: 1px solid rgba(255,255,255,0.2);">🎟️</div>
            <h1 style="font-size: 2.5rem; font-weight: 800; letter-spacing: -1px; text-shadow: 0 2px 20px rgba(0,0,0,0.3);">EventTiket</h1>
            <p style="opacity: 0.8; margin-top: 1rem; font-size: 1rem; max-width: 300px;">Platform manajemen tiket event modern untuk pengalaman lebih baik.</p>

            <div style="margin-top: 3rem; display: flex; flex-direction: column; gap: 1rem; text-align: left; max-width: 280px; margin-left: auto; margin-right: auto;">
                <div style="display: flex; align-items: center; gap: 1rem; background: rgba(255,255,255,0.1); padding: 1rem; border-radius: 14px; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.15);">
                    <span style="font-size: 1.5rem;">🚀</span>
                    <div>
                        <div style="font-weight: 700; font-size: 0.9rem;">Pemesanan Instan</div>
                        <div style="font-size: 0.78rem; opacity: 0.7;">Beli tiket dalam hitungan detik</div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 1rem; background: rgba(255,255,255,0.1); padding: 1rem; border-radius: 14px; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.15);">
                    <span style="font-size: 1.5rem;">🔒</span>
                    <div>
                        <div style="font-weight: 700; font-size: 0.9rem;">Aman & Terpercaya</div>
                        <div style="font-size: 0.78rem; opacity: 0.7;">Transaksi dibela dengan enkripsi</div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 1rem; background: rgba(255,255,255,0.1); padding: 1rem; border-radius: 14px; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.15);">
                    <span style="font-size: 1.5rem;">📊</span>
                    <div>
                        <div style="font-weight: 700; font-size: 0.9rem;">Analytics Real-time</div>
                        <div style="font-size: 0.78rem; opacity: 0.7;">Pantau performa event kamu</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT - Login Form -->
    <div class="login-right">
        <div class="login-box">
            <div class="logo-wrap">
                <div class="logo-icon">🎟️</div>
                <div>
                    <div style="font-size: 1rem; font-weight: 800; color: var(--text-primary);">EventTiket</div>
                    <div style="font-size: 0.7rem; color: var(--text-muted);">LOGIN PORTAL</div>
                </div>
            </div>
            <h1>Welcome back</h1>
            <p class="login-sub">Masuk ke akun kamu untuk melanjutkan.</p>

            <?php if (isset($_GET['registered'])) : ?>
            <div class="alert alert-success mb-4 small">
                <i class="bi bi-check-circle-fill"></i> Registrasi berhasil! Silakan login.
            </div>
            <?php endif; ?>

            <?php if (isset($error)) : ?>
            <div class="alert alert-danger mb-4 small">
                <i class="bi bi-exclamation-circle-fill"></i> Email atau password salah.
            </div>
            <?php endif; ?>

            <form action="" method="post">
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.9rem;"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="admin@event.com" style="padding-left: 2.5rem;" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.9rem;"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" style="padding-left: 2.5rem;" required>
                        <button type="button" onclick="togglePass()" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 0.9rem;">
                            <i class="bi bi-eye" id="eye-icon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" name="login" class="btn btn-primary w-100 btn-lg mt-2 mb-3" style="justify-content: center; letter-spacing: 0.3px;">
                    Sign In <i class="bi bi-arrow-right-short" style="font-size: 1.2rem;"></i>
                </button>
                <div class="text-center small">
                    <span class="text-muted">Belum punya akun?</span> 
                    <a href="register.php" class="text-primary fw-600 text-decoration-none">Daftar Sekarang</a>
                </div>
            </form>

            <div style="margin-top: 2rem; padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 12px; border: 1px solid var(--border); text-align: center;">
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.5rem;">Demo Credentials</div>
                <div style="font-size: 0.8rem; color: var(--text-secondary);">
                    <strong>Admin:</strong> admin@event.com / password<br>
                    <strong>User:</strong> user@event.com / password
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePass() {
    const p = document.getElementById('password');
    const icon = document.getElementById('eye-icon');
    if (p.type === 'password') {
        p.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        p.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>
</body>
</html>
