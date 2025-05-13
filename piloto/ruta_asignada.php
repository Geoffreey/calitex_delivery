<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'piloto') {
  header("Location: ../login.php");
  exit;
}

// Obtener el ID del piloto
$stmt = $pdo->prepare("SELECT id FROM pilotos WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$piloto_id = $stmt->fetchColumn();

if (!$piloto_id) {
  echo "No se encontró el perfil del piloto.";
  exit;
}

// Obtener la ruta asignada al piloto
$stmt = $pdo->prepare("SELECT * FROM rutas WHERE piloto_id = ? ORDER BY id DESC LIMIT 1");
$stmt->execute([$piloto_id]);
$ruta = $stmt->fetch();

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Mi Ruta Asignada</h2>

  <?php if (!$ruta): ?>
    <div class="alert alert-warning">No tienes ninguna ruta asignada actualmente.</div>
  <?php else: ?>
    <div class="mb-4">
      <strong>Ruta:</strong> <?= htmlspecialchars($ruta['nombre']) ?><br>
      <strong>ID:</strong> <?= $ruta['id'] ?>
    </div>

    <h4>Paquetes asignados a esta ruta</h4>

    <?php
    // Obtener paquetes asignados a esta ruta
    $stmt = $pdo->prepare("
      SELECT p.*, u.nombre AS cliente_nombre, u.apellido AS cliente_apellido
      FROM paquetes p
      JOIN clientes c ON p.cliente_id = c.id
      JOIN users u ON c.user_id = u.id
      WHERE p.ruta_id = ?
      ORDER BY p.created_at ASC
    ");
    $stmt->execute([$ruta['id']]);
    $paquetes = $stmt->fetchAll();
    ?>

    <?php if (empty($paquetes)): ?>
      <div class="alert alert-info">Esta ruta aún no tiene paquetes asignados.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-bordered table-striped">
          <thead class="table-light">
            <tr>
              <th>Cliente</th>
              <th>Tamaño</th>
              <th>Peso</th>
              <th>Estado</th>
              <th>Fecha</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($paquetes as $p): ?>
              <tr>
                <td><?= $p['cliente_nombre'] . ' ' . $p['cliente_apellido'] ?></td>
                <td><?= $p['tamano'] ?></td>
                <td><?= $p['peso'] ?> kg</td>
                <td><?= ucfirst($p['estado_envio']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php include 'partials/footer.php'; ?>