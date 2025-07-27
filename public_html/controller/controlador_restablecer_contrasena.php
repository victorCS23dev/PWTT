<?php
// controller/controlador_restablecer_contrasena.php
// Este archivo procesa la solicitud para restablecer la contraseña.

// Establecer cabeceras para JSON
header('Content-Type: application/json');
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Fecha en el pasado

// Incluye tu modelo de usuario
require_once __DIR__ . '/../model/modelo_usuario.php'; 

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'restablecer_contrasena') {
    $token = $_POST['token'] ?? '';
    $nuevaContrasena = $_POST['nuevaContrasena'] ?? '';
    $confirmarContrasena = $_POST['confirmarContrasena'] ?? '';

    if (empty($token) || empty($nuevaContrasena) || empty($confirmarContrasena)) {
        $response['message'] = 'Todos los campos son obligatorios.';
        echo json_encode($response);
        exit();
    }

    if ($nuevaContrasena !== $confirmarContrasena) {
        $response['message'] = 'Las contraseñas no coinciden.';
        echo json_encode($response);
        exit();
    }

    // Puedes añadir aquí reglas de complejidad de contraseña (longitud mínima, caracteres especiales, etc.)
    if (strlen($nuevaContrasena) < 8) {
        $response['message'] = 'La contraseña debe tener al menos 8 caracteres.';
        echo json_encode($response);
        exit();
    }

    try {
        $usuarioModel = new Usuario(); // Instanciar el modelo de usuario

        // 1. Validar el token usando el modelo
        $resetRequest = $usuarioModel->obtenerTokenRestablecimiento($token);

        if (!$resetRequest) {
            $response['message'] = 'El enlace de restablecimiento es inválido o ya ha sido utilizado.';
            echo json_encode($response);
            exit();
        }

        // Verificar si el token ha expirado
        if (new DateTime() > new DateTime($resetRequest['expires_at'])) {
            $response['message'] = 'El enlace de restablecimiento ha expirado. Por favor, solicita uno nuevo.';
            // Opcional: Eliminar el token expirado de la DB usando el modelo
            $usuarioModel->eliminarTokenRestablecimiento($token);
            echo json_encode($response);
            exit();
        }

        $userEmail = $resetRequest['email'];

        // 2. Hashear la nueva contraseña
        $hashedPassword = password_hash($nuevaContrasena, PASSWORD_DEFAULT);

        // 3. Actualizar la contraseña del usuario usando el modelo
        if ($usuarioModel->actualizarContrasena($userEmail, $hashedPassword)) {
            // 4. Eliminar el token de la base de datos para que no pueda ser reutilizado usando el modelo
            $usuarioModel->eliminarTokenRestablecimiento($token);

            $response['success'] = true;
            $response['message'] = 'Tu contraseña ha sido restablecida exitosamente. Ahora puedes iniciar sesión con tu nueva contraseña.';
        } else {
            $response['message'] = 'Error al actualizar la contraseña. Por favor, inténtalo de nuevo.';
        }

    } catch (Exception $e) {
        // Manejo de errores generales
        error_log("Error en controlador_restablecer_contrasena: " . $e->getMessage());
        $response['message'] = 'Error inesperado. Por favor, inténtalo de nuevo más tarde.';
    }
} else {
    $response['message'] = 'Solicitud inválida.';
}

echo json_encode($response);
exit();
?>
