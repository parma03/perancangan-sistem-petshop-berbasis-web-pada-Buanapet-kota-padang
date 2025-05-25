<?php
// controller/preview_data.php
session_start();
include '../../../db/koneksi.php';

header('Content-Type: application/json');

try {
    $jenis_laporan = $_POST['jenis_laporan'];
    $status_transaksi = $_POST['status_transaksi'];
    $date_range = $_POST['date_range'];
    $tanggal_mulai = $_POST['tanggal_mulai'] ?? null;
    $tanggal_selesai = $_POST['tanggal_selesai'] ?? null;

    $data = [];
    $total_nilai = 0;
    $total_transaksi = 0;

    // Build date condition
    $date_condition = "";
    if ($date_range === 'custom' && $tanggal_mulai && $tanggal_selesai) {
        $date_condition = " AND DATE(tgl_transaksi_cart) BETWEEN '$tanggal_mulai' AND '$tanggal_selesai'";
        $date_condition_service = " AND DATE(tgl_transaksi_service) BETWEEN '$tanggal_mulai' AND '$tanggal_selesai'";
    } else {
        $date_condition_service = "";
    }

    // Build status condition
    $status_condition = "";
    $status_condition_service = "";
    if ($status_transaksi !== 'all') {
        $status_condition = " AND status_transaksi_cart = '$status_transaksi'";
        $status_condition_service = " AND status_transaksi_service = '$status_transaksi'";
    }

    // Get cart transactions
    if ($jenis_laporan === 'cart' || $jenis_laporan === 'all') {
        $query_cart = "
            SELECT 
                tc.id_transaksi_cart as id,
                tc.tgl_transaksi_cart as tanggal,
                tc.status_transaksi_cart as status,
                'Cart' as jenis,
                (cb.jumlah * CAST(b.harga_barang AS DECIMAL)) as total
            FROM tb_transaksi_cart tc
            JOIN tb_cart cb ON tc.id_cart = cb.id_cart
            JOIN tb_barang b ON cb.id_barang = b.id_barang
            WHERE 1=1 $status_condition $date_condition
            ORDER BY tc.tgl_transaksi_cart DESC
        ";

        $result_cart = mysqli_query($conn, $query_cart);
        while ($row = mysqli_fetch_assoc($result_cart)) {
            $data[] = [
                'id' => 'TC-' . $row['id'],
                'tanggal' => date('d/m/Y', strtotime($row['tanggal'])),
                'jenis' => $row['jenis'],
                'status' => $row['status'],
                'total' => number_format($row['total'], 0, ',', '.')
            ];
            $total_nilai += $row['total'];
            $total_transaksi++;
        }
    }

    // Get service transactions
    if ($jenis_laporan === 'service' || $jenis_laporan === 'all') {
        $query_service = "
            SELECT 
                ts.id_transaksi_service as id,
                ts.tgl_transaksi_service as tanggal,
                ts.status_transaksi_service as status,
                'Service' as jenis,
                CAST(s.harga_service AS DECIMAL) as total
            FROM tb_transaksi_service ts
            JOIN tb_booking b ON ts.id_booking = b.id_booking
            JOIN tb_service s ON b.id_service = s.id_service
            WHERE 1=1 $status_condition_service $date_condition_service
            ORDER BY ts.tgl_transaksi_service DESC
        ";

        $result_service = mysqli_query($conn, $query_service);
        while ($row = mysqli_fetch_assoc($result_service)) {
            $data[] = [
                'id' => 'TS-' . $row['id'],
                'tanggal' => date('d/m/Y', strtotime($row['tanggal'])),
                'jenis' => $row['jenis'],
                'status' => $row['status'],
                'total' => number_format($row['total'], 0, ',', '.')
            ];
            $total_nilai += $row['total'];
            $total_transaksi++;
        }
    }

    // Sort by date
    usort($data, function ($a, $b) {
        return strtotime($b['tanggal']) - strtotime($a['tanggal']);
    });

    echo json_encode([
        'success' => true,
        'data' => $data,
        'summary' => [
            'total_transaksi' => $total_transaksi,
            'total_nilai' => number_format($total_nilai, 0, ',', '.')
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>