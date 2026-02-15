$(document).ready(function() {
    cargarResumenFinanciero();
});

function cargarResumenFinanciero() {
    $.post('TiendaFinanzasController.php', {
        funcion: 'obtener_resumen'
    }, function(response) {
        if (response.success) {
            actualizarResumen(response.resumen);
            actualizarMovimientos(response.resumen.movimientos);
        } else {
            toastr.error('Error al cargar datos financieros');
        }
    });
}

function actualizarResumen(resumen) {
    $('#saldoDisponible').text('$' + parseFloat(resumen.saldo?.saldo_disponible || 0).toFixed(2));
    $('#saldoRetenido').text('$' + parseFloat(resumen.saldo?.saldo_retenido || 0).toFixed(2));
    $('#totalGanado').text('$' + parseFloat(resumen.saldo?.total_ganado || 0).toFixed(2));

    if (resumen.proximas_liberaciones) {
        $('#pendientesCount').text(resumen.proximas_liberaciones.pendientes || 0);
        const monto = parseFloat(resumen.proximas_liberaciones.total_pendiente || 0);
        $('#pendientesMonto').text('$' + monto.toFixed(2));

        const total = parseFloat(resumen.saldo?.total_ganado || 1);
        const porcentaje = (monto / total) * 100;
        $('#progressLiberacion').css('width', porcentaje + '%');
    }
}

function actualizarMovimientos(movimientos) {
    if (!movimientos || movimientos.length === 0) {
        $('#movimientosContainer').html('<div class="text-center py-4">No hay movimientos</div>');
        return;
    }

    let html = '';
    movimientos.forEach(mov => {
        const claseMonto = parseFloat(mov.monto) >= 0 ? 'monto-positivo' : 'monto-negativo';
        const signo = parseFloat(mov.monto) >= 0 ? '+' : '';
        
        html += `
            <div class="movimiento-item">
                <div>
                    <strong>${mov.concepto}</strong>
                    <small class="d-block text-muted">${new Date(mov.fecha).toLocaleString()}</small>
                </div>
                <div class="${claseMonto}">
                    ${signo}$${Math.abs(parseFloat(mov.monto)).toFixed(2)}
                </div>
            </div>
        `;
    });

    $('#movimientosContainer').html(html);
}