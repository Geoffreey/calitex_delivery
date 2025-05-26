<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

include 'partials/header.php';
include 'partials/sidebar.php';

$query = $pdo->query("
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
  ORDER BY p.created_at DESC
");
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Paquetes Registrados</h2>
  <a href="crear_paquete.php" class="btn btn-success mb-3">+ Nuevo Paquete</a>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>Cliente</th>
          <th>Tamaño</th>
          <th>Peso</th>
          <th>Origen</th>
          <th>Destino</th>
          <th>Estado</th>
          <th>Fecha</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($query as $p): ?>
        <tr>
          <td><?= htmlspecialchars($p['cliente_nombre'] . ' ' . $p['cliente_apellido']) ?></td>
          <td><?= htmlspecialchars($p['tamano']) ?></td>
          <td><?= $p['peso'] ?> kg</td>
          <td><?= $p['muni_origen'] ?> - Zona <?= $p['zona_origen'] ?></td>
          <td><?= $p['muni_destino'] ?> - Zona <?= $p['zona_destino'] ?></td>
          <td>
            <?php
$estado = $p['estado_envio'];
$colores = [
  'pendiente'   => 'secondary',
  'en_proceso'  => 'warning',
  'entregado'   => 'success',
  'anulado'     => 'danger'
];
$color = $colores[$estado] ?? 'light';
?>
<span class="badge bg-<?= $color ?>">
  <?= ucfirst($estado) ?>
</span>

          </td>
          <td><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></td>
          <td>
            <a href="editar_paquete.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
            <a href="eliminar_paquete.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este paquete?')">Eliminar</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'partials/footer.php'; ?>