<?php
session_start();
include 'php/conexion.php';

// Verificar si hay productos en el carrito
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) { 
    header("Location: carrito.php"); 
    exit; 
}

$total = 0;

// Preparar los IDs del carrito de forma segura
$ids = array_map('intval', array_keys($_SESSION['carrito']));
if (empty($ids)) {
    $_SESSION['carrito'] = array();
    header("Location: carrito.php");
    exit;
}

// Crear los placeholders para la consulta
$placeholders = str_repeat('?,', count($ids) - 1) . '?';

// Preparar y ejecutar la consulta
$sql = $conn->prepare("SELECT id, precio FROM productos WHERE id IN ($placeholders)");
if (!$sql) {
    die("Error en la preparación de la consulta: " . $conn->error);
}

// Vincular los parámetros
$types = str_repeat('i', count($ids));
$sql->bind_param($types, ...$ids);

if (!$sql->execute()) {
    die("Error al ejecutar la consulta: " . $sql->error);
}

$result = $sql->get_result();
$productos = $result->fetch_all(MYSQLI_ASSOC);

foreach ($productos as $p) {
    $total += $p['precio'] * $_SESSION['carrito'][$p['id']];
}

// Inicializar variables para información del cliente
$telefono = '';
$email = '';
$nombre = '';
$direccion = '';

