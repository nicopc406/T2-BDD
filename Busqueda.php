<?php
session_start();
if (!isset($_SESSION['rut_usuario'])) {
    header('Location: Login.php');
    exit();
}

require_once 'Conexion.php';

// Inicializamos la variable de resultados
$resultados = [];
$termino_busqueda = '';

// Verificamos si se está realizando una búsqueda simple
if (isset($_GET['termino']) && !empty($_GET['termino'])) {
    $termino_busqueda = $_GET['termino'];

    // El '%' es un comodín que significa "cualquier cadena de caracteres"
    $termino_like = '%' . $termino_busqueda . '%';

    // Usamos la vista para obtener toda la información necesaria
    $sql = "SELECT titulo, tipo, topico, solicitante, estado 
            FROM Vista_Solicitudes 
            WHERE titulo LIKE ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('s', $termino_like);
    $stmt->execute();
    $resultado_query = $stmt->get_result();

    if ($resultado_query) {
        $resultados = $resultado_query->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados de Búsqueda - ZeroPressure</title>
    <style>
        body {font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f4f4f9; margin: 0; padding: 0;}
        .navbar {background-color: #333; color: white; padding: 1rem; display: flex; justify-content: space-between; align-items: center;}
        .navbar a {color: white; text-decoration: none; padding: 0.5rem 1rem;}
        .container {padding: 2rem; max-width: 960px; margin: auto;}
        .table-container {background-color: white; padding: 2rem; border-radius: 8px;}
        table {width: 100%; border-collapse: collapse; margin-top: 1.5rem;}
        th, td {padding: 12px; border: 1px solid #ddd; text-align: left;}
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
        <h1>Resultados para: "<?php echo htmlspecialchars($termino_busqueda); ?>"</h1>

        <?php if (!empty($resultados)): ?>
            <table>
                <thead>
                <tr>
                    <th>Título</th>
                    <th>Tipo</th>
                    <th>Tópico</th>
                    <th>Solicitante</th>
                    <th>Estado</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($resultados as $fila): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fila['titulo']); ?></td>
                        <td><?php echo htmlspecialchars($fila['tipo']); ?></td>
                        <td><?php echo htmlspecialchars($fila['topico']); ?></td>
                        <td><?php echo htmlspecialchars($fila['solicitante']); ?></td>
                        <td><?php echo htmlspecialchars($fila['estado']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No se encontraron solicitudes que coincidan con tu búsqueda.</p>
        <?php endif; ?>

        <a href="Page.php" style="display: inline-block; margin-top: 1.5rem;">Volver al Panel</a>
    </div>
</div>

</body>
</html>
<?php $conexion->close(); ?>
