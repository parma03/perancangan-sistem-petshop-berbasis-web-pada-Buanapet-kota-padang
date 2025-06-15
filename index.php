<?php
session_start();
include 'db/koneksi.php';
include 'controller/indexController.php';

// Ambil data untuk halaman index
$newArrivals = getNewArrivals($conn, 4);
$bestSellers = getBestSellers($conn, 4);
$productTypes = getProductTypes($conn);
$priceRange = getPriceRange($conn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Home - Buana Pet Shop</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="author" content="TemplatesJungle">
    <meta name="keywords" content="ecommerce,fashion,store">
    <meta name="description" content="Bootstrap 5 Fashion Store HTML CSS Template">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="assets/css/vendor.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&family=Marcellus&display=swap"
        rel="stylesheet">

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

    <!-- Modal -->
    <?php include '_component/modalauth.php'; ?>
    <?php include '_component/modalpesanan.php'; ?>
    <?php include '_component/modalprofile.php'; ?>
    <!-- Hero Section -->
    <section id="billboard" class="bg-light py-5">
        <div class="container">
            <div class="row justify-content-center">
                <h1 class="section-title text-center mt-4" data-aos="fade-up">Selamat Datang di Buana Pet Shop</h1>
                <div class="col-md-6 text-center" data-aos="fade-up" data-aos-delay="300">
                    <p>Kami menyediakan berbagai produk dan layanan terbaik untuk hewan peliharaan Anda. Dari makanan
                        berkualitas tinggi hingga aksesoris yang stylish, semua kebutuhan pet Anda tersedia di sini.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <?php include '_component/features.php'; ?>

    <!-- New Arrivals -->
    <?php include '_component/new_arrivals.php'; ?>

    <!-- Best Sellers -->
    <?php include '_component/best_sellers.php'; ?>

    <!-- Service Section -->
    <?php include '_component/service.php'; ?>

    <!-- Comments Section -->
    <?php include '_component/comments.php'; ?>

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