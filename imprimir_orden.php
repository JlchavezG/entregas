<?php
require 'includes/db.php';

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
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Orden de Entrega #<?= htmlspecialchars($entrega['id']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        @media screen {
            body {
                padding: 2rem;
                background: #f9fafb;
            }

            .print-button {
                text-align: center;
                margin-bottom: 2rem;
            }

            .print-button button {
                padding: 0.75rem 1.5rem;
                font-size: 1.1rem;
            }
        }

        @media print {
            body {
                font-family: 'Courier New', monospace;
                margin: 0;
                padding: 0.5rem;
                background: white;
                color: black;
            }

            .no-print {
                display: none !important;
            }

            .page {
                width: 210mm;
                min-height: 297mm;
                padding: 15mm;
                margin: 0 auto;
                border: 1px solid #000;
            }
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 24px;
            margin: 0;
            color: #111827;
        }

        .barcode-container {
            text-align: center;
            margin: 20px 0;
        }

        .barcode {
            height: 50px;
            width: 100%;
            max-width: 300px;
            margin: 0 auto;
        }

        .section {
            margin: 15px 0;
        }

        .section h2 {
            font-size: 16px;
            margin: 0 0 8px 0;
            padding-bottom: 4px;
            border-bottom: 1px dashed #6b7280;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            font-size: 14px;
        }

        .info-item {
            margin-bottom: 5px;
        }

        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 100px;
        }

        .signature-box {
            border: 1px solid #000;
            height: 60px;
            margin-top: 10px;
            position: relative;
        }

        .signature-label {
            position: absolute;
            top: -10px;
            left: 5px;
            background: white;
            padding: 0 3px;
            font-size: 12px;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
</head>

<body>
    <div class="print-button no-print">
        <button onclick="window.print()" style="background: #f97316; color: white; border: none; border-radius: 8px; cursor: pointer;">
            <i class="fas fa-print"></i> Imprimir Orden
        </button>
        <a href="ver_ruta.php?id=<?= $entrega['id'] ?>" style="display: inline-block; margin-top: 10px; color: #f97316; text-decoration: none;">
            ← Volver a la entrega
        </a>
    </div>

    <div class="page">
        <div class="header">
            <h1>ORDEN DE ENTREGA</h1>
            <div style="font-size: 18px; font-weight: bold; margin-top: 5px;">#<?= htmlspecialchars($entrega['id']) ?></div>
        </div>

        <!-- ✅ SOLO CÓDIGO DE BARRAS -->
        <div class="barcode-container">
            <svg class="barcode" jsbarcode-value="<?= str_pad($entrega['id'], 8, '0', STR_PAD_LEFT) ?>"
                jsbarcode-format="CODE128"
                jsbarcode-display-value="true"
                jsbarcode-font-size="14"
                jsbarcode-height="50"
                jsbarcode-text-margin="5"></svg>
        </div>

        <div class="section">
            <h2>DETALLES DE LA ENTREGA</h2>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Paquete:</span>
                    <?= htmlspecialchars($entrega['descripcion']) ?>
                </div>
                <div class="info-item">
                    <span class="info-label">Repartidor:</span>
                    <?= htmlspecialchars($entrega['repartidor_nombre'] ?? '—') ?>
                </div>
                <div class="info-item">
                    <span class="info-label">Fecha/Hora:</span>
                    <?= htmlspecialchars($entrega['fecha_entrega']) ?> <?= htmlspecialchars($entrega['hora_entrega']) ?>
                </div>
                <div class="info-item">
                    <span class="info-label">Estado:</span>
                    <span style="text-transform: capitalize;"><?= htmlspecialchars($entrega['estado']) ?></span>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>ORIGEN</h2>
            <div><?= htmlspecialchars($entrega['direccion_origen']) ?></div>
        </div>

        <div class="section">
            <h2>DESTINO</h2>
            <div><?= htmlspecialchars($entrega['direccion_destino']) ?></div>
        </div>

        <div class="section">
            <h2>CONFIRMACIÓN DE ENTREGA</h2>
            <div class="signature-box">
                <div class="signature-label">FIRMA DEL RECEPTOR</div>
            </div>
            <div style="margin-top: 10px; font-size: 12px; color: #6b7280;">
                Al firmar, confirma que ha recibido el paquete en buen estado.
            </div>
        </div>

        <div class="footer">
            Generado el <?= date('d/m/Y H:i') ?> | DeliveryApp
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            JsBarcode(".barcode").init();
        });
    </script>
</body>

</html>