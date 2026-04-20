<?php
/**
 * View: Admin Venue Management
 * Data dari VenueController: $venues, $msg
 */
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <title>Venue Management — <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <script>document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'dark');</script>
    <style>
        .vn-page-header{display:flex;justify-content:space-between;align-items:center}
        .vn-stat{background:var(--bg-card);border:1px solid var(--border);border-radius:20px;padding:1.4rem 1.8rem;display:flex;align-items:center;gap:1.1rem;transition:all .3s}
        .vn-stat:hover{border-color:var(--border-hover);box-shadow:var(--shadow-glow)}
        .vn-stat-icon{width:48px;height:48px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0}
        .vn-stat-icon.cyan{background:rgba(6,182,212,.12);color:#22d3ee}
        .vn-stat-val{font-size:1.8rem;font-weight:800;color:var(--text-primary);line-height:1}
        .vn-stat-lbl{font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-top:3px}
        .vn-card{background:var(--bg-card);border:1px solid var(--border);border-radius:20px;overflow:hidden}
        .vn-toolbar{display:flex;justify-content:space-between;align-items:center;padding:1.4rem 1.8rem;border-bottom:1px solid var(--border)}
        .vn-toolbar-title{font-size:1rem;font-weight:700;color:var(--text-primary)}
        .vn-table{width:100%;border-collapse:collapse}
        .vn-table thead th{padding:.9rem 1.4rem;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);background:rgba(255,255,255,.02);border-bottom:1px solid var(--border)}
        .vn-table tbody td{padding:1.2rem 1.4rem;border-bottom:1px solid var(--border);vertical-align:middle}
        .vn-table tbody tr:last-child td{border-bottom:none}
        .vn-table tbody tr:hover td{background:rgba(255,255,255,.02)}
        .vn-icon{width:46px;height:46px;border-radius:13px;background:rgba(6,182,212,.08);border:1px solid rgba(6,182,212,.15);display:flex;align-items:center;justify-content:center;color:#22d3ee;font-size:1.2rem;transition:all .3s}
        tr:hover .vn-icon{background:#22d3ee;color:white;border-color:transparent;box-shadow:0 4px 15px rgba(6,182,212,.4);transform:rotate(-5deg) scale(1.08)}
        .vn-name{font-weight:700;color:var(--text-primary)}.vn-id{font-size:.7rem;color:var(--text-muted);margin-top:2px}
        .vn-addr{font-size:.82rem;color:var(--text-secondary)}
        .vn-cap{display:inline-flex;align-items:center;gap:5px;background:rgba(6,182,212,.08);border:1px solid rgba(6,182,212,.15);color:#22d3ee;border-radius:8px;padding:3px 10px;font-size:.78rem;font-weight:700}
        .vn-actions{display:flex;gap:6px;justify-content:flex-end}
        .vn-btn{width:34px;height:34px;border-radius:8px;border:1px solid var(--border);background:transparent;display:flex;align-items:center;justify-content:center;font-size:.85rem;cursor:pointer;transition:all .25s;color:var(--text-secondary);text-decoration:none}
        .vn-btn:hover{background:var(--bg-hover);border-color:var(--border-hover);color:var(--text-primary)}
        .vn-btn.danger:hover{background:rgba(239,68,68,.1);border-color:rgba(239,68,68,.3);color:#f87171}
        .vn-modal .modal-dialog{max-width:500px}
        .vn-modal .modal-content{background:var(--bg-surface);border:1px solid var(--border);border-radius:24px;overflow:hidden}
        .vn-modal-header{padding:1.6rem 2rem 0;display:flex;align-items:center;justify-content:space-between}
        .vn-modal-icon{width:44px;height:44px;border-radius:13px;background:linear-gradient(135deg,#0891b2,#06b6d4);display:flex;align-items:center;justify-content:center;font-size:1.2rem}
        .vn-modal-title{font-size:1.1rem;font-weight:800;color:var(--text-primary);margin-top:.9rem;padding:0 2rem}
        .vn-modal-sub{font-size:.8rem;color:var(--text-muted);padding:4px 2rem 0}
        .vn-modal-divider{height:1px;background:var(--border);margin:1.2rem 0 0}
        .vn-modal-body{padding:1.6rem 2rem}.vn-modal-footer{padding:0 2rem 1.8rem;display:flex;gap:.7rem;justify-content:flex-end}
        .vn-label{font-size:.78rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;display:block}
        .vn-field{background:var(--bg-elevated);border:1px solid var(--border);color:var(--text-primary);border-radius:12px;padding:.7rem 1rem;font-family:inherit;font-size:.875rem;width:100%;transition:all .25s}
        .vn-field:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(124,58,237,.15)}
        .vn-close-btn{width:32px;height:32px;border-radius:8px;background:var(--bg-elevated);border:1px solid var(--border);cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--text-muted)}
        .vn-btn-cancel{background:var(--bg-elevated);border:1px solid var(--border);color:var(--text-secondary);border-radius:12px;padding:.65rem 1.5rem;font-size:.87rem;font-weight:600;cursor:pointer}
        .vn-btn-save{background:linear-gradient(135deg,#0891b2,#06b6d4);border:none;color:white;border-radius:12px;padding:.65rem 1.8rem;font-size:.87rem;font-weight:700;cursor:pointer;box-shadow:0 4px 15px rgba(6,182,212,.35)}
        .vn-btn-save:hover{transform:translateY(-2px)}
        .vn-mb{margin-bottom:1.1rem}
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../../layouts/sidebar.php'; ?>

<div class="main-content">
    <div class="topnav">
        <div class="topnav-left">
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard.php" class="text-decoration-none small" style="color:var(--text-muted)">Admin</a></li>
                <li class="breadcrumb-item active small text-white">Venues</li>
            </ol></nav>
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
        <div class="vn-page-header mb-4">
            <div>
                <h1 style="font-size:1.6rem;font-weight:800;color:var(--text-primary);margin:0;"><i class="ri-building-2-fill" style="background:linear-gradient(135deg,#0891b2,#06b6d4);-webkit-background-clip:text;-webkit-text-fill-color:transparent;"></i> Venue Management</h1>
                <p style="color:var(--text-muted);font-size:.85rem;margin:4px 0 0;">Kelola lokasi dan kapasitas tempat pelaksanaan event.</p>
            </div>
            <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalAdd"><i class="ri-add-circle-line"></i> Add Venue</button>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="vn-stat"><div class="vn-stat-icon cyan"><i class="ri-map-pin-fill"></i></div><div><div class="vn-stat-val"><?= count($venues) ?></div><div class="vn-stat-lbl">Total Venues</div></div></div>
            </div>
        </div>

        <?php if ($msg): ?>
        <div class="alert alert-<?= strpos($msg,'err_') === 0 ? 'danger' : 'success' ?> alert-dismissible fade show mb-4" role="alert">
            <i class="<?= strpos($msg,'err_') === 0 ? 'ri-alert-fill' : 'ri-checkbox-circle-fill' ?>"></i>
            <?= match($msg) {
                'success_add'  => 'Venue baru berhasil ditambahkan!',
                'success_edit' => 'Data venue berhasil diperbarui!',
                'success_del'  => 'Venue telah dihapus.',
                default        => htmlspecialchars($msg),
            } ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="vn-card">
            <div class="vn-toolbar">
                <div class="vn-toolbar-title">Registered Venues <span style="color:var(--text-muted);font-weight:500;">(<?= count($venues) ?>)</span></div>
            </div>
            <div class="table-responsive">
                <table class="vn-table">
                    <thead><tr><th width="70">–</th><th>Nama Venue</th><th>Alamat</th><th>Kapasitas</th><th style="text-align:right;">Actions</th></tr></thead>
                    <tbody>
                        <?php if (empty($venues)): ?>
                        <tr><td colspan="5" style="text-align:center;padding:3rem;color:var(--text-muted);">Belum ada venue yang ditambahkan.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($venues as $v): ?>
                        <tr>
                            <td><div class="vn-icon"><i class="ri-map-pin-line"></i></div></td>
                            <td>
                                <div class="vn-name"><?= htmlspecialchars($v['nama_venue']) ?></div>
                                <div class="vn-id">#VEN-<?= $v['id_venue'] ?></div>
                            </td>
                            <td><div class="vn-addr"><?= htmlspecialchars($v['alamat']) ?></div></td>
                            <td><span class="vn-cap"><i class="ri-team-line-fill"></i><?= number_format($v['kapasitas']) ?> pax</span></td>
                            <td><div class="vn-actions">
                                <button class="vn-btn btn-edit-vn"
                                    data-id="<?= $v['id_venue'] ?>"
                                    data-nama="<?= htmlspecialchars($v['nama_venue']) ?>"
                                    data-alamat="<?= htmlspecialchars($v['alamat']) ?>"
                                    data-kap="<?= $v['kapasitas'] ?>" title="Edit">
                                    <i class="ri-pencil-line"></i>
                                </button>
                                <a href="?del=<?= $v['id_venue'] ?>" class="vn-btn danger" onclick="return confirm('Hapus venue ini? Event yang terkait mungkin terpengaruh.')" title="Delete">
                                    <i class="ri-delete-bin-6-line"></i>
                                </a>
                            </div></td>
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
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <div class="vn-modal-header"><div class="vn-modal-icon"><i class="ri-map-pin-2-fill" style="color:white;"></i></div><button type="button" class="vn-close-btn" data-bs-dismiss="modal"><i class="ri-close-line"></i></button></div>
        <div class="vn-modal-title">Add New Venue</div>
        <div class="vn-modal-sub">Daftarkan lokasi baru sebagai tempat pelaksanaan event.</div>
        <div class="vn-modal-divider"></div>
        <form action="" method="post"><div class="vn-modal-body">
            <div class="vn-mb"><label class="vn-label">Nama Venue</label><input type="text" name="nama_venue" id="add-nama" class="vn-field" placeholder="E.g. Jakarta Convention Center" required></div>
            <div class="vn-mb"><label class="vn-label">Alamat Lengkap</label><textarea name="alamat" id="add-alamat" class="vn-field" rows="2" placeholder="Jl. Gatot Subroto No. 1, Jakarta" required></textarea></div>
            <div class="vn-mb"><label class="vn-label">Kapasitas (orang)</label><input type="number" name="kapasitas" id="add-kap" class="vn-field" placeholder="0" min="1" required></div>
        </div><div class="vn-modal-footer"><button type="button" class="vn-btn-cancel" data-bs-dismiss="modal">Batal</button><button type="submit" name="submit" class="vn-btn-save"><i class="ri-check-double-line"></i> Simpan</button></div></form>
    </div></div>
</div>

<!-- Modal Edit -->
<div class="modal fade vn-modal" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <div class="vn-modal-header"><div class="vn-modal-icon" style="background:var(--gradient-warning);"><i class="ri-edit-2-line" style="color:white;"></i></div><button type="button" class="vn-close-btn" data-bs-dismiss="modal"><i class="ri-close-line"></i></button></div>
        <div class="vn-modal-title">Edit Venue</div>
        <div class="vn-modal-sub">Perbarui informasi lokasi venue yang sudah ada.</div>
        <div class="vn-modal-divider"></div>
        <form action="" method="post"><input type="hidden" name="id" id="evn-id"><div class="vn-modal-body">
            <div class="vn-mb"><label class="vn-label">Nama Venue</label><input type="text" name="nama_venue" id="evn-nama" class="vn-field" required></div>
            <div class="vn-mb"><label class="vn-label">Alamat Lengkap</label><textarea name="alamat" id="evn-alamat" class="vn-field" rows="2" required></textarea></div>
            <div class="vn-mb"><label class="vn-label">Kapasitas (orang)</label><input type="number" name="kapasitas" id="evn-kap" class="vn-field" min="1" required></div>
        </div><div class="vn-modal-footer"><button type="button" class="vn-btn-cancel" data-bs-dismiss="modal">Batal</button><button type="submit" name="edit" class="vn-btn-save"><i class="ri-check-double-line"></i> Simpan Perubahan</button></div></form>
    </div></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.btn-edit-vn').forEach(btn => {
    btn.addEventListener('click', () => {
        const d = btn.dataset;
        document.getElementById('evn-id').value    = d.id;
        document.getElementById('evn-nama').value  = d.nama;
        document.getElementById('evn-alamat').value = d.alamat;
        document.getElementById('evn-kap').value   = d.kap;
        new bootstrap.Modal(document.getElementById('modalEdit')).show();
    });
});
</script>
</body>
</html>
