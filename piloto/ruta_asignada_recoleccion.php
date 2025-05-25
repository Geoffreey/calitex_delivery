<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'piloto') {
  header("Location: ../login.php");
  exit;
}

// Obtener ID del piloto
$stmt = $pdo->prepare("SELECT id FROM pilotos WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$piloto = $stmt->fetch();

if (!$piloto) {
  echo "<div class='p-4 alert alert-warning'>No tienes un perfil de piloto asignado aún.</div>";
  include 'partials/footer.php';
  exit;
}

$piloto_id = $piloto['id'];

include 'partials/header.php';
include 'partials/sidebar.php';

// Obtener todas las rutas asignadas al piloto
$stmt = $pdo->prepare("SELECT id, nombre FROM rutas WHERE piloto_id = ?");
$stmt->execute([$piloto_id]);
$rutas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($rutas)) {
  echo "<div class='p-4 alert alert-warning'>No tienes rutas asignadas aún.</div>";
  include 'partials/footer.php';
  exit;
}

// Extraer los IDs de las rutas
$ruta_ids = array_column($rutas, 'id');
$placeholders = implode(',', array_fill(0, count($ruta_ids), '?'));

// Obtener recolecciones asignadas a esas rutas
$stmt = $pdo->prepare("
  SELECT r.id, r.tamano, r.peso, r.descripcion, r.created_at, r.ruta_recoleccion_id,
         u.nombre AS cliente_nombre, u.apellido AS cliente_apellido,
         rutas.nombre AS ruta_nombre
  FROM recolecciones r
  JOIN clientes c ON r.cliente_id = c.id
  JOIN users u ON c.user_id = u.id
  JOIN rutas ON r.ruta_recoleccion_id = rutas.id
  WHERE r.ruta_recoleccion_id IN ($placeholders) AND r.estado_recoleccion = 'pendiente'
  ORDER BY r.created_at ASC
");
$stmt->execute($ruta_ids);
$recolecciones = $stmt->fetchAll();

// Confirmar recolección
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recoleccion_id'])) {
  $id = $_POST['recoleccion_id'];

  // 1. Marcar recolección como recibida
  $stmt = $pdo->prepare("UPDATE recolecciones SET estado_recoleccion = 'recibido' WHERE id = ?");
  $stmt->execute([$id]);

  // 2. Obtener la ruta de recolección
  $stmtRuta = $pdo->prepare("SELECT ruta_recoleccion_id FROM recolecciones WHERE id = ?");
  $stmtRuta->execute([$id]);
  $ruta_id = $stmtRuta->fetchColumn();

  if ($ruta_id) {
    // 3. Verificar si ya todas las recolecciones fueron recibidas o canceladas
    $stmtCheck = $pdo->prepare("
      SELECT COUNT(*) FROM recolecciones 
      WHERE ruta_recoleccion_id = ? 
      AND estado_recoleccion NOT IN ('recibido', 'cancelado')
    ");
    $stmtCheck->execute([$ruta_id]);
    $pendientes = $stmtCheck->fetchColumn();

    if ($pendientes == 0) {
      // 4. Marcar en historial como completada
      // Obtener el ID del historial más reciente pendiente
$stmtGet = $pdo->prepare("
  SELECT id FROM historial_asignaciones_recolecciones
  WHERE ruta_id = ? AND tipo_recoleccion = 'recoleccion' AND estado = 'pendiente'
  ORDER BY fecha_asignacion DESC
  LIMIT 1
");
$stmtGet->execute([$ruta_id]);
$historial_id = $stmtGet->fetchColumn();

// Si se encontró, actualizarlo
if ($historial_id) {
  $stmtUpdate = $pdo->prepare("
    UPDATE historial_asignaciones_recolecciones
    SET estado = 'completada'
    WHERE id = ?
  ");
  $stmtUpdate->execute([$historial_id]);
}



      // 5. (opcional) Marcar la ruta como inactiva
      $pdo->prepare("UPDATE rutas SET estado = 0 WHERE id = ?")->execute([$ruta_id]);
    }
  }

  header("Location: ruta_asignada_recoleccion.php");
  exit;
}

?>

<div class="col-lg-10 col-12 p-4">
  <h2>Recolecciones asignadas</h2>

  <?php if (empty($recolecciones)): ?>
    <div class="alert alert-info">No tienes recolecciones pendientes por recoger.</div>
  <?php else: ?>
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>Ruta</th>
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
            <td><?= htmlspecialchars($r['ruta_nombre']) ?></td>
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
