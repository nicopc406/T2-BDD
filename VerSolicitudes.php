<?php
session_start();
if (!isset($_SESSION['rut_usuario'])) {
    header('Location: Login.php');
    exit();
}


if (!isset($_GET['tipo']) || !in_array($_GET['tipo'], ['Funcionalidad', 'Error'])) {
    die("Tipo de solicitud no válido.");
}

$tipo_solicitud = $_GET['tipo'];
$titulo_pagina = "Mis Solicitudes de " . $tipo_solicitud;
$rut_usuario_actual = $_SESSION['rut_usuario'];

require_once 'Conexion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($titulo_pagina); ?> - ZeroPressure</title>
    <style>
        body {font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f4f4f9; margin: 0; padding: 0;}
        .navbar {background-color: #333; color: white; padding: 1rem; display: flex; justify-content: space-between; align-items: center;}
        .navbar a {color: white; text-decoration: none; padding: 0.5rem 1rem;}
        .navbar a:hover {background-color: #555; border-radius: 4px;}
        .container {padding: 2rem; max-width: 960px; margin: auto;}
        .table-container {background-color: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);}
        h1 {color: #333;}
        table {width: 100%; border-collapse: collapse; margin-top: 1.5rem;}
        th, td {padding: 12px; border: 1px solid #ddd; text-align: left;}
        th {background-color: #f8f9fa;}
        .actions a {margin-right: 10px; text-decoration: none; color: #007bff;}
        .actions a.delete {color: #dc3545;}
    </style>
</head>
<body>

<nav class="navbar">
    <a href="Page.php"><strong>ZeroPressure</strong></a>
    <div>
        <span>Hola, <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></span>
        <a href="Salir.php">Cerrar Sesión</a>
    </div>
</nav>

<div class="container">
    <div class="table-container">
        <h1><?php echo htmlspecialchars($titulo_pagina); ?></h1>

        <?php
        $sql = "SELECT id_solicitud, titulo, estado, topico FROM Vista_Solicitudes WHERE tipo = ? AND rut_usuario = ? ORDER BY fecha DESC";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('ss', $tipo_solicitud, $rut_usuario_actual);
        $stmt->execute();
        $resultado = $stmt->get_result();
        ?>
        <table>
            <thead>
            <tr>
                <th>Título</th>
                <th>Tópico</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($resultado && $resultado->num_rows > 0): ?>
                <?php while($fila = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fila['titulo']); ?></td>
                        <td><?php echo htmlspecialchars($fila['topico']); ?></td>
                        <td><?php echo htmlspecialchars($fila['estado']); ?></td>
                        <td class="actions">
                            <?php if ($fila['estado'] !== 'En Progreso'): ?>
                                <a href="EditarSolicitud.php?id=<?php echo $fila['id_solicitud']; ?>&tipo=<?php echo urlencode($tipo_solicitud); ?>">Editar</a>
                                <a href="Eliminar.php?id=<?php echo $fila['id_solicitud']; ?>&tipo=<?php echo urlencode($tipo_solicitud); ?>" class="delete" onclick="return confirm('¿Estás seguro de que quieres eliminar esta solicitud?');">Eliminar</a>
                            <?php else: ?>
                                <span>Bloqueado</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" style="text-align: center;">No has creado solicitudes de este tipo.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
<?php
if (isset($stmt)) {
    $stmt->close();
}
$conexion->close();
?>
