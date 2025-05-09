<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $tipo       = $_POST['tipo'];
  $placa      = $_POST['placa'];
  $descripcion = $_POST['descripcion'];

  try {
    $stmt = $pdo->prepare("INSERT INTO flotas (tipo, placa, descripcion) VALUES (?, ?, ?)");
    $stmt->execute([$tipo, $placa, $descripcion]);
    header("Location: flotas.php");
    exit;
  } catch (Exception $e) {
    echo "Error al guardar flota: " . $e->getMessage();
  }
}

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Nueva Flota</h2>
  <form method="POST" class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Tipo de flota</label>
      <input type="text" name="tipo" class="form-control" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Placa</label>
      <input type="text" name="placa" class="form-control" required>
    </div>
    <div class="col-12">
      <label class="form-label">Descripción</label>
      <textarea name="descripcion" class="form-control" rows="3"></textarea>
    </div>
    <div class="col-12">
      <button type="submit" class="btn btn-success">Guardar</button>
      <a href="flotas.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>

<?php include 'partials/footer.php'; ?>