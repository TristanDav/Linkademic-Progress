<?php
session_start();
require_once '../conexion.php';

// Verificar si el padre está logueado
if (!isset($_SESSION['padre_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

// Verificar que se proporcione el ID del alumno
if (!isset($_GET['alumno_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de alumno requerido']);
    exit();
}

$alumno_id = intval($_GET['alumno_id']);
$padre_id = $_SESSION['padre_id'];

// Verificar que el alumno pertenezca al padre
$stmt_verificar = $conexion->prepare("
    SELECT id FROM alumnos 
    WHERE id = ? AND padre_id = ?
");
$stmt_verificar->bind_param('ii', $alumno_id, $padre_id);
$stmt_verificar->execute();
$result_verificar = $stmt_verificar->get_result();

if ($result_verificar->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['error' => 'Alumno no autorizado']);
    exit();
}
$stmt_verificar->close();

// Obtener asistencias del alumno
$stmt_asistencias = $conexion->prepare("
    SELECT fecha, presente
    FROM asistencia 
    WHERE alumno_id = ?
    ORDER BY fecha
");
$stmt_asistencias->bind_param('i', $alumno_id);
$stmt_asistencias->execute();
$result_asistencias = $stmt_asistencias->get_result();

$asistencias = [];
$faltas = [];
$total_dias = 0;
$dias_asistidos = 0;

while ($row = $result_asistencias->fetch_assoc()) {
    $fecha = $row['fecha'];
    $presente = $row['presente'];
    
    if ($presente == 1) {
        $asistencias[] = $fecha;
        $dias_asistidos++;
    } else {
        $faltas[] = $fecha;
    }
    $total_dias++;
}

$stmt_asistencias->close();

// Calcular porcentaje
$porcentaje = $total_dias > 0 ? round(($dias_asistidos / $total_dias) * 100, 1) : 0;

// Preparar respuesta
$response = [
    'asistencias' => $asistencias,
    'faltas' => $faltas,
    'total_dias' => $total_dias,
    'dias_asistidos' => $dias_asistidos,
    'faltas_count' => count($faltas),
    'porcentaje' => $porcentaje
];

header('Content-Type: application/json');
echo json_encode($response);
?> 