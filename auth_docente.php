<?php
require_once 'security_config.php';
session_start();

// Verificar si el usuario está logueado como docente
if (!isset($_SESSION['usuario_id'])) {
    log_unauthorized_access('vista_docentes', $_SERVER['REMOTE_ADDR']);
    header('Location: ../login_docentes.php');
    exit;
}

// Verificar timeout de sesión
if (!check_session_timeout()) {
    header('Location: ../login_docentes.php?timeout=1');
    exit;
}

// Verificar que el usuario sea realmente un docente
require_once '../conexion.php';
$usuario_id = $_SESSION['usuario_id'];

$stmt = $conexion->prepare("SELECT rol_id FROM usuarios WHERE id = ? AND rol_id = 2");
$stmt->bind_param('i', $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    // Si no es docente, destruir sesión y redirigir
    session_destroy();
    header('Location: ../login_docentes.php');
    exit;
}

$stmt->close();
?> 