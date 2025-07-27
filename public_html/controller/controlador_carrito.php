<?php
// controller/controlador_carrito.php

// Asegúrate de iniciar la sesión si no se ha iniciado ya.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluye el modelo de producto para interactuar con la base de datos
require_once '../model/modelo_producto.php'; 

// Instancia del modelo de producto para obtener stock
$producto_model = new Producto(); 

header('Content-Type: application/json'); // Indicar que la respuesta será JSON

// Función para calcular el total del carrito
function calculateCartTotal() {
    $total = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }
    }
    return $total;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        // Agrega un producto al carrito
        $id = $_POST['id'] ?? null;
        $nombre = $_POST['nombre'] ?? '';
        $marca_nombre = $_POST['marca'] ?? ''; // Nombre de la marca
        $precio = $_POST['precio'] ?? 0.0;
        $cantidad = $_POST['cantidad'] ?? 1;
        $imagen = $_POST['imagen'] ?? '';
        // También necesitamos el stock del producto desde el frontend para la validación inicial
        // (aunque el backend siempre lo verificará con la DB)
        $product_stock_from_frontend = $_POST['stock'] ?? null; 


        if ($id && $nombre && $precio) {
            // Obtener el stock actual del producto desde la base de datos
            $current_stock_data = $producto_model->obtenerStockProducto($id);
            $current_stock = $current_stock_data ? $current_stock_data['stock'] : 0;

            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }

            $current_cart_quantity = $_SESSION['cart'][$id]['cantidad'] ?? 0;
            $requested_total_quantity = $current_cart_quantity + $cantidad;

            if ($requested_total_quantity > $current_stock) {
                $adjusted_quantity_to_add = $current_stock - $current_cart_quantity;
                
                if ($adjusted_quantity_to_add <= 0) {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Producto agotado o no hay suficiente stock para añadir más. Stock disponible: ' . $current_stock,
                        'cartItems' => array_values($_SESSION['cart']), 
                        'total' => calculateCartTotal()
                    ]);
                    exit;
                }
                
                $_SESSION['cart'][$id]['cantidad'] = $current_stock; 
                echo json_encode([
                    'success' => false, 
                    'message' => 'Solo se añadieron ' . $adjusted_quantity_to_add . ' unidades. Stock máximo alcanzado para este producto. Stock disponible: ' . $current_stock, 
                    'cartItems' => array_values($_SESSION['cart']), 
                    'total' => calculateCartTotal()
                ]);
                exit;
            }

            if (isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id]['cantidad'] += $cantidad;
            } else {
                $_SESSION['cart'][$id] = [
                    'id' => $id,
                    'nombre' => $nombre,
                    'marca' => $marca_nombre,
                    'precio' => (float)$precio,
                    'cantidad' => (int)$cantidad,
                    'imagen' => $imagen,
                    'stock' => $current_stock 
                ];
            }
            echo json_encode([
                'success' => true, 
                'message' => 'Producto añadido al carrito.', 
                'cartItems' => array_values($_SESSION['cart']), 
                'total' => calculateCartTotal()
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Datos de producto incompletos.']);
        }
        break;

    case 'remove':
        $id = $_POST['id'] ?? null;
        if ($id && isset($_SESSION['cart'][$id])) {
            unset($_SESSION['cart'][$id]);
            echo json_encode([
                'success' => true, 
                'message' => 'Producto eliminado del carrito.', 
                'cartItems' => array_values($_SESSION['cart']), 
                'total' => calculateCartTotal()
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Producto no encontrado en el carrito.']);
        }
        break;

    case 'update':
        $id = $_POST['id'] ?? null;
        $cantidad = $_POST['cantidad'] ?? null;

        if ($id && $cantidad !== null && isset($_SESSION['cart'][$id])) {
            $new_quantity = (int)$cantidad;

            // Obtener el stock actual del producto desde la base de datos
            $current_stock_data = $producto_model->obtenerStockProducto($id);
            $current_stock = $current_stock_data ? $current_stock_data['stock'] : 0;

            if ($new_quantity < 1) {
                $new_quantity = 1; 
            }

            if ($new_quantity > $current_stock) {
                $new_quantity = $current_stock; 
                if ($new_quantity === 0) {
                    unset($_SESSION['cart'][$id]); 
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Producto agotado y eliminado del carrito.', 
                        'cartItems' => array_values($_SESSION['cart']), 
                        'total' => calculateCartTotal()
                    ]);
                    exit;
                }
                $_SESSION['cart'][$id]['cantidad'] = $new_quantity;
                echo json_encode([
                    'success' => false, 
                    'message' => 'Cantidad ajustada al stock disponible: ' . $new_quantity, 
                    'cartItems' => array_values($_SESSION['cart']), 
                    'total' => calculateCartTotal()
                ]);
                exit;
            }

            $_SESSION['cart'][$id]['cantidad'] = $new_quantity;
            echo json_encode([
                'success' => true, 
                'message' => 'Cantidad actualizada.', 
                'cartItems' => array_values($_SESSION['cart']), 
                'total' => calculateCartTotal()
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Datos de actualización incompletos o producto no encontrado.']);
        }
        break;

    case 'get_cart':
        echo json_encode([
            'success' => true, 
            'cartItems' => array_values($_SESSION['cart'] ?? []), 
            'total' => calculateCartTotal()
        ]);
        break;

    case 'validate_checkout_stock': 
        $validation_errors = [];
        
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            echo json_encode(['success' => false, 'message' => 'Tu carrito está vacío.', 'cartItems' => [], 'total' => 0.00]);
            exit;
        }

        $temp_cart = $_SESSION['cart']; 
        foreach ($temp_cart as $id => &$item) { 
            $current_stock_data = $producto_model->obtenerStockProducto($id);
            $current_stock = $current_stock_data ? $current_stock_data['stock'] : 0;

            if ($item['cantidad'] > $current_stock) {
                $validation_errors[] = "La cantidad de '" . htmlspecialchars($item['nombre']) . "' (" . $item['cantidad'] . ") excede el stock disponible (" . $current_stock . ").";
                $item['cantidad'] = $current_stock; 
                
                if ($current_stock === 0) {
                    unset($_SESSION['cart'][$id]); 
                    $validation_errors[] = "El producto '" . htmlspecialchars($item['nombre']) . "' está agotado y ha sido eliminado de tu carrito.";
                } else {
                    $_SESSION['cart'][$id]['cantidad'] = $current_stock; 
                }
            }
        }
        $_SESSION['cart'] = array_values($_SESSION['cart']);

        if (!empty($validation_errors)) {
            echo json_encode([
                'success' => false, 
                'message' => implode('<br>', $validation_errors), 
                'cartItems' => array_values($_SESSION['cart']), 
                'total' => calculateCartTotal()
            ]);
        } else {
            echo json_encode([
                'success' => true, 
                'message' => 'Stock verificado y disponible.', 
                'cartItems' => array_values($_SESSION['cart']), 
                'total' => calculateCartTotal()
            ]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
        break;
}
