<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['login']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'petugas')) {
    header("Location: ../login.php"); exit;
}

$msg = null; $type = null; $data = null;

if (isset($_POST['checkin'])) {
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
            $msg  = "Tiket ini sudah digunakan!";
            $type = "warning";
        } else {
            $now = date('Y-m-d H:i:s');
            mysqli_query($conn, "UPDATE attendee SET status_checkin='sudah', waktu_checkin='$now' WHERE kode_tiket='$kode'");
            $msg  = "success";
            $type = "success";
        }
    } else {
        $msg  = "Kode tiket tidak ditemukan!";
        $type = "danger";
        $data = null;
    }
}

$recent_checkins = query("SELECT a.*, t.nama_tiket, e.nama_event, u.nama
                          FROM attendee a
                          JOIN order_detail od ON a.id_detail = od.id_detail
                          JOIN orders o ON od.id_order = o.id_order
                          JOIN users u ON o.id_user = u.id_user
                          JOIN tiket t ON od.id_tiket = t.id_tiket
                          JOIN event e ON t.id_event = e.id_event
                          WHERE a.status_checkin = 'sudah'
                          ORDER BY a.waktu_checkin DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-in — EventTiket Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php require_once '../includes/sidebar.php'; ?>
<div class="main-content">
    <div class="topnav">
        <div class="topnav-left">
            <span style="font-size:0.85rem; font-weight:600; color:var(--text-primary);">Check-in Attendees</span>
        </div>
        <div class="topnav-right">
            <div class="user-badge"><div class="user-avatar"><?= strtoupper(substr($_SESSION['nama'],0,1)) ?></div></div>
        </div>
    </div>
    <div class="page-header">
        <div class="page-title">📲 Attendee Check-in</div>
        <div class="page-subtitle">Validasi dan proses kehadiran peserta event</div>
    </div>
    <div class="page-body">
        <div class="row g-3">
            <div class="col-md-5">
                <div class="checkin-panel">
                    <div class="checkin-icon">📲</div>
                    <h4 style="font-weight:800; margin-bottom:0.5rem;">Scan Ticket Code</h4>
                    <p style="color:var(--text-secondary); font-size:0.875rem; margin-bottom:2rem;">Masukkan kode tiket peserta untuk proses check-in</p>

                    <?php if ($msg === 'success' && $data) : ?>
                    <div class="alert alert-success mb-3">
                        <div>
                            <div style="font-weight:700; font-size:1rem;">✅ Check-in Berhasil!</div>
                            <div style="margin-top:0.5rem; font-size:0.85rem;">
                                <strong>Event:</strong> <?= $data['nama_event'] ?><br>
                                <strong>Tiket:</strong> <?= $data['nama_tiket'] ?><br>
                                <strong>Peserta:</strong> <?= $data['customer'] ?>
                            </div>
                        </div>
                    </div>
                    <?php elseif ($msg && $type !== 'success') : ?>
                    <div class="alert alert-<?= $type ?> mb-3"><i class="bi bi-exclamation-triangle"></i> <?= $msg ?></div>
                    <?php endif; ?>

                    <form action="" method="post">
                        <div class="form-group" style="margin-bottom:1rem;">
                            <input type="text" name="kode_tiket" id="tiket-input" class="form-control"
                                style="text-align:center; font-size:1.1rem; font-weight:700; letter-spacing:2px; padding:1rem;"
                                placeholder="TKT-XXXXXXXX" required autofocus autocomplete="off">
                        </div>
                        <button type="submit" name="checkin" class="btn btn-primary btn-lg w-100" style="justify-content:center; letter-spacing:0.5px;">
                            <i class="bi bi-check-circle me-1"></i> Process Check-in
                        </button>
                    </form>
                </div>

                <!-- Stats -->
                <div class="row g-2 mt-3">
                    <div class="col-6">
                        <div class="card" style="text-align:center; padding:1.2rem;">
                            <div style="font-size:1.6rem; font-weight:800; color:var(--primary-light);"><?= count($recent_checkins) ?></div>
                            <div style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">Today Check-ins</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <?php $total_attendees = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM attendee"))['t']; ?>
                        <div class="card" style="text-align:center; padding:1.2rem;">
                            <div style="font-size:1.6rem; font-weight:800; color:#34d399;"><?= $total_attendees ?></div>
                            <div style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">Total Attendees</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="table-wrapper">
                    <div class="table-header">
                        <div class="table-title">🕑 Recent Check-ins</div>
                    </div>
                    <table class="table">
                        <thead><tr><th>Time</th><th>Customer</th><th>Ticket Code</th><th>Event</th></tr></thead>
                        <tbody>
                            <?php foreach($recent_checkins as $rc): ?>
                            <tr>
                                <td style="color:#fbbf24; font-size:0.82rem; font-weight:600;"><?= date('H:i:s', strtotime($rc['waktu_checkin'])) ?></td>
                                <td style="color:var(--text-primary); font-weight:500;"><?= $rc['nama'] ?></td>
                                <td><code><?= $rc['kode_tiket'] ?></code></td>
                                <td style="font-size:0.82rem;"><?= $rc['nama_event'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($recent_checkins)): ?>
                            <tr><td colspan="4" style="text-align:center; padding:3rem; color:var(--text-muted);">No recent check-ins</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
