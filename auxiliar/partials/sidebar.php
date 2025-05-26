<!-- Sidebar móvil -->
<!--<div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="sidebarLabel">Menú Auxiliar</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
  </div>-->
  <!--<div class="offcanvas-body">
    <a href="dashboard.php" class="list-group-item list-group-item-action">Inicio</a>
    <a href="paquetes_por_confirmar.php" class="list-group-item list-group-item-action">Confirmar llegadas</a>
    <a href="asignar_ruta_envios.php" class="list-group-item list-group-item-action">Asignar ruta envios</a>
    <a href="historial_asignacciones.php" class="list-group-item list-group-item-action">Hitorial rutas envios asignados</a>
    <a href="asignar_ruta_recolecciones.php" class="list-group-item list-group-item-action">Asignar ruta recolecciones</a>
    <a href="asignar_ruta_entrega_recoleccion.php" class="list-group-item list-group-item-action">Asignar ruta entrega recolecciones</a>
     <a href="historial_asignaciones_reocoleccion.php" class="list-group-item list-group-item-action">Hitorial rutas recolecciones asignadas</a>
    <a href="asignar_piloto_a_ruta.php" class="list-group-item list-group-item-action">Asignar ruta pilotos</a>
    <a href="rutas_asignadas.php" class="list-group-item list-group-item-action">Rutas Asignadas</a>
    <a href="perfil.php" class="list-group-item list-group-item-action">Mi perfil</a>
  </div>
</div>-->

<!-- Sidebar escritorio -->
<!--<div class="col-lg-2 d-none d-lg-block bg-light border-end vh-100">
  <div class="list-group list-group-flush mt-3">
    <a href="dashboard.php" class="list-group-item list-group-item-action">Inicio</a>
    <a href="paquetes_por_confirmar.php" class="list-group-item list-group-item-action">Confirmar llegadas</a>
    <a href="asignar_ruta_envios.php" class="list-group-item list-group-item-action">Asignar ruta envios</a>
    <a href="historial_asignaciones.php" class="list-group-item list-group-item-action">Hitorial rutas envios asignados</a>
    <a href="asignar_ruta_recolecciones.php" class="list-group-item list-group-item-action">Asignar ruta recolecciones</a>
    <a href="asignar_ruta_entrega_recoleccion.php" class="list-group-item list-group-item-action">Asignar ruta entrega recolecciones</a>
     <a href="historial_asignaciones_recoleccion.php" class="list-group-item list-group-item-action">Hitorial rutas recolecciones asignadas</a>
    <a href="asignar_piloto_a_ruta.php" class="list-group-item list-group-item-action">Asignar ruta pilotos</a>
    <a href="rutas_asignadas.php" class="list-group-item list-group-item-action">Rutas Asignadas</a>
    <a href="perfil.php" class="list-group-item list-group-item-action">Mi perfil</a>
  </div>
</div>-->
<!-------------------------------------------------------------------------------------------------------------------------------->

<!-- Sidebar como offcanvas para móvil -->
<div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="sidebarLabel">Menú</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
  </div>
  <div class="offcanvas-body list-group list-group-flush">
    <a href="dashboard.php" class="list-group-item list-group-item-action">Inicio</a>
    <!--<a href="clientes.php" class="list-group-item list-group-item-action">Clientes</a>-->

    <!-- RUTAS - menú colapsable -->
    <a class="list-group-item list-group-item-action" data-bs-toggle="collapse" href="#submenuRutasMovil" role="button" aria-expanded="false" aria-controls="submenuRutasMovil">
      Rutas paquetes
    </a>
    <div class="collapse ps-3" id="submenuRutasMovil">
      <a href="asignar_ruta_envios.php" class="list-group-item list-group-item-action">Ruta para envios</a>
      <a href="historial_asignaciones_recoleccion.php" class="list-group-item list-group-item-action">Ruta para recolecciones</a>
      <a href="asignar_ruta_entrega_recoleccion.php" class="list-group-item list-group-item-action">Ruta para envio de recoleccion</a>
    </div>

    <a href="asignar_piloto_a_ruta.php" class="list-group-item list-group-item-action">Ruta piloto</a>
    <a class="list-group-item list-group-item-action" data-bs-toggle="collapse" href="#submenuReportesMovil" role="button" aria-expanded="false" aria-controls="submenuReportesMovil">
      Reportes
    </a>
    <div class="collapse ps-3" id="submenuReportesMovil">
      <a href="historial_asignaciones.php" class="list-group-item list-group-item-action">Ruta para envios</a>
      <a href="asignar_ruta_recolecciones.php" class="list-group-item list-group-item-action">Ruta para recolecciones y envinos reocelccion</a>
    </div>
    <!--<a href="flotas.php" class="list-group-item list-group-item-action">Flotas</a>
    <a href="rutas.php" class="list-group-item list-group-item-action">Rutas</a>
    <a href="paquetes.php" class="list-group-item list-group-item-action">Paquetes</a>
    <a href="departamentos.php" class="list-group-item list-group-item-action">Departamentos</a>
    <a href="municipios.php" class="list-group-item list-group-item-action">Municipios</a>
    <a href="zonas.php" class="list-group-item list-group-item-action">Zonas</a>
    <a href="reporte.php" class="list-group-item list-group-item-action">Reportes</a>-->
  </div>
</div>

<!-- Sidebar para escritorio -->
<div class="col-lg-2 d-none d-lg-block bg-light border-end vh-100">
  <div class="list-group list-group-flush mt-3">
    <a href="dashboard.php" class="list-group-item list-group-item-action">Inicio</a>
    <!--<a href="clientes.php" class="list-group-item list-group-item-action">Clientes</a>-->

    <!-- PILOTOS - menú colapsable -->
    <a class="list-group-item list-group-item-action" data-bs-toggle="collapse" href="#submenuRutas" role="button" aria-expanded="false" aria-controls="submenuRutas">
      Rutas
    </a>
    <div class="collapse ps-3" id="submenuRutas">
      <a href="asignar_ruta_envios.php" class="list-group-item list-group-item-action">Ruta para envios</a>
      <a href="historial_asignaciones_recoleccion.php" class="list-group-item list-group-item-action">Ruta para recolecciones</a>
      <a href="asignar_ruta_entrega_recoleccion.php" class="list-group-item list-group-item-action">Ruta para envio de recoleccion</a>
    </div>

    <a href="historial_asignaciones.php" class="list-group-item list-group-item-action">Ruta piloto</a>
    <a class="list-group-item list-group-item-action" data-bs-toggle="collapse" href="#submenuReportes" role="button" aria-expanded="false" aria-controls="submenuReportes">
      Reportes
    </a>
    <div class="collapse ps-3" id="submenuReportes">
      <a href="historial_asignaciones.php" class="list-group-item list-group-item-action">Ruta para envios</a>
      <a href="asignar_ruta_recolecciones.php" class="list-group-item list-group-item-action">Ruta para recolecciones</a>
    </div>
    <!--<a href="flotas.php" class="list-group-item list-group-item-action">Flotas</a>
    <a href="rutas.php" class="list-group-item list-group-item-action">Rutas</a>
    <a href="paquetes.php" class="list-group-item list-group-item-action">Paquetes</a>
    <a href="departamentos.php" class="list-group-item list-group-item-action">Departamentos</a>
    <a href="municipios.php" class="list-group-item list-group-item-action">Municipios</a>
    <a href="zonas.php" class="list-group-item list-group-item-action">Zonas</a>
    <a href="reporte.php" class="list-group-item list-group-item-action">Reportes</a>-->
  </div>
</div>


