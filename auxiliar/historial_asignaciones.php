<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'auxiliar') {
  header("Location: ../login.php");
  exit;
}

// 👉 Procesar actualización de asignación si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ruta_id'], $_POST['piloto_id'], $_POST['tipo_asignacion'])) {
  $ruta_id = $_POST['ruta_id'];
  $piloto_id = $_POST['piloto_id'];
  $tipo_asignacion = $_POST['tipo_asignacion'];

  // Obtener semana actual (opcional, podrías agregar un input para seleccionarla si lo deseas)
  $semana = date('Y-\WW'); // Semana ISO tipo "2025-W21"

  // ✅ Guardar historial
  $stmtHistorial = $pdo->prepare("
    INSERT INTO historial_asignaciones (ruta_id, piloto_id, tipo_asignacion, semana_asignada)
    VALUES (?, ?, ?, ?)
  ");
  $stmtHistorial->execute([$ruta_id, $piloto_id, $tipo_asignacion, $semana]);

  // ✅ Actualizar la asignación actual de la ruta
  $stmtUpdate = $pdo->prepare("
    UPDATE rutas 
    SET piloto_id = ?, tipo_asignacion = ?, semana_asignada = ?, fecha_asignacion = NOW()
    WHERE id = ?
  ");
  $stmtUpdate->execute([$piloto_id, $tipo_asignacion, $semana, $ruta_id]);

  // Redirigir a sí misma para evitar reenviar el formulario
  header("Location: historial_asignaciones.php");
  exit;
}

include 'partials/header.php';
//include 'partials/sidebar.php';

// Obtener rutas con asignación
$stmt = $pdo->query("SELECT * FROM rutas WHERE piloto_id IS NOT NULL ORDER BY fecha_asignacion DESC");
$rutas = $stmt->fetchAll();
?>


<div class="col-lg-10 col-12 p-4">
  <h2>Rutas Asignadas a Pilotos</h2>

  <?php if (empty($rutas)): ?>
    <div class="alert alert-info">No hay rutas asignadas aún.</div>
  <?php else: ?>
    <div class="accordion" id="accordionRutas">
      <?php foreach ($rutas as $index => $ruta): ?>
        <div class="accordion-item">
          <h2 class="accordion-header" id="heading<?= $index ?>">
            <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>" aria-expanded="<?= $index == 0 ? 'true' : 'false' ?>" aria-controls="collapse<?= $index ?>">
              <?= htmlspecialchars($ruta['nombre']) ?> - <?= ucfirst($ruta['tipo_asignacion']) ?> - Semana: <?= $ruta['semana_asignada'] ?>
            </button>
          </h2>
          <div id="collapse<?= $index ?>" class="accordion-collapse collapse <?= $index == 0 ? 'show' : '' ?>" aria-labelledby="heading<?= $index ?>" data-bs-parent="#accordionRutas">
            <div class="accordion-body">
              <form method="POST" action="historial_asignaciones.php">
                <input type="hidden" name="ruta_id" value="<?= $ruta['id'] ?>">

                <div class="mb-2">
                  <label class="form-label">Piloto</label>
                  <select name="piloto_id" class="form-select" required>
                    <option value="">-- Selecciona --</option>
                    <?php
                      $pilotos = $pdo->query("SELECT p.id, u.nombre, u.apellido FROM pilotos p JOIN users u ON p.user_id = u.id")->fetchAll();
                      foreach ($pilotos as $p):
                        $selected = $p['id'] == $ruta['piloto_id'] ? 'selected' : '';
                        echo "<option value='{$p['id']}' $selected>{$p['nombre']} {$p['apellido']}</option>";
                      endforeach;
                    ?>
                  </select>
                </div>

                <div class="mb-2">
                  <label class="form-label">Tipo Asignación</label>
                  <select name="tipo_asignacion" class="form-select" required>
                    <option value="principal" <?= $ruta['tipo_asignacion'] === 'principal' ? 'selected' : '' ?>>Principal</option>
                    <option value="apoyo" <?= $ruta['tipo_asignacion'] === 'apoyo' ? 'selected' : '' ?>>Apoyo</option>
                  </select>
                </div>

                <button type="submit" class="btn btn-primary btn-sm">Actualizar Asignación</button>
              </form>

              <hr>
              <h6>Historial de Asignaciones</h6>
              <?php
                $stmtHist = $pdo->prepare("SELECT h.*, u.nombre, u.apellido FROM historial_asignaciones h JOIN pilotos p ON h.piloto_id = p.id JOIN users u ON p.user_id = u.id WHERE h.ruta_id = ? ORDER BY h.fecha_asignacion DESC");
                $stmtHist->execute([$ruta['id']]);
                $historial = $stmtHist->fetchAll();
              ?>
              <?php if (empty($historial)): ?>
                <p class="text-muted">No hay historial para esta ruta.</p>
              <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($historial as $h): ?>
                    <li class="list-group-item small">
                        <?= htmlspecialchars($h['nombre'] . ' ' . $h['apellido']) ?>
                        - <?= ucfirst($h['tipo_asignacion']) ?>
                        | Semana: <?= $h['semana_asignada'] ?>
                        | Estado: <strong><?= ucfirst($h['estado']) ?></strong>
                        | <?= date('d/m/Y H:i', strtotime($h['fecha_asignacion'])) ?>
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
