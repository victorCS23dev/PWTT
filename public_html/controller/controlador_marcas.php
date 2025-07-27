<?php
// Incluye el modelo de marca para interactuar con la base de datos
require_once '../model/modelo_marca.php';

// Creamos el objeto marca una sola vez
$marca = new Marca();

// Verifica el método de la solicitud HTTP
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $accion = $_POST['accion'] ?? '';  // Obtiene la acción desde POST

    switch ($accion) {
        case 'registrar_marca':
            registrarMarca($marca);
            break;
        case 'editar_marca':
            editarMarca($marca);
            break;
        case 'eliminar_marca':
            eliminarMarca($marca);
            break;
        default:
            echo json_encode(["status" => 'error', "message" => "Acción POST no válida."]);
            break;
    }
    exit; // Detiene la ejecución después de enviar la respuesta JSON

} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $accion = $_GET['accion'] ?? '';  // Obtiene la acción desde GET

    switch ($accion) {
        case 'listar_marcas':
            listarMarcas($marca);
            break;
        case 'listar_marcas_select':
            listarMarcasParaSelect($marca);
            break;
        case 'listar_todas_las_marcas': 
            listarTodasLasMarcas($marca);
            break;
        case 'obtener_marca':
            obtenerMarca($marca);
            break;
        default:
            echo json_encode(["status" => 'error', "message" => "Acción GET no válida."]);
            break;
    }
    exit; // Detiene la ejecución después de enviar la respuesta JSON

} else {
    // Si el método de la solicitud no es POST ni GET
    echo json_encode(["status" => 'error', "message" => "Método no permitido."]);
    exit;
}

function registrarMarca($marca_model) {
    $nombre = $_POST['nombre_marca'] ?? '';
    // Obtener el ID del usuario que crea y convertir a NULL si está vacío
    $creadoPor = !empty($_POST['creado_por']) ? $_POST['creado_por'] : null;

    if (empty($nombre)) {
        echo json_encode(['status' => 'error', "message" => "El nombre de la marca es obligatorio."]);
        return;
    }

    try {
        $resultado = $marca_model->registrarMarca($nombre, $creadoPor); 
        
        if ($resultado) {
            echo json_encode(['status' => 'success', "message" => "Marca registrada correctamente."]);
        } else {
            echo json_encode(['status' => 'error', "message" => "Error al registrar la marca. Posiblemente ya existe una marca con ese nombre."]);
        }

    } catch (Exception $e) {
        error_log("Error en registrarMarca (controlador): " . $e->getMessage());
        echo json_encode(["status" => 'error', "message" => "Error interno del servidor al registrar la marca."]);
    }
}

function listarMarcas($marca_model) {
    try {
        $marcas = $marca_model->listarMarcas(); 

        if ($marcas) {
            echo json_encode(['status' => 'success', 'data' => $marcas]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se encontraron marcas.']);
        }
    } catch (Exception $e) {
        error_log("Error al listar marcas (controlador): " . $e->getMessage()); 
        echo json_encode(['status' => 'error', 'message' => 'Error al obtener las marcas: ' . $e->getMessage()]);
    }
}

function listarTodasLasMarcas($marca_model) {
    try {
        $marcas = $marca_model->listarTodasLasMarcas(); // Llama al nuevo método en el modelo
        if ($marcas) {
            echo json_encode(['status' => 'success', 'data' => $marcas]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se encontraron marcas.']);
        }
    } catch (Exception $e) {
        error_log("Error en listarTodasLasMarcas (controlador): " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error al obtener todas las marcas: ' . $e->getMessage()]);
    }
}

function listarMarcasParaSelect($marca_model) {
    try {
        $marcas = $marca_model->listarMarcasParaSelect();
        if ($marcas) {
            echo json_encode(['status' => 'success', 'data' => $marcas]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se encontraron marcas para select.']);
        }
    } catch (Exception $e) {
        error_log("Error en listarMarcasParaSelect (controlador): " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error al obtener las marcas para select: ' . $e->getMessage()]);
    }
}

function obtenerMarca($marca_model) {
    $idMarca = $_GET['id_marca'] ?? null;

    if (empty($idMarca)) {
        echo json_encode(['status' => 'error', 'message' => 'ID de marca no proporcionado para obtener.']);
        return;
    }

    try {
        $data = $marca_model->obtenerMarcaPorId($idMarca);
        if ($data) {
            echo json_encode(['status' => 'success', 'data' => $data]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Marca no encontrada.']);
        }
    } catch (Exception $e) {
        error_log("Error en obtenerMarca (controlador): " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error interno del servidor al obtener la marca.']);
    }
}

function editarMarca($marca_model) {
    $idMarca = $_POST['id_marca'] ?? null;
    $nombreMarca = $_POST['nombre_marca'] ?? null;
    $estadoMarca = $_POST['estado_marca'] ?? null;
    $modificadoPor = !empty($_POST['modificado_por']) ? $_POST['modificado_por'] : null; 

    if (empty($idMarca) || empty($nombreMarca) || $estadoMarca === null) {
        echo json_encode(['status' => 'error', 'message' => 'Datos de marca incompletos para actualizar.']);
        return;
    }

    try {
        $resultado = $marca_model->actualizarMarca($idMarca, $nombreMarca, $estadoMarca, $modificadoPor); 
        if ($resultado) {
            echo json_encode(['status' => 'success', 'message' => 'Marca actualizada correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar la marca.']);
        }
    } catch (Exception $e) {
        error_log("Error en editarMarca (controlador): " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar la marca: ' . $e->getMessage()]);
    }
}

function eliminarMarca($marca_model) {
    $idMarca = $_POST['id_marca'] ?? null;

    if (empty($idMarca)) {
        echo json_encode(['status' => 'error', 'message' => 'ID de marca no proporcionado para eliminar.']);
        return;
    }

    try {
        $resultado = $marca_model->eliminarMarca($idMarca);
        if ($resultado) {
            echo json_encode(['status' => 'success', 'message' => 'Marca eliminada correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al eliminar la marca.']);
        }
    } catch (mysqli_sql_exception $e) {
        // Captura la excepción específica de MySQLi (ej. error 1451 por clave foránea)
        if ($e->getCode() == 1451) {
            echo json_encode(['status' => 'error', 'message' => 'No se puede eliminar la marca porque tiene productos asociados. Edita o elimina primero los productos relacionados.']);
        } else {
            error_log("SQL Error al eliminar marca: " . $e->getMessage() . " Code: " . $e->getCode());
            echo json_encode(['status' => 'error', 'message' => 'Error de base de datos al eliminar la marca: ' . $e->getMessage()]);
        }
    } catch (Exception $e) {
        error_log("Error general al eliminar marca: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Ocurrió un error inesperado al intentar eliminar la marca: ' . $e->getMessage()]);
    }
}
