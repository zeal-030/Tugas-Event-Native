<?php
/**
 * Base Model — parent class untuk semua model
 * Menyediakan koneksi DB dan helper query umum
 */

abstract class BaseModel {
    protected mysqli $conn;

    public function __construct() {
        require_once __DIR__ . '/../config/database.php';
        $this->conn = getDbConnection();
    }

    /**
     * Jalankan query dan kembalikan semua baris sebagai array
     */
    protected function fetchAll(string $sql): array {
        $result = mysqli_query($this->conn, $sql);
        if (!$result) return [];
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Jalankan query dan kembalikan satu baris
     */
    protected function fetchOne(string $sql): ?array {
        $result = mysqli_query($this->conn, $sql);
        if (!$result || mysqli_num_rows($result) === 0) return null;
        return mysqli_fetch_assoc($result);
    }

    /**
     * Jalankan query INSERT/UPDATE/DELETE, kembalikan bool
     */
    protected function execute(string $sql): bool {
        return (bool) mysqli_query($this->conn, $sql);
    }

    /**
     * Escape string untuk mencegah SQL injection
     */
    protected function escape(string $value): string {
        return mysqli_real_escape_string($this->conn, $value);
    }
}
