<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

// Obtener pilotos
$pilotos = $pdo->query("SELECT p.id, u.nombre, u.apellido FROM pilotos p JOIN users u ON p.user_id = u.id ORDER BY u.nombre")->fetchAll();

// Obtener rutas
$rutas = $pdo->query("SELECT id, nombre FROM rutas ORDER BY nombre")->fetchAll();

// Insertar asignaciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $ruta_id = $_POST['ruta_id'];
  $semana = $_POST['semana'];

  if (isset($_POST['piloto_ids']) && is_array($_POST['piloto_ids'])) {
    foreach ($_POST['piloto_ids'] as $piloto_id) {
      $estado = $_POST['estado'][$piloto_id] ?? 'activo';

      $stmt = $pdo->prepare("INSERT INTO asignaciones_rutas (piloto_id, ruta_id, semana, estado) VALUES (?, ?, ?, ?)");
      $stmt->execute([$piloto_id, $ruta_id, $semana, $estado]);
    }
    header("Location: rutas_asignadas.php");
    exit;
  }
}

include 'partials/header.php';
//include 'partials/sidebar.php';
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Asignar Múltiples Pilotos a una Ruta</h2>
  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Ruta</label>
      <select name="ruta_id" class="form-select" required>
        <option value="">-- Selecciona una Ruta --</option>
        <?php foreach ($rutas as $r): ?>
          <option value="<?= $r['id'] ?>"><?= $r['nombre'] ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Semana (fecha de inicio)</label>
      <input type="date" name="semana" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Selecciona pilotos</label>
      <?php foreach ($pilotos as $p): ?>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="piloto_ids[]" value="<?= $p['id'] ?>" id="piloto<?= $p['id'] ?>">
          <label class="form-check-label" for="piloto<?= $p['id'] ?>">
            <?= $p['nombre'] . ' ' . $p['apellido'] ?>
          </label>
          <select name="estado[<?= $p['id'] ?>]" class="form-select form-select-sm d-inline w-auto ms-2">
            <option value="activo">Activo</option>
            <option value="apoyo">Apoyo</option>
          </select>
        </div>
      <?php endforeach; ?>
    </div>

    <button type="submit" class="btn btn-primary">Asignar</button>
    <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>

<?php include 'partials/footer.php'; ?>
