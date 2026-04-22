<?php
require_once __DIR__ . '/BaseModel.php';

class AttendeeModel extends BaseModel {

    public function countCheckedIn(): int {
        $row = $this->fetchOne("SELECT COUNT(*) as t FROM attendee WHERE status_checkin = 'sudah'");
        return (int)($row['t'] ?? 0);
    }

    public function countAll(): int {
        $row = $this->fetchOne("SELECT COUNT(*) as t FROM attendee");
        return (int)($row['t'] ?? 0);
    }

    public function findByKode(string $kode): ?array {
        $kode = $this->escape($kode);
        return $this->fetchOne(
            "SELECT a.*, t.nama_tiket, e.nama_event, u.nama as customer
             FROM attendee a
             JOIN order_detail od ON a.id_detail = od.id_detail
             JOIN orders o ON od.id_order = o.id_order
             JOIN users u ON o.id_user = u.id_user
             JOIN tiket t ON od.id_tiket = t.id_tiket
             JOIN event e ON t.id_event = e.id_event
             WHERE a.kode_tiket = '$kode'"
        );
    }

    public function checkin(string $kode): bool {
        $kode = $this->escape($kode);
        $now  = date('Y-m-d H:i:s');
        return $this->execute(
            "UPDATE attendee SET status_checkin='sudah', waktu_checkin='$now' WHERE kode_tiket='$kode'"
        );
    }

    public function getRecentCheckins(int $limit = 10): array {
        return $this->fetchAll(
            "SELECT a.*, t.nama_tiket, e.nama_event, u.nama
             FROM attendee a
             JOIN order_detail od ON a.id_detail = od.id_detail
             JOIN orders o ON od.id_order = o.id_order
             JOIN users u ON o.id_user = u.id_user
             JOIN tiket t ON od.id_tiket = t.id_tiket
             JOIN event e ON t.id_event = e.id_event
             WHERE a.status_checkin = 'sudah'
             ORDER BY a.waktu_checkin DESC LIMIT $limit"
        );
    }

    public function getByDetail(int $id_detail): array {
        return $this->fetchAll("SELECT * FROM attendee WHERE id_detail = $id_detail");
    }

    public function getByUser(int $id_user): array {
        return $this->fetchAll(
            "SELECT a.kode_tiket, e.nama_event, e.tanggal, t.nama_tiket
             FROM attendee a
             JOIN order_detail od ON a.id_detail = od.id_detail
             JOIN orders o ON od.id_order = o.id_order
             JOIN tiket t ON od.id_tiket = t.id_tiket
             JOIN event e ON t.id_event = e.id_event
             WHERE o.id_user = $id_user
             ORDER BY o.tanggal_order DESC LIMIT 3"
        );
    }

    public function getFilteredAttendees(?int $id_event = null, ?string $status = null): array {
        $where = ["1=1"];
        if ($id_event) $where[] = "e.id_event = $id_event";
        if ($status)   $where[] = "a.status_checkin = '$status'";
        $where_str = implode(" AND ", $where);

        return $this->fetchAll(
            "SELECT a.*, t.nama_tiket, e.nama_event, u.nama as customer, v.nama_venue
             FROM attendee a
             JOIN order_detail od ON a.id_detail = od.id_detail
             JOIN orders o ON od.id_order = o.id_order
             JOIN users u ON o.id_user = u.id_user
             JOIN tiket t ON od.id_tiket = t.id_tiket
             JOIN event e ON t.id_event = e.id_event
             JOIN venue v ON e.id_venue = v.id_venue
             WHERE $where_str
             ORDER BY a.waktu_checkin DESC, a.kode_tiket ASC"
        );
    }
}
