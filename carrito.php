<?php
session_start();
include 'php/conexion.php';

error_log("Carrito.php - SESSION: " . print_r($_SESSION, true));

// Inicializar el carrito si no existe
if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
    $_SESSION['carrito'] = array();
}

// Si el usuario está logueado, intentar cargar su carrito de la base de datos
if (isset($_SESSION['id'])) {
    $stmt = $conn->prepare("SELECT producto_id, cantidad FROM carrito WHERE usuario_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $_SESSION['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Si hay productos en la base de datos, actualizar el carrito de la sesión
        if ($result->num_rows > 0) {
            $_SESSION['carrito'] = array();
            while ($row = $result->fetch_assoc()) {
                $_SESSION['carrito'][$row['producto_id']] = $row['cantidad'];
            }
        }
        $stmt->close();
    }
}

$total = 0;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Carrito - SYSCOTEL</title>
    <link rel="stylesheet" href="css/styles.css" />
    <link rel="stylesheet" href="css/styles2.css">
    <link rel="stylesheet" href="css/product.css">
    <link rel="stylesheet" href="css/user.css">
    <link rel="stylesheet" href="css/custom.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="shortcut icon" href="img/syscotel.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
        integrity="sha512-...hash..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include 'header.php'; ?>

    <main>
        <div class="container mt-4">
            <h2 class="text-center mb-4">Carrito de Compras</h2>
            <div class="row">
                <div class="col-md-8">
                    <div class="continue-shopping" data-aos="fade-up">
                        <a href="index.php" class="btn-continue-shopping">
                            <i class="fas fa-arrow-left"></i> Seguir comprando
                        </a>
                    </div>

                    <hr class="separator" data-aos="fade-up">
                    <br>
                    <br>
                    <div class='container-products' data-aos='fade-up'>
                        <?php
                        if (empty($_SESSION['carrito'])) {
                            echo "<p class='no-products-message'>No hay productos en el carrito</p>";
                        } else {
                            foreach ($_SESSION['carrito'] as $id => $cantidad) {
                                error_log("Procesando producto ID: $id, Cantidad: $cantidad");
                                
                                $stmt = $conn->prepare("SELECT * FROM productos WHERE id = ?");
                                $stmt->bind_param("i", $id);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                
                                if ($result && $result->num_rows > 0) {
                                    $producto = $result->fetch_assoc();
                                    $subtotal = $producto['precio'] * $cantidad;
                                    $total += $subtotal;
                                    
                                    $ruta_img = 'img/' . strtolower($producto['seccion']) . '/' . $producto['imagen'];
                                    
                                    $nombre = $producto['nombre'];
                                    if (strlen($nombre) > 25) {
                                        $nombre = substr($nombre, 0, 25) . '...';
                                    }
                                    
                                    echo "
                                    <div class='card-product' data-product-id='{$producto['id']}' onclick='showQuickView(\"{$producto['nombre']}\", \"{$producto['descripcion']}\", \"{$producto['precio']}\", \"$ruta_img\", \"{$producto['seccion']}\", \"{$producto['id']}\")'>
                                        <div class='container-img'>
                                            <img class='dynamic-image' src='$ruta_img' alt='{$producto['nombre']}' />
                                        </div>
                                        <div class='content-card-product'>
                                            <h3>{$nombre}</h3>
                                            <div class='quantity-controls'>
                                                <button class='btn-quantity' onclick='event.stopPropagation(); updateCartQuantity($id, -1)'>-</button>
                                                <span>Cantidad: $cantidad</span>
                                                <button class='btn-quantity' onclick='event.stopPropagation(); updateCartQuantity($id, 1)'>+</button>
                                            </div>
                                            <p class='price'>$" . number_format($producto['precio'], 2) . "</p>
                                            <p>Subtotal: $" . number_format($subtotal, 2) . "</p>
                                            <a href='javascript:void(0)' onclick='event.stopPropagation(); confirmRemoveFromCart($id)' class='btn-remove'>
                                                <i class='fas fa-trash'></i> Eliminar
                                            </a>
                                        </div>
                                    </div>";
                                } else {
                                    error_log("Producto no encontrado para ID: $id");
                                }
                                $stmt->close();
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="cart-total">
                        <h2 class="total-amount">Total: $<?php echo number_format($total, 2); ?></h2>
                        <?php if (!empty($_SESSION['carrito'])): ?>
                            <div class="cart-buttons">
                                <a href="pago.php?total=<?php echo $total; ?>" class="btn-checkout">
                                    Proceder al pago
                                </a>
                                <button onclick="confirmEmptyCart()" class="btn-empty-cart">
                                    <i class="fas fa-trash"></i> Vaciar carrito
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function actualizarCarrito() {
            fetch('php/obtener_carrito.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        location.reload(); 
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    </script>

    <br><br><br><br>
    <footer class="footer">
        <div class="container footer-content">
            <div class="footer-section about">
                <h3>syscotel</h3>
                <p>Bienvenido a syscotel, tu destino definitivo para todo lo relacionado con la electrónica y la tecnología de vanguardia. syscotel se destaca como un oasis de innovación en un mundo digital en constante evolución.</p>
            </div>
            <div class="footer-section contact">
                <h3>Contáctanos</h3>
                <ul>
                    <li><i class="fas fa-phone"></i> (+503) 7850-8218</li>
                    <li><i class="fas fa-envelope"></i> SYSCOTEL@gmail.com</li>
                    <li><i class="fas fa-map-marker-alt"></i> Calle Principal, Ciudad, Principal</li>
                </ul>
            </div>
            <div class="footer-section social">
                <h3>Síguenos</h3>
                <div class="flex justify-center space-x-5">
                    <a href="#" target="_blank" rel="noopener noreferrer">
                        <img src="https://img.icons8.com/fluent/30/000000/facebook-new.png" alt="Facebook" />
                    </a>
                    <a href="#" target="_blank" rel="noopener noreferrer">
                        <img src="https://img.icons8.com/fluent/30/000000/twitter.png" alt="Twitter" />
                    </a>
                    <a href="#" target="_blank" rel="noopener noreferrer">
                        <img src="https://img.icons8.com/fluent/30/000000/instagram-new.png" alt="Instagram" />
                    </a>
                </div>
            </div>
        </div>
        <div class="footer-bar">
            <p>&copy; 2025 syscotel. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>
</body>

</html>

<div id="quickView" class="modal" onclick="closeQuickView()">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="quickView-bg">
            <div id="quickViewImageContainer" class="container-img">
                <div id="quickViewImage"></div>
            </div>
            <div class="quickView-details">
                <h2 id="quickViewTitle"></h2>
                <p id="quickViewDescription"></p>
                <p id="quickViewPrice"></p>
                <div class="btn-container">
                    <button onclick="closeQuickView()" class="btn-close-modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function updateCartQuantity(productId, change) {
        // Get current quantity
        const quantitySpan = document.querySelector(`.card-product[data-product-id='${productId}'] .quantity-controls span`);
        const currentQuantity = parseInt(quantitySpan.textContent.replace('Cantidad: ', ''));
        
        // If trying to decrease from 1, show confirmation dialog
        if (currentQuantity === 1 && change < 0) {
            confirmRemoveFromCart(productId);
            return;
        }
        
        fetch('php/actualizar_cantidad.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `producto_id=${productId}&cambio=${change}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'error') {
                Swal.fire({
                    icon: 'error',
                    title: '<span style="font-size: 24px;">Error</span>',
                    html: `<div style="font-size: 18px;">${data.message}</div>`,
                    confirmButtonColor: '#007bff',
                    customClass: {
                        popup: 'swal-large-text'
                    }
                });
            } else {
                // Update UI without reloading
                updateCartUI(data);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: '<span style="font-size: 24px;">Error</span>',
                html: '<div style="font-size: 18px;">Error al actualizar la cantidad</div>',
                confirmButtonColor: '#007bff',
                customClass: {
                    popup: 'swal-large-text'
                }
            });
        });
    }
    
    function updateCartUI(data) {
        // Update total amount
        document.querySelector('.total-amount').innerText = 'Total: $' + parseFloat(data.total).toFixed(2);
        
        // If cart is empty, update UI accordingly
        if (data.carrito_vacio) {
            document.querySelector('.container-products').innerHTML = "<p class='no-products-message'>No hay productos en el carrito</p>";
            const cartButtons = document.querySelector('.cart-buttons');
            if (cartButtons) {
                cartButtons.style.display = 'none';
            }
            return;
        }
        
        // Update product quantities and subtotals
        for (const productId in data.carrito) {
            const productCard = document.querySelector(`.card-product[data-product-id='${productId}']`);
            if (productCard) {
                const quantity = data.carrito[productId];
                const quantitySpan = productCard.querySelector('.quantity-controls span');
                const priceElement = productCard.querySelector('.price');
                const price = parseFloat(priceElement.innerText.replace('$', '').replace(',', ''));
                const subtotalElement = productCard.querySelector('p:nth-of-type(2)');
                
                quantitySpan.innerText = 'Cantidad: ' + quantity;
                subtotalElement.innerText = 'Subtotal: $' + (price * quantity).toFixed(2);
            }
        }
        
        // Show success toast
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true
        });
        
        Toast.fire({
            icon: 'success',
            title: 'Carrito actualizado'
        });
    }

    function confirmRemoveFromCart(productId) {
        Swal.fire({
            icon: 'warning',
            title: '<span style="font-size: 24px;">¿Eliminar producto?</span>',
            html: '<div style="font-size: 18px;">¿Estás seguro de que deseas eliminar este producto del carrito?</div>',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<span style="font-size: 18px;">Sí, eliminar</span>',
            cancelButtonText: '<span style="font-size: 18px;">Cancelar</span>',
            customClass: {
                popup: 'swal-large-text'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('php/eliminar_carrito.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `producto_id=${productId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Remove product from UI
                        const productCard = document.querySelector(`.card-product[data-product-id="${productId}"]`);
                        if (productCard) {
                            productCard.remove();
                        }
                        
                        // Update total
                        document.querySelector('.total-amount').innerText = 'Total: $' + parseFloat(data.total).toFixed(2);
                        
                        // Check if cart is empty
                        if (data.carrito_vacio) {
                            document.querySelector('.container-products').innerHTML = "<p class='no-products-message'>No hay productos en el carrito</p>";
                            
                            // Hide buttons
                            const cartButtons = document.querySelector('.cart-buttons');
                            if (cartButtons) {
                                cartButtons.style.display = 'none';
                            }
                        }
                        
                        // Show success message
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 1500,
                            timerProgressBar: true
                        });
                        
                        Toast.fire({
                            icon: 'success',
                            title: 'Producto eliminado del carrito'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '<span style="font-size: 24px;">Error</span>',
                            html: `<div style="font-size: 18px;">${data.message}</div>`,
                            confirmButtonColor: '#007bff',
                            customClass: {
                                popup: 'swal-large-text'
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: '<span style="font-size: 24px;">Error</span>',
                        html: '<div style="font-size: 18px;">Error al eliminar el producto</div>',
                        confirmButtonColor: '#007bff',
                        customClass: {
                            popup: 'swal-large-text'
                        }
                    });
                });
            }
        });
    }

    let currentImages = [];
    let currentImageIndex = 0;

    function incrementModalQuantity() {
        const input = document.getElementById('modalQuantity');
        const currentValue = parseInt(input.value);
        if (currentValue < 99) {
            input.value = currentValue + 1;
        }
    }

    function decrementModalQuantity() {
        const input = document.getElementById('modalQuantity');
        const currentValue = parseInt(input.value);
        if (currentValue > 1) {
            input.value = currentValue - 1;
        }
    }

    function showQuickView(title, description, price, image, seccion, id) {
        let truncatedDescription = description;
        if (description.length > 100) {
            truncatedDescription = description.substring(0, 100) + '...';
        }
        
        document.getElementById('quickViewTitle').innerText = title;
        document.getElementById('quickViewDescription').innerText = truncatedDescription;
        document.getElementById('quickViewPrice').innerText = 'Precio: $' + price;
        
        var quickViewImage = document.getElementById('quickViewImage');
        quickViewImage.innerHTML = "<img class='dynamic-image' src='" + image + "' alt='Imagen del producto' />";
        
        document.getElementById('quickView').style.display = 'block';
    }

    function closeQuickView() {
        document.getElementById('quickView').style.display = 'none';
    }

    function quickViewAddToCart() {
        const id = document.getElementById('quickViewProductId').value;
        const quantity = parseInt(document.getElementById('modalQuantity').value);
        
        if (isNaN(quantity) || quantity < 1) {
            Swal.fire({
                icon: 'error',
                title: '<span style="font-size: 24px;">Error</span>',
                html: '<div style="font-size: 18px;">Por favor, ingrese una cantidad válida.</div>',
                confirmButtonColor: '#007bff',
                customClass: {
                    popup: 'swal-large-text'
                }
            });
            return;
        }
        
        fetch('php/agregar_carrito.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'id=' + id + '&cantidad=' + quantity
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                updateCartDisplay(data.carrito);
                closeQuickView();
                Swal.fire({
                    icon: 'success',
                    title: '<span style="font-size: 24px;">Producto agregado</span>',
                    html: '<div style="font-size: 18px;">El producto ha sido agregado al carrito.</div>',
                    confirmButtonColor: '#007bff',
                    customClass: {
                        popup: 'swal-large-text'
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: '<span style="font-size: 24px;">Error</span>',
                    html: '<div style="font-size: 18px;">' + data.message + '</div>',
                    confirmButtonColor: '#007bff',
                    customClass: {
                        popup: 'swal-large-text'
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: '<span style="font-size: 24px;">Error</span>',
                html: '<div style="font-size: 18px;">Error de conexión</div>',
                confirmButtonColor: '#007bff',
                customClass: {
                    popup: 'swal-large-text'
                }
            });
        });
    }

    function confirmEmptyCart() {
        Swal.fire({
            icon: 'warning',
            title: '<span style="font-size: 24px;">¿Vaciar carrito?</span>',
            html: '<div style="font-size: 18px;">¿Estás seguro de que deseas vaciar todo el carrito?</div>',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<span style="font-size: 18px;">Sí, vaciar</span>',
            cancelButtonText: '<span style="font-size: 18px;">Cancelar</span>',
            customClass: {
                popup: 'swal-large-text'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('php/vaciar_carrito.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Actualizar la interfaz
                        const container = document.querySelector('.container-products');
                        if (container) {
                            container.innerHTML = "<p class='no-products-message'>No hay productos en el carrito</p>";
                        }
                        
                        // Ocultar los botones
                        const cartButtons = document.querySelector('.cart-buttons');
                        if (cartButtons) {
                            cartButtons.style.display = 'none';
                        }
                        
                        // Actualizar el total
                        const totalAmount = document.querySelector('.total-amount');
                        if (totalAmount) {
                            totalAmount.innerText = 'Total: $0.00';
                        }

                        // Mostrar mensaje de éxito
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 1500,
                            timerProgressBar: true
                        });
                        
                        Toast.fire({
                            icon: 'success',
                            title: 'Carrito vaciado correctamente'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '<span style="font-size: 24px;">Error</span>',
                            html: `<div style="font-size: 18px;">${data.message}</div>`,
                            confirmButtonColor: '#007bff',
                            customClass: {
                                popup: 'swal-large-text'
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: '<span style="font-size: 24px;">Error</span>',
                        html: '<div style="font-size: 18px;">Error al vaciar el carrito</div>',
                        confirmButtonColor: '#007bff',
                        customClass: {
                            popup: 'swal-large-text'
                        }
                    });
                });
            }
        });
    }
