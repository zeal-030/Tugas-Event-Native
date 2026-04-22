<?php
/**
 * TiketController — CRUD Tiket Management
 */
class TiketController {
    private $tiketModel;
    private $eventModel;
    private $venueModel;

    public function __construct() {
        require_once __DIR__ . '/../models/TiketModel.php';
        require_once __DIR__ . '/../models/EventModel.php';
        require_once __DIR__ . '/../models/VenueModel.php';
        $this->tiketModel = new TiketModel();
        $this->eventModel = new EventModel();
        $this->venueModel = new VenueModel();
    }

    public function index(): void {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePost();
        }

        if (isset($_GET['del'])) {
            $this->tiketModel->delete((int)$_GET['del']);
            header('Location: ' . BASE_URL . '/admin/tiket.php?msg=success_del');
            exit;
        }

        $tikets = $this->tiketModel->getAll();
        $events = $this->eventModel->getAll();
        $msg    = $_GET['msg'] ?? '';

        require_once __DIR__ . '/../views/admin/tiket/index.php';
    }

    private function handlePost(): void {
        $ide  = (int)($_POST['id_event'] ?? 0);
        $nama = $_POST['nama_tiket'] ?? '';
        $harga = (int)($_POST['harga'] ?? 0);
        $kuota = (int)($_POST['kuota'] ?? 0);

        // Ambil data event & kapasitas venue
        $event = $this->eventModel->findById($ide);
        $venue = $event ? $this->venueModel->findById((int)$event['id_venue']) : null;

        // Validasi: Event sudah lewat tidak bisa buat tiket
        if ($event && strtotime($event['tanggal']) < strtotime(date('Y-m-d'))) {
            header('Location: ' . BASE_URL . '/admin/tiket.php?msg=err_date');
            exit;
        }

        if (isset($_POST['submit'])) {
            if ($harga < 10000) {
                header('Location: ' . BASE_URL . '/admin/tiket.php?msg=err_price');
                exit;
            }
            $total_existing = $this->tiketModel->getTotalKuota($ide);
            if ($venue && ($total_existing + $kuota) > $venue['kapasitas']) {
                header('Location: ' . BASE_URL . '/admin/tiket.php?msg=err_capacity&cap=' . $venue['kapasitas']);
                exit;
            }
            $this->tiketModel->create($ide, $nama, $harga, $kuota);
            header('Location: ' . BASE_URL . '/admin/tiket.php?msg=success_add');
            exit;
        }

        if (isset($_POST['edit'])) {
            if ($harga < 10000) {
                header('Location: ' . BASE_URL . '/admin/tiket.php?msg=err_price');
                exit;
            }
            $id           = (int)$_POST['id'];
            $total_other  = $this->tiketModel->getTotalKuota($ide, $id);
            if ($venue && ($total_other + $kuota) > $venue['kapasitas']) {
                header('Location: ' . BASE_URL . '/admin/tiket.php?msg=err_capacity&cap=' . $venue['kapasitas']);
                exit;
            }
            $this->tiketModel->update($id, $ide, $nama, $harga, $kuota);
            header('Location: ' . BASE_URL . '/admin/tiket.php?msg=success_edit');
            exit;
        }
    }
}
