<?php
/**
 * Scanner View — sama dengan checkin tetapi menggunakan UI QR Scanner
 * Data dari ScannerController: $msg, $type, $data
 */
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <title>QR Scanner — <?= APP_NAME ?> Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <style>
        .scanner-wrapper { display: grid; grid-template-columns: 1fr 360px; gap: 1.5rem; align-items: start; }
        .camera-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 24px; overflow: hidden; }
        .camera-card-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .camera-card-title { font-size: 0.9rem; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem; }
        .live-badge { display: inline-flex; align-items: center; gap: 0.35rem; background: rgba(16,185,129,0.12); color: #34d399; border: 1px solid rgba(16,185,129,0.25); font-size: 0.7rem; font-weight: 700; letter-spacing: 0.5px; padding: 0.2rem 0.6rem; border-radius: 50px; text-transform: uppercase; }
        .live-badge::before { content: ''; width: 6px; height: 6px; background: #34d399; border-radius: 50%; animation: blink 1.2s infinite; }
        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.2; } }
        .camera-body { padding: 1.5rem; }
        #reader { border: none !important; border-radius: 16px !important; overflow: hidden !important; width: 100% !important; }
        .side-panel { display: flex; flex-direction: column; gap: 1.25rem; }
        .result-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 20px; overflow: hidden; animation: slideIn 0.4s ease; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(-12px); } to { opacity: 1; transform: translateY(0); } }
        .result-header { padding: 1rem 1.25rem; display: flex; align-items: center; gap: 0.75rem; }
        .result-header.success { background: rgba(16,185,129,0.1); border-bottom: 1px solid rgba(16,185,129,0.15); }
        .result-header.warning { background: rgba(245,158,11,0.1); border-bottom: 1px solid rgba(245,158,11,0.15); }
        .result-header.danger  { background: rgba(239,68,68,0.1);  border-bottom: 1px solid rgba(239,68,68,0.15);  }
        .result-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
        .result-icon.success { background: rgba(16,185,129,0.2); color: #34d399; }
        .result-icon.warning { background: rgba(245,158,11,0.2); color: #fbbf24; }
        .result-icon.danger  { background: rgba(239,68,68,0.2);  color: #f87171; }
        .result-title { font-size: 0.85rem; font-weight: 700; }
        .result-title.success { color: #34d399; } .result-title.warning { color: #fbbf24; } .result-title.danger { color: #f87171; }
        .result-subtitle { font-size: 0.75rem; color: var(--text-muted); }
        .result-body { padding: 1.25rem; }
        .result-row { display: flex; justify-content: space-between; align-items: center; padding: 0.55rem 0; border-bottom: 1px solid var(--border); font-size: 0.82rem; }
        .result-row:last-child { border-bottom: none; }
        .result-row-label { color: var(--text-muted); font-weight: 500; } .result-row-value { color: var(--text-primary); font-weight: 600; text-align: right; max-width: 55%; }
        .manual-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 20px; overflow: hidden; }
        .manual-card-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); font-size: 0.85rem; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem; }
        .manual-card-body { padding: 1.25rem; }
        @media (max-width: 900px) { .scanner-wrapper { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="main-content">
    <div class="topnav">
        <div class="topnav-left">
            <i class="ri-qr-scan-2-line" style="font-size:1.1rem; color:var(--primary-light);"></i>
            <span style="font-size:0.85rem; font-weight:600; color:var(--text-primary);">QR Scanner</span>
        </div>
        <div class="topnav-right d-flex gap-3 align-items-center">
            <span class="live-badge">Live</span>
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
        <div class="page-title"><i class="ri-qr-scan-2-line"></i> QR Code Live Scanner</div>
        <div class="page-subtitle">Arahkan kamera ke QR Code tiket untuk proses check-in otomatis</div>
    </div>

    <div class="page-body">
        <div class="scanner-wrapper">
            <div>
                <div class="camera-card">
                    <div class="camera-card-header">
                        <div class="camera-card-title"><i class="ri-camera-line" style="color:var(--primary-light);"></i> Live Camera Feed</div>
                        <span class="live-badge">Aktif</span>
                    </div>
                    <div class="camera-body"><div id="reader"></div></div>
                </div>
            </div>

            <div class="side-panel">
                <?php if ($msg): ?>
                <div class="result-card">
                    <div class="result-header <?= $type ?>">
                        <div class="result-icon <?= $type ?>">
                            <?php if ($type === 'success'): ?><i class="ri-checkbox-circle-fill"></i>
                            <?php elseif ($type === 'warning'): ?><i class="ri-alert-fill"></i>
                            <?php else: ?><i class="ri-close-circle-fill"></i><?php endif; ?>
                        </div>
                        <div>
                            <div class="result-title <?= $type ?>">
                                <?= $type === 'success' ? 'Check-In Berhasil' : ($type === 'warning' ? 'Tiket Sudah Dipakai' : 'Tiket Tidak Valid') ?>
                            </div>
                            <div class="result-subtitle"><?= date('d M Y, H:i:s') ?></div>
                        </div>
                    </div>
                    <?php if ($data): ?>
                    <div class="result-body">
                        <div class="result-row"><span class="result-row-label">Nama</span><span class="result-row-value"><?= htmlspecialchars($data['customer']) ?></span></div>
                        <div class="result-row"><span class="result-row-label">Event</span><span class="result-row-value"><?= htmlspecialchars($data['nama_event']) ?></span></div>
                        <div class="result-row"><span class="result-row-label">Tiket</span><span class="result-row-value"><?= htmlspecialchars($data['nama_tiket']) ?></span></div>
                        <div class="result-row"><span class="result-row-label">Kode</span><span class="result-row-value" style="font-family:monospace; color:var(--primary-light);"><?= htmlspecialchars($data['kode_tiket']) ?></span></div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="camera-card" style="text-align:center; padding:2rem 1.5rem;">
                    <div style="width:64px;height:64px; background:rgba(124,58,237,0.1); border:2px solid rgba(124,58,237,0.2); border-radius:50%; display:flex;align-items:center;justify-content:center; margin:0 auto 1rem; font-size:1.8rem; color:var(--primary-light);"><i class="ri-qr-scan-2-line"></i></div>
                    <div style="font-weight:700; color:var(--text-primary); margin-bottom:0.35rem;">Menunggu Scan</div>
                    <div style="font-size:0.8rem; color:var(--text-muted);">Hasil scan tiket akan muncul di sini</div>
                </div>
                <?php endif; ?>

                <div class="manual-card">
                    <div class="manual-card-header"><i class="ri-keyboard-line" style="color:var(--primary-light);"></i> Input Manual Kode Tiket</div>
                    <div class="manual-card-body">
                        <form method="POST">
                            <div style="margin-bottom:0.75rem;">
                                <input type="text" name="kode_tiket" id="kode_tiket_manual" class="form-control" placeholder="Contoh: TKT-XXXXXX" autocomplete="off">
                            </div>
                            <button type="submit" class="btn btn-primary" style="width:100%;"><i class="ri-search-line"></i> Verifikasi Tiket</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <form id="scan-form" method="POST" style="display:none;">
            <input type="hidden" name="kode_tiket" id="scan-result">
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function onScanSuccess(decodedText) {
    html5QrcodeScanner.clear();
    try { new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3').play(); } catch(e) {}
    document.getElementById('scan-result').value = decodedText;
    document.getElementById('scan-form').submit();
}
let html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: { width: 260, height: 260 } }, false);
html5QrcodeScanner.render(onScanSuccess, () => {});
</script>
</body>
</html>
