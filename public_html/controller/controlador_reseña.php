<?php
// controller/controlador_reseñas.php

// Establece el encabezado para indicar que la respuesta será JSON
header('Content-Type: application/json'); 

// Inicia la sesión si no está activa para acceder a $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Activa el reporte de errores para depuración (desactivar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluye el modelo de reseñas
require_once '../model/modelo_reseñas.php';
require_once '../model/modelo_factura.php'; 

// Crea instancias de los modelos
$reseña_model = new Reseña();
$factura_model = new Factura(); 

// Verifica el método de la solicitud HTTP
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtiene la acción de la solicitud POST
    $accion = $_POST['accion'] ?? '';

    switch ($accion) {
        case 'guardar_reseña':
            guardarReseña($reseña_model, $factura_model);
            break;
        default:
            // Acción POST no válida
            echo json_encode(['status' => 'error', 'message' => 'Acción POST no válida.']);
            break;
    }
    exit; // Termina la ejecución del script después de manejar la solicitud
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') { // Nuevo bloque para manejar solicitudes GET
    $accion = $_GET['accion'] ?? '';

    switch ($accion) {
        case 'listar_reseñas_producto':
            listarReseñasProducto($reseña_model);
            break;
        default:
            echo json_encode(['status' => 'error', 'message' => 'Acción GET no válida para reseñas.']);
            break;
    }
    exit;
} else {
    // Método HTTP no permitido
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
    exit;
}

function guardarReseña($reseña_model, $factura_model) {
    // Obtiene los datos de la solicitud POST
    $idUsuario = $_POST['id_usuario'] ?? null;
    $idProducto = $_POST['id_producto'] ?? null;
    $idFactura = $_POST['id_factura'] ?? null; // ID de la factura (para referencia o validación)
    $calificacion = $_POST['calificacion'] ?? null;
    $comentario = $_POST['comentario'] ?? null;

    // Validaciones básicas de los datos
    if (is_null($idUsuario) || is_null($idProducto) || is_null($calificacion) || $calificacion < 1 || $calificacion > 5) {
        echo json_encode(['status' => 'error', 'message' => 'Datos de reseña incompletos o inválidos.']);
        return;
    }

    // Llama al método del modelo para guardar la reseña
    $resultado = $reseña_model->guardarReseña($idProducto, $idUsuario, $calificacion, $comentario);

    if ($resultado) {
        echo json_encode(['status' => 'success', 'message' => 'Reseña guardada con éxito.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al guardar la reseña.']);
    }
}

function listarReseñasProducto($reseña_model) {
    $idProducto = $_GET['id_producto'] ?? null;

    if (is_null($idProducto)) {
        echo json_encode(['status' => 'error', 'message' => 'ID de producto no proporcionado para listar reseñas.']);
        return;
    }

    try {
        $reseñas = $reseña_model->obtenerReseñasPorProducto($idProducto);
        echo json_encode(['status' => 'success', 'data' => $reseñas]);
    } catch (Exception $e) {
        error_log("Error en listarReseñasProducto (controlador): " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error al obtener las reseñas: ' . $e->getMessage()]);
    }
}
