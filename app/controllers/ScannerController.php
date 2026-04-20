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
}
