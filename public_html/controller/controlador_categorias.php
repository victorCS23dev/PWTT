<?php
// Incluye el modelo de categoría para interactuar con la base de datos
require_once '../model/modelo_categoria.php';

// Creamos el objeto categoría una sola vez
$categoria = new Categoria();

// Verifica el método de la solicitud HTTP
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $accion = $_POST['accion'] ?? ''; 

    switch ($accion) {
        case 'registrar_categoria':
            registrarCategoria($categoria);
            break;
        case 'editar_categoria': 
            editarCategoria($categoria);
            break;
        case 'editar_categoria_con_marcas': 
            editarCategoriaConMarcas($categoria);
            break;
        case 'eliminar_categoria':
            eliminarCategoria($categoria);
            break;
        default:
            echo json_encode(["status" => 'error', "message" => "Acción POST no válida."]);
            break;
    }
    exit; 

} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $accion = $_GET['accion'] ?? '';  

    switch ($accion) {
        case 'listar_categorias':
            listarCategorias($categoria);
            break;
        case 'listar_categorias_select': 
            listarCategoriasParaSelect($categoria);
            break;
        case 'obtener_categoria':
            obtenerCategoria($categoria);
            break;
        case 'obtener_marcas_por_categoria': 
            obtenerMarcasPorCategoria($categoria);
            break;
        default:
            echo json_encode(["status" => 'error', "message" => "Acción GET no válida."]);
            break;
    }
    exit;

} else {
    echo json_encode(["status" => 'error', "message" => "Método no permitido."]);
    exit;
}

function registrarCategoria($categoria_model) {
    $nombre = $_POST['nombre_categoria'] ?? '';
    $creadoPor = !empty($_POST['creado_por']) ? $_POST['creado_por'] : null;

    if (empty($nombre)) {
        echo json_encode(['status' => 'error', "message" => "El nombre de la categoría es obligatorio."]);
        return;
    }

    try {
        $resultado = $categoria_model->registrarCategoria($nombre, $creadoPor); 
        
        if ($resultado) {
            echo json_encode(['status' => 'success', "message" => "Categoría registrada correctamente."]);
        } else {
            echo json_encode(['status' => 'error', "message" => "Error al registrar la categoría. Posiblemente ya existe una categoría con ese nombre."]);
        }

    } catch (Exception $e) {
        error_log("Error en registrarCategoria (controlador): " . $e->getMessage());
        echo json_encode(["status" => 'error', "message" => "Error interno del servidor al registrar la categoría."]);
    }
}

