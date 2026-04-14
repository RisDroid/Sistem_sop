<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | E-Monev SOP BPS Banten</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap');

        body {
            background: linear-gradient(135deg, #0d47a1 0%, #1976d2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: none;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        .btn-bps {
            background: #2196f3;
            color: white;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-bps:hover {
            background: #1565c0;
            transform: translateY(-2px);
        }
        .form-control {
            border-radius: 10px;
            padding: 12px;
            border: 1px solid #dee2e6;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #2196f3;
        }
        .input-group-text {
            border-radius: 10px;
            border: 1px solid #dee2e6;
        }
        .logo-bps {
            width: 100px;
            margin-bottom: 20px;
        }
        .cursor-pointer {
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card login-card p-4 shadow-lg">
                <div class="text-center">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/2/28/Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg" class="logo-bps" alt="BPS Logo">
                    <h4 class="fw-bold text-dark">E-Monev SOP</h4>
                    <p class="text-muted small">BPS Provinsi Banten</p>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger border-0 small text-center">{{ session('error') }}</div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" class="mt-4">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-primary"></i></span>
                            <input type="text" name="username" class="form-control bg-light border-start-0" placeholder="Masukkan username" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-secondary">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-primary"></i></span>
                            <input type="password" name="password" id="password" class="form-control bg-light border-start-0 border-end-0" placeholder="••••••••" required>
                            <span class="input-group-text bg-light border-start-0 cursor-pointer" onclick="togglePassword()">
                                <i class="bi bi-eye-slash text-muted" id="toggleIcon"></i>
                            </span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-bps w-100 shadow-sm mb-2">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Masuk Sekarang
                    </button>
                </form>

                <div class="text-center mt-4">
                    <small class="text-muted">&copy; 2026 BPS Provinsi Banten</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const passwordField = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.classList.remove('bi-eye-slash');
            toggleIcon.classList.add('bi-eye');
            toggleIcon.classList.remove('text-muted');
            toggleIcon.classList.add('text-primary');
        } else {
            passwordField.type = 'password';
            toggleIcon.classList.remove('bi-eye');
            toggleIcon.classList.add('bi-eye-slash');
            toggleIcon.classList.remove('text-primary');
            toggleIcon.classList.add('text-muted');
        }
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
