<?php
/**
 * View: User Riwayat Tiket
 * Data dari UserDashboardController::riwayat():
 *   $orders (array), $conn
 */
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tickets — <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <script>document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'dark');</script>
    <style>
        .ticket-item{background:var(--bg-surface);border:1px solid var(--border);border-radius:24px;padding:1.5rem;margin-bottom:1.5rem;transition:all .3s}
        .ticket-item:hover{transform:translateY(-5px);border-color:var(--primary);box-shadow:var(--shadow-md)}
        .ticket-badge{font-size:.7rem;padding:.4rem .8rem;border-radius:50px;font-weight:700;text-transform:uppercase;letter-spacing:.5px}
        .badge-ready{background:#10b981;color:white;box-shadow:0 4px 10px rgba(16,185,129,.3)}
        .badge-used{background:var(--bg-elevated);color:var(--text-muted);border:1px solid var(--border)}
        .modal-ticket{background:#11111d;color:white;border-radius:25px;overflow:hidden;border:1px solid rgba(255,255,255,.1)}
        .ticket-top{background:var(--gradient-primary);padding:2rem;position:relative}
        .ticket-top::after{content:'';position:absolute;bottom:-15px;left:0;right:0;height:30px;background:#11111d;clip-path:polygon(0% 50%,5% 100%,10% 50%,15% 100%,20% 50%,25% 100%,30% 50%,35% 100%,40% 50%,45% 100%,50% 50%,55% 100%,60% 50%,65% 100%,70% 50%,75% 100%,80% 50%,85% 100%,90% 50%,95% 100%,100% 50%)}
        .ticket-bottom{padding:2.5rem 2rem 2rem;text-align:center}
        .qr-large{background:white;padding:15px;border-radius:20px;display:inline-block;margin-bottom:1.5rem;box-shadow:0 10px 40px rgba(0,0,0,.5);border:5px solid #f8fafc}
        .text-muted-custom { color: var(--text-muted) !important; opacity: 0.8; }
        .text-secondary-custom { color: var(--text-secondary) !important; opacity: 0.9; }
        [data-theme="dark"] .text-muted-custom { color: #cbd5e1 !important; opacity: 0.6; }
        [data-theme="dark"] .text-secondary-custom { color: #f1f5f9 !important; opacity: 0.8; }
    </style>
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
            <a href="<?= BASE_URL ?>/user/events.php" class="btn btn-primary btn-sm">Explore Events</a>
        </div>
    </div>

    <div class="page-header mt-4 pb-0">
        <div class="page-title">My Tickets <i class="ri-ticket-2-fill text-primary"></i></div>
        <div class="page-subtitle">Klik tiket untuk melihat detail lengkap dan QR Code</div>

        <?php if (isset($_GET['success']) || isset($_GET['payment_success'])): ?>
            <div class="alert alert-success mt-4 mb-0"><i class="ri-checkbox-circle-line-fill"></i> Transaksi berhasil! Tiket Anda sudah aktif.</div>
        <?php endif; ?>
        <?php if (isset($_GET['cancelled'])): ?>
            <div class="alert alert-warning mt-4 mb-0"><i class="ri-alert-fill"></i> Pesanan tiket Anda berhasil dibatalkan.</div>
        <?php endif; ?>
    </div>

    <div class="page-body">
        <?php if (empty($orders)): ?>
        <div class="text-center py-5">
            <div style="font-size:4rem; color:var(--text-muted); opacity:0.3;"><i class="ri-inbox-archive-line"></i></div>
            <h4 class="mt-3">Belum ada tiket</h4>
            <p class="text-muted">Kamu belum melakukan pemesanan apapun.</p>
            <a href="<?= BASE_URL ?>/user/events.php" class="btn btn-primary">Cari Event</a>
        </div>
        <?php endif; ?>

        <?php foreach ($orders as $o): ?>
        <div class="ticket-item">
            <div class="d-flex justify-content-between align-items-start mb-4 pb-3" style="border-bottom: 1px solid var(--border);">
                <div>
                    <div class="text-primary small fw-bold mb-1">#ORD-<?= $o['id_order'] ?></div>
                    <div class="text-muted-custom small"><?= date('d M Y, H:i', strtotime($o['tanggal_order'])) ?> &nbsp;&bull;&nbsp; Rp <?= number_format($o['total'], 0, ',', '.') ?></div>
                </div>
                <div class="text-end">
                    <?php if ($o['status'] === 'paid'): ?>
                        <span class="badge bg-success bg-opacity-10 text-success small px-3 py-2">Paid</span>
                    <?php elseif ($o['status'] === 'pending'): ?>
                        <span class="badge bg-warning bg-opacity-10 text-warning small px-3 py-2 mb-2 d-block">Pending Checkout</span>
                        <a href="<?= BASE_URL ?>/user/payment.php?id=<?= $o['id_order'] ?>" class="btn btn-primary btn-sm">Complete Payment <i class="ri-arrow-right-line"></i></a>
                    <?php else: ?>
                        <span class="badge bg-danger bg-opacity-10 text-danger small px-3 py-2">Cancelled</span>
                    <?php endif; ?>
                </div>
            </div>

            <?php
            $id_order = $o['id_order'];
            $details  = require_once __DIR__ . '/../../../app/config/database.php';

            // Ambil detail langsung via koneksi
            $details = [];
            $res = mysqli_query(getDbConnection(),
                "SELECT od.*, t.nama_tiket, e.nama_event, v.nama_venue, v.alamat, e.tanggal, e.gambar
                 FROM order_detail od
                 JOIN tiket t ON od.id_tiket = t.id_tiket
                 JOIN event e ON t.id_event = e.id_event
                 JOIN venue v ON e.id_venue = v.id_venue
                 WHERE od.id_order = $id_order"
            );
            while ($row = mysqli_fetch_assoc($res)) $details[] = $row;
            ?>

            <?php if ($o['status'] === 'paid'): ?>
                <?php foreach ($details as $d):
                    $id_detail = $d['id_detail'];
                    $attendees = [];
                    $res2 = mysqli_query(getDbConnection(), "SELECT * FROM attendee WHERE id_detail = $id_detail");
                    while ($row = mysqli_fetch_assoc($res2)) $attendees[] = $row;
                ?>
                <div class="mb-4 pb-3" style="border-bottom: 1px solid var(--border);">
                    <h5 class="fw-bold mb-1" style="color: var(--text-primary);"><?= htmlspecialchars($d['nama_event']) ?></h5>
                    <p class="small mb-3 text-muted-custom"><i class="ri-map-pin-line"></i> <?= htmlspecialchars($d['nama_venue']) ?></p>
                    <div class="row g-3">
                        <?php foreach ($attendees as $tk):
                            $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($tk['kode_tiket']);
                            $is_expired = date('Y-m-d') > $d['tanggal'];
                            $ticket_data = json_encode([
                                'event'   => $d['nama_event'],
                                'venue'   => $d['nama_venue'],
                                'alamat'  => $d['alamat'],
                                'tanggal' => date('d M Y', strtotime($d['tanggal'])),
                                'tiket'   => $d['nama_tiket'],
                                'kode'    => $tk['kode_tiket'],
                                'status'  => $tk['status_checkin'],
                                'expired' => $is_expired,
                                'qr'      => $qr_url,
                                'gambar'  => !empty($d['gambar']) ? BASE_URL . "/assets/img/events/" . $d['gambar'] : ''
                            ]);
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div onclick="showDetail(<?= htmlspecialchars($ticket_data) ?>)"
                                 style="background:var(--bg-elevated);border:1px solid var(--border);border-radius:16px;padding:1rem;display:flex;align-items:center;gap:.85rem;cursor:pointer;transition:border-color .25s,box-shadow .25s,transform .25s"
                                 onmouseover="this.style.borderColor='rgba(124,58,237,0.5)';this.style.boxShadow='0 4px 20px rgba(124,58,237,0.15)';this.style.transform='translateY(-2px)'"
                                 onmouseout="this.style.borderColor='var(--border)';this.style.boxShadow='none';this.style.transform='translateY(0)'">
                                <div style="background:white;padding:5px;border-radius:10px;flex-shrink:0;border:2px solid rgba(255,255,255,.1)">
                                    <img src="<?= $qr_url ?>" alt="QR" width="60" height="60" style="display:block;">
                                </div>
                                <div style="flex:1;min-width:0">
                                    <div style="font-size:.72rem;margin-bottom:.3rem;font-weight:500;" class="text-muted-custom"><?= htmlspecialchars($d['nama_tiket']) ?></div>
                                    <div style="font-family:'Courier New',monospace;font-size:.78rem;font-weight:700;color:var(--primary-light);background:rgba(124,58,237,.1);border:1px solid rgba(124,58,237,.2);border-radius:7px;padding:.25rem .5rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:.45rem;">
                                        <?= htmlspecialchars($tk['kode_tiket']) ?>
                                    </div>
                                    <?php 
                                        $is_expired = date('Y-m-d') > $d['tanggal'];
                                    ?>
                                    <span class="ticket-badge <?= $tk['status_checkin'] === 'sudah' ? 'badge-used' : ($is_expired ? 'bg-danger text-white' : 'badge-ready') ?>">
                                        <?php 
                                            if ($tk['status_checkin'] === 'sudah') echo '✓ Checked-in';
                                            elseif ($is_expired) echo '✕ Expired';
                                            else echo '🔍 Klik Detail';
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="p-3 text-center opacity-75">
                    <?php if ($o['status'] === 'pending'): ?>
                        <p class="text-warning mb-0"><i class="ri-time-line-history"></i> Silakan selesaikan pembayaran untuk melihat QR Code tiket Anda.</p>
                    <?php else: ?>
                        <p class="text-danger mb-0"><i class="ri-close-circle-line"></i> Pesanan ini telah dibatalkan.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal Detail Tiket -->
<div class="modal fade" id="ticketModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-ticket">
            <div class="ticket-top" id="m_banner" style="background-size: cover; background-position: center;">
                <div style="position:absolute; inset:0; background:rgba(0,0,0,0.5); z-index:0;"></div>
                <div style="position:relative; z-index:1;">
                    <button type="button" class="btn-close btn-close-white float-end" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="badge bg-white text-dark small fw-bold mb-2">E-TICKET EVENT</div>
                    <h3 class="fw-bold mb-1" id="m_event"></h3>
                    <div class="small opacity-75" id="m_tanggal"></div>
                </div>
            </div>
            <div class="ticket-bottom">
                <div class="qr-large"><img id="m_qr" src="" alt="QR" width="180"></div>
                <div class="mb-4">
                    <div class="small text-secondary text-uppercase mb-1" style="opacity: 0.8; font-weight: 700; letter-spacing: 1px;">Ticket Holder Code</div>
                    <div class="h4 fw-bold text-primary mb-3" id="m_kode"></div>
                    <div class="row text-start g-3">
                        <div class="col-6"><label class="small text-secondary fw-bold d-block mb-1" style="opacity: 0.8;">Tiket Type</label><span class="fw-bold" id="m_tipe"></span></div>
                        <div class="col-6"><label class="small text-secondary fw-bold d-block mb-1" style="opacity: 0.8;">Status</label><span class="fw-bold" id="m_status"></span></div>
                        <div class="col-12"><label class="small text-secondary fw-bold d-block mb-1" style="opacity: 0.8;">Location</label><span class="small" id="m_venue"></span><br><span class="text-secondary" style="font-size:.7rem;" id="m_alamat"></span></div>
                    </div>
                </div>
                <a id="btn-download" href="#" target="_blank" class="btn btn-outline-primary w-100 rounded-pill mt-2"><i class="ri-printer-line me-2"></i>Download / Print Ticket</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showDetail(data) {
    document.getElementById('m_event').innerText  = data.event;
    document.getElementById('m_tanggal').innerText = '📅 ' + data.tanggal;
    document.getElementById('m_qr').src            = data.qr;
    document.getElementById('m_kode').innerText   = data.kode;
    document.getElementById('m_tipe').innerText   = data.tiket;
    document.getElementById('m_venue').innerText  = data.venue;
    document.getElementById('m_alamat').innerText = data.alamat;
    document.getElementById('btn-download').href  = '<?= BASE_URL ?>/user/download_ticket.php?kode=' + data.kode;

    const banner = document.getElementById('m_banner');
    if (data.gambar) {
        banner.style.backgroundImage = `url('${data.gambar}')`;
    } else {
        banner.style.background = 'var(--gradient-primary)';
    }

    const statusEl = document.getElementById('m_status');
    if (data.status === 'sudah') {
        statusEl.innerText = '✓ CHECKED-IN';
        statusEl.className = 'fw-bold text-muted';
    } else if (data.expired) {
        statusEl.innerText = '✕ EXPIRED';
        statusEl.className = 'fw-bold text-danger';
    } else {
        statusEl.innerText = 'READY';
        statusEl.className = 'fw-bold text-success';
    }

    new bootstrap.Modal(document.getElementById('ticketModal')).show();
}
</script>
</body>
</html>
