<?php
session_start();
require_once '../config/db.php';
require_once '../tcpdf/tcpdf.php'; // Asegúrate que la ruta sea correcta según dónde coloques TCPDF

if (!isset($_GET['id'])) {
  die("ID de envío no proporcionado");
}

$envio_id = (int) $_GET['id'];

// Obtener datos del envío
$stmt = $pdo->prepare("SELECT e.id, e.nombre_destinatario, e.telefono_destinatario, e.descripcion,
  d.calle, d.numero, z.numero AS zona, m.nombre AS municipio, dp.nombre AS departamento
  FROM envios e
  JOIN direcciones d ON e.direccion_destino_id = d.id
  JOIN zona z ON d.zona_id = z.id
  JOIN municipios m ON d.municipio_id = m.id
  JOIN departamentos dp ON d.departamento_id = dp.id
  WHERE e.id = ?");
$stmt->execute([$envio_id]);
$envio = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$envio) {
  die("Envío no encontrado");
}

$direccion = "{$envio['calle']} #{$envio['numero']}, Zona {$envio['zona']}, {$envio['municipio']}, {$envio['departamento']}";

$pdf = new TCPDF();
$pdf->AddPage();

$html = '<h2 style="text-align:center">📨 Guía de Entrega - Envío</h2><hr>';
$html .= '<strong>No. de Guía:</strong> ' . $envio['id'] . '<br>';
$html .= '<strong>Nombre:</strong> ' . htmlspecialchars($envio['nombre_destinatario']) . '<br>';
$html .= '<strong>Teléfono:</strong> ' . htmlspecialchars($envio['telefono_destinatario']) . '<br>';
$html .= '<strong>Dirección:</strong> ' . htmlspecialchars($direccion) . '<br>';
$html .= '<strong>Descripción:</strong> ' . nl2br(htmlspecialchars($envio['descripcion'])) . '<br><br>';
$html .= '<p style="text-align:center">📦 ¡Gracias por usar nuestro servicio!</p>';

$pdf->writeHTML($html);
$pdf->Output('guia_envio_' . $envio['id'] . '.pdf', 'I');
exit;
