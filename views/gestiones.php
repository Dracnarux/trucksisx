<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Repuestos | TruckSISX</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            margin: 2rem auto;
            overflow: hidden;
        }

        .header-section {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
            color: white;
            padding: 3rem 2rem;
            position: relative;
            overflow: hidden;
        }

        .header-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translate(0px, 0px) rotate(0deg); }
            33% { transform: translate(30px, -30px) rotate(120deg); }
            66% { transform: translate(-20px, 20px) rotate(240deg); }
        }

        .content-section {
            padding: 3rem 2rem;
        }

        .module-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .module-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            border-color: var(--card-color);
        }

        .module-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--card-color);
            border-radius: 12px 12px 0 0;
        }

        .card-body {
            padding: 2rem;
            text-align: center;
            position: relative;
        }

        .module-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 1.5rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--card-color);
            background: rgba(100, 116, 139, 0.1);
            border: 1px solid rgba(100, 116, 139, 0.2);
        }

        .module-title {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #1f2937;
        }

        .module-description {
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }

        .module-btn {
            background: var(--card-color);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
            font-size: 0.9rem;
        }

        .module-btn:hover {
            background: color-mix(in srgb, var(--card-color) 85%, black);
            color: white;
        }

        .categories { --card-color: #64748b; }
        .subcategories { --card-color: #475569; }
        .parts { --card-color: #334155; }
        .providers { --card-color: #1e293b; }

        .stats-section {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .stat-item {
            text-align: center;
            padding: 1rem;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: #64748b;
            display: block;
        }

        .stat-label {
            color: #6b7280;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .breadcrumb-custom {
            background: transparent;
            padding: 0;
            margin-bottom: 2rem;
        }

        .breadcrumb-custom .breadcrumb-item a {
            color: #94a3b8;
            text-decoration: none;
        }

        .welcome-text {
            position: relative;
            z-index: 1;
        }

        .user-info {
            background: rgba(255, 255, 255, 0.2);
            padding: 1rem 1.5rem;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        @media (max-width: 768px) {
            .header-section {
                padding: 2rem 1rem;
            }
            .content-section {
                padding: 2rem 1rem;
            }
            .module-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="main-container">
        <!-- Header Section -->
        <div class="header-section">
            <div class="welcome-text">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <nav aria-label="breadcrumb" class="breadcrumb-custom">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="dashboard.php" class="text-white-50">
                                        <i class="bi bi-house-door me-1"></i>Dashboard
                                    </a>
                                </li>
                                <li class="breadcrumb-item active text-white" aria-current="page">
                                    Gestión de Repuestos
                                </li>
                            </ol>
                        </nav>
                        <h1 class="display-4 fw-bold mb-2">
                            <i class="bi bi-gear-wide-connected me-3"></i>Gestión de Repuestos
                        </h1>
                        <p class="lead mb-0 text-white-50">
                            Centro de control para la administración completa del inventario de repuestos y proveedores
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="user-info">
                            <div class="d-flex align-items-center justify-content-end">
                                <i class="bi bi-person-circle me-2 fs-4"></i>
                                <div class="text-start">
                                    <small class="d-block text-white-50">Bienvenido/a</small>
                                    <strong class="text-white">
                                        <?= htmlspecialchars($_SESSION['usuario']['usuario'] ?? $_SESSION['usuario']['nombre'] ?? 'Usuario') ?>
                                    </strong>
                                    <?php if (isset($_SESSION['usuario']['rol'])): ?>
                                    <small class="d-block text-white-50" style="font-size: 0.75rem;">
                                        <?= ucfirst(htmlspecialchars($_SESSION['usuario']['rol'])) ?>
                                    </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Section -->
        <div class="content-section">
            
            <!-- Módulos de Gestión -->
            <div class="row g-4 mb-5">
                <!-- Categorías de Repuestos -->
                <div class="col-lg-4 col-md-6">
                    <div class="module-card categories">
                        <div class="card-body">
                            <div class="module-icon">
                                <i class="bi bi-collection"></i>
                            </div>
                            <h3 class="module-title">Categorías de Repuestos</h3>
                            <p class="module-description">
                                Organiza y clasifica los repuestos por categorías principales. 
                                Gestiona la estructura jerárquica de tu inventario.
                            </p>
                            <a href="cat_repu.php" class="module-btn">
                                <i class="bi bi-arrow-right-circle"></i>
                                Gestionar Categorías
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Subcategorías de Repuestos -->
                <div class="col-lg-4 col-md-6">
                    <div class="module-card subcategories">
                        <div class="card-body">
                            <div class="module-icon">
                                <i class="bi bi-diagram-3"></i>
                            </div>
                            <h3 class="module-title">Subcategorías</h3>
                            <p class="module-description">
                                Define subcategorías específicas para una clasificación detallada. 
                                Mejora la organización y búsqueda de repuestos.
                            </p>
                            <a href="subcat_repu.php" class="module-btn">
                                <i class="bi bi-arrow-right-circle"></i>
                                Gestionar Subcategorías
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Repuestos -->
                <div class="col-lg-4 col-md-6">
                    <div class="module-card parts">
                        <div class="card-body">
                            <div class="module-icon">
                                <i class="bi bi-gear-wide-connected"></i>
                            </div>
                            <h3 class="module-title">Inventario de Repuestos</h3>
                            <p class="module-description">
                                Administra el catálogo completo de repuestos. Registro, edición, 
                                consulta y control de stock de todos los componentes.
                            </p>
                            <a href="repue.php" class="module-btn">
                                <i class="bi bi-arrow-right-circle"></i>
                                Gestionar Inventario
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Proveedores -->
                <div class="col-lg-4 col-md-6">
                    <div class="module-card providers">
                        <div class="card-body">
                            <div class="module-icon">
                                <i class="bi bi-building"></i>
                            </div>
                            <h3 class="module-title">Red de Proveedores</h3>
                            <p class="module-description">
                                Gestiona tu red de proveedores. Información de contacto, 
                                condiciones comerciales, tiempos de entrega y cobertura.
                            </p>
                            <a href="proveedor.php" class="module-btn">
                                <i class="bi bi-arrow-right-circle"></i>
                                Gestionar Proveedores
                            </a>
                        </div>
                    </div>
                </div>


            </div>

            <!-- Navegación -->
            <div class="row">
                <div class="col-12 text-center">
                    <a href="dashboard.php" class="btn btn-outline-secondary btn-lg">
                        <i class="bi bi-arrow-left me-2"></i>Volver al Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animaciones suaves al cargar
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.module-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(30px)';
                    card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    
                    requestAnimationFrame(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    });
                }, index * 100);
            });
        });

        // Efecto hover mejorado
        document.querySelectorAll('.module-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Atajos de teclado para módulos de repuestos
        document.addEventListener('keydown', function(e) {
            if (e.altKey) {
                switch(e.key) {
                    case '1':
                        e.preventDefault();
                        window.location.href = 'cat_repu.php';
                        break;
                    case '2':
                        e.preventDefault();
                        window.location.href = 'subcat_repu.php';
                        break;
                    case '3':
                        e.preventDefault();
                        window.location.href = 'repue.php';
                        break;
                    case '4':
                        e.preventDefault();
                        window.location.href = 'proveedor.php';
                        break;
                }
            }
        });

        // Mostrar tooltips para atajos
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>
</html>
