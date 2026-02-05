<?php include BASE_PATH . '/piloto/partials/header.php'; ?>
<!-- CONTENIDO DE LA PÁGINA -->
<div class="flex-1 px-4 sm:px-8 py-6 lg:py-8">
  <div class="mb-8 mt-2">
    <h1 class="text-[#0d121b] dark:text-white text-3xl md:text-4xl font-black leading-tight tracking-[-0.033em]">
      Crear Nuevo Envio
    </h1>
    <p class="text-[#4c669a] dark:text-gray-400 text-base font-normal mt-2">
      Genere una guía digital y programe una recolección.
    </p>
  </div>
  <form method="POST" id="form-envio" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    <div class="lg:col-span-7 flex flex-col gap-6">
      <?php include BASE_PATH . '/piloto/partials/piloto/form_cliente_receptor.php'; ?>
      <?php include BASE_PATH . '/piloto/partials/piloto/form_logica_envio.php'; ?>
    </div>

    <div class="lg:col-span-5 flex flex-col gap-6">
      <?php include BASE_PATH . '/piloto/partials/piloto/form_paquetes.php'; ?>
      <?php include BASE_PATH . '/piloto/partials/piloto/help_card.php'; ?>
    </div>
  </form>

  <?php include BASE_PATH . '/piloto/partials/piloto/modal_guia.php'; ?>

  <script src="<?= BASE_URL ?>/piloto/partials/js/envio-paquetes.js?v=<?= time(); ?>"></script>
  <script>
    window.BASE_URL = <?= json_encode(BASE_URL) ?>;
  </script>


  <?php if (!empty($guia_data)): ?>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        // 1) llenar campos
        document.getElementById('modalGuiaId').textContent = <?= json_encode($guia_data['envio_id']) ?>;
        document.getElementById('modalGuiaNombre').textContent = <?= json_encode($guia_data['nombre']) ?>;
        document.getElementById('modalGuiaTelefono').textContent = <?= json_encode($guia_data['telefono']) ?>;
        document.getElementById('modalGuiaDireccion').textContent = <?= json_encode($guia_data['direccion']) ?>;
        document.getElementById('modalGuiaDescripcion').textContent = <?= json_encode($guia_data['descripcion']) ?>;
        document.getElementById('modalGuiaPagoEnvio').textContent = <?= json_encode($guia_data['pago_texto']) ?>;
        document.getElementById('modalGuiaCobro').textContent = "Q" + <?= json_encode($guia_data['cobro_total']) ?>;

        // 2) mostrar overlay
        const overlay = document.getElementById('modalGuiaOverlay');
        if (overlay) overlay.classList.remove('hidden');
      });
    </script>
  <?php endif; ?>


  <?php include BASE_PATH . '/piloto/partials/footer.php'; ?>