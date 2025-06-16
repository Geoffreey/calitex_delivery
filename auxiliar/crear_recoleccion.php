<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'auxiliar') {
  header("Location: ../login.php");
  exit;
}

// Obtener ID del cliente
$stmt = $pdo->prepare("SELECT c.id, u.nombre, u.telefono 
                       FROM clientes c 
                       JOIN users u ON c.user_id = u.id 
                       WHERE c.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$cliente_data = $stmt->fetch(PDO::FETCH_ASSOC);

$cliente_id = $cliente_data['id'];
$cliente_nombre = $cliente_data['nombre'];
$cliente_telefono = $cliente_data['telefono'];

// Obtener direcciones
$stmt = $pdo->prepare("SELECT d.*, z.numero AS zona, m.nombre AS municipio, dp.nombre AS departamento 
                      FROM direcciones d 
                      JOIN zona z ON d.zona_id = z.id 
                      JOIN municipios m ON d.municipio_id = m.id 
                      JOIN departamentos dp ON d.departamento_id = dp.id 
                      WHERE d.cliente_id = ?");
$stmt->execute([$cliente_id]);
$direcciones = $stmt->fetchAll();

// Mapear direcciones por ID
$direccion_map = [];
foreach ($direcciones as $d) {
  $direccion_map[$d['id']] = "{$d['calle']} #{$d['numero']}, Zona {$d['zona']}, {$d['municipio']}, {$d['departamento']}";
}

// Obtener paquetes disponibles
$paquetes = $pdo->query("SELECT id, nombre, tamano, peso, tarifa FROM paquetes ORDER BY nombre")->fetchAll();

$guia_script = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $direccion_origen_id = $_POST['direccion_origen_id'];
  $direccion_destino_id = $_POST['direccion_destino_id'];
  $nombre_destinatario  = $_POST['nombre_destinatario'];
  $telefono_destinatario = $_POST['telefono_destinatario'];
  $observaciones = $_POST['observaciones'] ?? null;
  $paquete_ids = $_POST['paquete_ids'] ?? [];
  $pago_envio = $_POST['pago_envio'] ?? 'cliente';
  $monto_cobros = $_POST['monto_cobros'] ?? [];


  // Validar que al menos un paquete tenga cantidad > 0
  if (empty($paquete_ids)) {
    echo "<script>alert('Debes seleccionar al menos un paquete.'); window.location.href=window.location.href;</script>";
    exit;
  }

  // Validar datos obligatorios
  if (empty($direccion_origen_id) || empty($direccion_destino_id) || empty($nombre_destinatario) || empty($telefono_destinatario)) {
    echo "<script>alert('Todos los campos obligatorios deben ser completados.'); window.location.href=window.location.href;</script>";
    exit;
  }

  // Validar que al menos un paquete tenga cantidad > 0
  $paquetes_validos = array_filter($paquete_ids, function($c) {
    return (int)$c > 0;
  });
  if (empty($paquetes_validos)) {
    echo "<script>alert('Debes seleccionar al menos un paquete con cantidad mayor a 0.'); window.location.href=window.location.href;</script>";
    exit;
  }

  $direccion_origen_texto = $direccion_map[$direccion_origen_id] ?? '';
  $direccion_destino_texto = $direccion_map[$direccion_destino_id] ?? '';

  try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO recolecciones (cliente_id, direccion_origen_id, direccion_destino_id, nombre_destinatario, telefono_destinatario, descripcion, pago_envio) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$cliente_id, $direccion_origen_id, $direccion_destino_id, $nombre_destinatario, $telefono_destinatario, $observaciones, $pago_envio]);



    $recoleccion_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO recolecciones_paquetes (recoleccion_id, paquete_id, monto_cobro) VALUES (?, ?, ?)");
$total_cobro = 0;

// Recolección de cobros definidos por el cliente
foreach ($paquete_ids as $paquete_id => $cantidad) {
  $monto = isset($monto_cobros[$paquete_id]) ? floatval($monto_cobros[$paquete_id]) : 0.00;
  for ($i = 0; $i < (int)$cantidad; $i++) {
    $stmt->execute([$recoleccion_id, $paquete_id, $monto]);
  }
  $total_cobro += ((int)$cantidad) * $monto;
}

