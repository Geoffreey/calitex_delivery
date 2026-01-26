<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

include 'partials/header.php';
////include 'partials/sidebar.php';

// Obtener pilotos activos
$query = $pdo->query("
  SELECT p.id, u.nombre, u.apellido, u.telefono, u.email, f.tipo AS flota
  FROM pilotos p
  JOIN users u ON p.user_id = u.id
  LEFT JOIN flotas f ON p.flota_id = f.id
  WHERE u.estado = 1
  ORDER BY u.nombre
");
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Pilotos registrados</h2>
  <a href="crear_piloto.php" class="btn btn-success mb-3">+ Agregar Piloto</a>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>Nombre</th>
          <th>Apellido</th>
          <th>Teléfono</th>
          <th>Email</th>
          <th>Flota</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($query as $row): ?>
        <tr>
          <td><?= htmlspecialchars($row['nombre']) ?></td>
          <td><?= htmlspecialchars($row['apellido']) ?></td>
          <td><?= htmlspecialchars($row['telefono']) ?></td>
          <td><?= htmlspecialchars($row['email']) ?></td>
          <td><?= htmlspecialchars($row['flota'] ?? 'No asignada') ?></td>
          <td>
            <a href="editar_piloto.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
            <a href="eliminar_piloto.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar piloto?')">Eliminar</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'partials/footer.php'; ?>