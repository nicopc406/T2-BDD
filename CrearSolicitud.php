<?php
// Habilitar la visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar la sesión para obtener datos del usuario, como su ID
session_start();

// Verificar si el usuario ha iniciado sesión. Redirigir si no es así.
// Se asume que el ID del usuario se almacena en $_SESSION['usuario_id']
if (!isset($_SESSION['usuario_id'])) {
    // header('Location: login.php'); // Descomentar para redirigir a la página de login
    // exit();
    $_SESSION['usuario_id'] = 1; // Asignación temporal para pruebas
}

// Configuración de la conexión a la base de datos
$servername = "localhost";
$username = "root"; // Reemplazar con tu usuario de BD
$password = ""; // Reemplazar con tu contraseña de BD
$dbname = "nombre_de_tu_bdd"; // Reemplazar con el nombre de tu BD

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$mensaje = ""; // Variable para mostrar mensajes al usuario

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar y sanitizar las entradas
    $id_funcionalidad = isset($_POST['id_funcionalidad']) ? (int)$_POST['id_funcionalidad'] : 0;
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $id_usuario_solicitante = $_SESSION['usuario_id'];

    // Validaciones básicas
    if ($id_funcionalidad > 0 && !empty($descripcion)) {
        // Preparar la sentencia SQL para insertar la nueva solicitud
        // Se asume que la tabla se llama 'solicitudes' y tiene estas columnas.
        // El estado por defecto es 'Pendiente' y la fecha se inserta automáticamente.
        $sql = "INSERT INTO solicitudes (id_funcionalidad, id_usuario_solicitante, descripcion, estado, fecha_creacion) VALUES (?, ?, ?, 'Pendiente', NOW())";

        if ($stmt = $conn->prepare($sql)) {
            // Vincular parámetros: iis -> integer, integer, string
            $stmt->bind_param("iis", $id_funcionalidad, $id_usuario_solicitante, $descripcion);

            // Ejecutar la sentencia
            if ($stmt->execute()) {
                $mensaje = "Solicitud creada exitosamente.";
                // Podrías redirigir a otra página, por ejemplo, a la lista de solicitudes
                // header('Location: mis_solicitudes.php');
                // exit();
            } else {
                $mensaje = "Error al crear la solicitud: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $mensaje = "Error al preparar la consulta: " . $conn->error;
        }
    } else {
        $mensaje = "Por favor, complete todos los campos del formulario.";
    }
}

// Obtener las funcionalidades para el menú desplegable
$funcionalidades = [];
$sql_funcionalidades = "SELECT id_funcionalidad, nombre_funcionalidad FROM funcionalidades ORDER BY nombre_funcionalidad";
$result_funcionalidades = $conn->query($sql_funcionalidades);
if ($result_funcionalidades && $result_funcionalidades->num_rows > 0) {
    while ($row = $result_funcionalidades->fetch_assoc()) {
        $funcionalidades[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Nueva Solicitud</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        select, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        textarea { resize: vertical; min-height: 100px; }
        button { background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #0056b3; }
        .mensaje { padding: 10px; margin-bottom: 15px; border-radius: 4px; color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; }
        .error { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
    </style>
</head>
<body>

    <div class="container">
        <h1>Crear Nueva Solicitud</h1>

        <?php if (!empty($mensaje)): ?>
            <div class="mensaje <?php echo strpos($mensaje, 'Error') !== false ? 'error' : ''; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="id_funcionalidad">Funcionalidad Relacionada</label>
                <select name="id_funcionalidad" id="id_funcionalidad" required>
                    <option value="">-- Seleccione una funcionalidad --</option>
                    <?php foreach ($funcionalidades as $funcionalidad): ?>
                        <option value="<?php echo htmlspecialchars($funcionalidad['id_funcionalidad']); ?>">
                            <?php echo htmlspecialchars($funcionalidad['nombre_funcionalidad']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción de la Solicitud</label>
                <textarea name="descripcion" id="descripcion" placeholder="Detalle aquí su requerimiento o problema..." required></textarea>
            </div>

            <button type="submit">Enviar Solicitud</button>
        </form>
    </div>

</body>
</html>