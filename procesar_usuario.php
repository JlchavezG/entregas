<?php
header('Content-Type: application/json; charset=utf-8');
require 'includes/db.php';
require 'includes/auth.php';

// Solo permitido para ciertos roles
if (!estaLogueado()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}
$usuario = usuarioActual();
if (!in_array($usuario['rol'], ['sistemas', 'administracion'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

$nombre = trim($input['nombre_completo'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$rol = $input['rol'] ?? '';
$repartidor_id = !empty($input['repartidor_id']) ? (int)$input['repartidor_id'] : null;

$errors = [];

// Validaciones
if (!$nombre) $errors['nombre'] = 'Nombre es obligatorio';
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Correo inválido';
if (!$password || strlen($password) < 6) $errors['password'] = 'Contraseña mínima de 6 caracteres';
if (!in_array($rol, ['sistemas', 'administracion', 'vendedores', 'clientes'])) {
    $errors['rol'] = 'Rol no válido';
}

// Verificar email único
if ($email && !$errors['email']) {
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors['email'] = 'Este correo ya está registrado';
    }
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['errors' => $errors]);
    exit;
}

// Hash seguro de la contraseña
$hashedPassword = password_hash($password, PASSWORD_ARGON2ID);

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO usuarios (
            nombre_completo, email, password, rol, repartidor_id, activo, creado_en
        ) VALUES (?, ?, ?, ?, ?, 1, NOW())
    ");

    $stmt->execute([
        $nombre,
        $email,
        $hashedPassword,
        $rol,
        $repartidor_id
    ]);

    $pdo->commit();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Error al crear usuario: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error al guardar el usuario']);
}
exit;