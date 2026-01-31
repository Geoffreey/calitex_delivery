<?php
session_start();
require_once '../config/db.php';
define('BASE_PATH', realpath(__DIR__ . '/..')); 

if (!isset($_SESSION['user_id']) || ($_SESSION['rol'] ?? '') !== 'piloto') {
  header("Location: ../login.php");
  exit;
}

$user_id = (int)$_SESSION['user_id'];

// piloto_id real
$stmt = $pdo->prepare("SELECT id FROM pilotos WHERE user_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$piloto_id = (int)$stmt->fetchColumn();

if (!$piloto_id) {
  include 'partials/header.php';
  echo "<div class='p-4'><div class='bg-red-50 text-red-700 border border-red-200 rounded-lg p-4'>No se encontró piloto asociado a este usuario.</div></div>";
  include 'partials/footer.php';
  exit;
}

// filtro por tab (pendiente|recibido|cancelado|all)
$tab = $_GET['tab'] ?? 'all';
$tab = in_array($tab, ['all','pendiente','recibido','cancelado'], true) ? $tab : 'all';

$whereEstado = "";
$params = [$piloto_id];

if ($tab !== 'all') {
  $whereEstado = " AND e.estado_envio = ? ";
  $params[] = $tab;
}

$query = $pdo->prepare("
  SELECT e.*,
         u.nombre  AS cliente_nombre,
         u.apellido AS cliente_apellido,

         CONCAT(
           COALESCE(dor.calle, ''), 
           IF(dor.numero IS NULL OR dor.numero = '', '', CONCAT(' #', dor.numero)),
           IF(zo.numero IS NULL, '', CONCAT(' - Zona ', zo.numero)),
           IF(mo.nombre IS NULL, '', CONCAT(', ', mo.nombre)),
           IF(dpto_o.nombre IS NULL, '', CONCAT(', ', dpto_o.nombre))
         ) AS origen_direccion,

         CONCAT(
           COALESCE(ddes.calle, ''), 
           IF(ddes.numero IS NULL OR ddes.numero = '', '', CONCAT(' #', ddes.numero)),
           IF(zd.numero IS NULL, '', CONCAT(' - Zona ', zd.numero)),
           IF(md.nombre IS NULL, '', CONCAT(', ', md.nombre)),
           IF(dpto_d.nombre IS NULL, '', CONCAT(', ', dpto_d.nombre))
         ) AS destino_direccion

  FROM envios e
  JOIN clientes c ON e.cliente_id = c.id
  JOIN users u ON c.user_id = u.id

  LEFT JOIN direcciones dor  ON e.direccion_origen_id = dor.id
  LEFT JOIN direcciones ddes ON e.direccion_destino_id = ddes.id

  LEFT JOIN zona zo ON dor.zona_id = zo.id
  LEFT JOIN municipios mo ON dor.municipio_id = mo.id
  LEFT JOIN departamentos dpto_o ON dor.departamento_id = dpto_o.id

  LEFT JOIN zona zd ON ddes.zona_id = zd.id
  LEFT JOIN municipios md ON ddes.municipio_id = md.id
  LEFT JOIN departamentos dpto_d ON ddes.departamento_id = dpto_d.id

  WHERE e.piloto_id = ?
  {$whereEstado}
  ORDER BY e.created_at DESC
");
$query->execute($params);
$envios = $query->fetchAll(PDO::FETCH_ASSOC);

// Contadores para tabs
$stCount = $pdo->prepare("
  SELECT 
    SUM(CASE WHEN estado_envio='pendiente' THEN 1 ELSE 0 END) AS pendientes,
    SUM(CASE WHEN estado_envio='recibido' THEN 1 ELSE 0 END) AS recibidos,
    SUM(CASE WHEN estado_envio='cancelado' THEN 1 ELSE 0 END) AS cancelados,
    COUNT(*) AS total
  FROM envios
  WHERE piloto_id = ?
");
$stCount->execute([$piloto_id]);
$counts = $stCount->fetch(PDO::FETCH_ASSOC) ?: ['pendientes'=>0,'recibidos'=>0,'cancelados'=>0,'total'=>0];

$pilot_name = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''));

// Helpers view
function estado_ui($estado) {
  // devuelve [label, dotClass, pillClass] para tu plantilla Tailwind
  switch ($estado) {
    case 'recibido':
      return ['Recibido', 'bg-green-500', 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'];
    case 'cancelado':
      return ['Cancelado', 'bg-red-500', 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'];
    case 'pendiente':
    default:
      return ['Pendiente', 'bg-amber-500', 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'];
  }
}

$pilot_name = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''));

if (!isset($counts)) {
  $counts = [
    'total' => count($envios),
    'pendientes' => 0,
    'recibidos' => 0,
    'cancelados' => 0,
  ];
  foreach ($envios as $e) {
    if ($e['estado_envio'] === 'pendiente') $counts['pendientes']++;
    if ($e['estado_envio'] === 'recibido')  $counts['recibidos']++;
    if ($e['estado_envio'] === 'cancelado') $counts['cancelados']++;
  }
}

function estado_pill_class($estado) {
  switch ($estado) {
    case 'recibido':
      return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
    case 'cancelado':
      return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
    case 'pendiente':
    default:
      return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400';
  }
}

function estado_dot_class($estado) {
  switch ($estado) {
    case 'recibido': return 'bg-green-500';
    case 'cancelado': return 'bg-red-500';
    case 'pendiente':
    default: return 'bg-amber-500';
  }
}

function estado_label_ui($estado) {
  switch ($estado) {
    case 'recibido': return 'Recibido';
    case 'cancelado': return 'Cancelado';
    case 'pendiente':
    default: return 'Pendiente';
  }
}


require_once '../views/piloto/paquetes_asignados.view.php';
