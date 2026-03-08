<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
  echo "ID de cliente no proporcionado.";
  exit;
}

// Obtener el user_id desde la tabla clientes
$stmt = $pdo->prepare("SELECT user_id FROM clientes WHERE id = ?");
$stmt->execute([$id]);
$cliente = $stmt->fetch();

if (!$cliente) {
  echo "Cliente no encontrado.";
  exit;
}

try {
  // Marcar como inactivo
  $stmt = $pdo->prepare("UPDATE users SET estado = 0 WHERE id = ?");
  $stmt->execute([$cliente['user_id']]);

  header("Location: clientes.php");
  exit;
} catch (Exception $e) {
  echo "Error al eliminar cliente: " . $e->getMessage();
}
