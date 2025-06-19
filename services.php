<?php
session_start();
include 'db/koneksi.php';
// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'book_service':
                handleBookingService($conn);
                break;
            case 'upload_payment':
                handlePaymentUpload($conn);
                break;
        }
    }
}

// Function to handle service booking
function handleBookingService($conn) {
    // Set header JSON untuk semua response
    header('Content-Type: application/json');
    
    // Enhanced login check dengan konsistensi JSON response
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => false,
            'login_required' => true,
            'message' => 'Silakan login terlebih dahulu untuk melakukan booking!'
        ]);
        exit;
    }
    
    try {
        $id_service = $_POST['id_service'] ?? null;
        $id_user = $_SESSION['user_id'];
        $booking_date = $_POST['booking_date'] ?? null;
        $booking_time = $_POST['booking_time'] ?? null;
        
        // Validasi input
        if (!$id_service || !$booking_date || !$booking_time) {
            echo json_encode([
                'success' => false,
                'message' => 'Data booking tidak lengkap!'
            ]);
            exit;
        }
        
        $waktu_booking = $booking_date . ' ' . $booking_time;
        
        // Check if the selected time is within service schedule
        $day_name = date('l', strtotime($booking_date));
        $day_indonesian = translateDayToIndonesian($day_name);
        
        $check_schedule = "SELECT * FROM tb_jadwal WHERE id_service = ? AND hari = ? AND ? BETWEEN duty_start AND duty_end";
        $stmt = $conn->prepare($check_schedule);
        
        if (!$stmt) {
            throw new Exception('Database preparation error: ' . $conn->error);
        }
        
        $stmt->bind_param("iss", $id_service, $day_indonesian, $booking_time);
        $stmt->execute();
        $schedule_result = $stmt->get_result();
        
        if ($schedule_result->num_rows == 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Waktu yang dipilih tidak tersedia untuk layanan ini!'
            ]);
            exit;
        }
        
        // Start transaction untuk memastikan atomicity
        $conn->begin_transaction();
        
        try {
            // Hapus booking yang sudah expired (lebih dari 30 menit tanpa pembayaran)
            $cleanup_expired = "DELETE b FROM tb_booking b 
                               LEFT JOIN tb_transaksi_service ts ON b.id_booking = ts.id_booking 
                               WHERE ts.id_booking IS NULL 
                               AND b.created_at < DATE_SUB(NOW(), INTERVAL 30 MINUTE)";
            $conn->query($cleanup_expired);
            
            // Check for existing bookings at the same time (termasuk yang belum bayar)
            $check_booking = "SELECT b.*, ts.id_transaksi_service, ts.status_transaksi_service 
                             FROM tb_booking b 
                             LEFT JOIN tb_transaksi_service ts ON b.id_booking = ts.id_booking 
                             WHERE b.id_service = ? AND b.waktu_booking = ?";
            $stmt = $conn->prepare($check_booking);
            
            if (!$stmt) {
                throw new Exception('Database preparation error: ' . $conn->error);
            }
            
            $stmt->bind_param("is", $id_service, $waktu_booking);
            $stmt->execute();
            $booking_result = $stmt->get_result();
            
            if ($booking_result->num_rows > 0) {
                $existing_booking = $booking_result->fetch_assoc();
                
                // Jika ada booking yang sudah dibayar atau sedang pending
                if ($existing_booking['id_transaksi_service'] !== null) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Slot waktu tersebut sudah dibooking oleh pengguna lain!'
                    ]);
                    $conn->rollback();
                    exit;
                }
                
                // Jika ada booking yang belum bayar, cek apakah itu milik user yang sama
                if ($existing_booking['id_user'] == $id_user) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Anda sudah memiliki booking pada waktu tersebut. Silakan lakukan pembayaran atau pilih waktu lain!'
                    ]);
                    $conn->rollback();
                    exit;
                } else {
                    // Jika booking milik user lain dan belum bayar, hapus booking lama
                    $delete_old_booking = "DELETE FROM tb_booking WHERE id_booking = ?";
                    $delete_stmt = $conn->prepare($delete_old_booking);
                    $delete_stmt->bind_param("i", $existing_booking['id_booking']);
                    $delete_stmt->execute();
                }
            }
            
            // Insert booking baru dengan timestamp
            $insert_booking = "INSERT INTO tb_booking (id_service, id_user, waktu_booking, created_at) VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($insert_booking);
            
            if (!$stmt) {
                throw new Exception('Database preparation error: ' . $conn->error);
            }
            
            $stmt->bind_param("iis", $id_service, $id_user, $waktu_booking);
            
            if ($stmt->execute()) {
                $conn->commit();
                echo json_encode([
                    'success' => true,
                    'message' => 'Booking berhasil! Silakan lakukan pembayaran dalam 30 menit.',
                    'booking_id' => $conn->insert_id
                ]);
            } else {
                throw new Exception('Gagal menyimpan booking: ' . $stmt->error);
            }
            
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
        
    } catch (Exception $e) {
        error_log('Booking Error: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ]);
    }
    
    exit; // Penting: keluar setelah mengirim response
}


