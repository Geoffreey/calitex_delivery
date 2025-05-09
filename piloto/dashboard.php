<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'piloto') {
  header("Location: ../login.php");
  exit;
}

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<!-- CONTENIDO PRINCIPAL -->
<div class="col-lg-10 col-12 p-4">
  <h2>Bienvenido, Piloto</h2>
  <p class="lead">Aquí puedes ver tus rutas, entregas y recolecciones asignadas.</p>

  <div class="row">
    <div class="col-md-6 col-xl-4 mb-3">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <h5 class="card-title">Rutas asignadas</h5>
          <p class="card-text">Consulta las rutas que tienes hoy.</p>
          <a href="rutas.php" class="btn btn-dark">Ver</a>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-4 mb-3">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <h5 class="card-title">Recolecciones</h5>
          <p class="card-text">Ve los paquetes que debes recolectar.</p>
          <a href="recolecciones.php" class="btn btn-dark">Ver</a>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-4 mb-3">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <h5 class="card-title">Entregas</h5>
          <p class="card-text">Consulta los paquetes por entregar.</p>
          <a href="envios.php" class="btn btn-dark">Ver</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'partials/footer.php'; ?>

