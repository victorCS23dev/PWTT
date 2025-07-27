<?php
// view/ver_pedidos.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id_usuario_logueado = $_SESSION['id_usuario'] ?? null; // ID del usuario que está viendo la página
?>

<div class="container mt-5">
    <h2 class="mb-4">📋 Gestión de Pedidos (Administrador)</h2>

    <div class="row mb-4">
        <div class="col-md-2">
            <button type="button" id="bt_actualizar_pedidos" class="btn btn-secondary btn-lg px-4 shadow mt-3">Actualizar Pedidos</button>
        </div>
    </div>

    <div class="table-responsive">
        <table id="tablaPedidosAdmin" class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th class="text-center">ID Factura</th>
                    <th class="text-center">Fecha</th>
                    <th class="text-center">Cliente</th>
                    <th class="text-center">Correo Cliente</th>
                    <th class="text-center">Método de Pago</th>
                    <th class="text-center">Total (S/)</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Acciones</th> 
                </tr>
            </thead>
            <tbody>
                <!-- Los datos se cargarán aquí vía AJAX -->
                <tr>
                    <td colspan="8" class="text-center">Cargando pedidos...</td> 
                </tr>
            </tbody>
        </table>
    </div>
    <div id="pedidos-status-message" class="mt-3"></div>
</div>

<!-- Modal para ver y editar el detalle de la factura -->
<div class="modal fade" id="detalleFacturaAdminModal" tabindex="-1" aria-labelledby="detalleFacturaAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detalleFacturaAdminModalLabel">Detalle de Factura #<span id="modalFacturaIdAdmin"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Cliente:</strong> <span id="modalClienteNombreAdmin"></span> (<span id="modalClienteDNIAdmin"></span>)</p>
                <p><strong>Correo Cliente:</strong> <span id="modalClienteCorreoAdmin"></span></p>
                <p><strong>Fecha:</strong> <span id="modalFechaAdmin"></span></p>
                <p><strong>Método de Pago:</strong> <span id="modalMetodoPagoAdmin"></span></p>
                <p id="modalDescuentoRowAdmin" style="display: none;"><strong>Descuento Aplicado:</strong> <span id="modalDescuentoAdmin"></span></p>
                <p><strong>Monto Total:</strong> S/<span id="modalMontoTotalAdmin"></span></p>
                
                <hr>
                <h6>Productos:</h6>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">Producto</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-center">Precio Unitario</th>
                            <th class="text-center">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="modalProductosDetalleAdmin">
                        <!-- Detalles de productos se cargarán aquí -->
                    </tbody>
                </table>
                <hr>

                <!-- Sección para editar el estado -->
                <h6>Editar Estado de Factura:</h6>
                <form id="editarEstadoFacturaForm">
                    <input type="hidden" id="editFacturaId" name="id_factura">
                    <input type="hidden" id="modificadoPorAdmin" name="id_usuario_modificador" value="<?php echo htmlspecialchars($id_usuario_logueado); ?>">
                    <div class="mb-3">
                        <label for="estadoFacturaSelect" class="form-label">Nuevo Estado:</label>
                        <select class="form-select" id="estadoFacturaSelect" name="nuevo_estado">
                            <option value="0">Cancelado</option>
                            <option value="1">Pendiente</option>
                            <option value="2">En preparación</option>
                            <option value="3">En camino</option>
                            <option value="4">Entregado</option>
                            <option value="5">Devuelto</option>
                            <option value="6">Solicitud de Cancelación</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar Estado</button>
                    <div id="estadoUpdateMessage" class="mt-2"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Variables JS globales para los controladores -->
<script>
    // Asegúrate de que estas URLs sean correctas para tu estructura de proyecto
    window.CONTROLADOR_FACTURAS_URL = '../controller/controlador_facturas.php';
    window.ID_USUARIO_LOGUEADO = <?php echo json_encode($id_usuario_logueado); ?>;
</script>

<script src="../js/editar_factura_modal.js"></script>
