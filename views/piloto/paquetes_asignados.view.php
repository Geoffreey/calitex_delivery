<!-- PageHeading -->
<div class="mb-8">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">Lista de Tareas</h1>
            <div class="mt-2 flex items-center gap-2 text-slate-500 dark:text-slate-400">
                <span class="material-symbols-outlined text-sm">person</span>
                <p class="text-sm font-medium">Juan Pérez</p>
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
            Todas <span class="bg-primary/10 px-2 py-0.5 rounded-full text-xs">12</span>
        </button>
        <button class="flex items-center gap-2 border-b-2 border-transparent px-1 pb-4 text-sm font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
            Pendientes <span class="bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full text-xs">5</span>
        </button>
        <button class="flex items-center gap-2 border-b-2 border-transparent px-1 pb-4 text-sm font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
            En Tránsito <span class="bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full text-xs">3</span>
        </button>
        <button class="flex items-center gap-2 border-b-2 border-transparent px-1 pb-4 text-sm font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
            Completadas <span class="bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full text-xs">4</span>
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
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Estado</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                <!-- Row 1 -->
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a class="text-sm font-bold text-primary hover:underline" href="#">#GTX-9920</a>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-semibold text-slate-900 dark:text-white">Carlos Mendoza</div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <a class="inline-flex items-center justify-center size-8 rounded-full bg-primary/10 text-primary hover:bg-primary hover:text-white transition-all" href="tel:5550123">
                            <span class="material-symbols-outlined text-lg">call</span>
                        </a>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2 group cursor-pointer">
                            <span class="text-sm text-slate-600 dark:text-slate-400 truncate max-w-[200px]">Av. Siempre Viva 123</span>
                            <span class="material-symbols-outlined text-lg text-slate-400 group-hover:text-primary">map</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full text-xs font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                            En Camino
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button class="px-3 py-1.5 text-xs font-bold text-primary border border-primary rounded hover:bg-primary hover:text-white transition-colors">Ver</button>
                            <button class="px-3 py-1.5 text-xs font-bold bg-green-600 text-white rounded hover:bg-green-700 shadow-sm transition-colors">Recibido</button>
                            <button class="px-3 py-1.5 text-xs font-bold text-red-600 border border-red-200 rounded hover:bg-red-50 transition-colors">Cancelar</button>
                        </div>
                    </td>
                </tr>
                <!-- Row 2 -->
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a class="text-sm font-bold text-primary hover:underline" href="#">#GTX-8815</a>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-semibold text-slate-900 dark:text-white">María García</div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <a class="inline-flex items-center justify-center size-8 rounded-full bg-primary/10 text-primary hover:bg-primary hover:text-white transition-all" href="tel:5550124">
                            <span class="material-symbols-outlined text-lg">call</span>
                        </a>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2 group cursor-pointer">
                            <span class="text-sm text-slate-600 dark:text-slate-400 truncate max-w-[200px]">Calle Falsa 456</span>
                            <span class="material-symbols-outlined text-lg text-slate-400 group-hover:text-primary">map</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full text-xs font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                            Pendiente
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button class="px-3 py-1.5 text-xs font-bold bg-primary text-white rounded hover:bg-primary/90 shadow-md shadow-primary/20 transition-colors">Iniciar Entrega</button>
                            <button class="px-3 py-1.5 text-xs font-bold text-red-600 border border-red-200 rounded hover:bg-red-50 transition-colors">Cancelar</button>
                        </div>
                    </td>
                </tr>
                <!-- Row 3 -->
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a class="text-sm font-bold text-primary hover:underline" href="#">#GTX-7732</a>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-semibold text-slate-900 dark:text-white">Roberto Gomez</div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <a class="inline-flex items-center justify-center size-8 rounded-full bg-primary/10 text-primary hover:bg-primary hover:text-white transition-all" href="tel:5550125">
                            <span class="material-symbols-outlined text-lg">call</span>
                        </a>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2 group cursor-pointer">
                            <span class="text-sm text-slate-600 dark:text-slate-400 truncate max-w-[200px]">Diagonal 12 #4-50</span>
                            <span class="material-symbols-outlined text-lg text-slate-400 group-hover:text-primary">map</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full text-xs font-bold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                            Retrasado
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button class="px-3 py-1.5 text-xs font-bold text-primary border border-primary rounded hover:bg-primary hover:text-white transition-colors">Ver</button>
                            <button class="px-3 py-1.5 text-xs font-bold bg-primary text-white rounded hover:bg-primary/90 transition-colors">Reintentar</button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- Mobile Card View -->
    <div class="@[800px]:hidden space-y-4">
        <!-- Mobile Card 1 -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-start">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-bold text-primary uppercase">En Camino</span>
                        <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                        <span class="text-xs font-medium text-slate-500">#GTX-9920</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Carlos Mendoza</h3>
                </div>
                <a class="size-10 flex items-center justify-center rounded-full bg-primary/10 text-primary" href="tel:5550123">
                    <span class="material-symbols-outlined">call</span>
                </a>
            </div>
            <div class="p-4 space-y-3">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-slate-400 mt-0.5">location_on</span>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-slate-900 dark:text-slate-100">Dirección</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Av. Siempre Viva 123</p>
                    </div>
                    <button class="p-2 text-primary">
                        <span class="material-symbols-outlined" data-location="Bogotá">map</span>
                    </button>
                </div>
            </div>
            <div class="p-4 bg-slate-50 dark:bg-slate-800/40 grid grid-cols-2 gap-3">
                <button class="flex items-center justify-center py-3 text-sm font-bold text-primary border border-primary rounded-lg">
                    Ver
                </button>
                <button class="flex items-center justify-center py-3 text-sm font-bold bg-green-600 text-white rounded-lg shadow-sm">
                    Recibido
                </button>
                <button class="col-span-2 flex items-center justify-center py-3 text-sm font-bold text-red-600 border border-red-200 rounded-lg bg-white dark:bg-slate-900">
                    Cancelar Entrega
                </button>
            </div>
        </div>
        <!-- Mobile Card 2 -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-start">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-bold text-amber-600 uppercase">Pendiente</span>
                        <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                        <span class="text-xs font-medium text-slate-500">#GTX-8815</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">María García</h3>
                </div>
                <a class="size-10 flex items-center justify-center rounded-full bg-primary/10 text-primary" href="tel:5550124">
                    <span class="material-symbols-outlined">call</span>
                </a>
            </div>
            <div class="p-4 space-y-3">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-slate-400 mt-0.5">location_on</span>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-slate-900 dark:text-slate-100">Dirección</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Calle Falsa 456</p>
                    </div>
                    <button class="p-2 text-primary">
                        <span class="material-symbols-outlined" data-location="Bogotá">map</span>
                    </button>
                </div>
            </div>
            <div class="p-4 bg-slate-50 dark:bg-slate-800/40 space-y-3">
                <button class="w-full flex items-center justify-center py-4 text-base font-bold bg-primary text-white rounded-lg shadow-lg shadow-primary/20">
                    Iniciar Entrega
                </button>
                <div class="grid grid-cols-2 gap-3">
                    <button class="flex items-center justify-center py-3 text-sm font-bold text-primary border border-primary rounded-lg bg-white dark:bg-slate-900">
                        Ver
                    </button>
                    <button class="flex items-center justify-center py-3 text-sm font-bold text-red-600 border border-red-200 rounded-lg bg-white dark:bg-slate-900">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
        <!-- Mobile Card 3 -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-start">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-bold text-red-600 uppercase">Retrasado</span>
                        <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                        <span class="text-xs font-medium text-slate-500">#GTX-7732</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Roberto Gomez</h3>
                </div>
                <a class="size-10 flex items-center justify-center rounded-full bg-primary/10 text-primary" href="tel:5550125">
                    <span class="material-symbols-outlined">call</span>
                </a>
            </div>
            <div class="p-4 space-y-3">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-slate-400 mt-0.5">location_on</span>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-slate-900 dark:text-slate-100">Dirección</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Diagonal 12 #4-50</p>
                    </div>
                </div>
            </div>
            <div class="p-4 bg-slate-50 dark:bg-slate-800/40">
                <button class="w-full flex items-center justify-center py-3 text-sm font-bold bg-primary text-white rounded-lg">
                    Reintentar Entrega
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Footer / Pagination -->
<div class="mt-8 flex flex-col sm:flex-row items-center justify-between gap-4 py-6 border-t border-slate-200 dark:border-slate-800">
    <p class="text-sm text-slate-500 dark:text-slate-400">Mostrando <span class="font-bold text-slate-900 dark:text-white">3</span> de <span class="font-bold text-slate-900 dark:text-white">12</span> tareas asignadas</p>
    <div class="flex items-center gap-2">
        <button class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 disabled:opacity-50" disabled="">
            <span class="material-symbols-outlined">chevron_left</span>
        </button>
        <div class="flex gap-1">
            <button class="size-9 rounded-lg bg-primary text-white font-bold text-sm">1</button>
            <button class="size-9 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-sm font-medium">2</button>
            <button class="size-9 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-sm font-medium">3</button>
        </div>
        <button class="p-2 rounded-lg border border-slate-200 dark:border-slate-700">
            <span class="material-symbols-outlined">chevron_right</span>
        </button>
    </div>
</div>