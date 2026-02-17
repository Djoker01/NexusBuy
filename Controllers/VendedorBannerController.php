<?php
// Controllers/VendedorBannerController.php
require_once '../../../Models/Banner.php';
require_once '../../../Models/Escrow.php';
require_once '../../../Models/Notificacion.php';

class VendedorBannerController {
    
    private $banner;
    private $escrow;
    private $notificacion;
    
    public function __construct() {
        $this->banner = new Banner();
        $this->escrow = new Escrow();
        $this->notificacion = new Notificacion();
        
        // Iniciar sesión si no está iniciada
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verificar que el usuario está logueado
        $this->verificarAcceso();
    }
    
    /**
     * Verifica que el usuario tenga acceso
     */
    private function verificarAcceso() {
        if (!isset($_SESSION['id_usuario'])) {
            header('Location: /login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
    }
    
    /**
     * Página principal: listado de banners del usuario
     */
    public function index() {
        $usuario_id = $_SESSION['id_usuario'];
        
        // Obtener banners del usuario
        $banners = $this->banner->getBannersByUsuario($usuario_id);
        
        // Obtener estadísticas generales
        $stats = $this->obtenerEstadisticasUsuario($usuario_id);
        
        // Obtener configuraciones de precios
        $configuraciones = $this->banner->getConfiguracionPrecios();
        
        // Obtener saldo disponible
        $saldo = $this->escrow->getSaldoDisponible($usuario_id);
        
        // Incluir la vista
        include 'Views/mi-tienda/banners/index.php';
    }
    
    /**
     * Mostrar formulario de creación
     */
    public function crear() {
        // Obtener configuraciones de precios
        $configuraciones = $this->banner->getConfiguracionPrecios();
        
        // Obtener saldo disponible
        $saldo = $this->escrow->getSaldoDisponible($_SESSION['id_usuario']);
        
        // Posiciones disponibles
        $posiciones = [
            'home' => 'Página Principal',
            'categorias' => 'Páginas de Categorías',
            'producto' => 'Página de Producto',
            'lateral' => 'Barra Lateral',
            'footer' => 'Pie de Página'
        ];
        
        include 'Views/mi-tienda/banners/crear.php';
    }
    
    /**
     * Procesar creación de banner
     */
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: /mi-tienda/banners/crear.php');
            exit;
        }
        
        $usuario_id = $_SESSION['id_usuario'];
        
        // Validar datos requeridos
        $errores = $this->validarDatosBanner($_POST);
        
        if (!empty($errores)) {
            $_SESSION['errores'] = $errores;
            $_SESSION['old_data'] = $_POST;
            header('Location: /mi-tienda/banners/crear.php');
            exit;
        }
        
        // Procesar la imagen
        $imagen = $this->procesarImagen($_FILES['imagen']);
        
        if (!$imagen) {
            $_SESSION['errores'] = ['imagen' => 'Error al subir la imagen. Asegúrate que sea JPG, PNG o GIF y menor a 2MB.'];
            $_SESSION['old_data'] = $_POST;
            header('Location: /mi-tienda/banners/crear.php');
            exit;
        }
        
        // Preparar datos para el banner
        $datos_banner = [
            'titulo' => $_POST['titulo'],
            'descripcion' => $_POST['descripcion'] ?? '',
            'imagen' => $imagen,
            'url' => $_POST['url'],
            'posicion' => $_POST['posicion'] ?? 'home',
            'orden' => 0,
            'texto_boton' => $_POST['texto_boton'] ?? 'Ver más',
            'icono_boton' => $_POST['icono_boton'] ?? '',
            'duracion' => $_POST['duracion']
        ];
        
        // Crear banner con pago
        $resultado = $this->banner->crearBannerConPago($datos_banner, $usuario_id);
        
        if ($resultado && $resultado->success) {
            // Crear notificación de éxito
            $this->notificacion->crearNotificacion(
                $usuario_id,
                'banner_creado',
                'Banner creado exitosamente',
                "Tu banner '{$_POST['titulo']}' ha sido creado y estará activo hasta el " . date('d/m/Y', strtotime($resultado->fecha_fin)),
                $resultado->banner_id
            );
            
            $_SESSION['success'] = $resultado->mensaje;
            header('Location: /mi-tiendar/banners/index.php');
        } else {
            $error = $resultado->error ?? 'Error al crear el banner';
            $_SESSION['error'] = $error;
            $_SESSION['old_data'] = $_POST;
            header('Location: /mi-tienda/banners/crear.php');
        }
        exit;
    }
    
