<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
  echo "ID de municipio no proporcionado.";
  exit;
}

// Obtener municipio actual
$stmt = $pdo->prepare("SELECT * FROM municipios WHERE id = ?");
$stmt->execute([$id]);
$muni = $stmt->fetch();

if (!$muni) {
  echo "Municipio no encontrado.";
  exit;
}

// Obtener departamentos activos
$departamentos = $pdo->query("SELECT id, nombre FROM departamentos WHERE estado = 1 ORDER BY nombre ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = $_POST['nombre'];
  $departamento_id = $_POST['departamento_id'];

  try {
    $stmt = $pdo->prepare("UPDATE municipios SET nombre = ?, departamento_id = ? WHERE id = ?");
    $stmt->execute([$nombre, $departamento_id, $id]);
    header("Location: municipios.php");
    exit;
  } catch (Exception $e) {
    echo "Error al actualizar municipio: " . $e->getMessage();
  }
}

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Editar Municipio</h2>
  <form method="POST" class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Nombre del municipio</label>
      <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($muni['nombre']) ?>" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Departamento</label>
      <select name="departamento_id" class="form-select" required>
        <?php foreach ($departamentos as $dep): ?>
          <option value="<?= $dep['id'] ?>" <?= $dep['id'] == $muni['departamento_id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($dep['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-12">
      <button type="submit" class="btn btn-primary">Actualizar</button>
      <a href="municipios.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>

<?php include 'partials/footer.php'; ?>