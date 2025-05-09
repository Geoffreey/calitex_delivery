<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'auxiliar') {
  header("Location: ../login.php");
  exit;
}

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<!-- CONTENIDO PRINCIPAL -->
<div class="col-lg-10 col-12 p-4">
  <h2>Bienvenido, Auxiliar</h2>
  <p class="lead">Desde aquí puedes registrar la llegada de paquetes y realizar entregas a los pilotos.</p>

  <div class="row">
    <div class="col-md-6 col-xl-4 mb-3">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <h5 class="card-title">Llegadas</h5>
          <p class="card-text">Confirmar paquetes que llegan a bodega.</p>
          <a href="llegadas.php" class="btn btn-warning text-dark">Ver</a>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-4 mb-3">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <h5 class="card-title">Entregas</h5>
          <p class="card-text">Registrar entrega de paquetes al piloto.</p>
          <a href="entregas.php" class="btn btn-warning text-dark">Ver</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'partials/footer.php'; ?>
