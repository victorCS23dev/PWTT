<?php
require_once '../model/modelo_producto.php';

// Creamos el objeto producto una sola vez
$producto = new Producto();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $accion = $_POST['accion'] ?? '';  // Obtenemos la acción desde POST

    switch ($accion) {
        case 'registrar_producto':
            registrarProducto($producto);
            break;
        case 'editar_producto':
            editarProducto($producto);
            break;
        case 'eliminar_producto':
            eliminarProducto($producto);
            break;
        default:
            echo json_encode(["status" => 'error', "message" => "Acción no válida."]);
            break;
    }

    exit;
}  elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $accion = $_GET['accion'] ?? '';  // Obtenemos la acción desde GET

    switch ($accion) {
        case 'listar_productos':
            listar_productos($producto);
            break;
        case 'listar_productos_activos': 
            listarProductosCliente($producto);
            break;
        case 'obtener_producto':
            obtenerProducto($producto);
            break;
        case 'listar_productos_mas_vendidos':
            listarProductosMasVendidos($producto);
            break;
        case 'obtener_productos_bajo_stock_count': 
            obtenerProductosBajoStockCount($producto);
            break;
        default:
            echo json_encode(["status" => 'error', "message" => "Acción no válida (GET)."]);
            break;
    }

    exit;
    
} else {
    echo json_encode(["status" => 'error', "message" => "Método no permitido."]);
    exit;
}

