$(document).ready(function () {
    
    function showValidationMessage(inputElement, message) {
        clearValidationMessage(inputElement);         const errorMessageDiv = $('<div>')
            .addClass('invalid-feedback d-block') 
            .css('color', 'red') 
            .text(message);
        
        inputElement.after(errorMessageDiv);
        inputElement.addClass('is-invalid');
    }    
    function clearValidationMessage(inputElement) {
        inputElement.next('.invalid-feedback').remove();
        inputElement.removeClass('is-invalid');
    }    $('#registroMarcaForm').on('submit', function (e) {
        e.preventDefault();         
        let formIsValid = true;
        $('#nombre_marca').trigger('blur');         $(this).find('input, select, textarea').each(function() {
            if ($(this).hasClass('is-invalid')) {
                formIsValid = false;
            }
        });        if (!formIsValid) {
            alert('Por favor, corrige los errores en el formulario antes de guardar.');
            return;
        }        var form = this;
        var url = $(form).data('url');
        var formData = new FormData(form);        $.ajax({
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
    });    $('#clearMarcaForm').on('click', function () {
        $('#registroMarcaForm')[0].reset();
        
        $('#registroMarcaForm').find('input, select, textarea').each(function() {
            clearValidationMessage($(this));
        });
    });    
    $('#nombre_marca').on('blur', function () {
        const valor = $(this).val().trim();
        clearValidationMessage($(this)); 
        if (valor === "") {
            showValidationMessage($(this), "El nombre de la marca no puede estar vacío.");
        } else if (!/^[a-zA-Z0-9\s.,áéíóúÁÉÍÓÚñÑ-]+$/.test(valor)) {
            showValidationMessage($(this), "El nombre contiene caracteres no permitidos.");
        }
    });
});
