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
        :root{
            --bg-base: #0F172A; /* background general */
            --card-bg: #111827; /* tarjeta */
            --card-radius: 24px;
            --text-primary: #F1F5F9; /* title */
            --text-secondary: #94A3B8; /* labels */
            --border: #1E293B; /* input borders */
            --accent: #F97316; /* focus / icons */
            --accent-amber: #F59E0B; /* forgot */
            --accent-red: #EF4444; /* sign up */
            --button-grad-from: #F97316;
            --button-grad-to: #FB923C;
        }

        *{box-sizing:border-box;margin:0;padding:0}
        html,body{height:100%}

        body{
            background: var(--bg-base);
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
            color:var(--text-primary);
            -webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;
            min-height:100vh;display:flex;align-items:center;justify-content:center;overflow:hidden;
        }

        /* Bokeh / difuminado cinematico sobre fondo oscuro */
        .bg-bokeh{
            position:fixed;inset:0;z-index:0;pointer-events:none;background:linear-gradient(135deg, rgba(10,20,40,0.9) 0%, rgba(14,23,40,0.85) 40%, rgba(15,23,42,0.9) 100%);
        }

        .bg-bokeh::before{
            content:'';position:absolute;inset:-10% -5% -10% -5%;background:
                radial-gradient(520px 520px at 8% 20%, rgba(14,82,160,0.22), transparent 36%),
                radial-gradient(420px 420px at 90% 82%, rgba(249,115,22,0.12), transparent 36%),
                radial-gradient(700px 400px at 50% 30%, rgba(34,99,255,0.10), transparent 30%),
                radial-gradient(480px 480px at 30% 86%, rgba(59,130,246,0.10), transparent 36%);
            filter:blur(60px) saturate(118%);mix-blend-mode:screen;opacity:0.95;
        }

        /* Centered card */
        .card.login-card{position:relative;z-index:2;min-width:520px;max-width:640px;border-radius:var(--card-radius);background:var(--card-bg);border:1px solid rgba(255,255,255,0.04);box-shadow:0 24px 60px rgba(2,6,23,0.7);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);opacity:0.98;padding:48px}

        /* Header */
        .login-head{display:flex;flex-direction:column;align-items:center;gap:12px;margin-bottom:28px}
        .brand-icon{width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--button-grad-from),var(--button-grad-to));box-shadow:0 8px 30px rgba(249,115,22,0.14)}
        .brand-icon svg{width:34px;height:34px;color:var(--card-bg)}
        .brand-title{font-weight:700;font-size:48px;line-height:1;color:var(--text-primary);letter-spacing:-0.5px}
        .brand-sub{color:var(--text-secondary);font-size:14px}

        form{width:100%}

        .form-row{display:flex;flex-direction:column;gap:16px;margin-bottom:20px}

        .input-with-icon{display:flex;align-items:center;background:transparent;border:1px solid var(--border);border-radius:12px;padding:12px 14px;color:var(--text-primary)}
        .input-with-icon .icon{width:36px;height:36px;display:flex;align-items:center;justify-content:center;border-radius:8px;margin-right:12px;color:var(--accent)}
        .input-with-icon input{flex:1;background:transparent;border:0;color:var(--text-primary);outline:none;font-size:15px}
        .input-with-icon input::placeholder{color:var(--text-secondary)}

        .input-with-icon:focus-within{box-shadow:0 6px 24px rgba(249,115,22,0.06);border-color:var(--accent)}

        .actions{display:flex;align-items:center;justify-content:space-between;margin-top:8px}
        .link-left{color:var(--accent-amber);text-decoration:none;font-weight:600}
        .link-right{color:var(--accent-red);text-decoration:none;font-weight:600}

        .btn-signin{display:block;width:100%;padding:14px 18px;border-radius:12px;border:0;background:linear-gradient(90deg,var(--button-grad-from),var(--button-grad-to));color:#fff;font-weight:800;letter-spacing:0.6px;box-shadow:0 10px 30px rgba(249,115,22,0.12);transition:transform .14s ease,box-shadow .14s ease}
        .btn-signin:hover{transform:translateY(-2px);box-shadow:0 18px 40px rgba(249,115,22,0.18)}

        .small-note{display:flex;justify-content:space-between;margin-top:14px;font-size:13px;color:var(--text-secondary)}

        @media (max-width:900px){.card.login-card{min-width:360px;width:92%;padding:28px}.brand-title{font-size:32px}}

    </style>
</head>
    <body>
    <div class="bg-bokeh"></div>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card login-card shadow">
            <div class="login-head">
                <div class="brand-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="1" y="4" width="14" height="10" rx="1"></rect>
                        <path d="M15 8h3l4 4v3"></path>
                        <circle cx="6.5" cy="17" r="1.5"></circle>
                        <circle cx="18.5" cy="17" r="1.5"></circle>
                    </svg>
                </div>
                <div class="brand-title">TruckSisX</div>
                <div class="brand-sub">Trazabilidad de repuestos y flotas pesadas</div>
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

            <form method="POST" id="loginForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                <div class="form-row">
                    <div class="input-with-icon">
                        <div class="icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <input id="usuario" name="usuario" type="text" placeholder="Usuario o correo" required autocomplete="username">
                    </div>

                    <div class="input-with-icon">
                        <div class="icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </div>
                        <input id="contrasena" name="contrasena" type="password" placeholder="Contraseña" required autocomplete="current-password">
                        <button type="button" id="togglePassword" aria-label="Mostrar contraseña" style="background:none;border:none;color:var(--text-secondary);margin-left:12px">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-signin">INICIAR SESIÓN</button>

                <!-- Links removed as requested -->
            </form>
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
