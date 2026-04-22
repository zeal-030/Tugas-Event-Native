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
<div class="sidebar" id="main-sidebar">
    <div class="sidebar-toggle-btn" id="sidebar-toggle" title="Toggle Sidebar">
        <i class="ri-arrow-left-s-line"></i>
    </div>
    
    <div class="sidebar-content">
        <div class="sidebar-brand">
            <a href="<?= BASE_URL ?>/admin/dashboard.php" class="brand-logo">
                <div class="brand-icon" style="background:var(--gradient-primary); color:white;"><i class="ri-ticket-2-fill"></i></div>
                <div class="brand-info">
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

                <div class="nav-section-label section-op" style="margin-top: 1rem;">Operations</div>
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
            <div class="footer-actions">
                <button onclick="toggleTheme()" class="btn btn-ghost btn-sm flex-fill" id="theme-btn" title="Toggle Theme">
                    <i class="ri-moon-line" id="theme-icon"></i> <span id="theme-text">Dark</span>
                </button>
            </div>
            <div class="user-info-card">
                <div class="u-name"><?= htmlspecialchars($user['nama']) ?></div>
                <div class="u-role"><?= htmlspecialchars($user['role']) ?></div>
            </div>
            <a href="<?= BASE_URL ?>/logout.php" class="nav-link logout" title="Logout">
                <div class="nav-icon"><i class="ri-logout-box-r-line"></i></div><span>Logout</span>
            </a>
        </div>
    </div>
</div>

<script>
(function() {
    const sidebar = document.getElementById('main-sidebar');
    const toggleBtn = document.getElementById('sidebar-toggle');
    const isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';

    if (isCollapsed && sidebar) {
        sidebar.classList.add('collapsed');
        document.body.classList.add('sidebar-collapsed');
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const collapsed = sidebar.classList.toggle('collapsed');
            document.body.classList.toggle('sidebar-collapsed', collapsed);
            localStorage.setItem('sidebar-collapsed', collapsed);
        });
    }

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

<style>
/* Improved Collapsible Sidebar CSS */
:root {
    --sidebar-width: 260px;
    --sidebar-collapsed-width: 85px;
}

.sidebar {
    width: var(--sidebar-width);
    height: 100vh;
    position: fixed;
    top: 0; left: 0;
    background: var(--bg-surface);
    border-right: 1px solid var(--border);
    z-index: 1000;
    transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: visible !important; /* Allow toggle button to overflow */
}

.sidebar-content {
    height: 100%;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    overflow-x: hidden;
}

.sidebar.collapsed { width: var(--sidebar-collapsed-width); }

.sidebar-toggle-btn {
    position: absolute;
    top: 30px;
    right: -14px;
    width: 28px;
    height: 28px;
    background: var(--primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 1005;
    transition: all 0.3s ease;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}
.sidebar-toggle-btn:hover { transform: scale(1.1); }
.sidebar.collapsed .sidebar-toggle-btn { transform: rotate(180deg); }

/* Main Content Adjustment */
.main-content { transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1); margin-left: var(--sidebar-width); }
body.sidebar-collapsed .main-content { margin-left: var(--sidebar-collapsed-width); }

/* Hiding elements in collapsed state */
.sidebar.collapsed .brand-info,
.sidebar.collapsed .nav-section-label,
.sidebar.collapsed .nav-link span,
.sidebar.collapsed #theme-text,
.sidebar.collapsed .user-info-card {
    display: none !important;
}

/* Specific styling for collapsed items */
.sidebar.collapsed .sidebar-brand { padding: 1.5rem 0.5rem; justify-content: center; display: flex; }
.sidebar.collapsed .nav-link { padding: 0.8rem; justify-content: center; }
.sidebar.collapsed .nav-icon { margin: 0 !important; }
.sidebar.collapsed .footer-actions { padding: 0.5rem; justify-content: center; display: flex; }
.sidebar.collapsed .footer-actions .btn { width: 44px; height: 44px; padding: 0; justify-content: center; border-radius: 12px; }

/* Styling for non-collapsed elements to keep structure */
.footer-actions { padding: 1rem; display: flex; gap: 0.5rem; }
.user-info-card {
    margin: 0 1rem 0.5rem;
    padding: 0.75rem;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--border);
    border-radius: 12px;
}
.u-name { font-size: 0.8rem; font-weight: 700; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.u-role { font-size: 0.7rem; color: var(--text-muted); }

/* Ensure topnav shifts if it's fixed/sticky correctly */
.topnav { transition: all 0.3s ease; }

/* Custom Scrollbar for Sidebar */
.sidebar-content::-webkit-scrollbar { width: 4px; }
.sidebar-content::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.05); border-radius: 10px; }
</style>
