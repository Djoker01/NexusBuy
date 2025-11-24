-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-11-2025 a las 10:48:43
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
(1, 2, 'session123', 3, 2, '2025-11-17 02:21:44', '2025-11-17 02:21:44'),
(2, 2, 'session123', 4, 1, '2025-11-17 02:21:44', '2025-11-17 02:21:44');

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
(6, 'Salud y Bienestar', 'Productos médicos, suplementos y artículos para el bienestar', 'default_category.png', 'fas fa-heartbeat', 6, 'activa', '2025-10-06 17:01:19', '2025-11-14 17:47:18'),
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
(19, 'Fitness y Ejercicio', 'Equipo de ejercicio, fitness, yoga y entrenamiento personal', 'fitness.jpg', 'fas fa-dumbbell', 19, 'activa', '2025-10-06 21:57:58', '2025-11-17 21:31:38');

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
(4, 'impuesto_venta', '0', 'number', 'finanzas', 'Porcentaje de impuesto sobre ventas', 1, '2025-11-14 14:54:30', '2025-11-14 14:54:30'),
(5, 'costo_envio_gratis', '50.00', 'number', 'envios', 'Monto mínimo para envío gratis', 1, '2025-11-14 14:54:30', '2025-11-14 14:54:30'),
(6, 'dias_para_devolucion', '30', 'number', 'devoluciones', 'Días permitidos para devoluciones', 1, '2025-11-14 14:54:30', '2025-11-14 14:54:30'),
(7, 'email_contacto', 'ventas@nexusbuy.com', 'string', 'contacto', 'Email de contacto principal', 1, '2025-11-14 14:54:30', '2025-11-14 14:54:30'),
(8, 'telefono_contacto', '+53555123456', 'string', 'contacto', 'Teléfono de contacto', 1, '2025-11-14 14:54:30', '2025-11-14 14:54:30'),
(9, 'direccion_tienda', 'Avenida Principal #123', 'string', 'contacto', 'Dirección física de la tienda', 1, '2025-11-14 14:54:30', '2025-11-14 14:54:30'),
(10, 'politica_privacidad', 'Texto de política de privacidad...', 'string', 'legal', 'Política de privacidad', 1, '2025-11-14 14:54:30', '2025-11-14 14:54:30'),
(11, 'terminos_condiciones', 'Texto de términos y condiciones...', 'string', 'legal', 'Términos y condiciones', 1, '2025-11-14 14:54:30', '2025-11-14 14:54:30'),
(12, 'tiempo_sesion_minutos', '120', 'number', 'seguridad', 'Tiempo de expiración de sesión', 1, '2025-11-14 14:54:30', '2025-11-14 14:54:30'),
(13, 'max_intentos_login', '5', 'number', 'seguridad', 'Máximo de intentos de login fallidos', 1, '2025-11-14 14:54:30', '2025-11-14 14:54:30');

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
(2, 2, 5, '2025-11-17 02:22:17'),
(4, 2, 1, '2025-11-24 01:53:35');

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
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(5, 5, 'entrada', 15, 0, 15, 'Stock inicial', 'INV-INICIAL', 2, 'Stock inicial del producto', '2025-11-17 02:20:54');

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
(5, 'IKEA', 'Muebles y artículos para el hogar', 'default_logo.png', NULL, 'activa', '2025-11-16 23:54:15', '2025-11-16 23:54:15'),
(6, 'Sony', 'Electrónicos y entretenimiento', 'sony.jpeg', NULL, 'activa', '2025-11-16 23:54:15', '2025-11-16 23:54:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metodo_pago`
--

CREATE TABLE `metodo_pago` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icono` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `configuracion` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`configuracion`)),
  `estado` enum('activo','inactivo') COLLATE utf8mb4_unicode_ci DEFAULT 'activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `metodo_pago`
--

INSERT INTO `metodo_pago` (`id`, `nombre`, `descripcion`, `icono`, `configuracion`, `estado`, `fecha_creacion`) VALUES
(1, 'tarjeta_credito', 'Pago con tarjeta de crédito', 'fas fa-credit-card', '{\"campos\": [\"numero\", \"fecha_vencimiento\", \"cvv\", \"titular\"], \"encriptar\": [\"numero\", \"cvv\"]}', 'activo', '2025-11-14 14:54:30'),
(2, 'transferencia_bancaria', 'Transferencia bancaria', 'fas fa-university', '{\"campos\": [\"banco\", \"numero_cuenta\", \"titular\"], \"encriptar\": [\"numero_cuenta\"]}', 'activo', '2025-11-14 14:54:30'),
(3, 'efectivo', 'Pago en efectivo', 'fas fa-money-bill-wave', '{\"campos\": [], \"encriptar\": []}', 'activo', '2025-11-14 14:54:30'),
(4, 'paypal', 'Pago con Paypal', 'fab fa-paypal', '{\"campos\": [\"email\"], \"encriptar\": []}', 'activo', '2025-11-14 14:54:30');

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
(1, 'CUP', 'Peso Cubano', '$', '1.0000', 'activa', '2025-11-14 14:54:29'),
(2, 'USD', 'Dólar Americano', 'US$', '492.0000', 'activa', '2025-11-15 16:14:10'),
(3, 'EUR', 'Euro', '€', '540.0000', 'activa', '2025-11-15 16:14:20');

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
  `total` decimal(12,2) NOT NULL CHECK (`total` >= 0),
  `codigo_seguimiento` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_entrega_estimada` date DEFAULT NULL,
  `fecha_entrega_real` datetime DEFAULT NULL,
  `notas_cliente` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notas_internas` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `orden`
--

INSERT INTO `orden` (`id`, `numero_orden`, `id_usuario`, `id_direccion_envio`, `id_metodo_pago`, `estado`, `subtotal`, `descuento`, `costo_envio`, `impuestos`, `total`, `codigo_seguimiento`, `fecha_entrega_estimada`, `fecha_entrega_real`, `notas_cliente`, `notas_internas`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'ORD-001', 2, 1, NULL, 'entregada', '270.00', '30.00', '3.00', '0.00', '273.00', NULL, NULL, NULL, NULL, NULL, '2025-11-17 02:30:22', '2025-11-17 02:30:22');

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
(2, 1, 4, 1, '140.00', '0.00', '140.00', '2025-11-17 02:30:41');

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
(2, 'AP-IP13P', 'iPhone 13 Pro', 'iPhone profesional con chip A15 Bionic', 'iPhone 13 Pro con sistema de cámaras Pro, pantalla Super Retina XDR con ProMotion y el más rápido chip A15 Bionic.', 2, 1, 6, '204.000', '146.7 x 71.5 x 7.7 mm', '{\"Pantalla\": \"6.1 pulgadas\", \"RAM\": \"6GB\", \"Almacenamiento\": \"128GB\", \"Cámara\": \"12MP + 12MP + 12MP\"}', '[\"iphone\", \"apple\", \"ios\", \"5g\"]', 'activo', '2025-11-17 02:11:50', '2025-11-17 02:11:50'),
(3, 'NK-AF1', 'Nike Air Force 1', 'Zapatillas clásicas de baloncesto', 'Las Nike Air Force 1 son un ícono del baloncesto con amortiguación Air y diseño timeless.', 3, 102, 6, '800.000', '30 x 15 x 10 cm', '{\"Material\": \"Cuero\", \"Suela\": \"Goma\", \"Cierre\": \"Cordones\"}', '[\"zapatillas\", \"deportivas\", \"nike\", \"urbanas\"]', 'activo', '2025-11-17 02:11:50', '2025-11-17 02:11:50'),
(4, 'AD-SUPR', 'Adidas Ultraboost 22', 'Zapatillas running con tecnología Boost', 'Adidas Ultraboost 22 con amortiguación Boost responsive y upper Primeknit para máximo confort.', 4, 102, 6, '750.000', '29 x 14 x 9 cm', '{\"Material\": \"Primeknit\", \"Suela\": \"Boost\", \"Drop\": \"10mm\"}', '[\"running\", \"deportivas\", \"adidas\", \"comfort\"]', 'activo', '2025-11-17 02:11:50', '2025-11-17 02:11:50'),
(5, 'SN-WH1000', 'Sony WH-1000XM4', 'Audífonos noise cancelling líderes', 'Audífonos inalámbricos con cancelación de ruido líder en la industria y calidad de sonido excepcional.', 6, 4, 6, '254.000', '21 x 19 x 8 cm', '{\"Cancelación\": \"Activa\", \"Batería\": \"30h\", \"Conectividad\": \"Bluetooth 5.0\"}', '[\"audífonos\", \"noise-cancelling\", \"sony\", \"wireless\"]', 'activo', '2025-11-17 02:11:50', '2025-11-17 02:11:50');

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
(49, 5, '81zNaO7AyoL._AC_UF894,1000_QL80_FMwebp_.webp', 2, 0, 'Sony WH-1000XM4', 'activa', '2025-11-22 01:15:46');

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

INSERT INTO `producto_tienda` (`id`, `id_producto`, `id_tienda`, `precio`, `precio_original`, `descuento_porcentaje`, `stock`, `stock_minimo`, `sku_tienda`, `garantia_meses`, `tiempo_entrega`, `costo_envio`, `envio_gratis`, `calificacion_promedio`, `total_resenas`, `total_ventas`, `visitas`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 1, 1, '850.00', '900.00', '5.56', 15, 3, 'TS-SGS21U', 12, 3, '5.00', 0, '4.70', 23, 45, 156, 'activo', '2025-11-17 02:19:08', '2025-11-17 02:19:08'),
(2, 2, 1, '950.00', '950.00', '0.00', 8, 2, 'TS-IP13P', 12, 3, '5.00', 0, '4.80', 18, 32, 142, 'activo', '2025-11-17 02:19:08', '2025-11-17 02:19:08'),
(3, 3, 1, '120.00', '150.00', '20.00', 50, 10, 'TS-NAF1', 6, 2, '3.00', 1, '4.60', 67, 123, 289, 'activo', '2025-11-17 02:19:08', '2025-11-23 20:29:03'),
(4, 4, 1, '140.00', '160.00', '12.50', 35, 8, 'TS-AU22', 6, 2, '3.00', 1, '4.50', 45, 87, 201, 'activo', '2025-11-17 02:19:08', '2025-11-23 01:20:56'),
(5, 5, 1, '280.00', '320.00', '12.50', 12, 3, 'TS-SWHXM4', 12, 3, '5.00', 0, '4.90', 34, 56, 178, 'activo', '2025-11-17 02:19:08', '2025-11-17 02:19:08');

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
(1, 2, 3, 1, 5, 'Excelentes zapatillas', 'Muy cómodas y buena calidad. Las uso todos los días.', '¡Gracias por tu compra! Nos alegra que te gusten.', NULL, 0, 'aprobada', '2025-11-17 02:31:04', '2025-11-23 19:48:08'),
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
(10, 'Computadoras', NULL, 13, NULL, 10, 'activa', '2025-11-14 17:45:36', '2025-11-14 17:45:36'),
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
(102, 'Zapatos Deportivos', NULL, 19, NULL, 102, 'activa', '2025-11-14 18:55:22', '2025-11-14 18:55:22'),
(103, 'Equipo de Gimnasio', NULL, 19, NULL, 103, 'activa', '2025-11-14 18:55:22', '2025-11-14 18:55:22'),
(104, 'Pesas y Mancuernas', NULL, 19, NULL, 104, 'activa', '2025-11-14 18:55:22', '2025-11-14 18:55:22'),
(105, 'Toallas Deportivas', NULL, 11, NULL, 105, 'activa', '2025-11-14 18:55:22', '2025-11-14 18:55:22'),
(106, 'Flotadores', NULL, 11, NULL, 106, 'activa', '2025-11-14 18:55:22', '2025-11-14 18:55:22'),
(107, 'Accesorios de Natación', NULL, 11, NULL, 107, 'activa', '2025-11-14 18:55:22', '2025-11-14 18:55:22'),
(108, 'Goggles', NULL, 11, NULL, 108, 'activa', '2025-11-14 18:55:22', '2025-11-14 18:55:22'),
(109, 'Refrigeradores', NULL, 5, NULL, 109, 'activa', '2025-11-14 18:55:22', '2025-11-14 18:55:22'),
(110, 'Lavadoras', NULL, 5, NULL, 110, 'activa', '2025-11-14 18:55:22', '2025-11-14 18:55:22'),
(151, 'Secadoras', NULL, 9, NULL, 111, 'activa', '2025-11-14 19:28:58', '2025-11-14 19:28:58'),
(171, 'Cocinas y Hornos', NULL, 9, NULL, 112, 'activa', '2025-11-14 19:30:11', '2025-11-14 19:30:11'),
(172, 'Lavavajillas', NULL, 9, NULL, 113, 'activa', '2025-11-14 19:30:11', '2025-11-14 19:30:11'),
(173, 'Microondas', NULL, 9, NULL, 114, 'activa', '2025-11-14 19:30:11', '2025-11-14 19:30:11'),
(174, 'Licuadoras', NULL, 9, NULL, 115, 'activa', '2025-11-14 19:30:11', '2025-11-14 19:30:11'),
(175, 'Cafeteras', NULL, 9, NULL, 116, 'activa', '2025-11-14 19:30:11', '2025-11-14 19:30:11'),
(176, 'Aspiradoras', NULL, 9, NULL, 117, 'activa', '2025-11-14 19:30:11', '2025-11-14 19:30:11'),
(177, 'Plancha de Ropa', NULL, 9, NULL, 118, 'activa', '2025-11-14 19:30:11', '2025-11-14 19:30:11'),
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
(205, 'Ceriales', NULL, 2, NULL, 138, 'activa', '2025-11-14 19:36:12', '2025-11-14 19:36:12');

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
(1, 'TechStore Cuba', 'Tienda especializada en tecnología y electrónica', 2, 42, 'Calle 23 #456 entre L y M', '+5371234567', 'ventas@techstore.cu', 'https://techstore.cu', 'techstore_logo.png', 'techstore_banner.jpg', '{\"facebook\": \"techstorecuba\", \"instagram\": \"techstore_cu\", \"twitter\": \"techstorecu\"}', '{\"devoluciones\": \"30 días\", \"garantia\": \"1 año\", \"envios\": \"Gratis en compras > $50\"}', '4.80', 'activa', '2025-11-17 02:15:55', '2025-11-17 02:15:55');

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
(1, 'superadmin', 'Administrador principal del sistema', 100, 'activo', '2025-11-14 14:54:29'),
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
  `genero` enum('M','F','O','prefiero_no_decir') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'default_avatar.png',
  `id_tipo_usuario` int(11) NOT NULL DEFAULT 2,
  `email_verificado` tinyint(1) DEFAULT 0,
  `token_verificacion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_recuperacion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_expiracion_token` datetime DEFAULT NULL,
  `ultimo_login` datetime DEFAULT NULL,
  `estado` enum('activo','inactivo','suspendido','pendiente_verificacion') COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente_verificacion',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `username`, `email`, `password_hash`, `nombres`, `apellidos`, `dni`, `telefono`, `fecha_nacimiento`, `genero`, `avatar`, `id_tipo_usuario`, `email_verificado`, `token_verificacion`, `token_recuperacion`, `fecha_expiracion_token`, `ultimo_login`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(2, 'cliente', 'adsdfg@gmail.com', '$2y$10$vfPyb.a2SAc6Yfwp3ZhS0eqUed05KFLVTyiSjAhcQr/dh2tCwqPc.', 'Carlos', 'Garcia', '12345678901', '12345678', NULL, NULL, 'default_avatar.png', 2, 1, NULL, NULL, NULL, '2025-11-22 20:41:49', 'pendiente_verificacion', '2025-11-15 06:04:48', '2025-11-23 01:41:49');

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
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, 2, 'cliente', 'Neptuno #616B / Gervacio y Escobar', 36, '10400', '51004754', NULL, 1, 'activa', '2025-11-17 02:25:20', '2025-11-17 02:25:20');

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

--
-- Índices para tablas volcadas
--

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
-- Indices de la tabla `configuracion_sitio`
--
ALTER TABLE `configuracion_sitio`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clave` (`clave`),
  ADD KEY `idx_clave` (`clave`),
  ADD KEY `idx_categoria` (`categoria`);

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
  ADD KEY `idx_modulo` (`modulo`);

--
-- Indices de la tabla `inventario_movimiento`
--
ALTER TABLE `inventario_movimiento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `idx_producto_fecha` (`id_producto_tienda`,`fecha_movimiento`),
  ADD KEY `idx_referencia` (`referencia`);

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
  ADD UNIQUE KEY `nombre` (`nombre`);

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
  ADD KEY `idx_orden_fecha_estado` (`fecha_creacion`,`estado`);

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
  ADD KEY `idx_producto_tienda_ventas` (`total_ventas`,`estado`);

--
-- Indices de la tabla `provincia`
--
ALTER TABLE `provincia`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

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
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Indices de la tabla `tienda`
--
ALTER TABLE `tienda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario_propietario` (`id_usuario_propietario`),
  ADD KEY `id_municipio` (`id_municipio`);

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
  ADD KEY `id_moneda` (`id_moneda`);

--
-- Indices de la tabla `usuario_direccion`
--
ALTER TABLE `usuario_direccion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario_alias` (`id_usuario`,`alias`),
  ADD KEY `id_municipio` (`id_municipio`);

--
-- Indices de la tabla `usuario_metodo_pago`
--
ALTER TABLE `usuario_metodo_pago`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario_metodo` (`id_usuario`,`id_metodo_pago`,`alias`),
  ADD KEY `id_metodo_pago` (`id_metodo_pago`);

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
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `configuracion_sitio`
--
ALTER TABLE `configuracion_sitio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `historial`
--
ALTER TABLE `historial`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inventario_movimiento`
--
ALTER TABLE `inventario_movimiento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `marca`
--
ALTER TABLE `marca`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `metodo_pago`
--
ALTER TABLE `metodo_pago`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `moneda`
--
ALTER TABLE `moneda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `municipio`
--
ALTER TABLE `municipio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=170;

--
-- AUTO_INCREMENT de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `orden`
--
ALTER TABLE `orden`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `orden_detalle`
--
ALTER TABLE `orden_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `producto_imagen`
--
ALTER TABLE `producto_imagen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de la tabla `producto_tienda`
--
ALTER TABLE `producto_tienda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `provincia`
--
ALTER TABLE `provincia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `reseña`
--
ALTER TABLE `reseña`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `subcategoria`
--
ALTER TABLE `subcategoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=206;

--
-- AUTO_INCREMENT de la tabla `tienda`
--
ALTER TABLE `tienda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuario_direccion`
--
ALTER TABLE `usuario_direccion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuario_metodo_pago`
--
ALTER TABLE `usuario_metodo_pago`
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
-- Filtros para la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carrito_ibfk_2` FOREIGN KEY (`id_producto_tienda`) REFERENCES `producto_tienda` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `historial_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `inventario_movimiento`
--
ALTER TABLE `inventario_movimiento`
  ADD CONSTRAINT `inventario_movimiento_ibfk_1` FOREIGN KEY (`id_producto_tienda`) REFERENCES `producto_tienda` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventario_movimiento_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`);

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
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`id_tipo_usuario`) REFERENCES `tipo_usuario` (`id`);

--
-- Filtros para la tabla `usuario_configuracion`
--
ALTER TABLE `usuario_configuracion`
  ADD CONSTRAINT `usuario_configuracion_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuario_configuracion_ibfk_2` FOREIGN KEY (`id_moneda`) REFERENCES `moneda` (`id`);

--
-- Filtros para la tabla `usuario_direccion`
--
ALTER TABLE `usuario_direccion`
  ADD CONSTRAINT `usuario_direccion_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuario_direccion_ibfk_2` FOREIGN KEY (`id_municipio`) REFERENCES `municipio` (`id`);

--
-- Filtros para la tabla `usuario_metodo_pago`
--
ALTER TABLE `usuario_metodo_pago`
  ADD CONSTRAINT `usuario_metodo_pago_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuario_metodo_pago_ibfk_2` FOREIGN KEY (`id_metodo_pago`) REFERENCES `metodo_pago` (`id`);

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
