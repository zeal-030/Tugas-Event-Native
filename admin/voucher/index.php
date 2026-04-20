<?php
session_start();
require_once '../../config/db.php';
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') { header("Location: ../../login.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit'])) {
        $kode     = mysqli_real_escape_string($conn, strtoupper(trim($_POST['kode_voucher'])));
        $potongan = (int)$_POST['potongan'];
        $kuota    = (int)$_POST['kuota'];
        $status   = $_POST['status'];
        mysqli_query($conn, "INSERT INTO voucher (kode_voucher, potongan, kuota, status) VALUES ('$kode',$potongan,$kuota,'$status')");
        header("Location: index.php?msg=success_add"); exit;
    }
    if (isset($_POST['edit'])) {
        $id       = (int)$_POST['id'];
        $kode     = mysqli_real_escape_string($conn, strtoupper(trim($_POST['kode_voucher'])));
        $potongan = (int)$_POST['potongan'];
        $kuota    = (int)$_POST['kuota'];
        $status   = $_POST['status'];
        mysqli_query($conn, "UPDATE voucher SET kode_voucher='$kode', potongan=$potongan, kuota=$kuota, status='$status' WHERE id_voucher=$id");
        header("Location: index.php?msg=success_edit"); exit;
    }
}
if (isset($_GET['del'])) {
    mysqli_query($conn, "DELETE FROM voucher WHERE id_voucher=".(int)$_GET['del']);
    header("Location: index.php?msg=success_del"); exit;
}

$vouchers = query("SELECT * FROM voucher ORDER BY id_voucher DESC");
$msg      = $_GET['msg'] ?? '';

