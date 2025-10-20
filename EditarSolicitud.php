<?php
session_start();
require_once 'Conexion.php';

// Guardián: si no hay sesión, ID o tipo, redirigimos
if (!isset($_SESSION['rut_usuario']) || !isset($_GET['id']) || !isset($_GET['tipo'])) {
    header('Location: Page.php');
    exit();
}

$id_solicitud = (int)$_GET['id'];
$tipo_solicitud = $_GET['tipo'];
$rut_usuario = $_SESSION['rut_usuario'];
$mensaje = '';
$solicitud = null;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo_nuevo = $_POST['titulo'];
    $id_topico_nuevo = (int)$_POST['id_topico'];

    $conexion->begin_transaction();
    try {
        
        $sql_padre = "UPDATE Solicitudes SET titulo = ?, id_topico = ? 
                      WHERE id_solicitud = ? AND rut_usuario = ? AND estado != 'En Progreso'";
        $stmt_padre = $conexion->prepare($sql_padre);
        $stmt_padre->bind_param('siss', $titulo_nuevo, $id_topico_nuevo, $id_solicitud, $rut_usuario);
        $stmt_padre->execute();

        if ($stmt_padre->affected_rows > 0) {
            
            if ($tipo_solicitud === 'Funcionalidad') {
                $resumen_nuevo = $_POST['resumen'];
                $sql_hija = "UPDATE Solicitudes_Funcionalidades SET resumen = ? WHERE id_funcion = ?";
                $stmt_hija = $conexion->prepare($sql_hija);
                $stmt_hija->bind_param('si', $resumen_nuevo, $id_solicitud);
                $stmt_hija->execute();
            } else { 
                $descripcion_nueva = $_POST['descripcion'];
                $sql_hija = "UPDATE Solicitudes_Errores SET descripcion = ? WHERE id_error = ?";
                $stmt_hija = $conexion->prepare($sql_hija);
                $stmt_hija->bind_param('si', $descripcion_nueva, $id_solicitud);
                $stmt_hija->execute();
            }
            $conexion->commit();
            $mensaje = "Solicitud actualizada con éxito.";
        } else {
            
            $conexion->rollback();
            $mensaje = "Error: No se pudo actualizar la solicitud (quizás está 'En Progreso' o no eres el dueño).";
        }

    } catch (Exception $e) {
        $conexion->rollback();
        $mensaje = "Error al actualizar: " . $e->getMessage();
    }
}


$sql_carga = "SELECT * FROM Vista_Solicitudes WHERE id_solicitud = ? AND rut_usuario = ?";
$stmt_carga = $conexion->prepare($sql_carga);
$stmt_carga->bind_param('is', $id_solicitud, $rut_usuario);
$stmt_carga->execute();
$resultado = $stmt_carga->get_result();

if ($resultado->num_rows === 1) {
    $solicitud = $resultado->fetch_assoc();
    
    if ($solicitud['estado'] === 'En Progreso') {
        die("Error: Las solicitudes 'En Progreso' no se pueden modificar.");
    }
} else {
    die("Error: Solicitud no encontrada o no te pertenece.");
}


$topicos = $conexion->query("SELECT id_topico, categoria FROM Topicos")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Solicitud - ZeroPressure</title>
    <style>
        body {font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f4f4f9; margin: 0; padding: 0;}
        .navbar {background-color: #333; color: white; padding: 1rem; display: flex; justify-content: space-between; align-items: center;}
        .navbar a {color: white; text-decoration: none; padding: 0.5rem 1rem;}
        .container {padding: 2rem; max-width: 760px; margin: auto;}
        .form-container {background-color: white; padding: 2rem; border-radius: 8px;}
        .form-group {margin-bottom: 1.5rem; }
        label {display: block; margin-bottom: 0.5rem; font-weight: 500; }
        input, select, textarea {width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;}
        button {width: 100%; padding: 1rem; background-color: #007bff; color: white; border: none; font-size: 1rem;}
        .mensaje {text-align: center; margin-bottom: 1.5rem; padding: 1rem; border-radius: 4px;}
        .exito {background-color: #d4edda; color: #155724;}
        .error {background-color: #f8d7da; color: #721c24;}
    </style>
</head>
<body>
<nav class="navbar">
    <a href="Page.php"><strong>ZeroPressure</strong></a>
    <a href="Salir.php">Cerrar Sesión</a>
</nav>

<div class="container">
    <div class="form-container">
        <h1>Editar Solicitud (ID: <?php echo $id_solicitud; ?>)</h1>

        <?php if (!empty($mensaje)): ?>
            <div class="mensaje <?php echo (strpos($mensaje, 'éxito') !== false) ? 'exito' : 'error'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <form action="EditarSolicitud.php?id=<?php echo $id_solicitud; ?>&tipo=<?php echo $tipo_solicitud; ?>" method="POST">
            <div class="form-group">
                <label>Tipo:</label>
                <input type="text" value="<?php echo htmlspecialchars($solicitud['tipo']); ?>" disabled>
            </div>
            
            <div class="form-group">
                <label for="titulo">Título:</label>
                <input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($solicitud['titulo']); ?>" required>
            </div>

            <div class="form-group">
                <label for="id_topico">Tópico:</label>
                <select id="id_topico" name="id_topico" required>
                    <?php foreach ($topicos as $topico): ?>
                        <option value="<?php echo $topico['id_topico']; ?>" <?php if ($topico['id_topico'] == $solicitud['id_topico']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($topico['categoria']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if ($tipo_solicitud === 'Funcionalidad'): ?>
                <div class="form-group">
                    <label for="resumen">Resumen:</label>
                    <textarea id="resumen" name="resumen" rows="4"><?php echo htmlspecialchars($solicitud['resumen']); ?></textarea>
                </div>
            <?php else: // 'Error' ?>
                <div class="form-group">
                    <label for="descripcion">Descripción del Error:</label>
                    <textarea id="descripcion" name="descripcion" rows="4"><?php echo htmlspecialchars($solicitud['descripcion']); ?></textarea>
                </div>
            <?php endif; ?>

            <button type="submit">Actualizar Solicitud</button>
        </form>
         <a href="VerSolicitudes.php?tipo=<?php echo $tipo_solicitud; ?>" style="display: block; text-align: center; margin-top: 1rem;">Volver</a>
    </div>
</div>
</body>
</html>