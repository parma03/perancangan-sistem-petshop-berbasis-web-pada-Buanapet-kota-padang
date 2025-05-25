<?php
// controller/pesananController.php
session_start();
include '../db/koneksi.php';

// ===== ROLE-BASED ACCESS CONTROL =====
// Cek jika user sudah login dan memiliki role admin atau pimpinan
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    $user_role = $_SESSION['role'];

    // Redirect admin dan pimpinan ke dashboard mereka
    if ($user_role === 'admin') {
        header('Location: dashboard/admin/index.php');
        exit();
    } elseif ($user_role === 'pimpinan') {
        header('Location: dashboard/pimpinan/index.php');
        exit();
    }
    // Role 'pelanggan' tetap bisa mengakses halaman index
}


header('Content-Type: application/json');

// Debug: Log session information
error_log("Session ID: " . session_id());
error_log("User ID in session: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set'));
error_log("All session data: " . print_r($_SESSION, true));

if (!isset($_SESSION['user_id'])) {
    error_log("User not logged in - session check failed");
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu', 'debug_session' => $_SESSION]);
    exit;
}

$action = $_POST['action'] ?? '';
$userId = $_SESSION['user_id'];

error_log("Action: $action, User ID: $userId");

switch ($action) {
    case 'get_orders':
        getOrders($conn, $userId);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Action tidak valid']);
        break;
}

function getOrders($conn, $userId)
{
    try {
        error_log("Getting orders for user ID: $userId");

        // Get product orders (cart transactions)
        $produkOrders = getProdukOrders($conn, $userId);

        // Get service orders
        $serviceOrders = getServiceOrders($conn, $userId);

        echo json_encode([
            'success' => true,
            'data' => [
                'produk' => $produkOrders,
                'service' => $serviceOrders
            ]
        ]);

    } catch (Exception $e) {
        error_log("Error in getOrders: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
    }
}

function getProdukOrders($conn, $userId)
{
    $query = "
        SELECT 
            tc.id_transaksi_cart,
            tc.status_transaksi_cart,
            tc.bukti_transaksi_cart,
            c.id_cart,
            c.jumlah,
            b.nama_barang,
            b.foto_barang,
            b.harga_barang,
            b.tipe_barang,
            (c.jumlah * CAST(b.harga_barang AS UNSIGNED)) as subtotal
        FROM tb_transaksi_cart tc
        JOIN tb_cart c ON tc.id_cart = c.id_cart
        JOIN tb_barang b ON c.id_barang = b.id_barang
        WHERE c.id_user = ?
        ORDER BY tc.id_transaksi_cart DESC
    ";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed for getProdukOrders: " . $conn->error);
        throw new Exception("Database preparation error");
    }

    $stmt->bind_param("i", $userId);
    if (!$stmt->execute()) {
        error_log("Execute failed for getProdukOrders: " . $stmt->error);
        throw new Exception("Database execution error");
    }

    $result = $stmt->get_result();

    $orders = [];
    $currentOrder = null;
    $currentOrderId = null;

    while ($row = $result->fetch_assoc()) {
        if ($currentOrderId !== $row['id_transaksi_cart']) {
            // Save previous order if exists
            if ($currentOrder !== null) {
                $orders[] = $currentOrder;
            }

            // Start new order
            $currentOrder = [
                'id_transaksi' => $row['id_transaksi_cart'],
                'status' => $row['status_transaksi_cart'],
                'bukti_pembayaran' => $row['bukti_transaksi_cart'],
                'total' => 0,
                'items' => []
            ];
            $currentOrderId = $row['id_transaksi_cart'];
        }

        // Add item to current order
        $currentOrder['items'][] = [
            'nama' => $row['nama_barang'],
            'foto' => $row['foto_barang'],
            'tipe' => $row['tipe_barang'],
            'harga' => $row['harga_barang'],
            'jumlah' => $row['jumlah'],
            'subtotal' => $row['subtotal']
        ];

        $currentOrder['total'] += $row['subtotal'];
    }

    // Add last order if exists
    if ($currentOrder !== null) {
        $orders[] = $currentOrder;
    }

    // Separate by status
    $pending = array_filter($orders, function ($order) {
        return $order['status'] === 'pending';
    });

    $completed = array_filter($orders, function ($order) {
        return $order['status'] === 'completed';
    });

    return [
        'pending' => array_values($pending),
        'completed' => array_values($completed)
    ];
}

function getServiceOrders($conn, $userId)
{
    $query = "
        SELECT 
            ts.id_transaksi_service,
            ts.status_transaksi_service,
            ts.bukti_transaksi_service,
            b.id_booking,
            b.waktu_booking,
            s.nama_service,
            s.deskripsi_service,
            s.harga_service
        FROM tb_transaksi_service ts
        JOIN tb_booking b ON ts.id_booking = b.id_booking
        JOIN tb_service s ON b.id_service = s.id_service
        WHERE b.id_user = ?
        ORDER BY ts.id_transaksi_service DESC
    ";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed for getServiceOrders: " . $conn->error);
        throw new Exception("Database preparation error");
    }

    $stmt->bind_param("i", $userId);
    if (!$stmt->execute()) {
        error_log("Execute failed for getServiceOrders: " . $stmt->error);
        throw new Exception("Database execution error");
    }

    $result = $stmt->get_result();

    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = [
            'id_transaksi' => $row['id_transaksi_service'],
            'id_booking' => $row['id_booking'],
            'status' => $row['status_transaksi_service'],
            'bukti_pembayaran' => $row['bukti_transaksi_service'],
            'nama_service' => $row['nama_service'],
            'deskripsi_service' => $row['deskripsi_service'],
            'harga_service' => $row['harga_service'],
            'waktu_booking' => $row['waktu_booking'],
            'tanggal_booking' => date('d/m/Y H:i', strtotime($row['waktu_booking']))
        ];
    }

    // Separate by status
    $pending = array_filter($orders, function ($order) {
        return $order['status'] === 'pending';
    });

    $completed = array_filter($orders, function ($order) {
        return $order['status'] === 'completed';
    });

    return [
        'pending' => array_values($pending),
        'completed' => array_values($completed)
    ];
}
?>