<?php
/**
 * View: Admin Tiket Management
 * Data dari TiketController: $tikets, $events, $msg
 */
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <title>Ticket Management — <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <script>document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'dark');</script>
    <style>
        .tk-page-header{display:flex;justify-content:space-between;align-items:center}
        .tk-stat{background:var(--bg-card);border:1px solid var(--border);border-radius:20px;padding:1.4rem 1.8rem;display:flex;align-items:center;gap:1.1rem;transition:all .3s}
        .tk-stat:hover{border-color:var(--border-hover);box-shadow:var(--shadow-glow)}
        .tk-stat-icon{width:48px;height:48px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0}
        .tk-stat-icon.green{background:rgba(52,211,153,.12);color:#34d399}.tk-stat-icon.purple{background:rgba(124,58,237,.12);color:#a78bfa}
        .tk-stat-val{font-size:1.8rem;font-weight:800;color:var(--text-primary);line-height:1}
        .tk-stat-lbl{font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-top:3px}
        .tk-card{background:var(--bg-card);border:1px solid var(--border);border-radius:20px;overflow:hidden}
        .tk-toolbar{display:flex;justify-content:space-between;align-items:center;padding:1.4rem 1.8rem;border-bottom:1px solid var(--border)}
        .tk-toolbar-title{font-size:1rem;font-weight:700;color:var(--text-primary)}
        .tk-table{width:100%;border-collapse:collapse}
        .tk-table thead th{padding:.9rem 1.4rem;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);background:rgba(255,255,255,.02);border-bottom:1px solid var(--border)}
        .tk-table tbody td{padding:1.2rem 1.4rem;border-bottom:1px solid var(--border);vertical-align:middle}
        .tk-table tbody tr:last-child td{border-bottom:none}
        .tk-table tbody tr:hover td{background:rgba(255,255,255,.02)}
        .tk-icon{width:46px;height:46px;border-radius:13px;background:rgba(52,211,153,.08);border:1px solid rgba(52,211,153,.15);display:flex;align-items:center;justify-content:center;color:#34d399;font-size:1.2rem;transition:all .3s}
        tr:hover .tk-icon{background:#34d399;color:white;border-color:transparent;box-shadow:0 4px 15px rgba(52,211,153,.4);transform:rotate(-5deg) scale(1.08)}
        .tk-name{font-weight:700;color:var(--text-primary)}.tk-id{font-size:.7rem;color:var(--text-muted);margin-top:2px}
        .tk-event-name{font-weight:600;color:var(--text-primary);font-size:.88rem}.tk-event-id{font-size:.7rem;color:var(--text-muted);margin-top:2px}
        .tk-price{font-size:1.05rem;font-weight:900;color:#34d399;letter-spacing:-.3px}
        .tk-stock{display:inline-flex;align-items:center;gap:5px;background:rgba(255,255,255,.04);border:1px solid var(--border);color:var(--text-muted);border-radius:8px;padding:3px 10px;font-size:.72rem;font-weight:600;margin-top:6px}
        .tk-actions{display:flex;gap:6px;justify-content:flex-end}
        .tk-btn{width:34px;height:34px;border-radius:8px;border:1px solid var(--border);background:transparent;display:flex;align-items:center;justify-content:center;font-size:.85rem;cursor:pointer;transition:all .25s;color:var(--text-secondary);text-decoration:none}
        .tk-btn:hover{background:var(--bg-hover);border-color:var(--border-hover);color:var(--text-primary)}
        .tk-btn.danger:hover{background:rgba(239,68,68,.1);border-color:rgba(239,68,68,.3);color:#f87171}
        .tk-modal .modal-dialog{max-width:520px}
        .tk-modal .modal-content{background:var(--bg-surface);border:1px solid var(--border);border-radius:24px;overflow:hidden}
        .tk-modal-header{padding:1.6rem 2rem 0;display:flex;align-items:center;justify-content:space-between}
        .tk-modal-icon{width:44px;height:44px;border-radius:13px;background:linear-gradient(135deg,#059669,#10b981);display:flex;align-items:center;justify-content:center;font-size:1.2rem}
        .tk-modal-title{font-size:1.1rem;font-weight:800;color:var(--text-primary);margin-top:.9rem;padding:0 2rem}
        .tk-modal-sub{font-size:.8rem;color:var(--text-muted);padding:4px 2rem 0}
        .tk-modal-divider{height:1px;background:var(--border);margin:1.2rem 0 0}
        .tk-modal-body{padding:1.6rem 2rem}.tk-modal-footer{padding:0 2rem 1.8rem;display:flex;gap:.7rem;justify-content:flex-end}
        .tk-label{font-size:.78rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;display:block}
        .tk-field{background:var(--bg-elevated);border:1px solid var(--border);color:var(--text-primary);border-radius:12px;padding:.7rem 1rem;font-family:inherit;font-size:.875rem;width:100%;transition:all .25s}
        .tk-field:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(124,58,237,.15)}
        .tk-input-group{display:flex}.tk-input-pre{background:var(--bg-elevated);border:1px solid var(--border);border-right:none;border-radius:12px 0 0 12px;padding:.7rem 1rem;display:flex;align-items:center;color:var(--text-muted);font-size:.8rem;font-weight:700}
        .tk-input-group .tk-field{border-radius:0 12px 12px 0}
        .tk-close-btn{width:32px;height:32px;border-radius:8px;background:var(--bg-elevated);border:1px solid var(--border);cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--text-muted)}
        .tk-btn-cancel{background:var(--bg-elevated);border:1px solid var(--border);color:var(--text-secondary);border-radius:12px;padding:.65rem 1.5rem;font-size:.87rem;font-weight:600;cursor:pointer}
        .tk-btn-save{background:linear-gradient(135deg,#059669,#10b981);border:none;color:white;border-radius:12px;padding:.65rem 1.8rem;font-size:.87rem;font-weight:700;cursor:pointer;box-shadow:0 4px 15px rgba(16,185,129,.3)}
        .tk-btn-save:hover{transform:translateY(-2px)}
        .tk-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:1rem}.tk-mb{margin-bottom:1.1rem}
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../../layouts/sidebar.php'; ?>

<div class="main-content">
    <div class="topnav">
        <div class="topnav-left">
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard.php" class="text-decoration-none small" style="color:var(--text-muted)">Admin</a></li>
                <li class="breadcrumb-item active small text-white">Tickets</li>
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
        <div class="tk-page-header mb-4">
            <div><h1 style="font-size:1.6rem;font-weight:800;color:var(--text-primary);margin:0;"><i class="ri-ticket-2-fill" style="background:var(--gradient-primary);-webkit-background-clip:text;-webkit-text-fill-color:transparent;"></i> Ticket Management</h1>
                <p style="color:var(--text-muted);font-size:.85rem;margin:4px 0 0;">Kelola kategori tiket, harga, dan stok untuk setiap event.</p></div>
            <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalAdd"><i class="ri-add-circle-line"></i> Add Ticket Class</button>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4"><div class="tk-stat"><div class="tk-stat-icon green"><i class="ri-ticket-2-line"></i></div><div><div class="tk-stat-val"><?= count($tikets) ?></div><div class="tk-stat-lbl">Total Ticket Classes</div></div></div></div>
        </div>

        <?php if ($msg): ?>
        <div class="alert alert-<?= strpos($msg,'err_') === 0 ? 'danger' : 'success' ?> alert-dismissible fade show mb-4" role="alert">
            <i class="<?= strpos($msg,'err_') === 0 ? 'ri-alert-fill' : 'ri-checkbox-circle-fill' ?>"></i>
            <?= match($msg) {
                'success_add'  => 'Kategori tiket berhasil ditambahkan!',
                'success_edit' => 'Data tiket berhasil diperbarui!',
                'success_del'  => 'Data tiket telah dihapus.',
                'err_capacity' => 'Kuota melebihi kapasitas venue (Maks: ' . ($_GET['cap'] ?? 0) . ' pax).',
                'err_price'    => 'Harga tiket minimal Rp 10.000 dan tidak boleh gratis.',
                'err_date'     => 'Tidak dapat menambah/mengubah tiket untuk event yang sudah berlalu.',
                default        => '',
            } ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="tk-card">
            <div class="tk-toolbar"><div class="tk-toolbar-title">Active Ticket Classes <span style="color:var(--text-muted);font-weight:500;">(<?= count($tikets) ?>)</span></div></div>
            <div class="table-responsive">
                <table class="tk-table">
                    <thead><tr><th width="80">–</th><th>Ticket Class</th><th>Event</th><th>Price</th><th>Stock</th><th style="text-align:right;">Actions</th></tr></thead>
                    <tbody>
                        <?php if (empty($tikets)): ?>
                        <tr><td colspan="6" style="text-align:center;padding:3rem;color:var(--text-muted);">Belum ada kategori tiket.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($tikets as $t): ?>
                        <tr>
                            <td><div class="tk-icon"><i class="ri-ticket-2-line"></i></div></td>
                            <td><div class="tk-name"><?= htmlspecialchars($t['nama_tiket']) ?></div><div class="tk-id">#TKT-<?= $t['id_tiket'] ?></div></td>
                            <td><div class="tk-event-name"><?= htmlspecialchars($t['nama_event']) ?></div><div class="tk-event-id">#EV-<?= $t['id_event'] ?></div></td>
                            <td><div class="tk-price">Rp <?= number_format($t['harga'],0,',','.') ?></div></td>
                            <td><span class="tk-stock"><i class="ri-box-3-line"></i><?= number_format($t['kuota']) ?> left</span></td>
                            <td><div class="tk-actions">
                                <button class="tk-btn btn-edit-tk" data-id="<?= $t['id_tiket'] ?>" data-nama="<?= htmlspecialchars($t['nama_tiket']) ?>" data-event="<?= $t['id_event'] ?>" data-harga="<?= $t['harga'] ?>" data-kuota="<?= $t['kuota'] ?>" title="Edit"><i class="ri-pencil-line"></i></button>
                                <a href="?del=<?= $t['id_tiket'] ?>" class="tk-btn danger" onclick="return confirm('Hapus tiket ini?')" title="Delete"><i class="ri-delete-bin-6-line"></i></a>
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
<div class="modal fade tk-modal" id="modalAdd" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <div class="tk-modal-header"><div class="tk-modal-icon"><i class="ri-ticket-2-fill" style="color:white;"></i></div><button type="button" class="tk-close-btn" data-bs-dismiss="modal"><i class="ri-close-line"></i></button></div>
        <div class="tk-modal-title">Create New Ticket Class</div>
        <div class="tk-modal-sub">Buat kategori tiket untuk salah satu event yang tersedia.</div>
        <div class="tk-modal-divider"></div>
        <form action="" method="post"><div class="tk-modal-body">
            <div class="tk-mb"><label class="tk-label">Nama Kategori</label><input type="text" name="nama_tiket" class="tk-field" placeholder="E.g. VIP Front Row" required></div>
            <div class="tk-mb"><label class="tk-label">Event Terkait</label>
                <select name="id_event" class="tk-field" required><option value="">-- Pilih Event --</option>
                    <?php foreach ($events as $e): 
                        if (strtotime($e['tanggal']) < strtotime(date('Y-m-d'))) continue;
                    ?>
                        <option value="<?= $e['id_event'] ?>"><?= htmlspecialchars($e['nama_event']) ?> (<?= date('d M Y', strtotime($e['tanggal'])) ?>)</option>
                    <?php endforeach; ?>
                </select></div>
            <div class="tk-grid-2">
                <div><label class="tk-label">Harga Tiket</label><div class="tk-input-group"><div class="tk-input-pre">Rp</div><input type="number" name="harga" class="tk-field" placeholder="0" min="10000" required></div></div>
                <div><label class="tk-label">Stok Awal</label><input type="number" name="kuota" class="tk-field" placeholder="0" required></div>
            </div>
        </div><div class="tk-modal-footer"><button type="button" class="tk-btn-cancel" data-bs-dismiss="modal">Batal</button><button type="submit" name="submit" class="tk-btn-save"><i class="ri-check-double-line"></i> Simpan</button></div></form>
    </div></div>
</div>

<!-- Modal Edit -->
<div class="modal fade tk-modal" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <div class="tk-modal-header"><div class="tk-modal-icon" style="background:var(--gradient-warning);"><i class="ri-edit-2-line" style="color:white;"></i></div><button type="button" class="tk-close-btn" data-bs-dismiss="modal"><i class="ri-close-line"></i></button></div>
        <div class="tk-modal-title">Edit Ticket Class</div>
        <div class="tk-modal-sub">Perbarui data kategori tiket yang sudah ada.</div>
        <div class="tk-modal-divider"></div>
        <form action="" method="post"><input type="hidden" name="id" id="etk-id"><div class="tk-modal-body">
            <div class="tk-mb"><label class="tk-label">Nama Kategori</label><input type="text" name="nama_tiket" id="etk-nama" class="tk-field" required></div>
            <div class="tk-mb"><label class="tk-label">Event Terkait</label>
                <select name="id_event" id="etk-event" class="tk-field" required>
                    <?php foreach ($events as $e): 
                        $isPast = strtotime($e['tanggal']) < strtotime(date('Y-m-d'));
                    ?>
                        <option value="<?= $e['id_event'] ?>" <?= $isPast ? 'disabled style="color:var(--danger);"' : '' ?>>
                            <?= htmlspecialchars($e['nama_event']) ?> (<?= date('d M Y', strtotime($e['tanggal'])) ?>) <?= $isPast ? '[BERAKHIR]' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select></div>
            <div class="tk-grid-2">
                <div><label class="tk-label">Harga</label><div class="tk-input-group"><div class="tk-input-pre">Rp</div><input type="number" name="harga" id="etk-harga" class="tk-field" min="10000" required></div></div>
                <div><label class="tk-label">Sisa Stok</label><input type="number" name="kuota" id="etk-kuota" class="tk-field" required></div>
            </div>
        </div><div class="tk-modal-footer"><button type="button" class="tk-btn-cancel" data-bs-dismiss="modal">Batal</button><button type="submit" name="edit" class="tk-btn-save"><i class="ri-check-double-line"></i> Simpan Perubahan</button></div></form>
    </div></div>
</div>

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
