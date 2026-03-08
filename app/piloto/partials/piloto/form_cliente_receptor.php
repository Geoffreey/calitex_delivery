<section class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-[#e7ebf3] dark:border-gray-800 p-6">
            <div class="flex items-center gap-2 mb-6 border-b border-gray-100 dark:border-gray-800 pb-4">
                <span class="material-symbols-outlined text-primary">person</span>
                <h2 class="text-[#0d121b] dark:text-white text-xl font-bold">Cliente &amp; Receptor</h2>
            </div>
            <div class="grid grid-cols-1 gap-6">
                <!-- Searchable Select -->
                <div>
                    <label class="block text-sm font-semibold text-[#0d121b] dark:text-gray-200 mb-2">Seleccionar Cliente</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <span class="material-symbols-outlined">search</span>
                        </span>
                        <select name="cliente_id" required onchange="document.getElementById('form-envio').submit();" class="custom-select-arrow block w-full pl-10 pr-10 py-3 rounded-lg border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-primary focus:border-primary">
                            <option value="">Seleccione un cliente</option>
                            <?php foreach ($clientes as $cli): ?>
                                <option value="<?= $cli['cliente_id'] ?>" <?= $cliente_id == $cli['cliente_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cli['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-[#0d121b] dark:text-gray-200 mb-2">Nombre del destinatario</label>
                        <input name="nombre_destinatario" required class="w-full px-4 py-3 rounded-lg border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 focus:ring-primary focus:border-primary text-sm" placeholder="Full name" type="text" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-[#0d121b] dark:text-gray-200 mb-2">Teléfono del destinatario</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <span class="material-symbols-outlined text-[20px]">call</span>
                            </span>
                            <input name="telefono_destinatario" required class="w-full pl-10 pr-4 py-3 rounded-lg border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 focus:ring-primary focus:border-primary text-sm" placeholder="+1 (555) 000-0000" type="tel" />
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#0d121b] dark:text-gray-200 mb-2">Dirección de entrega</label>
                    <div class="relative">
                        <span class="absolute top-3 left-3 flex items-start pointer-events-none text-gray-400">
                            <span class="material-symbols-outlined text-[20px]">location_on</span>
                        </span>
                        <select name="direccion_destino_id" required class="custom-select-arrow block w-full pl-10 pr-10 py-3 rounded-lg border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-primary focus:border-primary">
                            <option value="">Seleccione una dirección</option>
                            <?php foreach ($direcciones as $dir): ?>
                                <option value="<?= $dir['id'] ?>">
                                    <?= "{$dir['calle']} #{$dir['numero']}, Zona {$dir['zona']}, {$dir['municipio']}, {$dir['departamento']}" ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </section>