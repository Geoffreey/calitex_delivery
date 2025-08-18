<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'piloto') {
  header("Location: ../login.php");
  exit;
}

// Obtener ID del piloto
$stmt = $pdo->prepare("SELECT id FROM pilotos WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$piloto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$piloto) {
  include 'partials/header.php';
  include 'partials/sidebar.php';
  echo "<div class='p-4 alert alert-warning'>No tienes un perfil de piloto asignado aún.</div>";
  include 'partials/footer.php';
  exit;
}

$piloto_id = (int)$piloto['id'];

/* =======================
   POST: Confirmar acciones
   ======================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recoleccion_id'], $_POST['accion'])) {

  // Obtener todas las rutas asignadas al piloto
  $stmt = $pdo->prepare("SELECT id FROM rutas WHERE piloto_id = ?");
  $stmt->execute([$piloto_id]);
  $rutas = $stmt->fetchAll(PDO::FETCH_ASSOC);

  if (!empty($rutas)) {
    $ruta_ids = [];
foreach ($rutas as $r) {
  $ruta_ids[] = (int)$r['id'];
}
    $placeholders = implode(',', array_fill(0, count($ruta_ids), '?'));
  } else {
    // Sin rutas, no debería poder operar
    header("Location: ruta_asignada_recoleccion.php");
    exit;
  }

  $recoleccion_id = (int)$_POST['recoleccion_id'];
  $accion = $_POST['accion'];

  if ($accion === 'recibido' && !empty($_POST['firma_base64'])) {
    // Asegurar directorio de firmas
    $dirFirmas = realpath(__DIR__ . '/../firmas');
    if ($dirFirmas === false) {
      // si no existe, intentar crearlo
      @mkdir(__DIR__ . '/../firmas', 0775, true);
      $dirFirmas = realpath(__DIR__ . '/../firmas');
      if ($dirFirmas === false) {
        // No se pudo crear
        $_SESSION['flash_error'] = 'No se pudo crear el directorio de firmas.';
        header("Location: ruta_asignada_recoleccion.php");
        exit;
      }
    }

    // Guardar firma
    $firma_data = $_POST['firma_base64'];
    $firma_data = preg_replace('#^data:image/\w+;base64,#i', '', $firma_data);
    $firma_bin  = base64_decode(str_replace(' ', '+', $firma_data));
    $firma_nombre = 'firmarec_' . $recoleccion_id . '_' . time() . '.png';
    $firma_path_rel = '../firmas/' . $firma_nombre;
    $firma_path_abs = __DIR__ . '/../firmas/' . $firma_nombre;
    file_put_contents($firma_path_abs, $firma_bin);

    // Solo cerramos FASE 1 (recogida). NO tocar estado_recoleccion_entrega.
    $sql = "UPDATE recolecciones 
            SET estado_recoleccion = 'recibido',
                fecha_recogido = NOW(),
                firma_rec = ?
            WHERE id = ? 
              AND ruta_recoleccion_id IN ($placeholders)
              AND estado <> 'cancelado'";
    $params = array_merge([$firma_path_rel, $recoleccion_id], $ruta_ids);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Opcional: podrías insertar en historial aquí si lo deseas.

  } elseif ($accion === 'cancelado' && !empty($_POST['observacion_cancelacion'])) {
    $observacion = trim($_POST['observacion_cancelacion']);

    // Cancelar FASE 1
    $sql = "UPDATE recolecciones 
            SET estado_recoleccion = 'cancelado',
                observacion_cancelacion = ?
            WHERE id = ?
              AND ruta_recoleccion_id IN ($placeholders)
              AND estado <> 'cancelado'";
    $params = array_merge([$observacion, $recoleccion_id], $ruta_ids);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // El trigger pondrá estado='cancelado'.
    // Opcional: insertar historial.

  }

  header("Location: ruta_asignada_recoleccion.php");
  exit;
}

/* =======================
   GET: Render de pantalla
   ======================= */

// Obtener todas las rutas asignadas al piloto (para el listado)
$stmt = $pdo->prepare("SELECT id, nombre FROM rutas WHERE piloto_id = ?");
$stmt->execute([$piloto_id]);
$rutas = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'partials/header.php';
include 'partials/sidebar.php';

if (empty($rutas)) {
  echo "<div class='p-4 alert alert-warning'>No tienes rutas asignadas aún.</div>";
  include 'partials/footer.php';
  exit;
}

$ruta_ids = array_column($rutas, 'id');
$placeholders = implode(',', array_fill(0, count($ruta_ids), '?'));

