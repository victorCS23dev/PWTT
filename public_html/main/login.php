<?php
// main/login.php
header("Cache-Control: no-cache");

require_once __DIR__ . '/../vendor/autoload.php';

// --- CONFIGURACIÓN DE GOOGLE OAUTH ---
$google_client_id = 'GOOGLE OAUTH';
$google_client_secret = 'GOOGLE OAUTH';

$google_redirect_uri = 'http://localhost:3000/controller/google_callback.php';


// Crear un objeto de cliente de Google
$client = new Google_Client();
$client->setClientId($google_client_id);
$client->setClientSecret($google_client_secret);
$client->setRedirectUri($google_redirect_uri);

// Solicitar acceso al email y perfil básico del usuario
$client->addScope('email');
$client->addScope('profile');

// Generar la URL de autenticación que redirigirá al usuario a Google
$google_login_url = $client->createAuthUrl();

// --- FIN CONFIGURACIÓN DE GOOGLE OAUTH ---

// Incluye el head.php después de la configuración de Google si necesitas algo de head para Google
include 'head.php';
?>

<div class="container d-flex align-items-center justify-content-center vh-100">
    <div class="card text-center shadow-lg p-3 mb-5 bg-white rounded" style="width: 20rem;">
        <div class="card-body">
            <h3 class="card-title">Login</h3>
            <div id="mensaje" class="alert d-none" role="alert"></div>

            <form id="regLogin" class="ajaxForm" data-url="../controller/controlador_login.php">
                <input type="hidden" name="accion" value="validlogin">
                <div class="form-group mt-3">
                    <label for="usuario">Correo</label>
                    <input type="email" name="usuario" class="form-control" id="usuario" placeholder="Correo" required>
                </div>
                <div class="form-group mt-3">
                    <label for="logincontraseña">Contraseña</label>
                    <div class="input-group">
                        <input type="password" name="logincontraseña" class="form-control" id="logincontraseña" placeholder="Contraseña" required>
                        <span class="input-group-text" id="loginPassword" style="cursor: pointer;">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
                <div class="text-end mt-2">
                    <a href="../main/recuperar_contrasena.php" class="text-decoration-none">¿Olvidaste tu contraseña?</a>
                </div>
                <button type="submit" class="btn btn-primary mt-4 w-100">Ingresar</button>
            </form>

            <div class="text-center my-3">
                <p>— O —</p>
            </div>

            <a href="<?php echo htmlspecialchars($google_login_url); ?>" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center">
                <img src="https://img.icons8.com/color/16/000000/google-logo.png" alt="Google logo" class="me-2">
                Iniciar sesión con Google
            </a>

            <a href="../main/registrar.php" class="d-block mt-3">Regístrate</a>
            </div>
    </div>
</div>

<script src="../js/main.js"></script>
<?php include 'footer.php'; ?>