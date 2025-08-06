<?php
require_once 'security_config.php';
session_start();

// Verificar si el usuario está logueado como padre
if (!isset($_SESSION['padre_id'])) {
    log_unauthorized_access('vista_padres', $_SERVER['REMOTE_ADDR']);
    header('Location: ../login_padres.php');
    exit;
}

// Verificar timeout de sesión
if (!check_session_timeout()) {
    header('Location: ../login_padres.php?timeout=1');
    exit;
}

// Verificar que el usuario sea realmente un padre
require_once '../conexion.php';
$padre_id = $_SESSION['padre_id'];

$stmt = $conexion->prepare("SELECT rol_id FROM usuarios WHERE id = ? AND rol_id = 1");
$stmt->bind_param('i', $padre_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    // Si no es padre, destruir sesión y redirigir
    session_destroy();
    header('Location: ../login_padres.php');
    exit;
}

$stmt->close();
?> 