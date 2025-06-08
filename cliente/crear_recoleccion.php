<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'cliente') {
  header("Location: ../login.php");
  exit;
}

// Obtener ID del cliente
$stmt = $pdo->prepare("SELECT id FROM clientes WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$cliente_id = $stmt->fetchColumn();
//echo "<pre>Cliente ID: $cliente_id</pre>"; // Debug

// Obtener direcciones
$stmt = $pdo->prepare("SELECT d.*, z.numero AS zona, m.nombre AS municipio, dp.nombre AS departamento 
                      FROM direcciones d 
                      JOIN zona z ON d.zona_id = z.id 
                      JOIN municipios m ON d.municipio_id = m.id 
                      JOIN departamentos dp ON d.departamento_id = dp.id 
                      WHERE d.cliente_id = ?");
$stmt->execute([$cliente_id]);
$direcciones = $stmt->fetchAll();

// Obtener paquetes disponibles
$paquetes = $pdo->query("SELECT id, nombre, tamano, peso, tarifa FROM paquetes ORDER BY nombre")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $direccion_origen_id = $_POST['direccion_origen_id'];
  $direccion_destino_id = $_POST['direccion_destino_id'];
  $nombre_destinatario  = $_POST['nombre_destinatario'];
  $telefono_destinatario = $_POST['telefono_destinatario'];
  $observaciones = $_POST['observaciones'] ?? null;
  $paquete_ids = $_POST['paquete_ids'] ?? [];

  try {
    $pdo->beginTransaction();

    // Insertar recolección
    $stmt = $pdo->prepare("INSERT INTO recolecciones (cliente_id, direccion_origen_id, direccion_destino_id, nombre_destinatario, telefono_destinatario, descripcion) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$cliente_id, $direccion_origen_id, $direccion_destino_id, $nombre_destinatario, $telefono_destinatario, $observaciones]);

    $recoleccion_id = $pdo->lastInsertId();

    // Asociar paquetes
    $stmt = $pdo->prepare("INSERT INTO recolecciones_paquetes (recoleccion_id, paquete_id) VALUES (?, ?)");
    foreach ($paquete_ids as $paquete_id => $cantidad) {
      for ($i = 0; $i < (int)$cantidad; $i++) {
        $stmt->execute([$recoleccion_id, $paquete_id]);
      }
    }

    $pdo->commit();
    header("Location: mis_recolecciones.php");
    exit;
  } catch (Exception $e) {
    $pdo->rollBack();
    echo "Error al crear recolección: " . $e->getMessage();
  }
}

include 'partials/header.php';
include 'partials/sidebar.php';
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
</script>

<?php include 'partials/footer.php'; ?>
