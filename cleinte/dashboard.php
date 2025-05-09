<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'cliente') {
  header("Location: ../login.php");
  exit;
}

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<!-- CONTENIDO PRINCIPAL -->
<div class="col-lg-10 col-12 p-4">
  <h2>Bienvenido, Cliente</h2>
  <p class="lead">Desde aquí puedes gestionar tus envíos y recolecciones.</p>

  <div class="row">
    <div class="col-md-6 col-xl-4 mb-3">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <h5 class="card-title">Cotizador</h5>
          <p class="card-text">Calcula el costo de tu envío.</p>
          <a href="cotizador.php" class="btn btn-success">Ir</a>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-4 mb-3">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <h5 class="card-title">Recolecciones</h5>
          <p class="card-text">Solicita o revisa recolecciones.</p>
          <a href="recolecciones.php" class="btn btn-success">Ir</a>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-4 mb-3">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <h5 class="card-title">Mis envíos</h5>
          <p class="card-text">Consulta el estado de tus paquetes.</p>
          <a href="envios.php" class="btn btn-success">Ir</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'partials/footer.php'; ?>
