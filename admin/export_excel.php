<?php
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

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        .table-report {
            border-collapse: collapse;
            width: 100%;
            font-family: Arial, sans-serif;
        }
        .table-report th, .table-report td {
            border: 1px solid #000000;
            padding: 8px;
        }
        .header-title {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            background-color: #2c3e50;
            color: #ffffff;
            padding: 15px;
        }
        .th-header {
            background-color: #34495e;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .status-success {
            color: #27ae60;
            font-weight: bold;
        }
        .status-pending {
            color: #f39c12;
            font-weight: bold;
        }
        .status-failed {
            color: #e74c3c;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <table class="table-report">
        <tr>
            <th colspan="5" class="header-title">LAPORAN PENJUALAN TIKET - EVENT-KU</th>
        </tr>
        <tr>
            <td colspan="5"><b>Tanggal Cetak:</b> <?php echo date('d M Y H:i'); ?></td>
        </tr>
        <tr>
            <td colspan="5"></td> <!-- Empty row for spacing -->
        </tr>
        <tr>
            <th class="th-header">Order ID</th>
            <th class="th-header">Customer</th>
            <th class="th-header">Date</th>
            <th class="th-header">Total</th>
            <th class="th-header">Status</th>
        </tr>
        <?php 
        $totalPendapatan = 0;
        foreach($orders as $o): 
            $status = strtolower($o['status']);
            $statusClass = '';
            if ($status === 'success' || $status === 'paid' || $status === 'settlement') {
                $statusClass = 'status-success';
                $totalPendapatan += $o['total'];
            } elseif ($status === 'pending') {
                $statusClass = 'status-pending';
            } else {
                $statusClass = 'status-failed';
            }
        ?>
            <tr>
                <td class="text-center">#ORD-<?php echo $o['id_order']; ?></td>
                <td><?php echo htmlspecialchars($o['nama']); ?></td>
                <td class="text-center"><?php echo date('d/m/Y H:i', strtotime($o['tanggal_order'])); ?></td>
                <td class="text-right">Rp <?php echo number_format($o['total'], 0, ',', '.'); ?></td>
                <td class="text-center <?php echo $statusClass; ?>"><?php echo strtoupper($o['status']); ?></td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <td colspan="3" class="text-right"><b>Total Pendapatan (Paid/Success):</b></td>
            <td class="text-right"><b>Rp <?php echo number_format($totalPendapatan, 0, ',', '.'); ?></b></td>
            <td></td>
        </tr>
    </table>
</body>
</html>
<?php exit;
