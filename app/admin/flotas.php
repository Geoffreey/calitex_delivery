<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

include 'partials/header.php';
//include 'partials/sidebar.php';

// Obtener flotas activas
$query = $pdo->query("SELECT * FROM flotas WHERE estado = 1 ORDER BY tipo ASC");
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Flotas registradas</h2>
  <a href="crear_flota.php" class="btn btn-success mb-3">+ Agregar Flota</a>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>Tipo</th>
          <th>Placa</th>
          <th>Descripción</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($query as $row): ?>
        <tr>
          <td><?= htmlspecialchars($row['tipo']) ?></td>
          <td><?= htmlspecialchars($row['placa']) ?></td>
          <td><?= htmlspecialchars($row['descripcion']) ?></td>
          <td>
            <a href="editar_flota.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
            <a href="eliminar_flota.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar flota?')">Eliminar</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'partials/footer.php'; ?>