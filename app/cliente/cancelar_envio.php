<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'cliente') {
  header("Location: ../login.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
  $id = $_POST['id'];

  $stmt = $pdo->prepare("UPDATE envios SET estado_envio = 'cancelado' WHERE id = ? AND estado_envio = 'pendiente'");
  $stmt->execute([$id]);
}

header("Location: mis_envios.php");
exit;