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

// Obtener piloto_id real
$stmt = $pdo->prepare("SELECT id FROM pilotos WHERE user_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$piloto_id = (int)$stmt->fetchColumn();

if (!$piloto_id) {
  echo "Acceso denegado. Piloto no válido.";
  exit;
}

// Leer POST
$envio_id = isset($_POST['envio_id']) ? (int)$_POST['envio_id'] : 0;
$nuevo_estado = $_POST['nuevo_estado'] ?? '';

// Aquí quitamos 'recibido' para que solo se procese desde entregar_envio.php
$permitidos = ['pendiente', 'cancelado'];

if ($envio_id <= 0 || !in_array($nuevo_estado, $permitidos, true)) {
  echo "Datos incompletos o estado no permitido.";
  exit;
}

// Verificar que el envío pertenezca al piloto logueado
$stmt = $pdo->prepare("
  SELECT id
  FROM envios
  WHERE id = ? AND piloto_id = ?
  LIMIT 1
");
$stmt->execute([$envio_id, $piloto_id]);
$envio = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$envio) {
  echo "Acceso denegado o envío no encontrado.";
  exit;
}

// Actualizar estado
$stmt = $pdo->prepare("
  UPDATE envios
  SET estado_envio = ?
  WHERE id = ?
  LIMIT 1
");
$stmt->execute([$nuevo_estado, $envio_id]);

header("Location: paquetes_asignados.php");
exit;