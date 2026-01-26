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
  SELECT id, nombre, tamano, peso, tarifa, descripcion, created_at 
  FROM paquetes 
  ORDER BY created_at DESC
");
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Tipos de Paquete Registrados</h2>
  <a href="crear_paquete.php" class="btn btn-success mb-3">+ Nuevo Tipo de Paquete</a>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>Nombre</th>
          <th>Tamaño</th>
          <th>Peso (kg)</th>
          <th>Tarifa (Q)</th>
          <th>Descripción</th>
          <th>Fecha</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($query as $p): ?>
        <tr>
          <td><?= htmlspecialchars($p['nombre']) ?></td>
          <td><?= htmlspecialchars($p['tamano']) ?></td>
          <td><?= number_format($p['peso'], 2) ?> kg</td>
          <td>Q<?= number_format($p['tarifa'], 2) ?></td>
          <td><?= htmlspecialchars($p['descripcion']) ?></td>
          <td><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></td>
          <td>
            <a href="editar_paquete.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
            <a href="eliminar_paquete.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este tipo de paquete?')">Eliminar</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'partials/footer.php'; ?>
