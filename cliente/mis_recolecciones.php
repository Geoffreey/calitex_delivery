<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'cliente') {
  header("Location: ../login.php");
  exit;
}

$stmt = $pdo->prepare("SELECT id FROM clientes WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$cliente_id = $stmt->fetchColumn();

$stmt = $pdo->prepare("
  SELECT r.*, 
         do.calle AS calle_origen, do.numero AS num_origen, zo.numero AS zona_origen, mo.nombre AS muni_origen, dp1.nombre AS depto_origen,
         dd.calle AS calle_destino, dd.numero AS num_destino, zd.numero AS zona_destino, md.nombre AS muni_destino, dp2.nombre AS depto_destino
  FROM recolecciones r
  JOIN direcciones do ON r.direccion_origen_id = do.id
  JOIN zona zo ON do.zona_id = zo.id
  JOIN municipios mo ON do.municipio_id = mo.id
  JOIN departamentos dp1 ON do.departamento_id = dp1.id
  JOIN direcciones dd ON r.direccion_destino_id = dd.id
  JOIN zona zd ON dd.zona_id = zd.id
  JOIN municipios md ON dd.municipio_id = md.id
  JOIN departamentos dp2 ON dd.departamento_id = dp2.id
  WHERE r.cliente_id = ?
  ORDER BY r.created_at DESC
");
$stmt->execute([$cliente_id]);
$recolecciones = $stmt->fetchAll();

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Mis Recolecciones</h2>

  <?php if (empty($recolecciones)): ?>
    <div class="alert alert-info">No tienes recolecciones registradas.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-bordered table-striped">
        <thead class="table-light">
          <tr>
            <th>Origen</th>
            <th>Destino</th>
            <th>Tamaño</th>
            <th>Peso</th>
            <th>Estado</th>
            <th>Fecha</th>
            <th>Accion</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recolecciones as $r): ?>
            <tr>
              <td><?= "{$r['calle_origen']} #{$r['num_origen']}, Zona {$r['zona_origen']}, {$r['muni_origen']}, {$r['depto_origen']}" ?></td>
              <td><?= "{$r['calle_destino']} #{$r['num_destino']}, Zona {$r['zona_destino']}, {$r['muni_destino']}, {$r['depto_destino']}" ?></td>
              <td><?= $r['tamano'] ?></td>
              <td><?= $r['peso'] ?> kg</td>
              <td><?= ucfirst($r['estado_recoleccion']) ?></td>
              <td><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
              <td>
  <?php if ($r['estado_recoleccion'] === 'pendiente'): ?>
    <form method="POST" action="cancelar_recoleccion.php" onsubmit="return confirm('¿Cancelar esta recolección?');">
      <input type="hidden" name="id" value="<?= $r['id'] ?>">
      <button type="submit" class="btn btn-danger btn-sm">Cancelar</button>
    </form>
  <?php else: ?>
    <span class="text-muted">No disponible</span>
  <?php endif; ?>
</td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php include 'partials/footer.php'; ?>