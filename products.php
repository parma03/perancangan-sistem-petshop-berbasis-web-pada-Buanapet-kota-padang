<?php
session_start();
include 'db/koneksi.php';
include 'controller/indexController.php';

// Initialize filter variables with default values
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$tipe_filter = isset($_GET['tipe']) ? trim($_GET['tipe']) : '';
$harga_min = isset($_GET['harga_min']) && is_numeric($_GET['harga_min']) ? (int)$_GET['harga_min'] : 0;
$harga_max = isset($_GET['harga_max']) && is_numeric($_GET['harga_max']) ? (int)$_GET['harga_max'] : 999999999;
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'tanggal_dibuat';

// Get filtered products
$products = getAllProducts($conn, $tipe_filter, $harga_min, $harga_max, $search, $sort_by);

// Ambil data untuk filter sidebar
$productTypes = getProductTypes($conn);
$priceRange = getPriceRange($conn);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Produk - Buana Pet Shop</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/css/vendor.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&family=Marcellus&display=swap" rel="stylesheet">
 <style>
        .profile-dropdown {
            position: relative;
        }

        .profile-dropdown .dropdown-menu {
            min-width: 200px;
        }

        .profile-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #dee2e6;
        }

        .profile-info {
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .auth-btn {
            background: none;
            border: none;
            color: inherit;
            text-decoration: none;
            text-transform: uppercase;
            cursor: pointer;
        }

        .auth-btn:hover {
            color: #0d6efd;
        }
        .product-item {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.product-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.image-holder {
    width: 100%;
    height: 200px; /* Fixed height for consistency */
    overflow: hidden;
    position: relative;
    background: #f8f9fa;
}

.product-image {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover; /* This ensures image covers the area without distortion */
    object-position: center; /* Centers the image */
    transition: transform 0.3s ease;
}

.product-image:hover {
    transform: scale(1.05);
}

.product-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 1rem;
}

.product-content .element-title {
    margin-bottom: 0.5rem;
    height: 2.5rem; /* Fixed height for title consistency */
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    line-height: 1.25;
}

.product-content p {
    flex: 1;
    margin-bottom: 1rem;
    height: 3rem; /* Fixed height for description */
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.product-content .d-flex {
    margin-top: auto; /* Push price and category to bottom */
}

.product-content .btn {
    margin-top: 0.75rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .image-holder {
        height: 180px;
    }
}

@media (max-width: 576px) {
    .image-holder {
        height: 160px;
    }
    
    .product-content {
        padding: 0.75rem;
    }
}

/* Badge positioning */
.badge.position-absolute {
    z-index: 2;
}

/* Loading placeholder for images */
.product-image[src=""], .product-image:not([src]) {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
    </style>
</head>

<body class="homepage">

    <!-- svg -->
    <?php include '_component/svg.php'; ?>
    <div class="preloader text-white fs-6 text-uppercase overflow-hidden"></div>

    <!-- Search Popup -->
    <?php include '_component/search.php'; ?>

    <!-- Cart Offcanvas -->
    <?php include '_component/cart.php'; ?>

    <!-- Navigation -->
    <?php include '_component/navigation.php'; ?>

    <!-- Auth Modal -->
    <?php include '_component/modalauth.php'; ?>
    <?php include '_component/modalpesanan.php'; ?>
    <?php include '_component/modalprofile.php'; ?>
    <div class="container my-5">
        <div class="row">
            <!-- Filter Sidebar -->
            <div class="col-md-3">
                <div class="filter-sidebar">
                    <h5 class="mb-4">Filter Produk</h5>
                    
                    <form method="GET" action="products.php" id="filterForm">
                        <!-- Search -->
                        <div class="mb-4">
                            <label for="search" class="form-label">Cari Produk</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Nama produk...">
                        </div>

                        <!-- Category Filter -->
                        <div class="mb-4">
                            <label class="form-label">Kategori</label>
                            <select class="form-select" name="tipe" onchange="this.form.submit()">
                                <option value="">Semua Kategori</option>
                                <?php foreach ($productTypes as $type): ?>
                                    <option value="<?php echo htmlspecialchars($type['tipe_barang']); ?>" 
                                            <?php echo ($tipe_filter == $type['tipe_barang']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($type['tipe_barang']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-4">
                            <label class="form-label">Range Harga</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="number" class="form-control" placeholder="Min" 
                                           name="harga_min" value="<?php echo $harga_min > 0 ? $harga_min : ''; ?>" min="0">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control" placeholder="Max" 
                                           name="harga_max" value="<?php echo $harga_max < 999999999 ? $harga_max : ''; ?>" min="0">
                                </div>
                            </div>
                            <small class="text-muted">
                                Range: <?php echo formatPrice($priceRange['min_price']); ?> - 
                                <?php echo formatPrice($priceRange['max_price']); ?>
                            </small>
                        </div>

                        <!-- Sort -->
                        <div class="mb-4">
                            <label class="form-label">Urutkan</label>
                            <select class="form-select" name="sort" onchange="this.form.submit()">
                                <option value="tanggal_dibuat" <?php echo ($sort_by == 'tanggal_dibuat') ? 'selected' : ''; ?>>
                                    Terbaru
                                </option>
                                <option value="terlaris" <?php echo ($sort_by == 'terlaris') ? 'selected' : ''; ?>>
                                    Terlaris
                                </option>
                                <option value="harga_asc" <?php echo ($sort_by == 'harga_asc') ? 'selected' : ''; ?>>
                                    Harga Terendah
                                </option>
                                <option value="harga_desc" <?php echo ($sort_by == 'harga_desc') ? 'selected' : ''; ?>>
                                    Harga Tertinggi
                                </option>
                                <option value="nama" <?php echo ($sort_by == 'nama') ? 'selected' : ''; ?>>
                                    Nama A-Z
                                </option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Terapkan Filter</button>
                        <a href="products.php" class="btn btn-outline-secondary w-100 mt-2">Reset Filter</a>
                    </form>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>
                        <?php if (!empty($tipe_filter)): ?>
                            <?php echo htmlspecialchars($tipe_filter); ?>
                        <?php elseif (!empty($search)): ?>
                            Hasil pencarian "<?php echo htmlspecialchars($search); ?>"
                        <?php else: ?>
                            Semua Produk
                        <?php endif; ?>
                        <small class="text-muted">(<?php echo count($products); ?> produk)</small>
                    </h4>
                </div>

                <?php if (empty($products)): ?>
                    <div class="text-center py-5">
                        <h5>Tidak ada produk ditemukan</h5>
                        <p class="text-muted">Coba ubah filter pencarian Anda</p>
                        <a href="products.php" class="btn btn-primary">Lihat Semua Produk</a>
                    </div>
                <?php else: ?>
                    <div class="row">
    <?php foreach ($products as $product): ?>
        <div class="col-lg-4 col-md-6 col-sm-6 mb-4">
            <div class="product-item h-100">
                <div class="image-holder">
                    <a href="product-detail.php?id=<?php echo $product['id_barang']; ?>">
                        <?php 
                        $imagePath = 'assets/uploads/barang/' . htmlspecialchars($product['foto_barang']);
                        $defaultImage = 'assets/images/default-product.jpg'; // Create a default image
                        ?>
                        <img src="<?php echo file_exists($imagePath) ? $imagePath : $defaultImage; ?>" 
                             alt="<?php echo htmlspecialchars($product['nama_barang']); ?>" 
                             class="product-image"
                             onerror="this.src='<?php echo $defaultImage; ?>'">
                    </a>
                    
                    <?php if ($product['total_terjual'] > 0): ?>
                        <span class="badge bg-success position-absolute top-0 end-0 m-2">
                            Terjual <?php echo $product['total_terjual']; ?>
                        </span>
                    <?php endif; ?>
                    
                    <!-- Add stock indicator if needed -->
                    <?php if (isset($product['stok_barang']) && $product['stok_barang'] < 5): ?>
                        <span class="badge bg-warning position-absolute top-0 start-0 m-2">
                            Stok Terbatas
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="product-content">
                    <h5 class="element-title text-uppercase fs-6">
                        <a href="product-detail.php?id=<?php echo $product['id_barang']; ?>" 
                           class="text-decoration-none text-dark">
                            <?php echo htmlspecialchars($product['nama_barang']); ?>
                        </a>
                    </h5>
                    
                    <p class="text-muted small">
                        <?php 
                        $description = htmlspecialchars($product['deskripsi_barang']);
                        echo strlen($description) > 80 ? substr($description, 0, 80) . '...' : $description;
                        ?>
                    </p>
                    
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="price fw-bold text-primary fs-5">
                            <?php echo formatPrice($product['harga_barang']); ?>
                        </span>
                        <span class="badge bg-light text-dark">
                            <?php echo htmlspecialchars($product['tipe_barang']); ?>
                        </span>
                    </div>
                    
                    <button class="btn btn-outline-primary w-100 add-to-cart" 
                            data-id="<?php echo $product['id_barang']; ?>"
                            <?php echo (isset($product['stok_barang']) && $product['stok_barang'] <= 0) ? 'disabled' : ''; ?>>
                        <?php if (isset($product['stok_barang']) && $product['stok_barang'] <= 0): ?>
                            Stok Habis
                        <?php else: ?>
                            <i class="fas fa-cart-plus me-2"></i>Tambah ke Keranjang
                        <?php endif; ?>
                    </button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <!-- footer Section -->
    <?php include '_component/footer.php'; ?>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/plugins.js"></script>
    <script src="assets/js/SmoothScroll.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous">
        </script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script src="assets/js/script.min.js"></script>
    <script>
        $(document).ready(function () {
            checkUserSession();
            initializeCart(); // Initialize cart functionality

            // Login form submit
            $('#loginForm').on('submit', function (e) {
                e.preventDefault();
                handleLogin();
            });

            // Register form submit
            $('#registerForm').on('submit', function (e) {
                e.preventDefault();
                handleRegister();
            });
        });

        function checkUserSession() {
            $.post('controller/authController.php', {
                action: 'check_session'
            }, function (response) {
                if (response.success && response.logged_in) {
                    showUserSection(response.user);
                    showCartSection(); // Show cart when logged in
                    loadCartCount(); // Load cart count for logged in user
                } else {
                    showGuestSection();
                    hideCartSection(); // Hide cart when not logged in
                }
            }, 'json').fail(function () {
                showGuestSection();
                hideCartSection();
            });
        }

        function showGuestSection() {
            $('#guestSection').show();
            $('#userSection').hide();
            updateCartCount(0); // Reset cart count for guest
        }

        function showUserSection(user) {
            $('#guestSection').hide();
            $('#userSection').show();

            $('#userName').text(user.nama || user.username);
            $('#userNameHeader').text(user.nama || user.username);

            if (user.profile && user.profile.trim() !== '') {
                $('#userAvatar').attr('src', user.profile).show();
                $('#defaultAvatar').hide();
            } else {
                $('#userAvatar').hide();
                $('#defaultAvatar').show();
            }

            loadCartCount(); // Load cart count for logged in user
        }

        function showCartSection() {
            // Show desktop cart
            $('#desktopCartSection').show().removeClass('d-none').addClass('d-lg-block');

            // Show mobile cart
            $('#mobileCartSection').show().removeClass('d-none').addClass('d-lg-none');
        }

        function hideCartSection() {
            // Hide desktop cart
            $('#desktopCartSection').hide();

            // Hide mobile cart
            $('#mobileCartSection').hide();

            // Reset cart count display
            updateCartCount(0);
        }

       function handleLogin() {
            const username = $('#loginUsername').val();
            const password = $('#loginPassword').val();

            setLoading('loginBtn', true);

            $.post('controller/authController.php', {
                action: 'login',
                username: username,
                password: password
            }, function (response) {
                setLoading('loginBtn', false);

                if (response.success) {
                    showAlert('success', response.message);

                    // Check if user role requires redirect
                    const userRole = response.user.role;

                    if (userRole === 'admin' || userRole === 'pimpinan') {
                        // For admin and pimpinan, redirect immediately
                        setTimeout(() => {
                            window.location.href = response.redirect_url;
                        }, 1000);
                    } else {
                        // For pelanggan, stay on current page
                        setTimeout(() => {
                            $('#authModal').modal('hide');
                            showUserSection(response.user);
                            showCartSection();
                            resetForms();
                        }, 1000);
                    }
                } else {
                    showAlert('danger', response.message);
                }
            }, 'json').fail(function () {
                setLoading('loginBtn', false);
                showAlert('danger', 'Terjadi kesalahan sistem');
            });
        }

        function handleRegister() {
            const nama = $('#registerNama').val();
            const username = $('#registerUsername').val();
            const password = $('#registerPassword').val();
            const confirmPassword = $('#registerConfirmPassword').val();

            if (password !== confirmPassword) {
                showAlert('danger', 'Password tidak cocok');
                return;
            }

            setLoading('registerBtn', true);

            $.post('controller/authController.php', {
                action: 'register',
                nama: nama,
                username: username,
                password: password,
                confirm_password: confirmPassword
            }, function (response) {
                setLoading('registerBtn', false);

                if (response.success) {
                    showAlert('success', response.message);
                    setTimeout(() => {
                        $('#authModal').modal('hide');
                        showUserSection(response.user);
                        showCartSection(); // Show cart after successful registration
                        resetForms();
                    }, 1000);
                } else {
                    showAlert('danger', response.message);
                }
            }, 'json').fail(function () {
                setLoading('registerBtn', false);
                showAlert('danger', 'Terjadi kesalahan sistem');
            });
        }

        function logout() {
            $.post('controller/authController.php', {
                action: 'logout'
            }, function (response) {
                if (response.success) {
                    showGuestSection();
                    hideCartSection(); // Hide cart after logout
                    location.reload();
                }
            }, 'json');
        }

        function switchToRegister() {
            $('#authModalLabel').text('Register');
            $('#loginForm').hide();
            $('#registerForm').show();
            $('#loginSwitch').hide();
            $('#registerSwitch').show();
            hideAlert();
        }

        function switchToLogin() {
            $('#authModalLabel').text('Login');
            $('#registerForm').hide();
            $('#loginForm').show();
            $('#registerSwitch').hide();
            $('#loginSwitch').show();
            hideAlert();
        }

        function setLoading(btnId, isLoading) {
            const btn = $('#' + btnId);
            const spinner = btn.find('.spinner-border');

            if (isLoading) {
                btn.prop('disabled', true);
                spinner.removeClass('d-none');
            } else {
                btn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        }

        function showAlert(type, message) {
            const alert = $('#authAlert');
            alert.removeClass('d-none alert-success alert-danger alert-warning alert-info')
                .addClass('alert-' + type)
                .text(message);
        }

        function hideAlert() {
            $('#authAlert').addClass('d-none');
        }

        function resetForms() {
            $('#loginForm')[0].reset();
            $('#registerForm')[0].reset();
            hideAlert();
        }

        // Reset modal when hidden
        $('#authModal').on('hidden.bs.modal', function () {
            switchToLogin();
            resetForms();
        });

        // Cart functionality
        function initializeCart() {
            // Add event listeners for add to cart buttons
            document.addEventListener('click', function (e) {
                if (e.target.classList.contains('add-to-cart') || e.target.closest('.add-to-cart')) {
                    e.preventDefault();

                    const button = e.target.classList.contains('add-to-cart') ? e.target : e.target.closest('.add-to-cart');
                    const productId = button.getAttribute('data-id');

                    if (productId) {
                        addToCart(productId, button);
                    }
                }
            });
        }

        function loadCartCount() {
            $.post('controller/cartController.php', {
                action: 'get_cart_count'
            }, function (response) {
                if (response.success) {
                    updateCartCount(response.cart_count);
                }
            }, 'json').fail(function () {
                console.log('Failed to load cart count');
            });
        }

        function addToCart(productId, button) {
            // Disable button and show loading
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Menambah...';

            $.post('controller/cartController.php', {
                action: 'add_to_cart',
                product_id: productId
            }, function (response) {
                // Re-enable button
                button.disabled = false;
                button.innerHTML = originalText;

                if (response.success) {
                    // Show success message
                    showCartAlert('success', response.message);

                    // Update cart count
                    updateCartCount(response.cart_count);

                    // Optional: Add visual feedback to button
                    button.innerHTML = '✓ Ditambahkan';
                    button.classList.remove('btn-outline-primary');
                    button.classList.add('btn-success');

                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.classList.remove('btn-success');
                        button.classList.add('btn-outline-primary');
                    }, 2000);

                } else {
                    if (response.login_required) {
                        // Show login required alert and open modal
                        showCartAlert('warning', response.message);

                        // Automatically open login modal
                        setTimeout(() => {
                            $('#authModal').modal('show');
                            switchToLogin();
                        }, 1500);
                    } else {
                        // Show other error messages
                        showCartAlert('danger', response.message);
                    }
                }
            }, 'json').fail(function () {
                // Re-enable button
                button.disabled = false;
                button.innerHTML = originalText;

                showCartAlert('danger', 'Terjadi kesalahan sistem');
            });
        }

        function updateCartCount(count) {
            // Update cart count in navigation
            const cartCountElements = document.querySelectorAll('.cart-count');
            cartCountElements.forEach(element => {
                element.textContent = '(' + count + ')';
            });

            // Update cart badge if exists
            const cartBadges = document.querySelectorAll('.cart-badge');
            cartBadges.forEach(badge => {
                if (count > 0) {
                    badge.textContent = count;
                    badge.style.display = 'inline';
                } else {
                    badge.style.display = 'none';
                }
            });
        }

        function showCartAlert(type, message) {
            // Create alert element if it doesn't exist
            let alertContainer = document.getElementById('cartAlertContainer');
            if (!alertContainer) {
                alertContainer = document.createElement('div');
                alertContainer.id = 'cartAlertContainer';
                alertContainer.style.position = 'fixed';
                alertContainer.style.top = '20px';
                alertContainer.style.right = '20px';
                alertContainer.style.zIndex = '9999';
                alertContainer.style.maxWidth = '400px';
                document.body.appendChild(alertContainer);
            }

            // Create alert
            const alertElement = document.createElement('div');
            alertElement.className = `alert alert-${type} alert-dismissible fade show`;
            alertElement.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

            alertContainer.appendChild(alertElement);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (alertElement.parentNode) {
                    alertElement.remove();
                }
            }, 5000);
        }
    </script>
    
</body>
</html>