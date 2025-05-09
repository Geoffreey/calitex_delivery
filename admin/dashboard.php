<?php
session_start(); // ← ESTE FALTABA
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<!-- CONTENIDO PRINCIPAL -->
<div class="col-lg-10 col-12 p-4">
  <h2>Bienvenido, Administrador</h2>
  <p class="lead">Este es el panel de control donde puedes gestionar todo el sistema.</p>

  <div class="row">
    <!-- Tarjetas que se acomodan automáticamente -->
    <div class="col-md-6 col-xl-4 mb-3">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <h5 class="card-title">Clientes</h5>
          <p class="card-text">Ver o administrar clientes registrados.</p>
          <a href="clientes.php" class="btn btn-primary">Ir</a>
        </div>
      </div>
    </div>
    <!-- Repite para otros módulos -->
  </div>
</div>


<?php include 'partials/footer.php'; ?>