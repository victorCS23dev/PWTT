$(document).ready(function () {
    
    function showStatusMessage(message, type = 'info') {
        const messageDiv = $('#payment-status-message');
        messageDiv.empty(); 
        const alertClass = `alert alert-${type} mt-3`;
        const alertHtml = `<div class="${alertClass}">${message}</div>`;
        messageDiv.html(alertHtml);
    }    
    $('#btnConfirmarPago').on('click', function () {
        showStatusMessage('Procesando tu pago, por favor espera...', 'info');
        $('#btnConfirmarPago').prop('disabled', true).text('Procesando...');         var form = $('#confirmarPagoForm')[0]; 
        var url = $(form).data('url'); 
        var formData = new FormData(form);         $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false, 
            contentType: false, 
            success: function (response) {
                
                var data = response; 
                
                
                
                if (typeof data === 'object' && data !== null && typeof data.status !== 'undefined') {
                    if (data.status === 'success') {
                        showStatusMessage('✅ Pago Realizado. ¡Gracias por tu compra!', 'success');
                        
                        setTimeout(function() {
                            $('#btnConfirmarPago').hide(); 
                            
                            
                            
                            $('#payment-status-message').after('<a href="index.php?page=view/productos.php" class="btn btn-outline-primary mt-3">← Volver a la tienda</a>');
                        }, 1000); 
                    } else {
                        showStatusMessage('❌ Error al procesar el pago: ' + (data.message || 'Error desconocido.'), 'danger');
                        $('#btnConfirmarPago').prop('disabled', false).text('Confirmar Pago'); 
                    }
                } else {
                    
                    showStatusMessage('Respuesta inesperada del servidor. Detalles: ' + response, 'danger');
                    console.error('Respuesta inesperada del servidor (no es un objeto JSON esperado):', response);
                    $('#btnConfirmarPago').prop('disabled', false).text('Confirmar Pago'); 
                }
            },
            error: function (xhr, status, error) {
                console.error('Detalles del error AJAX:', {
                    estado: status,
                    error: error,
                    respuesta: xhr.responseText,
                    xhr: xhr
                });
                showStatusMessage('❌ Error de conexión al servidor. Inténtalo de nuevo. Detalles: ' + error, 'danger');
                $('#btnConfirmarPago').prop('disabled', false).text('Confirmar Pago'); 
            }
        });
    });
});
