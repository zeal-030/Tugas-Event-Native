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
}
