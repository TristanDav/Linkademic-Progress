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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        
        .profile-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid #0d6efd;
            object-fit: cover;
            display: block;
            margin: 0 auto 20px;
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
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
        
        .calendar-nav {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .calendar-nav button {
            background: #0d6efd;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .calendar-nav button:hover {
            background: #0b5ed7;
        }
        
        .calendar-nav span {
            font-weight: 600;
            color: #495057;
            font-size: 1.1rem;
        }
        
        .leyenda {
            display: flex;
            gap: 20px;
            margin: 15px 0;
            justify-content: center;
        }
        
        .leyenda span {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }
        
        .cuadro {
            width: 20px;
            height: 20px;
            border-radius: 4px;
        }
        
        .verde { background: #198754; }
        .rojo { background: #dc3545; }
        
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
    </style>
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
            <div class="card">
                <h2 class="mb-4 text-primary">
                    <i class="bi bi-person-circle me-2"></i>
                    Perfil del Estudiante
                </h2>
                <div class="text-center">
                    <img src="https://via.placeholder.com/120x120/0d6efd/ffffff?text=HP" alt="Foto del alumno" class="profile-img">
                    <h4 class="text-primary mb-3">Hugo Pablimix Chavez</h4>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong><i class="bi bi-person me-2"></i>Edad:</strong> 10 años</p>
                        <p><strong><i class="bi bi-mortarboard me-2"></i>Grado:</strong> 5to</p>
                        <p><strong><i class="bi bi-people me-2"></i>Grupo:</strong> A</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong><i class="bi bi-building me-2"></i>Salón:</strong> A-202</p>
                        <p><strong><i class="bi bi-person-heart me-2"></i>Tutor:</strong> Mami Luisa</p>
                        <p><strong><i class="bi bi-person-badge me-2"></i>Maestro:</strong> Prof. Luis García</p>
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