// Sumar el valor de las tarifas solo si el pago es contra entrega
$tarifa_envio_total = 0;
foreach ($paquetes as $p) {
  $id_paquete = $p['id'];
  $cantidad = isset($paquete_ids[$id_paquete]) ? (int)$paquete_ids[$id_paquete] : 0;
  $tarifa_envio_total += $cantidad * floatval($p['tarifa']);
}

if ($pago_envio === 'destinatario') {
  $total_cobro += $tarifa_envio_total;
}


$pdo->commit(); // ✅ Aquí ya está calculado


$guia_script = '<script>
  document.addEventListener("DOMContentLoaded", function() {
    const modal = new bootstrap.Modal(document.getElementById("modalGuia"));    
    document.getElementById("modalGuiaId").textContent = "' . $recoleccion_id . '";
    document.getElementById("modalGuiaNombreRemitente").textContent = "' . $cliente_nombre . '";
    document.getElementById("modalGuiaTelefonoRemitente").textContent = "' . $cliente_telefono . '";
    document.getElementById("modalGuiaOrigen").textContent = "' . addslashes($direccion_origen_texto) . '";
    document.getElementById("modalGuiaNombre").textContent = "' . $nombre_destinatario . '";
    document.getElementById("modalGuiaTelefono").textContent = "' . $telefono_destinatario . '";
    document.getElementById("modalGuiaDireccion").textContent = "' . addslashes($direccion_destino_texto) . '";
    document.getElementById("modalGuiaDescripcion").textContent = "' . addslashes($observaciones) . '";
    document.getElementById("modalGuiaPagoEnvio").textContent = "' . ($pago_envio === "cliente" ? "Cobro a mi cuenta" : "Cobro contra entrega") . '";
    document.getElementById("modalGuiaCobro").textContent = "Q' . number_format($total_cobro, 2) . '";
    modal.show();
  });
</script>';

  } catch (Exception $e) {
    $pdo->rollBack();
    echo "Error al crear recolección: " . $e->getMessage();
  }
}

include 'partials/header.php';
include 'partials/sidebar.php';
echo $guia_script;
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Solicitar Recolección</h2>
  <form method="POST" class="row g-3" id="form-recoleccion">
    <div class="col-md-6">
      <label class="form-label">Dirección de Recolección</label>
      <select name="direccion_origen_id" class="form-select" required>
        <option value="">Seleccione</option>
        <?php foreach ($direcciones as $dir): ?>
          <option value="<?= $dir['id'] ?>">
            <?= "{$dir['calle']} #{$dir['numero']}, Zona {$dir['zona']}, {$dir['municipio']}, {$dir['departamento']}" ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">Dirección de Entrega</label>
      <select name="direccion_destino_id" class="form-select" required>
        <option value="">Seleccione</option>
        <?php foreach ($direcciones as $dir): ?>
          <option value="<?= $dir['id'] ?>">
            <?= "{$dir['calle']} #{$dir['numero']}, Zona {$dir['zona']}, {$dir['municipio']}, {$dir['departamento']}" ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">Nombre del Destinatario</label>
      <input type="text" name="nombre_destinatario" class="form-control" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Teléfono del Destinatario</label>
      <input type="text" name="telefono_destinatario" class="form-control" required>
    </div>
    <div class="col-12">
      <label class="form-label">Observaciones</label>
      <textarea name="observaciones" class="form-control" rows="2"></textarea>
    </div>
    <div class="col-md-6">
      <label class="form-label">¿Quién paga el envío?</label>
      <select name="pago_envio" class="form-select" required>
        <option value="cliente">Cobro a mi cuenta</option>
        <option value="destinatario">Cobro contra entrega</option>
      </select>
    </div>
    <div class="col-12">
      <label class="form-label">Seleccionar paquetes y cantidad</label>
      <?php foreach ($paquetes as $p): ?>
        <div class="row mb-2 align-items-center border rounded py-2 px-2">
          <div class="col-md-6">
            <strong><?= htmlspecialchars("{$p['nombre']} - {$p['tamano']} ({$p['peso']} kg) - Q{$p['tarifa']}") ?></strong>
          </div>
          <div class="col-md-4">
            <input type="number" min="0" name="paquete_ids[<?= $p['id'] ?>]" class="form-control cantidad-paquete" data-tarifa="<?= $p['tarifa'] ?>" placeholder="Cantidad">
          </div>
          <div class="col-md-2">
            <input type="number" min="0" step="0.01" name="monto_cobros[<?= $p['id'] ?>]" class="form-control" placeholder="Cobro (Q)">
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="col-12">
      <div class="alert alert-info text-center">
        <strong>Total estimado: Q<span id="total-estimado">0.00</span></strong>
      </div>
    </div>
    <div class="col-12 text-center">
      <button type="submit" class="btn btn-success">Crear recolección</button>
      <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>

