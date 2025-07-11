<?php
// admin/controller/userController.php
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

function isAjaxRequest()
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// Function to get profile image or initials
function getProfileDisplay($nama, $profile)
{
    $profile_path = "../../../assets/uploads/profile/";

    if (!empty($profile) && file_exists($profile_path . $profile)) {
        return '<img src="' . $profile_path . htmlspecialchars($profile) . '" 
                     alt="Profile" 
                     class="rounded-circle" 
                     style="width: 35px; height: 35px; object-fit: cover;">';
    } else {
        return '<div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                     style="width: 35px; height: 35px; font-size: 14px;">
                    ' . strtoupper(substr($nama, 0, 2)) . '
                </div>';
    }
}

// Function to get large profile display for detail view
function getLargeProfileDisplay($nama, $profile)
{
    $profile_path = "../../../assets/uploads/profile/";

    if (!empty($profile) && file_exists($profile_path . $profile)) {
        return '<img src="' . $profile_path . htmlspecialchars($profile) . '" 
                     alt="Profile" 
                     class="rounded-circle mx-auto mb-3" 
                     style="width: 80px; height: 80px; object-fit: cover; display: block;">';
    } else {
        return '<div class="bg-primary text-white rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3"
                     style="width: 80px; height: 80px; font-size: 24px;">
                    ' . strtoupper(substr($nama, 0, 2)) . '
                </div>';
    }
}

// Function to handle file upload
function handleProfileUpload($file, $oldProfile = null)
{
    $upload_dir = "../../../assets/uploads/profile/";

    // Create directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Validate file
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception("Format file tidak valid! Gunakan JPG, PNG, atau GIF.");
    }

    // Check file size (max 2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        throw new Exception("Ukuran file terlalu besar! Maksimal 2MB.");
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'profile_' . uniqid() . '.' . $extension;
    $filepath = $upload_dir . $filename;

    // Upload file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception("Gagal mengupload file!");
    }

    // Delete old profile if exists
    if ($oldProfile && file_exists($upload_dir . $oldProfile)) {
        unlink($upload_dir . $oldProfile);
    }

    return $filename;
}

// Function to delete profile image
function deleteProfileImage($filename)
{
    $upload_dir = "../../../assets/uploads/profile/";
    $filepath = $upload_dir . $filename;

    if (file_exists($filepath)) {
        return unlink($filepath);
    }

    return true;
}

// Check request
$request = $_POST['request'] ?? '';

