<?php

header("Cache-Control: no-cache");
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once __DIR__ . '/../connectDB/conexion.php'; 

class Reseña {
    private $db;

    public function __construct() {
        $this->db = new Database(); 
    }

    public function guardarReseña($idProducto, $idUsuario, $calificacion, $comentario) {
        $stmt = null; // Inicializa la variable de sentencia preparada
        try {
            // Verificar si ya existe una reseña de este usuario para este producto
            $reseña_existente = $this->obtenerReseñaPorProductoYUsuario($idProducto, $idUsuario);

            if ($reseña_existente) {
                // Si la reseña ya existe, se actualiza
                $sql = "UPDATE reseñas_productos SET calificacion = ?, comentario = ?, fecha_creacion = CURRENT_TIMESTAMP WHERE idProducto = ? AND idUsuario = ?";
                $stmt = $this->db->prepare($sql);
                
                if ($stmt === false) {
                    error_log("Error al preparar la consulta UPDATE en guardarReseña: " . $this->db->getLastError());
                    return false;
                }
                // 'isii' -> i: integer, s: string, i: integer, i: integer
                $stmt->bind_param("isii", $calificacion, $comentario, $idProducto, $idUsuario);
            } else {
                // Si la reseña no existe, se inserta una nueva
                $sql = "INSERT INTO reseñas_productos (idProducto, idUsuario, calificacion, comentario) VALUES (?, ?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                
                if ($stmt === false) {
                    error_log("Error al preparar la consulta INSERT en guardarReseña: " . $this->db->getLastError());
                    return false;
                }
                // 'iiis' -> i: integer, i: integer, i: integer, s: string
                $stmt->bind_param("iiis", $idProducto, $idUsuario, $calificacion, $comentario);
            }

            // Ejecuta la sentencia preparada
            if (!$stmt->execute()) {
                error_log("Error al ejecutar la consulta en guardarReseña: " . $stmt->error);
                return false;
            }

            return true; // La operación fue exitosa
        } catch (Exception $e) {
            // Captura cualquier excepción y registra el error
            error_log("Excepción en guardarReseña (modelo): " . $e->getMessage());
            return false;
        } finally {
            // Asegura que la sentencia se cierre, si ha sido preparada
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public function obtenerReseñaPorProductoYUsuario($idProducto, $idUsuario) {
        $stmt = null;
        try {
            $sql = "SELECT idReseña, calificacion, comentario, fecha_creacion FROM reseñas_productos WHERE idProducto = ? AND idUsuario = ?";
            $stmt = $this->db->prepare($sql);
            
            if ($stmt === false) {
                error_log("Error al preparar la consulta en obtenerReseñaPorProductoYUsuario: " . $this->db->getLastError());
                return false;
            }
            $stmt->bind_param("ii", $idProducto, $idUsuario);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc(); // Devuelve la reseña (array asociativo) o null si no hay resultados
        } catch (Exception $e) {
            error_log("Excepción en obtenerReseñaPorProductoYUsuario (modelo): " . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public function obtenerReseñasPorProducto($idProducto) {
        $stmt = null;
        $reseñas = [];
        try {
            // Unir con la tabla de usuarios para obtener el nombre del usuario
            $sql = "SELECT rp.idReseña, rp.calificacion, rp.comentario, rp.fecha_creacion, u.nombres AS usuario_nombre, u.apellidos AS usuario_apellido
                    FROM reseñas_productos rp
                    JOIN usuarios u ON rp.idUsuario = u.idUsuarios
                    WHERE rp.idProducto = ? AND rp.estado = 1
                    ORDER BY rp.fecha_creacion DESC";
            $stmt = $this->db->prepare($sql);
            
            if ($stmt === false) {
                error_log("Error al preparar obtenerReseñasPorProducto: " . $this->db->getLastError());
                return [];
            }
            $stmt->bind_param("i", $idProducto);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($fila = $result->fetch_assoc()) {
                $reseñas[] = $fila;
            }
            return $reseñas;
        } catch (Exception $e) {
            error_log("Excepción en obtenerReseñasPorProducto (modelo): " . $e->getMessage());
            return [];
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }
}
?>
