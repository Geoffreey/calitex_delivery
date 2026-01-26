<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'auxiliar') {
  header("Location: ../login.php");
  exit;
}

// Obtener datos para selects
$departamentos = $pdo->query("SELECT id, nombre FROM departamentos ORDER BY nombre")->fetchAll();
$zonas = $pdo->query("
  SELECT z.id, z.numero, m.nombre AS municipio 
  FROM zona z 
  JOIN municipios m ON z.municipio_id = m.id 
  ORDER BY m.nombre, z.numero
")->fetchAll();

// Obtener lista de clientes
$clientes = $pdo->query("SELECT id, nombre FROM users WHERE rol = 'cliente' ORDER BY nombre")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_cliente_id = $_POST['cliente_id'] ?? null;
  $tipo = $_POST['tipo'];
  $calle = $_POST['calle'];
  $numero = $_POST['numero'];
  $zona_id = $_POST['zona_id'];
  $municipio_id = $_POST['municipio_id'];
  $departamento_id = $_POST['departamento_id'];
  $referencia = $_POST['referencia'];

  try {
    // Validar cliente
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE id = ? AND rol = 'cliente'");
    $stmt->execute([$user_cliente_id]);
    if ($stmt->fetchColumn() == 0) {
      $_SESSION['mensaje_error'] = "Cliente no válido.";
      header("Location: crear_direccion.php");
      exit;
    }

    // Obtener ID real del cliente
    $stmt = $pdo->prepare("SELECT id FROM clientes WHERE user_id = ?");
    $stmt->execute([$user_cliente_id]);
    $cliente_id_real = $stmt->fetchColumn();

    if (!$cliente_id_real) {
      $_SESSION['mensaje_error'] = "No se encontró el cliente en la tabla clientes.";
      header("Location: agregar_direccion.php");
      exit;
    }

    // Insertar dirección
    $stmt = $pdo->prepare("
      INSERT INTO direcciones 
      (cliente_id, tipo, calle, numero, zona_id, municipio_id, departamento_id, referencia)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$cliente_id_real, $tipo, $calle, $numero, $zona_id, $municipio_id, $departamento_id, $referencia]);

    $_SESSION['mensaje_exito'] = "Dirección creada exitosamente.";
    header("Location: agregar_direccion.php");
    exit;
  } catch (Exception $e) {
    $_SESSION['mensaje_error'] = "Error al guardar dirección: " . $e->getMessage();
    header("Location: agregar_direccion.php");
    exit;
  }
}

include 'partials/header.php';
//include 'partials/sidebar.php';
?>

<!-- Select2 y jQuery -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="col-lg-10 col-12 p-4">
  <?php if (isset($_SESSION['mensaje_exito'])): ?>
    <div class="alert alert-success"><?= $_SESSION['mensaje_exito']; unset($_SESSION['mensaje_exito']); ?></div>
  <?php endif; ?>

  <?php if (isset($_SESSION['mensaje_error'])): ?>
    <div class="alert alert-danger"><?= $_SESSION['mensaje_error']; unset($_SESSION['mensaje_error']); ?></div>
  <?php endif; ?>

  <h2>Agregar Nueva Dirección</h2>
  <form method="POST" class="row g-3">

    <div class="col-md-6">
      <label class="form-label">Seleccionar Cliente</label>
      <select id="cliente_id" name="cliente_id" class="form-select" required>
        <option value="">Seleccione un cliente</option>
        <?php foreach ($clientes as $cli): ?>
          <option value="<?= $cli['id'] ?>" <?= isset($_POST['cliente_id']) && $_POST['cliente_id'] == $cli['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($cli['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-6">
      <label class="form-label">Tipo</label>
      <select name="tipo" class="form-select" required>
        <option value="recoleccion">Recolección</option>
        <option value="entrega">Entrega</option>
      </select>
    </div>

    <div class="col-md-4">
      <label class="form-label">Calle</label>
      <input type="text" name="calle" class="form-control" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Número</label>
      <input type="text" name="numero" class="form-control" required>
    </div>

    <div class="col-md-4">
      <label class="form-label">Departamento</label>
      <select id="departamento" name="departamento_id" class="form-select" required>
        <option value="">Seleccione</option>
        <?php foreach ($departamentos as $d): ?>
          <option value="<?= $d['id'] ?>"><?= $d['nombre'] ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Municipio</label>
      <select id="municipio" name="municipio_id" class="form-select" required>
        <option value="">Seleccione</option>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Zona</label>
      <select id="zona" name="zona_id" class="form-select" required>
        <option value="">Seleccione</option>
      </select>
    </div>
    <div class="col-12">
      <label class="form-label">Referencia (opcional)</label>
      <textarea name="referencia" class="form-control" rows="3"></textarea>
    </div>
    <div class="col-12">
      <button type="submit" class="btn btn-success">Guardar dirección</button>
      <a href="mis_direcciones.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>

<script>
$(document).ready(function () {
  $('#cliente_id').select2({
    placeholder: "Buscar cliente...",
    width: '100%'
  });
});

document.getElementById('departamento').addEventListener('change', function () {
  const departamento_id = this.value;
  fetch('../ajax/municipios_por_departamento.php?departamento_id=' + departamento_id)
    .then(res => res.json())
    .then(data => {
      const municipioSelect = document.getElementById('municipio');
      municipioSelect.innerHTML = '<option value="">Seleccione</option>';
      data.forEach(m => {
        municipioSelect.innerHTML += `<option value="${m.id}">${m.nombre}</option>`;
      });

      document.getElementById('zona').innerHTML = '<option value="">Seleccione</option>';
    })
    .catch(error => {
      console.error('Error al cargar municipios:', error);
    });
});

document.getElementById('municipio').addEventListener('change', function () {
  const municipio_id = this.value;
  fetch('../ajax/zonas_por_municipio.php?municipio_id=' + municipio_id)
    .then(res => res.json())
    .then(data => {
      const zonaSelect = document.getElementById('zona');
      zonaSelect.innerHTML = '<option value="">Seleccione</option>';
      data.forEach(z => {
        zonaSelect.innerHTML += `<option value="${z.id}">Zona ${z.numero}</option>`;
      });
    })
    .catch(error => {
      console.error('Error al cargar zonas:', error);
    });
});
</script>

<?php include 'partials/footer.php'; ?>
