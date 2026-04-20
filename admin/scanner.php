<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['login']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'petugas')) {
    header("Location: ../login.php"); exit;
}

$msg = null; $type = null; $data = null;

if (isset($_POST['kode_tiket'])) {
    $kode = mysqli_real_escape_string($conn, $_POST['kode_tiket']);
    $result = mysqli_query($conn, "SELECT a.*, t.nama_tiket, e.nama_event, u.nama as customer
                                   FROM attendee a
                                   JOIN order_detail od ON a.id_detail = od.id_detail
                                   JOIN orders o ON od.id_order = o.id_order
                                   JOIN users u ON o.id_user = u.id_user
                                   JOIN tiket t ON od.id_tiket = t.id_tiket
                                   JOIN event e ON t.id_event = e.id_event
                                   WHERE a.kode_tiket = '$kode'");
    if (mysqli_num_rows($result) === 1) {
        $data = mysqli_fetch_assoc($result);
        if ($data['status_checkin'] == 'sudah') {
            $msg  = "Tiket <strong>" . htmlspecialchars($kode) . "</strong> sudah pernah digunakan!";
            $type = "warning";
        } else {
            $now = date('Y-m-d H:i:s');
            mysqli_query($conn, "UPDATE attendee SET status_checkin='sudah', waktu_checkin='$now' WHERE kode_tiket='$kode'");
            $msg  = "Check-in berhasil untuk <strong>" . htmlspecialchars($data['customer']) . "</strong>";
            $type = "success";
        }
    } else {
        $msg  = "Kode tiket tidak dikenal atau tidak valid!";
        $type = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>QR Scanner — EventTiket Admin</title>
    <?php $is_sub = true; include '../includes/head.php'; ?>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        /* ── Scanner Page Overrides ── */
        .scanner-wrapper {
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 1.5rem;
            align-items: start;
        }

        /* Camera Card */
        .camera-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 24px;
            overflow: hidden;
            transition: border-color 0.3s;
        }
        .camera-card:hover { border-color: var(--border-hover); }

        .camera-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .camera-card-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .live-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: rgba(16,185,129,0.12);
            color: #34d399;
            border: 1px solid rgba(16,185,129,0.25);
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            padding: 0.2rem 0.6rem;
            border-radius: 50px;
            text-transform: uppercase;
        }
        .live-badge::before {
            content: '';
            width: 6px; height: 6px;
            background: #34d399;
            border-radius: 50%;
            animation: blink 1.2s infinite;
        }
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.2; }
        }

        .camera-body { padding: 1.5rem; }

        /* QR Reader Override */
        #reader {
            border: none !important;
            border-radius: 16px !important;
            overflow: hidden !important;
            width: 100% !important;
        }
        #reader__scan_region { background: #0a0a12 !important; border-radius: 14px !important; }
        #reader__scan_region img { display: none !important; }
        #reader__dashboard { padding: 0.75rem 0 0 !important; }
        #reader__dashboard_section_csr button {
            background: var(--gradient-primary) !important;
            color: white !important;
            border: none !important;
            padding: 0.5rem 1.25rem !important;
            border-radius: 10px !important;
            cursor: pointer;
            font-weight: 600;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.82rem !important;
            box-shadow: var(--shadow-primary) !important;
            transition: transform 0.2s !important;
        }
        #reader__dashboard_section_csr button:hover { transform: translateY(-2px) !important; }
        #reader__dashboard_section_swaplink { color: var(--primary-light) !important; font-size: 0.78rem !important; }
        #reader__camera_permission_button {
            background: var(--gradient-primary) !important;
            color: white !important;
            border: none !important;
            padding: 0.6rem 1.5rem !important;
            border-radius: 10px !important;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 600;
            cursor: pointer;
        }
        #reader__header_message { color: var(--text-secondary) !important; font-size: 0.8rem !important; }
        #reader__status_span { color: var(--text-muted) !important; font-size: 0.75rem !important; }
        #reader__filescan_input { color: var(--text-secondary) !important; font-size: 0.8rem; }

        /* Steps instruction */
        .steps-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 1.25rem;
        }
        .step-item {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.7rem 1rem;
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            border-radius: 12px;
            transition: border-color 0.25s;
        }
        .step-item:hover { border-color: var(--border-hover); }
        .step-num {
            width: 28px; height: 28px;
            background: rgba(124,58,237,0.15);
            color: var(--primary-light);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.72rem;
            font-weight: 700;
            flex-shrink: 0;
        }
        .step-text { font-size: 0.82rem; color: var(--text-secondary); line-height: 1.4; }

        /* Side Panel */
        .side-panel { display: flex; flex-direction: column; gap: 1.25rem; }

        /* Result Card */
        .result-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            overflow: hidden;
            animation: slideIn 0.4s ease;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .result-header {
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .result-header.success { background: rgba(16,185,129,0.1); border-bottom: 1px solid rgba(16,185,129,0.15); }
        .result-header.warning { background: rgba(245,158,11,0.1); border-bottom: 1px solid rgba(245,158,11,0.15); }
        .result-header.danger  { background: rgba(239,68,68,0.1);  border-bottom: 1px solid rgba(239,68,68,0.15);  }

        .result-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }
        .result-icon.success { background: rgba(16,185,129,0.2); color: #34d399; }
        .result-icon.warning { background: rgba(245,158,11,0.2); color: #fbbf24; }
        .result-icon.danger  { background: rgba(239,68,68,0.2);  color: #f87171; }

        .result-title { font-size: 0.85rem; font-weight: 700; margin-bottom: 0.1rem; }
        .result-title.success { color: #34d399; }
        .result-title.warning { color: #fbbf24; }
        .result-title.danger  { color: #f87171; }
        .result-subtitle { font-size: 0.75rem; color: var(--text-muted); }

        .result-body { padding: 1.25rem; }
        .result-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.55rem 0;
            border-bottom: 1px solid var(--border);
            font-size: 0.82rem;
        }
        .result-row:last-child { border-bottom: none; }
        .result-row-label { color: var(--text-muted); font-weight: 500; }
        .result-row-value { color: var(--text-primary); font-weight: 600; text-align: right; max-width: 55%; }

        /* Stats mini */
        .stat-mini-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }
        .stat-mini {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 1rem;
            text-align: center;
            transition: border-color 0.25s, transform 0.25s;
        }
        .stat-mini:hover { border-color: var(--border-hover); transform: translateY(-2px); }
        .stat-mini-val {
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 0.2rem;
        }
        .stat-mini-label { font-size: 0.7rem; color: var(--text-muted); font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }

        /* Manual Form */
        .manual-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            overflow: hidden;
        }
        .manual-card-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border);
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .manual-card-body { padding: 1.25rem; }
        .input-group-custom { display: flex; gap: 0.5rem; }
        .input-group-custom .form-control { flex: 1; }

        @media (max-width: 900px) {
            .scanner-wrapper { grid-template-columns: 1fr; }
        }

        @media print { .sidebar, .topnav, .main-content .btn { display: none !important; } }
    </style>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>

<div class="main-content">
    <!-- Top Nav -->
    <div class="topnav">
        <div class="topnav-left">
            <i class="bi bi-qr-code-scan" style="font-size:1.1rem; color:var(--primary-light);"></i>
            <span style="font-size:0.85rem; font-weight:600; color:var(--text-primary);">QR Scanner</span>
        </div>
        <div class="topnav-right">
            <span class="live-badge">Live</span>
        </div>
    </div>

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-title">📷 QR Code Live Scanner</div>
        <div class="page-subtitle">Arahkan kamera ke QR Code tiket untuk proses check-in otomatis</div>
    </div>

    <div class="page-body">
        <div class="scanner-wrapper">

            <!-- LEFT: Camera Card -->
            <div>
                <div class="camera-card">
                    <div class="camera-card-header">
                        <div class="camera-card-title">
                            <i class="bi bi-camera-video" style="color:var(--primary-light);"></i>
                            Live Camera Feed
                        </div>
                        <span class="live-badge">Aktif</span>
                    </div>
                    <div class="camera-body">
                        <div id="reader"></div>
                        <div class="steps-list">
                            <div class="step-item">
                                <div class="step-num">1</div>
                                <div class="step-text">Klik <strong style="color:var(--primary-light);">Start Scanning</strong> dan berikan izin akses kamera</div>
                            </div>
                            <div class="step-item">
                                <div class="step-num">2</div>
                                <div class="step-text">Posisikan QR Code tiket di dalam area bingkai kamera</div>
                            </div>
                            <div class="step-item">
                                <div class="step-num">3</div>
                                <div class="step-text">Sistem akan otomatis mendeteksi dan memproses tiket</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Side Panel -->
            <div class="side-panel">

                <?php if ($msg) : ?>
                <!-- Result Card -->
                <div class="result-card">
                    <div class="result-header <?= $type ?>">
                        <div class="result-icon <?= $type ?>">
                            <?php if ($type === 'success'): ?><i class="bi bi-check-circle-fill"></i>
                            <?php elseif ($type === 'warning'): ?><i class="bi bi-exclamation-triangle-fill"></i>
                            <?php else: ?><i class="bi bi-x-circle-fill"></i>
                            <?php endif; ?>
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
                        <div class="result-row">
                            <span class="result-row-label">Nama</span>
                            <span class="result-row-value"><?= htmlspecialchars($data['customer']) ?></span>
                        </div>
                        <div class="result-row">
                            <span class="result-row-label">Event</span>
                            <span class="result-row-value"><?= htmlspecialchars($data['nama_event']) ?></span>
                        </div>
                        <div class="result-row">
                            <span class="result-row-label">Tiket</span>
                            <span class="result-row-value"><?= htmlspecialchars($data['nama_tiket']) ?></span>
                        </div>
                        <div class="result-row">
                            <span class="result-row-label">Kode</span>
                            <span class="result-row-value" style="font-family:monospace; color:var(--primary-light);"><?= htmlspecialchars($data['kode_tiket']) ?></span>
                        </div>
                        <?php if (!empty($data['waktu_checkin'])): ?>
                        <div class="result-row">
                            <span class="result-row-label">Waktu</span>
                            <span class="result-row-value"><?= date('d/m/Y H:i', strtotime($data['waktu_checkin'])) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="result-body">
                        <p style="font-size:0.82rem; color:var(--text-muted); margin:0;"><?= $msg ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>

                <!-- Idle State Card -->
                <div class="camera-card" style="text-align:center; padding:2rem 1.5rem;">
                    <div style="width:64px;height:64px; background:rgba(124,58,237,0.1); border:2px solid rgba(124,58,237,0.2); border-radius:50%; display:flex;align-items:center;justify-content:center; margin:0 auto 1rem; animation: pulse 3s infinite; font-size:1.8rem;">📷</div>
                    <div style="font-weight:700; color:var(--text-primary); margin-bottom:0.35rem;">Menunggu Scan</div>
                    <div style="font-size:0.8rem; color:var(--text-muted);">Hasil scan tiket akan muncul di sini</div>
                </div>
                <?php endif; ?>

                <!-- Manual Input Card -->
                <div class="manual-card">
                    <div class="manual-card-header">
                        <i class="bi bi-keyboard" style="color:var(--primary-light);"></i>
                        Input Manual Kode Tiket
                    </div>
                    <div class="manual-card-body">
                        <form method="POST">
                            <div style="margin-bottom:0.75rem;">
                                <input type="text"
                                       name="kode_tiket"
                                       id="kode_tiket_manual"
                                       class="form-control"
                                       placeholder="Contoh: TKT-XXXXXX"
                                       autocomplete="off">
                            </div>
                            <button type="submit" class="btn btn-primary" style="width:100%;">
                                <i class="bi bi-search"></i> Verifikasi Tiket
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Info Card -->
                <div style="background:rgba(6,182,212,0.06); border:1px solid rgba(6,182,212,0.15); border-radius:14px; padding:1rem 1.25rem;">
                    <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.5rem;">
                        <i class="bi bi-info-circle" style="color:var(--accent); font-size:0.95rem;"></i>
                        <span style="font-size:0.8rem; font-weight:700; color:var(--accent);">Info Scanner</span>
                    </div>
                    <ul style="margin:0; padding-left:1.1rem; font-size:0.78rem; color:var(--text-muted); line-height:1.8;">
                        <li>Scan otomatis berhenti setelah berhasil</li>
                        <li>Satu tiket hanya bisa check-in sekali</li>
                        <li>Gunakan input manual jika QR sulit terbaca</li>
                    </ul>
                </div>

            </div><!-- end side-panel -->
        </div><!-- end scanner-wrapper -->

        <!-- Hidden Form -->
        <form id="scan-form" method="POST" style="display:none;">
            <input type="hidden" name="kode_tiket" id="scan-result">
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function onScanSuccess(decodedText, decodedResult) {
        html5QrcodeScanner.clear();
        try {
            const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
            audio.play();
        } catch(e) {}
        document.getElementById('scan-result').value = decodedText;
        document.getElementById('scan-form').submit();
    }

    function onScanFailure(error) { /* silent */ }

    let html5QrcodeScanner = new Html5QrcodeScanner(
        "reader",
        { fps: 10, qrbox: { width: 260, height: 260 } },
        false
    );
    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
</script>
</body>
</html>
