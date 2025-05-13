<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'cliente') {
  header("Location: ../login.php");
  exit;
}

// Obtener ID del cliente
$stmt = $pdo->prepare("SELECT id FROM clientes WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$cliente_id = $stmt->fetchColumn();

// Obtener direcciones del cliente
$stmt = $pdo->prepare("SELECT d.*, z.numero AS zona, m.nombre AS municipio, dp.nombre AS departamento 
                      FROM direcciones d 
                      JOIN zona z ON d.zona_id = z.id 
                      JOIN municipios m ON d.municipio_id = m.id 
                      JOIN departamentos dp ON d.departamento_id = dp.id 
                      WHERE d.cliente_id = ?");
$stmt->execute([$cliente_id]);
$direcciones = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $tamano = $_POST['tamano'];
  $peso = $_POST['peso'];
  $descripcion = $_POST['descripcion'];
  $direccion_origen_id = $_POST['direccion_origen_id'];
  $direccion_destino_id = $_POST['direccion_destino_id'];

  try {
    $stmt = $pdo->prepare("
      INSERT INTO recolecciones 
      (cliente_id, direccion_origen_id, direccion_destino_id, tamano, peso, descripcion)
      VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$cliente_id, $direccion_origen_id, $direccion_destino_id, $tamano, $peso, $descripcion]);

    header("Location: mis_recolecciones.php");
    exit;
  } catch (Exception $e) {
    echo "Error al crear recolección: " . $e->getMessage();
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
        <option value="">Seleccione</option>
        <?php foreach ($direcciones as $dir): ?>
          <option value="<?= $dir['id'] ?>">
            <?= "{$dir['calle']} #{$dir['numero']}, Zona {$dir['zona']}, {$dir['municipio']}, {$dir['departamento']}" ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">Dirección de Entrega</label>
      <select name="direccion_destino_id" class="form-select" required>
        <option value="">Seleccione</option>
        <?php foreach ($direcciones as $dir): ?>
          <option value="<?= $dir['id'] ?>">
            <?= "{$dir['calle']} #{$dir['numero']}, Zona {$dir['zona']}, {$dir['municipio']}, {$dir['departamento']}" ?>
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
      <label class="form-label">Descripción</label>
      <textarea name="descripcion" class="form-control" rows="3" required></textarea>
    </div>
    <div class="col-12">
      <button type="submit" class="btn btn-success">Crear recolección</button>
      <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>

<?php include 'partials/footer.php'; ?>