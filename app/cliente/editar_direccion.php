<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'cliente') {
  header("Location: ../login.php");
  exit;
}

$id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

// Obtener ID del cliente
$stmt = $pdo->prepare("SELECT id FROM clientes WHERE user_id = ?");
$stmt->execute([$user_id]);
$cliente_id = $stmt->fetchColumn();

// Validar propiedad de la dirección
$stmt = $pdo->prepare("SELECT * FROM direcciones WHERE id = ? AND cliente_id = ?");
$stmt->execute([$id, $cliente_id]);
$direccion = $stmt->fetch();

if (!$direccion) {
  echo "Dirección no encontrada o no autorizada.";
  exit;
}

// Cargar datos para selects
$departamentos = $pdo->query("SELECT id, nombre FROM departamentos ORDER BY nombre")->fetchAll();
$municipios = $pdo->query("SELECT id, nombre FROM municipios ORDER BY nombre")->fetchAll();
$zonas = $pdo->query("
  SELECT z.id, z.numero, m.nombre AS municipio 
  FROM zona z 
  JOIN municipios m ON z.municipio_id = m.id 
  ORDER BY m.nombre, z.numero
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $tipo = $_POST['tipo'];
  $calle = $_POST['calle'];
  $numero = $_POST['numero'];
  $zona_id = $_POST['zona_id'];
  $municipio_id = $_POST['municipio_id'];
  $departamento_id = $_POST['departamento_id'];
  $referencia = $_POST['referencia'];

  try {
    $stmt = $pdo->prepare("
      UPDATE direcciones SET 
        tipo = ?, calle = ?, numero = ?, zona_id = ?, municipio_id = ?, departamento_id = ?, referencia = ?
      WHERE id = ? AND cliente_id = ?
    ");
    $stmt->execute([
      $tipo, $calle, $numero, $zona_id, $municipio_id, $departamento_id, $referencia,
      $id, $cliente_id
    ]);
    header("Location: mis_direcciones.php");
    exit;
  } catch (Exception $e) {
    echo "Error al actualizar dirección: " . $e->getMessage();
  }
}

include 'partials/header.php';
//include 'partials/sidebar.php';
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Editar Dirección</h2>
  <form method="POST" class="row g-3">
    <div class="col-md-4">
      <label class="form-label">Tipo</label>
      <select name="tipo" class="form-select" required>
        <option value="recoleccion" <?= $direccion['tipo'] == 'recoleccion' ? 'selected' : '' ?>>Recolección</option>
        <option value="entrega" <?= $direccion['tipo'] == 'entrega' ? 'selected' : '' ?>>Entrega</option>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Calle</label>
      <input type="text" name="calle" class="form-control" value="<?= htmlspecialchars($direccion['calle']) ?>" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Número</label>
      <input type="text" name="numero" class="form-control" value="<?= htmlspecialchars($direccion['numero']) ?>" required>
    </div>

    <div class="col-md-4">
      <label class="form-label">Departamento</label>
      <select name="departamento_id" class="form-select" required>
        <?php foreach ($departamentos as $d): ?>
          <option value="<?= $d['id'] ?>" <?= $d['id'] == $direccion['departamento_id'] ? 'selected' : '' ?>>
            <?= $d['nombre'] ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-4">
      <label class="form-label">Municipio</label>
      <select name="municipio_id" class="form-select" required>
        <?php foreach ($municipios as $m): ?>
          <option value="<?= $m['id'] ?>" <?= $m['id'] == $direccion['municipio_id'] ? 'selected' : '' ?>>
            <?= $m['nombre'] ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-4">
      <label class="form-label">Zona</label>
      <select name="zona_id" class="form-select" required>
        <?php foreach ($zonas as $z): ?>
          <option value="<?= $z['id'] ?>" <?= $z['id'] == $direccion['zona_id'] ? 'selected' : '' ?>>
            <?= $z['municipio'] ?> - Zona <?= $z['numero'] ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-12">
      <label class="form-label">Referencia</label>
      <textarea name="referencia" class="form-control" rows="3"><?= htmlspecialchars($direccion['referencia']) ?></textarea>
    </div>

    <div class="col-12">
      <button type="submit" class="btn btn-primary">Actualizar dirección</button>
      <a href="mis_direcciones.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>

<?php include 'partials/footer.php'; ?>