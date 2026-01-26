<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
  echo "ID de cliente no proporcionado.";
  exit;
}

// Obtener datos del cliente
$stmt = $pdo->prepare("SELECT u.id AS user_id, c.id AS cliente_id, u.nombre, u.apellido, u.telefono, u.email
                       FROM clientes c
                       INNER JOIN users u ON c.user_id = u.id
                       WHERE c.id = ?");
$stmt->execute([$id]);
$cliente = $stmt->fetch();

if (!$cliente) {
  echo "Cliente no encontrado.";
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = $_POST['nombre'];
  $apellido = $_POST['apellido'];
  $telefono = $_POST['telefono'];
  $email = $_POST['email'];
  $password = $_POST['password'];

  try {
    if (!empty($password)) {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $pdo->prepare("UPDATE users SET nombre = ?, apellido = ?, telefono = ?, email = ?, password = ? WHERE id = ?");
      $stmt->execute([$nombre, $apellido, $telefono, $email, $hash, $cliente['user_id']]);
    } else {
      $stmt = $pdo->prepare("UPDATE users SET nombre = ?, apellido = ?, telefono = ?, email = ? WHERE id = ?");
      $stmt->execute([$nombre, $apellido, $telefono, $email, $cliente['user_id']]);
    }

    header("Location: clientes.php");
    exit;
  } catch (Exception $e) {
    echo "Error al actualizar cliente: " . $e->getMessage();
  }
}

include 'partials/header.php';
//include 'partials/sidebar.php';
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Editar Cliente</h2>
  <form method="POST" class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Nombre</label>
      <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($cliente['nombre']) ?>" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Apellido</label>
      <input type="text" name="apellido" class="form-control" value="<?= htmlspecialchars($cliente['apellido']) ?>" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Teléfono</label>
      <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($cliente['telefono']) ?>" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Correo</label>
      <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($cliente['email']) ?>" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Nueva contraseña (opcional)</label>
      <input type="password" name="password" class="form-control">
    </div>
    <div class="col-12">
      <button type="submit" class="btn btn-primary">Actualizar</button>
      <a href="clientes.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>

<?php include 'partials/footer.php'; ?>
