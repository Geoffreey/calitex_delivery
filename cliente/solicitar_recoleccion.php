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

// Obtener zonas
$zonas = $pdo->query("
  SELECT z.id, z.numero, m.nombre AS municipio
  FROM zona z
  JOIN municipios m ON z.municipio_id = m.id
  ORDER BY m.nombre, z.numero
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $tamano = $_POST['tamano'];
  $peso = $_POST['peso'];
  $descripcion = $_POST['descripcion'];
  $zona_origen_id = $_POST['zona_origen_id'];
  $zona_destino_id = $_POST['zona_destino_id'];

  // Buscar tarifa automáticamente
  $stmt = $pdo->prepare("
    SELECT id FROM tarifas 
    WHERE zona_origen_id = ? AND zona_destino_id = ? 
    AND tamano = ? AND peso_min <= ? AND peso_max >= ? AND estado = 1
    LIMIT 1
  ");
  $stmt->execute([$zona_origen_id, $zona_destino_id, $tamano, $peso, $peso]);
  $tarifa = $stmt->fetch();
  $tarifa_id = $tarifa ? $tarifa['id'] : null;

  try {
    $stmt = $pdo->prepare("
      INSERT INTO paquetes 
      (cliente_id, tamano, peso, descripcion, zona_origen_id, zona_destino_id, tarifa_id) 
      VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$cliente_id, $tamano, $peso, $descripcion, $zona_origen_id, $zona_destino_id, $tarifa_id]);
    header("Location: mis_envios.php");
    exit;
  } catch (Exception $e) {
    echo "Error al solicitar recolección: " . $e->getMessage();
  }
}

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Solicitar Recolección</h2>
  <form method="POST" class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Zona de Recolección</label>
      <select name="zona_origen_id" class="form-select" required>
        <option value="">Seleccione zona</option>
        <?php foreach ($zonas as $z): ?>
          <option value="<?= $z['id'] ?>"><?= $z['municipio'] ?> - Zona <?= $z['numero'] ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">Zona de Destino</label>
      <select name="zona_destino_id" class="form-select" required>
        <option value="">Seleccione zona</option>
        <?php foreach ($zonas as $z): ?>
          <option value="<?= $z['id'] ?>"><?= $z['municipio'] ?> - Zona <?= $z['numero'] ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Tamaño</label>
      <input type="text" name="tamano" class="form-control" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Peso (kg)</label>
      <input type="number" name="peso" step="0.01" class="form-control" required>
    </div>
    <div class="col-12">
      <label class="form-label">Descripción del paquete</label>
      <textarea name="descripcion" class="form-control" rows="3" required></textarea>
    </div>
    <div class="col-12">
      <button type="submit" class="btn btn-success">Enviar solicitud</button>
      <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>

<?php include 'partials/footer.php'; ?>