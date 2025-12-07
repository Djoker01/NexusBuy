<?php
include_once '../Models/Orden.php';
include_once '../Models/MetodoPago.php';
include_once '../Models/Carrito.php';
include_once '../Models/Historial.php';
include_once '../Util/Config/config.php';

$orden = new Orden();
$metodo_pago = new MetodoPago();
$carrito = new Carrito();
$historial = new Historial();

session_start();
header('Content-Type: application/json');

if ($_POST['funcion'] == 'procesar_pago') {
    try {
        if (empty($_SESSION['id'])) {
            echo json_encode(['success' => false, 'error' => 'no_sesion']);
            exit();
        }

        $id_usuario = $_SESSION['id'];
        $datos_pago = $_POST['datos_pago'];
        $direccion_envio = $_POST['direccion_envio'];
        $items_seleccionados = $_POST['items_seleccionados'];
        
        // === NUEVO: Recibir totales convertidos del frontend ===
        $subtotal = isset($_POST['subtotal']) ? floatval($_POST['subtotal']) : 0;
        $envio = isset($_POST['envio']) ? floatval($_POST['envio']) : 0;
        $total = isset($_POST['total']) ? floatval($_POST['total']) : 0;
        $moneda = isset($_POST['moneda']) ? $_POST['moneda'] : 'CUP';

        // Si no se enviaron totales, calcularlos (backward compatibility)
        if ($subtotal == 0) {
            $subtotal = 0;
            $envio = 0;
            $carrito->obtener_carrito($id_usuario);
            
            foreach ($items_seleccionados as $item_id) {
                $item = array_filter($carrito->objetos, function($obj) use ($item_id) {
                    return $obj->id == $item_id;
                });
                $item = array_values($item)[0];
                
                if ($item) {
                    $subtotal += $item->precio_final * $item->cantidad_producto;
                    if ($item->envio == 'pago') {
                        $envio = 250;
                    }
                }
            }
            $total = $subtotal + $envio;
        }

        // 1. Verificar stock de todos los productos
        foreach ($items_seleccionados as $item_id) {
            $carrito->obtener_carrito($id_usuario);
            $item = array_filter($carrito->objetos, function($obj) use ($item_id) {
                return $obj->id == $item_id;
            });
            $item = array_values($item)[0] ?? null;
            
            if (!$item || $item->cantidad < $item->cantidad_producto) {
                throw new Exception("Stock insuficiente para: " . ($item->nombre ?? 'producto'));
            }
        }

        // 3. Crear orden (usar los totales convertidos)
        $resultado_orden = $orden->crear_orden($id_usuario, $subtotal, $envio, 0, $total, $direccion_envio);
        
        if (!$resultado_orden['success']) {
            throw new Exception($resultado_orden['error']);
        }

        $id_orden = $resultado_orden['id_orden'];

        // 4. Agregar items a la orden y actualizar stock
        foreach ($items_seleccionados as $item_id) {
            $item = array_filter($carrito->objetos, function($obj) use ($item_id) {
                return $obj->id == $item_id;
            });
            $item = array_values($item)[0];
            
            if ($item) {
                // Agregar a orden_detalle
                $orden->agregar_detalle_orden(
                    $id_orden,
                    $item->id_producto_tienda,
                    $item->cantidad_producto,
                    $item->precio_unitario,
                    $item->descuento_porcentaje,
                    $item->precio_final * $item->cantidad_producto
                );
                
                // Actualizar stock
                if (!$orden->actualizar_stock($item->id_producto_tienda, $item->cantidad_producto)) {
                    throw new Exception("Error actualizando stock para: " . $item->nombre);
                }
                
                // Eliminar del carrito
                $carrito->eliminar_del_carrito($item_id, $id_usuario);
            }
        }

        // 5. Guardar método de pago si se proporcionó
        if (!empty($datos_pago['tipo'])) {
            $metodo_pago->crear_metodo_pago(
                $id_usuario,
                $datos_pago['tipo'],
                $datos_pago['titular'] ?? '',
                $datos_pago['numero'] ?? '',
                $datos_pago['fecha_vencimiento'] ?? null,
                $datos_pago['cvv'] ?? null,
                $datos_pago['paypal_email'] ?? null,
                $datos_pago['banco'] ?? null,
                $datos_pago['numero_cuenta'] ?? null
            );
        }

        // 6. Registrar en historial
        $descripcion = "Realizó una compra por {$moneda} {$total}. Orden: {$resultado_orden['numero_orden']}";
        $historial->crear_historial($descripcion, 2, 2, $id_usuario);

        echo json_encode([
            'success' => true,
            'numero_orden' => $resultado_orden['numero_orden'],
            'id_orden' => $id_orden,
            'total' => $total,
            'moneda' => $moneda
        ]);

    } catch (Exception $e) {
        error_log("Error en procesar_pago: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

if ($_POST['funcion'] == 'obtener_metodos_pago') {
    if (!empty($_SESSION['id'])) {
        $id_usuario = $_SESSION['id'];
        $metodos = $metodo_pago->obtener_metodos_usuario($id_usuario);
        echo json_encode($metodos);
    } else {
        echo json_encode([]);
    }
}