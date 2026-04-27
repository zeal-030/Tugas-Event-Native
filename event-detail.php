<?php
require_once 'bootstrap.php';
$conn = getDbConnection();

// Global query helper for legacy parts
if (!function_exists('query')) {
    function query($q) {
        global $conn;
        $res = mysqli_query($conn, $q);
        $rows = [];
        if ($res && !is_bool($res)) {
            while ($row = mysqli_fetch_assoc($res)) $rows[] = $row;
        }
        return $rows;
    }
}

if (!isset($_GET['id'])) { header("Location: index.php"); exit; }

$id_event = (int)$_GET['id'];
$event    = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT e.*, v.nama_venue, v.alamat, v.kapasitas
       FROM event e JOIN venue v ON e.id_venue = v.id_venue
      WHERE id_event = $id_event"));
$tikets   = query("SELECT * FROM tiket WHERE id_event = $id_event ORDER BY harga ASC");

if (!$event) { header("Location: index.php"); exit; }

$imgSrc   = !empty($event['gambar']) ? "assets/img/events/" . $event['gambar'] : null;
$minPrice = !empty($tikets) ? min(array_column($tikets, 'harga')) : 0;
$isPastEvent = strtotime($event['tanggal']) < strtotime(date('Y-m-d'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title><?= htmlspecialchars($event['nama_event']) ?> — EventTiket</title>
    <meta name="description" content="Detail event <?= htmlspecialchars($event['nama_event']) ?> di <?= htmlspecialchars($event['nama_venue']) ?>. Beli tiket sekarang!">
    <?php include 'includes/head.php'; ?>
    <style>
        /* ─── Banner ─── */
        .detail-banner {
            position: relative;
            height: 480px;
            overflow: hidden;
            display: flex;
            align-items: flex-end;
        }
        .banner-img {
            position: absolute;
            inset: 0;
            background: var(--gradient-primary);
        }
        .banner-img img {
            width: 100%; height: 100%;
            object-fit: cover;
            filter: brightness(0.6);
        }
        .banner-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom,
                rgba(0,0,0,0.1) 0%,
                rgba(0,0,0,0.35) 40%,
                var(--bg-base) 100%);
        }
        .banner-content {
            position: relative;
            z-index: 2;
            padding: 0 0 3rem;
            width: 100%;
        }
        .banner-breadcrumb {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.75rem;
            color: rgba(255,255,255,0.6);
            margin-bottom: 1rem;
            text-decoration: none;
            transition: color 0.2s;
        }
        .banner-breadcrumb:hover { color: rgba(255,255,255,0.9); }
        .banner-date-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(124,58,237,0.85);
            backdrop-filter: blur(8px);
            color: white;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.35rem 0.9rem;
            border-radius: 50px;
            margin-bottom: 1rem;
            letter-spacing: 0.3px;
        }
        .banner-title {
            font-size: clamp(1.8rem, 5vw, 3rem);
            font-weight: 800;
            color: white;
            letter-spacing: -1.5px;
            line-height: 1.15;
            margin-bottom: 0.75rem;
            text-shadow: 0 2px 20px rgba(0,0,0,0.3);
        }
        .banner-meta {
            display: flex;
            gap: 1.25rem;
            flex-wrap: wrap;
        }
        .banner-meta-item {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.85rem;
            color: rgba(255,255,255,0.75);
        }
        .banner-meta-item i { color: rgba(255,255,255,0.6); font-size: 0.9rem; }

        /* ─── Layout ─── */
        .detail-body { padding: 2.5rem 0 5rem; }

        /* ─── Section headers ─── */
        .detail-section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 1.25rem;
            padding-bottom: 0.85rem;
            border-bottom: 1px solid var(--border);
        }
        .detail-section-title i {
            width: 32px; height: 32px;
            background: rgba(124,58,237,0.12);
            color: var(--primary-light);
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        /* ─── Ticket Cards ─── */
        .ticket-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 1.4rem 1.6rem;
            margin-bottom: 1rem;
            transition: border-color 0.25s, box-shadow 0.25s, transform 0.25s;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            position: relative;
            overflow: hidden;
        }
        .ticket-card::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 4px;
            background: var(--gradient-primary);
            border-radius: 4px 0 0 4px;
            opacity: 0;
            transition: opacity 0.25s;
        }
        .ticket-card:hover {
            border-color: var(--border-hover);
            box-shadow: var(--shadow-glow);
            transform: translateX(4px);
        }
        .ticket-card:hover::before { opacity: 1; }

        .ticket-card-icon {
            width: 44px; height: 44px;
            background: rgba(124,58,237,0.1);
            border: 1px solid rgba(124,58,237,0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: var(--primary-light);
            flex-shrink: 0;
            transition: all 0.25s;
        }
        .ticket-card:hover .ticket-card-icon {
            background: var(--gradient-primary);
            color: white;
            border-color: transparent;
            box-shadow: 0 4px 15px rgba(124,58,237,0.4);
        }
        .ticket-card-info { flex: 1; min-width: 0; }
        .ticket-card-name {
            font-weight: 700;
            color: var(--text-primary);
            font-size: 1rem;
            margin-bottom: 0.2rem;
        }
        .ticket-card-price {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--primary-light);
            letter-spacing: -0.5px;
            margin-bottom: 0.2rem;
        }
        .ticket-card-stock {
            font-size: 0.72rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        .stock-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #34d399;
            display: inline-block;
        }
        .stock-dot.low { background: var(--warning); }
        .stock-dot.empty { background: var(--danger); }

        .btn-ticket-buy {
            background: var(--gradient-primary);
            color: white;
            font-weight: 700;
            font-size: 0.82rem;
            padding: 0.55rem 1.35rem;
            border-radius: 12px;
            text-decoration: none;
            white-space: nowrap;
            transition: transform 0.25s, box-shadow 0.25s;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            flex-shrink: 0;
            box-shadow: 0 4px 15px rgba(124,58,237,0.3);
        }
        .btn-ticket-buy:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(124,58,237,0.5); color: white; }

        .btn-ticket-login {
            background: transparent;
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 0.82rem;
            padding: 0.55rem 1.35rem;
            border-radius: 12px;
            text-decoration: none;
            white-space: nowrap;
            border: 1px solid var(--border);
            transition: all 0.25s;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            flex-shrink: 0;
        }
        .btn-ticket-login:hover { border-color: var(--primary); color: var(--primary-light); }

        /* ─── Info note ─── */
        .info-note {
            background: rgba(6,182,212,0.07);
            border: 1px solid rgba(6,182,212,0.2);
            border-radius: 14px;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
        }
        .info-note-icon {
            width: 36px; height: 36px;
            background: rgba(6,182,212,0.12);
            color: var(--accent);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
            margin-top: 0.1rem;
        }
        .info-note-text { font-size: 0.82rem; color: var(--text-secondary); line-height: 1.6; }

        /* ─── Description ─── */
        .event-desc {
            font-size: 0.9rem;
            color: var(--text-secondary);
            line-height: 1.85;
        }

        /* ─── Sidebar card ─── */
        .info-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            overflow: hidden;
            position: sticky;
            top: 90px;
        }
        .info-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .info-card-header i { color: var(--primary-light); }
        .info-card-body { padding: 1.25rem 1.5rem; }

        .info-row {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
            padding: 0.85rem 0;
            border-bottom: 1px solid var(--border);
        }
        .info-row:last-child { border-bottom: none; }
        .info-row-label {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: var(--text-muted);
        }
        .info-row-value {
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .event-poster {
            width: 100%;
            height: 220px;
            object-fit: cover;
            display: block;
            border-top: 1px solid var(--border);
        }
        .event-poster-placeholder {
            height: 220px;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            opacity: 0.4;
            border-top: 1px solid var(--border);
        }

        /* Price summary */
        .price-summary {
            background: rgba(124,58,237,0.08);
            border: 1px solid rgba(124,58,237,0.15);
            border-radius: 14px;
            padding: 1rem 1.25rem;
            margin-top: 1.25rem;
        }
        .price-summary-label { font-size: 0.7rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .price-summary-value { font-size: 1.4rem; font-weight: 800; color: var(--primary-light); letter-spacing: -0.5px; }

        /* Empty state */
        .empty-tickets {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--text-muted);
        }
        .empty-tickets i { font-size: 2.5rem; display: block; margin-bottom: 0.75rem; opacity: 0.35; }

        @media (max-width: 768px) {
            .detail-banner { height: 360px; }
            .banner-title  { font-size: 1.7rem; }
            .ticket-card   { flex-wrap: wrap; }
        }
    </style>
</head>
<body>
<?php include 'includes/navbar_public.php'; ?>

<!-- ═════════ BANNER ═════════ -->
<div class="detail-banner">
    <div class="banner-img">
        <?php if ($imgSrc): ?>
            <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($event['nama_event']) ?>"
                 onerror="this.parentElement.style.background='var(--gradient-primary)'; this.remove();">
        <?php endif; ?>
    </div>
    <div class="banner-overlay"></div>

    <div class="banner-content">
        <div class="container">
            <a href="index.php" class="banner-breadcrumb">
                <i class="ri-home-4-line"></i> Home <i class="ri-arrow-right-s-line"></i>
                <span style="color: rgba(255,255,255,0.8);">Event Detail</span>
            </a>
            <br>
            <div class="banner-date-tag">
                <i class="ri-calendar-3-line"></i>
                <?= date('d M Y', strtotime($event['tanggal'])) ?>
            </div>
            <h1 class="banner-title"><?= htmlspecialchars($event['nama_event']) ?></h1>
            <div class="banner-meta">
                <span class="banner-meta-item">
                    <i class="ri-map-pin-fill"></i>
                    <?= htmlspecialchars($event['nama_venue']) ?>
                </span>
                <span class="banner-meta-item">
                    <i class="ri-map-2-line"></i>
                    <?= htmlspecialchars($event['alamat']) ?>
                </span>
                <span class="banner-meta-item">
                    <i class="ri-team-line-fill"></i>
                    Kapasitas <?= number_format($event['kapasitas']) ?> Pax
                </span>
            </div>
        </div>
    </div>
</div>

<!-- ═════════ BODY ═════════ -->
<div class="container detail-body">
    <div class="row g-4 g-lg-5">

        <!-- LEFT: Tickets + Description -->
        <div class="col-lg-8">

            <!-- Ticket Selection -->
            <div class="mb-5">
                <div class="detail-section-title">
                    <i class="ri-ticket-2-line-fill"></i>
                    Pilih Kategori Tiket
                </div>

                <?php if (empty($tikets)): ?>
                <div class="empty-tickets">
                    <i class="ri-ticket-line-detailed"></i>
                    <p>Belum ada tiket tersedia untuk event ini.</p>
                </div>
                <?php else: ?>
                    <?php foreach ($tikets as $t): 
                        $stockLow = $t['kuota'] > 0 && $t['kuota'] <= 10;
                        $noStock  = $t['kuota'] == 0;
                    ?>
                    <div class="ticket-card">
                        <div class="ticket-card-icon">
                            <i class="ri-ticket-2-line"></i>
                        </div>
                        <div class="ticket-card-info">
                            <div class="ticket-card-name"><?= htmlspecialchars($t['nama_tiket']) ?></div>
                            <div class="ticket-card-price">
                                Rp <?= number_format($t['harga'], 0, ',', '.') ?>
                            </div>
                            <div class="ticket-card-stock">
                                <span class="stock-dot <?= $noStock ? 'empty' : ($stockLow ? 'low' : '') ?>"></span>
                                <?php if ($noStock): ?>
                                    <span style="color:var(--danger);">Tiket habis</span>
                                <?php elseif ($stockLow): ?>
                                    <span style="color:var(--warning);">Sisa <?= $t['kuota'] ?> tiket — hampir habis!</span>
                                <?php else: ?>
                                    <?= number_format($t['kuota']) ?> tiket tersedia
                                <?php endif; ?>
                            </div>
                        </div>
                        <div>
                            <?php if ($isPastEvent): ?>
                                <span style="font-size:0.78rem; color:var(--danger); border:1px solid var(--danger); border-radius:10px; padding:0.5rem 1rem;">Event Berakhir</span>
                            <?php elseif ($noStock): ?>
                                <span style="font-size:0.78rem; color:var(--text-muted); border:1px solid var(--border); border-radius:10px; padding:0.5rem 1rem;">Habis</span>
                            <?php elseif (isset($_SESSION['login'])): ?>
                                <a href="user/pesan.php?id=<?= $event['id_event'] ?>" class="btn-ticket-buy">
                                    <i class="ri-shopping-bag-4-line"></i> Beli Sekarang
                                </a>
                            <?php else: ?>
                                <a href="login.php" class="btn-ticket-login">
                                    <i class="ri-user-line"></i> Login untuk Beli
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Description -->
            <div class="mb-4">
                <div class="detail-section-title">
                    <i class="bi bi-file-earmark-text"></i>
                    Deskripsi Event
                </div>
                <div class="event-desc">
                    <?php if (!empty($event['deskripsi'])): ?>
                        <?= nl2br(htmlspecialchars($event['deskripsi'])) ?>
                    <?php else: ?>
                        Ini adalah acara spektakuler yang diselenggarakan di <strong><?= htmlspecialchars($event['nama_venue']) ?></strong>.
                        Segera amankan tiket Anda sebelum kehabisan kuota!
                    <?php endif; ?>
                </div>
            </div>

            <!-- Info Note -->
            <div class="info-note mt-4">
                <div class="info-note-icon"><i class="ri-information-fill"></i></div>
                <div class="info-note-text">
                    <strong style="color: var(--accent); display: block; margin-bottom: 0.25rem;">Cara Kerja Tiket Digital</strong>
                    Tiket akan langsung di-generate dalam bentuk <strong>kode QR unik</strong> setelah pembayaran dikonfirmasi.
                    Simpan kode tersebut dan tunjukkan saat memasuki venue untuk proses check-in otomatis.
                </div>
            </div>
        </div>

        <!-- RIGHT: Info Sidebar -->
        <div class="col-lg-4">
            <div class="info-card">
                <div class="info-card-header">
                    <i class="ri-map-2-line-fill"></i>
                    Informasi Event
                </div>

                <!-- Poster -->
                <?php if ($imgSrc): ?>
                    <img src="<?= htmlspecialchars($imgSrc) ?>"
                         class="event-poster" alt="Poster"
                         onerror="this.parentElement.innerHTML='<div class=\'event-poster-placeholder\'>🎪</div>'">
                <?php else: ?>
                    <div class="event-poster-placeholder">🎪</div>
                <?php endif; ?>

                <div class="info-card-body">
                    <div class="info-row">
                        <span class="info-row-label">Venue</span>
                        <span class="info-row-value"><?= htmlspecialchars($event['nama_venue']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-row-label">Alamat</span>
                        <span class="info-row-value" style="font-size:0.82rem; color:var(--text-secondary);"><?= htmlspecialchars($event['alamat']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-row-label">Tanggal</span>
                        <span class="info-row-value"><?= date('d F Y', strtotime($event['tanggal'])) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-row-label">Kapasitas Venue</span>
                        <span class="info-row-value"><?= number_format($event['kapasitas']) ?> Pax</span>
                    </div>

                    <?php if (!empty($tikets)): ?>
                    <div class="price-summary">
                        <div class="price-summary-label">Harga Tiket Mulai Dari</div>
                        <div class="price-summary-value">
                            Rp <?= number_format($minPrice, 0, ',', '.') ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($isPastEvent): ?>
                        <div class="alert alert-danger py-2 text-center" style="font-size: 0.8rem; border-radius: 12px; margin-top: 1rem;">
                            <i class="ri-error-warning-line"></i> Penjualan tiket sudah ditutup.
                        </div>
                    <?php elseif (!isset($_SESSION['login'])): ?>
                    <a href="login.php" class="btn-ticket-buy" style="width:100%; justify-content:center; margin-top:1rem;">
                        <i class="ri-user-line-check"></i> Login & Beli Tiket
                    </a>
                    <?php else: ?>
                    <a href="user/pesan.php?id=<?= $event['id_event'] ?>" class="btn-ticket-buy" style="width:100%; justify-content:center; margin-top:1rem;">
                        <i class="ri-shopping-bag-4-fill"></i> Pesan Tiket Sekarang
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
