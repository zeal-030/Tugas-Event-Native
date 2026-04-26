<?php
/**
 * View: Register Page
 * Data dari AuthController: $error (string|null)
 */
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <style>
        body { background: var(--bg-base); display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .auth-card { background: var(--bg-surface); border: 1px solid var(--border); border-radius: 30px; width: 100%; max-width: 450px; padding: 2.5rem; box-shadow: var(--shadow-lg); }
    </style>
    <script>
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body>
<div class="auth-card">
    <div class="text-center mb-4">
        <div style="width:50px; height:50px; background:var(--gradient-primary); border-radius:15px; display:inline-flex; align-items:center; justify-content:center; font-size:1.5rem; color:white; margin-bottom:1rem;">🎟️</div>
        <h3 class="fw-bold">Create Account</h3>
        <p class="text-muted small">Daftar untuk mulai memesan tiket event</p>
    </div>

    <?php if ($error ?? null): ?>
    <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="" method="post">
        <div class="mb-3">
            <label class="form-label small fw-600">Nama Lengkap</label>
            <input type="text" name="nama" id="nama" class="form-control rounded-3" placeholder="Masukkan nama" required>
        </div>
        <div class="mb-3">
            <label class="form-label small fw-600">Email Address</label>
            <input type="email" name="email" id="reg-email" class="form-control rounded-3" placeholder="nama@email.com" required>
        </div>
        <div class="mb-3">
            <label class="form-label small fw-600">Password</label>
            <input type="password" name="password" id="reg-password" class="form-control rounded-3" placeholder="••••••••" required>
        </div>
        <div class="mb-4">
            <label class="form-label small fw-600">Konfirmasi Password</label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control rounded-3" placeholder="••••••••" required>
        </div>
        <button type="submit" name="register" id="btn-register" class="btn btn-primary w-100 py-2 fw-600 rounded-3 mb-3">Daftar Sekarang</button>
        <div class="text-center small">
            <span class="text-muted">Sudah punya akun?</span>
            <a href="<?= BASE_URL ?>/login.php" class="text-primary fw-600 text-decoration-none">Login</a>
        </div>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
