<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Asegúrate de que id_usuario y nombre estén definidos en la sesión
$id_usuario = $_SESSION['id_usuario'] ?? null;
$nombre_usuario = $_SESSION['nombre'] ?? 'Invitado';

if (is_null($id_usuario)) {
    // Si el usuario no está logueado, redirigir o mostrar un mensaje de error
    echo '<div class="container mt-5 text-center">';
    echo '<h2>Acceso Denegado</h2>';
    echo '<p class="text-danger">Por favor, inicia sesión para ver tu historial de compras.</p>';
    echo '<a href="index.php?page=view/login.php" class="btn btn-primary mt-3">Iniciar Sesión</a>';
    echo '</div>';
    exit; // Detener la ejecución si no hay ID de usuario
}
?>

<div class="container mt-5">
    <h2 class="mb-4">🧾 Historial de Compras de <?php echo htmlspecialchars($nombre_usuario); ?></h2>

    <div class="table-responsive">
        <table id="tablaHistorial" class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th class="text-center">ID Factura</th>
                    <th class="text-center">Fecha</th>
                    <th class="text-center">Método de Pago</th>
                    <th class="text-center">Total (S/)</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Acciones</th> 
                </tr>
            </thead>
            <tbody>
                <!-- Los datos se cargarán aquí vía AJAX -->
                <tr>
                    <td colspan="6" class="text-center">Cargando historial de compras...</td> 
                </tr>
            </tbody>
        </table>
    </div>
    <div id="historial-status-message" class="mt-3"></div>
</div>

<!-- Modal para ver el detalle de la factura -->
<div class="modal fade" id="detalleFacturaModal" tabindex="-1" aria-labelledby="detalleFacturaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detalleFacturaModalLabel">Detalle de Factura #<span id="modalFacturaId"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Fecha:</strong> <span id="modalFecha"></span></p>
                <p><strong>Método de Pago:</strong> <span id="modalMetodoPago"></span></p>
                <!-- Nueva línea para el descuento -->
                <p id="modalDescuentoRow" style="display: none;"><strong>Descuento Aplicado:</strong> <span id="modalDescuento"></span></p>
                <p><strong>Monto Total:</strong> S/<span id="modalMontoTotal"></span></p>
                <p><strong>Estado:</strong> <span id="modalEstado"></span></p>
                <hr>
                <h6>Productos:</h6>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">Producto</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-center">Precio Unitario</th>
                            <th class="text-center">Subtotal</th>
                            <th class="text-center">Calificar</th> <!-- NUEVA COLUMNA -->
                        </tr>
                    </thead>
                    <tbody id="modalProductosDetalle">
                        <!-- Detalles de productos se cargarán aquí -->
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Incluir tu script AJAX -->
<script src="../js/historial_compras.js"></script>
<!-- Incluir Bootstrap JS (necesario para el modal) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