// Recolecciones PENDIENTES DE RECOGER (FASE 1)
// Nota: no incluimos canceladas y no incluimos ya recibidas
$stmt = $pdo->prepare("
  SELECT r.id AS recoleccion_id, r.descripcion, r.created_at, r.ruta_recoleccion_id,
         u.nombre AS cliente_nombre, u.apellido AS cliente_apellido, u.telefono,
         d.calle, d.numero, 
         z.numero AS zona, 
         m.nombre AS municipio, 
         dept.nombre AS departamento
  FROM recolecciones r
  JOIN clientes c ON r.cliente_id = c.id
  JOIN users u ON c.user_id = u.id
  JOIN rutas ON r.ruta_recoleccion_id = rutas.id
  JOIN direcciones d ON r.direccion_origen_id = d.id
  LEFT JOIN zona z ON d.zona_id = z.id
  LEFT JOIN municipios m ON d.municipio_id = m.id
  LEFT JOIN departamentos dept ON d.departamento_id = dept.id
  WHERE r.ruta_recoleccion_id IN ($placeholders)
    AND r.estado = 'pendiente'
    AND r.estado_recoleccion = 'pendiente'
  ORDER BY r.created_at ASC
");
$stmt->execute($ruta_ids);
$recolecciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Recolecciones asignadas (por recoger)</h2>

  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
    <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>

  <?php if (empty($recolecciones)): ?>
    <div class="alert alert-info">No tienes recolecciones pendientes por recoger.</div>
  <?php else: ?>
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>No. Guía</th>
          <th>Cliente</th>
          <th>Teléfono</th>
          <th>Dirección</th>
          <th>Descripción</th>
          <th>Fecha</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recolecciones as $r): ?>
          <tr>
            <td><?= (int)$r['recoleccion_id'] ?></td>
            <td><?= htmlspecialchars($r['cliente_nombre'] . ' ' . $r['cliente_apellido']) ?></td>
            <td><?= htmlspecialchars($r['telefono']) ?></td>
            <td>
              <?= htmlspecialchars($r['calle'] . ' ' . $r['numero']) ?>,
              <?= 'Zona ' . htmlspecialchars($r['zona']) ?>,
              <?= htmlspecialchars($r['municipio']) ?>,
              <?= htmlspecialchars($r['departamento']) ?>
            </td>
            <td><?= htmlspecialchars($r['descripcion']) ?></td>
            <td><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
            <td class="d-flex gap-2">
              <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalFirma" data-recoleccion-id="<?= (int)$r['recoleccion_id'] ?>">Recogido</button>
              <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalCancelar" data-recoleccion-id="<?= (int)$r['recoleccion_id'] ?>">Cancelar</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<!-- Modal Firma (FASE 1) -->
<div class="modal fade" id="modalFirma" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" id="formFirma">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Firma de recogida</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <canvas id="signatureCanvas" style="width: 100%; height: 200px; border: 1px solid #ccc;"></canvas>
          <input type="hidden" name="firma_base64" id="firma_base64">
          <input type="hidden" name="recoleccion_id" id="recoleccion_id_firma">
          <input type="hidden" name="accion" value="recibido">
          <small class="text-muted d-block mt-2">Use el mouse o el dedo para firmar.</small>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" id="btnClearFirma">Limpiar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar Firma</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal Cancelar -->
<div class="modal fade" id="modalCancelar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" id="formCancelar">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Cancelar Recolección</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Motivo de cancelación</label>
            <textarea name="observacion_cancelacion" class="form-control" required></textarea>
          </div>
          <input type="hidden" name="recoleccion_id" id="recoleccion_id_cancelar">
          <input type="hidden" name="accion" value="cancelado">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-danger">Confirmar Cancelación</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.6/dist/signature_pad.umd.min.js"></script>
<script>
  const canvas = document.getElementById("signatureCanvas");
  const signaturePad = new SignaturePad(canvas);

  function resizeCanvas() {
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    canvas.width = canvas.offsetWidth * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext("2d").scale(ratio, ratio);
    signaturePad.clear();
  }

  window.addEventListener("resize", resizeCanvas);

  const modalFirma = document.getElementById("modalFirma");
  modalFirma.addEventListener("shown.bs.modal", function (event) {
    const button = event.relatedTarget;
    const recoleccionId = button.getAttribute("data-recoleccion-id");
    document.getElementById("recoleccion_id_firma").value = recoleccionId;
    resizeCanvas();
  });

  document.getElementById("btnClearFirma").addEventListener("click", function() {
    signaturePad.clear();
  });

  document.getElementById("formFirma").addEventListener("submit", function (e) {
    if (signaturePad.isEmpty()) {
      alert("Por favor, dibuja la firma antes de enviar.");
      e.preventDefault();
      return;
    }
    const dataUrl = signaturePad.toDataURL("image/png");
    document.getElementById("firma_base64").value = dataUrl;
  });

  // Modal Cancelar
  const modalCancelar = document.getElementById("modalCancelar");
  modalCancelar.addEventListener("shown.bs.modal", function (event) {
    const button = event.relatedTarget;
    const recoleccionId = button.getAttribute("data-recoleccion-id");
    document.getElementById("recoleccion_id_cancelar").value = recoleccionId;
  });
</script>

<?php include 'partials/footer.php'; ?>
