<?php
session_start();
require_once '../conexion.php';

// Obtener alumnos con grupo y tutor
$sql = "
SELECT a.id, u.usuario, u.nombre, u.apellido, u.correo, a.grado_cursa, g.nombre AS grupo_nombre,
       tu.nombre AS tutor_nombre, tu.apellido AS tutor_apellido
FROM alumnos a
JOIN usuarios u ON a.usuario_id = u.id
LEFT JOIN grupos g ON a.grupo_id = g.id
LEFT JOIN tutores t ON a.tutor_id = t.id
LEFT JOIN usuarios tu ON t.usuario_id = tu.id
ORDER BY u.nombre, u.apellido
";
$res = $conexion->query($sql);
$alumnos = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

// Obtener padres reales de la BD
$sql_padres = "SELECT id, nombre, apellido, correo FROM usuarios WHERE rol_id = 1 ORDER BY nombre, apellido";
$res_padres = $conexion->query($sql_padres);
$padres = $res_padres ? $res_padres->fetch_all(MYSQLI_ASSOC) : [];

// (Opcional) Obtener número de hijos por padre
$hijos_por_padre = [];
$res_hijos = $conexion->query("SELECT padre_id, COUNT(*) as hijos FROM alumnos GROUP BY padre_id");
if ($res_hijos) {
    while ($row = $res_hijos->fetch_assoc()) {
        $hijos_por_padre[$row['padre_id']] = $row['hijos'];
    }
}

// Obtener docentes reales de la BD
$sql_docentes = "SELECT id, usuario, nombre, apellido, correo FROM usuarios WHERE rol_id = 2 ORDER BY apellido, nombre";
$res_docentes = $conexion->query($sql_docentes);
$docentes = $res_docentes ? $res_docentes->fetch_all(MYSQLI_ASSOC) : [];

