<?php

// Comprobación más robusta si el usuario está logueado
$is_logged_in = isset($_SESSION['id_usuario']); 
$user_role = $_SESSION['rol'] ?? '';

// Define las rutas de los logos
$logo_admin_empleado = '../img/CRMlogo.png'; // Ruta para administradores y empleados
$logo_cliente = '../img/TiendaLogo.png';   // Ruta para clientes

// Decide qué logo mostrar
$current_logo = ($user_role === 'cliente' || !$is_logged_in) ? $logo_cliente : $logo_admin_empleado;

// Detectar si la página actual es login.php
$current_script_name = basename($_SERVER['PHP_SELF']);
$is_login_page = ($current_script_name === 'login.php');

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PWTT - Tienda Tecnologica</title>

    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- DataTables CSS para integración con Bootstrap 5 -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css"/>
    
    <!-- DataTables JS core -->
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script> 


    <style>
        .navbar-brand img {
            max-height: 80px; /* Altura máxima del logo en la barra de navegación */
            width: auto;     /* Mantiene la proporción de la imagen */
        }
    </style>
</head>
<body>
    <?php if ($is_login_page): ?>
        <!-- Navbar simplificada para la página de login -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="../index.php">
                    <img src="<?php echo $logo_cliente; ?>" alt="logo" class="img-fluid"> <!-- Siempre el logo de cliente para login -->
                </a>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary text-white" href="../index.php?guest=true" id="guestLogin">Ingresar como Invitado</a>
                    </li>
                </ul>
            </div>
        </nav>
    <?php else: ?>
    <!-- Navbar completa para todas las demás páginas (catálogo, perfil, dashboards) -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="index.php">
                    <img src="<?php echo $current_logo; ?>" alt="logo" class="img-fluid">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <?php if($user_role === 'cliente' || !$is_logged_in): // Mostrar barra de búsqueda solo para clientes y no logueados ?>
                        <form id="searchForm" class="d-flex mx-auto my-2 my-lg-0" role="search" style="max-width: 500px;">
                            <input class="form-control me-2" type="search" placeholder="Buscar productos..." aria-label="Search" name="search_query" id="searchInput">
                            <button class="btn btn-outline-success" type="submit" id="searchButton">Buscar</button>
                        </form>
                    <?php endif; ?>
                    <ul class="navbar-nav ms-auto">
                    <?php if($is_logged_in):?>
                        <?php if ($user_role === 'cliente'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="?page=view/historial_compras.php" id="historial">Mis compras</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?page=view/carrito.php" id="carrito">Mi carrito</a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="?page=view/perfil.php" id="perfil">Mi Perfil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../controller/logout.php" id="logout">Logout</a>
                        </li>
                        <?php else: // Si no está logueado, mostrar "Login" ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/main/login.php" id="login">Login</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    <?php endif; ?>