<?php
// Mostrar errores solo para pruebas
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'includes/db.php';
require 'includes/auth.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Prueba de Autenticación</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f9fafb;
            margin: 0;
            padding: 2rem;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .status {
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 8px;
            font-weight: 600;
        }
        .success {
            background: #dcfce7;
            color: #166534;
        }
        .error {
            background: #fee2e2;
            color: #b91c1c;
        }
        .diagnostic {
            margin-top: 2rem;
            padding: 1.5rem;
            background: #f3f4f6;
            border-radius: 8px;
        }
        .diagnostic h2 {
            margin-top: 0;
            color: #1f2937;
        }
        .diagnostic ul {
            padding-left: 1.5rem;
        }
        .diagnostic li {
            margin: 0.5rem 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Prueba de Autenticación - Fase 1</h1>
        
        <?php if (estaLogueado()): ?>
            <div class="status success">
                ✅ ¡Estás logueado como: <strong><?= htmlspecialchars(usuarioActual()['nombre']) ?></strong><br>
                Rol: <strong><?= htmlspecialchars(usuarioActual()['rol']) ?></strong>
            </div>
            <p><a href="logout.php" style="display: inline-block; padding: 0.5rem 1rem; background: #f97316; color: white; text-decoration: none; border-radius: 6px;">Cerrar sesión</a></p>
        <?php else: ?>
            <div class="status error">
                ❌ No estás logueado.
            </div>
            <p><a href="login.php" style="display: inline-block; padding: 0.5rem 1rem; background: #f97316; color: white; text-decoration: none; border-radius: 6px;">Iniciar sesión</a></p>
        <?php endif; ?>
        
        <div class="diagnostic">
            <h2>🔍 Diagnóstico del Sistema</h2>
            <ul>
                <li><strong>Versión de PHP:</strong> <?= phpversion() ?></li>
                <li><strong>Sesión activa:</strong> <?= session_status() === PHP_SESSION_ACTIVE ? 'Sí' : 'No' ?></li>
                <li><strong>Conexión a BD:</strong> <?= isset($pdo) ? 'Sí' : 'No' ?></li>
                <li><strong>Archivo auth.php cargado:</strong> Sí</li>
                <li><strong>Ruta actual:</strong> <?= $_SERVER['SCRIPT_NAME'] ?></li>
            </ul>
        </div>
        
        <div style="margin-top: 2rem; padding: 1rem; background: #fffbeb; border-left: 4px solid #f59e0b;">
            <h3> Instrucciones</h3>
            <p>1. Si ves este mensaje, la Fase 1 está funcionando correctamente.</p>
            <p>2. Haz clic en "Iniciar sesión" y usa las credenciales de prueba.</p>
            <p>3. Tu sistema existente (<code>index.php</code>, etc.) debe seguir funcionando sin cambios.</p>
        </div>
    </div>
</body>
</html>