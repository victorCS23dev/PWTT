<?php
// controller/controlador_descuentos.php
// Importante para que cURL lea la respuesta JSON (o para que el navegador maneje descargas)
header('Content-Type: application/json'); 

error_reporting(E_ALL); // Activa el reporte de todos los errores
ini_set('display_errors', 1); // Muestra los errores en la salida (solo para desarrollo)

// Para acceder a $_SESSION para vaciar el carrito, es necesario iniciar la sesión aquí.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../model/modelo_descuentos.php';
require_once '../model/modelo_categoria.php'; 
require_once '../model/modelo_marca.php';
require_once '../model/modelo_usuario.php';

// Incluir la librería de correo PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php'; // Asegúrate de que esta ruta sea correcta

$usuario_model = new Usuario();
$descuento_model = new Descuento();
$categoria_model = new Categoria();
$marca_model = new Marca();

$accion = $_REQUEST['accion'] ?? ''; // Usa $_REQUEST para GET y POST

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        switch ($accion) {
            case 'registrar_descuento':
                registrarDescuento($descuento_model);
                break;
            case 'actualizar_descuento':
                actualizarDescuento($descuento_model);
                break;
            case 'eliminar_descuento':
                eliminarDescuento($descuento_model);
                break;
            case 'notificar_descuento':
                notificarDescuento($usuario_model);
                break;
            case 'validar_descuento':
                validarDescuento($descuento_model);
                break;
            default:
                echo json_encode(['status' => 'error', 'message' => 'Acción POST no válida.']);
                break;
        }
        break;
    case 'GET':
        switch ($accion) {
            case 'listar_descuentos':
                listarDescuentos($descuento_model);
                break;
            case 'obtener_descuento':
                obtenerDescuento($descuento_model);
                break;
            case 'listar_categorias':
                listarCategorias($categoria_model);
                break;
            case 'listar_marcas':
                listarMarcas($marca_model);
                break;
            default:
                echo json_encode(['status' => 'error', 'message' => 'Acción GET no válida.']);
                break;
        }
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
        break;
}
exit;

function registrarDescuento($descuento_model) {
    $idUsuario = $_SESSION['id_usuario'] ?? null;
    if (!$idUsuario) {
        echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado.']);
        return;
    }

    $codigo = $_POST['codigo'] ?? '';
    $valor_descuento = $_POST['valor_descuento'] ?? 0;
    $aplica_a_categoria = $_POST['aplica_a_categoria'] ?? null;
    $aplica_a_marca = $_POST['aplica_a_marca'] ?? null;
    $descripcion = $_POST['descripcion'] ?? '';
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $fecha_fin = $_POST['fecha_fin'] ?? null;
    
    if (empty($codigo) || !is_numeric($valor_descuento) || empty($fecha_inicio)) {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos para registrar el descuento.']);
        return;
    }

    $resultado = $descuento_model->registrarDescuento(
        $codigo, $valor_descuento, 
        $aplica_a_categoria, $aplica_a_marca, $descripcion, 
        $fecha_inicio, $fecha_fin, $idUsuario
    );

    if ($resultado) {
        echo json_encode(['status' => 'success', 'message' => 'Descuento registrado con éxito.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al registrar el descuento. El código podría ya existir.']);
    }
}

function actualizarDescuento($descuento_model) {
    $idUsuario = $_SESSION['id_usuario'] ?? null;
    if (!$idUsuario) {
        echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado.']);
        return;
    }

    $idCodigo = $_POST['id_codigo'] ?? null;
    $codigo = $_POST['codigo'] ?? '';
    $valor_descuento = $_POST['valor_descuento'] ?? 0;
    $aplica_a_categoria = $_POST['aplica_a_categoria'] ?? null;
    $aplica_a_marca = $_POST['aplica_a_marca'] ?? null;
    $descripcion = $_POST['descripcion'] ?? '';
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $fecha_fin = $_POST['fecha_fin'] ?? null;
    $estado = $_POST['estado'] ?? null; // Recibir el estado (0 o 1)

    if (is_null($idCodigo) || empty($codigo) || !is_numeric($valor_descuento) || empty($fecha_inicio) || is_null($estado)) {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos para actualizar el descuento.']);
        return;
    }

    $resultado = $descuento_model->actualizarDescuento(
        $idCodigo, $codigo, $valor_descuento, 
        $aplica_a_categoria, $aplica_a_marca, $descripcion, 
        $fecha_inicio, $fecha_fin, $estado, $idUsuario
    );

    if ($resultado) {
        echo json_encode(['status' => 'success', 'message' => 'Descuento actualizado con éxito.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el descuento. El código podría ya existir o el ID no es válido.']);
    }
}

