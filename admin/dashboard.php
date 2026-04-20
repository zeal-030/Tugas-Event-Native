<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); exit;
}

$total_users    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM users WHERE role = 'user'"))['t'];
$total_orders   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM orders"))['t'];
$total_revenue  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(total),0) as t FROM orders WHERE status = 'paid'"))['t'];
$total_events   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM event"))['t'];
$total_checkins = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM attendee WHERE status_checkin = 'sudah'"))['t'];

$recent_orders = query("SELECT o.*, u.nama FROM orders o JOIN users u ON o.id_user = u.id_user ORDER BY tanggal_order DESC LIMIT 6");
$recent_events = query("SELECT e.*, v.nama_venue FROM event e JOIN venue v ON e.id_venue = v.id_venue ORDER BY tanggal DESC LIMIT 4");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — EventTiket Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php require_once '../includes/sidebar.php'; ?>

<div class="main-content">
    <!-- Top Nav -->
    <div class="topnav">
        <div class="topnav-left">
            <span style="color: var(--text-muted); font-size: 0.8rem;">Welcome back,</span>
            <span style="color: var(--text-primary); font-weight: 700; font-size: 0.9rem;"><?= $_SESSION['nama'] ?></span>
        </div>
        <div class="topnav-right">
            <div style="font-size: 0.78rem; color: var(--text-muted);"><?= date('l, d F Y') ?></div>
            <div class="user-badge">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?></div>
                <div class="user-info">
                    <div class="user-name"><?= $_SESSION['nama'] ?></div>
                    <div class="user-role"><?= $_SESSION['role'] ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="page-title">Dashboard Overview</div>
                <div class="page-subtitle">Monitor performa dan statistik sistem real-time</div>
            </div>
            <a href="laporan.php" class="btn btn-ghost btn-sm">
                <i class="bi bi-download"></i> Export Report
            </a>
        </div>
    </div>

    <div class="page-body">
        <!-- Stat Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="stat-card primary">
                    <div class="stat-icon primary"><i class="bi bi-people-fill"></i></div>
                    <div class="stat-value"><?= number_format($total_users) ?></div>
                    <div class="stat-label">Total Users</div>
                    <div class="stat-change up"><i class="bi bi-arrow-up"></i> Registered users</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-card success">
                    <div class="stat-icon success"><i class="bi bi-bag-check-fill"></i></div>
                    <div class="stat-value"><?= number_format($total_orders) ?></div>
                    <div class="stat-label">Total Orders</div>
                    <div class="stat-change up"><i class="bi bi-arrow-up"></i> All time orders</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-card info">
                    <div class="stat-icon info"><i class="bi bi-currency-exchange"></i></div>
                    <div class="stat-value" style="font-size: 1.3rem;">Rp<?= number_format($total_revenue, 0, ',', '.') ?></div>
                    <div class="stat-label">Total Revenue</div>
                    <div class="stat-change up"><i class="bi bi-arrow-up"></i> From paid orders</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-card warning">
                    <div class="stat-icon warning"><i class="bi bi-calendar-event-fill"></i></div>
                    <div class="stat-value"><?= number_format($total_events) ?></div>
                    <div class="stat-label">Active Events</div>
                    <div class="stat-change"><i class="bi bi-check-circle"></i> <?= $total_checkins ?> checked-in</div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-8">
                <div class="table-wrapper p-4">
                    <h5 class="fw-bold mb-4">Revenue Trend (Last 7 Days)</h5>
                    <div style="height: 250px;"><canvas id="revenueChart"></canvas></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="table-wrapper p-4">
                    <h5 class="fw-bold mb-4">Distribution</h5>
                    <div style="height: 250px;"><canvas id="distChart"></canvas></div>
                </div>
            </div>
        </div>
        <!-- Recent Transactions & Upcoming Events -->
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="table-wrapper" style="background: var(--bg-surface) !important;">
                    <div class="table-header">
                        <div class="table-title">🧾 Recent Transactions</div>
                        <a href="laporan.php" class="btn btn-ghost btn-sm">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0" style="background: transparent !important;">
                            <thead>
                                <tr style="background: transparent !important; border-bottom: 1px solid var(--border) !important;">
                                    <th style="background: transparent !important; border: none;">Order</th>
                                    <th style="background: transparent !important; border: none;">Customer</th>
                                    <th style="background: transparent !important; border: none;">Total</th>
                                    <th style="background: transparent !important; border: none;">Status</th>
                                </tr>
                            </thead>
                            <tbody style="background: transparent !important;">
                                <?php foreach ($recent_orders as $o) : ?>
                                <tr style="background: transparent !important;">
                                    <td style="background: transparent !important; border: none;"><code>#ORD-<?= $o['id_order'] ?></code></td>
                                    <td class="text-white" style="background: transparent !important; border: none;"><?= $o['nama'] ?></td>
                                    <td class="text-primary fw-bold" style="background: transparent !important; border: none;">Rp <?= number_format($o['total'], 0, ',', '.') ?></td>
                                    <td style="background: transparent !important; border: none;"><span class="badge-status badge-<?= $o['status'] ?>"><?= strtoupper($o['status']) ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card p-4" style="height: 100%; background: var(--bg-surface) !important; border: 1px solid var(--border) !important;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0 text-white">📅 Events</h5>
                        <a href="event/index.php" class="small text-decoration-none">View all</a>
                    </div>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($recent_events as $e) : ?>
                        <div class="d-flex align-items-center gap-3 p-3 rounded-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">
                            <div class="stat-icon primary small" style="width:35px; height:35px; font-size:0.8rem; background: var(--gradient-primary); color: white;">📅</div>
                            <div class="overflow-hidden">
                                <div class="text-white fw-600 small text-truncate"><?= $e['nama_event'] ?></div>
                                <div class="text-muted" style="font-size:0.75rem; color: #8e8ea8 !important;"><?= date('d M', strtotime($e['tanggal'])) ?> · <?= $e['nama_venue'] ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Revenue Chart
    const revCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revCtx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Revenue',
                data: [1200000, 1900000, 3000000, 5000000, 2300000, 7000000, 9000000],
                borderColor: '#7c3aed',
                backgroundColor: 'rgba(124, 58, 237, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { display: false }, x: { grid: { display: false } } }
        }
    });

    // Distribution Chart
    const distCtx = document.getElementById('distChart').getContext('2d');
    new Chart(distCtx, {
        type: 'doughnut',
        data: {
            labels: ['Concerts', 'Seminar', 'Workshop'],
            datasets: [{
                data: [55, 25, 20],
                backgroundColor: ['#7c3aed', '#06b6d4', '#10b981'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { color: '#8e8ea8', padding: 20 } } }
        }
    });
</script>
</body>
</html>
