<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'auxiliar') {
  header("Location: ../login.php");
  exit;
}

$paquete_id = $_POST['paquete_id'] ?? null;

if (!$paquete_id) {
  echo "ID del paquete no proporcionado.";
  exit;
}

try {
  // Confirmar el paquete en bodega
  $stmt = $pdo->prepare("UPDATE paquetes SET confirmado_bodega = 1 WHERE id = ?");
  $stmt->execute([$paquete_id]);

  // Redirigir de nuevo a la lista
  header("Location: paquetes_por_confirmar.php");
  exit;
} catch (Exception $e) {
  echo "Error al confirmar paquete: " . $e->getMessage();
}