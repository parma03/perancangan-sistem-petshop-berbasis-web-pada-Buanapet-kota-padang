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
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#profileModal">
                        <i class="fas fa-user me-2"></i>Profile
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

<!-- Modal Edit Profile -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="profileModalLabel">
                    <i class="fas fa-user-edit me-2"></i>Edit Profile
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form id="profileForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <!-- Profile Photo Section -->
                        <div class="col-md-4 text-center mb-4">
                            <div class="profile-photo-section">
                                <div class="current-photo mb-3">
                                    <div class="photo-wrapper">
                                        <?php if ($hasProfileImage): ?>
                                            <img src="<?php echo htmlspecialchars($profileImagePath); ?>"
                                                alt="Current Profile" class="img-fluid rounded-circle"
                                                id="currentProfileImage"
                                                style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #e9ecef;">
                                        <?php else: ?>
                                            <div class="default-avatar" id="currentProfileImage"
                                                style="width: 150px; height: 150px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                                                        border-radius: 50%; display: flex; align-items: center; justify-content: center; 
                                                        color: white; font-weight: bold; font-size: 48px; border: 4px solid #e9ecef; margin: 0 auto;">
                                                <?php echo $firstLetter; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Photo Preview (Hidden by default) -->
                                <div class="photo-preview mb-3" id="photoPreview" style="display: none;">
                                    <img id="previewImage" src="" alt="Preview" class="img-fluid rounded-circle"
                                        style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #28a745;">
                                    <p class="text-success mt-2"><small><i class="fas fa-check-circle me-1"></i>Foto
                                            baru dipilih</small></p>
                                </div>

                                <!-- Upload Button -->
                                <div class="upload-section">
                                    <input type="file" class="form-control d-none" id="profilePhoto"
                                        name="profile_photo" accept="image/jpeg,image/jpg,image/png,image/gif">
                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                        onclick="document.getElementById('profilePhoto').click()">
                                        <i class="fas fa-camera me-1"></i>Pilih Foto
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm ms-2" id="removePhoto"
                                        style="display: none;">
                                        <i class="fas fa-trash me-1"></i>Hapus
                                    </button>
                                </div>
                                <small class="text-muted mt-2 d-block">
                                    Format: JPG, PNG, GIF<br>
                                    Maksimal: 2MB
                                </small>
                            </div>
                        </div>

                        <!-- Profile Form Section -->
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username"
                                        value="<?php echo htmlspecialchars($currentUser['username']); ?>" readonly>
                                    <small class="text-muted">Username tidak dapat diubah</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="nama" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="nama" name="nama"
                                        value="<?php echo htmlspecialchars($currentUser['nama']); ?>" required>
                                </div>
                            </div>

                            <!-- Change Password Section -->
                            <div class="mt-4">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="changePassword">
                                    <label class="form-check-label" for="changePassword">
                                        <strong>Ubah Password</strong>
                                    </label>
                                </div>
                                <div id="passwordSection" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="currentPassword" class="form-label">Password Lama</label>
                                            <input type="password" class="form-control" id="currentPassword"
                                                name="current_password">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="newPassword" class="form-label">Password Baru</label>
                                            <input type="password" class="form-control" id="newPassword"
                                                name="new_password">
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="confirmPassword" class="form-label">Konfirmasi Password
                                                Baru</label>
                                            <input type="password" class="form-control" id="confirmPassword"
                                                name="confirm_password">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary" id="saveProfileBtn">
                        <i class="fas fa-save me-1"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CSS tambahan untuk styling profile avatar dan modal -->
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

    /* Modal Styles */
    .modal-content {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        border-radius: 15px 15px 0 0;
        padding: 20px;
    }

    .profile-photo-section {
        padding: 20px;
        background: #f8f9fa;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .photo-wrapper {
        position: relative;
        display: inline-block;
    }

    .upload-section {
        margin-top: 15px;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .btn-outline-primary:hover {
        background-color: #667eea;
        border-color: #667eea;
    }

    .modal-body {
        padding: 30px;
    }

    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
    }

    .text-muted {
        font-size: 0.875rem;
    }

    #passwordSection {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #ffc107;
    }

    .form-check-input:checked {
        background-color: #667eea;
        border-color: #667eea;
    }

    .modal-footer {
        padding: 20px 30px;
        border-top: 1px solid #dee2e6;
    }

    /* Animation for photo preview */
    .photo-preview {
        animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: scale(0.9);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    /* Loading state */
    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .loading-spinner {
        width: 1rem;
        height: 1rem;
        border: 2px solid transparent;
        border-top: 2px solid currentColor;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }
</style>

<!-- JavaScript untuk handling modal profile -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const profilePhoto = document.getElementById('profilePhoto');
        const previewImage = document.getElementById('previewImage');
        const photoPreview = document.getElementById('photoPreview');
        const currentProfileImage = document.getElementById('currentProfileImage');
        const removePhotoBtn = document.getElementById('removePhoto');
        const changePasswordCheck = document.getElementById('changePassword');
        const passwordSection = document.getElementById('passwordSection');
        const profileForm = document.getElementById('profileForm');
        const saveProfileBtn = document.getElementById('saveProfileBtn');

        // Handle photo upload preview
        profilePhoto.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    showToast('Ukuran file terlalu besar! Maksimal 2MB.', 'error');
                    e.target.value = '';
                    return;
                }

                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    showToast('Format file tidak didukung! Gunakan JPG, PNG, atau GIF.', 'error');
                    e.target.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImage.src = e.target.result;
                    photoPreview.style.display = 'block';
                    removePhotoBtn.style.display = 'inline-block';
                };
                reader.readAsDataURL(file);
            }
        });

        // Handle remove photo
        removePhotoBtn.addEventListener('click', function () {
            profilePhoto.value = '';
            photoPreview.style.display = 'none';
            removePhotoBtn.style.display = 'none';
            previewImage.src = '';
        });

        // Handle change password checkbox
        changePasswordCheck.addEventListener('change', function () {
            if (this.checked) {
                passwordSection.style.display = 'block';
                passwordSection.style.animation = 'fadeIn 0.3s ease-in-out';
            } else {
                passwordSection.style.display = 'none';
                // Clear password fields
                document.getElementById('currentPassword').value = '';
                document.getElementById('newPassword').value = '';
                document.getElementById('confirmPassword').value = '';
            }
        });

        // Handle form submission
        profileForm.addEventListener('submit', function (e) {
            e.preventDefault();

            // Validate form
            const nama = document.getElementById('nama').value.trim();
            if (!nama) {
                showToast('Nama lengkap harus diisi!', 'error');
                return;
            }

            // Validate password if changing
            if (changePasswordCheck.checked) {
                const currentPassword = document.getElementById('currentPassword').value;
                const newPassword = document.getElementById('newPassword').value;
                const confirmPassword = document.getElementById('confirmPassword').value;

                if (!currentPassword || !newPassword || !confirmPassword) {
                    showToast('Semua field password harus diisi!', 'error');
                    return;
                }

                if (newPassword !== confirmPassword) {
                    showToast('Konfirmasi password tidak cocok!', 'error');
                    return;
                }

                if (newPassword.length < 6) {
                    showToast('Password baru minimal 6 karakter!', 'error');
                    return;
                }
            }

            // Show loading state
            saveProfileBtn.disabled = true;
            saveProfileBtn.innerHTML = '<span class="loading-spinner me-2"></span>Menyimpan...';

            // Create FormData
            const formData = new FormData(profileForm);
            formData.append('action', 'update_profile');

            // Send AJAX request
            fetch('controller/adminController.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');

                        // Update navbar display if needed
                        if (data.data && data.data.profile_updated) {
                            setTimeout(() => {
                                location.reload(); // Reload to update navbar
                            }, 1500);
                        }

                        // Close modal after success
                        setTimeout(() => {
                            bootstrap.Modal.getInstance(document.getElementById('profileModal')).hide();
                        }, 2000);
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Terjadi kesalahan saat menyimpan profile!', 'error');
                })
                .finally(() => {
                    // Reset button state
                    saveProfileBtn.disabled = false;
                    saveProfileBtn.innerHTML = '<i class="fas fa-save me-1"></i>Simpan Perubahan';
                });
        });

        // Reset form when modal is closed
        document.getElementById('profileModal').addEventListener('hidden.bs.modal', function () {
            profileForm.reset();
            photoPreview.style.display = 'none';
            removePhotoBtn.style.display = 'none';
            passwordSection.style.display = 'none';
            changePasswordCheck.checked = false;
            previewImage.src = '';
            profilePhoto.value = '';
        });
    });
</script>