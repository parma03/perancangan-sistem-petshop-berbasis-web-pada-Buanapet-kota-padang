<nav class="navbar navbar-expand-lg bg-light text-uppercase fs-6 p-3 border-bottom align-items-center">
    <div class="container-fluid">
        <div class="row justify-content-between align-items-center w-100">
            <div class="col-auto">
                <a class="navbar-brand text-dark fw-bold" href="index.php">
                    Buana Pet Shop
                </a>
            </div>

            <div class="col-auto">
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar"
                    aria-labelledby="offcanvasNavbarLabel">
                    <div class="offcanvas-header">
                        <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Menu</h5>
                        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                            aria-label="Close"></button>
                    </div>

                    <div class="offcanvas-body">
                        <ul class="navbar-nav justify-content-end flex-grow-1 gap-1 gap-md-5 pe-3">
                            <li class="nav-item">
                                <a class="nav-link active" href="index.php">Home</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="products.php">Shop</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="services.php">Service</a>
                            </li>

                            <!-- Mobile Cart - Only show when logged in -->
                            <li class="nav-item d-lg-none" id="mobileCartSection" style="display: none;">
                                <a class="nav-link" href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart"
                                    aria-controls="offcanvasCart">
                                    Cart <span class="cart-count">(0)</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-3 col-lg-auto">
                <ul class="list-unstyled d-flex m-0">
                    <!-- Desktop Cart - Only show when logged in -->
                    <li class="d-none d-lg-block" id="desktopCartSection" style="display: none;">
                        <a href="#" class="text-uppercase mx-3" data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvasCart" aria-controls="offcanvasCart">Cart <span
                                class="cart-count">(0)</span></a>
                    </li>

                    <li class="search-box mx-2">
                        <a href="#search" class="search-button">
                            <svg width="24" height="24" viewBox="0 0 24 24">
                                <use xlink:href="#search"></use>
                            </svg>
                        </a>
                    </li>

                    <!-- Auth Section -->
                    <li class="mx-2" id="authSection">
                        <!-- Guest Section -->
                        <div id="guestSection" style="display: none;">
                            <button class="auth-btn" data-bs-toggle="modal" data-bs-target="#authModal">
                                <svg width="24" height="24" viewBox="0 0 24 24">
                                    <use xlink:href="#user"></use>
                                </svg>
                            </button>
                        </div>

                        <!-- User Profile Section -->
                        <div id="userSection" class="profile-dropdown" style="display: none;">
                            <button class="btn dropdown-toggle d-flex align-items-center p-0 border-0 bg-transparent"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img id="userAvatar" src="" alt="Profile" class="profile-avatar me-2"
                                    style="display: none;">
                                <svg id="defaultAvatar" width="24" height="24" viewBox="0 0 24 24" class="me-2">
                                    <use xlink:href="#user"></use>
                                </svg>
                                <span id="userName" class="profile-info d-none d-md-inline"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <h6 class="dropdown-header" id="userNameHeader"></h6>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="#" onclick="openEditProfileModal()">
                                        <svg width="16" height="16" viewBox="0 0 24 24" class="me-2">
                                            <use xlink:href="#user"></use>
                                        </svg>Profile</a>
                                </li>
                                <li><a class="dropdown-item" href="#" onclick="openPesananModal()">
                                        <svg width="16" height="16" viewBox="0 0 24 24" class="me-2">
                                            <use xlink:href="#shopping-bag"></use>
                                        </svg>Pesanan</a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><button class="dropdown-item text-danger" onclick="logout()">
                                        <svg width="16" height="16" viewBox="0 0 24 24" class="me-2">
                                            <use xlink:href="#logout"></use>
                                        </svg>Logout</button>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>