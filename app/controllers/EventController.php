<?php
/**
 * EventController — CRUD Event Management
 */
class EventController {
    private $eventModel;
    private $venueModel;

    public function __construct() {
        require_once __DIR__ . '/../models/EventModel.php';
        require_once __DIR__ . '/../models/VenueModel.php';
        $this->eventModel = new EventModel();
        $this->venueModel = new VenueModel();
    }

    public function index(): void {
        requireSuperAdmin();

        $search      = trim($_GET['search'] ?? '');
        $limit       = 10;
        $page        = max(1, (int)($_GET['page'] ?? 1));
        $offset      = ($page - 1) * $limit;
        $total_data  = $this->eventModel->count($search);
        $total_pages = (int)ceil($total_data / $limit);
        $events      = $this->eventModel->getAll($search, $limit, $offset);
        $venues      = $this->venueModel->getAll();
        $stats_upcoming = $this->eventModel->countUpcoming();
        $stats_venues   = $this->venueModel->count();

        // Handle POST (Create/Edit)
        $msg = $_GET['msg'] ?? '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePost();
        }

        // Handle DELETE
        if (isset($_GET['del'])) {
            $this->delete((int)$_GET['del']);
        }

        require_once __DIR__ . '/../views/admin/event/index.php';
    }

    private function handlePost(): void {
        if (isset($_POST['submit'])) {
            $gambar = $this->uploadGambar();
            $this->eventModel->create(
                $_POST['nama_event'],
                $_POST['deskripsi'],
                $_POST['tanggal'],
                (int)$_POST['id_venue'],
                $gambar
            );
            header('Location: ' . BASE_URL . '/admin/event.php?msg=success_add');
            exit;
        }

        if (isset($_POST['edit'])) {
            $id    = (int)$_POST['id'];
            $vid   = (int)$_POST['id_venue'];
            $venue = $this->venueModel->findById($vid);

            // Cek kapasitas
            require_once __DIR__ . '/../models/TiketModel.php';
            $tiketModel   = new TiketModel();
            $total_tikets = $tiketModel->getTotalKuota($id);

            if ($venue && $total_tikets > $venue['kapasitas']) {
                header('Location: ' . BASE_URL . '/admin/event.php?msg=err_capacity&cap=' . $venue['kapasitas'] . '&need=' . $total_tikets);
                exit;
            }

            $gambar = ($_FILES['gambar']['error'] === 0) ? $this->uploadGambar() : null;
            $this->eventModel->update(
                $id,
                $_POST['nama_event'],
                $_POST['deskripsi'],
                $_POST['tanggal'],
                $vid,
                $gambar
            );
            header('Location: ' . BASE_URL . '/admin/event.php?msg=success_edit');
            exit;
        }
    }

    private function delete(int $id): void {
        $old = $this->eventModel->getGambar($id);
        if ($old && $old !== 'default.jpg') {
            $path = UPLOAD_DIR . $old;
            if (file_exists($path)) unlink($path);
        }
        $this->eventModel->delete($id);
        header('Location: ' . BASE_URL . '/admin/event.php?msg=success_del');
        exit;
    }

    private function uploadGambar(): string {
        if (!isset($_FILES['gambar']) || $_FILES['gambar']['error'] !== 0) return 'default.jpg';
        $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) return 'default.jpg';
        $newName = 'event_' . uniqid() . '.' . $ext;
        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0777, true);
        move_uploaded_file($_FILES['gambar']['tmp_name'], UPLOAD_DIR . $newName);
        return $newName;
    }
}
