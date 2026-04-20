<?php
/**
 * DashboardController — Statistik & Data Dashboard Admin
 */
class DashboardController {
    private $userModel;
    private $orderModel;
    private $eventModel;
    private $attendeeModel;

    public function __construct() {
        require_once __DIR__ . '/../models/UserModel.php';
        require_once __DIR__ . '/../models/OrderModel.php';
        require_once __DIR__ . '/../models/EventModel.php';
        require_once __DIR__ . '/../models/AttendeeModel.php';
        $this->userModel     = new UserModel();
        $this->orderModel    = new OrderModel();
        $this->eventModel    = new EventModel();
        $this->attendeeModel = new AttendeeModel();
    }

    public function index(): void {
        requireAdmin();

        $total_users    = $this->userModel->countByRole('user');
        $total_orders   = $this->orderModel->countAll();
        $total_revenue  = $this->orderModel->totalRevenue();
        $total_events   = $this->eventModel->countTotal();
        $total_checkins = $this->attendeeModel->countCheckedIn();
        $recent_orders  = $this->orderModel->getRecent(6);
        $recent_events  = $this->eventModel->getRecent(4);

        require_once __DIR__ . '/../views/admin/dashboard.php';
    }
}
