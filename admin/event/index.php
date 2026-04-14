<?php
session_start();
require_once '../../config/db.php';
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') { header("Location: ../../login.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $nama  = mysqli_real_escape_string($conn, $_POST['nama_event']);
    $tgl   = $_POST['tanggal'];
    $vid   = (int)$_POST['id_venue'];
    mysqli_query($conn, "INSERT INTO event (nama_event, tanggal, id_venue) VALUES ('$nama','$tgl','$vid')");
    header("Location: index.php"); exit;
}
if (isset($_GET['del'])) {
    mysqli_query($conn, "DELETE FROM event WHERE id_event = " . (int)$_GET['del']);
    header("Location: index.php"); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $id    = (int)$_POST['id'];
    $nama  = mysqli_real_escape_string($conn, $_POST['nama_event']);
    $tgl   = $_POST['tanggal'];
    $vid   = (int)$_POST['id_venue'];
    mysqli_query($conn, "UPDATE event SET nama_event='$nama', tanggal='$tgl', id_venue='$vid' WHERE id_event=$id");
    header("Location: index.php"); exit;
}

$events  = query("SELECT e.*, v.nama_venue FROM event e JOIN venue v ON e.id_venue=v.id_venue ORDER BY tanggal DESC");
$venues  = query("SELECT * FROM venue");
$editRow = null;
if (isset($_GET['edit'])) {
    $editRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM event WHERE id_event=".(int)$_GET['edit']));
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events — EventTiket Admin</title>
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
            <span style="font-size:0.85rem; font-weight:600; color:var(--text-primary);">Events</span>
        </div>
        <div class="topnav-right">
            <div class="user-badge"><div class="user-avatar"><?= strtoupper(substr($_SESSION['nama'],0,1)) ?></div></div>
        </div>
    </div>
    <div class="page-header">
        <div class="page-title">📅 Event Management</div>
        <div class="page-subtitle">Buat dan kelola semua acara</div>
    </div>
    <div class="page-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header-custom">
                        <div class="card-title"><?= $editRow ? '✏️ Edit Event' : '➕ Add New Event' ?></div>
                    </div>
                    <form method="post">
                        <?php if ($editRow) : ?><input type="hidden" name="id" value="<?= $editRow['id_event'] ?>"><?php endif; ?>
                        <div class="form-group">
                            <label class="form-label">Event Name</label>
                            <input type="text" name="nama_event" class="form-control" value="<?= $editRow['nama_event'] ?? '' ?>" placeholder="e.g. Soundrenaline 2026" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Event Date</label>
                            <input type="date" name="tanggal" class="form-control" value="<?= $editRow['tanggal'] ?? '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Venue</label>
                            <select name="id_venue" class="form-select" required>
                                <option value="">Select Venue</option>
                                <?php foreach ($venues as $v) : ?>
                                <option value="<?= $v['id_venue'] ?>" <?= (isset($editRow) && $editRow['id_venue'] == $v['id_venue']) ? 'selected' : '' ?>><?= $v['nama_venue'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" name="<?= $editRow ? 'edit' : 'submit' ?>" class="btn btn-primary flex-fill" style="justify-content:center;">
                                <i class="bi bi-check-lg"></i> <?= $editRow ? 'Update' : 'Save Event' ?>
                            </button>
                            <?php if($editRow): ?><a href="index.php" class="btn btn-ghost"><i class="bi bi-x"></i></a><?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-8">
                <div class="table-wrapper">
                    <div class="table-header">
                        <div class="table-title">All Events <span style="color:var(--text-muted); font-weight:400;">(<?= count($events) ?>)</span></div>
                    </div>
                    <table class="table">
                        <thead>
                            <tr><th>#</th><th>Event Name</th><th>Date</th><th>Venue</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php $i=1; foreach ($events as $e) : ?>
                            <tr>
                                <td style="color:var(--text-muted);"><?= $i++ ?></td>
                                <td style="color:var(--text-primary); font-weight:600;"><?= $e['nama_event'] ?></td>
                                <td>
                                    <span style="background:rgba(6,182,212,0.1); color:#22d3ee; padding:0.25rem 0.6rem; border-radius:6px; font-size:0.78rem; font-weight:600;">
                                        <?= date('d M Y', strtotime($e['tanggal'])) ?>
                                    </span>
                                </td>
                                <td><?= $e['nama_venue'] ?></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="index.php?edit=<?= $e['id_event'] ?>" class="btn btn-icon btn-warning btn-sm"><i class="bi bi-pencil"></i></a>
                                        <a href="index.php?del=<?= $e['id_event'] ?>" class="btn btn-icon btn-danger btn-sm" onclick="return confirm('Delete?')"><i class="bi bi-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($events)): ?><tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--text-muted);">No events yet.</td></tr><?php endif; ?>
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
