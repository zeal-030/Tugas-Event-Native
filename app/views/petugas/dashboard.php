<?php
/**
 * View: Petugas Dashboard
 * Menggunakan struktur yang sama dengan Admin Dashboard agar tampilan konsisten.
 */
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Petugas — <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body>

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="main-content">
    <!-- Top Nav -->
    <div class="topnav">
        <div class="topnav-left">
            <span style="color: var(--text-muted); font-size: 0.8rem;">Welcome back,</span>
            <span style="color: var(--text-primary); font-weight: 700; font-size: 0.9rem;"><?= htmlspecialchars($user['nama']) ?></span>
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

    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="page-title">Dashboard Overview</div>
                <div class="page-subtitle">Monitor status kehadiran peserta secara real-time</div>
            </div>
            <button onclick="location.reload()" class="btn btn-ghost btn-sm">
                <i class="ri-refresh-line"></i> Refresh Data
            </button>
        </div>
    </div>

    <div class="page-body">
        <!-- Stat Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card primary">
                    <div class="stat-icon primary"><i class="ri-group-fill"></i></div>
                    <div class="stat-value"><?= number_format($total) ?></div>
                    <div class="stat-label">Total Peserta</div>
                    <div class="stat-change"><i class="ri-checkbox-circle-line"></i> Terbayar</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card success">
                    <div class="stat-icon success"><i class="ri-checkbox-circle-fill"></i></div>
                    <div class="stat-value"><?= number_format($checked) ?></div>
                    <div class="stat-label">Sudah Check-in</div>
                    <div class="stat-change up"><i class="ri-arrow-up-line"></i> Di lokasi</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card warning">
                    <div class="stat-icon warning"><i class="ri-time-fill"></i></div>
                    <div class="stat-value"><?= number_format($pending) ?></div>
                    <div class="stat-label">Belum Check-in</div>
                    <div class="stat-change"><i class="ri-error-warning-line"></i> Menunggu</div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Recent Activity -->
            <div class="col-lg-8">
                <div class="table-wrapper">
                    <div class="table-header">
                        <div class="table-title">🕒 Riwayat Check-in Terbaru</div>
                        <a href="<?= BASE_URL ?>/petugas/checkin.php" class="btn btn-ghost btn-sm">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Ticket Code</th>
                                    <th>Status</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($recent) > 0): ?>
                                    <?php while($r = mysqli_fetch_assoc($recent)): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="bg-success rounded-circle" style="width:8px; height:8px;"></div>
                                                <span style="color: var(--text-primary);" class="fw-600"><?= htmlspecialchars($r['customer']) ?></span>
                                            </div>
                                        </td>
                                        <td><code><?= $r['kode_tiket'] ?></code></td>
                                        <td><span class="badge-status badge-paid">CHECKED-IN</span></td>
                                        <td style="color: #8e8ea8;" class="small"><?= date('H:i', strtotime($r['waktu_checkin'])) ?> WIB</td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center p-4 text-muted small">Belum ada aktivitas hari ini.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-lg-4">
                <div class="card p-4" style="background: var(--bg-surface) !important; border: 1px solid var(--border) !important; border-radius: 20px;">
                    <h5 class="fw-bold mb-4" style="color: var(--text-primary);">Quick Actions</h5>
                    <div class="d-flex flex-column gap-3">
                        <a href="<?= BASE_URL ?>/petugas/scanner.php" class="btn btn-primary w-100 py-3 rounded-4 fw-bold">
                            <i class="ri-qr-scan-2-line me-2"></i> Buka QR Scanner
                        </a>
                        <a href="<?= BASE_URL ?>/petugas/checkin.php" class="btn btn-ghost w-100 py-3 rounded-4 fw-bold border" style="border-color: var(--border) !important;">
                            <i class="ri-list-check-2 me-2"></i> Daftar Check-in
                        </a>
                        <a href="<?= BASE_URL ?>/petugas/profil.php" class="btn btn-ghost w-100 py-3 rounded-4 border" style="border-color: var(--border) !important;">
                            <i class="ri-user-settings-line me-2"></i> Pengaturan Akun
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
