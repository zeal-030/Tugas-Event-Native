<?php
/**
 * View: Payment Gateway
 * Data dari PaymentController: $order, $items
 */
$user = currentUser();
$event_name = !empty($items) ? $items[0]['nama_event'] : 'Event';
$event_date = !empty($items) ? date('d M Y', strtotime($items[0]['tanggal'])) : '';
$venue_name = !empty($items) ? $items[0]['nama_venue'] : '';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran — <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <script>document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'dark');</script>
    <style>
        body { background: var(--bg-base); font-family: 'Plus Jakarta Sans', sans-serif; min-height: 100vh; }

        /* TOP BAR */
        .pay-topbar {
            background: var(--bg-surface);
            border-bottom: 1px solid var(--border);
            padding: 0.8rem 2rem;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 100;
        }
        .pay-brand { display: flex; align-items: center; gap: 0.6rem; font-weight: 800; }
        .pay-brand .logo { width: 30px; height: 30px; background: var(--gradient-primary); border-radius: 8px; display: flex; align-items: center; justify-content: center; }
        .pay-brand .brand-text { background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .secure-pill { display: flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.8rem; border-radius: 20px; background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2); font-size: 0.72rem; color: var(--success); font-weight: 600; }
        .timer-pill { display: flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.8rem; border-radius: 20px; background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.2); font-size: 0.8rem; color: var(--warning); font-weight: 700; }

        /* STEPS */
        .steps-bar { display: flex; align-items: center; justify-content: center; gap: 0; margin: 1.5rem 0; }
        .step { display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; font-weight: 600; color: var(--text-muted); }
        .step.active { color: var(--primary-light); }
        .step.done { color: var(--success); }
        .step-num { width: 28px; height: 28px; border-radius: 50%; background: var(--bg-elevated); border: 2px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; }
        .step.active .step-num { background: var(--gradient-primary); border-color: transparent; color: white; }
        .step.done .step-num { background: var(--success); border-color: transparent; color: white; }
        .step-line { width: 40px; height: 2px; background: var(--border); margin: 0 0.5rem; }

        /* MAIN LAYOUT */
        .payment-layout { display: grid; grid-template-columns: 1fr 420px; gap: 1.5rem; max-width: 1050px; margin: 0 auto; padding: 1rem 1.5rem 3rem; }
        @media (max-width: 900px) { .payment-layout { grid-template-columns: 1fr; } }

        /* ORDER SUMMARY (right) */
        .order-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 20px; overflow: hidden; }
        .order-card-header { background: var(--gradient-primary); padding: 1.25rem 1.5rem; }
        .order-card-body { padding: 1.25rem 1.5rem; }
        .order-line { display: flex; justify-content: space-between; padding: 0.5rem 0; font-size: 0.85rem; border-bottom: 1px solid var(--border); color: var(--text-secondary); }
        .order-line:last-child { border-bottom: none; }
        .order-total { display: flex; justify-content: space-between; align-items: center; padding: 0.85rem 1.5rem; background: rgba(124,58,237,0.08); border-top: 1.5px solid rgba(124,58,237,0.25); }

        /* PAYMENT METHODS (left) */
        .pay-section { background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px; padding: 1.25rem 1.5rem; margin-bottom: 1rem; }
        .pay-section-title { font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-bottom: 1rem; }
        .method-item { display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 1.5px solid var(--border); border-radius: 12px; cursor: pointer; transition: all 0.2s; margin-bottom: 0.5rem; }
        .method-item:last-child { margin-bottom: 0; }
        .method-item:hover { border-color: rgba(124,58,237,0.4); background: rgba(124,58,237,0.04); }
        .method-item.selected { border-color: var(--primary); background: rgba(124,58,237,0.08); }
        .method-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden; background: white; border: 1px solid var(--border); }
        .method-radio { width: 18px; height: 18px; border-radius: 50%; border: 2px solid var(--border); margin-left: auto; flex-shrink: 0; transition: all 0.15s; }
        .method-item.selected .method-radio { border: 5px solid var(--primary); background: white; }
        .method-detail { display: none; margin-top: 0.75rem; padding: 1rem; background: var(--bg-elevated); border-radius: 10px; font-size: 0.82rem; }
        .method-detail.show { display: block; }
        .va-number { font-family: 'Courier New', monospace; font-size: 1.4rem; font-weight: 800; color: var(--primary-light); letter-spacing: 3px; display: flex; align-items: center; gap: 0.5rem; }
        .copy-btn { background: rgba(124,58,237,0.15); border: 1px solid rgba(124,58,237,0.3); color: var(--primary-light); font-size: 0.72rem; padding: 0.2rem 0.6rem; border-radius: 6px; cursor: pointer; transition: all 0.15s; }
        .copy-btn:hover { background: rgba(124,58,237,0.3); }
        .bank-logo { width: 85%; height: 85%; object-fit: contain; }

        /* PAY BUTTON */
        .pay-now-btn { background: var(--gradient-primary); color: white; border: none; border-radius: 14px; padding: 1rem; width: 100%; font-weight: 800; font-size: 1rem; cursor: pointer; transition: all 0.25s; box-shadow: 0 4px 20px rgba(124,58,237,0.4); display: flex; align-items: center; justify-content: center; gap: 0.5rem; }
        .pay-now-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(124,58,237,0.5); }
        .cancel-btn { background: transparent; border: 1.5px solid var(--border); color: var(--text-secondary); border-radius: 14px; padding: 0.75rem; width: 100%; font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: all 0.2s; margin-top: 0.75rem; }
        .cancel-btn:hover { border-color: var(--danger); color: var(--danger); background: rgba(239,68,68,0.05); }

        /* SUCCESS & CANCEL PAGE */
        .result-page { min-height: calc(100vh - 65px); display: flex; align-items: center; justify-content: center; }
        .result-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 24px; padding: 3rem 2.5rem; max-width: 460px; width: 100%; text-align: center; }
        .result-icon { width: 90px; height: 90px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.8rem; margin: 0 auto 1.5rem; }
        .result-icon.success { background: rgba(16,185,129,0.15); border: 2px solid rgba(16,185,129,0.3); animation: scaleIn 0.4s ease; }
        .result-icon.danger { background: rgba(239,68,68,0.15); border: 2px solid rgba(239,68,68,0.3); }
        @keyframes scaleIn { from { transform: scale(0.5); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    </style>
</head>
<body>

<!-- TOP BAR -->
<div class="pay-topbar">
    <div class="pay-brand">
        <a href="<?= BASE_URL ?>/user/events.php" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 0.6rem;">
            <div class="logo"><i class="ri-ticket-2-fill" style="color:white;"></i></div>
            <span class="brand-text"><?= APP_NAME ?></span>
        </a>
    </div>
    <div class="d-flex align-items-center gap-2">
        <?php if ($order['status'] === 'pending'): ?>
        <div class="timer-pill">
            <i class="ri-time-line"></i> <span id="timer">14:59</span>
        </div>
        <?php endif; ?>
        <div class="secure-pill">
            <i class="ri-shield-keyhole-fill"></i> SSL Secured
        </div>
    </div>
    <div class="text-muted small"><?= htmlspecialchars($user['nama']) ?></div>
</div>

<?php if ($order['status'] === 'paid'): ?>
<!-- SUCCESS STATE -->
<div class="result-page">
    <div class="result-card">
        <div class="result-icon success"><i class="ri-checkbox-circle-fill" style="color:var(--success);"></i></div>
        <h2 class="fw-bold text-primary mb-2">Pembayaran Berhasil!</h2>
        <p class="text-muted mb-1">No. Pesanan: <strong class="text-primary">#ORD-<?= $order['id_order'] ?></strong></p>
        <p class="text-muted mb-4">E-ticket Anda siap! Silakan cek di menu "My Tickets".</p>
        <div class="py-3 px-4 mb-4 rounded-3" style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2);">
            <div class="text-success fw-bold">Rp <?= number_format($order['total'], 0, ',', '.') ?></div>
            <div class="text-muted small mt-1">telah berhasil kami terima</div>
        </div>
        <a href="<?= BASE_URL ?>/user/riwayat.php" class="pay-now-btn text-decoration-none d-flex mb-2">
            <i class="ri-ticket-2-line-fill me-2"></i> Lihat E-Ticket Saya
        </a>
        <a href="<?= BASE_URL ?>/user/events.php" class="cancel-btn d-block text-decoration-none">
            Jelajahi Event Lainnya
        </a>
    </div>
</div>

<?php elseif ($order['status'] === 'cancel'): ?>
<!-- CANCELLED STATE -->
<div class="result-page">
    <div class="result-card">
        <div class="result-icon danger"><i class="ri-close-circle-fill" style="color:var(--danger);"></i></div>
        <h2 class="fw-bold text-danger mb-2">Pesanan Dibatalkan</h2>
        <p class="text-muted mb-4">Pesanan <strong class="text-primary">#ORD-<?= $order['id_order'] ?></strong> telah dibatalkan. Kuota tiket sudah dikembalikan.</p>
        <a href="<?= BASE_URL ?>/user/events.php" class="pay-now-btn text-decoration-none d-flex mb-2">
            <i class="ri-search-line me-2"></i> Cari Event Lainnya
        </a>
        <a href="<?= BASE_URL ?>/user/riwayat.php" class="cancel-btn d-block text-decoration-none">
            Lihat Riwayat Pesanan
        </a>
    </div>
</div>

<?php else: ?>
<!-- PENDING PAYMENT STATE -->
<div class="steps-bar">
    <div class="step done"><div class="step-num"><i class="ri-check-line" style="font-size:0.7rem;"></i></div><span>Pilih Tiket</span></div>
    <div class="step-line" style="background: var(--success);"></div>
    <div class="step active"><div class="step-num">2</div><span>Pembayaran</span></div>
    <div class="step-line"></div>
    <div class="step"><div class="step-num">3</div><span>E-Ticket</span></div>
</div>

<div class="payment-layout">
    <!-- LEFT: PAYMENT METHODS -->
    <div>
        <!-- Bank Transfer -->
        <div class="pay-section">
            <div class="pay-section-title"><i class="ri-bank-line me-1"></i> Transfer Bank / Virtual Account</div>

            <!-- BCA -->
            <div class="method-item selected" onclick="selectMethod(this, 'detail-bca')">
                <div class="method-icon" style="background: white;">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg" class="bank-logo" alt="BCA">
                </div>
                <div>
                    <div class="fw-bold" style="font-size: 0.9rem; color: var(--text-primary);">BCA Virtual Account</div>
                    <div class="text-muted" style="font-size: 0.75rem;">Transfer ke nomor VA BCA</div>
                </div>
                <div class="method-radio" style="border: 5px solid var(--primary); background: white;"></div>
            </div>
            <div class="method-detail show" id="detail-bca">
                <div class="text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Nomor Virtual Account BCA</div>
                <div class="va-number">
                    8277 5<?= str_pad($order['id_order'], 6, '0', STR_PAD_LEFT) ?>
                    <button class="copy-btn" onclick="copyVA(this, '82775<?= str_pad($order['id_order'], 6, '0', STR_PAD_LEFT) ?>')">Copy</button>
                </div>
                <div class="text-muted mt-2" style="font-size: 0.75rem;"><i class="ri-information-line me-1"></i> Selesaikan pembayaran sebelum timer habis</div>
            </div>

            <!-- Mandiri -->
            <div class="method-item" onclick="selectMethod(this, 'detail-mandiri')">
                <div class="method-icon" style="background: white;">
                    <img src="https://upload.wikimedia.org/wikipedia/id/f/fa/Bank_Mandiri_logo.svg" class="bank-logo" alt="Mandiri">
                </div>
                <div>
                    <div class="fw-bold" style="font-size: 0.9rem; color: var(--text-primary);">Mandiri Virtual Account</div>
                    <div class="text-muted" style="font-size: 0.75rem;">Transfer ke nomor VA Mandiri</div>
                </div>
                <div class="method-radio"></div>
            </div>
            <div class="method-detail" id="detail-mandiri">
                <div class="text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase;">Kode Bayar Mandiri</div>
                <div class="va-number">
                    70013 <?= str_pad($order['id_order'], 7, '0', STR_PAD_LEFT) ?>
                    <button class="copy-btn" onclick="copyVA(this, '7001300<?= str_pad($order['id_order'], 7, '0', STR_PAD_LEFT) ?>')">Copy</button>
                </div>
            </div>
        </div>

        <!-- E-Wallet -->
        <div class="pay-section">
            <div class="pay-section-title"><i class="ri-smartphone-line me-1"></i> Dompet Digital / E-Wallet</div>

            <div class="method-item" onclick="selectMethod(this, 'detail-gopay')">
                <div class="method-icon" style="background: white;">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/8/86/Gopay_logo.svg" class="bank-logo" alt="GoPay">
                </div>
                <div>
                    <div class="fw-bold" style="font-size: 0.9rem; color: var(--text-primary);">GoPay</div>
                    <div class="text-muted" style="font-size: 0.75rem;">Bayar via GoPay / Gojek App</div>
                </div>
                <div class="method-radio"></div>
            </div>
            <div class="method-detail" id="detail-gopay">
                <div class="text-muted small"><i class="ri-smartphone-line me-1"></i> Scan QR atau buka Gojek app untuk menyelesaikan pembayaran</div>
                <div class="mt-2 text-center">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=130x130&data=GOPAY-<?= $order['id_order'] ?>-<?= $order['total'] ?>" alt="QR" style="border-radius: 10px; background: white; padding: 8px;">
                </div>
            </div>

            <div class="method-item" onclick="selectMethod(this, 'detail-ovo')">
                <div class="method-icon" style="background: white;">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/e/eb/Logo_ovo_purple.svg" class="bank-logo" alt="OVO">
                </div>
                <div>
                    <div class="fw-bold" style="font-size: 0.9rem; color: var(--text-primary);">OVO</div>
                    <div class="text-muted" style="font-size: 0.75rem;">Bayar via OVO App</div>
                </div>
                <div class="method-radio"></div>
            </div>
            <div class="method-detail" id="detail-ovo">
                <div class="text-muted small"><i class="ri-smartphone-line me-1"></i> Buka OVO App dan scan QR code berikut untuk melakukan pembayaran.</div>
                <div class="mt-2 text-center">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=130x130&data=OVO-<?= $order['id_order'] ?>-<?= $order['total'] ?>" alt="QR" style="border-radius: 10px; background: white; padding: 8px;">
                </div>
            </div>
        </div>

        <!-- Instruction Box -->
        <div class="pay-section" style="background: rgba(6,182,212,0.04); border-color: rgba(6,182,212,0.15);">
            <div class="pay-section-title"><i class="ri-menu-line-ol me-1" style="color: var(--accent);"></i> Cara Pembayaran</div>
            <ol class="small text-secondary mb-0 ps-3" style="line-height: 2.2;">
                <li>Pilih metode pembayaran yang Anda inginkan di atas</li>
                <li>Salin nomor VA atau scan QR yang tersedia</li>
                <li>Selesaikan pembayaran sebelum batas waktu berakhir</li>
                <li>Tiket akan otomatis aktif dan muncul di "My Tickets"</li>
            </ol>
        </div>
    </div>

    <!-- RIGHT: ORDER SUMMARY & ACTION -->
    <div>
        <!-- Order Summary Card -->
        <div class="order-card" style="margin-bottom: 1rem;">
            <div class="order-card-header">
                <div class="fw-bold text-white mb-1"><i class="ri-ticket-2-fill me-1"></i> Detail Pesanan</div>
                <div class="text-white text-opacity-75" style="font-size: 0.75rem;">#ORD-<?= $order['id_order'] ?></div>
            </div>
            <div class="order-card-body">
                <div class="mb-3 pb-2" style="border-bottom: 1px dashed var(--border);">
                    <div class="fw-bold" style="font-size: 0.9rem; color: var(--text-primary);"><?= htmlspecialchars($event_name) ?></div>
                    <?php if ($event_date): ?>
                    <div style="font-size: 0.75rem; color: var(--text-secondary);"><i class="ri-calendar-3-line me-1"></i><?= $event_date ?> &bull; <?= htmlspecialchars($venue_name) ?></div>
                    <?php endif; ?>
                </div>
                <?php foreach ($items as $item): ?>
                <div class="order-line">
                    <span><?= htmlspecialchars($item['nama_tiket']) ?> <span class="badge" style="background:rgba(124,58,237,0.15);color:var(--primary-light);font-size:0.65rem;">×<?= $item['qty'] ?></span></span>
                    <span class="fw-bold" style="color: var(--text-primary);">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
                </div>
                <?php endforeach; ?>
                <?php 
                $subtotal = array_sum(array_column($items, 'subtotal'));
                $discount = $subtotal - $order['total'];
                if ($discount > 0): ?>
                <div class="order-line">
                    <span class="text-success small"><i class="ri-price-tag-3-line-fill"></i> Diskon Promo</span>
                    <span class="text-success fw-bold small">- Rp <?= number_format($discount, 0, ',', '.') ?></span>
                </div>
                <?php endif; ?>
                <div class="order-line">
                    <span class="text-muted">Biaya Layanan</span>
                    <span class="text-success fw-bold small">GRATIS</span>
                </div>
            </div>
            <div class="order-total">
                <span class="fw-bold" style="color: var(--text-primary);">Total Pembayaran</span>
                <span class="fw-bold text-primary" style="font-size: 1.3rem;">Rp <?= number_format($order['total'], 0, ',', '.') ?></span>
            </div>
        </div>

        <!-- Pay Now Form -->
        <form action="" method="post">
            <button type="submit" name="pay" class="pay-now-btn">
                <i class="ri-lock-2-line-fill"></i> Konfirmasi & Bayar Sekarang
            </button>
            <div style="font-size: 0.72rem; color: var(--text-muted); text-align: center; margin: 0.6rem 0; display: flex; align-items: center; justify-content: center; gap: 0.4rem;">
                <i class="ri-shield-check-fill" style="color: var(--success);"></i> Pembayaran 100% aman &amp; terenkripsi
            </div>
            <button type="submit" name="cancel" class="cancel-btn" onclick="return confirm('Yakin ingin membatalkan pesanan ini?')">
                <i class="ri-close-circle-line me-1"></i> Batalkan Pesanan
            </button>
        </form>

        <!-- Accepted Payments -->
        <div class="text-center mt-3" style="opacity: 0.45; font-size: 0.7rem; color: var(--text-muted);">
            Metode pembayaran diterima:<br>
            <span style="letter-spacing: 3px;">BCA &bull; MANDIRI &bull; BRI &bull; BNI &bull; GOPAY &bull; OVO &bull; DANA</span>
        </div>
    </div>
</div>

<!-- Hidden form for auto-cancel -->
<form id="auto-cancel-form" method="POST" style="display:none;">
    <input type="hidden" name="cancel" value="1">
</form>

<script>
// Countdown timer
let seconds = <?= $order['status'] === 'pending' ? (15 * 60) : 0 ?>; // 15 Menit simulasi (atau sesuaikan kebutuhan)
const timerEl = document.getElementById('timer');
const countdown = setInterval(() => {
    if (seconds <= 0) { 
        clearInterval(countdown); 
        timerEl.innerText = '00:00'; 
        
        // Popup dan Auto Cancel
        setTimeout(() => {
            alert('Waktu pembayaran telah habis (Tidak Dibayar). Pesanan Anda akan dibatalkan secara otomatis.');
            document.getElementById('auto-cancel-form').submit();
        }, 100);
        return; 
    }
    seconds--;
    const m = String(Math.floor(seconds / 60)).padStart(2, '0');
    const s = String(seconds % 60).padStart(2, '0');
    timerEl.innerText = `${m}:${s}`;
    if (seconds < 60) timerEl.style.color = '#ef4444';
}, 1000);

function selectMethod(el, detailId) {
    // Deselect all
    document.querySelectorAll('.method-item').forEach(m => {
        m.classList.remove('selected');
        m.querySelector('.method-radio').style.cssText = '';
    });
    document.querySelectorAll('.method-detail').forEach(d => d.classList.remove('show'));

    // Select current
    el.classList.add('selected');
    el.querySelector('.method-radio').style.cssText = 'border: 5px solid var(--primary); background: white;';
    const detail = document.getElementById(detailId);
    if (detail) detail.classList.add('show');
}

function copyVA(btn, text) {
    navigator.clipboard.writeText(text).then(() => {
        btn.innerText = '✓ Tersalin!';
        btn.style.color = 'var(--success)';
        setTimeout(() => { btn.innerText = 'Copy'; btn.style.color = ''; }, 2000);
    });
}
</script>
<?php endif; ?>

</body>
</html>