    /**
     * Ver detalles de un banner
     */
    public function ver($id) {
        $usuario_id = $_SESSION['id_usuario'];
        
        // Obtener banner
        $banner = $this->banner->obtenerBannerPorId($id);
        
        // Verificar que el banner pertenece al usuario
        if (!$banner || $banner->id_usuario != $usuario_id) {
            $_SESSION['error'] = "Banner no encontrado";
            header('Location: /mi-tienda/banners/index.php');
            exit;
        }
        
        // Obtener estadísticas del banner
        $estadisticas = $this->banner->getEstadisticasBanner($id);
        
        // Obtener estadísticas por día (últimos 30 días)
        $estadisticas_diarias = $this->getEstadisticasDiarias($id);
        
        include 'Views/mi-tienda/banners/ver.php';
    }
    
    /**
     * Mostrar formulario de edición
     */
    public function editar($id) {
        $usuario_id = $_SESSION['id_usuario'];
        
        // Obtener banner
        $banner = $this->banner->obtenerBannerPorId($id);
        
        // Verificar que el banner pertenece al usuario
        if (!$banner || $banner->id_usuario != $usuario_id) {
            $_SESSION['error'] = "Banner no encontrado";
            header('Location: /mi-tienda/banners/index.php');
            exit;
        }
        
        // Solo se pueden editar banners que no están activos
        if ($banner->estado_pago == 'pagado' && $banner->estado == 1) {
            $_SESSION['error'] = "No puedes editar un banner activo. Debes cancelarlo primero.";
            header('Location: /mi-tiendar/banners/index.php');
            exit;
        }
        
        // Posiciones disponibles
        $posiciones = [
            'home' => 'Página Principal',
            'categorias' => 'Páginas de Categorías',
            'producto' => 'Página de Producto',
            'lateral' => 'Barra Lateral',
            'footer' => 'Pie de Página'
        ];
        
        include 'Views/mi-tienda/banners/editar.php';
    }
    
    /**
     * Procesar actualización de banner
     */
    public function actualizar($id) {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: /mi-tienda/banners/index.php');
            exit;
        }
        
        $usuario_id = $_SESSION['id_usuario'];
        
        // Verificar que el banner existe y pertenece al usuario
        $banner = $this->banner->obtenerBannerPorId($id);
        
        if (!$banner || $banner->id_usuario != $usuario_id) {
            $_SESSION['error'] = "Banner no encontrado";
            header('Location: /mi-tienda/banners/index.php');
            exit;
        }
        
        // Validar datos
        $errores = $this->validarDatosBanner($_POST, false);
        
        if (!empty($errores)) {
            $_SESSION['errores'] = $errores;
            $_SESSION['old_data'] = $_POST;
            header("Location: /mi-tienda/banners/editar.php?id={$id}");
            exit;
        }
        
