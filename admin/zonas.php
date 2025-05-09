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
  SELECT z.id, z.numero, m.nombre AS municipio
  FROM zona z
  JOIN municipios m ON z.municipio_id = m.id
  ORDER BY m.nombre, z.numero
");
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Zonas registradas</h2>
  <a href="crear_zona.php" class="btn btn-success mb-3">+ Agregar Zona</a>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>Número</th>
          <th>Municipio</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($query as $zona): ?>
        <tr>
          <td><?= htmlspecialchars($zona['numero']) ?></td>
          <td><?= htmlspecialchars($zona['municipio']) ?></td>
          <td>
            <a href="editar_zona.php?id=<?= $zona['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
            <a href="eliminar_zona.php?id=<?= $zona['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar esta zona?')">Eliminar</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'partials/footer.php'; ?>