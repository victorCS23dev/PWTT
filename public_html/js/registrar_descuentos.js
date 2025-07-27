
$(document).ready(function() {
    const CONTROLADOR_DESCUENTOS_URL = '../controller/controlador_descuentos.php';
    const registroDescuentoForm = $('#registroDescuentoForm');     loadCategorias();
    loadMarcas();    function showStatusAlert(message, type = 'info') {
        alert(message); 
    }    
    function loadCategorias() {
        $.ajax({
            url: CONTROLADOR_DESCUENTOS_URL,
            method: 'GET',
            dataType: 'json',
            data: { accion: 'listar_categorias' },
            success: function(response) {
                const select = $('#aplica_a_categoria');
                select.find('option:not(:first)').remove();
                if (response.status === 'success' && response.data) {
                    response.data.forEach(cat => {
                        select.append(`<option value="${cat.id}">${cat.nombre}</option>`);
                    });
                } else {
                    console.error('Error al cargar categorías:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX al cargar categorías:', status, error, xhr.responseText);
            }
        });
    }    function loadMarcas() {
        $.ajax({
            url: CONTROLADOR_DESCUENTOS_URL,
            method: 'GET',
            dataType: 'json',
            data: { accion: 'listar_marcas' }, 
            success: function(response) {
                const select = $('#aplica_a_marca');
                select.find('option:not(:first)').remove(); 
                if (response.status === 'success' && response.data) {
                    response.data.forEach(marca => {
                        select.append(`<option value="${marca.id}">${marca.nombre}</option>`);
                    });
                } else {
                    console.error('Error al cargar marcas:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX al cargar marcas:', status, error, xhr.responseText);
            }
        });
    }    registroDescuentoForm.on('submit', function(e) {
        e.preventDefault();         const form = this; 
        const url = $(form).data('url');
        const formData = new FormData(form); 
        formData.append('accion', 'registrar_descuento');         $.ajax({
            url: url,
            method: 'POST',
            dataType: 'json',
            data: formData,
            processData: false, 
            contentType: false, 
            success: function(response) {
                if (response.status === 'success') {
                    showStatusAlert(response.message, 'success');
                    form.reset(); 
                    loadCategorias();
                    loadMarcas();
                } else {
                    showStatusAlert(response.message, 'danger');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX al registrar descuento:', status, error, xhr.responseText);
                showStatusAlert('Error al registrar el descuento. Inténtalo de nuevo.', 'danger');
            }
        });
    });    $('#clearDescuentoForm').on('click', function() {
        registroDescuentoForm[0].reset();
        loadCategorias();
        loadMarcas();
    });
});
