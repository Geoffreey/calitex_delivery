<!-- MODAL FOTO -->
<div id="modalFoto" class="fixed inset-0 z-[999] hidden">
  <div class="absolute inset-0 bg-black/60" onclick="closeFotoModal()"></div>

  <div class="relative mx-auto mt-10 w-[95%] max-w-xl rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl">
    <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
      <h3 class="text-lg font-black text-slate-900 dark:text-white">Foto de entrega</h3>
      <button class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" onclick="closeFotoModal()" type="button">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>

    <div class="p-5 space-y-4">
      <p class="text-sm text-slate-500 dark:text-slate-400">
        Tomá una foto del paquete entregado (o evidencia).
      </p>

      <input id="fotoInput" type="file" accept="image/*" capture="environment"
             class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary file:text-white file:font-bold">

      <img id="fotoPreview" class="hidden w-full rounded-xl border border-slate-200 dark:border-slate-800" alt="preview">

      <form id="formEntregar" method="POST" action="entregar_envio.php" class="space-y-3">
        <input type="hidden" name="envio_id" id="envioIdInput">
        <input type="hidden" name="firma_base64" id="firmaBase64Input">
        <input type="hidden" name="foto_base64" id="fotoBase64Input">

        <div class="flex gap-2 justify-end">
          <button type="button" class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 font-bold text-sm"
                  onclick="backToFirma()">
            Volver
          </button>
          <button type="submit" class="px-4 py-2 rounded-lg bg-green-600 text-white font-bold text-sm">
            Finalizar entrega
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
