<?php
session_start();
require_once 'config/db.php';

if (!isset($_GET['id'])) { header("Location: index.php"); exit; }

$id_event = (int)$_GET['id'];
$event = mysqli_fetch_assoc(mysqli_query($conn, "SELECT e.*, v.nama_venue, v.alamat, v.kapasitas FROM event e JOIN venue v ON e.id_venue = v.id_venue WHERE id_event = $id_event"));
$tikets = query("SELECT * FROM tiket WHERE id_event = $id_event ORDER BY harga ASC");

if (!$event) { header("Location: index.php"); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?= $event['nama_event'] ?> — EventTiket</title>
    <?php include 'includes/head.php'; ?>
    <style>
        .detail-banner { height: 400px; background: var(--gradient-primary); position: relative; display: flex; align-items: flex-end; padding-bottom: 4rem; overflow: hidden; }
        .detail-banner::before { content: ''; position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(0,0,0,0.2) 0%, var(--bg-base) 100%); }
        .ticket-card-ui { background: var(--bg-surface); border: 1px solid var(--border); border-radius: 20px; padding: 1.5rem; transition: all 0.3s ease; margin-bottom: 1rem; }
        .ticket-card-ui:hover { border-color: var(--primary); background: var(--bg-elevated); }
        .floating-summary { position: sticky; top: 100px; }
    </style>
</head>
<body data-theme="dark">

<?php include 'includes/navbar_public.php'; ?>

<div class="detail-banner">
    <div class="container" style="position:relative; z-index:1;">
        <div class="badge bg-primary px-3 py-2 rounded-pill mb-3" style="font-size:0.75rem;">📅 <?= date('d M Y', strtotime($event['tanggal'])) ?></div>
        <h1 class="display-4 fw-800 text-white mb-2" style="letter-spacing:-2px;"><?= $event['nama_event'] ?></h1>
        <p class="text-secondary mb-0"><i class="bi bi-geo-alt"></i> <?= $event['nama_venue'] ?> · <?= $event['alamat'] ?></p>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-8">
            <h4 class="fw-bold mb-4">Pilih Tiket</h4>
            <?php foreach($tikets as $t): ?>
            <div class="ticket-card-ui d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-1"><?= $t['nama_tiket'] ?></h5>
                    <div class="text-primary fw-bold fs-5">Rp <?= number_format($t['harga'], 0, ',', '.') ?></div>
                    <div class="text-muted small mt-1"><?= $t['kuota'] ?> tiket tersedia</div>
                </div>
                <div class="text-end">
                    <?php if(isset($_SESSION['login'])): ?>
                        <a href="user/pesan.php?id=<?= $event['id_event'] ?>" class="btn btn-primary">Beli Sekarang</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-ghost">Login untuk Beli</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="mt-5">
                <h4 class="fw-bold mb-4">Deskripsi Event</h4>
                <p class="text-secondary">Ini adalah acara spektakuler yang diselenggarakan di <?= $event['nama_venue'] ?>. Segera amankan tiket Anda sebelum kehabisan kuota!</p>
                <div class="card p-4 mt-4 border-dashed border-primary bg-primary bg-opacity-10 text-primary">
                    <div class="d-flex gap-3 align-items-center">
                        <i class="bi bi-info-circle fs-3"></i>
                        <div class="small">Tiket akan langsung di-generate dalam bentuk kode unik setelah pembayaran dikonfirmasi. Tunjukkan kode tersebut saat memasuki venue.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card floating-summary p-4">
                <h5 class="fw-bold mb-3">Informasi Lokasi</h5>
                <div class="bg-elevated p-3 rounded-4 mb-3">
                    <div class="small text-muted mb-1">Venue</div>
                    <div class="fw-bold"><?= $event['nama_venue'] ?></div>
                </div>
                <div class="bg-elevated p-3 rounded-4 mb-3">
                    <div class="small text-muted mb-1">Kapasitas Maksimal</div>
                    <div class="fw-bold"><?= number_format($event['kapasitas']) ?> Pax</div>
                </div>
                <img src="https://source.unsplash.com/random/400x300/?concert,stage&sig=<?= $event['id_event'] ?>" class="w-100 rounded-4 mt-2" alt="venue">
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>
