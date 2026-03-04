<?php
include_once 'Conexion.php';
class MetodoPago
{
    var $objetos;
    var $acceso;

    public function __construct()
    {
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }

    function obtener_metodos_usuario($id_usuario)
    {
        $sql = "SELECT * FROM usuario_metodo_pago 
            WHERE id_usuario = :id_usuario 
            AND estado = 'activo'
            ORDER BY es_predeterminado DESC, fecha_creacion DESC";

        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_usuario' => $id_usuario));

        // Asegurar que devuelve un array, incluso si está vacío
        $resultados = $query->fetchAll(PDO::FETCH_OBJ);

        if (!$resultados) {
            return []; // Siempre devolver array
        }

        $this->objetos = $resultados;
        return $this->objetos;
    }

    function crear_metodo_pago($id_usuario, $tipo, $titular, $numero, $fecha_vencimiento = null, $cvv = null, $paypal_email = null, $banco = null, $numero_cuenta = null)
    {
        // Primero, si este será el predeterminado, quitar predeterminado de otros
        if (true) { // Siempre hacerlo predeterminado por ahora
            $sql_quitar_pred = "UPDATE metodo_pago SET predeterminado = 0 WHERE id_usuario = :id_usuario";
            $query = $this->acceso->prepare($sql_quitar_pred);
            $query->execute(array(':id_usuario' => $id_usuario));
        }

        $sql = "INSERT INTO metodo_pago (id_usuario, tipo, titular, numero, fecha_vencimiento, cvv, paypal_email, banco, numero_cuenta, predeterminado) 
                VALUES (:id_usuario, :tipo, :titular, :numero, :fecha_vencimiento, :cvv, :paypal_email, :banco, :numero_cuenta, 1)";

        $query = $this->acceso->prepare($sql);
        $resultado = $query->execute(array(
            ':id_usuario' => $id_usuario,
            ':tipo' => $tipo,
            ':titular' => $titular,
            ':numero' => $numero,
            ':fecha_vencimiento' => $fecha_vencimiento,
            ':cvv' => $cvv,
            ':paypal_email' => $paypal_email,
            ':banco' => $banco,
            ':numero_cuenta' => $numero_cuenta
        ));

        return $resultado;
    }

    /**
     * Obtener la configuración específica de Transfermóvil desde la tabla configuracion_sitio.
     * Esta función es clave para el flujo de pago manual.
     * 
     * @return array|false Arreglo con los datos de configuración o false si no se encuentran.
     */
    function obtenerConfiguracionTransfermovil()
    {
        try {
            $claves_requeridas = [
                'transfermovil_numero_tarjeta',
                'transfermovil_nombre_titular',
                'transfermovil_banco',
                'transfermovil_activo'
            ];

            // Convertir el array de claves a una lista para la consulta SQL
            $marcadores = implode(',', array_fill(0, count($claves_requeridas), '?'));

            $sql = "SELECT clave, valor 
                FROM configuracion_sitio 
                WHERE clave IN ($marcadores)";

            $query = $this->acceso->prepare($sql);
            $query->execute($claves_requeridas);
            $resultados = $query->fetchAll(PDO::FETCH_OBJ);

            if (empty($resultados)) {
                error_log("Advertencia: No se encontró la configuración de Transfermóvil en configuracion_sitio.");
                return false;
            }

            // Organizar los resultados en un array asociativo
            $config = [];
            foreach ($resultados as $row) {
                $config[$row->clave] = $row->valor;
            }

            // Verificar que todas las claves requeridas estén presentes
            foreach ($claves_requeridas as $clave) {
                if (!isset($config[$clave])) {
                    $config[$clave] = ''; // Asignar un valor por defecto si falta
                }
            }

            // Verificar si Transfermóvil está activo en el sistema
            $config['activo'] = ($config['transfermovil_activo'] == '1');

            // Formatear un nombre más amigable para usar en las respuestas
            $config['nombre_display'] = 'Transfermóvil';
            if (!empty($config['transfermovil_nombre_titular'])) {
                $config['nombre_display'] .= ' - ' . $config['transfermovil_nombre_titular'];
            }

            return $config;
        } catch (Exception $e) {
            error_log("Error en obtenerConfiguracionTransfermovil: " . $e->getMessage());
            return false;
        }
    }
}
