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
    }    
    const toggleRegPassword = $('#toggleRegPassword');
    const regContrasena = $('#regContrasena');
    const toggleConfirmPassword = $('#toggleConfirmPassword');
    const confirmContrasena = $('#confirmContrasena');    if (toggleRegPassword.length && regContrasena.length) {
        toggleRegPassword.on('click', function () {
            const type = regContrasena.attr('type') === 'password' ? 'text' : 'password';
            regContrasena.attr('type', type);
            $(this).find('i').toggleClass('fa-eye fa-eye-slash');
        });
    }    if (toggleConfirmPassword.length && confirmContrasena.length) {
        toggleConfirmPassword.on('click', function () {
            const type = confirmContrasena.attr('type') === 'password' ? 'text' : 'password';
            confirmContrasena.attr('type', type);
            $(this).find('i').toggleClass('fa-eye fa-eye-slash');
        });
    }    
    function handleRegistrationFormSubmit(form, url) {
        
        let formIsValid = true;        
        $('#nombre').trigger('blur');
        $('#apellido').trigger('blur');
        $('#dni').trigger('blur');
        $('#regUsuario').trigger('blur'); 
        $('#telefono').trigger('blur');
        $('#direccion').trigger('blur');
        $('#regContrasena').trigger('blur');
        $('#confirmContrasena').trigger('blur');         
        $(form).find('input, select').each(function() {
            if ($(this).hasClass('is-invalid')) {
                formIsValid = false;
                
            }
        });        if (!formIsValid) {
            alert('Por favor, corrige los errores en el formulario antes de registrarte.');
            return; 
        }        var formData = $(form).serialize();        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            success: function (response) {
                var jsonResponse;
                try {
                    jsonResponse = JSON.parse(response);
                } catch (e) {
                    alert('Respuesta inválida del servidor. Inténtalo de nuevo.');
                    console.error('JSON Parse Error:', e, 'Response:', response);
                    return;
                }                if (jsonResponse.status === 'success') {
                    alert(jsonResponse.message); 
                    $('#regUser').trigger("reset"); 
                    
                    $(form).find('input, select').each(function() {
                        clearValidationMessage($(this));
                    });
                    window.location.replace('../main/login.php'); 
                } else {
                    alert('Error: ' + jsonResponse.message); 
                }
            },
            error: function (xhr) {
                alert('Error en la operación: No se pudo conectar con el servidor.');
                console.error('AJAX Error:', xhr);
            }
        });
    }    
    $('#regUser').submit(function (event) {
        event.preventDefault(); 
        var form = this;
        var url = $(form).data('url'); 
        handleRegistrationFormSubmit(form, url);
    });    
    $('#nombre').on('blur', function () {
        const valor = $(this).val().trim();
        clearValidationMessage($(this));
        const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
        if (valor === "") {
            showValidationMessage($(this), "El nombre no puede estar vacío.");
        } else if (!regex.test(valor)) {
            showValidationMessage($(this), "El nombre solo debe contener letras y espacios.");
        }
    });    $('#apellido').on('blur', function () {
        const valor = $(this).val().trim();
        clearValidationMessage($(this));
        const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
        if (valor === "") {
            showValidationMessage($(this), "El apellido no puede estar vacío.");
        } else if (!regex.test(valor)) {
            showValidationMessage($(this), "El apellido solo debe contener letras y espacios.");
        }
    });    $('#dni').on('blur', function () {
        const valor = $(this).val().trim();
        clearValidationMessage($(this));
        const regex = /^\d{8}$/;
        if (!regex.test(valor)) {
            showValidationMessage($(this), "El DNI debe tener exactamente 8 dígitos.");
        }
    });    $('#regUsuario').on('blur', function () { 
        const valor = $(this).val().trim();
        clearValidationMessage($(this));
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (valor === "") {
            showValidationMessage($(this), "El correo electrónico no puede estar vacío.");
        } else if (!regex.test(valor)) {
            showValidationMessage($(this), "El correo electrónico no tiene un formato válido.");
        }
    });    $('#telefono').on('blur', function () {
        const valor = $(this).val().trim();
        clearValidationMessage($(this));
        const regex = /^\d{7,9}$/;
        
        if (valor !== "" && !regex.test(valor)) {
            showValidationMessage($(this), "El teléfono debe tener entre 7 y 9 dígitos.");
        }
    });    $('#direccion').on('blur', function () {
        const valor = $(this).val().trim();
        clearValidationMessage($(this));
        
        
    });    $('#regContrasena').on('blur', function () {
        const valor = $(this).val().trim();
        clearValidationMessage($(this));
        if (valor.length < 6) {
            showValidationMessage($(this), "La contraseña debe tener al menos 6 caracteres.");
        }
        
        $('#confirmContrasena').trigger('blur'); 
    });    $('#confirmContrasena').on('blur', function () {
        const password = $('#regContrasena').val();
        const confirmPassword = $(this).val();
        clearValidationMessage($(this));
        if (confirmPassword === "") {
            showValidationMessage($(this), "Por favor, confirma tu contraseña.");
        } else if (password !== confirmPassword) {
            showValidationMessage($(this), "Las contraseñas no coinciden.");
        }
    });
});
