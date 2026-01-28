<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'piloto') {
    header("Location: ../login.php");
    exit;
}

// Obtener lista de clientes
$clientes = $pdo->query("
  SELECT c.id AS cliente_id, u.nombre
  FROM clientes c
  JOIN users u ON u.id = c.user_id
  WHERE u.rol = 'cliente'
  ORDER BY u.nombre
")->fetchAll(PDO::FETCH_ASSOC);
$cliente_id = $_POST['cliente_id'] ?? null;

// Obtener direcciones del cliente seleccionado
$direcciones = [];
$direccion_map = [];

if ($cliente_id) {

    // obtener user_id del cliente seleccionado (clientes.id)
    $stmt = $pdo->prepare("SELECT user_id FROM clientes WHERE id = ?");
    $stmt->execute([$cliente_id]);
    $cliente_user_id = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
      SELECT d.*, z.numero AS zona, m.nombre AS municipio, dp.nombre AS departamento
      FROM direcciones d
      JOIN zona z ON d.zona_id = z.id
      JOIN municipios m ON d.municipio_id = m.id
      JOIN departamentos dp ON d.departamento_id = dp.id
      WHERE d.cliente_id = ?
    ");
    $stmt->execute([$cliente_id]); // 👈 aquí va el user_id
    $direcciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($direcciones as $d) {
        $direccion_map[$d['id']] = "{$d['calle']} #{$d['numero']}, Zona {$d['zona']}, {$d['municipio']}, {$d['departamento']}";
    }
}

// Obtener paquetes disponibles
$paquetes = $pdo->query("SELECT id, nombre, tamano, peso, tarifa FROM paquetes ORDER BY nombre")->fetchAll();
$guia_script = "";

$es_crear_envio =
  ($_SERVER['REQUEST_METHOD'] === 'POST')
  && !empty($_POST['cliente_id'])
  && !empty($_POST['direccion_destino_id'])
  && !empty($_POST['nombre_destinatario'])
  && !empty($_POST['telefono_destinatario']);

if ($es_crear_envio) {
    $direccion_destino_id = $_POST['direccion_destino_id'];
    $nombre_destinatario  = $_POST['nombre_destinatario'];
    $telefono_destinatario = $_POST['telefono_destinatario'];
    $descripcion = $_POST['descripcion'] ?? null;
    $paquete_ids = $_POST['paquete_ids'] ?? [];
    $pago_envio = $_POST['pago_envio'] ?? 'cliente';
    $piloto_id = $_SESSION['user_id'];

    $direccion_origen_id = $direcciones[0]['id'] ?? null;
    $direccion_texto = $direccion_map[$direccion_destino_id] ?? '';

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO envios (cliente_id, piloto_id, direccion_origen_id, direccion_destino_id, nombre_destinatario, telefono_destinatario, descripcion, pago_envio) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$cliente_id, $piloto_id, $direccion_origen_id, $direccion_destino_id, $nombre_destinatario, $telefono_destinatario, $descripcion, $pago_envio]);

        $envio_id = $pdo->lastInsertId();

        $monto_cobros = $_POST['monto_cobros'] ?? [];
        $stmt = $pdo->prepare("INSERT INTO envios_paquetes (envio_id, paquete_id, monto_cobro) VALUES (?, ?, ?)");
        $total_cobro = 0;

        foreach ($paquete_ids as $paquete_id => $cantidad) {
            $monto = isset($monto_cobros[$paquete_id]) ? floatval($monto_cobros[$paquete_id]) : 0.00;
            for ($i = 0; $i < (int)$cantidad; $i++) {
                $stmt->execute([$envio_id, $paquete_id, $monto]);
            }
            $total_cobro += ((int)$cantidad) * $monto;
        }

        $tarifa_envio_total = 0;
        foreach ($paquetes as $p) {
            $id_paquete = $p['id'];
            $cantidad = isset($paquete_ids[$id_paquete]) ? (int)$paquete_ids[$id_paquete] : 0;
            $tarifa_envio_total += $cantidad * floatval($p['tarifa']);
        }

        if ($pago_envio === 'destinatario') {
            $total_cobro += $tarifa_envio_total;
        }

        $pdo->commit();

        $guia_script = "<script>
  document.addEventListener('DOMContentLoaded', function() {
    const modal = new bootstrap.Modal(document.getElementById('modalGuia'));
    document.getElementById('modalGuiaId').textContent = '$envio_id';
    document.getElementById('modalGuiaNombre').textContent = '$nombre_destinatario';
    document.getElementById('modalGuiaTelefono').textContent = '$telefono_destinatario';
    document.getElementById('modalGuiaDireccion').textContent = `{$direccion_texto}`;
    document.getElementById('modalGuiaDescripcion').textContent = `$descripcion`;
    document.getElementById('modalGuiaPagoEnvio').textContent = '" . ($pago_envio === 'destinatario' ? 'Cobro contra entrega' : 'Cobro a mi cuenta') . "';
    document.getElementById('modalGuiaCobro').textContent = 'Q" . number_format($total_cobro, 2) . "';
    setTimeout(() => { modal.show(); }, 300);
  });
