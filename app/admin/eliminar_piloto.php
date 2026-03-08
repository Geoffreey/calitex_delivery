<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
  echo "ID de piloto no proporcionado.";
  exit;
}

// Obtener user_id del piloto
$stmt = $pdo->prepare("SELECT user_id FROM pilotos WHERE id = ?");
$stmt->execute([$id]);
$piloto = $stmt->fetch();

if (!$piloto) {
  echo "Piloto no encontrado.";
  exit;
}

try {
  $stmt = $pdo->prepare("UPDATE users SET estado = 0 WHERE id = ?");
  $stmt->execute([$piloto['user_id']]);

  header("Location: pilotos.php");
  exit;
} catch (Exception $e) {
  echo "Error al eliminar piloto: " . $e->getMessage();
}