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

        <div class="row g-3">
            <!-- Recent Transactions -->
            <div class="col-md-8">
                <div class="table-wrapper">
                    <div class="table-header">
                        <div class="table-title">🧾 Recent Transactions</div>
                        <a href="laporan.php" class="btn btn-ghost btn-sm">View All</a>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $o) : ?>
                            <tr>
                                <td><code>#ORD-<?= $o['id_order'] ?></code></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.6rem;">
                                        <div style="width: 30px; height: 30px; background: var(--gradient-primary); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; color: white; flex-shrink: 0;">
                                            <?= strtoupper(substr($o['nama'], 0, 1)) ?>
                                        </div>
                                        <span style="color: var(--text-primary); font-weight: 500;"><?= $o['nama'] ?></span>
                                    </div>
                                </td>
                                <td style="color: var(--primary-light); font-weight: 600;">Rp <?= number_format($o['total'], 0, ',', '.') ?></td>
                                <td><?= date('d M, H:i', strtotime($o['tanggal_order'])) ?></td>
                                <td>
                                    <span class="badge-status badge-<?= $o['status'] ?>">
                                        <?= strtoupper($o['status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recent_orders)) : ?>
                            <tr><td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-muted);">No transactions yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Upcoming Events -->
            <div class="col-md-4">
                <div class="card" style="height: 100%;">
                    <div class="card-header-custom">
                        <div class="card-title">📅 Upcoming Events</div>
                        <a href="event/index.php" style="font-size:0.78rem; color: var(--primary-light); text-decoration: none;">View all</a>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <?php foreach ($recent_events as $i => $e) :
                            $colors = ['#7c3aed','#06b6d4','#10b981','#f59e0b'];
                            $c = $colors[$i % count($colors)];
                        ?>
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="width: 42px; height: 42px; background: <?= $c ?>22; border: 1px solid <?= $c ?>44; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; color: <?= $c ?>;">
                                📅
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <div style="font-size: 0.85rem; font-weight: 600; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= $e['nama_event'] ?></div>
                                <div style="font-size: 0.72rem; color: var(--text-muted);"><?= date('d M Y', strtotime($e['tanggal'])) ?> · <?= $e['nama_venue'] ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($recent_events)) : ?>
                        <div style="text-align: center; padding: 2rem; color: var(--text-muted); font-size: 0.875rem;">No events yet</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
