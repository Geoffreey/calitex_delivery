<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Obtener flotas activas
$flotas = $pdo->query("SELECT id, placa FROM flotas ORDER BY placa ASC")->fetchAll();
$flota_id = intval($_POST['flota_id'] ?? 0);

// ============================
// Datos para crear flota (opcional, solo si no seleccionan una)
// ============================
$flota_tipo        = trim($_POST['flota_tipo'] ?? '');
$flota_placa       = trim($_POST['flota_placa'] ?? '');
$flota_descripcion = trim($_POST['flota_descripcion'] ?? '');

// Ruta base para almacenar imágenes de pilotos (asegúrate que tenga permisos de escritura)
$uploadBase = dirname(__DIR__) . '/uploads/pilotos'; // ../uploads/pilotos

// Crea el directorio si no existe
if (!is_dir($uploadBase)) {
    @mkdir($uploadBase, 0775, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ============================
    // 1) Recibir datos del formulario
    // ============================
    $nombre   = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $dpi      = trim($_POST['dpi'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $flota_id = intval($_POST['flota_id'] ?? 0);
    $licencia = $_POST['licencia'] ?? ''; // ahora viene del <select>
    $password = password_hash('piloto123', PASSWORD_DEFAULT);

    // ============================
    // 2) Validaciones simples (servidor)
    // ============================
    $errores = [];
    if ($nombre === '')   $errores[] = "El nombre es obligatorio.";
    if ($apellido === '') $errores[] = "El apellido es obligatorio.";
    if ($dpi === '' || !preg_match('/^\d{13}$/', $dpi)) $errores[] = "El DPI debe tener 13 dígitos.";
    if ($telefono === '') $errores[] = "El teléfono es obligatorio.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = "Correo electrónico inválido.";
    // Si NO seleccionó flota, entonces debe crear una nueva
    if (!$flota_id) {
        if ($flota_tipo === '' || $flota_placa === '') {
            $errores[] = "Selecciona una flota existente o crea una nueva (Tipo y Placa son obligatorios).";
        }
    }
    if (!in_array($licencia, ['A', 'B', 'C', 'M', 'E'], true)) $errores[] = "Tipo de licencia inválido.";

    // Validación de archivos/imágenes (opcionales pero recomendadas)
    // Tamaño máximo 5MB por archivo (ajusta si lo necesitas)
    $maxSize = 5 * 1024 * 1024;
    $camposImagen = [
        'dpi_frente' => 'DPI (frente)',
        'dpi_dorso'  => 'DPI (dorso)',
        'lic_frente' => 'Licencia (frente)',
        'lic_dorso'  => 'Licencia (dorso)',
    ];

    // Pequeña función de validación MIME/size
    $validarArchivo = function ($file) use ($maxSize) {
        if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return [true, null]; // permitido no subir (si quieres hacerlos obligatorios, valida aquí)
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [false, "Error al subir archivo (código {$file['error']})."];
        }
        if ($file['size'] > $maxSize) {
            return [false, "El archivo excede el tamaño máximo permitido (5MB)."];
        }
        // Validar tipo (solo imágenes comunes)
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        $permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        if (!isset($permitidos[$mime])) {
            return [false, "Tipo de archivo no permitido. Usa JPG/PNG/WebP/GIF."];
        }
        return [true, $permitidos[$mime]];
    };

    // Validar todos
    $extensiones = [];
    foreach ($camposImagen as $campo => $label) {
        [$ok, $ext] = $validarArchivo($_FILES[$campo] ?? null);
        if (!$ok) $errores[] = "($label) no válido: $ext";
        $extensiones[$campo] = $ext; // puede quedar null si no se subió
    }

    if ($errores) {
        // Mostrar errores y no continuar
        include 'partials/header.php';
        //include 'partials/sidebar.php';
        echo '<div class="col-lg-10 col-12 p-4">';
        echo '<div class="alert alert-danger"><ul>';
        foreach ($errores as $e) echo '<li>' . htmlspecialchars($e) . '</li>';
        echo '</ul></div>';
        include 'partials/footer.php';
        exit;
    }

    // ============================
    // 3) Guardado en DB + archivos
    // ============================
    $pdo->beginTransaction();
    try {
        // ============================
        // 3A.1) Si no seleccionaron flota, crear una nueva y usarla
        // ============================
        if (!$flota_id) {

            // (Opcional) Validar que la placa no exista (si tu tabla no tiene UNIQUE)
            $stmt = $pdo->prepare("SELECT id FROM flotas WHERE placa = ? LIMIT 1");
            $stmt->execute([$flota_placa]);
            $flotaExistente = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($flotaExistente) {
                // Si ya existe, usamos esa flota (evita duplicados)
                $flota_id = (int)$flotaExistente['id'];
            } else {
                // Crear flota nueva
                $stmt = $pdo->prepare("INSERT INTO flotas (tipo, placa, descripcion) VALUES (?, ?, ?)");
                $stmt->execute([$flota_tipo, $flota_placa, $flota_descripcion]);
                // ID de la flota recién creada
                $flota_id = (int)$pdo->lastInsertId();
            }
        }
        // 3A) Insertar en users
        // OJO: aquí faltaba pasar el DPI en tu versión original
        $stmt = $pdo->prepare("
            INSERT INTO users (nombre, apellido, dpi, telefono, email, password, rol) 
            VALUES (?, ?, ?, ?, ?, ?, 'piloto')
        ");
        $stmt->execute([$nombre, $apellido, $dpi, $telefono, $email, $password]);
        $user_id = $pdo->lastInsertId();

        // 3B) Guardar archivos (si vienen) con nombres únicos y seguros
        //     Se recomienda nombrarlos con el user_id para fácil asociación
        $rutasRel = [
            'dpi_frente' => null,
            'dpi_dorso'  => null,
            'lic_frente' => null,
            'lic_dorso'  => null,
        ];

        foreach ($camposImagen as $campo => $label) {
            $file = $_FILES[$campo] ?? null;
            if ($file && $file['error'] === UPLOAD_ERR_OK) {
                $ext = $extensiones[$campo] ?? 'jpg';
                // ejemplo: 42_dpi_frente_20251025_173045.jpg
                $nombreFinal = $user_id . '_' . $campo . '_' . date('Ymd_His') . '.' . $ext;
                $destinoAbs  = $uploadBase . '/' . $nombreFinal;
                $destinoRel  = 'uploads/pilotos/' . $nombreFinal; // esto guardarás en DB

                if (!move_uploaded_file($file['tmp_name'], $destinoAbs)) {
                    throw new Exception("No se pudo guardar el archivo de $label en disco.");
                }
                $rutasRel[$campo] = $destinoRel;
            }
        }

        // 3C) Insertar en pilotos con las rutas de imagen
        $stmt = $pdo->prepare("
            INSERT INTO pilotos (user_id, flota_id, licencia, dpi_frente, dpi_dorso, lic_frente, lic_dorso)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user_id,
            $flota_id,
            $licencia,
            $rutasRel['dpi_frente'],
            $rutasRel['dpi_dorso'],
            $rutasRel['lic_frente'],
            $rutasRel['lic_dorso'],
        ]);

        $pdo->commit();
        header("Location: pilotos.php");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error al crear piloto: " . htmlspecialchars($e->getMessage());
        exit;
    }
}

include 'partials/header.php';
//include 'partials/sidebar.php';
?>

<div class="layout-content-container flex flex-col max-w-[960px] flex-1">
    <!-- Page Heading -->
    <div class="flex flex-wrap justify-between items-center gap-3 p-4 mb-4">
        <div class="flex min-w-72 flex-col gap-1">
            <p class="text-[#0d121b] dark:text-white text-3xl md:text-4xl font-black leading-tight tracking-[-0.033em]">Registro de pilotos</p>
            <p class="text-[#4c669a] dark:text-gray-400 text-base font-normal leading-normal">Complete el formulario de incorporación para agregar un nuevo conductor a la flota de CaliTex.</p>
        </div>
        <button class="flex min-w-[120px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-4 bg-primary/10 text-primary text-sm font-bold leading-normal tracking-[0.015em] hover:bg-primary/20 transition-all">
            <span class="truncate">Ver todos los pilotos</span>
        </button>
    </div>
    <!-- Form Card -->
    <div class="bg-white dark:bg-[#1a2130] rounded-xl shadow-sm border border-[#e7ebf3] dark:border-gray-800 p-2 sm:p-6">
        <form method="post" enctype="multipart/form-data" class="space-y-8">
            <!-- Personal Information Section -->
            <section>
                <div class="flex items-center gap-2 px-4 pb-3 pt-5 border-b border-gray-100 dark:border-gray-800 mb-6">
                    <span class="material-symbols-outlined text-primary">person</span>
                    <h2 class="text-[#0d121b] dark:text-white text-[22px] font-bold leading-tight tracking-[-0.015em]">Información personal</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 px-4">
                    <!-- Photo Upload -->
                    <div class="md:col-span-1 flex flex-col items-center gap-3 text-center pb-3">
                        <div class="relative group cursor-pointer">
                            <div class="size-32 bg-background-light dark:bg-gray-800 bg-center bg-no-repeat bg-cover rounded-full border-2 border-dashed border-primary/30 flex flex-col items-center justify-center overflow-hidden" data-alt="Profile photo upload placeholder">
                                <span class="material-symbols-outlined text-4xl text-primary/40">add_a_photo</span>
                                <p class="text-[10px] text-primary/60 font-semibold px-2">CLICK TO UPLOAD</p>
                            </div>
                            <div class="absolute inset-0 rounded-full bg-black/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        </div>
                        <div>
                            <p class="text-[#0d121b] dark:text-white text-base font-medium leading-normal">Profile Photo</p>
                            <p class="text-[#4c669a] dark:text-gray-400 text-xs font-normal leading-normal">JPG, PNG up to 5MB</p>
                        </div>
                    </div>
                    <!-- Name and ID -->
                    <div class="md:col-span-3 space-y-4">
                        <div class="flex flex-col md:flex-row gap-4">
                            <label class="flex flex-col flex-1">
                                <p class="text-[#0d121b] dark:text-gray-200 text-sm font-semibold leading-normal pb-2">Nombre</p>
                                <div class="flex w-full items-stretch rounded-lg shadow-sm">
                                    <input name="nombre" class="form-input flex w-full min-w-0 flex-1 rounded-lg text-[#0d121b] dark:text-white focus:outline-0 focus:ring-1 focus:ring-primary border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 h-12 placeholder:text-[#4c669a] dark:placeholder:text-gray-500 p-[12px] rounded-r-none border-r-0 text-base font-normal" placeholder="e.g. Johnathan Doe" type="text" />
                                    <div class="text-[#4c669a] flex border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 items-center justify-center px-3 rounded-r-lg border-l-0">
                                        <span class="material-symbols-outlined text-[20px]">badge</span>
                                    </div>
                                </div>
                            </label>
                            <label class="flex flex-col flex-1">
                                <p class="text-[#0d121b] dark:text-gray-200 text-sm font-semibold leading-normal pb-2">Apellido</p>
                                <div class="flex w-full items-stretch rounded-lg shadow-sm">
                                    <input name="apellido" class="form-input flex w-full min-w-0 flex-1 rounded-lg text-[#0d121b] dark:text-white focus:outline-0 focus:ring-1 focus:ring-primary border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 h-12 placeholder:text-[#4c669a] dark:placeholder:text-gray-500 p-[12px] rounded-r-none border-r-0 text-base font-normal" placeholder="e.g. Doe" type="text" />
                                    <div class="text-[#4c669a] flex border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 items-center justify-center px-3 rounded-r-lg border-l-0">
                                        <span class="material-symbols-outlined text-[20px]">badge</span>
                                    </div>
                                </div>
                            </label>
                            <label class="flex flex-col flex-1">
                                <p class="text-[#0d121b] dark:text-gray-200 text-sm font-semibold leading-normal pb-2">DPI</p>
                                <div class="flex w-full items-stretch rounded-lg shadow-sm">
                                    <input name="dpi" inputmode="numeric" maxlength="13" class="form-input flex w-full min-w-0 flex-1 rounded-lg text-[#0d121b] dark:text-white focus:outline-0 focus:ring-1 focus:ring-primary border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 h-12 placeholder:text-[#4c669a] dark:placeholder:text-gray-500 p-[12px] rounded-r-none border-r-0 text-base font-normal" placeholder="12345678-X" type="text" />
                                    <div class="text-[#4c669a] flex border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 items-center justify-center px-3 rounded-r-lg border-l-0">
                                        <span class="material-symbols-outlined text-[20px]">id_card</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <label class="flex flex-col">
                            <p class="text-[#0d121b] dark:text-gray-200 text-sm font-semibold leading-normal pb-2">Telefono</p>
                            <div class="flex w-full items-stretch rounded-lg shadow-sm">
                                <input name="telefono" class="form-input flex w-full min-w-0 flex-1 rounded-lg text-[#0d121b] dark:text-white focus:outline-0 focus:ring-1 focus:ring-primary border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 h-12 placeholder:text-[#4c669a] dark:placeholder:text-gray-500 p-[12px] rounded-r-none border-r-0 text-base font-normal" placeholder="+1 (555) 000-0000" type="tel" />
                                <div class="text-[#4c669a] flex border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 items-center justify-center px-3 rounded-r-lg border-l-0">
                                    <span class="material-symbols-outlined text-[20px]">call</span>
                                </div>
                            </div>
                        </label>
                        <label class="flex flex-col">
                            <p class="text-[#0d121b] dark:text-gray-200 text-sm font-semibold leading-normal pb-2">Correo electronico</p>
                            <div class="flex w-full items-stretch rounded-lg shadow-sm">
                                <input name="email" class="form-input flex w-full min-w-0 flex-1 rounded-lg text-[#0d121b] dark:text-white focus:outline-0 focus:ring-1 focus:ring-primary border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 h-12 placeholder:text-[#4c669a] dark:placeholder:text-gray-500 p-[12px] rounded-r-none border-r-0 text-base font-normal" placeholder="john.doe@example.com" type="email" />
                                <div class="text-[#4c669a] flex border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 items-center justify-center px-3 rounded-r-lg border-l-0">
                                    <span class="material-symbols-outlined text-[20px]">email</span>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </section>
            <!-- Vehicle Details Section -->
            <section>
                <div class="flex items-center gap-2 px-4 pb-3 pt-5 border-b border-gray-100 dark:border-gray-800 mb-6">
                    <span class="material-symbols-outlined text-primary">delivery_dining</span>
                    <h2 class="text-[#0d121b] dark:text-white text-[22px] font-bold leading-tight tracking-[-0.015em]">Detalles del vehículo</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 px-4">
                    <!-- Flota existente -->
                    <label class="flex flex-col">
                        <p class="text-[#0d121b] dark:text-gray-200 text-sm font-semibold leading-normal pb-2">Vehículo</p>

                        <div class="relative">
                            <select
                                name="flota_id"
                                id="flota_id"
                                class="form-select w-full rounded-lg text-[#0d121b] dark:text-white focus:outline-0 focus:ring-1 focus:ring-primary border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 h-12 px-4 text-base appearance-none">
                                <option value="">Selecciona una flota</option>
                                <?php foreach ($flotas as $flota): ?>
                                    <option value="<?= $flota['id'] ?>"><?= htmlspecialchars($flota['placa']) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-[#4c669a]">
                                <span class="material-symbols-outlined">expand_more</span>
                            </div>
                        </div>

                        <p class="text-xs text-[#4c669a] dark:text-gray-400 mt-2">
                            Si no existe, activa “Crear nueva flota” y completá los datos.
                        </p>
                    </label>

                    <!-- Toggle crear flota -->
                    <label class="flex flex-col">
                        <p class="text-[#0d121b] dark:text-gray-200 text-sm font-semibold leading-normal pb-2">Crear nueva flota</p>

                        <div class="flex items-center justify-between w-full rounded-lg border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 h-12 px-4">
                            <span class="text-sm text-[#0d121b] dark:text-gray-200">Activar</span>

                            <!-- Switch estilo simple -->
                            <input
                                type="checkbox"
                                id="toggleNuevaFlota"
                                class="h-5 w-5 accent-[var(--tw-ring-color)]" />
                        </div>

                        <p class="text-xs text-[#4c669a] dark:text-gray-400 mt-2">
                            Al activar, se creará una flota y se asignará automáticamente al piloto.
                        </p>
                    </label>

                    <!-- Tipo de flota (nuevo) -->
                    <label class="flex flex-col nuevaFlotaCampo hidden">
                        <p class="text-[#0d121b] dark:text-gray-200 text-sm font-semibold leading-normal pb-2">Tipo de vehículo</p>

                        <div class="relative">
                            <select
                                name="flota_tipo"
                                id="flota_tipo"
                                class="form-select w-full rounded-lg text-[#0d121b] dark:text-white focus:outline-0 focus:ring-1 focus:ring-primary border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 h-12 px-4 text-base appearance-none">
                                <option value="">Selecciona tipo</option>
                                <option value="MOTOCICLETA">MOTOCICLETA</option>
                                <option value="CARRO">CARRO</option>
                                <option value="PANEL">PANEL</option>
                                <option value="CAMION">CAMION</option>
                            </select>

                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-[#4c669a]">
                                <span class="material-symbols-outlined">expand_more</span>
                            </div>
                        </div>
                    </label>

                    <!-- Placa (nuevo) -->
                    <label class="flex flex-col nuevaFlotaCampo hidden">
                        <p class="text-[#0d121b] dark:text-gray-200 text-sm font-semibold leading-normal pb-2">Placa</p>
                        <div class="flex w-full items-stretch rounded-lg shadow-sm">
                            <input
                                name="flota_placa"
                                id="flota_placa"
                                class="form-input flex w-full min-w-0 flex-1 rounded-lg text-[#0d121b] dark:text-white focus:outline-0 focus:ring-1 focus:ring-primary border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 h-12 placeholder:text-[#4c669a] dark:placeholder:text-gray-500 p-[12px] rounded-r-none border-r-0 text-base font-normal"
                                placeholder="ABC-1234"
                                type="text" />
                            <div class="text-[#4c669a] flex border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 items-center justify-center px-3 rounded-r-lg border-l-0">
                                <span class="material-symbols-outlined text-[20px]">directions_car</span>
                            </div>
                        </div>
                    </label>

                    <!-- Descripción (nuevo) -->
                    <label class="flex flex-col nuevaFlotaCampo hidden">
                        <p class="text-[#0d121b] dark:text-gray-200 text-sm font-semibold leading-normal pb-2">Descripcion</p>
                        <div class="flex w-full items-stretch rounded-lg shadow-sm">
                            <input
                                name="flota_descripcion"
                                id="flota_descripcion"
                                class="form-input flex w-full min-w-0 flex-1 rounded-lg text-[#0d121b] dark:text-white focus:outline-0 focus:ring-1 focus:ring-primary border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 h-12 placeholder:text-[#4c669a] dark:placeholder:text-gray-500 p-[12px] rounded-r-none border-r-0 text-base font-normal"
                                placeholder="Marca, color, año, etc."
                                type="text" />
                            <div class="text-[#4c669a] flex border border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 items-center justify-center px-3 rounded-r-lg border-l-0">
                                <span class="material-symbols-outlined text-[20px]">palette</span>
                            </div>
                        </div>
                    </label>
                    <!-- JS mini: mostrar/ocultar campos nueva flota sin romper estilos -->
                    <script>
                        (function() {
                            const toggle = document.getElementById('toggleNuevaFlota');
                            const campos = document.querySelectorAll('.nuevaFlotaCampo');
                            const flotaSelect = document.getElementById('flota_id');

                            function actualizarUI() {
                                const activo = toggle.checked;

                                campos.forEach(el => {
                                    el.classList.toggle('hidden', !activo);
                                });

                                // Si activa nueva flota, limpiamos selección de flota existente (evita confusión)
                                if (activo) {
                                    flotaSelect.value = '';
                                }
                            }

                            toggle.addEventListener('change', actualizarUI);

                            // Si el usuario selecciona una flota existente, desactiva nueva flota
                            flotaSelect.addEventListener('change', function() {
                                if (flotaSelect.value) {
                                    toggle.checked = false;
                                    actualizarUI();
                                }
                            });

                            // Estado inicial
                            actualizarUI();
                        })();
                    </script>

                </div>
            </section>
            <!-- Documentation Section -->
            <section>
                <div class="flex items-center gap-2 px-4 pb-3 pt-5 border-b border-gray-100 dark:border-gray-800 mb-6">
                    <span class="material-symbols-outlined text-primary">description</span>
                    <h2 class="text-[#0d121b] dark:text-white text-[22px] font-bold leading-tight tracking-[-0.015em]">Documentacion</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 px-4">
                    <label class="flex flex-col">
                        <p class="text-[#0d121b] dark:text-gray-200 text-sm font-semibold leading-normal pb-2">DPI frente</p>
                        <div class="flex w-full items-stretch rounded-lg shadow-sm">
                            <label class="form-label">DPI - Frente</label>
                            <!-- accept + capture permite tomar foto directa desde móvil -->
                            <input type="file" name="dpi_frente" class="form-control" accept="image/*" capture="environment">
                            <small class="text-muted">Formatos: JPG/PNG/WebP, máx. 5MB.</small>
                        </div>
                    </label>
                    <label class="flex flex-col">
                        <p class="text-[#0d121b] dark:text-gray-200 text-sm font-semibold leading-normal pb-2">DPI dorso</p>
                        <div class="flex w-full items-stretch rounded-lg shadow-sm">
                            <label class="form-label">DPI - Dorso</label>
                            <!-- accept + capture permite tomar foto directa desde móvil -->
                            <input type="file" name="dpi_dorso" class="form-control" accept="image/*" capture="environment">
                            <small class="text-muted">Formatos: JPG/PNG/WebP, máx. 5MB.</small>
                        </div>
                    </label>
                    <label class="flex flex-col">
                        <p class="text-[#0d121b] dark:text-gray-200 text-sm font-semibold leading-normal pb-2">Licencia frente</p>
                        <div class="flex w-full items-stretch rounded-lg shadow-sm">
                            <label class="form-label">Licencia - Frente</label>
                            <input type="file" name="lic_frente" class="form-control" accept="image/*" capture="environment">
                            <small class="text-muted">Formatos: JPG/PNG/WebP, máx. 5MB.</small>
                        </div>
                        <p class="text-accent-orange text-xs font-medium mt-1.5 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">warning</span> Debe tener una validez mínima de 6 meses.
                        </p>
                    </label>
                    <label class="flex flex-col">
                        <p class="text-[#0d121b] dark:text-gray-200 text-sm font-semibold leading-normal pb-2">Licencia dorso</p>
                        <div class="flex w-full items-stretch rounded-lg shadow-sm">
                            <label class="form-label">Licencia dorso</label>
                            <input type="file" name="lic_dorso" class="form-control" accept="image/*" capture="environment">
                            <small class="text-muted">Formatos: JPG/PNG/WebP, máx. 5MB.</small>
                        </div>
                        <p class="text-accent-orange text-xs font-medium mt-1.5 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">warning</span> Debe tener una validez mínima de 6 meses.
                        </p>
                    </label>
                    <label class="flex flex-col">
                        <p class="text-[#0d121b] dark:text-gray-200 text-sm font-semibold leading-normal pb-2">Tipo de licencia</p>
                        <div class="flex w-full items-stretch rounded-lg shadow-sm">
                            <select name="licencia" class="form-select" required>
                                <option value="">Selecciona tipo</option>
                                <option value="A">Tipo A</option>
                                <option value="B">Tipo B</option>
                                <option value="C">Tipo C</option>
                                <option value="M">Tipo M</option>
                                <option value="E">Tipo E</option>
                            </select>
                        </div>
                    </label>
                </div>
            </section>
            <!-- Form Actions -->
            <div class="flex flex-col sm:flex-row items-center justify-end gap-4 p-4 mt-10 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                <button  class="w-full sm:w-auto px-8 h-12 rounded-lg text-[#4c669a] dark:text-gray-400 font-bold text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" type="button">
                    <a href="pilotos.php">Cancel</a>
                </button>
                <button  class="w-full sm:w-auto flex items-center justify-center gap-2 min-w-[200px] h-12 px-8 bg-primary text-white text-sm font-bold rounded-lg shadow-lg shadow-primary/20 hover:bg-primary/90 transition-all active:scale-95" type="submit">
                    <span class="material-symbols-outlined">person_add</span>
                    Registrar piloto
                </button>
            </div>
        </form>
    </div>
<?php include 'partials/footerp.php'; ?>