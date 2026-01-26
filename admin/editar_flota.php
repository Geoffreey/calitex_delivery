<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
  echo "ID de flota no proporcionado.";
  exit;
}

// Obtener flota actual
$stmt = $pdo->prepare("SELECT * FROM flotas WHERE id = ?");
$stmt->execute([$id]);
$flota = $stmt->fetch();

if (!$flota) {
  echo "Flota no encontrada.";
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $tipo       = $_POST['tipo'];
  $placa      = $_POST['placa'];
  $descripcion = $_POST['descripcion'];

  try {
    $stmt = $pdo->prepare("UPDATE flotas SET tipo = ?, placa = ?, descripcion = ? WHERE id = ?");
    $stmt->execute([$tipo, $placa, $descripcion, $id]);
    header("Location: flotas.php");
    exit;
  } catch (Exception $e) {
    echo "Error al actualizar flota: " . $e->getMessage();
  }
}

include 'partials/header.php';
//include 'partials/sidebar.php';
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Editar Flota</h2>
  <form method="POST" class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Tipo de flota</label>
      <input type="text" name="tipo" class="form-control" value="<?= htmlspecialchars($flota['tipo']) ?>" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Placa</label>
      <input type="text" name="placa" class="form-control" value="<?= htmlspecialchars($flota['placa']) ?>" required>
    </div>
    <div class="col-12">
      <label class="form-label">Descripción</label>
      <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($flota['descripcion']) ?></textarea>
    </div>
    <div class="col-12">
      <button type="submit" class="btn btn-primary">Actualizar</button>
      <a href="flotas.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>

<?php include 'partials/footer.php'; ?>