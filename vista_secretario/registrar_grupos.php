<?php
session_start();
require_once '../conexion.php';

$errores = [];
$exito = false;
$editando = false;
$grupo_edit = null;

// --- Eliminar grupo ---
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conexion->query("DELETE FROM grupos WHERE id = $id");
    header('Location: registrar_grupos.php');
    exit;
}

// --- Editar grupo (mostrar datos en formulario) ---
if (isset($_GET['editar'])) {
    $editando = true;
    $id = intval($_GET['editar']);
    $res = $conexion->query("SELECT * FROM grupos WHERE id = $id");
    if ($res && $res->num_rows > 0) {
        $grupo_edit = $res->fetch_assoc();
    }
}

// --- Obtener docentes para el select ---
$docentes = [];
$res_doc = $conexion->query("SELECT id, nombre, apellido FROM usuarios WHERE rol_id = 2 ORDER BY apellido, nombre");
while ($row = $res_doc->fetch_assoc()) {
    $docentes[] = $row;
}

// Obtener materias para asignar al grupo
$materias = [];
$res_mat = $conexion->query("SELECT id, nombre, descripcion FROM materias ORDER BY nombre");
while ($row = $res_mat->fetch_assoc()) {
    $materias[] = $row;
}

// --- Guardar grupo (nuevo o editado) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nivel = trim($_POST['nivel'] ?? '');
    $letra = trim($_POST['letra'] ?? '');
    $docente_id = intval($_POST['docente_id'] ?? 0);
    $id_edit = intval($_POST['id_edit'] ?? 0);
    $nombre = ($nivel && $letra) ? ("$nivel-$letra") : '';
    $materias_seleccionadas = $_POST['materias'] ?? [];

    // Validaciones
    if (!$nivel) $errores[] = 'El nivel es obligatorio.';
    if (!$letra) $errores[] = 'La letra del grupo es obligatoria.';
    if ($docente_id <= 0) $errores[] = 'Selecciona un docente asignado.';
    if (empty($materias_seleccionadas)) $errores[] = 'Selecciona al menos una materia para el grupo.';

    if (empty($errores)) {
        if ($id_edit > 0) {
            $stmt = $conexion->prepare("UPDATE grupos SET nombre=?, docente_id=? WHERE id=?");
            $stmt->bind_param('sii', $nombre, $docente_id, $id_edit);
            $exito = $stmt->execute();
            $stmt->close();
            if ($exito) {
                // Actualizar materias asignadas (opcional, si se implementa edición de materias)
                header('Location: registrar_grupos.php');
                exit;
            } else {
                $errores[] = 'Error al actualizar el grupo.';
            }
        } else {
            $stmt = $conexion->prepare("INSERT INTO grupos (nombre, docente_id) VALUES (?, ?)");
            $stmt->bind_param('si', $nombre, $docente_id);
            if ($stmt->execute()) {
                $grupo_id = $conexion->insert_id;
                // Insertar materias seleccionadas en grupo_materias
                $stmt_mat = $conexion->prepare("INSERT INTO grupo_materias (grupo_id, materia_id) VALUES (?, ?)");
                foreach ($materias_seleccionadas as $materia_id) {
                    $stmt_mat->bind_param('ii', $grupo_id, $materia_id);
                    $stmt_mat->execute();
                }
                $stmt_mat->close();
                // Actualizar el grupo_id en la tabla docentes para el docente asignado
                $stmt_doc = $conexion->prepare("UPDATE docentes SET grupo_id = ? WHERE usuario_id = ?");
                $stmt_doc->bind_param('ii', $grupo_id, $docente_id);
                $stmt_doc->execute();
                $stmt_doc->close();
                $exito = true;
            } else {
                $errores[] = 'Error al registrar el grupo.';
            }
            $stmt->close();
        }
    }
}

