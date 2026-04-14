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
        .ticket-item { background: var(--bg-surface); border: 1px solid var(--border); border-radius: 24px; padding: 1.5rem; margin-bottom: 1.5rem; transition: transform 0.3s; }
        .ticket-item:hover { transform: translateY(-5px); border-color: var(--primary); }
        .qr-card { background: white; padding: 10px; border-radius: 12px; display: inline-block; }
        .ticket-badge { font-size: 0.65rem; padding: 0.3rem 0.6rem; border-radius: 50px; font-weight: 700; text-transform: uppercase; }
        .badge-ready { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
        .badge-used { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
    </style>
</head>
<body>

<?php include '../includes/sidebar.php'; ?>

<div class="main-content">
    <div class="page-header">
        <div class="page-title">My Tickets 🎟️</div>
        <div class="page-subtitle">Kelola dan unduh QR Code tiket kamu untuk check-in</div>
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
                    <span class="badge bg-primary bg-opacity-10 text-primary small">Status: <?= strtoupper($o['status']) ?></span>
                </div>
            </div>

            <?php 
            $id_order = $o['id_order'];
            $details = query("SELECT od.*, t.nama_tiket, e.nama_event FROM order_detail od JOIN tiket t ON od.id_tiket = t.id_tiket JOIN event e ON t.id_event = e.id_event WHERE od.id_order = $id_order");
            foreach($details as $d): 
                $id_detail = $d['id_detail'];
                $attendees = query("SELECT * FROM attendee WHERE id_detail = $id_detail");
            ?>
            <div class="mb-4 pb-3 border-bottom border-white border-opacity-5">
                <h5 class="fw-bold mb-3"><?= $d['nama_event'] ?> <span class="text-muted small fw-normal">(<?= $d['nama_tiket'] ?>)</span></h5>
                
                <div class="row g-3">
                    <?php foreach($attendees as $tk): 
                        $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . $tk['kode_tiket'];
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="bg-elevated p-3 rounded-4 d-flex align-items-center gap-3">
                            <div class="qr-card">
                                <img src="<?= $qr_url ?>" alt="QR" width="80" height="80">
                            </div>
                            <div class="flex-fill">
                                <div class="small text-muted mb-1">Ticket Code</div>
                                <code class="d-block mb-2 fw-bold" style="color: var(--text-primary);"><?= $tk['kode_tiket'] ?></code>
                                <div class="d-flex align-items-center justify-content-between">
                                    <?php if($tk['status_checkin'] == 'sudah'): ?>
                                        <span class="ticket-badge badge-used">Sudah Check-in</span>
                                    <?php else: ?>
                                        <span class="ticket-badge badge-ready">Ready to Use</span>
                                    <?php endif; ?>
                                    
                                    <button onclick="downloadQR('<?= $qr_url ?>', '<?= $tk['kode_tiket'] ?>')" class="btn btn-ghost btn-sm p-1" title="Download QR">
                                        <i class="bi bi-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">Total Pembayaran: <strong class="text-white">Rp <?= number_format($o['total'], 0, ',', '.') ?></strong></div>
                <div style="font-size: 0.7rem; color: var(--text-muted);">Tunjukkan QR Code di atas kepada petugas saat masuk venue.</div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
async function downloadQR(url, filename) {
    try {
        const response = await fetch(url);
        const blob = await response.blob();
        const blobUrl = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = blobUrl;
        link.download = `Ticket_${filename}.png`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(blobUrl);
    } catch (err) {
        alert("Gagal mendownload QR Code. Coba Klik kanan pada gambar lalu simpan.");
    }
}
</script>

</body>
</html>
