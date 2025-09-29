<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Técnico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Crear Técnico</h2>
    <form method="post" action="../controllers/UserController.php?action=create_tecnico">
        <div class="mb-3">
            <label for="especialidad" class="form-label">Especialidad</label>
            <input type="text" class="form-control" id="especialidad" name="especialidad" required>
        </div>
        <div class="mb-3">
            <label for="nivel_experiencia" class="form-label">Nivel de Experiencia</label>
            <select class="form-select" id="nivel_experiencia" name="nivel_experiencia" required>
                <option value="Junior">Junior</option>
                <option value="Semi-Senior">Semi-Senior</option>
                <option value="Senior">Senior</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="categoria" class="form-label">Categoría que pertenece el nivel de experiencia</label>
            <input type="text" class="form-control" id="categoria" name="categoria" required>
        </div>
        <button type="submit" class="btn btn-primary">Crear Técnico</button>
    </form>
</div>
</body>
</html>