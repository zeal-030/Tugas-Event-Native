<?php
/**
 * View: Admin Event/index
 * Data dari EventController: $events, $venues, $search, $page, $total_pages,
 *   $total_data, $stats_upcoming, $stats_venues, $msg
 */
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <title>Event Management — <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <script>document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'dark');</script>
    <style>
        .ev-stat { background:var(--bg-card); border:1px solid var(--border); border-radius:20px; padding:1.4rem 1.8rem; display:flex; align-items:center; gap:1.1rem; transition:all .3s; }
        .ev-stat:hover { border-color:var(--border-hover); box-shadow:var(--shadow-glow); }
        .ev-stat-icon { width:50px; height:50px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:1.4rem; flex-shrink:0; }
        .ev-stat-icon.purple { background:rgba(124,58,237,.15); color:#a78bfa; }
        .ev-stat-icon.green  { background:rgba(16,185,129,.15);  color:#34d399; }
        .ev-stat-icon.cyan   { background:rgba(6,182,212,.15);   color:#22d3ee; }
        .ev-stat-val { font-size:1.9rem; font-weight:800; line-height:1; color:var(--text-primary); }
        .ev-stat-lbl { font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--text-muted); margin-top:4px; }
        .ev-table-card { background:var(--bg-card); border:1px solid var(--border); border-radius:20px; overflow:hidden; }
        .ev-table-toolbar { display:flex; justify-content:space-between; align-items:center; padding:1.4rem 1.8rem; border-bottom:1px solid var(--border); }
        .ev-search { position:relative; }
        .ev-search input { background:var(--bg-elevated); border:1px solid var(--border); border-radius:12px; color:var(--text-primary); font-size:.85rem; padding:.6rem 1rem .6rem 2.4rem; width:260px; transition:all .3s; }
        .ev-search input:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(124,58,237,.15); }
        .ev-search .ic { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:.85rem; pointer-events:none; }
        .ev-table { width:100%; border-collapse:collapse; }
        .ev-table thead th { padding:.9rem 1.4rem; font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.8px; color:var(--text-muted); background:rgba(255,255,255,.02); border-bottom:1px solid var(--border); }
        .ev-table tbody td { padding:1.1rem 1.4rem; border-bottom:1px solid var(--border); color:var(--text-primary); vertical-align:middle; }
        .ev-table tbody tr:last-child td { border-bottom:none; }
        .ev-table tbody tr:hover td { background:rgba(255,255,255,.02); }
        .ev-poster-wrap { width:72px; height:46px; border-radius:10px; overflow:hidden; border:1px solid var(--border); }
        .ev-poster-wrap img { width:100%; height:100%; object-fit:cover; transition:transform .4s; }
        tr:hover .ev-poster-wrap img { transform:scale(1.08); }
        .ev-status { display:inline-flex; align-items:center; gap:6px; padding:4px 12px; border-radius:50px; font-size:.68rem; font-weight:700; text-transform:uppercase; }
        .ev-status-dot { width:6px; height:6px; border-radius:50%; }
        .ev-status.upcoming { background:rgba(124,58,237,.12); color:#a78bfa; border:1px solid rgba(124,58,237,.2); }
        .ev-status.upcoming .ev-status-dot { background:#a78bfa; box-shadow:0 0 6px #a78bfa; }
        .ev-status.past { background:rgba(255,255,255,.04); color:var(--text-muted); border:1px solid var(--border); }
        .ev-actions { display:flex; gap:6px; justify-content:flex-end; }
        .ev-btn-act { width:34px; height:34px; border-radius:8px; border:1px solid var(--border); background:transparent; display:flex; align-items:center; justify-content:center; font-size:.85rem; cursor:pointer; transition:all .25s; color:var(--text-secondary); }
        .ev-btn-act:hover { background:var(--bg-hover); border-color:var(--border-hover); color:var(--text-primary); }
        .ev-btn-act.danger:hover { background:rgba(239,68,68,.1); border-color:rgba(239,68,68,.3); color:#f87171; }
        .ev-pagination { display:flex; justify-content:space-between; align-items:center; padding:1rem 1.6rem; border-top:1px solid var(--border); }
        .ev-page-btn { min-width:32px; height:32px; padding:0 8px; border-radius:8px; background:var(--bg-elevated); border:1px solid var(--border); color:var(--text-secondary); font-size:.78rem; font-weight:600; display:flex; align-items:center; justify-content:center; text-decoration:none; transition:all .2s; }
        .ev-page-btn.active { background:var(--gradient-primary); border-color:transparent; color:white; }
        .ev-modal .modal-content { background:var(--bg-surface); border:1px solid var(--border); border-radius:24px; overflow:hidden; }
        .ev-modal-header { padding:1.6rem 2rem 0; display:flex; align-items:center; justify-content:space-between; }
        .ev-modal-icon { width:44px; height:44px; border-radius:13px; background:var(--gradient-primary); display:flex; align-items:center; justify-content:center; font-size:1.2rem; }
        .ev-modal-title { font-size:1.1rem; font-weight:800; color:var(--text-primary); margin-top:.9rem; padding:0 2rem; }
        .ev-modal-sub   { font-size:.8rem; color:var(--text-muted); padding:4px 2rem 0; }
        .ev-modal-divider { height:1px; background:var(--border); margin:1.2rem 0 0; }
        .ev-modal-body { padding:1.6rem 2rem; }
        .ev-modal-footer { padding:0 2rem 1.8rem; display:flex; gap:.7rem; justify-content:flex-end; }
        .ev-field-label { font-size:.78rem; font-weight:700; color:var(--text-secondary); text-transform:uppercase; letter-spacing:.4px; margin-bottom:6px; display:block; }
        .ev-field { background:var(--bg-elevated); border:1px solid var(--border); color:var(--text-primary); border-radius:12px; padding:.7rem 1rem; font-family:inherit; font-size:.875rem; width:100%; transition:all .25s; }
        .ev-field:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(124,58,237,.15); }
        .ev-file-label { display:flex; align-items:center; gap:.75rem; background:var(--bg-elevated); border:1.5px dashed var(--border); border-radius:12px; padding:1rem 1.2rem; cursor:pointer; transition:all .3s; }
        .ev-file-label:hover { border-color:var(--primary); background:rgba(124,58,237,.05); }
        .ev-file-label input[type="file"] { display:none; }
        .ev-file-icon { width:36px; height:36px; background:rgba(124,58,237,.12); border-radius:10px; display:flex; align-items:center; justify-content:center; color:#a78bfa; font-size:1.1rem; }
        .ev-btn-cancel { background:var(--bg-elevated); border:1px solid var(--border); color:var(--text-secondary); border-radius:12px; padding:.65rem 1.5rem; font-size:.87rem; font-weight:600; cursor:pointer; transition:all .25s; }
        .ev-btn-save { background:var(--gradient-primary); border:none; color:white; border-radius:12px; padding:.65rem 1.8rem; font-size:.87rem; font-weight:700; cursor:pointer; box-shadow:var(--shadow-primary); transition:all .25s; }
        .ev-btn-save:hover { transform:translateY(-2px); }
        .ev-close-btn { width:32px; height:32px; border-radius:8px; background:var(--bg-elevated); border:1px solid var(--border); cursor:pointer; display:flex; align-items:center; justify-content:center; color:var(--text-muted); transition:all .2s; }
        .ev-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        .ev-mb { margin-bottom:1.1rem; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../../layouts/sidebar.php'; ?>

<div class="main-content">
    <div class="topnav">
        <div class="topnav-left">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard.php" class="text-decoration-none small" style="color:var(--text-muted)">Admin</a></li>
                    <li class="breadcrumb-item active small text-white">Events</li>
                </ol>
            </nav>
        </div>
        <div class="topnav-right">
            <div style="font-size: 0.78rem; color: var(--text-muted);"><?= date('l, d F Y') ?></div>
            <div class="user-badge">
                <div class="user-avatar"><?= strtoupper(substr($user['nama'], 0, 1)) ?></div>
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars($user['nama']) ?></div>
                    <div class="user-role"><?= htmlspecialchars($user['role']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body" style="padding:2rem 2rem 3rem;">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 style="font-size:1.6rem;font-weight:800;color:var(--text-primary);margin:0;"><i class="ri-calendar-event-fill" style="background:var(--gradient-primary);-webkit-background-clip:text;-webkit-text-fill-color:transparent;"></i> Event Management</h1>
                <p style="color:var(--text-muted);font-size:.85rem;margin:4px 0 0;">Terbitkan dan kelola semua acara yang tersedia untuk publik.</p>
            </div>
            <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalAdd">
                <i class="ri-add-circle-line"></i> Publish Event
            </button>
        </div>

        <div class="row g-3 mb-4">
            <?php
            $cards = [
                ['purple', 'ri-calendar-event-fill', $total_data,      'Total Events'],
                ['green',  'ri-time-fill',           $stats_upcoming,  'Upcoming'],
                ['cyan',   'ri-map-pin-fill',        $stats_venues,    'Venues'],
            ];
            foreach ($cards as [$color, $icon, $val, $lbl]): ?>
            <div class="col-md-4">
                <div class="ev-stat">
                    <div class="ev-stat-icon <?= $color ?>"><i class="<?= $icon ?>"></i></div>
                    <div><div class="ev-stat-val"><?= $val ?></div><div class="ev-stat-lbl"><?= $lbl ?></div></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($msg): ?>
        <div class="alert alert-<?= strpos($msg, 'err_') === 0 ? 'danger' : 'success' ?> alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-<?= strpos($msg, 'err_') === 0 ? 'exclamation-triangle-fill' : 'check-circle-fill' ?>"></i>
            <?php
            echo match($msg) {
                'success_add'  => 'Event baru berhasil diterbitkan!',
                'success_edit' => 'Perubahan event berhasil disimpan!',
                'success_del'  => 'Event telah dihapus.',
                'err_capacity' => "Gagal! Venue baru hanya menampung {$_GET['cap']} orang, tiket yang ada: {$_GET['need']} orang.",
                default        => '',
            };
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="ev-table-card">
            <div class="ev-table-toolbar">
                <div style="font-size:1rem; font-weight:700; color:var(--text-primary);">Active Events <span style="color:var(--text-muted);font-weight:500;">(<?= $total_data ?>)</span></div>
                <form method="get" class="ev-search">
                    <i class="ri-search-line ic"></i>
                    <input type="text" name="search" placeholder="Search events..." value="<?= htmlspecialchars($search) ?>">
                </form>
            </div>
            <div class="table-responsive">
                <table class="ev-table">
                    <thead>
                        <tr><th>Poster</th><th>Event Name</th><th>Date</th><th>Status</th><th>Venue</th><th style="text-align:right;">Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($events)): ?>
                        <tr><td colspan="6" style="text-align:center;padding:3rem;color:var(--text-muted);">Belum ada event.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($events as $e):
                            $upcoming = strtotime($e['tanggal']) >= strtotime(date('Y-m-d'));
                            $ts = strtotime($e['tanggal']);
                        ?>
                        <tr>
                            <td><div class="ev-poster-wrap"><img src="<?= BASE_URL ?>/assets/img/events/<?= $e['gambar'] ?>" onerror="this.src='https://placehold.co/80x50/1e1e2e/5a5a78?text=No+Img'"></div></td>
                            <td>
                                <div style="font-weight:700;"><?= htmlspecialchars($e['nama_event']) ?></div>
                                <div style="font-size:.7rem; color:var(--text-muted);">#EVT-<?= $e['id_event'] ?></div>
                            </td>
                            <td>
                                <div style="font-size:1.1rem; font-weight:800;"><?= date('d', $ts) ?></div>
                                <div style="font-size:.65rem; font-weight:700; text-transform:uppercase; color:var(--text-muted);"><?= date('M Y', $ts) ?></div>
                            </td>
                            <td>
                                <span class="ev-status <?= $upcoming ? 'upcoming' : 'past' ?>">
                                    <span class="ev-status-dot"></span>
                                    <?= $upcoming ? 'Upcoming' : 'Past' ?>
                                </span>
                            </td>
                            <td><div style="display:inline-flex; align-items:center; gap:8px; background:var(--bg-elevated); border:1px solid var(--border); border-radius:10px; padding:5px 12px; font-size:.8rem;"><i class="ri-map-pin-fill" style="color:#a78bfa;"></i><?= htmlspecialchars($e['nama_venue']) ?></div></td>
                            <td>
                                <div class="ev-actions">
                                    <button class="ev-btn-act btn-edit-trigger"
                                        data-id="<?= $e['id_event'] ?>" data-nama="<?= htmlspecialchars($e['nama_event']) ?>"
                                        data-desc="<?= htmlspecialchars($e['deskripsi']) ?>" data-tgl="<?= $e['tanggal'] ?>"
                                        data-vid="<?= $e['id_venue'] ?>" title="Edit"><i class="ri-pencil-line"></i>
                                    </button>
                                    <a href="?del=<?= $e['id_event'] ?>&search=<?= $search ?>&page=<?= $page ?>" class="ev-btn-act danger" onclick="return confirm('Hapus event ini?')" title="Delete"><i class="ri-delete-bin-6-line"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($total_pages > 1): ?>
            <div class="ev-pagination">
                <div style="font-size:.8rem;color:var(--text-muted);">Showing <?= count($events) ?> of <?= $total_data ?></div>
                <div style="display:flex; gap:4px;">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>&search=<?= $search ?>" class="ev-page-btn <?= $page == $i ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Add -->
<div class="modal fade ev-modal" id="modalAdd" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:540px;">
        <div class="modal-content">
            <div class="ev-modal-header"><div class="ev-modal-icon"><i class="ri-add-line" style="color:white;"></i></div><button type="button" class="ev-close-btn" data-bs-dismiss="modal"><i class="ri-close-line"></i></button></div>
            <div class="ev-modal-title">Publish New Event</div>
            <div class="ev-modal-sub">Isi detail acara yang akan diterbitkan untuk publik.</div>
            <div class="ev-modal-divider"></div>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="ev-modal-body">
                    <div class="ev-mb"><label class="ev-field-label">Nama Event</label><input type="text" name="nama_event" class="ev-field" placeholder="E.g. Musical Night Festival" required></div>
                    <div class="ev-mb"><label class="ev-field-label">Deskripsi Event</label><textarea name="deskripsi" class="ev-field" rows="3" placeholder="Ceritakan event ini..."></textarea></div>
                    <div class="ev-grid-2 ev-mb">
                        <div><label class="ev-field-label">Tanggal</label><input type="date" name="tanggal" class="ev-field" required></div>
                        <div><label class="ev-field-label">Venue</label>
                            <select name="id_venue" class="ev-field" required>
                                <option value="">-- Pilih Venue --</option>
                                <?php foreach ($venues as $v): ?><option value="<?= $v['id_venue'] ?>"><?= htmlspecialchars($v['nama_venue']) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div><label class="ev-field-label">Poster / Banner</label>
                        <label class="ev-file-label"><div class="ev-file-icon"><i class="ri-image-line"></i></div><div style="font-size:.82rem; color:var(--text-secondary);"><strong>Klik untuk upload</strong><br>JPG, PNG, WEBP</div><input type="file" name="gambar" accept=".jpg,.jpeg,.png,.webp"></label>
                    </div>
                </div>
                <div class="ev-modal-footer"><button type="button" class="ev-btn-cancel" data-bs-dismiss="modal">Batal</button><button type="submit" name="submit" class="ev-btn-save"><i class="ri-send-plane-line"></i> Publish</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade ev-modal" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:540px;">
        <div class="modal-content">
            <div class="ev-modal-header"><div class="ev-modal-icon" style="background:var(--gradient-warning);"><i class="ri-edit-2-line" style="color:white;"></i></div><button type="button" class="ev-close-btn" data-bs-dismiss="modal"><i class="ri-close-line"></i></button></div>
            <div class="ev-modal-title">Edit Event</div>
            <div class="ev-modal-sub">Perbarui informasi event yang sudah ada.</div>
            <div class="ev-modal-divider"></div>
            <form action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edt-id">
                <div class="ev-modal-body">
                    <div class="ev-mb"><label class="ev-field-label">Nama Event</label><input type="text" name="nama_event" id="edt-nama" class="ev-field" required></div>
                    <div class="ev-mb"><label class="ev-field-label">Deskripsi</label><textarea name="deskripsi" id="edt-desc" class="ev-field" rows="3"></textarea></div>
                    <div class="ev-grid-2 ev-mb">
                        <div><label class="ev-field-label">Tanggal</label><input type="date" name="tanggal" id="edt-tgl" class="ev-field" required></div>
                        <div><label class="ev-field-label">Venue</label>
                            <select name="id_venue" id="edt-vid" class="ev-field" required>
                                <?php foreach ($venues as $v): ?><option value="<?= $v['id_venue'] ?>"><?= htmlspecialchars($v['nama_venue']) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div><label class="ev-field-label">Ganti Poster (Opsional)</label>
                        <label class="ev-file-label"><div class="ev-file-icon"><i class="ri-refresh-line"></i></div><div style="font-size:.82rem; color:var(--text-secondary);"><strong>Upload poster baru</strong><br>Kosongkan untuk tetap pakai lama.</div><input type="file" name="gambar" accept=".jpg,.jpeg,.png,.webp"></label>
                    </div>
                </div>
                <div class="ev-modal-footer"><button type="button" class="ev-btn-cancel" data-bs-dismiss="modal">Batal</button><button type="submit" name="edit" class="ev-btn-save"><i class="ri-check-double-line"></i> Simpan</button></div>
            </form>
        </div>
    </div>
</div>

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
</script>
</body>
</html>
