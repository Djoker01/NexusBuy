// console.log('funcion_general.js cargado');
// console.log('BASE_PATH en script:', typeof BASE_PATH !== 'undefined' ? BASE_PATH : 'NO DEFINIDA');
// console.log('jQuery disponible:', typeof $ !== 'undefined');
// funcion_general.js - Funciones generales para todo el sitio
$(document).ready(function () {
  // console.log('Inicializando funciones generales...');
  
  // Variable para controlar si el dropdown está abierto
    let dropdownOpen = false;
    
    // Toggle dropdown al hacer clic en user-info
    $('#userDropdown').click(function(e) {
        e.stopPropagation();
        e.preventDefault();
        
        const $this = $(this);
        const $menu = $('#userDropdownMenu');
        
        // Cerrar cualquier otro dropdown abierto
        $('.dropdown-menu.show').not($menu).removeClass('show');
        $('.user-info.open').not($this).removeClass('open');
        
        // Toggle el actual
        $this.toggleClass('open');
        $menu.toggleClass('show');
        
        dropdownOpen = $menu.hasClass('show');
    });

    // Cerrar dropdown al hacer clic fuera
    $(document).click(function(e) {
        if (!$(e.target).closest('.dropdown-container').length) {
            $('#userDropdown').removeClass('open');
            $('#userDropdownMenu').removeClass('show');
            dropdownOpen = false;
        }
    });

    // Cerrar dropdown con tecla ESC
    $(document).keydown(function(e) {
        if (e.key === 'Escape' && dropdownOpen) {
            $('#userDropdown').removeClass('open');
            $('#userDropdownMenu').removeClass('show');
            dropdownOpen = false;
        }
    });

    // Prevenir que el dropdown se cierre al hacer clic dentro de él
    $('#userDropdownMenu').click(function(e) {
        e.stopPropagation();
    });

    // En móviles, ajustar comportamiento
    if (window.innerWidth <= 768) {
        $('#userDropdown').off('click').on('click', function(e) {
            e.stopPropagation();
            const $menu = $('#userDropdownMenu');
            
            if ($menu.hasClass('show')) {
                $menu.removeClass('show');
                $(this).removeClass('open');
            } else {
                // Cerrar cualquier otro menú abierto
                $('.dropdown-menu.show').removeClass('show');
                $('.user-info.open').removeClass('open');
                
                $menu.addClass('show');
                $(this).addClass('open');
                
                // Scroll suave para mostrar el menú
                $('html, body').animate({
                    scrollTop: $(document).height()
                }, 300);
            }
        });
    }
     
});
