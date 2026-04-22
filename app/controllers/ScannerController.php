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
        requireAdmin();

        $msg = null; $type = null; $data = null;

        $kode = trim($_POST['kode_tiket'] ?? '');
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $kode !== '') {
            $attendee = $this->attendeeModel->findByKode($kode);
            if ($attendee) {
                if ($attendee['status_checkin'] === 'sudah') {
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
        requireAdmin();
        require_once __DIR__ . '/../models/EventModel.php';
        $eventModel = new EventModel();

        $id_event = isset($_GET['event']) ? (int)$_GET['event'] : null;
        $status   = isset($_GET['status']) ? $_GET['status'] : null;

        $attendees = $this->attendeeModel->getFilteredAttendees($id_event, $status);
        $events    = $eventModel->getAll();

        require_once __DIR__ . '/../views/admin/checkin_list.php';
    }
}