$total_active   = count(array_filter($vouchers, fn($v) => $v['status'] === 'aktif'));
$total_inactive = count($vouchers) - $total_active;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Voucher Management — Admin Panel</title>
    <?php $is_sub = true; include '../../includes/head.php'; ?>
    <style>
        .vc-page-header { display:flex; justify-content:space-between; align-items:center; }

        /* Stats */
        .vc-stat { background:var(--bg-card); border:1px solid var(--border); border-radius:20px; padding:1.4rem 1.8rem; display:flex; align-items:center; gap:1.1rem; transition:all .3s; }
        .vc-stat:hover { border-color:var(--border-hover); box-shadow:var(--shadow-glow); }
        .vc-stat-icon { width:48px; height:48px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:1.3rem; flex-shrink:0; }
        .vc-stat-icon.purple { background:rgba(124,58,237,.12); color:#a78bfa; }
        .vc-stat-icon.green  { background:rgba(52,211,153,.12); color:#34d399; }
        .vc-stat-icon.gray   { background:rgba(91,91,120,.2);   color:var(--text-muted); }
        .vc-stat-val { font-size:1.8rem; font-weight:800; color:var(--text-primary); line-height:1; }
        .vc-stat-lbl { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--text-muted); margin-top:3px; }

        /* Table Card */
        .vc-card { background:var(--bg-card); border:1px solid var(--border); border-radius:20px; overflow:hidden; }
        .vc-toolbar { display:flex; justify-content:space-between; align-items:center; padding:1.4rem 1.8rem; border-bottom:1px solid var(--border); }
        .vc-toolbar-title { font-size:1rem; font-weight:700; color:var(--text-primary); }

        .vc-table { width:100%; border-collapse:collapse; }
        .vc-table thead th { padding:.9rem 1.4rem; font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.8px; color:var(--text-muted); background:rgba(255,255,255,.02); border-bottom:1px solid var(--border); }
        .vc-table tbody td { padding:1.2rem 1.4rem; border-bottom:1px solid var(--border); vertical-align:middle; }
        .vc-table tbody tr:last-child td { border-bottom:none; }
        .vc-table tbody tr { transition:background .2s; }
        .vc-table tbody tr:hover td { background:rgba(255,255,255,.02); }

        /* Voucher Code display */
        .vc-code-wrap { display:inline-flex; align-items:center; gap:.6rem; background:rgba(124,58,237,.06); border:1.5px dashed rgba(124,58,237,.3); border-radius:12px; padding:.55rem 1.1rem; transition:all .3s; cursor:default; }
        tr:hover .vc-code-wrap { background:rgba(124,58,237,.1); border-color:rgba(124,58,237,.5); }
        .vc-code-tag { color:var(--text-muted); font-size:.9rem; }
        .vc-code-text { font-family:'Courier New',monospace; font-weight:800; color:#a78bfa; letter-spacing:2px; font-size:.92rem; }

        /* Discount */
        .vc-discount-val { font-size:1.05rem; font-weight:900; color:#34d399; }
        .vc-discount-type { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--text-muted); margin-top:2px; }

        /* Quota */
        .vc-quota-val { font-weight:700; color:var(--text-primary); font-size:.9rem; }
        .vc-quota-lbl { font-size:.7rem; color:var(--text-muted); margin-top:2px; }

        /* Status */
        .vc-status { display:inline-flex; align-items:center; gap:6px; padding:5px 13px; border-radius:50px; font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.4px; }
        .vc-status-dot { width:6px; height:6px; border-radius:50%; flex-shrink:0; }
        .vc-status.active   { background:rgba(52,211,153,.1);   color:#34d399; border:1px solid rgba(52,211,153,.25); }
        .vc-status.active .vc-status-dot   { background:#34d399; box-shadow:0 0 6px #34d399; }
        .vc-status.inactive { background:rgba(255,255,255,.04); color:var(--text-muted); border:1px solid var(--border); }
        .vc-status.inactive .vc-status-dot { background:var(--text-muted); }

        /* Actions */
        .vc-actions { display:flex; gap:6px; justify-content:flex-end; }
        .vc-btn { width:34px; height:34px; border-radius:8px; border:1px solid var(--border); background:transparent; display:flex; align-items:center; justify-content:center; font-size:.85rem; cursor:pointer; transition:all .25s; color:var(--text-secondary); text-decoration:none; }
        .vc-btn:hover { background:var(--bg-hover); border-color:var(--border-hover); color:var(--text-primary); }
        .vc-btn.danger:hover { background:rgba(239,68,68,.1); border-color:rgba(239,68,68,.3); color:#f87171; }

        /* Copy button */
        .vc-copy-btn { width:28px; height:28px; border-radius:7px; border:1px solid var(--border); background:transparent; display:flex; align-items:center; justify-content:center; font-size:.75rem; cursor:pointer; color:var(--text-muted); transition:all .2s; }
        .vc-copy-btn:hover { color:#a78bfa; border-color:rgba(124,58,237,.4); background:rgba(124,58,237,.08); }

        /* Modal */
        .vc-modal .modal-dialog { max-width:520px; }
        .vc-modal .modal-content { background:var(--bg-surface); border:1px solid var(--border); border-radius:24px; box-shadow:0 25px 80px rgba(0,0,0,.6); overflow:hidden; }
        .vc-modal-header  { padding:1.6rem 2rem 0; display:flex; align-items:center; justify-content:space-between; }
        .vc-modal-icon    { width:44px; height:44px; border-radius:13px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; }
        .vc-modal-icon.add  { background:var(--gradient-primary); }
        .vc-modal-icon.edit { background:var(--gradient-warning); }
        .vc-modal-title   { font-size:1.1rem; font-weight:800; color:var(--text-primary); margin-top:.9rem; padding:0 2rem; }
        .vc-modal-sub     { font-size:.8rem; color:var(--text-muted); padding:4px 2rem 0; }
        .vc-modal-divider { height:1px; background:var(--border); margin:1.2rem 0 0; }
        .vc-modal-body    { padding:1.6rem 2rem; }
        .vc-modal-footer  { padding:0 2rem 1.8rem; display:flex; gap:.7rem; justify-content:flex-end; }

        .vc-label { font-size:.78rem; font-weight:700; color:var(--text-secondary); text-transform:uppercase; letter-spacing:.4px; margin-bottom:6px; display:block; }
        .vc-field { background:var(--bg-elevated); border:1px solid var(--border); color:var(--text-primary); border-radius:12px; padding:.7rem 1rem; font-family:inherit; font-size:.875rem; width:100%; transition:all .25s; }
        .vc-field:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(124,58,237,.15); }
        .vc-field::placeholder { color:var(--text-muted); }

        /* code input with Rp prefix */
        .vc-input-group { display:flex; }
        .vc-input-pre { background:var(--bg-elevated); border:1px solid var(--border); border-right:none; border-radius:12px 0 0 12px; padding:.7rem 1rem; display:flex; align-items:center; color:var(--text-muted); font-size:.8rem; font-weight:700; white-space:nowrap; }
        .vc-input-group .vc-field { border-radius:0 12px 12px 0; }

        /* code field styling */
        .vc-field-mono { font-family:'Courier New',monospace; text-transform:uppercase; letter-spacing:1px; font-weight:700; color:#a78bfa; font-size:.95rem; }

        /* Status radio pills */
        .vc-radio-group { display:flex; gap:.6rem; }
        .vc-radio-group input[type="radio"] { display:none; }
        .vc-radio-group label { padding:.55rem 1.3rem; border-radius:10px; border:1px solid var(--border); background:var(--bg-elevated); color:var(--text-secondary); font-size:.82rem; font-weight:600; cursor:pointer; transition:all .25s; display:flex; align-items:center; gap:6px; }
        .vc-radio-group input:checked + label { border-color:var(--primary); background:rgba(124,58,237,.12); color:#a78bfa; }
        .vc-radio-group input:checked + label.danger-label { border-color:rgba(239,68,68,.4); background:rgba(239,68,68,.08); color:#f87171; }

        .vc-close-btn { width:32px; height:32px; border-radius:8px; background:var(--bg-elevated); border:1px solid var(--border); cursor:pointer; display:flex; align-items:center; justify-content:center; color:var(--text-muted); transition:all .2s; }
        .vc-close-btn:hover { background:var(--bg-hover); color:var(--text-primary); }
        .vc-btn-cancel { background:var(--bg-elevated); border:1px solid var(--border); color:var(--text-secondary); border-radius:12px; padding:.65rem 1.5rem; font-size:.87rem; font-weight:600; cursor:pointer; transition:all .25s; }
        .vc-btn-cancel:hover { border-color:var(--border-hover); color:var(--text-primary); }
        .vc-btn-save { background:var(--gradient-primary); border:none; color:white; border-radius:12px; padding:.65rem 1.8rem; font-size:.87rem; font-weight:700; cursor:pointer; box-shadow:var(--shadow-primary); transition:all .25s; }
        .vc-btn-save:hover { transform:translateY(-2px); box-shadow:0 8px 25px rgba(124,58,237,.5); }

        .vc-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        .vc-mb { margin-bottom:1.1rem; }

        /* Toast */
        .vc-toast { position:fixed; bottom:1.5rem; right:1.5rem; background:var(--bg-surface); border:1px solid rgba(124,58,237,.3); border-radius:14px; padding:.9rem 1.4rem; display:flex; align-items:center; gap:.7rem; font-size:.85rem; color:var(--text-primary); box-shadow:var(--shadow-lg); z-index:9999; opacity:0; transform:translateY(20px); transition:all .35s; pointer-events:none; }
        .vc-toast.show { opacity:1; transform:translateY(0); }
    </style>
</head>
<body data-theme="dark">
<?php require_once '../../includes/sidebar.php'; ?>

<div class="main-content">
    <div class="topnav">
        <div class="topnav-left">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="../dashboard.php" class="text-decoration-none small" style="color:var(--text-muted)">Admin</a></li>
                    <li class="breadcrumb-item active small text-white">Vouchers</li>
                </ol>
            </nav>
        </div>
        <div class="topnav-right">
            <div class="user-badge">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['nama'],0,1)) ?></div>
                <div class="user-info"><div class="user-name"><?= $_SESSION['nama'] ?></div><div class="user-role">Administrator</div></div>
            </div>
        </div>
    </div>

    <div class="page-body" style="padding:2rem 2rem 3rem;">

        <!-- Page Header -->
        <div class="vc-page-header mb-4">
            <div>
                <h1 style="font-size:1.6rem;font-weight:800;color:var(--text-primary);margin:0;">🎁 Voucher Management</h1>
                <p style="color:var(--text-muted);font-size:.85rem;margin:4px 0 0;">Buat dan kelola kode diskon promo untuk meningkatkan penjualan tiket.</p>
            </div>
            <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalAdd">
                <i class="bi bi-plus-lg"></i> Create Voucher
            </button>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="vc-stat">
                    <div class="vc-stat-icon purple"><i class="bi bi-tag"></i></div>
                    <div>
                        <div class="vc-stat-val"><?= count($vouchers) ?></div>
                        <div class="vc-stat-lbl">Total Vouchers</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="vc-stat">
                    <div class="vc-stat-icon green"><i class="bi bi-check-circle"></i></div>
                    <div>
                        <div class="vc-stat-val"><?= $total_active ?></div>
                        <div class="vc-stat-lbl">Active</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="vc-stat">
                    <div class="vc-stat-icon gray"><i class="bi bi-x-circle"></i></div>
                    <div>
                        <div class="vc-stat-val"><?= $total_inactive ?></div>
                        <div class="vc-stat-lbl">Inactive</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert -->
        <?php if($msg): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <div>
                <?php if($msg=='success_add')  echo 'Voucher baru berhasil diterbitkan!'; ?>
                <?php if($msg=='success_edit') echo 'Data voucher berhasil diperbarui.'; ?>
                <?php if($msg=='success_del')  echo 'Voucher telah dihapus.'; ?>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Table -->
        <div class="vc-card">
            <div class="vc-toolbar">
                <div class="vc-toolbar-title">Promo Campaigns <span style="color:var(--text-muted);font-weight:500;">(<?= count($vouchers) ?>)</span></div>
            </div>
            <div class="table-responsive">
                <table class="vc-table">
                    <thead>
                        <tr>
                            <th>Kode Voucher</th>
                            <th>Nilai Diskon</th>
                            <th>Sisa Kuota</th>
                            <th>Status</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($vouchers)): ?>
                        <tr><td colspan="5" style="text-align:center;padding:3rem;color:var(--text-muted);">Belum ada voucher yang dibuat.</td></tr>
                        <?php endif; ?>
                        <?php foreach($vouchers as $v): ?>
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:.6rem;">
                                    <div class="vc-code-wrap">
                                        <span class="vc-code-tag"><i class="bi bi-tag-fill"></i></span>
                                        <span class="vc-code-text"><?= strtoupper($v['kode_voucher']) ?></span>
                                    </div>
                                    <button class="vc-copy-btn" onclick="copyCode('<?= strtoupper($v['kode_voucher']) ?>')" title="Copy kode">
                                        <i class="bi bi-copy"></i>
                                    </button>
                                </div>
                            </td>
                            <td>
                                <div class="vc-discount-val">- Rp <?= number_format($v['potongan'],0,',','.') ?></div>
                                <div class="vc-discount-type">Flat Deduction</div>
                            </td>
                            <td>
                                <div class="vc-quota-val"><?= number_format($v['kuota']) ?></div>
                                <div class="vc-quota-lbl">claims left</div>
                            </td>
                            <td>
                                <span class="vc-status <?= $v['status']==='aktif' ? 'active' : 'inactive' ?>">
                                    <span class="vc-status-dot"></span>
                                    <?= $v['status']==='aktif' ? 'ACTIVE' : 'INACTIVE' ?>
                                </span>
                            </td>
                            <td>
                                <div class="vc-actions">
                                    <button class="vc-btn btn-edit-vc"
                                        data-id="<?= $v['id_voucher'] ?>"
                                        data-kode="<?= htmlspecialchars($v['kode_voucher']) ?>"
                                        data-potongan="<?= $v['potongan'] ?>"
                                        data-kuota="<?= $v['kuota'] ?>"
                                        data-status="<?= $v['status'] ?>" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="?del=<?= $v['id_voucher'] ?>" class="vc-btn danger" onclick="return confirm('Hapus voucher <?= strtoupper($v['kode_voucher']) ?>?')" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </a>
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

<!-- ====================== MODAL ADD ====================== -->
<div class="modal fade vc-modal" id="modalAdd" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="vc-modal-header">
                <div class="vc-modal-icon add">🎁</div>
                <button type="button" class="vc-close-btn" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="vc-modal-title">Create New Promo Voucher</div>
            <div class="vc-modal-sub">Buat kode unik dan tentukan nilai diskon serta kuota penggunaan.</div>
            <div class="vc-modal-divider"></div>
            <form action="" method="post">
                <div class="vc-modal-body">
                    <div class="vc-mb">
                        <label class="vc-label">Kode Voucher</label>
                        <input type="text" name="kode_voucher" class="vc-field vc-field-mono" placeholder="SUMMER25" required>
                    </div>
                    <div class="vc-grid-2 vc-mb">
                        <div>
                            <label class="vc-label">Nilai Diskon</label>
                            <div class="vc-input-group">
                                <div class="vc-input-pre">Rp</div>
                                <input type="number" name="potongan" class="vc-field" placeholder="0" required>
                            </div>
                        </div>
                        <div>
                            <label class="vc-label">Kuota Klaim</label>
                            <input type="number" name="kuota" class="vc-field" placeholder="0" required>
                        </div>
                    </div>
                    <div>
                        <label class="vc-label">Status Awal</label>
                        <div class="vc-radio-group">
                            <input type="radio" name="status" id="add-aktif" value="aktif" checked>
                            <label for="add-aktif"><i class="bi bi-circle-fill" style="font-size:.55rem;color:#34d399;"></i> Aktif</label>
                            <input type="radio" name="status" id="add-nonaktif" value="nonaktif">
                            <label for="add-nonaktif" class="danger-label"><i class="bi bi-circle-fill" style="font-size:.55rem;color:#f87171;"></i> Nonaktif</label>
                        </div>
                    </div>
                </div>
                <div class="vc-modal-footer">
                    <button type="button" class="vc-btn-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="submit" class="vc-btn-save"><i class="bi bi-send"></i> Publish Voucher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ====================== MODAL EDIT ====================== -->
<div class="modal fade vc-modal" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="vc-modal-header">
                <div class="vc-modal-icon edit">✏️</div>
                <button type="button" class="vc-close-btn" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="vc-modal-title">Edit Promo Voucher</div>
            <div class="vc-modal-sub">Perbarui kode, nilai diskon, atau status voucher.</div>
            <div class="vc-modal-divider"></div>
            <form action="" method="post">
                <input type="hidden" name="id" id="evc-id">
                <div class="vc-modal-body">
                    <div class="vc-mb">
                        <label class="vc-label">Kode Voucher</label>
                        <input type="text" name="kode_voucher" id="evc-kode" class="vc-field vc-field-mono" required>
                    </div>
                    <div class="vc-grid-2 vc-mb">
                        <div>
                            <label class="vc-label">Nilai Diskon</label>
                            <div class="vc-input-group">
                                <div class="vc-input-pre">Rp</div>
                                <input type="number" name="potongan" id="evc-potongan" class="vc-field" required>
                            </div>
                        </div>
                        <div>
                            <label class="vc-label">Sisa Kuota</label>
                            <input type="number" name="kuota" id="evc-kuota" class="vc-field" required>
                        </div>
                    </div>
                    <div>
                        <label class="vc-label">Status Voucher</label>
                        <div class="vc-radio-group">
                            <input type="radio" name="status" id="edt-aktif" value="aktif">
                            <label for="edt-aktif"><i class="bi bi-circle-fill" style="font-size:.55rem;color:#34d399;"></i> Aktif</label>
                            <input type="radio" name="status" id="edt-nonaktif" value="nonaktif">
                            <label for="edt-nonaktif" class="danger-label"><i class="bi bi-circle-fill" style="font-size:.55rem;color:#f87171;"></i> Nonaktif</label>
                        </div>
                    </div>
                </div>
                <div class="vc-modal-footer">
                    <button type="button" class="vc-btn-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit" class="vc-btn-save"><i class="bi bi-check-lg"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="vc-toast" id="vcToast">
    <i class="bi bi-check-circle-fill" style="color:#34d399;"></i>
    <span id="vcToastMsg">Kode disalin!</span>
</div>

<?php include '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Edit modal populate
document.querySelectorAll('.btn-edit-vc').forEach(btn => {
    btn.addEventListener('click', () => {
        const d = btn.dataset;
        document.getElementById('evc-id').value       = d.id;
        document.getElementById('evc-kode').value     = d.kode;
        document.getElementById('evc-potongan').value = d.potongan;
        document.getElementById('evc-kuota').value    = d.kuota;

        // Set radio buttons
        document.getElementById('edt-aktif').checked    = (d.status === 'aktif');
        document.getElementById('edt-nonaktif').checked = (d.status === 'nonaktif');

        new bootstrap.Modal(document.getElementById('modalEdit')).show();
    });
});

// Copy to clipboard
function copyCode(code) {
    navigator.clipboard.writeText(code).then(() => showToast('Kode "' + code + '" disalin!'));
}

function showToast(msg) {
    const t = document.getElementById('vcToast');
    document.getElementById('vcToastMsg').textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2500);
}

// Auto-uppercase voucher code inputs
document.querySelectorAll('.vc-field-mono').forEach(inp => {
    inp.addEventListener('input', () => inp.value = inp.value.toUpperCase());
});
</script>
</body>
</html>
