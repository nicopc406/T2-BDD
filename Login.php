<?php

session_start();

if (isset($_SESSION['rut_usuario'])){

    header('Location: Page.php');
    exit();
}

require_once 'Conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST'){

    $rut = $_POST['rut'];
    $contrasena_ingresada = $_POST['contrasena'];
    $sql = "SELECT rut_usuario, nombre, email, contrasena FROM Usuarios WHERE rut_usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('s', $rut);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1){

        $usuario = $resultado->fetch_assoc();
        $contrasena_guardada = $usuario['contrasena'];

        if ($contrasena_ingresada === $contrasena_guardada){

            $_SESSION['rut_usuario'] = $usuario['rut_usuario'];
            $_SESSION['nombre_usuario'] = $usuario['nombre'];
            $_SESSION['email_usuario'] = $usuario['email'];
            $sql_ingeniero = "SELECT rut_ingeniero FROM Ingenieros WHERE rut_ingeniero = ?";
            $stmt_ingeniero = $conexion->prepare($sql_ingeniero);
            $stmt_ingeniero->bind_param('s', $rut);
            $stmt_ingeniero->execute();
            $resultado_ingeniero = $stmt_ingeniero->get_result();

            if ($resultado_ingeniero->num_rows === 1){

                $_SESSION['rol'] = 'ingeniero';
            }

            else{

                $_SESSION['rol'] = 'usuario';
            }

            $stmt_ingeniero->close();
            header('Location: Page.php');
            exit();
        }

        else{

            $error = "RUT o contraseña incorrectos.";
        }
    }

    else{

        $error = "RUT o contraseña incorrectos.";
    }

    $stmt->close();
    $conexion->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - ZeroPressure</title>
    <style>
        body {font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f4f4f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 1rem; box-sizing: border-box;}
        .container {background-color: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); width: 100%; max-width: 400px;}
        h1 {text-align: center; color: #333;}
        .form-group {margin-bottom: 1rem;}
        label {display: block; margin-bottom: 0.5rem; color: #555; font-weight: 500;}
        input {width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;}
        button {width: 100%; padding: 0.75rem; background-color: #28a745; color: white; border: none; border-radius: 4px; font-size: 1rem; cursor: pointer; transition: background-color 0.2s;}
        button:hover {background-color: #218838;}
        .error {text-align: center; margin-top: 1.5rem; padding: 1rem; border-radius: 4px; background-color: #f8d7da; color: #721c24;}
        .register-link {text-align: center; margin-top: 1rem;}
        .register-link a {color: #007bff; text-decoration: none;}
        .register-link a:hover {text-decoration: underline;}
    </style>
</head>
<body>
<div class="container">
    <h1>Iniciar Sesión</h1>

    <?php if (isset($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="Login.php" method="POST">
        <div class="form-group">
            <label for="rut">RUT:</label>
            <input type="text" id="rut" name="rut" required>
        </div>
        <div class="form-group">
            <label for="contrasena">Contraseña:</label>
            <input type="password" id="contrasena" name="contrasena" required>
        </div>
        <button type="submit">Ingresar</button>
    </form>
    <div class="register-link">
        <p>¿No tienes una cuenta? <a href="Inicio.php">Regístrate aquí</a></p>
    </div>
</div>
</body>
</html>
