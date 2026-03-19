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

/**
 * Configuración:
 * true  = firma obligatoria + foto
 * false = solo foto
 */
$requiere_firma_recibido = false;

// piloto_id real
$stmt = $pdo->prepare("SELECT id FROM pilotos WHERE user_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$piloto_id = (int)$stmt->fetchColumn();

if (!$piloto_id) {
  die("Piloto no válido.");
}

$envio_id  = isset($_POST['envio_id']) ? (int)$_POST['envio_id'] : 0;
$firma_b64 = trim($_POST['firma_base64'] ?? '');
$foto_b64  = trim($_POST['foto_base64'] ?? '');

if ($envio_id <= 0) {
  die("Envío no válido.");
}

if ($requiere_firma_recibido) {
  if ($firma_b64 === '' || $foto_b64 === '') {
    die("Datos incompletos. La firma y la foto son obligatorias.");
  }
} else {
  if ($foto_b64 === '') {
    die("Datos incompletos. La foto es obligatoria.");
  }
}

// Verificar que el envío es del piloto y está pendiente
$stmt = $pdo->prepare("
  SELECT id
  FROM envios
  WHERE id = ? AND piloto_id = ? AND estado_envio = 'pendiente'
  LIMIT 1
");
$stmt->execute([$envio_id, $piloto_id]);

if (!$stmt->fetch()) {
  die("Acceso denegado o envío no válido.");
}

// Directorios
$dirFirmas = __DIR__ . '/../firmas';
$dirFotos  = __DIR__ . '/../fotos_entrega';

if ($requiere_firma_recibido && !is_dir($dirFirmas)) {
  @mkdir($dirFirmas, 0775, true);
}
if (!is_dir($dirFotos)) {
  @mkdir($dirFotos, 0775, true);
}

if ($requiere_firma_recibido && !is_writable($dirFirmas)) {
  die("No se pudo escribir en la carpeta de firmas.");
}
if (!is_writable($dirFotos)) {
  die("No se pudo escribir en la carpeta de fotos.");
}

function save_base64_image($dataUrl, $absDir, $relDir, $prefix) {
  $dataUrl = preg_replace('#^data:image/\w+;base64,#i', '', $dataUrl);
  $bin = base64_decode(str_replace(' ', '+', $dataUrl));

  if ($bin === false) {
    return [null, null];
  }

  $name = $prefix . '_' . time() . '_' . mt_rand(1000, 9999) . '.png';
  $abs  = rtrim($absDir, '/') . '/' . $name;
  $rel  = rtrim($relDir, '/') . '/' . $name;

  if (file_put_contents($abs, $bin) === false) {
    return [null, null];
  }

  return [$rel, $abs];
}

$firma_rel = null;
$foto_rel  = null;

// Guardar firma solo si aplica
if ($requiere_firma_recibido) {
  [$firma_rel, $firma_abs] = save_base64_image(
    $firma_b64,
    $dirFirmas,
    '../firmas',
    'firma_envio_' . $envio_id
  );

  if (!$firma_rel) {
    die("Error guardando la firma.");
  }
}

// Guardar foto
[$foto_rel, $foto_abs] = save_base64_image(
  $foto_b64,
  $dirFotos,
  '../fotos_entrega',
  'foto_envio_' . $envio_id
);

if (!$foto_rel) {
  die("Error guardando la foto.");
}

// Actualizar envío
if ($requiere_firma_recibido) {
  $stmt = $pdo->prepare("
    UPDATE envios
    SET firma = ?, foto_entrega = ?, estado_envio = 'recibido', fecha_recibido = NOW()
    WHERE id = ? AND piloto_id = ?
    LIMIT 1
  ");
  $stmt->execute([$firma_rel, $foto_rel, $envio_id, $piloto_id]);
} else {
  $stmt = $pdo->prepare("
    UPDATE envios
    SET foto_entrega = ?, estado_envio = 'recibido', fecha_recibido = NOW()
    WHERE id = ? AND piloto_id = ?
    LIMIT 1
  ");
  $stmt->execute([$foto_rel, $envio_id, $piloto_id]);
}

header("Location: paquetes_asignados.php");
exit;