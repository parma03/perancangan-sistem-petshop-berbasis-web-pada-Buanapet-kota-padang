<?php
// indexController.php - Controller untuk halaman index
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

// Fungsi untuk mendapatkan 4 barang terbaru
function getNewArrivals($conn, $limit = 10)
{
    $sql = "SELECT * FROM tb_barang ORDER BY tanggal_dibuat DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Fungsi untuk mendapatkan 4 barang terlaris berdasarkan transaksi
function getBestSellers($conn, $limit = 10)
{
    $sql = "SELECT 
                b.*,
                COALESCE(COUNT(tc.id_transaksi_cart), 0) as total_terjual
            FROM tb_barang b
            LEFT JOIN tb_cart c ON b.id_barang = c.id_barang
            LEFT JOIN tb_transaksi_cart tc ON c.id_cart = tc.id_cart
            WHERE tc.status_transaksi_cart = 'completed' OR tc.status_transaksi_cart = 'success'
            GROUP BY b.id_barang, b.nama_barang, b.harga_barang, b.deskripsi_barang, b.foto_barang, b.tipe_barang, b.tanggal_dibuat
            HAVING total_terjual > 0
            ORDER BY total_terjual DESC, b.tanggal_dibuat DESC
            LIMIT ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);

    // Jika tidak ada produk yang terjual, ambil produk terbaru
    if (empty($products)) {
        $sql = "SELECT *, 0 as total_terjual FROM tb_barang ORDER BY tanggal_dibuat DESC LIMIT ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);
    }

    return $products;
}

// Fungsi untuk mendapatkan semua barang dengan filter
function getAllProducts($conn, $tipe_filter = '', $harga_min = 0, $harga_max = 999999999, $search = '', $sort_by = 'tanggal_dibuat', $sort_order = 'DESC')
{
    $sql = "SELECT 
                b.*,
                COALESCE(COUNT(tc.id_transaksi_cart), 0) as total_terjual
            FROM tb_barang b
            LEFT JOIN tb_cart c ON b.id_barang = c.id_barang
            LEFT JOIN tb_transaksi_cart tc ON c.id_cart = tc.id_cart AND (tc.status_transaksi_cart = 'completed' OR tc.status_transaksi_cart = 'success')
            WHERE 1=1";

    $params = [];
    $types = "";

    if (!empty($tipe_filter)) {
        $sql .= " AND b.tipe_barang = ?";
        $params[] = $tipe_filter;
        $types .= "s";
    }

    if ($harga_min > 0) {
        $sql .= " AND CAST(b.harga_barang AS UNSIGNED) >= ?";
        $params[] = $harga_min;
        $types .= "i";
    }

    if ($harga_max < 999999999) {
        $sql .= " AND CAST(b.harga_barang AS UNSIGNED) <= ?";
        $params[] = $harga_max;
        $types .= "i";
    }

    if (!empty($search)) {
        $sql .= " AND (b.nama_barang LIKE ? OR b.deskripsi_barang LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= "ss";
    }

    $sql .= " GROUP BY b.id_barang, b.nama_barang, b.harga_barang, b.deskripsi_barang, b.foto_barang, b.tipe_barang, b.tanggal_dibuat";

    // Sorting
    switch ($sort_by) {
        case 'harga_asc':
            $sql .= " ORDER BY CAST(b.harga_barang AS UNSIGNED) ASC";
            break;
        case 'harga_desc':
            $sql .= " ORDER BY CAST(b.harga_barang AS UNSIGNED) DESC";
            break;
        case 'nama':
            $sql .= " ORDER BY b.nama_barang ASC";
            break;
        case 'terlaris':
            $sql .= " ORDER BY total_terjual DESC, b.tanggal_dibuat DESC";
            break;
        default:
            $sql .= " ORDER BY b.tanggal_dibuat DESC";
    }

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Fungsi untuk mendapatkan tipe barang unik
function getProductTypes($conn)
{
    $sql = "SELECT DISTINCT tipe_barang FROM tb_barang WHERE tipe_barang IS NOT NULL AND tipe_barang != '' ORDER BY tipe_barang";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Fungsi untuk format harga
function formatPrice($price)
{
    return "Rp " . number_format($price, 0, ',', '.');
}

// Fungsi untuk mendapatkan detail produk berdasarkan ID
function getProductById($conn, $id)
{
    $sql = "SELECT 
                b.*,
                COALESCE(COUNT(tc.id_transaksi_cart), 0) as total_terjual
            FROM tb_barang b
            LEFT JOIN tb_cart c ON b.id_barang = c.id_barang
            LEFT JOIN tb_transaksi_cart tc ON c.id_cart = tc.id_cart AND (tc.status_transaksi_cart = 'completed' OR tc.status_transaksi_cart = 'success')
            WHERE b.id_barang = ?
            GROUP BY b.id_barang, b.nama_barang, b.harga_barang, b.deskripsi_barang, b.foto_barang, b.tipe_barang, b.tanggal_dibuat";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Fungsi untuk mendapatkan range harga
function getPriceRange($conn)
{
    $sql = "SELECT 
                MIN(CAST(harga_barang AS UNSIGNED)) as min_price,
                MAX(CAST(harga_barang AS UNSIGNED)) as max_price
            FROM tb_barang
            WHERE harga_barang IS NOT NULL AND harga_barang != ''";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Fungsi untuk mendapatkan statistik penjualan
function getSalesStats($conn)
{
    $sql = "SELECT 
                COUNT(DISTINCT tc.id_transaksi_cart) as total_transaksi,
                COUNT(DISTINCT b.id_barang) as produk_terjual,
                SUM(CAST(b.harga_barang AS UNSIGNED)) as total_pendapatan
            FROM tb_transaksi_cart tc
            JOIN tb_cart c ON tc.id_cart = c.id_cart
            JOIN tb_barang b ON c.id_barang = b.id_barang
            WHERE tc.status_transaksi_cart = 'completed' OR tc.status_transaksi_cart = 'success'";

    $result = $conn->query($sql);
    return $result->fetch_assoc();
}
?>