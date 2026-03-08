<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

include 'partials/header.php';
//include 'partials/sidebar.php';

// Obtener rutas sin piloto
$rutas = $pdo->query("
  SELECT r.id, r.nombre, r.fecha_creacion
  FROM rutas r
  WHERE r.piloto_id IS NULL
  ORDER BY r.id DESC
")->fetchAll();

// Obtener lista de pilotos disponibles
$pilotos = $pdo->query("
  SELECT p.id, CONCAT(u.nombre, ' ', u.apellido) AS nombre
  FROM pilotos p
  JOIN users u ON p.user_id = u.id
  ORDER BY u.nombre
")->fetchAll();

// Asignar piloto a ruta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ruta_id'], $_POST['piloto_id'])) {
  $ruta_id = $_POST['ruta_id'];
  $piloto_id = $_POST['piloto_id'];

  $stmt = $pdo->prepare("UPDATE rutas SET piloto_id = ? WHERE id = ?");
  $stmt->execute([$piloto_id, $ruta_id]);

  header("Location: asignar_piloto_a_ruta.php");
  exit;
}
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Asignar Piloto a Ruta</h2>

  <?php if (empty($rutas)): ?>
    <div class="alert alert-info">No hay rutas pendientes de asignación.</div>
  <?php else: ?>
    <form method="POST" class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Ruta</label>
        <select name="ruta_id" class="form-select" required>
          <option value="">Seleccione una ruta</option>
          <?php foreach ($rutas as $r): ?>
            <option value="<?= $r['id'] ?>">
              <?= $r['nombre'] ?> (creada: <?= date('d/m/Y', strtotime($r['fecha_creacion'])) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Piloto</label>
        <select name="piloto_id" class="form-select" required>
          <option value="">Seleccione un piloto</option>
          <?php foreach ($pilotos as $p): ?>
            <option value="<?= $p['id'] ?>"><?= $p['nombre'] ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-12">
        <button type="submit" class="btn btn-primary">Asignar Piloto</button>
      </div>
    </form>
  <?php endif; ?>
</div>

<?php include 'partials/footer.php'; ?>