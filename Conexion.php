<?php

$db_host = 'localhost';
$db_usuario = 'root';
$db_contrasena = 'Zazalele1!';
$db_nombre = 'Tarea2';

try {

    $conexion = new mysqli($db_host, $db_usuario, $db_contrasena, $db_nombre);

    if ($conexion->connect_error) {

        die("Error de conexion: " . $conexion->connect_error);
    }

}

catch (Exception $e) {

    die("Error inesperado de conexion: " . $e->getMessage());
}

//echo "Conexion exitosa";
?>
