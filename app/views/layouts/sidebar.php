<?php
/**
 * Layout: Sidebar
 * Menggunakan BASE_URL dari constants.php — tidak perlu $base_url relatif lagi
 */
$user        = currentUser();
$current_uri = $_SERVER['REQUEST_URI'];
$isAdmin     = $user['role'] === 'admin';

// Helper fungsi untuk menandai menu aktif
function isActive(string $keyword): string {
    return strpos($_SERVER['REQUEST_URI'], $keyword) !== false ? 'active' : '';
}
function isActivePage(string $page): string {
    return basename($_SERVER['PHP_SELF']) === $page ? 'active' : '';
}
?>
<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-brand">
        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="brand-logo">
            <div class="brand-icon">🎟️</div>
            <div>
                <div class="brand-name"><?= APP_NAME ?></div>
                <div class="brand-subtitle">ADMIN PANEL</div>
            </div>
        </a>
    </div>

    <div class="sidebar-nav">
        <?php if (in_array($user['role'], ADMIN_ROLES)): ?>
            <div class="nav-section-label">Main Menu</div>
            <a href="<?= BASE_URL ?>/admin/dashboard.php" class="nav-link <?= isActivePage('dashboard.php') ?>">
                <div class="nav-icon"><i class="ri-dashboard-3-line"></i></div><span>Dashboard</span>
            </a>

            <?php if ($isAdmin): ?>
            <a href="<?= BASE_URL ?>/admin/venue.php" class="nav-link <?= isActive('/admin/venue') ?>">
                <div class="nav-icon"><i class="ri-building-2-line"></i></div><span>Venues</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/event.php" class="nav-link <?= isActive('/admin/event') ?>">
                <div class="nav-icon"><i class="ri-calendar-event-line"></i></div><span>Events</span>
            </a>
            <?php endif; ?>

            <a href="<?= BASE_URL ?>/admin/tiket.php" class="nav-link <?= isActive('/admin/tiket') ?>">
                <div class="nav-icon"><i class="ri-ticket-2-line"></i></div><span>Tickets</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/voucher.php" class="nav-link <?= isActive('/admin/voucher') ?>">
                <div class="nav-icon"><i class="ri-coupon-3-line"></i></div><span>Vouchers</span>
            </a>

            <div class="nav-section-label" style="margin-top: 1rem;">Operations</div>
            <a href="<?= BASE_URL ?>/admin/checkin.php" class="nav-link <?= isActivePage('checkin.php') ?>">
                <div class="nav-icon"><i class="ri-qr-scan-2-line"></i></div><span>Check-in</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/scanner.php" class="nav-link <?= isActivePage('scanner.php') ?>">
                <div class="nav-icon"><i class="ri-camera-lens-line"></i></div><span>QR Scanner</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/laporan.php" class="nav-link <?= isActivePage('laporan.php') ?>">
                <div class="nav-icon"><i class="ri-bar-chart-grouped-line"></i></div><span>Reports</span>
            </a>

        <?php else: ?>
            <div class="nav-section-label">Buyer Menu</div>
            <a href="<?= BASE_URL ?>/user/dashboard.php" class="nav-link <?= isActivePage('dashboard.php') ?>">
                <div class="nav-icon"><i class="ri-home-4-line"></i></div><span>Home</span>
            </a>
            <a href="<?= BASE_URL ?>/user/events.php" class="nav-link <?= isActivePage('events.php') ?>">
                <div class="nav-icon"><i class="ri-compass-3-line"></i></div><span>Browse Events</span>
            </a>
            <a href="<?= BASE_URL ?>/user/riwayat.php" class="nav-link <?= isActivePage('riwayat.php') ?>">
                <div class="nav-icon"><i class="ri-ticket-2-line"></i></div><span>My Tickets</span>
            </a>
        <?php endif; ?>
    </div>

    <div class="sidebar-footer">
        <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
            <button onclick="toggleTheme()" class="btn btn-ghost btn-sm flex-fill" style="justify-content: center;" id="theme-btn">
                <i class="ri-moon-line" id="theme-icon"></i> <span id="theme-text">Dark</span>
            </button>
        </div>
        <div style="padding: 0.6rem 1rem; margin-bottom: 0.5rem; background: rgba(255,255,255,0.03); border-radius: 10px; border: 1px solid var(--border);">
            <div style="font-size: 0.78rem; font-weight: 600; color: var(--text-primary); text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"><?= htmlspecialchars($user['nama']) ?></div>
            <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: capitalize;"><?= htmlspecialchars($user['role']) ?></div>
        </div>
        <a href="<?= BASE_URL ?>/logout.php" class="nav-link logout">
            <div class="nav-icon"><i class="ri-logout-box-r-line"></i></div><span>Logout</span>
        </a>
    </div>
</div>

<script>
(function() {
    window.updateThemeUI = function(theme) {
        const themeText = document.getElementById('theme-text');
        const themeIcon = document.getElementById('theme-icon');
        if (!themeText || !themeIcon) return;
        themeText.innerText = theme === 'light' ? 'Light' : 'Dark';
        themeIcon.className = theme === 'light' ? 'ri-sun-line' : 'ri-moon-line';
    };
    window.toggleTheme = function() {
        const html    = document.documentElement;
        const current = html.getAttribute('data-theme') || 'dark';
        const newTheme = current === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeUI(newTheme);
    };
    const initialTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', initialTheme);
    updateThemeUI(initialTheme);
})();
</script>
