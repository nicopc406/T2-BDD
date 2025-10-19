<?php
// Fichero: /var/www/html/zeropressure/logout.php

// 1. Iniciar la sesión para poder acceder a ella.
session_start();

// 2. Vaciar todas las variables de sesión.
$_SESSION = array();

// 3. Destruir la sesión por completo.
session_destroy();

// 4. Redirigir al usuario a la página de login.
header('Location: Login.php');
exit();
?>
