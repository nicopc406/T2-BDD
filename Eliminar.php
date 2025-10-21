<?php
session_start();


if (!isset($_SESSION['rut_usuario']) || !isset($_GET['id']) || !isset($_GET['tipo'])) {
    header('Location: Page.php');
    exit();
}

require_once 'Conexion.php';

$id_solicitud = filter_var($_GET['id'], FILTER_VALIDATE_INT);
$rut_usuario = $_SESSION['rut_usuario'];
$tipo_solicitud = $_GET['tipo'];


$url_regreso = "VerSolicitudes.php?tipo=" . urlencode($tipo_solicitud);

if ($id_solicitud === false || !in_array($tipo_solicitud, ['Funcionalidad', 'Error'])) {

    header('Location: ' . $url_regreso);
    exit();
}

$conexion->begin_transaction();
try {
    
    $sql_check = "SELECT estado FROM Solicitudes WHERE id_solicitud = ? AND rut_usuario = ?";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->bind_param('is', $id_solicitud, $rut_usuario);
    $stmt_check->execute();
    $resultado = $stmt_check->get_result();

    if ($resultado->num_rows === 1) {
        $solicitud = $resultado->fetch_assoc();

        if ($solicitud['estado'] === 'En Progreso') {
            $conexion->rollback();
            
            header('Location: ' . $url_regreso . '&error=en_progreso');
            exit();
        }

        
        $sql_delete = "DELETE FROM Solicitudes WHERE id_solicitud = ?";
        $stmt_delete = $conexion->prepare($sql_delete);
        $stmt_delete->bind_param('i', $id_solicitud);
        $stmt_delete->execute();

        $conexion->commit();
    } else {
        
        $conexion->rollback();
    }

} catch (Exception $e) {
    
    $conexion->rollback();
}

$conexion->close();


header('Location: ' . $url_regreso);
exit();
?>