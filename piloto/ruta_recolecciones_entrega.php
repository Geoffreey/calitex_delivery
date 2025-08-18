<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'piloto') {
  header("Location: ../login.php");
  exit;
}

$stmt = $pdo->prepare("SELECT id FROM pilotos WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$piloto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$piloto) {
  include 'partials/header.php';
  include 'partials/sidebar.php';
  echo "<div class='p-4 alert alert-warning'>No tienes un perfil de piloto asignado.</div>";
  include 'partials/footer.php';
  exit;
}

$piloto_id = (int)$piloto['id'];

include 'partials/header.php';
include 'partials/sidebar.php';

/* =======================
   Rutas del piloto
   ======================= */
$stmt = $pdo->prepare("SELECT id, nombre FROM rutas WHERE piloto_id = ?");
$stmt->execute([$piloto_id]);
$rutas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($rutas)) {
  echo "<div class='p-4 alert alert-warning'>No tienes rutas asignadas.</div>";
  include 'partials/footer.php';
  exit;
}

$ruta_ids = array_column($rutas, 'id');
$placeholders = implode(',', array_fill(0, count($ruta_ids), '?'));

/* =======================
   POST acciones
   ======================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recoleccion_id'], $_POST['accion'])) {
  $recoleccion_id = (int)$_POST['recoleccion_id'];
  $accion = $_POST['accion'];

  // Entregado (firma)
  if ($accion === 'entregado' && !empty($_POST['firma_base64'])) {

    // Asegurar directorio firmas
    $dirFirmas = __DIR__ . '/../firmas';
    if (!is_dir($dirFirmas)) {
      @mkdir($dirFirmas, 0775, true);
    }
    if (!is_dir($dirFirmas) || !is_writable($dirFirmas)) {
      $_SESSION['flash_error'] = 'No se pudo acceder/crear el directorio de firmas.';
      header("Location: ruta_recolecciones_entrega.php");
      exit;
    }

    // Guardar firma
    $firma_data = $_POST['firma_base64'];
    $firma_data = preg_replace('#^data:image/\w+;base64,#i', '', $firma_data);
    $firma_bin  = base64_decode(str_replace(' ', '+', $firma_data));
    $firma_nombre    = 'firmaent_' . $recoleccion_id . '_' . time() . '.png';
    $firma_path_rel  = '../firmas/' . $firma_nombre;
    $firma_path_abs  = $dirFirmas . '/' . $firma_nombre;
    file_put_contents($firma_path_abs, $firma_bin);

    // Cerrar FASE 2
    $sql = "UPDATE recolecciones
            SET estado_recoleccion_entrega='entregada',
                fecha_entregado=NOW(),
                firma_ent=?
            WHERE id=?
              AND ruta_entrega_id IN ($placeholders)
              AND estado <> 'cancelado'";
    $params = array_merge([$firma_path_rel, $recoleccion_id], $ruta_ids);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

  // Cancelar entrega
  } elseif ($accion === 'cancelado' && !empty($_POST['observacion_cancelacion'])) {
    $observacion = trim($_POST['observacion_cancelacion']);

    $sql = "UPDATE recolecciones
            SET estado_recoleccion_entrega='cancelada',
                observacion_cancelacion=?
            WHERE id=?
              AND ruta_entrega_id IN ($placeholders)
              AND estado <> 'cancelado'";
    $params = array_merge([$observacion, $recoleccion_id], $ruta_ids);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

  // Iniciar entrega (opcional, si quieres marcar en_ruta)
  } elseif ($accion === 'iniciar') {
    $sql = "UPDATE recolecciones
            SET estado_recoleccion_entrega='en_ruta'
            WHERE id=?
              AND ruta_entrega_id IN ($placeholders)
              AND estado_recoleccion='recibido'
              AND estado_recoleccion_entrega IN ('asignada','pendiente')
              AND estado <> 'cancelado'";
    $params = array_merge([$recoleccion_id], $ruta_ids);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
  }

  header("Location: ruta_recolecciones_entrega.php");
  exit;
}

/* =======================
   GET listado: por entregar
   ======================= */
$sql = "
  SELECT r.id, r.descripcion, r.created_at, r.ruta_entrega_id,
         r.nombre_destinatario, r.telefono_destinatario,
         r.estado_recoleccion_entrega,
         d.calle, d.numero,
         z.numero AS zona, m.nombre AS municipio, dept.nombre AS departamento
  FROM recolecciones r
  JOIN rutas ON r.ruta_entrega_id = rutas.id
  LEFT JOIN direcciones d   ON r.direccion_destino_id = d.id
  LEFT JOIN zona z          ON d.zona_id = z.id
  LEFT JOIN municipios m    ON d.municipio_id = m.id
  LEFT JOIN departamentos dept ON d.departamento_id = dept.id
  WHERE r.ruta_entrega_id IN ($placeholders)
    AND r.estado = 'pendiente'
    AND r.estado_recoleccion = 'recibido'
    AND r.estado_recoleccion_entrega IN ('asignada','en_ruta')
  ORDER BY r.created_at ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($ruta_ids);
$recolecciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Recolecciones por Entregar</h2>

  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
    <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>

  <?php if (empty($recolecciones)): ?>
    <div class="alert alert-info">No tienes recolecciones pendientes por entregar.</div>
  <?php else: ?>
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>No. Guía</th>
          <th>Destinatario</th>
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
          <td><?= (int)$r['id'] ?></td>
          <td><?= htmlspecialchars($r['nombre_destinatario']) ?></td>
          <td><?= htmlspecialchars($r['telefono_destinatario']) ?></td>
          <td>
            <?= htmlspecialchars($r['calle'] . ' ' . $r['numero']) ?>,
            <?= 'Zona ' . htmlspecialchars($r['zona']) ?>,
            <?= htmlspecialchars($r['municipio']) ?>,
            <?= htmlspecialchars($r['departamento']) ?>
          </td>
          <td><?= htmlspecialchars($r['descripcion']) ?></td>
          <td><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
          <td class="d-flex gap-2">
            <?php if ($r['estado_recoleccion_entrega'] === 'asignada'): ?>
              <!-- Opcional: marcar en ruta -->
              <form method="POST" class="d-inline">
                <input type="hidden" name="recoleccion_id" value="<?= (int)$r['id'] ?>">
                <input type="hidden" name="accion" value="iniciar">
                <button type="submit" class="btn btn-outline-primary btn-sm">Iniciar entrega</button>
              </form>
            <?php endif; ?>
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalFirma" data-recoleccion-id="<?= (int)$r['id'] ?>">Entregado</button>
            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalCancelar" data-recoleccion-id="<?= (int)$r['id'] ?>">Cancelar</button>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<!-- Modal Firma (entrega) -->
<div class="modal fade" id="modalFirma" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" id="formFirma">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Firma de entrega</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <canvas id="signatureCanvas" style="width: 100%; height: 200px; border: 1px solid #ccc;"></canvas>
          <input type="hidden" name="firma_base64" id="firma_base64">
          <input type="hidden" name="recoleccion_id" id="recoleccion_id_firma">
          <input type="hidden" name="accion" value="entregado">
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
          <h5 class="modal-title">Cancelar Entrega</h5>
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
