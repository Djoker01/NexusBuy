<?php
while (ob_get_level()) {
    ob_end_clean();
}

include_once '../Models/Orden.php';
include_once '../Models/Usuario.php';
include_once '../Models/Historial.php';
include_once '../Util/Config/config.php';

session_start();
header('Content-Type: application/json');

// Verificar que el usuario sea administrador
function verificarAdmin()
{
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
function verificarUsuario()
{
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
                notas_internas = CONCAT(COALESCE(notas_internas, ''), 
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
            $sqlLog = "INSERT INTO logs_pagos (orden_id, accion, detalles, id_usuario, fecha
                      VALUES (:orden_id, :accion, :detalles, :id_usuario, NOW())";

            $queryLog = $orden->acceso->prepare($sqlLog);
            $queryLog->execute([
                ':orden_id' => $orden_id,
                ':accion' => "verificacion_{$accion}",
                ':detalles' => "Estado: {$nuevo_estado}. Comentario: {$comentario}",
                ':id_usuario' => $admin_id
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
            'orden_id',
            'banco',
            'fecha',
            'numero_transaccion',
            'monto_transferido',
            'numero_tarjeta_beneficiario'
        ];

        foreach ($camposRequeridos as $campo) {
            if (empty($datos[$campo])) {
                throw new Exception("El campo {$campo} es requerido");
            }
        }

        $id_usuario = $_SESSION['id'];

        // Iniciar transacción
        $orden->acceso->beginTransaction();

        try {
            // 1. Verificar que la orden pertenece al usuario
            $sqlVerificarOrden = "SELECT 
                        o.id,
                        o.total as monto_total,
                        o.id_usuario,
                        o.estado_pago,
                        o.metodo_pago_codigo,
                        o.numero_orden,
                        o.referencia_pago
                      FROM orden o 
                      WHERE o.id = :orden_id 
                      AND o.id_usuario = :id_usuario
                      AND o.metodo_pago_codigo = 'transfermovil'
                      AND o.estado_pago = 'pendiente'";

            $queryVerificar = $orden->acceso->prepare($sqlVerificarOrden);
            $queryVerificar->execute([
                ':orden_id' => intval($datos['orden_id']),
                ':id_usuario' => $id_usuario
            ]);

            $ordenData = $queryVerificar->fetch();

            if (!$ordenData) {
                throw new Exception('Orden no válida o ya procesada');
            }

             // 2. Obtener productos del carrito del usuario
            $sqlCarrito = "SELECT c.id_producto_tienda, c.cantidad, 
                          pt.precio, pt.descuento_porcentaje, pt.stock,
                          p.nombre
                          FROM carrito c
                          JOIN producto_tienda pt ON c.id_producto_tienda = pt.id
                          JOIN producto p ON pt.id_producto = p.id
                          WHERE c.id_usuario = :id_usuario";

            $queryCarrito = $orden->acceso->prepare($sqlCarrito);
            $queryCarrito->execute([':id_usuario' => $id_usuario]);
            $productosCarrito = $queryCarrito->fetchAll();

            if (empty($productosCarrito)) {
                throw new Exception('El carrito está vacío');
            }

            // 3. Verificar stock
            $productos_sin_stock = [];
            foreach ($productosCarrito as $producto) {
                if ($producto->stock < $producto->cantidad) {
                    $productos_sin_stock[] = [
                        'nombre' => $producto->nombre,
                        'disponible' => $producto->stock,
                        'solicitado' => $producto->cantidad
                    ];
                }
            }

            if (!empty($productos_sin_stock)) {
                $error_msg = "Stock insuficiente: ";
                foreach ($productos_sin_stock as $prod) {
                    $error_msg .= "{$prod['nombre']} (disp: {$prod['disponible']}, sol: {$prod['solicitado']}); ";
                }
                throw new Exception($error_msg);
            }

            // 3. Verificar duplicidad de transacción
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

            // 4. Validar formato del número de transacción
            if (!preg_match('/^[A-Z0-9]{8,20}$/', strtoupper($datos['numero_transaccion']))) {
                throw new Exception('Formato de número de transacción inválido. Debe tener 8-20 caracteres alfanuméricos.');
            }

            // 5. Validar fecha (máximo 2 días de diferencia)
            $fechaTransferencia = new DateTime($datos['fecha']);
            $hoy = new DateTime();
            $diferencia = $hoy->diff($fechaTransferencia)->days;

            if ($diferencia > 2 || $fechaTransferencia > $hoy) {
                throw new Exception('La fecha de la transferencia no es válida (máximo 2 días de diferencia)');
            }

            // 6. Validar monto
            $montoOrden = floatval($ordenData->monto_total);
            $montoTransferido = floatval($datos['monto_transferido']);
            $diferenciaMonto = abs($montoOrden - $montoTransferido);

            if ($diferenciaMonto > 0.01) {
                throw new Exception("El monto transferido ($$montoTransferido) no coincide con el monto de la orden ($$montoOrden)");
            }

            // 7. Validar número de cuenta del beneficiario
            $numeroCuenta = preg_replace('/\s+/', '', $datos['numero_tarjeta_beneficiario']);

            if (!preg_match('/^9238\d{8}5406$/', $numeroCuenta)) {
                throw new Exception('Número de cuenta inválido. Debe tener formato: 9238 + 8 dígitos + 5406');
            }

            // 8. Validar banco
            $bancosPermitidos = [
                'BANCO POPULAR DE AHORRO',
                'BANCO METROPOLITANO',
                'BANCO DE CRÉDITO Y COMERCIO',
                'BANCO FINANCIERO INTERNACIONAL',
                'TRANSFERMÓVIL',
                'BANDEC',
                'BPA',
                'METROPOLITANO',
                'BCC',
                'BFI'
            ];

            $bancoUpper = strtoupper(trim($datos['banco']));
            $bancoValido = false;
            foreach ($bancosPermitidos as $bancoPermitido) {
                if (
                    strpos($bancoUpper, $bancoPermitido) !== false ||
                    strpos($bancoPermitido, $bancoUpper) !== false
                ) {
                    $bancoValido = true;
                    break;
                }
            }

            if (!$bancoValido) {
                throw new Exception('Banco no permitido. Bancos aceptados: ' . implode(', ', $bancosPermitidos));
            }

            // 5. Crear orden_detalle y actualizar stock
            $productos_creados = 0;
            $productos_actualizados = 0;
            
            foreach ($productosCarrito as $producto) {
                // Calcular precios
                $precio_unitario = $producto->precio;
                $descuento_unitario = ($producto->descuento_porcentaje > 0) ? 
                    ($producto->precio * $producto->descuento_porcentaje / 100) : 0;
                $precio_final = $precio_unitario - $descuento_unitario;
                $subtotal = $precio_final * $producto->cantidad;

                // Insertar en orden_detalle
                $sqlDetalle = "INSERT INTO orden_detalle 
                              (id_orden, id_producto_tienda, cantidad, 
                               precio_unitario, descuento_unitario, subtotal)
                              VALUES (:id_orden, :id_producto_tienda, :cantidad,
                                      :precio_unitario, :descuento_unitario, :subtotal)";

                $queryDetalle = $orden->acceso->prepare($sqlDetalle);
                $detalle_ok = $queryDetalle->execute([
                    ':id_orden' => $ordenData->id,
                    ':id_producto_tienda' => $producto->id_producto_tienda,
                    ':cantidad' => $producto->cantidad,
                    ':precio_unitario' => $precio_unitario,
                    ':descuento_unitario' => $descuento_unitario,
                    ':subtotal' => $subtotal
                ]);

                if ($detalle_ok) {
                    $productos_creados++;
                    
                    // Actualizar stock
                    $sql_update_stock = "UPDATE producto_tienda 
                                        SET stock = stock - :cantidad,
                                            fecha_actualizacion = NOW()
                                        WHERE id = :id_producto_tienda";
                    
                    $query_update = $orden->acceso->prepare($sql_update_stock);
                    $stock_actualizado = $query_update->execute([
                        ':cantidad' => $producto->cantidad,
                        ':id_producto_tienda' => $producto->id_producto_tienda
                    ]);
                    
                    if ($stock_actualizado) {
                        $productos_actualizados++;
                    }
                }
            }

            if ($productos_creados == 0) {
                throw new Exception('No se pudieron agregar productos a la orden');
            }

            // 6. Vaciar carrito
            $sqlEliminarCarrito = "DELETE FROM carrito WHERE id_usuario = :id_usuario";
            $queryEliminar = $orden->acceso->prepare($sqlEliminarCarrito);
            $queryEliminar->execute([':id_usuario' => $id_usuario]);

            // 9. Registrar la transferencia
            $usuario_id = intval($_SESSION['id'] ?? 0);
            if ($usuario_id == 0) {
                throw new Exception('Usuario no autenticado. Por favor, inicia sesión nuevamente.');
            }

            // CORREGIR los nombres de parámetros
            $sqlInsert = "INSERT INTO transferencia_pagos 
             (id_orden, id_usuario, banco, fecha_transferencia, 
              numero_transaccion, monto, estado) 
             VALUES (:id_orden, :id_usuario, :banco, :fecha_transferencia, 
                     :numero_transaccion, :monto, 'pendiente')";

            $queryInsert = $orden->acceso->prepare($sqlInsert);
            $resultInsert = $queryInsert->execute([
                ':id_orden' => intval($datos['orden_id']),
                ':id_usuario' => $usuario_id,
                ':banco' => $datos['banco'],
                ':fecha_transferencia' => $datos['fecha'],
                ':numero_transaccion' => strtoupper($datos['numero_transaccion']),
                ':monto' => floatval($datos['monto_transferido'])
            ]);

            if (!$resultInsert) {
                $errorInfo = $queryInsert->errorInfo();
                throw new Exception('Error al registrar: ' . $errorInfo[2]);
            }

            $idTransferencia = $orden->acceso->lastInsertId();

            // Actualizar estado de la orden
            $sql_actualizar_estado = "UPDATE orden 
                                     SET estado_pago = 'procesando_verificacion',
                                         fecha_actualizacion = NOW()
                                     WHERE id = :orden_id";
            
            $query_actualizar = $orden->acceso->prepare($sql_actualizar_estado);
            $query_actualizar->execute([':orden_id' => $ordenData->id]);

            // 10. Actualizar notas_internas
            $observacion = "Transferencia registrada: " . strtoupper($datos['numero_transaccion']) .
                " | Monto: $" . $datos['monto_transferido'] .
                " | Fecha: " . $datos['fecha'] .
                " | Banco: " . $datos['banco'] .
                " | Stock actualizado para " . $productos_actualizados . " productos";

            $sqlUpdateOrden = "UPDATE orden 
                  SET notas_internas = CONCAT(
                      COALESCE(notas_internas, ''), 
                      IF(COALESCE(notas_internas, '') != '', ' | ', ''), 
                      :observacion
                  ),
                  fecha_actualizacion = NOW()
                  WHERE id = :orden_id";

            $queryUpdate = $orden->acceso->prepare($sqlUpdateOrden);
            $queryUpdate->execute([
                ':observacion' => $observacion,
                ':orden_id' => $datos['orden_id']
            ]);

            // 11. Registrar en logs_pagos
            $sqlLog = "INSERT INTO logs_pagos (orden_id, accion, detalles, usuario_id, fecha)
                      VALUES (:orden_id, :accion, :detalles, :usuario_id, NOW())";

            $queryLog = $orden->acceso->prepare($sqlLog);
            $queryLog->execute([
                ':orden_id' => $datos['orden_id'],
                ':accion' => 'transferencia_registrada',
                ':detalles' => "Transacción: " . strtoupper($datos['numero_transaccion']) .
                    " | Monto: $" . $datos['monto_transferido'] .
                    " | Banco: " . $datos['banco'] .
                    " | Stock actualizado",
                ':usuario_id' => $usuario_id
            ]);

            // 12. Obtener número de orden para respuesta
            $sqlNumeroOrden = "SELECT numero_orden FROM orden WHERE id = :orden_id";
            $queryNumero = $orden->acceso->prepare($sqlNumeroOrden);
            $queryNumero->execute([':orden_id' => $datos['orden_id']]);
            $resultadoNumero = $queryNumero->fetch(PDO::FETCH_OBJ);

            $numeroOrden = $resultadoNumero ? $resultadoNumero->numero_orden : 'N/A';

            // 13. Registrar en historial (si tienes la clase historial)
            if (class_exists('Historial') && isset($historial)) {
                $descripcion = "Transferencia realizada exitosamente. Stock actualizado para " . 
                             $productos_actualizados . " productos";
                $historial->crear_historial($descripcion, 6, 14, $id_usuario, 'transferencia_registrada');
            }

            // Confirmar transacción
            $orden->acceso->commit();

            // Enviar respuesta
            echo json_encode([
                'success' => true,
                'message' => 'Transferencia registrada exitosamente',
                'registro_id' => $idTransferencia,
                'numero_transaccion' => strtoupper($datos['numero_transaccion']),
                'numero_orden' => $numeroOrden,
                'stock_actualizado' => true,
                'productos_actualizados' => $productos_actualizados,
                'estado_pago' => 'procesando_verificacion'
            ]);

            // NUEVO: Procesar cada venta para retención
    include_once '../Models/Escrow.php';
    $escrow = new Escrow();
    
    foreach ($items_seleccionados as $item_id) {
        // Obtener detalles del producto
        $sql_item = "SELECT c.cantidad, c.id_producto_tienda, pt.precio, pt.id_tienda
                    FROM carrito c
                    JOIN producto_tienda pt ON c.id_producto_tienda = pt.id
                    WHERE c.id = :item_id";
        
        $query_item = $orden->acceso->prepare($sql_item);
        $query_item->execute([':item_id' => $item_id]);
        $item = $query_item->fetch();
        
        if ($item) {
            $escrow->procesarVenta(
                $id_orden,
                $item->id_tienda,
                $item->id_producto_tienda,
                $item->cantidad,
                $item->precio,
                0 // descuento (calcular si aplica)
            );
        }
    }
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
        $id_usuario = $_SESSION['id'];

        // Verificar que la orden pertenece al usuario
        $sqlVerificar = "SELECT COUNT(*) as count 
                        FROM orden 
                        WHERE id = :orden_id 
                        AND id_usuario = :id_usuario
                        AND estado_pago = 'pendiente'";

        $queryVerificar = $orden->acceso->prepare($sqlVerificar);
        $queryVerificar->execute([
            ':orden_id' => $orden_id,
            ':id_usuario' => $id_usuario
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
        $historial->registrar($orden_id, $id_usuario, 'pago_notificado', 'Usuario notificó que realizó el pago');

        // Registrar en logs_pagos
        $sqlLog = "INSERT INTO logs_pagos (orden_id, accion, detalles, id_usuario, fecha)
                  VALUES (:orden_id, 'notificacion_usuario', 
                          'Usuario notificó haber realizado el pago', 
                          :id_usuario, NOW())";

        $queryLog = $orden->acceso->prepare($sqlLog);
        $queryLog->execute([
            ':orden_id' => $orden_id,
            ':id_usuario' => $id_usuario
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
        $id_usuario = $_SESSION['id'];

        // Verificar que la orden pertenece al usuario
        $sql = "SELECT o.total as monto_total, o.numero_orden, o.referencia_pago,
                       o.metodo_pago_codigo, o.estado_pago, o.id
                FROM orden o
                WHERE o.id = :orden_id 
                AND o.id_usuario = :id_usuario
                AND o.metodo_pago_codigo = 'transfermovil'";

        $query = $orden->acceso->prepare($sql);
        $query->execute([
            ':orden_id' => $orden_id,
            ':id_usuario' => $id_usuario
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
                'id' => $ordenData->id,
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
        $id_usuario = $_SESSION['id'];

        // Verificar que la orden pertenece al usuario
        $sqlVerificar = "SELECT COUNT(*) as count 
                        FROM orden 
                        WHERE id = :orden_id 
                        AND id_usuario = :id_usuario
                        AND metodo_pago_codigo = 'transfermovil'
                        AND estado_pago = 'pendiente'";

        $queryVerificar = $orden->acceso->prepare($sqlVerificar);
        $queryVerificar->execute([
            ':orden_id' => $orden_id,
            ':id_usuario' => $id_usuario
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

        $id_usuario = $_SESSION['id'];
        $pagina = isset($_POST['pagina']) ? intval($_POST['pagina']) : 1;
        $porPagina = isset($_POST['por_pagina']) ? intval($_POST['por_pagina']) : 10;
        $offset = ($pagina - 1) * $porPagina;

        // Contar total
        $sqlCount = "SELECT COUNT(*) as total 
                    FROM transferencia_pagos tp
                    JOIN orden o ON tp.id_orden = o.id
                    WHERE tp.id_usuario = :id_usuario";

        $queryCount = $orden->acceso->prepare($sqlCount);
        $queryCount->execute([':id_usuario' => $id_usuario]);
        $total = $queryCount->fetch()->total;

        // Obtener transferencias
        $sql = "SELECT tp.*, 
                       o.numero_orden, o.monto_total, o.estado_pago,
                       o.fecha_creacion as fecha_orden
                FROM transferencia_pagos tp
                JOIN orden o ON tp.id_orden = o.id
                WHERE tp.id_usuario = :id_usuario
                ORDER BY tp.fecha_registro DESC
                LIMIT :offset, :limit";

        $query = $orden->acceso->prepare($sql);
        $query->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
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
