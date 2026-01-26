<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

include 'partials/header.php';
//include 'partials/sidebar.php';

$query = $pdo->query("
  SELECT t.*, 
         zo.numero AS zona_origen,
         zd.numero AS zona_destino,
         mo.nombre AS municipio_origen,
         md.nombre AS municipio_destino
  FROM tarifas t
  JOIN zona zo ON t.zona_origen_id = zo.id
  JOIN zona zd ON t.zona_destino_id = zd.id
  JOIN municipios mo ON zo.municipio_id = mo.id
  JOIN municipios md ON zd.municipio_id = md.id
  WHERE t.estado = 1
  ORDER BY mo.nombre, md.nombre, t.tamano
");
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Tarifas registradas</h2>
  <a href="crear_tarifa.php" class="btn btn-success mb-3">+ Agregar Tarifa</a>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>Zona Origen</th>
          <th>Zona Destino</th>
          <th>Tamaño</th>
          <th>Peso (kg)</th>
          <th>Precio</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($query as $row): ?>
        <tr>
          <td><?= htmlspecialchars($row['municipio_origen']) ?> - Zona <?= $row['zona_origen'] ?></td>
          <td><?= htmlspecialchars($row['municipio_destino']) ?> - Zona <?= $row['zona_destino'] ?></td>
          <td><?= htmlspecialchars($row['tamano']) ?></td>
          <td><?= $row['peso_min'] ?> - <?= $row['peso_max'] ?> kg</td>
          <td>Q<?= number_format($row['precio'], 2) ?></td>
          <td>
            <a href="editar_tarifa.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
            <a href="eliminar_tarifa.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar esta tarifa?')">Eliminar</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'partials/footer.php'; ?>