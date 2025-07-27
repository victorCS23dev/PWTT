<?php session_start(); ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Ver Marcas</h1>
</div>

<div class="row">
    <form id="view_marcas-form" class="row g-3 align-items-end">
        <div class="row mb-4">
            <div class="col-md-2">
                <button type="button" id="bt_actualizar_marcas" class="btn btn-secondary btn-lg px-4 shadow mt-3">Actualizar</button>
            </div>
        </div>
    </form>
</div>

<div class="row">
    <div class="table-responsive mt-6">
        <table class="table table-striped table-hover table-bordered align-middle" id="tabla_marcas">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="marcas-table-body">
                <!-- Los datos de las marcas se cargarán aquí -->
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="editMarcaModal" tabindex="-1" aria-labelledby="editMarcaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMarcaModalLabel">Editar Marca</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="formulario_editar_marca">
                    <form id="editarMarcaForm" data-url="../controller/controlador_marcas.php">
                        <input type="hidden" name="accion" value="editar_marca">
                        <input type="hidden" id="id_marca_editar" name="id_marca">
                        <!-- Campo oculto para el ID del usuario que modifica -->
                        <!-- Asegúrate de que $_SESSION['id_usuario'] contenga el ID del usuario logueado -->
                        <input type="hidden" id="modificado_por_marca" name="modificado_por" value="<?php echo $_SESSION['id_usuario'] ?? ''; ?>"> 

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nombre_marca_editar" class="form-label">Nombre de Marca:</label>
                                <input type="text" class="form-control" id="nombre_marca_editar" name="nombre_marca" required>
                            </div>
                            <div class="col-md-6">
                                <label for="estado_marca_editar" class="form-label">Estado:</label>
                                <select id="estado_marca_editar" name="estado_marca" class="form-control" required>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-3">
                        <h5>Información de Auditoría</h5>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="creado_por_marca_display" class="form-label">Creado Por:</label>
                                <p class="form-control-static p-2 border rounded bg-light" id="creado_por_marca_display"></p>
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_creacion_marca_display" class="form-label">Fecha Creación:</label>
                                <p class="form-control-static p-2 border rounded bg-light" id="fecha_creacion_marca_display"></p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="modificado_por_marca_display" class="form-label">Última Modificación Por:</label>
                                <p class="form-control-static p-2 border rounded bg-light" id="modificado_por_marca_display"></p>
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_modificacion_marca_display" class="form-label">Fecha Última Modificación:</label>
                                <p class="form-control-static p-2 border rounded bg-light" id="fecha_modificacion_marca_display"></p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">Guardar Cambios</button>
                            </div>
                            <div class="col-md-4">
                                <button type="button" id="eliminarMarcaModal" class="btn btn-danger w-100">Eliminar Marca</button>
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

<script src="../js/editar_marca_modal.js"></script>
