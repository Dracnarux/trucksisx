<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Trucksisx</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/login-bg.css" rel="stylesheet">
    <style>
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        .btn-primary {
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
        }
        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo-icon {
            font-size: 3rem;
            color: #0d6efd;
            margin-bottom: 15px;
        }
        .system-title {
            color: #333;
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        .system-subtitle {
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</head>
<body class="login-bg">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card login-card p-5 shadow" style="min-width:400px; max-width:450px;">
            <div class="logo-container">
                <div class="logo-icon">
                    <i class="bi bi-truck"></i>
                </div>
                <div class="system-title">TruckSISX</div>
                <div class="system-subtitle">Sistema de Gestión de Flotas</div>
            </div>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['logout_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= htmlspecialchars($_SESSION['logout_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['logout_message']); ?>
            <?php endif; ?>
            
            <form method="POST" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                
                <div class="mb-3">
                    <label for="usuario" class="form-label">
                        <i class="bi bi-person-circle me-2"></i>Usuario o correo
                    </label>
                    <input type="text" class="form-control" id="usuario" name="usuario" 
                           placeholder="Ingrese su usuario o correo" required 
                           value="<?= isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : '' ?>">
                </div>
                
                <div class="mb-4">
                    <label for="contrasena" class="form-label">
                        <i class="bi bi-shield-lock me-2"></i>Contraseña
                    </label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="contrasena" name="contrasena" 
                               placeholder="Ingrese su contraseña" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                </button>
            </form>
            
            <div class="text-center">
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Usuarios de prueba: admin, tecnico, conduc
                </small>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('contrasena');
            const icon = document.getElementById('toggleIcon');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
        
        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const usuario = document.getElementById('usuario').value.trim();
            const contrasena = document.getElementById('contrasena').value;
            
            if (usuario === '' || contrasena === '') {
                e.preventDefault();
                alert('Por favor, complete todos los campos');
            }
        });
    </script>
</body>
</html>
