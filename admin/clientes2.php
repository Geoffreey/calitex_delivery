<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include 'partials/header.php';
//include 'partials/sidebar.php';

// Obtener clientes con INNER JOIN a users
$clientes = $pdo->query("SELECT c.id, u.nombre, u.apellido, u.telefono, u.email
                         FROM clientes c 
                         INNER JOIN users u ON c.user_id = u.id
                         WHERE u.estado = 1
                         ORDER BY u.nombre ASC");
?>

<div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
    <div class="flex flex-col gap-1">
        <h2 class="text-3xl font-black tracking-tight">Administracion de cleintes</h2>
        <p class="text-gray-500 dark:text-gray-400">Ver y administrar todas las cuentas activas y su historial de entregas.</p>
    </div>
    <button class="flex items-center justify-center gap-2 bg-primary text-white px-6 py-2.5 rounded-lg font-bold shadow-lg shadow-primary/20 hover:bg-primary/90 transition-all active:scale-95">
        <span class="material-symbols-outlined">person_add</span>
        <span>Agregar nuevo cliente</span>
    </button>
</div>
<!-- Search and Filter Section -->
<div class="bg-white dark:bg-background-dark p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm mb-6">
    <div class="flex flex-col md:flex-row gap-4">
        <div class="flex-1 relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                <span class="material-symbols-outlined">manage_search</span>
            </div>
            <input class="block w-full pl-11 pr-4 py-3 border-gray-200 dark:border-gray-700 bg-transparent rounded-lg focus:ring-primary focus:border-primary text-sm" placeholder="Search by name, email, or phone number..." type="text" />
        </div>
        <div class="flex gap-2">
            <button class="flex items-center gap-2 px-4 py-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-sm font-medium">
                <span class="material-symbols-outlined text-[20px]">filter_list</span>
                <span>Filters</span>
            </button>
            <button class="flex items-center gap-2 px-4 py-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-sm font-medium">
                <span class="material-symbols-outlined text-[20px]">download</span>
                <span>Export</span>
            </button>
        </div>
    </div>
</div>
<!-- Responsive Container -->
<div class="space-y-4">
    <!-- Desktop Table View (Hidden on mobile) -->
    <div class="hidden md:block overflow-hidden bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800">
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Nombre</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Contact Details</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 text-center">Total Shipments</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                 <?php foreach ($clientes as $c): ?>
                    <!-- Table Row 1 -->
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-full bg-cover bg-center border border-gray-200" data-alt="Portrait of a woman with curly hair" style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuCtURrTqPVwKnbn-MVfVMmCoc65RUyqq9ZKXomMkVPcR5ckjBd6Z4gtxlnByLswxNPJZSXIyQReXQMOEneblqICW0l5Y6Ri7kydV4fuKdGInKO6ABDgbXqiS74PJfgKzE_jwJ5S6lji3Fsml0UqIuKzVC2QraeYkzxKWNtQDl77bfRk_6-fYKvz8MeR7zj5wmLUx8cWyEtUNULuwPwkOC_fBVnFS0ZG80uPQrsLszbTrrlR5_0WalRzbW2mIR3Wz93kif2XyLV_EXAd");'></div>
                            <div>
                                <p class="font-semibold text-sm"><?= htmlspecialchars($c['nombre']) ?> <?= htmlspecialchars($c['apellido']) ?></p>
                                <p class="text-xs text-gray-400">ID: #CT-8291</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                <span class="material-symbols-outlined text-[16px]">mail</span>
                                <span><?= htmlspecialchars($c['email']) ?></span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                <span class="material-symbols-outlined text-[16px]">call</span>
                                <span><?= htmlspecialchars($c['telefono']) ?></span>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary">
                            128 Units
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <button class="p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors" title="View Details">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                            <button class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Edit Profile">
                                <a href="editar_cliente.php?id=<?= $c['id'] ?>" class="material-symbols-outlined">edit</a>
                            </button>
                            <button  class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete Record">
                                <a href="eliminar_cliente.php?id=<?= $c['id'] ?>" onclick="return confirm('¿Eliminar cliente?')" class="material-symbols-outlined">delete</a>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- Vista de tarjeta móvil (visible solo en pantallas pequeñas) -->
    <div class="md:hidden grid grid-cols-1 gap-4">
        <!-- Mobile Card 1 -->
        <div class="bg-white dark:bg-background-dark p-5 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <div class="flex items-center gap-3">
                    <div class="size-12 rounded-lg bg-cover bg-center border border-gray-100" data-alt="Portrait of a woman with curly hair" style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuDBKplPHAEfL7Bd6fiI0M-g58meD0hpHj2dlicxvREA_x0tOKV6ad4ZQ9x1PluFmeGNf9_9orKGekH7golnUuNxn00lnl0WzUyIMcySzg6aTSZcBNbp9cYLiEgbLKfJgUpSE3EtRF3lf92Qp6L_GWzIIyTZpV9kY6iaQ2ZUYWcI0qufaIgmFapW2-luC11PMOXgRsrPzd3UrnALiUB-QBX2Wa4l22_0GfMjXULkL3vgzMQ_umGT4UF4cdwmYLhyj3hkDv63F8M-Sa23");'></div>
                    <div>
                        <h3 class="font-bold text-base"><?= htmlspecialchars($c['nombre']) ?> <?= htmlspecialchars($c['apellido']) ?></h3>
                        <p class="text-xs text-gray-400">ID: #CT-8291</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-primary/10 text-primary">
                    128 Ships
                </span>
            </div>
            <div class="space-y-2 py-3 border-t border-gray-50 dark:border-gray-800">
                <div class="flex items-center gap-3 text-sm text-gray-500">
                    <span class="material-symbols-outlined text-lg">mail</span>
                    <span><?= htmlspecialchars($c['email']) ?></span>
                </div>
                <div class="flex items-center gap-3 text-sm text-gray-500">
                    <span class="material-symbols-outlined text-lg">call</span>
                    <span><?= htmlspecialchars($c['telefono']) ?></span>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2 mt-4 pt-4 border-t border-gray-50 dark:border-gray-800">
                <button class="flex flex-col items-center justify-center gap-1 p-2 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 transition-colors">
                    <span class="material-symbols-outlined">visibility</span>
                    <span class="text-[10px] font-bold">VIEW</span>
                </button>
                <button class="flex flex-col items-center justify-center gap-1 p-2 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors">
                    <a href="editar_cliente.php?id=<?= $c['id'] ?>" class="material-symbols-outlined">edit</a>
                    <span class="text-[10px] font-bold">EDIT</span>
                </button>
                <button class="flex flex-col items-center justify-center gap-1 p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                    <a href="eliminar_cliente.php?id=<?= $c['id'] ?>" class="material-symbols-outlined">delete</a>
                    <span class="text-[10px] font-bold">DELETE</span>
                </button>
            </div>
        </div>
    </div>
    <!-- Paginación -->
    <div class="flex items-center justify-between pt-6">
        <p class="text-sm text-gray-500 hidden sm:block">Showing <span class="font-semibold text-gray-900 dark:text-gray-200">1</span> to <span class="font-semibold text-gray-900 dark:text-gray-200">10</span> of <span class="font-semibold text-gray-900 dark:text-gray-200">128</span> customers</p>
        <div class="flex items-center gap-1 w-full sm:w-auto justify-between sm:justify-end">
            <button class="flex items-center justify-center size-10 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <span class="material-symbols-outlined">chevron_left</span>
            </button>
            <div class="flex items-center gap-1">
                <button class="size-10 rounded-lg bg-primary text-white font-bold text-sm">1</button>
                <button class="size-10 rounded-lg border border-transparent hover:border-gray-200 dark:hover:border-gray-800 font-medium text-sm transition-all">2</button>
                <button class="size-10 rounded-lg border border-transparent hover:border-gray-200 dark:hover:border-gray-800 font-medium text-sm transition-all">3</button>
                <span class="px-2 text-gray-400">...</span>
                <button class="size-10 rounded-lg border border-transparent hover:border-gray-200 dark:hover:border-gray-800 font-medium text-sm transition-all">13</button>
            </div>
            <button class="flex items-center justify-center size-10 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                <span class="material-symbols-outlined">chevron_right</span>
            </button>
        </div>
    </div>
</div>


<?php include 'partials/footer.php'; ?>