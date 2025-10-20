<?php
session_start();
require_once 'Conexion.php';


if (!isset($_SESSION['rut_usuario']) || $_SESSION['rol'] !== 'ingeniero' || !isset($_GET['id'])) {
    header('Location: Page.php');
    exit();
}

$id_solicitud = (int)$_GET['id'];
$rut_ingeniero = $_SESSION['rut_usuario'];
$mensaje = '';



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado'])) {
    $nuevo_estado = $_POST['estado'];
    $sql_estado = "UPDATE Solicitudes SET estado = ? WHERE id_solicitud = ?";
    $stmt_estado = $conexion->prepare($sql_estado);
    $stmt_estado->bind_param('si', $nuevo_estado, $id_solicitud);
    $stmt_estado->execute();
    $mensaje = "Estado actualizado a '$nuevo_estado'.";
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_resena'])) {
    $observacion = trim($_POST['observacion']);
    if (!empty($observacion)) {
        $sql_resena = "INSERT INTO Resenas (id_solicitud, rut_ingeniero, observacion) VALUES (?, ?, ?)";
        $stmt_resena = $conexion->prepare($sql_resena);
        $stmt_resena->bind_param('iss', $id_solicitud, $rut_ingeniero, $observacion);
        $stmt_resena->execute();
        $mensaje = "Reseña añadida.";
    }
}


if (isset($_GET['eliminar_resena_id'])) {
    $id_resena = (int)$_GET['eliminar_resena_id'];
    
    $sql_del_resena = "DELETE FROM Resenas WHERE id_resena = ? AND rut_ingeniero = ?";
    $stmt_del = $conexion->prepare($sql_del_resena);
    $stmt_del->bind_param('is', $id_resena, $rut_ingeniero);
    $stmt_del->execute();
    
    if ($stmt_del->affected_rows > 0) {
        $mensaje = "Reseña eliminada.";
    } else {
        $mensaje = "Error: No se pudo eliminar la reseña (quizás no es tuya).";
    }
    
    header("Location: GestionarSolicitud.php?id=$id_solicitud&msg=" . urlencode($mensaje));
    exit();
}


if (isset($_GET['msg'])) {
    $mensaje = $_GET['msg'];
}



$sql_sol = "SELECT * FROM Vista_Solicitudes WHERE id_solicitud = ?";
$stmt_sol = $conexion->prepare($sql_sol);
$stmt_sol->bind_param('i', $id_solicitud);
$stmt_sol->execute();
$solicitud = $stmt_sol->get_result()->fetch_assoc();
if (!$solicitud) { die("Error: Solicitud no encontrada."); }


$sql_res = "SELECT r.*, u.nombre AS nombre_ingeniero 
            FROM Resenas r 
            JOIN Usuarios u ON r.rut_ingeniero = u.rut_usuario
            WHERE r.id_solicitud = ? ORDER BY r.fecha_resena DESC";
