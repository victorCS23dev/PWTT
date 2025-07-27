<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtener los datos del formulario POST o de la sesión para mostrarlos y pasarlos al JS
$metodo = $_POST['metodo_pago'] ?? '';
$montoOriginal = $_POST['monto_total'] ?? 0.00; // Este es el monto ya con descuento si se aplicó
$idCodigoDescuento = $_POST['id_codigo_descuento_aplicado'] ?? null;
$montoDescuento = $_POST['monto_descuento_aplicado'] ?? 0.00;

// Asegurarse de que los montos sean flotantes
$montoOriginal = (float)$montoOriginal;
$montoDescuento = (float)$montoDescuento;

// Calcular el subtotal antes del descuento (solo para mostrarlo, si es necesario)
// Ahora $_POST['subtotal_carrito_raw'] debería estar disponible
$subtotalCarrito = (float)($_POST['subtotal_carrito_raw'] ?? 0.00);


// Serializar productos a JSON para enviar vía AJAX
$productos = $_SESSION['cart'] ?? [];
$productos_json = json_encode(array_map(function ($item) {
    return [
        'idProducto' => $item['id'],
        'cantidad' => $item['cantidad'],
        'precio' => $item['precio']
    ];
}, array_values($productos)));

$idUsuario = $_SESSION['id_usuario'] ?? null;

// Validar que idUsuario no sea nulo antes de mostrar el formulario de pago
if (is_null($idUsuario)) {
    echo '<div class="container mt-5 text-center">';
    echo '<h2>⚠️ Error en el Pago</h2>';
    echo '<p class="text-danger">No se pudo procesar la compra. ID de usuario no encontrado. Por favor, inicia sesión.</p>';
    echo '<a href="index.php?page=view/login.php" class="btn btn-outline-danger mt-3">← Iniciar Sesión</a>';
    echo '</div>';
    exit; // Detiene la ejecución si no hay ID de usuario
}
?>

<div class="container mt-5 text-center">
    <h2>Resumen de Tu Compra</h2>
    <p>Método de pago seleccionado: <strong><?php echo htmlspecialchars($metodo); ?></strong></p>
    
    <?php if ($montoDescuento > 0): ?>
        <p>Subtotal del Carrito: <strong>S/<?php echo number_format($subtotalCarrito, 2); ?></strong></p>
        <p class="text-success">Descuento Aplicado: <strong>-S/<?php echo number_format($montoDescuento, 2); ?></strong></p>
        <p class="fw-bold fs-4">Total a pagar: <strong class="text-primary">S/<?php echo number_format($montoOriginal, 2); ?></strong></p>
    <?php else: ?>
        <p class="fw-bold fs-4">Total a pagar: <strong class="text-primary">S/<?php echo number_format($montoOriginal, 2); ?></strong></p>
    <?php endif; ?>

    <!-- Puedes agregar aquí un listado de productos para mayor detalle si lo deseas -->
    <?php if (!empty($productos)): ?>
        <h4 class="mt-4">Productos en el carrito:</h4>
        <ul class="list-group mb-4">
            <?php foreach ($productos as $item): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?php echo htmlspecialchars($item['nombre'] ?? 'Producto'); ?> (x<?php echo htmlspecialchars($item['cantidad']); ?>)
                    <span class="badge bg-primary rounded-pill">S/<?php echo number_format((float)$item['precio'], 2); ?> c/u</span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <div class="alert alert-warning mt-3">
            Tu carrito está vacío. No hay productos para confirmar la compra.
        </div>
        <a href="index.php?page=view/productos.php" class="btn btn-outline-primary mt-3">← Volver a la tienda</a>
    <?php endif; ?>

    <?php if (!empty($productos)): // Solo mostrar el botón si hay productos en el carrito ?>
        <!-- Formulario oculto para enviar los datos vía AJAX -->
        <form id="confirmarPagoForm" class="ajaxForm" data-url="../../controller/controlador_facturas.php" style="display: none;">
            <input type="hidden" name="accion" value="registrar_factura">
            <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($idUsuario); ?>">
            <input type="hidden" name="metodo_pago" value="<?php echo htmlspecialchars($metodo); ?>">
            <input type="hidden" name="monto_total" value="<?php echo htmlspecialchars(number_format($montoOriginal, 2, '.', '')); ?>">
            <input type="hidden" name="productos_json" value="<?php echo htmlspecialchars($productos_json); ?>">
            <!-- Campo para el correo del usuario, para que el controlador lo use en el PHPMailer -->
            <input type="hidden" name="correo_destinatario" value="<?php echo htmlspecialchars($_SESSION['correo'] ?? 'correo_destino@ejemplo.com'); ?>">
            
            <!-- Nuevos campos para el descuento -->
            <input type="hidden" name="id_codigo_descuento" value="<?php echo htmlspecialchars($idCodigoDescuento ?? ''); ?>">
            <input type="hidden" name="monto_descuento" value="<?php echo htmlspecialchars(number_format($montoDescuento, 2, '.', '')); ?>">
        </form>

        <div id="payment-status-message" class="mt-4">
            <!-- Aquí se mostrará el mensaje de éxito o error del pago -->
            <p>Haz clic en "Confirmar Pago" para finalizar tu compra.</p>
        </div>

        <button type="button" id="btnConfirmarPago" class="btn btn-danger btn-lg mt-3">Confirmar Pago</button>
        <a href="index.php?page=view/productos.php" class="btn btn-outline-secondary mt-3 ms-2">← Volver al Carrito</a>

    <?php endif; ?>
</div>

<!-- Incluir jQuery si aún no está cargado -->
<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script> -->
<!-- Incluir tu script AJAX -->
<script src="../js/resumen_pago.js"></script>
