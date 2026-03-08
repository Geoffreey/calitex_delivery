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
  //include 'partials/sidebar.php';
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

  // ---- 1) Directorio de firmas
  $dirFirmas = __DIR__ . '/../firmas';
  if (!is_dir($dirFirmas)) {
    @mkdir($dirFirmas, 0775, true);
  }
  if (!is_dir($dirFirmas)) {
    $_SESSION['flash_error'] = 'No se pudo crear el directorio de firmas.';
    header("Location: ruta_asignada_recoleccion.php");
    exit;
  }

  // Guardar firma
  $firma_data = $_POST['firma_base64'];
  $firma_data = preg_replace('#^data:image/\w+;base64,#i', '', $firma_data);
  $firma_bin  = base64_decode(str_replace(' ', '+', $firma_data));
  $firma_nombre   = 'firmarec_' . $recoleccion_id . '_' . time() . '.png';
  $firma_path_abs = $dirFirmas . '/' . $firma_nombre;
  $firma_path_rel = '../firmas/' . $firma_nombre; // CONSISTENTE con lo que ya usas
  file_put_contents($firma_path_abs, $firma_bin);

  // ---- 2) FOTO del cliente (si viene)
  $foto_path_rel = null; // por defecto sin foto
  if (!empty($_POST['foto_cliente'])) {
    $img = $_POST['foto_cliente'];
    if (strpos($img, 'base64,') !== false) {
      $img = explode('base64,', $img, 2)[1];
    }
    $img = str_replace(' ', '+', $img);
    $data = base64_decode($img);

    if ($data !== false) {
      $dirFotos = __DIR__ . '/../fotos_pilotos';
      if (!is_dir($dirFotos)) {
        @mkdir($dirFotos, 0775, true);
      }
      // nombre archivo
      $rand = function_exists('random_bytes') ? bin2hex(random_bytes(4)) : substr(md5(uniqid('', true)), 0, 8);
      $foto_nombre   = 'cli_' . (int)$recoleccion_id . '_' . date('Ymd_His') . '_' . $rand . '.jpg';
      $foto_path_abs = $dirFotos . '/' . $foto_nombre;
      $foto_path_rel = '../fotos_pilotos/' . $foto_nombre; // MISMA lógica que firma (ruta relativa similar)

      // guardar archivo
      if (file_put_contents($foto_path_abs, $data) === false) {
        error_log('[FOTO] file_put_contents falló: ' . $foto_path_abs);
        $foto_path_rel = null; // no guardamos ruta si falló
      }
    } else {
      error_log('[FOTO] base64_decode falló');
    }
  }

  // ---- 3) UPDATE en una sola sentencia (firma + foto si existe)
  if ($foto_path_rel) {
    $sql = "UPDATE recolecciones 
            SET estado_recoleccion = 'recibido',
                fecha_recogido = NOW(),
                firma_rec = ?,
                foto_cliente = ?
            WHERE id = ?
              AND ruta_recoleccion_id IN ($placeholders)
              AND estado <> 'cancelado'";
    $params = array_merge([$firma_path_rel, $foto_path_rel, $recoleccion_id], $ruta_ids);
  } else {
    $sql = "UPDATE recolecciones 
            SET estado_recoleccion = 'recibido',
                fecha_recogido = NOW(),
                firma_rec = ?
            WHERE id = ?
              AND ruta_recoleccion_id IN ($placeholders)
              AND estado <> 'cancelado'";
    $params = array_merge([$firma_path_rel, $recoleccion_id], $ruta_ids);
  }

  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);

  header("Location: ruta_asignada_recoleccion.php");
  exit;
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
//include 'partials/sidebar.php';

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
          <input type="hidden" name="foto_cliente" id="foto_cliente">
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
        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#modalFotoCliente" data-recoleccion-id="<?= (int)$r['recoleccion_id'] ?>">Tomar foto del paquete</button>

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

<!--FUNCION DE FIRMA-->
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

