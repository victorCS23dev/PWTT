<?php
// view/calificar_producto.php
$id_usuario = (string) ($id_usuario ?? ''); 
$nombre_usuario = $_SESSION['nombre'] ?? 'Invitado';  
$id_producto = (string) ($id_producto ?? ''); 
$id_factura = (string) ($id_factura ?? ''); 

$error_message = (string) ($error_message ?? ''); 
$access_denied = $access_denied ?? false;
$product_not_specified = $product_not_specified ?? false;
$product_not_found = $product_not_found ?? false;

if ($access_denied || $product_not_specified || $product_not_found) {
    echo '<div class="container mt-5 text-center">';
    echo '<h2>' . htmlspecialchars($access_denied ? 'Acceso Denegado' : ($product_not_specified ? 'Producto no especificado' : 'Producto no encontrado')) . '</h2>';
    echo '<p class="text-danger">' . htmlspecialchars($error_message) . '</p>';
    if ($access_denied) {
        echo '<a href="index.php?page=view/login.php" class="btn btn-primary mt-3">Iniciar Sesión</a>';
    } else {
        echo '<a href="index.php?page=view/historial_compras.php" class="btn btn-secondary mt-3">Volver al Historial</a>';
    }
    echo '</div>';
    exit;
}
$producto_info = $producto_info ?? [];
$reseña_existente = $reseña_existente ?? null;
?>

<div class="container mt-5">
    <h2 class="mb-4 text-center">⭐ Calificar Producto</h2>
    <div class="card shadow-sm mx-auto" style="max-width: 600px;">
        <div class="card-body">
            <div class="d-flex align-items-center mb-4">
                <img src="../../img/productos/<?php echo htmlspecialchars($producto_info['imagen_url'] ?? 'https://placehold.co/80x80'); ?>" 
                     alt="Imagen de <?php echo htmlspecialchars($producto_info['producto_nombre'] ?? 'Producto'); ?>" 
                     class="img-fluid rounded me-3" style="width: 80px; height: 80px; object-fit: cover;">
                <div>
                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($producto_info['producto_nombre'] ?? 'Nombre del Producto'); ?></h5>
                    <p class="card-text text-muted">Marca: <?php echo htmlspecialchars($producto_info['marca'] ?? 'N/A'); ?></p>
                    <p class="card-text text-muted">Precio: S/<?php echo number_format($producto_info['precio'] ?? 0, 2); ?></p>
                </div>
            </div>

            <form id="reviewForm">
                <input type="hidden" id="idProducto" name="id_producto" value="<?php echo htmlspecialchars($id_producto); ?>">
                <input type="hidden" id="idUsuario" name="id_usuario" value="<?php echo htmlspecialchars($id_usuario); ?>">
                <input type="hidden" id="idFactura" name="id_factura" value="<?php echo htmlspecialchars($id_factura); ?>">
                
                <div class="mb-3 text-center">
                    <label for="calificacion" class="form-label fs-5">Tu Calificación:</label>
                    <div id="starRating" class="d-flex justify-content-center align-items-center mb-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <svg class="bi bi-star star-icon" data-value="<?php echo $i; ?>" width="40" height="40" fill="#ccc" viewBox="0 0 16 16" style="cursor: pointer; transition: fill 0.2s ease-in-out;">
                                <path d="M2.866 14.85c-.078.444.36.791.746.593l4.39-2.256 4.389 2.256c.386.198.824-.149.746-.592l-.83-4.73 3.523-3.356c.33-.314.16-.888-.282-.95l-4.898-.696-2.192-4.327a.513.513 0 0 0-.927 0L5.354 4.327.456 5.023c-.441.062-.612.636-.282.95l3.522 3.356-.83 4.73z"/>
                            </svg>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" id="calificacionInput" name="calificacion" value="<?php echo htmlspecialchars($reseña_existente['calificacion'] ?? ''); ?>">
                    <small class="text-muted" id="selectedRatingText">
                        <?php echo $reseña_existente ? 'Tu calificación actual: ' . htmlspecialchars($reseña_existente['calificacion'] ?? '') . ' estrellas' : 'Haz clic en una estrella para calificar'; ?>
                    </small>
                </div>

                <div class="mb-3">
                    <label for="comentario" class="form-label">Tu Comentario (Opcional):</label>
                    <textarea class="form-control" id="comentario" name="comentario" rows="4" placeholder="Escribe tu opinión sobre el producto..."><?php echo htmlspecialchars($reseña_existente['comentario'] ?? ''); ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <?php echo $reseña_existente ? 'Actualizar Reseña' : 'Enviar Reseña'; ?>
                </button>
                <div id="reviewStatusMessage" class="mt-3 text-center"></div>
            </form>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/calificar_producto.js"></script>
