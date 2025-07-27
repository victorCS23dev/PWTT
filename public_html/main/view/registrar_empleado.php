<?php
header("Cache-Control: no-cache"); 
// Asegúrate de que session_start() esté al inicio de tu archivo o en un archivo incluido antes de usar $_SESSION
session_start(); 
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Registro de Empleado</h1>
</div>

<div class="row">
    <form id="registroForm" class="ajaxForm" data-url="../controller/controlador_usuarios.php">
        <input type="hidden" name="accion" value="registrar_empleado">
        <!-- Nuevo campo oculto para el ID del usuario que crea el registro -->
        <input type="hidden" name="creado_por" value="<?php echo $_SESSION['id_usuario'] ?? ''; ?>">
        <!-- Asegúrate de que $_SESSION['id_usuario'] contenga el ID del usuario logueado. Si no, usa NULL. -->

        <div class="row mb-4">
            <div class="col-md-4">
                <label for="dni_empleado">DNI:</label>
                <input type="number" id="dni_empleado" name="dni_empleado" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label for="nombre_empleado">Nombre:</label>
                <input type="text" id="nombre_empleado" name="nombre_empleado" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label for="apellido_empleado">Apellido:</label>
                <input type="text" id="apellido_empleado" name="apellido_empleado" class="form-control" required>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <label for="correo_empleado">Correo:</label>
                <input type="email" id="correo_empleado" name="correo_empleado" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label for="contrasena_empleado">Contraseña:</label>
                <input type="password" id="contrasena_empleado" name="contrasena_empleado" class="form-control" required>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <label for="telefono_empleado">Teléfono:</label>
                <input type="tel" id="telefono_empleado" name="telefono_empleado" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label for="direccion_empleado">Dirección:</label>
                <input type="text" id="direccion_empleado" name="direccion_empleado" class="form-control" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-2">
                <button type="submit" class="btn btn-danger btn-block">Registrar</button>
            </div>

            <div class="col-md-2">
                <button type="button" id="clearForm" class="btn btn-outline-secondary btn-block">Limpiar</button>
            </div>
        </div>
    </form>
</div>

<script src="../js/registrar_empleado.js"></script>
