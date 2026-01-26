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
  SELECT e.*, 
         do.calle AS calle_origen, do.numero AS num_origen, zo.numero AS zona_origen, mo.nombre AS muni_origen, dp1.nombre AS depto_origen,
         dd.calle AS calle_destino, dd.numero AS num_destino, zd.numero AS zona_destino, md.nombre AS muni_destino, dp2.nombre AS depto_destino
  FROM envios e
  JOIN direcciones do ON e.direccion_origen_id = do.id
  JOIN zona zo ON do.zona_id = zo.id
  JOIN municipios mo ON do.municipio_id = mo.id
  JOIN departamentos dp1 ON do.departamento_id = dp1.id
  JOIN direcciones dd ON e.direccion_destino_id = dd.id
  JOIN zona zd ON dd.zona_id = zd.id
  JOIN municipios md ON dd.municipio_id = md.id
  JOIN departamentos dp2 ON dd.departamento_id = dp2.id
  WHERE e.cliente_id = ?
  ORDER BY e.created_at DESC
");
$stmt->execute([$cliente_id]);
$envios = $stmt->fetchAll();

include 'partials/header.php';
//include 'partials/sidebar.php';
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Mis Envíos</h2>

  <?php if (empty($envios)): ?>
    <div class="alert alert-info">No tienes envíos registrados.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-bordered table-striped">
        <thead class="table-light">
          <tr>
            <th>Origen</th>
            <th>Destino</th>
            <th>Paquetes</th>
            <th>Estado</th>
            <th>Fecha</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($envios as $e): ?>
            <?php
              // Obtener los paquetes asociados a este envío
              $stmt_pq = $pdo->prepare("
                SELECT p.nombre, p.tamano, p.peso
                FROM envios_paquetes ep
                JOIN paquetes p ON ep.paquete_id = p.id
                WHERE ep.envio_id = ?
              ");
              $stmt_pq->execute([$e['id']]);
              $paquetes = $stmt_pq->fetchAll();
            ?>
            <tr>
              <td><?= "{$e['calle_origen']} #{$e['num_origen']}, Zona {$e['zona_origen']}, {$e['muni_origen']}, {$e['depto_origen']}" ?></td>
              <td><?= "{$e['calle_destino']} #{$e['num_destino']}, Zona {$e['zona_destino']}, {$e['muni_destino']}, {$e['depto_destino']}" ?></td>
              <td>
                <ul class="mb-0">
                  <?php foreach ($paquetes as $p): ?>
                    <li><?= "{$p['nombre']} - {$p['tamano']} ({$p['peso']} kg)" ?></li>
                  <?php endforeach; ?>
                </ul>
              </td>
              <td><?= ucfirst($e['estado_envio']) ?></td>
              <td><?= date('d/m/Y H:i', strtotime($e['created_at'])) ?></td>
              <td>
                <?php if ($e['estado_envio'] === 'pendiente'): ?>
                  <form method="POST" action="cancelar_envio.php" onsubmit="return confirm('¿Cancelar este envío?');">
                    <input type="hidden" name="id" value="<?= $e['id'] ?>">
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
