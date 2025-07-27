<?php
session_start();
require_once '../conexion.php';

// Obtener el ID de usuario desde la sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login_docentes.php');
    exit();
}
$usuario_id = $_SESSION['usuario_id'];

// Consultar datos básicos del docente y su grupo asignado
$consulta = $conexion->prepare('
    SELECT u.nombre, u.apellido, d.especialidad, d.numero_empleado, g.nombre AS grupo
    FROM usuarios u
    INNER JOIN docentes d ON d.usuario_id = u.id
    LEFT JOIN grupos g ON d.grupo_id = g.id
    WHERE u.id = ?
');
$consulta->bind_param('i', $usuario_id);
$consulta->execute();
$consulta->bind_result($nombre, $apellido, $especialidad, $numero_empleado, $grupo);
$consulta->fetch();
$consulta->close();

// Obtener alumnos del grupo asignado al docente
$alumnos = [];
if ($grupo) {
    $stmtAlumnos = $conexion->prepare('SELECT id, nombre, apellido, fecha_nacimiento FROM alumnos WHERE grupo_id = ?');
    $stmtAlumnos->bind_param('i', $grupo_id);
    // Obtener el id real del grupo
    // Primero, obtener el id del grupo por el nombre
    $stmtGrupo = $conexion->prepare('SELECT id FROM grupos WHERE nombre = ?');
    $stmtGrupo->bind_param('s', $grupo);
    $stmtGrupo->execute();
    $stmtGrupo->bind_result($grupo_id);
    if ($stmtGrupo->fetch()) {
        $stmtGrupo->close();
        $stmtAlumnos->bind_param('i', $grupo_id);
        $stmtAlumnos->execute();
        $resAlumnos = $stmtAlumnos->get_result();
        while ($row = $resAlumnos->fetch_assoc()) {
            $alumnos[] = $row;
        }
        $stmtAlumnos->close();
    } else {
        $stmtGrupo->close();
    }
}

// Obtener materias del grupo asignado al docente
$materias = [];
$periodo_cerrado = false; // Simulación, puedes cambiarlo según tu lógica de periodos
if (isset($grupo_id)) {
    $sqlMaterias = "SELECT m.id, m.nombre FROM grupo_materias gm INNER JOIN materias m ON gm.materia_id = m.id WHERE gm.grupo_id = ?";
    $stmtMat = $conexion->prepare($sqlMaterias);
    $stmtMat->bind_param('i', $grupo_id);
    $stmtMat->execute();
    $resMat = $stmtMat->get_result();
    while ($row = $resMat->fetch_assoc()) {
        // Calcular promedio del grupo en la materia
        $sqlProm = "SELECT AVG(c.calificacion) as promedio FROM calificaciones c INNER JOIN evaluaciones e ON c.evaluacion_id = e.id WHERE e.materia_id = ? AND e.grupo_id = ?";
        $stmtProm = $conexion->prepare($sqlProm);
        $stmtProm->bind_param('ii', $row['id'], $grupo_id);
        $stmtProm->execute();
        $stmtProm->bind_result($promedio);
        $stmtProm->fetch();
        $stmtProm->close();
        $row['promedio_grupo'] = $promedio !== null ? round($promedio, 2) : '-';
        $row['total_alumnos'] = count($alumnos);
        // Estado dinámico
        if ($periodo_cerrado) {
            $row['estado'] = ['Cerrado', 'secondary'];
        } else {
            // ¿Hay evaluaciones para esta materia y grupo?
            $sqlEval = "SELECT id FROM evaluaciones WHERE materia_id = ? AND grupo_id = ?";
            $stmtEval = $conexion->prepare($sqlEval);
            $stmtEval->bind_param('ii', $row['id'], $grupo_id);
            $stmtEval->execute();
            $resEval = $stmtEval->get_result();
            if ($resEval->num_rows == 0) {
                $row['estado'] = ['Pendiente', 'warning'];
            } else {
                // Contar calificaciones registradas
                $evaluaciones_ids = [];
                while ($ev = $resEval->fetch_assoc()) {
                    $evaluaciones_ids[] = $ev['id'];
                }
                $stmtEval->close();
                if (count($evaluaciones_ids) > 0) {
                    $eval_ids_str = implode(',', $evaluaciones_ids);
                    $sqlCal = "SELECT COUNT(*) FROM calificaciones WHERE evaluacion_id IN ($eval_ids_str)";
                    $resCal = $conexion->query($sqlCal);
                    $total_cal = $resCal ? $resCal->fetch_row()[0] : 0;
                    $total_esperado = count($alumnos) * count($evaluaciones_ids);
                    if ($total_cal == 0) {
                        $row['estado'] = ['Pendiente', 'warning'];
                    } elseif ($total_cal < $total_esperado) {
                        $row['estado'] = ['En revisión', 'info'];
                    } else {
                        $row['estado'] = ['Actualizado', 'success'];
                    }
                } else {
                    $row['estado'] = ['Pendiente', 'warning'];
                }
            }
        }
        $materias[] = $row;
    }
    $stmtMat->close();
}

$avisos = [
    ['titulo' => 'Reunión de Padres', 'fecha' => '2025-02-15', 'estado' => 'Pendiente'],
    ['titulo' => 'Entrega de Proyectos', 'fecha' => '2025-02-20', 'estado' => 'Enviado'],
    ['titulo' => 'Examen Bimestral', 'fecha' => '2025-02-25', 'estado' => 'Pendiente'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Docente | Escuela Primaria</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="css/docente_style.css">
</head>
<body>
    <div class="menu-btn" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </div>
    
    <div class="sidebar" id="sidebar">
        <a href="#" onclick="showSection('inicio')">
            <i class="bi bi-house-door"></i> Inicio
        </a>
        <a href="#" onclick="showSection('alumnos')">
            <i class="bi bi-people"></i> Alumnos
        </a>
        <a href="#" onclick="showSection('calificaciones')">
            <i class="bi bi-clipboard-data"></i> Calificaciones
        </a>
        <a href="#" onclick="showSection('asistencias')">
            <i class="bi bi-calendar-check"></i> Asistencias
        </a>
        <a href="#" onclick="showSection('avisos')">
            <i class="bi bi-megaphone"></i> Avisos
        </a>
        <a href="#" onclick="showSection('evaluaciones')">
            <i class="bi bi-journal-plus"></i> Evaluaciones
        </a>
        <a href="#" onclick="window.location.href='../index.html'" style="margin-top: 20px; color: #dc3545;">
            <i class="bi bi-box-arrow-left"></i> Cerrar Sesión
        </a>
    </div>

    <div class="content" id="mainContent">
        <div class="section active" id="inicio">
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card welcome-card">
                        <h2><i class="bi bi-person-badge me-2"></i>¡Bienvenido, Docente!</h2>
                        <p>Gestiona tus alumnos, registra calificaciones y asistencias, y mantén informados a los padres de familia desde este panel.</p>
                    </div>
                    <!-- Tarjeta de perfil docente -->
                    <div class="card mt-3">
                        <div class="card-body">
                            <h5 class="card-title mb-3"><i class="bi bi-person-circle me-2"></i>Perfil del Docente</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><strong>Nombre:</strong> <?= htmlspecialchars($nombre . ' ' . $apellido) ?></li>
                                <li class="list-group-item"><strong>Especialidad:</strong> <?= htmlspecialchars($especialidad) ?></li>
                                <li class="list-group-item"><strong>Número de empleado:</strong> <?= htmlspecialchars($numero_empleado) ?></li>
                                <li class="list-group-item"><strong>Grupo asignado:</strong> <?= htmlspecialchars($grupo ? $grupo : 'Sin grupo asignado') ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card stats-card">
                        <h3><?= count($alumnos) ?></h3>
                        <p>Alumnos en tu grupo</p>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="card text-center acceso-panel">
                        <div class="card-body">
                            <i class="bi bi-people-fill display-4 text-primary mb-3"></i>
                            <h5 class="card-title">Alumnos</h5>
                            <p class="card-text">Consulta y gestiona la lista de alumnos de tu grupo.</p>
                            <a href="#" onclick="showSection('alumnos')" class="btn btn-primary">Ver Alumnos</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card text-center acceso-panel">
                        <div class="card-body">
                            <i class="bi bi-clipboard-data-fill display-4 text-success mb-3"></i>
                            <h5 class="card-title">Calificaciones</h5>
                            <p class="card-text">Registra y consulta las calificaciones de los estudiantes.</p>
                            <a href="#" onclick="showSection('calificaciones')" class="btn btn-success">Ver Calificaciones</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card text-center acceso-panel">
                        <div class="card-body">
                            <i class="bi bi-calendar-check-fill display-4 text-warning mb-3"></i>
                            <h5 class="card-title">Asistencias</h5>
                            <p class="card-text">Registra y revisa la asistencia de los alumnos.</p>
                            <a href="#" onclick="showSection('asistencias')" class="btn btn-warning text-white">Ver Asistencias</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card text-center acceso-panel">
                        <div class="card-body">
                            <i class="bi bi-megaphone-fill display-4 text-info mb-3"></i>
                            <h5 class="card-title">Avisos</h5>
                            <p class="card-text">Envía avisos importantes a los padres de familia.</p>
                            <a href="#" onclick="showSection('avisos')" class="btn btn-info text-white">Ver Avisos</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section" id="alumnos">
            <div class="card">
                <h2 class="mb-4 text-primary">
                    <i class="bi bi-people me-2"></i>
                    Lista de Alumnos
                </h2>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Grupo</th>
                                <th>Asistencias (%)</th>
                                <th>Promedio</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($alumnos) > 0): ?>
                                <?php foreach ($alumnos as $alumno): ?>
                                <?php
                                    // Calcular porcentaje de asistencias
                                    $total_dias = 0;
                                    $total_presentes = 0;
                                    $stmtTotal = $conexion->prepare('SELECT COUNT(*) FROM asistencia WHERE alumno_id = ?');
                                    $stmtTotal->bind_param('i', $alumno['id']);
                                    $stmtTotal->execute();
                                    $stmtTotal->bind_result($total_dias);
                                    $stmtTotal->fetch();
                                    $stmtTotal->close();
                                    $stmtPres = $conexion->prepare('SELECT COUNT(*) FROM asistencia WHERE alumno_id = ? AND presente = 1');
                                    $stmtPres->bind_param('i', $alumno['id']);
                                    $stmtPres->execute();
                                    $stmtPres->bind_result($total_presentes);
                                    $stmtPres->fetch();
                                    $stmtPres->close();
                                    $porc_asistencia = ($total_dias > 0) ? round(($total_presentes / $total_dias) * 100, 1) : '-';
                                    // Calcular promedio de calificaciones
                                    $promedio = '-';
                                    $stmtProm = $conexion->prepare('SELECT AVG(calificacion) FROM calificaciones WHERE alumno_id = ?');
                                    $stmtProm->bind_param('i', $alumno['id']);
                                    $stmtProm->execute();
                                    $stmtProm->bind_result($prom);
                                    if ($stmtProm->fetch() && $prom !== null) {
                                        $promedio = round($prom, 2);
                                    }
                                    $stmtProm->close();
                                ?>
                                <tr>
                                    <td><?= $alumno['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido']) ?></strong></td>
                                    <td><?= htmlspecialchars($grupo) ?></td>
                                    <td><?= $porc_asistencia !== '-' ? $porc_asistencia . '%' : '-' ?></td>
                                    <td><?= $promedio !== '-' ? '<span class="badge bg-'.($promedio >= 9 ? 'success' : ($promedio >= 8 ? 'warning' : 'danger')).'">'.$promedio.'</span>' : '<span class="badge bg-secondary">-</span>' ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center">No hay alumnos registrados en este grupo.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="section" id="calificaciones">
            <div class="card">
                <h2 class="mb-4 text-primary">
                    <i class="bi bi-clipboard-data me-2"></i>
                    Calificaciones por Evaluación
                </h2>
                <?php
                // Obtener evaluaciones del grupo
                $evaluaciones = [];
                if (isset($grupo_id)) {
                    $sqlEval = "SELECT e.*, m.nombre as materia_nombre FROM evaluaciones e INNER JOIN materias m ON e.materia_id = m.id WHERE e.grupo_id = ? ORDER BY e.fecha DESC";
                    $stmtEval = $conexion->prepare($sqlEval);
                    $stmtEval->bind_param('i', $grupo_id);
                    $stmtEval->execute();
                    $resEval = $stmtEval->get_result();
                    while ($ev = $resEval->fetch_assoc()) {
                        // Calcular promedio de calificación para la evaluación
                        $sqlProm = "SELECT AVG(calificacion) as promedio FROM calificaciones WHERE evaluacion_id = ?";
                        $stmtProm = $conexion->prepare($sqlProm);
                        $stmtProm->bind_param('i', $ev['id']);
                        $stmtProm->execute();
                        $stmtProm->bind_result($promedio);
                        $stmtProm->fetch();
                        $stmtProm->close();
                        $ev['promedio'] = $promedio !== null ? round($promedio, 2) : '-';
                        $evaluaciones[] = $ev;
                    }
                    $stmtEval->close();
                }
                ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Evaluación</th>
                                <th>Materia</th>
                                <th>Fecha</th>
                                <th>Promedio de Calificación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($evaluaciones) > 0): ?>
                                <?php foreach ($evaluaciones as $ev): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($ev['nombre']) ?></strong></td>
                                    <td><?= htmlspecialchars($ev['materia_nombre']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($ev['fecha'])) ?></td>
                                    <td>
                                        <?php if ($ev['promedio'] !== '-'): ?>
                                            <span class="badge bg-<?= $ev['promedio'] >= 9 ? 'success' : ($ev['promedio'] >= 8 ? 'warning' : 'danger') ?>">
                                                <?= $ev['promedio'] ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="registrar_calificaciones.php?evaluacion_id=<?= $ev['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil-square"></i> Registrar/Editar Calificaciones
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center">No hay evaluaciones registradas.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="section" id="asistencias">
            <div class="card">
                <h2 class="mb-4 text-primary">
                    <i class="bi bi-calendar-check me-2"></i>
                    Control de Asistencias
                </h2>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4 class="text-success"><?= count($alumnos) ?></h4>
                                <p class="mb-0">Total de Alumnos</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4 class="text-primary">Hoy</h4>
                                <p class="mb-0">Fecha: <?= date('d/m/Y') ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <button class="btn btn-success btn-lg" onclick="window.location.href='registrar_asistencia.php'">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Tomar Asistencia
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Instrucciones:</strong> Haz clic en "Tomar Asistencia" para registrar la asistencia de hoy, o selecciona una fecha específica para ver el historial.
                </div>
            </div>
        </div>

        <div class="section" id="avisos">
            <div class="card">
                <h2 class="mb-4 text-primary">
                    <i class="bi bi-megaphone me-2"></i>
                    Gestión de Avisos
                </h2>
                <div class="row mb-4">
                    <div class="col-md-8">
                        <button class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>
                            Nuevo Aviso
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Título del Aviso</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($avisos as $aviso): ?>
                            <tr>
                                <td><strong><?= $aviso['titulo'] ?></strong></td>
                                <td><?= date('d/m/Y', strtotime($aviso['fecha'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= $aviso['estado'] == 'Enviado' ? 'success' : 'warning' ?>">
                                        <?= $aviso['estado'] ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="section" id="evaluaciones">
            <div class="card">
                <h2 class="mb-4 text-primary">
                    <i class="bi bi-journal-plus me-2"></i>
                    Evaluaciones Registradas
                </h2>
                <div class="mb-3">
                    <a href="registrar_evaluaciones.php" class="btn btn-success">
                        <i class="bi bi-plus-circle me-2"></i>Registrar Nueva Evaluación
                    </a>
                </div>
                <?php
                // Obtener evaluaciones del grupo y materias del docente
                $evaluaciones = [];
                if (isset($grupo_id) && count($materias) > 0) {
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
                ?>
                <div class="table-responsive mt-4">
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
                                        <button class="btn btn-sm btn-outline-success"><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
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

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('mainContent').classList.toggle('shift');
        }

        function showSection(id) {
            document.querySelectorAll('.section').forEach(sec => sec.classList.remove('active'));
            document.getElementById(id).classList.add('active');
        }
    </script>
</body>
</html> 