<?php
session_start();
require_once '../conexion.php';

// Seguridad: solo docentes logueados
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login_docentes.php');
    exit();
}
$usuario_id = $_SESSION['usuario_id'];

// Obtener grupo asignado
$consulta = $conexion->prepare('SELECT d.grupo_id, u.nombre, u.apellido FROM docentes d INNER JOIN usuarios u ON d.usuario_id = u.id WHERE d.usuario_id = ?');
$consulta->bind_param('i', $usuario_id);
$consulta->execute();
$consulta->bind_result($grupo_id, $nombre_docente, $apellido_docente);
$consulta->fetch();
$consulta->close();

if (!$grupo_id) {
    die('<div class="alert alert-danger m-4">No tienes un grupo asignado.</div>');
}

// Fecha seleccionada (por defecto hoy)
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
$mensaje = '';

// Obtener alumnos del grupo
$alumnos = [];
$stmtAlum = $conexion->prepare('SELECT id, nombre, apellido FROM alumnos WHERE grupo_id = ? ORDER BY apellido, nombre');
$stmtAlum->bind_param('i', $grupo_id);
$stmtAlum->execute();
$resAlum = $stmtAlum->get_result();
while ($row = $resAlum->fetch_assoc()) {
    $alumnos[] = $row;
}
$stmtAlum->close();

// Obtener asistencias ya registradas para la fecha
$asistencias = [];
$stmtAsis = $conexion->prepare('SELECT alumno_id, presente FROM asistencia WHERE fecha = ? AND alumno_id IN (SELECT id FROM alumnos WHERE grupo_id = ?)');
$stmtAsis->bind_param('si', $fecha, $grupo_id);
$stmtAsis->execute();
$resAsis = $stmtAsis->get_result();
while ($row = $resAsis->fetch_assoc()) {
    $asistencias[$row['alumno_id']] = $row['presente'];
}
$stmtAsis->close();

// Guardar asistencia
if (isset($_POST['guardar_asistencia'])) {
    $fecha_post = $_POST['fecha'] ?? date('Y-m-d');
    foreach ($alumnos as $alumno) {
        $alumno_id = $alumno['id'];
        $presente = isset($_POST['presente'][$alumno_id]) ? 1 : 0;
        // Verificar si ya existe
        $stmtCheck = $conexion->prepare('SELECT id FROM asistencia WHERE alumno_id = ? AND fecha = ?');
        $stmtCheck->bind_param('is', $alumno_id, $fecha_post);
        $stmtCheck->execute();
        $stmtCheck->store_result();
        if ($stmtCheck->num_rows > 0) {
            // Actualizar
            $stmtUpd = $conexion->prepare('UPDATE asistencia SET presente = ? WHERE alumno_id = ? AND fecha = ?');
            $stmtUpd->bind_param('iis', $presente, $alumno_id, $fecha_post);
            $stmtUpd->execute();
            $stmtUpd->close();
        } else {
            // Insertar
            $stmtIns = $conexion->prepare('INSERT INTO asistencia (alumno_id, fecha, presente) VALUES (?, ?, ?)');
            $stmtIns->bind_param('isi', $alumno_id, $fecha_post, $presente);
            $stmtIns->execute();
            $stmtIns->close();
        }
        $stmtCheck->close();
    }
    $mensaje = '<div class="alert alert-success mt-2">Asistencia guardada correctamente para ' . date('d/m/Y', strtotime($fecha_post)) . '.</div>';
    // Recargar asistencias
    $asistencias = [];
    $stmtAsis = $conexion->prepare('SELECT alumno_id, presente FROM asistencia WHERE fecha = ? AND alumno_id IN (SELECT id FROM alumnos WHERE grupo_id = ?)');
    $stmtAsis->bind_param('si', $fecha_post, $grupo_id);
    $stmtAsis->execute();
    $resAsis = $stmtAsis->get_result();
    while ($row = $resAsis->fetch_assoc()) {
        $asistencias[$row['alumno_id']] = $row['presente'];
    }
    $stmtAsis->close();
    $fecha = $fecha_post;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pase de Lista | Docente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/docente_style.css">
    <style>
        body { background: #e8f5e9; }
        .asistencia-card { border-left: 6px solid #43a047; box-shadow: 0 2px 8px #0001; }
        .asistencia-table th, .asistencia-table td { vertical-align: middle; }
        .calendar-label { font-weight: bold; color: #388e3c; }
        .btn-guardar { background: #43a047; border: none; }
        .btn-guardar:hover { background: #388e3c; }
    </style>
</head>
<body>
<div class="container py-4">
    <a href="panel_docente.php#asistencias" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Volver al Panel</a>
    <div class="card asistencia-card mb-4">
        <div class="card-body">
            <h2 class="mb-4 text-success"><i class="bi bi-calendar-check me-2"></i>Pase de Lista</h2>
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="mb-2 calendar-label">Fecha de asistencia:</div>
                    <form method="get" class="d-inline">
                        <input type="date" name="fecha" value="<?= htmlspecialchars($fecha) ?>" max="<?= date('Y-m-d') ?>" class="form-control d-inline w-auto" onchange="this.form.submit()">
                    </form>
                </div>
                <div class="col-md-6 text-end">
                    <div class="calendar-label">Docente:</div>
                    <span><?= htmlspecialchars($nombre_docente . ' ' . $apellido_docente) ?></span>
                </div>
            </div>
            <?php if ($mensaje) echo $mensaje; ?>
            <form method="post">
                <input type="hidden" name="fecha" value="<?= htmlspecialchars($fecha) ?>">
                <div class="table-responsive">
                    <table class="table table-bordered asistencia-table">
                        <thead class="table-success">
                            <tr>
                                <th>#</th>
                                <th>Alumno</th>
                                <th>Presente</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alumnos as $i => $alumno): ?>
                            <tr>
                                <td><?= $i+1 ?></td>
                                <td><?= htmlspecialchars($alumno['apellido'] . ' ' . $alumno['nombre']) ?></td>
                                <td class="text-center">
                                    <input type="checkbox" name="presente[<?= $alumno['id'] ?>]" value="1" <?= (isset($asistencias[$alumno['id']]) ? ($asistencias[$alumno['id']] ? 'checked' : '') : 'checked') ?> >
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (count($alumnos) == 0): ?>
                            <tr><td colspan="3" class="text-center">No hay alumnos registrados en el grupo.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (count($alumnos) > 0): ?>
                <button type="submit" name="guardar_asistencia" class="btn btn-guardar btn-lg mt-3"><i class="bi bi-save me-2"></i>Guardar Asistencia</button>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 