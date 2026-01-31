<?php
session_start();
require_once '../config/db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id']) || ($_SESSION['rol'] ?? '') !== 'piloto') {
  header("Location: ../login.php");
  exit;
}

$user_id = (int)$_SESSION['user_id'];

// Obtener piloto_id real
$stmt = $pdo->prepare("SELECT id FROM pilotos WHERE user_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$piloto_id = (int)$stmt->fetchColumn();

if (!$piloto_id) {
  include 'partials/header.php';
  echo "<div class='alert alert-danger m-4'>No se encontró piloto asociado a este usuario.</div>";
  include 'partials/footer.php';
  exit;
}

include 'partials/header.php';

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
  ORDER BY e.created_at DESC
");
$query->execute([$piloto_id]);
$envios = $query->fetchAll(PDO::FETCH_ASSOC);

// Helpers (PHP 7)
function estado_badge_class($estado) {
  switch ($estado) {
    case 'pendiente': return 'secondary';
    case 'recibido':  return 'success';
    case 'cancelado': return 'danger';
    default:          return 'light';
  }
}

function estado_label($estado) {
  switch ($estado) {
    case 'pendiente': return 'Pendiente';
    case 'recibido':  return 'Recibido';
    case 'cancelado': return 'Cancelado';
    default:          return ucfirst($estado);
  }
}
?>

<div class="col-lg-10 col-12 p-4">
  <h2 class="mb-3">Mis Envíos Asignados</h2>

  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Cliente</th>
          <th>Destinatario</th>
          <th>Origen</th>
          <th>Destino</th>
          <th>Pago</th>
          <th>Estado</th>
          <th>Fecha</th>
          <th style="min-width: 210px;">Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($envios)): ?>
          <tr>
            <td colspan="9" class="text-center text-muted py-4">No hay envíos asignados.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($envios as $e): ?>
            <tr>
              <td><?= (int)$e['id'] ?></td>
              <td><?= htmlspecialchars(trim($e['cliente_nombre'].' '.$e['cliente_apellido'])) ?></td>
              <td>
                <div><strong><?= htmlspecialchars($e['nombre_destinatario']) ?></strong></div>
                <div class="text-muted"><?= htmlspecialchars($e['telefono_destinatario']) ?></div>
              </td>
              <td><?= htmlspecialchars($e['origen_direccion'] ?: '—') ?></td>
              <td><?= htmlspecialchars($e['destino_direccion'] ?: '—') ?></td>
              <td><?= ($e['pago_envio'] === 'destinatario') ? 'Destinatario' : 'Cliente' ?></td>
              <td>
                <span class="badge bg-<?= estado_badge_class($e['estado_envio']) ?>">
                  <?= estado_label($e['estado_envio']) ?>
                </span>
              </td>
              <td><?= date('d/m/Y H:i', strtotime($e['created_at'])) ?></td>
              <td>
                <?php if ($e['estado_envio'] !== 'recibido' && $e['estado_envio'] !== 'cancelado'): ?>
                  <form method="POST" action="actualizar_estado.php" class="d-inline">
                    <input type="hidden" name="envio_id" value="<?= (int)$e['id'] ?>">
                    <select name="nuevo_estado" class="form-select form-select-sm d-inline w-auto" required>
                      <option value="pendiente" <?= $e['estado_envio']==='pendiente' ? 'selected' : '' ?>>Pendiente</option>
                      <option value="recibido">Recibido</option>
                      <option value="cancelado">Cancelado</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary">Actualizar</button>
                  </form>
                <?php else: ?>
                  <span class="text-muted">—</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'partials/footer.php'; ?>
