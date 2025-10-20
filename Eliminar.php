<?php
session_start();

// Verificamos que se reciban todos los parámetros necesarios
if (!isset($_SESSION['rut_usuario']) || !isset($_GET['id']) || !isset($_GET['tipo'])) {
    header('Location: Page.php');
    exit();
}

require_once 'Conexion.php';

$id_solicitud = filter_var($_GET['id'], FILTER_VALIDATE_INT);
$rut_usuario = $_SESSION['rut_usuario'];
$tipo_solicitud = $_GET['tipo'];

// Construimos la URL de regreso para usarla en todas las redirecciones
$url_regreso = "VerSolicitudes.php?tipo=" . urlencode($tipo_solicitud);

if ($id_solicitud === false || !in_array($tipo_solicitud, ['Funcionalidad', 'Error'])) {
    // Si el ID o el tipo son inválidos, regresamos sin hacer nada
    header('Location: ' . $url_regreso);
    exit();
}

$conexion->begin_transaction();
try {
    // 1. Verificamos que el usuario es el dueño y que el estado no es "En Progreso"
    $sql_check = "SELECT estado FROM Solicitudes WHERE id_solicitud = ? AND rut_usuario = ?";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->bind_param('is', $id_solicitud, $rut_usuario);
    $stmt_check->execute();
    $resultado = $stmt_check->get_result();

    if ($resultado->num_rows === 1) {
        $solicitud = $resultado->fetch_assoc();

        if ($solicitud['estado'] === 'En Progreso') {
            $conexion->rollback();
            // Redirigimos con un mensaje de error
            header('Location: ' . $url_regreso . '&error=en_progreso');
            exit();
        }

        // 2. Procedemos con la eliminación
        $sql_delete = "DELETE FROM Solicitudes WHERE id_solicitud = ?";
        $stmt_delete = $conexion->prepare($sql_delete);
        $stmt_delete->bind_param('i', $id_solicitud);
        $stmt_delete->execute();

        $conexion->commit();
    } else {
        // Si el usuario no es dueño o la solicitud no existe, no hacemos nada
        $conexion->rollback();
    }

} catch (Exception $e) {
    // Si hay un error de base de datos, revertimos la transacción
    $conexion->rollback();
}

$conexion->close();

// Redirigimos a la URL correcta después de la operación
header('Location: ' . $url_regreso);
exit();
?>