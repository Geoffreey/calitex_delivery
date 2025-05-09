<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
  echo "ID de paquete no proporcionado.";
  exit;
}

// Obtener paquete
$stmt = $pdo->prepare("SELECT * FROM paquetes WHERE id = ?");
$stmt->execute([$id]);
$paquete = $stmt->fetch();
if (!$paquete) {
  echo "Paquete no encontrado.";
  exit;
}

// Obtener clientes
$clientes = $pdo->query("
  SELECT c.id, u.nombre, u.apellido 
  FROM clientes c JOIN users u ON c.user_id = u.id 
  WHERE u.estado = 1 ORDER BY u.nombre
")->fetchAll();

// Zonas
$zonas = $pdo->query("
  SELECT z.id, z.numero, m.nombre AS municipio 
  FROM zona z JOIN municipios m ON z.municipio_id = m.id 
  WHERE z.estado = 1 ORDER BY m.nombre, z.numero
")->fetchAll();

// Estado del paquete
$estados = ['pendiente', 'en_proceso', 'entregado', 'anulado'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $cliente_id     = $_POST['cliente_id'];
  $tamano         = $_POST['tamano'];
  $peso           = $_POST['peso'];
  $descripcion    = $_POST['descripcion'];
  $zona_origen_id = $_POST['zona_origen_id'];
  $zona_destino_id= $_POST['zona_destino_id'];
  $estado_envio   = $_POST['estado_envio'];

  // Buscar tarifa compatible
  $stmt = $pdo->prepare("
    SELECT id FROM tarifas 
    WHERE zona_origen_id = ? AND zona_destino_id = ? 
    AND tamano = ? 
    AND peso_min <= ? AND peso_max >= ?
    AND estado = 1
    LIMIT 1
  ");
  $stmt->execute([$zona_origen_id, $zona_destino_id, $tamano, $peso, $peso]);
  $tarifa = $stmt->fetch();
  $tarifa_id = $tarifa ? $tarifa['id'] : null;

  try {
    $stmt = $pdo->prepare("
      UPDATE paquetes SET 
        cliente_id = ?, tamano = ?, peso = ?, descripcion = ?, 
        zona_origen_id = ?, zona_destino_id = ?, tarifa_id = ?, estado_envio = ?
      WHERE id = ?
    ");
    $stmt->execute([$cliente_id, $tamano, $peso, $descripcion, $zona_origen_id, $zona_destino_id, $tarifa_id, $estado_envio, $id]);
    header("Location: paquetes.php");
    exit;
  } catch (Exception $e) {
    echo "Error al actualizar paquete: " . $e->getMessage();
  }
}

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Editar Paquete</h2>
  <form method="POST" class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Cliente</label>
      <select name="cliente_id" class="form-select" required>
        <?php foreach ($clientes as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $c['id'] == $paquete['cliente_id'] ? 'selected' : '' ?>>
            <?= $c['nombre'] . ' ' . $c['apellido'] ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Tamaño</label>
      <input type="text" name="tamano" class="form-control" value="<?= htmlspecialchars($paquete['tamano']) ?>" required>
    </div>
    <div class="col-md-3">
      <label class="form-label">Peso (kg)</label>
      <input type="number" step="0.01" name="peso" class="form-control" value="<?= $paquete['peso'] ?>" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Zona de Origen</label>
      <select name="zona_origen_id" class="form-select" required>
        <?php foreach ($zonas as $z): ?>
          <option value="<?= $z['id'] ?>" <?= $z['id'] == $paquete['zona_origen_id'] ? 'selected' : '' ?>>
            <?= $z['municipio'] ?> - Zona <?= $z['numero'] ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">Zona de Destino</label>
      <select name="zona_destino_id" class="form-select" required>
        <?php foreach ($zonas as $z): ?>
          <option value="<?= $z['id'] ?>" <?= $z['id'] == $paquete['zona_destino_id'] ? 'selected' : '' ?>>
            <?= $z['municipio'] ?> - Zona <?= $z['numero'] ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">Estado de envío</label>
      <select name="estado_envio" class="form-select" required>
        <?php foreach ($estados as $estado): ?>
          <option value="<?= $estado ?>" <?= $estado === $paquete['estado_envio'] ? 'selected' : '' ?>>
            <?= ucfirst($estado) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-12">
      <label class="form-label">Descripción</label>
      <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($paquete['descripcion']) ?></textarea>
    </div>
    <div class="col-12">
      <button type="submit" class="btn btn-primary">Actualizar</button>
      <a href="paquetes.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>

<?php include 'partials/footer.php'; ?>