<?php include BASE_PATH . '/auxiliar/partials/header.php'; ?>
<!-- PageHeading -->
<div class="flex-1 px-4 sm:px-8 py-6 lg:py-8">
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">Lista de Tareas</h1>
                <div class="mt-2 flex items-center gap-2 text-slate-500 dark:text-slate-400">
                    <span class="material-symbols-outlined text-sm">person</span>
                    <p class="text-sm font-medium"><?= htmlspecialchars(trim(($_SESSION['nombre'] ?? '').' '.($_SESSION['apellido'] ?? '')) ?: 'Auxiliar') ?></p>
                    <span class="mx-1">•</span>
                    <span class="material-symbols-outlined text-sm">route</span>
                    <p class="text-sm font-medium">Ruta: Zona Norte - 25 Oct</p>
                </div>
            </div>
            <div class="flex gap-2">
                <button class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-semibold hover:bg-slate-50 transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-lg">download</span>
                    Exportar
                </button>
                <button class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg text-sm font-semibold hover:bg-primary/90 transition-colors shadow-lg shadow-primary/20">
                    <span class="material-symbols-outlined text-lg">refresh</span>
                    Actualizar
                </button>
            </div>
        </div>
    </div>
    <!-- Tabs -->
    <div class="mb-6 border-b border-slate-200 dark:border-slate-800">
        <div class="flex gap-8">
            <button class="flex items-center gap-2 border-b-2 border-primary px-1 pb-4 text-sm font-bold text-primary">
                Todas <span class="bg-primary/10 px-2 py-0.5 rounded-full text-xs"><?= (int)$counts['total'] ?></span>
            </button>
            <button class="flex items-center gap-2 border-b-2 border-transparent px-1 pb-4 text-sm font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
                Pendientes <span class="bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full text-xs"><?= (int)$counts['pendientes'] ?></span>
            </button>
            <!--<button class="flex items-center gap-2 border-b-2 border-transparent px-1 pb-4 text-sm font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
                En Tránsito <span class="bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full text-xs"><?= (int)$counts['en_transito'] ?? 0 ?></span>
            </button> -->
            <button class="flex items-center gap-2 border-b-2 border-transparent px-1 pb-4 text-sm font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
                Completadas <span class="bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full text-xs"><?= (int)$counts['recibidos'] ?></span>
            </button>
        </div>
    </div>
    <!-- Content Area -->
    <div class="@container">
        <!-- Desktop Table View -->
        <div class="hidden @[800px]:block overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">No. Guía</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Destinatario</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-center">Teléfono</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Dirección</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Piloto</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Estado</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    <?php if (empty($envios)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                No hay envíos asignados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($envios as $e): ?>
                            <?php
                            $estado = $e['estado_envio'] ?? 'pendiente';
                            $estadoLabel = estado_label_ui($estado);
                            $pill = estado_pill_class($estado);
                            $dot  = estado_dot_class($estado);

                            // En la tabla de tu plantilla solo hay una dirección visible; usamos destino.
                            $direccion = $e['destino_direccion'] ?: '—';

                            // “No. Guía” en tu plantilla es #GTX-xxxx, pero en tu BD tienes e.id.
                            $guia = '#' . $e['id'];

                            $tel = $e['telefono_destinatario'] ?? '';

                            $pilotoNombre = trim(($e['piloto_nombre'] ?? '') . ' ' . ($e['piloto_apellido'] ?? ''));
                            if ($pilotoNombre === '') $pilotoNombre = 'No asignado';
                            ?>

                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a class="text-sm font-bold text-primary hover:underline" href="#">
                                        <?= htmlspecialchars($guia) ?>
                                    </a>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="text-sm font-semibold text-slate-900 dark:text-white">
                                        <?= htmlspecialchars($e['nombre_destinatario'] ?? '—') ?>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <?php if ($tel !== ''): ?>
                                        <a class="inline-flex items-center justify-center size-8 rounded-full bg-primary/10 text-primary hover:bg-primary hover:text-white transition-all"
                                            href="tel:<?= htmlspecialchars($tel) ?>">
                                            <span class="material-symbols-outlined text-lg">call</span>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400">—</span>
                                    <?php endif; ?>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2 group cursor-pointer">
                                        <span class="text-sm text-slate-600 dark:text-slate-400 truncate max-w-[200px]">
                                            <?= htmlspecialchars($direccion) ?>
                                        </span>
                                        <span class="material-symbols-outlined text-lg text-slate-400 group-hover:text-primary">map</span>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="text-sm font-semibold text-slate-900 dark:text-white">
                                        <?= htmlspecialchars($pilotoNombre) ?>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full text-xs font-bold <?= $pill ?>">
                                        <span class="h-1.5 w-1.5 rounded-full <?= $dot ?>"></span>
                                        <?= htmlspecialchars($estadoLabel) ?>
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            type="button"
                                            class="px-3 py-1.5 text-xs font-bold text-primary border border-primary rounded hover:bg-primary hover:text-white transition-colors btnVerGuia"
                                            data-envio-id="<?= (int)$e['id'] ?>">
                                            Ver
                                        </button>


                                        <?php if (($e['estado_envio'] ?? '') !== 'recibido' && ($e['estado_envio'] ?? '') !== 'cancelado'): ?>
                                            <button
                                                type="button"
                                                onclick="openFirmaModal(<?= (int)$e['id'] ?>)"
                                                class="flex items-center justify-center py-3 text-sm font-bold bg-green-600 text-white rounded-lg shadow-sm w-full">
                                                Recibido
                                            </button>


                                            <a href="cancelar_envio.php?id=<?= (int)$e['id'] ?>"
                                                class="px-3 py-1.5 text-xs font-bold text-red-600 border border-red-200 rounded hover:bg-red-50 transition-colors">
                                                Cancelar
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Mobile Card View -->
        <div class="@[800px]:hidden space-y-4">
            <?php if (empty($envios)): ?>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 text-center text-sm text-slate-500 dark:text-slate-400">
                    No hay envíos asignados.
                </div>
            <?php else: ?>
                <?php foreach ($envios as $e): ?>
                    <?php
                    $estado = $e['estado_envio'] ?? 'pendiente';
                    $estadoLabel = estado_label_ui($estado);
                    $guia = '#' . $e['id'];
                    $direccion = $e['destino_direccion'] ?: '—';
                    $tel = $e['telefono_destinatario'] ?? '';
                    ?>
                    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                        <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-start">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs font-bold uppercase
                                        <?php
                                        // respetamos tu estilo: pendiente amber, recibido primary/verde, cancelado rojo
                                        echo $estado === 'recibido'
                                            ? 'text-primary'
                                            : ($estado === 'cancelado' ? 'text-red-600' : 'text-amber-600');
                                        ?>
                                        ">
                                        <?= htmlspecialchars($estadoLabel) ?>
                                    </span>
                                    <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                    <span class="text-xs font-medium text-slate-500"><?= htmlspecialchars($guia) ?></span>
                                </div>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                                    <?= htmlspecialchars($e['nombre_destinatario'] ?? '—') ?>
                                </h3>
                            </div>

                            <?php if ($tel !== ''): ?>
                                <a class="size-10 flex items-center justify-center rounded-full bg-primary/10 text-primary" href="tel:<?= htmlspecialchars($tel) ?>">
                                    <span class="material-symbols-outlined">call</span>
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="p-4 space-y-3">
                            <div class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-slate-400 mt-0.5">location_on</span>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-slate-900 dark:text-slate-100">Dirección</p>
                                    <p class="text-sm text-slate-500 dark:text-slate-400"><?= htmlspecialchars($direccion) ?></p>
                                </div>
                                <button class="p-2 text-primary" type="button">
                                    <span class="material-symbols-outlined">map</span>
                                </button>
                            </div>
                        </div>

                        <?php
                        $pilotoNombre = trim(($e['piloto_nombre'] ?? '') . ' ' . ($e['piloto_apellido'] ?? ''));
                        if ($pilotoNombre === '') $pilotoNombre = 'No asignado';
                        ?>
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-slate-400 mt-0.5">two_wheeler</span>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-slate-900 dark:text-slate-100">Piloto</p>
                                <p class="text-sm text-slate-500 dark:text-slate-400"><?= htmlspecialchars($pilotoNombre) ?></p>
                            </div>
                        </div>

                        <div class="p-4 bg-slate-50 dark:bg-slate-800/40 grid grid-cols-2 gap-3">
                            <button type="button" class="px-3 py-1.5 text-xs font-bold text-primary border border-primary rounded hover:bg-primary hover:text-white transition-colors btnVerGuia" data-envio-id="<?= (int)$e['id'] ?>">
                                Ver
                            </button>

                            <?php if ($estado !== 'recibido' && $estado !== 'cancelado'): ?>
                                <button
                                    type="button"
                                    onclick="openFirmaModal(<?= (int)$e['id'] ?>)"
                                    class="px-3 py-1.5 text-xs font-bold bg-green-600 text-white border rounded hover:bg-green-700 shadow-sm hover:text-white transition-colors transition-colors">
                                    Recibido
                                </button>


                                <a href="cancelar_envio.php?id=<?= (int)$e['id'] ?>"
                                    class="col-span-2 flex items-center justify-center py-3 text-sm font-bold text-red-600 border border-red-200 rounded-lg bg-white dark:bg-slate-900">
                                    Cancelar Entrega
                                </a>
                            <?php else: ?>
                                <div class="col-span-2 text-center text-xs text-slate-500 dark:text-slate-400">
                                    Sin acciones
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
    <script>
        window.APP_BASE_URL = <?= json_encode(BASE_URL) ?>;
    </script>

    <?php include BASE_PATH . '/auxiliar/partials/auxiliar/modal_firma.php'; ?>
    <?php include BASE_PATH . '/auxiliar/partials/auxiliar/modal_foto.php'; ?>
    <?php include BASE_PATH . '/auxiliar/partials/auxiliar/help_card.php'; ?>
    <?php include __DIR__ . '/../../auxiliar/partials/auxiliar/modal_guia.php'; ?>
    <script src="<?= BASE_URL ?>/auxiliar/partials/js/guia.js?v=<?= time(); ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <script src="<?= BASE_URL ?>/auxiliar/partials/js/firma-foto.js?v=<?= time(); ?>"></script>

    <?php include BASE_PATH . '/auxiliar/partials/footer.php'; ?>