<?php
require_once '../auth_secretario.php';

// Obtener alumnos con grupo y tutor
$sql = "SELECT a.id, a.nombre, a.apellido, a.fecha_nacimiento, g.nombre AS grupo, 
               u.nombre AS padre_nombre, u.apellido AS padre_apellido
        FROM alumnos a
        LEFT JOIN grupos g ON a.grupo_id = g.id
        LEFT JOIN usuarios u ON a.padre_id = u.id
        ORDER BY a.apellido, a.nombre";
$res = $conexion->query($sql);
$alumnos = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

// Obtener padres reales de la BD

$sql_padres = "SELECT u.id, u.nombre, u.apellido, u.correo, p.telefono, p.direccion, p.parentesco, p.profesion, p.correo_alternativo, p.observaciones
FROM usuarios u
LEFT JOIN padres p ON u.id = p.usuario_id
WHERE u.rol_id = 1
ORDER BY u.nombre, u.apellido";
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

// Obtener materias reales de la BD
$sql_materias = "SELECT * FROM materias ORDER BY nombre";
$res_materias = $conexion->query($sql_materias);
$materias = $res_materias ? $res_materias->fetch_all(MYSQLI_ASSOC) : [];
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
        <a href="#" onclick="showSection('notificaciones')">
            <i class="bi bi-bell"></i> Notificaciones
        </a>
        <a href="../logout.php" style="margin-top: 20px; color: #dc3545;">
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
                            <i class="bi bi-bell-fill display-4 text-info mb-3"></i>
                            <h5 class="card-title">Notificaciones</h5>
                            <p class="card-text">Gestiona las comunicaciones con los padres.</p>
                            <a href="#" onclick="showSection('notificaciones')" class="btn btn-info text-white">Ver Notificaciones</a>
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
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Fecha de Nacimiento</th>
                                <th>Grupo</th>
                                <th>Padre</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alumnos as $alumno): ?>
                            <tr>
                                <td><?= htmlspecialchars($alumno['id']) ?></td>
                                <td><strong><?= htmlspecialchars($alumno['nombre']) ?></strong></td>
                                <td><?= htmlspecialchars($alumno['apellido']) ?></td>
                                <td><?= htmlspecialchars($alumno['fecha_nacimiento']) ?></td>
                                <td><?= htmlspecialchars($alumno['grupo']) ?></td>
                                <td><?= htmlspecialchars(($alumno['padre_nombre'] ?? '') . ' ' . ($alumno['padre_apellido'] ?? '')) ?: 'Sin padre' ?></td>
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
                                <th>Teléfono</th>
                                <th>Dirección</th>
                                <th>Parentesco</th>
                                <th>Profesión</th>
                                <th>Correo alternativo</th>
                                <th>Observaciones</th>
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
                                <td><?= htmlspecialchars($padre['telefono']) ?></td>
                                <td><?= htmlspecialchars($padre['direccion']) ?></td>
                                <td><?= htmlspecialchars($padre['parentesco']) ?></td>
                                <td><?= htmlspecialchars($padre['profesion']) ?></td>
                                <td><?= htmlspecialchars($padre['correo_alternativo']) ?></td>
                                <td><?= htmlspecialchars($padre['observaciones']) ?></td>
                                <td><span class="badge bg-info"><?= $hijos_por_padre[$padre['id']] ?? 0 ?></span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-success" onclick="editarPadre(<?= $padre['id'] ?>)" title="Editar padre">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="eliminarPadre(<?= $padre['id'] ?>, '<?= htmlspecialchars($padre['nombre'] . ' ' . $padre['apellido']) ?>')" title="Eliminar padre">
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
                                    <button class="btn btn-sm btn-outline-success" onclick="editarDocente(<?= $docente['id'] ?>)" title="Editar docente">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="eliminarDocente(<?= $docente['id'] ?>, '<?= htmlspecialchars($docente['nombre'] . ' ' . $docente['apellido']) ?>')" title="Eliminar docente">
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
                                    <button class="btn btn-sm btn-outline-success" onclick="editarGrupo(<?= $grupo['id'] ?>)" title="Editar grupo">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="eliminarGrupo(<?= $grupo['id'] ?>, '<?= htmlspecialchars($grupo['nombre']) ?>')" title="Eliminar grupo">
                                        <i class="bi bi-trash"></i>
                                    </button>
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
                    <a href="registrar_materias.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>
                        Nueva Materia
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                <?php if (empty($materias)): ?>
                    <tr><td colspan="4" class="text-center">No hay materias registradas.</td></tr>
                <?php else: foreach ($materias as $i => $m): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td><?= htmlspecialchars($m['nombre']) ?></td>
                        <td><?= htmlspecialchars($m['descripcion']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-success" onclick="editarMateria(<?= $m['id'] ?>)" title="Editar materia">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="eliminarMateria(<?= $m['id'] ?>, '<?= htmlspecialchars($m['nombre']) ?>')" title="Eliminar materia">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

        <div class="section" id="notificaciones">
            <div class="card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-primary mb-0">
                        <i class="bi bi-bell me-2"></i>
                        Sistema de Notificaciones
                    </h2>
                    <a href="enviar_notificacion.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>
                        Nueva Notificación
                    </a>
                </div>
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Información del Sistema
                                </h5>
                                <p class="card-text">
                                    Desde aquí puedes enviar notificaciones a los padres de familia. 
                                    Puedes enviar mensajes individuales o masivos según tus necesidades.
                                </p>
                                <ul class="list-unstyled">
                                    <li><i class="bi bi-check-circle text-success me-2"></i>Enviar a padres específicos</li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>Enviar a todos los padres</li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>Notificaciones en tiempo real</li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>Seguimiento de lectura</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="bi bi-bell-fill text-primary notification-icon"></i>
                                <h5 class="mt-3">Notificaciones</h5>
                                <p class="text-muted">Gestiona las comunicaciones con los padres</p>
                                <a href="enviar_notificacion.php" class="btn btn-primary">
                                    <i class="bi bi-send me-2"></i>
                                    Enviar Notificación
                                </a>
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

        // Funciones para editar padres
        function editarPadre(padreId) {
            window.location.href = `registrar_padres.php?editar=${padreId}`;
        }

        function eliminarPadre(padreId, nombrePadre) {
            if (confirm(`¿Estás seguro de que deseas eliminar al padre "${nombrePadre}"?\n\nEsta acción no se puede deshacer.`)) {
                window.location.href = `registrar_padres.php?eliminar=${padreId}`;
            }
        }

        // Funciones para editar docentes
        function editarDocente(docenteId) {
            window.location.href = `registrar_docentes.php?editar=${docenteId}`;
        }

        function eliminarDocente(docenteId, nombreDocente) {
            if (confirm(`¿Estás seguro de que deseas eliminar al docente "${nombreDocente}"?\n\nEsta acción no se puede deshacer.`)) {
                window.location.href = `registrar_docentes.php?eliminar=${docenteId}`;
            }
        }

        // Funciones para editar grupos
        function editarGrupo(grupoId) {
            window.location.href = `registrar_grupos.php?editar=${grupoId}`;
        }

        function eliminarGrupo(grupoId, nombreGrupo) {
            if (confirm(`¿Estás seguro de que deseas eliminar el grupo "${nombreGrupo}"?\n\nEsta acción no se puede deshacer.`)) {
                window.location.href = `registrar_grupos.php?eliminar=${grupoId}`;
            }
        }

        // Funciones para editar materias
        function editarMateria(materiaId) {
            window.location.href = `registrar_materias.php?editar=${materiaId}`;
        }

        function eliminarMateria(materiaId, nombreMateria) {
            if (confirm(`¿Estás seguro de que deseas eliminar la materia "${nombreMateria}"?\n\nEsta acción no se puede deshacer.`)) {
                window.location.href = `registrar_materias.php?eliminar=${materiaId}`;
            }
        }
    </script>
</body>
</html> 