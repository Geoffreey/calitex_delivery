<?php
session_start();

// Elimina todas las variables de sesión
session_unset();

// Destruye completamente la sesión
session_destroy();

// Redirige al login principal
header("Location: index.php");
exit;