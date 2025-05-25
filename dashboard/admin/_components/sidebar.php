<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h4><i class="fas fa-paw me-2"></i>Buana Pet Shop</h4>
    </div>

    <div class="sidebar-menu">
        <!-- Home Menu -->
        <div class="menu-item">
            <a href="index.php" class="menu-link active">
                <i class="fas fa-box menu-icon"></i>
                <span class="menu-text">Home</span>
            </a>
        </div>

        <!-- User Menu -->
        <div class="menu-item">
            <a href="#" class="menu-link" data-bs-toggle="collapse" data-bs-target="#userMenu">
                <i class="fas fa-users menu-icon"></i>
                <span class="menu-text">User</span>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse" id="userMenu">
                <div class="dropdown-menu show position-static">
                    <a href="admin.php" class="dropdown-item">
                        <i class="fas fa-user-shield me-2"></i>Admin
                    </a>
                    <a href="pimpinan.php" class="dropdown-item">
                        <i class="fas fa-user-tie me-2"></i>Pimpinan
                    </a>
                    <a href="pelanggan.php" class="dropdown-item">
                        <i class="fas fa-user me-2"></i>Pelanggan
                    </a>
                </div>
            </div>
        </div>

        <!-- Barang Menu -->
        <div class="menu-item">
            <a href="barang.php" class="menu-link">
                <i class="fas fa-box menu-icon"></i>
                <span class="menu-text">Barang</span>
            </a>
        </div>

        <!-- Service Menu -->
        <div class="menu-item">
            <a href="services.php" class="menu-link">
                <i class="fas fa-cut menu-icon"></i>
                <span class="menu-text">Service</span>
            </a>
        </div>

        <!-- Transaksi Menu -->
        <div class="menu-item">
            <a href="#" class="menu-link" data-bs-toggle="collapse" data-bs-target="#transaksiMenu">
                <i class="fas fa-shopping-cart menu-icon"></i>
                <span class="menu-text">Transaksi</span>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse" id="transaksiMenu">
                <div class="dropdown-menu show position-static">
                    <a href="transaksi-services.php" class="dropdown-item">
                        <i class="fas fa-calendar-check me-2"></i>Booking Service
                    </a>
                    <a href="transaksi-barang.php" class="dropdown-item">
                        <i class="fas fa-shopping-bag me-2"></i>Transaksi Barang
                    </a>
                </div>
            </div>
        </div>

        <!-- Transaksi History Menu -->
        <div class="menu-item">
            <a href="#" class="menu-link" data-bs-toggle="collapse" data-bs-target="#historyMenu">
                <i class="fas fa-history menu-icon"></i>
                <span class="menu-text">Transaksi History</span>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse" id="historyMenu">
                <div class="dropdown-menu show position-static">
                    <a href="histori-service.php" class="dropdown-item">
                        <i class="fas fa-calendar-alt me-2"></i>Booking Service
                    </a>
                    <a href="histori-barang.php" class="dropdown-item">
                        <i class="fas fa-receipt me-2"></i>Transaksi Barang
                    </a>
                </div>
            </div>
        </div>

        <!-- Laporan Menu -->
        <div class="menu-item">
            <a href="laporan.php" class="menu-link">
                <i class="fas fa-chart-bar menu-icon"></i>
                <span class="menu-text">Laporan</span>
            </a>
        </div>
    </div>
</div>