<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'user') { header("Location: ../login.php"); exit; }

$events = query("SELECT e.*, v.nama_venue, (SELECT MIN(harga) FROM tiket WHERE id_event = e.id_event) as min_price, (SELECT COUNT(*) FROM tiket WHERE id_event = e.id_event) as tiket_types FROM event e JOIN venue v ON e.id_venue = v.id_venue ORDER BY e.tanggal DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Browse Events — EventTiket</title>
    <?php $is_sub = true; include '../includes/head.php'; ?>
</head>
<body>

<?php include '../includes/sidebar.php'; ?>

<div class="main-content">
    <div class="page-header">
        <div class="page-title">Explore Events</div>
        <div class="page-subtitle">Temukan berbagai event menarik dan amankan tiketmu</div>
    </div>

    <div class="page-body">
        <div class="row g-3">
            <?php foreach($events as $i => $e) : ?>
            <div class="col-md-4 col-sm-6">
                <div class="event-card">
                    <div class="event-card-img" style="background: var(--gradient-primary); height: 160px;">
                        <div class="event-card-img-overlay"></div>
                        <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; font-size:3rem; z-index:1;">🎪</div>
                        <div class="event-date-float">📅 <?= date('d M Y', strtotime($e['tanggal'])) ?></div>
                    </div>
                    <div class="event-card-body">
                        <h3 class="event-name"><?= $e['nama_event'] ?></h3>
                        <div class="event-meta"><i class="bi bi-geo-alt"></i> <?= $e['nama_venue'] ?></div>
                    </div>
                    <div class="event-card-footer">
                        <div>
                            <div class="event-price-label">Starting from</div>
                            <div class="event-price">Rp <?= number_format($e['min_price'], 0, ',', '.') ?></div>
                        </div>
                        <a href="pesan.php?id=<?= $e['id_event'] ?>" class="btn btn-primary btn-sm">Book</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

</body>
</html>
