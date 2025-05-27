document.addEventListener('DOMContentLoaded', function() {
    // Verificar si hay una sección a la que desplazarse
    const scrollTo = localStorage.getItem('scrollTo');
    if (scrollTo) {
        // Buscar el elemento con ese ID
        const element = document.getElementById(scrollTo);
        if (element) {
            // Desplazarse al elemento después de un breve retraso
            setTimeout(() => {
                element.scrollIntoView({ behavior: 'smooth' });
                // Limpiar el localStorage después de desplazarse
                localStorage.removeItem('scrollTo');
            }, 500);
        } else {
            // Si no se encuentra el elemento, simplemente limpiar el localStorage
            localStorage.removeItem('scrollTo');
            console.log('Sección no encontrada:', scrollTo);
        }
    }
});