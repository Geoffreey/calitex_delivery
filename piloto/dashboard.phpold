<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'piloto') {
  header("Location: ../login.php");
  exit;
}

$piloto_id = $_SESSION['user_id'];

include 'partials/header.php';
//include 'partials/sidebar.php';

// Consulta resumen
$resumen = $pdo->prepare("
  SELECT estado_envio, COUNT(*) AS total
  FROM envios
  WHERE piloto_id = ?
  GROUP BY estado_envio
");
$resumen->execute([$piloto_id]);

$estados = [
  'pendiente'   => 0,
  'en_proceso'  => 0,
  'entregado'   => 0,
  'anulado'     => 0
];

foreach ($resumen->fetchAll() as $row) {
  $estados[$row['estado_envio']] = $row['total'];
}
?>

<div class="col-lg-10 col-12 p-4">
  <h2>Bienvenido, Piloto</h2>
  <p class="lead">Resumen de tus paquetes asignados</p>

  <div class="row">
    <div class="col-md-6 col-xl-3 mb-3">
      <div class="card shadow-sm text-bg-secondary h-100">
        <div class="card-body text-center">
          <h5>Pendientes</h5>
          <h2><?= $estados['pendiente'] ?></h2>
        </div>
      </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-3">
      <div class="card shadow-sm text-bg-warning h-100">
        <div class="card-body text-center">
          <h5>En Proceso</h5>
          <h2><?= $estados['en_proceso'] ?></h2>
        </div>
      </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-3">
      <div class="card shadow-sm text-bg-success h-100">
        <div class="card-body text-center">
          <h5>Entregados</h5>
          <h2><?= $estados['entregado'] ?></h2>
        </div>
      </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-3">
      <div class="card shadow-sm text-bg-danger h-100">
        <div class="card-body text-center">
          <h5>Anulados</h5>
          <h2><?= $estados['anulado'] ?></h2>
        </div>
      </div>
    </div>
    <div class="mt-4 text-center">
  <a href="paquetes_asignados.php" class="btn btn-primary btn-lg">
    Ver Paquetes Asignados
  </a>
</div>
  </div>
</div>

<?php include 'partials/footer.php'; ?>