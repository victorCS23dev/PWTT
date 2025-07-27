<?php
// main/restablecer_contrasena.php
header("Cache-Control: no-cache");

$token = $_GET['token'] ?? '';

if (empty($token)) {
    header("Location: login.php");
    exit();
}

include 'head.php';
?>

<div class="container d-flex align-items-center justify-content-center vh-100">
    <div class="card text-center shadow-lg p-3 mb-5 bg-white rounded" style="width: 25rem;">
        <div class="card-body">
            <h3 class="card-title">Restablecer Contraseña</h3>
            <!-- Div para mostrar mensajes al usuario (éxito, error) -->
            <div id="mensaje" class="alert d-none" role="alert"></div>

            <!-- Formulario para establecer la nueva contraseña -->
            <!-- La clase 'ajaxForm' y el atributo 'data-url' son para tu script JS que maneja peticiones AJAX -->
            <form id="restablecerPassForm" class="ajaxForm" data-url="../controller/controlador_restablecer_contrasena.php">
                <!-- Campo oculto para enviar el token al controlador -->
                <input type="hidden" name="accion" value="restablecer_contrasena">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <div class="form-group mt-3">
                    <label for="nuevaContrasena">Nueva Contraseña</label>
                    <div class="input-group">
                        <input type="password" name="nuevaContrasena" class="form-control" id="nuevaContrasena" placeholder="Nueva Contraseña" required>
                        <span class="input-group-text toggle-password" style="cursor: pointer;">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
                
                <div class="form-group mt-3">
                    <label for="confirmarContrasena">Confirmar Nueva Contraseña</label>
                    <div class="input-group">
                        <input type="password" name="confirmarContrasena" class="form-control" id="confirmarContrasena" placeholder="Confirmar Nueva Contraseña" required>
                        <span class="input-group-text toggle-password" style="cursor: pointer;">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary mt-4 w-100">Restablecer Contraseña</button>
            </form>

            <!-- Enlace para volver a la página de login -->
            <a href="../main/login.php" class="d-block mt-3">Volver al Login</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toggle-password').forEach(function(toggle) {
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
</script>
<script src="../js/main.js"></script>
<?php 
include 'footer.php'; 
?>
