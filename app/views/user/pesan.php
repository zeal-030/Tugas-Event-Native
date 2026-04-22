<?php
/**
 * View: User Pesan Tiket
 * Data dari UserEventController: $event, $tikets, $error
 */
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Tickets: <?= htmlspecialchars($event['nama_event']) ?> — <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <script>document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'dark');</script>
    <style>
        body { background: var(--bg-base); font-family: 'Plus Jakarta Sans', sans-serif; }

        /* ---- TOP BAR ---- */
        .checkout-topbar {
            background: var(--bg-surface);
            border-bottom: 1px solid var(--border);
            padding: 0.85rem 2rem;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 100;
        }
        .checkout-brand { display: flex; align-items: center; gap: 0.6rem; text-decoration: none; }
        .checkout-brand .brand-icon { width: 32px; height: 32px; background: var(--gradient-primary); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
        .checkout-brand .brand-text { font-weight: 800; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-size: 1rem; }
        .secure-tag { display: flex; align-items: center; gap: 0.4rem; font-size: 0.75rem; color: var(--text-muted); }
        .secure-tag i { color: var(--success); }

        /* ---- EVENT HERO BANNER ---- */
        .event-hero {
            background: linear-gradient(135deg, rgba(124,58,237,0.15) 0%, rgba(6,182,212,0.1) 100%);
            border: 1px solid rgba(124,58,237,0.2);
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            display: flex; align-items: center; gap: 1rem;
        }
        .event-hero .hero-icon { width: 60px; height: 60px; background: var(--gradient-primary); border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; flex-shrink: 0; }
        .event-hero .event-name { font-size: 1.15rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.25rem; }
        .event-hero .event-meta-chip { display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.75rem; color: var(--text-secondary); padding: 0.25rem 0.7rem; background: rgba(255,255,255,0.05); border-radius: 20px; border: 1px solid var(--border); }

        /* ---- STEPS ---- */
        .steps-bar { display: flex; align-items: center; justify-content: center; gap: 0; margin-bottom: 2rem; }
        .step { display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; font-weight: 600; color: var(--text-muted); }
        .step.active { color: var(--primary-light); }
        .step-num { width: 28px; height: 28px; border-radius: 50%; background: var(--bg-elevated); border: 2px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; }
        .step.active .step-num { background: var(--gradient-primary); border-color: transparent; color: white; }
        .step-line { width: 40px; height: 2px; background: var(--border); margin: 0 0.5rem; }

        /* ---- SECTION CARD ---- */
        .section-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px; padding: 1.25rem 1.5rem; margin-bottom: 1rem; }
        .section-label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }

        /* ---- TICKET ROW ---- */
        .ticket-row { background: var(--bg-elevated); border: 1.5px solid var(--border); border-radius: 12px; padding: 1rem 1.25rem; transition: all 0.2s; display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
        .ticket-row:hover { border-color: rgba(124,58,237,0.4); background: rgba(124,58,237,0.04); }
        .ticket-type-badge { font-size: 0.68rem; padding: 0.2rem 0.6rem; border-radius: 20px; background: rgba(124,58,237,0.15); color: var(--primary-light); font-weight: 700; letter-spacing: 0.3px; }
        .qty-control { display: flex; align-items: center; gap: 0.5rem; }
        .qty-btn { width: 34px; height: 34px; border-radius: 50%; border: 1.5px solid var(--border); background: var(--bg-surface); color: var(--text-primary); font-size: 1.1rem; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.15s; font-weight: 700; }
        .qty-btn:hover { border-color: var(--primary); color: var(--primary-light); background: rgba(124,58,237,0.1); }
        .qty-input { width: 52px; height: 34px; text-align: center; border: 1.5px solid var(--border); border-radius: 8px; background: var(--bg-elevated); color: var(--text-primary); font-weight: 800; font-size: 0.95rem; }
        .qty-input:focus { outline: none; border-color: var(--primary); }

        /* ---- VOUCHER ---- */
        .voucher-input-refined { 
            background: var(--bg-elevated) !important; 
            border: 1.5px solid var(--border) !important; 
            border-left: none !important;
            color: var(--text-primary) !important; 
            border-radius: 0 12px 12px 0 !important; 
            padding: 0.7rem 1rem; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            font-size: 0.875rem; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
        }
        .voucher-input-refined:focus { 
            outline: none; 
            border-color: var(--primary) !important; 
            box-shadow: 0 0 0 3px rgba(124,58,237,0.15); 
        }
        .voucher-input-refined::placeholder { text-transform: none; letter-spacing: 0; }

        /* ---- ORDER SUMMARY STICKY ---- */
        .order-summary-sticky { position: sticky; top: 76px; }
        .summary-box { background: var(--bg-card); border: 1px solid var(--border); border-radius: 20px; overflow: hidden; }
        .summary-header { background: var(--gradient-primary); padding: 1.2rem 1.5rem; }
        .summary-body { padding: 1.25rem 1.5rem; }
        .summary-item-row { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; font-size: 0.85rem; color: var(--text-secondary); border-bottom: 1px solid var(--border); }
        .summary-item-row:last-child { border-bottom: none; }
        .summary-total { display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; margin-top: 0.5rem; border-top: 1.5px solid rgba(124,58,237,0.3); }
        .empty-cart { text-align: center; padding: 1.5rem; color: var(--text-muted); font-size: 0.85rem; }
        .pay-btn { background: var(--gradient-primary); color: white; border: none; border-radius: 14px; padding: 0.9rem 1.5rem; width: 100%; font-weight: 800; font-size: 1rem; cursor: pointer; transition: all 0.25s; display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-top: 1rem; box-shadow: 0 4px 20px rgba(124,58,237,0.4); }
        .pay-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(124,58,237,0.5); }
        .pay-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
        .secure-notice { font-size: 0.72rem; color: var(--text-muted); text-align: center; margin-top: 0.75rem; display: flex; align-items: center; justify-content: center; gap: 0.35rem; }
    </style>
