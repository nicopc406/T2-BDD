<?php
session_start();

if (!isset($_SESSION['rut_usuario']) || !isset($_GET['id'])) {
    header('Location: Page.php');
    exit();
}

require_once 'Conexion.php';

$id_solicitud = filter_var($_GET['id'], FILTER_VALIDATE_INT);
$rut_usuario = $_SESSION['rut_usuario'];

if ($id_solicitud === false) {
    header('Location: VerSolicitudes.php');
    exit();
}

$conexion->begin_transaction();
try {
    // 1. Verificar que el usuario es el dueño y que el estado no es "En Progreso"
    $sql_check = "SELECT estado FROM Solicitudes WHERE id_solicitud = ? AND rut_usuario = ?";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->bind_param('is', $id_solicitud, $rut_usuario);
    $stmt_check->execute();
    $resultado = $stmt_check->get_result();

    if ($resultado->num_rows === 1) {
        $solicitud = $resultado->fetch_assoc();

        if ($solicitud['estado'] === 'En Progreso') {
            // Si está en progreso, no se puede eliminar. Hacemos rollback y salimos.
            $conexion->rollback();
            header('Location: VerSolicitudes.php?error=en_progreso');
            exit();
        }

        // 2. Proceder con la eliminación (ON DELETE CASCADE se encargará del resto)
        $sql_delete = "DELETE FROM Solicitudes WHERE id_solicitud = ?";
        $stmt_delete = $conexion->prepare($sql_delete);
        $stmt_delete->bind_param('i', $id_solicitud);
        $stmt_delete->execute();

        $conexion->commit();

    } else {
        // El usuario no es el dueño o la solicitud no existe
        $conexion->rollback();
    }

} catch (Exception $e) {
    $conexion->rollback();
    // Podrías registrar el error en un log
    // error_log($e->getMessage());
}

$conexion->close();
header('Location: VerSolicitudes.php');
exit();
?>