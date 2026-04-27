<?php
require_once 'bootstrap.php';
$conn = getDbConnection();

// Initialize filters
$search = $_GET['search'] ?? '';
$id_venue = $_GET['id_venue'] ?? '';
$date_filter = $_GET['date'] ?? '';

// Build query
$query_str = "SELECT e.*, v.nama_venue, (SELECT MIN(harga) FROM tiket WHERE id_event = e.id_event) as min_price 
              FROM event e 
              JOIN venue v ON e.id_venue = v.id_venue 
              WHERE 1=1";

$params = [];
$types = "";

if (!empty($search)) {
    $query_str .= " AND e.nama_event LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

if (!empty($id_venue)) {
    $query_str .= " AND e.id_venue = ?";
    $params[] = $id_venue;
    $types .= "i";
}

if (!empty($date_filter)) {
    $query_str .= " AND e.tanggal = ?";
    $params[] = $date_filter;
    $types .= "s";
}

$query_str .= " ORDER BY e.tanggal ASC";

$stmt = mysqli_prepare($conn, $query_str);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$events = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Fetch venues for filter
$venues = mysqli_query($conn, "SELECT * FROM venue ORDER BY nama_venue ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Semua Event — E-Tiket</title>
    <meta name="description" content="Jelajahi semua event menarik dan beli tiket Anda sekarang.">
    <?php include 'includes/head.php'; ?>
    <style>
        .events-hero {
            padding: 8rem 0 4rem;
            background: linear-gradient(to bottom, var(--bg-surface), var(--bg-base));
        }
        .filter-card {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 3rem;
            backdrop-filter: blur(10px);
        }
        .form-control-custom {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            color: var(--text-primary);
            border-radius: 12px;
            padding: 0.6rem 1rem;
        }
        .form-control-custom:focus {
            background: var(--bg-elevated);
            border-color: var(--primary-light);
            box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.15);
            color: var(--text-primary);
        }
        .btn-filter {
            background: var(--gradient-primary);
            border: none;
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-glow);
        }
        .btn-reset {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            color: var(--text-secondary);
            padding: 0.6rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-reset:hover {
            background: var(--bg-hover);
            color: var(--text-primary);
        }

        /* Card styles from index */
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
        .ev-img { height: 200px; position: relative; overflow: hidden; background: var(--gradient-primary); }
        .ev-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease; }
        .ev-card:hover .ev-img img { transform: scale(1.06); }
        .ev-img-overlay { position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(0,0,0,0) 40%, rgba(0,0,0,0.65)); }
        .ev-date-tag { position: absolute; top: 1rem; left: 1rem; background: rgba(0,0,0,0.55); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.1); color: white; font-size: 0.72rem; font-weight: 600; padding: 0.3rem 0.75rem; border-radius: 50px; display: flex; align-items: center; gap: 0.35rem; }
        .ev-body { padding: 1.4rem 1.4rem 0; flex: 1; }
        .ev-name { font-size: 1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .ev-venue { display: flex; align-items: center; gap: 0.35rem; font-size: 0.78rem; color: var(--text-muted); }
        .ev-venue i { color: var(--primary-light); font-size: 0.8rem; }
        .ev-footer { display: flex; align-items: center; justify-content: space-between; padding: 1.2rem 1.4rem; border-top: 1px solid var(--border); margin-top: 1.2rem; }
        .ev-price-label { font-size: 0.65rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .ev-price { font-size: 1.05rem; font-weight: 800; color: var(--primary-light); letter-spacing: -0.5px; }
        .btn-buy { background: var(--gradient-primary); color: white; font-size: 0.78rem; font-weight: 700; padding: 0.45rem 1.1rem; border-radius: 10px; text-decoration: none; transition: transform 0.25s, box-shadow 0.25s; white-space: nowrap; border: none; }
        .btn-buy:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(124,58,237,0.4); color: white; }
    </style>
</head>
<body>

<?php include 'includes/navbar_public.php'; ?>

<section class="events-hero">
    <div class="container text-center">
        <div class="section-tag">Explore Events</div>
        <h1 class="hero-title" style="font-size: 3rem; font-weight: 800; margin-bottom: 1rem;">Temukan <span class="text-gradient">Event Favoritmu</span></h1>
        <p class="section-sub mx-auto" style="max-width: 600px;">Cari dan temukan berbagai event menarik dari berbagai kategori dan lokasi.</p>
    </div>
</section>

<section class="pb-5">
    <div class="container">
        <!-- Filter Form -->
        <div class="filter-card">
            <form action="" method="GET" class="row g-3">
                <div class="col-lg-4">
                    <label class="form-label text-muted small fw-bold text-uppercase">Cari Event</label>
                    <input type="text" name="search" class="form-control form-control-custom" placeholder="Nama event..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-lg-3">
                    <label class="form-label text-muted small fw-bold text-uppercase">Venue</label>
                    <select name="id_venue" class="form-select form-control-custom">
                        <option value="">Semua Venue</option>
                        <?php while($v = mysqli_fetch_assoc($venues)): ?>
                            <option value="<?= $v['id_venue'] ?>" <?= $id_venue == $v['id_venue'] ? 'selected' : '' ?>><?= htmlspecialchars($v['nama_venue']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="form-label text-muted small fw-bold text-uppercase">Tanggal</label>
                    <input type="date" name="date" class="form-control form-control-custom" value="<?= htmlspecialchars($date_filter) ?>">
                </div>
                <div class="col-lg-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn-filter w-100"><i class="ri-search-line"></i></button>
                    <a href="events.php" class="btn-reset"><i class="ri-refresh-line"></i></a>
                </div>
            </form>
        </div>

        <!-- Events Grid -->
        <?php if (empty($events)): ?>
        <div class="text-center py-5">
            <i class="ri-calendar-event-line" style="font-size: 4rem; opacity: 0.2;"></i>
            <p class="mt-3 text-muted">Tidak ada event yang ditemukan sesuai kriteria pencarian Anda.</p>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach($events as $e): 
                $imgSrc = !empty($e['gambar']) ? "assets/img/events/" . $e['gambar'] : null;
                $priceLabel = 'Rp ' . number_format($e['min_price'] ?? 0, 0, ',', '.');
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="ev-card">
                    <div class="ev-img">
                        <?php if ($imgSrc): ?>
                            <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($e['nama_event']) ?>"
                                 onerror="this.parentElement.style.background='var(--gradient-primary)'; this.remove();">
                        <?php else: ?>
                            <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; font-size:3rem; opacity:0.4;"><i class="ri-tent-fill" style="color:white;"></i></div>
                        <?php endif; ?>
                        <div class="ev-img-overlay"></div>
                        <div class="ev-date-tag"><i class="ri-calendar-3-line"></i> <?= date('d M Y', strtotime($e['tanggal'])) ?></div>
                    </div>

                    <div class="ev-body">
                        <h3 class="ev-name"><?= htmlspecialchars($e['nama_event']) ?></h3>
                        <div class="ev-venue">
                            <i class="ri-map-pin-fill"></i>
                            <span><?= htmlspecialchars($e['nama_venue']) ?></span>
                        </div>
                    </div>

                    <div class="ev-footer">
                        <div>
                            <div class="ev-price-label">Mulai dari</div>
                            <div class="ev-price"><?= $priceLabel ?></div>
                        </div>
                        <a href="event-detail.php?id=<?= $e['id_event'] ?>" class="btn-buy">
                            Lihat Detail →
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
</body>
</html>
