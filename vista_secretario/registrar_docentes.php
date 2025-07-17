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
    $conexion->query("DELETE FROM docentes WHERE usuario_id = $id");
    $conexion->query("DELETE FROM usuarios WHERE id = $id AND rol_id = 2");
    header('Location: registrar_docentes.php');
    exit;
}

// --- Editar docente (mostrar datos en formulario) ---
$editando = false;
$docente_edit = null;
if (isset($_GET['editar'])) {
    $editando = true;
    $id = intval($_GET['editar']);
    $res = $conexion->query("SELECT u.*, d.edad, d.anos_academico, d.direccion AS direccion_doc, d.telefono, d.especialidad, d.numero_empleado, d.nivel_educativo FROM usuarios u LEFT JOIN docentes d ON u.id = d.usuario_id WHERE u.id = $id AND u.rol_id = 2");
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
    $edad = trim($_POST['edad'] ?? '');
    $anos_academico = trim($_POST['anos_academico'] ?? '');
    $direccion_doc = trim($_POST['direccion_doc'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $especialidad = trim($_POST['especialidad'] ?? '');
    $numero_empleado = trim($_POST['numero_empleado'] ?? '');
    $nivel_educativo = trim($_POST['nivel_educativo'] ?? '');
    $id_edit = intval($_POST['id_edit'] ?? 0);

    // Validaciones
    if (!$usuario) $errores[] = 'El usuario es obligatorio.';
    if (!$correo) $errores[] = 'El correo es obligatorio.';
    if (!$contrasena && !$id_edit) $errores[] = 'La contraseña es obligatoria.';
    if (!$nombre) $errores[] = 'El nombre es obligatorio.';
    if (!$apellido) $errores[] = 'El apellido es obligatorio.';
    if (!$edad) $errores[] = 'La edad es obligatoria.';
    if (!$telefono) $errores[] = 'El teléfono es obligatorio.';
    if (!$direccion_doc) $errores[] = 'La dirección es obligatoria.';
    if (!$especialidad) $errores[] = 'La especialidad es obligatoria.';
    if (!$numero_empleado) $errores[] = 'El número de empleado es obligatorio.';
    if (!$nivel_educativo) $errores[] = 'El nivel educativo es obligatorio.';

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
            // Actualizar usuario
            if ($contrasena) {
                $stmt = $conexion->prepare("UPDATE usuarios SET usuario=?, correo=?, contrasena=?, nombre=?, apellido=? WHERE id=? AND rol_id=2");
                $stmt->bind_param('sssssi', $usuario, $correo, $contrasena, $nombre, $apellido, $id_edit);
            } else {
                $stmt = $conexion->prepare("UPDATE usuarios SET usuario=?, correo=?, nombre=?, apellido=? WHERE id=? AND rol_id=2");
                $stmt->bind_param('ssssi', $usuario, $correo, $nombre, $apellido, $id_edit);
            }
            $stmt->execute();
            $stmt->close();
            // Actualizar datos en docentes
            $stmt2 = $conexion->prepare("UPDATE docentes SET edad=?, anos_academico=?, direccion=?, telefono=?, especialidad=?, numero_empleado=?, nivel_educativo=? WHERE usuario_id=?");
            $stmt2->bind_param('iisssssi', $edad, $anos_academico, $direccion_doc, $telefono, $especialidad, $numero_empleado, $nivel_educativo, $id_edit);
            $stmt2->execute();
            $stmt2->close();
            $exito = true;
        } else {
            $stmt = $conexion->prepare("INSERT INTO usuarios (usuario, correo, contrasena, nombre, apellido, rol_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssssi', $usuario, $correo, $contrasena, $nombre, $apellido, $rol_id);
            if ($stmt->execute()) {
                $usuario_id = $conexion->insert_id;
                // Insertar en tabla docentes
                $stmt2 = $conexion->prepare("INSERT INTO docentes (usuario_id, edad, anos_academico, direccion, telefono, especialidad, numero_empleado, nivel_educativo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt2->bind_param('iiisssss', $usuario_id, $edad, $anos_academico, $direccion_doc, $telefono, $especialidad, $numero_empleado, $nivel_educativo);
                if ($stmt2->execute()) {
                    $exito = true;
                    // Limpiar variables para que el formulario quede vacío
                    $usuario = $correo = $contrasena = $nombre = $apellido = $edad = $anos_academico = $direccion_doc = $telefono = $especialidad = $numero_empleado = $nivel_educativo = '';
                } else {
                    $errores[] = 'Error al registrar los datos adicionales del docente.';
                }
                $stmt2->close();
            } else {
                $errores[] = 'Error al registrar el docente.';
            }
            $stmt->close();
        }
    }
}
// --- Listar docentes ---
$docentes = [];
$sql = "SELECT u.id, u.usuario, u.nombre, u.apellido, u.correo, d.edad, d.anos_academico, d.direccion, d.telefono, d.especialidad, d.numero_empleado, d.nivel_educativo FROM usuarios u LEFT JOIN docentes d ON u.id = d.usuario_id WHERE u.rol_id = 2 ORDER BY u.apellido, u.nombre";
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
                    <input type="text" class="form-control" id="usuario" name="usuario" required value="<?= $docente_edit['usuario'] ?? ($usuario ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label for="correo" class="form-label">Correo electrónico</label>
                    <input type="email" class="form-control" id="correo" name="correo" required value="<?= $docente_edit['correo'] ?? ($correo ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label for="contrasena" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="contrasena" name="contrasena" required value="<?= $editando ? $docente_edit['contrasena'] : '' ?>">
                </div>
                <div class="col-md-6">
                    <label for="nombre" class="form-label">Nombre(s)</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required value="<?= $docente_edit['nombre'] ?? ($nombre ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label for="apellido" class="form-label">Apellido(s)</label>
                    <input type="text" class="form-control" id="apellido" name="apellido" required value="<?= $docente_edit['apellido'] ?? ($apellido ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label for="edad" class="form-label">Edad</label>
                    <input type="number" class="form-control" id="edad" name="edad" required value="<?= $docente_edit['edad'] ?? ($edad ?? '') ?>">
                </div>
                <div class="col-md-12">
                    <label for="direccion_doc" class="form-label">Dirección</label>
                    <input type="text" class="form-control" id="direccion_doc" name="direccion_doc" required value="<?= $docente_edit['direccion_doc'] ?? ($direccion_doc ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="telefono" name="telefono" required value="<?= $docente_edit['telefono'] ?? ($telefono ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label for="especialidad" class="form-label">Especialidad</label>
                    <input type="text" class="form-control" id="especialidad" name="especialidad" required value="<?= $docente_edit['especialidad'] ?? ($especialidad ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label for="numero_empleado" class="form-label">Número de Empleado</label>
                    <input type="text" class="form-control" id="numero_empleado" name="numero_empleado" required value="<?= $docente_edit['numero_empleado'] ?? ($numero_empleado ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label for="nivel_educativo" class="form-label">Nivel Educativo</label>
                    <input type="text" class="form-control" id="nivel_educativo" name="nivel_educativo" required value="<?= $docente_edit['nivel_educativo'] ?? ($nivel_educativo ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label for="anos_academico" class="form-label">Años de Académico</label>
                    <input type="number" class="form-control" id="anos_academico" name="anos_academico" required value="<?= $docente_edit['anos_academico'] ?? ($anos_academico ?? '') ?>">
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-person-plus"></i> <?= $editando ? 'Actualizar' : 'Registrar' ?></button>
                    <?php if ($editando): ?>
                        <a href="registrar_docentes.php" class="btn btn-secondary ms-2">Cancelar</a>
                    <?php else: ?>
                        <a href="panel_secretario.php" class="btn btn-outline-light ms-2"><i class="bi bi-arrow-left"></i> Volver al panel</a>
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
        <?php if ($editando): ?>
            <a href="registrar_docentes.php" class="btn btn-secondary ms-2">Cancelar</a>
        <?php else: ?>
            <a href="panel_secretario.php" class="btn btn-outline-light ms-2"><i class="bi bi-arrow-left"></i> Volver al panel</a>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 