$stmt_res = $conexion->prepare($sql_res);
$stmt_res->bind_param('i', $id_solicitud);
$stmt_res->execute();
$resenas = $stmt_res->get_result()->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Solicitud - ZeroPressure</title>
    <style>
        body {font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f4f4f9; margin: 0; padding: 0;}
        .navbar {background-color: #333; color: white; padding: 1rem; display: flex; justify-content: space-between; align-items: center;}
        .navbar a {color: white; text-decoration: none; padding: 0.5rem 1rem;}
        .container {padding: 2rem; max-width: 960px; margin: auto;}
        .grid-container {display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;}
        .detalle, .acciones, .resenas {background-color: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);}
        .resenas {grid-column: 1 / -1; margin-top: 2rem;}
        .resena-item {border-bottom: 1px solid #eee; padding: 1rem 0;}
        .resena-meta {font-size: 0.9em; color: #555;}
        textarea, select {width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; margin-bottom: 1rem;}
        button {width: 100%; padding: 0.75rem; background-color: #007bff; color: white; border: none; font-size: 1rem; cursor: pointer;}
        .mensaje {text-align: center; margin-bottom: 1.5rem; padding: 1rem; border-radius: 4px; background-color: #d4edda; color: #155724;}
    </style>
</head>
<body>
<nav class="navbar">
    <a href="Page.php"><strong>ZeroPressure</strong></a>
    <a href="Salir.php">Cerrar Sesión</a>
</nav>
<div class="container">
    <?php if ($mensaje): ?>
        <div class="mensaje"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>

    <div class="grid-container">
        <div class="detalle">
            <h1><?php echo htmlspecialchars($solicitud['titulo']); ?></h1>
            <p><strong>Solicitante:</strong> <?php echo htmlspecialchars($solicitud['solicitante']); ?></p>
            <p><strong>Tópico:</strong> <?php echo htmlspecialchars($solicitud['topico']); ?></p>
            <p><strong>Estado Actual:</strong> <?php echo htmlspecialchars($solicitud['estado']); ?></p>
            <hr>
            <?php if ($solicitud['tipo'] === 'Funcionalidad'): ?>
                <p><strong>Resumen:</strong> <?php echo nl2br(htmlspecialchars($solicitud['resumen'])); ?></p>
            <?php else: ?>
                <p><strong>Descripción:</strong> <?php echo nl2br(htmlspecialchars($solicitud['descripcion'])); ?></p>
            <?php endif; ?>
        </div>

        <div class="acciones">
            <h4>Acciones</h4>
            <form action="GestionarSolicitud.php?id=<?php echo $id_solicitud; ?>" method="POST">
                <label for="estado">Actualizar Estado:</label>
                <select id="estado" name="estado">
                    <option value="Abierto" <?php if ($solicitud['estado'] == 'Abierto') echo 'selected'; ?>>Abierto</option>
                    <option value="En Progreso" <?php if ($solicitud['estado'] == 'En Progreso') echo 'selected'; ?>>En Progreso</option>
                    <option value="Resuelto" <?php if ($solicitud['estado'] == 'Resuelto') echo 'selected'; ?>>Resuelto</option>
                    <option value="Cerrado" <?php if ($solicitud['estado'] == 'Cerrado') echo 'selected'; ?>>Cerrado</option>
                </select>
                <button type="submit" name="cambiar_estado">Actualizar</button>
            </form>
            <hr style="margin: 1.5rem 0;">
            <form action="GestionarSolicitud.php?id=<?php echo $id_solicitud; ?>" method="POST">
                <label for="observacion">Añadir Reseña:</label>
                <textarea id="observacion" name="observacion" rows="5" required></textarea>
                <button type="submit" name="agregar_resena">Añadir</button>
            </form>
        </div>

        <div class="resenas">
            <h2>Reseñas y Observaciones</h2>
            <?php if (empty($resenas)): ?>
                <p>Aún no hay reseñas para esta solicitud.</p>
            <?php else: ?>
                <?php foreach ($resenas as $resena): ?>
                    <div class="resena-item">
                        <p><?php echo nl2br(htmlspecialchars($resena['observacion'])); ?></p>
                        <p class="resena-meta">
                            Por: <strong><?php echo htmlspecialchars($resena['nombre_ingeniero']); ?></strong> el <?php echo $resena['fecha_resena']; ?>
                            
                            <?php if ($resena['rut_ingeniero'] === $rut_ingeniero): ?>
                                <a href="GestionarSolicitud.php?id=<?php echo $id_solicitud; ?>&eliminar_resena_id=<?php echo $resena['id_resena']; ?>" 
                                   style="color: red; float: right;" 
                                   onclick="return confirm('¿Estás seguro de eliminar esta reseña?');">
                                   Eliminar
                                </a>
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <a href="VerAsignaciones.php" style="display: block; text-align: center; margin-top: 2rem;">Volver a Mis Asignaciones</a>
</div>
</body>
</html>