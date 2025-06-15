<?php
// profileController.php - Controller untuk mengelola profile user
session_start();
include '../db/koneksi.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'get_profile':
        getProfile($conn);
        break;
    case 'update_profile':
        updateProfile($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function getProfile($conn)
{
    $user_id = $_SESSION['user_id'];

    try {
        $stmt = $conn->prepare("SELECT u.id_user, u.username, u.role, 
                                COALESCE(p.nama, a.nama) as nama,
                                COALESCE(p.profile, a.profile) as profile
                                FROM tb_user u 
                                LEFT JOIN tb_pelanggan p ON u.id_user = p.id_user 
                                LEFT JOIN tb_admin a ON u.id_user = a.id_user 
                                WHERE u.id_user = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            echo json_encode([
                'success' => true,
                'user' => $user
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
    }
}

function updateProfile($conn)
{
    $user_id = $_SESSION['user_id'];
    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($nama) || empty($username)) {
        echo json_encode(['success' => false, 'message' => 'Nama dan username harus diisi']);
        return;
    }

    try {
        $conn->begin_transaction();

        // Check if username is already taken by another user
        $stmt = $conn->prepare("SELECT id_user FROM tb_user WHERE username = ? AND id_user != ?");
        $stmt->bind_param("si", $username, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Username sudah digunakan']);
            return;
        }

        // Handle password change (kode ini tetap sama)
        if (!empty($new_password)) {
            if ($new_password !== $confirm_password) {
                echo json_encode(['success' => false, 'message' => 'Password baru tidak cocok']);
                return;
            }

            $stmt = $conn->prepare("SELECT password FROM tb_user WHERE id_user = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_data = $result->fetch_assoc();

            if ($current_password !== $user_data['password']) {
                echo json_encode(['success' => false, 'message' => 'Password saat ini salah']);
                return;
            }

            $stmt = $conn->prepare("UPDATE tb_user SET username = ?, password = ? WHERE id_user = ?");
            $stmt->bind_param("ssi", $username, $new_password, $user_id);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("UPDATE tb_user SET username = ? WHERE id_user = ?");
            $stmt->bind_param("si", $username, $user_id);
            $stmt->execute();
        }

        // BAGIAN YANG DIUBAH: Handle profile picture upload
        $profile_path = '';
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            // Dapatkan profile lama sebelum upload baru
            $old_profile = getCurrentProfilePath($conn, $user_id);

            $profile_path = handleProfilePictureUpload($_FILES['profile_picture'], $user_id);
            if ($profile_path === false) {
                echo json_encode(['success' => false, 'message' => 'Gagal mengupload foto profile']);
                return;
            }

            // Hapus file profile lama jika berhasil upload baru
            if ($old_profile && file_exists($old_profile)) {
                unlink($old_profile);
            }
        }

        // Update profile data based on role (kode ini tetap sama)
        $role = $_SESSION['role'];
        if ($role === 'pelanggan') {
            if ($profile_path) {
                $stmt = $conn->prepare("UPDATE tb_pelanggan SET nama = ?, profile = ? WHERE id_user = ?");
                $stmt->bind_param("ssi", $nama, $profile_path, $user_id);
            } else {
                $stmt = $conn->prepare("UPDATE tb_pelanggan SET nama = ? WHERE id_user = ?");
                $stmt->bind_param("si", $nama, $user_id);
            }
        } else {
            if ($profile_path) {
                $stmt = $conn->prepare("UPDATE tb_admin SET nama = ?, profile = ? WHERE id_user = ?");
                $stmt->bind_param("ssi", $nama, $profile_path, $user_id);
            } else {
                $stmt = $conn->prepare("UPDATE tb_admin SET nama = ? WHERE id_user = ?");
                $stmt->bind_param("si", $nama, $user_id);
            }
        }

        $stmt->execute();
        $conn->commit();

        // Update session data
        $_SESSION['username'] = $username;
        $_SESSION['nama'] = $nama;
        if ($profile_path) {
            $_SESSION['profile'] = $profile_path;
        }

        // Return updated user data
        $updated_user = [
            'id' => $user_id,
            'username' => $username,
            'role' => $role,
            'nama' => $nama,
            'profile' => $profile_path ?: $_SESSION['profile']
        ];

        echo json_encode([
            'success' => true,
            'message' => 'Profile berhasil diperbarui',
            'user' => $updated_user
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
    }
}

// TAMBAHKAN FUNCTION BARU INI:
function getCurrentProfilePath($conn, $user_id)
{
    $role = $_SESSION['role'];

    try {
        if ($role === 'pelanggan') {
            $stmt = $conn->prepare("SELECT profile FROM tb_pelanggan WHERE id_user = ?");
        } else {
            $stmt = $conn->prepare("SELECT profile FROM tb_admin WHERE id_user = ?");
        }

        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $data = $result->fetch_assoc();
            return $data['profile'];
        }

        $stmt->close();
        return null;
    } catch (Exception $e) {
        return null;
    }
}

// Function handleProfilePictureUpload tetap sama, tidak perlu diubah
function handleProfilePictureUpload($file, $user_id)
{
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB

    // Validate file type
    if (!in_array($file['type'], $allowed_types)) {
        return false;
    }

    // Validate file size
    if ($file['size'] > $max_size) {
        return false;
    }

    // Create upload directory if not exists
    $upload_dir = '../assets/uploads/profile/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'profile_' . $user_id . '_' . time() . '.' . $extension;
    $upload_path = $upload_dir . $filename;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        // Return relative path for database storage
        return '../assets/uploads/profile/' . $filename;
    }

    return false;
}
?>