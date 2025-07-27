$(document).ready(function() {
    
    const CONTROLADOR_PRODUCTOS_URL = '../controller/controlador_productos.php';
    const CONTROLADOR_RESEÑAS_URL = '../controller/controlador_reseña.php';

    if (!PRODUCT_ID) {
        $('#product-detail-container').html('<div class="alert alert-danger" role="alert">Error: ID de producto no proporcionado.</div>');
        return;
    }

    function loadProductDetails(productId) {
        $.ajax({
            url: CONTROLADOR_PRODUCTOS_URL,
            method: 'GET',
            dataType: 'json',
            data: {
                accion: 'obtener_producto', 
                id_producto: productId
            },
            success: function(respuesta) {
                const container = $('#product-detail-container');
                container.empty(); 

                if (respuesta.status === 'success' && respuesta.data) {
                    const product = respuesta.data;

                    
                    const productEncodedUrl = encodeURIComponent(CURRENT_PAGE_URL);
                    const productEncodedName = encodeURIComponent(`¡Mira este producto: ${product.producto_nombre} en nuestra tienda!`);

                    const productHtml = `
                        <div class="row">
                            <div class="col-md-5 d-flex justify-content-center align-items-center">
                                <img src="../img/productos/${product.imagen_url || 'placeholder.png'}" 
                                    alt="${product.producto_nombre}" 
                                    class="img-fluid rounded shadow-sm" 
                                    style="max-height: 400px; object-fit: contain;"
                                    onerror="this.onerror=null;this.src='https:
                            </div>
                            <div class="col-md-7">
                                <h2 class="card-title fw-bold text-primary mb-3">${product.producto_nombre}</h2>
                                <h4 class="text-muted mb-3">${product.marca}</h4>
                                <p class="lead fw-bold text-success fs-3">S/${parseFloat(product.precio).toFixed(2)}</p>
                                <p class="card-text"><strong>Descripción:</strong> ${product.descripcion || 'No disponible'}</p>
                                <p class="card-text"><strong>Categoría:</strong> ${product.categoria_nombre || 'No disponible'}</p>
                                <p class="card-text"><strong>Stock Disponible:</strong> <span class="badge bg-info text-dark">${product.stock}</span></p>
                                
                                <hr class="my-4">

                                <div class="d-grid gap-2">
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

                                    <button class="btn btn-outline-primary btn-lg buy-now-btn"
                                        data-product-id="${product.idProductos}"
                                        data-product-name="${product.producto_nombre}"
                                        data-product-brand="${product.marca}"
                                        data-product-price="${parseFloat(product.precio).toFixed(2)}"
                                        data-product-image="../img/productos/${product.imagen_url || 'placeholder.png'}"
                                        data-quantity="1"
                                        data-product-stock="${product.stock}">
                                        Comprar Ahora
                                    </button>
                                </div>

                                <div class="mt-4">
                                    <h5>Compartir Producto:</h5>
                                    <div class="d-flex gap-2">
                                        <a href="https:
                                        target="_blank" class="btn btn-outline-primary">
                                        <i class="fab fa-facebook-f"></i> Facebook
                                        </a>
                                        <a href="https:
                                        target="_blank" class="btn btn-outline-info text-dark">
                                        <i class="fab fa-x-twitter"></i> X
                                        </a>
                                        <a href="https:
                                        target="_blank" class="btn btn-outline-success">
                                        <i class="fab fa-whatsapp"></i> WhatsApp
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    container.html(productHtml);

                    
                    loadProductReviews(productId);

                } else {
                    container.html('<div class="alert alert-warning" role="alert">Producto no encontrado o no disponible.</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX al cargar detalles del producto:', status, error, xhr.responseText);
                $('#product-detail-container').html('<div class="alert alert-danger" role="alert">Error al cargar los detalles del producto. Inténtalo de nuevo más tarde.</div>');
            }
        });
    }

    
    function loadProductReviews(productId) {
        $.ajax({
            url: CONTROLADOR_RESEÑAS_URL,
            method: 'GET',
            dataType: 'json',
            data: {
                accion: 'listar_reseñas_producto', 
                id_producto: productId
            },
            success: function(respuesta) {
                const reviewsContainer = $('#product-reviews-container');
                reviewsContainer.empty(); 

                if (respuesta.status === 'success' && respuesta.data && respuesta.data.length > 0) {
                    let reviewsHtml = '';
                    respuesta.data.forEach(review => {
                        
                        let starsHtml = '';
                        for (let i = 1; i <= 5; i++) {
                            starsHtml += `<svg xmlns="http:
                                <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
                            </svg>`;
                        }

                        reviewsHtml += `
                            <div class="card mb-3 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title mb-1">${review.usuario_nombre} ${review.usuario_apellido}</h5>
                                    <div class="mb-2">
                                        ${starsHtml}
                                        <small class="text-muted ms-2">${review.calificacion} estrellas</small>
                                    </div>
                                    <p class="card-text">${review.comentario || 'Sin comentario.'}</p>
                                    <small class="text-muted">Fecha: ${new Date(review.fecha_creacion).toLocaleDateString()}</small>
                                </div>
                            </div>
                        `;
                    });
                    reviewsContainer.html(reviewsHtml);
                } else {
                    reviewsContainer.html('<div class="alert alert-info text-center" role="alert">Aún no hay reseñas para este producto. ¡Sé el primero en calificarlo!</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX al cargar reseñas:', status, error, xhr.responseText);
                $('#product-reviews-container').html('<div class="alert alert-danger" role="alert">Error al cargar las reseñas. Inténtalo de nuevo más tarde.</div>');
            }
        });
    }

    
    if (PRODUCT_ID) {
        loadProductDetails(PRODUCT_ID);
    }

    
    $(document).on('click', '.buy-now-btn', function() {
        const productId = $(this).data('product-id');
        const productName = $(this).data('product-name');
        const productBrand = $(this).data('product-brand');
        const productPrice = $(this).data('product-price');
        const productImage = $(this).data('product-image');
        const quantity = $(this).data('quantity') || 1;

        $.ajax({
            url: '../controller/controlador_carrito.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'add',
                id: productId,
                nombre: productName,
                marca: productBrand,
                precio: productPrice,
                cantidad: quantity,
                imagen: productImage
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = 'index.php?page=view/carrito.php';
                } else {
                    alert('Error al añadir al carrito: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al añadir al carrito:', status, error);
                alert('Error al procesar la compra. Inténtalo de nuevo.');
            }
        });
    });

    $(document).on('click', '.add-to-cart-btn', function() {
        const productId = $(this).data('product-id');
        const productName = $(this).data('product-name');
        const productBrand = $(this).data('product-brand');
        const productPrice = $(this).data('product-price');
        const productImage = $(this).data('product-image');
        const quantity = $(this).data('quantity') || 1;

        $.ajax({
            url: '../controller/controlador_carrito.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'add',
                id: productId,
                nombre: productName,
                marca: productBrand,
                precio: productPrice,
                cantidad: quantity,
                imagen: productImage
            },
            success: function(response) {
                if (!response.success) {
                    alert('Error al añadir al carrito: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al añadir al carrito:', status, error);
                alert('Error al procesar la solicitud. Inténtalo de nuevo.');
            }
        });
    });
});
