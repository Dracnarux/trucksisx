<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}
$rol_conductor = isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'conductor';
$usuario_nombre = $_SESSION['usuario']['nombre'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Alertas - TruckSISX</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../assets/css/truck-alerts.css" rel="stylesheet">
    
    <style>
        /* HEADER PRINCIPAL */
        .main-header {
            background: linear-gradient(135deg, #1E3A8A 0%, #3B82F6 100%);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(30, 58, 138, 0.2);
            color: #FFFFFF;
            margin-bottom: 2rem;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }
        .main-header::before {
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="2"/></svg>');
            content: '';
            height: 200px;
            opacity: 0.1;
            position: absolute;
            right: -50px;
            top: -50px;
            width: 200px;
        }
        .main-header h1 {
            color: #FFFFFF;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 2;
        }
        .main-header .lead {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }
        /* TARJETAS Y CONTENEDORES */
        .truck-diagram-container, .card, .tab-content .card {
            background: #FFFFFF;
            border: 1px solid rgba(209, 213, 219, 0.3);
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .truck-diagram-container {
            padding: 2rem 1.5rem;
        }
        .card:hover, .truck-diagram-container:hover {
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }
        /* BOTONES */
        .btn {
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 500;
            min-height: 44px;
            padding: 0.75rem 1.5rem;
            position: relative;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .btn:focus {
            box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.3);
            outline: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #FBBF24 0%, #F59E0B 100%);
            box-shadow: 0 4px 12px rgba(251, 191, 36, 0.3);
            color: #1E3A8A !important;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            box-shadow: 0 6px 20px rgba(251, 191, 36, 0.4);
            color: #1E3A8A !important;
            transform: translateY(-2px);
        }
        .btn-outline-primary {
            background: #FFFFFF;
            border: 2px solid #1E3A8A;
            color: #1E3A8A !important;
        }
        .btn-outline-primary:hover {
            background: #1E3A8A;
            color: #FFFFFF !important;
            transform: translateY(-2px);
        }
        .btn-light {
            background: rgba(13,110,253,0.08) !important;
            color: #111 !important;
            border: 1px solid rgba(13,110,253,0.15) !important;
        }
        /* BADGES */
        .badge {
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            padding: 0.5rem 1rem;
        }
        .badge.bg-success {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%) !important;
        }
        .badge.bg-warning {
            background: linear-gradient(135deg, #FBBF24 0%, #F59E0B 100%) !important;
            color: #1E3A8A !important;
        }
        .badge.bg-danger {
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%) !important;
        }
        .badge.bg-info {
            background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%) !important;
        }
        /* FORMULARIOS */
        .form-control, .form-select {
            background: #FFFFFF;
            border: 2px solid #D1D5DB;
            border-radius: 8px;
            color: #374151;
            font-size: 16px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #1E3A8A;
            box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
            outline: none;
        }
        .form-label {
            color: #1E3A8A;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        /* ANIMACIONES */
        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-slide-up { animation: slideInUp 0.6s ease-out; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .animate-fade-in { animation: fadeIn 0.4s ease-out; }
        }
        
        /* Estilos para el botón de eliminar alerta */
        .btn-danger:hover {
            background-color: #c82333 !important;
            border-color: #bd2130 !important;
            transform: scale(1.05);
            transition: all 0.2s ease;
        }
        
        /* Animación para la eliminación de alertas */
        .alert-deleting {
            transition: all 0.3s ease;
            transform: translateX(-100%);
            opacity: 0;
        }
        
        /* Mejorar la presentación de los botones de acción */
        .btn-group .btn {
            margin: 1px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* Estilos específicos para botones de alertas recientes */
        .alert-actions {
            gap: 0.5rem !important;
        }
        
        .alert-actions .btn {
            min-width: 80px;
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.2s ease;
        }
        
        .alert-actions .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        /* Responsivo para pantallas pequeñas */
        @media (max-width: 576px) {
            .alert-actions {
                justify-content: center !important;
            }
            
            .alert-actions .btn {
                min-width: 70px;
                font-size: 0.8rem;
            }
        }
        
        .btn-group .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="container-fluid py-4">
        <div class="main-header animate-fade-in mb-4">
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between">
                <div>
                    <h1 class="mb-2"><i class="fas fa-truck text-accent"></i> Sistema de Alertas</h1>
                    <span class="lead">Monitoreo en tiempo real de alertas para camiones doble troque</span>
                    <div class="mt-2">
                        <small class="text-white-50" id="last-update">
                            <i class="fas fa-clock"></i> Última actualización: <span id="last-update-time">--</span>
                        </small>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-3 mt-md-0">
                    <a href="dashboard.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left"></i> Volver al Dashboard
                    </a>
                    <button class="btn btn-primary" id="refresh-alerts">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
            </div>
        </div>

    <div class="container">
        <!-- Diagrama del Camión -->
        <div class="row">
            <div class="col-12">
                <div class="truck-diagram-container">
                    <h3 class="text-center mb-3">
                        <i class="fas fa-truck"></i> Camión Doble Troque - Vista Superior
                    </h3>
                    <div id="truck-diagram-container"></div>
                </div>
            </div>
        </div>

        <!-- Tabs para diferentes vistas -->
        <div class="row">
            <div class="col-12">
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <button class="nav-link active" id="nav-alerts-tab" data-bs-toggle="tab" 
                                data-bs-target="#nav-alerts" type="button" role="tab" 
                                aria-controls="nav-alerts" aria-selected="true">
                            <i class="fas fa-exclamation-triangle"></i> Alertas Recientes
                        </button>
                        <button class="nav-link" id="nav-statistics-tab" data-bs-toggle="tab" 
                                data-bs-target="#nav-statistics" type="button" role="tab" 
                                aria-controls="nav-statistics" aria-selected="false">
                            <i class="fas fa-chart-bar"></i> Estadísticas por Posición
                        </button>
                        <button class="nav-link" id="nav-work-orders-tab" data-bs-toggle="tab" 
                                data-bs-target="#nav-work-orders" type="button" role="tab" 
                                aria-controls="nav-work-orders" aria-selected="false">
                            <i class="fas fa-clipboard-list"></i> Órdenes de Trabajo<?php if ($rol_conductor): ?> <small>(Solo lectura)</small><?php endif; ?>
                        </button>
                    </div>
                </nav>
                
                <div class="tab-content" id="nav-tabContent">
                    <!-- Tab de Alertas -->
                    <div class="tab-pane fade show active" id="nav-alerts" role="tabpanel" 
                         aria-labelledby="nav-alerts-tab">
                        <div class="mt-3">
                            <h4>Alertas de Llantas Recientes</h4>
                            <div id="alerts-list" class="alerts-dashboard">
                                <!-- Las alertas se cargarán dinámicamente -->
                            </div>
                        </div>
                    </div>

                    <!-- Tab de Estadísticas -->
                    <div class="tab-pane fade" id="nav-statistics" role="tabpanel" 
                         aria-labelledby="nav-statistics-tab">
                        <div class="mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4>Estadísticas por Posición de Llanta</h4>
                                <div class="btn-group" role="group" aria-label="Tipo de vista">
                                    <input type="radio" class="btn-check" name="view-type" id="chart-view" checked>
                                    <label class="btn btn-outline-primary btn-sm" for="chart-view">
                                        <i class="fas fa-chart-bar"></i> Gráfico
                                    </label>
                                    <input type="radio" class="btn-check" name="view-type" id="table-view">
                                    <label class="btn btn-outline-primary btn-sm" for="table-view">
                                        <i class="fas fa-table"></i> Tabla
                                    </label>
                                    <input type="radio" class="btn-check" name="view-type" id="both-view">
                                    <label class="btn btn-outline-primary btn-sm" for="both-view">
                                        <i class="fas fa-th-large"></i> Ambos
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Controles de filtro -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <select id="chart-type-selector" class="form-select form-select-sm">
                                        <option value="bar">Gráfico de Barras</option>
                                        <option value="doughnut">Gráfico Circular</option>
                                        <option value="line">Gráfico de Líneas</option>
                                        <option value="radar">Gráfico Radar</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select id="data-type-selector" class="form-select form-select-sm">
                                        <option value="all">Todas las Métricas</option>
                                        <option value="total">Solo Total</option>
                                        <option value="active">Solo Activas</option>
                                        <option value="critical">Solo Críticas</option>
                                        <option value="percentages">Solo Porcentajes</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button id="export-chart" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-download"></i> Exportar
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Resumen de estadísticas -->
                            <div class="row mb-4" id="statistics-summary">
                                <!-- Se llenará dinámicamente -->
                            </div>
                            
                            <div id="chart-container" class="row animate-slide-up">
                                <div class="col-12">
                                    <div class="card shadow-corporate">
                                        <div class="card-body">
                                            <div style="position: relative; height: 400px;">
                                                <canvas id="tire-statistics-chart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="statistics-table" class="mt-3" style="animation:none;">
                                <!-- Tabla de estadísticas se cargará dinámicamente -->
                            </div>
                        </div>
                    </div>

                    <!-- Tab de Órdenes de Trabajo -->
                    <div class="tab-pane fade" id="nav-work-orders" role="tabpanel" 
                         aria-labelledby="nav-work-orders-tab">
                        <div class="mt-3">
                            <h4>Órdenes de Trabajo Generadas por Alertas<?php if ($rol_conductor): ?> <small class="text-muted">(Solo visualización)</small><?php endif; ?></h4>
                            <?php if (!$rol_conductor): ?>
                            <a href="orden_trabajo.php" class="btn btn-primary mb-3">
                                <i class="fas fa-plus"></i> Ir a Gestión de Órdenes de Trabajo
                            </a>
                            <?php else: ?>
                            <a href="orden_trabajo.php" class="btn btn-outline-primary mb-3">
                                <i class="fas fa-eye"></i> Ver Todas las Órdenes de Trabajo
                            </a>
                            <?php endif; ?>
                            <div id="work-orders-list">
                                <!-- Órdenes de trabajo se cargarán dinámicamente -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/js/truck-alerts.js?v=2"></script>
    
    <script>
        // Extender funcionalidad para estadísticas y gráficos
        document.addEventListener('DOMContentLoaded', function() {
            // Cargar dashboard inicial
            loadDashboard();
            
            // Configurar eventos de tabs
            document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
                tab.addEventListener('shown.bs.tab', function(event) {
                    const target = event.target.getAttribute('data-bs-target');
                    if (target === '#nav-statistics') {
                        loadStatistics();
                    } else if (target === '#nav-work-orders') {
                        loadWorkOrders();
                    }
                });
            });
            
            // Configurar evento del botón de actualizar
            document.getElementById('refresh-alerts').addEventListener('click', function() {
                // Agregar animación de loading
                const button = this;
                const icon = button.querySelector('i');
                const originalClass = icon.className;
                
                icon.className = 'fas fa-spinner fa-spin';
                button.disabled = true;
                
                // Recargar datos según el tab activo
                const activeTab = document.querySelector('.nav-link.active').getAttribute('data-bs-target');
                
                Promise.all([
                    loadDashboard(),
                    activeTab === '#nav-statistics' ? loadStatistics() : Promise.resolve(),
                    activeTab === '#nav-work-orders' ? loadWorkOrders() : Promise.resolve()
                ]).finally(() => {
                    // Restaurar botón
                    setTimeout(() => {
                        icon.className = originalClass;
                        button.disabled = false;
                    }, 500);
                });
            });
            
            // Configurar actualización automática cada 30 segundos
            setInterval(function() {
                // Mostrar indicador de actualización automática
                const refreshBtn = document.getElementById('refresh-alerts');
                const icon = refreshBtn.querySelector('i');
                const originalClass = icon.className;
                
                // Cambiar temporalmente el icono para indicar actualización automática
                icon.className = 'fas fa-sync-alt fa-spin';
                icon.style.color = '#28a745'; // Verde para indicar auto-actualización
                
                loadDashboard();
                // También actualizar el tab activo si no es el de alertas principales
                const activeTab = document.querySelector('.nav-link.active');
                if (activeTab) {
                    const target = activeTab.getAttribute('data-bs-target');
                    if (target === '#nav-statistics') {
                        loadStatistics();
                    } else if (target === '#nav-work-orders') {
                        loadWorkOrders();
                    }
                }
                
                // Restaurar el icono después de 1 segundo
                setTimeout(() => {
                    icon.className = originalClass;
                    icon.style.color = '';
                }, 1000);
            }, 30000); // 30 segundos
        });

        // Actualizar timestamp de última actualización
        function updateLastUpdateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('es-ES', { 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit' 
            });
            document.getElementById('last-update-time').textContent = timeString;
        }

        // Cargar dashboard general
        async function loadDashboard() {
            try {
                const response = await fetch(`../controllers/AlertController.php?action=getDashboard`);
                const data = await response.json();
                
                if (data.success) {
                    const dashboard = data.dashboard;
                    displayRecentAlerts(dashboard.recent_alerts);
                    updateLastUpdateTime();
                }
            } catch (error) {
                console.error('Error loading dashboard:', error);
            }
        }

        // Mostrar alertas recientes en el dashboard
        function displayRecentAlerts(alerts) {
            const container = document.getElementById('alerts-list');
            if (!container) return;
            if (!alerts || alerts.length === 0) {
                container.innerHTML = '<p>No hay alertas recientes.</p>';
                return;
            }
            
            // Eliminar duplicados basándose en el ID de la alerta
            const uniqueAlerts = alerts.filter((alert, index, self) => 
                index === self.findIndex(a => a.id === alert.id)
            );
            
            const alertsHTML = uniqueAlerts.map(alert => `
                <div class="card mb-3 shadow-corporate animate-slide-up">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title mb-1 text-corporate"><i class="fas fa-exclamation-triangle me-2 text-danger"></i>${alert.descripcion}</h5>
                                <p class="card-text mb-1">${alert.observaciones || ''}</p>
                                <small class="text-muted">
                                    <i class="fas fa-calendar"></i> ${alert.fecha_hora || ''}
                                    ${alert.placa ? `| <i class=\"fas fa-truck\"></i> ${alert.placa}` : ''}
                                    ${alert.orden_trabajo ? `| <i class=\"fas fa-clipboard-list text-primary\"></i> Orden: ${alert.orden_trabajo}` : ''}
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-${alert.prioridad === 'alta' || alert.prioridad === 'critica' ? 'danger' : alert.prioridad === 'media' ? 'warning' : 'info'} mb-1 text-uppercase">${alert.prioridad}</span>
                                <br>
                                <span class="badge bg-${alert.estado === 'activa' ? 'danger' : alert.estado === 'en_proceso' ? 'warning' : alert.estado === 'resuelta' ? 'success' : 'secondary'} mt-1 text-uppercase">${alert.estado}</span>
                                <?php if (!$rol_conductor): ?>
                                <br>
                                <div class="d-flex flex-wrap gap-1 mt-2 justify-content-end alert-actions">
                                    <button class="btn btn-sm btn-outline-success" onclick="updateAlertStatus(${alert.id}, 'resuelta')" title="Marcar como resuelta">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" onclick="updateAlertStatus(${alert.id}, 'en_proceso')" title="Marcar en proceso">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="updateAlertStatus(${alert.id}, 'activa')" title="Marcar como activa">
                                        <i class="fas fa-exclamation"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteAlert(${alert.id})" title="Eliminar alerta">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
            container.innerHTML = alertsHTML;
        }

        // Cargar estadísticas por posición
        async function loadStatistics() {
            try {
                const response = await fetch(`../controllers/AlertController.php?action=getTireAlerts`);
                const data = await response.json();
                
                if (data.success && data.statistics) {
                    // Guardar datos para uso posterior
                    window.currentStatistics = data.statistics;
                    
                    createStatisticsChart(data.statistics);
                    createStatisticsTable(data.statistics);
                    updateLastUpdateTime();
                    
                    // Configurar controles si no se ha hecho
                    if (!window.viewControlsSetup) {
                        setupViewControls();
                        window.viewControlsSetup = true;
                    }
                }
            } catch (error) {
                console.error('Error loading statistics:', error);
                document.getElementById('statistics-summary').innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            Error al cargar las estadísticas. Por favor, intente nuevamente.
                        </div>
                    </div>
                `;
            }
        }

        // Crear gráfico de estadísticas mejorado
        function createStatisticsChart(statistics) {
            const ctx = document.getElementById('tire-statistics-chart').getContext('2d');
            
            // Destruir gráfico anterior si existe
            if (window.statisticsChart) {
                window.statisticsChart.destroy();
            }
            
            console.log('Estadísticas para gráfico:', statistics.length, statistics);
            
            const labels = statistics.map(stat => {
                const positions = {
                    'direccion_izquierda': 'Dirección Izquierda',
                    'direccion_derecha': 'Dirección Derecha',
                    'traccion1_izquierda': 'Tracción 1 Izquierda',
                    'traccion1_derecha': 'Tracción 1 Derecha',
                    'traccion1_izquierda2': 'Tracción 1 Izquierda 2',
                    'traccion1_derecha2': 'Tracción 1 Derecha 2',
                    'traccion2_izquierda': 'Tracción 2 Izquierda',
                    'traccion2_derecha': 'Tracción 2 Derecha',
                    'traccion2_izquierda2': 'Tracción 2 Izquierda 2',
                    'traccion2_derecha2': 'Tracción 2 Derecha 2'
                };
                return positions[stat.posicion_llanta] || stat.posicion_llanta;
            });
            
            const chartType = document.getElementById('chart-type-selector').value || 'bar';
            const dataType = document.getElementById('data-type-selector').value || 'all';
            
            // Preparar datasets según el tipo de datos seleccionado
            let datasets = [];
            
            if (dataType === 'all' || dataType === 'total') {
                datasets.push({
                    label: 'Total de Alertas',
                    data: statistics.map(stat => stat.total_alertas),
                    backgroundColor: 'rgba(100, 116, 139, 0.8)',
                    borderColor: 'rgba(100, 116, 139, 1)',
                    borderWidth: 2,
                    borderRadius: 4,
                    tension: 0.4
                });
            }
            
            if (dataType === 'all' || dataType === 'active') {
                datasets.push({
                    label: 'Alertas Activas',
                    data: statistics.map(stat => stat.alertas_activas),
                    backgroundColor: 'rgba(251, 191, 36, 0.8)',
                    borderColor: 'rgba(251, 191, 36, 1)',
                    borderWidth: 2,
                    borderRadius: 4,
                    tension: 0.4
                });
            }
            
            if (dataType === 'all' || dataType === 'critical') {
                datasets.push({
                    label: 'Alertas Críticas',
                    data: statistics.map(stat => stat.alertas_criticas),
                    backgroundColor: 'rgba(239, 68, 68, 0.8)',
                    borderColor: 'rgba(239, 68, 68, 1)',
                    borderWidth: 2,
                    borderRadius: 4,
                    tension: 0.4
                });
            }
            
            if (dataType === 'percentages') {
                datasets = [
                    {
                        label: '% Críticas',
                        data: statistics.map(stat => stat.total_alertas > 0 ? 
                            ((stat.alertas_criticas / stat.total_alertas) * 100).toFixed(1) : 0),
                        backgroundColor: 'rgba(239, 68, 68, 0.8)',
                        borderColor: 'rgba(239, 68, 68, 1)',
                        borderWidth: 2
                    },
                    {
                        label: '% Altas',
                        data: statistics.map(stat => stat.total_alertas > 0 ? 
                            (((stat.alertas_altas || 0) / stat.total_alertas) * 100).toFixed(1) : 0),
                        backgroundColor: 'rgba(245, 101, 101, 0.8)',
                        borderColor: 'rgba(245, 101, 101, 1)',
                        borderWidth: 2
                    },
                    {
                        label: '% Medias',
                        data: statistics.map(stat => stat.total_alertas > 0 ? 
                            (((stat.alertas_medias || 0) / stat.total_alertas) * 100).toFixed(1) : 0),
                        backgroundColor: 'rgba(251, 191, 36, 0.8)',
                        borderColor: 'rgba(251, 191, 36, 1)',
                        borderWidth: 2
                    },
                    {
                        label: '% Bajas',
                        data: statistics.map(stat => stat.total_alertas > 0 ? 
                            (((stat.alertas_bajas || 0) / stat.total_alertas) * 100).toFixed(1) : 0),
                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        borderColor: 'rgba(34, 197, 94, 1)',
                        borderWidth: 2
                    }
                ];
            }

            const config = {
                type: chartType,
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Estadísticas de Alertas por Posición de Llanta',
                            font: {
                                size: 16,
                                weight: 'bold'
                            },
                            color: '#374151'
                        },
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#64748b',
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += context.parsed.y || context.parsed;
                                    if (dataType === 'percentages') {
                                        label += '%';
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: chartType === 'radar' ? {} : {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                font: {
                                    size: 11
                                },
                                callback: function(value) {
                                    if (dataType === 'percentages') {
                                        return value + '%';
                                    }
                                    return value;
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 10
                                },
                                maxRotation: 45,
                                minRotation: 0
                            }
                        }
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeInOutQuart'
                    }
                }
            };

            window.statisticsChart = new Chart(ctx, config);
            
            // Crear resumen de estadísticas
            createStatisticsSummary(statistics);
        }

        // Crear resumen de estadísticas
        function createStatisticsSummary(statistics) {
            const container = document.getElementById('statistics-summary');
            
            // Calcular totales
            const totalAlerts = statistics.reduce((sum, stat) => sum + (stat.total_alertas || 0), 0);
            const totalActive = statistics.reduce((sum, stat) => sum + (stat.alertas_activas || 0), 0);
            const totalCritical = statistics.reduce((sum, stat) => sum + (stat.alertas_criticas || 0), 0);
            const totalResolved = totalAlerts - totalActive;
            
            // Encontrar posición con más alertas
            const maxAlerts = statistics.reduce((max, stat) => 
                stat.total_alertas > max.total_alertas ? stat : max, statistics[0] || {});
            
            const positions = {
                'direccion_izquierda': 'Dirección Izquierda',
                'direccion_derecha': 'Dirección Derecha',
                'traccion1_izquierda': 'Tracción 1 Izquierda',
                'traccion1_derecha': 'Tracción 1 Derecha',
                'traccion1_izquierda2': 'Tracción 1 Izquierda 2',
                'traccion1_derecha2': 'Tracción 1 Derecha 2',
                'traccion2_izquierda': 'Tracción 2 Izquierda',
                'traccion2_derecha': 'Tracción 2 Derecha',
                'traccion2_izquierda2': 'Tracción 2 Izquierda 2',
                'traccion2_derecha2': 'Tracción 2 Derecha 2'
            };
            
            container.innerHTML = `
                <div class="col-md-3">
                    <div class="card text-center border-primary">
                        <div class="card-body">
                            <h5 class="card-title text-primary">${totalAlerts}</h5>
                            <p class="card-text">Total de Alertas</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-warning">
                        <div class="card-body">
                            <h5 class="card-title text-warning">${totalActive}</h5>
                            <p class="card-text">Alertas Activas</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-danger">
                        <div class="card-body">
                            <h5 class="card-title text-danger">${totalCritical}</h5>
                            <p class="card-text">Alertas Críticas</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-success">
                        <div class="card-body">
                            <h5 class="card-title text-success">${totalResolved}</h5>
                            <p class="card-text">Alertas Resueltas</p>
                        </div>
                    </div>
                </div>
            `;
            
            if (maxAlerts && maxAlerts.posicion_llanta) {
                container.innerHTML += `
                    <div class="col-12 mt-3">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Posición con más alertas:</strong> 
                            ${positions[maxAlerts.posicion_llanta] || maxAlerts.posicion_llanta} 
                            (${maxAlerts.total_alertas} alertas)
                        </div>
                    </div>
                `;
            }
        }

        // Crear tabla de estadísticas mejorada
        // Crear tabla de estadísticas mejorada
        function createStatisticsTable(statistics) {
            const container = document.getElementById('statistics-table');
            
            console.log('Estadísticas recibidas:', statistics.length, statistics);
            
            if (statistics.length === 0) {
                container.innerHTML = `
                    <div class="alert alert-warning text-center">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>No hay estadísticas disponibles.</strong>
                        <p class="mb-0">No se encontraron alertas para generar estadísticas.</p>
                    </div>
                `;
                return;
            }

            const positions = {
                'direccion_izquierda': 'Dirección Izquierda',
                'direccion_derecha': 'Dirección Derecha',
                'traccion1_izquierda': 'Tracción 1 - Izquierda',
                'traccion1_derecha': 'Tracción 1 - Derecha',
                'traccion1_izquierda2': 'Tracción 1 - Izquierda 2',
                'traccion1_derecha2': 'Tracción 1 - Derecha 2',
                'traccion2_izquierda': 'Tracción 2 - Izquierda',
                'traccion2_derecha': 'Tracción 2 - Derecha',
                'traccion2_izquierda2': 'Tracción 2 - Izquierda 2',
                'traccion2_derecha2': 'Tracción 2 - Derecha 2'
            };

            // Ordenar estadísticas por total de alertas (descendente)
            const sortedStatistics = [...statistics].sort((a, b) => b.total_alertas - a.total_alertas);

            const tableHTML = `
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-table"></i> Tabla Detallada de Estadísticas
                            </h6>
                            <button class="btn btn-outline-primary btn-sm" onclick="exportTableToCSV()">
                                <i class="fas fa-file-csv"></i> Exportar CSV
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="statistics-data-table">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th>Posición de Llanta</th>
                                        <th class="text-center">Total Alertas</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Críticas</th>
                                        <th class="text-center">% Críticas</th>
                                        <th class="text-center">% Altas</th>
                                        <th class="text-center">% Medias</th>
                                        <th class="text-center">% Bajas</th>
                                        <th class="text-center">Tendencia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${sortedStatistics.map((stat, index) => {
                                        const criticalPercentage = stat.total_alertas > 0 ? 
                                            ((stat.alertas_criticas / stat.total_alertas) * 100).toFixed(1) : 0;
                                        const highPercentage = stat.total_alertas > 0 ? 
                                            (((stat.alertas_altas || 0) / stat.total_alertas) * 100).toFixed(1) : 0;
                                        const mediumPercentage = stat.total_alertas > 0 ? 
                                            (((stat.alertas_medias || 0) / stat.total_alertas) * 100).toFixed(1) : 0;
                                        const lowPercentage = stat.total_alertas > 0 ? 
                                            (((stat.alertas_bajas || 0) / stat.total_alertas) * 100).toFixed(1) : 0;
                                        
                                        // Determinar el estado de las alertas activas
                                        const alertStatus = stat.alertas_activas > 0 ? 
                                            'Pendiente' : 
                                            (stat.total_alertas > 0 ? 'Completada' : 'Sin alertas');
                                        
                                        const alertStatusBadge = stat.alertas_activas > 0 ? 
                                            'bg-warning text-dark' : 
                                            (stat.total_alertas > 0 ? 'bg-success' : 'bg-secondary');
                                        
                                        // Determinar tendencia (simulada por prioridad)
                                        const criticalRatio = parseFloat(criticalPercentage);
                                        let trendIcon = '';
                                        let trendClass = '';
                                        
                                        if (criticalRatio > 50) {
                                            trendIcon = '<i class="fas fa-arrow-up"></i>';
                                            trendClass = 'text-danger';
                                        } else if (criticalRatio > 25) {
                                            trendIcon = '<i class="fas fa-minus"></i>';
                                            trendClass = 'text-warning';
                                        } else {
                                            trendIcon = '<i class="fas fa-arrow-down"></i>';
                                            trendClass = 'text-success';
                                        }
                                        
                                        // Determinar clase de fila según criticidad
                                        const rowClass = stat.alertas_criticas > 0 ? 'table-danger' : 
                                                        stat.alertas_activas > 0 ? 'table-warning' : '';
                                            
                                        return `
                                            <tr class="${rowClass}">
                                                <td class="text-center fw-bold">${index + 1}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="tire-indicator me-2" style="width: 12px; height: 12px; border-radius: 50%; background-color: ${stat.alertas_criticas > 0 ? '#dc3545' : stat.alertas_activas > 0 ? '#ffc107' : '#28a745'};"></div>
                                                        <strong>${positions[stat.posicion_llanta] || stat.posicion_llanta}</strong>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary fs-6">${stat.total_alertas}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge ${alertStatusBadge}">${alertStatus}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-danger fs-6">${stat.alertas_criticas}</span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-danger" role="progressbar" 
                                                             style="width: ${criticalPercentage}%" 
                                                             aria-valuenow="${criticalPercentage}" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                            ${criticalPercentage}%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-warning" role="progressbar" 
                                                             style="width: ${highPercentage}%" 
                                                             aria-valuenow="${highPercentage}" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                            ${highPercentage}%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-info" role="progressbar" 
                                                             style="width: ${mediumPercentage}%" 
                                                             aria-valuenow="${mediumPercentage}" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                            ${mediumPercentage}%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-success" role="progressbar" 
                                                             style="width: ${lowPercentage}%" 
                                                             aria-valuenow="${lowPercentage}" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                            ${lowPercentage}%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center ${trendClass}">
                                                    ${trendIcon}
                                                </td>
                                            </tr>
                                        `;
                                    }).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;

            container.innerHTML = tableHTML;
        }

        // Función para exportar tabla a CSV
        function exportTableToCSV() {
            const table = document.getElementById('statistics-data-table');
            if (!table) return;
            
            let csv = [];
            const rows = table.querySelectorAll('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = [];
                const cols = rows[i].querySelectorAll('td, th');
                
                for (let j = 0; j < cols.length; j++) {
                    let text = cols[j].innerText.replace(/"/g, '""');
                    row.push('"' + text + '"');
                }
                csv.push(row.join(','));
            }
            
            const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
            const downloadLink = document.createElement('a');
            downloadLink.download = `estadisticas_llantas_${new Date().toISOString().split('T')[0]}.csv`;
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = 'none';
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }

        // Controlar visibilidad de vistas
        function setupViewControls() {
            const chartView = document.getElementById('chart-view');
            const tableView = document.getElementById('table-view');
            const bothView = document.getElementById('both-view');
            const chartContainer = document.getElementById('chart-container');
            const statisticsTable = document.getElementById('statistics-table');
            
            function updateView() {
                if (chartView.checked) {
                    chartContainer.style.display = 'block';
                    statisticsTable.style.display = 'none';
                } else if (tableView.checked) {
                    chartContainer.style.display = 'none';
                    statisticsTable.style.display = 'block';
                } else if (bothView.checked) {
                    chartContainer.style.display = 'block';
                    statisticsTable.style.display = 'block';
                }
            }
            
            chartView.addEventListener('change', updateView);
            tableView.addEventListener('change', updateView);
            bothView.addEventListener('change', updateView);
            
            // Evento para cambio de tipo de gráfico
            document.getElementById('chart-type-selector').addEventListener('change', function() {
                if (window.currentStatistics) {
                    createStatisticsChart(window.currentStatistics);
                }
            });
            
            // Evento para cambio de tipo de datos
            document.getElementById('data-type-selector').addEventListener('change', function() {
                if (window.currentStatistics) {
                    createStatisticsChart(window.currentStatistics);
                }
            });
            
            // Evento para exportar gráfico
            document.getElementById('export-chart').addEventListener('click', function() {
                if (window.statisticsChart) {
                    const link = document.createElement('a');
                    link.download = `grafico_estadisticas_${new Date().toISOString().split('T')[0]}.png`;
                    link.href = window.statisticsChart.toBase64Image();
                    link.click();
                }
            });
        }

        // Cargar órdenes de trabajo
        async function loadWorkOrders() {
            try {
                const response = await fetch('../controllers/AlertController.php?action=getAll');
                const data = await response.json();
                
                if (data.success) {
                    const workOrdersWithAlerts = data.data.filter(alert => alert.orden_trabajo);
                    displayWorkOrders(workOrdersWithAlerts);
                    updateLastUpdateTime();
                }
            } catch (error) {
                console.error('Error loading work orders:', error);
            }
        }

        // Mostrar órdenes de trabajo
        function displayWorkOrders(workOrders) {
            const container = document.getElementById('work-orders-list');
            
            if (workOrders.length === 0) {
                container.innerHTML = '<p>No hay órdenes de trabajo generadas por alertas.</p>';
                return;
            }

            const workOrdersHTML = workOrders.map(order => `
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="card-title">${order.orden_trabajo || 'Orden sin título'}</h5>
                                <p class="card-text">${order.descripcion}</p>
                                <small class="text-muted">
                                    <i class="fas fa-calendar"></i> ${order.fecha_hora}
                                    ${order.tecnico_nombre ? `| <i class="fas fa-user"></i> ${order.tecnico_nombre}` : ''}
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-${order.prioridad === 'alta' || order.prioridad === 'critica' ? 'danger' : 
                                    order.prioridad === 'media' ? 'warning' : 'info'}">
                                    ${order.prioridad}
                                </span>
                                <br>
                                <span class="badge bg-secondary mt-1">${order.estado}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');

            container.innerHTML = workOrdersHTML;
        }

        // Actualizar estado de alerta
        async function updateAlertStatus(alertId, newStatus) {
            try {
                const formData = new FormData();
                formData.append('id', alertId);
                formData.append('estado', newStatus);
                
                const response = await fetch('../controllers/AlertController.php?action=updateStatus', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Mostrar feedback visual temporal
                    const alertElement = document.querySelector(`[onclick*="${alertId}"]`).closest('.card');
                    if (alertElement) {
                        alertElement.style.borderLeft = '4px solid #28a745';
                        setTimeout(() => {
                            alertElement.style.borderLeft = '';
                        }, 2000);
                    }
                    
                    // Actualizar la vista después de cambiar el estado
                    setTimeout(() => {
                        loadDashboard();
                        
                        // También actualizar las estadísticas si están visibles
                        const activeTab = document.querySelector('.nav-link.active').getAttribute('data-bs-target');
                        if (activeTab === '#nav-statistics') {
                            loadStatistics();
                        } else if (activeTab === '#nav-work-orders') {
                            loadWorkOrders();
                        }
                    }, 500);
                    
                    console.log('Estado de alerta actualizado correctamente');
                } else {
                    console.error('Error al actualizar estado:', data.message);
                    alert('Error al actualizar el estado de la alerta');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error de conexión al actualizar el estado');
            }
        }

        // Eliminar alerta
        async function deleteAlert(alertId) {
            // Confirmar eliminación con mensaje detallado
            const confirmMessage = `¿Estás seguro de que deseas eliminar esta alerta?

⚠️ Esta acción:
• Eliminará permanentemente la alerta
• Desvinculará las órdenes de trabajo relacionadas
• No se puede deshacer

¿Continuar con la eliminación?`;
            
            if (!confirm(confirmMessage)) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('id', alertId);
                
                const response = await fetch('../controllers/AlertController.php?action=delete', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Mostrar feedback visual temporal
                    const alertElement = document.querySelector(`[onclick*="deleteAlert(${alertId})"]`).closest('.card');
                    if (alertElement) {
                        alertElement.style.borderLeft = '4px solid #dc3545';
                        alertElement.style.opacity = '0.7';
                        
                        // Deshabilitar botones para evitar clics múltiples
                        const buttons = alertElement.querySelectorAll('button');
                        buttons.forEach(btn => btn.disabled = true);
                        
                        // Animación de eliminación
                        setTimeout(() => {
                            alertElement.classList.add('alert-deleting');
                            
                            setTimeout(() => {
                                alertElement.remove();
                            }, 300);
                        }, 1000);
                    }
                    
                    // Actualizar la vista después de eliminar
                    setTimeout(() => {
                        loadDashboard();
                        
                        // También actualizar las estadísticas si están visibles
                        const activeTab = document.querySelector('.nav-link.active').getAttribute('data-bs-target');
                        if (activeTab === '#nav-statistics') {
                            loadStatistics();
                        } else if (activeTab === '#nav-work-orders') {
                            loadWorkOrders();
                        }
                    }, 1500);
                    
                    console.log('Alerta eliminada correctamente');
                } else {
                    console.error('Error al eliminar alerta:', data.message);
                    alert('Error al eliminar la alerta: ' + (data.message || 'Error desconocido'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error de conexión al eliminar la alerta');
            }
        }
    </script>
</body>
</html>