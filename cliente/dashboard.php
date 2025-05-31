<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'cliente') {
  header("Location: ../login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

// Obtener el ID del cliente
$stmt = $pdo->prepare("SELECT id FROM clientes WHERE user_id = ?");
$stmt->execute([$user_id]);
$cliente_id = $stmt->fetchColumn();

// Consultar resumen desde tabla 'envios'
$resumen = $pdo->prepare("
  SELECT estado_envio, COUNT(*) AS total
  FROM envios
  WHERE cliente_id = ?
  GROUP BY estado_envio
");
$resumen->execute([$cliente_id]);

// Inicializar todos los posibles estados
$estado = [
  'pendiente'  => 0,
  'recibido'   => 0,
  'cancelado'  => 0
];

// Llenar resultados
foreach ($resumen->fetchAll() as $r) {
  $estado[$r['estado_envio']] = $r['total'];
}

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Bienvenido</h2>
  <p class="lead">Resumen de tus envíos</p>

  <div class="row">
    <div class="col-md-4 mb-3">
      <div class="card text-bg-secondary h-100 shadow-sm text-center">
        <div class="card-body">
          <h5>Pendientes</h5>
          <h2><?= $estado['pendiente'] ?></h2>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-3">
      <div class="card text-bg-success h-100 shadow-sm text-center">
        <div class="card-body">
          <h5>Recibidos</h5>
          <h2><?= $estado['recibido'] ?></h2>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-3">
      <div class="card text-bg-danger h-100 shadow-sm text-center">
        <div class="card-body">
          <h5>Cancelados</h5>
          <h2><?= $estado['cancelado'] ?></h2>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-4 text-center">
    <a href="mis_envios.php" class="btn btn-primary btn-lg me-2">Ver Mis Envíos</a>
    <a href="solicitar_recoleccion.php" class="btn btn-outline-success btn-lg">Solicitar Recolección</a>
  </div>
</div>

<?php include 'partials/footer.php'; ?>
