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
  // Eliminar directamente sin validar relaciones
  $stmt = $pdo->prepare("DELETE FROM paquetes WHERE id = ?");
  $stmt->execute([$id]);

  header("Location: paquetes.php");
  exit;
} catch (Exception $e) {
  echo "Error al eliminar paquete: " . $e->getMessage();
}
