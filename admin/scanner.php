<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['login']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'petugas')) {
    header("Location: ../login.php"); exit;
}

$msg = null; $type = null; $data = null;

// Logika Check-in (Sama dengan checkin.php agar konsisten)
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
            $msg  = "Tiket " . $kode . " SUDAH digunakan!";
            $type = "warning";
        } else {
            $now = date('Y-m-d H:i:s');
            mysqli_query($conn, "UPDATE attendee SET status_checkin='sudah', waktu_checkin='$now' WHERE kode_tiket='$kode'");
            $msg  = "CHECK-IN Berhasil: " . $data['customer'];
            $type = "success";
        }
    } else {
        $msg  = "Kode tiket tidak dikenal!";
        $type = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>QR Scanner — EventTiket Admin</title>
    <?php $is_sub = true; include '../includes/head.php'; ?>
    <!-- Library Scanner -->
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        #reader { border: none !important; border-radius: 20px; overflow: hidden; box-shadow: var(--shadow-lg); }
        #reader__scan_region { background: #000; }
        .scanner-container { max-width: 600px; margin: 0 auto; }
        #reader__dashboard_section_csr button { 
            background: var(--primary) !important; 
            color: white !important; 
            border: none !important; 
            padding: 8px 16px !important; 
            border-radius: 8px !important;
            cursor: pointer; font-weight: 600; font-family: inherit;
        }
    </style>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
    <div class="page-header text-center">
        <div class="page-title">📷 QR Code Live Scanner</div>
        <div class="page-subtitle">Arahkan kamera ke QR Code tiket untuk check-in otomatis</div>
    </div>

    <div class="page-body">
        <div class="scanner-container">
            <?php if ($msg) : ?>
                <div class="alert alert-<?= $type ?> alert-dismissible fade show mb-4" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi <?= $type == 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle' ?> fs-4"></i>
                        <div><?= $msg ?></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div id="reader"></div>
            
            <div class="card mt-4 p-4 text-center">
                <h6 class="fw-bold mb-2">Instruksi Scan</h6>
                <p class="text-muted small mb-0">1. Berikan izin akses kamera<br>2. Posisikan QR Code di dalam kotak hijau<br>3. Sistem akan otomatis memproses tiket</p>
            </div>

            <!-- Hidden Form untuk submit hasil scan -->
            <form id="scan-form" method="POST" style="display:none;">
                <input type="hidden" name="kode_tiket" id="scan-result">
            </form>
        </div>
    </div>
</div>

<script>
    function onScanSuccess(decodedText, decodedResult) {
        // Hentikan scanner sementara biar gak dobel submit
        html5QrcodeScanner.clear();
        
        // Bunyi Beep (Opsional)
        const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
        audio.play();

        // Submit form
        document.getElementById('scan-result').value = decodedText;
        document.getElementById('scan-form').submit();
    }

    function onScanFailure(error) {
        // Gak perlu dimunculin biar gak nyepam console
    }

    let html5QrcodeScanner = new Html5QrcodeScanner(
        "reader", { fps: 10, qrbox: {width: 250, height: 250} }, /* verbose= */ false);
    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
</script>

</body>
</html>
