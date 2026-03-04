document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('btn-procesar-pago');
    
    if (btn) {
        // Remover todos los event listeners existentes
        const newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);
        
        // Agregar nuevo event listener simple
        newBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            
            console.log('🎯 Redirección forzada activada');
            
            // Validaciones básicas
            if (!document.getElementById('terminos').checked) {
                alert('Debes aceptar los términos y condiciones');
                return;
            }
            
            // Guardar flag simple
            sessionStorage.setItem('checkout_ready', 'true');
            
            // Redirigir inmediatamente
            window.location.href = 'procesar-pago.php';
        });
    }
});