<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Conductor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Crear Conductor</h2>
    <form method="post" action="../controllers/CondController.php?action=create">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required>
        </div>
        <div class="mb-3">
            <label for="cargo" class="form-label">Cargo</label>
            <input type="text" class="form-control" id="cargo" name="cargo" value="Conductor" readonly>
        </div>
        <div class="mb-3">
            <label for="regis_vehic_id" class="form-label">Vehículo Asignado</label>
            <select class="form-select" id="regis_vehic_id" name="regis_vehic_id" required>
                <option value="">Seleccione un vehículo</option>
                <?php
                require_once '../config/db.php';
                $db = new Database();
                $conn = $db->getConnection();
                $stmt = $conn->prepare("SELECT id, placa, marca_vehiculo FROM regis_vehic ORDER BY placa");
                $stmt->execute();
                $vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($vehiculos as $vehiculo) {
                    echo '<option value="' . $vehiculo['id'] . '">' . htmlspecialchars($vehiculo['placa']) . ' - ' . htmlspecialchars($vehiculo['marca_vehiculo']) . '</option>';
                }
                ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Crear Conductor</button>
    </form>
</div>
</body>
</html>