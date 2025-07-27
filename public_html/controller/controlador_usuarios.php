<?php
require_once '../model/modelo_usuario.php';

// Creamos el objeto usuario una sola vez
$usuario = new Usuario();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $accion = $_POST['accion'] ?? '';  // Obtenemos la acción desde POST

    switch ($accion) {
        case 'registrar_empleado':
            registrarEmpleado($usuario);
            break;
        case 'editar_usuario':
            editarUsuario($usuario);
            break;
        case 'eliminar_usuario':
            eliminarUsuario($usuario);
            break;
        case 'actualizar_perfil':
            actualizarPerfil($usuario);
            break;
        case 'actualizar_preferencia_notificacion': 
            actualizarPreferenciaNotificacion($usuario);
            break;
        default:
            echo json_encode(["status" => 'error', "message" => "Acción POST no válida."]);
            break;
    }
    exit;

} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $accion = $_GET['accion'] ?? ''; // Obtenemos la acción desde GET

    switch ($accion) {
        case 'listar_usuarios':
            listarUsuarios($usuario);
            break;
        case 'obtener_usuario':
            obtenerUsuario($usuario);
            break;
        case 'obtener_perfil_usuario':
            obtenerPerfilUsuario($usuario);
            break;
        case 'obtener_nuevos_usuarios_7dias_count': // NUEVA ACCIÓN para el dashboard
            obtenerNuevosUsuarios7DiasCount($usuario);
            break;
        default:
            echo json_encode(['status' => 'error', 'message' => 'Acción GET no válida.']);
            break;
    }
    exit;
} else {
    echo json_encode(["status" => 'error', "message" => "Método no permitido."]);
    exit;
}


