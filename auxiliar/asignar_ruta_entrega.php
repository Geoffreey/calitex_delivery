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

// Obtener recolecciones ya recibidas y sin ruta de entrega asignada
$recolecciones = $pdo->query("
  SELECT r.id, r.tamano, r.peso, r.descripcion, r.created_at,
         u.nombre AS cliente_nombre, u.apellido AS cliente_apellido
  FROM recolecciones r
  JOIN clientes c ON r.cliente_id = c.id
  JOIN users u ON c.user_id = u.id
  WHERE TRIM(LOWER(r.estado_recoleccion)) = 'recibido' AND r.ruta_entrega_id IS NULL
  ORDER BY r.created_at ASC
")->fetchAll();

// Procesar asignación de ruta de entrega
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recoleccion_id'], $_POST['ruta_id'])) {
  $recoleccion_id = $_POST['recoleccion_id'];
  $ruta_id = $_POST['ruta_id'];

  $stmt = $pdo->prepare("UPDATE recolecciones SET ruta_entrega_id = ? WHERE id = ?");
  $stmt->execute([$ruta_id, $recoleccion_id]);

  header("Location: asignar_ruta_entrega.php");
  exit;
}
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Asignar Ruta para Entrega</h2>

  <?php if (empty($recolecciones)): ?>
    <div class="alert alert-info">No hay recolecciones pendientes por entregar.</div>
  <?php else: ?>
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>Cliente</th>
          <th>Tamaño</th>
          <th>Peso</th>
          <th>Descripción</th>
          <th>Fecha</th>
          <th>Asignar Ruta</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recolecciones as $r): ?>
          <tr>
            <td><?= $r['cliente_nombre'] . ' ' . $r['cliente_apellido'] ?></td>
            <td><?= $r['tamano'] ?></td>
            <td><?= $r['peso'] ?> kg</td>
            <td><?= $r['descripcion'] ?></td>
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
  <?php endif; ?>
</div>

<?php include 'partials/footer.php'; ?>
