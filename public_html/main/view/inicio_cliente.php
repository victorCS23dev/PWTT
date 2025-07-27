<style>
    .sticky-filters {
        position: sticky; 
        top: 20px; 
        align-self: flex-start; 
        max-height: calc(100vh - 40px);  
        overflow-y: auto;  
        padding-bottom: 20px;  
    }

    @media (max-width: 767.98px) {
        .sticky-filters {
            position: static;  
            max-height: none;
            overflow-y: visible;
        }
    }
</style>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Bienvenido a la Tienda (Cliente)</h1>
</div>

<div class="container-fluid mt-3">
    <div class="row">
        <div class="col-md-3 col-lg-2 sticky-filters">
            <h4 class="mb-3">Filtros</h4>
            <hr>
            <div class="mb-4">
                <h5>Ordenar por</h5>
                <select class="form-select" id="ordenarPor">
                    <option value="">Por defecto</option>
                    <option value="precio_desc">Precio (Mayor a Menor)</option>
                    <option value="precio_asc">Precio (Menor a Mayor)</option>
                    <option value="nombre_asc">Nombre (A-Z)</option>
                    <option value="nombre_desc">Nombre (Z-A)</option>
                </select>
            </div>
            <hr>

            <div class="mb-4">
                <h5>Precio</h5>
                <div class="mb-2">
                    <label for="precioMin" class="form-label">Mínimo:</label>
                    <input type="number" class="form-control" id="precioMin" placeholder="Ej: 10" min="0" step="0.10">
                </div>
                <div class="mb-2">
                    <label for="precioMax" class="form-label">Máximo:</label>
                    <input type="number" class="form-control" id="precioMax" placeholder="Ej: 100" min="0" step="0.10">
                </div>
            </div>
            <hr>

            <div class="mb-4">
                <h5>Categoría</h5>
                <select class="form-select" id="filtroCategoria">
                    <option value="">Todas las categorías</option>
                </select>
            </div>
            <hr>

            <div class="mb-4">
                <h5>Marca</h5>
                <select class="form-select" id="filtroMarca">
                    <option value="">Todas las marcas</option>
                </select>
            </div>
            <hr>
            
            <button type="button" class="btn btn-primary w-100" id="aplicarFiltrosBtn">Aplicar Filtros</button>
            <button type="button" class="btn btn-outline-secondary w-100 mt-2" id="limpiarFiltrosBtn">Limpiar Filtros</button>
        </div>

        <div class="col-md-9 col-lg-10">
            <h4 class="mb-3">Catálogo de Productos</h4>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4" id="catalogo_productos">
            </div>
            <div class="text-center mt-4 mb-5">
                <button type="button" class="btn btn-secondary btn-lg" id="cargarMasProductosBtn">Cargar Más Productos</button>
            </div>
        </div>
    </div>
</div>

<script src="../../js/carrito.js"></script>
<script src="../../js/catalogo_cliente.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

