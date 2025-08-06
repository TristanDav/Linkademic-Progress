<?php
require_once '../auth_secretario.php';

$mensaje_exito = '';
$mensaje_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');
    $padre_id = $_POST['padre_id'] ?? 'all';

    if (empty($titulo) || empty($mensaje)) {
        $mensaje_error = "Debe llenar todos los campos.";
    } else {
        try {
            if ($padre_id === 'all') {
                // Enviar a todos los padres
                $sql = "SELECT p.id FROM padres p JOIN usuarios u ON p.usuario_id = u.id WHERE u.rol_id = 1";
                $result = $conexion->query($sql);
                
                $enviadas = 0;
                while ($row = $result->fetch_assoc()) {
                    $stmt = $conexion->prepare("INSERT INTO notificaciones (titulo, mensaje, fecha, id_padre, leido) VALUES (?, ?, NOW(), ?, 0)");
                    $stmt->bind_param("ssi", $titulo, $mensaje, $row['id']);
                    if ($stmt->execute()) {
                        $enviadas++;
                    }
                    $stmt->close();
                }
                $mensaje_exito = "Notificación enviada a $enviadas padres correctamente.";
            } else {
                // Enviar a un padre específico
                $stmt = $conexion->prepare("INSERT INTO notificaciones (titulo, mensaje, fecha, id_padre, leido) VALUES (?, ?, NOW(), ?, 0)");
                $stmt->bind_param("ssi", $titulo, $mensaje, $padre_id);
                if ($stmt->execute()) {
                    $mensaje_exito = "Notificación enviada correctamente.";
                } else {
                    $mensaje_error = "Error al enviar la notificación.";
                }
                $stmt->close();
            }
        } catch (Exception $e) {
            $mensaje_error = "Error en la base de datos: " . $e->getMessage();
        }
    }
}

// Obtener lista de padres
$padres = [];
$sql_padres = "SELECT p.id, u.nombre, u.apellido, u.correo 
               FROM padres p 
               JOIN usuarios u ON p.usuario_id = u.id 
               WHERE u.rol_id = 1 
               ORDER BY u.nombre, u.apellido";
$result_padres = $conexion->query($sql_padres);
while ($row = $result_padres->fetch_assoc()) {
    $padres[] = $row;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Notificación - Sistema Escolar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/secretario_style.css" rel="stylesheet">
    <link href="css/enviar_notificacion_style.css" rel="stylesheet">
</head>
<body>
    <div class="form-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header text-center">
                            <h3 class="mb-0">
                                <i class="bi bi-bell me-2"></i>
                                Enviar Notificación a Padres
                            </h3>
                        </div>
                        <div class="card-body p-4">
                            <?php if (!empty($mensaje_error)): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <?= htmlspecialchars($mensaje_error) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($mensaje_exito)): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <?= htmlspecialchars($mensaje_exito) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="">
                                <div class="mb-4">
                                    <label for="padre_id" class="form-label">
                                        <i class="bi bi-people me-2"></i>
                                        Seleccionar destinatario:
                                    </label>
                                    <select name="padre_id" id="padre_id" class="form-select" required>
                                        <option value="all">📢 Todos los padres</option>
                                        <?php foreach ($padres as $padre): ?>
                                            <option value="<?= $padre['id'] ?>">
                                                👤 <?= htmlspecialchars($padre['nombre'] . ' ' . $padre['apellido']) ?> 
                                                (<?= htmlspecialchars($padre['correo']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label for="titulo" class="form-label">
                                        <i class="bi bi-type-bold me-2"></i>
                                        Título de la notificación:
                                    </label>
                                    <input type="text" name="titulo" id="titulo" class="form-control" 
                                           required maxlength="255" placeholder="Ej: Reunión de padres">
                                </div>

                                <div class="mb-4">
                                    <label for="mensaje" class="form-label">
                                        <i class="bi bi-chat-text me-2"></i>
                                        Mensaje:
                                    </label>
                                    <textarea name="mensaje" id="mensaje" class="form-control" rows="6" 
                                              required placeholder="Escriba aquí el contenido de la notificación..."></textarea>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="panel_secretario.php" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left me-2"></i>
                                        Volver al Panel
                                    </a>
                                    <button type="submit" class="btn btn-enviar">
                                        <i class="bi bi-send me-2"></i>
                                        Enviar Notificación
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Limpiar formulario después de envío exitoso
        <?php if (!empty($mensaje_exito)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('titulo').value = '';
            document.getElementById('mensaje').value = '';
        });
        <?php endif; ?>
    </script>
</body>
</html>
