<?php
// view/carrito.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$cart_items_initial = isset($_SESSION['cart']) ? array_values($_SESSION['cart']) : [];
$total_carrito_initial = 0.00;
foreach ($cart_items_initial as $item) {
    $total_carrito_initial += $item['precio'] * $item['cantidad'];
}
?>

<div class="container mt-5">
    <h1 class="text-center mb-4">Mi Carrito de Compras</h1>

    <!-- Mensaje si el carrito está vacío -->
    <div class="alert alert-info text-center <?php echo empty($cart_items_initial) ? '' : 'd-none'; ?>" role="alert">
        <strong>¡Tu carrito está vacío!</strong> Añade algunos productos para empezar a comprar.
    </div>

    <div class="text-center mb-4 <?php echo empty($cart_items_initial) ? '' : 'd-none'; ?>">
        <a href="index.php?page=view/productos.php" class="btn btn-primary">
            🛒 Ir a la Tienda
        </a>
    </div>

    <!-- Carrito con productos -->
    <div class="row <?php echo empty($cart_items_initial) ? 'd-none' : ''; ?>">
        <!-- Sección de productos -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Productos</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle text-center mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th>Precio</th>
                                    <th>Cantidad</th>
                                    <th>Subtotal</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="cart-items-body">
                                <?php foreach ($cart_items_initial as $item): ?>
                                <tr>
                                    <td class="text-start">
                                        <div class="d-flex align-items-center gap-3">
                                            <div style="flex-shrink: 0;">
                                                <img src="<?php echo htmlspecialchars($item['imagen']); ?>"
                                                    alt="Imagen de <?php echo htmlspecialchars($item['nombre']); ?>"
                                                    style="width: 40px; height: 40px; object-fit: cover; border: 1px solid #dee2e6; border-radius: 6px;">
                                            </div>
                                            <div>
                                                <div><strong><?php echo htmlspecialchars($item['nombre']); ?></strong></div>
                                                <div class="text-muted small">Marca: <?php echo htmlspecialchars($item['marca']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>$<?php echo number_format($item['precio'], 2); ?></td>
                                    <td>
                                        <!-- Eliminado data-product-stock -->
                                        <input type="number" min="1" class="form-control quantity-input" style="width: 70px;" value="<?php echo htmlspecialchars($item['cantidad']); ?>" data-product-id="<?php echo htmlspecialchars($item['id']); ?>">
                                    </td>
                                    <td id="subtotal-<?php echo htmlspecialchars($item['id']); ?>">
                                        $<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-danger remove-item-btn" data-product-id="<?php echo htmlspecialchars($item['id']); ?>">Eliminar</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center p-3 border-top">
                        <a href="index.php?page=view/productos.php" class="btn btn-outline-primary">
                            ← Seguir Comprando
                        </a>
                        <button class="btn btn-secondary" id="update-cart-btn">Actualizar Carrito</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen del pedido -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Resumen del Pedido</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span id="cart-subtotal">S/<?php echo number_format($total_carrito_initial, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Envío</span>
                        <span>Calculado al finalizar</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total</strong>
                        <strong class="text-success" id="cart-total">S/<?php echo number_format($total_carrito_initial, 2); ?></strong>
                    </div>
                    <p class="text-muted small text-center">
                        Los gastos de envío e impuestos se calcularán al finalizar la compra.
                    </p>
                    <form action="index.php" method="GET">
                        <input type="hidden" name="page" value="view/compra.php">
                        <button type="submit" class="btn btn-success w-100 mt-3" id="checkout-btn">Proceder a la Compra</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Variables JS -->
<script>
    var CONTROLADOR_PRODUCTOS_URL = '../controller/controlador_productos.php';
    var CONTROLADOR_CARRITO_URL = '../controller/controlador_carrito.php';
    var CONTROLADOR_USUARIOS_URL = '../controller/controlador_usuarios.php';
    var CONTROLADOR_CATEGORIAS_URL = '../controller/controlador_categorias.php';
</script>

<!-- jQuery y DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

<!-- Scripts del carrito -->
<script src="../../js/carrito.js"></script>
<script src="../../js/view.js"></script>
