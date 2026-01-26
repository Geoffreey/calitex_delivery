<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
  echo "ID de piloto no proporcionado.";
  exit;
}

// Obtener datos del piloto
$stmt = $pdo->prepare("
  SELECT p.id AS piloto_id, p.user_id, p.flota_id, p.licencia, 
         u.nombre, u.apellido, u.telefono, u.email
  FROM pilotos p
  JOIN users u ON p.user_id = u.id
  WHERE p.id = ?
");
$stmt->execute([$id]);
$piloto = $stmt->fetch();

if (!$piloto) {
  echo "Piloto no encontrado.";
  exit;
}

// Obtener flotas
$flotas = $pdo->query("SELECT id, tipo FROM flotas ORDER BY tipo ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre    = $_POST['nombre'];
  $apellido  = $_POST['apellido'];
  $telefono  = $_POST['telefono'];
  $email     = $_POST['email'];
  $licencia  = $_POST['licencia'];
  $flota_id  = $_POST['flota_id'];
  $password  = $_POST['password'];

  try {
    // Actualizar usuario
    if (!empty($password)) {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $pdo->prepare("UPDATE users SET nombre = ?, apellido = ?, telefono = ?, email = ?, password = ? WHERE id = ?");
      $stmt->execute([$nombre, $apellido, $telefono, $email, $hash, $piloto['user_id']]);
    } else {
      $stmt = $pdo->prepare("UPDATE users SET nombre = ?, apellido = ?, telefono = ?, email = ? WHERE id = ?");
      $stmt->execute([$nombre, $apellido, $telefono, $email, $piloto['user_id']]);
    }

    // Actualizar piloto
    $stmt = $pdo->prepare("UPDATE pilotos SET flota_id = ?, licencia = ? WHERE id = ?");
    $stmt->execute([$flota_id, $licencia, $piloto['piloto_id']]);

    header("Location: pilotos.php");
    exit;
  } catch (Exception $e) {
    echo "Error al actualizar piloto: " . $e->getMessage();
  }
}

include 'partials/header.php';
//include 'partials/sidebar.php';
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Editar Piloto</h2>
  <form method="POST" class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Nombre</label>
      <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($piloto['nombre']) ?>" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Apellido</label>
      <input type="text" name="apellido" class="form-control" value="<?= htmlspecialchars($piloto['apellido']) ?>" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Teléfono</label>
      <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($piloto['telefono']) ?>" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Correo electrónico</label>
      <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($piloto['email']) ?>" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Flota asignada</label>
      <select name="flota_id" class="form-select" required>
        <?php foreach ($flotas as $flota): ?>
          <option value="<?= $flota['id'] ?>" <?= $flota['id'] == $piloto['flota_id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($flota['tipo']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">Licencia</label>
      <input type="text" name="licencia" class="form-control" value="<?= htmlspecialchars($piloto['licencia']) ?>" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Nueva contraseña (opcional)</label>
      <input type="password" name="password" class="form-control">
    </div>
    <div class="col-12">
      <button type="submit" class="btn btn-primary">Actualizar</button>
      <a href="pilotos.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>

<?php include 'partials/footer.php'; ?>