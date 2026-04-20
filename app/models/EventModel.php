<?php
require_once __DIR__ . '/BaseModel.php';

class EventModel extends BaseModel {

    public function getAll(string $search = '', int $limit = 10, int $offset = 0): array {
        $search = $this->escape($search);
        return $this->fetchAll(
            "SELECT e.*, v.nama_venue FROM event e
             JOIN venue v ON e.id_venue = v.id_venue
             WHERE e.nama_event LIKE '%$search%'
             ORDER BY tanggal DESC LIMIT $offset, $limit"
        );
    }

    public function count(string $search = ''): int {
        $search = $this->escape($search);
        $row    = $this->fetchOne("SELECT COUNT(*) as t FROM event WHERE nama_event LIKE '%$search%'");
        return (int)($row['t'] ?? 0);
    }

    public function countTotal(): int {
        $row = $this->fetchOne("SELECT COUNT(*) as t FROM event");
        return (int)($row['t'] ?? 0);
    }

    public function countUpcoming(): int {
        $row = $this->fetchOne("SELECT COUNT(*) as t FROM event WHERE tanggal >= CURDATE()");
        return (int)($row['t'] ?? 0);
    }

    public function findById(int $id): ?array {
        return $this->fetchOne(
            "SELECT e.*, v.nama_venue, v.alamat FROM event e
             JOIN venue v ON e.id_venue = v.id_venue
             WHERE e.id_event = $id"
        );
    }

    public function getBrowseEvents(): array {
        return $this->fetchAll(
            "SELECT e.*, v.nama_venue, 
             (SELECT MIN(harga) FROM tiket WHERE id_event = e.id_event) as min_price, 
             (SELECT COUNT(*) FROM tiket WHERE id_event = e.id_event) as tiket_types 
             FROM event e JOIN venue v ON e.id_venue = v.id_venue 
             ORDER BY e.tanggal DESC"
        );
    }

    public function getPublished(int $limit = 8): array {
        return $this->fetchAll(
            "SELECT e.*, v.nama_venue FROM event e
             JOIN venue v ON e.id_venue = v.id_venue
             ORDER BY tanggal DESC LIMIT $limit"
        );
    }

    public function getRecent(int $limit = 4): array {
        return $this->fetchAll(
            "SELECT e.*, v.nama_venue FROM event e
             JOIN venue v ON e.id_venue = v.id_venue
             ORDER BY tanggal DESC LIMIT $limit"
        );
    }

    public function create(string $nama, string $desc, string $tanggal, int $id_venue, string $gambar): bool {
        $nama    = $this->escape($nama);
        $desc    = $this->escape($desc);
        $tanggal = $this->escape($tanggal);
        $gambar  = $this->escape($gambar);
        return $this->execute(
            "INSERT INTO event (nama_event, deskripsi, tanggal, id_venue, gambar)
             VALUES ('$nama','$desc','$tanggal',$id_venue,'$gambar')"
        );
    }

    public function update(int $id, string $nama, string $desc, string $tanggal, int $id_venue, ?string $gambar = null): bool {
        $nama    = $this->escape($nama);
        $desc    = $this->escape($desc);
        $tanggal = $this->escape($tanggal);
        $sql     = "UPDATE event SET nama_event='$nama', deskripsi='$desc', tanggal='$tanggal', id_venue=$id_venue";
        if ($gambar !== null) {
            $gambar = $this->escape($gambar);
            $sql   .= ", gambar='$gambar'";
        }
        return $this->execute("$sql WHERE id_event=$id");
    }

    public function delete(int $id): bool {
        return $this->execute("DELETE FROM event WHERE id_event=$id");
    }

    public function getGambar(int $id): ?string {
        $row = $this->fetchOne("SELECT gambar FROM event WHERE id_event=$id");
        return $row['gambar'] ?? null;
    }
}