</script>

<style>
    /* Estilos generales para textos */
    h2.text-center {
        font-size: 2.5rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 2rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .quantity-controls {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 20px 0;
        gap: 15px;
    }

    .quantity-controls span {
        font-size: 1.4rem;
        font-weight: 500;
        color: #2c3e50;
    }

    .btn-quantity {
        background: linear-gradient(135deg, #6187ca, #446eaa);
        color: white;
        border: none;
        border-radius: 50%;
        width: 42px;
        height: 42px;
        font-size: 24px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .btn-quantity:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        background: linear-gradient(135deg, #446eaa, #2c4b7c);
    }

    .btn-quantity:active {
        transform: translateY(0);
    }

    .btn-remove {
        display: block;
        background: linear-gradient(135deg, #ff4444, #cc0000);
        color: #ffffff !important;
        padding: 15px 30px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 1.3rem;
        transition: all 0.3s ease;
        font-weight: 600;
        margin: 20px auto;
        width: fit-content;
        text-align: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .btn-remove:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        background: linear-gradient(135deg, #cc0000, #990000);
        text-decoration: none;
        color: #ffffff !important;
    }

    .btn-remove:active {
        transform: translateY(0);
    }

    .card-product {
        max-width: 340px;
        width: 100%;
        background: #ffffff;
        padding: 2.5rem;
        border-radius: 15px;
        cursor: pointer;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        margin: 0 auto;
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.05);
    }

    .card-product:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    .container-img {
        position: relative;
        width: 100%;
        height: 240px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        border-radius: 12px;
        margin-bottom: 25px;
        padding: 20px;
    }

    .container-img img {
        max-width: 90%;
        max-height: 90%;
        width: auto;
        height: auto;
        object-fit: contain;
        transition: transform 0.5s ease;
    }

    .card-product:hover .container-img img {
        transform: scale(1.05);
    }

    .container-products {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 35px;
        width: 95%;
        margin: 0 auto;
        padding: 25px 0;
    }

    .content-card-product h3 {
        font-size: 1.6rem;
        color: #2c3e50;
        margin-bottom: 20px;
        font-weight: 600;
        line-height: 1.4;
    }

    .price {
        font-size: 1.7rem;
        color: #2c3e50;
        font-weight: 700;
        margin: 20px 0;
    }

    .cart-total {
        background: #ffffff;
        padding: 35px;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        position: sticky;
        top: 20px;
    }

    .total-amount {
        font-size: 2.5rem;
        color: #2c3e50;
        font-weight: 700;
        margin-bottom: 30px;
        text-align: center;
    }

    .btn-checkout {
        display: block;
        width: 100%;
        padding: 20px;
        background: linear-gradient(135deg, #4CAF50, #45a049);
        color: white;
        text-align: center;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 1.5rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .btn-checkout:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        background: linear-gradient(135deg, #45a049, #3d8b40);
        text-decoration: none;
        color: white;
    }

    .btn-checkout:active {
        transform: translateY(0);
    }

    .continue-shopping {
        margin-bottom: 30px;
    }

    .btn-continue-shopping {
        display: inline-flex;
        align-items: center;
        padding: 18px 35px;
        background: linear-gradient(135deg, #6187ca, #446eaa);
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1.4rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .btn-continue-shopping:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        background: linear-gradient(135deg, #446eaa, #2c4b7c);
        text-decoration: none;
        color: white;
    }

    .btn-continue-shopping i {
        margin-right: 12px;
        font-size: 1.4rem;
    }

    .separator {
        border: none;
        height: 3px;
        background: linear-gradient(to right, transparent, #6187ca, transparent);
        margin: 35px 0;
    }

    .no-products-message {
        text-align: center;
        font-size: 1.8rem;
        color: #666;
        padding: 50px;
        background: #f8f9fa;
        border-radius: 12px;
        margin: 25px 0;
        font-weight: 500;
    }

    /* Estilos para el subtotal */
    .content-card-product p:nth-of-type(2) {
        font-size: 1.5rem;
        color: #2c3e50;
        font-weight: 600;
        margin: 15px 0;
    }

    /* Ajuste del contenedor principal */
    .container.mt-4 {
        padding: 2rem;
    }

    /* Ajuste del espaciado general */
    main {
        padding: 2rem 0;
    }

    /* Estilos para los botones del carrito */
    .cart-buttons {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .btn-empty-cart {
        display: block;
        width: 100%;
        padding: 20px;
        background: linear-gradient(135deg, #ff4444, #cc0000);
        color: white;
        text-align: center;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 1.5rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        border: none;
        cursor: pointer;
    }

    .btn-empty-cart:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        background: linear-gradient(135deg, #cc0000, #990000);
        text-decoration: none;
        color: white;
    }

    .btn-empty-cart:active {
        transform: translateY(0);
    }

    .btn-empty-cart i {
        margin-right: 10px;
    }
</style>



