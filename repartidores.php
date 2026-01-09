<?php
require 'includes/db.php';
$mensaje = $_GET['mensaje'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Repartidores</title>
    <link rel="stylesheet" href="assets/css/styles.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <h1 class="page-title"> Repartidores</h1>
        <?php if ($mensaje): ?>
            <div class="alert"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                <h2 style="font-size: 1.25rem; color: var(--text);">Lista de Repartidores</h2>
                <a href="registrar_repartidor.php" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                    <i class="fas fa-plus"></i> Nuevo
                </a>
            </div>
            <?php
            $stmt = $pdo->query("SELECT * FROM repartidores ORDER BY nombre");
            $repartidores = $stmt->fetchAll();
            ?>
            <?php if (empty($repartidores)): ?>
                <p style="color: var(--text-muted);">No hay repartidores registrados.</p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: var(--bg-sidebar);">
                                <th style="padding: 0.75rem; text-align: left; font-weight: 600; font-size: 0.9rem;">ID</th>
                                <th style="padding: 0.75rem; text-align: left; font-weight: 600; font-size: 0.9rem;">Nombre</th>
                                <th style="padding: 0.75rem; text-align: left; font-weight: 600; font-size: 0.9rem;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($repartidores as $r): ?>
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 0.75rem; font-size: 0.95rem;"><?= $r['id'] ?></td>
                                    <td style="padding: 0.75rem; font-size: 0.95rem;"><?= htmlspecialchars($r['nombre']) ?></td>
                                    <td style="padding: 0.75rem;">
                                        <div style="display: flex; gap: 0.4rem;">
                                            <a href="editar_repartidor.php?id=<?= $r['id'] ?>" class="btn btn-outline" style="padding: 0.35rem 0.6rem; font-size: 0.8rem; display: flex; align-items: center; justify-content: center;"><i class="fas fa-edit"></i></a>
                                            <button type="button" class="btn btn-outline" style="padding: 0.35rem 0.6rem; font-size: 0.8rem; display: flex; align-items: center; justify-content: center; color: #ef4444; border-color: #fecaca;" onclick="eliminarRepartidor(<?= $r['id'] ?>, '<?= addslashes($r['nombre']) ?>')"><i class="fas fa-trash"></i></button>
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
                0
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

        function eliminarRepartidor(id, nombre) {
            if (confirm(`¿Eliminar al repartidor "${nombre}"? Esta acción no se puede deshacer.`)) {
                fetch(`${baseUrl}eliminar_repartidor.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `id=${id}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) document.querySelector(`button[onclick*="eliminarRepartidor(${id},"]`).closest('tr').remove();
                        else alert('No se puede eliminar: el repartidor tiene entregas asignadas.');
                    })
                    .catch(() => alert('Error de conexión.'));
            }
        }
    </script>
</body>

</html>