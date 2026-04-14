<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php"); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_venue']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $kapasitas = (int)$_POST['kapasitas'];
    mysqli_query($conn, "INSERT INTO venue (nama_venue, alamat, kapasitas) VALUES ('$nama','$alamat','$kapasitas')");
    header("Location: index.php"); exit;
}

if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    mysqli_query($conn, "DELETE FROM venue WHERE id_venue = $id");
    header("Location: index.php"); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $id = (int)$_POST['id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama_venue']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $kapasitas = (int)$_POST['kapasitas'];
    mysqli_query($conn, "UPDATE venue SET nama_venue='$nama', alamat='$alamat', kapasitas='$kapasitas' WHERE id_venue=$id");
    header("Location: index.php"); exit;
}

$venues = query("SELECT * FROM venue ORDER BY id_venue DESC");
$editRow = null;
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $editRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM venue WHERE id_venue = $eid"));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venues — EventTiket Admin</title>
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
            <span style="color:var(--border); margin: 0 0.5rem;">/</span>
            <span style="font-size:0.85rem; font-weight:600; color:var(--text-primary);">Venues</span>
        </div>
        <div class="topnav-right">
            <div class="user-badge">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['nama'],0,1)) ?></div>
                <div class="user-info"><div class="user-name"><?= $_SESSION['nama'] ?></div></div>
            </div>
        </div>
    </div>

    <div class="page-header">
        <div class="page-title">📍 Venue Management</div>
        <div class="page-subtitle">Kelola semua tempat penyelenggaraan event</div>
    </div>

    <div class="page-body">
        <div class="row g-3">
            <!-- Form -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header-custom">
                        <div class="card-title"><?= $editRow ? '✏️ Edit Venue' : '➕ Add New Venue' ?></div>
                    </div>
                    <form action="" method="post">
                        <?php if ($editRow) : ?>
                            <input type="hidden" name="id" value="<?= $editRow['id_venue'] ?>">
                        <?php endif; ?>
                        <div class="form-group">
                            <label class="form-label">Venue Name</label>
                            <input type="text" name="nama_venue" class="form-control" value="<?= $editRow['nama_venue'] ?? '' ?>" placeholder="e.g. Jakarta Convention Center" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Address</label>
                            <textarea name="alamat" class="form-control" rows="3" placeholder="Full address..." required><?= $editRow['alamat'] ?? '' ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Capacity (pax)</label>
                            <input type="number" name="kapasitas" class="form-control" value="<?= $editRow['kapasitas'] ?? '' ?>" placeholder="e.g. 5000" required>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" name="<?= $editRow ? 'edit' : 'submit' ?>" class="btn btn-primary flex-fill" style="justify-content:center;">
                                <i class="bi bi-check-lg"></i> <?= $editRow ? 'Update' : 'Save Venue' ?>
                            </button>
                            <?php if ($editRow) : ?>
                            <a href="index.php" class="btn btn-ghost"><i class="bi bi-x"></i></a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="col-md-8">
                <div class="table-wrapper">
                    <div class="table-header">
                        <div class="table-title">All Venues <span style="color:var(--text-muted); font-weight:400;">(<?= count($venues) ?>)</span></div>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Venue Name</th>
                                <th>Address</th>
                                <th>Capacity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($venues as $v) : ?>
                            <tr>
                                <td style="color:var(--text-muted);"><?= $i++ ?></td>
                                <td style="color:var(--text-primary); font-weight:600;"><?= $v['nama_venue'] ?></td>
                                <td><?= substr($v['alamat'], 0, 40) ?>...</td>
                                <td><span style="color:var(--primary-light); font-weight:600;"><?= number_format($v['kapasitas']) ?></span> pax</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="index.php?edit=<?= $v['id_venue'] ?>" class="btn btn-icon btn-warning btn-sm" title="Edit"><i class="bi bi-pencil"></i></a>
                                        <a href="index.php?del=<?= $v['id_venue'] ?>" class="btn btn-icon btn-danger btn-sm" title="Delete" onclick="return confirm('Delete venue?')"><i class="bi bi-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($venues)): ?>
                            <tr><td colspan="5" style="text-align:center; padding:2rem; color:var(--text-muted);">No venues yet. Add one!</td></tr>
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
