<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso para Secretarios | Escuela Primaria</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body {
            background: linear-gradient(135deg, #6f42c1 0%, #a78bfa 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.2);
            background: #fff;
            padding: 2.5rem 2rem;
            max-width: 400px;
            width: 100%;
        }
        .login-card .form-control {
            border-radius: 10px;
        }
        .login-card .btn-primary {
            width: 100%;
            border-radius: 10px;
            background: #6f42c1;
            border-color: #6f42c1;
        }
        .login-card .btn-primary:hover {
            background: #5a32a3;
            border-color: #5a32a3;
        }
        .login-icon {
            font-size: 3.5rem;
            color: #6f42c1;
            margin-bottom: 1rem;
        }
        .back-link {
            display: block;
            margin-top: 1.5rem;
            text-align: center;
            color: #6f42c1;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        .back-link:hover {
            color: #5a32a3;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-card mx-auto">
        <div class="text-center mb-4">
            <i class="bi bi-shield-lock-fill login-icon"></i>
            <h2 class="fw-bold mb-2">Acceso para Secretarios</h2>
            <p class="text-muted mb-0">Ingresa tus datos para administrar el sistema escolar</p>
        </div>
        <form id="formLoginSecretario">
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario o Correo</label>
                <input type="text" class="form-control" id="usuario" placeholder="Ingresa tu usuario o correo" required>
            </div>
            <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="contrasena" placeholder="Ingresa tu contraseña" required>
            </div>
            <button type="submit" class="btn btn-primary">Ingresar</button>
        </form>
        <a href="index.html" class="back-link"><i class="bi bi-arrow-left"></i> Volver al inicio</a>
    </div>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('formLoginSecretario').addEventListener('submit', function(e) {
            e.preventDefault();
            window.location.href = 'vista_secretario/panel_secretario.php';
        });
    </script>
</body>
</html> 