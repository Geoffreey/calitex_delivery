<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'auxiliar') {
  header("Location: ../login.php");
  exit;
}

include 'partials/header.php';
include 'partials/sidebar.php';

// Rutas disponibles
$rutas = $pdo->query("
  SELECT id, nombre 
  FROM rutas 
  WHERE piloto_id IS NOT NULL 
  ORDER BY id DESC
")->fetchAll();

// Recolecciones pendientes sin ruta de recolección asignada
$recolecciones = $pdo->query("
  SELECT r.id, r.created_at,
         u.nombre AS cliente_nombre, u.apellido AS cliente_apellido
         d.calle, d.numero,
         z.numero AS zona,
         m.nombre AS municipio,
         dept.nombre AS departament
  FROM recolecciones r
  JOIN clientes c ON r.cliente_id = c.id
  JOIN users u ON c.user_id = u.id
  JOIN direcciones d ON r.direccion_origen_id = d.id
  LEFT JOIN zona z ON d.zona_id = z.id
  LEFT JOIN municipios m ON d.muinicipio_id = m.id
  LEFT JOIN departamentos dept ON d.departamento_id = dept.id
  WHERE TRIM(LOWER(r.estado_recoleccion)) = 'pendiente' AND r.ruta_recoleccion_id IS NULL
  ORDER BY r.created_at ASC
")->fetchAll();

// Procesar asignación de ruta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recoleccion_id'], $_POST['ruta_id'])) {
  $recoleccion_id = $_POST['recoleccion_id'];
  $ruta_id = $_POST['ruta_id'];

  // Obtener piloto asignado a la ruta
  $stmtPiloto = $pdo->prepare("SELECT piloto_id FROM rutas WHERE id = ?");
  $stmtPiloto->execute([$ruta_id]);
  $piloto_id = $stmtPiloto->fetchColumn();

  // Actualizar recolección con la ruta asignada
  $stmt = $pdo->prepare("UPDATE recolecciones SET ruta_recoleccion_id = ? WHERE id = ?");
  $stmt->execute([$ruta_id, $recoleccion_id]);

  // Guardar en historial
  $stmtHistorial = $pdo->prepare("
    INSERT INTO historial_asignaciones_recolecciones (ruta_id, piloto_id, tipo_asignacion, tipo_recoleccion, semana_asignada, estado)
    VALUES (?, ?, 'principal', 'recoleccion', ?, 'pendiente')
  ");
  $semana = date('Y-\WW'); // Semana ISO
  $stmtHistorial->execute([$ruta_id, $piloto_id, $semana]);

  header("Location: asignar_ruta_recolecciones.php");
  exit;
}

?>

<div class="col-lg-10 col-12 p-4">
  <h2>Asignar Ruta para Recolección</h2>

  <?php if (empty($recolecciones)): ?>
    <div class="alert alert-info">No hay recolecciones pendientes para asignar ruta.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-bordered table-striped">
        <thead class="table-light">
          <tr>
            <th>Cliente</th>
            <th>Direccion</th>
            <th>Fecha</th>
            <th>Asignar Ruta</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recolecciones as $r): ?>
            <tr>
              <td><?= $r['cliente_nombre'] . ' ' . $r['cliente_apellido'] ?></td>
              <td><?= "{$r['calle']} #{$r['numero']}, Zona {$r['zona']}, {$r['municipio']}, {$r['departamento']}" ?></td>
              <td><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
              <td>
                <form method="POST" class="d-flex">
                  <input type="hidden" name="recoleccion_id" value="<?= $r['id'] ?>">
                  <select name="ruta_id" class="form-select form-select-sm me-2" required>
                    <option value="">-- Ruta --</option>
                    <?php foreach ($rutas as $ruta): ?>
                      <option value="<?= $ruta['id'] ?>"><?= $ruta['nombre'] ?></option>
                    <?php endforeach; ?>
                  </select>
                  <button type="submit" class="btn btn-sm btn-primary">Asignar</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php include 'partials/footer.php'; ?>
