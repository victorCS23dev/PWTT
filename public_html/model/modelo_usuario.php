<?php

header("Cache-Control: no-cache");
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once __DIR__ . '/../connectDB/conexion.php'; 

class Usuario {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function login($correo, $contrasena_ingresada) { // Renombramos para claridad
        try {
            // Paso 1: Obtener el hash de la contraseña y otros datos del usuario por correo
            // El procedimiento almacenado ahora solo necesita el correo
            $stmt = $this->db->prepare("CALL obtener_usuario_por_correo(?)"); // NECESITAS CREAR ESTE SP
            $stmt->bind_param("s", $correo);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $usuario_data = $result->fetch_assoc();
                $hash_almacenado = $usuario_data['contrasena']; // Asume que la columna se llama 'contrasena'
                // Paso 2: Verificar la contraseña ingresada contra el hash almacenado
                if (password_verify($contrasena_ingresada, $hash_almacenado)) {
                    // Contraseña correcta
                    return $usuario_data;
                } else {
                    // Contraseña incorrecta
                    return null;
                }
            } else {
                // Usuario no encontrado o más de uno (debería ser único)
                return null;
            }
        } catch (Exception $e) {
            // Manejo de errores de base de datos o ejecución
            error_log("Error en login (modelo): " . $e->getMessage()); // Para depuración en logs
            return null;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public function obtenerUsuarioPorCorreoParaRecuperacion($correo) {
        try {
            $stmt = $this->db->prepare("SELECT idUsuarios, correo FROM usuarios WHERE correo = ? LIMIT 1");
            $stmt->bind_param("s", $correo);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc(); // Devuelve null si no encuentra, o el array asociativo
        } catch (Exception $e) {
            error_log("Error en obtenerUsuarioPorCorreoParaRecuperacion: " . $e->getMessage());
            return null;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public function guardarTokenRestablecimiento($email, $token, $expiresAt) {
        try {
            // Eliminar cualquier token anterior para este email
            $stmt = $this->db->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->close();

            // Insertar el nuevo token
            $stmt = $this->db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $email, $token, $expiresAt);
            $stmt->execute();
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Error al guardar token de restablecimiento: " . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public function obtenerTokenRestablecimiento($token) {
        try {
            $stmt = $this->db->prepare("SELECT email, expires_at FROM password_resets WHERE token = ? LIMIT 1");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc(); // Devuelve null si no encuentra, o el array asociativo
        } catch (Exception $e) {
            error_log("Error al obtener token de restablecimiento: " . $e->getMessage());
            return null;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public function actualizarContrasena($email, $hashedPassword) {
        try {
            // Asume que la columna de contraseña en tu tabla 'usuarios' se llama 'contrasena'
            $stmt = $this->db->prepare("UPDATE usuarios SET contrasena = ? WHERE correo = ?");
            $stmt->bind_param("ss", $hashedPassword, $email);
            $stmt->execute();
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Error al actualizar contraseña: " . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public function eliminarTokenRestablecimiento($token) {
        try {
            $stmt = $this->db->prepare("DELETE FROM password_resets WHERE token = ?");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Error al eliminar token de restablecimiento: " . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public function obtenerUsuarioPorEmailOGoogleId($email, $google_id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE correo = ? OR google_id = ? LIMIT 1");
            $stmt->bind_param("ss", $email, $google_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc(); // Devuelve null si no encuentra, o el array asociativo
        } catch (Exception $e) {
            error_log("Error en obtenerUsuarioPorEmailOGoogleId: " . $e->getMessage());
            return null;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public function registrarUsuarioDesdeGoogle($google_id, $nombres, $apellidos, $correo, $contrasena_hashed, $dni, $telefono, $direccion, $rol, $estado) {
        try {
            // Verificar si el correo ya existe (puede estar registrado con login normal)
            $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM usuarios WHERE correo = ?");
            $checkStmt->bind_param("s", $correo);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $count = $checkResult->fetch_row()[0];
            $checkStmt->close();

            if ($count > 0) {
                error_log("Intento de registrar usuario de Google con correo existente: " . $correo);
                return false; 
            }

            // Procedimiento almacenado para registrar usuario, ahora debe aceptar google_id y estado
            // NECESITAS CREAR O MODIFICAR ESTE SP:
            // CALL registrar_usuario_google(google_id, dni, nombres, apellidos, correo, contrasena, telefono, direccion, rol, estado)
            $stmt = $this->db->prepare("CALL registrar_usuario_google(?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssss", $google_id, $dni, $nombres, $apellidos, $correo, $contrasena_hashed, $telefono, $direccion, $rol);
            $stmt->execute();

            return true; // Registro exitoso
        } catch (Exception $e) {
            error_log("Error al registrar usuario desde Google: " . $e->getMessage());
            return false; // Registro fallido
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public function actualizarGoogleId($id_usuario, $google_id) {
        try {
            $stmt = $this->db->prepare("UPDATE usuarios SET google_id = ? WHERE idUsuarios = ?");
            $stmt->bind_param("si", $google_id, $id_usuario);
            $stmt->execute();
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Error al actualizar Google ID para usuario " . $id_usuario . ": " . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }
    
    public function registrarUsuario($dni, $nombres, $apellidos, $correo, $contrasena_hashed, $telefono, $direccion, $rol, $creadoPor) {
        try {
            // --- INICIO DE DEPURACIÓN DE REGISTRO ---
            error_log("DEBUG modelo_usuario.php: Intentando registrar DNI: " . $dni . ", Correo: " . $correo);
            // --- FIN DE DEPURACIÓN DE REGISTRO ---

            // Verificar si DNI o correo ya existen
            $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM usuarios WHERE dni = ? OR correo = ?");
            $checkStmt->bind_param("ss", $dni, $correo);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $count = $checkResult->fetch_row()[0];
            $checkStmt->close();

            // --- INICIO DE DEPURACIÓN DE REGISTRO ---
            error_log("DEBUG modelo_usuario.php: Resultado COUNT(*) para DNI/Correo: " . $count);
            // --- FIN DE DEPURACIÓN DE REGISTRO ---

            if ($count > 0) {
                // El usuario con el mismo DNI o correo ya existe
                error_log("DEBUG modelo_usuario.php: DNI o correo ya existen. Devolviendo false.");
                return false;
            }

            // Preparar la llamada al procedimiento almacenado 'registrar_usuario'
            // Asegúrate de que tu SP coincida con estos parámetros y tipos
            // 'ssssssssi' corresponde a 8 string parameters y 1 integer (para creado_por)
            $stmt = $this->db->prepare("CALL registrar_usuario(?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssi", $dni, $nombres, $apellidos, $correo, $contrasena_hashed, $telefono, $direccion, $rol, $creadoPor);
            $stmt->execute();

            // --- INICIO DE DEPURACIÓN DE REGISTRO ---
            error_log("DEBUG modelo_usuario.php: Registro exitoso. affected_rows: " . $stmt->affected_rows);
            // --- FIN DE DEPURACIÓN DE REGISTRO ---
            return true; // Registro exitoso
        } catch (Exception $e) {
            error_log("Error al registrar usuario: " . $e->getMessage());
            return false; // El registro falló
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public function registrarEmpleado($dni, $nombres, $apellidos, $correo, $contrasena, $telefono, $direccion, $rol) {
        try {
            // Preparamos la consulta con la llamada al procedimiento almacenado 'registrar_usuario'
            $stmt = $this->db->prepare("CALL registrar_usuario(?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $dni, $nombres, $apellidos, $correo, $contrasena, $telefono, $direccion, $rol);
            $stmt->execute();

            // Si la ejecución fue exitosa, retornamos true
            return true;
        } catch (Exception $e) {
            // Si hubo un error, retornamos false
            return false;
        }
    }

    public function listarUsuarios() {
        try {
            // Preparamos la llamada al procedimiento almacenado
            $stmt = $this->db->prepare("CALL listar_usuarios()");
            // Ejecutamos el procedimiento
            $stmt->execute();
            // Obtenemos el resultado
            $resultado = $stmt->get_result();
            // Convertimos el resultado a array asociativo
            $usuarios = [];
            while ($fila = $resultado->fetch_assoc()) {
                $usuarios[] = $fila;
            }
            // Cerramos el statement y retornamos los usuarios
            $stmt->close();
            return $usuarios;
        } catch (Exception $e) {
            error_log("Error al listar usuarios: " . $e->getMessage());
            return null; // Si hay un error, retornamos null
        }
    }

    public function obtenerUsuarioPorId($id) {
        try {
            $stmt = $this->db->prepare("CALL obtener_usuario_por_id(?)");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $resultado = $stmt->get_result();
            if ($resultado->num_rows == 1) {
                return $resultado->fetch_assoc();
            } else {
                return null;
            }
        } catch (Exception $e) {
            return null;
        } finally {
            if ($stmt) {
                $stmt->close();
            }
        }
    }

    public function actualizarRolEstado($id, $rol, $estado, $modificadoPor) {
        try {
            // El tipo de parámetro para modificadoPor debe ser 'i' si es INT o 's' si es STRING (dependiendo de tu DB)
            // Asumiendo que modificado_por es INT en tu tabla
            $stmt = $this->db->prepare("CALL actualizar_rol_estado(?, ?, ?, ?)");
            $stmt->bind_param("isii", $id, $rol, $estado, $modificadoPor); // i: entero, s: string, i: entero, i: entero
            $stmt->execute();
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Error al actualizar rol/estado del usuario: " . $e->getMessage());
            return false;
        } finally {
            if ($stmt) {
                $stmt->close();
            }
        }
    }

    public function actualizarPerfil($id_usuario, $dni, $nombres, $apellidos, $telefono, $direccion, $modificado_por) {
        try {
            // El correo (email) no se actualiza a través de este método por seguridad.
            // Si necesitas actualizar el correo, debería ser un proceso separado con verificación.
            $stmt = $this->db->prepare("CALL actualizar_perfil_usuario(?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssisi", $id_usuario, $dni, $nombres, $apellidos, $telefono, $direccion, $modificado_por);
            $stmt->execute();
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Error al actualizar perfil de usuario: " . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public function eliminarUsuario($id) {
        try {
            $stmt = $this->db->prepare("CALL eliminar_usuario(?)");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return $stmt->affected_rows > 0;
        } catch (Exception $e) { // Captura cualquier excepción (incluyendo PDOException si usas PDO)
            // Aquí capturamos el mensaje de error de la base de datos (del SIGNAL)
            $errorMessage = $e->getMessage();

            // Verificamos si el mensaje de error es el que definimos en el procedimiento almacenado
            if (strpos($errorMessage, 'No se puede eliminar el único administrador activo del sistema.') !== false) {
                // Si es el error específico, relanza una excepción con ese mensaje claro
                throw new Exception('No se puede eliminar el único administrador activo del sistema.');
            } else {
                // Para cualquier otro error (errores de SQL, etc.), loguéalo y lanza una excepción genérica
                error_log("Error inesperado en el modelo al eliminar usuario: " . $errorMessage);
                throw new Exception('Ocurrió un error en la base de datos al eliminar el usuario.');
            }
        } finally {
            if ($stmt) {
                $stmt->close();
            }
        }
    }

    public function actualizarPreferenciaNotificacion($id, $recibirNotificacionesDescuento, $modificadoPor) {
        $stmt = null;
        try {
            $sql = "CALL actualizar_preferencia_notificacion_usuario(?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            if ($stmt === false) {
                error_log("Error al preparar CALL actualizar_preferencia_notificacion_usuario: " . $this->db->getLastError());
                return false;
            }
            $modificadoPor = empty($modificadoPor) ? null : $modificadoPor;
            // 'iii' -> i:id, i:recibirNotificacionesDescuento (boolean se trata como int), i:modificadoPor
            $stmt->bind_param("iii", $id, $recibirNotificacionesDescuento, $modificadoPor);

            if (!$stmt->execute()) {
                error_log("Error al ejecutar CALL actualizar_preferencia_notificacion_usuario: " . $stmt->error);
                return false;
            }
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Excepción en actualizarPreferenciaNotificacion (modelo): " . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt) && $stmt) $stmt->close();
        }
    }

    public function obtenerUsuariosParaNotificacion() {
        $stmt = null;
        $usuarios = [];
        try {
            $sql = "CALL obtener_usuarios_para_notificacion()";
            $stmt = $this->db->prepare($sql);
            if ($stmt === false) {
                error_log("Error al preparar CALL obtener_usuarios_para_notificacion: " . $this->db->getLastError());
                return [];
            }
            $stmt->execute();
            $result = $stmt->get_result();
            while ($fila = $result->fetch_assoc()) {
                $usuarios[] = $fila;
            }
            return $usuarios;
        } catch (Exception $e) {
            error_log("Excepción en obtenerUsuariosParaNotificacion (modelo): " . $e->getMessage());
            return [];
        } finally {
            if (isset($stmt) && $stmt) $stmt->close();
        }
    }

    public function obtenerNuevosUsuarios7DiasCount() {
        $stmt = null;
        try {
            $stmt = $this->db->prepare("CALL obtener_nuevos_usuarios_7DiasCount()");
            if ($stmt === false) {
                error_log("Error al preparar obtener_nuevos_usuarios_7DiasCount: " . $this->db->getLastError());
                return 0;
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            return $data['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error en obtener_nuevos_usuarios_7DiasCount (modelo): " . $e->getMessage());
            return 0;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    
}

?>