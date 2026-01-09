<?php
header('Content-Type: application/json');
require 'includes/db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}
$id = (int) $_POST['id'];
$nombre = trim($_POST['nombre']);
if (!$id || !$nombre) {
    http_response_code(400);
    echo json_encode(['error' => 'Nombre inválido']);
    exit;
}
try {
    $stmt = $pdo->prepare("UPDATE repartidores SET nombre = ? WHERE id = ?");
    $stmt->execute([$nombre, $id]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar']);
}
?>