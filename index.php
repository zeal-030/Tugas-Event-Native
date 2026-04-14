<?php
session_start();
require_once 'config/db.php';

$featured_events = query("SELECT e.*, v.nama_venue, (SELECT MIN(harga) FROM tiket WHERE id_event = e.id_event) as min_price 
                         FROM event e JOIN venue v ON e.id_venue = v.id_venue 
                         ORDER BY e.tanggal DESC LIMIT 3");

$total_events = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM event"))['t'];
$total_venues = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM venue"))['t'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>EventTiket — Platform Manajemen Tiket Modern</title>
    <?php include 'includes/head.php'; ?>
    <style>
        .hero-glow { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 500px; height: 500px; background: var(--primary); filter: blur(150px); opacity: 0.15; z-index: 0; pointer-events: none; }
        .feature-card { background: var(--bg-card); border: 1px solid var(--border); padding: 2.5rem; border-radius: 24px; transition: all 0.3s ease; height: 100%; }
        .feature-card:hover { border-color: var(--primary); transform: translateY(-10px); box-shadow: var(--shadow-glow); }
        .navbar-landing { backdrop-filter: blur(15px); background: rgba(15, 15, 23, 0.8); border-bottom: 1px solid var(--border); padding: 1rem 0; position: sticky; top: 0; z-index: 1000; }
    </style>
</head>
<body data-theme="dark">

<div class="hero-glow"></div>

<?php include 'includes/navbar_public.php'; ?>

<!-- Hero Section -->
<header class="py-5 mt-4">
    <div class="container text-center" style="position:relative; z-index:1;">
        <div class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill mb-4 border border-primary border-opacity-25" style="letter-spacing:1px; font-weight:700; font-size:0.75rem;">🚀 THE FUTURE OF EVENT MANAGEMENT</div>
        <h1 class="display-3 fw-bolder mb-3 text-white" style="letter-spacing:-3px;">Temukan Pengalaman <br><span class="text-gradient">Tak Terlupakan.</span></h1>
        <p class="lead text-secondary mx-auto mb-5" style="max-width: 650px; font-size:1.1rem; line-height:1.6;">Sistem manajemen tiket event paling modern dengan keamanan tinggi, voucher diskon, dan sistem check-in real-time.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="#events" class="btn btn-primary btn-lg px-5">Lihat Event</a>
            <a href="login.php" class="btn btn-ghost btn-lg px-5">Join Now</a>
        </div>
    </div>
</header>

<!-- Stats -->
<section class="py-5">
    <div class="container">
        <div class="row g-4 justify-content-center text-center">
            <div class="col-md-3">
                <div class="h2 fw-bold text-white mb-0"><?= $total_events ?>+</div>
                <div class="small text-muted text-uppercase tracking-wider">Events Hosted</div>
            </div>
            <div class="col-md-3">
                <div class="h2 fw-bold text-white mb-0"><?= $total_venues ?></div>
                <div class="small text-muted text-uppercase tracking-wider">Partner Venues</div>
            </div>
            <div class="col-md-3">
                <div class="h2 fw-bold text-white mb-0">10k+</div>
                <div class="small text-muted text-uppercase tracking-wider">Tickets Sold</div>
            </div>
        </div>
    </div>
</section>

<!-- Features -->
<section id="features" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-2">Kenapa <span class="text-gradient">EventTiket?</span></h2>
            <p class="text-muted">Fitur canggih untuk penyelenggara dan pembeli.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="stat-icon primary mb-4" style="width:60px; height:60px;"><i class="bi bi-shield-check fs-3"></i></div>
                    <h4 class="fw-bold mb-3">Sistem Aman</h4>
                    <p class="text-muted small">Transaksi aman dengan enkripsi dan kode tiket unik untuk setiap peserta guna mencegah pemalsuan.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="stat-icon success mb-4" style="width:60px; height:60px;"><i class="bi bi-lightning-charge fs-3"></i></div>
                    <h4 class="fw-bold mb-3">Check-in Cepat</h4>
                    <p class="text-muted small">Proses validasi kehadiran di lokasi hanya butuh beberapa detik dengan dashboard check-in petugas.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="stat-icon info mb-4" style="width:60px; height:60px;"><i class="bi bi-gift fs-3"></i></div>
                    <h4 class="fw-bold mb-3">Voucher Diskon</h4>
                    <p class="text-muted small">Gunakan berbagai kode promo menarik untuk mendapatkan harga tiket terbaik bagi event favorit kamu.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Events -->
<section id="events" class="py-5 mb-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h2 class="fw-bold mb-1">Event <span class="text-gradient">Terbaru</span></h2>
                <p class="text-muted small">Jangan sampai ketinggalan, amankan tiketmu sekarang!</p>
            </div>
            <a href="login.php" class="btn btn-ghost btn-sm">Lihat Semua <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="row g-4">
            <?php foreach($featured_events as $i => $e): ?>
            <div class="col-md-4">
                <div class="event-card">
                    <div class="event-card-img">
                        <div class="event-card-img-overlay"></div>
                        <div style="font-size:2.5rem; position:relative; z-index:1;">🎪</div>
                        <div class="event-date-float">📅 <?= date('d M Y', strtotime($e['tanggal'])) ?></div>
                    </div>
                    <div class="event-card-body">
                        <h5 class="event-name"><?= $e['nama_event'] ?></h5>
                        <div class="event-meta small"><i class="bi bi-geo-alt"></i> <?= $e['nama_venue'] ?></div>
                    </div>
                    <div class="event-card-footer">
                        <div>
                            <div class="event-price-label">Mulai dari</div>
                            <div class="event-price">Rp <?= number_format($e['min_price'], 0, ',', '.') ?></div>
                        </div>
                        <a href="event-detail.php?id=<?= $e['id_event'] ?>" class="btn btn-primary btn-sm px-3">Beli Tiket</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-5 mb-5">
    <div class="container">
        <div class="p-5 rounded-4 border border-primary border-opacity-25" style="background: linear-gradient(135deg, rgba(124,58,237,0.1) 0%, rgba(6,182,212,0.1) 100%);">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="fw-bold mb-2">Siap untuk membuat event?</h2>
                    <p class="text-muted mb-0">Daftar sekarang sebagai penyelenggara dan kelola tiketmu dengan mudah.</p>
                </div>
                <div class="col-md-4 text-md-end mt-4 mt-md-0">
                    <a href="login.php" class="btn btn-primary btn-lg px-5">Mulai Sekarang</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

</body>
</html>
