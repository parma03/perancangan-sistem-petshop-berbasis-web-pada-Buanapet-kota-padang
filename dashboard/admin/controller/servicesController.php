<?php
// services/controller/servicesController.php
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

// Function to format currency
function formatCurrency($amount)
{
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Check request
$request = $_POST['request'] ?? '';

if (isAjaxRequest()) {

    // Get Services Data
    if ($request == 'get_services') {
        try {
            // Get all services data with schedule count
            $query = "SELECT s.*, COUNT(j.id_jadwal) as total_jadwal 
                     FROM tb_service s 
                     LEFT JOIN tb_jadwal j ON s.id_service = j.id_service 
                     GROUP BY s.id_service 
                     ORDER BY s.nama_service ASC";
            $result = $conn->query($query);

            if (!$result) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Database error: ' . $conn->error
                ]);
                exit;
            }

            $services = [];
            while ($row = $result->fetch_assoc()) {
                $services[] = $row;
            }

            // Format the output HTML
            ob_start();

            if (count($services) > 0) {
                ?>
                                <div class="table-responsive">
                                    <table id="servicesTable" class="table table-hover align-middle">
                                        <thead>
                                            <tr class="table-dark">
                                                <th scope="col" style="width: 5%;" class="text-center">#</th>
                                                <th scope="col" style="width: 25%;">Service</th>
                                                <th scope="col" style="width: 15%;">Harga</th>
                                                <th scope="col" style="width: 20%;">Deskripsi</th>
                                                <th scope="col" style="width: 10%;" class="text-center">Jadwal</th>
                                                <th scope="col" style="width: 15%;" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $no = 1;
                                            foreach ($services as $service) {
                                                ?>
                                                    <tr class="border-bottom">
                                                        <td class="text-center">
                                                            <span class="badge bg-light text-dark rounded-pill"><?php echo $no++; ?></span>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div>
                                                                    <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($service['nama_service']); ?></h6>
                                                                    <small class="text-muted">ID: <?php echo $service['id_service']; ?></small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="fw-bold text-success">
                                                                <?php echo formatCurrency($service['harga_service']); ?>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="text-truncate" style="max-width: 150px;"
                                                                title="<?php echo htmlspecialchars($service['deskripsi_service']); ?>">
                                                                <?php
                                                                $deskripsi = $service['deskripsi_service'];
                                                                echo !empty($deskripsi) ? htmlspecialchars($deskripsi) : '<em class="text-muted">Tidak ada deskripsi</em>';
                                                                ?>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge bg-info text-white">
                                                                <?php echo $service['total_jadwal']; ?> Jadwal
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm" role="group">
                                                                <button type="button" class="btn btn-info view-services-btn"
                                                                    data-id="<?php echo $service['id_service']; ?>" title="Lihat Detail">
                                                                    <i class="fas fa-eye"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-warning edit-services-btn"
                                                                    data-id="<?php echo $service['id_service']; ?>" title="Edit Service">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-success manage-schedule-btn"
                                                                    data-id="<?php echo $service['id_service']; ?>"
                                                                    data-name="<?php echo htmlspecialchars($service['nama_service']); ?>"
                                                                    title="Kelola Jadwal">
                                                                    <i class="fas fa-calendar"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-danger delete-services-btn"
                                                                    data-id="<?php echo $service['id_service']; ?>"
                                                                    data-name="<?php echo htmlspecialchars($service['nama_service']); ?>"
                                                                    title="Hapus Service">
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
                                        <i class="fas fa-hand-holding-heart fa-4x text-muted"></i>
                                    </div>
                                    <h5 class="text-muted mb-3">Belum Ada Data Service</h5>
                                    <p class="text-muted mb-4">Mulai dengan menambahkan service pertama Anda</p>
                                    <button type="button" class="btn btn-primary btn-lg add-services-btn">
                                        <i class="fas fa-plus me-2"></i>Tambah Service Pertama
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

    // Get Service by ID for editing
    if ($request == 'get_services_by_id') {
        $service_id = $_POST['services_id'] ?? 0;

        if (!$service_id) {
            echo json_encode(['status' => 'error', 'message' => 'ID Service tidak valid']);
            exit;
        }

        try {
            $query = "SELECT * FROM tb_service WHERE id_service = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $service_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $service = $result->fetch_assoc();
                echo json_encode(['status' => 'success', 'data' => $service]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Data service tidak ditemukan']);
            }

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Get Service Detail with Schedules
    if ($request == 'get_services_detail') {
        $service_id = $_POST['services_id'] ?? 0;

        if (!$service_id) {
            echo json_encode(['status' => 'error', 'message' => 'ID Service tidak valid']);
            exit;
        }

        try {
            // Get service data
            $query = "SELECT * FROM tb_service WHERE id_service = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $service_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $service = $result->fetch_assoc();

                // Get schedules for this service
                $schedule_query = "SELECT * FROM tb_jadwal WHERE id_service = ? ORDER BY 
                                 CASE hari 
                                     WHEN 'Senin' THEN 1
                                     WHEN 'Selasa' THEN 2
                                     WHEN 'Rabu' THEN 3
                                     WHEN 'Kamis' THEN 4
                                     WHEN 'Jumat' THEN 5
                                     WHEN 'Sabtu' THEN 6
                                     WHEN 'Minggu' THEN 7
                                 END, duty_start ASC";
                $schedule_stmt = $conn->prepare($schedule_query);
                $schedule_stmt->bind_param("i", $service_id);
                $schedule_stmt->execute();
                $schedule_result = $schedule_stmt->get_result();
                $schedules = [];
                while ($row = $schedule_result->fetch_assoc()) {
                    $schedules[] = $row;
                }

                ob_start();
                ?>
                                <div class="container-fluid">
                                    <div class="row">
                                        <!-- Left Column - Service Info -->
                                        <div class="col-md-6">
                                            <div class="mb-4">
                                                <h4 class="fw-bold text-primary mb-2"><?php echo htmlspecialchars($service['nama_service']); ?></h4>
                                                <span class="badge bg-primary bg-gradient fs-6">
                                                    <i class="fas fa-tag me-1"></i>ID: <?php echo $service['id_service']; ?>
                                                </span>
                                            </div>

                                            <div class="card border-0 bg-light mb-4">
                                                <div class="card-body text-center">
                                                    <div class="text-success mb-2">
                                                        <i class="fas fa-money-bill-wave fa-2x"></i>
                                                    </div>
                                                    <h6 class="text-muted mb-1">Harga Service</h6>
                                                    <h4 class="fw-bold text-success mb-0">
                                                        <?php echo formatCurrency($service['harga_service']); ?>
                                                    </h4>
                                                </div>
                                            </div>

                                            <div class="mb-4">
                                                <h6 class="fw-bold mb-3">
                                                    <i class="fas fa-align-left text-secondary me-2"></i>Deskripsi Service
                                                </h6>
                                                <div class="card border-0 bg-light">
                                                    <div class="card-body">
                                                        <?php
                                                        $deskripsi = trim($service['deskripsi_service']);
                                                        if (!empty($deskripsi)) {
                                                            echo '<p class="mb-0 text-dark">' . nl2br(htmlspecialchars($deskripsi)) . '</p>';
                                                        } else {
                                                            echo '<p class="mb-0 text-muted font-italic">
                                                    <i class="fas fa-info-circle me-1"></i>Tidak ada deskripsi untuk service ini.
                                                  </p>';
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Right Column - Schedules -->
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <h6 class="fw-bold">
                                                    <i class="fas fa-calendar-alt text-info me-2"></i>Jadwal Operasional
                                                    <span class="badge bg-info ms-2"><?php echo count($schedules); ?> Jadwal</span>
                                                </h6>
                                            </div>

                                            <?php if (count($schedules) > 0): ?>
                                                    <div class="schedule-list">
                                                        <?php foreach ($schedules as $schedule): ?>
                                                                <div class="card mb-2 border-start border-info border-3">
                                                                    <div class="card-body py-2 px-3">
                                                                        <div class="d-flex justify-content-between align-items-center">
                                                                            <div>
                                                                                <h6 class="mb-1 fw-bold text-info"><?php echo $schedule['hari']; ?></h6>
                                                                                <small class="text-muted">
                                                                                    <i class="fas fa-clock me-1"></i>
                                                                                    <?php echo date('H:i', strtotime($schedule['duty_start'])); ?> - 
                                                                                    <?php echo date('H:i', strtotime($schedule['duty_end'])); ?>
                                                                                </small>
                                                                            </div>
                                                                            <div class="text-end">
                                                                                <?php
                                                                                $start = new DateTime($schedule['duty_start']);
                                                                                $end = new DateTime($schedule['duty_end']);
                                                                                $diff = $start->diff($end);
                                                                                $hours = $diff->h + ($diff->days * 24);
                                                                                $minutes = $diff->i;
                                                                                ?>
                                                                                <small class="badge bg-light text-dark">
                                                                                    <?php echo $hours; ?>j <?php echo $minutes; ?>m
                                                                                </small>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                            <?php else: ?>
                                                    <div class="text-center py-4">
                                                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                                        <h6 class="text-muted">Belum Ada Jadwal</h6>
                                                        <p class="text-muted mb-0">Service ini belum memiliki jadwal operasional</p>
                                                    </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Quick Actions -->
                                    <div class="mt-4 pt-3 border-top">
                                        <div class="d-flex gap-2 flex-wrap">
                                            <button type="button" class="btn btn-warning edit-services-btn"
                                                data-id="<?php echo $service['id_service']; ?>" data-bs-dismiss="modal">
                                                <i class="fas fa-edit me-1"></i>Edit Service
                                            </button>
                                            <button type="button" class="btn btn-success manage-schedule-btn"
                                                data-id="<?php echo $service['id_service']; ?>"
                                                data-name="<?php echo htmlspecialchars($service['nama_service']); ?>" data-bs-dismiss="modal">
                                                <i class="fas fa-calendar me-1"></i>Kelola Jadwal
                                            </button>
                                            <button type="button" class="btn btn-danger delete-services-btn"
                                                data-id="<?php echo $service['id_service']; ?>"
                                                data-name="<?php echo htmlspecialchars($service['nama_service']); ?>" data-bs-dismiss="modal">
                                                <i class="fas fa-trash me-1"></i>Hapus Service
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                $html = ob_get_clean();
                                echo json_encode(['status' => 'success', 'html' => $html]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Data service tidak ditemukan']);
            }

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Add Service
    if ($request == 'add_services') {
        $nama_service = trim($_POST['nama_services'] ?? '');
        $harga_service = (int) ($_POST['harga_services'] ?? 0);
        $deskripsi_service = trim($_POST['deskripsi_services'] ?? '');

        // Validation
        if (empty($nama_service) || $harga_service <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Nama service dan harga harus diisi dengan benar!']);
            exit;
        }

        try {
            // Check if service name already exists
            $check_query = "SELECT id_service FROM tb_service WHERE nama_service = ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bind_param("s", $nama_service);
            $check_stmt->execute();

            if ($check_stmt->get_result()->num_rows > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Nama service sudah digunakan!']);
                exit;
            }

            // Insert into tb_service
            $insert_query = "INSERT INTO tb_service (nama_service, harga_service, deskripsi_service) 
                           VALUES (?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("sis", $nama_service, $harga_service, $deskripsi_service);

            if ($insert_stmt->execute()) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Service berhasil ditambahkan!'
                ]);
            } else {
                throw new Exception("Gagal menyimpan data service!");
            }

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Update Service
    if ($request == 'update_services') {
        $service_id = $_POST['services_id'] ?? 0;
        $nama_service = trim($_POST['nama_services'] ?? '');
        $harga_service = (int) ($_POST['harga_services'] ?? 0);
        $deskripsi_service = trim($_POST['deskripsi_services'] ?? '');

        // Validation
        if (!$service_id || empty($nama_service) || $harga_service <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID Service, nama service, dan harga harus diisi dengan benar!']);
            exit;
        }

        try {
            // Check if service name already exists (exclude current service)
            $check_query = "SELECT id_service FROM tb_service WHERE nama_service = ? AND id_service != ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bind_param("si", $nama_service, $service_id);
            $check_stmt->execute();

            if ($check_stmt->get_result()->num_rows > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Nama service sudah digunakan!']);
                exit;
            }

            // Update tb_service
            $update_query = "UPDATE tb_service SET nama_service = ?, harga_service = ?, deskripsi_service = ? WHERE id_service = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("sisi", $nama_service, $harga_service, $deskripsi_service, $service_id);

            if ($update_stmt->execute()) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Service berhasil diupdate!'
                ]);
            } else {
                throw new Exception("Gagal mengupdate data service!");
            }

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Delete Service
    if ($request == 'delete_services') {
        $service_id = $_POST['services_id'] ?? 0;

        if (!$service_id) {
            echo json_encode(['status' => 'error', 'message' => 'ID Service tidak valid']);
            exit;
        }

        try {
            // Check if service exists
            $check_query = "SELECT nama_service FROM tb_service WHERE id_service = ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bind_param("i", $service_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();

            if ($result->num_rows === 0) {
                echo json_encode(['status' => 'error', 'message' => 'Data service tidak ditemukan']);
                exit;
            }

            // Delete from tb_service (schedules will be deleted automatically due to CASCADE)
            $delete_query = "DELETE FROM tb_service WHERE id_service = ?";
            $delete_stmt = $conn->prepare($delete_query);
            $delete_stmt->bind_param("i", $service_id);

            if ($delete_stmt->execute()) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Service dan semua jadwalnya berhasil dihapus!'
                ]);
            } else {
                throw new Exception("Gagal menghapus data service!");
            }

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Get Schedules for Service
    if ($request == 'get_service_schedules') {
        $service_id = $_POST['service_id'] ?? 0;

        if (!$service_id) {
            echo json_encode(['status' => 'error', 'message' => 'ID Service tidak valid']);
            exit;
        }

        try {
            $query = "SELECT * FROM tb_jadwal WHERE id_service = ? ORDER BY 
                     CASE hari 
                         WHEN 'Senin' THEN 1
                         WHEN 'Selasa' THEN 2
                         WHEN 'Rabu' THEN 3
                         WHEN 'Kamis' THEN 4
                         WHEN 'Jumat' THEN 5
                         WHEN 'Sabtu' THEN 6
                         WHEN 'Minggu' THEN 7
                     END, duty_start ASC";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $service_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $schedules = [];
            while ($row = $result->fetch_assoc()) {
                $schedules[] = $row;
            }

            echo json_encode(['status' => 'success', 'data' => $schedules]);

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Add Schedule
    if ($request == 'add_schedule') {
        $service_id = $_POST['service_id'] ?? 0;
        $hari = trim($_POST['hari'] ?? '');
        $duty_start = trim($_POST['duty_start'] ?? '');
        $duty_end = trim($_POST['duty_end'] ?? '');

        // Validation
        if (!$service_id || empty($hari) || empty($duty_start) || empty($duty_end)) {
            echo json_encode(['status' => 'error', 'message' => 'Semua field harus diisi!']);
            exit;
        }

        // Validate time
        if ($duty_start >= $duty_end) {
            echo json_encode(['status' => 'error', 'message' => 'Waktu mulai harus lebih awal dari waktu selesai!']);
            exit;
        }

        try {
            // Check if schedule already exists for this day
            $check_query = "SELECT id_jadwal FROM tb_jadwal WHERE id_service = ? AND hari = ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bind_param("is", $service_id, $hari);
            $check_stmt->execute();

            if ($check_stmt->get_result()->num_rows > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Jadwal untuk hari ' . $hari . ' sudah ada!']);
                exit;
            }

            // Insert schedule
            $insert_query = "INSERT INTO tb_jadwal (id_service, hari, duty_start, duty_end) VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("isss", $service_id, $hari, $duty_start, $duty_end);

            if ($insert_stmt->execute()) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Jadwal berhasil ditambahkan!'
                ]);
            } else {
                throw new Exception("Gagal menyimpan jadwal!");
            }

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Update Schedule
    if ($request == 'update_schedule') {
        $schedule_id = $_POST['schedule_id'] ?? 0;
        $hari = trim($_POST['hari'] ?? '');
        $duty_start = trim($_POST['duty_start'] ?? '');
        $duty_end = trim($_POST['duty_end'] ?? '');

        // Validation
        if (!$schedule_id || empty($hari) || empty($duty_start) || empty($duty_end)) {
            echo json_encode(['status' => 'error', 'message' => 'Semua field harus diisi!']);
            exit;
        }

        // Validate time
        if ($duty_start >= $duty_end) {
            echo json_encode(['status' => 'error', 'message' => 'Waktu mulai harus lebih awal dari waktu selesai!']);
            exit;
        }

        try {
            // Update schedule
            $update_query = "UPDATE tb_jadwal SET hari = ?, duty_start = ?, duty_end = ? WHERE id_jadwal = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("sssi", $hari, $duty_start, $duty_end, $schedule_id);

            if ($update_stmt->execute()) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Jadwal berhasil diupdate!'
                ]);
            } else {
                throw new Exception("Gagal mengupdate jadwal!");
            }

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Delete Schedule
    if ($request == 'delete_schedule') {
        $schedule_id = $_POST['schedule_id'] ?? 0;

        if (!$schedule_id) {
            echo json_encode(['status' => 'error', 'message' => 'ID Jadwal tidak valid']);
            exit;
        }

        try {
            $delete_query = "DELETE FROM tb_jadwal WHERE id_jadwal = ?";
            $delete_stmt = $conn->prepare($delete_query);
            $delete_stmt->bind_param("i", $schedule_id);

            if ($delete_stmt->execute()) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Jadwal berhasil dihapus!'
                ]);
            } else {
                throw new Exception("Gagal menghapus jadwal!");
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