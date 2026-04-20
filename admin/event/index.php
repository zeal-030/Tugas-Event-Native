<?php
session_start();
require_once '../../config/db.php';
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') { header("Location: ../../login.php"); exit; }

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$limit  = 10;
$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start  = ($page > 1) ? ($page * $limit) - $limit : 0;

$total_data   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM event WHERE nama_event LIKE '%$search%'"))['t'];
$total_pages  = ceil($total_data / $limit);
$stats_upcoming = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM event WHERE tanggal >= CURDATE()"))['t'];
$stats_venues   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM venue"))['t'];

$events = query("SELECT e.*, v.nama_venue FROM event e JOIN venue v ON e.id_venue=v.id_venue
                 WHERE e.nama_event LIKE '%$search%' ORDER BY tanggal DESC LIMIT $start, $limit");

function uploadGambar() {
    if (!isset($_FILES['gambar']) || $_FILES['gambar']['error'] !== 0) return 'default.jpg';
    $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp'])) return 'default.jpg';
    $newName = "event_" . uniqid() . '.' . $ext;
    if (!is_dir('../../assets/img/events/')) mkdir('../../assets/img/events/', 0777, true);
    move_uploaded_file($_FILES['gambar']['tmp_name'], '../../assets/img/events/' . $newName);
    return $newName;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit'])) {
        $nama = mysqli_real_escape_string($conn, $_POST['nama_event']);
        $desc = mysqli_real_escape_string($conn, $_POST['deskripsi']);
        $tgl  = $_POST['tanggal'];
        $vid  = (int)$_POST['id_venue'];
        $img  = uploadGambar();
        mysqli_query($conn, "INSERT INTO event (nama_event, deskripsi, tanggal, id_venue, gambar) VALUES ('$nama','$desc','$tgl','$vid','$img')");
        header("Location: index.php?msg=success_add"); exit;
    }
    if (isset($_POST['edit'])) {
        $id   = (int)$_POST['id'];
        $nama = mysqli_real_escape_string($conn, $_POST['nama_event']);
        $desc = mysqli_real_escape_string($conn, $_POST['deskripsi']);
        $tgl  = $_POST['tanggal'];
        $vid  = (int)$_POST['id_venue'];

        // Kapasitas Check: Pastikan venue baru muat untuk tiket yang sudah ada
        $venue_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT kapasitas FROM venue WHERE id_venue = $vid"));
        $total_tickets = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(kuota) as total FROM tiket WHERE id_event = $id"))['total'] ?? 0;

        if ($total_tickets > $venue_info['kapasitas']) {
            header("Location: index.php?msg=err_capacity&cap=".$venue_info['kapasitas']."&need=".$total_tickets); exit;
        }

        $sql  = "UPDATE event SET nama_event='$nama', deskripsi='$desc', tanggal='$tgl', id_venue='$vid'";
        if ($_FILES['gambar']['error'] === 0) { $img = uploadGambar(); $sql .= ", gambar='$img'"; }
        $sql .= " WHERE id_event=$id";
        mysqli_query($conn, $sql);
        header("Location: index.php?msg=success_edit"); exit;
    }
}

if (isset($_GET['del'])) {
    $id  = (int)$_GET['del'];
    $old = mysqli_fetch_assoc(mysqli_query($conn, "SELECT gambar FROM event WHERE id_event=$id"));
    if ($old && $old['gambar'] != 'default.jpg' && file_exists('../../assets/img/events/'.$old['gambar']))
        unlink('../../assets/img/events/'.$old['gambar']);
    mysqli_query($conn, "DELETE FROM event WHERE id_event=$id");
    header("Location: index.php?msg=success_del"); exit;
}

