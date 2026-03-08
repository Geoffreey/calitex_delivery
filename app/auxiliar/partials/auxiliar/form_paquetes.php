<section class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-[#e7ebf3] dark:border-gray-800 p-6 h-fit">
            <div class="flex items-center justify-between mb-6 border-b border-gray-100 dark:border-gray-800 pb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">inventory_2</span>
                    <h2 class="text-[#0d121b] dark:text-white text-xl font-bold">Detalles del Paquete</h2>
                </div>
                <span id="itemsBadge" class="bg-primary/10 text-primary text-xs font-bold px-2.5 py-1 rounded-full">0 Items</span>
            </div>

            <div id="itemsContainer" class="space-y-4 mb-6"></div>

            <button id="btnAddItem"
                class="w-full py-3 px-4 rounded-lg border-2 border-dashed border-accent/40 text-accent font-bold hover:bg-accent/5 transition-all flex items-center justify-center gap-2"
                type="button">
                <span class="material-symbols-outlined">add</span>
                Add New Package
            </button>

            <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800 space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Peso Total:</span>
                    <span class="font-bold text-[#0d121b] dark:text-white"><span id="totalWeight">0.00</span> LB</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">costo de envío:</span>
                    <span class="font-bold text-[#0d121b] dark:text-white">Q <span id="totalFee">0.00</span></span>
                </div>
                <div class="flex justify-between text-lg pt-2">
                    <span class="font-bold text-[#0d121b] dark:text-white">Total Amount:</span>
                    <span class="font-black text-primary">Q <span id="totalAmount">0.00</span></span>
                </div>
            </div>

            <div class="mt-8 grid grid-cols-1 gap-3">
                <button
                    class="w-full bg-primary text-white py-4 rounded-xl font-bold text-lg shadow-lg shadow-primary/20 hover:shadow-primary/40 hover:-translate-y-0.5 active:translate-y-0 transition-all flex items-center justify-center gap-2"
                    type="submit">
                    <span class="material-symbols-outlined">local_shipping</span>
                    Crear Guía
                </button>

                <button class="w-full bg-transparent text-gray-500 dark:text-gray-400 py-3 rounded-lg font-semibold text-sm hover:text-gray-700 dark:hover:text-gray-200 transition-colors"
                    type="button">
                    Guardar como borrador
                </button>
            </div>

            <!-- Template oculto para clonar items -->
            <template id="itemTemplate">
                <div class="itemCard p-4 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30 flex flex-col gap-4">
                    <div class="flex justify-between items-start">
                        <p class="itemTitle text-xs font-bold text-gray-400 uppercase tracking-wider">Item #1</p>
                        <button class="btnDelete text-gray-400 hover:text-red-500 transition-colors" type="button">
                            <span class="material-symbols-outlined text-[20px]">delete</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-12 gap-3">
                        <div class="col-span-12">
                            <select class="pkgSelect custom-select-arrow w-full py-2 px-3 rounded-lg border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-primary">
                                <option value="">Seleccione un paquete</option>
                                <?php foreach ($paquetes as $p): ?>
                                    <option
                                        value="<?= (int)$p['id'] ?>"
                                        data-tarifa="<?= htmlspecialchars($p['tarifa']) ?>"
                                        data-peso="<?= htmlspecialchars($p['peso']) ?>">
                                        <?= htmlspecialchars("{$p['nombre']} - {$p['tamano']} ({$p['peso']} kg) - Q{$p['tarifa']}") ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-span-4">
                            <label class="text-[11px] font-bold text-gray-500 block mb-1">QTY</label>
                            <input class="qtyInput w-full py-2 px-3 rounded-lg border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-primary"
                                type="number" min="0" value="1" />
                        </div>

                        <div class="col-span-4">
                            <label class="text-[11px] font-bold text-gray-500 block mb-1">Cobro (Q)</label>
                            <input class="cobroInput w-full py-2 px-3 rounded-lg border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-primary"
                                type="number" min="0" step="0.01" value="0" />
                        </div>

                        <div class="col-span-4">
                            <label class="text-[11px] font-bold text-gray-500 block mb-1">Peso (kg)</label>
                            <input class="pesoInput w-full py-2 px-3 rounded-lg border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-primary"
                                type="number" min="0" step="0.01" value="0" />
                        </div>

                        <!-- Inputs REALES para backend (se llenan por JS) -->
                        <input type="hidden" class="hiddenPkgName" />
                        <input type="hidden" class="hiddenCobroName" />
                    </div>

                    <div class="flex justify-between text-sm pt-2 border-t border-gray-200/70 dark:border-gray-700">
                        <span class="text-gray-500">Subtotal item:</span>
                        <span class="font-bold text-[#0d121b] dark:text-white">Q <span class="itemSubtotal">0.00</span></span>
                    </div>
                </div>
            </template>
        </section>