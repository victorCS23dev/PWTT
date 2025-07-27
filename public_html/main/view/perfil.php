<?php
// main/view/perfil.php
$id_usuario = $_SESSION['id_usuario'] ?? ''; 
$nombre_usuario = $_SESSION['nombres'] ?? 'Invitado'; // Usar 'nombres' de la sesión
$rol_usuario = $_SESSION['rol'] ?? 'cliente';

$modo_edicion = isset($_GET['edit']) && $_GET['edit'] === 'true';

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Mi Perfil</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <?php if (!$modo_edicion): ?>
            <a href="index.php?page=view/perfil.php&edit=true" class="btn btn-primary">Editar Perfil</a>
        <?php else: ?>
            <a href="index.php?page=view/perfil.php" class="btn btn-secondary">Cancelar Edición</a>
        <?php endif; ?>
    </div>
</div>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-md-12">
            <p class="mb-4">¡Bienvenido a tu perfil, **<span id="welcome-nombre"><?php echo htmlspecialchars($nombre_usuario); ?></span>**!</p>

            <!-- El formulario ahora usará data-url para AJAX y no action/method directos -->
            <form id="perfilForm" data-url="../controller/controlador_usuarios.php">
                <input type="hidden" name="accion" value="actualizar_perfil">
                <!-- Campo oculto para enviar el ID del usuario cuyo perfil se está editando -->
                <input type="hidden" id="id_usuario_perfil" name="id_usuario_perfil" value="<?php echo htmlspecialchars($id_usuario); ?>">
                <!-- Campo oculto para el ID del usuario que realiza la modificación (el propio usuario logueado) -->
                <input type="hidden" id="modificado_por" name="modificado_por" value="<?php echo htmlspecialchars($id_usuario); ?>">

                <div class="row mb-3">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label for="dni" class="form-label">DNI:</label>
                        <?php if ($modo_edicion): ?>
                            <input type="text" class="form-control" id="dni" name="dni" value="" required maxlength="8" pattern="[0-9]{8}">
                            <div class="invalid-feedback">El DNI debe tener 8 dígitos.</div>
                        <?php else: ?>
                            <p class="form-control-static p-2 border rounded bg-light" id="dni_display"></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="nombres" class="form-label">Nombres:</label>
                        <?php if ($modo_edicion): ?>
                            <input type="text" class="form-control" id="nombres" name="nombres" value="" required>
                            <div class="invalid-feedback">Por favor, ingresa tus nombres.</div>
                        <?php else: ?>
                            <p class="form-control-static p-2 border rounded bg-light" id="nombres_display"></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label for="apellidos" class="form-label">Apellidos:</label>
                        <?php if ($modo_edicion): ?>
                            <input type="text" class="form-control" id="apellidos" name="apellidos" value="" required>
                            <div class="invalid-feedback">Por favor, ingresa tus apellidos.</div>
                        <?php else: ?>
                            <p class="form-control-static p-2 border rounded bg-light" id="apellidos_display"></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="correo" class="form-label">Correo Electrónico:</label>
                        <p class="form-control-static p-2 border rounded bg-light" id="correo_display"></p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label for="telefono" class="form-label">Teléfono:</label>
                        <?php if ($modo_edicion): ?>
                            <input type="tel" class="form-control" id="telefono" name="telefono" value="" maxlength="9" pattern="[0-9]{7,9}">
                            <div class="invalid-feedback">El teléfono debe tener entre 7 y 9 dígitos.</div>
                        <?php else: ?>
                            <p class="form-control-static p-2 border rounded bg-light" id="telefono_display"></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="direccion" class="form-label">Dirección:</label>
                        <?php if ($modo_edicion): ?>
                            <input type="text" class="form-control" id="direccion" name="direccion" value="">
                            <div id="address-suggestions"></div>
                        <?php else: ?>
                            <p class="form-control-static p-2 border rounded bg-light" id="direccion_display"></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Preferencia de notificación SIEMPRE visible -->
                <h3 class="mb-3 mt-4">Preferencias</h3>
                <div class="mb-4 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="recibirNotificacionesDescuento" name="recibir_notificaciones_descuento" value="1" data-user-id="<?php echo htmlspecialchars($id_usuario); ?>">
                    <label class="form-check-label" for="recibirNotificacionesDescuento">Recibir notificaciones de descuentos por correo electrónico</label>
                </div>

                <?php if ($rol_usuario === 'administrador' || $rol_usuario === 'empleado'): ?>
                    <hr class="my-4">
                    <h5 class="mb-3">Información Administrativa</h5>
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label for="rol" class="form-label">Rol:</label>
                            <p class="form-control-static p-2 border rounded bg-light" id="rol_display"></p>
                        </div>
                        <div class="col-md-6">
                            <label for="estado" class="form-label">Estado:</label>
                            <p class="form-control-static p-2 border rounded bg-light" id="estado_display"></p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label for="creado_por_info" class="form-label">Creado Por:</label>
                            <p class="form-control-static p-2 border rounded bg-light" id="creado_por_display"></p>
                        </div>
                        <div class="col-md-6">
                            <label for="fecha_creacion_info" class="form-label">Fecha Creación:</label>
                            <p class="form-control-static p-2 border rounded bg-light" id="fecha_creacion_display"></p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label for="modificado_por_info" class="form-label">Última Modificación Por:</label>
                            <p class="form-control-static p-2 border rounded bg-light" id="modificado_por_display_view"></p>
                        </div>
                        <div class="col-md-6">
                            <label for="fecha_modificacion_info" class="form-label">Fecha Última Modificación:</label>
                            <p class="form-control-static p-2 border rounded bg-light" id="fecha_modificacion_display"></p>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="row mt-4">
                    <div class="col-12 text-center">
                        <?php if ($modo_edicion): ?>
                            <button type="submit" class="btn btn-success me-2">Guardar Cambios</button>
                            <a href="index.php?page=view/perfil.php" class="btn btn-outline-secondary">Cancelar</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
            <div id="perfilStatusMessage" class="mt-3"></div> 
        </div>
    </div>
</div>

<!-- Define las variables JavaScript antes de cargar los scripts externos -->
<script>
    var js_modo_edicion = <?php echo json_encode($modo_edicion); ?>;
    var CONTROLADOR_USUARIOS_URL = '../controller/controlador_usuarios.php';
    var CONTROLADOR_PRODUCTOS_URL = '../controller/controlador_productos.php';
    var CONTROLADOR_CATEGORIAS_URL = '../controller/controlador_categorias.php';
</script>

<!-- Luego, tus scripts específicos -->
<script src="../../js/api_google_maps.js"></script>
<script src="../../js/editar_perfil.js"></script>