// Obtener grupos reales de la BD
$sql_grupos = "SELECT g.id, g.nombre, u.nombre AS docente_nombre, u.apellido AS docente_apellido FROM grupos g LEFT JOIN usuarios u ON g.docente_id = u.id ORDER BY g.nombre";
$res_grupos = $conexion->query($sql_grupos);
$grupos = $res_grupos ? $res_grupos->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Secretario | Escuela Primaria</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="css/secretario_style.css">
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
        <a href="#" onclick="showSection('padres')">
            <i class="bi bi-person-heart"></i> Padres
        </a>
        <a href="#" onclick="showSection('docentes')">
            <i class="bi bi-person-badge"></i> Docentes
        </a>
        <a href="#" onclick="showSection('grupos')">
            <i class="bi bi-collection"></i> Grupos
        </a>
        <a href="#" onclick="showSection('materias')">
            <i class="bi bi-book"></i> Materias
        </a>
        <a href="#" onclick="showSection('reportes')">
            <i class="bi bi-graph-up"></i> Reportes
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
                        <h2><i class="bi bi-shield-lock me-2"></i>¡Bienvenido, Secretario!</h2>
                        <p>Administra el sistema escolar completo: alumnos, padres, docentes, grupos y materias desde este panel central.</p>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card stats-card">
                        <h3><?= count($alumnos) ?></h3>
                        <p>Total de Alumnos</p>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="card text-center acceso-panel">
                        <div class="card-body">
                            <i class="bi bi-people-fill display-4 text-primary mb-3"></i>
                            <h5 class="card-title">Alumnos</h5>
                            <p class="card-text">Gestiona el registro de alumnos y sus datos personales.</p>
                            <a href="#" onclick="showSection('alumnos')" class="btn btn-primary">Gestionar Alumnos</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card text-center acceso-panel">
                        <div class="card-body">
                            <i class="bi bi-person-heart-fill display-4 text-success mb-3"></i>
                            <h5 class="card-title">Padres</h5>
                            <p class="card-text">Administra los datos de los padres de familia.</p>
                            <a href="#" onclick="showSection('padres')" class="btn btn-success">Gestionar Padres</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card text-center acceso-panel">
                        <div class="card-body">
                            <i class="bi bi-person-badge-fill display-4 text-warning mb-3"></i>
                            <h5 class="card-title">Docentes</h5>
                            <p class="card-text">Registra y gestiona el personal docente.</p>
                            <a href="#" onclick="showSection('docentes')" class="btn btn-warning text-white">Gestionar Docentes</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card text-center acceso-panel">
                        <div class="card-body">
                            <i class="bi bi-graph-up-arrow display-4 text-info mb-3"></i>
                            <h5 class="card-title">Reportes</h5>
                            <p class="card-text">Genera reportes y estadísticas del sistema.</p>
                            <a href="#" onclick="showSection('reportes')" class="btn btn-info text-white">Ver Reportes</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section" id="alumnos">
            <div class="card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-primary mb-0">
                        <i class="bi bi-people me-2"></i>
                        Gestión de Alumnos
                    </h2>
                    <a href="registrar_alumnos.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>
                        Nuevo Alumno
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Grado</th>
                                <th>Grupo</th>
                                <th>Tutor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alumnos as $alumno): ?>
                            <tr>
                                <td><?= htmlspecialchars($alumno['id']) ?></td>
                                <td><?= htmlspecialchars($alumno['usuario']) ?></td>
                                <td><strong><?= htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido']) ?></strong></td>
                                <td><?= htmlspecialchars($alumno['correo']) ?></td>
                                <td><?= htmlspecialchars($alumno['grado_cursa']) ?></td>
                                <td><?= htmlspecialchars($alumno['grupo_nombre'] ?? 'Sin grupo') ?></td>
                                <td><?= htmlspecialchars(($alumno['tutor_nombre'] ?? '') . ' ' . ($alumno['tutor_apellido'] ?? '')) ?: 'Sin tutor' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="section" id="padres">
            <div class="card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-primary mb-0">
                        <i class="bi bi-person-heart me-2"></i>
                        Gestión de Padres
                    </h2>
                    <a href="registrar_padres.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>
                        Nuevo Padre
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Hijos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($padres as $padre): ?>
                            <tr>
                                <td><?= htmlspecialchars($padre['id']) ?></td>
                                <td><strong><?= htmlspecialchars($padre['nombre'] . ' ' . $padre['apellido']) ?></strong></td>
                                <td><?= htmlspecialchars($padre['correo']) ?></td>
                                <td>
                                    <span class="badge bg-info"><?= $hijos_por_padre[$padre['id']] ?? 0 ?></span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></button>
                                    <button class="btn btn-sm btn-outline-success"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="section" id="docentes">
            <div class="card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-primary mb-0">
                        <i class="bi bi-person-badge me-2"></i>
                        Gestión de Docentes
                    </h2>
                    <a href="registrar_docentes.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>
                        Nuevo Docente
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($docentes as $docente): ?>
                            <tr>
                                <td><?= htmlspecialchars($docente['id']) ?></td>
                                <td><?= htmlspecialchars($docente['usuario']) ?></td>
                                <td><strong><?= htmlspecialchars($docente['nombre'] . ' ' . $docente['apellido']) ?></strong></td>
                                <td><?= htmlspecialchars($docente['correo']) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></button>
                                    <button class="btn btn-sm btn-outline-success"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="section" id="grupos">
            <div class="card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-primary mb-0">
                        <i class="bi bi-collection me-2"></i>
                        Gestión de Grupos
                    </h2>
                    <a href="registrar_grupos.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>
                        Nuevo Grupo
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Docente Asignado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($grupos)): ?>
                                <tr><td colspan="4" class="text-center">No hay grupos registrados.</td></tr>
                            <?php else: foreach ($grupos as $i => $grupo): ?>
                            <tr>
                                <td><?= $i+1 ?></td>
                                <td><?= htmlspecialchars($grupo['nombre']) ?></td>
                                <td><?= htmlspecialchars($grupo['docente_nombre'] . ' ' . $grupo['docente_apellido']) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></button>
                                    <button class="btn btn-sm btn-outline-success"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="section" id="materias">
            <div class="card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-primary mb-0">
                        <i class="bi bi-book me-2"></i>
                        Gestión de Materias
                    </h2>
                    <button class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>
                        Nueva Materia
                    </button>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <h5 class="card-title text-primary">Materias Básicas</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Matemáticas
                                    <span class="badge bg-primary rounded-pill">5 grupos</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Español
                                    <span class="badge bg-primary rounded-pill">5 grupos</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Ciencias Naturales
                                    <span class="badge bg-primary rounded-pill">4 grupos</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Historia
                                    <span class="badge bg-primary rounded-pill">4 grupos</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <h5 class="card-title text-primary">Materias Especiales</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Educación Física
                                    <span class="badge bg-success rounded-pill">6 grupos</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Artes
                                    <span class="badge bg-success rounded-pill">6 grupos</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Inglés
                                    <span class="badge bg-success rounded-pill">5 grupos</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Computación
                                    <span class="badge bg-success rounded-pill">4 grupos</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section" id="reportes">
            <div class="card">
                <h2 class="mb-4 text-primary">
                    <i class="bi bi-graph-up me-2"></i>
                    Reportes y Estadísticas
                </h2>
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card stats-card">
                            <h3><?= count($alumnos) ?></h3>
                            <p>Total Alumnos</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card stats-card">
                            <h3><?= count($padres) ?></h3>
                            <p>Total Padres</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card stats-card">
                            <h3><?= count($docentes) ?></h3>
                            <p>Total Docentes</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card stats-card">
                            <h3><?= count($grupos) ?></h3>
                            <p>Total Grupos</p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <h5 class="card-title text-primary">Reportes Disponibles</h5>
                            <div class="list-group">
                                <a href="#" class="list-group-item list-group-item-action">
                                    <i class="bi bi-file-earmark-text me-2"></i>
                                    Reporte de Alumnos por Grado
                                </a>
                                <a href="#" class="list-group-item list-group-item-action">
                                    <i class="bi bi-file-earmark-text me-2"></i>
                                    Reporte de Asistencias
                                </a>
                                <a href="#" class="list-group-item list-group-item-action">
                                    <i class="bi bi-file-earmark-text me-2"></i>
                                    Reporte de Calificaciones
                                </a>
                                <a href="#" class="list-group-item list-group-item-action">
                                    <i class="bi bi-file-earmark-text me-2"></i>
                                    Reporte de Docentes
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <h5 class="card-title text-primary">Acciones Rápidas</h5>
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary">
                                    <i class="bi bi-download me-2"></i>
                                    Exportar Datos
                                </button>
                                <button class="btn btn-outline-success">
                                    <i class="bi bi-printer me-2"></i>
                                    Imprimir Reportes
                                </button>
                                <button class="btn btn-outline-info">
                                    <i class="bi bi-gear me-2"></i>
                                    Configuración del Sistema
                                </button>
                                <button class="btn btn-outline-warning">
                                    <i class="bi bi-shield-check me-2"></i>
                                    Respaldo de Datos
                                </button>
                            </div>
                        </div>
                    </div>
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