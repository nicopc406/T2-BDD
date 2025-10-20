<?php
session_start();
if (!isset($_SESSION['rut_usuario'])) {
    header('Location: Login.php');
    exit();
}
require_once 'Conexion.php';

// Base de la consulta
// COALESCE es una función que devuelve el primer valor no nulo de la lista.
// La usamos para mostrar el resumen o la descripción en una sola columna.
$sql = "SELECT titulo, topico, COALESCE(resumen, descripcion) AS detalle FROM Vista_Solicitudes WHERE 1=1";

$params = [];
$types = '';

// Añadir filtros dinámicamente
if (!empty($_GET['fecha'])) {
    $sql .= " AND fecha = ?";
    $params[] = $_GET['fecha'];
    $types .= 's';
}
if (!empty($_GET['id_topico'])) {
    $sql .= " AND id_topico = ?";
    $params[] = $_GET['id_topico'];
    $types .= 'i';
}
if (!empty($_GET['ambiente'])) {
    $sql .= " AND ambiente = ?";
    $params[] = $_GET['ambiente'];
    $types .= 's';
}
if (!empty($_GET['estado'])) {
    $sql .= " AND estado = ?";
    $params[] = $_GET['estado'];
    $types .= 's';
}

$stmt = $conexion->prepare($sql);
if (!empty($params)) {
    // El operador '...' (splat) expande el array de parámetros
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$resultado_query = $stmt->get_result();
$resultados = $resultado_query ? $resultado_query->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados de Búsqueda Avanzada - ZeroPressure</title>
    <style>
        body {font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f4f4f9; margin: 0; padding: 0;}
        .navbar {background-color: #333; color: white; padding: 1rem; display: flex; justify-content: space-between; align-items: center;}
        .navbar a {color: white; text-decoration: none;}
        .container {padding: 2rem; max-width: 960px; margin: auto;}
        .table-container {background-color: white; padding: 2rem; border-radius: 8px;}
        table {width: 100%; border-collapse: collapse; margin-top: 1.5rem;}
        th, td {padding: 12px; border: 1px solid #ddd; text-align: left;}
    </style>
</head>
<body>
<nav class="navbar">
    <a href="Page.php"><strong>ZeroPressure</strong></a>
    <a href="Salir.php">Cerrar Sesión</a>
</nav>

<div class="container">
    <div class="table-container">
        <h1>Resultados de la Búsqueda Avanzada</h1>

        <?php if (!empty($resultados)): ?>
            <table>
                <thead>
                <tr>
                    <th>Título de la Solicitud</th>
                    <th>Tópico</th>
                    <th>Resumen/Descripción</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($resultados as $fila): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fila['titulo']); ?></td>
                        <td><?php echo htmlspecialchars($fila['topico']); ?></td>
                        <td><?php echo htmlspecialchars($fila['detalle']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No se encontraron solicitudes que coincidan con los filtros aplicados.</p>
        <?php endif; ?>

        <a href="BusquedaPlus.php" style="display: inline-block; margin-top: 1.5rem;">Realizar otra búsqueda</a>
    </div>
</div>
</body>
</html>
<?php
$stmt->close();
$conexion->close();
?>
