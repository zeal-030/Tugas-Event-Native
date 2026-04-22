<?php
session_start();
require_once '../bootstrap.php';
$conn = getDbConnection();

// Global query helper
if (!function_exists('query')) {
    function query($q) {
        global $conn;
        $res = mysqli_query($conn, $q);
        $rows = [];
        if ($res && !is_bool($res)) {
            while ($row = mysqli_fetch_assoc($res)) $rows[] = $row;
        }
        return $rows;
    }
}
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') { exit; }

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Penjualan_".date('Ymd').".xls");

$orders = query("SELECT o.id_order, u.nama, o.tanggal_order, o.total, o.status 
                 FROM orders o JOIN users u ON o.id_user=u.id_user 
                 ORDER BY o.tanggal_order DESC");

echo "Order ID\tCustomer\tDate\tTotal\tStatus\n";
foreach($orders as $o) {
    echo "#ORD-{$o['id_order']}\t{$o['nama']}\t{$o['tanggal_order']}\t{$o['total']}\t{$o['status']}\n";
}
exit;
