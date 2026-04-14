<?php 
require_once __DIR__ . '/../config/db.php'; 

// Cek folder project kita (biasanya /event-ku/)
$current_uri = $_SERVER['REQUEST_URI'];
$base_url = (strpos($current_uri, '/admin/venue/') !== false || strpos($current_uri, '/admin/event/') !== false || strpos($current_uri, '/admin/tiket/') !== false || strpos($current_uri, '/admin/voucher/') !== false) ? '../../' : '../';
?>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-brand">
        <a href="<?= $base_url ?>admin/dashboard.php" class="brand-logo">
            <div class="brand-icon">🎟️</div>
            <div>
                <div class="brand-name">EventTiket</div>
                <div class="brand-subtitle">ADMIN PANEL</div>
            </div>
        </a>
    </div>

    <div class="sidebar-nav">
        <?php if ($_SESSION['role'] == 'admin') : ?>
            <div class="nav-section-label">Main Menu</div>
            <a href="<?= $base_url ?>admin/dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                <div class="nav-icon">⚡</div>
                <span>Dashboard</span>
            </a>
            <a href="<?= $base_url ?>admin/venue/index.php" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/venue/') !== false ? 'active' : '' ?>">
                <div class="nav-icon">📍</div>
                <span>Venues</span>
            </a>
            <a href="<?= $base_url ?>admin/event/index.php" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/event/') !== false ? 'active' : '' ?>">
                <div class="nav-icon">📅</div>
                <span>Events</span>
            </a>
            <a href="<?= $base_url ?>admin/tiket/index.php" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/tiket/') !== false ? 'active' : '' ?>">
                <div class="nav-icon">🎫</div>
                <span>Tickets</span>
            </a>
            <a href="<?= $base_url ?>admin/voucher/index.php" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/voucher/') !== false ? 'active' : '' ?>">
                <div class="nav-icon">🎁</div>
                <span>Vouchers</span>
            </a>

            <div class="nav-section-label" style="margin-top: 1rem;">Operations</div>
            <a href="<?= $base_url ?>admin/checkin.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'checkin.php' ? 'active' : '' ?>">
                <div class="nav-icon">📲</div>
                <span>Check-in</span>
            </a>
            <a href="<?= $base_url ?>admin/scanner.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'scanner.php' ? 'active' : '' ?>">
                <div class="nav-icon">📷</div>
                <span>QR Scanner</span>
            </a>
            <a href="<?= $base_url ?>admin/laporan.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'active' : '' ?>">
                <div class="nav-icon">📊</div>
                <span>Reports</span>
            </a>
        <?php else : ?>
            <div class="nav-section-label">Buyer Menu</div>
            <a href="<?= $base_url ?>user/dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                <div class="nav-icon">🏠</div>
                <span>Home</span>
            </a>
            <a href="<?= $base_url ?>user/events.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'events.php' ? 'active' : '' ?>">
                <div class="nav-icon">🔍</div>
                <span>Browse Events</span>
            </a>
            <a href="<?= $base_url ?>user/riwayat.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'riwayat.php' ? 'active' : '' ?>">
                <div class="nav-icon">🎟️</div>
                <span>My Tickets</span>
            </a>
        <?php endif; ?>
    </div>

    <div class="sidebar-footer">
        <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
            <button onclick="toggleTheme()" class="btn btn-ghost btn-sm flex-fill" style="justify-content: center;" id="theme-btn">
                <i class="bi bi-moon-stars"></i> <span id="theme-text">Dark</span>
            </button>
        </div>
        <div style="padding: 0.6rem 1rem; margin-bottom: 0.5rem; background: rgba(255,255,255,0.03); border-radius: 10px; border: 1px solid var(--border);">
            <div style="font-size: 0.78rem; font-weight: 600; color: var(--text-primary); text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"><?= $_SESSION['nama'] ?? 'Admin' ?></div>
            <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: capitalize;"><?= $_SESSION['role'] ?? 'admin' ?></div>
        </div>
        <a href="<?= $base_url ?>logout.php" class="nav-link logout">
            <div class="nav-icon">🚪</div>
            <span>Logout</span>
        </a>
    </div>
</div>

<script>
(function() {
    /** 
     * Logic Mode Terang/Gelap
     * Dibungkus IIFE untuk menghindari konflik variabel (seperti savedTheme) 
     * yang mungkin sudah dideklarasikan di head.php
     */
    
    window.updateThemeUI = function(theme) {
        const themeText = document.getElementById('theme-text');
        const themeIcon = document.querySelector('#theme-btn i');
        if (!themeText || !themeIcon) return;

        if (theme === 'light') {
            themeText.innerText = 'Light';
            themeIcon.className = 'bi bi-sun';
        } else {
            themeText.innerText = 'Dark';
            themeIcon.className = 'bi bi-moon-stars';
        }
    }

    window.toggleTheme = function() {
        const html = document.documentElement;
        const currentTheme = html.getAttribute('data-theme') || 'dark';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeUI(newTheme);
    }

    // Jalankan update UI pertama kali berdasarkan yang tersimpan
    const initialTheme = localStorage.getItem('theme') || 'dark';
    updateThemeUI(initialTheme);
})();
</script>