<!--TOMAR FOTO-->
<script>
document.addEventListener('DOMContentLoaded', () => {
  const modalEl = document.getElementById('modalFotoCliente');
  if (!modalEl) return;

  // Siempre busca elementos DENTRO del modal (evita null y colisiones)
  const videoEl       = modalEl.querySelector('#camVideo');
  const canvasEl      = document.getElementById('camCanvas');
  const previewWrap   = document.getElementById('previewWrap');
  const previewImg    = document.getElementById('fotoPreview');
  const fallbackWrap  = document.getElementById('fallbackWrap');
  const fallbackInput = document.getElementById('fallbackInput');
  const fotoInput     = document.getElementById('foto_cliente');

  // Botones (dentro del modal)
  const btnTomar    = modalEl.querySelector('#btnTomar');
  const btnRepetir  = modalEl.querySelector('#btnRepetir');
  const btnGuardar  = modalEl.querySelector('#btnGuardarFoto');

  // Si por alguna razón faltan botones, no reventar
  function safe(el) { return !!el; }

  let stream = null;
  function stopStream(){ if(stream){ stream.getTracks().forEach(t=>t.stop()); stream=null; } if (videoEl) videoEl.srcObject = null; }

  function setShotMode(on){
    if (safe(btnTomar))   btnTomar.style.display   = on ? 'none' : '';
    if (safe(btnRepetir)) btnRepetir.style.display = on ? '' : 'none';
    if (safe(btnGuardar)) btnGuardar.style.display = on ? '' : 'none';
    if (previewWrap)      previewWrap.style.display = on ? '' : 'none';
  }

  // Diagnóstico visible bajo el video
  let diag = document.getElementById('camDiag');
  if (!diag && videoEl && videoEl.parentElement && videoEl.parentElement.parentElement) {
    diag = document.createElement('div');
    diag.id = 'camDiag';
    diag.className = 'text-danger small mt-2';
    videoEl.parentElement.parentElement.appendChild(diag);
  }
  function showDiag(txt){ if (diag) diag.textContent = txt || ''; }

  async function pickBestCamera(){
    const md = navigator.mediaDevices;
    if (!md || !md.enumerateDevices) return null;
    const devices = await md.enumerateDevices();
    const videos = devices.filter(d => d.kind === 'videoinput');
    if (!videos.length) return null;
    let backCam = videos.find(v => /back|rear|environment/i.test(v.label));
    if (!backCam) backCam = videos[videos.length - 1];
    return { video: { deviceId: { exact: backCam.deviceId } }, audio: false };
  }

  async function tryStartCamera(){
    showDiag('');
    const tries = [
      { video: { facingMode: { ideal: 'environment' } }, audio: false },
      await pickBestCamera(),
      { video: true, audio: false }
    ].filter(Boolean);

    let lastErr = null;
    for (const c of tries) {
      try { showDiag('Iniciando cámara...'); return await navigator.mediaDevices.getUserMedia(c); }
      catch(e){ lastErr = e; console.warn('[getUserMedia] fallo', c, e); }
    }
    throw lastErr || new Error('No se pudo iniciar la cámara');
  }

  function explainError(err){
    const name = (err && (err.name || err.code)) || '';
    const msg  = (err && err.message) || '';
    switch (name) {
      case 'NotAllowedError':        return 'Permiso de cámara denegado. Permite acceso en el candado del navegador.';
      case 'NotFoundError':          return 'No se encontró cámara. Verifica que exista una cámara disponible.';
      case 'NotReadableError':       return 'La cámara está siendo usada por otra app.';
      case 'OverconstrainedError':   return 'La cámara no cumple con las restricciones solicitadas.';
      case 'SecurityError':          return 'Bloqueado por seguridad. Usa https o localhost.';
      default:                       return 'No se pudo iniciar la cámara: ' + (msg || name);
    }
  }

  // ====== Eventos del modal ======
  modalEl.addEventListener('shown.bs.modal', async (ev) => {
    setShotMode(false);
    if (previewImg) previewImg.src = '';
    if (fotoInput)  fotoInput.value = '';

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      showDiag('Tu navegador no soporta cámara (getUserMedia).');
      if (fallbackWrap) fallbackWrap.style.display = '';
      return;
    }

    try {
      stream = await tryStartCamera();
      if (videoEl) {
        videoEl.srcObject = stream;
        await videoEl.play().catch(()=>{});
      }
      showDiag('');
      if (fallbackWrap) fallbackWrap.style.display = 'none';
    } catch (err) {
      showDiag(explainError(err));
      if (fallbackWrap) fallbackWrap.style.display = '';
    }
  });

  modalEl.addEventListener('hidden.bs.modal', () => {
    stopStream();
    setShotMode(false);
    if (previewImg) previewImg.src = '';
    showDiag('');
    if (fallbackInput) fallbackInput.value = '';
  });

  // ====== Botones (agrega listeners solo si existen) ======
  if (safe(btnTomar)) {
    btnTomar.addEventListener('click', () => {
      if (!stream || !videoEl) {
        showDiag('No hay cámara activa. Cierra y vuelve a abrir el modal o usa el selector de archivo.');
        if (fallbackWrap) fallbackWrap.style.display = '';
        fallbackInput?.click();
        return;
      }
      const w = videoEl.videoWidth || 1280, h = videoEl.videoHeight || 720;
      if (canvasEl) {
        canvasEl.width = w; canvasEl.height = h;
        canvasEl.getContext('2d').drawImage(videoEl, 0, 0, w, h);
        if (previewImg) previewImg.src = canvasEl.toDataURL('image/jpeg', 0.9);
        setShotMode(true);
      }
    });
  }

  if (safe(btnRepetir)) {
    btnRepetir.addEventListener('click', () => { setShotMode(false); if (previewImg) previewImg.src = ''; showDiag(''); });
  }

  if (safe(btnGuardar)) {
    btnGuardar.addEventListener('click', () => {
      if (previewImg && previewImg.src && previewImg.src.startsWith('data:image')) {
        if (fotoInput) fotoInput.value = previewImg.src; // Copia al form de firma
        return; // el data-bs-dismiss cierra el modal
      }
      if (fallbackInput?.files?.[0]) {
        const r = new FileReader();
        r.onload = () => { if (fotoInput) fotoInput.value = r.result; };
        r.readAsDataURL(fallbackInput.files[0]);
      }
    });
  }
});
</script>

<?php include 'partials/footer.php'; ?>
