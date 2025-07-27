$(document).ready(function() {
    
    const formularioEditarProducto = $('#editarProductoForm');
    const editProductoModal = $('#editProductoModal');
    const currentImageDisplay = $('#current_imagen_display');    
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
        const modalElement = document.getElementById('editProductoModal');
        const bsModal = bootstrap.Modal.getInstance(modalElement);
        if (bsModal) {
            bsModal.hide();
        }
    }    
    function limpiarFormularioModal() {
        formularioEditarProducto[0].reset();
        $('#id_producto_editar').val('');
        $('#nombre_producto_editar').val('');
        $('#idMarcas_producto_editar').val(''); 
        $('#descripcion_producto_editar').val('');
        $('#precio_producto_editar').val('');
        $('#stock_producto_editar').val('');
        $('#imagen_url_actual_editar').val('');
        $('#estado_producto_editar').val('1'); 
        $('#idCategorias_producto_editar').val(''); 
        
        
        $('#creado_por_producto_display').text('');
        $('#fecha_creacion_producto_display').text('');
        $('#modificado_por_producto_display').text('');
        $('#fecha_modificacion_producto_display').text('');
        currentImageDisplay.html('<p class="text-muted">No hay imagen para mostrar.</p>');        
        formularioEditarProducto.find('input, select, textarea').each(function() {
            clearValidationMessage($(this));
        });
    }    
    function cargarCategoriasSelect(selectId, selectedCategoryId = null) {
        const selectElement = $('#' + selectId);
        $.ajax({
            url: window.CONTROLADOR_CATEGORIAS_URL,
            method: 'GET',
            data: { accion: 'listar_categorias_select' },
            dataType: 'json',
            success: function(respuesta) {
                console.log('Respuesta del servidor para categorías (en modal de producto):', respuesta);
                selectElement.empty();
                selectElement.append('<option value="">Seleccionar Categoría</option>');
                if (respuesta.status === 'success' && respuesta.data && respuesta.data.length > 0) {
                    $.each(respuesta.data, function(index, categoria) {
                        selectElement.append(`<option value="${categoria.id}">${categoria.nombre}</option>`); 
                    });
                    if (selectedCategoryId) {
                        selectElement.val(selectedCategoryId);
                    }
                } else {
                    console.log('No se encontraron categorías para cargar en el select del modal o la respuesta no es la esperada.');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al cargar categorías en el modal:", error, xhr.responseText);
            }
        });
    }    
    function cargarMarcasSelect(selectId, selectedMarcaId = null) {
        const selectElement = $('#' + selectId);
        $.ajax({
            url: window.CONTROLADOR_MARCAS_URL,
            method: 'GET',
            data: { accion: 'listar_marcas_select' },
            dataType: 'json',
            success: function(respuesta) {
                console.log('Respuesta del servidor para marcas (en modal de producto):', respuesta);
                selectElement.empty();
                selectElement.append('<option value="">Seleccionar Marca</option>');
                if (respuesta.status === 'success' && respuesta.data && respuesta.data.length > 0) {
                    $.each(respuesta.data, function(index, marca) {
                        
                        selectElement.append(`<option value="${marca.id}">${marca.nombre}</option>`);
                    });
                    if (selectedMarcaId) {
                        selectElement.val(selectedMarcaId);
                    }
                } else {
                    console.log('No se encontraron marcas para cargar en el select del modal o la respuesta no es la esperada.');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al cargar marcas en el modal:", error, xhr.responseText);
            }
        });
    }    
    try {
        editProductoModal.on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            const idProducto = button.data('id');             
            limpiarFormularioModal();            if (idProducto) {
                
                $.ajax({
                    url: window.CONTROLADOR_PRODUCTOS_URL, 
                    method: 'GET', 
                    dataType: 'json',
                    data: {
                        accion: 'obtener_producto', 
                        id_producto: idProducto
                    },
                    success: function(respuesta) {
                        if (respuesta.status === 'success' && respuesta.data) {
                            const producto = respuesta.data;
                            
                            $('#id_producto_editar').val(producto.idProductos);
                            $('#nombre_producto_editar').val(producto.producto_nombre);
                            $('#descripcion_producto_editar').val(producto.descripcion);
                            $('#precio_producto_editar').val(producto.precio);
                            $('#stock_producto_editar').val(producto.stock);
                            $('#estado_producto_editar').val(producto.producto_estado); 
                            
                            
                            $('#imagen_url_actual_editar').val(producto.imagen_url);                             
                            if (producto.imagen_url) {
                                currentImageDisplay.html(`<img src="../img/productos/${producto.imagen_url}" alt="Imagen Actual" style="max-width: 150px; height: auto; display: block; margin-top: 10px;">`);
                            } else {
                                currentImageDisplay.html('<p class="text-muted">No hay imagen actual para este producto.</p>');
                            }                            
                            cargarCategoriasSelect('idCategorias_producto_editar', producto.idCategorias); 
                            
                            cargarMarcasSelect('idMarcas_producto_editar', producto.id_marca);                             
                            const creadoPorTexto = (producto.creado_por_nombre || '') + ' ' + (producto.creado_por_apellido || '') + (producto.creado_por ? ' (ID: ' + producto.creado_por + ')' : '');
                            $('#creado_por_producto_display').text(creadoPorTexto.trim() || 'N/A');
                            $('#fecha_creacion_producto_display').text(producto.fecha_creacion || 'N/A');                            const modificadoPorTexto = (producto.modificado_por_nombre || '') + ' ' + (producto.modificado_por_apellido || '') + (producto.modificado_por ? ' (ID: ' + producto.modificado_por + ')' : '');
                            $('#modificado_por_producto_display').text(modificadoPorTexto.trim() || 'N/A');
                            $('#fecha_modificacion_producto_display').text(producto.fecha_modificacion || 'N/A');
                            
                        } else {
                            mostrarMensaje(respuesta.message || 'Error al obtener los datos del producto para edición.');
                            closeModal(); 
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error al obtener producto para editar (desde modal):", error, xhr.responseText);
                        mostrarMensaje('Error al comunicarse con el servidor para obtener datos del producto.');
                        closeModal(); 
                    }
                });
            } else {
                mostrarMensaje('ID de producto no proporcionado para edición.', true);
                closeModal();
            }
        });
    } catch (e) {
        console.error('Error al adjuntar evento show.bs.modal para #editProductoModal:', e);
    }
    
    formularioEditarProducto.on('submit', function(e) {
        e.preventDefault();         
        let formIsValid = true;
        $('#nombre_producto_editar').trigger('blur');
        $('#idMarcas_producto_editar').trigger('blur'); 
        $('#precio_producto_editar').trigger('blur');
        $('#stock_producto_editar').trigger('blur');
        $('#idCategorias_producto_editar').trigger('blur');        
        const hasNewImage = $('#imagen_producto_editar').get(0).files.length > 0;
        const hasCurrentImage = $('#imagen_url_actual_editar').val() !== '';
        
        if (!hasNewImage && !hasCurrentImage) {
            showValidationMessage($('#imagen_producto_editar'), "Se requiere una imagen para el producto.");
            formIsValid = false;
        } else {
            clearValidationMessage($('#imagen_producto_editar'));
        }        
        $(this).find('input, select, textarea').each(function() {
            if ($(this).hasClass('is-invalid')) {
                formIsValid = false;
            }
        });        if (!formIsValid) {
            mostrarMensaje('Por favor, corrige los errores en el formulario antes de guardar.', true);
            return;
        }        const formData = new FormData(this); 
        
        
        
        if (!hasNewImage) {
            formData.delete('imagen_producto');
        }        const urlControlador = $(this).data('url');         if (!urlControlador) {
            mostrarMensaje('URL del controlador no definida en el formulario.', true);
            return;
        }        $.ajax({
            url: urlControlador,
            method: 'POST',
            dataType: 'json',
            data: formData,
            processData: false, 
            contentType: false, 
            success: function(respuesta) {
                if (respuesta.status === 'success') {
                    mostrarMensaje('Producto actualizado correctamente.', false);
                    closeModal(); 
                    if (typeof window.listar_productos === 'function') {
                        window.listar_productos(window.CONTROLADOR_PRODUCTOS_URL); 
                    } else {
                        console.error("La función 'listar_productos' no está definida globalmente.");
                    }
                } else {
                    mostrarMensaje(respuesta.message || 'Error al actualizar el producto.');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al actualizar producto:", error, xhr.responseText);
                mostrarMensaje('Error al comunicarse con el servidor.');
            }
        });
    });    
    $('#imagen_producto_editar').on('change', function() {
        const [file] = this.files;
        const currentImageDisplay = $('#current_imagen_display');
        clearValidationMessage($(this));         if (file) {
            
            currentImageDisplay.html(`<img src="${URL.createObjectURL(file)}" alt="Nueva Imagen" style="max-width: 150px; height: auto; display: block; margin-top: 10px;">`);
        } else {
            
            const currentImageUrl = $('#imagen_url_actual_editar').val();
            if (currentImageUrl) {
                currentImageDisplay.html(`<img src="../img/productos/${currentImageUrl}" alt="Imagen Actual" style="max-width: 150px; height: auto; display: block; margin-top: 10px;">`);
            } else {
                currentImageDisplay.html('<p class="text-muted">No hay imagen para mostrar.</p>');
            }
        }
    });    
    $('#eliminarProductoModal').on('click', function() {
        const idProductoAEliminar = $('#id_producto_editar').val();
        if (!idProductoAEliminar) {
            mostrarMensaje('No hay un producto seleccionado para eliminar.', true);
            return;
        }        if (confirm('¿Estás seguro de que quieres eliminar este producto? Esta acción no se puede deshacer.')) {
            const urlControlador = formularioEditarProducto.data('url'); 
            if (!urlControlador) {
                mostrarMensaje('URL del controlador no definida.', true);
                return;
            }            $.ajax({
                url: urlControlador,
                method: 'POST',
                dataType: 'json',
                data: {
                    accion: 'eliminar_producto',
                    id_producto: idProductoAEliminar
                },
                success: function(respuesta) {
                    if (respuesta.status === 'success') {
                        mostrarMensaje('Producto eliminado correctamente.', false);
                        closeModal(); 
                        if (typeof window.listar_productos === 'function') {
                            window.listar_productos(window.CONTROLADOR_PRODUCTOS_URL);
                        } else {
                            console.error("La función 'listar_productos' no está definida globalmente.");
                        }
                    } else {
                        mostrarMensaje(respuesta.message || 'Error al eliminar el producto.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error (eliminar producto desde modal):", status, error, xhr.responseText);
                    mostrarMensaje('Error al comunicarse con el servidor al eliminar el producto.');
                }
            });
        }
    });    
    editProductoModal.on('hidden.bs.modal', function () {
        limpiarFormularioModal();
    });
    
    $('#nombre_producto_editar').on('blur', function () {
        const valor = $(this).val().trim();
        clearValidationMessage($(this));
        if (valor === "") {
            showValidationMessage($(this), "El nombre del producto no puede estar vacío.");
        } else if (!/^[a-zA-Z0-9\s.,áéíóúÁÉÍÓÚñÑ-]+$/.test(valor)) {
            showValidationMessage($(this), "El nombre contiene caracteres no permitidos.");
        }
    });    
    $('#idMarcas_producto_editar').on('blur change', function () {
        const valor = $(this).val();
        clearValidationMessage($(this));
        if (valor === "" || valor === null) {
            showValidationMessage($(this), "Debe seleccionar una marca.");
        }
    });    $('#precio_producto_editar').on('blur', function () {
        const valor = parseFloat($(this).val());
        clearValidationMessage($(this));
        if (isNaN(valor) || valor <= 0) {
            showValidationMessage($(this), "El precio debe ser un número positivo.");
        }
    });    $('#stock_producto_editar').on('blur', function () {
        const valor = parseInt($(this).val());
        clearValidationMessage($(this));
        if (isNaN(valor) || valor < 0) {
            showValidationMessage($(this), "El stock debe ser un número entero no negativo.");
        }
    });    $('#idCategorias_producto_editar').on('blur change', function () {
        const valor = $(this).val();
        clearValidationMessage($(this));
        if (valor === "" || valor === null) {
            showValidationMessage($(this), "Debe seleccionar una categoría.");
        }
    });});