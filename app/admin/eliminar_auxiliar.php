<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
  echo "ID no proporcionado.";
  exit;
}

try {
  $stmt = $pdo->prepare("UPDATE auxiliares SET estado = 0 WHERE id = ?");
  $stmt->execute([$id]);

  header("Location: auxiliares.php");
  exit;
} catch (Exception $e) {
  echo "Error al desactivar auxiliar: " . $e->getMessage();
}