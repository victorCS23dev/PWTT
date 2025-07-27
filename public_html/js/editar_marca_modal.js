$(document).ready(function() {
    const formularioEditarMarca = $('#editarMarcaForm');
    const eliminarButtonMarcaModal = $('#eliminarMarcaModal');
    const editMarcaModal = $('#editMarcaModal');    
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
    function mostrarMensaje(message, isError = true) {
        if (isError) {
            alert('Error: ' + message);
            console.error(message);
        } else {
            alert('Éxito: ' + message);
            console.log(message);
        }
    }    
    function closeModal() {
        const modalElement = document.getElementById('editMarcaModal');
        const bsModal = bootstrap.Modal.getInstance(modalElement);
        if (bsModal) {
            bsModal.hide();
        }
    }    
    function limpiarFormularioModal() {
        formularioEditarMarca[0].reset();
        $('#id_marca_editar').val('');
        $('#nombre_marca_editar').val('');
        $('#estado_marca_editar').val('1'); 
        
        $('#creado_por_marca_display').text('');
        $('#fecha_creacion_marca_display').text('');
        $('#modificado_por_marca_display').text('');
        $('#fecha_modificacion_marca_display').text('');        
        formularioEditarMarca.find('input, select').each(function() {
            clearValidationMessage($(this));
        });
    }    
    editMarcaModal.on('show.bs.modal', function (event) {
        
        limpiarFormularioModal();        const button = $(event.relatedTarget); 
        const marcaId = button.data('id');         if (marcaId) {
            $.ajax({
                url: window.CONTROLADOR_MARCAS_URL, 
                method: 'GET', 
                dataType: 'json',
                data: {
                    accion: 'obtener_marca', 
                    id_marca: marcaId
                },
                success: function(response) {
                    if (response.status === 'success' && response.data) {
                        const marca = response.data;
                        
                        $('#id_marca_editar').val(marca.idMarcas); 
                        $('#nombre_marca_editar').val(marca.nombre_marca); 
                        $('#estado_marca_editar').val(marca.estado);                         
                        const creadoPorTexto = (marca.creado_por_nombre || '') + ' ' + (marca.creado_por_apellido || '') + (marca.creado_por ? ' (ID: ' + marca.creado_por + ')' : '');
                        $('#creado_por_marca_display').text(creadoPorTexto.trim() || 'N/A');
                        $('#fecha_creacion_marca_display').text(marca.fecha_creacion || 'N/A');                        const modificadoPorTexto = (marca.modificado_por_nombre || '') + ' ' + (marca.modificado_por_apellido || '') + (marca.modificado_por ? ' (ID: ' + marca.modificado_por + ')' : '');
                        $('#modificado_por_marca_display').text(modificadoPorTexto.trim() || 'N/A');
                        $('#fecha_modificacion_marca_display').text(marca.fecha_modificacion || 'N/A');
                        
                    } else {
                        mostrarMensaje(response.message || 'Error al obtener los datos de la marca para edición.');
                        closeModal(); 
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error al obtener marca para editar (desde modal):", error, xhr.responseText);
                    mostrarMensaje('Error al comunicarse con el servidor para obtener datos de la marca.');
                    closeModal(); 
                }
            });
        } else {
            mostrarMensaje('ID de marca no proporcionado para edición.', true);
            closeModal();
        }
    });
    
    formularioEditarMarca.on('submit', function(e) {
        e.preventDefault();        
        let formIsValid = true;
        $('#nombre_marca_editar').trigger('blur');         $(this).find('input, select').each(function() {
            if ($(this).hasClass('is-invalid')) {
                formIsValid = false;
            }
        });        if (!formIsValid) {
            mostrarMensaje('Por favor, corrige los errores en el formulario antes de guardar.', true);
            return;
        }        const formData = $(this).serialize();
        const urlControlador = $(this).data('url');         if (!urlControlador) {
            mostrarMensaje('URL del controlador no definida en el formulario.', true);
            return;
        }        $.ajax({
            url: urlControlador,
            method: 'POST',
            dataType: 'json',
            data: formData,
            success: function(response) {
                if (response.status === 'success') {
                    mostrarMensaje('Marca actualizada correctamente.', false);
                    closeModal(); 
                    
                    if (typeof window.listar_marcas === 'function') {
                        
                        window.listar_marcas(window.CONTROLADOR_MARCAS_URL); 
                    } else {
                        console.error("La función 'listar_marcas' no está definida globalmente.");
                    }
                } else {
                    mostrarMensaje(response.message || 'Error al actualizar la marca.');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al actualizar marca:", error, xhr.responseText);
                mostrarMensaje('Error al comunicarse con el servidor.');
            }
        });
    });    
    eliminarButtonMarcaModal.on('click', function() {
        const idMarca = $('#id_marca_editar').val();
        if (!idMarca) {
            mostrarMensaje('No hay una marca seleccionada para eliminar.', true);
            return;
        }        if (confirm('¿Estás seguro de que deseas eliminar esta marca? Esta acción es irreversible.')) {
            const urlControlador = formularioEditarMarca.data('url'); 
            if (!urlControlador) {
                mostrarMensaje('URL del controlador no definida.', true);
                return;
            }            $.ajax({
                url: urlControlador,
                method: 'POST',
                dataType: 'json',
                data: {
                    accion: 'eliminar_marca',
                    id_marca: idMarca
                },
                success: function(response) {
                    if (response.status === 'success') {
                        mostrarMensaje('Marca eliminada correctamente.', false);
                        closeModal(); 
                        
                        if (typeof window.listar_marcas === 'function') {
                            window.listar_marcas(window.CONTROLADOR_MARCAS_URL);
                        } else {
                            console.error("La función 'listar_marcas' no está definida globalmente.");
                        }
                    } else {
                        mostrarMensaje(response.message || 'Error al eliminar la marca.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error al eliminar marca desde modal:", error, xhr.responseText);
                    mostrarMensaje('Error al comunicarse con el servidor al eliminar.');
                }
            });
        }
    });    
    editMarcaModal.on('hidden.bs.modal', function () {
        limpiarFormularioModal();
    });    
    $('#nombre_marca_editar').on('blur', function () {
        const value = $(this).val().trim();
        clearValidationMessage($(this));
        if (value === "") {
            showValidationMessage($(this), "El nombre de la marca no puede estar vacío.");
        } else if (!/^[a-zA-Z0-9\s.,áéíóúÁÉÍÓÚñÑ-]+$/.test(value)) {
            showValidationMessage($(this), "El nombre contiene caracteres no permitidos.");
        }
    });
});
