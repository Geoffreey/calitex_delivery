<?php
session_start();
require_once __DIR__ . '/../config/db.php';
// ✅ TCPDF (ajustá ruta si lo tenés en otra carpeta)
require_once __DIR__ . '/../tcpdf/tcpdf.php';

// Seguridad básica
if (!isset($_SESSION['user_id']) || ($_SESSION['rol'] ?? '') !== 'piloto') {
  http_response_code(403);
  exit('Acceso denegado');
}

$piloto_id = (int)$_SESSION['user_id'];
$envio_id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($envio_id <= 0) {
  http_response_code(400);
  exit('ID inválido');
}

// 1) Datos generales del envío
$stmt = $pdo->prepare("
  SELECT e.id, e.nombre_destinatario, e.telefono_destinatario, e.descripcion, e.pago_envio,
         d.calle, d.numero,
         z.numero AS zona,
         m.nombre AS municipio,
         dp.nombre AS departamento
  FROM envios e
  LEFT JOIN direcciones d ON d.id = e.direccion_destino_id
  LEFT JOIN zona z ON z.id = d.zona_id
  LEFT JOIN municipios m ON m.id = d.municipio_id
  LEFT JOIN departamentos dp ON dp.id = d.departamento_id
  WHERE e.id = ? AND e.piloto_id = ?
  LIMIT 1
");
$stmt->execute([$envio_id, $piloto_id]);
$e = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$e) {
  http_response_code(404);
  exit('Envío no encontrado o no pertenece al piloto');
}

$direccion = '';
if (!empty($e['calle'])) {
  $direccion = "{$e['calle']} #{$e['numero']}, Zona {$e['zona']}, {$e['municipio']}, {$e['departamento']}";
}

// 2) Cálculo cobros/paquetes
// envios_paquetes tiene 1 fila por unidad (según tu lógica), por eso count(*) = qty
$stmt = $pdo->prepare("
  SELECT ep.paquete_id,
         COUNT(*) AS qty,
         SUM(ep.monto_cobro) AS extras,
         MAX(p.tarifa) AS tarifa
  FROM envios_paquetes ep
  JOIN paquetes p ON p.id = ep.paquete_id
  WHERE ep.envio_id = ?
  GROUP BY ep.paquete_id
");
$stmt->execute([$envio_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalExtras = 0.0;
$totalTarifa = 0.0;

foreach ($rows as $r) {
  $qty    = (int)$r['qty'];
  $extras = (float)$r['extras'];
  $tarifa = (float)$r['tarifa'];
  $totalExtras += $extras;
  $totalTarifa += ($qty * $tarifa);
}

// Regla: tarifa solo se suma si paga destinatario
$totalCobro = $totalExtras;
$pago_texto = ($e['pago_envio'] === 'destinatario') ? 'Cobro contra entrega' : 'Cobro a mi cuenta';

if ($e['pago_envio'] === 'destinatario') {
  $totalCobro += $totalTarifa;
}

// 3) Texto tipo ticket
$texto = "----------------------------------------\n";
$texto .= "          📨 GUÍA DE ENTREGA - ENVÍO\n";
$texto .= "----------------------------------------\n";
$texto .= "No. de Guía: {$e['id']}\n";
$texto .= "Nombre: {$e['nombre_destinatario']}\n";
$texto .= "Teléfono: {$e['telefono_destinatario']}\n";
$texto .= "Dirección: {$direccion}\n";
$texto .= "Descripción: " . ($e['descripcion'] ?? '') . "\n";
$texto .= "Forma de pago del envío: {$pago_texto}\n";
$texto .= "Cobro total al cliente: Q" . number_format($totalCobro, 2) . "\n\n";
$texto .= "📦 ¡Gracias por usar nuestro servicio!\n";
$texto .= "----------------------------------------\n";

// 4) Crear PDF (TCPDF)
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('CaliTex Delivery');
$pdf->SetAuthor('CaliTex');
$pdf->SetTitle('Guía de Envío #' . $envio_id);
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 10);
$pdf->AddPage();

// Fuente monospace similar a ticket
$pdf->SetFont('courier', '', 10);

// MultiCell respeta saltos de línea
$pdf->MultiCell(0, 0, $texto, 0, 'L', false, 1, '', '', true);

// Forzar descarga
$pdf->Output("guia_envio_{$envio_id}.pdf", 'D');
exit;
