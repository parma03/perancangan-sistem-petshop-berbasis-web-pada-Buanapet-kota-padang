<!-- Modal Pesanan -->
<div class="modal fade" id="pesananModal" tabindex="-1" aria-labelledby="pesananModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pesananModalLabel">
                    <i class="fas fa-shopping-bag me-2"></i>Pesanan Saya
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Alert for messages -->
                <div id="pesananAlert" class="alert d-none" role="alert"></div>

                <!-- Loading indicator -->
                <div id="pesananLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Memuat data pesanan...</p>
                </div>

                <!-- Tab Navigation -->
                <ul class="nav nav-tabs" id="pesananTabs" role="tablist" style="display: none;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="produk-tab" data-bs-toggle="tab"
                            data-bs-target="#produk-pane" type="button" role="tab" aria-controls="produk-pane"
                            aria-selected="true">
                            <i class="fas fa-shopping-cart me-2"></i>Pesanan Produk
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="service-tab" data-bs-toggle="tab" data-bs-target="#service-pane"
                            type="button" role="tab" aria-controls="service-pane" aria-selected="false">
                            <i class="fas fa-concierge-bell me-2"></i>Pesanan Service
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content mt-3" id="pesananTabContent" style="display: none;">
                    <!-- Produk Tab -->
                    <div class="tab-pane fade show active" id="produk-pane" role="tabpanel"
                        aria-labelledby="produk-tab">
                        <!-- Sub-tabs for Pending and Completed -->
                        <ul class="nav nav-pills mb-3" id="produkSubTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="produk-pending-tab" data-bs-toggle="pill"
                                    data-bs-target="#produk-pending" type="button" role="tab"
                                    aria-controls="produk-pending" aria-selected="true">
                                    <i class="fas fa-clock me-2"></i>Pending (<span id="produkPendingCount">0</span>)
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="produk-completed-tab" data-bs-toggle="pill"
                                    data-bs-target="#produk-completed" type="button" role="tab"
                                    aria-controls="produk-completed" aria-selected="false">
                                    <i class="fas fa-check-circle me-2"></i>Selesai (<span
                                        id="produkCompletedCount">0</span>)
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="produkSubTabContent">
                            <!-- Produk Pending -->
                            <div class="tab-pane fade show active" id="produk-pending" role="tabpanel"
                                aria-labelledby="produk-pending-tab">
                                <div id="produkPendingContent">
                                    <!-- Content will be loaded here -->
                                </div>
                            </div>
                            <!-- Produk Completed -->
                            <div class="tab-pane fade" id="produk-completed" role="tabpanel"
                                aria-labelledby="produk-completed-tab">
                                <div id="produkCompletedContent">
                                    <!-- Content will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Service Tab -->
                    <div class="tab-pane fade" id="service-pane" role="tabpanel" aria-labelledby="service-tab">
                        <!-- Sub-tabs for Pending and Completed -->
                        <ul class="nav nav-pills mb-3" id="serviceSubTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="service-pending-tab" data-bs-toggle="pill"
                                    data-bs-target="#service-pending" type="button" role="tab"
                                    aria-controls="service-pending" aria-selected="true">
                                    <i class="fas fa-clock me-2"></i>Pending (<span id="servicePendingCount">0</span>)
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="service-completed-tab" data-bs-toggle="pill"
                                    data-bs-target="#service-completed" type="button" role="tab"
                                    aria-controls="service-completed" aria-selected="false">
                                    <i class="fas fa-check-circle me-2"></i>Selesai (<span
                                        id="serviceCompletedCount">0</span>)
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="serviceSubTabContent">
                            <!-- Service Pending -->
                            <div class="tab-pane fade show active" id="service-pending" role="tabpanel"
                                aria-labelledby="service-pending-tab">
                                <div id="servicePendingContent">
                                    <!-- Content will be loaded here -->
                                </div>
                            </div>
                            <!-- Service Completed -->
                            <div class="tab-pane fade" id="service-completed" role="tabpanel"
                                aria-labelledby="service-completed-tab">
                                <div id="serviceCompletedContent">
                                    <!-- Content will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div id="emptyState" class="text-center py-5" style="display: none;">
                    <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Belum ada pesanan</h5>
                    <p class="text-muted">Anda belum memiliki pesanan apapun</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .order-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin-bottom: 15px;
        transition: box-shadow 0.3s ease;
    }

    .order-card:hover {
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .order-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        padding: 15px;
        border-radius: 8px 8px 0 0;
    }

    .order-content {
        padding: 15px;
    }

    .status-badge {
        font-size: 0.8em;
        padding: 5px 10px;
        border-radius: 15px;
    }

    .status-pending {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .status-completed {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .product-item {
        border-bottom: 1px solid #eee;
        padding: 10px 0;
    }

    .product-item:last-child {
        border-bottom: none;
    }

    .product-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 5px;
    }

    .total-price {
        font-size: 1.1em;
        font-weight: bold;
        color: #28a745;
    }

    .payment-proof {
        max-width: 100px;
        max-height: 100px;
        object-fit: cover;
        border-radius: 5px;
        cursor: pointer;
    }

    .nav-pills .nav-link {
        border-radius: 20px;
        margin-right: 10px;
    }

    .nav-pills .nav-link.active {
        background-color: #007bff;
    }
</style>

<script>
    // Pesanan Modal Functions
    function openPesananModal() {
        $('#pesananModal').modal('show');
        loadPesananData();
    }

    function loadPesananData() {
        // Show loading
        $('#pesananLoading').show();
        $('#pesananTabs').hide();
        $('#pesananTabContent').hide();
        $('#emptyState').hide();
        hidePesananAlert();

        $.post('controller/pesananController.php', {
            action: 'get_orders'
        }, function (response) {
            $('#pesananLoading').hide();

            if (response.success) {
                displayPesananData(response.data);
            } else {
                showPesananAlert('danger', response.message);
            }
        }, 'json').fail(function () {
            $('#pesananLoading').hide();
            showPesananAlert('danger', 'Terjadi kesalahan sistem');
        });
    }

    function displayPesananData(data) {
        const produkData = data.produk;
        const serviceData = data.service;

        // Check if there's any data
        const hasData = (produkData.pending.length + produkData.completed.length +
            serviceData.pending.length + serviceData.completed.length) > 0;

        if (!hasData) {
            $('#emptyState').show();
            return;
        }

        // Update counts
        updatePesananCounts(produkData, serviceData);

        // Display produk orders
        displayProdukOrders(produkData);

        // Display service orders
        displayServiceOrders(serviceData);

        // Show tabs
        $('#pesananTabs').show();
        $('#pesananTabContent').show();
    }

    function updatePesananCounts(produkData, serviceData) {
        $('#produkPendingCount').text(produkData.pending.length);
        $('#produkCompletedCount').text(produkData.completed.length);
        $('#servicePendingCount').text(serviceData.pending.length);
        $('#serviceCompletedCount').text(serviceData.completed.length);
    }

    function displayProdukOrders(data) {
        // Display pending orders
        displayProdukOrdersByStatus(data.pending, '#produkPendingContent', 'pending');

        // Display completed orders
        displayProdukOrdersByStatus(data.completed, '#produkCompletedContent', 'completed');
    }

    function displayProdukOrdersByStatus(orders, containerId, status) {
        const container = $(containerId);

        if (orders.length === 0) {
            container.html(`
            <div class="text-center py-4">
                <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                <p class="text-muted">Tidak ada pesanan ${status === 'pending' ? 'yang sedang diproses' : 'yang selesai'}</p>
            </div>
        `);
            return;
        }

        let html = '';
        orders.forEach(order => {
            html += createProdukOrderCard(order, status);
        });

        container.html(html);
    }

    function createProdukOrderCard(order, status) {
        const statusClass = status === 'pending' ? 'status-pending' : 'status-completed';
        const statusText = status === 'pending' ? 'Menunggu Konfirmasi' : 'Selesai';

        let itemsHtml = '';
        order.items.forEach(item => {
            itemsHtml += `
            <div class="product-item">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <img src="assets/uploads/barang/${item.foto}" alt="${item.nama}" class="product-image">
                    </div>
                    <div class="col">
                        <h6 class="mb-1">${item.nama}</h6>
                        <small class="text-muted">${item.tipe}</small>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span class="text-muted">Rp ${formatNumber(item.harga)} x ${item.jumlah}</span>
                            <span class="fw-bold">Rp ${formatNumber(item.subtotal)}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        });

        return `
        <div class="order-card">
            <div class="order-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Pesanan #${order.id_transaksi}</h6>
                        <small class="text-muted">${order.items.length} item</small>
                    </div>
                    <span class="status-badge ${statusClass}">${statusText}</span>
                </div>
            </div>
            <div class="order-content">
                ${itemsHtml}
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                    <div>
                        ${order.bukti_pembayaran ? `
                            <small class="text-muted d-block">Bukti Pembayaran:</small>
                            <img src="assets/uploads/payment_proofs/${order.bukti_pembayaran}" 
                                 alt="Bukti Pembayaran" 
                                 class="payment-proof"
                                 onclick="showPaymentProof('assets/uploads/payment_proofs/${order.bukti_pembayaran}')">
                        ` : '<small class="text-muted">Belum ada bukti pembayaran</small>'}
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">Total:</small>
                        <span class="total-price">Rp ${formatNumber(order.total)}</span>
                    </div>
                </div>
            </div>
        </div>
    `;
    }

    function displayServiceOrders(data) {
        // Display pending orders
        displayServiceOrdersByStatus(data.pending, '#servicePendingContent', 'pending');

        // Display completed orders
        displayServiceOrdersByStatus(data.completed, '#serviceCompletedContent', 'completed');
    }

    function displayServiceOrdersByStatus(orders, containerId, status) {
        const container = $(containerId);

        if (orders.length === 0) {
            container.html(`
            <div class="text-center py-4">
                <i class="fas fa-concierge-bell fa-2x text-muted mb-2"></i>
                <p class="text-muted">Tidak ada pesanan service ${status === 'pending' ? 'yang sedang diproses' : 'yang selesai'}</p>
            </div>
        `);
            return;
        }

        let html = '';
        orders.forEach(order => {
            html += createServiceOrderCard(order, status);
        });

        container.html(html);
    }

    function createServiceOrderCard(order, status) {
        const statusClass = status === 'pending' ? 'status-pending' : 'status-completed';
        const statusText = status === 'pending' ? 'Menunggu Konfirmasi' : 'Selesai';

        return `
        <div class="order-card">
            <div class="order-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Booking #${order.id_booking}</h6>
                        <small class="text-muted">Transaksi #${order.id_transaksi}</small>
                    </div>
                    <span class="status-badge ${statusClass}">${statusText}</span>
                </div>
            </div>
            <div class="order-content">
                <div class="row">
                    <div class="col-md-8">
                        <h6 class="mb-2">${order.nama_service}</h6>
                        <p class="text-muted mb-2">${order.deskripsi_service}</p>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-calendar-alt me-2 text-primary"></i>
                            <span>${order.tanggal_booking}</span>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="mb-3">
                            <small class="text-muted d-block">Harga Service:</small>
                            <span class="total-price">Rp ${formatNumber(order.harga_service)}</span>
                        </div>
                        ${order.bukti_pembayaran ? `
                            <div>
                                <small class="text-muted d-block">Bukti Pembayaran:</small>
                                <img src="assets/uploads/payment_proofs/${order.bukti_pembayaran}" 
                                     alt="Bukti Pembayaran" 
                                     class="payment-proof"
                                     onclick="showPaymentProof('assets/uploads/payment_proofs/${order.bukti_pembayaran}')">
                            </div>
                        ` : '<small class="text-muted">Belum ada bukti pembayaran</small>'}
                    </div>
                </div>
            </div>
        </div>
    `;
    }

    function showPaymentProof(imageSrc) {
        const modal = `
        <div class="modal fade" id="paymentProofModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Bukti Pembayaran</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="assets/uploads/payment_proofs/${imageSrc}" alt="Bukti Pembayaran" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    `;

        // Remove existing modal if any
        $('#paymentProofModal').remove();

        // Add new modal
        $('body').append(modal);
        $('#paymentProofModal').modal('show');

        // Remove modal after hiding
        $('#paymentProofModal').on('hidden.bs.modal', function () {
            $(this).remove();
        });
    }

    function showPesananAlert(type, message) {
        const alert = $('#pesananAlert');
        alert.removeClass('d-none alert-success alert-danger alert-warning alert-info')
            .addClass('alert-' + type)
            .html(`<i class="fas fa-exclamation-triangle me-2"></i>${message}`);
    }

    function hidePesananAlert() {
        $('#pesananAlert').addClass('d-none');
    }

    function formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }

    // Initialize when document is ready
    $(document).ready(function () {
        // Reset modal when hidden
        $('#pesananModal').on('hidden.bs.modal', function () {
            hidePesananAlert();
            $('#pesananLoading').hide();
            $('#pesananTabs').hide();
            $('#pesananTabContent').hide();
            $('#emptyState').hide();
        });
    });
</script>