<?php
require_once '../auth_docente.php';
$usuario_id = $_SESSION['usuario_id'];

// Obtener grupo asignado
$consulta = $conexion->prepare('
    SELECT d.grupo_id
    FROM docentes d
    WHERE d.usuario_id = ?
');
$consulta->bind_param('i', $usuario_id);
$consulta->execute();
$consulta->bind_result($grupo_id);
$consulta->fetch();
$consulta->close();

// Obtener evaluaciones del grupo
$evaluaciones = [];
if ($grupo_id) {
    $sqlEval = "SELECT e.id, e.nombre, m.nombre as materia_nombre, e.fecha FROM evaluaciones e INNER JOIN materias m ON e.materia_id = m.id WHERE e.grupo_id = ? ORDER BY e.fecha DESC";
    $stmtEval = $conexion->prepare($sqlEval);
    $stmtEval->bind_param('i', $grupo_id);
    $stmtEval->execute();
    $resEval = $stmtEval->get_result();
    while ($row = $resEval->fetch_assoc()) {
        $evaluaciones[] = $row;
    }
    $stmtEval->close();
}

// Selección de evaluación
$eval_id = isset($_GET['evaluacion_id']) ? intval($_GET['evaluacion_id']) : 0;
$mensaje = '';

// Obtener alumnos del grupo
$alumnos = [];
if ($grupo_id) {
    $sqlAlum = "SELECT id, nombre, apellido FROM alumnos WHERE grupo_id = ? ORDER BY apellido, nombre";
    $stmtAlum = $conexion->prepare($sqlAlum);
    $stmtAlum->bind_param('i', $grupo_id);
    $stmtAlum->execute();
    $resAlum = $stmtAlum->get_result();
    while ($row = $resAlum->fetch_assoc()) {
        $alumnos[] = $row;
    }
    $stmtAlum->close();
}

// Guardar calificaciones
if (isset($_POST['guardar_calificaciones']) && $eval_id) {
    foreach ($alumnos as $alumno) {
        $alumno_id = $alumno['id'];
        $calif = isset($_POST['calif'][$alumno_id]) ? floatval($_POST['calif'][$alumno_id]) : null;
        if ($calif !== null && $calif >= 0 && $calif <= 10) {
            // Verificar si ya existe
            $stmtCheck = $conexion->prepare("SELECT id FROM calificaciones WHERE alumno_id = ? AND evaluacion_id = ?");
            $stmtCheck->bind_param('ii', $alumno_id, $eval_id);
            $stmtCheck->execute();
            $stmtCheck->store_result();
            if ($stmtCheck->num_rows > 0) {
                // Actualizar
                $stmtUpd = $conexion->prepare("UPDATE calificaciones SET calificacion = ? WHERE alumno_id = ? AND evaluacion_id = ?");
                $stmtUpd->bind_param('dii', $calif, $alumno_id, $eval_id);
                $stmtUpd->execute();
                $stmtUpd->close();
            } else {
                // Insertar
                $stmtIns = $conexion->prepare("INSERT INTO calificaciones (alumno_id, evaluacion_id, calificacion) VALUES (?, ?, ?)");
                $stmtIns->bind_param('iid', $alumno_id, $eval_id, $calif);
                $stmtIns->execute();
                $stmtIns->close();
            }
            $stmtCheck->close();
        }
    }
    $mensaje = '<div class="alert alert-success mt-2">Calificaciones guardadas correctamente.</div>';
}

// Obtener calificaciones actuales para la evaluación
$calificaciones = [];
if ($eval_id) {
    $sqlC = "SELECT alumno_id, calificacion FROM calificaciones WHERE evaluacion_id = ?";
    $stmtC = $conexion->prepare($sqlC);
    $stmtC->bind_param('i', $eval_id);
    $stmtC->execute();
    $resC = $stmtC->get_result();
    while ($row = $resC->fetch_assoc()) {
        $calificaciones[$row['alumno_id']] = $row['calificacion'];
    }
    $stmtC->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Calificaciones | Docente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/docente_style.css">
    <link rel="stylesheet" href="css/registrar_calificaciones_style.css">
</head>
<body>
    <div class="container py-4">
        <a href="panel_docente.php#calificaciones" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Volver al Panel</a>
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="mb-4 text-primary"><i class="bi bi-clipboard-data me-2"></i>Registrar Calificaciones</h2>
                <?php if ($mensaje) echo $mensaje; ?>
                <form method="get" class="row g-3 mb-3">
                    <div class="col-md-8">
                        <label class="form-label">Selecciona Evaluación</label>
                        <select name="evaluacion_id" class="form-select" required onchange="this.form.submit()">
                            <option value="">-- Selecciona una evaluación --</option>
                            <?php foreach ($evaluaciones as $ev): ?>
                                <option value="<?= $ev['id'] ?>" <?= $eval_id == $ev['id'] ? 'selected' : '' ?>><?= htmlspecialchars($ev['nombre']) ?> (<?= htmlspecialchars($ev['materia_nombre']) ?>, <?= date('d/m/Y', strtotime($ev['fecha'])) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
                <?php if ($eval_id && count($alumnos) > 0): ?>
                <form method="post">
                    <input type="hidden" name="evaluacion_id" value="<?= $eval_id ?>">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>Alumno</th>
                                <th>Calificación (0-10)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alumnos as $alumno): ?>
                            <tr>
                                <td><?= htmlspecialchars($alumno['apellido'] . ' ' . $alumno['nombre']) ?></td>
                                <td>
                                    <input type="number" step="0.01" min="0" max="10" name="calif[<?= $alumno['id'] ?>]" class="form-control calificacion-input" value="<?= isset($calificaciones[$alumno['id']]) ? $calificaciones[$alumno['id']] : '' ?>">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="submit" name="guardar_calificaciones" class="btn btn-success">
                        <i class="bi bi-save me-2"></i>Guardar Calificaciones
                    </button>
                </form>
                <?php elseif ($eval_id): ?>
                    <div class="alert alert-warning mt-3">No hay alumnos registrados en el grupo.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 