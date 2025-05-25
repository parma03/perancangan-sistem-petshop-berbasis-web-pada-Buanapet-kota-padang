<?php
// barang/controller/barangController.php
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

// Function to get barang image or default placeholder
function getBarangImageDisplay($nama_barang, $foto_barang, $size = 'small')
{
    $barang_path = "../../../assets/uploads/barang/";

    if ($size === 'large') {
        $width = '120px';
        $height = '120px';
        $fontSize = '24px';
    } else {
        $width = '45px';
        $height = '45px';
        $fontSize = '14px';
    }

    if (!empty($foto_barang) && file_exists($barang_path . $foto_barang)) {
        return '<img src="' . $barang_path . htmlspecialchars($foto_barang) . '" 
                     alt="' . htmlspecialchars($nama_barang) . '" 
                     class="rounded-3 shadow-sm" 
                     style="width: ' . $width . '; height: ' . $height . '; object-fit: cover;">';
    } else {
        $initials = strtoupper(substr($nama_barang, 0, 2));
        return '<div class="bg-gradient text-white rounded-3 d-flex align-items-center justify-content-center shadow-sm" 
                     style="width: ' . $width . '; height: ' . $height . '; font-size: ' . $fontSize . '; 
                            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    ' . $initials . '
                </div>';
    }
}

// Function to format currency
function formatCurrency($amount)
{
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Function to handle file upload
function handleBarangPhotoUpload($file, $oldPhoto = null)
{
    $upload_dir = "../../../assets/uploads/barang/";

    // Create directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Validate file
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception("Format file tidak valid! Gunakan JPG, PNG, GIF, atau WEBP.");
    }

    // Check file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception("Ukuran file terlalu besar! Maksimal 5MB.");
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'barang_' . uniqid() . '.' . $extension;
    $filepath = $upload_dir . $filename;

    // Upload file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception("Gagal mengupload file!");
    }

    // Delete old photo if exists
    if ($oldPhoto && file_exists($upload_dir . $oldPhoto)) {
        unlink($upload_dir . $oldPhoto);
    }

    return $filename;
}

// Function to delete barang photo
function deleteBarangPhoto($filename)
{
    $upload_dir = "../../../assets/uploads/barang/";
    $filepath = $upload_dir . $filename;

    if (file_exists($filepath)) {
        return unlink($filepath);
    }

    return true;
}

// Check request
$request = $_POST['request'] ?? '';

