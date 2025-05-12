<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

$id = $_GET['id'] ?? null;

$stmt = $pdo->prepare("SELECT * FROM auxiliares WHERE id = ?");
$stmt->execute([$id]);
$aux = $stmt->fetch();

if (!$aux) {
  echo "Auxiliar no encontrado.";
  exit;
}

$usuarios = $pdo->query("
  SELECT id, email FROM users 
  WHERE rol = 'auxiliar'
  ORDER BY email
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = $_POST['nombre'];
  $apellido = $_POST['apellido'];
  $email = $_POST['email'];
  $bodega = $_POST['bodega_asignada'];
  $user_id = $_POST['user_id'];

  try {
    $stmt = $pdo->prepare("UPDATE auxiliares SET nombre = ?, apellido = ?, email = ?, bodega_asignada = ? WHERE id = ?");
    $stmt->execute([$nombre, $apellido, $email, $bodega, $user_id, $id]);

    header("Location: auxiliares.php");
    exit;
  } catch (Exception $e) {
    echo "Error al actualizar auxiliar: " . $e->getMessage();
  }
}

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Editar Auxiliar</h2>
  <form method="POST" class="row g-3">
    <div class="col-md-4">
      <label class="form-label">Nombre</label>
      <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($aux['nombre']) ?>" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Apellido</label>
      <input type="text" name="apellido" class="form-control" value="<?= htmlspecialchars($aux['apellido']) ?>" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($aux['email']) ?>" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Bodega asignada</label>
      <input type="text" name="bodega_asignada" class="form-control" value="<?= htmlspecialchars($aux['bodega_asignada']) ?>" required>
    </div>
    <div class="col-12">
      <button type="submit" class="btn btn-primary">Actualizar</button>
      <a href="auxiliares.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>

<?php include 'partials/footer.php'; ?>