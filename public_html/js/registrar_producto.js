
$(document).ready(function () {    
    function showValidationMessage(inputElement, message) {
        
        clearValidationMessage(inputElement);        
        const errorMessageDiv = $('<div>')
            .addClass('invalid-feedback d-block') 
            .css('color', 'red') 
            .text(message);
        
        
        inputElement.after(errorMessageDiv);
        
        inputElement.addClass('is-invalid');
    }    
    function clearValidationMessage(inputElement) {
        
        inputElement.next('.invalid-feedback').remove();
        
        inputElement.removeClass('is-invalid');
    }    
    $('#registroProductoForm').on('submit', function (e) {
        e.preventDefault();         
        let formIsValid = true;
        
        
        $('#nombre_producto').trigger('blur');
        $('#idMarcas_producto').trigger('blur'); 
        $('#precio_producto').trigger('blur');
        $('#stock_producto').trigger('blur');
        $('#idCategorias_producto').trigger('blur');
        
        
        
        if ($('#imagen_producto').get(0).files.length === 0) {
            showValidationMessage($('#imagen_producto'), "Debe seleccionar una imagen para el producto.");
            formIsValid = false;
        } else {
            clearValidationMessage($('#imagen_producto'));
        }        
        $(this).find('input, select, textarea').each(function() {
            if ($(this).hasClass('is-invalid')) {
                formIsValid = false;
                
                
                
                
                
            }
        });
        if (!formIsValid) {
            
            alert('Por favor, corrige los errores en el formulario antes de enviar.');
            return;
        }        var form = this; 
        var url = $(form).data('url'); 
        var formData = new FormData(form);         
        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false, 
            contentType: false, 
            success: function (response) {
                var data;
                try {
                    data = JSON.parse(response); 
                } catch (err) {
                    alert('Respuesta inválida del servidor.'); 
                    console.error('Error al parsear JSON:', err);
                    return;
                }                if (data.status === 'success') {
                    alert(data.message); 
                    form.reset(); 
                    
                    $(form).find('input, select, textarea').each(function() {
                        clearValidationMessage($(this));
                    });
                    
                    
                    
                    if (typeof cargarCategorias === 'function') {
                        cargarCategorias();
                    }
                    if (typeof cargarMarcasPorCategoria === 'function') {
                        cargarMarcasPorCategoria(''); 
                    }
                } else {
                    alert(data.message); 
                }
            },
            error: function (xhr, status, error) {
                console.error('Detalles del error:', {
                    estado: status,
                    error: error,
                    respuesta: xhr.responseText
                });
                alert('Hubo un error al procesar el formulario.'); 
            }
        });
    });    
    $('#clearProductoForm').on('click', function () {
        $('#registroProductoForm')[0].reset();
        
        $('#registroProductoForm').find('input, select, textarea').each(function() {
            clearValidationMessage($(this));
        });
        
        if (typeof cargarMarcasPorCategoria === 'function') {
            cargarMarcasPorCategoria(''); 
        }
    });    
    $('#nombre_producto').on('blur', function () {
        const valor = $(this).val().trim();
        clearValidationMessage($(this)); 
        if (valor === "") {
            showValidationMessage($(this), "El nombre del producto no puede estar vacío.");
        } else if (!/^[a-zA-Z0-9\s.,áéíóúÁÉÍÓÚñÑ-]+$/.test(valor)) { 
            showValidationMessage($(this), "El nombre contiene caracteres no permitidos.");
        }
    });    $('#precio_producto').on('blur', function () {
        const valor = parseFloat($(this).val());
        clearValidationMessage($(this));
        if (isNaN(valor) || valor <= 0) {
            showValidationMessage($(this), "El precio debe ser un número positivo.");
        }
    });    $('#stock_producto').on('blur', function () {
        const valor = parseInt($(this).val());
        clearValidationMessage($(this));
        if (isNaN(valor) || valor < 0) {
            showValidationMessage($(this), "El stock debe ser un número entero no negativo.");
        }
    });    
    $('#idCategorias_producto').on('blur change', function () {
        const valor = $(this).val();
        clearValidationMessage($(this));
        if (valor === "" || valor === null) {
            showValidationMessage($(this), "Debe seleccionar una categoría.");
        }
    });    $('#idMarcas_producto').on('blur change', function () {
        const valor = $(this).val();
        clearValidationMessage($(this));
        if (valor === "" || valor === null) {
            showValidationMessage($(this), "Debe seleccionar una marca.");
        }
    });    
    
});
