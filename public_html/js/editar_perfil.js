$(document).ready(function() {
    
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
    const perfilStatusMessage = $('#perfilStatusMessage');    
    function showStatusAlert(message, type = 'info') {
        perfilStatusMessage.html(`<div class="alert alert-${type}">${message}</div>`);
        setTimeout(() => perfilStatusMessage.empty(), 5000); 
    }    
    const userId = $('#id_usuario_perfil').val(); 
    const controladorUsuariosUrl = window.CONTROLADOR_USUARIOS_URL;
    const modoEdicion = window.js_modo_edicion;
    const recibirNotificacionesCheckbox = $('#recibirNotificacionesDescuento');
    const recibirNotificacionesDisplay = $('#recibir_notificaciones_descuento_display');
    const recibirNotificacionesDisplayContainer = $('#recibir_notificaciones_descuento_display_container');
    
    if (userId && controladorUsuariosUrl) {
        $.ajax({
            url: controladorUsuariosUrl,
            method: 'GET',
            dataType: 'json',
            data: {
                accion: 'obtener_usuario', 
                id_usuario: userId
            },
            success: function(respuesta) {
                if (respuesta.status === 'success' && respuesta.data) {
                    const usuario = respuesta.data;
                    
                    
                    $('#dni_display').text(usuario.dni || 'N/A');
                    $('#nombres_display').text(usuario.nombres || 'N/A'); 
                    $('#apellidos_display').text(usuario.apellidos || 'N/A'); 
                    $('#correo_display').text(usuario.correo || 'N/A'); 
                    $('#telefono_display').text(usuario.telefono || ''); 
                    $('#direccion_display').text(usuario.direccion || '');
                    
                    
                    $('#welcome-nombre').text(usuario.nombres || 'Invitado');                    
                    recibirNotificacionesCheckbox.prop('checked', usuario.recibir_notificaciones_descuento == 1);
                    
                    
                    recibirNotificacionesDisplay.text(usuario.recibir_notificaciones_descuento == 1 ? 'Activas' : 'Inactivas');
                    
                    
                    if (modoEdicion) {
                        recibirNotificacionesDisplayContainer.hide(); 
                    } else {
                        recibirNotificacionesDisplayContainer.show(); 
                    }                    
                    if (modoEdicion) {
                        $('#dni').val(usuario.dni || '');
                        $('#nombres').val(usuario.nombres || ''); 
                        $('#apellidos').val(usuario.apellidos || ''); 
                        $('#telefono').val(usuario.telefono || '');
                        $('#direccion').val(usuario.direccion || '');
                    }                     
                    if (usuario.rol === 'administrador' || usuario.rol === 'empleado') {
                        $('#rol_display').text(usuario.rol.charAt(0).toUpperCase() + usuario.rol.slice(1));
                        $('#estado_display').text(usuario.estado == 1 ? 'Activo' : 'Inactivo');
                        
                        const creadoPorNombre = usuario.creador_nombre || '';
                        const creadoPorApellido = usuario.creador_apellido || '';
                        const creadoPorId = usuario.creado_por || '';
                        $('#creado_por_display').text(creadoPorNombre + ' ' + creadoPorApellido + (creadoPorId ? ' (ID: ' + creadoPorId + ')' : ''));
                        $('#fecha_creacion_display').text(usuario.fecha_creacion || 'No disponible');                        const modificadoPorNombre = usuario.modificado_por_nombre || '';
                        const modificadoPorApellido = usuario.modificado_por_apellido || '';
                        const modificadoPorId = usuario.modificado_por || '';
                        $('#modificado_por_display_view').text(modificadoPorNombre + ' ' + modificadoPorApellido + (modificadoPorId ? ' (ID: ' + modificadoPorId + ')' : ''));
                        $('#fecha_modificacion_display').text(usuario.fecha_modificacion || 'No disponible');
                    }
                } else {
                    console.error('Error al obtener datos del perfil:', respuesta.message || 'Datos no encontrados.');
                    showStatusAlert('Error al cargar la información de tu perfil.', 'danger');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX al cargar perfil:', status, error, xhr.responseText);
                showStatusAlert('No se pudo conectar con el servidor para cargar tu perfil.', 'danger');
            }
        });
    } else {
        console.warn('ID de usuario o URL del controlador no disponible en la sesión para cargar el perfil.');
    }    
    $('#perfilForm').on('submit', function(e) {
        e.preventDefault();         let formIsValid = true;        
        $('#dni').trigger('blur');
        $('#nombres').trigger('blur');
        $('#apellidos').trigger('blur');
        $('#telefono').trigger('blur');
        $('#direccion').trigger('blur');         
        $(this).find('input[type="text"], input[type="tel"]').each(function() {
            if ($(this).hasClass('is-invalid')) {
                formIsValid = false;
            }
        });        if (!formIsValid) {
            showStatusAlert('Por favor, corrige los errores en el formulario antes de guardar.', 'danger');
            return; 
        }        const formData = new FormData(this);         
        
        formData.delete('recibir_notificaciones_descuento');        $.ajax({
            url: controladorUsuariosUrl, 
            method: 'POST',
            dataType: 'json',
            data: formData,
            processData: false, 
            contentType: false, 
            success: function(response) {
                if (response.status === 'success') {
                    showStatusAlert(response.message, 'success');
                    
                    
                    window.location.href = 'index.php?page=view/perfil.php'; 
                } else {
                    showStatusAlert(response.message, 'danger');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX al actualizar perfil:', status, error, xhr.responseText);
                showStatusAlert('Error al actualizar el perfil. Inténtalo de nuevo.', 'danger');
            }
        });
    });    
    recibirNotificacionesCheckbox.on('change', function() {
        const isChecked = $(this).is(':checked');
        const userIdForNotification = $(this).data('user-id');
        const newValue = isChecked ? 1 : 0;        if (!userIdForNotification) {
            showStatusAlert('No se pudo obtener el ID de usuario para actualizar la preferencia.', 'danger');
            return;
        }        const formData = new FormData();
        formData.append('accion', 'actualizar_preferencia_notificacion');
        formData.append('id_usuario', userIdForNotification); 
        formData.append('recibir_notificaciones_descuento', newValue);        $.ajax({
            url: controladorUsuariosUrl,
            method: 'POST',
            dataType: 'json',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success') {
                    showStatusAlert(response.message, 'success');
                    
                    recibirNotificacionesDisplay.text(newValue == 1 ? 'Activas' : 'Inactivas');
                } else {
                    showStatusAlert(response.message, 'danger');
                    
                    recibirNotificacionesCheckbox.prop('checked', !isChecked);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX al actualizar preferencia de notificación:', status, error, xhr.responseText);
                showStatusAlert('Error de red al actualizar la preferencia de notificación. Inténtalo de nuevo.', 'danger');
                
                recibirNotificacionesCheckbox.prop('checked', !isChecked);
            }
        });
    });    
    $('#dni').on('blur', function () {
        const valor = $(this).val().trim();
        clearValidationMessage($(this));
        const regex = /^\d{8}$/;
        if (!regex.test(valor)) {
            showValidationMessage($(this), "El DNI debe tener exactamente 8 dígitos.");
        }
    });    $('#nombres').on('blur', function () { 
        const valor = $(this).val().trim();
        clearValidationMessage($(this));
        const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
        if (valor === "") {
            showValidationMessage($(this), "El nombre no puede estar vacío.");
        } else if (!regex.test(valor)) {
            showValidationMessage($(this), "El nombre solo debe contener letras y espacios.");
        }
    });    $('#apellidos').on('blur', function () { 
        const valor = $(this).val().trim();
        clearValidationMessage($(this));
        const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
        if (valor === "") {
            showValidationMessage($(this), "El apellido no puede estar vacío.");
        } else if (!regex.test(valor)) {
            showValidationMessage($(this), "El apellido solo debe contener letras y espacios.");
        }
    });    $('#telefono').on('blur', function () {
        const valor = $(this).val().trim();
        clearValidationMessage($(this));
        const regex = /^\d{7,9}$/;
        if (valor !== "" && !regex.test(valor)) {
            showValidationMessage($(this), "El teléfono debe tener entre 7 y 9 dígitos.");
        }
    });    $('#direccion').on('blur', function () {
        clearValidationMessage($(this));
    });
});
