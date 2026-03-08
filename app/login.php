<?php 
session_start();
require_once 'config/db.php';

if ($_SERVER ['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND estado = 1");
    $stmt ->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['rol'] = $user['rol'];
        header("location: {$user['rol']}/dashboard.php");
        exit;
    } else {
        echo "Credenciales incorrectas.";
    }
}
?>