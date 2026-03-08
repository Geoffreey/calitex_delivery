<?php
require_once '../config/db.php';

$cliente_id = $_POST['cliente_id'] ?? null;
$tipo = $_POST['tipo'] ?? '';
$calle = $_POST['calle'] ?? '';
$numero = $_POST['numero'] ?? '';
$zona_id = $_POST['zona_id'] ?? '';
$municipio_id = $_POST['municipio_id'] ?? '';
$departamento_id = $_POST['departamento_id'] ?? '';
$referencia = $_POST['referencia'] ?? '';

if (!$cliente_id || !$calle || !$numero || !$zona_id || !$municipio_id || !$departamento_id) {
  http_response_code(400);
  echo "Campos obligatorios faltantes";
  exit;
}

$stmt = $pdo->prepare("
  INSERT INTO direcciones (cliente_id, tipo, calle, numero, zona_id, municipio_id, departamento_id, referencia)
  VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->execute([$cliente_id, $tipo, $calle, $numero, $zona_id, $municipio_id, $departamento_id, $referencia]);

$id = $pdo->lastInsertId();

// Obtener texto formateado
$stmt = $pdo->prepare("
  SELECT z.numero AS zona, m.nombre AS municipio, d.nombre AS departamento 
  FROM zona z
  JOIN municipios m ON z.municipio_id = m.id
  JOIN departamentos d ON d.id = ?
  WHERE z.id = ? AND m.id = ?
");
$stmt->execute([$departamento_id, $zona_id, $municipio_id]);
$data = $stmt->fetch();

echo json_encode([
  'id' => $id,
  'texto' => "{$calle} #{$numero}, Zona {$data['zona']}, {$data['municipio']}, {$data['departamento']}"
]);
