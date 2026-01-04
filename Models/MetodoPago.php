<?php
include_once 'Conexion.php';
class MetodoPago {
    var $objetos;
    var $acceso;
    
    public function __construct() {
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }

    function obtener_metodos_usuario($id_usuario) {
    $sql = "SELECT * FROM usuario_metodo_pago 
            WHERE id_usuario = :id_usuario 
            AND estado = 'activo'
            ORDER BY predeterminado DESC, fecha_creacion DESC";
    
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

    function crear_metodo_pago($id_usuario, $tipo, $titular, $numero, $fecha_vencimiento = null, $cvv = null, $paypal_email = null, $banco = null, $numero_cuenta = null) {
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
}
