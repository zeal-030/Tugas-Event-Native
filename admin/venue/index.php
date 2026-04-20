<?php
session_start();
require_once '../../config/db.php';
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') { header("Location: ../../login.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit'])) {
        $nama      = mysqli_real_escape_string($conn, $_POST['nama_venue']);
        $alamat    = mysqli_real_escape_string($conn, $_POST['alamat']);
        $kapasitas = (int)$_POST['kapasitas'];
        mysqli_query($conn, "INSERT INTO venue (nama_venue, alamat, kapasitas) VALUES ('$nama','$alamat','$kapasitas')");
        header("Location: index.php?msg=success_add"); exit;
    }
    if (isset($_POST['edit'])) {
        $id        = (int)$_POST['id'];
        $nama      = mysqli_real_escape_string($conn, $_POST['nama_venue']);
        $alamat    = mysqli_real_escape_string($conn, $_POST['alamat']);
        $kapasitas = (int)$_POST['kapasitas'];
        mysqli_query($conn, "UPDATE venue SET nama_venue='$nama', alamat='$alamat', kapasitas='$kapasitas' WHERE id_venue=$id");
        header("Location: index.php?msg=success_edit"); exit;
    }
}
if (isset($_GET['del'])) {
    mysqli_query($conn, "DELETE FROM venue WHERE id_venue=".(int)$_GET['del']);
    header("Location: index.php?msg=success_del"); exit;
}

