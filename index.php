<?php
session_start();
require_once 'config/db.php';

$featured_events = query("SELECT e.*, v.nama_venue, (SELECT MIN(harga) FROM tiket WHERE id_event = e.id_event) as min_price 
                         FROM event e JOIN venue v ON e.id_venue = v.id_venue 
                         ORDER BY e.tanggal DESC LIMIT 6");

$total_events = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM event"))['t'];
$total_venues = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM venue"))['t'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>EventTiket — Platform Manajemen Tiket Modern</title>
    <meta name="description" content="Platform tiket event modern dengan QR check-in, voucher diskon, dan manajemen real-time.">
    <?php include 'includes/head.php'; ?>
    <style>
        /* ─── Reset untuk landing page ─── */
        body { overflow-x: hidden; }

        /* ─── Navbar ─── */
        .pub-nav {
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            background: rgba(15, 15, 23, 0.75) !important;
            border-bottom: 1px solid rgba(255,255,255,0.06) !important;
            padding: 1.1rem 0 !important;
        }

        /* ─── Hero ─── */
        .hero-wrap {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
            padding: 8rem 1rem 6rem;
            overflow: hidden;
        }

        /* Animated mesh gradient background */
        .hero-bg {
            position: absolute;
            inset: 0;
            z-index: 0;
            background: var(--bg-base);
        }
        .hero-bg::before {
            content: '';
            position: absolute;
            top: -20%;
            left: -10%;
            width: 700px;
            height: 700px;
            background: radial-gradient(circle, rgba(124,58,237,0.25) 0%, transparent 70%);
            animation: drift1 12s ease-in-out infinite alternate;
        }
        .hero-bg::after {
            content: '';
            position: absolute;
            bottom: -20%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(6,182,212,0.18) 0%, transparent 70%);
            animation: drift2 15s ease-in-out infinite alternate;
        }
        @keyframes drift1 {
            from { transform: translate(0, 0) scale(1); }
            to   { transform: translate(60px, 40px) scale(1.1); }
        }
        @keyframes drift2 {
            from { transform: translate(0, 0) scale(1); }
            to   { transform: translate(-50px, -30px) scale(1.08); }
        }

        .hero-content { position: relative; z-index: 1; max-width: 800px; }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(124,58,237,0.12);
            border: 1px solid rgba(124,58,237,0.3);
            color: var(--primary-light);
            padding: 0.45rem 1.1rem;
            border-radius: 50px;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 2rem;
            animation: fadeDown 0.6s ease both;
        }
        .hero-badge::before {
            content: '';
            width: 6px; height: 6px;
            background: var(--primary-light);
            border-radius: 50%;
            animation: blink 1.2s infinite;
        }
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.2; }
        }

        .hero-title {
            font-size: clamp(2.8rem, 7vw, 5rem);
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -2px;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            animation: fadeDown 0.7s ease 0.1s both;
        }
        .hero-title .line2 { display: block; }

        .hero-sub {
            font-size: 1.1rem;
            color: var(--text-secondary);
            max-width: 580px;
            margin: 0 auto 2.5rem;
            line-height: 1.7;
            animation: fadeDown 0.7s ease 0.2s both;
        }

        .hero-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
            animation: fadeDown 0.7s ease 0.3s both;
        }

        .btn-hero-primary {
            background: var(--gradient-primary);
            color: white;
            font-weight: 700;
            padding: 0.85rem 2.5rem;
            border-radius: 14px;
            border: none;
            font-size: 0.95rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 8px 30px rgba(124,58,237,0.4);
            transition: transform 0.25s, box-shadow 0.25s;
        }
        .btn-hero-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 40px rgba(124,58,237,0.55);
            color: white;
        }

        .btn-hero-ghost {
            background: rgba(255,255,255,0.05);
            color: var(--text-primary);
            font-weight: 600;
            padding: 0.85rem 2rem;
            border-radius: 14px;
            border: 1px solid var(--border);
            font-size: 0.95rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: background 0.25s, border-color 0.25s;
        }
        .btn-hero-ghost:hover {
            background: rgba(255,255,255,0.08);
            border-color: rgba(255,255,255,0.15);
            color: var(--text-primary);
        }

        @keyframes fadeDown {
            from { opacity: 0; transform: translateY(-20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Floating ticker / scroll indicator */
        .scroll-hint {
            position: absolute;
            bottom: 2.5rem;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.7rem;
            color: var(--text-muted);
            font-weight: 500;
            letter-spacing: 1px;
            text-transform: uppercase;
            z-index: 1;
            animation: bob 2s ease-in-out infinite;
        }
        .scroll-hint i { font-size: 1.1rem; }
        @keyframes bob {
            0%, 100% { transform: translateX(-50%) translateY(0); }
            50%       { transform: translateX(-50%) translateY(6px); }
        }

        /* ─── Stats Bar ─── */
        .stats-bar {
            background: var(--bg-surface);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            padding: 2rem 0;
        }
        .stat-pill {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.2rem;
        }
        .stat-pill-num {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -1.5px;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .stat-pill-label {
            font-size: 0.68rem;
            color: var(--text-muted);
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .stats-divider {
            width: 1px;
            height: 40px;
            background: var(--border);
            margin: auto;
        }

        /* ─── Section Heading ─── */
        .section-tag {
            display: inline-block;
            background: rgba(124,58,237,0.1);
            border: 1px solid rgba(124,58,237,0.25);
            color: var(--primary-light);
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 0.3rem 0.9rem;
            border-radius: 50px;
            margin-bottom: 0.85rem;
        }
        .section-title {
            font-size: clamp(1.6rem, 4vw, 2.2rem);
            font-weight: 800;
            letter-spacing: -1px;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        .section-sub {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        /* ─── Feature Cards ─── */
        .feat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 2.5rem 2rem;
            height: 100%;
            transition: transform 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .feat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: var(--gradient-primary);
            opacity: 0;
            transition: opacity 0.3s;
        }
        .feat-card:hover { transform: translateY(-8px); border-color: var(--border-hover); box-shadow: var(--shadow-glow); }
        .feat-card:hover::before { opacity: 1; }

        .feat-icon {
            width: 60px; height: 60px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .feat-icon.purple { background: rgba(124,58,237,0.15); color: var(--primary-light); }
        .feat-icon.teal   { background: rgba(16,185,129,0.15); color: #34d399; }
        .feat-icon.cyan   { background: rgba(6,182,212,0.15);  color: var(--accent); }
        .feat-icon.amber  { background: rgba(245,158,11,0.15); color: var(--accent-2); }

        .feat-title { font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.75rem; }
        .feat-desc  { font-size: 0.85rem; color: var(--text-secondary); line-height: 1.7; }

        /* ─── Event Cards ─── */
        .ev-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 24px;
            overflow: hidden;
            transition: transform 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .ev-card:hover { transform: translateY(-8px); border-color: var(--border-hover); box-shadow: 0 20px 60px rgba(0,0,0,0.4), var(--shadow-glow); }

        .ev-img {
            height: 200px;
            position: relative;
            overflow: hidden;
            background: var(--gradient-primary);
        }
        .ev-img img {
            width: 100%; height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }
        .ev-card:hover .ev-img img { transform: scale(1.06); }
        .ev-img-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0) 40%, rgba(0,0,0,0.65));
        }
        .ev-date-tag {
            position: absolute;
            top: 1rem; left: 1rem;
            background: rgba(0,0,0,0.55);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.1);
            color: white;
            font-size: 0.72rem;
            font-weight: 600;
            padding: 0.3rem 0.75rem;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }
        .ev-category-tag {
            position: absolute;
            top: 1rem; right: 1rem;
            background: var(--gradient-primary);
            color: white;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 0.25rem 0.65rem;
            border-radius: 50px;
        }

        .ev-body { padding: 1.4rem 1.4rem 0; flex: 1; }
        .ev-name {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            line-height: 1.35;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .ev-venue {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.78rem;
            color: var(--text-muted);
        }
        .ev-venue i { color: var(--primary-light); font-size: 0.8rem; }

        .ev-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.2rem 1.4rem;
            border-top: 1px solid var(--border);
            margin-top: 1.2rem;
        }
        .ev-price-label { font-size: 0.65rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .ev-price       { font-size: 1.05rem; font-weight: 800; color: var(--primary-light); letter-spacing: -0.5px; }
        .ev-price.free  { color: #34d399; }

        .btn-buy {
            background: var(--gradient-primary);
            color: white;
            font-size: 0.78rem;
            font-weight: 700;
            padding: 0.45rem 1.1rem;
            border-radius: 10px;
            text-decoration: none;
            transition: transform 0.25s, box-shadow 0.25s;
            white-space: nowrap;
            border: none;
        }
        .btn-buy:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(124,58,237,0.4); color: white; }

        /* ─── CTA ─── */
        .cta-wrap {
            background: linear-gradient(135deg, rgba(124,58,237,0.12) 0%, rgba(6,182,212,0.1) 100%);
            border: 1px solid rgba(124,58,237,0.2);
            border-radius: 28px;
            padding: 4rem 3rem;
            position: relative;
            overflow: hidden;
        }
        .cta-wrap::before {
            content: '';
            position: absolute;
            top: -80px; right: -80px;
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(124,58,237,0.15) 0%, transparent 70%);
        }
        .cta-title { font-size: clamp(1.5rem, 4vw, 2rem); font-weight: 800; letter-spacing: -1px; color: var(--text-primary); margin-bottom: 0.6rem; }
        .cta-sub   { font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0; }

        /* ─── Footer ─── */
        .site-footer {
            background: var(--bg-surface);
            border-top: 1px solid var(--border);
            padding: 3rem 0 2rem;
        }
        .footer-brand {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            margin-bottom: 0.75rem;
        }
        .footer-brand-icon {
            width: 36px; height: 36px;
            background: var(--gradient-primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }
        .footer-brand-name {
            font-weight: 800;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .footer-copy {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-bottom: 0;
        }
        .footer-links a {
            font-size: 0.82rem;
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.2s;
        }
        .footer-links a:hover { color: var(--primary-light); }

        /* ─── Responsive ─── */
        @media (max-width: 768px) {
            .hero-wrap { padding: 7rem 1.5rem 5rem; }
            .cta-wrap  { padding: 2.5rem 1.5rem; }
        }
    </style>
</head>
<body>

<?php include 'includes/navbar_public.php'; ?>

<!-- ═══════════════ HERO ═══════════════ -->
<section class="hero-wrap">
    <div class="hero-bg"></div>

    <div class="hero-content">
        <div class="hero-badge">🚀 Platform Tiket Event Modern</div>

        <h1 class="hero-title">
            Temukan Pengalaman
            <span class="line2 text-gradient">Tak Terlupakan.</span>
        </h1>

        <p class="hero-sub">
            Sistem manajemen tiket event dengan keamanan tinggi, voucher diskon, check-in QR Code real-time, dan pengalaman pembelian yang mulus.
        </p>

        <div class="hero-actions">
            <a href="#events" class="btn-hero-primary">
                <i class="bi bi-calendar-event"></i> Lihat Event
            </a>
            <?php if (!isset($_SESSION['login'])): ?>
            <a href="login.php" class="btn-hero-ghost">
                <i class="bi bi-person-plus"></i> Daftar Gratis
            </a>
            <?php else: ?>
            <a href="user/dashboard.php" class="btn-hero-ghost">
                <i class="bi bi-grid"></i> Dashboard
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="scroll-hint">
        <span>Scroll</span>
        <i class="bi bi-chevron-down"></i>
    </div>
</section>

<!-- ═══════════════ STATS ═══════════════ -->
<div class="stats-bar">
    <div class="container">
        <div class="row g-0 text-center">
            <div class="col">
                <div class="stat-pill">
                    <div class="stat-pill-num"><?= $total_events ?>+</div>
                    <div class="stat-pill-label">Events Hosted</div>
                </div>
            </div>
            <div class="col-auto d-none d-md-flex align-items-center px-4">
                <div class="stats-divider"></div>
            </div>
            <div class="col">
                <div class="stat-pill">
                    <div class="stat-pill-num"><?= $total_venues ?></div>
                    <div class="stat-pill-label">Partner Venues</div>
                </div>
            </div>
            <div class="col-auto d-none d-md-flex align-items-center px-4">
                <div class="stats-divider"></div>
            </div>
            <div class="col">
                <div class="stat-pill">
                    <div class="stat-pill-num">10k+</div>
                    <div class="stat-pill-label">Tiket Terjual</div>
                </div>
            </div>
            <div class="col-auto d-none d-md-flex align-items-center px-4">
                <div class="stats-divider"></div>
            </div>
            <div class="col">
                <div class="stat-pill">
                    <div class="stat-pill-num">99%</div>
                    <div class="stat-pill-label">Kepuasan User</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════ FEATURES ═══════════════ -->
<section id="features" class="py-5 my-4">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-tag">Keunggulan</div>
            <h2 class="section-title">Kenapa <span class="text-gradient">EventTiket?</span></h2>
            <p class="section-sub">Fitur canggih untuk penyelenggara event dan para pembeli tiket.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="feat-card">
                    <div class="feat-icon purple"><i class="bi bi-shield-check"></i></div>
                    <div class="feat-title">Sistem Aman</div>
                    <p class="feat-desc">Transaksi aman dengan enkripsi dan kode tiket unik untuk setiap peserta guna mencegah pemalsuan.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feat-card">
                    <div class="feat-icon teal"><i class="bi bi-qr-code-scan"></i></div>
                    <div class="feat-title">QR Check-in</div>
                    <p class="feat-desc">Proses validasi kehadiran di venue hanya dalam hitungan detik menggunakan kamera dan QR Code.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feat-card">
                    <div class="feat-icon cyan"><i class="bi bi-lightning-charge"></i></div>
                    <div class="feat-title">Transaksi Cepat</div>
                    <p class="feat-desc">Proses pembelian tiket yang simpel, cepat, dan bisa dilakukan kapan saja dari perangkat manapun.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feat-card">
                    <div class="feat-icon amber"><i class="bi bi-gift"></i></div>
                    <div class="feat-title">Voucher Diskon</div>
                    <p class="feat-desc">Gunakan berbagai kode promo menarik untuk mendapatkan harga tiket terbaik bagi event favoritmu.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════ EVENTS ═══════════════ -->
<section id="events" class="py-5 mb-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-5 flex-wrap gap-3">
            <div>
                <div class="section-tag">Event</div>
                <h2 class="section-title mb-1">Event <span class="text-gradient">Terbaru</span></h2>
                <p class="section-sub m-0">Jangan sampai ketinggalan, amankan tiketmu sekarang!</p>
            </div>
            <a href="login.php" class="btn-hero-ghost" style="padding: 0.5rem 1.25rem; font-size: 0.82rem; border-radius: 10px;">
                Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <?php if (empty($featured_events)): ?>
        <div style="text-align:center; padding: 4rem; color: var(--text-muted);">
            <i class="bi bi-calendar-x" style="font-size: 3rem; display: block; margin-bottom: 1rem; opacity: 0.4;"></i>
            <p>Belum ada event tersedia.</p>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach($featured_events as $e): 
                $imgSrc = !empty($e['gambar']) ? "assets/img/events/" . $e['gambar'] : null;
                $priceLabel = $e['min_price'] > 0 ? 'Rp ' . number_format($e['min_price'], 0, ',', '.') : 'GRATIS';
                $isFree = $e['min_price'] == 0 || $e['min_price'] === null;
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="ev-card">
                    <div class="ev-img">
                        <?php if ($imgSrc): ?>
                            <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($e['nama_event']) ?>"
                                 onerror="this.parentElement.style.background='var(--gradient-primary)'; this.remove();">
                        <?php else: ?>
                            <!-- Placeholder gradient with icon -->
                            <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; font-size:3rem; opacity:0.4;">🎪</div>
                        <?php endif; ?>
                        <div class="ev-img-overlay"></div>
                        <div class="ev-date-tag"><i class="bi bi-calendar3"></i> <?= date('d M Y', strtotime($e['tanggal'])) ?></div>
                        <?php if ($isFree): ?>
                        <div class="ev-category-tag">GRATIS</div>
                        <?php endif; ?>
                    </div>

                    <div class="ev-body">
                        <h3 class="ev-name"><?= htmlspecialchars($e['nama_event']) ?></h3>
                        <div class="ev-venue">
                            <i class="bi bi-geo-alt-fill"></i>
                            <span><?= htmlspecialchars($e['nama_venue']) ?></span>
                        </div>
                    </div>

                    <div class="ev-footer">
                        <div>
                            <div class="ev-price-label">Mulai dari</div>
                            <div class="ev-price <?= $isFree ? 'free' : '' ?>"><?= $priceLabel ?></div>
                        </div>
                        <a href="event-detail.php?id=<?= $e['id_event'] ?>" class="btn-buy">
                            <?= isset($_SESSION['login']) ? 'Beli Tiket' : 'Lihat Detail' ?> →
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ═══════════════ CTA ═══════════════ -->
<section class="py-5 mb-5">
    <div class="container">
        <div class="cta-wrap">
            <div class="row align-items-center g-4">
                <div class="col-lg-7" style="position:relative; z-index:1;">
                    <div class="section-tag mb-3">Bergabung Sekarang</div>
                    <h2 class="cta-title">Siap untuk membuat atau menghadiri event?</h2>
                    <p class="cta-sub">Daftar sebagai penyelenggara atau pembeli tiket dan nikmati pengalaman manajemen event terbaik.</p>
                </div>
                <div class="col-lg-5 text-lg-end" style="position:relative; z-index:1;">
                    <div class="d-flex gap-3 flex-wrap justify-content-lg-end">
                        <a href="login.php" class="btn-hero-primary">
                            <i class="bi bi-person-check"></i> Mulai Sekarang
                        </a>
                        <a href="#events" class="btn-hero-ghost">
                            Jelajahi Event
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════ FOOTER ═══════════════ -->
<footer class="site-footer">
    <div class="container">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <div class="footer-brand">
                    <div class="footer-brand-icon">🎟️</div>
                    <span class="footer-brand-name">EventTiket</span>
                </div>
                <p class="footer-copy">Platform manajemen tiket event modern yang aman dan cepat.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="footer-links d-flex gap-3 justify-content-md-end">
                    <a href="index.php">Home</a>
                    <a href="#events">Events</a>
                    <a href="#features">Fitur</a>
                    <a href="login.php">Login</a>
                </div>
                <p class="footer-copy mt-2">© <?= date('Y') ?> EventTiket. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
        const target = document.querySelector(a.getAttribute('href'));
        if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
    });
});
</script>
</body>
</html>
