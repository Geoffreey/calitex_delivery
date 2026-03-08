<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

include 'partials/header.php';
//include 'partials/sidebar.php';

// Obtener todos los departamentos activos
$query = $pdo->query("SELECT * FROM departamentos ORDER BY nombre ASC");
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Departamentos</h2>
  <a href="crear_departamento.php" class="btn btn-success mb-3">+ Agregar Departamento</a>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>Nombre</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($query as $row): ?>
        <tr>
          <td><?= htmlspecialchars($row['nombre']) ?></td>
          <td>
            <a href="editar_departamento.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
            <a href="eliminar_departamento.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este departamento?')">Eliminar</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'partials/footer.php'; ?>