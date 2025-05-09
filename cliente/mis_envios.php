 <?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'cliente') {
  header("Location: ../login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

// Obtener ID del cliente
$stmt = $pdo->prepare("SELECT id FROM clientes WHERE user_id = ?");
$stmt->execute([$user_id]);
$cliente = $stmt->fetch();
$cliente_id = $cliente['id'] ?? 0;

// Filtro opcional por estado
$filtro = $_GET['estado'] ?? '';

$query = "
  SELECT p.*, 
         zo.numero AS zona_origen, mo.nombre AS muni_origen,
         zd.numero AS zona_destino, md.nombre AS muni_destino
  FROM paquetes p
  JOIN zona zo ON p.zona_origen_id = zo.id
  JOIN municipios mo ON zo.municipio_id = mo.id
  JOIN zona zd ON p.zona_destino_id = zd.id
  JOIN municipios md ON zd.municipio_id = md.id
  WHERE p.cliente_id = ?
";

$params = [$cliente_id];

if ($filtro && in_array($filtro, ['pendiente','en_proceso','entregado','anulado'])) {
  $query .= " AND p.estado_envio = ?";
  $params[] = $filtro;
}

$query .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$paquetes = $stmt->fetchAll();

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Mis Envíos</h2>

  <div class="mb-3">
    <a href="?estado=" class="btn btn-outline-secondary <?= $filtro == '' ? 'active' : '' ?>">Todos</a>
    <a href="?estado=pendiente" class="btn btn-outline-secondary <?= $filtro == 'pendiente' ? 'active' : '' ?>">Pendientes</a>
    <a href="?estado=en_proceso" class="btn btn-outline-secondary <?= $filtro == 'en_proceso' ? 'active' : '' ?>">En proceso</a>
    <a href="?estado=entregado" class="btn btn-outline-secondary <?= $filtro == 'entregado' ? 'active' : '' ?>">Entregados</a>
    <a href="?estado=anulado" class="btn btn-outline-secondary <?= $filtro == 'anulado' ? 'active' : '' ?>">Anulados</a>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>Origen</th>
          <th>Destino</th>
          <th>Tamaño</th>
          <th>Peso (kg)</th>
          <th>Estado</th>
          <th>Fecha</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($paquetes as $p): ?>
        <tr>
          <td><?= $p['muni_origen'] ?> - Zona <?= $p['zona_origen'] ?></td>
          <td><?= $p['muni_destino'] ?> - Zona <?= $p['zona_destino'] ?></td>
          <td><?= htmlspecialchars($p['tamano']) ?></td>
          <td><?= $p['peso'] ?></td>
          <td>
            <span class="badge bg-<?= match($p['estado_envio']) {
              'pendiente' => 'secondary',
              'en_proceso' => 'warning',
              'entregado' => 'success',
              'anulado' => 'danger',
              default => 'light'
            } ?>">
              <?= ucfirst($p['estado_envio']) ?>
            </span>
          </td>
          <td><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'partials/footer.php'; ?>