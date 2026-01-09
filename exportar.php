<?php
require 'includes/db.php';
$formato = $_GET['formato'] ?? 'pdf';
if ($formato === 'excel') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="entregas_' . date('Y-m-d') . '.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Paquete', 'Repartidor', 'Origen', 'Destino', 'Fecha', 'Hora', 'Estado']);
    $stmt = $pdo->query("SELECT e.*, r.nombre as repartidor_nombre FROM entregas e LEFT JOIN repartidores r ON e.repartidor_id = r.id ORDER BY e.fecha_entrega DESC");
    while ($e = $stmt->fetch()) {
        fputcsv($output, [$e['id'], $e['descripcion'], $e['repartidor_nombre'] ?? '—', $e['direccion_origen'], $e['direccion_destino'], $e['fecha_entrega'], $e['hora_entrega'], ucfirst($e['estado'])]);
    }
    fclose($output);
    exit;
} else {
    header('Content-Type: text/html; charset=utf-8');
    $stmt = $pdo->query("SELECT e.*, r.nombre as repartidor_nombre FROM entregas e LEFT JOIN repartidores r ON e.repartidor_id = r.id ORDER BY e.fecha_entrega DESC");
    $entregas = $stmt->fetchAll();
?>
    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="UTF-8">
        <title>Reporte de Entregas</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 2rem;
            }

            .header {
                text-align: center;
                margin-bottom: 2rem;
            }

            .logo {
                width: 80px;
                height: 80px;
                background: #f97316;
                border-radius: 50%;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: bold;
                font-size: 24px;
                margin-bottom: 0.5rem;
            }

            h1 {
                color: #111827;
                margin: 0;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 1rem;
            }

            th,
            td {
                padding: 0.75rem;
                text-align: left;
                border-bottom: 1px solid #e5e7eb;
            }

            th {
                background: #f9fafb;
                font-weight: 600;
            }

            .status {
                display: inline-block;
                padding: 0.25rem 0.5rem;
                border-radius: 12px;
                font-size: 0.85rem;
                font-weight: 600;
            }

            .pendiente {
                background: #fef3c7;
                color: #92400e;
            }

            .en-ruta {
                background: #dbeafe;
                color: #1e40af;
            }

            .entregado {
                background: #dcfce7;
                color: #166534;
            }

            @media print {
                .no-print {
                    display: none;
                }
            }
        </style>
    </head>

    <body>
        <div class="header">
            <div class="logo">D</div>
            <h1>DeliveryApp - Reporte de Entregas</h1>
            <div><?= date('d/m/Y H:i') ?></div>
        </div>
        <?php if (empty($entregas)): ?>
            <p>No hay entregas.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Paquete</th>
                        <th>Repartidor</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Fecha/Hora</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entregas as $e): ?>
                        <tr>
                            <td><?= $e['id'] ?></td>
                            <td><?= htmlspecialchars($e['descripcion']) ?></td>
                            <td><?= htmlspecialchars($e['repartidor_nombre'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($e['direccion_origen']) ?></td>
                            <td><?= htmlspecialchars($e['direccion_destino']) ?></td>
                            <td><?= $e['fecha_entrega'] ?><br><small><?= $e['hora_entrega'] ?></small></td>
                            <td><span class="status <?= str_replace(' ', '-', $e['estado']) ?>"><?= ucfirst($e['estado']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <div class="no-print" style="margin-top: 2rem; text-align: center;">
            <p>💡 Usa <kbd>Ctrl+P</kbd> y selecciona "Guardar como PDF".</p>
        </div>
    </body>

    </html>
<?php
}
?>