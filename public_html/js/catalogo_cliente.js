$(document).ready(function() {
    const CONTROLADOR_PRODUCTOS_URL = '../controller/controlador_productos.php'; 
    const CONTROLADOR_CATEGORIAS_URL = '../controller/controlador_categorias.php';
    const CONTROLADOR_MARCAS_URL = '../controller/controlador_marcas.php';
    
    const PRODUCTOS_POR_PAGINA = 8; 
    let offsetActual = 0; 
    
    function getFilterParams() {
        const precioMin = $('#precioMin').val();
        const precioMax = $('#precioMax').val();
        const categoria = $('#filtroCategoria').val();
        const ordenarPor = $('#ordenarPor').val();
        const marcaSeleccionada = $('#filtroMarca').val(); 
        const urlParams = new URLSearchParams(window.location.search);
        const searchQueryFromUrl = urlParams.get('search_query');
        const searchInputVal = $('#searchInput').val(); 
        const finalSearchQuery = searchInputVal || searchQueryFromUrl;
        return {
            precio_min: precioMin ? parseFloat(precioMin) : null,
            precio_max: precioMax ? parseFloat(precioMax) : null,
            id_categoria: categoria || null, 
            
            marcas: marcaSeleccionada || null, 
            ordenar_por: ordenarPor || null, 
            search_query: finalSearchQuery || null 
        };
    }

    function renderProducts(products, append = false) {
        const catalogoDiv = $('#catalogo_productos');
        if (!append) {
            catalogoDiv.empty(); 
        }

        if (products.length === 0 && !append) {
            catalogoDiv.html('<div class="col-12 text-center mt-5"><p>No se encontraron productos con los criterios seleccionados.</p></div>');
            $('#cargarMasProductosBtn').hide(); 
            return;
        }

        products.forEach(product => {
            const productCard = `
                <div class="col">
                    <div class="card h-100 shadow-sm rounded-lg">
                        <a href="../index.php?page=view/detalle_producto.php&id=${product.idProductos}">
                            <img src="../img/productos/${product.imagen_url || 'placeholder.png'}" 
                                class="card-img-top p-3 rounded-lg-top" 
                                alt="${product.producto_nombre}" 
                                onerror="this.onerror=null;this.src='https:
                        </a>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-truncate">${product.producto_nombre}</h5>
                            <p class="card-text text-muted">${product.marca}</p>
                            <p class="card-text fw-bold text-primary">S/${product.precio ? parseFloat(product.precio).toFixed(2) : 'N/A'}</p>
                            <p class="card-text text-sm">Stock: ${product.stock}</p>
                            <div class="mt-auto d-grid gap-2">
                                <button class="btn btn-dark btn-sm add-to-cart-btn"
                                    data-product-id="${product.idProductos}"
                                    data-product-name="${product.producto_nombre}"
                                    data-product-brand="${product.marca}"
                                    data-product-price="${parseFloat(product.precio).toFixed(2)}"
                                    data-product-image="../img/productos/${product.imagen_url || 'placeholder.png'}"
                                    data-quantity="1"
                                    data-product-stock="${product.stock}">
                                    Añadir al Carrito
                                </button>
                                <a href="../index.php?page=view/detalle_producto.php&id=${product.idProductos}" class="btn btn-outline-info btn-sm">Ver Detalles</a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            catalogoDiv.append(productCard);
        });

        
        if (products.length < PRODUCTOS_POR_PAGINA) {
            $('#cargarMasProductosBtn').hide();
        } else {
            $('#cargarMasProductosBtn').show();
        }
    }

    function loadProducts(append = false, resetOffset = false) {
        if (resetOffset) {
            offsetActual = 0; 
        }
        const filtros = getFilterParams();
        const requestData = {
            accion: 'listar_productos_activos', 
            limite: PRODUCTOS_POR_PAGINA,
            desplazamiento: offsetActual,
            ...filtros 
        };

        $.ajax({
            url: CONTROLADOR_PRODUCTOS_URL,
            method: 'GET',
            dataType: 'json',
            data: requestData,
            success: function(respuesta) {
                if (respuesta.status === 'success' && respuesta.data) {
                    renderProducts(respuesta.data, append);
                    if (respuesta.data.length > 0) {
                        offsetActual += respuesta.data.length;
                    }
                } else {
                    console.warn('No se encontraron productos o hubo un error:', respuesta.message);
                    if (!append) { 
                        renderProducts([], false); 
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX al cargar productos:', status, error, xhr.responseText);
                if (!append) {
                    $('#catalogo_productos').html('<div class="col-12 text-center mt-5 text-danger"><p>Error al cargar el catálogo de productos. Inténtalo de nuevo más tarde.</p></div>');
                }
                $('#cargarMasProductosBtn').hide();
            }
        });
    }

    function cargarFiltroCategorias() {
        $.ajax({
            url: CONTROLADOR_CATEGORIAS_URL,
            method: 'GET',
            data: { accion: 'listar_categorias_select' }, 
            dataType: 'json',
            success: function(respuesta) {
                const selectCategoria = $('#filtroCategoria');
                if (selectCategoria.find('option[value=""]').length === 0) {
                    selectCategoria.empty().append('<option value="">Todas las categorías</option>'); 
                } else {
                    selectCategoria.find('option:not([value=""])').remove();
                }

                if (respuesta.status === 'success' && respuesta.data && respuesta.data.length > 0) {
                    $.each(respuesta.data, function(index, categoria) {
                        selectCategoria.append(`<option value="${categoria.id}">${categoria.nombre}</option>`);
                    });
                } else {
                    console.warn('No se encontraron categorías o la respuesta no fue exitosa.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX al cargar categorías para el filtro:', status, error, xhr.responseText);
            }
        });
    }

    function cargarFiltroMarcas() {
        $.ajax({
            url: CONTROLADOR_MARCAS_URL, 
            method: 'GET',
            data: { accion: 'listar_marcas_select' }, 
            dataType: 'json',
            success: function(respuesta) {
                const selectMarca = $('#filtroMarca'); 
                if (selectMarca.find('option[value=""]').length === 0) {
                    selectMarca.empty().append('<option value="">Todas las marcas</option>');
                } else {
                    selectMarca.find('option:not([value=""])').remove();
                }

                if (respuesta.status === 'success' && respuesta.data && respuesta.data.length > 0) {
                    $.each(respuesta.data, function(index, marca) { 
                        selectMarca.append(`<option value="${marca.id}">${marca.nombre}</option>`);
                    });
                } else {
                    console.warn('No se encontraron marcas o la respuesta no fue exitosa.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX al cargar marcas para el filtro:', status, error, xhr.responseText);
            }
        });
    }
    
    const urlParams = new URLSearchParams(window.location.search);
    const initialSearchQuery = urlParams.get('search_query');
    if (initialSearchQuery) {
        $('#searchInput').val(initialSearchQuery); 
    }
    loadProducts(false, true); 
    cargarFiltroCategorias();
    cargarFiltroMarcas(); 

    $('#cargarMasProductosBtn').on('click', function() {
        loadProducts(true); 
    });
    
    $('#aplicarFiltrosBtn, #ordenarPor, #filtroCategoria, #filtroMarca').on('click change', function() { 
        loadProducts(false, true); 
    });
    
    $('#searchForm').on('submit', function(e) {
        e.preventDefault(); 
        const currentUrl = new URL(window.location.href);
        const searchQuery = $('#searchInput').val();
        if (searchQuery) {
            currentUrl.searchParams.set('search_query', searchQuery);
            currentUrl.searchParams.set('page', 'main/view/inicio_cliente.php'); 
        } else {
            currentUrl.searchParams.delete('search_query');
        }
        window.history.pushState({}, '', currentUrl.toString()); 
        loadProducts(false, true); 
    });

    $('#limpiarFiltrosBtn').on('click', function() {
        $('#precioMin').val('');
        $('#precioMax').val('');
        $('#filtroCategoria').val('');
        $('#filtroMarca').val(''); 
        $('#ordenarPor').val(''); 
        $('#searchInput').val(''); 
        const url = new URL(window.location.href);
        url.searchParams.delete('search_query');
        url.searchParams.delete('page'); 
        window.history.replaceState({}, document.title, url.toString());
        loadProducts(false, true); 
    });
});
