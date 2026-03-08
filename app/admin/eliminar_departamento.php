<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
  echo "ID de departamento no proporcionado.";
  exit;
}

try {
  $stmt = $pdo->prepare("UPDATE departamentos SET estado = 0 WHERE id = ?");
  $stmt->execute([$id]);

  header("Location: departamentos.php");
  exit;
} catch (Exception $e) {
  echo "Error al eliminar departamento: " . $e->getMessage();
}