<?php
$materias = [
    ['nombre' => 'Matemáticas', 'total_tareas' => 10, 'tareas_entregadas' => 8],
    ['nombre' => 'Español', 'total_tareas' => 12, 'tareas_entregadas' => 10],
];

$examenes = [
    ['materia' => 'Matemáticas', 'total' => 2, 'calificacion' => 85],
    ['materia' => 'Español', 'total' => 3, 'calificacion' => 90],
];

$asistencias = [
    '2025-02-01' => true,
    '2025-02-02' => false,
    '2025-02-03' => true,
    '2025-02-05' => false,
    '2025-02-07' => true,
    '2025-02-10' => true,
    '2025-02-12' => false,
    '2025-02-15' => true,
    '2025-02-16' => false,
    '2025-02-20' => true,
    '2025-02-23' => false,
    '2025-02-28' => true,
];

function calcularEstadistica($materias, $examenes, $asistencias) {
    $tareas_total = 0;
    $tareas_entregadas = 0;
    foreach ($materias as $m) {
        $tareas_total += $m['total_tareas'];
        $tareas_entregadas += $m['tareas_entregadas'];
    }
    $porcentaje_tareas = $tareas_total > 0 ? ($tareas_entregadas / $tareas_total) * 100 : 0;

    $total_calif = array_sum(array_column($examenes, 'calificacion'));
    $porcentaje_exam = count($examenes) > 0 ? ($total_calif / (count($examenes) * 100)) * 100 : 0;

    $asist_total = count($asistencias);
    $asist_ok = array_sum($asistencias);
    $porcentaje_asist = $asist_total > 0 ? ($asist_ok / $asist_total) * 100 : 0;

    return round(($porcentaje_tareas + $porcentaje_exam + $porcentaje_asist) / 3, 2);
}

