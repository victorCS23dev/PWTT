$(document).ready(function() {
    const CART_CONTROLLER_URL = typeof CONTROLADOR_CARRITO_URL !== 'undefined' ? CONTROLADOR_CARRITO_URL : '../controller/controlador_carrito.php';

    function sendCartRequest(action, data = {}) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: CART_CONTROLLER_URL,
                type: 'POST',
                dataType: 'json',
                data: { action: action, ...data },
                success: function(response) {
                    if (response.success) {
                        console.log('Cart operation successful:', response.message);
                        if (action !== 'validate_checkout_stock') {
                            renderCart(response.cartItems, response.total);
                        }
                        resolve(response);
                    } else {
                        console.error('Cart operation failed:', response.message);
                        showNotification('Error: ' + response.message, 'error');
                        renderCart(response.cartItems, response.total);
                        reject(new Error(response.message));
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX Error:', textStatus, errorThrown);
                    showNotification('Error de conexión con el servidor.', 'error');
                    reject(errorThrown);
                }
            });
        });
    }

    function showNotification(message, type) {
        const alertType = {
            success: 'alert-success',
            error: 'alert-danger',
            info: 'alert-primary'
        }[type] || 'alert-primary';

        const notification = $(`
            <div class="alert ${alertType} alert-dismissible fade show position-fixed top-0 end-0 m-4" role="alert" style="z-index: 1055; min-width: 250px;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `);

        $('body').append(notification);

        setTimeout(() => {
            notification.alert('close');
        }, 3000);
    }

    function renderCart(cartItems, total) {
        const $cartBody = $('#cart-items-body');
        $cartBody.empty(); 

        const $emptyCartMessage = $('.container .alert-info');
        const $cartContentSection = $('.container .row').eq(1); 

        if (cartItems && cartItems.length > 0) {
            $emptyCartMessage.addClass('d-none'); 
            $cartContentSection.removeClass('d-none');

            cartItems.forEach(item => {
                const subtotal = (item.precio * item.cantidad).toFixed(2);
                const row = `
                    <tr>
                        <td class="text-start">
                            <div class="d-flex align-items-center">
                                <div class="me-3 d-flex align-items-center justify-content-center border rounded" style="width: 60px; height: 60px; overflow: hidden;">
                                    <img src="${item.imagen || 'https://placehold.co/60x60'}"
                                        alt="Imagen de ${item.nombre}"
                                        style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                                <div>
                                    <div class="fw-bold">${item.nombre}</div>
                                    <div class="text-muted small">Marca: ${item.marca || 'N/A'}</div>
                                </div>
                            </div>
                        </td>
                        <td>S/${item.precio.toFixed(2)}</td>
                        <td>
                            <input type="number" value="${item.cantidad}" min="1"
                                class="form-control form-control-sm w-auto quantity-input"
                                data-product-id="${item.id}"
                                data-product-stock="${item.stock || 99999}"> <!-- Añadir data-product-stock aquí -->
                        </td>
                        <td id="subtotal-${item.id}">S/${subtotal}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-danger remove-item-btn" data-product-id="${item.id}">Eliminar</button>
                        </td>
                    </tr>`;
                $cartBody.append(row);
            });
            $('#cart-subtotal').text(`S/${total.toFixed(2)}`);
            $('#cart-total').text(`S/${total.toFixed(2)}`);
        } else {
            $cartContentSection.addClass('d-none');
            $emptyCartMessage.removeClass('d-none');
        }
    }

    sendCartRequest('get_cart').catch(() => {
        renderCart([], 0);
    });

    $(document).on('click', '.add-to-cart-btn', function() {
        const productId = $(this).data('product-id');
        const productName = $(this).data('product-name');
        const productBrand = $(this).data('product-brand');
        const productPrice = $(this).data('product-price');
        const productImage = $(this).data('product-image'); 
        const quantity = $(this).data('quantity') || 1; 
        const productStock = $(this).data('product-stock');

        sendCartRequest('add', {
            id: productId,
            nombre: productName,
            marca: productBrand,
            precio: productPrice,
            cantidad: quantity,
            imagen: productImage,
            stock: productStock
        }).then(() => {
            showNotification('Producto añadido al carrito.', 'success');
        });
    });

    $(document).on('click', '.remove-item-btn', function() {
        const productId = $(this).data('product-id');
        sendCartRequest('remove', { id: productId }).then(() => {
            showNotification('Producto eliminado.', 'success');
        });
    });

    $(document).on('change', '.quantity-input', function() {
        const productId = $(this).data('product-id');
        let newQuantity = parseInt($(this).val());
        const productStock = parseInt($(this).data('product-stock'));

        if (isNaN(newQuantity) || newQuantity < 1) { 
            newQuantity = 1; 
            $(this).val(1); 
            showNotification('La cantidad mínima es 1.', 'info');
        }

        if (newQuantity > productStock) {
            showNotification(`No puedes pedir más de ${productStock} unidades. Cantidad ajustada.`, 'error');
            newQuantity = productStock; 
            $(this).val(newQuantity);x
            if (newQuantity === 0) {
                showNotification('Producto agotado y eliminado del carrito.', 'info');
                sendCartRequest('remove', { id: productId }); 
                return; 
            }
        }

        if (newQuantity === 0) {
            showNotification('Producto agotado y eliminado del carrito.', 'info');
            sendCartRequest('remove', { id: productId });
            return;
        }

        sendCartRequest('update', { id: productId, cantidad: newQuantity }).then(() => {
            showNotification('Cantidad actualizada.', 'success');
        }).catch(error => {
            console.error('Fallo al actualizar la cantidad del carrito:', error.message);
        });
    });

    $('#update-cart-btn').on('click', function() {
        sendCartRequest('get_cart').then(() => {
            showNotification('Carrito actualizado manualmente.', 'info');
        });
    });

    $('#checkout-btn').on('click', function(e) {
        e.preventDefault(); 

        const $thisButton = $(this);
        $thisButton.prop('disabled', true).text('Verificando stock...'); 

        sendCartRequest('validate_checkout_stock', {}).then(response => {
            if (response.success) {
                showNotification('Stock verificado. Redirigiendo a la compra...', 'info');
                window.location.href = $thisButton.closest('form').attr('action') + '?page=' + $thisButton.closest('form').find('input[name="page"]').val();
            } else {
                renderCart(response.cartItems, response.total); 
                $thisButton.prop('disabled', false).text('Proceder a la Compra');
            }
        }).catch(error => {
            $thisButton.prop('disabled', false).text('Proceder a la Compra');
        });
    });
});
