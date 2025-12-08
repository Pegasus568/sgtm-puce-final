<?php
// api_horarios.php
require_once 'includes/db.php';

header('Content-Type: application/json');

$docente_id = $_GET['docente_id'] ?? null;
$fecha      = $_GET['fecha'] ?? null; // Formato Y-m-d

if (!$docente_id || !$fecha) {
    echo json_encode([]);
    exit;
}

// 1. Obtener el día de la semana (1 = Lunes, 5 = Viernes)
// Nota: date('N') devuelve 1 para lunes, 7 para domingo.
$dia_semana = date('N', strtotime($fecha));

// 2. Buscar disponibilidad configurada en 'horarios_docentes'
$stmt = $pdo->prepare("SELECT hora_inicio, hora_fin FROM horarios_docentes WHERE docente_id = ? AND dia_semana = ?");
$stmt->execute([$docente_id, $dia_semana]);
$disponibilidad = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Buscar citas ya ocupadas ese día para excluirlas (Opcional, pero recomendado)
$stmt_ocupado = $pdo->prepare("SELECT hora_inicio, hora_fin FROM tutorias WHERE tutor_id = ? AND fecha = ? AND estado IN ('CONFIRMADA', 'PENDIENTE')");
$stmt_ocupado->execute([$docente_id, $fecha]);
$ocupados = $stmt_ocupado->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'disponibles' => $disponibilidad,
    'ocupados'    => $ocupados
]);
?>