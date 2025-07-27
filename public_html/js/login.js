$(document).ready(function () {
    
    $('#loginPassword').on('click', function () {
        var passwordField = $('#logincontraseña');
        var type = passwordField.attr('type') === 'password' ? 'text' : 'password';
        passwordField.attr('type', type);        
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });    
    function handleFormSubmit(form, url) {
        var formData = $(form).serialize();        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            success: function (response) {
                var jsonResponse;
                try {
                    jsonResponse = JSON.parse(response);
                } catch (e) {
                    alert('Respuesta inválida del servidor.');
                    return;
                }                if (jsonResponse.status === 'success') {
                    $('#regLogin').trigger("reset");
                    
                    window.location.replace('../index.php?page=main/home.php');
                } else {
                    alert('Error: ' + jsonResponse.message);
                }
            },
            error: function (xhr) {
                alert('Error en la operación: ' + xhr.responseText);
            }
        });
    }    
    $('#regLogin').submit(function (event) {
        event.preventDefault();
        var form = this;
        var url = $(form).data('url'); 
        handleFormSubmit(form, url);
    });
});