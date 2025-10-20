<?php

require_once 'Conexion.php';

// Función para validar el RUT chileno
function validarRut($rut){
    $rut = strtoupper(str_replace(['.', '-'], '', $rut));
    if (!preg_match('/^[0-9]{7,8}[0-9K]$/', $rut)){
        return false;
    }
    $cuerpo = substr($rut, 0, -1);
    $dv = substr($rut, -1);
    $suma = 0;
    $multiplo = 2;
    for ($i = strlen($cuerpo) - 1; $i >= 0; $i--){
        $suma += $cuerpo[$i] * $multiplo;
        $multiplo = $multiplo == 7 ? 2 : $multiplo + 1;
    }
    $dv_esperado = 11 - ($suma % 11);
    $dv_esperado = ($dv_esperado == 11) ? '0' : (($dv_esperado == 10) ? 'K' : (string)$dv_esperado);
    return ($dv == $dv_esperado);
}

// Obtener los tópicos para el formulario de registro de ingenieros
$topicos = [];
$resultado_topicos = $conexion->query("SELECT id_topico, categoria FROM Topicos ORDER BY categoria ASC");
if ($resultado_topicos) {
    $topicos = $resultado_topicos->fetch_all(MYSQLI_ASSOC);
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $rut = $_POST['rut'];
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $rol = $_POST['rol'];
    $contrasena = $_POST['contrasena'];
    $confirmar_contrasena = $_POST['confirmar_contrasena'];

    if (!validarRut($rut)){
        $mensaje = "Error: el RUT ingresado no es válido.";
    } elseif ($contrasena !== $confirmar_contrasena){
        $mensaje = "Error al confirmar la contraseña, por favor intente nuevamente.";
    } else {
        $conexion->begin_transaction();
        try {
            // Insertar en la tabla principal de Usuarios
            $sql_usuario = "INSERT INTO Usuarios (rut_usuario, nombre, email, contrasena) VALUES (?, ?, ?, ?)";
            $stmt_usuario = $conexion->prepare($sql_usuario);
            // NOTA: Recuerda que guardar contraseñas en texto plano no es seguro.
            // Para un proyecto real, deberías usar password_hash() aquí.
            $stmt_usuario->bind_param('ssss', $rut, $nombre, $email, $contrasena);
            $stmt_usuario->execute();
            $stmt_usuario->close();

            // Si el rol es ingeniero, realizar las inserciones adicionales
            if ($rol === 'ingeniero') {
                // 1. Insertar en la tabla Ingenieros
                $sql_ingeniero = "INSERT INTO Ingenieros (rut_ingeniero) VALUES (?)";
                $stmt_ingeniero = $conexion->prepare($sql_ingeniero);
                $stmt_ingeniero->bind_param('s', $rut);
                $stmt_ingeniero->execute();
                $stmt_ingeniero->close();

                // 2. Insertar las especialidades seleccionadas
                if (isset($_POST['especialidades']) && is_array($_POST['especialidades'])) {
                    $sql_especialidad = "INSERT INTO Especialidades (rut_ingeniero, id_topico) VALUES (?, ?)";
                    $stmt_esp = $conexion->prepare($sql_especialidad);
                    foreach ($_POST['especialidades'] as $id_topico) {
                        $stmt_esp->bind_param('si', $rut, $id_topico);
                        $stmt_esp->execute();
                    }
                    $stmt_esp->close();
                }
            }

            $conexion->commit();
            $mensaje = "¡Registro exitoso!";
        } catch (Exception $e) {
            $conexion->rollback();
            if ($e->getCode() == 1062) { // Error de clave duplicada
                $mensaje = "Error: El RUT o el correo electrónico ya están registrados.";
            } else {
                $mensaje = "Error en el registro: " . $e->getMessage();
            }
        }
    }
}
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - ZeroPressure</title>
    <style>
        body {font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f4f4f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 1rem; box-sizing: border-box;}
        .container {background-color: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); width: 100%; max-width: 450px;}
        h1 {text-align: center; color: #333;}
        .form-group {margin-bottom: 1.5rem;}
        label {display: block; margin-bottom: 0.5rem; color: #555; font-weight: 500;}
        input, select {width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;}
        button {width: 100%; padding: 0.75rem; background-color: #007bff; color: white; border: none; border-radius: 4px; font-size: 1rem; cursor: pointer; transition: background-color 0.2s;}
        button:hover {background-color: #0056b3;}
        .mensaje {text-align: center; margin-top: 1.5rem; padding: 1rem; border-radius: 4px;}
        .exito {background-color: #d4edda; color: #155724;}
        .error {background-color: #f8d7da; color: #721c24;}
        .login-link {text-align: center; margin-top: 1.5rem;}
        .login-link a {color: #007bff; text-decoration: none;}
        .checkbox-label {display: block; margin-bottom: 8px; font-weight: normal;}
        .checkbox-label input {width: auto; margin-right: 10px;}
    </style>
</head>
<body>
<div class="container">
    <h1>Crea tu cuenta ZeroPressure</h1>

    <?php if (isset($mensaje)): ?>
        <div class="mensaje <?php echo (strpos($mensaje, 'exitoso') !== false) ? 'exito' : 'error'; ?>">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>

    <form action="Inicio.php" method="POST">
        <div class="form-group">
            <label for="rut">RUT:</label>
            <input type="text" id="rut" name="rut" required>
        </div>
        <div class="form-group">
            <label for="nombre">Nombre Completo:</label>
            <input type="text" id="nombre" name="nombre" required>
        </div>
        <div class="form-group">
            <label for="email">Correo Electrónico:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="rol">Registrarse como:</label>
            <select id="rol" name="rol" required onchange="mostrarEspecialidades()">
                <option value="usuario">Usuario</option>
                <option value="ingeniero">Ingeniero</option>
            </select>
        </div>

        <div id="campos-especialidades" class="form-group" style="display: none;">
            <label>Especialidades (selecciona una o más):</label>
            <?php foreach ($topicos as $topico): ?>
                <label class="checkbox-label">
                    <input type="checkbox" name="especialidades[]" value="<?php echo htmlspecialchars($topico['id_topico']); ?>">
                    <?php echo htmlspecialchars($topico['categoria']); ?>
                </label>
            <?php endforeach; ?>
        </div>

        <div class="form-group">
            <label for="contrasena">Contraseña:</label>
            <input type="password" id="contrasena" name="contrasena" required>
        </div>
        <div class="form-group">
            <label for="confirmar_contrasena">Confirmar Contraseña:</label>
            <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" required>
        </div>
        <button type="submit">Registrarse</button>
    </form>
    <div class="login-link">
        <p>¿Ya tienes cuenta? <a href="Login.php">Ir a Iniciar Sesión</a></p>
    </div>
</div>

<script>
    function mostrarEspecialidades() {
        const rolSeleccionado = document.getElementById('rol').value;
        const camposEspecialidades = document.getElementById('campos-especialidades');

        if (rolSeleccionado === 'ingeniero') {
            camposEspecialidades.style.display = 'block';
        } else {
            camposEspecialidades.style.display = 'none';
        }
    }
</script>

</body>
</html>