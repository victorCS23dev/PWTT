$(document).ready(function () {
        $('#registroCategoriaForm').on('submit', function (e) {
            e.preventDefault();            var form = this;
            var url = $(form).data('url');
            var formData = new FormData(form);            $.ajax({
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
                    }                    if (data.status === 'success') {
                        alert(data.message);
                        form.reset();
                        
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
        });        $('#clearCategoriaForm').on('click', function () {
            $('#registroCategoriaForm')[0].reset();
        });
    });