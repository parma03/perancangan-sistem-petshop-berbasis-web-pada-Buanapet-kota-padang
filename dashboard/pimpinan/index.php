<?php
session_start();
include '../../db/koneksi.php';
include 'controller/pimpinanController.php';

// Query untuk mendapatkan data statistik real dari database
try {
    // Total Pelanggan
    $query_pelanggan = "SELECT COUNT(*) as total_pelanggan FROM tb_pelanggan";
    $result_pelanggan = mysqli_query($conn, $query_pelanggan);
    $total_pelanggan = mysqli_fetch_assoc($result_pelanggan)['total_pelanggan'];

    // Total Produk Barang
    $query_barang = "SELECT COUNT(*) as total_barang FROM tb_barang";
    $result_barang = mysqli_query($conn, $query_barang);
    $total_barang = mysqli_fetch_assoc($result_barang)['total_barang'];

    // Total Service Booking
    $query_booking = "SELECT COUNT(*) as total_booking FROM tb_booking";
    $result_booking = mysqli_query($conn, $query_booking);
    $total_booking = mysqli_fetch_assoc($result_booking)['total_booking'];

    // Total Transaksi (Cart + Service)
    $query_transaksi_cart = "SELECT COUNT(*) as total_cart FROM tb_transaksi_cart";
    $query_transaksi_service = "SELECT COUNT(*) as total_service FROM tb_transaksi_service";
    $result_cart = mysqli_query($conn, $query_transaksi_cart);
    $result_service = mysqli_query($conn, $query_transaksi_service);
    $total_transaksi = mysqli_fetch_assoc($result_cart)['total_cart'] + mysqli_fetch_assoc($result_service)['total_service'];

    // Data untuk cart yang belum di-checkout (untuk perbaikan cart)
    $query_cart_active = "SELECT 
        c.id_cart,
        c.jumlah,
        b.nama_barang,
        b.harga_barang,
        p.nama as nama_pelanggan,
        u.username
    FROM tb_cart c
    JOIN tb_barang b ON c.id_barang = b.id_barang
    JOIN tb_pelanggan p ON c.id_user = p.id_user
    JOIN tb_user u ON c.id_user = u.id_user
    LEFT JOIN tb_transaksi_cart tc ON c.id_cart = tc.id_cart
    WHERE tc.id_cart IS NULL OR tc.status_transaksi_cart = 'pending'";
    $result_cart_active = mysqli_query($conn, $query_cart_active);

    // Transaksi pending untuk review
    $query_pending_cart = "SELECT COUNT(*) as pending_cart FROM tb_transaksi_cart WHERE status_transaksi_cart = 'pending'";
    $query_pending_service = "SELECT COUNT(*) as pending_service FROM tb_transaksi_service WHERE status_transaksi_service = 'pending'";
    $result_pending_cart = mysqli_query($conn, $query_pending_cart);
    $result_pending_service = mysqli_query($conn, $query_pending_service);
    $total_pending = mysqli_fetch_assoc($result_pending_cart)['pending_cart'] + mysqli_fetch_assoc($result_pending_service)['pending_service'];

} catch (Exception $e) {
    // Default values jika ada error
    $total_pelanggan = 0;
    $total_barang = 0;
    $total_booking = 0;
    $total_transaksi = 0;
    $total_pending = 0;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pimpinan Dashboard Panel - Buana Pet Shop</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.32/sweetalert2.min.css"
        rel="stylesheet">
    <link rel="stylesheet" href="assets/index.css">
</head>

<body>
    <!-- Sidebar -->
    <?php include '_components/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Navbar -->
        <?php include '_components/navbar.php'; ?>

        <!-- Content -->
        <div class="content-wrapper">
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="fw-bold text-dark">Dashboard Overview</h2>
                    <p class="text-muted">Selamat datang di pimpinan panel Buana Pet Shop</p>
                </div>
            </div>

            <!-- Dashboard Stats -->
            <div class="row g-4 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card dashboard-card h-100">
                        <div class="card-body">
                            <div class="card-icon users">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3 class="fw-bold mb-1"><?php echo number_format($total_pelanggan); ?></h3>
                            <p class="text-muted mb-0">Total Pelanggan</p>
                            <small class="text-success"><i class="fas fa-arrow-up me-1"></i>Pelanggan terdaftar</small>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card dashboard-card h-100">
                        <div class="card-body">
                            <div class="card-icon products">
                                <i class="fas fa-box"></i>
                            </div>
                            <h3 class="fw-bold mb-1"><?php echo number_format($total_barang); ?></h3>
                            <p class="text-muted mb-0">Produk Barang</p>
                            <small class="text-info"><i class="fas fa-arrow-right me-1"></i>Produk tersedia</small>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card dashboard-card h-100">
                        <div class="card-body">
                            <div class="card-icon services">
                                <i class="fas fa-cut"></i>
                            </div>
                            <h3 class="fw-bold mb-1"><?php echo number_format($total_booking); ?></h3>
                            <p class="text-muted mb-0">Service Booking</p>
                            <small class="text-warning"><i class="fas fa-calendar me-1"></i>Booking terjadwal</small>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card dashboard-card h-100">
                        <div class="card-body">
                            <div class="card-icon transactions">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <h3 class="fw-bold mb-1"><?php echo number_format($total_transaksi); ?></h3>
                            <p class="text-muted mb-0">Total Transaksi</p>
                            <small class="text-success"><i class="fas fa-arrow-up me-1"></i>Semua transaksi</small>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($total_pending > 0): ?>
                <!-- Alert untuk transaksi pending -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <div>
                                <strong>Perhatian!</strong> Ada <?php echo $total_pending; ?> transaksi yang menunggu
                                konfirmasi.
                                <a href="transaksi-pending.php" class="alert-link">Lihat detail</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>


            <!-- Quick Actions -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-plus-circle fa-3x text-primary mb-3"></i>
                            <h5>Tambah Produk</h5>
                            <p class="text-muted">Menambah produk barang baru</p>
                            <a href="barang.php" class="btn btn-primary">Tambah Produk</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-cogs fa-3x text-success mb-3"></i>
                            <h5>Kelola Service</h5>
                            <p class="text-muted">Mengelola layanan dan jadwal</p>
                            <a href="services.php" class="btn btn-success">Kelola Service</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-chart-bar fa-3x text-warning mb-3"></i>
                            <h5>Laporan</h5>
                            <p class="text-muted">Lihat laporan transaksi</p>
                            <a href="laporan.php" class="btn btn-warning">Lihat Laporan</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.32/sweetalert2.min.js"></script>

    <!-- Custom Scripts -->
    <script src="assets/index.js"></script>
    <script src="controller/pimpinanController.js"></script>

    <script>
        // Tampilkan welcome toast saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function () {
            // Simulasi welcome message
            setTimeout(() => {
                showToast('Selamat datang di Pimpinan Dashboard!', 'success');
            }, 1000);
        });
        // Function untuk refresh data secara real-time
        function refreshDashboard() {
            location.reload();
        }

        // Auto refresh setiap 5 menit
        setInterval(refreshDashboard, 300000);
    </script>
</body>

</html>