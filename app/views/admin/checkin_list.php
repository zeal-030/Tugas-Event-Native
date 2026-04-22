<?php
/**
 * View: Admin Check-in List
 * Data dari ScannerController::list(): $attendees, $events
 */
$user = currentUser();
$selected_event = isset($_GET['event']) ? (int)$_GET['event'] : null;
$selected_status = isset($_GET['status']) ? $_GET['status'] : null;
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Check-in — <?= APP_NAME ?> Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <style>
        .filter-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 20px; padding: 1.5rem; margin-bottom: 2rem; }
        .table-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 20px; overflow: hidden; }
        .status-badge { font-size: 0.72rem; font-weight: 700; padding: 0.35rem 0.85rem; border-radius: 50px; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-sudah { background: rgba(16,185,129,0.12); color: #34d399; border: 1px solid rgba(16,185,129,0.25); }
        .status-belum { background: rgba(255,255,255,0.05); color: var(--text-muted); border: 1px solid var(--border); }
        .time-cell { font-size: 0.8rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.4rem; }
        .customer-name { font-weight: 700; color: var(--text-primary); font-size: 0.9rem; }
        .event-info { font-size: 0.78rem; color: var(--text-secondary); margin-top: 2px; }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="main-content">
    <div class="topnav">
        <div class="topnav-left">
            <i class="ri-team-line" style="font-size:1.1rem; color:var(--primary-light);"></i>
            <span style="font-size:0.85rem; font-weight:600; color:var(--text-primary);">Manajemen Check-in</span>
        </div>
        <div class="topnav-right">
            <div style="font-size: 0.78rem; color: var(--text-muted);"><?= date('l, d F Y') ?></div>
        </div>
    </div>

    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <div class="page-title">Daftar Peserta & Check-in</div>
                <div class="page-subtitle">Pantau kehadiran peserta secara real-time dari semua event</div>
            </div>
            <a href="<?= BASE_URL ?>/admin/scanner.php" class="btn btn-primary">
                <i class="ri-qr-scan-2-line"></i> Buka Live Scanner
            </a>
        </div>
    </div>

    <div class="page-body">
        <!-- Filter Bar -->
        <div class="filter-card">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-secondary fw-bold">Filter Berdasarkan Event</label>
                    <select name="event" class="form-select">
                        <option value="">Semua Event</option>
                        <?php foreach($events as $e): ?>
                            <option value="<?= $e['id_event'] ?>" <?= $selected_event == $e['id_event'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($e['nama_event']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary fw-bold">Status Kehadiran</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="sudah" <?= $selected_status == 'sudah' ? 'selected' : '' ?>>Sudah Check-in</option>
                        <option value="belum" <?= $selected_status == 'belum' ? 'selected' : '' ?>>Belum Check-in</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-ghost w-100"><i class="ri-filter-3-line"></i> Terapkan</button>
                </div>
                <?php if($selected_event || $selected_status): ?>
                <div class="col-md-2">
                    <a href="<?= BASE_URL ?>/admin/checkin.php" class="btn btn-link text-muted text-decoration-none small">Reset Filter</a>
                </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Table -->
        <div class="table-card">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Peserta & Event</th>
                            <th>Kode Tiket</th>
                            <th>Tipe Tiket</th>
                            <th>Status</th>
                            <th>Waktu Check-in</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($attendees)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div style="font-size: 2.5rem; opacity: 0.2; margin-bottom: 1rem;"><i class="ri-user-search-line"></i></div>
                                <h6 class="text-muted">Tidak ada data ditemukan</h6>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php foreach($attendees as $a): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="customer-name"><?= htmlspecialchars($a['customer']) ?></div>
                                <div class="event-info"><i class="ri-calendar-event-line"></i> <?= htmlspecialchars($a['nama_event']) ?></div>
                            </td>
                            <td>
                                <code class="small" style="color:var(--primary-light); background:rgba(124,58,237,0.1); padding:2px 6px; border-radius:4px;">
                                    <?= htmlspecialchars($a['kode_tiket']) ?>
                                </code>
                            </td>
                            <td>
                                <span class="small fw-semibold"><?= htmlspecialchars($a['nama_tiket']) ?></span>
                            </td>
                            <td>
                                <span class="status-badge status-<?= $a['status_checkin'] ?>">
                                    <?= $a['status_checkin'] === 'sudah' ? 'Checked-in' : 'Belum' ?>
                                </span>
                            </td>
                            <td>
                                <?php if($a['status_checkin'] === 'sudah'): ?>
                                    <div class="time-cell">
                                        <i class="ri-time-line"></i>
                                        <?= date('d M Y, H:i', strtotime($a['waktu_checkin'])) ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
