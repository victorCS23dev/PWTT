<?php
// main/home.php
include 'head.php';

$is_logged_in = isset($_SESSION['id_usuario']); 
$user_role = $_SESSION['rol'] ?? '';

$requested_page = $_GET['page'] ?? '';

$content_to_include = ''; // Usaremos esta variable para almacenar la ruta del archivo a incluir

$public_pages = [
    'view/inicio_cliente.php', 
    'view/detalle_producto.php'
];

$private_pages = [
    'view/perfil.php',
    'view/carrito.php',
    'view/compra.php',
    'view/resumen_pago.php',
    'view/historial_compras.php'
];

$admin_employee_pages = [
    'view/inicio.php', // Dashboard de administrador/empleado
    'view/ver_usuarios.php',
    'view/registrar_empleado.php',
    'view/ver_categorias.php',
    'view/registrar_categoria.php',
    'view/ver_productos.php',
    'view/registrar_producto.php',
    'view/ver_marcas.php',
    'view/registrar_marca.php',
    'view/ver_descuentos.php',
    'view/registrar_descuentos.php',
    'view/ver_pedidos.php'
];


// Lógica para determinar la página de contenido a cargar
if (empty($requested_page)) {
    if ($is_logged_in && ($user_role === 'administrador' || $user_role === 'empleado')) {
        $content_to_include = 'view/inicio.php'; // Dashboard para admin/empleado
    } else {
        $content_to_include = 'view/inicio_cliente.php'; // Catálogo para clientes y visitas (invitados)
    }
} elseif ($requested_page === 'view/calificar_producto.php') {
    if ($is_logged_in) {
        $content_to_include = 'controller/controlador_calificar.php';
    } else {
        header("Location: login.php?redirect_to=" . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
} elseif (in_array($requested_page, $public_pages)) {
    $content_to_include = $requested_page;
} elseif (in_array($requested_page, $private_pages)) {
    if ($is_logged_in) {
        $content_to_include = $requested_page; // Permitir acceso si está logueado
    } else {
        header("Location: login.php?redirect_to=" . urlencode($_SERVER['REQUEST_URI']));
        exit; // Detener la ejecución del script
    }
} elseif (in_array($requested_page, $admin_employee_pages)) {
    if ($is_logged_in && ($user_role === 'administrador' || $user_role === 'empleado')) {
        $content_to_include = $requested_page; // Permitir acceso si tiene el rol adecuado
    } else {
        $content_to_include = 'view/inicio_cliente.php'; // Puedes redirigir a login.php o mostrar un mensaje de acceso denegado
    }
} else {
    if ($is_logged_in && ($user_role === 'administrador' || $user_role === 'empleado')) {
        $content_to_include = 'view/inicio.php'; // Por defecto, dashboard de admin/empleado
    } else {
        $content_to_include = 'view/inicio_cliente.php'; // Por defecto, catálogo para otros roles y visitas
    }
}

?>

<div class="container-fluid">
    <div class="row d-flex">

        <?php if ($is_logged_in && ($user_role === 'administrador' || $user_role === 'empleado')): ?>
            <?php include 'view/sildebar/sild-admin.php'; ?> 

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4" id="contenido">
                <?php 
                if (!empty($content_to_include)) {
                    include $content_to_include; 
                } else {
                    echo '<div class="alert alert-danger text-center mt-5">Página no disponible.</div>';
                }
                ?>
            </main>

        <?php else: // Cliente o Invitado ?>
            <main class="col-12 px-md-4" id="contenido">
                <?php 
                if (!empty($content_to_include)) {
                    include $content_to_include; 
                } else {
                    echo '<div class="alert alert-danger text-center mt-5">Página no disponible.</div>';
                }
                ?>
            </main>
        <?php endif; ?>

    </div>
</div>

<?php
// Incluimos el footer.php (que está en la misma carpeta 'main/')
include 'footer.php';
?>