function listarCategorias($categoria_model) {
    try {
        $categorias = $categoria_model->listarCategorias(); 

        if ($categorias) {
            echo json_encode(['status' => 'success', 'data' => $categorias]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se encontraron categorías.']);
        }
    } catch (Exception $e) {
        error_log("Error al listar categorías (controlador): " . $e->getMessage()); 
        echo json_encode(['status' => 'error', 'message' => 'Error al obtener las categorías: ' . $e->getMessage()]);
    }
}

function listarCategoriasParaSelect($categoria_model) {
    try {
        $categorias = $categoria_model->listarCategoriasParaSelect();
        if ($categorias) {
            echo json_encode(['status' => 'success', 'data' => $categorias]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se encontraron categorías para select.']);
        }
    } catch (Exception $e) {
        error_log("Error en listarCategoriasParaSelect (controlador): " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error al obtener las categorías para select: ' . $e->getMessage()]);
    }
}

function obtenerCategoria($categoria_model) {
    $idCategoria = $_GET['id_categoria'] ?? null;

    if (empty($idCategoria)) {
        echo json_encode(['status' => 'error', 'message' => 'ID de categoría no proporcionado para obtener.']);
        return;
    }

    try {
        $data = $categoria_model->obtenerCategoriaPorId($idCategoria);
        if ($data) {
            echo json_encode(['status' => 'success', 'data' => $data]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Categoría no encontrada.']);
        }
    } catch (Exception $e) {
        error_log("Error en obtenerCategoria (controlador): " . $e->getMessage());
        echo json_encode(['status' => 'error', "message" => "Error interno del servidor al obtener la categoría."]);
    }
}

// NUEVA FUNCIÓN: Para obtener las marcas asociadas a una categoría
function obtenerMarcasPorCategoria($categoria_model) {
    $idCategoria = $_GET['id_categoria'] ?? null;

    if (empty($idCategoria)) {
        echo json_encode(['status' => 'error', 'message' => 'ID de categoría no proporcionado para obtener marcas asociadas.']);
        return;
    }

    try {
        $marcas = $categoria_model->obtenerMarcasPorCategoria($idCategoria);
        if ($marcas !== false) { // Puede devolver un array vacío, lo cual es válido
            echo json_encode(['status' => 'success', 'data' => $marcas]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al obtener las marcas asociadas.']);
        }
    } catch (Exception $e) {
        error_log("Error en obtenerMarcasPorCategoria (controlador): " . $e->getMessage());
        echo json_encode(['status' => 'error', "message" => "Error interno del servidor al obtener marcas asociadas."]);
    }
}


function editarCategoria($categoria_model) {
    $idCategoria = $_POST['id_categoria'] ?? null;
    $nombreCategoria = $_POST['nombre_categoria'] ?? null;
    $estadoCategoria = $_POST['estado_categoria'] ?? null;
    $modificadoPor = !empty($_POST['modificado_por']) ? $_POST['modificado_por'] : null; 

    if (empty($idCategoria) || empty($nombreCategoria) || $estadoCategoria === null) {
        echo json_encode(['status' => 'error', 'message' => 'Datos de categoría incompletos para actualizar.']);
        return;
    }

    try {
        $resultado = $categoria_model->actualizarCategoria($idCategoria, $nombreCategoria, $estadoCategoria, $modificadoPor); 
        if ($resultado) {
            echo json_encode(['status' => 'success', 'message' => 'Categoría actualizada correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar la categoría.']);
        }
    } catch (Exception $e) {
        error_log("Error en editarCategoria (controlador): " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar la categoría: ' . $e->getMessage()]);
    }
}

// NUEVA FUNCIÓN: Para editar la categoría y sus marcas asociadas
function editarCategoriaConMarcas($categoria_model) {
    $idCategoria = $_POST['id_categoria'] ?? null;
    $nombreCategoria = $_POST['nombre_categoria'] ?? null;
    $estadoCategoria = $_POST['estado_categoria'] ?? null;
    $modificadoPor = !empty($_POST['modificado_por']) ? $_POST['modificado_por'] : null; 
    $marcasAsociadas = $_POST['marcas_asociadas'] ?? []; // Array de IDs de marcas

    // Convertir el array de IDs de marcas a una cadena CSV
    $marcasCsv = implode(',', array_map('intval', $marcasAsociadas));

    if (empty($idCategoria) || empty($nombreCategoria) || $estadoCategoria === null) {
        echo json_encode(['status' => 'error', 'message' => 'Datos de categoría incompletos para actualizar.']);
        return;
    }

    try {
        $resultado = $categoria_model->editarCategoriaConMarcas(
            $idCategoria, 
            $nombreCategoria, 
            $estadoCategoria, 
            $modificadoPor, 
            $marcasCsv
        ); 
        
        if ($resultado) {
            echo json_encode(['status' => 'success', 'message' => 'Categoría y marcas asociadas actualizadas correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar la categoría y sus marcas.']);
        }
    } catch (Exception $e) {
        error_log("Error en editarCategoriaConMarcas (controlador): " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar la categoría y sus marcas: ' . $e->getMessage()]);
    }
}

function eliminarCategoria($categoria_model) {
    $idCategoria = $_POST['id_categoria'] ?? null;

    if (empty($idCategoria)) {
        echo json_encode(['status' => 'error', 'message' => 'ID de categoría no proporcionado para eliminar.']);
        return;
    }

    try {
        $resultado = $categoria_model->eliminarCategoria($idCategoria);
        if ($resultado) {
            echo json_encode(['status' => 'success', 'message' => 'Categoría eliminada correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al eliminar la categoría.']);
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1451) {
            echo json_encode(['status' => 'error', 'message' => 'No se puede eliminar la categoría porque tiene productos asociados. Edita o elimina primero los productos relacionados.']);
        } else {
            error_log("SQL Error al eliminar categoría: " . $e->getMessage() . " Code: " . $e->getCode());
            echo json_encode(['status' => 'error', 'message' => 'Error de base de datos al eliminar la categoría: ' . $e->getMessage()]);
        }
    } catch (Exception $e) {
        error_log("Error general al eliminar categoría: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Ocurrió un error inesperado al intentar eliminar la categoría: ' . $e->getMessage()]);
    }
}
