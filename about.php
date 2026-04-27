<?php
require_once 'bootstrap.php';
$conn = getDbConnection();


$total_events = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM event"))['t'];
$total_venues = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM venue"))['t'];
$total_tiket = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM tiket"))['t'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Tentang Kami — E-Tiket</title>
    <meta name="description" content="Pelajari lebih lanjut tentang misi dan visi E-Tiket sebagai platform manajemen tiket modern.">
    <?php include 'includes/head.php'; ?>
    <style>
        /* ─── Hero Section ─── */
        .about-hero {
            padding: 8rem 0 5rem;
            background: linear-gradient(to bottom, var(--bg-surface), var(--bg-base));
            text-align: center;
        }

        /* ─── About Section Styling (Copied from index) ─── */
        .about-wrap {
            padding: 6rem 0;
            position: relative;
        }
        .about-img-box {
            position: relative;
            border-radius: 30px;
            overflow: hidden;
            box-shadow: var(--shadow-glow);
        }
        .about-img-box img {
            width: 100%;
            height: auto;
            display: block;
            transition: transform 0.5s ease;
        }
        .about-img-box:hover img {
            transform: scale(1.05);
        }
        .about-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-top: 2rem;
        }
        .info-pill {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            padding: 1.2rem;
            border-radius: 20px;
            transition: all 0.3s ease;
        }
        .info-pill:hover {
            background: rgba(124, 58, 237, 0.05);
            border-color: rgba(124, 58, 237, 0.3);
            transform: translateY(-5px);
        }
        .info-pill i {
            font-size: 1.5rem;
            color: var(--primary-light);
            margin-bottom: 0.8rem;
            display: block;
        }
        .info-pill-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.3rem;
        }
        .info-pill-desc {
            font-size: 0.78rem;
            color: var(--text-muted);
            line-height: 1.5;
        }

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
    </style>
</head>
<body>

<?php include 'includes/navbar_public.php'; ?>

<section class="about-hero">
    <div class="container">
        <div class="section-tag">Explore Our Story</div>
        <h1 class="hero-title" style="font-size: clamp(2.5rem, 6vw, 4rem); font-weight: 800; margin-bottom: 1rem;">Tentang <span class="text-gradient">E-Tiket</span></h1>
        <p class="section-sub mx-auto" style="max-width: 600px;">Kami berkomitmen untuk menghubungkan orang-orang melalui pengalaman event yang luar biasa dan teknologi tiket yang mulus.</p>
    </div>
</section>

<section class="about-wrap">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="about-img-box">
                    <img src="assets/img/about-me.png" alt="Tentang Kami">
                    <div class="ev-img-overlay"></div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="section-tag">Misi Kami</div>
                <h2 class="section-title">Misi Kami Adalah Memberikan <span class="text-gradient">Pengalaman Terbaik.</span></h2>
                <p class="section-sub mt-3">
                    E-Tiket lahir dari keinginan untuk mempermudah akses masyarakat terhadap berbagai event berkualitas. Kami percaya bahwa teknologi harus mempermudah, bukan mempersulit. Itulah mengapa kami membangun platform yang intuitif, aman, dan dapat diandalkan.
                </p>
                
                <div class="about-info-grid">
                    <div class="info-pill">
                        <i class="ri-heart-pulse-line"></i>
                        <div class="info-pill-title">Visi Kreatif</div>
                        <div class="info-pill-desc">Mendukung pertumbuhan industri kreatif melalui digitalisasi tiket yang efisien.</div>
                    </div>
                    <div class="info-pill">
                        <i class="ri-shield-flash-line"></i>
                        <div class="info-pill-title">Keamanan Utama</div>
                        <div class="info-pill-desc">Setiap transaksi dilindungi dengan sistem enkripsi standar industri terkini.</div>
                    </div>
                </div>

                <p class="section-sub mt-4">
                    Sejak didirikan, kami telah membantu ribuan penyelenggara event untuk menjangkau audiens yang lebih luas. Dengan sistem QR check-in real-time dan manajemen voucher, kami memastikan setiap event berjalan lancar dari awal hingga akhir.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="py-5 mb-5">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <h3 class="text-gradient" style="font-size: 2.5rem; font-weight: 800;"><?= $total_events?></h3>
                <p class="text-muted">Event Terlaksana</p>
            </div>
            <div class="col-md-4">
                <h3 class="text-gradient" style="font-size: 2.5rem; font-weight: 800;"><?= $total_tiket?></h3>
                <p class="text-muted">Tiket Terjual</p>
            </div>
            <div class="col-md-4">
                <h3 class="text-gradient" style="font-size: 2.5rem; font-weight: 800;"><?= $total_venues?></h3>
                <p class="text-muted">Partner Venue</p>
            </div>
        </div>
    </div>
</section>


<?php include 'includes/footer.php'; ?>
</body>
</html>
