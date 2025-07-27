<?php
// controlador_facturas.php
// Importante para que cURL lea la respuesta JSON (o para que el navegador maneje descargas)
header('Content-Type: application/json'); 

error_reporting(E_ALL); // Activa el reporte de todos los errores
ini_set('display_errors', 1); // Muestra los errores en la salida (solo para desarrollo)

// Para acceder a $_SESSION para vaciar el carrito, es necesario iniciar la sesión aquí.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../model/modelo_factura.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php'; // Asegúrate de que esta ruta sea correcta

// Incluir la librería FPDF (AJUSTA LA RUTA SI ES NECESARIO)
require('../fpdf/fpdf.php'); 

$factura = new Factura();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    switch ($accion) {
        case 'listar_historial':
            listarHistorial($factura);
            break;
        case 'obtener_detalle_factura':
            obtenerDetalleFactura($factura);
            break;
        case 'descargar_boleta': 
            descargarBoleta($factura);
            break;
        case 'listar_todas_las_facturas': 
            listarTodasLasFacturas($factura);
            break;
        case 'obtener_ventas_totales_mes': 
            obtenerVentasTotalesMes($factura);
            break;
        case 'obtener_pedidos_pendientes_count':
            obtenerPedidosPendientesCount($factura);
            break;
        case 'obtener_ventas_por_rango_fechas': 
            obtenerVentasPorRangoFechas($factura);
            break;
        case 'descargar_reporte_ventas_pdf':
            // Cambiar el Content-Type para PDF
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="reporte_ventas.pdf"');
            descargarReporteVentasPDF($factura);
            exit; 
        default:
            echo json_encode(['status' => 'error', 'message' => 'Acción GET no válida.']);
            break;
    }
    exit;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    switch ($accion) {
        case 'registrar_factura':
            registrarFactura($factura);
            break;
        case 'solicitar_cancelacion':
            solicitarCancelacion($factura);
            break;
        case 'actualizar_estado_factura':
            actualizarEstadoFactura($factura);
            break;
        default:
            echo json_encode(['status' => 'error', 'message' => 'Acción POST no válida.']);
            break;
    }
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
    exit;
}

