<?php
/**
 * Controller: User Event (Browse & Pesan)
 * Mengelola logic pencarian event dan pemesanan tiket oleh user.
 */
require_once __DIR__ . '/../models/EventModel.php';
require_once __DIR__ . '/../models/TiketModel.php';

class UserEventController {
    public function index() {
        requireLogin();
        
        // Pengecekan role khusus user (opsional jika ditaruh di requireLogin, tp role spesifik)
        if (currentUser()['role'] !== 'user') {
            header("Location: " . BASE_URL . "/login.php");
            exit;
        }

        $eventModel = new EventModel();
        $events = $eventModel->getBrowseEvents();

        require_once __DIR__ . '/../views/user/events.php';
    }

    public function pesan() {
        requireLogin();
        
        if (currentUser()['role'] !== 'user') {
            header("Location: " . BASE_URL . "/login.php");
            exit;
        }

        $id_event = (int)($_GET['id'] ?? 0);
        $eventModel = new EventModel();
        $tiketModel = new TiketModel();

        $event = $eventModel->findById($id_event);
        if (!$event) {
            header("Location: dashboard.php");
            exit;
        }

        $tikets = $tiketModel->getByEvent($id_event);
        $error = null;

        // Process ORDER
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order'])) {
            $user = currentUser();
            $id_user = $user['id'];
            $tickets_sel = $_POST['tickets'] ?? [];
            
            // Re-fetch raw connection for transaction
            require_once __DIR__ . '/../config/database.php';
            $conn = getDbConnection();
            
            $voucher_code = strtoupper(trim(mysqli_real_escape_string($conn, $_POST['voucher_code'] ?? '')));
            $total_gross = 0;
            $order_items = [];

            // Valdiasi tiket dan stok
            foreach ($tickets_sel as $id_tiket => $qty) {
                if ((int)$qty > 0) {
                    $id_tiket = (int)$id_tiket;
                    $t = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tiket WHERE id_tiket=$id_tiket"));
                    if (!$t) continue;
                    if ($t['kuota'] < (int)$qty) {
                        $error = "Stok tiket '{$t['nama_tiket']}' tidak mencukupi!";
                        break;
                    }
                    $sub = $t['harga'] * (int)$qty;
                    $total_gross += $sub;
                    $order_items[] = ['id_tiket' => $id_tiket, 'qty' => (int)$qty, 'subtotal' => $sub];
                }
            }

            if (!$error && empty($order_items)) {
                $error = "Pilih setidaknya satu tiket!";
            }

            if (!$error) {
                $potongan = 0; 
                $id_v = "NULL";
                
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
                    mysqli_query($conn, "INSERT INTO orders (id_user, total, status, id_voucher) VALUES ($id_user, $total_final, 'pending', $id_v)");
                    $id_order = mysqli_insert_id($conn);
                    
                    foreach ($order_items as $item) {
                        $it = $item['id_tiket']; 
                        $qty = $item['qty']; 
                        $sub = $item['subtotal'];
                        
                        mysqli_query($conn, "INSERT INTO order_detail (id_order, id_tiket, qty, subtotal) VALUES ($id_order, $it, $qty, $sub)");
                        $id_detail = mysqli_insert_id($conn);
                        
                        mysqli_query($conn, "UPDATE tiket SET kuota=kuota-$qty WHERE id_tiket=$it");
                        
                        for ($x = 0; $x < $qty; $x++) {
                            // Generate random unique code
                            $kode = "TKT-" . strtoupper(bin2hex(random_bytes(4)));
                            mysqli_query($conn, "INSERT INTO attendee (id_detail, kode_tiket) VALUES ($id_detail, '$kode')");
                        }
                    }
                    mysqli_commit($conn);
                    header("Location: " . BASE_URL . "/user/payment.php?id=" . $id_order);
                    exit;
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    $error = "Order gagal. Silakan coba lagi.";
                }
            }
        }

        require_once __DIR__ . '/../views/user/pesan.php';
    }
}
