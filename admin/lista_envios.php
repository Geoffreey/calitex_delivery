<?php
session_start();
require_once '../config/db.php';
define('BASE_PATH', realpath(__DIR__ . '/..'));

if (!isset($_SESSION['user_id']) || ($_SESSION['rol'] ?? '') !== 'admin') {
  header("Location: ../login.php");
  exit;
}

// TAB: all | pendiente | recibido | cancelado
$tab = $_GET['tab'] ?? 'all';
$tab = in_array($tab, ['all','pendiente','recibido','cancelado'], true) ? $tab : 'all';

$whereEstado = "";
$params = [];

if ($tab !== 'all') {
  $whereEstado = " AND e.estado_envio = ? ";
  $params[] = $tab;
}

// ===================== LISTA ENVIOS (GLOBAL: todos los pilotos) =====================
$query = $pdo->prepare("
  SELECT e.*,
         u.nombre  AS cliente_nombre,
         u.apellido AS cliente_apellido,

         pu.nombre AS piloto_nombre,
         pu.apellido AS piloto_apellido,
         p.id AS piloto_real_id,

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

  LEFT JOIN pilotos p ON p.id = e.piloto_id
  LEFT JOIN users pu ON pu.id = p.user_id

  LEFT JOIN direcciones dor  ON e.direccion_origen_id = dor.id
  LEFT JOIN direcciones ddes ON e.direccion_destino_id = ddes.id

  LEFT JOIN zona zo ON dor.zona_id = zo.id
  LEFT JOIN municipios mo ON dor.municipio_id = mo.id
  LEFT JOIN departamentos dpto_o ON dor.departamento_id = dpto_o.id

  LEFT JOIN zona zd ON ddes.zona_id = zd.id
  LEFT JOIN municipios md ON ddes.municipio_id = md.id
  LEFT JOIN departamentos dpto_d ON ddes.departamento_id = dpto_d.id

  WHERE 1=1
  {$whereEstado}
  ORDER BY e.created_at DESC
");
$query->execute($params);
$envios = $query->fetchAll(PDO::FETCH_ASSOC);

// ===================== CONTADORES (GLOBAL) =====================
$stCount = $pdo->prepare("
  SELECT 
    SUM(CASE WHEN estado_envio='pendiente' THEN 1 ELSE 0 END) AS pendientes,
    SUM(CASE WHEN estado_envio='recibido' THEN 1 ELSE 0 END) AS recibidos,
    SUM(CASE WHEN estado_envio='cancelado' THEN 1 ELSE 0 END) AS cancelados,
    COUNT(*) AS total
  FROM envios
");
$stCount->execute();
$counts = $stCount->fetch(PDO::FETCH_ASSOC) ?: ['pendientes'=>0,'recibidos'=>0,'cancelados'=>0,'total'=>0];

// Nombre mostrado en el header (es auxiliar)
$pilot_name = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''));

// Helpers view
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

require_once '../views/admin/lista_envios.view.php';