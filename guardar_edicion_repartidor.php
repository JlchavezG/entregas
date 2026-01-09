<?php
//  SIN SALIDA ANTES DE header()
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

require 'includes/db.php';

$id = (int) $_POST['id'];
$nombre = trim($_POST['nombre'] ?? '');

if (!$id || !$nombre) {
    http_response_code(400);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE repartidores SET nombre = ? WHERE id = ?");
    $stmt->execute([$nombre, $id]);
    
    // ✅ Redirección limpia
    header('Location: repartidores.php?mensaje=✅ Repartidor actualizado correctamente.');
    exit;
    
} catch (Exception $e) {
    error_log("Error al actualizar repartidor: " . $e->getMessage());
    http_response_code(500);
    exit;
}
?>