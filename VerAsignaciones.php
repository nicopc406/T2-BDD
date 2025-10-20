<?php
session_start();
// Redirigir si no es ingeniero o no ha iniciado sesión
if (!isset($_SESSION['rut_usuario']) || $_SESSION['rol'] !== 'ingeniero') {
    header('Location: Page.php');
    exit();
}

require_once 'Conexion.php';
$rut_ingeniero_actual = $_SESSION['rut_usuario'];

$sql = "
    SELECT 
        s.id_solicitud,
        s.titulo,
        s.tipo,
        s.estado,
        s.fecha,
        u.nombre AS nombre_solicitante,
        t.categoria AS nombre_topico
    FROM Asignaciones a
    JOIN Solicitudes s ON a.id_asignacion = s.id_solicitud
    JOIN Usuarios u ON s.rut_usuario = u.rut_usuario
    JOIN Topicos t ON s.id_topico = t.id_topico
    WHERE a.rut_ingeniero = ?
    ORDER BY s.estado ASC, s.fecha DESC
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param('s', $rut_ingeniero_actual);
$stmt->execute();
$resultado = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Solicitudes Asignadas - ZeroPressure</title>
    <style>
        body {font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f4f4f9; margin: 0; padding: 0;}
        .navbar {background-color: #333; color: white; padding: 1rem; display: flex; justify-content: space-between; align-items: center;}
        .navbar a {color: white; text-decoration: none; padding: 0.5rem 1rem;}
        .navbar a:hover {background-color: #555; border-radius: 4px;}
        .container {padding: 2rem; max-width: 960px; margin: auto;}
        .table-container {background-color: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);}
        table {width: 100%; border-collapse: collapse; margin-top: 1.5rem;}
        th, td {padding: 12px; border: 1px solid #ddd; text-align: left;}
        th {background-color: #f8f9fa;}
        tr:nth-child(even) {background-color: #f2f2f2;}
        .actions a {margin-right: 10px; text-decoration: none; color: #007bff;}
    </style>
</head>
<body>

<nav class="navbar">
    <a href="Page.php"><strong>ZeroPressure</strong></a>
    <div>
        <span>Hola, <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?> (<?php echo htmlspecialchars($_SESSION['rol']); ?>)</span>
        <a href="Salir.php">Cerrar Sesión</a>
    </div>
</nav>

<div class="container">
    <div class="table-container">
        <h1>Mis Solicitudes Asignadas</h1>
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Fecha</th>
                <th>Solicitante</th>
                <th>Tópico</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($resultado && $resultado->num_rows > 0): ?>
                <?php while($fila = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fila['id_solicitud']); ?></td>
                        <td><?php echo htmlspecialchars($fila['titulo']); ?></td>
                        <td><?php echo htmlspecialchars($fila['tipo']); ?></td>
                        <td><?php echo htmlspecialchars($fila['estado']); ?></td>
                        <td><?php echo htmlspecialchars($fila['fecha']); ?></td>
                        <td><?php echo htmlspecialchars($fila['nombre_solicitante']); ?></td>
                        <td><?php echo htmlspecialchars($fila['nombre_topico']); ?></td>
                        <td class="actions">
                            <a href="GestionarSolicitud.php?id=<?php echo $fila['id_solicitud']; ?>">Gestionar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align: center;">No tienes ninguna solicitud asignada.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
<?php
$stmt->close();
$conexion->close();
?>
