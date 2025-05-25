<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'piloto') {
  header("Location: ../login.php");
  exit;
}

$stmt = $pdo->prepare("SELECT id FROM pilotos WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$piloto = $stmt->fetch();

if (!$piloto) {
  echo "<div class='p-4 alert alert-warning'>No tienes un perfil de piloto asignado.</div>";
  include 'partials/footer.php';
  exit;
}

$piloto_id = $piloto['id'];

include 'partials/header.php';
include 'partials/sidebar.php';

// Obtener las rutas asignadas al piloto
$stmt = $pdo->prepare("SELECT id, nombre FROM rutas WHERE piloto_id = ?");
$stmt->execute([$piloto_id]);
$rutas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($rutas)) {
  echo "<div class='p-4 alert alert-warning'>No tienes rutas asignadas.</div>";
  include 'partials/footer.php';
  exit;
}

$ruta_ids = array_column($rutas, 'id');
$placeholders = implode(',', array_fill(0, count($ruta_ids), '?'));

// Marcar como entregado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recoleccion_id'])) {
  $recoleccion_id = $_POST['recoleccion_id'];

  // 1. Actualizar la recolección como entregada
  $stmt = $pdo->prepare("UPDATE recolecciones SET estado_recoleccion = 'entregado' WHERE id = ?");
  $stmt->execute([$recoleccion_id]);

  // 2. Obtener la ruta_entrega_id de esta recolección
  $stmtRuta = $pdo->prepare("SELECT ruta_entrega_id FROM recolecciones WHERE id = ?");
  $stmtRuta->execute([$recoleccion_id]);
  $ruta_id = $stmtRuta->fetchColumn();

  if ($ruta_id) {
    // 3. Verificar si todas las recolecciones con esa ruta_entrega_id ya están entregadas o canceladas
    $stmtCheck = $pdo->prepare("
      SELECT COUNT(*) FROM recolecciones 
      WHERE ruta_entrega_id = ? 
      AND estado_recoleccion NOT IN ('entregado', 'cancelado')
    ");
    $stmtCheck->execute([$ruta_id]);
    $pendientes = $stmtCheck->fetchColumn();

    if ($pendientes == 0) {
      // 4. Actualizar el historial de entrega como completado
      $stmtHist = $pdo->prepare("
        UPDATE historial_asignaciones_recolecciones
        SET estado = 'completada'
        WHERE ruta_id = ? AND tipo_recoleccion = 'entrega'
      ");
      $stmtHist->execute([$ruta_id]);

      // 5. (opcional) Actualizar tabla rutas a estado 0
      $stmtRutaEstado = $pdo->prepare("UPDATE rutas SET estado = 0 WHERE id = ?");
      $stmtRutaEstado->execute([$ruta_id]);
    }
  }

  header("Location: ruta_recolecciones_entrega.php");
  exit;
}



// Obtener recolecciones por entregar
$stmt = $pdo->prepare("
  SELECT r.id, r.tamano, r.peso, r.descripcion, r.created_at,
         u.nombre AS cliente_nombre, u.apellido AS cliente_apellido,
         rutas.nombre AS ruta_nombre
  FROM recolecciones r
  JOIN clientes c ON r.cliente_id = c.id
  JOIN users u ON c.user_id = u.id
  JOIN rutas ON r.ruta_entrega_id = rutas.id
  WHERE r.ruta_entrega_id IN ($placeholders) AND r.estado_recoleccion = 'recibido'
  ORDER BY r.created_at ASC
");
$stmt->execute($ruta_ids);
$recolecciones = $stmt->fetchAll();
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Recolecciones por Entregar</h2>

  <?php if (empty($recolecciones)): ?>
    <div class="alert alert-info">No tienes recolecciones pendientes por entregar.</div>
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
              <form method="POST" onsubmit="return confirm('¿Confirmar entrega de la recolección?');">
                <input type="hidden" name="recoleccion_id" value="<?= $r['id'] ?>">
                <button type="submit" class="btn btn-success btn-sm">Entregado</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php include 'partials/footer.php'; ?>
