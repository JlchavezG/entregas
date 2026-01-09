<?php
header('Content-Type: application/json');
require 'includes/db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}
$id = (int) $_POST['id'];
$repartidor_id = (int) $_POST['repartidor_id'];
$descripcion = trim($_POST['descripcion']);
$direccion_origen = trim($_POST['direccion_origen']);
$lat_origen = $_POST['lat_origen'];
$lng_origen = $_POST['lng_origen'];
$direccion_destino = trim($_POST['direccion_destino']);
$lat_destino = $_POST['lat_destino'];
$lng_destino = $_POST['lng_destino'];
$fecha = $_POST['fecha_entrega'];
$hora = $_POST['hora_entrega'];
if (!$id || !$repartidor_id || !$descripcion || !$direccion_origen || !$direccion_destino || !$fecha || !$hora) {
    http_response_code(400);
    echo json_encode(['error' => 'Todos los campos son obligatorios.']);
    exit;
}
try {
    $stmt = $pdo->prepare("UPDATE entregas SET repartidor_id = ?, descripcion = ?, direccion_origen = ?, lat_origen = ?, lng_origen = ?, direccion_destino = ?, lat_destino = ?, lng_destino = ?, fecha_entrega = ?, hora_entrega = ? WHERE id = ?");
    $stmt->execute([$repartidor_id, $descripcion, $direccion_origen, $lat_origen, $lng_origen, $direccion_destino, $lat_destino, $lng_destino, $fecha, $hora, $id]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al guardar los cambios.']);
}
?>