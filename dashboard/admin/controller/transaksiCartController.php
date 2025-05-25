<?php
// admin/controller/transaksiCartController.php
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

    // Get Transaksi Cart Data
    if ($request == 'get_transaksi_cart') {
        try {
            // Get all transaksi cart data with related information
            $query = "SELECT 
                        tc.id_transaksi_cart,
                        tc.id_cart,
                        tc.status_transaksi_cart,
                        tc.bukti_transaksi_cart,
                        tc.tgl_transaksi_cart,
                        c.jumlah,
                        b.nama_barang,
                        b.harga_barang,
                        b.foto_barang,
                        b.tipe_barang,
                        p.nama as nama_pelanggan,
                        u.username,
                        (c.jumlah * b.harga_barang) as total_harga
                      FROM tb_transaksi_cart tc
                      JOIN tb_cart c ON tc.id_cart = c.id_cart
                      JOIN tb_barang b ON c.id_barang = b.id_barang
                      JOIN tb_user u ON c.id_user = u.id_user
                      JOIN tb_pelanggan p ON u.id_user = p.id_user
                      WHERE tc.status_transaksi_cart = 'pending'
                      ORDER BY tc.tgl_transaksi_cart DESC, tc.id_transaksi_cart DESC";

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
                    <table id="transaksiCartTable" class="table table-hover table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col" style="width: 5%;">#</th>
                                <th scope="col" style="width: 15%;">Pelanggan</th>
                                <th scope="col" style="width: 20%;">Barang</th>
                                <th scope="col" style="width: 10%;">Jumlah</th>
                                <th scope="col" style="width: 12%;">Harga Satuan</th>
                                <th scope="col" style="width: 12%;">Total Harga</th>
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
                                        <div class="d-flex align-items-center">
                                            <img src="../../../assets/uploads/barang/<?php echo htmlspecialchars($transaksi['foto_barang']); ?>"
                                                alt="<?php echo htmlspecialchars($transaksi['nama_barang']); ?>" class="rounded me-2"
                                                style="width: 40px; height: 40px; object-fit: cover;">
                                            <div>
                                                <div class="fw-bold text-primary"><?php echo htmlspecialchars($transaksi['nama_barang']); ?>
                                                </div>
                                                <small class="text-muted"><?php echo htmlspecialchars($transaksi['tipe_barang']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo $transaksi['jumlah']; ?> pcs</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success"><?php echo formatRupiah($transaksi['harga_barang']); ?></span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success"><?php echo formatRupiah($transaksi['total_harga']); ?></span>
                                    </td>
                                    <td>
                                        <small><?php echo formatTanggal($transaksi['tgl_transaksi_cart']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo getStatusBadge($transaksi['status_transaksi_cart']); ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-info view-transaksi-cart-btn"
                                                data-id="<?php echo $transaksi['id_transaksi_cart']; ?>" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if (strtolower($transaksi['status_transaksi_cart']) == 'pending'): ?>
                                                <button type="button" class="btn btn-success confirm-transaksi-cart-btn"
                                                    data-id="<?php echo $transaksi['id_transaksi_cart']; ?>"
                                                    data-barang="<?php echo htmlspecialchars($transaksi['nama_barang']); ?>"
                                                    data-customer="<?php echo htmlspecialchars($transaksi['nama_pelanggan']); ?>"
                                                    data-total="<?php echo formatRupiah($transaksi['total_harga']); ?>"
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
                    <i class="fas fa-info-circle me-2"></i>Belum ada data transaksi barang yang pending.
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

    // Get Transaksi Cart Detail
    if ($request == 'get_transaksi_cart_detail') {
        $transaksi_id = $_POST['transaksi_id'] ?? 0;

        if (!$transaksi_id) {
            echo json_encode(['status' => 'error', 'message' => 'ID Transaksi tidak valid']);
            exit;
        }

        try {
            $query = "SELECT 
                        tc.id_transaksi_cart,
                        tc.id_cart,
                        tc.status_transaksi_cart,
                        tc.bukti_transaksi_cart,
                        tc.tgl_transaksi_cart,
                        c.jumlah,
                        b.nama_barang,
                        b.deskripsi_barang,
                        b.harga_barang,
                        b.foto_barang,
                        b.tipe_barang,
                        p.nama as nama_pelanggan,
                        u.username,
                        (c.jumlah * b.harga_barang) as total_harga
                      FROM tb_transaksi_cart tc
                      JOIN tb_cart c ON tc.id_cart = c.id_cart
                      JOIN tb_barang b ON c.id_barang = b.id_barang
                      JOIN tb_user u ON c.id_user = u.id_user
                      JOIN tb_pelanggan p ON u.id_user = p.id_user
                      WHERE tc.id_transaksi_cart = ? AND tc.status_transaksi_cart = 'pending'";

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

                        <h6 class="text-primary mb-3 mt-4"><i class="fas fa-box me-2"></i>Informasi Barang</h6>
                        <div class="card border-0 bg-light">
                            <div class="card-body p-3">
                                <div class="row align-items-center">
                                    <div class="col-4">
                                        <img src="../../../assets/uploads/barang/<?php echo htmlspecialchars($transaksi['foto_barang']); ?>"
                                            alt="<?php echo htmlspecialchars($transaksi['nama_barang']); ?>"
                                            class="img-fluid rounded shadow-sm">
                                    </div>
                                    <div class="col-8">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($transaksi['nama_barang']); ?></h6>
                                        <p class="text-muted small mb-2"><?php echo htmlspecialchars($transaksi['deskripsi_barang']); ?>
                                        </p>
                                        <span
                                            class="badge bg-secondary mb-2"><?php echo htmlspecialchars($transaksi['tipe_barang']); ?></span>
                                        <div class="fw-bold text-success"><?php echo formatRupiah($transaksi['harga_barang']); ?> / pcs
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3"><i class="fas fa-receipt me-2"></i>Informasi Transaksi</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td><strong>ID Transaksi:</strong></td>
                                <td>#<?php echo $transaksi['id_transaksi_cart']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>ID Cart:</strong></td>
                                <td>#<?php echo $transaksi['id_cart']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Jumlah:</strong></td>
                                <td><span class="badge bg-info"><?php echo $transaksi['jumlah']; ?> pcs</span></td>
                            </tr>
                            <tr>
                                <td><strong>Harga Satuan:</strong></td>
                                <td class="fw-bold text-success"><?php echo formatRupiah($transaksi['harga_barang']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Total Harga:</strong></td>
                                <td><span
                                        class="fw-bold text-success fs-5"><?php echo formatRupiah($transaksi['total_harga']); ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Transaksi:</strong></td>
                                <td><?php echo formatTanggal($transaksi['tgl_transaksi_cart']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td><?php echo getStatusBadge($transaksi['status_transaksi_cart']); ?></td>
                            </tr>
                        </table>

                        <?php if ($transaksi['bukti_transaksi_cart']): ?>
                            <h6 class="text-primary mb-3 mt-4"><i class="fas fa-image me-2"></i>Bukti Pembayaran</h6>
                            <div class="text-center">
                                <img src="../../../assets/uploads/payment_proofs/<?php echo htmlspecialchars($transaksi['bukti_transaksi_cart']); ?>"
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
                echo json_encode(['status' => 'error', 'message' => 'Data transaksi tidak ditemukan']);
            }

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Confirm Transaksi Cart (Change status from pending to completed)
    if ($request == 'confirm_transaksi_cart') {
        $transaksi_id = $_POST['transaksi_id'] ?? 0;

        if (!$transaksi_id) {
            echo json_encode(['status' => 'error', 'message' => 'ID Transaksi tidak valid']);
            exit;
        }

        try {
            // Check if transaksi exists and is pending
            $check_query = "SELECT status_transaksi_cart FROM tb_transaksi_cart WHERE id_transaksi_cart = ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bind_param("i", $transaksi_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows === 0) {
                echo json_encode(['status' => 'error', 'message' => 'Data transaksi tidak ditemukan']);
                exit;
            }

            $transaksi_data = $check_result->fetch_assoc();

            if (strtolower($transaksi_data['status_transaksi_cart']) !== 'pending') {
                echo json_encode(['status' => 'error', 'message' => 'Transaksi ini sudah dikonfirmasi sebelumnya']);
                exit;
            }

            // Update status to completed
            $update_query = "UPDATE tb_transaksi_cart SET status_transaksi_cart = 'completed' WHERE id_transaksi_cart = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("i", $transaksi_id);

            if ($update_stmt->execute()) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Transaksi barang berhasil dikonfirmasi!'
                ]);
            } else {
                throw new Exception("Gagal mengupdate status transaksi!");
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