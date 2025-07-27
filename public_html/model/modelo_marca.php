<?php

header("Cache-Control: no-cache");
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once __DIR__ . '/../connectDB/conexion.php'; 

class Marca {
    private $db;

    public function __construct() {
        $this->db = new Database(); 
    }

    public function registrarMarca($nombre, $creadoPor) {
        try {
            $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM marcas WHERE nombre = ?");
            $checkStmt->bind_param("s", $nombre);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $count = $checkResult->fetch_row()[0];
            $checkStmt->close();

            if ($count > 0) {
                error_log("Intento de registrar marca duplicada: " . $nombre);
                return false; 
            }

            $stmt = $this->db->prepare("CALL registrar_marca(?, ?)");
            $stmt->bind_param("si", $nombre, $creadoPor);
            $stmt->execute();
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Error al registrar marca (modelo): " . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public function listarMarcas() {
        try {
            $stmt = $this->db->prepare("CALL listar_marcas()");
            $stmt->execute();
            $resultado = $stmt->get_result();
            $marcas = [];
            while ($fila = $resultado->fetch_assoc()) {
                $marcas[] = $fila;
            }
            $stmt->close();
            return $marcas;
        } catch (Exception $e) {
            error_log("Error al listar marcas (modelo): " . $e->getMessage());
            return null;
        }
    }

    public function listarMarcasParaSelect() {
        try {
            // Reutiliza el procedimiento listar_marcas y adapta el resultado
            $stmt = $this->db->prepare("CALL listar_marcas()");
            $stmt->execute();
            $resultado = $stmt->get_result();
            $marcas = [];
            while ($fila = $resultado->fetch_assoc()) {
                // Asegúrate de que las claves 'id' y 'nombre' coincidan con lo que espera el frontend
                $marcas[] = ['id' => $fila['id'], 'nombre' => $fila['nombre_marca']];
            }
            $stmt->close();
            return $marcas;
        } catch (Exception $e) {
            error_log("Error al listar marcas para select (modelo): " . $e->getMessage());
            return [];
        }
    }

    public function listarTodasLasMarcas() {
        try {
            // Llama al procedimiento almacenado existente 'listar_marcas'
            $stmt = $this->db->prepare("CALL listar_marcas()"); 
            $stmt->execute();
            $result = $stmt->get_result();
            $marcas = [];
            while ($fila = $result->fetch_assoc()) {
                // Adapta las claves para que coincidan con lo que espera el frontend
                // idMarcas, nombre, estado
                $marcas[] = [
                    'idMarcas' => $fila['id'], 
                    'nombre' => $fila['nombre_marca'], 
                    'estado' => $fila['estado']
                ];
            }
            $stmt->close();
            return $marcas;
        } catch (Exception $e) {
            error_log("Error en modelo_marca->listarTodasLasMarcas: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerMarcaPorId($id) {
        try {
            $stmt = $this->db->prepare("CALL obtener_marca_por_id(?)");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            // Si se encuentra una fila, la devuelve, si no, devuelve null
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error al obtener marca por ID (modelo): " . $e->getMessage());
            return null;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public function actualizarMarca($id, $nombre, $estado, $modificadoPor) {
        try {
            $stmt = $this->db->prepare("CALL actualizar_marca(?, ?, ?, ?)");
            // 'isii' para INT (id), STRING (nombre), INT (estado), INT (modificadoPor)
            $stmt->bind_param("isii", $id, $nombre, $estado, $modificadoPor); 
            $stmt->execute();
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Error al actualizar marca (modelo): " . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public function eliminarMarca($id) {
        try {
            $stmt = $this->db->prepare("CALL eliminar_marca(?)");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return $stmt->affected_rows > 0;
        } catch (mysqli_sql_exception $e) {
            // Captura específicamente excepciones de MySQLi (como el error 1451 por clave foránea)
            error_log("Error de MySQL en eliminarMarca (modelo): " . $e->getMessage() . " Código: " . $e->getCode());
            // ¡Importante! Relanza la excepción para que el controlador pueda capturarla y manejarla.
            throw $e; 
        } catch (Exception $e) {
            // Captura cualquier otra excepción general que no sea de MySQLi
            error_log("Error general en eliminarMarca (modelo): " . $e->getMessage());
            // ¡Importante! Relanza la excepción para que el controlador pueda capturarla.
            throw $e;
        } finally {
            if (isset($stmt) && $stmt) { // Asegúrate de que $stmt se haya inicializado
                $stmt->close();
            }
        }
    }
}
?>
