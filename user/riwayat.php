<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'user') { header("Location: ../login.php"); exit; }

$id_user = $_SESSION['id_user'];
$orders  = query("SELECT o.*, v.kode_voucher, v.potongan FROM orders o LEFT JOIN voucher v ON o.id_voucher=v.id_voucher WHERE o.id_user=$id_user ORDER BY o.tanggal_order DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Tickets — EventTiket</title>
    <?php $is_sub = true; include '../includes/head.php'; ?>
    <style>
        .ticket-item { background: var(--bg-surface); border: 1px solid var(--border); border-radius: 24px; padding: 1.5rem; margin-bottom: 1.5rem; transition: all 0.3s; }
        .ticket-item:hover { transform: translateY(-5px); border-color: var(--primary); box-shadow: var(--shadow-md); }
        .qr-card-mini { background: white; padding: 5px; border-radius: 8px; cursor: pointer; border: 2px solid transparent; transition: all 0.2s; }
        .qr-card-mini:hover { border-color: var(--primary); }
        .ticket-badge { font-size: 0.7rem; padding: 0.4rem 0.8rem; border-radius: 50px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-ready { background: #10b981; color: white; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3); }
        .badge-used { background: var(--bg-elevated); color: var(--text-muted); border: 1px solid var(--border); }
        .text-muted { color: #8e8ea8 !important; } /* Mencerahkan teks muted */
        
        /* Modal Ticket Style */
        .modal-ticket { background: #11111d; color: white; border-radius: 25px; overflow: hidden; border: 1px solid rgba(255,255,255,0.1); }
        .ticket-top { background: var(--gradient-primary); padding: 2rem; position: relative; }
        .ticket-top::after { content: ''; position: absolute; bottom: -15px; left: 0; right: 0; height: 30px; background: #11111d; clip-path: polygon(0% 50%, 5% 100%, 10% 50%, 15% 100%, 20% 50%, 25% 100%, 30% 50%, 35% 100%, 40% 50%, 45% 100%, 50% 50%, 55% 100%, 60% 50%, 65% 100%, 70% 50%, 75% 100%, 80% 50%, 85% 100%, 90% 50%, 95% 100%, 100% 50%); }
        .ticket-bottom { padding: 2.5rem 2rem 2rem; text-align: center; }
        .qr-large { background: white; padding: 15px; border-radius: 20px; display: inline-block; margin-bottom: 1.5rem; box-shadow: 0 10px 40px rgba(0,0,0,0.5); border: 5px solid #f8fafc; }
    </style>
</head>
<body>

<?php include '../includes/sidebar.php'; ?>

<div class="main-content">
    <div class="page-header">
        <div class="page-title">My Tickets 🎟️</div>
        <div class="page-subtitle">Klik tiket untuk melihat detail lengkap dan QR Code</div>
    </div>

    <div class="page-body">
        <?php if(empty($orders)): ?>
            <div class="text-center py-5">
                <div style="font-size: 4rem;">📦</div>
                <h4 class="mt-3">Belum ada tiket</h4>
                <p class="text-muted">Kamu belum melakukan pemesanan apapun.</p>
                <a href="events.php" class="btn btn-primary">Cari Event</a>
            </div>
        <?php endif; ?>

        <?php foreach($orders as $o): ?>
        <div class="ticket-item">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <div class="text-primary small fw-bold mb-1">#ORD-<?= $o['id_order'] ?></div>
                    <div class="text-muted small"><?= date('d M Y, H:i', strtotime($o['tanggal_order'])) ?></div>
                </div>
                <div class="text-end">
                    <span class="badge bg-primary bg-opacity-10 text-primary small">Order: <?= strtoupper($o['status']) ?></span>
                </div>
            </div>

            <?php 
            $id_order = $o['id_order'];
            $details = query("SELECT od.*, t.nama_tiket, e.nama_event, v.nama_venue, v.alamat, e.tanggal 
                             FROM order_detail od 
                             JOIN tiket t ON od.id_tiket = t.id_tiket 
                             JOIN event e ON t.id_event = e.id_event 
                             JOIN venue v ON e.id_venue = v.id_venue
                             WHERE od.id_order = $id_order");
            foreach($details as $d): 
                $id_detail = $d['id_detail'];
                $attendees = query("SELECT * FROM attendee WHERE id_detail = $id_detail");
            ?>
            <div class="mb-4 pb-3 border-bottom border-white border-opacity-5">
                <h5 class="fw-bold mb-1"><?= $d['nama_event'] ?></h5>
                <p class="text-muted small mb-3"><i class="bi bi-geo-alt"></i> <?= $d['nama_venue'] ?></p>
                
                    <div class="row g-3">
                        <?php foreach($attendees as $tk): 
                            $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . $tk['kode_tiket'];
                            $ticket_data = [
                                'event'   => $d['nama_event'],
                                'venue'   => $d['nama_venue'],
                                'alamat'  => $d['alamat'],
                                'tanggal' => date('d M Y', strtotime($d['tanggal'])),
                                'tiket'   => $d['nama_tiket'],
                                'kode'    => $tk['kode_tiket'],
                                'status'  => $tk['status_checkin'],
                                'qr'      => $qr_url
                            ];
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div onclick="showDetail(<?= htmlspecialchars(json_encode($ticket_data)) ?>)"
                                 style="background: var(--bg-elevated);
                                        border: 1px solid var(--border);
                                        border-radius: 16px;
                                        padding: 1rem;
                                        display: flex;
                                        align-items: center;
                                        gap: 0.85rem;
                                        cursor: pointer;
                                        transition: border-color 0.25s, box-shadow 0.25s, transform 0.25s;" 
                                 onmouseover="this.style.borderColor='rgba(124,58,237,0.5)'; this.style.boxShadow='0 4px 20px rgba(124,58,237,0.15)'; this.style.transform='translateY(-2px)';"
                                 onmouseout="this.style.borderColor='var(--border)'; this.style.boxShadow='none'; this.style.transform='translateY(0)';">
                                <!-- QR Thumbnail -->
                                <div style="background: white; padding: 5px; border-radius: 10px; flex-shrink: 0; border: 2px solid rgba(255,255,255,0.1);">
                                    <img src="<?= $qr_url ?>" alt="QR" width="60" height="60" style="display:block;">
                                </div>
                                <!-- Info -->
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-size: 0.72rem; color: var(--text-muted); margin-bottom: 0.3rem; font-weight: 500;"><?= htmlspecialchars($d['nama_tiket']) ?></div>
                                    <div style="font-family: 'Courier New', monospace;
                                                font-size: 0.78rem;
                                                font-weight: 700;
                                                color: var(--primary-light);
                                                background: rgba(124,58,237,0.1);
                                                border: 1px solid rgba(124,58,237,0.2);
                                                border-radius: 7px;
                                                padding: 0.25rem 0.5rem;
                                                white-space: nowrap;
                                                overflow: hidden;
                                                text-overflow: ellipsis;
                                                margin-bottom: 0.45rem;">
                                        <?= htmlspecialchars($tk['kode_tiket']) ?>
                                    </div>
                                    <span class="ticket-badge <?= $tk['status_checkin'] == 'sudah' ? 'badge-used' : 'badge-ready' ?>">
                                        <?= $tk['status_checkin'] == 'sudah' ? '✓ Checked-in' : '🔍 Klik Detail' ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal Detail Tiket -->
<div class="modal fade" id="ticketModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-ticket">
            <div class="ticket-top">
                <button type="button" class="btn-close btn-close-white float-end" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="badge bg-white text-dark small fw-bold mb-2">E-TICKET EVENT</div>
                <h3 class="fw-bold mb-1" id="m_event"></h3>
                <div class="small opacity-75" id="m_tanggal"></div>
            </div>
            <div class="ticket-bottom">
                <div class="qr-large">
                    <img id="m_qr" src="" alt="QR" width="180">
                </div>
                <div class="mb-4">
                    <div class="small text-muted text-uppercase tracking-wider mb-1">Ticket Holder Code</div>
                    <div class="h4 fw-bold text-primary mb-3" id="m_kode"></div>
                    <div class="row text-start g-3">
                        <div class="col-6">
                            <label class="small text-muted d-block">Tiket Type</label>
                            <span class="fw-bold" id="m_tipe"></span>
                        </div>
                        <div class="col-6">
                            <label class="small text-muted d-block">Status</label>
                            <span class="fw-bold" id="m_status text-success">READY</span>
                        </div>
                        <div class="col-12">
                            <label class="small text-muted d-block">Location</label>
                            <span class="small" id="m_venue"></span><br>
                            <span class="text-muted" style="font-size: 0.7rem;" id="m_alamat"></span>
                        </div>
                    </div>
                </div>
                <button onclick="window.print()" class="btn btn-outline-primary w-100 rounded-pill mt-2">
                    <i class="bi bi-printer me-2"></i> Download / Print Ticket
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showDetail(data) {
    document.getElementById('m_event').innerText = data.event;
    document.getElementById('m_tanggal').innerText = "📅 " + data.tanggal;
    document.getElementById('m_qr').src = data.qr;
    document.getElementById('m_kode').innerText = data.kode;
    document.getElementById('m_tipe').innerText = data.tiket;
    document.getElementById('m_venue').innerText = data.venue;
    document.getElementById('m_alamat').innerText = data.alamat;
    
    const myModal = new bootstrap.Modal(document.getElementById('ticketModal'));
    myModal.show();
}
</script>

<?php include '../includes/footer.php'; ?>

</body>
</html>
