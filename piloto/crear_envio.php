<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'piloto') {
  header("Location: ../login.php");
  exit;
}

$piloto_id = (int)$_SESSION['user_id'];

$clientes = $pdo->query("
  SELECT c.id AS cliente_id, u.nombre
  FROM clientes c
  JOIN users u ON u.id = c.user_id
  WHERE u.rol = 'cliente'
  ORDER BY u.nombre
")->fetchAll(PDO::FETCH_ASSOC);

$cliente_id = $_POST['cliente_id'] ?? null;

// Direcciones del cliente seleccionado
$direcciones = [];
$direccion_map = [];

if ($cliente_id) {
  $stmt = $pdo->prepare("
    SELECT d.*, z.numero AS zona, m.nombre AS municipio, dp.nombre AS departamento
    FROM direcciones d
    JOIN zona z ON d.zona_id = z.id
    JOIN municipios m ON d.municipio_id = m.id
    JOIN departamentos dp ON d.departamento_id = dp.id
    WHERE d.cliente_id = ?
  ");
  $stmt->execute([$cliente_id]);
  $direcciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($direcciones as $d) {
    $direccion_map[$d['id']] = "{$d['calle']} #{$d['numero']}, Zona {$d['zona']}, {$d['municipio']}, {$d['departamento']}";
  }
}

$paquetes = $pdo->query("SELECT id, nombre, tamano, peso, tarifa FROM paquetes ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

$errors = [];
$guia_data = null; // ← en vez de $guia_script

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
  $monto_cobros = $_POST['monto_cobros'] ?? [];

  // OJO: tus values actuales son "cliente" y "destinatario" (no sender/recipient)
  $pago_envio = $_POST['pago_envio'] ?? 'cliente';

  $direccion_origen_id = $direcciones[0]['id'] ?? null;
  $direccion_texto = $direccion_map[$direccion_destino_id] ?? '';

  try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
      INSERT INTO envios (cliente_id, piloto_id, direccion_origen_id, direccion_destino_id, nombre_destinatario, telefono_destinatario, descripcion, pago_envio)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$cliente_id, $piloto_id, $direccion_origen_id, $direccion_destino_id, $nombre_destinatario, $telefono_destinatario, $descripcion, $pago_envio]);

    $envio_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO envios_paquetes (envio_id, paquete_id, monto_cobro) VALUES (?, ?, ?)");

    $total_cobro = 0;

    foreach ($paquete_ids as $paquete_id => $cantidad) {
      $cantidad = (int)$cantidad;
      if ($cantidad <= 0) continue;

      $monto = isset($monto_cobros[$paquete_id]) ? (float)$monto_cobros[$paquete_id] : 0.00;

      for ($i = 0; $i < $cantidad; $i++) {
        $stmt->execute([$envio_id, $paquete_id, $monto]);
      }

      $total_cobro += $cantidad * $monto;
    }

    // Tarifa total
    $tarifa_envio_total = 0;
    foreach ($paquetes as $p) {
      $id_paquete = (int)$p['id'];
      $cantidad = isset($paquete_ids[$id_paquete]) ? (int)$paquete_ids[$id_paquete] : 0;
      $tarifa_envio_total += $cantidad * (float)$p['tarifa'];
    }

    if ($pago_envio === 'destinatario') {
      $total_cobro += $tarifa_envio_total;
    }

    $pdo->commit();

    // Datos para la vista/modal (sin concatenar script gigante)
    $guia_data = [
      'envio_id' => $envio_id,
      'nombre' => $nombre_destinatario,
      'telefono' => $telefono_destinatario,
      'direccion' => $direccion_texto,
      'descripcion' => $descripcion,
      'pago_texto' => ($pago_envio === 'destinatario') ? 'Cobro contra entrega' : 'Cobro a mi cuenta',
      'cobro_total' => number_format($total_cobro, 2),
    ];

  } catch (Exception $e) {
    $pdo->rollBack();
    $errors[] = "Error al crear envío: " . $e->getMessage();
  }
}

require_once '../views/piloto/crear_envio.view.php';