<?php
header("Cache-Control: no-cache"); 
// Asegúrate de que session_start() esté al inicio de tu archivo o en un archivo incluido antes de usar $_SESSION
session_start(); 
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Registro de Categoría</h1>
</div>

<div class="row">
    <form id="registroCategoriaForm" class="ajaxForm" data-url="../controller/controlador_categorias.php">
        <input type="hidden" name="accion" value="registrar_categoria">
        <!-- Nuevo campo oculto para el ID del usuario que crea la categoría -->
        <input type="hidden" name="creado_por" value="<?php echo $_SESSION['id_usuario'] ?? ''; ?>">
        <!-- Asegúrate de que $_SESSION['id_usuario'] contenga el ID del usuario logueado. Si no, usa NULL. -->

        <div class="row mb-4">
            <div class="col-md-6">
                <label for="nombre_categoria">Nombre de la Categoría:</label>
                <input type="text" id="nombre_categoria" name="nombre_categoria" class="form-control" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-2">
                <button type="submit" class="btn btn-danger btn-block">Registrar Categoría</button>
            </div>
            <div class="col-md-2">
                <button type="button" id="clearCategoriaForm" class="btn btn-outline-secondary btn-block">Limpiar</button>
            </div>
        </div>
    </form>
</div>
<script src="../js/registrar_categoria.js"></script>
