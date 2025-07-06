<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso para Padres | Linkademic-Progress</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/styles_logins.css">
</head>

<body class="login-padres">
    <div class="login-card mx-auto">
        <div class="text-center mb-4">
            <i class="bi bi-people-fill login-icon"></i>
            <h2 class="fw-bold mb-2">Acceso para Padres</h2>
            <p class="text-muted mb-0">Ingresa tus datos para acceder al seguimiento académico de tu hijo</p>
        </div>
        <form id="formLoginPadre">
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario o Correo</label>
                <input type="text" class="form-control" id="usuario" placeholder="Ingresa tu usuario o correo" required>
            </div>
            <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="contrasena" placeholder="Ingresa tu contraseña"
                    required>
            </div>
            <button type="submit" class="btn btn-primary">Ingresar</button>
        </form>
        <a href="index.html" class="back-link"><i class="bi bi-arrow-left"></i> Volver al inicio</a>
    </div>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('formLoginPadre').addEventListener('submit', function(e) {
            e.preventDefault();
            window.location.href = 'vista_padres/panel_padre.php';
        });
    </script>
</body>

</html>