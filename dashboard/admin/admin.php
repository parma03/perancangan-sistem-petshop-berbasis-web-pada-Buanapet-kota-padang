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
                            <i class="fas fa-users-cog me-2"></i>Data Admin
                        </h6>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-success btn-sm add-admin-btn">
                                <i class="fas fa-plus me-1"></i>
                                Tambah Admin
                            </button>
                        </div>
                    </div>

                    <div class="admin-data-container">
                        <div class="text-center py-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Memuat data admin...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail Admin -->
    <div class="modal fade" id="adminDetailModal" tabindex="-1" aria-labelledby="adminDetailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="adminDetailModalLabel">
                        <i class="fas fa-user-shield me-2"></i>Detail Admin
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="adminDetailContent">
                        <div class="text-center py-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Memuat detail admin...</p>
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

    <!-- Modal Form Admin (Tambah/Edit) - UPDATED DESIGN -->
    <div class="modal fade" id="adminFormModal" tabindex="-1" aria-labelledby="adminFormModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-gradient text-white"
                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="modal-title" id="adminFormModalLabel">
                        <i class="fas fa-user-plus me-2"></i>Tambah Admin
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="adminForm" enctype="multipart/form-data">
                    <div class="modal-body p-4">
                        <input type="hidden" id="admin_id" name="admin_id">
                        <input type="hidden" id="user_id" name="user_id">
                        <input type="hidden" id="removeExistingProfile" name="removeExistingProfile" value="0">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="username" class="form-label fw-semibold">
                                        <i class="fas fa-user text-primary me-1"></i>Username <span
                                            class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="username"
                                        name="username" required placeholder="Masukkan username">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nama" class="form-label fw-semibold">
                                        <i class="fas fa-id-card text-success me-1"></i>Nama Lengkap <span
                                            class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="nama" name="nama"
                                        required placeholder="Masukkan nama lengkap">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label fw-semibold">
                                        <i class="fas fa-lock text-warning me-1"></i>Password <span
                                            class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control form-control-lg" id="password"
                                            name="password" placeholder="Masukkan password">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>Kosongkan jika tidak ingin mengubah
                                        password
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label fw-semibold">
                                        <i class="fas fa-lock text-warning me-1"></i>Konfirmasi Password
                                    </label>
                                    <input type="password" class="form-control form-control-lg" id="confirm_password"
                                        name="confirm_password" placeholder="Konfirmasi password">
                                </div>
                            </div>
                        </div>

                        <!-- Current Profile Display (untuk edit mode) -->
                        <div id="currentProfileDisplay" style="display: none;"></div>

                        <!-- Profile Image Upload -->
                        <div class="mb-3">
                            <label for="profile" class="form-label fw-semibold">
                                <i class="fas fa-camera text-primary me-1"></i>Foto Profile
                            </label>
                            <div class="profile-upload-area">
                                <input type="file" class="form-control" id="profile" name="profile"
                                    accept="image/jpeg,image/jpg,image/png,image/gif" style="display: none;">

                                <div id="uploadPlaceholder" class="upload-placeholder">
                                    <div class="upload-icon">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                    </div>
                                    <h6 class="mb-2">Klik untuk memilih foto atau drag & drop di sini</h6>
                                    <small class="text-muted">Format: JPG, PNG, GIF (Max: 2MB)</small>
                                    <div class="mt-2">
                                        <span class="badge bg-light text-dark">Rekomendasi: 300x300px</span>
                                    </div>
                                </div>

                                <div id="profilePreview" style="display: none;" class="mt-3"></div>

                                <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="removeProfileBtn"
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
                            <i class="fas fa-save me-1"></i>Simpan Admin
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
    <script src="controller/userController.js"></script>
</body>

</html>