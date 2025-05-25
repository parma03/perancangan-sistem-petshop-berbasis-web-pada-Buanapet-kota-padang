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
                            <i class="fas fa-concierge-bell me-2"></i>Data Services
                        </h6>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-success btn-sm add-services-btn">
                                <i class="fas fa-plus me-1"></i>
                                Tambah Services
                            </button>
                        </div>
                    </div>

                    <div class="services-data-container">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary mb-3" role="status"
                                style="width: 3rem; height: 3rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <h5 class="text-muted">Memuat data services...</h5>
                            <p class="text-muted mb-0">Silakan tunggu sebentar</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail Services -->
    <div class="modal fade" id="servicesDetailModal" tabindex="-1" aria-labelledby="servicesDetailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-gradient text-white"
                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="modal-title" id="servicesDetailModalLabel">
                        <i class="fas fa-concierge-bell me-2"></i>Detail Services
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="servicesDetailContent">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary mb-3" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <h6 class="text-muted">Memuat detail services...</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form Services (Tambah/Edit) -->
    <div class="modal fade" id="servicesFormModal" tabindex="-1" aria-labelledby="servicesFormModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-gradient text-white"
                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="modal-title" id="servicesFormModalLabel">
                        <i class="fas fa-plus me-2"></i>Tambah Services
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="servicesForm" enctype="multipart/form-data">
                    <div class="modal-body p-4">
                        <input type="hidden" id="services_id" name="services_id">
                        <input type="hidden" id="removeExistingPhoto" name="removeExistingPhoto" value="0">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nama_services" class="form-label fw-semibold">
                                        <i class="fas fa-tag text-primary me-1"></i>Nama Services <span
                                            class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="nama_services"
                                        name="nama_services" required placeholder="Masukkan nama services">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="harga_services" class="form-label fw-semibold">
                                        <i class="fas fa-money-bill-wave text-warning me-1"></i>Harga Services <span
                                            class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control form-control-lg" id="harga_services"
                                            name="harga_services" required min="0" step="1000" placeholder="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi_services" class="form-label fw-semibold">
                                <i class="fas fa-align-left text-secondary me-1"></i>Deskripsi Services
                            </label>
                            <textarea class="form-control" id="deskripsi_services" name="deskripsi_services" rows="3"
                                placeholder="Masukkan deskripsi services (opsional)"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-1"></i>Simpan Services
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Schedule Management -->
    <div class="modal fade" id="scheduleManagementModal" tabindex="-1" aria-labelledby="scheduleManagementModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-gradient text-white"
                    style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                    <h5 class="modal-title" id="scheduleManagementModalLabel">
                        <i class="fas fa-calendar me-2"></i>Kelola Jadwal Service
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="scheduleManagementContent">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary mb-3" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <h6 class="text-muted">Memuat jadwal service...</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form Schedule (Tambah/Edit) -->
    <div class="modal fade" id="scheduleFormModal" tabindex="-1" aria-labelledby="scheduleFormModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-gradient text-white"
                    style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
                    <h5 class="modal-title" id="scheduleFormModalLabel">
                        <i class="fas fa-plus me-2"></i>Tambah Jadwal
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="scheduleForm">
                    <div class="modal-body p-4">
                        <input type="hidden" id="schedule_id" name="schedule_id">
                        <input type="hidden" id="service_id" name="service_id">

                        <div class="mb-3">
                            <label for="hari" class="form-label fw-semibold">
                                <i class="fas fa-calendar-day text-primary me-1"></i>Hari <span
                                    class="text-danger">*</span>
                            </label>
                            <select class="form-select form-select-lg" id="hari" name="hari" required>
                                <option value="">Pilih Hari</option>
                                <option value="Senin">Senin</option>
                                <option value="Selasa">Selasa</option>
                                <option value="Rabu">Rabu</option>
                                <option value="Kamis">Kamis</option>
                                <option value="Jumat">Jumat</option>
                                <option value="Sabtu">Sabtu</option>
                                <option value="Minggu">Minggu</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="duty_start" class="form-label fw-semibold">
                                        <i class="fas fa-clock text-success me-1"></i>Jam Mulai <span
                                            class="text-danger">*</span>
                                    </label>
                                    <input type="time" class="form-control form-control-lg" id="duty_start"
                                        name="duty_start" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="duty_end" class="form-label fw-semibold">
                                        <i class="fas fa-clock text-danger me-1"></i>Jam Selesai <span
                                            class="text-danger">*</span>
                                    </label>
                                    <input type="time" class="form-control form-control-lg" id="duty_end"
                                        name="duty_end" required>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info border-0 rounded-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <small>Pastikan jam selesai lebih besar dari jam mulai</small>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-warning text-white">
                            <i class="fas fa-save me-1"></i>Simpan Jadwal
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
    <script src="controller/servicesController.js"></script>
</body>

</html>