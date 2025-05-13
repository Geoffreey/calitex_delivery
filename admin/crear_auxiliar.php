<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = $_POST['nombre'];
  $apellido = $_POST['apellido'];
  $email = $_POST['email'];
  $bodega_asignada = $_POST['bodega_asignada'];
  $password = password_hash('auxiliar123', PASSWORD_DEFAULT); // Contraseña predeterminada

  try {
    // Insertar en users
    $stmt = $pdo->prepare("INSERT INTO users (nombre, apellido, email, password, rol) VALUES (?, ?, ?, ?, 'auxiliar')");
    $stmt->execute([$nombre, $apellido, $email, $password]);

    $user_id = $pdo->lastInsertId();

    // Insertar en clientes
    $stmt = $pdo->prepare("INSERT INTO auxiliares (user_id, bodega_asignada) VALUES (?, '')");
    $stmt->execute([$user_id]);

    header("Location: auxiliares.php");
    exit;
  } catch (Exception $e) {
    echo "Error al guardar auxiliar: " . $e->getMessage();
  }
}

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Nuevo Auxiliar de Bodega</h2>
  <form method="POST" class="row g-3">
    <div class="col-md-4">
      <label class="form-label">Nombre</label>
      <input type="text" name="nombre" class="form-control" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Apellido</label>
      <input type="text" name="apellido" class="form-control" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Bodega asignada</label>
      <input type="text" name="bodega_asignada" class="form-control" required>
    </div>
    <div class="col-12">
      <button type="submit" class="btn btn-success">Guardar</button>
      <a href="auxiliares.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>

<?php include 'partials/footer.php'; ?>