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

// piloto_id real
$stmt = $pdo->prepare("SELECT id FROM pilotos WHERE user_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$piloto_id = (int)$stmt->fetchColumn();

$envio_id = isset($_POST['envio_id']) ? (int)$_POST['envio_id'] : 0;
$firma_b64 = $_POST['firma_base64'] ?? '';
$foto_b64  = $_POST['foto_base64'] ?? '';

if ($envio_id <= 0 || empty($firma_b64) || empty($foto_b64)) {
  die("Datos incompletos.");
}

// Verificar que el envío es del piloto y está pendiente
$stmt = $pdo->prepare("SELECT id FROM envios WHERE id = ? AND piloto_id = ? AND estado_envio = 'pendiente' LIMIT 1");
$stmt->execute([$envio_id, $piloto_id]);
if (!$stmt->fetch()) {
  die("Acceso denegado o envío no válido.");
}

// Directorios
$dirFirmas = __DIR__ . '/../firmas';
$dirFotos  = __DIR__ . '/../fotos_entrega';

if (!is_dir($dirFirmas)) @mkdir($dirFirmas, 0775, true);
if (!is_dir($dirFotos))  @mkdir($dirFotos, 0775, true);

if (!is_writable($dirFirmas) || !is_writable($dirFotos)) {
  die("No se pudo escribir en carpetas de firmas/fotos.");
}

function save_base64_image($dataUrl, $absDir, $relDir, $prefix) {
  $dataUrl = preg_replace('#^data:image/\w+;base64,#i', '', $dataUrl);
  $bin = base64_decode(str_replace(' ', '+', $dataUrl));
  if ($bin === false) return [null, null];

  $name = $prefix . '_' . time() . '_' . mt_rand(1000,9999) . '.png';
  $abs = rtrim($absDir,'/') . '/' . $name;
  $rel = rtrim($relDir,'/') . '/' . $name;

  file_put_contents($abs, $bin);
  return [$rel, $abs];
}

// Guardar firma y foto
[$firma_rel, $firma_abs] = save_base64_image($firma_b64, $dirFirmas, '../firmas', 'firma_envio_'.$envio_id);
[$foto_rel,  $foto_abs ] = save_base64_image($foto_b64,  $dirFotos,  '../fotos_entrega', 'foto_envio_'.$envio_id);

if (!$firma_rel || !$foto_rel) {
  die("Error guardando firma o foto.");
}

// Actualizar envío: firma, foto y estado recibido
$stmt = $pdo->prepare("
  UPDATE envios
  SET firma = ?, foto_entrega = ?, estado_envio = 'recibido', fecha_recibido = NOW()
  WHERE id = ? AND piloto_id = ?
  LIMIT 1
");
$stmt->execute([$firma_rel, $foto_rel, $envio_id, $piloto_id]);

header("Location: paquetes_asignados.php");
exit;
