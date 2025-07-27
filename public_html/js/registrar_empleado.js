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

    $('#registroForm').on('submit', function (e) {
        e.preventDefault();

        let formIsValid = true;
        $(this).find('input, select, textarea').each(function() {
            if ($(this).is(':visible') && !$(this).prop('readonly')) { 
                $(this).trigger('blur');
                if ($(this).hasClass('is-invalid')) {
                    formIsValid = false;
                }
            }
        });

        if (!formIsValid) {
            alert('Por favor, corrige los errores en el formulario antes de enviar.');
            return;
        }

        var form = this;
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
                }

                if (data.status === 'success') {
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
    });

    $('#clearForm').on('click', function () {
        $('#registroForm')[0].reset();
        $('#registroForm').find('input, select, textarea').each(function() {
            clearValidationMessage($(this));
        });
    });

    $('#dni_empleado').on('blur', function () {
        const valor = $(this).val().trim();
        clearValidationMessage($(this)); 
        const regex = /^\d{8}$/;
        if (!regex.test(valor)) {
            showValidationMessage($(this), "El DNI debe tener exactamente 8 dígitos.");
        }
    });

    $('#nombre_empleado').on('blur', function () {
        const valor = $(this).val().trim();
        clearValidationMessage($(this));
        const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
        if (!regex.test(valor)) {
            showValidationMessage($(this), "El nombre solo debe contener letras y espacios.");
        }
    });

    $('#apellido_empleado').on('blur', function () {
        const valor = $(this).val().trim();
        clearValidationMessage($(this));
        const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/; 
        if (!regex.test(valor)) {
            showValidationMessage($(this), "El apellido solo debe contener letras y espacios.");
        }
    });

    $('#correo_empleado').on('blur', function () {
        const valor = $(this).val().trim();
        clearValidationMessage($(this));
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!regex.test(valor)) {
            showValidationMessage($(this), "El correo no tiene un formato válido.");
        }
    });

    $('#contrasena_empleado').on('blur', function () {
        const valor = $(this).val().trim();
        clearValidationMessage($(this));
        if (valor.length < 6) {
            showValidationMessage($(this), "La contraseña debe tener al menos 6 caracteres.");
        }
    });

    $('#telefono_empleado').on('blur', function () {
        const valor = $(this).val().trim();
        clearValidationMessage($(this));
        const regex = /^\d{7,9}$/;
        if (valor !== "" && !regex.test(valor)) { 
            showValidationMessage($(this), "El teléfono debe tener entre 7 y 9 dígitos.");
        }
    });

    $('#direccion_empleado').on('blur', function () {
        clearValidationMessage($(this)); 
    });

});
