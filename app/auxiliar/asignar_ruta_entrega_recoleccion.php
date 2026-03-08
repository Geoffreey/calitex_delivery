<?php
session_start();
require_once '../config/db.php';

// Guard: solo auxiliar
$rol = isset($_SESSION['rol']) ? strtolower(trim($_SESSION['rol'])) : '';
if (!isset($_SESSION['user_id']) || $rol !== 'auxiliar') {
  header("Location: ../login.php");
  exit;
}

/* =========================
   POST: Asignar ruta entrega
   ========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recoleccion_id'], $_POST['ruta_id'])) {
  $recoleccion_id = trim($_POST['recoleccion_id']);
  $ruta_id        = trim($_POST['ruta_id']);

  if (ctype_digit($recoleccion_id) && ctype_digit($ruta_id)) {
    $recoleccion_id = (int)$recoleccion_id;
    $ruta_id        = (int)$ruta_id;

    try {
      $pdo->beginTransaction();

      // Validar que la recolección exista y esté lista para asignar entrega
      $stmt = $pdo->prepare("
        SELECT id
        FROM recolecciones
        WHERE id = ?
          AND TRIM(LOWER(estado_recoleccion)) = 'recibido'
          AND (ruta_entrega_id IS NULL OR TRIM(LOWER(estado_recoleccion_entrega)) = 'pendiente')
      FOR UPDATE
      ");
      $stmt->execute([$recoleccion_id]);
      if (!$stmt->fetchColumn()) {
        $pdo->rollBack();
        $_SESSION['flash_error'] = 'La recolección no está lista para asignar entrega.';
        header("Location: " . $_SERVER['PHP_SELF']); exit;
      }

      // Obtener piloto asignado a la ruta
      $stmtPiloto = $pdo->prepare("SELECT piloto_id FROM rutas WHERE id = ?");
      $stmtPiloto->execute([$ruta_id]);
      $piloto_id = $stmtPiloto->fetchColumn();

      // Asignar ruta y marcar estado de entrega = asignada (SIN updated_at)
      $stmtU = $pdo->prepare("
        UPDATE recolecciones
        SET ruta_entrega_id = ?,
            estado_recoleccion_entrega = 'asignada'
        WHERE id = ?
      ");
      $stmtU->execute([$ruta_id, $recoleccion_id]);

      // Insertar historial (usar solo columnas existentes)
      $stmtHist = $pdo->prepare("
        INSERT INTO historial_asignaciones_recolecciones
          (ruta_id, piloto_id, tipo_asignacion, tipo_recoleccion, semana_asignada, estado)
        VALUES
          (?, ?, 'principal', 'entrega', ?, 'pendiente')
      ");
      $semana = date('Y-\WW'); // p.ej. 2025-W33
      $stmtHist->execute([$ruta_id, $piloto_id, $semana]);

      $pdo->commit();
      $_SESSION['flash_ok'] = 'Ruta de entrega asignada correctamente.';
    } catch (Exception $e) {
      if ($pdo->inTransaction()) $pdo->rollBack();
      $_SESSION['flash_error'] = 'Error al asignar ruta: ' . $e->getMessage();
    }
  } else {
    $_SESSION['flash_error'] = 'Datos inválidos.';
  }

  // Siempre redirigir ANTES de imprimir HTML
  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}


/* =========================
   GET: Listados para la vista
   ========================= */

// Rutas con piloto asignado
$rutas = $pdo->query("
  SELECT id, nombre 
  FROM rutas 
  WHERE piloto_id IS NOT NULL 
  ORDER BY id DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Recolecciones ya recibidas y con entrega pendiente (sin asignar)
$recolecciones = $pdo->query("
  SELECT r.id, r.created_at,
         u.nombre AS cliente_nombre, u.apellido AS cliente_apellido,
         d.calle, d.numero,
         z.numero AS zona,
         m.nombre AS municipio,
         dept.nombre AS departamento
  FROM recolecciones r
  JOIN clientes c ON r.cliente_id = c.id
  JOIN users u ON c.user_id = u.id
  JOIN direcciones d ON r.direccion_destino_id = d.id
  LEFT JOIN zona z ON d.zona_id = z.id
  LEFT JOIN municipios m ON d.municipio_id = m.id
  LEFT JOIN departamentos dept ON d.departamento_id = dept.id
  WHERE r.estado = 'pendiente'
    AND r.estado_recoleccion = 'recibido'
    AND (r.estado_recoleccion_entrega IS NULL OR r.estado_recoleccion_entrega = 'pendiente')
  ORDER BY r.created_at ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Ahora sí, incluir parciales (ya no habrá header() después de esto)
include 'partials/header.php';
//include 'partials/sidebar.php';
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Asignar Ruta para Entrega de Recolecciones</h2>

  <?php if (!empty($_SESSION['flash_ok'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_ok']) ?></div>
    <?php unset($_SESSION['flash_ok']); ?>
  <?php endif; ?>

  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
    <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>

  <?php if (empty($recolecciones)): ?>
    <div class="alert alert-info">No hay recolecciones pendientes de entrega para asignar ruta.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-bordered table-striped">
        <thead class="table-light">
          <tr>
            <th>Cliente</th>
            <th>Dirección</th>
            <th>Fecha</th>
            <th>Asignar Ruta</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($recolecciones as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['cliente_nombre'] . ' ' . $r['cliente_apellido']) ?></td>
            <td><?= htmlspecialchars("{$r['calle']} #{$r['numero']}, Zona {$r['zona']}, {$r['municipio']}, {$r['departamento']}") ?></td>
            <td><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
            <td>
              <form method="POST" class="d-flex">
                <input type="hidden" name="recoleccion_id" value="<?= (int)$r['id'] ?>">
                <select name="ruta_id" class="form-select form-select-sm me-2" required>
                  <option value="">-- Ruta --</option>
                  <?php foreach ($rutas as $ruta): ?>
                    <option value="<?= (int)$ruta['id'] ?>"><?= htmlspecialchars($ruta['nombre']) ?></option>
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
