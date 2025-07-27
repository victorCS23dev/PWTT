<?php
// controller/controlador_recuperar_contrasena.php

// Establecer cabeceras para JSON
header('Content-Type: application/json');
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Incluye el autoload de Composer para PHPMailer
require_once __DIR__ . '/../vendor/autoload.php';

// Importar clases de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Incluye tu modelo de usuario
require_once __DIR__ . '/../model/modelo_usuario.php'; 

// Función para enviar el correo electrónico de restablecimiento de contraseña usando PHPMailer
function sendPasswordResetEmail($toEmail, $token) {
    // Define la URL base de tu aplicación
    $baseUrl = 'http://localhost:3000'; 

    $resetLink = $baseUrl . '/main/restablecer_contrasena.php?token=' . urlencode($token);
    $subject = "Recuperación de Contraseña para tu cuenta";
    $message = "Hola,\n\n";
    $message .= "Has solicitado restablecer tu contraseña. Haz clic en el siguiente enlace para continuar:\n\n";
    $message .= $resetLink . "\n\n";
    $message .= "Este enlace expirará en 1 hora. Si no solicitaste esto, ignora este correo.\n\n";
    $message .= "Atentamente,\nEl Equipo de Soporte";

    $mail = new PHPMailer(true); 
    try {
        // Configuración del servidor SMTP (basado en tu controlador_facturas.php)
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'CUENTA GMAIL'; // Tu Gmail
        $mail->Password = 'CONTRASEÑA DE APLICACION DE GMAIL'; // Tu Contraseña de aplicación de Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Usar TLS
        $mail->Port = 587;

        // Remitente y Destinatario
        $mail->setFrom('CUENTA GMAIL', 'Soporte de Cuenta'); // Tu Gmail y nombre del remitente
        $mail->addAddress($toEmail); // Correo del usuario que solicita el restablecimiento

        // Contenido del correo
        $mail->isHTML(false); // No es HTML, es texto plano
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();
        error_log("DEBUG controlador_recuperar_contrasena.php: Correo de recuperación enviado a " . $toEmail);
        return true;
    } catch (Exception $e) {
        // Captura y registra cualquier error de PHPMailer
        error_log("ERROR controlador_recuperar_contrasena.php: Error al enviar el correo de recuperación a {$toEmail}: {$mail->ErrorInfo}");
        return false;
    }
}


$response = ['success' => false, 'message' => ''];

// --- INICIO DE LÍNEAS DE DEPURACIÓN ---
error_log("DEBUG controlador_recuperar_contrasena.php: Método de solicitud: " . $_SERVER['REQUEST_METHOD']);
error_log("DEBUG controlador_recuperar_contrasena.php: Contenido de _POST: " . print_r($_POST, true));
// --- FIN DE LÍNEAS DE DEPURACIÓN ---

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'solicitar_recuperacion') {
    $email = filter_var($_POST['emailRecuperacion'], FILTER_SANITIZE_EMAIL);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Por favor, introduce un correo electrónico válido.';
        echo json_encode($response);
        exit();
    }

    try {
        $usuarioModel = new Usuario(); // Instanciar el modelo de usuario

        // 1. Verificar si el correo existe en la tabla de usuarios usando el modelo
        $user = $usuarioModel->obtenerUsuarioPorCorreoParaRecuperacion($email);

        if (!$user) {
            // No se encontró el usuario, pero no revelamos esta información por seguridad.
            $response['success'] = true;
            $response['message'] = 'Si tu correo electrónico está registrado, recibirás un enlace para restablecer tu contraseña.';
            echo json_encode($response);
            exit();
        }

        // 2. Generar un token único y seguro
        $token = bin2hex(random_bytes(32)); // Genera un token de 64 caracteres hexadecimales
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token válido por 1 hora

        // 3. Almacenar el token en la base de datos usando el modelo
        if ($usuarioModel->guardarTokenRestablecimiento($email, $token, $expiresAt)) {
            // 4. Enviar el correo electrónico con el enlace de recuperación
            if (sendPasswordResetEmail($email, $token)) {
                $response['success'] = true;
                $response['message'] = 'Se ha enviado un enlace para restablecer tu contraseña a tu correo electrónico. Por favor, revisa tu bandeja de entrada (y la carpeta de spam).';
            } else {
                $response['message'] = 'Hubo un problema al enviar el correo electrónico de recuperación. Por favor, inténtalo de nuevo más tarde.';
            }
        } else {
            $response['message'] = 'Error al guardar el token de recuperación en la base de datos.';
        }

    } catch (Exception $e) {
        // Manejo de errores generales
        error_log("Error en controlador_recuperar_contrasena: " . $e->getMessage());
        $response['message'] = 'Error inesperado. Por favor, inténtalo de nuevo más tarde.';
    }
} else {
    $response['message'] = 'Solicitud inválida.';
}

echo json_encode($response);
exit();
?>
