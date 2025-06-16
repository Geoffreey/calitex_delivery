<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'piloto') {
  header("Location: ../login.php");
  exit;
}

$stmt = $pdo->prepare("SELECT id FROM pilotos WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$piloto = $stmt->fetch();

if (!$piloto) {
  echo "<div class='p-4 alert alert-warning'>No tienes un perfil de piloto asignado.</div>";
  include 'partials/footer.php';
  exit;
}

$piloto_id = $piloto['id'];

include 'partials/header.php';
include 'partials/sidebar.php';

// Obtener las rutas asignadas al piloto
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['recoleccion_id']) && isset($_POST['accion'])) {
    $recoleccion_id = $_POST['recoleccion_id'];
    $accion = $_POST['accion'];

    if ($accion === 'entregado' && !empty($_POST['firma_base64'])) {
      $firma_data = $_POST['firma_base64'];
      $firma_data = str_replace('data:image/png;base64,', '', $firma_data);
      $firma_data = str_replace(' ', '+', $firma_data);
      $firma_bin = base64_decode($firma_data);

      $firma_nombre = 'firmaent_' . $recoleccion_id . '_' . time() . '.png';
      $firma_path = '../firmas/' . $firma_nombre;
      file_put_contents($firma_path, $firma_bin);

      $stmt = $pdo->prepare("UPDATE recolecciones SET estado_recoleccion = 'entregado', firma_ent = ? WHERE id = ? AND ruta_entrega_id IN ($placeholders)");
      $stmt->execute(array_merge([$firma_path, $recoleccion_id], $ruta_ids));
    } elseif ($accion === 'cancelado' && !empty($_POST['observacion_cancelacion'])) {
      $observacion = trim($_POST['observacion_cancelacion']);
      $stmt = $pdo->prepare("UPDATE recolecciones SET estado_recoleccion = 'cancelado', observacion_cancelacion = ? WHERE id = ? AND ruta_entrega_id IN ($placeholders)");
      $stmt->execute(array_merge([$observacion, $recoleccion_id], $ruta_ids));
    }


    header("Location: ruta_recolecciones_entrega.php");
    exit;
  }
}

// Obtener recolecciones por entregar
$stmt = $pdo->prepare("
  SELECT r.id, r.descripcion, r.created_at, r.ruta_entrega_id,
         r.nombre_destinatario, r.telefono_destinatario,
         d.calle, d.numero, z.numero AS zona, m.nombre AS municipio, dept.nombre AS departamento
  FROM recolecciones r
  JOIN rutas ON r.ruta_entrega_id = rutas.id
  LEFT JOIN direcciones d ON r.direccion_destino_id = d.id
  LEFT JOIN zona z ON d.zona_id = z.id
  LEFT JOIN municipios m ON d.municipio_id = m.id
  LEFT JOIN departamentos dept ON d.departamento_id = dept.id
  WHERE r.ruta_entrega_id IN ($placeholders) AND r.estado_recoleccion = 'recibido' AND r.estado_recoleccion NOT IN ('entregado', 'cancelado')
  ORDER BY r.created_at ASC
");
$stmt->execute($ruta_ids);
$recolecciones = $stmt->fetchAll();
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Recolecciones por Entregar</h2>
  <?php if (empty($recolecciones)): ?>
  <div class="alert alert-info">No tienes recolecciones pendientes por entregar.</div>
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
        <th>Acción</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($recolecciones as $r): ?>
      <tr>
        <td><?= $r['id'] ?></td>
        <td><?= htmlspecialchars($r['nombre_destinatario']) ?></td>
        <td><?= htmlspecialchars($r['telefono_destinatario']) ?></td>
        <td>
          <?= $r['calle'] . ' ' . $r['numero'] . ', Zona ' . $r['zona'] . ', ' . $r['municipio'] . ', ' . $r['departamento'] ?>
        </td>
        <td><?= $r['descripcion'] ?></td>
        <td><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
        <td class="d-flex gap-2">
          <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalFirma" data-recoleccion-id="<?= $r['id'] ?>">Recogido</button>
          <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalCancelar" data-recoleccion-id="<?= $r['id'] ?>">Cancelar</button>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<!-- Modal Firma -->
<div class="modal fade" id="modalFirma" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" id="formFirma">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Firma del destinatario</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <canvas id="signatureCanvas" style="width: 100%; height: 200px; border: 1px solid #ccc;"></canvas>
          <input type="hidden" name="firma_base64" id="firma_base64">
          <input type="hidden" name="recoleccion_id" id="recoleccion_id_firma">
          <input type="hidden" name="accion" value="entregado">
        </div>
        <div class="modal-footer">
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

  const modal = document.getElementById("modalFirma");
  modal.addEventListener("shown.bs.modal", function (event) {
    const button = event.relatedTarget;
    const recoleccionId = button.getAttribute("data-recoleccion-id");
    document.getElementById("recoleccion_id_firma").value = recoleccionId;
    resizeCanvas();
  });

  document.getElementById("formFirma").addEventListener("submit", function (e) {
    if (signaturePad.isEmpty()) {
      alert("Por favor, dibuja la firma antes de enviar.");
      e.preventDefault();
      return;
    }
    const dataUrl = signaturePad.toDataURL();
    document.getElementById("firma_base64").value = dataUrl;
  });

  //scrip para modal cancelar
  const modalCancelar = document.getElementById("modalCancelar");
  modalCancelar.addEventListener("shown.bs.modal", function (event) {
  const button = event.relatedTarget;
  const recoleccionId = button.getAttribute("data-recoleccion-id");
  document.getElementById("recoleccion_id_cancelar").value = recoleccionId;
});

</script>

<?php include 'partials/footer.php'; ?>
