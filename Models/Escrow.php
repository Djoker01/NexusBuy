<?php
include_once 'Conexion.php';

class Escrow {
    private $acceso;
    public $error;

    public function __construct() {
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }

    /**
     * Procesar una nueva venta (llamar cuando se confirma el pago)
     */
    public function procesarVenta($id_orden, $id_tienda, $id_producto_tienda, $cantidad, $precio_unitario, $descuento = 0) {
        try {
            $this->acceso->beginTransaction();

            // Calcular montos
            $subtotal = $precio_unitario * $cantidad;
            $total_vendedor = $subtotal - $descuento;

            // Registrar venta
            $sql_venta = "INSERT INTO venta (
                id_orden, id_tienda, id_producto_tienda, cantidad,
                precio_unitario, subtotal, descuento_aplicado,
                total_vendedor, fecha_venta, estado
            ) VALUES (
                :id_orden, :id_tienda, :id_producto_tienda, :cantidad,
                :precio_unitario, :subtotal, :descuento_aplicado,
                :total_vendedor, NOW(), 'retenida'
            )";

            $query = $this->acceso->prepare($sql_venta);
            $query->execute([
                ':id_orden' => $id_orden,
                ':id_tienda' => $id_tienda,
                ':id_producto_tienda' => $id_producto_tienda,
                ':cantidad' => $cantidad,
                ':precio_unitario' => $precio_unitario,
                ':subtotal' => $subtotal,
                ':descuento_aplicado' => $descuento,
                ':total_vendedor' => $total_vendedor
            ]);

            $id_venta = $this->acceso->lastInsertId();

            // Crear retención por 7 días
            $fecha_retencion = date('Y-m-d H:i:s');
            $fecha_liberacion = date('Y-m-d H:i:s', strtotime('+7 days'));

            $sql_retencion = "INSERT INTO retencion (
                id_venta, id_tienda, monto_retenido,
                fecha_retencion, fecha_liberacion, liberada
            ) VALUES (
                :id_venta, :id_tienda, :monto_retenido,
                :fecha_retencion, :fecha_liberacion, 0
            )";

            $query = $this->acceso->prepare($sql_retencion);
            $query->execute([
                ':id_venta' => $id_venta,
                ':id_tienda' => $id_tienda,
                ':monto_retenido' => $total_vendedor,
                ':fecha_retencion' => $fecha_retencion,
                ':fecha_liberacion' => $fecha_liberacion
            ]);

            // Actualizar saldo del vendedor (retenido)
            $this->actualizarSaldoVendedor($id_tienda, $total_vendedor, 'retenido');

