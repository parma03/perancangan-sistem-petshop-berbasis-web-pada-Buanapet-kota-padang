<div class="offcanvas offcanvas-end" data-bs-scroll="true" tabindex="-1" id="offcanvasCart" aria-labelledby="My Cart">
    <div class="offcanvas-header justify-content-center">
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="order-md-last">
            <h4 class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-primary">Keranjang Anda</span>
                <span class="badge bg-primary rounded-pill" id="cartBadge">0</span>
            </h4>

            <!-- Loading State -->
            <div id="cartLoading" class="text-center py-4" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Memuat keranjang...</p>
            </div>

            <!-- Empty Cart -->
            <div id="emptyCart" class="empty-cart text-center py-4" style="display: none;">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" class="text-muted mb-3">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="m1 1 4 4 40 12-7.5 7.5H6"></path>
                </svg>
                <p class="text-muted">Keranjang masih kosong</p>
                <a href="products.php" class="btn btn-outline-primary btn-sm">Mulai Belanja</a>
            </div>

            <!-- Cart Items -->
            <div id="cartItems" style="display: none;">
                <!-- Cart items will be loaded here -->
            </div>

            <!-- Cart Summary -->
            <div id="cartSummary" class="border-top pt-3 mt-3" style="display: none;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Total Items:</span>
                    <span id="totalItems">0</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fw-bold">Total Harga:</span>
                    <span class="fw-bold text-primary" id="totalPrice">Rp 0</span>
                </div>
                <div class="d-grid gap-2">
                    <button class="btn btn-primary" onclick="proceedToCheckout()">
                        Lanjutkan ke Checkout
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="clearCart()">
                        Kosongkan Keranjang
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Upload Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Upload Bukti Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Alert Container -->
                <div id="paymentAlert" class="alert d-none" role="alert"></div>

                <!-- Payment Info -->
                <div class="payment-info mb-4 p-3 bg-light rounded">
                    <h6 class="mb-3">Informasi Pembayaran</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Total Items:</strong> <span id="checkoutTotalItems">0</span></p>
                            <p class="mb-2"><strong>Total Pembayaran:</strong> <span id="checkoutTotalPrice"
                                    class="text-primary fw-bold">Rp 0</span></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Bank:</strong> BCA</p>
                            <p class="mb-2"><strong>No. Rekening:</strong> 1234567890</p>
                            <p class="mb-0"><strong>Atas Nama:</strong> Buana Pet Shop</p>
                        </div>
                    </div>
                </div>

                <!-- Upload Form -->
                <form id="paymentForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="paymentProof" class="form-label">Upload Bukti Transfer <span
                                class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="paymentProof" name="payment_proof" accept="image/*"
                            required>
                        <div class="form-text">Format yang diizinkan: JPG, JPEG, PNG, GIF. Maksimal 5MB.</div>
                    </div>

                    <!-- Preview Container -->
                    <div id="imagePreview" class="mb-3 d-none">
                        <label class="form-label">Preview Bukti Transfer:</label>
                        <div class="border rounded p-2">
                            <img id="previewImg" src="" alt="Preview" class="img-fluid" style="max-height: 300px;">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="submitPayment()" id="submitPaymentBtn">
                    <span class="spinner-border spinner-border-sm d-none me-2" role="status"></span>
                    Submit Pembayaran
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .cart-item {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        background: #fff;
    }

    .cart-item-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 6px;
    }

    .quantity-controls {
        display: flex;
        align-items: center;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        overflow: hidden;
    }

    .quantity-btn {
        background: #f8f9fa;
        border: none;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        cursor: pointer;
        transition: all 0.2s;
    }

    .quantity-btn:hover {
        background: #e9ecef;
        color: #495057;
    }

    .quantity-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .quantity-input {
        border: none;
        width: 50px;
        text-align: center;
        font-weight: 500;
        background: transparent;
    }

    .quantity-input:focus {
        outline: none;
        background: #f8f9fa;
    }

    .remove-item {
        color: #dc3545;
        background: none;
        border: none;
        font-size: 18px;
        cursor: pointer;
        padding: 0;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .remove-item:hover {
        background: #f8d7da;
        border-radius: 50%;
    }

    .cart-item-details {
        flex: 1;
        min-width: 0;
    }

    .cart-item-name {
        font-weight: 500;
        font-size: 14px;
        margin-bottom: 4px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .cart-item-price {
        color: #6c757d;
        font-size: 13px;
        margin-bottom: 8px;
    }

    .item-total {
        font-weight: 600;
        color: #0d6efd;
        font-size: 14px;
    }

    .payment-info {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: 1px solid #dee2e6;
    }

    #imagePreview {
        max-height: 350px;
        overflow: hidden;
    }

    #previewImg {
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
</style>

<script>
    let currentCartSummary = null;

    // Load cart when offcanvas is shown
    document.addEventListener('DOMContentLoaded', function () {
        const cartOffcanvas = document.getElementById('offcanvasCart');
        cartOffcanvas.addEventListener('show.bs.offcanvas', function () {
            loadCartItems();
        });

        // Image preview handler
        document.getElementById('paymentProof').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    showPaymentAlert('danger', 'Ukuran file terlalu besar. Maksimal 5MB.');
                    e.target.value = '';
                    hideImagePreview();
                    return;
                }

                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    showPaymentAlert('danger', 'Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau GIF.');
                    e.target.value = '';
                    hideImagePreview();
                    return;
                }

                // Show preview
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('previewImg').src = e.target.result;
                    document.getElementById('imagePreview').classList.remove('d-none');
                };
                reader.readAsDataURL(file);
                hidePaymentAlert();
            } else {
                hideImagePreview();
            }
        });
    });

    function loadCartItems() {
        showCartLoading(true);

        $.post('controller/cartController.php', {
            action: 'get_cart_items'
        }, function (response) {
            showCartLoading(false);

            if (response.success) {
                displayCartItems(response.cart_items);
                updateCartSummary(response.summary);
                currentCartSummary = response.summary; // Store for checkout
            } else {
                console.error('Failed to load cart items:', response.message);
                showEmptyCart();
            }
        }, 'json').fail(function (xhr, status, error) {
            showCartLoading(false);
            console.error('Failed to load cart items - Network error:', error);
            showEmptyCart();
        });
    }

    function displayCartItems(items) {
        const cartItemsContainer = document.getElementById('cartItems');
        const emptyCart = document.getElementById('emptyCart');
        const cartSummary = document.getElementById('cartSummary');

        if (!items || items.length === 0) {
            showEmptyCart();
            return;
        }

        let itemsHTML = '';
        items.forEach(item => {
            const itemTotal = item.harga_barang * item.jumlah;
            const imageSrc = item.foto_barang;

            itemsHTML += `
            <div class="cart-item" data-item-id="${item.id_cart}">
                <div class="d-flex align-items-start">
                    <img src="assets/uploads/barang/${imageSrc}" 
                         alt="${item.nama_barang}" 
                         class="cart-item-image me-3">
                    
                    <div class="cart-item-details me-2">
                        <div class="cart-item-name">${item.nama_barang}</div>
                        <div class="cart-item-price">Rp ${formatNumber(item.harga_barang)} / item</div>
                        
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="quantity-controls">
                                <button class="quantity-btn" onclick="updateQuantity(${item.id_cart}, ${item.jumlah - 1})" 
                                        ${item.jumlah <= 1 ? 'disabled' : ''}>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                </button>
                                <input type="number" class="quantity-input" value="${item.jumlah}" 
                                       onchange="updateQuantity(${item.id_cart}, this.value)" 
                                       min="1" max="999">
                                <button class="quantity-btn" onclick="updateQuantity(${item.id_cart}, ${item.jumlah + 1})">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                </button>
                            </div>
                            
                            <div class="item-total">Rp ${formatNumber(itemTotal)}</div>
                        </div>
                    </div>
                    
                    <button class="remove-item" onclick="removeFromCart(${item.id_cart})" 
                            title="Hapus item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
            </div>
        `;
        });

        cartItemsContainer.innerHTML = itemsHTML;
        cartItemsContainer.style.display = 'block';
        emptyCart.style.display = 'none';
        cartSummary.style.display = 'block';
    }

    function updateCartSummary(summary) {
        document.getElementById('totalItems').textContent = summary.total_items;
        document.getElementById('totalPrice').textContent = 'Rp ' + formatNumber(summary.total_price);
        document.getElementById('cartBadge').textContent = summary.total_items;

        // Update main cart count
        updateCartCount(summary.total_items);
    }

    function showEmptyCart() {
        document.getElementById('cartItems').style.display = 'none';
        document.getElementById('cartSummary').style.display = 'none';
        document.getElementById('emptyCart').style.display = 'block';
        document.getElementById('cartBadge').textContent = '0';
        updateCartCount(0);
    }

    function showCartLoading(show) {
        const loading = document.getElementById('cartLoading');
        const cartItems = document.getElementById('cartItems');
        const emptyCart = document.getElementById('emptyCart');
        const cartSummary = document.getElementById('cartSummary');

        if (show) {
            loading.style.display = 'block';
            cartItems.style.display = 'none';
            emptyCart.style.display = 'none';
            cartSummary.style.display = 'none';
        } else {
            loading.style.display = 'none';
        }
    }

    function updateQuantity(cartId, newQuantity) {
        newQuantity = parseInt(newQuantity);

        if (newQuantity < 1) {
            newQuantity = 1;
        }

        if (newQuantity > 999) {
            newQuantity = 999;
        }

        $.post('controller/cartController.php', {
            action: 'update_quantity',
            cart_id: cartId,
            quantity: newQuantity
        }, function (response) {
            if (response.success) {
                loadCartItems(); // Reload cart items
                showCartAlert('success', response.message);
            } else {
                showCartAlert('danger', response.message);
            }
        }, 'json').fail(function () {
            showCartAlert('danger', 'Gagal mengupdate jumlah item');
        });
    }

    function removeFromCart(cartId) {
        if (!confirm('Apakah Anda yakin ingin menghapus item ini dari keranjang?')) {
            return;
        }

        $.post('controller/cartController.php', {
            action: 'remove_from_cart',
            cart_id: cartId
        }, function (response) {
            if (response.success) {
                loadCartItems(); // Reload cart items
                showCartAlert('success', response.message);
            } else {
                showCartAlert('danger', response.message);
            }
        }, 'json').fail(function () {
            showCartAlert('danger', 'Gagal menghapus item dari keranjang');
        });
    }

    function clearCart() {
        if (!confirm('Apakah Anda yakin ingin mengosongkan seluruh keranjang?')) {
            return;
        }

        $.post('controller/cartController.php', {
            action: 'clear_cart'
        }, function (response) {
            if (response.success) {
                showEmptyCart();
                showCartAlert('success', response.message);
            } else {
                showCartAlert('danger', response.message);
            }
        }, 'json').fail(function () {
            showCartAlert('danger', 'Gagal mengosongkan keranjang');
        });
    }

    function proceedToCheckout() {
        if (!currentCartSummary || currentCartSummary.total_items === 0) {
            showCartAlert('warning', 'Keranjang masih kosong!');
            return;
        }

        // Update checkout summary in modal
        document.getElementById('checkoutTotalItems').textContent = currentCartSummary.total_items;
        document.getElementById('checkoutTotalPrice').textContent = 'Rp ' + formatNumber(currentCartSummary.total_price);

        // Reset form
        document.getElementById('paymentForm').reset();
        hideImagePreview();
        hidePaymentAlert();

        // Show payment modal
        const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
        paymentModal.show();
    }

    function submitPayment() {
        const form = document.getElementById('paymentForm');
        const formData = new FormData(form);

        // Validate form
        if (!document.getElementById('paymentProof').files[0]) {
            showPaymentAlert('danger', 'Harap upload bukti pembayaran terlebih dahulu.');
            return;
        }

        // Set loading state
        setPaymentLoading(true);

        // Add action to form data
        formData.append('action', 'submit_payment');

        $.ajax({
            url: 'controller/cartController.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                setPaymentLoading(false);

                if (response.success) {
                    showPaymentAlert('success', response.message);

                    // Close modal after delay
                    setTimeout(() => {
                        const paymentModal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
                        paymentModal.hide();

                        // Close cart offcanvas
                        const cartOffcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('offcanvasCart'));
                        if (cartOffcanvas) {
                            cartOffcanvas.hide();
                        }

                        // Reload cart to reflect changes
                        loadCartItems();

                        // Show success message
                        showCartAlert('success', 'Pembayaran berhasil disubmit! Tim kami akan segera memverifikasi pembayaran Anda.');
                    }, 2000);
                } else {
                    showPaymentAlert('danger', response.message);
                }
            },
            error: function (xhr, status, error) {
                setPaymentLoading(false);
                showPaymentAlert('danger', 'Terjadi kesalahan sistem. Silakan coba lagi.');
                console.error('Payment submission error:', error);
            }
        });
    }

    function setPaymentLoading(isLoading) {
        const btn = document.getElementById('submitPaymentBtn');
        const spinner = btn.querySelector('.spinner-border');

        if (isLoading) {
            btn.disabled = true;
            spinner.classList.remove('d-none');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Memproses...';
        } else {
            btn.disabled = false;
            spinner.classList.add('d-none');
            btn.innerHTML = 'Submit Pembayaran';
        }
    }

    function showPaymentAlert(type, message) {
        const alert = document.getElementById('paymentAlert');
        alert.className = `alert alert-${type}`;
        alert.textContent = message;
        alert.classList.remove('d-none');
    }

    function hidePaymentAlert() {
        const alert = document.getElementById('paymentAlert');
        alert.classList.add('d-none');
    }

    function hideImagePreview() {
        document.getElementById('imagePreview').classList.add('d-none');
        document.getElementById('previewImg').src = '';
    }

    function formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }
</script>