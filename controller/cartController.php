<?php
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add_to_cart':
            addToCart($conn);
            break;
        case 'get_cart_count':
            getCartCount($conn);
            break;
        case 'get_cart_items':
            getCartItems($conn);
            break;
        case 'update_quantity':
            updateQuantity($conn);
            break;
        case 'remove_from_cart':
            removeFromCart($conn);
            break;
        case 'clear_cart':
            clearCart($conn);
            break;
        case 'submit_payment':
            submitPayment($conn);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

function addToCart($conn)
{
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Anda harus login terlebih dahulu untuk menambahkan produk ke keranjang',
            'login_required' => true
        ]);
        return;
    }

    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'] ?? '';

    if (empty($product_id)) {
        echo json_encode(['success' => false, 'message' => 'ID produk tidak valid']);
        return;
    }

    // Check if product exists
    $check_product = "SELECT id_barang, nama_barang FROM tb_barang WHERE id_barang = ?";
    $stmt = $conn->prepare($check_product);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan']);
        return;
    }

    $product = $result->fetch_assoc();

    // Check if product already in cart
    $check_cart = "SELECT c.id_cart, c.jumlah FROM tb_cart c 
                   LEFT JOIN tb_transaksi_cart tc ON c.id_cart = tc.id_cart
                   WHERE c.id_user = ? AND c.id_barang = ? AND tc.id_cart IS NULL";
    $stmt = $conn->prepare($check_cart);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $cart_result = $stmt->get_result();

    if ($cart_result->num_rows > 0) {
        // Product already exists, increase quantity
        $cart_item = $cart_result->fetch_assoc();
        $new_quantity = $cart_item['jumlah'] + 1;

        $update_cart = "UPDATE tb_cart SET jumlah = ? WHERE id_cart = ?";
        $stmt = $conn->prepare($update_cart);
        $stmt->bind_param("ii", $new_quantity, $cart_item['id_cart']);

        if ($stmt->execute()) {
            // Get updated cart count
            $cart_count = getTotalCartItems($conn, $user_id);

            echo json_encode([
                'success' => true,
                'message' => $product['nama_barang'] . ' berhasil ditambahkan ke keranjang (Jumlah: ' . $new_quantity . ')',
                'cart_count' => $cart_count
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menambahkan produk ke keranjang']);
        }
    } else {
        // Add new product to cart
        $insert_cart = "INSERT INTO tb_cart (id_user, id_barang, jumlah) VALUES (?, ?, 1)";
        $stmt = $conn->prepare($insert_cart);
        $stmt->bind_param("ii", $user_id, $product_id);

        if ($stmt->execute()) {
            // Get updated cart count
            $cart_count = getTotalCartItems($conn, $user_id);

            echo json_encode([
                'success' => true,
                'message' => $product['nama_barang'] . ' berhasil ditambahkan ke keranjang',
                'cart_count' => $cart_count
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menambahkan produk ke keranjang']);
        }
    }
}

function getCartCount($conn)
{
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => true, 'cart_count' => 0]);
        return;
    }

    $user_id = $_SESSION['user_id'];
    $cart_count = getTotalCartItems($conn, $user_id);
    echo json_encode(['success' => true, 'cart_count' => $cart_count]);
}

function getCartItems($conn)
{
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }

    $user_id = $_SESSION['user_id'];

    // Modified query to exclude items that already have transactions
    $query = "SELECT c.id_cart, c.jumlah, b.id_barang, b.nama_barang, b.harga_barang, b.foto_barang 
              FROM tb_cart c 
              JOIN tb_barang b ON c.id_barang = b.id_barang 
              LEFT JOIN tb_transaksi_cart tc ON c.id_cart = tc.id_cart
              WHERE c.id_user = ? AND tc.id_cart IS NULL
              ORDER BY c.id_cart DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $cart_items = [];
    $total_items = 0;
    $total_price = 0;

    while ($row = $result->fetch_assoc()) {
        $cart_items[] = $row;
        $total_items += $row['jumlah'];
        $total_price += ($row['harga_barang'] * $row['jumlah']);
    }

    $summary = [
        'total_items' => $total_items,
        'total_price' => $total_price,
        'item_count' => count($cart_items)
    ];

    echo json_encode([
        'success' => true,
        'cart_items' => $cart_items,
        'summary' => $summary
    ]);
}

function updateQuantity($conn)
{
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }

    $user_id = $_SESSION['user_id'];
    $cart_id = $_POST['cart_id'] ?? '';
    $quantity = intval($_POST['quantity'] ?? 0);

    if (empty($cart_id) || $quantity < 1) {
        echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
        return;
    }

    // Verify cart item belongs to user and not in transaction
    $verify_query = "SELECT c.id_cart FROM tb_cart c 
                     LEFT JOIN tb_transaksi_cart tc ON c.id_cart = tc.id_cart
                     WHERE c.id_cart = ? AND c.id_user = ? AND tc.id_cart IS NULL";
    $stmt = $conn->prepare($verify_query);
    $stmt->bind_param("ii", $cart_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Item tidak ditemukan atau sudah dalam transaksi']);
        return;
    }

    // Update quantity
    $update_query = "UPDATE tb_cart SET jumlah = ? WHERE id_cart = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ii", $quantity, $cart_id);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Jumlah item berhasil diperbarui'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui jumlah item']);
    }
}

