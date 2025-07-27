<?php
// view/compra.php

// Calcular el total desde el carrito
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total = 0.00;
foreach ($cart as $item) {
    $total += $item['precio'] * $item['cantidad'];
}
?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Simulación de Pago</h2>

    <?php if (empty($cart)): ?>
        <div class="alert alert-warning text-center">Tu carrito está vacío. Agrega productos antes de comprar.</div>
        <div class="text-center mt-3">
            <a href="index.php?page=view/productos.php" class="btn btn-primary">Ir a la tienda</a>
        </div>
    <?php else: ?>
        <form action="index.php?page=view/resumen_pago.php" method="POST" id="formPago">
            <input type="hidden" name="id_usuario" value="<?php echo $_SESSION['id_usuario'] ?? ''; ?>">
            <input type="hidden" name="productos_json" value='<?php echo json_encode(array_values($cart)); ?>'>
            
            <!-- Campo oculto para el ID del código de descuento aplicado -->
            <input type="hidden" name="id_codigo_descuento_aplicado" id="id_codigo_descuento_aplicado" value="">
            <!-- Campo oculto para el monto del descuento aplicado -->
            <input type="hidden" name="monto_descuento_aplicado" id="monto_descuento_aplicado" value="0.00">

            <div class="mb-4">
                <label class="form-label fw-bold">Subtotal del Carrito:</label>
                <input type="text" class="form-control" id="subtotal_carrito_display" value="S/<?php echo number_format($total, 2); ?>" readonly>
                <!-- Campo oculto para enviar el subtotal original del carrito -->
                <input type="hidden" name="subtotal_carrito_raw" id="subtotal_carrito_raw" value="<?php echo $total; ?>">
            </div>

            <!-- Sección de Código de Descuento -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Código de Descuento</h5>
                </div>
                <div class="card-body">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="codigo_descuento_input" placeholder="Ingresa tu código de descuento">
                        <button class="btn btn-primary" type="button" id="aplicar_descuento_btn">Aplicar</button>
                    </div>
                    <div id="descuento_feedback" class="mb-2"></div>
                    <div id="descuento_aplicado_display" class="fw-bold text-success d-none">
                        Descuento Aplicado: <span id="valor_descuento_display">S/0.00</span>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Total a Pagar (con descuento):</label>
                <!-- Campo de texto para la visualización del total final -->
                <input type="text" class="form-control" id="monto_total_final_display_visual" value="S/<?php echo number_format($total, 2); ?>" readonly>
                <!-- Campo oculto para el valor numérico del total final que se enviará al backend -->
                <input type="hidden" name="monto_total" id="monto_total_final_hidden" value="<?php echo $total; ?>">
            </div>

            <div class="mb-4">
                <label for="metodo_pago" class="form-label fw-bold">Selecciona un método de pago:</label>
                <select class="form-select" id="metodo_pago" name="metodo_pago" required>
                    <option value="">-- Elige un método --</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="yape">Yape</option>
                    <option value="paypal">PayPal</option>
                </select>
            </div>

            <!-- Tarjeta -->
            <div id="pagoTarjeta" class="d-none">
                <div class="mb-3">
                    <label class="form-label">Número de Tarjeta</label>
                    <input type="text" name="numero_tarjeta" id="tarjeta-numero" class="form-control" maxlength="19" placeholder="1234 5678 9012 3456">
                </div>
                <div class="mb-3 row">
                    <div class="col">
                        <label class="form-label">Fecha de Vencimiento</label>
                        <input type="month" name="fecha_vencimiento" id="tarjeta-vencimiento" class="form-control">
                    </div>
                    <div class="col">
                        <label class="form-label">CVV</label>
                        <input type="text" name="cvv" id="tarjeta-cvv" class="form-control" maxlength="4" placeholder="123">
                    </div>
                </div>
            </div>

            <!-- YAPE -->
            <div class="d-none metodo-pago-form" id="form-yape">
                <p>Escanea el código QR con Yape para completar tu pago:</p>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=Paga%20con%20Yape" alt="QR Yape" class="img-thumbnail">
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="confirmYape">
                    <label class="form-check-label" for="confirmYape">
                        He escaneado el código QR y completado el pago.
                    </label>
                </div>
            </div>

            <!-- PAYPAL -->
            <div class="d-none metodo-pago-form" id="form-paypal">
                <p>Escanea este código para pagar con PayPal (simulado):</p>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=https%3A%2F%2Fpaypal.com%2Fpago_simulado" alt="QR PayPal" class="img-thumbnail">
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="confirmPaypal">
                    <label class="form-check-label" for="confirmPaypal">
                        He escaneado el código QR y completado el pago.
                    </label>
                </div>
            </div>

            <!-- Botón de Pagar -->
            <div class="mt-4 text-center">
                <button type="submit" class="btn btn-success">Pagar</button>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
    // URL del controlador de descuentos (ajusta si es necesario)
    const CONTROLADOR_DESCUENTOS_URL = '../controller/controlador_descuentos.php';

    document.addEventListener('DOMContentLoaded', function() {
        const formPago = document.getElementById('formPago');
        const metodoPagoSelect = document.getElementById('metodo_pago');
        const pagoTarjetaDiv = document.getElementById('pagoTarjeta');
        const formYapeDiv = document.getElementById('form-yape');
        const formPaypalDiv = document.getElementById('form-paypal');

        const subtotalCarritoRawInput = document.getElementById('subtotal_carrito_raw');
        const montoTotalFinalVisual = document.getElementById('monto_total_final_display_visual'); // Campo visual
        const montoTotalFinalHidden = document.getElementById('monto_total_final_hidden'); // Campo oculto para enviar
        const codigoDescuentoInput = document.getElementById('codigo_descuento_input');
        const aplicarDescuentoBtn = document.getElementById('aplicar_descuento_btn');
        const descuentoFeedback = document.getElementById('descuento_feedback');
        const descuentoAplicadoDisplay = document.getElementById('descuento_aplicado_display');
        const valorDescuentoDisplay = document.getElementById('valor_descuento_display');
        const idCodigoDescuentoAplicadoInput = document.getElementById('id_codigo_descuento_aplicado');
        const montoDescuentoAplicadoInput = document.getElementById('monto_descuento_aplicado');

        let currentTotal = parseFloat(subtotalCarritoRawInput.value); // El total inicial es el subtotal del carrito

        // Función para actualizar el total final mostrado y el campo oculto
        function updateFinalTotalDisplay() {
            montoTotalFinalVisual.value = `S/${currentTotal.toFixed(2)}`;
            montoTotalFinalHidden.value = currentTotal.toFixed(2); // Actualiza el campo oculto con el valor numérico
        }

        // Inicializar el total final al cargar la página
        updateFinalTotalDisplay();

        // Evento para aplicar descuento
        aplicarDescuentoBtn.addEventListener('click', function() {
            const codigo = codigoDescuentoInput.value.trim();
            if (codigo === '') {
                descuentoFeedback.innerHTML = '<div class="alert alert-warning">Ingresa un código de descuento.</div>';
                return;
            }

            // Ocultar mensajes anteriores
            descuentoFeedback.innerHTML = '';
            descuentoAplicadoDisplay.classList.add('d-none');
            valorDescuentoDisplay.textContent = 'S/0.00';
            idCodigoDescuentoAplicadoInput.value = '';
            montoDescuentoAplicadoInput.value = '0.00';
            currentTotal = parseFloat(subtotalCarritoRawInput.value); // Resetear total antes de aplicar nuevo descuento
            updateFinalTotalDisplay();

            fetch(CONTROLADOR_DESCUENTOS_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `accion=validar_descuento&codigo=${encodeURIComponent(codigo)}&monto_compra=${currentTotal}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const descuentoPorcentaje = parseFloat(data.valor_descuento);
                    const montoDescuentoCalculado = currentTotal * (descuentoPorcentaje / 100);
                    
                    currentTotal -= montoDescuentoCalculado;
                    
                    descuentoFeedback.innerHTML = `<div class="alert alert-success">Código de descuento aplicado: ${data.codigo_aplicado} (${descuentoPorcentaje.toFixed(2)}%).</div>`;
                    valorDescuentoDisplay.textContent = `S/${montoDescuentoCalculado.toFixed(2)}`;
                    descuentoAplicadoDisplay.classList.remove('d-none');
                    
                    // Guardar el ID del código y el monto del descuento para el envío del formulario
                    idCodigoDescuentoAplicadoInput.value = data.id_codigo;
                    montoDescuentoAplicadoInput.value = montoDescuentoCalculado.toFixed(2);

                } else {
                    descuentoFeedback.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                    // Asegurarse de que no se aplique ningún descuento si falla la validación
                    idCodigoDescuentoAplicadoInput.value = '';
                    montoDescuentoAplicadoInput.value = '0.00';
                }
                updateFinalTotalDisplay(); // Actualizar el total mostrado y el campo oculto
            })
            .catch(error => {
                console.error('Error al validar descuento:', error);
                descuentoFeedback.innerHTML = '<div class="alert alert-danger">Error de conexión al validar el descuento.</div>';
                // Asegurarse de que no se aplique ningún descuento si hay un error de red
                idCodigoDescuentoAplicadoInput.value = '';
                montoDescuentoAplicadoInput.value = '0.00';
                currentTotal = parseFloat(subtotalCarritoRawInput.value); // Resetear total
                updateFinalTotalDisplay();
            });
        });

        // Evento para cambiar el método de pago
        metodoPagoSelect.addEventListener('change', function () {
            pagoTarjetaDiv.classList.add('d-none');
            formYapeDiv.classList.add('d-none');
            formPaypalDiv.classList.add('d-none');

            // Resetear validaciones de campos de pago
            const allPaymentInputs = document.querySelectorAll('#pagoTarjeta input, #form-yape input, #form-paypal input');
            allPaymentInputs.forEach(input => {
                input.required = false; // Quitar required por defecto
                input.classList.remove('is-invalid'); // Limpiar clases de validación
            });
            document.getElementById('confirmYape').checked = false;
            document.getElementById('confirmPaypal').checked = false;

            if (this.value === 'tarjeta') {
                pagoTarjetaDiv.classList.remove('d-none');
                document.getElementById('tarjeta-numero').required = true;
                document.getElementById('tarjeta-vencimiento').required = true;
                document.getElementById('tarjeta-cvv').required = true;
            } else if (this.value === 'yape') {
                formYapeDiv.classList.remove('d-none');
                document.getElementById('confirmYape').required = true;
            } else if (this.value === 'paypal') {
                formPaypalDiv.classList.remove('d-none');
                document.getElementById('confirmPaypal').required = true;
            }
        });

        // Validaciones del formulario al enviar
        formPago.addEventListener('submit', function(event) {
            const metodo = metodoPagoSelect.value;
            let isValid = true;

            // Limpiar validaciones anteriores
            const allInputs = formPago.querySelectorAll('input, select');
            allInputs.forEach(input => input.classList.remove('is-invalid'));
            descuentoFeedback.innerHTML = ''; // Limpiar feedback de descuento

            if (!metodo) {
                alert('Selecciona un método de pago');
                metodoPagoSelect.classList.add('is-invalid');
                isValid = false;
            }

            if (metodo === 'tarjeta') {
                const numero = document.getElementById('tarjeta-numero').value.replace(/\s/g, '').trim();
                const vencimiento = document.getElementById('tarjeta-vencimiento').value;
                const cvv = document.getElementById('tarjeta-cvv').value.trim();

                if (!/^\d{16}$/.test(numero)) {
                    alert('El número de tarjeta debe tener exactamente 16 dígitos numéricos.');
                    document.getElementById('tarjeta-numero').classList.add('is-invalid');
                    isValid = false;
                }
                if (!/^\d{3,4}$/.test(cvv)) {
                    alert('El CVV debe tener 3 o 4 dígitos numéricos.');
                    document.getElementById('tarjeta-cvv').classList.add('is-invalid');
                    isValid = false;
                }
                if (!vencimiento) {
                    alert('Selecciona la fecha de vencimiento.');
                    document.getElementById('tarjeta-vencimiento').classList.add('is-invalid');
                    isValid = false;
                } else {
                    const hoy = new Date();
                    const venc = new Date(vencimiento + '-01');
                    if (venc < hoy) {
                        alert('La tarjeta está vencida. Selecciona una fecha válida.');
                        document.getElementById('tarjeta-vencimiento').classList.add('is-invalid');
                        isValid = false;
                    }
                }
            } else if (metodo === 'yape') {
                if (!document.getElementById('confirmYape').checked) {
                    alert('Debes confirmar que realizaste el pago por Yape.');
                    isValid = false;
                }
            } else if (metodo === 'paypal') {
                if (!document.getElementById('confirmPaypal').checked) {
                    alert('Debes confirmar que realizaste el pago por PayPal.');
                    isValid = false;
                }
            }

            if (!isValid) {
                event.preventDefault();
            }
        });

        // Formateo de número de tarjeta y solo números en CVV
        document.getElementById('tarjeta-numero').addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.slice(0, 16);
            let formatted = '';
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) {
                    formatted += ' ';
                }
                formatted += value[i];
            }
            e.target.value = formatted;
        });

        document.getElementById('tarjeta-cvv').addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '');
        });
    });
</script>
