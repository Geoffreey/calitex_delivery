<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'auxiliar') {
  header("Location: ../login.php");
  exit;
}

$paquete_id = $_GET['id'] ?? null;

// Obtener rutas disponibles
$rutas = $pdo->query("
  SELECT r.id, r.nombre, CONCAT(u.nombre, ' ', u.apellido) AS piloto
  FROM rutas r
  LEFT JOIN pilotos p ON r.piloto_id = p.id
  LEFT JOIN users u ON p.user_id = u.id
  ORDER BY r.id DESC
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ruta_id'])) {
  $ruta_id = $_POST['ruta_id'];

  $stmt = $pdo->prepare("UPDATE paquetes SET ruta_id = ? WHERE id = ?");
  $stmt->execute([$ruta_id, $paquete_id]);

  header("Location: paquetes_por_confirmar.php");
  exit;
}

include 'partials/header.php';
//include 'partials/sidebar.php';
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Asignar Ruta al Paquete #<?= htmlspecialchars($paquete_id) ?></h2>

  <form method="POST">
    <div class="mb-3">
      <label for="ruta_id" class="form-label">Selecciona una Ruta</label>
      <select name="ruta_id" id="ruta_id" class="form-select" required>
        <option value="">-- Selecciona --</option>
        <?php foreach ($rutas as $r): ?>
          <option value="<?= $r['id'] ?>">
            <?= $r['nombre'] ?> <?= $r['piloto'] ? "(Piloto: " . $r['piloto'] . ")" : '' ?>
          </option>
        <?php endforeach; ?>
      </select>
      <p class="text-danger mt-2">
         * Solo se pueden asignar rutas que ya tienen un piloto asignado.
      </p>
    </div>
    <button type="submit" class="btn btn-primary">Asignar Ruta</button>
    <a href="paquetes_por_confirmar.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>

<?php include 'partials/footer.php'; ?>