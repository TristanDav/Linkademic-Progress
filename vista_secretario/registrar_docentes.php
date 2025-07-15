<?php
session_start();
require_once '../conexion.php';

$errores = [];
$exito = false;
$editando = false;
$docente_edit = null;

// --- Eliminar docente ---
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conexion->query("DELETE FROM usuarios WHERE id = $id AND rol_id = 2");
    header('Location: registrar_docentes.php');
    exit;
}

// --- Editar docente (mostrar datos en formulario) ---
if (isset($_GET['editar'])) {
    $editando = true;
    $id = intval($_GET['editar']);
    $res = $conexion->query("SELECT * FROM usuarios WHERE id = $id AND rol_id = 2");
    if ($res && $res->num_rows > 0) {
        $docente_edit = $res->fetch_assoc();
    }
}

// --- Guardar docente (nuevo o editado) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $id_edit = intval($_POST['id_edit'] ?? 0);

    // Validaciones
    if (!$usuario) $errores[] = 'El usuario es obligatorio.';
    if (!$correo) $errores[] = 'El correo es obligatorio.';
    if (!$contrasena) $errores[] = 'La contraseña es obligatoria.';
    if (!$nombre) $errores[] = 'El nombre es obligatorio.';
    if (!$apellido) $errores[] = 'El apellido es obligatorio.';

    // Validar usuario/correo únicos
    if (empty($errores)) {
        if ($id_edit > 0) {
            $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE (usuario = ? OR correo = ?) AND id != ?");
            $stmt->bind_param('ssi', $usuario, $correo, $id_edit);
        } else {
            $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE usuario = ? OR correo = ?");
            $stmt->bind_param('ss', $usuario, $correo);
        }
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errores[] = 'El usuario o correo ya existe.';
        }
        $stmt->close();
    }

    if (empty($errores)) {
        $rol_id = 2; // docente
        if ($id_edit > 0) {
            $stmt = $conexion->prepare("UPDATE usuarios SET usuario=?, correo=?, contrasena=?, nombre=?, apellido=? WHERE id=? AND rol_id=2");
            $stmt->bind_param('sssssi', $usuario, $correo, $contrasena, $nombre, $apellido, $id_edit);
            $exito = $stmt->execute();
            $stmt->close();
            if ($exito) {
                header('Location: registrar_docentes.php');
                exit;
            } else {
                $errores[] = 'Error al actualizar el docente.';
            }
        } else {
            $stmt = $conexion->prepare("INSERT INTO usuarios (usuario, correo, contrasena, nombre, apellido, rol_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssssi', $usuario, $correo, $contrasena, $nombre, $apellido, $rol_id);
            $exito = $stmt->execute();
            $stmt->close();
        }
    }
}

// --- Listar docentes ---
$docentes = [];
$sql = "SELECT * FROM usuarios WHERE rol_id = 2 ORDER BY apellido, nombre";
$res = $conexion->query($sql);
while ($row = $res->fetch_assoc()) {
    $docentes[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Docentes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/secretario_style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background: #18191a; }
        .main-card { max-width: 800px; margin: 0 auto; }
        .welcome-card { border-radius: 18px; margin-bottom: 32px; padding: 32px 32px 24px 32px; }
        .form-card { border-radius: 16px; padding: 32px 32px 24px 32px; margin-bottom: 32px; }
        .form-label { color: #fff; font-weight: 500; }
        .form-control, .form-select { background: #23272b; color: #fff; border: 1px solid #444; }
        .form-control:focus, .form-select:focus { border-color: #9c27b0; box-shadow: 0 0 0 0.2rem rgba(156,39,176,.15); }
        .btn-primary { background: #9c27b0; border-color: #9c27b0; }
        .btn-primary:hover { background: #7b1fa2; border-color: #7b1fa2; }
        .text-primary { color: #e1aaff !important; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="main-card">
        <div class="card mb-4 welcome-card">
            <h2 class="mb-1" style="font-size:2.4rem;"><i class="bi bi-person-badge me-2"></i> Gestión de Docentes</h2>
            <p class="mb-0" style="font-size:1.2rem;">Registra, edita y elimina docentes del sistema.</p>
        </div>
        <div class="card form-card shadow-sm bg-dark">
            <h4 class="mb-3 text-primary"><i class="bi bi-person-plus"></i> <?= $editando ? 'Editar Docente' : 'Registrar Nuevo Docente' ?></h4>
            <?php if ($exito && !$editando): ?>
                <div class="alert alert-success">¡Docente registrado correctamente!</div>
            <?php elseif (!empty($errores)): ?>
                <div class="alert alert-danger"><ul><?php foreach ($errores as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>
            <form method="post" class="row g-4">
                <input type="hidden" name="id_edit" value="<?= $docente_edit['id'] ?? '' ?>">
                <div class="col-md-6">
                    <label for="usuario" class="form-label">Usuario</label>
                    <input type="text" class="form-control" id="usuario" name="usuario" required value="<?= htmlspecialchars($docente_edit['usuario'] ?? ($_POST['usuario'] ?? '')) ?>">
                </div>
                <div class="col-md-6">
                    <label for="correo" class="form-label">Correo electrónico</label>
                    <input type="email" class="form-control" id="correo" name="correo" required value="<?= htmlspecialchars($docente_edit['correo'] ?? ($_POST['correo'] ?? '')) ?>">
                </div>
                <div class="col-md-6">
                    <label for="contrasena" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="contrasena" name="contrasena" required value="<?= $editando ? $docente_edit['contrasena'] : '' ?>">
                </div>
                <div class="col-md-6">
                    <label for="nombre" class="form-label">Nombre(s)</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required value="<?= htmlspecialchars($docente_edit['nombre'] ?? ($_POST['nombre'] ?? '')) ?>">
                </div>
                <div class="col-md-12">
                    <label for="apellido" class="form-label">Apellido(s)</label>
                    <input type="text" class="form-control" id="apellido" name="apellido" required value="<?= htmlspecialchars($docente_edit['apellido'] ?? ($_POST['apellido'] ?? '')) ?>">
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-person-plus"></i> <?= $editando ? 'Actualizar' : 'Registrar' ?></button>
                    <?php if ($editando): ?>
                        <a href="registrar_docentes.php" class="btn btn-secondary ms-2">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <div class="card bg-dark">
            <h4 class="mb-3 text-primary"><i class="bi bi-list"></i> Lista de Docentes</h4>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Usuario</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($docentes)): ?>
                        <tr><td colspan="5" class="text-center">No hay docentes registrados.</td></tr>
                    <?php else: foreach ($docentes as $i => $d): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><?= htmlspecialchars($d['usuario']) ?></td>
                            <td><?= htmlspecialchars($d['nombre'] . ' ' . $d['apellido']) ?></td>
                            <td><?= htmlspecialchars($d['correo']) ?></td>
                            <td>
                                <a href="?editar=<?= $d['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                <a href="?eliminar=<?= $d['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que deseas eliminar este docente?')"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <a href="panel_secretario.php" class="btn btn-link mt-3"><i class="bi bi-arrow-left"></i> Volver al panel</a>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 