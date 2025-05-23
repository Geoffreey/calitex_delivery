<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'piloto') {
  header("Location: ../login.php");
  exit;
}

$piloto_id = $_SESSION['user_id'];
<<<<<<< HEAD

=======
>>>>>>> 0352497 (Se copian archivos correctos)
include 'partials/header.php';
include 'partials/sidebar.php';

// Obtener rutas asignadas al piloto
$rutas = $pdo->prepare("SELECT id, nombre FROM rutas WHERE piloto_id = ?");
$rutas->execute([$piloto_id]);
$ruta = $rutas->fetch();

if (!$ruta) {
  echo "<div class='p-4 alert alert-warning'>No tienes una ruta asignada aún.</div>";
  include 'partials/footer.php';
  exit;
}

// Obtener recolecciones con esa ruta
$stmt = $pdo->prepare("
  SELECT r.id, r.tamano, r.peso, r.descripcion, r.created_at,
         u.nombre AS cliente_nombre, u.apellido AS cliente_apellido
  FROM recolecciones r
  JOIN clientes c ON r.cliente_id = c.id
  JOIN users u ON c.user_id = u.id
  WHERE r.ruta_recoleccion_id = ? AND r.estado_recoleccion = 'pendiente'
");
$stmt->execute([$ruta['id']]);
$recolecciones = $stmt->fetchAll();

// Confirmar recolección
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recoleccion_id'])) {
  $id = $_POST['recoleccion_id'];
  $stmt = $pdo->prepare("UPDATE recolecciones SET estado_recoleccion = 'recibido' WHERE id = ?");
  $stmt->execute([$id]);
  header("Location: ruta_asignada_recoleccion.php");
  exit;
}
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Recolecciones asignadas - Ruta: <?= htmlspecialchars($ruta['nombre']) ?></h2>

  <?php if (empty($recolecciones)): ?>
    <div class="alert alert-info">No tienes recolecciones pendientes por recoger.</div>
  <?php else: ?>
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>Cliente</th>
          <th>Tamaño</th>
          <th>Peso</th>
          <th>Descripción</th>
          <th>Fecha</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recolecciones as $r): ?>
          <tr>
            <td><?= $r['cliente_nombre'] . ' ' . $r['cliente_apellido'] ?></td>
            <td><?= $r['tamano'] ?></td>
            <td><?= $r['peso'] ?> kg</td>
            <td><?= $r['descripcion'] ?></td>
            <td><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
            <td>
              <form method="POST" onsubmit="return confirm('¿Confirmar recolección del paquete?');">
                <input type="hidden" name="recoleccion_id" value="<?= $r['id'] ?>">
                <button type="submit" class="btn btn-success btn-sm">Recogido</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
<?php include 'partials/footer.php'; ?>
>>>>>>> 7232fce (no recuerdo)
>>>>>>> 0352497 (Se copian archivos correctos)
