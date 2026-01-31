<!-- Overlay Modal (oculto por defecto) -->
<div id="modalGuiaOverlay" class="hidden fixed inset-0 z-50">
  <!-- fondo oscuro -->
  <div class="absolute inset-0 bg-black/50"></div>

  <!-- caja modal centrada -->
  <div class="relative z-10 h-full w-full flex items-center justify-center p-4">
    <div id="modalGuiaPanel" class="w-full max-w-2xl bg-white dark:bg-[#101622] rounded-xl overflow-hidden shadow-2xl max-h-[85vh] flex flex-col">
      <!-- Botón cerrar (no afecta tu diseño) -->
      <div class="no-print flex justify-end p-3 border-b border-[#e7ebf3] dark:border-[#2d3748]">
        <button type="button" id="btnCerrarGuia" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-[#1e273a]">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>

      <!-- Tu contenido tal cual -->
      <div id="modalGuia" class="flex-1 overflow-y-auto p-6 space-y-6">
        <!-- Headline -->
        <div class="text-center space-y-1">
          <h1 class="text-[#0d121b] dark:text-white text-2xl font-bold tracking-tight">📨 GUÍA DE ENTREGA - ENVÍO</h1>
          <p class="text-[#4c669a] dark:text-gray-400 text-sm">Resumen oficial de su paquete</p>
        </div>

        <!-- Primary Guide ID Card -->
        <div id="contenidoGuia" class="bg-primary/5 dark:bg-primary/10 border border-primary/20 rounded-xl p-6 flex flex-col sm:flex-row items-center gap-4">
          <div class="w-full sm:w-24 h-24 bg-white dark:bg-[#1a2131] rounded-lg border border-[#cfd7e7] dark:border-[#2d3748] flex items-center justify-center">
            <span class="material-symbols-outlined text-4xl text-primary">qr_code_2</span>
          </div>
          <div class="flex-1 text-center sm:text-left">
            <p class="text-primary text-xs font-bold uppercase tracking-widest mb-1">DETALLES DE LA GUÍA</p>
            <p class="text-[#0d121b] dark:text-white text-2xl font-extrabold leading-tight">No. de Guía: <span id="modalGuiaId"></span></p>
            <p class="text-[#4c669a] dark:text-gray-400 text-sm mt-1">Generada el <?= date('d \d\e F, Y'); ?></p>
          </div>
        </div>

        <!-- Information Grid -->
        <div class="grid grid-cols-1 gap-y-0 border-t border-[#cfd7e7] dark:border-[#2d3748]">
          <div class="grid grid-cols-[30%_1fr] py-4 border-b border-[#cfd7e7] dark:border-[#2d3748] items-center">
            <p class="text-[#4c669a] dark:text-gray-400 text-sm font-medium">Nombre</p>
            <p class="text-[#0d121b] dark:text-white text-sm font-semibold"><span id="modalGuiaNombre"></span></p>
          </div>

          <div class="grid grid-cols-[30%_1fr] py-4 border-b border-[#cfd7e7] dark:border-[#2d3748] items-center">
            <p class="text-[#4c669a] dark:text-gray-400 text-sm font-medium">Teléfono</p>
            <p class="text-[#0d121b] dark:text-white text-sm font-semibold"><span id="modalGuiaTelefono"></span></p>
          </div>

          <div class="grid grid-cols-[30%_1fr] py-4 border-b border-[#cfd7e7] dark:border-[#2d3748] items-start">
            <p class="text-[#4c669a] dark:text-gray-400 text-sm font-medium pt-0.5">Dirección</p>
            <p class="text-[#0d121b] dark:text-white text-sm leading-relaxed"><span id="modalGuiaDireccion"></span></p>
          </div>

          <div class="grid grid-cols-[30%_1fr] py-4 border-b border-[#cfd7e7] dark:border-[#2d3748] items-start">
            <p class="text-[#4c669a] dark:text-gray-400 text-sm font-medium pt-0.5">Descripción</p>
            <p class="text-[#0d121b] dark:text-white text-sm leading-relaxed"><span id="modalGuiaDescripcion"></span></p>
          </div>

          <div class="grid grid-cols-[30%_1fr] py-4 border-b border-[#cfd7e7] dark:border-[#2d3748] items-center">
            <p class="text-[#4c669a] dark:text-gray-400 text-sm font-medium">Forma de pago</p>
            <p class="text-[#0d121b] dark:text-white text-sm font-semibold"><span id="modalGuiaPagoEnvio"></span></p>
          </div>
        </div>

        <!-- Financial Highlight Section -->
        <div class="@container">
          <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-5 rounded-xl border-2 border-dashed border-[#cfd7e7] dark:border-[#2d3748] bg-gray-50 dark:bg-[#1e273a]">
            <div class="flex flex-col gap-1 text-center sm:text-left">
              <p class="text-[#0d121b] dark:text-white text-base font-bold">Cobro total al cliente</p>
              <p class="text-[#4c669a] dark:text-gray-400 text-sm">Impuestos y envío incluidos</p>
            </div>
            <div class="flex items-center gap-2 bg-success/10 text-success border border-success/20 px-6 py-2 rounded-full">
              <span id="modalGuiaCobro" class="text-2xl font-black tracking-tight"></span>
              <span class="material-symbols-outlined">check_circle</span>
            </div>
          </div>
        </div>

        <!-- Footer Message -->
        <div class="text-center pt-2 pb-4">
          <p class="text-[#4c669a] dark:text-gray-400 text-sm font-medium">📦 ¡Gracias por usar nuestro servicio!</p>
          <div class="mt-4 border-t border-[#cfd7e7] dark:border-[#2d3748] w-1/4 mx-auto"></div>
        </div>
      </div>

      <!-- Action Footer -->
      <footer class="no-print bg-gray-50 dark:bg-[#1e273a] p-6 border-t border-[#e7ebf3] dark:border-[#2d3748] flex flex-col sm:flex-row gap-3">
        <button type="button" id="btnDescargarPdf" class="flex-1 flex items-center justify-center gap-2 h-12 bg-primary text-white rounded-lg font-bold hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">
          <span class="material-symbols-outlined">download</span>
          Descargar PDF
        </button>

        <button type="button" id="btnImprimirGuia" class="flex-1 flex items-center justify-center gap-2 h-12 bg-white dark:bg-[#2d3748] text-[#0d121b] dark:text-white border border-[#cfd7e7] dark:border-[#4a5568] rounded-lg font-bold hover:bg-gray-100 dark:hover:bg-[#38445a] transition-all">
          <span class="material-symbols-outlined">print</span>
          Imprimir
        </button>
      </footer>

    </div>
  </div>
</div>
