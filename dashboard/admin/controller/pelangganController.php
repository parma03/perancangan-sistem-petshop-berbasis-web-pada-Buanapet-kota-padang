<?php
// pelanggan/controller/userController.php
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

    // Get Pelanggan Data
    if ($request == 'get_pelanggan') {
        try {
            // Get all pelanggan data with user information
            $query = "SELECT a.id_pelanggan, a.id_user, a.nama, a.profile, u.username, u.role
                      FROM tb_pelanggan a 
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

            $pelanggans = [];
            while ($row = $result->fetch_assoc()) {
                $pelanggans[] = $row;
            }

            // Format the output HTML
            ob_start();

            if (count($pelanggans) > 0) {
                ?>
                <div class="table-responsive">
                    <table id="pelangganTable" class="table table-hover table-striped align-middle">
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
                            foreach ($pelanggans as $pelanggan) {
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php echo getProfileDisplay($pelanggan['nama'], $pelanggan['profile']); ?>
                                            <div class="ms-2">
                                                <strong><?php echo htmlspecialchars($pelanggan['nama']); ?></strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($pelanggan['username']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <i class="fas fa-shield-alt me-1"></i><?php echo htmlspecialchars($pelanggan['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-info view-pelanggan-btn"
                                                data-id="<?php echo $pelanggan['id_pelanggan']; ?>" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-warning edit-pelanggan-btn"
                                                data-id="<?php echo $pelanggan['id_pelanggan']; ?>" title="Edit Pelanggan">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger delete-pelanggan-btn"
                                                data-id="<?php echo $pelanggan['id_pelanggan']; ?>"
                                                data-name="<?php echo htmlspecialchars($pelanggan['nama']); ?>" title="Hapus Pelanggan">
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
                    <i class="fas fa-info-circle me-2"></i>Belum ada data pelanggan.
                    <br><br>
                    <button type="button" class="btn btn-primary add-pelanggan-btn">
                        <i class="fas fa-plus me-1"></i>Tambah Pelanggan Pertama
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

    // Get Pelanggan by ID for editing
    if ($request == 'get_pelanggan_by_id') {
        $pelanggan_id = $_POST['pelanggan_id'] ?? 0;

        if (!$pelanggan_id) {
            echo json_encode(['status' => 'error', 'message' => 'ID Pelanggan tidak valid']);
            exit;
        }

        try {
            $query = "SELECT a.id_pelanggan, a.id_user, a.nama, a.profile, u.username
                      FROM tb_pelanggan a 
                      JOIN tb_user u ON a.id_user = u.id_user 
                      WHERE a.id_pelanggan = ?";

            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $pelanggan_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $pelanggan = $result->fetch_assoc();
                echo json_encode(['status' => 'success', 'data' => $pelanggan]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Data pelanggan tidak ditemukan']);
            }

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Get Pelanggan Detail
    if ($request == 'get_pelanggan_detail') {
        $pelanggan_id = $_POST['pelanggan_id'] ?? 0;

        if (!$pelanggan_id) {
            echo json_encode(['status' => 'error', 'message' => 'ID Pelanggan tidak valid']);
            exit;
        }

        try {
            $query = "SELECT a.id_pelanggan, a.id_user, a.nama, a.profile, u.username, u.role
                      FROM tb_pelanggan a 
                      JOIN tb_user u ON a.id_user = u.id_user 
                      WHERE a.id_pelanggan = ?";

            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $pelanggan_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $pelanggan = $result->fetch_assoc();

                ob_start();
                ?>
                <div class="row">
                    <div class="col-md-4 text-center">
                        <?php echo getLargeProfileDisplay($pelanggan['nama'], $pelanggan['profile']); ?>
                        <h5><?php echo htmlspecialchars($pelanggan['nama']); ?></h5>
                        <span class="badge bg-primary"><?php echo htmlspecialchars($pelanggan['role']); ?></span>
                    </div>
                    <div class="col-md-8">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Username:</strong></td>
                                <td><?php echo htmlspecialchars($pelanggan['username']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Nama Lengkap:</strong></td>
                                <td><?php echo htmlspecialchars($pelanggan['nama']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Role:</strong></td>
                                <td>
                                    <span class="badge bg-primary">
                                        <i class="fas fa-shield-alt me-1"></i><?php echo htmlspecialchars($pelanggan['role']); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Profile Image:</strong></td>
                                <td>
                                    <?php if ($pelanggan['profile']): ?>
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
                echo json_encode(['status' => 'error', 'message' => 'Data pelanggan tidak ditemukan']);
            }

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Add Pelanggan
    if ($request == 'add_pelanggan') {
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
            $user_query = "INSERT INTO tb_user (username, password, role) VALUES (?, ?, 'pelanggan')";
            $user_stmt = $conn->prepare($user_query);
            $user_stmt->bind_param("ss", $username, $password);

            if (!$user_stmt->execute()) {
                throw new Exception("Gagal menyimpan data user!");
            }

            $user_id = $conn->insert_id;

            // Insert into tb_pelanggan
            $pelanggan_query = "INSERT INTO tb_pelanggan (id_user, nama, profile) VALUES (?, ?, ?)";
            $pelanggan_stmt = $conn->prepare($pelanggan_query);
            $pelanggan_stmt->bind_param("iss", $user_id, $nama, $profile_filename);

            if (!$pelanggan_stmt->execute()) {
                throw new Exception("Gagal menyimpan data pelanggan!");
            }

            // Commit transaction
            $conn->commit();

            echo json_encode([
                'status' => 'success',
                'message' => 'Pelanggan berhasil ditambahkan!'
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

    // Update Pelanggan
    if ($request == 'update_pelanggan') {
        $pelanggan_id = $_POST['pelanggan_id'] ?? 0;
        $user_id = $_POST['user_id'] ?? 0;
        $username = trim($_POST['username'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $password = $_POST['password'] ?? '';
        $remove_existing_profile = $_POST['removeExistingProfile'] ?? '0';

        // Validation
        if (!$pelanggan_id || !$user_id || empty($username) || empty($nama)) {
            echo json_encode(['status' => 'error', 'message' => 'ID Pelanggan, Username, dan nama harus diisi!']);
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
            $current_query = "SELECT profile FROM tb_pelanggan WHERE id_pelanggan = ?";
            $current_stmt = $conn->prepare($current_query);
            $current_stmt->bind_param("i", $pelanggan_id);
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

            // Update tb_pelanggan
            $pelanggan_query = "UPDATE tb_pelanggan SET nama = ?, profile = ? WHERE id_pelanggan = ?";
            $pelanggan_stmt = $conn->prepare($pelanggan_query);
            $pelanggan_stmt->bind_param("ssi", $nama, $profile_filename, $pelanggan_id);

            if (!$pelanggan_stmt->execute()) {
                throw new Exception("Gagal mengupdate data pelanggan!");
            }

            // Commit transaction
            $conn->commit();

            echo json_encode([
                'status' => 'success',
                'message' => 'Pelanggan berhasil diupdate!'
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

    // Delete Pelanggan
    if ($request == 'delete_pelanggan') {
        $pelanggan_id = $_POST['pelanggan_id'] ?? 0;

        if (!$pelanggan_id) {
            echo json_encode(['status' => 'error', 'message' => 'ID Pelanggan tidak valid']);
            exit;
        }

        try {
            // Get pelanggan data before deletion
            $get_query = "SELECT a.id_user, a.profile FROM tb_pelanggan a WHERE a.id_pelanggan = ?";
            $get_stmt = $conn->prepare($get_query);
            $get_stmt->bind_param("i", $pelanggan_id);
            $get_stmt->execute();
            $result = $get_stmt->get_result();

            if ($result->num_rows === 0) {
                echo json_encode(['status' => 'error', 'message' => 'Data pelanggan tidak ditemukan']);
                exit;
            }

            $pelanggan_data = $result->fetch_assoc();
            $user_id = $pelanggan_data['id_user'];
            $profile_image = $pelanggan_data['profile'];

            // Begin transaction
            $conn->begin_transaction();

            // Delete from tb_pelanggan first (foreign key constraint)
            $delete_pelanggan_query = "DELETE FROM tb_pelanggan WHERE id_pelanggan = ?";
            $delete_pelanggan_stmt = $conn->prepare($delete_pelanggan_query);
            $delete_pelanggan_stmt->bind_param("i", $pelanggan_id);

            if (!$delete_pelanggan_stmt->execute()) {
                throw new Exception("Gagal menghapus data pelanggan!");
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
                'message' => 'Pelanggan berhasil dihapus!'
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