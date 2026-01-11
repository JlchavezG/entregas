<?php
require 'includes/db.php';

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear Usuario</title>
    <link rel="stylesheet" href="assets/css/styles.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <h1 class="page-title">Crear Nuevo Usuario</h1>

        <div class="card">
            <form id="usuarioForm">
                <div style="display: grid; gap: 1.25rem;">
                    <!-- Nombre completo -->
                    <div>
                        <label for="nombre_completo" style="display:block; margin-bottom:0.5rem; font-weight:600; color:var(--text);">Nombre completo</label>
                        <input type="text" id="nombre_completo" name="nombre_completo" required
                            style="width:100%; padding:0.6rem 0.8rem; border:1px solid var(--border); border-radius:4px; font-size:1rem;">
                        <div id="errorNombre" style="color:#ef4444; font-size:0.875rem; margin-top:0.25rem; display:none;"></div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" style="display:block; margin-bottom:0.5rem; font-weight:600; color:var(--text);">Correo electrónico</label>
                        <input type="email" id="email" name="email" required
                            style="width:100%; padding:0.6rem 0.8rem; border:1px solid var(--border); border-radius:4px; font-size:1rem;">
                        <div id="errorEmail" style="color:#ef4444; font-size:0.875rem; margin-top:0.25rem; display:none;"></div>
                    </div>

                    <!-- Contraseña -->
                    <div>
                        <label for="password" style="display:block; margin-bottom:0.5rem; font-weight:600; color:var(--text);">Contraseña</label>
                        <input type="password" id="password" name="password" required minlength="6"
                            style="width:100%; padding:0.6rem 0.8rem; border:1px solid var(--border); border-radius:4px; font-size:1rem;">
                        <div id="errorPassword" style="color:#ef4444; font-size:0.875rem; margin-top:0.25rem; display:none;"></div>
                    </div>

                    <!-- Rol -->
                    <div>
                        <label for="rol" style="display:block; margin-bottom:0.5rem; font-weight:600; color:var(--text);">Rol</label>
                        <select id="rol" name="rol" required
                            style="width:100%; padding:0.6rem 0.8rem; border:1px solid var(--border); border-radius:4px; font-size:1rem; background:white;">
                            <option value="">-- Selecciona un rol --</option>
                            <option value="sistemas">Sistemas</option>
                            <option value="administracion">Administración</option>
                            <option value="vendedores">Vendedores</option>
                            <option value="clientes">Clientes</option>
                        </select>
                        <div id="errorRol" style="color:#ef4444; font-size:0.875rem; margin-top:0.25rem; display:none;"></div>
                    </div>

                    <!-- Repartidor (solo visible si aplica) -->
                    <div id="campoRepartidor" style="display:none;">
                        <label for="repartidor_id" style="display:block; margin-bottom:0.5rem; font-weight:600; color:var(--text);">Repartidor asignado</label>
                        <select id="repartidor_id" name="repartidor_id"
                            style="width:100%; padding:0.6rem 0.8rem; border:1px solid var(--border); border-radius:4px; font-size:1rem; background:white;">
                            <option value="">-- Sin repartidor --</option>
                            <?php
                            $stmt = $pdo->query("SELECT id, nombre FROM repartidores ORDER BY nombre");
                            while ($r = $stmt->fetch()):
                            ?>
                                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Botones -->
                    <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
                        <button type="submit" class="btn btn-primary" style="padding:0.5rem 1rem; font-size:0.9rem;">
                            <i class="fas fa-user-plus"></i> Crear Usuario
                        </button>
                        <a href="usuarios.php" class="btn btn-outline" style="padding:0.5rem 1rem; font-size:0.9rem;">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <script>
        const baseUrl = window.location.origin + window.location.pathname.replace(/[^/]+$/, '');

        // Inicializar sidebar y tema (igual que en otros archivos)
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

        // Mostrar/ocultar campo de repartidor (opcional: solo si necesitas vincular)
        document.getElementById('rol').addEventListener('change', function() {
            // En tu caso actual, ninguno de los roles requiere repartidor,
            // pero dejamos la lógica por si en el futuro agregas "repartidor" como rol.
            const campo = document.getElementById('campoRepartidor');
            if (this.value === 'repartidor') {
                campo.style.display = 'block';
            } else {
                campo.style.display = 'none';
                document.getElementById('repartidor_id').value = '';
            }
        });

        // Envío del formulario
        document.getElementById('usuarioForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            // Limpiar errores
            ['Nombre', 'Email', 'Password', 'Rol'].forEach(field => {
                document.getElementById('error' + field).style.display = 'none';
            });

            const data = {
                nombre_completo: document.getElementById('nombre_completo').value.trim(),
                email: document.getElementById('email').value.trim(),
                password: document.getElementById('password').value,
                rol: document.getElementById('rol').value,
                repartidor_id: document.getElementById('repartidor_id')?.value || null
            };

            // Validaciones simples
            let hasError = false;
            if (!data.nombre_completo) {
                document.getElementById('errorNombre').textContent = 'El nombre es obligatorio';
                document.getElementById('errorNombre').style.display = 'block';
                hasError = true;
            }
            if (!data.email) {
                document.getElementById('errorEmail').textContent = 'El correo es obligatorio';
                document.getElementById('errorEmail').style.display = 'block';
                hasError = true;
            }
            if (!data.password || data.password.length < 6) {
                document.getElementById('errorPassword').textContent = 'La contraseña debe tener al menos 6 caracteres';
                document.getElementById('errorPassword').style.display = 'block';
                hasError = true;
            }
            if (!data.rol) {
                document.getElementById('errorRol').textContent = 'Selecciona un rol';
                document.getElementById('errorRol').style.display = 'block';
                hasError = true;
            }

            if (hasError) return;

            try {
                const response = await fetch(baseUrl + 'procesar_usuario.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    window.location.href = baseUrl + 'usuarios.php?mensaje=Usuario%20creado%20correctamente';
                } else {
                    const errors = result.errors || {};
                    for (const [field, msg] of Object.entries(errors)) {
                        const el = document.getElementById('error' + field.charAt(0).toUpperCase() + field.slice(1));
                        if (el) {
                            el.textContent = msg;
                            el.style.display = 'block';
                        }
                    }
                    if (!Object.keys(errors).length) {
                        alert(result.error || 'Error desconocido al crear el usuario');
                    }
                }
            } catch (err) {
                alert('Error de conexión. Verifica tu red.');
            }
        });
    </script>
</body>

</html>