<?php
// Datos de ejemplo para el panel de secretarios
$alumnos = [
    ['id' => 1, 'nombre' => 'Hugo Pablimix Chavez', 'grado' => '5to', 'grupo' => 'A', 'padre' => 'Mami Luisa', 'estado' => 'Activo'],
    ['id' => 2, 'nombre' => 'María González López', 'grado' => '4to', 'grupo' => 'B', 'padre' => 'Juan González', 'estado' => 'Activo'],
    ['id' => 3, 'nombre' => 'Carlos Rodríguez Pérez', 'grado' => '6to', 'grupo' => 'A', 'padre' => 'Ana Rodríguez', 'estado' => 'Inactivo'],
    ['id' => 4, 'nombre' => 'Ana Martínez Silva', 'grado' => '3ro', 'grupo' => 'C', 'padre' => 'Pedro Martínez', 'estado' => 'Activo'],
];

$padres = [
    ['id' => 1, 'nombre' => 'Mami Luisa', 'email' => 'luisa@email.com', 'telefono' => '555-0101', 'hijos' => 1],
    ['id' => 2, 'nombre' => 'Juan González', 'email' => 'juan@email.com', 'telefono' => '555-0102', 'hijos' => 2],
    ['id' => 3, 'nombre' => 'Ana Rodríguez', 'email' => 'ana@email.com', 'telefono' => '555-0103', 'hijos' => 1],
    ['id' => 4, 'nombre' => 'Pedro Martínez', 'email' => 'pedro@email.com', 'telefono' => '555-0104', 'hijos' => 1],
];

$docentes = [
    ['id' => 1, 'nombre' => 'Prof. Luis García', 'materia' => 'Matemáticas', 'grupo' => '5to A', 'estado' => 'Activo'],
    ['id' => 2, 'nombre' => 'Prof. María López', 'materia' => 'Español', 'grupo' => '4to B', 'estado' => 'Activo'],
    ['id' => 3, 'nombre' => 'Prof. Carlos Ruiz', 'materia' => 'Ciencias', 'grupo' => '6to A', 'estado' => 'Activo'],
    ['id' => 4, 'nombre' => 'Prof. Ana Silva', 'materia' => 'Historia', 'grupo' => '3ro C', 'estado' => 'Inactivo'],
];

$grupos = [
    ['grado' => '3ro', 'grupo' => 'A', 'alumnos' => 25, 'docente' => 'Prof. García'],
    ['grado' => '3ro', 'grupo' => 'B', 'alumnos' => 23, 'docente' => 'Prof. López'],
    ['grado' => '4to', 'grupo' => 'A', 'alumnos' => 26, 'docente' => 'Prof. Ruiz'],
    ['grado' => '4to', 'grupo' => 'B', 'alumnos' => 24, 'docente' => 'Prof. Silva'],
    ['grado' => '5to', 'grupo' => 'A', 'alumnos' => 25, 'docente' => 'Prof. García'],
    ['grado' => '6to', 'grupo' => 'A', 'alumnos' => 27, 'docente' => 'Prof. Ruiz'],
];
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
                    <button class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>
                        Nuevo Alumno
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Grado</th>
                                <th>Grupo</th>
                                <th>Padre/Tutor</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alumnos as $alumno): ?>
                            <tr>
                                <td><?= $alumno['id'] ?></td>
                                <td><strong><?= $alumno['nombre'] ?></strong></td>
                                <td><?= $alumno['grado'] ?></td>
                                <td><?= $alumno['grupo'] ?></td>
                                <td><?= $alumno['padre'] ?></td>
                                <td>
                                    <span class="badge bg-<?= $alumno['estado'] == 'Activo' ? 'success' : 'danger' ?>">
                                        <?= $alumno['estado'] ?>
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

        <div class="section" id="padres">
            <div class="card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-primary mb-0">
                        <i class="bi bi-person-heart me-2"></i>
                        Gestión de Padres
                    </h2>
                    <button class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>
                        Nuevo Padre
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Hijos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($padres as $padre): ?>
                            <tr>
                                <td><?= $padre['id'] ?></td>
                                <td><strong><?= $padre['nombre'] ?></strong></td>
                                <td><?= $padre['email'] ?></td>
                                <td><?= $padre['telefono'] ?></td>
                                <td>
                                    <span class="badge bg-info"><?= $padre['hijos'] ?></span>
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

        <div class="section" id="docentes">
            <div class="card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-primary mb-0">
                        <i class="bi bi-person-badge me-2"></i>
                        Gestión de Docentes
                    </h2>
                    <button class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>
                        Nuevo Docente
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Materia</th>
                                <th>Grupo Asignado</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($docentes as $docente): ?>
                            <tr>
                                <td><?= $docente['id'] ?></td>
                                <td><strong><?= $docente['nombre'] ?></strong></td>
                                <td><?= $docente['materia'] ?></td>
                                <td><?= $docente['grupo'] ?></td>
                                <td>
                                    <span class="badge bg-<?= $docente['estado'] == 'Activo' ? 'success' : 'danger' ?>">
                                        <?= $docente['estado'] ?>
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

        <div class="section" id="grupos">
            <div class="card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-primary mb-0">
                        <i class="bi bi-collection me-2"></i>
                        Gestión de Grupos
                    </h2>
                    <button class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>
                        Nuevo Grupo
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Grado</th>
                                <th>Grupo</th>
                                <th>Total Alumnos</th>
                                <th>Docente Asignado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grupos as $grupo): ?>
                            <tr>
                                <td><strong><?= $grupo['grado'] ?></strong></td>
                                <td><?= $grupo['grupo'] ?></td>
                                <td>
                                    <span class="badge bg-info"><?= $grupo['alumnos'] ?></span>
                                </td>
                                <td><?= $grupo['docente'] ?></td>
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