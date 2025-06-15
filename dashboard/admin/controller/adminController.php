<?php
// admin/controller/adminController.php

// Check request
$request = $_POST['request'] ?? '';

// Fungsi untuk mengecek apakah user sudah login dan memiliki role admin
function checkAdminAccess()
{
    // Cek apakah session sudah dimulai
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Cek apakah user sudah login
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        // Jika belum login, redirect ke halaman login
        header('Location: ../../index.php');
        exit();
    }

    // Cek apakah role adalah admin
    if ($_SESSION['role'] !== 'admin') {
        // Jika bukan admin, redirect ke halaman unauthorized atau halaman utama
        header('Location: ../../unauthorized.php');
        exit();
    }

    return true;
}

// Fungsi untuk mengecek apakah user memiliki role tertentu
function checkRole($allowedRoles = [])
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        return false;
    }

    return in_array($_SESSION['role'], $allowedRoles);
}

// Fungsi untuk mendapatkan informasi user yang sedang login
function getCurrentUser()
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? '',
        'role' => $_SESSION['role'] ?? '',
        'nama' => $_SESSION['nama'] ?? '',
        'profile' => $_SESSION['profile'] ?? ''
    ];
}

// Fungsi untuk logout dengan dukungan SweetAlert response
function handleLogout()
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Hapus semua session variables
    $_SESSION = array();

    // Hapus session cookie jika ada
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Destroy session
    session_destroy();

    // Jika request adalah AJAX
    if (isAjaxRequest()) {
        sendJsonResponse(true, 'Logout berhasil! Anda akan diarahkan ke halaman login.', [
            'redirect' => '../../index.php',
            'type' => 'success'
        ]);
    } else {
        // Redirect ke halaman login atau homepage
        header('Location: ../../index.php');
        exit();
    }
}

// Fungsi untuk mengecek apakah request adalah AJAX
function isAjaxRequest()
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// Fungsi untuk mengirim response JSON (untuk AJAX requests dengan dukungan SweetAlert)
function sendJsonResponse($success, $message, $data = null)
{
    header('Content-Type: application/json');

    $response = [
        'success' => $success,
        'message' => $message,
        'type' => $success ? 'success' : 'error'
    ];

    if ($data !== null) {
        $response['data'] = $data;
        // Jika ada type khusus di data, gunakan itu
        if (isset($data['type'])) {
            $response['type'] = $data['type'];
        }
    }

    echo json_encode($response);
    exit();
}

// Fungsi untuk mengecek admin access dengan response yang berbeda untuk AJAX
function checkAdminAccessWithAjax()
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Cek apakah user sudah login
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        if (isAjaxRequest()) {
            sendJsonResponse(false, 'Anda harus login terlebih dahulu!', [
                'redirect' => '../../index.php',
                'type' => 'warning'
            ]);
        } else {
            header('Location: ../../index.php');
            exit();
        }
    }

    // Cek apakah role adalah admin
    if ($_SESSION['role'] !== 'admin') {
        if (isAjaxRequest()) {
            sendJsonResponse(false, 'Akses ditolak! Anda tidak memiliki izin untuk mengakses halaman ini.', [
                'redirect' => '../../unauthorized.php',
                'type' => 'error'
            ]);
        } else {
            header('Location: ../../unauthorized.php');
            exit();
        }
    }

    return true;
}

// Middleware untuk proteksi halaman admin
function requireAdmin()
{
    return checkAdminAccess();
}

// Handle logout request
if (isset($_POST['action']) && $_POST['action'] === 'logout') {
    handleLogout();
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    handleLogout();
}

// Auto-execute checkAdminAccess ketika file ini di-include
// Kecuali jika konstanta SKIP_ADMIN_CHECK sudah didefinisikan
if (!defined('SKIP_ADMIN_CHECK')) {
    checkAdminAccess();
}

