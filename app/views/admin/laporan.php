<?php
/**
 * View: Admin Laporan
 * Data dari LaporanController:
 *   $orders, $tickets_sold, $total_rev, $total_terjual, $total_orders, $paid_orders
 */
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan — <?= APP_NAME ?> Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <style>
        .rev-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 20px; padding: 1.5rem; position: relative; overflow: hidden; transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease; }
        .rev-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); border-color: var(--border-hover); }
        .rev-card-icon { width: 46px; height: 46px; border-radius: 13px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; margin-bottom: 1.1rem; }
        .rev-card-icon.success { background: rgba(16,185,129,0.15); color: #34d399; }
        .rev-card-icon.primary { background: rgba(124,58,237,0.15); color: var(--primary-light); }
        .rev-card-icon.info    { background: rgba(6,182,212,0.15);  color: var(--accent); }
        .rev-card-icon.warning { background: rgba(245,158,11,0.15); color: var(--accent-2); }
        .rev-card-value { font-size: 1.55rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.5px; line-height: 1.1; margin-bottom: 0.3rem; }
        .rev-card-label { font-size: 0.75rem; color: var(--text-muted); font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
        .rev-card-sub { font-size: 0.72rem; color: var(--text-muted); margin-top: 0.5rem; }
        .event-progress-bar  { height: 8px; background: rgba(255,255,255,0.05); border-radius: 20px; overflow: hidden; flex: 1; }
        .event-progress-fill { height: 100%; background: var(--gradient-primary); border-radius: 20px; transition: width 1s cubic-bezier(0.4,0,0.2,1); }
        .rank-badge { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 800; flex-shrink: 0; }
        .rank-1 { background: rgba(245,158,11,0.2); color: #f59e0b; }
        .rank-2 { background: rgba(148,163,184,0.15); color: #94a3b8; }
        .rank-3 { background: rgba(180,120,80,0.15); color: #b47841; }
        .rank-other { background: rgba(255,255,255,0.04); color: var(--text-muted); }
        .ticket-tag { background: rgba(124,58,237,0.12); color: var(--primary-light); padding: 0.22rem 0.7rem; border-radius: 20px; font-size: 0.78rem; font-weight: 700; }
        .filter-bar { display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; }
        .filter-bar .form-control, .filter-bar .form-select { background: var(--bg-elevated); border: 1px solid var(--border); color: var(--text-primary); border-radius: 10px; padding: 0.45rem 0.9rem; font-size: 0.82rem; }
        .filter-bar .form-control { min-width: 200px; }
        @media print { .sidebar, .topnav, .btn { display: none !important; } .main-content { margin-left: 0 !important; padding: 0 !important; } }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="main-content">
    <div class="topnav">
        <div class="topnav-left">
            <i class="ri-bar-chart-grouped-line" style="font-size:1.1rem; color:var(--primary-light);"></i>
            <span style="font-size:0.85rem; font-weight:600; color:var(--text-primary);">Financial Reports</span>
        </div>
        <div class="topnav-right d-flex gap-3 align-items-center">
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>/admin/export_excel.php" class="btn btn-success btn-sm">
                    <i class="ri-file-excel-2-fill"></i> Export Excel
                </a>
                <button onclick="window.print()" class="btn btn-ghost btn-sm">
                    <i class="ri-printer-line"></i> Cetak PDF
                </button>
            </div>
            <div style="width: 1px; height: 30px; background: var(--border);"></div>
            <div class="user-badge">
                <div class="user-avatar"><?= strtoupper(substr($user['nama'], 0, 1)) ?></div>
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars($user['nama']) ?></div>
                    <div class="user-role"><?= htmlspecialchars($user['role']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-header">
        <div class="page-title"><i class="ri-bar-chart-grouped-fill" style="color:var(--primary-light);"></i> Laporan Keuangan</div>
        <div class="page-subtitle">Ringkasan transaksi, penjualan tiket, dan analitik revenue event</div>
    </div>

    <div class="page-body">
        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <?php
            $cards = [
                ['success', 'ri-money-dollar-circle-fill',  'Rp ' . number_format($total_rev, 0, ',', '.'), 'Total Revenue',    'Hanya dari order berstatus Paid'],
                ['primary', 'ri-ticket-2-fill',              number_format($total_terjual),                  'Tiket Terjual',    count($tickets_sold) . ' event aktif memiliki penjualan'],
                ['info',    'ri-receipt-line',               $total_orders,                                  'Total Transaksi',  "$paid_orders transaksi berhasil (paid)"],
                ['warning', 'ri-line-chart-line',            ($total_orders > 0 ? round($paid_orders / $total_orders * 100) : 0) . '%', 'Conversion Rate', 'Rasio transaksi paid vs total order'],
            ];
            foreach ($cards as [$color, $icon, $value, $label, $sub]):
            ?>
            <div class="col-6 col-lg-3">
                <div class="rev-card">
                    <div class="rev-card-icon <?= $color ?>"><i class="<?= $icon ?>"></i></div>
                    <div class="rev-card-value"><?= $value ?></div>
                    <div class="rev-card-label"><?= $label ?></div>
                    <div class="rev-card-sub"><?= $sub ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Ticket Sales per Event -->
        <div class="table-wrapper mb-4">
            <div class="table-header">
                <div class="table-title"><i class="ri-trophy-fill" style="color:#fbbf24;"></i> Penjualan Tiket per Event</div>
                <span style="font-size:0.75rem; color:var(--text-muted);"><?= count($tickets_sold) ?> event</span>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:36px;">#</th>
                        <th>Nama Event</th>
                        <th>Tiket Terjual</th>
                        <th>Revenue</th>
                        <th style="min-width:160px;">Porsi Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets_sold as $i => $ts):
                        $pct = $total_rev > 0 ? round($ts['revenue'] / $total_rev * 100) : 0;
                        $rankClass = match(true) { $i === 0 => 'rank-1', $i === 1 => 'rank-2', $i === 2 => 'rank-3', default => 'rank-other' };
                    ?>
                    <tr>
                        <td><div class="rank-badge <?= $rankClass ?>"><?= $i + 1 ?></div></td>
                        <td style="font-weight: 600; color: var(--text-primary);"><?= htmlspecialchars($ts['nama_event']) ?></td>
                        <td><span class="ticket-tag"><?= number_format($ts['total_terjual']) ?> tkts</span></td>
                        <td style="color: #34d399; font-weight: 700;">Rp <?= number_format($ts['revenue'], 0, ',', '.') ?></td>
                        <td>
                            <div style="display:flex; align-items:center; gap:0.75rem;">
                                <div class="event-progress-bar"><div class="event-progress-fill" style="width:<?= $pct ?>%;"></div></div>
                                <span style="font-size:0.75rem; color:var(--text-primary); font-weight:700; width:35px; text-align:right; flex-shrink:0;"><?= $pct ?>%</span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($tickets_sold)): ?>
                    <tr><td colspan="5" style="text-align:center; padding:3rem; color:var(--text-muted);">Belum ada penjualan tiket</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Transaction History -->
        <div class="table-wrapper">
            <div class="table-header">
                <div class="table-title"><i class="ri-history-fill" style="color:var(--primary-light);"></i> Riwayat Transaksi</div>
                <div class="filter-bar">
                    <input type="text" id="search-tx" class="form-control" placeholder="🔍 Cari nama / order ID..." oninput="filterTable()">
                    <select id="filter-status" class="form-select" style="width:auto;" onchange="filterTable()">
                        <option value="">Semua Status</option>
                        <option value="paid">Paid</option>
                        <option value="pending">Pending</option>
                        <option value="cancel">Cancel</option>
                    </select>
                </div>
            </div>
            <div style="overflow-x:auto;">
                <table class="table" id="tx-table">
                    <thead>
                        <tr><th>Order ID</th><th>Customer</th><th>Tanggal</th><th>Voucher</th><th>Total</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $o): ?>
                        <tr data-status="<?= $o['status'] ?>" data-name="<?= strtolower(htmlspecialchars($o['nama'])) ?>" data-id="<?= $o['id_order'] ?>">
                            <td><code>#ORD-<?= $o['id_order'] ?></code></td>
                            <td style="font-weight:500; color:var(--text-primary);"><?= htmlspecialchars($o['nama']) ?></td>
                            <td style="font-size:0.82rem; color:var(--text-secondary);"><?= date('d/m/Y H:i', strtotime($o['tanggal_order'])) ?></td>
                            <td>
                                <?php if ($o['kode_voucher']): ?>
                                <span style="font-family:'Courier New',monospace; font-size:0.78rem; color:var(--accent-2); background:rgba(245,158,11,0.08); padding:0.2rem 0.5rem; border-radius:6px;"><?= htmlspecialchars($o['kode_voucher']) ?></span>
                                <?php else: ?><span style="color:var(--text-muted);">—</span><?php endif; ?>
                            </td>
                            <td style="font-weight:700; color:var(--primary-light);">Rp <?= number_format($o['total'], 0, ',', '.') ?></td>
                            <td><span class="badge-status badge-<?= $o['status'] ?>"><?= strtoupper($o['status']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($orders)): ?>
                        <tr><td colspan="6" style="text-align:center; padding:3rem; color:var(--text-muted);">Belum ada transaksi</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div id="tx-empty" style="display:none; text-align:center; padding:2.5rem; color:var(--text-muted);">
                Tidak ada transaksi yang cocok dengan filter
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function filterTable() {
    const search = document.getElementById('search-tx').value.toLowerCase().trim();
    const status = document.getElementById('filter-status').value.toLowerCase();
    const rows   = document.querySelectorAll('#tx-table tbody tr[data-status]');
    let visible  = 0;
    rows.forEach(row => {
        const matchSearch = !search || row.dataset.name.includes(search) || row.dataset.id.includes(search);
        const matchStatus = !status || row.dataset.status === status;
        row.style.display = (matchSearch && matchStatus) ? '' : 'none';
        if (matchSearch && matchStatus) visible++;
    });
    document.getElementById('tx-empty').style.display = visible === 0 ? 'block' : 'none';
}
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.event-progress-fill').forEach(bar => {
        const w = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => { bar.style.width = w; }, 150);
    });
});
</script>
</body>
</html>
