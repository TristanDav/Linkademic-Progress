<?php
require_once '../auth_docente.php';
$usuario_id = $_SESSION['usuario_id'];

// Obtener datos del docente y grupo asignado
$consulta = $conexion->prepare('
    SELECT u.nombre, u.apellido, d.grupo_id
    FROM usuarios u
    INNER JOIN docentes d ON d.usuario_id = u.id
    WHERE u.id = ?
');
$consulta->bind_param('i', $usuario_id);
$consulta->execute();
$consulta->bind_result($nombre, $apellido, $grupo_id);
$consulta->fetch();
$consulta->close();

// Materias del grupo
$materias = [];
if ($grupo_id) {
    $sqlMat = "SELECT m.id, m.nombre FROM grupo_materias gm INNER JOIN materias m ON gm.materia_id = m.id WHERE gm.grupo_id = ?";
    $stmtMat = $conexion->prepare($sqlMat);
    $stmtMat->bind_param('i', $grupo_id);
    $stmtMat->execute();
    $resMat = $stmtMat->get_result();
    while ($row = $resMat->fetch_assoc()) {
        $materias[] = $row;
    }
    $stmtMat->close();
}

// Registrar evaluación
$mensaje = '';
if (isset($_POST['registrar_evaluacion'])) {
    $materia_id = intval($_POST['materia_id'] ?? 0);
    $tipo = trim($_POST['tipo'] ?? '');
    $nombre_eval = trim($_POST['nombre_eval'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $fecha = $_POST['fecha'] ?? '';
    if ($materia_id && $tipo && $nombre_eval && $fecha && $grupo_id) {
        $stmtEval = $conexion->prepare("INSERT INTO evaluaciones (nombre, descripcion, fecha, materia_id, grupo_id, tipo) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtEval->bind_param('sssiss', $nombre_eval, $descripcion, $fecha, $materia_id, $grupo_id, $tipo);
        if ($stmtEval->execute()) {
            $mensaje = '<div class="alert alert-success mt-2">Evaluación registrada correctamente.</div>';
        } else {
            $mensaje = '<div class="alert alert-danger mt-2">Error al registrar la evaluación.</div>';
        }
        $stmtEval->close();
    } else {
        $mensaje = '<div class="alert alert-warning mt-2">Todos los campos son obligatorios.</div>';
    }
}

// Evaluaciones ya registradas
$evaluaciones = [];
if ($grupo_id && count($materias) > 0) {
    $materia_ids = array_column($materias, 'id');
    $placeholders = implode(',', array_fill(0, count($materia_ids), '?'));
    $types = str_repeat('i', count($materia_ids));
    $sqlEvals = "SELECT e.*, m.nombre as materia_nombre FROM evaluaciones e INNER JOIN materias m ON e.materia_id = m.id WHERE e.grupo_id = ? AND e.materia_id IN ($placeholders) ORDER BY e.fecha DESC";
    $stmtEvals = $conexion->prepare($sqlEvals);
    $params = array_merge([$grupo_id], $materia_ids);
    $stmtEvals->bind_param('i'. $types, ...$params);
    $stmtEvals->execute();
    $resEvals = $stmtEvals->get_result();
    while ($ev = $resEvals->fetch_assoc()) {
        $evaluaciones[] = $ev;
    }
    $stmtEvals->close();
}

// Eliminar evaluación
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $eval_id = intval($_GET['eliminar']);
    $stmtDel = $conexion->prepare("DELETE FROM evaluaciones WHERE id = ? AND grupo_id = ?");
    $stmtDel->bind_param('ii', $eval_id, $grupo_id);
    if ($stmtDel->execute()) {
        $mensaje = '<div class="alert alert-success mt-2">Evaluación eliminada correctamente.</div>';
    } else {
        $mensaje = '<div class="alert alert-danger mt-2">Error al eliminar la evaluación.</div>';
    }
    $stmtDel->close();
}

// Editar evaluación
$editando = false;
$edit_eval = null;
if (isset($_GET['editar']) && is_numeric($_GET['editar'])) {
    $editando = true;
    $edit_id = intval($_GET['editar']);
    $stmtEdit = $conexion->prepare("SELECT * FROM evaluaciones WHERE id = ? AND grupo_id = ?");
    $stmtEdit->bind_param('ii', $edit_id, $grupo_id);
    $stmtEdit->execute();
    $resEdit = $stmtEdit->get_result();
    if ($resEdit && $resEdit->num_rows > 0) {
        $edit_eval = $resEdit->fetch_assoc();
    }
    $stmtEdit->close();
}

// Actualizar evaluación
if (isset($_POST['actualizar_evaluacion'])) {
    $eval_id = intval($_POST['eval_id'] ?? 0);
    $materia_id = intval($_POST['materia_id'] ?? 0);
    $tipo = trim($_POST['tipo'] ?? '');
    $nombre_eval = trim($_POST['nombre_eval'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $fecha = $_POST['fecha'] ?? '';
    if ($eval_id && $materia_id && $tipo && $nombre_eval && $fecha && $grupo_id) {
        $stmtUpd = $conexion->prepare("UPDATE evaluaciones SET nombre=?, descripcion=?, fecha=?, materia_id=?, tipo=? WHERE id=? AND grupo_id=?");
        $stmtUpd->bind_param('sssissi', $nombre_eval, $descripcion, $fecha, $materia_id, $tipo, $eval_id, $grupo_id);
        if ($stmtUpd->execute()) {
            $mensaje = '<div class="alert alert-success mt-2">Evaluación actualizada correctamente.</div>';
            $editando = false;
        } else {
            $mensaje = '<div class="alert alert-danger mt-2">Error al actualizar la evaluación.</div>';
        }
        $stmtUpd->close();
    } else {
        $mensaje = '<div class="alert alert-warning mt-2">Todos los campos son obligatorios para actualizar.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Evaluaciones | Docente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/docente_style.css">
</head>
<body>
    <div class="container py-4">
        <a href="panel_docente.php#evaluaciones" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Volver al Panel</a>
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="mb-4 text-primary"><i class="bi bi-journal-plus me-2"></i>Registrar Nueva Evaluación</h2>
                <?php if ($mensaje) echo $mensaje; ?>
                <form method="post" class="row g-3" autocomplete="off">
                    <?php if ($editando && $edit_eval): ?>
                        <input type="hidden" name="eval_id" value="<?= $edit_eval['id'] ?>">
                    <?php endif; ?>
                    <div class="col-md-4">
                        <label class="form-label">Materia</label>
                        <select name="materia_id" class="form-select" required>
                            <option value="">Selecciona una materia</option>
                            <?php foreach ($materias as $mat): ?>
                                <option value="<?= $mat['id'] ?>" <?= ($editando && $edit_eval && $edit_eval['materia_id'] == $mat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($mat['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" class="form-select" required>
                            <option value="">Selecciona tipo</option>
                            <option value="Tarea" <?= ($editando && $edit_eval && $edit_eval['tipo'] == 'Tarea') ? 'selected' : '' ?>>Tarea</option>
                            <option value="Examen" <?= ($editando && $edit_eval && $edit_eval['tipo'] == 'Examen') ? 'selected' : '' ?>>Examen</option>
                            <option value="Actividad" <?= ($editando && $edit_eval && $edit_eval['tipo'] == 'Actividad') ? 'selected' : '' ?>>Actividad</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Nombre de la Evaluación</label>
                        <input type="text" name="nombre_eval" class="form-control" maxlength="100" required value="<?= $editando && $edit_eval ? htmlspecialchars($edit_eval['nombre']) : '' ?>">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Descripción</label>
                        <input type="text" name="descripcion" class="form-control" maxlength="255" value="<?= $editando && $edit_eval ? htmlspecialchars($edit_eval['descripcion']) : '' ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha</label>
                        <input type="date" name="fecha" class="form-control" required value="<?= $editando && $edit_eval ? $edit_eval['fecha'] : '' ?>">
                    </div>
                    <div class="col-12">
                        <?php if ($editando && $edit_eval): ?>
                            <button type="submit" name="actualizar_evaluacion" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Actualizar Evaluación
                            </button>
                            <a href="registrar_evaluaciones.php" class="btn btn-secondary ms-2">Cancelar</a>
                        <?php else: ?>
                            <button type="submit" name="registrar_evaluacion" class="btn btn-success">
                                <i class="bi bi-plus-circle me-2"></i>Registrar Evaluación
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h4 class="mb-3 text-primary"><i class="bi bi-list-task me-2"></i>Evaluaciones Registradas</h4>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Materia</th>
                                <th>Tipo</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($evaluaciones) > 0): ?>
                                <?php foreach ($evaluaciones as $ev): ?>
                                <tr>
                                    <td><?= htmlspecialchars($ev['materia_nombre']) ?></td>
                                    <td><?= htmlspecialchars($ev['tipo']) ?></td>
                                    <td><strong><?= htmlspecialchars($ev['nombre']) ?></strong></td>
                                    <td><?= htmlspecialchars($ev['descripcion']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($ev['fecha'])) ?></td>
                                    <td>
                                        <a href="registrar_evaluaciones.php?editar=<?= $ev['id'] ?>" class="btn btn-sm btn-outline-success"><i class="bi bi-pencil"></i></a>
                                        <a href="registrar_evaluaciones.php?eliminar=<?= $ev['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Seguro que deseas eliminar esta evaluación?');"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center">No hay evaluaciones registradas.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 