// Function to handle payment upload with conflict resolution
function handlePaymentUpload($conn) {
    // Set header JSON jika ini adalah AJAX request
    if (isset($_POST['ajax']) || isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
    }
    
    if (!isset($_SESSION['user_id'])) {
        if (isset($_POST['ajax']) || isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode([
                'success' => false,
                'login_required' => true,
                'message' => 'Silakan login terlebih dahulu!'
            ]);
            exit;
        } else {
            echo "<script>alert('Silakan login terlebih dahulu!');</script>";
            return;
        }
    }
    
    try {
        $id_booking = $_POST['id_booking'] ?? null;
        $id_user = $_SESSION['user_id'];
        $status = 'pending';
        
        if (!$id_booking) {
            throw new Exception('ID booking tidak valid');
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Cek apakah booking masih valid dan milik user ini
            $check_booking = "SELECT b.*, ts.id_transaksi_service 
                             FROM tb_booking b 
                             LEFT JOIN tb_transaksi_service ts ON b.id_booking = ts.id_booking 
                             WHERE b.id_booking = ? AND b.id_user = ?";
            $stmt = $conn->prepare($check_booking);
            $stmt->bind_param("ii", $id_booking, $id_user);
            $stmt->execute();
            $booking_result = $stmt->get_result();
            
            if ($booking_result->num_rows == 0) {
                throw new Exception('Booking tidak ditemukan atau bukan milik Anda!');
            }
            
            $booking_data = $booking_result->fetch_assoc();
            
            // Cek apakah sudah ada transaksi untuk booking ini
            if ($booking_data['id_transaksi_service'] !== null) {
                throw new Exception('Pembayaran untuk booking ini sudah pernah diupload!');
            }
            
            // Cek apakah ada booking lain yang sudah dibayar pada waktu yang sama
            $check_conflict = "SELECT b2.id_booking, ts2.status_transaksi_service 
                              FROM tb_booking b1 
                              INNER JOIN tb_booking b2 ON (b1.id_service = b2.id_service AND b1.waktu_booking = b2.waktu_booking) 
                              INNER JOIN tb_transaksi_service ts2 ON b2.id_booking = ts2.id_booking 
                              WHERE b1.id_booking = ? AND b2.id_booking != ? 
                              AND ts2.status_transaksi_service IN ('pending', 'confirmed')";
            $stmt = $conn->prepare($check_conflict);
            $stmt->bind_param("ii", $id_booking, $id_booking);
            $stmt->execute();
            $conflict_result = $stmt->get_result();
            
            if ($conflict_result->num_rows > 0) {
                // Ada konflik, hapus booking ini
                $delete_booking = "DELETE FROM tb_booking WHERE id_booking = ?";
                $delete_stmt = $conn->prepare($delete_booking);
                $delete_stmt->bind_param("i", $id_booking);
                $delete_stmt->execute();
                
                $conn->commit();
                
                if (isset($_POST['ajax']) || isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                    echo json_encode([
                        'success' => false,
                        'booking_cancelled' => true,
                        'message' => 'Maaf, slot waktu tersebut sudah dibooking dan dibayar oleh pengguna lain. Booking Anda telah dibatalkan.'
                    ]);
                } else {
                    echo "<script>alert('Maaf, slot waktu tersebut sudah dibooking dan dibayar oleh pengguna lain. Booking Anda telah dibatalkan.'); window.location.href='services.php';</script>";
                }
                return;
            }
            
            // Handle file upload
            $bukti_pembayaran = null;
            if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] == 0) {
                $upload_dir = 'assets/uploads/payment_proofs/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Validasi file
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                $file_type = $_FILES['bukti_pembayaran']['type'];
                
                if (!in_array($file_type, $allowed_types)) {
                    throw new Exception('Format file tidak diizinkan. Gunakan JPG, PNG, atau GIF.');
                }
                
                if ($_FILES['bukti_pembayaran']['size'] > 2 * 1024 * 1024) { // 2MB
                    throw new Exception('Ukuran file terlalu besar. Maksimal 2MB.');
                }
                
                $file_extension = pathinfo($_FILES['bukti_pembayaran']['name'], PATHINFO_EXTENSION);
                $file_name = 'payment_service_' . $id_booking . '_' . time() . '.' . $file_extension;
                $file_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['bukti_pembayaran']['tmp_name'], $file_path)) {
                    $bukti_pembayaran = $file_name;
                } else {
                    throw new Exception('Gagal mengupload file');
                }
            } else {
                throw new Exception('Bukti pembayaran harus diupload');
            }
            
            // Insert transaction
            $insert_transaction = "INSERT INTO tb_transaksi_service (id_booking, status_transaksi_service, bukti_transaksi_service, tgl_transaksi_service) 
                                  VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($insert_transaction);
            
            if (!$stmt) {
                throw new Exception('Database preparation error: ' . $conn->error);
            }
            
            $stmt->bind_param("iss", $id_booking, $status, $bukti_pembayaran);
            
            if ($stmt->execute()) {
                $conn->commit();
                
                if (isset($_POST['ajax']) || isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Bukti pembayaran berhasil diupload! Menunggu konfirmasi admin.'
                    ]);
                } else {
                    echo "<script>alert('Bukti pembayaran berhasil diupload! Menunggu konfirmasi admin.'); window.location.href='services.php';</script>";
                }
            } else {
                throw new Exception('Gagal menyimpan transaksi: ' . $stmt->error);
            }
            
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
        
    } catch (Exception $e) {
        error_log('Payment Upload Error: ' . $e->getMessage());
        
        if (isset($_POST['ajax']) || isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        } else {
            echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
    
    if (isset($_POST['ajax']) || isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        exit;
    }
}

// Function untuk cleanup booking yang expired (bisa dijalankan via cron job)
function cleanupExpiredBookings($conn) {
    try {
        $cleanup_query = "DELETE b FROM tb_booking b 
                         LEFT JOIN tb_transaksi_service ts ON b.id_booking = ts.id_booking 
                         WHERE ts.id_booking IS NULL 
                         AND b.created_at < DATE_SUB(NOW(), INTERVAL 30 MINUTE)";
        
        $result = $conn->query($cleanup_query);
        
        if ($result) {
            $deleted_count = $conn->affected_rows;
            error_log("Cleanup: $deleted_count expired bookings deleted");
            return $deleted_count;
        }
        
        return 0;
    } catch (Exception $e) {
        error_log('Cleanup Error: ' . $e->getMessage());
        return 0;
    }
}
// Function to translate English day names to Indonesian
function translateDayToIndonesian($day) {
    $days = [
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
        'Sunday' => 'Minggu'
    ];
    return $days[$day] ?? $day;
}

// Get all services
$services_query = "SELECT * FROM tb_service ORDER BY id_service";
$services_result = $conn->query($services_query);

// Get user's bookings if logged in
$user_bookings = [];
if (isset($_SESSION['user_id'])) {
    $bookings_query = "SELECT b.*, s.nama_service, s.harga_service, 
                              COALESCE(ts.status_transaksi_service, 'belum_bayar') as status_pembayaran
                       FROM tb_booking b 
                       JOIN tb_service s ON b.id_service = s.id_service 
                       LEFT JOIN tb_transaksi_service ts ON b.id_booking = ts.id_booking
                       WHERE b.id_user = ? 
                       AND (ts.status_transaksi_service IS NULL OR ts.status_transaksi_service = 'belum_bayar')
                       ORDER BY b.waktu_booking DESC";
    $stmt = $conn->prepare($bookings_query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $user_bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Get service schedules
function getServiceSchedules($conn, $service_id) {
    $schedule_query = "SELECT * FROM tb_jadwal WHERE id_service = ? ORDER BY 
                       CASE hari 
                           WHEN 'Senin' THEN 1
                           WHEN 'Selasa' THEN 2
                           WHEN 'Rabu' THEN 3
                           WHEN 'Kamis' THEN 4
                           WHEN 'Jumat' THEN 5
                           WHEN 'Sabtu' THEN 6
                           WHEN 'Minggu' THEN 7
                       END";
    $stmt = $conn->prepare($schedule_query);
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

include 'controller/indexController.php';
$productTypes = getProductTypes($conn);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Services - Buana Pet Shop</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="author" content="TemplatesJungle">
    <meta name="keywords" content="ecommerce,fashion,store">
    <meta name="description" content="Bootstrap 5 Fashion Store HTML CSS Template">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="assets/css/vendor.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&family=Marcellus&display=swap"
        rel="stylesheet">

    <style>
        .service-card {
            transition: transform 0.3s ease;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .schedule-badge {
            font-size: 0.8rem;
            margin: 2px;
        }
        .booking-status {
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85rem;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-confirmed {
            background-color: #d1edff;
            color: #0c5460;
        }
        .status-belum-bayar {
            background-color: #f8d7da;
            color: #721c24;
        }
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-bottom: none;
        }
        .btn-book-now {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 10px 30px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        .btn-book-now:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        .btn-book-now:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .profile-dropdown {
            position: relative;
        }

        .profile-dropdown .dropdown-menu {
            min-width: 200px;
        }

        .profile-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #dee2e6;
        }

        .profile-info {
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .auth-btn {
            background: none;
            border: none;
            color: inherit;
            text-decoration: none;
            text-transform: uppercase;
            cursor: pointer;
        }

        .auth-btn:hover {
            color: #0d6efd;
        }

        .login-required-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            border-radius: 8px;
        }

        .service-card.login-required {
            position: relative;
        }

        .login-prompt {
            text-align: center;
            padding: 20px;
        }

        .login-prompt .btn {
            margin-top: 10px;
        }
    </style>
</head>

<body class="homepage">
    <!-- svg -->
    <?php include '_component/svg.php'; ?>

    <div class="preloader text-white fs-6 text-uppercase overflow-hidden"></div>

    <!-- Search Popup -->
    <?php include '_component/search.php'; ?>

    <!-- Cart Offcanvas -->
    <?php include '_component/cart.php'; ?>

    <!-- Navigation -->
    <?php include '_component/navigation.php'; ?>

    <!-- Auth Modal -->
    <?php include '_component/modalauth.php'; ?>
    <?php include '_component/modalpesanan.php'; ?>
    <?php include '_component/modalprofile.php'; ?>
    <main>
        <!-- Hero Section -->
        <section class="hero-section bg-light py-5">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="display-4 fw-bold text-dark">Layanan kami</h1>
                        <p class="lead text-muted">Berikan perawatan terbaik untuk hewan kesayangan Anda dengan layanan profesional kami</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section class="services-section py-5">
            <div class="container">
                <div class="row g-4">
                    <?php while($service = $services_result->fetch_assoc()): ?>
                    <?php $schedules = getServiceSchedules($conn, $service['id_service']); ?>
                    <div class="col-lg-6 col-md-6">
                        <div class="card service-card h-100 <?= !isset($_SESSION['user_id']) ? 'login-required' : '' ?>">
                            <?php if (!isset($_SESSION['user_id'])): ?>
                            <div class="login-required-overlay">
                                <div class="login-prompt">
                                    <h6 class="text-primary">Login Required</h6>
                                    <p class="text-muted mb-0">Silakan login untuk melakukan booking</p>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#authModal">
                                        Login Sekarang
                                    </button>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="card-body p-4">
                                <h5 class="card-title fw-bold text-dark"><?= htmlspecialchars($service['nama_service']) ?></h5>
                                <p class="card-text text-muted mb-3"><?= htmlspecialchars($service['deskripsi_service']) ?></p>
                                
                                <div class="mb-3">
                                    <h6 class="fw-bold text-primary">Jadwal Tersedia:</h6>
                                    <?php if (!empty($schedules)): ?>
                                        <?php foreach($schedules as $schedule): ?>
                                            <span class="badge bg-secondary schedule-badge">
                                                <?= $schedule['hari'] ?>: <?= substr($schedule['duty_start'], 0, 5) ?> - <?= substr($schedule['duty_end'], 0, 5) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-danger">Jadwal belum tersedia</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="h5 text-primary fw-bold mb-0">Rp <?= number_format($service['harga_service'], 0, ',', '.') ?></span>
                                    <?php if (!empty($schedules)): ?>
                                        <?php if (isset($_SESSION['user_id'])): ?>
                                            <button class="btn btn-book-now" data-bs-toggle="modal" data-bs-target="#bookingModal<?= $service['id_service'] ?>">
                                                Book Now
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-book-now" disabled title="Login diperlukan">
                                                Login untuk Book
                                            </button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <button class="btn btn-secondary" disabled>Tidak Tersedia</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Modal for each service (only show if user is logged in) -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="modal fade" id="bookingModal<?= $service['id_service'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Book <?= htmlspecialchars($service['nama_service']) ?></h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST" id="bookingForm<?= $service['id_service'] ?>">
                                    <div class="modal-body">
                                        <input type="hidden" name="action" value="book_service">
                                        <input type="hidden" name="id_service" value="<?= $service['id_service'] ?>">
                                        <input type="hidden" name="ajax" value="1">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Layanan</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($service['nama_service']) ?>" readonly>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Harga</label>
                                            <input type="text" class="form-control" value="Rp <?= number_format($service['harga_service'], 0, ',', '.') ?>" readonly>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="booking_date<?= $service['id_service'] ?>" class="form-label">Tanggal Booking *</label>
                                            <input type="date" class="form-control" name="booking_date" id="booking_date<?= $service['id_service'] ?>" 
                                                   min="<?= date('Y-m-d') ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="booking_time<?= $service['id_service'] ?>" class="form-label">Waktu Booking *</label>
                                            <select class="form-select" name="booking_time" id="booking_time<?= $service['id_service'] ?>" required>
                                                <option value="">Pilih waktu...</option>
                                                <!-- Time options will be populated by JavaScript based on selected date and schedule -->
                                            </select>
                                        </div>
                                        
                                        <div class="alert alert-info">
                                            <small>
                                                <strong>Jadwal Layanan:</strong><br>
                                                <?php foreach($schedules as $schedule): ?>
                                                    <?= $schedule['hari'] ?>: <?= substr($schedule['duty_start'], 0, 5) ?> - <?= substr($schedule['duty_end'], 0, 5) ?><br>
                                                <?php endforeach; ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">
                                            <span class="spinner-border spinner-border-sm d-none me-2" role="status"></span>
                                            Konfirmasi Booking
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>

        <!-- User Bookings Section -->
        <?php if (isset($_SESSION['user_id']) && !empty($user_bookings)): ?>
        <section class="bookings-section py-5 bg-light">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <h3 class="fw-bold mb-4">Booking Saya</h3>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Layanan</th>
                                        <th>Waktu Booking</th>
                                        <th>Harga</th>
                                        <th>Status Pembayaran</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($user_bookings as $booking): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($booking['nama_service']) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($booking['waktu_booking'])) ?></td>
                                        <td>Rp <?= number_format($booking['harga_service'], 0, ',', '.') ?></td>
                                        <td>
                                            <span class="booking-status status-<?= $booking['status_pembayaran'] ?>">
                                                <?php 
                                                switch($booking['status_pembayaran']) {
                                                    case 'pending': echo 'Menunggu Konfirmasi'; break;
                                                    case 'confirmed': echo 'Dikonfirmasi'; break;
                                                    case 'belum_bayar': echo 'Belum Bayar'; break;
                                                    default: echo ucfirst($booking['status_pembayaran']);
                                                }
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($booking['status_pembayaran'] == 'belum_bayar'): ?>
                                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" 
                                                        data-bs-target="#paymentModal<?= $booking['id_booking'] ?>">
                                                    Upload Pembayaran
                                                </button>

                                                <!-- Payment Modal -->
                                                <div class="modal fade" id="paymentModal<?= $booking['id_booking'] ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Upload Bukti Pembayaran</h5>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form method="POST" enctype="multipart/form-data">
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="action" value="upload_payment">
                                                                    <input type="hidden" name="id_booking" value="<?= $booking['id_booking'] ?>">
                                                                    
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Layanan</label>
                                                                        <input type="text" class="form-control" value="<?= htmlspecialchars($booking['nama_service']) ?>" readonly>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Total Pembayaran</label>
                                                                        <input type="text" class="form-control" value="Rp <?= number_format($booking['harga_service'], 0, ',', '.') ?>" readonly>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label for="bukti_pembayaran" class="form-label">Bukti Pembayaran *</label>
                                                                        <input type="file" class="form-control" name="bukti_pembayaran" 
                                                                               accept="image/*" required>
                                                                        <small class="text-muted">Format: JPG, PNG, GIF (Max: 2MB)</small>
                                                                    </div>
                                                                    
                                                                    <div class="alert alert-warning">
                                                                        <small>
                                                                            <strong>Informasi Pembayaran:</strong><br>
                                                                            Transfer ke rekening: BCA 1234567890<br>
                                                                            A.n: Buana Pet Shop<br>
                                                                            Jumlah: Rp <?= number_format($booking['harga_service'], 0, ',', '.') ?>
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                    <button type="submit" class="btn btn-primary">Upload Bukti</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <?php include '_component/footer.php'; ?>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/plugins.js"></script>
    <script src="assets/js/SmoothScroll.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous">
        </script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script src="assets/js/script.min.js"></script>
<script>
    // Schedule data untuk JavaScript (tetap seperti yang sudah ada)
    const scheduleData = {
        <?php 
        $services_result = $conn->query("SELECT * FROM tb_service");
        while($service = $services_result->fetch_assoc()): 
            $schedules = getServiceSchedules($conn, $service['id_service']);
        ?>
        <?= $service['id_service'] ?>: [
            <?php foreach($schedules as $schedule): ?>
            {
                day: '<?= $schedule['hari'] ?>',
                start: '<?= $schedule['duty_start'] ?>',
                end: '<?= $schedule['duty_end'] ?>'
            },
            <?php endforeach; ?>
        ],
        <?php endwhile; ?>
    };

    // Day translation
    const dayTranslation = {
        'Sunday': 'Minggu',
        'Monday': 'Senin',
        'Tuesday': 'Selasa',
        'Wednesday': 'Rabu',
        'Thursday': 'Kamis',
        'Friday': 'Jumat',
        'Saturday': 'Sabtu'
    };

    function initializeBookingForms() {
    // Handle semua form booking dengan AJAX
    $('form[id^="bookingForm"]').on('submit', function(e) {
        e.preventDefault(); // Mencegah form submit biasa
        
        const form = $(this);
        const formData = new FormData(this);
        const submitBtn = form.find('button[type="submit"]');
        const spinner = submitBtn.find('.spinner-border');
        
        // Set loading state
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        
        // Kirim AJAX request
        $.ajax({
            url: '', // Current page (services.php)
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Response received:', response);
                
                // Reset loading state
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
                
                if (response.success) {
                    // Tutup modal
                    form.closest('.modal').modal('hide');
                    
                    // Tampilkan notifikasi sukses
                    showBookingAlert('success', response.message);
                    
                    // Reset form
                    form[0].reset();
                    
                    // Refresh halaman setelah 2 detik untuk menampilkan booking baru
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                    
                } else if (response.login_required) {
                    // Tutup modal booking
                    form.closest('.modal').modal('hide');
                    
                    // Tampilkan pesan dan buka modal login
                    showBookingAlert('warning', response.message);
                    setTimeout(() => {
                        $('#authModal').modal('show');
                        switchToLogin();
                    }, 1500);
                    
                } else {
                    // Tampilkan error message
                    showBookingAlert('danger', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Response Text:', xhr.responseText);
                
                // Reset loading state
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
                
                // Tampilkan error
                showBookingAlert('danger', 'Terjadi kesalahan sistem. Silakan coba lagi.');
            }
        });
    });
}

// Fungsi untuk menampilkan alert booking
function showBookingAlert(type, message) {
    // Hapus alert yang ada
    $('.booking-alert').remove();
    
    // Buat alert baru
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show booking-alert" role="alert" 
             style="position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    $('body').append(alertHtml);
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        $('.booking-alert').fadeOut(() => {
            $('.booking-alert').remove();
        });
    }, 5000);
}

    // TAMBAHKAN: Document ready function dengan inisialisasi lengkap
$(document).ready(function () {
    checkUserSession();
    initializeCart();
    initializeScheduling();
    initializeBookingForms(); // Tambahkan ini
    
    // Login form submit
    $('#loginForm').on('submit', function (e) {
        e.preventDefault();
        handleLogin();
    });

    // Register form submit
    $('#registerForm').on('submit', function (e) {
        e.preventDefault();
        handleRegister();
    });
    
    // Reset modals when hidden
    $('.modal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
        $(this).find('.was-validated').removeClass('was-validated');
    });
});

// PERBAIKAN: Fungsi untuk menangani response dari server
function handleAjaxResponse(response, successCallback, errorCallback) {
    try {
        // Jika response berupa string, parse sebagai JSON
        if (typeof response === 'string') {
            response = JSON.parse(response);
        }
        
        if (response.success) {
            if (successCallback) successCallback(response);
        } else {
            if (errorCallback) errorCallback(response);
        }
    } catch (e) {
        console.error('Error parsing response:', e);
        if (errorCallback) {
            errorCallback({ success: false, message: 'Terjadi kesalahan dalam memproses response' });
        }
    }
}

    // TAMBAHKAN: Fungsi autentikasi
    function checkUserSession() {
        $.post('controller/authController.php', {
            action: 'check_session'
        }, function (response) {
            if (response.success && response.logged_in) {
                showUserSection(response.user);
                showCartSection();
                loadCartCount();
            } else {
                showGuestSection();
                hideCartSection();
            }
        }, 'json').fail(function () {
            showGuestSection();
            hideCartSection();
        });
    }

    function showGuestSection() {
        $('#guestSection').show();
        $('#userSection').hide();
        updateCartCount(0);
    }

    function showUserSection(user) {
        $('#guestSection').hide();
        $('#userSection').show();

        $('#userName').text(user.nama || user.username);
        $('#userNameHeader').text(user.nama || user.username);

        if (user.profile && user.profile.trim() !== '') {
            $('#userAvatar').attr('src', user.profile).show();
            $('#defaultAvatar').hide();
        } else {
            $('#userAvatar').hide();
            $('#defaultAvatar').show();
        }

        loadCartCount();
    }

    function showCartSection() {
        $('#desktopCartSection').show().removeClass('d-none').addClass('d-lg-block');
        $('#mobileCartSection').show().removeClass('d-none').addClass('d-lg-none');
    }

    function hideCartSection() {
        $('#desktopCartSection').hide();
        $('#mobileCartSection').hide();
        updateCartCount(0);
    }

    function handleLogin() {
            const username = $('#loginUsername').val();
            const password = $('#loginPassword').val();

            setLoading('loginBtn', true);

            $.post('controller/authController.php', {
                action: 'login',
                username: username,
                password: password
            }, function (response) {
                setLoading('loginBtn', false);

                if (response.success) {
                    showAlert('success', response.message);

                    // Check if user role requires redirect
                    const userRole = response.user.role;

                    if (userRole === 'admin' || userRole === 'pimpinan') {
                        // For admin and pimpinan, redirect immediately
                        setTimeout(() => {
                            window.location.href = response.redirect_url;
                        }, 1000);
                    } else {
                        // For pelanggan, stay on current page
                        setTimeout(() => {
                            $('#authModal').modal('hide');
                            showUserSection(response.user);
                            showCartSection();
                            resetForms();
                        }, 1000);
                    }
                } else {
                    showAlert('danger', response.message);
                }
            }, 'json').fail(function () {
                setLoading('loginBtn', false);
                showAlert('danger', 'Terjadi kesalahan sistem');
            });
        }

    function handleRegister() {
        const nama = $('#registerNama').val();
        const username = $('#registerUsername').val();
        const password = $('#registerPassword').val();
        const confirmPassword = $('#registerConfirmPassword').val();

        if (password !== confirmPassword) {
            showAlert('danger', 'Password tidak cocok');
            return;
        }

        setLoading('registerBtn', true);

        $.post('controller/authController.php', {
            action: 'register',
            nama: nama,
            username: username,
            password: password,
            confirm_password: confirmPassword
        }, function (response) {
            setLoading('registerBtn', false);

            if (response.success) {
                showAlert('success', response.message);
                setTimeout(() => {
                    $('#authModal').modal('hide');
                    showUserSection(response.user);
                    showCartSection();
                    resetForms();
                }, 1000);
            } else {
                showAlert('danger', response.message);
            }
        }, 'json').fail(function () {
            setLoading('registerBtn', false);
            showAlert('danger', 'Terjadi kesalahan sistem');
        });
    }

    function logout() {
        $.post('controller/authController.php', {
            action: 'logout'
        }, function (response) {
            if (response.success) {
                showGuestSection();
                hideCartSection();
                location.reload();
            }
        }, 'json');
    }

    function switchToRegister() {
        $('#authModalLabel').text('Register');
        $('#loginForm').hide();
        $('#registerForm').show();
        $('#loginSwitch').hide();
        $('#registerSwitch').show();
        hideAlert();
    }

    function switchToLogin() {
        $('#authModalLabel').text('Login');
        $('#registerForm').hide();
        $('#loginForm').show();
        $('#registerSwitch').hide();
        $('#loginSwitch').show();
        hideAlert();
    }

    function setLoading(btnId, isLoading) {
        const btn = $('#' + btnId);
        const spinner = btn.find('.spinner-border');

        if (isLoading) {
            btn.prop('disabled', true);
            spinner.removeClass('d-none');
        } else {
            btn.prop('disabled', false);
            spinner.addClass('d-none');
        }
    }

    function showAlert(type, message) {
        const alert = $('#authAlert');
        alert.removeClass('d-none alert-success alert-danger alert-warning alert-info')
            .addClass('alert-' + type)
            .text(message);
    }

    function hideAlert() {
        $('#authAlert').addClass('d-none');
    }

    function resetForms() {
        $('#loginForm')[0].reset();
        $('#registerForm')[0].reset();
        hideAlert();
    }

    // Reset modal when hidden
    $('#authModal').on('hidden.bs.modal', function () {
        switchToLogin();
        resetForms();
    });

    // TAMBAHKAN: Cart functionality
    function initializeCart() {
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('add-to-cart') || e.target.closest('.add-to-cart')) {
                e.preventDefault();

                const button = e.target.classList.contains('add-to-cart') ? e.target : e.target.closest('.add-to-cart');
                const productId = button.getAttribute('data-id');

                if (productId) {
                    addToCart(productId, button);
                }
            }
        });
    }

    function loadCartCount() {
        $.post('controller/cartController.php', {
            action: 'get_cart_count'
        }, function (response) {
            if (response.success) {
                updateCartCount(response.cart_count);
            }
        }, 'json').fail(function () {
            console.log('Failed to load cart count');
        });
    }

    function addToCart(productId, button) {
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Menambah...';

        $.post('controller/cartController.php', {
            action: 'add_to_cart',
            product_id: productId
        }, function (response) {
            button.disabled = false;
            button.innerHTML = originalText;

            if (response.success) {
                showCartAlert('success', response.message);
                updateCartCount(response.cart_count);

                button.innerHTML = '✓ Ditambahkan';
                button.classList.remove('btn-outline-primary');
                button.classList.add('btn-success');

                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.remove('btn-success');
                    button.classList.add('btn-outline-primary');
                }, 2000);

            } else {
                if (response.login_required) {
                    showCartAlert('warning', response.message);
                    setTimeout(() => {
                        $('#authModal').modal('show');
                        switchToLogin();
                    }, 1500);
                } else {
                    showCartAlert('danger', response.message);
                }
            }
        }, 'json').fail(function () {
            button.disabled = false;
            button.innerHTML = originalText;
            showCartAlert('danger', 'Terjadi kesalahan sistem');
        });
    }

    function updateCartCount(count) {
        const cartCountElements = document.querySelectorAll('.cart-count');
        cartCountElements.forEach(element => {
            element.textContent = '(' + count + ')';
        });

        const cartBadges = document.querySelectorAll('.cart-badge');
        cartBadges.forEach(badge => {
            if (count > 0) {
                badge.textContent = count;
                badge.style.display = 'inline';
            } else {
                badge.style.display = 'none';
            }
        });
    }

    function showCartAlert(type, message) {
        let alertContainer = document.getElementById('cartAlertContainer');
        if (!alertContainer) {
            alertContainer = document.createElement('div');
            alertContainer.id = 'cartAlertContainer';
            alertContainer.style.position = 'fixed';
            alertContainer.style.top = '20px';
            alertContainer.style.right = '20px';
            alertContainer.style.zIndex = '9999';
            alertContainer.style.maxWidth = '400px';
            document.body.appendChild(alertContainer);
        }

        const alertElement = document.createElement('div');
        alertElement.className = `alert alert-${type} alert-dismissible fade show`;
        alertElement.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        alertContainer.appendChild(alertElement);

        setTimeout(() => {
            if (alertElement.parentNode) {
                alertElement.remove();
            }
        }, 5000);
    }

    // FUNGSI SCHEDULING (yang sudah ada, diperbaiki)
    function initializeScheduling() {
        <?php 
        $services_result = $conn->query("SELECT * FROM tb_service");
        while($service = $services_result->fetch_assoc()): 
        ?>
        const dateInput<?= $service['id_service'] ?> = document.getElementById('booking_date<?= $service['id_service'] ?>');
        const timeSelect<?= $service['id_service'] ?> = document.getElementById('booking_time<?= $service['id_service'] ?>');
        
        if (dateInput<?= $service['id_service'] ?>) {
            dateInput<?= $service['id_service'] ?>.addEventListener('change', function() {
                updateTimeOptions(<?= $service['id_service'] ?>, this.value, timeSelect<?= $service['id_service'] ?>);
            });
        }
        <?php endwhile; ?>
    }

    function updateTimeOptions(serviceId, selectedDate, timeSelect) {
        timeSelect.innerHTML = '<option value="">Pilih waktu...</option>';
        
        if (!selectedDate) return;
        
        const selectedDay = new Date(selectedDate).toLocaleDateString('en-US', {weekday: 'long'});
        const indonesianDay = dayTranslation[selectedDay];
        
        const schedules = scheduleData[serviceId] || [];
        const availableSchedule = schedules.find(schedule => schedule.day === indonesianDay);
        
        if (!availableSchedule) {
            timeSelect.innerHTML = '<option value="">Tidak ada jadwal untuk hari ini</option>';
            return;
        }
        
        const startTime = availableSchedule.start;
        const endTime = availableSchedule.end;
        
        const start = new Date(`2000-01-01 ${startTime}`);
        const end = new Date(`2000-01-01 ${endTime}`);
        
        while (start < end) {
            const timeString = start.toTimeString().substring(0, 5);
            const option = document.createElement('option');
            option.value = timeString;
            option.textContent = timeString;
            timeSelect.appendChild(option);
            
            start.setMinutes(start.getMinutes() + 30);
        }
    }

    // Form validation
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
                form.classList.add('was-validated');
            }
        });
    });
</script>
</body>
</html>