$venues = query("SELECT * FROM venue");
$msg    = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Event Management — Admin Panel</title>
    <?php $is_sub = true; include '../../includes/head.php'; ?>
    <style>
        /* ---- Page Header ---- */
        .ev-page-header { display:flex; justify-content:space-between; align-items:center; }

        /* ---- Stat Cards ---- */
        .ev-stat { background:var(--bg-card); border:1px solid var(--border); border-radius:20px; padding:1.5rem 1.8rem; display:flex; align-items:center; gap:1.2rem; transition:border-color .3s,box-shadow .3s; }
        .ev-stat:hover { border-color:var(--border-hover); box-shadow:var(--shadow-glow); }
        .ev-stat-icon { width:50px; height:50px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:1.4rem; flex-shrink:0; }
        .ev-stat-icon.purple { background:rgba(124,58,237,.15); color:#a78bfa; }
        .ev-stat-icon.green  { background:rgba(16,185,129,.15);  color:#34d399; }
        .ev-stat-icon.cyan   { background:rgba(6,182,212,.15);   color:#22d3ee; }
        .ev-stat-val  { font-size:1.9rem; font-weight:800; line-height:1; color:var(--text-primary); }
        .ev-stat-lbl  { font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--text-muted); margin-top:4px; }

        /* ---- Table ---- */
        .ev-table-card { background:var(--bg-card); border:1px solid var(--border); border-radius:20px; overflow:hidden; }
        .ev-table-toolbar { display:flex; justify-content:space-between; align-items:center; padding:1.4rem 1.8rem; border-bottom:1px solid var(--border); }
        .ev-table-title { font-size:1rem; font-weight:700; color:var(--text-primary); }

        /* Search */
        .ev-search { position:relative; }
        .ev-search input { background:var(--bg-elevated); border:1px solid var(--border); border-radius:12px; color:var(--text-primary); font-size:.85rem; padding:.6rem 1rem .6rem 2.4rem; width:260px; transition:all .3s; }
        .ev-search input:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(124,58,237,.15); width:300px; }
        .ev-search .ic { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:.85rem; pointer-events:none; }

        /* Table rows */
        .ev-table { width:100%; border-collapse:collapse; }
        .ev-table thead th { padding:.9rem 1.4rem; font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.8px; color:var(--text-muted); background:rgba(255,255,255,.02); border-bottom:1px solid var(--border); }
        .ev-table tbody td { padding:1.1rem 1.4rem; border-bottom:1px solid var(--border); color:var(--text-primary); vertical-align:middle; }
        .ev-table tbody tr:last-child td { border-bottom:none; }
        .ev-table tbody tr { transition:background .2s; }
        .ev-table tbody tr:hover td { background:rgba(255,255,255,.02); }

        /* Poster */
        .ev-poster-wrap { width:72px; height:46px; border-radius:10px; overflow:hidden; border:1px solid var(--border); flex-shrink:0; }
        .ev-poster-wrap img { width:100%; height:100%; object-fit:cover; transition:transform .4s; }
        tr:hover .ev-poster-wrap img { transform:scale(1.08); }

        /* Name */
        .ev-name { font-weight:700; font-size:.9rem; color:var(--text-primary); }
        .ev-id   { font-size:.7rem; color:var(--text-muted); margin-top:2px; }

        /* Date */
        .ev-date-day   { font-size:1.1rem; font-weight:800; color:var(--text-primary); line-height:1; }
        .ev-date-month { font-size:.65rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--text-muted); }

        /* Status */
        .ev-status { display:inline-flex; align-items:center; gap:6px; padding:4px 12px; border-radius:50px; font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.4px; }
        .ev-status-dot { width:6px; height:6px; border-radius:50%; }
        .ev-status.upcoming { background:rgba(124,58,237,.12); color:#a78bfa; border:1px solid rgba(124,58,237,.2); }
        .ev-status.upcoming .ev-status-dot { background:#a78bfa; box-shadow:0 0 6px #a78bfa; }
        .ev-status.past { background:rgba(255,255,255,.04); color:var(--text-muted); border:1px solid var(--border); }
        .ev-status.past .ev-status-dot { background:var(--text-muted); }

        /* Venue */
        .ev-venue { display:inline-flex; align-items:center; gap:8px; background:var(--bg-elevated); border:1px solid var(--border); border-radius:10px; padding:5px 12px; font-size:.8rem; color:var(--text-primary); transition:all .3s; }
        tr:hover .ev-venue { border-color:var(--border-hover); background:var(--bg-hover); }

        /* Actions */
        .ev-actions { display:flex; gap:6px; justify-content:flex-end; }
        .ev-btn-act { width:34px; height:34px; border-radius:8px; border:1px solid var(--border); background:transparent; display:flex; align-items:center; justify-content:center; font-size:.85rem; cursor:pointer; transition:all .25s; color:var(--text-secondary); }
        .ev-btn-act:hover { background:var(--bg-hover); border-color:var(--border-hover); color:var(--text-primary); }
        .ev-btn-act.danger:hover { background:rgba(239,68,68,.1); border-color:rgba(239,68,68,.3); color:#f87171; }

        /* Pagination */
        .ev-pagination { display:flex; justify-content:space-between; align-items:center; padding:1rem 1.6rem; border-top:1px solid var(--border); }
        .ev-page-btns { display:flex; gap:4px; }
        .ev-page-btn { min-width:32px; height:32px; padding:0 8px; border-radius:8px; background:var(--bg-elevated); border:1px solid var(--border); color:var(--text-secondary); font-size:.78rem; font-weight:600; display:flex; align-items:center; justify-content:center; text-decoration:none; transition:all .2s; }
        .ev-page-btn.active { background:var(--gradient-primary); border-color:transparent; color:white; }
        .ev-page-btn:hover:not(.active) { border-color:var(--border-hover); color:var(--text-primary); }

        /* ---- Modal ---- */
        .ev-modal .modal-dialog { max-width:540px; }
        .ev-modal .modal-content { background:var(--bg-surface); border:1px solid var(--border); border-radius:24px; box-shadow:0 25px 80px rgba(0,0,0,.6); overflow:hidden; }
        .ev-modal .modal-backdrop-custom { background:rgba(0,0,0,.7); backdrop-filter:blur(4px); }

        .ev-modal-header { padding:1.6rem 2rem 0; display:flex; align-items:center; justify-content:space-between; }
        .ev-modal-icon { width:44px; height:44px; border-radius:13px; background:var(--gradient-primary); display:flex; align-items:center; justify-content:center; font-size:1.2rem; }
        .ev-modal-title { font-size:1.1rem; font-weight:800; color:var(--text-primary); margin-top:.9rem; padding:0 2rem; }
        .ev-modal-sub   { font-size:.8rem; color:var(--text-muted); padding:4px 2rem 0; margin-bottom:.2rem; }
        .ev-modal-divider { height:1px; background:var(--border); margin:1.2rem 0 0; }
        .ev-modal-body { padding:1.6rem 2rem; }
        .ev-modal-footer { padding:0 2rem 1.8rem; display:flex; gap:.7rem; justify-content:flex-end; }

        /* modal form elements */
        .ev-field-label { font-size:.78rem; font-weight:700; color:var(--text-secondary); text-transform:uppercase; letter-spacing:.4px; margin-bottom:6px; display:block; }
        .ev-field { background:var(--bg-elevated); border:1px solid var(--border); color:var(--text-primary); border-radius:12px; padding:.7rem 1rem; font-family:inherit; font-size:.875rem; width:100%; transition:all .25s; }
        .ev-field:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(124,58,237,.15); }
        .ev-field::placeholder { color:var(--text-muted); }
        .ev-file-label { display:flex; align-items:center; gap:.75rem; background:var(--bg-elevated); border:1.5px dashed var(--border); border-radius:12px; padding:1rem 1.2rem; cursor:pointer; transition:all .3s; }
        .ev-file-label:hover { border-color:var(--primary); background:rgba(124,58,237,.05); }
        .ev-file-label input[type="file"] { display:none; }
        .ev-file-icon { width:36px; height:36px; background:rgba(124,58,237,.12); border-radius:10px; display:flex; align-items:center; justify-content:center; color:#a78bfa; font-size:1.1rem; }
        .ev-file-text { font-size:.82rem; color:var(--text-secondary); }
        .ev-file-text strong { color:var(--text-primary); font-size:.85rem; }

        .ev-btn-cancel { background:var(--bg-elevated); border:1px solid var(--border); color:var(--text-secondary); border-radius:12px; padding:.65rem 1.5rem; font-size:.87rem; font-weight:600; cursor:pointer; transition:all .25s; }
        .ev-btn-cancel:hover { border-color:var(--border-hover); color:var(--text-primary); }
        .ev-btn-save { background:var(--gradient-primary); border:none; color:white; border-radius:12px; padding:.65rem 1.8rem; font-size:.87rem; font-weight:700; cursor:pointer; box-shadow:var(--shadow-primary); transition:all .25s; }
        .ev-btn-save:hover { transform:translateY(-2px); box-shadow:0 8px 25px rgba(124,58,237,.5); }

        .ev-close-btn { width:32px; height:32px; border-radius:8px; background:var(--bg-elevated); border:1px solid var(--border); cursor:pointer; display:flex; align-items:center; justify-content:center; color:var(--text-muted); transition:all .2s; }
        .ev-close-btn:hover { background:var(--bg-hover); color:var(--text-primary); }

        .ev-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        .ev-mb { margin-bottom:1.1rem; }
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
                    <li class="breadcrumb-item active small text-white">Events</li>
                </ol>
            </nav>
        </div>
        <div class="topnav-right">
            <div class="user-badge">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['nama'],0,1)) ?></div>
                <div class="user-info">
                    <div class="user-name"><?= $_SESSION['nama'] ?></div>
                    <div class="user-role">Administrator</div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body" style="padding:2rem 2rem 3rem;">

        <!-- Page Header -->
        <div class="ev-page-header mb-4">
            <div>
                <h1 style="font-size:1.6rem;font-weight:800;color:var(--text-primary);margin:0;">📅 Event Management</h1>
                <p style="color:var(--text-muted);font-size:.85rem;margin:4px 0 0;">Terbitkan dan kelola semua acara yang tersedia untuk publik.</p>
            </div>
            <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalAdd">
                <i class="bi bi-plus-lg"></i> Publish Event
            </button>
        </div>

        <!-- Stat Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="ev-stat">
                    <div class="ev-stat-icon purple"><i class="bi bi-calendar-event"></i></div>
                    <div>
                        <div class="ev-stat-val"><?= $total_data ?></div>
                        <div class="ev-stat-lbl">Total Events</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="ev-stat">
                    <div class="ev-stat-icon green"><i class="bi bi-clock"></i></div>
                    <div>
                        <div class="ev-stat-val"><?= $stats_upcoming ?></div>
                        <div class="ev-stat-lbl">Upcoming</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="ev-stat">
                    <div class="ev-stat-icon cyan"><i class="bi bi-geo-alt"></i></div>
                    <div>
                        <div class="ev-stat-val"><?= $stats_venues ?></div>
                        <div class="ev-stat-lbl">Venues</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert -->
        <?php if($msg): ?>
        <?php if(strpos($msg, 'err_') === 0): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4 ev-modal-backdrop-custom" style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.2); color:#f87171;" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>
                    <?php if($msg=='err_capacity') echo "Gagal memindah venue! Venue baru hanya menampung ".$_GET['cap']." orang, sedangkan tiket yang sudah ada totalnya ".$_GET['need']." orang."; ?>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php else: ?>
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                <div>
                    <?php if($msg=='success_add')  echo 'Event baru berhasil diterbitkan!'; ?>
                    <?php if($msg=='success_edit') echo 'Perubahan event berhasil disimpan!'; ?>
                    <?php if($msg=='success_del')  echo 'Event telah dihapus.'; ?>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php endif; ?>

        <!-- Table Card -->
        <div class="ev-table-card">
            <div class="ev-table-toolbar">
                <div class="ev-table-title">Active Events <span style="color:var(--text-muted);font-weight:500;">(<?= $total_data ?>)</span></div>
                <form method="get" class="ev-search">
                    <i class="bi bi-search ic"></i>
                    <input type="text" name="search" placeholder="Search events..." value="<?= htmlspecialchars($search) ?>">
                </form>
            </div>

            <div class="table-responsive">
                <table class="ev-table">
                    <thead>
                        <tr>
                            <th>Poster</th>
                            <th>Event Name</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Venue</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($events)): ?>
                        <tr><td colspan="6" style="text-align:center;padding:3rem;color:var(--text-muted);">Belum ada event tersedia.</td></tr>
                        <?php endif; ?>
                        <?php foreach($events as $e):
                            $upcoming = strtotime($e['tanggal']) >= strtotime(date('Y-m-d'));
                            $ts = strtotime($e['tanggal']);
                        ?>
                        <tr>
                            <td><div class="ev-poster-wrap"><img src="../../assets/img/events/<?= $e['gambar'] ?>" onerror="this.src='https://placehold.co/80x50/1e1e2e/5a5a78?text=No+Img'"></div></td>
                            <td>
                                <div class="ev-name"><?= $e['nama_event'] ?></div>
                                <div class="ev-id">#EVT-<?= $e['id_event'] ?></div>
                            </td>
                            <td>
                                <div class="ev-date-day"><?= date('d', $ts) ?></div>
                                <div class="ev-date-month"><?= date('M Y', $ts) ?></div>
                            </td>
                            <td>
                                <span class="ev-status <?= $upcoming ? 'upcoming' : 'past' ?>">
                                    <span class="ev-status-dot"></span>
                                    <?= $upcoming ? 'Upcoming' : 'Past' ?>
                                </span>
                            </td>
                            <td>
                                <div class="ev-venue">
                                    <i class="bi bi-geo-alt-fill" style="color:#a78bfa;font-size:.75rem;"></i>
                                    <?= $e['nama_venue'] ?>
                                </div>
                            </td>
                            <td>
                                <div class="ev-actions">
                                    <button class="ev-btn-act btn-edit-trigger"
                                        data-id="<?= $e['id_event'] ?>"
                                        data-nama="<?= htmlspecialchars($e['nama_event']) ?>"
                                        data-desc="<?= htmlspecialchars($e['deskripsi']) ?>"
                                        data-tgl="<?= $e['tanggal'] ?>"
                                        data-vid="<?= $e['id_venue'] ?>" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="?del=<?= $e['id_event'] ?>" class="ev-btn-act danger" onclick="return confirm('Hapus event ini?')" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if($total_pages > 1): ?>
            <div class="ev-pagination">
                <div style="font-size:.8rem;color:var(--text-muted);">Showing <?= count($events) ?> of <?= $total_data ?></div>
                <div class="ev-page-btns">
                    <?php for($i=1;$i<=$total_pages;$i++): ?>
                    <a href="?page=<?= $i ?>&search=<?= $search ?>" class="ev-page-btn <?= $page==$i?'active':'' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ==================== MODAL ADD ==================== -->
