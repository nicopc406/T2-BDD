<?php

session_start();

if (!isset($_SESSION['rut_usuario'])){

    header('Location: Login.php');
    exit();
}

$nombre_usuario = $_SESSION['nombre_usuario'];
$rol_usuario = $_SESSION['rol'];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Principal - ZeroPressure</title>
    <style>
        body {font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f4f4f9; margin: 0; padding: 0;}
        .navbar {background-color: #333; color: white; padding: 1rem; display: flex; justify-content: space-between; align-items: center;}
        .navbar a {color: white; text-decoration: none; padding: 0.5rem 1rem;}
        .navbar a:hover {background-color: #555; border-radius: 4px;}
        .container {padding: 2rem; max-width: 960px; margin: auto;}
        .welcome-box {background-color: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); text-align: center;}
        h1 {color: #333;}
        .menu {margin-top: 2rem; list-style: none; padding: 0;}
        .menu li {background-color: #007bff; margin-bottom: 1rem; border-radius: 4px;}
        .menu li a {display: block; padding: 1rem; color: white; text-decoration: none;}
        .menu li a:hover {background-color: #0056b3;}
    </style>
</head>
<body>

<nav class="navbar">
    <div>
        <strong>ZeroPressure</strong>
    </div>
    <div>
        <span>Hola, <?php echo htmlspecialchars($nombre_usuario); ?> (<?php echo htmlspecialchars($rol_usuario); ?>)</span>
        <a href="Salir.php">Cerrar Sesión</a>
    </div>
</nav>

<div class="container">
    <div class="welcome-box">
        <h1>Bienvenido a tu Panel de Control</h1>
        <p>Desde aquí puedes gestionar tus solicitudes y reportes.</p>
    </div>

    <ul class="menu">
        <?php if ($rol_usuario === 'ingeniero'): ?>
            <li><a href="ver_solicitudes.php?tipo=funcionalidad">Ver Todas las Solicitudes de Funcionalidad</a></li>
            <li><a href="ver_solicitudes.php?tipo=error">Ver Todas las Solicitudes de Error</a></li>
            <li><a href="mis_asignaciones.php">Ver Mis Solicitudes Asignadas</a></li>
        <?php else: ?>
            <li><a href="mis_solicitudes.php?tipo=funcionalidad">Ver Mis Solicitudes de Funcionalidad</a></li>
            <li><a href="mis_solicitudes.php?tipo=error">Ver Mis Solicitudes de Error</a></li>
        <?php endif; ?>

        <li><a href="crear_solicitud.php">Crear una Nueva Solicitud</a></li>
    </ul>
</div>

</body>
</html>
