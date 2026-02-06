<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$auxiliar_id = $_SESSION['user_id'];

include 'partials/header.php';
//include 'partials/sidebar.php';

// Consulta resumen (solo tareas del auxiliar logueado)
$estados = [
    'pendiente'   => 0,
    'en_proceso'  => 0,
    'recibido' => 0,
    'cancelado'   => 0
];

$resumen = $pdo->query("
  SELECT
    SUM(CASE WHEN estado_envio = 'pendiente' AND (piloto_id IS NOT NULL OR ruta_id IS NOT NULL) THEN 1 ELSE 0 END) AS en_proceso,
    SUM(CASE WHEN estado_envio = 'recibido' THEN 1 ELSE 0 END) AS recibido,
    SUM(CASE WHEN estado_envio = 'pendiente' AND piloto_id IS NULL AND ruta_id IS NULL THEN 1 ELSE 0 END) AS pendientes,
    SUM(CASE WHEN estado_envio = 'cancelado' THEN 1 ELSE 0 END) AS cancelados
  FROM envios
")->fetch(PDO::FETCH_ASSOC);

$estados['en_proceso']  = (int)($resumen['en_proceso'] ?? 0);
$estados['recibido'] = (int)($resumen['recibido'] ?? 0);
$estados['pendiente']   = (int)($resumen['pendientes'] ?? 0);
$estados['cancelado']   = (int)($resumen['cancelados'] ?? 0);

// Tareas recientes (últimas 10)
$recientes = $pdo->query("
  SELECT
    e.id,
    e.nombre_destinatario,
    e.telefono_destinatario,
    e.estado_envio,
    e.created_at,
    e.fecha_recibido,
    e.piloto_id,
    u.nombre AS piloto_nombre,
    u.apellido AS piloto_apellido
  FROM envios e
  LEFT JOIN pilotos p ON p.id = e.piloto_id
  LEFT JOIN users u ON u.id = p.user_id
  ORDER BY e.id DESC
  LIMIT 10
");
$rows_recientes = $recientes->fetchAll(PDO::FETCH_ASSOC);

function badge_estado_envio($estado)
{
    switch ($estado) {
        case 'pendiente':
            return ['bg' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400', 'txt' => 'Pendiente'];
        case 'recibido':
            return ['bg' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400', 'txt' => 'Recibido'];
        case 'cancelado':
            return ['bg' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400', 'txt' => 'Cancelado'];
        default:
            return ['bg' => 'bg-slate-100 text-slate-700 dark:bg-slate-800/50 dark:text-slate-300', 'txt' => ucfirst((string)$estado)];
    }
}
?>

<!-- CONTENIDO PRINCIPAL -->
<div class="p-4 sm:p-8 space-y-6 lg:space-y-8">
    <section>
        <div class="mb-6">
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight leading-tight">Buenos dias auxiliar caliex</h2>
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
                <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white mt-1"><?= $estados['recibido'] ?></h3>
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
            <div class="bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm border-l-4 border-l-accent-red hover:border-accent-red/30 transition-colors sm:col-span-2 lg:col-span-1">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-red-50 dark:bg-red-900/30 p-2 rounded-lg text-accent-red">
                        <span class="material-symbols-outlined">error</span>
                    </div>
                    <span class="text-rose-600 bg-rose-50 dark:bg-rose-900/30 text-xs font-bold px-2 py-1 rounded">-2.1%</span>
                </div>
                <p class="text-slate-500 text-sm font-medium">Tareas Canceladas</p>
                <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white mt-1"><?= $estados['cancelado'] ?></h3>
            </div>
        </div>
    </section>
    <section class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tareas Recientes</h3>
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
                        <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Piloto</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php if (empty($rows_recientes)): ?>
                        <tr>
                            <td class="px-6 py-6 text-sm text-slate-500 dark:text-slate-400" colspan="6">
                                No hay tareas recientes todavía.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows_recientes as $r):
                            $b = badge_estado_envio($r['estado_envio'] ?? 'pendiente');

                            $tracking = '#ENV-' . str_pad((string)$r['id'], 6, '0', STR_PAD_LEFT);

                            $destTitle = $r['nombre_destinatario'] ?: 'Destino';
                            $destSub   = $r['telefono_destinatario'] ?: 'Sin teléfono';

                            $piloto = trim(($r['piloto_nombre'] ?? '') . ' ' . ($r['piloto_apellido'] ?? ''));
                            if ($piloto === '') $piloto = 'No asignado';

                            // Fecha visible: si está recibido, usa fecha_recibido; si no, created_at
                            $fecha = $r['fecha_recibido'] ?: $r['created_at'];
                            $fechaTxt = $fecha ? date('d/m/Y h:i A', strtotime($fecha)) : '-';
                        ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="px-6 py-4 font-bold text-primary whitespace-nowrap"><?= htmlspecialchars($tracking) ?></td>

                                <td class="px-6 py-4">
                                    <div class="flex flex-col min-w-[150px]">
                                        <span class="text-sm font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($destTitle) ?></span>
                                        <span class="text-xs text-slate-500 truncate"><?= htmlspecialchars($destSub) ?></span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                    <?= htmlspecialchars($piloto) ?>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold <?= $b['bg'] ?>">
                                        <?= htmlspecialchars($b['txt']) ?>
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                    <?= htmlspecialchars($fechaTxt) ?>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <button type="button" class="px-3 py-1.5 text-xs font-bold text-primary border border-primary rounded hover:bg-primary hover:text-white transition-colors btnVerGuia" data-envio-id="<?= (int)$r['id'] ?>">
                                        Ver
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
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

<script>
  window.APP_BASE_URL = <?= json_encode(BASE_URL) ?>;
</script>

   <?php include BASE_PATH . '/admin/partials/admin/modal_firma.php'; ?>
    <?php include BASE_PATH . '/admin/partials/admin/modal_foto.php'; ?>
    <?php include BASE_PATH . '/admin/partials/admin/help_card.php'; ?>
    <?php include __DIR__ . '/../../admin/partials/admin/modal_guia.php'; ?>
    <script src="<?= BASE_URL ?>/admin/partials/js/guia.js?v=<?= time(); ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <script src="<?= BASE_URL ?>/admin/partials/js/firma-foto.js?v=<?= time(); ?>"></script>

<?php include 'partials/footer.php'; ?>