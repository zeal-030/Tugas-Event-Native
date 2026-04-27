<?php
/**
 * UserDashboardController — Dashboard untuk User biasa
 */
class UserDashboardController {
    private $orderModel;
    private $attendeeModel;

    public function __construct() {
        require_once __DIR__ . '/../models/OrderModel.php';
        require_once __DIR__ . '/../models/AttendeeModel.php';
        $this->orderModel    = new OrderModel();
        $this->attendeeModel = new AttendeeModel();
    }

    public function index(): void {
        requireUser();

        $id_user       = currentUser()['id'];
        $recent_tickets = $this->attendeeModel->getByUser($id_user);
        $orders        = $this->orderModel->getByUser($id_user);

        // Statistik user
        require_once __DIR__ . '/../config/database.php';
        $conn = getDbConnection();
        $total_tickets = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT COUNT(*) as t FROM attendee a
             JOIN order_detail od ON a.id_detail = od.id_detail
             JOIN orders o ON od.id_order = o.id_order
             WHERE o.id_user = $id_user"
        ))['t'];
        $total_spent = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT SUM(total) as t FROM orders WHERE id_user = $id_user AND status = 'paid'"
        ))['t'];
        $active_events = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT COUNT(DISTINCT t.id_event) as t
             FROM attendee a
             JOIN order_detail od ON a.id_detail = od.id_detail
             JOIN orders o ON od.id_order = o.id_order
             JOIN tiket t ON od.id_tiket = t.id_tiket
             WHERE o.id_user = $id_user"
        ))['t'];

        require_once __DIR__ . '/../views/user/dashboard.php';
    }

    public function riwayat(): void {
        requireUser();
        $id_user = currentUser()['id'];

        require_once __DIR__ . '/../config/database.php';
        $conn    = getDbConnection();

        // ---- AUTO CANCEL EXPIRED PENDING ORDERS ----
        $today = date('Y-m-d');
        // Cari order pending milik user ini dimana event-nya sudah lewat
        $q_expired = mysqli_query($conn, 
            "SELECT DISTINCT o.id_order, o.id_voucher 
             FROM orders o
             JOIN order_detail od ON o.id_order = od.id_order
             JOIN tiket t ON od.id_tiket = t.id_tiket
             JOIN event e ON t.id_event = e.id_event
             WHERE o.id_user = $id_user AND o.status = 'pending' AND e.tanggal < '$today'"
        );

        while ($exp = mysqli_fetch_assoc($q_expired)) {
            $id_order = (int)$exp['id_order'];
            $id_voucher = !empty($exp['id_voucher']) ? (int)$exp['id_voucher'] : null;
            mysqli_begin_transaction($conn);
            try {
                // Update order status to cancel
                mysqli_query($conn, "UPDATE orders SET status = 'cancel' WHERE id_order = $id_order");
                
                // Restore kuota tiket
                $details = mysqli_query($conn, "SELECT id_tiket, qty FROM order_detail WHERE id_order = $id_order");
                while ($row = mysqli_fetch_assoc($details)) {
                    $id_t = (int)$row['id_tiket'];
                    $qty = (int)$row['qty'];
                    mysqli_query($conn, "UPDATE tiket SET kuota = kuota + $qty WHERE id_tiket = $id_t");
                }

                // Restore voucher quota
                if ($id_voucher) {
                    mysqli_query($conn, "UPDATE voucher SET kuota = kuota + 1 WHERE id_voucher = $id_voucher");
                }
                
                // Hapus attendee (QR) yang mungkin sudah terbuat
                mysqli_query($conn, "DELETE FROM attendee WHERE id_detail IN (SELECT id_detail FROM order_detail WHERE id_order = $id_order)");
                
                mysqli_commit($conn);
            } catch (Exception $e) {
                mysqli_rollback($conn);
            }
        }
        // --------------------------------------------

        $orders  = $this->orderModel->getByUser($id_user);

        require_once __DIR__ . '/../views/user/riwayat.php';
    }

    public function downloadTicket(): void {
        requireUser();
        $kode = $_GET['kode'] ?? '';
        if (!$kode) die("Kode tiket tidak ditemukan.");

        require_once __DIR__ . '/../config/database.php';
        $conn = getDbConnection();

        // Get ticket info
        $ticket = mysqli_fetch_assoc(mysqli_query($conn, 
            "SELECT a.*, t.nama_tiket, e.nama_event, v.nama_venue, v.alamat, e.tanggal, t.harga, o.id_order, u.nama as nama_user, e.gambar
             FROM attendee a
             JOIN order_detail od ON a.id_detail = od.id_detail
             JOIN orders o ON od.id_order = o.id_order
             JOIN tiket t ON od.id_tiket = t.id_tiket
             JOIN event e ON t.id_event = e.id_event
             JOIN venue v ON e.id_venue = v.id_venue
             JOIN users u ON o.id_user = u.id_user
             WHERE a.kode_tiket = '".mysqli_real_escape_string($conn, $kode)."' AND o.id_user = ".currentUser()['id']
        ));

        if (!$ticket) die("Tiket tidak valid atau bukan milik Anda.");

        require_once __DIR__ . '/../views/user/ticket_pdf.php';
    }

    public function profil(): void {
        requireLogin();
        $id_user = currentUser()['id'];
        
        require_once __DIR__ . '/../models/UserModel.php';
        $userModel = new UserModel();
        $profile_user = $userModel->findById($id_user);

        // Fetch Stats for Profile
        require_once __DIR__ . '/../config/database.php';
        $conn = getDbConnection();
        $total_tickets = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT COUNT(*) as t FROM attendee a
             JOIN order_detail od ON a.id_detail = od.id_detail
             JOIN orders o ON od.id_order = o.id_order
             WHERE o.id_user = $id_user AND o.status = 'paid'"
        ))['t'] ?? 0;

        $total_spent = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT SUM(total) as t FROM orders WHERE id_user = $id_user AND status = 'paid'"
        ))['t'] ?? 0;
        
        $msg = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama  = trim($_POST['nama'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $pass  = $_POST['password'] ?? '';
            
            // Basic validation
            if (!$nama || !$email) {
                $msg = 'err_input';
            } else {
                $hashed = $pass ? password_hash($pass, PASSWORD_DEFAULT) : null;
                if ($userModel->update($id_user, $nama, $email, $hashed)) {
                    $_SESSION['nama'] = $nama; // Update session
                    $msg  = 'success';
                    $profile_user = $userModel->findById($id_user); // Refresh data
                } else {
                    $msg = 'err_db';
                }
            }
        }
        
        require_once __DIR__ . '/../views/user/profil.php';
    }
}
