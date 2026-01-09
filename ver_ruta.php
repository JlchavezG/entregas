<?php
require 'includes/db.php';
require 'config.php';

$id = (int) ($_GET['id'] ?? 0);
if (!$id) die('ID no válido');

$stmt = $pdo->prepare('
    SELECT e.*, r.nombre as repartidor_nombre 
    FROM entregas e 
    LEFT JOIN repartidores r ON e.repartidor_id = r.id 
    WHERE e.id = ?
');
$stmt->execute([$id]);
$entrega = $stmt->fetch();

if (!$entrega) die('Entrega no encontrada');

$destLat = $entrega['lat_destino'];
$destLng = $entrega['lng_destino'];
$googleMapsUrl = "https://www.google.com/maps/dir/?api=1&destination={$destLat},{$destLng}";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ruta de Entrega #<?= htmlspecialchars($entrega['id']) ?></title>
    <link rel="stylesheet" href="assets/css/styles.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <h1 class="page-title"> Ruta de Entrega #<?= htmlspecialchars($entrega['id']) ?></h1>

        <div class="card">
            <h2><?= htmlspecialchars($entrega['descripcion']) ?></h2>

            <div style="margin: 1.5rem 0;">
                <ol style="padding: 0; margin: 0; list-style: none;">
                    <li style="display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid var(--border);">
                        <span style="color: var(--text-muted); font-size: 0.95rem;">Repartidor</span>
                        <span style="font-weight: 600; color: var(--text);"><?= htmlspecialchars($entrega['repartidor_nombre'] ?? '—') ?></span>
                    </li>
                    <li style="display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid var(--border);">
                        <span style="color: var(--text-muted); font-size: 0.95rem;">Origen</span>
                        <span style="font-weight: 600; color: var(--text);"><?= htmlspecialchars($entrega['direccion_origen']) ?></span>
                    </li>
                    <li style="display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid var(--border);">
                        <span style="color: var(--text-muted); font-size: 0.95rem;">Destino</span>
                        <span style="font-weight: 600; color: var(--text);"><?= htmlspecialchars($entrega['direccion_destino']) ?></span>
                    </li>
                    <li style="display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid var(--border);">
                        <span style="color: var(--text-muted); font-size: 0.95rem;">Hora de Entrega</span>
                        <span style="font-weight: 600; color: var(--text);"><?= htmlspecialchars($entrega['fecha_entrega']) ?> <?= htmlspecialchars($entrega['hora_entrega']) ?></span>
                    </li>
                    <li style="display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid var(--border);">
                        <span style="color: var(--text-muted); font-size: 0.95rem;">Tiempo Estimado</span>
                        <span style="font-weight: 600; color: var(--text);" id="travel-time">Cargando...</span>
                    </li>
                    <li style="display: flex; justify-content: space-between; padding: 0.75rem 0;">
                        <span style="color: var(--text-muted); font-size: 0.95rem;">Estado</span>
                        <span style="font-weight: 600;">
                            <span class="status-badge <?= str_replace(' ', '-', 'status-' . $entrega['estado']) ?>">
                                <?= ucfirst(str_replace('-', ' ', $entrega['estado'])) ?>
                            </span>
                        </span>
                    </li>
                </ol>
            </div>

            <div id="map" style="height: 500px; border-radius: 12px; margin: 1.5rem 0; border: 1px solid var(--border);"></div>

            <div style="display: flex; gap: 1rem; margin-top: 1rem; flex-wrap: wrap;">
                <a href="<?= htmlspecialchars($googleMapsUrl) ?>" target="_blank" class="google-nav-btn">
                    <i class="fas fa-directions"></i> Ir con Google Maps
                </a>
                <!-- ✅ BOTÓN DE IMPRESIÓN AÑADIDO -->
                <a href="imprimir_orden.php?id=<?= $entrega['id'] ?>" target="_blank" class="google-nav-btn" style="background: #10b981;">
                    <i class="fas fa-print"></i> Imprimir Orden
                </a>
            </div>

            <div class="final-actions">
                <a href="index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
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

        function initMap() {
            const map = new google.maps.Map(document.getElementById('map'), {
                zoom: 12,
                center: {
                    lat: <?= (float)$entrega['lat_origen'] ?>,
                    lng: <?= (float)$entrega['lng_origen'] ?>
                },
                styles: document.documentElement.getAttribute('data-theme') === 'dark' ? [{
                        elementType: "geometry",
                        stylers: [{
                            color: "#242f3e"
                        }]
                    },
                    {
                        elementType: "labels.text.stroke",
                        stylers: [{
                            color: "#242f3e"
                        }]
                    },
                    {
                        elementType: "labels.text.fill",
                        stylers: [{
                            color: "#746855"
                        }]
                    },
                    {
                        featureType: "administrative.locality",
                        elementType: "labels.text.fill",
                        stylers: [{
                            color: "#d59563"
                        }]
                    },
                    {
                        featureType: "poi",
                        stylers: [{
                            color: "#242f3e"
                        }]
                    },
                    {
                        featureType: "poi.park",
                        elementType: "geometry",
                        stylers: [{
                            color: "#263c3f"
                        }]
                    },
                    {
                        featureType: "poi.park",
                        elementType: "labels.text.fill",
                        stylers: [{
                            color: "#6b9a76"
                        }]
                    },
                    {
                        featureType: "road",
                        elementType: "geometry",
                        stylers: [{
                            color: "#38414e"
                        }]
                    },
                    {
                        featureType: "road",
                        elementType: "geometry.stroke",
                        stylers: [{
                            color: "#212a37"
                        }]
                    },
                    {
                        featureType: "road",
                        elementType: "labels.text.fill",
                        stylers: [{
                            color: "#9ca5b3"
                        }]
                    },
                    {
                        featureType: "road.highway",
                        elementType: "geometry",
                        stylers: [{
                            color: "#746855"
                        }]
                    },
                    {
                        featureType: "road.highway",
                        elementType: "geometry.stroke",
                        stylers: [{
                            color: "#1f2835"
                        }]
                    },
                    {
                        featureType: "road.highway",
                        elementType: "labels.text.fill",
                        stylers: [{
                            color: "#f3d19c"
                        }]
                    },
                    {
                        featureType: "transit",
                        stylers: [{
                            color: "#2f3948"
                        }]
                    },
                    {
                        featureType: "transit.station",
                        elementType: "labels.text.fill",
                        stylers: [{
                            color: "#d59563"
                        }]
                    },
                    {
                        featureType: "water",
                        elementType: "geometry",
                        stylers: [{
                            color: "#17263c"
                        }]
                    },
                    {
                        featureType: "water",
                        elementType: "labels.text.fill",
                        stylers: [{
                            color: "#515c6d"
                        }]
                    },
                    {
                        featureType: "water",
                        elementType: "labels.text.stroke",
                        stylers: [{
                            color: "#17263c"
                        }]
                    }
                ] : []
            });

            const directionsService = new google.maps.DirectionsService();
            const directionsRenderer = new google.maps.DirectionsRenderer();
            directionsRenderer.setMap(map);

            directionsService.route({
                origin: {
                    lat: <?= (float)$entrega['lat_origen'] ?>,
                    lng: <?= (float)$entrega['lng_origen'] ?>
                },
                destination: {
                    lat: <?= (float)$entrega['lat_destino'] ?>,
                    lng: <?= (float)$entrega['lng_destino'] ?>
                },
                travelMode: google.maps.TravelMode.DRIVING
            }, (response, status) => {
                if (status === 'OK') {
                    directionsRenderer.setDirections(response);
                    const route = response.routes[0];
                    if (route && route.legs[0]) {
                        document.getElementById('travel-time').textContent = route.legs[0].duration.text;
                    }
                } else {
                    document.getElementById('travel-time').textContent = 'No disponible';
                }
            });
        }

        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=<?= GOOGLE_MAPS_API_KEY ?>&callback=initMap`;
        script.async = true;
        document.head.appendChild(script);
    </script>
</body>

</html>