<!-- Modal guía -->
<div class="modal fade" id="modalGuia" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body">
        <div id="contenidoGuia" style="font-family: monospace; font-size: 12px; line-height: 1.2;">
  ----------------------------------------<br>
          📦 GUÍA DE RECOLECCIÓN<br>
  ----------------------------------------<br>
  No. de Recolección: <span id="modalGuiaId"></span><br><br>

  ORIGEN (Remitente):<br>
  Nombre: <span id="modalGuiaNombreRemitente"></span><br>
  Teléfono: <span id="modalGuiaTelefonoRemitente"></span><br>
  Dirección de Recolección: <span id="modalGuiaOrigen"></span><br><br>

  DESTINO (Destinatario):<br>
  Nombre: <span id="modalGuiaNombre"></span><br>
  Teléfono: <span id="modalGuiaTelefono"></span><br>
  Dirección de Entrega: <span id="modalGuiaDireccion"></span><br><br>

  Descripción: <span id="modalGuiaDescripcion"></span><br><br>
  Forma de pago del envío: <span id="modalGuiaPagoEnvio"></span><br>
  Cobro total al cliente: <span id="modalGuiaCobro"></span><br><br>
  ¡Gracias por solicitar tu recolección!<br>
  ----------------------------------------
</div>


      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary no-print" onclick="window.print()">Imprimir</button>
        <a href="#" onclick="descargarPDF()" class="btn btn-success no-print">Descargar PDF</a>
      </div>
    </div>
  </div>
</div>

<style media="print">
  @page {
    size: A4 portrait;
    margin: 0;
  }

  html, body {
    height: 100%;
    margin: 0 !important;
    padding: 0 !important;
    overflow: hidden;
  }

  body * {
    visibility: hidden;
  }

  .modal.show,
  .modal.show * {
    visibility: visible;
  }

  .modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    margin: 0;
    padding: 0;
    height: 100%;
    overflow: hidden;
    background: white;
  }

  .modal-content {
    border: none !important;
    box-shadow: none !important;
    margin: 0 !important;
  }

  .modal-body {
    padding: 20px !important;
    max-height: 100%;
    overflow: hidden !important;
  }

  #contenidoGuia {
    font-family: monospace;
    font-size: 11px;
    line-height: 1.4;
    page-break-inside: avoid;
    max-height: 270mm;
    overflow: hidden;
  }

  .modal-footer,
  .no-print {
    display: none !important;
  }
</style>

<script>
  document.querySelectorAll('.cantidad-paquete').forEach(input => {
    input.addEventListener('input', calcularTotal);
  });

  function calcularTotal() {
    let total = 0;
    document.querySelectorAll('.cantidad-paquete').forEach(input => {
      const cantidad = parseInt(input.value) || 0;
      const tarifa = parseFloat(input.dataset.tarifa);
      total += cantidad * tarifa;
    });
    document.getElementById('total-estimado').textContent = total.toFixed(2);
  }

  function descargarPDF() {
    const id = document.getElementById('modalGuiaId').textContent;
    if (id) {
      window.open(`generar_pdf_recoleccion.php?id=${id}`, '_blank');
    }
  }
</script>

<?php include 'partials/footer.php'; ?>
