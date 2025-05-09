<?php
require_once 'config/db.php';

$nombre = 'Admin';
$apellido = 'General';
$telefono = '12345678';
$email = 'admin@app.com';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$rol = 'admin';

$stmt = $pdo->prepare("INSERT INTO users (nombre, apellido, telefono, email, password, rol) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([$nombre, $apellido, $telefono, $email, $password, $rol]);

echo "Administrador creado con éxito.";