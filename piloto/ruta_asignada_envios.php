<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'piloto') {
  header("Location: ../login.php");
  exit;
}

/* Obtener ID del piloto a partir del user_id de sesión*/
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

// Obtener TODAS las rutas del piloto
$stmt = $pdo->prepare("SELECT id, nombre FROM rutas WHERE piloto_id = ?");
$stmt->execute([$piloto_id]);
$rutas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($rutas)) {
  echo "<div class='p-4 alert alert-warning'>No tienes rutas asignadas aún.</div>";
  include 'partials/footer.php';
  exit;
}

// 👇🏽 AHORA definís $ruta_ids aquí
$ruta_ids = array_column($rutas, 'id');
$placeholders = implode(',', array_fill(0, count($ruta_ids), '?'));

// ✅ AHORA sí podés usar $ruta_ids
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['envio_id'])) {
  $envio_id = $_POST['envio_id'];

  $stmt = $pdo->prepare("UPDATE envios SET estado_envio = 'recibido' WHERE id = ? AND ruta_id IN ($placeholders)");
  $stmt->execute(array_merge([$envio_id], $ruta_ids));

  header("Location: ruta_asignada_envios.php");
  exit;
}


// Extraer los IDs de las rutas
$ruta_ids = array_column($rutas, 'id');
$placeholders = implode(',', array_fill(0, count($ruta_ids), '?'));

// Obtener envíos de esas rutas
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
    <div class="alert alert-info">No tienes envios pendientes por entregar.</div>
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