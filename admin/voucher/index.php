<?php
session_start();
require_once '../../config/db.php';
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') { header("Location: ../../login.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $kode   = mysqli_real_escape_string($conn, $_POST['kode_voucher']);
    $potongan = (int)$_POST['potongan'];
    $kuota  = (int)$_POST['kuota'];
    $status = $_POST['status'];
    mysqli_query($conn, "INSERT INTO voucher (kode_voucher, potongan, kuota, status) VALUES ('$kode',$potongan,$kuota,'$status')");
    header("Location: index.php"); exit;
}
if (isset($_GET['del'])) {
    mysqli_query($conn, "DELETE FROM voucher WHERE id_voucher=".(int)$_GET['del']);
    header("Location: index.php"); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $id     = (int)$_POST['id'];
    $kode   = mysqli_real_escape_string($conn, $_POST['kode_voucher']);
    $potongan = (int)$_POST['potongan'];
    $kuota  = (int)$_POST['kuota'];
    $status = $_POST['status'];
    mysqli_query($conn, "UPDATE voucher SET kode_voucher='$kode', potongan=$potongan, kuota=$kuota, status='$status' WHERE id_voucher=$id");
    header("Location: index.php"); exit;
}

$vouchers = query("SELECT * FROM voucher ORDER BY id_voucher DESC");
$editRow  = null;
if (isset($_GET['edit'])) {
    $editRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM voucher WHERE id_voucher=".(int)$_GET['edit']));
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vouchers — EventTiket Admin</title>
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
            <span style="font-size:0.85rem; font-weight:600; color:var(--text-primary);">Vouchers</span>
        </div>
    </div>
    <div class="page-header">
        <div class="page-title">🎁 Voucher Management</div>
        <div class="page-subtitle">Buat kode voucher diskon untuk pengguna</div>
    </div>
    <div class="page-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header-custom">
                        <div class="card-title"><?= $editRow ? '✏️ Edit Voucher' : '➕ New Voucher' ?></div>
                    </div>
                    <form method="post">
                        <?php if($editRow): ?><input type="hidden" name="id" value="<?= $editRow['id_voucher'] ?>"><?php endif; ?>
                        <div class="form-group">
                            <label class="form-label">Voucher Code</label>
                            <input type="text" name="kode_voucher" class="form-control" value="<?= $editRow['kode_voucher'] ?? '' ?>" placeholder="EVENT2026" style="text-transform:uppercase;" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Discount Amount (Rp)</label>
                            <input type="number" name="potongan" class="form-control" value="<?= $editRow['potongan'] ?? '' ?>" placeholder="50000" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Usage Quota</label>
                            <input type="number" name="kuota" class="form-control" value="<?= $editRow['kuota'] ?? '' ?>" placeholder="100" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="aktif" <?= (isset($editRow) && $editRow['status']=='aktif') ? 'selected':'' ?>>🟢 Aktif</option>
                                <option value="nonaktif" <?= (isset($editRow) && $editRow['status']=='nonaktif') ? 'selected':'' ?>>🔴 Nonaktif</option>
                            </select>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" name="<?= $editRow ? 'edit' : 'submit' ?>" class="btn btn-primary flex-fill" style="justify-content:center;">
                                <i class="bi bi-check-lg"></i> <?= $editRow ? 'Update' : 'Create Voucher' ?>
                            </button>
                            <?php if($editRow): ?><a href="index.php" class="btn btn-ghost"><i class="bi bi-x"></i></a><?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-8">
                <div class="table-wrapper">
                    <div class="table-header">
                        <div class="table-title">All Vouchers <span style="color:var(--text-muted); font-weight:400;">(<?= count($vouchers) ?>)</span></div>
                    </div>
                    <table class="table">
                        <thead><tr><th>#</th><th>Code</th><th>Discount</th><th>Quota</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php $i=1; foreach($vouchers as $v): ?>
                            <tr>
                                <td style="color:var(--text-muted);"><?= $i++ ?></td>
                                <td>
                                    <div style="background:rgba(124,58,237,0.08); border:1px dashed rgba(124,58,237,0.3); padding:0.4rem 0.9rem; border-radius:8px; display:inline-block;">
                                        <span style="font-family:'Courier New',monospace; font-weight:700; color:var(--primary-light); letter-spacing:1px; font-size:0.88rem;"><?= $v['kode_voucher'] ?></span>
                                    </div>
                                </td>
                                <td style="color:#34d399; font-weight:700;">- Rp <?= number_format($v['potongan'],0,',','.') ?></td>
                                <td><?= number_format($v['kuota']) ?> uses</td>
                                <td>
                                    <span class="badge-status badge-<?= $v['status'] == 'aktif' ? 'active' : 'inactive' ?>">
                                        <?= $v['status'] == 'aktif' ? '● ACTIVE' : '● INACTIVE' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="index.php?edit=<?= $v['id_voucher'] ?>" class="btn btn-icon btn-warning btn-sm"><i class="bi bi-pencil"></i></a>
                                        <a href="index.php?del=<?= $v['id_voucher'] ?>" class="btn btn-icon btn-danger btn-sm" onclick="return confirm('Delete?')"><i class="bi bi-trash"></i></a>
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