if (isAjaxRequest()) {

    // Get Barang Data
    if ($request == 'get_barang') {
        try {
            // Get all barang data
            $query = "SELECT * FROM tb_barang ORDER BY nama_barang ASC";
            $result = $conn->query($query);

            if (!$result) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Database error: ' . $conn->error
                ]);
                exit;
            }

            $barangs = [];
            while ($row = $result->fetch_assoc()) {
                $barangs[] = $row;
            }

            // Format the output HTML
            ob_start();

            if (count($barangs) > 0) {
                ?>
                <div class="table-responsive">
                    <table id="barangTable" class="table table-hover align-middle">
                        <thead>
                            <tr class="table-dark">
                                <th scope="col" style="width: 5%;" class="text-center">#</th>
                                <th scope="col" style="width: 25%;">Barang</th>
                                <th scope="col" style="width: 15%;">Tipe</th>
                                <th scope="col" style="width: 15%;">Harga</th>
                                <th scope="col" style="width: 20%;">Deskripsi</th>
                                <th scope="col" style="width: 8%;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            foreach ($barangs as $barang) {
                                ?>
                                <tr class="border-bottom">
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark rounded-pill"><?php echo $no++; ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <?php echo getBarangImageDisplay($barang['nama_barang'], $barang['foto_barang']); ?>
                                            </div>
                                            <div>
                                                <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($barang['nama_barang']); ?></h6>
                                                <small class="text-muted">ID: <?php echo $barang['id_barang']; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-gradient">
                                            <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($barang['tipe_barang']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-success">
                                            <?php echo formatCurrency($barang['harga_barang']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 150px;"
                                            title="<?php echo htmlspecialchars($barang['deskripsi_barang']); ?>">
                                            <?php
                                            $deskripsi = $barang['deskripsi_barang'];
                                            echo !empty($deskripsi) ? htmlspecialchars($deskripsi) : '<em class="text-muted">Tidak ada deskripsi</em>';
                                            ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-info view-barang-btn"
                                                data-id="<?php echo $barang['id_barang']; ?>" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-warning edit-barang-btn"
                                                data-id="<?php echo $barang['id_barang']; ?>" title="Edit Barang">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger delete-barang-btn"
                                                data-id="<?php echo $barang['id_barang']; ?>"
                                                data-name="<?php echo htmlspecialchars($barang['nama_barang']); ?>" title="Hapus Barang">
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
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-box-open fa-4x text-muted"></i>
                    </div>
                    <h5 class="text-muted mb-3">Belum Ada Data Barang</h5>
                    <p class="text-muted mb-4">Mulai dengan menambahkan barang pertama Anda</p>
                    <button type="button" class="btn btn-primary btn-lg add-barang-btn">
                        <i class="fas fa-plus me-2"></i>Tambah Barang Pertama
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

    // Get Barang by ID for editing
    if ($request == 'get_barang_by_id') {
        $barang_id = $_POST['barang_id'] ?? 0;

        if (!$barang_id) {
            echo json_encode(['status' => 'error', 'message' => 'ID Barang tidak valid']);
            exit;
        }

        try {
            $query = "SELECT * FROM tb_barang WHERE id_barang = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $barang_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $barang = $result->fetch_assoc();
                echo json_encode(['status' => 'success', 'data' => $barang]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Data barang tidak ditemukan']);
            }

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Get Barang Detail
    if ($request == 'get_barang_detail') {
        $barang_id = $_POST['barang_id'] ?? 0;

        if (!$barang_id) {
            echo json_encode(['status' => 'error', 'message' => 'ID Barang tidak valid']);
            exit;
        }

        try {
            $query = "SELECT * FROM tb_barang WHERE id_barang = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $barang_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $barang = $result->fetch_assoc();

                ob_start();
                ?>
                <div class="container-fluid">
                    <div class="row">
                        <!-- Left Column - Image -->
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="mb-4">
                                    <?php echo getBarangImageDisplay($barang['nama_barang'], $barang['foto_barang'], 'large'); ?>
                                </div>
                                <div class="bg-light rounded-3 p-3">
                                    <div class="row text-center">
                                        <div class="col">
                                            <h6 class="mb-1 text-muted">ID Barang</h6>
                                            <span class="badge bg-dark"><?php echo $barang['id_barang']; ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Details -->
                        <div class="col-md-8">
                            <div class="mb-4">
                                <h4 class="fw-bold text-primary mb-2"><?php echo htmlspecialchars($barang['nama_barang']); ?></h4>
                                <span class="badge bg-primary bg-gradient fs-6">
                                    <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($barang['tipe_barang']); ?>
                                </span>
                            </div>

                            <div class="row g-4">
                                <div class="col-sm-6">
                                    <div class="card border-0 bg-light h-100">
                                        <div class="card-body text-center">
                                            <div class="text-success mb-2">
                                                <i class="fas fa-money-bill-wave fa-2x"></i>
                                            </div>
                                            <h6 class="text-muted mb-1">Harga Barang</h6>
                                            <h4 class="fw-bold text-success mb-0"><?php echo formatCurrency($barang['harga_barang']); ?>
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <h6 class="fw-bold mb-3">
                                    <i class="fas fa-align-left text-secondary me-2"></i>Deskripsi Barang
                                </h6>
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <?php
                                        $deskripsi = trim($barang['deskripsi_barang']);
                                        if (!empty($deskripsi)) {
                                            echo '<p class="mb-0 text-dark">' . nl2br(htmlspecialchars($deskripsi)) . '</p>';
                                        } else {
                                            echo '<p class="mb-0 text-muted font-italic">
                                                    <i class="fas fa-info-circle me-1"></i>Tidak ada deskripsi untuk barang ini.
                                                  </p>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="mt-4 pt-3 border-top">
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-warning edit-barang-btn"
                                        data-id="<?php echo $barang['id_barang']; ?>" data-bs-dismiss="modal">
                                        <i class="fas fa-edit me-1"></i>Edit Barang
                                    </button>
                                    <button type="button" class="btn btn-danger delete-barang-btn"
                                        data-id="<?php echo $barang['id_barang']; ?>"
                                        data-name="<?php echo htmlspecialchars($barang['nama_barang']); ?>" data-bs-dismiss="modal">
                                        <i class="fas fa-trash me-1"></i>Hapus Barang
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                $html = ob_get_clean();
                echo json_encode(['status' => 'success', 'html' => $html]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Data barang tidak ditemukan']);
            }

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Add Barang
    if ($request == 'add_barang') {
        $nama_barang = trim($_POST['nama_barang'] ?? '');
        $tipe_barang = trim($_POST['tipe_barang'] ?? '');
        $harga_barang = (int) ($_POST['harga_barang'] ?? 0);
        $deskripsi_barang = trim($_POST['deskripsi_barang'] ?? '');

        // Validation
        if (empty($nama_barang) || empty($tipe_barang) || $harga_barang <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Nama barang, tipe barang, dan harga harus diisi dengan benar!']);
            exit;
        }

        try {
            // Check if barang name already exists
            $check_query = "SELECT id_barang FROM tb_barang WHERE nama_barang = ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bind_param("s", $nama_barang);
            $check_stmt->execute();

            if ($check_stmt->get_result()->num_rows > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Nama barang sudah digunakan!']);
                exit;
            }

            // Handle photo upload
            $foto_filename = null;
            if (isset($_FILES['foto_barang']) && $_FILES['foto_barang']['error'] === UPLOAD_ERR_OK) {
                $foto_filename = handleBarangPhotoUpload($_FILES['foto_barang']);
            }

            // Insert into tb_barang
            $insert_query = "INSERT INTO tb_barang (nama_barang, tipe_barang, harga_barang, deskripsi_barang, foto_barang) 
                           VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("ssiss", $nama_barang, $tipe_barang, $harga_barang, $deskripsi_barang, $foto_filename);

            if ($insert_stmt->execute()) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Barang berhasil ditambahkan!'
                ]);
            } else {
                // Delete uploaded file if database insert failed
                if ($foto_filename) {
                    deleteBarangPhoto($foto_filename);
                }
                throw new Exception("Gagal menyimpan data barang!");
            }

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Update Barang
    if ($request == 'update_barang') {
        $barang_id = $_POST['barang_id'] ?? 0;
        $nama_barang = trim($_POST['nama_barang'] ?? '');
        $tipe_barang = trim($_POST['tipe_barang'] ?? '');
        $harga_barang = (int) ($_POST['harga_barang'] ?? 0);
        $deskripsi_barang = trim($_POST['deskripsi_barang'] ?? '');
        $remove_existing_photo = $_POST['removeExistingPhoto'] ?? '0';

        // Validation
        if (!$barang_id || empty($nama_barang) || empty($tipe_barang) || $harga_barang <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID Barang, nama barang, tipe barang, dan harga harus diisi dengan benar!']);
            exit;
        }

        try {
            // Check if barang name already exists (exclude current barang)
            $check_query = "SELECT id_barang FROM tb_barang WHERE nama_barang = ? AND id_barang != ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bind_param("si", $nama_barang, $barang_id);
            $check_stmt->execute();

            if ($check_stmt->get_result()->num_rows > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Nama barang sudah digunakan!']);
                exit;
            }

            // Get current photo
            $current_query = "SELECT foto_barang FROM tb_barang WHERE id_barang = ?";
            $current_stmt = $conn->prepare($current_query);
            $current_stmt->bind_param("i", $barang_id);
            $current_stmt->execute();
            $current_result = $current_stmt->get_result();
            $current_photo = $current_result->fetch_assoc()['foto_barang'] ?? null;

            // Handle photo
            $foto_filename = $current_photo;

            // If user wants to remove existing photo
            if ($remove_existing_photo === '1' && $current_photo) {
                deleteBarangPhoto($current_photo);
                $foto_filename = null;
            }

            // If new photo is uploaded
            if (isset($_FILES['foto_barang']) && $_FILES['foto_barang']['error'] === UPLOAD_ERR_OK) {
                $foto_filename = handleBarangPhotoUpload($_FILES['foto_barang'], $current_photo);
            }

            // Update tb_barang
            $update_query = "UPDATE tb_barang SET nama_barang = ?, tipe_barang = ?, harga_barang = ?, deskripsi_barang = ?, foto_barang = ? WHERE id_barang = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("ssissi", $nama_barang, $tipe_barang, $harga_barang, $deskripsi_barang, $foto_filename, $barang_id);

            if ($update_stmt->execute()) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Barang berhasil diupdate!'
                ]);
            } else {
                throw new Exception("Gagal mengupdate data barang!");
            }

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Delete Barang
    if ($request == 'delete_barang') {
        $barang_id = $_POST['barang_id'] ?? 0;

        if (!$barang_id) {
            echo json_encode(['status' => 'error', 'message' => 'ID Barang tidak valid']);
            exit;
        }

        try {
            // Get barang data before deletion
            $get_query = "SELECT foto_barang FROM tb_barang WHERE id_barang = ?";
            $get_stmt = $conn->prepare($get_query);
            $get_stmt->bind_param("i", $barang_id);
            $get_stmt->execute();
            $result = $get_stmt->get_result();

            if ($result->num_rows === 0) {
                echo json_encode(['status' => 'error', 'message' => 'Data barang tidak ditemukan']);
                exit;
            }

            $barang_data = $result->fetch_assoc();
            $foto_barang = $barang_data['foto_barang'];

            // Delete from tb_barang
            $delete_query = "DELETE FROM tb_barang WHERE id_barang = ?";
            $delete_stmt = $conn->prepare($delete_query);
            $delete_stmt->bind_param("i", $barang_id);

            if ($delete_stmt->execute()) {
                // Delete photo file if exists
                if ($foto_barang) {
                    deleteBarangPhoto($foto_barang);
                }

                echo json_encode([
                    'status' => 'success',
                    'message' => 'Barang berhasil dihapus!'
                ]);
            } else {
                throw new Exception("Gagal menghapus data barang!");
            }

        } catch (Exception $e) {
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