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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'login':
        handleLogin($conn);
        break;
    case 'register':
        handleRegister($conn);
        break;
    case 'logout':
        handleLogout();
        break;
    case 'check_session':
        checkSession($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function handleLogin($conn)
{
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Username dan password harus diisi']);
        return;
    }

    try {
        $stmt = $conn->prepare("SELECT u.id_user, u.username, u.password, u.role, 
                                COALESCE(p.nama, a.nama) as nama,
                                COALESCE(p.profile, a.profile) as profile
                                FROM tb_user u 
                                LEFT JOIN tb_pelanggan p ON u.id_user = p.id_user 
                                LEFT JOIN tb_admin a ON u.id_user = a.id_user 
                                WHERE u.username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Direct password comparison without hashing
            if ($password === $user['password']) {
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['profile'] = $user['profile'];

                // Determine redirect URL based on role
                $redirectUrl = getRedirectUrl($user['role']);

                echo json_encode([
                    'success' => true,
                    'message' => 'Login berhasil',
                    'redirect_url' => $redirectUrl,
                    'user' => [
                        'id' => $user['id_user'],
                        'username' => $user['username'],
                        'role' => $user['role'],
                        'nama' => $user['nama'],
                        'profile' => $user['profile']
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Password salah']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Username tidak ditemukan']);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
    }
}

function handleRegister($conn)
{
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $nama = trim($_POST['nama'] ?? '');

    if (empty($username) || empty($password) || empty($confirm_password) || empty($nama)) {
        echo json_encode(['success' => false, 'message' => 'Semua field harus diisi']);
        return;
    }

    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Password tidak cocok']);
        return;
    }

    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password minimal 6 karakter']);
        return;
    }

    try {
        // Check if username already exists
        $stmt = $conn->prepare("SELECT id_user FROM tb_user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Username sudah digunakan']);
            return;
        }

        $conn->begin_transaction();

        // Insert user with plain text password (no hashing)
        $stmt = $conn->prepare("INSERT INTO tb_user (username, password, role) VALUES (?, ?, 'pelanggan')");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();

        $user_id = $conn->insert_id;

        // Insert pelanggan
        $stmt = $conn->prepare("INSERT INTO tb_pelanggan (id_user, nama, profile) VALUES (?, ?, '')");
        $stmt->bind_param("is", $user_id, $nama);
        $stmt->execute();

        $conn->commit();

        // Auto login after registration
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'pelanggan';
        $_SESSION['nama'] = $nama;
        $_SESSION['profile'] = '';

        // Get redirect URL for pelanggan role
        $redirectUrl = getRedirectUrl('pelanggan');

        echo json_encode([
            'success' => true,
            'message' => 'Registrasi berhasil',
            'redirect_url' => $redirectUrl,
            'user' => [
                'id' => $user_id,
                'username' => $username,
                'role' => 'pelanggan',
                'nama' => $nama,
                'profile' => ''
            ]
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
    }
}

function handleLogout()
{
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logout berhasil']);
}

function checkSession($conn)
{
    if (isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => true,
            'logged_in' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'role' => $_SESSION['role'],
                'nama' => $_SESSION['nama'],
                'profile' => $_SESSION['profile']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'logged_in' => false
        ]);
    }
}

function getRedirectUrl($role)
{
    switch ($role) {
        case 'admin':
            return 'dashboard/admin/index.php';
        case 'pimpinan':
            return 'dashboard/pimpinan/index.php';
        default:
            return 'index.php'; // Default redirect
    }
}
?>