<?php
session_start();
include('conexion.php');

$error = '';

// Mostrar mensaje de timeout si la sesión expiró
if (isset($_GET['timeout'])) {
    $error = 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');

    if ($usuario && $contrasena) {
        // Buscar por usuario o correo, y rol docente (rol_id=2)
        $stmt = $conexion->prepare("SELECT id, usuario, contrasena, nombre, apellido FROM usuarios WHERE (usuario = ? OR correo = ?) AND contrasena = ? AND rol_id = 2 LIMIT 1");
        $stmt->bind_param('sss', $usuario, $usuario, $contrasena);
        $stmt->execute();
        $resultado = $stmt->get_result();
        if ($resultado->num_rows === 1) {
            $datos = $resultado->fetch_assoc();
            $_SESSION['usuario_id'] = $datos['id'];
            $_SESSION['nombre_docente'] = $datos['nombre'];
            $_SESSION['apellido_docente'] = $datos['apellido'];
            header('Location: vista_docentes/panel_docente.php');
            exit;
        } else {
            $error = 'Usuario, correo o contraseña incorrectos.';
        }
        $stmt->close();
    } else {
        $error = 'Por favor, completa todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso para Docentes | Linkademic-Progress</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/styles_logins.css">
</head>
<body class="login-docentes">
    <div class="login-card mx-auto">
        <div class="text-center mb-4">
            <i class="bi bi-person-badge-fill login-icon"></i>
            <h2 class="fw-bold mb-2">Acceso para Docentes</h2>
            <p class="text-muted mb-0">Ingresa tus datos para administrar alumnos y calificaciones</p>
        </div>
        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" id="formLoginDocente" autocomplete="off">
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario o Correo</label>
                <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Ingresa tu usuario o correo" required>
            </div>
            <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="contrasena" name="contrasena" placeholder="Ingresa tu contraseña" required>
            </div>
            <button type="submit" class="btn btn-success">Ingresar</button>
        </form>
        <a href="index.html" class="back-link"><i class="bi bi-arrow-left"></i> Volver al inicio</a>
    </div>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 