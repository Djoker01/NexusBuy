-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-10-2025 a las 23:11:40
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
-- Estructura de tabla para la tabla `caracteristica`
--

CREATE TABLE `caracteristica` (
  `id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descripcion` varchar(1000) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_ediccion` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(10) NOT NULL DEFAULT 'A',
  `id_producto` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `caracteristica`
--

INSERT INTO `caracteristica` (`id`, `titulo`, `descripcion`, `fecha_creacion`, `fecha_ediccion`, `estado`, `id_producto`) VALUES
(10, 'Pomo', '1 Litro', '2025-10-10 11:55:35', '2025-10-10 11:55:35', 'A', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito_items`
--

CREATE TABLE `carrito_items` (
  `id` int(11) NOT NULL,
  `id_carrito` int(11) NOT NULL,
  `id_producto_tienda` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precio_unitario` decimal(10,2) NOT NULL,
  `descuento_unitario` decimal(10,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(10,2) NOT NULL,
  `fecha_agregado` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `carrito_items`
--

INSERT INTO `carrito_items` (`id`, `id_carrito`, `id_producto_tienda`, `cantidad`, `precio_unitario`, `descuento_unitario`, `subtotal`, `fecha_agregado`, `fecha_actualizacion`) VALUES
(1, 1, 3, 2, '2500.00', '250.00', '4500.00', '2025-10-20 01:41:20', '2025-10-21 22:39:15'),
(2, 1, 2, 4, '650.00', '0.00', '2600.00', '2025-10-20 18:18:15', '2025-10-21 22:40:32'),
(3, 1, 6, 2, '230.00', '23.00', '414.00', '2025-10-20 18:19:18', '2025-10-20 18:19:52'),
(4, 1, 11, 3, '450.00', '0.00', '1350.00', '2025-10-20 18:20:27', '2025-10-20 18:20:57'),
(5, 1, 10, 4, '799.00', '79.90', '2876.40', '2025-10-20 18:21:38', '2025-10-20 18:24:44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrrito`
--

CREATE TABLE `carrrito` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` enum('activo','abandonado','convertido') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `carrrito`
--

INSERT INTO `carrrito` (`id`, `id_usuario`, `fecha_creacion`, `fecha_actualizacion`, `estado`) VALUES
(1, 2, '2025-10-20 01:40:44', '2025-10-20 01:40:44', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_ediccion` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(10) NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`id`, `nombre`, `fecha_creacion`, `fecha_ediccion`, `estado`) VALUES
(1, 'Smartphones y Teléfonos', '2025-09-25 17:27:30', '2025-09-25 17:27:30', 'A'),
(2, 'Alimentos y Bebidas', '2025-10-06 13:01:19', '2025-10-06 13:01:19', 'A'),
(3, 'Belleza y Cuidado Personal', '2025-10-06 13:01:19', '2025-10-06 13:01:19', 'A'),
(4, 'Computadoras y Laptops', '2025-10-06 16:46:29', '2025-10-06 16:46:29', 'A'),
(5, 'Electrodomésticos', '2025-10-06 13:01:19', '2025-10-06 13:01:19', 'A'),
(6, 'Salud y Bienestar', '2025-10-06 13:01:19', '2025-10-06 13:01:19', 'A'),
(7, 'Moda, Ropa y Accesorios', '2025-10-06 13:01:19', '2025-10-06 13:01:19', 'A'),
(8, 'Productos para Bebe', '2025-10-06 13:01:19', '2025-10-06 13:01:19', 'A'),
(9, 'Juguetes, Juegos y Bebés', '2025-10-06 13:01:19', '2025-10-06 13:01:19', 'A'),
(10, 'Hogar y Jardín', '2025-10-06 13:01:19', '2025-10-06 13:01:19', 'A'),
(12, 'Mascotas', '2025-10-06 13:01:19', '2025-10-06 13:01:19', 'A'),
(13, 'Deportes', '2025-10-06 13:01:19', '2025-10-06 13:01:19', 'A'),
(14, 'Automóvil', '2025-10-06 13:09:08', '2025-10-06 13:09:08', 'A'),
(15, 'Oficina y Papelería', '2025-10-06 13:09:48', '2025-10-06 13:09:48', 'A'),
(16, 'Televisores y Video', '2025-10-06 16:50:17', '2025-10-06 16:50:17', 'A'),
(17, 'Audio y Sonido', '2025-10-06 16:52:50', '2025-10-06 16:52:50', 'A'),
(18, 'Cámaras y Fotrografía', '2025-10-06 16:58:50', '2025-10-06 16:58:50', 'A'),
(19, 'Videojuegos', '2025-10-06 17:01:23', '2025-10-06 17:01:23', 'A'),
(20, 'Bricolaje y Ferretería', '2025-10-06 17:19:04', '2025-10-06 17:19:04', 'A'),
(22, 'Fitness y Ejercicio', '2025-10-06 17:57:58', '2025-10-06 17:57:58', 'A');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_usuario`
--

CREATE TABLE `configuracion_usuario` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `datos` text NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `configuracion_usuario`
--

INSERT INTO `configuracion_usuario` (`id`, `id_usuario`, `tipo`, `datos`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(19, 2, 'visualizacion', '{\"tema\":\"claro\",\"densidad\":\"normal\",\"idioma\":\"es\",\"moneda\":\"CUP\"}', '2025-10-19 02:30:19', '2025-10-19 03:10:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `envio_detalles`
--

CREATE TABLE `envio_detalles` (
  `id` int(11) NOT NULL,
  `id_orden` int(11) NOT NULL,
  `empresa_envio` varchar(100) NOT NULL,
  `tipo_envio` varchar(50) NOT NULL,
  `costo_envio` decimal(10,0) NOT NULL,
  `tiempo_estimado` varchar(100) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `favorito`
--

CREATE TABLE `favorito` (
  `id` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('A','I') NOT NULL DEFAULT 'A',
  `id_usuario` int(11) NOT NULL,
  `id_producto_tienda` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `favorito`
--

INSERT INTO `favorito` (`id`, `fecha_creacion`, `estado`, `id_usuario`, `id_producto_tienda`) VALUES
(1, '2025-10-19 04:36:11', 'I', 2, 3),
(2, '2025-10-19 04:36:11', 'A', 2, 9),
(3, '2025-10-20 03:48:57', 'I', 2, 2),
(4, '2025-10-20 21:58:13', 'I', 2, 2),
(5, '2025-10-20 21:58:43', 'I', 2, 2),
(6, '2025-10-20 22:01:07', 'I', 2, 2),
(7, '2025-10-20 22:03:37', 'I', 2, 2),
(8, '2025-10-20 22:03:47', 'A', 2, 2),
(9, '2025-10-22 01:14:34', 'I', 2, 5),
(10, '2025-10-22 02:35:20', 'I', 2, 3),
(11, '2025-10-22 02:37:09', 'I', 2, 3),
(12, '2025-10-22 02:38:54', 'I', 2, 3),
(13, '2025-10-22 02:39:09', 'I', 2, 3),
(14, '2025-10-22 04:34:22', 'I', 2, 3),
(15, '2025-10-22 04:35:05', 'A', 2, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial`
--

CREATE TABLE `historial` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `id_tipo_historial` int(11) NOT NULL,
  `id_modulo` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `historial`
--

INSERT INTO `historial` (`id`, `descripcion`, `fecha`, `id_tipo_historial`, `id_modulo`, `id_usuario`) VALUES
(1, 'Cambiaste tu avatar de usuario', '2025-09-01 04:30:40', 1, 1, 1),
(2, 'Compraste un celular Iphone 11', '2025-09-01 04:30:40', 2, 2, 1),
(3, 'Se elimino una dirección', '2025-09-04 04:35:59', 3, 1, 1),
(4, 'Se creo una dirección', '2025-09-04 04:35:59', 2, 1, 1),
(5, 'Cambiaste el nombre', '2025-09-09 04:35:59', 1, 1, 1),
(6, 'Cambiaste el número de teléfono', '2025-09-09 04:35:59', 1, 1, 1),
(7, 'Cambiaste el avatar', '2025-09-17 04:35:59', 1, 1, 1),
(8, 'Compraste una Licuadora', '2025-09-24 04:35:59', 2, 2, 1),
(9, 'Ha creado una nueva dirección: Neptune Ave 616', '2025-09-24 14:07:17', 2, 1, 1),
(10, 'Ha creado una nueva dirección: prueba 1', '2025-09-24 14:10:41', 2, 1, 1),
(11, 'Ha eliminado la dirección: ', '2025-09-24 14:32:52', 3, 1, 1),
(12, 'Ha eliminado la dirección: ', '2025-09-24 14:33:40', 3, 1, 1),
(13, 'Ha eliminado la dirección: sdfghh, Dirección: Cruces, Municipio: Cienfuegos, Provincia: ', '2025-09-24 14:35:05', 3, 1, 1),
(14, 'Ha eliminado la dirección: asd, Municipio: Cotorro, Provincia: La Habana, Provincia: ', '2025-09-24 14:36:12', 3, 1, 1),
(15, 'Ha creado una nueva dirección: Neptune Ave 616', '2025-09-24 14:36:58', 2, 1, 1),
(16, 'Ha eliminado la dirección: Neptune Ave 616, Municipio: Artemisa, Provincia: ', '2025-09-24 14:38:55', 3, 1, 1),
(17, 'Ha creado una nueva dirección: Neptune Ave 616', '2025-09-24 14:40:25', 2, 1, 1),
(18, 'Ha eliminado la dirección: Neptune Ave 616, Municipio: Pinar del Río, Provincia: ', '2025-09-24 14:40:34', 3, 1, 1),
(19, 'Ha creado una nueva dirección: we', '2025-09-24 14:41:26', 2, 1, 1),
(20, 'Ha eliminado la dirección: Consolación del Sur, Municipio: Pinar del Río, Provincia: ', '2025-09-24 14:41:35', 3, 1, 1),
(21, 'Ha creado una nueva dirección: asd', '2025-09-24 14:44:12', 2, 1, 1),
(22, 'Ha eliminado la Dirección: Boyeros, Municipio: La Habana, Provincia: ', '2025-09-24 14:44:22', 3, 1, 1),
(23, 'Ha creado una nueva dirección: Neptune Ave 616', '2025-09-24 14:46:49', 2, 1, 1),
(24, 'Ha eliminado la Dirección: Neptune Ave 616, Municipio: Consolación del Sur, Provincia: Pinar del Río', '2025-09-24 14:46:54', 3, 1, 1),
(25, 'Ha editado sus datos personales, Ha realizado los siguientes cambios: ', '2025-09-24 15:19:29', 1, 1, 1),
(26, 'Ha editado sus datos personales, Ha realizado los siguientes cambios: ', '2025-09-24 15:24:22', 1, 1, 1),
(27, 'Ha editado sus datos personales, Ha realizado los siguientes cambios: ', '2025-09-24 15:30:58', 1, 1, 1),
(28, 'Ha editado sus datos personales, Ha realizado los siguientes cambios: ', '2025-09-24 15:31:39', 1, 1, 1),
(29, 'Ha editado sus datos personales, Ha realizado los siguientes cambios: ', '2025-09-24 15:31:47', 1, 1, 1),
(30, 'Ha editado sus datos personales, Ha realizado los siguientes cambios: ', '2025-09-24 15:31:51', 1, 1, 1),
(31, 'Ha editado sus datos personales, Ha realizado los siguientes cambios: ', '2025-09-24 15:33:29', 1, 1, 1),
(32, 'Ha editado sus datos personales, Ha realizado los siguientes cambios: ', '2025-09-24 15:35:55', 1, 1, 1),
(33, 'Ha editado sus datos personales, Ha realizado los siguientes cambios: ', '2025-09-24 15:38:25', 1, 1, 1),
(34, 'Ha editado sus datos personales, Ha realizado los siguientes cambios: ', '2025-09-24 15:39:48', 1, 1, 1),
(35, 'Ha editado sus datos personales, Ha realizado los siguientes cambios: su avatar fue cambiado. ', '2025-09-24 15:41:02', 1, 1, 1),
(36, 'Ha editado sus datos personales, Ha realizado los siguientes cambios: ', '2025-09-24 15:50:07', 1, 1, 1),
(37, 'Ha editado sus datos personales, Ha realizado los siguientes cambios: ', '2025-09-24 15:50:49', 1, 1, 1),
(38, 'Ha editado sus datos personales, Ha realizado los siguientes cambios: su nombre cambio de: Noel David aNoel, ', '2025-09-24 16:34:29', 1, 1, 1),
(39, 'Ha editado sus datos personales, Ha realizado los siguientes cambios: su nombre cambio de: Noel a Noel David, ', '2025-09-24 16:35:25', 1, 1, 1),
(40, 'Ha editado sus datos personales, Ha realizado los siguientes cambios: su nombre cambio de: Noel David a Noel, ', '2025-09-24 16:36:06', 1, 1, 1),
(41, 'Ha editado sus datos personales, Ha realizado los siguientes cambios: ', '2025-09-25 15:40:40', 1, 1, 1),
(42, 'Ha cambiado su password', '2025-09-25 15:56:22', 1, 1, 1),
(43, 'Ha cambiado su password', '2025-09-25 15:57:49', 1, 1, 1),
(44, 'Ha cambiado su password', '2025-09-25 15:58:27', 1, 1, 1),
(45, 'Ha creado una nueva dirección: San Miguel 426 / Lealtad y Campanario', '2025-09-27 13:09:59', 2, 1, 8),
(46, 'Ha editado sus datos personales, Ha realizado los siguientes cambios: su avatar fue cambiado. ', '2025-09-27 13:38:59', 1, 1, 8),
(47, 'Agregó una reseña de 3 estrellas a un producto', '2025-10-15 18:36:19', 2, 2, 2),
(48, 'Ha creado una nueva dirección: San Jose', '2025-10-17 12:59:46', 2, 1, 2),
(49, 'Realizó una compra por $ 2500. Orden: ORD-20251017-68F2B7C128E56', '2025-10-17 17:40:17', 2, 2, 2),
(50, 'Realizó una compra por $ 719.1. Orden: ORD-20251017-68F2BC53D35F2', '2025-10-17 17:59:48', 2, 2, 2),
(51, 'Realizó una compra por $ 6750. Orden: ORD-20251018-68F2E8695BCBB', '2025-10-17 21:07:53', 2, 2, 2),
(52, 'Realizó una compra por $ 13595.5. Orden: ORD-20251018-68F2F3471A4B6', '2025-10-17 21:54:15', 2, 2, 2),
(53, 'Agregó una reseña de 1 estrellas a un producto', '2025-10-18 14:10:35', 2, 2, 2),
(54, 'Ha editado sus datos personales, Ha realizado los siguientes cambios: su avatar fue cambiado. ', '2025-10-18 14:12:09', 1, 1, 2),
(55, 'Realizó una compra por $ 31750. Orden: ORD-20251018-68F3FBEFE1997', '2025-10-18 16:43:28', 2, 2, 2),
(56, 'Agregó una reseña de 1 estrellas a un producto', '2025-10-18 16:47:34', 2, 2, 2),
(57, 'Eliminó un producto de sus favoritos', '2025-10-19 00:39:44', 3, 2, 2),
(58, 'Limpió todos sus productos favoritos', '2025-10-19 00:39:51', 3, 2, 2),
(59, 'Eliminó un producto de sus favoritos', '2025-10-19 00:45:14', 3, 2, 2),
(60, 'Eliminó un producto de sus favoritos', '2025-10-19 11:27:04', 3, 2, 2),
(61, 'Agregó un producto a sus favoritos', '2025-10-19 23:48:57', 1, 2, 2),
(62, 'Eliminó un producto de sus favoritos', '2025-10-20 17:58:08', 3, 2, 2),
(63, 'Agregó un producto a sus favoritos', '2025-10-20 17:58:13', 1, 2, 2),
(64, 'Eliminó un producto de sus favoritos', '2025-10-20 17:58:18', 3, 2, 2),
(65, 'Agregó un producto a sus favoritos', '2025-10-20 17:58:43', 1, 2, 2),
(66, 'Eliminó un producto de sus favoritos', '2025-10-20 18:00:16', 3, 2, 2),
(67, 'Agregó un producto a sus favoritos', '2025-10-20 18:01:07', 1, 2, 2),
(68, 'Eliminó un producto de sus favoritos', '2025-10-20 18:03:10', 3, 2, 2),
(69, 'Eliminó un producto de sus favoritos', '2025-10-20 18:03:20', 3, 2, 2),
(70, 'Eliminó un producto de sus favoritos', '2025-10-20 18:03:23', 3, 2, 2),
(71, 'Agregó un producto a sus favoritos', '2025-10-20 18:03:37', 1, 2, 2),
(72, 'Eliminó un producto de sus favoritos', '2025-10-20 18:03:45', 3, 2, 2),
(73, 'Agregó un producto a sus favoritos', '2025-10-20 18:03:47', 1, 2, 2),
(74, 'Agregó un producto a sus favoritos', '2025-10-21 21:14:34', 1, 2, 2),
(75, 'Eliminó un producto de sus favoritos', '2025-10-21 21:14:38', 3, 2, 2),
(76, 'Agregó una reseña de 1 estrellas a un producto', '2025-10-21 21:30:02', 2, 2, 2),
(77, 'Eliminó un producto de sus favoritos', '2025-10-21 22:34:43', 3, 2, 2),
(78, 'Agregó un producto a sus favoritos', '2025-10-21 22:35:20', 1, 2, 2),
(79, 'Eliminó un producto de sus favoritos', '2025-10-21 22:36:26', 3, 2, 2),
(80, 'Agregó un producto a sus favoritos', '2025-10-21 22:37:09', 1, 2, 2),
(81, 'Eliminó un producto de sus favoritos', '2025-10-21 22:37:12', 3, 2, 2),
(82, 'Agregó un producto a sus favoritos', '2025-10-21 22:38:54', 1, 2, 2),
(83, 'Eliminó un producto de sus favoritos', '2025-10-21 22:39:06', 3, 2, 2),
(84, 'Agregó un producto a sus favoritos', '2025-10-21 22:39:09', 1, 2, 2),
(85, 'Eliminó un producto de sus favoritos', '2025-10-22 00:34:15', 3, 2, 2),
(86, 'Agregó un producto a sus favoritos', '2025-10-22 00:34:22', 1, 2, 2),
(87, 'Eliminó un producto de sus favoritos', '2025-10-22 00:34:49', 3, 2, 2),
(88, 'Agregó un producto a sus favoritos', '2025-10-22 00:35:05', 1, 2, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imagen`
--

CREATE TABLE `imagen` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_ediccion` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(10) NOT NULL DEFAULT 'A',
  `id_producto` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marca`
--

CREATE TABLE `marca` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `imagen` varchar(100) NOT NULL DEFAULT 'marca_default.png',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_ediccion` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(10) NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `marca`
--

INSERT INTO `marca` (`id`, `nombre`, `descripcion`, `imagen`, `fecha_creacion`, `fecha_ediccion`, `estado`) VALUES
(3, 'NexusBuy', '', 'marca_default.png', '2025-10-08 23:50:14', '2025-10-08 23:50:14', 'A');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metodo_pago`
--

CREATE TABLE `metodo_pago` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo` enum('tarjeta','paypal','transferencia') NOT NULL,
  `titular` varchar(255) NOT NULL,
  `numero` varchar(255) NOT NULL,
  `fecha_vencimiento` varchar(10) DEFAULT NULL,
  `cvv` varchar(10) DEFAULT NULL,
  `paypal_email` varchar(255) DEFAULT NULL,
  `banco` varchar(255) DEFAULT NULL,
  `numero_cuenta` varchar(255) DEFAULT NULL,
  `predeterminado` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(10) NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `metodo_pago`
--

INSERT INTO `metodo_pago` (`id`, `id_usuario`, `tipo`, `titular`, `numero`, `fecha_vencimiento`, `cvv`, `paypal_email`, `banco`, `numero_cuenta`, `predeterminado`, `fecha_creacion`, `estado`) VALUES
(1, 2, 'transferencia', '', '', NULL, NULL, NULL, 'Metropolitano', '1234567890123456', 0, '2025-10-17 17:40:17', 'A'),
(2, 2, '', '', '', NULL, NULL, NULL, NULL, NULL, 0, '2025-10-17 17:59:48', 'A'),
(3, 2, '', '', '', NULL, NULL, NULL, NULL, NULL, 0, '2025-10-17 21:07:53', 'A'),
(4, 2, '', '', '', NULL, NULL, NULL, NULL, NULL, 0, '2025-10-17 21:54:15', 'A'),
(5, 2, '', '', '', NULL, NULL, NULL, NULL, NULL, 1, '2025-10-18 16:43:28', 'A');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modulo`
--

CREATE TABLE `modulo` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `icono` varchar(100) NOT NULL,
  `estado` varchar(10) NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `modulo`
--

INSERT INTO `modulo` (`id`, `nombre`, `icono`, `estado`) VALUES
(1, 'Mi Perfil', '<i class=\"fas fa-user bg-info\"></i>', 'A'),
(2, 'Mis compras', '<i class=\"fas fa-shopping-cart bg bg-success\"></i>', 'A'),
(3, 'Reseña', '<i class=\"fas fa-star\"></i>', 'A');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `municipio`
--

CREATE TABLE `municipio` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `id_provincia` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `municipio`
--

INSERT INTO `municipio` (`id`, `nombre`, `id_provincia`) VALUES
(1, 'Consolación del Sur', 1),
(2, 'Guane', 1),
(3, 'La Palma', 1),
(4, 'Los Palacios', 1),
(5, 'Mantua', 1),
(6, 'Minas de Matahambre', 1),
(7, 'Pinar del Río', 1),
(8, 'San Juan y Martínez', 1),
(9, 'San Luis', 1),
(10, 'Sandino', 1),
(11, 'Viñales', 1),
(12, 'Alquízar', 2),
(13, 'Artemisa', 2),
(14, 'Bauta', 2),
(15, 'Caimito', 2),
(16, 'Guanajay', 2),
(17, 'Güira de Melena', 2),
(18, 'Mariel', 2),
(19, 'San Antonio de los Baños', 2),
(20, 'Bahía Honda', 2),
(21, 'San Cristóbal', 2),
(22, 'Candelaria', 2),
(23, 'Batabanó', 3),
(24, 'Bejucal', 3),
(25, 'Güines', 3),
(26, 'Jaruco', 3),
(27, 'Madruga', 3),
(28, 'Melena del Sur', 3),
(29, 'Nueva Paz', 3),
(30, 'Quivicán', 3),
(31, 'San José de las Lajas', 3),
(32, 'San Nicolás de Bari', 3),
(33, 'Santa Cruz del Norte', 3),
(34, 'Arroyo Naranjo', 4),
(35, 'Boyeros', 4),
(36, 'Centro Habana', 4),
(37, 'Cerro', 4),
(38, 'Cotorro', 4),
(39, 'Diez de Octubre', 4),
(40, 'Guanabacoa', 4),
(41, 'Habana del Este', 4),
(42, 'Habana Vieja', 4),
(43, 'La Lisa', 4),
(44, 'Marianao', 4),
(45, 'Playa', 4),
(46, 'Plaza', 4),
(47, 'Regla', 4),
(48, 'San Miguel del Padrón', 4),
(49, 'Calimete', 5),
(50, 'Cárdenas', 5),
(51, 'Ciénaga de Zapata', 5),
(52, 'Colón', 5),
(53, 'Jagüey Grande', 5),
(54, 'Jovellanos', 5),
(55, 'Limonar', 5),
(56, 'Los Arabos', 5),
(57, 'Martí', 5),
(58, 'Matanzas', 5),
(59, 'Pedro Betancourt', 5),
(60, 'Perico', 5),
(61, 'Unión de Reyes', 5),
(62, 'Abreus', 6),
(63, 'Aguada de Pasajeros', 6),
(64, 'Cienfuegos', 6),
(65, 'Cruces', 6),
(66, 'Cumanayagua', 6),
(67, 'Palmira', 6),
(68, 'Rodas', 6),
(69, 'Santa Isabel de las Lajas', 6),
(71, 'Caibarién', 7),
(72, 'Camajuaní', 7),
(73, 'Cifuentes', 7),
(74, 'Corralillo', 7),
(75, 'Encrucijada', 7),
(76, 'Manicaragua', 7),
(77, 'Placetas', 7),
(78, 'Quemado de Güines', 7),
(79, 'Ranchuelo', 7),
(80, 'Remedios', 7),
(81, 'Sagua la Grande', 7),
(82, 'Santa Clara', 7),
(83, 'Santo Domingo', 7),
(84, 'Cabaigúan', 8),
(85, 'Fomento', 8),
(86, 'Jatibonico', 8),
(87, 'La Sierpe', 8),
(88, 'Sancti Spíritus', 8),
(89, 'Taguasco', 8),
(90, 'Trinidad', 8),
(91, 'Yaguajay', 8),
(92, 'Ciro Redondo', 9),
(93, 'Baragúa', 9),
(94, 'Bolivia', 9),
(95, 'Chambas', 9),
(96, 'Ciego de Ávila', 9),
(97, 'Florencia', 9),
(98, 'Majagua', 9),
(99, 'Morón', 9),
(100, 'Primero de Enero', 9),
(101, 'Venezuela', 9),
(102, 'Camagüey', 10),
(103, 'Carlos Manuel de Céspedes', 10),
(104, 'Esmeralda', 10),
(105, 'Florida', 10),
(106, 'Guaimaro', 10),
(107, 'Jimagüayú', 10),
(108, 'Minas', 10),
(109, 'Najasa', 10),
(110, 'Nuevitas', 10),
(111, 'Santa Cruz del Sur', 10),
(112, 'Sibanicú', 10),
(113, 'Sierra de Cubitas', 10),
(114, 'Vertientes', 10),
(115, 'Amancio Rodríguez', 11),
(116, 'Colombia', 11),
(117, 'Jesús Menéndez', 11),
(118, 'Jobabo', 11),
(119, 'Las Tunas', 11),
(120, 'Majibacoa', 11),
(121, 'Manatí', 11),
(122, 'Puerto Padre', 11),
(123, 'Antilla', 1),
(124, 'Báguanos', 12),
(125, 'Banes', 12),
(126, 'Cacocum', 12),
(127, 'Calixto García', 12),
(128, 'Cueto', 12),
(129, 'Frank País', 12),
(130, 'Gibara', 12),
(131, 'Holguín', 12),
(132, 'Mayarí', 12),
(133, 'Moa', 12),
(134, 'Rafael Freyre', 12),
(135, 'Sagua de Tánamo', 12),
(136, 'Urbano Noris', 12),
(137, 'Contramaestre', 13),
(138, 'Guamá', 13),
(139, 'Julio Antonio Mella', 13),
(140, 'Palma Soriano', 13),
(141, 'San Luis', 13),
(142, 'Santiago de Cuba', 13),
(143, 'Segundo Frente', 13),
(144, 'Songo la Maya', 13),
(145, 'Tercer Frente', 13),
(146, 'Baracoa', 14),
(147, 'Caimanera', 14),
(148, 'El Salvador', 14),
(149, 'Guantánamo', 14),
(150, 'Imías', 14),
(151, 'Maisí', 14),
(152, 'Manuel Tames', 14),
(153, 'Niceto Pérez', 14),
(154, 'San Antonio del Sur', 14),
(155, 'Yateras', 14),
(156, 'Isla de la Juventud', 15),
(157, 'Bartolomé Masó', 16),
(158, 'Bayamo', 16),
(159, 'Buey Arriba', 16),
(160, 'Campechuela', 16),
(161, 'Cauto Cristo', 16),
(162, 'Guisa', 16),
(163, 'Jiguaní', 16),
(164, 'Manzanillo', 16),
(165, 'Media Luna', 16),
(166, 'Niquero', 16),
(167, 'Pilón', 16),
(168, 'Río Cauto', 16),
(169, 'Yara', 16);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificacion`
--

CREATE TABLE `notificacion` (
  `id` int(11) NOT NULL,
  `titulo` varchar(500) NOT NULL,
  `asunto` varchar(500) NOT NULL,
  `contenido` varchar(1000) NOT NULL,
  `imagen` varchar(500) NOT NULL,
  `url_1` varchar(1000) NOT NULL,
  `url_2` varchar(1000) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `estado_abierto` varchar(10) NOT NULL DEFAULT ' 0',
  `estado` varchar(10) NOT NULL DEFAULT 'A',
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden`
--

CREATE TABLE `orden` (
  `id` int(11) NOT NULL,
  `numero_orden` varchar(50) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_metodo_pago` int(11) DEFAULT NULL,
  `estado` enum('pendiente','procesando','completado','cancelado','reembolsado') NOT NULL DEFAULT 'pendiente',
  `subtotal` decimal(10,2) NOT NULL,
  `envio` decimal(10,2) NOT NULL,
  `descuento` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `direccion_envio` text NOT NULL,
  `notas` text DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `orden`
--

INSERT INTO `orden` (`id`, `numero_orden`, `id_usuario`, `id_metodo_pago`, `estado`, `subtotal`, `envio`, `descuento`, `total`, `direccion_envio`, `notas`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'ORD-20251017-68F2B7C128E56', 2, NULL, 'completado', '2500.00', '0.00', '0.00', '2500.00', 'asdasd Perez, San Jose, Quivicán, Mayabeque. Tel: 56239933, Email: adsdfg@gmail.com', NULL, '2025-10-17 17:40:17', '2025-10-17 17:40:17'),
(2, 'ORD-20251017-68F2BC53D35F2', 2, NULL, 'pendiente', '719.10', '0.00', '0.00', '719.10', 'asdasd Perez, San Jose, Songo la Maya, Santiago de Cuba. Tel: 56239933, Email: adsdfg@gmail.com', NULL, '2025-10-17 17:59:47', '2025-10-17 17:59:47'),
(3, 'ORD-20251018-68F2E8695BCBB', 2, NULL, 'procesando', '6750.00', '0.00', '0.00', '6750.00', 'asdasd Perez, San Jose, Alquízar, Artemisa. Tel: 56239933, Email: adsdfg@gmail.com', NULL, '2025-10-17 21:07:53', '2025-10-17 21:07:53'),
(4, 'ORD-20251018-68F2F3471A4B6', 2, NULL, 'cancelado', '13595.50', '0.00', '0.00', '13595.50', 'asdasd Perez, San Jose, Julio Antonio Mella, Santiago de Cuba. Tel: 56239933, Email: adsdfg@gmail.com', NULL, '2025-10-17 21:54:15', '2025-10-17 21:54:15'),
(5, 'ORD-20251018-68F3FBEFE1997', 2, NULL, 'completado', '31750.00', '0.00', '0.00', '31750.00', 'asdasd Perez, San Jose, Quivicán, Mayabeque. Tel: 56239933, Email: adsdfg@gmail.com', NULL, '2025-10-18 16:43:27', '2025-10-18 16:43:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden_detalle`
--

CREATE TABLE `orden_detalle` (
  `id` int(11) NOT NULL,
  `id_orden` int(11) NOT NULL,
  `id_producto_tienda` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `descuento` decimal(5,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `orden_detalle`
--

INSERT INTO `orden_detalle` (`id`, `id_orden`, `id_producto_tienda`, `cantidad`, `precio_unitario`, `descuento`, `subtotal`) VALUES
(1, 1, 1, 1, '2500.00', '0.00', '2500.00'),
(2, 2, 10, 1, '799.00', '10.00', '719.10'),
(3, 3, 3, 3, '2500.00', '10.00', '6750.00'),
(4, 4, 1, 4, '2500.00', '0.00', '10000.00'),
(5, 4, 10, 5, '799.00', '10.00', '3595.50'),
(6, 5, 3, 3, '2500.00', '10.00', '6750.00'),
(7, 5, 1, 10, '2500.00', '0.00', '25000.00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `id` int(11) NOT NULL,
  `nombre` varchar(500) NOT NULL,
  `sku` varchar(100) NOT NULL,
  `detalles` text NOT NULL,
  `imagen_principal` varchar(100) NOT NULL DEFAULT 'producto_default.png',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_ediccion` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(10) NOT NULL DEFAULT 'A',
  `precio_venta` decimal(10,2) NOT NULL DEFAULT 650.00,
  `costo_promedio` decimal(10,2) NOT NULL DEFAULT 450.00,
  `stock_minimo` int(11) NOT NULL DEFAULT 25,
  `stock_maximo` int(11) NOT NULL DEFAULT 100,
  `id_marca` int(11) NOT NULL,
  `id_subcategoria` int(11) NOT NULL,
  `id_unidad_medida` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`id`, `nombre`, `sku`, `detalles`, `imagen_principal`, `fecha_creacion`, `fecha_ediccion`, `estado`, `precio_venta`, `costo_promedio`, `stock_minimo`, `stock_maximo`, `id_marca`, `id_subcategoria`, `id_unidad_medida`) VALUES
(1, 'ACEITE', 'PROD-001', 'hola', 'aceite_1L.jpg', '2025-10-09 11:53:57', '2025-10-09 11:53:57', 'A', '950.00', '500.00', 15, 100, 3, 95, 7),
(2, 'AJO ', 'PROD-002', '', 'ajo.jpg', '2025-10-09 11:53:57', '2025-10-09 11:53:57', 'A', '500.00', '250.00', 20, 100, 3, 22, 2),
(3, 'ARROZ PROGRESO ', 'PROD-003', '', 'arroz_progreso.jpg', '2025-10-09 11:53:57', '2025-10-09 11:53:57', 'A', '650.00', '300.00', 25, 100, 3, 22, 2),
(4, 'ATUN ACEITE LATA (174gr.)', 'PROD-004', '', 'atun_aceite_lata(174gr.).jpg', '2025-10-09 11:53:57', '2025-10-09 11:53:57', 'A', '300.00', '150.00', 25, 100, 3, 24, 10),
(9, 'AZUCAR GLASS ', 'PROD-005', '', 'azucar_glass.jpg', '2025-10-09 12:54:34', '2025-10-09 12:54:34', 'A', '650.00', '450.00', 25, 100, 3, 22, 2),
(10, 'BOMBON CHICO (340gr.)', 'PROD-006', '', 'bombom_chiqui.jpg', '2025-10-09 12:54:34', '2025-10-09 12:54:34', 'A', '650.00', '450.00', 25, 100, 3, 146, 11),
(11, 'BOMBON MINIMINI (750gr.)', 'PROD-007', '', 'bombom_minimini.jpg', '2025-10-09 12:54:34', '2025-10-09 12:54:34', 'A', '650.00', '450.00', 25, 100, 3, 146, 11),
(12, 'CEREAL QUAKER STARS FRUTAS (400gr.) CA', 'PROD-008', '', 'cerial_Quaker_stars_fruta.jpg', '2025-10-09 12:54:34', '2025-10-09 12:54:34', 'A', '650.00', '450.00', 25, 100, 3, 147, 19),
(13, 'CIRUELA PASA S/S', 'PROD-009', '', 'ciruela_pasa.jpg', '2025-10-09 12:54:34', '2025-10-09 12:54:34', 'A', '650.00', '450.00', 25, 100, 3, 22, 2),
(14, 'CIRUELA PASA S/S', 'PROD-010', '', 'ciruela_pasa.jpg', '2025-10-09 12:55:52', '2025-10-09 12:55:52', 'A', '650.00', '450.00', 25, 100, 3, 22, 20);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_tienda`
--

CREATE TABLE `producto_tienda` (
  `id` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `descuento` decimal(5,2) NOT NULL DEFAULT 0.00,
  `garantia` varchar(100) NOT NULL,
  `estado_envio` enum('gratis','pago') NOT NULL DEFAULT 'pago',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_ediccion` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(10) NOT NULL DEFAULT 'A',
  `id_producto` int(11) NOT NULL,
  `id_tienda` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `producto_tienda`
--

INSERT INTO `producto_tienda` (`id`, `precio`, `cantidad`, `descuento`, `garantia`, `estado_envio`, `fecha_creacion`, `fecha_ediccion`, `estado`, `id_producto`, `id_tienda`) VALUES
(1, '2500.00', 15, '0.00', '', '', '2025-10-10 11:06:59', '2025-10-10 11:06:59', 'A', 4, 1),
(2, '650.00', 15, '0.00', '', '', '2025-10-10 11:06:59', '2025-10-10 11:06:59', 'A', 9, 1),
(3, '2500.00', 4, '10.00', '3 años', 'gratis', '2025-10-10 11:02:21', '2025-10-10 11:02:21', 'A', 1, 1),
(4, '1000.00', 10, '5.00', '', '', '2025-10-10 11:05:39', '2025-10-10 11:05:39', 'A', 2, 1),
(5, '2500.00', 10, '0.00', '', '', '2025-10-10 11:05:39', '2025-10-10 11:05:39', 'A', 3, 1),
(6, '230.00', 12, '10.00', '', 'gratis', '2025-10-10 11:09:50', '2025-10-10 11:09:50', 'A', 10, 1),
(7, '650.00', 15, '5.00', '', '', '2025-10-10 11:09:50', '2025-10-10 11:09:50', 'A', 12, 1),
(8, '120.00', 20, '3.00', '', 'gratis', '2025-10-10 11:09:50', '2025-10-10 11:09:50', 'A', 13, 1),
(9, '450.00', 30, '0.00', '', '', '2025-10-10 11:23:21', '2025-10-10 11:23:21', 'A', 11, 1),
(10, '799.00', 4, '10.00', '', '', '2025-10-10 11:23:21', '2025-10-10 11:23:21', 'A', 12, 1),
(11, '450.00', 10, '0.00', '', '', '2025-10-10 11:26:14', '2025-10-10 11:26:14', 'A', 13, 1),
(12, '50.00', 10, '0.00', '', '', '2025-10-10 11:26:14', '2025-10-10 11:26:14', 'A', 14, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `provincia`
--

CREATE TABLE `provincia` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `provincia`
--

INSERT INTO `provincia` (`id`, `nombre`) VALUES
(1, 'Pinar del Río'),
(2, 'Artemisa'),
(3, 'Mayabeque'),
(4, 'La Habana'),
(5, 'Matanzas'),
(6, 'Cienfuegos'),
(7, 'Villa Clara'),
(8, 'Sancti Spíritus'),
(9, 'Ciego de Ávila'),
(10, 'Camagüey'),
(11, 'Las Tunas'),
(12, 'Holguín'),
(13, 'Santiago de Cuba'),
(14, 'Guantánamo'),
(15, 'Isla de la Juventud'),
(16, 'Granma');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reseña`
--

CREATE TABLE `reseña` (
  `id` int(11) NOT NULL,
  `calificacion` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_ediccion` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(10) NOT NULL DEFAULT 'A',
  `id_producto_tienda` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `reseña`
--

INSERT INTO `reseña` (`id`, `calificacion`, `descripcion`, `fecha_creacion`, `fecha_ediccion`, `estado`, `id_producto_tienda`, `id_usuario`) VALUES
(2, 5, 'bueno', '2025-10-10 17:36:00', '2025-10-10 17:36:00', 'A', 3, 2),
(3, 3, 'probando la reseña', '2025-10-15 18:36:19', '2025-10-15 18:36:19', 'A', 2, 2),
(4, 1, 'probando nuevamente las reseñas', '2025-10-18 14:10:35', '2025-10-18 14:10:35', 'A', 1, 2),
(5, 1, 'comprate este ajo, esta bueno', '2025-10-18 16:47:34', '2025-10-18 16:47:34', 'A', 4, 2),
(6, 1, 'probando a ver', '2025-10-21 21:30:01', '2025-10-21 21:30:01', 'A', 5, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subcategoria`
--

CREATE TABLE `subcategoria` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_ediccion` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(10) NOT NULL DEFAULT 'A',
  `id_categoria` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `subcategoria`
--

INSERT INTO `subcategoria` (`id`, `nombre`, `descripcion`, `fecha_creacion`, `fecha_ediccion`, `estado`, `id_categoria`) VALUES
(1, 'Smartphones Android', '', '2025-09-25 17:28:06', '2025-09-25 17:28:06', 'A', 1),
(2, 'Computadoras de Escritorio', '', '2025-09-25 17:28:06', '2025-09-25 17:28:06', 'A', 4),
(3, 'Smart TVs', '', '2025-10-06 13:20:52', '2025-10-06 13:20:52', 'A', 16),
(4, 'Audífonos', '', '2025-10-06 13:20:52', '2025-10-06 13:20:52', 'A', 17),
(5, 'Cámaras DSLR', '', '2025-10-06 13:20:52', '2025-10-06 13:20:52', 'A', 18),
(6, 'Consolas', '', '2025-10-06 13:20:52', '2025-10-06 13:20:52', 'A', 19),
(7, 'Material de Escritura', '\r\n', '2025-10-06 14:15:35', '2025-10-06 14:15:35', 'A', 15),
(8, 'Papel e Impresión', '', '2025-10-06 14:15:35', '2025-10-06 14:15:35', 'A', 15),
(9, 'Organizadores', '', '2025-10-06 14:15:35', '2025-10-06 14:15:35', 'A', 15),
(10, 'Computadoras', '', '2025-10-06 14:15:35', '2025-10-06 14:15:35', 'A', 15),
(11, 'Mobiliario de Oficina', '', '2025-10-06 14:15:35', '2025-10-06 14:15:35', 'A', 15),
(12, 'Alimento para Mascotas', '', '2025-10-06 14:43:17', '2025-10-06 14:43:17', 'A', 12),
(13, 'Juguetes y Accesorios', '', '2025-10-06 14:43:17', '2025-10-06 14:43:17', 'A', 12),
(14, 'Salud y Cuidado', '', '2025-10-06 14:43:17', '2025-10-06 14:43:17', 'A', 12),
(15, 'Transporte y Viaje', '', '2025-10-06 14:43:17', '2025-10-06 14:43:17', 'A', 12),
(16, 'Aseo y Limpiza', '', '2025-10-06 14:43:17', '2025-10-06 14:43:17', 'A', 12),
(17, 'Motor y Transmisión', '', '2025-10-06 14:43:17', '2025-10-06 14:43:17', 'A', 14),
(18, 'Herramientas Manuales', '', '2025-10-06 14:43:17', '2025-10-06 14:43:17', 'A', 20),
(19, 'Aceite de Motor', '', '2025-10-06 14:43:17', '2025-10-06 14:43:17', 'A', 14),
(20, 'LLantas Nuevas', '', '2025-10-06 14:43:17', '2025-10-06 14:43:17', 'A', 14),
(21, 'Audio para Auo', '', '2025-10-06 14:43:17', '2025-10-06 14:43:17', 'A', 14),
(22, 'Arroz y Granos', '', '2025-10-06 15:03:28', '2025-10-06 15:03:28', 'A', 2),
(23, 'Frutas y Verduras', '', '2025-10-06 15:03:28', '2025-10-06 15:03:28', 'A', 2),
(24, 'Carnes y Pescados', '', '2025-10-06 15:03:28', '2025-10-06 15:03:28', 'A', 2),
(25, 'Lácteos y Huevos', '', '2025-10-06 15:03:28', '2025-10-06 15:03:28', 'A', 2),
(26, 'Aguas, Refrescos y Jugos', '', '2025-10-06 15:03:28', '2025-10-06 15:03:28', 'A', 2),
(27, 'Juguetes', '', '2025-10-06 15:03:28', '2025-10-06 15:03:28', 'A', 9),
(28, 'Juguetes Educativos', '\r\n', '2025-10-06 15:03:28', '2025-10-06 15:03:28', 'A', 9),
(29, 'Juegos de Mesa', '', '2025-10-06 15:03:28', '2025-10-06 15:03:28', 'A', 9),
(30, 'Juguetes de Exterior', '', '2025-10-06 15:03:28', '2025-10-06 15:03:28', 'A', 9),
(31, 'Artículos papa Bebés', '', '2025-10-06 15:03:28', '2025-10-06 15:03:28', 'A', 9),
(32, 'Vitaminas y Suplementos', '', '2025-10-06 15:56:18', '2025-10-06 15:56:18', 'A', 6),
(33, 'Cuidado Médicos', '', '2025-10-06 15:56:18', '2025-10-06 15:56:18', 'A', 6),
(34, 'Cuidadio para Adultos Mayores', '', '2025-10-06 15:56:18', '2025-10-06 15:56:18', 'A', 6),
(35, 'Maternidad y Bebés', '', '2025-10-06 15:56:18', '2025-10-06 15:56:18', 'A', 6),
(36, 'Salud Sexual', '', '2025-10-06 15:56:18', '2025-10-06 15:56:18', 'A', 6),
(37, 'Ropa Deportiva', '', '2025-10-06 15:56:18', '2025-10-06 15:56:18', 'A', 22),
(38, 'Deportes al Aire Libre', '', '2025-10-06 15:56:18', '2025-10-06 15:56:18', 'A', 13),
(39, 'Deportes de Equipo', '', '2025-10-06 15:56:18', '2025-10-06 15:56:18', 'A', 13),
(40, 'Trajes de Baño', '', '2025-10-06 15:56:18', '2025-10-06 15:56:18', 'A', 13),
(41, 'Suplementos Deportivos', '', '2025-10-06 15:56:18', '2025-10-06 15:56:18', 'A', 13),
(47, 'Cremas para la Piel', '', '2025-10-06 16:07:11', '2025-10-06 16:07:11', 'A', 3),
(48, 'Maquillaje', '', '2025-10-06 16:07:11', '2025-10-06 16:07:11', 'A', 3),
(49, 'Perfumes', '', '2025-10-06 16:07:11', '2025-10-06 16:07:11', 'A', 3),
(50, 'Shampoo y Acondicionador', '', '2025-10-06 16:07:11', '2025-10-06 16:07:11', 'A', 3),
(51, 'Desodorantes', '', '2025-10-06 16:07:11', '2025-10-06 16:07:11', 'A', 3),
(52, 'iPhones', '', '2025-10-06 16:43:14', '2025-10-06 16:43:14', 'A', 1),
(53, 'Teléfonos Básicos', '', '2025-10-06 16:43:14', '2025-10-06 16:43:14', 'A', 1),
(54, 'Smartphones Reacondicionados', '', '2025-10-06 16:43:14', '2025-10-06 16:43:14', 'A', 1),
(55, 'Accesorios para Teléfonos', '', '2025-10-06 16:43:14', '2025-10-06 16:43:14', 'A', 1),
(56, 'Laptops', '', '2025-10-06 16:50:32', '2025-10-06 16:50:32', 'A', 4),
(57, 'Tablets', '', '2025-10-06 16:50:32', '2025-10-06 16:50:32', 'A', 1),
(58, 'Ultrabooks', '', '2025-10-06 16:50:32', '2025-10-06 16:50:32', 'A', 4),
(59, 'Workstation', '', '2025-10-06 16:50:32', '2025-10-06 16:50:32', 'A', 4),
(60, '4K/ 8K TVs', '', '2025-10-06 16:52:10', '2025-10-06 16:52:10', 'A', 16),
(61, 'Proyectores', '', '2025-10-06 16:52:10', '2025-10-06 16:52:10', 'A', 16),
(62, 'Barras de Sonido', '', '2025-10-06 16:52:10', '2025-10-06 16:52:10', 'A', 16),
(63, 'Soporte para TVs y Video', '', '2025-10-06 16:52:10', '2025-10-06 16:52:10', 'A', 16),
(64, 'Bocinas Bluetooth', '', '2025-10-06 16:54:21', '2025-10-06 16:54:21', 'A', 17),
(65, 'Sistemas de Sonido', '', '2025-10-06 16:54:21', '2025-10-06 16:54:21', 'A', 17),
(66, 'Equipos de Audio', '', '2025-10-06 16:54:21', '2025-10-06 16:54:21', 'A', 17),
(67, 'Micrófonos', '', '2025-10-06 16:54:21', '2025-10-06 16:54:21', 'A', 17),
(68, 'Cámaras Mirroless', '', '2025-10-06 17:00:17', '2025-10-06 17:00:17', 'A', 18),
(69, 'Cámaras Deportivas', '', '2025-10-06 17:00:17', '2025-10-06 17:00:17', 'A', 18),
(70, 'Drones', '', '2025-10-06 17:00:17', '2025-10-06 17:00:17', 'A', 18),
(71, 'Accesorios de Fotografía', '', '2025-10-06 17:00:17', '2025-10-06 17:00:17', 'A', 18),
(72, 'Juegos Físicos', '', '2025-10-06 17:03:04', '2025-10-06 17:03:04', 'A', 19),
(73, 'Juegos Digitales', '', '2025-10-06 17:03:04', '2025-10-06 17:03:04', 'A', 19),
(74, 'Accesorios Gaming', '', '2025-10-06 17:03:04', '2025-10-06 17:03:04', 'A', 19),
(75, 'PC Gaming', '', '2025-10-06 17:03:04', '2025-10-06 17:03:04', 'A', 19),
(76, 'Impresoras', '', '2025-10-06 17:13:07', '2025-10-06 17:13:07', 'A', 15),
(77, 'Escáneres', '', '2025-10-06 17:13:07', '2025-10-06 17:13:07', 'A', 15),
(78, 'Calculadoras', '', '2025-10-06 17:13:07', '2025-10-06 17:13:07', 'A', 15),
(79, 'Teléfonos', '', '2025-10-06 17:13:07', '2025-10-06 17:13:07', 'A', 15),
(80, 'Iluminación', '', '2025-10-06 17:18:10', '2025-10-06 17:18:10', 'A', 14),
(81, 'Accesorios Exteriores', '', '2025-10-06 17:18:10', '2025-10-06 17:18:10', 'A', 14),
(82, 'Accesorios Interiores', '', '2025-10-06 17:18:10', '2025-10-06 17:18:10', 'A', 14),
(83, 'Frenos y Suspensión', '', '2025-10-06 17:18:10', '2025-10-06 17:18:10', 'A', 14),
(84, 'Herramientas Eléctricas', '', '2025-10-06 17:20:32', '2025-10-06 17:20:32', 'A', 20),
(85, 'Cajas de Herramientas', '', '2025-10-06 17:20:32', '2025-10-06 17:20:32', 'A', 20),
(86, 'Medición y Precisión', '', '2025-10-06 17:20:32', '2025-10-06 17:20:32', 'A', 20),
(87, 'Fluidos y Lubricantes', '', '2025-10-06 17:22:13', '2025-10-06 17:22:13', 'A', 14),
(88, 'Aditivos', '', '2025-10-06 17:22:13', '2025-10-06 17:22:13', 'A', 14),
(89, 'Limpiadores', '', '2025-10-06 17:22:13', '2025-10-06 17:22:13', 'A', 14),
(90, 'Químicos Especializados', '', '2025-10-06 17:22:13', '2025-10-06 17:22:13', 'A', 14),
(91, 'Rines', '', '2025-10-06 17:24:31', '2025-10-06 17:24:31', 'A', 14),
(92, 'Válvulas y Balanceo', '', '2025-10-06 17:24:31', '2025-10-06 17:24:31', 'A', 14),
(93, 'Cámaras de Aire', '', '2025-10-06 17:24:31', '2025-10-06 17:24:31', 'A', 14),
(94, 'Reparación de Llantas', '', '2025-10-06 17:24:31', '2025-10-06 17:24:31', 'A', 14),
(95, 'Aceites y Vinagres', '', '2025-10-06 17:27:25', '2025-10-06 17:27:25', 'A', 2),
(96, 'Harinas y Polvos', '', '2025-10-06 17:27:25', '2025-10-06 17:27:25', 'A', 2),
(97, 'Legumbres', '', '2025-10-06 17:27:25', '2025-10-06 17:27:25', 'A', 2),
(98, 'Conservas', '', '2025-10-06 17:27:25', '2025-10-06 17:27:25', 'A', 2),
(99, 'Bebidas Energéticas', '', '2025-10-06 17:30:00', '2025-10-06 17:30:00', 'A', 2),
(100, 'Café y Té', '', '2025-10-06 17:30:00', '2025-10-06 17:30:00', 'A', 2),
(101, 'Bebidas Alcohólicas', '', '2025-10-06 17:30:00', '2025-10-06 17:30:00', 'A', 2),
(102, 'Cuchillas y Afeitado', '', '2025-10-06 17:56:43', '2025-10-06 17:56:43', 'A', 3),
(103, 'Higiene Bocal', '', '2025-10-06 17:56:43', '2025-10-06 17:56:43', 'A', 3),
(104, 'Productos para Peinar', '', '2025-10-06 17:56:43', '2025-10-06 17:56:43', 'A', 3),
(105, 'Coloración', '', '2025-10-06 17:56:43', '2025-10-06 17:56:43', 'A', 3),
(106, 'Accesorios para Cabello', '', '2025-10-06 17:56:43', '2025-10-06 17:56:43', 'A', 3),
(107, 'Protector Solar', '', '2025-10-06 17:56:43', '2025-10-06 17:56:43', 'A', 3),
(108, 'Mascarillas', '', '2025-10-06 17:56:43', '2025-10-06 17:56:43', 'A', 3),
(109, 'Zapatos Deportivos', '', '2025-10-06 17:59:35', '2025-10-06 17:59:35', 'A', 22),
(110, 'Equipo de Gimnasio', '', '2025-10-06 17:59:35', '2025-10-06 17:59:35', 'A', 22),
(111, 'Pesas y Mancuernas', '', '2025-10-06 17:59:35', '2025-10-06 17:59:35', 'A', 22),
(112, 'Accesorios de Fitness', '', '2025-10-06 17:59:35', '2025-10-06 17:59:35', 'A', 22),
(113, 'Toallas Deportivas', '', '2025-10-06 18:02:06', '2025-10-06 18:02:06', 'A', 13),
(114, 'Flotaores', '', '2025-10-06 18:02:06', '2025-10-06 18:02:06', 'A', 13),
(115, 'Accesorios de Natación', '', '2025-10-06 18:02:06', '2025-10-06 18:02:06', 'A', 13),
(116, 'Goggles', '', '2025-10-06 18:02:06', '2025-10-06 18:02:06', 'A', 13),
(117, 'Refrigeradores', '', '2025-10-06 18:08:10', '2025-10-06 18:08:10', 'A', 5),
(118, 'Lavadoras', '', '2025-10-06 18:08:10', '2025-10-06 18:08:10', 'A', 5),
(119, 'Secadoras', '', '2025-10-06 18:08:10', '2025-10-06 18:08:10', 'A', 5),
(120, 'Cocinas y Hornos', '', '2025-10-06 18:08:10', '2025-10-06 18:08:10', 'A', 5),
(121, 'Lavavajillas', '', '2025-10-06 18:08:10', '2025-10-06 18:08:10', 'A', 5),
(122, 'Microondas', '', '2025-10-06 18:08:10', '2025-10-06 18:08:10', 'A', 5),
(123, 'Licuadoras', '', '2025-10-06 18:08:10', '2025-10-06 18:08:10', 'A', 5),
(124, 'Cafeteras', '', '2025-10-06 18:08:10', '2025-10-06 18:08:10', 'A', 5),
(125, 'Aspiradoras', '', '2025-10-06 18:08:10', '2025-10-06 18:08:10', 'A', 5),
(126, 'Plancha de Ropa', '', '2025-10-06 18:08:10', '2025-10-06 18:08:10', 'A', 5),
(127, 'Ropa de Mujer', '', '2025-10-06 18:12:45', '2025-10-06 18:12:45', 'A', 7),
(128, 'Ropa de Hombre', '', '2025-10-06 18:12:45', '2025-10-06 18:12:45', 'A', 7),
(129, 'Ropa de Niños/a', '', '2025-10-06 18:12:45', '2025-10-06 18:12:45', 'A', 7),
(130, 'Calzado', '', '2025-10-06 18:12:45', '2025-10-06 18:12:45', 'A', 7),
(131, 'Bolsos y Carteras', '', '2025-10-06 18:12:45', '2025-10-06 18:12:45', 'A', 7),
(132, 'Cinturones', '', '2025-10-06 18:12:45', '2025-10-06 18:12:45', 'A', 7),
(133, 'Gafas', '', '2025-10-06 18:12:45', '2025-10-06 18:12:45', 'A', 7),
(134, 'Sombreros y Gorras', '', '2025-10-06 18:12:45', '2025-10-06 18:12:45', 'A', 7),
(135, 'Bisutería', '', '2025-10-06 18:12:45', '2025-10-06 18:12:45', 'A', 7),
(136, 'Muebles', '', '2025-10-06 18:45:25', '2025-10-06 18:45:25', 'A', 10),
(137, 'Decoración', '', '2025-10-06 18:45:25', '2025-10-06 18:45:25', 'A', 10),
(138, 'Utensilios de Cocina', '', '2025-10-06 18:45:25', '2025-10-06 18:45:25', 'A', 10),
(139, 'Vajillas', '', '2025-10-06 18:45:25', '2025-10-06 18:45:25', 'A', 10),
(140, 'Cristalería', '', '2025-10-06 18:45:25', '2025-10-06 18:45:25', 'A', 10),
(141, 'Cubiertos', '', '2025-10-06 18:45:25', '2025-10-06 18:45:25', 'A', 10),
(142, 'Accesorios de Baño', '', '2025-10-06 18:45:25', '2025-10-06 18:45:25', 'A', 10),
(143, 'Adornos de Exteriores', '', '2025-10-06 18:45:25', '2025-10-06 18:45:25', 'A', 10),
(144, 'Iluminarias de Interior', '', '2025-10-06 18:45:25', '2025-10-06 18:45:25', 'A', 10),
(145, 'Iluminaria de Exterior', '', '2025-10-06 18:45:25', '2025-10-06 18:45:25', 'A', 10),
(146, 'Dulce y Confituras', '', '2025-10-09 12:05:09', '2025-10-09 12:05:09', 'A', 2),
(147, 'Ceriales', '', '2025-10-09 12:40:17', '2025-10-09 12:40:17', 'A', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tienda`
--

CREATE TABLE `tienda` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `razon_social` varchar(200) NOT NULL,
  `direccion` varchar(100) NOT NULL,
  `RUC` varchar(12) NOT NULL,
  `imagen` varchar(100) NOT NULL DEFAULT 'tienda_default.png',
  `facebook` varchar(500) DEFAULT NULL,
  `instagram` varchar(500) DEFAULT NULL,
  `tiktok` varchar(500) DEFAULT NULL,
  `youtube` varchar(500) DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `email_contacto` varchar(200) DEFAULT NULL,
  `sitio_web` varchar(500) DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_ediccion` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(10) NOT NULL DEFAULT 'A',
  `id_usuario` int(11) NOT NULL,
  `id_municipio` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `tienda`
--

INSERT INTO `tienda` (`id`, `nombre`, `razon_social`, `direccion`, `RUC`, `imagen`, `facebook`, `instagram`, `tiktok`, `youtube`, `whatsapp`, `email_contacto`, `sitio_web`, `fecha_creacion`, `fecha_ediccion`, `estado`, `id_usuario`, `id_municipio`) VALUES
(1, 'NexusBuy - Store', 'Tienda de abarrotes', 'avenida las palmeras 2345', '12345678901', 'tienda_default.png', 'https://facebook.com/NexusBuyStore', 'https://instagram.com/NexusBuyStore', 'https://tiktok.com/@NexusbuyStore', 'https://youtube.com/c/NexusBuyStore', '+53555123456', 'ventas@nexusbuystore.com', 'https://nexusbuystore.com', '2025-09-25 17:32:36', '2025-09-25 17:32:36', 'A', 1, 36);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_historial`
--

CREATE TABLE `tipo_historial` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `icono` varchar(100) NOT NULL,
  `estado` varchar(10) NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `tipo_historial`
--

INSERT INTO `tipo_historial` (`id`, `nombre`, `icono`, `estado`) VALUES
(1, 'Editar', '<i class=\"far fa-edit\"></i>', 'A'),
(2, 'Crear', '<i class=\"fas fa-plus\"></i>', 'A'),
(3, 'Borrar', '<i class=\"far fa-trash-alt\"></i>', 'A'),
(4, 'Compra', '<i class=\"fas fa-shopping-cart\"></i>', 'A'),
(5, 'Eliminacion', '<i class=\"fas fa-trash-alt\"></i>', 'A'),
(6, 'Pago', '<i class=\"fas fa-credit-card\"></i>', 'A'),
(7, 'Envio', '<i class=\"fas fa-shipping-fast\"></i>', 'A'),
(8, 'Reseña', '<i class=\"fas fa-star\"></i>', 'A'),
(9, 'Login', '<i class=\"fas fa-sing-in-alt\"></i>', 'A'),
(10, 'Registro', '<i class=\"fas fa-user-plus\"></i>', 'A');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_usuario`
--

CREATE TABLE `tipo_usuario` (
  `id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `estado` varchar(10) NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `tipo_usuario`
--

INSERT INTO `tipo_usuario` (`id`, `tipo`, `estado`) VALUES
(1, 'Root', 'A'),
(2, 'Cliente', 'A'),
(3, 'Vendedor', 'A');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transaccion_pago`
--

CREATE TABLE `transaccion_pago` (
  `id` int(11) NOT NULL,
  `id_orden` int(11) NOT NULL,
  `referencia` varchar(100) NOT NULL,
  `metodo` varchar(50) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `estado` enum('pendiente','completado','fallido','rembolsado') NOT NULL DEFAULT 'pendiente',
  `respuesta_gateway` text DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `unidad_medida`
--

CREATE TABLE `unidad_medida` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `abreviatura` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `unidad_medida`
--

INSERT INTO `unidad_medida` (`id`, `nombre`, `abreviatura`) VALUES
(1, 'Gramo', 'g'),
(2, 'Kilogramo', 'kg'),
(3, 'Onza', 'oz'),
(4, 'Libra', 'lb'),
(5, 'Milimetro', 'ml'),
(6, 'Centímetro cúbico', 'cm^3'),
(7, 'Litro', 'l'),
(8, 'Galón', 'gal'),
(9, 'Unidad', 'u'),
(10, 'Pieza', 'pz'),
(11, 'Paquete', 'pqt'),
(12, 'Par', 'par'),
(13, 'Docena', 'doc'),
(14, 'Ciento', 'cto'),
(15, 'Centímetro', 'cm'),
(16, 'Metro', 'm'),
(17, 'Pulgada', 'in'),
(18, 'Metro cuadrado', 'm^2'),
(19, 'Caja', 'caja'),
(20, 'Lata', 'lata');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `user` varchar(50) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `nombres` varchar(50) NOT NULL,
  `apellidos` varchar(50) NOT NULL,
  `direccion` varchar(100) DEFAULT NULL,
  `dni` varchar(11) NOT NULL,
  `email` varchar(200) NOT NULL,
  `telefono` int(12) NOT NULL,
  `avatar` varchar(200) NOT NULL DEFAULT 'user_default.png',
  `estado` varchar(10) NOT NULL DEFAULT 'A',
  `id_tipo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `user`, `pass`, `nombres`, `apellidos`, `direccion`, `dni`, `email`, `telefono`, `avatar`, `estado`, `id_tipo`) VALUES
(1, 'david', 'zzgd1nsZg3Jt46AjxUKtjA==', 'Noel', 'Chacon Sanchez', 'Neptuno # 616 / Gervacio y Escobar', '98122121500', 'noeldavidchaconsanchez@gmail.com', 51004754, '68d4494eaebff-avatar5.png', 'A', 2),
(2, 'cliente', 'diIM9sU/SDXHo8CGwqqiuQ==', 'asdasd', 'Perez', NULL, '12345678901', 'adsdfg@gmail.com', 56239933, '68f3d8793a26b-884b9aac9ac8b5e3124457c2edf16eb6.jpg', 'A', 2),
(3, 'vendedor', 'suswYnqJMbgBF/RHo2ZuYA==', 'Vendedor', 'Vendedor', NULL, '12345678901', 'vendedor@gmail.com', 12345678, 'user_default.png', 'A', 3),
(7, 'Hitsoka', 'diIM9sU/SDXHo8CGwqqiuQ==', 'Carlos', 'Garcia', NULL, '98122121500', 'adsdfg@gmail.com', 51004754, 'user_default.png', 'A', 1),
(8, 'chrnos', 'lvsRzZc/3YtWzsUBxDqCJw==', 'Alibey', 'Gonzalez', NULL, '91092828500', 'alibeygm91@gmail.com', 56239933, '68d821331e697-avatar5.png', 'A', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_municipio`
--

CREATE TABLE `usuario_municipio` (
  `id` int(11) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `estado` varchar(10) NOT NULL DEFAULT 'A',
  `id_municipio` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `usuario_municipio`
--

INSERT INTO `usuario_municipio` (`id`, `direccion`, `estado`, `id_municipio`, `id_usuario`) VALUES
(1, 'Concordia', 'A', 36, 1),
(2, 'asd', 'I', 38, 1),
(3, 'sdfghh', 'I', 65, 1),
(4, 'Neptune Ave 616', 'I', 2, 1),
(5, 'prueba 1', 'I', 24, 1),
(6, 'Neptune Ave 616', 'I', 13, 1),
(7, 'Neptune Ave 616', 'I', 1, 1),
(8, 'we', 'I', 1, 1),
(9, 'asd', 'I', 35, 1),
(10, 'Neptune Ave 616', 'I', 1, 1),
(11, 'San Miguel 426 / Lealtad y Campanario', 'A', 36, 8),
(12, 'San Jose', 'A', 50, 2);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `caracteristica`
--
ALTER TABLE `caracteristica`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `carrito_items`
--
ALTER TABLE `carrito_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_carrito` (`id_carrito`,`id_producto_tienda`),
  ADD KEY `id_producto_tienda` (`id_producto_tienda`);

--
-- Indices de la tabla `carrrito`
--
ALTER TABLE `carrrito`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `configuracion_usuario`
--
ALTER TABLE `configuracion_usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`) USING BTREE;

--
-- Indices de la tabla `envio_detalles`
--
ALTER TABLE `envio_detalles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_orden` (`id_orden`);

--
-- Indices de la tabla `favorito`
--
ALTER TABLE `favorito`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`,`id_producto_tienda`),
  ADD KEY `id_producto_tienda` (`id_producto_tienda`);

--
-- Indices de la tabla `historial`
--
ALTER TABLE `historial`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_tipo_historial` (`id_tipo_historial`,`id_modulo`,`id_usuario`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_modulo` (`id_modulo`);

--
-- Indices de la tabla `imagen`
--
ALTER TABLE `imagen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `marca`
--
ALTER TABLE `marca`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `metodo_pago`
--
ALTER TABLE `metodo_pago`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `modulo`
--
ALTER TABLE `modulo`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `municipio`
--
ALTER TABLE `municipio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_provincia` (`id_provincia`);

--
-- Indices de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `orden`
--
ALTER TABLE `orden`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_orden` (`numero_orden`),
  ADD KEY `id_usuario` (`id_usuario`,`id_metodo_pago`),
  ADD KEY `id_metodo_pago` (`id_metodo_pago`);

--
-- Indices de la tabla `orden_detalle`
--
ALTER TABLE `orden_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_orden` (`id_orden`,`id_producto_tienda`),
  ADD KEY `id_producto_tienda` (`id_producto_tienda`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_marca` (`id_marca`,`id_subcategoria`,`id_unidad_medida`),
  ADD KEY `id_subcategoria` (`id_subcategoria`),
  ADD KEY `id_unidad_medida` (`id_unidad_medida`);

--
-- Indices de la tabla `producto_tienda`
--
ALTER TABLE `producto_tienda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_producto` (`id_producto`,`id_tienda`),
  ADD KEY `id_tienda` (`id_tienda`);

--
-- Indices de la tabla `provincia`
--
ALTER TABLE `provincia`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `reseña`
--
ALTER TABLE `reseña`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_producto_tienda` (`id_producto_tienda`,`id_usuario`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `subcategoria`
--
ALTER TABLE `subcategoria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Indices de la tabla `tienda`
--
ALTER TABLE `tienda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`,`id_municipio`),
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
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `transaccion_pago`
--
ALTER TABLE `transaccion_pago`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `referencia` (`referencia`),
  ADD KEY `id_orden` (`id_orden`);

--
-- Indices de la tabla `unidad_medida`
--
ALTER TABLE `unidad_medida`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_tipo` (`id_tipo`);

--
-- Indices de la tabla `usuario_municipio`
--
ALTER TABLE `usuario_municipio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_municipio` (`id_municipio`,`id_usuario`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `caracteristica`
--
ALTER TABLE `caracteristica`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `carrito_items`
--
ALTER TABLE `carrito_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `carrrito`
--
ALTER TABLE `carrrito`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `configuracion_usuario`
--
ALTER TABLE `configuracion_usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `envio_detalles`
--
ALTER TABLE `envio_detalles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `favorito`
--
ALTER TABLE `favorito`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `historial`
--
ALTER TABLE `historial`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT de la tabla `imagen`
--
ALTER TABLE `imagen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `marca`
--
ALTER TABLE `marca`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `metodo_pago`
--
ALTER TABLE `metodo_pago`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `modulo`
--
ALTER TABLE `modulo`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `orden_detalle`
--
ALTER TABLE `orden_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `producto_tienda`
--
ALTER TABLE `producto_tienda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `provincia`
--
ALTER TABLE `provincia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `reseña`
--
ALTER TABLE `reseña`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `subcategoria`
--
ALTER TABLE `subcategoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT de la tabla `tienda`
--
ALTER TABLE `tienda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tipo_historial`
--
ALTER TABLE `tipo_historial`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `tipo_usuario`
--
ALTER TABLE `tipo_usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `transaccion_pago`
--
ALTER TABLE `transaccion_pago`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `unidad_medida`
--
ALTER TABLE `unidad_medida`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `usuario_municipio`
--
ALTER TABLE `usuario_municipio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `caracteristica`
--
ALTER TABLE `caracteristica`
  ADD CONSTRAINT `caracteristica_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id`);

--
-- Filtros para la tabla `carrito_items`
--
ALTER TABLE `carrito_items`
  ADD CONSTRAINT `carrito_items_ibfk_1` FOREIGN KEY (`id_producto_tienda`) REFERENCES `producto_tienda` (`id`),
  ADD CONSTRAINT `carrito_items_ibfk_2` FOREIGN KEY (`id_carrito`) REFERENCES `carrito` (`id`);

--
-- Filtros para la tabla `carrrito`
--
ALTER TABLE `carrrito`
  ADD CONSTRAINT `carrrito_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`);

--
-- Filtros para la tabla `configuracion_usuario`
--
ALTER TABLE `configuracion_usuario`
  ADD CONSTRAINT `configuracion_usuario_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`);

--
-- Filtros para la tabla `envio_detalles`
--
ALTER TABLE `envio_detalles`
  ADD CONSTRAINT `envio_detalles_ibfk_1` FOREIGN KEY (`id`) REFERENCES `carrito` (`id_carrito_envio`),
  ADD CONSTRAINT `envio_detalles_ibfk_2` FOREIGN KEY (`id_orden`) REFERENCES `orden` (`id`);

--
-- Filtros para la tabla `favorito`
--
ALTER TABLE `favorito`
  ADD CONSTRAINT `favorito_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`),
  ADD CONSTRAINT `favorito_ibfk_2` FOREIGN KEY (`id_producto_tienda`) REFERENCES `producto_tienda` (`id`);

--
-- Filtros para la tabla `historial`
--
ALTER TABLE `historial`
  ADD CONSTRAINT `historial_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`),
  ADD CONSTRAINT `historial_ibfk_2` FOREIGN KEY (`id_tipo_historial`) REFERENCES `tipo_historial` (`id`),
  ADD CONSTRAINT `historial_ibfk_3` FOREIGN KEY (`id_modulo`) REFERENCES `modulo` (`id`);

--
-- Filtros para la tabla `imagen`
--
ALTER TABLE `imagen`
  ADD CONSTRAINT `imagen_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id`);

--
-- Filtros para la tabla `metodo_pago`
--
ALTER TABLE `metodo_pago`
  ADD CONSTRAINT `metodo_pago_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`);

--
-- Filtros para la tabla `municipio`
--
ALTER TABLE `municipio`
  ADD CONSTRAINT `municipio_ibfk_1` FOREIGN KEY (`id_provincia`) REFERENCES `provincia` (`id`);

--
-- Filtros para la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD CONSTRAINT `notificacion_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`);

--
-- Filtros para la tabla `orden`
--
ALTER TABLE `orden`
  ADD CONSTRAINT `orden_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`),
  ADD CONSTRAINT `orden_ibfk_2` FOREIGN KEY (`id_metodo_pago`) REFERENCES `metodo_pago` (`id`);

--
-- Filtros para la tabla `orden_detalle`
--
ALTER TABLE `orden_detalle`
  ADD CONSTRAINT `orden_detalle_ibfk_1` FOREIGN KEY (`id_orden`) REFERENCES `orden` (`id`),
  ADD CONSTRAINT `orden_detalle_ibfk_2` FOREIGN KEY (`id_producto_tienda`) REFERENCES `producto_tienda` (`id`);

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `producto_ibfk_1` FOREIGN KEY (`id_subcategoria`) REFERENCES `subcategoria` (`id`),
  ADD CONSTRAINT `producto_ibfk_2` FOREIGN KEY (`id_unidad_medida`) REFERENCES `unidad_medida` (`id`),
  ADD CONSTRAINT `producto_ibfk_3` FOREIGN KEY (`id_marca`) REFERENCES `marca` (`id`);

--
-- Filtros para la tabla `producto_tienda`
--
ALTER TABLE `producto_tienda`
  ADD CONSTRAINT `producto_tienda_ibfk_1` FOREIGN KEY (`id_tienda`) REFERENCES `tienda` (`id`),
  ADD CONSTRAINT `producto_tienda_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id`);

--
-- Filtros para la tabla `reseña`
--
ALTER TABLE `reseña`
  ADD CONSTRAINT `reseña_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`),
  ADD CONSTRAINT `reseña_ibfk_2` FOREIGN KEY (`id_producto_tienda`) REFERENCES `producto_tienda` (`id`);

--
-- Filtros para la tabla `subcategoria`
--
ALTER TABLE `subcategoria`
  ADD CONSTRAINT `subcategoria_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id`);

--
-- Filtros para la tabla `tienda`
--
ALTER TABLE `tienda`
  ADD CONSTRAINT `tienda_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`),
  ADD CONSTRAINT `tienda_ibfk_2` FOREIGN KEY (`id_municipio`) REFERENCES `municipio` (`id`);

--
-- Filtros para la tabla `transaccion_pago`
--
ALTER TABLE `transaccion_pago`
  ADD CONSTRAINT `transaccion_pago_ibfk_1` FOREIGN KEY (`id_orden`) REFERENCES `orden` (`id`);

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`id_tipo`) REFERENCES `tipo_usuario` (`id`);

--
-- Filtros para la tabla `usuario_municipio`
--
ALTER TABLE `usuario_municipio`
  ADD CONSTRAINT `usuario_municipio_ibfk_1` FOREIGN KEY (`id_municipio`) REFERENCES `municipio` (`id`),
  ADD CONSTRAINT `usuario_municipio_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
