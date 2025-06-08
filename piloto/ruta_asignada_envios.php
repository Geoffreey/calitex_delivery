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
  echo "<div class='p-4 alert alert-warning'>No tienes un perfil de piloto asignado aún.</div>";
  include 'partials/footer.php';
  exit;
}

$piloto_id = $piloto['id'];

include 'partials/header.php';
include 'partials/sidebar.php';

$stmt = $pdo->prepare("SELECT id, nombre FROM rutas WHERE piloto_id = ?");
$stmt->execute([$piloto_id]);
$rutas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($rutas)) {
  echo "<div class='p-4 alert alert-warning'>No tienes rutas asignadas aún.</div>";
  include 'partials/footer.php';
  exit;
}

$ruta_ids = array_column($rutas, 'id');
$placeholders = implode(',', array_fill(0, count($ruta_ids), '?'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['envio_id']) && isset($_POST['accion'])) {
    $envio_id = $_POST['envio_id'];
    $accion = $_POST['accion'];

    if ($accion === 'entregado' && !empty($_POST['firma_base64'])) {
      $firma_data = $_POST['firma_base64'];
      $firma_data = str_replace('data:image/png;base64,', '', $firma_data);
      $firma_data = str_replace(' ', '+', $firma_data);
      $firma_bin = base64_decode($firma_data);

      $firma_nombre = 'firma_' . $envio_id . '_' . time() . '.png';
      $firma_path = '../firmas/' . $firma_nombre;
      file_put_contents($firma_path, $firma_bin);

      $stmt = $pdo->prepare("UPDATE envios SET estado_envio = 'recibido', firma = ? WHERE id = ? AND ruta_id IN ($placeholders)");
      $stmt->execute(array_merge([$firma_path, $envio_id], $ruta_ids));
    } elseif ($accion === 'cancelado') {
      $stmt = $pdo->prepare("UPDATE envios SET estado_envio = 'cancelado' WHERE id = ? AND ruta_id IN ($placeholders)");
      $stmt->execute(array_merge([$envio_id], $ruta_ids));
    }

    header("Location: ruta_asignada_envios.php");
    exit;
  }
}

$stmt = $pdo->prepare("
  SELECT 
    e.id AS envio_id,
    e.created_at,
    e.nombre_destinatario,
    e.telefono_destinatario,
    d.calle, d.numero AS numero_direccion,
    z.numero AS zona,
    m.nombre AS municipio,
    dp.nombre AS departamento
  FROM envios e
  JOIN direcciones d ON e.direccion_destino_id = d.id
  JOIN zona z ON d.zona_id = z.id
  JOIN municipios m ON d.municipio_id = m.id
  JOIN departamentos dp ON d.departamento_id = dp.id
  WHERE e.ruta_id IN ($placeholders) AND e.estado_envio != 'recibido'
  ORDER BY e.created_at DESC
");
$stmt->execute($ruta_ids);
$envios = $stmt->fetchAll();
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Envíos asignados</h2>

  <?php if (empty($envios)): ?>
    <div class="alert alert-info">No tienes envíos pendientes por entregar.</div>
  <?php else: ?>
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>No. Guía</th>
          <th>Destinatario</th>
          <th>Teléfono</th>
          <th>Dirección</th>
          <th>Fecha</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($envios as $r): ?>
          <tr>
            <td><?= $r['envio_id'] ?></td>
            <td><?= htmlspecialchars($r['nombre_destinatario']) ?></td>
            <td><?= htmlspecialchars($r['telefono_destinatario']) ?></td>
            <td><?= htmlspecialchars($r['calle']) . ' #' . $r['numero_direccion'] . ', Zona ' . $r['zona'] . ', ' . $r['municipio'] . ', ' . $r['departamento'] ?></td>
            <td><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
            <td class="d-flex gap-2">
              <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalFirma" data-envio-id="<?= $r['envio_id'] ?>">Entregado</button>
              <form method="POST">
                <input type="hidden" name="envio_id" value="<?= $r['envio_id'] ?>">
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
          <input type="hidden" name="envio_id" id="envio_id_firma">
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
    const envioId = button.getAttribute("data-envio-id");
    document.getElementById("envio_id_firma").value = envioId;
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
