<?php

session_start();

if (!isset($_SESSION['rut_usuario'])){
    header('Location: Login.php');
    exit();
}

require_once 'Conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST'){

    $tipo = $_POST['tipo'];
    $titulo = $_POST['titulo'];
    $id_topico = $_POST['id_topico'];
    $rut_usuario = $_SESSION['rut_usuario'];
    $conexion->begin_transaction();

    try {

        $sql_solicitud = "INSERT INTO Solicitudes (tipo, titulo, id_topico, rut_usuario) VALUES (?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql_solicitud);
        $stmt->bind_param('ssis', $tipo, $titulo, $id_topico, $rut_usuario);
        $stmt->execute();
        $id_solicitud_nueva = $conexion->insert_id;
        $stmt->close();

        if ($tipo === 'Funcionalidad'){

            $ambiente = $_POST['ambiente'];
            $resumen = $_POST['resumen'];
            $sql_funcionalidad = "INSERT INTO Solicitudes_Funcionalidades (id_funcion, ambiente, resumen) VALUES (?, ?, ?)";
            $stmt_f = $conexion->prepare($sql_funcionalidad);
            $stmt_f->bind_param('iss', $id_solicitud_nueva, $ambiente, $resumen);
            $stmt_f->execute();
            $stmt_f->close();
        }

        elseif ($tipo === 'Error'){

            $descripcion = $_POST['descripcion'];
            $sql_error = "INSERT INTO Solicitudes_Errores (id_error, descripcion) VALUES (?, ?)";
            $stmt_e = $conexion->prepare($sql_error);
            $stmt_e->bind_param('is', $id_solicitud_nueva, $descripcion);
            $stmt_e->execute();
            $stmt_e->close();
        }

        $conexion->commit();
        $mensaje = "Solicitud creada con exito!";
    }

    catch (Exception $e){

        $conexion->rollback();
        $mensaje = "Error al crear la solicitud: " . $e->getMessage();
    }
}

$topicos = [];
$resultado_topicos = $conexion->query("SELECT id_topico, categoria FROM Topicos ORDER BY categoria ASC");

if ($resultado_topicos){

    $topicos = $resultado_topicos->fetch_all(MYSQLI_ASSOC);
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Nueva Solicitud - ZeroPressure</title>
    <style>
        body {font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f4f4f9; margin: 0; padding: 0;}
        .navbar {background-color: #333; color: white; padding: 1rem; display: flex; justify-content: space-between; align-items: center;}
        .navbar a {color: white; text-decoration: none; padding: 0.5rem 1rem;}
        .navbar a:hover {background-color: #555; border-radius: 4px;}
        .container {padding: 2rem; max-width: 760px; margin: auto;}
        .form-container {background-color: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);}
        .form-group {margin-bottom: 1.5rem; }
        label {display: block; margin-bottom: 0.5rem; font-weight: 500; }
        input, select, textarea {width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;}
        button {width: 100%; padding: 1rem; background-color: #007bff; color: white; border: none; border-radius: 4px; font-size: 1rem; cursor: pointer;}
        .mensaje {text-align: center; margin-bottom: 1.5rem; padding: 1rem; border-radius: 4px;}
        .exito {background-color: #d4edda; color: #155724;}
        .error {background-color: #f8d7da; color: #721c24;}
        .campo-extra {display: none;}
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
    <div class="form-container">
        <h1>Crear Nueva Solicitud</h1>

        <?php if (isset($mensaje)): ?>
            <div class="mensaje <?php echo (strpos($mensaje, 'éxito') !== false) ? 'exito' : 'error'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <form action="CrearSolicitudes.php" method="POST">
            <div class="form-group">
                <label for="tipo">Tipo de Solicitud:</label>
                <select id="tipo" name="tipo" required onchange="mostrarCampos()">
                    <option value="">-- Seleccione un tipo --</option>
                    <option value="Funcionalidad">Nueva Funcionalidad</option>
                    <option value="Error">Reportar Error</option>
                </select>
            </div>

            <div class="form-group">
                <label for="titulo">Título:</label>
                <input type="text" id="titulo" name="titulo" required>
            </div>

            <div class="form-group">
                <label for="id_topico">Tópico:</label>
                <select id="id_topico" name="id_topico" required>
                    <option value="">-- Seleccione un tópico --</option>
                    <?php foreach ($topicos as $topico): ?>
                        <option value="<?php echo htmlspecialchars($topico['id_topico']); ?>">
                            <?php echo htmlspecialchars($topico['categoria']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="campos-funcionalidad" class="campo-extra">
                <div class="form-group">
                    <label for="ambiente">Ambiente:</label>
                    <select id="ambiente" name="ambiente">
                        <option value="Web">Web</option>
                        <option value="Movil">Móvil</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="resumen">Resumen:</label>
                    <textarea id="resumen" name="resumen" rows="4"></textarea>
                </div>
            </div>

            <div id="campos-error" class="campo-extra">
                <div class="form-group">
                    <label for="descripcion">Descripción del Error:</label>
                    <textarea id="descripcion" name="descripcion" rows="4"></textarea>
                </div>
            </div>

            <button type="submit">Enviar Solicitud</button>
        </form>
    </div>
</div>

<script>
    function mostrarCampos() {
        const tipoSeleccionado = document.getElementById('tipo').value;
        const camposFuncionalidad = document.getElementById('campos-funcionalidad');
        const camposError = document.getElementById('campos-error');

        // Ocultamos ambos contenedores
        camposFuncionalidad.style.display = 'none';
        camposError.style.display = 'none';

        // Mostramos el contenedor correspondiente al tipo seleccionado
        if (tipoSeleccionado === 'Funcionalidad') {
            camposFuncionalidad.style.display = 'block';
        } else if (tipoSeleccionado === 'Error') {
            camposError.style.display = 'block';
        }
    }
</script>
</body>
</html>
