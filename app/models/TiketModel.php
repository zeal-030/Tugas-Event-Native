<?php
require_once __DIR__ . '/BaseModel.php';

class TiketModel extends BaseModel {

    public function getAll(): array {
        return $this->fetchAll(
            "SELECT t.*, e.nama_event FROM tiket t
             JOIN event e ON t.id_event = e.id_event
             ORDER BY t.id_tiket DESC"
        );
    }

    public function getByEvent(int $id_event): array {
        return $this->fetchAll("SELECT * FROM tiket WHERE id_event = $id_event");
    }

    public function findById(int $id): ?array {
        return $this->fetchOne("SELECT * FROM tiket WHERE id_tiket = $id");
    }

    public function getTotalKuota(int $id_event, int $exclude_id = 0): int {
        $sql = "SELECT SUM(kuota) as total FROM tiket WHERE id_event = $id_event";
        if ($exclude_id > 0) $sql .= " AND id_tiket != $exclude_id";
        $row = $this->fetchOne($sql);
        return (int)($row['total'] ?? 0);
    }

    public function count(): int {
        $row = $this->fetchOne("SELECT COUNT(*) as t FROM tiket");
        return (int)($row['t'] ?? 0);
    }

    public function create(int $id_event, string $nama, int $harga, int $kuota): bool {
        $nama = $this->escape($nama);
        return $this->execute(
            "INSERT INTO tiket (id_event, nama_tiket, harga, kuota) VALUES ($id_event,'$nama',$harga,$kuota)"
        );
    }

    public function update(int $id, int $id_event, string $nama, int $harga, int $kuota): bool {
        $nama = $this->escape($nama);
        return $this->execute(
            "UPDATE tiket SET id_event=$id_event, nama_tiket='$nama', harga=$harga, kuota=$kuota WHERE id_tiket=$id"
        );
    }

    public function delete(int $id): bool {
        return $this->execute("DELETE FROM tiket WHERE id_tiket=$id");
    }
}
