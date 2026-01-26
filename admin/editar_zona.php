<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
  echo "ID de zona no proporcionado.";
  exit;
}

// Obtener zona actual
$stmt = $pdo->prepare("SELECT * FROM zona WHERE id = ?");
$stmt->execute([$id]);
$zona = $stmt->fetch();

if (!$zona) {
  echo "Zona no encontrada.";
  exit;
}

// Obtener municipios activos
$municipios = $pdo->query("SELECT id, nombre FROM municipios WHERE estado = 1 ORDER BY nombre ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $municipio_id = $_POST['municipio_id'];
  $numero = $_POST['numero'];

  try {
    $stmt = $pdo->prepare("UPDATE zona SET municipio_id = ?, numero = ? WHERE id = ?");
    $stmt->execute([$municipio_id, $numero, $id]);
    header("Location: zonas.php");
    exit;
  } catch (Exception $e) {
    echo "Error al actualizar zona: " . $e->getMessage();
  }
}

include 'partials/header.php';
//include 'partials/sidebar.php';
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Editar Zona</h2>
  <form method="POST" class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Municipio</label>
      <select name="municipio_id" class="form-select" required>
        <?php foreach ($municipios as $muni): ?>
          <option value="<?= $muni['id'] ?>" <?= $muni['id'] == $zona['municipio_id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($muni['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">Número de zona</label>
      <input type="text" name="numero" class="form-control" value="<?= htmlspecialchars($zona['numero']) ?>" required>
    </div>
    <div class="col-12">
      <button type="submit" class="btn btn-primary">Actualizar</button>
      <a href="zonas.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>

<?php include 'partials/footer.php'; ?>