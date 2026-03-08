<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'auxiliar') {
  header("Location: ../login.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['paquete_id'])) {
  $paquete_id = $_POST['paquete_id'];

  // Confirmar llegada
  $stmt = $pdo->prepare("UPDATE paquetes SET confirmado_bodega = 1, fecha_recepcion = NOW() WHERE id = ?");
  $stmt->execute([$paquete_id]);

  // Redirigir a asignar ruta
  header("Location: asignar_ruta.php?id=" . $paquete_id);
  exit;
} else {
  echo "Solicitud inválida.";
}