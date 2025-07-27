<?php

header("Cache-Control: no-cache");
// Para depuración, activa el reporte de errores también aquí temporalmente
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ruta corregida usando __DIR__ para mayor robustez
include_once __DIR__ . '/../connectDB/conexion.php'; 

class Categoria {
    private $db;

    public function __construct() {
        $this->db = new Database(); 
    }

    public function registrarCategoria($nombre, $creadoPor) {
        try {
            $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM categorias WHERE nombre = ?");
            $checkStmt->bind_param("s", $nombre);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $count = $checkResult->fetch_row()[0];
            $checkStmt->close();

            if ($count > 0) {
                error_log("Intento de registrar categoría duplicada: " . $nombre);
                return false; 
            }

            $stmt = $this->db->prepare("CALL registrar_categoria(?, ?)");
            $stmt->bind_param("si", $nombre, $creadoPor);
            $stmt->execute();
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Error al registrar categoría (modelo): " . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public function listarCategorias() {
        try {
            $stmt = $this->db->prepare("CALL listar_categorias()");
            $stmt->execute();
            $resultado = $stmt->get_result();
            $categorias = [];
            while ($fila = $resultado->fetch_assoc()) {
                $categorias[] = $fila;
            }
            $stmt->close();
            return $categorias;
        } catch (Exception $e) {
            error_log("Error al listar categorías (modelo): " . $e->getMessage());
            return null;
        }
    }

    public function listarCategoriasParaSelect() {
        try {
            $stmt = $this->db->prepare("CALL listar_categorias()"); // Reutiliza el SP, adapta el resultado
            $stmt->execute();
            $resultado = $stmt->get_result();
            $categorias = [];
            while ($fila = $resultado->fetch_assoc()) {
                $categorias[] = ['id' => $fila['idCategorias'], 'nombre' => $fila['categoria_nombre']];
            }
            $stmt->close();
            return $categorias;
        } catch (Exception $e) {
            error_log("Error al listar categorías para select (modelo): " . $e->getMessage());
            return [];
        }
    }

    public function obtenerCategoriaPorId($id) {
        try {
            $stmt = $this->db->prepare("CALL obtener_categoria_por_id(?)");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error al obtener categoría por ID (modelo): " . $e->getMessage());
            return null;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public function obtenerMarcasPorCategoria($idCategoria) {
        try {
            $stmt = $this->db->prepare("CALL obtenerMarcasPorCategoria(?)");
            $stmt->bind_param("i", $idCategoria);
            $stmt->execute();
            $result = $stmt->get_result();
            $marcas = [];
            while ($fila = $result->fetch_assoc()) {
                $marcas[] = $fila;
            }
            $stmt->close();
            return $marcas;
        } catch (Exception $e) {
            error_log("Error en modelo_categoria->obtenerMarcasPorCategoria: " . $e->getMessage());
            return false;
        }
    }

    public function actualizarCategoria($id, $nombre, $estado, $modificadoPor) {
        try {
            $stmt = $this->db->prepare("CALL actualizar_categoria(?, ?, ?, ?)");
            $stmt->bind_param("isii", $id, $nombre, $estado, $modificadoPor); 
            $stmt->execute();
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Error al actualizar categoría (modelo): " . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public function editarCategoriaConMarcas($idCategoria, $nombre, $estado, $modificadoPor, $marcasCsv) {
        try {
            $stmt = $this->db->prepare("CALL editarCategoriaConMarcas(?, ?, ?, ?, ?)");
            $stmt->bind_param("isiss", $idCategoria, $nombre, $estado, $modificadoPor, $marcasCsv); 
            $stmt->execute();
            return true; 
        } catch (Exception $e) {
            error_log("Error en modelo_categoria->editarCategoriaConMarcas: " . $e->getMessage());
            if ($this->db->getConnection()->error) {
                error_log("MySQL Error: " . $this->db->getConnection()->error);
            }
            return false;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public function eliminarCategoria($id) {
        try {
            $stmt = $this->db->prepare("CALL eliminar_categoria(?)");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return $stmt->affected_rows > 0;
        } catch (mysqli_sql_exception $e) {
            error_log("Error de MySQL en eliminarCategoria (modelo): " . $e->getMessage() . " Código: " . $e->getCode());
            throw $e; 
        } catch (Exception $e) {
            error_log("Error general en eliminarCategoria (modelo): " . $e->getMessage());
            throw $e;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }
}
