<?php
// controller/get_statistics.php
session_start();
include '../../../db/koneksi.php';

header('Content-Type: application/json');

try {
    // Get cart transactions count
    $query_cart = "SELECT COUNT(*) as total FROM tb_transaksi_cart";
    $result_cart = mysqli_query($conn, $query_cart);
    $total_cart = mysqli_fetch_assoc($result_cart)['total'];

    // Get service transactions count
    $query_service = "SELECT COUNT(*) as total FROM tb_transaksi_service";
    $result_service = mysqli_query($conn, $query_service);
    $total_service = mysqli_fetch_assoc($result_service)['total'];

    echo json_encode([
        'success' => true,
        'total_cart' => $total_cart,
        'total_service' => $total_service
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>