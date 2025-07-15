<?php
session_start();
require_once '../conexion.php';

$errores = [];
$exito = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');

    // Validaciones
    if (!$usuario) $errores[] = 'El usuario es obligatorio.';
    if (!$correo) $errores[] = 'El correo es obligatorio.';
    if (!$contrasena) $errores[] = 'La contraseña es obligatoria.';
    if (!$nombre) $errores[] = 'El nombre es obligatorio.';
    if (!$apellido) $errores[] = 'El apellido es obligatorio.';

    // Validar usuario/correo únicos
    if (empty($errores)) {
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE usuario = ? OR correo = ?");
        $stmt->bind_param('ss', $usuario, $correo);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errores[] = 'El usuario o correo ya existe.';
        }
        $stmt->close();
    }

    if (empty($errores)) {
        $rol_id = 1; // padre
        $stmt = $conexion->prepare("INSERT INTO usuarios (usuario, correo, contrasena, nombre, apellido, rol_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssssi', $usuario, $correo, $contrasena, $nombre, $apellido, $rol_id);
        if ($stmt->execute()) {
            $exito = true;
        } else {
            $errores[] = 'Error al registrar el padre.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Padre</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/secretario_style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            background: #18191a;
        }
        .main-card {
            max-width: 700px;
            margin: 0 auto;
        }
        .welcome-card {
            border-radius: 18px;
            margin-bottom: 32px;
            padding: 32px 32px 24px 32px;
        }
        .form-card {
            border-radius: 16px;
            padding: 32px 32px 24px 32px;
            margin-bottom: 32px;
        }
        .form-label {
            color: #fff;
            font-weight: 500;
        }
        .form-control, .form-select {
            background: #23272b;
            color: #fff;
            border: 1px solid #444;
        }
        .form-control:focus, .form-select:focus {
            border-color: #9c27b0;
            box-shadow: 0 0 0 0.2rem rgba(156,39,176,.15);
        }
        .btn-primary {
            background: #9c27b0;
            border-color: #9c27b0;
        }
        .btn-primary:hover {
            background: #7b1fa2;
            border-color: #7b1fa2;
        }
        .text-primary {
            color: #e1aaff !important;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="main-card">
        <div class="card mb-4 welcome-card welcome-card">
            <h2 class="mb-1" style="font-size:2.4rem;"><i class="bi bi-person-heart me-2"></i> Gestión de Padres</h2>
            <p class="mb-0" style="font-size:1.2rem;">Registra nuevos padres de familia en el sistema.</p>
        </div>
        <div class="card form-card shadow-sm bg-dark">
            <h4 class="mb-3 text-primary"><i class="bi bi-person-plus"></i> Registrar Nuevo Padre</h4>
            <?php if ($exito): ?>
                <div class="alert alert-success">¡Padre registrado correctamente!</div>
            <?php elseif (!empty($errores)): ?>
                <div class="alert alert-danger"><ul><?php foreach ($errores as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>
            <form method="post" class="row g-4">
                <div class="col-md-6">
                    <label for="usuario" class="form-label">Usuario</label>
                    <input type="text" class="form-control" id="usuario" name="usuario" required value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label for="correo" class="form-label">Correo electrónico</label>
                    <input type="email" class="form-control" id="correo" name="correo" required value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label for="contrasena" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                </div>
                <div class="col-md-6">
                    <label for="nombre" class="form-label">Nombre(s)</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                </div>
                <div class="col-md-12">
                    <label for="apellido" class="form-label">Apellido(s)</label>
                    <input type="text" class="form-control" id="apellido" name="apellido" required value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>">
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-person-plus"></i> Registrar</button>
                    <a href="panel_secretario.php" class="btn btn-link ms-3"><i class="bi bi-arrow-left"></i> Volver al panel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 