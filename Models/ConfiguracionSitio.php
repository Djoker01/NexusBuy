<?php
include_once 'Conexion.php';
class ConfiguracionSitio {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    // ✅ Obtener configuración por clave
    public function obtener($clave) {
        $sql = "SELECT * FROM configuracion_sitio WHERE clave = :clave";
        $config = $this->conexion->obtenerUno($sql, [':clave' => $clave]);
        
        if ($config) {
            // Convertir según el tipo
            switch ($config['tipo']) {
                case 'number':
                    return floatval($config['valor']);
                case 'boolean':
                    return filter_var($config['valor'], FILTER_VALIDATE_BOOLEAN);
                case 'json':
                    return json_decode($config['valor'], true);
                case 'array':
                    return explode(',', $config['valor']);
                default:
                    return $config['valor'];
            }
        }
        
        return null;
    }
    
    // ✅ Obtener todas las configuraciones
    public function obtenerTodas($categoria = null) {
        $where = "";
        $parametros = [];
        
        if ($categoria) {
            $where = "WHERE categoria = :categoria";
            $parametros[':categoria'] = $categoria;
        }
        
        $sql = "SELECT * FROM configuracion_sitio $where ORDER BY categoria, clave";
        return $this->conexion->obtenerTodos($sql, $parametros);
    }
    
    // ✅ Guardar configuración
    public function guardar($clave, $valor, $tipo = 'string', $categoria = 'general', $descripcion = null, $editable = true) {
        // Preparar valor según el tipo
        $valorPreparado = $this->prepararValor($valor, $tipo);
        
        $sql = "INSERT INTO configuracion_sitio (clave, valor, tipo, categoria, descripcion, editable)
                VALUES (:clave, :valor, :tipo, :categoria, :descripcion, :editable)
                ON DUPLICATE KEY UPDATE
                valor = VALUES(valor),
                tipo = VALUES(tipo),
                categoria = VALUES(categoria),
                descripcion = VALUES(descripcion),
                editable = VALUES(editable),
                fecha_actualizacion = CURRENT_TIMESTAMP";
        
        return $this->conexion->ejecutar($sql, [
            ':clave' => $clave,
            ':valor' => $valorPreparado,
            ':tipo' => $tipo,
            ':categoria' => $categoria,
            ':descripcion' => $descripcion,
            ':editable' => $editable
        ]);
    }
    
    // ✅ Eliminar configuración
    public function eliminar($clave) {
        $sql = "DELETE FROM configuracion_sitio WHERE clave = :clave AND editable = TRUE";
        return $this->conexion->ejecutar($sql, [':clave' => $clave]);
    }
    
    // ✅ Obtener configuración general del sitio
    public function obtenerConfiguracionGeneral() {
        $configs = $this->obtenerTodas();
        $resultado = [];
        
        foreach ($configs as $config) {
            $resultado[$config['clave']] = $this->obtener($config['clave']);
        }
        
        return $resultado;
    }
    
    // ✅ Preparar valor para almacenamiento
    private function prepararValor($valor, $tipo) {
        switch ($tipo) {
            case 'number':
                return strval($valor);
            case 'boolean':
                return $valor ? '1' : '0';
            case 'json':
                return json_encode($valor, JSON_UNESCAPED_UNICODE);
            case 'array':
                return is_array($valor) ? implode(',', $valor) : $valor;
            default:
                return strval($valor);
        }
    }
}