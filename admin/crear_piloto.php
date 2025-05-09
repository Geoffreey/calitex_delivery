 <?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

// Obtener flotas activas
$flotas = $pdo->query("SELECT id, tipo FROM flotas ORDER BY tipo ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre    = $_POST['nombre'];
  $apellido  = $_POST['apellido'];
  $telefono  = $_POST['telefono'];
  $email     = $_POST['email'];
  $flota_id  = $_POST['flota_id'];
  $licencia  = $_POST['licencia'];
  $password  = password_hash('piloto123', PASSWORD_DEFAULT);

  $pdo->beginTransaction();

  try {
    // Insertar en users
    $stmt = $pdo->prepare("INSERT INTO users (nombre, apellido, telefono, email, password, rol) VALUES (?, ?, ?, ?, ?, 'piloto')");
    $stmt->execute([$nombre, $apellido, $telefono, $email, $password]);
    $user_id = $pdo->lastInsertId();

    // Insertar en pilotos
    $stmt = $pdo->prepare("INSERT INTO pilotos (user_id, flota_id, licencia) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $flota_id, $licencia]);

    $pdo->commit();
    header("Location: pilotos.php");
    exit;
  } catch (Exception $e) {
    $pdo->rollBack();
    echo "Error al crear piloto: " . $e->getMessage();
  }
}

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Nuevo Piloto</h2>
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
      <label class="form-label">Correo electrónico</label>
      <input type="email" name="email" class="form-control" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Flota asignada</label>
      <select name="flota_id" class="form-select" required>
        <option value="">Selecciona una flota</option>
        <?php foreach ($flotas as $flota): ?>
          <option value="<?= $flota['id'] ?>"><?= htmlspecialchars($flota['tipo']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">Licencia</label>
      <input type="text" name="licencia" class="form-control" required>
    </div>
    <div class="col-12">
      <p class="text-muted">Contraseña por defecto: <strong>piloto123</strong></p>
      <button type="submit" class="btn btn-success">Guardar</button>
      <a href="pilotos.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>

<?php include 'partials/footer.php'; ?>