<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'cliente') {
  header("Location: ../login.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
  $id = $_POST['id'];

  $stmt = $pdo->prepare("UPDATE recolecciones SET estado_recoleccion = 'cancelado' WHERE id = ? AND estado_recoleccion = 'pendiente'");
  $stmt->execute([$id]);
}

header("Location: mis_recolecciones.php");
exit;