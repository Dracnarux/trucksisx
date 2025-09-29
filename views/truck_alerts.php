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
        .header-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            margin-bottom: 20px;
        }
        
        .stats-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #333;
        }
        
        .stats-label {
            color: #666;
            margin-top: 10px;
        }
        
        .control-panel {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-container">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-truck"></i> Sistema de Alertas</h1>
                    <p class="mb-0">Monitoreo en tiempo real de alertas para camiones doble troque</p>
                </div>
                <div class="col-md-4 text-end d-flex flex-column align-items-end gap-2">
                    <a href="dashboard.php" class="btn btn-outline-primary mb-2">
                        <i class="fas fa-arrow-left"></i> Volver al Dashboard
                    </a>
                    <button class="btn btn-light" id="refresh-alerts">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Panel de Control -->
        <div class="control-panel">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <label for="vehicle-selector" class="form-label">
                        <i class="fas fa-truck"></i> Seleccionar Vehículo:
                    </label>
                    <select class="form-select" id="vehicle-selector">
                        <option value="">Todos los vehículos</option>
                        <?php
                        // Cargar vehículos desde la base de datos
                        require_once '../config/db.php';
                        try {
                            $database = new Database();
                            $db = $database->getConnection();
                            $query = "SELECT id, placa, marca_vehiculo FROM regis_vehic ORDER BY placa";
                            $stmt = $db->prepare($query);
                            $stmt->execute();
                            $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($vehicles as $vehicle) {
                                echo "<option value='{$vehicle['id']}'>{$vehicle['placa']} - {$vehicle['marca_vehiculo']}</option>";
                            }
                        } catch (Exception $e) {
                            echo "<option value='1'>Vehículo de Prueba - ABC123</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">
                        <i class="fas fa-info-circle"></i> Instrucciones:
                    </label>
                    <p class="text-muted mb-0">
                        Haga clic en cualquier llanta del diagrama para registrar una nueva alerta.
                        Las llantas se colorearán según la prioridad de las alertas activas.
                    </p>
                </div>
            </div>
        </div>

        <!-- Estadísticas Generales -->
        <div class="row" id="stats-container">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number text-primary" id="total-alerts">-</div>
                    <div class="stats-label">Total Alertas</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number text-success" id="active-alerts">-</div>
                    <div class="stats-label">Alertas Activas</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number text-warning" id="critical-alerts">-</div>
                    <div class="stats-label">Alertas Críticas</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number text-info" id="tire-alerts">-</div>
                    <div class="stats-label">Alertas de Llantas</div>
                </div>
            </div>
        </div>

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
                            <i class="fas fa-clipboard-list"></i> Órdenes de Trabajo
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
                            <h4>Estadísticas por Posición de Llanta</h4>
                            <div class="row">
                                <div class="col-12">
                                    <canvas id="tire-statistics-chart" width="400" height="200"></canvas>
                                </div>
                            </div>
                            <div id="statistics-table" class="mt-3">
                                <!-- Tabla de estadísticas se cargará dinámicamente -->
                            </div>
                        </div>
                    </div>

                    <!-- Tab de Órdenes de Trabajo -->
                    <div class="tab-pane fade" id="nav-work-orders" role="tabpanel" 
                         aria-labelledby="nav-work-orders-tab">
                        <div class="mt-3">
                            <h4>Órdenes de Trabajo Generadas por Alertas</h4>
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
        });

        // Cargar dashboard general
        async function loadDashboard() {
            try {
                const vehicleParam = document.getElementById('vehicle-selector').value ? 
                    `?vehicle_id=${document.getElementById('vehicle-selector').value}` : '';
                const response = await fetch(`../controllers/AlertController.php?action=getDashboard${vehicleParam}`);
                const data = await response.json();
                
                if (data.success) {
                    const dashboard = data.dashboard;
                    document.getElementById('total-alerts').textContent = dashboard.total_alerts;
                    document.getElementById('active-alerts').textContent = dashboard.active_alerts;
                    document.getElementById('critical-alerts').textContent = dashboard.critical_alerts;
                    document.getElementById('tire-alerts').textContent = dashboard.tire_alerts;
                }
            } catch (error) {
                console.error('Error loading dashboard:', error);
            }
        }

        // Cargar estadísticas por posición
        async function loadStatistics() {
            try {
                const vehicleParam = document.getElementById('vehicle-selector').value ? 
                    `?vehicle_id=${document.getElementById('vehicle-selector').value}` : '';
                const response = await fetch(`../controllers/AlertController.php?action=getTireAlerts${vehicleParam}`);
                const data = await response.json();
                
                if (data.success && data.statistics) {
                    createStatisticsChart(data.statistics);
                    createStatisticsTable(data.statistics);
                }
            } catch (error) {
                console.error('Error loading statistics:', error);
            }
        }

        // Crear gráfico de estadísticas
        function createStatisticsChart(statistics) {
            const ctx = document.getElementById('tire-statistics-chart').getContext('2d');
            
            const labels = statistics.map(stat => {
                const positions = {
                    'direccion_izquierda': 'DIR-I',
                    'direccion_derecha': 'DIR-D',
                    'traccion1_izquierda': 'T1-I',
                    'traccion1_derecha': 'T1-D',
                    'traccion1_izquierda2': 'T1-I2',
                    'traccion1_derecha2': 'T1-D2',
                    'traccion2_izquierda': 'T2-I',
                    'traccion2_derecha': 'T2-D'
                };
                return positions[stat.posicion_llanta] || stat.posicion_llanta;
            });
            
            const totalAlerts = statistics.map(stat => stat.total_alertas);
            const activeAlerts = statistics.map(stat => stat.alertas_activas);
            const criticalAlerts = statistics.map(stat => stat.alertas_criticas);

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Total de Alertas',
                            data: totalAlerts,
                            backgroundColor: 'rgba(54, 162, 235, 0.5)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Alertas Activas',
                            data: activeAlerts,
                            backgroundColor: 'rgba(255, 206, 86, 0.5)',
                            borderColor: 'rgba(255, 206, 86, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Alertas Críticas',
                            data: criticalAlerts,
                            backgroundColor: 'rgba(255, 99, 132, 0.5)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Estadísticas de Alertas por Posición de Llanta'
                        }
                    }
                }
            });
        }

        // Crear tabla de estadísticas
        function createStatisticsTable(statistics) {
            const container = document.getElementById('statistics-table');
            
            if (statistics.length === 0) {
                container.innerHTML = '<p>No hay estadísticas disponibles.</p>';
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
                'traccion2_derecha': 'Tracción 2 - Derecha'
            };

            const tableHTML = `
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Posición de Llanta</th>
                                <th>Total Alertas</th>
                                <th>Alertas Activas</th>
                                <th>Alertas Críticas</th>
                                <th>Porcentaje Críticas</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${statistics.map(stat => {
                                const percentage = stat.total_alertas > 0 ? 
                                    ((stat.alertas_criticas / stat.total_alertas) * 100).toFixed(1) : 0;
                                return `
                                    <tr>
                                        <td>${positions[stat.posicion_llanta] || stat.posicion_llanta}</td>
                                        <td><span class="badge bg-primary">${stat.total_alertas}</span></td>
                                        <td><span class="badge bg-warning">${stat.alertas_activas}</span></td>
                                        <td><span class="badge bg-danger">${stat.alertas_criticas}</span></td>
                                        <td>${percentage}%</td>
                                    </tr>
                                `;
                            }).join('')}
                        </tbody>
                    </table>
                </div>
            `;

            container.innerHTML = tableHTML;
        }

        // Cargar órdenes de trabajo
        async function loadWorkOrders() {
            try {
                const response = await fetch('../controllers/AlertController.php?action=getAll');
                const data = await response.json();
                
                if (data.success) {
                    const workOrdersWithAlerts = data.data.filter(alert => alert.orden_trabajo);
                    displayWorkOrders(workOrdersWithAlerts);
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
    </script>
</body>
</html>