function obtenerPerfilUsuario($usuario_model) {
    $id_usuario = $_GET['id_usuario'] ?? null;

    if ($id_usuario) {
        $data = $usuario_model->obtenerUsuarioPorId($id_usuario); // Reutiliza el método existente
        if ($data) {
            echo json_encode(['status' => 'success', 'data' => $data]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ID de usuario no proporcionado.']);
    }
    exit; // Asegúrate de salir después de enviar la respuesta JSON
}

function registrarEmpleado($usuario) {
    $dni = $_POST['dni_empleado'];
    $nombres = $_POST['nombre_empleado'];
    $apellidos = $_POST['apellido_empleado'];
    $correo = $_POST['correo_empleado'];
    $contrasena = $_POST['contrasena_empleado'];
    $telefono = $_POST['telefono_empleado'];
    $direccion = $_POST['direccion_empleado'];
    $rol = 'empleado';  // Rol fijo como 'empleado'
    $contra_encrip=password_hash($contrasena, PASSWORD_DEFAULT); // Encriptar la contraseña
    $creadoPor = $_POST['creado_por'] ?? null; // Obtener el ID del usuario que crea este registro

    try {
        // No es necesario crear una nueva instancia de Usuario aquí, ya se pasó como parámetro
        // Llamamos a la función del modelo para registrar al empleado
        $resultado = $usuario->registrarUsuario(
            $dni, $nombres, $apellidos, $correo, $contra_encrip, $telefono, $direccion, $rol, $creadoPor // Pasar el nuevo parámetro
        );

        // Respondemos con el resultado
        if ($resultado) {
            echo json_encode(['status' => 'success', "message" => "Empleado registrado correctamente."]);
        } else {
            echo json_encode(['status' => 'error', "message" => "Error al registrar el empleado (posiblemente DNI o correo duplicado)."]);
        }

    } catch (Exception $e) {
        echo json_encode(["status" => 'error', "message" => "Error: " . $e->getMessage()]);
    }
}


function listarUsuarios($usuario) {
    try {
        // No es necesario crear una nueva instancia de Usuario aquí, ya se pasó como parámetro
        $usuarios = $usuario->listarUsuarios(); // Llama a la función del modelo sin parámetros
        
        if ($usuarios) {
            echo json_encode(['status' => 'success', 'data' => $usuarios]);  // Devuelve los usuarios como un array JSON
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se encontraron usuarios.']);
        }

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error al obtener los usuarios: ' . $e->getMessage()]);
    }
}


function obtenerUsuario($usuario) {
    $idUsuario = $_GET['id_usuario'] ?? null;

    if ($idUsuario) {
        $datosUsuario = $usuario->obtenerUsuarioPorId($idUsuario); // Debes crear esta función en tu modelo
        if ($datosUsuario) {
            echo json_encode(['status' => 'success', 'data' => $datosUsuario]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se encontró el usuario con ese ID.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ID de usuario no proporcionado.']);
    }
}


function editarUsuario($usuario) {
    $idUsuario = $_POST['id_usuario'] ?? null;
    $rol = $_POST['rol_usuario'] ?? null;
    $estado = $_POST['estado_usuario'] ?? null;
    $modificadoPor = $_POST['modificado_por'] ?? null; // Obtener el ID del usuario que modifica

    if ($idUsuario && $rol !== null && $estado !== null) {
        // Pasar el nuevo parámetro a la función del modelo
        $resultado = $usuario->actualizarRolEstado($idUsuario, $rol, $estado, $modificadoPor);
        if ($resultado) {
            echo json_encode(['status' => 'success', 'message' => 'Usuario actualizado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el usuario.']);
        }
    } else {
        echo json_encode(['status' => 'error', "message" => "Datos de actualización incompletos (ID, Rol, Estado, Modificado por)."]);
    }
}

function actualizarPerfil($usuario_model) {
    $id_usuario_perfil = $_POST['id_usuario_perfil'] ?? null;
    $dni = $_POST['dni'] ?? null;
    $nombre = $_POST['nombre'] ?? null;
    $apellido = $_POST['apellido'] ?? null;
    // El correo electrónico no se actualiza directamente desde aquí por seguridad
    $telefono = !empty($_POST['telefono']) ? $_POST['telefono'] : null;
    $direccion = $_POST['direccion'] ?? null;
    $modificadoPor = $_POST['modificado_por'] ?? null; // ID del usuario que modifica (el propio usuario logueado)

    // Validación básica de campos requeridos para la actualización de perfil
    if (empty($id_usuario_perfil) || empty($dni) || empty($nombre) || empty($apellido)) {
        header("Location: ../index.php?page=view/perfil.php&edit=true&status=error&message=Datos incompletos para actualizar el perfil.");
        exit;
    }

    try {
        $resultado = $usuario_model->actualizarPerfil($id_usuario_perfil,$dni,$nombre,$apellido,$telefono,$direccion,$modificadoPor);

        if ($resultado) {
            // Si la actualización es exitosa, recargar la sesión para reflejar los cambios
            // Esto es crucial para que el perfil.php muestre los datos actualizados inmediatamente
            session_start(); // Asegúrate de que la sesión esté iniciada
            $_SESSION['nombre'] = $nombre;
            $_SESSION['apellido'] = $apellido;
            $_SESSION['dni'] = $dni;
            $_SESSION['telefono'] = $telefono;
            $_SESSION['direccion'] = $direccion;

            header("Location: ../index.php?page=view/perfil.php&status=success");
            exit;
        } else {
            header("Location: ../index.php?page=view/perfil.php&edit=true&status=error&message=Error al actualizar el perfil.");
            exit;
        }
    } catch (Exception $e) {
        error_log("Error en actualizarPerfil (controlador): " . $e->getMessage());
        header("Location: ../index.php?page=view/perfil.php&edit=true&status=error&message=Error interno del servidor al actualizar el perfil.");
        exit;
    }
}

function eliminarUsuario($usuario) {
    $idUsuario = $_POST['id_usuario'] ?? null;

    if ($idUsuario) {
        try {
            // Llama a la función del modelo. Si el modelo lanza una excepción, será capturada aquí.
            $resultado = $usuario->eliminarUsuario($idUsuario); 
            
            // Si no se lanzó una excepción, la función del modelo retornó true/false basada en affected_rows
            if ($resultado) {
                echo json_encode(['status' => 'success', 'message' => 'Usuario eliminado correctamente.']);
            } else {
                // Si $resultado es false, es porque affected_rows fue 0 (ninguna fila eliminada por alguna otra razón)
                echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar el usuario (Posiblemente unico administrador).']);
            }

        } catch (Exception $e) {
            // Captura la excepción lanzada por el modelo (e.g., "No se puede eliminar el único administrador...")
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ID de usuario no proporcionado para eliminar.']);
    }
}

function actualizarPreferenciaNotificacion($usuario_model) {
    session_start(); // Asegúrate de que la sesión esté iniciada
    $id_usuario = $_SESSION['id_usuario'] ?? null; // Obtener el ID del usuario logueado
    $recibir_notificaciones_descuento = $_POST['recibir_notificaciones_descuento'] ?? null;
    $modificadoPor = $id_usuario; // El propio usuario es quien modifica

    if (is_null($id_usuario) || is_null($recibir_notificaciones_descuento)) {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos para actualizar la preferencia de notificación.']);
        return;
    }

    try {
        $resultado = $usuario_model->actualizarPreferenciaNotificacion(
            $id_usuario,
            $recibir_notificaciones_descuento,
            $modificadoPor
        );

        if ($resultado) {
            // Actualizar la preferencia en la sesión para reflejar el cambio inmediatamente
            $_SESSION['recibir_notificaciones_descuento'] = $recibir_notificaciones_descuento;
            echo json_encode(['status' => 'success', 'message' => 'Preferencia de notificación actualizada con éxito.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar la preferencia de notificación.']);
        }
    } catch (Exception $e) {
        error_log("Error en actualizarPreferenciaNotificacion (controlador): " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error interno del servidor al actualizar la preferencia de notificación.']);
    }
}

function obtenerNuevosUsuarios7DiasCount($usuario_model) {
    try {
        $count = $usuario_model->obtenerNuevosUsuarios7DiasCount();
        echo json_encode(['status' => 'success', 'count' => $count]);
    } catch (Exception $e) {
        error_log("Error en obtenerNuevosUsuarios7DiasCount (controlador): " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error al obtener el conteo de nuevos usuarios.']);
    }
}

?>

