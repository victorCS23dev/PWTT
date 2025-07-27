$(document).ready(function() {
    const formularioEditarCategoria = $('#editarCategoriaForm');
    const eliminarButtonCategoriaModal = $('#eliminarCategoriaModal');
    const editCategoriaModal = $('#editCategoriaModal');
    const selectMarcasAsociadas = $('#marcas_asociadas_editar');

    
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
        const modalElement = document.getElementById('editCategoriaModal');
        const bsModal = bootstrap.Modal.getInstance(modalElement);
        if (bsModal) {
            bsModal.hide();
        }
    }

    
    function limpiarFormularioModal() {
        formularioEditarCategoria[0].reset();
        $('#id_categoria_editar').val('');
        $('#nombre_categoria_editar').val('');
        $('#estado_categoria_editar').val('1'); 
        selectMarcasAsociadas.empty(); 
        
        $('#creado_por_categoria_display').text('');
        $('#fecha_creacion_categoria_display').text('');
        $('#modificado_por_categoria_display').text('');
        $('#fecha_modificacion_categoria_display').text('');

        
        formularioEditarCategoria.find('input, select').each(function() {
            clearValidationMessage($(this));
        });
    }

    
    function cargarMarcasDisponibles() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: window.CONTROLADOR_MARCAS_URL, 
                method: 'GET',
                dataType: 'json',
                data: { accion: 'listar_todas_las_marcas' }, 
                success: function(respuesta) {
                    if (respuesta.status === 'success' && respuesta.data) {
                        selectMarcasAsociadas.empty(); 
                        respuesta.data.forEach(marca => {
                            selectMarcasAsociadas.append(
                                `<option value="${marca.idMarcas}">${marca.nombre} (${marca.estado == 1 ? 'Activa' : 'Inactiva'})</option>` 
                            );
                        });
                        resolve(); 
                    } else {
                        console.error('Error al cargar marcas disponibles:', respuesta.message || 'No se pudieron cargar las marcas.');
                        mostrarMensaje('Error al cargar las marcas disponibles.', true);
                        reject('Error al cargar marcas');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error al cargar marcas disponibles:", error, xhr.responseText);
                    mostrarMensaje('Error de comunicación al cargar marcas disponibles.', true);
                    reject('Error de AJAX');
                }
            });
        });
    }

    
    function obtenerMarcasDeCategoria(idCategoria) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: window.CONTROLADOR_CATEGORIAS_URL, 
                method: 'GET',
                dataType: 'json',
                data: { 
                    accion: 'obtener_marcas_por_categoria', 
                    id_categoria: idCategoria 
                },
                success: function(respuesta) {
                    if (respuesta.status === 'success' && respuesta.data) {
                        resolve(respuesta.data.map(marca => marca.idMarcas)); 
                    } else {
                        console.error('Error al obtener marcas asociadas:', respuesta.message || 'No se pudieron obtener las marcas asociadas.');
                        resolve([]); 
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error al obtener marcas asociadas:", error, xhr.responseText);
                    mostrarMensaje('Error de comunicación al obtener marcas asociadas.', true);
                    resolve([]); 
                }
            });
        });
    }

    
    editCategoriaModal.on('show.bs.modal', async function (event) {
        
        limpiarFormularioModal();

        const button = $(event.relatedTarget); 
        const categoriaId = button.data('id'); 

        if (categoriaId) {
            try {
                
                await cargarMarcasDisponibles();

                
                const respuestaCategoria = await $.ajax({
                    url: window.CONTROLADOR_CATEGORIAS_URL,
                    method: 'GET', 
                    dataType: 'json',
                    data: {
                        accion: 'obtener_categoria', 
                        id_categoria: categoriaId
                    }
                });

                if (respuestaCategoria.status === 'success' && respuestaCategoria.data) {
                    const categoria = respuestaCategoria.data;
                    
                    $('#id_categoria_editar').val(categoria.idCategorias); 
                    $('#nombre_categoria_editar').val(categoria.categoria_nombre); 
                    $('#estado_categoria_editar').val(categoria.estado); 

                    
                    const creadoPorTexto = (categoria.creado_por_nombre || '') + ' ' + (categoria.creado_por_apellido || '') + (categoria.creado_por ? ' (ID: ' + categoria.creado_por + ')' : '');
                    $('#creado_por_categoria_display').text(creadoPorTexto.trim() || 'N/A');
                    $('#fecha_creacion_categoria_display').text(categoria.fecha_creacion || 'N/A');

                    const modificadoPorTexto = (categoria.modificado_por_nombre || '') + ' ' + (categoria.modificado_por_apellido || '') + (categoria.modificado_por ? ' (ID: ' + categoria.modificado_por + ')' : '');
                    $('#modificado_por_categoria_display').text(modificadoPorTexto.trim() || 'N/A');
                    $('#fecha_modificacion_categoria_display').text(categoria.fecha_modificacion || 'N/A');

                    
                    const marcasAsociadasIds = await obtenerMarcasDeCategoria(categoriaId);
                    selectMarcasAsociadas.val(marcasAsociadasIds); 
                
                } else {
                    mostrarMensaje(respuestaCategoria.message || 'Error al obtener los datos de la categoría para edición.');
                    closeModal(); 
                }
            } catch (error) {
                console.error("Error en la carga de datos del modal de categoría:", error);
                mostrarMensaje('Error al cargar la información de la categoría y sus marcas.', true);
                closeModal();
            }
        } else {
            mostrarMensaje('ID de categoría no proporcionado para edición.', true);
            closeModal();
        }
    });

    
    formularioEditarCategoria.on('submit', function(e) {
        e.preventDefault();

        
        let formIsValid = true;
        $('#nombre_categoria_editar').trigger('blur'); 

        $(this).find('input, select').each(function() {
            if ($(this).hasClass('is-invalid')) {
                formIsValid = false;
            }
        });

        if (!formIsValid) {
            mostrarMensaje('Por favor, corrige los errores en el formulario antes de guardar.', true);
            return;
        }

        const formData = new FormData(this); 
        
        
        

        
        formData.set('accion', 'editar_categoria_con_marcas'); 
        
        
        
        
        

        const urlControlador = $(this).data('url'); 

        if (!urlControlador) {
            mostrarMensaje('URL del controlador no definida en el formulario.', true);
            return;
        }

        $.ajax({
            url: urlControlador,
            method: 'POST',
            dataType: 'json',
            data: formData, 
            processData: false, 
            contentType: false, 
            success: function(respuesta) {
                if (respuesta.status === 'success') {
                    mostrarMensaje('Categoría y marcas asociadas actualizadas correctamente.', false);
                    closeModal(); 
                    
                    if (typeof window.listar_categorias === 'function') {
                        window.listar_categorias(window.CONTROLADOR_CATEGORIAS_URL); 
                    } else {
                        console.error("La función 'listar_categorias' no está definida globalmente.");
                    }
                } else {
                    mostrarMensaje(respuesta.message || 'Error al actualizar la categoría y sus marcas.');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al actualizar categoría con marcas:", error, xhr.responseText);
                mostrarMensaje('Error al comunicarse con el servidor.', true);
            }
        });
    });

    
    eliminarButtonCategoriaModal.on('click', function() {
        const idCategoria = $('#id_categoria_editar').val();
        if (!idCategoria) {
            mostrarMensaje('No hay una categoría seleccionada para eliminar.', true);
            return;
        }

        if (confirm('¿Estás seguro de que deseas eliminar esta categoría? Esto también desasociará todas las marcas relacionadas.')) {
            const urlControlador = formularioEditarCategoria.data('url'); 
            if (!urlControlador) {
                mostrarMensaje('URL del controlador no definida.', true);
                return;
            }

            $.ajax({
                url: urlControlador,
                method: 'POST',
                dataType: 'json',
                data: {
                    accion: 'eliminar_categoria', 
                    id_categoria: idCategoria
                },
                success: function(respuesta) {
                    if (respuesta.status === 'success') {
                        mostrarMensaje('Categoría eliminada correctamente.', false);
                        closeModal(); 
                        
                        if (typeof window.listar_categorias === 'function') {
                            window.listar_categorias(window.CONTROLADOR_CATEGORIAS_URL);
                        } else {
                            console.error("La función 'listar_categorias' no está definida globalmente.");
                        }
                    } else {
                        mostrarMensaje(respuesta.message || 'Error al eliminar la categoría.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error al eliminar categoría desde modal:", error, xhr.responseText);
                    mostrarMensaje('Error al comunicarse con el servidor al eliminar.', true);
                }
            });
        }
    });

    
    editCategoriaModal.on('hidden.bs.modal', function () {
        limpiarFormularioModal();
    });

    
    $('#nombre_categoria_editar').on('blur', function () {
        const valor = $(this).val().trim();
        clearValidationMessage($(this));
        if (valor === "") {
            showValidationMessage($(this), "El nombre de la categoría no puede estar vacío.");
        } else if (!/^[a-zA-Z0-9\s.,áéíóúÁÉÍÓÚñÑ-]+$/.test(valor)) {
            showValidationMessage($(this), "El nombre contiene caracteres no permitidos.");
        }
    });
});