function removeFromCart($conn)
{
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }

    $user_id = $_SESSION['user_id'];
    $cart_id = $_POST['cart_id'] ?? '';

    if (empty($cart_id)) {
        echo json_encode(['success' => false, 'message' => 'ID cart tidak valid']);
        return;
    }

    // Verify cart item belongs to user and get product name, also check if not in transaction
    $verify_query = "SELECT c.id_cart, b.nama_barang 
                     FROM tb_cart c 
                     JOIN tb_barang b ON c.id_barang = b.id_barang 
                     LEFT JOIN tb_transaksi_cart tc ON c.id_cart = tc.id_cart
                     WHERE c.id_cart = ? AND c.id_user = ? AND tc.id_cart IS NULL";
    $stmt = $conn->prepare($verify_query);
    $stmt->bind_param("ii", $cart_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Item tidak ditemukan atau sudah dalam transaksi']);
        return;
    }

    $item = $result->fetch_assoc();

    $delete_query = "DELETE FROM tb_cart WHERE id_cart = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $cart_id);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => $item['nama_barang'] . ' berhasil dihapus dari keranjang'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus produk dari keranjang']);
    }
}

function clearCart($conn)
{
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }

    $user_id = $_SESSION['user_id'];

    // Only delete cart items that are not in transaction
    $delete_query = "DELETE c FROM tb_cart c 
                     LEFT JOIN tb_transaksi_cart tc ON c.id_cart = tc.id_cart
                     WHERE c.id_user = ? AND tc.id_cart IS NULL";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Keranjang berhasil dikosongkan'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengosongkan keranjang']);
    }
}

function submitPayment($conn)
{
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }

    $user_id = $_SESSION['user_id'];

    // Validate file upload
    if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Gagal mengupload bukti pembayaran']);
        return;
    }

    $file = $_FILES['payment_proof'];

    // Validate file type
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $file_type = mime_content_type($file['tmp_name']);

    if (!in_array($file_type, $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau GIF.']);
        return;
    }

    // Validate file size (5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 5MB.']);
        return;
    }

    // Get all cart items that don't have transactions yet
    $cart_query = "SELECT c.id_cart FROM tb_cart c 
                   LEFT JOIN tb_transaksi_cart tc ON c.id_cart = tc.id_cart
                   WHERE c.id_user = ? AND tc.id_cart IS NULL";
    $stmt = $conn->prepare($cart_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cart_result = $stmt->get_result();

    if ($cart_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Tidak ada item di keranjang untuk diproses']);
        return;
    }

    // Create upload directory if not exists
    $upload_dir = '../assets/uploads/payment_proofs/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generate unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'payment_' . $user_id . '_' . time() . '_' . uniqid() . '.' . $file_extension;
    $file_path = $upload_dir . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan bukti pembayaran']);
        return;
    }

    // Start transaction
    $conn->autocommit(false);

    try {
        // Insert transaction records for each cart item
        $insert_transaction = "INSERT INTO tb_transaksi_cart (id_cart, status_transaksi_cart, bukti_transaksi_cart, tgl_transaksi_cart) VALUES (?, 'pending', ?, NOW())";
        $stmt = $conn->prepare($insert_transaction);

        $success_count = 0;

        // Reset result pointer
        $cart_result->data_seek(0);

        while ($cart_row = $cart_result->fetch_assoc()) {
            $stmt->bind_param("is", $cart_row['id_cart'], $filename);
            if ($stmt->execute()) {
                $success_count++;
            }
        }

        if ($success_count > 0) {
            $conn->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Bukti pembayaran berhasil disubmit! Tim kami akan segera memverifikasi pembayaran Anda.',
                'processed_items' => $success_count
            ]);
        } else {
            $conn->rollback();
            // Delete uploaded file if transaction failed
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            echo json_encode(['success' => false, 'message' => 'Gagal memproses transaksi']);
        }

    } catch (Exception $e) {
        $conn->rollback();
        // Delete uploaded file if transaction failed
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
    }

    $conn->autocommit(true);
}

// Helper function to get total cart items (excluding items in transaction)
function getTotalCartItems($conn, $user_id)
{
    $count_query = "SELECT SUM(c.jumlah) as total FROM tb_cart c 
                    LEFT JOIN tb_transaksi_cart tc ON c.id_cart = tc.id_cart
                    WHERE c.id_user = ? AND tc.id_cart IS NULL";
    $stmt = $conn->prepare($count_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}
?>