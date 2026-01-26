<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

include 'partials/header.php';
//include 'partials/sidebar.php';

$query = $pdo->query("SELECT * FROM rutas WHERE estado = 1 ORDER BY nombre ASC");
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Rutas registradas</h2>
  <a href="crear_ruta.php" class="btn btn-success mb-3">+ Agregar Ruta</a>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>Nombre</th>
          <th>Descripción</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($query as $ruta): ?>
        <tr>
          <td><?= htmlspecialchars($ruta['nombre']) ?></td>
          <td><?= htmlspecialchars($ruta['descripcion']) ?></td>
          <td>
            <a href="editar_ruta.php?id=<?= $ruta['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
            <a href="eliminar_ruta.php?id=<?= $ruta['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar esta ruta?')">Eliminar</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'partials/footer.php'; ?>