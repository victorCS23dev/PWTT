<?php session_start(); ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Registro de Producto</h1>
</div>

<div class="row">
    <form id="registroProductoForm" class="ajaxForm" data-url="../controller/controlador_productos.php" enctype="multipart/form-data">
        <input type="hidden" name="accion" value="registrar_producto">
        <!-- Campo oculto para el ID del usuario que crea el producto -->
        <input type="hidden" id="creado_por_producto" name="creado_por" value="<?php echo $_SESSION['id_usuario'] ?? ''; ?>">

        <div class="row mb-4">
            <div class="col-md-6">
                <label for="nombre_producto" class="form-label">Nombre:</label>
                <input type="text" id="nombre_producto" name="nombre_producto" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="idMarcas_producto" class="form-label">Marca:</label>
                <select id="idMarcas_producto" name="idMarcas_producto" class="form-control" required>
                    <option value="">Seleccionar Marca</option>
                    <!-- Las marcas se cargarán aquí dinámicamente según la categoría -->
                </select>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <label for="precio_producto" class="form-label">Precio:</label>
                <input type="number" step="0.01" id="precio_producto" name="precio_producto" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="stock_producto" class="form-label">Stock:</label>
                <input type="number" id="stock_producto" name="stock_producto" class="form-control" required>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <label for="idCategorias_producto" class="form-label">Categoría:</label>
                <select id="idCategorias_producto" name="idCategorias_producto" class="form-control" required>
                    <option value="">Seleccionar Categoría</option>
                    <!-- Las categorías se cargarán aquí dinámicamente -->
                </select>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <label for="descripcion_producto" class="form-label">Descripción:</label>
                <textarea id="descripcion_producto" name="descripcion_producto" class="form-control" rows="3"></textarea>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <label for="imagen_producto" class="form-label">Imagen del Producto:</label>
                <input type="file" class="form-control" id="imagen_producto" name="imagen_producto" accept="image/*" required>
                <div class="form-text">Sube una imagen para el producto (JPG, JPEG, PNG).</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Registrar</button>
            </div>
            <div class="col-md-2">
                <button type="button" id="clearProductoForm" class="btn btn-outline-secondary w-100">Limpiar</button>
            </div>
        </div>
    </form>
</div>

<script src="../js/registrar_producto.js"></script>
<script>
$(document).ready(function() {
    // Definir las URLs de los controladores aquí, o asegúrate de que sean globales
    // window.CONTROLADOR_CATEGORIAS_URL = '../controller/controlador_categorias.php';
    // window.CONTROLADOR_MARCAS_URL = '../controller/controlador_marcas.php'; // Asegúrate de que esta URL esté definida

    // Función para cargar las categorías en el select
    function cargarCategorias() {
        $.ajax({
            url: window.CONTROLADOR_CATEGORIAS_URL, // Usar la variable global
            method: 'GET',
            data: { accion: 'listar_categorias_select' }, // Acción para listar categorías para un select
            dataType: 'json',
            success: function(respuesta) {
                const selectCategoria = $('#idCategorias_producto');
                selectCategoria.empty();
                selectCategoria.append('<option value="">Seleccionar Categoría</option>'); // Opción por defecto

                if (respuesta.status === 'success' && respuesta.data && respuesta.data.length > 0) {
                    $.each(respuesta.data, function(index, categoria) {
                        selectCategoria.append(`<option value="${categoria.id}">${categoria.nombre}</option>`);
                    });
                } else {
                    console.log('No se encontraron categorías para cargar en el select o la respuesta no es la esperada.');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al cargar categorías para el select:", error, xhr.responseText);
                alert('Error al cargar las categorías. Inténtalo de nuevo.');
            }
        });
    }

    // NUEVA FUNCIÓN: Cargar marcas relacionadas a una categoría específica
    function cargarMarcasPorCategoria(idCategoria) {
        const selectMarca = $('#idMarcas_producto');
        selectMarca.empty();
        selectMarca.append('<option value="">Seleccionar Marca</option>'); // Opción por defecto
        selectMarca.prop('disabled', true); // Deshabilitar el select de marcas inicialmente

        if (idCategoria) {
            $.ajax({
                url: window.CONTROLADOR_CATEGORIAS_URL, // Usar controlador de categorías para obtener marcas relacionadas
                method: 'GET',
                data: { 
                    accion: 'obtener_marcas_por_categoria', // Nueva acción
                    id_categoria: idCategoria 
                },
                dataType: 'json',
                success: function(respuesta) {
                    if (respuesta.status === 'success' && respuesta.data && respuesta.data.length > 0) {
                        $.each(respuesta.data, function(index, marca) {
                            selectMarca.append(`<option value="${marca.idMarcas}">${marca.nombre}</option>`);
                        });
                        selectMarca.prop('disabled', false); // Habilitar si hay marcas
                    } else {
                        console.log('No se encontraron marcas activas para la categoría seleccionada.');
                        selectMarca.prop('disabled', true); // Mantener deshabilitado si no hay marcas
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error al cargar marcas por categoría:", error, xhr.responseText);
                    alert('Error al cargar las marcas para la categoría seleccionada. Inténtalo de nuevo.');
                    selectMarca.prop('disabled', true);
                }
            });
        }
    }

    cargarCategorias(); // Carga las categorías al iniciar la página

    // Evento change para el selector de categorías
    $('#idCategorias_producto').on('change', function() {
        const selectedCategoryId = $(this).val();
        cargarMarcasPorCategoria(selectedCategoryId);
    });

    // Deshabilitar el select de marcas al cargar la página inicialmente
    $('#idMarcas_producto').prop('disabled', true);
});
</script>
