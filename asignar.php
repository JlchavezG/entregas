<?php
require 'config.php';
require 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nueva Entrega</title>
    <link rel="stylesheet" href="assets/css/styles.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <h1 class="page-title"> Nueva Entrega</h1>
        <form method="POST" action="guardar_entrega_mapa.php" id="deliveryForm">
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
                                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <input type="text" name="descripcion" placeholder="Ej. Pedido #1234" required>
                    </div>
                    <div class="form-group">
                        <label>Fecha</label>
                        <input type="date" name="fecha_entrega" required>
                    </div>
                    <div class="form-group">
                        <label>Hora</label>
                        <input type="time" name="hora_entrega" required>
                    </div>
                </div>
                <div class="card" style="flex: 2; min-width: 300px;">
                    <h2 style="font-size: 1.25rem; margin-bottom: 1.25rem; color: var(--text);">Ubicaciones</h2>
                    <div class="form-group">
                        <label>Origen</label>
                        <input type="text" id="origin-input" placeholder="Escribe una dirección..." required>
                        <div id="origin-map" class="map-container">Cargando...</div>
                        <button type="button" class="btn-location" id="origin-location-btn" title="Mi ubicación"><i class="fas fa-location-crosshairs"></i></button>
                        <button type="button" class="btn-clear" id="origin-clear-btn" title="Borrar ubicación"><i class="fas fa-times"></i></button>
                        <input type="hidden" name="direccion_origen" id="origin-address" required>
                        <input type="hidden" name="lat_origen" id="origin-lat" required>
                        <input type="hidden" name="lng_origen" id="origin-lng" required>
                        <div class="validation-error" id="origin-error"></div>
                    </div>
                    <div class="form-group">
                        <label>Destino</label>
                        <input type="text" id="destination-input" placeholder="Escribe una dirección..." required>
                        <div id="destination-map" class="map-container">Cargando...</div>
                        <button type="button" class="btn-location" id="destination-location-btn" title="Mi ubicación"><i class="fas fa-location-crosshairs"></i></button>
                        <button type="button" class="btn-clear" id="destination-clear-btn" title="Borrar ubicación"><i class="fas fa-times"></i></button>
                        <input type="hidden" name="direccion_destino" id="destination-address" required>
                        <input type="hidden" name="lat_destino" id="destination-lat" required>
                        <input type="hidden" name="lng_destino" id="destination-lng" required>
                        <div class="validation-error" id="destination-error"></div>
                    </div>
                </div>
            </div>
            <div class="final-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Asignar Entrega</button>
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
            const defaultCenter = {
                lat: 19.4326,
                lng: -99.1332
            };
            geocoder = new google.maps.Geocoder();
            originMap = new google.maps.Map(document.getElementById('origin-map'), {
                zoom: 12,
                center: defaultCenter
            });
            destinationMap = new google.maps.Map(document.getElementById('destination-map'), {
                zoom: 12,
                center: defaultCenter
            });
            setupAutocomplete('origin-input', originMap, 'origin');
            setupAutocomplete('destination-input', destinationMap, 'destination');
            originMap.addListener('click', e => updateMarker('origin', e.latLng));
            destinationMap.addListener('click', e => updateMarker('destination', e.latLng));
            document.getElementById('origin-location-btn').addEventListener('click', () => getCurrentLocation('origin'));
            document.getElementById('destination-location-btn').addEventListener('click', () => getCurrentLocation('destination'));
            document.getElementById('origin-clear-btn').addEventListener('click', () => clearLocation('origin'));
            document.getElementById('destination-clear-btn').addEventListener('click', () => clearLocation('destination'));
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
                if (place.geometry) updateMarker(type, place.geometry.location, place.formatted_address);
            });
        }

        function updateMarker(type, latLng, address = null) {
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
            map.setCenter(latLng);
            map.setZoom(16);
            document.getElementById(`${type}-lat`).value = latLng.lat();
            document.getElementById(`${type}-lng`).value = latLng.lng();
            document.getElementById(`${type}-error`).style.display = 'none';
            if (address) {
                document.getElementById(`${type}-input`).value = address;
                document.getElementById(`${type}-address`).value = address;
            } else {
                geocoder.geocode({
                    location: latLng
                }, (results, status) => {
                    if (status === 'OK' && results[0]) {
                        const addr = results[0].formatted_address;
                        document.getElementById(`${type}-input`).value = addr;
                        document.getElementById(`${type}-address`).value = addr;
                    }
                });
            }
            newMarker.addListener('dragend', e => updateMarker(type, e.latLng));
        }

        function getCurrentLocation(type) {
            if (!navigator.geolocation) return alert('Geolocalización no soportada.');
            navigator.geolocation.getCurrentPosition(
                pos => updateMarker(type, {
                    lat: pos.coords.latitude,
                    lng: pos.coords.longitude
                }),
                err => alert('Permite el acceso a tu ubicación.'), {
                    enableHighAccuracy: true
                }
            );
        }

        function clearLocation(type) {
            document.getElementById(`${type}-input`).value = '';
            document.getElementById(`${type}-address`).value = '';
            document.getElementById(`${type}-lat`).value = '';
            document.getElementById(`${type}-lng`).value = '';
            const marker = type === 'origin' ? originMarker : destinationMarker;
            if (marker) marker.setMap(null);
            document.getElementById(`${type}-error`).textContent = 'Ubicación requerida';
            document.getElementById(`${type}-error`).style.display = 'block';
        }
        document.getElementById('deliveryForm').addEventListener('submit', function(e) {
            let valid = true;
            ['origin', 'destination'].forEach(type => {
                if (!document.getElementById(`${type}-lat`).value) {
                    document.getElementById(`${type}-error`).textContent = 'Selecciona una ubicación';
                    document.getElementById(`${type}-error`).style.display = 'block';
                    valid = false;
                }
            });
            if (!valid) e.preventDefault();
        });
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=<?= GOOGLE_MAPS_API_KEY ?>&libraries=places`;
        script.onload = () => window.google && google.maps ? initializeMaps() : console.error('Google Maps no cargó');
        document.head.appendChild(script);
    </script>
</body>

</html>