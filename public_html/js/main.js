document.addEventListener('DOMContentLoaded', function() {
    
    function showMessage(message, type = 'success') {
        const messageDiv = document.getElementById('mensaje');
        if (messageDiv) {
            messageDiv.textContent = message;
            messageDiv.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning', 'alert-info');
            messageDiv.classList.add(`alert-${type}`);
            
            setTimeout(() => {
                messageDiv.classList.add('d-none');
            }, 5000); 
        }
    }    
    
    
    document.querySelectorAll('#regLogin, #recuperarPassForm, #restablecerPassForm').forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault();             const form = this;
            const url = form.getAttribute('data-url');
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');            if (submitButton) {
                submitButton.disabled = true; 
                submitButton.textContent = 'Procesando...'; 
            }            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    form.reset(); 
                    
                    if (form.id === 'restablecerPassForm') {
                        setTimeout(() => {
                            window.location.href = '../main/login.php';
                        }, 2000); 
                    }
                } else {
                    showMessage(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Ocurrió un error de conexión. Inténtalo de nuevo.', 'danger');
            })
            .finally(() => {
                if (submitButton) {
                    submitButton.disabled = false; 
                    
                    if (form.id === 'recuperarPassForm') {
                        submitButton.textContent = 'Enviar enlace de recuperación';
                    } else if (form.id === 'restablecerPassForm') {
                        submitButton.textContent = 'Restablecer Contraseña';
                    } else if (form.id === 'regLogin') {
                        submitButton.textContent = 'Ingresar';
                    }
                    
                }
            });
        });
    });    
    document.querySelectorAll('.input-group-text[id$="Password"], .input-group-text.toggle-password').forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
});
