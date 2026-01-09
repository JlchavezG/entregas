<?php
header('Content-Type: application/json');
require 'includes/db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}
$nombre = trim($_POST['nombre'] ?? '');
if (!$nombre) {
    http_response_code(400);
    echo json_encode(['error' => 'Nombre es obligatorio']);
    exit;
}
try {
    $stmt = $pdo->prepare("INSERT INTO repartidores (nombre) VALUES (?)");
    $stmt->execute([$nombre]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al registrar']);
}
?>