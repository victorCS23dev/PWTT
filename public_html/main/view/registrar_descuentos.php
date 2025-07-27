<?php
// view/registrar_descuentos.php
session_start(); // Asegúrate de que la sesión esté iniciada para obtener el ID de usuario
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Registro de Código de Descuento</h1>
</div>

<div class="row">
    <form id="registroDescuentoForm" class="ajaxForm" data-url="../../controller/controlador_descuentos.php">
        <input type="hidden" name="accion" value="registrar_descuento">
        <!-- Campo oculto para el ID del usuario que crea el descuento -->
        <input type="hidden" id="creadoPor" name="creado_por" value="<?php echo htmlspecialchars($_SESSION['id_usuario'] ?? ''); ?>">
        
        <div class="row mb-4">
            <div class="col-md-6">
                <label for="codigo" class="form-label">Código de Descuento:</label>
                <input type="text" class="form-control" id="codigo" name="codigo" required maxlength="50" placeholder="Ej: MADRE2025">
            </div>
            <div class="col-md-6">
                <label for="valor_descuento" class="form-label">Valor del Descuento (%):</label>
                <input type="number" step="0.01" min="0" max="100" class="form-control" id="valor_descuento" name="valor_descuento" required placeholder="Ej: 10.00 para 10%">
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <label for="descripcion" class="form-label">Descripción (Opcional):</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="2" placeholder="Breve descripción del descuento..."></textarea>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <label for="aplica_a_categoria" class="form-label">Aplica a Categoría (Todas si no se selecciona):</label>
                <select class="form-select" id="aplica_a_categoria" name="aplica_a_categoria">
                    <option value="">Todas las Categorías</option>
                    <!-- Opciones cargadas por JS -->
                </select>
            </div>
            <div class="col-md-6">
                <label for="aplica_a_marca" class="form-label">Aplica a Marca (Todas si no se selecciona):</label>
                <select class="form-select" id="aplica_a_marca" name="aplica_a_marca">
                    <option value="">Todas las Marcas</option>
                    <!-- Opciones cargadas por JS -->
                </select>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <label for="fecha_inicio" class="form-label">Fecha y Hora de Inicio:</label>
                <input type="datetime-local" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
            </div>
            <div class="col-md-6">
                <label for="fecha_fin" class="form-label">Fecha y Hora de Fin (Opcional):</label>
                <input type="datetime-local" class="form-control" id="fecha_fin" name="fecha_fin">
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <label for="estado" class="form-label">Estado:</label>
                <select class="form-select" id="estado" name="estado" required>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Registrar</button>
            </div>
            <div class="col-md-2">
                <button type="button" id="clearDescuentoForm" class="btn btn-outline-secondary w-100">Limpiar</button>
            </div>
        </div>
    </form>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/registrar_descuentos.js"></script>
