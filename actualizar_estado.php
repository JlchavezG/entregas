<?php
header('Content-Type: application/json');
require 'includes/db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}
$id = (int) ($_POST['id'] ?? 0);
$estado = trim($_POST['estado'] ?? '');
if (!$id || !in_array($estado, ['pendiente', 'en ruta', 'entregado'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Estado inválido']);
    exit;
}
try {
    $stmt = $pdo->prepare("UPDATE entregas SET estado = ? WHERE id = ?");
    $stmt->execute([$estado, $id]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar']);
}
?>