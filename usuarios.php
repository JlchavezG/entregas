<?php
require 'includes/db.php';
require 'includes/auth.php';

// Solo sistemas y administración pueden ver esta lista
verificarRol(['sistemas', 'administracion']);

$mensaje = $_GET['mensaje'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Usuarios del Sistema</title>
    <link rel="stylesheet" href="assets/css/styles.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .rol-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .rol-sistemas {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .rol-administracion {
            background: #dcfce7;
            color: #166534;
        }

        .rol-vendedores {
            background: #ffedd5;
            color: #c2410c;
        }

        .rol-clientes {
            background: #f3e8ff;
            color: #7e22ce;
        }
    </style>
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <h1 class="page-title">Usuarios del Sistema</h1>

        <?php if ($mensaje): ?>
            <div class="alert" style="padding:0.75rem 1rem; background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; border-radius:0.375rem; margin-bottom:1.5rem; font-size:0.95rem;">
                <?= htmlspecialchars(urldecode($mensaje)) ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                <h2 style="font-size: 1.25rem; color: var(--text);">Lista de Usuarios</h2>
                <a href="crear_usuario.php" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                    <i class="fas fa-user-plus"></i> Nuevo Usuario
                </a>
            </div>

            <?php
            $stmt = $pdo->query("
                SELECT id, nombre_completo, email, rol, activo, creado_en 
                FROM usuarios 
                ORDER BY rol, nombre_completo
            ");
            $usuarios = $stmt->fetchAll();
            ?>

            <?php if (empty($usuarios)): ?>
                <p style="color: var(--text-muted);">No hay usuarios registrados.</p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: var(--bg-sidebar);">
                                <th style="padding: 0.75rem; text-align: left; font-weight: 600; font-size: 0.9rem;">ID</th>
                                <th style="padding: 0.75rem; text-align: left; font-weight: 600; font-size: 0.9rem;">Nombre</th>
                                <th style="padding: 0.75rem; text-align: left; font-weight: 600; font-size: 0.9rem;">Email</th>
                                <th style="padding: 0.75rem; text-align: left; font-weight: 600; font-size: 0.9rem;">Rol</th>
                                <th style="padding: 0.75rem; text-align: left; font-weight: 600; font-size: 0.9rem;">Estado</th>
                                <th style="padding: 0.75rem; text-align: left; font-weight: 600; font-size: 0.9rem;">Creado</th>
                                <th style="padding: 0.75rem; text-align: left; font-weight: 600; font-size: 0.9rem;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $u): ?>
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 0.75rem; font-size: 0.9rem;"><?= $u['id'] ?></td>
                                    <td style="padding: 0.75rem; font-size: 0.95rem;"><?= htmlspecialchars($u['nombre_completo']) ?></td>
                                    <td style="padding: 0.75rem; font-size: 0.9rem; color: var(--text-muted);"><?= htmlspecialchars($u['email']) ?></td>
                                    <td style="padding: 0.75rem;">
                                        <span class="rol-badge rol-<?= htmlspecialchars($u['rol']) ?>">
                                            <?= htmlspecialchars($u['rol']) ?>
                                        </span>
                                    </td>
                                    <td style="padding: 0.75rem; font-size: 0.9rem;">
                                        <?php if ($u['activo']): ?>
                                            <span style="color: #10b981; font-weight: 600;">Activo</span>
                                        <?php else: ?>
                                            <span style="color: #ef4444; font-weight: 600;">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 0.75rem; font-size: 0.85rem; color: var(--text-muted);">
                                        <?= date('d/m/Y H:i', strtotime($u['creado_en'])) ?>
                                    </td>
                                    <td style="padding: 0.75rem;">
                                        <div style="display: flex; gap: 0.4rem;">
                                            <a href="editar_usuario.php?id=<?= $u['id'] ?>" class="btn btn-outline" style="padding: 0.35rem 0.6rem; font-size: 0.8rem; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($u['id'] !== usuarioActual()['id']): // Evita auto-eliminación 
                                            ?>
                                                <button type="button" class="btn btn-outline"
                                                    style="padding: 0.35rem 0.6rem; font-size: 0.8rem; display: flex; align-items: center; justify-content: center; color: #ef4444; border-color: #fecaca;"
                                                    onclick="eliminarUsuario(<?= $u['id'] ?>, '<?= addslashes(htmlspecialchars($u['nombre_completo'], ENT_QUOTES)) ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        const baseUrl = window.location.origin + window.location.pathname.replace(/[^/]+$/, '');

        // Inicializar sidebar y tema
        function initSidebarAndTheme() {
            const themeToggle = document.getElementById('themeToggle');
            const themeIcon = document.getElementById('themeIcon');
            if (themeToggle && themeIcon) {
                const currentTheme = localStorage.getItem('theme') || 'light';
                document.documentElement.setAttribute('data-theme', currentTheme);
                themeIcon.className = currentTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
                if (!themeToggle.dataset.initialized) {
                    themeToggle.addEventListener('click', () => {
                        const newTheme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                        document.documentElement.setAttribute('data-theme', newTheme);
                        localStorage.setItem('theme', newTheme);
                        themeIcon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
                    });
                    themeToggle.dataset.initialized = 'true';
                }
            }

            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarIcon = document.getElementById('sidebarIcon');
            if (sidebarToggle && sidebarIcon) {
                const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                if (isCollapsed) {
                    document.body.classList.add('collapsed');
                    sidebarIcon.className = 'fas fa-chevron-right';
                }
                if (!sidebarToggle.dataset.initialized) {
                    sidebarToggle.addEventListener('click', () => {
                        document.body.classList.toggle('collapsed');
                        const isNowCollapsed = document.body.classList.contains('collapsed');
                        localStorage.setItem('sidebarCollapsed', isNowCollapsed);
                        sidebarIcon.className = isNowCollapsed ? 'fas fa-chevron-right' : 'fas fa-chevron-left';
                    });
                    sidebarToggle.dataset.initialized = 'true';
                }
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initSidebarAndTheme);
        } else {
            initSidebarAndTheme();
        }

        function eliminarUsuario(id, nombre) {
            if (confirm(`¿Eliminar al usuario "${nombre}"? Esta acción no se puede deshacer.`)) {
                fetch(`${baseUrl}eliminar_usuario.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `id=${encodeURIComponent(id)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.querySelector(`button[onclick*="eliminarUsuario(${id},"]`).closest('tr').remove();
                        } else {
                            alert(data.error || 'No se puede eliminar este usuario.');
                        }
                    })
                    .catch(() => alert('Error de conexión.'));
            }
        }
    </script>
</body>

</html>