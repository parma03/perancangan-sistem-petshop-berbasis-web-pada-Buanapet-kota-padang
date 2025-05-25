<?php
// Mendapatkan informasi user yang sedang login
$currentUser = getCurrentUser();

// Menentukan nama untuk ditampilkan
$displayName = $currentUser['nama'] ?? $currentUser['username'] ?? 'User';

// Menentukan role untuk ditampilkan
$displayRole = '';
switch ($currentUser['role']) {
    case 'admin':
        $displayRole = 'Administrator';
        break;
    case 'pimpinan':
        $displayRole = 'Pimpinan';
        break;
    case 'pelanggan':
        $displayRole = 'Pelanggan';
        break;
    default:
        $displayRole = 'User';
        break;
}

// Menentukan avatar - jika ada profile photo gunakan itu, jika tidak gunakan huruf pertama nama
$profileImage = $currentUser['profile'] ?? '';
$profileImagePath = '';
$hasProfileImage = false;

// Cek apakah ada profile image dan file tersebut ada
if (!empty($profileImage)) {
    // Tambahkan path lengkap untuk gambar
    $profileImagePath = '../../assets/uploads/profile/' . $profileImage;
    // Cek apakah file benar-benar ada
    if (file_exists($profileImagePath)) {
        $hasProfileImage = true;
    }
}

$firstLetter = strtoupper(substr($displayName, 0, 1));
?>

<nav class="main-navbar">
    <div class="navbar-content">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>

        <div class="profile-section dropdown">
            <div class="d-flex align-items-center cursor-pointer" data-bs-toggle="dropdown">
                <div class="profile-avatar">
                    <?php if ($hasProfileImage): ?>
                        <img src="<?php echo htmlspecialchars($profileImagePath); ?>" alt="Profile"
                            class="rounded-circle w-100 h-100 object-fit-cover">
                    <?php else: ?>
                        <?php echo $firstLetter; ?>
                    <?php endif; ?>
                </div>
                <div class="profile-info ms-2">
                    <h6><?php echo htmlspecialchars($displayName); ?></h6>
                    <small><?php echo htmlspecialchars($displayRole); ?></small>
                </div>
                <i class="fas fa-chevron-down ms-2 text-muted"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="#">
                        <i class="fas fa-user me-2"></i>Profile
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="#">
                        <i class="fas fa-cog me-2"></i>Settings
                    </a>
                </li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <button class="dropdown-item" onclick="logout()">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </button>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- CSS tambahan untuk styling profile avatar -->
<style>
    .profile-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 16px;
        position: relative;
        overflow: hidden;
    }

    .profile-avatar img {
        border: 2px solid #fff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .profile-info h6 {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        color: #333;
        max-width: 120px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .profile-info small {
        color: #6c757d;
        font-size: 12px;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    .cursor-pointer:hover .profile-avatar {
        transform: scale(1.05);
        transition: transform 0.2s ease;
    }

    .dropdown-menu {
        border: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        border-radius: 8px;
        overflow: hidden;
    }

    .dropdown-item {
        padding: 10px 16px;
        transition: background-color 0.2s ease;
    }

    .dropdown-item:hover {
        background-color: #f8f9fa;
    }

    .dropdown-item i {
        color: #6c757d;
        width: 16px;
    }
</style>