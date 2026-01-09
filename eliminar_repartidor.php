<?php
header('Content-Type: application/json');
require 'includes/db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}
$id = (int) ($_POST['id'] ?? 0);
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID inválido']);
    exit;
}
try {
    $check = $pdo->prepare("SELECT COUNT(*) FROM entregas WHERE repartidor_id = ?");
    $check->execute([$id]);
    if ($check->fetchColumn() > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'No se puede eliminar: tiene entregas asignadas']);
        exit;
    }
    $stmt = $pdo->prepare("DELETE FROM repartidores WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al eliminar el repartidor']);
}
?>