<?php
require_once '../config/db.php';

$municipio_id = $_GET['municipio_id'] ?? 0;

$stmt = $pdo->prepare("
  SELECT z.id, z.numero 
  FROM zona z 
  WHERE z.municipio_id = ?
  ORDER BY z.numero
");
$stmt->execute([$municipio_id]);

$zonas = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($zonas);