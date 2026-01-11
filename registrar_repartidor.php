<?php
require 'includes/db.php';
$mensaje_error = $_GET['mensaje'] ?? ''; // por si vienes con error desde redirect (opcional)
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrar Repartidor</title>
    <link rel="stylesheet" href="assets/css/styles.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <h1 class="page-title">Registrar Repartidor</h1>

        <?php if ($mensaje_error): ?>
            <div class="alert" style="background:#fee; color:#c53030; border:1px solid #fecaca; padding:0.75rem 1rem; border-radius:0.375rem; margin-bottom:1.5rem;">
                <?= htmlspecialchars(urldecode($mensaje_error)) ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <form id="formularioRepartidor">
                <div style="margin-bottom: 1.5rem;">
                    <label for="nombre" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text);">Nombre del repartidor</label>
                    <input type="text" id="nombre" name="nombre" required
                        style="width: 100%; padding: 0.6rem 0.8rem; border: 1px solid var(--border); border-radius: 4px; font-size: 1rem;">
                    <div id="errorNombre" style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem; display: none;"></div>
                </div>

                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                    <a href="repartidores.php" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </main>

    <script>
        const baseUrl = window.location.origin + window.location.pathname.replace(/[^/]+$/, '');

        // Inicializar sidebar y tema (igual que en repartidores.php)
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

        // Manejo del formulario con AJAX
        document.getElementById('formularioRepartidor').addEventListener('submit', async function(e) {
            e.preventDefault();

            const nombre = document.getElementById('nombre').value.trim();
            const errorDiv = document.getElementById('errorNombre');
            errorDiv.style.display = 'none';
            errorDiv.textContent = '';

            if (!nombre) {
                errorDiv.textContent = 'El nombre es obligatorio';
                errorDiv.style.display = 'block';
                return;
            }

            const formData = new FormData();
            formData.append('nombre', nombre);

            try {
                const response = await fetch(baseUrl + 'guardar_repartidor.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    // Éxito: redirigir a la lista con mensaje
                    window.location.href = baseUrl + 'repartidores.php?mensaje=Repartidor%20registrado%20correctamente';
                } else {
                    // Mostrar error en la misma página
                    errorDiv.textContent = result.error || 'Error desconocido al guardar';
                    errorDiv.style.display = 'block';
                }
            } catch (err) {
                errorDiv.textContent = 'Error de conexión. Verifica tu red.';
                errorDiv.style.display = 'block';
            }
        });
    </script>
</body>

</html>