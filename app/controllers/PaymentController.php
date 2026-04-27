<?php
/**
 * Controller: Payment
 * Simulasi Payment Gateway dan Pembatalan
 */
require_once __DIR__ . '/../models/OrderModel.php';

class PaymentController {
    public function index() {
        requireLogin();
        
        $id_order = (int)($_GET['id'] ?? 0);
        if (!$id_order) {
            header("Location: dashboard.php");
            exit;
        }

        $user = currentUser();
        require_once __DIR__ . '/../config/database.php';
        $conn = getDbConnection();

        // Get Order
        $order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM orders WHERE id_order = $id_order AND id_user = {$user['id']}"));
        if (!$order) {
            header("Location: riwayat.php");
            exit;
        }

        // Action: BAYAR
        if (isset($_POST['pay'])) {
            if ($order['status'] === 'pending') {
                mysqli_query($conn, "UPDATE orders SET status = 'paid' WHERE id_order = $id_order");
                header("Location: " . BASE_URL . "/user/riwayat.php?payment_success=1");
                exit;
            }
        }

        // Action: BATALKAN
        if (isset($_POST['cancel'])) {
            if ($order['status'] === 'pending') {
                mysqli_begin_transaction($conn);
                try {
                    // Update order status to cancel
                    mysqli_query($conn, "UPDATE orders SET status = 'cancel' WHERE id_order = $id_order");

                    // Restore kuota (ticket stock)
                    $details = mysqli_query($conn, "SELECT id_tiket, qty FROM order_detail WHERE id_order = $id_order");
                    while ($row = mysqli_fetch_assoc($details)) {
                        $id_t = (int)$row['id_tiket'];
                        $qty = (int)$row['qty'];
                        mysqli_query($conn, "UPDATE tiket SET kuota = kuota + $qty WHERE id_tiket = $id_t");
                    }

                    // Restore voucher quota
                    if (!empty($order['id_voucher'])) {
                        $id_v = (int)$order['id_voucher'];
                        mysqli_query($conn, "UPDATE voucher SET kuota = kuota + 1 WHERE id_voucher = $id_v");
                    }

                    // Delete generated attendees (QR codes)
                    mysqli_query($conn, "DELETE FROM attendee WHERE id_detail IN (SELECT id_detail FROM order_detail WHERE id_order = $id_order)");

                    mysqli_commit($conn);
                    header("Location: " . BASE_URL . "/user/riwayat.php?cancelled=1");
                    exit;
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                }
            }
        }

        // Fetch order details for display
        $items = [];
        $res = mysqli_query($conn, "SELECT od.*, t.nama_tiket, t.harga, e.nama_event, e.tanggal, v.nama_venue
                                    FROM order_detail od
                                    JOIN tiket t ON od.id_tiket = t.id_tiket
                                    JOIN event e ON t.id_event = e.id_event
                                    JOIN venue v ON e.id_venue = v.id_venue
                                    WHERE od.id_order = $id_order");
        while ($row = mysqli_fetch_assoc($res)) $items[] = $row;

        require_once __DIR__ . '/../views/user/payment.php';
    }
}