</head>
<body>

<!-- TOP BAR -->
<div class="checkout-topbar">
    <a href="<?= BASE_URL ?>/user/events.php" class="checkout-brand">
        <div class="brand-icon"><i class="ri-ticket-2-fill" style="color:white;"></i></div>
        <div class="brand-text"><?= APP_NAME ?></div>
    </a>
    <div class="secure-tag">
        <i class="ri-shield-keyhole-fill"></i> Secure Checkout
    </div>
    <div class="text-muted small">Hi, <?= htmlspecialchars($user['nama']) ?></div>
</div>

<div class="container py-4" style="max-width: 1100px;">

    <!-- Steps Progress -->
    <div class="steps-bar mb-4">
        <div class="step active">
            <div class="step-num">1</div>
            <span>Pilih Tiket</span>
        </div>
        <div class="step-line"></div>
        <div class="step">
            <div class="step-num">2</div>
            <span>Pembayaran</span>
        </div>
        <div class="step-line"></div>
        <div class="step">
            <div class="step-num">3</div>
            <span>E-Ticket</span>
        </div>
    </div>

    <!-- Event Hero -->
    <div class="event-hero">
        <div class="hero-icon"><i class="ri-tent-fill" style="color:white;"></i></div>
        <div>
            <div class="event-name"><?= htmlspecialchars($event['nama_event']) ?></div>
            <div class="d-flex flex-wrap gap-2 mt-1">
                <span class="event-meta-chip"><i class="ri-calendar-event-line"></i> <?= date('l, d M Y', strtotime($event['tanggal'])) ?></span>
                <span class="event-meta-chip"><i class="ri-map-pin-line"></i> <?= htmlspecialchars($event['nama_venue']) ?></span>
                <span class="event-meta-chip"><i class="ri-map-2-line"></i> <?= htmlspecialchars($event['alamat']) ?></span>
            </div>
        </div>
    </div>

    <?php if(isset($error)): ?>
        <div class="alert alert-danger mb-4"><i class="ri-alert-fill me-2"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="" method="post" id="main-form">
    <div class="row g-4">
        <!-- LEFT COLUMN -->
        <div class="col-lg-7">
            <!-- Pilih Tiket -->
            <div class="section-card">
                <div class="section-label"><i class="ri-ticket-2-line" style="color:var(--primary-light)"></i> Pilih Tiket</div>
                <div class="d-flex flex-column gap-2">
                    <?php if (empty($tikets)): ?>
                        <p class="text-muted text-center py-3">Belum ada tiket tersedia untuk event ini.</p>
                    <?php endif; ?>
                    <?php foreach($tikets as $t): ?>
                    <div class="ticket-row">
                        <div style="flex: 1;">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <div class="fw-bold"><?= htmlspecialchars($t['nama_tiket']) ?></div>
                                <span class="ticket-type-badge">Regular</span>
                            </div>
                            <div class="fw-bold" style="color:var(--primary-light); font-size:1.05rem;">Rp <?= number_format($t['harga'],0,',','.') ?></div>
                            <div class="text-muted" style="font-size:0.75rem; margin-top:0.2rem;">
                                <?php if ($t['kuota'] <= 10 && $t['kuota'] > 0): ?>
                                    <span style="color: var(--warning);"><i class="ri-error-warning-fill"></i> Tersisa <?= $t['kuota'] ?> kursi!</span>
                                <?php elseif ($t['kuota'] == 0): ?>
                                    <span style="color: var(--danger);">Habis Terjual</span>
                                <?php else: ?>
                                    <i class="ri-team-line"></i> <?= $t['kuota'] ?> kursi tersedia
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="qty-control">
                            <button type="button" onclick="changeQty('t-<?= $t['id_tiket'] ?>', -1)" class="qty-btn" <?= $t['kuota']==0?'disabled':'' ?>>−</button>
                            <input type="number" name="tickets[<?= $t['id_tiket'] ?>]" id="t-<?= $t['id_tiket'] ?>" value="0" min="0" max="<?= $t['kuota'] ?>" class="qty-input" oninput="recalc()" <?= $t['kuota']==0?'disabled':'' ?>>
                            <button type="button" onclick="changeQty('t-<?= $t['id_tiket'] ?>', 1, <?= $t['kuota'] ?>)" class="qty-btn" <?= $t['kuota']==0?'disabled':'' ?>>+</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Kode Voucher -->
            <div class="section-card">
                <div class="section-label"><i class="ri-price-tag-3-line-fill" style="color: var(--accent-2)"></i> Kode Voucher / Promo</div>
                <div class="d-flex gap-2">
                    <div class="input-group" style="flex: 1;">
                        <span class="input-group-text" style="background:var(--bg-elevated); border-color:var(--border); color:var(--text-muted); border-radius: 12px 0 0 12px;">
                            <i class="ri-gift-2-line"></i>
                        </span>
                        <input type="text" name="voucher_code" id="v_code" class="form-control voucher-input-refined" placeholder="Masukkan kode promo (contoh: DISKON50)">
                    </div>
                    <button type="button" onclick="checkVoucher()" class="btn btn-primary px-4" style="border-radius: 12px; font-weight: 700;">
                        Gunakan
                    </button>
                </div>
                <div id="v_msg" class="small mt-2"></div>
            </div>

            <!-- Info Pembelian -->
            <div class="section-card" style="background: rgba(6,182,212,0.04); border-color: rgba(6,182,212,0.15);">
                <div class="d-flex gap-3 align-items-start">
                    <i class="ri-information-fill mt-1" style="color: var(--accent); font-size: 1.1rem; flex-shrink: 0;"></i>
                    <div class="small text-secondary lh-lg">
                        <strong class="text-white">Syarat &amp; Ketentuan Pembelian Tiket:</strong><br>
                        • Tiket yang sudah dibeli <strong>tidak dapat di-refund</strong>.<br>
                        • QR Code hanya valid untuk satu kali check-in di pintu masuk.<br>
                        • Simpan e-ticket Anda dengan baik, tunjukkan saat masuk venue.
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: Order Summary -->
        <div class="col-lg-5">
            <div class="order-summary-sticky">
                <div class="summary-box">
                    <div class="summary-header">
                        <div class="fw-bold text-white" style="font-size: 0.9rem;"><i class="ri-ticket-2-fill me-1"></i> Ringkasan Pesanan</div>
                        <div class="text-white text-opacity-75" style="font-size: 0.75rem; margin-top: 0.25rem;"><?= htmlspecialchars($event['nama_event']) ?></div>
                    </div>
                    <div class="summary-body">
                        <!-- Items List -->
                        <div id="sum_items">
                            <div class="empty-cart">
                                <i class="ri-shopping-cart-line" style="font-size: 2rem; opacity: 0.3; display: block; margin-bottom: 0.5rem;"></i>
                                Belum ada tiket dipilih
                            </div>
                        </div>

                        <!-- Price Breakdown -->
                        <div id="price-breakdown" class="d-none">
                            <div class="summary-total">
                                <span class="text-secondary" style="font-size: 0.82rem;">Subtotal</span>
                                <span class="fw-bold" id="sum_sub">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-1" id="row_disc" style="display:none !important;">
                                <span class="text-success small"><i class="ri-price-tag-3-line-fill"></i> Diskon Voucher</span>
                                <span class="text-success fw-bold" id="sum_disc">- Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-1">
                                <span class="text-secondary small">Biaya Layanan</span>
                                <span class="text-success small fw-bold">GRATIS</span>
                            </div>
                            <div style="border-top: 1.5px dashed rgba(124,58,237,0.3); margin: 0.75rem 0;"></div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-white">Total Bayar</span>
                                <span class="fw-bold text-primary" id="sum_total" style="font-size: 1.35rem;">Rp 0</span>
                            </div>
                        </div>

                        <button type="button" id="pay-btn" onclick="document.getElementById('real-submit').click()" class="pay-btn" disabled>
                            <i class="ri-lock-2-line-fill"></i> Lanjut ke Pembayaran
                        </button>
                        <div class="secure-notice">
                            <i class="ri-shield-check-fill" style="color: var(--success);"></i>
                            Transaksi diproses dengan aman
                        </div>
                    </div>
                </div>

                <!-- Payment Methods Preview -->
                <div class="mt-3 p-3 d-flex justify-content-center align-items-center gap-3 flex-wrap" style="opacity: 0.5;">
                    <span class="text-muted" style="font-size: 0.7rem;">Diterima:</span>
                    <span class="badge" style="background:var(--bg-elevated); border:1px solid var(--border); color:var(--text-secondary); font-size:0.7rem;"><i class="ri-bank-card-line me-1"></i> Kartu Kredit</span>
                    <span class="badge" style="background:var(--bg-elevated); border:1px solid var(--border); color:var(--text-secondary); font-size:0.7rem;"><i class="ri-bank-line me-1"></i> Transfer</span>
                    <span class="badge" style="background:var(--bg-elevated); border:1px solid var(--border); color:var(--text-secondary); font-size:0.7rem;"><i class="ri-wallet-3-line me-1"></i> E-Wallet</span>
                </div>
            </div>
        </div>
    </div>

    <button type="submit" name="order" class="btn btn-primary d-none" id="real-submit"></button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const prices = <?= json_encode(array_column($tikets, 'harga', 'id_tiket')) ?>;
