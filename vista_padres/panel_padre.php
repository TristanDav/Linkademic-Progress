<?php
session_start();
require_once '../conexion.php';

// Verificar si el padre está logueado
if (!isset($_SESSION['padre_id'])) {
    header('Location: ../login_padres.php');
    exit();
}

$padre_id = $_SESSION['padre_id'];

// Obtener datos del padre
$stmt_padre = $conexion->prepare("
    SELECT u.nombre, u.apellido, u.correo, p.telefono, p.direccion, p.parentesco, p.profesion, p.correo_alternativo
    FROM usuarios u
    LEFT JOIN padres p ON u.id = p.usuario_id
    WHERE u.id = ?
");
$stmt_padre->bind_param('i', $padre_id);
$stmt_padre->execute();
$stmt_padre->bind_result($nombre_padre, $apellido_padre, $correo_padre, $telefono_padre, $direccion_padre, $parentesco_padre, $profesion_padre, $correo_alt_padre);
$stmt_padre->fetch();
$stmt_padre->close();

// Obtener hijos del padre
$hijos = [];
$stmt_hijos = $conexion->prepare("
    SELECT a.id, a.nombre, a.apellido, a.fecha_nacimiento, g.nombre as grupo_nombre
    FROM alumnos a
    LEFT JOIN grupos g ON a.grupo_id = g.id
    WHERE a.padre_id = ?
    ORDER BY a.nombre, a.apellido
");
$stmt_hijos->bind_param('i', $padre_id);
$stmt_hijos->execute();
$result_hijos = $stmt_hijos->get_result();
while ($hijo = $result_hijos->fetch_assoc()) {
    // Calcular promedio del hijo
    $stmt_prom = $conexion->prepare("
        SELECT AVG(c.calificacion) as promedio
        FROM calificaciones c
        INNER JOIN evaluaciones e ON c.evaluacion_id = e.id
        WHERE c.alumno_id = ?
    ");
    $stmt_prom->bind_param('i', $hijo['id']);
    $stmt_prom->execute();
    $stmt_prom->bind_result($promedio);
    $stmt_prom->fetch();
    $hijo['promedio'] = $promedio !== null ? round($promedio, 1) : '-';
    $stmt_prom->close();
    
    // Calcular porcentaje de asistencia del hijo
    $stmt_asist = $conexion->prepare("
        SELECT 
            COUNT(*) as total_dias,
            SUM(presente) as dias_presente
        FROM asistencia 
        WHERE alumno_id = ?
    ");
    $stmt_asist->bind_param('i', $hijo['id']);
    $stmt_asist->execute();
    $stmt_asist->bind_result($total_dias, $dias_presente);
    $stmt_asist->fetch();
    $hijo['asistencia'] = $total_dias > 0 ? round(($dias_presente / $total_dias) * 100, 1) : '-';
    $stmt_asist->close();
    
    $hijos[] = $hijo;
}
$stmt_hijos->close();

// Si no hay hijos, mostrar mensaje
if (empty($hijos)) {
    $hijos = [];
}


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Padre | Escuela Primaria</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="css/padre_style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="menu-btn" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </div>
    
    <div class="sidebar" id="sidebar">
        <a href="#" onclick="showSection('perfil')" class="active">
            <i class="bi bi-person-circle"></i> Mi Perfil
        </a>
        <a href="#" onclick="showSection('materias')">
            <i class="bi bi-book"></i> Materias y Evaluaciones
        </a>
        <a href="#" onclick="showSection('asistencias')">
            <i class="bi bi-calendar-check"></i> Asistencias
        </a>
        <a href="#" onclick="showSection('estadisticas')">
            <i class="bi bi-graph-up"></i> Estadísticas
        </a>
        <a href="notificaciones.php">
            <i class="bi bi-bell"></i> Notificaciones
        </a>
        <a href="#" onclick="window.location.href='../index.html'" style="margin-top: 20px; color: #dc3545;">
            <i class="bi bi-box-arrow-left"></i> Cerrar Sesión
        </a>
    </div>

    <div class="content" id="mainContent">
        <div class="section active" id="perfil">
            <!-- Perfil del Padre -->
            <div class="card mb-4">
                <h2 class="mb-4 text-primary">
                    <i class="bi bi-person-circle me-2"></i>
                    Mi Perfil
                </h2>
                <div class="row">
                    <div class="col-md-3 text-center">
                        <div class="profile-icon-container mb-3">
                            <i class="bi bi-person-circle profile-icon"></i>
                        </div>
                        <h4 class="text-primary mt-3"><?= htmlspecialchars($nombre_padre . ' ' . $apellido_padre) ?></h4>
                    </div>
                    <div class="col-md-9">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong><i class="bi bi-person me-2"></i>Nombre:</strong> <?= htmlspecialchars($nombre_padre) ?></p>
                                <p><strong><i class="bi bi-person me-2"></i>Apellidos:</strong> <?= htmlspecialchars($apellido_padre) ?></p>
                                <p><strong><i class="bi bi-envelope me-2"></i>Email:</strong> <?= htmlspecialchars($correo_padre) ?></p>
                                <p><strong><i class="bi bi-telephone me-2"></i>Teléfono:</strong> <?= htmlspecialchars($telefono_padre ?: 'No registrado') ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong><i class="bi bi-geo-alt me-2"></i>Dirección:</strong> <?= htmlspecialchars($direccion_padre ?: 'No registrada') ?></p>
                                <p><strong><i class="bi bi-person-heart me-2"></i>Parentesco:</strong> <?= htmlspecialchars($parentesco_padre ?: 'No especificado') ?></p>
                                <p><strong><i class="bi bi-briefcase me-2"></i>Profesión:</strong> <?= htmlspecialchars($profesion_padre ?: 'No especificada') ?></p>
                                <p><strong><i class="bi bi-envelope-at me-2"></i>Email Alternativo:</strong> <?= htmlspecialchars($correo_alt_padre ?: 'No registrado') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hijos Inscritos -->
            <div class="card">
                <h2 class="mb-4 text-primary">
                    <i class="bi bi-people me-2"></i>
                    Mis Hijos Inscritos
                </h2>
                <?php if (!empty($hijos)): ?>
                    <div class="row">
                        <?php foreach ($hijos as $index => $hijo): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card border-primary">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 text-center">
                                                <div class="profile-icon-container mb-3">
                                                    <i class="bi bi-person-circle profile-icon"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <h5 class="card-title"><?= htmlspecialchars($hijo['nombre'] . ' ' . $hijo['apellido']) ?></h5>
                                                <p class="card-text">
                                                    <strong><i class="bi bi-people me-2"></i>Grupo:</strong> <?= htmlspecialchars($hijo['grupo_nombre'] ?: 'No asignado') ?><br>
                                                    <strong><i class="bi bi-star me-2"></i>Promedio:</strong> <?= $hijo['promedio'] !== '-' ? $hijo['promedio'] : 'Sin calificaciones' ?><br>
                                                    <strong><i class="bi bi-calendar-check me-2"></i>Asistencia:</strong> <?= $hijo['asistencia'] !== '-' ? $hijo['asistencia'] . '%' : 'Sin registro' ?>
                                                </p>
                                                <button class="btn btn-primary btn-sm" onclick="showSection('hijo<?= $index + 1 ?>')">Ver Detalles</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>No tienes hijos registrados en el sistema.</strong> Contacta al administrador para registrar a tus hijos.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="section" id="materias">
            <div class="card">
                <h2 class="mb-4 text-primary">
                    <i class="bi bi-book me-2"></i>
                    Materias y Evaluaciones por Hijo
                </h2>
                
                <?php if (!empty($hijos)): ?>
                    <?php foreach ($hijos as $index => $hijo): ?>
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-person-circle me-2"></i>
                                    <?= htmlspecialchars($hijo['nombre'] . ' ' . $hijo['apellido']) ?> - <?= htmlspecialchars($hijo['grupo_nombre'] ?: 'Sin grupo asignado') ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php
                                // Obtener materias del grupo del hijo con evaluaciones
                                $materias_hijo = [];
                                if ($hijo['grupo_nombre']) {
                                    $stmt_materias = $conexion->prepare("
                                        SELECT m.id, m.nombre, m.descripcion
                                        FROM materias m
                                        INNER JOIN grupo_materias gm ON m.id = gm.materia_id
                                        INNER JOIN grupos g ON gm.grupo_id = g.id
                                        WHERE g.nombre = ?
                                        ORDER BY m.nombre
                                    ");
                                    $stmt_materias->bind_param('s', $hijo['grupo_nombre']);
                                    $stmt_materias->execute();
                                    $result_materias = $stmt_materias->get_result();
                                    
                                    while ($materia = $result_materias->fetch_assoc()) {
                                        // Obtener total de evaluaciones para esta materia y grupo
                                        $stmt_eval_total = $conexion->prepare("
                                            SELECT COUNT(*) as total_evaluaciones
                                            FROM evaluaciones e
                                            INNER JOIN grupos g ON e.grupo_id = g.id
                                            WHERE e.materia_id = ? AND g.nombre = ?
                                        ");
                                        $stmt_eval_total->bind_param('is', $materia['id'], $hijo['grupo_nombre']);
                                        $stmt_eval_total->execute();
                                        $stmt_eval_total->bind_result($total_evaluaciones);
                                        $stmt_eval_total->fetch();
                                        $stmt_eval_total->close();
                                        
                                        // Obtener evaluaciones completadas por el alumno
                                        $stmt_eval_completadas = $conexion->prepare("
                                            SELECT COUNT(DISTINCT e.id) as evaluaciones_completadas
                                            FROM evaluaciones e
                                            INNER JOIN grupos g ON e.grupo_id = g.id
                                            INNER JOIN calificaciones c ON e.id = c.evaluacion_id
                                            WHERE e.materia_id = ? AND g.nombre = ? AND c.alumno_id = ?
                                        ");
                                        $stmt_eval_completadas->bind_param('isi', $materia['id'], $hijo['grupo_nombre'], $hijo['id']);
                                        $stmt_eval_completadas->execute();
                                        $stmt_eval_completadas->bind_result($evaluaciones_completadas);
                                        $stmt_eval_completadas->fetch();
                                        $stmt_eval_completadas->close();
                                        
                                        // Calcular porcentaje
                                        $porcentaje = $total_evaluaciones > 0 ? round(($evaluaciones_completadas / $total_evaluaciones) * 100, 1) : 0;
                                        
                                        $materia['total_evaluaciones'] = $total_evaluaciones;
                                        $materia['evaluaciones_completadas'] = $evaluaciones_completadas;
                                        $materia['porcentaje'] = $porcentaje;
                                        
                                        $materias_hijo[] = $materia;
                                    }
                                    $stmt_materias->close();
                                }
                                ?>
                                
                                <?php if (!empty($materias_hijo)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Materia</th>
                                                    <th>Descripción</th>
                                                    <th>Total Evaluaciones</th>
                                                    <th>Evaluaciones Completadas</th>
                                                    <th>Porcentaje</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($materias_hijo as $materia): ?>
                                                <tr>
                                                    <td><strong><?= htmlspecialchars($materia['nombre']) ?></strong></td>
                                                    <td><?= htmlspecialchars($materia['descripcion'] ?: 'Sin descripción') ?></td>
                                                    <td><?= $materia['total_evaluaciones'] ?></td>
                                                    <td><?= $materia['evaluaciones_completadas'] ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= $materia['porcentaje'] >= 80 ? 'success' : ($materia['porcentaje'] >= 60 ? 'warning' : 'danger') ?>">
                                                            <?= $materia['porcentaje'] ?>%
                                                        </span>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <!-- Resumen del hijo -->
                                    <div class="row mt-3">
                                        <div class="col-md-4">
                                            <div class="card bg-light">
                                                <div class="card-body text-center">
                                                    <h6 class="card-title">Total de Evaluaciones</h6>
                                                    <h4 class="text-primary"><?= array_sum(array_column($materias_hijo, 'total_evaluaciones')) ?></h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card bg-light">
                                                <div class="card-body text-center">
                                                    <h6 class="card-title">Evaluaciones Completadas</h6>
                                                    <h4 class="text-success"><?= array_sum(array_column($materias_hijo, 'evaluaciones_completadas')) ?></h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card bg-light">
                                                <div class="card-body text-center">
                                                    <h6 class="card-title">Promedio de Cumplimiento</h6>
                                                    <h4 class="text-info"><?= round(array_sum(array_column($materias_hijo, 'porcentaje')) / count($materias_hijo), 1) ?>%</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <strong>No hay materias asignadas al grupo de <?= htmlspecialchars($hijo['nombre']) ?>.</strong>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>No tienes hijos registrados en el sistema.</strong> Contacta al administrador para registrar a tus hijos.
                    </div>
                <?php endif; ?>
            </div>
        </div>



        <div class="section" id="asistencias">
            <div class="card">
                <h2 class="mb-4 text-primary">
                    <i class="bi bi-calendar-check me-2"></i>
                    Asistencias
                </h2>
                
                <?php if (!empty($hijos)): ?>
                    <!-- Selector de hijo -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="selectorHijo" class="form-label"><strong>Seleccionar Hijo:</strong></label>
                            <select class="form-select" id="selectorHijo" onchange="cambiarHijo()">
                                <?php foreach ($hijos as $index => $hijo): ?>
                                    <option value="<?= $hijo['id'] ?>" <?= $index === 0 ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($hijo['nombre'] . ' ' . $hijo['apellido']) ?> - <?= htmlspecialchars($hijo['grupo_nombre'] ?: 'Sin grupo') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Hijo seleccionado:</strong> <span id="hijoSeleccionado"><?= !empty($hijos) ? htmlspecialchars($hijos[0]['nombre'] . ' ' . $hijos[0]['apellido']) : 'Ninguno' ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Controles del calendario -->
                    <div class="calendar-nav">
                        <button onclick="cambiarMes(-1)">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <span id="mesActual"></span>
                        <button onclick="cambiarMes(1)">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                    
                    <!-- Leyenda -->
                    <div class="leyenda">
                        <span><div class="cuadro verde"></div> Asistió</span>
                        <span><div class="cuadro rojo"></div> Faltó</span>
                        <span><div class="cuadro gris"></div> Sin registro</span>
                    </div>
                    
                    <!-- Calendario -->
                    <div id="calendario" class="card"></div>
                    
                    <!-- Estadísticas de asistencia -->
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Días Asistidos</h6>
                                    <h4 id="diasAsistidos">0</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Faltas</h6>
                                    <h4 id="faltas">0</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Porcentaje</h6>
                                    <h4 id="porcentajeAsistencia">0%</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-secondary text-white">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Total Días</h6>
                                    <h4 id="totalDias">0</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>No tienes hijos registrados en el sistema.</strong> Contacta al administrador para registrar a tus hijos.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="section" id="estadisticas">
            <div class="card">
                <h2 class="mb-4 text-primary">
                    <i class="bi bi-graph-up me-2"></i>
                    Estadísticas Académicas por Hijo
                </h2>
                
                <?php if (!empty($hijos)): ?>
                    <?php foreach ($hijos as $index => $hijo): ?>
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-person-circle me-2"></i>
                                    <?= htmlspecialchars($hijo['nombre'] . ' ' . $hijo['apellido']) ?> - <?= htmlspecialchars($hijo['grupo_nombre'] ?: 'Sin grupo asignado') ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Promedio Evaluaciones</h6>
                                                <h4 class="text-primary"><?= $hijo['promedio'] !== '-' ? $hijo['promedio'] : 'N/A' ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Asistencia</h6>
                                                <h4 class="text-success"><?= $hijo['asistencia'] !== '-' ? $hijo['asistencia'] . '%' : 'N/A' ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Desempeño General</h6>
                                                <h4 class="text-info">
                                                    <?php
                                                    if ($hijo['promedio'] !== '-' && $hijo['asistencia'] !== '-') {
                                                        $promedio_num = floatval($hijo['promedio']);
                                                        $asistencia_num = floatval($hijo['asistencia']);
                                                        $desempeno_general = round(($promedio_num + $asistencia_num) / 2, 1);
                                                        echo $desempeno_general . '%';
                                                    } else {
                                                        echo 'N/A';
                                                    }
                                                    ?>
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-8">
                                        <canvas id="grafica<?= $hijo['id'] ?>"></canvas>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Detalles</h6>
                                            </div>
                                            <div class="card-body">
                                                <p><strong>Evaluaciones:</strong> <?= $hijo['promedio'] !== '-' ? 'Completadas' : 'Sin datos' ?></p>
                                                <p><strong>Asistencias:</strong> <?= $hijo['asistencia'] !== '-' ? 'Registradas' : 'Sin datos' ?></p>
                                                <p><strong>Estado:</strong> 
                                                    <?php
                                                    if ($hijo['promedio'] !== '-' && $hijo['asistencia'] !== '-') {
                                                        $promedio_num = floatval($hijo['promedio']);
                                                        $asistencia_num = floatval($hijo['asistencia']);
                                                        $desempeno = ($promedio_num + $asistencia_num) / 2;
                                                        if ($desempeno >= 9) {
                                                            echo '<span class="badge bg-success">Excelente</span>';
                                                        } elseif ($desempeno >= 8) {
                                                            echo '<span class="badge bg-warning">Bueno</span>';
                                                        } else {
                                                            echo '<span class="badge bg-danger">Necesita mejorar</span>';
                                                        }
                                                    } else {
                                                        echo '<span class="badge bg-secondary">Sin datos</span>';
                                                    }
                                                    ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>No tienes hijos registrados en el sistema.</strong> Contacta al administrador para registrar a tus hijos.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php foreach ($hijos as $index => $hijo): ?>
        <!-- Sección Hijo <?= $index + 1 ?> - <?= htmlspecialchars($hijo['nombre']) ?> -->
        <div class="section" id="hijo<?= $index + 1 ?>">
            <div class="card mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="text-primary mb-0">
                        <i class="bi bi-person-circle me-2"></i>
                        <?= htmlspecialchars($hijo['nombre'] . ' ' . $hijo['apellido']) ?> - <?= htmlspecialchars($hijo['grupo_nombre'] ?: 'Sin grupo asignado') ?>
                    </h2>
                    <button class="btn btn-outline-primary" onclick="showSection('perfil')">
                        <i class="bi bi-arrow-left me-2"></i>Volver al Perfil
                    </button>
                </div>
                <div class="text-center mb-4">
                    <div class="profile-icon-container mb-3">
                        <i class="bi bi-person-circle profile-icon"></i>
                    </div>
                    <h4 class="text-primary"><?= htmlspecialchars($hijo['nombre'] . ' ' . $hijo['apellido']) ?></h4>
                    <p class="text-muted">
                        <?= htmlspecialchars($hijo['grupo_nombre'] ?: 'Sin grupo asignado') ?> | 
                        Promedio: <?= $hijo['promedio'] !== '-' ? $hijo['promedio'] : 'Sin calificaciones' ?> | 
                        Asistencia: <?= $hijo['asistencia'] !== '-' ? $hijo['asistencia'] . '%' : 'Sin registro' ?>
                    </p>
                </div>
                
                <!-- Información Académica -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="bi bi-book me-2"></i>Materias y Calificaciones</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                // Obtener calificaciones por materia del hijo
                                $stmt_calif = $conexion->prepare("
                                    SELECT m.nombre as materia, AVG(c.calificacion) as promedio
                                    FROM calificaciones c
                                    INNER JOIN evaluaciones e ON c.evaluacion_id = e.id
                                    INNER JOIN materias m ON e.materia_id = m.id
                                    WHERE c.alumno_id = ?
                                    GROUP BY m.id, m.nombre
                                    ORDER BY m.nombre
                                ");
                                $stmt_calif->bind_param('i', $hijo['id']);
                                $stmt_calif->execute();
                                $result_calif = $stmt_calif->get_result();
                                
                                if ($result_calif->num_rows > 0):
                                    while ($calif = $result_calif->fetch_assoc()):
                                        $promedio_materia = round($calif['promedio'], 1);
                                        $badge_class = $promedio_materia >= 9 ? 'success' : ($promedio_materia >= 8 ? 'warning' : 'danger');
                                ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span><?= htmlspecialchars($calif['materia']) ?></span>
                                    <span class="badge bg-<?= $badge_class ?>"><?= $promedio_materia ?></span>
                                </div>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                <div class="text-center text-muted">
                                    <i class="bi bi-info-circle me-2"></i>
                                    No hay calificaciones registradas para este alumno.
                                </div>
                                <?php endif; ?>
                                <?php $stmt_calif->close(); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Asistencia del Mes</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                // Obtener estadísticas de asistencia del mes actual
                                $mes_actual = date('Y-m');
                                $stmt_asist_mes = $conexion->prepare("
                                    SELECT 
                                        COUNT(*) as total_dias,
                                        SUM(presente) as dias_presente
                                    FROM asistencia 
                                    WHERE alumno_id = ? AND DATE_FORMAT(fecha, '%Y-%m') = ?
                                ");
                                $stmt_asist_mes->bind_param('is', $hijo['id'], $mes_actual);
                                $stmt_asist_mes->execute();
                                $stmt_asist_mes->bind_result($total_dias_mes, $dias_presente_mes);
                                $stmt_asist_mes->fetch();
                                $stmt_asist_mes->close();
                                
                                $faltas_mes = $total_dias_mes - $dias_presente_mes;
                                $porcentaje_mes = $total_dias_mes > 0 ? round(($dias_presente_mes / $total_dias_mes) * 100, 1) : 0;
                                ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Días Asistidos</span>
                                    <span class="badge bg-success"><?= $dias_presente_mes ?>/<?= $total_dias_mes ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Faltas</span>
                                    <span class="badge bg-danger"><?= $faltas_mes ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Porcentaje</span>
                                    <span class="badge bg-info"><?= $porcentaje_mes ?>%</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Mes</span>
                                    <span class="badge bg-secondary"><?= date('F Y') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentMonth = new Date().getMonth();
        let currentYear = new Date().getFullYear();
        let currentAlumnoId = <?= !empty($hijos) ? $hijos[0]['id'] : 'null' ?>;
        let asistenciasData = { asistencias: [], faltas: [] };

        const calendario = document.getElementById('calendario');
        const mesActual = document.getElementById('mesActual');

        // Función para cargar asistencias del alumno seleccionado
        async function cargarAsistencias(alumnoId) {
            try {
                const response = await fetch(`obtener_asistencias.php?alumno_id=${alumnoId}`);
                const data = await response.json();
                
                if (data.error) {
                    console.error('Error:', data.error);
                    return;
                }
                
                asistenciasData = data;
                actualizarEstadisticas(data);
                generarCalendario(currentMonth, currentYear);
            } catch (error) {
                console.error('Error al cargar asistencias:', error);
            }
        }

        // Función para cambiar de hijo
        function cambiarHijo() {
            const selector = document.getElementById('selectorHijo');
            const hijoSeleccionado = document.getElementById('hijoSeleccionado');
            
            if (selector && hijoSeleccionado) {
                const option = selector.options[selector.selectedIndex];
                
                currentAlumnoId = parseInt(selector.value);
                hijoSeleccionado.textContent = option.text;
                
                cargarAsistencias(currentAlumnoId);
            }
        }

        // Función para actualizar estadísticas
        function actualizarEstadisticas(data) {
            const diasAsistidos = document.getElementById('diasAsistidos');
            const faltas = document.getElementById('faltas');
            const porcentajeAsistencia = document.getElementById('porcentajeAsistencia');
            const totalDias = document.getElementById('totalDias');
            
            if (diasAsistidos) diasAsistidos.textContent = data.dias_asistidos;
            if (faltas) faltas.textContent = data.faltas_count;
            if (porcentajeAsistencia) porcentajeAsistencia.textContent = data.porcentaje + '%';
            if (totalDias) totalDias.textContent = data.total_dias;
        }

        function generarCalendario(mes, año) {
            const diasMes = new Date(año, mes + 1, 0).getDate();
            const primerDia = new Date(año, mes, 1).getDay();

            let html = `<div style="display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; gap: 8px;">`;
            const diasSemana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
            diasSemana.forEach(d => html += `<div style="font-weight:bold; padding: 10px; background: #f8f9fa; border-radius: 6px;">${d}</div>`);

            for (let i = 0; i < primerDia; i++) html += `<div></div>`;

            for (let dia = 1; dia <= diasMes; dia++) {
                const fechaStr = `${año}-${String(mes + 1).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
                let estilo = 'background: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6;';
                
                if (asistenciasData.asistencias.includes(fechaStr)) {
                    estilo = 'background: #198754; color: white; border: 1px solid #198754;';
                } else if (asistenciasData.faltas.includes(fechaStr)) {
                    estilo = 'background: #dc3545; color: white; border: 1px solid #dc3545;';
                }
                
                html += `<div style="padding: 12px; border-radius: 8px; ${estilo}; font-weight: 500;">${dia}</div>`;
            }

            html += '</div>';
            calendario.innerHTML = html;
            mesActual.textContent = new Date(año, mes).toLocaleString('es', { month: 'long', year: 'numeric' });
        }

        function cambiarMes(delta) {
            currentMonth += delta;
            if (currentMonth > 11) { currentMonth = 0; currentYear++; }
            else if (currentMonth < 0) { currentMonth = 11; currentYear--; }
            generarCalendario(currentMonth, currentYear);
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            if (sidebar && mainContent) {
                sidebar.classList.toggle('active');
                mainContent.classList.toggle('shift');
            }
        }

        function showSection(id) {
            const sections = document.querySelectorAll('.section');
            const targetSection = document.getElementById(id);
            const sidebarLinks = document.querySelectorAll('.sidebar a');
            
            if (sections && targetSection) {
                sections.forEach(sec => sec.classList.remove('active'));
                targetSection.classList.add('active');
                
                // Marcar enlace activo en sidebar
                sidebarLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('onclick') && link.getAttribute('onclick').includes(id)) {
                        link.classList.add('active');
                    }
                });
            }
        }

        // Cargar asistencias del primer hijo al cargar la página
        if (currentAlumnoId) {
            cargarAsistencias(currentAlumnoId);
        }
        
        // Inicializar calendario
        if (calendario) {
            generarCalendario(currentMonth, currentYear);
        }

        // Crear gráficas individuales para cada hijo
        <?php if (!empty($hijos)): ?>
            <?php foreach ($hijos as $hijo): ?>
                const ctx<?= $hijo['id'] ?> = document.getElementById('grafica<?= $hijo['id'] ?>');
                if (ctx<?= $hijo['id'] ?>) {
                    new Chart(ctx<?= $hijo['id'] ?>, {
                        type: 'bar',
                        data: {
                            labels: ['Evaluaciones', 'Asistencias'],
                            datasets: [{
                                label: 'Desempeño (%)',
                                data: [
                                    <?= $hijo['promedio'] !== '-' ? round($hijo['promedio'] * 10, 1) : 0 ?>, 
                                    <?= $hijo['asistencia'] !== '-' ? round($hijo['asistencia'], 1) : 0 ?>
                                ],
                                backgroundColor: ['#0d6efd', '#198754'],
                                borderColor: ['#0b5ed7', '#146c43'],
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: { 
                                    beginAtZero: true, 
                                    max: 100,
                                    ticks: {
                                        callback: function(value) {
                                            return value + '%';
                                        }
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                title: {
                                    display: true,
                                    text: '<?= htmlspecialchars($hijo['nombre'] . ' ' . $hijo['apellido']) ?>'
                                }
                            }
                        }
                    });
                }
            <?php endforeach; ?>
        <?php endif; ?>
    </script>
</body>
</html>
 