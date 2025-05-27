// Variable global para el contador
let cartCount = 0;
let lastSyncTime = 0;

// Función para actualizar el contador del carrito
function updateCartCount(animate = false) {
    const cartCountElement = document.querySelector('.cart-count');
    if (cartCountElement) {
        cartCountElement.textContent = cartCount.toString();
        if (animate) {
            cartCountElement.style.transform = 'scale(1.2)';
            setTimeout(() => {
                cartCountElement.style.transform = 'scale(1)';
            }, 200);
        }
    }
}

// Función para incrementar el contador
function incrementCartCount(quantity = 1, animate = true) {
    cartCount = Math.max(0, cartCount + quantity);
    updateCartCount(animate);
}

// Función para establecer el contador
function setCartCount(count) {
    cartCount = Math.max(0, parseInt(count) || 0);
    updateCartCount(false);
}

// Función para sincronizar el contador con el servidor
async function syncCartCount(force = false) {
    const currentTime = Date.now();
    
    // Solo sincronizar si han pasado al menos 2 segundos desde la última sincronización
    // o si se fuerza la sincronización
    if (!force && currentTime - lastSyncTime < 2000) {
        return;
    }

    try {
        const response = await fetch('php/obtener_cantidad_carrito.php', {
            method: 'GET',
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'Expires': '0'
            }
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            const newCount = parseInt(data.cantidad) || 0;
            if (newCount !== cartCount) {
                setCartCount(newCount);
            }
        } else {
            console.error('Error al obtener la cantidad del carrito:', data.message);
        }
        
        lastSyncTime = currentTime;
    } catch (error) {
        console.error('Error al sincronizar el contador:', error);
    }
}

// Inicializar el contador al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    // Sincronizar el contador inmediatamente
    syncCartCount(true);

    // Sincronizar el contador cada 5 segundos
    setInterval(() => syncCartCount(false), 5000);

    // Sincronizar el contador cuando la página vuelve a estar visible
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'visible') {
            syncCartCount(true);
        }
    });

    // Sincronizar el contador cuando la ventana recupera el foco
    window.addEventListener('focus', () => syncCartCount(true));

    // Sincronizar el contador antes de que la página se descargue
    window.addEventListener('beforeunload', () => syncCartCount(true));

    // Sincronizar el contador cuando se detecta actividad en la página
    document.addEventListener('mousemove', () => syncCartCount(false));
    document.addEventListener('click', () => syncCartCount(false));
    document.addEventListener('keypress', () => syncCartCount(false));
}); 