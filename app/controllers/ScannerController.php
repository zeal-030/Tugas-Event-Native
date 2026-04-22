<?php
/**
 * ScannerController — QR Code Check-in
 */
class ScannerController {
    private $attendeeModel;

    public function __construct() {
        require_once __DIR__ . '/../models/AttendeeModel.php';
        $this->attendeeModel = new AttendeeModel();
    }

    public function index(): void {
        requireStaff();

        $msg = null; $type = null; $data = null;

        $kode = trim($_POST['kode_tiket'] ?? '');
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $kode !== '') {
            $attendee = $this->attendeeModel->findByKode($kode);
            if ($attendee) {
                $today = date('Y-m-d');
                $event_date = $attendee['tanggal_event'];

                if ($today > $event_date) {
                    $msg  = "Tiket kedaluwarsa! Event ini sudah dilaksanakan pada <strong>" . date('d M Y', strtotime($event_date)) . "</strong>";
                    $type = 'danger';
                } elseif ($attendee['status_checkin'] === 'sudah') {
                    $msg  = "Tiket <strong>" . htmlspecialchars($kode) . "</strong> sudah pernah digunakan!";
                    $type = 'warning';
                } else {
                    $this->attendeeModel->checkin($kode);
                    $msg  = "Check-in berhasil untuk <strong>" . htmlspecialchars($attendee['customer']) . "</strong>";
                    $type = 'success';
                }
                $data = $attendee;
            } else {
                $msg  = "Kode tiket tidak dikenal atau tidak valid!";
                $type = 'danger';
            }
        }

        require_once __DIR__ . '/../views/admin/scanner.php';
    }

    public function list(): void {
        requireStaff();
        require_once __DIR__ . '/../models/EventModel.php';
        $eventModel = new EventModel();

        $id_event = isset($_GET['event']) ? (int)$_GET['event'] : null;
        $status   = isset($_GET['status']) ? $_GET['status'] : null;

        $attendees = $this->attendeeModel->getFilteredAttendees($id_event, $status);
        $events    = $eventModel->getAll();

        require_once __DIR__ . '/../views/admin/checkin_list.php';
    }

    public function petugasDashboard(): void {
        requirePetugas();
        
        require_once __DIR__ . '/../config/database.php';
        $conn = getDbConnection();

        // 1. Total Peserta (Paid)
        $total = mysqli_fetch_assoc(mysqli_query($conn, 
            "SELECT COUNT(*) as t FROM attendee a 
             JOIN order_detail od ON a.id_detail = od.id_detail 
             JOIN orders o ON od.id_order = o.id_order 
             WHERE o.status = 'paid'"))['t'];

        // 2. Sudah Checkin
        $checked = mysqli_fetch_assoc(mysqli_query($conn, 
            "SELECT COUNT(*) as t FROM attendee WHERE status_checkin = 'sudah'"))['t'];

        // 3. Belum Checkin
        $pending = $total - $checked;

        // 4. Riwayat 5 Terakhir (Join Users untuk dapat nama)
        $recent = mysqli_query($conn, 
            "SELECT a.*, u.nama as customer 
             FROM attendee a 
             JOIN order_detail od ON a.id_detail = od.id_detail 
             JOIN orders o ON od.id_order = o.id_order 
             JOIN users u ON o.id_user = u.id_user
             WHERE a.status_checkin = 'sudah' 
             ORDER BY a.waktu_checkin DESC LIMIT 5");

        require_once __DIR__ . '/../views/petugas/dashboard.php';
    }
}
