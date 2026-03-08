<?php
require_once '../tcpdf/tcpdf.php';
require_once '../config/db.php';

if (!isset($_GET['id'])) {
    die("ID de recolección no proporcionado.");
}

$id = intval($_GET['id']);

// Obtener la información de la recolección
$stmt = $pdo->prepare("
    SELECT r.*, 
           u.nombre AS remitente_nombre, 
           u.telefono AS remitente_telefono,
           d1.calle AS origen_calle, d1.numero AS origen_numero, z1.numero AS zona_origen, m1.nombre AS municipio_origen, dp1.nombre AS departamento_origen,
           d2.calle AS destino_calle, d2.numero AS destino_numero, z2.numero AS zona_destino, m2.nombre AS municipio_destino, dp2.nombre AS departamento_destino
    FROM recolecciones r
    JOIN clientes c ON r.cliente_id = c.id
    JOIN users u ON c.user_id = u.id
    JOIN direcciones d1 ON r.direccion_origen_id = d1.id
    JOIN zona z1 ON d1.zona_id = z1.id
    JOIN municipios m1 ON d1.municipio_id = m1.id
    JOIN departamentos dp1 ON d1.departamento_id = dp1.id
    JOIN direcciones d2 ON r.direccion_destino_id = d2.id
    JOIN zona z2 ON d2.zona_id = z2.id
    JOIN municipios m2 ON d2.municipio_id = m2.id
    JOIN departamentos dp2 ON d2.departamento_id = dp2.id
    WHERE r.id = ?
");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die("Recolección no encontrada.");
}

// Construir direcciones
$direccion_origen = "{$data['origen_calle']} #{$data['origen_numero']}, Zona {$data['zona_origen']}, {$data['municipio_origen']}, {$data['departamento_origen']}";
$direccion_destino = "{$data['destino_calle']} #{$data['destino_numero']}, Zona {$data['zona_destino']}, {$data['municipio_destino']}, {$data['departamento_destino']}";

// Crear el PDF
$pdf = new TCPDF();
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);
$pdf->AddPage();
$pdf->SetFont('courier', '', 12);

// Contenido
$html = <<<EOD
<pre>
----------------------------------------
        GUÍA DE RECOLECCIÓN
----------------------------------------
No. de Recolección: {$data['id']}

ORIGEN (Remitente):
Nombre: {$data['remitente_nombre']}
Teléfono: {$data['remitente_telefono']}
Dirección de Recolección: $direccion_origen

DESTINO (Destinatario):
Nombre: {$data['nombre_destinatario']}
Teléfono: {$data['telefono_destinatario']}
Dirección de Entrega: $direccion_destino

Descripción: {$data['descripcion']}

¡Gracias por solicitar tu recolección!
----------------------------------------
</pre>
EOD;

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output("guia_recoleccion_{$data['id']}.pdf", 'I');
?>
