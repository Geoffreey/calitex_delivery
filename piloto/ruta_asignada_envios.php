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
  echo "<div class='p-4 alert alert-warning'>No tienes un perfil de piloto asignado aún.</div>";
  include 'partials/footer.php';
  exit;
}

$piloto_id = $piloto['id'];

include 'partials/header.php';
include 'partials/sidebar.php';

$stmt = $pdo->prepare("SELECT id, nombre FROM rutas WHERE piloto_id = ?");
$stmt->execute([$piloto_id]);
$rutas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($rutas)) {
  echo "<div class='p-4 alert alert-warning'>No tienes rutas asignadas aún.</div>";
  include 'partials/footer.php';
  exit;
}

$ruta_ids = array_column($rutas, 'id');
$placeholders = implode(',', array_fill(0, count($ruta_ids), '?'));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['envio_id'])) {
  $envio_id = $_POST['envio_id'];

  // Obtener la ruta_id del envío
  $stmtRuta = $pdo->prepare("SELECT ruta_id FROM envios WHERE id = ?");
  $stmtRuta->execute([$envio_id]);
  $ruta_id = $stmtRuta->fetchColumn();

  // Actualizar estado del envío
  $stmt = $pdo->prepare("UPDATE envios SET estado_envio = 'recibido' WHERE id = ? AND ruta_id IN ($placeholders)");
  $stmt->execute(array_merge([$envio_id], $ruta_ids));

  // Verificar si la ruta ahora tiene todos los envíos entregados o cancelados
$ruta_actual_stmt = $pdo->prepare("SELECT ruta_id FROM envios WHERE id = ?");
$ruta_actual_stmt->execute([$envio_id]);
$ruta_id_entregada = $ruta_actual_stmt->fetchColumn();

$pendientes_stmt = $pdo->prepare("SELECT COUNT(*) FROM envios WHERE ruta_id = ? AND estado_envio NOT IN ('recibido', 'cancelado')");
$pendientes_stmt->execute([$ruta_id_entregada]);
$pendientes = $pendientes_stmt->fetchColumn();

if ($pendientes == 0) {
    // Actualiza la tabla rutas
    $pdo->prepare("UPDATE rutas SET estado = 0 WHERE id = ?")->execute([$ruta_id_entregada]);

    // Y el historial (si lo estás usando para mostrar también el estado)
    $pdo->prepare("UPDATE historial_asignaciones SET estado = 'completada' WHERE ruta_id = ?")->execute([$ruta_id_entregada]);
}


  // Verificar si todos los envíos de esa ruta están completados
  $stmtCheck = $pdo->prepare("
    SELECT COUNT(*) FROM envios 
    WHERE ruta_id = ? AND estado_envio NOT IN ('recibido', 'cancelado')
  ");
  $stmtCheck->execute([$ruta_id]);
  $pendientes = $stmtCheck->fetchColumn();

  if ($pendientes == 0) {
    $stmtFinalizar = $pdo->prepare("UPDATE rutas SET estado = 0 WHERE id = ?");
    $stmtFinalizar->execute([$ruta_id]);
  }

  header("Location: ruta_asignada_envios.php");
  exit;
}

$stmt = $pdo->prepare("
  SELECT e.id, e.tamano, e.peso, e.descripcion, e.created_at, e.ruta_id,
         u.nombre AS cliente_nombre, u.apellido AS cliente_apellido,
         r.nombre AS ruta_nombre
  FROM envios e
  JOIN clientes c ON e.cliente_id = c.id
  JOIN users u ON c.user_id = u.id
  JOIN rutas r ON e.ruta_id = r.id
  WHERE e.ruta_id IN ($placeholders) AND e.estado_envio = 'pendiente'
");
$stmt->execute($ruta_ids);
$envios = $stmt->fetchAll();
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Envíos asignados</h2>

  <?php if (empty($envios)): ?>
    <div class="alert alert-info">No tienes envíos pendientes por entregar.</div>
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
        <?php foreach ($envios as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['ruta_nombre']) ?></td>
            <td><?= $r['cliente_nombre'] . ' ' . $r['cliente_apellido'] ?></td>
            <td><?= $r['tamano'] ?></td>
            <td><?= $r['peso'] ?> kg</td>
            <td><?= $r['descripcion'] ?></td>
            <td><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
            <td>
              <form method="POST" onsubmit="return confirm('¿Confirmar entrega del paquete?');">
                <input type="hidden" name="envio_id" value="<?= $r['id'] ?>">
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
