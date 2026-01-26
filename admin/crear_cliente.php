<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = $_POST['nombre'];
  $apellido = $_POST['apellido'];
  $telefono = $_POST['telefono'];
  $email = $_POST['email'];
  $password = password_hash('cliente123', PASSWORD_DEFAULT); // Contraseña predeterminada

  $pdo->beginTransaction();

  try {
    // Insertar en users
    $stmt = $pdo->prepare("INSERT INTO users (nombre, apellido, telefono, email, password, rol) VALUES (?, ?, ?, ?, ?, 'cliente')");
    $stmt->execute([$nombre, $apellido, $telefono, $email, $password]);

    $user_id = $pdo->lastInsertId();

    // Insertar en clientes
    $stmt = $pdo->prepare("INSERT INTO clientes (user_id, direccion) VALUES (?, '')");
    $stmt->execute([$user_id]);

    $pdo->commit();
    header("Location: clientes.php");
    exit;
  } catch (Exception $e) {
    $pdo->rollBack();
    echo "Error al crear cliente: " . $e->getMessage();
  }
}

include 'partials/header.php';
//include 'partials/sidebar.php';
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Nuevo Cliente</h2>
  <form method="POST" class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Nombre</label>
      <input type="text" name="nombre" class="form-control" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Apellido</label>
      <input type="text" name="apellido" class="form-control" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Teléfono</label>
      <input type="text" name="telefono" class="form-control" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Correo</label>
      <input type="email" name="email" class="form-control" required>
    </div>
    <div class="col-12">
      <button type="submit" class="btn btn-success">Guardar</button>
      <a href="clientes.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>

<?php include 'partials/footer.php'; ?>
