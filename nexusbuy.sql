-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 02-02-2026 a las 04:03:32
-- Versión del servidor: 10.4.17-MariaDB
-- Versión de PHP: 7.4.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `nexusbuy`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `cerrar_conversacion_chat` (IN `p_conversacion_id` VARCHAR(50), IN `p_estado` ENUM('cerrada','resuelta'), IN `p_valoracion` TINYINT, IN `p_comentario` TEXT)  BEGIN
    -- Obtener agente asignado
    DECLARE v_agente_id INT;
    
    SELECT agente_asignado INTO v_agente_id
    FROM `chat_conversaciones`
    WHERE id = p_conversacion_id;
    
    -- Cerrar conversación
    UPDATE `chat_conversaciones`
    SET estado = p_estado,
        fecha_cierre = CURRENT_TIMESTAMP(),
        valoracion = p_valoracion,
        comentario_cierre = p_comentario
    WHERE id = p_conversacion_id;
    
    -- Liberar agente si existe
    IF v_agente_id IS NOT NULL THEN
        UPDATE `chat_agentes`
        SET conversaciones_activas = GREATEST(0, conversaciones_activas - 1)
        WHERE id = v_agente_id;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `crear_conversacion_chat` (IN `p_conversacion_id` VARCHAR(50), IN `p_usuario_id` INT, IN `p_nombre_usuario` VARCHAR(100), IN `p_email_usuario` VARCHAR(100), IN `p_asunto` VARCHAR(200), IN `p_categoria` ENUM('general','tecnico','ventas','devoluciones','otros'))  BEGIN
    INSERT INTO `chat_conversaciones` 
    (`id`, `usuario_id`, `nombre_usuario`, `email_usuario`, `asunto`, `categoria`, `estado`)
    VALUES 
    (p_conversacion_id, p_usuario_id, p_nombre_usuario, p_email_usuario, p_asunto, p_categoria, 'activa');
    
    -- Asignar automáticamente un agente disponible
    UPDATE `chat_conversaciones` c
    JOIN (
        SELECT id 
        FROM `chat_agentes` 
        WHERE estado = 'disponible' 
        AND conversaciones_activas < max_conversaciones
        ORDER BY conversaciones_activas ASC, RAND()
        LIMIT 1
    ) a ON 1=1
    SET c.agente_asignado = a.id
    WHERE c.id = p_conversacion_id;
    
    -- Actualizar contador del agente
    UPDATE `chat_agentes` 
    SET conversaciones_activas = conversaciones_activas + 1
    WHERE id = (SELECT agente_asignado FROM `chat_conversaciones` WHERE id = p_conversacion_id);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `enviar_mensaje_chat` (IN `p_conversacion_id` VARCHAR(50), IN `p_usuario_id` INT, IN `p_nombre_usuario` VARCHAR(100), IN `p_email_usuario` VARCHAR(100), IN `p_mensaje` TEXT, IN `p_tipo` ENUM('usuario','agente','sistema'), IN `p_ip_address` VARCHAR(45), IN `p_user_agent` TEXT)  BEGIN
    -- Insertar mensaje
    INSERT INTO `chat_mensajes` 
    (`conversacion_id`, `usuario_id`, `nombre_usuario`, `email_usuario`, 
     `mensaje`, `tipo`, `ip_address`, `user_agent`)
    VALUES 
    (p_conversacion_id, p_usuario_id, p_nombre_usuario, p_email_usuario, 
     p_mensaje, p_tipo, p_ip_address, p_user_agent);
    
    -- Actualizar última actividad de la conversación
    UPDATE `chat_conversaciones`
    SET ultimo_mensaje = CURRENT_TIMESTAMP()
    WHERE id = p_conversacion_id;
    
    -- Si es mensaje de agente, actualizar su última actividad
    IF p_tipo = 'agente' THEN
        UPDATE `chat_agentes`
        SET ultima_actividad = CURRENT_TIMESTAMP()
        WHERE usuario_id = p_usuario_id;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `generar_referencia_transfermovil` (IN `p_orden_id` INT, OUT `p_referencia` VARCHAR(50), OUT `p_hash_verificacion` VARCHAR(64))  BEGIN
    DECLARE v_prefijo VARCHAR(10);
    DECLARE v_contador INT;
    DECLARE v_numero_secuencia VARCHAR(6);
    DECLARE v_existe_referencia INT;
    
    -- Verificar si la orden ya tiene referencia
    SELECT COUNT(*) INTO v_existe_referencia 
    FROM `orden` 
    WHERE `id` = p_orden_id AND `referencia_pago` IS NOT NULL;
    
    IF v_existe_referencia > 0 THEN
        -- Ya tiene referencia, devolver la existente
        SELECT `referencia_pago`, `hash_verificacion` 
        INTO p_referencia, p_hash_verificacion
        FROM `orden` 
        WHERE `id` = p_orden_id;
    ELSE
        -- Obtener el prefijo de la configuración
        SELECT COALESCE(valor, 'NX') INTO v_prefijo 
        FROM configuracion_sitio 
        WHERE clave = 'transfermovil_prefijo_referencia'
        LIMIT 1;
        
        -- Generar número secuencial basado en fecha
        SELECT COUNT(*) + 1 INTO v_contador 
        FROM `orden` 
        WHERE DATE(fecha_creacion) = CURDATE();
        
        SET v_numero_secuencia = LPAD(v_contador, 4, '0');
        
        -- Generar referencia: PREFIJO-AAAAMMDD-NUMERO
        SET p_referencia = CONCAT(
            v_prefijo, '-',
            DATE_FORMAT(NOW(), '%Y%m%d'), '-',
            v_numero_secuencia
        );
        
        -- Generar hash de verificación (orden_id + referencia + timestamp)
        SET p_hash_verificacion = SHA2(CONCAT(p_orden_id, p_referencia, UNIX_TIMESTAMP(), RAND()), 256);
        
        -- Actualizar la orden con la referencia y hash
        UPDATE `orden` 
        SET `referencia_pago` = p_referencia,
            `hash_verificacion` = p_hash_verificacion,
            `estado_pago` = 'pendiente',
            `fecha_pago` = NOW()
        WHERE `id` = p_orden_id;
        
        -- Registrar en logs
        INSERT INTO `logs_pagos` (`orden_id`, `accion`, `detalles`, `usuario_id`)
        SELECT p_orden_id, 'referencia_generada', 
               CONCAT('Referencia: ', p_referencia, ' | Hash: ', LEFT(p_hash_verificacion, 16)),
               `id_usuario`
        FROM `orden` 
        WHERE `id` = p_orden_id;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_chat_limpiar_conversaciones_antiguas` (IN `dias` INT)  BEGIN
    DECLARE fecha_limite DATETIME;
    SET fecha_limite = DATE_SUB(NOW(), INTERVAL dias DAY);
    
    -- Marcar como cerradas las conversaciones inactivas
    UPDATE chat_conversaciones 
    SET estado = 'cerrada', 
        fecha_cierre = NOW(),
        comentario_cierre = 'Cerrada automáticamente por inactividad'
    WHERE estado IN ('en_espera', 'activa')
    AND ultimo_mensaje < fecha_limite;
    
    SELECT ROW_COUNT() as conversaciones_cerradas;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_chat_resumen_agente` (IN `agente_id` INT)  BEGIN
    SELECT 
        ca.nombre_agente,
        ca.estado,
        ca.conversaciones_activas,
        ca.max_conversaciones,
        ca.ultima_actividad,
        (SELECT COUNT(*) FROM chat_conversaciones WHERE agente_asignado = agente_id AND estado = 'activa') as conversaciones_activas_asignadas,
        (SELECT COUNT(*) FROM chat_conversaciones WHERE agente_asignado = agente_id AND estado = 'cerrada') as conversaciones_cerradas_asignadas,
        (SELECT COUNT(*) FROM chat_mensajes cm 
         JOIN chat_conversaciones cc ON cm.conversacion_id = cc.id 
         WHERE cc.agente_asignado = agente_id AND cm.tipo = 'agente' AND DATE(cm.fecha_envio) = CURDATE()) as mensajes_enviados_hoy,
        (SELECT AVG(valoracion) FROM chat_conversaciones WHERE agente_asignado = agente_id AND valoracion IS NOT NULL) as valoracion_promedio
    FROM chat_agentes ca
    WHERE ca.id = agente_id;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bundle`
--

CREATE TABLE `bundle` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `imagen` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'default_bundle.png',
  `precio_original` decimal(12,2) NOT NULL,
  `precio_oferta` decimal(12,2) NOT NULL,
  `descuento_porcentaje` decimal(5,2) DEFAULT 0.00,
  `tienda_id` int(11) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `fecha_inicio` datetime DEFAULT NULL,
  `fecha_fin` datetime DEFAULT NULL,
  `estado` enum('activo','inactivo','agotado') COLLATE utf8mb4_unicode_ci DEFAULT 'activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `bundle`
--

INSERT INTO `bundle` (`id`, `nombre`, `descripcion`, `imagen`, `precio_original`, `precio_oferta`, `descuento_porcentaje`, `tienda_id`, `stock`, `fecha_inicio`, `fecha_fin`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(6, 'Kit Tecnología Premium', 'Productos tecnológicos seleccionados para máxima productividad', 'producto_default.png', '1395000.00', '299.99', '99.98', 1, 10, '2025-12-27 21:20:42', '2026-02-25 21:20:42', 'activo', '2025-12-28 02:20:42', '2025-12-28 02:29:48'),
(7, 'Set Hogar y Bienestar', 'Productos para cuidar tu hogar y tu persona', 'producto_default.png', '27900.00', '149.99', '99.46', 1, 15, '2025-12-27 21:20:42', '2026-02-10 21:20:42', 'activo', '2025-12-28 02:20:42', '2025-12-28 02:29:32'),
(8, 'Combo Fitness Total', 'Equipo completo para mantenerte en forma', 'producto_default.png', '121500.00', '199.99', '99.84', 1, 8, '2025-12-27 21:20:43', '2026-01-26 21:20:43', 'activo', '2025-12-28 02:20:43', '2025-12-28 02:30:00'),
(9, 'Kit Mascotas Felices', 'Todo lo que necesitan tus mascotas y bebés', 'producto_default.png', '40500.00', '89.99', '99.78', 1, 12, '2025-12-27 21:20:43', '2026-03-27 21:20:43', 'activo', '2025-12-28 02:20:43', '2025-12-28 02:30:08');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bundle_producto`
--

CREATE TABLE `bundle_producto` (
  `id` int(11) NOT NULL,
  `bundle_id` int(11) NOT NULL,
  `producto_tienda_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `bundle_producto`
--

INSERT INTO `bundle_producto` (`id`, `bundle_id`, `producto_tienda_id`, `cantidad`, `fecha_creacion`) VALUES
(7, 6, 6, 1, '2025-12-28 02:20:42'),
(8, 6, 8, 1, '2025-12-28 02:20:42'),
(9, 6, 10, 1, '2025-12-28 02:20:42'),
(10, 7, 74, 1, '2025-12-28 02:20:43'),
(11, 7, 73, 1, '2025-12-28 02:20:43'),
(12, 7, 72, 1, '2025-12-28 02:20:43'),
(13, 8, 3, 1, '2025-12-28 02:20:43'),
(14, 8, 68, 1, '2025-12-28 02:20:43'),
(15, 8, 67, 1, '2025-12-28 02:20:43'),
(16, 9, 57, 1, '2025-12-28 02:20:43'),
(17, 9, 59, 1, '2025-12-28 02:20:43');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--

CREATE TABLE `carrito` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_producto_tienda` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL CHECK (`cantidad` > 0),
  `fecha_agregado` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `carrito`
--

INSERT INTO `carrito` (`id`, `id_usuario`, `session_id`, `id_producto_tienda`, `cantidad`, `fecha_agregado`, `fecha_actualizacion`) VALUES
(39, 2, NULL, 5, 1, '2026-01-24 20:36:22', '2026-01-24 20:36:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `imagen` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'default_category.png',
  `icono` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `orden` int(11) DEFAULT 0,
  `estado` enum('activa','inactiva') COLLATE utf8mb4_unicode_ci DEFAULT 'activa',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`id`, `nombre`, `descripcion`, `imagen`, `icono`, `orden`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'Smartphones y Teléfonos', 'Teléfonos inteligentes, smartphones, tablets y accesorios móviles', 'smartphones y teléfonos.jpg', 'fas fa-mobile-alt', 1, 'activa', '2025-09-25 21:27:30', '2025-11-17 21:13:22'),
(2, 'Alimentos y Bebidas', 'Alimentos, bebidas, productos gourmet y despensa', 'alimentos.jpg', 'fas fa-utensils', 2, 'activa', '2025-10-06 17:01:19', '2025-11-17 21:28:14'),
(3, 'Belleza y Cuidado Personal', 'Productos de belleza, cosméticos, cuidado de la piel y cabello', 'belleza y cuidado personal.jpg', 'fas fa-spa', 3, 'activa', '2025-10-06 17:01:19', '2025-11-17 21:28:20'),
(4, 'Computadoras y Laptops', 'Computadoras de escritorio, laptops, notebooks y workstations', 'computadoras y laptops.jpg', 'fas fa-laptop', 4, 'activa', '2025-10-06 20:46:29', '2025-11-17 21:15:57'),
(5, 'Electrodomésticos', 'Electrodomésticos mayores y menores para el hogar', 'electrodomésticos.jpg', 'fas fa-blender', 5, 'activa', '2025-10-06 17:01:19', '2025-11-17 21:28:26'),
(6, 'Salud y Bienestar', 'Productos médicos, suplementos y artículos para el bienestar', 'salud y bienestar.jpg', 'fas fa-heartbeat', 6, 'activa', '2025-10-06 17:01:19', '2025-11-24 15:43:07'),
(7, 'Moda, Ropa y Accesorios', 'Ropa, calzado, accesorios de moda para hombres y mujeres', 'moda.jpg', 'fas fa-tshirt', 7, 'activa', '2025-10-06 17:01:19', '2025-11-17 21:28:32'),
(8, 'Productos para Bebé', 'Artículos para bebés, pañales, alimentación y cuidado infantil', 'productos para bebes.jpg', 'fas fa-baby', 8, 'activa', '2025-10-06 17:01:19', '2025-11-17 21:29:05'),
(9, 'Hogar y Jardín', 'Muebles, decoración, jardinería y artículos para el hogar', 'hogar.jpg', 'fas fa-home', 9, 'activa', '2025-10-06 17:01:19', '2025-11-17 21:29:17'),
(10, 'Mascotas', 'Alimentos, juguetes y accesorios para mascotas', 'mascotas.jpg', 'fas fa-paw', 10, 'activa', '2025-10-06 17:01:19', '2025-11-17 21:29:30'),
(11, 'Deportes', 'Artículos deportivos, equipo de ejercicio y actividades al aire libre', 'deportes.jpg', 'fas fa-futbol', 11, 'activa', '2025-10-06 17:01:19', '2025-11-17 21:29:43'),
(12, 'Automóvil', 'Repuestos, accesorios y productos para automóviles', 'automotriz.jpg', 'fas fa-car', 12, 'activa', '2025-10-06 17:09:08', '2025-11-17 21:30:00'),
(13, 'Oficina y Papelería', 'Artículos de oficina, papelería y suministros escolares', 'oficina.jpg', 'fas fa-print', 13, 'activa', '2025-10-06 17:09:48', '2025-11-17 21:30:11'),
(14, 'Televisores y Video', 'Televisores, pantallas, proyectores y equipos de video', 'televisores.jpg', 'fas fa-tv', 14, 'activa', '2025-10-06 20:50:17', '2025-11-17 21:30:24'),
(15, 'Audio y Sonido', 'Equipos de audio, parlantes, sistemas de sonido y accesorios', 'audio.jpg', 'fas fa-headphones', 15, 'activa', '2025-10-06 20:52:50', '2025-11-17 21:30:33'),
(16, 'Cámaras y Fotografía', 'Cámaras digitales, lentes, accesorios y equipo fotográfico', 'camara.jpg', 'fas fa-camera', 16, 'activa', '2025-10-06 20:58:50', '2025-11-17 21:30:57'),
(17, 'Videojuegos', 'Consolas, videojuegos, controles y accesorios gaming', 'videojuegos.jpg', 'fas fa-gamepad', 17, 'activa', '2025-10-06 21:01:23', '2025-11-17 21:31:10'),
(18, 'Bricolaje y Ferretería', 'Herramientas, materiales de construcción y artículos de ferretería', 'bricolaje.jpg', 'fas fa-tools', 18, 'activa', '2025-10-06 21:19:04', '2025-11-17 21:31:22'),
(19, 'Fitness y Ejercicio', 'Equipo de ejercicio, fitness, yoga y entrenamiento personal', 'fitness.jpg', 'fas fa-dumbbell', 19, 'activa', '2025-10-06 21:57:58', '2025-11-17 21:31:38'),
(23, 'Juguetes y Juegos', 'Juguetes educativos, juegos de mesa, juguetes electrónicos', 'juguetes.jpg', 'fas fa-gamepad', 20, 'activa', '2025-12-16 14:46:47', '2025-12-16 14:46:47'),
(24, 'Libros y Material Educativo', 'Libros, textos, material escolar y educativo', 'libros.jpg', 'fas fa-book', 21, 'activa', '2025-12-16 14:46:47', '2025-12-16 14:46:47'),
(25, 'Instrumentos Musicales', 'Instrumentos musicales y equipos de audio profesional', 'instrumentos.jpg', 'fas fa-guitar', 22, 'activa', '2025-12-16 14:46:47', '2025-12-16 14:46:47'),
(26, 'Joyería y Relojes', 'Joyas, relojes y accesorios de lujo', 'joyeria.jpg', 'fas fa-gem', 23, 'activa', '2025-12-16 14:46:47', '2025-12-16 14:46:47'),
(27, 'Viajes y Turismo', 'Equipaje, maletas y accesorios de viaje', 'viajes.jpg', 'fas fa-suitcase-rolling', 24, 'activa', '2025-12-16 14:46:47', '2025-12-16 14:46:47'),
(28, 'Suministros Industriales', 'Equipos y materiales para industria y construcción', 'industrial.jpg', 'fas fa-industry', 25, 'activa', '2025-12-16 14:46:47', '2026-01-15 00:28:35'),
(29, 'Arte y Manualidades', 'Materiales de arte, manualidades y bellas artes', 'arte.jpg', 'fas fa-paint-brush', 26, 'activa', '2025-12-16 14:46:47', '2025-12-16 14:46:47'),
(30, 'Sex Shop', 'Productos para la intimidad y vida sexual', 'sexshop.jpg', 'fas fa-heart', 27, 'activa', '2025-12-16 14:46:47', '2025-12-16 14:46:47'),
(31, 'Productos Ecológicos', 'Productos orgánicos, biodegradables y sostenibles', 'ecologico.jpg', 'fas fa-leaf', 28, 'activa', '2025-12-16 14:46:47', '2025-12-16 14:46:47'),
(32, 'Outlet y Ofertas', 'Productos en oferta, descuentos y liquidaciones', 'outlet.jpg', 'fas fa-tag', 29, 'inactiva', '2025-12-16 14:46:47', '2026-01-15 00:29:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria_oferta`
--

CREATE TABLE `categoria_oferta` (
  `id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `descuento_maximo` decimal(5,2) DEFAULT NULL,
  `imagen_banner` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color_fondo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '#4361ee',
  `orden` int(11) DEFAULT 0,
  `fecha_inicio` datetime DEFAULT NULL,
  `fecha_fin` datetime DEFAULT NULL,
  `estado` enum('activa','inactiva') COLLATE utf8mb4_unicode_ci DEFAULT 'activa',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categoria_oferta`
--

INSERT INTO `categoria_oferta` (`id`, `categoria_id`, `descuento_maximo`, `imagen_banner`, `color_fondo`, `orden`, `fecha_inicio`, `fecha_fin`, `estado`, `fecha_creacion`) VALUES
(4, 1, '35.00', 'smartphones.jpg', '#4361ee', 1, '2025-12-27 15:02:47', '2026-01-31 23:59:59', 'activa', '2025-12-27 20:02:47'),
(5, 7, '50.00', 'moda.jpg', '#f72585', 2, '2025-12-27 15:02:47', '2026-01-15 23:59:59', 'activa', '2025-12-27 20:02:47'),
(6, 17, '40.00', 'videojuegos.jpg', '#7209b7', 3, '2025-12-27 15:02:47', '2026-02-28 23:59:59', 'activa', '2025-12-27 20:02:47'),
(7, 9, '30.00', 'hogar.jpg', '#4cc9f0', 4, '2025-12-27 15:02:47', '2026-01-20 23:59:59', 'activa', '2025-12-27 20:02:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_agentes`
--

CREATE TABLE `chat_agentes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nombre_agente` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('disponible','ocupado','offline') COLLATE utf8mb4_unicode_ci DEFAULT 'offline',
  `conversaciones_activas` int(11) DEFAULT 0,
  `max_conversaciones` int(11) DEFAULT 5,
  `ultima_actividad` timestamp NULL DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `chat_agentes`
--

INSERT INTO `chat_agentes` (`id`, `usuario_id`, `nombre_agente`, `estado`, `conversaciones_activas`, `max_conversaciones`, `ultima_actividad`, `fecha_creacion`) VALUES
(1, 1, 'Soporte NexusBuy', 'disponible', 2, 5, NULL, '2026-01-07 10:40:44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_configuracion`
--

CREATE TABLE `chat_configuracion` (
  `id` int(11) NOT NULL,
  `clave` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'texto',
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `chat_configuracion`
--

INSERT INTO `chat_configuracion` (`id`, `clave`, `valor`, `descripcion`, `tipo`, `fecha_actualizacion`) VALUES
(1, 'tiempo_polling', '3000', 'Intervalo de polling en milisegundos', 'numero', '2026-01-07 10:40:43'),
(2, 'max_mensajes_carga', '100', 'Máximo de mensajes a cargar por vez', 'numero', '2026-01-07 10:40:43'),
(3, 'horario_atencion_inicio', '09:00', 'Hora de inicio de atención', 'hora', '2026-01-07 10:40:43'),
(4, 'horario_atencion_fin', '18:00', 'Hora de fin de atención', 'hora', '2026-01-07 10:40:43'),
(5, 'mensaje_bienvenida', '¡Hola! ¿En qué puedo ayudarte?', 'Mensaje de bienvenida automático', 'texto', '2026-01-07 10:40:43'),
(6, 'tiempo_maximo_inactividad', '30', 'Tiempo máximo de inactividad en minutos', 'numero', '2026-01-07 10:40:43'),
(7, 'notificar_nuevos_mensajes', '1', 'Notificar nuevos mensajes', 'booleano', '2026-01-07 10:40:43');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_conversaciones`
--

CREATE TABLE `chat_conversaciones` (
  `id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `nombre_usuario` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_usuario` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `asunto` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT 'Consulta general',
  `categoria` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'general',
  `agente_asignado` int(11) DEFAULT NULL,
  `estado` enum('en_espera','activa','cerrada','resuelta') COLLATE utf8mb4_unicode_ci DEFAULT 'en_espera',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_inicio` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultimo_mensaje` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fecha_cierre` timestamp NULL DEFAULT NULL,
  `valoracion` tinyint(4) DEFAULT NULL CHECK (`valoracion` >= 0 and `valoracion` <= 5),
  `comentario_cierre` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `chat_conversaciones`
--

INSERT INTO `chat_conversaciones` (`id`, `usuario_id`, `nombre_usuario`, `email_usuario`, `asunto`, `categoria`, `agente_asignado`, `estado`, `ip_address`, `user_agent`, `fecha_inicio`, `ultimo_mensaje`, `fecha_cierre`, `valoracion`, `comentario_cierre`) VALUES
('a1f2d8177700d3e63b703c02af507b5a', 2, 'Noel', 'ass@gmail.com', '123', 'general', 1, 'activa', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 11:01:28', '2026-01-15 00:08:37', NULL, NULL, NULL);

--
-- Disparadores `chat_conversaciones`
--
DELIMITER $$
CREATE TRIGGER `trg_chat_actualizar_contador_agente` AFTER UPDATE ON `chat_conversaciones` FOR EACH ROW BEGIN
    -- Si se asigna un agente nuevo
    IF NEW.agente_asignado IS NOT NULL AND (OLD.agente_asignado IS NULL OR OLD.agente_asignado != NEW.agente_asignado) THEN
        UPDATE chat_agentes 
        SET conversaciones_activas = conversaciones_activas + 1
        WHERE id = NEW.agente_asignado;
    END IF;
    
    -- Si se desasigna un agente
    IF OLD.agente_asignado IS NOT NULL AND (NEW.agente_asignado IS NULL OR NEW.agente_asignado != OLD.agente_asignado) THEN
        UPDATE chat_agentes 
        SET conversaciones_activas = GREATEST(0, conversaciones_activas - 1)
        WHERE id = OLD.agente_asignado;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_mensajes`
--

CREATE TABLE `chat_mensajes` (
  `id` int(11) NOT NULL,
  `conversacion_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `nombre_usuario` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_usuario` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'usuario',
  `leido` tinyint(1) DEFAULT 0,
  `estado` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'enviado',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_envio` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_lectura` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `chat_mensajes`
--

INSERT INTO `chat_mensajes` (`id`, `conversacion_id`, `usuario_id`, `nombre_usuario`, `email_usuario`, `mensaje`, `tipo`, `leido`, `estado`, `ip_address`, `user_agent`, `fecha_envio`, `fecha_lectura`) VALUES
(1, 'a1f2d8177700d3e63b703c02af507b5a', NULL, 'Sistema', 'sistema@nexusbuy.com', '¡Hola Noel! Has iniciado una conversación de chat. Un agente de soporte te atenderá en breve. Por favor, describe tu consulta.', 'sistema', 0, 'enviado', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 11:01:30', NULL),
(2, 'a1f2d8177700d3e63b703c02af507b5a', 2, 'Noel', 'ass@gmail.com', '456', 'usuario', 0, 'enviado', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 11:22:09', NULL),
(3, 'a1f2d8177700d3e63b703c02af507b5a', 2, 'Noel', 'ass@gmail.com', '7989', 'usuario', 0, 'enviado', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 11:22:23', NULL),
(4, 'a1f2d8177700d3e63b703c02af507b5a', 2, 'Noel', 'ass@gmail.com', '897', 'usuario', 0, 'enviado', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 11:23:12', NULL),
(5, 'a1f2d8177700d3e63b703c02af507b5a', 2, 'Noel', 'ass@gmail.com', '235', 'usuario', 0, 'enviado', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 11:39:19', NULL),
(6, 'a1f2d8177700d3e63b703c02af507b5a', 2, 'Noel', 'ass@gmail.com', '3', 'usuario', 0, 'enviado', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 11:40:32', NULL),
(7, 'a1f2d8177700d3e63b703c02af507b5a', 2, 'Noel', 'ass@gmail.com', '1', 'usuario', 0, 'enviado', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 11:44:39', NULL),
(8, 'a1f2d8177700d3e63b703c02af507b5a', 2, 'Noel', 'ass@gmail.com', '1', 'usuario', 0, 'enviado', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 11:46:08', NULL),
(9, 'a1f2d8177700d3e63b703c02af507b5a', 2, 'Noel', 'ass@gmail.com', '2', 'usuario', 0, 'enviado', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 11:46:22', NULL),
(10, 'a1f2d8177700d3e63b703c02af507b5a', 2, 'Noel', 'ass@gmail.com', '55', 'usuario', 0, 'enviado', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 11:50:59', NULL),
(11, 'a1f2d8177700d3e63b703c02af507b5a', 2, 'Noel', 'ass@gmail.com', '5', 'usuario', 0, 'enviado', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 12:26:38', NULL),
(12, 'a1f2d8177700d3e63b703c02af507b5a', 2, 'Noel', 'ass@gmail.com', 'hola', 'usuario', 0, 'enviado', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-15 00:08:37', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_notificaciones`
--

CREATE TABLE `chat_notificaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `conversacion_id` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `leida` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_lectura` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_sitio`
--

CREATE TABLE `configuracion_sitio` (
  `id` int(11) NOT NULL,
  `clave` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('string','number','boolean','json','array') COLLATE utf8mb4_unicode_ci DEFAULT 'string',
  `categoria` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'general',
  `descripcion` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `editable` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `configuracion_sitio`
--

INSERT INTO `configuracion_sitio` (`id`, `clave`, `valor`, `tipo`, `categoria`, `descripcion`, `editable`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'nombre_tienda', 'NexusBuy', 'string', 'general', 'Nombre de la tienda', 1, '2025-11-14 14:54:30', '2025-11-14 14:54:30'),
(2, 'slogan', 'Tu tienda online de confianza', 'string', 'general', 'Slogan de la tienda', 1, '2025-11-14 14:54:30', '2025-11-14 14:54:30'),
(3, 'moneda_principal', 'CUP', 'string', 'finanzas', 'Moneda principal del sitio', 1, '2025-11-14 14:54:30', '2025-11-14 14:54:30'),
(4, 'impuesto_venta', '10', 'number', 'finanzas', 'Porcentaje de impuesto sobre ventas', 1, '2025-11-14 14:54:30', '2026-01-31 06:28:21'),
(5, 'costo_envio_gratis', '50.00', 'number', 'envios', 'Monto mínimo para envío gratis', 1, '2025-11-14 14:54:30', '2025-11-14 14:54:30'),
(6, 'dias_para_devolucion', '30', 'number', 'devoluciones', 'Días permitidos para devoluciones', 1, '2025-11-14 14:54:30', '2025-11-14 14:54:30'),
(7, 'email_contacto', 'ventas@nexusbuy.com', 'string', 'contacto', 'Email de contacto principal', 1, '2025-11-14 14:54:30', '2025-11-14 14:54:30'),
(8, 'telefono_contacto', '+5351004754', 'string', 'contacto', 'Teléfono de contacto', 1, '2025-11-14 14:54:30', '2026-01-04 03:34:03'),
(9, 'direccion_principal', 'Avenida Principal #123', 'string', 'contacto', 'Dirección física de la tienda', 1, '2025-11-14 14:54:30', '2026-01-09 03:33:08'),
(10, 'politica_privacidad', 'Este Aviso de Privacidad para NexusBuy (“nosotros”), describe cómo y por qué podríamos acceder, recopilar, almacenar, usar y/o compartir (“proceso”) su información personal cuando utilice nuestros servicios (“Servicio”), incluyendo cuando usted: ¿Tienes alguna pregunta o inquietud? Leer este Aviso de Privacidad te ayudará a comprender tus derechos y opciones en materia de privacidad. Somos responsables de decidir cómo se procesa tu información personal. Si no estás de acuerdo con nuestras políticas y prácticas, por favor, no uses nuestro Servicios.\r\n\r\nRESUMEN DE PUNTOS CLAVE\r\nEste resumen presenta los puntos clave de nuestro Aviso de Privacidad, pero puede encontrar más detalles sobre cualquiera de estos temas haciendo clic en el enlace que aparece después de cada punto clave o utilizando nuestra tabla de contenido a continuación para encontrar la sección que busca.\r\n¿Qué  información personal procesamos? Al visitar, usar o navegar por nuestros Servicios, podemos procesar información personal según cómo interactúe con nosotros y los Servicios, las opciones que seleccione y los productos y funciones sobre la información personal que nos proporciona.\r\n¿Procesamos información personal sensible? Parte de la información puede considerarse sensible “especial” o “sensible”. En ciertas jurisdicciones, por ejemplo, su origen racial o ético, su orientación sexual y sus creencias religiosas. No procesamos información personal sensible.\r\n¿Recopilamos información de terceros? Podemos recopilar información de bases de datos públicas, socios de marketing, plataformas de redes sociales y otras fuentes externas. Obtenga más información sobre la información recopilada de otras fuentes.\r\n¿Cómo tratamos su información? Tratamos su información para proporcionar, mejorar y administrar nuestros Servicios, comunicarnos con usted, para garantizar la seguridad y prevenir el fraude, y para cumplir con la ley. También podemos tratar su información para otros fines con su consentimiento. Solo tratamos su información cuando tenemos una razón legal válida para hacerlo. Obtenga más información sobre como tratamos su información.\r\n¿En qué situaciones y con cuáles? ¿Con qué terceros compartimos información personal? Podemos compartir información en situaciones específicas y con entidades específicas de terceros. Obtenga más información sobre cuándo y con quien compartimos su información personal.\r\n¿Cuáles son sus derechos? Dependiendo de su ubicación geográfica, la ley de privacidad aplicable puede otorgarle ciertos derechos con respecto a su información personal. Obtenga más información sobre sus derechos de privacidad.\r\n¿Cómo ejerces tus derechos? La forma más sencilla de ejercer tu derechos es mediante la presentación de una solicitud de acceso del interesado o bien, poniéndose en contacto con nosotros. Consideraremos y daremos respuesta a cualquier legislación aplicable en materia de protección de datos.\r\n¿Desea obtener más información sobre qué hacemos con la información que recopilamos? Consulte el Aviso de privacidad completo.\r\n\r\nTABLA DE CONTENIDO\r\n\r\n1.	¿QUÉ INFORMACIÓN RECOPILAMOS?\r\n2.	¿CÓMO PROCESAMOS SU INFORMACIÓN?\r\n3.	¿CUÁNDO Y CON QUIÉN COMPARTIMOS SU INFORMACIÓN PERSONAL?\r\n4.	¿UTILIZAMOS COOKIES Y OTRAS TECNOLOGÍAS DE SEGUIMIENTO?\r\n5.	¿CÓMO GESTIONAMOS SUS INICIOS DE SESIÓN EN REDES SOCIALES?\r\n6.	¿SE TRANSFIERE SU INFORMACIÓN IMTERNACIONALMENTE?\r\n7.	¿DURANTE CUÁNTO TIEMPO CONSERVAMOS SU INFORMACIÓN?\r\n8.	¿RECOPILAMOS INFORMACIÓN DE MENORES?\r\n9.	¿CUÁLES SON SUS DERECHOS DE PRIVACIDAD?\r\n10.	CONTROLES PARA LAS FUNCIONES DE NO RASTREAR\r\n11.	¿ACTUALIZAMOS ESTE AVISO?\r\n12.	¿CÓMO PUEDE CONTACTAR CON NOSOTROS EN RELACIÓN CON ESTE AVISO?\r\n13.	¿CÓMO PUEDE REVISAR, ACTUALIZAR O ELIMINAR LOS DATOS QUE RECOPILAMOS DE USTED?\r\n\r\n1.	¿QUÉ INFORMACIÓN RECOPILAMOS?\r\nInformación personal que usted nos revela\r\nEn resumen: Recopilamos la información personal que usted nos proporciona, la cual varía si actúa como Cliente o como Vendedor.\r\n\r\nRecopilamos la información personal que usted nos proporciona voluntariamente cuando se registra en los Servicios, expresa interés en obtener información sobre nosotros, participa en actividades en los Servicios o se pone en contacto con nosotros.\r\nLa información recopilada incluye, según su rol:\r\n•	Para Clientes y Vendedores: Nombre, dirección de correo electrónico, contraseña, dirección de envío y facturación, número de teléfono.\r\n•	Específica para Vendedores: Información fiscal, dirección comercial, datos de la cuenta bancaria para recibir pagos, y descripciones e imágenes de los productos que vende.\r\n•	Información de Pago: Los datos de su tarjeta de crédito/débito o de su cuenta de PayPal son procesados directamente por nuestro proveedor de pagos autorizado y no se almacenan en nuestros servidores.\r\nInformación confidencial: No solicitamos ni procesamos a sabiendas información personal considerada “sensible” según la mayoría de las legislaciones (origen racial, ético, creencias religiosas, etc.).\r\nInformación recopilada automáticamente\r\nEn resumen: Recopilamos automáticamente información sobre su dispositivo y como interactúa con nuestros Servicios.\r\nRecopilamos automáticamente cierta información cuando visita, usa o navega por los Servicios. Esta información no revela su identidad específica pero puede incluir: dirección IP, características del navegador y del dispositivo, sistema operativo, preferencias de idioma, URL de referencia, páginas visualizadas, duración de la visita, y otra información técnica. Esta información es necesaria para mantener la seguridad y el funcionamiento de nuestros Servicios y para nuestro análisis e informes internos.\r\n\r\n2.	¿CÓMO PROCESAMOS SU INFORMACIÓN?\r\nEn resumen: Procesamos su información para proporcionar, mejorar y administrar nuestros Servicios, comunicarnos con usted, para la seguridad y la prevención del fraude, y para cumplir con la ley.\r\n\r\nProcesamos su información personal por los siguientes motivos y según las bases legales indicadas:\r\nFinalidad del Procesamiento	Base Legal\r\nFacilitar la creación y gestión de su cuenta	Ejecución de un contrato (los Términos y Condiciones).\r\nGestionar pagos, devoluciones y procesar pedidos como Cliente	Ejecución de un contrato.\r\nHabilitar la funcionalidad de Vendedor: publicar productos, recibir pagos, contactar con Clientes.	Ejecución de un contrato.\r\nEnviar comunicaciones administrativas (confirmaciones de pedidos, actualizaciones de políticas, avisos de seguridad).	Interés legítimo (para operar nuestros Servicios de forma segura y eficiente).\r\nEnviar marketing y ofertas promocionales (solo si ha dado su consentimiento, que puede retirar en cualquier momento).	Consentimiento.\r\nProteger la seguridad de nuestros Servicios, prevenir fraudes e investigar actividades maliciosas.	Interés legítimo (proteger nuestro negocio y a nuestros usuarios).\r\nCumplir con nuestras obligaciones legales (como requisitos fiscales y de mantenimiento de registros).	Cumplimiento de una obligación legal.\r\nAnalizar y mejorar nuestros Servicios (a través de cookies analíticas, sujeto a sus preferencias).	Interés legítimo (mejorar y optimizar nuestros Servicios.\r\n\r\n3.	¿CUÁNDO Y CON QUIÉN COMPARTIMOS SU INFORMACIÓN PERSONAL?\r\nEn resumen: Podemos compartir información en situaciones específicas descritas en esta sección y/o con terceros específicos.\r\n\r\nPodemos necesitar compartir su información personal en las siguientes situaciones:\r\n•	Vendedores y Clientes: Para completar una transacción, compartimos la información necesaria del Cliente (nombre, dirección de envío) con el Vendedor, y la información necesaria del Vendedor (nombre de la tienda) con el Cliente.\r\n•	Proveedores de Servicios de Pago: Compartimos los datos de la transacción con nuestros procesadores de pago (ej.: Stripe, PayPal) para facilitar los pagos.\r\n•	Proveedores de Servicios: Empresas que nos prestan servicios como alojamiento web, análisis de datos, envío de correos electrónicos y servicios al cliente.\r\nEstos proveedores tienen acceso a su información solo para realizar tareas en nuestro nombre y están obligados a no divulgar ni utilizarla para otros fines.\r\n•	Transferencias comerciales: En relación con una fusión, venta de activos de la empresa, financiación o adquisición de la totalidad o una parte de nuestro negocio por otra empresa.\r\n•	Cumplimiento de la Ley: Si estamos legalmente obligados a hacerlo, podemos divulgar su información para cumplir con la ley aplicable, solicitudes gubernamentales, o para proteger nuestros derechos.\r\n\r\n4.	¿UTILIZAMOS COOKIES Y OTRAS TECNOLOGÍAS DE SEGUIMIENTO?\r\nEn resumen: Podemos utilizar cookies y otras tecnologías de seguimiento para recopilar y almacenar su información.\r\nPodemos utilizar cookies y tecnologías de seguimiento similares (como balizas web y píxeles) para recopilar información cuando interactúas con nuestros Servicios. Algunas tecnologías de seguimiento en línea nos ayudan a mantener la seguridad de nuestros Servicios, evitar fallos, corregir errores, guardar tus preferencias y ayudarte con las funciones básicas del sitio.\r\nTambién permitirnos que terceros y proveedores de servicios utilicen tecnologías de seguimiento en línea en nuestros Servicios para análisis y publicidad, incluyendo la gestión y visualización de anuncios según tus intereses o el envío de recordatorios de carritos de compra abandonados (según tus preferencias de comunicación). Estos terceros y proveedores de servicios utilizan su tecnología para ofrecer publicidad sobre productos y servicios adaptados a tus intereses, a cual puede aparecer tanto en nuestros Servicios como en otros sitios web.\r\nEn nuestro Aviso de Cookies encontrará información específica sobre como utilizamos estas tecnologías y como puede rechazar ciertos cookies.\r\n\r\n5.	¿CÓMO GESTIONAMOS SUS INICIOS DE SESIÓN EN REDES SOCIALES?\r\nEn resumen: Si decide registrarse o iniciar sesión en nuestros Servicios utilizando una cuenta de redes sociales, es posible que tengamos acceso a cierta información sobre usted.\r\n\r\nNuestros servicios le permiten registrarse e iniciar sección utilizando los datos de su cuenta de redes sociales (Facebook y Google). Si elige esta opción, recibiremos cierta información de su perfil de su proveedor de redes sociales. Esta información puede variar según el proveedor, pero generalmente incluye su nombre, correo electrónico, lista de amigos, foto de perfil, así como otra información que haya decidido hacer pública en dicha plataforma.\r\nUtilizaremos las información que recibamos únicamente para los fines descritos en este Aviso de Privacidad o que se le indique claramente en los Servicios correspondientes. Tenga en cuenta que no controlamos ni somos responsable del uso que su proveedor de redes sociales haga de su información personal. Le recomendamos que revise su aviso de privacidad para comprender cómo recopilan, usan y comparten su información personal, y como puede configurar sus preferencias de privacidad en sitios web y aplicaciones.\r\n\r\n6.	¿SE TRANSFIERE SU INFORMACIÓN IMTERNACIONALMENTE?\r\nEn resumen: Podemos transferir, almacenar y procesar su información en países distintos al suyo.\r\n\r\nNuestros servidores están ubicados en Cuba. Si se encuentra fuera de Cuba, su información puede ser transferida, almacenada y procesada por nosotros o nuestros proveedores en Cuba y otras jurisdicciones.\r\n\r\n7.	¿DURANTE CUÁNTO TIEMPO CONSERVAMOS SU INFORMACIÓN?\r\nEn resumen: Conservamos su información durante el tiempo necesario para realizar los fines descritos en este Aviso de Privacidad, salvo que la ley exija otra cosa.\r\n\r\nSolo conservamos su información personal durante un tiempo necesario para los fines establecidos en este Aviso de Privacidad, a menos que la ley exija o permita un periodo de retención más prolongado (como en el caso de cualquier requisito fiscal, contable u otro requisito legal).\r\nCuando ya no tengamos una necesidad comercial legítima para procesar su información personal, la eliminaremos o anonimizar dicha información, o, si esto no es posible (por ejemplo, porque su información se ha almacenado en copias de seguridad), almacenaremos de forma segura su información personal y la aislaremos posteriormente hasta que sea posible su eliminación.\r\n\r\n8.	¿RECOPILAMOS INFORMACIÓN DE MENORES?\r\nEn resumen: No recopilamos datos de personas a sabiendas ni les hacemos marketing a niños menores de 18 años.\r\nNo recopilamos, solicitamos datos ni realizamos marketing dirigido a niños menores de 18 años a sabiendas. Al usar los Servicios, usted declara tener al menos 18 años o que es el padre, madre o tutor legal de dicho menor y  consiente el uso de los Servicios por parte de dicho menor. Si descubrimos que hemos recopilado información personal de un usuario menor de 18 años sin verificación del consentimiento parental, tomaremos las medidas necesarias para eliminar dicha información de nuestros servidores a la mayor veracidad. Por favor, contáctenos en soporte@nexusbuy.com si tiene conocimiento de que hemos recopilado datos de un menor.\r\n\r\n9.	¿CUÁLES SON SUS DERECHOS DE PRIVACIDAD?\r\nEn resumen: Dependiendo de su ubicación, puede tener derechos específicos sobre su información personal.\r\nDependiendo de su lugar de residencia, las leyes de protección de datos pueden otorgarle cierto derecho sobre su información personal. Estos pueden incluir el derecho a:\r\n•	Acceder y obtener una copia de su información personal.\r\n•	Rectificar o actualizar su información personal inexacta o incompleta.\r\n•	Suprimir su información personal.\r\n•	Oponerse al tratamiento de su información personal.\r\n•	Restringir el tratamiento de su información personal.\r\n•	Portar sus datos a otros servicios.\r\n•	Retirar su consentimiento en cualquier momento, si el tratamiento se basaba en ello.\r\nPara ejercer estos derechos, puede acceder a la configuración de su cuenta o contactarnos usando los datos de la Sección 12. Verificaremos su identidad antes de responder a cualquier solicitud. Tenga en cuenta que ciertos derechos pueden ser limitados por la ley (ej.: podemos negarnos a eliminar información que necesitemos para cumplir con una obligación legal).\r\n\r\n10.	CONTROLES PARA LAS FUNCIONES DE NO RASTREAR\r\nLa mayoría de los navegadores web y algunos sistemas operativos móviles y aplicaciones móviles incluyen una función de No rastrear (“DNT”) función o configuración que puede activar para indicar su preferencia de privacidad de que no se supervisen ni recopilen datos sobre sus actividades de navegación en línea. En esta etapa, no existe un estándar tecnológico uniforme para reconocer la implementación de señales DNT ha sido finalizado. Por lo tanto, actualmente no respondemos a las señales DNT del navegador ni a ningún otro mecanismo que comunique automáticamente su decisión de no ser rastreado en línea. Si en el futuro se adopta un estándar para el seguimiento en línea que debemos cumplir, le informaremos sobre dicha práctica en una versión revisada de este Aviso de Privacidad.\r\n\r\n11.	¿ACTUALIZAMOS ESTE AVISO?\r\nEn resumen: Sí, actualizaremos este aviso según sea necesario para cumplir con las leyes pertinentes.\r\nEs posible que actualicemos este Aviso de Privacidad periódicamente. La versión actualizada se indicara mediante un aviso de actualización “Revisado” la fecha se encuentra en la parte superior de este Aviso de Privacidad. Si realizamos cambios sustanciales en este Aviso de Privacidad, le notificaremos publicando un aviso destacado de dichos cambios o enviándole una notificación directamente. Le recordamos revisar este Aviso de Privacidad con frecuencia para estar al tanto de cómo protegemos su información.\r\n\r\n12.	¿CÓMO PUEDE CONTACTAR CON NOSOTROS EN RELACIÓN CON ESTE AVISO?\r\nSi tiene preguntas o comentarios sobre este aviso, puede contactarnos por correo postal a la siguiente dirección:\r\n{Nombre de la empresa}\r\n{Dirección completa}\r\n{Ciudad, Código Postal}\r\nO por correo electrónico a privacidad@nexusbuy.com\r\n\r\n13.	¿CÓMO PUEDE REVISAR, ACTUALIZAR O ELIMINAR LOS DATOS QUE RECOPILAMOS DE USTED?\r\nEn base a las leyes aplicables de su país, tiene derecho a solicitar acceso, corrección o eliminación de su información personal. Para hacerlo, puede:\r\n1.	Acceder a la configuración de su cuenta en nuestro sitio web o aplicación y actualizar su información directamente.\r\n2.	Enviarnos una solicitud a la dirección de contacto indicada en la Sección 12.\r\n', 'string', 'legal', 'Política de privacidad', 1, '2025-11-14 14:54:30', '2025-11-14 14:54:30'),
(11, 'terminos_condiciones', 'ACUERDO CON NUESTRO TÉRMINOS Y CONDICIONES:\r\n\r\nSomos NexusBuy, en lo adelante “Nosotros”. Operamos el sitio web https://www.nexusbuy.com (el “Sitio”), así como cualesquier otro producto y servicio relacionado que tengan referencia o enlacen a estos Términos Legales (los “Términos Legales”) (colectivamente, los “Servicios”).\r\nPuedes contactarnos por correo electrónico a contacto@nexusbuy.com o por correo  {Dirección completa de la empresa}.\r\nEstos Términos Legales constituyen un acuerdo jurídicamente vinculante celebrado entre usted, ya sea personalmente o en nombre de una entidad (“usted”), y Nosotros.  En relación con su acceso y uso de los Servicios, usted acepta que, al acceder a ellos, ha leído, comprendido y aceptado todos estos Términos Legales. SI NO ESTÁ DE ACUERDO CON TODOS ESTOS TÉRMINOS LEGALES, TIENE EXPRESAMENTE PROHIBIDO UTILIZAR LOS SERVICIOS Y DEBE INTERUMPIR SU USO INMEDIATAMENTE.\r\n\r\nLos términos y condiciones o documentos complementarios que se publiquen en los Servicios de vez en cuando (como la Política de Privacidad, Política de Devoluciones, etc.) se incorporan expresamente al presente documento por referencia. Nos reservamos el derecho, a nuestra entera discreción, de realizar cambios o modificaciones a estos Términos Legales en cualquier momento y por cualquier motivo. Le avisaremos sobre cualquier cambio actualizando la “Última actualización”. Al aceptar estos Términos Legales, usted acepta que es su responsabilidad revisar periódicamente estos Términos Legales para mantenerse al tanto de las actualizaciones. Su uso continuado de los Servicios después de la fecha de publicación de los Términos Legales revisados implica que usted los ha aceptado.\r\n\r\n\r\n\r\n\r\n \r\nTABLA DE CONTENIDO\r\n\r\n1.	NUESTROS SERVICIOS Y ROLES DE USUARIO\r\n2.	DERECHOS DE PROPIEDAD INTELECTUAL\r\n3.	REPRESENTACIONES DEL USUARIO\r\n4.	REGISTRO Y CUENTAS\r\n5.	COMPRAS Y PAGOS (ROL DEL CLIENE)\r\n6.	VENTAS Y OBLIGACIONES DEL VENDEDOR\r\n7.	ACTIVIDADES PROHIBIDAS\r\n8.	CONTRIBUCIONES GENERADAS POR EL USUARIO\r\n9.	GESTIÓN Y SERVICIOS\r\n10.	PLAZO Y TERMINACIÓN\r\n11.	MODIFICACIONES E INTERRUPCIONES\r\n12.	LEY APLICABLE\r\n13.	RESOLUCIÓN DE DISPUTAS\r\n14.	CORRECCIONES\r\n15.	DESCARGO DE RESPONSABILIDAD\r\n16.	LIMITACIONES DE RESPONSABILIDAD\r\n17.	INDEMNIZACIÓN\r\n18.	DATOS DEL USUARIO\r\n19.	COMUNICACIONES ELECTRÓNICAS\r\n20.	MISCELÁNEOS\r\n21.	CONTÁCTENOS\r\n\r\n\r\n1.	NUESTROS SERVICIOS Y ROLES DEL USUARIO\r\n\r\nLos Servicios permiten a los usuarios registrarse con dos roles principales:\r\n•	Cliente: Usuario que navega, compra y adquiere productos ofrecidos por los Vendedores en la plataforma.\r\n•	Vendedor: Usuario que se registra para publicar, ofrecer y vender productos a los Clientes a través de la plataforma.\r\nUn usuario puede tener ambos roles, pero debe cumplir con las obligaciones de cada uno. Nosotros actuamos como un mero intermediario que facilita la transacción entre Clientes y Vendedores, sin ser parte de la misma.\r\n\r\n2.	DERECHOS DE PROPIEDAD INTELECTUAL\r\nNuestra propiedad intelectual\r\nSomos el propietario o el intelectual de todos los derechos de propiedad intelectual de nuestros Servicios, incluidos todos los códigos fuentes, base de datos, funcionalidades, software, diseños de sitio web, audio, video, texto, fotografías y gráficos en los Servicios (colectivamente los “Contenido”), así como las marcas comerciales, marcas de servicio y logotipos contenidos en el mismo (“Marcas”).\r\nNuestro Contenido y las Marcas están protegidos por leyes de derechos de autor y marcas registradas (y varios otros derechos de propiedad intelectual y leyes de competencia desleal) y tratados alrededor del mundo.\r\n\r\nEl Contenido y las Marcas se proporcionan en o a través de los Servicios “TAL CUAL” para tu uso personal, no comercial o propósito comercial interno solo.\r\n\r\nSu uso de nuestro Servicios\r\n\r\nSujeto a su cumplimiento de estos Términos Legales, incluidas las “ACTIVIDADES PROHIBIDAS” sección a continuación, le otorgamos un derecho de uso exclusivo, intransferible y revocable\r\n•	Acceder a los Servicios; y\r\n•	Descargar o imprimir una copia de cualquier parte del Contenido al que haya obtenido acceso debidamente;\r\nÚnicamente para su uso personal, no comercial o propósito comercial interno.\r\n\r\nSalvo lo establecido en esta sección o en cualquier otra parte de nuestros Términos Legales, ninguna parte de los Servicios ni ningún Contenido o Marca puede copiarse, reproducirse, agregarse, replicarse, cargarse, distribuirse, exhibirse públicamente, codificarse, traducirse, transmitirse, venderse, licenciarse o explotarse de otro modo para ningún propósito comercial, sin nuestro expreso permiso previo por escrito.\r\n\r\nSi deseas hacer cualquier uso de los Servicios, el Contenido o las Marcas distinto al establecido en esta sección o en otra parte de nuestros Términos Legales, dirija su solicitud a: solicitud@nexusbuy.com. Si alguna vez le otorgamos permiso para publicar, reproducir o mostrar públicamente cualquier parte de nuestros Servicios o Contenido, deberá identificarnos como propietarios o licenciantes de los Servicios, Contenidos o Marcas y asegurarse de que cualquier aviso de derecho de autor o propiedad aparezca o sea visible al publicar, reproducir o mostrar nuestro Contenido.\r\n\r\nNos reservamos todos los derechos no expresamente otorgados a usted en, y sobre los Servicios, el Contenido y las Marcas.\r\n\r\nCualquier incumplimiento de estos Derechos de Propiedad Intelectual constituiría un incumplimiento material de nuestros Términos Legales y su derecho a utilizar nuestros Servicios finalizara de inmediato.\r\nSus envíos\r\n\r\nPor favor revise esta sección y la “ACTIVIDADES PROHIBIDAS” Lea atentamente la sección antes de usar nuestros Servicios para comprender (a) los derechos que nos otorga y (b) las obligaciones que tiene cuando publica o carga cualquier contenido a través de los Servicios.\r\n\r\nEnvíos: Enviándonos directamente cualquier pregunta, comentario, sugerencia, idea, opinión u otra información sobre nuestros Servicios (“Envíos”), usted acepta cedernos todos los derechos de propiedad intelectual sobre dicha Presentación. Usted acepta que seremos los propietarios de esta Presentación y tendremos derecho a su uso y difusión sin restricciones para cualquier fin lícito, comercial o de otro tipo, sin necesidad de reconocimiento ni compensación para usted.\r\n\r\nUsted es responsable de lo que publica o carga: Al enviarnos a través de cualquier parte de los Servicios, usted:\r\n\r\n•	Confirma que has leído y estás de acuerdo con nuestra “ACTIVIDADES PROHIBIDAS” y no publicará, enviará, cargará ni transmitirá a través de los Servicios ningún Envío que sea ilegal, acosador, odioso, dañino, difamatorio, obsceno, abusivo, discriminatorio, amenazante para cualquier persona o grupo, sexualmente explícito, falso, inexacto, engañoso o confuso;\r\n•	En la medida en que lo permita la ley aplicable, renunciar a todos y cada uno de los derechos morales sobre dicha Presentación;\r\n•	Garantizar que cualquier Envió de este tipo son originales de usted o que tiene los derechos necesarios y licencias para presentar dichas presentaciones y que tiene plena autoridad para concedernos los derechos mencionados anteriormente en relación con sus Envíos; y\r\n•	Garantizar y declarar que sus Envíos constituyen información confidencial.\r\n\r\nUsted es el único responsable de sus Envíos y usted acepta expresamente reembolsarnos todas y cada una de las pérdidas que podamos sufrir debido a su incumplimiento de (a) esta sección, (b) los derechos de propiedad intelectual de terceros o (c) la ley aplicable.\r\n\r\n\r\n\r\n3.	DECLARACIONES DEL USUARIO\r\n\r\nAl utilizar los Servicios, usted declara y garantiza que: (1) usted tiene la capacidad legal y acepta cumplir con estos Términos Legales; (2) no eres menor de edad en la jurisdicción en la que resides; (3) no accederá a los Servicios a través de medios automatizados o no humanos; (4) no utilizará los Servicios para ningún fin ilegal o no autorizado; (5) su uso de los Servicios no violara ninguna ley o regulación aplicable; (6) si actúa como Vendedor, declara que tiene derecho a vender los productos que publica y que estos no infringen derechos de terceros.\r\n\r\n4.	REGISTRO Y CUENTAS\r\n\r\nPara acceder a ciertas funciones de los Servicios, debe registrarse. Usted se compromete a proporcionar información de registro veraz, exacta y completa. Es usted responsable de la confidencialidad de su contraseña y de todas las actividades que ocurran bajo su cuenta. Los Vendedores deben proporcionar información comercial adicional que podemos solicitarles para verificar su identidad.\r\n\r\n\r\n5.	COMPRAS Y PAGOS (ROL DE CLIENTE)\r\n\r\n5.1.	Proceso de compra: Al realizar un pedido, usted ofrece comprar el producto al Vendedor. La confirmación del pedido no constituye la aceptación por parte del Vendedor, sino la confirmación de la recepción de su oferta. La aceptación se produce cuando el Vendedor envía el producto o confirma el envío.\r\n5.2.	Precios e Impuestos: Los precios los fija el Vendedor. Nos reservamos el derecho de corregir errores en los precios. Los impuestos son responsabilidad del Vendedor.\r\n5.3.	Métodos de Pago: Aceptamos las siguientes formas de pago: Tarjeta de crédito, PayPal y Payer. Usted autoriza a cobrar el importe total a su método de pago seleccionado.\r\n5.4.	Política de Devoluciones: Las devoluciones se regirán por nuestra Política de Devoluciones, disponible en el Sitio, y por la política específica que el vendedor haya establecido para su producto.\r\n\r\n6.	VENTAS Y OBLIGACIONES DEL VENDEDOR\r\n\r\n6.1.	Publicación de Productos: Usted, como Vendedor, garantiza que posee o tiene los derechos necesarios para vender cada producto que publique. Es responsable de la exactitud, veracidad y legalidad de las descripciones, imágenes y precios.\r\n6.2.	Obligaciones con el Cliente: Se compromete a (a) cumplir con los plazos de envío estimados, (b) responder a las consultas de los Clientes de manera oportuna, (c) gestionar las devoluciones y reclamaciones de acuerdo con nuestras políticas, y (d) asumir los costos de devolución si el producto es defectuoso o no se ajusta a la descripción.\r\n6.3.	Tarifas y Comisiones: Nosotros retendremos una comisión del 10 % sobre el precio de venta de cada producto. Los pagos a los Vendedores se realizarán semanal, quincenal o mensual (según el nivel de venta) una vez descontada la comisión.\r\n6.4.	Exclusión de Responsabilidad: La empresa actúa como un mero intermediario. No somos propietarios de los productos, no los almacenamos, no somos responsables de su calidad, seguridad o legalidad, ni de los incumplimientos del Vendedor. Toda la responsabilidad sobre el producto recae en el Vendedor.\r\n\r\n7.	ACTIVIDADES PROHIBIDAS\r\n\r\nNo podrá acceder ni utilizar los Servicios para ningún otro fin que no sea aquel para el que los ponemos a disposición. Los Servicios no podrán utilizarse en relación con ningún propósito comercial, excepto aquellos que estén específicamente respaldados o aprobados por nosotros.\r\n\r\nComo usuario de los Servicios, usted acepta no:\r\n•	Queda prohibido recuperar sistemáticamente datos u otro contenido de los Servicios para crear o compilar, directa o indirectamente, una colección, compilación, base de datos o directorios sin nuestro permiso por escrito.\r\n•	Engañar, defraudar o inducir a error a nosotros o a otros usuarios, especialmente en cualquier intento de obtener información confidencial de la cuenta, como las contraseñas de los usuarios o algún otro dato sensible.\r\n•	Eludir, deshabilitar o interferir de otro modo las funciones relacionadas con la seguridad de los Servicios, incluidas las funciones que impiden o restringen el uso o la copia de cualquier Contenido o imponen limitaciones en el uso de los Servicios y/o el Contenido incluido de ellos.\r\n•	Menospreciar, manchar o de cualquier otra manera dañar, en nuestra opinión a nosotros y/o los Servicios.\r\n•	Utilizar cualquier información obtenida de los Servicios para acosar, abusar o dañar a otra persona.\r\n•	Hacer uso indebido de nuestros servicios de soporte o enviar informes falsos de abuso o mala conducta.\r\n•	Utilizar los Servicios de manera incompatible con las leyes o reglamentaciones aplicables.\r\n•	Participar de forma no autorizada en marcar o vincular a los Servicios.\r\n•	Queda prohibido cargar o transmitir (o intentar cargar o transmitir) virus, troyanos u otro material, incluyendo el uso excesivo de mayúsculas y el envió de spam (publicidad continua de texto repetitivo), que interfiera con el uso y disfrute ininterrumpido de los Servicios por parte de cualquier usuario o que modifique, perjudique, interrumpa, altere o interfiera con el uso, las características, las funciones, el funcionamiento o el mantenimiento de los Servicios.\r\n•	Queda prohibido cualquier uso automatizado del sistema, como el uso de scripts para enviar comentario o mensajes, o el uso de cualquier herramienta de minería de datos, bot o herramientas similares de recopilación y extracción de datos.\r\n•	Elimine el aviso de derechos de autor u otros derechos de propiedad de cualquier contenido.\r\n•	Intentar suplantar la identidad de otro usuario o persona o utilizar el nombre de usuario de otro usuario.\r\n•	Cargar o transmitir (o intentar cargar o transmitir) cualquier material que actúe como mecanismo pasivo o activo de recopilación o transmisión de información, incluyendo, entre otros, formatos de intercambio de gráficos claros (GIM).”gifs”), píxeles de 1x1, balizas web, cookies u otros dispositivos similares (a veces denominados “software espía” o “mecanismos de recopilación pasiva” o “PCM”).\r\n•	Interferir, interrumpir o sobrecargar indebidamente los Servicios o las redes o servicios conectados a los Servicios.\r\n•	Acosar, molestar, intimidar o amenazar a cualquiera de nuestros empleados o agentes que participen en la prestación de cualquier parte de los Servicios.\r\n•	Intentar eludir cualquier medida de los Servicios diseñada para impedir o restringir el acceso a los Servicios, o cualquier parte de los mismos.\r\n•	Copiar o adaptar el software de los Servicios, incluyendo, entre otros, Flash, PHP, HTML, JavaScript u otro código.\r\n•	Excepto en los casos permitidos por la ley aplicable, queda prohibido descifrar, descompilar, desensamblar o realizar ingeniería inversa de cualquier software que forme parte de los Servicios o que de alguna manera los constituya.\r\n•	Salvo en el caso del uso de un motor de búsqueda o navegador de Internet, queda prohibido usar, ejecutar, desarrollar o distribuir cualquier sistema automatizado, incluyendo, entre otros, arañas web, bot, programas para hacer trampa, raspadores o lectores sin conexión que accedan a los Servicios, o usar o ejecutar cualquier script no autorizado u otro software.\r\n•	Utilizar un agente de compras para realizar compras en los Servicios.\r\n•	El uso indebido de los Servicios, incluyendo la recopilación de nombres de usuarios y/o direcciones de correo electrónicos o de otro tipo con el fin de enviar correo electrónico no solicitado, o la creación de cuentas de usuario por medios automatizados o mediante información falsa.\r\n\r\n8.	CONTRIBUCIONES GENERADAS POR EL USUARIO\r\n\r\n8.1.	Definición: “Contribuciones” incluyen cualquier contenido que usted publique en los Servicios: como Vendedor (descripciones de productos, imágenes, etc.) o como Cliente (comentarios, valoraciones, etc.).\r\n8.2.	Licencia que Usted Nos Otorga: Al publicar Contribuciones, nos otorga una licencia no exclusiva, mundial, libre de regalías y sublicenciable para usar, modificar, reproducir y mostrar dichas Contribuciones con el fin de operar, promocionar y mejorar los Servicios.\r\n8.3.	Sus Garantías: Usted garantiza que posee o tiene los derechos sobre sus Contribuciones y que estas (a) son veraces y exactas (especialmente las descripciones de los productos), (b) no infringen derechos de terceros, y (c) no son contrarias a lo establecido en la sección “ACTIVIDADES PROHIBIDAS”.\r\n8.4.	Exención de Responsabilidad: No somos responsables de las contribuciones de los usuarios. Sin embargo, nos reservamos el derecho de eliminar cualquier Contribución que, a nuestro juicio, viole estos Términos.\r\n\r\n9.	GESTIÓN DE SERVICIOS\r\n\r\nNos reservamos el derecho, pero no la obligación de:\r\n1.	Supervisar los Servicios para detectar infracciones de estos Términos Legales;\r\n2.	Emprender las acciones legales pertinentes contra cualquier persona que, infrinja la ley o estos Términos Legales, incluyendo, entre otras, la denuncia de dicho usuario ante las autoridades competentes;\r\n3.	A nuestra discreción y sin limitación alguna, rechazar, restringir el acceso a, limitar la disponibilidad de, o inhabilitar (en la medida en que sea tecnológicamente posible) cualquiera de sus Contribuciones o cualquier parte de las mismas;\r\n4.	A nuestra entera discreción y sin limitación alguna, notificación o responsabilidad, eliminar de los Servicio o inhabilitar de cualquier otro método todos los archivos y contenidos que sean de tamaño excesivo o que resulten gravosos para nuestro sistema;\r\n5.	Gestionar los Servicios de cualquier otra forma que proteja nuestros derechos y propiedad y facilite el correcto funcionamiento de los mismos.\r\n\r\n10.	PLAZO Y TERMINACIÓN\r\n\r\nEstos Términos Legales permanecerán en pleno vigor y efecto mientras utilice los Servicios. SIN PREJUICIO DE CUALQUIER OTRA DISPOCICIÓN DE ESTOS TÉRMINOS LEGALES, NOS RESERVAMOS EL DERECHO, A NUESTRA EXCLUSIVA DISCREPCIÓN Y SIN PREVIO AVISO NI RESPONSABILIDAD ALGUNA, DE NEGAR EL ACCESO Y EL USO DE LOS SERVICIOS (INCLUYENDO EL BLOQUEO DE CIERTAS DIRECCIONES IP) A CUALQUIER PERSONA, POR CUALQUIER MOTIVO O SIN MOTIVO ALGUNO, INCLUYENDO ENTRE OTROS, EL INCUMPLIMIENTO DE CUALQUIER DECLARACIÓN, GARANTÍA O CONVENIO EN ESTOS TÉRMINOS LEGALES O DE CUALQUIER LEY O REGLAMENTO APLICABLE. PODEMOS SUSPENDER SU USO O PARTICIPACIÓN EN LOS SERVICIOS O ELIMINAR. Nos reservamos el derecho de utilizar cualquier contenido o información que usted haya publicado en cualquier momento, sin previo aviso.\r\nSi cancelamos o suspendemos  su cuenta por cualquier motivo, le queda prohibido registrarse y crear una nueva cuenta a su nombre, con un nombre falso o prestado, o con el nombre de un tercero, incluso si actúa en nombre de dicho tercero. Además de la cancelación o suspensión de su cuenta, nos reservamos el derecho de emprender las acciones legales pertinentes, incluyendo, entre otras, acciones civiles, penales y cautelares.\r\n\r\n11.	MODIFICACIONES E INTERRUPCIONES\r\n\r\nNos reservamos el derecho de cambiar, modificar o eliminar el contenido de los Servicios en cualquier momento y por cualquier motivo, a nuestra  entera discreción y sin previo aviso. Sin embargo, no tenemos la obligación de actualizar la información de nuestros Servicios. Así mismo, nos reservamos el derecho a modificar o interrumpir la totalidad o parte de los Servicios sin previo aviso en cualquier momento. No seremos responsables ante usted ni ante terceros por ninguna modificación, cambio de precio, suspensión o interrupción de los Servicios.\r\n\r\nNo podemos garantizar la disponibilidad permanente de los Servicios. Podríamos experimentar problemas de hardware, software u otros, o necesitar realizar mantenimiento relacionado con los Servicios, lo que ocasionaría interrupciones, demoras o errores. Nos reservamos el derecho de cambiar, revisar, actualizar, suspender, discontinuar, modificar los Servicios en cualquier momento y por cualquier motivo, sin previo aviso. Usted acepta que no seremos responsables de ninguna pérdida, daño o inconveniente causado por su imposibilidad de acceder a los Servicios o utilizarlos durante cualquier periodo de inactividad o discontinuación de los mismos. Ninguna disposición de estos Términos Legales nos obliga a mantener y brindar soporte para los Servicios ni proporcionar correcciones, actualizaciones o nuevas versiones relacionadas con ellos.\r\n\r\n12.	LEY APLICABLE\r\n\r\nEstos Términos Legales se regirán e interpretaran de conformidad con las leyes de Cuba y usted acepta someterse a la jurisdicción no exclusiva de los tribunales de Cuba para la resolución de cualquier disputa.\r\n\r\n13.	RESOLUCIÓN DE DISPUTAS\r\n\r\nNegociaciones informales:\r\nPara agilizar la resolución y controlar el coste de cualquier disputa, controversia o reclamación relacionada con estos Términos Legales (cada uno de ellos) “Disputa” y en conjunto, las “Disputas”) presentado por usted o por nosotros (individualmente, un “Partido” y,  colectivamente, los “Partidos”), las Partes acuerdan intentar primero negociar cualquier Controversia (excepto aquellas expresamente previstas a continuación) de manera informal durante al menos 30 días antes de iniciar el arbitraje. Dichas negociaciones informales comienzan tras la notificación escrita de una Parte a la otra.\r\nArbitraje vinculante:\r\nCualquier controversia que surja de o en relación con estos Términos Legales, incluyendo cualquier cuestión relativa a su existencia, validez o terminación se someterá a arbitraje y será resuelta definitivamente por el Tribunal.\r\nRestricciones:\r\nLas Partes acuerdan que cualquier arbitraje se limitará a la controversia entre las Partes individualmente. En la máxima medida permitida por la ley, (a) ningún arbitraje se acumulará con ningún otro procedimiento; (b) no existe derecho ni facultad para que ninguna controversia se someta a arbitraje mediante una demanda colectiva ni para utilizar procedimientos de acción colectiva; y (c) no existe derecho ni autoridad para que se presente ninguna disputa  en una supuesta capacidad representativa en nombre del público en general o de cualquier otra persona.\r\nExcepciones a las negociaciones informales y al arbitraje:\r\nLa Partes acuerdan que las siguientes controversias no están sujetas a las disposiciones anteriores relativas a las negociaciones informales y al arbitraje vinculante: (a) cualquier controversia que tenga por objeto hacer valer o proteger, o que se refiera a la validez de, cualquiera de los derechos de propiedad intelectual de una Parte; (b) cualquier controversia relacionada con, o que surja de,  alegaciones de robo, invasión de privacidad, como uso no autorizado; y (c) cualquier reclamación de medidas cautelares. Si se determina que esta disposición es ilegal o inaplicable, ninguna de las Partes optará por el arbitraje para resolver cualquier controversia comprendida  en la parte de esta disposición que se considera ilegal o inaplicable, y dicha controversia será resuelta por un Tribunal competente dentro de la jurisdicción de los tribunales mencionados anteriormente, y las Partes acuerdan someterse a la jurisdicción personal de dicho tribunal.\r\n\r\n14.	CORRECCIONES\r\n\r\nEs posible que la información de los Servicios contenga errores tipográficos, inexactitudes u omisiones, incluyendo descripciones, precios, disponibilidad y otra información diversa. Nos reservamos el derecho de corregir cualquier error, inexactitud u omisión y de cambiar o actualizar la información de los Servicios en cualquier momento, sin previo aviso.\r\n\r\n15.	DESCARGO DE RESPONSABILIDAD\r\n\r\nLos Servicios se propician tal cual y según disponibilidad. Usted acepta que el uso de los Servicios será bajo su exclusiva responsabilidad. En la máxima medida permitida por la ley, renunciamos a todas las garantías, expresas o implícitas, en relación con los Servicios y su uso, incluidas, entre otras, las garantías implícitas de comerciabilidad, idoneidad para un propósito particular y no infracción. NO OFRECEMOS GARANTÍAS NI DECLARACIONES SOBRE LA EXACTITUD O INTEGRIDAD DEL CONTENIDO DE LOS SERVICIOS NI DEL CONTENIDO DE NINGÚN SITIO WEB O APLICACIÓN MÓVIL VINCULADA A LOS SERVICIOS, Y NO ASUMIMOS NINGUNA RESPONSABILIDAD POR (1) ERRORES, EQUIVOCACIONES O INEXACTITUDES EN EL CONTENIDO Y LOS MATERIALES, (2) LESIONES PERSONALES O DAÑOS A LA PROPIEDAD, DE CUALQUIER NATURALEZA, QUE RESULTEN DE SU ACCESO Y USO DE LOS SEVICIOS, (3) CUALQUIER OTRO CASO NO AUTORIZADO. (4) El acceso o uso de nuestros servidores seguros y/o cualquier información personal y/o financiera almacenada en ellos, (5) cualquier interrupción o cese de la transmisión hacia o desde los servicios, (6) cualquier error, virus, troyano o similar que pueda transmitirse hacia o a través de los servicios por cualquier tercero, y/o (7) cualquier error u omisión en cualquier contenido y material o por cualquier pérdida o daño de cualquier tipo incurrido como resultado del uso de cualquier contenido publicado, transmitido o puesto a disposición de otro modo a través de los servicios.\r\nNo garantizamos, avalamos ni asumimos responsabilidades por ningún producto o servicio anunciado u ofrecido por terceros a través de los Servicios, cualquier sitio web vinculado o cualquier sitio web o aplicación móvil que aparezca en banners u otros anuncios. Tampoco participamos ni seremos responsables de supervisar ninguna transacción entre usted y terceros proveedores de productos o servicios. Al igual que con la compra de cualquier producto o servicio a través de cualquier medio o en cualquier entorno, debe usar su mejor criterio. EXTREME LA PRECAUCIÓN CUANDO CORRESPONDA.\r\n\r\n16.	LIMITACIONES DE RESPONSABILIDAD\r\n\r\nEN NINGÚN CASO NOSOTROS, NI NUSTROS DIRECTORES, EMPLEADOS O AGENTES SEREMOS RESPONSABLES ANTE USTED NI ANTE NINGÚN TERCERO POR DAÑOS DIRECTOS, INDIRECTOS, CONSECUENCIALES, EJEMPLARES, INCIDENTALES, ESPECIALES O PUNITIVOS, INCLUIDA LA PÉRDIDA DE BENEFICIOS, LA PÉRDIDA DE INGRESOS, LA PÉRDIDA DE DATOS U OTROS DAÑOS DERIVADOS DEL USO DE LOS SERVICIOS, INCLUSO SI SE NOS HA INFORMADO DE LA POSIBILIDAD DE DICHOS DAÑOS. Sin perjuicio de cualquier disposición en contrario contenida en el presente documento, nuestra responsabilidad ante usted por cualquier causa, cualquiera que sea, e independientemente de la forma de la acción, se limitara en todo momento.\r\n\r\n17.	INDEMIZACIÓN\r\nUsted acepta defendernos, indemnizarnos y mantenernos indemnes, incluyendo a nuestras subsidiarias, afiliadas y a todos nuestros respectivos funcionarios, agentes, socios y empleado, frente a cualquier pérdida, daño, responsabilidad, reclamación o demanda, incluyendo honorarios y gastos razonables de abogados, presentada por cualquier tercero debido a o derivada de: (1) El uso de los Servicios; (2) el incumplimiento de estos Términos Legales; (3) cualquier incumplimiento de las declaraciones y garantías que usted haya otorgado en estos Términos Legales; (4) la violación de los derechos de terceros incluidos, entre otros, los derechos de propiedad intelectual; o (5) cualquier acto perjudicial manifiesto contra cualquier otro usuario de los Servicios con quien usted se haya conectado a través de los mismos. Sin prejuicio de lo anterior, nos reservamos el derecho, a su cargo, de asumir la responsabilidad exclusiva, defensa y el control de cualquier asunto por el cual usted esté obligado a indemnizarnos, y usted acepta cooperar, a su cargo, con nuestras defensas de dichas reclamaciones. Nos esforzaremos razonablemente por notificarle cualquier reclamación, acción o procedimiento sujeto a esta indemnización de cuanto tengamos conocimiento de ello.\r\n\r\n18.	DATOS DEL USUARIO\r\n\r\nConservamos ciertos datos que usted transmita a los Servicios con el fin de gestionar su funcionamiento, así como datos relativos de uso. Si bien realizamos copias de seguridad periódicas de los datos, usted es el único responsable de todos los datos que transmita o que estén relacionados con cualquier actividad que haya realizado utilizando los Servicios. Usted acepta que no seremos responsables ante usted por ninguna pérdida o corrupción de dichos datos y, por la presente, renuncia a cualquier derecho de acción contra nosotros derivado de dicha pérdida o corrupción.\r\n\r\n19.	COMUNICACIONES ELECTRÓNICAS\r\n\r\nAl acceder a los Servicios, enviaremos correos electrónicos y completar formularios en línea, usted acepta recibir comunicaciones electrónicas. Usted consiente recibir comunicaciones electrónicas y reconoce que todos los acuerdos, avisos, divulgaciones y demás comunicaciones que le proporcionemos electrónicamente, por correo electrónico y a través de los Servicios, cumplen con cualquier requisito legal de que dicha comunicación conste por escrito. Por la presente, usted acepta el uso de firmas, contratos, pedidos y demás registros electrónicos, así como la entrega electrónica de avisos, políticas y registros de transacciones, iniciadas o completadas por nosotros o a través de los Servicios. Asimismo, renuncia a cualquier derecho o requisito en cualquier ley, reglamento, norma, ordenanza u otra disposición legal de cualquier jurisdicción que exija una firma original, la entrega o conservación de registros no electrónicos, o pagos o la concesión de créditos por cualquier medio que no sea electrónico.\r\n\r\n20.	MISCELÁNIAS\r\n\r\nEstos Términos Legales y cualquier política o norma operativa que publiquemos en los Servicios o en relación con ellos constituyen el acuerdo completo entre usted y nosotros. El hecho de que no ejerzamos o hagamos valer algún derecho o disposición de estos Términos Legales no se considerará una renuncia a dicho derecho o disposición. Estos Términos Legales se aplicarán en la máxima medida permitida por la ley. Podemos ceder cualquiera o todos nuestros derechos y obligaciones a terceros en cualquier momento. No seremos responsables de ninguna pérdida, daño, demora o incumplimiento causado por cualquier motivo ajeno a nuestro control razonable. Si alguna disposición o parte de una disposición de estos Términos Legales se considera ilegal, nula o inaplicable, dicha disposición o parte de la disposición se considerará separable de estos Términos Legales y no afectará la validez de las demás disposiciones. Estos Términos Legales o el uso de los Servicios no crearan ninguna relación de empresa conjunta, sociedad, empleo o agencia entre usted y nosotros. Usted acepta que estos Términos Legales no se interpretarán en nuestra contra por el mero hecho de haberlos redactado. Por la presente, usted renuncia a cualquier derecho o reclamación contra nosotros. Es posible que usted haya actuado en base a la forma electrónica de estos Términos Legales y a la falta de firma de las partes para ejecutar estos Términos Legales.\r\n\r\n21.	CONTÁCTENOS\r\n\r\nPara resolver una queja relacionada con los Servicios o para obtener más información, póngase en contacto con nosotros en: soporte@nexusbuy.com\r\n', 'string', 'legal', 'Términos y condiciones', 1, '2025-11-14 14:54:30', '2025-11-14 14:54:30'),
(12, 'tiempo_sesion_minutos', '120', 'number', 'seguridad', 'Tiempo de expiración de sesión', 1, '2025-11-14 14:54:30', '2025-11-14 14:54:30'),
(13, 'max_intentos_login', '5', 'number', 'seguridad', 'Máximo de intentos de login fallidos', 1, '2025-11-14 14:54:30', '2025-11-14 14:54:30'),
(14, 'transfermovil_activo', '1', 'boolean', 'pagos', 'Activar Transfermóvil como método de pago', 1, '2026-01-07 19:11:57', '2026-01-07 19:11:57'),
(15, 'transfermovil_numero_tarjeta', '9238959871235406', 'string', 'pagos', 'Número de tarjeta (16 dígitos) para recibir pagos por Transfermóvil', 1, '2026-01-07 19:11:57', '2026-01-07 19:15:22'),
(16, 'transfermovil_nombre_titular', 'NexusBuy', 'string', 'pagos', 'Nombre del titular de la tarjeta de Transfermóvil', 1, '2026-01-07 19:11:57', '2026-01-09 03:29:26'),
(17, 'transfermovil_banco', 'Metropolitano', 'string', 'pagos', 'Banco emisor de la tarjeta Transfermóvil', 1, '2026-01-07 19:11:57', '2026-01-07 19:16:15');
INSERT INTO `configuracion_sitio` (`id`, `clave`, `valor`, `tipo`, `categoria`, `descripcion`, `editable`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(18, 'transfermovil_tipo_cuenta', 'ahorros', 'string', 'pagos', 'Tipo de cuenta: ahorros o corriente', 1, '2026-01-07 19:11:57', '2026-01-07 19:11:57'),
(19, 'transfermovil_prefijo_referencia', 'NEXUS-001', 'string', 'pagos', 'Prefijo para las referencias de pago (ej: NEXUS-001)', 1, '2026-01-07 19:11:57', '2026-01-07 19:16:39'),
(20, 'transfermovil_tiempo_espera_horas', '1', 'number', 'pagos', 'Horas máximas para verificar un pago pendiente', 1, '2026-01-07 19:11:57', '2026-01-07 19:17:04'),
(21, 'devolucion_reembolso', 'Texto de polítida de Devolución y Reembolso', 'string', 'legal', 'Polítida de Devolución y Reembolso', 1, '2026-01-09 03:38:43', '2026-01-09 03:38:43'),
(22, 'descargo_responsabilidad', 'Texto de política de descargo de responsabilidad', 'string', 'legal', 'Política de Descargo de Responsabilidad', 1, '2026-01-09 03:38:43', '2026-01-09 03:38:43'),
(23, 'envios_entregas', '1.	Introducción\r\nEsta Política de Envíos y Entregas se describe cómo se gestionan el procesamiento, la entrega y los costes asociados a los pedidos realizados en NexusBuy. Es fundamental entender que NexusBuy actúa como una plataforma que conecta a Compradores y Vendedores independientes. Por lo tanto, la logística de envío es responsabilidad de cada Vendedor. Al realizar una compra, usted acepta los términos de esta política.\r\n\r\n2.	Procesamiento de Pedidos y Preparación\r\n\r\n•	Tiempo de Procesamiento: El tiempo de procesamiento es el tiempo que tarda el Vendedor en preparar el pedido para su envío. Este tiempo varía según cada Vendedor y se indica claramente en la página de cada producto. El tiempo de procesamiento típico es de 1 a 3 días hábiles desde la confirmación del pago, pero puede ser mayor para productos artesanales o hechos bajo pedido.\r\n•	Pedidos los fines de semana y festivos: Los pedidos realizados los sábados, domingo o días festivos comenzaran a procesarse el siguiente día hábil.\r\n•	Confirmación: Recibirá un correo electrónico de confirmación una vez que se pedido haya sido confirmado y pasado a nuestro Vendedor para su preparación.\r\n\r\n3.	Opciones y Costos de Envío\r\n\r\n•	Método Disponibles: Los métodos de envío disponibles (estándar, urgente, económico, etc.) los determina cada Vendedor y se muestra durante el proceso de checkout. NexusBuy no controla directamente las compañías de transporte.\r\n•	Cálculo de Costes: Los costes de envío se calculan en función de:\r\no	El peso y las dimensiones del paquete.\r\no	La dirección de entrega.\r\no	El método de envío seleccionado.\r\no	La política del Vendedor (algunos ofrecen envío gratuito a partir de un importe mínimo de compra).\r\n•	Envío Gratuito: Algunos Vendedores pueden ofrecer envío gratuito. Los términos de esta promoción (como el importe mínimo de compra o las regiones aplicables) se indicarán claramente en la página del producto y/o durante el checkout.\r\n\r\n4.	Tiempos de Entrega Estimados\r\n\r\n•	Estimaciones, No Garantías: Los plazos de entrega mostrados son estimaciones proporcionadas por los Vendedores y las compañías de transporte. No son plazos garantizados. NexusBuy no se hace responsable por los retrasos causados por el transportista o por circunstancias ajenas a nuestro control (condiciones climáticas, aduanas, etc.).\r\n•	Cálculo: El tiempo de entrega total = Tiempo de Procesamiento del Vendedor + Tiempo de Tránsito del Transportista.\r\n•	Ejemplos de Estimaciones (Península): \r\no	Envío Estándar: 3 – 7 días hábiles.\r\no	Envío Urgente (24 / 48h): 1 – 2 días hábiles.\r\n•	Envíos Internacionales: Los plazos para envíos internacionales (fuera de Cuba) pueden variar significativamente de 7 a 25 días hábiles, dependiendo del país de destino y los trámites aduaneros.\r\n\r\n5.	Seguimiento de su Pedido\r\nUna vez que su pedido sea enviado por el Vendedor, recibirá un correo electrónico de notificación con un número de seguimiento y una actualización del estado de su pedido en la sección “Mis Pedidos”. Si no recibe esta información después de que haya pasado el tiempo de procesamiento, puede:\r\n1.	Revisar la sección “Mis Pedidos” en su cuenta de NexusBuy.\r\n2.	Contactar directamente al Vendedor a través del sistema de mensaje de NexusBuy.\r\n3.	Contactar con nuestro servicio de atención al cliente si el Vendedor no responde.\r\n\r\n6.	Direcciones de Entrega y Problemas Comunes\r\n\r\n•	Exactitud de la Dirección: Usted es responsable de propiciar una dirección de entrega completa y correcta. NexusBuy y nuestro Vendedores no nos hacemos responsables por los paquetes entregados en una dirección incorrecta o incompleta proporcionada por usted.\r\n•	Intentos de Entrega: La compañía de transporte usualmente realiza uno a tres intentos de entrega. Si no hay nadie en la dirección, dejara un aviso de paso. Es su responsabilidad seguir las instrucciones del aviso para reprogramar la entrega o recoger el paquete en la oficina postal más cercana.\r\n•	Paquetes Perdidos o Robados: Una vez que el paquete sea marcado como “entregado” por la compañía de transporte, NexusBuy y el Vendedor no son responsables por paquetes perdidos o robados. Le recomendamos que se ponga en contacto directamente con la compañía de transporte y con su comunidad de vecinos para localizarlo. En caso de robo, debe presentar una denuncia ante la policía.\r\n\r\n7.	Envíos Internacionales y Aduanas\r\n\r\n•	El Comprador es el Importador: Para envíos fuera de Cuba, el comprador es considerado el “importador de registro” y es responsable de todos los impuestos, aranceles y tasas aduaneras asociadas con la importación del producto en su país.\r\n•	Fondos por Aduanas No Incluidos: El precio de compra y los gastos de envío no incluyen estos impuestos. Las autoridades aduaneras de su país pueden retener el paquete hasta que se realice el pago. NexusBuy y nuestros Vendedores no tenemos control sobre esos cargos y no podemos predecir su importe.\r\n\r\n8.	Política del Vendedor\r\nCada Vendedor en NexusBuy establece su propia política de envíos dentro del marco de esta política general. Es su responsabilidad, como Comprador, revisar la información de envío proporcionada por el Vendedor en la página del producto antes de realizar la compra.\r\n\r\n9.	Contacto\r\nSi tiene alguna pregunta sobre el envío de su pedido, le recomendamos que se ponga en contacto primero con el Vendedor a través del sistema de mensaje de NexusBuy.\r\nSi no recibe respuesta o necesita ayuda adicional, puede contactar con nuestro equipo de soporte:\r\nEmail de Soporte: envios@nexusbuy.com\r\nAsunto: Consulta sobre Envío – [Número de Pedido]\r\n', 'string', 'legal', 'Política de Entregas y Envios', 1, '2026-01-09 03:41:57', '2026-01-14 23:58:06'),
(24, 'cookies', 'Texto de política de cookies', 'string', 'legal', 'Política de Cookies', 1, '2026-01-09 03:41:57', '2026-01-09 03:41:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contacto_mensajes`
--

CREATE TABLE `contacto_mensajes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `asunto` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('pendiente','leido','respondido','archivado') COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente',
  `id_usuario` int(11) DEFAULT NULL COMMENT 'Usuario registrado que envió el mensaje',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `contacto_mensajes`
--

INSERT INTO `contacto_mensajes` (`id`, `nombre`, `email`, `telefono`, `asunto`, `mensaje`, `estado`, `id_usuario`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(8, 'Noel Chacón', 'noeldavidchaconsanchez@gmail.com', NULL, 'Problemas técnicos', 'Comprobando el cierre del modal', 'pendiente', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 05:48:43', '2026-01-07 05:48:43'),
(13, 'Noel Chacón', 'noeldavidchaconsanchez@gmail.com', NULL, 'Problemas técnicos', '&lt;?php\r\n// ContactoController.php - Versión corregida\r\n\r\n// =============================================\r\n// CONFIGURACIÓN INICIAL\r\n// =============================================\r\nob_start();\r\nini_set(&#039;display_errors&#039;, 0);\r\nini_set(&#039;log_errors&#039;, 1);\r\ndate_default_timezone_set(&#039;America/Havana&#039;);\r\n\r\n// =============================================\r\n// FUNCIÓN PARA ENVIAR RESPUESTA JSON\r\n// =============================================\r\nfunction enviarJson($data, $statusCode = 200) {\r\n    while (ob_get_level() &gt; 0) {\r\n        ob_end_clean();\r\n    }\r\n    \r\n    http_response_code($statusCode);\r\n    header(&#039;Content-Type: application/json; charset=UTF-8&#039;);\r\n    echo json_encode($data, JSON_UNESCAPED_UNICODE);\r\n    exit;\r\n}\r\n\r\n// =============================================\r\n// VALIDACIÓN DE MÉTODO HTTP\r\n// =============================================\r\nif ($_SERVER[&#039;REQUEST_METHOD&#039;] !== &#039;POST&#039;) {\r\n    enviarJson([\r\n        &#039;status&#039; =&gt; &#039;error&#039;,\r\n        &#039;message&#039; =&gt; &#039;Método no permitido. Use POST.&#039;\r\n    ], 405);\r\n}\r\n\r\ntry {\r\n    // =============================================\r\n    // 1. SANITIZAR Y VALIDAR DATOS\r\n    // =============================================\r\n    \r\n    // Función helper para sanitizar\r\n    function sanitizar($input) {\r\n        return trim(htmlspecialchars($input ?? &#039;&#039;, ENT_QUOTES, &#039;UTF-8&#039;));\r\n    }\r\n    \r\n    // Obtener datos\r\n    $nombre = sanitizar($_POST[&#039;nombre&#039;] ?? &#039;&#039;);\r\n    $email = isset($_POST[&#039;email&#039;]) ? trim(filter_var($_POST[&#039;email&#039;], FILTER_SANITIZE_EMAIL)) : &#039;&#039;;\r\n    $telefono = sanitizar($_POST[&#039;telefono&#039;] ?? &#039;&#039;);\r\n    $asunto = sanitizar($_POST[&#039;asunto&#039;] ?? &#039;&#039;);\r\n    $mensaje = sanitizar($_POST[&#039;mensaje&#039;] ?? &#039;&#039;);\r\n    \r\n    // Para checkbox: si no está presente en POST, es false\r\n    $aceptaPrivacidad = isset($_POST[&#039;privacidad&#039;]) &amp;&amp; $_POST[&#039;privacidad&#039;] === &#039;on&#039;;\r\n    \r\n    // =============================================\r\n    // 2. VALIDACIONES\r\n    // =============================================\r\n    $errores = [];\r\n    \r\n    // Validar nombre\r\n    if (empty($nombre) || strlen($nombre) &lt;', 'pendiente', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 06:28:57', '2026-01-07 06:28:57'),
(14, 'Noel Chacón', 'noeldavidchaconsanchez@gmail.com', NULL, 'Problemas técnicos', 'Realizando pruebas en el controlador', 'pendiente', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 06:50:21', '2026-01-07 06:50:21'),
(15, 'Noel Chacón', 'noeldavidchaconsanchez@gmail.com', NULL, 'Problemas técnicos', 'asasasasasasasasasasasasasasasas', 'pendiente', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:13:57', '2026-01-07 07:13:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cupon`
--

CREATE TABLE `cupon` (
  `id` int(11) NOT NULL,
  `codigo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_descuento` enum('porcentaje','monto_fijo','envio_gratis') COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` decimal(10,2) NOT NULL CHECK (`valor` >= 0),
  `minimo_compra` decimal(10,2) DEFAULT 0.00 CHECK (`minimo_compra` >= 0),
  `maximo_descuento` decimal(10,2) DEFAULT NULL,
  `usos_maximos` int(11) DEFAULT NULL,
  `usos_actuales` int(11) DEFAULT 0,
  `usos_por_usuario` int(11) DEFAULT 1,
  `fecha_inicio` datetime NOT NULL,
  `fecha_expiracion` datetime NOT NULL,
  `aplicable_todo` tinyint(1) DEFAULT 1,
  `estado` enum('activo','inactivo','expirado') COLLATE utf8mb4_unicode_ci DEFAULT 'activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cupon`
--

INSERT INTO `cupon` (`id`, `codigo`, `descripcion`, `tipo_descuento`, `valor`, `minimo_compra`, `maximo_descuento`, `usos_maximos`, `usos_actuales`, `usos_por_usuario`, `fecha_inicio`, `fecha_expiracion`, `aplicable_todo`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'TECH10', '10% de descuento en tecnología', 'porcentaje', '10.00', '100.00', '50.00', 100, 5, 1, '2025-11-01 00:00:00', '2025-12-31 23:59:59', 1, 'activo', '2025-11-17 02:31:27', '2025-11-17 02:31:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cupon_producto`
--

CREATE TABLE `cupon_producto` (
  `id` int(11) NOT NULL,
  `id_cupon` int(11) NOT NULL,
  `id_producto_tienda` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cupon_producto`
--

INSERT INTO `cupon_producto` (`id`, `id_cupon`, `id_producto_tienda`, `fecha_creacion`) VALUES
(1, 1, 1, '2025-11-17 02:31:46'),
(2, 1, 2, '2025-11-17 02:31:46'),
(3, 1, 5, '2025-11-17 02:31:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cupon_usado`
--

CREATE TABLE `cupon_usado` (
  `id` int(11) NOT NULL,
  `id_cupon` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_orden` int(11) NOT NULL,
  `descuento_aplicado` decimal(10,2) NOT NULL,
  `fecha_uso` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `favorito`
--

CREATE TABLE `favorito` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_producto_tienda` int(11) NOT NULL,
  `fecha_agregado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `favorito`
--

INSERT INTO `favorito` (`id`, `id_usuario`, `id_producto_tienda`, `fecha_agregado`) VALUES
(52, 2, 7, '2025-12-17 19:27:04'),
(53, 2, 43, '2025-12-25 02:59:06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial`
--

CREATE TABLE `historial` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `accion` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modulo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `datos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos`)),
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_tipo_historial` int(11) DEFAULT 9,
  `id_modulo` int(11) DEFAULT 8
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `historial`
--

INSERT INTO `historial` (`id`, `id_usuario`, `accion`, `modulo`, `descripcion`, `ip_address`, `user_agent`, `datos`, `fecha_creacion`, `id_tipo_historial`, `id_modulo`) VALUES
(1, 2, 'editar_perfil', 'Perfil', 'Editó su perfil personal', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '{\"campos\": [\"nombres\", \"avatar\"]}', '2025-12-14 17:02:26', 2, 2),
(2, 2, 'crear_direccion', 'Direcciones', 'Agregó una nueva dirección de envío', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '{\"municipio\": \"Centro Habana\", \"provincia\": \"La Habana\"}', '2025-12-14 15:02:26', 1, 3),
(3, 2, 'crear_pedido', 'Pedidos', 'Realizó una compra - Orden #ORD-001', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '{\"orden\": \"ORD-001\", \"total\": 273.00}', '2025-12-14 13:02:26', 6, 4),
(4, 2, 'cambiar_password', 'Perfil', 'Cambió su contraseña de acceso', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '{}', '2025-12-13 19:02:26', 13, 2),
(5, 2, 'crear_resena', 'Reseñas', 'Escribió una reseña para Nike Air Force 1', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '{\"producto\": \"Nike Air Force 1\", \"calificacion\": 5}', '2025-12-13 19:02:26', 7, 5),
(6, 2, 'agregar_favorito', 'Favoritos', 'Agregó producto a favoritos', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '{\"producto\": \"Samsung Galaxy S21 Ultra\"}', '2025-12-13 19:02:26', 1, 7),
(7, 2, 'login', 'Autenticación', 'Inició sesión en el sistema', '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '{\"ip\": \"192.168.1.102\"}', '2025-12-12 19:02:26', 4, 9),
(8, 2, 'registro', 'Usuario', 'Completó su registro de usuario', '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '{\"email\": \"adsdfg@gmail.com\"}', '2025-12-12 19:02:26', 10, 1),
(9, 2, 'verificar_email', 'Usuario', 'Verificó su dirección de email', '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '{}', '2025-12-12 19:02:26', 14, 1),
(10, 2, 'buscar_productos', 'Productos', 'Exploró productos en la categoría Tecnología', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '{\"categoria\": \"Tecnología\"}', '2025-12-11 19:02:26', 9, 11),
(11, 2, 'agregar_carrito', 'Carrito', 'Agregó producto al carrito', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '{\"producto\": \"Adidas Ultraboost 22\", \"cantidad\": 1}', '2025-12-11 19:02:26', 1, 6),
(12, 2, 'actualizar_configuracion', 'Configuración', 'Actualizó sus preferencias de notificación', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '{\"notificaciones_email\": true}', '2025-12-11 19:02:26', 2, 10),
(13, 2, 'cambiar_password', '', 'Ha cambiado su password', NULL, NULL, NULL, '2025-12-17 05:02:23', 13, 12),
(14, 2, 'editar_perfil', '', 'Editó sus datos personales: Juan Miguel (DNI: 12345678901)', NULL, NULL, NULL, '2025-12-17 05:12:02', 2, 2),
(15, 2, 'editar_perfil', '', 'Editó sus datos personales: Juan Torres (DNI: 12345678901)', NULL, NULL, NULL, '2025-12-17 05:15:37', 2, 2),
(16, 2, 'cambiar_password', '', 'Ha cambiado su password', NULL, NULL, NULL, '2025-12-17 05:23:24', 13, 12),
(17, 2, 'marcar_principal', '', 'Marcó como dirección principal: Lois', NULL, NULL, NULL, '2025-12-17 05:50:49', 2, 3),
(18, 2, 'eliminar_direccion', '', 'Ha eliminado la Dirección: Lois, Municipio: Centro Habana, Provincia: La Habana', NULL, NULL, NULL, '2025-12-17 05:51:17', 3, 3),
(19, 2, 'crear_direccion', '', 'Ha creado una nueva dirección: Neptuno #616 / Gervacio y Escobar', NULL, NULL, NULL, '2025-12-17 18:25:11', 1, 3),
(20, 2, 'eliminar_direccion', '', 'Ha eliminado la Dirección: Neptuno #616B /Gervacio y Escobar, Municipio: Centro Habana, Provincia: La Habana', NULL, NULL, NULL, '2025-12-17 18:25:25', 3, 3),
(21, 2, 'crear_direccion', '', 'Ha creado una nueva dirección: Calle 26', NULL, NULL, NULL, '2025-12-18 04:21:14', 1, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario_movimiento`
--

CREATE TABLE `inventario_movimiento` (
  `id` int(11) NOT NULL,
  `id_producto_tienda` int(11) NOT NULL,
  `tipo_movimiento` enum('entrada','salida','ajuste','devolucion') COLLATE utf8mb4_unicode_ci NOT NULL,
  `cantidad` int(11) NOT NULL,
  `stock_anterior` int(11) NOT NULL,
  `stock_nuevo` int(11) NOT NULL,
  `motivo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `referencia` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_usuario` int(11) NOT NULL,
  `notas` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_movimiento` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `inventario_movimiento`
--

INSERT INTO `inventario_movimiento` (`id`, `id_producto_tienda`, `tipo_movimiento`, `cantidad`, `stock_anterior`, `stock_nuevo`, `motivo`, `referencia`, `id_usuario`, `notas`, `fecha_movimiento`) VALUES
(1, 1, 'entrada', 20, 0, 20, 'Stock inicial', 'INV-INICIAL', 2, 'Stock inicial del producto', '2025-11-17 02:20:54'),
(2, 2, 'entrada', 15, 0, 15, 'Stock inicial', 'INV-INICIAL', 2, 'Stock inicial del producto', '2025-11-17 02:20:54'),
(3, 3, 'entrada', 60, 0, 60, 'Stock inicial', 'INV-INICIAL', 2, 'Stock inicial del producto', '2025-11-17 02:20:54'),
(4, 4, 'entrada', 40, 0, 40, 'Stock inicial', 'INV-INICIAL', 2, 'Stock inicial del producto', '2025-11-17 02:20:54'),
(5, 5, 'entrada', 15, 0, 15, 'Stock inicial', 'INV-INICIAL', 2, 'Stock inicial del producto', '2025-11-17 02:20:54'),
(6, 6, 'entrada', 25, 0, 25, 'Stock inicial', 'INV-INICIAL', 2, 'Stock inicial del producto', '2025-12-13 02:07:25'),
(7, 7, 'entrada', 18, 0, 18, 'Stock inicial', 'INV-INICIAL', 2, 'Stock inicial del producto', '2025-12-13 02:07:25'),
(8, 8, 'entrada', 22, 0, 22, 'Stock inicial', 'INV-INICIAL', 2, 'Stock inicial del producto', '2025-12-13 02:07:25'),
(9, 9, 'entrada', 15, 0, 15, 'Stock inicial', 'INV-INICIAL', 2, 'Stock inicial del producto', '2025-12-13 02:07:25'),
(10, 10, 'entrada', 20, 0, 20, 'Stock inicial', 'INV-INICIAL', 2, 'Stock inicial del producto', '2025-12-13 02:07:25'),
(11, 11, 'entrada', 12, 0, 12, 'Stock inicial', 'INV-INICIAL', 2, 'Stock inicial del producto', '2025-12-13 02:07:25'),
(12, 12, 'entrada', 8, 0, 8, 'Stock inicial', 'INV-INICIAL', 2, 'Stock inicial del producto', '2025-12-13 02:07:25'),
(13, 13, 'entrada', 15, 0, 15, 'Stock inicial', 'INV-INICIAL', 2, 'Stock inicial del producto', '2025-12-13 02:07:25'),
(14, 14, 'entrada', 20, 0, 20, 'Stock inicial', 'INV-INICIAL', 2, 'Stock inicial del producto', '2025-12-13 02:07:25'),
(15, 15, 'entrada', 10, 0, 10, 'Stock inicial', 'INV-INICIAL', 2, 'Stock inicial del producto', '2025-12-13 02:07:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs_pagos`
--

CREATE TABLE `logs_pagos` (
  `id` int(11) NOT NULL,
  `orden_id` int(11) NOT NULL COMMENT 'Referencia a la tabla orden',
  `accion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ej: creado, comprobante_subido, verificado_manual, rechazado',
  `detalles` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Detalles adicionales en texto o JSON',
  `usuario_id` int(11) DEFAULT NULL COMMENT 'ID del usuario que realizó la acción (cliente o admin)',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `logs_pagos`
--

INSERT INTO `logs_pagos` (`id`, `orden_id`, `accion`, `detalles`, `usuario_id`, `ip_address`, `user_agent`, `fecha`) VALUES
(1, 1, 'referencia_generada', 'Referencia: NEXUS-001-20260107-0001 | Hash: 0f1aa8efcf602e52', 2, NULL, NULL, '2026-01-07 19:18:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marca`
--

CREATE TABLE `marca` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'default_logo.png',
  `sitio_web` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('activa','inactiva') COLLATE utf8mb4_unicode_ci DEFAULT 'activa',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `marca`
--

INSERT INTO `marca` (`id`, `nombre`, `descripcion`, `logo`, `sitio_web`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'Samsung', 'Tecnología y electrónica coreana', 'samsung.png', NULL, 'activa', '2025-11-15 15:51:34', '2025-11-16 23:50:41'),
(2, 'Appel', 'Tecnología y dispositivos Apple', 'appel.jpeg', NULL, 'activa', '2025-11-16 23:54:15', '2025-11-16 23:54:15'),
(3, 'Nike', 'Ropa y calzado deportivo', 'nike.jpeg', NULL, 'activa', '2025-11-16 23:54:15', '2025-11-16 23:54:15'),
(4, 'Adidas', 'Ropa y calzado deportivo', 'adidas.png', NULL, 'activa', '2025-11-16 23:54:15', '2025-11-16 23:54:15'),
(5, 'IKEA', 'Muebles y artículos para el hogar', 'ikea.png', NULL, 'activa', '2025-11-16 23:54:15', '2025-12-06 21:59:06'),
(6, 'Sony', 'Electrónicos y entretenimiento', 'sony.jpeg', NULL, 'activa', '2025-11-16 23:54:15', '2025-11-16 23:54:15'),
(7, 'HP', 'Computadoras y periféricos', 'hp.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(8, 'Lenovo', 'Computadoras y dispositivos', 'lenovo.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(9, 'Dell', 'Computadoras y tecnología', 'dell.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(10, 'LG', 'Electrodomésticos y electrónica', 'lg.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(11, 'Xiaomi', 'Tecnología y smartphones', 'xiaomi.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(12, 'Huawei', 'Tecnología y comunicaciones', 'huawei.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(13, 'Canon', 'Cámaras e impresoras', 'canon.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(14, 'Nikon', 'Cámaras fotográficas', 'nikon.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(15, 'Microsoft', 'Software y hardware', 'microsoft.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(16, 'Logitech', 'Periféricos de computadora', 'logitech.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(17, 'Kingston', 'Memorias y almacenamiento', 'kingston.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(18, 'Seagate', 'Discos duros', 'seagate.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(19, 'Western Digital', 'Almacenamiento digital', 'wd.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(20, 'Corsair', 'Componentes gaming', 'corsair.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(21, 'AMD', 'Procesadores y tarjetas gráficas', 'amd.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(22, 'Intel', 'Procesadores', 'intel.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(23, 'NVIDIA', 'Tarjetas gráficas', 'nvidia.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(24, 'Asus', 'Placas base y laptops', 'asus.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(25, 'MSI', 'Componentes gaming', 'msi.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(26, 'Gigabyte', 'Componentes de computadora', 'gigabyte.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(27, 'Razer', 'Periféricos gaming', 'razer.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(28, 'HyperX', 'Periféricos gaming', 'hyperx.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(29, 'TP-Link', 'Redes y conectividad', 'tp-link.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(30, 'Netgear', 'Equipos de red', 'netgear.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(31, 'Epson', 'Impresoras y proyectores', 'epson.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(32, 'Brother', 'Impresoras y scanners', 'brother.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(33, 'Philips', 'Electrodomésticos e iluminación', 'philips.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(34, 'Braun', 'Electrodomésticos personales', 'braun.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(35, 'Oral-B', 'Cuidado dental', 'oral-b.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(36, 'Remington', 'Cuidado personal', 'remington.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(37, 'Whirlpool', 'Electrodomésticos', 'whirlpool.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(38, 'Mabe', 'Electrodomésticos', 'mabe.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(39, 'Panasonic', 'Electrónica y electrodomésticos', 'panasonic.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(40, 'Toshiba', 'Electrónica y computadoras', 'toshiba.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(41, 'Acer', 'Computadoras y monitores', 'acer.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(42, 'ViewSonic', 'Monitores y proyectores', 'viewsonic.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(43, 'BenQ', 'Monitores y proyectores', 'benq.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(44, 'GoPro', 'Cámaras deportivas', 'gopro.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(45, 'Fitbit', 'Dispositivos fitness', 'fitbit.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(46, 'Garmin', 'Dispositivos GPS y fitness', 'garmin.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(47, 'JBL', 'Audio y parlantes', 'jbl.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(48, 'Bose', 'Audio de alta calidad', 'bose.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(49, 'Beats', 'Audífonos y audio', 'beats.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(50, 'Skullcandy', 'Audífonos y audio', 'skullcandy.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(51, 'Marshall', 'Audio y parlantes', 'marshall.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(52, 'Nintendo', 'Consolas y videojuegos', 'nintendo.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(53, 'Xbox', 'Consolas y videojuegos', 'xbox.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(54, 'PlayStation', 'Consolas y videojuegos', 'playstation.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(55, 'LEGO', 'Juguetes educativos', 'lego.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(56, 'Hasbro', 'Juguetes y juegos', 'hasbro.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(57, 'Mattel', 'Juguetes', 'mattel.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(58, 'Pampers', 'Productos para bebés', 'pampers.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(59, 'Huggies', 'Productos para bebés', 'huggies.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(60, 'Gerber', 'Alimentos para bebés', 'gerber.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(61, 'Nestlé', 'Alimentos y bebidas', 'nestle.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(62, 'Coca-Cola', 'Bebidas', 'coca-cola.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(63, 'Pepsi', 'Bebidas', 'pepsi.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(64, 'Kellogg\'s', 'Cereales y alimentos', 'kelloggs.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(65, 'Campbell\'s', 'Alimentos enlatados', 'campbells.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(66, 'Knorr', 'Alimentos y condimentos', 'knorr.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(67, 'Maggi', 'Alimentos y condimentos', 'maggi.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(68, 'Heinz', 'Alimentos y salsas', 'heinz.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(69, 'Nescafé', 'Café', 'nescafe.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(70, 'L\'Oréal', 'Productos de belleza', 'loreal.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(71, 'Maybelline', 'Cosméticos', 'maybelline.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(72, 'Revlon', 'Cosméticos', 'revlon.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(73, 'MAC', 'Cosméticos profesionales', 'mac.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(74, 'Estée Lauder', 'Productos de belleza', 'esteelauder.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(75, 'Clinique', 'Cuidado de la piel', 'clinique.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(76, 'Neutrogena', 'Cuidado de la piel', 'neutrogena.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(77, 'Nivea', 'Cuidado personal', 'nivea.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(78, 'Dove', 'Cuidado personal', 'dove.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(79, 'Pantene', 'Cuidado del cabello', 'pantene.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(80, 'Head & Shoulders', 'Cuidado del cabello', 'headshoulders.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(81, 'Garnier', 'Cuidado personal', 'garnier.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(82, 'Colgate', 'Cuidado dental', 'colgate.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(83, 'Crest', 'Cuidado dental', 'crest.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(84, 'Sensodyne', 'Cuidado dental', 'sensodyne.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(85, 'Listerine', 'Cuidado bucal', 'listerine.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(86, 'Axe', 'Cuidado personal masculino', 'axe.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(87, 'Old Spice', 'Cuidado personal masculino', 'oldspice.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(88, 'Gillette', 'Afeitado y cuidado personal', 'gillette.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(89, 'Schick', 'Afeitado', 'schick.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(90, 'Veet', 'Depilación', 'veet.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(91, 'Secret', 'Desodorantes', 'secret.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(92, 'Degree', 'Desodorantes', 'degree.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(93, 'Sure', 'Desodorantes', 'sure.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(94, 'Rexona', 'Desodorantes', 'rexona.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(95, 'Calvin Klein', 'Moda y fragancias', 'calvinklein.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(96, 'Levi\'s', 'Ropa', 'levis.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(97, 'Zara', 'Moda', 'zara.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(98, 'H&M', 'Moda', 'hm.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(99, 'Uniqlo', 'Ropa', 'uniqlo.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(100, 'Gap', 'Ropa', 'gap.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(101, 'Tommy Hilfiger', 'Moda', 'tommyhilfiger.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(102, 'Ralph Lauren', 'Moda', 'ralphlauren.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(103, 'Lacoste', 'Moda', 'lacoste.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(104, 'Under Armour', 'Ropa deportiva', 'underarmour.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(105, 'Puma', 'Ropa y calzado deportivo', 'puma.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(106, 'Reebok', 'Ropa y calzado deportivo', 'reebok.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(107, 'Converse', 'Calzado', 'converse.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(108, 'Vans', 'Calzado', 'vans.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(109, 'Timberland', 'Calzado', 'timberland.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(110, 'Crocs', 'Calzado', 'crocs.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(111, 'Skechers', 'Calzado', 'skechers.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(112, 'Clarks', 'Calzado', 'clarks.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(113, 'Dr. Martens', 'Calzado', 'drmartens.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(114, 'Nine West', 'Calzado y accesorios', 'ninewest.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(115, 'Michael Kors', 'Accesorios de moda', 'michaelkors.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(116, 'Kate Spade', 'Accesorios de moda', 'katespade.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(117, 'Coach', 'Accesorios de moda', 'coach.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(118, 'Fossil', 'Relojes y accesorios', 'fossil.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(119, 'Casio', 'Relojes y electrónica', 'casio.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(120, 'Swatch', 'Relojes', 'swatch.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(121, 'Timex', 'Relojes', 'timex.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(122, 'Seiko', 'Relojes', 'seiko.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53'),
(123, 'Citizen', 'Relojes', 'citizen.png', NULL, 'activa', '2025-12-12 13:49:53', '2025-12-12 13:49:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metodo_pago`
--

CREATE TABLE `metodo_pago` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre para mostrar (ej: Transfermóvil)',
  `codigo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Código interno (ej: transfermovil)',
  `descripcion` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Descripción breve para el usuario',
  `activo` tinyint(1) DEFAULT 0 COMMENT 'TRUE para métodos manuales como Transfermóvil',
  `requiere_verificacion` tinyint(1) DEFAULT 0 COMMENT 'TRUE para métodos manuales como Transfermóvil',
  `icono` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Clase de FontAwesome (ej: fas fa-mobile-alt)',
  `orden` int(11) DEFAULT 0 COMMENT 'Para ordenar en el checkout',
  `configuracion` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON para datos específicos (ej: número de tarjeta)' CHECK (json_valid(`configuracion`)),
  `instrucciones` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Instrucciones detalladas para el usuario',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `predeterminado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `metodo_pago`
--

INSERT INTO `metodo_pago` (`id`, `nombre`, `codigo`, `descripcion`, `activo`, `requiere_verificacion`, `icono`, `orden`, `configuracion`, `instrucciones`, `fecha_creacion`, `fecha_actualizacion`, `predeterminado`) VALUES
(1, 'Transfermóvil', 'transfermovil', 'Pago mediante la aplicación Transfermóvil. Recibirás una referencia única para usar en la transferencia.', 1, 1, 'fas fa-mobile-alt', 1, NULL, '1. Abre la aplicación Transfermóvil.\r\n2. Selecciona \"Transferir\" o \"Pagar\".\r\n3. Ingresa este número de tarjeta: 9201123456789012\r\n4. Introduce el monto exacto: 2,495.00 CUP.\r\n5. En el CONCEPTO, escribe esta referencia:\r\n6. Confirma la transferencia y guarda el comprobante.\r\n7. Tu pedido se procesará una vez verifiquemos el pago.', '2026-01-07 19:11:54', '2026-02-01 06:53:08', 0),
(2, 'Efectivo', 'efectivo', 'Paga en efectivo cuando recibas tu pedido.', 1, 0, 'fas fa-money-bill-wave', 2, NULL, 'Paga en efectivo al momento de recibir tu pedido.', '2026-01-07 19:11:54', '2026-02-01 06:53:14', 0),
(3, 'Transferencia Bancaria', 'transferencia', 'Realiza una transferencia directa a nuestra cuenta bancaria.', 1, 1, 'fas fa-university', 3, NULL, '1. Realiza transferencia a nuestra cuenta bancaria\n2. Usa la referencia del pedido como concepto\n3. Envía el comprobante', '2026-01-07 19:11:54', '2026-02-01 06:53:20', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modulo`
--

CREATE TABLE `modulo` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icono` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'fas fa-folder',
  `estado` enum('activo','inactivo') COLLATE utf8mb4_unicode_ci DEFAULT 'activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `modulo`
--

INSERT INTO `modulo` (`id`, `nombre`, `icono`, `estado`, `fecha_creacion`) VALUES
(1, 'Usuario', '<i class=\"fas fa-user\"></i>', 'activo', '2025-12-14 18:35:26'),
(2, 'Perfil', '<i class=\"fas fa-user-circle\"></i>', 'activo', '2025-12-14 18:35:26'),
(3, 'Direcciones', '<i class=\"fas fa-map-marked-alt\"></i>', 'activo', '2025-12-14 18:35:26'),
(4, 'Pedidos', '<i class=\"fas fa-shopping-bag\"></i>', 'activo', '2025-12-14 18:35:26'),
(5, 'Reseñas', '<i class=\"fas fa-star\"></i>', 'activo', '2025-12-14 18:35:26'),
(6, 'Carrito', '<i class=\"fas fa-shopping-cart\"></i>', 'activo', '2025-12-14 18:35:26'),
(7, 'Favoritos', '<i class=\"fas fa-heart\"></i>', 'activo', '2025-12-14 18:35:26'),
(8, 'Sistema', '<i class=\"fas fa-cog\"></i>', 'activo', '2025-12-14 18:35:26'),
(9, 'Autenticación', '<i class=\"fas fa-lock\"></i>', 'activo', '2025-12-14 18:35:26'),
(10, 'Configuración', '<i class=\"fas fa-cogs\"></i>', 'activo', '2025-12-14 18:35:26'),
(11, 'Productos', '<i class=\"fas fa-box\"></i>', 'activo', '2025-12-14 18:35:26'),
(12, 'Tienda', '<i class=\"fas fa-store\"></i>', 'activo', '2025-12-14 18:35:26'),
(13, 'Notificaciones', 'fas fa-bell', 'activo', '2025-12-14 18:35:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `moneda`
--

CREATE TABLE `moneda` (
  `id` int(11) NOT NULL,
  `codigo` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `simbolo` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tasa_cambio` decimal(10,4) DEFAULT 1.0000,
  `estado` enum('activa','inactiva') COLLATE utf8mb4_unicode_ci DEFAULT 'activa',
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `moneda`
--

INSERT INTO `moneda` (`id`, `codigo`, `nombre`, `simbolo`, `tasa_cambio`, `estado`, `fecha_actualizacion`) VALUES
(1, 'CUP', 'Peso Cubano', 'CUP', '1.0000', 'activa', '2025-12-14 04:55:57'),
(2, 'USD', 'Dólar Americano', 'USD$', '450.0000', 'activa', '2025-12-14 02:13:30'),
(3, 'EUR', 'Euro', '€', '540.0000', 'activa', '2025-11-15 16:14:20'),
(4, 'RUB', 'Rublio', 'Ꝑ', '120.0000', 'activa', '2025-12-03 04:36:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `municipio`
--

CREATE TABLE `municipio` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_provincia` int(11) NOT NULL,
  `estado` enum('activo','inactivo') COLLATE utf8mb4_unicode_ci DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `municipio`
--

INSERT INTO `municipio` (`id`, `nombre`, `id_provincia`, `estado`) VALUES
(1, 'Consolación del Sur', 1, 'activo'),
(2, 'Guane', 1, 'activo'),
(3, 'La Palma', 1, 'activo'),
(4, 'Los Palacios', 1, 'activo'),
(5, 'Mantua', 1, 'activo'),
(6, 'Minas de Matahambre', 1, 'activo'),
(7, 'Pinar del Río', 1, 'activo'),
(8, 'San Juan y Martínez', 1, 'activo'),
(9, 'San Luis', 1, 'activo'),
(10, 'Sandino', 1, 'activo'),
(11, 'Viñales', 1, 'activo'),
(12, 'Alquízar', 2, 'activo'),
(13, 'Artemisa', 2, 'activo'),
(14, 'Bauta', 2, 'activo'),
(15, 'Caimito', 2, 'activo'),
(16, 'Guanajay', 2, 'activo'),
(17, 'Güira de Melena', 2, 'activo'),
(18, 'Mariel', 2, 'activo'),
(19, 'San Antonio de los Baños', 2, 'activo'),
(20, 'Bahía Honda', 2, 'activo'),
(21, 'San Cristóbal', 2, 'activo'),
(22, 'Candelaria', 2, 'activo'),
(23, 'Batabanó', 3, 'activo'),
(24, 'Bejucal', 3, 'activo'),
(25, 'Güines', 3, 'activo'),
(26, 'Jaruco', 3, 'activo'),
(27, 'Madruga', 3, 'activo'),
(28, 'Melena del Sur', 3, 'activo'),
(29, 'Nueva Paz', 3, 'activo'),
(30, 'Quivicán', 3, 'activo'),
(31, 'San José de las Lajas', 3, 'activo'),
(32, 'San Nicolás de Bari', 3, 'activo'),
(33, 'Santa Cruz del Norte', 3, 'activo'),
(34, 'Arroyo Naranjo', 4, 'activo'),
(35, 'Boyeros', 4, 'activo'),
(36, 'Centro Habana', 4, 'activo'),
(37, 'Cerro', 4, 'activo'),
(38, 'Cotorro', 4, 'activo'),
(39, 'Diez de Octubre', 4, 'activo'),
(40, 'Guanabacoa', 4, 'activo'),
(41, 'Habana del Este', 4, 'activo'),
(42, 'Habana Vieja', 4, 'activo'),
(43, 'La Lisa', 4, 'activo'),
(44, 'Marianao', 4, 'activo'),
(45, 'Playa', 4, 'activo'),
(46, 'Plaza', 4, 'activo'),
(47, 'Regla', 4, 'activo'),
(48, 'San Miguel del Padrón', 4, 'activo'),
(49, 'Calimete', 5, 'activo'),
(50, 'Cárdenas', 5, 'activo'),
(51, 'Ciénaga de Zapata', 5, 'activo'),
(52, 'Colón', 5, 'activo'),
(53, 'Jagüey Grande', 5, 'activo'),
(54, 'Jovellanos', 5, 'activo'),
(55, 'Limonar', 5, 'activo'),
(56, 'Los Arabos', 5, 'activo'),
(57, 'Martí', 5, 'activo'),
(58, 'Matanzas', 5, 'activo'),
(59, 'Pedro Betancourt', 5, 'activo'),
(60, 'Perico', 5, 'activo'),
(61, 'Unión de Reyes', 5, 'activo'),
(62, 'Abreus', 6, 'activo'),
(63, 'Aguada de Pasajeros', 6, 'activo'),
(64, 'Cienfuegos', 6, 'activo'),
(65, 'Cruces', 6, 'activo'),
(66, 'Cumanayagua', 6, 'activo'),
(67, 'Palmira', 6, 'activo'),
(68, 'Rodas', 6, 'activo'),
(69, 'Santa Isabel de las Lajas', 6, 'activo'),
(71, 'Caibarién', 7, 'activo'),
(72, 'Camajuaní', 7, 'activo'),
(73, 'Cifuentes', 7, 'activo'),
(74, 'Corralillo', 7, 'activo'),
(75, 'Encrucijada', 7, 'activo'),
(76, 'Manicaragua', 7, 'activo'),
(77, 'Placetas', 7, 'activo'),
(78, 'Quemado de Güines', 7, 'activo'),
(79, 'Ranchuelo', 7, 'activo'),
(80, 'Remedios', 7, 'activo'),
(81, 'Sagua la Grande', 7, 'activo'),
(82, 'Santa Clara', 7, 'activo'),
(83, 'Santo Domingo', 7, 'activo'),
(84, 'Cabaigúan', 8, 'activo'),
(85, 'Fomento', 8, 'activo'),
(86, 'Jatibonico', 8, 'activo'),
(87, 'La Sierpe', 8, 'activo'),
(88, 'Sancti Spíritus', 8, 'activo'),
(89, 'Taguasco', 8, 'activo'),
(90, 'Trinidad', 8, 'activo'),
(91, 'Yaguajay', 8, 'activo'),
(92, 'Ciro Redondo', 9, 'activo'),
(93, 'Baragúa', 9, 'activo'),
(94, 'Bolivia', 9, 'activo'),
(95, 'Chambas', 9, 'activo'),
(96, 'Ciego de Ávila', 9, 'activo'),
(97, 'Florencia', 9, 'activo'),
(98, 'Majagua', 9, 'activo'),
(99, 'Morón', 9, 'activo'),
(100, 'Primero de Enero', 9, 'activo'),
(101, 'Venezuela', 9, 'activo'),
(102, 'Camagüey', 10, 'activo'),
(103, 'Carlos Manuel de Céspedes', 10, 'activo'),
(104, 'Esmeralda', 10, 'activo'),
(105, 'Florida', 10, 'activo'),
(106, 'Guaimaro', 10, 'activo'),
(107, 'Jimagüayú', 10, 'activo'),
(108, 'Minas', 10, 'activo'),
(109, 'Najasa', 10, 'activo'),
(110, 'Nuevitas', 10, 'activo'),
(111, 'Santa Cruz del Sur', 10, 'activo'),
(112, 'Sibanicú', 10, 'activo'),
(113, 'Sierra de Cubitas', 10, 'activo'),
(114, 'Vertientes', 10, 'activo'),
(115, 'Amancio Rodríguez', 11, 'activo'),
(116, 'Colombia', 11, 'activo'),
(117, 'Jesús Menéndez', 11, 'activo'),
(118, 'Jobabo', 11, 'activo'),
(119, 'Las Tunas', 11, 'activo'),
(120, 'Majibacoa', 11, 'activo'),
(121, 'Manatí', 11, 'activo'),
(122, 'Puerto Padre', 11, 'activo'),
(123, 'Antilla', 1, 'activo'),
(124, 'Báguanos', 12, 'activo'),
(125, 'Banes', 12, 'activo'),
(126, 'Cacocum', 12, 'activo'),
(127, 'Calixto García', 12, 'activo'),
(128, 'Cueto', 12, 'activo'),
(129, 'Frank País', 12, 'activo'),
(130, 'Gibara', 12, 'activo'),
(131, 'Holguín', 12, 'activo'),
(132, 'Mayarí', 12, 'activo'),
(133, 'Moa', 12, 'activo'),
(134, 'Rafael Freyre', 12, 'activo'),
(135, 'Sagua de Tánamo', 12, 'activo'),
(136, 'Urbano Noris', 12, 'activo'),
(137, 'Contramaestre', 13, 'activo'),
(138, 'Guamá', 13, 'activo'),
(139, 'Julio Antonio Mella', 13, 'activo'),
(140, 'Palma Soriano', 13, 'activo'),
(141, 'San Luis', 13, 'activo'),
(142, 'Santiago de Cuba', 13, 'activo'),
(143, 'Segundo Frente', 13, 'activo'),
(144, 'Songo la Maya', 13, 'activo'),
(145, 'Tercer Frente', 13, 'activo'),
(146, 'Baracoa', 14, 'activo'),
(147, 'Caimanera', 14, 'activo'),
(148, 'El Salvador', 14, 'activo'),
(149, 'Guantánamo', 14, 'activo'),
(150, 'Imías', 14, 'activo'),
(151, 'Maisí', 14, 'activo'),
(152, 'Manuel Tames', 14, 'activo'),
(153, 'Niceto Pérez', 14, 'activo'),
(154, 'San Antonio del Sur', 14, 'activo'),
(155, 'Yateras', 14, 'activo'),
(156, 'Isla de la Juventud', 15, 'activo'),
(157, 'Bartolomé Masó', 16, 'activo'),
(158, 'Bayamo', 16, 'activo'),
(159, 'Buey Arriba', 16, 'activo'),
(160, 'Campechuela', 16, 'activo'),
(161, 'Cauto Cristo', 16, 'activo'),
(162, 'Guisa', 16, 'activo'),
(163, 'Jiguaní', 16, 'activo'),
(164, 'Manzanillo', 16, 'activo'),
(165, 'Media Luna', 16, 'activo'),
(166, 'Niquero', 16, 'activo'),
(167, 'Pilón', 16, 'activo'),
(168, 'Río Cauto', 16, 'activo'),
(169, 'Yara', 16, 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificacion`
--

CREATE TABLE `notificacion` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('sistema','promocion','pedido','seguridad','soporte') COLLATE utf8mb4_unicode_ci DEFAULT 'sistema',
  `url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icono` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `leida` tinyint(1) DEFAULT 0,
  `fecha_leida` datetime DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `notificacion`
--

INSERT INTO `notificacion` (`id`, `id_usuario`, `titulo`, `mensaje`, `tipo`, `url`, `icono`, `leida`, `fecha_leida`, `fecha_creacion`) VALUES
(2, 2, 'Oferta especial', '50% de descuento en productos seleccionados. ¡Solo hoy!', 'promocion', NULL, '<i class=\"fas fa-tag\"></i>', 1, '2025-12-20 20:26:54', '2024-03-15 00:30:27'),
(3, 2, 'Actualización del sistema', 'Nuevas funciones disponibles en tu cuenta.', 'sistema', NULL, '<i class=\"fas fa-shopping-cart\"></i>', 1, '2025-12-20 21:56:45', '2024-03-14 03:50:27'),
(4, 2, 'Inicio de sesión nuevo', 'Se detectó un inicio de sesión desde un nuevo dispositivo.', 'seguridad', NULL, '<i class\"fas fa-shield-alt\"></i>', 0, NULL, '2024-03-12 19:21:17'),
(5, 2, 'Comentario en tu reseña', 'Alguien comentó en tu reseña del producto.', 'sistema', NULL, '<i class\"fas fa-users\"></i>', 0, NULL, '2024-03-12 01:00:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `oferta_flash`
--

CREATE TABLE `oferta_flash` (
  `id` int(11) NOT NULL,
  `producto_tienda_id` int(11) NOT NULL,
  `precio_especial` decimal(12,2) NOT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `stock_limitado` int(11) DEFAULT NULL,
  `stock_vendido` int(11) DEFAULT 0,
  `visitas` int(11) DEFAULT 0,
  `estado` enum('activa','finalizada','cancelada') COLLATE utf8mb4_unicode_ci DEFAULT 'activa',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `oferta_flash`
--

INSERT INTO `oferta_flash` (`id`, `producto_tienda_id`, `precio_especial`, `fecha_inicio`, `fecha_fin`, `stock_limitado`, `stock_vendido`, `visitas`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 7, '495000.00', '2026-01-25 11:09:00', '2026-01-27 20:07:59', 5, 0, 0, 'activa', '2025-12-25 04:00:58', '2026-01-25 16:07:42'),
(2, 28, '225000.00', '2025-12-25 12:00:00', '2025-12-26 18:00:00', 10, 0, 0, 'activa', '2025-12-25 04:00:58', '2025-12-25 04:05:59'),
(3, 43, '25000.00', '2025-12-26 09:00:00', '2025-12-26 15:00:00', 20, 0, 0, 'activa', '2025-12-25 04:00:58', '2025-12-25 04:00:58'),
(4, 75, '120000.00', '2025-12-26 14:00:00', '2025-12-26 20:00:00', 8, 0, 0, 'activa', '2025-12-25 04:00:58', '2025-12-25 04:00:58'),
(5, 6, '440000.00', '2025-12-27 10:00:00', '2025-12-27 22:00:00', 15, 0, 0, 'activa', '2025-12-25 04:00:58', '2025-12-25 04:00:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden`
--

CREATE TABLE `orden` (
  `id` int(11) NOT NULL,
  `numero_orden` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_direccion_envio` int(11) NOT NULL,
  `id_metodo_pago` int(11) DEFAULT NULL,
  `estado` enum('pendiente','confirmada','procesando','enviada','entregada','cancelada','reembolsada') COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente',
  `subtotal` decimal(12,2) NOT NULL CHECK (`subtotal` >= 0),
  `descuento` decimal(12,2) DEFAULT 0.00 CHECK (`descuento` >= 0),
  `costo_envio` decimal(10,2) DEFAULT 0.00 CHECK (`costo_envio` >= 0),
  `impuestos` decimal(10,2) DEFAULT 0.00 CHECK (`impuestos` >= 0),
  `total` decimal(12,2) NOT NULL,
  `codigo_seguimiento` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_entrega_estimada` date DEFAULT NULL,
  `fecha_entrega_real` datetime DEFAULT NULL,
  `notas_cliente` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notas_internas` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `metodo_pago_codigo` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'efectivo' COMMENT 'Código del método (ej: transfermovil, efectivo)',
  `referencia_pago` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Referencia única generada para este pago (ej: NEXUS-001)',
  `estado_pago` enum('pendiente','verificado','rechazado','cancelado') COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente',
  `comprobante_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ruta al archivo del comprobante subido por el cliente',
  `fecha_pago` datetime DEFAULT NULL,
  `fecha_verificacion` datetime DEFAULT NULL,
  `verificado_por` int(11) DEFAULT NULL COMMENT 'ID del administrador que verificó el pago',
  `hash_verificacion` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Hash para verificación segura del pago'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `orden`
--

INSERT INTO `orden` (`id`, `numero_orden`, `id_usuario`, `id_direccion_envio`, `id_metodo_pago`, `estado`, `subtotal`, `descuento`, `costo_envio`, `impuestos`, `total`, `codigo_seguimiento`, `fecha_entrega_estimada`, `fecha_entrega_real`, `notas_cliente`, `notas_internas`, `fecha_creacion`, `fecha_actualizacion`, `metodo_pago_codigo`, `referencia_pago`, `estado_pago`, `comprobante_url`, `fecha_pago`, `fecha_verificacion`, `verificado_por`, `hash_verificacion`) VALUES
(1, 'ORD-001', 2, 1, NULL, 'entregada', '270.00', '30.00', '1350.00', '0.00', '273.00', NULL, NULL, NULL, NULL, NULL, '2025-11-17 02:30:22', '2026-01-07 19:18:10', 'efectivo', 'NEXUS-001-20260107-0001', 'pendiente', NULL, '2026-01-07 14:18:10', NULL, NULL, '0f1aa8efcf602e52a5bf0f124c767760d9d22952fb38f6bc382db0f54304d836'),
(2, 'ORD-20251214-693E7AFE94C46', 2, 1, NULL, 'pendiente', '540843.75', '0.00', '0.00', '0.00', '540843.75', NULL, NULL, NULL, NULL, NULL, '2025-12-14 08:53:18', '2025-12-14 08:53:18', 'efectivo', NULL, 'pendiente', NULL, NULL, NULL, NULL, NULL),
(3, 'ORD-20251214-693E7D5E3CC96', 2, 1, NULL, 'pendiente', '43200.00', '0.00', '0.00', '0.00', '43200.00', NULL, NULL, NULL, NULL, NULL, '2025-12-14 09:03:26', '2025-12-14 09:03:26', 'efectivo', NULL, 'pendiente', NULL, NULL, NULL, NULL, NULL),
(4, 'ORD-20251214-693E879B52F64', 2, 1, NULL, 'pendiente', '55125.00', '0.00', '0.00', '0.00', '55125.00', NULL, NULL, NULL, NULL, NULL, '2025-12-14 09:47:07', '2025-12-14 09:47:07', 'efectivo', NULL, 'pendiente', NULL, NULL, NULL, NULL, NULL),
(5, 'ORD-20251214-693E8AF86FB9A', 2, 1, NULL, 'pendiente', '271615.50', '0.00', '0.00', '0.00', '271615.50', NULL, NULL, NULL, NULL, NULL, '2025-12-14 10:01:28', '2025-12-14 10:01:28', 'efectivo', NULL, 'pendiente', NULL, NULL, NULL, NULL, NULL),
(6, 'ORD-20251214-693E95AA15247', 2, 1, NULL, 'pendiente', '540843.75', '0.00', '1350.00', '0.00', '540846.75', NULL, NULL, NULL, NULL, NULL, '2025-12-14 10:47:06', '2025-12-17 03:25:17', 'efectivo', NULL, 'pendiente', NULL, NULL, NULL, NULL, NULL),
(7, 'ORD-20251214-693EAB9C8CC95', 2, 1, NULL, 'confirmada', '453771.50', '0.00', '2250.00', '0.00', '453771.50', NULL, NULL, NULL, NULL, NULL, '2025-12-14 12:20:44', '2025-12-17 03:28:18', 'efectivo', NULL, 'pendiente', NULL, NULL, NULL, NULL, NULL),
(51, 'ORD-20260120-696EF9635CE56', 2, 5, NULL, 'pendiente', '0.54', '0.00', '5.00', '0.00', '5.54', NULL, NULL, NULL, NULL, NULL, '2026-01-20 03:41:23', '2026-01-20 03:41:23', 'transfermovil', 'NX-20260120-61C7BB', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(52, 'ORD-20260120-696EF9ED50468', 2, 5, NULL, 'pendiente', '0.54', '0.00', '5.00', '0.00', '5.54', NULL, NULL, NULL, NULL, NULL, '2026-01-20 03:43:41', '2026-01-20 03:43:41', 'transfermovil', 'NX-20260120-6C0920', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(53, 'ORD-20260120-696EFC4E7BB3E', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-20 03:53:50', '2026-01-20 03:53:50', 'transfermovil', 'NX-20260120-655703', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(54, 'ORD-20260120-696EFD119309C', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-20 03:57:05', '2026-01-20 03:57:05', 'transfermovil', 'NX-20260120-A4B9C4', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(55, 'ORD-20260120-696F04A3CD529', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-20 04:29:23', '2026-01-20 04:29:23', 'transfermovil', 'NX-20260120-C8477C', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(56, 'ORD-20260120-696F0813D3336', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-20 04:44:03', '2026-01-20 04:44:03', 'transfermovil', 'NX-20260120-474BB3', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(57, 'ORD-20260120-696F11337FE7E', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-20 05:22:59', '2026-01-20 05:22:59', 'transfermovil', 'NX-20260120-46B09B', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(58, 'ORD-20260120-696F119E39FA2', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-20 05:24:46', '2026-01-20 05:24:46', 'transfermovil', 'NX-20260120-16D9CD', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(59, 'ORD-20260120-696F13D96501D', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-20 05:34:17', '2026-01-20 05:34:17', 'transfermovil', 'NX-20260120-C7D094', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(60, 'ORD-20260120-696F1400EDE59', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-20 05:34:57', '2026-01-20 05:34:57', 'transfermovil', 'NX-20260120-1B012B', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(61, 'ORD-20260121-697052FFB7136', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-21 04:15:59', '2026-01-21 04:15:59', 'transfermovil', 'NX-20260121-B985DC', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(62, 'ORD-20260123-6973A4DA0ED4A', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-23 16:42:02', '2026-01-23 16:42:02', 'transfermovil', 'NX-20260123-75B359', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(63, 'ORD-20260123-6973BC032803C', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-23 18:20:51', '2026-01-23 18:20:51', 'transfermovil', 'NX-20260123-E3F014', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(64, 'ORD-20260123-6973BC464670C', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-23 18:21:58', '2026-01-23 18:21:58', 'transfermovil', 'NX-20260123-87EB29', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(65, 'ORD-20260123-6973BCC7D9BD0', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-23 18:24:07', '2026-01-23 18:24:07', 'transfermovil', 'NX-20260123-B1C1C3', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(66, 'ORD-20260123-6973BD1B78075', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-23 18:25:31', '2026-01-23 18:25:31', 'transfermovil', 'NX-20260123-E9C717', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(67, 'ORD-20260124-6974D8AA4F67B', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-24 14:35:22', '2026-01-24 14:35:22', 'transfermovil', 'NX-20260124-FA6543', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(68, 'ORD-20260124-6974E108D58FF', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-24 15:11:04', '2026-01-24 15:11:04', 'transfermovil', 'NX-20260124-0241B5', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(69, 'ORD-20260124-6974E1B35D3AB', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-24 15:13:55', '2026-01-24 15:13:55', 'transfermovil', 'NX-20260124-B6220A', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(70, 'ORD-20260124-6974E22174FAF', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-24 15:15:45', '2026-01-24 15:15:45', 'transfermovil', 'NX-20260124-AEEA05', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(71, 'ORD-20260124-6974E5321FA01', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-24 15:28:50', '2026-01-24 15:28:50', 'transfermovil', 'NX-20260124-833A6C', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(72, 'ORD-20260124-6974E645456E3', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-24 15:33:25', '2026-01-24 15:33:25', 'transfermovil', 'NX-20260124-EF3B3B', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(73, 'ORD-20260124-6974E6EB45FC2', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-24 15:36:11', '2026-01-24 15:36:11', 'transfermovil', 'NX-20260124-636FB4', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(74, 'ORD-20260124-6974EB0E8C0C0', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-24 15:53:50', '2026-01-24 15:53:50', 'transfermovil', 'NX-20260124-FFD73B', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(75, 'ORD-20260124-6974EC7D3AD59', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-24 15:59:57', '2026-01-24 15:59:57', 'transfermovil', 'NX-20260124-D64458', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(76, 'ORD-20260124-6974ED642BEF5', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-24 16:03:48', '2026-01-24 16:03:48', 'transfermovil', 'NX-20260124-0EE257', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(77, 'ORD-20260124-6974F33215DD7', 2, 5, NULL, 'pendiente', '110250.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-24 16:28:34', '2026-01-24 16:28:34', 'transfermovil', 'NX-20260124-AB96FA', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(78, 'ORD-20260124-6974F58E65537', 2, 5, NULL, 'pendiente', '110250.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-24 16:38:38', '2026-01-24 16:38:38', 'transfermovil', 'NX-20260124-08B7C3', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(79, 'ORD-20260124-6975016607B04', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-24 17:29:10', '2026-01-24 17:29:10', 'transfermovil', 'NX-20260124-547282', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(80, 'ORD-20260124-6975020A1BB75', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-24 17:31:54', '2026-01-24 17:31:54', 'transfermovil', 'NX-20260124-01E52A', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(81, 'ORD-20260124-6975286870EC3', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-24 20:15:38', '2026-01-24 20:15:38', 'transfermovil', 'NX-20260124-95AA69', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(82, 'ORD-20260124-69752DA19B98E', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-24 20:37:53', '2026-01-24 20:37:53', 'transfermovil', 'NX-20260124-BE29A1', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(83, 'ORD-20260125-69763D4014E03', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-25 15:56:48', '2026-01-25 15:56:48', 'transfermovil', 'NX-20260125-2F6311', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(84, 'ORD-20260125-69763D9EDF5ED', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-25 15:58:22', '2026-01-25 15:58:22', 'transfermovil', 'NX-20260125-286996', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(85, 'ORD-20260125-69763DFDC55F8', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-25 15:59:57', '2026-01-25 15:59:57', 'transfermovil', 'NX-20260125-2B97B6', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(86, 'ORD-20260125-69763E776FE8E', 2, 5, NULL, 'pendiente', '245.00', '0.00', '2250.00', '0.00', '2495.00', NULL, NULL, NULL, NULL, NULL, '2026-01-25 16:01:59', '2026-01-25 16:01:59', 'transfermovil', 'NX-20260125-98016D', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(87, 'ORD-20260125-69765F7AC5FC1', 2, 5, NULL, 'pendiente', '245.00', '0.00', '0.00', '0.00', '245.00', NULL, NULL, NULL, NULL, NULL, '2026-01-25 18:22:59', '2026-01-25 18:22:59', 'transfermovil', 'NX-20260125-05F20B', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(88, 'ORD-20260125-69767A2D7A42B', 2, 5, NULL, 'pendiente', '280.00', '0.00', '0.00', '0.00', '245.00', NULL, NULL, NULL, NULL, NULL, '2026-01-25 20:16:45', '2026-01-25 20:16:45', 'transfermovil', 'NX-20260125-64E9D3', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(89, 'ORD-20260125-69767AAC6D112', 2, 5, NULL, 'pendiente', '280.00', '0.00', '0.00', '0.00', '245.00', NULL, NULL, NULL, NULL, NULL, '2026-01-25 20:18:57', '2026-01-25 20:18:57', 'transfermovil', 'NX-20260125-C4CF65', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(90, 'ORD-20260125-6976803D1DAA1', 2, 5, NULL, 'pendiente', '280.00', '0.00', '0.00', '0.00', '245.00', NULL, NULL, NULL, NULL, NULL, '2026-01-25 20:42:37', '2026-01-25 20:42:37', 'transfermovil', 'NX-20260125-AA6D24', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(91, 'ORD-20260126-6976BE3F345DA', 2, 5, NULL, 'pendiente', '280.00', '0.00', '0.00', '0.00', '245.00', NULL, NULL, NULL, NULL, NULL, '2026-01-26 01:07:11', '2026-01-26 01:07:11', 'transfermovil', 'NX-20260126-67B09E', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(92, 'ORD-20260126-6976BE8A15E16', 2, 5, NULL, 'pendiente', '280.00', '0.00', '0.00', '0.00', '245.00', NULL, NULL, NULL, NULL, NULL, '2026-01-26 01:08:26', '2026-01-26 01:08:26', 'transfermovil', 'NX-20260126-EE8394', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(93, 'ORD-20260126-6976D6DDCE80E', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-26 02:52:13', '2026-01-26 02:52:13', 'transfermovil', 'NX-20260126-0CDF03', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(94, 'ORD-20260126-6976D7629D34C', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-26 02:54:26', '2026-01-26 02:54:26', 'transfermovil', 'NX-20260126-094796', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(95, 'ORD-20260126-6976D83AA14CD', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-26 02:58:02', '2026-01-26 02:58:02', 'transfermovil', 'NX-20260126-E2468B', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(96, 'ORD-20260126-6976DC169BDAD', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-26 03:14:30', '2026-01-26 03:14:30', 'transfermovil', 'NX-20260126-5762A9', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(97, 'ORD-20260126-6976E33C90ADD', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-26 03:45:00', '2026-01-26 03:45:00', 'transfermovil', 'NX-20260126-870925', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(98, 'ORD-20260126-6976E44E622B7', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-26 03:49:34', '2026-01-26 03:49:34', 'transfermovil', 'NX-20260126-08BA8F', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(99, 'ORD-20260126-6976E79E9C3C8', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-26 04:03:43', '2026-01-26 04:03:43', 'transfermovil', 'NX-20260126-F6AE26', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(100, 'ORD-20260126-6976EA331237B', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-26 04:14:43', '2026-01-26 04:14:43', 'transfermovil', 'NX-20260126-9513D7', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(101, 'ORD-20260126-697746E5A3827', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-26 10:50:19', '2026-01-26 10:50:19', 'transfermovil', 'NX-20260126-A5295D', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(102, 'ORD-20260126-6977477B2D97A', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-26 10:52:43', '2026-01-26 10:52:43', 'transfermovil', 'NX-20260126-9714AC', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(103, 'ORD-20260126-697749FAAC2BE', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-26 11:03:28', '2026-01-26 11:03:28', 'transfermovil', 'NX-20260126-9E3B51', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(104, 'ORD-20260126-69778C77A3411', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-26 15:47:07', '2026-01-26 15:47:07', 'transfermovil', 'NX-20260126-7C57FA', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(105, 'ORD-20260127-6978ECBA4DD6D', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-27 16:50:08', '2026-01-27 16:50:08', 'transfermovil', 'NX-20260127-BAFA5C', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(106, 'ORD-20260127-6978ED8C52758', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-27 16:53:32', '2026-01-27 16:53:32', 'transfermovil', 'NX-20260127-BFCD11', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(107, 'ORD-20260127-6978EE0A17AE2', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-27 16:55:38', '2026-01-27 16:55:38', 'transfermovil', 'NX-20260127-CD98A4', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(108, 'ORD-20260127-6978EE5255741', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-27 16:56:50', '2026-01-27 16:56:50', 'transfermovil', 'NX-20260127-79268C', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(109, 'ORD-20260127-6978F39CCAA4D', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-27 17:19:25', '2026-01-27 17:19:25', 'transfermovil', 'NX-20260127-8E8F07', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(110, 'ORD-20260127-6978F3D843AD5', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-27 17:20:24', '2026-01-27 17:20:24', 'transfermovil', 'NX-20260127-8BBED0', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(111, 'ORD-20260127-6978F41B00274', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-27 17:21:31', '2026-01-27 17:21:31', 'transfermovil', 'NX-20260127-946678', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(112, 'ORD-20260128-6979851BA7482', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-28 03:40:11', '2026-01-28 03:40:11', 'transfermovil', 'NX-20260128-07938C', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(113, 'ORD-20260128-69798578DB57C', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-28 03:41:44', '2026-01-28 03:41:44', 'transfermovil', 'NX-20260128-A61674', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(114, 'ORD-20260128-697985C243F1E', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-28 03:43:04', '2026-01-28 03:43:04', 'transfermovil', 'NX-20260128-E42AF3', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(115, 'ORD-20260128-6979889A03E55', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-28 03:55:06', '2026-01-28 03:55:06', 'transfermovil', 'NX-20260128-D2A29C', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(116, 'ORD-20260128-6979895D8F8E7', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-28 03:58:22', '2026-01-28 03:58:22', 'transfermovil', 'NX-20260128-1E62F8', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(117, 'ORD-20260128-69798E3D7D282', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-28 04:19:09', '2026-01-28 04:19:09', 'transfermovil', 'NX-20260128-E94531', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(118, 'ORD-20260128-69798FFCD790A', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-28 04:26:36', '2026-01-28 04:26:36', 'transfermovil', 'NX-20260128-994803', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(119, 'ORD-20260128-69799283557C7', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-28 04:37:23', '2026-01-28 04:37:23', 'transfermovil', 'NX-20260128-6084D3', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(120, 'ORD-20260128-6979941F15C74', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-28 04:44:15', '2026-01-28 04:44:15', 'transfermovil', 'NX-20260128-C49956', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(121, 'ORD-20260129-697ACF7EAC9C1', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-29 03:09:50', '2026-01-29 03:09:50', 'transfermovil', 'NX-20260129-2F2D41', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(122, 'ORD-20260129-697AD183C1736', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-29 03:18:27', '2026-01-29 03:18:27', 'transfermovil', 'NX-20260129-7678FE', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(123, 'ORD-20260129-697AD215F1948', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-29 03:20:53', '2026-01-29 03:20:53', 'transfermovil', 'NX-20260129-8366E8', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(124, 'ORD-20260129-697AD430EE0E7', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-29 03:29:52', '2026-01-29 03:29:52', 'transfermovil', 'NX-20260129-F8C84B', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(125, 'ORD-20260129-697AD4BBE6C5B', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-29 03:32:11', '2026-01-29 03:32:11', 'transfermovil', 'NX-20260129-CB7B00', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(126, 'ORD-20260129-697AD5DE52E28', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-29 03:37:02', '2026-01-29 03:37:02', 'transfermovil', 'NX-20260129-CBD6BB', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(127, 'ORD-20260129-697AD6097339A', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-29 03:37:45', '2026-01-29 03:37:45', 'transfermovil', 'NX-20260129-9E4D92', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(128, 'ORD-20260129-697AD67EC428D', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-29 03:39:42', '2026-01-29 03:39:42', 'transfermovil', 'NX-20260129-E1CD3B', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(129, 'ORD-20260131-697D711716C09', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-31 03:03:53', '2026-01-31 03:03:53', 'transfermovil', 'NX-20260131-A71CCB', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(130, 'ORD-20260131-697D7215A6597', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-31 03:08:07', '2026-01-31 03:08:07', 'transfermovil', 'NX-20260131-80E803', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(131, 'ORD-20260131-697D728D2192D', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-31 03:10:05', '2026-01-31 03:10:05', 'transfermovil', 'NX-20260131-0DD297', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(132, 'ORD-20260131-697D77164D721', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-31 03:29:26', '2026-01-31 03:29:26', 'transfermovil', 'NX-20260131-528B07', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(133, 'ORD-20260131-697D7DDDD0F60', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-31 03:58:22', '2026-01-31 03:58:22', 'transfermovil', 'NX-20260131-D13A18', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(134, 'ORD-20260131-697D7FF2C933C', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-31 04:07:14', '2026-01-31 04:07:14', 'transfermovil', 'NX-20260131-F967A5', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(135, 'ORD-20260131-697D804ED5FF8', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-31 04:08:46', '2026-01-31 04:08:46', 'transfermovil', 'NX-20260131-76E19E', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(136, 'ORD-20260131-697D80C038FAF', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-31 04:10:40', '2026-01-31 04:10:40', 'transfermovil', 'NX-20260131-8CE007', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(137, 'ORD-20260131-697D87E539952', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-31 04:41:09', '2026-01-31 04:41:09', 'transfermovil', 'NX-20260131-434652', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(138, 'ORD-20260131-697D8824593F1', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-31 04:42:12', '2026-01-31 04:42:12', 'transfermovil', 'NX-20260131-E14545', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(139, 'ORD-20260131-697D8A49578A0', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-31 04:51:22', '2026-01-31 04:51:22', 'transfermovil', 'NX-20260131-E188FF', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(140, 'ORD-20260131-697D8B61679E1', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-31 04:56:01', '2026-01-31 04:56:01', 'transfermovil', 'NX-20260131-CA4A27', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(141, 'ORD-20260131-697D8C0A56973', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-31 04:58:50', '2026-01-31 04:58:50', 'transfermovil', 'NX-20260131-D1C6F9', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(142, 'ORD-20260131-697D8CED036FA', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-31 05:02:37', '2026-01-31 05:02:37', 'transfermovil', 'NX-20260131-2BF9EE', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(143, 'ORD-20260131-697D8D241BED0', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-31 05:03:32', '2026-01-31 05:03:32', 'transfermovil', 'NX-20260131-991630', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(144, 'ORD-20260131-697D94C8411D2', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-31 05:36:08', '2026-01-31 05:36:08', 'transfermovil', 'NX-20260131-775F4F', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(145, 'ORD-20260131-697D95F21FCAC', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-31 05:41:06', '2026-01-31 05:41:06', 'transfermovil', 'NX-20260131-90594C', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(146, 'ORD-20260131-697D97C6BDC9D', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-31 05:48:54', '2026-01-31 05:48:54', 'transfermovil', 'NX-20260131-964175', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(147, 'ORD-20260131-697D9F3D1333A', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-31 06:20:45', '2026-01-31 06:20:45', 'transfermovil', 'NX-20260131-A486F2', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(148, 'ORD-20260131-697DABB54F773', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-31 07:13:57', '2026-01-31 07:13:57', 'transfermovil', 'NX-20260131-4C2A48', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(149, 'ORD-20260131-697DABFB6326F', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-31 07:15:07', '2026-01-31 07:15:07', 'transfermovil', 'NX-20260131-A290D3', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(150, 'ORD-20260131-697DAC92E3AB1', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-01-31 07:17:38', '2026-01-31 07:17:38', 'transfermovil', 'NX-20260131-CE43D7', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(151, 'ORD-20260201-697EF2C5E4325', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-02-01 06:29:25', '2026-02-01 06:29:25', 'transfermovil', 'NX-20260201-D8531B', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(152, 'ORD-20260201-697EF54CC0BFD', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-02-01 06:40:12', '2026-02-01 06:40:12', 'transfermovil', 'NX-20260201-154B11', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(153, 'ORD-20260201-697EF58D9B035', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-02-01 06:41:17', '2026-02-01 06:41:17', 'transfermovil', 'NX-20260201-B20E39', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(154, 'ORD-20260201-697EFFC828BAA', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-02-01 07:24:56', '2026-02-01 07:24:56', 'transfermovil', 'NX-20260201-7B7323', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(155, 'ORD-20260201-697F004257C15', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-02-01 07:26:58', '2026-02-01 07:26:58', 'transfermovil', 'NX-20260201-627DC5', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(156, 'ORD-20260201-697F02BE8EEB0', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-02-01 07:37:34', '2026-02-01 07:37:34', 'transfermovil', 'NX-20260201-DFEF69', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(157, 'ORD-20260201-697F048EE2717', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-02-01 07:45:19', '2026-02-01 07:45:19', 'transfermovil', 'NX-20260201-070019', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(158, 'ORD-20260201-697F0812A5CF8', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-02-01 08:00:18', '2026-02-01 08:00:18', 'transfermovil', 'NX-20260201-7A759F', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(159, 'ORD-20260202-697FFB90EE985', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-02-02 01:19:12', '2026-02-02 01:19:12', 'transfermovil', 'NX-20260202-58317E', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(160, 'ORD-20260202-697FFD341A8CC', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-02-02 01:26:12', '2026-02-02 01:26:12', 'transfermovil', 'NX-20260202-FA56A1', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(161, 'ORD-20260202-697FFE696D1E5', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-02-02 01:31:21', '2026-02-02 01:31:21', 'transfermovil', 'NX-20260202-0BD12B', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(162, 'ORD-20260202-697FFF1E54F21', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-02-02 01:34:22', '2026-02-02 01:34:22', 'transfermovil', 'NX-20260202-4269A3', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(163, 'ORD-20260202-697FFFC2BEE68', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-02-02 01:37:06', '2026-02-02 01:37:06', 'transfermovil', 'NX-20260202-DAE040', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(164, 'ORD-20260202-698001F0429B1', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-02-02 01:46:24', '2026-02-02 01:46:24', 'transfermovil', 'NX-20260202-ABA80B', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(165, 'ORD-20260202-698002E826D01', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-02-02 01:50:32', '2026-02-02 01:50:32', 'transfermovil', 'NX-20260202-567A43', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(166, 'ORD-20260202-69800325D8ADF', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-02-02 01:51:33', '2026-02-02 01:51:33', 'transfermovil', 'NX-20260202-F36B6D', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(167, 'ORD-20260202-698004A04C435', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-02-02 01:57:52', '2026-02-02 01:57:52', 'transfermovil', 'NX-20260202-F318BE', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(168, 'ORD-20260202-698005D5F0734', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-02-02 02:03:01', '2026-02-02 02:03:01', 'transfermovil', 'NX-20260202-DF8DBC', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(169, 'ORD-20260202-698006447DE04', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-02-02 02:04:52', '2026-02-02 02:04:52', 'transfermovil', 'NX-20260202-F28C71', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(170, 'ORD-20260202-6980078E83C62', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-02-02 02:10:22', '2026-02-02 02:10:22', 'transfermovil', 'NX-20260202-317F26', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(171, 'ORD-20260202-698007DE833BA', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-02-02 02:11:42', '2026-02-02 02:11:42', 'transfermovil', 'NX-20260202-809BD6', 'pendiente', NULL, NULL, NULL, NULL, NULL),
(172, 'ORD-20260202-6980092F7EF88', 2, 5, NULL, 'pendiente', '126000.00', '0.00', '2250.00', '0.00', '112500.00', NULL, NULL, NULL, NULL, NULL, '2026-02-02 02:17:19', '2026-02-02 02:17:19', 'transfermovil', 'NX-20260202-81D4C0', 'pendiente', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden_detalle`
--

CREATE TABLE `orden_detalle` (
  `id` int(11) NOT NULL,
  `id_orden` int(11) NOT NULL,
  `id_producto_tienda` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL CHECK (`cantidad` > 0),
  `precio_unitario` decimal(10,2) NOT NULL CHECK (`precio_unitario` >= 0),
  `descuento_unitario` decimal(10,2) DEFAULT 0.00 CHECK (`descuento_unitario` >= 0),
  `subtotal` decimal(12,2) NOT NULL CHECK (`subtotal` >= 0),
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `orden_detalle`
--

INSERT INTO `orden_detalle` (`id`, `id_orden`, `id_producto_tienda`, `cantidad`, `precio_unitario`, `descuento_unitario`, `subtotal`, `fecha_creacion`) VALUES
(1, 1, 3, 2, '120.00', '30.00', '240.00', '2025-11-17 02:30:41'),
(2, 1, 4, 1, '140.00', '0.00', '140.00', '2025-11-17 02:30:41'),
(3, 2, 7, 1, '562500.00', '21656.25', '540843.75', '2025-12-14 08:53:27'),
(4, 3, 3, 1, '54000.00', '10800.00', '43200.00', '2025-12-14 09:03:26'),
(5, 4, 4, 1, '63000.00', '7875.00', '55125.00', '2025-12-14 09:47:07'),
(6, 5, 23, 1, '292500.00', '20884.50', '271615.50', '2025-12-14 10:01:28'),
(7, 6, 7, 1, '562500.00', '21656.25', '540843.75', '2025-12-14 10:47:06'),
(8, 7, 6, 1, '495000.00', '41233.50', '453766.50', '2025-12-14 12:20:44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `id` int(11) NOT NULL,
  `sku` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion_corta` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion_larga` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_marca` int(11) DEFAULT NULL,
  `id_subcategoria` int(11) NOT NULL,
  `id_unidad_medida` int(11) NOT NULL,
  `peso` decimal(8,3) DEFAULT NULL,
  `dimensiones` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `caracteristicas` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`caracteristicas`)),
  `etiquetas` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`etiquetas`)),
  `estado` enum('activo','inactivo','agotado','descontinuado') COLLATE utf8mb4_unicode_ci DEFAULT 'activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`id`, `sku`, `nombre`, `descripcion_corta`, `descripcion_larga`, `id_marca`, `id_subcategoria`, `id_unidad_medida`, `peso`, `dimensiones`, `caracteristicas`, `etiquetas`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'SM-G998B', 'Samsung Galaxy S21 Ultra', 'Flagship smartphone con cámara profesional', 'El Samsung Galaxy S21 Ultra es el smartphone más avanzado con cámara de 108MP, pantalla Dynamic AMOLED 2X de 6.8\" y procesador Exynos 2100.', 1, 1, 6, '228.000', '165.1 x 75.6 x 8.9 mm', '{\"Pantalla\": \"6.8 pulgadas\", \"RAM\": \"12GB\", \"Almacenamiento\": \"256GB\", \"Cámara\": \"108MP + 10MP + 10MP + 12MP\"}', '[\"smartphone\", \"android\", \"samsung\", \"5g\"]', 'activo', '2025-11-17 02:11:50', '2025-11-17 02:11:50'),
(2, 'AP-IP13P', 'iPhone 13 Pro', 'iPhone profesional con chip A15 Bionic', 'iPhone 13 Pro con sistema de cámaras Pro, pantalla Super Retina XDR con ProMotion y el más rápido chip A15 Bionic.', 2, 47, 6, '204.000', '146.7 x 71.5 x 7.7 mm', '{\"Pantalla\": \"6.1 pulgadas\", \"RAM\": \"6GB\", \"Almacenamiento\": \"128GB\", \"Cámara\": \"12MP + 12MP + 12MP\"}', '[\"iphone\", \"apple\", \"ios\", \"5g\"]', 'activo', '2025-11-17 02:11:50', '2025-12-11 06:43:08'),
(3, 'NK-AF1', 'Nike Air Force 1', 'Zapatillas clásicas de baloncesto', 'Las Nike Air Force 1 son un ícono del baloncesto con amortiguación Air y diseño timeless.', 3, 102, 6, '800.000', '30 x 15 x 10 cm', '{\"Material\": \"Cuero\", \"Suela\": \"Goma\", \"Cierre\": \"Cordones\"}', '[\"zapatillas\", \"deportivas\", \"nike\", \"urbanas\"]', 'activo', '2025-11-17 02:11:50', '2025-11-17 02:11:50'),
(4, 'AD-SUPR', 'Adidas Ultraboost 22', 'Zapatillas running con tecnología Boost', 'Adidas Ultraboost 22 con amortiguación Boost responsive y upper Primeknit para máximo confort.', 4, 102, 6, '750.000', '29 x 14 x 9 cm', '{\"Material\": \"Primeknit\", \"Suela\": \"Boost\", \"Drop\": \"10mm\"}', '[\"running\", \"deportivas\", \"adidas\", \"comfort\"]', 'activo', '2025-11-17 02:11:50', '2025-11-17 02:11:50'),
(5, 'SN-WH1000', 'Sony WH-1000XM4', 'Audífonos noise cancelling líderes', 'Audífonos inalámbricos con cancelación de ruido líder en la industria y calidad de sonido excepcional.', 6, 4, 6, '254.000', '21 x 19 x 8 cm', '{\"Cancelación\": \"Activa\", \"Batería\": \"30h\", \"Conectividad\": \"Bluetooth 5.0\"}', '[\"audífonos\", \"noise-cancelling\", \"sony\", \"wireless\"]', 'activo', '2025-11-17 02:11:50', '2025-11-17 02:11:50'),
(6, 'SM-G998B-2', 'Samsung Galaxy S22 Ultra', 'Smartphone con S Pen integrado', 'El Samsung Galaxy S22 Ultra combina lo mejor de Note y S Series con cámara de 108MP y S Pen integrado.', 1, 1, 6, '228.000', '163.3 x 77.9 x 8.9 mm', '{\"Pantalla\": \"6.8 pulgadas Dynamic AMOLED 2X\", \"Procesador\": \"Snapdragon 8 Gen 1\", \"RAM\": \"12GB\", \"Almacenamiento\": \"256GB\", \"Cámara\": \"108MP + 12MP + 10MP + 10MP\", \"Batería\": \"5000mAh\"}', '[\"smartphone\", \"android\", \"samsung\", \"5g\", \"spen\"]', 'activo', '2025-12-12 18:04:47', '2025-12-12 18:07:59'),
(7, 'AP-IP14P', 'iPhone 14 Pro', 'iPhone con Dynamic Island', 'iPhone 14 Pro con Dynamic Island, cámara de 48MP y pantalla siempre activa.', 2, 47, 6, '206.000', '147.5 x 71.5 x 7.9 mm', '{\"Pantalla\": \"6.1 pulgadas Super Retina XDR\", \"Procesador\": \"A16 Bionic\", \"RAM\": \"6GB\", \"Almacenamiento\": \"128GB\", \"Cámara\": \"48MP + 12MP + 12MP\", \"Batería\": \"3200mAh\"}', '[\"iphone\", \"apple\", \"ios\", \"5g\", \"dynamicisland\"]', 'activo', '2025-12-12 18:12:32', '2025-12-12 18:12:32'),
(8, 'XIA-13PRO', 'Xiaomi 13 Pro', 'Smartphone con cámara Leica', 'Xiaomi 13 Pro con colaboración Leica, pantalla AMOLED y carga súper rápida.', 5, 1, 6, '229.000', '163.2 x 74.6 x 8.4 mm', '{\"Pantalla\": \"6.73 pulgadas AMOLED\", \"Procesador\": \"Snapdragon 8 Gen 2\", \"RAM\": \"12GB\", \"Almacenamiento\": \"256GB\", \"Cámara\": \"50.3MP + 50MP + 50MP\", \"Batería\": \"4820mAh\"}', '[\"xiaomi\", \"android\", \"leica\", \"5g\"]', 'activo', '2025-12-12 18:12:32', '2025-12-12 18:12:32'),
(9, 'HW-P50PRO', 'Huawei P50 Pro', 'Smartphone con cámara dual de 50MP', 'Huawei P50 Pro con sistema de cámara XD Optics y pantalla OLED curvada.', 6, 1, 6, '195.000', '158.8 x 72.8 x 8.5 mm', '{\"Pantalla\": \"6.6 pulgadas OLED\", \"Procesador\": \"Kirin 9000\", \"RAM\": \"8GB\", \"Almacenamiento\": \"256GB\", \"Cámara\": \"50MP + 40MP + 13MP\", \"Batería\": \"4360mAh\"}', '[\"huawei\", \"android\", \"fotografia\", \"5g\"]', 'activo', '2025-12-12 18:12:32', '2025-12-12 18:12:32'),
(10, 'GO-PIX7', 'Google Pixel 7 Pro', 'Smartphone con Google Tensor G2', 'Google Pixel 7 Pro con cámara mejorada por IA y procesador Tensor G2.', 4, 1, 6, '212.000', '162.9 x 76.6 x 8.9 mm', '{\"Pantalla\": \"6.7 pulgadas LTPO OLED\", \"Procesador\": \"Google Tensor G2\", \"RAM\": \"12GB\", \"Almacenamiento\": \"128GB\", \"Cámara\": \"50MP + 12MP + 48MP\", \"Batería\": \"5000mAh\"}', '[\"google\", \"pixel\", \"android\", \"ia\"]', 'activo', '2025-12-12 18:12:32', '2025-12-12 18:12:32'),
(11, 'LP-HPENVY', 'HP Envy x360', 'Laptop convertible 2 en 1', 'HP Envy x360 con pantalla táctil, convertible y procesador AMD Ryzen.', 7, 51, 6, '1.500', '32.4 x 22.1 x 1.8 cm', '{\"Pantalla\": \"15.6 pulgadas FHD táctil\", \"Procesador\": \"AMD Ryzen 7\", \"RAM\": \"16GB\", \"Almacenamiento\": \"512GB SSD\", \"Graficos\": \"AMD Radeon\", \"Sistema\": \"Windows 11\"}', '[\"laptop\", \"convertible\", \"hp\", \"windows\"]', 'activo', '2025-12-12 18:38:52', '2025-12-12 18:38:52'),
(12, 'LP-LENYOG', 'Lenovo Yoga 9i', 'Laptop premium convertible', 'Lenovo Yoga 9i con sonido Bowers & Wilkins y construcción premium.', 8, 51, 6, '1.370', '31.9 x 21.5 x 1.5 cm', '{\"Pantalla\": \"14 pulgadas 4K OLED\", \"Procesador\": \"Intel Core i7\", \"RAM\": \"16GB\", \"Almacenamiento\": \"1TB SSD\", \"Graficos\": \"Intel Iris Xe\", \"Sistema\": \"Windows 11\"}', '[\"laptop\", \"convertible\", \"lenovo\", \"premium\"]', 'activo', '2025-12-12 18:41:14', '2025-12-12 18:41:14'),
(13, 'LP-DELLXPS', 'Dell XPS 13', 'Laptop ultraportátil', 'Dell XPS 13 con pantalla InfinityEdge y diseño compacto.', 9, 53, 6, '1.270', '29.5 x 19.9 x 1.5 cm', '{\"Pantalla\": \"13.4 pulgadas FHD+\", \"Procesador\": \"Intel Core i5\", \"RAM\": \"8GB\", \"Almacenamiento\": \"256GB SSD\", \"Graficos\": \"Intel Iris Xe\", \"Sistema\": \"Windows 11\"}', '[\"laptop\", \"ultrabook\", \"dell\", \"portatil\"]', 'activo', '2025-12-12 18:44:02', '2025-12-12 18:44:02'),
(14, 'LP-ACERSWIFT', 'Acer Swift 3', 'Laptop económica y potente', 'Acer Swift 3 con procesador AMD Ryzen y diseño delgado.', 35, 51, 6, '1.200', '32.3 x 21.8 x 1.6 cm', '{\"Pantalla\": \"14 pulgadas FHD\", \"Procesador\": \"AMD Ryzen 5\", \"RAM\": \"8GB\", \"Almacenamiento\": \"512GB SSD\", \"Graficos\": \"AMD Radeon\", \"Sistema\": \"Windows 11\"}', '[\"laptop\", \"economica\", \"acer\", \"amd\"]', 'activo', '2025-12-12 18:44:02', '2025-12-12 18:44:02'),
(15, 'LP-ASUSZEN', 'Asus ZenBook 14', 'Laptop con pantalla NanoEdge', 'Asus ZenBook 14 con bisagra ErgoLift y teclado NumberPad.', 18, 53, 6, '1.140', '31.9 x 19.9 x 1.4 cm', '{\"Pantalla\": \"14 pulgadas FHD\", \"Procesador\": \"Intel Core i7\", \"RAM\": \"16GB\", \"Almacenamiento\": \"1TB SSD\", \"Graficos\": \"Intel Iris Xe\", \"Sistema\": \"Windows 11\"}', '[\"laptop\", \"ultrabook\", \"asus\", \"delgado\"]', 'activo', '2025-12-12 18:44:02', '2025-12-12 18:44:02'),
(16, 'TB-IPADPRO', 'iPad Pro 12.9', 'Tablet profesional de Apple', 'iPad Pro con pantalla Liquid Retina XDR y chip M2.', 2, 52, 6, '682.000', '28.06 x 21.49 x 0.64 cm', '{\"Pantalla\": \"12.9 pulgadas Liquid Retina XDR\", \"Procesador\": \"Apple M2\", \"RAM\": \"8GB\", \"Almacenamiento\": \"128GB\", \"Camara\": \"12MP + 10MP\", \"Conectividad\": \"WiFi + Cellular\"}', '[\"tablet\", \"ipad\", \"apple\", \"profesional\"]', 'activo', '2025-12-12 18:58:11', '2025-12-12 18:58:11'),
(17, 'TB-SAMTABS8', 'Samsung Galaxy Tab S8', 'Tablet Android premium', 'Samsung Galaxy Tab S8 con S Pen incluido y pantalla Super AMOLED.', 1, 52, 6, '503.000', '25.3 x 16.5 x 0.6 cm', '{\"Pantalla\": \"11 pulgadas LTPS TFT\", \"Procesador\": \"Snapdragon 8 Gen 1\", \"RAM\": \"8GB\", \"Almacenamiento\": \"128GB\", \"Camara\": \"13MP + 6MP\", \"Bateria\": \"8000mAh\"}', '[\"tablet\", \"android\", \"samsung\", \"spen\"]', 'activo', '2025-12-12 18:58:11', '2025-12-12 18:58:11'),
(18, 'TB-LENOPAD', 'Lenovo Tab P12 Pro', 'Tablet para productividad', 'Lenovo Tab P12 Pro con pantalla OLED y compatibilidad con teclado y lápiz.', 8, 52, 6, '565.000', '28.5 x 18.9 x 0.6 cm', '{\"Pantalla\": \"12.6 pulgadas OLED\", \"Procesador\": \"Snapdragon 870\", \"RAM\": \"6GB\", \"Almacenamiento\": \"128GB\", \"Camara\": \"13MP + 5MP\", \"Bateria\": \"10200mAh\"}', '[\"tablet\", \"android\", \"lenovo\", \"productividad\"]', 'activo', '2025-12-12 18:58:11', '2025-12-12 18:58:11'),
(19, 'PC-HPOmen', 'HP Omen 45L', 'PC Gaming de alto rendimiento', 'HP Omen con refrigeración líquida y componentes gaming de última generación.', 7, 2, 6, '15.000', '50 x 25 x 50 cm', '{\"Procesador\": \"Intel Core i9\", \"RAM\": \"32GB\", \"Almacenamiento\": \"2TB SSD + 2TB HDD\", \"Graficos\": \"NVIDIA RTX 4080\", \"Fuente\": \"800W\", \"Sistema\": \"Windows 11\"}', '[\"pc\", \"gaming\", \"hp\", \"rendimiento\"]', 'activo', '2025-12-12 19:01:18', '2025-12-12 19:01:18'),
(20, 'PC-DELLXPS', 'Dell XPS Desktop', 'PC para creativos y profesionales', 'Dell XPS Desktop con potencia para edición de video y diseño gráfico.', 9, 2, 6, '12.500', '47 x 22 x 43 cm', '{\"Procesador\": \"Intel Core i7\", \"RAM\": \"16GB\", \"Almacenamiento\": \"1TB SSD + 2TB HDD\", \"Graficos\": \"NVIDIA RTX 3060\", \"Fuente\": \"500W\", \"Sistema\": \"Windows 11\"}', '[\"pc\", \"escritorio\", \"dell\", \"profesional\"]', 'activo', '2025-12-12 19:01:18', '2025-12-12 19:01:18'),
(21, 'PC-ASUSROG', 'Asus ROG Strix', 'PC Gaming Asus ROG', 'Asus ROG Strix con iluminación RGB y componentes seleccionados para gaming.', 18, 2, 6, '14.000', '48 x 24 x 51 cm', '{\"Procesador\": \"AMD Ryzen 9\", \"RAM\": \"32GB\", \"Almacenamiento\": \"2TB NVMe SSD\", \"Graficos\": \"AMD Radeon RX 7900 XTX\", \"Fuente\": \"1000W\", \"Sistema\": \"Windows 11\"}', '[\"pc\", \"gaming\", \"asus\", \"rog\"]', 'activo', '2025-12-12 19:01:18', '2025-12-12 19:01:18'),
(22, 'MN-SAMODYS', 'Samsung Odyssey G9', 'Monitor gaming curvo 49\"', 'Samsung Odyssey G9 con curvatura 1000R, resolución Dual QHD y tasa de refresco 240Hz.', 1, 55, 6, '16.700', '114.9 x 53.9 x 41.5 cm', '{\"Pantalla\": \"49 pulgadas QLED\", \"Resolucion\": \"5120x1440\", \"TasaRefresco\": \"240Hz\", \"TiempoRespuesta\": \"1ms\", \"Conectores\": \"2x HDMI, DisplayPort, USB\"}', '[\"monitor\", \"gaming\", \"curvo\", \"samsung\"]', 'activo', '2025-12-12 19:01:18', '2025-12-12 19:01:18'),
(23, 'MN-LGULTR', 'LG UltraGear 27\"', 'Monitor gaming 4K', 'LG UltraGear con resolución 4K, NVIDIA G-Sync y diseño sin bordes.', 4, 55, 6, '6.200', '61.4 x 36.7 x 19.2 cm', '{\"Pantalla\": \"27 pulgadas IPS\", \"Resolucion\": \"3840x2160\", \"TasaRefresco\": \"144Hz\", \"TiempoRespuesta\": \"1ms\", \"Tecnologia\": \"NVIDIA G-Sync\"}', '[\"monitor\", \"gaming\", \"4k\", \"lg\"]', 'activo', '2025-12-12 19:01:18', '2025-12-12 19:01:18'),
(24, 'MN-DELLULT', 'Dell UltraSharp 32\"', 'Monitor 4K para diseño', 'Dell UltraSharp con calibración de color precisa y USB-C con carga.', 9, 55, 6, '7.800', '71.4 x 52.1 x 21.9 cm', '{\"Pantalla\": \"32 pulgadas IPS\", \"Resolucion\": \"3840x2160\", \"Brillo\": \"400 nits\", \"Color\": \"99% sRGB\", \"Conectores\": \"USB-C, HDMI, DisplayPort\"}', '[\"monitor\", \"diseno\", \"4k\", \"dell\"]', 'activo', '2025-12-12 19:01:18', '2025-12-12 19:01:18'),
(25, 'TV-SAMQN90', 'Samsung Neo QLED 65\"', 'TV 4K con tecnología Quantum Matrix', 'Samsung Neo QLED con tecnología Mini LED, Object Tracking Sound y Gaming Hub.', 1, 55, 6, '28.500', '144.7 x 83.1 x 2.7 cm', '{\"Pantalla\": \"65 pulgadas Neo QLED\", \"Resolucion\": \"4K\", \"SmartTV\": \"Tizen\", \"HDR\": \"Quantum HDR 32x\", \"Sonido\": \"Object Tracking Sound+\"}', '[\"tv\", \"4k\", \"samsung\", \"qled\"]', 'activo', '2025-12-12 21:03:47', '2025-12-12 21:03:47'),
(26, 'TV-LGC2', 'LG OLED 55\"', 'TV OLED evo con auto-illuminación', 'LG OLED con procesador α9 Gen6, Dolby Vision IQ y Dolby Atmos.', 4, 55, 6, '18.200', '122.7 x 71.0 x 4.6 cm', '{\"Pantalla\": \"55 pulgadas OLED evo\", \"Resolucion\": \"4K\", \"SmartTV\": \"webOS\", \"Procesador\": \"α9 Gen6 AI\", \"Sonido\": \"40W Dolby Atmos\"}', '[\"tv\", \"oled\", \"lg\", \"4k\"]', 'activo', '2025-12-12 21:03:47', '2025-12-12 21:03:47'),
(27, 'TV-SONYXR', 'Sony Bravia XR 75\"', 'TV con Cognitive Processor XR', 'Sony Bravia XR con procesamiento cognitivo y tecnología Acoustic Surface Audio+.', 6, 55, 6, '38.400', '167.4 x 96.4 x 3.6 cm', '{\"Pantalla\": \"75pulgadas LED\", \"Resolucion\": \"4K\", \"SmartTV\": \"Google TV\", \"Procesador\": \"Cognitive Processor XR\", \"Sonido\": \"Acoustic Surface Audio+\"}', '[\"tv\", \"4k\", \"sony\", \"bravia\"]', 'activo', '2025-12-12 21:03:47', '2025-12-12 21:03:47'),
(28, 'AU-SONWH', 'Sony WH-1000XM5', 'Audífonos con cancelación de ruido líder', 'Sony WH-1000XM5 con cancelación de ruido mejorada y calidad de sonido excepcional.', 6, 4, 6, '250.000', '20.5 x 15.5 x 8.0 cm', '{\"Tipo\": \"Over-ear inalámbricos\", \"CancelacionRuido\": \"Sí\", \"Bateria\": \"30 horas\", \"Conectividad\": \"Bluetooth 5.2\", \"Micrófonos\": \"8 para llamadas\"}', '[\"audifonos\", \"sony\", \"cancelacion-ruido\", \"inalambricos\"]', 'activo', '2025-12-12 21:03:47', '2025-12-12 21:03:47'),
(29, 'AU-JBLFLIP', 'JBL Flip 6', 'Parlante Bluetooth portátil', 'JBL Flip 6 con sonido potente, resistencia al agua IP67 y hasta 12 horas de batería.', 41, 59, 6, '550.000', '17.8 x 7.2 x 7.2 cm', '{\"Potencia\": \"30W\", \"Bateria\": \"12 horas\", \"Resistencia\": \"IP67 (agua/polvo)\", \"Conectividad\": \"Bluetooth 5.1\", \"Colores\": \"Variados\"}', '[\"parlante\", \"bluetooth\", \"jbl\", \"portatil\"]', 'activo', '2025-12-12 21:03:47', '2025-12-12 21:03:47'),
(30, 'AU-BOSESOUN', 'Bose SoundLink Revolve', 'Parlante Bluetooth 360°', 'Bose SoundLink Revolve con sonido omnidireccional y resistencia al agua.', 42, 59, 6, '680.000', '15.2 x 8.3 x 8.3 cm', '{\"Sonido\": \"360°\", \"Bateria\": \"12 horas\", \"Resistencia\": \"IPX4\", \"Conectividad\": \"Bluetooth, NFC\", \"Funciones\": \"Asistente de voz\"}', '[\"parlante\", \"bose\", \"360grados\", \"bluetooth\"]', 'activo', '2025-12-12 21:03:47', '2025-12-12 21:03:47'),
(31, 'CAM-CANONEOS', 'Canon EOS R6', 'Cámara mirrorless full-frame', 'Canon EOS R6 con estabilización 8-stop, disparo en ráfaga y video 4K.', 47, 5, 6, '680.000', '13.8 x 9.8 x 8.8 cm', '{\"Sensor\": \"Full-frame 20.1MP\", \"Estabilizacion\": \"8-stop IBIS\", \"Video\": \"4K 60fps\", \"ISO\": \"100-102400\", \"Pantalla\": \"LCD táctil articulada\"}', '[\"camara\", \"canon\", \"mirrorless\", \"fullframe\"]', 'activo', '2025-12-12 21:03:47', '2025-12-12 21:03:47'),
(32, 'CAM-NIKONZ', 'Nikon Z7 II', 'Cámara mirrorless profesional', 'Nikon Z7 II con doble procesador y alta resolución para fotografía profesional.', 48, 5, 6, '705.000', '13.4 x 10.1 x 7.6 cm', '{\"Sensor\": \"Full-frame 45.7MP\", \"Procesador\": \"Dual EXPEED 6\", \"Video\": \"4K 60fps\", \"ISO\": \"64-25600\", \"Estabilizacion\": \"5-axis IBIS\"}', '[\"camara\", \"nikon\", \"mirrorless\", \"profesional\"]', 'activo', '2025-12-12 21:03:47', '2025-12-12 21:03:47'),
(33, 'CAM-GOPRO12', 'GoPro Hero 12 Black', 'Cámara deportiva 5.3K', 'GoPro Hero 12 Black con HyperSmooth 6.0, video 5.3K y resistencia extrema.', 38, 63, 6, '154.000', '7.1 x 5.0 x 3.4 cm', '{\"Video\": \"5.3K 60fps\", \"Estabilizacion\": \"HyperSmooth 6.0\", \"Resistencia\": \"10m sin carcasa\", \"Bateria\": \"Hasta 2.5 horas\", \"Pantalla\": \"LCD táctil trasero\"}', '[\"gopro\", \"camara-deportiva\", \"video\", \"accion\"]', 'activo', '2025-12-12 21:03:47', '2025-12-12 21:03:47'),
(34, 'BR-TALADRO', 'Taladro Percutor Inalámbrico', 'Taladro de 18V con 2 baterías', 'Taladro percutor inalámbrico para múltiples materiales.', 78, 78, 6, '2.200', '35.0 x 25.0 x 10.0 cm', '{\"Voltaje\": \"18V\", \"Velocidad\": \"0-1800 rpm\", \"Par\": \"50Nm\", \"Incluye\": \"2 baterías, cargador\", \"Uso\": \"Madera, metal, mampostería\"}', '[\"taladro\", \"inalambrico\", \"herramienta\", \"bricolaje\"]', 'activo', '2025-12-12 21:27:25', '2025-12-12 21:27:25'),
(35, 'BR-DESTORN', 'Juego de Destornilladores', 'Set de destornilladores profesionales', 'Juego de 6 destornilladores con mangos antideslizantes.', 18, 18, 8, '0.500', '20.0 x 15.0 x 3.0 cm', '{\"Puntas\": \"Planas y Phillips\", \"Cantidad\": \"6 piezas\", \"Material\": \"Acero cromo-vanadio\", \"Mango\": \"Antideslizante\", \"Tamano\": \"Variado\"}', '[\"destornilladores\", \"herramientas\", \"bricolaje\", \"reparacion\"]', 'activo', '2025-12-12 21:27:25', '2025-12-12 21:27:25'),
(36, 'BR-CINTA', 'Cinta Métrica 8m', 'Cinta métrica de acero', 'Cinta métrica de acero con bloqueo automático.', NULL, 80, 6, '0.300', '8.0 x 8.0 x 3.0 cm', '{\"Longitud\": \"8m\", \"Ancho\": \"25mm\", \"Material\": \"Acero\", \"Sistema\": \"Metrico/imperial\", \"Caracteristica\": \"Bloqueo automático\"}', '[\"cinta\", \"metrica\", \"medicion\", \"construccion\"]', 'activo', '2025-12-12 21:27:25', '2025-12-12 21:27:25'),
(37, 'AU-MOTULO', 'Aceite de Motor 5W-30 4L', 'Aceite sintético para motor', 'Aceite sintético de alta calidad para motores modernos.', 19, 81, 4, '3.800', '20.0 x 10.0 x 30.0 cm', '{\"Viscosidad\": \"5W-30\", \"Volumen\": \"4L\", \"Tipo\": \"Sintético\", \"API\": \"SN\", \"Vehiculos\": \"Gasolina/diésel\"}', '[\"aceite\", \"motor\", \"automovil\", \"mantenimiento\"]', 'activo', '2025-12-12 21:29:59', '2025-12-12 21:29:59'),
(38, 'AU-LIMPIA', 'Limpiador de Parabrisas 2L', 'Líquido limpiador parabrisas', 'Líquido concentrado para limpiar parabrisas.', 83, 83, 4, '2.100', '10.0 x 10.0 x 20.0 cm', '{\"Volumen\": \"2L\", \"Tipo\": \"Concentrado\", \"Temperatura\": \"Hasta -20°C\", \"Dilucion\": \"1:1 con agua\", \"Efecto\": \"Anticongelante\"}', '[\"limpiador\", \"parabrisas\", \"auto\", \"mantenimiento\"]', 'activo', '2025-12-12 21:29:59', '2025-12-12 21:29:59'),
(39, 'AU-CABLEA', 'Cables para Arranque', 'Cables para arranque de emergencia', 'Cables de 4m para arranque de emergencia.', NULL, 17, 6, '0.800', '25.0 x 15.0 x 5.0 cm', '{\"Longitud\": \"4m\", \"Calibre\": \"8 AWG\", \"Pinzas\": \"Aisladas\", \"Amperaje\": \"400A\", \"Uso\": \"Emergencia\"}', '[\"cables\", \"arranque\", \"auto\", \"emergencia\"]', 'activo', '2025-12-12 21:29:59', '2025-12-12 21:29:59'),
(40, 'OP-BICBOL', 'Bic Bolígrafos Azul Pack 10', 'Bolígrafos de tinta azul', 'Bic bolígrafos Cristal de punta media.', NULL, 7, 8, '0.050', '15.0 x 10.0 x 2.0 cm', '{\"Color\": \"Azul\", \"Punta\": \"1.0mm\", \"Cantidad\": \"10 unidades\", \"Tinta\": \"No recargable\", \"Escritura\": \"Suave\"}', '[\"boligrafos\", \"bic\", \"oficina\", \"escritura\"]', 'activo', '2025-12-12 21:29:59', '2025-12-12 21:29:59'),
(41, 'OP-PAPELA', 'Papel A4 500 Hojas', 'Papel multifunción A4', 'Papel A4 de 75g para impresión y copia.', NULL, 8, 8, '2.500', '30.0 x 21.0 x 5.0 cm', '{\"Tamano\": \"A4\", \"Gramaje\": \"75g/m²\", \"Cantidad\": \"500 hojas\", \"Color\": \"Blanco\", \"Uso\": \"Impresión, copia\"}', '[\"papel\", \"a4\", \"oficina\", \"impresion\"]', 'activo', '2025-12-12 21:29:59', '2025-12-12 21:29:59'),
(42, 'OP-CALCSC', 'Calculadora Científica Casio', 'Calculadora científica 240 funciones', 'Casio calculadora científica con pantalla de 2 líneas.', 86, 72, 6, '0.200', '15.5 x 7.8 x 1.2 cm', '{\"Funciones\": \"240\", \"Pantalla\": \"2 líneas\", \"Alimentacion\": \"Solar + batería\", \"Tipo\": \"Científica\", \"Escuela\": \"Secundaria/universidad\"}', '[\"calculadora\", \"cientifica\", \"casio\", \"estudio\"]', 'activo', '2025-12-12 21:29:59', '2025-12-12 21:29:59'),
(43, 'MS-PURINAC', 'Purina Alimento para Gato Adulto', 'Alimento balanceado para gatos', 'Purina alimento completo para gatos adultos.', NULL, 12, 2, '3.000', '30.0 x 20.0 x 10.0 cm', '{\"Tipo\": \"Seco\", \"Sabor\": \"Pollo\", \"Peso\": \"3kg\", \"Edad\": \"Gato adulto\", \"Beneficios\": \"Pelo, digestión\"}', '[\"alimento\", \"gato\", \"purina\", \"mascota\"]', 'activo', '2025-12-12 22:25:55', '2025-12-12 22:25:55'),
(44, 'MS-PELOTAJ', 'Pelota de Juguete para Perro', 'Pelota resistente para perros', 'Pelota de goma resistente para juegos con perro.', NULL, 13, 6, '0.150', '8.0 cm diámetro', '{\"Material\": \"Goma resistente\", \"Tamano\": \"Mediano\", \"Flota\": \"Sí\", \"Durabilidad\": \"Alta\", \"Lavable\": \"Sí\"}', '[\"juguete\", \"perro\", \"pelota\", \"mascota\"]', 'activo', '2025-12-12 22:25:55', '2025-12-12 22:25:55'),
(45, 'MS-CORREAP', 'Correa Retráctil para Perro', 'Correa retráctil 5m', 'Correa retráctil con mecanismo suave y agarre cómodo.', NULL, 13, 6, '0.400', '12.0 x 8.0 x 5.0 cm', '{\"Longitud\": \"5m\", \"Mecanismo\": \"Retráctil\", \"Material\": \"Nylon\", \"Gancho\": \"Acero\", \"PesoMax\": \"50kg\"}', '[\"correa\", \"perro\", \"retractil\", \"paseo\"]', 'activo', '2025-12-12 22:25:55', '2025-12-12 22:25:55'),
(46, 'PB-PAMPERS', 'Pampers Pañales Talla 3', 'Pañales desechables ultra absorbentes', 'Pampers pañales con protección extra y indicador de humedad.', 52, 31, 8, '2.800', '30.0 x 20.0 x 10.0 cm', '{\"Talla\": \"3 (5-9 kg)\", \"Cantidad\": \"44 pañales\", \"Absorcion\": \"Ultra absorbente\", \"Indicador\": \"De humedad\", \"Piel\": \"Hipoalergénico\"}', '[\"panales\", \"pampers\", \"bebe\", \"higiene\"]', 'activo', '2025-12-12 22:25:55', '2025-12-12 22:25:55'),
(47, 'PB-GERBERC', 'Gerber Cereal de Arroz', 'Cereal infantil de arroz', 'Gerber cereal de arroz fortificado con hierro.', 54, 205, 1, '0.300', '10.0 x 10.0 x 15.0 cm', '{\"Sabor\": \"Arroz\", \"Edad\": \"4+ meses\", \"Fortificado\": \"Hierro\", \"Preparacion\": \"Con leche\", \"Sin\": \"Gluten, lactosa\"}', '[\"cereal\", \"bebe\", \"gerber\", \"alimento\"]', 'activo', '2025-12-12 22:25:55', '2025-12-12 22:25:55'),
(48, 'PB-JUGSENT', 'Juguetes Sensoriales Bebé', 'Set de juguetes sensoriales', 'Set de juguetes para desarrollo sensorial del bebé.', 52, 27, 8, '0.500', '20.0 x 15.0 x 10.0 cm', '{\"Edad\": \"0-12 meses\", \"Material\": \"BPA-free\", \"Estimula\": \"Vista, tacto, oído\", \"Incluye\": \"5 juguetes\", \"Lavable\": \"Sí\"}', '[\"juguetes\", \"bebe\", \"sensorial\", \"educativo\"]', 'activo', '2025-12-12 22:25:55', '2025-12-12 22:25:55'),
(49, 'SB-VITCMUL', 'Vitamina C 1000mg', 'Suplemento de vitamina C', 'Vitamina C en tabletas de alta potencia.', NULL, 32, 6, '0.100', '5.0 x 5.0 x 10.0 cm', '{\"Dosis\": \"1000mg\", \"Presentacion\": \"Tabletas\", \"Cantidad\": \"100 tabletas\", \"Uso\": \"Diario\", \"Beneficios\": \"Sistema inmune\"}', '[\"vitamina-c\", \"suplemento\", \"salud\", \"inmunidad\"]', 'activo', '2025-12-12 22:25:55', '2025-12-12 22:25:55'),
(50, 'SB-TERMOM', 'Termómetro Digital Infrarrojo', 'Termómetro sin contacto', 'Termómetro digital infrarrojo para frente y oído.', 27, 33, 6, '0.150', '14.0 x 4.0 x 4.0 cm', '{\"Tipo\": \"Infrarrojo\", \"Contacto\": \"Sin contacto\", \"Medicion\": \"Frente/oído\", \"Pantalla\": \"LCD\", \"Memoria\": \"Última medición\"}', '[\"termometro\", \"digital\", \"infrarrojo\", \"salud\"]', 'activo', '2025-12-12 22:25:55', '2025-12-12 22:25:55'),
(51, 'SB-BANDEL', 'Bandas Elásticas de Resistencia', 'Set de bandas de ejercicio', 'Set de 5 bandas elásticas para entrenamiento.', NULL, 103, 6, '0.300', '20.0 x 15.0 x 3.0 cm', '{\"Resistencias\": \"5 niveles\", \"Material\": \"Latex\", \"Incluye\": \"5 bandas, bolsa\", \"Uso\": \"Fitness, rehab\", \"Color\": \"Variado\"}', '[\"bandas\", \"elasticas\", \"ejercicio\", \"fitness\"]', 'activo', '2025-12-12 22:25:55', '2025-12-12 22:25:55'),
(52, 'MR-NIKTSHI', 'Nike T-shirt Dri-FIT', 'Playera deportiva de secado rápido', 'Nike playera Dri-FIT con tecnología de absorción de humedad y diseño moderno.', 3, 37, 6, '0.180', 'Varía según talla', '{\"Material\": \"Poliester\", \"Tecnologia\": \"Dri-FIT\", \"Tallas\": \"S, M, L, XL\", \"Colores\": \"Variados\", \"Lavado\": \"Máquina\"}', '[\"playera\", \"nike\", \"deportiva\", \"drift\"]', 'activo', '2025-12-12 22:28:51', '2025-12-12 22:28:51'),
(53, 'MR-ADITRA', 'Adidas Pantalón de Entrenamiento', 'Pantalón deportivo cómodo', 'Adidas pantalón de entrenamiento con cintura elástica y bolsillos.', 4, 37, 6, '0.350', 'Varía según talla', '{\"Material\": \"Mezcla\", \"Tallas\": \"S, M, L, XL\", \"Cintura\": \"Elástica\", \"Bolsillos\": \"Sí\", \"Actividad\": \"Entrenamiento\"}', '[\"pantalon\", \"adidas\", \"deportivo\", \"entrenamiento\"]', 'activo', '2025-12-12 22:28:51', '2025-12-12 22:28:51'),
(54, 'MR-LEV501', 'Levi\'s Jeans 501 Original', 'Jeans clásicos corte recto', 'Levi\'s jeans 501 con corte recto, botones de latón y diseño timeless.', 77, 179, 6, '0.600', 'Varía según talla', '{\"Material\": \"Denim\", \"Corte\": \"Recto\", \"Tallas\": \"28-42\", \"Lavados\": \"Variados\", \"Caracteristica\": \"Botones de latón\"}', '[\"jeans\", \"levis\", \"501\", \"denim\"]', 'activo', '2025-12-12 22:28:51', '2025-12-12 22:28:51'),
(55, 'CZ-NIKAIRM', 'Nike Air Max 270', 'Zapatillas con unidad Air Max', 'Nike Air Max 270 con la unidad Air más grande y diseño moderno.', 3, 102, 6, '0.850', 'Varía según talla', '{\"Material\": \"Malla y sintético\", \"Suela\": \"Air Max\", \"Tallas\": \"US 6-13\", \"Colores\": \"Variados\", \"Uso\": \"Casual\"}', '[\"zapatillas\", \"nike\", \"airmax\", \"casual\"]', 'activo', '2025-12-12 22:28:51', '2025-12-12 22:28:51'),
(56, 'CZ-ADSUPER', 'Adidas Superstar', 'Zapatillas clásicas con caparazón', 'Adidas Superstar con puntera de caparazón y diseño icónico.', 4, 102, 6, '0.800', 'Varía según talla', '{\"Material\": \"Cuero\", \"Puntera\": \"Caparazón\", \"Tallas\": \"US 5-12\", \"Colores\": \"Blanco/Negro\", \"Estilo\": \"Clásico\"}', '[\"zapatillas\", \"adidas\", \"superstar\", \"clasicas\"]', 'activo', '2025-12-12 22:28:51', '2025-12-12 22:28:51'),
(57, 'CZ-CONCHUC', 'Converse Chuck Taylor All Star', 'Zapatillas de lona clásicas', 'Converse Chuck Taylor All Star con diseño original y suela de goma.', 83, 102, 6, '0.700', 'Varía según talla', '{\"Material\": \"Lona\", \"Diseño\": \"Original\", \"Tallas\": \"US 4-12\", \"Colores\": \"Variados\", \"Estilo\": \"Unisex\"}', '[\"converse\", \"chucktaylor\", \"zapatillas\", \"clasicas\"]', 'activo', '2025-12-12 22:28:51', '2025-12-12 22:28:51'),
(58, 'DF-BOWFLEX', 'Bowflex Pesas SelectTech', 'Pesas ajustables de 5 a 52.5 lb', 'Bowflex pesas ajustables que reemplazan 15 juegos de pesas en uno.', 3, 104, 6, '18.000', '43.0 x 30.0 x 30.0 cm', '{\"Rango\": \"5-52.5 lb (2.5-24 kg)\", \"Ajuste\": \"Dial selector\", \"Ejercicios\": \"Múltiples\", \"Garantia\": \"5 años\", \"Espacio\": \"Compacto\"}', '[\"pesas\", \"bowflex\", \"ajustables\", \"fitness\"]', 'activo', '2025-12-12 22:28:51', '2025-12-12 22:28:51'),
(59, 'DF-FITBITC', 'Fitbit Charge 5', 'Tracker de actividad avanzado', 'Fitbit Charge 5 con ECG, monitoreo de estrés y pantalla color.', 39, 103, 6, '0.028', '3.7 x 1.5 x 1.2 cm', '{\"Pantalla\": \"AMOLED color\", \"Bateria\": \"7 días\", \"Sensores\": \"ECG, EDA, SpO2\", \"Resistencia\": \"50m agua\", \"GPS\": \"Integrado\"}', '[\"fitbit\", \"tracker\", \"smartwatch\", \"fitness\"]', 'activo', '2025-12-12 22:28:51', '2025-12-12 22:28:51'),
(60, 'DF-YOGAMAT', 'Mat de Yoga Extra Grueso', 'Mat de yoga antideslizante 15mm', 'Mat de yoga con superficie antideslizante y espesor extra para mayor comodidad.', 3, 103, 6, '2.500', '183.0 x 61.0 x 1.5 cm', '{\"Material\": \"TPE\", \"Espesor\": \"15mm\", \"Superficie\": \"Antideslizante\", \"Color\": \"Morado\", \"Portabilidad\": \"Ligero\"}', '[\"yoga\", \"mat\", \"fitness\", \"ejercicio\"]', 'activo', '2025-12-12 22:28:51', '2025-12-12 22:28:51'),
(61, 'BC-LOREAL', 'L\'Oréal Paris Base True Match', 'Base de maquillaje líquida', 'L\'Oréal base True Match con SPF y acabado natural.', 64, 43, 5, '0.030', '4.5 x 4.5 x 11.0 cm', '{\"Tipo\": \"Líquida\", \"SPF\": \"17\", \"Tonos\": \"30 disponibles\", \"Acabado\": \"Natural\", \"Duración\": \"Hasta 24h\"}', '[\"base\", \"maquillaje\", \"loreal\", \"true-match\"]', 'activo', '2025-12-12 22:28:51', '2025-12-12 22:28:51'),
(62, 'BC-MAYBEL', 'Maybelline Mascara Sky High', 'Máscara de pestañas alargadora', 'Maybelline máscara Sky High con cepillo telescópico y fórmula lavable.', 65, 43, 5, '0.015', '2.0 x 2.0 x 12.0 cm', '{\"Efecto\": \"Alargar\", \"Cepillo\": \"Telescópico\", \"Formula\": \"Lavable\", \"Color\": \"Negro\", \"Hipoalergenica\": \"Sí\"}', '[\"mascara\", \"pestanas\", \"maybelline\", \"maquillaje\"]', 'activo', '2025-12-12 22:28:51', '2025-12-12 22:28:51'),
(63, 'BC-NIVEAU', 'Nivea Crema Hidratante', 'Crema hidratante corporal', 'Nivea crema hidratante con aceite de almendras para piel seca.', 67, 42, 5, '0.400', '8.0 x 8.0 x 10.0 cm', '{\"Tipo\": \"Corporal\", \"Ingredientes\": \"Aceite de almendras\", \"Piel\": \"Seca\", \"Fragancia\": \"Clásica\", \"Efecto\": \"24h hidratación\"}', '[\"crema\", \"hidratante\", \"nivea\", \"cuerpo\"]', 'activo', '2025-12-12 22:28:51', '2025-12-12 22:28:51'),
(64, 'AB-NESCAFE', 'Nescafé Clásico 200g', 'Café instantáneo premium', 'Nescafé café instantáneo de calidad premium.', 63, 94, 1, '0.200', '8.0 x 8.0 x 15.0 cm', '{\"Tipo\": \"Instantáneo\", \"Peso\": \"200g\", \"Intensidad\": \"Media\", \"Origen\": \"Mezcla\", \"Preparacion\": \"Agua caliente\"}', '[\"cafe\", \"nescafe\", \"instantaneo\", \"bebida\"]', 'activo', '2025-12-12 22:28:51', '2025-12-12 22:28:51'),
(65, 'AB-COCACOL', 'Coca-Cola 2L', 'Refresco de cola clásico', 'Coca-Cola refresco de cola en botella de 2 litros.', 56, 26, 4, '2.100', '10.0 x 10.0 x 30.0 cm', '{\"Sabor\": \"Cola\", \"Volumen\": \"2L\", \"Azucar\": \"Regular\", \"Temperatura\": \"Refrigerar\", \"Caducidad\": \"6 meses\"}', '[\"coca-cola\", \"refresco\", \"cola\", \"bebida\"]', 'activo', '2025-12-12 22:28:51', '2025-12-12 22:28:51'),
(66, 'AB-KNORRSO', 'Knorr Sopa de Pollo', 'Sopa instantánea de pollo', 'Knorr sopa instantánea de pollo con fideos.', 61, 92, 1, '0.080', '5.0 x 5.0 x 10.0 cm', '{\"Sabor\": \"Pollo\", \"Tipo\": \"Instantánea\", \"Peso\": \"80g\", \"Preparacion\": \"5 minutos\", \"Porciones\": \"2\"}', '[\"sopa\", \"knorr\", \"instantanea\", \"comida\"]', 'activo', '2025-12-12 22:28:51', '2025-12-12 22:28:51'),
(67, 'EL-LGREFR', 'LG Refrigerador InstaView', 'Refrigerador con puerta InstaView', 'LG Refrigerador con puerta de vidrio táctil, dispensador de agua y tecnología Linear Cooling.', 4, 109, 6, '98.000', '91.2 x 71.8 x 178.9 cm', '{\"Capacidad\": \"23 pies cúbicos\", \"Tecnologia\": \"Linear Cooling\", \"Caracteristicas\": \"InstaView, Dispensador\", \"Eficiencia\": \"Energy Star\", \"Garantia\": \"10 años compresor\"}', '[\"refrigerador\", \"lg\", \"instaview\", \"eficiente\"]', 'activo', '2025-12-12 22:32:39', '2025-12-12 22:32:39'),
(68, 'EL-SAMLAVE', 'Samsung Lavadora AddWash', 'Lavadora con AddWash door', 'Samsung Lavadora con tecnología EcoBubble, AddWash door y aplicación SmartThings.', 1, 110, 6, '72.000', '60.0 x 60.0 x 85.0 cm', '{\"Capacidad\": \"19kg\", \"Tecnologia\": \"EcoBubble\", \"Velocidad\": \"1400 rpm\", \"Eficiencia\": \"A+++\", \"Caracteristicas\": \"AddWash, Digital Inverter\"}', '[\"lavadora\", \"samsung\", \"addwash\", \"ecobubble\"]', 'activo', '2025-12-12 22:32:39', '2025-12-12 22:32:39'),
(69, 'EL-PHILAI', 'Philips Aire Acondicionado', 'Aire acondicionado inverter', 'Philips aire acondicionado con tecnología inverter, filtro PureAir y modo silencioso.', 27, 109, 6, '28.000', '78.0 x 27.5 x 19.8 cm', '{\"Capacidad\": \"12000 BTU\", \"Tecnologia\": \"Inverter\", \"Eficiencia\": \"A++\", \"Filtro\": \"PureAir\", \"Modos\": \"Cool, Dry, Fan, Auto\"}', '[\"aire-acondicionado\", \"philips\", \"inverter\", \"eficiente\"]', 'activo', '2025-12-12 22:32:39', '2025-12-12 22:32:39'),
(70, 'HJ-IKEASOF', 'IKEA Sillón Ektorp', 'Sillón cómodo y elegante', 'IKEA sillón Ektorp con funda desenfundable y estructura de madera maciza.', 5, 193, 6, '45.000', '95.0 x 88.0 x 83.0 cm', '{\"Material\": \"Tela\", \"Color\": \"Variados\", \"Estructura\": \"Madera maciza\", \"Funda\": \"Desenfundable\", \"Montaje\": \"Requiere montaje\"}', '[\"sillon\", \"ikea\", \"hogar\", \"mueble\"]', 'activo', '2025-12-12 22:32:39', '2025-12-12 22:32:39'),
(71, 'HJ-PHILLUZ', 'Philips Luz LED Inteligente', 'Bombillo LED inteligente', 'Philips bombillo LED inteligente con control por app y compatibilidad con asistentes de voz.', 27, 74, 6, '0.200', '6.0 x 6.0 x 11.0 cm', '{\"Potencia\": \"9W (equiv. 60W)\", \"Color\": \"RGB + blanco\", \"Conectividad\": \"WiFi\", \"Compatibilidad\": \"Alexa, Google Home\", \"VidaUtil\": \"25000 horas\"}', '[\"bombillo\", \"led\", \"inteligente\", \"philips\"]', 'activo', '2025-12-12 22:32:39', '2025-12-12 22:32:39'),
(72, 'HJ-BRAUNCAF', 'Braun Cafetera Multibebida', 'Cafetera para múltiples bebidas', 'Braun cafetera con sistema de cápsulas multibebida y espumador de leche automático.', 28, 175, 6, '4.800', '31.0 x 24.0 x 35.0 cm', '{\"Tipo\": \"Cápsulas\", \"Bebidas\": \"Café, té, chocolate\", \"Deposito\": \"1.2L\", \"Potencia\": \"1450W\", \"Funciones\": \"Espumador automático\"}', '[\"cafetera\", \"braun\", \"capsulas\", \"multibebida\"]', 'activo', '2025-12-12 22:32:39', '2025-12-12 22:32:39'),
(73, 'VG-PS5DISC', 'PlayStation 5', 'Consola de videojuegos de última generación', 'PlayStation 5 con lector de discos 4K UHD Blu-ray y SSD ultra rápido.', 48, 6, 6, '4.500', '39.0 x 10.4 x 26.0 cm', '{\"Procesador\": \"AMD Ryzen Zen 2\", \"Graficos\": \"AMD RDNA 2\", \"Almacenamiento\": \"825GB SSD\", \"Resolucion\": \"8K\", \"Retrocompatibilidad\": \"PS4\"}', '[\"playstation\", \"ps5\", \"consola\", \"gaming\"]', 'activo', '2025-12-13 02:00:05', '2025-12-13 02:00:05'),
(74, 'VG-XBOXSX', 'Xbox Series X', 'Consola más potente de Xbox', 'Xbox Series X con Quick Resume, ray tracing y Game Pass.', 47, 6, 6, '4.450', '15.1 x 15.1 x 30.1 cm', '{\"Procesador\": \"AMD Zen 2\", \"Graficos\": \"AMD RDNA 2\", \"Almacenamiento\": \"1TB SSD\", \"Resolucion\": \"8K\", \"TasaRefresco\": \"120fps\"}', '[\"xbox\", \"seriesx\", \"consola\", \"gaming\"]', 'activo', '2025-12-13 02:00:05', '2025-12-13 02:00:05'),
(75, 'VG-NINSWIT', 'Nintendo Switch OLED', 'Consola híbrida con pantalla OLED', 'Nintendo Switch OLED con pantalla OLED de 7\", audio mejorado y base ajustable.', 46, 6, 6, '420.000', '24.2 x 10.2 x 1.4 cm', '{\"Pantalla\": \"7 pulgadas OLED\", \"Modos\": \"TV, mesa, portátil\", \"Almacenamiento\": \"64GB\", \"Bateria\": \"4.5-9 horas\", \"Joy-Con\": \"Incluidos\"}', '[\"nintendo\", \"switch\", \"consola\", \"oled\"]', 'activo', '2025-12-13 02:00:05', '2025-12-13 02:00:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_imagen`
--

CREATE TABLE `producto_imagen` (
  `id` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `imagen_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `orden` int(11) DEFAULT 0,
  `es_principal` tinyint(1) DEFAULT 0,
  `alt_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('activa','inactiva') COLLATE utf8mb4_unicode_ci DEFAULT 'activa',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `producto_imagen`
--

INSERT INTO `producto_imagen` (`id`, `id_producto`, `imagen_url`, `orden`, `es_principal`, `alt_text`, `estado`, `fecha_creacion`) VALUES
(1, 1, 'samsung_s21_ultra_1.webp', 1, 1, 'Samsung Galaxy S21 Ultra frontal', 'activa', '2025-11-17 02:14:15'),
(2, 1, 'samsung_s21_ultra_2.webp', 2, 0, 'Samsung Galaxy S21 Ultra trasera', 'activa', '2025-11-17 02:14:15'),
(3, 2, '41uSlcid69L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 1, 'iPhone 13 Pro frontal', 'activa', '2025-11-17 02:14:15'),
(4, 2, '51k7+0Qu4dL._AC_UF894,1000_QL80_FMwebp_.webp', 2, 0, 'iPhone 13 Pro trasera', 'activa', '2025-11-17 02:14:15'),
(5, 3, 'AIR+FORCE+1+\'07 (8).png', 1, 1, 'Nike Air Force 1 lateral', 'activa', '2025-11-17 02:14:15'),
(6, 4, '41bYVfHbHHL._AC_UY900_.jpg', 1, 1, 'Adidas Ultraboost 22 lateral', 'activa', '2025-11-17 02:14:15'),
(7, 5, '61oqO1AMbdL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 1, 'Sony WH-1000XM4 frontal', 'activa', '2025-11-17 02:14:15'),
(15, 3, 'AIR+FORCE+1+\'07 (2).png', 2, 0, 'Nike Air Force 1 trasera', 'activa', '2025-11-22 00:21:28'),
(16, 3, 'AIR+FORCE+1+\'07 (4).png', 2, 0, 'Nike Air Force 1 frontal', 'activa', '2025-11-22 00:21:28'),
(17, 3, 'AIR+FORCE+1+\'07 (5).png', 2, 0, 'Nike Air Force 1 trasera doble', 'activa', '2025-11-22 00:21:28'),
(18, 3, 'AIR+FORCE+1+\'07 (6).png', 2, 0, 'Nike Air Force 1 lateral doble', 'activa', '2025-11-22 00:21:28'),
(19, 3, 'AIR+FORCE+1+\'07 (7).png', 2, 0, 'Nike Air Force 1 superior doble', 'activa', '2025-11-22 00:21:28'),
(20, 3, 'AIR+FORCE+1+\'07 (9).png', 2, 0, 'Nike Air Force 1 suela', 'activa', '2025-11-22 00:21:28'),
(21, 3, 'AIR+FORCE+1+\'07.png', 2, 0, 'Nike Air Force 1 lateral', 'activa', '2025-11-22 00:21:28'),
(22, 4, '51srsZmQQmL._AC_UY900_.jpg', 2, 0, 'Adidas Ultraboost 22', 'activa', '2025-11-22 01:00:57'),
(23, 4, '51XkCo5XgmL._AC_UY300_.jpg', 2, 0, 'Adidas Ultraboost 22', 'activa', '2025-11-22 01:00:57'),
(24, 4, '51XTmoRm-uL._AC_UY900_.jpg', 2, 0, 'Adidas Ultraboost 22', 'activa', '2025-11-22 01:00:57'),
(25, 4, '51xzEWBlKaL._AC_UY900_.jpg', 2, 0, 'Adidas Ultraboost 22', 'activa', '2025-11-22 01:00:57'),
(26, 4, '51a22Bc0IwL._AC_UY900_.jpg', 2, 0, 'Adidas Ultraboost 22', 'activa', '2025-11-22 01:00:57'),
(27, 4, '61aXSkFNTUL._AC_UY900_.jpg', 2, 0, 'Adidas Ultraboost 22', 'activa', '2025-11-22 01:00:57'),
(28, 4, '419w0MU3-CL._AC_UY900_.jpg', 2, 0, 'Adidas Ultraboost 22', 'activa', '2025-11-22 01:00:57'),
(29, 4, '517cTuf-wML._AC_UY900_.jpg', 2, 0, 'Adidas Ultraboost 22', 'activa', '2025-11-22 01:00:57'),
(30, 1, '61ibJ-k3KLL._AC_UF894,1000_QL80_FMwebp_.webp', 2, 0, 'Samsung Galaxy S21 Ultra', 'activa', '2025-11-22 01:15:46'),
(31, 1, '61MBsOS2-JL._AC_UF350,350_QL80_FMwebp_.webp', 2, 0, 'Samsung Galaxy S21 Ultra', 'activa', '2025-11-22 01:15:46'),
(32, 1, '61WOKIOt7BL._AC_UF894,1000_QL80_FMwebp_.webp', 2, 0, 'Samsung Galaxy S21 Ultra', 'activa', '2025-11-22 01:15:46'),
(33, 1, '91-0I2UfEvL._AC_UF350,350_QL80_FMwebp_.webp', 2, 0, 'Samsung Galaxy S21 Ultra', 'activa', '2025-11-22 01:15:46'),
(34, 1, '613OyzgH2wL._AC_UF350,350_QL80_FMwebp_.webp', 2, 0, 'Samsung Galaxy S21 Ultra', 'activa', '2025-11-22 01:15:46'),
(35, 1, '615oPIkWRSL._AC_UF894,1000_QL80_FMwebp_.webp', 2, 0, 'Samsung Galaxy S21 Ultra', 'activa', '2025-11-22 01:15:46'),
(36, 1, '914zxACFLPL._AC_UF350,350_QL80_FMwebp_.webp', 2, 0, 'Samsung Galaxy S21 Ultra', 'activa', '2025-11-22 01:15:46'),
(37, 2, '41Sg9vY82IL._AC_UF350,350_QL80_FMwebp_.webp', 2, 0, 'iPhone 13 Pro', 'activa', '2025-11-22 01:15:46'),
(38, 2, '51uxaMVrOnL._AC_UF350,350_QL80_FMwebp_.webp', 2, 0, 'iPhone 13 Pro', 'activa', '2025-11-22 01:15:46'),
(39, 2, '51uxaMVrOnL._AC_UF894,1000_QL80_FMwebp_.webp', 2, 0, 'iPhone 13 Pro', 'activa', '2025-11-22 01:15:46'),
(40, 5, '71joBKOGFBL._AC_UF894,1000_QL80_FMwebp_.webp', 2, 0, 'Sony WH-1000XM4', 'activa', '2025-11-22 01:15:46'),
(41, 5, '71R4AzlTi+L._AC_UF894,1000_QL80_FMwebp_.webp', 2, 0, 'Sony WH-1000XM4', 'activa', '2025-11-22 01:15:46'),
(42, 5, '71rjNiSyEDL._AC_UF894,1000_QL80_FMwebp_.webp', 2, 0, 'Sony WH-1000XM4', 'activa', '2025-11-22 01:15:46'),
(43, 5, '71UnE5AArZL._AC_UF894,1000_QL80_FMwebp_.webp', 2, 0, 'Sony WH-1000XM4', 'activa', '2025-11-22 01:15:46'),
(44, 5, '71x4KOsgDiL._AC_UF894,1000_QL80_FMwebp_.webp', 2, 0, 'Sony WH-1000XM4', 'activa', '2025-11-22 01:15:46'),
(45, 5, '71xsREqXS+L._AC_UF894,1000_QL80_FMwebp_.webp', 2, 0, 'Sony WH-1000XM4', 'activa', '2025-11-22 01:15:46'),
(46, 5, '81KwZzTIXJL._AC_UF894,1000_QL80_FMwebp_.webp', 2, 0, 'Sony WH-1000XM4', 'activa', '2025-11-22 01:15:46'),
(47, 5, '81rVZu5JHUL._AC_UF894,1000_QL80_FMwebp_.webp', 2, 0, 'Sony WH-1000XM4', 'activa', '2025-11-22 01:15:46'),
(48, 5, '81UmmzjGcYL._AC_UF894,1000_QL80_FMwebp_.webp', 2, 0, 'Sony WH-1000XM4', 'activa', '2025-11-22 01:15:46'),
(49, 5, '81zNaO7AyoL._AC_UF894,1000_QL80_FMwebp_.webp', 2, 0, 'Sony WH-1000XM4', 'activa', '2025-11-22 01:15:46'),
(74, 10, '51s9hFUcOWL._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, 'Google Pixel 7 Pro', 'activa', '2025-12-13 08:11:42'),
(75, 10, '41cyVzc+X1L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, 'Google Pixel 7 Pro', 'activa', '2025-12-13 08:11:42'),
(76, 10, '41pUNIXO8FL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, 'Google Pixel 7 Pro', 'activa', '2025-12-13 08:11:42'),
(77, 10, '51wGYKSfUsL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, 'Google Pixel 7 Pro', 'activa', '2025-12-13 08:11:42'),
(78, 10, '414plRUqcFL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, 'Google Pixel 7 Pro', 'activa', '2025-12-13 08:11:42'),
(79, 10, '618Qjqls8NL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, 'Google Pixel 7 Pro', 'activa', '2025-12-13 08:11:42'),
(80, 11, '71tK4-o2xRL._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, 'HP Envy x360', 'activa', '2025-12-13 18:31:30'),
(81, 11, '71+1ZrBFFJL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, 'HP Envy x360', 'activa', '2025-12-13 18:31:30'),
(82, 11, '61noHdegysL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, 'HP Envy x360', 'activa', '2025-12-13 18:31:30'),
(83, 11, '71MxMvPfZ9L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, 'HP Envy x360', 'activa', '2025-12-13 18:31:30'),
(84, 11, '71Yhnuri4NL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, 'HP Envy x360', 'activa', '2025-12-13 18:31:30'),
(85, 12, '713BXdxitWL._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, 'Lenovo Yoga 9i', 'activa', '2025-12-13 18:36:55'),
(86, 12, '71TMsdLBU9L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, 'Lenovo Yoga 9i', 'activa', '2025-12-13 18:36:55'),
(87, 12, '71gpqN91yHL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, 'Lenovo Yoga 9i', 'activa', '2025-12-13 18:36:55'),
(88, 12, '61WB23ovq7L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, 'Lenovo Yoga 9i', 'activa', '2025-12-13 18:36:55'),
(89, 12, '61NcEIntA-L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, 'Lenovo Yoga 9i', 'activa', '2025-12-13 18:36:55'),
(90, 12, '61loddHwgyL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, 'Lenovo Yoga 9i', 'activa', '2025-12-13 18:36:55'),
(91, 12, '41q79Tkqt7L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, 'Lenovo Yoga 9i', 'activa', '2025-12-13 18:36:55'),
(92, 13, '61KYqYcdx-L._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, 'Dell XPS 13', 'activa', '2025-12-13 18:47:28'),
(93, 13, '51kJCcbogML._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, 'Dell XPS 13', 'activa', '2025-12-13 18:47:28'),
(94, 13, '61BrOhrRStL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, 'Dell XPS 13', 'activa', '2025-12-13 18:47:28'),
(95, 13, '514cnww2cKL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, 'Dell XPS 13', 'activa', '2025-12-13 18:47:28'),
(96, 14, '51CyZhzk0bL._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, 'Acer Swift 3', 'activa', '2025-12-13 18:54:27'),
(97, 14, '41q2aMNM26L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, '', 'activa', '2025-12-13 18:54:27'),
(98, 14, '31o9NczDJaL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 18:54:27'),
(99, 14, '51x9SeUpbVL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 18:54:27'),
(100, 14, '61+fqAHlW0L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 18:54:27'),
(101, 14, '61I5NSC49nL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 18:54:27'),
(102, 14, '61jtpdqYJFL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 18:54:27'),
(103, 14, '61kvraN-MgL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 18:54:27'),
(104, 14, '61l+Kl5Os2L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 18:54:27'),
(105, 14, '71gvm4PcQUL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, '', 'activa', '2025-12-13 18:54:27'),
(106, 14, '71k+P9WwZpL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 18:54:27'),
(107, 14, '71P7YQ88Y1L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 18:54:27'),
(108, 14, '310Ftshl78L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, '', 'activa', '2025-12-13 18:54:27'),
(109, 14, '811hUtE-9FL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 18:54:27'),
(110, 15, '61zhixq4K6L._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-13 19:03:07'),
(111, 15, '61QGCD4JpFL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:03:07'),
(112, 15, '61MWObS0QtL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:03:07'),
(113, 15, '51nVnS6e1WL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:03:07'),
(114, 15, '71-JWcf+YgL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:03:07'),
(115, 15, '71W9loaBCZL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:03:07'),
(116, 15, '612aKkMeGQL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:03:07'),
(117, 15, '710fK8eFPCL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:03:07'),
(118, 16, '71G1K2KhguL._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-13 19:03:07'),
(119, 16, '71brAk7Sc2L._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:03:07'),
(120, 16, '61uWcXA95BL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:03:07'),
(121, 16, '71W-yjrXOIL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:03:07'),
(122, 16, '81c+9BOQNWL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:03:07'),
(123, 16, '91Eeym+0E9L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:03:07'),
(124, 16, '911AokjmKkL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:03:07'),
(125, 17, '91oX+DLArWL._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-13 19:06:01'),
(126, 17, '51RpLgFK9OL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:06:01'),
(127, 17, '91xM28NaKzL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:06:01'),
(128, 17, '81KaLt52YiL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:06:01'),
(129, 17, '81wWoHhfbjL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:06:01'),
(130, 18, '41Wx4k++1nL._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-13 19:09:10'),
(131, 18, '51vj-FraMaL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:09:10'),
(132, 18, '31w67fcz1PL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:09:10'),
(133, 18, '51yhxadq9UL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:09:10'),
(134, 18, '513Twl79WpL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:09:10'),
(135, 18, '612N-e+YPYL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:09:10'),
(136, 19, '71xGnYR9V1L._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-13 19:15:12'),
(137, 19, '71BY7PtextL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:15:12'),
(138, 19, '71dC8xQliGL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:15:12'),
(139, 19, '71Gkr6pkFML._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:15:12'),
(140, 19, '71laMGM2LKL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:15:12'),
(141, 19, '71NNWTsVSQL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:15:12'),
(142, 19, '71oBul9dl+L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:15:12'),
(143, 19, '71woxTI2IOL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:15:12'),
(144, 19, '81mQ3sRNA8L._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:15:12'),
(145, 19, '816+CGOuA3L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:15:12'),
(146, 20, '71Ja2CMw3cL._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-13 19:42:49'),
(147, 20, '71VCfZB3BiL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:42:49'),
(148, 20, '61vNGS-CQFL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:42:49'),
(149, 20, '71o7pMHJpkL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:42:49'),
(150, 20, '61o06bcwlzL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:42:49'),
(151, 20, '81KHphtF7wL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:42:49'),
(152, 21, '71WH7jtyWpL._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-13 19:42:49'),
(153, 21, '71KAeEZfBuL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:42:49'),
(154, 21, '71lqxoDPqhL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:42:49'),
(155, 21, '51Pbro0+YXL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:42:49'),
(156, 21, '71pEt2YiQBL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:42:49'),
(157, 21, '71Z5pmfnMOL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:42:49'),
(158, 21, '81B5pd857xL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:42:49'),
(159, 21, '81ITRCi4b8L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:42:49'),
(160, 21, '81uLb-joy2L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:42:49'),
(161, 31, '81zXlT186LL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:42:49'),
(162, 31, '715QL6gi3qL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:42:49'),
(163, 22, '71cuZoxL0cL._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-13 19:42:49'),
(164, 22, '61IFDbVPKnL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:42:49'),
(165, 22, '71gruesjARL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:42:49'),
(166, 22, '71jiqI6uJfL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:42:49'),
(167, 22, '71rjWOv1OqL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:42:49'),
(168, 22, '71WYoOp311L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:42:49'),
(169, 22, '81AVJpyGllL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:42:49'),
(170, 22, '81cG36whXpL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:42:49'),
(171, 22, '81kt1s3KqRL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:42:49'),
(172, 22, '81Q1wuRsClL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:42:49'),
(173, 22, '81viOu1vDhL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:42:49'),
(174, 22, '715sn3f0TaL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:42:49'),
(175, 22, '718itOgof+L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:42:49'),
(176, 22, '812zqJbvvgL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 19:42:49'),
(177, 23, '81twLlLSkUL._AC_UF894,1000_QL80_FMwebp_ (1).webp', 0, 1, NULL, 'activa', '2025-12-13 20:07:18'),
(178, 23, '71DstY4LqYL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(179, 23, '71tEp7wCA0L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(180, 23, '81e2Nm2mqYL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(181, 23, '71MYLH4Io-L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(182, 23, '81eBCBMoc9L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(183, 23, '81F5NujxyNL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(184, 23, '81nIaWhH41L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(185, 23, '616rxDSyPsL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(186, 23, '818XsZebYcL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(187, 23, '915xfTXWDoL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(188, 24, '612s6bFq1nL._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-13 20:07:18'),
(189, 24, '51jAHDbO-1L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(190, 24, '31f+SKDXAlL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(191, 24, '31jIiKegr7L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(192, 24, '31ytN1Hr8sL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(193, 24, '61+W13NTcnL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(194, 25, '91blSirEjOL._AC_UF1000,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-13 20:07:18'),
(195, 25, '81LimnlEEUL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(196, 25, '81dJuShXqNL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(197, 25, '71R6dtfSfZL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(198, 25, '81r9BCEzm0L._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(199, 25, '71jxdtUOB5L._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(200, 25, '81-3rdDfnoL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(201, 25, '81a-HgS8a9L._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(202, 25, '81BnwcHRbIL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(203, 25, '81PM03oM+cL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(204, 25, '81Vu+Yl48nL._AC_UF1000,1000_QL80_FMwebp_ (1).webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(205, 25, '81ZbiaH6vsL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(206, 25, '91JZYWqe0NL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(207, 25, '81378LwHf1L._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(208, 26, '91CEbtJBAeL._AC_UF1000,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-13 20:07:18'),
(209, 26, '81XLmkwSjlL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(210, 26, '516Vxsmt3kL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(211, 26, '710gkvNHTTL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(212, 26, '71DRnGvKlSL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(213, 26, '81AcYWSALHL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(214, 26, '81aF7IiH01L._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(215, 26, '81ogSPB+lCL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(216, 26, '81P1SmkraYL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(217, 26, '81pBC7g2K4L._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(218, 26, '81Q-CqsF9-L._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(219, 26, '81RuiRDoI+L._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(220, 26, '81wRDcShIcL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(221, 26, '91JzfeVd4RL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(222, 27, '81gh-FPrFyL._AC_UF1000,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-13 20:07:18'),
(223, 27, '71G+CkSUPDL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(224, 27, '71KKxqnRjTL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(225, 27, '71PR9p5qb+L._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(226, 27, '71w0poJUwEL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(227, 27, '81jtriqVNHL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(228, 27, '81JZhGoWoIL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(229, 27, '81KPRUm2UCL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(230, 27, '81Oiy02I1SL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(231, 27, '81RseSU0fGL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(232, 27, '81wv-qlTpEL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(233, 27, '81x3zwn7H7L._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(234, 27, '910TW+oEOFL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(235, 27, '61R27P42HKL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(236, 28, '61Gdpfwb4VL._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-13 20:07:18'),
(237, 28, '41JKcZGUKCL.jpg', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(238, 28, '51aXvjzcukL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(239, 28, '51gaT35OeML._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(240, 28, '51OGN-Nlu7L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(241, 28, '51pFYV7FHdL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(242, 28, '51QK24rMqIL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(243, 28, '61VUD9Zh9sL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(244, 28, '71GMFf-4cwL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(245, 28, '81+4fB1ehJL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(246, 28, '81hTYF7kIAL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(247, 28, '81kCCjRFZTL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(248, 28, '81q1SrpG1PL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(249, 28, '81zk7HFkz7L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:07:18'),
(250, 29, '71zohVROdFL._AC_UF1000,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-13 20:20:29'),
(251, 29, '81qtCsgRUkL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(252, 29, '81-vJlhxz2L._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(253, 29, '61Eaj593GtL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(254, 29, '91aLsNImBzL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(255, 29, '811BMUdgoJL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(256, 29, '61XE8N6tY4L._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(257, 30, '61qlhFddcFL._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-13 20:20:29'),
(258, 30, '41w8w4y+C8L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(259, 30, '51f6eKR8mPL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(260, 30, '51KrQe-OMaL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(261, 30, '51PHh14D90L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(262, 30, '61iolhuDBCL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(263, 30, '61nEJsUzzjL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(264, 30, '71T0pqz-S6L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(265, 30, '418lXg2+QVL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(266, 30, '519eT3Q6MYL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(267, 31, '715fM0yZV3L._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-13 20:20:29'),
(268, 31, '51iFTvyMDvL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(269, 31, '51MvEY4ZPSL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(270, 31, '61edAuPV2uL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(271, 31, '61gBBi08R7L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(272, 31, '61H9kxAR5oL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(273, 31, '61s5kI0U4cL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(274, 31, '61tmEBG6+TL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(275, 31, '61yirkqH5jL.jpg', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(276, 31, '516WpnJxrkL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(277, 31, '518yo3Z01QL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(278, 32, '61d6+iw1imL._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-13 20:20:29'),
(279, 32, '61EXR1PZDiL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(280, 32, '61rSWs+HpnL.jpg', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(281, 32, '61Ul2vsYfnL.jpg', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(282, 32, '61xoy14AWPL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(283, 32, '710OGFo6vkL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(284, 33, '71HNFlNMzrL._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-13 20:20:29'),
(285, 33, '71lWcn6c01L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(286, 33, '71p5V8+OnfL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(287, 33, '71rk4AHajWL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(288, 33, '71Vg9JzUJfL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(289, 33, '81RrX9Y+faL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(290, 33, '81w6e+7s1dL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(291, 33, '712qTE0CejL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(292, 33, '718-cCZu0DL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:20:29'),
(293, 67, '41AoXD6zyUL._AC_UF350,350_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-13 20:48:28'),
(294, 67, '41lSHUF-qHL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(295, 67, '41xM6Ck57yL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(296, 67, '51aWup5Z8wL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(297, 67, '51eLQHn+07L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(298, 67, '51jsuZD0d2L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(299, 67, '410WvAuJIHL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(300, 67, '419+9qEhMkL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(301, 68, '512w+yyxHRL._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-13 20:48:28'),
(302, 68, '51B7FLJqvTL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(303, 68, '51HVvHxZ9YL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(304, 68, '51Mjbagd9YL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(305, 68, '51QaMc4Hu9L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(306, 68, '61lfRqGkRwL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(307, 68, '61NUcDHywGL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(308, 68, '411FYBnS+WL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(309, 69, '61ILwty6jtL._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-13 20:48:28'),
(310, 69, '71iDXFHESeL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(311, 69, '71-SVOnoOXL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(312, 69, '81gS7G131sL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(313, 69, '81L+ZYinrML._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(314, 69, '81Mh9rqXyOL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(315, 69, '91VRRhwLX5L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(316, 70, '51ddObzryUL._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-13 20:48:28'),
(317, 70, '51OWRXKbz+L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(318, 70, '61TosLOjxLL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(319, 70, '61w8sZouVrL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(320, 70, '61y3wPH-gjL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(321, 70, '518aIDZMN0L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(322, 70, '51040YrY8OL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(323, 71, '51drrEPXPlL._AC_UF350,350_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-13 20:48:28'),
(324, 71, '61DXAMrpnlL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(325, 71, '61EeIzLZhKL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(326, 71, '61Qv8LhNq-L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(327, 71, '71+T0040axL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(328, 71, '71XGN9P7xpL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(329, 71, '71XGN9P7xpL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(330, 72, '51f48PtAncL._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-13 20:48:28'),
(331, 72, '61bnh5rkTkL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(332, 72, '610j1hsxU6L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(333, 72, '619EqsYf0DL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(334, 73, '31Czt9YaWrS._AC_UF350,350_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-13 20:48:28'),
(335, 73, '41zSIIvWDMS._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(336, 73, '71s7kvHyVvS._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(337, 73, '616X8zng9wS._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(338, 74, '31tictvCftL._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-13 20:48:28'),
(339, 74, '41kUzvIR-RL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(340, 74, '51GejAEQT2L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(341, 74, '51sUPEWaI6L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(342, 74, '61buFPDapaS._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(343, 74, '61iyoT2OltL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(344, 74, '71jrlQ2m7BS._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(345, 75, '51++kUYyMIL._AC_UF1000,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-13 20:48:28'),
(346, 75, '51br+8QidYL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(347, 75, '61E4b5drxzS._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(348, 75, '61U4SKnyS4L._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(349, 75, '61z-iuVjhdS._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(350, 75, '71Sgq7L+AuS._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(351, 75, '516XUlqUeiL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(352, 75, '719EZAc9WHS._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(353, 75, '6106vjwmtIS._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(354, 6, '81YUjHf6zqL._AC_UF350,350_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-13 20:48:28'),
(355, 6, '71QdVJSxZtL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(356, 6, '71jZ5DXKPiL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(357, 6, '71IjN0wwQZL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(358, 6, '61HcUCcW2VL._SL500_.jpg', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(359, 7, '51CJE8vrvIL._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-13 20:48:28'),
(360, 7, '51CM4kw8LrL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(361, 7, '51P6fVTz7BL.jpg', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(362, 7, '71WrpGdqDiL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(363, 7, '614EMTj1POL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(364, 7, '5138sSJ+x3L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(365, 8, '31AzxNTCUcL._AC_UF350,350_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-13 20:48:28'),
(366, 8, '51BVh3k47YL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(367, 8, '51pk5PzpzZL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(368, 9, '61RtGmktn2L._AC_UF350,350_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-13 20:48:28'),
(369, 9, '41ETdGqNk7L._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(370, 9, '41jFxNIS9XL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(371, 9, '51ZhHnTx-PL.jpg', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(372, 9, '61CDg9G9SBL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(373, 9, '61fBOXXstfL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(374, 9, '61gwCW9ZBkL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(375, 9, '61KI0IEtv0L._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(376, 9, '61TTwH1MOmL._SL500_.jpg', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(377, 9, '61VB-jNce8L._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(378, 9, '416v6JUn8xL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(379, 9, '8160OiLlJEL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-13 20:48:28'),
(380, 34, '61YcLROOvHL._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-16 15:38:48'),
(381, 34, '71N2B-o4jTL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 15:38:48'),
(382, 34, '61YcLROOvHL._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-16 15:39:08'),
(383, 34, '71N2B-o4jTL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 15:39:08'),
(384, 34, '61YcLROOvHL._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-16 16:24:44'),
(385, 34, '71N2B-o4jTL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(386, 34, '81+Qx-9ykJL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(387, 34, '81AiA6Xd7BL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(388, 34, '81FRx0PfV+L._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(389, 34, '81HahPg2mpL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(390, 34, '81L+sJrmcSL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(391, 34, '81NgznHl4fL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(392, 34, '81vvJJuLjIL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(393, 36, '51I3dCN+xhL._AC_UF1000,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-16 16:24:44'),
(394, 36, '61+7W04OiAL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(395, 36, '71Ti8IhJUqL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(396, 36, '71y130FSfzL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(397, 36, '71ZsRnJS2RL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(398, 35, '51eS8rTybvL._AC_UF350,350_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-16 16:24:44'),
(399, 35, '61b4-BmC+uL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(400, 35, '61sTGcAy3SL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(401, 35, '71A1LnfVQlL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(402, 35, '71NIaLAD+DL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(403, 35, '71Tmb9DKwKL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(404, 37, '71Xb8E5aDUL._AC_UF350,350_QL50_.jpg', 0, 1, NULL, 'activa', '2025-12-16 16:24:44'),
(405, 38, '81PRhEDyQ4L._AC_UF1000,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-16 16:24:44'),
(406, 39, '61QKcHh9A6L._AC_UF350,350_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-16 16:24:44'),
(407, 39, '61WYbzNluwL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(408, 39, '71m4wFBAgBL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(409, 39, '71nnMPU7GBL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(410, 39, '71vLUsql0iL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(411, 39, '711d2vAMlNL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(412, 40, '61ntcdCFwLL._AC_UF350,350_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-16 16:24:44'),
(413, 40, '71bEbjLWldL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(414, 40, '71Gz4XfYAIL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(415, 40, '81P6kB77U-L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(416, 40, '81w-NTnRUnL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(417, 40, '91iCix3DopL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(418, 41, '31Kp0h72XfL._AC_UF350,350_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-16 16:24:44'),
(419, 41, '31RUv47cYML._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(420, 41, '31yxtQUiJrL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(421, 41, '41PAkglMyFL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(422, 41, '41vfbLN1czL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(423, 41, '51UhPtP9rBL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(424, 42, '21xTS-YIMOL._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-16 16:24:44'),
(425, 42, '514Kel0a1xL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(426, 43, '71ScqBLVyoL._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-16 16:24:44'),
(427, 43, '81NUDpWzOTL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(428, 43, '81Png0AdLEL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(429, 44, '61dgZYPJb+L._AC_UF350,350_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-16 16:24:44'),
(430, 44, '61mKxWxGu7L._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(431, 44, '71Zvoe0TMOL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(432, 44, '611KhCm9SQL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(433, 45, '61STSRzgqOL._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-16 16:24:44'),
(434, 45, '71feTcxp9LL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(435, 45, '71IWlpnDoCL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(436, 45, '71KpvE+-HNL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(437, 45, '71UTVGmPMDL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(438, 46, '71vSh429oCL._AC_UF1000,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-16 16:24:44'),
(439, 46, '81chA-DrVCL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(440, 46, '81fsOLKe-EL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(441, 46, '81jM88JEQVL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(442, 46, '81KFarmVkSL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(443, 46, '81n9rCqdm4L._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(444, 46, '81nR0weeZRL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(445, 46, '81pEH3lllYL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(446, 46, '81q-zsAv0OL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(447, 46, '81UY-4E3s9L._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(448, 46, '81xo4UQQqxL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(449, 46, '81zRvbXoHoL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(450, 46, '810H71fkWIL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(451, 47, '61nAJNWWseL._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-16 16:24:44'),
(452, 47, '81VXB-JUUyL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(453, 47, '81XKY+O9htL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(454, 47, '81Zatixij1L._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(455, 47, '519-SxN1CIL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(456, 47, '812BSG+nA1L._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(457, 47, '813xWPmoqVL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(458, 48, '61imT1kQgDL._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-16 16:24:44'),
(459, 48, '61UbInKJ+VL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(460, 48, '61WEIlPvZ7L._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(461, 48, '71tAN1o+1dL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(462, 48, '71yBnR9966L._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(463, 49, '61cJa4QcszL._AC_UF350,350_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-16 16:24:44'),
(464, 49, '61Qy1zAVuOL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(465, 49, '61sc12WDgLL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(466, 49, '61tWRG6EhBL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(467, 49, '61XOOeQP7JL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(468, 49, '71-7mR+ljuL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(469, 49, '71q6Kd-cRLL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(470, 49, '71vL7eBezcL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(471, 50, '51OcdwOss8L._AC_UF1000,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-16 16:24:44'),
(472, 50, '71G0uK-bQkL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(473, 50, '71U+2vmTv6L._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(474, 50, '7143WIhzdFL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(475, 51, '61lUrhWuoFS._AC_UF350,350_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-16 16:24:44'),
(476, 51, '61TXk4Bk9RS._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(477, 51, '61xqRlpqxbS._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(478, 51, '71ABk3V7SdS._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(479, 51, '71sLYXFihDS._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(480, 51, '71W8TUCCi4S._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(481, 51, '81z2vvALO-S._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(482, 52, '411Pn95TGoL._AC_UY1000_.jpg', 0, 1, NULL, 'activa', '2025-12-16 16:24:44'),
(483, 52, '51WVsY80qjL._AC_UY1000_.jpg', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(484, 53, '31R3fvuktHL._AC_UY1000_.jpg', 0, 1, NULL, 'activa', '2025-12-16 16:24:44'),
(485, 53, '41hqRKVgzKL._AC_UY1000_.jpg', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(486, 53, '41YGvYSinUL._AC_UY1000_.jpg', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(487, 53, '51hvroL7YkL._AC_UY1000_.jpg', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(488, 53, '417Ve8XZ7jL._AC_UY1000_.jpg', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(489, 53, '810E-dKAGDL._AC_UY1000_.jpg', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(490, 53, '4149R9dK4gL._AC_UY1000_.jpg', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(491, 54, '41vb39dH0uL.jpg', 0, 1, NULL, 'activa', '2025-12-16 16:24:44'),
(492, 54, '71AutlgE7YL._AC_UY1000_.jpg', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(493, 54, '71G5P8Nk91L._AC_UY350_.jpg', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(494, 54, '71HQMSnUK8L._AC_UY1000_.jpg', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(495, 54, '81Eik5P9dgL._AC_UY1000_.jpg', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(496, 54, '81u-mUJwl+L._AC_UY1000_.jpg', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(497, 54, '91EpwScpCpL._AC_UY1000_.jpg', 1, 0, NULL, 'activa', '2025-12-16 16:24:44'),
(498, 55, '51bxXSJRTfL._AC_UY900_.jpg', 0, 1, NULL, 'activa', '2025-12-17 02:02:34'),
(499, 55, '51E22x7UAnL._AC_UY900_.jpg', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(500, 55, '51-FabGq73L._AC_UY900_.jpg', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(501, 55, '51fXpi2fcWL._AC_UY900_.jpg', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(502, 55, '51jS5XnR8eL._AC_UY900_.jpg', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(503, 56, '31LNbrR0mdL._AC_UY900_.jpg', 0, 1, NULL, 'activa', '2025-12-17 02:02:34'),
(504, 56, '31pioss-iLL._AC_UY900_.jpg', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(505, 56, '31-rW-4co4L._AC_UY900_.jpg', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(506, 56, '31Uw8YbT2uL._AC_UY900_.jpg', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(507, 56, '41HotpCaS2L._AC_UY900_.jpg', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(508, 56, '41MapYtUTWL._AC_UY900_.jpg', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(509, 56, '41XOAsGK4PL._AC_UY900_.jpg', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(510, 56, '71XQtxdPHIL._AC_UY900_.jpg', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(511, 56, '715mUb+iSjL._AC_UY900_.jpg', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(512, 57, '61zi2SuCRpL._AC_UY1000_.jpg', 1, 1, NULL, 'activa', '2025-12-17 02:02:34'),
(513, 57, '616ZvzuULzL._AC_UY1000_.jpg', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(514, 58, '61PGjp6f1lL._AC_UF1000,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-17 02:02:34'),
(515, 58, '61t+P8qd-lL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(516, 58, '6142uuqKSHL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(517, 59, '51gg+JWLZ9L._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-17 02:02:34'),
(518, 59, '51PS7CK5vCL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(519, 59, '51qHOQ7FaJL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(520, 59, '51s2gYa4eEL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(521, 59, '51Vn8g9dHzL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(522, 59, '61qYqL-cHfL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-17 02:02:34');
INSERT INTO `producto_imagen` (`id`, `id_producto`, `imagen_url`, `orden`, `es_principal`, `alt_text`, `estado`, `fecha_creacion`) VALUES
(523, 60, '41l2A8+3wlL._AC_UF894,1000_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-17 02:02:34'),
(524, 61, '61HV1xUVd0L._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 1, NULL, 'activa', '2025-12-17 02:02:34'),
(525, 61, '71GFiCnumcL._AC_UF350,350_QL80_FMwebp_.webp', 1, 1, NULL, 'activa', '2025-12-17 02:02:34'),
(526, 61, '81rGHpz4mSL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(527, 62, '61e8ZfsHdSL._AC_UF350,350_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-17 02:02:34'),
(528, 62, '71P6TqIG5AL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(529, 62, '81hC0qPHA4L._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(530, 62, '81njb8nY7pL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(531, 62, '417ljtsmj6L._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(532, 63, '81DMKl3-m4L._AC_UF350,350_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-17 02:02:34'),
(533, 63, '81EQcxYzoSL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(534, 63, '81fPIkIziWL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(535, 63, '81GPUJFfPyL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(536, 63, '81j3hAwllSL._AC_UF1000,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(537, 63, '81ZRKymAXgL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(538, 64, '61eQn-cMmiL._AC_UF350,350_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-17 02:02:34'),
(539, 64, '61eQn-cMmiL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(540, 64, '61rGUpU9JcL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(541, 64, '81f0BWm-e0L._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(542, 64, '81f0BWm-e0L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(543, 64, '81fZ5APnxuL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(544, 65, '61aIslsVkFL._AC_UF350,350_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-17 02:02:34'),
(545, 65, '61k2nSwUMGL._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(546, 66, '61TXueG28GL._AC_UF350,350_QL80_FMwebp_.webp', 0, 1, NULL, 'activa', '2025-12-17 02:02:34'),
(547, 66, '61uGzD5HFBL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(548, 66, '61WCLJfiT4L._AC_UF894,1000_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(549, 66, '71XDQ4ojPJL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(550, 66, '91xKfK+ZTmL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-17 02:02:34'),
(551, 66, '911ifzTiqRL._AC_UF350,350_QL80_FMwebp_.webp', 1, 0, NULL, 'activa', '2025-12-17 02:02:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_tienda`
--

CREATE TABLE `producto_tienda` (
  `id` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `id_tienda` int(11) NOT NULL,
  `precio` decimal(12,2) NOT NULL CHECK (`precio` >= 0),
  `precio_original` decimal(12,2) DEFAULT NULL,
  `descuento_porcentaje` decimal(5,2) DEFAULT 0.00 CHECK (`descuento_porcentaje` between 0 and 100),
  `precio_final` decimal(12,2) GENERATED ALWAYS AS (case when `descuento_porcentaje` > 0 then `precio` - `precio` * `descuento_porcentaje` / 100 else `precio` end) STORED,
  `stock` int(11) NOT NULL DEFAULT 0 CHECK (`stock` >= 0),
  `stock_minimo` int(11) DEFAULT 5,
  `sku_tienda` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `garantia_meses` int(11) DEFAULT NULL,
  `tiempo_entrega` int(11) DEFAULT NULL,
  `costo_envio` decimal(10,2) DEFAULT 0.00,
  `envio_gratis` tinyint(1) DEFAULT 0,
  `es_destacado` tinyint(1) NOT NULL DEFAULT 0,
  `calificacion_promedio` decimal(3,2) DEFAULT 0.00,
  `total_resenas` int(11) DEFAULT 0,
  `total_ventas` int(11) DEFAULT 0,
  `visitas` int(11) DEFAULT 0,
  `estado` enum('activo','inactivo','agotado','pausado') COLLATE utf8mb4_unicode_ci DEFAULT 'activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `producto_tienda`
--

INSERT INTO `producto_tienda` (`id`, `id_producto`, `id_tienda`, `precio`, `precio_original`, `descuento_porcentaje`, `stock`, `stock_minimo`, `sku_tienda`, `garantia_meses`, `tiempo_entrega`, `costo_envio`, `envio_gratis`, `es_destacado`, `calificacion_promedio`, `total_resenas`, `total_ventas`, `visitas`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 1, 1, '382500.00', '405000.00', '5.56', 15, 3, 'TS-SGS21U', 12, 3, '2250.00', 0, 1, '4.70', 23, 45, 156, 'activo', '2025-11-17 02:19:08', '2026-01-13 03:26:11'),
(2, 2, 1, '427500.00', '427500.00', '0.00', 8, 2, 'TS-IP13P', 12, 3, '2250.00', 0, 0, '4.80', 18, 32, 142, 'activo', '2025-11-17 02:19:08', '2026-01-13 03:25:43'),
(3, 3, 1, '54000.00', '67500.00', '50.00', 49, 10, 'TS-NAF1', 6, 2, '1350.00', 0, 1, '4.60', 67, 124, 289, 'activo', '2025-11-17 02:19:08', '2026-01-13 03:25:19'),
(4, 4, 1, '63000.00', '72000.00', '12.50', 34, 8, 'TS-AU22', 6, 2, '1350.00', 0, 0, '4.50', 45, 88, 201, 'activo', '2025-11-17 02:19:08', '2025-12-17 02:47:10'),
(5, 5, 1, '126000.00', '144000.00', '12.50', 12, 3, 'TS-SWHXM4', 12, 3, '2250.00', 0, 1, '4.90', 34, 56, 178, 'activo', '2025-11-17 02:19:08', '2025-12-17 02:32:23'),
(6, 6, 1, '495000.00', '540000.00', '8.33', 24, 5, 'TS-SGS22U', 12, 3, '2250.00', 0, 1, '4.80', 45, 79, 320, 'activo', '2025-12-13 02:02:28', '2025-12-17 02:32:36'),
(7, 7, 1, '562500.00', '585000.00', '3.85', 0, 4, 'TS-IP14P', 12, 3, '1350.00', 0, 1, '4.90', 56, 94, 410, 'activo', '2025-12-13 02:02:28', '2025-12-17 02:49:16'),
(8, 8, 1, '427500.00', '450000.00', '5.00', 22, 6, 'TS-XIA13P', 12, 3, '2250.00', 0, 0, '4.70', 34, 67, 290, 'activo', '2025-12-13 02:02:28', '2025-12-17 02:32:45'),
(9, 9, 1, '405000.00', '427500.00', '5.26', 15, 4, 'TS-HWP50P', 12, 3, '2250.00', 0, 0, '4.60', 28, 45, 210, 'activo', '2025-12-13 02:02:28', '2025-12-17 02:32:53'),
(10, 10, 1, '472500.00', '495000.00', '4.55', 20, 5, 'TS-GPIX7P', 12, 3, '2250.00', 0, 1, '4.85', 42, 71, 350, 'activo', '2025-12-13 02:02:28', '2025-12-17 02:33:02'),
(11, 11, 1, '382500.00', '405000.00', '5.56', 12, 3, 'TS-HPENVY', 12, 5, '3600.00', 0, 1, '4.75', 38, 52, 180, 'activo', '2025-12-13 02:02:28', '2025-12-17 02:36:17'),
(12, 12, 1, '540000.00', '585000.00', '7.69', 8, 2, 'TS-LENYOG', 12, 5, '3600.00', 0, 1, '4.90', 24, 36, 150, 'activo', '2025-12-13 02:02:28', '2025-12-17 02:36:26'),
(13, 13, 1, '427500.00', '450000.00', '5.00', 0, 4, 'TS-DELLXPS', 12, 5, '3600.00', 0, 1, '4.80', 31, 48, 170, 'activo', '2025-12-13 02:02:28', '2025-12-17 02:49:33'),
(14, 14, 1, '315000.00', '337500.00', '6.67', 20, 6, 'TS-ACERSW', 12, 5, '3600.00', 0, 0, '4.65', 27, 42, 160, 'activo', '2025-12-13 02:02:28', '2025-12-17 02:36:43'),
(15, 15, 1, '495000.00', '517500.00', '4.35', 10, 3, 'TS-ASUSZEN', 12, 5, '3600.00', 0, 1, '4.85', 22, 35, 140, 'activo', '2025-12-13 02:02:28', '2025-12-17 02:36:53'),
(16, 16, 1, '585000.00', '630000.00', '7.14', 6, 2, 'TS-IPADPRO', 12, 4, '2700.00', 0, 1, '4.95', 18, 29, 120, 'activo', '2025-12-13 02:02:28', '2025-12-17 02:37:27'),
(17, 17, 1, '382500.00', '405000.00', '5.56', 12, 4, 'TS-SAMTABS8', 12, 4, '2700.00', 0, 1, '4.75', 25, 40, 135, 'activo', '2025-12-13 02:02:28', '2025-12-17 02:37:36'),
(18, 18, 1, '337500.00', '360000.00', '6.25', 0, 3, 'TS-LENOPAD', 12, 4, '2700.00', 0, 0, '4.70', 20, 32, 110, 'activo', '2025-12-13 02:02:28', '2025-12-17 02:49:43'),
(19, 19, 1, '1125000.00', '1215000.00', '7.41', 4, 1, 'TS-HPOMEN', 24, 7, '6750.00', 0, 1, '4.95', 12, 18, 85, 'activo', '2025-12-13 02:02:28', '2025-12-17 02:40:10'),
(20, 20, 1, '81000.00', '855000.00', '5.26', 6, 2, 'TS-DELLXPSD', 24, 7, '6750.00', 0, 1, '4.85', 15, 22, 95, 'activo', '2025-12-13 02:02:28', '2025-12-17 02:40:17'),
(21, 21, 1, '990000.00', '1080000.00', '8.33', 3, 1, 'TS-ASUSROG', 24, 7, '6750.00', 0, 1, '4.90', 10, 15, 75, 'activo', '2025-12-13 02:02:28', '2025-12-17 02:40:20'),
(22, 22, 1, '675000.00', '720000.00', '6.25', 7, 2, 'TS-SAMODYS', 24, 5, '0.00', 1, 1, '4.85', 20, 30, 130, 'activo', '2025-12-13 02:02:28', '2026-01-13 02:56:03'),
(23, 23, 1, '292500.00', '315000.00', '7.14', 14, 4, 'TS-LGULTRA', 24, 5, '5400.00', 0, 1, '4.80', 28, 43, 155, 'activo', '2025-12-13 02:02:28', '2025-12-17 02:40:49'),
(24, 24, 1, '360000.00', '382500.00', '5.88', 10, 3, 'TS-DELLULT', 24, 5, '5400.00', 0, 0, '4.75', 22, 35, 125, 'activo', '2025-12-13 02:02:28', '2025-12-17 02:40:57'),
(25, 25, 1, '990000.00', '1080000.00', '8.33', 0, 2, 'TS-SAMQN90', 24, 7, '9000.00', 0, 1, '4.90', 16, 25, 105, 'activo', '2025-12-13 02:02:28', '2025-12-17 02:49:54'),
(26, 26, 1, '810000.00', '900000.00', '10.00', 8, 3, 'TS-LGC2', 24, 7, '9000.00', 0, 1, '4.85', 22, 34, 140, 'activo', '2025-12-13 02:02:28', '2025-12-17 02:41:46'),
(27, 27, 1, '1260000.00', '1350000.00', '6.67', 3, 1, 'TS-SONYXRR', 24, 7, '9000.00', 0, 1, '4.95', 12, 18, 90, 'activo', '2025-12-13 02:02:28', '2025-12-17 02:41:50'),
(28, 28, 1, '157500.00', '180000.00', '12.50', 21, 8, 'TS-SONWHXM5', 12, 3, '5.00', 0, 1, '4.90', 45, 82, 320, 'activo', '2025-12-13 02:02:28', '2026-01-24 20:26:25'),
(29, 29, 1, '54000.00', '58500.00', '7.69', 40, 12, 'TS-JBLFLIP6', 12, 2, '1350.00', 0, 1, '4.75', 38, 65, 280, 'activo', '2025-12-13 02:02:28', '2025-12-17 02:34:36'),
(30, 30, 1, '112500.00', '126000.00', '10.71', 18, 6, 'TS-BOSESOUND', 12, 3, '2250.00', 0, 1, '4.85', 32, 52, 230, 'activo', '2025-12-13 02:02:28', '2025-12-17 02:33:54'),
(42, 31, 1, '10800.00', '11000.00', '8.00', 7, 5, 'TS-CANONEOS', 5, 4, '2700.00', 0, 1, '4.30', 45, 25, 56, 'activo', '2025-12-17 03:06:34', '2025-12-17 03:06:34'),
(43, 32, 1, '15000.00', '17000.00', '3.00', 47, 23, 'TS-NIKONZ', 5, 7, '3500.00', 0, 0, '4.70', 67, 125, 150, 'activo', '2025-12-17 03:06:34', '2025-12-17 03:06:34'),
(44, 33, 1, '12000.00', '13000.00', '0.00', 7, 5, 'TS-CGOPRO', 7, 2, '2250.00', 0, 0, '3.50', 17, 10, 20, 'activo', '2025-12-17 03:32:17', '2025-12-17 03:32:17'),
(45, 34, 1, '67500.00', '75000.00', '10.00', 15, 5, 'TS-TALADRO18V', 12, 3, '2250.00', 0, 0, '4.50', 28, 42, 120, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(46, 35, 1, '18000.00', '20000.00', '10.00', 25, 10, 'TS-DESTORN6PZ', 6, 2, '1350.00', 0, 0, '4.30', 35, 58, 95, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(47, 36, 1, '4500.00', '5000.00', '10.00', 40, 15, 'TS-CINTA8M', 6, 2, '1350.00', 0, 0, '4.60', 42, 75, 110, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(48, 37, 1, '22500.00', '25000.00', '10.00', 20, 8, 'TS-ACEITE5W30', 0, 3, '2250.00', 0, 0, '4.40', 31, 46, 85, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(49, 38, 1, '4500.00', '5000.00', '10.00', 35, 12, 'TS-LIMPIA2L', 0, 2, '1350.00', 0, 0, '4.20', 26, 40, 70, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(50, 39, 1, '6750.00', '7500.00', '10.00', 30, 10, 'TS-CABLEARR', 6, 2, '1350.00', 0, 0, '4.50', 38, 62, 105, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(51, 40, 1, '900.00', '1000.00', '10.00', 100, 30, 'TS-BICBOL10', 0, 2, '1350.00', 0, 0, '4.70', 52, 95, 150, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(52, 41, 1, '5400.00', '6000.00', '10.00', 50, 15, 'TS-PAPELA4500', 0, 3, '2250.00', 0, 0, '4.60', 34, 55, 110, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(53, 42, 1, '13500.00', '15000.00', '10.00', 18, 6, 'TS-CALCCASIO', 12, 3, '2250.00', 0, 1, '4.80', 22, 35, 90, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(54, 43, 1, '18000.00', '20000.00', '10.00', 25, 8, 'TS-PURINAGATO', 0, 3, '2250.00', 0, 0, '4.50', 45, 78, 130, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(55, 44, 1, '900.00', '1000.00', '10.00', 60, 20, 'TS-PELOTAPERRO', 0, 2, '1350.00', 0, 0, '4.30', 39, 65, 95, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(56, 45, 1, '6750.00', '7500.00', '10.00', 22, 7, 'TS-CORREARET', 6, 2, '1350.00', 0, 0, '4.60', 28, 42, 85, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(57, 46, 1, '36000.00', '40000.00', '10.00', 30, 10, 'TS-PAMPERS3', 0, 3, '2250.00', 0, 1, '4.80', 56, 92, 180, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(58, 47, 1, '2700.00', '3000.00', '10.00', 45, 15, 'TS-GERBERARROZ', 0, 2, '1350.00', 0, 0, '4.40', 32, 48, 75, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(59, 48, 1, '4500.00', '5000.00', '10.00', 28, 9, 'TS-JUGSENSBEBE', 6, 2, '1350.00', 0, 0, '4.70', 25, 38, 70, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(60, 49, 1, '4500.00', '5000.00', '10.00', 65, 20, 'TS-VITC1000', 0, 2, '1350.00', 0, 0, '4.60', 41, 68, 120, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(61, 50, 1, '13500.00', '15000.00', '10.00', 15, 5, 'TS-TERMOMINF', 12, 3, '2250.00', 0, 1, '4.70', 29, 44, 95, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(62, 51, 1, '2700.00', '3000.00', '10.00', 35, 12, 'TS-BANDASELAS', 6, 2, '1350.00', 0, 0, '4.50', 33, 52, 85, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(63, 52, 1, '9000.00', '10000.00', '10.00', 50, 15, 'TS-NIKEDRIFIT', 0, 2, '1350.00', 0, 0, '4.60', 48, 82, 140, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(64, 53, 1, '18000.00', '20000.00', '10.00', 40, 12, 'TS-ADIPANTENT', 0, 2, '1350.00', 0, 0, '4.50', 35, 58, 110, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(65, 54, 1, '27000.00', '30000.00', '10.00', 30, 10, 'TS-LEVIS501', 0, 2, '1350.00', 0, 1, '4.80', 42, 75, 160, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(66, 55, 1, '40500.00', '45000.00', '10.00', 25, 8, 'TS-NIKEAIRMAX', 6, 3, '2250.00', 0, 1, '4.70', 39, 65, 150, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(67, 56, 1, '36000.00', '40000.00', '10.00', 35, 10, 'TS-ADIDASSUPER', 6, 3, '2250.00', 0, 1, '4.75', 45, 78, 170, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(68, 57, 1, '31500.00', '35000.00', '10.00', 45, 15, 'TS-CONVERSE', 6, 2, '1350.00', 0, 0, '4.85', 52, 90, 190, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(69, 58, 1, '180000.00', '200000.00', '10.00', 8, 3, 'TS-BOWFLEXPES', 24, 5, '5400.00', 0, 1, '4.90', 18, 25, 80, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(70, 59, 1, '67500.00', '75000.00', '10.00', 12, 4, 'TS-FITBITCH5', 12, 3, '2250.00', 0, 1, '4.80', 26, 40, 105, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(71, 60, 1, '9000.00', '10000.00', '10.00', 25, 8, 'TS-YOGAMAT15', 6, 2, '1350.00', 0, 0, '4.60', 34, 55, 95, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(72, 61, 1, '13500.00', '15000.00', '10.00', 40, 12, 'TS-LOREALBASE', 0, 2, '1350.00', 0, 0, '4.70', 47, 80, 145, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(73, 62, 1, '9000.00', '10000.00', '10.00', 55, 18, 'TS-MAYBELLSKY', 0, 2, '1350.00', 0, 0, '4.65', 39, 68, 130, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(74, 63, 1, '5400.00', '6000.00', '10.00', 60, 20, 'TS-NIVEACREMA', 0, 2, '1350.00', 0, 0, '4.60', 52, 88, 155, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(75, 64, 1, '4500.00', '5000.00', '10.00', 80, 25, 'TS-NESCAFECLAS', 0, 2, '1350.00', 0, 0, '4.80', 62, 105, 200, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(76, 65, 1, '900.00', '1000.00', '10.00', 100, 30, 'TS-COCACOLA2L', 0, 2, '1350.00', 0, 0, '4.90', 75, 128, 250, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(77, 66, 1, '450.00', '500.00', '10.00', 120, 40, 'TS-KNORRSOPA', 0, 2, '1350.00', 0, 0, '4.40', 45, 78, 135, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(78, 67, 1, '900000.00', '1000000.00', '10.00', 5, 2, 'TS-LGREFRI', 36, 7, '9000.00', 0, 1, '4.95', 15, 22, 90, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(79, 68, 1, '630000.00', '700000.00', '10.00', 6, 2, 'TS-SAMLAVAD', 36, 7, '9000.00', 0, 1, '4.90', 18, 28, 105, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(80, 69, 1, '315000.00', '350000.00', '10.00', 8, 3, 'TS-PHILAC', 24, 5, '5400.00', 0, 1, '4.85', 22, 35, 110, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(81, 70, 1, '135000.00', '150000.00', '10.00', 10, 4, 'TS-IKEASILLON', 0, 5, '5400.00', 0, 1, '4.75', 28, 42, 120, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(82, 71, 1, '4500.00', '5000.00', '10.00', 35, 12, 'TS-PHILLED', 12, 2, '1350.00', 0, 0, '4.70', 32, 50, 95, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(83, 72, 1, '81000.00', '90000.00', '10.00', 12, 4, 'TS-BRAUNCAFE', 12, 3, '2250.00', 0, 1, '4.80', 24, 38, 100, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(84, 73, 1, '450000.00', '500000.00', '10.00', 6, 2, 'TS-PS5DISC', 12, 5, '5400.00', 0, 1, '4.95', 20, 30, 125, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(85, 74, 1, '450000.00', '500000.00', '10.00', 7, 3, 'TS-XBOXSX', 12, 5, '5400.00', 0, 1, '4.90', 22, 32, 115, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44'),
(86, 75, 1, '180000.00', '200000.00', '10.00', 15, 5, 'TS-NINSWITOLED', 12, 4, '2700.00', 0, 1, '4.85', 35, 52, 140, 'activo', '2025-12-23 15:07:44', '2025-12-23 15:07:44');

--
-- Disparadores `producto_tienda`
--
DELIMITER $$
CREATE TRIGGER `actualizar_calificacion_tienda` AFTER UPDATE ON `producto_tienda` FOR EACH ROW BEGIN
    DECLARE avg_calificacion DECIMAL(3,2);
    
    -- Calcular el nuevo promedio para la tienda
    SELECT COALESCE(AVG(calificacion_promedio), 0.00)
    INTO avg_calificacion
    FROM producto_tienda
    WHERE id_tienda = NEW.id_tienda
      AND estado = 'activo'
      AND calificacion_promedio > 0;
    
    -- Actualizar la tienda
    UPDATE tienda
    SET calificacion_promedio = avg_calificacion,
        fecha_actualizacion = CURRENT_TIMESTAMP
    WHERE id = NEW.id_tienda;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `provincia`
--

CREATE TABLE `provincia` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('activa','inactiva') COLLATE utf8mb4_unicode_ci DEFAULT 'activa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `provincia`
--

INSERT INTO `provincia` (`id`, `nombre`, `codigo`, `estado`) VALUES
(1, 'Pinar del Río', 'PR', 'activa'),
(2, 'Artemisa', NULL, 'activa'),
(3, 'Mayabeque', NULL, 'activa'),
(4, 'La Habana', NULL, 'activa'),
(5, 'Matanzas', NULL, 'activa'),
(6, 'Cienfuegos', NULL, 'activa'),
(7, 'Villa Clara', NULL, 'activa'),
(8, 'Santi Spíritus', NULL, 'activa'),
(9, 'Ciego de Ávila', NULL, 'activa'),
(10, 'Camagüey', NULL, 'activa'),
(11, 'Las Tunas', NULL, 'activa'),
(12, 'Holguín', NULL, 'activa'),
(13, 'Santiago de Cuba', NULL, 'activa'),
(14, 'Guantánamo', NULL, 'activa'),
(15, 'Isla de la Juventud', NULL, 'activa'),
(16, 'Granma', NULL, 'activa');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `redes_sociales`
--

CREATE TABLE `redes_sociales` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icono` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '#4361ee',
  `orden` int(11) DEFAULT 0,
  `activa` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `redes_sociales`
--

INSERT INTO `redes_sociales` (`id`, `nombre`, `url`, `icono`, `color`, `orden`, `activa`, `fecha_creacion`) VALUES
(1, 'Facebook', 'https://facebook.com/nexusbuy', 'fab fa-facebook-f', '#1877F2', 1, 1, '2026-01-07 12:58:27'),
(2, 'Instagram', 'https://instagram.com/nexusbuy', 'fab fa-instagram', '#E4405F', 2, 1, '2026-01-07 12:58:27'),
(3, 'Twitter/X', 'https://twitter.com/nexusbuy', 'fab fa-twitter', '#1DA1F2', 3, 1, '2026-01-07 12:58:27'),
(4, 'TikTok', 'https://tiktok.com/@nexusbuy', 'fab fa-tiktok', '#000000', 4, 1, '2026-01-07 12:58:27'),
(5, 'YouTube', 'https://youtube.com/@nexusbuy', 'fab fa-youtube', '#FF0000', 5, 1, '2026-01-07 12:58:27'),
(6, 'LinkedIn', 'https://linkedin.com/company/nexusbuy', 'fab fa-linkedin-in', '#0A66C2', 6, 1, '2026-01-07 12:58:27'),
(7, 'WhatsApp', 'https://wa.me/5351004754', 'fab fa-whatsapp', '#25D366', 7, 1, '2026-01-07 12:58:27'),
(8, 'Telegram', 'https://t.me/nexusbuy', 'fab fa-telegram', '#26A5E4', 8, 1, '2026-01-07 12:58:27'),
(9, 'Pinterest', 'https://pinterest.com/nexusbuy', 'fab fa-pinterest-p', '#BD081C', 9, 1, '2026-01-07 12:58:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reseña`
--

CREATE TABLE `reseña` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_producto_tienda` int(11) NOT NULL,
  `id_orden_detalle` int(11) DEFAULT NULL,
  `calificacion` tinyint(4) NOT NULL CHECK (`calificacion` between 1 and 5),
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comentario` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `respuesta_tienda` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_respuesta` datetime DEFAULT NULL,
  `likes` int(11) DEFAULT 0,
  `estado` enum('pendiente','aprobada','rechazada') COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `reseña`
--

INSERT INTO `reseña` (`id`, `id_usuario`, `id_producto_tienda`, `id_orden_detalle`, `calificacion`, `titulo`, `comentario`, `respuesta_tienda`, `fecha_respuesta`, `likes`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 2, 3, 1, 5, 'Excelentes zapatillas', 'Muy cómodas y buena calidad. Las uso todos los días.', '¡Gracias por tu compra! Nos alegra que te gusten.', NULL, 0, 'aprobada', '2025-11-17 02:31:04', '2025-12-14 02:06:47'),
(2, 2, 4, 2, 4, 'Buenas para running', 'Buen amortiguamiento pero se calientan un poco.', NULL, NULL, 0, 'aprobada', '2025-11-17 02:31:04', '2025-11-17 02:31:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subcategoria`
--

CREATE TABLE `subcategoria` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_categoria` int(11) NOT NULL,
  `imagen` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `orden` int(11) DEFAULT 0,
  `estado` enum('activa','inactiva') COLLATE utf8mb4_unicode_ci DEFAULT 'activa',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `subcategoria`
--

INSERT INTO `subcategoria` (`id`, `nombre`, `descripcion`, `id_categoria`, `imagen`, `orden`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'Smartphones Android', NULL, 1, NULL, 1, 'activa', '2025-11-14 17:39:29', '2025-11-14 17:39:29'),
(2, 'Computadoras de Escritorio', NULL, 4, NULL, 2, 'activa', '2025-11-14 17:39:29', '2025-11-14 17:39:29'),
(3, 'Smart TVs', NULL, 14, NULL, 3, 'activa', '2025-11-14 17:39:29', '2025-11-14 17:39:29'),
(4, 'Audífonos', NULL, 15, NULL, 4, 'activa', '2025-11-14 17:39:29', '2025-11-14 17:39:29'),
(5, 'Cámaras DSLR', NULL, 16, NULL, 5, 'activa', '2025-11-14 17:39:29', '2025-11-14 17:39:29'),
(6, 'Consolas', NULL, 17, NULL, 6, 'activa', '2025-11-14 17:39:29', '2025-11-14 17:39:29'),
(7, 'Material de Escritura', NULL, 13, NULL, 7, 'activa', '2025-11-14 17:45:36', '2025-11-14 17:45:36'),
(8, 'Papel e Impresión', NULL, 13, NULL, 8, 'activa', '2025-11-14 17:45:36', '2025-11-14 17:45:36'),
(9, 'Organizadores', NULL, 13, NULL, 9, 'activa', '2025-11-14 17:45:36', '2025-11-14 17:45:36'),
(10, 'Computadoras', NULL, 4, NULL, 10, 'activa', '2025-11-14 17:45:36', '2025-12-16 04:58:12'),
(11, 'Mobiliario de Oficina', NULL, 13, NULL, 11, 'activa', '2025-11-14 17:45:36', '2025-11-14 17:45:36'),
(12, 'Alimento para Mascotas', NULL, 10, NULL, 12, 'activa', '2025-11-14 17:45:36', '2025-11-14 17:45:36'),
(13, 'Juguetes y Accesorios', NULL, 10, NULL, 13, 'activa', '2025-11-14 18:09:54', '2025-11-14 18:09:54'),
(14, 'Salud y Cuidado', NULL, 10, NULL, 14, 'activa', '2025-11-14 18:09:54', '2025-11-14 18:09:54'),
(15, 'Transporte y Viaje', NULL, 10, NULL, 15, 'activa', '2025-11-14 18:09:54', '2025-11-14 18:09:54'),
(16, 'Aseo y Limpieza', NULL, 10, NULL, 16, 'activa', '2025-11-14 18:09:54', '2025-11-14 18:09:54'),
(17, 'Motor y Transmisión', NULL, 12, NULL, 17, 'activa', '2025-11-14 18:09:54', '2025-11-14 18:09:54'),
(18, 'Herramientas Manuales', NULL, 18, NULL, 18, 'activa', '2025-11-14 18:09:54', '2025-11-14 18:09:54'),
(19, 'Aceite de Motor', NULL, 12, NULL, 19, 'activa', '2025-11-14 18:09:54', '2025-11-14 18:09:54'),
(20, 'LLantas Nuevas', NULL, 12, NULL, 20, 'activa', '2025-11-14 18:09:54', '2025-11-14 18:09:54'),
(21, 'Audio para Auto', NULL, 12, NULL, 21, 'activa', '2025-11-14 18:09:54', '2025-11-14 18:09:54'),
(22, 'Arroz y Granos', NULL, 2, NULL, 22, 'activa', '2025-11-14 18:09:54', '2025-11-14 18:09:54'),
(23, 'Frutas y Verduras', NULL, 2, NULL, 23, 'activa', '2025-11-14 18:16:21', '2025-11-14 18:16:21'),
(24, 'Carnes y Pescados', NULL, 2, NULL, 24, 'activa', '2025-11-14 18:16:21', '2025-11-14 18:16:21'),
(25, 'Lácteos y Huevos', NULL, 2, NULL, 25, 'activa', '2025-11-14 18:16:21', '2025-11-14 18:16:21'),
(26, 'Aguas, Refrescos y Jugos', NULL, 2, NULL, 26, 'activa', '2025-11-14 18:16:21', '2025-11-14 18:16:21'),
(27, 'Juguetes', NULL, 8, NULL, 27, 'activa', '2025-11-14 18:16:21', '2025-11-14 18:16:21'),
(28, 'Juguetes Educativos', NULL, 8, NULL, 28, 'activa', '2025-11-14 18:16:21', '2025-11-14 18:16:21'),
(29, 'Juegos de Mesa', NULL, 8, NULL, 29, 'activa', '2025-11-14 18:16:21', '2025-11-14 18:16:21'),
(30, 'Juguetes de Exterior', NULL, 8, NULL, 30, 'activa', '2025-11-14 18:16:21', '2025-11-14 18:16:21'),
(31, 'Artículos para Bebés', NULL, 8, NULL, 31, 'activa', '2025-11-14 18:16:21', '2025-11-14 18:16:21'),
(32, 'Vitaminas y Suplementos', NULL, 6, NULL, 32, 'activa', '2025-11-14 18:16:21', '2025-11-14 18:16:21'),
(33, 'Cuidados Médicos', NULL, 6, NULL, 33, 'activa', '2025-11-14 18:21:59', '2025-11-14 18:21:59'),
(34, 'Cuidado para Adultos Mayores', NULL, 6, NULL, 34, 'activa', '2025-11-14 18:21:59', '2025-11-14 18:21:59'),
(35, 'Maternidad y Bebés', NULL, 6, NULL, 35, 'activa', '2025-11-14 18:21:59', '2025-11-14 18:21:59'),
(36, 'Salud Sexual', NULL, 6, NULL, 36, 'activa', '2025-11-14 18:21:59', '2025-11-14 18:21:59'),
(37, 'Ropa Deportiva', NULL, 19, NULL, 37, 'activa', '2025-11-14 18:21:59', '2025-11-14 18:21:59'),
(38, 'Deportes al Aire Libre', NULL, 19, NULL, 38, 'activa', '2025-11-14 18:21:59', '2025-11-14 18:21:59'),
(39, 'Deportes en Equipo', NULL, 19, NULL, 39, 'activa', '2025-11-14 18:21:59', '2025-11-14 18:21:59'),
(40, 'Trajes de Baño', NULL, 19, NULL, 40, 'activa', '2025-11-14 18:21:59', '2025-11-14 18:21:59'),
(41, 'Suplementos Deportivos', NULL, 19, NULL, 41, 'activa', '2025-11-14 18:21:59', '2025-11-14 18:21:59'),
(42, 'Cremas para la Piel', NULL, 3, NULL, 42, 'activa', '2025-11-14 18:21:59', '2025-11-14 18:21:59'),
(43, 'Maquillaje', NULL, 3, NULL, 43, 'activa', '2025-11-14 18:26:24', '2025-11-14 18:26:24'),
(44, 'Perfumes', NULL, 3, NULL, 44, 'activa', '2025-11-14 18:26:24', '2025-11-14 18:26:24'),
(45, 'Shampoo y Acondicionador', NULL, 3, NULL, 45, 'activa', '2025-11-14 18:26:24', '2025-11-14 18:26:24'),
(46, 'Desodorantes', NULL, 3, NULL, 46, 'activa', '2025-11-14 18:26:24', '2025-11-14 18:26:24'),
(47, 'Iphones', NULL, 1, NULL, 47, 'activa', '2025-11-14 18:26:24', '2025-11-14 18:26:24'),
(48, 'Teléfonos Básicos', NULL, 1, NULL, 48, 'activa', '2025-11-14 18:26:24', '2025-11-14 18:26:24'),
(49, 'Smartphones Reacondicionados', NULL, 1, NULL, 49, 'activa', '2025-11-14 18:26:24', '2025-11-14 18:26:24'),
(50, 'Accesorios para Teléfonos', NULL, 1, NULL, 50, 'activa', '2025-11-14 18:26:24', '2025-11-14 18:26:24'),
(51, 'Laptops', NULL, 4, NULL, 51, 'activa', '2025-11-14 18:31:29', '2025-11-14 18:31:29'),
(52, 'Tablets', NULL, 1, NULL, 52, 'activa', '2025-11-14 18:31:29', '2025-11-14 18:31:29'),
(53, 'Ultrabooks', NULL, 4, NULL, 53, 'activa', '2025-11-14 18:31:29', '2025-11-14 18:31:29'),
(54, 'Workstation', NULL, 4, NULL, 54, 'activa', '2025-11-14 18:31:29', '2025-11-14 18:31:29'),
(55, '4K/ 8K TVs', NULL, 14, NULL, 55, 'activa', '2025-11-14 18:31:29', '2025-11-14 18:31:29'),
(56, 'Proyectores', NULL, 14, NULL, 56, 'activa', '2025-11-14 18:31:29', '2025-11-14 18:31:29'),
(57, 'Barras de Sonido', NULL, 14, NULL, 57, 'activa', '2025-11-14 18:31:29', '2025-11-14 18:31:29'),
(58, 'Soporte para TVs y Video', NULL, 14, NULL, 58, 'activa', '2025-11-14 18:31:29', '2025-11-14 18:31:29'),
(59, 'Bocinas Bluetooth', NULL, 15, NULL, 59, 'activa', '2025-11-14 18:31:29', '2025-11-14 18:31:29'),
(60, 'Sistemas de Sonido', NULL, 15, NULL, 60, 'activa', '2025-11-14 18:31:29', '2025-11-14 18:31:29'),
(61, 'Equipos de Audio', NULL, 15, NULL, 61, 'activa', '2025-11-14 18:38:02', '2025-11-14 18:38:02'),
(62, 'Micrófonos', NULL, 15, NULL, 62, 'activa', '2025-11-14 18:38:02', '2025-11-14 18:38:02'),
(63, 'Cámaras Deportivas', NULL, 16, NULL, 63, 'activa', '2025-11-14 18:38:02', '2025-11-14 18:38:02'),
(64, 'Drones', NULL, 16, NULL, 64, 'activa', '2025-11-14 18:38:02', '2025-11-14 18:38:02'),
(65, 'Accesorios de Fotografía', NULL, 16, NULL, 65, 'activa', '2025-11-14 18:38:02', '2025-11-14 18:38:02'),
(66, 'Juegos Físicos', NULL, 17, NULL, 66, 'activa', '2025-11-14 18:38:02', '2025-11-14 18:38:02'),
(67, 'Juegos Digitales', NULL, 17, NULL, 67, 'activa', '2025-11-14 18:38:02', '2025-11-14 18:38:02'),
(68, 'Accesorios Gaming', NULL, 17, NULL, 68, 'activa', '2025-11-14 18:38:02', '2025-11-14 18:38:02'),
(69, 'PC Gaming', NULL, 17, NULL, 69, 'activa', '2025-11-14 18:38:02', '2025-11-14 18:38:02'),
(70, 'Impresoras', NULL, 13, NULL, 70, 'activa', '2025-11-14 18:38:02', '2025-11-14 18:38:02'),
(71, 'Escáneres', NULL, 13, NULL, 71, 'activa', '2025-11-14 18:46:26', '2025-11-14 18:46:26'),
(72, 'Calculadoras', NULL, 13, NULL, 72, 'activa', '2025-11-14 18:46:26', '2025-11-14 18:46:26'),
(73, 'Teléfonos', NULL, 13, NULL, 73, 'activa', '2025-11-14 18:46:26', '2025-11-14 18:46:26'),
(74, 'Iluminación', NULL, 9, NULL, 74, 'activa', '2025-11-14 18:46:26', '2025-11-14 18:46:26'),
(75, 'Accesorios Exteriores', NULL, 9, NULL, 75, 'activa', '2025-11-14 18:46:26', '2025-11-14 18:46:26'),
(76, 'Accesorios Interiores', NULL, 9, NULL, 76, 'activa', '2025-11-14 18:46:26', '2025-11-14 18:46:26'),
(77, 'Frenos y Suspensión', NULL, 12, NULL, 77, 'activa', '2025-11-14 18:46:26', '2025-11-14 18:46:26'),
(78, 'Herramientas Eléctricas', NULL, 18, NULL, 78, 'activa', '2025-11-14 18:46:26', '2025-11-14 18:46:26'),
(79, 'Cajas de Herramientas', NULL, 18, NULL, 79, 'activa', '2025-11-14 18:46:26', '2025-11-14 18:46:26'),
(80, 'Medición y Presión', NULL, 18, NULL, 80, 'activa', '2025-11-14 18:46:26', '2025-11-14 18:46:26'),
(81, 'Fluidos y Lubricantes', NULL, 12, NULL, 81, 'activa', '2025-11-14 18:46:26', '2025-11-14 18:46:26'),
(82, 'Aditivos', NULL, 12, NULL, 82, 'activa', '2025-11-14 18:46:26', '2025-11-14 18:46:26'),
(83, 'Limpiadores', NULL, 12, NULL, 83, 'activa', '2025-11-14 18:46:26', '2025-11-14 18:46:26'),
(84, 'Químicos Especializados', NULL, 12, NULL, 84, 'activa', '2025-11-14 18:46:26', '2025-11-14 18:46:26'),
(85, 'Rines', NULL, 12, NULL, 85, 'activa', '2025-11-14 18:46:26', '2025-11-14 18:46:26'),
(86, 'Válvulas y Balanceo', NULL, 12, NULL, 86, 'activa', '2025-11-14 18:46:26', '2025-11-14 18:46:26'),
(87, 'Cámaras de Aire', NULL, 12, NULL, 87, 'activa', '2025-11-14 18:46:26', '2025-11-14 18:46:26'),
(88, 'Reparación de Llantas', NULL, 12, NULL, 88, 'activa', '2025-11-14 18:46:26', '2025-11-14 18:46:26'),
(89, 'Aceites y Vinagres', NULL, 2, NULL, 89, 'activa', '2025-11-14 18:46:26', '2025-11-14 18:46:26'),
(90, 'Harinas y Polvos', NULL, 2, NULL, 90, 'activa', '2025-11-14 18:46:26', '2025-11-14 18:46:26'),
(91, 'Legumbres', NULL, 2, NULL, 91, 'activa', '2025-11-14 18:55:22', '2025-11-14 18:55:22'),
(92, 'Conservas', NULL, 2, NULL, 92, 'activa', '2025-11-14 18:55:22', '2025-11-14 18:55:22'),
(93, 'Bebidas Energéticas', NULL, 2, NULL, 93, 'activa', '2025-11-14 18:55:22', '2025-11-14 18:55:22'),
(94, 'Cáfe y Té', NULL, 2, NULL, 94, 'activa', '2025-11-14 18:55:22', '2025-11-14 18:55:22'),
(95, 'Bebidas Alcohólicas', NULL, 2, NULL, 95, 'activa', '2025-11-14 18:55:22', '2025-11-14 18:55:22'),
(96, 'Cuchillas y Afeitado', NULL, 3, NULL, 96, 'activa', '2025-11-14 18:55:22', '2025-11-14 18:55:22'),
(97, 'Higiene Bocal', NULL, 3, NULL, 97, 'activa', '2025-11-14 18:55:22', '2025-11-14 18:55:22'),
(98, 'Coloración', NULL, 3, NULL, 98, 'activa', '2025-11-14 18:55:22', '2025-11-14 18:55:22'),
(99, 'Accesorios para Cabello', NULL, 3, NULL, 99, 'activa', '2025-11-14 18:55:22', '2025-11-14 18:55:22'),
(100, 'Protector Solar', NULL, 3, NULL, 100, 'activa', '2025-11-14 18:55:22', '2025-11-14 18:55:22'),
(101, 'Mascarillas', NULL, 3, NULL, 101, 'activa', '2025-11-14 18:55:22', '2025-11-14 18:55:22'),
(102, 'Zapatos Deportivos', NULL, 11, NULL, 102, 'activa', '2025-11-14 18:55:22', '2025-12-16 04:59:50'),
(103, 'Equipo de Gimnasio', NULL, 19, NULL, 103, 'activa', '2025-11-14 18:55:22', '2025-11-14 18:55:22'),
(104, 'Pesas y Mancuernas', NULL, 19, NULL, 104, 'activa', '2025-11-14 18:55:22', '2025-11-14 18:55:22'),
(105, 'Toallas Deportivas', NULL, 11, NULL, 105, 'activa', '2025-11-14 18:55:22', '2025-11-14 18:55:22'),
(106, 'Flotadores', NULL, 11, NULL, 106, 'activa', '2025-11-14 18:55:22', '2025-11-14 18:55:22'),
(107, 'Accesorios de Natación', NULL, 11, NULL, 107, 'activa', '2025-11-14 18:55:22', '2025-11-14 18:55:22'),
(108, 'Goggles', NULL, 11, NULL, 108, 'activa', '2025-11-14 18:55:22', '2025-11-14 18:55:22'),
(109, 'Refrigeradores', NULL, 5, NULL, 109, 'activa', '2025-11-14 18:55:22', '2025-11-14 18:55:22'),
(110, 'Lavadoras', NULL, 5, NULL, 110, 'activa', '2025-11-14 18:55:22', '2025-11-14 18:55:22'),
(151, 'Secadoras', NULL, 5, NULL, 111, 'activa', '2025-11-14 19:28:58', '2025-12-16 04:58:13'),
(171, 'Cocinas y Hornos', NULL, 5, NULL, 112, 'activa', '2025-11-14 19:30:11', '2025-12-16 04:58:13'),
(172, 'Lavavajillas', NULL, 5, NULL, 113, 'activa', '2025-11-14 19:30:11', '2025-12-16 04:58:13'),
(173, 'Microondas', NULL, 5, NULL, 114, 'activa', '2025-11-14 19:30:11', '2025-12-16 04:58:13'),
(174, 'Licuadoras', NULL, 5, NULL, 115, 'activa', '2025-11-14 19:30:11', '2025-12-16 04:58:13'),
(175, 'Cafeteras', NULL, 5, NULL, 116, 'activa', '2025-11-14 19:30:11', '2025-12-16 04:58:13'),
(176, 'Aspiradoras', NULL, 5, NULL, 117, 'activa', '2025-11-14 19:30:11', '2025-12-16 04:58:13'),
(177, 'Plancha de Ropa', NULL, 5, NULL, 118, 'activa', '2025-11-14 19:30:11', '2025-12-16 04:58:13'),
(178, 'Ropa de Mujer', NULL, 7, NULL, 119, 'activa', '2025-11-14 19:30:11', '2025-11-14 19:30:11'),
(179, 'Ropa de Hombre', NULL, 7, NULL, 120, 'activa', '2025-11-14 19:30:11', '2025-11-14 19:30:11'),
(180, 'Ropa de Niños/as', NULL, 7, NULL, 121, 'activa', '2025-11-14 19:30:11', '2025-11-14 19:30:11'),
(181, 'Calzado', NULL, 7, NULL, 122, 'activa', '2025-11-14 19:30:11', '2025-11-14 19:30:11'),
(190, 'Bolsos, Carteras y Mochilas', NULL, 7, NULL, 123, 'activa', '2025-11-14 19:31:06', '2025-11-14 19:31:06'),
(191, 'Bisuterías', NULL, 7, NULL, 124, 'activa', '2025-11-14 19:31:06', '2025-11-14 19:31:06'),
(192, 'Accesorios de Moda', NULL, 7, NULL, 125, 'activa', '2025-11-14 19:31:06', '2025-11-14 19:31:06'),
(193, 'Muebles', NULL, 9, NULL, 126, 'activa', '2025-11-14 19:31:06', '2025-11-14 19:31:06'),
(194, 'Decoración', NULL, 9, NULL, 127, 'activa', '2025-11-14 19:31:06', '2025-11-14 19:31:06'),
(195, 'Utensilios de Cocina', NULL, 9, NULL, 128, 'activa', '2025-11-14 19:31:06', '2025-11-14 19:31:06'),
(196, 'Vajillas', NULL, 9, NULL, 129, 'activa', '2025-11-14 19:31:06', '2025-11-14 19:31:06'),
(197, 'Cristalería', NULL, 9, NULL, 130, 'activa', '2025-11-14 19:31:54', '2025-11-14 19:31:54'),
(198, 'Cubiertos', NULL, 9, NULL, 131, 'activa', '2025-11-14 19:36:12', '2025-11-14 19:36:12'),
(199, 'Accesorios de Baño', NULL, 9, NULL, 132, 'activa', '2025-11-14 19:36:12', '2025-11-14 19:36:12'),
(200, 'Adornos de Exteriores', NULL, 9, NULL, 133, 'activa', '2025-11-14 19:36:12', '2025-11-14 19:36:12'),
(201, 'Adornos de Interiores', NULL, 9, NULL, 134, 'activa', '2025-11-14 19:36:12', '2025-11-14 19:36:12'),
(202, 'Iluminarias de Interiores', NULL, 9, NULL, 135, 'activa', '2025-11-14 19:36:12', '2025-11-14 19:36:12'),
(203, 'Iluminaria de Exteriores', NULL, 9, NULL, 136, 'activa', '2025-11-14 19:36:12', '2025-11-14 19:36:12'),
(204, 'Dulce y Confituras', NULL, 2, NULL, 137, 'activa', '2025-11-14 19:36:12', '2025-11-14 19:36:12'),
(205, 'Ceriales', NULL, 2, NULL, 138, 'activa', '2025-11-14 19:36:12', '2025-11-14 19:36:12'),
(206, 'Aires Acondicionados', 'Sistemas de aire acondicionado y climatización', 5, NULL, 9, 'activa', '2025-12-16 04:59:49', '2025-12-16 04:59:49'),
(207, 'Calentadores de Agua', 'Termos, calentadores y sistemas de agua caliente', 5, NULL, 10, 'activa', '2025-12-16 04:59:49', '2025-12-16 04:59:49'),
(208, 'Extractores y Campanas', 'Extractores de cocina y campanas', 5, NULL, 11, 'activa', '2025-12-16 04:59:49', '2025-12-16 04:59:49'),
(209, 'Batidoras y Procesadores', 'Batidoras, procesadores de alimentos y robots de cocina', 5, NULL, 12, 'activa', '2025-12-16 04:59:49', '2025-12-16 04:59:49'),
(210, 'Tostadoras y Sandwicheras', 'Tostadoras, sandwicheras y grill', 5, NULL, 13, 'activa', '2025-12-16 04:59:49', '2025-12-16 04:59:49'),
(211, 'Hervidores y Teteras', 'Hervidores eléctricos y teteras', 5, NULL, 14, 'activa', '2025-12-16 04:59:49', '2025-12-16 04:59:49'),
(212, 'Ventiladores', 'Ventiladores de pie, mesa y torre', 5, NULL, 15, 'activa', '2025-12-16 04:59:49', '2025-12-16 04:59:49'),
(213, 'Calefactores', 'Calefactores eléctricos y de ambiente', 5, NULL, 16, 'activa', '2025-12-16 04:59:49', '2025-12-16 04:59:49'),
(214, 'Purificadores de Aire', 'Purificadores y humidificadores de aire', 5, NULL, 17, 'activa', '2025-12-16 04:59:49', '2025-12-16 04:59:49'),
(215, 'Cocinas de Inducción', 'Placas de inducción y cocinas eléctricas', 5, NULL, 18, 'activa', '2025-12-16 04:59:49', '2025-12-16 04:59:49'),
(216, 'Freidoras de Aire', 'Freidoras de aire y hornos de convección', 5, NULL, 19, 'activa', '2025-12-16 04:59:49', '2025-12-16 04:59:49'),
(217, 'Máquinas de Coser', 'Máquinas de coser domésticas', 5, NULL, 20, 'activa', '2025-12-16 04:59:49', '2025-12-16 04:59:49'),
(218, 'Planchas a Vapor', 'Planchas a vapor y centros de planchado', 5, NULL, 21, 'activa', '2025-12-16 04:59:49', '2025-12-16 04:59:49'),
(219, 'Cortinas y Persianas', 'Cortinas, persianas y accesorios para ventanas', 9, NULL, 137, 'activa', '2025-12-16 04:59:50', '2025-12-16 04:59:50'),
(220, 'Alfombras y Tapetes', 'Alfombras, tapetes y felpudos', 9, NULL, 138, 'activa', '2025-12-16 04:59:50', '2025-12-16 04:59:50'),
(221, 'Ropa de Cama', 'Sábanas, fundas, edredones y almohadas', 9, NULL, 139, 'activa', '2025-12-16 04:59:50', '2025-12-16 04:59:50'),
(222, 'Toallas y Textiles', 'Toallas, manteles y textiles para el hogar', 9, NULL, 140, 'activa', '2025-12-16 04:59:50', '2025-12-16 04:59:50'),
(223, 'Jardinería', 'Herramientas y accesorios para jardinería', 9, NULL, 141, 'activa', '2025-12-16 04:59:50', '2025-12-16 04:59:50'),
(224, 'Macetas y Plantas', 'Macetas, plantas naturales y artificiales', 9, NULL, 142, 'activa', '2025-12-16 04:59:50', '2025-12-16 04:59:50'),
(225, 'Muebles de Jardín', 'Muebles para exteriores y jardín', 9, NULL, 143, 'activa', '2025-12-16 04:59:50', '2025-12-16 04:59:50'),
(226, 'Barbacoas y Parrillas', 'Barbacoas, parrillas y accesorios', 9, NULL, 144, 'activa', '2025-12-16 04:59:50', '2025-12-16 04:59:50'),
(227, 'Piscinas y Accesorios', 'Piscinas inflables y accesorios', 9, NULL, 145, 'activa', '2025-12-16 04:59:50', '2025-12-16 04:59:50'),
(228, 'Herramientas de Jardín', 'Herramientas manuales para jardinería', 9, NULL, 146, 'activa', '2025-12-16 04:59:50', '2025-12-16 04:59:50'),
(229, 'Sistemas de Riego', 'Sistemas de riego automático y manual', 9, NULL, 147, 'activa', '2025-12-16 04:59:50', '2025-12-16 04:59:50'),
(230, 'Iluminación Solar', 'Luces solares para jardín y exteriores', 9, NULL, 148, 'activa', '2025-12-16 04:59:50', '2025-12-16 04:59:50'),
(231, 'Decoración Navideña', 'Decoración para fiestas y navidad', 9, NULL, 149, 'activa', '2025-12-16 04:59:50', '2025-12-16 04:59:50'),
(232, 'Organización del Hogar', 'Organizadores, cajas y contenedores', 9, NULL, 150, 'activa', '2025-12-16 04:59:50', '2025-12-16 04:59:50'),
(233, 'Limpieza del Hogar', 'Productos y herramientas de limpieza', 9, NULL, 151, 'activa', '2025-12-16 04:59:50', '2025-12-16 04:59:50'),
(308, 'Máquinas de Cardio', 'Caminadoras, bicicletas estáticas, elípticas', 19, NULL, 109, 'activa', '2025-12-16 14:40:36', '2025-12-16 14:40:36'),
(309, 'Máquinas de Fuerza', 'Máquinas de musculación y poleas', 19, NULL, 110, 'activa', '2025-12-16 14:40:36', '2025-12-16 14:40:36'),
(310, 'Bancos de Pesas', 'Bancos para ejercicio y press', 19, NULL, 111, 'activa', '2025-12-16 14:40:36', '2025-12-16 14:40:36'),
(311, 'Barras y Discos', 'Barras olímpicas, discos y pesas', 19, NULL, 112, 'activa', '2025-12-16 14:40:36', '2025-12-16 14:40:36'),
(312, 'Kettlebells', 'Pesas rusas y kettlebells', 19, NULL, 113, 'activa', '2025-12-16 14:40:36', '2025-12-16 14:40:36'),
(313, 'Bandas de Resistencia', 'Bandas elásticas y tubos de resistencia', 19, NULL, 114, 'activa', '2025-12-16 14:40:36', '2025-12-16 14:40:36'),
(314, 'Guantes y Accesorios', 'Guantes, cinturones y straps', 19, NULL, 115, 'activa', '2025-12-16 14:40:36', '2025-12-16 14:40:36'),
(315, 'Equipo para Yoga', 'Mats, bloques, correas y ruedas', 19, NULL, 116, 'activa', '2025-12-16 14:40:36', '2025-12-16 14:40:36'),
(316, 'Equipo para Pilates', 'Pelotas, aros y bandas elásticas', 19, NULL, 117, 'activa', '2025-12-16 14:40:36', '2025-12-16 14:40:36'),
(317, 'Monitores de Actividad', 'Pulsómetros, smartwatches fitness', 19, NULL, 118, 'activa', '2025-12-16 14:40:36', '2025-12-16 14:40:36'),
(318, 'Ropa de Entrenamiento', 'Ropa especializada para entrenamiento', 19, NULL, 120, 'activa', '2025-12-16 14:40:36', '2025-12-16 14:40:36'),
(319, 'Accesorios de Recuperación', 'Rodillos de espuma, pistolas de masaje', 19, NULL, 121, 'activa', '2025-12-16 14:40:36', '2025-12-16 14:40:36'),
(323, 'Baterías', 'Baterías para automóviles y cargadores', 12, NULL, 90, 'activa', '2025-12-16 14:40:37', '2025-12-16 14:40:37'),
(324, 'Filtros', 'Filtros de aire, aceite y combustible', 12, NULL, 91, 'activa', '2025-12-16 14:40:37', '2025-12-16 14:40:37'),
(325, 'Sistema Eléctrico', 'Alternadores, bujías y cables', 12, NULL, 92, 'activa', '2025-12-16 14:40:37', '2025-12-16 14:40:37'),
(326, 'Sistema de Escape', 'Mofles, catalizadores y tuberías', 12, NULL, 93, 'activa', '2025-12-16 14:40:37', '2025-12-16 14:40:37'),
(327, 'Sistema de Dirección', 'Cremalleras, bombas y líquidos', 12, NULL, 94, 'activa', '2025-12-16 14:40:37', '2025-12-16 14:40:37'),
(328, 'Sistema de Refrigeración', 'Radiadores, termostatos y mangueras', 12, NULL, 95, 'activa', '2025-12-16 14:40:37', '2025-12-16 14:40:37'),
(329, 'Accesorios Interiores', 'Fundas, alfombras y organizadores', 12, NULL, 96, 'activa', '2025-12-16 14:40:37', '2025-12-16 14:40:37'),
(330, 'Accesorios Exteriores', 'Spoilers, defensas y molduras', 12, NULL, 97, 'activa', '2025-12-16 14:40:37', '2025-12-16 14:40:37'),
(331, 'Herramientas para Auto', 'Gatos, juegos de herramientas', 12, NULL, 98, 'activa', '2025-12-16 14:40:37', '2025-12-16 14:40:37'),
(332, 'GPS y Navegación', 'Navegadores GPS y sistemas', 12, NULL, 99, 'activa', '2025-12-16 14:40:37', '2025-12-16 14:40:37'),
(333, 'Seguridad', 'Alarmas, inmovilizadores y rastreadores', 12, NULL, 100, 'activa', '2025-12-16 14:40:37', '2025-12-16 14:40:37'),
(334, 'Cuidado y Lavado', 'Ceras, shampoos y equipos de lavado', 12, NULL, 101, 'activa', '2025-12-16 14:40:37', '2025-12-16 14:40:37'),
(338, 'Pañales y Toallitas', 'Pañales desechables y toallitas húmedas', 8, NULL, 32, 'activa', '2025-12-16 14:40:37', '2025-12-16 14:40:37'),
(339, 'Alimentación', 'Biberones, chupetes y esterilizadores', 8, NULL, 33, 'activa', '2025-12-16 14:40:37', '2025-12-16 14:40:37'),
(340, 'Ropa para Bebé', 'Ropa para recién nacidos y bebés', 8, NULL, 34, 'activa', '2025-12-16 14:40:37', '2025-12-16 14:40:37'),
(341, 'Cuidado del Bebé', 'Termómetros, aspiradores nasales', 8, NULL, 35, 'activa', '2025-12-16 14:40:37', '2025-12-16 14:40:37'),
(342, 'Seguridad', 'Protectores, seguros y monitores', 8, NULL, 36, 'activa', '2025-12-16 14:40:37', '2025-12-16 14:40:37'),
(343, 'Mobiliario', 'Cunas, moisés y cambiadores', 8, NULL, 37, 'activa', '2025-12-16 14:40:37', '2025-12-16 14:40:37'),
(344, 'Transporte', 'Coches, sillas de auto y portabebés', 8, NULL, 38, 'activa', '2025-12-16 14:40:37', '2025-12-16 14:40:37'),
(345, 'Baño del Bebé', 'Tinas, toallas y productos de baño', 8, NULL, 39, 'activa', '2025-12-16 14:40:37', '2025-12-16 14:40:37'),
(346, 'Lactancia', 'Extractores, almohadas y sujetadores', 8, NULL, 40, 'activa', '2025-12-16 14:40:37', '2025-12-16 14:40:37'),
(353, 'Juguetes Educativos', 'Juguetes para desarrollo cognitivo y aprendizaje', 23, NULL, 1, 'activa', '2025-12-16 14:49:57', '2025-12-16 14:49:57'),
(354, 'Juguetes de Construcción', 'Bloques, legos y sets de construcción', 23, NULL, 2, 'activa', '2025-12-16 14:49:57', '2025-12-16 14:49:57'),
(355, 'Muñecas y Accesorios', 'Muñecas, peluches y accesorios', 23, NULL, 3, 'activa', '2025-12-16 14:49:57', '2025-12-16 14:49:57'),
(356, 'Vehículos y Pistas', 'Carros, pistas y vehículos de juguete', 23, NULL, 4, 'activa', '2025-12-16 14:49:57', '2025-12-16 14:49:57'),
(357, 'Juguetes Electrónicos', 'Juguetes con tecnología y electrónica', 23, NULL, 5, 'activa', '2025-12-16 14:49:57', '2025-12-16 14:49:57'),
(358, 'Juegos de Mesa', 'Juegos de mesa familiares y educativos', 23, NULL, 6, 'activa', '2025-12-16 14:49:57', '2025-12-16 14:49:57'),
(359, 'Juguetes de Exterior', 'Columpios, toboganes y juegos de jardín', 23, NULL, 7, 'activa', '2025-12-16 14:49:57', '2025-12-16 14:49:57'),
(360, 'Puzzles y Rompecabezas', 'Puzzles, rompecabezas y juegos de ingenio', 23, NULL, 8, 'activa', '2025-12-16 14:49:57', '2025-12-16 14:49:57'),
(361, 'Juguetes para Bebés', 'Juguetes seguros para bebés 0-3 años', 23, NULL, 9, 'activa', '2025-12-16 14:49:57', '2025-12-16 14:49:57'),
(362, 'Figuras de Acción', 'Figuras de acción y coleccionables', 23, NULL, 10, 'activa', '2025-12-16 14:49:57', '2025-12-16 14:49:57'),
(368, 'Libros de Texto', 'Libros escolares y universitarios', 24, NULL, 1, 'activa', '2025-12-16 14:49:57', '2025-12-16 14:49:57'),
(369, 'Literatura y Novela', 'Novelas, cuentos y literatura general', 24, NULL, 2, 'activa', '2025-12-16 14:49:57', '2025-12-16 14:49:57'),
(370, 'Libros Técnicos', 'Libros técnicos y profesionales', 24, NULL, 3, 'activa', '2025-12-16 14:49:57', '2025-12-16 14:49:57'),
(371, 'Libros Infantiles', 'Libros para niños y jóvenes', 24, NULL, 4, 'activa', '2025-12-16 14:49:57', '2025-12-16 14:49:57'),
(372, 'Material Escolar', 'Cuadernos, lápices, reglas, etc.', 24, NULL, 5, 'activa', '2025-12-16 14:49:57', '2025-12-16 14:49:57'),
(373, 'Enciclopedias y Diccionarios', 'Obras de referencia y consulta', 24, NULL, 6, 'activa', '2025-12-16 14:49:57', '2025-12-16 14:49:57'),
(374, 'Libros Digitales', 'E-books y contenido digital', 24, NULL, 7, 'activa', '2025-12-16 14:49:57', '2025-12-16 14:49:57'),
(375, 'Revistas y Periódicos', 'Publicaciones periódicas', 24, NULL, 8, 'activa', '2025-12-16 14:49:57', '2025-12-16 14:49:57'),
(376, 'Material de Estudio', 'Guías, resúmenes y material de apoyo', 24, NULL, 9, 'activa', '2025-12-16 14:49:57', '2025-12-16 14:49:57'),
(377, 'Libros en Otros Idiomas', 'Libros en inglés, francés, etc.', 24, NULL, 10, 'activa', '2025-12-16 14:49:57', '2025-12-16 14:49:57'),
(383, 'Guitarras y Bajos', 'Guitarras eléctricas, acústicas y bajos', 25, NULL, 1, 'activa', '2025-12-16 14:49:58', '2025-12-16 14:49:58'),
(384, 'Teclados y Pianos', 'Pianos digitales, teclados y sintetizadores', 25, NULL, 2, 'activa', '2025-12-16 14:49:58', '2025-12-16 14:49:58'),
(385, 'Instrumentos de Viento', 'Saxofones, trompetas, flautas, etc.', 25, NULL, 3, 'activa', '2025-12-16 14:49:58', '2025-12-16 14:49:58'),
(386, 'Baterías y Percusión', 'Baterías, congas, bongós y percusión', 25, NULL, 4, 'activa', '2025-12-16 14:49:58', '2025-12-16 14:49:58'),
(387, 'Instrumentos de Cuerda', 'Violines, violas, cellos, etc.', 25, NULL, 5, 'activa', '2025-12-16 14:49:58', '2025-12-16 14:49:58'),
(388, 'Equipos de Audio Profesional', 'Amplificadores, mezcladoras, micrófonos', 25, NULL, 6, 'activa', '2025-12-16 14:49:58', '2025-12-16 14:49:58'),
(389, 'Accesorios Musicales', 'Cuerdas, baquetas, estuches, atriles', 25, NULL, 7, 'activa', '2025-12-16 14:49:58', '2025-12-16 14:49:58'),
(390, 'Instrumentos Tradicionales', 'Instrumentos folklóricos y tradicionales', 25, NULL, 8, 'activa', '2025-12-16 14:49:58', '2025-12-16 14:49:58'),
(391, 'Estudios de Grabación', 'Equipos para home studio', 25, NULL, 9, 'activa', '2025-12-16 14:49:58', '2025-12-16 14:49:58'),
(392, 'Instrumentos para Niños', 'Instrumentos infantiles y educativos', 25, NULL, 10, 'activa', '2025-12-16 14:49:58', '2025-12-16 14:49:58'),
(398, 'Anillos', 'Anillos de compromiso, sortijas, alianzas', 26, NULL, 1, 'activa', '2025-12-16 14:49:58', '2025-12-16 14:49:58'),
(399, 'Collares y Cadenas', 'Collares, cadenas, dijes y gargantillas', 26, NULL, 2, 'activa', '2025-12-16 14:49:58', '2025-12-16 14:49:58'),
(400, 'Pulseras y Brazaletes', 'Pulseras, brazaletes y tobilleras', 26, NULL, 3, 'activa', '2025-12-16 14:49:58', '2025-12-16 14:49:58'),
(401, 'Relojes de Pulsera', 'Relojes analógicos y digitales', 26, NULL, 4, 'activa', '2025-12-16 14:49:58', '2025-12-16 14:49:58'),
(402, 'Aretes y Pendientes', 'Aretes, pendientes y zarcillos', 26, NULL, 5, 'activa', '2025-12-16 14:49:58', '2025-12-16 14:49:58'),
(403, 'Joyas de Oro', 'Joyas en oro de 10k, 14k, 18k', 26, NULL, 6, 'activa', '2025-12-16 14:49:58', '2025-12-16 14:49:58'),
(404, 'Joyas de Plata', 'Joyas en plata esterlina y ley', 26, NULL, 7, 'activa', '2025-12-16 14:49:58', '2025-12-16 14:49:58'),
(405, 'Bisutería', 'Joyas de fantasía y accesorios', 26, NULL, 8, 'activa', '2025-12-16 14:49:58', '2025-12-16 14:49:58'),
(406, 'Relojes Inteligentes', 'Smartwatches y relojes deportivos', 26, NULL, 9, 'activa', '2025-12-16 14:49:58', '2025-12-16 14:49:58'),
(407, 'Joyas para Hombres', 'Relojes, anillos y collares masculinos', 26, NULL, 10, 'activa', '2025-12-16 14:49:58', '2025-12-16 14:49:58'),
(413, 'Maletas y Equipaje', 'Maletas, mochilas y bolsos de viaje', 27, NULL, 1, 'activa', '2025-12-16 14:49:58', '2025-12-16 14:49:58'),
(414, 'Accesorios de Viaje', 'Neceseres, organizadores y kits', 27, NULL, 2, 'activa', '2025-12-16 14:49:58', '2025-12-16 14:49:58'),
(415, 'Ropa de Viaje', 'Ropa especializada para viajes', 27, NULL, 3, 'activa', '2025-12-16 14:49:58', '2025-12-16 14:49:58'),
(416, 'Artículos de Camping', 'Tiendas, sleeping bags y equipo camping', 27, NULL, 4, 'activa', '2025-12-16 14:49:58', '2025-12-16 14:49:58'),
(417, 'Navegación GPS', 'GPS portátiles y dispositivos de navegación', 27, NULL, 5, 'activa', '2025-12-16 14:49:58', '2025-12-16 14:49:58'),
(418, 'Seguridad para Viaje', 'Candados, alarmas y seguros', 27, NULL, 6, 'activa', '2025-12-16 14:49:58', '2025-12-16 14:49:58'),
(419, 'Adaptadores y Cargadores', 'Adaptadores universales y cargadores', 27, NULL, 7, 'activa', '2025-12-16 14:49:58', '2025-12-16 14:49:58'),
(420, 'Artículos de Playa', 'Sombrillas, tumbonas, cooler', 27, NULL, 8, 'activa', '2025-12-16 14:49:58', '2025-12-16 14:49:58'),
(421, 'Guías y Mapas', 'Guías de viaje y mapas turísticos', 27, NULL, 9, 'activa', '2025-12-16 14:49:58', '2025-12-16 14:49:58'),
(422, 'Regalos Souvenir', 'Recuerdos y regalos típicos', 27, NULL, 10, 'activa', '2025-12-16 14:49:58', '2025-12-16 14:49:58'),
(423, 'Suministros Industriales', NULL, 28, NULL, 1, 'activa', '2026-01-15 00:34:28', '2026-01-15 00:34:28'),
(424, 'Artes y Manualidades', NULL, 29, NULL, 2, 'activa', '2026-01-15 00:34:28', '2026-01-15 00:34:28'),
(425, 'Sex Shop', NULL, 30, NULL, 3, 'activa', '2026-01-15 00:34:28', '2026-01-15 00:34:28'),
(426, 'Productos Ecológicos', NULL, 31, NULL, 4, 'activa', '2026-01-15 00:34:28', '2026-01-15 00:34:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `suscripcion_ofertas`
--

CREATE TABLE `suscripcion_ofertas` (
  `id` int(11) NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_confirmacion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `confirmada` tinyint(1) DEFAULT 0,
  `frecuencia` enum('diaria','semanal','mensual') COLLATE utf8mb4_unicode_ci DEFAULT 'semanal',
  `estado` enum('activa','inactiva','cancelada') COLLATE utf8mb4_unicode_ci DEFAULT 'activa',
  `fecha_confirmacion` datetime DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `suscripcion_ofertas`
--

INSERT INTO `suscripcion_ofertas` (`id`, `email`, `nombre`, `token_confirmacion`, `confirmada`, `frecuencia`, `estado`, `fecha_confirmacion`, `fecha_creacion`) VALUES
(6, 'noeldavidchaconsanchez@gmail.com', 'Noel Chacón', NULL, 1, 'diaria', 'activa', '2025-12-30 02:57:47', '2025-12-30 07:37:49'),
(7, 'asd@gmail.com', 'Juan', '0a6be7f26fea8886a85c87527a6091d7104e4dbfdd907cc82a5bd2c16d2128dd', 0, 'diaria', 'activa', NULL, '2025-12-31 00:20:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tienda`
--

CREATE TABLE `tienda` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_usuario_propietario` int(11) NOT NULL,
  `id_municipio` int(11) NOT NULL,
  `direccion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sitio_web` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'default_store_logo.png',
  `banner` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `redes_sociales` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`redes_sociales`)),
  `politicas` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`politicas`)),
  `calificacion_promedio` decimal(3,2) DEFAULT 0.00,
  `estado` enum('activa','inactiva','pendiente','suspendida') COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tienda`
--

INSERT INTO `tienda` (`id`, `nombre`, `descripcion`, `id_usuario_propietario`, `id_municipio`, `direccion`, `telefono`, `email`, `sitio_web`, `logo`, `banner`, `redes_sociales`, `politicas`, `calificacion_promedio`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'TechStore Cuba', 'Tienda especializada en tecnología y electrónica', 2, 42, 'Calle 23 #456 entre L y M', '+5371234567', 'ventas@techstore.cu', 'https://techstore.cu', 'techstore_logo.png', 'techstore_banner.jpg', '{\"facebook\": \"techstorecuba\", \"instagram\": \"techstore_cu\", \"twitter\": \"techstorecu\"}', '{\"devoluciones\": \"30 días\", \"garantia\": \"1 año\", \"envios\": \"Gratis en compras > $50\"}', '4.70', 'activa', '2025-11-17 02:15:55', '2026-01-24 20:26:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_historial`
--

CREATE TABLE `tipo_historial` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icono` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'fas fa-circle',
  `estado` enum('activo','inactivo') COLLATE utf8mb4_unicode_ci DEFAULT 'activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tipo_historial`
--

INSERT INTO `tipo_historial` (`id`, `nombre`, `icono`, `estado`, `fecha_creacion`) VALUES
(1, 'Creación', '<i class=\"fas fa-plus-circle\"></i>', 'activo', '2025-12-14 18:35:25'),
(2, 'Edición', '<i class=\"fas fa-edit\"></i>', 'activo', '2025-12-14 18:35:25'),
(3, 'Eliminación', '<i class=\"fas fa-trash-alt\"></i>', 'activo', '2025-12-14 18:35:25'),
(4, 'Login', '<i class=\"fas fa-sign-in-alt\"></i>', 'activo', '2025-12-14 18:35:25'),
(5, 'Logout', '<i class=\"fas fa-sign-out-alt\"></i>', 'activo', '2025-12-14 18:35:25'),
(6, 'Compra', '<i class=\"fas fa-shopping-cart\"></i>', 'activo', '2025-12-14 18:35:25'),
(7, 'Reseña', '<i class=\"fas fa-star\"></i>', 'activo', '2025-12-14 18:35:25'),
(8, 'Dirección', '<i class=\"fas fa-map-marker-alt\"></i>', 'activo', '2025-12-14 18:35:25'),
(9, 'Sistema', '<i class=\"fas fa-cog\"></i>', 'activo', '2025-12-14 18:35:25'),
(10, 'Registro', '<i class=\"fas fa-user-plus\"></i>', 'activo', '2025-12-14 18:35:25'),
(11, 'Pago', '<i class=\"fas fa-credit-card\"></i>', 'activo', '2025-12-14 18:35:25'),
(12, 'Envío', '<i class=\"fas fa-truck\"></i>', 'activo', '2025-12-14 18:35:25'),
(13, 'Cambio contraseña', '<i class=\"fas fa-key\"></i>', 'activo', '2025-12-14 18:35:25'),
(14, 'Verificación', '<i class=\"fas fa-check-circle\"></i>', 'activo', '2025-12-14 18:35:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_usuario`
--

CREATE TABLE `tipo_usuario` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nivel_permisos` int(11) DEFAULT 1,
  `estado` enum('activo','inactivo') COLLATE utf8mb4_unicode_ci DEFAULT 'activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tipo_usuario`
--

INSERT INTO `tipo_usuario` (`id`, `nombre`, `descripcion`, `nivel_permisos`, `estado`, `fecha_creacion`) VALUES
(1, 'admin', 'Administrador principal del sistema', 100, 'activo', '2025-11-14 14:54:29'),
(2, 'cliente', 'Usuario cliente que realiza compras', 1, 'activo', '2025-11-14 14:54:29'),
(3, 'vendedor', 'Usuario que gestiona una tienda', 50, 'activo', '2025-11-14 14:54:29'),
(4, 'empleado', 'Empleado con permisos limitados', 25, 'activo', '2025-11-14 14:54:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transaccion_pago`
--

CREATE TABLE `transaccion_pago` (
  `id` int(11) NOT NULL,
  `id_orden` int(11) NOT NULL,
  `id_metodo_pago` int(11) NOT NULL,
  `referencia_externa` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monto` decimal(12,2) NOT NULL CHECK (`monto` >= 0),
  `moneda` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT 'CUP',
  `estado` enum('pendiente','completada','fallida','reembolsada') COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente',
  `respuesta_gateway` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`respuesta_gateway`)),
  `fecha_transaccion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transferencia_pagos`
--

CREATE TABLE `transferencia_pagos` (
  `id` int(11) NOT NULL,
  `id_orden` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `banco` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_transferencia` date NOT NULL,
  `numero_transaccion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `saldo_restante` decimal(10,2) DEFAULT NULL,
  `numero_tarjeta_beneficiario` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('pendiente','verificado','rechazado') COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente',
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `unidad_medida`
--

CREATE TABLE `unidad_medida` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abreviatura` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('peso','volumen','unidad','longitud','area') COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('activa','inactiva') COLLATE utf8mb4_unicode_ci DEFAULT 'activa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `unidad_medida`
--

INSERT INTO `unidad_medida` (`id`, `nombre`, `abreviatura`, `tipo`, `estado`) VALUES
(1, 'Gramo', 'g', 'peso', 'activa'),
(2, 'Kilogramo', 'kg', 'peso', 'activa'),
(3, 'Libra', 'lb', 'peso', 'activa'),
(4, 'Litro', 'L', 'volumen', 'activa'),
(5, 'Mililitro', 'ml', 'volumen', 'activa'),
(6, 'Unidad', 'u', 'unidad', 'activa'),
(7, 'Paquete', 'pqt', 'unidad', 'activa'),
(8, 'Caja', 'caja', 'unidad', 'activa'),
(9, 'Centímetro', 'cm', 'longitud', 'activa'),
(10, 'Metro', 'm', 'longitud', 'activa');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombres` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dni` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `genero` enum('Masculino','Femenino','Otro','Prefiero no decir') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'default_avatar.png',
  `id_tipo_usuario` int(11) NOT NULL DEFAULT 2,
  `email_verificado` tinyint(1) DEFAULT 0,
  `token_verificacion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_recuperacion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_expiracion_token` datetime DEFAULT NULL,
  `intentos_recuperacion` int(11) NOT NULL DEFAULT 0,
  `fecha_ultimo_intento` datetime DEFAULT NULL,
  `ultimo_login` datetime DEFAULT NULL,
  `estado` enum('activo','inactivo','suspendido','pendiente_verificacion') COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente_verificacion',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_seen` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `chat_status` enum('online','offline','away') COLLATE utf8mb4_unicode_ci DEFAULT 'offline'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `username`, `email`, `password_hash`, `nombres`, `apellidos`, `dni`, `telefono`, `fecha_nacimiento`, `genero`, `avatar`, `id_tipo_usuario`, `email_verificado`, `token_verificacion`, `token_recuperacion`, `fecha_expiracion_token`, `intentos_recuperacion`, `fecha_ultimo_intento`, `ultimo_login`, `estado`, `fecha_creacion`, `fecha_actualizacion`, `last_seen`, `chat_status`) VALUES
(1, 'vendedor', 'vendedor@gmail.com', '$2y$10$XdkxQ47IR7nHAWYTjipuz.LCzAiUqEKoTd/QxKA44tIoHsmjzoj5K', 'Vendedor 1', '', NULL, NULL, NULL, NULL, 'default_avatar.png', 3, 1, NULL, NULL, NULL, 0, NULL, '2026-01-17 02:10:56', 'pendiente_verificacion', '2026-01-04 06:25:09', '2026-01-17 07:10:56', '2026-01-17 07:10:56', 'offline'),
(2, 'cliente', 'noeldavidchaconsanchez@gmail.com', '$2y$10$XdkxQ47IR7nHAWYTjipuz.LCzAiUqEKoTd/QxKA44tIoHsmjzoj5K', 'Juan', 'Torres', '12345678901', '12345678', '1998-12-21', 'Masculino', '6930d66d1f92f-884b9aac9ac8b5e3124457c2edf16eb6.jpg', 2, 1, NULL, 'b843ecac35de15c8dd4f45b0b5c12e2386d9eafcbe48c1fa08b125967b4d388c', '2026-01-03 02:02:09', 2, '2026-01-03 01:02:09', '2026-02-01 01:00:58', 'activo', '2025-11-15 06:04:48', '2026-02-01 06:00:58', '2026-02-01 06:00:58', 'offline');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_configuracion`
--

CREATE TABLE `usuario_configuracion` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_moneda` int(11) NOT NULL,
  `idioma` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'es',
  `tema` enum('claro','oscuro','auto') COLLATE utf8mb4_unicode_ci DEFAULT 'claro',
  `notificaciones_email` tinyint(1) DEFAULT 1,
  `notificaciones_push` tinyint(1) DEFAULT 1,
  `newsletter` tinyint(1) DEFAULT 0,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `densidad` enum('comoda','normal','compacta') COLLATE utf8mb4_unicode_ci DEFAULT 'normal',
  `privacy_profile_public` tinyint(1) DEFAULT 1,
  `privacy_activity_public` tinyint(1) DEFAULT 0,
  `privacy_searchable` tinyint(1) DEFAULT 1,
  `privacy_data_sharing` tinyint(1) DEFAULT 1,
  `notificaciones_productos` tinyint(1) DEFAULT 0,
  `notificaciones_resenas` tinyint(1) DEFAULT 1,
  `ultima_exportacion` datetime DEFAULT NULL,
  `exportaciones_realizadas` int(11) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario_configuracion`
--

INSERT INTO `usuario_configuracion` (`id`, `id_usuario`, `id_moneda`, `idioma`, `tema`, `notificaciones_email`, `notificaciones_push`, `newsletter`, `fecha_actualizacion`, `densidad`, `privacy_profile_public`, `privacy_activity_public`, `privacy_searchable`, `privacy_data_sharing`, `notificaciones_productos`, `notificaciones_resenas`, `ultima_exportacion`, `exportaciones_realizadas`, `fecha_creacion`) VALUES
(2, 2, 1, 'es', 'claro', 1, 1, 0, '2025-12-31 05:27:49', 'normal', 1, 0, 1, 1, 0, 1, NULL, 0, '2025-12-31 05:27:49');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_direccion`
--

CREATE TABLE `usuario_direccion` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `alias` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_municipio` int(11) NOT NULL,
  `codigo_postal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono_contacto` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instrucciones_entrega` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `es_principal` tinyint(1) DEFAULT 0,
  `estado` enum('activa','inactiva') COLLATE utf8mb4_unicode_ci DEFAULT 'activa',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario_direccion`
--

INSERT INTO `usuario_direccion` (`id`, `id_usuario`, `alias`, `direccion`, `id_municipio`, `codigo_postal`, `telefono_contacto`, `instrucciones_entrega`, `es_principal`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 2, 'cliente', 'Neptuno #616B /Gervacio y Escobar', 36, '10400', '12345678', NULL, 0, 'inactiva', '2025-11-17 02:25:20', '2025-12-17 18:25:24'),
(3, 2, 'Dirección 2025-12-17 06:38:11', 'Lois', 36, NULL, NULL, NULL, 1, 'inactiva', '2025-12-17 05:38:11', '2025-12-17 05:51:17'),
(5, 2, 'Casa', 'Calle 26, Habana del Este, La Habana', 36, NULL, '12345678', '', 1, 'activa', '2025-12-17 18:25:11', '2026-02-01 06:29:25'),
(6, 2, 'Trabajo', 'Calle 26', 41, NULL, NULL, '', 0, 'activa', '2025-12-18 04:21:14', '2025-12-18 04:21:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_exportaciones`
--

CREATE TABLE `usuario_exportaciones` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo_exportacion` enum('perfil','pedidos','resenas','direcciones','preferencias','completa') COLLATE utf8mb4_unicode_ci NOT NULL,
  `formato` enum('json','csv','pdf','xml') COLLATE utf8mb4_unicode_ci DEFAULT 'json',
  `tamano_archivo` int(11) DEFAULT NULL COMMENT 'Tamaño en bytes',
  `ruta_archivo` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ruta donde se guardó el archivo',
  `datos_exportados` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Resumen de datos exportados',
  `ip_solicitud` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_exportacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('completada','fallida','pendiente') COLLATE utf8mb4_unicode_ci DEFAULT 'completada'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_metodo_pago`
--

CREATE TABLE `usuario_metodo_pago` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_metodo_pago` int(11) NOT NULL,
  `alias` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `datos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`datos`)),
  `es_predeterminado` tinyint(1) DEFAULT 0,
  `estado` enum('activo','inactivo') COLLATE utf8mb4_unicode_ci DEFAULT 'activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_notificaciones`
--

CREATE TABLE `usuario_notificaciones` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo_notificacion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `canal` enum('email','push','sms','inapp') COLLATE utf8mb4_unicode_ci DEFAULT 'email',
  `frecuencia` enum('inmediata','diaria','semanal','mensual') COLLATE utf8mb4_unicode_ci DEFAULT 'inmediata',
  `activo` tinyint(1) DEFAULT 1,
  `preferencia_personalizada` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Configuraciones específicas en JSON',
  `ultimo_envio` datetime DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_privacidad`
--

CREATE TABLE `usuario_privacidad` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `perfil_publico` tinyint(1) DEFAULT 1,
  `actividad_publica` tinyint(1) DEFAULT 0,
  `aparecer_busquedas` tinyint(1) DEFAULT 1,
  `compartir_datos` tinyint(1) DEFAULT 1,
  `verificacion_dos_pasos` tinyint(1) DEFAULT 0,
  `sesiones_activas` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'JSON con sesiones activas',
  `historial_navegacion` tinyint(1) DEFAULT 1 COMMENT 'Guardar historial de navegación',
  `cookies_analiticas` tinyint(1) DEFAULT 1,
  `cookies_marketing` tinyint(1) DEFAULT 0,
  `politica_aceptada` tinyint(1) DEFAULT 0,
  `fecha_aceptacion` datetime DEFAULT NULL,
  `ultima_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `version_politica` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_conversaciones_activas`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_conversaciones_activas` (
`id` varchar(32)
,`usuario_id` int(11)
,`nombre_usuario` varchar(100)
,`email_usuario` varchar(100)
,`agente_asignado` int(11)
,`estado` enum('en_espera','activa','cerrada','resuelta')
,`ultimo_mensaje` timestamp
,`fecha_inicio` timestamp
,`fecha_cierre` timestamp
,`asunto` varchar(200)
,`categoria` varchar(50)
,`valoracion` tinyint(4)
,`comentario_cierre` text
,`nombre_agente` varchar(100)
,`estado_agente` enum('disponible','ocupado','offline')
,`carga_agente` int(11)
,`mensajes_sin_leer` bigint(21)
,`ultimo_mensaje_real` timestamp
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_estadisticas_chat`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_estadisticas_chat` (
`fecha` date
,`total_conversaciones` bigint(21)
,`conversaciones_resueltas` decimal(22,0)
,`conversaciones_cerradas` decimal(22,0)
,`promedio_valoracion` decimal(7,4)
,`total_mensajes` bigint(21)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_productos_oferta_actual`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_productos_oferta_actual` (
`id` int(11)
,`id_producto` int(11)
,`id_tienda` int(11)
,`precio` decimal(12,2)
,`descuento_porcentaje` decimal(19,2)
,`precio_final` decimal(12,2)
,`envio_gratis` tinyint(4)
,`stock` int(11)
,`estado` varchar(10)
,`tipo_oferta` varchar(17)
,`fecha_fin_oferta` datetime
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_chat_conversaciones_activas`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_chat_conversaciones_activas` (
`id` varchar(32)
,`nombre_usuario` varchar(100)
,`email_usuario` varchar(100)
,`asunto` varchar(200)
,`categoria` varchar(50)
,`estado` enum('en_espera','activa','cerrada','resuelta')
,`agente_asignado` int(11)
,`nombre_agente` varchar(100)
,`fecha_inicio` timestamp
,`ultimo_mensaje` timestamp
,`mensajes_sin_leer` bigint(21)
,`ultimo_mensaje_texto` mediumtext
,`ultimo_tipo` varchar(20)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_chat_estadisticas`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_chat_estadisticas` (
`conversaciones_activas` bigint(21)
,`conversaciones_espera` bigint(21)
,`conversaciones_hoy` bigint(21)
,`agentes_disponibles` bigint(21)
,`agentes_ocupados` bigint(21)
,`mensajes_usuario_hoy` bigint(21)
,`mensajes_agente_hoy` bigint(21)
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `es_publica` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `wishlist`
--

INSERT INTO `wishlist` (`id`, `id_usuario`, `nombre`, `descripcion`, `es_publica`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 2, 'Tecnología Deseada', 'Productos de tecnología que quiero comprar', 0, '2025-11-17 02:22:39', '2025-11-17 02:22:39');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `wishlist_producto`
--

CREATE TABLE `wishlist_producto` (
  `id` int(11) NOT NULL,
  `id_wishlist` int(11) NOT NULL,
  `id_producto_tienda` int(11) NOT NULL,
  `fecha_agregado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `wishlist_producto`
--

INSERT INTO `wishlist_producto` (`id`, `id_wishlist`, `id_producto_tienda`, `fecha_agregado`) VALUES
(1, 1, 1, '2025-11-17 02:23:01'),
(2, 1, 2, '2025-11-17 02:23:01');

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_conversaciones_activas`
--
DROP TABLE IF EXISTS `vista_conversaciones_activas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_conversaciones_activas`  AS SELECT `c`.`id` AS `id`, `c`.`usuario_id` AS `usuario_id`, `c`.`nombre_usuario` AS `nombre_usuario`, `c`.`email_usuario` AS `email_usuario`, `c`.`agente_asignado` AS `agente_asignado`, `c`.`estado` AS `estado`, `c`.`ultimo_mensaje` AS `ultimo_mensaje`, `c`.`fecha_inicio` AS `fecha_inicio`, `c`.`fecha_cierre` AS `fecha_cierre`, `c`.`asunto` AS `asunto`, `c`.`categoria` AS `categoria`, `c`.`valoracion` AS `valoracion`, `c`.`comentario_cierre` AS `comentario_cierre`, `a`.`nombre_agente` AS `nombre_agente`, `a`.`estado` AS `estado_agente`, `a`.`conversaciones_activas` AS `carga_agente`, (select count(0) from `chat_mensajes` `m` where `m`.`conversacion_id` = `c`.`id` and `m`.`tipo` = 'usuario' and `m`.`leido` = 0) AS `mensajes_sin_leer`, (select max(`m`.`fecha_envio`) from `chat_mensajes` `m` where `m`.`conversacion_id` = `c`.`id`) AS `ultimo_mensaje_real` FROM (`chat_conversaciones` `c` left join `chat_agentes` `a` on(`c`.`agente_asignado` = `a`.`id`)) WHERE `c`.`estado` in ('activa','en_espera') ORDER BY `c`.`ultimo_mensaje` DESC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_estadisticas_chat`
--
DROP TABLE IF EXISTS `vista_estadisticas_chat`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_estadisticas_chat`  AS SELECT cast(`c`.`fecha_inicio` as date) AS `fecha`, count(0) AS `total_conversaciones`, sum(case when `c`.`estado` = 'resuelta' then 1 else 0 end) AS `conversaciones_resueltas`, sum(case when `c`.`estado` = 'cerrada' then 1 else 0 end) AS `conversaciones_cerradas`, avg(`c`.`valoracion`) AS `promedio_valoracion`, (select count(0) from `chat_mensajes` where cast(`chat_mensajes`.`fecha_envio` as date) = cast(`c`.`fecha_inicio` as date)) AS `total_mensajes` FROM `chat_conversaciones` AS `c` GROUP BY cast(`c`.`fecha_inicio` as date) ORDER BY cast(`c`.`fecha_inicio` as date) DESC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_productos_oferta_actual`
--
DROP TABLE IF EXISTS `vista_productos_oferta_actual`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_productos_oferta_actual`  AS SELECT `pt`.`id` AS `id`, `pt`.`id_producto` AS `id_producto`, `pt`.`id_tienda` AS `id_tienda`, `pt`.`precio` AS `precio`, `pt`.`descuento_porcentaje` AS `descuento_porcentaje`, `pt`.`precio_final` AS `precio_final`, `pt`.`envio_gratis` AS `envio_gratis`, `pt`.`stock` AS `stock`, `pt`.`estado` AS `estado`, 'descuento_regular' AS `tipo_oferta`, NULL AS `fecha_fin_oferta` FROM `producto_tienda` AS `pt` WHERE `pt`.`descuento_porcentaje` > 0 AND `pt`.`estado` = 'activo' ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_chat_conversaciones_activas`
--
DROP TABLE IF EXISTS `vw_chat_conversaciones_activas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_chat_conversaciones_activas`  AS SELECT `cc`.`id` AS `id`, `cc`.`nombre_usuario` AS `nombre_usuario`, `cc`.`email_usuario` AS `email_usuario`, `cc`.`asunto` AS `asunto`, `cc`.`categoria` AS `categoria`, `cc`.`estado` AS `estado`, `cc`.`agente_asignado` AS `agente_asignado`, `ca`.`nombre_agente` AS `nombre_agente`, `cc`.`fecha_inicio` AS `fecha_inicio`, `cc`.`ultimo_mensaje` AS `ultimo_mensaje`, (select count(0) from `chat_mensajes` `cm` where `cm`.`conversacion_id` = `cc`.`id` and `cm`.`tipo` = 'agente' and `cm`.`leido` = 0) AS `mensajes_sin_leer`, (select `cm`.`mensaje` from `chat_mensajes` `cm` where `cm`.`conversacion_id` = `cc`.`id` order by `cm`.`fecha_envio` desc limit 1) AS `ultimo_mensaje_texto`, (select `cm`.`tipo` from `chat_mensajes` `cm` where `cm`.`conversacion_id` = `cc`.`id` order by `cm`.`fecha_envio` desc limit 1) AS `ultimo_tipo` FROM (`chat_conversaciones` `cc` left join `chat_agentes` `ca` on(`cc`.`agente_asignado` = `ca`.`id`)) WHERE `cc`.`estado` in ('en_espera','activa') ORDER BY `cc`.`ultimo_mensaje` DESC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_chat_estadisticas`
--
DROP TABLE IF EXISTS `vw_chat_estadisticas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_chat_estadisticas`  AS SELECT (select count(0) from `chat_conversaciones` where `chat_conversaciones`.`estado` = 'activa') AS `conversaciones_activas`, (select count(0) from `chat_conversaciones` where `chat_conversaciones`.`estado` = 'en_espera') AS `conversaciones_espera`, (select count(0) from `chat_conversaciones` where cast(`chat_conversaciones`.`fecha_inicio` as date) = curdate()) AS `conversaciones_hoy`, (select count(0) from `chat_agentes` where `chat_agentes`.`estado` = 'disponible') AS `agentes_disponibles`, (select count(0) from `chat_agentes` where `chat_agentes`.`estado` = 'ocupado') AS `agentes_ocupados`, (select count(0) from `chat_mensajes` where cast(`chat_mensajes`.`fecha_envio` as date) = curdate() and `chat_mensajes`.`tipo` = 'usuario') AS `mensajes_usuario_hoy`, (select count(0) from `chat_mensajes` where cast(`chat_mensajes`.`fecha_envio` as date) = curdate() and `chat_mensajes`.`tipo` = 'agente') AS `mensajes_agente_hoy` ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `bundle`
--
ALTER TABLE `bundle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_bundle_tienda` (`tienda_id`),
  ADD KEY `idx_bundle_fechas` (`fecha_inicio`,`fecha_fin`),
  ADD KEY `idx_bundle_estado` (`estado`),
  ADD KEY `idx_bundle_activo` (`estado`,`fecha_inicio`,`fecha_fin`);

--
-- Indices de la tabla `bundle_producto`
--
ALTER TABLE `bundle_producto`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_bundle_producto` (`bundle_id`,`producto_tienda_id`),
  ADD KEY `fk_bundle_producto_producto` (`producto_tienda_id`);

--
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_carrito_item` (`id_usuario`,`session_id`,`id_producto_tienda`),
  ADD KEY `id_producto_tienda` (`id_producto_tienda`),
  ADD KEY `idx_session` (`session_id`),
  ADD KEY `idx_usuario` (`id_usuario`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `categoria_oferta`
--
ALTER TABLE `categoria_oferta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_categoria_oferta` (`categoria_id`),
  ADD KEY `idx_categoria_oferta_estado` (`estado`,`orden`),
  ADD KEY `idx_categoria_oferta_activa` (`estado`,`fecha_inicio`,`fecha_fin`);

--
-- Indices de la tabla `chat_agentes`
--
ALTER TABLE `chat_agentes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_usuario_agente` (`usuario_id`),
  ADD KEY `idx_usuario_agente` (`usuario_id`),
  ADD KEY `idx_estado_agente` (`estado`),
  ADD KEY `idx_ultima_actividad` (`ultima_actividad`);

--
-- Indices de la tabla `chat_configuracion`
--
ALTER TABLE `chat_configuracion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_clave` (`clave`),
  ADD KEY `idx_clave` (`clave`);

--
-- Indices de la tabla `chat_conversaciones`
--
ALTER TABLE `chat_conversaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_email` (`email_usuario`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_agente` (`agente_asignado`),
  ADD KEY `idx_fecha_inicio` (`fecha_inicio`);

--
-- Indices de la tabla `chat_mensajes`
--
ALTER TABLE `chat_mensajes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_conversacion` (`conversacion_id`),
  ADD KEY `idx_tipo` (`tipo`),
  ADD KEY `idx_fecha_envio` (`fecha_envio`);

--
-- Indices de la tabla `chat_notificaciones`
--
ALTER TABLE `chat_notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_notificacion` (`usuario_id`),
  ADD KEY `idx_conversacion_notificacion` (`conversacion_id`),
  ADD KEY `idx_leida` (`leida`),
  ADD KEY `idx_fecha_creacion` (`fecha_creacion`);

--
-- Indices de la tabla `configuracion_sitio`
--
ALTER TABLE `configuracion_sitio`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clave` (`clave`),
  ADD KEY `idx_clave` (`clave`),
  ADD KEY `idx_categoria` (`categoria`);

--
-- Indices de la tabla `contacto_mensajes`
--
ALTER TABLE `contacto_mensajes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `fk_contacto_usuario` (`id_usuario`);

--
-- Indices de la tabla `cupon`
--
ALTER TABLE `cupon`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `idx_codigo` (`codigo`),
  ADD KEY `idx_fechas` (`fecha_inicio`,`fecha_expiracion`);

--
-- Indices de la tabla `cupon_producto`
--
ALTER TABLE `cupon_producto`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cupon_producto` (`id_cupon`,`id_producto_tienda`),
  ADD KEY `id_producto_tienda` (`id_producto_tienda`);

--
-- Indices de la tabla `cupon_usado`
--
ALTER TABLE `cupon_usado`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cupon_orden` (`id_cupon`,`id_orden`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_orden` (`id_orden`);

--
-- Indices de la tabla `favorito`
--
ALTER TABLE `favorito`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_favorito` (`id_usuario`,`id_producto_tienda`),
  ADD KEY `id_producto_tienda` (`id_producto_tienda`),
  ADD KEY `idx_usuario` (`id_usuario`);

--
-- Indices de la tabla `historial`
--
ALTER TABLE `historial`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_fecha` (`id_usuario`,`fecha_creacion`),
  ADD KEY `idx_modulo` (`modulo`),
  ADD KEY `idx_historial_tipo` (`id_tipo_historial`),
  ADD KEY `idx_historial_modulo` (`id_modulo`);

--
-- Indices de la tabla `inventario_movimiento`
--
ALTER TABLE `inventario_movimiento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `idx_producto_fecha` (`id_producto_tienda`,`fecha_movimiento`),
  ADD KEY `idx_referencia` (`referencia`);

--
-- Indices de la tabla `logs_pagos`
--
ALTER TABLE `logs_pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orden_id` (`orden_id`),
  ADD KEY `idx_fecha` (`fecha`),
  ADD KEY `idx_accion` (`accion`);

--
-- Indices de la tabla `marca`
--
ALTER TABLE `marca`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `metodo_pago`
--
ALTER TABLE `metodo_pago`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `modulo`
--
ALTER TABLE `modulo`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `moneda`
--
ALTER TABLE `moneda`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `municipio`
--
ALTER TABLE `municipio`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_municipio_provincia` (`nombre`,`id_provincia`),
  ADD KEY `id_provincia` (`id_provincia`);

--
-- Indices de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_leida` (`id_usuario`,`leida`),
  ADD KEY `idx_fecha` (`fecha_creacion`);

--
-- Indices de la tabla `oferta_flash`
--
ALTER TABLE `oferta_flash`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_producto_flash` (`producto_tienda_id`,`fecha_inicio`,`fecha_fin`),
  ADD KEY `fk_oferta_flash_producto` (`producto_tienda_id`),
  ADD KEY `idx_oferta_flash_fechas` (`fecha_inicio`,`fecha_fin`,`estado`),
  ADD KEY `idx_oferta_flash_activa` (`estado`,`fecha_inicio`,`fecha_fin`);

--
-- Indices de la tabla `orden`
--
ALTER TABLE `orden`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_orden` (`numero_orden`),
  ADD KEY `id_direccion_envio` (`id_direccion_envio`),
  ADD KEY `id_metodo_pago` (`id_metodo_pago`),
  ADD KEY `idx_usuario_fecha` (`id_usuario`,`fecha_creacion`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_numero_orden` (`numero_orden`),
  ADD KEY `idx_orden_fecha_estado` (`fecha_creacion`,`estado`),
  ADD KEY `idx_estado_pago` (`estado_pago`),
  ADD KEY `idx_referencia_pago` (`referencia_pago`),
  ADD KEY `idx_hash_verificacion` (`hash_verificacion`);

--
-- Indices de la tabla `orden_detalle`
--
ALTER TABLE `orden_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_producto_tienda` (`id_producto_tienda`),
  ADD KEY `idx_orden` (`id_orden`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `id_marca` (`id_marca`),
  ADD KEY `id_subcategoria` (`id_subcategoria`),
  ADD KEY `id_unidad_medida` (`id_unidad_medida`);
ALTER TABLE `producto` ADD FULLTEXT KEY `idx_producto_busqueda` (`nombre`,`descripcion_corta`,`descripcion_larga`);
ALTER TABLE `producto` ADD FULLTEXT KEY `idx_producto_etiquetas` (`etiquetas`);

--
-- Indices de la tabla `producto_imagen`
--
ALTER TABLE `producto_imagen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_producto_orden` (`id_producto`,`orden`);

--
-- Indices de la tabla `producto_tienda`
--
ALTER TABLE `producto_tienda`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_producto_tienda` (`id_producto`,`id_tienda`),
  ADD KEY `id_tienda` (`id_tienda`),
  ADD KEY `idx_precios` (`precio_final`),
  ADD KEY `idx_stock` (`stock`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_producto_tienda_ventas` (`total_ventas`,`estado`),
  ADD KEY `idx_producto_tienda_descuento` (`descuento_porcentaje`,`estado`),
  ADD KEY `idx_producto_tienda_envio_descuento` (`envio_gratis`,`descuento_porcentaje`,`estado`),
  ADD KEY `idx_producto_tienda_ventas_descuento` (`total_ventas`,`descuento_porcentaje`,`estado`);

--
-- Indices de la tabla `provincia`
--
ALTER TABLE `provincia`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `redes_sociales`
--
ALTER TABLE `redes_sociales`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `reseña`
--
ALTER TABLE `reseña`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_resena_orden` (`id_usuario`,`id_orden_detalle`),
  ADD KEY `id_orden_detalle` (`id_orden_detalle`),
  ADD KEY `idx_producto_calificacion` (`id_producto_tienda`,`calificacion`),
  ADD KEY `idx_resena_fecha` (`fecha_creacion`,`estado`);

--
-- Indices de la tabla `subcategoria`
--
ALTER TABLE `subcategoria`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_subcategoria_categoria` (`nombre`,`id_categoria`),
  ADD UNIQUE KEY `idx_subcategoria_nombre_categoria` (`nombre`,`id_categoria`),
  ADD KEY `idx_subcategoria_categoria_orden` (`id_categoria`,`orden`);

--
-- Indices de la tabla `suscripcion_ofertas`
--
ALTER TABLE `suscripcion_ofertas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_suscripcion_estado` (`estado`,`confirmada`);

--
-- Indices de la tabla `tienda`
--
ALTER TABLE `tienda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario_propietario` (`id_usuario_propietario`),
  ADD KEY `id_municipio` (`id_municipio`);

--
-- Indices de la tabla `tipo_historial`
--
ALTER TABLE `tipo_historial`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tipo_usuario`
--
ALTER TABLE `tipo_usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `transaccion_pago`
--
ALTER TABLE `transaccion_pago`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_referencia` (`referencia_externa`),
  ADD KEY `id_metodo_pago` (`id_metodo_pago`),
  ADD KEY `idx_orden_estado` (`id_orden`,`estado`);

--
-- Indices de la tabla `transferencia_pagos`
--
ALTER TABLE `transferencia_pagos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_transaccion` (`numero_transaccion`),
  ADD KEY `id_orden` (`id_orden`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `unidad_medida`
--
ALTER TABLE `unidad_medida`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD UNIQUE KEY `abreviatura` (`abreviatura`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `dni` (`dni`),
  ADD KEY `id_tipo_usuario` (`id_tipo_usuario`);

--
-- Indices de la tabla `usuario_configuracion`
--
ALTER TABLE `usuario_configuracion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_moneda` (`id_moneda`),
  ADD KEY `idx_tema` (`tema`),
  ADD KEY `idx_idioma` (`idioma`),
  ADD KEY `idx_densidad` (`densidad`);

--
-- Indices de la tabla `usuario_direccion`
--
ALTER TABLE `usuario_direccion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario_alias` (`id_usuario`,`alias`),
  ADD KEY `id_municipio` (`id_municipio`);

--
-- Indices de la tabla `usuario_exportaciones`
--
ALTER TABLE `usuario_exportaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_fecha` (`id_usuario`,`fecha_exportacion`),
  ADD KEY `idx_tipo_exportacion` (`tipo_exportacion`);

--
-- Indices de la tabla `usuario_metodo_pago`
--
ALTER TABLE `usuario_metodo_pago`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario_metodo` (`id_usuario`,`id_metodo_pago`,`alias`),
  ADD KEY `id_metodo_pago` (`id_metodo_pago`);

--
-- Indices de la tabla `usuario_notificaciones`
--
ALTER TABLE `usuario_notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario_tipo_canal` (`id_usuario`,`tipo_notificacion`,`canal`),
  ADD KEY `idx_usuario_activo` (`id_usuario`,`activo`),
  ADD KEY `idx_tipo_notificacion` (`tipo_notificacion`);

--
-- Indices de la tabla `usuario_privacidad`
--
ALTER TABLE `usuario_privacidad`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario` (`id_usuario`),
  ADD KEY `idx_verificacion` (`verificacion_dos_pasos`);

--
-- Indices de la tabla `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario_nombre` (`id_usuario`,`nombre`);

--
-- Indices de la tabla `wishlist_producto`
--
ALTER TABLE `wishlist_producto`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_wishlist_producto` (`id_wishlist`,`id_producto_tienda`),
  ADD KEY `id_producto_tienda` (`id_producto_tienda`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `bundle`
--
ALTER TABLE `bundle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `bundle_producto`
--
ALTER TABLE `bundle_producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `categoria_oferta`
--
ALTER TABLE `categoria_oferta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `chat_agentes`
--
ALTER TABLE `chat_agentes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `chat_configuracion`
--
ALTER TABLE `chat_configuracion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `chat_mensajes`
--
ALTER TABLE `chat_mensajes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `chat_notificaciones`
--
ALTER TABLE `chat_notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `configuracion_sitio`
--
ALTER TABLE `configuracion_sitio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `contacto_mensajes`
--
ALTER TABLE `contacto_mensajes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `cupon`
--
ALTER TABLE `cupon`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `cupon_producto`
--
ALTER TABLE `cupon_producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `cupon_usado`
--
ALTER TABLE `cupon_usado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `favorito`
--
ALTER TABLE `favorito`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT de la tabla `historial`
--
ALTER TABLE `historial`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `inventario_movimiento`
--
ALTER TABLE `inventario_movimiento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `logs_pagos`
--
ALTER TABLE `logs_pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `marca`
--
ALTER TABLE `marca`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT de la tabla `metodo_pago`
--
ALTER TABLE `metodo_pago`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `modulo`
--
ALTER TABLE `modulo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `moneda`
--
ALTER TABLE `moneda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `municipio`
--
ALTER TABLE `municipio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=170;

--
-- AUTO_INCREMENT de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `oferta_flash`
--
ALTER TABLE `oferta_flash`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `orden`
--
ALTER TABLE `orden`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=173;

--
-- AUTO_INCREMENT de la tabla `orden_detalle`
--
ALTER TABLE `orden_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT de la tabla `producto_imagen`
--
ALTER TABLE `producto_imagen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=552;

--
-- AUTO_INCREMENT de la tabla `producto_tienda`
--
ALTER TABLE `producto_tienda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT de la tabla `provincia`
--
ALTER TABLE `provincia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `redes_sociales`
--
ALTER TABLE `redes_sociales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `reseña`
--
ALTER TABLE `reseña`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `subcategoria`
--
ALTER TABLE `subcategoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=427;

--
-- AUTO_INCREMENT de la tabla `suscripcion_ofertas`
--
ALTER TABLE `suscripcion_ofertas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `tienda`
--
ALTER TABLE `tienda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tipo_historial`
--
ALTER TABLE `tipo_historial`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `tipo_usuario`
--
ALTER TABLE `tipo_usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `transaccion_pago`
--
ALTER TABLE `transaccion_pago`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `transferencia_pagos`
--
ALTER TABLE `transferencia_pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `unidad_medida`
--
ALTER TABLE `unidad_medida`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `usuario_configuracion`
--
ALTER TABLE `usuario_configuracion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuario_direccion`
--
ALTER TABLE `usuario_direccion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `usuario_exportaciones`
--
ALTER TABLE `usuario_exportaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuario_metodo_pago`
--
ALTER TABLE `usuario_metodo_pago`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuario_notificaciones`
--
ALTER TABLE `usuario_notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuario_privacidad`
--
ALTER TABLE `usuario_privacidad`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `wishlist_producto`
--
ALTER TABLE `wishlist_producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `bundle`
--
ALTER TABLE `bundle`
  ADD CONSTRAINT `fk_bundle_tienda` FOREIGN KEY (`tienda_id`) REFERENCES `tienda` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `bundle_producto`
--
ALTER TABLE `bundle_producto`
  ADD CONSTRAINT `fk_bundle_producto_bundle` FOREIGN KEY (`bundle_id`) REFERENCES `bundle` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bundle_producto_producto` FOREIGN KEY (`producto_tienda_id`) REFERENCES `producto_tienda` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carrito_ibfk_2` FOREIGN KEY (`id_producto_tienda`) REFERENCES `producto_tienda` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `categoria_oferta`
--
ALTER TABLE `categoria_oferta`
  ADD CONSTRAINT `fk_categoria_oferta_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `chat_notificaciones`
--
ALTER TABLE `chat_notificaciones`
  ADD CONSTRAINT `chat_notificaciones_ibfk_1` FOREIGN KEY (`conversacion_id`) REFERENCES `chat_conversaciones` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `cupon_producto`
--
ALTER TABLE `cupon_producto`
  ADD CONSTRAINT `cupon_producto_ibfk_1` FOREIGN KEY (`id_cupon`) REFERENCES `cupon` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cupon_producto_ibfk_2` FOREIGN KEY (`id_producto_tienda`) REFERENCES `producto_tienda` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `cupon_usado`
--
ALTER TABLE `cupon_usado`
  ADD CONSTRAINT `cupon_usado_ibfk_1` FOREIGN KEY (`id_cupon`) REFERENCES `cupon` (`id`),
  ADD CONSTRAINT `cupon_usado_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`),
  ADD CONSTRAINT `cupon_usado_ibfk_3` FOREIGN KEY (`id_orden`) REFERENCES `orden` (`id`);

--
-- Filtros para la tabla `favorito`
--
ALTER TABLE `favorito`
  ADD CONSTRAINT `favorito_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorito_ibfk_2` FOREIGN KEY (`id_producto_tienda`) REFERENCES `producto_tienda` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `historial`
--
ALTER TABLE `historial`
  ADD CONSTRAINT `fk_historial_modulo` FOREIGN KEY (`id_modulo`) REFERENCES `modulo` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_historial_tipo_historial` FOREIGN KEY (`id_tipo_historial`) REFERENCES `tipo_historial` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `historial_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `inventario_movimiento`
--
ALTER TABLE `inventario_movimiento`
  ADD CONSTRAINT `inventario_movimiento_ibfk_1` FOREIGN KEY (`id_producto_tienda`) REFERENCES `producto_tienda` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventario_movimiento_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`);

--
-- Filtros para la tabla `logs_pagos`
--
ALTER TABLE `logs_pagos`
  ADD CONSTRAINT `fk_logs_orden` FOREIGN KEY (`orden_id`) REFERENCES `orden` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `municipio`
--
ALTER TABLE `municipio`
  ADD CONSTRAINT `municipio_ibfk_1` FOREIGN KEY (`id_provincia`) REFERENCES `provincia` (`id`);

--
-- Filtros para la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD CONSTRAINT `notificacion_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `oferta_flash`
--
ALTER TABLE `oferta_flash`
  ADD CONSTRAINT `fk_oferta_flash_producto` FOREIGN KEY (`producto_tienda_id`) REFERENCES `producto_tienda` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `orden`
--
ALTER TABLE `orden`
  ADD CONSTRAINT `orden_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`),
  ADD CONSTRAINT `orden_ibfk_2` FOREIGN KEY (`id_direccion_envio`) REFERENCES `usuario_direccion` (`id`),
  ADD CONSTRAINT `orden_ibfk_3` FOREIGN KEY (`id_metodo_pago`) REFERENCES `usuario_metodo_pago` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `orden_detalle`
--
ALTER TABLE `orden_detalle`
  ADD CONSTRAINT `orden_detalle_ibfk_1` FOREIGN KEY (`id_orden`) REFERENCES `orden` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orden_detalle_ibfk_2` FOREIGN KEY (`id_producto_tienda`) REFERENCES `producto_tienda` (`id`);

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `producto_ibfk_1` FOREIGN KEY (`id_marca`) REFERENCES `marca` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `producto_ibfk_2` FOREIGN KEY (`id_subcategoria`) REFERENCES `subcategoria` (`id`),
  ADD CONSTRAINT `producto_ibfk_3` FOREIGN KEY (`id_unidad_medida`) REFERENCES `unidad_medida` (`id`);

--
-- Filtros para la tabla `producto_imagen`
--
ALTER TABLE `producto_imagen`
  ADD CONSTRAINT `producto_imagen_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `producto_tienda`
--
ALTER TABLE `producto_tienda`
  ADD CONSTRAINT `producto_tienda_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `producto_tienda_ibfk_2` FOREIGN KEY (`id_tienda`) REFERENCES `tienda` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reseña`
--
ALTER TABLE `reseña`
  ADD CONSTRAINT `reseña_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reseña_ibfk_2` FOREIGN KEY (`id_producto_tienda`) REFERENCES `producto_tienda` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reseña_ibfk_3` FOREIGN KEY (`id_orden_detalle`) REFERENCES `orden_detalle` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `subcategoria`
--
ALTER TABLE `subcategoria`
  ADD CONSTRAINT `subcategoria_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tienda`
--
ALTER TABLE `tienda`
  ADD CONSTRAINT `tienda_ibfk_1` FOREIGN KEY (`id_usuario_propietario`) REFERENCES `usuario` (`id`),
  ADD CONSTRAINT `tienda_ibfk_2` FOREIGN KEY (`id_municipio`) REFERENCES `municipio` (`id`);

--
-- Filtros para la tabla `transaccion_pago`
--
ALTER TABLE `transaccion_pago`
  ADD CONSTRAINT `transaccion_pago_ibfk_1` FOREIGN KEY (`id_orden`) REFERENCES `orden` (`id`),
  ADD CONSTRAINT `transaccion_pago_ibfk_2` FOREIGN KEY (`id_metodo_pago`) REFERENCES `metodo_pago` (`id`);

--
-- Filtros para la tabla `transferencia_pagos`
--
ALTER TABLE `transferencia_pagos`
  ADD CONSTRAINT `transferencia_pagos_ibfk_1` FOREIGN KEY (`id_orden`) REFERENCES `orden` (`id`),
  ADD CONSTRAINT `transferencia_pagos_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`);

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`id_tipo_usuario`) REFERENCES `tipo_usuario` (`id`);

--
-- Filtros para la tabla `usuario_configuracion`
--
ALTER TABLE `usuario_configuracion`
  ADD CONSTRAINT `usuario_configuracion_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuario_direccion`
--
ALTER TABLE `usuario_direccion`
  ADD CONSTRAINT `usuario_direccion_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuario_direccion_ibfk_2` FOREIGN KEY (`id_municipio`) REFERENCES `municipio` (`id`);

--
-- Filtros para la tabla `usuario_exportaciones`
--
ALTER TABLE `usuario_exportaciones`
  ADD CONSTRAINT `fk_exportaciones_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario_metodo_pago`
--
ALTER TABLE `usuario_metodo_pago`
  ADD CONSTRAINT `usuario_metodo_pago_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuario_metodo_pago_ibfk_2` FOREIGN KEY (`id_metodo_pago`) REFERENCES `metodo_pago` (`id`);

--
-- Filtros para la tabla `usuario_notificaciones`
--
ALTER TABLE `usuario_notificaciones`
  ADD CONSTRAINT `fk_notificaciones_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario_privacidad`
--
ALTER TABLE `usuario_privacidad`
  ADD CONSTRAINT `fk_privacidad_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `wishlist_producto`
--
ALTER TABLE `wishlist_producto`
  ADD CONSTRAINT `wishlist_producto_ibfk_1` FOREIGN KEY (`id_wishlist`) REFERENCES `wishlist` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_producto_ibfk_2` FOREIGN KEY (`id_producto_tienda`) REFERENCES `producto_tienda` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