$porcentaje_final = calcularEstadistica($materias, $examenes, $asistencias);
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
        <a href="#" onclick="showSection('perfil')">
            <i class="bi bi-person-circle"></i> Perfil del Estudiante
        </a>
        <a href="#" onclick="showSection('materias')">
            <i class="bi bi-book"></i> Materias y Tareas
        </a>
        <a href="#" onclick="showSection('examenes')">
            <i class="bi bi-pencil-square"></i> Exámenes
        </a>
        <a href="#" onclick="showSection('asistencias')">
            <i class="bi bi-calendar-check"></i> Asistencias
        </a>
        <a href="#" onclick="showSection('estadisticas')">
            <i class="bi bi-graph-up"></i> Estadísticas
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
                        <img src="https://via.placeholder.com/150x150/17a2b8/ffffff?text=CR" alt="Foto del Padre" class="profile-img">
                        <h4 class="text-primary mt-3">Carlos Alberto Rodríguez</h4>
                    </div>
                    <div class="col-md-9">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong><i class="bi bi-person me-2"></i>Nombre:</strong> Carlos Alberto</p>
                                <p><strong><i class="bi bi-person me-2"></i>Apellidos:</strong> Rodríguez Martínez</p>
                                <p><strong><i class="bi bi-envelope me-2"></i>Email:</strong> carlos.rodriguez@email.com</p>
                                <p><strong><i class="bi bi-telephone me-2"></i>Teléfono:</strong> (555) 123-4567</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong><i class="bi bi-geo-alt me-2"></i>Dirección:</strong> Av. Principal #123, Col. Centro</p>
                                <p><strong><i class="bi bi-building me-2"></i>Ciudad:</strong> Ciudad de México</p>
                                <p><strong><i class="bi bi-geo me-2"></i>Estado:</strong> CDMX</p>
                                <p><strong><i class="bi bi-mailbox me-2"></i>Código Postal:</strong> 06000</p>
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
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card border-primary">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 text-center">
                                        <img src="https://via.placeholder.com/100x100/17a2b8/ffffff?text=AR" alt="Ana Rodríguez" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                                    </div>
                                    <div class="col-md-8">
                                        <h5 class="card-title">Ana Sofía Rodríguez</h5>
                                        <p class="card-text">
                                            <strong><i class="bi bi-mortarboard me-2"></i>Grado:</strong> 3° Primaria<br>
                                            <strong><i class="bi bi-people me-2"></i>Grupo:</strong> A<br>
                                            <strong><i class="bi bi-star me-2"></i>Promedio:</strong> 9.2<br>
                                            <strong><i class="bi bi-calendar-check me-2"></i>Asistencia:</strong> 95%
                                        </p>
                                        <button class="btn btn-primary btn-sm" onclick="showSection('hijo1')">Ver Detalles</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card border-primary">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 text-center">
                                        <img src="https://via.placeholder.com/100x100/17a2b8/ffffff?text=LR" alt="Luis Rodríguez" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                                    </div>
                                    <div class="col-md-8">
                                        <h5 class="card-title">Luis Miguel Rodríguez</h5>
                                        <p class="card-text">
                                            <strong><i class="bi bi-mortarboard me-2"></i>Grado:</strong> 1° Primaria<br>
                                            <strong><i class="bi bi-people me-2"></i>Grupo:</strong> B<br>
                                            <strong><i class="bi bi-star me-2"></i>Promedio:</strong> 8.8<br>
                                            <strong><i class="bi bi-calendar-check me-2"></i>Asistencia:</strong> 92%
                                        </p>
                                        <button class="btn btn-primary btn-sm" onclick="showSection('hijo2')">Ver Detalles</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section" id="materias">
            <div class="card">
                <h2 class="mb-4 text-primary">
                    <i class="bi bi-book me-2"></i>
                    Materias y Tareas
                </h2>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Materia</th>
                                <th>Total Tareas</th>
                                <th>Tareas Entregadas</th>
                                <th>Porcentaje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($materias as $m): ?>
                            <tr>
                                <td><strong><?= $m['nombre'] ?></strong></td>
                                <td><?= $m['total_tareas'] ?></td>
                                <td><?= $m['tareas_entregadas'] ?></td>
                                <td>
                                    <span class="badge bg-<?= ($m['tareas_entregadas']/$m['total_tareas']*100) >= 80 ? 'success' : (($m['tareas_entregadas']/$m['total_tareas']*100) >= 60 ? 'warning' : 'danger') ?>">
                                        <?= round(($m['tareas_entregadas']/$m['total_tareas']*100), 1) ?>%
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="section" id="examenes">
            <div class="card">
                <h2 class="mb-4 text-primary">
                    <i class="bi bi-pencil-square me-2"></i>
                    Exámenes
                </h2>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Materia</th>
                                <th>Exámenes Aplicados</th>
                                <th>Calificación Promedio</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($examenes as $ex): ?>
                            <tr>
                                <td><strong><?= $ex['materia'] ?></strong></td>
                                <td><?= $ex['total'] ?></td>
                                <td><?= $ex['calificacion'] ?>/100</td>
                                <td>
                                    <span class="badge bg-<?= $ex['calificacion'] >= 80 ? 'success' : ($ex['calificacion'] >= 60 ? 'warning' : 'danger') ?>">
                                        <?= $ex['calificacion'] >= 80 ? 'Excelente' : ($ex['calificacion'] >= 60 ? 'Bueno' : 'Necesita mejorar') ?>
                                    </span>
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
                    Asistencias
                </h2>
                <div class="calendar-nav">
                    <button onclick="cambiarMes(-1)">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <span id="mesActual"></span>
                    <button onclick="cambiarMes(1)">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
                <div class="leyenda">
                    <span><div class="cuadro verde"></div> Asistió</span>
                    <span><div class="cuadro rojo"></div> Faltó</span>
                </div>
                <div id="calendario" class="card"></div>
            </div>
        </div>

        <div class="section" id="estadisticas">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card stats-card">
                        <h3><?= $porcentaje_final ?>%</h3>
                        <p>Desempeño General</p>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <h2 class="mb-4 text-primary">
                            <i class="bi bi-graph-up me-2"></i>
                            Estadísticas Académicas
                        </h2>
                        <canvas id="grafica"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección Hijo 1 - Ana Sofía -->
        <div class="section" id="hijo1">
            <div class="card mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="text-primary mb-0">
                        <i class="bi bi-person-circle me-2"></i>
                        Ana Sofía Rodríguez - 3° Primaria Grupo A
                    </h2>
                    <button class="btn btn-outline-primary" onclick="showSection('perfil')">
                        <i class="bi bi-arrow-left me-2"></i>Volver al Perfil
                    </button>
                </div>
                <div class="text-center mb-4">
                    <img src="https://via.placeholder.com/120x120/17a2b8/ffffff?text=AR" alt="Ana Rodríguez" class="profile-img">
                    <h4 class="text-primary">Ana Sofía Rodríguez</h4>
                    <p class="text-muted">3° Primaria - Grupo A | Promedio: 9.2 | Asistencia: 95%</p>
                </div>
                
                <!-- Información Académica -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="bi bi-book me-2"></i>Materias y Calificaciones</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Matemáticas</span>
                                    <span class="badge bg-success">9.5</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Español</span>
                                    <span class="badge bg-success">9.0</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Ciencias</span>
                                    <span class="badge bg-success">9.8</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Historia</span>
                                    <span class="badge bg-success">8.8</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Educación Física</span>
                                    <span class="badge bg-success">9.0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Asistencia del Mes</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Días Asistidos</span>
                                    <span class="badge bg-success">19/20</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Faltas</span>
                                    <span class="badge bg-danger">1</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Justificadas</span>
                                    <span class="badge bg-warning">1</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Porcentaje</span>
                                    <span class="badge bg-info">95%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tareas Pendientes -->
                <div class="card mt-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Tareas Pendientes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Materia</th>
                                        <th>Tarea</th>
                                        <th>Fecha de Entrega</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Matemáticas</strong></td>
                                        <td>Problemas de fracciones</td>
                                        <td>15/12/2024</td>
                                        <td><span class="badge bg-warning">Pendiente</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Ciencias</strong></td>
                                        <td>Experimento del agua</td>
                                        <td>18/12/2024</td>
                                        <td><span class="badge bg-warning">Pendiente</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección Hijo 2 - Luis Miguel -->
        <div class="section" id="hijo2">
            <div class="card mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="text-primary mb-0">
                        <i class="bi bi-person-circle me-2"></i>
                        Luis Miguel Rodríguez - 1° Primaria Grupo B
                    </h2>
                    <button class="btn btn-outline-primary" onclick="showSection('perfil')">
                        <i class="bi bi-arrow-left me-2"></i>Volver al Perfil
                    </button>
                </div>
                <div class="text-center mb-4">
                    <img src="https://via.placeholder.com/120x120/17a2b8/ffffff?text=LR" alt="Luis Rodríguez" class="profile-img">
                    <h4 class="text-primary">Luis Miguel Rodríguez</h4>
                    <p class="text-muted">1° Primaria - Grupo B | Promedio: 8.8 | Asistencia: 92%</p>
                </div>
                
                <!-- Información Académica -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="bi bi-book me-2"></i>Materias y Calificaciones</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Matemáticas</span>
                                    <span class="badge bg-success">8.5</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Español</span>
                                    <span class="badge bg-success">9.0</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Ciencias</span>
                                    <span class="badge bg-success">8.8</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Historia</span>
                                    <span class="badge bg-warning">7.5</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Educación Física</span>
                                    <span class="badge bg-success">9.2</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Asistencia del Mes</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Días Asistidos</span>
                                    <span class="badge bg-success">18/20</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Faltas</span>
                                    <span class="badge bg-danger">2</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Justificadas</span>
                                    <span class="badge bg-warning">1</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Porcentaje</span>
                                    <span class="badge bg-info">92%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tareas Pendientes -->
                <div class="card mt-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Tareas Pendientes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Materia</th>
                                        <th>Tarea</th>
                                        <th>Fecha de Entrega</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Español</strong></td>
                                        <td>Dictado de vocales</td>
                                        <td>16/12/2024</td>
                                        <td><span class="badge bg-warning">Pendiente</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Matemáticas</strong></td>
                                        <td>Sumas hasta 10</td>
                                        <td>17/12/2024</td>
                                        <td><span class="badge bg-warning">Pendiente</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Ciencias</strong></td>
                                        <td>Dibujo de animales</td>
                                        <td>19/12/2024</td>
                                        <td><span class="badge bg-warning">Pendiente</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentMonth = new Date().getMonth();
        let currentYear = new Date().getFullYear();

        const asistencias = <?= json_encode(array_keys(array_filter($asistencias))) ?>;
        const faltas = <?= json_encode(array_keys(array_filter($asistencias, fn($a) => !$a))) ?>;
        const calendario = document.getElementById('calendario');
        const mesActual = document.getElementById('mesActual');

        function generarCalendario(mes, año) {
            const diasMes = new Date(año, mes + 1, 0).getDate();
            const primerDia = new Date(año, mes, 1).getDay();
            const fechaHoy = new Date();

            let html = `<div style="display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; gap: 8px;">`;
            const diasSemana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
            diasSemana.forEach(d => html += `<div style="font-weight:bold; padding: 10px; background: #f8f9fa; border-radius: 6px;">${d}</div>`);

            for (let i = 0; i < primerDia; i++) html += `<div></div>`;

            for (let dia = 1; dia <= diasMes; dia++) {
                const fechaStr = `${año}-${String(mes + 1).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
                let estilo = 'background: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6;';
                
                if (asistencias.includes(fechaStr)) {
                    estilo = 'background: #198754; color: white; border: 1px solid #198754;';
                } else if (faltas.includes(fechaStr)) {
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
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('mainContent').classList.toggle('shift');
        }

        function showSection(id) {
            document.querySelectorAll('.section').forEach(sec => sec.classList.remove('active'));
            document.getElementById(id).classList.add('active');
        }

        generarCalendario(currentMonth, currentYear);

        new Chart(document.getElementById('grafica'), {
            type: 'bar',
            data: {
                labels: ['Tareas', 'Exámenes', 'Asistencias'],
                datasets: [{
                    label: 'Desempeño (%)',
                    data: [
                        <?= round(($materias[0]['tareas_entregadas'] + $materias[1]['tareas_entregadas']) / ($materias[0]['total_tareas'] + $materias[1]['total_tareas']) * 100, 2) ?>,
                        <?= round(array_sum(array_column($examenes, 'calificacion')) / (count($examenes) * 100) * 100, 2) ?>,
                        <?= round(array_sum($asistencias) / count($asistencias) * 100, 2) ?>
                    ],
                    backgroundColor: ['#0d6efd', '#198754', '#ffc107'],
                    borderColor: ['#0b5ed7', '#146c43', '#e0a800'],
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
                    }
                }
            }
        });
    </script>
</body>
</html>
 