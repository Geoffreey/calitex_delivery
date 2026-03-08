<!-- Sidebar como offcanvas para móvil -->
<!--<div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="sidebarLabel">Menú</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
  </div>
  <div class="offcanvas-body">
    <a href="dashboard.php" class="list-group-item list-group-item-action">Inicio</a>
    <a href="clientes.php" class="list-group-item list-group-item-action">Clientes</a>
    <a href="pilotos.php" class="list-group-item list-group-item-action">Pilotos</a>
    <li class="nav-item">
  <a class="nav-link" href="rutas_asignadas.php">
    <i class="bi bi-map"></i> Rutas Asignadas
  </a>
</li>
    <a href="asignar_piloto_a_ruta.php" class="list-group-item list-group-item-action">Asignar ruta pilotos</a>
    <a href="auxiliares.php" class="list-group-item list-group-item-action">Auxiliares</a>
    <a href="flotas.php" class="list-group-item list-group-item-action">Flotas</a>
    <a href="rutas.php" class="list-group-item list-group-item-action">Rutas</a>
    <a href="paquetes.php" class="list-group-item list-group-item-action">Paquetes</a>
    <a href="departamentos.php" class="list-group-item list-group-item-action">Departamentos</a>
    <a href="municipios.php" class="list-group-item list-group-item-action">Municipios</a>
    <a href="zonas.php" class="list-group-item list-group-item-action">Zonas</a>
    <a href="reporte.php" class="list-group-item list-group-item-action">Reportes</a>
  </div>
</div>-->

<!-- Sidebar para escritorio -->
<!--<div class="col-lg-2 d-none d-lg-block bg-light border-end vh-100">
  <div class="list-group list-group-flush mt-3">
    <a href="dashboard.php" class="list-group-item list-group-item-action">Inicio</a>
    <a href="clientes.php" class="list-group-item list-group-item-action">Clientes</a>
    <a href="pilotos.php" class="list-group-item list-group-item-action">Pilotos</a>
    <li class="nav-item">
  <a class="nav-link" href="rutas_asignadas.php">
    <i class="bi bi-map"></i> Rutas Asignadas
  </a>
</li>
    <a href="asignar_piloto_a_ruta.php" class="list-group-item list-group-item-action">Asignar ruta pilotos</a>
    <a href="auxiliares.php" class="list-group-item list-group-item-action">Auxiliares</a>
    <a href="flotas.php" class="list-group-item list-group-item-action">Flotas</a>
    <a href="rutas.php" class="list-group-item list-group-item-action">Rutas</a>
    <a href="paquetes.php" class="list-group-item list-group-item-action">Paquetes</a>
    <a href="departamentos.php" class="list-group-item list-group-item-action">Departamentos</a>
    <a href="municipios.php" class="list-group-item list-group-item-action">Municipios</a>
    <a href="zonas.php" class="list-group-item list-group-item-action">Zonas</a>
    <a href="reporte.php" class="list-group-item list-group-item-action">Reportes</a>
  </div>
</div>-->



<!-- Sidebar como offcanvas para móvil -->
<div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="sidebarLabel">Menú</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
  </div>
  <div class="offcanvas-body list-group list-group-flush">
    <a href="dashboard.php" class="list-group-item list-group-item-action">Inicio</a>
    <a href="clientes.php" class="list-group-item list-group-item-action">Clientes</a>

    <!-- PILOTOS - menú colapsable -->
    <a class="list-group-item list-group-item-action" data-bs-toggle="collapse" href="#submenuPilotosMovil" role="button" aria-expanded="false" aria-controls="submenuPilotosMovil">
      Pilotos
    </a>
    <div class="collapse ps-3" id="submenuPilotosMovil">
      <a href="pilotos.php" class="list-group-item list-group-item-action">lista pilotos</a>
      <a href="rutas_asignadas.php" class="list-group-item list-group-item-action">Rutas Asignadas</a>
      <a href="asignar_piloto_a_ruta.php" class="list-group-item list-group-item-action">Asignar Ruta a Piloto</a>
    </div>

    <a href="auxiliares.php" class="list-group-item list-group-item-action">Auxiliares</a>
    <a href="flotas.php" class="list-group-item list-group-item-action">Flotas</a>
    <a href="rutas.php" class="list-group-item list-group-item-action">Rutas</a>
    <a href="paquetes.php" class="list-group-item list-group-item-action">Paquetes</a>
    <a href="departamentos.php" class="list-group-item list-group-item-action">Departamentos</a>
    <a href="municipios.php" class="list-group-item list-group-item-action">Municipios</a>
    <a href="zonas.php" class="list-group-item list-group-item-action">Zonas</a>
    <a href="reporte.php" class="list-group-item list-group-item-action">Reportes</a>
  </div>
</div>

<!-- Sidebar para escritorio -->
<div class="col-lg-2 d-none d-lg-block bg-light border-end vh-100">
  <div class="list-group list-group-flush mt-3">
    <a href="dashboard.php" class="list-group-item list-group-item-action">Inicio</a>
    <a href="clientes.php" class="list-group-item list-group-item-action">Clientes</a>

    <!-- PILOTOS - menú colapsable -->
    <a class="list-group-item list-group-item-action" data-bs-toggle="collapse" href="#submenuPilotos" role="button" aria-expanded="false" aria-controls="submenuPilotos">
      Pilotos
    </a>
    <div class="collapse ps-3" id="submenuPilotos">
      <a href="pilotos.php" class="list-group-item list-group-item-action">Lista pilotos</a>
      <a href="rutas_asignadas.php" class="list-group-item list-group-item-action">Rutas Asignadas</a>
      <a href="asignar_piloto_a_ruta.php" class="list-group-item list-group-item-action">Asignar Ruta a Piloto</a>
    </div>

    <a href="auxiliares.php" class="list-group-item list-group-item-action">Auxiliares</a>
    <a href="flotas.php" class="list-group-item list-group-item-action">Flotas</a>
    <a href="rutas.php" class="list-group-item list-group-item-action">Rutas</a>
    <a href="paquetes.php" class="list-group-item list-group-item-action">Paquetes</a>
    <a href="departamentos.php" class="list-group-item list-group-item-action">Departamentos</a>
    <a href="municipios.php" class="list-group-item list-group-item-action">Municipios</a>
    <a href="zonas.php" class="list-group-item list-group-item-action">Zonas</a>
    <a href="reporte.php" class="list-group-item list-group-item-action">Reportes</a>
  </div>
</div>