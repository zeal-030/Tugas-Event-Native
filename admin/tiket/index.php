<?php
session_start();
require_once '../../config/db.php';
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') { header("Location: ../../login.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit'])) {
        $ide   = (int)$_POST['id_event'];
        $nama  = mysqli_real_escape_string($conn, $_POST['nama_tiket']);
        $harga = (int)$_POST['harga'];
        $kuota = (int)$_POST['kuota'];

        // Kapasitas Check
        $venue_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT v.kapasitas FROM venue v JOIN event e ON v.id_venue = e.id_venue WHERE e.id_event = $ide"));
        $total_existing = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(kuota) as total FROM tiket WHERE id_event = $ide"))['total'] ?? 0;
        
        if (($total_existing + $kuota) > $venue_info['kapasitas']) {
            header("Location: index.php?msg=err_capacity&cap=".$venue_info['kapasitas']); exit;
        }

        mysqli_query($conn, "INSERT INTO tiket (id_event, nama_tiket, harga, kuota) VALUES ($ide,'$nama',$harga,$kuota)");
        header("Location: index.php?msg=success_add"); exit;
    }
    if (isset($_POST['edit'])) {
        $id    = (int)$_POST['id'];
        $ide   = (int)$_POST['id_event'];
        $nama  = mysqli_real_escape_string($conn, $_POST['nama_tiket']);
        $harga = (int)$_POST['harga'];
        $kuota = (int)$_POST['kuota'];

        // Kapasitas Check (excluding this ticket's old quota)
        $venue_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT v.kapasitas FROM venue v JOIN event e ON v.id_venue = e.id_venue WHERE e.id_event = $ide"));
        $total_other = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(kuota) as total FROM tiket WHERE id_event = $ide AND id_tiket != $id"))['total'] ?? 0;

        if (($total_other + $kuota) > $venue_info['kapasitas']) {
            header("Location: index.php?msg=err_capacity&cap=".$venue_info['kapasitas']); exit;
        }

        mysqli_query($conn, "UPDATE tiket SET id_event=$ide, nama_tiket='$nama', harga=$harga, kuota=$kuota WHERE id_tiket=$id");
        header("Location: index.php?msg=success_edit"); exit;
    }
}
if (isset($_GET['del'])) {
    mysqli_query($conn, "DELETE FROM tiket WHERE id_tiket=".(int)$_GET['del']);
    header("Location: index.php?msg=success_del"); exit;
}

