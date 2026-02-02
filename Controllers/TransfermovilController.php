<?php
include_once '../Models/Orden.php';
include_once '../Models/Usuario.php';
include_once '../Models/Historial.php';
include_once '../Util/Config/config.php';

session_start();
header('Content-Type: application/json');

// Verificar que el usuario sea administrador
function verificarAdmin() {
    if (empty($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
        echo json_encode([
            'success' => false,
            'error' => 'no_autorizado',
            'message' => 'Acceso no autorizado. Solo administradores.'
        ]);
        exit();
    }
}

// Verificar que el usuario esté autenticado
function verificarUsuario() {
    if (empty($_SESSION['id'])) {
        echo json_encode([
            'success' => false,
            'error' => 'no_sesion',
            'message' => 'Debes iniciar sesión para continuar'
        ]);
        exit();
    }
}

$orden = new Orden();
$usuario = new Usuario();
$historial = new Historial();

// Determinar el tipo de solicitud (POST o JSON)
$funcion = $_POST['funcion'] ?? '';
if (empty($funcion) && file_get_contents('php://input')) {
    $input = json_decode(file_get_contents('php://input'), true);
    $funcion = $input['funcion'] ?? '';
}

// ================================================
// FUNCIONES PARA ADMINISTRADORES
// ================================================

// OBTENER PAGOS PENDIENTES (ADMIN)
if ($funcion == 'obtener_pagos_pendientes') {
    try {
        verificarAdmin();
        
        $pagina = isset($_POST['pagina']) ? intval($_POST['pagina']) : 1;
        $porPagina = isset($_POST['por_pagina']) ? intval($_POST['por_pagina']) : 20;
        $offset = ($pagina - 1) * $porPagina;
        
        // Contar total
        $sqlCount = "SELECT COUNT(*) as total 
                     FROM orden 
                     WHERE metodo_pago_codigo = 'transfermovil' 
                     AND estado_pago = 'pendiente'";
        $queryCount = $orden->acceso->query($sqlCount);
        $total = $queryCount->fetch()->total;
        
        // Obtener órdenes con información de transferencia
        $sql = "SELECT o.*, 
                       u.nombres, u.apellidos, u.email, u.telefono,
                       tp.banco, tp.numero_transaccion, tp.fecha_transferencia,
                       tp.monto as monto_transferido
                FROM orden o
                JOIN usuario u ON o.id_usuario = u.id
                LEFT JOIN transferencia_pagos tp ON o.id = tp.id_orden
                WHERE o.metodo_pago_codigo = 'transfermovil' 
                AND o.estado_pago = 'pendiente'
                ORDER BY o.fecha_creacion DESC
                LIMIT :offset, :limit";
        
        $query = $orden->acceso->prepare($sql);
        $query->bindValue(':offset', $offset, PDO::PARAM_INT);
        $query->bindValue(':limit', $porPagina, PDO::PARAM_INT);
        $query->execute();
        $ordenes = $query->fetchAll();
        
        echo json_encode([
            'success' => true,
            'ordenes' => $ordenes,
            'paginacion' => [
                'pagina_actual' => $pagina,
                'por_pagina' => $porPagina,
                'total' => $total,
                'total_paginas' => ceil($total / $porPagina)
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'error_consulta',
            'message' => 'Error al obtener pagos pendientes: ' . $e->getMessage()
        ]);
    }
}

// VERIFICAR PAGO (ADMIN - Aprobar o Rechazar)
if ($funcion == 'verificar_pago') {
    try {
        verificarAdmin();
        
        if (empty($_POST['orden_id'])) {
            throw new Exception('ID de orden requerido');
        }
        
        $orden_id = intval($_POST['orden_id']);
        $accion = $_POST['accion']; // 'aprobar' o 'rechazar'
        $comentario = $_POST['comentario'] ?? '';
        $admin_id = $_SESSION['id'];
        
        $nuevo_estado = ($accion === 'aprobar') ? 'pagado' : 'rechazado';
        
        // Iniciar transacción
        $orden->acceso->beginTransaction();
        
        try {
            // Actualizar orden
            $sqlOrden = "UPDATE orden 
                        SET estado_pago = :estado,
                            fecha_pago = NOW(),
                            fecha_actualizacion = NOW(),
                            observaciones = CONCAT(COALESCE(observaciones, ''), 
                                ' | Verificado por admin #', :admin_id, 
                                ' el ', DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i'), 
                                ': ', :comentario)
                        WHERE id = :orden_id 
                        AND estado_pago = 'pendiente'";
            
            $queryOrden = $orden->acceso->prepare($sqlOrden);
            $resultOrden = $queryOrden->execute([
                ':estado' => $nuevo_estado,
                ':admin_id' => $admin_id,
                ':comentario' => $comentario,
                ':orden_id' => $orden_id
            ]);
            
            if (!$resultOrden || $queryOrden->rowCount() == 0) {
                throw new Exception('No se pudo actualizar la orden o ya fue procesada');
            }
            
            // Actualizar transferencia_pagos si existe
            $sqlTransferencia = "UPDATE transferencia_pagos 
                                SET estado = :estado
                                WHERE id_orden = :orden_id";
            
            $queryTransferencia = $orden->acceso->prepare($sqlTransferencia);
            $queryTransferencia->execute([
                ':estado' => $nuevo_estado,
                ':orden_id' => $orden_id
            ]);
            
            // Registrar en historial
            $historial->registrar($orden_id, $admin_id, "pago_{$accion}", $comentario);
            
            // Registrar en logs_pagos
            $sqlLog = "INSERT INTO logs_pagos (orden_id, accion, detalles, usuario_id, fecha_log)
                      VALUES (:orden_id, :accion, :detalles, :usuario_id, NOW())";
            
            $queryLog = $orden->acceso->prepare($sqlLog);
            $queryLog->execute([
                ':orden_id' => $orden_id,
                ':accion' => "verificacion_{$accion}",
                ':detalles' => "Estado: {$nuevo_estado}. Comentario: {$comentario}",
                ':usuario_id' => $admin_id
            ]);
            
            // Confirmar transacción
            $orden->acceso->commit();
            
            echo json_encode([
                'success' => true,
                'message' => ($accion === 'aprobar') 
                    ? 'Pago verificado correctamente' 
                    : 'Pago rechazado correctamente',
                'nuevo_estado' => $nuevo_estado
            ]);
            
        } catch (Exception $e) {
            $orden->acceso->rollBack();
            throw $e;
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'error_verificacion',
            'message' => 'Error al verificar pago: ' . $e->getMessage()
        ]);
    }
}

// OBTENER DETALLE DE ORDEN (ADMIN)
if ($funcion == 'obtener_detalle_orden') {
    try {
        verificarAdmin();
        
        if (empty($_POST['orden_id'])) {
            throw new Exception('ID de orden requerido');
        }
        
        $orden_id = intval($_POST['orden_id']);
        
        // Obtener orden con detalles
        $sql = "SELECT o.*, 
                       u.nombres, u.apellidos, u.email, u.telefono,
                       tp.banco, tp.numero_transaccion, tp.fecha_transferencia,
                       tp.monto as monto_transferido, tp.numero_tarjeta_beneficiario,
                       tp.saldo_restante, tp.estado as estado_transferencia,
                       CONCAT(ud.direccion, ', ', m.nombre) as direccion_completa
                FROM orden o
                JOIN usuario u ON o.id_usuario = u.id
                LEFT JOIN transferencia_pagos tp ON o.id = tp.id_orden
                LEFT JOIN usuario_direccion ud ON o.id_direccion_envio = ud.id
                LEFT JOIN municipio m ON ud.id_municipio = m.id
                WHERE o.id = :orden_id";
        
        $query = $orden->acceso->prepare($sql);
        $query->execute([':orden_id' => $orden_id]);
        $ordenData = $query->fetch();
        
        if (!$ordenData) {
            throw new Exception('Orden no encontrada');
        }
        
        // Obtener productos de la orden
        $sqlProductos = "SELECT od.*, p.nombre, p.imagen_principal,
                                pt.precio as precio_unitario,
                                (od.cantidad * pt.precio) as subtotal
                        FROM orden_detalle od
                        JOIN producto_tienda pt ON od.id_producto_tienda = pt.id
                        JOIN producto p ON pt.id_producto = p.id
                        WHERE od.id_orden = :orden_id";
        
        $queryProductos = $orden->acceso->prepare($sqlProductos);
        $queryProductos->execute([':orden_id' => $orden_id]);
        $productos = $queryProductos->fetchAll();
        
        // Obtener historial de la orden
        $sqlHistorial = "SELECT h.*, u.nombres as usuario_nombre
                        FROM historial h
                        LEFT JOIN usuario u ON h.id_usuario = u.id
                        WHERE h.id_orden = :orden_id
                        ORDER BY h.fecha DESC";
        
        $queryHistorial = $orden->acceso->prepare($sqlHistorial);
        $queryHistorial->execute([':orden_id' => $orden_id]);
        $historialOrden = $queryHistorial->fetchAll();
        
        echo json_encode([
            'success' => true,
            'orden' => $ordenData,
            'productos' => $productos,
            'historial' => $historialOrden
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'error_detalle',
            'message' => 'Error al obtener detalle: ' . $e->getMessage()
        ]);
    }
}

// ================================================
// FUNCIONES PARA USUARIOS
// ================================================

// REGISTRAR TRANSFERENCIA SEGURA (USUARIO)
if ($funcion == 'registrar_transferencia_segura') {
    try {
        verificarUsuario();
        
        // Obtener datos (pueden venir por POST o JSON)
        $input = file_get_contents('php://input');
        $datos = json_decode($input, true);
        
        if (!$datos) {
            $datos = $_POST;
        }
        
        // Validar datos requeridos
        $camposRequeridos = [
            'orden_id', 'banco', 'fecha', 'numero_transaccion', 
            'monto_transferido', 'numero_tarjeta_beneficiario'
        ];
        
        foreach ($camposRequeridos as $campo) {
            if (empty($datos[$campo])) {
                throw new Exception("El campo {$campo} es requerido");
            }
        }
        
        $usuario_id = $_SESSION['id'];
        
        // Iniciar transacción
        $orden->acceso->beginTransaction();
        
        try {
            // 1. Verificar que la orden pertenece al usuario
            $sqlVerificarOrden = "SELECT o.* FROM orden o 
                                  WHERE o.id = :orden_id 
                                  AND o.id_usuario = :usuario_id
                                  AND o.metodo_pago_codigo = 'transfermovil'
                                  AND o.estado_pago = 'pendiente'";
            
            $queryVerificar = $orden->acceso->prepare($sqlVerificarOrden);
            $queryVerificar->execute([
                ':orden_id' => $datos['orden_id'],
                ':usuario_id' => $usuario_id
            ]);
            
            $ordenData = $queryVerificar->fetch();
            
            if (!$ordenData) {
                throw new Exception('Orden no válida o ya procesada');
            }
            
            // 2. Verificar duplicidad de transacción
            $sqlDuplicado = "SELECT COUNT(*) as count 
                            FROM transferencia_pagos 
                            WHERE numero_transaccion = :numero_transaccion 
                            AND estado != 'rechazado'";
            
            $queryDuplicado = $orden->acceso->prepare($sqlDuplicado);
            $queryDuplicado->execute([':numero_transaccion' => $datos['numero_transaccion']]);
            $resultDuplicado = $queryDuplicado->fetch();
            
            if ($resultDuplicado->count > 0) {
                throw new Exception('Esta transacción ya ha sido registrada anteriormente');
            }
            
            // 3. Validar formato del número de transacción (8-20 caracteres alfanuméricos)
            if (!preg_match('/^[A-Z0-9]{8,20}$/', strtoupper($datos['numero_transaccion']))) {
                throw new Exception('Formato de número de transacción inválido. Debe tener 8-20 caracteres alfanuméricos.');
            }
            
            // 4. Validar fecha (máximo 2 días de diferencia)
            $fechaTransferencia = new DateTime($datos['fecha']);
            $hoy = new DateTime();
            $diferencia = $hoy->diff($fechaTransferencia)->days;
            
            if ($diferencia > 2 || $fechaTransferencia > $hoy) {
                throw new Exception('La fecha de la transferencia no es válida (máximo 2 días de diferencia)');
            }
            
            // 5. Validar monto (debe coincidir exactamente con +/- 0.01 para redondeo)
            $montoOrden = floatval($ordenData->monto_total);
            $montoTransferido = floatval($datos['monto_transferido']);
            $diferenciaMonto = abs($montoOrden - $montoTransferido);
            
            if ($diferenciaMonto > 0.01) {
                throw new Exception("El monto transferido ($$montoTransferido) no coincide con el monto de la orden ($$montoOrden)");
            }
            
            // 6. Validar número de cuenta del beneficiario (formato: 9200XXXXXXXX5658)
            $numeroCuenta = preg_replace('/\s+/', '', $datos['numero_tarjeta_beneficiario']);
            if (strlen($numeroCuenta) !== 16 || !preg_match('/^9200\d{12}$/', $numeroCuenta)) {
                throw new Exception('Número de cuenta del beneficiario inválido. Formato: 9200XXXXXXXX5658');
            }
            
            // 7. Validar banco (lista blanca de bancos cubanos)
            $bancosPermitidos = [
                'BANCO POPULAR DE AHORRO', 'BANCO METROPOLITANO', 
                'BANCO DE CRÉDITO Y COMERCIO', 'BANCO FINANCIERO INTERNACIONAL',
                'TRANSFERMÓVIL', 'BANDEC', 'BPA', 'METROPOLITANO', 'BCC', 'BFI'
            ];
            
            $bancoUpper = strtoupper(trim($datos['banco']));
            $bancoValido = false;
            foreach ($bancosPermitidos as $bancoPermitido) {
                if (strpos($bancoUpper, $bancoPermitido) !== false || 
                    strpos($bancoPermitido, $bancoUpper) !== false) {
                    $bancoValido = true;
                    break;
                }
            }
            
            if (!$bancoValido) {
                throw new Exception('Banco no permitido. Bancos aceptados: ' . implode(', ', $bancosPermitidos));
            }
            
            // 8. Registrar la transferencia en transferencia_pagos
            $sqlInsert = "INSERT INTO transferencia_pagos 
                         (id_orden, usuario_id, banco, fecha_transferencia, 
                          numero_transaccion, monto, saldo_restante, 
                          numero_tarjeta_beneficiario, estado, fecha_registro) 
                         VALUES (:orden_id, :usuario_id, :banco, :fecha, 
                                 :numero_transaccion, :monto, :saldo_restante, 
                                 :numero_tarjeta, 'pendiente', NOW())";
            
            $queryInsert = $orden->acceso->prepare($sqlInsert);
            $resultInsert = $queryInsert->execute([
                ':orden_id' => $datos['orden_id'],
                ':usuario_id' => $usuario_id,
                ':banco' => $datos['banco'],
                ':fecha' => $datos['fecha'],
                ':numero_transaccion' => strtoupper($datos['numero_transaccion']),
                ':monto' => $datos['monto_transferido'],
                ':saldo_restante' => $datos['saldo_restante'] ?? null,
                ':numero_tarjeta' => $datos['numero_tarjeta_beneficiario']
            ]);
            
            if (!$resultInsert) {
                throw new Exception('Error al registrar la transferencia en la base de datos');
            }
            
            $idTransferencia = $orden->acceso->lastInsertId();
            
            // 9. Actualizar observaciones de la orden
            $observacion = "Transferencia registrada: " . strtoupper($datos['numero_transaccion']) . 
                          " | Monto: $" . $datos['monto_transferido'] . 
                          " | Fecha: " . $datos['fecha'] . 
                          " | Banco: " . $datos['banco'];
            
            $sqlUpdateOrden = "UPDATE orden 
                              SET observaciones = CONCAT(
                                  COALESCE(observaciones, ''), 
                                  IF(COALESCE(observaciones, '') != '', ' | ', ''), 
                                  :observacion
                              ),
                              fecha_actualizacion = NOW()
                              WHERE id = :orden_id";
            
            $queryUpdate = $orden->acceso->prepare($sqlUpdateOrden);
            $queryUpdate->execute([
                ':observacion' => $observacion,
                ':orden_id' => $datos['orden_id']
            ]);
            
            // 10. Registrar en historial
            $detalleHistorial = "Transf. registrada: " . strtoupper($datos['numero_transaccion']) . 
                               " - Banco: " . $datos['banco'] . 
                               " - Monto: $" . $datos['monto_transferido'];
            
            $historial->registrar($datos['orden_id'], $usuario_id, 'transferencia_registrada', $detalleHistorial);
            
            // 11. Registrar en logs_pagos
            $sqlLog = "INSERT INTO logs_pagos (orden_id, accion, detalles, usuario_id, fecha_log)
                      VALUES (:orden_id, :accion, :detalles, :usuario_id, NOW())";
            
            $queryLog = $orden->acceso->prepare($sqlLog);
            $queryLog->execute([
                ':orden_id' => $datos['orden_id'],
                ':accion' => 'transferencia_registrada',
                ':detalles' => "Transacción: " . strtoupper($datos['numero_transaccion']) . 
                              " | Monto: $" . $datos['monto_transferido'] . 
                              " | Banco: " . $datos['banco'],
                ':usuario_id' => $usuario_id
            ]);
            
            // Confirmar transacción
            $orden->acceso->commit();
            
            // Obtener número de orden para respuesta
            $sqlNumeroOrden = "SELECT numero_orden FROM orden WHERE id = :orden_id";
            $queryNumero = $orden->acceso->prepare($sqlNumeroOrden);
            $queryNumero->execute([':orden_id' => $datos['orden_id']]);
            $numeroOrden = $queryNumero->fetch()->numero_orden;
            
            echo json_encode([
                'success' => true,
                'message' => 'Transferencia registrada exitosamente',
                'numero_orden' => $numeroOrden,
                'registro_id' => $idTransferencia,
                'numero_transaccion' => strtoupper($datos['numero_transaccion'])
            ]);
            
        } catch (Exception $e) {
            $orden->acceso->rollBack();
            throw $e;
        }
        
    } catch (Exception $e) {
        error_log("Error en registrar_transferencia_segura: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

// VERIFICAR TRANSACCIÓN DUPLICADA
if ($funcion == 'verificar_transaccion_duplicada') {
    try {
        verificarUsuario();
        
        $input = file_get_contents('php://input');
        $datos = json_decode($input, true);
        
        if (!$datos) {
            $datos = $_POST;
        }
        
        $numeroTransaccion = $datos['numero_transaccion'] ?? '';
        
        if (empty($numeroTransaccion)) {
            throw new Exception('Número de transacción requerido');
        }
        
        $sql = "SELECT COUNT(*) as count 
                FROM transferencia_pagos 
                WHERE numero_transaccion = :numero_transaccion 
                AND estado != 'rechazado'";
        
        $query = $orden->acceso->prepare($sql);
        $query->execute([':numero_transaccion' => strtoupper($numeroTransaccion)]);
        $result = $query->fetch();
        
        echo json_encode([
            'duplicada' => $result->count > 0,
            'count' => $result->count,
            'numero_transaccion' => strtoupper($numeroTransaccion)
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'duplicada' => true, // Por seguridad, asumir duplicado si hay error
            'error' => $e->getMessage(),
            'numero_transaccion' => isset($numeroTransaccion) ? strtoupper($numeroTransaccion) : ''
        ]);
    }
}

// NOTIFICAR PAGO (USUARIO - Función alternativa)
if ($funcion == 'notificar_pago') {
    try {
        verificarUsuario();
        
        $orden_id = intval($_POST['orden_id']);
        $usuario_id = $_SESSION['id'];
        
        // Verificar que la orden pertenece al usuario
        $sqlVerificar = "SELECT COUNT(*) as count 
                        FROM orden 
                        WHERE id = :orden_id 
                        AND id_usuario = :usuario_id
                        AND estado_pago = 'pendiente'";
        
        $queryVerificar = $orden->acceso->prepare($sqlVerificar);
        $queryVerificar->execute([
            ':orden_id' => $orden_id,
            ':usuario_id' => $usuario_id
        ]);
        $result = $queryVerificar->fetch();
        
        if ($result->count == 0) {
            throw new Exception('Orden no encontrada o no autorizada');
        }
        
        // Actualizar observaciones de la orden
        $sqlUpdate = "UPDATE orden 
                     SET observaciones = CONCAT(
                         COALESCE(observaciones, ''), 
                         IF(COALESCE(observaciones, '') != '', ' | ', ''), 
                         'Usuario notificó pago el ', 
                         DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i')
                     ),
                     fecha_actualizacion = NOW()
                     WHERE id = :orden_id";
        
        $queryUpdate = $orden->acceso->prepare($sqlUpdate);
        $queryUpdate->execute([':orden_id' => $orden_id]);
        
        // Registrar en historial
        $historial->registrar($orden_id, $usuario_id, 'pago_notificado', 'Usuario notificó que realizó el pago');
        
        // Registrar en logs_pagos
        $sqlLog = "INSERT INTO logs_pagos (orden_id, accion, detalles, usuario_id, fecha_log)
                  VALUES (:orden_id, 'notificacion_usuario', 
                          'Usuario notificó haber realizado el pago', 
                          :usuario_id, NOW())";
        
        $queryLog = $orden->acceso->prepare($sqlLog);
        $queryLog->execute([
            ':orden_id' => $orden_id,
            ':usuario_id' => $usuario_id
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Notificación enviada correctamente. Nuestro equipo verificará tu pago pronto.'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al enviar notificación: ' . $e->getMessage()
        ]);
    }
}

// OBTENER DATOS DE ORDEN PARA VALIDACIÓN
if ($funcion == 'obtener_datos_orden') {
    try {
        verificarUsuario();
        
        $input = file_get_contents('php://input');
        $datos = json_decode($input, true);
        
        if (!$datos) {
            $datos = $_POST;
        }
        
        $orden_id = intval($datos['orden_id'] ?? 0);
        $usuario_id = $_SESSION['id'];
        
        // Verificar que la orden pertenece al usuario
        $sql = "SELECT o.total as monto_total, o.numero_orden, o.referencia_pago,
                       o.metodo_pago_codigo, o.estado_pago
                FROM orden o
                WHERE o.id = :orden_id 
                AND o.id_usuario = :usuario_id
                AND o.metodo_pago_codigo = 'transfermovil'";
        
        $query = $orden->acceso->prepare($sql);
        $query->execute([
            ':orden_id' => $orden_id,
            ':usuario_id' => $usuario_id
        ]);
        
        $ordenData = $query->fetch();
        
        if (!$ordenData) {
            throw new Exception('Orden no encontrada o no autorizada');
        }
        
        // Si no tiene referencia, generarla usando el procedimiento almacenado
        if (empty($ordenData->referencia_pago)) {
            // Llamar al procedimiento almacenado generar_referencia_transfermovil
            $sqlGenerarReferencia = "CALL generar_referencia_transfermovil(:orden_id, @referencia, @hash)";
            $queryGenerar = $orden->acceso->prepare($sqlGenerarReferencia);
            $queryGenerar->execute([':orden_id' => $orden_id]);
            
            // Obtener los valores de salida
            $sqlObtenerReferencia = "SELECT @referencia as referencia, @hash as hash";
            $queryObtener = $orden->acceso->query($sqlObtenerReferencia);
            $referenciaData = $queryObtener->fetch();
            
            $ordenData->referencia_pago = $referenciaData->referencia;
        }
        
        echo json_encode([
            'success' => true,
            'orden' => [
                'monto_total' => $ordenData->monto_total,
                'numero_orden' => $ordenData->numero_orden,
                'referencia' => $ordenData->referencia_pago,
                'metodo_pago' => $ordenData->metodo_pago_codigo,
                'estado_pago' => $ordenData->estado_pago
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

// GENERAR REFERENCIA DE PAGO (Para checkout)
if ($funcion == 'generar_referencia_pago') {
    try {
        verificarUsuario();
        
        $orden_id = intval($_POST['orden_id']);
        $usuario_id = $_SESSION['id'];
        
        // Verificar que la orden pertenece al usuario
        $sqlVerificar = "SELECT COUNT(*) as count 
                        FROM orden 
                        WHERE id = :orden_id 
                        AND id_usuario = :usuario_id
                        AND metodo_pago_codigo = 'transfermovil'
                        AND estado_pago = 'pendiente'";
        
        $queryVerificar = $orden->acceso->prepare($sqlVerificar);
        $queryVerificar->execute([
            ':orden_id' => $orden_id,
            ':usuario_id' => $usuario_id
        ]);
        $result = $queryVerificar->fetch();
        
        if ($result->count == 0) {
            throw new Exception('Orden no válida para generar referencia');
        }
        
        // Llamar al procedimiento almacenado
        $sql = "CALL generar_referencia_transfermovil(:orden_id, @referencia, @hash)";
        $query = $orden->acceso->prepare($sql);
        $query->execute([':orden_id' => $orden_id]);
        
        // Obtener los valores de salida
        $sqlResultados = "SELECT @referencia as referencia, @hash as hash";
        $queryResultados = $orden->acceso->query($sqlResultados);
        $resultados = $queryResultados->fetch();
        
        echo json_encode([
            'success' => true,
            'referencia' => $resultados->referencia,
            'hash' => $resultados->hash,
            'orden_id' => $orden_id
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al generar referencia: ' . $e->getMessage()
        ]);
    }
}

// OBTENER TRANSFERENCIAS DEL USUARIO
if ($funcion == 'obtener_mis_transferencias') {
    try {
        verificarUsuario();
        
        $usuario_id = $_SESSION['id'];
        $pagina = isset($_POST['pagina']) ? intval($_POST['pagina']) : 1;
        $porPagina = isset($_POST['por_pagina']) ? intval($_POST['por_pagina']) : 10;
        $offset = ($pagina - 1) * $porPagina;
        
        // Contar total
        $sqlCount = "SELECT COUNT(*) as total 
                    FROM transferencia_pagos tp
                    JOIN orden o ON tp.id_orden = o.id
                    WHERE tp.usuario_id = :usuario_id";
        
        $queryCount = $orden->acceso->prepare($sqlCount);
        $queryCount->execute([':usuario_id' => $usuario_id]);
        $total = $queryCount->fetch()->total;
        
        // Obtener transferencias
        $sql = "SELECT tp.*, 
                       o.numero_orden, o.monto_total, o.estado_pago,
                       o.fecha_creacion as fecha_orden
                FROM transferencia_pagos tp
                JOIN orden o ON tp.id_orden = o.id
                WHERE tp.usuario_id = :usuario_id
                ORDER BY tp.fecha_registro DESC
                LIMIT :offset, :limit";
        
        $query = $orden->acceso->prepare($sql);
        $query->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $query->bindValue(':offset', $offset, PDO::PARAM_INT);
        $query->bindValue(':limit', $porPagina, PDO::PARAM_INT);
        $query->execute();
        
        $transferencias = $query->fetchAll();
        
        echo json_encode([
            'success' => true,
            'transferencias' => $transferencias,
            'paginacion' => [
                'pagina_actual' => $pagina,
                'por_pagina' => $porPagina,
                'total' => $total,
                'total_paginas' => ceil($total / $porPagina)
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener transferencias: ' . $e->getMessage()
        ]);
    }
}

// FUNCIÓN POR DEFECTO SI NO SE RECONOCE LA FUNCIÓN
if ($funcion == '') {
    echo json_encode([
        'success' => false,
        'error' => 'funcion_no_especificada',
        'message' => 'Función no especificada o inválida'
    ]);
}
?>