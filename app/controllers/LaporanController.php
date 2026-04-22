<?php
/**
 * LaporanController — Laporan Keuangan & Transaksi
 */
class LaporanController {
    private $orderModel;

    public function __construct() {
        require_once __DIR__ . '/../models/OrderModel.php';
        $this->orderModel = new OrderModel();
    }

    public function index(): void {
        requireAdmin();

        $orders        = $this->orderModel->getAll();
        $tickets_sold  = $this->orderModel->getTicketSalesReport();
        $total_rev     = array_sum(array_column($tickets_sold, 'revenue'));
        $total_terjual = array_sum(array_column($tickets_sold, 'total_terjual'));
        $total_orders  = count($orders);
        $paid_orders   = count(array_filter($orders, fn($o) => $o['status'] === 'paid'));

        require_once __DIR__ . '/../views/admin/laporan.php';
    }

    public function pdf(): void {
        requireAdmin();

        $orders        = $this->orderModel->getAll();
        $tickets_sold  = $this->orderModel->getTicketSalesReport();
        $total_rev     = array_sum(array_column($tickets_sold, 'revenue'));
        $total_terjual = array_sum(array_column($tickets_sold, 'total_terjual'));
        $total_orders  = count($orders);
        $paid_orders   = count(array_filter($orders, fn($o) => $o['status'] === 'paid'));

        require_once __DIR__ . '/../views/admin/laporan_pdf.php';
    }
}
