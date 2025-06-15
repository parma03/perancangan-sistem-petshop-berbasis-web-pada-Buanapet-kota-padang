<!-- Modal Edit Profile -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Alert for profile edit -->
                <div id="profileAlert" class="alert d-none" role="alert"></div>

                <form id="editProfileForm" enctype="multipart/form-data">
                    <!-- Profile Picture Section -->
                    <div class="text-center mb-4">
                        <div class="profile-picture-container position-relative d-inline-block">
                            <img id="profilePreview"
                                src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIwIiBoZWlnaHQ9IjEyMCIgdmlld0JveD0iMCAwIDEyMCAxMjAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxMjAiIGhlaWdodD0iMTIwIiBmaWxsPSIjRjVGNUY1IiByeD0iNjAiLz4KPHN2ZyB4PSIzNiIgeT0iMzYiIHdpZHRoPSI0OCIgaGVpZ2h0PSI0OCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSIjOTk5OTk5Ij4KPHBhdGggZD0iTTEyIDEyYzIuMjEgMCA0LTEuNzkgNC00cy0xLjc5LTQtNC00LTQgMS43OS00IDQgMS43OSA0IDQgNHptMCAyYy0yLjY3IDAtOCAxLjM0LTggNHYyaDE2di0yYzAtMi42Ni01LjMzLTQtOC00eiIvPgo8L3N2Zz4KPC9zdmc+"
                                alt="Profile Picture"
                                class="profile-picture-large border border-3 border-light shadow-sm"
                                style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover;">
                            <button type="button"
                                class="btn btn-primary btn-sm position-absolute bottom-0 end-0 rounded-circle"
                                onclick="document.getElementById('profilePictureInput').click()"
                                style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" />
                                </svg>
                            </button>
                        </div>
                        <input type="file" id="profilePictureInput" name="profile_picture" accept="image/*"
                            style="display: none;">
                        <div class="mt-2">
                            <small class="text-muted">Klik ikon kamera untuk mengubah foto profile</small>
                        </div>
                    </div>

                    <!-- Form Fields -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editNama" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="editNama" name="nama" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editUsername" class="form-label">Username</label>
                            <input type="text" class="form-control" id="editUsername" name="username" required>
                        </div>
                    </div>

                    <!-- Password Change Section -->
                    <div class="border-top pt-3 mt-3">
                        <h6 class="mb-3">Ubah Password (Opsional)</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editCurrentPassword" class="form-label">Password Saat Ini</label>
                                <input type="password" class="form-control" id="editCurrentPassword"
                                    name="current_password">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editNewPassword" class="form-label">Password Baru</label>
                                <input type="password" class="form-control" id="editNewPassword" name="new_password">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editConfirmPassword" class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" id="editConfirmPassword"
                                name="confirm_password">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="saveProfileBtn" onclick="saveProfile()">
                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .profile-picture-container {
        position: relative;
    }

    .profile-picture-large {
        transition: all 0.3s ease;
    }

    .profile-picture-container:hover .profile-picture-large {
        transform: scale(1.05);
    }

    .profile-picture-container button {
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    .profile-picture-container button:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    #profileAlert {
        border-radius: 8px;
    }

    .form-control {
        border-radius: 8px;
    }

    .btn {
        border-radius: 8px;
    }

    .modal-content {
        border-radius: 12px;
        border: none;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    }

    .modal-header {
        border-bottom: 1px solid #e9ecef;
        padding: 1.5rem;
    }

    .modal-body {
        padding: 2rem;
    }

    .modal-footer {
        border-top: 1px solid #e9ecef;
        padding: 1.5rem;
    }
</style>

