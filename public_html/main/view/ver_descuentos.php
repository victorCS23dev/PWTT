<?php session_start(); ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Ver Códigos de Descuento</h1>
</div>

<div class="row">
    <form id="view_descuentos-form" class="row g-3 align-items-end">
        <div class="row mb-4">
            <div class="col-md-2">
                <button type="button" id="bt_actualizar_descuentos" class="btn btn-secondary btn-lg px-4 shadow mt-3">Actualizar</button>
            </div>
        </div>
    </form>
</div>

<div class="row">
    <div class="table-responsive mt-6">
        <table class="table table-striped table-hover table-bordered align-middle" id="tabla_descuentos">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Código</th>
                    <th>Valor (%)</th>
                    <th>Categoría</th>
                    <th>Marca</th>
                    <th>Descripción</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="descuentos-table-body">
                <!-- Los datos de descuentos se cargarán aquí -->
            </tbody>
        </table>
    </div>
</div>

<!-- El modal de edición de descuento -->
<div class="modal fade" id="editDescuentoModal" tabindex="-1" aria-labelledby="editDescuentoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDescuentoModalLabel">Editar Código de Descuento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="formulario_editar_descuento">
                    <form id="editarDescuentoForm" data-url="../controller/controlador_descuentos.php">
                        <input type="hidden" name="accion" value="actualizar_descuento">
                        <input type="hidden" id="id_codigo_editar" name="id_codigo">
                        <!-- Campo oculto para el ID del usuario que modifica -->
                        <input type="hidden" id="modificado_por_descuento" name="modificado_por" value="<?php echo $_SESSION['id_usuario'] ?? ''; ?>">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="codigo_editar" class="form-label">Código de Descuento:</label>
                                <input type="text" class="form-control" id="codigo_editar" name="codigo" required maxlength="50">
                            </div>
                            <div class="col-md-6">
                                <label for="valor_descuento_editar" class="form-label">Valor del Descuento (%):</label>
                                <input type="number" step="0.01" min="0" max="100" class="form-control" id="valor_descuento_editar" name="valor_descuento" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="descripcion_editar" class="form-label">Descripción (Opcional):</label>
                                <textarea class="form-control" id="descripcion_editar" name="descripcion" rows="2"></textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="aplica_a_categoria_editar" class="form-label">Aplica a Categoría (Todas si no se selecciona):</label>
                                <select class="form-select" id="aplica_a_categoria_editar" name="aplica_a_categoria">
                                    <option value="">Todas las Categorías</option>
                                    <!-- Opciones cargadas por JS -->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="aplica_a_marca_editar" class="form-label">Aplica a Marca (Todas si no se selecciona):</label>
                                <select class="form-select" id="aplica_a_marca_editar" name="aplica_a_marca">
                                    <option value="">Todas las Marcas</option>
                                    <!-- Opciones cargadas por JS -->
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="fecha_inicio_editar" class="form-label">Fecha y Hora de Inicio:</label>
                                <input type="datetime-local" class="form-control" id="fecha_inicio_editar" name="fecha_inicio" required>
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_fin_editar" class="form-label">Fecha y Hora de Fin (Opcional):</label>
                                <input type="datetime-local" class="form-control" id="fecha_fin_editar" name="fecha_fin">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="estado_editar" class="form-label">Estado:</label>
                                <select class="form-select" id="estado_editar" name="estado" required>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                        
                        <hr class="my-3">
                        <h5>Información de Auditoría</h5>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="creado_por_descuento_display" class="form-label">Creado Por:</label>
                                <p class="form-control-static p-2 border rounded bg-light" id="creado_por_descuento_display"></p>
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_creacion_descuento_display" class="form-label">Fecha Creación:</label>
                                <p class="form-control-static p-2 border rounded bg-light" id="fecha_creacion_descuento_display"></p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="modificado_por_descuento_display" class="form-label">Última Modificación Por:</label>
                                <p class="form-control-static p-2 border rounded bg-light" id="modificado_por_descuento_display"></p>
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_modificacion_descuento_display" class="form-label">Fecha Última Modificación:</label>
                                <p class="form-control-static p-2 border rounded bg-light" id="fecha_modificacion_descuento_display"></p>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">Guardar Cambios</button>
                            </div>
                            <div class="col-md-3">
                                <button type="button" id="eliminarDescuentoModal" class="btn btn-danger w-100">Eliminar Descuento</button>
                            </div>
                            <div class="col-md-3">
                                <button type="button" id="notificarDescuentoBtn" class="btn btn-info w-100" disabled>Notificar</button>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../../js/editar_descuento_modal.js"></script>
