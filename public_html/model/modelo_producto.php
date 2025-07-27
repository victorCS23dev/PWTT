<?php

header("Cache-Control: no-cache");
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once __DIR__ . '/../connectDB/conexion.php'; 

class Producto {
    private $db;

    public function __construct() {
        $this->db = new Database(); 
    }

    public function listarProductosClientePaginadoConFiltros($limite, $desplazamiento, $precio_min, $precio_max, $id_categoria, $id_marca, $ordenar_por, $search_query = null) {
        $stmt = null;
        try {
            $stmt = $this->db->prepare("CALL listar_productos_activos(?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iiddiiss", $limite, $desplazamiento, $precio_min, $precio_max, $id_categoria, $id_marca, $ordenar_por, $search_query);
            
            $stmt->execute();
            $resultado = $stmt->get_result();

            $productos = [];
            while ($fila = $resultado->fetch_assoc()) {
                $productos[] = $fila;
            }
            return $productos;
        } catch (Exception $e) {
            error_log("Error al listar productos con paginación, filtros y ordenamiento: " . $e->getMessage());
            return null;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public function listarMarcasMasUsadas($limite = 5) {
        $stmt = null;
        try {
            // Consulta para obtener las marcas más usadas
            $query = "
                SELECT marca, COUNT(marca) as count
                FROM productos
                WHERE estado = 1 -- Solo productos activos
                GROUP BY marca
                ORDER BY count DESC
                LIMIT ?
            ";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $limite);
            $stmt->execute();
            $resultado = $stmt->get_result();

            $marcas = [];
            while ($fila = $resultado->fetch_assoc()) {
                $marcas[] = $fila;
            }
            return $marcas;
        } catch (Exception $e) {
            error_log("Error al listar marcas más usadas: " . $e->getMessage());
            return null;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public function registrarProducto($nombre, $marca, $descripcion, $precio, $stock, $idCategorias, $imagen_url, $creadoPor) {
        try {
            // Preparamos la consulta con la llamada al procedimiento almacenado 'registrar_producto'
            $stmt = $this->db->prepare("CALL registrar_producto(?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssdiisi", $nombre, $marca, $descripcion, $precio, $stock, $idCategorias, $imagen_url, $creadoPor);
            $stmt->execute();

            // Si la ejecución fue exitosa, retornamos true
            return $stmt->affected_rows > 0;

        } catch (Exception $e) {
            // Si hubo un error, registramos el error y retornamos false
            error_log("Error al registrar producto en el modelo: " . $e->getMessage());
            return false;
        } finally {
            if ($stmt) {
                $stmt->close();
            }
        }
    }

    public function listarProductos() {
        try {
            // Preparamos la llamada al procedimiento almacenado
            $stmt = $this->db->prepare("CALL listar_productos()");
            // Ejecutamos el procedimiento
            $stmt->execute();
            // Obtenemos el resultado
            $resultado = $stmt->get_result();
            // Convertimos el resultado a array asociativo
            $productos = [];
            while ($fila = $resultado->fetch_assoc()) {
                $productos[] = $fila;
            }
            // Cerramos el statement y retornamos los productos
            $stmt->close();
            return $productos;
        } catch (Exception $e) {
            return null; // Si hay un error, retornamos null
        }
    }

    public function obtenerProductoPorId($id) {
        try {
            $stmt = $this->db->prepare("CALL obtener_producto_por_id(?)");
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

    public function actualizarProducto($id, $nombre, $marca, $descripcion, $precio, $stock, $idCategorias, $imagen_url, $estado, $modificadoPor) { // <--- AGREGADO: marca y modificadoPor
        try {
            $stmt = $this->db->prepare("CALL actualizar_producto(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            // Tipos de binding: i (id), s (nombre), i (marca), s (descripcion), d (precio), i (stock), 
            // i (idCategorias), s (imagen_url), i (estado), i (modificadoPor)
            $stmt->bind_param("isisdiisii", $id, $nombre, $marca, $descripcion, $precio, $stock, $idCategorias, $imagen_url, $estado, $modificadoPor); // <--- AGREGADO: 's' para marca, 'i' para modificadoPor
            $stmt->execute();
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Error al editar producto (modelo): " . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public function eliminarProducto($id) {
        try {
            $stmt = $this->db->prepare("CALL eliminar_producto(?)");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            return false;
        } finally {
            if ($stmt) {
                $stmt->close();
            }
        }
    }

    public function obtenerStockProducto($idProducto) {
        try {
            $stmt = $this->db->prepare("CALL obtenerStockProducto(?)");
            $stmt->bind_param("i", $idProducto);
            $stmt->execute();
            $result = $stmt->get_result();
            $stock_data = $result->fetch_assoc(); 
            return $stock_data; 
        } catch (Exception $e) {
            error_log("Error en modelo_producto->obtenerStockProducto: " . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public function listarProductosMasVendidos($limit = 10) {
        $stmt = null;
        try {
            $stmt = $this->db->prepare("CALL listar_productos_mas_vendidos(?)");
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $productos = [];
            while ($fila = $resultado->fetch_assoc()) {
                $productos[] = $fila;
            }
            return $productos;
        } catch (Exception $e) {
            error_log("Error en modelo_producto->listar_productos_mas_vendidos: " . $e->getMessage());
            return null;
        }
    }

    public function obtenerProductosBajoStockCount($umbral = 5) {
        $stmt = null;
        try {
            $stmt = $this->db->prepare("CALL obtener_productos_bajo_stockCount(?)");
            if ($stmt === false) {
                error_log("Error al preparar obtener_productos_bajo_stockCount: " . $this->db->getLastError());
                return 0;
            }
            $stmt->bind_param("i", $umbral);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            return $data['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error en obtener_productos_bajo_stockCount (modelo): " . $e->getMessage());
            return 0;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

}

?>