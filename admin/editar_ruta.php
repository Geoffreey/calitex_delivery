<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
  echo "ID de ruta no proporcionado.";
  exit;
}

// Obtener datos de la ruta
$stmt = $pdo->prepare("SELECT * FROM rutas WHERE id = ?");
$stmt->execute([$id]);
$ruta = $stmt->fetch();

if (!$ruta) {
  echo "Ruta no encontrada.";
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = $_POST['nombre'];
  $descripcion = $_POST['descripcion'];

  try {
    $stmt = $pdo->prepare("UPDATE rutas SET nombre = ?, descripcion = ? WHERE id = ?");
    $stmt->execute([$nombre, $descripcion, $id]);
    header("Location: rutas.php");
    exit;
  } catch (Exception $e) {
    echo "Error al actualizar ruta: " . $e->getMessage();
  }
}

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Editar Ruta</h2>
  <form method="POST" class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Nombre de la ruta</label>
      <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($ruta['nombre']) ?>" required>
    </div>
    <div class="col-12">
      <label class="form-label">Descripción</label>
      <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($ruta['descripcion']) ?></textarea>
    </div>
    <div class="col-12">
      <button type="submit" class="btn btn-primary">Actualizar</button>
      <a href="rutas.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>

<?php include 'partials/footer.php'; ?>