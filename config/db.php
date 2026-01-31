<?php 
$host = 'localhost';
$db = 'systccbx_flydelivery';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $se) {
    die("Error en la conexión: " . $se->getMessage());
}

define('BASE_URL', '/calitex_delivery'); // local
// en producción sería '' o '/'

?>