function eliminarDescuento($descuento_model) {
    $idCodigo = $_POST['id_codigo'] ?? null;

    if (is_null($idCodigo)) {
        echo json_encode(['status' => 'error', 'message' => 'ID de descuento no proporcionado para eliminar.']);
        return;
    }

    $resultado = $descuento_model->eliminarDescuento($idCodigo);

    if ($resultado) {
        echo json_encode(['status' => 'success', 'message' => 'Descuento eliminado con éxito.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al eliminar el descuento.']);
    }
}

function listarDescuentos($descuento_model) {
    $descuentos = $descuento_model->obtenerTodosLosDescuentos();
    echo json_encode(['status' => 'success', 'data' => $descuentos]);
}

function obtenerDescuento($descuento_model) {
    $idCodigo = $_GET['id_codigo'] ?? null;
    if (is_null($idCodigo)) {
        echo json_encode(['status' => 'error', 'message' => 'ID de descuento no proporcionado.']);
        return;
    }
    $descuento = $descuento_model->obtenerDescuentoPorId($idCodigo);
    if ($descuento) {
        echo json_encode(['status' => 'success', 'data' => $descuento]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Descuento no encontrado.']);
    }
}

function listarCategorias($categoria_model) {
    $categorias = $categoria_model->listarCategoriasParaSelect(); 
    echo json_encode(['status' => 'success', 'data' => $categorias]);
}

function listarMarcas($marca_model) {
    $marcas = $marca_model->listarMarcasParaSelect(); 
    echo json_encode(['status' => 'success', 'data' => $marcas]);
}

function notificarDescuento($usuario_model) {
    $idCodigo = $_POST['id_codigo'] ?? null;
    $codigoDescuento = $_POST['codigo_descuento'] ?? null;
    $valorDescuento = $_POST['valor_descuento'] ?? null;

    if (empty($idCodigo) || empty($codigoDescuento) || empty($valorDescuento)) {
        echo json_encode(['status' => 'error', 'message' => 'Datos del descuento incompletos para notificar.']);
        return;
    }

    try {
        // Obtener los usuarios que desean recibir notificaciones
        $usuariosNotificacion = $usuario_model->obtenerUsuariosParaNotificacion();

        if (empty($usuariosNotificacion)) {
            echo json_encode(['status' => 'info', 'message' => 'No hay usuarios suscritos a notificaciones de descuento.']);
            return;
        }

        $correosEnviados = 0;
        $correosFallidos = 0;

        foreach ($usuariosNotificacion as $usuario) {
            $destinatario = $usuario['correo'];
            $nombreDestinatario = $usuario['nombres'] . ' ' . $usuario['apellidos'];
            $asunto = "¡Nuevo Descuento Disponible! Código: " . $codigoDescuento;
            
            // Contenido del correo en formato HTML para un mejor estilo
            $mensajeHtml = '
                <!DOCTYPE html>
                <html lang="es">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>¡Nuevo Descuento de PCBYTE!</title>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; }
                        .header { background-color: #007bff; color: white; padding: 10px 0; text-align: center; border-radius: 8px 8px 0 0; }
                        .content { padding: 20px; }
                        .discount-code { font-size: 24px; font-weight: bold; color: #28a745; text-align: center; margin: 20px 0; padding: 10px; border: 2px dashed #28a745; border-radius: 5px; }
                        .footer { text-align: center; font-size: 12px; color: #777; margin-top: 20px; border-top: 1px solid #eee; padding-top: 10px; }
                        .button { display: inline-block; background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 15px; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <div class="header">
                            <h2>¡Oferta Exclusiva de PCBYTE!</h2>
                        </div>
                        <div class="content">
                            <p>Hola <strong>' . htmlspecialchars($nombreDestinatario) . '</strong>,</p>
                            <p>¡Tenemos una increíble noticia para ti! Un nuevo descuento especial ha llegado a nuestra tienda:</p>
                            <p style="text-align: center; font-size: 18px; color: #555;">Tu código de descuento es:</p>
                            <div class="discount-code">' . htmlspecialchars($codigoDescuento) . '</div>
                            <p style="text-align: center; font-size: 18px; color: #555;">Con un valor de: <strong>' . htmlspecialchars($valorDescuento) . '%</strong></p>
                            <p>No te pierdas esta oportunidad de ahorrar en tus próximas compras. ¡Visita nuestra tienda ahora y aplica este código al finalizar tu pedido!</p>
                            <p style="text-align: center;">
                                <a href="http://utp.equiposh1.sg-host.com/" class="button">Ir a la Tienda</a>
                            </p>
                            <p>¡Esperamos verte pronto!</p>
                        </div>
                        <div class="footer">
                            <p>&copy; ' . date('Y') . ' PCBYTE. Todos los derechos reservados.</p>
                            <p>Si no deseas recibir más notificaciones de descuentos, puedes actualizar tus preferencias en tu perfil.</p>
                        </div>
                    </div>
                </body>
                </html>
            ';

            $mail = new PHPMailer(true);
            try {
                // Configuración del servidor SMTP (tomada de controlador_facturas.php)
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'TUGMAIL@gmail.com'; // Tu Gmail
                $mail->Password = 'CONTRASENA APLICATION'; // Tu Contraseña de aplicación
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Usar TLS
                $mail->Port = 587;

                // Remitente y destinatario
                $mail->setFrom('TUGMAIL@gmail.com', 'PCBYTE - Descuentos Exclusivos');
                $mail->addAddress($destinatario, $nombreDestinatario);

                // Contenido del correo
                $mail->isHTML(true); // Establecer formato de correo a HTML
                $mail->Subject = utf8_decode($asunto);
                $mail->Body = utf8_decode($mensajeHtml);
                $mail->AltBody = strip_tags(str_replace('<br>', "\n", $mensajeHtml)); // Versión de texto plano

                $mail->send();
                $correosEnviados++;
                error_log("Correo de descuento enviado a: " . $destinatario . " para código " . $codigoDescuento);
            } catch (Exception $e) {
                error_log("Error al enviar correo de descuento a {$destinatario}: {$mail->ErrorInfo}");
                $correosFallidos++;
            }
        }

        echo json_encode([
            'status' => 'success',
            'message' => "Notificación enviada a {$correosEnviados} usuarios. Fallos: {$correosFallidos}."
        ]);

    } catch (Exception $e) {
        error_log("Error en notificarDescuento (controlador): " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error interno del servidor al notificar el descuento.']);
    }
}

function validarDescuento($descuento_model) {
    $codigo = $_POST['codigo'] ?? '';
    // $monto_compra = (float)($_POST['monto_compra'] ?? 0); // No se usa directamente en la validación del SP, pero podría ser útil para lógicas más complejas

    if (empty($codigo)) {
        echo json_encode(['status' => 'error', 'message' => 'El código de descuento no puede estar vacío.']);
        return;
    }

    try {
        $descuento_data = $descuento_model->validarCodigoDescuento($codigo);

        if ($descuento_data) {
            // El SP ya validó existencia, estado y fechas
            echo json_encode([
                'status' => 'success',
                'message' => 'Código de descuento válido.',
                'id_codigo' => $descuento_data['idCodigo'],
                'codigo_aplicado' => $descuento_data['codigo'],
                'valor_descuento' => (float)$descuento_data['valor_descuento'] // Asegura que sea float
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Código de descuento no válido, inactivo o expirado.']);
        }
    } catch (Exception $e) {
        error_log("Error en validarDescuento (controlador): " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error interno del servidor al validar el descuento.']);
    }
}
