<?php
require_once '../auth_secretario.php';

$errores = [];
$exito = false;

// --- Eliminar padre ---
if (isset($_GET['eliminar'])) {
    $usuario_id = intval($_GET['eliminar']);
    // Eliminar primero de padres, luego de usuarios
    $conexion->query("DELETE FROM padres WHERE usuario_id = $usuario_id");
    $conexion->query("DELETE FROM usuarios WHERE id = $usuario_id");
    header('Location: registrar_padres.php');
    exit;
}

// --- Editar padre (mostrar datos en formulario) ---
$editando = false;
$padre_edit = null;
if (isset($_GET['editar'])) {
    $editando = true;
    $usuario_id = intval($_GET['editar']);
    $sql = "SELECT u.*, p.telefono, p.direccion, p.parentesco, p.profesion, p.correo_alternativo, p.observaciones FROM usuarios u LEFT JOIN padres p ON u.id = p.usuario_id WHERE u.id = $usuario_id";
    $res = $conexion->query($sql);
    if ($res && $res->num_rows > 0) {
        $padre_edit = $res->fetch_assoc();
    }
}

// --- Guardar padre (nuevo o editado) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $parentesco = trim($_POST['parentesco'] ?? '');
    $profesion = trim($_POST['profesion'] ?? '');
    $correo_alternativo = trim($_POST['correo_alternativo'] ?? '');
    $observaciones = trim($_POST['observaciones'] ?? '');
    $id_edit = intval($_POST['id_edit'] ?? 0);

    // Validaciones
    if (!$usuario) $errores[] = 'El usuario es obligatorio.';
    if (!$correo) $errores[] = 'El correo es obligatorio.';
    if (!$contrasena && !$id_edit) $errores[] = 'La contraseña es obligatoria.';
    if (!$nombre) $errores[] = 'El nombre es obligatorio.';
    if (!$apellido) $errores[] = 'El apellido es obligatorio.';
    if (!$telefono) $errores[] = 'El teléfono es obligatorio.';
    if (!$direccion) $errores[] = 'La dirección es obligatoria.';
    if (!$parentesco) $errores[] = 'El parentesco es obligatorio.';

    // Validar usuario/correo únicos solo si es nuevo o si cambió
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
        $rol_id = 1; // padre
        if ($id_edit > 0) {
            // Actualizar usuario
            if ($contrasena) {
                $stmt = $conexion->prepare("UPDATE usuarios SET usuario=?, correo=?, contrasena=?, nombre=?, apellido=? WHERE id=?");
                $stmt->bind_param('sssssi', $usuario, $correo, $contrasena, $nombre, $apellido, $id_edit);
            } else {
                $stmt = $conexion->prepare("UPDATE usuarios SET usuario=?, correo=?, nombre=?, apellido=? WHERE id=?");
                $stmt->bind_param('ssssi', $usuario, $correo, $nombre, $apellido, $id_edit);
            }
            $stmt->execute();
            $stmt->close();
            // Actualizar datos en padres
            $stmt2 = $conexion->prepare("UPDATE padres SET telefono=?, direccion=?, parentesco=?, profesion=?, correo_alternativo=?, observaciones=? WHERE usuario_id=?");
            $stmt2->bind_param('ssssssi', $telefono, $direccion, $parentesco, $profesion, $correo_alternativo, $observaciones, $id_edit);
            $stmt2->execute();
            $stmt2->close();
            $exito = true;
        } else {
            $stmt = $conexion->prepare("INSERT INTO usuarios (usuario, correo, contrasena, nombre, apellido, rol_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssssi', $usuario, $correo, $contrasena, $nombre, $apellido, $rol_id);
            if ($stmt->execute()) {
                $usuario_id = $conexion->insert_id;
                // Insertar en tabla padres
                $stmt2 = $conexion->prepare("INSERT INTO padres (usuario_id, telefono, direccion, parentesco, profesion, correo_alternativo, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt2->bind_param('issssss', $usuario_id, $telefono, $direccion, $parentesco, $profesion, $correo_alternativo, $observaciones);
                if ($stmt2->execute()) {
                    $exito = true;
                    // Limpiar variables para que el formulario quede vacío
                    $usuario = $correo = $contrasena = $nombre = $apellido = $telefono = $direccion = $parentesco = $profesion = $correo_alternativo = $observaciones = '';
                } else {
                    $errores[] = 'Error al registrar los datos adicionales del padre.';
                }
                $stmt2->close();
            } else {
                $errores[] = 'Error al registrar el padre.';
            }
            $stmt->close();
        }
    }
}
// --- Listar padres ---
$padres = [];
$sql = "SELECT u.id, u.usuario, u.correo, u.nombre, u.apellido, p.telefono, p.direccion, p.parentesco, p.profesion, p.correo_alternativo, p.observaciones FROM usuarios u LEFT JOIN padres p ON u.id = p.usuario_id WHERE u.rol_id = 1 ORDER BY u.nombre, u.apellido";
$res = $conexion->query($sql);
while ($row = $res->fetch_assoc()) {
    $padres[] = $row;
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
    <link rel="stylesheet" href="css/registrar_padres_style.css">
</head>
<body>
<div class="container py-5">
    <div class="main-card">
        <div class="card mb-4 welcome-card welcome-card">
            <h2 class="mb-1 welcome-title"><i class="bi bi-person-heart me-2"></i> Gestión de Padres</h2>
            <p class="mb-0 welcome-subtitle">Registra nuevos padres de familia en el sistema.</p>
        </div>
        <div class="card form-card shadow-sm bg-dark">
            <h4 class="mb-3 text-primary"><i class="bi bi-person-plus"></i> Registrar Nuevo Padre</h4>
            <?php if ($exito): ?>
                <div class="alert alert-success">¡Padre registrado correctamente!</div>
            <?php elseif (!empty($errores)): ?>
                <div class="alert alert-danger"><ul><?php foreach ($errores as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>
            <form method="post" class="row g-4">
                <input type="hidden" name="id_edit" value="<?= $padre_edit['id'] ?? '' ?>">
                <div class="col-md-6">
                    <label for="usuario" class="form-label">Usuario</label>
                    <input type="text" class="form-control" id="usuario" name="usuario" required value="<?= $editando ? htmlspecialchars($padre_edit['usuario'] ?? '') : htmlspecialchars($usuario ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label for="correo" class="form-label">Correo electrónico</label>
                    <input type="email" class="form-control" id="correo" name="correo" required value="<?= htmlspecialchars($padre_edit['correo'] ?? ($correo ?? '')) ?>">
                </div>
                <div class="col-md-6">
                    <label for="contrasena" class="form-label">Contraseña <?= $editando ? '(dejar en blanco para no cambiar)' : '' ?></label>
                    <input type="password" class="form-control" id="contrasena" name="contrasena" <?= $editando ? '' : 'required' ?> >
                </div>
                <div class="col-md-6">
                    <label for="nombre" class="form-label">Nombre(s)</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required value="<?= htmlspecialchars($padre_edit['nombre'] ?? ($nombre ?? '')) ?>">
                </div>
                <div class="col-md-12">
                    <label for="apellido" class="form-label">Apellido(s)</label>
                    <input type="text" class="form-control" id="apellido" name="apellido" required value="<?= htmlspecialchars($padre_edit['apellido'] ?? ($apellido ?? '')) ?>">
                </div>
                <div class="col-md-6">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="telefono" name="telefono" required value="<?= htmlspecialchars($padre_edit['telefono'] ?? ($telefono ?? '')) ?>">
                </div>
                <div class="col-md-6">
                    <label for="direccion" class="form-label">Dirección</label>
                    <input type="text" class="form-control" id="direccion" name="direccion" required value="<?= htmlspecialchars($padre_edit['direccion'] ?? ($direccion ?? '')) ?>">
                </div>
                <div class="col-md-6">
                    <label for="parentesco" class="form-label">Parentesco</label>
                    <input type="text" class="form-control" id="parentesco" name="parentesco" required value="<?= htmlspecialchars($padre_edit['parentesco'] ?? ($parentesco ?? '')) ?>">
                </div>
                <div class="col-md-6">
                    <label for="profesion" class="form-label">Profesión</label>
                    <input type="text" class="form-control" id="profesion" name="profesion" value="<?= htmlspecialchars($padre_edit['profesion'] ?? ($profesion ?? '')) ?>">
                </div>
                <div class="col-md-6">
                    <label for="correo_alternativo" class="form-label">Correo alternativo</label>
                    <input type="email" class="form-control" id="correo_alternativo" name="correo_alternativo" value="<?= htmlspecialchars($padre_edit['correo_alternativo'] ?? ($correo_alternativo ?? '')) ?>">
                </div>
                <div class="col-md-6">
                    <label for="observaciones" class="form-label">Observaciones</label>
                    <input type="text" class="form-control" id="observaciones" name="observaciones" value="<?= htmlspecialchars($padre_edit['observaciones'] ?? ($observaciones ?? '')) ?>">
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-person-plus"></i> <?= $editando ? 'Actualizar' : 'Registrar' ?></button>
                    <?php if ($editando): ?>
                        <a href="registrar_padres.php" class="btn btn-secondary ms-2">Cancelar</a>
                    <?php else: ?>
                        <a href="panel_secretario.php" class="btn btn-outline-light ms-3"><i class="bi bi-arrow-left"></i> Volver al panel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    <div class="card mt-4 bg-dark">
        <h4 class="mb-3 text-primary"><i class="bi bi-list"></i> Lista de Padres</h4>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Usuario</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Dirección</th>
                        <th>Parentesco</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($padres)): ?>
                    <tr><td colspan="8" class="text-center">No hay padres registrados.</td></tr>
                <?php else: foreach ($padres as $i => $p): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td><?= htmlspecialchars($p['usuario']) ?></td>
                        <td><?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido']) ?></td>
                        <td><?= htmlspecialchars($p['correo']) ?></td>
                        <td><?= htmlspecialchars($p['telefono']) ?></td>
                        <td><?= htmlspecialchars($p['direccion']) ?></td>
                        <td><?= htmlspecialchars($p['parentesco']) ?></td>
                        <td>
                            <a href="?editar=<?= $p['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                            <a href="?eliminar=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que deseas eliminar este padre?')"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 