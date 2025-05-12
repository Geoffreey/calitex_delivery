<?php
require_once '../config/db.php';

$departamento_id = $_GET['departamento_id'] ?? 0;

$stmt = $pdo->prepare("SELECT id, nombre FROM municipios WHERE departamento_id = ?");
$stmt->execute([$departamento_id]);

$municipios = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($municipios);