const names  = <?= json_encode(array_column($tikets, 'nama_tiket', 'id_tiket')) ?>;
let discount = 0;

function changeQty(id, delta, max=999) {
    const el = document.getElementById(id);
    let v = parseInt(el.value||0) + delta;
    el.value = Math.max(0, Math.min(max, v));
    recalc();
}

function recalc() {
    let subtotal = 0;
    let itemsHtml = "";
    Object.keys(prices).forEach(id => {
        const el = document.getElementById('t-' + id);
        if (!el) return;
        const qty = parseInt(el.value||0);
        if(qty > 0) {
            const rowSub = qty * prices[id];
            subtotal += rowSub;
            itemsHtml += `<div class="summary-item-row"><span><span class="fw-bold text-white">${names[id]}</span> <span class="text-muted">×${qty}</span></span><span class="fw-bold">Rp ${rowSub.toLocaleString('id-ID')}</span></div>`;
        }
    });

    const summaryEl = document.getElementById('sum_items');
    const breakdownEl = document.getElementById('price-breakdown');
    const payBtn = document.getElementById('pay-btn');

    if (itemsHtml) {
        summaryEl.innerHTML = itemsHtml;
        breakdownEl.classList.remove('d-none');
        payBtn.disabled = false;
    } else {
        summaryEl.innerHTML = `<div class="empty-cart"><i class="ri-shopping-cart-line" style="font-size: 2rem; opacity: 0.3; display: block; margin-bottom: 0.5rem;"></i>Belum ada tiket dipilih</div>`;
        breakdownEl.classList.add('d-none');
        payBtn.disabled = true;
    }

    document.getElementById('sum_sub').innerText = "Rp " + subtotal.toLocaleString('id-ID');
    const final = Math.max(0, subtotal - discount);
    document.getElementById('sum_total').innerText = "Rp " + final.toLocaleString('id-ID');
}

