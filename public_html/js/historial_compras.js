document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.querySelector('#tablaHistorial tbody');
    const statusMessageDiv = document.querySelector('#historial-status-message');
    
    const detalleFacturaModal = new bootstrap.Modal(document.getElementById('detalleFacturaModal')); 
    const modalFacturaId = document.getElementById('modalFacturaId');
    const modalFecha = document.getElementById('modalFecha');
    const modalMetodoPago = document.getElementById('modalMetodoPago');
    const modalMontoTotal = document.getElementById('modalMontoTotal');
    const modalEstado = document.getElementById('modalEstado');
    const modalProductosDetalle = document.getElementById('modalProductosDetalle');
    
    
    const modalDescuentoRow = document.getElementById('modalDescuentoRow');
    const modalDescuento = document.getElementById('modalDescuento');
    
    function showStatus(message, type = 'info') {
        statusMessageDiv.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
    }
    
    function getEstadoText(estadoNum) {
        switch (parseInt(estadoNum)) {
            case 0: return 'Cancelado';
            case 1: return 'Pendiente';
            case 2: return 'En preparación';
            case 3: return 'En camino';
            case 4: return 'Entregado';
            case 5: return 'Devuelto';
            case 6: return 'Solicitud<br>de Cancelación';
            default: return 'Desconocido';
        }
    }    
    function getEstadoBadgeClass(estadoNum) {
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
    }    
    tbody.innerHTML = '<tr><td colspan="6" class="text-center">Cargando historial de compras...</td></tr>'; 
    showStatus('Obteniendo tu historial de compras...', 'info');    
    fetch('../../controller/controlador_facturas.php?accion=listar_historial')
        .then(response => {
            const contentType = response.headers.get("content-type");
            if (response.ok && contentType && contentType.includes("application/json")) {
                return response.json();
            } else {
                return response.text().then(text => {
                    console.error('Respuesta no JSON o error de HTTP:', response.status, text);
                    throw new Error(`Respuesta no válida del servidor. Código HTTP: ${response.status}. Contenido: ${text.substring(0, 200)}...`);
                });
            }
        })
        .then(data => {
            tbody.innerHTML = ''; 
            statusMessageDiv.innerHTML = '';             
            if (data && typeof data.status !== 'undefined' && data.status === 'error') {
                showStatus(`Error: ${data.message}`, 'danger');
                tbody.innerHTML = '<tr><td colspan="6" class="text-center">No se pudo cargar el historial de compras.</td></tr>'; 
                return;
            }            
            if (data.length > 0) {
                const facturasAgrupadas = {};
                
                data.forEach(item => {
                    if (!facturasAgrupadas[item.idFactura]) {
                        facturasAgrupadas[item.idFactura] = {
                            idFactura: item.idFactura,
                            fecha_emision: item.fecha,
                            metodo_pago: item.metodo_pago,
                            monto_total: item.monto_total,
                            estado: item.estado,
                            
                            monto_descuento: item.monto_descuento,
                            codigo_descuento_aplicado: item.codigo_descuento_aplicado,
                            detalles: []
                        };
                    }
                    facturasAgrupadas[item.idFactura].detalles.push({
                        idProducto: item.idProducto, 
                        producto: item.producto,
                        cantidad: item.cantidad,
                        precio_unitario: item.precio_unitario,
                        subtotal: item.subtotal
                    });
                });                
                Object.values(facturasAgrupadas).forEach(factura => {
                    const tr = document.createElement('tr');
                    
                    const estadoText = getEstadoText(factura.estado);
                    const estadoBadgeClass = getEstadoBadgeClass(factura.estado);                    
                    const isCancellableRequestable = (parseInt(factura.estado) === 1 || parseInt(factura.estado) === 2);
                    
                    let actionButtonHtml = '';
                    if (isCancellableRequestable) {
                        actionButtonHtml += `<button class="btn btn-sm btn-outline-danger me-1 btn-solicitar-cancelacion" data-idfactura="${factura.idFactura}">Solicitar Cancelación</button>`;
                    } else if (parseInt(factura.estado) === 6) {
                        actionButtonHtml += `<span class="badge bg-secondary me-1">Solicitud Enviada</span>`;
                    } else {
                        actionButtonHtml += `<span class="text-muted me-1">N/A</span>`; 
                    }                    
                    actionButtonHtml += `<button class="btn btn-sm btn-outline-primary me-1 btn-ver-detalle" data-idfactura="${factura.idFactura}">Ver Detalle</button>`;
                    
                    
                    actionButtonHtml += `<button class="btn btn-sm btn-outline-info btn-descargar-boleta" data-idfactura="${factura.idFactura}">Descargar Boleta</button>`;
                    
                    tr.innerHTML = `
                        <td>${htmlspecialchars(String(factura.idFactura))}</td>
                        <td>${htmlspecialchars(factura.fecha_emision)}</td>
                        <td>${htmlspecialchars(factura.metodo_pago)}</td>
                        <td>${parseFloat(factura.monto_total).toFixed(2)}</td>
                        <td><span class="badge rounded-pill ${estadoBadgeClass}">${estadoText}</span></td>
                        <td class="align-middle text-center">${actionButtonHtml}</td>
                    `;
                    tbody.appendChild(tr);
                });                
                tbody.querySelectorAll('.btn-solicitar-cancelacion').forEach(button => {
                    button.addEventListener('click', function() {
                        const idFactura = this.dataset.idfactura;
                        if (confirm(`¿Estás seguro de que quieres solicitar la cancelación de la factura #${idFactura}? Tu solicitud será revisada.`)) {
                            solicitarCancelacion(idFactura, this);
                        }
                    });
                });                
                tbody.querySelectorAll('.btn-ver-detalle').forEach(button => {
                    button.addEventListener('click', function() {
                        const idFactura = this.dataset.idfactura;
                        verDetalle(idFactura);
                    });
                });                
                tbody.querySelectorAll('.btn-descargar-boleta').forEach(button => {
                    button.addEventListener('click', function() {
                        const idFactura = this.dataset.idfactura;
                        descargarBoleta(idFactura);
                    });
                });            } else { 
                tbody.innerHTML = '<tr><td colspan="6" class="text-center">No tienes compras registradas.</td></tr>'; 
                showStatus('No hay historial de compras disponible.', 'info');
            }
        })
        .catch(error => {
            console.error('Error al cargar historial:', error);
            showStatus('Error al cargar historial de compras. Por favor, inténtalo de nuevo.', 'danger');
            tbody.innerHTML = '<tr><td colspan="6" class="text-center">Error al cargar el historial.</td></tr>'; 
        });    
    function solicitarCancelacion(idFactura, buttonElement) {
        buttonElement.disabled = true;
        buttonElement.textContent = 'Enviando Solicitud...';
        buttonElement.classList.remove('btn-outline-danger');
        buttonElement.classList.add('btn-secondary');        const formData = new FormData();
        formData.append('accion', 'solicitar_cancelacion');
        formData.append('id_factura', idFactura);        fetch('../../controller/controlador_facturas.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            const contentType = response.headers.get("content-type");
            if (response.ok && contentType && contentType.includes("application/json")) {
                return response.json();
            } else {
                return response.text().then(text => {
                    console.error('Respuesta no JSON o error de HTTP al solicitar cancelación:', response.status, text);
                    throw new Error(`Respuesta no válida del servidor. Código HTTP: ${response.status}. Contenido: ${text.substring(0, 200)}...`);
                });
            }
        })
        .then(data => {
            if (data.status === 'success') {
                showStatus(`✅ Solicitud de cancelación para factura #${idFactura} enviada con éxito.`, 'success');
                
                const facturaRow = buttonElement.closest('tr');
                if (facturaRow) {
                    const estadoCell = facturaRow.querySelector('td:nth-child(5)'); 
                    if (estadoCell) {
                        estadoCell.innerHTML = `<span class="badge rounded-pill ${getEstadoBadgeClass(6)}">${getEstadoText(6)}</span>`;
                    }
                    const actionCell = buttonElement.parentElement;
                    if (actionCell) {
                        
                        
                        actionCell.innerHTML = `<span class="badge bg-secondary me-1">Solicitud Enviada</span>
                                                <button class="btn btn-sm btn-outline-primary ms-1 btn-ver-detalle" data-idfactura="${idFactura}">Ver Detalle</button>
                                                <button class="btn btn-sm btn-outline-info ms-1 btn-descargar-boleta" data-idfactura="${idFactura}">Descargar Boleta</button>`;
                        
                        actionCell.querySelector('.btn-ver-detalle').addEventListener('click', function() {
                            verDetalle(this.dataset.idfactura);
                        });
                        actionCell.querySelector('.btn-descargar-boleta').addEventListener('click', function() {
                            descargarBoleta(this.dataset.idfactura);
                        });
                    }
                }
            } else {
                showStatus(`❌ Error al solicitar cancelación de factura #${idFactura}: ` + (data.message || 'Error desconocido.'), 'danger');
                buttonElement.disabled = false;
                buttonElement.textContent = 'Solicitar Cancelación';
                buttonElement.classList.remove('btn-secondary');
                buttonElement.classList.add('btn-outline-danger');
            }
        })
        .catch(error => {
            console.error('Error AJAX al solicitar cancelación de pedido:', error);
            showStatus(`❌ Error de red al solicitar cancelación de factura #${idFactura}.`, 'danger');
            buttonElement.disabled = false;
            buttonElement.textContent = 'Solicitar Cancelación';
            buttonElement.classList.remove('btn-secondary');
            buttonElement.classList.add('btn-outline-danger');
        });
    }    
    async function verDetalle(idFactura) {
        showStatus(`Cargando detalles de la factura #${idFactura}...`, 'info');
        try {
            const response = await fetch(`../../controller/controlador_facturas.php?accion=obtener_detalle_factura&id=${idFactura}`);
            const contentType = response.headers.get("content-type");            if (response.ok && contentType && contentType.includes("application/json")) {
                const data = await response.json();                if (data.status === 'error') {
                    showStatus(`Error al cargar el detalle: ${data.message}`, 'danger');
                    return;
                }                if (data.length === 0) {
                    showStatus(`No se encontraron detalles para la factura #${idFactura}.`, 'warning');
                    return;
                }                
                const cabecera = data[0]; 
                modalFacturaId.textContent = cabecera.idFactura;
                modalFecha.textContent = cabecera.fecha;
                modalMetodoPago.textContent = htmlspecialchars(cabecera.metodo_pago);
                modalMontoTotal.textContent = parseFloat(cabecera.monto_total).toFixed(2);
                modalEstado.innerHTML = `<span class="badge rounded-pill ${getEstadoBadgeClass(cabecera.estado)}">${getEstadoText(cabecera.estado)}</span>`;                
                const montoDescuento = parseFloat(cabecera.monto_descuento || 0);
                const codigoDescuento = htmlspecialchars(cabecera.codigo_descuento_aplicado || '');                if (montoDescuento > 0) {
                    modalDescuento.textContent = `-S/${montoDescuento.toFixed(2)}` + (codigoDescuento ? ` (Código: ${codigoDescuento})` : '');
                    modalDescuentoRow.style.display = 'block'; 
                } else {
                    modalDescuentoRow.style.display = 'none'; 
                }                
                modalProductosDetalle.innerHTML = ''; 
                data.forEach(detalle => {
                    const tr = document.createElement('tr');
                    
                    
                    const calificarLink = `index.php?page=view/calificar_producto.php&id_producto=${detalle.idProducto}&id_factura=${cabecera.idFactura}`;                    tr.innerHTML = `
                        <td>${htmlspecialchars(String(detalle.producto))}</td>
                        <td>${htmlspecialchars(String(detalle.cantidad))}</td>
                        <td>${parseFloat(detalle.precio_unitario).toFixed(2)}</td>
                        <td>${parseFloat(detalle.subtotal).toFixed(2)}</td>
                        <td class="text-center">
                            <a href="${calificarLink}" class="star-rating-icon" title="Calificar producto">
                                <svg xmlns="http:
                                    <path d="M2.866 14.85c-.078.444.36.791.746.593l4.39-2.256 4.389 2.256c.386.198.824-.149.746-.592l-.83-4.73 
                                        3.523-3.356c.33-.314.16-.888-.282-.95l-4.898-.696-2.192-4.327a.513.513 
                                        0 0 0-.927 0L5.354 4.327.456 5.023c-.441.062-.612.636-.282.95l3.522 
                                        3.356-.83 4.73z"/>
                                </svg>
                            </a>
                        </td>
                    `;
                    modalProductosDetalle.appendChild(tr);
                });                
                const style = document.createElement('style');
                style.innerHTML = `
                    .star-rating-icon {
                        color: #ccc;
                        transition: color 0.2s ease-in-out;
                    }
                    .star-rating-icon:hover {
                        color: #ffc107;
                    }
                `;
                document.head.appendChild(style);
                showStatus('', 'info'); 
                detalleFacturaModal.show(); 
            } else {
                const text = await response.text();
                console.error('Respuesta no JSON o error de HTTP al obtener detalle:', response.status, text);
                showStatus(`Error en la respuesta del servidor al cargar detalle. Código: ${response.status}.`, 'danger');
            }
        } catch (error) {
            console.error('Error AJAX al obtener detalle de factura:', error);
            showStatus(`Error de red al cargar el detalle de la factura #${idFactura}.`, 'danger');
        }
    }    
    function descargarBoleta(idFactura) {
        showStatus(`Generando boleta para factura #${idFactura}...`, 'info');
        const downloadUrl = `../../controller/controlador_facturas.php?accion=descargar_boleta&id_factura=${idFactura}`;
        
        
        window.open(downloadUrl, '_blank');
        showStatus(`Si la descarga no inicia automáticamente, revisa tu carpeta de descargas.`, 'success');
    }    
    function htmlspecialchars(str) {
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
    }
});
