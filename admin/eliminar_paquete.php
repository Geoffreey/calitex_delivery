<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
  echo "ID de paquete no proporcionado.";
  exit;
}

try {
  $stmt = $pdo->prepare("UPDATE paquetes SET estado_envio = 'anulado' WHERE id = ?");
  $stmt->execute([$id]);

  header("Location: paquetes.php");
  exit;
} catch (Exception $e) {
  echo "Error al anular paquete: " . $e->getMessage();
}