<!-- MODAL FIRMA -->
<div id="modalFirma" class="fixed inset-0 z-[999] hidden">
  <div class="absolute inset-0 bg-black/60" onclick="closeFirmaModal()"></div>

  <div class="relative mx-auto mt-10 w-[95%] max-w-xl rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl">
    <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
      <h3 class="text-lg font-black text-slate-900 dark:text-white">Firma de entrega</h3>
      <button class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" onclick="closeFirmaModal()" type="button">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>

    <div class="p-5 space-y-3">
      <p class="text-sm text-slate-500 dark:text-slate-400">
        Pedile al cliente que firme aquí. Luego continuamos con la foto.
      </p>

      <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 p-3">
        <canvas id="firmaCanvas" class="w-full h-48 bg-white rounded-lg"></canvas>
      </div>

      <div class="flex flex-wrap gap-2 justify-between">
        <button type="button" class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 font-bold text-sm"
                onclick="clearFirma()">
          Limpiar
        </button>

        <button type="button" class="px-4 py-2 rounded-lg bg-primary text-white font-bold text-sm"
                onclick="goFotoModal()">
          Continuar a foto
        </button>
      </div>
    </div>
  </div>
</div>
