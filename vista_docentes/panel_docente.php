<?php
// Datos de ejemplo para el panel de docentes
$alumnos = [
    ['id' => 1, 'nombre' => 'Hugo Pablimix Chavez', 'grado' => '5to', 'grupo' => 'A', 'asistencias' => 85, 'promedio' => 88],
    ['id' => 2, 'nombre' => 'María González López', 'grado' => '5to', 'grupo' => 'A', 'asistencias' => 92, 'promedio' => 95],
    ['id' => 3, 'nombre' => 'Carlos Rodríguez Pérez', 'grado' => '5to', 'grupo' => 'A', 'asistencias' => 78, 'promedio' => 82],
    ['id' => 4, 'nombre' => 'Ana Martínez Silva', 'grado' => '5to', 'grupo' => 'A', 'asistencias' => 90, 'promedio' => 91],
];

$materias = [
    ['nombre' => 'Matemáticas', 'total_alumnos' => 25, 'promedio_grupo' => 87],
    ['nombre' => 'Español', 'total_alumnos' => 25, 'promedio_grupo' => 89],
    ['nombre' => 'Ciencias Naturales', 'total_alumnos' => 25, 'promedio_grupo' => 85],
    ['nombre' => 'Historia', 'total_alumnos' => 25, 'promedio_grupo' => 88],
];

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
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .menu-btn {
            font-size: 24px;
            cursor: pointer;
            padding: 12px 16px;
            background: #0d6efd;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            border: none;
            border-radius: 0 0 8px 0;
            transition: background-color 0.3s ease;
        }
        
        .menu-btn:hover {
            background: #0b5ed7;
        }
        
        .sidebar {
            position: fixed;
            left: -280px;
            width: 280px;
            height: 100%;
            background: #fff;
            transition: left 0.3s ease;
            padding-top: 60px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 999;
        }
        
        .sidebar.active { 
            left: 0; 
        }
        
        .sidebar a {
            display: flex;
            align-items: center;
            padding: 16px 20px;
            color: #495057;
            text-decoration: none;
            border-bottom: 1px solid #e9ecef;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .sidebar a:hover { 
            background: #e7f3ff;
            color: #0d6efd;
            border-left: 4px solid #0d6efd;
        }
        
        .sidebar a i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }
        
        .content {
            margin-left: 0;
            padding: 30px;
            transition: margin-left 0.3s ease;
            min-height: 100vh;
        }
        
        .content.shift { 
            margin-left: 280px; 
        }
        
        .section {
            display: none;
            animation: fadeIn 0.5s ease;
        }
        
        .section.active { 
            display: block; 
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .card {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 20px;
        }
        
        .card:hover { 
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        }
        
        .table {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .table th {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            color: white;
            border: none;
            text-align: center;
        }
        
        .stats-card h3 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .stats-card p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .welcome-card {
            background: linear-gradient(135deg, #198754, #146c43);
            color: white;
            border: none;
        }
        
        .welcome-card h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .welcome-card p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
    </style>
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
                                <th>Grado</th>
                                <th>Grupo</th>
                                <th>Asistencias (%)</th>
                                <th>Promedio</th>
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
                                <td>
                                    <span class="badge bg-<?= $alumno['asistencias'] >= 90 ? 'success' : ($alumno['asistencias'] >= 80 ? 'warning' : 'danger') ?>">
                                        <?= $alumno['asistencias'] ?>%
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $alumno['promedio'] >= 90 ? 'success' : ($alumno['promedio'] >= 80 ? 'warning' : 'danger') ?>">
                                        <?= $alumno['promedio'] ?>
                                    </span>
                                </td>
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
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="section" id="calificaciones">
            <div class="card">
                <h2 class="mb-4 text-primary">
                    <i class="bi bi-clipboard-data me-2"></i>
                    Calificaciones por Materia
                </h2>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Materia</th>
                                <th>Total Alumnos</th>
                                <th>Promedio del Grupo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($materias as $materia): ?>
                            <tr>
                                <td><strong><?= $materia['nombre'] ?></strong></td>
                                <td><?= $materia['total_alumnos'] ?></td>
                                <td>
                                    <span class="badge bg-<?= $materia['promedio_grupo'] >= 90 ? 'success' : ($materia['promedio_grupo'] >= 80 ? 'warning' : 'danger') ?>">
                                        <?= $materia['promedio_grupo'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-success">Actualizado</span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary">
                                        <i class="bi bi-plus-circle"></i> Registrar
                                    </button>
                                    <button class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-eye"></i> Ver
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
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
                                <button class="btn btn-success btn-lg">
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