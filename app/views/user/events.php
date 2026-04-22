<?php
/**
 * View: User Browse Events
 * Data dari UserEventController: $events
 */
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Events — <?= APP_NAME ?></title>
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
    <div class="topnav">
        <div class="topnav-left">
            <span style="color: var(--text-muted); font-size: 0.8rem;">Welcome,</span>
            <span style="color: var(--text-primary); font-weight: 700; font-size: 0.9rem;"><?= htmlspecialchars($user['nama']) ?></span>
        </div>
        <div class="topnav-right">
            <a href="<?= BASE_URL ?>/user/dashboard.php" class="btn btn-ghost btn-sm">My Dashboard</a>
        </div>
    </div>

    <div class="page-header">
        <div class="page-title">Explore Events</div>
        <div class="page-subtitle">Temukan berbagai event menarik dan amankan tiketmu</div>
    </div>

    <div class="page-body">
        <div class="row g-3">
            <?php foreach($events as $i => $e) : ?>
            <div class="col-md-4 col-sm-6">
                <div class="event-card">
                    <div class="event-card-img" style="height: 160px; overflow: hidden; position: relative; background: var(--bg-elevated);">
                        <?php if(!empty($e['gambar'])): ?>
                            <img src="<?= BASE_URL ?>/assets/img/events/<?= $e['gambar'] ?>" alt="<?= htmlspecialchars($e['nama_event']) ?>" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; font-size:3rem; z-index:1;">🎪</div>
                        <?php endif; ?>
                        <div class="event-card-img-overlay"></div>
                        <div class="event-date-float">📅 <?= date('d M Y', strtotime($e['tanggal'])) ?></div>
                    </div>
                    <div class="event-card-body">
                        <h3 class="event-name"><?= htmlspecialchars($e['nama_event']) ?></h3>
                        <div class="event-meta"><i class="ri-map-pin-line"></i> <?= htmlspecialchars($e['nama_venue']) ?></div>
                    </div>
                    <div class="event-card-footer">
                        <div>
                            <div class="event-price-label">Starting from</div>
                            <div class="event-price">Rp <?= number_format($e['min_price'], 0, ',', '.') ?></div>
                        </div>
                        <a href="<?= BASE_URL ?>/user/pesan.php?id=<?= $e['id_event'] ?>" class="btn btn-primary btn-sm">Book</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($events)): ?>
            <div class="col-12 text-center py-5">
                <h4 class="text-muted">Oops, belum ada event yang tersedia saat ini.</h4>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