</script>";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<div class='alert alert-danger'>Error al crear envío: " . $e->getMessage() . "</div>";
    }
}

include 'partials/header.php';
//include 'partials/sidebar.php';
echo $guia_script;
?>

<!-- Page Heading -->
<div class="mb-8">
    <h1 class="text-[#0d121b] dark:text-white text-3xl md:text-4xl font-black leading-tight tracking-[-0.033em]">Crear Nuevo Envio</h1>
    <p class="text-[#4c669a] dark:text-gray-400 text-base font-normal mt-2">Genere una guía digital y programe una recolección.</p>
</div>
<form method="POST" id="form-envio" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    <!-- Left Column: Customer & Recipient -->
    <div class="lg:col-span-7 flex flex-col gap-6">
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
                            <input checked="" class="hidden peer" name="payment" type="radio" value="sender" />
                            <div class="flex items-center justify-center gap-2 py-3 px-4 rounded-lg border-2 border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 peer-checked:border-primary peer-checked:bg-primary/5 transition-all">
                                <span class="material-symbols-outlined text-gray-400 peer-checked:text-primary">upload</span>
                                <span class="font-medium text-gray-700 dark:text-gray-300 peer-checked:text-primary">Sender</span>
                            </div>
                        </label>
                        <label class="flex-1 cursor-pointer group">
                            <input class="hidden peer" name="payment" type="radio" value="recipient" />
                            <div class="flex items-center justify-center gap-2 py-3 px-4 rounded-lg border-2 border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 peer-checked:border-primary peer-checked:bg-primary/5 transition-all">
                                <span class="material-symbols-outlined text-gray-400 peer-checked:text-primary">download</span>
                                <span class="font-medium text-gray-700 dark:text-gray-300 peer-checked:text-primary">Recipient</span>
                            </div>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#0d121b] dark:text-gray-200 mb-2">Observaciones especiales</label>
                    <textarea class="w-full px-4 py-3 rounded-lg border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 focus:ring-primary focus:border-primary text-sm resize-none" placeholder="Fragile items, specific delivery hours, gate codes..." rows="4"></textarea>
                </div>
            </div>
        </section>
    </div>
    <!-- Right Column: Package Details -->
    <div class="lg:col-span-5 flex flex-col gap-6">
        <section class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-[#e7ebf3] dark:border-gray-800 p-6 h-fit">
            <div class="flex items-center justify-between mb-6 border-b border-gray-100 dark:border-gray-800 pb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">inventory_2</span>
                    <h2 class="text-[#0d121b] dark:text-white text-xl font-bold">Detalles del Paquete</h2>
                </div>
                <span class="bg-primary/10 text-primary text-xs font-bold px-2.5 py-1 rounded-full">3 Items</span>
            </div>
            <div class="space-y-4 mb-6">
                <!-- Dynamic Item Row 1 -->
                <div class="p-4 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30 flex flex-col gap-4">
                    <div class="flex justify-between items-start">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Item #1</p>
                        <button class="text-gray-400 hover:text-red-500 transition-colors" type="button">
                            <span class="material-symbols-outlined text-[20px]">delete</span>
                        </button>
                    </div>
                    <div class="grid grid-cols-12 gap-3">
                        <div class="col-span-12">
                            <select class="custom-select-arrow w-full py-2 px-3 rounded-lg border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-primary">
                                <option>Standard Box (Medium)</option>
                                <option>Document Envelope</option>
                                <option>Large Crate</option>
                                <option>Pallet</option>
                            </select>
                        </div>
                        <div class="col-span-6">
                            <label class="text-[11px] font-bold text-gray-500 block mb-1">QTY</label>
                            <input class="w-full py-2 px-3 rounded-lg border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-primary" type="number" value="1" />
                        </div>
                        <div class="col-span-6">
                            <label class="text-[11px] font-bold text-gray-500 block mb-1">Weight (kg)</label>
                            <input class="w-full py-2 px-3 rounded-lg border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-primary" type="number" value="2.5" />
                        </div>
                    </div>
                </div>
                <!-- Dynamic Item Row 2 (Minimal) -->
                <div class="p-4 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30 flex flex-col gap-4">
                    <div class="flex justify-between items-start">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Item #2</p>
                        <button class="text-gray-400 hover:text-red-500 transition-colors" type="button">
                            <span class="material-symbols-outlined text-[20px]">delete</span>
                        </button>
                    </div>
                    <div class="grid grid-cols-12 gap-3">
                        <div class="col-span-12">
                            <select class="custom-select-arrow w-full py-2 px-3 rounded-lg border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-primary">
                                <option>Document Envelope</option>
                                <option>Standard Box</option>
                            </select>
                        </div>
                        <div class="col-span-6">
                            <input class="w-full py-2 px-3 rounded-lg border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-primary" placeholder="Qty" type="number" value="3" />
                        </div>
                        <div class="col-span-6">
                            <input class="w-full py-2 px-3 rounded-lg border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-primary" placeholder="Weight" type="number" value="0.5" />
                        </div>
                    </div>
                </div>
            </div>
            <button class="w-full py-3 px-4 rounded-lg border-2 border-dashed border-accent/40 text-accent font-bold hover:bg-accent/5 transition-all flex items-center justify-center gap-2" type="button">
                <span class="material-symbols-outlined">add</span>
                Add New Package
            </button>
            <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800 space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Total Weight:</span>
                    <span class="font-bold text-[#0d121b] dark:text-white">4.0 kg</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Est. Shipping Fee:</span>
                    <span class="font-bold text-[#0d121b] dark:text-white">$12.50</span>
                </div>
                <div class="flex justify-between text-lg pt-2">
                    <span class="font-bold text-[#0d121b] dark:text-white">Total Amount:</span>
                    <span class="font-black text-primary">$12.50</span>
                </div>
            </div>
            <div class="mt-8 grid grid-cols-1 gap-3">
                <button class="w-full bg-primary text-white py-4 rounded-xl font-bold text-lg shadow-lg shadow-primary/20 hover:shadow-primary/40 hover:-translate-y-0.5 active:translate-y-0 transition-all flex items-center justify-center gap-2" type="submit">
                    <span class="material-symbols-outlined">local_shipping</span>
                    Crear Guia
                </button>
                <button class="w-full bg-transparent text-gray-500 dark:text-gray-400 py-3 rounded-lg font-semibold text-sm hover:text-gray-700 dark:hover:text-gray-200 transition-colors" type="button">
                    Guardar como borrador
                </button>
            </div>
        </section>
        <!-- Help Card -->
        <div class="bg-primary/5 dark:bg-primary/10 rounded-xl p-5 border border-primary/20 flex gap-4">
            <span class="material-symbols-outlined text-primary">info</span>
            <div>
                <p class="text-sm font-bold text-primary mb-1">¿Necesita ayuda?</p>
                <p class="text-xs text-[#4c669a] dark:text-gray-400 leading-relaxed">Asegúrese de que todos los pesos sean precisos para evitar cargos de procesamiento adicionales en el almacén.</p>
            </div>
        </div>
    </div>
</form>