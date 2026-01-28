<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'piloto') {
    header("Location: ../login.php");
    exit;
}

$piloto_id = $_SESSION['user_id'];

include 'partials/header.php';
//include 'partials/sidebar.php';

// Consulta resumen
$resumen = $pdo->prepare("
  SELECT estado_envio, COUNT(*) AS total
  FROM envios
  WHERE piloto_id = ?
  GROUP BY estado_envio
");
$resumen->execute([$piloto_id]);

$estados = [
    'pendiente'   => 0,
    'en_proceso'  => 0,
    'entregado'   => 0,
    'anulado'     => 0
];

foreach ($resumen->fetchAll() as $row) {
    $estados[$row['estado_envio']] = $row['total'];
}
?>

<!-- CONTENIDO PRINCIPAL -->
<div class="p-4 sm:p-8 space-y-6 lg:space-y-8">
    <section>
        <div class="mb-6">
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight leading-tight">Buenos dias despachador caliex</h2>
            <p class="text-slate-500 text-sm sm:text-base">Esto es lo que está pasando con tu flota hoy.</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            <div class="bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm hover:border-primary/30 transition-colors">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-blue-50 dark:bg-blue-900/30 p-2 rounded-lg text-primary">
                        <span class="material-symbols-outlined">package_2</span>
                    </div>
                    <span class="text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30 text-xs font-bold px-2 py-1 rounded">+5.2%</span>
                </div>
                <p class="text-slate-500 text-sm font-medium">Tareas en Proceso</p>
                <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white mt-1"><?= $estados['en_proceso'] ?></h3>
            </div>
            <div class="bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm hover:border-emerald-500/30 transition-colors">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-emerald-50 dark:bg-emerald-900/30 p-2 rounded-lg text-emerald-600">
                        <span class="material-symbols-outlined">task_alt</span>
                    </div>
                    <span class="text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30 text-xs font-bold px-2 py-1 rounded">+12.4%</span>
                </div>
                <p class="text-slate-500 text-sm font-medium">Tareas Finalizadas</p>
                <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white mt-1"><?= $estados['entregado'] ?></h3>
            </div>
            <div class="bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm border-l-4 border-l-accent-orange hover:border-accent-orange/30 transition-colors sm:col-span-2 lg:col-span-1">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-orange-50 dark:bg-orange-900/30 p-2 rounded-lg text-accent-orange">
                        <span class="material-symbols-outlined">error</span>
                    </div>
                    <span class="text-rose-600 bg-rose-50 dark:bg-rose-900/30 text-xs font-bold px-2 py-1 rounded">-2.1%</span>
                </div>
                <p class="text-slate-500 text-sm font-medium">Tareas Pendientes</p>
                <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white mt-1"><?= $estados['pendiente'] ?></h3>
            </div>
        </div>
    </section>
    <section class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Recent Packages</h3>
            <div class="flex gap-2">
                <button class="text-slate-500 hover:text-primary p-2">
                    <span class="material-symbols-outlined">filter_list</span>
                </button>
                <button class="text-slate-500 hover:text-primary p-2">
                    <span class="material-symbols-outlined">download</span>
                </button>
            </div>
        </div>
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left min-w-[700px] lg:min-w-0">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50">
                        <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Tracking ID</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Destination</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Estimated Delivery</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-6 py-4 font-bold text-primary whitespace-nowrap">#CTX-88291</td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col min-w-[150px]">
                                <span class="text-sm font-semibold text-slate-900 dark:text-white">Houston, TX</span>
                                <span class="text-xs text-slate-500 truncate">Regional Distribution Center</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400">
                                In Transit
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400 whitespace-nowrap">Today, 4:00 PM</td>
                        <td class="px-6 py-4 text-right">
                            <button class="text-slate-400 hover:text-primary p-1">
                                <span class="material-symbols-outlined">more_horiz</span>
                            </button>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-6 py-4 font-bold text-primary whitespace-nowrap">#CTX-99021</td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col min-w-[150px]">
                                <span class="text-sm font-semibold text-slate-900 dark:text-white">San Diego, CA</span>
                                <span class="text-xs text-slate-500 truncate">Corporate Office - Suite 400</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">
                                Pending Pickup
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400 whitespace-nowrap">Oct 24, 10:30 AM</td>
                        <td class="px-6 py-4 text-right">
                            <button class="text-slate-400 hover:text-primary p-1">
                                <span class="material-symbols-outlined">more_horiz</span>
                            </button>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-6 py-4 font-bold text-primary whitespace-nowrap">#CTX-77114</td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col min-w-[150px]">
                                <span class="text-sm font-semibold text-slate-900 dark:text-white">Austin, TX</span>
                                <span class="text-xs text-slate-500 truncate">Residential Front Porch</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">
                                Delivered
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400 whitespace-nowrap">Completed (2h ago)</td>
                        <td class="px-6 py-4 text-right">
                            <button class="text-slate-400 hover:text-primary p-1">
                                <span class="material-symbols-outlined">more_horiz</span>
                            </button>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-6 py-4 font-bold text-primary whitespace-nowrap">#CTX-11209</td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col min-w-[150px]">
                                <span class="text-sm font-semibold text-slate-900 dark:text-white">Phoenix, AZ</span>
                                <span class="text-xs text-slate-500 truncate">Terminal Hub 14</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400">
                                Delayed
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400 whitespace-nowrap">Oct 25, 12:00 PM</td>
                        <td class="px-6 py-4 text-right">
                            <button class="text-slate-400 hover:text-primary p-1">
                                <span class="material-symbols-outlined">more_horiz</span>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/20 text-center">
            <button class="text-primary text-sm font-bold hover:underline">View All Shipments</button>
        </div>
    </section>
    <section class="grid grid-cols-1 lg:grid-cols-2 gap-6 pb-8">
        <div class="bg-gradient-to-br from-primary to-blue-800 p-6 sm:p-8 rounded-xl text-white relative overflow-hidden group">
            <div class="relative z-10">
                <h4 class="text-xl font-bold mb-2">Need logistical support?</h4>
                <p class="text-blue-100 mb-6 text-sm max-w-sm">Our 24/7 dispatcher team is ready to assist with custom routing or large freight scheduling.</p>
                <button class="bg-white text-primary px-5 py-2 rounded-lg text-sm font-bold hover:bg-blue-50 transition-colors">Chat with Support</button>
            </div>
            <span class="material-symbols-outlined absolute -right-4 -bottom-4 text-[120px] opacity-10 rotate-12 group-hover:rotate-0 transition-transform">support_agent</span>
        </div>
        <div class="bg-white dark:bg-slate-900 p-6 sm:p-8 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
            <div>
                <h4 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Service Status</h4>
                <div class="flex items-center gap-2 mb-6">
                    <div class="size-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-sm font-medium text-emerald-600">All networks operational</span>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1 border border-slate-100 dark:border-slate-800 p-3 rounded-lg">
                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Ground</p>
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300">Fast (Avg 48h)</p>
                </div>
                <div class="flex-1 border border-slate-100 dark:border-slate-800 p-3 rounded-lg">
                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Express</p>
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300">On Time (99%)</p>
                </div>
            </div>
        </div>
    </section>
</div>


<?php include 'partials/footer.php'; ?>