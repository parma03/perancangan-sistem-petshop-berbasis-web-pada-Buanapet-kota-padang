<?php
session_start();
include '../../db/koneksi.php';
include 'controller/adminController.php';

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard Panel - Buana Pet Shop</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" />
    <!-- SweetAlert2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.32/sweetalert2.min.css"
        rel="stylesheet">

    <link rel="stylesheet" href="assets/index.css">
</head>

<body>
    <!-- Sidebar -->
    <?php include '_components/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Navbar -->
        <?php include '_components/navbar.php'; ?>

        <!-- Content -->
        <div class="content-wrapper">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-primary m-0">
                            <i class="fas fa-users-cog me-2"></i>Data Barang
                        </h6>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-success btn-sm add-barang-btn">
                                <i class="fas fa-plus me-1"></i>
                                Tambah Barang
                            </button>
                        </div>
                    </div>

                    <div class="barang-data-container">
                        <div class="text-center py-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Memuat data barang...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail Barang -->
    <div class="modal fade" id="barangDetailModal" tabindex="-1" aria-labelledby="barangDetailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="barangDetailModalLabel">
                        <i class="fas fa-user-shield me-2"></i>Detail Barang
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="barangDetailContent">
                        <div class="text-center py-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Memuat detail barang...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form Barang (Tambah/Edit) - GANTI BAGIAN INI -->
    <div class="modal fade" id="barangFormModal" tabindex="-1" aria-labelledby="barangFormModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-gradient text-white"
                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="modal-title" id="barangFormModalLabel">
                        <i class="fas fa-box me-2"></i>Tambah Barang
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="barangForm" enctype="multipart/form-data">
                    <div class="modal-body p-4">
                        <input type="hidden" id="barang_id" name="barang_id">
                        <input type="hidden" id="removeExistingPhoto" name="removeExistingPhoto" value="0">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nama_barang" class="form-label fw-semibold">
                                        <i class="fas fa-tag text-primary me-1"></i>Nama Barang <span
                                            class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="nama_barang"
                                        name="nama_barang" required placeholder="Masukkan nama barang">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tipe_barang" class="form-label fw-semibold">
                                        <i class="fas fa-layer-group text-success me-1"></i>Tipe Barang <span
                                            class="text-danger">*</span>
                                    </label>
                                    <select class="form-select form-select-lg" id="tipe_barang" name="tipe_barang"
                                        required>
                                        <option value="">Pilih Tipe Barang</option>
                                        <option value="Makanan Hewan">Makanan Hewan</option>
                                        <option value="Obat-obatan">Obat-obatan</option>
                                        <option value="Vitamin">Vitamin</option>
                                        <option value="Mainan">Mainan</option>
                                        <option value="Perawatan">Perawatan</option>
                                        <option value="Kandang">Kandang</option>
                                        <option value="Aksesoris">Aksesoris</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="harga_barang" class="form-label fw-semibold">
                                        <i class="fas fa-money-bill-wave text-warning me-1"></i>Harga Barang <span
                                            class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control form-control-lg" id="harga_barang"
                                            name="harga_barang" required min="0" step="1000" placeholder="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi_barang" class="form-label fw-semibold">
                                <i class="fas fa-align-left text-secondary me-1"></i>Deskripsi Barang
                            </label>
                            <textarea class="form-control" id="deskripsi_barang" name="deskripsi_barang" rows="3"
                                placeholder="Masukkan deskripsi barang (opsional)"></textarea>
                        </div>

                        <!-- Current Photo Display (untuk edit mode) -->
                        <div id="currentPhotoDisplay" style="display: none;"></div>

                        <!-- Photo Upload -->
                        <div class="mb-3">
                            <label for="foto_barang" class="form-label fw-semibold">
                                <i class="fas fa-camera text-primary me-1"></i>Foto Barang
                            </label>
                            <div class="photo-upload-area">
                                <input type="file" class="form-control" id="foto_barang" name="foto_barang"
                                    accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" style="display: none;">

                                <div id="uploadPlaceholder" class="upload-placeholder">
                                    <div class="upload-icon">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                    </div>
                                    <h6 class="mb-2">Klik untuk memilih foto atau drag & drop di sini</h6>
                                    <small class="text-muted">Format: JPG, PNG, GIF, WEBP (Max: 5MB)</small>
                                    <div class="mt-2">
                                        <span class="badge bg-light text-dark">Rekomendasi: 500x500px</span>
                                    </div>
                                </div>

                                <div id="photoPreview" style="display: none;" class="mt-3"></div>

                                <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="removePhotoBtn"
                                    style="display: none;">
                                    <i class="fas fa-trash me-1"></i>Hapus Foto
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-1"></i>Simpan Barang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- IMPORTANT: Load jQuery FIRST before any other jQuery-dependent scripts -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <!-- SweetAlert2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.32/sweetalert2.min.js"></script>

    <!-- DataTables JS (Load AFTER jQuery) -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <!-- Custom Scripts -->
    <script src="assets/index.js"></script>
    <script src="controller/adminController.js"></script>
    <script src="controller/barangController.js"></script>
</body>

</html>