function listarHistorial($factura_model) {
    $idUsuario = $_SESSION['id_usuario'] ?? null;

    if (!$idUsuario) {
        echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado para ver historial.']);
        return;
    }

    try {
        $resultado = $factura_model->listarFacturasPorUsuario($idUsuario);

        if ($resultado === null) {
            error_log("Error: listarFacturasPorUsuario devolvió null en controlador_facturas.");
            echo json_encode(['status' => 'error', 'message' => 'Error interno al obtener el historial.']);
        } else {
            echo json_encode($resultado); 
        }
    } catch (Exception $e) {
        error_log("Error en listarHistorial (controlador_facturas): " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Excepción al obtener historial: ' . $e->getMessage()]);
    }
}


function registrarFactura($factura_model) {
    $idUsuario = $_POST['id_usuario'] ?? null;
    $metodoPago = $_POST['metodo_pago'] ?? '';
    // Asegurarse de que montoTotal sea un float y eliminar cualquier coma como separador de miles
    $montoTotal = (float)str_replace(',', '', ($_POST['monto_total'] ?? 0));
    $productosJSON = $_POST['productos_json'] ?? '[]';
    $correoDestinatario = $_POST['correo_destinatario'] ?? 'correo_destino@ejemplo.com'; // Recibe el correo desde el formulario
    
    // Nuevos campos para el descuento
    $idCodigoDescuento = !empty($_POST['id_codigo_descuento']) ? (int)$_POST['id_codigo_descuento'] : null;
    $montoDescuento = !empty($_POST['monto_descuento']) ? (float)$_POST['monto_descuento'] : 0.00;

    if (!$idUsuario || empty($metodoPago) || empty($productosJSON)) {
        error_log("DEBUG controlador_facturas.php: Datos incompletos: idUsuario=" . var_export($idUsuario, true) . ", metodoPago=" . var_export($metodoPago, true) . ", productosJSON=" . var_export($productosJSON, true));
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos para registrar la factura. id_usuario, metodo_pago o productos_json están vacíos.']);
        return;
    }

    // Registrar la factura y obtener el ID generado
    // Pasar los nuevos parámetros de descuento al modelo
    $idFacturaGenerada = $factura_model->registrarFactura($idUsuario, $metodoPago, $montoTotal, $productosJSON, $idCodigoDescuento, $montoDescuento);

    if ($idFacturaGenerada) {
        unset($_SESSION['cart']); // Vacía el carrito de la sesión

        // Obtener todos los detalles de la factura recién creada
        $factura_completa = $factura_model->obtenerDetalleFactura($idFacturaGenerada);

        if (empty($factura_completa)) {
            error_log("ERROR controlador_facturas.php: No se pudieron obtener los detalles de la factura ID: " . $idFacturaGenerada);
            echo json_encode(['status' => 'error', 'message' => 'Factura registrada, pero no se pudieron obtener los detalles para el correo.']);
            return;
        }

        $cabecera = $factura_completa[0]; // La cabecera está en el primer elemento del array

        // Calcular subtotal e IGV (18%) - Asegurarse de que monto_total sea un float
        $montoTotalCabecera = (float)($cabecera['monto_total'] ?? 0);
        // El subtotal antes del IGV es el monto total de la factura / 1.18
        $subtotalSinIGV = $montoTotalCabecera / 1.18;
        $igv = $montoTotalCabecera - $subtotalSinIGV;

        $itemsHtml = '';
        foreach ($factura_completa as $item) {
            // Asegurarse de que los valores sean floats antes de number_format
            $itemPrecioUnitario = (float)($item['precio_unitario'] ?? 0);
            $itemSubtotal = (float)($item['subtotal'] ?? 0);

            $itemsHtml .= '
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd; text-align: left;">' . htmlspecialchars($item['producto'] ?? 'N/A') . '</td>
                    <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">' . htmlspecialchars($item['cantidad'] ?? 0) . '</td>
                    <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">S/' . number_format($itemPrecioUnitario, 2) . '</td>
                    <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">S/' . number_format($itemSubtotal, 2) . '</td>
                </tr>';
        }

        $asunto = 'Confirmación de Compra en PCBYTE - Boleta de Venta N° ' . str_pad($cabecera['idFactura'], 6, "0", STR_PAD_LEFT);
        $fechaActual = date('d/m/Y H:i:s');
        $numeroBoleta = 'B001-' . str_pad($cabecera['idFactura'], 6, "0", STR_PAD_LEFT);

        // Información de descuento para el correo
        $descuentoInfoHtml = '';
        if ((float)($cabecera['monto_descuento'] ?? 0) > 0) {
            $descuentoInfoHtml = '
                <tr>
                    <td class="text-right"><strong>Descuento (' . htmlspecialchars($cabecera['codigo_descuento_aplicado'] ?? 'N/A') . '):</strong></td>
                    <td class="text-right" style="color: #28a745;">-S/' . number_format((float)$cabecera['monto_descuento'], 2) . '</td>
                </tr>
            ';
        }


        $mensajeHtml = '
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Boleta de Venta - PCBYTE</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 700px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; }
                    .header { background-color: #007bff; color: white; padding: 15px 0; text-align: center; border-radius: 8px 8px 0 0; }
                    .header h2 { margin: 0; }
                    .section-title { font-size: 16px; font-weight: bold; margin-top: 20px; margin-bottom: 10px; color: #007bff; }
                    .info-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
                    .info-table td { padding: 5px 0; vertical-align: top; }
                    .item-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                    .item-table th, .item-table td { padding: 8px; border: 1px solid #ddd; text-align: left; }
                    .item-table th { background-color: #f2f2f2; }
                    .total-row { font-weight: bold; }
                    .footer { text-align: center; font-size: 12px; color: #777; margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px; }
                    .text-right { text-align: right; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h2>BOLETA DE VENTA</h2>
                        <p style="margin: 5px 0;">PCBYTE E.I.R.L. - RUC: 20506472343</p>
                        <p style="margin: 5px 0;">Av Inca Garcilaso de la Vega 1251, Tienda 243 y 167, Cercado de Lima 15001, Perú.</p>
                        <p style="margin: 5px 0;">Teléfono: 946480897, 998197574</p>
                    </div>

                    <div style="padding: 20px;">
                        <p class="section-title">Información de la Boleta</p>
                        <table class="info-table">
                            <tr>
                                <td><strong>Boleta N°:</strong> ' . htmlspecialchars($numeroBoleta) . '</td>
                                <td class="text-right"><strong>Fecha de Emisión:</strong> ' . htmlspecialchars($cabecera['fecha'] ?? 'N/A') . '</td>
                            </tr>
                            <tr>
                                <td colspan="2"><strong>Método de Pago:</strong> ' . htmlspecialchars($cabecera['metodo_pago'] ?? 'N/A') . '</td>
                            </tr>
                            <tr>
                                <td colspan="2"><strong>Estado:</strong> ' . htmlspecialchars(getEstadoTextPDF($cabecera['estado'] ?? 99)) . '</td>
                            </tr>
                        </table>

                        <p class="section-title">Datos del Cliente</p>
                        <table class="info-table">
                            <tr>
                                <td><strong>Nombre:</strong> ' . htmlspecialchars($cabecera['nombre_cliente'] ?? 'N/A') . ' ' . htmlspecialchars($cabecera['apellido_cliente'] ?? 'N/A') . '</td>
                            </tr>
                            <tr>
                                <td><strong>DNI:</strong> ' . htmlspecialchars($cabecera['dni_cliente'] ?? 'N/A') . '</td>
                            </tr>
                            <tr>
                                <td><strong>Correo:</strong> ' . htmlspecialchars($cabecera['correo_cliente'] ?? 'N/A') . '</td>
                            </tr>
                        </table>

                        <p class="section-title">Detalle de Productos</p>
                        <table class="item-table">
                            <thead>
                                <tr>
                                    <th style="width: 50%;">Producto</th>
                                    <th style="width: 15%; text-align: center;">Cantidad</th>
                                    <th style="width: 15%; text-align: right;">P. Unitario (S/)</th>
                                    <th style="width: 20%; text-align: right;">Subtotal (S/)</th>
                                </tr>
                            </thead>
                            <tbody>
                                ' . $itemsHtml . '
                            </tbody>
                        </table>

                        <table class="info-table" style="margin-top: 20px; float: right; width: 50%;">
                            <tr>
                                <td class="text-right"><strong>Subtotal Productos:</strong></td>
                                <td class="text-right">S/' . number_format($montoTotalCabecera + (float)($cabecera['monto_descuento'] ?? 0) - $igv, 2) . '</td>
                            </tr>
                            ' . $descuentoInfoHtml . '
                            <tr>
                                <td class="text-right"><strong>IGV (18%):</strong></td>
                                <td class="text-right">S/' . number_format($igv, 2) . '</td>
                            </tr>
                            <tr class="total-row">
                                <td class="text-right"><strong>TOTAL A PAGAR (S/):</strong></td>
                                <td class="text-right">S/' . number_format($montoTotalCabecera, 2) . '</td>
                            </tr>
                        </table>
                        <div style="clear: both;"></div>

                        <div class="footer">
                            <p>Gracias por su compra. ¡Esperamos verte de nuevo!</p>
                            <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
                        </div>
                    </div>
                </div>
            </body>
            </html>
        ';

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'cabrerasanchezv11@gmail.com'; // Tu Gmail
            $mail->Password = 'irea vcfr nvrn clhh'; // Tu Contraseña de aplicación
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('cabrerasanchezv11@gmail.com', 'PCBYTE - Confirmación de Compra');
            $mail->addAddress($correoDestinatario); // Correo del cliente

            $mail->isHTML(true); // Establecer formato de correo a HTML
            $mail->Subject = utf8_decode($asunto);
            $mail->Body = utf8_decode($mensajeHtml);
            $mail->AltBody = strip_tags(str_replace(['<br>', '<p>'], "\n", $mensajeHtml)); // Versión de texto plano

            $mail->send();
            error_log("DEBUG controlador_facturas.php: Correo de boleta enviado a " . $correoDestinatario);
        } catch (Exception $e) {
            error_log("ERROR controlador_facturas.php: Error al enviar el correo de boleta: {$mail->ErrorInfo}");
        }

        echo json_encode(['status' => 'success', 'message' => 'Factura registrada y boleta enviada a tu correo con éxito.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al confirmar la factura. Posible problema -> Producto con stock insuficiente']);
    }
}


function solicitarCancelacion($factura_model) {
    $idFactura = $_POST['id_factura'] ?? null;

    if (is_null($idFactura)) {
        echo json_encode(['status' => 'error', 'message' => 'ID de factura no proporcionado para la solicitud de cancelación.']);
        return;
    }

    $resultado = $factura_model->solicitarCancelacionFactura($idFactura);

    if ($resultado) {
        echo json_encode(['status' => 'success', 'message' => 'Solicitud de cancelación enviada con éxito.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo procesar la solicitud de cancelación. Verifique si el estado es elegible o hay un error en la base de datos.']);
    }
}

function obtenerDetalleFactura($factura_model) {
    $idFactura = $_GET['id'] ?? null; 

    if (is_null($idFactura)) {
        echo json_encode(['status' => 'error', 'message' => 'ID de factura no proporcionado para ver el detalle.']);
        return;
    }

    try {
        $detalle = $factura_model->obtenerDetalleFactura($idFactura);
        if (empty($detalle)) {
            echo json_encode(['status' => 'error', 'message' => 'Factura no encontrada o sin detalles.']);
        } else {
            echo json_encode($detalle);
        }
    } catch (Exception $e) {
        error_log("Error en obtenerDetalleFactura (controlador_facturas): " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error al obtener el detalle de la factura: ' . $e->getMessage()]);
    }
}

function descargarBoleta($factura_model) {
    $idFactura = $_GET['id_factura'] ?? null;

    if (is_null($idFactura)) {
        echo "Error: ID de factura no proporcionado para descargar la boleta.";
        exit;
    }

    try {
        $factura_completa = $factura_model->obtenerDetalleFactura($idFactura); 

        if (empty($factura_completa)) {
            echo "Error: Factura no encontrada o sin detalles para generar la boleta.";
            exit;
        }

        $cabecera = $factura_completa[0];

        // Calcular subtotal e IGV (18%)
        $montoTotalCabecera = (float)($cabecera['monto_total'] ?? 0);
        $subtotalSinIGV = $montoTotalCabecera / 1.18; // Subtotal antes de IGV
        $igv = $montoTotalCabecera - $subtotalSinIGV;

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, utf8_decode('BOLETA DE VENTA'), 0, 1, 'C');

        // Datos de la tienda
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 5, utf8_decode('PC BYTE E.I.R.L.'), 0, 1, 'C');
        $pdf->Cell(0, 5, utf8_decode('RUC: 20506472343'), 0, 1, 'C');
        $pdf->Cell(0, 5, utf8_decode('Dirección: Av Inca Garcilaso de la Vega 1251, Tienda 243 y Tienda 167, Cercado de Lima 15001, Perú.'), 0, 1, 'C');
        $pdf->Cell(0, 5, utf8_decode('Teléfono: 946480897, 998197574'), 0, 1, 'C');
        $pdf->Ln(10);

        // Datos del cliente
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, utf8_decode('Datos del Cliente'), 0, 1, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 6, utf8_decode('Nombre: ') . utf8_decode($cabecera['nombre_cliente'] ?? 'No registrado'), 0, 1, 'L');
        $pdf->Cell(0, 6, utf8_decode('DNI/RUC: ') . ($cabecera['dni_cliente'] ?? 'N/A'), 0, 1, 'L');
        $pdf->Cell(0, 6, utf8_decode('Correo: ') . ($cabecera['correo_cliente'] ?? 'N/A'), 0, 1, 'L');
        $pdf->Ln(5);

        // Información de la factura
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, utf8_decode('Información de la Factura'), 0, 1, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 6, utf8_decode('Boleta N°: B001-') . str_pad($cabecera['idFactura'], 6, "0", STR_PAD_LEFT), 0, 1, 'L');
        $pdf->Cell(0, 6, utf8_decode('Fecha de Emisión: ') . ($cabecera['fecha'] ?? 'N/A'), 0, 1, 'L');
        $pdf->Cell(0, 6, utf8_decode('Método de Pago: ') . utf8_decode($cabecera['metodo_pago'] ?? 'N/A'), 0, 1, 'L');
        $pdf->Cell(0, 6, utf8_decode('Estado: ') . utf8_decode(getEstadoTextPDF($cabecera['estado'] ?? 99)), 0, 1, 'L');
        $pdf->Ln(8);

        // Detalle de productos
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, utf8_decode('Detalle de Productos'), 0, 1, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(80, 7, utf8_decode('Producto'), 1, 0, 'C');
        $pdf->Cell(20, 7, utf8_decode('Cant.'), 1, 0, 'C');
        $pdf->Cell(40, 7, utf8_decode('P. Unitario (S/)'), 1, 0, 'C');
        $pdf->Cell(40, 7, utf8_decode('Subtotal (S/)'), 1, 1, 'C');

        $pdf->SetFont('Arial', '', 10);
        foreach ($factura_completa as $item) {
            $pdf->Cell(80, 7, utf8_decode($item['producto'] ?? 'N/A'), 1, 0, 'L');
            $pdf->Cell(20, 7, (int)($item['cantidad'] ?? 0), 1, 0, 'C');
            $pdf->Cell(40, 7, number_format((float)($item['precio_unitario'] ?? 0), 2), 1, 0, 'R');
            $pdf->Cell(40, 7, number_format((float)($item['subtotal'] ?? 0), 2), 1, 1, 'R');
        }

        // Totales
        $pdf->Ln(5);
        $pdf->SetFont('Arial', '', 10);
        
        // Subtotal de productos antes de descuento e IGV
        $subtotalProductos = $montoTotalCabecera + (float)($cabecera['monto_descuento'] ?? 0) - $igv;
        $pdf->Cell(140, 7, utf8_decode('Subtotal Productos:'), 1, 0, 'R');
        $pdf->Cell(40, 7, number_format($subtotalProductos, 2), 1, 1, 'R');

        // Descuento aplicado
        if ((float)($cabecera['monto_descuento'] ?? 0) > 0) {
            $pdf->Cell(140, 7, utf8_decode('Descuento (' . ($cabecera['codigo_descuento_aplicado'] ?? 'N/A') . '):'), 1, 0, 'R');
            $pdf->Cell(40, 7, '- ' . number_format((float)$cabecera['monto_descuento'], 2), 1, 1, 'R');
        }

        $pdf->Cell(140, 7, utf8_decode('IGV (18%):'), 1, 0, 'R');
        $pdf->Cell(40, 7, number_format($igv, 2), 1, 1, 'R');

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(140, 10, utf8_decode('TOTAL A PAGAR (S/):'), 1, 0, 'R');
        $pdf->Cell(40, 10, number_format($montoTotalCabecera, 2), 1, 1, 'R');

        // Pie de página
        $pdf->Ln(15);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 5, utf8_decode('Gracias por su compra.'), 0, 1, 'C');

        $pdf->Output('D', 'Boleta_Factura_' . $idFactura . '.pdf');

    } catch (Exception $e) {
        error_log("Error al generar boleta PDF: " . $e->getMessage());
        echo "Error interno del servidor al generar la boleta. Por favor, inténtalo de nuevo más tarde.";
        exit;
    }
}

function listarTodasLasFacturas($factura_model) {

    try {
        $resultado = $factura_model->listarTodasLasFacturas();

        if ($resultado === null) {
            error_log("Error: listarTodasLasFacturas devolvió null en controlador_facturas.");
            echo json_encode(['status' => 'error', 'message' => 'Error interno al obtener todas las facturas.']);
        } else {
            echo json_encode(['status' => 'success', 'data' => $resultado]); 
        }
    } catch (Exception $e) {
        error_log("Error en listarTodasLasFacturas (controlador_facturas): " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Excepción al obtener todas las facturas: ' . $e->getMessage()]);
    }
}

function actualizarEstadoFactura($factura_model) {
    $idFactura = $_POST['id_factura'] ?? null;
    $nuevoEstado = $_POST['nuevo_estado'] ?? null;
    $modificadoPor = $_SESSION['id_usuario'] ?? null; // Obtener el ID del usuario logueado (admin/empleado)

    if (is_null($idFactura) || is_null($nuevoEstado) || is_null($modificadoPor)) {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos para actualizar el estado de la factura.']);
        return;
    }

    // Opcional: Validar que el nuevo estado sea uno de los permitidos (0-6)
    $estados_permitidos = [0, 1, 2, 3, 4, 5, 6];
    if (!in_array((int)$nuevoEstado, $estados_permitidos)) {
        echo json_encode(['status' => 'error', 'message' => 'Estado de factura no válido.']);
        return;
    }

    try {
        $resultado = $factura_model->actualizarEstadoFactura($idFactura, (int)$nuevoEstado, $modificadoPor);

        if ($resultado) {
            echo json_encode(['status' => 'success', 'message' => 'Estado de factura actualizado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el estado de la factura.']);
        }
    } catch (Exception $e) {
        error_log("Error en actualizarEstadoFactura (controlador_facturas): " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Excepción al actualizar el estado de la factura: ' . $e->getMessage()]);
    }
}

function getEstadoTextPDF($estadoNum) {
    switch (intval($estadoNum)) {
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

function obtenerVentasTotalesMes($factura_model) {
    try {
        $totalVentas = $factura_model->obtenerVentasTotalesMes();
        echo json_encode(['status' => 'success', 'total_ventas' => $totalVentas]);
    } catch (Exception $e) {
        error_log("Error en obtenerVentasTotalesMes (controlador): " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error al obtener ventas totales del mes.']);
    }
}

function obtenerPedidosPendientesCount($factura_model) {
    try {
        $count = $factura_model->obtenerPedidosPendientesCount();
        echo json_encode(['status' => 'success', 'count' => $count]);
    } catch (Exception $e) {
        error_log("Error en obtenerPedidosPendientesCount (controlador): " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error al obtener el conteo de pedidos pendientes.']);
    }
}

function obtenerVentasPorRangoFechas($factura_model) {
    $fechaInicio = $_GET['fecha_inicio'] ?? null;
    $fechaFin = $_GET['fecha_fin'] ?? null;

    if (empty($fechaInicio) || empty($fechaFin)) {
        echo json_encode(['status' => 'error', 'message' => 'Fechas de inicio y fin son obligatorias.']);
        return;
    }

    $ventas = $factura_model->obtenerVentasPorRangoFechas($fechaInicio, $fechaFin);
    if ($ventas !== false) {
        echo json_encode(['status' => 'success', 'data' => $ventas]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al obtener el reporte de ventas.']);
    }
}

function descargarReporteVentasPDF($factura_model) {
    $fechaInicio = $_GET['fecha_inicio'] ?? null;
    $fechaFin = $_GET['fecha_fin'] ?? null;

    if (empty($fechaInicio) || empty($fechaFin)) {
        echo "Error: Fechas de inicio y fin son obligatorias para el reporte PDF.";
        return;
    }

    $ventas = $factura_model->obtenerVentasPorRangoFechas($fechaInicio, $fechaFin);

    if ($ventas === false) {
        echo "Error al obtener los datos de ventas para el reporte PDF.";
        return;
    }

    // Crear una nueva instancia de FPDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, utf8_decode('Reporte de Ventas'), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, utf8_decode("Desde: $fechaInicio Hasta: $fechaFin"), 0, 1, 'C');
    $pdf->Ln(10); // Salto de línea

    // Cabecera de la tabla
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(200, 220, 255); // Color de fondo para la cabecera
    $pdf->Cell(20, 8, 'ID', 1, 0, 'C', true);
    $pdf->Cell(30, 8, 'Fecha', 1, 0, 'C', true);
    $pdf->Cell(50, 8, 'Cliente', 1, 0, 'C', true);
    $pdf->Cell(30, 8, utf8_decode('Mét. Pago'), 1, 0, 'C', true);
    $pdf->Cell(30, 8, 'Total (S/)', 1, 0, 'C', true);
    $pdf->Cell(30, 8, 'Estado', 1, 1, 'C', true); // Última columna y salto de línea

    $pdf->SetFont('Arial', '', 9);
    $pdf->SetFillColor(240, 240, 240); // Color de fondo para las filas (alternar)
    $fill = false;
    $totalSalesAmount = 0;
    $totalOrders = 0;

    if (empty($ventas)) {
        $pdf->Cell(0, 10, utf8_decode('No se encontraron ventas para el rango de fechas seleccionado.'), 1, 1, 'C');
    } else {
        foreach ($ventas as $fila) {
            $estadoText = '';
            $estadoNum = intval($fila['estado']);
            switch ($estadoNum) {
                case 0: $estadoText = 'Cancelado'; break;
                case 1: $estadoText = 'Pendiente'; break;
                case 2: $estadoText = 'En preparación'; break;
                case 3: $estadoText = 'En camino'; break;
                case 4: $estadoText = 'Entregado'; break;
                case 5: $estadoText = 'Devuelto'; break;
                case 6: $estadoText = 'Solicitud de Cancelación'; break;
                default: $estadoText = 'Desconocido'; break;
            }

            $clienteNombre = utf8_decode($fila['nombre_cliente'] . ' ' . $fila['apellido_cliente']);
            $montoTotal = number_format($fila['monto_total'], 2);

            $pdf->Cell(20, 7, $fila['idFactura'], 1, 0, 'C', $fill);
            $pdf->Cell(30, 7, $fila['fecha'], 1, 0, 'C', $fill);
            $pdf->Cell(50, 7, $clienteNombre, 1, 0, 'L', $fill);
            $pdf->Cell(30, 7, utf8_decode($fila['metodo_pago']), 1, 0, 'C', $fill);
            $pdf->Cell(30, 7, 'S/' . $montoTotal, 1, 0, 'R', $fill);
            $pdf->Cell(30, 7, utf8_decode($estadoText), 1, 1, 'C', $fill);
            $fill = !$fill; // Alternar color de fondo

            if ($estadoNum >= 1 && $estadoNum <= 4) {
                $totalSalesAmount += $fila['monto_total'];
            }
            $totalOrders++;
        }
    }

    // Totales
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(130, 8, utf8_decode('Total de Ventas:'), 1, 0, 'R', true);
    $pdf->Cell(30, 8, 'S/' . number_format($totalSalesAmount, 2), 1, 0, 'R', true);
    $pdf->Cell(30, 8, '', 1, 1, 'C', true); // Celda vacía para alinear

    $pdf->Cell(130, 8, utf8_decode('Total de Pedidos:'), 1, 0, 'R', true);
    $pdf->Cell(30, 8, $totalOrders, 1, 0, 'C', true);
    $pdf->Cell(30, 8, '', 1, 1, 'C', true); // Celda vacía para alinear

    $pdf->Output('I', 'reporte_ventas_' . $fechaInicio . '_a_' . $fechaFin . '.pdf');
}
