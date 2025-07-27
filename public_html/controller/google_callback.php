<?php
// controller/google_callback.php

use Google\Service\CloudControlsPartnerService\Console;

session_start(); // Inicia la sesión para guardar los datos del usuario

// Requerimos el autoload de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Incluimos tu modelo de usuario y la conexión a la base de datos
require_once __DIR__ . '/../model/modelo_usuario.php';
require_once __DIR__ . '/../connectDB/conexion.php';

// --- CONFIGURACIÓN DE GOOGLE OAUTH (Debe coincidir con login.php) ---
$google_client_id = 'GOOGLE OAUTH';
$google_client_secret = 'GOOGLE OAUTH';

$google_redirect_uri = 'http://localhost:3000/controller/google_callback.php';

$client = new Google_Client();
$client->setClientId($google_client_id);
$client->setClientSecret($google_client_secret);
$client->setRedirectUri($google_redirect_uri);
// --- FIN CONFIGURACIÓN DE GOOGLE OAUTH ---

if (isset($_GET['code'])) {
    try {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $client->setAccessToken($token);

        // Obtener información del perfil del usuario
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();

        // Información que obtendrás del usuario de Google
        $email = $google_account_info->email;
        $google_id = $google_account_info->id;
        $nombre = $google_account_info->givenName;
        $apellido = $google_account_info->familyName?? 'N/A';;
        $dni = 'N/A';
        $telefono = 'N/A';
        $direccion = 'N/A';

        // Inicializa el modelo de usuario
        $usuario_model = new Usuario();

        // 1. Verifica si el usuario ya existe en tu DB por su email o google_id
        $usuario_existente = $usuario_model->obtenerUsuarioPorEmailOGoogleId($email, $google_id);
        if ($usuario_existente) {
            // El usuario ya existe, iniciar sesión
            $_SESSION['id_usuario'] = $usuario_existente['idUsuarios'];
            $_SESSION['nombre'] = $usuario_existente['nombres']; // Ajusta a tus nombres de columna
            $_SESSION['apellido'] = $usuario_existente['apellidos']; // Ajusta a tus nombres de columna
            $_SESSION['correo'] = $usuario_existente['correo']; // Ajusta a tus nombres de columna
            $_SESSION['rol'] = $usuario_existente['rol'];
            // Cargar más datos si los tienes en tu DB
            $_SESSION['dni'] = $usuario_existente['dni'] ?? 'No disponible';
            $_SESSION['telefono'] = $usuario_existente['telefono'] ?? 'No disponible';
            $_SESSION['direccion'] = $usuario_existente['direccion'] ?? 'No disponible';
            $_SESSION['estado'] = $usuario_existente['estado'] ?? 1; // Asume que tienes un campo 'estado'

            // Si el usuario existe pero no tenía google_id, lo actualizamos (útil para migración)
            if (empty($usuario_existente['google_id'])) {
                $usuario_model->actualizarGoogleId($usuario_existente['id'], $google_id);
            }

            header('Location: ../index.php');
            exit;

        } else {
            $rol_defecto = 'cliente';
            $estado_defecto = 1;
            // Genera una contraseña aleatoria y segura. No la usarán para iniciar sesión con Google.
            $password_aleatoria = password_hash(uniqid(rand(), true), PASSWORD_DEFAULT);

            $registro_exitoso = $usuario_model->registrarUsuarioDesdeGoogle($google_id, $nombre, $apellido, $email, $password_aleatoria, $dni, $telefono, $direccion, $rol_defecto, $estado_defecto);
            if ($registro_exitoso) {
                $nuevo_usuario_data = $usuario_model->obtenerUsuarioPorEmailOGoogleId($email, $google_id);
                $_SESSION['id_usuario'] = $nuevo_usuario_data['id'];
                $_SESSION['nombre'] = $nuevo_usuario_data['nombres'];
                $_SESSION['apellido'] = $nuevo_usuario_data['apellidos'];
                $_SESSION['email'] = $nuevo_usuario_data['correo'];
                $_SESSION['rol'] = $nuevo_usuario_data['rol'];
                $_SESSION['estado'] = $nuevo_usuario_data['estado'];
                $_SESSION['dni'] = $nuevo_usuario_data['dni'] ?? 'No disponible';
                $_SESSION['telefono'] = $nuevo_usuario_data['telefono'] ?? 'No disponible';
                $_SESSION['direccion'] = $nuevo_usuario_data['direccion'] ?? 'No disponible';


                header('Location: ../index.php');
                exit;
            } else {
                $_SESSION['error_login_google'] = 'No se pudo registrar su cuenta con Google. El correo ya podría estar en uso.';
                header('Location: ../main/login.php');
                exit;
            }
        }
    } catch (Google\Service\Exception $e) {
        // Error específico de la API de Google
        error_log("Error de Google OAuth: " . $e->getMessage());
        $_SESSION['error_login_google'] = 'Hubo un problema al iniciar sesión con Google. Intente de nuevo más tarde.';
        header('Location: ../main/login.php');
        exit;
    } catch (Exception $e) {
        // Cualquier otro error inesperado
        error_log("Error en google_callback: " . $e->getMessage());
        $_SESSION['error_login_google'] = 'Ocurrió un error inesperado. Por favor, intente de nuevo.';
        header('Location: ../main/login.php');
        exit;
    }
} else {
    $_SESSION['error_login_google'] = 'No se recibió la autorización de Google. Por favor, intente de nuevo.';
    header('Location: ../main/login.php');
    exit;
}