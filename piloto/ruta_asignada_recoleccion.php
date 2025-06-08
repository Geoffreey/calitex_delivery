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
$piloto = $stmt->fetch();

if (!$piloto) {
  echo "<div class='p-4 alert alert-warning'>No tienes un perfil de piloto asignado aún.</div>";
  include 'partials/footer.php';
  exit;
}

$piloto_id = $piloto['id'];

include 'partials/header.php';
include 'partials/sidebar.php';

// Obtener todas las rutas asignadas al piloto
$stmt = $pdo->prepare("SELECT id, nombre FROM rutas WHERE piloto_id = ?");
$stmt->execute([$piloto_id]);
$rutas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($rutas)) {
  echo "<div class='p-4 alert alert-warning'>No tienes rutas asignadas aún.</div>";
  include 'partials/footer.php';
  exit;
}

// Extraer los IDs de las rutas
$ruta_ids = array_column($rutas, 'id');
$placeholders = implode(',', array_fill(0, count($ruta_ids), '?'));

// Obtener recolecciones asignadas a esas rutas
$stmt = $pdo->prepare("
  SELECT r.id AS recoleccion_id, r.created_at, r.ruta_recoleccion_id,
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
  WHERE r.ruta_recoleccion_id IN ($placeholders) AND r.estado_recoleccion = 'pendiente'
  ORDER BY r.created_at ASC
");
$stmt->execute($ruta_ids);
$recolecciones = $stmt->fetchAll();

// Confirmar recolección
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['recoleccion_id']) && isset($_POST['accion'])) {
    $recoleccion_id = $_POST['recoleccion_id'];
    $accion = $_POST['accion'];

    if ($accion === 'recibido' && !empty($_POST['firma_base64'])) {
      $firma_data = $_POST['firma_base64'];
      $firma_data = str_replace('data:image/png;base64,', '', $firma_data);
      $firma_data = str_replace(' ', '+', $firma_data);
      $firma_bin = base64_decode($firma_data);

      $firma_nombre = 'firmarec_' . $recoleccion_id . '_' . time() . '.png';
      $firma_path = '../firmas/' . $firma_nombre;
      file_put_contents($firma_path, $firma_bin);

      $stmt = $pdo->prepare("UPDATE recolecciones SET estado_recoleccion = 'recibido', firma_rec = ? WHERE id = ? AND ruta_recoleccion_id IN ($placeholders)");
      $stmt->execute(array_merge([$firma_path, $recoleccion_id], $ruta_ids));
    } elseif ($accion === 'cancelado') {
      $stmt = $pdo->prepare("UPDATE recolecciones SET estado_recoleccion = 'cancelado' WHERE id = ? AND ruta_recoleccion_id IN ($placeholders)");
      $stmt->execute(array_merge([$recoleecion_id], $ruta_ids));
    }

    header("Location: ruta_asignada_recoleccion.php");
    exit;
  }
}
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Recolecciones asignadas</h2>

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
          <th>Fecha</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recolecciones as $r): ?>
          <tr>
            <td><?= $r['recoleccion_id'] ?></td>
            <td><?= $r['cliente_nombre'] . ' ' . $r['cliente_apellido'] ?></td>
            <td><?= htmlspecialchars($r['telefono']) ?></td>
            <td>
              <?= $r['calle'] . ' ' . $r['numero'] . ', Zona ' . $r['zona'] . ', ' . $r['municipio'] . ', ' . $r['departamento'] ?>
            </td>
            <td><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
            <td class="d-flex gap-2">
              <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalFirma" data-recoleccion-id="<?= $r['recoleccion_id'] ?>">Recogido</button>
              <form method="POST">
                <input type="hidden" name="recoleccion_id" value="<?= $r['recoleccion_id'] ?>">
                <input type="hidden" name="accion" value="cancelado">
                <button type="submit" class="btn btn-danger btn-sm">Cancelar</button>
              </form>
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
          <input type="hidden" name="accion" value="recibido">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar Firma</button>
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
</script>

<?php include 'partials/footer.php'; ?>