<script>
    // Default avatar SVG as base64 (placeholder image)
    const DEFAULT_AVATAR = "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIwIiBoZWlnaHQ9IjEyMCIgdmlld0JveD0iMCAwIDEyMCAxMjAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxMjAiIGhlaWdodD0iMTIwIiBmaWxsPSIjRjVGNUY1IiByeD0iNjAiLz4KPHN2ZyB4PSIzNiIgeT0iMzYiIHdpZHRoPSI0OCIgaGVpZ2h0PSI0OCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSIjOTk5OTk5Ij4KPHBhdGggZD0iTTEyIDEyYzIuMjEgMCA0LTEuNzkgNC00cy0xLjc5LTQtNC00LTQgMS43OS00IDQgMS43OSA0IDQgNHptMCAyYy0yLjY3IDAtOCAxLjM0LTggNHYyaDE2di0yYzAtMi42Ni01LjMzLTQtOC00eiIvPgo8L3N2Zz4KPC9zdmc+";

    // Profile Picture Preview
    document.getElementById('profilePictureInput').addEventListener('change', function (e) {
        const file = e.target.files[0];
        const profilePreview = document.getElementById('profilePreview');

        if (file) {
            // Validate file type
            if (!file.type.startsWith('image/')) {
                showProfileAlert('danger', 'File harus berupa gambar');
                return;
            }

            // Validate file size (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                showProfileAlert('danger', 'Ukuran file maksimal 5MB');
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                profilePreview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        } else {
            // Jika file dihapus, kembali ke default avatar
            profilePreview.src = DEFAULT_AVATAR;
        }
    });

    // Open Edit Profile Modal
    function openEditProfileModal() {
        // Reset modal first
        resetProfileModal();

        // Load current user data
        loadCurrentUserData();

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('editProfileModal'));
        modal.show();
    }

    // Reset modal profile
    function resetProfileModal() {
        // Reset form
        document.getElementById('editProfileForm').reset();

        // Reset alert
        document.getElementById('profileAlert').classList.add('d-none');

        // Reset gambar ke default avatar
        document.getElementById('profilePreview').src = DEFAULT_AVATAR;

        // Reset file input
        document.getElementById('profilePictureInput').value = '';
    }

    // Load current user data
    function loadCurrentUserData() {
        $.post('controller/profileController.php', {
            action: 'get_profile'
        }, function (response) {
            if (response.success) {
                const user = response.user;

                // Fill form with current data
                document.getElementById('editNama').value = user.nama || '';
                document.getElementById('editUsername').value = user.username || '';

                // Set profile picture
                const profilePreview = document.getElementById('profilePreview');

                if (user.profile && user.profile.trim() !== '') {
                    profilePreview.src = user.profile;
                } else {
                    profilePreview.src = DEFAULT_AVATAR;
                }
            }
        }, 'json').fail(function () {
            showProfileAlert('danger', 'Gagal memuat data profile');
        });
    }

    // Save Profile
    function saveProfile() {
        const form = document.getElementById('editProfileForm');
        const formData = new FormData(form);
        formData.append('action', 'update_profile');

        // Validate password if provided
        const currentPassword = document.getElementById('editCurrentPassword').value;
        const newPassword = document.getElementById('editNewPassword').value;
        const confirmPassword = document.getElementById('editConfirmPassword').value;

        if (newPassword && newPassword !== confirmPassword) {
            showProfileAlert('danger', 'Password baru tidak cocok');
            return;
        }

        if (newPassword && !currentPassword) {
            showProfileAlert('danger', 'Masukkan password saat ini untuk mengubah password');
            return;
        }

        // Show loading
        setProfileLoading(true);

        $.ajax({
            url: 'controller/profileController.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                setProfileLoading(false);

                if (response.success) {
                    showProfileAlert('success', response.message);

                    // Update user session data
                    if (response.user) {
                        showUserSection(response.user);
                    }

                    // Close modal after delay
                    setTimeout(() => {
                        $('#editProfileModal').modal('hide');
                    }, 1500);

                } else {
                    showProfileAlert('danger', response.message);
                }
            },
            error: function () {
                setProfileLoading(false);
                showProfileAlert('danger', 'Terjadi kesalahan sistem');
            }
        });
    }

    // Helper functions
    function showProfileAlert(type, message) {
        const alert = document.getElementById('profileAlert');
        alert.className = `alert alert-${type}`;
        alert.textContent = message;
        alert.classList.remove('d-none');

        // Auto hide after 5 seconds
        setTimeout(() => {
            alert.classList.add('d-none');
        }, 5000);
    }

    function setProfileLoading(isLoading) {
        const btn = document.getElementById('saveProfileBtn');
        const spinner = btn.querySelector('.spinner-border');

        if (isLoading) {
            btn.disabled = true;
            spinner.classList.remove('d-none');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Menyimpan...';
        } else {
            btn.disabled = false;
            spinner.classList.add('d-none');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>Simpan Perubahan';
        }
    }

    // Reset modal when hidden
    document.getElementById('editProfileModal').addEventListener('hidden.bs.modal', function () {
        resetProfileModal();
    });
</script>