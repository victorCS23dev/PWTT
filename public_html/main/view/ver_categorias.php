<?php session_start(); ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Ver Categorías</h1>
</div>

<div class="row">
    <form id="view_categorias-form" class="row g-3 align-items-end">
        <div class="row mb-4">
            <div class="col-md-2">
                <button type="button" id="bt_actualizar_categorias" class="btn btn-secondary btn-lg px-4 shadow mt-3">Actualizar</button>
            </div>
        </div>
    </form>
</div>

<div class="row">
    <div class="table-responsive mt-6">
        <table class="table table-striped table-hover table-bordered align-middle" id="tabla_categorias">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="categorias-table-body">
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="editCategoriaModal" tabindex="-1" aria-labelledby="editCategoriaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCategoriaModalLabel">Editar Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="formulario_editar_categoria">
                    <form id="editarCategoriaForm" data-url="../controller/controlador_categorias.php">
                        <!-- CAMBIO CLAVE AQUÍ: Se eliminó name="accion" de este input -->
                        <input type="hidden" id="id_categoria_editar" name="id_categoria">
                        <input type="hidden" id="modificado_por_categoria" name="modificado_por" value="<?php echo $_SESSION['id_usuario'] ?? ''; ?>"> 

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nombre_categoria_editar" class="form-label">Nombre de Categoría:</label>
                                <input type="text" class="form-control" id="nombre_categoria_editar" name="nombre_categoria" required>
                            </div>
                            <div class="col-md-6">
                                <label for="estado_categoria_editar" class="form-label">Estado:</label>
                                <select id="estado_categoria_editar" name="estado_categoria" class="form-control" required>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>

                        <!-- Nuevo campo para asociar marcas -->
                        <div class="mb-3">
                            <label for="marcas_asociadas_editar" class="form-label">Marcas Asociadas:</label>
                            <select id="marcas_asociadas_editar" name="marcas_asociadas[]" class="form-control" multiple>
                                <!-- Las opciones de marcas se cargarán aquí dinámicamente -->
                            </select>
                            <small class="form-text text-muted">Usa Ctrl/Cmd + click para seleccionar múltiples marcas.</small>
                        </div>
                        <!-- Fin del nuevo campo -->

                        <hr class="my-3">
                        <h5>Información de Auditoría</h5>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="creado_por_categoria_display" class="form-label">Creado Por:</label>
                                <p class="form-control-static p-2 border rounded bg-light" id="creado_por_categoria_display"></p>
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_creacion_categoria_display" class="form-label">Fecha Creación:</label>
                                <p class="form-control-static p-2 border rounded bg-light" id="fecha_creacion_categoria_display"></p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="modificado_por_categoria_display" class="form-label">Última Modificación Por:</label>
                                <p class="form-control-static p-2 border rounded bg-light" id="modificado_por_categoria_display"></p>
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_modificacion_categoria_display" class="form-label">Fecha Última Modificación:</label>
                                <p class="form-control-static p-2 border rounded bg-light" id="fecha_modificacion_categoria_display"></p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">Guardar Cambios</button>
                            </div>
                            <div class="col-md-4">
                                <button type="button" id="eliminarCategoriaModal" class="btn btn-danger w-100">Eliminar Categoría</button>
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

<script src="../js/editar_categoria_modal.js"></script>
