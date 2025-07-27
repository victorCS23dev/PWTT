<?php
// controller/controlador_calificar.php

// Asegúrate de iniciar la sesión si no se ha iniciado ya.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Activa el reporte de errores para depuración (desactivar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluye los modelos necesarios. Las rutas son relativas a este controlador.
require_once __DIR__ . '/../model/modelo_producto.php';
require_once __DIR__ . '/../model/modelo_reseñas.php'; // Asegúrate de que el nombre del archivo sea 'modelo_reseñas.php' (plural)

// Instancia los modelos
$producto_model = new Producto();
$reseña_model = new Reseña();

// Obtener los IDs del producto y usuario de la URL (GET)
$id_producto = $_GET['id_producto'] ?? null;
$id_usuario = $_SESSION['id_usuario'] ?? null; // El ID del usuario logueado

// Validar que el usuario esté logueado
if (is_null($id_usuario)) {
    // Si el usuario no está logueado, redirigir o mostrar un mensaje de error
    // En este caso, simplemente incluimos la vista y le pasamos un mensaje de error.
    $error_message = 'Por favor, inicia sesión para calificar productos.';
    $access_denied = true;
    include __DIR__ . '/../main/view/calificar_producto.php'; // Incluye la vista para mostrar el mensaje
    exit;
}

// Validar que se haya proporcionado un ID de producto
if (is_null($id_producto)) {
    $error_message = 'No se ha proporcionado un ID de producto para calificar.';
    $product_not_specified = true;
    include __DIR__ . '/../main/view/calificar_producto.php'; // Incluye la vista para mostrar el mensaje
    exit;
}

// Obtener la información del producto
$producto_info = $producto_model->obtenerProductoPorId($id_producto);

if (!$producto_info) {
    $error_message = 'El producto que intentas calificar no existe.';
    $product_not_found = true;
    include __DIR__ . '/../main/view/calificar_producto.php'; // Incluye la vista para mostrar el mensaje
    exit;
}

// Obtener la reseña existente del usuario para este producto
$reseña_existente = $reseña_model->obtenerReseñaPorProductoYUsuario($id_producto, $id_usuario);

// Ahora, incluye la vista. La vista tendrá acceso a las variables:
// $id_producto, $id_factura (si viene de la URL), $id_usuario, $producto_info, $reseña_existente
// Y para los errores: $error_message, $access_denied, $product_not_specified, $product_not_found
include __DIR__ . '/../main/view/calificar_producto.php';
