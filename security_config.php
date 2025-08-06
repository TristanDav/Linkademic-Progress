<?php
// Configuraciones de seguridad para el sistema

// Configurar headers de seguridad
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Configurar timezone
date_default_timezone_set('America/Mexico_City');

// Configurar parámetros de sesión seguros
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 en producción con HTTPS

// Función para limpiar datos de entrada
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Función para verificar si la sesión ha expirado (30 minutos)
function check_session_timeout() {
    $timeout = 1800; // 30 minutos en segundos
    
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
        session_unset();
        session_destroy();
        return false;
    }
    
    $_SESSION['last_activity'] = time();
    return true;
}

// Función para registrar intentos de acceso no autorizado
function log_unauthorized_access($page, $ip) {
    $log_entry = date('Y-m-d H:i:s') . " - Acceso no autorizado a: $page desde IP: $ip\n";
    $log_file = 'logs/security.log';
    
    // Crear directorio de logs si no existe
    if (!is_dir('logs')) {
        mkdir('logs', 0755, true);
    }
    
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}
?> 