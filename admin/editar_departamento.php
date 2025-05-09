<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
  echo "ID de departamento no proporcionado.";
  exit;
}

// Obtener departamento actual
$stmt = $pdo->prepare("SELECT * FROM departamentos WHERE id = ?");
$stmt->execute([$id]);
$dep = $stmt->fetch();

if (!$dep) {
  echo "Departamento no encontrado.";
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = $_POST['nombre'];

  try {
    $stmt = $pdo->prepare("UPDATE departamentos SET nombre = ? WHERE id = ?");
    $stmt->execute([$nombre, $id]);
    header("Location: departamentos.php");
    exit;
  } catch (Exception $e) {
    echo "Error al actualizar departamento: " . $e->getMessage();
  }
}

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Editar Departamento</h2>
  <form method="POST" class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Nombre del departamento</label>
      <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($dep['nombre']) ?>" required>
    </div>
    <div class="col-12">
      <button type="submit" class="btn btn-primary">Actualizar</button>
      <a href="departamentos.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>

<?php include 'partials/footer.php'; ?>