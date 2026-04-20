<?php
/**
 * VenueController — CRUD Venue Management
 */
class VenueController {
    private $venueModel;

    public function __construct() {
        require_once __DIR__ . '/../models/VenueModel.php';
        $this->venueModel = new VenueModel();
    }

    public function index(): void {
        requireSuperAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePost();
        }

        if (isset($_GET['del'])) {
            $this->venueModel->delete((int)$_GET['del']);
            header('Location: ' . BASE_URL . '/admin/venue.php?msg=success_del');
            exit;
        }

        $venues = $this->venueModel->getAll();
        $msg    = $_GET['msg'] ?? '';

        require_once __DIR__ . '/../views/admin/venue/index.php';
    }

    private function handlePost(): void {
        if (isset($_POST['submit'])) {
            $this->venueModel->create(
                $_POST['nama_venue'],
                $_POST['alamat'],
                (int)$_POST['kapasitas']
            );
            header('Location: ' . BASE_URL . '/admin/venue.php?msg=success_add');
            exit;
        }

        if (isset($_POST['edit'])) {
            $this->venueModel->update(
                (int)$_POST['id'],
                $_POST['nama_venue'],
                $_POST['alamat'],
                (int)$_POST['kapasitas']
            );
            header('Location: ' . BASE_URL . '/admin/venue.php?msg=success_edit');
            exit;
        }
    }
}
