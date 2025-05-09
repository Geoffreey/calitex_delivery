<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'auxiliar') {
  header("Location: ../login.php");
  exit;
}

include 'partials/header.php';
include 'partials/sidebar.php';

$stmt = $pdo->prepare("
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
  WHERE p.confirmado_bodega = 0
  ORDER BY p.created_at ASC
");
$stmt->execute();
$paquetes = $stmt->fetchAll();
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Paquetes por Confirmar en Bodega</h2>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>Cliente</th>
          <th>Origen</th>
          <th>Destino</th>
          <th>Tamaño</th>
          <th>Peso</th>
          <th>Fecha</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($paquetes as $p): ?>
        <tr>
          <td><?= $p['cliente_nombre'] . ' ' . $p['cliente_apellido'] ?></td>
          <td><?= $p['muni_origen'] ?> - Zona <?= $p['zona_origen'] ?></td>
          <td><?= $p['muni_destino'] ?> - Zona <?= $p['zona_destino'] ?></td>
          <td><?= $p['tamano'] ?></td>
          <td><?= $p['peso'] ?> kg</td>
          <td><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></td>
          <td>
            <form method="POST" action="confirmar_paquete.php" class="d-flex">
              <input type="hidden" name="paquete_id" value="<?= $p['id'] ?>">
              <button type="submit" class="btn btn-success btn-sm">Confirmar llegada</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'partials/footer.php'; ?>