function listarProductosCliente($producto_model) {
    // Parámetros de paginación
    $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 8;
    $desplazamiento = isset($_GET['desplazamiento']) ? (int)$_GET['desplazamiento'] : 0;

    // Parámetros de filtro
    $precio_min = isset($_GET['precio_min']) && $_GET['precio_min'] !== '' ? (float)$_GET['precio_min'] : null;
    $precio_max = isset($_GET['precio_max']) && $_GET['precio_max'] !== '' ? (float)$_GET['precio_max'] : null;
    $id_categoria = isset($_GET['id_categoria']) && $_GET['id_categoria'] !== '' ? (int)$_GET['id_categoria'] : null;
    // Ahora 'marcas' es un solo ID de marca, no un CSV, si se usa un select.
    // Si la marca es '0' o vacía, se interpreta como 'todas las marcas' (null para el modelo).
    $marcas_id = isset($_GET['marcas']) && $_GET['marcas'] !== '' ? (int)$_GET['marcas'] : null;
    $ordenar_por = isset($_GET['ordenar_por']) && $_GET['ordenar_por'] !== '' ? $_GET['ordenar_por'] : null;
    $search_query = isset($_GET['search_query']) && $_GET['search_query'] !== '' ? $_GET['search_query'] : null;
    
    try {
        // Pasa los parámetros al método del modelo
        $productos = $producto_model->listarProductosClientePaginadoConFiltros(
            $limite,
            $desplazamiento,
            $precio_min,
            $precio_max,
            $id_categoria,
            $marcas_id,
            $ordenar_por,
            $search_query
        );

        if ($productos !== null) {
            echo json_encode(['status' => 'success', 'data' => $productos]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se encontraron productos o hubo un error en la base de datos.']);
        }
    } catch (Exception $e) {
        error_log("Error en listarProductosCliente (controlador): " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error interno del servidor al cargar el catálogo: ' . $e->getMessage()]);
    }
}   

function listar_productos($productos) {
    try {
        $productos = $productos->listarProductos(); // Debes tener esta función en tu modelo

        if ($productos) {
            echo json_encode(['status' => 'success', 'data' => $productos]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se encontraron productos.']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error al obtener los productos: ' . $e->getMessage()]);
    }
}

function obtenerProducto($producto) {
    $idProducto = $_GET['id_producto'] ?? null;

    if ($idProducto) {
        try {
            $productoData = $producto->obtenerProductoPorId($idProducto);
            if ($productoData) {
                echo json_encode(['status' => 'success', 'data' => $productoData]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Producto no encontrado.']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al obtener el producto: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ID de producto no proporcionado.']);
    }
}

function registrarProducto($producto_model) {
    // 1. Recibir datos del producto
    $nombre = $_POST['nombre_producto'] ?? '';
    $marca = $_POST['idMarcas_producto'] ?? ''; // CAMBIO: Ahora se lee desde 'idMarcas_producto'
    $descripcion = $_POST['descripcion_producto'] ?? '';
    $precio = $_POST['precio_producto'] ?? '';
    $stock = $_POST['stock_producto'] ?? '';
    $idCategorias = $_POST['idCategorias_producto'] ?? '';
    // Nuevo: Recibe el ID del usuario creador
    $creadoPor = !empty($_POST['creado_por']) ? $_POST['creado_por'] : null;

    // Validaciones básicas (puedes hacerlas más robustas)
    if (empty($nombre) || empty($marca) || empty($precio) || empty($stock) || empty($idCategorias)) {
        echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios del producto (nombre, marca, precio, stock, categoría).']);
        return;
    }
    if (!is_numeric($precio) || $precio <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'El precio debe ser un número positivo.']);
        return;
    }
    if (!is_numeric($stock) || $stock < 0) {
        echo json_encode(['status' => 'error', 'message' => 'El stock debe ser un número no negativo.']);
        return;
    }

    // 2. Manejo de la imagen
    $imagen_url = ''; // Variable para almacenar el nombre del archivo a guardar en la DB

    if (isset($_FILES['imagen_producto']) && $_FILES['imagen_producto']['error'] === UPLOAD_ERR_OK) {
        $carpetaDestino = '../img/productos/'; // Ruta relativa a tu controlador
        
        // Asegúrate de que la carpeta de destino exista y tenga permisos de escritura
        if (!is_dir($carpetaDestino)) {
            mkdir($carpetaDestino, 0777, true); // Crea la carpeta si no existe
        }

        $nombreArchivo = basename($_FILES['imagen_producto']['name']);
        $rutaCompleta = $carpetaDestino . $nombreArchivo;
        $tipoArchivo = strtolower(pathinfo($rutaCompleta, PATHINFO_EXTENSION));

        // Validar formato de imagen
        $formatosPermitidos = ['jpg', 'jpeg', 'png']; // Agrega o quita formatos según necesites
        if (!in_array($tipoArchivo, $formatosPermitidos)) {
            echo json_encode(['status' => 'error', 'message' => 'Formato de imagen no permitido. Solo JPG, JPEG, PNG.']);
            return;
        }

        // Validar tamaño de archivo (ej. max 5MB)
        if ($_FILES['imagen_producto']['size'] > 5 * 1024 * 1024) { 
            echo json_encode(['status' => 'error', 'message' => 'La imagen es demasiado grande. Máximo 5MB.']);
            return;
        }

        // Mover el archivo subido
        if (move_uploaded_file($_FILES['imagen_producto']['tmp_name'], $rutaCompleta)) {
            $imagen_url = $nombreArchivo; // Esto es lo que guardarás en la base de datos
        } else {
            // Si hay un error al mover el archivo
            error_log("Error al mover archivo: " . $_FILES['imagen_producto']['tmp_name'] . " a " . $rutaCompleta);
            echo json_encode(['status' => 'error', 'message' => 'Error al subir la imagen al servidor.']);
            return;
        }
    } else {
        // Manejar el caso donde no se sube ninguna imagen (ej. si no es requerida)
        // O si hubo un error en la subida (ej. UPLOAD_ERR_NO_FILE)
        if ($_FILES['imagen_producto']['error'] === UPLOAD_ERR_NO_FILE) {
             echo json_encode(['status' => 'error', 'message' => 'Debe seleccionar una imagen para el producto.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Ocurrió un error inesperado al subir la imagen. Código: ' . $_FILES['imagen_producto']['error']]);
        }
        return; // Detener la ejecución si no hay imagen o hay error
    }

    // 3. Llamar al modelo para registrar el producto con la URL de la imagen
    try {
        // Pasar la marca y el creador al método registrarProducto del modelo
        $resultado = $producto_model->registrarProducto($nombre, $marca, $descripcion, $precio, $stock, $idCategorias, $imagen_url, $creadoPor);

        if ($resultado) {
            echo json_encode(['status' => 'success', "message" => "Producto registrado correctamente y imagen subida."]);
        } else {
            // Si el registro en la DB falla, puedes considerar borrar la imagen subida
            // unlink($rutaCompleta); // Descomentar si quieres revertir la subida de imagen
            echo json_encode(['status' => 'error', "message" => "Error al registrar el producto en la base de datos."]);
        }
    } catch (Exception $e) {
        error_log("Error al registrar producto en DB: " . $e->getMessage()); // Para depuración
        // unlink($rutaCompleta); // Descomentar si quieres revertir la subida de imagen
        echo json_encode(["status" => 'error', "message" => "Error interno al registrar el producto: " . $e->getMessage()]);
    }
}

function editarProducto($producto_model) { 
    $idProducto = $_POST['id_producto'] ?? null;
    // Nombres de los campos del formulario de edición (coinciden con tu ver_productos.php)
    $nombre = $_POST['nombre_producto'] ?? '';
    $idMarca = $_POST['idMarcas_producto'] ?? ''; 
    $descripcion = $_POST['descripcion_producto'] ?? '';
    $precio = $_POST['precio_producto'] ?? '';
    $stock = $_POST['stock_producto'] ?? '';
    $estado = $_POST['estado_producto'] ?? '';
    $idCategorias = $_POST['id_categoria_producto'] ?? ''; 
    $modificadoPor = !empty($_POST['modificado_por']) ? $_POST['modificado_por'] : null; // <--- AGREGADO: Recuperar modificado_por

    // Para la imagen actual, necesitas un campo oculto en tu HTML con name="imagen_url_actual"
    // Asegúrate de que este campo oculto se rellena en el JS al obtener el producto para editar
    $imagenActualUrl = $_POST['imagen_url_actual'] ?? ''; 
    $imagen_url_final = $imagenActualUrl; // Por defecto, mantiene la imagen actual

    if (!$idProducto) {
        echo json_encode(['status' => 'error', 'message' => 'ID de producto no proporcionado para edición.']);
        return;
    }

    // Validaciones básicas (estas validaciones deben ser completadas en JS también)
    if (empty($nombre) || empty($idMarca) || empty($precio) || empty($stock) || empty($idCategorias)) { // Validar marca
        echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios del producto para edición.']);
        return;
    }
    if (!is_numeric($precio) || $precio <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'El precio debe ser un número positivo.']);
        return;
    }
    if (!is_numeric($stock) || $stock < 0) {
        echo json_encode(['status' => 'error', 'message' => 'El stock debe ser un número no negativo.']);
        return;
    }

    // --- Manejo de la subida de NUEVA IMAGEN ---
    // El 'name' del input file es 'imagen_producto'
    if (isset($_FILES['imagen_producto']) && $_FILES['imagen_producto']['error'] === UPLOAD_ERR_OK) {
        $carpetaDestino = '../img/productos/';
        
        if (!is_dir($carpetaDestino)) {
            mkdir($carpetaDestino, 0777, true);
        }

        $nombreArchivoNuevo = uniqid('prod_') . '_' . basename($_FILES['imagen_producto']['name']); // Nombre único
        $rutaCompletaNuevo = $carpetaDestino . $nombreArchivoNuevo;
        $tipoArchivoNuevo = strtolower(pathinfo($rutaCompletaNuevo, PATHINFO_EXTENSION));

        $formatosPermitidos = ['jpg', 'jpeg', 'png'];
        if (!in_array($tipoArchivoNuevo, $formatosPermitidos)) {
            echo json_encode(['status' => 'error', 'message' => 'Formato de nueva imagen no permitido. Solo JPG, JPEG, PNG.']);
            return;
        }

        if ($_FILES['imagen_producto']['size'] > 5 * 1024 * 1024) { 
            echo json_encode(['status' => 'error', 'message' => 'La nueva imagen es demasiado grande. Máximo 5MB.']);
            return;
        }

        // Mover el archivo subido
        if (move_uploaded_file($_FILES['imagen_producto']['tmp_name'], $rutaCompletaNuevo)) {
            $imagen_url_final = $nombreArchivoNuevo;

            // Opcional: Eliminar la imagen antigua si existía y se subió una nueva
            if (!empty($imagenActualUrl) && file_exists($carpetaDestino . $imagenActualUrl)) {
                unlink($carpetaDestino . $imagenActualUrl);
            }
        } else {
            error_log("Error al mover nueva imagen: " . $_FILES['imagen_producto']['tmp_name'] . " a " . $rutaCompletaNuevo);
            echo json_encode(['status' => 'error', 'message' => 'Error al subir la nueva imagen al servidor.']);
            return;
        }
    }
    // Si no se subió una nueva imagen, $imagen_url_final mantiene la $imagenActualUrl

    try {
        $resultado = $producto_model->actualizarProducto( 
            $idProducto,
            $nombre,
            $idMarca, 
            $descripcion,
            $precio,
            $stock,
            $idCategorias,
            $imagen_url_final, 
            $estado,
            $modificadoPor 
        );

        if ($resultado) {
            echo json_encode(['status' => 'success', 'message' => 'Producto actualizado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el producto en la base de datos.']);
        }
    } catch (Exception $e) {
        error_log("Error al actualizar producto en DB: " . $e->getMessage());
        echo json_encode(["status" => 'error', "message" => "Error interno al actualizar el producto: " . $e->getMessage()]);
    }
}

