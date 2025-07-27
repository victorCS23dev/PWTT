<?php
    $imgUser = "../img/icon_perfil.png";
    $rolUsuario = $_SESSION['rol'] ?? ''; // Obtén el rol del usuario de la sesión
?>



<nav id="sidebar" class="col-md-3 col-lg-2 bg-light sidebar vh-100">
    <div class="position-sticky">
        <div class="d-flex align-items-center p-3">
            <img src="<?php echo $imgUser; ?>" alt="User" class="rounded-circle" width="40" height="40">
            <span class="ms-2"><?php echo $_SESSION['apellido'] . ', ' . $_SESSION['nombre']; ?></span> </div>
        <ul class="nav flex-column" id="menu">
        <li class="nav-item">
            <a class="nav-link" href="#" id="menu-home" data-page="inicio.php">
                <span data-feather="home"></span>
                Inicio
            </a>
        </li>
        <?php if ($rolUsuario === 'administrador'): ?>
        <li class="nav-item">
            <a class="nav-link" href="#" id="menu-registrar-empleado" data-page="registrar_empleado.php">
                <span data-feather="home"></span>
                Registrar Empleado
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" id="menu-ver-usuarios" data-page="ver_usuarios.php" data-action="getUsers">
                <span data-feather="file"></span>
                Ver Usuarios
            </a>
        </li>
        <?php elseif ($rolUsuario === 'empleado'): ?>
        <li class="nav-item">
            <a class="nav-link" href="#" id="menu-registrar-producto" data-page="registrar_producto.php" data-action="getCate_CrearProd">
                <span data-feather="plus-circle"></span>
                Registrar Producto
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" id="menu-listar-producto" data-page="ver_productos.php" data-action="getProductos">
                <span data-feather="list"></span>
                Listar Producto
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" id="menu-registrar-categoria" data-page="registrar_categoria.php">
                <span data-feather="plus-circle"></span>
                Registrar Categoría
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" id="menu-listar-categoria" data-page="ver_categorias.php" data-action="getCategorias">
                <span data-feather="list"></span>
                Listar Categoría
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" id="menu-registrar-marca" data-page="registrar_marca.php">
                <span data-feather="plus-circle"></span>
                Registrar Marca
            </a> 
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" id="menu-listar-marca" data-page="ver_marcas.php" data-action="getMarcas">
                <span data-feather="list"></span>
                Listar Marca
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" id="menu-registrar-descuentos" data-page="registrar_descuentos.php">
                <span data-feather="plus-circle"></span>
                Registrar Descuentos
            </a> 
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" id="menu-listar-descuentos" data-page="ver_descuentos.php" data-action="getDescuentos">
                <span data-feather="list"></span>
                Listar Descuentos
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" id="menu-listar-pedidos" data-page="ver_pedidos.php" data-action="getPedidos">
                <span data-feather="clipboard"></span>
                Listar Pedidos
            </a>
        </li>
        <?php endif; ?>
        </ul>
    </div>
</nav>

<script src="https://unpkg.com/feather-icons"></script>
<script>
    feather.replace();
</script>