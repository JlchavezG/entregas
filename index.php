<?php
require 'includes/db.php';
$mensaje = $_GET['mensaje'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entregas</title>
    <link rel="stylesheet" href="assets/css/styles.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <h1 class="page-title"> Entregas Asignadas</h1>
        <?php if ($mensaje): ?>
            <div class="alert"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
        <div style="display: flex; gap: 1rem; margin: 1rem 0; flex-wrap: wrap;">
            <button id="exportar-pdf" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                <i class="fas fa-file-pdf"></i> Exportar PDF
            </button>
            <button id="exportar-excel" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </button>
        </div>
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                <h2 style="font-size: 1.25rem; color: var(--text);">Mis Entregas</h2>
                <a href="asignar.php" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                    <i class="fas fa-plus"></i> Nueva
                </a>
            </div>
            <?php
            $stmt = $pdo->query("SELECT e.*, r.nombre as repartidor_nombre FROM entregas e LEFT JOIN repartidores r ON e.repartidor_id = r.id ORDER BY e.fecha_entrega DESC, e.hora_entrega DESC");
            $entregas = $stmt->fetchAll();
            ?>
            <?php if (empty($entregas)): ?>
                <p style="color: var(--text-muted);">No hay entregas registradas.</p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: var(--bg-sidebar);">
                                <th style="padding: 0.75rem; text-align: left; font-weight: 600; font-size: 0.9rem;">Paquete</th>
                                <th style="padding: 0.75rem; text-align: left; font-weight: 600; font-size: 0.9rem;">Repartidor</th>
                                <th style="padding: 0.75rem; text-align: left; font-weight: 600; font-size: 0.9rem;">Origen</th>
                                <th style="padding: 0.75rem; text-align: left; font-weight: 600; font-size: 0.9rem;">Destino</th>
                                <th style="padding: 0.75rem; text-align: left; font-weight: 600; font-size: 0.9rem;">Fecha/Hora</th>
                                <th style="padding: 0.75rem; text-align: left; font-weight: 600; font-size: 0.9rem;">Estado</th>
                                <th style="padding: 0.75rem; text-align: left; font-weight: 600; font-size: 0.9rem;">Acciones</th>
                                <th style="padding: 0.75rem; text-align: left; font-weight: 600; font-size: 0.9rem;">Mapa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($entregas as $e): ?>
                                <tr>
                                    <td style="padding: 0.75rem; font-size: 0.95rem;"><?= htmlspecialchars($e['descripcion']) ?></td>
                                    <td style="padding: 0.75rem; font-size: 0.9rem; color: var(--text-muted);"><?= htmlspecialchars($e['repartidor_nombre'] ?? '—') ?></td>
                                    <td style="padding: 0.75rem; font-size: 0.9rem; color: var(--text-muted);"><?= htmlspecialchars($e['direccion_origen']) ?></td>
                                    <td style="padding: 0.75rem; font-size: 0.9rem; color: var(--text-muted);"><?= htmlspecialchars($e['direccion_destino']) ?></td>
                                    <td style="padding: 0.75rem; font-size: 0.9rem;"><?= $e['fecha_entrega'] ?><br><span style="color: var(--text-muted);"><?= $e['hora_entrega'] ?></span></td>
                                    <td style="padding: 0.75rem;"><span class="status-badge status-<?= str_replace(' ', '-', $e['estado']) ?>"><?= ucfirst($e['estado']) ?></span></td>
                                    <td style="padding: 0.75rem;">
                                        <div style="display: flex; flex-direction: column; gap: 0.5rem; align-items: flex-start;">
                                            <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                                                <span style="font-size: 0.85rem; color: var(--text-muted);">En Ruta</span>
                                                <label class="estado-switch" style="margin: 0;"><input type="checkbox" class="en-ruta-checkbox" data-id="<?= $e['id'] ?>" <?= $e['estado'] === 'en ruta' ? 'checked' : '' ?>><span class="slider"></span></label>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 0.4rem; margin-top: 0.3rem;">
                                                <input type="checkbox" class="entregado-checkbox" data-id="<?= $e['id'] ?>" <?= $e['estado'] === 'entregado' ? 'checked' : '' ?>>
                                                <span style="font-size: 0.85rem; color: var(--text);">Entregado</span>
                                            </div>
                                            <div style="display: flex; gap: 0.4rem; margin-top: 0.6rem; width: 100%;">
                                                <a href="editar_entrega.php?id=<?= $e['id'] ?>" class="btn btn-outline" style="padding: 0.35rem 0.6rem; font-size: 0.8rem; flex: 1; justify-content: center;"><i class="fas fa-edit"></i></a>
                                                <button type="button" class="btn btn-outline" style="padding: 0.35rem 0.6rem; font-size: 0.8rem; flex: 1; justify-content: center; color: #ef4444; border-color: #fecaca;" onclick="confirmarEliminar(<?= $e['id'] ?>)"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding: 0.75rem;"><a href="ver_ruta.php?id=<?= $e['id'] ?>" class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.85rem;"><i class="fas fa-map"></i> Ver Ruta</a></td>
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
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('exportar-pdf')?.addEventListener('click', () => exportar('pdf'));
            document.getElementById('exportar-excel')?.addEventListener('click', () => exportar('excel'));
        });

        function exportar(formato) {
            const params = new URLSearchParams({
                formato: formato
            });
            window.open(`${baseUrl}exportar.php?${params.toString()}`, '_blank');
        }

        function confirmarEliminar(id) {
            if (confirm('¿Estás seguro de eliminar esta entrega? Esta acción no se puede deshacer.')) {
                fetch(`${baseUrl}eliminar_entrega.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `id=${id}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) document.querySelector(`.en-ruta-checkbox[data-id="${id}"]`).closest('tr').remove();
                        else alert('Error al eliminar.');
                    })
                    .catch(() => alert('Error de conexión.'));
            }
        }
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.en-ruta-checkbox').forEach(el => {
                el.addEventListener('change', function() {
                    const id = this.dataset.id;
                    const estado = this.checked ? 'en ruta' : 'pendiente';
                    const badge = this.closest('tr').querySelector('.status-badge');
                    const entregado = this.closest('tr').querySelector('.entregado-checkbox');
                    if (this.checked) entregado.checked = false;
                    if (badge) {
                        badge.className = 'status-badge status-' + estado.replace(' ', '-');
                        badge.textContent = estado.charAt(0).toUpperCase() + estado.slice(1);
                    }
                    fetch(`${baseUrl}actualizar_estado.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `id=${id}&estado=${encodeURIComponent(estado)}`
                    });
                });
            });
            document.querySelectorAll('.entregado-checkbox').forEach(el => {
                el.addEventListener('change', function() {
                    const id = this.dataset.id;
                    const estado = this.checked ? 'entregado' : 'pendiente';
                    const badge = this.closest('tr').querySelector('.status-badge');
                    const enRuta = this.closest('tr').querySelector('.en-ruta-checkbox');
                    if (this.checked) enRuta.checked = false;
                    if (badge) {
                        badge.className = 'status-badge status-' + estado.replace(' ', '-');
                        badge.textContent = estado.charAt(0).toUpperCase() + estado.slice(1);
                    }
                    fetch(`${baseUrl}actualizar_estado.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `id=${id}&estado=${encodeURIComponent(estado)}`
                    });
                });
            });
        });

        function requestNotificationPermission() {
            if (!('Notification' in window)) return;
            if (Notification.permission !== 'granted' && Notification.permission !== 'denied') Notification.requestPermission();
        }

        function checkNotifications() {
            fetch(`${baseUrl}get_notifications.php`)
                .then(res => res.json())
                .then(notifications => {
                    if (Notification.permission === 'granted') {
                        notifications.forEach(n => new Notification(n.title, {
                            body: n.body,
                            tag: n.tag,
                            icon: `${baseUrl}assets/img/logo-icon.png`
                        }));
                    }
                })
                .catch(() => {});
        }
        document.addEventListener('DOMContentLoaded', () => {
            requestNotificationPermission();
            checkNotifications();
            setInterval(checkNotifications, 60000);
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => navigator.serviceWorker.register(`${baseUrl}sw.js`).catch(err => console.error('Error al registrar SW:', err)));
            }
        });
    </script>
</body>

</html>