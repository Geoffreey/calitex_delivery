<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'piloto') {
  header("Location: ../login.php");
  exit;
}

$piloto_id = $_SESSION['user_id'];

include 'partials/header.php';
//include 'partials/sidebar.php';

$query = $pdo->prepare("
  SELECT p.*, 
         u.nombre AS cliente_nombre, u.apellido AS cliente_apellido,
         zo.numero AS zona_origen, mo.nombre AS muni_origen,
         zd.numero AS zona_destino, md.nombre AS muni_destino
  FROM paquetes p
  JOIN clientes c ON p.cliente_id = c.id
  JOIN users u ON c.user_id = u.id
  JOIN zona zo ON p.zona_origen_id = zo.id
  JOIN municipios mo ON zo.municipio_id = mo.id
  JOIN zona zd ON p.zona_destino_id = zd.id
  JOIN municipios md ON zd.municipio_id = md.id
  WHERE p.piloto_id = ?
  ORDER BY p.created_at DESC
");
$query->execute([$piloto_id]);
$paquetes = $query->fetchAll();
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Mis Paquetes Asignados</h2>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>Cliente</th>
          <th>Origen</th>
          <th>Destino</th>
          <th>Estado</th>
          <th>Fecha</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($paquetes as $p): ?>
        <tr>
          <td><?= htmlspecialchars($p['cliente_nombre'] . ' ' . $p['cliente_apellido']) ?></td>
          <td><?= $p['muni_origen'] ?> - Zona <?= $p['zona_origen'] ?></td>
          <td><?= $p['muni_destino'] ?> - Zona <?= $p['zona_destino'] ?></td>
          <td>
            <span class="badge bg-<?= match($p['estado_envio']) {
              'pendiente' => 'secondary',
              'en_proceso' => 'warning',
              'entregado' => 'success',
              'anulado' => 'danger',
              default => 'light'
            } ?>">
              <?= ucfirst($p['estado_envio']) ?>
            </span>
          </td>
          <td><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></td>
          <td>
            <?php if ($p['estado_envio'] !== 'entregado' && $p['estado_envio'] !== 'anulado'): ?>
              <form method="POST" action="actualizar_estado.php" class="d-inline">
                <input type="hidden" name="paquete_id" value="<?= $p['id'] ?>">
                <select name="nuevo_estado" class="form-select form-select-sm d-inline w-auto" required>
                  <option value="en_proceso" <?= $p['estado_envio'] === 'en_proceso' ? 'selected' : '' ?>>En proceso</option>
                  <option value="entregado">Entregado</option>
                </select>
                <button type="submit" class="btn btn-sm btn-primary">Actualizar</button>
              </form>
            <?php else: ?>
              -
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'partials/footer.php'; ?>