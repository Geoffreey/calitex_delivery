<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

// Obtener zonas con municipios
$zonas = $pdo->query("
  SELECT z.id, z.numero, m.nombre AS municipio
  FROM zona z
  JOIN municipios m ON z.municipio_id = m.id
  WHERE z.estado = 1
  ORDER BY m.nombre, z.numero
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $zona_origen_id = $_POST['zona_origen_id'];
  $zona_destino_id = $_POST['zona_destino_id'];
  $tamano = $_POST['tamano'];
  $peso_min = $_POST['peso_min'];
  $peso_max = $_POST['peso_max'];
  $precio = $_POST['precio'];

  try {
    $stmt = $pdo->prepare("
      INSERT INTO tarifas 
      (zona_origen_id, zona_destino_id, tamano, peso_min, peso_max, precio) 
      VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$zona_origen_id, $zona_destino_id, $tamano, $peso_min, $peso_max, $precio]);
    header("Location: tarifas.php");
    exit;
  } catch (Exception $e) {
    echo "Error al guardar tarifa: " . $e->getMessage();
  }
}

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Nueva Tarifa</h2>
  <form method="POST" class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Zona de Origen</label>
      <select name="zona_origen_id" class="form-select" required>
        <option value="">Seleccione una zona</option>
        <?php foreach ($zonas as $z): ?>
          <option value="<?= $z['id'] ?>">
            <?= htmlspecialchars($z['municipio']) ?> - Zona <?= $z['numero'] ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">Zona de Destino</label>
      <select name="zona_destino_id" class="form-select" required>
        <option value="">Seleccione una zona</option>
        <?php foreach ($zonas as $z): ?>
          <option value="<?= $z['id'] ?>">
            <?= htmlspecialchars($z['municipio']) ?> - Zona <?= $z['numero'] ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Tamaño</label>
      <input type="text" name="tamano" class="form-control" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Peso mínimo (kg)</label>
      <input type="number" step="0.01" name="peso_min" class="form-control" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Peso máximo (kg)</label>
      <input type="number" step="0.01" name="peso_max" class="form-control" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Precio (Q)</label>
      <input type="number" step="0.01" name="precio" class="form-control" required>
    </div>
    <div class="col-12">
      <button type="submit" class="btn btn-success">Guardar</button>
      <a href="tarifas.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>

<?php include 'partials/footer.php'; ?>