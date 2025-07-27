<?php
// model/modelo_descuentos.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once __DIR__ . '/../connectDB/conexion.php'; 
class Descuento {
    private $db;

    public function __construct() {
        $this->db = new Database(); 
    }

    public function registrarDescuento($codigo, $valor_descuento, $aplica_a_categoria, $aplica_a_marca, $descripcion, $fecha_inicio, $fecha_fin, $creado_por) {
        $stmt = null;
        try {
            // Se cambió el nombre del procedimiento almacenado a 'registrar_descuento'
            $sql = "CALL registrar_descuento(?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            if ($stmt === false) {
                error_log("Error al preparar CALL registrar_descuento: " . $this->db->getLastError());
                return false;
            }

            // Manejo de valores NULL para bind_param
            $aplica_a_categoria = $aplica_a_categoria === '' ? null : (int)$aplica_a_categoria;
            $aplica_a_marca = $aplica_a_marca === '' ? null : (int)$aplica_a_marca;
            $descripcion = $descripcion === '' ? null : $descripcion;
            $fecha_fin = $fecha_fin === '' ? null : $fecha_fin;
            
            // 'sdiisssi' -> s: string, d: double, i: integer, i: integer, s: string, s: string, s: string, i: integer
            $stmt->bind_param("sdiisssi", 
                $codigo, $valor_descuento, 
                $aplica_a_categoria, $aplica_a_marca, $descripcion, 
                $fecha_inicio, $fecha_fin, $creado_por
            );

            if (!$stmt->execute()) {
                error_log("Error al ejecutar CALL registrar_descuento: " . $stmt->error);
                return false;
            }
            return true;
        } catch (Exception $e) {
            error_log("Excepción en registrarDescuento (modelo): " . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt) && $stmt) $stmt->close();
        }
    }

    public function obtenerTodosLosDescuentos() {
        $stmt = null;
        $descuentos = [];
        try {
            // Se cambió el nombre del procedimiento almacenado a 'listar_descuentos'
            $sql = "CALL listar_descuentos()";
            $stmt = $this->db->prepare($sql);
            if ($stmt === false) {
                error_log("Error al preparar CALL listar_descuentos: " . $this->db->getLastError());
                return [];
            }
            $stmt->execute();
            $result = $stmt->get_result();
            while ($fila = $result->fetch_assoc()) {
                $descuentos[] = $fila;
            }
            return $descuentos;
        } catch (Exception $e) {
            error_log("Excepción en obtenerTodosLosDescuentos (modelo): " . $e->getMessage());
            return [];
        } finally {
            if (isset($stmt) && $stmt) $stmt->close();
        }
    }

    public function obtenerDescuentoPorId($idCodigo) {
        $stmt = null;
        try {
            // Se cambió el nombre del procedimiento almacenado a 'obtener_descuento_por_id'
            $sql = "CALL obtener_descuento_por_id(?)";
            $stmt = $this->db->prepare($sql);
            if ($stmt === false) {
                error_log("Error al preparar CALL obtener_descuento_por_id: " . $this->db->getLastError());
                return null;
            }
            $stmt->bind_param("i", $idCodigo);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("Excepción en obtenerDescuentoPorId (modelo): " . $e->getMessage());
            return null;
        } finally {
            if (isset($stmt) && $stmt) $stmt->close();
        }
    }

    public function actualizarDescuento($idCodigo, $codigo, $valor_descuento, $aplica_a_categoria, $aplica_a_marca, $descripcion, $fecha_inicio, $fecha_fin, $estado, $modificado_por) {
        $stmt = null;
        try {
            // Se cambió el nombre del procedimiento almacenado a 'actualizar_descuento'
            $sql = "CALL actualizar_descuento(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            if ($stmt === false) {
                error_log("Error al preparar CALL actualizar_descuento: " . $this->db->getLastError());
                return false;
            }

            // Manejo de valores NULL para bind_param
            $aplica_a_categoria = $aplica_a_categoria === '' ? null : (int)$aplica_a_categoria;
            $aplica_a_marca = $aplica_a_marca === '' ? null : (int)$aplica_a_marca;
            $descripcion = $descripcion === '' ? null : $descripcion;
            $fecha_fin = $fecha_fin === '' ? null : $fecha_fin;

            // 'isdiisssii' -> i: integer, s: string, d: double, i: integer, i: integer, s: string, s: string, s: string, i: integer, i: integer
            $stmt->bind_param("isdiisssii", 
                $idCodigo, $codigo, $valor_descuento, 
                $aplica_a_categoria, $aplica_a_marca, $descripcion, 
                $fecha_inicio, $fecha_fin, $estado, $modificado_por
            );

            if (!$stmt->execute()) {
                error_log("Error al ejecutar CALL actualizar_descuento: " . $stmt->error);
                return false;
            }
            return true;
        } catch (Exception $e) {
            error_log("Excepción en actualizarDescuento (modelo): " . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt) && $stmt) $stmt->close();
        }
    }

    public function eliminarDescuento($idCodigo) {
        $stmt = null;
        try {
            // Se cambió el nombre del procedimiento almacenado a 'eliminar_descuento'
            $sql = "CALL eliminar_descuento(?)";
            $stmt = $this->db->prepare($sql);
            if ($stmt === false) {
                error_log("Error al preparar CALL eliminar_descuento: " . $this->db->getLastError());
                return false;
            }
            $stmt->bind_param("i", $idCodigo);
            if (!$stmt->execute()) {
                error_log("Error al ejecutar CALL eliminar_descuento: " . $stmt->error);
                return false;
            }
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Excepción en eliminarDescuento (modelo): " . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt) && $stmt) $stmt->close();
        }
    }

    public function validarCodigoDescuento($codigo) {
        $stmt = null;
        try {
            // Llama al procedimiento almacenado para validar el código
            $stmt = $this->db->prepare("CALL validar_codigo_descuento(?)");
            if ($stmt === false) {
                error_log("Error al preparar CALL validar_codigo_descuento: " . $this->db->getLastError());
                return false;
            }
            $stmt->bind_param("s", $codigo);
            $stmt->execute();
            $result = $stmt->get_result();
            $descuento = $result->fetch_assoc(); // Obtiene los detalles del descuento

            // Si se encontró un descuento y está activo y dentro del rango de fechas
            if ($descuento) {
                // No es necesario verificar estado y fechas aquí, el SP ya lo hace
                return $descuento;
            } else {
                return false; // Código no válido o no aplicable
            }
        } catch (Exception $e) {
            error_log("Excepción en validarCodigoDescuento (modelo): " . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }
}
?>