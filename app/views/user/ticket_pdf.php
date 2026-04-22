<?php
/**
 * View: User Ticket PDF (Concert Style)
 * Data dari UserDashboardController::downloadTicket(): $ticket
 */
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($ticket['kode_tiket']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ticket_<?= $ticket['kode_tiket'] ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --secondary: #4f46e5;
            --accent: #f472b6;
            --bg: #0f172a;
            --card: #1e293b;
            --text: #f8fafc;
            --text-muted: #94a3b8;
        }
        * { box-sizing: border-box; -webkit-print-color-adjust: exact; }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: #f1f5f9; 
            margin: 0; padding: 40px; 
            display: flex; justify-content: center; align-items: center; min-height: 100vh;
        }

        .ticket-container {
            width: 850px;
            background: var(--bg);
            border-radius: 24px;
            display: flex;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
        }

        /* Ticket Side Cutouts */
        .ticket-container::before, .ticket-container::after {
            content: '';
            position: absolute;
            left: 600px;
            width: 40px; height: 40px;
            background: #f1f5f9;
            border-radius: 50%;
            z-index: 10;
        }
        .ticket-container::before { top: -20px; }
        .ticket-container::after { bottom: -20px; }

        .ticket-main {
            width: 620px;
            padding: 40px;
            position: relative;
            border-right: 2px dashed rgba(255,255,255,0.1);
        }

        .ticket-stub {
            width: 230px;
            background: rgba(255,255,255,0.03);
            padding: 40px 25px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            text-align: center;
        }

        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; }
        .brand { display: flex; align-items: center; gap: 10px; }
        .brand-icon { width: 32px; height: 32px; background: var(--primary); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; }
        .brand-name { font-weight: 800; font-size: 20px; color: white; letter-spacing: -0.5px; }
        
        .ticket-type-pill {
            background: rgba(244, 114, 182, 0.15);
            color: var(--accent);
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: 1px solid rgba(244, 114, 182, 0.3);
        }

        .event-name { font-size: 36px; font-weight: 800; color: white; line-height: 1.1; margin-bottom: 25px; letter-spacing: -1.5px; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .info-label { font-size: 10px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 6px; }
        .info-value { font-size: 15px; font-weight: 700; color: white; }

        .qr-area { background: white; padding: 12px; border-radius: 16px; margin-bottom: 20px; }
        .qr-code { width: 140px; height: 140px; display: block; }

        .stub-kode { font-family: 'JetBrains Mono', monospace; font-size: 14px; color: var(--primary); font-weight: 700; letter-spacing: 1px; margin-top: 10px; }
        .stub-label { font-size: 9px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-top: 5px; }

        .watermark {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 120px;
            font-weight: 900;
            color: rgba(255,255,255,0.02);
            pointer-events: none;
            white-space: nowrap;
            z-index: 0;
        }

        .print-bar {
            position: fixed; top: 0; left: 0; right: 0;
            background: #1e293b; color: white; padding: 15px 40px;
            display: flex; justify-content: space-between; align-items: center;
            z-index: 1000;
        }
        .btn-print {
            background: var(--primary); color: white; border: none; padding: 10px 24px;
            border-radius: 10px; font-weight: 700; cursor: pointer;
            display: flex; align-items: center; gap: 10px; transition: all 0.2s;
        }
        .btn-print:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3); }

        @media print {
            body { background: white; padding: 0; }
            .print-bar { display: none; }
            .ticket-container { box-shadow: none; border: 1px solid #eee; margin: 50px auto; }
            .ticket-container::before, .ticket-container::after { background: white; border: 1px solid #eee; }
        }
    </style>
</head>
<body>

<div class="print-bar">
    <div style="display: flex; align-items: center; gap: 15px;">
        <i class="ri-ticket-2-fill" style="font-size: 24px; color: var(--primary);"></i>
        <div style="line-height: 1.2;">
            <div style="font-weight: 700; font-size: 14px;">Ticket Ready to Print</div>
            <div style="font-size: 11px; color: var(--text-muted);"><?= $ticket['kode_tiket'] ?> &bull; <?= $ticket['nama_user'] ?></div>
        </div>
    </div>
    <button onclick="window.print()" class="btn-print">
        <i class="ri-printer-line"></i> Save as PDF / Print
    </button>
</div>

<div class="ticket-container">
    <div class="watermark">E-TICKET</div>
    
    <div class="ticket-main">
        <div class="header">
            <div class="brand">
                <div class="brand-icon"><i class="ri-ticket-2-fill"></i></div>
                <div class="brand-name">E-Tiket</div>
            </div>
            <div class="ticket-type-pill"><?= htmlspecialchars($ticket['nama_tiket']) ?></div>
        </div>

        <h1 class="event-name"><?= htmlspecialchars($ticket['nama_event']) ?></h1>

        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Date & Time</div>
                <div class="info-value"><?= date('D, d F Y', strtotime($ticket['tanggal'])) ?></div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Gate opens at 17:00 WIB</div>
            </div>
            <div class="info-item">
                <div class="info-label">Venue Location</div>
                <div class="info-value"><?= htmlspecialchars($ticket['nama_venue']) ?></div>
                <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px; line-height: 1.4;"><?= htmlspecialchars($ticket['alamat']) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Order Ref</div>
                <div class="info-value">#ORD-<?= $ticket['id_order'] ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Ticket Holder</div>
                <div class="info-value"><?= htmlspecialchars($ticket['nama_user'] ?? 'Attendee') ?></div>
            </div>
        </div>

        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.05); font-size: 10px; color: var(--text-muted); line-height: 1.6;">
            <strong>Important:</strong> Please bring this ticket and your ID to the venue. Each ticket is valid for one person and one scan. Do not share the QR code with anyone to prevent unauthorized use.
        </div>
    </div>

    <div class="ticket-stub">
        <div class="qr-area">
            <img src="<?= $qr_url ?>" class="qr-code" alt="QR">
        </div>
        <div>
            <div class="stub-kode"><?= $ticket['kode_tiket'] ?></div>
            <div class="stub-label">Scan to Enter</div>
        </div>
        <div style="margin-top: 30px;">
            <div style="font-size: 11px; font-weight: 700; color: white;">ADMIT ONE</div>
            <div style="font-size: 10px; color: var(--text-muted); margin-top: 4px;"><?= date('Y') ?> &copy; E-Tiket Platform</div>
        </div>
    </div>
</div>

</body>
</html>
