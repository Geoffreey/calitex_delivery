<?php include '../partials/header.php'; ?>

<form method="POST" id="form-envio" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
  <div class="lg:col-span-7 flex flex-col gap-6">
    <?php include '../partials/piloto/form_cliente_receptor.php'; ?>
    <?php include '../partials/piloto/form_logica_envio.php'; ?>
  </div>

  <div class="lg:col-span-5 flex flex-col gap-6">
    <?php include '../partials/piloto/form_paquetes.php'; ?>
    <partials>
    <piloto>form_paquetes.php'; ?>
    <?php include '../partials/piloto/help_card.php'; ?>
  </div>
</form>

<?php include '../partials/piloto/modal_guia.php'; ?>

<script src="../piloto/partials/js/envio-paquetes.js"></script>

<?php if (!empty($guia_data)): ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('modalGuia');
    if (!modalEl) return;
    const modal = new bootstrap.Modal(modalEl);

    document.getElementById('modalGuiaId').textContent = <?= json_encode($guia_data['envio_id']) ?>;
    document.getElementById('modalGuiaNombre').textContent = <?= json_encode($guia_data['nombre']) ?>;
    document.getElementById('modalGuiaTelefono').textContent = <?= json_encode($guia_data['telefono']) ?>;
    document.getElementById('modalGuiaDireccion').textContent = <?= json_encode($guia_data['direccion']) ?>;
    document.getElementById('modalGuiaDescripcion').textContent = <?= json_encode($guia_data['descripcion']) ?>;
    document.getElementById('modalGuiaPagoEnvio').textContent = <?= json_encode($guia_data['pago_texto']) ?>;
    document.getElementById('modalGuiaCobro').textContent = "Q" + <?= json_encode($guia_data['cobro_total']) ?>;

    setTimeout(() => modal.show(), 300);
  });
</script>
<?php endif; ?>

<?php include '../partials/footer.php'; ?>