<?php
/**
 * View: Login Page
 * Data dari AuthController: $error (bool), $registered (bool)
 */
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<div class="login-page">
    <!-- LEFT - Branding -->
    <div class="login-left">
        <div class="text-center text-white" style="position: relative; z-index: 1;">
            <div style="width: 80px; height: 80px; background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border-radius: 24px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 2rem; border: 1px solid rgba(255,255,255,0.2);">🎟️</div>
            <h1 style="font-size: 2.5rem; font-weight: 800; letter-spacing: -1px; text-shadow: 0 2px 20px rgba(0,0,0,0.3);"><?= APP_NAME ?></h1>
            <p style="opacity: 0.8; margin-top: 1rem; font-size: 1rem; max-width: 300px;">Platform manajemen tiket event modern untuk pengalaman lebih baik.</p>
            <div style="margin-top: 3rem; display: flex; flex-direction: column; gap: 1rem; text-align: left; max-width: 280px; margin-left: auto; margin-right: auto;">
                <?php foreach ([
                    ['🚀', 'Pemesanan Instan',    'Beli tiket dalam hitungan detik'],
                    ['🔒', 'Aman & Terpercaya',   'Transaksi dienkripsi sepenuhnya'],
                    ['📊', 'Analytics Real-time', 'Pantau performa event kamu'],
                ] as [$icon, $title, $desc]): ?>
                <div style="display: flex; align-items: center; gap: 1rem; background: rgba(255,255,255,0.1); padding: 1rem; border-radius: 14px; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.15);">
                    <span style="font-size: 1.5rem;"><?= $icon ?></span>
                    <div>
                        <div style="font-weight: 700; font-size: 0.9rem;"><?= $title ?></div>
                        <div style="font-size: 0.78rem; opacity: 0.7;"><?= $desc ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- RIGHT - Login Form -->
    <div class="login-right">
        <div class="login-box">
            <div class="logo-wrap">
                <div class="logo-icon">🎟️</div>
                <div>
                    <div style="font-size: 1rem; font-weight: 800; color: var(--text-primary);"><?= APP_NAME ?></div>
                    <div style="font-size: 0.7rem; color: var(--text-muted);">LOGIN PORTAL</div>
                </div>
            </div>
            <h1>Welcome back</h1>
            <p class="login-sub">Masuk ke akun kamu untuk melanjutkan.</p>

            <?php if ($registered ?? false): ?>
            <div class="alert alert-success mb-4 small">
                <i class="ri-checkbox-circle-line-fill"></i> Registrasi berhasil! Silakan login.
            </div>
            <?php endif; ?>

            <?php if ($error ?? false): ?>
            <div class="alert alert-danger mb-4 small">
                <i class="ri-error-warning-fill"></i> Email atau password salah.
            </div>
            <?php endif; ?>

            <form action="" method="post">
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.9rem;"><i class="ri-mail-line"></i></span>
                        <input type="email" name="email" id="email" class="form-control" placeholder="admin@event.com" style="padding-left: 2.5rem;" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.9rem;"><i class="ri-lock-2-line"></i></span>
                        <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" style="padding-left: 2.5rem;" required>
                        <button type="button" onclick="togglePass()" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 0.9rem;">
                            <i class="ri-eye-line" id="eye-icon"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" name="login" id="btn-login" class="btn btn-primary w-100 btn-lg mt-2 mb-3" style="justify-content: center; letter-spacing: 0.3px;">
                    Sign In <i class="ri-arrow-right-line-short" style="font-size: 1.2rem;"></i>
                </button>
                <div class="text-center small">
                    <span class="text-muted">Belum punya akun?</span>
                    <a href="<?= BASE_URL ?>/register.php" class="text-primary fw-600 text-decoration-none">Daftar Sekarang</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePass() {
    const p = document.getElementById('password');
    const icon = document.getElementById('eye-icon');
    p.type = p.type === 'password' ? 'text' : 'password';
    icon.className = p.type === 'password' ? 'ri-eye-line' : 'ri-eye-line-slash';
}
// Apply saved theme
const savedTheme = localStorage.getItem('theme') || 'dark';
document.documentElement.setAttribute('data-theme', savedTheme);
</script>
</body>
</html>
