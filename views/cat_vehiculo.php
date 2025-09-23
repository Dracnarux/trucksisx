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
    <title>Categoría de Vehículos</title>
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
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-0">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php"><i class="bi bi-truck"></i> Trucksisx</a>
            <span class="navbar-text">Categoría de Vehículos</span>
        </div>
    </nav>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0"><i class="bi bi-truck"></i> Categoría de Vehículos</h2>
            <a href="cat_vehiculo.php?form=1" class="btn btn-success"><i class="bi bi-plus-circle"></i> Agregar Categoría</a>
        </div>
        <?php
        require_once '../config/db.php';
        $conn = conectarDB();
        $filtro = isset($_GET['filtro_nombre']) ? $_GET['filtro_nombre'] : '';
        $sql = "SELECT * FROM cat_vehic WHERE nombre LIKE ? ORDER BY id DESC";
        $stmt = $conn->prepare($sql);
        $like = "%$filtro%";
        $stmt->bind_param('s', $like);
        $stmt->execute();
        $categorias = $stmt->get_result();
        ?>
        <form class="row mb-4" method="get">
            <div class="col-md-4">
                <input type="text" name="filtro_nombre" class="form-control" placeholder="Buscar por nombre" value="<?= htmlspecialchars($filtro) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filtrar</button>
            </div>
            <div class="col-md-2">
                <a href="cat_vehiculo.php" class="btn btn-secondary w-100"><i class="bi bi-x-circle"></i> Limpiar</a>
            </div>
        </form>
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($categorias && $categorias->num_rows > 0): ?>
                        <?php while ($row = $categorias->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['nombre']) ?></td>
                            <td class="text-center">
                                <a href="cat_vehiculo.php?form=1&id=<?= $row['id'] ?>" class="btn btn-warning btn-sm mx-1"><i class="bi bi-pencil-square"></i> Editar</a>
                                <a href="cat_vehiculo.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm mx-1" onclick="return confirm('¿Eliminar categoría?')"><i class="bi bi-trash"></i> Eliminar</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted">No hay categorías registradas.</td>
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
                $sql = "SELECT * FROM cat_vehic WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('i', $_GET['id']);
                $stmt->execute();
                $editData = $stmt->get_result()->fetch_assoc();
            }
        ?>
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-pencil-square"></i> <?= isset($editData['id']) ? 'Editar' : 'Agregar' ?> Categoría</h5>
                <form method="post" action="cat_vehiculo.php">
                    <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($editData['nombre'] ?? '') ?>">
                    </div>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg"></i> Guardar</button>
                    <a href="cat_vehiculo.php" class="btn btn-secondary mx-2"><i class="bi bi-x-lg"></i> Cancelar</a>
                </form>
            </div>
        </div>
        <?php endif; ?>
        <?php
        // Guardar/editar categoría
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'])) {
            if (!empty($_POST['id'])) {
                $sql = "UPDATE cat_vehic SET nombre = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('si', $_POST['nombre'], $_POST['id']);
                $stmt->execute();
            } else {
                $sql = "INSERT INTO cat_vehic (nombre) VALUES (?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('s', $_POST['nombre']);
                $stmt->execute();
            }
            echo '<script>window.location="cat_vehiculo.php";</script>';
            exit;
        }
        // Eliminar categoría
        if (isset($_GET['delete'])) {
            $sql = "DELETE FROM cat_vehic WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $_GET['delete']);
            $stmt->execute();
            echo '<script>window.location="cat_vehiculo.php";</script>';
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
