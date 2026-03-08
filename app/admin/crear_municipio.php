 <?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

// Obtener departamentos activos
$departamentos = $pdo->query("SELECT id, nombre FROM departamentos ORDER BY nombre ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = $_POST['nombre'];
  $departamento_id = $_POST['departamento_id'];

  try {
    $stmt = $pdo->prepare("INSERT INTO municipios (nombre, departamento_id) VALUES (?, ?)");
    $stmt->execute([$nombre, $departamento_id]);
    header("Location: municipios.php");
    exit;
  } catch (Exception $e) {
    echo "Error al guardar municipio: " . $e->getMessage();
  }
}

include 'partials/header.php';
//include 'partials/sidebar.php';
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Nuevo Municipio</h2>
  <form method="POST" class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Nombre del municipio</label>
      <input type="text" name="nombre" class="form-control" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Departamento</label>
      <select name="departamento_id" class="form-select" required>
        <option value="">Seleccione un departamento</option>
        <?php foreach ($departamentos as $dep): ?>
          <option value="<?= $dep['id'] ?>"><?= htmlspecialchars($dep['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-12">
      <button type="submit" class="btn btn-success">Guardar</button>
      <a href="municipios.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>

<?php include 'partials/footer.php'; ?>