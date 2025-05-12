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
$direcciones = $pdo->prepare("
  SELECT d.id, d.tipo, d.calle, d.numero, z.numero AS zona, m.nombre AS municipio, dep.nombre AS departamento
  FROM direcciones d
  JOIN zona z ON d.zona_id = z.id
  JOIN municipios m ON d.municipio_id = m.id
  JOIN departamentos dep ON d.departamento_id = dep.id
  WHERE d.cliente_id = ?
  ORDER BY d.tipo, d.id DESC
");
$direcciones->execute([$cliente_id]);
$dir_all = $direcciones->fetchAll();

$recolecciones = array_filter($dir_all, fn($d) => $d['tipo'] === 'recoleccion');
$entregas = array_filter($dir_all, fn($d) => $d['tipo'] === 'entrega');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $tamano = $_POST['tamano'];
  $peso = $_POST['peso'];
  $descripcion = $_POST['descripcion'];
  $direccion_origen_id = $_POST['direccion_origen_id'];
  $direccion_destino_id = $_POST['direccion_destino_id'];

  // Obtener zona_id de ambas direcciones
  $zona_stmt = $pdo->prepare("SELECT zona_id FROM direcciones WHERE id = ?");
  $zona_stmt->execute([$direccion_origen_id]);
  $zona_origen_id = $zona_stmt->fetchColumn();

  $zona_stmt->execute([$direccion_destino_id]);
  $zona_destino_id = $zona_stmt->fetchColumn();

  // Buscar tarifa compatible
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
      (cliente_id, tamano, peso, descripcion, zona_origen_id, zona_destino_id, direccion_origen_id, direccion_destino_id, tarifa_id) 
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
      $cliente_id, $tamano, $peso, $descripcion,
      $zona_origen_id, $zona_destino_id,
      $direccion_origen_id, $direccion_destino_id,
      $tarifa_id
    ]);
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
      <label class="form-label">Dirección de Recolección</label>
      <select name="direccion_origen_id" class="form-select" required>
        <option value="">Seleccione dirección</option>
        <?php foreach ($recolecciones as $dir): ?>
          <option value="<?= $dir['id'] ?>">
            <?= $dir['calle'] ?> #<?= $dir['numero'] ?>, Zona <?= $dir['zona'] ?>, <?= $dir['municipio'] ?>, <?= $dir['departamento'] ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">Dirección de Entrega</label>
      <select name="direccion_destino_id" class="form-select" required>
        <option value="">Seleccione dirección</option>
        <?php foreach ($entregas as $dir): ?>
          <option value="<?= $dir['id'] ?>">
            <?= $dir['calle'] ?> #<?= $dir['numero'] ?>, Zona <?= $dir['zona'] ?>, <?= $dir['municipio'] ?>, <?= $dir['departamento'] ?>
          </option>
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