<?php
/**
 * VoucherController — CRUD Voucher Management
 */
class VoucherController {
    private $voucherModel;

    public function __construct() {
        require_once __DIR__ . '/../models/VoucherModel.php';
        $this->voucherModel = new VoucherModel();
    }

    public function index(): void {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePost();
        }

        if (isset($_GET['del'])) {
            $this->voucherModel->delete((int)$_GET['del']);
            header('Location: ' . BASE_URL . '/admin/voucher.php?msg=success_del');
            exit;
        }

        $vouchers       = $this->voucherModel->getAll();
        $total_active   = count(array_filter($vouchers, fn($v) => $v['status'] === 'aktif'));
        $total_inactive = count($vouchers) - $total_active;
        $msg            = $_GET['msg'] ?? '';

        require_once __DIR__ . '/../views/admin/voucher/index.php';
    }

    private function handlePost(): void {
        $kode     = $_POST['kode_voucher'] ?? '';
        $potongan = (int)($_POST['potongan'] ?? 0);
        $kuota    = (int)($_POST['kuota']    ?? 0);
        $status   = $_POST['status']         ?? 'aktif';

        if (isset($_POST['submit'])) {
            $this->voucherModel->create($kode, $potongan, $kuota, $status);
            header('Location: ' . BASE_URL . '/admin/voucher.php?msg=success_add');
            exit;
        }

        if (isset($_POST['edit'])) {
            $this->voucherModel->update((int)$_POST['id'], $kode, $potongan, $kuota, $status);
            header('Location: ' . BASE_URL . '/admin/voucher.php?msg=success_edit');
            exit;
        }
    }

    public function check(): void {
        header('Content-Type: application/json');
        $code = $_GET['code'] ?? '';
        $v = $this->voucherModel->findByCode($code);
        
        if ($v) {
            echo json_encode(['status' => 'success', 'potongan' => (int)$v['potongan']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Voucher tidak valid!']);
        }
        exit;
    }
}
