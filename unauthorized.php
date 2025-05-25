<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak - Buana Pet Shop</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .error-container {
            text-align: center;
            color: white;
            max-width: 600px;
            padding: 2rem;
        }

        .error-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
            opacity: 0.8;
        }

        .error-code {
            font-size: 4rem;
            font-weight: bold;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .error-message {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .error-description {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            opacity: 0.8;
            line-height: 1.6;
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 12px 30px;
            font-size: 1.1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin: 0 10px;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-login {
            background: #28a745;
            border: 2px solid #28a745;
        }

        .btn-login:hover {
            background: #218838;
            border-color: #218838;
        }

        .animation-bounce {
            animation: bounce 2s infinite;
        }

        @keyframes bounce {

            0%,
            20%,
            50%,
            80%,
            100% {
                transform: translateY(0);
            }

            40% {
                transform: translateY(-10px);
            }

            60% {
                transform: translateY(-5px);
            }
        }

        .card-overlay {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 3rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <div class="error-container">
        <div class="card-overlay">
            <div class="error-icon animation-bounce">
                <i class="fas fa-shield-alt"></i>
            </div>

            <div class="error-code">403</div>

            <div class="error-message">
                Akses Ditolak
            </div>

            <div class="error-description">
                Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.
                Halaman ini khusus untuk administrator sistem.
                <br><br>
                Jika Anda merasa ini adalah kesalahan, silakan hubungi administrator atau
                pastikan Anda telah login dengan akun yang memiliki hak akses yang sesuai.
            </div>

            <div class="mt-4">
                <a href="javascript:history.back()" class="btn-back">
                    <i class="fas fa-arrow-left me-2"></i>
                    Kembali
                </a>

                <a href="index.php" class="btn-back">
                    <i class="fas fa-home me-2"></i>
                    Beranda
                </a>
            </div>

            <div class="mt-4 text-center">
                <small class="opacity-75">
                    <i class="fas fa-info-circle me-1"></i>
                    Buana Pet Shop - Sistem Manajemen
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto redirect setelah 10 detik jika tidak ada interaksi
        let redirectTimer = setTimeout(function () {
            window.location.href = 'index.php';
        }, 10000);

        // Cancel timer jika user berinteraksi
        document.addEventListener('click', function () {
            clearTimeout(redirectTimer);
        });

        document.addEventListener('keydown', function () {
            clearTimeout(redirectTimer);
        });
    </script>
</body>

</html>