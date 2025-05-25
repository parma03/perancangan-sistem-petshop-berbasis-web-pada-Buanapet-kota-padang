<div class="modal fade" id="authModal" tabindex="-1" aria-labelledby="authModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authModalLabel">Login</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Alert untuk notifikasi -->
                <div id="authAlert" class="alert d-none" role="alert"></div>

                <!-- Login Form -->
                <form id="loginForm">
                    <div class="mb-3">
                        <label for="loginUsername" class="form-label">Username</label>
                        <input type="text" class="form-control" id="loginUsername" required>
                    </div>
                    <div class="mb-3">
                        <label for="loginPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="loginPassword" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" id="loginBtn">
                        <span class="spinner-border spinner-border-sm d-none me-2" role="status"></span>
                        Login
                    </button>
                </form>

                <!-- Register Form -->
                <form id="registerForm" style="display: none;">
                    <div class="mb-3">
                        <label for="registerNama" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="registerNama" required>
                    </div>
                    <div class="mb-3">
                        <label for="registerUsername" class="form-label">Username</label>
                        <input type="text" class="form-control" id="registerUsername" required>
                    </div>
                    <div class="mb-3">
                        <label for="registerPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="registerPassword" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label for="registerConfirmPassword" class="form-label">Konfirmasi Password</label>
                        <input type="password" class="form-control" id="registerConfirmPassword" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100" id="registerBtn">
                        <span class="spinner-border spinner-border-sm d-none me-2" role="status"></span>
                        Register
                    </button>
                </form>
            </div>
            <div class="modal-footer">
                <div id="switchAuth">
                    <span id="loginSwitch">Belum punya akun? <button type="button" class="btn btn-link p-0"
                            onclick="switchToRegister()">Register</button></span>
                    <span id="registerSwitch" style="display: none;">Sudah punya akun? <button type="button"
                            class="btn btn-link p-0" onclick="switchToLogin()">Login</button></span>
                </div>
            </div>
        </div>
    </div>
</div>