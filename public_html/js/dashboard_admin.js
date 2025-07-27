$(document).ready(function() {
    const CONTROLADOR_PRODUCTOS_URL = '../controller/controlador_productos.php';
    const CONTROLADOR_FACTURAS_URL = '../controller/controlador_facturas.php';
    const CONTROLADOR_USUARIOS_URL = '../controller/controlador_usuarios.php'; 

    let mostSoldProductsChartInstance = null; 
    let orderStatusChartInstance = null; 

    function showDashboardMessage(containerId, message, type = 'info') {
        const container = $(`#${containerId}`);
        container.html(`<div class="alert alert-${type}">${message}</div>`);
        setTimeout(() => {
            container.empty();
        }, 5000);
    }
    
    function loadMostSoldProducts() {
        $('#loading-most-sold-products').show(); 
        $('#mostSoldProductsChart').hide(); 
        $('#most-sold-products-message').empty(); 

        $.ajax({
            url: CONTROLADOR_PRODUCTOS_URL,
            method: 'GET',
            dataType: 'json',
            data: { accion: 'listar_productos_mas_vendidos' },
            success: function(response) {
                $('#loading-most-sold-products').hide(); 

                if (response.status === 'success' && response.data && response.data.length > 0) {
                    $('#mostSoldProductsChart').show(); 

                    const productNames = response.data.map(item => item.nombre_producto);
                    const salesCounts = response.data.map(item => item.cantidad_vendida);
                    
                    if (mostSoldProductsChartInstance) {
                        mostSoldProductsChartInstance.destroy();
                    }

                    const ctx = document.getElementById('mostSoldProductsChart').getContext('2d');
                    mostSoldProductsChartInstance = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: productNames,
                            datasets: [{
                                label: 'Cantidad Vendida',
                                data: salesCounts,
                                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Unidades Vendidas'
                                    },
                                    ticks: {
                                        stepSize: 1 
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Producto'
                                    }
                                }
                            },
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Top 10 Productos Más Vendidos'
                                },
                                legend: {
                                    display: false
                                }
                            }
                        }
                    });
                } else {
                    $('#mostSoldProductsChart').hide(); 
                    showDashboardMessage('most-sold-products-message', response.message || 'No se encontraron datos de productos más vendidos.', 'info');
                }
            },
            error: function(xhr, status, error) {
                $('#loading-most-sold-products').hide(); 
                $('#mostSoldProductsChart').hide(); 
                console.error('Error AJAX al cargar productos más vendidos:', status, error, xhr.responseText);
                showDashboardMessage('most-sold-products-message', 'Error al cargar los productos más vendidos. Inténtalo de nuevo más tarde.', 'danger');
            }
        });
    }

    function loadOrderStatusSummary() {
        $('#loading-order-status').show(); 
        $('#orderStatusChart').hide(); 
        $('#order-status-message').empty(); 

        $.ajax({
            url: CONTROLADOR_FACTURAS_URL,
            method: 'GET',
            dataType: 'json',
            data: { accion: 'listar_todas_las_facturas' }, 
            success: function(response) {
                $('#loading-order-status').hide(); 

                if (response.status === 'success' && response.data && response.data.length > 0) {
                    $('#orderStatusChart').show(); 

                    const orderStatusCounts = {
                        'Cancelado': 0,
                        'Pendiente': 0,
                        'En preparación': 0,
                        'En camino': 0,
                        'Entregado': 0,
                        'Devuelto': 0,
                        'Solicitud de Cancelación': 0,
                        'Desconocido': 0
                    };
                    
                    function getEstadoText(estadoNum) {
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
                    }

                    const uniqueFacturas = {};
                    response.data.forEach(item => {
                        
                        if (!uniqueFacturas[item.idFactura]) {
                            uniqueFacturas[item.idFactura] = true; 
                            const estadoText = getEstadoText(item.estado);
                            orderStatusCounts[estadoText]++;
                        }
                    });
                    
                    const labels = Object.keys(orderStatusCounts);
                    const data = Object.values(orderStatusCounts);
                    const backgroundColors = [
                        'rgba(220, 53, 69, 0.7)',  
                        'rgba(255, 193, 7, 0.7)',  
                        'rgba(23, 162, 184, 0.7)', 
                        'rgba(0, 123, 255, 0.7)',  
                        'rgba(40, 167, 69, 0.7)',  
                        'rgba(108, 117, 125, 0.7)',
                        'rgba(255, 205, 86, 0.7)' 
                    ];
                    const borderColors = [
                        'rgba(220, 53, 69, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(23, 162, 184, 1)',
                        'rgba(0, 123, 255, 1)',
                        'rgba(40, 167, 69, 1)',
                        'rgba(108, 117, 125, 1)',
                        'rgba(255, 205, 86, 1)'
                    ];

                    if (orderStatusChartInstance) {
                        orderStatusChartInstance.destroy();
                    }

                    const ctx = document.getElementById('orderStatusChart').getContext('2d');
                    orderStatusChartInstance = new Chart(ctx, {
                        type: 'pie', 
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Número de Pedidos',
                                data: data,
                                backgroundColor: backgroundColors,
                                borderColor: borderColors,
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Distribución de Pedidos por Estado'
                                },
                                legend: {
                                    position: 'right', 
                                }
                            }
                        }
                    });

                } else {
                    $('#orderStatusChart').hide();
                    showDashboardMessage('order-status-message', response.message || 'No se encontraron datos de pedidos para el resumen.', 'info');
                }
            },
            error: function(xhr, status, error) {
                $('#loading-order-status').hide();
                $('#orderStatusChart').hide();
                console.error('Error AJAX al cargar resumen de pedidos:', status, error, xhr.responseText);
                showDashboardMessage('order-status-message', 'Error al cargar el resumen de pedidos. Inténtalo de nuevo más tarde.', 'danger');
            }
        });
    }
    
    function loadKeyMetrics() {
        $('#metric-status-message').html('<div class="alert alert-info py-1">Cargando métricas...</div>');

        $.ajax({
            url: CONTROLADOR_FACTURAS_URL,
            method: 'GET',
            dataType: 'json',
            data: { accion: 'obtener_ventas_totales_mes' }, 
            success: function(response) {
                if (response.status === 'success' && typeof response.total_ventas !== 'undefined') {
                    $('#metric-total-sales').text(`S/ ${parseFloat(response.total_ventas).toFixed(2)}`);
                } else {
                    $('#metric-total-sales').text('N/A');
                    console.warn('Error o datos incompletos para ventas totales:', response.message || '');
                }
            },
            error: function(xhr, status, error) {
                $('#metric-total-sales').text('Error');
                console.error('Error AJAX al cargar ventas totales:', status, error, xhr.responseText);
            }
        });
        
        $.ajax({
            url: CONTROLADOR_FACTURAS_URL,
            method: 'GET',
            dataType: 'json',
            data: { accion: 'obtener_pedidos_pendientes_count' }, 
            success: function(response) {
                if (response.status === 'success' && typeof response.count !== 'undefined') {
                    $('#metric-pending-orders').text(response.count);
                } else {
                    $('#metric-pending-orders').text('N/A');
                    console.warn('Error o datos incompletos para pedidos pendientes:', response.message || '');
                }
            },
            error: function(xhr, status, error) {
                $('#metric-pending-orders').text('Error');
                console.error('Error AJAX al cargar pedidos pendientes:', status, error, xhr.responseText);
            }
        });
        
        $.ajax({
            url: CONTROLADOR_USUARIOS_URL,
            method: 'GET',
            dataType: 'json',
            data: { accion: 'obtener_nuevos_usuarios_7dias_count' }, 
            success: function(response) {
                if (response.status === 'success' && typeof response.count !== 'undefined') {
                    $('#metric-new-users').text(response.count);
                } else {
                    $('#metric-new-users').text('N/A');
                    console.warn('Error o datos incompletos para nuevos usuarios:', response.message || '');
                }
            },
            error: function(xhr, status, error) {
                $('#metric-new-users').text('Error');
                console.error('Error AJAX al cargar nuevos usuarios:', status, error, xhr.responseText);
            }
        });

        $.ajax({
            url: CONTROLADOR_PRODUCTOS_URL,
            method: 'GET',
            dataType: 'json',
            data: { accion: 'obtener_productos_bajo_stock_count', umbral: 5 }, 
            success: function(response) {
                if (response.status === 'success' && typeof response.count !== 'undefined') {
                    $('#metric-low-stock-products').text(response.count);
                } else {
                    $('#metric-low-stock-products').text('N/A');
                    console.warn('Error o datos incompletos para productos bajo stock:', response.message || '');
                }
            },
            error: function(xhr, status, error) {
                $('#metric-low-stock-products').text('Error');
                console.error('Error AJAX al cargar productos bajo stock:', status, error, xhr.responseText);
            }
        }).always(function() {
            
            $('#metric-status-message').empty();
        });
    }
    
    const $reportStartDate = $('#report-start-date');
    const $reportEndDate = $('#report-end-date');
    const $generateReportBtn = $('#generate-sales-report-btn');
    const $downloadReportPdfBtn = $('#download-sales-report-pdf-btn');
    const $loadingSalesReport = $('#loading-sales-report');
    const $salesReportContent = $('#sales-report-content');
    const $salesReportMessage = $('#sales-report-message');

    function showSalesReportMessage(message, type = 'info') {
        $salesReportMessage.html(`<div class="alert alert-${type}">${message}</div>`);
        setTimeout(() => $salesReportMessage.empty(), 5000);
    }
    
    $generateReportBtn.on('click', function() {
        const startDate = $reportStartDate.val();
        const endDate = $reportEndDate.val();

        if (!startDate || !endDate) {
            showSalesReportMessage('Por favor, selecciona tanto la fecha de inicio como la de fin.', 'warning');
            return;
        }
        if (new Date(startDate) > new Date(endDate)) {
            showSalesReportMessage('La fecha de inicio no puede ser posterior a la fecha de fin.', 'warning');
            return;
        }
        generateSalesReport(startDate, endDate);
    });
    
    function generateSalesReport(startDate, endDate) {
        $loadingSalesReport.show(); 
        $salesReportContent.empty(); 
        $downloadReportPdfBtn.hide(); 

        $.ajax({
            url: CONTROLADOR_FACTURAS_URL,
            method: 'GET',
            dataType: 'json',
            data: { 
                accion: 'obtener_ventas_por_rango_fechas', 
                fecha_inicio: startDate,
                fecha_fin: endDate
            },
            success: function(response) {
                $loadingSalesReport.hide(); 

                if (response.status === 'success' && response.data && response.data.length > 0) {
                    renderSalesReport(response.data, startDate, endDate);
                    $downloadReportPdfBtn.show(); 
                } else {
                    $salesReportContent.html('<p class="text-muted text-center">No se encontraron ventas para el rango de fechas seleccionado.</p>');
                    showSalesReportMessage(response.message || 'No se encontraron datos de ventas.', 'info');
                }
            },
            error: function(xhr, status, error) {
                $loadingSalesReport.hide(); 
                console.error('Error AJAX al generar reporte de ventas:', status, error, xhr.responseText);
                showSalesReportMessage('Error al generar el reporte de ventas. Inténtalo de nuevo más tarde.', 'danger');
            }
        });
    }

    function renderSalesReport(data, startDate, endDate) {
        let reportHtml = `
            <h5>Reporte de Ventas desde ${startDate} hasta ${endDate}</h5>
            <table class="table table-striped table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th>ID Factura</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Método Pago</th>
                        <th>Monto Total (S/)</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
        `;
        let totalSalesAmount = 0;
        let totalOrders = 0;

        data.forEach(item => {
            const estadoText = (function(estadoNum) {
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
            })(item.estado);

            reportHtml += `
                <tr>
                    <td>${item.idFactura}</td>
                    <td>${item.fecha}</td>
                    <td>${item.nombre_cliente} ${item.apellido_cliente}</td>
                    <td>${item.metodo_pago}</td>
                    <td>S/${parseFloat(item.monto_total).toFixed(2)}</td>
                    <td>${estadoText}</td>
                </tr>
            `;
            if (item.estado >= 1 && item.estado <= 4) {
                totalSalesAmount += parseFloat(item.monto_total);
            }
            totalOrders++;
        });

        reportHtml += `
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">Total de Ventas:</th>
                        <th class="text-success">S/${totalSalesAmount.toFixed(2)}</th>
                        <th></th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-end">Total de Pedidos:</th>
                        <th>${totalOrders}</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        `;
        $salesReportContent.html(reportHtml);
    }

    $downloadReportPdfBtn.on('click', function() {
        const startDate = $reportStartDate.val();
        const endDate = $reportEndDate.val();
        if (!startDate || !endDate) {
            showSalesReportMessage('Por favor, selecciona las fechas para descargar el PDF.', 'warning');
            return;
        }
        downloadSalesReportPdf(startDate, endDate);
    });
    
    function downloadSalesReportPdf(startDate, endDate) {
        const pdfUrl = `${CONTROLADOR_FACTURAS_URL}?accion=descargar_reporte_ventas_pdf&fecha_inicio=${startDate}&fecha_fin=${endDate}`;
        window.open(pdfUrl, '_blank');
        showSalesReportMessage('Generando y descargando el reporte PDF...', 'info');
    }
    
    loadMostSoldProducts();
    loadOrderStatusSummary();
    loadKeyMetrics();
});
