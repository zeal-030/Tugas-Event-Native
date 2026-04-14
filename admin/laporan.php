<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') { header("Location: ../login.php"); exit; }

$orders       = query("SELECT o.*, u.nama, v.kode_voucher FROM orders o JOIN users u ON o.id_user=u.id_user LEFT JOIN voucher v ON o.id_voucher=v.id_voucher ORDER BY o.tanggal_order DESC");
$tickets_sold = query("SELECT e.nama_event, SUM(od.qty) AS total_terjual, SUM(od.subtotal) as revenue
                       FROM event e JOIN tiket t ON t.id_event=e.id_event
                       JOIN order_detail od ON od.id_tiket=t.id_tiket
                       JOIN orders o ON o.id_order=od.id_order
                       WHERE o.status='paid' GROUP BY e.id_event ORDER BY total_terjual DESC");
$total_rev    = array_sum(array_column($tickets_sold, 'revenue'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports — EventTiket Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php require_once '../includes/sidebar.php'; ?>
<div class="main-content">
    <div class="topnav">
        <div class="topnav-left">
            <span style="font-size:0.85rem; font-weight:600; color:var(--text-primary);">Financial Reports</span>
        </div>
        <div class="topnav-right">
            <button onclick="window.print()" class="btn btn-ghost btn-sm"><i class="bi bi-printer"></i> Print</button>
        </div>
    </div>
    <div class="page-header">
        <div class="page-title">📊 Financial Reports</div>
        <div class="page-subtitle">Laporan transaksi dan analitik penjualan tiket</div>
    </div>
    <div class="page-body">
        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card success">
                    <div class="stat-icon success"><i class="bi bi-currency-dollar"></i></div>
                    <div class="stat-value" style="font-size:1.3rem;">Rp <?= number_format($total_rev,0,',','.') ?></div>
                    <div class="stat-label">Total Revenue (Paid)</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card primary">
                    <div class="stat-icon primary"><i class="bi bi-ticket-detailed-fill"></i></div>
                    <div class="stat-value"><?= array_sum(array_column($tickets_sold, 'total_terjual')) ?></div>
                    <div class="stat-label">Total Tickets Sold</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card info">
                    <div class="stat-icon info"><i class="bi bi-receipt"></i></div>
                    <div class="stat-value"><?= count($orders) ?></div>
                    <div class="stat-label">Total Transactions</div>
                </div>
            </div>
        </div>

        <!-- Tickets per Event -->
        <div class="table-wrapper mb-4">
            <div class="table-header">
                <div class="table-title">🏆 Ticket Sales per Event</div>
            </div>
            <table class="table">
                <thead>
                    <tr><th>Event</th><th>Tickets Sold</th><th>Revenue</th><th>Share</th></tr>
                </thead>
                <tbody>
                    <?php foreach($tickets_sold as $ts): $pct = $total_rev > 0 ? round($ts['revenue'] / $total_rev * 100) : 0; ?>
                    <tr>
                        <td style="color:var(--text-primary); font-weight:600;"><?= $ts['nama_event'] ?></td>
                        <td>
                            <span style="background:rgba(124,58,237,0.15); color:var(--primary-light); padding:0.25rem 0.75rem; border-radius:20px; font-size:0.82rem; font-weight:700;">
                                <?= number_format($ts['total_terjual']) ?> tkts
                            </span>
                        </td>
                        <td style="color:#34d399; font-weight:700;">Rp <?= number_format($ts['revenue'],0,',','.') ?></td>
                        <td style="min-width:120px;">
                            <div style="display:flex; align-items:center; gap:0.5rem;">
                                <div style="flex:1; height:6px; background:rgba(255,255,255,0.05); border-radius:10px; overflow:hidden;">
                                    <div style="width:<?= $pct ?>%; height:100%; background:var(--gradient-primary); border-radius:10px;"></div>
                                </div>
                                <span style="font-size:0.75rem; color:var(--text-muted); width:28px; text-align:right;"><?= $pct ?>%</span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($tickets_sold)): ?>
                    <tr><td colspan="4" style="text-align:center;padding:2rem;color:var(--text-muted);">No paid orders yet</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- All Transactions -->
        <div class="table-wrapper">
            <div class="table-header">
                <div class="table-title">🧾 Transaction History</div>
            </div>
            <table class="table">
                <thead>
                    <tr><th>Order ID</th><th>Customer</th><th>Date</th><th>Voucher</th><th>Total</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php foreach($orders as $o): ?>
                    <tr>
                        <td><code>#ORD-<?= $o['id_order'] ?></code></td>
                        <td style="color:var(--text-primary); font-weight:500;"><?= $o['nama'] ?></td>
                        <td style="font-size:0.82rem;"><?= date('d/m/Y H:i', strtotime($o['tanggal_order'])) ?></td>
                        <td>
                            <?php if($o['kode_voucher']): ?>
                            <span style="font-family:'Courier New',monospace; font-size:0.78rem; color:var(--accent-2);"><?= $o['kode_voucher'] ?></span>
                            <?php else: ?><span style="color:var(--text-muted);">—</span><?php endif; ?>
                        </td>
                        <td style="color:var(--primary-light); font-weight:700;">Rp <?= number_format($o['total'],0,',','.') ?></td>
                        <td><span class="badge-status badge-<?= $o['status'] ?>"><?= strtoupper($o['status']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
