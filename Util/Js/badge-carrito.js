// badge-carrito.js - Para páginas que no son el carrito
$(document).ready(function() {
    //console.log('Inicializando badge del carrito...');
    
    // Actualizar badge al cargar la página
    actualizarBadgeCarrito();
    
    // Actualizar periódicamente
    setInterval(actualizarBadgeCarrito, 30000);
    
    async function actualizarBadgeCarrito() {
        try {
            const response = await $.post('Controllers/CarritoController.php', { 
                funcion: 'obtener_cantidad_total' 
            });
            
            const data = typeof response === 'string' ? JSON.parse(response) : response;
            const cantidad = data.cantidad_total || 0;
            const $badge = $('#cart-badge');
            
            if (cantidad > 0) {
                $badge.text(cantidad);
                $badge.show();
            } else {
                $badge.hide();
            }
        } catch (error) {
            console.error('Error actualizando badge:', error);
            $('#cart-badge').hide();
        }
    }
});