<section class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-[#e7ebf3] dark:border-gray-800 p-6">
            <div class="flex items-center gap-2 mb-6 border-b border-gray-100 dark:border-gray-800 pb-4">
                <span class="material-symbols-outlined text-primary">description</span>
                <h2 class="text-[#0d121b] dark:text-white text-xl font-bold">Lógica de envío</h2>
            </div>
            <div class="space-y-6">
                <div>
                    <p class="block text-sm font-semibold text-[#0d121b] dark:text-gray-200 mb-4">¿Quién paga el envío?</p>
                    <div class="flex gap-4">
                        <label class="flex-1 cursor-pointer group">
                            <input name="pago_envio" checked="" class="hidden peer" type="radio" value="cliente" />
                            <div class="flex items-center justify-center gap-2 py-3 px-4 rounded-lg border-2 border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 peer-checked:border-primary peer-checked:bg-primary/5 transition-all">
                                <span class="material-symbols-outlined text-gray-400 peer-checked:text-primary">upload</span>
                                <span class="font-medium text-gray-700 dark:text-gray-300 peer-checked:text-primary">Remitente</span>
                            </div>
                        </label>
                        <label class="flex-1 cursor-pointer group">
                            <input name="pago_envio" class="hidden peer" type="radio" value="destinatario" />
                            <div class="flex items-center justify-center gap-2 py-3 px-4 rounded-lg border-2 border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 peer-checked:border-primary peer-checked:bg-primary/5 transition-all">
                                <span class="material-symbols-outlined text-gray-400 peer-checked:text-primary">download</span>
                                <span class="font-medium text-gray-700 dark:text-gray-300 peer-checked:text-primary">Destinatario</span>
                            </div>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#0d121b] dark:text-gray-200 mb-2">Observaciones especiales</label>
                    <textarea name="descripcion" class="w-full px-4 py-3 rounded-lg border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 focus:ring-primary focus:border-primary text-sm resize-none" placeholder="Fragile items, specific delivery hours, gate codes..." rows="4"></textarea>
                </div>
            </div>
        </section>