        // Procesar imagen si se subió una nueva
        $imagen = $banner->imagen; // Mantener imagen actual por defecto
        
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == UPLOAD_ERR_OK) {
            $nueva_imagen = $this->procesarImagen($_FILES['imagen']);
            if ($nueva_imagen) {
                // Eliminar imagen anterior
                $this->eliminarImagenAnterior($banner->imagen);
                $imagen = $nueva_imagen;
            }
        }
        
        // Preparar datos para actualizar
        $datos = [
            'titulo' => $_POST['titulo'],
            'descripcion' => $_POST['descripcion'] ?? '',
            'imagen' => $imagen,
            'url' => $_POST['url'],
            'posicion' => $_POST['posicion'] ?? $banner->posicion,
            'orden' => $banner->orden,
            'texto_boton' => $_POST['texto_boton'] ?? $banner->texto_boton,
            'icono_boton' => $_POST['icono_boton'] ?? $banner->icono_boton,
            'fecha_inicio' => $banner->fecha_inicio,
            'fecha_fin' => $banner->fecha_fin,
            'estado' => $banner->estado
        ];
        
        // Actualizar banner
        $actualizado = $this->banner->actualizarBanner($id, $datos);
        
        if ($actualizado) {
            $_SESSION['success'] = "Banner actualizado correctamente";
            header("Location: /mi-tienda/banners/ver.php?id={$id}");
        } else {
            $_SESSION['error'] = "Error al actualizar el banner: " . $this->banner->error;
            header("Location: /mi-tienda/banners/editar.php?id={$id}");
        }
        exit;
    }
    
    /**
     * Cancelar banner y solicitar reembolso
     */
    public function cancelar($id) {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: /mi-tienda/banners/index.php');
            exit;
        }
        
        $usuario_id = $_SESSION['id_usuario'];
        $motivo = $_POST['motivo'] ?? 'Cancelado por el usuario';
        
        $resultado = $this->banner->cancelarBanner($id, $usuario_id, $motivo);
        
        if ($resultado && $resultado->success) {
            // Notificar al usuario
            $this->notificacion->crearNotificacion(
                $usuario_id,
                'banner_cancelado',
                'Banner cancelado',
                $resultado->mensaje,
                $id
            );
            
            $_SESSION['success'] = $resultado->mensaje;
        } else {
            $_SESSION['error'] = $resultado->error ?? 'Error al cancelar el banner';
        }
        
        header('Location: /mi-tienda/banners/index.php');
        exit;
    }
    
    /**
     * Renovar un banner existente
     */
    public function renovar($id) {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: /mi-tienda/banners/index.php');
            exit;
        }
        
        $usuario_id = $_SESSION['id_usuario'];
        $duracion = $_POST['duracion'];
        
        // Obtener banner original
        $banner_original = $this->banner->obtenerBannerPorId($id);
        
        if (!$banner_original || $banner_original->id_usuario != $usuario_id) {
            $_SESSION['error'] = "Banner no encontrado";
            header('Location: /mi-tienda/banners/index.php');
            exit;
        }
        
        // Crear nuevo banner con los mismos datos
        $datos_banner = [
            'titulo' => $banner_original->titulo,
            'descripcion' => $banner_original->descripcion,
            'imagen' => $banner_original->imagen,
            'url' => $banner_original->url,
            'posicion' => $banner_original->posicion,
            'texto_boton' => $banner_original->texto_boton,
            'icono_boton' => $banner_original->icono_boton,
            'duracion' => $duracion
        ];
        
        $resultado = $this->banner->crearBannerConPago($datos_banner, $usuario_id);
        
        if ($resultado && $resultado->success) {
            $_SESSION['success'] = "Banner renovado exitosamente. Vigencia hasta: " . date('d/m/Y', strtotime($resultado->fecha_fin));
            header("Location: /mi-tienda/banners/ver.php?id={$resultado->banner_id}");
        } else {
            $_SESSION['error'] = $resultado->error ?? 'Error al renovar el banner';
            header("Location: /mi-tienda/banners/ver.php?id={$id}");
        }
        exit;
    }
    
    /**
     * Mostrar estadísticas detalladas
     */
    public function estadisticas() {
        $usuario_id = $_SESSION['id_usuario'];
        
        // Obtener parámetros de filtro
        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
        $banner_id = $_GET['banner_id'] ?? null;
        
        // Obtener banners del usuario para el selector
        $banners = $this->banner->getBannersByUsuario($usuario_id, true);
        
        // Obtener estadísticas generales
        $estadisticas_generales = $this->getEstadisticasGenerales($usuario_id, $fecha_inicio, $fecha_fin);
        
        // Obtener top banners
        $top_banners = $this->banner->getTopBannersPorUsuario($usuario_id, $fecha_inicio, $fecha_fin);
        
        include 'Views/mi-tienda/banners/estadisticas.php';
    }
    
    /**
     * Exportar estadísticas a CSV
     */
    public function exportar() {
        $usuario_id = $_SESSION['id_usuario'];
        
        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
        
        // Obtener datos para exportar
        $datos = $this->getDatosExportacion($usuario_id, $fecha_inicio, $fecha_fin);
        
        // Configurar headers para descarga CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=banners_estadisticas_' . date('Y-m-d') . '.csv');
        
        // Crear archivo CSV
        $output = fopen('php://output', 'w');
        
        // UTF-8 BOM para Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Headers
        fputcsv($output, ['Banner', 'Fecha', 'Impresiones', 'Clicks', 'CTR (%)']);
        
        // Datos
        foreach ($datos as $fila) {
            fputcsv($output, $fila);
        }
        
        fclose($output);
        exit;
    }
    
    // ==================== MÉTODOS AUXILIARES ====================
    
    /**
     * Valida los datos del banner
     */
    private function validarDatosBanner($datos, $requerir_duracion = true) {
        $errores = [];
        
        if (empty($datos['titulo'])) {
            $errores['titulo'] = 'El título es obligatorio';
        } elseif (strlen($datos['titulo']) > 255) {
            $errores['titulo'] = 'El título no puede tener más de 255 caracteres';
        }
        
        if (!empty($datos['url']) && !filter_var($datos['url'], FILTER_VALIDATE_URL)) {
            $errores['url'] = 'La URL no es válida';
        }
        
        if ($requerir_duracion && empty($datos['duracion'])) {
            $errores['duracion'] = 'Debes seleccionar una duración';
        }
        
        return $errores;
    }
    
    /**
     * Procesa la subida de imagen
     */
    private function procesarImagen($archivo) {
        // Verificar que se subió un archivo
        if (!isset($archivo) || $archivo['error'] != UPLOAD_ERR_OK) {
            return false;
        }
        
        // Validar tipo de archivo
        $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $tipo_archivo = finfo_file($finfo, $archivo['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($tipo_archivo, $tipos_permitidos)) {
            return false;
        }
        
        // Validar tamaño (máximo 2MB)
        if ($archivo['size'] > 2 * 1024 * 1024) {
            return false;
        }
        
        // Generar nombre único
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombre_archivo = 'banner_' . uniqid() . '_' . time() . '.' . $extension;
        
        // Directorio de uploads (ajusta según tu estructura)
        $directorio = 'uploads/banners/';
        
        // Crear directorio si no existe
        if (!file_exists($directorio)) {
            mkdir($directorio, 0777, true);
        }
        
        $ruta_completa = $directorio . $nombre_archivo;
        
        // Mover archivo
        if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
            return $ruta_completa;
        }
        
        return false;
    }
    
    /**
     * Elimina imagen anterior
     */
    private function eliminarImagenAnterior($ruta_imagen) {
        if (file_exists($ruta_imagen) && strpos($ruta_imagen, 'uploads/') === 0) {
            unlink($ruta_imagen);
        }
    }
    
    /**
     * Obtiene estadísticas generales del usuario
     */
    private function obtenerEstadisticasUsuario($usuario_id) {
        $stats = [
            'total_banners' => 0,
            'banners_activos' => 0,
            'banners_vencidos' => 0,
            'total_invertido' => 0,
            'total_impresiones' => 0,
            'total_clicks' => 0,
            'ctr_promedio' => 0
        ];
        
        // Obtener banners
        $banners = $this->banner->getBannersByUsuario($usuario_id, true);
        
        if ($banners) {
            $stats['total_banners'] = count($banners);
            
            foreach ($banners as $b) {
                if ($b->estado == 1 && $b->fecha_fin > date('Y-m-d H:i:s')) {
                    $stats['banners_activos']++;
                } elseif (strtotime($b->fecha_fin) < time()) {
                    $stats['banners_vencidos']++;
                }
                
                $stats['total_invertido'] += $b->monto_pagado ?? 0;
                
                // Obtener estadísticas individuales
                $est = $this->banner->getEstadisticasBanner($b->id);
                if ($est) {
                    $stats['total_impresiones'] += $est->total_impresiones ?? 0;
                    $stats['total_clicks'] += $est->total_clicks ?? 0;
                }
            }
            
            if ($stats['total_impresiones'] > 0) {
                $stats['ctr_promedio'] = round(($stats['total_clicks'] / $stats['total_impresiones']) * 100, 2);
            }
        }
        
        return (object) $stats;
    }
    
    /**
     * Obtiene estadísticas diarias de un banner
     */
    private function getEstadisticasDiarias($banner_id, $dias = 30) {
        try {
            $sql = "SELECT 
                        fecha,
                        impresiones,
                        clicks,
                        CASE WHEN impresiones > 0 
                             THEN ROUND((clicks / impresiones) * 100, 2)
                             ELSE 0 
                        END as ctr
                    FROM banner_estadisticas
                    WHERE banner_id = :banner_id
                      AND fecha >= DATE_SUB(CURDATE(), INTERVAL :dias DAY)
                    ORDER BY fecha DESC";
            
            $query = $this->banner->acceso->prepare($sql);
            $query->bindParam(':banner_id', $banner_id);
            $query->bindParam(':dias', $dias, PDO::PARAM_INT);
            $query->execute();
            
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Obtiene estadísticas generales para el reporte
     */
    private function getEstadisticasGenerales($usuario_id, $fecha_inicio, $fecha_fin) {
        try {
            $sql = "SELECT 
                        DATE(e.fecha) as fecha,
                        COUNT(DISTINCT b.id) as banners_activos,
                        SUM(e.impresiones) as impresiones,
                        SUM(e.clicks) as clicks,
                        CASE WHEN SUM(e.impresiones) > 0 
                             THEN ROUND((SUM(e.clicks) / SUM(e.impresiones)) * 100, 2)
                             ELSE 0 
                        END as ctr
                    FROM banner_estadisticas e
                    INNER JOIN banners b ON e.banner_id = b.id
                    WHERE b.id_usuario = :usuario_id
                      AND e.fecha BETWEEN :fecha_inicio AND :fecha_fin
                    GROUP BY DATE(e.fecha)
                    ORDER BY fecha DESC";
            
            $query = $this->banner->acceso->prepare($sql);
            $query->bindParam(':usuario_id', $usuario_id);
            $query->bindParam(':fecha_inicio', $fecha_inicio);
            $query->bindParam(':fecha_fin', $fecha_fin);
            $query->execute();
            
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Obtiene datos para exportación
     */
    private function getDatosExportacion($usuario_id, $fecha_inicio, $fecha_fin) {
        try {
            $sql = "SELECT 
                        b.titulo as banner,
                        e.fecha,
                        e.impresiones,
                        e.clicks,
                        CASE WHEN e.impresiones > 0 
                             THEN ROUND((e.clicks / e.impresiones) * 100, 2)
                             ELSE 0 
                        END as ctr
                    FROM banner_estadisticas e
                    INNER JOIN banners b ON e.banner_id = b.id
                    WHERE b.id_usuario = :usuario_id
                      AND e.fecha BETWEEN :fecha_inicio AND :fecha_fin
                    ORDER BY e.fecha DESC, b.titulo";
            
            $query = $this->banner->acceso->prepare($sql);
            $query->bindParam(':usuario_id', $usuario_id);
            $query->bindParam(':fecha_inicio', $fecha_inicio);
            $query->bindParam(':fecha_fin', $fecha_fin);
            $query->execute();
            
            $resultados = $query->fetchAll(PDO::FETCH_ASSOC);
            
            // Formatear para CSV
            $datos = [];
            foreach ($resultados as $row) {
                $datos[] = [
                    $row['banner'],
                    date('d/m/Y', strtotime($row['fecha'])),
                    $row['impresiones'],
                    $row['clicks'],
                    $row['ctr']
                ];
            }
            
            return $datos;
        } catch (PDOException $e) {
            return [];
        }
    }
}

// ==================== MANEJO DE ACCIONES ====================
// Este código al final del archivo determina qué acción ejecutar
// basado en los parámetros de la URL

// Instanciar el controlador
$controller = new VendedorBannerController();

// Obtener la acción de la URL
$action = isset($_GET['action']) ? $_GET['action'] : 'index';
$id = isset($_GET['id']) ? $_GET['id'] : null;

// Ejecutar la acción correspondiente
switch ($action) {
    case 'crear':
        $controller->crear();
        break;
        
    case 'guardar':
        $controller->guardar();
        break;
        
    case 'ver':
        if ($id) {
            $controller->ver($id);
        } else {
            header('Location: /mi-tienda/banners/index.php');
        }
        break;
        
    case 'editar':
        if ($id) {
            $controller->editar($id);
        } else {
            header('Location: /mi-tienda/banners/index.php');
        }
        break;
        
    case 'actualizar':
        if ($id) {
            $controller->actualizar($id);
        } else {
            header('Location: /mi-tienda/banners/index.php');
        }
        break;
        
    case 'cancelar':
        if ($id) {
            $controller->cancelar($id);
        } else {
            header('Location: /mi-tienda/banners/index.php');
        }
        break;
        
    case 'renovar':
        if ($id) {
            $controller->renovar($id);
        } else {
            header('Location: /mi-tienda/banners/index.php');
        }
        break;
        
    case 'estadisticas':
        $controller->estadisticas();
        break;
        
    case 'exportar':
        $controller->exportar();
        break;
        
    default:
        $controller->index();
        break;
}
?>