<?php
session_start();

function estaLogueado() {
    return isset($_SESSION['usuario_id']);
}

function usuarioActual() {
    if (!estaLogueado()) return null;
    return [
        'id' => $_SESSION['usuario_id'],
        'nombre' => $_SESSION['usuario_nombre'],
        'rol' => $_SESSION['usuario_rol'],
        'repartidor_id' => $_SESSION['repartidor_id'] ?? null
    ];
}

function requerirLogin() {
    if (!estaLogueado()) {
        header('Location: login.php');
        exit;
    }
}

function verificarRol($roles) {
    requerirLogin();
    $usuario = usuarioActual();
    if (!in_array($usuario['rol'], (array)$roles)) {
        http_response_code(403);
        die('Acceso denegado');
    }
    return $usuario;
}

function iniciarSesion($usuario_id, $nombre, $rol, $repartidor_id = null) {
    $_SESSION['usuario_id'] = $usuario_id;
    $_SESSION['usuario_nombre'] = $nombre;
    $_SESSION['usuario_rol'] = $rol;
    if ($repartidor_id) {
        $_SESSION['repartidor_id'] = $repartidor_id;
    }
}

function cerrarSesion() {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>