// Handle update profile request
if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {

    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "db_buanapetshop";

    // Membuat koneksi
    $conn = new mysqli($servername, $username, $password, $database);

    // Memeriksa koneksi
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    // Pastikan user sudah login
    if (!isset($_SESSION['user_id'])) {
        sendJsonResponse(false, 'Anda harus login terlebih dahulu!');
    }

    $userId = $_SESSION['user_id'];
    $nama = trim($_POST['nama'] ?? '');
    $updatePassword = isset($_POST['current_password']) && !empty($_POST['current_password']);
    $profileUpdated = false;

    // Validasi input
    if (empty($nama)) {
        sendJsonResponse(false, 'Nama lengkap harus diisi!');
    }

    try {
        // Mulai transaksi
        mysqli_begin_transaction($conn);

        // Handle upload foto profile jika ada
        $newProfileImage = '';
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {

            $uploadDir = '../../../assets/uploads/profile/';

            // Buat folder jika belum ada
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $file = $_FILES['profile_photo'];
            $fileName = $file['name'];
            $fileTmpName = $file['tmp_name'];
            $fileSize = $file['size'];
            $fileType = $file['type'];

            // Validasi file
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $maxSize = 2 * 1024 * 1024; // 2MB

            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception('Format file tidak didukung! Gunakan JPG, PNG, atau GIF.');
            }

            if ($fileSize > $maxSize) {
                throw new Exception('Ukuran file terlalu besar! Maksimal 2MB.');
            }

            // Generate nama file unik
            $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
            $newFileName = 'profile_' . $userId . '_' . time() . '.' . $fileExtension;
            $uploadPath = $uploadDir . $newFileName;

            // Upload file
            if (move_uploaded_file($fileTmpName, $uploadPath)) {
                $newProfileImage = $newFileName;

                // Hapus foto lama jika ada - PERBAIKAN: Query ke tb_admin
                $oldProfileQuery = "SELECT profile FROM tb_admin WHERE id_user = ?";
                $oldProfileStmt = mysqli_prepare($conn, $oldProfileQuery);
                mysqli_stmt_bind_param($oldProfileStmt, "i", $userId);
                mysqli_stmt_execute($oldProfileStmt);
                $oldProfileResult = mysqli_stmt_get_result($oldProfileStmt);
                $oldProfile = mysqli_fetch_assoc($oldProfileResult);

                if (!empty($oldProfile['profile'])) {
                    $oldFilePath = $uploadDir . $oldProfile['profile'];
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }

                $profileUpdated = true;
            } else {
                throw new Exception('Gagal mengupload foto profile!');
            }
        }

        // PERBAIKAN: Update data admin (nama dan profile di tb_admin)
        $updateQuery = "UPDATE tb_admin SET nama = ?";
        $params = [$nama];
        $types = "s";

        // Tambahkan foto profile jika ada
        if (!empty($newProfileImage)) {
            $updateQuery .= ", profile = ?";
            $params[] = $newProfileImage;
            $types .= "s";
        }

        $updateQuery .= " WHERE id_user = ?";
        $params[] = $userId;
        $types .= "i";

        $updateStmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($updateStmt, $types, ...$params);

        if (!mysqli_stmt_execute($updateStmt)) {
            throw new Exception('Gagal mengupdate data profile!');
        }

        // Update password jika diminta - PERBAIKAN: Password di tb_user
        if ($updatePassword) {
            $currentPassword = $_POST['current_password'];
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];

            // Validasi password
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                throw new Exception('Semua field password harus diisi!');
            }

            if ($newPassword !== $confirmPassword) {
                throw new Exception('Konfirmasi password tidak cocok!');
            }

            if (strlen($newPassword) < 6) {
                throw new Exception('Password baru minimal 6 karakter!');
            }

            // PERBAIKAN: Cek password lama dari tb_user
            $checkPasswordQuery = "SELECT password FROM tb_user WHERE id_user = ?";
            $checkPasswordStmt = mysqli_prepare($conn, $checkPasswordQuery);
            mysqli_stmt_bind_param($checkPasswordStmt, "i", $userId);
            mysqli_stmt_execute($checkPasswordStmt);
            $passwordResult = mysqli_stmt_get_result($checkPasswordStmt);
            $userPassword = mysqli_fetch_assoc($passwordResult);

            if (!password_verify($currentPassword, $userPassword['password'])) {
                throw new Exception('Password lama tidak sesuai!');
            }

            // PERBAIKAN: Update password di tb_user
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updatePasswordQuery = "UPDATE tb_user SET password = ? WHERE id_user = ?";
            $updatePasswordStmt = mysqli_prepare($conn, $updatePasswordQuery);
            mysqli_stmt_bind_param($updatePasswordStmt, "si", $hashedPassword, $userId);

            if (!mysqli_stmt_execute($updatePasswordStmt)) {
                throw new Exception('Gagal mengupdate password!');
            }
        }

        // Update session dengan data terbaru
        $_SESSION['nama'] = $nama;
        if (!empty($newProfileImage)) {
            $_SESSION['profile'] = $newProfileImage;
        }

        // Commit transaksi
        mysqli_commit($conn);

        // Response sukses
        $message = 'Profile berhasil diupdate!';
        if ($updatePassword) {
            $message .= ' Password juga telah diubah.';
        }

        sendJsonResponse(true, $message, [
            'profile_updated' => $profileUpdated,
            'type' => 'success'
        ]);

    } catch (Exception $e) {
        // Rollback transaksi
        mysqli_rollback($conn);

        // Hapus file yang sudah diupload jika ada error
        if (!empty($newProfileImage) && file_exists($uploadDir . $newProfileImage)) {
            unlink($uploadDir . $newProfileImage);
        }

        sendJsonResponse(false, $e->getMessage());
    }

    // Tutup koneksi
    mysqli_close($conn);
}

?>