<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$departamento_id = $_GET['departamento_id'] ?? null;

if (!$departamento_id) {
  echo json_encode([]);
  exit;
}

$stmt = $pdo->prepare("SELECT id, nombre FROM municipios WHERE departamento_id = ?");
$stmt->execute([$departamento_id]);

$municipios = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($municipios);