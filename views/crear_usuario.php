<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php
require_once '../config/db.php';
$db = new Database();
$conn = $db->getConnection();
$stmt = $conn->prepare("SELECT * FROM users ORDER BY id DESC");
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container mt-5">
    <div class="d-flex justify-content-end mb-3">
        <a href="dashboard.php" class="btn btn-secondary">Volver al dashboard</a>
    </div>
    <h2>Gestión de Usuarios</h2>
    <!-- Filtro por nombre -->
    <form method="get" class="mb-3" action="">
        <div class="input-group">
            <input type="text" class="form-control" name="filtro_nombre" placeholder="Filtrar por nombre" value="<?php echo isset($_GET['filtro_nombre']) ? htmlspecialchars($_GET['filtro_nombre']) : ''; ?>">
            <button class="btn btn-outline-secondary" type="submit">Filtrar</button>
        </div>
    </form>
    <!-- Tabla de usuarios existentes -->
    <div class="table-responsive mb-5">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Documento</th>
                    <th>Tipo</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Celular</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $usuarios_filtrados = $usuarios;
            if (isset($_GET['filtro_nombre']) && $_GET['filtro_nombre'] !== '') {
                $filtro = strtolower($_GET['filtro_nombre']);
                $usuarios_filtrados = array_filter($usuarios, function($u) use ($filtro) {
                    return strpos(strtolower($u['nombre']), $filtro) !== false;
                });
            }
            foreach ($usuarios_filtrados as $usuario) {
                echo '<tr>';
                echo '<td>' . $usuario['id'] . '</td>';
                echo '<td>' . htmlspecialchars($usuario['num_documento']) . '</td>';
                echo '<td>' . htmlspecialchars($usuario['tipo_documento']) . '</td>';
                echo '<td>' . htmlspecialchars($usuario['nombre']) . '</td>';
                echo '<td>' . htmlspecialchars($usuario['apellido']) . '</td>';
                echo '<td>' . htmlspecialchars($usuario['num_celular']) . '</td>';
                echo '<td>' . htmlspecialchars($usuario['correo']) . '</td>';
                echo '<td>' . htmlspecialchars($usuario['rol']) . '</td>';
                echo '<td>';
                echo '<a href="?editar=' . $usuario['id'] . '" class="btn btn-sm btn-warning me-1">Editar</a>';
                echo '<a href="../controllers/UserController.php?action=delete&id=' . $usuario['id'] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'¿Seguro que deseas eliminar este usuario?\')">Eliminar</a>';
                echo '</td>';
                echo '</tr>';
            }
            ?>
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-end mb-3">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">Crear Usuario</button>
    </div>
    <?php
    // Mostrar solo uno de los formularios: edición o creación
    if (isset($_GET['editar'])) {
        $idEditar = intval($_GET['editar']);
        $usuarioEditar = null;
        foreach ($usuarios as $u) {
            if ($u['id'] == $idEditar) {
                $usuarioEditar = $u;
                break;
            }
        }
        if ($usuarioEditar) {
    ?>
        <h3>Editar Usuario</h3>
        <form id="form-editar-usuario" method="post" action="../controllers/UserController.php?action=update&id=<?php echo $usuarioEditar['id']; ?>">
            <div class="mb-3">
                <label for="nombre_edit" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre_edit" name="nombre" value="<?php echo htmlspecialchars($usuarioEditar['nombre']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="apellido_edit" class="form-label">Apellido</label>
                <input type="text" class="form-control" id="apellido_edit" name="apellido" value="<?php echo htmlspecialchars($usuarioEditar['apellido']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="num_celular_edit" class="form-label">Celular</label>
                <input type="text" class="form-control" id="num_celular_edit" name="num_celular" value="<?php echo htmlspecialchars($usuarioEditar['num_celular']); ?>">
            </div>
            <div class="mb-3">
                <label for="correo_edit" class="form-label">Correo</label>
                <input type="email" class="form-control" id="correo_edit" name="correo" value="<?php echo htmlspecialchars($usuarioEditar['correo']); ?>">
            </div>
            <div class="mb-3">
                <label for="rol_edit" class="form-label">Rol</label>
                <select class="form-select" id="rol_edit" name="rol" required>
                    <option value="conductor" <?php if($usuarioEditar['rol']==='conductor') echo 'selected'; ?>>Conductor</option>
                    <option value="tecnico" <?php if($usuarioEditar['rol']==='tecnico') echo 'selected'; ?>>Técnico</option>
                    <option value="admin" <?php if($usuarioEditar['rol']==='admin') echo 'selected'; ?>>Administrador</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="contrasena_edit" class="form-label">Nueva Contraseña (opcional)</label>
                <input type="password" class="form-control" id="contrasena_edit" name="contrasena">
            </div>
            <button type="submit" class="btn btn-success">Guardar Cambios</button>
            <a href="crear_usuario.php" class="btn btn-secondary">Cancelar</a>
        </form>
        <hr>
    <?php }
    }
    ?>

    <!-- Modal para crear usuario -->
    <div class="modal fade" id="modalCrearUsuario" tabindex="-1" aria-labelledby="modalCrearUsuarioLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalCrearUsuarioLabel">Crear Usuario</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <form id="form-crear-usuario" method="post" action="../controllers/UserController.php?action=create">
                <div class="mb-3">
                    <label for="num_documento_create" class="form-label">Número de Documento</label>
                    <input type="text" class="form-control" id="num_documento_create" name="num_documento" required>
                </div>
                <div class="mb-3">
                    <label for="tipo_documento_create" class="form-label">Tipo de Documento</label>
                    <select class="form-select" id="tipo_documento_create" name="tipo_documento" required>
                        <option value="CC">Cédula</option>
                        <option value="CE">Cédula de Extranjería</option>
                        <option value="TI">Tarjeta de Identidad</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="nombre_create" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="nombre_create" name="nombre" required>
                </div>
                <div class="mb-3">
                    <label for="apellido_create" class="form-label">Apellido</label>
                    <input type="text" class="form-control" id="apellido_create" name="apellido" required>
                </div>
                <div class="mb-3">
                    <label for="num_celular_create" class="form-label">Celular</label>
                    <input type="text" class="form-control" id="num_celular_create" name="num_celular">
                </div>
                <div class="mb-3">
                    <label for="correo_create" class="form-label">Correo</label>
                    <input type="email" class="form-control" id="correo_create" name="correo">
                </div>
                <div class="mb-3">
                    <label for="rol_create" class="form-label">Rol</label>
                    <select class="form-select" id="rol_create" name="rol" required>
                        <option value="conductor">Conductor</option>
                        <option value="tecnico">Técnico</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="contrasena_create" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="contrasena_create" name="contrasena" required>
                </div>
                <button type="submit" class="btn btn-primary">Crear Usuario</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</script>
<script src="../assets/js/usuario-rol.js?v=1"></script>
<script>
// Lógica para el formulario de edición (igual que crear)
document.addEventListener('DOMContentLoaded', function() {
    const rolSelect = document.getElementById('rol_edit');
    if (!rolSelect) return;
    rolSelect.addEventListener('change', function() {
        const selected = this.value;
        if (selected === 'conductor') {
            window.location.href = 'cond.php?fromUserEdit=1&id=<?php echo $usuarioEditar['id']; ?>';
        } else if (selected === 'tecnico') {
            showEspecialidadModal();
        } else if (selected === 'admin') {
            alert('El usuario administrador solo tendrá acceso a la gestión de usuarios.');
        }
    });
});
</script>
</body>
</html>