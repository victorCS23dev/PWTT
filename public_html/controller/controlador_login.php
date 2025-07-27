<?php
require_once '../model/modelo_usuario.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion == 'validlogin') {
        // Recibimos los datos del formulario (contraseña en texto plano)
        $correo = $_POST['usuario'] ?? '';
        $contrasena_ingresada = $_POST['logincontraseña'] ?? '';

        // Validaciones básicas de entrada
        if (empty($correo) || empty($contrasena_ingresada)) {
            echo json_encode(['status' => 'error', 'message' => 'Por favor, ingrese usuario y contraseña.']);
            exit;
        }

        $usuario = new Usuario();
        // El método login ahora internamente verifica el hash
        $datos = $usuario->login($correo, $contrasena_ingresada);

        if ($datos) {
            // Login correcto
            session_start();
            $_SESSION['id_usuario'] = $datos['id']; // Opcional, pero útil
            $_SESSION['nombre'] = $datos['nombres'];
            $_SESSION['apellido'] = $datos['apellidos'];
            $_SESSION['rol'] = $datos['rol'];
            $_SESSION['correo'] = $datos['correo'];
            echo json_encode(['status' => 'success', 'message' => 'Acceso correcto']);
        } else {
            // Credenciales inválidas (modelo devuelve null)
            echo json_encode(['status' => 'error', 'message' => 'Usuario o contraseña incorrectos']);
        }
    } else {
        echo json_encode(["status" => 'error', "message" => "Acción POST no válida para login."]);
    }
    exit; // Asegura que no se procesa nada más

} else {
    echo json_encode(["status" => 'error', "message" => "Método no permitido."]);
    exit;
}

?>