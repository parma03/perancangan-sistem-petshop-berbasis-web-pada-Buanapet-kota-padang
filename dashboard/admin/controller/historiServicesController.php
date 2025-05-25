<?php
// admin/controller/historiServicesController.php
session_start();
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
function formatRupiah($angka)
{
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Function to format date
function formatTanggal($tanggal)
{
    $bulan = array(
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    );

    $split = explode('-', $tanggal);
    return $split[2] . ' ' . $bulan[(int) $split[1]] . ' ' . $split[0];
}

// Function to get status badge
function getStatusBadge($status)
{
    switch (strtolower($status)) {
        case 'pending':
            return '<span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Pending</span>';
        case 'completed':
            return '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Completed</span>';
        case 'cancelled':
            return '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Cancelled</span>';
        default:
            return '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
    }
}

// Check request
$request = $_POST['request'] ?? '';

if (isAjaxRequest()) {

    // Get Transaksi Service Data
    if ($request == 'get_transaksi_services') {
        try {
            // Get all transaksi service data with related information
            $query = "SELECT 
                        ts.id_transaksi_service,
                        ts.id_booking,
                        ts.status_transaksi_service,
                        ts.bukti_transaksi_service,
                        ts.tgl_transaksi_service,
                        b.waktu_booking,
                        s.nama_service,
                        s.harga_service,
                        p.nama as nama_pelanggan,
                        u.username
                      FROM tb_transaksi_service ts
                      JOIN tb_booking b ON ts.id_booking = b.id_booking
                      JOIN tb_service s ON b.id_service = s.id_service
                      JOIN tb_user u ON b.id_user = u.id_user
                      JOIN tb_pelanggan p ON u.id_user = p.id_user
                      WHERE ts.status_transaksi_service = 'completed'
                      ORDER BY ts.tgl_transaksi_service DESC, ts.id_transaksi_service DESC";

            $result = $conn->query($query);

            if (!$result) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Database error: ' . $conn->error
                ]);
                exit;
            }

            $transaksis = [];
            while ($row = $result->fetch_assoc()) {
                $transaksis[] = $row;
            }

            // Format the output HTML
            ob_start();

            if (count($transaksis) > 0) {
                ?>
                <div class="table-responsive">
                    <table id="transaksiServicesTable" class="table table-hover table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col" style="width: 5%;">#</th>
                                <th scope="col" style="width: 15%;">Pelanggan</th>
                                <th scope="col" style="width: 20%;">Service</th>
                                <th scope="col" style="width: 12%;">Harga</th>
                                <th scope="col" style="width: 15%;">Waktu Booking</th>
                                <th scope="col" style="width: 12%;">Tanggal Transaksi</th>
                                <th scope="col" style="width: 10%;">Status</th>
                                <th scope="col" style="width: 11%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            foreach ($transaksis as $transaksi) {
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <strong><?php echo htmlspecialchars($transaksi['nama_pelanggan']); ?></strong>
                                            <small class="text-muted">@<?php echo htmlspecialchars($transaksi['username']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <span
                                            class="fw-bold text-primary"><?php echo htmlspecialchars($transaksi['nama_service']); ?></span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success"><?php echo formatRupiah($transaksi['harga_service']); ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <small><i
                                                    class="fas fa-calendar me-1"></i><?php echo date('d/m/Y', strtotime($transaksi['waktu_booking'])); ?></small>
                                            <small><i
                                                    class="fas fa-clock me-1"></i><?php echo date('H:i', strtotime($transaksi['waktu_booking'])); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <small><?php echo formatTanggal($transaksi['tgl_transaksi_service']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo getStatusBadge($transaksi['status_transaksi_service']); ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-info view-transaksi-btn"
                                                data-id="<?php echo $transaksi['id_transaksi_service']; ?>" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if (strtolower($transaksi['status_transaksi_service']) == 'pending'): ?>
                                                <button type="button" class="btn btn-success confirm-transaksi-btn"
                                                    data-id="<?php echo $transaksi['id_transaksi_service']; ?>"
                                                    data-service="<?php echo htmlspecialchars($transaksi['nama_service']); ?>"
                                                    data-customer="<?php echo htmlspecialchars($transaksi['nama_pelanggan']); ?>"
                                                    title="Konfirmasi Transaksi">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
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
                    <i class="fas fa-info-circle me-2"></i>Belum ada data histori transaksi service.
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

    // Get Transaksi Service Detail
    if ($request == 'get_transaksi_detail') {
        $transaksi_id = $_POST['transaksi_id'] ?? 0;

        if (!$transaksi_id) {
            echo json_encode(['status' => 'error', 'message' => 'ID Transaksi tidak valid']);
            exit;
        }

        try {
            $query = "SELECT 
                        ts.id_transaksi_service,
                        ts.id_booking,
                        ts.status_transaksi_service,
                        ts.bukti_transaksi_service,
                        ts.tgl_transaksi_service,
                        b.waktu_booking,
                        s.nama_service,
                        s.deskripsi_service,
                        s.harga_service,
                        p.nama as nama_pelanggan,
                        u.username
                      FROM tb_transaksi_service ts
                      JOIN tb_booking b ON ts.id_booking = b.id_booking
                      JOIN tb_service s ON b.id_service = s.id_service
                      JOIN tb_user u ON b.id_user = u.id_user
                      JOIN tb_pelanggan p ON u.id_user = p.id_user
                      WHERE ts.id_transaksi_service = ? AND ts.status_transaksi_service = 'completed'";

            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $transaksi_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $transaksi = $result->fetch_assoc();

                ob_start();
                ?>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3"><i class="fas fa-user me-2"></i>Informasi Pelanggan</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td><strong>Nama:</strong></td>
                                <td><?php echo htmlspecialchars($transaksi['nama_pelanggan']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Username:</strong></td>
                                <td>@<?php echo htmlspecialchars($transaksi['username']); ?></td>
                            </tr>
                        </table>

                        <h6 class="text-primary mb-3 mt-4"><i class="fas fa-concierge-bell me-2"></i>Informasi Service</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td><strong>Nama Service:</strong></td>
                                <td><?php echo htmlspecialchars($transaksi['nama_service']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Deskripsi:</strong></td>
                                <td><?php echo htmlspecialchars($transaksi['deskripsi_service']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Harga:</strong></td>
                                <td><span class="fw-bold text-success"><?php echo formatRupiah($transaksi['harga_service']); ?></span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3"><i class="fas fa-receipt me-2"></i>Informasi Transaksi</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td><strong>ID Transaksi:</strong></td>
                                <td>#<?php echo $transaksi['id_transaksi_service']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>ID Booking:</strong></td>
                                <td>#<?php echo $transaksi['id_booking']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Waktu Booking:</strong></td>
                                <td>
                                    <div><?php echo date('d/m/Y', strtotime($transaksi['waktu_booking'])); ?></div>
                                    <small class="text-muted"><?php echo date('H:i', strtotime($transaksi['waktu_booking'])); ?>
                                        WIB</small>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Transaksi:</strong></td>
                                <td><?php echo formatTanggal($transaksi['tgl_transaksi_service']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td><?php echo getStatusBadge($transaksi['status_transaksi_service']); ?></td>
                            </tr>
                        </table>

                        <?php if ($transaksi['bukti_transaksi_service']): ?>
                            <h6 class="text-primary mb-3 mt-4"><i class="fas fa-image me-2"></i>Bukti Pembayaran</h6>
                            <div class="text-center">
                                <img src="../../../assets/uploads/payment_proofs/<?php echo htmlspecialchars($transaksi['bukti_transaksi_service']); ?>"
                                    alt="Bukti Pembayaran" class="img-fluid rounded shadow" style="max-height: 200px; cursor: pointer;"
                                    onclick="window.open(this.src, '_blank')">
                                <br>
                                <small class="text-muted">Klik gambar untuk memperbesar</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php
                $html = ob_get_clean();
                echo json_encode(['status' => 'success', 'html' => $html]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Data histori transaksi tidak ditemukan']);
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