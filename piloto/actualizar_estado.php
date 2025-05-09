<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'piloto') {
  header("Location: ../login.php");
  exit;
}

$piloto_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $paquete_id   = $_POST['paquete_id'] ?? null;
  $nuevo_estado = $_POST['nuevo_estado'] ?? null;

  if (!$paquete_id || !$nuevo_estado) {
    echo "Datos incompletos.";
    exit;
  }

  // Verifica que el paquete pertenezca al piloto logueado
  $stmt = $pdo->prepare("SELECT id FROM paquetes WHERE id = ? AND piloto_id = ?");
  $stmt->execute([$paquete_id, $piloto_id]);
  $paquete = $stmt->fetch();

  if (!$paquete) {
    echo "Acceso denegado o paquete no encontrado.";
    exit;
  }

  // Actualizar estado
  $stmt = $pdo->prepare("UPDATE paquetes SET estado_envio = ? WHERE id = ?");
  $stmt->execute([$nuevo_estado, $paquete_id]);

  header("Location: paquetes_asignados.php");
  exit;
}