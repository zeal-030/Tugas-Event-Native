<?php
/**
 * Koneksi Database
 * Mengembalikan koneksi mysqli yang sudah dibuat (singleton sederhana)
 */

function getDbConnection(): mysqli {
    static $conn = null;

    if ($conn === null) {
        $host = 'localhost';
        $user = 'root';
        $pass = '';
        $db   = 'event_tiket';

        $conn = mysqli_connect($host, $user, $pass, $db);

        if (!$conn) {
            http_response_code(500);
            die(json_encode(['error' => 'Koneksi database gagal: ' . mysqli_connect_error()]));
        }

        mysqli_set_charset($conn, 'utf8mb4');
    }

    return $conn;
}
