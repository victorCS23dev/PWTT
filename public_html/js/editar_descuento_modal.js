$(document).ready(function() {
    
    const CONTROLADOR_DESCUENTOS_URL = '../controller/controlador_descuentos.php';    
    const editarDescuentoForm = $('#editarDescuentoForm');
    const eliminarDescuentoModalBtn = $('#eliminarDescuentoModal');
    const notificarDescuentoBtn = $('#notificarDescuentoBtn'); 
    const editDescuentoModal = $('#editDescuentoModal');
    const aplicaACategoriaEditar = $('#aplica_a_categoria_editar');
    const aplicaAMarcaEditar = $('#aplica_a_marca_editar');
    const estadoDescuentoSelect = $('#estado_editar');     
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
    function mostrarMensaje(mensaje, esError = true) {
        if (esError) {
            alert('Error: ' + mensaje);
            console.error(mensaje);
        } else {
            alert('Éxito: ' + mensaje);
            console.log(mensaje);
        }
    }    
    function closeModal() {
        const modalElement = document.getElementById('editDescuentoModal');
        const bsModal = bootstrap.Modal.getInstance(modalElement);
        if (bsModal) {
            bsModal.hide();
        }
    }    
    function limpiarFormularioModal() {
        editarDescuentoForm[0].reset();
        $('#id_codigo_editar').val('');
        $('#codigo_editar').val('');
        $('#valor_descuento_editar').val('');
        $('#descripcion_editar').val('');
        aplicaACategoriaEditar.empty().append('<option value="">Todas las Categorías</option>'); 
        aplicaAMarcaEditar.empty().append('<option value="">Todas las Marcas</option>'); 
        $('#fecha_inicio_editar').val('');
        $('#fecha_fin_editar').val('');
        $('#estado_editar').val('1');         
        $('#creado_por_descuento_display').text('');
        $('#fecha_creacion_descuento_display').text('');
        $('#modificado_por_descuento_display').text('');
        $('#fecha_modificacion_descuento_display').text('');        
        editarDescuentoForm.find('input, select, textarea').each(function() {
            clearValidationMessage($(this));
        });        
        notificarDescuentoBtn.prop('disabled', true);
    }    
    function loadCategoriasForSelect(selectedCategoryId = '') {
        $.ajax({
            url: window.CONTROLADOR_CATEGORIAS_URL, 
            method: 'GET',
            dataType: 'json',
            data: { accion: 'listar_categorias_select' }, 
            success: function(response) {
                aplicaACategoriaEditar.empty();
                aplicaACategoriaEditar.append('<option value="">Todas las Categorías</option>');
                if (response.status === 'success' && response.data) {
                    response.data.forEach(cat => {
                        aplicaACategoriaEditar.append(`<option value="${cat.id}">${cat.nombre}</option>`);
                    });
                    if (selectedCategoryId) {
                        aplicaACategoriaEditar.val(selectedCategoryId);
                    }
                } else {
                    console.error('Error al cargar categorías para el select:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX al cargar categorías para el select:', status, error);
            }
        });
    }    
    function loadMarcasForSelect(selectedMarcaId = '') {
        $.ajax({
            url: window.CONTROLADOR_MARCAS_URL, 
            method: 'GET',
            dataType: 'json',
            data: { accion: 'listar_marcas_select' }, 
            success: function(response) {
                aplicaAMarcaEditar.empty();
                aplicaAMarcaEditar.append('<option value="">Todas las Marcas</option>');
                if (response.status === 'success' && response.data) {
                    response.data.forEach(marca => {
                        aplicaAMarcaEditar.append(`<option value="${marca.id}">${marca.nombre}</option>`);
                    });
                    if (selectedMarcaId) {
                        aplicaAMarcaEditar.val(selectedMarcaId);
                    }
                } else {
                    console.error('Error al cargar marcas para el select:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX al cargar marcas para el select:', status, error);
            }
        });
    }    
    function updateNotificarButtonStatus(estadoDescuento) {
        if (estadoDescuento == 1) { 
            notificarDescuentoBtn.prop('disabled', false);
        } else { 
            notificarDescuentoBtn.prop('disabled', true);
        }
    }    
    editDescuentoModal.on('show.bs.modal', function (event) {
        limpiarFormularioModal();         const button = $(event.relatedTarget); 
        const idCodigo = button.data('id');         if (idCodigo) {
            $.ajax({
                url: CONTROLADOR_DESCUENTOS_URL,
                method: 'GET',
                dataType: 'json',
                data: { accion: 'obtener_descuento', id_codigo: idCodigo },
                success: function(response) {
                    if (response.status === 'success' && response.data) {
                        const descuento = response.data;
                        $('#id_codigo_editar').val(descuento.idCodigo);
                        $('#codigo_editar').val(descuento.codigo);
                        $('#valor_descuento_editar').val(parseFloat(descuento.valor_descuento).toFixed(2));
                        $('#descripcion_editar').val(descuento.descripcion || '');
                        
                        
                        loadCategoriasForSelect(descuento.aplica_a_categoria);
                        loadMarcasForSelect(descuento.aplica_a_marca);                        
                        
                        $('#fecha_inicio_editar').val(descuento.fecha_inicio ? new Date(descuento.fecha_inicio).toISOString().slice(0, 16) : '');
                        $('#fecha_fin_editar').val(descuento.fecha_fin ? new Date(descuento.fecha_fin).toISOString().slice(0, 16) : '');
                        estadoDescuentoSelect.val(descuento.estado);                         
                        const creadoPorTexto = (descuento.creador_nombre || '') + ' ' + (descuento.creador_apellido || '') + (descuento.creado_por ? ' (ID: ' + descuento.creado_por + ')' : '');
                        $('#creado_por_descuento_display').text(creadoPorTexto.trim() || 'N/A');
                        $('#fecha_creacion_descuento_display').text(descuento.fecha_creacion || 'N/A');                        const modificadoPorTexto = (descuento.modificado_por_nombre || '') + ' ' + (descuento.modificado_por_apellido || '') + (descuento.modificado_por ? ' (ID: ' + descuento.modificado_por + ')' : '');
                        $('#modificado_por_descuento_display').text(modificadoPorTexto.trim() || 'N/A');
                        $('#fecha_modificacion_descuento_display').text(descuento.fecha_modificacion || 'N/A');                        
                        updateNotificarButtonStatus(descuento.estado);                    } else {
                        mostrarMensaje(response.message || 'Error al obtener los datos del descuento para edición.', true);
                        closeModal();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error AJAX al obtener descuento para editar:', status, error, xhr.responseText);
                    mostrarMensaje('Error de red al cargar el descuento para editar.', true);
                    closeModal();
                }
            });
        } else {
            mostrarMensaje('ID de descuento no proporcionado para edición.', true);
            closeModal();
        }
    });    
    editarDescuentoForm.on('submit', function(e) {
        e.preventDefault();        
        let formIsValid = true;
        
        $('#codigo_editar').trigger('blur');
        $('#valor_descuento_editar').trigger('blur');
        $('#fecha_inicio_editar').trigger('blur');
        
        estadoDescuentoSelect.trigger('change');         $(this).find('input, select, textarea').each(function() {
            if ($(this).hasClass('is-invalid')) {
                formIsValid = false;
            }
        });        if (!formIsValid) {
            mostrarMensaje('Por favor, corrige los errores en el formulario antes de guardar.', true);
            return;
        }        const formData = new FormData(this);
        formData.append('accion', 'actualizar_descuento');         $.ajax({
            url: CONTROLADOR_DESCUENTOS_URL,
            method: 'POST',
            dataType: 'json',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success') {
                    mostrarMensaje(response.message, false);
                    closeModal();
                    
                    if (typeof window.listar_descuentos === 'function') {
                        window.listar_descuentos(window.CONTROLADOR_DESCUENTOS_URL);
                    } else {
                        console.error("La función 'listar_descuentos' no está definida globalmente en view.js.");
                    }
                } else {
                    mostrarMensaje(response.message, true);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX al actualizar descuento:', status, error, xhr.responseText);
                mostrarMensaje('Error al actualizar el descuento. Inténtalo de nuevo.', true);
            }
        });
    });    
    eliminarDescuentoModalBtn.on('click', function() {
        const idCodigo = $('#id_codigo_editar').val();
        if (!idCodigo) {
            mostrarMensaje('No hay un código de descuento seleccionado para eliminar.', true);
            return;
        }        if (confirm('¿Estás seguro de que deseas eliminar este código de descuento? Esta acción no se puede deshacer.')) {
            const formData = new FormData();
            formData.append('accion', 'eliminar_descuento');
            formData.append('id_codigo', idCodigo);            $.ajax({
                url: CONTROLADOR_DESCUENTOS_URL,
                method: 'POST',
                dataType: 'json',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status === 'success') {
                        mostrarMensaje(response.message, false);
                        closeModal();
                        
                        if (typeof window.listar_descuentos === 'function') {
                            window.listar_descuentos(window.CONTROLADOR_DESCUENTOS_URL);
                        } else {
                            console.error("La función 'listar_descuentos' no está definida globalmente en view.js.");
                        }
                    } else {
                        mostrarMensaje(response.message, true);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error AJAX al eliminar descuento:', status, error, xhr.responseText);
                    mostrarMensaje('Error de red al eliminar el descuento.', true);
                }
            });
        }
    });    
    notificarDescuentoBtn.on('click', function() {
        const idCodigo = $('#id_codigo_editar').val();
        const codigoDescuento = $('#codigo_editar').val();
        const valorDescuento = $('#valor_descuento_editar').val();        if (!idCodigo || !codigoDescuento || !valorDescuento) {
            mostrarMensaje('No se pudo obtener la información del descuento para notificar.', true);
            return;
        }        if (confirm(`¿Estás seguro de que deseas notificar el descuento "${codigoDescuento}" a los clientes suscritos?`)) {
            
            notificarDescuentoBtn.prop('disabled', true).text('Notificando...');            const formData = new FormData();
            formData.append('accion', 'notificar_descuento');
            formData.append('id_codigo', idCodigo);
            formData.append('codigo_descuento', codigoDescuento);
            formData.append('valor_descuento', valorDescuento);
                        $.ajax({
                url: CONTROLADOR_DESCUENTOS_URL,
                method: 'POST',
                dataType: 'json',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status === 'success') {
                        mostrarMensaje(response.message, false);
                    } else {
                        mostrarMensaje(response.message, true);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error AJAX al notificar descuento:', status, error, xhr.responseText);
                    mostrarMensaje('Error de red al intentar notificar el descuento.', true);
                },
                complete: function() {
                    
                    notificarDescuentoBtn.prop('disabled', false).text('Notificar');
                }
            });
        }
    });    
    editDescuentoModal.on('hidden.bs.modal', function () {
        limpiarFormularioModal();
    });    
    estadoDescuentoSelect.on('change', function() {
        updateNotificarButtonStatus($(this).val());
    });    
    $('#codigo_editar').on('blur', function () {
        const valor = $(this).val().trim();
        clearValidationMessage($(this));
        if (valor === "") {
            showValidationMessage($(this), "El código de descuento no puede estar vacío.");
        } else if (!/^[a-zA-Z0-9-]+$/.test(valor)) {
            showValidationMessage($(this), "El código solo puede contener letras, números y guiones.");
        }
    });    $('#valor_descuento_editar').on('blur', function () {
        const valor = parseFloat($(this).val());
        clearValidationMessage($(this));
        if (isNaN(valor) || valor < 0 || valor > 100) {
            showValidationMessage($(this), "El valor del descuento debe ser un número entre 0 y 100.");
        }
    });    $('#fecha_inicio_editar').on('blur', function () {
        const fechaInicio = $(this).val();
        clearValidationMessage($(this));
        if (fechaInicio === "") {
            showValidationMessage($(this), "La fecha de inicio es obligatoria.");
        }
    });    $('#fecha_fin_editar').on('blur', function () {
        const fechaInicio = $('#fecha_inicio_editar').val();
        const fechaFin = $(this).val();
        clearValidationMessage($(this));        if (fechaFin !== "" && fechaInicio !== "") {
            const start = new Date(fechaInicio);
            const end = new Date(fechaFin);
            if (end < start) {
                showValidationMessage($(this), "La fecha de fin no puede ser anterior a la fecha de inicio.");
            }
        }
    });
});
