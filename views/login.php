<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Trucksisx</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* ========== RESET Y BASE ========== */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* ========== TIPOGRAFÍA Y BODY ========== */
        body {
            background: linear-gradient(135deg, #1E3A8A 0%, #3B82F6 50%, #FBBF24 100%);
            color: #374151;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        /* Elementos decorativos de fondo */
        body::before {
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="15" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="80" r="20" fill="rgba(251,191,36,0.2)"/><circle cx="70" cy="30" r="10" fill="rgba(255,255,255,0.15)"/></svg>');
            content: '';
            height: 100%;
            left: 0;
            position: absolute;
            top: 0;
            width: 100%;
            z-index: 0;
        }

        /* ========== CONTENEDOR PRINCIPAL ========== */
        .container {
            position: relative;
            z-index: 1;
        }

        /* ========== CARD DE LOGIN ========== */
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            max-width: 450px;
            min-width: 400px;
            position: relative;
            transition: all 0.3s ease;
        }

        .login-card:hover {
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.25);
            transform: translateY(-5px);
        }

        /* ========== LOGO Y BRANDING ========== */
        .logo-container {
            margin-bottom: 2rem;
            position: relative;
            text-align: center;
        }

        .logo-icon {
            background: linear-gradient(135deg, #FBBF24 0%, #F59E0B 100%);
            border-radius: 50%;
            color: #1E3A8A;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            height: 80px;
            margin-bottom: 1rem;
            width: 80px;
            box-shadow: 0 8px 25px rgba(251, 191, 36, 0.3);
            animation: logoFloat 3s ease-in-out infinite;
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .system-title {
            color: #1E3A8A;
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            text-shadow: 0 2px 4px rgba(30, 58, 138, 0.1);
        }

        .system-subtitle {
            color: #6B7280;
            font-size: 0.95rem;
            font-weight: 500;
        }

        /* ========== FORMULARIOS ========== */
        .form-label {
            color: #1E3A8A;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }

        .form-label i {
            color: #FBBF24;
            margin-right: 0.5rem;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            color: #374151;
            font-size: 16px;
            padding: 0.875rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: #FFFFFF;
            border-color: #1E3A8A;
            box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
            outline: none;
            transform: translateY(-1px);
        }

        .form-control::placeholder {
            color: #9CA3AF;
        }

        /* ========== BOTONES ========== */
        .btn {
            border-radius: 12px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            min-height: 48px;
            padding: 0.875rem 1.5rem;
            position: relative;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #FBBF24 0%, #F59E0B 100%);
            box-shadow: 0 6px 20px rgba(251, 191, 36, 0.4);
            color: #1E3A8A !important;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            box-shadow: 0 8px 25px rgba(251, 191, 36, 0.5);
            color: #1E3A8A !important;
            transform: translateY(-2px);
        }

        .btn-outline-secondary {
            background: #FFFFFF;
            border: 2px solid #D1D5DB;
            color: #6B7280;
        }

        .btn-outline-secondary:hover {
            background: #F9FAFB;
            border-color: #1E3A8A;
            color: #1E3A8A;
        }

        /* ========== ALERTAS ========== */
        .alert {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            font-weight: 500;
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.1) 100%);
            color: #DC2626;
            border-left: 4px solid #EF4444;
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.1) 100%);
            color: #059669;
            border-left: 4px solid #10B981;
        }

        .alert-info {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.1) 100%);
            color: #2563EB;
            border-left: 4px solid #3B82F6;
        }

        /* ========== INPUT GROUP ========== */
        .input-group .form-control {
            border-radius: 12px 0 0 12px;
        }

        .input-group .btn {
            border-radius: 0 12px 12px 0;
            border-left: none;
        }

        /* ========== ANIMACIONES ========== */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-card {
            animation: slideIn 0.6s ease-out;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .btn-primary:active {
            animation: pulse 0.3s ease;
        }

        /* ========== RESPONSIVIDAD ========== */
        @media (max-width: 576px) {
            .login-card {
                margin: 1rem;
                min-width: auto;
                padding: 2rem 1.5rem !important;
            }

            .logo-icon {
                height: 60px;
                width: 60px;
                font-size: 2rem;
            }

            .system-title {
                font-size: 1.5rem;
            }

            .form-control {
                font-size: 16px; /* Evita zoom en iOS */
                padding: 0.75rem;
            }
        }

        /* ========== UTILIDADES ========== */
        .text-corporate {
            color: #1E3A8A !important;
        }

        .text-accent {
            color: #FBBF24 !important;
        }

        .bg-gradient-corporate {
            background: linear-gradient(135deg, #1E3A8A 0%, #3B82F6 100%) !important;
        }

        /* ========== EFECTOS ESPECIALES ========== */
        .login-card::before {
            background: linear-gradient(45deg, transparent 30%, rgba(251, 191, 36, 0.1) 50%, transparent 70%);
            content: '';
            height: 100%;
            left: -100%;
            position: absolute;
            top: 0;
            transition: left 0.8s ease;
            width: 100%;
            z-index: -1;
        }

        .login-card:hover::before {
            left: 100%;
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