if (isAjaxRequest()) {

    // Get Admin Data
    if ($request == 'get_admin') {
        try {
            // Get all admin data with user information
            $query = "SELECT a.id_admin, a.id_user, a.nama, a.profile, u.username, u.role
                      FROM tb_admin a 
                      JOIN tb_user u ON a.id_user = u.id_user 
                      ORDER BY a.nama ASC";

            $result = $conn->query($query);

            if (!$result) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Database error: ' . $conn->error
                ]);
                exit;
            }

            $admins = [];
            while ($row = $result->fetch_assoc()) {
                $admins[] = $row;
            }

            // Format the output HTML
            ob_start();

            if (count($admins) > 0) {
                ?>
                <div class="table-responsive">
                    <table id="adminTable" class="table table-hover table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col" style="width: 5%;">#</th>
                                <th scope="col" style="width: 20%;">Nama</th>
                                <th scope="col" style="width: 15%;">Username</th>
                                <th scope="col" style="width: 10%;">Role</th>
                                <th scope="col" style="width: 10%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            foreach ($admins as $admin) {
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php echo getProfileDisplay($admin['nama'], $admin['profile']); ?>
                                            <div class="ms-2">
                                                <strong><?php echo htmlspecialchars($admin['nama']); ?></strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($admin['username']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <i class="fas fa-shield-alt me-1"></i><?php echo htmlspecialchars($admin['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-info view-admin-btn"
                                                data-id="<?php echo $admin['id_admin']; ?>" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-warning edit-admin-btn"
                                                data-id="<?php echo $admin['id_admin']; ?>" title="Edit Admin">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger delete-admin-btn"
                                                data-id="<?php echo $admin['id_admin']; ?>"
                                                data-name="<?php echo htmlspecialchars($admin['nama']); ?>" title="Hapus Admin">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <?php
            } else {
                ?>
                <div class="alert alert-info text-center" role="alert">
                    <i class="fas fa-info-circle me-2"></i>Belum ada data admin.
                    <br><br>
                    <button type="button" class="btn btn-primary add-admin-btn">
                        <i class="fas fa-plus me-1"></i>Tambah Admin Pertama
                    </button>
                </div>
                <?php
            }

            $html = ob_get_clean();
            echo json_encode(['status' => 'success', 'html' => $html]);

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Get Admin by ID for editing
    if ($request == 'get_admin_by_id') {
        $admin_id = $_POST['admin_id'] ?? 0;

        if (!$admin_id) {
            echo json_encode(['status' => 'error', 'message' => 'ID Admin tidak valid']);
            exit;
        }

        try {
            $query = "SELECT a.id_admin, a.id_user, a.nama, a.profile, u.username
                      FROM tb_admin a 
                      JOIN tb_user u ON a.id_user = u.id_user 
                      WHERE a.id_admin = ?";

            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $admin_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $admin = $result->fetch_assoc();
                echo json_encode(['status' => 'success', 'data' => $admin]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Data admin tidak ditemukan']);
            }

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Get Admin Detail
    if ($request == 'get_admin_detail') {
        $admin_id = $_POST['admin_id'] ?? 0;

        if (!$admin_id) {
            echo json_encode(['status' => 'error', 'message' => 'ID Admin tidak valid']);
            exit;
        }

        try {
            $query = "SELECT a.id_admin, a.id_user, a.nama, a.profile, u.username, u.role
                      FROM tb_admin a 
                      JOIN tb_user u ON a.id_user = u.id_user 
                      WHERE a.id_admin = ?";

            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $admin_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $admin = $result->fetch_assoc();

                ob_start();
                ?>
                <div class="row">
                    <div class="col-md-4 text-center">
                        <?php echo getLargeProfileDisplay($admin['nama'], $admin['profile']); ?>
                        <h5><?php echo htmlspecialchars($admin['nama']); ?></h5>
                        <span class="badge bg-primary"><?php echo htmlspecialchars($admin['role']); ?></span>
                    </div>
                    <div class="col-md-8">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Username:</strong></td>
                                <td><?php echo htmlspecialchars($admin['username']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Nama Lengkap:</strong></td>
                                <td><?php echo htmlspecialchars($admin['nama']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Role:</strong></td>
                                <td>
                                    <span class="badge bg-primary">
                                        <i class="fas fa-shield-alt me-1"></i><?php echo htmlspecialchars($admin['role']); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Profile Image:</strong></td>
                                <td>
                                    <?php if ($admin['profile']): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Ada
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-times me-1"></i>Tidak ada
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <?php
                $html = ob_get_clean();
                echo json_encode(['status' => 'success', 'html' => $html]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Data admin tidak ditemukan']);
            }

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Add Admin
    if ($request == 'add_admin') {
        $username = trim($_POST['username'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validation
        if (empty($username) || empty($nama) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Username, nama, dan password harus diisi!']);
            exit;
        }

        try {
            // Check if username already exists
            $check_query = "SELECT id_user FROM tb_user WHERE username = ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bind_param("s", $username);
            $check_stmt->execute();

            if ($check_stmt->get_result()->num_rows > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Username sudah digunakan!']);
                exit;
            }

            // Handle profile image upload
            $profile_filename = null;
            if (isset($_FILES['profile']) && $_FILES['profile']['error'] === UPLOAD_ERR_OK) {
                $profile_filename = handleProfileUpload($_FILES['profile']);
            }

            // Begin transaction
            $conn->begin_transaction();

            // Insert into tb_user
            $user_query = "INSERT INTO tb_user (username, password, role) VALUES (?, ?, 'admin')";
            $user_stmt = $conn->prepare($user_query);
            $user_stmt->bind_param("ss", $username, $password);

            if (!$user_stmt->execute()) {
                throw new Exception("Gagal menyimpan data user!");
            }

            $user_id = $conn->insert_id;

            // Insert into tb_admin
            $admin_query = "INSERT INTO tb_admin (id_user, nama, profile) VALUES (?, ?, ?)";
            $admin_stmt = $conn->prepare($admin_query);
            $admin_stmt->bind_param("iss", $user_id, $nama, $profile_filename);

            if (!$admin_stmt->execute()) {
                throw new Exception("Gagal menyimpan data admin!");
            }

            // Commit transaction
            $conn->commit();

            echo json_encode([
                'status' => 'success',
                'message' => 'Admin berhasil ditambahkan!'
            ]);

        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();

            // Delete uploaded file if exists
            if ($profile_filename) {
                deleteProfileImage($profile_filename);
            }

            echo json_encode([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Update Admin
    if ($request == 'update_admin') {
        $admin_id = $_POST['admin_id'] ?? 0;
        $user_id = $_POST['user_id'] ?? 0;
        $username = trim($_POST['username'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $password = $_POST['password'] ?? '';
        $remove_existing_profile = $_POST['removeExistingProfile'] ?? '0';

        // Validation
        if (!$admin_id || !$user_id || empty($username) || empty($nama)) {
            echo json_encode(['status' => 'error', 'message' => 'ID Admin, Username, dan nama harus diisi!']);
            exit;
        }

        try {
            // Check if username already exists (exclude current user)
            $check_query = "SELECT id_user FROM tb_user WHERE username = ? AND id_user != ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bind_param("si", $username, $user_id);
            $check_stmt->execute();

            if ($check_stmt->get_result()->num_rows > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Username sudah digunakan!']);
                exit;
            }

            // Get current profile
            $current_query = "SELECT profile FROM tb_admin WHERE id_admin = ?";
            $current_stmt = $conn->prepare($current_query);
            $current_stmt->bind_param("i", $admin_id);
            $current_stmt->execute();
            $current_result = $current_stmt->get_result();
            $current_profile = $current_result->fetch_assoc()['profile'] ?? null;

            // Handle profile image
            $profile_filename = $current_profile;

            // If user wants to remove existing profile
            if ($remove_existing_profile === '1' && $current_profile) {
                deleteProfileImage($current_profile);
                $profile_filename = null;
            }

            // If new profile is uploaded
            if (isset($_FILES['profile']) && $_FILES['profile']['error'] === UPLOAD_ERR_OK) {
                $profile_filename = handleProfileUpload($_FILES['profile'], $current_profile);
            }

            // Begin transaction
            $conn->begin_transaction();

            // Update tb_user
            if (!empty($password)) {
                $user_query = "UPDATE tb_user SET username = ?, password = ? WHERE id_user = ?";
                $user_stmt = $conn->prepare($user_query);
                $user_stmt->bind_param("ssi", $username, $password, $user_id);
            } else {
                $user_query = "UPDATE tb_user SET username = ? WHERE id_user = ?";
                $user_stmt = $conn->prepare($user_query);
                $user_stmt->bind_param("si", $username, $user_id);
            }

            if (!$user_stmt->execute()) {
                throw new Exception("Gagal mengupdate data user!");
            }

            // Update tb_admin
            $admin_query = "UPDATE tb_admin SET nama = ?, profile = ? WHERE id_admin = ?";
            $admin_stmt = $conn->prepare($admin_query);
            $admin_stmt->bind_param("ssi", $nama, $profile_filename, $admin_id);

            if (!$admin_stmt->execute()) {
                throw new Exception("Gagal mengupdate data admin!");
            }

            // Commit transaction
            $conn->commit();

            echo json_encode([
                'status' => 'success',
                'message' => 'Admin berhasil diupdate!'
            ]);

        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();

            echo json_encode([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Delete Admin
    if ($request == 'delete_admin') {
        $admin_id = $_POST['admin_id'] ?? 0;

        if (!$admin_id) {
            echo json_encode(['status' => 'error', 'message' => 'ID Admin tidak valid']);
            exit;
        }

        try {
            // Get admin data before deletion
            $get_query = "SELECT a.id_user, a.profile FROM tb_admin a WHERE a.id_admin = ?";
            $get_stmt = $conn->prepare($get_query);
            $get_stmt->bind_param("i", $admin_id);
            $get_stmt->execute();
            $result = $get_stmt->get_result();

            if ($result->num_rows === 0) {
                echo json_encode(['status' => 'error', 'message' => 'Data admin tidak ditemukan']);
                exit;
            }

            $admin_data = $result->fetch_assoc();
            $user_id = $admin_data['id_user'];
            $profile_image = $admin_data['profile'];

            // Begin transaction
            $conn->begin_transaction();

            // Delete from tb_admin first (foreign key constraint)
            $delete_admin_query = "DELETE FROM tb_admin WHERE id_admin = ?";
            $delete_admin_stmt = $conn->prepare($delete_admin_query);
            $delete_admin_stmt->bind_param("i", $admin_id);

            if (!$delete_admin_stmt->execute()) {
                throw new Exception("Gagal menghapus data admin!");
            }

            // Delete from tb_user
            $delete_user_query = "DELETE FROM tb_user WHERE id_user = ?";
            $delete_user_stmt = $conn->prepare($delete_user_query);
            $delete_user_stmt->bind_param("i", $user_id);

            if (!$delete_user_stmt->execute()) {
                throw new Exception("Gagal menghapus data user!");
            }

            // Delete profile image if exists
            if ($profile_image) {
                deleteProfileImage($profile_image);
            }

            // Commit transaction
            $conn->commit();

            echo json_encode([
                'status' => 'success',
                'message' => 'Admin berhasil dihapus!'
            ]);

        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();

            echo json_encode([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
        exit;
    }

} else {
    // Not an AJAX request
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Close connection
$conn->close();
?>