<?php
include_once 'Conexion.php';
class ConfiguracionSitio
{
    var $objetos;
    var $acceso;

    public function __construct()
    {
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }

    // ✅ Obtener configuración por clave
    public function obtener($clave)
    {
        $sql = "SELECT * FROM configuracion_sitio WHERE clave = :clave";
        $query = $this->acceso->prepare($sql);
        $query->execute([':clave' => $clave]);
        $config = $query->fetch(PDO::FETCH_ASSOC);

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

    // ✅ Obtener configuración por categoría
    public function obtenerPorCategoria($categoria)
    {
        $sql = "SELECT * FROM configuracion_sitio WHERE categoria = :categoria ORDER BY clave";
        $query = $this->acceso->prepare($sql);
        $query->execute([':categoria' => $categoria]);
        $configs = $query->fetchAll(PDO::FETCH_ASSOC);

        $resultado = [];
        foreach ($configs as $config) {
            $resultado[$config['clave']] = [
                'valor' => $this->convertirValor($config['valor'], $config['tipo']),
                'descripcion' => $config['descripcion'],
                 'fecha_actualizacion' => $config['fecha_actualizacion']
            ];
        }

        return $resultado;
    }

    // ✅ Obtener todas las configuraciones
    public function obtenerTodas($categoria = null)
    {
        $sql = "SELECT * FROM configuracion_sitio";
        $parametros = [];

        if ($categoria) {
            $sql .= " WHERE categoria = :categoria";
            $parametros[':categoria'] = $categoria;
        }

        $sql .= " ORDER BY categoria, clave";
        $query = $this->acceso->prepare($sql);
        $query->execute($parametros);

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ Convertir valor según tipo
    private function convertirValor($valor, $tipo)
    {
        switch ($tipo) {
            case 'number':
                return floatval($valor);
            case 'boolean':
                return filter_var($valor, FILTER_VALIDATE_BOOLEAN);
            case 'json':
                return json_decode($valor, true);
            case 'array':
                return explode(',', $valor);
            default:
                return $valor;
        }
    }

    // ✅ Obtener datos de soporte específicos
    public function obtenerDatosSoporte()
    {
        $datos = [];

        // Información de contacto
        $contacto = $this->obtenerPorCategoria('contacto');
        $datos['contacto'] = $contacto;

        // Información legal
        $legal = $this->obtenerPorCategoria('legal');
        $datos['legal'] = $legal;

        // Información general
        $general = $this->obtenerPorCategoria('general');
        $datos['general'] = $general;

        // Información de finanzas
        $finanzas = $this->obtenerPorCategoria('finanzas');
        $datos['finanzas'] = $finanzas;

        return $datos;
    }

    // ✅ Guardar configuración (mantenido para futuras actualizaciones)
    public function guardar($clave, $valor, $tipo = 'string', $categoria = 'general', $descripcion = null, $editable = true)
    {
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

        $query = $this->acceso->prepare($sql);
        return $query->execute([
            ':clave' => $clave,
            ':valor' => $valorPreparado,
            ':tipo' => $tipo,
            ':categoria' => $categoria,
            ':descripcion' => $descripcion,
            ':editable' => $editable
        ]);
    }

    // ✅ Preparar valor para almacenamiento
    private function prepararValor($valor, $tipo)
    {
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

    public function obtenerDatosSitio()
    {
        return $this->obtenerDatosSoporte();
    }

    function obtenerRedesSociales() {
        $sql = "SELECT * FROM redes_sociales
                WHERE activa = 1";
        $query = $this->acceso->prepare($sql);
        $query->execute();
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }
}
