// Inicializar al cargar la página
$(document).ready(function() {
// Cargar configuración inicial
cargarConfiguracionInicial();
    
// Aplicar tema automático si es necesario
const temaGuardado = localStorage.getItem('tema-interface');
if (temaGuardado === 'auto') {
    escucharCambiosTemaSistema();
}

    // Función para detectar preferencia del sistema
function detectarTemaSistema() {
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        return 'oscuro';
    }
    return 'claro';
}

// Aplicar tema con soporte para modo automático
function aplicarTema(tema) {
    console.log('Aplicando tema:', tema);
    
    // Remover clases de tema existentes
    $('body').removeClass('tema-claro tema-oscuro');
    
    let temaAplicar = tema;
    
    // Si es automático, detectar preferencia del sistema
    if (tema === 'auto') {
        temaAplicar = detectarTemaSistema();
    }
    
    // Aplicar nuevo tema
    $('body').addClass(`tema-${temaAplicar}`);
    
    // Guardar en localStorage para persistencia
    localStorage.setItem('tema-interface', tema);
    localStorage.setItem('tema-aplicado', temaAplicar);
    
    // Actualizar meta tag para theme-color
    actualizarMetaThemeColor(temaAplicar);
    
    // Si el tema es automático, escuchar cambios del sistema
    if (tema === 'auto') {
        escucharCambiosTemaSistema();
    } else {
        // Remover listener si existe
        if (window.mediaQueryTema) {
            window.mediaQueryTema.removeListener(manejarCambioTemaSistema);
        }
    }
}

// Actualizar color del navegador en móviles
function actualizarMetaThemeColor(tema) {
    let metaTheme = document.querySelector('meta[name="theme-color"]');
    if (!metaTheme) {
        metaTheme = document.createElement('meta');
        metaTheme.name = 'theme-color';
        document.head.appendChild(metaTheme);
    }
    
    const colores = {
        'claro': '#ffffff',
        'oscuro': '#121212'
    };
    
    metaTheme.content = colores[tema] || '#ffffff';
}

// Escuchar cambios en la preferencia del sistema
function escucharCambiosTemaSistema() {
    if (window.matchMedia) {
        window.mediaQueryTema = window.matchMedia('(prefers-color-scheme: dark)');
        window.mediaQueryTema.addListener(manejarCambioTemaSistema);
    }
}

function manejarCambioTemaSistema(e) {
    const temaGuardado = localStorage.getItem('tema-interface');
    if (temaGuardado === 'auto') {
        const nuevoTema = e.matches ? 'oscuro' : 'claro';
        $('body').removeClass('tema-claro tema-oscuro').addClass(`tema-${nuevoTema}`);
        localStorage.setItem('tema-aplicado', nuevoTema);
        actualizarMetaThemeColor(nuevoTema);
    }
}

// Aplicar densidad mejorada
function aplicarDensidad(densidad) {
    console.log('Aplicando densidad:', densidad);
    
    $('body').removeClass('densidad-comoda densidad-normal densidad-compacta');
    $('body').addClass(`densidad-${densidad}`);
    
    localStorage.setItem('densidad-interface', densidad);
    
    // Ajustar componentes específicos según densidad
    ajustarComponentesDensidad(densidad);
}

function ajustarComponentesDensidad(densidad) {
    const ajustes = {
        'comoda': {
            cardPadding: '2rem',
            formMargin: '1.5rem',
            btnPadding: '0.75rem 1.5rem',
            fontSize: '1rem'
        },
        'normal': {
            cardPadding: '1.5rem',
            formMargin: '1rem',
            btnPadding: '0.5rem 1rem',
            fontSize: '0.9rem'
        },
        'compacta': {
            cardPadding: '1rem',
            formMargin: '0.5rem',
            btnPadding: '0.375rem 0.75rem',
            fontSize: '0.8rem'
        }
    };
    
    const ajuste = ajustes[densidad] || ajustes.normal;
    
    // Aplicar estilos inline para componentes específicos
    $('.card-body').css('padding', ajuste.cardPadding);
    $('.form-group').css('margin-bottom', ajuste.formMargin);
    $('.btn').not('.btn-sm, .btn-lg').css('padding', ajuste.btnPadding);
    $('body').css('font-size', ajuste.fontSize);
}

// Cargar configuración al iniciar
function cargarConfiguracionInicial() {
    const temaGuardado = localStorage.getItem('tema-interface') || 'claro';
    const densidadGuardada = localStorage.getItem('densidad-interface') || 'normal';
    const idiomaGuardado = localStorage.getItem('idioma-interface') || 'es';
    const monedaGuardada = localStorage.getItem('moneda-interface') || 'EUR';
    
    // Aplicar configuración guardada
    aplicarTema(temaGuardado);
    aplicarDensidad(densidadGuardada);
    
    // Actualizar selects
    $('#tema-interface').val(temaGuardado);
    $('#densidad-interface').val(densidadGuardada);
    $('#idioma-interface').val(idiomaGuardado);
    $('#moneda-interface').val(monedaGuardada);
    
    console.log('Configuración inicial cargada:', {
        tema: temaGuardado,
        densidad: densidadGuardada,
        idioma: idiomaGuardado,
        moneda: monedaGuardada
    });
}

// Selector de tema rápido en navbar
$(document).on('click', '.selector-tema', function(e) {
    e.preventDefault();
    const tema = $(this).data('tema');
    
    // Actualizar select en configuración
    $('#tema-interface').val(tema);
    
    // Aplicar tema inmediatamente
    aplicarTema(tema);
    
    // Guardar configuración en servidor si el usuario está logueado
    if (typeof usuarioData !== 'undefined' && usuarioData.id) {
        guardarConfiguracion('visualizacion', {
            tema: tema,
            densidad: $('#densidad-interface').val(),
            idioma: $('#idioma-interface').val(),
            moneda: $('#moneda-interface').val()
        });
    }
    
    // Mostrar feedback
    const nombresTema = {
        'claro': 'Tema Claro',
        'oscuro': 'Tema Oscuro', 
        'auto': 'Tema Automático'
    };
    
    Swal.fire({
        icon: 'success',
        title: 'Tema cambiado',
        text: `Has activado el ${nombresTema[tema]}`,
        timer: 2000,
        showConfirmButton: false
    });
});
    
    
});