function checkVoucher() {
    const code = document.getElementById('v_code').value.trim().toUpperCase();
    const msg = document.getElementById('v_msg');
    if(!code) { msg.className="small mt-2 text-muted"; msg.innerText="Masukkan kode voucher terlebih dahulu."; return; }

    msg.className = "small mt-2 text-muted";
    msg.innerText = "Memeriksa voucher...";

    fetch(`<?= BASE_URL ?>/admin/voucher/check.php?code=${code}`)
    .then(r => r.json())
    .then(data => {
        if(data.status === 'success') {
            discount = data.potongan;
            msg.className = "small mt-2 text-success fw-bold";
            msg.innerText = `Voucher valid! Hemat Rp ${discount.toLocaleString('id-ID')}`;
            msg.innerHTML = `<i class="ri-checkbox-circle-fill"></i> ` + msg.innerText;
            document.getElementById('row_disc').style.removeProperty('display');
            document.getElementById('row_disc').style.display = 'flex';
            document.getElementById('sum_disc').innerText = "- Rp " + discount.toLocaleString('id-ID');
        } else {
            discount = 0;
            msg.className = "small mt-2 text-danger fw-bold";
            msg.innerText = "Voucher tidak valid atau sudah habis.";
            msg.innerHTML = `<i class="ri-close-circle-fill"></i> ` + msg.innerText;
            document.getElementById('row_disc').style.display = 'none';
        }
        recalc();
    })
    .catch(() => { msg.className = "small mt-2 text-danger"; msg.innerText = "Gagal memeriksa voucher."; });
}
</script>
</body>
</html>
