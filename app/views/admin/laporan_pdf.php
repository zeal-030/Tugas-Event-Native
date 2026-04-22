<?php
/**
 * View: Admin Laporan PDF (Print Friendly)
 */
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan_Keuangan_<?= date('Ymd_His') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4F46E5;
            --secondary: #64748B;
            --success: #10B981;
            --dark: #0F172A;
            --light: #F8FAFC;
            --border: #E2E8F0;
        }
        * { box-sizing: border-box; -webkit-print-color-adjust: exact; }
        body { 
            font-family: 'Inter', sans-serif; 
            color: var(--dark); 
            background: white; 
            margin: 0; 
            padding: 100px 40px 40px; 
            line-height: 1.5;
            font-size: 13px;
        }
        @page { size: A4; margin: 0; }
        
        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: flex-start; 
            border-bottom: 2px solid var(--primary);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo-area { display: flex; align-items: center; gap: 12px; }
        .logo-icon { 
            width: 40px; height: 40px; 
            background: var(--primary); 
            border-radius: 10px; 
            display: flex; align-items: center; justify-content: center; 
            color: white; font-size: 20px;
        }
        .logo-text { font-size: 22px; font-weight: 800; color: var(--primary); letter-spacing: -0.5px; }
        
        .report-info { text-align: right; }
        .report-title { font-size: 18px; font-weight: 800; text-transform: uppercase; margin: 0; }
        .report-meta { font-size: 11px; color: var(--secondary); margin-top: 4px; }

        .summary-grid { 
            display: grid; 
            grid-template-columns: repeat(4, 1fr); 
            gap: 20px; 
            margin-bottom: 40px;
        }
        .stat-card { 
            background: var(--light); 
            padding: 15px; 
            border-radius: 12px; 
            border: 1px solid var(--border);
        }
        .stat-label { font-size: 10px; font-weight: 700; color: var(--secondary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px; }
        .stat-value { font-size: 18px; font-weight: 800; color: var(--dark); }

        .section-title { 
            font-size: 14px; 
            font-weight: 700; 
            margin-bottom: 15px; 
            display: flex; 
            align-items: center; 
            gap: 8px; 
            color: var(--primary);
        }
        .section-title i { font-size: 16px; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { 
            background: var(--light); 
            text-align: left; 
            padding: 10px 12px; 
            font-size: 11px; 
            font-weight: 700; 
            color: var(--secondary);
            text-transform: uppercase;
            border-bottom: 1px solid var(--border);
        }
        td { 
            padding: 12px; 
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        
        .badge { 
            padding: 4px 8px; 
            border-radius: 6px; 
            font-size: 10px; 
            font-weight: 700; 
            text-transform: uppercase;
        }
        .badge-paid { background: #DCFCE7; color: #166534; }
        .badge-pending { background: #FEF9C3; color: #854D0E; }
        .badge-cancel { background: #FEE2E2; color: #991B1B; }

        .event-perf-row { display: flex; align-items: center; gap: 15px; }
        .progress-bg { flex: 1; height: 6px; background: #F1F5F9; border-radius: 10px; overflow: hidden; }
        .progress-fill { height: 100%; background: var(--primary); border-radius: 10px; }

        .footer { 
            margin-top: 50px; 
            border-top: 1px solid var(--border); 
            padding-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10px;
            color: var(--secondary);
        }

        .signatures {
            margin-top: 60px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 100px;
            text-align: center;
        }
        .sig-box { padding-top: 80px; border-top: 1px solid #000; display: inline-block; width: 200px; }
        
        @media print {
            body { padding: 40px !important; }
            .no-print { display: none !important; }
        }
        
        .no-print-bar {
            position: fixed;
            top: 0; left: 0; right: 0;
            background: #1e293b;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
        }
        .btn-print {
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>
<body>
    <div class="no-print-bar no-print">
        <span style="font-weight: 600;">PDF Report Preview</span>
        <button onclick="window.print()" class="btn-print">
            <i class="ri-printer-line"></i> Cetak ke PDF
        </button>
    </div>

    <div class="header">
        <div class="logo-area">
            <div class="logo-icon"><i class="ri-ticket-2-fill"></i></div>
            <div class="logo-text">E-Tiket</div>
        </div>
        <div class="report-info">
            <h1 class="report-title">Financial Performance Report</h1>
            <div class="report-meta">
                Generated by <?= htmlspecialchars($user['nama']) ?> &bull; <?= date('d F Y, H:i') ?>
            </div>
        </div>
    </div>

    <div class="summary-grid">
        <div class="stat-card">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">Rp <?= number_format($total_rev, 0, ',', '.') ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Tickets Sold</div>
            <div class="stat-value"><?= number_format($total_terjual) ?> Units</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Orders</div>
            <div class="stat-value"><?= number_format($total_orders) ?> Txns</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Paid Rate</div>
            <div class="stat-value"><?= ($total_orders > 0 ? round($paid_orders / $total_orders * 100) : 0) ?>%</div>
        </div>
    </div>

    <div class="section-title">
        <i class="ri-pie-chart-2-line"></i> Event Performance Breakdown
    </div>
    <table>
        <thead>
            <tr>
                <th style="width: 40px;">Rank</th>
                <th>Event Name</th>
                <th style="text-align: right;">Tickets</th>
                <th style="text-align: right;">Revenue</th>
                <th style="width: 180px;">Revenue Contribution</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tickets_sold as $i => $ts): 
                $pct = $total_rev > 0 ? round($ts['revenue'] / $total_rev * 100) : 0;
            ?>
            <tr>
                <td style="font-weight: 700; color: var(--secondary);">#<?= $i + 1 ?></td>
                <td style="font-weight: 600;"><?= htmlspecialchars($ts['nama_event']) ?></td>
                <td style="text-align: right;"><?= number_format($ts['total_terjual']) ?></td>
                <td style="text-align: right; font-weight: 700;">Rp <?= number_format($ts['revenue'], 0, ',', '.') ?></td>
                <td>
                    <div class="event-perf-row">
                        <div class="progress-bg"><div class="progress-fill" style="width: <?= $pct ?>%;"></div></div>
                        <span style="font-size: 10px; font-weight: 700; width: 30px;"><?= $pct ?>%</span>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="section-title">
        <i class="ri-history-line"></i> Recent Transactions Review
    </div>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer Name</th>
                <th>Date / Time</th>
                <th style="text-align: right;">Amount</th>
                <th style="text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (array_slice($orders, 0, 15) as $o): ?>
            <tr>
                <td style="color: var(--secondary); font-family: monospace;">#ORD-<?= $o['id_order'] ?></td>
                <td style="font-weight: 500;"><?= htmlspecialchars($o['nama']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($o['tanggal_order'])) ?></td>
                <td style="text-align: right; font-weight: 600;">Rp <?= number_format($o['total'], 0, ',', '.') ?></td>
                <td style="text-align: center;">
                    <span class="badge badge-<?= $o['status'] ?>"><?= $o['status'] ?></span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="signatures">
        <div></div>
        <div>
            <p style="margin-bottom: 80px;">Approved by,</p>
            <div class="sig-box">
                <span style="font-weight: 700; font-size: 14px;"><?= htmlspecialchars($user['nama']) ?></span><br>
                <span style="font-size: 10px; color: var(--secondary);">Administrator</span>
            </div>
        </div>
    </div>

    <div class="footer">
        <div>&copy; <?= date('Y') ?> E-Tiket Management Platform &bull; Professional Edition</div>
        <div>Page 1 of 1</div>
    </div>

    <script>
        // Automatic print prompt (optional)
        // window.onload = () => { setTimeout(() => { window.print(); }, 500); }
    </script>
</body>
</html>
