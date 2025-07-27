<?php session_start(); ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Ver Productos</h1>
</div>

<div class="row">
    <form id="view_productos-form" class="row g-3 align-items-end">
        <div class="row mb-4">
            <div class="col-md-2">
                <button type="button" id="bt_actualizar_productos" class="btn btn-secondary btn-lg px-4 shadow mt-3">Actualizar</button>
            </div>
        </div>
    </form>
</div>

<div class="row">
    <div class="table-responsive mt-6">
        <table class="table table-striped table-hover table-bordered align-middle" id="tabla_productos">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Marca</th> <!-- Columna para la marca -->
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Estado</th>
                    <th>Categoría</th>
                    <th>Imagen</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="productos-table-body">
                <!-- Los datos de productos se cargarán aquí -->
            </tbody>
        </table>
    </div>
</div>

<!-- El modal de edición de producto -->
<div class="modal fade" id="editProductoModal" tabindex="-1" aria-labelledby="editProductoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductoModalLabel">Editar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="formulario_editar_producto">
                    <form id="editarProductoForm" data-url="../controller/controlador_productos.php" enctype="multipart/form-data">
                        <input type="hidden" name="accion" value="editar_producto">
                        <input type="hidden" id="id_producto_editar" name="id_producto">
                        <input type="hidden" id="imagen_url_actual_editar" name="imagen_url_actual"> 
                        <!-- Campo oculto para el ID del usuario que modifica -->
                        <input type="hidden" id="modificado_por_producto" name="modificado_por" value="<?php echo $_SESSION['id_usuario'] ?? ''; ?>">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nombre_producto_editar" class="form-label">Nombre del Producto:</label>
                                <input type="text" class="form-control" id="nombre_producto_editar" name="nombre_producto" required>
                            </div>
                            <div class="col-md-6">
                                <label for="idMarcas_producto_editar" class="form-label">Marca:</label>
                                <!-- CAMBIO CLAVE AQUÍ: De input a select para la marca -->
                                <select id="idMarcas_producto_editar" name="idMarcas_producto" class="form-control" required>
                                    <option value="">Seleccionar Marca</option>
                                    <!-- Las marcas se cargarán aquí dinámicamente por JavaScript -->
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="precio_producto_editar" class="form-label">Precio:</label>
                                <input type="number" step="0.01" class="form-control" id="precio_producto_editar" name="precio_producto" required>
                            </div>
                            <div class="col-md-6">
                                <label for="stock_producto_editar" class="form-label">Stock:</label>
                                <input type="number" class="form-control" id="stock_producto_editar" name="stock_producto" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="estado_producto_editar" class="form-label">Estado:</label>
                                <select id="estado_producto_editar" name="estado_producto" class="form-control" required>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="idCategorias_producto_editar" class="form-label">Categoría:</label>
                                <select id="idCategorias_producto_editar" name="id_categoria_producto" class="form-control" required>
                                    <!-- Las categorías se cargarán aquí dinámicamente -->
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion_producto_editar" class="form-label">Descripción:</label>
                            <textarea class="form-control" id="descripcion_producto_editar" name="descripcion_producto" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="imagen_producto_editar" class="form-label">Imagen:</label>
                            <input type="file" class="form-control" id="imagen_producto_editar" name="imagen_producto" accept="image/*">
                            <small class="form-text text-muted">Deja en blanco para no cambiar la imagen actual.</small>
                            <div id="current_imagen_display" class="mt-2">
                                <!-- La imagen actual se mostrará aquí -->
                            </div>
                        </div>
                        
                        <hr class="my-3">
                        <h5>Información de Auditoría</h5>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="creado_por_producto_display" class="form-label">Creado Por:</label>
                                <p class="form-control-static p-2 border rounded bg-light" id="creado_por_producto_display"></p>
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_creacion_producto_display" class="form-label">Fecha Creación:</label>
                                <p class="form-control-static p-2 border rounded bg-light" id="fecha_creacion_producto_display"></p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="modificado_por_producto_display" class="form-label">Última Modificación Por:</label>
                                <p class="form-control-static p-2 border rounded bg-light" id="modificado_por_producto_display"></p>
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_modificacion_producto_display" class="form-label">Fecha Última Modificación:</label>
                                <p class="form-control-static p-2 border rounded bg-light" id="fecha_modificacion_producto_display"></p>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">Guardar Cambios</button>
                            </div>
                            <div class="col-md-4">
                                <button type="button" id="eliminarProductoModal" class="btn btn-danger w-100">Eliminar Producto</button>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../../js/editar_producto_modal.js"></script>
