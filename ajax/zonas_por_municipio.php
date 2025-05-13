<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$municipio_id = $_GET['municipio_id'] ?? null;

if (!$municipio_id) {
  echo json_encode([]);
  exit;
}

$stmt = $pdo->prepare("SELECT id, numero FROM zona WHERE municipio_id = ?");
$stmt->execute([$municipio_id]);

$zonas = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($zonas);