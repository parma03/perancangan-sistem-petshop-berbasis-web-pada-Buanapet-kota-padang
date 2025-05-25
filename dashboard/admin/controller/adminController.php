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

?>