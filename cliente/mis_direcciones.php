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

// Obtener direcciones del cliente
$query = $pdo->prepare("
  SELECT d.*, 
         z.numero AS zona,
         m.nombre AS municipio,
         dep.nombre AS departamento
  FROM direcciones d
  JOIN zona z ON d.zona_id = z.id
  JOIN municipios m ON d.municipio_id = m.id
  JOIN departamentos dep ON d.departamento_id = dep.id
  WHERE d.cliente_id = ?
  ORDER BY d.tipo, d.id DESC
");
$query->execute([$cliente_id]);
$direcciones = $query->fetchAll();

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Mis Direcciones</h2>
  <a href="crear_direccion.php" class="btn btn-success mb-3">+ Nueva Dirección</a>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>Tipo</th>
          <th>Calle y Número</th>
          <th>Zona</th>
          <th>Municipio</th>
          <th>Departamento</th>
          <th>Referencia</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($direcciones as $d): ?>
        <tr>
          <td><?= ucfirst($d['tipo']) ?></td>
          <td><?= htmlspecialchars($d['calle']) ?> #<?= htmlspecialchars($d['numero']) ?></td>
          <td>Zona <?= $d['zona'] ?></td>
          <td><?= $d['municipio'] ?></td>
          <td><?= $d['departamento'] ?></td>
          <td><?= htmlspecialchars($d['referencia']) ?></td>
          <td>
            <a href="editar_direccion.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'partials/footer.php'; ?>