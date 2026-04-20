<?php
require_once __DIR__ . '/BaseModel.php';

class VoucherModel extends BaseModel {

    public function getAll(): array {
        return $this->fetchAll("SELECT * FROM voucher ORDER BY id_voucher DESC");
    }

    public function findById(int $id): ?array {
        return $this->fetchOne("SELECT * FROM voucher WHERE id_voucher = $id");
    }

    public function create(string $kode, int $potongan, int $kuota, string $status): bool {
        $kode   = $this->escape(strtoupper(trim($kode)));
        $status = $this->escape($status);
        return $this->execute(
            "INSERT INTO voucher (kode_voucher, potongan, kuota, status) VALUES ('$kode',$potongan,$kuota,'$status')"
        );
    }

    public function update(int $id, string $kode, int $potongan, int $kuota, string $status): bool {
        $kode   = $this->escape(strtoupper(trim($kode)));
        $status = $this->escape($status);
        return $this->execute(
            "UPDATE voucher SET kode_voucher='$kode', potongan=$potongan, kuota=$kuota, status='$status' WHERE id_voucher=$id"
        );
    }

    public function delete(int $id): bool {
        return $this->execute("DELETE FROM voucher WHERE id_voucher=$id");
    }

    public function findByCode(string $code): ?array {
        $code = $this->escape(strtoupper(trim($code)));
        return $this->fetchOne("SELECT * FROM voucher WHERE kode_voucher = '$code' AND status = 'aktif' AND kuota > 0");
    }
}
