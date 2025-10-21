<?php

$db_host = 'localhost';
$db_usuario = 'root';
$db_contrasena = 'Tu Contraseña';
$db_nombre = 'Nombre de TU Base de Datos (Nosotros usamos Tarea2)';

try {

    $conexion = new mysqli($db_host, $db_usuario, $db_contrasena, $db_nombre);

    if ($conexion->connect_error){

        die("Error de conexion: " . $conexion->connect_error);
    }

}

catch (Exception $e){

    die("Error inesperado de conexion: " . $e->getMessage());
}


?>
