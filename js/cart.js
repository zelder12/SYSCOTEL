// Función para guardar el carrito en localStorage
function saveCartToLocalStorage(cart) {
    localStorage.setItem('cart', JSON.stringify(cart));
}

// Función para obtener el carrito de localStorage
function getCartFromLocalStorage() {
    const cart = localStorage.getItem('cart');
    return cart ? JSON.parse(cart) : {};
}

// Función para sincronizar el carrito con el servidor
async function syncCartWithServer() {
    if (!isUserLoggedIn()) return;
    
    const localCart = getCartFromLocalStorage();
    if (Object.keys(localCart).length === 0) return;

    try {
        // Enviar cada producto del carrito local al servidor
        for (const [productId, quantity] of Object.entries(localCart)) {
            await fetch('php/agregar_carrito.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `producto_id=${productId}&cantidad=${quantity}`
            });
        }

        // Limpiar el carrito local después de sincronizar
        localStorage.removeItem('cart');
        updateCartDisplay();
    } catch (error) {
        console.error('Error al sincronizar el carrito:', error);
        showErrorMessage('Error al sincronizar el carrito');
    }
}

// Función para agregar producto al carrito
async function addToCart(productId, quantity = 1) {
    if (!isUserLoggedIn()) {
        showErrorMessage('Debes iniciar sesión para agregar productos al carrito');
        return;
    }

            try {
        const response = await fetch('php/agregar_carrito.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `producto_id=${productId}&cantidad=${quantity}`
        });

        const data = await response.json();
                
        if (data.status === 'success') {
            incrementCartCount(quantity, true);
            showSuccessMessage(`Se han agregado ${quantity} unidad(es) al carrito`);
            await syncCartWithServer();
                } else {
            showErrorMessage(data.message || 'Error al agregar el producto al carrito');
        }
    } catch (error) {
        console.error('Error:', error);
        showErrorMessage('Error al comunicarse con el servidor');
    }
}

// Función para verificar si el usuario está logueado
function isUserLoggedIn() {
    return document.body.classList.contains('logged-in');
}

// Función para actualizar la visualización del carrito
function updateCartDisplay() {
    if (isUserLoggedIn()) {
        // Si el usuario está logueado, obtener el carrito del servidor
        fetch('php/obtener_carrito.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    setCartCount(data.total_items);
                    if (data.carrito) {
                        updateCartItems(data.carrito);
                    }
                }
            })
            .catch(error => console.error('Error:', error));
    } else {
        // Si no está logueado, obtener el carrito de localStorage
        const cart = getCartFromLocalStorage();
        setCartCount(Object.values(cart).reduce((a, b) => a + b, 0));
        updateCartItems(cart);
    }
}

// Función para actualizar los items del carrito en la interfaz
function updateCartItems(cart) {
    const cartContainer = document.querySelector('.container-products');
    if (!cartContainer) return;

    if (Object.keys(cart).length === 0) {
        cartContainer.innerHTML = "<p class='no-products-message'>No hay productos en el carrito</p>";
        return;
    }

    // Obtener los detalles de los productos
    fetch('php/obtener_productos_carrito.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(cart)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            let html = '';
            let total = 0;

            data.productos.forEach(producto => {
                const cantidad = cart[producto.id];
                const subtotal = producto.precio * cantidad;
                total += subtotal;

                html += `
                    <div class='card-product' data-product-id='${producto.id}'>
                        <div class='container-img'>
                            <img src='${producto.imagen}' alt='${producto.nombre}' />
                        </div>
                        <div class='content-card-product'>
                            <h3>${producto.nombre}</h3>
                            <div class='quantity-controls'>
                                <button onclick='updateCartQuantity(${producto.id}, -1)'>-</button>
                                <span>${cantidad}</span>
                                <button onclick='updateCartQuantity(${producto.id}, 1)'>+</button>
                            </div>
                            <p class='price'>$${producto.precio}</p>
                            <p>Subtotal: $${subtotal}</p>
                            <button onclick='removeFromCart(${producto.id})' class='btn-remove'>
                                <i class='fas fa-trash'></i> Eliminar
                            </button>
                        </div>
                    </div>`;
            });

            cartContainer.innerHTML = html;
            document.querySelector('.total-amount').textContent = `Total: $${total.toFixed(2)}`;
            }
    })
    .catch(error => console.error('Error:', error));
}

// Función para mostrar mensaje de éxito
function showSuccessMessage(message) {
            Swal.fire({
        icon: 'success',
        title: message,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
            });
        }

// Función para mostrar mensaje de error
function showErrorMessage(message) {
        Swal.fire({
            icon: 'error',
        title: message,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
        });
}

// Función para actualizar la cantidad de un producto
function updateCartQuantity(productId, change) {
    const cart = getCartFromLocalStorage();
    const item = cart.find(item => item.id === productId);
    
    if (item) {
        const newQuantity = item.quantity + change;
        if (newQuantity > 0) {
            item.quantity = newQuantity;
            saveCartToLocalStorage(cart);
            updateCartDisplay();
            incrementCartCount(change, true);
        } else if (newQuantity <= 0) {
            removeFromCart(productId);
        }
    }
}

// Función para eliminar un producto del carrito
function removeFromCart(productId) {
    const cart = getCartFromLocalStorage();
    const item = cart.find(item => item.id === productId);
    
    if (item) {
        const index = cart.indexOf(item);
        cart.splice(index, 1);
        saveCartToLocalStorage(cart);
        updateCartDisplay();
        incrementCartCount(-item.quantity, true);
}
}

// Función para mostrar la vista rápida del producto
function showQuickView(title, description, price, image, seccion, id) {
    let imagePath = 'img/';
    
    switch(seccion) {
        case 'Perifericos':
        imagePath += 'perifericos/';
            break;
        case 'Consolas':
        imagePath += 'consolas/';
            break;
        case 'Equipos':
        imagePath += 'equipos/';
            break;
        case 'Seguridad':
        imagePath += 'seguridad/';
            break;
        case 'Unidades':
        imagePath += 'unidades/';
            break;
        case 'Varios':
        imagePath += 'varios/';
            break;
        case 'Audifonos':
        imagePath += 'audifonos/';
            break;
        case 'Celulares':
        imagePath += 'celulares/';
            break;
        case 'Gadgets':
        imagePath += 'gadgets/';
            break;
        default:
            imagePath += seccion.toLowerCase() + '/';
    }

    const quickViewImage = document.getElementById('quickViewImage');
    quickViewImage.innerHTML = `<img class='dynamic-image' src='${imagePath}${image}' alt='${title}' />`;
    document.getElementById('quickViewTitle').innerText = title;
    document.getElementById('quickViewDescription').innerText = description;
    document.getElementById('quickViewPrice').innerText = `Precio: $${price}`;
    document.getElementById('quickViewProductId').value = id;
    document.getElementById('quickView').style.display = 'block';
}

// Función para cerrar la vista rápida
function closeQuickView() {
    document.getElementById('quickView').style.display = 'none';
}

// Función para agregar al carrito desde la vista rápida
function quickViewAddToCart() {
    const id = document.getElementById('quickViewProductId').value;
    const quantity = parseInt(document.getElementById('modalQuantity').value) || 1;
    addToCart(id, quantity);
    closeQuickView();
}




