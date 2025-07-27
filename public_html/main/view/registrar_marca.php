<?php
header("Cache-Control: no-cache"); 
// Asegúrate de que session_start() esté al inicio de tu archivo o en un archivo incluido antes de usar $_SESSION
session_start(); 
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Registro de Marca</h1>
</div>

<div class="row">
    <form id="registroMarcaForm" class="ajaxForm" data-url="../controller/controlador_marcas.php">
        <input type="hidden" name="accion" value="registrar_marca">
        <!-- Campo oculto para el ID del usuario que crea la marca -->
        <input type="hidden" name="creado_por" value="<?php echo $_SESSION['id_usuario'] ?? ''; ?>">
        <!-- Asegúrate de que $_SESSION['id_usuario'] contenga el ID del usuario logueado. Si no está logueado, se envía NULL. -->

        <div class="row mb-4">
            <div class="col-md-6">
                <label for="nombre_marca">Nombre de la Marca:</label>
                <input type="text" id="nombre_marca" name="nombre_marca" class="form-control" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-2">
                <button type="submit" class="btn btn-danger btn-block">Registrar Marca</button>
            </div>
            <div class="col-md-2">
                <button type="button" id="clearMarcaForm" class="btn btn-outline-secondary btn-block">Limpiar</button>
            </div>
        </div>
    </form>
</div>
<script src="../js/registrar_marca.js"></script>
