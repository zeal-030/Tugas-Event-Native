<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php"); exit;
}

$id_user = $_SESSION['id_user'];
// Statistik User
$total_tickets = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM attendee a JOIN order_detail od ON a.id_detail = od.id_detail JOIN orders o ON od.id_order = o.id_order WHERE o.id_user = $id_user"))['t'];
$total_spent   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total) as t FROM orders WHERE id_user = $id_user AND status = 'paid'"))['t'];
$active_events = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT t.id_event) as t FROM attendee a JOIN order_detail od ON a.id_detail = od.id_detail JOIN orders o ON od.id_order = o.id_order JOIN tiket t ON od.id_tiket = t.id_tiket WHERE o.id_user = $id_user"))['t'];

// Tiket Terbaru
$recent_tickets = query("SELECT a.kode_tiket, e.nama_event, e.tanggal, t.nama_tiket
                         FROM attendee a 
                         JOIN order_detail od ON a.id_detail = od.id_detail 
                         JOIN orders o ON od.id_order = o.id_order 
                         JOIN tiket t ON od.id_tiket = t.id_tiket
                         JOIN event e ON t.id_event = e.id_event
                         WHERE o.id_user = $id_user
                         ORDER BY o.tanggal_order DESC LIMIT 3");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Dashboard — EventTiket</title>
    <?php $is_sub = true; include '../includes/head.php'; ?>
</head>
<body>

<?php include '../includes/sidebar.php'; ?>

<div class="main-content">
    <div class="topnav">
        <div class="topnav-left">
            <span style="color: var(--text-muted); font-size: 0.8rem;">Welcome,</span>
            <span style="color: var(--text-primary); font-weight: 700; font-size: 0.9rem;"><?= $_SESSION['nama'] ?></span>
        </div>
        <div class="topnav-right">
            <a href="events.php" class="btn btn-primary btn-sm">Explore Events</a>
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
                    <div class="stat-icon primary"><i class="bi bi-ticket-perforated"></i></div>
                    <div class="stat-value"><?= number_format($total_tickets) ?></div>
                    <div class="stat-label">Tickets Owned</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card success">
                    <div class="stat-icon success"><i class="bi bi-calendar-check"></i></div>
                    <div class="stat-value"><?= number_format($active_events) ?></div>
                    <div class="stat-label">Events Joined</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card info">
                    <div class="stat-icon info"><i class="bi bi-wallet2"></i></div>
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
                        <a href="riwayat.php" class="btn btn-ghost btn-sm">See All</a>
                    </div>
                    <table class="table">
                        <thead>
                            <tr><th>Event</th><th>Type</th><th>Code</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_tickets as $tk): ?>
                            <tr>
                                <td style="color: var(--text-primary); font-weight: 600; font-size: 0.85rem;"><?= $tk['nama_event'] ?></td>
                                <td><span class="badge bg-secondary opacity-75" style="font-size: 0.65rem;"><?= $tk['nama_tiket'] ?></span></td>
                                <td><code><?= $tk['kode_tiket'] ?></code></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($recent_tickets)): ?>
                            <tr><td colspan="3" class="text-center py-4 text-muted">You haven't bought any tickets yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-5">
                <div class="card bg-gradient-primary text-white p-4" style="background: var(--gradient-primary); border: none;">
                    <h5 class="fw-bold mb-3">Siap untuk petualangan baru?</h5>
                    <p class="small opacity-75 mb-4">Temukan berbagai event menarik mulai dari konser, seminar, hingga workshop kreatif.</p>
                    <a href="events.php" class="btn btn-light w-100 fw-bold">Cari Event Sekarang</a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
