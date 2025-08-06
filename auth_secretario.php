<?php
require_once 'security_config.php';
session_start();

// Verificar si el usuario está logueado como secretario
if (!isset($_SESSION['secretario_id'])) {
    log_unauthorized_access('vista_secretario', $_SERVER['REMOTE_ADDR']);
    header('Location: ../login_secretario.php');
    exit;
}

// Verificar timeout de sesión
if (!check_session_timeout()) {
    header('Location: ../login_secretario.php?timeout=1');
    exit;
}

// Verificar que el usuario sea realmente un secretario
require_once '../conexion.php';
$secretario_id = $_SESSION['secretario_id'];

$stmt = $conexion->prepare("SELECT rol_id FROM usuarios WHERE id = ? AND rol_id = 3");
$stmt->bind_param('i', $secretario_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    // Si no es secretario, destruir sesión y redirigir
    session_destroy();
    header('Location: ../login_secretario.php');
    exit;
}

$stmt->close();
?> 