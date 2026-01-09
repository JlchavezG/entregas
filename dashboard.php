<?php
require 'includes/db.php';

$total_entregas = $pdo->query("SELECT COUNT(*) FROM entregas")->fetchColumn();
$entregas_activas = $pdo->query("SELECT COUNT(*) FROM entregas WHERE estado IN ('pendiente', 'en ruta')")->fetchColumn();
$entregas_terminadas = $pdo->query("SELECT COUNT(*) FROM entregas WHERE estado = 'entregado'")->fetchColumn();
$total_repartidores = $pdo->query("SELECT COUNT(*) FROM repartidores")->fetchColumn();

// === ENTREGAS POR REPARTIDOR ===
$entregas_por_repartidor = [];
$stmt = $pdo->query("
    SELECT r.nombre, COUNT(e.id) as total
    FROM repartidores r
    LEFT JOIN entregas e ON r.id = e.repartidor_id
    GROUP BY r.id, r.nombre
    ORDER BY total DESC
");
while ($row = $stmt->fetch()) {
    $entregas_por_repartidor[$row['nombre']] = (int)$row['total'];
}

// === TIEMPO PROMEDIO DE ENTREGA (últimos 30 días) ===
$tiempo_promedio = [];
for ($i = 29; $i >= 0; $i--) {
    $fecha = date('Y-m-d', strtotime("-$i days"));
    $stmt = $pdo->prepare("
        SELECT AVG(TIMESTAMPDIFF(MINUTE, 
            CONCAT(fecha_entrega, ' ', hora_entrega), 
            NOW()
        )) as avg_minutes
        FROM entregas 
        WHERE estado = 'entregado' 
        AND fecha_entrega = ?
    ");
    $stmt->execute([$fecha]);
    $avg = $stmt->fetchColumn();
    $tiempo_promedio[] = [
        'fecha' => $fecha,
        'promedio' => $avg ? round((int)$avg / 60, 1) : 0 // en horas
    ];
}

// === CALENDARIO DE ENTREGAS (últimos 7 días) ===
$calendario_semanal = [];
$dias_semana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
for ($i = 6; $i >= 0; $i--) {
    $fecha = date('Y-m-d', strtotime("-$i days"));
    $dia_semana = $dias_semana[date('w', strtotime($fecha))];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM entregas WHERE fecha_entrega = ?");
    $stmt->execute([$fecha]);
    $total = (int)$stmt->fetchColumn();
    
    $pendientes = (int)$pdo->prepare("SELECT COUNT(*) FROM entregas WHERE fecha_entrega = ? AND estado = 'pendiente'")->execute([$fecha]) ? $pdo->prepare("SELECT COUNT(*) FROM entregas WHERE fecha_entrega = ? AND estado = 'pendiente'")->fetchColumn() : 0;
    $en_ruta = (int)$pdo->prepare("SELECT COUNT(*) FROM entregas WHERE fecha_entrega = ? AND estado = 'en ruta'")->execute([$fecha]) ? $pdo->prepare("SELECT COUNT(*) FROM entregas WHERE fecha_entrega = ? AND estado = 'en ruta'")->fetchColumn() : 0;
    $entregado = (int)$pdo->prepare("SELECT COUNT(*) FROM entregas WHERE fecha_entrega = ? AND estado = 'entregado'")->execute([$fecha]) ? $pdo->prepare("SELECT COUNT(*) FROM entregas WHERE fecha_entrega = ? AND estado = 'entregado'")->fetchColumn() : 0;
    
    $calendario_semanal[] = [
        'dia' => $dia_semana,
        'fecha' => $fecha,
        'total' => $total,
        'pendientes' => $pendientes,
        'en_ruta' => $en_ruta,
        'entregado' => $entregado
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/css/styles.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <h1 class="page-title">  Dashboard Operativo</h1>

        <!-- === MÉTRICAS PRINCIPALES === -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div class="card" style="text-align: center; padding: 1.25rem;">
                <div style="font-size: 2rem; font-weight: 700; color: var(--primary);"><?= $total_entregas ?></div>
                <div style="color: var(--text-muted); font-size: 0.95rem;">Total Entregas</div>
            </div>
            <div class="card" style="text-align: center; padding: 1.25rem;">
                <div style="font-size: 2rem; font-weight: 700; color: #f59e0b;"><?= $entregas_activas ?></div>
                <div style="color: var(--text-muted); font-size: 0.95rem;">Entregas Activas</div>
            </div>
            <div class="card" style="text-align: center; padding: 1.25rem;">
                <div style="font-size: 2rem; font-weight: 700; color: #10b981;"><?= $entregas_terminadas ?></div>
                <div style="color: var(--text-muted); font-size: 0.95rem;">Entregas Terminadas</div>
            </div>
            <div class="card" style="text-align: center; padding: 1.25rem;">
                <div style="font-size: 2rem; font-weight: 700; color: #8b5cf6;"><?= $total_repartidores ?></div>
                <div style="color: var(--text-muted); font-size: 0.95rem;">Repartidores</div>
            </div>
        </div>

        <!-- === GRÁFICAS AVANZADAS === -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <!-- Entregas por repartidor -->
            <div class="card">
                <h2 style="font-size: 1.1rem; margin-bottom: 1rem; color: var(--text);">📦 Entregas por Repartidor</h2>
                <canvas id="repartidoresChart" height="250"></canvas>
            </div>

            <!-- Tiempo promedio de entrega -->
            <div class="card">
                <h2 style="font-size: 1.1rem; margin-bottom: 1rem; color: var(--text);">⏱️ Tiempo Promedio (últimos 30 días)</h2>
                <canvas id="tiempoChart" height="250"></canvas>
            </div>
        </div>

        <!-- === CALENDARIO SEMANAL === -->
        <div class="card">
            <h2 style="font-size: 1.1rem; margin-bottom: 1rem; color: var(--text);">📅 Calendario de Entregas (Últimos 7 días)</h2>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--bg-sidebar);">
                            <?php foreach ($calendario_semanal as $dia): ?>
                                <th style="padding: 0.75rem; text-align: center; font-weight: 600; font-size: 0.9rem;">
                                    <?= $dia['dia'] ?><br>
                                    <small style="font-weight: normal;"><?= date('d/m', strtotime($dia['fecha'])) ?></small>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <?php foreach ($calendario_semanal as $dia): ?>
                                <td style="padding: 0.75rem; text-align: center; vertical-align: top;">
                                    <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?= $dia['total'] ?></div>
                                    <div style="font-size: 0.85rem; display: flex; flex-direction: column; gap: 0.1rem;">
                                        <span style="color: #f97316;">Pend: <?= $dia['pendientes'] ?></span>
                                        <span style="color: #f59e0b;">Ruta: <?= $dia['en_ruta'] ?></span>
                                        <span style="color: #10b981;">Ent: <?= $dia['entregado'] ?></span>
                                    </div>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- === BOTONES DE EXPORTACIÓN === -->
        <div style="display: flex; gap: 1rem; margin-top: 1.5rem; flex-wrap: wrap;">
            <a href="exportar.php?formato=pdf" class="btn btn-outline" target="_blank" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                <i class="fas fa-file-pdf"></i> Reporte PDF Completo
            </a>
            <a href="exportar.php?formato=excel" class="btn btn-outline" target="_blank" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                <i class="fas fa-file-excel"></i> Exportar Datos
            </a>
        </div>
    </main>

    <script>
    // --- Ruta base dinámica ---
    const baseUrl = window.location.origin + window.location.pathname.replace(/[^/]+$/, '');

    // --- Sidebar y modo oscuro ---
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

    // --- GRÁFICAS AVANZADAS ---
    document.addEventListener('DOMContentLoaded', () => {
        // --- Entregas por repartidor ---
        const ctx1 = document.getElementById('repartidoresChart').getContext('2d');
        const repartidoresData = <?= json_encode($entregas_por_repartidor) ?>;
        const repartidoresLabels = Object.keys(repartidoresData);
        const repartidoresValues = Object.values(repartidoresData);

        new Chart(ctx1, {
            type: 'bar',
            data: {  // ✅ CORREGIDO: añadido "data"
                labels: repartidoresLabels,
                datasets: [{
                    label: 'Entregas',
                    data: repartidoresValues,  // ✅ CORREGIDO: "data" en lugar de array directo
                    backgroundColor: '#f97316',
                    borderColor: '#ea580c',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (ctx) => `${ctx.parsed.y} entregas` } }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: 'var(--text-muted)' },
                        grid: { color: 'var(--border)' }
                    },
                    x: {
                        ticks: { color: 'var(--text-muted)', maxRotation: 45, minRotation: 45 }
                    }
                }
            }
        });

        // --- Tiempo promedio de entrega ---
        const ctx2 = document.getElementById('tiempoChart').getContext('2d');
        const tiempoData = <?= json_encode(array_column($tiempo_promedio, 'promedio')) ?>;
        const tiempoLabels = <?= json_encode(array_column($tiempo_promedio, 'fecha')) ?>;

        new Chart(ctx2, {
            type: 'line',
            data: {  // ✅ CORREGIDO: añadido "data"
                labels: tiempoLabels,
                datasets: [{
                    label: 'Horas promedio',
                    data: tiempoData,  // ✅ CORREGIDO: "data"
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (ctx) => `${ctx.parsed.y} horas` } }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: 'var(--text-muted)' },
                        grid: { color: 'var(--border)' }
                    },
                    x: {
                        ticks: { color: 'var(--text-muted)', maxRotation: 0, minRotation: 0 }
                    }
                }
            }
        });
    });

    // --- NOTIFICACIONES (mantener funcionalidad) ---
    function requestNotificationPermission() {
        if (!('Notification' in window)) return;
        if (Notification.permission !== 'granted' && Notification.permission !== 'denied') {
            Notification.requestPermission();
        }
    }

    function checkNotifications() {
        fetch(`${baseUrl}get_notifications.php`)
            .then(res => res.json())
            .then(notifications => {
                if (Notification.permission === 'granted') {
                    notifications.forEach(n => {
                        new Notification(n.title, {
                            body: n.body,
                            tag: n.tag,
                            icon: `${baseUrl}assets/img/logo-icon.png`
                        });
                    });
                }
            })
            .catch(() => {});
    }

    document.addEventListener('DOMContentLoaded', () => {
        requestNotificationPermission();
        checkNotifications();
        setInterval(checkNotifications, 60000);

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register(`${baseUrl}sw.js`)
                    .catch(err => console.error('Error al registrar SW:', err));
            });
        }
    });
    </script>
</body>
</html>