// Si el usuario está logueado, obtener su información
if (isset($_SESSION['id'])) {
    $uid = $_SESSION['id'];
    $stmt = $conn->prepare("SELECT email, nombre FROM login WHERE id = ?");
    if (!$stmt) {
        die("Error en la preparación de la consulta de usuario: " . $conn->error);
    }
    
    $stmt->bind_param("i", $uid);
    if (!$stmt->execute()) {
        die("Error al ejecutar la consulta de usuario: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        $email = $user_data['email'] ?? '';
        $nombre = $user_data['nombre'] ?? '';
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Finalizar Compra | Syscotel</title>
  <link rel="shortcut icon" href="img/syscotel.png">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    :root {
      --primary: #4CAF50;
      --primary-dark: #45a049;
      --light: #f9f9f9;
      --bg: #fff;
      --text: #2e2e2e;
      --border: #e1e1e1;
      --error: #e74c3c;
      --success: #2ecc71;
      --warning: #f1c40f;
    }
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body {
      font-family: 'Montserrat', sans-serif;
      background: #f5f5f5;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
      min-height: 100vh;
    }
    .checkout-panel {
      background: var(--bg);
      border-radius: 15px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
      max-width: 900px;
      width: 100%;
      overflow: hidden;
    }
    .panel-body {
      padding: 2.5rem;
    }
    .title {
      font-size: 2rem;
      color: var(--text);
      margin-bottom: 2rem;
      text-align: center;
      font-weight: 600;
    }
    .progress-bar {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      position: relative;
      margin-bottom: 3rem;
      overflow: visible;
    }
    .progress-bar::before {
      content: '';
      position: absolute;
      top: calc(50% - 2px);
      left: 2rem;
      right: 2rem;
      height: 4px;
      background: var(--border);
      z-index: 0;
    }
    .step {
      position: relative;
      z-index: 1;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: var(--border);
      margin: 0 auto;
      transition: all 0.3s ease;
      background-size: 60%;
      background-position: center;
      background-repeat: no-repeat;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .step.completed {
      background-color: var(--primary);
      background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='white'><path d='M9 16.17l-3.88-3.88a1 1 0 1 1 1.41-1.41L9 13.34l8.47-8.47a1 1 0 0 1 1.41 1.41L9 16.17z'/></svg>");
    }
    .step::after {
      position: absolute;
      top: calc(100% + 0.75rem);
      left: 50%;
      transform: translateX(-50%);
      font-size: 0.875rem;
      color: var(--text);
      white-space: nowrap;
      font-weight: 500;
    }
    .step[data-step="1"]::after { content: 'Carrito'; }
    .step[data-step="2"]::after { content: 'Confirmar'; }
    .step[data-step="3"]::after { content: 'Pago'; }
    .step[data-step="4"]::after { content: 'Final'; }
    .payment-method {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1.5rem;
      margin-bottom: 2.5rem;
    }
    .method {
      display: flex;
      align-items: center;
      padding: 1.25rem;
      background: var(--light);
      border: 2px solid transparent;
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .method:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .method.selected {
      border-color: var(--primary);
      background: rgba(76, 175, 80, 0.05);
    }
    .method img {
      height: 28px;
      margin-right: 1rem;
    }
    .purchase-type {
      margin-bottom: 2.5rem;
      background: var(--light);
      padding: 1.5rem;
      border-radius: 12px;
    }
    .purchase-type h3 {
      margin-bottom: 1rem;
      color: var(--text);
      font-size: 1.25rem;
    }
    .purchase-options {
      display: flex;
      gap: 2rem;
    }
    .purchase-options label {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      cursor: pointer;
      font-weight: 500;
    }
    .client-info {
      margin-bottom: 2.5rem;
    }
    .client-info .input-group {
      margin-bottom: 1.5rem;
    }
    .client-info label {
      display: block;
      margin-bottom: 0.75rem;
      color: var(--text);
      font-weight: 500;
    }
    .client-info input {
      width: 100%;
      padding: 1rem;
      border: 2px solid var(--border);
      border-radius: 8px;
      font-size: 1rem;
      transition: all 0.3s ease;
    }
    .client-info input:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
      outline: none;
    }
    .client-info input.warning {
      border-color: var(--error);
      background-color: rgba(231, 76, 60, 0.05);
    }
    .card-details {
      display: none;
      margin-bottom: 2.5rem;
      background: var(--light);
      padding: 1.5rem;
      border-radius: 12px;
    }
    .card-details.visible {
      display: block;
    }
    .card-details .input-group {
      margin-bottom: 1.5rem;
    }
    .card-details input {
      width: 100%;
      padding: 1rem;
      border: 2px solid var(--border);
      border-radius: 8px;
      font-size: 1rem;
      transition: all 0.3s ease;
    }
    .card-details input:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
      outline: none;
    }
    .panel-footer {
      background: var(--light);
      padding: 1.5rem 2.5rem;
      display: flex;
      justify-content: space-between;
      border-top: 1px solid var(--border);
    }
    .btn {
      padding: 1rem 2rem;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .btn.back {
      background: var(--bg);
      color: var(--text);
      border: 2px solid var(--border);
    }
    .btn.back:hover {
      background: var(--light);
      border-color: var(--text);
    }
    .btn.next {
      background: var(--primary);
      color: #fff;
    }
    .btn.next:hover {
      background: var(--primary-dark);
      transform: translateY(-2px);
    }
    #loader {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(255, 255, 255, 0.9);
      align-items: center;
      justify-content: center;
      z-index: 100;
      backdrop-filter: blur(4px);
    }
    #loader .spinner {
      width: 48px;
      height: 48px;
      border: 4px solid var(--light);
      border-top-color: var(--primary);
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    .error-message {
      color: var(--error);
      font-size: 0.875rem;
      margin-top: 0.5rem;
      display: none;
    }
    .input-group.error .error-message {
      display: block;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
  </style>
</head>
<body>
  <div id="loader"><div class="spinner"></div></div>
  <div class="checkout-panel">
    <div class="panel-body">
      <h2 class="title">Finalizar Compra</h2>
      <div class="progress-bar">
        <div class="step completed" data-step="1"></div>
        <div class="step completed" data-step="2"></div>
        <div class="step" data-step="3"></div>
        <div class="step" data-step="4"></div>
      </div>
      <div class="payment-method">
        <label class="method" data-method="tarjeta">
          <img src="https://designmodo.com/demo/checkout-panel/img/visa_logo.png" alt="Visa"><img src="https://designmodo.com/demo/checkout-panel/img/mastercard_logo.png" alt="Mastercard"><input type="radio" name="payment" hidden><span>Pagar $<?php echo number_format($total,2); ?> con Tarjeta</span>
        </label>
        <label class="method" data-method="paypal">
          <img src="https://designmodo.com/demo/checkout-panel/img/paypal_logo.png" alt="PayPal"><input type="radio" name="payment" hidden><span>Pagar $<?php echo number_format($total,2); ?> con PayPal</span>
        </label>
        <label class="method selected" data-method="efectivo">
          <img src="https://cdn-icons-png.flaticon.com/512/2489/2489756.png" alt="Efectivo"><input type="radio" name="payment" hidden checked><span>Pagar $<?php echo number_format($total,2); ?> en Efectivo</span>
        </label>
      </div>
      <div class="shipping-cost" id="shipping-cost" style="display: none; margin: 15px 0; padding: 10px; background: #f9f9f9; border-radius: 5px; text-align: right;">
        <p>Costo de envío: <strong>$5.00</strong></p>
        <p>Total con envío: <strong id="total-with-shipping">$<?php echo number_format($total + 5, 2); ?></strong></p>
      </div>
      <div class="purchase-type">
        <h3>¿Dónde retirarás?</h3>
        <div class="purchase-options" id="purchase-options">
          <label><input type="radio" name="purchase_type" value="online" id="purchase-online">Envío a domicilio</label>
          <label><input type="radio" name="purchase_type" value="local" id="purchase-local" checked>Recoger en tienda</label>
        </div>
      </div>
      <div class="client-info" id="client-info">
        <div class="input-group"><label for="cliente-name">Nombre completo</label><input type="text" id="cliente-name" value="<?php echo htmlspecialchars($nombre); ?>"></div>
        <div class="input-group"><label for="cliente-telefono">Teléfono</label><input type="tel" id="cliente-telefono" value="<?php echo htmlspecialchars($telefono); ?>"></div>
        <div class="input-group"><label for="cliente-email">Correo electrónico</label><input type="email" id="cliente-email" value="<?php echo htmlspecialchars($email); ?>"></div>
        <div class="input-group" id="domicilio-group"><label for="cliente-address">Dirección de envío</label><input type="text" id="cliente-address" value="<?php echo htmlspecialchars($direccion); ?>"></div>
      </div>
      <div class="card-details" id="card-details">
        <div class="input-group"><label for="cardholder">Titular de la tarjeta</label><input type="text" id="cardholder"></div>
        <div class="input-group"><label for="cardnumber">Número de Tarjeta</label><input type="text" id="cardnumber" maxlength="19" placeholder="XXXX XXXX XXXX XXXX"></div>
        <div class="input-group" style="display:flex;gap:1rem;">
          <div style="flex:1;"><label for="expiry">Vencimiento (MM/AA)</label><input type="text" id="expiry" maxlength="5" placeholder="MM/AA"></div>
          <div style="flex:1;"><label for="cvv">CVV</label><input type="password" id="cvv" maxlength="3"></div>
        </div>
      </div>
    </div>
    <div class="panel-footer"><a href="carrito.php" class="btn back">Volver</a><button id="pay-btn" class="btn next">Pagar</button></div>
  </div>
  <script>
  (() => {
    const { jsPDF } = window.jspdf;
    const methods = document.querySelectorAll('.method');
    const loader = document.getElementById('loader');
    const cardDetails = document.getElementById('card-details');
    const payBtn = document.getElementById('pay-btn');
    const domicilioGrp = document.getElementById('domicilio-group');
    const purchaseOptions = document.getElementById('purchase-options');
    let selectedMethod = 'efectivo';

    // Validaciones para El Salvador
    const validarTelefono = (telefono) => {
        const regex = /^[267]\d{7}$/;
        return regex.test(telefono);
    };

    const validarDireccion = (direccion) => {
        return direccion.length >= 10 && direccion.length <= 200;
    };

    const validarNombre = (nombre) => {
        const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,50}$/;
        return regex.test(nombre);
    };

    const validarTarjeta = (numero) => {
        const regex = /^[0-9]{16}$/;
        return regex.test(numero.replace(/\s/g, ''));
    };

    const validarCVV = (cvv) => {
        const regex = /^[0-9]{3}$/;
        return regex.test(cvv);
    };

    const validarFechaVencimiento = (fecha) => {
        const regex = /^(0[1-9]|1[0-2])\/([0-9]{2})$/;
        if (!regex.test(fecha)) return false;
        
        const [mes, anio] = fecha.split('/');
        const hoy = new Date();
        const anioActual = hoy.getFullYear() % 100;
        const mesActual = hoy.getMonth() + 1;
        
        if (parseInt(anio) < anioActual) return false;
        if (parseInt(anio) === anioActual && parseInt(mes) < mesActual) return false;
        
        return true;
    };

    const validarEmail = (email) => {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    };

    // Actualizar opciones de entrega según método de pago
    function actualizarOpcionesEntrega(metodo) {
        const opciones = purchaseOptions.querySelectorAll('label');
        const radioOnline = document.getElementById('purchase-online');
        const radioLocal = document.getElementById('purchase-local');
        
        if (metodo === 'efectivo') {
            // Solo mostrar recoger en tienda para efectivo
            opciones[0].style.display = 'none';
            radioLocal.checked = true;
            domicilioGrp.style.display = 'none';
        } else {
            // Mostrar ambas opciones para tarjeta y PayPal
            opciones[0].style.display = 'flex';
            // No forzar ninguna selección si ya hay una
            if (!radioOnline.checked && !radioLocal.checked) {
                radioOnline.checked = true;
                domicilioGrp.style.display = 'block';
            }
        }
        
        // Asegurarse de que siempre haya una opción seleccionada
        if (!radioOnline.checked && !radioLocal.checked) {
            if (metodo === 'efectivo') {
                radioLocal.checked = true;
            } else {
                radioOnline.checked = true;
            }
        }
        
        // Actualizar la visualización del grupo de dirección
        domicilioGrp.style.display = radioOnline.checked ? 'block' : 'none';
    }

    // Inicializar opciones según método por defecto
    actualizarOpcionesEntrega('efectivo');

    methods.forEach(m => {
        m.addEventListener('click', () => {
            methods.forEach(x => x.classList.remove('selected'));
            m.classList.add('selected');
            selectedMethod = m.dataset.method;
            cardDetails.classList.toggle('visible', selectedMethod === 'tarjeta');
            actualizarOpcionesEntrega(selectedMethod);
        });
    });

    document.querySelectorAll('input[name="purchase_type"]').forEach(radio => {
        radio.addEventListener('change', () => {
            domicilioGrp.style.display = radio.value === 'local' ? 'none' : 'block';
            if (radio.value === 'local') {
                document.getElementById('cliente-address').value = '';
            }
        });
    });

    // Formateo de inputs
    document.getElementById('cardnumber').addEventListener('input', e => {
        let v = e.target.value.replace(/\D/g,'').slice(0,16);
        e.target.value = v.replace(/(.{4})/g,'$1 ').trim();
    });

    document.getElementById('expiry').addEventListener('input', e => {
        let v = e.target.value.replace(/\D/g,'').slice(0,4);
        if (v.length > 2) v = v.slice(0,2) + '/' + v.slice(2);
        e.target.value = v;
    });

    document.getElementById('cliente-telefono').addEventListener('input', e => {
        let v = e.target.value.replace(/\D/g,'').slice(0,8);
        e.target.value = v;
    });

    function mostrarError(input, mensaje) {
        const grupo = input.closest('.input-group');
        grupo.classList.add('error');
        let errorDiv = grupo.querySelector('.error-message');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            grupo.appendChild(errorDiv);
        }
        errorDiv.textContent = mensaje;
    }

    function limpiarError(input) {
        const grupo = input.closest('.input-group');
        grupo.classList.remove('error');
        const errorDiv = grupo.querySelector('.error-message');
        if (errorDiv) errorDiv.textContent = '';
    }

    function validarFormulario() {
        let valid = true;
        const nombre = document.getElementById('cliente-name');
        const telefono = document.getElementById('cliente-telefono');
        const email = document.getElementById('cliente-email');
        const direccion = document.getElementById('cliente-address');
        const esLocal = document.querySelector('input[name="purchase_type"]:checked').value === 'local';

        // Validar nombre
        if (!validarNombre(nombre.value)) {
            mostrarError(nombre, 'El nombre debe contener solo letras y tener entre 3 y 50 caracteres');
            valid = false;
        } else {
            limpiarError(nombre);
        }

        // Validar teléfono
        if (!validarTelefono(telefono.value)) {
            mostrarError(telefono, 'Ingrese un número de teléfono válido de El Salvador (8 dígitos)');
            valid = false;
        } else {
            limpiarError(telefono);
        }

        // Validar email
        if (!validarEmail(email.value)) {
            mostrarError(email, 'Ingrese un correo electrónico válido');
            valid = false;
        } else {
            limpiarError(email);
        }

        // Validar dirección si no es compra local y no es efectivo
        if (!esLocal && selectedMethod !== 'efectivo') {
            if (!validarDireccion(direccion.value)) {
                mostrarError(direccion, 'La dirección debe tener entre 10 y 200 caracteres');
                valid = false;
            } else {
                limpiarError(direccion);
            }
        }

        // Validar datos de tarjeta si es pago con tarjeta
        if (selectedMethod === 'tarjeta') {
            const cardholder = document.getElementById('cardholder');
            const cardnumber = document.getElementById('cardnumber');
            const expiry = document.getElementById('expiry');
            const cvv = document.getElementById('cvv');

            if (!validarNombre(cardholder.value)) {
                mostrarError(cardholder, 'Nombre del titular inválido');
                valid = false;
            } else {
                limpiarError(cardholder);
            }

            if (!validarTarjeta(cardnumber.value)) {
                mostrarError(cardnumber, 'Número de tarjeta inválido');
                valid = false;
            } else {
                limpiarError(cardnumber);
            }

            if (!validarFechaVencimiento(expiry.value)) {
                mostrarError(expiry, 'Fecha de vencimiento inválida');
                valid = false;
            } else {
                limpiarError(expiry);
            }

            if (!validarCVV(cvv.value)) {
                mostrarError(cvv, 'CVV inválido');
                valid = false;
            } else {
                limpiarError(cvv);
            }
        }

        return valid;
    }

    function animateProgressSteps(callback) {
        const steps = document.querySelectorAll('.step');
        let idx = 0;
        (function fill() {
            if (idx < steps.length) {
                steps[idx].classList.add('completed');
                idx++;
                setTimeout(fill, 400);
            } else callback();
        })();
    }

    payBtn.addEventListener('click', () => {
        if (!validarFormulario()) {
            Swal.fire({
                icon: 'error',
                title: 'Por favor, corrija los errores en el formulario',
                confirmButtonColor: 'var(--primary)'
            });
            return;
        }

        const esLocal = document.querySelector('input[name="purchase_type"]:checked').value === 'local';
        let totalFinal = <?php echo $total; ?>;
        
        // Agregar costo de envío si es compra online y no es efectivo
        if (!esLocal && selectedMethod !== 'efectivo') {
            totalFinal += 5.00; // Costo fijo de envío
        }

        const data = {
            total: totalFinal,
            metodo_pago: selectedMethod,
            compra_local: esLocal,
            cliente_nombre: document.getElementById('cliente-name').value,
            cliente_telefono: document.getElementById('cliente-telefono').value,
            cliente_email: document.getElementById('cliente-email').value,
            cliente_direccion: document.getElementById('cliente-address').value || ''
        };

        if (selectedMethod === 'tarjeta') {
            data.tarjeta = {
                titular: document.getElementById('cardholder').value,
                numero: document.getElementById('cardnumber').value.replace(/\s/g, ''),
                vencimiento: document.getElementById('expiry').value,
                cvv: document.getElementById('cvv').value
            };
        }

        Swal.fire({
            title: 'Procesando pago...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        // Verificar los datos antes de enviar
        console.log('Datos a enviar:', JSON.stringify(data));

        animateProgressSteps(() => {
            Swal.fire({
                title: 'Enviando datos...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch('php/procesar_pago.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(res => {
                if (!res.ok) throw new Error('Error en servidor');
                return res.json();
            })
            .then(res => {
                if (res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Pago procesado correctamente!',
                        text: 'Redirigiendo a la factura...',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.href = 'factura.php?pedido_id=' + res.pedido_id;
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: res.message || 'Error al procesar el pago',
                        confirmButtonColor: 'var(--primary)'
                    });
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: err.message,
                    confirmButtonColor: 'var(--primary)'
                });
            });
      });
    });

    function generarFacturaPDF(pedidoId){
      const doc=new jsPDF();
      doc.setFontSize(18);
      doc.text("Factura SYSCOTEL",14,20);
      doc.setFontSize(12);
      doc.text(`Cliente: ${document.getElementById('cliente-name').value}`,14,30);
      if(document.querySelector('input[name="purchase_type"]:checked').value!=='local'){
        doc.text(`Dirección: ${document.getElementById('cliente-address').value}`,14,36);
      }
      doc.text(`Pago con: ${selectedMethod}`,14,42);
      const body=<?php $tabla=[]; foreach($productos as $p){$sql_p=$conn->prepare("SELECT nombre FROM productos WHERE id=?");$sql_p->bind_param("i",$p['id']);$sql_p->execute();$info=$sql_p->get_result()->fetch_assoc();$tabla[]=['nombre'=>$info['nombre'],'cantidad'=>$_SESSION['carrito'][$p['id']],'precio'=>number_format($p['precio'],2),'subtotal'=>number_format($p['precio']*$_SESSION['carrito'][$p['id']],2)];} echo json_encode(array_values($tabla));?>;
      doc.autoTable({startY:50,head:[['Producto','Cantidad','Precio','Subtotal']],body:body.map(r=>[r.nombre,r.cantidad,'$'+r.precio,'$'+r.subtotal])});
      doc.setFontSize(14);
      doc.text(`Total: $<?php echo number_format($total,2);?>`,14,doc.lastAutoTable.finalY+10);
      doc.save(`factura_${pedidoId}_<?php echo date('Ymd_His');?>.pdf`);
    }
  })();
  </script>
</body>
</html>









