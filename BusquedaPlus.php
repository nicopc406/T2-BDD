<?php
session_start();
if (!isset($_SESSION['rut_usuario'])) {
    header('Location: Login.php');
    exit();
}
require_once 'Conexion.php';


$topicos = $conexion->query("SELECT id_topico, categoria FROM Topicos ORDER BY categoria ASC")->fetch_all(MYSQLI_ASSOC);


$estados = ['Abierto', 'En Progreso', 'Resuelto', 'Cerrado'];
$ambientes = ['Web', 'Movil'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Búsqueda Avanzada - ZeroPressure</title>
    <style>
        body {font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f4f4f9; margin: 0; padding: 0;}
        .navbar {background-color: #333; color: white; padding: 1rem; display: flex; justify-content: space-between; align-items: center;}
        .navbar a {color: white; text-decoration: none;}
        .container {padding: 2rem; max-width: 760px; margin: auto;}
        .form-container {background-color: white; padding: 2rem; border-radius: 8px;}
        .form-group {margin-bottom: 1.5rem;}
        label {display: block; margin-bottom: 0.5rem;}
        input, select {width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;}
        button {width: 100%; padding: 1rem; background-color: #007bff; color: white; border: none; font-size: 1rem;}
    </style>
</head>
<body>
<nav class="navbar">
    <a href="Page.php"><strong>ZeroPressure</strong></a>
    <a href="Salir.php">Cerrar Sesión</a>
</nav>

<div class="container">
    <div class="form-container">
        <h1>Búsqueda Avanzada de Solicitudes</h1>
        <form action="ResultadosBA.php" method="GET">
            <div class="form-group">
                <label for="fecha">Fecha de Envío:</label>
                <input type="date" id="fecha" name="fecha">
            </div>
            <div class="form-group">
                <label for="topico">Tópico:</label>
                <select id="topico" name="id_topico">
                    <option value="">Cualquiera</option>
                    <?php foreach ($topicos as $topico): ?>
                        <option value="<?php echo $topico['id_topico']; ?>"><?php echo htmlspecialchars($topico['categoria']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="ambiente">Ambiente (solo para Funcionalidades):</label>
                <select id="ambiente" name="ambiente">
                    <option value="">Cualquiera</option>
                    <?php foreach ($ambientes as $ambiente): ?>
                        <option value="<?php echo $ambiente; ?>"><?php echo $ambiente; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="estado">Estado:</label>
                <select id="estado" name="estado">
                    <option value="">Cualquiera</option>
                    <?php foreach ($estados as $estado): ?>
                        <option value="<?php echo $estado; ?>"><?php echo $estado; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit">Filtrar Resultados</button>
        </form>
    </div>
</div>
</body>
</html>
<?php $conexion->close(); ?>
