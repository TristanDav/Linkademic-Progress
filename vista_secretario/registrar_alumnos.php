<?php
require_once '../auth_secretario.php';

// Obtener grupos
$grupos = [];
$res = $conexion->query("SELECT id, nombre FROM grupos ORDER BY nombre");
while ($row = $res->fetch_assoc()) {
    $grupos[] = $row;
}

// Obtener padres (usuarios con rol_id=1)
$padres = [];
$res = $conexion->query("SELECT id, nombre, apellido FROM usuarios WHERE rol_id = 1 ORDER BY nombre, apellido");
while ($row = $res->fetch_assoc()) {
    $padres[] = $row;
}

$errores = [];
$exito = false;
$editando = false;
$alumno_edit = null;

// --- Eliminar alumno ---
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conexion->query("DELETE FROM alumnos WHERE id = $id");
    header('Location: registrar_alumnos.php');
    exit;
}

// --- Editar alumno (mostrar datos en formulario) ---
if (isset($_GET['editar'])) {
    $editando = true;
    $id = intval($_GET['editar']);
    $res = $conexion->query("SELECT * FROM alumnos WHERE id = $id");
    if ($res && $res->num_rows > 0) {
        $alumno_edit = $res->fetch_assoc();
    }
}

// --- Guardar alumno (nuevo o editado) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? '');
    $grupo_id = intval($_POST['grupo_id'] ?? 0);
    $padre_id = intval($_POST['padre_id'] ?? 0);
    $id_edit = intval($_POST['id_edit'] ?? 0);

    // Validaciones
    if (!$nombre) $errores[] = 'El nombre es obligatorio.';
    if (!$apellido) $errores[] = 'El apellido es obligatorio.';
    if (!$fecha_nacimiento) $errores[] = 'La fecha de nacimiento es obligatoria.';
    if ($grupo_id <= 0) $errores[] = 'Selecciona un grupo.';
    if ($padre_id <= 0) $errores[] = 'Selecciona un padre.';

    if (empty($errores)) {
        if ($id_edit > 0) {
            // Actualizar alumno
            $stmt = $conexion->prepare("UPDATE alumnos SET nombre=?, apellido=?, fecha_nacimiento=?, grupo_id=?, padre_id=? WHERE id=?");
            $stmt->bind_param('sssiii', $nombre, $apellido, $fecha_nacimiento, $grupo_id, $padre_id, $id_edit);
            $exito = $stmt->execute();
            $stmt->close();
            if ($exito) {
                header('Location: registrar_alumnos.php');
                exit;
            } else {
                $errores[] = 'Error al actualizar el alumno.';
            }
        } else {
            // Insertar alumno
            $stmt = $conexion->prepare("INSERT INTO alumnos (nombre, apellido, fecha_nacimiento, grupo_id, padre_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('sssii', $nombre, $apellido, $fecha_nacimiento, $grupo_id, $padre_id);
            $exito = $stmt->execute();
            $stmt->close();
        }
    }
}

// --- Listar alumnos ---
$alumnos = [];
$sql = "SELECT a.*, g.nombre AS grupo, u.nombre AS padre_nombre, u.apellido AS padre_apellido FROM alumnos a
        LEFT JOIN grupos g ON a.grupo_id = g.id
        LEFT JOIN usuarios u ON a.padre_id = u.id
        ORDER BY a.apellido, a.nombre";
$res = $conexion->query($sql);
while ($row = $res->fetch_assoc()) {
    $alumnos[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Alumnos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/secretario_style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/registrar_alumnos_style.css">
</head>
<body>
<div class="container py-5">
    <div class="card mb-4 welcome-card">
        <h2 class="mb-0"><i class="bi bi-people"></i> Gestión de Alumnos</h2>
        <p class="mb-0">Registra, edita y elimina alumnos del sistema.</p>
    </div>
    <div class="card mb-4">
        <h4 class="mb-3 text-primary"><?= $editando ? 'Editar Alumno' : 'Registrar Nuevo Alumno' ?></h4>
        <?php if ($exito && !$editando): ?>
            <div class="alert alert-success">¡Alumno registrado correctamente!</div>
        <?php elseif (!empty($errores)): ?>
            <div class="alert alert-danger"><ul><?php foreach ($errores as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>
        <form method="post" class="row g-3">
            <input type="hidden" name="id_edit" value="<?= $alumno_edit['id'] ?? '' ?>">
            <div class="col-md-6">
                <label for="nombre" class="form-label">Nombre(s)</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required value="<?= htmlspecialchars($alumno_edit['nombre'] ?? ($_POST['nombre'] ?? '')) ?>">
            </div>
            <div class="col-md-6">
                <label for="apellido" class="form-label">Apellido(s)</label>
                <input type="text" class="form-control" id="apellido" name="apellido" required value="<?= htmlspecialchars($alumno_edit['apellido'] ?? ($_POST['apellido'] ?? '')) ?>">
            </div>
            <div class="col-md-6">
                <label for="fecha_nacimiento" class="form-label">Fecha de nacimiento</label>
                <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required value="<?= htmlspecialchars($alumno_edit['fecha_nacimiento'] ?? ($_POST['fecha_nacimiento'] ?? '')) ?>">
            </div>
            <div class="col-md-6">
                <label for="grupo_id" class="form-label">Grupo</label>
                <select class="form-select" id="grupo_id" name="grupo_id" required>
                    <option value="">-- Selecciona grupo --</option>
                    <?php foreach ($grupos as $g): ?>
                        <option value="<?= $g['id'] ?>" <?= ((isset($alumno_edit['grupo_id']) && $alumno_edit['grupo_id'] == $g['id']) || (isset($_POST['grupo_id']) && $_POST['grupo_id'] == $g['id'])) ? 'selected' : '' ?>><?= htmlspecialchars($g['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="padre_id" class="form-label">Padre</label>
                <select class="form-select" id="padre_id" name="padre_id" required>
                    <option value="">-- Selecciona padre --</option>
                    <?php foreach ($padres as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ((isset($alumno_edit['padre_id']) && $alumno_edit['padre_id'] == $p['id']) || (isset($_POST['padre_id']) && $_POST['padre_id'] == $p['id'])) ? 'selected' : '' ?>><?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-person-plus"></i> <?= $editando ? 'Actualizar' : 'Registrar' ?></button>
                <?php if ($editando): ?>
                    <a href="registrar_alumnos.php" class="btn btn-secondary ms-2">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    <div class="card">
        <h4 class="mb-3 text-primary"><i class="bi bi-list"></i> Lista de Alumnos</h4>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Fecha Nac.</th>
                        <th>Grupo</th>
                        <th>Padre</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($alumnos)): ?>
                    <tr><td colspan="7" class="text-center">No hay alumnos registrados.</td></tr>
                <?php else: foreach ($alumnos as $i => $a): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td><?= htmlspecialchars($a['nombre']) ?></td>
                        <td><?= htmlspecialchars($a['apellido']) ?></td>
                        <td><?= htmlspecialchars($a['fecha_nacimiento']) ?></td>
                        <td><?= htmlspecialchars($a['grupo']) ?></td>
                        <td><?= htmlspecialchars($a['padre_nombre'] . ' ' . $a['padre_apellido']) ?></td>
                        <td>
                            <a href="?editar=<?= $a['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                            <a href="?eliminar=<?= $a['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que deseas eliminar este alumno?')"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <a href="panel_secretario.php" class="btn btn-link mt-3"><i class="bi bi-arrow-left"></i> Volver al panel</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 