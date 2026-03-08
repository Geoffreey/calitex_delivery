<?php
declare(strict_types=1);
ob_start();
ini_set('display_errors', '0');
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

try {
  if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'msg' => 'No autorizado']);
    exit;
  }

  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  if ($id <= 0) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'msg' => 'ID inválido']);
    exit;
  }

  $stmt = $pdo->prepare("
  SELECT
    e.id,
    e.nombre_destinatario,
    e.telefono_destinatario,
    e.descripcion,
    e.pago_envio,
    e.estado_envio,
    e.created_at,
    e.firma,
    e.foto_entrega,
    e.fecha_recibido,
    e.direccion_destino_id,

    (SELECT COALESCE(SUM(ep.monto_cobro),0)
     FROM envios_paquetes ep
     WHERE ep.envio_id = e.id) AS cobro_total,

    TRIM(CONCAT(
      COALESCE(d.calle, ''),
      CASE WHEN d.numero IS NULL OR d.numero = '' THEN '' ELSE CONCAT(' ', d.numero) END,
      CASE WHEN d.referencia IS NULL OR d.referencia = '' THEN '' ELSE CONCAT(', Ref: ', d.referencia) END
    )) AS direccion_texto

  FROM envios e
  LEFT JOIN direcciones d ON d.id = e.direccion_destino_id
  WHERE e.id = ?
  LIMIT 1
");

  $stmt->execute([$id]);
  $e = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$e) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'msg' => 'Envío no encontrado']);
    exit;
  }

  function public_url_from_db_path(?string $dbPath): ?string {
  if (!$dbPath) return null;

  $p = str_replace('\\', '/', trim($dbPath));

  // Quitar ../ (uno o varios al inicio)
  while (strpos($p, '../') === 0) {
    $p = substr($p, 3);
  }

  // Quitar ./ si existiera
  if (strpos($p, './') === 0) {
    $p = substr($p, 2);
  }

  $p = ltrim($p, '/');

  return BASE_URL . '/' . $p;
}


  // Firma/foto: en tu tabla son "firma" y "foto_entrega"
  $firmaUrl = public_url_from_db_path($e['firma'] ?? null);
  $fotoUrl  = public_url_from_db_path($e['foto_entrega'] ?? null);

  $direccionFinal = $e['direccion_texto'] ?? '';
  if ($direccionFinal === '') {
    $direccionFinal = 'ID Dirección: ' . ($e['direccion_destino_id'] ?? '—');
  }

  if (ob_get_length()) ob_clean();

  echo json_encode([
    'ok' => true,
    'data' => [
      'id' => (int)$e['id'],
      'nombre' => $e['nombre_destinatario'] ?? '',
      'telefono' => $e['telefono_destinatario'] ?? '',
      'direccion' => $direccionFinal,
      'descripcion' => $e['descripcion'] ?? '',
      'pago_envio' => $e['pago_envio'] ?? 'cliente',
      'cobro' => (string)($e['cobro_total'] ?? '0.00'),
      'fecha_creacion' => $e['created_at'] ?? null,
      'estado' => $e['estado_envio'] ?? 'pendiente',
      'firma_url' => $firmaUrl,
      'foto_url' => $fotoUrl,
      'fecha_recibido' => $e['fecha_recibido'] ?? null,
    ]
  ]);
  exit;

} catch (Throwable $ex) {
  if (ob_get_length()) ob_clean();
  http_response_code(500);
  echo json_encode(['ok' => false, 'msg' => 'Error interno', 'detail' => $ex->getMessage()]);
  exit;
}
