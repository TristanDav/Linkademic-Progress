<?php
require_once '../auth_secretario.php';

$errores = [];
$exito = false;
$editando = false;
$materia_edit = null;

// --- Eliminar materia ---
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conexion->query("DELETE FROM materias WHERE id = $id");
    header('Location: registrar_materias.php');
    exit;
}

// --- Editar materia (mostrar datos en formulario) ---
if (isset($_GET['editar'])) {
    $editando = true;
    $id = intval($_GET['editar']);
    $res = $conexion->query("SELECT * FROM materias WHERE id = $id");
    if ($res && $res->num_rows > 0) {
        $materia_edit = $res->fetch_assoc();
    }
}

// --- Guardar materia (nuevo o editado) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $id_edit = intval($_POST['id_edit'] ?? 0);

    // Validaciones
    if (!$nombre) $errores[] = 'El nombre de la materia es obligatorio.';

    if (empty($errores)) {
        if ($id_edit > 0) {
            $stmt = $conexion->prepare("UPDATE materias SET nombre=?, descripcion=? WHERE id=?");
            $stmt->bind_param('ssi', $nombre, $descripcion, $id_edit);
            $exito = $stmt->execute();
            $stmt->close();
            if ($exito) {
                header('Location: registrar_materias.php');
                exit;
            } else {
                $errores[] = 'Error al actualizar la materia.';
            }
        } else {
            $stmt = $conexion->prepare("INSERT INTO materias (nombre, descripcion) VALUES (?, ?)");
            $stmt->bind_param('ss', $nombre, $descripcion);
            if ($stmt->execute()) {
                $exito = true;
                $nombre = $descripcion = '';
            } else {
                $errores[] = 'Error al registrar la materia.';
            }
            $stmt->close();
        }
    }
}

// --- Listar materias ---
$materias = [];
$res = $conexion->query("SELECT * FROM materias ORDER BY nombre");
while ($row = $res->fetch_assoc()) {
    $materias[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Materias</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/secretario_style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/registrar_materias_style.css">
</head>
<body>
<div class="container py-5">
    <div class="main-card">
        <div class="card mb-4 welcome-card">
            <h2 class="mb-1 welcome-title"><i class="bi bi-book me-2"></i> Gestión de Materias</h2>
            <p class="mb-0 welcome-subtitle">Registra, edita y elimina materias escolares.</p>
        </div>
        <div class="card form-card shadow-sm bg-dark">
            <h4 class="mb-3 text-primary"><i class="bi bi-plus-circle"></i> <?= $editando ? 'Editar Materia' : 'Registrar Nueva Materia' ?></h4>
            <?php if ($exito && !$editando): ?>
                <div class="alert alert-success">¡Materia registrada correctamente!</div>
            <?php elseif (!empty($errores)): ?>
                <div class="alert alert-danger"><ul><?php foreach ($errores as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>
            <form method="post" class="row g-4">
                <input type="hidden" name="id_edit" value="<?= $materia_edit['id'] ?? '' ?>">
                <div class="col-md-6">
                    <label for="nombre" class="form-label">Nombre de la materia</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required value="<?= $materia_edit['nombre'] ?? ($nombre ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <input type="text" class="form-control" id="descripcion" name="descripcion" value="<?= $materia_edit['descripcion'] ?? ($descripcion ?? '') ?>">
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-plus-circle"></i> <?= $editando ? 'Actualizar' : 'Registrar' ?></button>
                    <?php if ($editando): ?>
                        <a href="registrar_materias.php" class="btn btn-secondary ms-2">Cancelar</a>
                    <?php else: ?>
                        <a href="panel_secretario.php" class="btn btn-outline-light ms-2"><i class="bi bi-arrow-left"></i> Volver al panel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <div class="card bg-dark">
            <h4 class="mb-3 text-primary"><i class="bi bi-list"></i> Lista de Materias</h4>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($materias)): ?>
                        <tr><td colspan="4" class="text-center">No hay materias registradas.</td></tr>
                    <?php else: foreach ($materias as $i => $m): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><?= htmlspecialchars($m['nombre']) ?></td>
                            <td><?= htmlspecialchars($m['descripcion']) ?></td>
                            <td>
                                <a href="?editar=<?= $m['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                <a href="?eliminar=<?= $m['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que deseas eliminar esta materia?')"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 