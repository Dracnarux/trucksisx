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
    <title>Gestión de Conductores</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #f8fafc 0%, #e3e6ed 100%);
        }
        .card {
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            border-radius: 1rem;
        }
        .card-title {
            font-weight: 600;
        }
        .navbar-brand {
            font-weight: bold;
            letter-spacing: 1px;
        }
        .table-responsive {
            max-height: 500px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-0">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php"><i class="bi bi-truck"></i> Trucksisx</a>
            <span class="navbar-text">Gestión de Conductores</span>
        </div>
    </nav>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0"><i class="bi bi-person-badge"></i> Conductores</h2>
            <a href="cond.php?form=1" class="btn btn-success"><i class="bi bi-plus-circle"></i> Agregar Conductor</a>
        </div>
        <?php
        require_once '../config/db.php';
        $conn = conectarDB();
        // Obtener vehículos para filtro y formulario
        $vehicQuery = $conn->query("SELECT id, placa, marca_vehiculo FROM regis_vehic ORDER BY placa ASC");
        $vehiculos = [];
        while ($vehic = $vehicQuery->fetch_assoc()) {
            $vehiculos[$vehic['id']] = $vehic['placa'] . ' (' . $vehic['marca_vehiculo'] . ')';
        }
        // Filtros
        $filtro_cargo = isset($_GET['filtro_cargo']) ? $_GET['filtro_cargo'] : '';
        $filtro_vehic = isset($_GET['filtro_vehic']) ? $_GET['filtro_vehic'] : '';
        $sql = "SELECT c.*, v.placa, v.marca_vehiculo FROM cond c JOIN regis_vehic v ON c.regis_vehic_id = v.id WHERE c.cargo LIKE ?";
        $params = ["%$filtro_cargo%"];
        $types = 's';
        if ($filtro_vehic) {
            $sql .= " AND v.id = ?";
            $params[] = $filtro_vehic;
            $types .= 'i';
        }
        $sql .= " ORDER BY c.id DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $conductores = $stmt->get_result();
        ?>
        <form class="row mb-4" method="get">
            <div class="col-md-4">
                <input type="text" name="filtro_cargo" class="form-control" placeholder="Buscar por cargo" value="<?= htmlspecialchars($filtro_cargo) ?>">
            </div>
            <div class="col-md-4">
                <select name="filtro_vehic" class="form-select">
                    <option value="">Todos los vehículos</option>
                    <?php foreach ($vehiculos as $id => $desc): ?>
                        <option value="<?= $id ?>" <?= $filtro_vehic == $id ? 'selected' : '' ?>><?= htmlspecialchars($desc) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filtrar</button>
            </div>
            <div class="col-md-2">
                <a href="cond.php" class="btn btn-secondary w-100"><i class="bi bi-x-circle"></i> Limpiar</a>
            </div>
        </form>
        <div class="card">
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Cargo</th>
                            <th>Horas Trabajadas</th>
                            <th>Tareas Completadas</th>
                            <th>Eficiencia</th>
                            <th>Descripción</th>
                            <th>Vehículo</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($conductores && $conductores->num_rows > 0): ?>
                        <?php while ($row = $conductores->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['cargo']) ?></td>
                            <td><?= htmlspecialchars($row['horas_trabajadas']) ?></td>
                            <td><?= htmlspecialchars($row['tareas_completadas']) ?></td>
                            <td><?= htmlspecialchars($row['efeciencia']) ?></td>
                            <td><?= htmlspecialchars($row['descripcion']) ?></td>
                            <td><?= htmlspecialchars($row['placa']) ?> (<?= htmlspecialchars($row['marca_vehiculo']) ?>)</td>
                            <td class="text-center">
                                <a href="cond.php?form=1&id=<?= $row['id'] ?>" class="btn btn-warning btn-sm mx-1"><i class="bi bi-pencil-square"></i> Editar</a>
                                <a href="cond.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm mx-1" onclick="return confirm('¿Eliminar conductor?')"><i class="bi bi-trash"></i> Eliminar</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">No hay conductores registrados.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
        // Formulario alta/edición
        if (isset($_GET['form'])):
            $editData = [];
            if (isset($_GET['id'])) {
                $sql = "SELECT * FROM cond WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('i', $_GET['id']);
                $stmt->execute();
                $editData = $stmt->get_result()->fetch_assoc();
            }
        ?>
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-pencil-square"></i> <?= isset($editData['id']) ? 'Editar' : 'Agregar' ?> Conductor</h5>
                <form method="post" action="cond.php">
                    <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Cargo</label>
                            <input type="text" name="cargo" class="form-control" required value="<?= htmlspecialchars($editData['cargo'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Horas Trabajadas</label>
                            <input type="number" name="horas_trabajadas" class="form-control" required value="<?= htmlspecialchars($editData['horas_trabajadas'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tareas Completadas</label>
                            <input type="number" name="tareas_completadas" class="form-control" required value="<?= htmlspecialchars($editData['tareas_completadas'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Eficiencia</label>
                            <input type="number" step="0.01" name="efeciencia" class="form-control" required value="<?= htmlspecialchars($editData['efeciencia'] ?? '') ?>">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="2"><?= htmlspecialchars($editData['descripcion'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Vehículo Registrado</label>
                            <select name="regis_vehic_id" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($vehiculos as $id => $desc): ?>
                                    <option value="<?= $id ?>" <?= (isset($editData['regis_vehic_id']) && $editData['regis_vehic_id'] == $id) ? 'selected' : '' ?>><?= htmlspecialchars($desc) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success mt-3"><i class="bi bi-check-lg"></i> Guardar</button>
                    <a href="cond.php" class="btn btn-secondary mx-2 mt-3"><i class="bi bi-x-lg"></i> Cancelar</a>
                </form>
            </div>
        </div>
        <?php endif; ?>
        <?php
        // Guardar/editar conductor
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cargo'], $_POST['horas_trabajadas'], $_POST['tareas_completadas'], $_POST['efeciencia'], $_POST['regis_vehic_id'])) {
            $fields = [
                'cargo','horas_trabajadas','tareas_completadas','efeciencia','descripcion','regis_vehic_id'
            ];
            $values = [];
            foreach ($fields as $f) {
                $values[] = $_POST[$f] ?? '';
            }
            if (!empty($_POST['id'])) {
                $sql = "UPDATE cond SET cargo=?, horas_trabajadas=?, tareas_completadas=?, efeciencia=?, descripcion=?, regis_vehic_id=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $values_update = $values;
                $values_update[] = $_POST['id'];
                $stmt->bind_param('siidsii', ...$values_update);
                $stmt->execute();
            } else {
                $sql = "INSERT INTO cond (cargo, horas_trabajadas, tareas_completadas, efeciencia, descripcion, regis_vehic_id) VALUES (?,?,?,?,?,?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('siidsi', ...$values);
                $stmt->execute();
            }
            echo '<script>window.location="cond.php";</script>';
            exit;
        }
        // Eliminar conductor
        if (isset($_GET['delete'])) {
            $sql = "DELETE FROM cond WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $_GET['delete']);
            $stmt->execute();
            echo '<script>window.location="cond.php";</script>';
            exit;
        }
        ?>
        <div class="mt-5 text-end">
            <a href="dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver al Dashboard</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
