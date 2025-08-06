<?php
require_once '../auth_padre.php';
$usuario_id = $_SESSION['padre_id'];

// Obtener el id de la tabla padres basado en el usuario_id
$sql_padre = "SELECT p.id FROM padres p WHERE p.usuario_id = ?";
$stmt_padre = $conexion->prepare($sql_padre);
$stmt_padre->bind_param("i", $usuario_id);
$stmt_padre->execute();
$resultado_padre = $stmt_padre->get_result();
$padre_data = $resultado_padre->fetch_assoc();
$stmt_padre->close();

if (!$padre_data) {
    // Si no existe en la tabla padres, redirigir
    header('Location: ../login_padres.php');
    exit;
}

$padre_id = $padre_data['id'];

// Marcar notificaciones como leídas si se accede con ?leidas=1
if (isset($_GET['leidas']) && $_GET['leidas'] == 1) {
    $sqlUpdate = "UPDATE notificaciones SET leido = 1 WHERE id_padre = ?";
    $stmtUpdate = $conexion->prepare($sqlUpdate);
    $stmtUpdate->bind_param("i", $padre_id);
    $stmtUpdate->execute();
    $stmtUpdate->close();
}

// Obtener notificaciones del padre
$sql = "SELECT id, titulo, mensaje, fecha, leido FROM notificaciones WHERE id_padre = ? ORDER BY fecha DESC";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $padre_id);
$stmt->execute();
$resultado = $stmt->get_result();
$notificaciones = $resultado->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones - Sistema Escolar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/padre_style.css" rel="stylesheet">
    <link href="css/notificaciones_style.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="panel_padre.php">
                <i class="bi bi-arrow-left me-2"></i>
                Volver al Panel
            </a>
            <div class="navbar-nav ms-auto">
                <a href="notificaciones.php" class="nav-link active">
                    <i class="bi bi-bell-fill me-1"></i>
                    Notificaciones
                </a>
                <a href="../index.html" class="nav-link">
                    <i class="bi bi-box-arrow-right me-1"></i>
                    Cerrar Sesión
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">
                            <i class="bi bi-bell me-2"></i>
                            Notificaciones
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if (count($notificaciones) === 0): ?>
                            <div class="empty-state">
                                <i class="bi bi-bell-slash"></i>
                                <h4>No hay notificaciones</h4>
                                <p>Cuando recibas notificaciones importantes, aparecerán aquí.</p>
                            </div>
                        <?php else: ?>
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="mb-0">
                                    <i class="bi bi-list-ul me-2"></i>
                                    Total: <?= count($notificaciones) ?> notificación<?= count($notificaciones) != 1 ? 'es' : '' ?>
                                </h5>
                                <a href="notificaciones.php?leidas=1" class="btn btn-leer-todo">
                                    <i class="bi bi-check-all me-2"></i>
                                    Marcar todas como leídas
                                </a>
                            </div>
                            
                            <?php foreach ($notificaciones as $notif): ?>
                                <div class="notificacion <?= $notif['leido'] ? 'leido' : 'no-leido' ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h4>
                                                <i class="bi bi-info-circle me-2"></i>
                                                <?= htmlspecialchars($notif['titulo']); ?>
                                            </h4>
                                            <p class="mb-2"><?= nl2br(htmlspecialchars($notif['mensaje'])); ?></p>
                                            <small>
                                                <i class="bi bi-calendar me-1"></i>
                                                <?= date('d/m/Y H:i', strtotime($notif['fecha'])); ?> 
                                                | 
                                                <i class="bi bi-circle-fill me-1 notification-status-<?= $notif['leido'] ? 'leido' : 'no-leido' ?>"></i>
                                                <?= $notif['leido'] ? 'Leído' : 'No leído'; ?>
                                            </small>
                                        </div>
                                        <?php if (!$notif['leido']): ?>
                                            <span class="badge bg-warning text-dark ms-2">Nuevo</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>