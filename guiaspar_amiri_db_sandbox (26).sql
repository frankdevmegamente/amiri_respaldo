-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 19-05-2026 a las 17:47:09
-- Versión del servidor: 10.6.25-MariaDB-cll-lve-log
-- Versión de PHP: 8.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `guiaspar_amiri_db_sandbox`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria`
--

CREATE TABLE `auditoria` (
  `id` bigint(20) NOT NULL,
  `usuario_id` int(11) NOT NULL COMMENT 'Quién hizo la acción',
  `accion` varchar(50) NOT NULL COMMENT 'INSERT, UPDATE, DELETE, LOGIN, LOGOUT, ANULACION, etc.',
  `modulo` varchar(50) NOT NULL COMMENT 'clientes, gastos, usuarios, movimientos, etc.',
  `tabla` varchar(50) DEFAULT NULL COMMENT 'Tabla afectada',
  `registro_id` int(11) DEFAULT NULL COMMENT 'ID del registro afectado',
  `datos_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Valores antes del cambio' CHECK (json_valid(`datos_anteriores`)),
  `datos_nuevos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Valores después del cambio' CHECK (json_valid(`datos_nuevos`)),
  `descripcion` text DEFAULT NULL COMMENT 'Descripción legible de la acción',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP desde donde se hizo',
  `user_agent` varchar(255) DEFAULT NULL COMMENT 'Navegador/dispositivo',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `auditoria`
--

INSERT INTO `auditoria` (`id`, `usuario_id`, `accion`, `modulo`, `tabla`, `registro_id`, `datos_anteriores`, `datos_nuevos`, `descripcion`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-20 16:34:50'),
(2, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-20 16:36:33'),
(3, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-20 19:45:07'),
(4, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-21 20:56:47'),
(5, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-21 21:49:49'),
(6, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-21 23:08:00'),
(7, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-22 00:50:50'),
(8, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-22 18:30:30'),
(9, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-22 18:30:39'),
(10, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-22 18:33:44'),
(11, 2, 'INSERT', 'clientes', 'clientes', 13, NULL, '{\"nombre\":\"Rodrigo Zermeño\",\"contacto\":\"3221389861\",\"promotor_id\":1,\"retencion_total\":1.5,\"porcentaje_amiri\":1.5,\"porcentaje_promotor\":0,\"opera_mxn\":1,\"opera_usd\":1,\"opera_usdt\":1,\"id\":\"13\"}', 'Creó cliente: Rodrigo Zermeño', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-22 18:44:17'),
(12, 2, 'INSERT', 'clientes', 'cuentas', 49, NULL, '{\"id\":\"49\",\"nombre\":\"Caja - Rodrigo Zermeño\",\"tipo_moneda\":null}', 'Creó cuenta automática para cliente: Caja - Rodrigo Zermeño', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-22 18:44:17'),
(13, 2, 'INSERT', 'clientes', 'clientes', 14, NULL, '{\"nombre\":\"Publico En General\",\"contacto\":\"33 3333 3333\",\"promotor_id\":1,\"retencion_total\":1,\"porcentaje_amiri\":1,\"porcentaje_promotor\":0,\"opera_mxn\":1,\"opera_usd\":1,\"opera_usdt\":1,\"id\":\"14\"}', 'Creó cliente: Publico En General', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-22 18:44:45'),
(14, 2, 'INSERT', 'clientes', 'cuentas', 50, NULL, '{\"id\":\"50\",\"nombre\":\"Caja - Publico En General\",\"tipo_moneda\":null}', 'Creó cuenta automática para cliente: Caja - Publico En General', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-22 18:44:45'),
(15, 2, 'INSERT', 'prestamos', 'deudas_empresa', 1, NULL, '{\"id\":\"1\",\"prestamista_id\":\"4\",\"prestamista_nombre\":\"Hyperion\",\"cuenta_id\":\"2\",\"monto\":511500,\"fecha\":\"2026-04-22 12:46:37\",\"descripcion\":\"\"}', 'Registró préstamo: $511,500.00 de Hyperion', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-22 18:46:35'),
(16, 2, 'INSERT', 'cuentas', 'cuentas', 51, NULL, '{\"id\":\"51\",\"nombre\":\"Binance Emiliano\",\"numero_cuenta\":null,\"banco\":null,\"tipo_cuenta\":2,\"tipo_moneda\":\"USDT\",\"activo\":1,\"es_caja_empresa\":1}', 'Creó cuenta: Binance Emiliano (USDT)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-22 18:47:40'),
(17, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":14487.370000000000800355337560176849365234375,\"tipo_cambio\":17.14999999999999857891452847979962825775146484375,\"cliente_id\":null,\"proveedor_id\":8}', 'Compra de USDT: 14487.37 USDT (TC: 17.15)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-22 18:48:30'),
(18, 2, 'UPDATE', 'prestamos', 'deudas_empresa', 1, '{\"monto_pagado\":\"0.00\",\"estado\":\"pendiente\"}', '{\"monto_pagado\":34823.1500000000014551915228366851806640625,\"estado\":\"parcial\",\"pago_realizado\":34823.1500000000014551915228366851806640625,\"fecha_pago\":\"2026-04-22 12:56:55\"}', 'Registró pago de $34,823.15 al préstamo de Hyperion (Estado: parcial)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-22 18:56:53'),
(19, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1776884329363,\"movimientos\":4}', '{\"nuevo_grupo\":\"1776884329364\",\"reversos\":[\"9\",\"10\",\"11\",\"12\"]}', 'Anuló grupo de movimientos #1776884329363 (Reversión del último movimiento). 4 movimientos anulados.', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-22 18:59:40'),
(20, 2, 'INSERT', 'prestamos', 'deudas_empresa', 2, NULL, '{\"id\":\"2\",\"prestamista_id\":\"4\",\"prestamista_nombre\":\"Hyperion\",\"cuenta_id\":\"32\",\"monto\":511500,\"fecha\":\"2026-04-22 13:20:46\",\"descripcion\":\"\"}', 'Registró préstamo: $511,500.00 de Hyperion', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-22 19:20:44'),
(21, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1776885798719,\"movimientos\":1}', '{\"nuevo_grupo\":\"1776885798720\",\"reversos\":[\"25\"]}', 'Anuló grupo de movimientos #1776885798719 (Reversión del último movimiento). 1 movimientos anulados.', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-22 19:23:31'),
(22, 2, 'UPDATE', 'prestamos', 'deudas_empresa', 1, '{\"monto_pagado\":\"34823.15\",\"estado\":\"parcial\"}', '{\"monto_pagado\":483323.150000000023283064365386962890625,\"estado\":\"parcial\",\"pago_realizado\":448500,\"fecha_pago\":\"2026-04-22 13:32:12\"}', 'Registró pago de $448,500.00 al préstamo de Hyperion (Estado: parcial)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-22 19:32:10'),
(23, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-22 19:40:42'),
(24, 2, 'INSERT', 'cuentas', 'cuentas', 52, NULL, '{\"id\":\"52\",\"nombre\":\"Rojas Retiros\",\"numero_cuenta\":\"123456789012345678\",\"banco\":\"BANORTE\",\"tipo_cuenta\":1,\"tipo_moneda\":\"MXN\",\"activo\":1,\"es_caja_empresa\":1}', 'Creó cuenta: Rojas Retiros (MXN)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-22 19:42:50'),
(25, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', '2026-04-22 19:48:55'),
(26, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', '2026-04-22 20:49:46'),
(27, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-22 20:52:53'),
(28, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-22 23:29:49'),
(29, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '177.248.38.51', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', '2026-04-22 23:50:49'),
(30, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-22 23:54:17'),
(31, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-22 23:56:58'),
(32, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '192.141.247.49', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', '2026-04-23 16:13:29'),
(33, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-23 16:38:34'),
(34, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":20000,\"tipo_cambio\":17,\"cliente_id\":null,\"proveedor_id\":2}', 'Compra de USDT: 20000 USDT (TC: 17)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-23 17:01:12'),
(35, 2, 'DELETE', 'flujo_divisas', 'movimientos', 2147483647, NULL, NULL, 'Eliminación de movimientos grupo #1776963672250', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-23 17:01:26'),
(36, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":20000,\"tipo_cambio\":17.199799999999999755573298898525536060333251953125,\"cliente_id\":null,\"proveedor_id\":2}', 'Compra de USDT: 20000 USDT (TC: 17.1998)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-23 17:01:47'),
(37, 2, 'DELETE', 'flujo_divisas', 'movimientos', 2147483647, NULL, NULL, 'Eliminación de movimientos grupo #1776963707541', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-23 17:01:57'),
(38, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":20000,\"tipo_cambio\":17.199999999999999289457264239899814128875732421875,\"cliente_id\":null,\"proveedor_id\":2}', 'Compra de USDT: 20000 USDT (TC: 17.2)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-23 17:02:25'),
(39, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '79.127.147.92', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-23 17:12:56'),
(40, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-23 17:56:56'),
(41, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-23 22:57:19'),
(42, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-23 23:41:53'),
(43, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":7942.850000000000363797880709171295166015625,\"tipo_cambio\":17.5,\"cliente_id\":10,\"proveedor_id\":null}', 'Venta de USDT: 7942.85 USDT (TC: 17.5)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-23 23:45:41'),
(44, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":34614.7900000000008731149137020111083984375,\"tipo_cambio\":17.461999999999999744204615126363933086395263671875,\"cliente_id\":null,\"proveedor_id\":8}', 'Compra de USDT: 34614.79 USDT (TC: 17.462)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-23 23:47:09'),
(45, 2, 'INSERT', 'prestamos', 'deudas_empresa', 3, NULL, '{\"id\":\"3\",\"prestamista_id\":\"4\",\"prestamista_nombre\":\"Hyperion\",\"cuenta_id\":\"32\",\"monto\":500000,\"fecha\":\"2026-04-23 17:53:28\",\"descripcion\":\"\"}', 'Registró préstamo: $500,000.00 de Hyperion', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-23 23:53:28'),
(46, 2, 'SOFT_DELETE', 'clientes', 'clientes', 9, '{\"id\":\"9\",\"nombre\":\"Maestros Joyeros\",\"promotor_id\":\"1\",\"cuenta_id\":\"30\",\"retencion_total\":\"0.00\",\"porcentaje_amiri\":\"0.00\",\"porcentaje_promotor\":\"0.00\",\"opera_mxn\":\"1\",\"opera_usd\":\"1\",\"opera_usdt\":\"1\",\"contacto\":\"maestrosjoyeros@gmail.com\",\"activo\":\"1\",\"creado_en\":\"2026-03-26 18:08:17\",\"eliminado_en\":null,\"eliminado_por\":null,\"promotor\":\"AMIRI DIRECTO\",\"cuenta_nombre\":\"Caja - Maestros Joyeros\",\"saldo_actual\":\"0.00\",\"tipo_moneda\":\"MXN\"}', NULL, 'Desactivó cliente: Maestros Joyeros', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-23 23:54:54'),
(47, 2, 'SOFT_DELETE', 'clientes', 'cuentas', 30, '{\"id\":\"30\",\"nombre\":\"Caja - Maestros Joyeros\",\"numero_cuenta\":null,\"banco\":null,\"tipo_cuenta\":\"4\",\"tipo_moneda\":\"MXN\",\"saldo_actual\":\"0.00\",\"valor_mxn\":\"0.00\",\"es_caja_empresa\":\"0\",\"documento_path\":null,\"activo\":\"1\",\"creado_en\":\"2026-03-26 18:08:17\",\"eliminado_en\":null,\"eliminado_por\":null}', NULL, 'Desactivó cuenta asociada a cliente: Caja - Maestros Joyeros', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-23 23:54:54'),
(48, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '79.127.147.92', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-24 00:01:51'),
(49, 1, 'INSERT', 'clientes', 'clientes', 83, NULL, '{\"nombre\":\"Frank Prueba\",\"contacto\":\"prueba@correo.com\",\"promotor_id\":8,\"retencion_total\":2,\"porcentaje_amiri\":1.75,\"porcentaje_promotor\":0.25,\"opera_mxn\":1,\"opera_usd\":1,\"opera_usdt\":1,\"id\":\"83\"}', 'Creó cliente: Frank Prueba', '79.127.147.92', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-24 00:08:16'),
(50, 1, 'INSERT', 'clientes', 'cuentas', 122, NULL, '{\"id\":\"122\",\"nombre\":\"Caja - Frank Prueba\",\"tipo_moneda\":\"MXN\"}', 'Creó cuenta automática para cliente: Caja - Frank Prueba', '79.127.147.92', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-24 00:08:16'),
(51, 1, 'SOFT_DELETE', 'clientes', 'clientes', 83, '{\"id\":\"83\",\"nombre\":\"Frank Prueba\",\"promotor_id\":\"8\",\"cuenta_id\":\"122\",\"retencion_total\":\"2.00\",\"porcentaje_amiri\":\"1.75\",\"porcentaje_promotor\":\"0.25\",\"opera_mxn\":\"1\",\"opera_usd\":\"1\",\"opera_usdt\":\"1\",\"contacto\":\"prueba@correo.com\",\"activo\":\"1\",\"creado_en\":\"2026-04-23 18:08:16\",\"eliminado_en\":null,\"eliminado_por\":null,\"promotor\":\"Ana Maria - Maestros Joyeros\",\"cuenta_nombre\":\"Caja - Frank Prueba\",\"saldo_actual\":\"0.00\",\"tipo_moneda\":\"MXN\"}', NULL, 'Desactivó cliente: Frank Prueba', '79.127.147.92', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-24 00:08:23'),
(52, 1, 'SOFT_DELETE', 'clientes', 'cuentas', 122, '{\"id\":\"122\",\"nombre\":\"Caja - Frank Prueba\",\"numero_cuenta\":null,\"banco\":null,\"tipo_cuenta\":\"4\",\"tipo_moneda\":\"MXN\",\"saldo_actual\":\"0.00\",\"valor_mxn\":\"0.00\",\"es_caja_empresa\":\"0\",\"documento_path\":null,\"activo\":\"1\",\"creado_en\":\"2026-04-23 18:08:16\",\"eliminado_en\":null,\"eliminado_por\":null}', NULL, 'Desactivó cuenta asociada a cliente: Caja - Frank Prueba', '79.127.147.92', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-24 00:08:23'),
(53, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '79.127.147.92', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-24 00:09:55'),
(54, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-24 00:10:02'),
(55, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-24 00:11:08'),
(56, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '79.127.147.92', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-24 00:12:45'),
(57, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-24 00:13:23'),
(58, 1, 'INSERT', 'clientes', 'clientes', 131, NULL, '{\"nombre\":\"Maestros Joyeros\",\"contacto\":\"maestrosjoyeros@gmail.com\",\"promotor_id\":8,\"retencion_total\":1.75,\"porcentaje_amiri\":1.5,\"porcentaje_promotor\":0.25,\"opera_mxn\":1,\"opera_usd\":1,\"opera_usdt\":1,\"id\":\"131\"}', 'Creó cliente: Maestros Joyeros', '79.127.147.92', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-24 00:34:03'),
(59, 1, 'INSERT', 'clientes', 'cuentas', 170, NULL, '{\"id\":\"170\",\"nombre\":\"Caja - Maestros Joyeros\",\"tipo_moneda\":\"MXN\"}', 'Creó cuenta automática para cliente: Caja - Maestros Joyeros', '79.127.147.92', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-24 00:34:03'),
(60, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '177.248.38.51', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', '2026-04-24 05:37:43'),
(61, 2, 'INSERT', 'clientes', 'clientes', 133, NULL, '{\"nombre\":\"Maestros Joyeros -\",\"contacto\":\"maestrosjoyeros@gmail.com\",\"promotor_id\":8,\"retencion_total\":1.75,\"porcentaje_amiri\":1.5,\"porcentaje_promotor\":0.25,\"opera_mxn\":1,\"opera_usd\":1,\"opera_usdt\":1,\"id\":\"133\"}', 'Creó cliente: Maestros Joyeros -', '177.248.38.51', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', '2026-04-24 05:39:11'),
(62, 2, 'INSERT', 'clientes', 'cuentas', 172, NULL, '{\"id\":\"172\",\"nombre\":\"Caja - Maestros Joyeros -\",\"tipo_moneda\":\"MXN\"}', 'Creó cuenta automática para cliente: Caja - Maestros Joyeros -', '177.248.38.51', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', '2026-04-24 05:39:11'),
(63, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":61159.3099999999976716935634613037109375,\"tipo_cambio\":17.671099999999999141664375201798975467681884765625,\"cliente_id\":133,\"proveedor_id\":null}', 'Venta de USDT: 61159.31 USDT (TC: 17.6711)', '177.248.38.51', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', '2026-04-24 05:41:03'),
(64, 2, 'UPDATE', 'prestamos', 'deudas_empresa', 2, '{\"monto_pagado\":\"0.00\",\"estado\":\"pendiente\"}', '{\"monto_pagado\":511500,\"estado\":\"pagado\",\"pago_realizado\":511500,\"fecha_pago\":\"2026-04-23 23:42:08\"}', 'Registró pago de $511,500.00 al préstamo de Hyperion (Estado: pagado)', '177.248.38.51', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', '2026-04-24 05:42:08'),
(65, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1777009328548,\"movimientos\":1}', '{\"nuevo_grupo\":\"1777009328549\",\"reversos\":[\"62\"]}', 'Anuló grupo de movimientos #1777009328548 (Reversión del último movimiento). 1 movimientos anulados.', '177.248.38.51', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', '2026-04-24 05:42:25'),
(66, 2, 'UPDATE', 'prestamos', 'deudas_empresa', 2, '{\"monto_pagado\":\"0.00\",\"estado\":\"pendiente\"}', '{\"monto_pagado\":511500,\"estado\":\"pagado\",\"pago_realizado\":511500,\"fecha_pago\":\"2026-04-23 23:42:50\"}', 'Registró pago de $511,500.00 al préstamo de Hyperion (Estado: pagado)', '177.248.38.51', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', '2026-04-24 05:42:50'),
(67, 2, 'UPDATE', 'prestamos', 'deudas_empresa', 1, '{\"monto_pagado\":\"483323.15\",\"estado\":\"parcial\"}', '{\"monto_pagado\":511500,\"estado\":\"pagado\",\"pago_realizado\":28176.8499999999985448084771633148193359375,\"fecha_pago\":\"2026-04-22 23:43:02\"}', 'Registró pago de $28,176.85 al préstamo de Hyperion (Estado: pagado)', '177.248.38.51', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', '2026-04-24 05:43:02'),
(68, 2, 'UPDATE', 'prestamos', 'deudas_empresa', 3, '{\"monto_pagado\":\"0.00\",\"estado\":\"pendiente\"}', '{\"monto_pagado\":500000,\"estado\":\"pagado\",\"pago_realizado\":500000,\"fecha_pago\":\"2026-04-23 23:43:14\"}', 'Registró pago de $500,000.00 al préstamo de Hyperion (Estado: pagado)', '177.248.38.51', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', '2026-04-24 05:43:14'),
(69, 2, 'INSERT', 'gastos', 'gastos', 1, NULL, '{\"id\":\"1\",\"fecha\":\"2026-04-23\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"17\",\"clasificacion\":\"variable\",\"monto\":\"1570.00\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-04-23 23:44:36\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Tóner Impresora\"}', 'Registró gasto: $1,570.00 - Tóner Impresora', '177.248.38.51', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', '2026-04-24 05:44:36'),
(70, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.163.86.192', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-24 15:27:54'),
(71, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '177.248.38.51', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', '2026-04-24 15:28:06'),
(72, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.163.86.192', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-24 16:07:25'),
(73, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '189.163.86.192', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-24 16:09:31'),
(74, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-24 16:34:09'),
(75, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1777009476784,\"movimientos\":2}', '{\"nuevo_grupo\":\"1777009476785\",\"reversos\":[\"68\",\"69\"]}', 'Anuló grupo de movimientos #1777009476784 (Reversión del último movimiento). 2 movimientos anulados.', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-24 16:39:22'),
(76, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1777009394191,\"movimientos\":1}', '{\"nuevo_grupo\":\"1777009476786\",\"reversos\":[\"70\"]}', 'Anuló grupo de movimientos #1777009394191 (Reversión del último movimiento). 1 movimientos anulados.', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-24 16:39:26'),
(77, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1777009382428,\"movimientos\":1}', '{\"nuevo_grupo\":\"1777009476787\",\"reversos\":[\"71\"]}', 'Anuló grupo de movimientos #1777009382428 (Reversión del último movimiento). 1 movimientos anulados.', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-24 16:39:30'),
(78, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1777009370208,\"movimientos\":1}', '{\"nuevo_grupo\":\"1777009476788\",\"reversos\":[\"72\"]}', 'Anuló grupo de movimientos #1777009370208 (Reversión del último movimiento). 1 movimientos anulados.', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-24 16:39:32'),
(79, 2, 'DELETE', 'flujo_divisas', 'movimientos', 2147483647, NULL, NULL, 'Eliminación de movimientos grupo #1777009263075', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-24 16:39:37'),
(80, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1777048945011,\"movimientos\":1}', '{\"nuevo_grupo\":\"1777048945012\",\"reversos\":[\"74\"]}', 'Anuló grupo de movimientos #1777048945011 (Reversión del último movimiento). 1 movimientos anulados.', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-24 16:42:45'),
(81, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1776988430308,\"movimientos\":1}', '{\"nuevo_grupo\":\"1777048945013\",\"reversos\":[\"75\"]}', 'Anuló grupo de movimientos #1776988430308 (Reversión del último movimiento). 1 movimientos anulados.', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-24 16:42:48'),
(82, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1776988408642,\"movimientos\":1}', '{\"nuevo_grupo\":\"1777048945014\",\"reversos\":[\"76\"]}', 'Anuló grupo de movimientos #1776988408642 (Reversión del último movimiento). 1 movimientos anulados.', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-24 16:42:56'),
(83, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1776988334587,\"movimientos\":1}', '{\"nuevo_grupo\":\"1777048945015\",\"reversos\":[\"77\"]}', 'Anuló grupo de movimientos #1776988334587 (Reversión del último movimiento). 1 movimientos anulados.', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-24 16:42:58'),
(84, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1776988304286,\"movimientos\":1}', '{\"nuevo_grupo\":\"1777048945016\",\"reversos\":[\"78\"]}', 'Anuló grupo de movimientos #1776988304286 (Reversión del último movimiento). 1 movimientos anulados.', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-24 16:43:03'),
(85, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1776988159039,\"movimientos\":4}', '{\"nuevo_grupo\":\"1777048945017\",\"reversos\":[\"79\",\"80\",\"81\",\"82\"]}', 'Anuló grupo de movimientos #1776988159039 (Reversión del último movimiento). 4 movimientos anulados.', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-24 16:43:20'),
(86, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.163.86.192', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-24 16:45:25'),
(87, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.163.86.192', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-25 00:06:10'),
(88, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '189.163.86.192', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-25 00:24:52'),
(89, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-27 16:19:45'),
(90, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 16:57:19'),
(91, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.163.86.192', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 16:57:38'),
(92, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 17:34:11'),
(93, 2, 'INSERT', 'prestamos', 'deudas_empresa', 4, NULL, '{\"id\":\"4\",\"prestamista_id\":\"4\",\"prestamista_nombre\":\"Hyperion\",\"cuenta_id\":\"34\",\"monto\":361000,\"fecha\":\"2026-04-23 11:35:06\",\"descripcion\":\"\"}', 'Registró préstamo: $361,000.00 de Hyperion', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 17:35:05'),
(94, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":61159.3099999999976716935634613037109375,\"tipo_cambio\":17.671099999999999141664375201798975467681884765625,\"cliente_id\":133,\"proveedor_id\":null}', 'Venta de USDT: 61159.31 USDT (TC: 17.6711)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 17:38:37'),
(95, 2, 'UPDATE', 'prestamos', 'deudas_empresa', 4, '{\"monto_pagado\":\"0.00\",\"estado\":\"pendiente\"}', '{\"monto_pagado\":361000,\"estado\":\"pagado\",\"pago_realizado\":361000,\"fecha_pago\":\"2026-04-23 11:39:47\"}', 'Registró pago (múltiple) de $361,000.00 al préstamo de Hyperion (Estado: pagado)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 17:39:46'),
(96, 2, 'UPDATE', 'prestamos', 'deudas_empresa', 1, '{\"monto_pagado\":\"483323.15\",\"estado\":\"parcial\"}', '{\"monto_pagado\":511500,\"estado\":\"pagado\",\"pago_realizado\":28176.849999999976716935634613037109375,\"fecha_pago\":\"2026-04-23 11:39:47\"}', 'Registró pago (múltiple) de $28,176.85 al préstamo de Hyperion (Estado: pagado)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 17:39:46'),
(97, 2, 'UPDATE', 'prestamos', 'deudas_empresa', 2, '{\"monto_pagado\":\"0.00\",\"estado\":\"pendiente\"}', '{\"monto_pagado\":511500,\"estado\":\"pagado\",\"pago_realizado\":511500,\"fecha_pago\":\"2026-04-23 11:39:47\"}', 'Registró pago (múltiple) de $511,500.00 al préstamo de Hyperion (Estado: pagado)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 17:39:46'),
(98, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":126,\"tipo_cambio\":17.199999999999999289457264239899814128875732421875,\"cliente_id\":null,\"proveedor_id\":2}', 'Compra de USDT: 126 USDT (TC: 17.2)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 17:43:33'),
(99, 2, 'INSERT', 'gastos', 'gastos', 2, NULL, '{\"id\":\"2\",\"fecha\":\"2026-04-20\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"2\",\"clasificacion\":\"fijo\",\"monto\":\"800.00\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-04-28 11:45:26\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Gas Kia\"}', 'Registró gasto: $800.00 - Gas Kia', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 17:45:26'),
(100, 2, 'INSERT', 'gastos', 'gastos', 3, NULL, '{\"id\":\"3\",\"fecha\":\"2026-04-22\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"11\",\"clasificacion\":\"variable\",\"monto\":\"1100.00\",\"concepto\":\"Totatlplay Eduardo\",\"activo\":\"1\",\"creado_en\":\"2026-04-28 11:46:32\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Cuentas Pro Amiri\"}', 'Registró gasto: $1,100.00 - Cuentas Pro Amiri', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 17:46:32'),
(101, 2, 'INSERT', 'gastos', 'gastos', 4, NULL, '{\"id\":\"4\",\"fecha\":\"2026-04-28\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"13\",\"clasificacion\":\"variable\",\"monto\":\"1570.00\",\"concepto\":\"Toner Impresora\",\"activo\":\"1\",\"creado_en\":\"2026-04-28 11:47:24\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Papeleria\"}', 'Registró gasto: $1,570.00 - Papeleria', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 17:47:24'),
(102, 2, 'INSERT', 'gastos', 'gastos', 5, NULL, '{\"id\":\"5\",\"fecha\":\"2026-04-28\",\"cuenta_id\":\"36\",\"movimiento_id\":null,\"categoria_id\":\"18\",\"clasificacion\":\"variable\",\"monto\":\"3500.00\",\"concepto\":\"Citlaly Azul\",\"activo\":\"1\",\"creado_en\":\"2026-04-28 11:48:58\",\"cuenta_nombre\":\"Rosa Huerta\",\"tipo_gasto_nombre\":\"Azul\"}', 'Registró gasto: $3,500.00 - Azul', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 17:48:58'),
(103, 2, 'INSERT', 'gastos', 'gastos', 6, NULL, '{\"id\":\"6\",\"fecha\":\"2026-04-28\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"19\",\"clasificacion\":\"variable\",\"monto\":\"15400.00\",\"concepto\":\"Poderes Empresa\",\"activo\":\"1\",\"creado_en\":\"2026-04-28 11:51:58\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Amiri Operaciones Integrales\"}', 'Registró gasto: $15,400.00 - Amiri Operaciones Integrales', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 17:51:58'),
(104, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1777398718277,\"movimientos\":2}', '{\"nuevo_grupo\":\"1777398718278\",\"reversos\":[\"114\",\"115\"]}', 'Anuló grupo de movimientos #1777398718277 (Reversión del último movimiento). 2 movimientos anulados.', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 17:52:13'),
(105, 2, 'INSERT', 'gastos', 'gastos', 7, NULL, '{\"id\":\"7\",\"fecha\":\"2026-04-24\",\"cuenta_id\":\"36\",\"movimiento_id\":null,\"categoria_id\":\"19\",\"clasificacion\":\"variable\",\"monto\":\"15400.00\",\"concepto\":\"Poderes Empresa\",\"activo\":\"1\",\"creado_en\":\"2026-04-28 11:52:43\",\"cuenta_nombre\":\"Rosa Huerta\",\"tipo_gasto_nombre\":\"Amiri Operaciones Integrales\"}', 'Registró gasto: $15,400.00 - Amiri Operaciones Integrales', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 17:52:43'),
(106, 2, 'INSERT', 'gastos', 'gastos', 8, NULL, '{\"id\":\"8\",\"fecha\":\"2026-04-28\",\"cuenta_id\":\"52\",\"movimiento_id\":null,\"categoria_id\":\"19\",\"clasificacion\":\"variable\",\"monto\":\"3000.00\",\"concepto\":\"Vuelta Eduardo Socio Amiri\",\"activo\":\"1\",\"creado_en\":\"2026-04-28 11:53:39\",\"cuenta_nombre\":\"Rojas Retiros\",\"tipo_gasto_nombre\":\"Amiri Operaciones Integrales\"}', 'Registró gasto: $3,000.00 - Amiri Operaciones Integrales', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 17:53:40'),
(107, 2, 'INSERT', 'clientes', 'clientes', 134, NULL, '{\"nombre\":\"Juan Pablo - Picañas\",\"contacto\":\"N\\/A\",\"promotor_id\":1,\"retencion_total\":2,\"porcentaje_amiri\":2,\"porcentaje_promotor\":0,\"opera_mxn\":1,\"opera_usd\":0,\"opera_usdt\":0,\"id\":\"134\"}', 'Creó cliente: Juan Pablo - Picañas', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 17:55:57'),
(108, 2, 'INSERT', 'clientes', 'cuentas', 173, NULL, '{\"id\":\"173\",\"nombre\":\"Caja - Juan Pablo - Picañas\",\"tipo_moneda\":\"MXN\"}', 'Creó cuenta automática para cliente: Caja - Juan Pablo - Picañas', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 17:55:57'),
(109, 2, 'INSERT', 'prestamos', 'deudas_empresa', 5, NULL, '{\"id\":\"5\",\"prestamista_id\":\"3\",\"prestamista_nombre\":\"Diego Alfredo H\",\"cuenta_id\":\"37\",\"monto\":200000,\"fecha\":\"2026-04-28 12:01:59\",\"descripcion\":\"Los Tome De Mantela Para Amiri\"}', 'Registró préstamo: $200,000.00 de Diego Alfredo H', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 18:01:58'),
(110, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":11369.989999999999781721271574497222900390625,\"tipo_cambio\":17.199999999999999289457264239899814128875732421875,\"cliente_id\":null,\"proveedor_id\":2}', 'Compra de USDT: 11369.99 USDT (TC: 17.2)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 18:03:06'),
(111, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":5329.989999999999781721271574497222900390625,\"tipo_cambio\":17.470099999999998630073605454526841640472412109375,\"cliente_id\":null,\"proveedor_id\":8}', 'Compra de USDT: 5329.99 USDT (TC: 17.4701)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 18:04:41'),
(112, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1777399828123,\"movimientos\":1}', '{\"nuevo_grupo\":\"1777399828124\",\"reversos\":[\"139\"]}', 'Anuló grupo de movimientos #1777399828123 (Reversión del último movimiento). 1 movimientos anulados.', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 18:11:10'),
(113, 2, 'INSERT', 'clientes', 'clientes', 135, NULL, '{\"nombre\":\"Maximos Joyeros\",\"contacto\":\"N\\/A\",\"promotor_id\":7,\"retencion_total\":3.5,\"porcentaje_amiri\":1,\"porcentaje_promotor\":2.5,\"opera_mxn\":1,\"opera_usd\":1,\"opera_usdt\":1,\"id\":\"135\"}', 'Creó cliente: Maximos Joyeros', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 18:28:57'),
(114, 2, 'INSERT', 'clientes', 'cuentas', 174, NULL, '{\"id\":\"174\",\"nombre\":\"Caja - Maximos Joyeros\",\"tipo_moneda\":\"MXN\"}', 'Creó cuenta automática para cliente: Caja - Maximos Joyeros', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 18:28:57'),
(115, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":16825.97999999999956344254314899444580078125,\"tipo_cambio\":17.5681000000000011596057447604835033416748046875,\"cliente_id\":135,\"proveedor_id\":null}', 'Venta de USDT: 16825.98 USDT (TC: 17.5681)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 18:30:04'),
(116, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 00:08:26'),
(117, 2, 'INSERT', 'prestamos', 'deudas_empresa', 6, NULL, '{\"id\":\"6\",\"prestamista_id\":\"4\",\"prestamista_nombre\":\"Hyperion\",\"cuenta_id\":\"34\",\"monto\":300000,\"fecha\":\"2026-04-28 18:09:31\",\"descripcion\":\"\"}', 'Registró préstamo: $300,000.00 de Hyperion', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 00:09:31'),
(118, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":700,\"tipo_cambio\":17.199999999999999289457264239899814128875732421875,\"cliente_id\":null,\"proveedor_id\":2}', 'Compra de USDT: 700 USDT (TC: 17.2)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 00:29:30'),
(119, 2, 'INSERT', 'gastos', 'gastos', 9, NULL, '{\"id\":\"9\",\"fecha\":\"2026-04-28\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"19\",\"clasificacion\":\"variable\",\"monto\":\"1700.00\",\"concepto\":\"Entrega Poderes\",\"activo\":\"1\",\"creado_en\":\"2026-04-28 18:30:42\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Amiri Operaciones Integrales\"}', 'Registró gasto: $1,700.00 - Amiri Operaciones Integrales', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 00:30:42'),
(120, 2, 'INSERT', 'gastos', 'gastos', 10, NULL, '{\"id\":\"10\",\"fecha\":\"2026-04-29\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"2\",\"clasificacion\":\"fijo\",\"monto\":\"500.00\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-04-28 18:37:17\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Gas Kia\"}', 'Registró gasto: $500.00 - Gas Kia', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 00:37:17'),
(121, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1777423037952,\"movimientos\":2}', '{\"nuevo_grupo\":\"1777423037953\",\"reversos\":[\"161\",\"162\"]}', 'Anuló grupo de movimientos #1777423037952 (Reversión del último movimiento). 2 movimientos anulados.', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 00:37:49'),
(122, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1777422889860,\"movimientos\":4}', '{\"nuevo_grupo\":\"1777423037954\",\"reversos\":[\"163\",\"164\",\"165\",\"166\"]}', 'Anuló grupo de movimientos #1777422889860 (Reversión del último movimiento). 4 movimientos anulados.', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 00:37:53'),
(123, 2, 'INSERT', 'gastos', 'gastos', 11, NULL, '{\"id\":\"11\",\"fecha\":\"2026-04-29\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"2\",\"clasificacion\":\"fijo\",\"monto\":\"500.00\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-04-28 18:39:41\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Gas Kia\"}', 'Registró gasto: $500.00 - Gas Kia', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 00:39:41'),
(124, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 17:44:54'),
(125, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.163.86.192', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 17:49:46'),
(126, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '189.163.86.192', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 17:51:58'),
(127, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1777484903428,\"movimientos\":1}', '{\"nuevo_grupo\":\"1777484903429\",\"reversos\":[\"177\"]}', 'Anuló grupo de movimientos #1777484903428 (Reversión del último movimiento). 1 movimientos anulados.', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 17:52:06'),
(128, 2, 'INSERT', 'clientes', 'clientes', 136, NULL, '{\"nombre\":\"Osvaldo Vazquez - Joyero\",\"contacto\":\"osvaldovazquez@gmail.com\",\"promotor_id\":9,\"retencion_total\":1.5,\"porcentaje_amiri\":1,\"porcentaje_promotor\":0.5,\"opera_mxn\":1,\"opera_usd\":1,\"opera_usdt\":1,\"id\":\"136\"}', 'Creó cliente: Osvaldo Vazquez - Joyero', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 17:55:48');
INSERT INTO `auditoria` (`id`, `usuario_id`, `accion`, `modulo`, `tabla`, `registro_id`, `datos_anteriores`, `datos_nuevos`, `descripcion`, `ip_address`, `user_agent`, `created_at`) VALUES
(129, 2, 'INSERT', 'clientes', 'cuentas', 176, NULL, '{\"id\":\"176\",\"nombre\":\"Caja - Osvaldo Vazquez - Joyero\",\"tipo_moneda\":\"MXN\"}', 'Creó cuenta automática para cliente: Caja - Osvaldo Vazquez - Joyero', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 17:55:48'),
(130, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1777485638778,\"movimientos\":1}', '{\"nuevo_grupo\":\"1777485638779\",\"reversos\":[\"179\"]}', 'Anuló grupo de movimientos #1777485638778 (Reversión del último movimiento). 1 movimientos anulados.', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 18:00:52'),
(131, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.163.86.192', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 18:11:33'),
(132, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '189.163.86.192', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 18:15:01'),
(133, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.163.86.192', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 18:22:08'),
(134, 2, 'UPDATE', 'prestamos', 'deudas_empresa', 5, '{\"monto_pagado\":\"0.00\",\"estado\":\"pendiente\"}', '{\"monto_pagado\":200000,\"estado\":\"pagado\",\"pago_realizado\":200000,\"fecha_pago\":\"2026-04-29 12:38:23\"}', 'Registró pago (múltiple) de $200,000.00 al préstamo de Diego Alfredo H (Estado: pagado)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 18:38:23'),
(135, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1777487903480,\"movimientos\":1}', '{\"nuevo_grupo\":\"1777487903481\",\"reversos\":[\"187\"]}', 'Anuló grupo de movimientos #1777487903480 (Reversión del último movimiento). 1 movimientos anulados.', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 18:38:33'),
(136, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '189.163.86.192', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 18:48:30'),
(137, 1, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":\"1777490429763\",\"movimientos\":1}', '{\"nuevo_grupo\":1777490429764,\"reversos\":[\"189\"]}', 'Anuló grupo de movimientos #1777490429763 (Reversión del último movimiento). 1 movimientos anulados.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 19:20:52'),
(138, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 20:33:55'),
(139, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":\"1777490429763\",\"movimientos\":1}', '{\"nuevo_grupo\":\"1777490429764\",\"reversos\":[\"190\"]}', 'Anuló grupo de movimientos #1777490429763 (Reversión del último movimiento). 1 movimientos anulados.', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 20:36:34'),
(140, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":13880.6800000000002910383045673370361328125,\"tipo_cambio\":17.612100000000001642774805077351629734039306640625,\"cliente_id\":null,\"proveedor_id\":8}', 'Compra de USDT: 13880.68 USDT (TC: 17.6121)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 21:16:54'),
(141, 2, 'DELETE', 'flujo_divisas', 'movimientos', 2147483647, NULL, NULL, 'Eliminación de movimientos grupo #1777497414520', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 21:16:58'),
(142, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":13880.6800000000002910383045673370361328125,\"tipo_cambio\":17.612100000000001642774805077351629734039306640625,\"cliente_id\":null,\"proveedor_id\":8}', 'Compra de USDT: 13880.68 USDT (TC: 17.6121)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 21:17:43'),
(143, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":14580.6800000000002910383045673370361328125,\"tipo_cambio\":17.7426999999999992496668710373342037200927734375,\"cliente_id\":10,\"proveedor_id\":null}', 'Venta de USDT: 14580.68 USDT (TC: 17.7427)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 21:18:44'),
(144, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 00:07:01'),
(145, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 16:41:49'),
(146, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.163.86.192', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 17:20:36'),
(147, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '189.163.86.192', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 17:20:57'),
(148, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 19:01:59'),
(149, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 19:07:31'),
(150, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.163.86.192', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 19:08:02'),
(151, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":\"1777576221374\",\"movimientos\":1}', '{\"nuevo_grupo\":\"1777576221375\",\"reversos\":[\"221\"]}', 'Anuló grupo de movimientos #1777576221374 (Reversión del último movimiento). 1 movimientos anulados.', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 19:10:42'),
(152, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 20:09:04'),
(153, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 20:28:38'),
(154, 2, 'INSERT', 'gastos', 'gastos', 12, NULL, '{\"id\":\"12\",\"fecha\":\"2026-04-30\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"2\",\"clasificacion\":\"fijo\",\"monto\":\"500.00\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-04-30 14:32:27\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Gas Kia\"}', 'Registró gasto: $500.00 - Gas Kia', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 20:32:27'),
(155, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 21:32:08'),
(156, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":50827.139999999999417923390865325927734375,\"tipo_cambio\":17.5741000000000013869794202037155628204345703125,\"cliente_id\":null,\"proveedor_id\":8}', 'Compra de USDT: 50827.14 USDT (TC: 17.5741)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 21:32:56'),
(157, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":30334.3199999999997089616954326629638671875,\"tipo_cambio\":17.82679999999999864712663111276924610137939453125,\"cliente_id\":133,\"proveedor_id\":null}', 'Venta de USDT: 30334.32 USDT (TC: 17.8268)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 21:40:50'),
(158, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.163.86.192', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 22:18:00'),
(159, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.163.86.192', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 23:06:28'),
(160, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '189.163.86.192', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 23:09:41'),
(161, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.163.86.192', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 23:11:39'),
(162, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 23:23:09'),
(163, 2, 'INSERT', 'gastos', 'gastos', 13, NULL, '{\"id\":\"13\",\"fecha\":\"2026-04-30\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"18\",\"clasificacion\":\"variable\",\"monto\":\"5000.00\",\"concepto\":\"Mandados A Citlaly Azul\",\"activo\":\"1\",\"creado_en\":\"2026-04-30 17:25:32\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Azul\"}', 'Registró gasto: $5,000.00 - Azul', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 23:25:32'),
(164, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":\"1777591532200\",\"movimientos\":2}', '{\"nuevo_grupo\":\"1777591532201\",\"reversos\":[\"241\",\"242\"]}', 'Anuló grupo de movimientos #1777591532200 (Reversión del último movimiento). 2 movimientos anulados.', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 23:25:44'),
(165, 2, 'INSERT', 'gastos', 'gastos', 14, NULL, '{\"id\":\"14\",\"fecha\":\"2026-04-30\",\"cuenta_id\":\"34\",\"movimiento_id\":null,\"categoria_id\":\"18\",\"clasificacion\":\"variable\",\"monto\":\"5000.00\",\"concepto\":\"Mandados A Azul Citlaly\",\"activo\":\"1\",\"creado_en\":\"2026-04-30 17:26:25\",\"cuenta_nombre\":\"Tarpen Sa De Cv\",\"tipo_gasto_nombre\":\"Azul\"}', 'Registró gasto: $5,000.00 - Azul', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 23:26:25'),
(166, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-01 00:28:57'),
(167, 2, 'INSERT', 'gastos', 'gastos', 15, NULL, '{\"id\":\"15\",\"fecha\":\"2026-05-01\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"20\",\"clasificacion\":\"fijo\",\"monto\":\"20000.00\",\"concepto\":\"Emiliano\",\"activo\":\"1\",\"creado_en\":\"2026-04-30 18:38:37\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Nomina\"}', 'Registró gasto: $20,000.00 - Nomina', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-01 00:38:37'),
(168, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":5395,\"tipo_cambio\":17.5741000000000013869794202037155628204345703125,\"cliente_id\":14,\"proveedor_id\":null}', 'Venta de USDT: 5395 USDT (TC: 17.5741)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-01 00:42:50'),
(169, 2, 'DELETE', 'flujo_divisas', 'movimientos', 2147483647, NULL, NULL, 'Eliminación de movimientos grupo #1777596170648', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-01 00:43:44'),
(170, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":\"1777595917972\",\"movimientos\":2}', '{\"nuevo_grupo\":\"1777596170649\",\"reversos\":[\"255\",\"256\"]}', 'Anuló grupo de movimientos #1777595917972 (Reversión del último movimiento). 2 movimientos anulados.', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-01 00:43:53'),
(171, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":5395,\"tipo_cambio\":17.574000000000001620037437533028423786163330078125,\"cliente_id\":14,\"proveedor_id\":null}', 'Venta de USDT: 5395 USDT (TC: 17.574)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-01 00:44:59'),
(172, 2, 'INSERT', 'gastos', 'gastos', 16, NULL, '{\"id\":\"16\",\"fecha\":\"2026-04-30\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"21\",\"clasificacion\":\"fijo\",\"monto\":\"94811.73\",\"concepto\":\"Diego\",\"activo\":\"1\",\"creado_en\":\"2026-04-30 18:45:35\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Retiro Utilidades\"}', 'Registró gasto: $94,811.73 - Retiro Utilidades', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-01 00:45:35'),
(173, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.163.86.192', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-01 00:51:45'),
(174, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-01 00:53:17'),
(175, 2, 'INSERT', 'gastos', 'gastos', 17, NULL, '{\"id\":\"17\",\"fecha\":\"2026-04-30\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"20\",\"clasificacion\":\"fijo\",\"monto\":\"20000.00\",\"concepto\":\"Emiliano\",\"activo\":\"1\",\"creado_en\":\"2026-04-30 18:55:05\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Nomina\"}', 'Registró gasto: $20,000.00 - Nomina', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-01 00:55:05'),
(176, 3, 'LOGIN', 'auth', 'usuarios', 3, NULL, NULL, 'Usuario \'Diego\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-01 01:58:56'),
(177, 3, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":2105,\"tipo_cambio\":17.574000000000001620037437533028423786163330078125,\"cliente_id\":14,\"proveedor_id\":null}', 'Venta de USDT: 2105 USDT (TC: 17.574)', '189.203.100.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-01 02:01:46'),
(178, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '177.248.38.51', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', '2026-05-04 15:37:43'),
(179, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '177.248.38.51', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', '2026-05-04 15:40:55'),
(180, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 16:34:37'),
(181, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":3725.1300000000001091393642127513885498046875,\"tipo_cambio\":17.725699999999999789679350215010344982147216796875,\"cliente_id\":133,\"proveedor_id\":null}', 'Venta de USDT: 3725.13 USDT (TC: 17.7257)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 16:39:04'),
(182, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 16:51:39'),
(183, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 16:53:16'),
(184, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 18:18:29'),
(185, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', '2026-05-04 18:54:39'),
(186, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 21:34:06'),
(187, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 21:44:16'),
(188, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 22:00:54'),
(189, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 22:47:57'),
(190, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 23:02:33'),
(191, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 23:12:50'),
(192, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 23:49:34'),
(193, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 23:53:16'),
(194, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-05 00:35:26'),
(195, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-05 16:25:50'),
(196, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":\"1777998501918\",\"movimientos\":1}', '{\"nuevo_grupo\":\"1777998501919\",\"reversos\":[\"287\"]}', 'Anuló grupo de movimientos #1777998501918 (Reversión del último movimiento). 1 movimientos anulados.', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-05 16:28:30'),
(197, 2, 'INSERT', 'gastos', 'gastos', 18, NULL, '{\"id\":\"18\",\"fecha\":\"2026-05-05\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"2\",\"clasificacion\":\"fijo\",\"monto\":\"500.00\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-05-05 10:51:50\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Gas Kia\"}', 'Registró gasto: $500.00 - Gas Kia', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-05 16:51:50'),
(198, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-05 19:32:11'),
(199, 2, 'INSERT', 'gastos', 'gastos', 19, NULL, '{\"id\":\"19\",\"fecha\":\"2026-05-05\",\"cuenta_id\":\"36\",\"movimiento_id\":null,\"categoria_id\":\"3\",\"clasificacion\":\"fijo\",\"monto\":\"16000.00\",\"concepto\":\"Renta Mayo\",\"activo\":\"1\",\"creado_en\":\"2026-05-05 13:32:47\",\"cuenta_nombre\":\"Rosa Huerta\",\"tipo_gasto_nombre\":\"Renta\"}', 'Registró gasto: $16,000.00 - Renta', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-05 19:32:47'),
(200, 2, 'INSERT', 'gastos', 'gastos', 20, NULL, '{\"id\":\"20\",\"fecha\":\"2026-05-05\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"19\",\"clasificacion\":\"variable\",\"monto\":\"1600.00\",\"concepto\":\"Juan Pablo Poderes\",\"activo\":\"1\",\"creado_en\":\"2026-05-05 13:56:58\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Amiri Operaciones Integrales\"}', 'Registró gasto: $1,600.00 - Amiri Operaciones Integrales', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-05 19:56:58'),
(201, 2, 'INSERT', 'gastos', 'gastos', 21, NULL, '{\"id\":\"21\",\"fecha\":\"2026-05-05\",\"cuenta_id\":\"36\",\"movimiento_id\":null,\"categoria_id\":\"4\",\"clasificacion\":\"fijo\",\"monto\":\"3600.00\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-05-05 13:57:52\",\"cuenta_nombre\":\"Rosa Huerta\",\"tipo_gasto_nombre\":\"Recargas\"}', 'Registró gasto: $3,600.00 - Recargas', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-05 19:57:52'),
(202, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-05 20:30:18'),
(203, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-05 21:20:52'),
(204, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-05 22:02:53'),
(205, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-06 00:27:12'),
(206, 2, 'INSERT', 'gastos', 'gastos', 22, NULL, '{\"id\":\"22\",\"fecha\":\"2026-05-05\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"7\",\"clasificacion\":\"fijo\",\"monto\":\"5435.00\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-05-05 18:44:16\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Comisiones Rojas\"}', 'Registró gasto: $5,435.00 - Comisiones Rojas', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-06 00:44:16'),
(207, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":\"1778028333578\",\"movimientos\":1}', '{\"nuevo_grupo\":\"1778028333579\",\"reversos\":[\"323\"]}', 'Anuló grupo de movimientos #1778028333578 (Reversión del último movimiento). 1 movimientos anulados.', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-06 00:47:55'),
(208, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-06 01:04:53'),
(209, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-06 15:24:03'),
(210, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-06 16:46:19'),
(211, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-06 16:49:26'),
(212, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-06 17:48:54'),
(213, 2, 'INSERT', 'gastos', 'gastos', 23, NULL, '{\"id\":\"23\",\"fecha\":\"2026-05-06\",\"cuenta_id\":\"34\",\"movimiento_id\":null,\"categoria_id\":\"7\",\"clasificacion\":\"fijo\",\"monto\":\"5435.00\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-05-06 11:49:54\",\"cuenta_nombre\":\"Tarpen Sa De Cv\",\"tipo_gasto_nombre\":\"Comisiones Rojas\"}', 'Registró gasto: $5,435.00 - Comisiones Rojas', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-06 17:49:54'),
(214, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-06 18:37:13'),
(215, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":2594.65000000000009094947017729282379150390625,\"tipo_cambio\":17.360499999999998266275724745355546474456787109375,\"cliente_id\":null,\"proveedor_id\":8}', 'Compra de USDT: 2594.65 USDT (TC: 17.3605)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-06 18:37:53'),
(216, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":11857.139999999999417923390865325927734375,\"tipo_cambio\":17.5,\"cliente_id\":10,\"proveedor_id\":null}', 'Venta de USDT: 11857.14 USDT (TC: 17.5)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-06 18:41:18'),
(217, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-06 18:49:28'),
(218, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-06 20:59:37'),
(219, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-06 21:29:47'),
(220, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":165000,\"tipo_cambio\":17.14999999999999857891452847979962825775146484375,\"cliente_id\":null,\"proveedor_id\":8}', 'Compra de USDT: 165000 USDT (TC: 17.15)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-06 21:30:20'),
(221, 2, 'DELETE', 'flujo_divisas', 'movimientos', 2147483647, NULL, NULL, 'Eliminación de movimientos grupo #1778103020307', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-06 21:30:25'),
(222, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":165000,\"tipo_cambio\":17.14999999999999857891452847979962825775146484375,\"cliente_id\":null,\"proveedor_id\":3}', 'Compra de USDT: 165000 USDT (TC: 17.15)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-06 21:30:45'),
(223, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":\"1778103084465\",\"movimientos\":1}', '{\"nuevo_grupo\":\"1778103084466\",\"reversos\":[\"351\"]}', 'Anuló grupo de movimientos #1778103084465 (Reversión del último movimiento). 1 movimientos anulados.', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-06 21:31:39'),
(224, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":\"1778103192646\",\"movimientos\":1}', '{\"nuevo_grupo\":\"1778103192647\",\"reversos\":[\"354\"]}', 'Anuló grupo de movimientos #1778103192646 (Reversión del último movimiento). 1 movimientos anulados.', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-06 21:33:42'),
(225, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":2425.260000000000218278728425502777099609375,\"tipo_cambio\":17.295500000000000540012479177676141262054443359375,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 2425.26 USDT (TC: 17.2955)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-06 21:59:00'),
(226, 2, 'DELETE', 'flujo_divisas', 'movimientos', 2147483647, NULL, NULL, 'Eliminación de movimientos grupo #1778104740919', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-06 22:00:46'),
(227, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":2425.260000000000218278728425502777099609375,\"tipo_cambio\":17.295500000000000540012479177676141262054443359375,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 2425.26 USDT (TC: 17.2955)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-06 22:02:09'),
(228, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-06 22:03:56'),
(229, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":3265.5399999999999636202119290828704833984375,\"tipo_cambio\":17.295500000000000540012479177676141262054443359375,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 3265.54 USDT (TC: 17.2955)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-06 22:04:49'),
(230, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":3469.09999999999990905052982270717620849609375,\"tipo_cambio\":17.29560000000000030695446184836328029632568359375,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 3469.1 USDT (TC: 17.2956)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-06 22:27:30'),
(231, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-06 22:58:39'),
(232, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '89.18.205.221', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-07 00:09:22'),
(233, 2, 'INSERT', 'clientes', 'clientes', 137, NULL, '{\"nombre\":\"Raul\",\"contacto\":\"-\",\"promotor_id\":6,\"retencion_total\":1.5,\"porcentaje_amiri\":1,\"porcentaje_promotor\":0.5,\"opera_mxn\":1,\"opera_usd\":1,\"opera_usdt\":1,\"id\":\"137\"}', 'Creó cliente: Raul', '89.18.205.221', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-07 00:14:37'),
(234, 2, 'INSERT', 'clientes', 'cuentas', 177, NULL, '{\"id\":\"177\",\"nombre\":\"Caja - Raul\",\"tipo_moneda\":\"MXN\"}', 'Creó cuenta automática para cliente: Caja - Raul', '89.18.205.221', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-07 00:14:37'),
(235, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":15490.370000000000800355337560176849365234375,\"tipo_cambio\":17.305499999999998550492819049395620822906494140625,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 15490.37 USDT (TC: 17.3055)', '89.18.205.221', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-07 00:27:50'),
(236, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-07 00:38:54'),
(237, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-07 16:50:40'),
(238, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-07 18:50:53'),
(239, 1, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":\"1778179883261\",\"movimientos\":4}', '{\"nuevo_grupo\":\"1778179883262\",\"reversos\":[\"392\",\"393\",\"394\",\"395\"]}', 'Anuló grupo de movimientos #1778179883261 (Reversión del último movimiento). 4 movimientos anulados.', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-07 18:51:46'),
(240, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-07 20:37:39'),
(241, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":3836.82000000000016370904631912708282470703125,\"tipo_cambio\":17.24080000000000012505552149377763271331787109375,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 3836.82 USDT (TC: 17.2408)', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-07 20:42:43'),
(242, 2, 'INSERT', 'gastos', 'gastos', 24, NULL, '{\"id\":\"24\",\"fecha\":\"2026-05-07\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"22\",\"clasificacion\":\"variable\",\"monto\":\"500.00\",\"concepto\":\"Reconexión\",\"activo\":\"1\",\"creado_en\":\"2026-05-07 14:44:38\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Cfe\"}', 'Registró gasto: $500.00 - Cfe', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-07 20:44:38'),
(243, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-07 20:56:38'),
(244, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-07 21:42:45'),
(245, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-07 22:57:11'),
(246, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-07 23:06:13'),
(247, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-07 23:48:01'),
(248, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":1825.299999999999954525264911353588104248046875,\"tipo_cambio\":17.2409999999999996589394868351519107818603515625,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 1825.3 USDT (TC: 17.241)', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-07 23:53:36'),
(249, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-08 01:00:38'),
(250, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-08 16:48:46'),
(251, 2, 'INSERT', 'proveedores', 'proveedores', 10, NULL, '{\"proveedor_id\":\"10\",\"nombre\":\"Alfonso Santos\",\"email\":\"alfonsosantos@gmail.com\",\"telefono\":\"33 1918 6813\",\"acepta_usd\":1,\"acepta_usdt\":1}', 'Creó proveedor: Alfonso Santos', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-08 16:53:42'),
(252, 2, 'SOFT_DELETE', 'proveedores', 'proveedores', 10, '{\"id\":\"10\",\"nombre\":\"Alfonso Santos\",\"email\":\"alfonsosantos@gmail.com\",\"telefono\":\"33 1918 6813\",\"tipo_moneda\":\"USD\",\"acepta_usd\":\"1\",\"acepta_usdt\":\"1\",\"cuenta_id\":null,\"cuenta_usd_id\":\"178\",\"cuenta_usdt_id\":\"179\",\"activo\":\"1\",\"creado_en\":\"2026-05-08 10:53:42\",\"eliminado_en\":null,\"eliminado_por\":null}', NULL, 'Desactivó proveedor: Alfonso Santos', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-08 16:54:09'),
(253, 2, 'INSERT', 'clientes', 'clientes', 138, NULL, '{\"nombre\":\"Miguel Cisneros\",\"contacto\":\"- SANTOS\",\"promotor_id\":10,\"retencion_total\":1.1999999999999999555910790149937383830547332763671875,\"porcentaje_amiri\":1,\"porcentaje_promotor\":0.200000000000000011102230246251565404236316680908203125,\"opera_mxn\":1,\"opera_usd\":1,\"opera_usdt\":1,\"id\":\"138\"}', 'Creó cliente: Miguel Cisneros', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-08 16:57:31'),
(254, 2, 'INSERT', 'clientes', 'cuentas', 181, NULL, '{\"id\":\"181\",\"nombre\":\"Caja - Miguel Cisneros\",\"tipo_moneda\":\"MXN\"}', 'Creó cuenta automática para cliente: Caja - Miguel Cisneros', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-08 16:57:31'),
(255, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":4125.1800000000002910383045673370361328125,\"tipo_cambio\":17.30539999999999878355083637870848178863525390625,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 4125.18 USDT (TC: 17.3054)', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-08 17:07:04'),
(256, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '200.68.167.48', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '2026-05-08 19:04:59'),
(257, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', '2026-05-08 20:45:41'),
(258, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-08 21:24:34'),
(259, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-08 23:17:59'),
(260, 2, 'INSERT', 'gastos', 'gastos', 25, NULL, '{\"id\":\"25\",\"fecha\":\"2026-05-08\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"2\",\"clasificacion\":\"fijo\",\"monto\":\"500.00\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-05-08 17:20:51\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Gas Kia\"}', 'Registró gasto: $500.00 - Gas Kia', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-08 23:20:51'),
(261, 2, 'INSERT', 'gastos', 'gastos', 26, NULL, '{\"id\":\"26\",\"fecha\":\"2026-05-08\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"23\",\"clasificacion\":\"fijo\",\"monto\":\"3800.00\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-05-08 17:24:39\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Operativo\"}', 'Registró gasto: $3,800.00 - Operativo', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-08 23:24:39'),
(262, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-09 00:05:27'),
(263, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-09 00:32:03'),
(264, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '200.68.167.40', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '2026-05-09 01:14:51'),
(265, 1, 'UPDATE', 'clientes', 'clientes', 138, '{\"id\":\"138\",\"nombre\":\"Miguel Cisneros\",\"promotor_id\":\"10\",\"cuenta_id\":\"181\",\"retencion_total\":\"1.20\",\"porcentaje_amiri\":\"1.00\",\"porcentaje_promotor\":\"0.20\",\"opera_mxn\":\"1\",\"opera_usd\":\"1\",\"opera_usdt\":\"1\",\"contacto\":\"- SANTOS\",\"activo\":\"1\",\"creado_en\":\"2026-05-08 10:57:31\",\"eliminado_en\":null,\"eliminado_por\":null}', '{\"nombre\":\"Miguel Cisneros\",\"contacto\":\"- SANTOS\",\"promotor_id\":10,\"retencion_total\":1,\"porcentaje_amiri\":0.8000000000000000444089209850062616169452667236328125,\"porcentaje_promotor\":0.200000000000000011102230246251565404236316680908203125,\"opera_mxn\":1,\"opera_usd\":1,\"opera_usdt\":1}', 'Actualizó cliente: Miguel Cisneros', '200.68.167.40', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '2026-05-09 01:15:30'),
(266, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-09 01:47:16'),
(267, 2, 'INSERT', 'gastos', 'gastos', 27, NULL, '{\"id\":\"27\",\"fecha\":\"2026-05-08\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"7\",\"clasificacion\":\"fijo\",\"monto\":\"1880.00\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-05-08 19:48:30\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Comisiones Rojas\"}', 'Registró gasto: $1,880.00 - Comisiones Rojas', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-09 01:48:30'),
(268, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":\"1778291310532\",\"movimientos\":1}', '{\"nuevo_grupo\":\"1778291310533\",\"reversos\":[\"432\"]}', 'Anuló grupo de movimientos #1778291310532 (Reversión del último movimiento). 1 movimientos anulados.', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-09 01:48:43');
INSERT INTO `auditoria` (`id`, `usuario_id`, `accion`, `modulo`, `tabla`, `registro_id`, `datos_anteriores`, `datos_nuevos`, `descripcion`, `ip_address`, `user_agent`, `created_at`) VALUES
(269, 2, 'INSERT', 'gastos', 'gastos', 28, NULL, '{\"id\":\"28\",\"fecha\":\"2026-05-08\",\"cuenta_id\":\"34\",\"movimiento_id\":null,\"categoria_id\":\"7\",\"clasificacion\":\"fijo\",\"monto\":\"1880.00\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-05-08 19:49:05\",\"cuenta_nombre\":\"Tarpen Sa De Cv\",\"tipo_gasto_nombre\":\"Comisiones Rojas\"}', 'Registró gasto: $1,880.00 - Comisiones Rojas', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-09 01:49:05'),
(270, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":13359.25,\"tipo_cambio\":17.251000000000001222133505507372319698333740234375,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 13359.25 USDT (TC: 17.251)', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-09 01:55:55'),
(271, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-09 01:58:20'),
(272, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-11 17:53:33'),
(273, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":\"1778523303388\",\"movimientos\":1}', '{\"nuevo_grupo\":\"1778523303389\",\"reversos\":[\"444\"]}', 'Anuló grupo de movimientos #1778523303388 (Reversión del último movimiento). 1 movimientos anulados.', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-11 18:21:27'),
(274, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.163.86.192', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-11 19:19:19'),
(275, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-11 19:28:34'),
(276, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.163.86.192', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-11 20:49:52'),
(277, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-11 21:23:25'),
(278, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":9485.84000000000014551915228366851806640625,\"tipo_cambio\":17.063700000000000756017470848746597766876220703125,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 9485.84 USDT (TC: 17.0637)', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-11 22:13:05'),
(279, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-11 23:04:17'),
(280, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.144.107.64', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 02:07:19'),
(281, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 16:47:00'),
(282, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 16:47:38'),
(283, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":1000,\"tipo_cambio\":17.10000000000000142108547152020037174224853515625,\"cliente_id\":null,\"proveedor_id\":2}', 'Compra de USDT: 1000 USDT (TC: 17.1)', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 16:48:32'),
(284, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 18:02:59'),
(285, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":14341.329999999999927240423858165740966796875,\"tipo_cambio\":17.277899999999998925659383530728518962860107421875,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 14341.33 USDT (TC: 17.2779)', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 18:05:46'),
(286, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 18:09:01'),
(287, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 19:44:14'),
(288, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":285,\"tipo_cambio\":17.10000000000000142108547152020037174224853515625,\"cliente_id\":null,\"proveedor_id\":2}', 'Compra de USDT: 285 USDT (TC: 17.1)', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 19:52:34'),
(289, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 19:57:18'),
(290, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 20:01:59'),
(291, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":3770,\"tipo_cambio\":17.259899999999998243538357201032340526580810546875,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 3770 USDT (TC: 17.2599)', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 20:04:03'),
(292, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 20:31:52'),
(293, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 21:27:05'),
(294, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 22:10:27'),
(295, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-13 00:04:24'),
(296, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-13 01:01:55'),
(297, 2, 'INSERT', 'clientes', 'clientes', 139, NULL, '{\"nombre\":\"Wb - Gas\",\"contacto\":\"NETO\",\"promotor_id\":11,\"retencion_total\":1,\"porcentaje_amiri\":0.6999999999999999555910790149937383830547332763671875,\"porcentaje_promotor\":0.299999999999999988897769753748434595763683319091796875,\"opera_mxn\":1,\"opera_usd\":1,\"opera_usdt\":1,\"id\":\"139\"}', 'Creó cliente: Wb - Gas', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-13 01:04:01'),
(298, 2, 'INSERT', 'clientes', 'cuentas', 183, NULL, '{\"id\":\"183\",\"nombre\":\"Caja - Wb - Gas\",\"tipo_moneda\":\"MXN\"}', 'Creó cuenta automática para cliente: Caja - Wb - Gas', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-13 01:04:01'),
(299, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-13 01:41:52'),
(300, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-13 17:03:06'),
(301, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-13 17:04:37'),
(302, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-13 18:05:25'),
(303, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-13 19:19:02'),
(304, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-13 20:11:17'),
(305, 2, 'INSERT', 'gastos', 'gastos', 29, NULL, '{\"id\":\"29\",\"fecha\":\"2026-05-13\",\"cuenta_id\":\"36\",\"movimiento_id\":null,\"categoria_id\":\"19\",\"clasificacion\":\"variable\",\"monto\":\"1000.00\",\"concepto\":\"Cita\",\"activo\":\"1\",\"creado_en\":\"2026-05-13 14:23:42\",\"cuenta_nombre\":\"Rosa Huerta\",\"tipo_gasto_nombre\":\"Amiri Operaciones Integrales\"}', 'Registró gasto: $1,000.00 - Amiri Operaciones Integrales', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-13 20:23:42'),
(306, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":4345,\"tipo_cambio\":17.10000000000000142108547152020037174224853515625,\"cliente_id\":null,\"proveedor_id\":2}', 'Compra de USDT: 4345 USDT (TC: 17.1)', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-13 20:25:42'),
(307, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-13 20:58:14'),
(308, 2, 'INSERT', 'prestamistas', 'prestamistas', 5, NULL, '{\"id\":\"5\",\"nombre\":\"Alejandro Mora\",\"telefono\":\"33 3576 6064\",\"email\":\"alexmora@gmail.com\",\"direccion\":\"\"}', 'Creó prestamista: Alejandro Mora', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-13 20:59:39'),
(309, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":16925.28729999999995925463736057281494140625,\"tipo_cambio\":17.39999999999999857891452847979962825775146484375,\"cliente_id\":10,\"proveedor_id\":null}', 'Venta de USDT: 16925.2873 USDT (TC: 17.4)', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-13 21:01:56'),
(310, 2, 'DELETE', 'flujo_divisas', 'movimientos', 2147483647, NULL, NULL, 'Eliminación de movimientos grupo #1778706116294', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-13 21:02:28'),
(311, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":16925.28729999999995925463736057281494140625,\"tipo_cambio\":17.39999999999999857891452847979962825775146484375,\"cliente_id\":10,\"proveedor_id\":null}', 'Venta de USDT: 16925.2873 USDT (TC: 17.4)', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-13 21:03:07'),
(312, 2, 'INSERT', 'gastos', 'gastos', 30, NULL, '{\"id\":\"30\",\"fecha\":\"2026-05-13\",\"cuenta_id\":\"34\",\"movimiento_id\":null,\"categoria_id\":\"7\",\"clasificacion\":\"fijo\",\"monto\":\"651.11\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-05-13 15:07:26\",\"cuenta_nombre\":\"Tarpen Sa De Cv\",\"tipo_gasto_nombre\":\"Comisiones Rojas\"}', 'Registró gasto: $651.11 - Comisiones Rojas', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-13 21:07:26'),
(313, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-13 22:19:04'),
(314, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-13 22:29:10'),
(315, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 16:48:32'),
(316, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.163.141.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 16:50:53'),
(317, 2, 'INSERT', 'prestamistas', 'prestamistas', 6, NULL, '{\"id\":\"6\",\"nombre\":\"Yuyu\",\"telefono\":\"-\",\"email\":\"yuyu@gmail.com\",\"direccion\":\"\"}', 'Creó prestamista: Yuyu', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:17:09'),
(318, 2, 'UPDATE', 'prestamistas', 'prestamistas', 6, '{\"id\":\"6\",\"nombre\":\"Yuyu\",\"telefono\":\"-\",\"email\":\"yuyu@gmail.com\",\"direccion\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-05-14 11:17:09\",\"eliminado_en\":null,\"eliminado_por\":null}', '{\"nombre\":\"Yuyu\",\"telefono\":\"33 3149 6323\",\"email\":\"yuyu@gmail.com\",\"direccion\":\"\",\"activo\":\"1\"}', 'Actualizó prestamista: Yuyu', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:17:22'),
(319, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.163.141.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:38:35'),
(320, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 18:59:40'),
(321, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 18:59:40'),
(322, 2, 'INSERT', 'clientes', 'clientes', 140, NULL, '{\"nombre\":\"Emiliano Rivera\",\"contacto\":\"33 3902 8994\",\"promotor_id\":12,\"retencion_total\":1,\"porcentaje_amiri\":0.6999999999999999555910790149937383830547332763671875,\"porcentaje_promotor\":0.299999999999999988897769753748434595763683319091796875,\"opera_mxn\":1,\"opera_usd\":1,\"opera_usdt\":1,\"id\":\"140\"}', 'Creó cliente: Emiliano Rivera', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 19:00:56'),
(323, 2, 'INSERT', 'clientes', 'cuentas', 185, NULL, '{\"id\":\"185\",\"nombre\":\"Caja - Emiliano Rivera\",\"tipo_moneda\":\"MXN\"}', 'Creó cuenta automática para cliente: Caja - Emiliano Rivera', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 19:00:56'),
(324, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":1330.589999999999918145476840436458587646484375,\"tipo_cambio\":17.28556499999999829242369742132723331451416015625,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 1330.59 USDT (TC: 17.285565)', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 19:28:32'),
(325, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 21:16:14'),
(326, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":100757,\"tipo_cambio\":17.199999999999999289457264239899814128875732421875,\"cliente_id\":null,\"proveedor_id\":2}', 'Compra de USDT: 100757 USDT (TC: 17.2)', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 21:38:36'),
(327, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":16573.27000000000043655745685100555419921875,\"tipo_cambio\":17.29007299999999958117768983356654644012451171875,\"cliente_id\":null,\"proveedor_id\":8}', 'Compra de USDT: 16573.27 USDT (TC: 17.290073)', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:22:12'),
(328, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":6000,\"tipo_cambio\":17.29020200000000073714545578695833683013916015625,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 6000 USDT (TC: 17.290202)', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:36:10'),
(329, 2, 'DELETE', 'flujo_divisas', 'movimientos', 2147483647, NULL, NULL, 'Eliminación de movimientos grupo #1778798170570', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:36:20'),
(330, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":6000,\"tipo_cambio\":17.29020200000000073714545578695833683013916015625,\"cliente_id\":null,\"proveedor_id\":8}', 'Compra de USDT: 6000 USDT (TC: 17.290202)', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:36:41'),
(331, 2, 'INSERT', 'prestamistas', 'prestamistas', 7, NULL, '{\"id\":\"7\",\"nombre\":\"Valeria Garcia\",\"telefono\":\"33 3127 2849\",\"email\":\"valgarcia@gmail.com\",\"direccion\":\"\"}', 'Creó prestamista: Valeria Garcia', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 00:37:45'),
(332, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 14:59:59'),
(333, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 15:13:22'),
(334, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 15:44:47'),
(335, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 16:45:52'),
(336, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 16:46:13'),
(337, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":30578.45000000000072759576141834259033203125,\"tipo_cambio\":17.663799999999998391331246239133179187774658203125,\"cliente_id\":133,\"proveedor_id\":null}', 'Venta de USDT: 30578.45 USDT (TC: 17.6638)', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 16:49:44'),
(338, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":29,\"tipo_cambio\":17.241378999999998455905370065011084079742431640625,\"cliente_id\":14,\"proveedor_id\":null}', 'Venta de USDT: 29 USDT (TC: 17.241379)', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 16:58:24'),
(339, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 18:14:06'),
(340, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":20000,\"tipo_cambio\":17.199999999999999289457264239899814128875732421875,\"cliente_id\":null,\"proveedor_id\":3}', 'Compra de USDT: 20000 USDT (TC: 17.2)', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 18:14:23'),
(341, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 18:59:46'),
(342, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":11997,\"tipo_cambio\":16.42830000000000012505552149377763271331787109375,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 11997 USDT (TC: 16.4283)', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 19:02:57'),
(343, 2, 'DELETE', 'flujo_divisas', 'movimientos', 2147483647, NULL, NULL, 'Eliminación de movimientos grupo #1778871777540', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 19:03:01'),
(344, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":11997.809999999999490682967007160186767578125,\"tipo_cambio\":17.385290000000001242597136297263205051422119140625,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 11997.81 USDT (TC: 17.38529)', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 19:04:00'),
(345, 2, 'INSERT', 'gastos', 'gastos', 31, NULL, '{\"id\":\"31\",\"fecha\":\"2026-05-15\",\"cuenta_id\":\"36\",\"movimiento_id\":null,\"categoria_id\":\"4\",\"clasificacion\":\"fijo\",\"monto\":\"30.00\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-05-15 13:24:27\",\"cuenta_nombre\":\"Rosa Huerta\",\"tipo_gasto_nombre\":\"Recargas\"}', 'Registró gasto: $30.00 - Recargas', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 19:24:27'),
(346, 3, 'LOGIN', 'auth', 'usuarios', 3, NULL, NULL, 'Usuario \'Diego\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 21:04:53'),
(347, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 21:05:56'),
(348, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 21:11:43'),
(349, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 22:49:58'),
(350, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 23:58:49'),
(351, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 00:32:58'),
(352, 2, 'INSERT', 'gastos', 'gastos', 32, NULL, '{\"id\":\"32\",\"fecha\":\"2026-05-15\",\"cuenta_id\":\"34\",\"movimiento_id\":null,\"categoria_id\":\"7\",\"clasificacion\":\"fijo\",\"monto\":\"81.32\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-05-15 18:42:22\",\"cuenta_nombre\":\"Tarpen Sa De Cv\",\"tipo_gasto_nombre\":\"Comisiones Rojas\"}', 'Registró gasto: $81.32 - Comisiones Rojas', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 00:42:22'),
(353, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":\"1778892142359\",\"movimientos\":2}', '{\"nuevo_grupo\":\"1778892142360\",\"reversos\":[\"570\",\"571\"]}', 'Anuló grupo de movimientos #1778892142359 (Reversión del último movimiento). 2 movimientos anulados.', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 00:42:29'),
(354, 2, 'INSERT', 'gastos', 'gastos', 33, NULL, '{\"id\":\"33\",\"fecha\":\"2026-05-15\",\"cuenta_id\":\"34\",\"movimiento_id\":null,\"categoria_id\":\"7\",\"clasificacion\":\"fijo\",\"monto\":\"81.20\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-05-15 18:42:43\",\"cuenta_nombre\":\"Tarpen Sa De Cv\",\"tipo_gasto_nombre\":\"Comisiones Rojas\"}', 'Registró gasto: $81.20 - Comisiones Rojas', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 00:42:43'),
(355, 2, 'INSERT', 'gastos', 'gastos', 34, NULL, '{\"id\":\"34\",\"fecha\":\"2026-05-15\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"2\",\"clasificacion\":\"fijo\",\"monto\":\"500.00\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-05-15 18:43:26\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Gas Kia\"}', 'Registró gasto: $500.00 - Gas Kia', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 00:43:26'),
(356, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":8348.97999999999956344254314899444580078125,\"tipo_cambio\":17.38538199999999989131538313813507556915283203125,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 8348.98 USDT (TC: 17.385382)', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 00:51:27'),
(357, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 17:33:09'),
(358, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":13244.100000000000363797880709171295166015625,\"tipo_cambio\":17.4322149999999993497112882323563098907470703125,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 13244.1 USDT (TC: 17.432215)', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 17:45:37'),
(359, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 18:37:12'),
(360, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:16:58'),
(361, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 22:55:20'),
(362, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":12850,\"tipo_cambio\":17.4729999999999989768184605054557323455810546875,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 12850 USDT (TC: 17.473)', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 23:23:08'),
(363, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":1153.0399999999999636202119290828704833984375,\"tipo_cambio\":17.34545200000000164664015755988657474517822265625,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 1153.04 USDT (TC: 17.345452)', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 23:25:39'),
(364, 2, 'INSERT', 'gastos', 'gastos', 35, NULL, '{\"id\":\"35\",\"fecha\":\"2026-05-18\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"6\",\"clasificacion\":\"variable\",\"monto\":\"43057.69\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-05-18 17:46:02\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Comisiones Bancarias\"}', 'Registró gasto: $43,057.69 - Comisiones Bancarias', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 23:46:02'),
(365, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 00:44:33'),
(366, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 16:40:25'),
(367, 2, 'INSERT', 'clientes', 'clientes', 141, NULL, '{\"nombre\":\"Raul Ortiz\",\"contacto\":\"Kike rizo\",\"promotor_id\":6,\"retencion_total\":1.5,\"porcentaje_amiri\":1,\"porcentaje_promotor\":0.5,\"opera_mxn\":1,\"opera_usd\":1,\"opera_usdt\":1,\"id\":\"141\"}', 'Creó cliente: Raul Ortiz', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 17:01:58'),
(368, 2, 'INSERT', 'clientes', 'cuentas', 186, NULL, '{\"id\":\"186\",\"nombre\":\"Caja - Raul Ortiz\",\"tipo_moneda\":\"MXN\"}', 'Creó cuenta automática para cliente: Caja - Raul Ortiz', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 17:01:58'),
(369, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.163.141.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 17:02:08'),
(370, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 18:09:34'),
(371, 2, 'INSERT', 'gastos', 'gastos', 36, NULL, '{\"id\":\"36\",\"fecha\":\"2026-05-19\",\"cuenta_id\":\"34\",\"movimiento_id\":null,\"categoria_id\":\"7\",\"clasificacion\":\"fijo\",\"monto\":\"330.40\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-05-19 12:14:04\",\"cuenta_nombre\":\"Tarpen Sa De Cv\",\"tipo_gasto_nombre\":\"Comisiones Rojas\"}', 'Registró gasto: $330.40 - Comisiones Rojas', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 18:14:04'),
(372, 2, 'INSERT', 'gastos', 'gastos', 37, NULL, '{\"id\":\"37\",\"fecha\":\"2026-05-19\",\"cuenta_id\":\"34\",\"movimiento_id\":null,\"categoria_id\":\"7\",\"clasificacion\":\"fijo\",\"monto\":\"81.20\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-05-19 12:48:29\",\"cuenta_nombre\":\"Tarpen Sa De Cv\",\"tipo_gasto_nombre\":\"Comisiones Rojas\"}', 'Registró gasto: $81.20 - Comisiones Rojas', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 18:48:29'),
(373, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 22:01:27'),
(374, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 23:38:19'),
(375, 2, 'INSERT', 'clientes', 'clientes', 142, NULL, '{\"nombre\":\"Arq\",\"contacto\":\"DOLLAR APP\",\"promotor_id\":1,\"retencion_total\":0,\"porcentaje_amiri\":0,\"porcentaje_promotor\":0,\"opera_mxn\":1,\"opera_usd\":1,\"opera_usdt\":1,\"id\":\"142\"}', 'Creó cliente: Arq', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 23:38:46'),
(376, 2, 'INSERT', 'clientes', 'cuentas', 187, NULL, '{\"id\":\"187\",\"nombre\":\"Caja - Arq\",\"tipo_moneda\":\"MXN\"}', 'Creó cuenta automática para cliente: Caja - Arq', '187.190.202.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 23:38:46'),
(377, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '146.70.230.84', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 23:38:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias_gasto`
--

CREATE TABLE `categorias_gasto` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias_gasto`
--

INSERT INTO `categorias_gasto` (`id`, `nombre`, `activo`, `creado_en`) VALUES
(2, 'Gas Kia', 1, '2026-03-27 23:57:10'),
(3, 'Renta', 1, '2026-03-27 23:57:17'),
(4, 'Recargas', 1, '2026-03-27 23:57:25'),
(5, 'Estacionamiento', 1, '2026-03-27 23:57:33'),
(6, 'Comisiones Bancarias', 1, '2026-03-27 23:57:46'),
(7, 'Comisiones Rojas', 1, '2026-04-02 22:36:20'),
(8, 'Pago Amiri - Diego', 1, '2026-04-14 00:10:35'),
(9, 'Pago Amiri - Emiliano', 1, '2026-04-14 00:10:42'),
(10, 'Pago Alondra', 1, '2026-04-14 00:12:12'),
(11, 'Cuentas Pro Amiri', 1, '2026-04-14 00:17:48'),
(12, 'Peeter', 1, '2026-04-14 00:18:38'),
(13, 'Papeleria', 1, '2026-04-14 00:25:49'),
(14, 'Retiros', 1, '2026-04-14 00:29:44'),
(15, 'Limpieza', 1, '2026-04-14 00:31:25'),
(16, 'Perdida', 1, '2026-04-14 00:37:39'),
(17, 'Tóner Impresora', 1, '2026-04-24 05:44:04'),
(18, 'Azul', 1, '2026-04-28 17:48:15'),
(19, 'Amiri Operaciones Integrales', 1, '2026-04-28 17:51:35'),
(20, 'Nomina', 1, '2026-05-01 00:38:07'),
(21, 'Retiro Utilidades', 1, '2026-05-01 00:38:19'),
(22, 'Cfe', 1, '2026-05-07 20:43:34'),
(23, 'Operativo', 1, '2026-05-08 23:24:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `promotor_id` int(11) DEFAULT NULL COMMENT 'NULL si no opera MXN',
  `cuenta_id` int(11) DEFAULT NULL COMMENT 'Cuenta virtual del cliente',
  `retencion_total` decimal(5,2) DEFAULT NULL COMMENT 'Porcentaje total de retención',
  `porcentaje_amiri` decimal(5,2) DEFAULT NULL COMMENT 'Comisión para Amiri',
  `porcentaje_promotor` decimal(5,2) DEFAULT NULL COMMENT 'Comisión para el promotor',
  `opera_mxn` tinyint(1) DEFAULT 1 COMMENT 'Opera venta de efectivo MXN',
  `opera_usd` tinyint(1) DEFAULT 0 COMMENT 'Opera con dólares',
  `opera_usdt` tinyint(1) DEFAULT 0 COMMENT 'Opera con USDT',
  `contacto` varchar(100) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  `eliminado_en` timestamp NULL DEFAULT NULL,
  `eliminado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `nombre`, `promotor_id`, `cuenta_id`, `retencion_total`, `porcentaje_amiri`, `porcentaje_promotor`, `opera_mxn`, `opera_usd`, `opera_usdt`, `contacto`, `activo`, `creado_en`, `eliminado_en`, `eliminado_por`) VALUES
(5, 'Cliente Cancun', 6, 26, 3.00, 2.00, 1.00, 1, 0, 0, 'Kike rizo', 1, '2026-03-27 00:06:16', NULL, NULL),
(6, 'Toño Hernandez', 1, 27, 1.00, 1.00, 0.00, 1, 0, 0, 'toñohernandez@gmail.com', 1, '2026-03-27 00:06:37', NULL, NULL),
(7, 'Adriana Hurtado', 1, 28, 1.00, 1.00, 0.00, 1, 0, 0, 'adyhurtado@gmail.com', 1, '2026-03-27 00:07:09', NULL, NULL),
(8, 'Juan Manuel Nuñez', 6, 29, 1.50, 1.00, 0.50, 1, 0, 0, 'juannuñez@gmail.com', 1, '2026-03-27 00:07:55', NULL, NULL),
(10, 'Martin', 1, 31, 0.00, 0.00, 0.00, 1, 1, 1, 'martin@gamil.com', 1, '2026-03-27 00:10:12', NULL, NULL),
(11, 'King Of Watches', 7, 39, 1.50, 1.30, 0.20, 1, 1, 1, '33 3391 9485', 1, '2026-04-02 23:13:54', NULL, NULL),
(12, 'Binance', 1, 42, 0.00, 0.00, 0.00, 1, 1, 1, 'N/A', 1, '2026-04-03 23:16:11', NULL, NULL),
(13, 'Rodrigo Zermeño', 1, 49, 1.50, 1.50, 0.00, 1, 1, 1, '3221389861', 1, '2026-04-22 18:44:17', NULL, NULL),
(14, 'Publico En General', 1, 50, 1.00, 1.00, 0.00, 1, 1, 1, '33 3333 3333', 1, '2026-04-22 18:44:45', NULL, NULL),
(133, 'Maestros Joyeros', 8, 172, 1.75, 1.50, 0.25, 1, 1, 1, 'maestrosjoyeros@gmail.com', 1, '2026-04-24 05:39:11', NULL, NULL),
(134, 'Juan Pablo - Picañas', 1, 173, 2.00, 2.00, 0.00, 1, 0, 0, 'N/A', 1, '2026-04-28 17:55:57', NULL, NULL),
(135, 'Maximos Joyeros', 7, 174, 3.50, 1.00, 2.50, 1, 1, 1, 'N/A', 1, '2026-04-28 18:28:57', NULL, NULL),
(136, 'Osvaldo Vazquez - Joyero', 9, 176, 1.50, 1.00, 0.50, 1, 1, 1, 'osvaldovazquez@gmail.com', 1, '2026-04-29 17:55:48', NULL, NULL),
(137, 'Raul', 6, 177, 1.50, 1.00, 0.50, 1, 1, 1, '-', 1, '2026-05-07 00:14:37', NULL, NULL),
(138, 'Miguel Cisneros', 10, 181, 1.00, 0.80, 0.20, 1, 1, 1, '- SANTOS', 1, '2026-05-08 16:57:31', NULL, NULL),
(139, 'Wb - Gas', 11, 183, 1.00, 0.70, 0.30, 1, 1, 1, 'NETO', 1, '2026-05-13 01:04:01', NULL, NULL),
(140, 'Emiliano Rivera', 12, 185, 1.00, 0.70, 0.30, 1, 1, 1, '33 3902 8994', 1, '2026-05-14 19:00:56', NULL, NULL),
(141, 'Raul Ortiz', 6, 186, 1.50, 1.00, 0.50, 1, 1, 1, 'Kike rizo', 1, '2026-05-19 17:01:58', NULL, NULL),
(142, 'Arq', 1, 187, 0.00, 0.00, 0.00, 1, 1, 1, 'DOLLAR APP', 1, '2026-05-19 23:38:46', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `consumos_inventario`
--

CREATE TABLE `consumos_inventario` (
  `id` bigint(20) NOT NULL,
  `movimiento_id` bigint(20) NOT NULL COMMENT 'ID del movimiento de venta',
  `inventario_id` bigint(20) NOT NULL COMMENT 'ID del lote de inventario consumido',
  `cantidad_consumida` decimal(16,8) NOT NULL,
  `paridad_compra` decimal(16,8) NOT NULL COMMENT 'Precio al que se compró',
  `paridad_venta` decimal(16,8) NOT NULL COMMENT 'Precio al que se vendió',
  `ganancia` decimal(16,8) NOT NULL COMMENT 'Ganancia calculada: (venta-compra)*cantidad',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `consumos_inventario`
--

INSERT INTO `consumos_inventario` (`id`, `movimiento_id`, `inventario_id`, `cantidad_consumida`, `paridad_compra`, `paridad_venta`, `ganancia`, `created_at`) VALUES
(1, 38, 1, 7942.85000000, 17.15000000, 17.50000000, 2779.99750000, '2026-04-23 23:45:41'),
(2, 57, 1, 6544.52000000, 17.15000000, 17.67110000, 3410.34937200, '2026-04-24 05:41:03'),
(3, 57, 4, 20000.00000000, 17.20000000, 17.67110000, 9422.00000000, '2026-04-24 05:41:03'),
(4, 57, 5, 34614.79000000, 17.46200000, 17.67110000, 7237.95258900, '2026-04-24 05:41:03'),
(5, 89, 1, 6544.52000000, 17.15000000, 17.67110000, 3410.34937200, '2026-04-28 17:38:37'),
(6, 89, 4, 20000.00000000, 17.20000000, 17.67110000, 9422.00000000, '2026-04-28 17:38:37'),
(7, 89, 5, 34614.79000000, 17.46200000, 17.67110000, 7237.95258900, '2026-04-28 17:38:37'),
(8, 143, 6, 126.00000000, 17.20000000, 17.56810000, 46.38060000, '2026-04-28 18:30:04'),
(9, 143, 7, 11369.99000000, 17.20000000, 17.56810000, 4185.29331900, '2026-04-28 18:30:04'),
(10, 143, 8, 5329.99000000, 17.47010000, 17.56810000, 522.33902000, '2026-04-28 18:30:04'),
(11, 200, 9, 700.00000000, 17.20000000, 17.74270000, 379.89000000, '2026-04-29 21:18:44'),
(12, 200, 11, 13880.68000000, 17.61210000, 17.74270000, 1812.81680800, '2026-04-29 21:18:44'),
(13, 233, 12, 30334.32000000, 17.57410000, 17.82680000, 7665.48266400, '2026-04-30 21:40:50'),
(14, 252, 12, 5395.00000000, 17.57410000, 17.57410000, 0.00000000, '2026-05-01 00:42:50'),
(15, 257, 12, 5395.00000000, 17.57410000, 17.57400000, -0.53950000, '2026-05-01 00:44:59'),
(16, 265, 12, 2105.00000000, 17.57410000, 17.57400000, -0.21050000, '2026-05-01 02:01:46'),
(17, 269, 12, 3725.13000000, 17.57410000, 17.72570000, 564.72970800, '2026-05-04 16:39:04'),
(18, 335, 12, 9267.69000000, 17.57410000, 17.50000000, -686.73582900, '2026-05-06 18:41:18'),
(19, 335, 13, 2589.45000000, 17.36050000, 17.50000000, 361.22827500, '2026-05-06 18:41:18'),
(20, 360, 13, 5.20000000, 17.36050000, 17.29550000, -0.33800000, '2026-05-06 21:59:00'),
(21, 360, 15, 2420.06000000, 17.15000000, 17.29550000, 352.11873000, '2026-05-06 21:59:00'),
(22, 364, 13, 5.20000000, 17.36050000, 17.29550000, -0.33800000, '2026-05-06 22:02:09'),
(23, 364, 15, 2420.06000000, 17.15000000, 17.29550000, 352.11873000, '2026-05-06 22:02:09'),
(24, 368, 15, 3265.54000000, 17.15000000, 17.29550000, 475.13607000, '2026-05-06 22:04:49'),
(25, 372, 15, 3469.10000000, 17.15000000, 17.29560000, 505.10096000, '2026-05-06 22:27:30'),
(26, 384, 15, 15490.37000000, 17.15000000, 17.30550000, 2408.75253500, '2026-05-07 00:27:50'),
(27, 397, 15, 3836.82000000, 17.15000000, 17.24080000, 348.38325600, '2026-05-07 20:42:43'),
(28, 410, 15, 1825.30000000, 17.15000000, 17.24100000, 166.10230000, '2026-05-07 23:53:36'),
(29, 419, 15, 4125.18000000, 17.15000000, 17.30540000, 641.05297200, '2026-05-08 17:07:04'),
(30, 434, 15, 13359.25000000, 17.15000000, 17.25100000, 1349.28425000, '2026-05-09 01:55:55'),
(31, 453, 15, 9485.84000000, 17.15000000, 17.06370000, -818.62799200, '2026-05-11 22:13:05'),
(32, 466, 15, 14341.33000000, 17.15000000, 17.27790000, 1834.25610700, '2026-05-12 18:05:46'),
(33, 472, 15, 3770.00000000, 17.15000000, 17.25990000, 414.32300000, '2026-05-12 20:04:03'),
(34, 497, 15, 16925.28730000, 17.15000000, 17.40000000, 4231.32000000, '2026-05-13 21:01:56'),
(35, 501, 15, 16925.28730000, 17.15000000, 17.40000000, 4231.32000000, '2026-05-13 21:03:07'),
(36, 517, 15, 1330.59000000, 17.15000000, 17.28556500, 180.38000000, '2026-05-14 19:28:32'),
(37, 527, 15, 6000.00000000, 17.15000000, 17.29020200, 841.21000000, '2026-05-14 22:36:10'),
(38, 542, 15, 30578.45000000, 17.15000000, 17.66380000, 15711.21000000, '2026-05-15 16:49:44'),
(39, 547, 15, 29.00000000, 17.15000000, 17.24137900, 2.65000000, '2026-05-15 16:58:24'),
(40, 553, 15, 11997.00000000, 17.15000000, 16.42830000, -8658.23000000, '2026-05-15 19:02:57'),
(41, 557, 15, 11997.81000000, 17.15000000, 17.38529000, 2822.96000000, '2026-05-15 19:04:00'),
(42, 576, 15, 8348.98000000, 17.15000000, 17.38538200, 1965.20000000, '2026-05-16 00:51:27'),
(43, 590, 15, 13244.10000000, 17.15000000, 17.43221500, 3737.68000000, '2026-05-18 17:45:37'),
(44, 610, 15, 7156.99270000, 17.15000000, 17.47300000, 2311.71000000, '2026-05-18 23:23:08'),
(45, 610, 16, 1000.00000000, 17.10000000, 17.47300000, 373.00000000, '2026-05-18 23:23:08'),
(46, 610, 17, 285.00000000, 17.10000000, 17.47300000, 106.30000000, '2026-05-18 23:23:08'),
(47, 610, 18, 4345.00000000, 17.10000000, 17.47300000, 1620.68000000, '2026-05-18 23:23:08'),
(48, 610, 19, 63.00730000, 17.20000000, 17.47300000, 17.20000000, '2026-05-18 23:23:08'),
(49, 614, 19, 1153.04000000, 17.20000000, 17.34545200, 167.71000000, '2026-05-18 23:25:39');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuentas`
--

CREATE TABLE `cuentas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `numero_cuenta` varchar(50) DEFAULT NULL,
  `banco` varchar(100) DEFAULT NULL,
  `tipo_cuenta` int(11) NOT NULL DEFAULT 1,
  `tipo_moneda` varchar(10) NOT NULL DEFAULT 'MXN',
  `saldo_actual` decimal(12,2) NOT NULL DEFAULT 0.00,
  `valor_mxn` decimal(14,2) NOT NULL DEFAULT 0.00,
  `es_caja_empresa` tinyint(1) DEFAULT 0,
  `documento_path` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  `eliminado_en` timestamp NULL DEFAULT NULL,
  `eliminado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cuentas`
--

INSERT INTO `cuentas` (`id`, `nombre`, `numero_cuenta`, `banco`, `tipo_cuenta`, `tipo_moneda`, `saldo_actual`, `valor_mxn`, `es_caja_empresa`, `documento_path`, `activo`, `creado_en`, `eliminado_en`, `eliminado_por`) VALUES
(1, 'Utilidades AMIRI', NULL, NULL, 3, 'MXN', -8896.47, 0.00, 1, NULL, 1, '2026-03-13 01:37:15', NULL, NULL),
(2, 'Caja de Efectivo', NULL, NULL, 2, 'MXN', 684857.50, 0.00, 1, NULL, 1, '2026-03-13 01:37:15', NULL, NULL),
(18, 'Caja Proveedor USD - Emilio Agraz', NULL, NULL, 6, 'USD', 0.00, 0.00, 0, NULL, 1, '2026-03-26 23:59:59', NULL, NULL),
(19, 'Caja Proveedor USDT - Emilio Agraz', NULL, NULL, 6, 'USDT', 0.00, 0.00, 0, NULL, 1, '2026-03-26 23:59:59', NULL, NULL),
(20, 'Caja Proveedor USD - Alejandro Bambas', NULL, NULL, 6, 'USD', 0.00, 0.00, 0, NULL, 0, '2026-03-27 00:01:27', NULL, NULL),
(21, 'Caja Proveedor USDT - Alejandro Bambas', NULL, NULL, 6, 'USDT', 0.00, 0.00, 0, NULL, 0, '2026-03-27 00:01:27', NULL, NULL),
(22, 'Caja Proveedor USD - Daniel Casa Cambio', NULL, NULL, 6, 'USD', 0.00, 0.00, 0, NULL, 0, '2026-03-27 00:02:33', NULL, NULL),
(23, 'Caja Proveedor USDT - Daniel Casa Cambio', NULL, NULL, 6, 'USDT', 0.00, 0.00, 0, NULL, 0, '2026-03-27 00:02:33', NULL, NULL),
(24, 'Comisiones - Kike Rizo', NULL, NULL, 5, 'MXN', 0.00, 0.00, 0, NULL, 1, '2026-03-27 00:04:23', NULL, NULL),
(25, 'Comisiones - Missael Jauregui', NULL, NULL, 5, 'MXN', 0.00, 0.00, 0, NULL, 1, '2026-03-27 00:05:33', NULL, NULL),
(26, 'Caja - Cliente Cancun', NULL, NULL, 4, 'MXN', 0.00, 0.00, 0, NULL, 1, '2026-03-27 00:06:16', NULL, NULL),
(27, 'Caja - Toño Hernandez', NULL, NULL, 4, 'MXN', 0.00, 0.00, 0, NULL, 1, '2026-03-27 00:06:37', NULL, NULL),
(28, 'Caja - Adriana Hurtado', NULL, NULL, 4, 'MXN', 0.00, 0.00, 0, NULL, 1, '2026-03-27 00:07:09', NULL, NULL),
(29, 'Caja - Juan Manuel Nuñez', NULL, NULL, 4, 'MXN', 0.00, 0.00, 0, NULL, 1, '2026-03-27 00:07:55', NULL, NULL),
(31, 'Caja - Martin', NULL, NULL, 4, 'MXN', 0.00, 0.00, 0, NULL, 1, '2026-03-27 00:10:12', NULL, NULL),
(32, 'Makalan Sa De Cv', '012320001261702500', 'BBVA', 1, 'MXN', 13801.36, 0.00, 1, NULL, 1, '2026-03-27 01:03:57', NULL, NULL),
(34, 'Tarpen Sa De Cv', '699180600007832575', 'FONDEADORA', 1, 'MXN', 702669.72, 0.00, 1, NULL, 1, '2026-03-27 01:05:11', NULL, NULL),
(36, 'Rosa Huerta', '7211801000038115231', 'ALBO', 1, 'MXN', 174261.14, 0.00, 1, NULL, 1, '2026-03-27 01:06:45', NULL, NULL),
(37, 'Mantela Sa De Cv', '01232001264988761', 'BBVA', 1, 'MXN', 0.00, 0.00, 1, NULL, 1, '2026-03-27 01:08:42', NULL, NULL),
(38, 'Ledger Stax', NULL, NULL, 2, 'USDT', 0.00, 0.00, 1, NULL, 1, '2026-04-01 22:38:24', NULL, NULL),
(39, 'Caja - King Of Watches', NULL, NULL, 4, 'MXN', 0.00, 0.00, 0, NULL, 1, '2026-04-02 23:13:54', NULL, NULL),
(40, 'Caja Proveedor USD - Jorge De La Vega', NULL, NULL, 6, 'USD', 0.00, 0.00, 0, NULL, 1, '2026-04-03 23:06:28', NULL, NULL),
(41, 'Caja Proveedor USDT - Jorge De La Vega', NULL, NULL, 6, 'USDT', 0.00, 0.00, 0, NULL, 1, '2026-04-03 23:06:28', NULL, NULL),
(42, 'Caja - Binance', NULL, NULL, 4, 'MXN', 0.00, 0.00, 0, NULL, 1, '2026-04-03 23:16:11', NULL, NULL),
(43, 'Caja Proveedor USD - Binance', NULL, NULL, 6, 'USD', 0.00, 0.00, 0, NULL, 1, '2026-04-03 23:32:20', NULL, NULL),
(44, 'Caja Proveedor USDT - Binance', NULL, NULL, 6, 'USDT', 0.00, 0.00, 0, NULL, 1, '2026-04-03 23:32:21', NULL, NULL),
(45, 'Caja Proveedor USD - Missael Jauregui', NULL, NULL, 6, 'USD', 0.00, 0.00, 0, NULL, 1, '2026-04-04 00:42:36', NULL, NULL),
(46, 'Caja Proveedor USDT - Missael Jauregui', NULL, NULL, 6, 'USDT', 0.00, 0.00, 0, NULL, 1, '2026-04-04 00:42:36', NULL, NULL),
(47, 'Fabiola Elena Bautista Martinez', '491312464', 'BBVA', 1, 'MXN', 0.00, 0.00, 1, NULL, 1, '2026-04-14 23:32:30', NULL, NULL),
(49, 'Caja - Rodrigo Zermeño', NULL, NULL, 4, 'MXN', 0.00, 0.00, 0, NULL, 1, '2026-04-22 18:44:17', NULL, NULL),
(50, 'Caja - Publico En General', NULL, NULL, 4, 'MXN', 0.00, 0.00, 0, NULL, 1, '2026-04-22 18:44:45', NULL, NULL),
(51, 'Binance Emiliano', NULL, NULL, 2, 'USDT', 142114.22, 0.00, 1, NULL, 1, '2026-04-22 18:47:40', NULL, NULL),
(52, 'Rojas Retiros', '123456789012345678', 'BANORTE', 1, 'MXN', 30.00, 0.00, 1, NULL, 1, '2026-04-22 19:42:50', NULL, NULL),
(53, 'Comisiones - Ana Maria - Maestros Joyeros', NULL, NULL, 5, 'MXN', 0.00, 0.00, 0, NULL, 1, '2026-04-23 23:42:11', NULL, NULL),
(172, 'Caja - Maestros Joyeros', NULL, NULL, 4, 'MXN', 0.00, 0.00, 0, NULL, 1, '2026-04-24 05:39:11', NULL, NULL),
(173, 'Caja - Juan Pablo - Picañas', NULL, NULL, 4, 'MXN', 0.00, 0.00, 0, NULL, 1, '2026-04-28 17:55:57', NULL, NULL),
(174, 'Caja - Maximos Joyeros', NULL, NULL, 4, 'MXN', 0.00, 0.00, 0, NULL, 1, '2026-04-28 18:28:57', NULL, NULL),
(175, 'Comisiones - Adrian Sandoval', NULL, NULL, 5, 'MXN', 70.00, 0.00, 0, NULL, 1, '2026-04-29 17:54:42', NULL, NULL),
(176, 'Caja - Osvaldo Vazquez - Joyero', NULL, NULL, 4, 'MXN', 13789.01, 0.00, 0, NULL, 1, '2026-04-29 17:55:48', NULL, NULL),
(177, 'Caja - Raul', NULL, NULL, 4, 'MXN', 0.00, 0.00, 0, NULL, 1, '2026-05-07 00:14:37', NULL, NULL),
(178, 'Caja Proveedor USD - Alfonso Santos', NULL, NULL, 6, 'USD', 0.00, 0.00, 0, NULL, 0, '2026-05-08 16:53:42', NULL, NULL),
(179, 'Caja Proveedor USDT - Alfonso Santos', NULL, NULL, 6, 'USDT', 0.00, 0.00, 0, NULL, 0, '2026-05-08 16:53:42', NULL, NULL),
(180, 'Comisiones - Alfonso Santos', NULL, NULL, 5, 'MXN', 0.00, 0.00, 0, NULL, 1, '2026-05-08 16:54:33', NULL, NULL),
(181, 'Caja - Miguel Cisneros', NULL, NULL, 4, 'MXN', 158400.00, 0.00, 0, NULL, 1, '2026-05-08 16:57:31', NULL, NULL),
(182, 'Comisiones - Ernesto Sandoval', NULL, NULL, 5, 'MXN', 318.84, 0.00, 0, NULL, 1, '2026-05-13 01:03:00', NULL, NULL),
(183, 'Caja - Wb - Gas', NULL, NULL, 4, 'MXN', 0.00, 0.00, 0, NULL, 1, '2026-05-13 01:04:01', NULL, NULL),
(184, 'Comisiones - Emiliano Hurtado', NULL, NULL, 5, 'MXN', 0.00, 0.00, 0, NULL, 1, '2026-05-14 19:00:17', NULL, NULL),
(185, 'Caja - Emiliano Rivera', NULL, NULL, 4, 'MXN', 0.00, 0.00, 0, NULL, 1, '2026-05-14 19:00:56', NULL, NULL),
(186, 'Caja - Raul Ortiz', NULL, NULL, 4, 'MXN', 700000.10, 0.00, 0, NULL, 1, '2026-05-19 17:01:58', NULL, NULL),
(187, 'Caja - Arq', NULL, NULL, 4, 'MXN', 0.00, 0.00, 0, NULL, 1, '2026-05-19 23:38:46', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `deudas_clientes`
--

CREATE TABLE `deudas_clientes` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `cuenta_cliente_id` int(11) DEFAULT NULL COMMENT 'Caja del cliente',
  `movimiento_venta_id` bigint(20) DEFAULT NULL COMMENT 'Referencia al grupo de movimientos de la venta',
  `movimiento_pago_id` bigint(20) DEFAULT NULL COMMENT 'Referencia al grupo de movimientos del pago',
  `monto_total` decimal(12,2) NOT NULL COMMENT 'Monto total de la venta',
  `comision_amiri` decimal(12,2) NOT NULL DEFAULT 0.00,
  `comision_promotor` decimal(12,2) NOT NULL DEFAULT 0.00,
  `monto_neto_cliente` decimal(12,2) NOT NULL COMMENT 'Lo que recibe el cliente después de comisiones',
  `moneda` enum('MXN','USD','USDT') NOT NULL DEFAULT 'MXN' COMMENT 'Tipo de moneda de la deuda',
  `fecha` date NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` enum('pendiente','pagado','parcial') NOT NULL DEFAULT 'pendiente',
  `monto_pagado` decimal(12,2) NOT NULL DEFAULT 0.00,
  `fecha_pago` date DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registro de ventas de efectivo pendientes de pago por clientes';

--
-- Volcado de datos para la tabla `deudas_clientes`
--

INSERT INTO `deudas_clientes` (`id`, `cliente_id`, `cuenta_cliente_id`, `movimiento_venta_id`, `movimiento_pago_id`, `monto_total`, `comision_amiri`, `comision_promotor`, `monto_neto_cliente`, `moneda`, `fecha`, `descripcion`, `estado`, `monto_pagado`, `fecha_pago`, `activo`, `creado_en`, `actualizado_en`) VALUES
(1, 11, 39, 1776884329363, NULL, 213000.00, 2769.00, 426.00, 209805.00, 'MXN', '2026-04-22', 'Venta de efectivo - Pendiente de pago', 'pendiente', 0.00, NULL, 0, '2026-04-22 18:58:49', '2026-04-22 18:59:40'),
(2, 5, 26, 1776884982298, 1777398104393, 213000.00, 1448.40, 0.00, 211551.60, 'MXN', '2026-04-22', 'Venta de efectivo - Pendiente de pago', 'pagado', 211551.60, '2026-04-23', 1, '2026-04-22 19:09:42', '2026-04-28 17:41:44'),
(3, 14, 50, 1776885542338, NULL, 83633.40, 83633.40, 0.00, 0.00, 'MXN', '2026-04-22', 'Venta de efectivo - Pendiente de pago', 'pendiente', 0.00, NULL, 1, '2026-04-22 19:19:02', '2026-04-22 19:19:02'),
(4, 5, 26, 1776988094308, 1777398091423, 53250.00, 1065.00, 532.50, 51652.50, 'MXN', '2026-04-23', 'Venta de efectivo - Pendiente de pago', 'pagado', 51652.50, '2026-04-23', 1, '2026-04-23 23:48:14', '2026-04-28 17:41:31'),
(5, 11, 39, 1776988159039, NULL, 123000.00, 1599.00, 246.00, 121155.00, 'MXN', '2026-04-23', 'Venta de efectivo - Pendiente de pago', 'pendiente', 0.00, NULL, 0, '2026-04-23 23:49:19', '2026-04-24 16:43:20'),
(6, 11, 39, 1777397860213, 1777398027533, 123000.00, 1599.00, 246.00, 121155.00, 'MXN', '2026-04-23', 'Venta de efectivo - Pendiente de pago', 'pagado', 121155.00, '2026-04-23', 1, '2026-04-28 17:37:40', '2026-04-28 17:40:27'),
(7, 134, 173, 1777399012166, 1777421409231, 590000.00, 11800.00, 0.00, 578200.00, 'MXN', '2026-04-28', 'Venta de efectivo - Pendiente de pago', 'pagado', 578200.00, '2026-04-28', 1, '2026-04-28 17:56:52', '2026-04-29 00:10:09'),
(8, 5, 26, 1777399059118, 1778027467689, 21300.00, 421.74, 213.00, 20665.26, 'MXN', '2026-04-28', 'Venta de efectivo - Pendiente de pago', 'pagado', 20665.26, '2026-05-05', 1, '2026-04-28 17:57:39', '2026-05-06 00:31:07'),
(9, 5, 26, 1777422889860, NULL, 266250.00, 5325.00, 2662.50, 258262.50, 'MXN', '2026-04-28', 'Venta de efectivo - Pendiente de pago', 'pendiente', 0.00, NULL, 0, '2026-04-29 00:34:49', '2026-04-29 00:37:53'),
(10, 5, 26, 1777423221857, 1778027481036, 266650.00, 5333.00, 2666.50, 258650.50, 'MXN', '2026-04-28', 'Venta de efectivo - Pendiente de pago', 'pagado', 258650.50, '2026-05-05', 1, '2026-04-29 00:40:21', '2026-05-06 00:31:21'),
(11, 136, 176, 1777486401416, 1777591697547, 17895.00, 178.95, 89.48, 17626.57, 'MXN', '2026-04-29', 'Venta de efectivo - Pendiente de pago', 'pagado', 17626.57, '2026-04-30', 1, '2026-04-29 18:13:21', '2026-04-30 23:28:17'),
(12, 11, 39, 1777498873764, 1777508291927, 132999.98, 1729.00, 266.00, 131004.98, 'MXN', '2026-04-29', 'Venta de efectivo - Pendiente de pago', 'pagado', 131004.98, '2026-04-29', 1, '2026-04-29 21:41:13', '2026-04-30 00:18:11'),
(13, 5, 26, 1777571534546, 1777998468177, 109833.00, 2196.66, 1098.33, 106538.01, 'MXN', '2026-04-30', 'Venta de efectivo - Pendiente de pago', 'pagado', 106538.01, '2026-05-04', 1, '2026-04-30 17:52:14', '2026-05-05 16:27:48'),
(14, 5, 26, 1777571782297, 1777576256743, 95850.00, 1917.00, 958.50, 92974.50, 'MXN', '2026-04-30', 'Venta de efectivo - Pendiente de pago', 'pagado', 92974.50, '2026-04-30', 1, '2026-04-30 17:56:22', '2026-04-30 19:10:56'),
(15, 136, 176, 1777580952544, 1777591713709, 52573.00, 525.73, 262.87, 51784.40, 'MXN', '2026-04-30', 'Venta de efectivo - Pendiente de pago', 'pagado', 51784.40, '2026-04-30', 1, '2026-04-30 20:29:12', '2026-04-30 23:28:33'),
(17, 136, 176, 1777919384658, 1778089871641, 12995.00, 129.95, 64.98, 12800.07, 'MXN', '2026-05-04', 'Venta de efectivo - Pendiente de pago', 'pagado', 12800.07, '2026-05-06', 1, '2026-05-04 18:29:44', '2026-05-06 17:51:11'),
(18, 8, 29, 1777941435760, 1777941823894, 304568.53, 3045.69, 1522.84, 300000.00, 'MXN', '2026-05-04', 'Venta de efectivo - Pendiente de pago', 'pagado', 300000.00, '2026-05-04', 1, '2026-05-05 00:37:15', '2026-05-05 00:43:43'),
(19, 136, 176, 1777999546654, 1778089871641, 5190.00, 51.90, 25.95, 5112.15, 'MXN', '2026-05-05', 'Venta de efectivo - Pendiente de pago', 'pagado', 5112.15, '2026-05-06', 1, '2026-05-05 16:45:46', '2026-05-06 17:51:11'),
(20, 136, 176, 1778016088652, 1778089871641, 44545.00, 445.45, 222.73, 43876.82, 'MXN', '2026-05-05', 'Venta de efectivo - Pendiente de pago', 'pagado', 43876.82, '2026-05-06', 1, '2026-05-05 21:21:28', '2026-05-06 17:51:11'),
(21, 136, 176, 1778027616820, 1778089871641, 3211.00, 32.11, 16.06, 3162.83, 'MXN', '2026-05-05', 'Venta de efectivo - Pendiente de pago', 'pagado', 3162.83, '2026-05-06', 1, '2026-05-06 00:33:36', '2026-05-06 17:51:11'),
(22, 8, 29, 1778090169753, 1778194668698, 265970.22, 2659.70, 1329.85, 261980.67, 'MXN', '2026-05-06', 'Venta de efectivo - Pendiente de pago', 'pagado', 261980.67, '2026-05-07', 1, '2026-05-06 17:56:09', '2026-05-07 22:57:48'),
(23, 11, 39, 1778101355456, 1778104560949, 110000.00, 1430.00, 220.00, 108350.00, 'MXN', '2026-05-06', 'Venta de efectivo - Pendiente de pago', 'pagado', 108350.00, '2026-05-06', 1, '2026-05-06 21:02:35', '2026-05-06 21:56:00'),
(24, 137, 177, 1778113007107, 1778113027990, 761421.32, 7614.21, 3807.11, 750000.00, 'MXN', '2026-05-06', 'Venta de efectivo - Pendiente de pago', 'pagado', 750000.00, '2026-05-06', 1, '2026-05-07 00:16:47', '2026-05-07 00:17:07'),
(25, 137, 177, 1778179883261, NULL, 100.00, 1.00, 0.50, 98.50, 'MXN', '2026-05-07', 'Venta de efectivo - Pendiente de pago', 'pendiente', 0.00, NULL, 0, '2026-05-07 18:51:23', '2026-05-07 18:51:46'),
(26, 138, 181, 1778259479502, 1778291260367, 500000.00, 4000.00, 1000.00, 495000.00, 'MXN', '2026-05-08', 'Venta de efectivo - Pendiente de pago', 'pagado', 495000.00, '2026-05-08', 1, '2026-05-08 16:57:59', '2026-05-09 01:47:40'),
(27, 139, 183, 1778634273177, 1778704201562, 241000.90, 1687.01, 723.00, 238590.89, 'MXN', '2026-05-12', 'Venta de efectivo - Pendiente de pago', 'pagado', 238590.89, '2026-05-13', 1, '2026-05-13 01:04:33', '2026-05-13 20:30:01'),
(28, 136, 176, 1778634325875, NULL, 3012.00, 30.12, 15.06, 2966.82, 'MXN', '2026-05-12', 'Venta de efectivo - Pendiente de pago', 'pendiente', 0.00, NULL, 1, '2026-05-13 01:05:25', '2026-05-13 01:05:25'),
(29, 140, 185, 1778785276570, 1778785336658, 80000.00, 560.00, 240.00, 79200.00, 'MXN', '2026-05-14', 'Venta de efectivo - Pendiente de pago', 'pagado', 79200.00, '2026-05-14', 1, '2026-05-14 19:01:16', '2026-05-14 19:02:16'),
(30, 139, 183, 1778893528541, 1779145896114, 106280.30, 743.96, 318.84, 105217.50, 'MXN', '2026-05-15', 'Venta de efectivo - Pendiente de pago', 'pagado', 105217.50, '2026-05-18', 1, '2026-05-16 01:05:28', '2026-05-18 23:11:36'),
(31, 138, 181, 1779136454671, NULL, 160000.00, 1280.00, 320.00, 158400.00, 'MXN', '2026-05-18', 'Venta de efectivo - Pendiente de pago', 'pendiente', 0.00, NULL, 1, '2026-05-18 20:34:14', '2026-05-18 20:34:14'),
(32, 136, 176, 1779147388334, NULL, 10987.00, 109.87, 54.94, 10822.19, 'MXN', '2026-05-18', 'Venta de efectivo - Pendiente de pago', 'pendiente', 0.00, NULL, 1, '2026-05-18 23:36:28', '2026-05-18 23:36:28'),
(33, 141, 186, 1779210230246, NULL, 710660.00, 7106.60, 3553.30, 700000.10, 'MXN', '2026-05-19', 'Venta de efectivo - Pendiente de pago', 'pendiente', 0.00, NULL, 1, '2026-05-19 17:03:50', '2026-05-19 17:03:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `deudas_empresa`
--

CREATE TABLE `deudas_empresa` (
  `id` int(11) NOT NULL,
  `prestamista_id` int(11) NOT NULL,
  `cuenta_id` int(11) DEFAULT NULL COMMENT 'Cuenta donde entró el préstamo',
  `monto` decimal(12,2) NOT NULL,
  `fecha` date NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` enum('pendiente','pagado','parcial') NOT NULL DEFAULT 'pendiente',
  `monto_pagado` decimal(12,2) NOT NULL DEFAULT 0.00,
  `fecha_pago` date DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `deudas_empresa`
--

INSERT INTO `deudas_empresa` (`id`, `prestamista_id`, `cuenta_id`, `monto`, `fecha`, `descripcion`, `estado`, `monto_pagado`, `fecha_pago`, `activo`, `creado_en`, `actualizado_en`) VALUES
(1, 4, 2, 511500.00, '2026-04-22', '', 'pagado', 511500.00, '2026-04-23', 1, '2026-04-22 18:46:35', '2026-04-28 17:39:46'),
(2, 4, 32, 511500.00, '2026-04-22', '', 'pagado', 511500.00, '2026-04-23', 1, '2026-04-22 19:20:44', '2026-04-28 17:39:46'),
(3, 4, 32, 500000.00, '2026-04-23', '', 'pendiente', 0.00, NULL, 0, '2026-04-23 23:53:28', '2026-04-24 16:42:56'),
(4, 4, 34, 361000.00, '2026-04-23', '', 'pagado', 361000.00, '2026-04-23', 1, '2026-04-28 17:35:05', '2026-04-28 17:39:46'),
(5, 3, 37, 200000.00, '2026-04-28', 'Los Tome De Mantela Para Amiri', 'pagado', 200000.00, '2026-05-11', 1, '2026-04-28 18:01:58', '2026-05-11 17:55:07'),
(6, 4, 34, 300000.00, '2026-04-28', '', 'pagado', 300000.00, '2026-05-07', 1, '2026-04-29 00:09:31', '2026-05-07 23:50:18'),
(7, 4, 36, 260000.00, '2026-04-29', NULL, 'pagado', 260000.00, '2026-04-30', 1, '2026-04-29 21:03:04', '2026-04-30 17:50:19'),
(8, 4, 34, 1000000.00, '2026-04-30', NULL, 'pagado', 1000000.00, '2026-05-07', 1, '2026-04-30 20:30:27', '2026-05-07 23:50:18'),
(9, 4, 2, 2048971.40, '2026-05-06', NULL, 'pendiente', 0.00, NULL, 0, '2026-05-06 21:31:24', '2026-05-06 21:31:39'),
(10, 4, 2, 2048972.78, '2026-05-06', NULL, 'parcial', 472350.60, '2026-05-14', 1, '2026-05-06 21:32:19', '2026-05-14 16:51:11'),
(11, 4, 2, 108349.40, '2026-05-06', NULL, 'pagado', 108349.40, '2026-05-11', 1, '2026-05-06 21:55:43', '2026-05-11 22:27:51'),
(12, 4, 2, 750000.00, '2026-05-06', NULL, 'pagado', 750000.00, '2026-05-11', 1, '2026-05-07 00:11:56', '2026-05-11 22:27:05'),
(13, 4, 2, 495000.00, '2026-05-08', NULL, 'pagado', 495000.00, '2026-05-08', 1, '2026-05-08 21:47:15', '2026-05-09 02:08:10'),
(14, 4, 2, 4300.00, '2026-05-08', NULL, 'pagado', 4300.00, '2026-05-08', 1, '2026-05-08 23:20:38', '2026-05-09 02:08:10'),
(15, 3, 2, 698000.00, '2026-05-13', NULL, 'pendiente', 0.00, NULL, 1, '2026-05-13 20:29:19', '2026-05-13 20:29:19'),
(16, 5, 2, 550000.00, '2026-05-13', NULL, 'pagado', 550000.00, '2026-05-18', 1, '2026-05-13 21:00:06', '2026-05-18 23:39:44'),
(17, 6, 2, 486000.00, '2026-05-14', NULL, 'pagado', 486000.00, '2026-05-18', 1, '2026-05-14 17:17:35', '2026-05-18 22:59:05'),
(18, 6, 36, 150000.00, '2026-05-14', NULL, 'pagado', 150000.00, '2026-05-19', 1, '2026-05-14 21:58:25', '2026-05-19 22:03:23'),
(19, 7, 2, 74779.00, '2026-05-14', NULL, 'pagado', 74779.00, '2026-05-14', 1, '2026-05-15 00:37:55', '2026-05-15 00:38:07'),
(20, 4, 2, 455265.29, '2026-05-14', NULL, 'parcial', 348460.63, '2026-05-15', 1, '2026-05-15 00:46:02', '2026-05-16 00:59:57'),
(21, 7, 2, 4629.00, '2026-05-15', NULL, 'pagado', 4629.00, '2026-05-15', 1, '2026-05-15 19:23:48', '2026-05-15 19:23:57'),
(22, 7, 2, 920.00, '2026-05-18', NULL, 'pagado', 920.00, '2026-05-18', 1, '2026-05-18 17:33:22', '2026-05-18 17:33:45'),
(23, 3, 2, 200000.00, '2026-05-18', NULL, 'pendiente', 0.00, NULL, 1, '2026-05-18 17:39:16', '2026-05-18 17:39:16'),
(24, 6, 2, 200000.00, '2026-05-18', NULL, 'pagado', 200000.00, '2026-05-18', 1, '2026-05-18 22:56:38', '2026-05-18 22:59:05'),
(25, 6, 36, 308850.00, '2026-05-18', NULL, 'pagado', 308850.00, '2026-05-18', 1, '2026-05-18 22:56:53', '2026-05-18 22:59:05'),
(26, 4, 2, 600000.00, '2026-05-18', NULL, 'pendiente', 0.00, NULL, 1, '2026-05-18 22:57:40', '2026-05-18 22:57:40'),
(27, 6, 2, 100000.00, '2026-05-18', NULL, 'pagado', 100000.00, '2026-05-19', 1, '2026-05-18 22:59:57', '2026-05-19 22:03:23'),
(28, 6, 2, 81000.00, '2026-05-19', NULL, 'pagado', 81000.00, '2026-05-19', 1, '2026-05-19 22:01:38', '2026-05-19 22:03:23'),
(29, 6, 36, 174150.00, '2026-05-19', NULL, 'pagado', 174150.00, '2026-05-19', 1, '2026-05-19 22:02:40', '2026-05-19 22:03:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `directorio_depositos`
--

CREATE TABLE `directorio_depositos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `banco` varchar(100) DEFAULT NULL,
  `cuenta` varchar(50) DEFAULT NULL,
  `clabe` varchar(50) DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'activo',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `directorio_depositos`
--

INSERT INTO `directorio_depositos` (`id`, `nombre`, `banco`, `cuenta`, `clabe`, `estado`, `creado_en`) VALUES
(1, 'MANTELA SA DE CV', 'BBVA', '0126498876', '012320004913124640', 'activo', '2026-04-14 23:39:06'),
(2, 'JORGE GUTIERREZ CORONADO', 'BBVA', '491310615', '12180004913106154', 'inactivo', '2026-04-14 23:46:30'),
(3, 'COMERCIALIZADORA MAKALAN SA DE CV', 'BBVA', '0126170250', '012320001261702500', 'activo', '2026-04-14 23:47:49'),
(4, 'FABIOLA ELENA BAUTISTA MARTINEZ', 'BBVA', '491312464', '12320004913124640', 'inactivo', '2026-04-21 23:09:14'),
(5, 'Sara Sanchez Jacobo', 'Banorte', '1338638684', '072320013386386842', 'inactivo', '2026-04-21 23:09:14'),
(6, 'MANTELA SA DE CV', 'BBVA', '126498876', '12320001264988760', 'inactivo', '2026-04-21 23:09:14'),
(7, 'Liliana Reyes Cervantes', 'BANORTE', '1339024190', '072320013390241902', 'inactivo', '2026-04-22 19:55:53'),
(8, 'Rosa Lilia Lopez Lopez', 'BANORTE', '1339619756', '072320013396197568', 'inactivo', '2026-04-22 19:55:53'),
(9, 'Victor Manuel Luevanos Miranda', 'BANORTE', '1325979752', '072320013259797526', 'inactivo', '2026-04-22 19:55:53'),
(10, 'Cesar Adrian Becerra Villa', 'BANORTE', '1310454714', '072320013104547148', 'inactivo', '2026-04-22 19:55:53'),
(11, 'Sonia Alejandra Esquivel Gonzalez', 'BANORTE', '1337002822', '072320013370028226', 'inactivo', '2026-04-22 19:55:53'),
(12, 'Mario Alberto Rodriguez Flores', 'BANORTE', '1347086308', '072320013470863080', 'inactivo', '2026-04-22 19:55:53'),
(13, 'Ana Fernanda Chong Vargas', 'BANORTE', '1341321315', '072320013413213150', 'inactivo', '2026-04-22 19:55:53'),
(14, 'Rosa Guadalupe Villalvazo Flores', 'BANORTE', '1347082252', '072320013470822526', 'inactivo', '2026-04-22 19:55:53'),
(15, 'Miriam Tejeda Aguilar', 'BANORTE', '1345608939', '072320013456089396', 'inactivo', '2026-04-22 19:55:53'),
(16, 'Citlaly Padilla Gonzalez', 'BANORTE', '1356107429', '072320013561074296', 'inactivo', '2026-04-22 19:55:53'),
(17, 'Mario Michel Alonso Jardon', 'BANORTE', '1352118810', '072320013521188106', 'inactivo', '2026-04-22 19:55:53'),
(18, 'Cristian Jarete Alonso Huizar', 'BANORTE', '1352133505', '072320013521335056', 'inactivo', '2026-04-22 19:55:53'),
(19, 'Paulina Yazmin Caldera Barragan', 'BANORTE', '1352128202', '072320013521282028', 'inactivo', '2026-04-22 19:55:53'),
(20, 'Enedina Trinidad Perez', 'BANORTE', '1352379684', '072320013523796842', 'inactivo', '2026-04-22 19:55:53'),
(21, 'Ricardo Reynaga Nunez', 'BANORTE', '1352387942', '072320013523879426', 'inactivo', '2026-04-22 19:55:53'),
(22, 'Maritza Tafoya Glaytan', 'BANORTE', '1352369108', '072320013523691086', 'inactivo', '2026-04-22 19:55:53'),
(23, 'Hilda Margarita Ruelas Vazquez', 'BANORTE', '1359083300', '072320013590833000', 'inactivo', '2026-04-22 19:55:53'),
(24, 'Luis Saucedo Lopez', 'BANORTE', '1361146244', '072320013611462444', 'inactivo', '2026-04-22 19:55:53'),
(25, 'Santos Ricardo Esparza Gomez', 'BANORTE', '1338631902', '072320013386319026', 'inactivo', '2026-04-23 18:28:53'),
(26, 'Zaira Yazmin Esparza Ruiz', 'BANORTE', '1339832274', '072320013398322742', 'inactivo', '2026-04-23 18:28:53'),
(27, 'Neyli Berenice Martinez Flores', 'BANORTE', '1317593135', '072320013175931356', 'inactivo', '2026-04-23 18:28:53'),
(28, 'Evelyn Guadalupe Garcia Amaya', 'BANORTE', '1311294850', '072320013112948506', 'inactivo', '2026-04-23 18:28:53'),
(29, 'Joaquin Ezequiel Tapia Guzman', 'BANORTE', '1319145556', '072320013191455560', 'activo', '2026-04-23 18:28:53'),
(30, 'Roberto Camarena Cordero', 'BANORTE', '1341225413', '072320013412254136', 'inactivo', '2026-04-23 18:28:53'),
(31, 'Aillyn Gissel Santos Meraz', 'BANORTE', '1338683657', '072320013386836570', 'inactivo', '2026-04-23 18:28:53'),
(32, 'Angel Fernando Garcia Reyes', 'BANORTE', '1323119376', '072320013231193766', 'inactivo', '2026-04-23 18:28:53'),
(33, 'Jorge De Carlo Hurtado Rocha', 'BANORTE', '1338882973', '072320013388829734', 'activo', '2026-04-23 18:28:53'),
(34, 'Karla Lizeth Hernandez Peña', 'BANORTE', '1347253294', '072320013472532940', 'activo', '2026-04-23 18:28:53'),
(35, 'Edgar Omar Sifuentes Plazola', 'BANORTE', '1342699794', '072320013426997942', 'inactivo', '2026-04-23 18:28:53'),
(36, 'Joab Emanuel Raiz Diaz', 'BANORTE', '1347257573', '072320013472575732', 'activo', '2026-04-23 18:28:53'),
(37, 'Jazmin Alejandra Rendon Pacheco', 'BANORTE', '1339419107', '072320013394191074', 'activo', '2026-04-23 18:28:53'),
(38, 'Nayeli De Jesus Caldera Barragan', 'BANORTE', '1353023797', '072320013530237970', 'inactivo', '2026-04-23 18:28:53'),
(39, 'Guadalupe Zenorina Rivera Palacios', 'BANORTE', '1352809668', '072320013528096686', 'inactivo', '2026-04-23 18:28:53'),
(40, 'Brenda Aracely Montano Hernandez', 'BANORTE', '1352736335', '072320013527363352', 'inactivo', '2026-04-23 18:28:53'),
(41, 'Maria De Luz Barragan Ruelas', 'BANORTE', '1352812912', '072320013528129126', 'activo', '2026-04-23 18:28:53'),
(42, 'Leslie Judith Ruiz Rodriguez', 'BANORTE', '1356732009', '072320013567320096', 'activo', '2026-04-23 18:28:53'),
(43, 'Dulce Maria Mora Herrera', 'BANORTE', '1358855564', '072320013588555646', 'inactivo', '2026-04-23 18:28:53'),
(44, 'Lorena Ramirez De La O', 'BANORTE', '1361426816', '072320013614268166', 'activo', '2026-04-23 18:28:53'),
(45, 'Jorge Gutierrez Coronado', 'BANORTE', '1360141466', '072320013601414664', 'activo', '2026-04-23 18:28:53'),
(46, 'Jose Salvador Hernandez De La O', 'BANORTE', '1361430699', '072320013614306992', 'activo', '2026-04-23 18:28:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gastos`
--

CREATE TABLE `gastos` (
  `id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `cuenta_id` int(11) DEFAULT NULL,
  `movimiento_id` bigint(20) DEFAULT NULL COMMENT 'Referencia al movimiento de flujo',
  `categoria_id` int(11) DEFAULT NULL COMMENT 'Tipo/categoría de gasto',
  `clasificacion` enum('fijo','variable') NOT NULL DEFAULT 'variable',
  `monto` decimal(12,2) NOT NULL,
  `concepto` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `gastos`
--

INSERT INTO `gastos` (`id`, `fecha`, `cuenta_id`, `movimiento_id`, `categoria_id`, `clasificacion`, `monto`, `concepto`, `activo`, `creado_en`) VALUES
(1, '2026-04-23', 2, NULL, 17, 'variable', 1570.00, '', 0, '2026-04-24 05:44:36'),
(2, '2026-04-20', 2, NULL, 2, 'fijo', 800.00, '', 1, '2026-04-28 17:45:26'),
(3, '2026-04-22', 2, NULL, 11, 'variable', 1100.00, 'Totatlplay Eduardo', 1, '2026-04-28 17:46:32'),
(4, '2026-04-28', 2, NULL, 13, 'variable', 1570.00, 'Toner Impresora', 1, '2026-04-28 17:47:24'),
(5, '2026-04-28', 36, NULL, 18, 'variable', 3500.00, 'Citlaly Azul', 1, '2026-04-28 17:48:58'),
(6, '2026-04-28', 2, NULL, 19, 'variable', 15400.00, 'Poderes Empresa', 0, '2026-04-28 17:51:58'),
(7, '2026-04-24', 36, NULL, 19, 'variable', 15400.00, 'Poderes Empresa', 1, '2026-04-28 17:52:43'),
(8, '2026-04-28', 52, NULL, 19, 'variable', 3000.00, 'Vuelta Eduardo Socio Amiri', 1, '2026-04-28 17:53:39'),
(9, '2026-04-28', 2, NULL, 19, 'variable', 1700.00, 'Entrega Poderes', 1, '2026-04-29 00:30:42'),
(10, '2026-04-29', 2, NULL, 2, 'fijo', 500.00, '', 0, '2026-04-29 00:37:17'),
(11, '2026-04-29', 2, NULL, 2, 'fijo', 500.00, '', 1, '2026-04-29 00:39:41'),
(12, '2026-04-30', 2, NULL, 2, 'fijo', 500.00, '', 1, '2026-04-30 20:32:27'),
(13, '2026-04-30', 2, NULL, 18, 'variable', 5000.00, 'Mandados A Citlaly Azul', 0, '2026-04-30 23:25:32'),
(14, '2026-04-30', 34, NULL, 18, 'variable', 5000.00, 'Mandados A Azul Citlaly', 1, '2026-04-30 23:26:25'),
(15, '2026-05-01', 2, NULL, 20, 'fijo', 20000.00, 'Emiliano', 0, '2026-05-01 00:38:37'),
(16, '2026-04-30', 2, NULL, 21, 'fijo', 94811.73, 'Diego', 1, '2026-05-01 00:45:35'),
(17, '2026-04-30', 2, NULL, 20, 'fijo', 20000.00, 'Emiliano', 1, '2026-05-01 00:55:05'),
(18, '2026-05-05', 2, NULL, 2, 'fijo', 500.00, '', 1, '2026-05-05 16:51:50'),
(19, '2026-05-05', 36, NULL, 3, 'fijo', 16000.00, 'Renta Mayo', 1, '2026-05-05 19:32:47'),
(20, '2026-05-05', 2, NULL, 19, 'variable', 1600.00, 'Juan Pablo Poderes', 1, '2026-05-05 19:56:58'),
(21, '2026-05-05', 36, NULL, 4, 'fijo', 3600.00, '', 1, '2026-05-05 19:57:52'),
(22, '2026-05-05', 2, NULL, 7, 'fijo', 5435.00, '', 0, '2026-05-06 00:44:16'),
(23, '2026-05-06', 34, NULL, 7, 'fijo', 5435.00, '', 1, '2026-05-06 17:49:54'),
(24, '2026-05-07', 2, NULL, 22, 'variable', 500.00, 'Reconexión', 1, '2026-05-07 20:44:38'),
(25, '2026-05-08', 2, NULL, 2, 'fijo', 500.00, '', 1, '2026-05-08 23:20:51'),
(26, '2026-05-08', 2, NULL, 23, 'fijo', 3800.00, '', 1, '2026-05-08 23:24:39'),
(27, '2026-05-08', 2, NULL, 7, 'fijo', 1880.00, '', 0, '2026-05-09 01:48:30'),
(28, '2026-05-08', 34, NULL, 7, 'fijo', 1880.00, '', 1, '2026-05-09 01:49:05'),
(29, '2026-05-13', 36, NULL, 19, 'variable', 1000.00, 'Cita', 1, '2026-05-13 20:23:42'),
(30, '2026-05-13', 34, NULL, 7, 'fijo', 651.11, '', 1, '2026-05-13 21:07:26'),
(31, '2026-05-15', 36, NULL, 4, 'fijo', 30.00, '', 1, '2026-05-15 19:24:27'),
(32, '2026-05-15', 34, NULL, 7, 'fijo', 81.32, '', 0, '2026-05-16 00:42:22'),
(33, '2026-05-15', 34, NULL, 7, 'fijo', 81.20, '', 1, '2026-05-16 00:42:43'),
(34, '2026-05-15', 2, NULL, 2, 'fijo', 500.00, '', 1, '2026-05-16 00:43:26'),
(35, '2026-05-18', 2, NULL, 6, 'variable', 43057.69, '', 1, '2026-05-18 23:46:02'),
(36, '2026-05-19', 34, NULL, 7, 'fijo', 330.40, '', 1, '2026-05-19 18:14:04'),
(37, '2026-05-19', 34, NULL, 7, 'fijo', 81.20, '', 1, '2026-05-19 18:48:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_saldos`
--

CREATE TABLE `historial_saldos` (
  `id` bigint(20) NOT NULL,
  `cuenta_id` int(11) NOT NULL,
  `movimiento_id` bigint(20) NOT NULL COMMENT 'Referencia al movimiento que causó el cambio',
  `tipo` enum('entrada','salida') NOT NULL,
  `monto` decimal(14,2) NOT NULL,
  `saldo_anterior` decimal(14,2) NOT NULL,
  `saldo_nuevo` decimal(14,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial_saldos`
--

INSERT INTO `historial_saldos` (`id`, `cuenta_id`, `movimiento_id`, `tipo`, `monto`, `saldo_anterior`, `saldo_nuevo`, `created_at`) VALUES
(1, 32, 9, 'salida', 213000.00, 213000.00, 0.00, '2026-04-22 18:59:40'),
(2, 39, 10, 'salida', 209805.00, 209805.00, 0.00, '2026-04-22 18:59:40'),
(3, 1, 11, 'salida', 2769.00, 2769.00, 0.00, '2026-04-22 18:59:40'),
(4, 25, 12, 'salida', 426.00, 426.00, 0.00, '2026-04-22 18:59:40'),
(5, 32, 25, 'entrada', 473978.50, 100028.42, 574006.92, '2026-04-22 19:23:31'),
(6, 2, 25, 'salida', 473978.50, 473978.50, 0.00, '2026-04-22 19:23:31'),
(7, 2, 62, 'entrada', 511500.00, 569278.81, 1080778.81, '2026-04-24 05:42:25'),
(8, 2, 66, 'salida', 1570.00, 41101.96, 39531.96, '2026-04-24 05:44:36'),
(9, 1, 67, 'salida', 1570.00, 110596.10, 109026.10, '2026-04-24 05:44:36'),
(10, 2, 68, 'entrada', 1570.00, 39531.96, 41101.96, '2026-04-24 16:39:22'),
(11, 1, 69, 'entrada', 1570.00, 109026.10, 110596.10, '2026-04-24 16:39:22'),
(12, 2, 70, 'entrada', 500000.00, 41101.96, 541101.96, '2026-04-24 16:39:26'),
(13, 2, 71, 'entrada', 28176.85, 541101.96, 569278.81, '2026-04-24 16:39:30'),
(14, 2, 72, 'entrada', 511500.00, 569278.81, 1080778.81, '2026-04-24 16:39:32'),
(15, 32, 74, 'entrada', 45556.54, 3338.42, 48894.96, '2026-04-24 16:42:45'),
(16, 36, 74, 'salida', 45556.54, 53416.01, 7859.47, '2026-04-24 16:42:45'),
(17, 32, 75, 'entrada', 604443.46, 48894.96, 653338.42, '2026-04-24 16:42:48'),
(18, 32, 76, 'salida', 500000.00, 653338.42, 153338.42, '2026-04-24 16:42:56'),
(19, 34, 77, 'entrada', 1652.50, 44140.71, 45793.21, '2026-04-24 16:42:58'),
(20, 26, 77, 'entrada', 1652.50, 50000.00, 51652.50, '2026-04-24 16:42:58'),
(21, 34, 78, 'entrada', 211550.67, 45793.21, 257343.88, '2026-04-24 16:43:03'),
(22, 26, 78, 'entrada', 211550.67, 51652.50, 263203.17, '2026-04-24 16:43:03'),
(23, 52, 79, 'salida', 123000.00, 140000.00, 17000.00, '2026-04-24 16:43:20'),
(24, 39, 80, 'salida', 121155.00, 121155.00, 0.00, '2026-04-24 16:43:20'),
(25, 1, 81, 'salida', 1599.00, 90525.80, 88926.80, '2026-04-24 16:43:20'),
(26, 25, 82, 'salida', 246.00, 246.00, 0.00, '2026-04-24 16:43:20'),
(27, 2, 104, 'salida', 800.00, 42764.96, 41964.96, '2026-04-28 17:45:26'),
(28, 1, 105, 'salida', 800.00, 110596.10, 109796.10, '2026-04-28 17:45:26'),
(29, 2, 106, 'salida', 1100.00, 41964.96, 40864.96, '2026-04-28 17:46:32'),
(30, 1, 107, 'salida', 1100.00, 109796.10, 108696.10, '2026-04-28 17:46:32'),
(31, 2, 108, 'salida', 1570.00, 40864.96, 39294.96, '2026-04-28 17:47:24'),
(32, 1, 109, 'salida', 1570.00, 108696.10, 107126.10, '2026-04-28 17:47:24'),
(33, 36, 110, 'salida', 3500.00, 21874.27, 18374.27, '2026-04-28 17:48:58'),
(34, 1, 111, 'salida', 3500.00, 107126.10, 103626.10, '2026-04-28 17:48:58'),
(35, 2, 112, 'salida', 15400.00, 39294.96, 23894.96, '2026-04-28 17:51:58'),
(36, 1, 113, 'salida', 15400.00, 103626.10, 88226.10, '2026-04-28 17:51:58'),
(37, 2, 114, 'entrada', 15400.00, 23894.96, 39294.96, '2026-04-28 17:52:13'),
(38, 1, 115, 'entrada', 15400.00, 88226.10, 103626.10, '2026-04-28 17:52:13'),
(39, 36, 116, 'salida', 15400.00, 18374.27, 2974.27, '2026-04-28 17:52:43'),
(40, 1, 117, 'salida', 15400.00, 103626.10, 88226.10, '2026-04-28 17:52:43'),
(41, 52, 118, 'salida', 3000.00, 17000.00, 14000.00, '2026-04-28 17:53:40'),
(42, 1, 119, 'salida', 3000.00, 88226.10, 85226.10, '2026-04-28 17:53:40'),
(43, 52, 139, 'entrada', 111130.00, 120370.00, 231500.00, '2026-04-28 18:11:10'),
(44, 34, 139, 'salida', 111130.00, 144065.25, 32935.25, '2026-04-28 18:11:10'),
(45, 2, 153, 'salida', 1700.00, 398671.24, 396971.24, '2026-04-29 00:30:42'),
(46, 1, 154, 'salida', 1700.00, 102201.85, 100501.85, '2026-04-29 00:30:42'),
(47, 2, 159, 'salida', 500.00, 396971.24, 396471.24, '2026-04-29 00:37:17'),
(48, 1, 160, 'salida', 500.00, 105826.85, 105326.85, '2026-04-29 00:37:17'),
(49, 2, 161, 'entrada', 500.00, 396471.24, 396971.24, '2026-04-29 00:37:49'),
(50, 1, 162, 'entrada', 500.00, 105326.85, 105826.85, '2026-04-29 00:37:49'),
(51, 32, 163, 'salida', 266250.00, 287550.42, 21300.42, '2026-04-29 00:37:53'),
(52, 26, 164, 'salida', 258262.50, 258927.76, 665.26, '2026-04-29 00:37:53'),
(53, 1, 165, 'salida', 5325.00, 105826.85, 100501.85, '2026-04-29 00:37:53'),
(54, 24, 166, 'salida', 2662.50, 2662.50, 0.00, '2026-04-29 00:37:53'),
(55, 2, 167, 'salida', 500.00, 396971.24, 396471.24, '2026-04-29 00:39:41'),
(56, 1, 168, 'salida', 500.00, 100501.85, 100001.85, '2026-04-29 00:39:41'),
(57, 36, 177, 'entrada', 5329.00, 432.45, 5761.45, '2026-04-29 17:52:06'),
(58, 24, 177, 'entrada', 5329.00, -2662.50, 2666.50, '2026-04-29 17:52:06'),
(59, 36, 179, 'entrada', 2666.51, 3094.94, 5761.45, '2026-04-29 18:00:52'),
(60, 24, 179, 'entrada', 2666.51, -0.01, 2666.50, '2026-04-29 18:00:52'),
(61, 32, 187, 'entrada', 200000.00, 2045.42, 202045.42, '2026-04-29 18:38:33'),
(62, 2, 189, 'entrada', 170000.00, 238231.24, 408231.24, '2026-04-29 19:20:52'),
(63, 2, 190, 'entrada', 170000.00, 68231.24, 238231.24, '2026-04-29 20:36:34'),
(64, 32, 221, 'entrada', 92974.50, 114753.92, 207728.42, '2026-04-30 19:10:42'),
(65, 26, 221, 'entrada', 92974.50, 115853.77, 208828.27, '2026-04-30 19:10:42'),
(66, 2, 229, 'salida', 500.00, 344726.26, 344226.26, '2026-04-30 20:32:27'),
(67, 1, 230, 'salida', 500.00, 114074.90, 113574.90, '2026-04-30 20:32:27'),
(68, 2, 239, 'salida', 5000.00, 884990.12, 879990.12, '2026-04-30 23:25:32'),
(69, 1, 240, 'salida', 5000.00, 119888.48, 114888.48, '2026-04-30 23:25:32'),
(70, 2, 241, 'entrada', 5000.00, 879990.12, 884990.12, '2026-04-30 23:25:44'),
(71, 1, 242, 'entrada', 5000.00, 114888.48, 119888.48, '2026-04-30 23:25:44'),
(72, 34, 243, 'salida', 5000.00, 127859.39, 122859.39, '2026-04-30 23:26:25'),
(73, 1, 244, 'salida', 5000.00, 119888.48, 114888.48, '2026-04-30 23:26:25'),
(74, 2, 250, 'salida', 20000.00, 841079.15, 821079.15, '2026-05-01 00:38:37'),
(75, 1, 251, 'salida', 20000.00, 114888.48, 94888.48, '2026-05-01 00:38:37'),
(76, 2, 255, 'entrada', 20000.00, 821079.15, 841079.15, '2026-05-01 00:43:53'),
(77, 1, 256, 'entrada', 20000.00, 94888.48, 114888.48, '2026-05-01 00:43:53'),
(78, 2, 261, 'salida', 94811.73, 935890.88, 841079.15, '2026-05-01 00:45:35'),
(79, 1, 262, 'salida', 94811.73, 114887.94, 20076.21, '2026-05-01 00:45:35'),
(80, 2, 263, 'salida', 20000.00, 841079.15, 821079.15, '2026-05-01 00:55:05'),
(81, 1, 264, 'salida', 20000.00, 20076.21, 76.21, '2026-05-01 00:55:05'),
(82, 36, 287, 'entrada', 8650.50, 35361.12, 44011.62, '2026-05-05 16:28:30'),
(83, 26, 287, 'entrada', 8650.50, 665.26, 9315.76, '2026-05-05 16:28:30'),
(84, 2, 295, 'salida', 500.00, 659709.69, 659209.69, '2026-05-05 16:51:50'),
(85, 1, 296, 'salida', 500.00, 3703.19, 3203.19, '2026-05-05 16:51:50'),
(86, 36, 297, 'salida', 16000.00, 34135.35, 18135.35, '2026-05-05 19:32:47'),
(87, 1, 298, 'salida', 16000.00, 3203.19, -12796.81, '2026-05-05 19:32:47'),
(88, 2, 299, 'salida', 1600.00, 659209.69, 657609.69, '2026-05-05 19:56:58'),
(89, 1, 300, 'salida', 1600.00, -12796.81, -14396.81, '2026-05-05 19:56:58'),
(90, 36, 301, 'salida', 3600.00, 18135.35, 14535.35, '2026-05-05 19:57:52'),
(91, 1, 302, 'salida', 3600.00, -14396.81, -17996.81, '2026-05-05 19:57:52'),
(92, 2, 320, 'salida', 5435.00, 810109.69, 804674.69, '2026-05-06 00:44:16'),
(93, 1, 321, 'salida', 5435.00, -17519.25, -22954.25, '2026-05-06 00:44:16'),
(94, 36, 322, 'salida', 2000.00, 13243.30, 11243.30, '2026-05-06 00:45:33'),
(95, 36, 323, 'entrada', 2000.00, 11243.30, 13243.30, '2026-05-06 00:47:55'),
(96, 34, 324, 'salida', 5435.00, 10994.36, 5559.36, '2026-05-06 17:49:54'),
(97, 1, 325, 'salida', 5435.00, -17519.25, -22954.25, '2026-05-06 17:49:54'),
(98, 2, 351, 'salida', 2048971.40, 2829749.22, 780777.82, '2026-05-06 21:31:39'),
(99, 2, 354, 'entrada', 2829750.00, 0.60, 2829750.60, '2026-05-06 21:33:42'),
(100, 34, 356, 'salida', 2000.00, 213059.31, 211059.31, '2026-05-06 21:45:26'),
(101, 177, 388, 'entrada', 98.50, 0.00, 98.50, '2026-05-07 18:51:23'),
(102, 1, 389, 'entrada', 1.00, -7835.08, -7834.08, '2026-05-07 18:51:23'),
(103, 24, 390, 'entrada', 0.50, 0.00, 0.50, '2026-05-07 18:51:23'),
(104, 177, 392, 'salida', 98.50, 98.50, 0.00, '2026-05-07 18:51:46'),
(105, 1, 393, 'salida', 1.00, -7834.08, -7835.08, '2026-05-07 18:51:46'),
(106, 24, 394, 'salida', 0.50, 1.00, 0.50, '2026-05-07 18:51:46'),
(107, 24, 395, 'salida', 0.50, 0.50, 0.00, '2026-05-07 18:51:46'),
(108, 2, 401, 'salida', 500.00, 485000.00, 484500.00, '2026-05-07 20:44:38'),
(109, 2, 402, 'salida', 484500.00, 484500.00, 0.00, '2026-05-07 20:59:17'),
(110, 2, 406, 'salida', 15500.00, 460000.00, 444500.00, '2026-05-07 23:50:18'),
(111, 2, 407, 'salida', 300000.00, 444500.00, 144500.00, '2026-05-07 23:50:18'),
(112, 2, 408, 'salida', 144500.00, 144500.00, 0.00, '2026-05-07 23:50:40'),
(113, 36, 409, 'salida', 20000.00, 311874.48, 291874.48, '2026-05-07 23:52:25'),
(114, 36, 414, 'salida', 30000.00, 323344.48, 293344.48, '2026-05-08 01:01:35'),
(115, 181, 415, 'entrada', 494000.00, 0.00, 494000.00, '2026-05-08 16:57:59'),
(116, 1, 416, 'entrada', 5000.00, -7320.60, -2320.60, '2026-05-08 16:57:59'),
(117, 180, 417, 'entrada', 1000.00, 0.00, 1000.00, '2026-05-08 16:57:59'),
(118, 34, 418, 'entrada', 500000.00, 211059.31, 711059.31, '2026-05-08 16:57:59'),
(119, 34, 423, 'salida', 207500.00, 711059.31, 503559.31, '2026-05-08 17:42:25'),
(120, 2, 426, 'salida', 500.00, 499300.00, 498800.00, '2026-05-08 23:20:51'),
(121, 2, 427, 'salida', 3800.00, 498800.00, 495000.00, '2026-05-08 23:24:39'),
(122, 2, 430, 'salida', 495000.00, 1139000.00, 644000.00, '2026-05-09 01:47:40'),
(123, 2, 431, 'salida', 1880.00, 644000.00, 642120.00, '2026-05-09 01:48:30'),
(124, 2, 432, 'entrada', 1880.00, 642120.00, 644000.00, '2026-05-09 01:48:43'),
(125, 34, 433, 'salida', 1880.00, 209559.31, 207679.31, '2026-05-09 01:49:05'),
(126, 36, 438, 'salida', 50000.00, 245192.79, 195192.79, '2026-05-09 01:58:51'),
(127, 2, 439, 'salida', 4300.00, 644000.00, 639700.00, '2026-05-09 02:08:10'),
(128, 2, 440, 'salida', 495000.00, 639700.00, 144700.00, '2026-05-09 02:08:10'),
(129, 2, 441, 'salida', 144700.00, 144700.00, 0.00, '2026-05-09 02:08:30'),
(130, 36, 442, 'salida', 41000.00, 195192.79, 154192.79, '2026-05-11 17:55:07'),
(131, 34, 443, 'salida', 600.00, 207679.31, 207079.31, '2026-05-11 18:15:03'),
(132, 34, 444, 'entrada', 600.00, 207079.31, 207679.31, '2026-05-11 18:21:27'),
(133, 34, 445, 'salida', 3600.00, 207679.31, 204079.31, '2026-05-11 18:23:00'),
(134, 34, 446, 'salida', 1000.00, 204079.31, 203079.31, '2026-05-11 18:23:17'),
(135, 36, 447, 'salida', 79800.00, 154192.79, 74392.79, '2026-05-11 19:29:09'),
(136, 34, 448, 'salida', 50200.00, 203079.31, 152879.31, '2026-05-11 19:29:26'),
(137, 1, 449, 'salida', 500.00, -1330.27, -1830.27, '2026-05-11 20:52:37'),
(138, 1, 450, 'salida', 500.00, -1830.27, -2330.27, '2026-05-11 20:52:37'),
(139, 1, 451, 'salida', 3800.00, -2330.27, -6130.27, '2026-05-11 20:52:37'),
(140, 1, 452, 'salida', 1880.00, -6130.27, -8010.27, '2026-05-11 20:52:37'),
(141, 2, 459, 'salida', 103300.00, 241500.00, 138200.00, '2026-05-11 22:27:05'),
(142, 2, 460, 'salida', 108349.40, 138200.00, 29850.60, '2026-05-11 22:27:51'),
(143, 2, 461, 'salida', 29850.60, 29850.60, 0.00, '2026-05-11 22:28:18'),
(144, 34, 479, 'salida', 2500.00, 163413.63, 160913.63, '2026-05-12 20:43:08'),
(145, 183, 481, 'entrada', 238590.89, 0.00, 238590.89, '2026-05-13 01:04:33'),
(146, 1, 482, 'entrada', 1687.01, -6580.10, -4893.09, '2026-05-13 01:04:33'),
(147, 182, 483, 'entrada', 723.00, 0.00, 723.00, '2026-05-13 01:04:33'),
(148, 32, 484, 'entrada', 241000.90, 7333.11, 248334.01, '2026-05-13 01:04:33'),
(149, 176, 485, 'entrada', 2966.82, 0.00, 2966.82, '2026-05-13 01:05:25'),
(150, 1, 486, 'entrada', 30.12, -4893.09, -4862.97, '2026-05-13 01:05:25'),
(151, 175, 487, 'entrada', 15.06, 0.00, 15.06, '2026-05-13 01:05:25'),
(152, 32, 488, 'entrada', 3012.00, 248334.01, 251346.01, '2026-05-13 01:05:25'),
(153, 36, 490, 'salida', 1000.00, 54080.07, 53080.07, '2026-05-13 20:23:42'),
(154, 1, 491, 'salida', 1000.00, -4862.97, -5862.97, '2026-05-13 20:23:42'),
(155, 2, 495, 'salida', 238590.89, 844727.00, 606136.11, '2026-05-13 20:30:01'),
(156, 34, 505, 'salida', 651.11, 455413.63, 454762.52, '2026-05-13 21:07:26'),
(157, 1, 506, 'salida', 651.11, -1631.65, -2282.76, '2026-05-13 21:07:26'),
(158, 2, 509, 'salida', 442500.00, 1155916.11, 713416.11, '2026-05-14 16:51:11'),
(159, 185, 511, 'entrada', 79200.00, 0.00, 79200.00, '2026-05-14 19:01:16'),
(160, 1, 512, 'entrada', 560.00, -2282.76, -1722.76, '2026-05-14 19:01:16'),
(161, 184, 513, 'entrada', 240.00, 0.00, 240.00, '2026-05-14 19:01:16'),
(162, 32, 514, 'entrada', 80000.00, 251346.01, 331346.01, '2026-05-14 19:01:16'),
(163, 2, 515, 'salida', 79200.00, 1199416.11, 1120216.11, '2026-05-14 19:02:16'),
(164, 36, 538, 'salida', 74779.00, 82062.82, 7283.82, '2026-05-15 00:38:07'),
(165, 34, 539, 'salida', 5000.00, 454762.52, 449762.52, '2026-05-15 00:39:09'),
(166, 36, 562, 'salida', 4629.00, 216369.23, 211740.23, '2026-05-15 19:23:57'),
(167, 36, 563, 'salida', 30.00, 211740.23, 211710.23, '2026-05-15 19:24:27'),
(168, 1, 564, 'salida', 30.00, 15644.11, 15614.11, '2026-05-15 19:24:27'),
(169, 34, 568, 'salida', 81.32, 293562.52, 293481.20, '2026-05-16 00:42:22'),
(170, 1, 569, 'salida', 81.32, 15614.11, 15532.79, '2026-05-16 00:42:22'),
(171, 34, 570, 'entrada', 81.32, 293481.20, 293562.52, '2026-05-16 00:42:29'),
(172, 1, 571, 'entrada', 81.32, 15532.79, 15614.11, '2026-05-16 00:42:29'),
(173, 34, 572, 'salida', 81.20, 293562.52, 293481.32, '2026-05-16 00:42:43'),
(174, 1, 573, 'salida', 81.20, 15614.11, 15532.91, '2026-05-16 00:42:43'),
(175, 2, 574, 'salida', 500.00, 692960.63, 692460.63, '2026-05-16 00:43:26'),
(176, 1, 575, 'salida', 500.00, 15532.91, 15032.91, '2026-05-16 00:43:26'),
(177, 36, 580, 'salida', 220844.00, 364860.44, 144016.44, '2026-05-16 00:57:05'),
(178, 2, 582, 'salida', 348460.63, 348460.63, 0.00, '2026-05-16 00:59:57'),
(179, 183, 583, 'entrada', 105217.50, 0.00, 105217.50, '2026-05-16 01:05:28'),
(180, 1, 584, 'entrada', 743.96, 16998.11, 17742.07, '2026-05-16 01:05:28'),
(181, 182, 585, 'entrada', 318.84, 0.00, 318.84, '2026-05-16 01:05:28'),
(182, 32, 586, 'entrada', 106280.30, 1346.01, 107626.31, '2026-05-16 01:05:28'),
(183, 36, 588, 'salida', 920.00, 144016.44, 143096.44, '2026-05-18 17:33:45'),
(184, 181, 594, 'entrada', 158400.00, 0.00, 158400.00, '2026-05-18 20:34:14'),
(185, 1, 595, 'entrada', 1280.00, 21479.75, 22759.75, '2026-05-18 20:34:14'),
(186, 180, 596, 'entrada', 320.00, 1000.00, 1320.00, '2026-05-18 20:34:14'),
(187, 34, 597, 'entrada', 160000.00, 293481.32, 453481.32, '2026-05-18 20:34:14'),
(188, 2, 601, 'salida', 308850.00, 1000920.00, 692070.00, '2026-05-18 22:59:05'),
(189, 2, 602, 'salida', 200000.00, 692070.00, 492070.00, '2026-05-18 22:59:05'),
(190, 2, 603, 'salida', 486000.00, 492070.00, 6070.00, '2026-05-18 22:59:05'),
(191, 2, 604, 'salida', 5150.00, 6070.00, 920.00, '2026-05-18 22:59:05'),
(192, 34, 606, 'salida', 5000.00, 453481.32, 448481.32, '2026-05-18 23:01:14'),
(193, 2, 608, 'salida', 105217.50, 319420.00, 214202.50, '2026-05-18 23:11:36'),
(194, 176, 618, 'entrada', 10822.19, 2966.82, 13789.01, '2026-05-18 23:36:28'),
(195, 1, 619, 'entrada', 109.87, 27356.35, 27466.22, '2026-05-18 23:36:28'),
(196, 175, 620, 'entrada', 54.94, 15.06, 70.00, '2026-05-18 23:36:28'),
(197, 32, 621, 'entrada', 10987.00, 332154.36, 343141.36, '2026-05-18 23:36:28'),
(198, 36, 623, 'salida', 329156.00, 1042820.44, 713664.44, '2026-05-18 23:39:44'),
(199, 2, 624, 'salida', 43057.69, 212882.50, 169824.81, '2026-05-18 23:46:02'),
(200, 1, 625, 'salida', 43057.69, 27466.22, -15591.47, '2026-05-18 23:46:02'),
(201, 186, 627, 'entrada', 700000.10, 0.00, 700000.10, '2026-05-19 17:03:50'),
(202, 1, 628, 'entrada', 7106.60, -15591.47, -8484.87, '2026-05-19 17:03:50'),
(203, 24, 629, 'entrada', 3553.30, 0.00, 3553.30, '2026-05-19 17:03:50'),
(204, 32, 630, 'entrada', 710660.00, 3141.36, 713801.36, '2026-05-19 17:03:50'),
(205, 34, 634, 'salida', 330.40, 703081.32, 702750.92, '2026-05-19 18:14:04'),
(206, 1, 635, 'salida', 330.40, -8484.87, -8815.27, '2026-05-19 18:14:04'),
(207, 34, 636, 'salida', 81.20, 702750.92, 702669.72, '2026-05-19 18:48:29'),
(208, 1, 637, 'salida', 81.20, -8815.27, -8896.47, '2026-05-19 18:48:29'),
(209, 36, 641, 'salida', 174150.00, 674261.14, 500111.14, '2026-05-19 22:03:23'),
(210, 36, 642, 'salida', 81000.00, 500111.14, 419111.14, '2026-05-19 22:03:23'),
(211, 36, 643, 'salida', 100000.00, 419111.14, 319111.14, '2026-05-19 22:03:23'),
(212, 36, 644, 'salida', 144850.00, 319111.14, 174261.14, '2026-05-19 22:03:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario_monedas`
--

CREATE TABLE `inventario_monedas` (
  `id` bigint(20) NOT NULL,
  `moneda` varchar(10) NOT NULL,
  `cantidad_original` decimal(20,8) NOT NULL,
  `cantidad_restante` decimal(20,8) NOT NULL,
  `paridad` decimal(18,8) NOT NULL COMMENT 'Precio de compra por unidad',
  `cuenta_id` int(11) NOT NULL,
  `movimiento_id` bigint(20) NOT NULL COMMENT 'Referencia al movimiento de compra',
  `fecha` datetime NOT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inventario_monedas`
--

INSERT INTO `inventario_monedas` (`id`, `moneda`, `cantidad_original`, `cantidad_restante`, `paridad`, `cuenta_id`, `movimiento_id`, `fecha`, `activo`) VALUES
(1, 'USDT', 14487.37000000, 0.00000000, 17.15000000, 51, 2, '2026-04-22 00:00:00', 1),
(2, 'USDT', 20000.00000000, 20000.00000000, 17.00000000, 51, 30, '2026-04-23 00:00:00', 0),
(3, 'USDT', 20000.00000000, 20000.00000000, 17.19980000, 51, 32, '2026-04-23 00:00:00', 0),
(4, 'USDT', 20000.00000000, 0.00000000, 17.20000000, 51, 34, '2026-04-23 00:00:00', 1),
(5, 'USDT', 34614.79000000, 0.00000000, 17.46200000, 51, 42, '2026-04-23 00:00:00', 1),
(6, 'USDT', 126.00000000, 0.00000000, 17.20000000, 51, 100, '2026-04-23 00:00:00', 1),
(7, 'USDT', 11369.99000000, 0.00000000, 17.20000000, 51, 133, '2026-04-28 00:00:00', 1),
(8, 'USDT', 5329.99000000, 0.00000000, 17.47010000, 51, 135, '2026-04-28 00:00:00', 1),
(9, 'USDT', 700.00000000, 0.00000000, 17.20000000, 51, 151, '2026-04-28 00:00:00', 1),
(10, 'USDT', 13880.68000000, 13880.68000000, 17.61210000, 51, 196, '2026-04-29 00:00:00', 0),
(11, 'USDT', 13880.68000000, 0.00000000, 17.61210000, 51, 198, '2026-04-29 00:00:00', 1),
(12, 'USDT', 50827.14000000, 0.00000000, 17.57410000, 51, 231, '2026-04-30 00:00:00', 1),
(13, 'USDT', 2594.65000000, 0.00000000, 17.36050000, 51, 333, '2026-05-06 00:00:00', 1),
(14, 'USDT', 165000.00000000, 165000.00000000, 17.15000000, 51, 346, '2026-05-06 00:00:00', 0),
(15, 'USDT', 165000.00000000, 0.00000000, 17.15000000, 51, 348, '2026-05-06 00:00:00', 1),
(16, 'USDT', 1000.00000000, 0.00000000, 17.10000000, 51, 463, '2026-05-12 00:00:00', 1),
(17, 'USDT', 285.00000000, 0.00000000, 17.10000000, 51, 470, '2026-05-12 00:00:00', 1),
(18, 'USDT', 4345.00000000, 0.00000000, 17.10000000, 51, 492, '2026-05-13 00:00:00', 1),
(19, 'USDT', 100757.00000000, 99540.95270000, 17.20000000, 51, 522, '2026-05-14 00:00:00', 1),
(20, 'USDT', 16573.27000000, 16573.27000000, 17.29007300, 51, 525, '2026-05-14 00:00:00', 1),
(21, 'USDT', 6000.00000000, 6000.00000000, 17.29020200, 51, 531, '2026-05-14 00:00:00', 1),
(22, 'USDT', 20000.00000000, 20000.00000000, 17.20000000, 51, 551, '2026-05-15 00:00:00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos`
--

CREATE TABLE `movimientos` (
  `id` bigint(20) NOT NULL,
  `grupo_id` bigint(20) DEFAULT NULL COMMENT 'Agrupa movimientos de una misma operación',
  `fecha` date NOT NULL,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  `servicio_id` int(11) NOT NULL,
  `tipo` enum('entrada','salida','traspaso','comision','ajuste') NOT NULL,
  `moneda` enum('MXN','USD','USDT') NOT NULL,
  `monto` decimal(18,6) NOT NULL,
  `cuenta_origen_id` int(11) DEFAULT NULL,
  `cuenta_destino_id` int(11) DEFAULT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `promotor_id` int(11) DEFAULT NULL,
  `proveedor_id` int(11) DEFAULT NULL,
  `prestamista_id` int(11) DEFAULT NULL COMMENT 'Si es pago/préstamo de prestamista',
  `gasto_id` int(11) DEFAULT NULL COMMENT 'Si es un gasto registrado',
  `referencia_id` bigint(20) DEFAULT NULL,
  `referencia_tipo` varchar(50) DEFAULT NULL,
  `beneficiario` varchar(150) DEFAULT NULL COMMENT 'Nombre a quien se le presta dinero',
  `paridad` decimal(18,6) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL COMMENT 'Usuario que registró el movimiento',
  `activo` tinyint(4) DEFAULT 1,
  `estado` enum('activo','anulado','corregido') NOT NULL DEFAULT 'activo' COMMENT 'Estado del movimiento',
  `es_reverso` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Si es un movimiento de reverso',
  `movimiento_original_id` bigint(20) DEFAULT NULL COMMENT 'ID del movimiento que corrige/anula',
  `motivo_correccion` text DEFAULT NULL COMMENT 'Motivo de la anulación/corrección',
  `corregido_por_usuario_id` int(11) DEFAULT NULL COMMENT 'Usuario que hizo la corrección',
  `corregido_en` timestamp NULL DEFAULT NULL COMMENT 'Fecha/hora de la corrección'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `movimientos`
--

INSERT INTO `movimientos` (`id`, `grupo_id`, `fecha`, `creado_en`, `servicio_id`, `tipo`, `moneda`, `monto`, `cuenta_origen_id`, `cuenta_destino_id`, `cliente_id`, `promotor_id`, `proveedor_id`, `prestamista_id`, `gasto_id`, `referencia_id`, `referencia_tipo`, `beneficiario`, `paridad`, `descripcion`, `usuario_id`, `activo`, `estado`, `es_reverso`, `movimiento_original_id`, `motivo_correccion`, `corregido_por_usuario_id`, `corregido_en`) VALUES
(1, 1776883595383, '2026-04-22', '2026-04-22 18:46:35', 10, 'entrada', 'MXN', 511500.000000, NULL, 2, NULL, NULL, NULL, 4, NULL, NULL, NULL, NULL, NULL, 'Préstamo de Hyperion: ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(2, 1776883710957, '2026-04-22', '2026-04-22 18:48:30', 6, 'entrada', 'USDT', 14487.370000, NULL, 51, NULL, NULL, 8, NULL, NULL, NULL, NULL, NULL, 17.150000, 'Compra de 14487.37 USDT a TC 17.15', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(3, 1776883710957, '2026-04-22', '2026-04-22 18:48:30', 6, 'salida', 'MXN', 248458.395500, 2, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, NULL, 17.150000, 'Pago de contado: $248458.3955 MXN por 14487.37 USDT (TC: 17.15)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(4, 1776884213295, '2026-04-22', '2026-04-22 18:56:53', 14, 'salida', 'MXN', 34823.150000, 2, NULL, NULL, NULL, NULL, 4, NULL, NULL, NULL, NULL, NULL, 'Pago de préstamo a Hyperion', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(5, 1776884329363, '2026-04-22', '2026-04-22 18:58:49', 1, 'entrada', 'MXN', 213000.000000, NULL, 32, 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-22 18:59:40'),
(6, 1776884329363, '2026-04-22', '2026-04-22 18:58:49', 1, 'entrada', 'MXN', 209805.000000, NULL, 39, 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-22 18:59:40'),
(7, 1776884329363, '2026-04-22', '2026-04-22 18:58:49', 1, 'comision', 'MXN', 2769.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-22 18:59:40'),
(8, 1776884329363, '2026-04-22', '2026-04-22 18:58:49', 1, 'comision', 'MXN', 426.000000, NULL, 25, NULL, 7, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-22 18:59:40'),
(9, 1776884329364, '2026-04-22', '2026-04-22 18:59:40', 1, 'ajuste', 'MXN', -213000.000000, 32, NULL, 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1776884329363 - Venta de Efectivo - dinero recibido del cliente', 2, 1, 'activo', 1, 5, 'Reversión del último movimiento', NULL, NULL),
(10, 1776884329364, '2026-04-22', '2026-04-22 18:59:40', 1, 'ajuste', 'MXN', -209805.000000, 39, NULL, 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1776884329363 - Venta de Efectivo - efectivo recibido neto', 2, 1, 'activo', 1, 6, 'Reversión del último movimiento', NULL, NULL),
(11, 1776884329364, '2026-04-22', '2026-04-22 18:59:40', 1, 'ajuste', 'MXN', -2769.000000, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1776884329363 - Comisión Amiri - Venta de Efectivo', 2, 1, 'activo', 1, 7, 'Reversión del último movimiento', NULL, NULL),
(12, 1776884329364, '2026-04-22', '2026-04-22 18:59:40', 1, 'ajuste', 'MXN', -426.000000, 25, NULL, NULL, 7, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1776884329363 - Comisión Promotor - Venta de Efectivo', 2, 1, 'activo', 1, 8, 'Reversión del último movimiento', NULL, NULL),
(13, 1776884982298, '2026-04-22', '2026-04-22 19:09:42', 1, 'entrada', 'MXN', 213000.000000, NULL, 32, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(14, 1776884982298, '2026-04-22', '2026-04-22 19:09:42', 1, 'entrada', 'MXN', 211551.600000, NULL, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(15, 1776884982298, '2026-04-22', '2026-04-22 19:09:42', 1, 'comision', 'MXN', 1448.400000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(16, 1776885034090, '2026-04-22', '2026-04-22 19:10:34', 13, 'salida', 'MXN', 0.930000, 2, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 2', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(17, 1776885250825, '2026-04-22', '2026-04-22 19:14:10', 4, 'traspaso', 'MXN', 228217.520000, 2, 34, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(18, 1776885308453, '2026-04-22', '2026-04-22 19:15:08', 4, 'traspaso', 'MXN', 213000.000000, 32, 34, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(19, 1776885542338, '2026-04-22', '2026-04-22 19:19:02', 1, 'entrada', 'MXN', 83633.400000, NULL, 32, 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(20, 1776885542338, '2026-04-22', '2026-04-22 19:19:02', 1, 'entrada', 'MXN', 0.000000, NULL, 50, 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(21, 1776885542338, '2026-04-22', '2026-04-22 19:19:02', 1, 'comision', 'MXN', 83633.400000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(22, 1776885618955, '2026-04-22', '2026-04-22 19:20:18', 4, 'traspaso', 'MXN', 21126.480000, 32, 34, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(23, 1776885644097, '2026-04-22', '2026-04-22 19:20:44', 10, 'entrada', 'MXN', 511500.000000, NULL, 32, NULL, NULL, NULL, 4, NULL, NULL, NULL, NULL, NULL, 'Préstamo de Hyperion: ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(24, 1776885798719, '2026-04-22', '2026-04-22 19:23:18', 4, 'traspaso', 'MXN', 473978.500000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-22 19:23:31'),
(25, 1776885798720, '2026-04-22', '2026-04-22 19:23:31', 4, 'ajuste', 'MXN', -473978.500000, 2, 32, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1776885798719 - Cambio de flujo - transferencia entre cuentas', 2, 1, 'activo', 1, 24, 'Reversión del último movimiento', NULL, NULL),
(26, 1776885860107, '2026-04-22', '2026-04-22 19:24:20', 4, 'traspaso', 'MXN', 473918.500000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(27, 1776886050437, '2026-04-22', '2026-04-22 19:27:30', 4, 'traspaso', 'MXN', 8391.970000, 2, 36, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(28, 1776886330028, '2026-04-22', '2026-04-22 19:32:10', 14, 'salida', 'MXN', 448500.000000, 2, NULL, NULL, NULL, NULL, 4, NULL, NULL, NULL, NULL, NULL, 'Pago de préstamo a Hyperion', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(29, 1776887062562, '2026-04-22', '2026-04-22 19:44:22', 4, 'traspaso', 'MXN', 17000.000000, 2, 52, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(30, 1776963672250, '2026-04-23', '2026-04-23 17:01:12', 6, 'entrada', 'USDT', 20000.000000, NULL, 51, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 17.000000, 'Compra de 20000 USDT a TC 17', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(31, 1776963672250, '2026-04-23', '2026-04-23 17:01:12', 6, 'salida', 'USDT', 20000.000000, NULL, 19, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 17.000000, 'Adeudo a proveedor: 20000 USDT (TC: 17)', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(32, 1776963707541, '2026-04-23', '2026-04-23 17:01:47', 6, 'entrada', 'USDT', 20000.000000, NULL, 51, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 17.199800, 'Compra de 20000 USDT a TC 17.1998', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(33, 1776963707541, '2026-04-23', '2026-04-23 17:01:47', 6, 'salida', 'USDT', 20000.000000, NULL, 19, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 17.199800, 'Adeudo a proveedor: 20000 USDT (TC: 17.1998)', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(34, 1776963745371, '2026-04-23', '2026-04-23 17:02:25', 6, 'entrada', 'USDT', 20000.000000, NULL, 51, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 17.200000, 'Compra de 20000 USDT a TC 17.2', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(35, 1776963745371, '2026-04-23', '2026-04-23 17:02:25', 6, 'salida', 'USDT', 20000.000000, NULL, 19, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 17.200000, 'Adeudo a proveedor: 20000 USDT (TC: 17.2)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(36, 1776964349348, '2026-04-23', '2026-04-23 17:12:29', 11, 'salida', 'MXN', 200000.000000, 34, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 17.200000, 'Pago a proveedor Emilio Agraz — 11627.90697674 USDT (TC: 17.2)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(37, 1776987855522, '2026-04-23', '2026-04-23 23:44:15', 11, 'salida', 'MXN', 144000.000000, 34, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 17.200006, 'Pago a proveedor Emilio Agraz — 8372.09000054 USDT (TC: 17.20000621)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(38, 1776987941652, '2026-04-23', '2026-04-23 23:45:41', 3, 'entrada', 'MXN', 138999.875000, NULL, 34, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.500000, 'Venta de 7942.85 USDT a TC 17.5', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(39, 1776987941652, '2026-04-23', '2026-04-23 23:45:41', 3, 'salida', 'USDT', 7942.850000, 51, NULL, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.500000, 'Salida de 7942.85 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(40, 1776987941652, '2026-04-23', '2026-04-23 23:45:41', 3, 'comision', 'MXN', 2779.997500, NULL, 1, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.500000, 'Utilidad FIFO por venta de 7942.85 USDT a TC 17.5', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(41, 1776987941652, '2026-04-23', '2026-04-23 23:45:41', 3, 'entrada', 'USDT', 7942.850000, NULL, 31, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.500000, 'Registro venta: 7942.85 USDT a TC 17.5 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(42, 1776988029591, '2026-04-23', '2026-04-23 23:47:09', 6, 'entrada', 'USDT', 34614.790000, NULL, 51, NULL, NULL, 8, NULL, NULL, NULL, NULL, NULL, 17.462000, 'Compra de 34614.79 USDT a TC 17.462', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(43, 1776988029591, '2026-04-23', '2026-04-23 23:47:09', 6, 'salida', 'USDT', 34614.790000, NULL, 44, NULL, NULL, 8, NULL, NULL, NULL, NULL, NULL, 17.462000, 'Adeudo a proveedor: 34614.79 USDT (TC: 17.462)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(44, 1776988094308, '2026-04-23', '2026-04-23 23:48:14', 1, 'entrada', 'MXN', 53250.000000, NULL, 32, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(45, 1776988094308, '2026-04-23', '2026-04-23 23:48:14', 1, 'entrada', 'MXN', 51652.500000, NULL, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(46, 1776988094308, '2026-04-23', '2026-04-23 23:48:14', 1, 'comision', 'MXN', 1065.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(47, 1776988094308, '2026-04-23', '2026-04-23 23:48:14', 1, 'comision', 'MXN', 532.500000, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(48, 1776988115114, '2026-04-23', '2026-04-23 23:48:35', 15, 'salida', 'MXN', 532.500000, 36, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(49, 1776988159039, '2026-04-23', '2026-04-23 23:49:19', 1, 'entrada', 'MXN', 123000.000000, NULL, 52, 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-24 16:43:20'),
(50, 1776988159039, '2026-04-23', '2026-04-23 23:49:19', 1, 'entrada', 'MXN', 121155.000000, NULL, 39, 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-24 16:43:20'),
(51, 1776988159039, '2026-04-23', '2026-04-23 23:49:19', 1, 'comision', 'MXN', 1599.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-24 16:43:20'),
(52, 1776988159039, '2026-04-23', '2026-04-23 23:49:19', 1, 'comision', 'MXN', 246.000000, NULL, 25, NULL, 7, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-24 16:43:20'),
(53, 1776988304286, '2026-04-23', '2026-04-23 23:51:44', 13, 'salida', 'MXN', 211550.670000, 34, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 2', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-24 16:43:03'),
(54, 1776988334587, '2026-04-23', '2026-04-23 23:52:14', 13, 'salida', 'MXN', 1652.500000, 34, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 4', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-24 16:42:58'),
(55, 1776988408642, '2026-04-23', '2026-04-23 23:53:28', 10, 'entrada', 'MXN', 500000.000000, NULL, 32, NULL, NULL, NULL, 4, NULL, NULL, NULL, NULL, NULL, 'Préstamo de Hyperion: ', 2, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-24 16:42:56'),
(56, 1776988430308, '2026-04-23', '2026-04-23 23:53:50', 11, 'salida', 'MXN', 604443.460000, 32, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, NULL, 17.462000, 'Pago a proveedor Binance — 34614.79000775 USDT (TC: 17.46199991)', 2, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-24 16:42:48'),
(57, 1777009263075, '2026-04-23', '2026-04-24 05:41:03', 3, 'entrada', 'MXN', 1080752.282941, NULL, 2, 133, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.671100, 'Venta de 61159.31 USDT a TC 17.6711', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(58, 1777009263075, '2026-04-23', '2026-04-24 05:41:03', 3, 'salida', 'USDT', 61159.310000, 51, NULL, 133, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.671100, 'Salida de 61159.31 USDT del wallet por venta', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(59, 1777009263075, '2026-04-23', '2026-04-24 05:41:03', 3, 'comision', 'MXN', 20070.301961, NULL, 1, 133, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.671100, 'Utilidad FIFO por venta de 61159.31 USDT a TC 17.6711', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(60, 1777009263075, '2026-04-23', '2026-04-24 05:41:03', 3, 'entrada', 'USDT', 61159.310000, NULL, 172, 133, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.671100, 'Registro venta: 61159.31 USDT a TC 17.6711 al cliente', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(61, 1777009328548, '2026-04-23', '2026-04-24 05:42:08', 14, 'salida', 'MXN', 511500.000000, 2, NULL, NULL, NULL, NULL, 4, NULL, NULL, NULL, NULL, NULL, 'Pago de préstamo a Hyperion', 2, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-24 05:42:25'),
(62, 1777009328549, '2026-04-23', '2026-04-24 05:42:25', 14, 'ajuste', 'MXN', -511500.000000, NULL, 2, NULL, NULL, NULL, 4, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1777009328548 - Pago de préstamo a Hyperion', 2, 1, 'activo', 1, 61, 'Reversión del último movimiento', NULL, NULL),
(63, 1777009370208, '2026-04-23', '2026-04-24 05:42:50', 14, 'salida', 'MXN', 511500.000000, 2, NULL, NULL, NULL, NULL, 4, NULL, NULL, NULL, NULL, NULL, 'Pago de préstamo a Hyperion', 2, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-24 16:39:32'),
(64, 1777009382428, '2026-04-22', '2026-04-24 05:43:02', 14, 'salida', 'MXN', 28176.850000, 2, NULL, NULL, NULL, NULL, 4, NULL, NULL, NULL, NULL, NULL, 'Pago de préstamo a Hyperion', 2, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-24 16:39:30'),
(65, 1777009394191, '2026-04-23', '2026-04-24 05:43:14', 14, 'salida', 'MXN', 500000.000000, 2, NULL, NULL, NULL, NULL, 4, NULL, NULL, NULL, NULL, NULL, 'Pago de préstamo a Hyperion', 2, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-24 16:39:26'),
(66, 1777009476784, '2026-04-23', '2026-04-24 05:44:36', 12, 'salida', 'MXN', 1570.000000, 2, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, 'Gasto: Tóner Impresora - ', 2, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-24 16:39:22'),
(67, 1777009476784, '2026-04-23', '2026-04-24 05:44:36', 12, 'salida', 'MXN', 1570.000000, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Tóner Impresora - ', 2, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-24 16:39:22'),
(68, 1777009476785, '2026-04-24', '2026-04-24 16:39:22', 12, 'ajuste', 'MXN', -1570.000000, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1777009476784 - Gasto: Tóner Impresora - ', 2, 1, 'activo', 1, 66, 'Reversión del último movimiento', NULL, NULL),
(69, 1777009476785, '2026-04-24', '2026-04-24 16:39:22', 12, 'ajuste', 'MXN', -1570.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1777009476784 - Gasto (desde Comisiones AMIRI): Tóner Impresora - ', 2, 1, 'activo', 1, 67, 'Reversión del último movimiento', NULL, NULL),
(70, 1777009476786, '2026-04-24', '2026-04-24 16:39:26', 14, 'ajuste', 'MXN', -500000.000000, NULL, 2, NULL, NULL, NULL, 4, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1777009394191 - Pago de préstamo a Hyperion', 2, 1, 'activo', 1, 65, 'Reversión del último movimiento', NULL, NULL),
(71, 1777009476787, '2026-04-24', '2026-04-24 16:39:30', 14, 'ajuste', 'MXN', -28176.850000, NULL, 2, NULL, NULL, NULL, 4, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1777009382428 - Pago de préstamo a Hyperion', 2, 1, 'activo', 1, 64, 'Reversión del último movimiento', NULL, NULL),
(72, 1777009476788, '2026-04-24', '2026-04-24 16:39:32', 14, 'ajuste', 'MXN', -511500.000000, NULL, 2, NULL, NULL, NULL, 4, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1777009370208 - Pago de préstamo a Hyperion', 2, 1, 'activo', 1, 63, 'Reversión del último movimiento', NULL, NULL),
(73, 1777048945011, '2026-04-24', '2026-04-24 16:42:25', 4, 'traspaso', 'MXN', 45556.540000, 32, 36, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-24 16:42:45'),
(74, 1777048945012, '2026-04-24', '2026-04-24 16:42:45', 4, 'ajuste', 'MXN', -45556.540000, 36, 32, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1777048945011 - Cambio de flujo - transferencia entre cuentas', 2, 1, 'activo', 1, 73, 'Reversión del último movimiento', NULL, NULL),
(75, 1777048945013, '2026-04-24', '2026-04-24 16:42:48', 11, 'ajuste', 'MXN', -604443.460000, NULL, 32, NULL, NULL, 8, NULL, NULL, NULL, NULL, NULL, 17.462000, 'REVERSO: Anulación de grupo #1776988430308 - Pago a proveedor Binance — 34614.79000775 USDT (TC: 17.46199991)', 2, 1, 'activo', 1, 56, 'Reversión del último movimiento', NULL, NULL),
(76, 1777048945014, '2026-04-24', '2026-04-24 16:42:56', 10, 'ajuste', 'MXN', -500000.000000, 32, NULL, NULL, NULL, NULL, 4, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1776988408642 - Préstamo de Hyperion: ', 2, 1, 'activo', 1, 55, 'Reversión del último movimiento', NULL, NULL),
(77, 1777048945015, '2026-04-24', '2026-04-24 16:42:58', 13, 'ajuste', 'MXN', -1652.500000, 26, 34, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1776988334587 - Pago de venta efectivo ID 4', 2, 1, 'activo', 1, 54, 'Reversión del último movimiento', NULL, NULL),
(78, 1777048945016, '2026-04-24', '2026-04-24 16:43:03', 13, 'ajuste', 'MXN', -211550.670000, 26, 34, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1776988304286 - Pago de venta efectivo ID 2', 2, 1, 'activo', 1, 53, 'Reversión del último movimiento', NULL, NULL),
(79, 1777048945017, '2026-04-24', '2026-04-24 16:43:20', 1, 'ajuste', 'MXN', -123000.000000, 52, NULL, 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1776988159039 - Venta de Efectivo - dinero recibido del cliente', 2, 1, 'activo', 1, 49, 'Reversión del último movimiento', NULL, NULL),
(80, 1777048945017, '2026-04-24', '2026-04-24 16:43:20', 1, 'ajuste', 'MXN', -121155.000000, 39, NULL, 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1776988159039 - Venta de Efectivo - efectivo recibido neto', 2, 1, 'activo', 1, 50, 'Reversión del último movimiento', NULL, NULL),
(81, 1777048945017, '2026-04-24', '2026-04-24 16:43:20', 1, 'ajuste', 'MXN', -1599.000000, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1776988159039 - Comisión Amiri - Venta de Efectivo', 2, 1, 'activo', 1, 51, 'Reversión del último movimiento', NULL, NULL),
(82, 1777048945017, '2026-04-24', '2026-04-24 16:43:20', 1, 'ajuste', 'MXN', -246.000000, 25, NULL, NULL, 7, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1776988159039 - Comisión Promotor - Venta de Efectivo', 2, 1, 'activo', 1, 52, 'Reversión del último movimiento', NULL, NULL),
(83, 1777397705716, '2026-04-23', '2026-04-28 17:35:05', 10, 'entrada', 'MXN', 361000.000000, NULL, 34, NULL, NULL, NULL, 4, NULL, 4, NULL, NULL, NULL, 'Préstamo de Hyperion: ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(84, 1777397757355, '2026-04-23', '2026-04-28 17:35:57', 11, 'salida', 'MXN', 604443.460000, 34, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, NULL, 17.462000, 'Pago a proveedor Binance — 34614.79000775 USDT (TC: 17.46199991)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(85, 1777397860213, '2026-04-23', '2026-04-28 17:37:40', 1, 'entrada', 'MXN', 123000.000000, NULL, 34, 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(86, 1777397860213, '2026-04-23', '2026-04-28 17:37:40', 1, 'entrada', 'MXN', 121155.000000, NULL, 39, 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(87, 1777397860213, '2026-04-23', '2026-04-28 17:37:40', 1, 'comision', 'MXN', 1599.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(88, 1777397860213, '2026-04-23', '2026-04-28 17:37:40', 1, 'comision', 'MXN', 246.000000, NULL, 25, NULL, 7, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(89, 1777397917528, '2026-04-23', '2026-04-28 17:38:37', 3, 'entrada', 'MXN', 1080752.282941, NULL, 2, 133, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.671100, 'Venta de 61159.31 USDT a TC 17.6711', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(90, 1777397917528, '2026-04-23', '2026-04-28 17:38:37', 3, 'salida', 'USDT', 61159.310000, 51, NULL, 133, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.671100, 'Salida de 61159.31 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(91, 1777397917528, '2026-04-23', '2026-04-28 17:38:37', 3, 'comision', 'MXN', 20070.301961, NULL, 1, 133, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.671100, 'Utilidad FIFO por venta de 61159.31 USDT a TC 17.6711', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(92, 1777397917528, '2026-04-23', '2026-04-28 17:38:37', 3, 'entrada', 'USDT', 61159.310000, NULL, 172, 133, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.671100, 'Registro venta: 61159.31 USDT a TC 17.6711 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(93, 1777397986292, '2026-04-23', '2026-04-28 17:39:46', 14, 'salida', 'MXN', 361000.000000, 2, NULL, NULL, NULL, NULL, 4, NULL, 4, NULL, NULL, NULL, 'Pago de préstamo a Hyperion', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(94, 1777397986298, '2026-04-23', '2026-04-28 17:39:46', 14, 'salida', 'MXN', 28176.850000, 2, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, NULL, NULL, 'Pago de préstamo a Hyperion', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(95, 1777397986300, '2026-04-23', '2026-04-28 17:39:46', 14, 'salida', 'MXN', 511500.000000, 2, NULL, NULL, NULL, NULL, 4, NULL, 2, NULL, NULL, NULL, 'Pago de préstamo a Hyperion', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(96, 1777398027533, '2026-04-23', '2026-04-28 17:40:27', 13, 'salida', 'MXN', 121155.000000, 2, 39, 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 6', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(97, 1777398071088, '2026-04-23', '2026-04-28 17:41:11', 4, 'traspaso', 'MXN', 153338.000000, 32, 34, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(98, 1777398091423, '2026-04-23', '2026-04-28 17:41:31', 13, 'salida', 'MXN', 51652.500000, 34, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 4', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(99, 1777398104393, '2026-04-23', '2026-04-28 17:41:44', 13, 'salida', 'MXN', 211550.670000, 34, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 2', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(100, 1777398213652, '2026-04-23', '2026-04-28 17:43:33', 6, 'entrada', 'USDT', 126.000000, NULL, 51, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 17.200000, 'Compra de 126 USDT a TC 17.2', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(101, 1777398213652, '2026-04-23', '2026-04-28 17:43:33', 6, 'salida', 'USDT', 126.000000, NULL, 19, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 17.200000, 'Adeudo a proveedor: 126 USDT (TC: 17.2)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(102, 1777398231683, '2026-04-23', '2026-04-28 17:43:51', 11, 'salida', 'MXN', 2167.200000, 36, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 17.200000, 'Pago a proveedor Emilio Agraz — 126 USDT (TC: 17.2)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(103, 1777398273139, '2026-04-23', '2026-04-28 17:44:33', 4, 'traspaso', 'MXN', 16182.000000, 2, 36, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(104, 1777398326483, '2026-04-20', '2026-04-28 17:45:26', 12, 'salida', 'MXN', 800.000000, 2, NULL, NULL, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, 'Gasto: Gas Kia - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(105, 1777398326483, '2026-04-20', '2026-04-28 17:45:26', 12, 'salida', 'MXN', 800.000000, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Gas Kia - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(106, 1777398392670, '2026-04-22', '2026-04-28 17:46:32', 12, 'salida', 'MXN', 1100.000000, 2, NULL, NULL, NULL, NULL, NULL, 3, NULL, NULL, NULL, NULL, 'Gasto: Cuentas Pro Amiri - Totatlplay Eduardo', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(107, 1777398392670, '2026-04-22', '2026-04-28 17:46:32', 12, 'salida', 'MXN', 1100.000000, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Cuentas Pro Amiri - Totatlplay Eduardo', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(108, 1777398444220, '2026-04-28', '2026-04-28 17:47:24', 12, 'salida', 'MXN', 1570.000000, 2, NULL, NULL, NULL, NULL, NULL, 4, NULL, NULL, NULL, NULL, 'Gasto: Papeleria - Toner Impresora', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(109, 1777398444220, '2026-04-28', '2026-04-28 17:47:24', 12, 'salida', 'MXN', 1570.000000, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Papeleria - Toner Impresora', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(110, 1777398538042, '2026-04-28', '2026-04-28 17:48:58', 12, 'salida', 'MXN', 3500.000000, 36, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, 'Gasto: Azul - Citlaly Azul', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(111, 1777398538042, '2026-04-28', '2026-04-28 17:48:58', 12, 'salida', 'MXN', 3500.000000, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Azul - Citlaly Azul', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(112, 1777398718277, '2026-04-28', '2026-04-28 17:51:58', 12, 'salida', 'MXN', 15400.000000, 2, NULL, NULL, NULL, NULL, NULL, 6, NULL, NULL, NULL, NULL, 'Gasto: Amiri Operaciones Integrales - Poderes Empresa', 2, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-28 17:52:13'),
(113, 1777398718277, '2026-04-28', '2026-04-28 17:51:58', 12, 'salida', 'MXN', 15400.000000, 1, NULL, NULL, NULL, NULL, NULL, 6, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Amiri Operaciones Integrales - Poderes Empresa', 2, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-28 17:52:13'),
(114, 1777398718278, '2026-04-28', '2026-04-28 17:52:13', 12, 'ajuste', 'MXN', -15400.000000, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1777398718277 - Gasto: Amiri Operaciones Integrales - Poderes Empresa', 2, 1, 'activo', 1, 112, 'Reversión del último movimiento', NULL, NULL),
(115, 1777398718278, '2026-04-28', '2026-04-28 17:52:13', 12, 'ajuste', 'MXN', -15400.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1777398718277 - Gasto (desde Comisiones AMIRI): Amiri Operaciones Integrales - Poderes Empresa', 2, 1, 'activo', 1, 113, 'Reversión del último movimiento', NULL, NULL),
(116, 1777398763225, '2026-04-24', '2026-04-28 17:52:43', 12, 'salida', 'MXN', 15400.000000, 36, NULL, NULL, NULL, NULL, NULL, 7, NULL, NULL, NULL, NULL, 'Gasto: Amiri Operaciones Integrales - Poderes Empresa', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(117, 1777398763225, '2026-04-24', '2026-04-28 17:52:43', 12, 'salida', 'MXN', 15400.000000, 1, NULL, NULL, NULL, NULL, NULL, 7, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Amiri Operaciones Integrales - Poderes Empresa', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(118, 1777398820005, '2026-04-28', '2026-04-28 17:53:40', 12, 'salida', 'MXN', 3000.000000, 52, NULL, NULL, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, 'Gasto: Amiri Operaciones Integrales - Vuelta Eduardo Socio Amiri', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(119, 1777398820005, '2026-04-28', '2026-04-28 17:53:40', 12, 'salida', 'MXN', 3000.000000, 1, NULL, NULL, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Amiri Operaciones Integrales - Vuelta Eduardo Socio Amiri', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(120, 1777399012166, '2026-04-28', '2026-04-28 17:56:52', 1, 'entrada', 'MXN', 590000.000000, NULL, 52, 134, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(121, 1777399012166, '2026-04-28', '2026-04-28 17:56:52', 1, 'entrada', 'MXN', 578200.000000, NULL, 173, 134, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(122, 1777399012166, '2026-04-28', '2026-04-28 17:56:52', 1, 'comision', 'MXN', 11800.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(123, 1777399059118, '2026-04-28', '2026-04-28 17:57:39', 1, 'entrada', 'MXN', 21300.000000, NULL, 32, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(124, 1777399059118, '2026-04-28', '2026-04-28 17:57:39', 1, 'entrada', 'MXN', 20665.260000, NULL, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(125, 1777399059118, '2026-04-28', '2026-04-28 17:57:39', 1, 'comision', 'MXN', 421.740000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(126, 1777399059118, '2026-04-28', '2026-04-28 17:57:39', 1, 'comision', 'MXN', 213.000000, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(127, 1777399073426, '2026-04-28', '2026-04-28 17:57:53', 15, 'salida', 'MXN', 213.000000, 36, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(128, 1777399109232, '2026-04-28', '2026-04-28 17:58:29', 13, 'salida', 'MXN', 20000.000000, 52, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 8', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(129, 1777399143079, '2026-04-28', '2026-04-28 17:59:03', 4, 'traspaso', 'MXN', 295000.000000, 52, 34, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(130, 1777399180065, '2026-04-28', '2026-04-28 17:59:40', 13, 'salida', 'MXN', 289100.000000, 34, 173, 134, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 7', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(131, 1777399318377, '2026-04-28', '2026-04-28 18:01:58', 10, 'entrada', 'MXN', 200000.000000, NULL, 37, NULL, NULL, NULL, 3, NULL, 5, NULL, NULL, NULL, 'Préstamo de Diego Alfredo H: Los Tome De Mantela Para Amiri', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(132, 1777399356514, '2026-04-28', '2026-04-28 18:02:36', 4, 'traspaso', 'MXN', 200000.000000, 37, 36, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(133, 1777399386506, '2026-04-28', '2026-04-28 18:03:06', 6, 'entrada', 'USDT', 11369.990000, NULL, 51, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 17.200000, 'Compra de 11369.99 USDT a TC 17.2', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(134, 1777399386506, '2026-04-28', '2026-04-28 18:03:06', 6, 'salida', 'MXN', 195563.828000, 36, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 17.200000, 'Pago de contado: $195563.828 MXN por 11369.99 USDT (TC: 17.2)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(135, 1777399481063, '2026-04-28', '2026-04-28 18:04:41', 6, 'entrada', 'USDT', 5329.990000, NULL, 51, NULL, NULL, 8, NULL, NULL, NULL, NULL, NULL, 17.470100, 'Compra de 5329.99 USDT a TC 17.4701', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(136, 1777399481063, '2026-04-28', '2026-04-28 18:04:41', 6, 'salida', 'USDT', 5329.990000, NULL, 44, NULL, NULL, 8, NULL, NULL, NULL, NULL, NULL, 17.470100, 'Adeudo a proveedor: 5329.99 USDT (TC: 17.4701)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(137, 1777399551969, '2026-04-28', '2026-04-28 18:05:51', 7, 'traspaso', 'MXN', 57500.000000, 52, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(138, 1777399828123, '2026-04-28', '2026-04-28 18:10:28', 4, 'traspaso', 'MXN', 111130.000000, 52, 34, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-28 18:11:10'),
(139, 1777399828124, '2026-04-28', '2026-04-28 18:11:10', 4, 'ajuste', 'MXN', -111130.000000, 34, 52, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1777399828123 - Cambio de flujo - transferencia entre cuentas', 2, 1, 'activo', 1, 138, 'Reversión del último movimiento', NULL, NULL),
(140, 1777399943036, '2026-04-28', '2026-04-28 18:12:23', 4, 'traspaso', 'MXN', 53520.000000, 52, 34, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(141, 1777399964697, '2026-04-28', '2026-04-28 18:12:44', 4, 'traspaso', 'MXN', 86455.250000, 34, 36, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(142, 1777399975658, '2026-04-28', '2026-04-28 18:12:55', 11, 'salida', 'MXN', 93115.460000, 36, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, NULL, 17.470100, 'Pago a proveedor Binance — 5329.98999974 USDT (TC: 17.47010032)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(143, 1777401004463, '2026-04-28', '2026-04-28 18:30:04', 3, 'entrada', 'MXN', 295600.499238, NULL, 2, 135, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.568100, 'Venta de 16825.98 USDT a TC 17.5681', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(144, 1777401004463, '2026-04-28', '2026-04-28 18:30:04', 3, 'salida', 'USDT', 16825.980000, 51, NULL, 135, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.568100, 'Salida de 16825.98 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(145, 1777401004463, '2026-04-28', '2026-04-28 18:30:04', 3, 'comision', 'MXN', 4754.012939, NULL, 1, 135, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.568100, 'Utilidad FIFO por venta de 16825.98 USDT a TC 17.5681', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(146, 1777401004463, '2026-04-28', '2026-04-28 18:30:04', 3, 'entrada', 'USDT', 16825.980000, NULL, 174, 135, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.568100, 'Registro venta: 16825.98 USDT a TC 17.5681 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(147, 1777401086761, '2026-04-28', '2026-04-28 18:31:26', 4, 'traspaso', 'MXN', 5224.220000, 2, 36, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(148, 1777421371426, '2026-04-28', '2026-04-29 00:09:31', 10, 'entrada', 'MXN', 300000.000000, NULL, 34, NULL, NULL, NULL, 4, NULL, 6, NULL, NULL, NULL, 'Préstamo de Hyperion: ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(149, 1777421409231, '2026-04-28', '2026-04-29 00:10:09', 13, 'salida', 'MXN', 289100.000000, 34, 173, 134, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 7', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(150, 1777422460945, '2026-04-28', '2026-04-29 00:27:40', 7, 'traspaso', 'MXN', 11500.000000, 52, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(151, 1777422570922, '2026-04-28', '2026-04-29 00:29:30', 6, 'entrada', 'USDT', 700.000000, NULL, 51, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 17.200000, 'Compra de 700 USDT a TC 17.2', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(152, 1777422570922, '2026-04-28', '2026-04-29 00:29:30', 6, 'salida', 'USDT', 700.000000, NULL, 19, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 17.200000, 'Adeudo a proveedor: 700 USDT (TC: 17.2)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(153, 1777422642096, '2026-04-28', '2026-04-29 00:30:42', 12, 'salida', 'MXN', 1700.000000, 2, NULL, NULL, NULL, NULL, NULL, 9, NULL, NULL, NULL, NULL, 'Gasto: Amiri Operaciones Integrales - Entrega Poderes', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(154, 1777422642096, '2026-04-28', '2026-04-29 00:30:42', 12, 'salida', 'MXN', 1700.000000, 1, NULL, NULL, NULL, NULL, NULL, 9, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Amiri Operaciones Integrales - Entrega Poderes', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(155, 1777422889860, '2026-04-28', '2026-04-29 00:34:49', 1, 'entrada', 'MXN', 266250.000000, NULL, 32, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-29 00:37:53'),
(156, 1777422889860, '2026-04-28', '2026-04-29 00:34:49', 1, 'entrada', 'MXN', 258262.500000, NULL, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-29 00:37:53'),
(157, 1777422889860, '2026-04-28', '2026-04-29 00:34:49', 1, 'comision', 'MXN', 5325.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-29 00:37:53'),
(158, 1777422889860, '2026-04-28', '2026-04-29 00:34:49', 1, 'comision', 'MXN', 2662.500000, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-29 00:37:53'),
(159, 1777423037952, '2026-04-29', '2026-04-29 00:37:17', 12, 'salida', 'MXN', 500.000000, 2, NULL, NULL, NULL, NULL, NULL, 10, NULL, NULL, NULL, NULL, 'Gasto: Gas Kia - ', 2, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-29 00:37:49'),
(160, 1777423037952, '2026-04-29', '2026-04-29 00:37:17', 12, 'salida', 'MXN', 500.000000, 1, NULL, NULL, NULL, NULL, NULL, 10, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Gas Kia - ', 2, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-29 00:37:49'),
(161, 1777423037953, '2026-04-28', '2026-04-29 00:37:49', 12, 'ajuste', 'MXN', -500.000000, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1777423037952 - Gasto: Gas Kia - ', 2, 1, 'activo', 1, 159, 'Reversión del último movimiento', NULL, NULL),
(162, 1777423037953, '2026-04-28', '2026-04-29 00:37:49', 12, 'ajuste', 'MXN', -500.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1777423037952 - Gasto (desde Comisiones AMIRI): Gas Kia - ', 2, 1, 'activo', 1, 160, 'Reversión del último movimiento', NULL, NULL),
(163, 1777423037954, '2026-04-28', '2026-04-29 00:37:53', 1, 'ajuste', 'MXN', -266250.000000, 32, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1777422889860 - Venta de Efectivo - dinero recibido del cliente', 2, 1, 'activo', 1, 155, 'Reversión del último movimiento', NULL, NULL),
(164, 1777423037954, '2026-04-28', '2026-04-29 00:37:53', 1, 'ajuste', 'MXN', -258262.500000, 26, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1777422889860 - Venta de Efectivo - efectivo recibido neto', 2, 1, 'activo', 1, 156, 'Reversión del último movimiento', NULL, NULL),
(165, 1777423037954, '2026-04-28', '2026-04-29 00:37:53', 1, 'ajuste', 'MXN', -5325.000000, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1777422889860 - Comisión Amiri - Venta de Efectivo', 2, 1, 'activo', 1, 157, 'Reversión del último movimiento', NULL, NULL),
(166, 1777423037954, '2026-04-28', '2026-04-29 00:37:53', 1, 'ajuste', 'MXN', -2662.500000, 24, NULL, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1777422889860 - Comisión Promotor - Venta de Efectivo', 2, 1, 'activo', 1, 158, 'Reversión del último movimiento', NULL, NULL),
(167, 1777423181528, '2026-04-29', '2026-04-29 00:39:41', 12, 'salida', 'MXN', 500.000000, 2, NULL, NULL, NULL, NULL, NULL, 11, NULL, NULL, NULL, NULL, 'Gasto: Gas Kia - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(168, 1777423181528, '2026-04-29', '2026-04-29 00:39:41', 12, 'salida', 'MXN', 500.000000, 1, NULL, NULL, NULL, NULL, NULL, 11, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Gas Kia - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(169, 1777423221857, '2026-04-28', '2026-04-29 00:40:21', 1, 'entrada', 'MXN', 266650.000000, NULL, 32, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(170, 1777423221857, '2026-04-28', '2026-04-29 00:40:21', 1, 'entrada', 'MXN', 258650.500000, NULL, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(171, 1777423221857, '2026-04-28', '2026-04-29 00:40:21', 1, 'comision', 'MXN', 5333.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(172, 1777423221857, '2026-04-28', '2026-04-29 00:40:21', 1, 'comision', 'MXN', 2666.500000, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(173, 1777484775777, '2026-04-28', '2026-04-29 17:46:15', 7, 'traspaso', 'MXN', 11760.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(174, 1777484840157, '2026-04-29', '2026-04-29 17:47:20', 11, 'salida', 'MXN', 12040.000000, 32, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 17.200000, 'Pago a proveedor Emilio Agraz — 700 USDT (TC: 17.2)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(175, 1777484887272, '2026-04-29', '2026-04-29 17:48:07', 13, 'salida', 'MXN', 250000.000000, 32, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 10', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(176, 1777484903428, '2026-04-29', '2026-04-29 17:48:23', 15, 'salida', 'MXN', 5329.000000, 36, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-29 17:52:06'),
(177, 1777484903429, '2026-04-29', '2026-04-29 17:52:06', 15, 'ajuste', 'MXN', -5329.000000, 24, 36, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1777484903428 - Pago de Comisiones a Kike Rizo', 2, 1, 'activo', 1, 176, 'Reversión del último movimiento', NULL, NULL),
(178, 1777485638778, '2026-04-29', '2026-04-29 18:00:38', 15, 'salida', 'MXN', 2666.510000, 36, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-29 18:00:52'),
(179, 1777485638779, '2026-04-29', '2026-04-29 18:00:52', 15, 'ajuste', 'MXN', -2666.510000, 24, 36, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1777485638778 - Pago de Comisiones a Kike Rizo', 2, 1, 'activo', 1, 178, 'Reversión del último movimiento', NULL, NULL),
(180, 1777485664880, '2026-04-29', '2026-04-29 18:01:04', 15, 'salida', 'MXN', 2666.500000, 36, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(181, 1777486401416, '2026-04-29', '2026-04-29 18:13:21', 1, 'entrada', 'MXN', 17895.000000, NULL, 32, 136, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(182, 1777486401416, '2026-04-29', '2026-04-29 18:13:21', 1, 'entrada', 'MXN', 17626.570000, NULL, 176, 136, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(183, 1777486401416, '2026-04-29', '2026-04-29 18:13:21', 1, 'comision', 'MXN', 178.950000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(184, 1777486401416, '2026-04-29', '2026-04-29 18:13:21', 1, 'comision', 'MXN', 89.480000, NULL, 175, NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(185, 1777486562479, '2026-04-29', '2026-04-29 18:16:02', 15, 'salida', 'MXN', 89.480000, 36, 175, NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Adrian Sandoval', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(186, 1777487903480, '2026-04-29', '2026-04-29 18:38:23', 14, 'salida', 'MXN', 200000.000000, 32, NULL, NULL, NULL, NULL, 3, NULL, 5, NULL, NULL, NULL, 'Pago de préstamo a Diego Alfredo H', 2, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-29 18:38:33'),
(187, 1777487903481, '2026-04-29', '2026-04-29 18:38:33', 14, 'ajuste', 'MXN', -200000.000000, NULL, 32, NULL, NULL, NULL, 3, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1777487903480 - Pago de préstamo a Diego Alfredo H', 2, 1, 'activo', 1, 186, 'Reversión del último movimiento', NULL, NULL);
INSERT INTO `movimientos` (`id`, `grupo_id`, `fecha`, `creado_en`, `servicio_id`, `tipo`, `moneda`, `monto`, `cuenta_origen_id`, `cuenta_destino_id`, `cliente_id`, `promotor_id`, `proveedor_id`, `prestamista_id`, `gasto_id`, `referencia_id`, `referencia_tipo`, `beneficiario`, `paridad`, `descripcion`, `usuario_id`, `activo`, `estado`, `es_reverso`, `movimiento_original_id`, `motivo_correccion`, `corregido_por_usuario_id`, `corregido_en`) VALUES
(188, 1777490429763, '2026-04-29', '2026-04-29 19:20:29', 14, 'salida', 'MXN', 170000.000000, 2, NULL, NULL, NULL, NULL, 3, NULL, 5, NULL, NULL, NULL, 'Abono manual a préstamo #5', 1, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-29 20:36:34'),
(190, 1777490429764, '2026-04-29', '2026-04-29 20:36:34', 14, 'ajuste', 'MXN', -170000.000000, NULL, 2, NULL, NULL, NULL, 3, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1777490429763 - Abono manual a préstamo #5', 2, 1, 'activo', 1, 188, 'Reversión del último movimiento', NULL, NULL),
(191, 1777495701380, '2026-04-29', '2026-04-29 20:48:21', 4, 'traspaso', 'MXN', 170000.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(192, 1777496475882, '2026-04-29', '2026-04-29 21:01:15', 7, 'traspaso', 'MXN', 33000.000000, 52, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(193, 1777496584540, '2026-04-29', '2026-04-29 21:03:04', 10, 'entrada', 'MXN', 260000.000000, NULL, 36, NULL, NULL, NULL, 4, NULL, 7, NULL, NULL, NULL, 'Préstamo de Hyperion', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(194, 1777496616732, '2026-04-29', '2026-04-29 21:03:36', 14, 'salida', 'MXN', 30000.000000, 32, NULL, NULL, NULL, NULL, 3, NULL, 5, NULL, NULL, NULL, 'Abono manual a préstamo #5', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(195, 1777497340533, '2026-04-29', '2026-04-29 21:15:40', 14, 'salida', 'MXN', 11500.000000, 52, NULL, NULL, NULL, NULL, 4, NULL, 7, NULL, NULL, NULL, 'Abono manual a préstamo #7', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(196, 1777497414520, '2026-04-29', '2026-04-29 21:16:54', 6, 'entrada', 'USDT', 13880.680000, NULL, 51, NULL, NULL, 8, NULL, NULL, NULL, NULL, NULL, 17.612100, 'Compra de 13880.68 USDT a TC 17.6121', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(197, 1777497414520, '2026-04-29', '2026-04-29 21:16:54', 6, 'salida', 'USDT', 13880.680000, NULL, 44, NULL, NULL, 8, NULL, NULL, NULL, NULL, NULL, 17.612100, 'Adeudo a proveedor: 13880.68 USDT (TC: 17.6121)', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(198, 1777497463440, '2026-04-29', '2026-04-29 21:17:43', 6, 'entrada', 'USDT', 13880.680000, NULL, 51, NULL, NULL, 8, NULL, NULL, NULL, NULL, NULL, 17.612100, 'Compra de 13880.68 USDT a TC 17.6121', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(199, 1777497463440, '2026-04-29', '2026-04-29 21:17:43', 6, 'salida', 'MXN', 244467.924228, 36, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, NULL, 17.612100, 'Pago de contado: $244467.924228 MXN por 13880.68 USDT (TC: 17.6121)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(200, 1777497524676, '2026-04-29', '2026-04-29 21:18:44', 3, 'entrada', 'MXN', 258700.631036, NULL, 34, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.742700, 'Venta de 14580.68 USDT a TC 17.7427', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(201, 1777497524676, '2026-04-29', '2026-04-29 21:18:44', 3, 'salida', 'USDT', 14580.680000, 51, NULL, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.742700, 'Salida de 14580.68 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(202, 1777497524676, '2026-04-29', '2026-04-29 21:18:44', 3, 'comision', 'MXN', 2192.706808, NULL, 1, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.742700, 'Utilidad FIFO por venta de 14580.68 USDT a TC 17.7427', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(203, 1777497524676, '2026-04-29', '2026-04-29 21:18:44', 3, 'entrada', 'USDT', 14580.680000, NULL, 31, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.742700, 'Registro venta: 14580.68 USDT a TC 17.7427 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(204, 1777498873764, '2026-04-29', '2026-04-29 21:41:13', 1, 'entrada', 'MXN', 132999.980000, NULL, 52, 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(205, 1777498873764, '2026-04-29', '2026-04-29 21:41:13', 1, 'entrada', 'MXN', 131004.980000, NULL, 39, 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(206, 1777498873764, '2026-04-29', '2026-04-29 21:41:13', 1, 'comision', 'MXN', 1729.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(207, 1777498873764, '2026-04-29', '2026-04-29 21:41:13', 1, 'comision', 'MXN', 266.000000, NULL, 25, NULL, 7, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(208, 1777507639996, '2026-04-29', '2026-04-30 00:07:19', 7, 'traspaso', 'MXN', 34500.000000, 52, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(209, 1777508291927, '2026-04-29', '2026-04-30 00:18:11', 13, 'salida', 'MXN', 131004.980000, 2, 39, 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 12', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(210, 1777508380426, '2026-04-29', '2026-04-30 00:19:40', 15, 'salida', 'MXN', 512.000000, 36, 25, NULL, 7, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Missael Jauregui', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(211, 1777571419502, '2026-04-30', '2026-04-30 17:50:19', 14, 'salida', 'MXN', 248500.000000, 34, NULL, NULL, NULL, NULL, 4, NULL, 7, NULL, NULL, NULL, 'Abono manual a préstamo #7', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(212, 1777571534546, '2026-04-30', '2026-04-30 17:52:14', 1, 'entrada', 'MXN', 109833.000000, NULL, 32, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(213, 1777571534546, '2026-04-30', '2026-04-30 17:52:14', 1, 'entrada', 'MXN', 106538.010000, NULL, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(214, 1777571534546, '2026-04-30', '2026-04-30 17:52:14', 1, 'comision', 'MXN', 2196.660000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(215, 1777571534546, '2026-04-30', '2026-04-30 17:52:14', 1, 'comision', 'MXN', 1098.330000, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(216, 1777571782297, '2026-04-30', '2026-04-30 17:56:22', 1, 'entrada', 'MXN', 95850.000000, NULL, 32, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(217, 1777571782297, '2026-04-30', '2026-04-30 17:56:22', 1, 'entrada', 'MXN', 92974.500000, NULL, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(218, 1777571782297, '2026-04-30', '2026-04-30 17:56:22', 1, 'comision', 'MXN', 1917.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(219, 1777571782297, '2026-04-30', '2026-04-30 17:56:22', 1, 'comision', 'MXN', 958.500000, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(220, 1777576221374, '2026-04-30', '2026-04-30 19:10:21', 13, 'salida', 'MXN', 92974.500000, 32, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 14', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-30 19:10:42'),
(221, 1777576221375, '2026-04-30', '2026-04-30 19:10:42', 13, 'ajuste', 'MXN', -92974.500000, 26, 32, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1777576221374 - Pago de venta efectivo ID 14', 2, 1, 'activo', 1, 220, 'Reversión del último movimiento', NULL, NULL),
(222, 1777576256743, '2026-04-30', '2026-04-30 19:10:56', 13, 'salida', 'MXN', 92974.500000, 52, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 14', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(223, 1777576286458, '2026-04-30', '2026-04-30 19:11:26', 13, 'salida', 'MXN', 82525.500000, 52, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 13', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(224, 1777580952544, '2026-04-30', '2026-04-30 20:29:12', 1, 'entrada', 'MXN', 52573.000000, NULL, 32, 136, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(225, 1777580952544, '2026-04-30', '2026-04-30 20:29:12', 1, 'entrada', 'MXN', 51784.400000, NULL, 176, 136, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(226, 1777580952544, '2026-04-30', '2026-04-30 20:29:12', 1, 'comision', 'MXN', 525.730000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(227, 1777580952544, '2026-04-30', '2026-04-30 20:29:12', 1, 'comision', 'MXN', 262.870000, NULL, 175, NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(228, 1777581027889, '2026-04-30', '2026-04-30 20:30:27', 10, 'entrada', 'MXN', 1000000.000000, NULL, 34, NULL, NULL, NULL, 4, NULL, 8, NULL, NULL, NULL, 'Préstamo de Hyperion', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(229, 1777581147675, '2026-04-30', '2026-04-30 20:32:27', 12, 'salida', 'MXN', 500.000000, 2, NULL, NULL, NULL, NULL, NULL, 12, NULL, NULL, NULL, NULL, 'Gasto: Gas Kia - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(230, 1777581147675, '2026-04-30', '2026-04-30 20:32:27', 12, 'salida', 'MXN', 500.000000, 1, NULL, NULL, NULL, NULL, NULL, 12, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Gas Kia - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(231, 1777584776728, '2026-04-30', '2026-04-30 21:32:56', 6, 'entrada', 'USDT', 50827.140000, NULL, 51, NULL, NULL, 8, NULL, NULL, NULL, NULL, NULL, 17.574100, 'Compra de 50827.14 USDT a TC 17.5741', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(232, 1777584776728, '2026-04-30', '2026-04-30 21:32:56', 6, 'salida', 'MXN', 893241.241074, 34, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, NULL, 17.574100, 'Pago de contado: $893241.241074 MXN por 50827.14 USDT (TC: 17.5741)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(233, 1777585250857, '2026-04-30', '2026-04-30 21:40:50', 3, 'entrada', 'MXN', 540763.855776, NULL, 2, 133, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.826800, 'Venta de 30334.32 USDT a TC 17.8268 - Pendiente de pago', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(234, 1777585250857, '2026-04-30', '2026-04-30 21:40:50', 3, 'salida', 'USDT', 30334.320000, 51, NULL, 133, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.826800, 'Salida de 30334.32 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(235, 1777585250857, '2026-04-30', '2026-04-30 21:40:50', 3, 'comision', 'MXN', 6313.580000, NULL, 1, 133, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.826800, 'Utilidad neta FIFO por venta de 30334.32 USDT a TC 17.8268', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(236, 1777585250857, '2026-04-30', '2026-04-30 21:40:50', 3, 'entrada', 'USDT', 30334.320000, NULL, 172, 133, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.826800, 'Registro venta: 30334.32 USDT a TC 17.8268 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(237, 1777587073349, '2026-04-30', '2026-04-30 22:11:13', 13, 'salida', 'MXN', 15325.010000, 52, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 13', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(238, 1777585250857, '2026-04-30', '2026-04-30 22:36:54', 3, 'comision', 'MXN', 1351.900000, NULL, 53, 133, 8, NULL, NULL, NULL, NULL, NULL, NULL, 17.826800, 'Comisión Promotor por venta de 30334.32 USDT', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(239, 1777591532200, '2026-04-30', '2026-04-30 23:25:32', 12, 'salida', 'MXN', 5000.000000, 2, NULL, NULL, NULL, NULL, NULL, 13, NULL, NULL, NULL, NULL, 'Gasto: Azul - Mandados A Citlaly Azul', 2, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-30 23:25:44'),
(240, 1777591532200, '2026-04-30', '2026-04-30 23:25:32', 12, 'salida', 'MXN', 5000.000000, 1, NULL, NULL, NULL, NULL, NULL, 13, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Azul - Mandados A Citlaly Azul', 2, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-30 23:25:44'),
(241, 1777591532201, '2026-04-30', '2026-04-30 23:25:44', 12, 'ajuste', 'MXN', -5000.000000, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1777591532200 - Gasto: Azul - Mandados A Citlaly Azul', 2, 1, 'activo', 1, 239, 'Reversión del último movimiento', NULL, NULL),
(242, 1777591532201, '2026-04-30', '2026-04-30 23:25:44', 12, 'ajuste', 'MXN', -5000.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1777591532200 - Gasto (desde Comisiones AMIRI): Azul - Mandados A Citlaly Azul', 2, 1, 'activo', 1, 240, 'Reversión del último movimiento', NULL, NULL),
(243, 1777591585612, '2026-04-30', '2026-04-30 23:26:25', 12, 'salida', 'MXN', 5000.000000, 34, NULL, NULL, NULL, NULL, NULL, 14, NULL, NULL, NULL, NULL, 'Gasto: Azul - Mandados A Azul Citlaly', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(244, 1777591585612, '2026-04-30', '2026-04-30 23:26:25', 12, 'salida', 'MXN', 5000.000000, 1, NULL, NULL, NULL, NULL, NULL, 14, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Azul - Mandados A Azul Citlaly', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(245, 1777591697547, '2026-04-30', '2026-04-30 23:28:17', 13, 'salida', 'MXN', 17626.570000, 2, 176, 136, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 11', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(246, 1777591713709, '2026-04-30', '2026-04-30 23:28:33', 13, 'salida', 'MXN', 51784.400000, 2, 176, 136, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 15', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(247, 1777591789608, '2026-04-30', '2026-04-30 23:29:49', 15, 'salida', 'MXN', 2056.830000, 36, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(248, 1777591985322, '2026-04-30', '2026-04-30 23:33:05', 15, 'salida', 'MXN', 262.870000, 36, 175, NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Adrian Sandoval', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(249, 1777592257376, '2026-04-30', '2026-04-30 23:37:37', 7, 'traspaso', 'MXN', 25500.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(250, 1777595917972, '2026-05-01', '2026-05-01 00:38:37', 12, 'salida', 'MXN', 20000.000000, 2, NULL, NULL, NULL, NULL, NULL, 15, NULL, NULL, NULL, NULL, 'Gasto: Nomina - Emiliano', 2, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-05-01 00:43:53'),
(251, 1777595917972, '2026-05-01', '2026-05-01 00:38:37', 12, 'salida', 'MXN', 20000.000000, 1, NULL, NULL, NULL, NULL, NULL, 15, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Nomina - Emiliano', 2, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-05-01 00:43:53'),
(252, 1777596170648, '2026-04-30', '2026-05-01 00:42:50', 3, 'entrada', 'MXN', 94812.269500, NULL, 2, 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.574100, 'Venta de 5395 USDT a TC 17.5741', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(253, 1777596170648, '2026-04-30', '2026-05-01 00:42:50', 3, 'salida', 'USDT', 5395.000000, 51, NULL, 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.574100, 'Salida de 5395 USDT del wallet por venta', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(254, 1777596170648, '2026-04-30', '2026-05-01 00:42:50', 3, 'entrada', 'USDT', 5395.000000, NULL, 50, 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.574100, 'Registro venta: 5395 USDT a TC 17.5741 al cliente', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(255, 1777596170649, '2026-04-30', '2026-05-01 00:43:53', 12, 'ajuste', 'MXN', -20000.000000, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1777595917972 - Gasto: Nomina - Emiliano', 2, 1, 'activo', 1, 250, 'Reversión del último movimiento', NULL, NULL),
(256, 1777596170649, '2026-04-30', '2026-05-01 00:43:53', 12, 'ajuste', 'MXN', -20000.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1777595917972 - Gasto (desde Comisiones AMIRI): Nomina - Emiliano', 2, 1, 'activo', 1, 251, 'Reversión del último movimiento', NULL, NULL),
(257, 1777596299364, '2026-04-30', '2026-05-01 00:44:59', 3, 'entrada', 'MXN', 94811.730000, NULL, 2, 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.574000, 'Venta de 5395 USDT a TC 17.574', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(258, 1777596299364, '2026-04-30', '2026-05-01 00:44:59', 3, 'salida', 'USDT', 5395.000000, 51, NULL, 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.574000, 'Salida de 5395 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(259, 1777596299364, '2026-04-30', '2026-05-01 00:44:59', 3, 'comision', 'MXN', -0.539500, NULL, 1, 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.574000, 'Utilidad neta FIFO por venta de 5395 USDT', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(260, 1777596299364, '2026-04-30', '2026-05-01 00:44:59', 3, 'entrada', 'USDT', 5395.000000, NULL, 50, 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.574000, 'Registro venta: 5395 USDT a TC 17.574 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(261, 1777596335217, '2026-04-30', '2026-05-01 00:45:35', 12, 'salida', 'MXN', 94811.730000, 2, NULL, NULL, NULL, NULL, NULL, 16, NULL, NULL, NULL, NULL, 'Gasto: Retiro Utilidades - Diego', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(262, 1777596335217, '2026-04-30', '2026-05-01 00:45:35', 12, 'salida', 'MXN', 94811.730000, 1, NULL, NULL, NULL, NULL, NULL, 16, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Retiro Utilidades - Diego', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(263, 1777596905893, '2026-04-30', '2026-05-01 00:55:05', 12, 'salida', 'MXN', 20000.000000, 2, NULL, NULL, NULL, NULL, NULL, 17, NULL, NULL, NULL, NULL, 'Gasto: Nomina - Emiliano', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(264, 1777596905893, '2026-04-30', '2026-05-01 00:55:05', 12, 'salida', 'MXN', 20000.000000, 1, NULL, NULL, NULL, NULL, NULL, 17, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Nomina - Emiliano', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(265, 1777600906293, '2026-04-30', '2026-05-01 02:01:46', 3, 'entrada', 'MXN', 36993.270000, NULL, 36, 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.574000, 'Venta de 2105 USDT a TC 17.574', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(266, 1777600906293, '2026-04-30', '2026-05-01 02:01:46', 3, 'salida', 'USDT', 2105.000000, 51, NULL, 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.574000, 'Salida de 2105 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(267, 1777600906293, '2026-04-30', '2026-05-01 02:01:46', 3, 'comision', 'MXN', -0.210500, NULL, 1, 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.574000, 'Utilidad neta FIFO por venta de 2105 USDT', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(268, 1777600906293, '2026-04-30', '2026-05-01 02:01:46', 3, 'entrada', 'USDT', 2105.000000, NULL, 50, 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.574000, 'Registro venta: 2105 USDT a TC 17.574 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(269, 1777912744697, '2026-05-04', '2026-05-04 16:39:04', 3, 'entrada', 'MXN', 66030.536841, NULL, 2, 133, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.725700, 'Venta de 3725.13 USDT a TC 17.7257', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(270, 1777912744697, '2026-05-04', '2026-05-04 16:39:04', 3, 'salida', 'USDT', 3725.130000, 51, NULL, 133, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.725700, 'Salida de 3725.13 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(271, 1777912744697, '2026-05-04', '2026-05-04 16:39:04', 3, 'comision', 'MXN', 399.653366, NULL, 1, 133, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.725700, 'Utilidad neta FIFO por venta de 3725.13 USDT', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(272, 1777912744697, '2026-05-04', '2026-05-04 16:39:04', 3, 'comision', 'MXN', 165.076342, NULL, 53, 133, NULL, 8, NULL, NULL, NULL, NULL, NULL, 17.725700, 'Comisión Promotor por venta de 3725.13 USDT', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(273, 1777912744697, '2026-05-04', '2026-05-04 16:39:04', 3, 'entrada', 'USDT', 3725.130000, NULL, 172, 133, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.725700, 'Registro venta: 3725.13 USDT a TC 17.7257 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(274, 1777918776981, '2026-05-04', '2026-05-04 18:19:36', 16, 'salida', 'MXN', 3990.000000, 34, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, 'Préstamo a Citlaly Azul', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(275, 1777919384658, '2026-05-04', '2026-05-04 18:29:44', 1, 'entrada', 'MXN', 12995.000000, NULL, 32, 136, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(276, 1777919384658, '2026-05-04', '2026-05-04 18:29:44', 1, 'entrada', 'MXN', 12800.070000, NULL, 176, 136, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(277, 1777919384658, '2026-05-04', '2026-05-04 18:29:44', 1, 'comision', 'MXN', 129.950000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(278, 1777919384658, '2026-05-04', '2026-05-04 18:29:44', 1, 'comision', 'MXN', 64.980000, NULL, 175, NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(279, 1777934928058, '2026-05-04', '2026-05-04 22:48:48', 7, 'traspaso', 'MXN', 72600.000000, 34, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(280, 1777941435760, '2026-05-04', '2026-05-05 00:37:15', 1, 'entrada', 'MXN', 304568.530000, NULL, 32, 8, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(281, 1777941435760, '2026-05-04', '2026-05-05 00:37:15', 1, 'entrada', 'MXN', 300000.000000, NULL, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(282, 1777941435760, '2026-05-04', '2026-05-05 00:37:15', 1, 'comision', 'MXN', 3045.690000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(283, 1777941435760, '2026-05-04', '2026-05-05 00:37:15', 1, 'comision', 'MXN', 1522.840000, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(284, 1777941823894, '2026-05-04', '2026-05-05 00:43:43', 13, 'salida', 'MXN', 300000.000000, 2, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 18', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(285, 1777998468177, '2026-05-04', '2026-05-05 16:27:48', 13, 'salida', 'MXN', 8687.500000, 36, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 13', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(286, 1777998501918, '2026-05-04', '2026-05-05 16:28:21', 13, 'salida', 'MXN', 8650.500000, 36, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 10', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-05-05 16:28:30'),
(287, 1777998501919, '2026-05-05', '2026-05-05 16:28:30', 13, 'ajuste', 'MXN', -8650.500000, 26, 36, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1777998501918 - Pago de venta efectivo ID 10', 2, 1, 'activo', 1, 286, 'Reversión del último movimiento', NULL, NULL),
(288, 1777998583041, '2026-05-04', '2026-05-05 16:29:43', 13, 'salida', 'MXN', 8262.500000, 36, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 10', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(289, 1777999546654, '2026-05-05', '2026-05-05 16:45:46', 1, 'entrada', 'MXN', 5190.000000, NULL, 32, 136, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(290, 1777999546654, '2026-05-05', '2026-05-05 16:45:46', 1, 'entrada', 'MXN', 5112.150000, NULL, 176, 136, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(291, 1777999546654, '2026-05-05', '2026-05-05 16:45:46', 1, 'comision', 'MXN', 51.900000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(292, 1777999546654, '2026-05-05', '2026-05-05 16:45:46', 1, 'comision', 'MXN', 25.950000, NULL, 175, NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(293, 1777999599478, '2026-05-05', '2026-05-05 16:46:39', 15, 'salida', 'MXN', 90.930000, 36, 175, NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Adrian Sandoval', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(294, 1777999627925, '2026-05-05', '2026-05-05 16:47:07', 15, 'salida', 'MXN', 1522.840000, 36, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(295, 1777999910293, '2026-05-05', '2026-05-05 16:51:50', 12, 'salida', 'MXN', 500.000000, 2, NULL, NULL, NULL, NULL, NULL, 18, NULL, NULL, NULL, NULL, 'Gasto: Gas Kia - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(296, 1777999910293, '2026-05-05', '2026-05-05 16:51:50', 12, 'salida', 'MXN', 500.000000, 1, NULL, NULL, NULL, NULL, NULL, 18, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Gas Kia - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(297, 1778009567294, '2026-05-05', '2026-05-05 19:32:47', 12, 'salida', 'MXN', 16000.000000, 36, NULL, NULL, NULL, NULL, NULL, 19, NULL, NULL, NULL, NULL, 'Gasto: Renta - Renta Mayo', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(298, 1778009567294, '2026-05-05', '2026-05-05 19:32:47', 12, 'salida', 'MXN', 16000.000000, 1, NULL, NULL, NULL, NULL, NULL, 19, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Renta - Renta Mayo', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(299, 1778011018839, '2026-05-05', '2026-05-05 19:56:58', 12, 'salida', 'MXN', 1600.000000, 2, NULL, NULL, NULL, NULL, NULL, 20, NULL, NULL, NULL, NULL, 'Gasto: Amiri Operaciones Integrales - Juan Pablo Poderes', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(300, 1778011018839, '2026-05-05', '2026-05-05 19:56:58', 12, 'salida', 'MXN', 1600.000000, 1, NULL, NULL, NULL, NULL, NULL, 20, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Amiri Operaciones Integrales - Juan Pablo Poderes', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(301, 1778011072231, '2026-05-05', '2026-05-05 19:57:52', 12, 'salida', 'MXN', 3600.000000, 36, NULL, NULL, NULL, NULL, NULL, 21, NULL, NULL, NULL, NULL, 'Gasto: Recargas - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(302, 1778011072231, '2026-05-05', '2026-05-05 19:57:52', 12, 'salida', 'MXN', 3600.000000, 1, NULL, NULL, NULL, NULL, NULL, 21, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Recargas - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(303, 1778013057744, '2026-05-05', '2026-05-05 20:30:57', 4, 'traspaso', 'MXN', 100000.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(304, 1778016088652, '2026-05-05', '2026-05-05 21:21:28', 1, 'entrada', 'MXN', 44545.000000, NULL, 32, 136, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(305, 1778016088652, '2026-05-05', '2026-05-05 21:21:28', 1, 'entrada', 'MXN', 43876.820000, NULL, 176, 136, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(306, 1778016088652, '2026-05-05', '2026-05-05 21:21:28', 1, 'comision', 'MXN', 445.450000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(307, 1778016088652, '2026-05-05', '2026-05-05 21:21:28', 1, 'comision', 'MXN', 222.730000, NULL, 175, NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(308, 1778018594355, '2026-05-05', '2026-05-05 22:03:14', 4, 'traspaso', 'MXN', 16000.000000, 34, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(309, 1778019336054, '2026-05-05', '2026-05-05 22:15:36', 14, 'salida', 'MXN', 500000.000000, 32, NULL, NULL, NULL, NULL, 4, NULL, 8, NULL, NULL, NULL, 'Abono manual a préstamo #8', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(310, 1778027282255, '2026-05-05', '2026-05-06 00:28:02', 7, 'traspaso', 'MXN', 17224.970000, 52, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(311, 1778027318168, '2026-05-05', '2026-05-06 00:28:38', 4, 'traspaso', 'MXN', 19275.030000, 34, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(312, 1778027395921, '2026-05-05', '2026-05-06 00:29:55', 15, 'salida', 'MXN', 222.730000, 36, 175, NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Adrian Sandoval', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(313, 1778027467689, '2026-05-05', '2026-05-06 00:31:07', 13, 'salida', 'MXN', 665.260000, 36, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 8', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(314, 1778027481036, '2026-05-05', '2026-05-06 00:31:21', 13, 'salida', 'MXN', 388.000000, 36, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 10', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(315, 1778027616820, '2026-05-05', '2026-05-06 00:33:36', 1, 'entrada', 'MXN', 3211.000000, NULL, 32, 136, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(316, 1778027616820, '2026-05-05', '2026-05-06 00:33:36', 1, 'entrada', 'MXN', 3162.830000, NULL, 176, 136, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(317, 1778027616820, '2026-05-05', '2026-05-06 00:33:36', 1, 'comision', 'MXN', 32.110000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(318, 1778027616820, '2026-05-05', '2026-05-06 00:33:36', 1, 'comision', 'MXN', 16.060000, NULL, 175, NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(319, 1778027643801, '2026-05-05', '2026-05-06 00:34:03', 15, 'salida', 'MXN', 16.060000, 36, 175, NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Adrian Sandoval', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(320, 1778028256535, '2026-05-05', '2026-05-06 00:44:16', 12, 'salida', 'MXN', 5435.000000, 2, NULL, NULL, NULL, NULL, NULL, 22, NULL, NULL, NULL, NULL, 'Gasto: Comisiones Rojas - ', 2, 1, 'anulado', 0, NULL, NULL, NULL, NULL),
(321, 1778028256535, '2026-05-05', '2026-05-06 00:44:16', 12, 'salida', 'MXN', 5435.000000, 1, NULL, NULL, NULL, NULL, NULL, 22, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Comisiones Rojas - ', 2, 1, 'anulado', 0, NULL, NULL, NULL, NULL),
(322, 1778028333578, '2026-05-06', '2026-05-06 00:45:33', 16, 'salida', 'MXN', 2000.000000, 36, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 'Citlaly Azul', NULL, 'Préstamo a Citlaly Azul', 2, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-05-06 00:47:55'),
(323, 1778028333579, '2026-05-05', '2026-05-06 00:47:55', 16, 'ajuste', 'MXN', -2000.000000, NULL, 36, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1778028333578 - Préstamo a Citlaly Azul', 2, 1, 'activo', 1, 322, 'Reversión del último movimiento', NULL, NULL),
(324, 1778089794456, '2026-05-06', '2026-05-06 17:49:54', 12, 'salida', 'MXN', 5435.000000, 34, NULL, NULL, NULL, NULL, NULL, 23, NULL, NULL, NULL, NULL, 'Gasto: Comisiones Rojas - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(325, 1778089794456, '2026-05-06', '2026-05-06 17:49:54', 12, 'salida', 'MXN', 5435.000000, 1, NULL, NULL, NULL, NULL, NULL, 23, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Comisiones Rojas - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(326, 1778089871641, '2026-05-06', '2026-05-06 17:51:11', 13, 'salida', 'MXN', 64951.870000, 2, 176, 136, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta(s) a Osvaldo Vazquez - Joyero (Pago Múltiple)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(327, 1778090169753, '2026-05-06', '2026-05-06 17:56:09', 1, 'entrada', 'MXN', 265970.220000, NULL, 32, 8, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(328, 1778090169753, '2026-05-06', '2026-05-06 17:56:09', 1, 'entrada', 'MXN', 261980.670000, NULL, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(329, 1778090169753, '2026-05-06', '2026-05-06 17:56:09', 1, 'comision', 'MXN', 2659.700000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(330, 1778090169753, '2026-05-06', '2026-05-06 17:56:09', 1, 'comision', 'MXN', 1329.850000, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(331, 1778090192070, '2026-05-06', '2026-05-06 17:56:32', 4, 'traspaso', 'MXN', 250000.000000, 32, 36, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(332, 1778090214422, '2026-05-06', '2026-05-06 17:56:54', 4, 'traspaso', 'MXN', 23220.000000, 36, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(333, 1778092673298, '2026-05-06', '2026-05-06 18:37:53', 6, 'entrada', 'USDT', 2594.650000, NULL, 51, NULL, NULL, 8, NULL, NULL, NULL, NULL, NULL, 17.360500, 'Compra de 2594.65 USDT a TC 17.3605', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(334, 1778092673298, '2026-05-06', '2026-05-06 18:37:53', 6, 'salida', 'USDT', 2594.650000, NULL, 44, NULL, NULL, 8, NULL, NULL, NULL, NULL, NULL, 17.360500, 'Adeudo a proveedor: 2594.65 USDT (TC: 17.3605)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(335, 1778092878450, '2026-05-06', '2026-05-06 18:41:18', 3, 'entrada', 'MXN', 207499.950000, NULL, 34, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.500000, 'Venta de 11857.14 USDT a TC 17.5', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(336, 1778092878450, '2026-05-06', '2026-05-06 18:41:18', 3, 'salida', 'USDT', 11857.140000, 51, NULL, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.500000, 'Salida de 11857.14 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(337, 1778092878450, '2026-05-06', '2026-05-06 18:41:18', 3, 'comision', 'MXN', -325.507554, NULL, 1, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.500000, 'Utilidad neta FIFO por venta de 11857.14 USDT', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(338, 1778092878450, '2026-05-06', '2026-05-06 18:41:18', 3, 'entrada', 'USDT', 11857.140000, NULL, 31, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.500000, 'Registro venta: 11857.14 USDT a TC 17.5 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(339, 1778092903004, '2026-05-06', '2026-05-06 18:41:43', 11, 'salida', 'MXN', 45044.420000, 36, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, NULL, 17.360499, 'Pago a proveedor Binance — 2594.6499999 USDT (TC: 17.36049949)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(340, 1778101193713, '2026-05-06', '2026-05-06 20:59:53', 7, 'traspaso', 'MXN', 12400.000000, 52, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(341, 1778101355456, '2026-05-06', '2026-05-06 21:02:35', 1, 'entrada', 'MXN', 110000.000000, NULL, 32, 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(342, 1778101355456, '2026-05-06', '2026-05-06 21:02:35', 1, 'entrada', 'MXN', 108350.000000, NULL, 39, 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(343, 1778101355456, '2026-05-06', '2026-05-06 21:02:35', 1, 'comision', 'MXN', 1430.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(344, 1778101355456, '2026-05-06', '2026-05-06 21:02:35', 1, 'comision', 'MXN', 220.000000, NULL, 25, NULL, 7, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(345, 1778101390283, '2026-05-06', '2026-05-06 21:03:10', 14, 'salida', 'MXN', 49000.000000, 32, NULL, NULL, NULL, NULL, 3, NULL, 5, NULL, NULL, NULL, 'Abono manual a préstamo #5', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(346, 1778103020307, '2026-05-06', '2026-05-06 21:30:20', 6, 'entrada', 'USDT', 165000.000000, NULL, 51, NULL, NULL, 8, NULL, NULL, NULL, NULL, NULL, 17.150000, 'Compra de 165000 USDT a TC 17.15', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(347, 1778103020307, '2026-05-06', '2026-05-06 21:30:20', 6, 'salida', 'USDT', 165000.000000, NULL, 44, NULL, NULL, 8, NULL, NULL, NULL, NULL, NULL, 17.150000, 'Adeudo a proveedor: 165000 USDT (TC: 17.15)', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(348, 1778103045225, '2026-05-06', '2026-05-06 21:30:45', 6, 'entrada', 'USDT', 165000.000000, NULL, 51, NULL, NULL, 3, NULL, NULL, NULL, NULL, NULL, 17.150000, 'Compra de 165000 USDT a TC 17.15', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(349, 1778103045225, '2026-05-06', '2026-05-06 21:30:45', 6, 'salida', 'USDT', 165000.000000, NULL, 21, NULL, NULL, 3, NULL, NULL, NULL, NULL, NULL, 17.150000, 'Adeudo a proveedor: 165000 USDT (TC: 17.15)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(350, 1778103084465, '2026-05-06', '2026-05-06 21:31:24', 10, 'entrada', 'MXN', 2048971.400000, NULL, 2, NULL, NULL, NULL, 4, NULL, 9, NULL, NULL, NULL, 'Préstamo de Hyperion', 2, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-05-06 21:31:39'),
(351, 1778103084466, '2026-05-06', '2026-05-06 21:31:39', 10, 'ajuste', 'MXN', -2048971.400000, 2, NULL, NULL, NULL, NULL, 4, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1778103084465 - Préstamo de Hyperion', 2, 1, 'activo', 1, 350, 'Reversión del último movimiento', NULL, NULL),
(352, 1778103139381, '2026-05-06', '2026-05-06 21:32:19', 10, 'entrada', 'MXN', 2048972.780000, NULL, 2, NULL, NULL, NULL, 4, NULL, 10, NULL, NULL, NULL, 'Préstamo de Hyperion', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(353, 1778103192646, '2026-05-06', '2026-05-06 21:33:12', 11, 'salida', 'MXN', 2829750.000000, 2, NULL, NULL, NULL, 3, NULL, NULL, NULL, NULL, NULL, 17.150000, 'Pago a proveedor Alejandro Bambas — 165000 USDT (TC: 17.15)', 2, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-05-06 21:33:42'),
(354, 1778103192647, '2026-05-06', '2026-05-06 21:33:42', 11, 'ajuste', 'MXN', -2829750.000000, NULL, 2, NULL, NULL, 3, NULL, NULL, NULL, NULL, NULL, 17.150000, 'REVERSO: Anulación de grupo #1778103192646 - Pago a proveedor Alejandro Bambas — 165000 USDT (TC: 17.15)', 2, 1, 'activo', 1, 353, 'Reversión del último movimiento', NULL, NULL),
(355, 1778103251717, '2026-05-06', '2026-05-06 21:34:11', 11, 'salida', 'MXN', 2829750.000000, 2, NULL, NULL, NULL, 3, NULL, NULL, NULL, NULL, NULL, 17.150000, 'Pago a proveedor Alejandro Bambas — 165000 USDT (TC: 17.15)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(356, 1778103926066, '2026-05-06', '2026-05-06 21:45:26', 16, 'salida', 'MXN', 2000.000000, 34, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, 'Citlaly Azul', NULL, 'Préstamo a Citlaly Azul', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(357, 1778104543743, '2026-05-06', '2026-05-06 21:55:43', 10, 'entrada', 'MXN', 108349.400000, NULL, 2, NULL, NULL, NULL, 4, NULL, 11, NULL, NULL, NULL, 'Préstamo de Hyperion', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(358, 1778104560949, '2026-05-06', '2026-05-06 21:56:00', 13, 'salida', 'MXN', 108350.000000, 2, 39, 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta(s) a King Of Watches', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(359, 1778104596159, '2026-05-06', '2026-05-06 21:56:36', 15, 'salida', 'MXN', 1329.850000, 36, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(360, 1778104740919, '2026-05-06', '2026-05-06 21:59:00', 3, 'entrada', 'MXN', 41946.084330, NULL, 36, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.295500, 'Venta de 2425.26 USDT a TC 17.2955', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(361, 1778104740919, '2026-05-06', '2026-05-06 21:59:00', 3, 'salida', 'USDT', 2425.260000, 51, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.295500, 'Salida de 2425.26 USDT del wallet por venta', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(362, 1778104740919, '2026-05-06', '2026-05-06 21:59:00', 3, 'comision', 'MXN', 351.780730, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.295500, 'Utilidad neta FIFO por venta de 2425.26 USDT', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(363, 1778104740919, '2026-05-06', '2026-05-06 21:59:00', 3, 'entrada', 'USDT', 2425.260000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.295500, 'Registro venta: 2425.26 USDT a TC 17.2955 al cliente', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(364, 1778104929458, '2026-05-06', '2026-05-06 22:02:09', 3, 'entrada', 'MXN', 41946.084330, NULL, 36, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.295500, 'Venta de 2425.26 USDT a TC 17.2955', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(365, 1778104929458, '2026-05-06', '2026-05-06 22:02:09', 3, 'salida', 'USDT', 2425.260000, 51, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.295500, 'Salida de 2425.26 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(366, 1778104929458, '2026-05-06', '2026-05-06 22:02:09', 3, 'comision', 'MXN', 351.780730, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.295500, 'Utilidad neta FIFO por venta de 2425.26 USDT', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(367, 1778104929458, '2026-05-06', '2026-05-06 22:02:09', 3, 'entrada', 'USDT', 2425.260000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.295500, 'Registro venta: 2425.26 USDT a TC 17.2955 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(368, 1778105089340, '2026-05-06', '2026-05-06 22:04:49', 3, 'entrada', 'MXN', 56479.147070, NULL, 36, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.295500, 'Venta de 3265.54 USDT a TC 17.2955', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(369, 1778105089340, '2026-05-06', '2026-05-06 22:04:49', 3, 'salida', 'USDT', 3265.540000, 51, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.295500, 'Salida de 3265.54 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(370, 1778105089340, '2026-05-06', '2026-05-06 22:04:49', 3, 'comision', 'MXN', 475.136070, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.295500, 'Utilidad neta FIFO por venta de 3265.54 USDT', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(371, 1778105089340, '2026-05-06', '2026-05-06 22:04:49', 3, 'entrada', 'USDT', 3265.540000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.295500, 'Registro venta: 3265.54 USDT a TC 17.2955 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(372, 1778106450130, '2026-05-06', '2026-05-06 22:27:30', 3, 'entrada', 'MXN', 60000.165960, NULL, 36, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.295600, 'Venta de 3469.1 USDT a TC 17.2956', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(373, 1778106450130, '2026-05-06', '2026-05-06 22:27:30', 3, 'salida', 'USDT', 3469.100000, 51, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.295600, 'Salida de 3469.1 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(374, 1778106450130, '2026-05-06', '2026-05-06 22:27:30', 3, 'comision', 'MXN', 505.100960, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.295600, 'Utilidad neta FIFO por venta de 3469.1 USDT', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(375, 1778106450130, '2026-05-06', '2026-05-06 22:27:30', 3, 'entrada', 'USDT', 3469.100000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.295600, 'Registro venta: 3469.1 USDT a TC 17.2956 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(376, 1778112716254, '2026-05-06', '2026-05-07 00:11:56', 10, 'entrada', 'MXN', 750000.000000, NULL, 2, NULL, NULL, NULL, 4, NULL, 12, NULL, NULL, NULL, 'Préstamo de Hyperion', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(377, 1778113007107, '2026-05-06', '2026-05-07 00:16:47', 1, 'entrada', 'MXN', 761421.320000, NULL, 32, 137, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(378, 1778113007107, '2026-05-06', '2026-05-07 00:16:47', 1, 'entrada', 'MXN', 750000.000000, NULL, 177, 137, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(379, 1778113007107, '2026-05-06', '2026-05-07 00:16:47', 1, 'comision', 'MXN', 7614.210000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(380, 1778113007107, '2026-05-06', '2026-05-07 00:16:47', 1, 'comision', 'MXN', 3807.110000, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(381, 1778113027990, '2026-05-06', '2026-05-07 00:17:07', 13, 'salida', 'MXN', 750000.000000, 2, 177, 137, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta(s) a Raul', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(382, 1778113090034, '2026-05-06', '2026-05-07 00:18:10', 15, 'salida', 'MXN', 3807.110000, 36, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(383, 1778113239624, '2026-05-06', '2026-05-07 00:20:39', 13, 'salida', 'MXN', 11980.670000, 36, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta(s) a Juan Manuel Nuñez', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(384, 1778113670324, '2026-05-06', '2026-05-07 00:27:50', 3, 'entrada', 'MXN', 268068.598035, NULL, 36, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.305500, 'Venta de 15490.37 USDT a TC 17.3055', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(385, 1778113670324, '2026-05-06', '2026-05-07 00:27:50', 3, 'salida', 'USDT', 15490.370000, 51, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.305500, 'Salida de 15490.37 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(386, 1778113670324, '2026-05-06', '2026-05-07 00:27:50', 3, 'comision', 'MXN', 2408.752535, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.305500, 'Utilidad neta FIFO por venta de 15490.37 USDT', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL);
INSERT INTO `movimientos` (`id`, `grupo_id`, `fecha`, `creado_en`, `servicio_id`, `tipo`, `moneda`, `monto`, `cuenta_origen_id`, `cuenta_destino_id`, `cliente_id`, `promotor_id`, `proveedor_id`, `prestamista_id`, `gasto_id`, `referencia_id`, `referencia_tipo`, `beneficiario`, `paridad`, `descripcion`, `usuario_id`, `activo`, `estado`, `es_reverso`, `movimiento_original_id`, `motivo_correccion`, `corregido_por_usuario_id`, `corregido_en`) VALUES
(387, 1778113670324, '2026-05-06', '2026-05-07 00:27:50', 3, 'entrada', 'USDT', 15490.370000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.305500, 'Registro venta: 15490.37 USDT a TC 17.3055 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(388, 1778179883261, '2026-05-07', '2026-05-07 18:51:23', 1, 'entrada', 'MXN', 98.500000, NULL, 177, 137, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 1, '2026-05-07 18:51:46'),
(389, 1778179883261, '2026-05-07', '2026-05-07 18:51:23', 1, 'comision', 'MXN', 1.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 1, '2026-05-07 18:51:46'),
(390, 1778179883261, '2026-05-07', '2026-05-07 18:51:23', 1, 'comision', 'MXN', 0.500000, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 1, '2026-05-07 18:51:46'),
(391, 1778179883261, '2026-05-07', '2026-05-07 18:51:23', 1, 'comision', 'MXN', 0.500000, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 1, '2026-05-07 18:51:46'),
(392, 1778179883262, '2026-05-07', '2026-05-07 18:51:46', 1, 'ajuste', 'MXN', -98.500000, 177, NULL, 137, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1778179883261 - Venta de Efectivo - efectivo recibido neto', 1, 1, 'activo', 1, 388, 'Reversión del último movimiento', NULL, NULL),
(393, 1778179883262, '2026-05-07', '2026-05-07 18:51:46', 1, 'ajuste', 'MXN', -1.000000, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1778179883261 - Comisión Amiri - Venta de Efectivo', 1, 1, 'activo', 1, 389, 'Reversión del último movimiento', NULL, NULL),
(394, 1778179883262, '2026-05-07', '2026-05-07 18:51:46', 1, 'ajuste', 'MXN', -0.500000, 24, NULL, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1778179883261 - Comisión Promotor - Venta de Efectivo', 1, 1, 'activo', 1, 390, 'Reversión del último movimiento', NULL, NULL),
(395, 1778179883262, '2026-05-07', '2026-05-07 18:51:46', 1, 'ajuste', 'MXN', -0.500000, 24, NULL, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1778179883261 - Comisión Promotor - Venta de Efectivo', 1, 1, 'activo', 1, 391, 'Reversión del último movimiento', NULL, NULL),
(396, 1778186331600, '2026-05-07', '2026-05-07 20:38:51', 4, 'traspaso', 'MXN', 485000.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(397, 1778186563322, '2026-05-07', '2026-05-07 20:42:43', 3, 'entrada', 'MXN', 66149.846256, NULL, 36, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.240800, 'Venta de 3836.82 USDT a TC 17.2408', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(398, 1778186563322, '2026-05-07', '2026-05-07 20:42:43', 3, 'salida', 'USDT', 3836.820000, 51, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.240800, 'Salida de 3836.82 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(399, 1778186563322, '2026-05-07', '2026-05-07 20:42:43', 3, 'comision', 'MXN', 348.383256, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.240800, 'Utilidad neta FIFO por venta de 3836.82 USDT', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(400, 1778186563322, '2026-05-07', '2026-05-07 20:42:43', 3, 'entrada', 'USDT', 3836.820000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.240800, 'Registro venta: 3836.82 USDT a TC 17.2408 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(401, 1778186678687, '2026-05-07', '2026-05-07 20:44:38', 12, 'salida', 'MXN', 500.000000, 2, NULL, NULL, NULL, NULL, NULL, 24, NULL, NULL, NULL, NULL, 'Gasto: Cfe - Reconexión', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(402, 1778187557222, '2026-05-07', '2026-05-07 20:59:17', 14, 'salida', 'MXN', 484500.000000, 2, NULL, NULL, NULL, NULL, 4, NULL, 8, NULL, NULL, NULL, 'Abono manual a préstamo #8', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(403, 1778194668698, '2026-05-07', '2026-05-07 22:57:48', 13, 'salida', 'MXN', 250000.000000, 36, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta(s) a Juan Manuel Nuñez', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(404, 1778197715601, '2026-05-07', '2026-05-07 23:48:35', 4, 'traspaso', 'MXN', 351369.380000, 32, 36, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(405, 1778197747784, '2026-05-07', '2026-05-07 23:49:07', 7, 'traspaso', 'MXN', 460000.000000, 36, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(406, 1778197818159, '2026-05-07', '2026-05-07 23:50:18', 14, 'salida', 'MXN', 15500.000000, 2, NULL, NULL, NULL, NULL, 4, NULL, 8, NULL, NULL, NULL, 'Abono manual a préstamo #8', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(407, 1778197818159, '2026-05-07', '2026-05-07 23:50:18', 14, 'salida', 'MXN', 300000.000000, 2, NULL, NULL, NULL, NULL, 4, NULL, 6, NULL, NULL, NULL, 'Abono manual a préstamo #6', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(408, 1778197840435, '2026-05-07', '2026-05-07 23:50:40', 14, 'salida', 'MXN', 144500.000000, 2, NULL, NULL, NULL, NULL, 4, NULL, 12, NULL, NULL, NULL, 'Abono manual a préstamo #12', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(409, 1778197945938, '2026-05-07', '2026-05-07 23:52:25', 14, 'salida', 'MXN', 20000.000000, 36, NULL, NULL, NULL, NULL, 4, NULL, 12, NULL, NULL, NULL, 'Abono manual a préstamo #12', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(410, 1778198016202, '2026-05-07', '2026-05-07 23:53:36', 3, 'entrada', 'MXN', 31469.997300, NULL, 36, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.241000, 'Venta de 1825.3 USDT a TC 17.241', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(411, 1778198016202, '2026-05-07', '2026-05-07 23:53:36', 3, 'salida', 'USDT', 1825.300000, 51, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.241000, 'Salida de 1825.3 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(412, 1778198016202, '2026-05-07', '2026-05-07 23:53:36', 3, 'comision', 'MXN', 166.102300, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.241000, 'Utilidad neta FIFO por venta de 1825.3 USDT', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(413, 1778198016202, '2026-05-07', '2026-05-07 23:53:36', 3, 'entrada', 'USDT', 1825.300000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.241000, 'Registro venta: 1825.3 USDT a TC 17.241 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(414, 1778202095921, '2026-05-07', '2026-05-08 01:01:35', 14, 'salida', 'MXN', 30000.000000, 36, NULL, NULL, NULL, NULL, 3, NULL, 5, NULL, NULL, NULL, 'Abono manual a préstamo #5', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(415, 1778259479502, '2026-05-08', '2026-05-08 16:57:59', 1, 'entrada', 'MXN', 495000.000000, NULL, 181, 138, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(416, 1778259479502, '2026-05-08', '2026-05-08 16:57:59', 1, 'comision', 'MXN', 4000.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(417, 1778259479502, '2026-05-08', '2026-05-08 16:57:59', 1, 'comision', 'MXN', 1000.000000, NULL, 180, NULL, 10, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(418, 1778259479502, '2026-05-08', '2026-05-08 16:57:59', 1, 'entrada', 'MXN', 500000.000000, NULL, 34, 138, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Ingreso por Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(419, 1778260024710, '2026-05-08', '2026-05-08 17:07:04', 3, 'entrada', 'MXN', 71387.889972, NULL, 36, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.305400, 'Venta de 4125.18 USDT a TC 17.3054', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(420, 1778260024710, '2026-05-08', '2026-05-08 17:07:04', 3, 'salida', 'USDT', 4125.180000, 51, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.305400, 'Salida de 4125.18 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(421, 1778260024710, '2026-05-08', '2026-05-08 17:07:04', 3, 'comision', 'MXN', 641.052972, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.305400, 'Utilidad neta FIFO por venta de 4125.18 USDT', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(422, 1778260024710, '2026-05-08', '2026-05-08 17:07:04', 3, 'entrada', 'USDT', 4125.180000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.305400, 'Registro venta: 4125.18 USDT a TC 17.3054 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(423, 1778262145333, '2026-05-08', '2026-05-08 17:42:25', 14, 'salida', 'MXN', 207500.000000, 34, NULL, NULL, NULL, NULL, 4, NULL, 12, NULL, NULL, NULL, 'Abono manual a préstamo #12', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(424, 1778276835472, '2026-05-08', '2026-05-08 21:47:15', 10, 'entrada', 'MXN', 495000.000000, NULL, 2, NULL, NULL, NULL, 4, NULL, 13, NULL, NULL, NULL, 'Préstamo de Hyperion', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(425, 1778282438133, '2026-05-08', '2026-05-08 23:20:38', 10, 'entrada', 'MXN', 4300.000000, NULL, 2, NULL, NULL, NULL, 4, NULL, 14, NULL, NULL, NULL, 'Préstamo de Hyperion', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(426, 1778282451527, '2026-05-08', '2026-05-08 23:20:51', 12, 'salida', 'MXN', 500.000000, 2, NULL, NULL, NULL, NULL, NULL, 25, NULL, NULL, NULL, NULL, 'Gasto: Gas Kia - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(427, 1778282679498, '2026-05-08', '2026-05-08 23:24:39', 12, 'salida', 'MXN', 3800.000000, 2, NULL, NULL, NULL, NULL, NULL, 26, NULL, NULL, NULL, NULL, 'Gasto: Operativo - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(428, 1778282823613, '2026-05-08', '2026-05-08 23:27:03', 4, 'traspaso', 'MXN', 350000.000000, 36, 34, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(429, 1778282881121, '2026-05-08', '2026-05-08 23:28:01', 7, 'traspaso', 'MXN', 644000.000000, 34, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(430, 1778291260367, '2026-05-08', '2026-05-09 01:47:40', 13, 'salida', 'MXN', 495000.000000, 2, 181, 138, NULL, NULL, NULL, NULL, 26, NULL, NULL, 1.000000, 'Pago a Miguel Cisneros - Venta #26', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(431, 1778291310532, '2026-05-08', '2026-05-09 01:48:30', 12, 'salida', 'MXN', 1880.000000, 2, NULL, NULL, NULL, NULL, NULL, 27, NULL, NULL, NULL, NULL, 'Gasto: Comisiones Rojas - ', 2, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-05-09 01:48:43'),
(432, 1778291310533, '2026-05-08', '2026-05-09 01:48:43', 12, 'ajuste', 'MXN', -1880.000000, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1778291310532 - Gasto: Comisiones Rojas - ', 2, 1, 'activo', 1, 431, 'Reversión del último movimiento', NULL, NULL),
(433, 1778291345491, '2026-05-08', '2026-05-09 01:49:05', 12, 'salida', 'MXN', 1880.000000, 34, NULL, NULL, NULL, NULL, NULL, 28, NULL, NULL, NULL, NULL, 'Gasto: Comisiones Rojas - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(434, 1778291755331, '2026-05-08', '2026-05-09 01:55:55', 3, 'entrada', 'MXN', 230460.421750, NULL, 36, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.251000, 'Venta de 13359.25 USDT a TC 17.251', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(435, 1778291755331, '2026-05-08', '2026-05-09 01:55:55', 3, 'salida', 'USDT', 13359.250000, 51, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.251000, 'Salida de 13359.25 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(436, 1778291755331, '2026-05-08', '2026-05-09 01:55:55', 3, 'comision', 'MXN', 1349.284250, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.251000, 'Utilidad neta FIFO por venta de 13359.25 USDT', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(437, 1778291755331, '2026-05-08', '2026-05-09 01:55:55', 3, 'entrada', 'USDT', 13359.250000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.251000, 'Registro venta: 13359.25 USDT a TC 17.251 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(438, 1778291931819, '2026-05-08', '2026-05-09 01:58:51', 14, 'salida', 'MXN', 50000.000000, 36, NULL, NULL, NULL, NULL, 3, NULL, 5, NULL, NULL, NULL, 'Abono manual a préstamo #5', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(439, 1778292490504, '2026-05-08', '2026-05-09 02:08:10', 14, 'salida', 'MXN', 4300.000000, 2, NULL, NULL, NULL, NULL, 4, NULL, 14, NULL, NULL, NULL, 'Abono manual a préstamo #14', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(440, 1778292490504, '2026-05-08', '2026-05-09 02:08:10', 14, 'salida', 'MXN', 495000.000000, 2, NULL, NULL, NULL, NULL, 4, NULL, 13, NULL, NULL, NULL, 'Abono manual a préstamo #13', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(441, 1778292510074, '2026-05-08', '2026-05-09 02:08:30', 14, 'salida', 'MXN', 144700.000000, 2, NULL, NULL, NULL, NULL, 4, NULL, 12, NULL, NULL, NULL, 'Abono manual a préstamo #12', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(442, 1778522107962, '2026-05-11', '2026-05-11 17:55:07', 14, 'salida', 'MXN', 41000.000000, 36, NULL, NULL, NULL, NULL, 3, NULL, 5, NULL, NULL, NULL, 'Abono manual a préstamo #5', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(443, 1778523303388, '2026-05-11', '2026-05-11 18:15:03', 16, 'salida', 'MXN', 600.000000, 34, NULL, NULL, NULL, NULL, NULL, NULL, 4, NULL, 'Citlaly Azul', NULL, 'Préstamo a Citlaly Azul', 2, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-05-11 18:21:27'),
(444, 1778523303389, '2026-05-11', '2026-05-11 18:21:27', 16, 'ajuste', 'MXN', -600.000000, NULL, 34, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1778523303388 - Préstamo a Citlaly Azul', 2, 1, 'activo', 1, 443, 'Reversión del último movimiento', NULL, NULL),
(445, 1778523780931, '2026-05-11', '2026-05-11 18:23:00', 16, 'salida', 'MXN', 3600.000000, 34, NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, 'Citlaly Azul', NULL, 'Préstamo a Citlaly Azul', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(446, 1778523797836, '2026-05-11', '2026-05-11 18:23:17', 16, 'salida', 'MXN', 1000.000000, 34, NULL, NULL, NULL, NULL, NULL, NULL, 6, NULL, 'Nataly Azul', NULL, 'Préstamo a Nataly Azul', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(447, 1778527749179, '2026-05-11', '2026-05-11 19:29:09', 14, 'salida', 'MXN', 79800.000000, 36, NULL, NULL, NULL, NULL, 4, NULL, 12, NULL, NULL, NULL, 'Abono manual a préstamo #12', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(448, 1778527766652, '2026-05-11', '2026-05-11 19:29:26', 14, 'salida', 'MXN', 50200.000000, 34, NULL, NULL, NULL, NULL, 4, NULL, 12, NULL, NULL, NULL, 'Abono manual a préstamo #12', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(449, 1778532757358, '2026-05-07', '2026-05-11 20:52:37', 12, 'salida', 'MXN', 500.000000, 1, NULL, NULL, NULL, NULL, NULL, 24, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Cfe - Reconexión', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(450, 1778532757363, '2026-05-08', '2026-05-11 20:52:37', 12, 'salida', 'MXN', 500.000000, 1, NULL, NULL, NULL, NULL, NULL, 25, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Gas Kia - ', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(451, 1778532757366, '2026-05-08', '2026-05-11 20:52:37', 12, 'salida', 'MXN', 3800.000000, 1, NULL, NULL, NULL, NULL, NULL, 26, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Operativo - ', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(452, 1778532757369, '2026-05-08', '2026-05-11 20:52:37', 12, 'salida', 'MXN', 1880.000000, 1, NULL, NULL, NULL, NULL, NULL, 28, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Comisiones Rojas - ', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(453, 1778537585311, '2026-05-11', '2026-05-11 22:13:05', 3, 'entrada', 'MXN', 161863.528008, NULL, 36, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.063700, 'Venta de 9485.84 USDT a TC 17.0637', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(454, 1778537585311, '2026-05-11', '2026-05-11 22:13:05', 3, 'salida', 'USDT', 9485.840000, 51, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.063700, 'Salida de 9485.84 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(455, 1778537585311, '2026-05-11', '2026-05-11 22:13:05', 3, 'comision', 'MXN', -818.627992, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.063700, 'Utilidad neta FIFO por venta de 9485.84 USDT', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(456, 1778537585311, '2026-05-11', '2026-05-11 22:13:05', 3, 'entrada', 'USDT', 9485.840000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.063700, 'Registro venta: 9485.84 USDT a TC 17.0637 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(457, 1778538352994, '2026-05-11', '2026-05-11 22:25:52', 4, 'traspaso', 'MXN', 100034.320000, 36, 34, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(458, 1778538402713, '2026-05-11', '2026-05-11 22:26:42', 7, 'traspaso', 'MXN', 241500.000000, 34, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(459, 1778538425221, '2026-05-11', '2026-05-11 22:27:05', 14, 'salida', 'MXN', 103300.000000, 2, NULL, NULL, NULL, NULL, 4, NULL, 12, NULL, NULL, NULL, 'Abono manual a préstamo #12', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(460, 1778538471490, '2026-05-11', '2026-05-11 22:27:51', 14, 'salida', 'MXN', 108349.400000, 2, NULL, NULL, NULL, NULL, 4, NULL, 11, NULL, NULL, NULL, 'Abono manual a préstamo #11', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(461, 1778538498217, '2026-05-11', '2026-05-11 22:28:18', 14, 'salida', 'MXN', 29850.600000, 2, NULL, NULL, NULL, NULL, 4, NULL, 10, NULL, NULL, NULL, 'Abono manual a préstamo #10', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(462, 1778540707019, '2026-05-11', '2026-05-11 23:05:07', 4, 'traspaso', 'MXN', 105000.000000, 36, 34, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(463, 1778604512211, '2026-05-12', '2026-05-12 16:48:32', 6, 'entrada', 'USDT', 1000.000000, NULL, 51, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 17.100000, 'Compra de 1000 USDT a TC 17.1', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(464, 1778604512211, '2026-05-12', '2026-05-12 16:48:32', 6, 'salida', 'USDT', 1000.000000, NULL, 19, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 17.100000, 'Adeudo a proveedor: 1000 USDT (TC: 17.1)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(465, 1778604604873, '2026-05-12', '2026-05-12 16:50:04', 11, 'salida', 'MXN', 2000.000000, 36, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 17.100000, 'Pago a proveedor Emilio Agraz — 116.95906433 USDT (TC: 17.1)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(466, 1778609146749, '2026-05-12', '2026-05-12 18:05:46', 3, 'entrada', 'MXN', 247788.350000, NULL, 36, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.277900, 'Venta de 14341.33 USDT a TC 17.2779', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(467, 1778609146749, '2026-05-12', '2026-05-12 18:05:46', 3, 'salida', 'USDT', 14341.330000, 51, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.277900, 'Salida de 14341.33 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(468, 1778609146749, '2026-05-12', '2026-05-12 18:05:46', 3, 'comision', 'MXN', 1834.256107, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.277900, 'Utilidad neta FIFO por venta de 14341.33 USDT', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(469, 1778609146749, '2026-05-12', '2026-05-12 18:05:46', 3, 'entrada', 'USDT', 14341.330000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.277900, 'Registro venta: 14341.33 USDT a TC 17.2779 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(470, 1778615554988, '2026-05-12', '2026-05-12 19:52:34', 6, 'entrada', 'USDT', 285.000000, NULL, 51, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 17.100000, 'Compra de 285 USDT a TC 17.1', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(471, 1778615554988, '2026-05-12', '2026-05-12 19:52:34', 6, 'salida', 'USDT', 285.000000, NULL, 19, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 17.100000, 'Adeudo a proveedor: 285 USDT (TC: 17.1)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(472, 1778616243595, '2026-05-12', '2026-05-12 20:04:03', 3, 'entrada', 'MXN', 65070.000000, NULL, 36, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.259900, 'Venta de 3770 USDT a TC 17.2599', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(473, 1778616243595, '2026-05-12', '2026-05-12 20:04:03', 3, 'salida', 'USDT', 3770.000000, 51, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.259900, 'Salida de 3770 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(474, 1778616243595, '2026-05-12', '2026-05-12 20:04:03', 3, 'comision', 'MXN', 414.323000, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.259900, 'Utilidad neta FIFO por venta de 3770 USDT', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(475, 1778616243595, '2026-05-12', '2026-05-12 20:04:03', 3, 'entrada', 'USDT', 3770.000000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.259900, 'Registro venta: 3770 USDT a TC 17.2599 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(476, 1778616364494, '2026-05-12', '2026-05-12 20:06:04', 4, 'traspaso', 'MXN', 300000.000000, 36, 34, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(477, 1778616380715, '2026-05-12', '2026-05-12 20:06:20', 7, 'traspaso', 'MXN', 253000.000000, 34, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(478, 1778616447443, '2026-05-12', '2026-05-12 20:07:27', 11, 'salida', 'MXN', 19973.500000, 2, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 17.100014, 'Pago a proveedor Emilio Agraz — 1168.03999987 USDT (TC: 17.1000137)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(479, 1778618588843, '2026-05-12', '2026-05-12 20:43:08', 16, 'salida', 'MXN', 2500.000000, 34, NULL, NULL, NULL, NULL, NULL, NULL, 7, NULL, 'Nataly Azul', NULL, 'Préstamo a Nataly Azul', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(480, 1778631060000, '2026-05-12', '2026-05-13 00:11:00', 13, 'entrada', 'MXN', 0.220000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Ajuste por redondeo acumulado USDT', 1, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(481, 1778634273177, '2026-05-12', '2026-05-13 01:04:33', 1, 'entrada', 'MXN', 238590.890000, NULL, 183, 139, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(482, 1778634273177, '2026-05-12', '2026-05-13 01:04:33', 1, 'comision', 'MXN', 1687.010000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(483, 1778634273177, '2026-05-12', '2026-05-13 01:04:33', 1, 'comision', 'MXN', 723.000000, NULL, 182, NULL, 11, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(484, 1778634273177, '2026-05-12', '2026-05-13 01:04:33', 1, 'entrada', 'MXN', 241000.900000, NULL, 32, 139, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Ingreso por Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(485, 1778634325875, '2026-05-12', '2026-05-13 01:05:25', 1, 'entrada', 'MXN', 2966.820000, NULL, 176, 136, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(486, 1778634325875, '2026-05-12', '2026-05-13 01:05:25', 1, 'comision', 'MXN', 30.120000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(487, 1778634325875, '2026-05-12', '2026-05-13 01:05:25', 1, 'comision', 'MXN', 15.060000, NULL, 175, NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(488, 1778634325875, '2026-05-12', '2026-05-13 01:05:25', 1, 'entrada', 'MXN', 3012.000000, NULL, 32, 136, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Ingreso por Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(489, 1778634461119, '2026-05-12', '2026-05-13 01:07:41', 4, 'traspaso', 'MXN', 12000.000000, 2, 36, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(490, 1778703822593, '2026-05-13', '2026-05-13 20:23:42', 12, 'salida', 'MXN', 1000.000000, 36, NULL, NULL, NULL, NULL, NULL, 29, NULL, NULL, NULL, NULL, 'Gasto: Amiri Operaciones Integrales - Cita', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(491, 1778703822593, '2026-05-13', '2026-05-13 20:23:42', 12, 'salida', 'MXN', 1000.000000, 1, NULL, NULL, NULL, NULL, NULL, 29, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Amiri Operaciones Integrales - Cita', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(492, 1778703942324, '2026-05-13', '2026-05-13 20:25:42', 6, 'entrada', 'USDT', 4345.000000, NULL, 51, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 17.100000, 'Compra de 4345 USDT a TC 17.1', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(493, 1778703942324, '2026-05-13', '2026-05-13 20:25:42', 6, 'salida', 'MXN', 74299.500000, 2, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 17.100000, 'Pago de contado: $74299.5 MXN por 4345 USDT (TC: 17.1)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(494, 1778704159537, '2026-05-13', '2026-05-13 20:29:19', 10, 'entrada', 'MXN', 698000.000000, NULL, 2, NULL, NULL, NULL, 3, NULL, 15, NULL, NULL, NULL, 'Préstamo de Diego Alfredo H', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(495, 1778704201562, '2026-05-13', '2026-05-13 20:30:01', 13, 'salida', 'MXN', 238590.890000, 2, 183, 139, NULL, NULL, NULL, NULL, 27, NULL, NULL, 1.000000, 'Pago a Wb - Gas - Venta #27', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(496, 1778706006640, '2026-05-13', '2026-05-13 21:00:06', 10, 'entrada', 'MXN', 550000.000000, NULL, 2, NULL, NULL, NULL, 5, NULL, 16, NULL, NULL, NULL, 'Préstamo de Alejandro Mora', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(497, 1778706116294, '2026-05-13', '2026-05-13 21:01:56', 3, 'entrada', 'MXN', 294500.000000, NULL, 34, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.400000, 'Venta de 16925.2873 USDT a TC 17.4', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(498, 1778706116294, '2026-05-13', '2026-05-13 21:01:56', 3, 'salida', 'USDT', 16925.287300, 51, NULL, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.400000, 'Salida de 16925.2873 USDT del wallet por venta', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(499, 1778706116294, '2026-05-13', '2026-05-13 21:01:56', 3, 'comision', 'MXN', 4231.320000, NULL, 1, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.400000, 'Utilidad neta FIFO por venta de 16925.2873 USDT', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(500, 1778706116294, '2026-05-13', '2026-05-13 21:01:56', 3, 'entrada', 'USDT', 16925.287300, NULL, 31, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.400000, 'Registro venta: 16925.2873 USDT a TC 17.4 al cliente', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(501, 1778706187140, '2026-05-13', '2026-05-13 21:03:07', 3, 'entrada', 'MXN', 294500.000000, NULL, 34, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.400000, 'Venta de 16925.2873 USDT a TC 17.4', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(502, 1778706187140, '2026-05-13', '2026-05-13 21:03:07', 3, 'salida', 'USDT', 16925.287300, 51, NULL, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.400000, 'Salida de 16925.2873 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(503, 1778706187140, '2026-05-13', '2026-05-13 21:03:07', 3, 'comision', 'MXN', 4231.320000, NULL, 1, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.400000, 'Utilidad neta FIFO por venta de 16925.2873 USDT', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(504, 1778706187140, '2026-05-13', '2026-05-13 21:03:07', 3, 'entrada', 'USDT', 16925.287300, NULL, 31, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.400000, 'Registro venta: 16925.2873 USDT a TC 17.4 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(505, 1778706446982, '2026-05-13', '2026-05-13 21:07:26', 12, 'salida', 'MXN', 651.110000, 34, NULL, NULL, NULL, NULL, NULL, 30, NULL, NULL, NULL, NULL, 'Gasto: Comisiones Rojas - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(506, 1778706446982, '2026-05-13', '2026-05-13 21:07:26', 12, 'salida', 'MXN', 651.110000, 1, NULL, NULL, NULL, NULL, NULL, 30, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Comisiones Rojas - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(507, 1778706493226, '2026-05-13', '2026-05-13 21:08:13', 15, 'salida', 'MXN', 220.000000, 2, 25, NULL, 7, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Missael Jauregui', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(508, 1778711453365, '2026-05-13', '2026-05-13 22:30:53', 15, 'salida', 'MXN', 723.000000, 36, 182, NULL, 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Ernesto Sandoval', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(509, 1778777471977, '2026-05-14', '2026-05-14 16:51:11', 14, 'salida', 'MXN', 442500.000000, 2, NULL, NULL, NULL, NULL, 4, NULL, 10, NULL, NULL, NULL, 'Abono manual a préstamo #10', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(510, 1778779055977, '2026-05-14', '2026-05-14 17:17:35', 10, 'entrada', 'MXN', 486000.000000, NULL, 2, NULL, NULL, NULL, 6, NULL, 17, NULL, NULL, NULL, 'Préstamo de Yuyu', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(511, 1778785276570, '2026-05-14', '2026-05-14 19:01:16', 1, 'entrada', 'MXN', 79200.000000, NULL, 185, 140, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(512, 1778785276570, '2026-05-14', '2026-05-14 19:01:16', 1, 'comision', 'MXN', 560.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(513, 1778785276570, '2026-05-14', '2026-05-14 19:01:16', 1, 'comision', 'MXN', 240.000000, NULL, 184, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(514, 1778785276570, '2026-05-14', '2026-05-14 19:01:16', 1, 'entrada', 'MXN', 80000.000000, NULL, 32, 140, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Ingreso por Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(515, 1778785336658, '2026-05-14', '2026-05-14 19:02:16', 13, 'salida', 'MXN', 79200.000000, 2, 185, 140, NULL, NULL, NULL, NULL, 29, NULL, NULL, 1.000000, 'Pago a Emiliano Rivera - Venta #29', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(516, 1778785414527, '2026-05-14', '2026-05-14 19:03:34', 15, 'salida', 'MXN', 240.000000, 2, 184, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Emiliano Hurtado', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(517, 1778786912163, '2026-05-14', '2026-05-14 19:28:32', 3, 'entrada', 'MXN', 23000.000000, NULL, 36, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.285565, 'Venta de 1330.59 USDT a TC 17.285565', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(518, 1778786912163, '2026-05-14', '2026-05-14 19:28:32', 3, 'salida', 'USDT', 1330.590000, 51, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.285565, 'Salida de 1330.59 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(519, 1778786912163, '2026-05-14', '2026-05-14 19:28:32', 3, 'comision', 'MXN', 180.380000, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.285565, 'Utilidad neta FIFO por venta de 1330.59 USDT', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(520, 1778786912163, '2026-05-14', '2026-05-14 19:28:32', 3, 'entrada', 'USDT', 1330.590000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.285565, 'Registro venta: 1330.59 USDT a TC 17.285565 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(521, 1778794377001, '2026-05-14', '2026-05-14 21:32:57', 4, 'traspaso', 'MXN', 23000.000000, 36, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(522, 1778794716177, '2026-05-14', '2026-05-14 21:38:36', 6, 'entrada', 'USDT', 100757.000000, NULL, 51, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 17.200000, 'Compra de 100757 USDT a TC 17.2', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(523, 1778794716177, '2026-05-14', '2026-05-14 21:38:36', 6, 'salida', 'USDT', 100757.000000, NULL, 19, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 17.200000, 'Adeudo a proveedor: 100757 USDT (TC: 17.2)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(524, 1778795905124, '2026-05-14', '2026-05-14 21:58:25', 10, 'entrada', 'MXN', 150000.000000, NULL, 36, NULL, NULL, NULL, 6, NULL, 18, NULL, NULL, NULL, 'Préstamo de Yuyu', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(525, 1778797332536, '2026-05-14', '2026-05-14 22:22:12', 6, 'entrada', 'USDT', 16573.270000, NULL, 51, NULL, NULL, 8, NULL, NULL, NULL, NULL, NULL, 17.290073, 'Compra de 16573.27 USDT a TC 17.290073', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(526, 1778797332536, '2026-05-14', '2026-05-14 22:22:12', 6, 'salida', 'USDT', 16573.270000, NULL, 44, NULL, NULL, 8, NULL, NULL, NULL, NULL, NULL, 17.290073, 'Adeudo a proveedor: 16573.27 USDT (TC: 17.290073)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(527, 1778798170570, '2026-05-14', '2026-05-14 22:36:10', 3, 'entrada', 'MXN', 103741.210000, NULL, 36, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.290202, 'Venta de 6000 USDT a TC 17.290202', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(528, 1778798170570, '2026-05-14', '2026-05-14 22:36:10', 3, 'salida', 'USDT', 6000.000000, 51, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.290202, 'Salida de 6000 USDT del wallet por venta', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(529, 1778798170570, '2026-05-14', '2026-05-14 22:36:10', 3, 'comision', 'MXN', 841.210000, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.290202, 'Utilidad neta FIFO por venta de 6000 USDT', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(530, 1778798170570, '2026-05-14', '2026-05-14 22:36:10', 3, 'entrada', 'USDT', 6000.000000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.290202, 'Registro venta: 6000 USDT a TC 17.290202 al cliente', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(531, 1778798201230, '2026-05-14', '2026-05-14 22:36:41', 6, 'entrada', 'USDT', 6000.000000, NULL, 51, NULL, NULL, 8, NULL, NULL, NULL, NULL, NULL, 17.290202, 'Compra de 6000 USDT a TC 17.290202', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(532, 1778798201230, '2026-05-14', '2026-05-14 22:36:41', 6, 'salida', 'USDT', 6000.000000, NULL, 44, NULL, NULL, 8, NULL, NULL, NULL, NULL, NULL, 17.290202, 'Adeudo a proveedor: 6000 USDT (TC: 17.290202)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(533, 1778800010777, '2026-05-14', '2026-05-14 23:06:50', 4, 'traspaso', 'MXN', 300000.000000, 32, 36, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(534, 1778800035233, '2026-05-14', '2026-05-14 23:07:15', 11, 'salida', 'MXN', 390294.250000, 36, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, NULL, 17.290107, 'Pago a proveedor Binance — 22573.26999837 USDT (TC: 17.29010684)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(535, 1778800142132, '2026-05-14', '2026-05-14 23:09:02', 11, 'salida', 'MXN', 60000.000000, 36, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 17.200000, 'Pago a proveedor Emilio Agraz — 3488.37209302 USDT (TC: 17.2)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(536, 1778804659987, '2026-05-14', '2026-05-15 00:24:19', 4, 'traspaso', 'MXN', 30000.000000, 32, 36, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(537, 1778805475940, '2026-05-14', '2026-05-15 00:37:55', 10, 'entrada', 'MXN', 74779.000000, NULL, 2, NULL, NULL, NULL, 7, NULL, 19, NULL, NULL, NULL, 'Préstamo de Valeria Garcia', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(538, 1778805487845, '2026-05-14', '2026-05-15 00:38:07', 14, 'salida', 'MXN', 74779.000000, 36, NULL, NULL, NULL, NULL, 7, NULL, 19, NULL, NULL, NULL, 'Abono manual a préstamo #19', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(539, 1778805549746, '2026-05-15', '2026-05-15 00:39:09', 16, 'salida', 'MXN', 5000.000000, 34, NULL, NULL, NULL, NULL, NULL, NULL, 8, NULL, 'Nataly Azul', NULL, 'Préstamo a Nataly Azul', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(540, 1778805962784, '2026-05-14', '2026-05-15 00:46:02', 10, 'entrada', 'MXN', 455265.290000, NULL, 2, NULL, NULL, NULL, 4, NULL, 20, NULL, NULL, NULL, 'Préstamo de Hyperion', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(541, 1778805981736, '2026-05-14', '2026-05-15 00:46:21', 11, 'salida', 'MXN', 1673020.400000, 2, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 17.200000, 'Pago a proveedor Emilio Agraz — 97268.62999938 USDT (TC: 17.19999963)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(542, 1778863784187, '2026-05-15', '2026-05-15 16:49:44', 3, 'entrada', 'MXN', 540131.630000, NULL, 2, 133, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.663800, 'Venta de 30578.45 USDT a TC 17.6638', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(543, 1778863784187, '2026-05-15', '2026-05-15 16:49:44', 3, 'salida', 'USDT', 30578.450000, 51, NULL, 133, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.663800, 'Salida de 30578.45 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(544, 1778863784187, '2026-05-15', '2026-05-15 16:49:44', 3, 'comision', 'MXN', 14360.880000, NULL, 1, 133, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.663800, 'Utilidad neta FIFO por venta de 30578.45 USDT', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(545, 1778863784187, '2026-05-15', '2026-05-15 16:49:44', 3, 'comision', 'MXN', 1350.330000, NULL, 53, 133, NULL, 8, NULL, NULL, NULL, NULL, NULL, 17.663800, 'Comisión Promotor por venta de 30578.45 USDT', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(546, 1778863784187, '2026-05-15', '2026-05-15 16:49:44', 3, 'entrada', 'USDT', 30578.450000, NULL, 172, 133, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.663800, 'Registro venta: 30578.45 USDT a TC 17.6638 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(547, 1778864304205, '2026-05-15', '2026-05-15 16:58:24', 3, 'entrada', 'MXN', 500.000000, NULL, 36, 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.241379, 'Venta de 29 USDT a TC 17.241379', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(548, 1778864304205, '2026-05-15', '2026-05-15 16:58:24', 3, 'salida', 'USDT', 29.000000, 51, NULL, 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.241379, 'Salida de 29 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(549, 1778864304205, '2026-05-15', '2026-05-15 16:58:24', 3, 'comision', 'MXN', 2.650000, NULL, 1, 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.241379, 'Utilidad neta FIFO por venta de 29 USDT', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(550, 1778864304205, '2026-05-15', '2026-05-15 16:58:24', 3, 'entrada', 'USDT', 29.000000, NULL, 50, 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.241379, 'Registro venta: 29 USDT a TC 17.241379 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(551, 1778868863451, '2026-05-15', '2026-05-15 18:14:23', 6, 'entrada', 'USDT', 20000.000000, NULL, 51, NULL, NULL, 3, NULL, NULL, NULL, NULL, NULL, 17.200000, 'Compra de 20000 USDT a TC 17.2', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(552, 1778868863451, '2026-05-15', '2026-05-15 18:14:23', 6, 'salida', 'USDT', 20000.000000, NULL, 21, NULL, NULL, 3, NULL, NULL, NULL, NULL, NULL, 17.200000, 'Adeudo a proveedor: 20000 USDT (TC: 17.2)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(553, 1778871777540, '2026-05-15', '2026-05-15 19:02:57', 3, 'entrada', 'MXN', 197090.320000, NULL, 36, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 16.428300, 'Venta de 11997 USDT a TC 16.4283', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(554, 1778871777540, '2026-05-15', '2026-05-15 19:02:57', 3, 'salida', 'USDT', 11997.000000, 51, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 16.428300, 'Salida de 11997 USDT del wallet por venta', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(555, 1778871777540, '2026-05-15', '2026-05-15 19:02:57', 3, 'comision', 'MXN', -8658.230000, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 16.428300, 'Utilidad neta FIFO por venta de 11997 USDT', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(556, 1778871777540, '2026-05-15', '2026-05-15 19:02:57', 3, 'entrada', 'USDT', 11997.000000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 16.428300, 'Registro venta: 11997 USDT a TC 16.4283 al cliente', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(557, 1778871840739, '2026-05-15', '2026-05-15 19:04:00', 3, 'entrada', 'MXN', 208585.410000, NULL, 36, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.385290, 'Venta de 11997.81 USDT a TC 17.38529', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(558, 1778871840739, '2026-05-15', '2026-05-15 19:04:00', 3, 'salida', 'USDT', 11997.810000, 51, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.385290, 'Salida de 11997.81 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(559, 1778871840739, '2026-05-15', '2026-05-15 19:04:00', 3, 'comision', 'MXN', 2822.960000, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.385290, 'Utilidad neta FIFO por venta de 11997.81 USDT', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(560, 1778871840739, '2026-05-15', '2026-05-15 19:04:00', 3, 'entrada', 'USDT', 11997.810000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.385290, 'Registro venta: 11997.81 USDT a TC 17.38529 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(561, 1778873028720, '2026-05-15', '2026-05-15 19:23:48', 10, 'entrada', 'MXN', 4629.000000, NULL, 2, NULL, NULL, NULL, 7, NULL, 21, NULL, NULL, NULL, 'Préstamo de Valeria Garcia', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(562, 1778873037805, '2026-05-15', '2026-05-15 19:23:57', 14, 'salida', 'MXN', 4629.000000, 36, NULL, NULL, NULL, NULL, 7, NULL, 21, NULL, NULL, NULL, 'Abono manual a préstamo #21', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(563, 1778873067777, '2026-05-15', '2026-05-15 19:24:27', 12, 'salida', 'MXN', 30.000000, 36, NULL, NULL, NULL, NULL, NULL, 31, NULL, NULL, NULL, NULL, 'Gasto: Recargas - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(564, 1778873067777, '2026-05-15', '2026-05-15 19:24:27', 12, 'salida', 'MXN', 30.000000, 1, NULL, NULL, NULL, NULL, NULL, 31, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Recargas - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(565, 1778874164641, '2026-05-15', '2026-05-15 19:42:44', 4, 'traspaso', 'MXN', 7000.000000, 36, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(566, 1778891591567, '2026-05-15', '2026-05-16 00:33:11', 4, 'traspaso', 'MXN', 15000.000000, 34, 36, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(567, 1778892095884, '2026-05-15', '2026-05-16 00:41:35', 7, 'traspaso', 'MXN', 141200.000000, 34, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(568, 1778892142359, '2026-05-15', '2026-05-16 00:42:22', 12, 'salida', 'MXN', 81.320000, 34, NULL, NULL, NULL, NULL, NULL, 32, NULL, NULL, NULL, NULL, 'Gasto: Comisiones Rojas - ', 2, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-05-16 00:42:29'),
(569, 1778892142359, '2026-05-15', '2026-05-16 00:42:22', 12, 'salida', 'MXN', 81.320000, 1, NULL, NULL, NULL, NULL, NULL, 32, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Comisiones Rojas - ', 2, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-05-16 00:42:29'),
(570, 1778892142360, '2026-05-15', '2026-05-16 00:42:29', 12, 'ajuste', 'MXN', -81.320000, NULL, 34, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1778892142359 - Gasto: Comisiones Rojas - ', 2, 1, 'activo', 1, 568, 'Reversión del último movimiento', NULL, NULL),
(571, 1778892142360, '2026-05-15', '2026-05-16 00:42:29', 12, 'ajuste', 'MXN', -81.320000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1778892142359 - Gasto (desde Comisiones AMIRI): Comisiones Rojas - ', 2, 1, 'activo', 1, 569, 'Reversión del último movimiento', NULL, NULL),
(572, 1778892163556, '2026-05-15', '2026-05-16 00:42:43', 12, 'salida', 'MXN', 81.200000, 34, NULL, NULL, NULL, NULL, NULL, 33, NULL, NULL, NULL, NULL, 'Gasto: Comisiones Rojas - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(573, 1778892163556, '2026-05-15', '2026-05-16 00:42:43', 12, 'salida', 'MXN', 81.200000, 1, NULL, NULL, NULL, NULL, NULL, 33, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Comisiones Rojas - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(574, 1778892206888, '2026-05-15', '2026-05-16 00:43:26', 12, 'salida', 'MXN', 500.000000, 2, NULL, NULL, NULL, NULL, NULL, 34, NULL, NULL, NULL, NULL, 'Gasto: Gas Kia - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(575, 1778892206888, '2026-05-15', '2026-05-16 00:43:26', 12, 'salida', 'MXN', 500.000000, 1, NULL, NULL, NULL, NULL, NULL, 34, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Gas Kia - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(576, 1778892687084, '2026-05-15', '2026-05-16 00:51:27', 3, 'entrada', 'MXN', 145150.210000, NULL, 36, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.385382, 'Venta de 8348.98 USDT a TC 17.385382', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(577, 1778892687084, '2026-05-15', '2026-05-16 00:51:27', 3, 'salida', 'USDT', 8348.980000, 51, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.385382, 'Salida de 8348.98 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(578, 1778892687084, '2026-05-15', '2026-05-16 00:51:27', 3, 'comision', 'MXN', 1965.200000, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.385382, 'Utilidad neta FIFO por venta de 8348.98 USDT', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(579, 1778892687084, '2026-05-15', '2026-05-16 00:51:27', 3, 'entrada', 'USDT', 8348.980000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.385382, 'Registro venta: 8348.98 USDT a TC 17.385382 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(580, 1778893025553, '2026-05-15', '2026-05-16 00:57:05', 14, 'salida', 'MXN', 220844.000000, 36, NULL, NULL, NULL, NULL, 5, NULL, 16, NULL, NULL, NULL, 'Abono manual a préstamo #16', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(581, 1778893158439, '2026-05-16', '2026-05-16 00:59:18', 11, 'salida', 'MXN', 344000.000000, 2, NULL, NULL, NULL, 3, NULL, NULL, NULL, NULL, NULL, 17.200000, 'Pago a proveedor Alejandro Bambas — 20000 USDT (TC: 17.2)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(582, 1778893197025, '2026-05-15', '2026-05-16 00:59:57', 14, 'salida', 'MXN', 348460.630000, 2, NULL, NULL, NULL, NULL, 4, NULL, 20, NULL, NULL, NULL, 'Abono manual a préstamo #20', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(583, 1778893528541, '2026-05-15', '2026-05-16 01:05:28', 1, 'entrada', 'MXN', 105217.500000, NULL, 183, 139, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(584, 1778893528541, '2026-05-15', '2026-05-16 01:05:28', 1, 'comision', 'MXN', 743.960000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(585, 1778893528541, '2026-05-15', '2026-05-16 01:05:28', 1, 'comision', 'MXN', 318.840000, NULL, 182, NULL, 11, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(586, 1778893528541, '2026-05-15', '2026-05-16 01:05:28', 1, 'entrada', 'MXN', 106280.300000, NULL, 32, 139, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Ingreso por Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL);
INSERT INTO `movimientos` (`id`, `grupo_id`, `fecha`, `creado_en`, `servicio_id`, `tipo`, `moneda`, `monto`, `cuenta_origen_id`, `cuenta_destino_id`, `cliente_id`, `promotor_id`, `proveedor_id`, `prestamista_id`, `gasto_id`, `referencia_id`, `referencia_tipo`, `beneficiario`, `paridad`, `descripcion`, `usuario_id`, `activo`, `estado`, `es_reverso`, `movimiento_original_id`, `motivo_correccion`, `corregido_por_usuario_id`, `corregido_en`) VALUES
(587, 1779125602319, '2026-05-18', '2026-05-18 17:33:22', 10, 'entrada', 'MXN', 920.000000, NULL, 2, NULL, NULL, NULL, 7, NULL, 22, NULL, NULL, NULL, 'Préstamo de Valeria Garcia', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(588, 1779125625660, '2026-05-18', '2026-05-18 17:33:45', 14, 'salida', 'MXN', 920.000000, 36, NULL, NULL, NULL, NULL, 7, NULL, 22, NULL, NULL, NULL, 'Abono manual a préstamo #22', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(589, 1779125956487, '2026-05-18', '2026-05-18 17:39:16', 10, 'entrada', 'MXN', 200000.000000, NULL, 2, NULL, NULL, NULL, 3, NULL, 23, NULL, NULL, NULL, 'Préstamo de Diego Alfredo H', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(590, 1779126337698, '2026-05-18', '2026-05-18 17:45:37', 3, 'entrada', 'MXN', 230874.000000, NULL, 36, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.432215, 'Venta de 13244.1 USDT a TC 17.432215', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(591, 1779126337698, '2026-05-18', '2026-05-18 17:45:37', 3, 'salida', 'USDT', 13244.100000, 51, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.432215, 'Salida de 13244.1 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(592, 1779126337698, '2026-05-18', '2026-05-18 17:45:37', 3, 'comision', 'MXN', 3737.680000, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.432215, 'Utilidad neta FIFO por venta de 13244.1 USDT', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(593, 1779126337698, '2026-05-18', '2026-05-18 17:45:37', 3, 'entrada', 'USDT', 13244.100000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.432215, 'Registro venta: 13244.1 USDT a TC 17.432215 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(594, 1779136454671, '2026-05-18', '2026-05-18 20:34:14', 1, 'entrada', 'MXN', 158400.000000, NULL, 181, 138, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(595, 1779136454671, '2026-05-18', '2026-05-18 20:34:14', 1, 'comision', 'MXN', 1280.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(596, 1779136454671, '2026-05-18', '2026-05-18 20:34:14', 1, 'comision', 'MXN', 320.000000, NULL, 180, NULL, 10, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(597, 1779136454671, '2026-05-18', '2026-05-18 20:34:14', 1, 'entrada', 'MXN', 160000.000000, NULL, 34, 138, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Ingreso por Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(598, 1779144998670, '2026-05-18', '2026-05-18 22:56:38', 10, 'entrada', 'MXN', 200000.000000, NULL, 2, NULL, NULL, NULL, 6, NULL, 24, NULL, NULL, NULL, 'Préstamo de Yuyu', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(599, 1779145013564, '2026-05-18', '2026-05-18 22:56:53', 10, 'entrada', 'MXN', 308850.000000, NULL, 36, NULL, NULL, NULL, 6, NULL, 25, NULL, NULL, NULL, 'Préstamo de Yuyu', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(600, 1779145060933, '2026-05-18', '2026-05-18 22:57:40', 10, 'entrada', 'MXN', 600000.000000, NULL, 2, NULL, NULL, NULL, 4, NULL, 26, NULL, NULL, NULL, 'Préstamo de Hyperion', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(601, 1779145145714, '2026-05-18', '2026-05-18 22:59:05', 14, 'salida', 'MXN', 308850.000000, 2, NULL, NULL, NULL, NULL, 6, NULL, 25, NULL, NULL, NULL, 'Abono manual a préstamo #25', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(602, 1779145145714, '2026-05-18', '2026-05-18 22:59:05', 14, 'salida', 'MXN', 200000.000000, 2, NULL, NULL, NULL, NULL, 6, NULL, 24, NULL, NULL, NULL, 'Abono manual a préstamo #24', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(603, 1779145145714, '2026-05-18', '2026-05-18 22:59:05', 14, 'salida', 'MXN', 486000.000000, 2, NULL, NULL, NULL, NULL, 6, NULL, 17, NULL, NULL, NULL, 'Abono manual a préstamo #17', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(604, 1779145145714, '2026-05-18', '2026-05-18 22:59:05', 14, 'salida', 'MXN', 5150.000000, 2, NULL, NULL, NULL, NULL, 6, NULL, 18, NULL, NULL, NULL, 'Abono manual a préstamo #18', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(605, 1779145197458, '2026-05-18', '2026-05-18 22:59:57', 10, 'entrada', 'MXN', 100000.000000, NULL, 2, NULL, NULL, NULL, 6, NULL, 27, NULL, NULL, NULL, 'Préstamo de Yuyu', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(606, 1779145274279, '2026-05-18', '2026-05-18 23:01:14', 16, 'salida', 'MXN', 5000.000000, 34, NULL, NULL, NULL, NULL, NULL, NULL, 9, NULL, 'Nataly Azul', NULL, 'Préstamo a Nataly Azul', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(607, 1779145287413, '2026-05-18', '2026-05-18 23:01:27', 7, 'traspaso', 'MXN', 218500.000000, 34, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(608, 1779145896114, '2026-05-18', '2026-05-18 23:11:36', 13, 'salida', 'MXN', 105217.500000, 2, 183, 139, NULL, NULL, NULL, NULL, 30, NULL, NULL, 1.000000, 'Pago a Wb - Gas - Venta #30', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(609, 1779145972698, '2026-05-18', '2026-05-18 23:12:52', 15, 'salida', 'MXN', 1320.000000, 2, 180, NULL, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Alfonso Santos', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(610, 1779146588219, '2026-05-18', '2026-05-18 23:23:08', 3, 'entrada', 'MXN', 224528.050000, NULL, 32, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.473000, 'Venta de 12850 USDT a TC 17.473', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(611, 1779146588219, '2026-05-18', '2026-05-18 23:23:08', 3, 'salida', 'USDT', 12850.000000, 51, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.473000, 'Salida de 12850 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(612, 1779146588219, '2026-05-18', '2026-05-18 23:23:08', 3, 'comision', 'MXN', 4428.890000, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.473000, 'Utilidad neta FIFO por venta de 12850 USDT', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(613, 1779146588219, '2026-05-18', '2026-05-18 23:23:08', 3, 'entrada', 'USDT', 12850.000000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.473000, 'Registro venta: 12850 USDT a TC 17.473 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(614, 1779146739167, '2026-05-18', '2026-05-18 23:25:39', 3, 'entrada', 'MXN', 20000.000000, NULL, 36, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.345452, 'Venta de 1153.04 USDT a TC 17.345452', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(615, 1779146739167, '2026-05-18', '2026-05-18 23:25:39', 3, 'salida', 'USDT', 1153.040000, 51, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.345452, 'Salida de 1153.04 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(616, 1779146739167, '2026-05-18', '2026-05-18 23:25:39', 3, 'comision', 'MXN', 167.710000, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.345452, 'Utilidad neta FIFO por venta de 1153.04 USDT', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(617, 1779146739167, '2026-05-18', '2026-05-18 23:25:39', 3, 'entrada', 'USDT', 1153.040000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.345452, 'Registro venta: 1153.04 USDT a TC 17.345452 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(618, 1779147388334, '2026-05-18', '2026-05-18 23:36:28', 1, 'entrada', 'MXN', 10822.190000, NULL, 176, 136, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(619, 1779147388334, '2026-05-18', '2026-05-18 23:36:28', 1, 'comision', 'MXN', 109.870000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(620, 1779147388334, '2026-05-18', '2026-05-18 23:36:28', 1, 'comision', 'MXN', 54.940000, NULL, 175, NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(621, 1779147388334, '2026-05-18', '2026-05-18 23:36:28', 1, 'entrada', 'MXN', 10987.000000, NULL, 32, 136, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Ingreso por Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(622, 1779147403678, '2026-05-18', '2026-05-18 23:36:43', 4, 'traspaso', 'MXN', 340000.000000, 32, 36, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(623, 1779147584447, '2026-05-18', '2026-05-18 23:39:44', 14, 'salida', 'MXN', 329156.000000, 36, NULL, NULL, NULL, NULL, 5, NULL, 16, NULL, NULL, NULL, 'Abono manual a préstamo #16', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(624, 1779147962048, '2026-05-18', '2026-05-18 23:46:02', 12, 'salida', 'MXN', 43057.690000, 2, NULL, NULL, NULL, NULL, NULL, 35, NULL, NULL, NULL, NULL, 'Gasto: Comisiones Bancarias - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(625, 1779147962048, '2026-05-18', '2026-05-18 23:46:02', 12, 'salida', 'MXN', 43057.690000, 1, NULL, NULL, NULL, NULL, NULL, 35, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Comisiones Bancarias - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(626, 1779147973737, '2026-05-18', '2026-05-18 23:46:13', 15, 'salida', 'MXN', 2867.310000, 2, 53, NULL, 8, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Ana Maria - Maestros Joyeros', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(627, 1779210230246, '2026-05-19', '2026-05-19 17:03:50', 1, 'entrada', 'MXN', 700000.100000, NULL, 186, 141, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(628, 1779210230246, '2026-05-19', '2026-05-19 17:03:50', 1, 'comision', 'MXN', 7106.600000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(629, 1779210230246, '2026-05-19', '2026-05-19 17:03:50', 1, 'comision', 'MXN', 3553.300000, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(630, 1779210230246, '2026-05-19', '2026-05-19 17:03:50', 1, 'entrada', 'MXN', 710660.000000, NULL, 32, 141, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Ingreso por Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(631, 1779214188570, '2026-05-19', '2026-05-19 18:09:48', 4, 'traspaso', 'MXN', 700000.000000, 32, 34, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(632, 1779214370298, '2026-05-19', '2026-05-19 18:12:50', 4, 'traspaso', 'MXN', 210000.000000, 36, 34, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(633, 1779214392657, '2026-05-19', '2026-05-19 18:13:12', 7, 'traspaso', 'MXN', 436900.000000, 34, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(634, 1779214444865, '2026-05-19', '2026-05-19 18:14:04', 12, 'salida', 'MXN', 330.400000, 34, NULL, NULL, NULL, NULL, NULL, 36, NULL, NULL, NULL, NULL, 'Gasto: Comisiones Rojas - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(635, 1779214444865, '2026-05-19', '2026-05-19 18:14:04', 12, 'salida', 'MXN', 330.400000, 1, NULL, NULL, NULL, NULL, NULL, 36, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Comisiones Rojas - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(636, 1779216509864, '2026-05-19', '2026-05-19 18:48:29', 12, 'salida', 'MXN', 81.200000, 34, NULL, NULL, NULL, NULL, NULL, 37, NULL, NULL, NULL, NULL, 'Gasto: Comisiones Rojas - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(637, 1779216509864, '2026-05-19', '2026-05-19 18:48:29', 12, 'salida', 'MXN', 81.200000, 1, NULL, NULL, NULL, NULL, NULL, 37, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Comisiones Rojas - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(638, 1779218294191, '2026-05-19', '2026-05-19 19:18:14', 15, 'salida', 'MXN', 3553.300000, 36, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(639, 1779228098250, '2026-05-19', '2026-05-19 22:01:38', 10, 'entrada', 'MXN', 81000.000000, NULL, 2, NULL, NULL, NULL, 6, NULL, 28, NULL, NULL, NULL, 'Préstamo de Yuyu', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(640, 1779228160083, '2026-05-19', '2026-05-19 22:02:40', 10, 'entrada', 'MXN', 174150.000000, NULL, 36, NULL, NULL, NULL, 6, NULL, 29, NULL, NULL, NULL, 'Préstamo de Yuyu', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(641, 1779228203686, '2026-05-19', '2026-05-19 22:03:23', 14, 'salida', 'MXN', 174150.000000, 36, NULL, NULL, NULL, NULL, 6, NULL, 29, NULL, NULL, NULL, 'Abono manual a préstamo #29', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(642, 1779228203686, '2026-05-19', '2026-05-19 22:03:23', 14, 'salida', 'MXN', 81000.000000, 36, NULL, NULL, NULL, NULL, 6, NULL, 28, NULL, NULL, NULL, 'Abono manual a préstamo #28', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(643, 1779228203686, '2026-05-19', '2026-05-19 22:03:23', 14, 'salida', 'MXN', 100000.000000, 36, NULL, NULL, NULL, NULL, 6, NULL, 27, NULL, NULL, NULL, 'Abono manual a préstamo #27', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(644, 1779228203686, '2026-05-19', '2026-05-19 22:03:23', 14, 'salida', 'MXN', 144850.000000, 36, NULL, NULL, NULL, NULL, 6, NULL, 18, NULL, NULL, NULL, 'Abono manual a préstamo #18', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_promotores`
--

CREATE TABLE `pagos_promotores` (
  `id` int(11) NOT NULL,
  `promotor_id` int(11) NOT NULL,
  `cuenta_pago_id` int(11) NOT NULL COMMENT 'Cuenta desde donde se pagó (caja/banco empresa)',
  `cuenta_promotor_id` int(11) NOT NULL COMMENT 'Cuenta del promotor donde se registra',
  `monto_pagado` decimal(12,2) NOT NULL,
  `fecha_pago` date NOT NULL,
  `movimiento_id` bigint(20) DEFAULT NULL COMMENT 'Referencia al movimiento',
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagos_promotores`
--

INSERT INTO `pagos_promotores` (`id`, `promotor_id`, `cuenta_pago_id`, `cuenta_promotor_id`, `monto_pagado`, `fecha_pago`, `movimiento_id`, `descripcion`, `activo`, `creado_en`) VALUES
(1, 6, 36, 24, 532.50, '2026-04-23', 48, 'Pago de comisiones pendientes', 1, '2026-04-23 23:48:35'),
(2, 6, 36, 24, 213.00, '2026-04-28', 127, 'Pago de comisiones pendientes', 1, '2026-04-28 17:57:53'),
(3, 6, 36, 24, 5329.00, '2026-04-29', 176, 'Pago de comisiones pendientes', 0, '2026-04-29 17:48:23'),
(4, 6, 36, 24, 2666.51, '2026-04-29', 178, 'Pago de comisiones pendientes', 0, '2026-04-29 18:00:38'),
(5, 6, 36, 24, 2666.50, '2026-04-29', 180, 'Pago de comisiones pendientes', 1, '2026-04-29 18:01:04'),
(6, 9, 36, 175, 89.48, '2026-04-29', 185, 'Pago de comisiones', 1, '2026-04-29 18:16:02'),
(7, 7, 36, 25, 512.00, '2026-04-29', 210, 'Pago de comisiones', 1, '2026-04-30 00:19:40'),
(8, 6, 36, 24, 2056.83, '2026-04-30', 247, 'Pago de comisiones', 1, '2026-04-30 23:29:49'),
(9, 9, 36, 175, 262.87, '2026-04-30', 248, 'Pago de comisiones', 1, '2026-04-30 23:33:05'),
(10, 9, 36, 175, 90.93, '2026-05-05', 293, 'Pago de comisiones', 1, '2026-05-05 16:46:39'),
(11, 6, 36, 24, 1522.84, '2026-05-05', 294, 'Pago de comisiones', 1, '2026-05-05 16:47:07'),
(12, 9, 36, 175, 222.73, '2026-05-05', 312, 'Pago de comisiones', 1, '2026-05-06 00:29:55'),
(13, 9, 36, 175, 16.06, '2026-05-05', 319, 'Pago de comisiones', 1, '2026-05-06 00:34:03'),
(14, 6, 36, 24, 1329.85, '2026-05-06', 359, 'Pago de comisiones', 1, '2026-05-06 21:56:36'),
(15, 6, 36, 24, 3807.11, '2026-05-06', 382, 'Pago de comisiones', 1, '2026-05-07 00:18:10'),
(16, 7, 2, 25, 220.00, '2026-05-13', 507, 'Pago de comisiones', 1, '2026-05-13 21:08:13'),
(17, 11, 36, 182, 723.00, '2026-05-13', 508, 'Pago de comisiones', 1, '2026-05-13 22:30:53'),
(18, 12, 2, 184, 240.00, '2026-05-14', 516, 'Pago de comisiones', 1, '2026-05-14 19:03:34'),
(19, 10, 2, 180, 1320.00, '2026-05-18', 609, 'Pago de comisiones', 1, '2026-05-18 23:12:52'),
(20, 8, 2, 53, 2867.31, '2026-05-18', 626, 'Pago de comisiones', 1, '2026-05-18 23:46:13'),
(21, 6, 36, 24, 3553.30, '2026-05-19', 638, 'Pago de comisiones', 1, '2026-05-19 19:18:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prestamistas`
--

CREATE TABLE `prestamistas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  `eliminado_en` timestamp NULL DEFAULT NULL,
  `eliminado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `prestamistas`
--

INSERT INTO `prestamistas` (`id`, `nombre`, `telefono`, `email`, `direccion`, `activo`, `creado_en`, `eliminado_en`, `eliminado_por`) VALUES
(3, 'Diego Alfredo H', '33 3317 1974', 'diego@albardi.mx', 'Socio Amiri', 1, '2026-03-26 23:58:17', NULL, NULL),
(4, 'Hyperion', '33 2726 8440', 'hyperion@gmail.com', '', 1, '2026-03-26 23:59:16', NULL, NULL),
(5, 'Alejandro Mora', '33 3576 6064', 'alexmora@gmail.com', '', 1, '2026-05-13 20:59:39', NULL, NULL),
(6, 'Yuyu', '33 3149 6323', 'yuyu@gmail.com', '', 1, '2026-05-14 17:17:09', NULL, NULL),
(7, 'Valeria Garcia', '33 3127 2849', 'valgarcia@gmail.com', '', 1, '2026-05-15 00:37:45', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prestamos_otorgados`
--

CREATE TABLE `prestamos_otorgados` (
  `id` int(11) NOT NULL,
  `persona_nombre` varchar(150) NOT NULL COMMENT 'A quién se le prestó el dinero',
  `cuenta_id` int(11) DEFAULT NULL COMMENT 'Caja desde donde salió el préstamo',
  `monto_total` decimal(12,2) NOT NULL COMMENT 'Capital original entregado',
  `monto_pagado` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Dinero que ya te han devuelto',
  `estado` enum('pendiente','parcial','pagado') NOT NULL DEFAULT 'pendiente',
  `fecha_prestamo` date NOT NULL COMMENT 'Fecha en que se otorgó el préstamo',
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `prestamos_otorgados`
--

INSERT INTO `prestamos_otorgados` (`id`, `persona_nombre`, `cuenta_id`, `monto_total`, `monto_pagado`, `estado`, `fecha_prestamo`, `descripcion`, `activo`, `creado_en`, `actualizado_en`) VALUES
(1, 'Citlaly Azul', 34, 3990.00, 0.00, 'pendiente', '2026-05-04', NULL, 1, '2026-05-04 18:19:36', '2026-05-04 18:19:36'),
(2, 'Citlaly Azul', 36, 2000.00, 0.00, 'pendiente', '2026-05-06', NULL, 0, '2026-05-06 00:45:33', '2026-05-06 00:47:55'),
(3, 'Citlaly Azul', 34, 2000.00, 0.00, 'pendiente', '2026-05-06', NULL, 1, '2026-05-06 21:45:26', '2026-05-06 21:45:26'),
(4, 'Citlaly Azul', 34, 600.00, 0.00, 'pendiente', '2026-05-11', NULL, 0, '2026-05-11 18:15:03', '2026-05-11 18:21:27'),
(5, 'Citlaly Azul', 34, 3600.00, 0.00, 'pendiente', '2026-05-11', NULL, 1, '2026-05-11 18:23:00', '2026-05-11 18:23:00'),
(6, 'Nataly Azul', 34, 1000.00, 0.00, 'pendiente', '2026-05-11', NULL, 1, '2026-05-11 18:23:17', '2026-05-11 18:23:17'),
(7, 'Nataly Azul', 34, 2500.00, 0.00, 'pendiente', '2026-05-12', NULL, 1, '2026-05-12 20:43:08', '2026-05-12 20:43:08'),
(8, 'Nataly Azul', 34, 5000.00, 0.00, 'pendiente', '2026-05-15', NULL, 1, '2026-05-15 00:39:09', '2026-05-15 00:39:09'),
(9, 'Nataly Azul', 34, 5000.00, 0.00, 'pendiente', '2026-05-18', NULL, 1, '2026-05-18 23:01:14', '2026-05-18 23:01:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `promotores`
--

CREATE TABLE `promotores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `cuenta_id` int(11) DEFAULT NULL COMMENT 'Cuenta donde acumulan comisiones',
  `es_interno` tinyint(1) DEFAULT 0 COMMENT 'Indica si es un promotor interno de Amiri',
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  `eliminado_en` timestamp NULL DEFAULT NULL,
  `eliminado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `promotores`
--

INSERT INTO `promotores` (`id`, `nombre`, `telefono`, `email`, `cuenta_id`, `es_interno`, `activo`, `creado_en`, `eliminado_en`, `eliminado_por`) VALUES
(1, 'AMIRI DIRECTO', 'N/A', 'interno@amiri.com', 1, 1, 1, '2026-03-13 01:37:15', NULL, NULL),
(6, 'Kike Rizo', '33 1716 2178', 'kikerizo@gmail.com', 24, 0, 1, '2026-03-27 00:04:23', NULL, NULL),
(7, 'Missael Jauregui', '33 3167 7268', 'misajauregui@gmail.com', 25, 0, 1, '2026-03-27 00:05:33', NULL, NULL),
(8, 'Ana Maria - Maestros Joyeros', 'N / A', 'anamaria@gmail.com', 53, 0, 1, '2026-04-23 23:42:11', NULL, NULL),
(9, 'Adrian Sandoval', '33 2298 9014', 'adriansando@gmail.com', 175, 0, 1, '2026-04-29 17:54:42', NULL, NULL),
(10, 'Alfonso Santos', '33 1918 6813', 'alfonsosantos@gmail.com', 180, 0, 1, '2026-05-08 16:54:33', NULL, NULL),
(11, 'Ernesto Sandoval', '33 2454 4597', 'netosando@GMAIL.COM', 182, 0, 1, '2026-05-13 01:03:00', NULL, NULL),
(12, 'Emiliano Hurtado', '33 1847 4338', 'grupoamiri2@gmail.com', 184, 0, 1, '2026-05-14 19:00:17', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `tipo_moneda` enum('USD','USDT') NOT NULL DEFAULT 'USD' COMMENT 'Tipo de moneda principal del proveedor',
  `acepta_usd` tinyint(1) NOT NULL DEFAULT 0,
  `acepta_usdt` tinyint(1) NOT NULL DEFAULT 0,
  `cuenta_id` int(11) DEFAULT NULL COMMENT 'Cuenta original (legacy)',
  `cuenta_usd_id` int(11) DEFAULT NULL,
  `cuenta_usdt_id` int(11) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  `eliminado_en` timestamp NULL DEFAULT NULL,
  `eliminado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id`, `nombre`, `email`, `telefono`, `tipo_moneda`, `acepta_usd`, `acepta_usdt`, `cuenta_id`, `cuenta_usd_id`, `cuenta_usdt_id`, `activo`, `creado_en`, `eliminado_en`, `eliminado_por`) VALUES
(2, 'Emilio Agraz', 'emilioagraz@gmail.com', '33 1544 0424', 'USD', 1, 1, NULL, 18, 19, 1, '2026-03-26 23:59:58', NULL, NULL),
(3, 'Alejandro Bambas', 'alejandrobambas@gmail.com', '33 3492 2267', 'USD', 1, 1, NULL, 20, 21, 1, '2026-03-27 00:01:27', NULL, NULL),
(4, 'Alejandro Bambas', 'alejandrobambas@gmail.com', '33 3492 2267', 'USD', 1, 1, NULL, 20, 21, 0, '2026-03-27 00:01:27', NULL, NULL),
(5, 'Daniel Casa Cambio', 'danielcc@gmail.com', '33 2212 3796', 'USD', 1, 1, NULL, 22, 23, 1, '2026-03-27 00:02:33', NULL, NULL),
(6, 'Daniel Casa Cambio', 'danielcc@gmail.com', '33 2212 3796', 'USD', 1, 1, NULL, 22, 23, 0, '2026-03-27 00:02:33', NULL, NULL),
(7, 'Jorge De La Vega', 'jorgedelavega@gmail.com', 'N / A', 'USD', 1, 1, NULL, 40, 41, 1, '2026-04-03 23:06:28', NULL, NULL),
(8, 'Binance', 'Binance@gmail.com', 'N / A', 'USD', 1, 1, NULL, 43, 44, 1, '2026-04-03 23:32:20', NULL, NULL),
(9, 'Missael Jauregui', 'misajauregui@gmail.com', '33 3167 7268', 'USD', 1, 1, NULL, 45, 46, 1, '2026-04-04 00:42:36', NULL, NULL),
(10, 'Alfonso Santos', 'alfonsosantos@gmail.com', '33 1918 6813', 'USD', 1, 1, NULL, 178, 179, 0, '2026-05-08 16:53:42', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo_movimiento` enum('entrada','salida') NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`id`, `nombre`, `descripcion`, `tipo_movimiento`, `activo`, `creado_en`, `actualizado_en`) VALUES
(1, 'Venta de Efectivo', 'Venta de efectivo a clientes', 'entrada', 1, '2026-03-13 01:37:15', '2026-03-13 01:37:15'),
(2, 'Venta de Dolares', 'Venta de dólares a clientes', 'entrada', 1, '2026-03-13 01:37:15', '2026-03-13 01:37:15'),
(3, 'Venta de USDT', 'Venta de USDT a clientes', 'entrada', 1, '2026-03-13 01:37:15', '2026-03-13 01:37:15'),
(4, 'Cambio de Flujo', 'Movimientos entre cuentas sin cambiar capital', 'salida', 1, '2026-03-13 01:37:15', '2026-03-13 01:37:15'),
(5, 'Compra de Dolares', 'Compra de dólares a clientes', 'salida', 1, '2026-03-13 01:37:15', '2026-03-13 01:37:15'),
(6, 'Compra de USDT', 'Compra de USDT a clientes', 'salida', 1, '2026-03-13 01:37:15', '2026-03-13 01:37:15'),
(7, 'Retiro de Efectivo', 'Retiro de efectivo por el cliente', 'salida', 1, '2026-03-13 01:37:15', '2026-03-13 01:37:15'),
(10, 'Préstamo de Prestamista', 'Préstamo recibido de prestamista', 'entrada', 1, '2026-03-13 01:37:15', '2026-03-13 01:37:15'),
(11, 'Pago a Proveedor', 'Pago realizado a proveedor', 'salida', 1, '2026-03-13 01:37:15', '2026-03-13 01:37:15'),
(12, 'Gasto', 'Gasto operativo', 'salida', 1, '2026-03-13 01:37:15', '2026-03-13 01:37:15'),
(13, 'Pago a Cliente', 'Pago realizado a cliente', 'salida', 1, '2026-03-13 01:37:15', '2026-03-13 01:37:15'),
(14, 'Pago de Préstamo', 'Pago de préstamo a prestamista', 'salida', 1, '2026-03-13 01:37:15', '2026-03-13 01:37:15'),
(15, 'Pago de Comisión a Promotor', 'Pago de comisión a promotor', 'salida', 1, '2026-03-13 01:37:15', '2026-03-13 01:37:15'),
(16, 'Préstamo Otorgado', 'Salida de dinero por préstamo a terceros', 'salida', 1, '2026-05-04 22:00:57', '2026-05-04 22:00:57'),
(17, 'Cobro de Préstamo', 'Entrada de dinero por cobro de préstamo', 'entrada', 1, '2026-05-04 22:00:57', '2026-05-04 22:00:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_cuenta`
--

CREATE TABLE `tipos_cuenta` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipos_cuenta`
--

INSERT INTO `tipos_cuenta` (`id`, `descripcion`) VALUES
(1, 'Bancaria'),
(2, 'Caja'),
(3, 'Capital'),
(4, 'Cliente'),
(5, 'Promotor'),
(6, 'Proveedor');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trusted_devices`
--

CREATE TABLE `trusted_devices` (
  `id` bigint(20) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `device_token` varchar(255) NOT NULL COMMENT 'Token único del dispositivo',
  `device_name` varchar(100) NOT NULL COMMENT 'Nombre del dispositivo (ej: Chrome Windows)',
  `ip_address` varchar(45) NOT NULL COMMENT 'IP de donde se confió',
  `user_agent` varchar(255) NOT NULL COMMENT 'User agent del navegador',
  `trusted_at` timestamp NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Expires 24 horas después',
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Dispositivos confiables por 24 horas para saltarse 2FA';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `two_fa_attempts`
--

CREATE TABLE `two_fa_attempts` (
  `id` bigint(20) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `failed_attempts` int(11) NOT NULL DEFAULT 1,
  `first_attempt_at` timestamp NULL DEFAULT current_timestamp(),
  `last_attempt_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `blocked_until` timestamp NULL DEFAULT NULL COMMENT 'Bloqueado hasta esta hora si hay demasiados intentos',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Rastreo de intentos fallidos de 2FA';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','prestamista','usuario') DEFAULT 'usuario',
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires_at` datetime DEFAULT NULL,
  `two_fa_enabled` tinyint(1) DEFAULT 0 COMMENT 'Indica si 2FA está habilitado',
  `two_fa_secret` varchar(255) DEFAULT NULL COMMENT 'Secret key para TOTP (encriptado)',
  `two_fa_backup_codes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array de códigos de respaldo' CHECK (json_valid(`two_fa_backup_codes`)),
  `two_fa_created_at` timestamp NULL DEFAULT NULL COMMENT 'Cuándo se configuró 2FA'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol`, `activo`, `creado_en`, `reset_token`, `reset_expires_at`, `two_fa_enabled`, `two_fa_secret`, `two_fa_backup_codes`, `two_fa_created_at`) VALUES
(1, 'Administrador', 'admin@amiri.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, '2026-03-13 01:37:14', NULL, NULL, 0, NULL, NULL, NULL),
(2, 'Emiliano', 'grupoamiri2@gmail.com', '$2y$12$l3qZ.Qso3mGJfXAaOQfmzOQHcdtMAESZvSRvhdhybE55dJ1eUafQG', 'admin', 1, '2026-03-18 15:35:14', NULL, NULL, 0, NULL, NULL, NULL),
(3, 'Diego', 'diego@albardi.mx', '$2y$12$GHRNNpnBPM8pQ6tyBEOi9er7x2wGwUjgAJpbHShiwTDY2IR1HmoJq', 'admin', 1, '2026-03-24 16:25:44', NULL, NULL, 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `v_deudas_clientes_pendientes`
--

CREATE TABLE `v_deudas_clientes_pendientes` (
  `id` int(11) DEFAULT NULL,
  `cliente` varchar(100) DEFAULT NULL,
  `monto_total` decimal(12,2) DEFAULT NULL,
  `monto_pagado` decimal(12,2) DEFAULT NULL,
  `saldo_pendiente` decimal(13,2) DEFAULT NULL,
  `estado` enum('pendiente','pagado','parcial') DEFAULT NULL,
  `fecha` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `v_deudas_empresa_pendientes`
--

CREATE TABLE `v_deudas_empresa_pendientes` (
  `id` int(11) DEFAULT NULL,
  `prestamista` varchar(100) DEFAULT NULL,
  `monto` decimal(12,2) DEFAULT NULL,
  `monto_pagado` decimal(12,2) DEFAULT NULL,
  `saldo_pendiente` decimal(13,2) DEFAULT NULL,
  `estado` enum('pendiente','pagado','parcial') DEFAULT NULL,
  `fecha` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `v_saldos_cuentas`
--

CREATE TABLE `v_saldos_cuentas` (
  `id` int(11) DEFAULT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `moneda` varchar(10) DEFAULT NULL,
  `saldo_actual` decimal(12,2) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_accion` (`accion`),
  ADD KEY `idx_modulo` (`modulo`),
  ADD KEY `idx_tabla_registro` (`tabla`,`registro_id`),
  ADD KEY `idx_fecha` (`created_at`),
  ADD KEY `idx_modulo_accion` (`modulo`,`accion`),
  ADD KEY `idx_usuario_fecha` (`usuario_id`,`created_at`);

--
-- Indices de la tabla `categorias_gasto`
--
ALTER TABLE `categorias_gasto`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cliente_promotor` (`promotor_id`),
  ADD KEY `fk_cliente_cuenta` (`cuenta_id`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `consumos_inventario`
--
ALTER TABLE `consumos_inventario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_movimiento` (`movimiento_id`),
  ADD KEY `idx_inventario` (`inventario_id`);

--
-- Indices de la tabla `cuentas`
--
ALTER TABLE `cuentas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `fk_tipo_cuenta` (`tipo_cuenta`),
  ADD KEY `idx_activo_tipo` (`activo`,`tipo_cuenta`),
  ADD KEY `idx_tipo_moneda` (`tipo_moneda`),
  ADD KEY `idx_tipo_activo_moneda` (`tipo_cuenta`,`activo`,`tipo_moneda`),
  ADD KEY `idx_tipo_valor` (`tipo_cuenta`,`valor_mxn`);

--
-- Indices de la tabla `deudas_clientes`
--
ALTER TABLE `deudas_clientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cliente` (`cliente_id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_fecha` (`fecha`),
  ADD KEY `idx_movimiento_venta` (`movimiento_venta_id`),
  ADD KEY `idx_movimiento_pago` (`movimiento_pago_id`),
  ADD KEY `idx_estado_activo` (`estado`,`activo`),
  ADD KEY `idx_cliente_estado` (`cliente_id`,`estado`);

--
-- Indices de la tabla `deudas_empresa`
--
ALTER TABLE `deudas_empresa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_deuda_prestamista` (`prestamista_id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_fecha` (`fecha`),
  ADD KEY `idx_estado_activo` (`estado`,`activo`),
  ADD KEY `idx_prestamista_estado` (`prestamista_id`,`estado`);

--
-- Indices de la tabla `directorio_depositos`
--
ALTER TABLE `directorio_depositos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `gastos`
--
ALTER TABLE `gastos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_gastos_movimiento` (`movimiento_id`),
  ADD KEY `fk_gastos_categoria` (`categoria_id`),
  ADD KEY `idx_fecha` (`fecha`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `historial_saldos`
--
ALTER TABLE `historial_saldos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cuenta` (`cuenta_id`),
  ADD KEY `idx_movimiento` (`movimiento_id`),
  ADD KEY `idx_fecha` (`created_at`),
  ADD KEY `idx_cuenta_fecha` (`cuenta_id`,`created_at`);

--
-- Indices de la tabla `inventario_monedas`
--
ALTER TABLE `inventario_monedas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_movimiento` (`movimiento_id`),
  ADD KEY `idx_moneda_activo` (`moneda`,`activo`),
  ADD KEY `idx_cuenta` (`cuenta_id`);

--
-- Indices de la tabla `movimientos`
--
ALTER TABLE `movimientos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fecha` (`fecha`),
  ADD KEY `idx_servicio` (`servicio_id`),
  ADD KEY `idx_moneda` (`moneda`),
  ADD KEY `idx_origen` (`cuenta_origen_id`),
  ADD KEY `idx_destino` (`cuenta_destino_id`),
  ADD KEY `idx_cliente` (`cliente_id`),
  ADD KEY `idx_proveedor` (`proveedor_id`),
  ADD KEY `idx_grupo_id` (`grupo_id`),
  ADD KEY `idx_promotor_tipo` (`promotor_id`,`tipo`),
  ADD KEY `idx_tipo_descripcion` (`tipo`,`descripcion`(20)),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_prestamista` (`prestamista_id`),
  ADD KEY `idx_gasto` (`gasto_id`),
  ADD KEY `idx_activo` (`activo`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_fecha_activo` (`fecha`,`activo`),
  ADD KEY `idx_tipo_activo` (`tipo`,`activo`),
  ADD KEY `idx_movimiento_original` (`movimiento_original_id`);

--
-- Indices de la tabla `pagos_promotores`
--
ALTER TABLE `pagos_promotores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_promotor` (`promotor_id`),
  ADD KEY `idx_cuenta_pago` (`cuenta_pago_id`),
  ADD KEY `idx_cuenta_promotor` (`cuenta_promotor_id`),
  ADD KEY `idx_movimiento` (`movimiento_id`),
  ADD KEY `idx_fecha` (`fecha_pago`),
  ADD KEY `idx_promotor_fecha` (`promotor_id`,`fecha_pago`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `prestamistas`
--
ALTER TABLE `prestamistas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `prestamos_otorgados`
--
ALTER TABLE `prestamos_otorgados`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `promotores`
--
ALTER TABLE `promotores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `fk_promotor_cuenta` (`cuenta_id`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_proveedor_cuenta` (`cuenta_id`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `tipos_cuenta`
--
ALTER TABLE `tipos_cuenta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `descripcion` (`descripcion`);

--
-- Indices de la tabla `trusted_devices`
--
ALTER TABLE `trusted_devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_token` (`device_token`),
  ADD KEY `idx_usuario_id` (`usuario_id`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `idx_usuario_activo` (`usuario_id`,`expires_at`);

--
-- Indices de la tabla `two_fa_attempts`
--
ALTER TABLE `two_fa_attempts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_ip` (`usuario_id`,`ip_address`),
  ADD KEY `idx_usuario_id` (`usuario_id`),
  ADD KEY `idx_ip_address` (`ip_address`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=378;

--
-- AUTO_INCREMENT de la tabla `categorias_gasto`
--
ALTER TABLE `categorias_gasto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- AUTO_INCREMENT de la tabla `consumos_inventario`
--
ALTER TABLE `consumos_inventario`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de la tabla `cuentas`
--
ALTER TABLE `cuentas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=188;

--
-- AUTO_INCREMENT de la tabla `deudas_clientes`
--
ALTER TABLE `deudas_clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `deudas_empresa`
--
ALTER TABLE `deudas_empresa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `directorio_depositos`
--
ALTER TABLE `directorio_depositos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT de la tabla `gastos`
--
ALTER TABLE `gastos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `historial_saldos`
--
ALTER TABLE `historial_saldos`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=213;

--
-- AUTO_INCREMENT de la tabla `inventario_monedas`
--
ALTER TABLE `inventario_monedas`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `movimientos`
--
ALTER TABLE `movimientos`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=645;

--
-- AUTO_INCREMENT de la tabla `pagos_promotores`
--
ALTER TABLE `pagos_promotores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `prestamistas`
--
ALTER TABLE `prestamistas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `prestamos_otorgados`
--
ALTER TABLE `prestamos_otorgados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `promotores`
--
ALTER TABLE `promotores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `tipos_cuenta`
--
ALTER TABLE `tipos_cuenta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `trusted_devices`
--
ALTER TABLE `trusted_devices`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `two_fa_attempts`
--
ALTER TABLE `two_fa_attempts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
