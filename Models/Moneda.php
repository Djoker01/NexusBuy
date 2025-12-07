<?php
include_once 'Conexion.php';
class Moneda
{
    var $objetos;
    var $acceso;

    public function __construct()
    {
        $db = new Conexion();
        $this->acceso = $db->pdo;
        $this->objetos = array(); // Inicializar el array
    }

    function obtener_monedas()
    {
        $sql = "SELECT id, codigo, nombre, simbolo, tasa_cambio,
                       CASE WHEN codigo = 'CUP' THEN 1 ELSE 0 END as predeterminada
                FROM moneda 
                WHERE estado = 'activa' 
                ORDER BY codigo = 'CUP' DESC, nombre";

        $query = $this->acceso->prepare($sql);
        $query->execute();
        $this->objetos = $query->fetchAll(PDO::FETCH_OBJ);
        return $this->objetos;
    }

    function obtener_tasa_cambio($moneda_codigo)
    {
        $sql = "SELECT codigo, nombre, simbolo, tasa_cambio 
                FROM moneda 
                WHERE codigo = :codigo AND estado = 'activa'";

        $query = $this->acceso->prepare($sql);
        $query->execute(array(':codigo' => $moneda_codigo));
        $moneda_data = $query->fetch(PDO::FETCH_ASSOC);

        if ($moneda_data) {
            $this->objetos = $moneda_data;
        } else {
            $this->objetos = array();
        }
        return $this->objetos;
    }


    function guardar_moneda_preferida($id_usuario, $moneda_codigo)
    {
        try {
            $sql_verificar = "SELECT codigo FROM moneda WHERE codigo = :codigo AND estado = 'activa'";
            $query = $this->acceso->prepare($sql_verificar);
            $query->execute(array(':codigo' => $moneda_codigo));
            $moneda_existe = $query->fetch();

            if (!$moneda_existe) {
                return false;
            }

            $sql = "INSERT INTO usuario_configuracion (usuario_id, tipo_config, valor_config) 
                    VALUES (:usuario_id, 'moneda_preferida', :moneda_codigo)
                    ON DUPLICATE KEY UPDATE valor_config = :moneda_codigo_dup, fecha_actualizacion = CURRENT_TIMESTAMP";

            $query = $this->acceso->prepare($sql);
            $resultado = $query->execute(array(
                ':usuario_id' => $id_usuario,
                ':moneda_codigo' => $moneda_codigo,
                ':moneda_codigo_dup' => $moneda_codigo
            ));

            return $resultado;
        } catch (Exception $e) {
            error_log("Error en guardar_moneda_preferida: " . $e->getMessage());
            return false;
        }
    }

    function obtener_moneda_preferida($id_usuario)
    {
        $sql = "SELECT valor_config FROM usuario_configuracion 
                WHERE usuario_id = :usuario_id AND tipo_config = 'moneda_preferida'";

        $query = $this->acceso->prepare($sql);
        $query->execute(array(':usuario_id' => $id_usuario));
        $config = $query->fetch();

        if ($config) {
            return $config['valor_config'];
        }
        return 'CUP';
    }

    function convertir_precio_productos($productos, $moneda_destino)
    {
        try {
            // Obtener información de la moneda destino
            $sql_moneda = "SELECT codigo, nombre, simbolo, tasa_cambio 
                          FROM moneda 
                          WHERE codigo = :codigo AND estado = 'activa'";

            $query = $this->acceso->prepare($sql_moneda);
            $query->execute(array(':codigo' => $moneda_destino));
            $moneda_data = $query->fetch(PDO::FETCH_ASSOC);

            if (!$moneda_data) {
                return $productos;
            }

            $tasa_cambio = floatval($moneda_data['tasa_cambio']);
            $simbolo = $moneda_data['simbolo'];

            // Convertir precios de todos los productos
            foreach ($productos as &$producto) {
                // Precio original
                if (isset($producto['precio'])) {
                    $producto['precio_original'] = $producto['precio'];
                    $producto['precio_convertido'] = floatval($producto['precio']) / $tasa_cambio;
                }

                // Precio con descuento
                if (isset($producto['precio_descuento'])) {
                    $producto['precio_descuento_original'] = $producto['precio_descuento'];
                    $producto['precio_descuento_convertido'] = floatval($producto['precio_descuento']) / $tasa_cambio;
                }

                // Precio unitario (para carrito)
                if (isset($producto['precio_unitario'])) {
                    $producto['precio_unitario_convertido'] = floatval($producto['precio_unitario']) / $tasa_cambio;
                }

                // Agregar información de moneda
                $producto['moneda_actual'] = $moneda_destino;
                $producto['simbolo_moneda'] = $simbolo;
                $producto['tasa_cambio'] = $tasa_cambio;
            }

            return $productos;
        } catch (Exception $e) {
            error_log("Error en convertir_precio_productos: " . $e->getMessage());
            return $productos;
        }
    }
}
