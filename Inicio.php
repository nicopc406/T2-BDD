<?php

require_once 'Conexion.php';
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

if ($_SERVER['REQUEST_METHOD'] === 'POST'){

    $rut = $_POST['rut'];
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $rol = $_POST['rol'];
    $contrasena = $_POST['contrasena'];
    $confirmar_contrasena = $_POST['confirmar_contrasena'];

    if (!validarRut($rut)){

        $mensaje = "Error el RUT ingresado no es valido.";
    }

    if ($contrasena !== $confirmar_contrasena){

        $mensaje = "Error al confirmar la contraseña, por favor intente nuevamente.";
    }

    else{

        $conexion->begin_transaction();

        try {

            $sql_usuario = "INSERT INTO Usuarios (rut_usuario, nombre, email, contrasena) VALUES (?, ?, ?, ?)";
            $stmt_usuario = $conexion->prepare($sql_usuario);
            $stmt_usuario->bind_param('ssss', $rut, $nombre, $email, $contrasena);
            $stmt_usuario->execute();
            $stmt_usuario->close();

            if ($rol === 'ingeniero') {

                $sql_ingeniero = "INSERT INTO Ingenieros (rut_ingeniero) VALUES (?)";
                $stmt_ingeniero = $conexion->prepare($sql_ingeniero);
                $stmt_ingeniero->bind_param('s', $rut);
                $stmt_ingeniero->execute();
                $stmt_ingeniero->close();
            }

            $conexion->commit();
            $mensaje = "Registro exitoso!";
        }

        catch (Exception $e){

            $conexion->rollback();

            if ($e->getCode() == 1062){

                $mensaje = "Error: El RUT o el correo electrónico ya están registrados.";
            }

            else{
                $mensaje = "Error en el registro: " . $e->getMessage();
            }
        }
        $conexion->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f4f4f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 1rem; box-sizing: border-box;}
        .container {background-color: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); width: 100%; max-width: 400px;}
        h1 {text-align: center; color: #333;}
        .form-group {margin-bottom: 1rem;}
        label {display: block; margin-bottom: 0.5rem; color: #555; font-weight: 500;}
        input, select {width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;}
        button {width: 100%; padding: 0.75rem; background-color: #007bff; color: white; border: none; border-radius: 4px; font-size: 1rem; cursor: pointer; transition: background-color 0.2s;}
        button:hover {background-color: #0056b3;}
        .mensaje {text-align: center; margin-top: 1.5rem; padding: 1rem; border-radius: 4px;}
        .exito {background-color: #d4edda; color: #155724;}
        .error {background-color: #f8d7da; color: #721c24;}
        .login-link {text-align: center; margin-top: 1.5rem;}
        .login-link p {color: #555; margin: 0; font-size: 0.9rem;}
        .login-link a {color: #007bff; font-weight: 500; text-decoration: none;}
        .login-link a:hover {text-decoration: underline;}
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
            <select id="rol" name="rol" required>
                <option value="usuario">Usuario</option>
                <option value="ingeniero">Ingeniero</option>
            </select>
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
        </form> <div class="login-link">
            <p> ¿Ya tienes cuenta? <a href="Login.php">Ir a Iniciar Sesión</a></p>
        </div>
    </form>
</div>
</body>
</html>