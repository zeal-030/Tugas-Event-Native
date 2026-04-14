<?php
require_once '../../config/db.php';
header('Content-Type: application/json');

if (isset($_GET['code'])) {
    $code = strtoupper(trim(mysqli_real_escape_string($conn, $_GET['code'])));
    $result = mysqli_query($conn, "SELECT * FROM voucher WHERE UPPER(kode_voucher) = '$code' AND status = 'aktif' AND kuota > 0");

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        echo json_encode([
            'status' => 'success',
            'potongan' => (int)$row['potongan']
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Voucher tidak valid']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No code provided']);
}
