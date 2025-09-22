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

// $_POST['foto_cliente'] viene en base64 dataURL
if (!empty($_POST['foto_cliente'])) {
    $img = $_POST['foto_cliente'];

    // Extraer base64 limpio
    if (strpos($img, 'base64,') !== false) {
        $img = explode('base64,', $img, 2)[1];
    }
    // A veces vienen espacios reemplazados por '+'
    $img = str_replace(' ', '+', $img);

    $data = base64_decode($img);
    if ($data === false) {
        error_log('[FOTO] base64_decode falló');
        // Opcional: feedback al usuario
        // echo json_encode(['error' => 'Imagen inválida']); exit;
    } else {
        // Directorio físico donde guardaremos (fuera de /piloto)
        // __DIR__ = .../piloto
        $saveDir = __DIR__ . '/../fotos_pilotos';

        if (!is_dir($saveDir)) {
            if (!mkdir($saveDir, 0755, true)) {
                error_log('[FOTO] No se pudo crear directorio: ' . $saveDir);
            }
        }

        if (!is_writable($saveDir)) {
            error_log('[FOTO] Directorio no escribible: ' . $saveDir);
        }

        // Nombre de archivo
        $fileBase = 'cli_' . (int)$recoleccion_id . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.jpg';
        $fsPath   = $saveDir . '/' . $fileBase;         // Ruta en disco (absoluta)
        $dbPath   = 'fotos_pilotos/' . $fileBase;       // Ruta para BD (relativa al proyecto)

        // Guardar archivo
        $bytes = file_put_contents($fsPath, $data);
        if ($bytes === false) {
            error_log('[FOTO] file_put_contents falló: ' . $fsPath);
        } else {
            // Actualiza tu tabla
            $stmt = $pdo->prepare("UPDATE recolecciones SET foto_cliente = ? WHERE id = ?");
            $stmt->execute([$dbPath, $recoleccion_id]);
            // Opcional: verifica filas afectadas
            if ($stmt->rowCount() === 0) {
                error_log('[FOTO] UPDATE sin filas afectadas. ID: ' . (int)$recoleccion_id);
            }
        }
    }
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
              <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#modalFotoCliente">Tomar foto del paquete</button>
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

<!--Modal Foto paquete-->
<div class="modal fade" id="modalFotoCliente" tabindex="-1" aria-labelledby="modalFotoClienteLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="modalFotoClienteLabel" class="modal-title">Foto del cliente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <!-- Contenedor responsive para evitar distorsión -->
        <div class="position-relative w-100" style="max-width: 640px; margin: 0 auto;">
          <!-- Mantener relación 16:9 -->
          <div style="position: relative; width: 100%; padding-top: 56.25%;">
            <video id="camVideo" autoplay playsinline
                   style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;border-radius:12px;border:1px solid #ddd;"></video>
          </div>
        </div>

        <!-- Vista previa de la foto tomada -->
        <div id="previewWrap" class="text-center mt-3" style="display:none;">
          <img id="fotoPreview" alt="Vista previa" style="max-width: 100%; border-radius:12px; border:1px solid #ddd;">
        </div>

        <!-- Canvas oculto para tomar snapshot -->
        <canvas id="camCanvas" width="1280" height="720" style="display:none;"></canvas>

        <!-- Fallback si no hay getUserMedia (iOS viejo, navegador sin permiso) -->
        <div id="fallbackWrap" class="mt-3" style="display:none;">
          <label class="form-label">Si la cámara no se activa, sube una foto:</label>
          <input type="file" accept="image/*" capture="environment" class="form-control" id="fallbackInput">
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" id="btnTomar" class="btn btn-primary">Tomar foto</button>
        <button type="button" id="btnRepetir" class="btn btn-outline-secondary" style="display:none;">Repetir</button>
        <button type="button" id="btnGuardarFoto" class="btn btn-success" style="display:none;" data-bs-dismiss="modal">Usar esta foto</button>
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
  let stream = null;

  const modalEl = document.getElementById('modalFotoCliente');
  if (!modalEl) {
    console.warn('No existe #modalFotoCliente en el DOM.');
    return;
  }

  const fotoInput     = document.getElementById('foto_cliente');
  const canvasEl      = document.getElementById('camCanvas');
  const previewWrap   = document.getElementById('previewWrap');
  const previewImg    = document.getElementById('fotoPreview');
  const btnTomar      = document.getElementById('btnTomar');
  const btnRepetir    = document.getElementById('btnRepetir');
  const btnGuardar    = document.getElementById('btnGuardarFoto');
  const fallbackWrap  = document.getElementById('fallbackWrap');
  const fallbackInput = document.getElementById('fallbackInput');

  function stopStream() {
    if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
  }
  function setShotMode(hasShot) {
    btnTomar.style.display    = hasShot ? 'none' : '';
    btnRepetir.style.display  = hasShot ? '' : 'none';
    btnGuardar.style.display  = hasShot ? '' : 'none';
    previewWrap.style.display = hasShot ? '' : 'none';
  }

  setShotMode(false);

  // Abrir modal ⇒ iniciar cámara
  modalEl.addEventListener('shown.bs.modal', async () => {
    setShotMode(false);
    previewImg.src = '';
    if (fotoInput) fotoInput.value = '';

    // Toma el <video> dentro del modal (evita null y duplicados)
    const videoEl = modalEl.querySelector('#camVideo');
    if (!videoEl) {
      console.error('No se encontró #camVideo dentro del modal');
      return;
    }

    try {
      stream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: { ideal: 'environment' } },
        audio: false
      });
      videoEl.srcObject = stream;
      videoEl.play().catch(()=>{});
      if (fallbackWrap) fallbackWrap.style.display = 'none';
    } catch (err) {
      console.warn('No se pudo acceder a la cámara:', err);
      if (fallbackWrap) fallbackWrap.style.display = '';
    }
  });

  // Cerrar modal ⇒ detener cámara y limpiar
  modalEl.addEventListener('hidden.bs.modal', () => {
    stopStream();
    setShotMode(false);
    previewImg.src = '';
    if (fallbackInput) fallbackInput.value = '';
  });

  // Tomar foto
  btnTomar.addEventListener('click', () => {
    const videoEl = modalEl.querySelector('#camVideo');
    if (stream && videoEl) {
      const w = videoEl.videoWidth || 1280;
      const h = videoEl.videoHeight || 720;
      canvasEl.width = w; canvasEl.height = h;
      const ctx = canvasEl.getContext('2d');
      ctx.drawImage(videoEl, 0, 0, w, h);
      previewImg.src = canvasEl.toDataURL('image/jpeg', 0.9);
      setShotMode(true);
    } else {
      if (fallbackWrap) fallbackWrap.style.display = '';
      fallbackInput?.click();
    }
  });

  // Repetir
  btnRepetir.addEventListener('click', () => {
    setShotMode(false);
    previewImg.src = '';
  });

  // Guardar
  btnGuardar.addEventListener('click', () => {
    if (previewImg.src && previewImg.src.startsWith('data:image')) {
      if (fotoInput) fotoInput.value = previewImg.src; // base64
      return; // el modal se cierra por data-bs-dismiss
    }
    if (fallbackInput?.files?.[0]) {
      const reader = new FileReader();
      reader.onload = () => {
        if (fotoInput) fotoInput.value = reader.result;
        bootstrap.Modal.getInstance(modalEl)?.hide();
      };
      reader.readAsDataURL(fallbackInput.files[0]);
    }
  });

  // Fallback: archivo
  if (fallbackInput) {
    fallbackInput.addEventListener('change', () => {
      const file = fallbackInput.files?.[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = () => {
        previewImg.src = reader.result;
        setShotMode(true);
      };
      reader.readAsDataURL(file);
    });
  }
});
</script>

<?php include 'partials/footer.php'; ?>
