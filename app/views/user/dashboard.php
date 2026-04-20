<?php
/**
 * View: User Dashboard
 * Data dari UserDashboardController:
 *   $total_tickets, $total_spent, $active_events, $recent_tickets
 */
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard — <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body>

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="main-content">
    <div class="topnav">
        <div class="topnav-left">
            <span style="color: var(--text-muted); font-size: 0.8rem;">Welcome,</span>
            <span style="color: var(--text-primary); font-weight: 700; font-size: 0.9rem;"><?= htmlspecialchars($user['nama']) ?></span>
        </div>
        <div class="topnav-right">
            <a href="<?= BASE_URL ?>/user/events.php" class="btn btn-primary btn-sm">Explore Events</a>
        </div>
    </div>

    <div class="page-header">
        <div class="page-title">Personal Dashboard</div>
        <div class="page-subtitle">Ringkasan aktivitas dan tiket yang kamu miliki</div>
    </div>

    <div class="page-body">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card primary">
                    <div class="stat-icon primary"><i class="ri-ticket-2-line"></i></div>
                    <div class="stat-value"><?= number_format($total_tickets) ?></div>
                    <div class="stat-label">Tickets Owned</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card success">
                    <div class="stat-icon success"><i class="ri-calendar-check-line"></i></div>
                    <div class="stat-value"><?= number_format($active_events) ?></div>
                    <div class="stat-label">Events Joined</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card info">
                    <div class="stat-icon info"><i class="ri-wallet-3-line"></i></div>
                    <div class="stat-value" style="font-size:1.3rem;">Rp <?= number_format($total_spent ?? 0, 0, ',', '.') ?></div>
                    <div class="stat-label">Total Investment</div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-7">
                <div class="table-wrapper">
                    <div class="table-header">
                        <div class="table-title">🎟️ Your Recent Tickets</div>
                        <a href="<?= BASE_URL ?>/user/riwayat.php" class="btn btn-ghost btn-sm">See All</a>
                    </div>
                    <table class="table">
                        <thead><tr><th>Event</th><th>Type</th><th>Code</th></tr></thead>
                        <tbody>
                            <?php foreach ($recent_tickets as $tk): ?>
                            <tr>
                                <td style="color: var(--text-primary); font-weight: 600; font-size: 0.85rem;"><?= htmlspecialchars($tk['nama_event']) ?></td>
                                <td><span class="badge bg-secondary opacity-75" style="font-size: 0.65rem;"><?= htmlspecialchars($tk['nama_tiket']) ?></span></td>
                                <td><code><?= htmlspecialchars($tk['kode_tiket']) ?></code></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recent_tickets)): ?>
                            <tr><td colspan="3" class="text-center py-4 text-muted">You haven't bought any tickets yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-5">
                <div class="card p-4" style="background: var(--gradient-primary); border: none;">
                    <h5 class="fw-bold mb-3 text-white">Siap untuk petualangan baru?</h5>
                    <p class="small mb-4" style="color: rgba(255,255,255,0.75);">Temukan berbagai event menarik mulai dari konser, seminar, hingga workshop kreatif.</p>
                    <a href="<?= BASE_URL ?>/user/events.php" class="btn btn-light w-100 fw-bold">Cari Event Sekarang</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