// --- Listar grupos ---
$grupos = [];
$sql = "SELECT g.*, u.nombre AS docente_nombre, u.apellido AS docente_apellido FROM grupos g LEFT JOIN usuarios u ON g.docente_id = u.id ORDER BY g.nombre";
$res = $conexion->query($sql);
while ($row = $res->fetch_assoc()) {
    $grupos[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Grupos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/secretario_style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background: #18191a; }
        .main-card { max-width: 700px; margin: 0 auto; }
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
            <h2 class="mb-1" style="font-size:2.4rem;"><i class="bi bi-collection me-2"></i> Gestión de Grupos</h2>
            <p class="mb-0" style="font-size:1.2rem;">Registra, edita y elimina grupos escolares.</p>
        </div>
        <div class="card form-card shadow-sm bg-dark">
            <h4 class="mb-3 text-primary"><i class="bi bi-person-plus"></i> <?= $editando ? 'Editar Grupo' : 'Registrar Nuevo Grupo' ?></h4>
            <?php if ($exito && !$editando): ?>
                <div class="alert alert-success">¡Grupo registrado correctamente!</div>
            <?php elseif (!empty($errores)): ?>
                <div class="alert alert-danger"><ul><?php foreach ($errores as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>
            <form method="post" class="row g-4">
                <input type="hidden" name="id_edit" value="<?= $grupo_edit['id'] ?? '' ?>">
                <div class="col-md-4">
                    <label for="nivel" class="form-label">Grado:</label>
                    <select class="form-select" id="nivel" name="nivel" required>
                        <option value="">-- Nivel (1-6) --</option>
                        <?php
                        $niveles = range(1, 6);
                        $nivel_actual = $grupo_edit ? (explode('-', $grupo_edit['nombre'])[0] ?? '') : ($_POST['nivel'] ?? '');
                        foreach ($niveles as $nivel): ?>
                            <option value="<?= $nivel ?>" <?= ($nivel_actual == $nivel) ? 'selected' : '' ?>><?= $nivel ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="letra" class="form-label">Grupo:</label>
                    <select class="form-select" id="letra" name="letra" required>
                        <option value="">-- Letra (A-H) --</option>
                        <?php
                        $letras = range('A', 'H');
                        $letra_actual = $grupo_edit ? (explode('-', $grupo_edit['nombre'])[1] ?? '') : ($_POST['letra'] ?? '');
                        foreach ($letras as $letra): ?>
                            <option value="<?= $letra ?>" <?= ($letra_actual == $letra) ? 'selected' : '' ?>><?= $letra ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="docente_id" class="form-label">Docente asignado</label>
                    <select class="form-select" id="docente_id" name="docente_id" required>
                        <option value="">-- Selecciona docente --</option>
                        <?php foreach ($docentes as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= ((isset($grupo_edit['docente_id']) && $grupo_edit['docente_id'] == $d['id']) || (isset($_POST['docente_id']) && $_POST['docente_id'] == $d['id'])) ? 'selected' : '' ?>><?= htmlspecialchars($d['nombre'] . ' ' . $d['apellido']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Materias a asignar al grupo:</label>
                    <div class="table-responsive">
                        <table class="table table-sm table-dark table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($materias as $mat): ?>
                                <tr>
                                    <td><input type="checkbox" name="materias[]" value="<?= $mat['id'] ?>" <?= (isset($_POST['materias']) && in_array($mat['id'], $_POST['materias'])) ? 'checked' : '' ?>></td>
                                    <td><?= htmlspecialchars($mat['nombre']) ?></td>
                                    <td><?= htmlspecialchars($mat['descripcion']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-person-plus"></i> <?= $editando ? 'Actualizar' : 'Registrar' ?></button>
                    <?php if ($editando): ?>
                        <a href="registrar_grupos.php" class="btn btn-secondary ms-2">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <div class="card bg-dark">
            <h4 class="mb-3 text-primary"><i class="bi bi-list"></i> Lista de Grupos</h4>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Docente Asignado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($grupos)): ?>
                        <tr><td colspan="4" class="text-center">No hay grupos registrados.</td></tr>
                    <?php else: foreach ($grupos as $i => $g): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><?= htmlspecialchars($g['nombre']) ?></td>
                            <td><?= htmlspecialchars($g['docente_nombre'] . ' ' . $g['docente_apellido']) ?></td>
                            <td>
                                <a href="?editar=<?= $g['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                <a href="?eliminar=<?= $g['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que deseas eliminar este grupo?')"><i class="bi bi-trash"></i></a>
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