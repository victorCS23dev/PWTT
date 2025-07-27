$(document).ready(function() {
    var urlControladorUsuario = "../controller/controlador_usuarios.php";
    var urlControladorCategorias = "../controller/controlador_categorias.php";
    var urlControladorProductos = "../controller/controlador_productos.php";
    var urlControladorMarcas = "../controller/controlador_marcas.php";
    var urlControladorDescuentos = "../controller/controlador_descuentos.php";
    var urlControladorFacturas = "../controller/controlador_facturas.php";    
    const savedMenu = localStorage.getItem('selectedMenu');
    
    const userType = $('body').data('user-type'); 
    
    if (savedMenu) {
        const $savedMenuEl = $(`#${savedMenu}`);
        if ($savedMenuEl.length > 0) {
            $savedMenuEl.addClass('active');
            const page = $savedMenuEl.data('page');
            if (page) {
                $('#contenido').load(page, function() {
                    initializePageEvents();
                });
            } else {
                console.warn(`El atributo data-page no existe en #${savedMenu}.`);
            }
        } else {
            console.warn(`No se encontró ningún elemento con id=${savedMenu}.`);
        }
    }    
    function initializePageEvents() {
        
        manageUIVisibility(userType);         $('#bt_actualizar_usuarios').off('click').on('click', function(e) {
            e.preventDefault();
            if (typeof listar_usuarios === 'function') {
                listar_usuarios(urlControladorUsuario);
            } else {
                console.error("La función 'listar_usuarios' no está definida.");
            }
        });        $('#bt_actualizar_productos').off('click').on('click', function(e) {
            e.preventDefault();
            if (typeof listar_productos === 'function') {
                listar_productos(urlControladorProductos);
            } else {
                console.error("La función 'listar_productos' no está definida.");
            }
        });        $('#bt_actualizar_categorias').off('click').on('click', function(e) {
            e.preventDefault();
            if (typeof listar_categorias === 'function') {
                listar_categorias(urlControladorCategorias);
            } else {
                console.error("La función 'listar_categorias' no está definida.");
            }
        });        $('#bt_actualizar_marcas').off('click').on('click', function(e) {
            e.preventDefault();
            if (typeof listar_marcas === 'function') {
                listar_marcas(urlControladorMarcas);
            } else {
                console.error("La función 'listar_marcas' no está definida.");
            }
        });        $('#bt_actualizar_descuentos').off('click').on('click', function(e) {
            e.preventDefault();
            if (typeof listar_descuentos === 'function') {
                listar_descuentos(urlControladorDescuentos);
            } else {
                console.error("La función 'listar_descuentos' no está definida.");
            }
        });        $('#bt_actualizar_pedidos').off('click').on('click', function(e) {
            e.preventDefault();
            if (typeof listar_facturas === 'function') {
                listar_facturas(urlControladorFacturas);
            } else {
                console.error("La función 'listar_facturas' no está definida.");
            }
        });
    }    
    $('#menu a').on('click', function (e) {
        e.preventDefault(); 
        const page = $(this).data('page'); 
        const menuId = $(this).attr('id'); 
        const action = $(this).data('action');
        const fullPath = 'main/view/' + page;         
        localStorage.setItem('selectedMenu', menuId);        
        $('#contenido').load(fullPath, function (response, status) {
            if (status === "error") {
                $('#contenido').html('<p>Error al cargar el contenido. Por favor, inténtalo más tarde.</p>');
            } else {
                
                initializePageEvents();                if (action === "getUsers") {
                    if (typeof listar_usuarios === 'function') {
                        listar_usuarios(urlControladorUsuario);
                    }
                }
                if (action === "getCate_CrearProd") {
                    
                    
                    if (typeof cargarCategorias === 'function') {
                        cargarCategorias(urlControladorCategorias);
                    }
                }
                if (action === "getProductos") {
                    if (typeof listar_productos === 'function') {
                        listar_productos(urlControladorProductos);
                    }
                }
                if (action === "getCategorias") {
                    if (typeof listar_categorias === 'function') {
                        listar_categorias(urlControladorCategorias);
                    }
                }
                if (action === "getMarcas") {
                    if (typeof listar_marcas === 'function') {
                        listar_marcas(urlControladorMarcas);
                    }
                }
                if (action === "getDescuentos") {
                    if (typeof listar_descuentos === 'function') {
                        listar_descuentos(urlControladorDescuentos);
                    }
                }
                if (action === "getPedidos") {
                    if (typeof listar_facturas === 'function') {
                        listar_facturas(urlControladorFacturas);
                    }
                }
            }
        });        
        $('#menu a').removeClass('active');
        $(this).addClass('active');
    });    
    
    if (!savedMenu) {
        
        
        
        
        
        
        
        
    }
});
