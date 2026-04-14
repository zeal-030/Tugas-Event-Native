<nav class="pub-nav">
    <div class="container d-flex justify-content-between align-items-center px-4">
        <a href="index.php" class="navbar-brand d-flex align-items-center gap-2">
            <div style="width:32px; height:32px; background:var(--gradient-primary); border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:1rem; color:white;">🎟️</div>
            <span style="font-weight:800; background:var(--gradient-primary); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">EventTiket</span>
        </a>
        <div class="d-flex gap-3 align-items-center">
            <a href="index.php" class="nav-link-pub <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">Home</a>
            <?php if(!isset($_SESSION['login'])): ?>
                <a href="login.php" class="btn btn-primary btn-sm px-4">Login</a>
            <?php else: ?>
                <a href="user/dashboard.php" class="btn btn-ghost btn-sm px-4">Dashboard</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
