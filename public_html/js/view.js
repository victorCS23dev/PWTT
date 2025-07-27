window.CONTROLADOR_USUARIOS_URL = '../controller/controlador_usuarios.php';
window.CONTROLADOR_CATEGORIAS_URL = '../controller/controlador_categorias.php';
window.CONTROLADOR_PRODUCTOS_URL = '../controller/controlador_productos.php';
window.CONTROLADOR_MARCAS_URL = '../controller/controlador_marcas.php';
window.CONTROLADOR_DESCUENTOS_URL = '../controller/controlador_descuentos.php';function listar_usuarios(url) {
    $.ajax({
        url: url,
        method: 'GET',
        data: {
            accion: 'listar_usuarios'
        },
        dataType: 'json',
        success: function (respuesta) {
            let tableHead = '';
            let usuariosData = [];             if (respuesta.status === 'success' && respuesta.data.length > 0) {
                usuariosData = respuesta.data; 
                
                const headerKeys = [
                    'id', 'google_id', 'dni', 'nombre', 'apellido', 'correo', 'telefono', 'direccion', 'rol',
                    'estado', 'acciones'
                ];                
                tableHead = '<tr>';
                headerKeys.forEach(key => {
                    let headerText = '';
                    switch (key) {
                        case 'id': headerText = 'ID'; break;
                        case 'google_id': headerText = 'Google ID'; break;
                        case 'dni': headerText = 'DNI'; break;
                        case 'nombre': headerText = 'Nombre'; break;
                        case 'apellido': headerText = 'Apellido'; break;
                        case 'correo': headerText = 'Correo'; break;
                        case 'telefono': headerText = 'Teléfono'; break;
                        case 'direccion': headerText = 'Dirección'; break;
                        case 'rol': headerText = 'Rol'; break;
                        case 'estado': headerText = 'Estado'; break;
                        case 'acciones': headerText = 'Acciones'; break;
                        default: headerText = key.charAt(0).toUpperCase() + key.slice(1).replace(/_/g, ' ');
                    }
                    tableHead += `<th>${headerText}</th>`;
                });
                tableHead += '</tr>';            } else {
                
                tableHead = `<tr>
                    <th>ID</th><th>Google ID</th><th>DNI</th><th>Nombre</th><th>Apellido</th><th>Correo</th>
                    <th>Teléfono</th><th>Dirección</th><th>Rol</th><th>Estado</th><th>Acciones</th>
                </tr>`;
                
                tableBody = '<tr><td colspan="11" class="text-center">No hay usuarios registrados.</td></tr>'; 
            }            
            const $tablaUsuarios = $('#tabla_usuarios');
            if ($.fn.DataTable.isDataTable($tablaUsuarios)) {
                $tablaUsuarios.DataTable().clear().destroy();
                $tablaUsuarios.find('thead').empty();
                $tablaUsuarios.find('tbody').empty(); 
            }            
            $tablaUsuarios.find('thead').html(tableHead);
            
            if (usuariosData.length > 0) {
                 $tablaUsuarios.find('tbody').empty(); 
            } else {
                $tablaUsuarios.find('tbody').html(tableBody); 
            }
            
            $tablaUsuarios.DataTable({
                data: usuariosData, 
                responsive: true,
                scrollY: '400px', 
                scrollX: true,
                scrollCollapse: true,
                autoWidth: false,
                pageLength: 10,
                ordering: true,
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
                },
                columns: [ 
                    { data: 'id' },
                    { data: 'google_id' },
                    { data: 'dni' },
                    { data: 'nombre' },
                    { data: 'apellido' },
                    { data: 'correo' },
                    { data: 'telefono' },
                    { data: 'direccion' },
                    { data: 'rol' },
                    {
                        data: 'estado',
                        render: function (data, type, row) {
                            const estadoText = data == 1 ? 'Activo' : 'Inactivo';
                            const estadoClass = data == 1 ? 'badge bg-success' : 'badge bg-danger';
                            return `<span class="${estadoClass}">${estadoText}</span>`;
                        }
                    },
                    {
                        data: null, 
                        render: function (data, type, row) {
                            const userId = row.idUsuarios || row.id;
                            return `<button class="btn btn-sm btn-info btn-edit-user" data-id="${userId}" data-bs-toggle="modal" data-bs-target="#editUserModal">Editar</button>
                                    <button class="btn btn-sm btn-danger btn-delete-user" data-id="${userId}">Eliminar</button>`;
                        },
                        orderable: false,
                        searchable: false
                    }
                ]
            });            
            
            $tablaUsuarios.off('click', '.btn-delete-user').on('click', '.btn-delete-user', function () {
                const idUsuario = $(this).data('id');
                if (confirm('¿Estás seguro de que deseas eliminar este usuario? Esta acción es irreversible.')) {
                    $.ajax({
                        url: CONTROLADOR_USUARIOS_URL,
                        method: 'POST',
                        dataType: 'json',
                        data: {
                            accion: 'eliminar_usuario',
                            id_usuario: idUsuario
                        },
                        success: function (respuesta) {
                            if (respuesta.status === 'success') {
                                alert('Usuario eliminado correctamente.');
                                
                                listar_usuarios(CONTROLADOR_USUARIOS_URL);
                            } else {
                                alert(respuesta.message || 'Error al eliminar el usuario.');
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("Error al eliminar usuario desde tabla:", error, xhr.responseText);
                            alert('Error al comunicarse con el servidor.');
                        }
                    });
                }
            });
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error al listar usuarios:", status, error);
            console.log("Respuesta completa:", xhr.responseText);
            alert('Error al obtener los usuarios.');
        }
    });
}function cargarCategorias(url) {
    const selectCategorias = $('#idCategorias_producto');
    selectCategorias.empty().append('<option value="">Seleccionar Categoría</option>');     $.ajax({
        url: url,
        method: 'GET', 
        dataType: 'json',
        data: {
            accion: 'listar_categorias_select' 
        },
        success: function (respuesta) {
            if (respuesta.status === 'success' && respuesta.data.length > 0) {
                const categorias = respuesta.data;
                $.each(categorias, function (index, categoria) {
                    selectCategorias.append(`<option value="${categoria.id}">${categoria.nombre}</option>`);
                });
            } else {
                console.log('respuesta.status: ',respuesta)
                console.log('No se encontraron categorías o hubo un error:', respuesta.message);
                selectCategorias.append('<option value="" disabled>No hay categorías disponibles</option>');
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error (Cargar Categorías):", status, error);
            console.log("Respuesta completa (Cargar Categorías):", xhr.responseText);
            selectCategorias.append('<option value="" disabled>Error al cargar categorías</option>');
        }
    });
}function cargarCategoriasSelect(url, selectElementId, selectedValue = null) {
    const select = $('#' + selectElementId);
    select.empty().append('<option value="">Seleccionar Categoría</option>');    $.ajax({
        url: url,
        method: 'GET',
        dataType: 'json',
        data: {
            accion: 'listar_categorias_select'
        },
        success: function (respuesta) {
            if (respuesta.status === 'success' && respuesta.data.length > 0) {
                $.each(respuesta.data, function (index, categoria) {
                    const option = `<option value="${categoria.id}">${categoria.nombre}</option>`;
                    select.append(option);
                });
                if (selectedValue !== null) {
                    console.log('view.js -> ',selectedValue);
                    select.val(selectedValue); 
                }
            } else {
                console.log('No se encontraron categorías o hubo un error:', respuesta.message);
                select.append('<option value="" disabled>No hay categorías disponibles</option>');
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error (Cargar Categorías Select):", status, error);
            select.append('<option value="" disabled>Error al cargar categorías</option>');
        }
    });
}function listar_productos(url) {
    const $tablaProductos = $('#tabla_productos');
    
    
    if ($.fn.DataTable.isDataTable($tablaProductos)) {
        $tablaProductos.DataTable().clear().destroy();
        
        
    }    
    $tablaProductos.find('tbody').html('<tr><td colspan="10" class="text-center">Cargando productos...</td></tr>');    $.ajax({
        url: url,
        method: 'GET',
        data: { accion: 'listar_productos' },
        dataType: 'json',
        success: function (respuesta) {
            let productosData = [];            if (respuesta.status === 'success' && respuesta.data.length > 0) {
                productosData = respuesta.data;
            } else {
                
            }
            
            
            $tablaProductos.DataTable({
                data: productosData, 
                responsive: true,
                scrollY: '400px',
                scrollX: true,
                scrollCollapse: true,
                autoWidth: false,
                pageLength: 10,
                ordering: true, 
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
                },
                columns: [ 
                    { data: 'idProductos' },
                    { data: 'producto_nombre' },
                    { data: 'marca' }, 
                    { data: 'descripcion' },
                    { data: 'precio' },
                    { data: 'stock' },
                    { 
                        data: 'estado',
                        render: function (data, type, row) {
                            const estadoText = data == 1 ? 'Activo' : 'Inactivo';
                            const estadoClass = data == 1 ? 'badge bg-success' : 'badge bg-danger';
                            return `<span class="${estadoClass}">${estadoText}</span>`;
                        }
                    },
                    { data: 'categoria_nombre' },
                    { 
                        data: 'imagen_url',
                        render: function (data, type, row) {
                            return `<img src="../img/productos/${data}" alt="${row.producto_nombre}" width="50" class="img-thumbnail rounded">`;
                        },
                        orderable: false, 
                        searchable: false 
                    },
                    { 
                        data: null, 
                        render: function (data, type, row) {
                            const productId = row.idProductos;
                            return `
                                <button class="btn btn-info btn-sm btn-edit-producto"
                                    data-id="${productId}"
                                    data-bs-toggle="modal" data-bs-target="#editProductoModal">
                                    Editar
                                </button>
                                <button class="btn btn-danger btn-sm btn-delete-producto"
                                    data-id="${productId}">
                                    Eliminar
                                </button>
                            `;
                        },
                        orderable: false,
                        searchable: false
                    }
                ]
            });            
            
            $tablaProductos.off('click', '.btn-delete-producto').on('click', '.btn-delete-producto', function() {
                const idProducto = $(this).data('id');
                if (confirm('¿Estás seguro de que deseas eliminar este producto? Esta acción es irreversible.')) {
                    $.ajax({
                        url: url, 
                        method: 'POST',
                        dataType: 'json',
                        data: {
                            accion: 'eliminar_producto',
                            id_producto: idProducto
                        },
                        success: function(respuesta) {
                            if (respuesta.status === 'success') {
                                alert('Producto eliminado correctamente.');
                                listar_productos(url); 
                            } else {
                                alert(respuesta.message || 'Error al eliminar el producto.');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error al eliminar producto:", error, xhr.responseText);
                            alert('Error al comunicarse con el servidor al eliminar el producto.');
                        }
                    });
                }
            });
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error (listar_productos):", status, error);
            console.log("Respuesta completa (listar_productos):", xhr.responseText);
            alert('Error al obtener los productos.');
        }
    });
}
function listar_categorias(url) {
    $.ajax({
        url: url,
        method: 'GET',
        data: {
            accion: 'listar_categorias'
        },
        dataType: 'json',
        success: function (respuesta) {
            let tableHead = '';
            let categoriasData = [];             if (respuesta.status === 'success' && respuesta.data.length > 0) {
                categoriasData = respuesta.data; 
                
                
                
                const headerKeys = [
                    'idCategorias', 'categoria_nombre', 'estado', 
                    'acciones' 
                ];                 
                tableHead = '<tr>';
                headerKeys.forEach(key => {
                    let headerText = '';
                    switch(key) {
                        case 'idCategorias': headerText = 'ID'; break;
                        case 'categoria_nombre': headerText = 'Nombre'; break;
                        case 'estado': headerText = 'Estado'; break;
                        case 'acciones': headerText = 'Acciones'; break;
                        default: headerText = key.charAt(0).toUpperCase() + key.slice(1).replace(/_/g, ' ');
                    }
                    tableHead += `<th>${headerText}</th>`;
                });
                tableHead += '</tr>';            } else {
                
                tableHead = '<tr><th>ID</th><th>Nombre</th><th>Estado</th><th>Acciones</th></tr>';
                
            }            
            const $tablaCategorias = $('#tabla_categorias');
            if ($.fn.DataTable.isDataTable($tablaCategorias)) {
                $tablaCategorias.DataTable().clear().destroy();
                $tablaCategorias.find('thead').empty(); 
                
            }            
            $tablaCategorias.find('thead').html(tableHead);
                        
            const dataTableInstance = $tablaCategorias.DataTable({
                data: categoriasData, 
                responsive: true,
                scrollY: '400px', 
                scrollX: true, 
                scrollCollapse: true,
                autoWidth: false,
                pageLength: 10,
                ordering: true,
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
                },
                columns: [ 
                    { data: 'idCategorias' },
                    { data: 'categoria_nombre' },
                    { data: 'estado', 
                        render: function (data, type, row) {
                            const estadoText = data == 1 ? 'Activo' : 'Inactivo';
                            const estadoClass = data == 1 ? 'badge bg-success' : 'badge bg-danger';
                            return `<span class="${estadoClass}">${estadoText}</span>`;
                        }
                    },
                    {   
                        data: null, 
                        render: function (data, type, row) {
                            const categoriaId = row.idCategorias || row.id; 
                            return `
                                <button class="btn btn-sm btn-info btn-edit-categoria" data-id="${categoriaId}" data-bs-toggle="modal" data-bs-target="#editCategoriaModal">Editar</button>
                                <button class="btn btn-sm btn-danger btn-delete-categoria" data-id="${categoriaId}">Eliminar</button>
                            `;
                        }
                    }
                ]
            });            
            
            
            $tablaCategorias.off('click', '.btn-delete-categoria').on('click', '.btn-delete-categoria', function() {
                const idCategoria = $(this).data('id');
                if (confirm('¿Estás seguro de que deseas eliminar esta categoría? Esta acción es irreversible.')) {
                    $.ajax({
                        url: CONTROLADOR_CATEGORIAS_URL,
                        method: 'POST', 
                        dataType: 'json',
                        data: {
                            accion: 'eliminar_categoria', 
                            id_categoria: idCategoria
                        },
                        success: function(respuesta) {
                            if (respuesta.status === 'success') {
                                alert('Categoría eliminada correctamente.');
                                
                                listar_categorias(CONTROLADOR_CATEGORIAS_URL); 
                            } else {
                                alert(respuesta.message || 'Error al eliminar la categoría.');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error al eliminar categoría desde tabla:", error, xhr.responseText);
                            alert('Error al comunicarse con el servidor.');
                        }
                    });
                }
            });
            
        }, 
        error: function (xhr, status, error) {
            console.error("AJAX Error al listar categorías:", status, error);
            console.log("Respuesta completa:", xhr.responseText);
            alert('Error al obtener las categorías.');
        }
    });
}function listar_marcas(url) {
    $.ajax({
        url: url,
        method: 'GET',
        data: {
            accion: 'listar_marcas' 
        },
        dataType: 'json',
        success: function (respuesta) {
            let tableHead = '';
            let marcasData = [];             if (respuesta.status === 'success' && respuesta.data.length > 0) {
                marcasData = respuesta.data; 
                
                
                
                const headerKeys = [
                    'idMarcas', 'marca_nombre', 'estado', 
                    'acciones' 
                ];                 
                tableHead = '<tr>';
                headerKeys.forEach(key => {
                    let headerText = '';
                    switch(key) {
                        case 'idMarcas': headerText = 'ID'; break;
                        case 'marca_nombre': headerText = 'Nombre'; break;
                        case 'estado': headerText = 'Estado'; break;
                        case 'acciones': headerText = 'Acciones'; break;
                        default: headerText = key.charAt(0).toUpperCase() + key.slice(1).replace(/_/g, ' ');
                    }
                    tableHead += `<th>${headerText}</th>`;
                });
                tableHead += '</tr>';            } else {
                
                tableHead = '<tr><th>ID</th><th>Nombre</th><th>Estado</th><th>Acciones</th></tr>';
                
            }            
            const $tablaMarcas = $('#tabla_marcas');
            if ($.fn.DataTable.isDataTable($tablaMarcas)) {
                $tablaMarcas.DataTable().clear().destroy();
                $tablaMarcas.find('thead').empty(); 
            }            
            $tablaMarcas.find('thead').html(tableHead);            
            const dataTableInstance = $tablaMarcas.DataTable({
                data: marcasData, 
                responsive: true,
                scrollY: '400px', 
                scrollX: true, 
                scrollCollapse: true,
                autoWidth: false,
                pageLength: 10,
                ordering: true,
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
                },
                columns: [ 
                    { data: 'id' },
                    { data: 'nombre_marca' },
                    { data: 'estado', 
                        render: function (data, type, row) {
                            const estadoText = data == 1 ? 'Activo' : 'Inactivo';
                            const estadoClass = data == 1 ? 'badge bg-success' : 'badge bg-danger';
                            return `<span class="${estadoClass}">${estadoText}</span>`;
                        }
                    },
                    {   
                        data: null, 
                        render: function (data, type, row) {
                            const marcaId = row.idMarcas || row.id; 
                            return `
                                <button class="btn btn-sm btn-info btn-edit-marca" data-id="${marcaId}" data-bs-toggle="modal" data-bs-target="#editMarcaModal">Editar</button>
                                <button class="btn btn-sm btn-danger btn-delete-marca" data-id="${marcaId}">Eliminar</button>
                            `;
                        }
                    }
                ]
            });            
            
            $tablaMarcas.off('click', '.btn-delete-marca').on('click', '.btn-delete-marca', function() {
                const idMarca = $(this).data('id');
                if (confirm('¿Estás seguro de que deseas eliminar esta marca? Esta acción es irreversible.')) {
                    $.ajax({
                        url: CONTROLADOR_MARCAS_URL,
                        method: 'POST', 
                        dataType: 'json',
                        data: {
                            accion: 'eliminar_marca', 
                            id_marca: idMarca
                        },
                        success: function(respuesta) {
                            if (respuesta.status === 'success') {
                                alert('Marca eliminada correctamente.');
                                
                                listar_marcas(CONTROLADOR_MARCAS_URL); 
                            } else {
                                alert(respuesta.message || 'Error al eliminar la marca.');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error al eliminar marca desde tabla:", error, xhr.responseText);
                            alert('Error al comunicarse con el servidor.');
                        }
                    });
                }
            });
            
            
                    }, 
        error: function (xhr, status, error) {
            console.error("AJAX Error al listar marcas:", status, error);
            console.log("Respuesta completa:", xhr.responseText);
            alert('Error al obtener las marcas.');
        }
    });
}function listar_descuentos(url) {
    const $tablaDescuentos = $('#tabla_descuentos');
    if ($.fn.DataTable.isDataTable($tablaDescuentos)) {
        $tablaDescuentos.DataTable().clear().destroy();
    }    $tablaDescuentos.find('tbody').html('<tr><td colspan="10" class="text-center">Cargando códigos de descuento...</td></tr>');    $.ajax({
        url: url,
        method: 'GET',
        dataType: 'json',
        data: { accion: 'listar_descuentos' },
        success: function(response) {
            if (response.status === 'success' && response.data) {
                const descuentos = response.data;
                const dataTableData = descuentos.map(descuento => {
                    return {
                        idCodigo: descuento.idCodigo,
                        codigo: descuento.codigo,
                        valor_descuento: parseFloat(descuento.valor_descuento).toFixed(2) + '%',
                        categoria_nombre: descuento.aplica_a_categoria ? descuento.categoria_nombre : 'Todas',
                        marca_nombre: descuento.aplica_a_marca ? descuento.marca_nombre : 'Todas',
                        descripcion: descuento.descripcion || 'N/A',
                        fecha_inicio: descuento.fecha_inicio ? new Date(descuento.fecha_inicio).toLocaleString() : 'N/A',
                        fecha_fin: descuento.fecha_fin ? new Date(descuento.fecha_fin).toLocaleString() : 'N/A',
                        estado: descuento.estado == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>',
                        acciones: `
                            <button class="btn btn-info btn-sm btn-edit-descuento" data-id="${descuento.idCodigo}" data-bs-toggle="modal" data-bs-target="#editDescuentoModal">Editar</button>
                            <button class="btn btn-danger btn-sm btn-delete-descuento" data-id="${descuento.idCodigo}">Eliminar</button>
                        `
                    };
                });                $tablaDescuentos.DataTable({
                    data: dataTableData,
                    responsive: true,
                    scrollY: '400px',
                    scrollX: true,
                    scrollCollapse: true,
                    autoWidth: false,
                    pageLength: 10,
                    ordering: true,
                    language: {
                        url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
                    },
                    columns: [
                        { data: 'idCodigo' },
                        { data: 'codigo' },
                        { data: 'valor_descuento' },
                        { data: 'categoria_nombre' },
                        { data: 'marca_nombre' },
                        { data: 'descripcion' },
                        { data: 'fecha_inicio' },
                        { data: 'fecha_fin' },
                        { data: 'estado' },
                        { data: 'acciones', orderable: false, searchable: false }
                    ]
                });                
                $tablaDescuentos.off('click', '.btn-delete-descuento').on('click', '.btn-delete-descuento', function() {
                    const idCodigo = $(this).data('id');
                    if (confirm('¿Estás seguro de que deseas eliminar este código de descuento? Esta acción no se puede deshacer.')) {
                        const formData = new FormData();
                        formData.append('accion', 'eliminar_descuento');
                        formData.append('id_codigo', idCodigo);                        $.ajax({
                            url: CONTROLADOR_DESCUENTOS_URL,
                            method: 'POST',
                            dataType: 'json',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                if (response.status === 'success') {
                                    alert(response.message);
                                    listar_descuentos(CONTROLADOR_DESCUENTOS_URL); 
                                } else {
                                    alert(response.message);
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Error AJAX al eliminar descuento:', status, error, xhr.responseText);
                                alert('Error de red al eliminar el descuento.');
                            }
                        });
                    }
                });            } else {
                $tablaDescuentos.find('tbody').html('<tr><td colspan="10" class="text-center">No hay códigos de descuento registrados.</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX al cargar descuentos:', status, error, xhr.responseText);
            $tablaDescuentos.find('tbody').html('<tr><td colspan="10" class="text-center text-danger">Error al cargar los códigos de descuento.</td></tr>');
        }
    });
}function htmlspecialchars(str) {
    if (typeof str !== 'string') {
        str = String(str);
    }
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return str.replace(/[&<>"']/g, function(m) { return map[m]; });
}function getEstadoText(estadoNum) {
    switch (parseInt(estadoNum)) {
        case 0: return 'Cancelado';
        case 1: return 'Pendiente';
        case 2: return 'En preparación';
        case 3: return 'En camino';
        case 4: return 'Entregado';
        case 5: return 'Devuelto';
        case 6: return 'Solicitud de Cancelación';
        default: return 'Desconocido';
    }
}function getEstadoBadgeClass(estadoNum) {
    switch (parseInt(estadoNum)) {
        case 0: return 'bg-danger';      
        case 1: return 'bg-warning text-dark'; 
        case 2: return 'bg-info text-dark';    
        case 3: return 'bg-primary';   
        case 4: return 'bg-success';   
        case 5: return 'bg-secondary'; 
        case 6: return 'bg-warning text-dark'; 
        default: return 'bg-light text-dark'; 
    }
}function listar_facturas(url) {
    const $tablaPedidosAdmin = $('#tablaPedidosAdmin');
    const $tbody = $tablaPedidosAdmin.find('tbody');
    const $statusMessageDiv = $('#pedidos-status-message');    
    if ($.fn.DataTable.isDataTable($tablaPedidosAdmin)) {
        $tablaPedidosAdmin.DataTable().clear().destroy();
    }    $tbody.html('<tr><td colspan="8" class="text-center">Cargando pedidos...</td></tr>');
    $statusMessageDiv.html('<div class="alert alert-info">Obteniendo todos los pedidos...</div>');    $.ajax({
        url: url,
        method: 'GET',
        dataType: 'json',
        data: { accion: 'listar_todas_las_facturas' },
        success: function(response) {
            $tbody.empty();
            $statusMessageDiv.empty();            if (response.status === 'error') {
                $statusMessageDiv.html(`<div class="alert alert-danger">Error: ${response.message}</div>`);
                $tbody.html('<tr><td colspan="8" class="text-center">No se pudieron cargar los pedidos.</td></tr>');
                return;
            }            const facturasAgrupadas = {};
            if (response.data) {
                
                response.data.forEach(item => {
                    if (!facturasAgrupadas[item.idFactura]) {
                        facturasAgrupadas[item.idFactura] = {
                            idFactura: item.idFactura,
                            fecha: item.fecha,
                            metodo_pago: item.metodo_pago,
                            monto_total: item.monto_total,
                            estado: item.estado,
                            monto_descuento: item.monto_descuento,
                            codigo_descuento_aplicado: item.codigo_descuento_aplicado,
                            nombre_cliente: item.nombre_cliente,
                            apellido_cliente: item.apellido_cliente,
                            correo_cliente: item.correo_cliente,
                            dni_cliente: item.dni_cliente,
                            productos: [] 
                        };
                    }
                    
                });
            }            const dataTableData = [];
            if (Object.keys(facturasAgrupadas).length > 0) {
                Object.values(facturasAgrupadas).forEach(factura => {
                    const estadoText = getEstadoText(factura.estado);
                    const estadoBadgeClass = getEstadoBadgeClass(factura.estado);                    dataTableData.push({
                        idFactura: htmlspecialchars(String(factura.idFactura)),
                        fecha: htmlspecialchars(factura.fecha),
                        cliente: `${htmlspecialchars(factura.nombre_cliente)} ${htmlspecialchars(factura.apellido_cliente)}`,
                        correo_cliente: htmlspecialchars(factura.correo_cliente),
                        metodo_pago: htmlspecialchars(factura.metodo_pago),
                        monto_total: `S/${parseFloat(factura.monto_total).toFixed(2)}`,
                        estado: `<span class="badge rounded-pill ${estadoBadgeClass}">${estadoText}</span>`,
                        acciones: `
                            <button class="btn btn-sm btn-outline-primary me-1 btn-ver-detalle-admin" data-idfactura="${factura.idFactura}">Ver Detalle</button>
                            <button class="btn btn-sm btn-outline-info btn-descargar-boleta-admin" data-idfactura="${factura.idFactura}">Descargar Boleta</button>
                        `
                    });
                });                $tablaPedidosAdmin.DataTable({
                    data: dataTableData,
                    responsive: true,
                    scrollY: '400px',
                    scrollX: true,
                    scrollCollapse: true,
                    autoWidth: false,
                    pageLength: 10,
                    ordering: true,
                    language: {
                        url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
                    },
                    columns: [
                        { data: 'idFactura' },
                        { data: 'fecha' },
                        { data: 'cliente' },
                        { data: 'correo_cliente' },
                        { data: 'metodo_pago' },
                        { data: 'monto_total' },
                        { data: 'estado' },
                        { data: 'acciones', orderable: false, searchable: false }
                    ]
                });                
                $tablaPedidosAdmin.off('click', '.btn-ver-detalle-admin').on('click', '.btn-ver-detalle-admin', function() {
                    const idFactura = $(this).data('idfactura');
                    if (typeof window.verDetalleAdmin === 'function') {
                        window.verDetalleAdmin(idFactura);
                    } else {
                        console.error("La función 'verDetalleAdmin' no está definida globalmente.");
                    }
                });                $tablaPedidosAdmin.off('click', '.btn-descargar-boleta-admin').on('click', '.btn-descargar-boleta-admin', function() {
                    const idFactura = $(this).data('idfactura');
                    if (typeof window.descargarBoletaAdmin === 'function') {
                        window.descargarBoletaAdmin(idFactura);
                    } else {
                        console.error("La función 'descargarBoletaAdmin' no está definida globalmente.");
                    }
                });            } else {
                $tbody.html('<tr><td colspan="8" class="text-center">No hay pedidos registrados.</td></tr>');
                $statusMessageDiv.html('<div class="alert alert-info">No hay pedidos disponibles.</div>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX al cargar pedidos:', status, error, xhr.responseText);
            $statusMessageDiv.html('<div class="alert alert-danger">Error al cargar pedidos. Por favor, inténtalo de nuevo.</div>');
            $tbody.html('<tr><td colspan="8" class="text-center">Error al cargar los pedidos.</td></tr>');
        }
    });
};
window.listar_categorias = listar_categorias;
window.listar_productos = listar_productos;
window.listar_usuarios = listar_usuarios;
window.listar_marcas = listar_marcas;
window.listar_descuentos = listar_descuentos;
window.listar_facturas = listar_facturas;
