<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'auxiliar') {
  header("Location: ../login.php");
  exit;
}

include 'partials/header.php';
include 'partials/sidebar.php';

// Obtener clientes con INNER JOIN a users
$clientes = $pdo->query("SELECT c.id, u.nombre, u.apellido, u.telefono, u.email
                         FROM clientes c 
                         INNER JOIN users u ON c.user_id = u.id
                         WHERE u.estado = 1
                         ORDER BY u.nombre ASC");
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Lista de Clientes</h2>
  <a href="crear_cliente.php" class="btn btn-success mb-3">+ Agregar Cliente</a>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>Nombre</th>
          <th>Apellido</th>
          <th>Teléfono</th>
          <th>Email</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($clientes as $c): ?>
        <tr>
          <td><?= htmlspecialchars($c['nombre']) ?></td>
          <td><?= htmlspecialchars($c['apellido']) ?></td>
          <td><?= htmlspecialchars($c['telefono']) ?></td>
          <td><?= htmlspecialchars($c['email']) ?></td>
          <td>
            <a href="editar_cliente.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
            <a href="eliminar_cliente.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar cliente?')">Eliminar</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'partials/footer.php'; ?>
