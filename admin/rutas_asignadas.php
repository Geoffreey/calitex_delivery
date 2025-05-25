<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

include 'partials/header.php';
include 'partials/sidebar.php';

// Obtener rutas con pilotos asignados
$stmt = $pdo->query("
  SELECT r.id, r.nombre AS ruta_nombre, r.fecha_asignacion, r.tipo_asignacion, r.semana_asignada,
         u.nombre AS piloto_nombre, u.apellido AS piloto_apellido
  FROM rutas r
  JOIN pilotos p ON r.piloto_id = p.id
  JOIN users u ON p.user_id = u.id
  WHERE r.piloto_id IS NOT NULL
  ORDER BY r.fecha_asignacion DESC
");
$rutas = $stmt->fetchAll();
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Rutas Asignadas a Pilotos</h2>

  <?php if (empty($rutas)): ?>
    <div class="alert alert-info">No hay rutas asignadas aún.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-bordered table-striped">
        <thead class="table-light">
          <tr>
            <th>Piloto</th>
            <th>Ruta</th>
            <th>Tipo de Asignación</th>
            <th>Semana Asignada</th>
            <th>Fecha de Asignación</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rutas as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r['piloto_nombre'] . ' ' . $r['piloto_apellido']) ?></td>
              <td><?= htmlspecialchars($r['ruta_nombre']) ?></td>
              <td><?= ucfirst($r['tipo_asignacion']) ?></td>
              <td><?= htmlspecialchars($r['semana_asignada']) ?></td>
              <td><?= date('d/m/Y H:i', strtotime($r['fecha_asignacion'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php include 'partials/footer.php'; ?>
