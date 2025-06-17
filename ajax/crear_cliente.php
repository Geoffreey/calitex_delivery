<?php
require_once '../config/db.php';

$nombre = $_POST['nombre'] ?? '';
$apellido = $_POST['apellido'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$email = $_POST['email'] ?? '';

if (!$nombre || !$email) {
  http_response_code(400);
  echo "Faltan campos requeridos.";
  exit;
}

// Verifica duplicado
$check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$check->execute([$email]);
if ($check->fetch()) {
  http_response_code(409);
  echo "Este correo ya está registrado.";
  exit;
}

// Generar contraseña por defecto: el apellido
$password_plano = $apellido ?: 'cliente123';
$password = password_hash($password_plano, PASSWORD_DEFAULT);

// Insertar en users
$stmt = $pdo->prepare("INSERT INTO users (nombre, apellido, telefono, email, password, rol) VALUES (?, ?, ?, ?, ?, 'cliente')");
$stmt->execute([$nombre, $apellido, $telefono, $email, $password]);
$user_id = $pdo->lastInsertId();

// Insertar en clientes
$stmt = $pdo->prepare("INSERT INTO clientes (user_id) VALUES (?)");
$stmt->execute([$user_id]);

// Devolver datos como JSON válido
$cliente_id = $pdo->lastInsertId();
header('Content-Type: application/json');
echo json_encode([
  'id' => $cliente_id,
  'user_id' => $user_id,
  'nombre' => $nombre . ' ' . $apellido
]);
