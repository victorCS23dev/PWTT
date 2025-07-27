$(document).ready(function() {
    const detalleFacturaAdminModal = new bootstrap.Modal(document.getElementById('detalleFacturaAdminModal'));     
    const modalFacturaIdAdmin = $('#modalFacturaIdAdmin');
    const modalClienteNombreAdmin = $('#modalClienteNombreAdmin');
    const modalClienteDNIAdmin = $('#modalClienteDNIAdmin');
    const modalClienteCorreoAdmin = $('#modalClienteCorreoAdmin');
    const modalFechaAdmin = $('#modalFechaAdmin');
    const modalMetodoPagoAdmin = $('#modalMetodoPagoAdmin');
    const modalMontoTotalAdmin = $('#modalMontoTotalAdmin');
    const modalProductosDetalleAdmin = $('#modalProductosDetalleAdmin');
    const modalDescuentoRowAdmin = $('#modalDescuentoRowAdmin');
    const modalDescuentoAdmin = $('#modalDescuentoAdmin');    
    const editarEstadoFacturaForm = $('#editarEstadoFacturaForm');
    const editFacturaId = $('#editFacturaId');
    const estadoFacturaSelect = $('#estadoFacturaSelect');
    const estadoUpdateMessage = $('#estadoUpdateMessage');
    const modificadoPorAdmin = $('#modificadoPorAdmin');     
    function showStatus(message, type = 'info') {
        const statusMessageDiv = $('#pedidos-status-message'); 
        statusMessageDiv.html(`<div class="alert alert-${type}">${message}</div>`);
        setTimeout(() => {
            statusMessageDiv.empty();
        }, 5000); 
    }    
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
    window.verDetalleAdmin = async function(idFactura) {
        showStatus(`Cargando detalles de la factura #${idFactura}...`, 'info');
        try {
            const response = await fetch(`${window.CONTROLADOR_FACTURAS_URL}?accion=obtener_detalle_factura&id=${idFactura}`);
            const contentType = response.headers.get("content-type");            if (response.ok && contentType && contentType.includes("application/json")) {
                const data = await response.json();                if (data.status === 'error') {
                    showStatus(`Error al cargar el detalle: ${data.message}`, 'danger');
                    return;
                }                if (data.length === 0) {
                    showStatus(`No se encontraron detalles para la factura #${idFactura}.`, 'warning');
                    return;
                }                const cabecera = data[0]; 
                modalFacturaIdAdmin.text(cabecera.idFactura);
                modalClienteNombreAdmin.text(`${htmlspecialchars(cabecera.nombre_cliente)} ${htmlspecialchars(cabecera.apellido_cliente)}`);
                modalClienteDNIAdmin.text(htmlspecialchars(cabecera.dni_cliente));
                modalClienteCorreoAdmin.text(htmlspecialchars(cabecera.correo_cliente));
                modalFechaAdmin.text(cabecera.fecha);
                modalMetodoPagoAdmin.text(htmlspecialchars(cabecera.metodo_pago));
                modalMontoTotalAdmin.text(parseFloat(cabecera.monto_total).toFixed(2));
                
                
                estadoFacturaSelect.val(cabecera.estado);
                editFacturaId.val(cabecera.idFactura);                 
                const montoDescuento = parseFloat(cabecera.monto_descuento || 0);
                const codigoDescuento = htmlspecialchars(cabecera.codigo_descuento_aplicado || '');                if (montoDescuento > 0) {
                    modalDescuentoAdmin.text(`-S/${montoDescuento.toFixed(2)}` + (codigoDescuento ? ` (Código: ${codigoDescuento})` : ''));
                    modalDescuentoRowAdmin.show(); 
                } else {
                    modalDescuentoRowAdmin.hide(); 
                }                
                modalProductosDetalleAdmin.empty(); 
                data.forEach(detalle => {
                    const tr = `
                        <tr>
                            <td>${htmlspecialchars(String(detalle.producto))}</td>
                            <td>${htmlspecialchars(String(detalle.cantidad))}</td>
                            <td>S/${parseFloat(detalle.precio_unitario).toFixed(2)}</td>
                            <td>S/${parseFloat(detalle.subtotal).toFixed(2)}</td>
                        </tr>`;
                    modalProductosDetalleAdmin.append(tr);
                });                showStatus('', 'info'); 
                detalleFacturaAdminModal.show(); 
            } else {
                const text = await response.text();
                console.error('Respuesta no JSON o error de HTTP al obtener detalle:', response.status, text);
                showStatus(`Error en la respuesta del servidor al cargar detalle. Código: ${response.status}.`, 'danger');
            }
        } catch (error) {
            console.error('Error AJAX al obtener detalle de factura:', error);
            showStatus(`Error de red al cargar el detalle de la factura #${idFactura}.`, 'danger');
        }
    };    
    window.descargarBoletaAdmin = function(idFactura) {
        showStatus(`Generando boleta para factura #${idFactura}...`, 'info');
        const downloadUrl = `${window.CONTROLADOR_FACTURAS_URL}?accion=descargar_boleta&id_factura=${idFactura}`;
        window.open(downloadUrl, '_blank');
        showStatus(`Si la descarga no inicia automáticamente, revisa tu carpeta de descargas.`, 'success');
    };    
    editarEstadoFacturaForm.on('submit', function(event) {
        event.preventDefault();        const idFactura = editFacturaId.val();
        const nuevoEstado = estadoFacturaSelect.val();
        const idUsuarioModificador = modificadoPorAdmin.val();        if (!idFactura || !nuevoEstado || !idUsuarioModificador) {
            estadoUpdateMessage.html('<div class="alert alert-danger">Datos incompletos para actualizar el estado.</div>');
            return;
        }        const formData = new FormData();
        formData.append('accion', 'actualizar_estado_factura');
        formData.append('id_factura', idFactura);
        formData.append('nuevo_estado', nuevoEstado);
        formData.append('modificado_por', idUsuarioModificador);         fetch(window.CONTROLADOR_FACTURAS_URL, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            const contentType = response.headers.get("content-type");
            if (response.ok && contentType && contentType.includes("application/json")) {
                return response.json();
            } else {
                return response.text().then(text => {
                    console.error('Respuesta no JSON o error de HTTP al actualizar estado:', response.status, text);
                    throw new Error(`Respuesta no válida del servidor. Código HTTP: ${response.status}. Contenido: ${text.substring(0, 200)}...`);
                });
            }
        })
        .then(data => {
            if (data.status === 'success') {
                estadoUpdateMessage.html('<div class="alert alert-success">Estado actualizado correctamente.</div>');
                
                setTimeout(() => {
                    detalleFacturaAdminModal.hide();
                    
                    if (typeof window.listar_facturas_admin === 'function') {
                        window.listar_facturas_admin(window.CONTROLADOR_FACTURAS_URL); 
                    } else {
                        console.error("La función 'listar_facturas_admin' no está definida globalmente.");
                    }
                }, 1000);
            } else {
                estadoUpdateMessage.html(`<div class="alert alert-danger">Error al actualizar estado: ${data.message}</div>`);
            }
        })
        .catch(error => {
            console.error('Error AJAX al actualizar estado:', error);
            estadoUpdateMessage.html(`<div class="alert alert-danger">Error de red al actualizar el estado.</div>`);
        });
    });
});
