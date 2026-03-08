<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

// Obtener flotas activas
$flotas = $pdo->query("SELECT id, placa FROM flotas ORDER BY placa ASC")->fetchAll();

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
  if (!$flota_id) $errores[] = "Debes seleccionar una flota.";
  if (!in_array($licencia, ['A','B','C','M','E'], true)) $errores[] = "Tipo de licencia inválido.";

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
  $validarArchivo = function($file) use ($maxSize) {
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
    $permitidos = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];
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
    foreach ($errores as $e) echo '<li>'.htmlspecialchars($e).'</li>';
    echo '</ul></div>';
    include 'partials/footer.php';
    exit;
  }

  // ============================
  // 3) Guardado en DB + archivos
  // ============================
  $pdo->beginTransaction();
  try {

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

<div class="col-lg-10 col-12 p-4">
  <h2>Nuevo Piloto</h2>

  <!-- Enctype necesario para subir imágenes -->
  <form method="POST" class="row g-3" enctype="multipart/form-data">
    <div class="col-md-6">
      <label class="form-label">Nombre</label>
      <input type="text" name="nombre" class="form-control" required>
    </div>

    <div class="col-md-6">
      <label class="form-label">Apellido</label>
      <input type="text" name="apellido" class="form-control" required>
    </div>

    <div class="col-md-6">
      <label class="form-label">DPI</label>
      <input type="text" name="dpi" class="form-control" inputmode="numeric" maxlength="13" required>
    </div>

    <div class="col-md-6">
      <label class="form-label">Teléfono</label>
      <input type="text" name="telefono" class="form-control" required>
    </div>

    <div class="col-md-6">
      <label class="form-label">Correo electrónico</label>
      <input type="email" name="email" class="form-control" required>
    </div>

    <div class="col-md-6">
      <label class="form-label">Flota asignada</label>
      <select name="flota_id" class="form-select" required>
        <option value="">Selecciona una flota</option>
        <?php foreach ($flotas as $flota): ?>
          <option value="<?= $flota['id'] ?>"><?= htmlspecialchars($flota['placa']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Campo de licencia como SELECT -->
    <div class="col-md-6">
      <label class="form-label">Tipo de licencia</label>
      <select name="licencia" class="form-select" required>
        <option value="">Selecciona tipo</option>
        <option value="A">Tipo A</option>
        <option value="B">Tipo B</option>
        <option value="C">Tipo C</option>
        <option value="M">Tipo M</option>
        <option value="E">Tipo E</option>
      </select>
    </div>

    <!-- Carga de imágenes: DPI y Licencia (frente/dorso) -->
    <div class="col-12"><hr></div>
    <div class="col-md-6">
      <label class="form-label">DPI - Frente</label>
      <!-- accept + capture permite tomar foto directa desde móvil -->
      <input type="file" name="dpi_frente" class="form-control" accept="image/*" capture="environment">
      <small class="text-muted">Formatos: JPG/PNG/WebP, máx. 5MB.</small>
    </div>
    <div class="col-md-6">
      <label class="form-label">DPI - Dorso</label>
      <input type="file" name="dpi_dorso" class="form-control" accept="image/*" capture="environment">
    </div>
    <div class="col-md-6">
      <label class="form-label">Licencia - Frente</label>
      <input type="file" name="lic_frente" class="form-control" accept="image/*" capture="environment">
    </div>
    <div class="col-md-6">
      <label class="form-label">Licencia - Dorso</label>
      <input type="file" name="lic_dorso" class="form-control" accept="image/*" capture="environment">
    </div>

    <div class="col-12">
      <p class="text-muted">Contraseña por defecto: <strong>piloto123</strong></p>
      <button type="submit" class="btn btn-success">Guardar</button>
      <a href="pilotos.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>

<?php include 'partials/footer.php'; ?>
