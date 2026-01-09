<?php
require 'includes/db.php';
require 'config.php';
$id = (int) ($_GET['id'] ?? 0);
if (!$id) die('ID no válido');
$stmt = $pdo->prepare("SELECT * FROM entregas WHERE id = ?");
$stmt->execute([$id]);
$entrega = $stmt->fetch();
if (!$entrega) die('Entrega no encontrada');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Entrega</title>
    <link rel="stylesheet" href="assets/css/styles.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <h1 class="page-title">✏️ Editar Entrega</h1>
        <form method="POST" action="guardar_edicion.php" id="editForm">
            <input type="hidden" name="id" value="<?= $entrega['id'] ?>">
            <div style="display: flex; gap: 1.5rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
                <div class="card" style="flex: 1; min-width: 300px;">
                    <h2 style="font-size: 1.25rem; margin-bottom: 1.25rem; color: var(--text);">Datos de la Entrega</h2>
                    <div class="form-group">
                        <label>Repartidor</label>
                        <select name="repartidor_id" required>
                            <option value="">Selecciona</option>
                            <?php
                            $stmt = $pdo->query("SELECT id, nombre FROM repartidores ORDER BY nombre");
                            while ($r = $stmt->fetch()):
                            ?>
                                <option value="<?= $r['id'] ?>" <?= $r['id'] == $entrega['repartidor_id'] ? 'selected' : '' ?>><?= htmlspecialchars($r['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <input type="text" name="descripcion" value="<?= htmlspecialchars($entrega['descripcion']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Fecha</label>
                        <input type="date" name="fecha_entrega" value="<?= $entrega['fecha_entrega'] ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Hora</label>
                        <input type="time" name="hora_entrega" value="<?= $entrega['hora_entrega'] ?>" required>
                    </div>
                </div>
                <div class="card" style="flex: 2; min-width: 300px;">
                    <h2 style="font-size: 1.25rem; margin-bottom: 1.25rem; color: var(--text);">Ubicaciones</h2>
                    <div class="form-group">
                        <label>Origen</label>
                        <input type="text" id="origin-input" value="<?= htmlspecialchars($entrega['direccion_origen']) ?>" required>
                        <div id="origin-map" class="map-container">Cargando...</div>
                        <button type="button" class="btn-location" id="origin-location-btn" title="Mi ubicación"><i class="fas fa-location-crosshairs"></i></button>
                        <button type="button" class="btn-clear" id="origin-clear-btn" title="Borrar ubicación"><i class="fas fa-times"></i></button>
                        <input type="hidden" name="direccion_origen" id="origin-address" value="<?= htmlspecialchars($entrega['direccion_origen']) ?>" required>
                        <input type="hidden" name="lat_origen" id="origin-lat" value="<?= $entrega['lat_origen'] ?>" required>
                        <input type="hidden" name="lng_origen" id="origin-lng" value="<?= $entrega['lng_origen'] ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Destino</label>
                        <input type="text" id="destination-input" value="<?= htmlspecialchars($entrega['direccion_destino']) ?>" required>
                        <div id="destination-map" class="map-container">Cargando...</div>
                        <button type="button" class="btn-location" id="destination-location-btn" title="Mi ubicación"><i class="fas fa-location-crosshairs"></i></button>
                        <button type="button" class="btn-clear" id="destination-clear-btn" title="Borrar ubicación"><i class="fas fa-times"></i></button>
                        <input type="hidden" name="direccion_destino" id="destination-address" value="<?= htmlspecialchars($entrega['direccion_destino']) ?>" required>
                        <input type="hidden" name="lat_destino" id="destination-lat" value="<?= $entrega['lat_destino'] ?>" required>
                        <input type="hidden" name="lng_destino" id="destination-lng" value="<?= $entrega['lng_destino'] ?>" required>
                    </div>
                </div>
            </div>
            <div class="final-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Cambios</button>
                <a href="index.php" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
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
        let originMap, destinationMap, originMarker, destinationMarker, geocoder;

        function initializeMaps() {
            const originLat = <?= (float)$entrega['lat_origen'] ?>;
            const originLng = <?= (float)$entrega['lng_origen'] ?>;
            const destLat = <?= (float)$entrega['lat_destino'] ?>;
            const destLng = <?= (float)$entrega['lng_destino'] ?>;
            geocoder = new google.maps.Geocoder();
            originMap = new google.maps.Map(document.getElementById('origin-map'), {
                zoom: 14,
                center: {
                    lat: originLat,
                    lng: originLng
                }
            });
            destinationMap = new google.maps.Map(document.getElementById('destination-map'), {
                zoom: 14,
                center: {
                    lat: destLat,
                    lng: destLng
                }
            });
            placeMarker('origin', {
                lat: originLat,
                lng: originLng
            });
            placeMarker('destination', {
                lat: destLat,
                lng: destLng
            });
            originMap.addListener('click', (e) => updateMarker('origin', e.latLng));
            destinationMap.addListener('click', (e) => updateMarker('destination', e.latLng));
            document.getElementById('origin-location-btn').addEventListener('click', () => getCurrentLocation('origin'));
            document.getElementById('destination-location-btn').addEventListener('click', () => getCurrentLocation('destination'));
            document.getElementById('origin-clear-btn').addEventListener('click', () => clearLocation('origin'));
            document.getElementById('destination-clear-btn').addEventListener('click', () => clearLocation('destination'));
            setupAutocomplete('origin-input', originMap, 'origin');
            setupAutocomplete('destination-input', destinationMap, 'destination');
        }

        function placeMarker(type, latLng) {
            const map = type === 'origin' ? originMap : destinationMap;
            const marker = type === 'origin' ? originMarker : destinationMarker;
            if (marker) marker.setMap(null);
            const newMarker = new google.maps.Marker({
                map: map,
                position: latLng,
                draggable: true
            });
            if (type === 'origin') originMarker = newMarker;
            else destinationMarker = newMarker;
            newMarker.addListener('dragend', (e) => updateMarker(type, e.latLng));
        }

        function updateMarker(type, latLng) {
            placeMarker(type, latLng);
            geocoder.geocode({
                location: latLng
            }, (results, status) => {
                if (status === 'OK' && results[0]) {
                    const addr = results[0].formatted_address;
                    document.getElementById(`${type}-input`).value = addr;
                    document.getElementById(`${type}-address`).value = addr;
                }
                document.getElementById(`${type}-lat`).value = latLng.lat();
                document.getElementById(`${type}-lng`).value = latLng.lng();
            });
        }

        function setupAutocomplete(inputId, map, type) {
            const input = document.getElementById(inputId);
            const autocomplete = new google.maps.places.Autocomplete(input, {
                fields: ['address_components', 'geometry', 'formatted_address'],
                types: ['address']
            });
            autocomplete.bindTo('bounds', map);
            autocomplete.addListener('place_changed', () => {
                const place = autocomplete.getPlace();
                if (place.geometry) updateMarker(type, place.geometry.location);
            });
        }

        function getCurrentLocation(type) {
            if (!navigator.geolocation) {
                alert('Tu navegador no soporta geolocalización.');
                return;
            }
            navigator.geolocation.getCurrentPosition(
                (position) => updateMarker(type, {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                }),
                (error) => alert('Permite el acceso a tu ubicación para usar esta función.')
            );
        }

        function clearLocation(type) {
            document.getElementById(`${type}-input`).value = '';
            document.getElementById(`${type}-address`).value = '';
            document.getElementById(`${type}-lat`).value = '';
            document.getElementById(`${type}-lng`).value = '';
            const marker = type === 'origin' ? originMarker : destinationMarker;
            if (marker) marker.setMap(null);
        }
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=<?= GOOGLE_MAPS_API_KEY ?>&libraries=places`;
        script.onload = () => {
            if (window.google && google.maps) initializeMaps();
            else {
                document.getElementById('origin-map').innerHTML = '❌ Error al cargar Google Maps';
                document.getElementById('destination-map').innerHTML = '❌ Error al cargar Google Maps';
            }
        };
        script.onerror = () => {
            document.getElementById('origin-map').innerHTML = '❌ No se pudo cargar Google Maps';
            document.getElementById('destination-map').innerHTML = '❌ No se pudo cargar Google Maps';
        };
        document.head.appendChild(script);
    </script>
</body>

</html>