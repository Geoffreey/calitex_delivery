<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include 'partials/headerp.php';
////include 'partials/sidebar.php';

// Obtener pilotos activos
// ============================
// Paginación (server-side)
// ============================
$limit = 4; // cantidad por página
$page  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Contar total de pilotos activos (para calcular páginas)
$stmtTotal = $pdo->query("
  SELECT COUNT(*) AS total
  FROM pilotos p
  JOIN users u ON p.user_id = u.id
  WHERE u.estado = 1
");
$totalRows = (int)$stmtTotal->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $limit));

// Ajustar si piden una página mayor a la última
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

// ============================
// Obtener pilotos activos (solo los de la página actual)
// ============================
// Nota: quité el GROUP BY p.user_id porque puede dar resultados raros.
// Si tu tabla tiene duplicados por diseño, lo arreglamos luego con una subconsulta.
$stmt = $pdo->prepare("
  SELECT p.id, u.nombre, u.apellido, u.telefono, u.email, f.tipo AS flota
  FROM pilotos p
  JOIN users u ON p.user_id = u.id
  LEFT JOIN flotas f ON p.flota_id = f.id
  WHERE u.estado = 1
  ORDER BY u.nombre ASC
  LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$pilotos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
        <div class="flex flex-col gap-1">
            <h2 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">Administracion de pilotos</h2>
            <p class="text-slate-500 dark:text-slate-400">Centro de coordinación de flotas y estado en tiempo real.</p>
        </div>
        <button class="flex items-center justify-center gap-2 bg-primary text-white px-6 py-2.5 rounded-lg font-bold text-sm transition-transform active:scale-95">
            <span class="material-symbols-outlined">person_add</span>
            <a href="crear_piloto.php" class="text-white">Registrar nuevo piloto</a>
        </button>
    </div>
    <!-- Filter Chips Section -->
    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 mb-6 shadow-sm border border-slate-200 dark:border-slate-800">
        <div class="flex flex-wrap items-center gap-3">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 mr-2">Filtrar por:</span>
            <button class="flex items-center gap-2 px-4 py-1.5 rounded-full bg-primary/10 text-primary text-sm font-semibold border border-primary/20">
                Todos los vehiculos
                <span class="material-symbols-outlined text-lg">keyboard_arrow_down</span>
            </button>
            <button class="flex items-center gap-2 px-4 py-1.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-sm font-semibold hover:bg-slate-200">
                <span class="material-symbols-outlined text-lg">two_wheeler</span>
                Moto
            </button>
            <button class="flex items-center gap-2 px-4 py-1.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-sm font-semibold hover:bg-slate-200">
                <span class="material-symbols-outlined text-lg">airport_shuttle</span>
                Van
            </button>
            <div class="h-6 w-px bg-slate-200 dark:bg-slate-700 mx-2"></div>
            <button class="flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-50 text-emerald-700 text-sm font-semibold border border-emerald-100 hover:bg-emerald-100">
                Disponible
            </button>
            <button class="flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-50 text-blue-700 text-sm font-semibold border border-blue-100 hover:bg-blue-100">
                Contra entrega
            </button>
        </div>
    </div>
    <!-- Desktop Table Layout -->
    <div class="desktop-only overflow-hidden bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                    <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">Detalle de piloto</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">Vehiculo</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">Telefono</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">Status</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                <?php foreach ($pilotos as $row): ?>
                    <!-- Row 1 -->
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="size-10 rounded-full bg-slate-200 overflow-hidden shrink-0" data-alt="Professional pilot headshot">
                                    <img alt="John Doe" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBiA14UIVIMgSTmbtxb71Hv1DQvpgyotDMwmht9mx9ow1E4mdNaibD3NamQnGmRcCNl6_HjAchplK8CAYYyenZmLM6L2RrcqlacEwkQBbnG3odipZ80I36oCLBz1eD0_GdQJlb5I5rWvWAP5duAyJAj3vRfYUE8wolEVZ9JpZD-Stxn1u0uyyvATFW9uTmTTvjpEqGcuVu5WWRAQFY0g22UHXWSgsO0MApFO-rkxEOrKgIkXBZsq_JDS-TC2oEV4Gnj1HJCytle-EEo" />
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900 dark:text-white"><?= htmlspecialchars($row['nombre']) ?> <?= htmlspecialchars($row['apellido']) ?></p>
                                    <p class="text-xs text-slate-500">Joined Oct 2023</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
                                <span class="material-symbols-outlined text-slate-400">two_wheeler</span>
                                <span class="text-sm font-medium"><?= htmlspecialchars($row['flota'] ?? 'No asignada') ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm">
                                <p class="text-slate-900 dark:text-slate-200 font-medium"><?= htmlspecialchars($row['telefono']) ?></p>
                                <p class="text-xs font-mono text-slate-500"><?= htmlspecialchars($row['flota'] ?? 'No asignada') ?></p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold">
                                <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                Available
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button class="p-2 hover:bg-primary/10 hover:text-primary rounded-lg text-slate-400 transition-colors" title="View Profile">
                                    <a href="ruta_asignada_envios.php" class="material-symbols-outlined">visibility</a>
                                </button>
                                <button class="p-2 hover:bg-primary/10 hover:text-primary rounded-lg text-slate-400 transition-colors" title="Assign Task">
                                    <a href="ruta_asignada_envios.php" class="material-symbols-outlined">assignment_add</a>
                                </button>
                                <button class="p-2 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg text-slate-400 transition-colors" title="Edit">
                                    <a href="editar_piloto.php?id=<?= $row['id'] ?>" class="material-symbols-outlined">edit</a>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- Mobile Grid Layout -->
    <div class="mobile-only mobile-card-grid grid grid-cols-1 gap-4">
        <?php foreach ($pilotos as $p): ?>
            <!-- Mobile Card 1 -->
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="size-12 rounded-full bg-slate-200 border-2 border-emerald-500 p-0.5" data-alt="Pilot profile headshot">
                            <img alt="John Doe" class="w-full h-full rounded-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuA1CyJQThcAbHU6FiZ9pyF9zQevP2v7pg22OngGxpfYdHyzS2v8jBABKC6qaTlCZj6uENNs680iDFBNbH0mkFhKmxzrBm_FaozkghliVg2qHOoYOJ_tPJK9XkJ0yl40E4Pu2npmS0o3M010lRGam5Ez5vxyyliPm_-9GClqNbzaEATSf3kTLufnDublCi4dQEJ7bvo3heHxWqGmEM88zOx8uQvJT0Zkrr5IYk6G4YbuJ1VpIMwMFbdFKr2Gn3AeCj1kqdza8iyg1ITs" />
                        </div>
                        <div>
                            <p class="font-bold text-slate-900 dark:text-white"> <?= htmlspecialchars($p['nombre']) ?> <?= htmlspecialchars($p['apellido']) ?></p>
                            <span class="text-xs text-emerald-600 font-bold">Available</span>
                        </div>
                    </div>
                    <button class="material-symbols-outlined text-slate-400">more_vert</button>
                </div>
                <div class="grid grid-cols-2 gap-y-3 mb-4 text-sm">
                    <div>
                        <p class="text-xs text-slate-500 mb-0.5 uppercase tracking-wide">Vehicle</p>
                        <p class="font-medium flex items-center gap-1.5"><span class="material-symbols-outlined text-base">two_wheeler</span> <?= htmlspecialchars($p['flota'] ?? 'No asignada') ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 mb-0.5 uppercase tracking-wide">License</p>
                        <p class="font-mono font-medium">CAL-1024</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-xs text-slate-500 mb-0.5 uppercase tracking-wide">Contact</p>
                        <p class="font-medium"><?= htmlspecialchars($p['telefono']) ?></p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button class="flex-1 bg-primary text-white py-2 rounded-lg text-sm font-bold">Asignar tarea</button>
                    <button class="flex-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 py-2 rounded-lg text-sm font-bold">Ver perfil</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    // ============================
    // Helper para construir URLs conservando parámetros existentes
    // ============================
    function buildPageUrl($pageNum)
    {
        $params = $_GET;
        $params['page'] = $pageNum;
        return '?' . http_build_query($params);
    }
    ?>

    <!-- Pagination -->
    <div class="mt-6 px-4">
        <div class="flex items-center justify-between gap-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">

            <!-- Info -->
            <div class="text-sm text-slate-600 dark:text-slate-300">
                Mostrando
                <span class="font-bold text-slate-900 dark:text-white">
                    <?= $totalRows ? ($offset + 1) : 0 ?>
                </span>
                -
                <span class="font-bold text-slate-900 dark:text-white">
                    <?= min($offset + $limit, $totalRows) ?>
                </span>
                de
                <span class="font-bold text-slate-900 dark:text-white">
                    <?= $totalRows ?>
                </span>
            </div>

            <!-- Buttons -->
            <div class="flex items-center gap-2">
                <!-- Prev -->
                <a
                    href="<?= htmlspecialchars(buildPageUrl(max(1, $page - 1))) ?>"
                    class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 transition
        <?= ($page <= 1) ? 'pointer-events-none opacity-50' : '' ?>">
                    <span class="material-symbols-outlined align-middle text-[18px]">chevron_left</span>
                </a>

                <!-- Page indicator -->
                <div class="px-3 py-2 rounded-lg bg-primary/10 text-primary font-bold text-sm border border-primary/20">
                    Página <?= $page ?> / <?= $totalPages ?>
                </div>

                <!-- Next -->
                <a
                    href="<?= htmlspecialchars(buildPageUrl(min($totalPages, $page + 1))) ?>"
                    class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 transition
        <?= ($page >= $totalPages) ? 'pointer-events-none opacity-50' : '' ?>">
                    <span class="material-symbols-outlined align-middle text-[18px]">chevron_right</span>
                </a>
            </div>

        </div>
    </div>
</div>
<?php include 'partials/footerp.php'; ?>