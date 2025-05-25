<?php
session_start();
include '../../db/koneksi.php';
include 'controller/pimpinanController.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Laporan - Buana Pet Shop</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="fw-bold text-dark">Export Laporan Transaksi</h2>
                    <p class="text-muted">Generate laporan transaksi dalam format PDF</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-file-pdf me-2"></i>Filter Laporan
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="exportForm" method="POST" action="controller/export_pdf.php">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="jenis_laporan" class="form-label">Jenis Laporan</label>
                                        <select class="form-select" id="jenis_laporan" name="jenis_laporan" required>
                                            <option value="">Pilih Jenis Laporan</option>
                                            <option value="cart">Transaksi Cart (Produk)</option>
                                            <option value="service">Transaksi Service</option>
                                            <option value="all">Semua Transaksi</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="status_transaksi" class="form-label">Status Transaksi</label>
                                        <select class="form-select" id="status_transaksi" name="status_transaksi"
                                            required>
                                            <option value="">Pilih Status</option>
                                            <option value="pending">Pending</option>
                                            <option value="completed">Completed</option>
                                            <option value="all">Semua Status</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-12">
                                        <label class="form-label">Range Tanggal</label>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="date_range" id="all_date"
                                                value="all" checked>
                                            <label class="form-check-label" for="all_date">
                                                Semua Tanggal
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="date_range"
                                                id="custom_date" value="custom">
                                            <label class="form-check-label" for="custom_date">
                                                Range Tanggal Khusus
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3" id="date_inputs" style="display: none;">
                                    <div class="col-md-6">
                                        <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                                        <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                                        <input type="date" class="form-control" id="tanggal_selesai"
                                            name="tanggal_selesai">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="nama_pimpinan" class="form-label">Nama Pimpinan</label>
                                        <input type="text" class="form-control" id="nama_pimpinan" name="nama_pimpinan"
                                            placeholder="Nama untuk tanda tangan" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="jabatan_pimpinan" class="form-label">Jabatan</label>
                                        <input type="text" class="form-control" id="jabatan_pimpinan"
                                            name="jabatan_pimpinan" placeholder="Contoh: Manager, Direktur"
                                            value="Pimpinan">
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-file-pdf me-2"></i>Export PDF
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="previewData()">
                                        <i class="fas fa-eye me-2"></i>Preview Data
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i>Informasi
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <strong>Tips:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Pilih jenis laporan sesuai kebutuhan</li>
                                    <li>Gunakan filter status untuk laporan spesifik</li>
                                    <li>Range tanggal membantu analisis periode tertentu</li>
                                    <li>Tanda tangan pimpinan akan muncul di bagian bawah</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-chart-bar me-2"></i>Statistik Cepat
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="border-end">
                                        <h5 class="text-primary mb-1" id="total_cart">-</h5>
                                        <small class="text-muted">Transaksi Cart</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <h5 class="text-success mb-1" id="total_service">-</h5>
                                    <small class="text-muted">Transaksi Service</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.32/sweetalert2.min.js"></script>

    <!-- Custom Scripts -->
    <script src="assets/index.js"></script>
    <script src="controller/pimpinanController.js"></script>

    <script>
        // Toggle date inputs
        document.querySelectorAll('input[name="date_range"]').forEach(radio => {
            radio.addEventListener('change', function () {
                const dateInputs = document.getElementById('date_inputs');
                if (this.value === 'custom') {
                    dateInputs.style.display = 'block';
                    document.getElementById('tanggal_mulai').required = true;
                    document.getElementById('tanggal_selesai').required = true;
                } else {
                    dateInputs.style.display = 'none';
                    document.getElementById('tanggal_mulai').required = false;
                    document.getElementById('tanggal_selesai').required = false;
                }
            });
        });

        // Load statistics on page load
        document.addEventListener('DOMContentLoaded', function () {
            loadStatistics();
        });

        function loadStatistics() {
            fetch('controller/get_statistics.php')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('total_cart').textContent = data.total_cart || 0;
                    document.getElementById('total_service').textContent = data.total_service || 0;
                })
                .catch(error => {
                    console.error('Error loading statistics:', error);
                });
        }

        function previewData() {
            const form = document.getElementById('exportForm');
            const formData = new FormData(form);

            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Show loading
            Swal.fire({
                title: 'Memuat preview...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('controller/preview_data.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    Swal.close();
                    if (data.success) {
                        showPreviewModal(data.data, data.summary);
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.close();
                    Swal.fire('Error', 'Terjadi kesalahan saat memuat preview', 'error');
                });
        }

        function showPreviewModal(data, summary) {
            let tableContent = '';

            if (data.length > 0) {
                tableContent = `
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tanggal</th>
                                    <th>Jenis</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                data.forEach(item => {
                    tableContent += `
                        <tr>
                            <td>${item.id}</td>
                            <td>${item.tanggal}</td>
                            <td>${item.jenis}</td>
                            <td><span class="badge bg-${item.status === 'completed' ? 'success' : 'warning'}">${item.status}</span></td>
                            <td>Rp ${item.total}</td>
                        </tr>
                    `;
                });

                tableContent += `
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 p-3 bg-light rounded">
                        <strong>Ringkasan:</strong><br>
                        Total Transaksi: ${summary.total_transaksi}<br>
                        Total Nilai: Rp ${summary.total_nilai}
                    </div>
                `;
            } else {
                tableContent = '<div class="alert alert-warning">Tidak ada data yang sesuai dengan filter yang dipilih.</div>';
            }

            Swal.fire({
                title: 'Preview Data',
                html: tableContent,
                width: '80%',
                showCancelButton: true,
                confirmButtonText: 'Export PDF',
                cancelButtonText: 'Tutup'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('exportForm').submit();
                }
            });
        }

        // Form submission
        document.getElementById('exportForm').addEventListener('submit', function (e) {
            e.preventDefault();

            if (!this.checkValidity()) {
                this.reportValidity();
                return;
            }

            Swal.fire({
                title: 'Generating PDF...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Submit form
            this.submit();

            // Close loading after a delay (PDF generation)
            setTimeout(() => {
                Swal.close();
                Swal.fire('Berhasil!', 'Laporan PDF telah dihasilkan', 'success');
            }, 3000);
        });
    </script>
</body>

</html>