function eliminarProducto($producto) {
    $idProducto = $_POST['id_producto'] ?? null;

    if ($idProducto) {
        try {
            $resultado = $producto->eliminarProducto($idProducto);
            if ($resultado) {
                echo json_encode(['status' => 'success', 'message' => 'Producto eliminado correctamente.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error al eliminar el producto.']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al eliminar el producto: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ID de producto no proporcionado para eliminar.']);
    }
}

function listarProductosMasVendidos($producto_model) {
    try {
        $productos = $producto_model->listarProductosMasVendidos();
        if ($productos) {
            echo json_encode(['status' => 'success', 'data' => $productos]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se encontraron productos más vendidos.']);
        }
    } catch (Exception $e) {
        error_log("Error en listarProductosMasVendidos (controlador): " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error al obtener los productos más vendidos: ' . $e->getMessage()]);
    }
}

function obtenerProductosBajoStockCount($producto_model) {
    $umbral = $_GET['umbral'] ?? 5;
    try {
        $count = $producto_model->obtenerProductosBajoStockCount($umbral);
        echo json_encode(['status' => 'success', 'count' => $count]);
    } catch (Exception $e) {
        error_log("Error en obtenerProductosBajoStockCount (controlador): " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error al obtener el conteo de productos con bajo stock.']);
    }
}

?>