<div class="modal fade ev-modal" id="modalAdd" tabindex="-1" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="ev-modal-header">
                <div class="ev-modal-icon">➕</div>
                <button type="button" class="ev-close-btn" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="ev-modal-title">Publish New Event</div>
            <div class="ev-modal-sub">Isi detail acara yang akan diterbitkan untuk publik.</div>
            <div class="ev-modal-divider"></div>

            <form action="" method="post" enctype="multipart/form-data">
                <div class="ev-modal-body">
                    <div class="ev-mb">
                        <label class="ev-field-label">Nama Event</label>
                        <input type="text" name="nama_event" class="ev-field" placeholder="E.g. Musical Night Festival" required>
                    </div>
                    <div class="ev-mb">
                        <label class="ev-field-label">Deskripsi Event</label>
                        <textarea name="deskripsi" class="ev-field" rows="3" placeholder="Ceritakan event yang menarik ini..."></textarea>
                    </div>
                    <div class="ev-grid-2 ev-mb">
                        <div>
                            <label class="ev-field-label">Tanggal Pelaksanaan</label>
                            <input type="date" name="tanggal" class="ev-field" required>
                        </div>
                        <div>
                            <label class="ev-field-label">Lokasi Venue</label>
                            <select name="id_venue" class="ev-field" required>
                                <option value="">-- Pilih Venue --</option>
                                <?php foreach($venues as $v): ?>
                                <option value="<?= $v['id_venue'] ?>"><?= $v['nama_venue'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="ev-field-label">Poster / Banner</label>
                        <label class="ev-file-label">
                            <div class="ev-file-icon"><i class="bi bi-image"></i></div>
                            <div class="ev-file-text">
                                <strong>Klik untuk upload gambar</strong><br>
                                JPG, PNG, WEBP — maks 2MB
                            </div>
                            <input type="file" name="gambar" accept=".jpg,.jpeg,.png,.webp">
                        </label>
                    </div>
                </div>
                <div class="ev-modal-footer">
                    <button type="button" class="ev-btn-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="submit" class="ev-btn-save"><i class="bi bi-send"></i> Publish Sekarang</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== MODAL EDIT ==================== -->
<div class="modal fade ev-modal" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="ev-modal-header">
                <div class="ev-modal-icon" style="background:var(--gradient-warning);">✏️</div>
                <button type="button" class="ev-close-btn" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="ev-modal-title">Edit Event</div>
            <div class="ev-modal-sub">Perbarui informasi event yang sudah ada.</div>
            <div class="ev-modal-divider"></div>

            <form action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edt-id">
                <div class="ev-modal-body">
                    <div class="ev-mb">
                        <label class="ev-field-label">Nama Event</label>
                        <input type="text" name="nama_event" id="edt-nama" class="ev-field" required>
                    </div>
                    <div class="ev-mb">
                        <label class="ev-field-label">Deskripsi</label>
                        <textarea name="deskripsi" id="edt-desc" class="ev-field" rows="3"></textarea>
                    </div>
                    <div class="ev-grid-2 ev-mb">
                        <div>
                            <label class="ev-field-label">Tanggal</label>
                            <input type="date" name="tanggal" id="edt-tgl" class="ev-field" required>
                        </div>
                        <div>
                            <label class="ev-field-label">Venue</label>
                            <select name="id_venue" id="edt-vid" class="ev-field" required>
                                <?php foreach($venues as $v): ?>
                                <option value="<?= $v['id_venue'] ?>"><?= $v['nama_venue'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="ev-field-label">Ganti Poster (Opsional)</label>
                        <label class="ev-file-label">
                            <div class="ev-file-icon"><i class="bi bi-arrow-repeat"></i></div>
                            <div class="ev-file-text">
                                <strong>Upload poster baru</strong><br>
                                Biarkan kosong untuk tetap menggunakan poster lama.
                            </div>
                            <input type="file" name="gambar" accept=".jpg,.jpeg,.png,.webp">
                        </label>
                    </div>
                </div>
                <div class="ev-modal-footer">
                    <button type="button" class="ev-btn-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit" class="ev-btn-save"><i class="bi bi-check-lg"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.btn-edit-trigger').forEach(btn => {
    btn.addEventListener('click', () => {
        const d = btn.dataset;
        document.getElementById('edt-id').value   = d.id;
        document.getElementById('edt-nama').value  = d.nama;
        document.getElementById('edt-desc').value  = d.desc;
        document.getElementById('edt-tgl').value   = d.tgl;
        document.getElementById('edt-vid').value   = d.vid;
        new bootstrap.Modal(document.getElementById('modalEdit')).show();
    });
});
// Update file label text
document.querySelectorAll('input[type="file"]').forEach(inp => {
    inp.addEventListener('change', () => {
        const txt = inp.closest('label').querySelector('strong');
        if (inp.files[0]) txt.textContent = inp.files[0].name;
    });
});
</script>
</body>
</html>
