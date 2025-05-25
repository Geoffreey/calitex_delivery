<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'auxiliar') {
  header("Location: ../login.php");
  exit;
}

include 'partials/header.php';
include 'partials/sidebar.php';

// Procesar actualización de asignación de recolección
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ruta_id'], $_POST['piloto_id'], $_POST['tipo_asignacion'], $_POST['tipo_recoleccion'])) {
  $ruta_id = $_POST['ruta_id'];
  $piloto_id = $_POST['piloto_id'];
  $tipo_asignacion = $_POST['tipo_asignacion'];
  $tipo_recoleccion = $_POST['tipo_recoleccion'];
  $semana = date('Y-\WW');

  $stmtHistorial = $pdo->prepare("INSERT INTO historial_asignaciones_recolecciones (ruta_id, piloto_id, tipo_asignacion, tipo_recoleccion, semana_asignada) VALUES (?, ?, ?, ?, ?)");
  $stmtHistorial->execute([$ruta_id, $piloto_id, $tipo_asignacion, $tipo_recoleccion, $semana]);
  // Determinar si todas las recolecciones de esta ruta están completadas
if ($tipo_recoleccion === 'recoleccion') {
  $stmtEstado = $pdo->prepare("SELECT COUNT(*) FROM recolecciones WHERE ruta_recoleccion_id = ? AND estado_recoleccion NOT IN ('recibido', 'cancelado')");
} else {
  $stmtEstado = $pdo->prepare("SELECT COUNT(*) FROM recolecciones WHERE ruta_entrega_id = ? AND estado_recoleccion NOT IN ('recibido', 'cancelado')");
}
$stmtEstado->execute([$ruta_id]);
$pendientes = $stmtEstado->fetchColumn();

// Actualizar estado en historial_asignaciones_recolecciones
$estado_final = ($pendientes == 0) ? 'completado' : 'pendiente';
$pdo->prepare("UPDATE historial_asignaciones_recolecciones SET estado = ? WHERE ruta_id = ? AND tipo_recoleccion = ? AND semana_asignada = ?")
    ->execute([$estado_final, $ruta_id, $tipo_recoleccion, $semana]);


  // Actualizar ruta asignada a las recolecciones
  if ($tipo_recoleccion === 'recoleccion') {
    $stmt = $pdo->prepare("UPDATE recolecciones SET ruta_recoleccion_id = ? WHERE ruta_recoleccion_id = ?");
  } else {
    $stmt = $pdo->prepare("UPDATE recolecciones SET ruta_entrega_id = ? WHERE ruta_entrega_id = ?");
  }
  $stmt->execute([$ruta_id, $ruta_id]);

  header("Location: historial_asignaciones_recoleccion.php");
  exit;
}

// Obtener rutas activas utilizadas en recolecciones
$stmt = $pdo->query("SELECT id, nombre, tipo_asignacion, semana_asignada FROM rutas WHERE id IN (SELECT DISTINCT ruta_recoleccion_id FROM recolecciones WHERE ruta_recoleccion_id IS NOT NULL UNION SELECT DISTINCT ruta_entrega_id FROM recolecciones WHERE ruta_entrega_id IS NOT NULL)");
$rutas = $stmt->fetchAll();
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Asignaciones de Recolecciones</h2>

  <?php if (empty($rutas)): ?>
    <div class="alert alert-info">No hay rutas de recolección asignadas aún.</div>
  <?php else: ?>
    <div class="accordion" id="accordionRecolecciones">
      <?php foreach ($rutas as $index => $ruta): ?>
        <div class="accordion-item">
          <h2 class="accordion-header" id="heading<?= $index ?>">
            <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>" aria-expanded="<?= $index == 0 ? 'true' : 'false' ?>" aria-controls="collapse<?= $index ?>">
              <?= htmlspecialchars($ruta['nombre']) ?> - <?= ucfirst($ruta['tipo_asignacion']) ?> - Semana: <?= $ruta['semana_asignada'] ?>
            </button>
          </h2>
          <div id="collapse<?= $index ?>" class="accordion-collapse collapse <?= $index == 0 ? 'show' : '' ?>" aria-labelledby="heading<?= $index ?>" data-bs-parent="#accordionRecolecciones">
            <div class="accordion-body">
              <form method="POST">
                <input type="hidden" name="ruta_id" value="<?= $ruta['id'] ?>">

                <div class="mb-2">
                  <label class="form-label">Piloto</label>
                  <select name="piloto_id" class="form-select" required>
                    <option value="">-- Selecciona --</option>
                    <?php
                    $pilotos = $pdo->query("SELECT p.id, u.nombre, u.apellido FROM pilotos p JOIN users u ON p.user_id = u.id")->fetchAll();
                    foreach ($pilotos as $p):
                      echo "<option value='{$p['id']}'>{$p['nombre']} {$p['apellido']}</option>";
                    endforeach;
                    ?>
                  </select>
                </div>

                <div class="mb-2">
                  <label class="form-label">Tipo Asignación</label>
                  <select name="tipo_asignacion" class="form-select" required>
                    <option value="principal">Principal</option>
                    <option value="apoyo">Apoyo</option>
                  </select>
                </div>

                <div class="mb-2">
                  <label class="form-label">Tipo Recolección</label>
                  <select name="tipo_recoleccion" class="form-select" required>
                    <option value="recoleccion">Recolección</option>
                    <option value="entrega">Entrega</option>
                  </select>
                </div>

                <button type="submit" class="btn btn-primary btn-sm">Actualizar Asignación</button>
              </form>

              <hr>
              <h6>Historial</h6>
              <?php
              $stmtHist = $pdo->prepare("SELECT h.*, u.nombre, u.apellido FROM historial_asignaciones_recolecciones h JOIN pilotos p ON h.piloto_id = p.id JOIN users u ON p.user_id = u.id WHERE h.ruta_id = ? ORDER BY h.fecha_asignacion DESC");
              $stmtHist->execute([$ruta['id']]);
              $historial = $stmtHist->fetchAll();
              ?>
              <?php if (empty($historial)): ?>
                <p class="text-muted">No hay historial para esta ruta.</p>
              <?php else: ?>
                <ul class="list-group">
                  <?php foreach ($historial as $h): ?>
                    <li class="list-group-item small">
                      <?= htmlspecialchars($h['nombre'] . ' ' . $h['apellido']) ?> - <?= ucfirst($h['tipo_asignacion']) ?> | Tipo: <?= ucfirst($h['tipo_recoleccion']) ?> | Estado: <strong><?= ucfirst($h['estado']) ?></strong> | Semana: <?= $h['semana_asignada'] ?> | <?= date('d/m/Y H:i', strtotime($h['fecha_asignacion'])) ?>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>

            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include 'partials/footer.php'; ?>