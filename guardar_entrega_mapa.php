<?php
header('Content-Type: application/json');
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$repartidor_id = (int) $_POST['repartidor_id'];
$descripcion = trim($_POST['descripcion']);
$direccion_origen = trim($_POST['direccion_origen']);
$lat_origen = $_POST['lat_origen'];
$lng_origen = $_POST['lng_origen'];
$direccion_destino = trim($_POST['direccion_destino']);
$lat_destino = $_POST['lat_destino'];
$lng_destino = $_POST['lng_destino'];
$fecha_entrega = $_POST['fecha_entrega'];
$hora_entrega = $_POST['hora_entrega'];

if (!$repartidor_id || !$descripcion || !$direccion_origen || !$direccion_destino || !$fecha_entrega || !$hora_entrega) {
    http_response_code(400);
    echo json_encode(['error' => 'Todos los campos son obligatorios.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO entregas (
            repartidor_id, descripcion, 
            direccion_origen, lat_origen, lng_origen,
            direccion_destino, lat_destino, lng_destino,
            fecha_entrega, hora_entrega, estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')
    ");

    $result = $stmt->execute([
        $repartidor_id,
        $descripcion,
        $direccion_origen, $lat_origen, $lng_origen,
        $direccion_destino, $lat_destino, $lng_destino,
        $fecha_entrega, $hora_entrega
    ]);

    if (!$result) {
        throw new Exception('Error al ejecutar la consulta');
    }

    // Enviar notificación
    require 'includes/notifications.php';
    sendWebNotification(
        'Nueva Entrega Asignada', 
        "Fecha: {$fecha_entrega} | Hora: {$hora_entrega}",
        'new-delivery'
    );

    // Redirigir con mensaje de éxito
    header('Location: index.php?mensaje=✅ Entrega asignada correctamente.');
    exit;

} catch (Exception $e) {
    error_log("Error al guardar entrega: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error al guardar la entrega.']);
    exit;
}
?>