            $this->acceso->commit();
            return $id_venta;

        } catch (PDOException $e) {
            $this->acceso->rollBack();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * Liberar retenciones vencidas (llamado por CRON)
     */
    public function liberarRetencionesVencidas() {
        try {
            $this->acceso->beginTransaction();

            $sql = "SELECT r.*, v.id_tienda, v.total_vendedor
                    FROM retencion r
                    JOIN venta v ON r.id_venta = v.id
                    WHERE r.liberada = 0 
                    AND r.fecha_liberacion <= NOW()";

            $query = $this->acceso->prepare($sql);
            $query->execute();
            $retenciones = $query->fetchAll(PDO::FETCH_OBJ);

            $liberadas = 0;
            foreach ($retenciones as $ret) {
                // Calcular comisión
                $comision_valor = $ret->total_vendedor * 0.10; // 10%
                $monto_final = $ret->total_vendedor - $comision_valor;

                // Registrar comisión
                $sql_comision = "INSERT INTO comision (
                    id_venta, id_tienda, monto_base, porcentaje,
                    valor_comision, monto_final_vendedor, fecha_calculo, estado
                ) VALUES (
                    :id_venta, :id_tienda, :monto_base, 10.00,
                    :valor_comision, :monto_final, NOW(), 'aplicada'
                )";

                $query_c = $this->acceso->prepare($sql_comision);
                $query_c->execute([
                    ':id_venta' => $ret->id_venta,
                    ':id_tienda' => $ret->id_tienda,
                    ':monto_base' => $ret->total_vendedor,
                    ':valor_comision' => $comision_valor,
                    ':monto_final' => $monto_final
                ]);

                // Liberar retención
                $sql_liberar = "UPDATE retencion 
                               SET liberada = 1, fecha_liberacion_real = NOW() 
                               WHERE id = :id";
                $query_l = $this->acceso->prepare($sql_liberar);
                $query_l->execute([':id' => $ret->id]);

                // Actualizar estado de la venta
                $sql_venta = "UPDATE venta SET estado = 'liberada' WHERE id = :id";
                $query_v = $this->acceso->prepare($sql_venta);
                $query_v->execute([':id' => $ret->id_venta]);

                // Actualizar saldo disponible del vendedor
                $this->actualizarSaldoVendedor($ret->id_tienda, $monto_final, 'disponible');

                // Registrar movimiento
                $this->registrarMovimiento(
                    $ret->id_tienda,
                    'venta',
                    "Venta liberada #{$ret->id_venta}",
                    $monto_final,
                    $ret->id_venta,
                    'venta'
                );

                $liberadas++;
            }

            $this->acceso->commit();
            return $liberadas;

        } catch (PDOException $e) {
            $this->acceso->rollBack();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * Aplicar descuento por servicio (banners, publicidad)
     */
    public function descontarServicio($id_tienda, $monto, $concepto, $id_servicio = null) {
        try {
            $this->acceso->beginTransaction();

            // Verificar saldo disponible
            $sql_saldo = "SELECT saldo_disponible FROM saldo_vendedor WHERE id_tienda = :id_tienda";
            $query_s = $this->acceso->prepare($sql_saldo);
            $query_s->execute([':id_tienda' => $id_tienda]);
            $saldo = $query_s->fetch();

            if (!$saldo || $saldo->saldo_disponible < $monto) {
                throw new Exception('Saldo insuficiente');
            }

            // Registrar el descuento en comisiones (servicios)
            $sql = "INSERT INTO comision (
                id_venta, id_tienda, monto_base, porcentaje,
                valor_comision, servicios_descontados, monto_final_vendedor,
                fecha_calculo, estado
            ) VALUES (
                NULL, :id_tienda, 0, 0,
                0, :monto, :monto_final,
                NOW(), 'aplicada'
            )";

            $monto_final = -$monto; // Negativo porque es un descuento

            $query = $this->acceso->prepare($sql);
            $query->execute([
                ':id_tienda' => $id_tienda,
                ':monto' => $monto,
                ':monto_final' => $monto_final
            ]);

            // Actualizar saldo
            $sql_update = "UPDATE saldo_vendedor 
                          SET saldo_disponible = saldo_disponible - :monto,
                              total_servicios = total_servicios + :monto,
                              fecha_actualizacion = NOW()
                          WHERE id_tienda = :id_tienda";

            $query_u = $this->acceso->prepare($sql_update);
            $query_u->execute([
                ':monto' => $monto,
                ':id_tienda' => $id_tienda
            ]);

            // Registrar movimiento
            $this->registrarMovimiento(
                $id_tienda,
                'servicio',
                $concepto,
                -$monto,
                $id_servicio,
                'banner_pago'
            );

            $this->acceso->commit();
            return true;

        } catch (PDOException $e) {
            $this->acceso->rollBack();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * Procesar devolución (reembolso al cliente)
     */
    public function procesarDevolucion($id_venta, $motivo, $id_admin) {
        try {
            $this->acceso->beginTransaction();

            // Obtener datos de la venta
            $sql_venta = "SELECT v.*, o.id_usuario as id_cliente 
                         FROM venta v
                         JOIN orden o ON v.id_orden = o.id
                         WHERE v.id = :id_venta";
            $query_v = $this->acceso->prepare($sql_venta);
            $query_v->execute([':id_venta' => $id_venta]);
            $venta = $query_v->fetch(PDO::FETCH_OBJ);

            if (!$venta) {
                throw new Exception('Venta no encontrada');
            }

            // Registrar devolución
            $sql_devolucion = "INSERT INTO devolucion (
                id_venta, id_orden, id_cliente, monto_devuelto,
                motivo, metodo_reembolso, estado, fecha_solicitud,
                id_admin_aprueba, fecha_aprobacion
            ) VALUES (
                :id_venta, :id_orden, :id_cliente, :monto_devuelto,
                :motivo, 'original', 'aprobada', NOW(),
                :id_admin, NOW()
            )";

            $query_d = $this->acceso->prepare($sql_devolucion);
            $query_d->execute([
                ':id_venta' => $id_venta,
                ':id_orden' => $venta->id_orden,
                ':id_cliente' => $venta->id_cliente,
                ':monto_devuelto' => $venta->total_vendedor,
                ':motivo' => $motivo,
                ':id_admin' => $id_admin
            ]);

            // Actualizar estado de la venta
            $sql_update = "UPDATE venta SET estado = 'devuelta' WHERE id = :id_venta";
            $query_u = $this->acceso->prepare($sql_update);
            $query_u->execute([':id_venta' => $id_venta]);

            // Si la venta ya fue liberada, descontar del saldo
            if ($venta->estado == 'liberada') {
                $sql_saldo = "UPDATE saldo_vendedor 
                             SET saldo_disponible = saldo_disponible - :monto
                             WHERE id_tienda = :id_tienda";
                $query_s = $this->acceso->prepare($sql_saldo);
                $query_s->execute([
                    ':monto' => $venta->total_vendedor,
                    ':id_tienda' => $venta->id_tienda
                ]);
            }

            $this->acceso->commit();
            return true;

        } catch (PDOException $e) {
            $this->acceso->rollBack();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * Iniciar disputa
     */
    public function iniciarDisputa($id_venta, $id_cliente, $tipo, $motivo) {
        try {
            $sql = "INSERT INTO disputa (
                id_venta, id_orden, id_cliente, id_vendedor,
                tipo, motivo, monto_reclamado, estado, fecha_apertura
            )
            SELECT v.id, v.id_orden, :id_cliente, u.id,
                   :tipo, :motivo, v.total_vendedor, 'abierta', NOW()
            FROM venta v
            JOIN tienda t ON v.id_tienda = t.id
            JOIN usuario u ON t.id_usuario = u.id
            WHERE v.id = :id_venta";

            $query = $this->acceso->prepare($sql);
            $result = $query->execute([
                ':id_venta' => $id_venta,
                ':id_cliente' => $id_cliente,
                ':tipo' => $tipo,
                ':motivo' => $motivo
            ]);

            if ($result) {
                // Actualizar estado de la venta
                $sql_update = "UPDATE venta SET estado = 'disputa' WHERE id = :id_venta";
                $query_u = $this->acceso->prepare($sql_update);
                $query_u->execute([':id_venta' => $id_venta]);
            }

            return $result;

        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * Resolver disputa (admin)
     */
    public function resolverDisputa($id_disputa, $decision, $id_admin, $comentario) {
        try {
            $this->acceso->beginTransaction();

            $sql = "UPDATE disputa 
                   SET estado = :decision,
                       decision_admin = :comentario,
                       id_admin_resuelve = :id_admin,
                       fecha_resolucion = NOW()
                   WHERE id = :id_disputa";

            $query = $this->acceso->prepare($sql);
            $query->execute([
                ':decision' => $decision,
                ':comentario' => $comentario,
                ':id_admin' => $id_admin,
                ':id_disputa' => $id_disputa
            ]);

            // Si la disputa se resuelve a favor del cliente, procesar devolución
            if ($decision == 'resuelta_favor_cliente') {
                $sql_venta = "SELECT id_venta FROM disputa WHERE id = :id_disputa";
                $query_v = $this->acceso->prepare($sql_venta);
                $query_v->execute([':id_disputa' => $id_disputa]);
                $id_venta = $query_v->fetch()->id_venta;

                $this->procesarDevolucion($id_venta, 'Resuelto por disputa', $id_admin);
            }

            $this->acceso->commit();
            return true;

        } catch (PDOException $e) {
            $this->acceso->rollBack();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * Actualizar saldo del vendedor
     */
    private function actualizarSaldoVendedor($id_tienda, $monto, $tipo) {
        // Verificar si ya existe registro de saldo
        $sql_check = "SELECT id FROM saldo_vendedor WHERE id_tienda = :id_tienda";
        $query_c = $this->acceso->prepare($sql_check);
        $query_c->execute([':id_tienda' => $id_tienda]);
        
        if ($query_c->rowCount() == 0) {
            // Crear registro
            $sql_insert = "INSERT INTO saldo_vendedor (
                id_tienda, id_vendedor, saldo_actual, saldo_retenido,
                saldo_disponible, total_ganado
            )
            SELECT :id_tienda, t.id_usuario, 0, 0, 0, 0
            FROM tienda t
            WHERE t.id = :id_tienda";

            $query_i = $this->acceso->prepare($sql_insert);
            $query_i->execute([':id_tienda' => $id_tienda]);
        }

        // Actualizar según tipo
        switch ($tipo) {
            case 'retenido':
                $sql = "UPDATE saldo_vendedor 
                       SET saldo_retenido = saldo_retenido + :monto,
                           saldo_actual = saldo_actual + :monto,
                           total_ganado = total_ganado + :monto
                       WHERE id_tienda = :id_tienda";
                break;

            case 'disponible':
                $sql = "UPDATE saldo_vendedor 
                       SET saldo_retenido = saldo_retenido - :monto,
                           saldo_disponible = saldo_disponible + :monto
                       WHERE id_tienda = :id_tienda";
                break;

            default:
                return false;
        }

        $query = $this->acceso->prepare($sql);
        return $query->execute([
            ':monto' => $monto,
            ':id_tienda' => $id_tienda
        ]);
    }

    /**
     * Registrar movimiento en historial
     */
    private function registrarMovimiento($id_tienda, $tipo, $concepto, $monto, $referencia_id, $referencia_tabla) {
        // Obtener saldo actual
        $sql_saldo = "SELECT saldo_actual FROM saldo_vendedor WHERE id_tienda = :id_tienda";
        $query_s = $this->acceso->prepare($sql_saldo);
        $query_s->execute([':id_tienda' => $id_tienda]);
        $saldo_anterior = $query_s->fetch()->saldo_actual ?? 0;

        $saldo_nuevo = $saldo_anterior + $monto;

        $sql = "INSERT INTO movimiento (
            id_tienda, tipo, concepto, monto,
            saldo_anterior, saldo_nuevo, referencia_id,
            referencia_tabla, fecha
        ) VALUES (
            :id_tienda, :tipo, :concepto, :monto,
            :saldo_anterior, :saldo_nuevo, :referencia_id,
            :referencia_tabla, NOW()
        )";

        $query = $this->acceso->prepare($sql);
        return $query->execute([
            ':id_tienda' => $id_tienda,
            ':tipo' => $tipo,
            ':concepto' => $concepto,
            ':monto' => $monto,
            ':saldo_anterior' => $saldo_anterior,
            ':saldo_nuevo' => $saldo_nuevo,
            ':referencia_id' => $referencia_id,
            ':referencia_tabla' => $referencia_tabla
        ]);
    }

    /**
     * Obtener resumen financiero de una tienda
     */
    public function getResumenTienda($id_tienda) {
        try {
            $sql = "SELECT * FROM saldo_vendedor WHERE id_tienda = :id_tienda";
            $query = $this->acceso->prepare($sql);
            $query->execute([':id_tienda' => $id_tienda]);
            $saldo = $query->fetch(PDO::FETCH_OBJ);

            // Últimos movimientos
            $sql_mov = "SELECT * FROM movimiento 
                       WHERE id_tienda = :id_tienda 
                       ORDER BY fecha DESC LIMIT 10";
            $query_m = $this->acceso->prepare($sql_mov);
            $query_m->execute([':id_tienda' => $id_tienda]);
            $movimientos = $query_m->fetchAll(PDO::FETCH_OBJ);

            // Próximas liberaciones
            $sql_lib = "SELECT COUNT(*) as pendientes, SUM(monto_retenido) as total_pendiente
                       FROM retencion r
                       JOIN venta v ON r.id_venta = v.id
                       WHERE v.id_tienda = :id_tienda 
                       AND r.liberada = 0";
            $query_l = $this->acceso->prepare($sql_lib);
            $query_l->execute([':id_tienda' => $id_tienda]);
            $liberaciones = $query_l->fetch(PDO::FETCH_OBJ);

            return [
                'saldo' => $saldo,
                'movimientos' => $movimientos,
                'proximas_liberaciones' => $liberaciones
            ];

        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
}
?>