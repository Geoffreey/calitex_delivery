<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'auxiliar') {
  header("Location: ../login.php");
  exit;
}

include 'partials/header.php';
include 'partials/sidebar.php';

// Obtener rutas disponibles
$rutas = $pdo->query("SELECT id, nombre FROM rutas WHERE piloto_id IS NOT NULL ORDER BY id DESC")->fetchAll();

// Obtener envíos sin ruta
$envios = $pdo->query("
  SELECT e.id, 'envio' AS tipo, e.tamano, e.peso, e.created_at,
         u.nombre AS cliente_nombre, u.apellido AS cliente_apellido
  FROM envios e
  JOIN clientes c ON e.cliente_id = c.id
  JOIN users u ON c.user_id = u.id
  WHERE e.estado_envio = 'pendiente' AND e.ruta_id IS NULL
  ORDER BY e.created_at ASC
")->fetchAll();

// Procesar asignación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $tipo = $_POST['tipo'];
  $id = $_POST['id'];
  $ruta_id = $_POST['ruta_id'];

  if ($tipo === 'recoleccion') {
    $stmt = $pdo->prepare("UPDATE recolecciones SET ruta_id = ? WHERE id = ?");
  } else {
    $stmt = $pdo->prepare("UPDATE envios SET ruta_id = ? WHERE id = ?");
  }

  $stmt->execute([$ruta_id, $id]);
  header("Location: asignar_ruta_envios_recolecciones.php");
  exit;
}
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Asignar Ruta a Recolecciones y Envíos</h2>

  <?php if (empty($solicitudes)): ?>
    <div class="alert alert-info">No hay recolecciones ni envíos pendientes por asignar a ruta.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-bordered table-striped">
        <thead class="table-light">
          <tr>
            <th>Tipo</th>
            <th>Cliente</th>
            <th>Tamaño</th>
            <th>Peso</th>
            <th>Fecha</th>
            <th>Asignar Ruta</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($solicitudes as $s): ?>
            <tr>
              <td><?= ucfirst($s['tipo']) ?></td>
              <td><?= $s['cliente_nombre'] . ' ' . $s['cliente_apellido'] ?></td>
              <td><?= $s['tamano'] ?></td>
              <td><?= $s['peso'] ?> kg</td>
              <td><?= date('d/m/Y H:i', strtotime($s['created_at'])) ?></td>
              <td>
                <form method="POST" class="d-flex">
                  <input type="hidden" name="tipo" value="<?= $s['tipo'] ?>">
                  <input type="hidden" name="id" value="<?= $s['id'] ?>">
                  <select name="ruta_id" class="form-select form-select-sm me-2" required>
                    <option value="">-- Ruta --</option>
                    <?php foreach ($rutas as $r): ?>
                      <option value="<?= $r['id'] ?>"><?= $r['nombre'] ?></option>
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