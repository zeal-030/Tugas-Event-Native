<?php
session_start();
require_once '../../config/db.php';
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') { header("Location: ../../login.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $ide   = (int)$_POST['id_event'];
    $nama  = mysqli_real_escape_string($conn, $_POST['nama_tiket']);
    $harga = (int)$_POST['harga'];
    $kuota = (int)$_POST['kuota'];
    mysqli_query($conn, "INSERT INTO tiket (id_event, nama_tiket, harga, kuota) VALUES ($ide,'$nama',$harga,$kuota)");
    header("Location: index.php"); exit;
}
if (isset($_GET['del'])) {
    mysqli_query($conn, "DELETE FROM tiket WHERE id_tiket=".(int)$_GET['del']);
    header("Location: index.php"); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $id    = (int)$_POST['id'];
    $ide   = (int)$_POST['id_event'];
    $nama  = mysqli_real_escape_string($conn, $_POST['nama_tiket']);
    $harga = (int)$_POST['harga'];
    $kuota = (int)$_POST['kuota'];
    mysqli_query($conn, "UPDATE tiket SET id_event=$ide, nama_tiket='$nama', harga=$harga, kuota=$kuota WHERE id_tiket=$id");
    header("Location: index.php"); exit;
}

$tikets  = query("SELECT t.*, e.nama_event FROM tiket t JOIN event e ON t.id_event=e.id_event ORDER BY t.id_tiket DESC");
$events  = query("SELECT * FROM event ORDER BY nama_event");
$editRow = null;
if (isset($_GET['edit'])) {
    $editRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tiket WHERE id_tiket=".(int)$_GET['edit']));
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets — EventTiket Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php require_once '../../includes/sidebar.php'; ?>
<div class="main-content">
    <div class="topnav">
        <div class="topnav-left">
            <a href="../dashboard.php" style="color:var(--text-muted); text-decoration:none; font-size:0.8rem;"><i class="bi bi-arrow-left"></i> Back</a>
            <span style="color:var(--border); margin:0 0.5rem;">/</span>
            <span style="font-size:0.85rem; font-weight:600; color:var(--text-primary);">Tickets</span>
        </div>
    </div>
    <div class="page-header">
        <div class="page-title">🎫 Ticket Management</div>
        <div class="page-subtitle">Kelola jenis tiket, harga, dan kuota</div>
    </div>
    <div class="page-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header-custom">
                        <div class="card-title"><?= $editRow ? '✏️ Edit Ticket' : '➕ Add Ticket Type' ?></div>
                    </div>
                    <form method="post">
                        <?php if($editRow): ?><input type="hidden" name="id" value="<?= $editRow['id_tiket'] ?>"><?php endif; ?>
                        <div class="form-group">
                            <label class="form-label">Event</label>
                            <select name="id_event" class="form-select" required>
                                <option value="">Select Event</option>
                                <?php foreach($events as $e): ?>
                                <option value="<?= $e['id_event'] ?>" <?= (isset($editRow) && $editRow['id_event'] == $e['id_event']) ? 'selected' : '' ?>><?= $e['nama_event'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ticket Name</label>
                            <input type="text" name="nama_tiket" class="form-control" value="<?= $editRow['nama_tiket'] ?? '' ?>" placeholder="e.g. VIP, Regular" required>
                        </div>
                        <div class="row g-2">
                            <div class="col-6 form-group">
                                <label class="form-label">Price (Rp)</label>
                                <input type="number" name="harga" class="form-control" value="<?= $editRow['harga'] ?? '' ?>" placeholder="250000" required>
                            </div>
                            <div class="col-6 form-group">
                                <label class="form-label">Quota</label>
                                <input type="number" name="kuota" class="form-control" value="<?= $editRow['kuota'] ?? '' ?>" placeholder="100" required>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" name="<?= $editRow ? 'edit' : 'submit' ?>" class="btn btn-primary flex-fill" style="justify-content:center;">
                                <i class="bi bi-check-lg"></i> <?= $editRow ? 'Update' : 'Save Ticket' ?>
                            </button>
                            <?php if($editRow): ?><a href="index.php" class="btn btn-ghost"><i class="bi bi-x"></i></a><?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-8">
                <div class="table-wrapper">
                    <div class="table-header">
                        <div class="table-title">All Tickets <span style="color:var(--text-muted); font-weight:400;">(<?= count($tikets) ?>)</span></div>
                    </div>
                    <table class="table">
                        <thead><tr><th>#</th><th>Event</th><th>Ticket Type</th><th>Price</th><th>Quota</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php $i=1; foreach($tikets as $t): ?>
                            <tr>
                                <td style="color:var(--text-muted);"><?= $i++ ?></td>
                                <td style="color:var(--text-primary); font-size:0.82rem;"><?= $t['nama_event'] ?></td>
                                <td>
                                    <span style="background:rgba(124,58,237,0.15); color:var(--primary-light); padding:0.25rem 0.75rem; border-radius:20px; font-size:0.78rem; font-weight:600;">
                                        <?= $t['nama_tiket'] ?>
                                    </span>
                                </td>
                                <td style="color:#34d399; font-weight:700;">Rp <?= number_format($t['harga'],0,',','.') ?></td>
                                <td><?= number_format($t['kuota']) ?></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="index.php?edit=<?= $t['id_tiket'] ?>" class="btn btn-icon btn-warning btn-sm"><i class="bi bi-pencil"></i></a>
                                        <a href="index.php?del=<?= $t['id_tiket'] ?>" class="btn btn-icon btn-danger btn-sm" onclick="return confirm('Delete?')"><i class="bi bi-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
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
