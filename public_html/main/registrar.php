<?php
header("Cache-Control: no-cache");
include 'head.php';
?>

<div class="container d-flex align-items-center justify-content-center vh-100">
    <div class="card text-center shadow-lg p-3 mb-5 bg-white rounded" style="width: 40rem;">
        <div class="card-body">
            <h3 class="card-title">Registro de Usuario</h3>
            <form id="regUser" class="ajaxForm" data-url="../controller/controlador_registro.php">
                <input type="hidden" name="accion" value="registeruser">
                <input type="hidden" name="rol" value="cliente">
                <!-- Nuevo campo oculto para creado_por, que será NULL para auto-registro -->
                <input type="hidden" name="creado_por" value=""> 

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mt-3">
                            <label for="nombre">Nombre</label>
                            <input type="text" name="nombre" class="form-control" id="nombre" placeholder="Tu Nombre" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mt-3">
                            <label for="apellido">Apellido</label>
                            <input type="text" name="apellido" class="form-control" id="apellido" placeholder="Tu Apellido" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mt-3">
                            <label for="dni">DNI</label>
                            <input type="text" name="dni" class="form-control" id="dni" placeholder="Número de DNI" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mt-3">
                            <label for="regUsuario">Correo Electrónico</label>
                            <input type="email" name="regUsuario" class="form-control" id="regUsuario" placeholder="Correo Electrónico" required>
                        </div>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <label for="direccion">Dirección (Opcional)</label>
                    <input type="text" name="direccion" class="form-control" id="direccion" placeholder="Tu Dirección">
                </div>

                <div class="form-group mt-3">
                    <label for="telefono">Teléfono (Opcional)</label>
                    <input type="tel" name="telefono" class="form-control" id="telefono" placeholder="Número de Teléfono (Ej. 987654321)">
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mt-3">
                            <label for="regContrasena">Contraseña</label>
                            <div class="input-group">
                                <input type="password" name="regContrasena" class="form-control" id="regContrasena" placeholder="Contraseña" required>
                                <span class="input-group-text" id="toggleRegPassword" style="cursor: pointer;">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mt-3">
                            <label for="confirmContrasena">Confirmar Contraseña</label>
                            <div class="input-group">
                                <input type="password" name="confirmContrasena" class="form-control" id="confirmContrasena" placeholder="Confirmar Contraseña" required>
                                <span class="input-group-text" id="toggleConfirmPassword" style="cursor: pointer;">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-4 w-100">Registrarse</button>
            </form>
            <a href="../main/login.php" class="d-block mt-3">¿Ya tienes cuenta? Inicia Sesión</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
