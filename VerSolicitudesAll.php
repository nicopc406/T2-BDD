<?php
session_start();
// Redirigir si no es ingeniero o no ha iniciado sesión
if (!isset($_SESSION['rut_usuario']) || $_SESSION['rol'] !== 'ingeniero') {
    header('Location: Page.php');
    exit();
}

// Validar el tipo de solicitud desde la URL
if (!isset($_GET['tipo']) || !in_array($_GET['tipo'], ['Funcionalidad', 'Error'])) {
    die("Tipo de solicitud no válido.");
}

$tipo_solicitud = $_GET['tipo'];
$titulo_pagina = "Solicitudes de " . $tipo_solicitud;

require_once 'Conexion.php';
?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Todas las <?php echo $titulo_pagina; ?> - ZeroPressure</title>
        <style>
            body {font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f4f4f9; margin: 0; padding: 0;}
            .navbar {background-color: #333; color: white; padding: 1rem; display: flex; justify-content: space-between; align-items: center;}
            .navbar a {color: white; text-decoration: none; padding: 0.5rem 1rem;}
            .navbar a:hover {background-color: #555; border-radius: 4px;}
            .container {padding: 2rem; max-width: 1200px; margin: auto;}
            .table-container {background-color: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);}
            h1 {color: #333;}
            table {width: 100%; border-collapse: collapse; margin-top: 1.5rem;}
            th, td {padding: 12px; border: 1px solid #ddd; text-align: left;}
            th {background-color: #f8f9fa;}
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
            <h1>Todas las <?php echo $titulo_pagina; ?></h1>

            <?php if ($tipo_solicitud === 'Funcionalidad'): ?>
                <?php
                $sql = "SELECT titulo, ambiente, resumen, topico, solicitante, estado FROM VistaSolicitudesDetalladas WHERE tipo = 'Funcionalidad' ORDER BY fecha DESC";
                $resultado = $conexion->query($sql);
                ?>
                <table>
                    <thead>
                    <tr>
                        <th>Título</th> <th>Ambiente</th> <th>Resumen</th> <th>Tópico</th> <th>Solicitante</th> <th>Estado</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($resultado && $resultado->num_rows > 0): ?>
                        <?php while($fila = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($fila['titulo']); ?></td>
                                <td><?php echo htmlspecialchars($fila['ambiente']); ?></td>
                                <td><?php echo htmlspecialchars($fila['resumen']); ?></td>
                                <td><?php echo htmlspecialchars($fila['topico']); ?></td>
                                <td><?php echo htmlspecialchars($fila['solicitante']); ?></td>
                                <td><?php echo htmlspecialchars($fila['estado']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center;">No hay solicitudes de funcionalidad.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            <?php elseif ($tipo_solicitud === 'Error'): ?>
                <?php
                $sql = "SELECT titulo, descripcion, fecha, topico, solicitante, estado FROM VistaSolicitudesDetalladas WHERE tipo = 'Error' ORDER BY fecha DESC";
                $resultado = $conexion->query($sql);
                ?>
                <table>
                    <thead>
                    <tr>
                        <th>Título</th> <th>Descripción</th> <th>Fecha</th> <th>Tópico</th> <th>Autor</th> <th>Estado</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($resultado && $resultado->num_rows > 0): ?>
                        <?php while($fila = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($fila['titulo']); ?></td>
                                <td><?php echo htmlspecialchars($fila['descripcion']); ?></td>
                                <td><?php echo htmlspecialchars($fila['fecha']); ?></td>
                                <td><?php echo htmlspecialchars($fila['topico']); ?></td>
                                <td><?php echo htmlspecialchars($fila['solicitante']); ?></td>
                                <td><?php echo htmlspecialchars($fila['estado']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center;">No hay solicitudes de error.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    </body>
    </html>
<?php $conexion->close(); ?>