$tikets = query("SELECT t.*, e.nama_event FROM tiket t JOIN event e ON t.id_event=e.id_event ORDER BY t.id_tiket DESC");
$events = query("SELECT * FROM event ORDER BY nama_event");
$msg    = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Ticket Management — Admin Panel</title>
    <?php $is_sub = true; include '../../includes/head.php'; ?>
    <style>
        .tk-page-header { display:flex; justify-content:space-between; align-items:center; }

        /* Stat */
        .tk-stat { background:var(--bg-card); border:1px solid var(--border); border-radius:20px; padding:1.4rem 1.8rem; display:flex; align-items:center; gap:1.1rem; transition:all .3s; }
        .tk-stat:hover { border-color:var(--border-hover); box-shadow:var(--shadow-glow); }
        .tk-stat-icon { width:48px; height:48px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:1.3rem; flex-shrink:0; }
        .tk-stat-icon.green  { background:rgba(52,211,153,.12); color:#34d399; }
        .tk-stat-icon.purple { background:rgba(124,58,237,.12); color:#a78bfa; }
        .tk-stat-val { font-size:1.8rem; font-weight:800; color:var(--text-primary); line-height:1; }
        .tk-stat-lbl { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--text-muted); margin-top:3px; }

        /* Table Card */
        .tk-card { background:var(--bg-card); border:1px solid var(--border); border-radius:20px; overflow:hidden; }
        .tk-toolbar { display:flex; justify-content:space-between; align-items:center; padding:1.4rem 1.8rem; border-bottom:1px solid var(--border); }
        .tk-toolbar-title { font-size:1rem; font-weight:700; color:var(--text-primary); }

        .tk-table { width:100%; border-collapse:collapse; }
        .tk-table thead th { padding:.9rem 1.4rem; font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.8px; color:var(--text-muted); background:rgba(255,255,255,.02); border-bottom:1px solid var(--border); }
        .tk-table tbody td { padding:1.2rem 1.4rem; border-bottom:1px solid var(--border); vertical-align:middle; }
        .tk-table tbody tr:last-child td { border-bottom:none; }
        .tk-table tbody tr { transition:background .2s; }
        .tk-table tbody tr:hover td { background:rgba(255,255,255,.02); }

        /* Ticket Icon */
        .tk-icon { width:46px; height:46px; border-radius:13px; background:rgba(52,211,153,.08); border:1px solid rgba(52,211,153,.15); display:flex; align-items:center; justify-content:center; color:#34d399; font-size:1.2rem; transition:all .3s; }
        tr:hover .tk-icon { background:#34d399; color:white; border-color:transparent; box-shadow:0 4px 15px rgba(52,211,153,.4); transform:rotate(-5deg) scale(1.08); }

        /* Fields */
        .tk-name { font-weight:700; color:var(--text-primary); }
        .tk-id   { font-size:.7rem; color:var(--text-muted); margin-top:2px; }
        .tk-event-name { font-weight:600; color:var(--text-primary); font-size:.88rem; }
        .tk-event-id   { font-size:.7rem; color:var(--text-muted); margin-top:2px; }

        /* Price */
        .tk-price { font-size:1.05rem; font-weight:900; color:#34d399; letter-spacing:-.3px; }
        .tk-stock { display:inline-flex; align-items:center; gap:5px; background:rgba(255,255,255,.04); border:1px solid var(--border); color:var(--text-muted); border-radius:8px; padding:3px 10px; font-size:.72rem; font-weight:600; margin-top:6px; }

        /* Actions */
        .tk-actions { display:flex; gap:6px; justify-content:flex-end; }
        .tk-btn { width:34px; height:34px; border-radius:8px; border:1px solid var(--border); background:transparent; display:flex; align-items:center; justify-content:center; font-size:.85rem; cursor:pointer; transition:all .25s; color:var(--text-secondary); text-decoration:none; }
        .tk-btn:hover { background:var(--bg-hover); border-color:var(--border-hover); color:var(--text-primary); }
        .tk-btn.danger:hover { background:rgba(239,68,68,.1); border-color:rgba(239,68,68,.3); color:#f87171; }

        /* Modal */
        .tk-modal .modal-dialog { max-width:520px; }
        .tk-modal .modal-content { background:var(--bg-surface); border:1px solid var(--border); border-radius:24px; box-shadow:0 25px 80px rgba(0,0,0,.6); overflow:hidden; }
        .tk-modal-header  { padding:1.6rem 2rem 0; display:flex; align-items:center; justify-content:space-between; }
        .tk-modal-icon    { width:44px; height:44px; border-radius:13px; background:linear-gradient(135deg,#059669,#10b981); display:flex; align-items:center; justify-content:center; font-size:1.2rem; }
        .tk-modal-title   { font-size:1.1rem; font-weight:800; color:var(--text-primary); margin-top:.9rem; padding:0 2rem; }
        .tk-modal-sub     { font-size:.8rem; color:var(--text-muted); padding:4px 2rem 0; }
        .tk-modal-divider { height:1px; background:var(--border); margin:1.2rem 0 0; }
        .tk-modal-body    { padding:1.6rem 2rem; }
        .tk-modal-footer  { padding:0 2rem 1.8rem; display:flex; gap:.7rem; justify-content:flex-end; }

        .tk-label { font-size:.78rem; font-weight:700; color:var(--text-secondary); text-transform:uppercase; letter-spacing:.4px; margin-bottom:6px; display:block; }
        .tk-field { background:var(--bg-elevated); border:1px solid var(--border); color:var(--text-primary); border-radius:12px; padding:.7rem 1rem; font-family:inherit; font-size:.875rem; width:100%; transition:all .25s; }
        .tk-field:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(124,58,237,.15); }
        .tk-field::placeholder { color:var(--text-muted); }

        .tk-input-group { display:flex; }
        .tk-input-pre { background:var(--bg-elevated); border:1px solid var(--border); border-right:none; border-radius:12px 0 0 12px; padding:.7rem 1rem; display:flex; align-items:center; color:var(--text-muted); font-size:.8rem; font-weight:700; white-space:nowrap; }
        .tk-input-group .tk-field { border-radius:0 12px 12px 0; }

        .tk-close-btn { width:32px; height:32px; border-radius:8px; background:var(--bg-elevated); border:1px solid var(--border); cursor:pointer; display:flex; align-items:center; justify-content:center; color:var(--text-muted); transition:all .2s; }
        .tk-close-btn:hover { background:var(--bg-hover); color:var(--text-primary); }
        .tk-btn-cancel { background:var(--bg-elevated); border:1px solid var(--border); color:var(--text-secondary); border-radius:12px; padding:.65rem 1.5rem; font-size:.87rem; font-weight:600; cursor:pointer; transition:all .25s; }
        .tk-btn-cancel:hover { border-color:var(--border-hover); color:var(--text-primary); }
        .tk-btn-save { background:linear-gradient(135deg,#059669,#10b981); border:none; color:white; border-radius:12px; padding:.65rem 1.8rem; font-size:.87rem; font-weight:700; cursor:pointer; box-shadow:0 4px 15px rgba(16,185,129,.3); transition:all .25s; }
        .tk-btn-save:hover { transform:translateY(-2px); box-shadow:0 8px 25px rgba(16,185,129,.45); }
        .tk-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        .tk-mb { margin-bottom:1.1rem; }
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
                    <li class="breadcrumb-item active small text-white">Tickets</li>
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
        <div class="tk-page-header mb-4">
            <div>
                <h1 style="font-size:1.6rem;font-weight:800;color:var(--text-primary);margin:0;">🎫 Ticket Management</h1>
                <p style="color:var(--text-muted);font-size:.85rem;margin:4px 0 0;">Kelola kategori tiket, harga, dan stok untuk setiap event.</p>
            </div>
            <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalAdd">
                <i class="bi bi-plus-lg"></i> Add Ticket Class
            </button>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="tk-stat">
                    <div class="tk-stat-icon green"><i class="bi bi-ticket-perforated"></i></div>
                    <div>
                        <div class="tk-stat-val"><?= count($tikets) ?></div>
                        <div class="tk-stat-lbl">Total Ticket Classes</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert -->
        <?php if($msg): ?>
        <?php if(strpos($msg, 'err_') === 0): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4 glass" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>
                    <?php 
                        if($msg == 'err_capacity') echo "Kuota gagal ditambahkan! Total tiket melebihi kapasitas venue (Maks: ".$_GET['cap']." pax).";
                    ?>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php else: ?>
            <div class="alert alert-success alert-dismissible fade show mb-4 glass" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                <div>
                    <?php if($msg=='success_add')  echo 'Kategori tiket berhasil ditambahkan!'; ?>
                    <?php if($msg=='success_edit') echo 'Data tiket berhasil diperbarui!'; ?>
                    <?php if($msg=='success_del')  echo 'Data tiket telah dihapus.'; ?>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php endif; ?>

        <!-- Table -->
        <div class="tk-card">
            <div class="tk-toolbar">
                <div class="tk-toolbar-title">Active Ticket Classes <span style="color:var(--text-muted);font-weight:500;">(<?= count($tikets) ?>)</span></div>
            </div>
            <div class="table-responsive">
                <table class="tk-table">
                    <thead>
                        <tr>
                            <th width="80">–</th>
                            <th>Ticket Class</th>
                            <th>Event</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($tikets)): ?>
                        <tr><td colspan="6" style="text-align:center;padding:3rem;color:var(--text-muted);">Belum ada kategori tiket.</td></tr>
                        <?php endif; ?>
                        <?php foreach($tikets as $t): ?>
                        <tr>
                            <td><div class="tk-icon"><i class="bi bi-ticket-perforated"></i></div></td>
                            <td>
                                <div class="tk-name"><?= $t['nama_tiket'] ?></div>
                                <div class="tk-id">#TKT-<?= $t['id_tiket'] ?></div>
                            </td>
                            <td>
                                <div class="tk-event-name"><?= $t['nama_event'] ?></div>
                                <div class="tk-event-id">#EV-<?= $t['id_event'] ?></div>
                            </td>
                            <td><div class="tk-price">Rp <?= number_format($t['harga'],0,',','.') ?></div></td>
                            <td>
                                <span class="tk-stock">
                                    <i class="bi bi-box-seam"></i>
                                    <?= number_format($t['kuota']) ?> left
                                </span>
                            </td>
                            <td>
                                <div class="tk-actions">
                                    <button class="tk-btn btn-edit-tk"
                                        data-id="<?= $t['id_tiket'] ?>"
                                        data-nama="<?= htmlspecialchars($t['nama_tiket']) ?>"
                                        data-event="<?= $t['id_event'] ?>"
                                        data-harga="<?= $t['harga'] ?>"
                                        data-kuota="<?= $t['kuota'] ?>" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="?del=<?= $t['id_tiket'] ?>" class="tk-btn danger" onclick="return confirm('Hapus tiket ini?')" title="Delete">
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
<div class="modal fade tk-modal" id="modalAdd" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="tk-modal-header">
                <div class="tk-modal-icon">🎫</div>
                <button type="button" class="tk-close-btn" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="tk-modal-title">Create New Ticket Class</div>
            <div class="tk-modal-sub">Buat kategori tiket untuk salah satu event yang tersedia.</div>
            <div class="tk-modal-divider"></div>
            <form action="" method="post">
                <div class="tk-modal-body">
                    <div class="tk-mb">
                        <label class="tk-label">Nama Kategori</label>
                        <input type="text" name="nama_tiket" class="tk-field" placeholder="E.g. VIP Front Row" required>
                    </div>
                    <div class="tk-mb">
                        <label class="tk-label">Event Terkait</label>
                        <select name="id_event" class="tk-field" required>
                            <option value="">-- Pilih Event --</option>
                            <?php foreach($events as $e): ?>
                            <option value="<?= $e['id_event'] ?>"><?= $e['nama_event'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="tk-grid-2">
                        <div>
                            <label class="tk-label">Harga Tiket</label>
                            <div class="tk-input-group">
                                <div class="tk-input-pre">Rp</div>
                                <input type="number" name="harga" class="tk-field" placeholder="0" required>
                            </div>
                        </div>
                        <div>
                            <label class="tk-label">Stok Awal</label>
                            <input type="number" name="kuota" class="tk-field" placeholder="0" required>
                        </div>
                    </div>
                </div>
                <div class="tk-modal-footer">
                    <button type="button" class="tk-btn-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="submit" class="tk-btn-save"><i class="bi bi-check-lg"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade tk-modal" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="tk-modal-header">
                <div class="tk-modal-icon" style="background:var(--gradient-warning);">✏️</div>
                <button type="button" class="tk-close-btn" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="tk-modal-title">Edit Ticket Class</div>
            <div class="tk-modal-sub">Perbarui data kategori tiket yang sudah ada.</div>
            <div class="tk-modal-divider"></div>
            <form action="" method="post">
                <input type="hidden" name="id" id="etk-id">
                <div class="tk-modal-body">
                    <div class="tk-mb">
                        <label class="tk-label">Nama Kategori</label>
                        <input type="text" name="nama_tiket" id="etk-nama" class="tk-field" required>
                    </div>
                    <div class="tk-mb">
                        <label class="tk-label">Event Terkait</label>
                        <select name="id_event" id="etk-event" class="tk-field" required>
                            <?php foreach($events as $e): ?>
                            <option value="<?= $e['id_event'] ?>"><?= $e['nama_event'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="tk-grid-2">
                        <div>
                            <label class="tk-label">Harga</label>
                            <div class="tk-input-group">
                                <div class="tk-input-pre">Rp</div>
                                <input type="number" name="harga" id="etk-harga" class="tk-field" required>
                            </div>
                        </div>
                        <div>
                            <label class="tk-label">Sisa Stok</label>
                            <input type="number" name="kuota" id="etk-kuota" class="tk-field" required>
                        </div>
                    </div>
                </div>
                <div class="tk-modal-footer">
                    <button type="button" class="tk-btn-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit" class="tk-btn-save"><i class="bi bi-check-lg"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.btn-edit-tk').forEach(btn => {
    btn.addEventListener('click', () => {
        const d = btn.dataset;
        document.getElementById('etk-id').value    = d.id;
        document.getElementById('etk-nama').value  = d.nama;
        document.getElementById('etk-event').value = d.event;
        document.getElementById('etk-harga').value = d.harga;
        document.getElementById('etk-kuota').value = d.kuota;
        new bootstrap.Modal(document.getElementById('modalEdit')).show();
    });
});
</script>
</body>
</html>
