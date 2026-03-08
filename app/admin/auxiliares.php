<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

include 'partials/header.php';
//include 'partials/sidebar.php';

$auxiliar = $pdo->query("SELECT a.id, u.nombre, u.apellido, u.telefono, u.email
                         FROM auxiliares a 
                         INNER JOIN users u ON a.user_id = u.id
                         WHERE u.estado = 1
                         ORDER BY u.nombre ASC");
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Auxiliares de Bodega</h2>
  <a href="crear_auxiliar.php" class="btn btn-success mb-3">+ Agregar Auxiliar</a>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>Nombre</th>
          <th>Email</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($auxiliar as $aux): ?>
        <tr>
          <td><?= htmlspecialchars($aux['nombre'] . ' ' . $aux['apellido']) ?></td>
          <td><?= htmlspecialchars($aux['email']) ?></td>
          <td>
            <a href="editar_auxiliar.php?id=<?= $aux['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
            <a href="eliminar_auxiliar.php?id=<?= $aux['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este auxiliar?')">Eliminar</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'partials/footer.php'; ?>