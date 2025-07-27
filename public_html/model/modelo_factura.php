<?php

header("Cache-Control: no-cache");
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once __DIR__ . '/../connectDB/conexion.php'; 

class Factura {
    private $db;

    public function __construct() {
        $this->db = new Database(); // Asumiendo que la clase Database contiene el método conectar()
    }

    public function listarFacturasPorUsuario($idUsuario) {
        $stmt = null; // Inicializar $stmt a null para el bloque finally
        try {
            $stmt = $this->db->prepare("CALL listar_facturas_usuario(?)");
            
            if ($stmt === false) {
                error_log("Error al preparar la consulta listar_facturas_usuario: " . $this->db->getLastError());
                return []; 
            }

            $stmt->bind_param("i", $idUsuario);
            $stmt->execute();

            $result = $stmt->get_result();
            $facturas = [];

            while ($fila = $result->fetch_assoc()) {
                $facturas[] = $fila;
            }

            return $facturas; 

        } catch (Exception $e) {
            error_log("Error al listar facturas por usuario (modelo): " . $e->getMessage());
            return []; 
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public function registrarFactura($idUsuario, $metodoPago, $montoTotal, $productosJSON, $idCodigoDescuento, $montoDescuento) {
        $stmt = null;
        try {
            // Ajustar la llamada al procedimiento almacenado para incluir los nuevos parámetros
            $sql = "CALL registrar_factura(?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            if ($stmt === false) {
                error_log("Error al preparar CALL registrar_factura: " . $this->db->getLastError());
                return false;
            }

            // 'isdsid' para INT, STRING, DECIMAL, STRING, INT (o NULL), DECIMAL
            // Para idCodigoDescuento, si es NULL, se debe pasar como 'null' explícitamente en el bind_param
            $stmt->bind_param("isdsid", $idUsuario, $metodoPago, $montoTotal, $productosJSON, $idCodigoDescuento, $montoDescuento);

            if (!$stmt->execute()) {
                error_log("Error al ejecutar CALL registrar_factura: " . $stmt->error);
                return false;
            }

            // El procedimiento almacenado devuelve el ID de la factura como un resultado
            $result = $stmt->get_result();
            if ($result && $row = $result->fetch_row()) {
                return $row[0]; // Retorna el ID de la factura
            } else {
                error_log("registrarFactura: No se obtuvo el ID de la factura del procedimiento almacenado.");
                return false;
            }
        } catch (Exception $e) {
            error_log("Excepción en registrarFactura (modelo): " . $e->getMessage()); 
            return false;
        } finally {
            if (isset($stmt) && $stmt) $stmt->close();
        }
    }

    public function solicitarCancelacionFactura($idFactura) {
        $stmt = null;
        try {
            $stmt = $this->db->prepare("CALL solicitar_cancelacion_factura(?)");
            if ($stmt === false) {
                error_log("Error al preparar la consulta solicitar_cancelacion_factura: " . $this->db->getLastError());
                return false;
            }
            $stmt->bind_param("i", $idFactura);
            if (!$stmt->execute()) {
                // Captura errores específicos del procedimiento (como el SIGNAL SQLSTATE)
                error_log("Error al ejecutar la consulta solicitar_cancelacion_factura: " . $stmt->error);
                return false;
            }
            // Si affected_rows es 0, puede ser que no se actualizó porque el estado no era cancelable.
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Excepción al solicitar cancelación de factura (modelo): " . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt) && $stmt) $stmt->close();
        }
    }

    public function obtenerDetalleFactura($idFactura) {
        $stmt = null;
        try {
            $stmt = $this->db->prepare("CALL obtener_detalle_factura(?)");
            if ($stmt === false) {
                error_log("Error al preparar la consulta obtener_detalle_factura: " . $this->db->getLastError());
                return [];
            }
            $stmt->bind_param("i", $idFactura);
            $stmt->execute();
            $result = $stmt->get_result();
            $detalles = [];
            while ($fila = $result->fetch_assoc()) {
                $detalles[] = $fila;
            }
            return $detalles; // Devuelve un array de los detalles de la factura
        } catch (Exception $e) {
            error_log("Excepción al obtener detalle de factura (modelo): " . $e->getMessage());
            return [];
        } finally {
            if (isset($stmt) && $stmt) $stmt->close();
        }
    }

    public function listarTodasLasFacturas() {
        $stmt = null;
        $facturas = [];
        try {
            $sql = "CALL listar_facturas()";
            $stmt = $this->db->prepare($sql);
            if ($stmt === false) {
                error_log("Error al preparar CALL listar_facturas: " . $this->db->getLastError());
                return null;
            }
            $stmt->execute();
            $result = $stmt->get_result();
            while ($fila = $result->fetch_assoc()) {
                $facturas[] = $fila;
            }
            return $facturas;
        } catch (Exception $e) {
            error_log("Excepción en listarTodasLasFacturas (modelo): " . $e->getMessage());
            return null;
        } finally {
            if (isset($stmt) && $stmt) $stmt->close();
        }
    }

    public function actualizarEstadoFactura($idFactura, $nuevoEstado, $modificadoPor) {
        $stmt = null;
        try {
            $sql = "CALL actualizar_estado_factura(?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            if ($stmt === false) {
                error_log("Error al preparar CALL actualizar_estado_factura: " . $this->db->getLastError());
                return false;
            }
            $stmt->bind_param("iii", $idFactura, $nuevoEstado, $modificadoPor);
            $stmt->execute();
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Excepción en actualizarEstadoFactura (modelo): " . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt) && $stmt) $stmt->close();
        }
    }

    public function obtenerVentasTotalesMes() {
        $stmt = null;
        try {
            $stmt = $this->db->prepare("CALL obtener_ventas_totalesMes()");
            if ($stmt === false) {
                error_log("Error al preparar obtener_ventas_totalesMes: " . $this->db->getLastError());
                return 0.00;
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            return $data['total_ventas'] ?? 0.00;
        } catch (Exception $e) {
            error_log("Error en obtenerVentasTotalesMes (modelo): " . $e->getMessage());
            return 0.00;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public function obtenerPedidosPendientesCount() {
        $stmt = null;
        try {
            $stmt = $this->db->prepare("CALL obtener_pedidos_pendientes_count()");
            if ($stmt === false) {
                error_log("Error al preparar obtener_pedidos_pendientes_count: " . $this->db->getLastError());
                return 0;
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            return $data['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error en obtener_pedidos_pendientes_count (modelo): " . $e->getMessage());
            return 0;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public function obtenerVentasPorRangoFechas($fechaInicio, $fechaFin) {
        $stmt = null;
        try {
            $stmt = $this->db->prepare("CALL obtener_ventas_por_rango_fechas(?, ?)");
            if ($stmt === false) {
                error_log("Error al preparar obtener_ventas_por_rango_fechas: " . $this->db->getLastError());
                return false;
            }
            // 'ss' indica dos parámetros de tipo string
            $stmt->bind_param("ss", $fechaInicio, $fechaFin);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $ventas = [];
            while ($fila = $resultado->fetch_assoc()) {
                $ventas[] = $fila;
            }
            return $ventas;
        } catch (Exception $e) {
            error_log("Error en modelo_factura->obtener_ventas_por_rango_fechas: " . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

}
?>
