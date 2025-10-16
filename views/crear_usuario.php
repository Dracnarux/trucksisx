<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}

$rol_conductor = isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'conductor';
$rol_tecnico = isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'tecnico';
$rol_admin = isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'admin';

// Solo admins pueden acceder a esta página
if (!$rol_admin) {
    header('Location: dashboard.php');
    exit();
}

require_once '../config/db.php';
$db = new Database();
$conn = $db->getConnection();
$stmt = $conn->prepare("SELECT * FROM users ORDER BY id DESC");
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - TruckSISX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #f8fafc 0%, #e3e6ed 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .main-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
        .header-section {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            color: white;
            padding: 2rem;
            border-radius: 20px 20px 0 0;
        }
        .content-section {
            padding: 2rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            border: none;
            border-radius: 10px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.4);
        }
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        .modal-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            color: white;
            border-radius: 20px 20px 0 0;
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        .btn-outline-secondary {
            border-radius: 10px;
            font-weight: 600;
        }
        .alert {
            border-radius: 15px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-container">
            <!-- Header Section -->
            <div class="header-section">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1"><i class="bi bi-people-fill"></i> Gestión de Usuarios</h2>
                        <p class="mb-0 opacity-75">Administra usuarios del sistema TruckSISX</p>
                    </div>
                    <a href="dashboard.php" class="btn btn-light">
                        <i class="bi bi-arrow-left"></i> Volver al Dashboard
                    </a>
                </div>
            </div>
            
            <!-- Content Section -->
            <div class="content-section">
                <!-- Filtro y Búsqueda -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <form method="get" action="">
                            <div class="input-group">
                                <input type="text" class="form-control" name="filtro_nombre" 
                                       placeholder="Buscar por nombre, documento o correo..." 
                                       value="<?php echo isset($_GET['filtro_nombre']) ? htmlspecialchars($_GET['filtro_nombre']) : ''; ?>">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                                <?php if (isset($_GET['filtro_nombre']) && $_GET['filtro_nombre'] !== ''): ?>
                                <a href="crear_usuario.php" class="btn btn-outline-danger">
                                    <i class="bi bi-x"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4 text-end">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
                            <i class="bi bi-person-plus"></i> Crear Usuario
                        </button>
                    </div>
                </div>
                <!-- Tabla de Usuarios -->
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th><i class="bi bi-hash"></i> ID</th>
                                    <th><i class="bi bi-card-text"></i> Documento</th>
                                    <th><i class="bi bi-person-circle"></i> Nombre Completo</th>
                                    <th><i class="bi bi-telephone"></i> Celular</th>
                                    <th><i class="bi bi-envelope"></i> Correo</th>
                                    <th><i class="bi bi-person-badge"></i> Rol</th>
                                    <th><i class="bi bi-gear"></i> Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $usuarios_filtrados = $usuarios;
                            if (isset($_GET['filtro_nombre']) && $_GET['filtro_nombre'] !== '') {
                                $filtro = strtolower($_GET['filtro_nombre']);
                                $usuarios_filtrados = array_filter($usuarios, function($u) use ($filtro) {
                                    return strpos(strtolower($u['nombre']), $filtro) !== false || 
                                           strpos(strtolower($u['apellido']), $filtro) !== false ||
                                           strpos(strtolower($u['num_documento']), $filtro) !== false ||
                                           strpos(strtolower($u['correo']), $filtro) !== false;
                                });
                            }
                            
                            if (empty($usuarios_filtrados)):
                            ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="bi bi-inbox" style="font-size: 2rem; opacity: 0.5;"></i>
                                        <p class="mt-2 text-muted">No se encontraron usuarios</p>
                                    </td>
                                </tr>
                            <?php 
                            else:
                            foreach ($usuarios_filtrados as $usuario): 
                                $rolBadgeClass = [
                                    'admin' => 'bg-danger',
                                    'tecnico' => 'bg-warning text-dark',
                                    'conductor' => 'bg-success'
                                ];
                            ?>
                                <tr>
                                    <td><strong>#<?= $usuario['id'] ?></strong></td>
                                    <td>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($usuario['tipo_documento']) ?></span>
                                        <?= htmlspecialchars($usuario['num_documento']) ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($usuario['nombre']) ?> <?= htmlspecialchars($usuario['apellido']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($usuario['num_celular']) ?: '<small class="text-muted">No registrado</small>' ?></td>
                                    <td><?= htmlspecialchars($usuario['correo']) ?: '<small class="text-muted">No registrado</small>' ?></td>
                                    <td>
                                        <span class="badge <?= $rolBadgeClass[$usuario['rol']] ?? 'bg-secondary' ?>">
                                            <?= ucfirst($usuario['rol']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" 
                                                    class="btn btn-outline-info" 
                                                    onclick="verUsuario(<?= htmlspecialchars(json_encode($usuario)) ?>)"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalVerUsuario"
                                                    title="Ver detalles">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-outline-warning" 
                                                    onclick="editarUsuario(<?= htmlspecialchars(json_encode($usuario)) ?>)"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalEditarUsuario"
                                                    title="Editar usuario">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-outline-danger" 
                                                    onclick="eliminarUsuario(<?= $usuario['id'] ?>, '<?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?>')"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalEliminarUsuario"
                                                    title="Eliminar usuario">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php 
                            endforeach; 
                            endif;
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Los formularios de edición ahora están en modales -->

            </div>
        </div>
    </div>

    <!-- Modal para crear usuario -->
    <div class="modal fade" id="modalCrearUsuario" tabindex="-1" aria-labelledby="modalCrearUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCrearUsuarioLabel">
                        <i class="bi bi-person-plus"></i> Crear Nuevo Usuario
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="../controllers/UserController.php?action=create" id="formCrearUsuario">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="num_documento_create" class="form-label">
                                    <i class="bi bi-card-text"></i> Número de Documento
                                </label>
                                <input type="text" class="form-control" id="num_documento_create" name="num_documento" 
                                       placeholder="Ej: 12345678" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tipo_documento_create" class="form-label">
                                    <i class="bi bi-file-text"></i> Tipo de Documento
                                </label>
                                <select class="form-select" id="tipo_documento_create" name="tipo_documento" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="CC">Cédula de Ciudadanía</option>
                                    <option value="CE">Cédula de Extranjería</option>
                                    <option value="TI">Tarjeta de Identidad</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre_create" class="form-label">
                                    <i class="bi bi-person"></i> Nombre
                                </label>
                                <input type="text" class="form-control" id="nombre_create" name="nombre" 
                                       placeholder="Primer nombre" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="apellido_create" class="form-label">
                                    <i class="bi bi-person-fill"></i> Apellido
                                </label>
                                <input type="text" class="form-control" id="apellido_create" name="apellido" 
                                       placeholder="Primer apellido" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="num_celular_create" class="form-label">
                                    <i class="bi bi-telephone"></i> Número de Celular
                                </label>
                                <input type="text" class="form-control" id="num_celular_create" name="num_celular" 
                                       placeholder="3001234567">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="correo_create" class="form-label">
                                    <i class="bi bi-envelope"></i> Correo Electrónico
                                </label>
                                <input type="email" class="form-control" id="correo_create" name="correo" 
                                       placeholder="usuario@ejemplo.com">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="rol_create" class="form-label">
                                    <i class="bi bi-person-badge"></i> Rol del Usuario
                                </label>
                                <select class="form-select" id="rol_create" name="rol" required>
                                    <option value="">Seleccionar rol...</option>
                                    <option value="conductor">Conductor</option>
                                    <option value="tecnico">Técnico</option>
                                    <option value="admin">Administrador</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="contrasena_create" class="form-label">
                                    <i class="bi bi-lock"></i> Contraseña
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="contrasena_create" name="contrasena" 
                                           placeholder="Mínimo 6 caracteres" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('contrasena_create')">
                                        <i class="bi bi-eye" id="eye_contrasena_create"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Información:</strong> El nuevo usuario podrá cambiar su contraseña después del primer inicio de sesión.
                        </div>
                        
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <button type="submit" form="formCrearUsuario" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Crear Usuario
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para ver detalles del usuario -->
    <div class="modal fade" id="modalVerUsuario" tabindex="-1" aria-labelledby="modalVerUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="modalVerUsuarioLabel">
                        <i class="bi bi-eye"></i> Detalles del Usuario
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <i class="bi bi-person-circle"></i> Información Personal
                                </div>
                                <div class="card-body">
                                    <p><strong>ID:</strong> <span id="ver_id"></span></p>
                                    <p><strong>Nombre Completo:</strong> <span id="ver_nombre_completo"></span></p>
                                    <p><strong>Documento:</strong> <span id="ver_documento"></span></p>
                                    <p><strong>Tipo de Documento:</strong> <span id="ver_tipo_documento"></span></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <i class="bi bi-telephone"></i> Información de Contacto
                                </div>
                                <div class="card-body">
                                    <p><strong>Celular:</strong> <span id="ver_celular"></span></p>
                                    <p><strong>Correo:</strong> <span id="ver_correo"></span></p>
                                    <p><strong>Rol:</strong> <span id="ver_rol"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para editar usuario -->
    <div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-labelledby="modalEditarUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="modalEditarUsuarioLabel">
                        <i class="bi bi-pencil-square"></i> Editar Usuario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="../controllers/UserController.php?action=update" id="formEditarUsuario">
                        <input type="hidden" name="id" id="editar_id">
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Nota:</strong> El documento y tipo de documento no se pueden modificar por seguridad.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editar_documento_readonly" class="form-label">
                                    <i class="bi bi-card-text"></i> Documento
                                </label>
                                <input type="text" class="form-control" id="editar_documento_readonly" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editar_tipo_documento_readonly" class="form-label">
                                    <i class="bi bi-file-text"></i> Tipo de Documento
                                </label>
                                <input type="text" class="form-control" id="editar_tipo_documento_readonly" readonly>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editar_nombre" class="form-label">
                                    <i class="bi bi-person"></i> Nombre *
                                </label>
                                <input type="text" class="form-control" id="editar_nombre" name="nombre" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editar_apellido" class="form-label">
                                    <i class="bi bi-person-fill"></i> Apellido *
                                </label>
                                <input type="text" class="form-control" id="editar_apellido" name="apellido" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editar_celular" class="form-label">
                                    <i class="bi bi-telephone"></i> Número de Celular
                                </label>
                                <input type="text" class="form-control" id="editar_celular" name="num_celular">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editar_correo" class="form-label">
                                    <i class="bi bi-envelope"></i> Correo Electrónico
                                </label>
                                <input type="email" class="form-control" id="editar_correo" name="correo">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editar_rol" class="form-label">
                                    <i class="bi bi-person-badge"></i> Rol del Usuario *
                                </label>
                                <select class="form-select" id="editar_rol" name="rol" required>
                                    <option value="conductor">Conductor</option>
                                    <option value="tecnico">Técnico</option>
                                    <option value="admin">Administrador</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editar_contrasena" class="form-label">
                                    <i class="bi bi-lock"></i> Nueva Contraseña (opcional)
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="editar_contrasena" name="contrasena" 
                                           placeholder="Dejar vacío para mantener actual">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('editar_contrasena')">
                                        <i class="bi bi-eye" id="eye_editar_contrasena"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <button type="submit" form="formEditarUsuario" class="btn btn-warning">
                        <i class="bi bi-check-circle"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para confirmar eliminación -->
    <div class="modal fade" id="modalEliminarUsuario" tabindex="-1" aria-labelledby="modalEliminarUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalEliminarUsuarioLabel">
                        <i class="bi bi-exclamation-triangle"></i> Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>¡Advertencia!</strong> Esta acción no se puede deshacer.
                    </div>
                    
                    <p class="mb-3">¿Está seguro de que desea eliminar al usuario:</p>
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title mb-1" id="eliminar_nombre_usuario"></h6>
                            <small class="text-muted">ID: <span id="eliminar_id_usuario"></span></small>
                        </div>
                    </div>
                    
                    <p class="mt-3 text-muted">
                        <i class="bi bi-info-circle"></i>
                        Se eliminarán todos los datos asociados a este usuario.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <a href="#" id="btn_confirmar_eliminar" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Sí, Eliminar Usuario
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para mostrar/ocultar contraseña
        function togglePasswordVisibility(inputId) {
            const input = document.getElementById(inputId);
            const eye = document.getElementById('eye_' + inputId);
            
            if (input.type === 'password') {
                input.type = 'text';
                eye.classList.remove('bi-eye');
                eye.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                eye.classList.remove('bi-eye-slash');
                eye.classList.add('bi-eye');
            }
        }
        
        // Función para ver detalles del usuario
        function verUsuario(usuario) {
            document.getElementById('ver_id').textContent = '#' + usuario.id;
            document.getElementById('ver_nombre_completo').textContent = usuario.nombre + ' ' + usuario.apellido;
            document.getElementById('ver_documento').textContent = usuario.num_documento;
            document.getElementById('ver_tipo_documento').textContent = usuario.tipo_documento;
            document.getElementById('ver_celular').textContent = usuario.num_celular || 'No registrado';
            document.getElementById('ver_correo').textContent = usuario.correo || 'No registrado';
            
            // Crear badge para el rol
            const rolBadges = {
                'admin': '<span class="badge bg-danger">Administrador</span>',
                'tecnico': '<span class="badge bg-warning text-dark">Técnico</span>',
                'conductor': '<span class="badge bg-success">Conductor</span>'
            };
            document.getElementById('ver_rol').innerHTML = rolBadges[usuario.rol] || '<span class="badge bg-secondary">' + usuario.rol + '</span>';
        }
        
        // Función para cargar datos en el modal de edición
        function editarUsuario(usuario) {
            document.getElementById('editar_id').value = usuario.id;
            document.getElementById('editar_documento_readonly').value = usuario.num_documento;
            document.getElementById('editar_tipo_documento_readonly').value = usuario.tipo_documento;
            document.getElementById('editar_nombre').value = usuario.nombre;
            document.getElementById('editar_apellido').value = usuario.apellido;
            document.getElementById('editar_celular').value = usuario.num_celular || '';
            document.getElementById('editar_correo').value = usuario.correo || '';
            document.getElementById('editar_rol').value = usuario.rol;
            document.getElementById('editar_contrasena').value = '';
        }
        
        // Función para configurar modal de eliminación
        function eliminarUsuario(id, nombreCompleto) {
            document.getElementById('eliminar_id_usuario').textContent = id;
            document.getElementById('eliminar_nombre_usuario').textContent = nombreCompleto;
            document.getElementById('btn_confirmar_eliminar').href = '../controllers/UserController.php?action=delete&id=' + id;
        }
        
        // Validaciones de formularios
        document.getElementById('formCrearUsuario').addEventListener('submit', function(e) {
            const password = document.getElementById('contrasena_create').value;
            if (password.length < 6) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 6 caracteres');
                return false;
            }
        });
        
        document.getElementById('formEditarUsuario').addEventListener('submit', function(e) {
            const password = document.getElementById('editar_contrasena').value;
            if (password && password.length < 6) {
                e.preventDefault();
                alert('La nueva contraseña debe tener al menos 6 caracteres');
                return false;
            }
        });
        
        // Limpiar formularios al cerrar modales
        document.getElementById('modalCrearUsuario').addEventListener('hidden.bs.modal', function () {
            document.getElementById('formCrearUsuario').reset();
        });
        
        document.getElementById('modalEditarUsuario').addEventListener('hidden.bs.modal', function () {
            document.getElementById('formEditarUsuario').reset();
        });
        
        // Mostrar mensajes de éxito/error si existen en la URL
        window.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const mensaje = urlParams.get('mensaje');
            const tipo = urlParams.get('tipo');
            
            if (mensaje && tipo) {
                const alertClass = tipo === 'exito' ? 'alert-success' : 'alert-danger';
                const iconClass = tipo === 'exito' ? 'bi-check-circle' : 'bi-exclamation-triangle';
                
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert ${alertClass} alert-dismissible fade show`;
                alertDiv.innerHTML = `
                    <i class="bi ${iconClass}"></i>
                    <strong>${tipo === 'exito' ? 'Éxito:' : 'Error:'}</strong> ${decodeURIComponent(mensaje)}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                
                const container = document.querySelector('.content-section');
                container.insertBefore(alertDiv, container.firstChild);
                
                // Remover parámetros de la URL
                const newUrl = window.location.pathname;
                window.history.replaceState({}, document.title, newUrl);
            }
        });
    </script>
</body>
</html>