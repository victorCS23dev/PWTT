<?php
// main/view/detalle_producto.php
$producto_id = $_GET['id'] ?? null;

// Obtener la URL base actual para compartir
$current_page_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
?>

<div class="container my-5">
    <div class="row">
        <div class="col-12 text-center mb-4">
            <h1>Detalles del Producto</h1>
            <a href="../../index.php" class="btn btn-outline-secondary mt-3">Volver al Catálogo</a>
        </div>
    </div>

    <div id="product-detail-container" class="card shadow-lg p-4">
        <!-- Contenido del producto se cargará aquí dinámicamente vía JavaScript -->
        <div class="d-flex justify-content-center my-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando detalles del producto...</span>
            </div>
        </div>
        <p class="text-center text-muted">Cargando...</p>
    </div>

    <!-- Sección para Reseñas del Producto -->
    <div class="mt-5">
        <h3 class="mb-4 text-center">Opiniones de Clientes</h3>
        <div id="product-reviews-container" class="card shadow-sm p-4">
            <!-- Las reseñas se cargarán aquí dinámicamente vía JavaScript -->
            <p class="text-center text-muted">Cargando reseñas...</p>
        </div>
    </div>
</div>

<!-- Script JavaScript para cargar los detalles del producto -->
<script src="../../js/carrito.js"></script>
<script src="../../js/detalle_producto.js"></script> 

<script>
    // Pasar el ID del producto a JavaScript para que el script pueda usarlo
    const PRODUCT_ID = <?php echo json_encode($producto_id); ?>;
    // Pasar la URL actual de la página para compartir
    const CURRENT_PAGE_URL = <?php echo json_encode($current_page_url); ?>;
</script>
