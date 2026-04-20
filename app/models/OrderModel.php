<?php
require_once __DIR__ . '/BaseModel.php';

class OrderModel extends BaseModel {

    public function getAll(): array {
        return $this->fetchAll(
            "SELECT o.*, u.nama, v.kode_voucher
             FROM orders o
             JOIN users u ON o.id_user = u.id_user
             LEFT JOIN voucher v ON o.id_voucher = v.id_voucher
             ORDER BY tanggal_order DESC"
        );
    }

    public function getRecent(int $limit = 6): array {
        return $this->fetchAll(
            "SELECT o.*, u.nama FROM orders o
             JOIN users u ON o.id_user = u.id_user
             ORDER BY tanggal_order DESC LIMIT $limit"
        );
    }

    public function getByUser(int $id_user): array {
        return $this->fetchAll(
            "SELECT o.*, v.kode_voucher, v.potongan
             FROM orders o
             LEFT JOIN voucher v ON o.id_voucher = v.id_voucher
             WHERE o.id_user = $id_user
             ORDER BY o.tanggal_order DESC"
        );
    }

    public function countAll(): int {
        $row = $this->fetchOne("SELECT COUNT(*) as t FROM orders");
        return (int)($row['t'] ?? 0);
    }

    public function totalRevenue(): float {
        $row = $this->fetchOne("SELECT COALESCE(SUM(total),0) as t FROM orders WHERE status = 'paid'");
        return (float)($row['t'] ?? 0);
    }

    public function getTicketSalesReport(): array {
        return $this->fetchAll(
            "SELECT e.nama_event, SUM(od.qty) AS total_terjual, SUM(od.subtotal) as revenue
             FROM event e
             JOIN tiket t ON t.id_event = e.id_event
             JOIN order_detail od ON od.id_tiket = t.id_tiket
             JOIN orders o ON o.id_order = od.id_order
             WHERE o.status = 'paid'
             GROUP BY e.id_event
             ORDER BY revenue DESC"
        );
    }

    public function getDetailByOrder(int $id_order): array {
        return $this->fetchAll(
            "SELECT od.*, t.nama_tiket, e.nama_event, v.nama_venue, v.alamat, e.tanggal
             FROM order_detail od
             JOIN tiket t ON od.id_tiket = t.id_tiket
             JOIN event e ON t.id_event = e.id_event
             JOIN venue v ON e.id_venue = v.id_venue
             WHERE od.id_order = $id_order"
        );
    }
}
