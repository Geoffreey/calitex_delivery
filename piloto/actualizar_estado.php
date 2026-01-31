<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['rol'] ?? '') !== 'piloto') {
  header("Location: ../login.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: paquetes_asignados.php");
  exit;
}

$user_id = (int)$_SESSION['user_id'];

// 1) Obtener piloto_id real (pilotos.id)
$stmt = $pdo->prepare("SELECT id FROM pilotos WHERE user_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$piloto_id = (int)$stmt->fetchColumn();

if (!$piloto_id) {
  echo "Acceso denegado. Piloto no válido.";
  exit;
}

// 2) Leer POST correcto
$envio_id = isset($_POST['envio_id']) ? (int)$_POST['envio_id'] : 0;
$nuevo_estado = $_POST['nuevo_estado'] ?? '';

$permitidos = ['pendiente', 'recibido', 'cancelado'];
if ($envio_id <= 0 || !in_array($nuevo_estado, $permitidos, true)) {
  echo "Datos incompletos.";
  exit;
}

// 3) Verificar que el envío pertenezca al piloto logueado
$stmt = $pdo->prepare("SELECT id FROM envios WHERE id = ? AND piloto_id = ? LIMIT 1");
$stmt->execute([$envio_id, $piloto_id]);
$envio = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$envio) {
  echo "Acceso denegado o envío no encontrado.";
  exit;
}

// 4) Actualizar estado en envios (NO en paquetes)
$stmt = $pdo->prepare("UPDATE envios SET estado_envio = ? WHERE id = ? LIMIT 1");
$stmt->execute([$nuevo_estado, $envio_id]);

header("Location: paquetes_asignados.php");
exit;
