<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'auxiliar') {
  header("Location: ../login.php");
  exit;
}

include 'partials/header.php';
//include 'partials/sidebar.php';

// Obtener rutas
$rutas = $pdo->query("SELECT id, nombre FROM rutas ORDER BY id DESC")->fetchAll();

// Obtener pilotos disponibles
$pilotos = $pdo->query("
  SELECT p.id, CONCAT(u.nombre, ' ', u.apellido) AS nombre
  FROM pilotos p
  JOIN users u ON p.user_id = u.id
  ORDER BY u.nombre
")->fetchAll();

// Asignación o edición
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ruta_id'], $_POST['piloto_id'], $_POST['tipo_asignacion'])) {
  $ruta_id = $_POST['ruta_id'];
  $piloto_id = $_POST['piloto_id'];
  $tipo = $_POST['tipo_asignacion'];
  $semana = $_POST['semana_asignada'] ?? date('o-\WW'); // Si no se pasa semana, usar la actual

  // Insertar en historial
  $stmt = $pdo->prepare("
    INSERT INTO historial_asignaciones (ruta_id, piloto_id, tipo_asignacion, semana_asignada)
    VALUES (?, ?, ?, ?)
  ");
  $stmt->execute([$ruta_id, $piloto_id, $tipo, $semana]);

  // Actualizar referencia en tabla rutas
  $stmt = $pdo->prepare("
    UPDATE rutas SET piloto_id = ?, tipo_asignacion = ?, semana_asignada = ?, fecha_asignacion = NOW()
    WHERE id = ?
  ");
  $stmt->execute([$piloto_id, $tipo, $semana, $ruta_id]);

  header("Location: asignar_piloto_a_ruta.php");
  exit;
}
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Asignar o Editar Piloto de Ruta</h2>

  <form method="POST">
    <div class="mb-3">
      <label for="ruta_id" class="form-label">Ruta</label>
      <select name="ruta_id" class="form-select" required>
        <option value="">Selecciona una ruta</option>
        <?php foreach ($rutas as $ruta): ?>
          <option value="<?= $ruta['id'] ?>"><?= htmlspecialchars($ruta['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label for="piloto_id" class="form-label">Piloto</label>
      <select name="piloto_id" class="form-select" required>
        <option value="">Selecciona un piloto</option>
        <?php foreach ($pilotos as $piloto): ?>
          <option value="<?= $piloto['id'] ?>"><?= htmlspecialchars($piloto['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label for="tipo_asignacion" class="form-label">Tipo de Asignación</label>
      <select name="tipo_asignacion" class="form-select" required>
        <option value="principal">Principal</option>
        <option value="apoyo">Apoyo</option>
      </select>
    </div>

    <div class="mb-3">
      <label for="semana_asignada" class="form-label">Semana</label>
      <input type="week" name="semana_asignada" class="form-control">
    </div>

    <button type="submit" class="btn btn-primary">Guardar</button>
  </form>
</div>

<?php include 'partials/footer.php'; ?>