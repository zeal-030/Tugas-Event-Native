<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'user') { header("Location: ../login.php"); exit; }

$id_event = (int)$_GET['id'];
$event    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT e.*, v.nama_venue, v.alamat FROM event e JOIN venue v ON e.id_venue=v.id_venue WHERE id_event=$id_event"));
$tikets   = query("SELECT * FROM tiket WHERE id_event=$id_event ORDER BY harga");

if (!$event) { header("Location: dashboard.php"); exit; }

// LOGIKA ORDER BARU
if (isset($_POST['order'])) {
    $id_user      = $_SESSION['id_user'];
    $tickets_sel  = $_POST['tickets'] ?? [];
    $voucher_code = strtoupper(trim(mysqli_real_escape_string($conn, $_POST['voucher_code'] ?? '')));
    $total_gross  = 0;
    $order_items  = [];
    $error        = null;

    foreach ($tickets_sel as $id_tiket => $qty) {
        if ((int)$qty > 0) {
            $t = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tiket WHERE id_tiket=".(int)$id_tiket));
            if ($t['kuota'] < (int)$qty) { $error = "Stok tiket '{$t['nama_tiket']}' tidak mencukupi!"; break; }
            $sub = $t['harga'] * (int)$qty;
            $total_gross += $sub;
            $order_items[] = ['id_tiket'=>(int)$id_tiket, 'qty'=>(int)$qty, 'subtotal'=>$sub];
        }
    }

    if (!$error && empty($order_items)) $error = "Pilih setidaknya satu tiket!";

    if (!$error) {
        $potongan = 0; $id_v = "NULL";
        if (!empty($voucher_code)) {
            $v = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM voucher WHERE UPPER(kode_voucher)='$voucher_code' AND status='aktif' AND kuota>0"));
            if ($v) {
                $potongan = $v['potongan'];
                $id_v = $v['id_voucher'];
                mysqli_query($conn, "UPDATE voucher SET kuota=kuota-1 WHERE id_voucher=$id_v");
            }
        }
        $total_final = max(0, $total_gross - $potongan);
        
        mysqli_begin_transaction($conn);
        try {
            mysqli_query($conn, "INSERT INTO orders (id_user, total, status, id_voucher) VALUES ($id_user, $total_final, 'paid', $id_v)");
            $id_order = mysqli_insert_id($conn);
            foreach ($order_items as $item) {
                $it = $item['id_tiket']; $qty = $item['qty']; $sub = $item['subtotal'];
                mysqli_query($conn, "INSERT INTO order_detail (id_order, id_tiket, qty, subtotal) VALUES ($id_order, $it, $qty, $sub)");
                $id_detail = mysqli_insert_id($conn);
                mysqli_query($conn, "UPDATE tiket SET kuota=kuota-$qty WHERE id_tiket=$it");
                for ($x=0; $x<$qty; $x++) {
                    $kode = "TKT-" . strtoupper(bin2hex(random_bytes(4)));
                    mysqli_query($conn, "INSERT INTO attendee (id_detail, kode_tiket) VALUES ($id_detail, '$kode')");
                }
            }
            mysqli_commit($conn);
            header("Location: riwayat.php?success=1"); exit;
        } catch (Exception $e) { mysqli_rollback($conn); $error = "Order gagal. Silakan coba lagi."; }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Book: <?= $event['nama_event'] ?> — EventTiket</title>
    <?php $is_sub = true; include '../includes/head.php'; ?>
    <style>
        .ticket-row { background: var(--bg-surface); border: 1px solid var(--border); border-radius: 16px; padding: 1.2rem; transition: all 0.25s; }
        .ticket-row:hover { border-color: var(--primary); transform: translateX(5px); }
        .summary-card { position: sticky; top: 100px; background: var(--bg-surface); border: 1px solid var(--border); border-radius: 20px; padding: 1.5rem; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="mb-4 d-flex align-items-center gap-3">
        <a href="dashboard.php" class="btn btn-ghost btn-sm ps-0"><i class="bi bi-arrow-left"></i></a>
        <h2 class="fw-bold mb-0">Order Tickets</h2>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <?php if(isset($error)): ?><div class="alert alert-danger mb-4"><?= $error ?></div><?php endif; ?>
            
            <form action="" method="post" id="main-form">
                <div class="d-flex flex-column gap-3 mb-4">
                    <?php foreach($tikets as $t): ?>
                    <div class="ticket-row d-flex justify-content-between align-items-center">
                        <div style="flex:1;">
                            <div class="fw-bold mb-1"><?= $t['nama_tiket'] ?></div>
                            <div class="text-primary fw-bold">Rp <?= number_format($t['harga'],0,',','.') ?></div>
                            <div class="text-muted small mt-1"><?= $t['kuota'] ?> seats left</div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" onclick="changeQty('t-<?= $t['id_tiket'] ?>', -1)" class="btn btn-ghost btn-icon">−</button>
                            <input type="number" name="tickets[<?= $t['id_tiket'] ?>]" id="t-<?= $t['id_tiket'] ?>" value="0" min="0" max="<?= $t['kuota'] ?>" 
                                   class="form-control text-center fw-bold" style="width: 60px;" oninput="recalc()">
                            <button type="button" onclick="changeQty('t-<?= $t['id_tiket'] ?>', 1, <?= $t['kuota'] ?>)" class="btn btn-ghost btn-icon">+</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="card p-4">
                    <h6 class="fw-bold mb-3">🎁 Have a Voucher?</h6>
                    <div class="d-flex gap-2">
                        <input type="text" name="voucher_code" id="v_code" class="form-control" style="text-transform:uppercase;" placeholder="Contoh: DISKON50">
                        <button type="button" onclick="checkVoucher()" class="btn btn-ghost px-4">Apply</button>
                    </div>
                    <div id="v_msg" class="small mt-2"></div>
                </div>
                
                <button type="submit" name="order" class="btn btn-primary d-none" id="real-submit"></button>
            </form>
        </div>

        <div class="col-lg-5">
            <div class="summary-card">
                <h5 class="fw-bold mb-4">Order Summary</h5>
                <div class="row g-2 mb-3 small opacity-75" id="sum_items">
                    <div class="text-center py-2 text-muted">No tickets selected</div>
                </div>
                <hr class="opacity-10">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary">Subtotal</span>
                    <span class="fw-bold text-white" id="sum_sub">Rp 0</span>
                </div>
                <div class="d-flex justify-content-between mb-2 text-success d-none" id="row_disc">
                    <span>Discount</span>
                    <span class="fw-bold" id="sum_disc">- Rp 0</span>
                </div>
                <div class="d-flex justify-content-between mb-4 fs-5 mt-3 pt-3 border-top border-white border-opacity-10">
                    <span class="fw-bold text-white">Total</span>
                    <span class="fw-bold text-primary" id="sum_total">Rp 0</span>
                </div>
                <button type="button" onclick="document.getElementById('real-submit').click()" class="btn btn-primary btn-lg w-100 py-3 fw-bold">Confirm & Pay</button>
            </div>
        </div>
    </div>
</div>

<script>
const prices = <?= json_encode(array_column($tikets, 'harga', 'id_tiket')) ?>;
const names  = <?= json_encode(array_column($tikets, 'nama_tiket', 'id_tiket')) ?>;
let discount = 0;

function changeQty(id, delta, max=999) {
    const el = document.getElementById(id);
    let v = parseInt(el.value) + delta;
    el.value = Math.max(0, Math.min(max, v));
    recalc();
}

function recalc() {
    let subtotal = 0;
    let itemsHtml = "";
    Object.keys(prices).forEach(id => {
        const qty = parseInt(document.getElementById('t-' + id).value);
        if(qty > 0) {
            const rowSub = qty * prices[id];
            subtotal += rowSub;
            itemsHtml += `<div class='d-flex justify-content-between'><span>${names[id]} ×${qty}</span><span>Rp ${rowSub.toLocaleString()}</span></div>`;
        }
    });

    document.getElementById('sum_items').innerHTML = itemsHtml || "<div class='text-center py-2'>No tickets selected</div>";
    document.getElementById('sum_sub').innerText = "Rp " + subtotal.toLocaleString();
    
    const final = Math.max(0, subtotal - discount);
    document.getElementById('sum_total').innerText = "Rp " + final.toLocaleString();
}

// Fitur Baru: Cek Voucher Real-time (tanpa library ajax eksternal)
function checkVoucher() {
    const code = document.getElementById('v_code').value.trim().toUpperCase();
    const msg = document.getElementById('v_msg');
    
    if(!code) { msg.className="small mt-2 text-muted"; msg.innerText="Masukkan kode voucher."; return; }

    // Kita hitung di sini simulasi (Idealnya pakai AJAX fetch ke PHP, tapi untuk sekarang kita pakai logika simple di frontend 
    // atau jika Anda ingin sempurna, saya bisa buatkan API kecil. Untuk sekarang biar berfungsi, kita fetch):
    fetch(`../admin/voucher/check.php?code=${code}`)
    .then(r => r.json())
    .then(data => {
        if(data.status === 'success') {
            discount = data.potongan;
            msg.className = "small mt-2 text-success fw-bold";
            msg.innerText = `✅ Berhasil! Potongan Rp ${discount.toLocaleString()}`;
            document.getElementById('row_disc').classList.remove('d-none');
            document.getElementById('sum_disc').innerText = "- Rp " + discount.toLocaleString();
        } else {
            discount = 0;
            msg.className = "small mt-2 text-danger fw-bold";
            msg.innerText = "❌ Voucher tidak valid atau habis.";
            document.getElementById('row_disc').classList.add('d-none');
        }
        recalc();
    });
}
</script>
</body>
</html>
