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
}
