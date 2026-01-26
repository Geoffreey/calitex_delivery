<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre      = $_POST['nombre'];
  $tamano      = $_POST['tamano'];
  $peso        = $_POST['peso'];
  $tarifa      = $_POST['tarifa'];
  $descripcion = $_POST['descripcion'];

  try {
    $stmt = $pdo->prepare("INSERT INTO paquetes (nombre, tamano, peso, tarifa, descripcion) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nombre, $tamano, $peso, $tarifa, $descripcion]);

    header("Location: paquetes.php");
    exit;
  } catch (Exception $e) {
    echo "Error al guardar paquete: " . $e->getMessage();
  }
}

include 'partials/header.php';
//include 'partials/sidebar.php';
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Nuevo Tipo de Paquete</h2>
  <form method="POST" class="row g-3">
    <div class="col-md-4">
      <label class="form-label">Nombre o Tipo</label>
      <input type="text" name="nombre" class="form-control" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Tamaño</label>
      <input type="text" name="tamano" class="form-control" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Peso Referencial (kg)</label>
      <input type="number" step="0.01" name="peso" class="form-control" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Tarifa (Q)</label>
      <input type="number" step="0.01" name="tarifa" class="form-control" required>
    </div>
    <div class="col-12">
      <label class="form-label">Descripción</label>
      <textarea name="descripcion" class="form-control" rows="3"></textarea>
    </div>
    <div class="col-12">
      <button type="submit" class="btn btn-success">Guardar</button>
      <a href="paquetes.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>

<?php include 'partials/footer.php'; ?>
