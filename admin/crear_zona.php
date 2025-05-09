<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

// Obtener municipios activos
$municipios = $pdo->query("SELECT id, nombre FROM municipios WHERE estado = 1 ORDER BY nombre ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $municipio_id = $_POST['municipio_id'];
  $numero = $_POST['numero'];

  try {
    $stmt = $pdo->prepare("INSERT INTO zona (municipio_id, numero) VALUES (?, ?)");
    $stmt->execute([$municipio_id, $numero]);
    header("Location: zonas.php");
    exit;
  } catch (Exception $e) {
    echo "Error al guardar zona: " . $e->getMessage();
  }
}

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Nueva Zona</h2>
  <form method="POST" class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Municipio</label>
      <select name="municipio_id" class="form-select" required>
        <option value="">Seleccione un municipio</option>
        <?php foreach ($municipios as $muni): ?>
          <option value="<?= $muni['id'] ?>"><?= htmlspecialchars($muni['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">Número de zona</label>
      <input type="text" name="numero" class="form-control" required>
    </div>
    <div class="col-12">
      <button type="submit" class="btn btn-success">Guardar</button>
      <a href="zonas.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>

<?php include 'partials/footer.php'; ?>