<?php
// main/recuperar_contrasena.php
header("Cache-Control: no-cache");
include 'head.php'; // Asegúrate de que esta ruta sea correcta
?>

<div class="container d-flex align-items-center justify-content-center vh-100">
    <div class="card text-center shadow-lg p-3 mb-5 bg-white rounded" style="width: 25rem;">
        <div class="card-body">
            <h3 class="card-title">Recuperar Contraseña</h3>
            <div id="mensaje" class="alert d-none" role="alert"></div>

            <form id="recuperarPassForm" class="ajaxForm" data-url="../controller/controlador_recuperar_contrasena.php">
                <input type="hidden" name="accion" value="solicitar_recuperacion">
                <div class="form-group mt-3">
                    <label for="emailRecuperacion">Correo Electrónico</label>
                    <input type="email" name="emailRecuperacion" class="form-control" id="emailRecuperacion" placeholder="Ingresa tu correo electrónico" required>
                </div>
                <button type="submit" class="btn btn-primary mt-4 w-100">Enviar enlace de recuperación</button>
            </form>

            <a href="../main/login.php" class="d-block mt-3">Volver al Login</a>
        </div>
    </div>
</div>
<script src="../js/main.js"></script>
<?php include 'footer.php';?>