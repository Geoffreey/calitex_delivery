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
  SELECT m.id, m.nombre AS municipio, d.nombre AS departamento
  FROM municipios m
  JOIN departamentos d ON m.departamento_id = d.id
  WHERE m.estado = 1
  ORDER BY d.nombre, m.nombre
");
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Municipios</h2>
  <a href="crear_municipio.php" class="btn btn-success mb-3">+ Agregar Municipio</a>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>Municipio</th>
          <th>Departamento</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($query as $row): ?>
        <tr>
          <td><?= htmlspecialchars($row['municipio']) ?></td>
          <td><?= htmlspecialchars($row['departamento']) ?></td>
          <td>
            <a href="editar_municipio.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
            <a href="eliminar_municipio.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este municipio?')">Eliminar</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'partials/footer.php'; ?>