<?php session_start(); ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Ver Usuarios</h1>
</div>

<div class="row">
    <form id="view_visit-form" class="row g-3 align-items-end">
        <div class="row mb-4">
            <div class="col-md-2">
                <button type="button" id="bt_actualizar_usuarios" class="btn btn-secondary btn-lg px-4 shadow mt-3">Actualizar</button>
            </div>
        </div>
    </form>
</div>

<div class="row">
    <!-- Tabla de Usuarios -->
    <div class="table-responsive mt-6">
        <table class="table table-striped table-hover table-bordered align-middle" id="tabla_usuarios">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Google ID</th>
                    <th>DNI</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <!-- Columnas de auditoría eliminadas de la tabla -->
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="users-table-body">
                <!-- Los datos de usuarios se cargarán aquí -->
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg"> <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Editar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="formulario_editar_usuario">
                    <form id="editarUsuarioForm" data-url="../controller/controlador_usuarios.php">
                        <input type="hidden" name="accion" value="editar_usuario">
                        <input type="hidden" id="id_usuario_editar" name="id_usuario">
                        <!-- Campo oculto para el ID del usuario que modifica, obtenido de la sesión -->
                        <input type="hidden" id="modificado_por_usuario" name="modificado_por" value="<?php echo $_SESSION['id_usuario'] ?? ''; ?>"> 

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="dni_usuario_editar" class="form-label">DNI:</label>
                                <input type="text" class="form-control" id="dni_usuario_editar" name="dni_usuario" readonly>
                            </div>
                            <div class="col-md-3">
                                <label for="nombres_usuario_editar" class="form-label">Nombres:</label>
                                <input type="text" class="form-control" id="nombres_usuario_editar" name="nombres_usuario" readonly>
                            </div>
                            <div class="col-md-3">
                                <label for="apellidos_usuario_editar" class="form-label">Apellidos:</label>
                                <input type="text" class="form-control" id="apellidos_usuario_editar" name="apellidos_usuario" readonly>
                            </div>
                            <div class="col-md-3">
                                <label for="correo_usuario_editar" class="form-label">Correo:</label>
                                <input type="email" class="form-control" id="correo_usuario_editar" name="correo_usuario" readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="telefono_usuario_editar" class="form-label">Teléfono:</label>
                                <input type="text" class="form-control" id="telefono_usuario_editar" name="telefono_usuario" readonly> </div>
                            <div class="col-md-6">
                                <label for="direccion_usuario_editar" class="form-label">Dirección:</label>
                                <input type="text" class="form-control" id="direccion_usuario_editar" name="direccion_usuario" readonly> </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="rol_usuario_editar" class="form-label">Rol:</label>
                                <select id="rol_usuario_editar" name="rol_usuario" class="form-control" required>
                                    <option value="administrador">Administrador</option>
                                    <option value="empleado">Empleado</option>
                                    <option value="cliente">Cliente</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="estado_usuario_editar" class="form-label">Estado:</label>
                                <select id="estado_usuario_editar" name="estado_usuario" class="form-control" required>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-3">
                        <h5>Información de Auditoría</h5>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="creado_por_usuario_display" class="form-label">Creado Por:</label>
                                <p class="form-control-static p-2 border rounded bg-light" id="creado_por_usuario_display"></p>
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_creacion_usuario_display" class="form-label">Fecha Creación:</label>
                                <p class="form-control-static p-2 border rounded bg-light" id="fecha_creacion_usuario_display"></p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="modificado_por_usuario_display" class="form-label">Última Modificación Por:</label>
                                <p class="form-control-static p-2 border rounded bg-light" id="modificado_por_usuario_display"></p>
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_modificacion_usuario_display" class="form-label">Fecha Última Modificación:</label>
                                <p class="form-control-static p-2 border rounded bg-light" id="fecha_modificacion_usuario_display"></p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">Guardar Cambios</button>
                            </div>
                            <div class="col-md-4">
                                <button type="button" id="eliminarUsuarioModal" class="btn btn-danger w-100">Eliminar Usuario</button>
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

<script src="../js/editar_usuario_modal.js"></script>
