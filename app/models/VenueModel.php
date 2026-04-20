<?php
require_once __DIR__ . '/BaseModel.php';

class VenueModel extends BaseModel {

    public function getAll(): array {
        return $this->fetchAll("SELECT * FROM venue ORDER BY id_venue DESC");
    }

    public function findById(int $id): ?array {
        return $this->fetchOne("SELECT * FROM venue WHERE id_venue = $id");
    }

    public function count(): int {
        $row = $this->fetchOne("SELECT COUNT(*) as t FROM venue");
        return (int)($row['t'] ?? 0);
    }

    public function create(string $nama, string $alamat, int $kapasitas): bool {
        $nama   = $this->escape($nama);
        $alamat = $this->escape($alamat);
        return $this->execute(
            "INSERT INTO venue (nama_venue, alamat, kapasitas) VALUES ('$nama','$alamat',$kapasitas)"
        );
    }

    public function update(int $id, string $nama, string $alamat, int $kapasitas): bool {
        $nama   = $this->escape($nama);
        $alamat = $this->escape($alamat);
        return $this->execute(
            "UPDATE venue SET nama_venue='$nama', alamat='$alamat', kapasitas=$kapasitas WHERE id_venue=$id"
        );
    }

    public function delete(int $id): bool {
        return $this->execute("DELETE FROM venue WHERE id_venue=$id");
    }
}
