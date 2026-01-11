<?php
header('Content-Type: application/json; charset=utf-8');
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$nombre = trim($_POST['nombre'] ?? '');

if (!$nombre) {
    http_response_code(400);
    echo json_encode(['error' => 'El nombre es obligatorio']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO repartidores (nombre) VALUES (?)");
    $stmt->execute([$nombre]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log("Error al registrar repartidor: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error al guardar el repartidor']);
}
exit;