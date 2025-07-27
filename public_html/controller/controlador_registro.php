<?php
// Include the user model to interact with the database
require_once '../model/modelo_usuario.php';

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the action from the form (it should be 'registeruser')
    $accion = $_POST['accion'] ?? '';

    // Check if the action is for registration
    if ($accion == 'registeruser') {
        // Collect all required and optional data from the form
        $nombre = $_POST['nombre'] ?? '';
        $apellido = $_POST['apellido'] ?? '';
        $dni = $_POST['dni'] ?? '';
        $correo = $_POST['regUsuario'] ?? ''; // Note: input name was 'regUsuario'
        $contrasena = $_POST['regContrasena'] ?? '';
        $confirmContrasena = $_POST['confirmContrasena'] ?? '';
        $telefono = $_POST['telefono'] ?? null;  // Optional, default to null if not provided
        $direccion = $_POST['direccion'] ?? null; // Optional, default to null if not provided
        $rol = 'cliente'; // Default to 'cliente' from hidden input
        $creadoPor = !empty($_POST['creado_por']) ? $_POST['creado_por'] : null;

        // --- Basic Server-Side Validation ---
        // Validate required fields
        if (empty($nombre) || empty($apellido) || empty($dni) || empty($correo) || empty($contrasena) || empty($confirmContrasena)) {
            echo json_encode(['status' => 'error', 'message' => 'Todos los campos obligatorios deben ser completados.']);
            exit;
        }

        // Validate email format
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Por favor, ingrese un correo electrónico válido.']);
            exit;
        }

        // Check if passwords match
        if ($contrasena !== $confirmContrasena) {
            echo json_encode(['status' => 'error', 'message' => 'Las contraseñas no coinciden.']);
            exit;
        }

        // Hash the password securely BEFORE storing it
        $contrasena_hashed = password_hash($contrasena, PASSWORD_DEFAULT);

        // Create an instance of the Usuario model
        $usuario = new Usuario();

        // Attempt to register the user
        // Pass all the collected data, including the hashed password and creadoPor
        $registroExitoso = $usuario->registrarUsuario($dni, $nombre, $apellido, $correo, $contrasena_hashed, $telefono, $direccion, $rol, $creadoPor);

        if ($registroExitoso) {
            echo json_encode(['status' => 'success', 'message' => '¡Registro exitoso! Ahora puedes iniciar sesión.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al registrar el usuario. El correo o DNI podrían ya estar registrados.']);
        }

    } else {
        // If the 'accion' parameter is not 'registeruser'
        echo json_encode(["status" => 'error', "message" => "Acción POST no válida para registro."]);
    }
    exit; // Stop execution after sending JSON response

} else {
    // If the request method is not POST
    echo json_encode(["status" => 'error', "message" => "Método no permitido."]);
    exit;
}
?>