$venues = query("SELECT * FROM venue ORDER BY id_venue DESC");
$msg    = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Venue Management — Admin Panel</title>
    <?php $is_sub = true; include '../../includes/head.php'; ?>
    <style>
        .vn-page-header { display:flex; justify-content:space-between; align-items:center; }
        
        /* Stat */
        .vn-stat { background:var(--bg-card); border:1px solid var(--border); border-radius:20px; padding:1.4rem 1.8rem; display:flex; align-items:center; gap:1.1rem; transition:border-color .3s,box-shadow .3s; }
        .vn-stat:hover { border-color:var(--border-hover); box-shadow:var(--shadow-glow); }
        .vn-stat-icon { width:48px; height:48px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:1.3rem; flex-shrink:0; }
        .vn-stat-icon.purple { background:rgba(124,58,237,.15); color:#a78bfa; }
        .vn-stat-val { font-size:1.8rem; font-weight:800; color:var(--text-primary); line-height:1; }
        .vn-stat-lbl { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--text-muted); margin-top:3px; }

        /* Table Card */
        .vn-card { background:var(--bg-card); border:1px solid var(--border); border-radius:20px; overflow:hidden; }
        .vn-toolbar { display:flex; justify-content:space-between; align-items:center; padding:1.4rem 1.8rem; border-bottom:1px solid var(--border); }
        .vn-toolbar-title { font-size:1rem; font-weight:700; color:var(--text-primary); }

        .vn-table { width:100%; border-collapse:collapse; }
        .vn-table thead th { padding:.9rem 1.4rem; font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.8px; color:var(--text-muted); background:rgba(255,255,255,.02); border-bottom:1px solid var(--border); }
        .vn-table tbody td { padding:1.2rem 1.4rem; border-bottom:1px solid var(--border); vertical-align:middle; }
        .vn-table tbody tr:last-child td { border-bottom:none; }
        .vn-table tbody tr { transition:background .2s; }
        .vn-table tbody tr:hover td { background:rgba(255,255,255,.02); }

        /* Icon */
        .vn-icon { width:46px; height:46px; border-radius:13px; background:rgba(124,58,237,.1); border:1px solid rgba(124,58,237,.15); display:flex; align-items:center; justify-content:center; color:#a78bfa; font-size:1.2rem; transition:all .3s; }
        tr:hover .vn-icon { background:var(--gradient-primary); color:white; border-color:transparent; box-shadow:0 4px 15px rgba(124,58,237,.4); transform:rotate(-5deg) scale(1.08); }

        /* Name */
        .vn-name { font-weight:700; color:var(--text-primary); }
        .vn-id   { font-size:.7rem; color:var(--text-muted); margin-top:2px; }

        /* Address */
        .vn-addr { font-size:.82rem; color:var(--text-secondary); line-height:1.55; max-width:340px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }

        /* Capacity */
        .vn-cap { display:inline-flex; align-items:center; gap:6px; background:rgba(52,211,153,.1); border:1px solid rgba(52,211,153,.2); color:#34d399; border-radius:50px; padding:5px 14px; font-size:.78rem; font-weight:700; }

        /* Actions */
        .vn-actions { display:flex; gap:6px; justify-content:flex-end; }
        .vn-btn { width:34px; height:34px; border-radius:8px; border:1px solid var(--border); background:transparent; display:flex; align-items:center; justify-content:center; font-size:.85rem; cursor:pointer; transition:all .25s; color:var(--text-secondary); text-decoration:none; }
        .vn-btn:hover { background:var(--bg-hover); border-color:var(--border-hover); color:var(--text-primary); }
        .vn-btn.danger:hover { background:rgba(239,68,68,.1); border-color:rgba(239,68,68,.3); color:#f87171; }

        /* Modal */
        .vn-modal .modal-dialog { max-width:520px; }
        .vn-modal .modal-content { background:var(--bg-surface); border:1px solid var(--border); border-radius:24px; box-shadow:0 25px 80px rgba(0,0,0,.6); overflow:hidden; }
        .vn-modal-header  { padding:1.6rem 2rem 0; display:flex; align-items:center; justify-content:space-between; }
        .vn-modal-icon    { width:44px; height:44px; border-radius:13px; background:var(--gradient-primary); display:flex; align-items:center; justify-content:center; font-size:1.2rem; }
        .vn-modal-title   { font-size:1.1rem; font-weight:800; color:var(--text-primary); margin-top:.9rem; padding:0 2rem; }
        .vn-modal-sub     { font-size:.8rem; color:var(--text-muted); padding:4px 2rem 0; }
        .vn-modal-divider { height:1px; background:var(--border); margin:1.2rem 0 0; }
        .vn-modal-body    { padding:1.6rem 2rem; }
        .vn-modal-footer  { padding:0 2rem 1.8rem; display:flex; gap:.7rem; justify-content:flex-end; }

        .vn-label { font-size:.78rem; font-weight:700; color:var(--text-secondary); text-transform:uppercase; letter-spacing:.4px; margin-bottom:6px; display:block; }
        .vn-field { background:var(--bg-elevated); border:1px solid var(--border); color:var(--text-primary); border-radius:12px; padding:.7rem 1rem; font-family:inherit; font-size:.875rem; width:100%; transition:all .25s; }
        .vn-field:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(124,58,237,.15); }
        .vn-field::placeholder { color:var(--text-muted); }

        .vn-input-group { display:flex; }
        .vn-input-pre { background:var(--bg-elevated); border:1px solid var(--border); border-right:none; border-radius:12px 0 0 12px; padding:.7rem 1rem; display:flex; align-items:center; color:var(--text-muted); font-size:.9rem; }
        .vn-input-group .vn-field { border-radius:0 12px 12px 0; }

        .vn-close-btn { width:32px; height:32px; border-radius:8px; background:var(--bg-elevated); border:1px solid var(--border); cursor:pointer; display:flex; align-items:center; justify-content:center; color:var(--text-muted); transition:all .2s; }
        .vn-close-btn:hover { background:var(--bg-hover); color:var(--text-primary); }
        .vn-btn-cancel { background:var(--bg-elevated); border:1px solid var(--border); color:var(--text-secondary); border-radius:12px; padding:.65rem 1.5rem; font-size:.87rem; font-weight:600; cursor:pointer; transition:all .25s; }
        .vn-btn-cancel:hover { border-color:var(--border-hover); color:var(--text-primary); }
        .vn-btn-save { background:var(--gradient-primary); border:none; color:white; border-radius:12px; padding:.65rem 1.8rem; font-size:.87rem; font-weight:700; cursor:pointer; box-shadow:var(--shadow-primary); transition:all .25s; }
        .vn-btn-save:hover { transform:translateY(-2px); box-shadow:0 8px 25px rgba(124,58,237,.5); }
        .vn-mb { margin-bottom:1.1rem; }
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
                    <li class="breadcrumb-item active small text-white">Venues</li>
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
        <!-- Header -->
        <div class="vn-page-header mb-4">
            <div>
                <h1 style="font-size:1.6rem;font-weight:800;color:var(--text-primary);margin:0;">📍 Venue Management</h1>
                <p style="color:var(--text-muted);font-size:.85rem;margin:4px 0 0;">Kelola semua lokasi dan kapasitas tempat penyelenggaraan acara.</p>
            </div>
            <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalAdd">
                <i class="bi bi-plus-lg"></i> Add Venue
            </button>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="vn-stat">
                    <div class="vn-stat-icon purple"><i class="bi bi-geo-alt"></i></div>
                    <div>
                        <div class="vn-stat-val"><?= count($venues) ?></div>
                        <div class="vn-stat-lbl">Total Venues</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert -->
        <?php if($msg): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <div>
                <?php if($msg=='success_add')  echo 'Venue baru berhasil didaftarkan!'; ?>
                <?php if($msg=='success_edit') echo 'Data venue berhasil diperbarui!'; ?>
                <?php if($msg=='success_del')  echo 'Venue telah dihapus dari sistem.'; ?>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Table -->
        <div class="vn-card">
            <div class="vn-toolbar">
                <div class="vn-toolbar-title">Registered Locations <span style="color:var(--text-muted);font-weight:500;">(<?= count($venues) ?>)</span></div>
            </div>
            <div class="table-responsive">
                <table class="vn-table">
                    <thead>
                        <tr>
                            <th width="80">Icon</th>
                            <th>Venue Name</th>
                            <th>Address</th>
                            <th>Capacity</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($venues)): ?>
                        <tr><td colspan="5" style="text-align:center;padding:3rem;color:var(--text-muted);">Belum ada data venue.</td></tr>
                        <?php endif; ?>
                        <?php foreach($venues as $v): ?>
                        <tr>
                            <td><div class="vn-icon"><i class="bi bi-geo-alt-fill"></i></div></td>
                            <td>
                                <div class="vn-name"><?= $v['nama_venue'] ?></div>
                                <div class="vn-id">#VNU-<?= $v['id_venue'] ?></div>
                            </td>
                            <td><div class="vn-addr" title="<?= htmlspecialchars($v['alamat']) ?>"><?= $v['alamat'] ?></div></td>
                            <td>
                                <span class="vn-cap">
                                    <i class="bi bi-people-fill"></i>
                                    <?= number_format($v['kapasitas']) ?> Pax
                                </span>
                            </td>
                            <td>
                                <div class="vn-actions">
                                    <button class="vn-btn btn-edit-vn"
                                        data-id="<?= $v['id_venue'] ?>"
                                        data-nama="<?= htmlspecialchars($v['nama_venue']) ?>"
                                        data-alamat="<?= htmlspecialchars($v['alamat']) ?>"
                                        data-kapasitas="<?= $v['kapasitas'] ?>" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="?del=<?= $v['id_venue'] ?>" class="vn-btn danger" onclick="return confirm('Hapus venue ini?')" title="Delete">
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

<!-- Modal Add -->
<div class="modal fade vn-modal" id="modalAdd" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="vn-modal-header">
                <div class="vn-modal-icon">📍</div>
                <button type="button" class="vn-close-btn" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="vn-modal-title">Register New Venue</div>
            <div class="vn-modal-sub">Tambahkan lokasi baru sebagai pilihan venue event.</div>
            <div class="vn-modal-divider"></div>
            <form action="" method="post">
                <div class="vn-modal-body">
                    <div class="vn-mb">
                        <label class="vn-label">Nama Venue</label>
                        <input type="text" name="nama_venue" class="vn-field" placeholder="E.g. Grand Ballroom Jakarta" required>
                    </div>
                    <div class="vn-mb">
                        <label class="vn-label">Alamat Lengkap</label>
                        <textarea name="alamat" class="vn-field" rows="3" placeholder="Jl. Raya Sudirman No. 1, Jakarta Selatan..." required></textarea>
                    </div>
                    <div>
                        <label class="vn-label">Kapasitas Maksimum</label>
                        <div class="vn-input-group">
                            <div class="vn-input-pre"><i class="bi bi-people"></i></div>
                            <input type="number" name="kapasitas" class="vn-field" placeholder="E.g. 500" required>
                        </div>
                    </div>
                </div>
                <div class="vn-modal-footer">
                    <button type="button" class="vn-btn-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="submit" class="vn-btn-save"><i class="bi bi-check-lg"></i> Simpan Venue</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade vn-modal" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="vn-modal-header">
                <div class="vn-modal-icon" style="background:var(--gradient-warning);">✏️</div>
                <button type="button" class="vn-close-btn" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="vn-modal-title">Edit Venue</div>
            <div class="vn-modal-sub">Perbarui data lokasi yang sudah terdaftar.</div>
            <div class="vn-modal-divider"></div>
            <form action="" method="post">
                <input type="hidden" name="id" id="evn-id">
                <div class="vn-modal-body">
                    <div class="vn-mb">
                        <label class="vn-label">Nama Venue</label>
                        <input type="text" name="nama_venue" id="evn-nama" class="vn-field" required>
                    </div>
                    <div class="vn-mb">
                        <label class="vn-label">Alamat Lengkap</label>
                        <textarea name="alamat" id="evn-alamat" class="vn-field" rows="3" required></textarea>
                    </div>
                    <div>
                        <label class="vn-label">Kapasitas</label>
                        <div class="vn-input-group">
                            <div class="vn-input-pre"><i class="bi bi-people"></i></div>
                            <input type="number" name="kapasitas" id="evn-kapasitas" class="vn-field" required>
                        </div>
                    </div>
                </div>
                <div class="vn-modal-footer">
                    <button type="button" class="vn-btn-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit" class="vn-btn-save"><i class="bi bi-check-lg"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.btn-edit-vn').forEach(btn => {
    btn.addEventListener('click', () => {
        const d = btn.dataset;
        document.getElementById('evn-id').value       = d.id;
        document.getElementById('evn-nama').value     = d.nama;
        document.getElementById('evn-alamat').value   = d.alamat;
        document.getElementById('evn-kapasitas').value= d.kapasitas;
        new bootstrap.Modal(document.getElementById('modalEdit')).show();
    });
});
</script>
</body>
</html>
