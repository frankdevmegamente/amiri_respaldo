-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 14-04-2026 a las 10:40:41
-- Versión del servidor: 10.6.24-MariaDB-cll-lve-log
-- Versión de PHP: 8.4.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `guiaspar_amiri_produccion`
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
(1, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '159.26.100.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-01 19:45:14'),
(2, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '159.26.100.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-01 19:46:08'),
(3, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '159.26.100.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-01 20:02:01'),
(4, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '159.26.100.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-01 20:05:03'),
(5, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-01 22:28:22'),
(6, 2, 'INSERT', 'prestamos', 'deudas_empresa', 1, NULL, '{\"id\":\"1\",\"prestamista_id\":\"3\",\"prestamista_nombre\":\"Diego Alfredo H\",\"cuenta_id\":\"32\",\"monto\":1000000,\"fecha\":\"2026-01-01 16:29:25\",\"descripcion\":\"Inicio\"}', 'Registró préstamo: $1,000,000.00 de Diego Alfredo H', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-01 22:29:25'),
(7, 2, 'INSERT', 'prestamos', 'deudas_empresa', 2, NULL, '{\"id\":\"2\",\"prestamista_id\":\"4\",\"prestamista_nombre\":\"Hyperion\",\"cuenta_id\":\"2\",\"monto\":1000000,\"fecha\":\"2026-04-01 16:29:52\",\"descripcion\":\"Inicio\"}', 'Registró préstamo: $1,000,000.00 de Hyperion', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-01 22:29:53'),
(8, 2, 'DELETE', 'prestamos', 'deudas_empresa', 2, '{\"id\":\"2\",\"prestamista_id\":\"4\",\"cuenta_id\":\"2\",\"monto\":\"1000000.00\",\"fecha\":\"2026-04-01\",\"descripcion\":\"Inicio\",\"estado\":\"pendiente\",\"monto_pagado\":\"0.00\",\"fecha_pago\":null,\"activo\":\"1\",\"creado_en\":\"2026-04-01 16:29:53\",\"actualizado_en\":\"2026-04-01 16:29:53\",\"prestamista_nombre\":\"Hyperion\"}', NULL, 'Desactivó préstamo de Hyperion por $1,000,000.00', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-01 22:29:57'),
(9, 2, 'INSERT', 'prestamos', 'deudas_empresa', 3, NULL, '{\"id\":\"3\",\"prestamista_id\":\"4\",\"prestamista_nombre\":\"Hyperion\",\"cuenta_id\":\"2\",\"monto\":1000000,\"fecha\":\"2026-01-01 16:30:16\",\"descripcion\":\"Inicio\"}', 'Registró préstamo: $1,000,000.00 de Hyperion', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-01 22:30:16'),
(10, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1775082913377,\"movimientos\":4}', '{\"nuevo_grupo\":\"1775082913378\",\"reversos\":[\"19\",\"20\",\"21\",\"22\"]}', 'Anuló grupo de movimientos #1775082913377 (Reversión del último movimiento). 4 movimientos anulados.', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-01 22:35:23'),
(11, 2, 'INSERT', 'cuentas', 'cuentas', 38, NULL, '{\"id\":\"38\",\"nombre\":\"Ledger Stax\",\"numero_cuenta\":null,\"banco\":null,\"tipo_cuenta\":2,\"tipo_moneda\":\"USDT\",\"activo\":1,\"es_caja_empresa\":1}', 'Creó cuenta: Ledger Stax (USDT)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-01 22:38:24'),
(12, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.163.55.141', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-01 23:33:41'),
(13, 1, 'INSERT', 'prestamos', 'deudas_empresa', 4, NULL, '{\"id\":\"4\",\"prestamista_id\":\"4\",\"prestamista_nombre\":\"Hyperion\",\"cuenta_id\":\"2\",\"monto\":10,\"fecha\":\"2026-04-01 17:38:13\",\"descripcion\":\"\"}', 'Registró préstamo: $10.00 de Hyperion', '189.163.55.141', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-01 23:38:14'),
(14, 1, 'DELETE', 'prestamos', 'deudas_empresa', 4, '{\"id\":\"4\",\"prestamista_id\":\"4\",\"cuenta_id\":\"2\",\"monto\":\"10.00\",\"fecha\":\"2026-04-01\",\"descripcion\":\"\",\"estado\":\"pendiente\",\"monto_pagado\":\"0.00\",\"fecha_pago\":null,\"activo\":\"1\",\"creado_en\":\"2026-04-01 17:38:14\",\"actualizado_en\":\"2026-04-01 17:38:14\",\"prestamista_nombre\":\"Hyperion\"}', NULL, 'Eliminó préstamo de Hyperion por $10.00 y restó el saldo de la caja automáticamente.', '189.163.55.141', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-01 23:38:27'),
(15, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '189.163.55.141', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-01 23:48:15'),
(16, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 00:48:14'),
(17, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1775092623336,\"movimientos\":1}', '{\"nuevo_grupo\":\"1775092623337\",\"reversos\":[\"34\"]}', 'Anuló grupo de movimientos #1775092623336 (Reversión del último movimiento). 1 movimientos anulados.', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 01:17:37'),
(18, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1775092580144,\"movimientos\":1}', '{\"nuevo_grupo\":\"1775092623338\",\"reversos\":[\"35\"]}', 'Anuló grupo de movimientos #1775092580144 (Reversión del último movimiento). 1 movimientos anulados.', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 01:17:42'),
(19, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1775092918597,\"movimientos\":1}', '{\"nuevo_grupo\":\"1775092918598\",\"reversos\":[\"46\"]}', 'Anuló grupo de movimientos #1775092918597 (Reversión del último movimiento). 1 movimientos anulados.', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 01:22:17'),
(20, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.163.170.233', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 01:24:22'),
(21, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1775093120355,\"movimientos\":1}', '{\"nuevo_grupo\":\"1775093120356\",\"reversos\":[\"53\"]}', 'Anuló grupo de movimientos #1775093120355 (Reversión del último movimiento). 1 movimientos anulados.', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 01:25:43'),
(22, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1775093089970,\"movimientos\":3}', '{\"nuevo_grupo\":\"1775093120357\",\"reversos\":[\"54\",\"55\",\"56\"]}', 'Anuló grupo de movimientos #1775093089970 (Reversión del último movimiento). 3 movimientos anulados.', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 01:25:49'),
(23, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 21:00:38'),
(24, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1775166865752,\"movimientos\":3}', '{\"nuevo_grupo\":\"1775166865753\",\"reversos\":[\"75\",\"76\",\"77\"]}', 'Anuló grupo de movimientos #1775166865752 (Reversión del último movimiento). 3 movimientos anulados.', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 21:54:42'),
(25, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 22:01:32'),
(26, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1775167965706,\"movimientos\":1}', '{\"nuevo_grupo\":\"1775167965707\",\"reversos\":[\"111\"]}', 'Anuló grupo de movimientos #1775167965706 (Reversión del último movimiento). 1 movimientos anulados.', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 22:12:58'),
(27, 2, 'INSERT', 'gastos', 'gastos', 1, NULL, '{\"id\":\"1\",\"fecha\":\"2026-03-02\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"7\",\"clasificacion\":\"fijo\",\"monto\":\"6290.00\",\"concepto\":\"Comisiones Rojas Febrero\",\"activo\":\"1\",\"creado_en\":\"2026-04-02 16:37:10\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Comisiones Rojas\"}', 'Registró gasto: $6,290.00 - Comisiones Rojas', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 22:37:10'),
(28, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1775170122539,\"movimientos\":1}', '{\"nuevo_grupo\":\"1775170122540\",\"reversos\":[\"192\"]}', 'Anuló grupo de movimientos #1775170122539 (Reversión del último movimiento). 1 movimientos anulados.', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 22:48:46'),
(29, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1775170518996,\"movimientos\":4}', '{\"nuevo_grupo\":\"1775170518997\",\"reversos\":[\"209\",\"210\",\"211\",\"212\"]}', 'Anuló grupo de movimientos #1775170518996 (Reversión del último movimiento). 4 movimientos anulados.', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 22:55:44'),
(30, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1775170632414,\"movimientos\":1}', '{\"nuevo_grupo\":\"1775170632415\",\"reversos\":[\"219\"]}', 'Anuló grupo de movimientos #1775170632414 (Reversión del último movimiento). 1 movimientos anulados.', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 22:57:24'),
(31, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1775170705688,\"movimientos\":1}', '{\"nuevo_grupo\":\"1775170705689\",\"reversos\":[\"221\"]}', 'Anuló grupo de movimientos #1775170705688 (Reversión del último movimiento). 1 movimientos anulados.', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 22:58:41'),
(32, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1775170737172,\"movimientos\":1}', '{\"nuevo_grupo\":\"1775170737173\",\"reversos\":[\"223\"]}', 'Anuló grupo de movimientos #1775170737172 (Reversión del último movimiento). 1 movimientos anulados.', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 22:59:12'),
(33, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 23:02:08'),
(34, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1775171188160,\"movimientos\":1}', '{\"nuevo_grupo\":\"1775171188161\",\"reversos\":[\"239\"]}', 'Anuló grupo de movimientos #1775171188160 (Reversión del último movimiento). 1 movimientos anulados.', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 23:07:08'),
(35, 2, 'INSERT', 'clientes', 'clientes', 11, NULL, '{\"nombre\":\"King Of Watches\",\"contacto\":\"33 3391 9485\",\"promotor_id\":7,\"retencion_total\":1.5,\"porcentaje_amiri\":1.3,\"porcentaje_promotor\":0.2,\"opera_mxn\":1,\"opera_usd\":1,\"opera_usdt\":1,\"id\":\"11\"}', 'Creó cliente: King Of Watches', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 23:13:54'),
(36, 2, 'INSERT', 'clientes', 'cuentas', 39, NULL, '{\"id\":\"39\",\"nombre\":\"Caja - King Of Watches\",\"tipo_moneda\":null}', 'Creó cuenta automática para cliente: Caja - King Of Watches', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 23:13:54'),
(37, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1775172449728,\"movimientos\":1}', '{\"nuevo_grupo\":\"1775172449729\",\"reversos\":[\"287\"]}', 'Anuló grupo de movimientos #1775172449728 (Reversión del último movimiento). 1 movimientos anulados.', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 23:27:47'),
(38, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1775172402124,\"movimientos\":1}', '{\"nuevo_grupo\":\"1775172449730\",\"reversos\":[\"288\"]}', 'Anuló grupo de movimientos #1775172402124 (Reversión del último movimiento). 1 movimientos anulados.', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 23:27:52'),
(39, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1775172384684,\"movimientos\":1}', '{\"nuevo_grupo\":\"1775172449731\",\"reversos\":[\"289\"]}', 'Anuló grupo de movimientos #1775172384684 (Reversión del último movimiento). 1 movimientos anulados.', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 23:27:59'),
(40, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1775172355181,\"movimientos\":4}', '{\"nuevo_grupo\":\"1775172449732\",\"reversos\":[\"290\",\"291\",\"292\",\"293\"]}', 'Anuló grupo de movimientos #1775172355181 (Reversión del último movimiento). 4 movimientos anulados.', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 23:28:05'),
(41, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1775172328111,\"movimientos\":1}', '{\"nuevo_grupo\":\"1775172449733\",\"reversos\":[\"294\"]}', 'Anuló grupo de movimientos #1775172328111 (Reversión del último movimiento). 1 movimientos anulados.', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 23:28:29'),
(42, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1775172255416,\"movimientos\":1}', '{\"nuevo_grupo\":\"1775172449734\",\"reversos\":[\"295\"]}', 'Anuló grupo de movimientos #1775172255416 (Reversión del último movimiento). 1 movimientos anulados.', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 23:29:03'),
(43, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1775172231541,\"movimientos\":4}', '{\"nuevo_grupo\":\"1775172449735\",\"reversos\":[\"296\",\"297\",\"298\",\"299\"]}', 'Anuló grupo de movimientos #1775172231541 (Reversión del último movimiento). 4 movimientos anulados.', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 23:29:07'),
(44, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.163.170.233', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 02:40:46'),
(45, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.163.170.233', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 06:22:41'),
(46, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 22:52:40'),
(47, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":1500,\"tipo_cambio\":18.04,\"cliente_id\":null,\"proveedor_id\":2}', 'Compra de USDT: 1500 USDT (TC: 18.04)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 23:00:33'),
(48, 2, 'INSERT', 'proveedores', 'proveedores', 7, NULL, '{\"proveedor_id\":\"7\",\"nombre\":\"Jorge De La Vega\",\"email\":\"jorgedelavega@gmail.com\",\"telefono\":\"N \\/ A\",\"acepta_usd\":1,\"acepta_usdt\":1}', 'Creó proveedor: Jorge De La Vega', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 23:06:28'),
(49, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":70000,\"tipo_cambio\":17.8239,\"cliente_id\":null,\"proveedor_id\":7}', 'Compra de USDT: 70000 USDT (TC: 17.8239)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 23:08:54'),
(50, 2, 'INSERT', 'clientes', 'clientes', 12, NULL, '{\"nombre\":\"Binance\",\"contacto\":\"N\\/A\",\"promotor_id\":1,\"retencion_total\":0,\"porcentaje_amiri\":0,\"porcentaje_promotor\":0,\"opera_mxn\":1,\"opera_usd\":1,\"opera_usdt\":1,\"id\":\"12\"}', 'Creó cliente: Binance', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 23:16:11'),
(51, 2, 'INSERT', 'clientes', 'cuentas', 42, NULL, '{\"id\":\"42\",\"nombre\":\"Caja - Binance\",\"tipo_moneda\":null}', 'Creó cuenta automática para cliente: Caja - Binance', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 23:16:11'),
(52, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":1500,\"tipo_cambio\":18.04,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 1500 USDT (TC: 18.04)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 23:19:01'),
(53, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":4291,\"tipo_cambio\":17.8,\"cliente_id\":null,\"proveedor_id\":2}', 'Compra de USDT: 4291 USDT (TC: 17.8)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 23:20:50'),
(54, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":2000,\"tipo_cambio\":17.85,\"cliente_id\":null,\"proveedor_id\":2}', 'Compra de USDT: 2000 USDT (TC: 17.85)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 23:21:32'),
(55, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":3500,\"tipo_cambio\":18.2478,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 3500 USDT (TC: 18.2478)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 23:24:49'),
(56, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":27295.07,\"tipo_cambio\":18.0707,\"cliente_id\":9,\"proveedor_id\":null}', 'Venta de USDT: 27295.07 USDT (TC: 18.0707)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 23:27:55'),
(57, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":34520.54,\"tipo_cambio\":17.9728,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 34520.54 USDT (TC: 17.9728)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 23:30:18'),
(58, 2, 'INSERT', 'proveedores', 'proveedores', 8, NULL, '{\"proveedor_id\":\"8\",\"nombre\":\"Binance\",\"email\":\"Binance@gmail.com\",\"telefono\":\"N \\/ A\",\"acepta_usd\":1,\"acepta_usdt\":1}', 'Creó proveedor: Binance', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 23:32:20'),
(59, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":15505.7,\"tipo_cambio\":17.74,\"cliente_id\":null,\"proveedor_id\":8}', 'Compra de USDT: 15505.7 USDT (TC: 17.74)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 23:32:59'),
(60, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":26403.33,\"tipo_cambio\":17.8807,\"cliente_id\":9,\"proveedor_id\":null}', 'Venta de USDT: 26403.33 USDT (TC: 17.8807)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 23:35:14'),
(61, 2, 'DELETE', 'flujo_divisas', 'movimientos', 2147483647, NULL, NULL, 'Eliminación de movimientos grupo #1775259314473', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 23:35:31'),
(62, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":26403.33,\"tipo_cambio\":17.8807,\"cliente_id\":9,\"proveedor_id\":null}', 'Venta de USDT: 26403.33 USDT (TC: 17.8807)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 23:36:19'),
(63, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":10296,\"tipo_cambio\":17.65,\"cliente_id\":null,\"proveedor_id\":8}', 'Compra de USDT: 10296 USDT (TC: 17.65)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 23:37:25'),
(64, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":10373.76,\"tipo_cambio\":17.785,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 10373.76 USDT (TC: 17.785)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 23:39:50'),
(65, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":43149.01,\"tipo_cambio\":17.58,\"cliente_id\":null,\"proveedor_id\":8}', 'Compra de USDT: 43149.01 USDT (TC: 17.58)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 23:40:48'),
(66, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":43149.01,\"tipo_cambio\":17.6843,\"cliente_id\":9,\"proveedor_id\":null}', 'Venta de USDT: 43149.01 USDT (TC: 17.6843)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 23:43:24'),
(67, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":14367,\"tipo_cambio\":17.3,\"cliente_id\":null,\"proveedor_id\":8}', 'Compra de USDT: 14367 USDT (TC: 17.3)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 23:44:20'),
(68, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":14367,\"tipo_cambio\":17.4985,\"cliente_id\":10,\"proveedor_id\":null}', 'Venta de USDT: 14367 USDT (TC: 17.4985)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 23:46:46'),
(69, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":3396.86,\"tipo_cambio\":17.25,\"cliente_id\":null,\"proveedor_id\":2}', 'Compra de USDT: 3396.86 USDT (TC: 17.25)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 23:49:09'),
(70, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":26983.87,\"tipo_cambio\":17.4715,\"cliente_id\":null,\"proveedor_id\":8}', 'Compra de USDT: 26983.87 USDT (TC: 17.4715)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 23:51:57'),
(71, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 23:53:11'),
(72, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":30380.73,\"tipo_cambio\":17.6615,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 30380.73 USDT (TC: 17.6615)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 23:55:53'),
(73, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":2061.56,\"tipo_cambio\":17.94,\"cliente_id\":null,\"proveedor_id\":8}', 'Compra de USDT: 2061.56 USDT (TC: 17.94)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 23:57:42'),
(74, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":2061.56,\"tipo_cambio\":18,\"cliente_id\":10,\"proveedor_id\":null}', 'Venta de USDT: 2061.56 USDT (TC: 18)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 00:00:33'),
(75, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":10923,\"tipo_cambio\":17.41,\"cliente_id\":null,\"proveedor_id\":8}', 'Compra de USDT: 10923 USDT (TC: 17.41)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 00:01:37'),
(76, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":5937.5,\"tipo_cambio\":17.5916,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 5937.5 USDT (TC: 17.5916)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 00:02:46'),
(77, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":4985.5,\"tipo_cambio\":17.6111,\"cliente_id\":9,\"proveedor_id\":null}', 'Venta de USDT: 4985.5 USDT (TC: 17.6111)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 00:03:36'),
(78, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":50889,\"tipo_cambio\":17.0666,\"cliente_id\":null,\"proveedor_id\":2}', 'Compra de USDT: 50889 USDT (TC: 17.0666)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 00:16:25'),
(79, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":23751,\"tipo_cambio\":17.3,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 23751 USDT (TC: 17.3)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 00:20:49'),
(80, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":27138,\"tipo_cambio\":17.5009,\"cliente_id\":9,\"proveedor_id\":null}', 'Venta de USDT: 27138 USDT (TC: 17.5009)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 00:21:58'),
(81, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":29997,\"tipo_cambio\":17.26,\"cliente_id\":null,\"proveedor_id\":8}', 'Compra de USDT: 29997 USDT (TC: 17.26)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 00:25:09'),
(82, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":29997,\"tipo_cambio\":17.3984,\"cliente_id\":9,\"proveedor_id\":null}', 'Venta de USDT: 29997 USDT (TC: 17.3984)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 00:28:03'),
(83, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":47738.84,\"tipo_cambio\":17.2725,\"cliente_id\":null,\"proveedor_id\":8}', 'Compra de USDT: 47738.84 USDT (TC: 17.2725)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 00:32:50'),
(84, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":32053.13,\"tipo_cambio\":17.5519,\"cliente_id\":9,\"proveedor_id\":null}', 'Venta de USDT: 32053.13 USDT (TC: 17.5519)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 00:35:47'),
(85, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":15685.71,\"tipo_cambio\":17.5,\"cliente_id\":10,\"proveedor_id\":null}', 'Venta de USDT: 15685.71 USDT (TC: 17.5)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 00:36:55'),
(86, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":61048.82,\"tipo_cambio\":17.3,\"cliente_id\":null,\"proveedor_id\":8}', 'Compra de USDT: 61048.82 USDT (TC: 17.3)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 00:38:11'),
(87, 2, 'DELETE', 'flujo_divisas', 'movimientos', 2147483647, NULL, NULL, 'Eliminación de movimientos grupo #1775263091843', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 00:38:58'),
(88, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":61048.82,\"tipo_cambio\":17.3,\"cliente_id\":null,\"proveedor_id\":8}', 'Compra de USDT: 61048.82 USDT (TC: 17.3)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 00:39:38'),
(89, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":61048.82,\"tipo_cambio\":17.5621,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 61048.82 USDT (TC: 17.5621)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 00:40:58'),
(90, 2, 'INSERT', 'proveedores', 'proveedores', 9, NULL, '{\"proveedor_id\":\"9\",\"nombre\":\"Missael Jauregui\",\"email\":\"misajauregui@gmail.com\",\"telefono\":\"33 3167 7268\",\"acepta_usd\":1,\"acepta_usdt\":1}', 'Creó proveedor: Missael Jauregui', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 00:42:36'),
(91, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":6000,\"tipo_cambio\":17.29,\"cliente_id\":null,\"proveedor_id\":8}', 'Compra de USDT: 6000 USDT (TC: 17.29)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 00:43:42'),
(92, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":6000,\"tipo_cambio\":17.5,\"cliente_id\":10,\"proveedor_id\":null}', 'Venta de USDT: 6000 USDT (TC: 17.5)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 00:44:42'),
(93, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":39821,\"tipo_cambio\":17.7,\"cliente_id\":null,\"proveedor_id\":2}', 'Compra de USDT: 39821 USDT (TC: 17.7)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 00:45:51'),
(94, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":10000,\"tipo_cambio\":17.5,\"cliente_id\":null,\"proveedor_id\":3}', 'Compra de USDT: 10000 USDT (TC: 17.5)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 00:48:02'),
(95, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":32387.67,\"tipo_cambio\":17.9588,\"cliente_id\":9,\"proveedor_id\":null}', 'Venta de USDT: 32387.67 USDT (TC: 17.9588)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 00:50:11'),
(96, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":891.6,\"tipo_cambio\":17.5,\"cliente_id\":null,\"proveedor_id\":2}', 'Compra de USDT: 891.6 USDT (TC: 17.5)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 00:52:09'),
(97, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":6953.73,\"tipo_cambio\":17.6,\"cliente_id\":null,\"proveedor_id\":3}', 'Compra de USDT: 6953.73 USDT (TC: 17.6)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 00:53:06'),
(98, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 00:54:53'),
(99, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":5983.24,\"tipo_cambio\":17.9,\"cliente_id\":10,\"proveedor_id\":null}', 'Venta de USDT: 5983.24 USDT (TC: 17.9)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 00:56:33'),
(100, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":7500,\"tipo_cambio\":17.6,\"cliente_id\":null,\"proveedor_id\":2}', 'Compra de USDT: 7500 USDT (TC: 17.6)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 00:58:24'),
(101, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":6169.38,\"tipo_cambio\":18.1115,\"cliente_id\":9,\"proveedor_id\":null}', 'Venta de USDT: 6169.38 USDT (TC: 18.1115)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 01:00:27'),
(102, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":20626.04,\"tipo_cambio\":17.735,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 20626.04 USDT (TC: 17.735)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 01:01:40'),
(103, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":72000,\"tipo_cambio\":17.6,\"cliente_id\":null,\"proveedor_id\":3}', 'Compra de USDT: 72000 USDT (TC: 17.6)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 01:03:10'),
(104, 2, 'DELETE', 'flujo_divisas', 'movimientos', 2147483647, NULL, NULL, 'Eliminación de movimientos grupo #1775264590492', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 01:16:26'),
(105, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":72000,\"tipo_cambio\":17.6,\"cliente_id\":null,\"proveedor_id\":3}', 'Compra de USDT: 72000 USDT (TC: 17.6)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 01:19:44'),
(106, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":5500,\"tipo_cambio\":18.0318,\"cliente_id\":10,\"proveedor_id\":null}', 'Venta de USDT: 5500 USDT (TC: 18.0318)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 01:21:24'),
(107, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":1329.71,\"tipo_cambio\":17.8804,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 1329.71 USDT (TC: 17.8804)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 01:22:27'),
(108, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":1720,\"tipo_cambio\":17.6392,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 1720 USDT (TC: 17.6392)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 01:24:53'),
(109, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":8869,\"tipo_cambio\":17.5,\"cliente_id\":null,\"proveedor_id\":2}', 'Compra de USDT: 8869 USDT (TC: 17.5)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 01:26:10'),
(110, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":35412.68,\"tipo_cambio\":17.81,\"cliente_id\":9,\"proveedor_id\":null}', 'Venta de USDT: 35412.68 USDT (TC: 17.81)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 01:28:25'),
(111, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":22290.92,\"tipo_cambio\":17.7153,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 22290.92 USDT (TC: 17.7153)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 01:30:32'),
(112, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":12100,\"tipo_cambio\":17.91,\"cliente_id\":null,\"proveedor_id\":8}', 'Compra de USDT: 12100 USDT (TC: 17.91)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 01:31:37'),
(113, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":10000,\"tipo_cambio\":17.6,\"cliente_id\":null,\"proveedor_id\":2}', 'Compra de USDT: 10000 USDT (TC: 17.6)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 01:32:42'),
(114, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":36714.69,\"tipo_cambio\":17.927,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 36714.69 USDT (TC: 17.927)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 01:34:17'),
(115, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":21029,\"tipo_cambio\":17.97,\"cliente_id\":null,\"proveedor_id\":8}', 'Compra de USDT: 21029 USDT (TC: 17.97)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 01:35:22'),
(116, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":21030,\"tipo_cambio\":18.2029,\"cliente_id\":9,\"proveedor_id\":null}', 'Venta de USDT: 21030 USDT (TC: 18.2029)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 01:36:27'),
(117, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":62682,\"tipo_cambio\":17.7,\"cliente_id\":null,\"proveedor_id\":2}', 'Compra de USDT: 62682 USDT (TC: 17.7)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 01:37:22'),
(118, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":30338.48,\"tipo_cambio\":17.9444,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 30338.48 USDT (TC: 17.9444)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 01:39:24'),
(119, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":32343.52,\"tipo_cambio\":18.2125,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 32343.52 USDT (TC: 18.2125)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 01:40:49'),
(120, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 01:42:38'),
(121, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":40000,\"tipo_cambio\":17.66,\"cliente_id\":null,\"proveedor_id\":2}', 'Compra de USDT: 40000 USDT (TC: 17.66)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 01:49:21'),
(122, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":34492.56,\"tipo_cambio\":18.1085,\"cliente_id\":9,\"proveedor_id\":null}', 'Venta de USDT: 34492.56 USDT (TC: 18.1085)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 01:52:48'),
(123, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":139000,\"tipo_cambio\":17.6,\"cliente_id\":null,\"proveedor_id\":3}', 'Compra de USDT: 139000 USDT (TC: 17.6)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 01:53:47'),
(124, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 01:56:20'),
(125, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":54673.37,\"tipo_cambio\":17.7568,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 54673.37 USDT (TC: 17.7568)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 01:56:54'),
(126, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":46810.65,\"tipo_cambio\":17.7581,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 46810.65 USDT (TC: 17.7581)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 02:00:05'),
(127, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":19600,\"tipo_cambio\":17.75,\"cliente_id\":null,\"proveedor_id\":2}', 'Compra de USDT: 19600 USDT (TC: 17.75)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 02:00:57'),
(128, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":26829.15,\"tipo_cambio\":18.0058,\"cliente_id\":9,\"proveedor_id\":null}', 'Venta de USDT: 26829.15 USDT (TC: 18.0058)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 02:02:40');
INSERT INTO `auditoria` (`id`, `usuario_id`, `accion`, `modulo`, `tabla`, `registro_id`, `datos_anteriores`, `datos_nuevos`, `descripcion`, `ip_address`, `user_agent`, `created_at`) VALUES
(129, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":35794.27,\"tipo_cambio\":17.8605,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 35794.27 USDT (TC: 17.8605)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 02:03:49'),
(130, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":5800,\"tipo_cambio\":17.9,\"cliente_id\":null,\"proveedor_id\":2}', 'Compra de USDT: 5800 USDT (TC: 17.9)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 02:05:50'),
(131, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":5800,\"tipo_cambio\":18.0997,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 5800 USDT (TC: 18.0997)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 02:07:14'),
(132, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":80000,\"tipo_cambio\":17.9,\"cliente_id\":null,\"proveedor_id\":3}', 'Compra de USDT: 80000 USDT (TC: 17.9)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 02:08:30'),
(133, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":25600,\"tipo_cambio\":18.4269,\"cliente_id\":9,\"proveedor_id\":null}', 'Venta de USDT: 25600 USDT (TC: 18.4269)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 02:10:17'),
(134, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":34266.29,\"tipo_cambio\":18.1434,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 34266.29 USDT (TC: 18.1434)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 02:11:17'),
(135, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":10000,\"tipo_cambio\":17.8,\"cliente_id\":null,\"proveedor_id\":2}', 'Compra de USDT: 10000 USDT (TC: 17.8)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 02:12:54'),
(136, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":60500,\"tipo_cambio\":17.8,\"cliente_id\":null,\"proveedor_id\":2}', 'Compra de USDT: 60500 USDT (TC: 17.8)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 02:13:59'),
(137, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":34060.35,\"tipo_cambio\":18.3556,\"cliente_id\":9,\"proveedor_id\":null}', 'Venta de USDT: 34060.35 USDT (TC: 18.3556)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 02:15:03'),
(138, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":49846.64,\"tipo_cambio\":17.9235,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 49846.64 USDT (TC: 17.9235)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 02:15:55'),
(139, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":1354,\"tipo_cambio\":17.6916,\"cliente_id\":null,\"proveedor_id\":2}', 'Compra de USDT: 1354 USDT (TC: 17.6916)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 02:17:36'),
(140, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":6726.78,\"tipo_cambio\":17.8744,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 6726.78 USDT (TC: 17.8744)', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 02:19:14'),
(141, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-06 16:30:56'),
(142, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-06 19:57:16'),
(143, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-07 15:10:20'),
(144, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-07 16:39:20'),
(145, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-07 17:30:00'),
(146, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-07 21:15:32'),
(147, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.163.55.141', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-07 21:49:23'),
(148, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-07 23:11:39'),
(149, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.163.55.141', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-08 00:12:41'),
(150, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-08 16:35:24'),
(151, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '187.191.8.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-08 17:20:57'),
(152, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.163.55.141', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-08 17:48:56'),
(153, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '189.163.55.141', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-08 17:49:50'),
(154, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-08 20:11:29'),
(155, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":44782.38,\"tipo_cambio\":17.3,\"cliente_id\":null,\"proveedor_id\":3}', 'Compra de USDT: 44782.38 USDT (TC: 17.3)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-08 20:17:24'),
(156, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.163.55.141', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-08 21:04:37'),
(157, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.163.55.141', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-08 22:20:23'),
(158, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '189.163.55.141', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-08 22:39:12'),
(159, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.163.55.141', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-09 21:15:48'),
(160, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '189.163.55.141', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-09 21:16:45'),
(161, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.163.55.141', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 23:30:55'),
(162, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '189.163.55.141', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 23:48:26'),
(163, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '187.191.8.207', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_4_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/147.0.7727.47 Mobile/15E148 Safari/604.1', '2026-04-11 16:01:02'),
(164, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '172.225.14.151', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.4 Mobile/15E148 Safari/604.1', '2026-04-11 16:03:40'),
(165, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-13 17:12:41'),
(166, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-13 21:11:49'),
(167, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":20484.48,\"tipo_cambio\":18.081,\"cliente_id\":9,\"proveedor_id\":null}', 'Venta de USDT: 20484.48 USDT (TC: 18.081)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-13 21:21:55'),
(168, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":21132.38,\"tipo_cambio\":17.4262,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 21132.38 USDT (TC: 17.4262)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-13 21:23:03'),
(169, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":105000,\"tipo_cambio\":17.15,\"cliente_id\":null,\"proveedor_id\":null}', 'Compra de USDT: 105000 USDT (TC: 17.15)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-13 21:25:37'),
(170, 2, 'DELETE', 'flujo_divisas', 'movimientos', 2147483647, NULL, NULL, 'Eliminación de movimientos grupo #1776115537542', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-13 21:25:56'),
(171, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Compra de USDT\",\"moneda\":\"USDT\",\"cantidad\":105000,\"tipo_cambio\":17.15,\"cliente_id\":null,\"proveedor_id\":3}', 'Compra de USDT: 105000 USDT (TC: 17.15)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-13 21:26:24'),
(172, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":44416.56,\"tipo_cambio\":17.3713,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 44416.56 USDT (TC: 17.3713)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-13 21:28:54'),
(173, 2, 'INSERT', 'flujo_divisas', 'movimientos', 2147483647, NULL, '{\"servicio\":\"Venta de USDT\",\"moneda\":\"USDT\",\"cantidad\":55573.57,\"tipo_cambio\":17.3896,\"cliente_id\":12,\"proveedor_id\":null}', 'Venta de USDT: 55573.57 USDT (TC: 17.3896)', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-13 21:37:07'),
(174, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-13 22:27:41'),
(175, 2, 'LOGIN', 'auth', 'usuarios', 2, NULL, NULL, 'Usuario \'Emiliano\' inició sesión', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:01:43'),
(176, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:01:51'),
(177, 2, 'INSERT', 'gastos', 'gastos', 2, NULL, '{\"id\":\"2\",\"fecha\":\"2026-01-04\",\"cuenta_id\":\"32\",\"movimiento_id\":null,\"categoria_id\":\"8\",\"clasificacion\":\"fijo\",\"monto\":\"120000.00\",\"concepto\":\"Diego\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:11:32\",\"cuenta_nombre\":\"Makalan Sa De Cv\",\"tipo_gasto_nombre\":\"Pago Amiri - Diego\"}', 'Registró gasto: $120,000.00 - Pago Amiri - Diego', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:11:32'),
(178, 2, 'INSERT', 'gastos', 'gastos', 3, NULL, '{\"id\":\"3\",\"fecha\":\"2026-01-01\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"10\",\"clasificacion\":\"variable\",\"monto\":\"2000.00\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:12:47\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Pago Alondra\"}', 'Registró gasto: $2,000.00 - Pago Alondra', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:12:47'),
(179, 2, 'ANULAR_GRUPO', 'flujo', 'movimientos', 2147483647, '{\"grupo_id\":1776125567014,\"movimientos\":2}', '{\"nuevo_grupo\":\"1776125567015\",\"reversos\":[\"619\",\"620\"]}', 'Anuló grupo de movimientos #1776125567014 (Reversión del último movimiento). 2 movimientos anulados.', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:14:23'),
(180, 2, 'INSERT', 'gastos', 'gastos', 4, NULL, '{\"id\":\"4\",\"fecha\":\"2026-01-12\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"8\",\"clasificacion\":\"fijo\",\"monto\":\"45000.00\",\"concepto\":\"Se Los Mande A Areli\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:15:25\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Pago Amiri - Diego\"}', 'Registró gasto: $45,000.00 - Pago Amiri - Diego', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:15:25'),
(181, 2, 'INSERT', 'gastos', 'gastos', 5, NULL, '{\"id\":\"5\",\"fecha\":\"2026-01-12\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"9\",\"clasificacion\":\"fijo\",\"monto\":\"25170.04\",\"concepto\":\"Emiliano\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:16:16\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Pago Amiri - Emiliano\"}', 'Registró gasto: $25,170.04 - Pago Amiri - Emiliano', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:16:16'),
(182, 2, 'INSERT', 'gastos', 'gastos', 6, NULL, '{\"id\":\"6\",\"fecha\":\"2026-01-19\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"9\",\"clasificacion\":\"fijo\",\"monto\":\"20932.48\",\"concepto\":\"Diego\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:17:21\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Pago Amiri - Emiliano\"}', 'Registró gasto: $20,932.48 - Pago Amiri - Emiliano', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:17:21'),
(183, 2, 'INSERT', 'gastos', 'gastos', 7, NULL, '{\"id\":\"7\",\"fecha\":\"2026-01-26\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"11\",\"clasificacion\":\"variable\",\"monto\":\"21050.00\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:18:30\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Cuentas Pro Amiri\"}', 'Registró gasto: $21,050.00 - Cuentas Pro Amiri', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:18:30'),
(184, 2, 'INSERT', 'gastos', 'gastos', 8, NULL, '{\"id\":\"8\",\"fecha\":\"2026-01-26\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"11\",\"clasificacion\":\"variable\",\"monto\":\"21050.00\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:18:30\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Cuentas Pro Amiri\"}', 'Registró gasto: $21,050.00 - Cuentas Pro Amiri', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:18:30'),
(185, 2, 'INSERT', 'gastos', 'gastos', 9, NULL, '{\"id\":\"9\",\"fecha\":\"2026-01-26\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"12\",\"clasificacion\":\"variable\",\"monto\":\"4500.00\",\"concepto\":\"Kia\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:18:57\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Peeter\"}', 'Registró gasto: $4,500.00 - Peeter', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:18:57'),
(186, 2, 'INSERT', 'gastos', 'gastos', 10, NULL, '{\"id\":\"10\",\"fecha\":\"2026-01-29\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"10\",\"clasificacion\":\"variable\",\"monto\":\"1400.00\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:19:44\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Pago Alondra\"}', 'Registró gasto: $1,400.00 - Pago Alondra', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:19:44'),
(187, 2, 'INSERT', 'gastos', 'gastos', 11, NULL, '{\"id\":\"11\",\"fecha\":\"2026-02-03\",\"cuenta_id\":\"32\",\"movimiento_id\":null,\"categoria_id\":\"7\",\"clasificacion\":\"fijo\",\"monto\":\"6290.00\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:20:54\",\"cuenta_nombre\":\"Makalan Sa De Cv\",\"tipo_gasto_nombre\":\"Comisiones Rojas\"}', 'Registró gasto: $6,290.00 - Comisiones Rojas', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:20:54'),
(188, 2, 'INSERT', 'gastos', 'gastos', 12, NULL, '{\"id\":\"12\",\"fecha\":\"2026-02-11\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"4\",\"clasificacion\":\"fijo\",\"monto\":\"1000.00\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:21:23\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Recargas\"}', 'Registró gasto: $1,000.00 - Recargas', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:21:23'),
(189, 2, 'INSERT', 'gastos', 'gastos', 13, NULL, '{\"id\":\"13\",\"fecha\":\"2026-02-04\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"6\",\"clasificacion\":\"variable\",\"monto\":\"14000.00\",\"concepto\":\"Multa\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:21:58\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Comisiones Bancarias\"}', 'Registró gasto: $14,000.00 - Comisiones Bancarias', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:21:58'),
(190, 2, 'INSERT', 'gastos', 'gastos', 14, NULL, '{\"id\":\"14\",\"fecha\":\"2026-02-16\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"3\",\"clasificacion\":\"variable\",\"monto\":\"10000.00\",\"concepto\":\"Apartado Depa Landmark\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:22:40\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Renta\"}', 'Registró gasto: $10,000.00 - Renta', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:22:40'),
(191, 2, 'INSERT', 'gastos', 'gastos', 15, NULL, '{\"id\":\"15\",\"fecha\":\"2026-02-18\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"3\",\"clasificacion\":\"variable\",\"monto\":\"3000.00\",\"concepto\":\"Representante Yudva Eder\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:23:26\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Renta\"}', 'Registró gasto: $3,000.00 - Renta', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:23:26'),
(192, 2, 'INSERT', 'gastos', 'gastos', 16, NULL, '{\"id\":\"16\",\"fecha\":\"2026-02-18\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"3\",\"clasificacion\":\"variable\",\"monto\":\"50000.00\",\"concepto\":\"Renta Depa Landmark\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:24:17\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Renta\"}', 'Registró gasto: $50,000.00 - Renta', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:24:17'),
(193, 2, 'INSERT', 'gastos', 'gastos', 17, NULL, '{\"id\":\"17\",\"fecha\":\"2026-02-20\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"11\",\"clasificacion\":\"variable\",\"monto\":\"18000.00\",\"concepto\":\"Para Cuentas\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:25:02\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Cuentas Pro Amiri\"}', 'Registró gasto: $18,000.00 - Cuentas Pro Amiri', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:25:02'),
(194, 2, 'INSERT', 'gastos', 'gastos', 18, NULL, '{\"id\":\"18\",\"fecha\":\"2026-02-25\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"3\",\"clasificacion\":\"variable\",\"monto\":\"5000.00\",\"concepto\":\"Uso Montacargas Landmark\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:25:37\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Renta\"}', 'Registró gasto: $5,000.00 - Renta', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:25:37'),
(195, 2, 'INSERT', 'gastos', 'gastos', 19, NULL, '{\"id\":\"19\",\"fecha\":\"2026-02-27\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"13\",\"clasificacion\":\"variable\",\"monto\":\"1500.00\",\"concepto\":\"Papel Burbuja Y Emplaye\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:26:51\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Papeleria\"}', 'Registró gasto: $1,500.00 - Papeleria', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:26:51'),
(196, 2, 'INSERT', 'gastos', 'gastos', 20, NULL, '{\"id\":\"20\",\"fecha\":\"2026-02-27\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"3\",\"clasificacion\":\"variable\",\"monto\":\"500.00\",\"concepto\":\"Wifi\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:27:26\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Renta\"}', 'Registró gasto: $500.00 - Renta', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:27:26'),
(197, 2, 'INSERT', 'gastos', 'gastos', 21, NULL, '{\"id\":\"21\",\"fecha\":\"2026-02-27\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"3\",\"clasificacion\":\"variable\",\"monto\":\"18500.00\",\"concepto\":\"Gastos De Mudanza\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:28:10\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Renta\"}', 'Registró gasto: $18,500.00 - Renta', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:28:10'),
(198, 2, 'INSERT', 'gastos', 'gastos', 22, NULL, '{\"id\":\"22\",\"fecha\":\"2026-03-02\",\"cuenta_id\":\"32\",\"movimiento_id\":null,\"categoria_id\":\"7\",\"clasificacion\":\"fijo\",\"monto\":\"8450.00\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:28:59\",\"cuenta_nombre\":\"Makalan Sa De Cv\",\"tipo_gasto_nombre\":\"Comisiones Rojas\"}', 'Registró gasto: $8,450.00 - Comisiones Rojas', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:28:59'),
(199, 2, 'INSERT', 'gastos', 'gastos', 23, NULL, '{\"id\":\"23\",\"fecha\":\"2026-03-02\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"9\",\"clasificacion\":\"fijo\",\"monto\":\"24000.00\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:29:20\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Pago Amiri - Emiliano\"}', 'Registró gasto: $24,000.00 - Pago Amiri - Emiliano', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:29:20'),
(200, 2, 'INSERT', 'gastos', 'gastos', 24, NULL, '{\"id\":\"24\",\"fecha\":\"2026-03-05\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"14\",\"clasificacion\":\"variable\",\"monto\":\"500.00\",\"concepto\":\"Gerardo\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:30:09\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Retiros\"}', 'Registró gasto: $500.00 - Retiros', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:30:09'),
(201, 2, 'INSERT', 'gastos', 'gastos', 25, NULL, '{\"id\":\"25\",\"fecha\":\"2026-03-12\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"2\",\"clasificacion\":\"fijo\",\"monto\":\"1420.00\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:31:01\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Gas Kia\"}', 'Registró gasto: $1,420.00 - Gas Kia', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:31:01'),
(202, 2, 'INSERT', 'gastos', 'gastos', 26, NULL, '{\"id\":\"26\",\"fecha\":\"2026-03-19\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"15\",\"clasificacion\":\"variable\",\"monto\":\"500.00\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:31:42\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Limpieza\"}', 'Registró gasto: $500.00 - Limpieza', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:31:42'),
(203, 2, 'INSERT', 'gastos', 'gastos', 27, NULL, '{\"id\":\"27\",\"fecha\":\"2026-03-24\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"6\",\"clasificacion\":\"variable\",\"monto\":\"6000.00\",\"concepto\":\"Socios Amiri\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:32:22\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Comisiones Bancarias\"}', 'Registró gasto: $6,000.00 - Comisiones Bancarias', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:32:22'),
(204, 2, 'INSERT', 'gastos', 'gastos', 28, NULL, '{\"id\":\"28\",\"fecha\":\"2026-03-24\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"5\",\"clasificacion\":\"fijo\",\"monto\":\"500.00\",\"concepto\":\"Estacionamientos Gerardo\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:33:04\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Estacionamiento\"}', 'Registró gasto: $500.00 - Estacionamiento', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:33:04'),
(205, 2, 'INSERT', 'gastos', 'gastos', 29, NULL, '{\"id\":\"29\",\"fecha\":\"2026-03-24\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"2\",\"clasificacion\":\"fijo\",\"monto\":\"570.00\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:33:26\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Gas Kia\"}', 'Registró gasto: $570.00 - Gas Kia', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:33:26'),
(206, 2, 'INSERT', 'gastos', 'gastos', 30, NULL, '{\"id\":\"30\",\"fecha\":\"2026-03-27\",\"cuenta_id\":\"32\",\"movimiento_id\":null,\"categoria_id\":\"6\",\"clasificacion\":\"fijo\",\"monto\":\"3800.00\",\"concepto\":\"Arreglada Caja Fuerte\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:34:41\",\"cuenta_nombre\":\"Makalan Sa De Cv\",\"tipo_gasto_nombre\":\"Comisiones Bancarias\"}', 'Registró gasto: $3,800.00 - Comisiones Bancarias', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:34:41'),
(207, 2, 'INSERT', 'gastos', 'gastos', 31, NULL, '{\"id\":\"31\",\"fecha\":\"2026-03-17\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"8\",\"clasificacion\":\"fijo\",\"monto\":\"51500.00\",\"concepto\":\"Entregados A Monica Secretaria\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:35:55\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Pago Amiri - Diego\"}', 'Registró gasto: $51,500.00 - Pago Amiri - Diego', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:35:55'),
(208, 2, 'INSERT', 'gastos', 'gastos', 32, NULL, '{\"id\":\"32\",\"fecha\":\"2026-03-19\",\"cuenta_id\":\"32\",\"movimiento_id\":null,\"categoria_id\":\"8\",\"clasificacion\":\"variable\",\"monto\":\"3500.00\",\"concepto\":\"Mandados A Alfredo\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:36:25\",\"cuenta_nombre\":\"Makalan Sa De Cv\",\"tipo_gasto_nombre\":\"Pago Amiri - Diego\"}', 'Registró gasto: $3,500.00 - Pago Amiri - Diego', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:36:25'),
(209, 2, 'INSERT', 'gastos', 'gastos', 33, NULL, '{\"id\":\"33\",\"fecha\":\"2026-03-26\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"8\",\"clasificacion\":\"fijo\",\"monto\":\"122000.00\",\"concepto\":\"Se Los Mande A Carmela\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:37:06\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Pago Amiri - Diego\"}', 'Registró gasto: $122,000.00 - Pago Amiri - Diego', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:37:06'),
(210, 2, 'INSERT', 'gastos', 'gastos', 34, NULL, '{\"id\":\"34\",\"fecha\":\"2026-03-26\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"16\",\"clasificacion\":\"variable\",\"monto\":\"11541.16\",\"concepto\":\"Cuenta Bancaria Perdida\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:38:09\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Perdida\"}', 'Registró gasto: $11,541.16 - Perdida', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:38:09'),
(211, 2, 'INSERT', 'gastos', 'gastos', 35, NULL, '{\"id\":\"35\",\"fecha\":\"2026-04-01\",\"cuenta_id\":\"32\",\"movimiento_id\":null,\"categoria_id\":\"4\",\"clasificacion\":\"fijo\",\"monto\":\"3400.00\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:38:42\",\"cuenta_nombre\":\"Makalan Sa De Cv\",\"tipo_gasto_nombre\":\"Recargas\"}', 'Registró gasto: $3,400.00 - Recargas', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:38:42'),
(212, 2, 'INSERT', 'gastos', 'gastos', 36, NULL, '{\"id\":\"36\",\"fecha\":\"2026-04-01\",\"cuenta_id\":\"32\",\"movimiento_id\":null,\"categoria_id\":\"7\",\"clasificacion\":\"fijo\",\"monto\":\"6614.60\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:39:06\",\"cuenta_nombre\":\"Makalan Sa De Cv\",\"tipo_gasto_nombre\":\"Comisiones Rojas\"}', 'Registró gasto: $6,614.60 - Comisiones Rojas', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:39:06'),
(213, 2, 'INSERT', 'gastos', 'gastos', 37, NULL, '{\"id\":\"37\",\"fecha\":\"2026-04-01\",\"cuenta_id\":\"32\",\"movimiento_id\":null,\"categoria_id\":\"16\",\"clasificacion\":\"variable\",\"monto\":\"29000.00\",\"concepto\":\"Bloqueados A Recuperar\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:39:35\",\"cuenta_nombre\":\"Makalan Sa De Cv\",\"tipo_gasto_nombre\":\"Perdida\"}', 'Registró gasto: $29,000.00 - Perdida', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:39:35'),
(214, 2, 'INSERT', 'gastos', 'gastos', 38, NULL, '{\"id\":\"38\",\"fecha\":\"2026-04-01\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"9\",\"clasificacion\":\"fijo\",\"monto\":\"24000.00\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:40:03\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Pago Amiri - Emiliano\"}', 'Registró gasto: $24,000.00 - Pago Amiri - Emiliano', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:40:03'),
(215, 2, 'INSERT', 'gastos', 'gastos', 39, NULL, '{\"id\":\"39\",\"fecha\":\"2026-04-02\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"14\",\"clasificacion\":\"variable\",\"monto\":\"500.00\",\"concepto\":\"Daniel\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:41:18\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Retiros\"}', 'Registró gasto: $500.00 - Retiros', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:41:18'),
(216, 2, 'INSERT', 'gastos', 'gastos', 40, NULL, '{\"id\":\"40\",\"fecha\":\"2026-04-07\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"13\",\"clasificacion\":\"variable\",\"monto\":\"120.00\",\"concepto\":\"Hojas\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:41:54\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Papeleria\"}', 'Registró gasto: $120.00 - Papeleria', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:41:54'),
(217, 2, 'INSERT', 'gastos', 'gastos', 41, NULL, '{\"id\":\"41\",\"fecha\":\"2026-04-07\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"2\",\"clasificacion\":\"fijo\",\"monto\":\"500.00\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:42:14\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Gas Kia\"}', 'Registró gasto: $500.00 - Gas Kia', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:42:14'),
(218, 2, 'INSERT', 'gastos', 'gastos', 42, NULL, '{\"id\":\"42\",\"fecha\":\"2026-04-08\",\"cuenta_id\":\"32\",\"movimiento_id\":null,\"categoria_id\":\"3\",\"clasificacion\":\"fijo\",\"monto\":\"16000.00\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:42:38\",\"cuenta_nombre\":\"Makalan Sa De Cv\",\"tipo_gasto_nombre\":\"Renta\"}', 'Registró gasto: $16,000.00 - Renta', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:42:38'),
(219, 2, 'INSERT', 'gastos', 'gastos', 43, NULL, '{\"id\":\"43\",\"fecha\":\"2026-04-13\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"2\",\"clasificacion\":\"fijo\",\"monto\":\"500.00\",\"concepto\":\"\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:43:03\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Gas Kia\"}', 'Registró gasto: $500.00 - Gas Kia', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:43:03'),
(220, 2, 'INSERT', 'gastos', 'gastos', 44, NULL, '{\"id\":\"44\",\"fecha\":\"2026-04-13\",\"cuenta_id\":\"2\",\"movimiento_id\":null,\"categoria_id\":\"13\",\"clasificacion\":\"variable\",\"monto\":\"260.00\",\"concepto\":\"Cinta Doble Cara\",\"activo\":\"1\",\"creado_en\":\"2026-04-13 18:43:49\",\"cuenta_nombre\":\"Caja de Efectivo\",\"tipo_gasto_nombre\":\"Papeleria\"}', 'Registró gasto: $260.00 - Papeleria', '189.203.100.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 00:43:49'),
(221, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.163.129.16', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_4_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/147.0.7727.47 Mobile/15E148 Safari/604.1', '2026-04-14 15:10:34'),
(222, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Administrador\' inició sesión', '189.203.7.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-14 16:20:33');

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
(16, 'Perdida', 1, '2026-04-14 00:37:39');

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
(9, 'Maestros Joyeros', 1, 30, 0.00, 0.00, 0.00, 1, 1, 1, 'maestrosjoyeros@gmail.com', 1, '2026-03-27 00:08:17', NULL, NULL),
(10, 'Martin', 1, 31, 0.00, 0.00, 0.00, 1, 1, 1, 'martin@gamil.com', 1, '2026-03-27 00:10:12', NULL, NULL),
(11, 'King Of Watches', 7, 39, 1.50, 1.30, 0.20, 1, 1, 1, '33 3391 9485', 1, '2026-04-02 23:13:54', NULL, NULL),
(12, 'Binance', 1, 42, 0.00, 0.00, 0.00, 1, 1, 1, 'N/A', 1, '2026-04-03 23:16:11', NULL, NULL);

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
(1, 319, 1, 1500.00000000, 18.04000000, 18.04000000, 0.00000000, '2026-04-03 23:19:01'),
(2, 327, 2, 3500.00000000, 17.82390000, 18.24780000, 1483.65000000, '2026-04-03 23:24:49'),
(3, 331, 2, 27295.07000000, 17.82390000, 18.07070000, 6736.42327600, '2026-04-03 23:27:55'),
(4, 335, 2, 34520.54000000, 17.82390000, 17.97280000, 5140.10840600, '2026-04-03 23:30:18'),
(5, 342, 2, 4684.39000000, 17.82390000, 17.88070000, 266.07335200, '2026-04-03 23:35:14'),
(6, 342, 3, 4291.00000000, 17.80000000, 17.88070000, 346.28370000, '2026-04-03 23:35:14'),
(7, 342, 4, 2000.00000000, 17.85000000, 17.88070000, 61.40000000, '2026-04-03 23:35:14'),
(8, 342, 5, 15427.94000000, 17.74000000, 17.88070000, 2170.71115800, '2026-04-03 23:35:14'),
(9, 346, 2, 4684.39000000, 17.82390000, 17.88070000, 266.07335200, '2026-04-03 23:36:19'),
(10, 346, 3, 4291.00000000, 17.80000000, 17.88070000, 346.28370000, '2026-04-03 23:36:19'),
(11, 346, 4, 2000.00000000, 17.85000000, 17.88070000, 61.40000000, '2026-04-03 23:36:19'),
(12, 346, 5, 15427.94000000, 17.74000000, 17.88070000, 2170.71115800, '2026-04-03 23:36:19'),
(13, 353, 5, 77.76000000, 17.74000000, 17.78500000, 3.49920000, '2026-04-03 23:39:50'),
(14, 353, 6, 10296.00000000, 17.65000000, 17.78500000, 1389.96000000, '2026-04-03 23:39:50'),
(15, 360, 7, 43149.01000000, 17.58000000, 17.68430000, 4500.44174300, '2026-04-03 23:43:24'),
(16, 367, 8, 14367.00000000, 17.30000000, 17.49850000, 2851.84950000, '2026-04-03 23:46:46'),
(17, 377, 9, 3396.86000000, 17.25000000, 17.66150000, 1397.80789000, '2026-04-03 23:55:53'),
(18, 377, 10, 26983.87000000, 17.47150000, 17.66150000, 5126.93530000, '2026-04-03 23:55:53'),
(19, 385, 11, 2061.56000000, 17.94000000, 18.00000000, 123.69360000, '2026-04-04 00:00:33'),
(20, 391, 12, 5937.50000000, 17.41000000, 17.59160000, 1078.25000000, '2026-04-04 00:02:46'),
(21, 395, 12, 4985.50000000, 17.41000000, 17.61110000, 1002.58405000, '2026-04-04 00:03:36'),
(22, 401, 13, 23751.00000000, 17.06660000, 17.30000000, 5543.48340000, '2026-04-04 00:20:49'),
(23, 405, 13, 27138.00000000, 17.06660000, 17.50090000, 11786.03340000, '2026-04-04 00:21:58'),
(24, 411, 14, 29997.00000000, 17.26000000, 17.39840000, 4151.58480000, '2026-04-04 00:28:03'),
(25, 417, 15, 32053.13000000, 17.27250000, 17.55190000, 8955.64452200, '2026-04-04 00:35:47'),
(26, 421, 15, 15685.71000000, 17.27250000, 17.50000000, 3568.49902500, '2026-04-04 00:36:55'),
(27, 429, 17, 61048.82000000, 17.30000000, 17.56210000, 16000.89572200, '2026-04-04 00:40:58'),
(28, 436, 18, 6000.00000000, 17.29000000, 17.50000000, 1260.00000000, '2026-04-04 00:44:42'),
(29, 444, 19, 32387.67000000, 17.70000000, 17.95880000, 8381.92899600, '2026-04-04 00:50:11'),
(30, 453, 19, 5983.24000000, 17.70000000, 17.90000000, 1196.64800000, '2026-04-04 00:56:33'),
(31, 459, 19, 1450.09000000, 17.70000000, 18.11150000, 596.71203500, '2026-04-04 01:00:27'),
(32, 459, 20, 4719.29000000, 17.50000000, 18.11150000, 2885.84583500, '2026-04-04 01:00:27'),
(33, 463, 20, 5280.71000000, 17.50000000, 17.73500000, 1240.96685000, '2026-04-04 01:01:40'),
(34, 463, 21, 891.60000000, 17.50000000, 17.73500000, 209.52600000, '2026-04-04 01:01:40'),
(35, 463, 22, 6953.73000000, 17.60000000, 17.73500000, 938.75355000, '2026-04-04 01:01:40'),
(36, 463, 23, 7500.00000000, 17.60000000, 17.73500000, 1012.50000000, '2026-04-04 01:01:40'),
(37, 472, 25, 5500.00000000, 17.60000000, 18.03180000, 2374.90000000, '2026-04-04 01:21:24'),
(38, 476, 25, 1329.71000000, 17.60000000, 17.88040000, 372.85068400, '2026-04-04 01:22:27'),
(39, 481, 25, 1720.00000000, 17.60000000, 17.63920000, 67.42400000, '2026-04-04 01:24:53'),
(40, 491, 25, 35412.68000000, 17.60000000, 17.81000000, 7436.66280000, '2026-04-04 01:28:25'),
(41, 495, 25, 22290.92000000, 17.60000000, 17.71530000, 2570.14307600, '2026-04-04 01:30:32'),
(42, 503, 25, 5746.69000000, 17.60000000, 17.92700000, 1879.16763000, '2026-04-04 01:34:17'),
(43, 503, 26, 8869.00000000, 17.50000000, 17.92700000, 3787.06300000, '2026-04-04 01:34:17'),
(44, 503, 27, 12100.00000000, 17.91000000, 17.92700000, 205.70000000, '2026-04-04 01:34:17'),
(45, 503, 28, 9999.00000000, 17.60000000, 17.92700000, 3269.67300000, '2026-04-04 01:34:17'),
(46, 509, 28, 1.00000000, 17.60000000, 18.20290000, 0.60290000, '2026-04-04 01:36:27'),
(47, 509, 29, 21029.00000000, 17.97000000, 18.20290000, 4897.65410000, '2026-04-04 01:36:27'),
(48, 517, 30, 30338.48000000, 17.70000000, 17.94440000, 7414.72451200, '2026-04-04 01:39:24'),
(49, 521, 30, 32343.52000000, 17.70000000, 18.21250000, 16576.05400000, '2026-04-04 01:40:49'),
(50, 527, 31, 34492.56000000, 17.66000000, 18.10850000, 15469.91316000, '2026-04-04 01:52:48'),
(51, 534, 31, 5507.44000000, 17.66000000, 17.75680000, 533.12019200, '2026-04-04 01:56:54'),
(52, 534, 32, 49165.93000000, 17.60000000, 17.75680000, 7709.21782400, '2026-04-04 01:56:54'),
(53, 540, 32, 46810.65000000, 17.60000000, 17.75810000, 7400.76376500, '2026-04-04 02:00:05'),
(54, 546, 32, 26829.15000000, 17.60000000, 18.00580000, 10887.26907000, '2026-04-04 02:02:40'),
(55, 550, 32, 16194.27000000, 17.60000000, 17.86050000, 4218.60733500, '2026-04-04 02:03:49'),
(56, 550, 33, 19600.00000000, 17.75000000, 17.86050000, 2165.80000000, '2026-04-04 02:03:49'),
(57, 556, 34, 5800.00000000, 17.90000000, 18.09970000, 1158.26000000, '2026-04-04 02:07:14'),
(58, 562, 35, 25600.00000000, 17.90000000, 18.42690000, 13488.64000000, '2026-04-04 02:10:17'),
(59, 566, 35, 34266.29000000, 17.90000000, 18.14340000, 8340.41498600, '2026-04-04 02:11:17'),
(60, 576, 35, 20133.71000000, 17.90000000, 18.35560000, 9172.91827600, '2026-04-04 02:15:03'),
(61, 576, 36, 10000.00000000, 17.80000000, 18.35560000, 5556.00000000, '2026-04-04 02:15:03'),
(62, 576, 37, 3926.64000000, 17.80000000, 18.35560000, 2181.64118400, '2026-04-04 02:15:03'),
(63, 580, 37, 49846.64000000, 17.80000000, 17.92350000, 6156.06004000, '2026-04-04 02:15:55'),
(64, 586, 37, 6726.72000000, 17.80000000, 17.87440000, 500.46796800, '2026-04-04 02:19:14'),
(65, 586, 38, 0.06000000, 17.69160000, 17.87440000, 0.01096800, '2026-04-04 02:19:14'),
(66, 592, 38, 1353.94000000, 17.69160000, 18.08100000, 527.22423600, '2026-04-13 21:21:55'),
(67, 592, 39, 19130.54000000, 17.30000000, 18.08100000, 14940.95174000, '2026-04-13 21:21:55'),
(68, 596, 39, 21132.38000000, 17.30000000, 17.42620000, 2666.90635600, '2026-04-13 21:23:03'),
(69, 607, 39, 4519.46000000, 17.30000000, 17.37130000, 322.23749800, '2026-04-13 21:28:54'),
(70, 607, 41, 39897.10000000, 17.15000000, 17.37130000, 8829.22823000, '2026-04-13 21:28:54'),
(71, 611, 41, 55573.57000000, 17.15000000, 17.38960000, 13315.42737200, '2026-04-13 21:37:07');

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
(1, 'Utilidades AMIRI', NULL, NULL, 3, 'MXN', -181663.59, 0.00, 1, NULL, 1, '2026-03-13 01:37:15', NULL, NULL),
(2, 'Caja de Efectivo', NULL, NULL, 2, 'MXN', 659464.52, 0.00, 1, NULL, 1, '2026-03-13 01:37:15', NULL, NULL),
(18, 'Caja Proveedor USD - Emilio Agraz', NULL, NULL, 6, 'USD', 0.00, 0.00, 0, NULL, 1, '2026-03-26 23:59:59', NULL, NULL),
(19, 'Caja Proveedor USDT - Emilio Agraz', NULL, NULL, 6, 'USDT', 0.00, 0.00, 0, NULL, 1, '2026-03-26 23:59:59', NULL, NULL),
(20, 'Caja Proveedor USD - Alejandro Bambas', NULL, NULL, 6, 'USD', 0.00, 0.00, 0, NULL, 0, '2026-03-27 00:01:27', NULL, NULL),
(21, 'Caja Proveedor USDT - Alejandro Bambas', NULL, NULL, 6, 'USDT', 0.00, 0.00, 0, NULL, 0, '2026-03-27 00:01:27', NULL, NULL),
(22, 'Caja Proveedor USD - Daniel Casa Cambio', NULL, NULL, 6, 'USD', 0.00, 0.00, 0, NULL, 0, '2026-03-27 00:02:33', NULL, NULL),
(23, 'Caja Proveedor USDT - Daniel Casa Cambio', NULL, NULL, 6, 'USDT', 0.00, 0.00, 0, NULL, 0, '2026-03-27 00:02:33', NULL, NULL),
(24, 'Comisiones - Kike Rizo', NULL, NULL, 5, 'MXN', 0.01, 0.00, 0, NULL, 1, '2026-03-27 00:04:23', NULL, NULL),
(25, 'Comisiones - Missael Jauregui', NULL, NULL, 5, 'MXN', 646.00, 0.00, 0, NULL, 1, '2026-03-27 00:05:33', NULL, NULL),
(26, 'Caja - Cliente Cancun', NULL, NULL, 4, 'MXN', 0.01, 0.00, 0, NULL, 1, '2026-03-27 00:06:16', NULL, NULL),
(27, 'Caja - Toño Hernandez', NULL, NULL, 4, 'MXN', 0.00, 0.00, 0, NULL, 1, '2026-03-27 00:06:37', NULL, NULL),
(28, 'Caja - Adriana Hurtado', NULL, NULL, 4, 'MXN', 0.00, 0.00, 0, NULL, 1, '2026-03-27 00:07:09', NULL, NULL),
(29, 'Caja - Juan Manuel Nuñez', NULL, NULL, 4, 'MXN', 0.00, 0.00, 0, NULL, 1, '2026-03-27 00:07:55', NULL, NULL),
(30, 'Caja - Maestros Joyeros', NULL, NULL, 4, 'MXN', 0.00, 0.00, 0, NULL, 1, '2026-03-27 00:08:17', NULL, NULL),
(31, 'Caja - Martin', NULL, NULL, 4, 'MXN', 0.00, 0.00, 0, NULL, 1, '2026-03-27 00:10:12', NULL, NULL),
(32, 'Makalan Sa De Cv', '012320001261702500', 'BBVA', 1, 'MXN', 996089.85, 0.00, 1, NULL, 1, '2026-03-27 01:03:57', NULL, NULL),
(34, 'Tarpen Sa De Cv', '699180600007832575', 'FONDEADORA', 1, 'MXN', 0.00, 0.00, 1, NULL, 1, '2026-03-27 01:05:11', NULL, NULL),
(36, 'Rosa Huerta', '7211801000038115231', 'ALBO', 1, 'MXN', 0.00, 0.00, 1, NULL, 1, '2026-03-27 01:06:45', NULL, NULL),
(37, 'Mantela Sa De Cv', '01232001264988761', 'BBVA', 1, 'MXN', 0.00, 0.00, 1, NULL, 1, '2026-03-27 01:08:42', NULL, NULL),
(38, 'Ledger Stax', NULL, NULL, 2, 'USDT', 9529.33, 0.00, 1, NULL, 1, '2026-04-01 22:38:24', NULL, NULL),
(39, 'Caja - King Of Watches', NULL, NULL, 4, 'MXN', 0.00, 0.00, 0, NULL, 1, '2026-04-02 23:13:54', NULL, NULL),
(40, 'Caja Proveedor USD - Jorge De La Vega', NULL, NULL, 6, 'USD', 0.00, 0.00, 0, NULL, 1, '2026-04-03 23:06:28', NULL, NULL),
(41, 'Caja Proveedor USDT - Jorge De La Vega', NULL, NULL, 6, 'USDT', 0.00, 0.00, 0, NULL, 1, '2026-04-03 23:06:28', NULL, NULL),
(42, 'Caja - Binance', NULL, NULL, 4, 'MXN', 0.00, 0.00, 0, NULL, 1, '2026-04-03 23:16:11', NULL, NULL),
(43, 'Caja Proveedor USD - Binance', NULL, NULL, 6, 'USD', 0.00, 0.00, 0, NULL, 1, '2026-04-03 23:32:20', NULL, NULL),
(44, 'Caja Proveedor USDT - Binance', NULL, NULL, 6, 'USDT', 0.00, 0.00, 0, NULL, 1, '2026-04-03 23:32:21', NULL, NULL),
(45, 'Caja Proveedor USD - Missael Jauregui', NULL, NULL, 6, 'USD', 0.00, 0.00, 0, NULL, 1, '2026-04-04 00:42:36', NULL, NULL),
(46, 'Caja Proveedor USDT - Missael Jauregui', NULL, NULL, 6, 'USDT', 0.00, 0.00, 0, NULL, 1, '2026-04-04 00:42:36', NULL, NULL);

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
(1, 5, 26, 1775082724895, 1775082750249, 106500.00, 2130.00, 1065.00, 103305.00, 'MXN', '2026-01-01', 'Venta de efectivo - Pendiente de pago', 'pagado', 103305.00, '2026-01-03', 1, '2026-04-01 22:32:04', '2026-04-01 22:32:30'),
(2, 5, 26, 1775082786009, 1775082818061, 197131.00, 3942.62, 1971.31, 191217.07, 'MXN', '2026-01-01', 'Venta de efectivo - Pendiente de pago', 'pagado', 191217.07, '2026-01-05', 1, '2026-04-01 22:33:06', '2026-04-01 22:33:38'),
(3, 5, 26, 1775082913377, NULL, 117150.00, 2343.00, 1171.50, 113635.50, 'MXN', '2026-04-01', 'Venta de efectivo - Pendiente de pago', 'pendiente', 0.00, NULL, 0, '2026-04-01 22:35:13', '2026-04-01 22:35:23'),
(4, 5, 26, 1775092405379, 1775092511408, 117150.00, 2343.00, 1171.50, 113635.50, 'MXN', '2026-01-14', 'Venta de efectivo - Pendiente de pago', 'pagado', 113635.50, '2026-01-15', 1, '2026-04-02 01:13:25', '2026-04-02 01:15:11'),
(5, 6, 27, 1775092727050, 1775092806490, 1858947.50, 18589.48, 0.00, 1840358.03, 'MXN', '2026-01-19', 'Venta de efectivo - Pendiente de pago', 'pagado', 1840358.03, '2026-01-20', 1, '2026-04-02 01:18:47', '2026-04-02 01:20:06'),
(6, 6, 27, 1775092867888, 1775092994893, 1316088.90, 13160.89, 0.00, 1302928.01, 'MXN', '2026-01-20', 'Venta de efectivo - Pendiente de pago', 'pagado', 1302928.01, '2026-01-20', 1, '2026-04-02 01:21:07', '2026-04-02 01:23:14'),
(7, 8, 29, 1775093089970, NULL, 575758.00, 5757.58, 0.00, 570000.42, 'MXN', '2026-04-01', 'Venta de efectivo - Pendiente de pago', 'pendiente', 0.00, NULL, 0, '2026-04-02 01:24:49', '2026-04-02 01:25:49'),
(8, 8, 29, 1775093185287, 1775093214746, 575758.00, 5757.58, 0.00, 570000.42, 'MXN', '2026-01-28', 'Venta de efectivo - Pendiente de pago', 'pagado', 570000.42, '2026-01-28', 1, '2026-04-02 01:26:25', '2026-04-02 01:26:54'),
(9, 6, 27, 1775093290814, 1775093375815, 1552258.80, 15522.59, 0.00, 1536736.21, 'MXN', '2026-01-28', 'Venta de efectivo - Pendiente de pago', 'pagado', 1536736.21, '2026-01-29', 1, '2026-04-02 01:28:10', '2026-04-02 01:29:35'),
(10, 5, 26, 1775163726632, 1775163781081, 173328.75, 3466.58, 1733.29, 168128.89, 'MXN', '2026-02-04', 'Venta de efectivo - Pendiente de pago', 'pagado', 168128.89, '2026-02-05', 1, '2026-04-02 21:02:06', '2026-04-02 21:03:01'),
(11, 6, 27, 1775166865752, NULL, 1010000.00, 10100.00, 0.00, 999900.00, 'MXN', '2026-04-02', 'Venta de efectivo - Pendiente de pago', 'pendiente', 0.00, NULL, 0, '2026-04-02 21:54:25', '2026-04-02 21:54:42'),
(12, 6, 27, 1775166986613, 1775167097862, 1010000.00, 10100.00, 0.00, 999900.00, 'MXN', '2026-02-11', 'Venta de efectivo - Pendiente de pago', 'pagado', 999900.00, '2026-02-12', 1, '2026-04-02 21:56:26', '2026-04-02 21:58:17'),
(13, 5, 26, 1775167048137, 1775167321443, 237708.00, 4754.16, 2377.08, 230576.76, 'MXN', '2026-02-11', 'Venta de efectivo - Pendiente de pago', 'pagado', 230576.76, '2026-02-12', 1, '2026-04-02 21:57:28', '2026-04-02 22:02:01'),
(14, 8, 29, 1775167207989, 1775167578795, 141414.14, 1414.14, 0.00, 140000.00, 'MXN', '2026-02-12', 'Venta de efectivo - Pendiente de pago', 'pagado', 140000.00, '2026-02-13', 1, '2026-04-02 22:00:08', '2026-04-02 22:06:18'),
(15, 8, 29, 1775167425027, 1775167562641, 111675.12, 1116.75, 558.38, 109999.99, 'MXN', '2026-02-12', 'Venta de efectivo - Pendiente de pago', 'pagado', 109999.99, '2026-02-13', 1, '2026-04-02 22:03:45', '2026-04-02 22:06:02'),
(16, 7, 28, 1775167648779, 1775167688980, 50505.05, 505.05, 0.00, 50000.00, 'MXN', '2026-02-13', 'Venta de efectivo - Pendiente de pago', 'pagado', 50000.00, '2026-02-13', 1, '2026-04-02 22:07:28', '2026-04-02 22:08:08'),
(17, 8, 29, 1775167804912, 1775167943198, 1522842.63, 15228.43, 7614.21, 1499999.99, 'MXN', '2026-02-16', 'Venta de efectivo - Pendiente de pago', 'pagado', 1499999.99, '2026-02-16', 1, '2026-04-02 22:10:05', '2026-04-02 22:12:23'),
(18, 6, 27, 1775168107284, 1775168265563, 1554845.91, 15548.46, 0.00, 1539297.45, 'MXN', '2026-02-16', 'Venta de efectivo - Pendiente de pago', 'pagado', 1539297.45, '2026-02-17', 1, '2026-04-02 22:15:07', '2026-04-02 22:17:45'),
(19, 8, 29, 1775168318987, 1775168472771, 1015228.43, 10152.28, 5076.14, 1000000.00, 'MXN', '2026-02-18', 'Venta de efectivo - Pendiente de pago', 'pagado', 1000000.00, '2026-02-18', 1, '2026-04-02 22:18:38', '2026-04-02 22:21:12'),
(20, 5, 26, 1775168345916, 1775168551312, 115183.00, 2303.66, 1151.83, 111727.51, 'MXN', '2026-02-18', 'Venta de efectivo - Pendiente de pago', 'pagado', 111727.51, '2026-02-19', 1, '2026-04-02 22:19:05', '2026-04-02 22:22:31'),
(21, 8, 29, 1775168522427, 1775168633113, 521829.30, 5218.29, 2609.15, 514001.86, 'MXN', '2026-02-19', 'Venta de efectivo - Pendiente de pago', 'pagado', 514001.86, '2026-02-20', 1, '2026-04-02 22:22:02', '2026-04-02 22:23:53'),
(22, 8, 29, 1775168750664, 1775168825344, 634810.00, 6348.10, 3174.05, 625287.85, 'MXN', '2026-02-26', 'Venta de efectivo - Pendiente de pago', 'pagado', 625287.85, '2026-02-27', 1, '2026-04-02 22:25:50', '2026-04-02 22:27:05'),
(23, 5, 26, 1775168920638, 1775169130383, 62835.00, 1256.70, 628.35, 60949.95, 'MXN', '2026-02-27', 'Venta de efectivo - Pendiente de pago', 'pagado', 60949.95, '2026-02-27', 1, '2026-04-02 22:28:40', '2026-04-02 22:32:10'),
(24, 8, 29, 1775168985026, 1775169085978, 1015228.42, 10152.28, 5076.14, 999999.99, 'MXN', '2026-02-27', 'Venta de efectivo - Pendiente de pago', 'pagado', 999999.99, '2026-02-27', 1, '2026-04-02 22:29:45', '2026-04-02 22:31:25'),
(25, 8, 29, 1775169222058, 1775169326669, 507614.21, 5076.14, 2538.07, 500000.00, 'MXN', '2026-03-02', 'Venta de efectivo - Pendiente de pago', 'pagado', 500000.00, '2026-03-02', 1, '2026-04-02 22:33:42', '2026-04-02 22:35:26'),
(26, 8, 29, 1775169658001, 1775169877619, 375959.00, 3759.59, 1879.80, 370319.62, 'MXN', '2026-03-02', 'Venta de efectivo - Pendiente de pago', 'pagado', 370319.62, '2026-03-03', 1, '2026-04-02 22:40:58', '2026-04-02 22:44:37'),
(27, 5, 26, 1775169723472, 1775169763948, 459183.67, 6887.76, 2295.92, 450000.00, 'MXN', '2026-03-03', 'Venta de efectivo - Pendiente de pago', 'pagado', 450000.00, '2026-03-03', 1, '2026-04-02 22:42:03', '2026-04-02 22:42:43'),
(28, 5, 26, 1775169915115, 1775169978952, 191700.00, 3834.00, 1917.00, 185949.00, 'MXN', '2026-03-04', 'Venta de efectivo - Pendiente de pago', 'pagado', 185949.00, '2026-03-05', 1, '2026-04-02 22:45:15', '2026-04-02 22:46:18'),
(29, 8, 29, 1775170053453, 1775170184833, 688197.30, 6881.97, 3440.99, 677874.34, 'MXN', '2026-02-09', 'Venta de efectivo - Pendiente de pago', 'pagado', 677874.34, '2026-03-09', 1, '2026-04-02 22:47:33', '2026-04-02 22:49:44'),
(30, 6, 27, 1775170295499, 1775170424130, 1931830.90, 19318.31, 0.00, 1912512.59, 'MXN', '2026-03-10', 'Venta de efectivo - Pendiente de pago', 'pagado', 1912512.59, '2026-03-11', 1, '2026-04-02 22:51:35', '2026-04-02 22:53:44'),
(31, 6, 27, 1775170396581, 1775170469359, 150046.10, 1500.46, 0.00, 148545.64, 'MXN', '2026-03-11', 'Venta de efectivo - Pendiente de pago', 'pagado', 148545.64, '2026-03-11', 1, '2026-04-02 22:53:16', '2026-04-02 22:54:29'),
(32, 5, 26, 1775170518996, NULL, 152614.50, 3052.29, 1526.15, 148036.07, 'MXN', '2026-03-13', 'Venta de efectivo - Pendiente de pago', 'pendiente', 0.00, NULL, 0, '2026-04-02 22:55:18', '2026-04-02 22:55:44'),
(33, 5, 26, 1775170566588, 1775170605402, 152614.50, 3052.29, 1526.15, 148036.07, 'MXN', '2026-03-12', 'Venta de efectivo - Pendiente de pago', 'pagado', 148036.07, '2026-03-13', 1, '2026-04-02 22:56:06', '2026-04-02 22:56:45'),
(34, 7, 28, 1775170848733, 1775170992734, 700000.00, 5600.00, 0.00, 694400.00, 'MXN', '2026-03-13', 'Venta de efectivo - Pendiente de pago', 'pagado', 694400.00, '2026-03-13', 1, '2026-04-02 23:00:48', '2026-04-02 23:03:12'),
(35, 7, 28, 1775171071311, 1775171093904, 290000.00, 2320.00, 0.00, 287680.00, 'MXN', '2026-03-17', 'Venta de efectivo - Pendiente de pago', 'pagado', 287680.00, '2026-03-17', 1, '2026-04-02 23:04:31', '2026-04-02 23:04:53'),
(36, 5, 26, 1775171150211, 1775171334431, 387435.00, 7748.70, 3874.35, 375811.95, 'MXN', '2026-03-18', 'Venta de efectivo - Pendiente de pago', 'pagado', 375811.95, '2026-03-19', 1, '2026-04-02 23:05:50', '2026-04-02 23:08:54'),
(37, 8, 29, 1775171392071, 1775171441520, 182741.12, 1827.41, 913.71, 180000.00, 'MXN', '2026-03-20', 'Venta de efectivo - Pendiente de pago', 'pagado', 180000.00, '2026-03-20', 1, '2026-04-02 23:09:52', '2026-04-02 23:10:41'),
(38, 6, 27, 1775171486038, 1775171508319, 450000.00, 9000.00, 0.00, 441000.00, 'MXN', '2026-03-20', 'Venta de efectivo - Pendiente de pago', 'pagado', 441000.00, '2026-03-20', 1, '2026-04-02 23:11:26', '2026-04-02 23:11:48'),
(39, 11, 39, 1775171661902, 1775171758588, 78000.00, 1014.00, 156.00, 76830.00, 'MXN', '2026-03-24', 'Venta de efectivo - Pendiente de pago', 'pagado', 76830.00, '2026-03-24', 1, '2026-04-02 23:14:21', '2026-04-02 23:15:58'),
(40, 6, 27, 1775171852087, 1775171876513, 269100.00, 2691.00, 0.00, 266409.00, 'MXN', '2026-03-25', 'Venta de efectivo - Pendiente de pago', 'pagado', 266409.00, '2026-03-25', 1, '2026-04-02 23:17:32', '2026-04-02 23:17:56'),
(41, 11, 39, 1775171904810, 1775171921087, 103000.00, 1339.00, 206.00, 101455.00, 'MXN', '2026-03-25', 'Venta de efectivo - Pendiente de pago', 'pagado', 101455.00, '2026-03-25', 1, '2026-04-02 23:18:24', '2026-04-02 23:18:41'),
(42, 8, 29, 1775172068178, 1775172107305, 90000.00, 900.00, 1350.00, 87750.00, 'MXN', '2026-03-30', 'Venta de efectivo - Pendiente de pago', 'pagado', 87750.00, '2026-03-30', 1, '2026-04-02 23:21:08', '2026-04-02 23:21:47'),
(43, 5, 26, 1775172231541, NULL, 213532.50, 4270.65, 2135.33, 207126.53, 'MXN', '2026-04-01', 'Venta de efectivo - Pendiente de pago', 'pendiente', 100000.00, NULL, 0, '2026-04-02 23:23:51', '2026-04-02 23:29:07'),
(44, 11, 39, 1775172355181, NULL, 220000.00, 2860.00, 440.00, 216700.00, 'MXN', '2026-04-01', 'Venta de efectivo - Pendiente de pago', 'pendiente', 0.00, NULL, 0, '2026-04-02 23:25:55', '2026-04-02 23:28:05'),
(45, 5, 26, 1775172659334, 1775172721955, 213532.50, 4270.65, 2135.33, 207126.53, 'MXN', '2026-04-01', 'Venta de efectivo - Pendiente de pago', 'pagado', 207126.53, '2026-04-01', 1, '2026-04-02 23:30:59', '2026-04-02 23:32:01'),
(46, 11, 39, 1775172770851, 1775172788697, 220000.00, 2860.00, 440.00, 216700.00, 'MXN', '2026-04-01', 'Venta de efectivo - Pendiente de pago', 'pagado', 216700.00, '2026-04-01', 1, '2026-04-02 23:32:50', '2026-04-02 23:33:08');

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
(1, 3, 32, 1000000.00, '2026-01-01', 'Inicio', 'pendiente', 0.00, NULL, 1, '2026-04-01 22:29:25', '2026-04-01 22:29:25'),
(2, 4, 2, 1000000.00, '2026-04-01', 'Inicio', 'pendiente', 0.00, NULL, 0, '2026-04-01 22:29:53', '2026-04-01 22:29:57'),
(3, 4, 2, 1000000.00, '2026-01-01', 'Inicio', 'pendiente', 0.00, NULL, 1, '2026-04-01 22:30:16', '2026-04-01 22:30:16'),
(4, 4, 2, 10.00, '2026-04-01', '', 'pendiente', 0.00, NULL, 0, '2026-04-01 23:38:14', '2026-04-01 23:38:27');

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
(1, '2026-03-02', 2, NULL, 7, 'fijo', 6290.00, 'Comisiones Rojas Febrero', 1, '2026-04-02 22:37:10'),
(2, '2026-01-04', 32, NULL, 8, 'fijo', 120000.00, 'Diego', 1, '2026-04-14 00:11:32'),
(3, '2026-01-01', 2, NULL, 10, 'variable', 2000.00, '', 0, '2026-04-14 00:12:47'),
(4, '2026-01-12', 2, NULL, 8, 'fijo', 45000.00, 'Se Los Mande A Areli', 1, '2026-04-14 00:15:25'),
(5, '2026-01-12', 2, NULL, 9, 'fijo', 25170.04, 'Emiliano', 1, '2026-04-14 00:16:16'),
(6, '2026-01-19', 2, NULL, 9, 'fijo', 20932.48, 'Diego', 1, '2026-04-14 00:17:21'),
(7, '2026-01-26', 2, NULL, 11, 'variable', 21050.00, '', 1, '2026-04-14 00:18:30'),
(8, '2026-01-26', 2, NULL, 11, 'variable', 21050.00, '', 1, '2026-04-14 00:18:30'),
(9, '2026-01-26', 2, NULL, 12, 'variable', 4500.00, 'Kia', 1, '2026-04-14 00:18:57'),
(10, '2026-01-29', 2, NULL, 10, 'variable', 1400.00, '', 1, '2026-04-14 00:19:44'),
(11, '2026-02-03', 32, NULL, 7, 'fijo', 6290.00, '', 1, '2026-04-14 00:20:54'),
(12, '2026-02-11', 2, NULL, 4, 'fijo', 1000.00, '', 1, '2026-04-14 00:21:23'),
(13, '2026-02-04', 2, NULL, 6, 'variable', 14000.00, 'Multa', 1, '2026-04-14 00:21:58'),
(14, '2026-02-16', 2, NULL, 3, 'variable', 10000.00, 'Apartado Depa Landmark', 1, '2026-04-14 00:22:40'),
(15, '2026-02-18', 2, NULL, 3, 'variable', 3000.00, 'Representante Yudva Eder', 1, '2026-04-14 00:23:26'),
(16, '2026-02-18', 2, NULL, 3, 'variable', 50000.00, 'Renta Depa Landmark', 1, '2026-04-14 00:24:17'),
(17, '2026-02-20', 2, NULL, 11, 'variable', 18000.00, 'Para Cuentas', 1, '2026-04-14 00:25:02'),
(18, '2026-02-25', 2, NULL, 3, 'variable', 5000.00, 'Uso Montacargas Landmark', 1, '2026-04-14 00:25:37'),
(19, '2026-02-27', 2, NULL, 13, 'variable', 1500.00, 'Papel Burbuja Y Emplaye', 1, '2026-04-14 00:26:51'),
(20, '2026-02-27', 2, NULL, 3, 'variable', 500.00, 'Wifi', 1, '2026-04-14 00:27:26'),
(21, '2026-02-27', 2, NULL, 3, 'variable', 18500.00, 'Gastos De Mudanza', 1, '2026-04-14 00:28:10'),
(22, '2026-03-02', 32, NULL, 7, 'fijo', 8450.00, '', 1, '2026-04-14 00:28:59'),
(23, '2026-03-02', 2, NULL, 9, 'fijo', 24000.00, '', 1, '2026-04-14 00:29:20'),
(24, '2026-03-05', 2, NULL, 14, 'variable', 500.00, 'Gerardo', 1, '2026-04-14 00:30:09'),
(25, '2026-03-12', 2, NULL, 2, 'fijo', 1420.00, '', 1, '2026-04-14 00:31:01'),
(26, '2026-03-19', 2, NULL, 15, 'variable', 500.00, '', 1, '2026-04-14 00:31:42'),
(27, '2026-03-24', 2, NULL, 6, 'variable', 6000.00, 'Socios Amiri', 1, '2026-04-14 00:32:22'),
(28, '2026-03-24', 2, NULL, 5, 'fijo', 500.00, 'Estacionamientos Gerardo', 1, '2026-04-14 00:33:04'),
(29, '2026-03-24', 2, NULL, 2, 'fijo', 570.00, '', 1, '2026-04-14 00:33:26'),
(30, '2026-03-27', 32, NULL, 6, 'fijo', 3800.00, 'Arreglada Caja Fuerte', 1, '2026-04-14 00:34:41'),
(31, '2026-03-17', 2, NULL, 8, 'fijo', 51500.00, 'Entregados A Monica Secretaria', 1, '2026-04-14 00:35:55'),
(32, '2026-03-19', 32, NULL, 8, 'variable', 3500.00, 'Mandados A Alfredo', 1, '2026-04-14 00:36:25'),
(33, '2026-03-26', 2, NULL, 8, 'fijo', 122000.00, 'Se Los Mande A Carmela', 1, '2026-04-14 00:37:06'),
(34, '2026-03-26', 2, NULL, 16, 'variable', 11541.16, 'Cuenta Bancaria Perdida', 1, '2026-04-14 00:38:09'),
(35, '2026-04-01', 32, NULL, 4, 'fijo', 3400.00, '', 1, '2026-04-14 00:38:42'),
(36, '2026-04-01', 32, NULL, 7, 'fijo', 6614.60, '', 1, '2026-04-14 00:39:06'),
(37, '2026-04-01', 32, NULL, 16, 'variable', 29000.00, 'Bloqueados A Recuperar', 1, '2026-04-14 00:39:35'),
(38, '2026-04-01', 2, NULL, 9, 'fijo', 24000.00, '', 1, '2026-04-14 00:40:03'),
(39, '2026-04-02', 2, NULL, 14, 'variable', 500.00, 'Daniel', 1, '2026-04-14 00:41:18'),
(40, '2026-04-07', 2, NULL, 13, 'variable', 120.00, 'Hojas', 1, '2026-04-14 00:41:54'),
(41, '2026-04-07', 2, NULL, 2, 'fijo', 500.00, '', 1, '2026-04-14 00:42:14'),
(42, '2026-04-08', 32, NULL, 3, 'fijo', 16000.00, '', 1, '2026-04-14 00:42:38'),
(43, '2026-04-13', 2, NULL, 2, 'fijo', 500.00, '', 1, '2026-04-14 00:43:03'),
(44, '2026-04-13', 2, NULL, 13, 'variable', 260.00, 'Cinta Doble Cara', 1, '2026-04-14 00:43:49');

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
(1, 32, 19, 'salida', 117150.00, 1123222.62, 1006072.62, '2026-04-01 22:35:23'),
(2, 26, 20, 'salida', 113635.50, 113635.50, 0.00, '2026-04-01 22:35:23'),
(3, 1, 21, 'salida', 2343.00, 8415.62, 6072.62, '2026-04-01 22:35:23'),
(4, 24, 22, 'salida', 1171.50, 1171.50, 0.00, '2026-04-01 22:35:23'),
(5, 32, 34, 'entrada', 180290.00, 434454.12, 614744.12, '2026-04-02 01:17:37'),
(6, 2, 34, 'salida', 180290.00, 1572790.00, 1392500.00, '2026-04-02 01:17:37'),
(7, 32, 35, 'entrada', 2343.00, 614744.12, 617087.12, '2026-04-02 01:17:42'),
(8, 24, 35, 'entrada', 2343.00, -1171.50, 1171.50, '2026-04-02 01:17:42'),
(9, 32, 46, 'entrada', 2193500.00, 1102952.02, 3296452.02, '2026-04-02 01:22:17'),
(10, 2, 46, 'salida', 2193500.00, 2240141.97, 46641.97, '2026-04-02 01:22:17'),
(11, 2, 53, 'entrada', 570000.42, 367213.54, 937213.96, '2026-04-02 01:25:43'),
(12, 29, 53, 'entrada', 570000.42, 0.00, 570000.42, '2026-04-02 01:25:43'),
(13, 32, 54, 'salida', 575758.00, 1678710.02, 1102952.02, '2026-04-02 01:25:49'),
(14, 29, 55, 'salida', 570000.42, 570000.42, 0.00, '2026-04-02 01:25:49'),
(15, 1, 56, 'salida', 5757.58, 45923.57, 40165.99, '2026-04-02 01:25:49'),
(16, 32, 75, 'salida', 1010000.00, 2744435.39, 1734435.39, '2026-04-02 21:54:42'),
(17, 27, 76, 'salida', 999900.00, 999900.00, 0.00, '2026-04-02 21:54:42'),
(18, 1, 77, 'salida', 10100.00, 75012.74, 64912.74, '2026-04-02 21:54:42'),
(19, 2, 111, 'entrada', 7774.23, 67703.12, 75477.35, '2026-04-02 22:12:58'),
(20, 24, 111, 'entrada', 7774.23, -160.02, 7614.21, '2026-04-02 22:12:58'),
(21, 2, 166, 'salida', 6290.00, 108267.02, 101977.02, '2026-04-02 22:37:10'),
(22, 1, 167, 'salida', 6290.00, 154087.18, 147797.18, '2026-04-02 22:37:10'),
(23, 32, 192, 'entrada', 10588570.00, -8397871.79, 2190698.21, '2026-04-02 22:48:46'),
(24, 2, 192, 'salida', 10588570.00, 11248347.61, 659777.61, '2026-04-02 22:48:46'),
(25, 32, 209, 'salida', 152614.50, 2311832.64, 2159218.14, '2026-04-02 22:55:44'),
(26, 26, 210, 'salida', 148036.07, 148036.07, 0.01, '2026-04-02 22:55:44'),
(27, 1, 211, 'salida', 3052.29, 193031.56, 189979.27, '2026-04-02 22:55:44'),
(28, 24, 212, 'salida', 1526.15, 1526.16, 0.02, '2026-04-02 22:55:44'),
(29, 32, 219, 'entrada', 1526.15, 2162270.42, 2163796.57, '2026-04-02 22:57:24'),
(30, 24, 219, 'entrada', 1526.15, 0.02, 1526.17, '2026-04-02 22:57:24'),
(31, 32, 221, 'entrada', 1526.19, 2162270.38, 2163796.57, '2026-04-02 22:58:41'),
(32, 24, 221, 'entrada', 1526.19, -0.02, 1526.17, '2026-04-02 22:58:41'),
(33, 32, 223, 'entrada', 1526.13, 2162270.44, 2163796.57, '2026-04-02 22:59:12'),
(34, 24, 223, 'entrada', 1526.13, 0.04, 1526.17, '2026-04-02 22:59:12'),
(35, 32, 239, 'entrada', 3874.35, 2388331.05, 2392205.40, '2026-04-02 23:07:08'),
(36, 24, 239, 'entrada', 3874.35, 0.00, 3874.35, '2026-04-02 23:07:08'),
(37, 32, 287, 'entrada', 646.00, 1825065.15, 1825711.15, '2026-04-02 23:27:47'),
(38, 25, 287, 'entrada', 646.00, 0.00, 646.00, '2026-04-02 23:27:47'),
(39, 32, 288, 'entrada', 107126.53, 1825711.15, 1932837.68, '2026-04-02 23:27:52'),
(40, 26, 288, 'entrada', 107126.53, 0.01, 107126.54, '2026-04-02 23:27:52'),
(41, 2, 289, 'entrada', 216700.00, 406537.12, 623237.12, '2026-04-02 23:27:59'),
(42, 39, 289, 'entrada', 216700.00, 0.00, 216700.00, '2026-04-02 23:27:59'),
(43, 32, 290, 'salida', 220000.00, 1932837.68, 1712837.68, '2026-04-02 23:28:05'),
(44, 39, 291, 'salida', 216700.00, 216700.00, 0.00, '2026-04-02 23:28:05'),
(45, 1, 292, 'salida', 2860.00, 232602.32, 229742.32, '2026-04-02 23:28:05'),
(46, 25, 293, 'salida', 440.00, 646.00, 206.00, '2026-04-02 23:28:05'),
(47, 32, 294, 'entrada', 100000.00, 1712837.68, 1812837.68, '2026-04-02 23:28:29'),
(48, 26, 294, 'entrada', 100000.00, 107126.54, 207126.54, '2026-04-02 23:28:29'),
(49, 32, 295, 'entrada', 3135.33, 1812837.68, 1815973.01, '2026-04-02 23:29:03'),
(50, 24, 295, 'entrada', 3135.33, -1000.00, 2135.33, '2026-04-02 23:29:03'),
(51, 32, 296, 'salida', 213532.50, 1815973.01, 1602440.51, '2026-04-02 23:29:07'),
(52, 26, 297, 'salida', 207126.53, 207126.54, 0.02, '2026-04-02 23:29:07'),
(53, 1, 298, 'salida', 4270.65, 229742.32, 225471.67, '2026-04-02 23:29:07'),
(54, 24, 299, 'salida', 2135.33, 2135.33, 0.01, '2026-04-02 23:29:07'),
(55, 32, 615, 'salida', 120000.00, 2044144.45, 1924144.45, '2026-04-14 00:11:32'),
(56, 1, 616, 'salida', 120000.00, 520404.69, 400404.69, '2026-04-14 00:11:32'),
(57, 2, 617, 'salida', 2000.00, 313478.20, 311478.20, '2026-04-14 00:12:47'),
(58, 1, 618, 'salida', 2000.00, 400404.69, 398404.69, '2026-04-14 00:12:47'),
(59, 2, 619, 'entrada', 2000.00, 311478.20, 313478.20, '2026-04-14 00:14:23'),
(60, 1, 620, 'entrada', 2000.00, 398404.69, 400404.69, '2026-04-14 00:14:23'),
(61, 2, 621, 'salida', 45000.00, 313478.20, 268478.20, '2026-04-14 00:15:25'),
(62, 1, 622, 'salida', 45000.00, 400404.69, 355404.69, '2026-04-14 00:15:25'),
(63, 2, 623, 'salida', 25170.04, 268478.20, 243308.16, '2026-04-14 00:16:16'),
(64, 1, 624, 'salida', 25170.04, 355404.69, 330234.65, '2026-04-14 00:16:16'),
(65, 2, 625, 'salida', 20932.48, 243308.16, 222375.68, '2026-04-14 00:17:21'),
(66, 1, 626, 'salida', 20932.48, 330234.65, 309302.17, '2026-04-14 00:17:21'),
(67, 2, 627, 'salida', 21050.00, 222375.68, 201325.68, '2026-04-14 00:18:30'),
(68, 1, 628, 'salida', 21050.00, 309302.17, 288252.17, '2026-04-14 00:18:30'),
(69, 2, 629, 'salida', 21050.00, 201325.68, 180275.68, '2026-04-14 00:18:30'),
(70, 1, 630, 'salida', 21050.00, 288252.17, 267202.17, '2026-04-14 00:18:30'),
(71, 2, 631, 'salida', 4500.00, 180275.68, 175775.68, '2026-04-14 00:18:57'),
(72, 1, 632, 'salida', 4500.00, 267202.17, 262702.17, '2026-04-14 00:18:57'),
(73, 2, 633, 'salida', 1400.00, 175775.68, 174375.68, '2026-04-14 00:19:44'),
(74, 1, 634, 'salida', 1400.00, 262702.17, 261302.17, '2026-04-14 00:19:44'),
(75, 32, 635, 'salida', 6290.00, 1924144.45, 1917854.45, '2026-04-14 00:20:54'),
(76, 1, 636, 'salida', 6290.00, 261302.17, 255012.17, '2026-04-14 00:20:54'),
(77, 2, 637, 'salida', 1000.00, 174375.68, 173375.68, '2026-04-14 00:21:23'),
(78, 1, 638, 'salida', 1000.00, 255012.17, 254012.17, '2026-04-14 00:21:23'),
(79, 2, 639, 'salida', 14000.00, 173375.68, 159375.68, '2026-04-14 00:21:58'),
(80, 1, 640, 'salida', 14000.00, 254012.17, 240012.17, '2026-04-14 00:21:58'),
(81, 2, 641, 'salida', 10000.00, 159375.68, 149375.68, '2026-04-14 00:22:40'),
(82, 1, 642, 'salida', 10000.00, 240012.17, 230012.17, '2026-04-14 00:22:40'),
(83, 2, 643, 'salida', 3000.00, 149375.68, 146375.68, '2026-04-14 00:23:26'),
(84, 1, 644, 'salida', 3000.00, 230012.17, 227012.17, '2026-04-14 00:23:26'),
(85, 2, 645, 'salida', 50000.00, 146375.68, 96375.68, '2026-04-14 00:24:17'),
(86, 1, 646, 'salida', 50000.00, 227012.17, 177012.17, '2026-04-14 00:24:17'),
(87, 2, 647, 'salida', 18000.00, 96375.68, 78375.68, '2026-04-14 00:25:02'),
(88, 1, 648, 'salida', 18000.00, 177012.17, 159012.17, '2026-04-14 00:25:02'),
(89, 2, 649, 'salida', 5000.00, 78375.68, 73375.68, '2026-04-14 00:25:37'),
(90, 1, 650, 'salida', 5000.00, 159012.17, 154012.17, '2026-04-14 00:25:37'),
(91, 2, 651, 'salida', 1500.00, 73375.68, 71875.68, '2026-04-14 00:26:51'),
(92, 1, 652, 'salida', 1500.00, 154012.17, 152512.17, '2026-04-14 00:26:51'),
(93, 2, 653, 'salida', 500.00, 71875.68, 71375.68, '2026-04-14 00:27:26'),
(94, 1, 654, 'salida', 500.00, 152512.17, 152012.17, '2026-04-14 00:27:26'),
(95, 2, 655, 'salida', 18500.00, 71375.68, 52875.68, '2026-04-14 00:28:10'),
(96, 1, 656, 'salida', 18500.00, 152012.17, 133512.17, '2026-04-14 00:28:10'),
(97, 32, 657, 'salida', 8450.00, 1917854.45, 1909404.45, '2026-04-14 00:28:59'),
(98, 1, 658, 'salida', 8450.00, 133512.17, 125062.17, '2026-04-14 00:28:59'),
(99, 2, 659, 'salida', 24000.00, 52875.68, 28875.68, '2026-04-14 00:29:20'),
(100, 1, 660, 'salida', 24000.00, 125062.17, 101062.17, '2026-04-14 00:29:20'),
(101, 2, 661, 'salida', 500.00, 28875.68, 28375.68, '2026-04-14 00:30:09'),
(102, 1, 662, 'salida', 500.00, 101062.17, 100562.17, '2026-04-14 00:30:09'),
(103, 2, 663, 'salida', 1420.00, 28375.68, 26955.68, '2026-04-14 00:31:01'),
(104, 1, 664, 'salida', 1420.00, 100562.17, 99142.17, '2026-04-14 00:31:01'),
(105, 2, 665, 'salida', 500.00, 26955.68, 26455.68, '2026-04-14 00:31:42'),
(106, 1, 666, 'salida', 500.00, 99142.17, 98642.17, '2026-04-14 00:31:42'),
(107, 2, 667, 'salida', 6000.00, 26455.68, 20455.68, '2026-04-14 00:32:22'),
(108, 1, 668, 'salida', 6000.00, 98642.17, 92642.17, '2026-04-14 00:32:22'),
(109, 2, 669, 'salida', 500.00, 20455.68, 19955.68, '2026-04-14 00:33:04'),
(110, 1, 670, 'salida', 500.00, 92642.17, 92142.17, '2026-04-14 00:33:04'),
(111, 2, 671, 'salida', 570.00, 19955.68, 19385.68, '2026-04-14 00:33:26'),
(112, 1, 672, 'salida', 570.00, 92142.17, 91572.17, '2026-04-14 00:33:26'),
(113, 32, 673, 'salida', 3800.00, 1909404.45, 1905604.45, '2026-04-14 00:34:41'),
(114, 1, 674, 'salida', 3800.00, 91572.17, 87772.17, '2026-04-14 00:34:41'),
(115, 2, 676, 'salida', 51500.00, 870385.68, 818885.68, '2026-04-14 00:35:55'),
(116, 1, 677, 'salida', 51500.00, 87772.17, 36272.17, '2026-04-14 00:35:55'),
(117, 32, 678, 'salida', 3500.00, 1054604.45, 1051104.45, '2026-04-14 00:36:25'),
(118, 1, 679, 'salida', 3500.00, 36272.17, 32772.17, '2026-04-14 00:36:25'),
(119, 2, 680, 'salida', 122000.00, 818885.68, 696885.68, '2026-04-14 00:37:06'),
(120, 1, 681, 'salida', 122000.00, 32772.17, -89227.83, '2026-04-14 00:37:06'),
(121, 2, 682, 'salida', 11541.16, 696885.68, 685344.52, '2026-04-14 00:38:09'),
(122, 1, 683, 'salida', 11541.16, -89227.83, -100768.99, '2026-04-14 00:38:09'),
(123, 32, 684, 'salida', 3400.00, 1051104.45, 1047704.45, '2026-04-14 00:38:42'),
(124, 1, 685, 'salida', 3400.00, -100768.99, -104168.99, '2026-04-14 00:38:42'),
(125, 32, 686, 'salida', 6614.60, 1047704.45, 1041089.85, '2026-04-14 00:39:06'),
(126, 1, 687, 'salida', 6614.60, -104168.99, -110783.59, '2026-04-14 00:39:06'),
(127, 32, 688, 'salida', 29000.00, 1041089.85, 1012089.85, '2026-04-14 00:39:35'),
(128, 1, 689, 'salida', 29000.00, -110783.59, -139783.59, '2026-04-14 00:39:35'),
(129, 2, 690, 'salida', 24000.00, 685344.52, 661344.52, '2026-04-14 00:40:03'),
(130, 1, 691, 'salida', 24000.00, -139783.59, -163783.59, '2026-04-14 00:40:03'),
(131, 2, 692, 'salida', 500.00, 661344.52, 660844.52, '2026-04-14 00:41:18'),
(132, 1, 693, 'salida', 500.00, -163783.59, -164283.59, '2026-04-14 00:41:18'),
(133, 2, 694, 'salida', 120.00, 660844.52, 660724.52, '2026-04-14 00:41:54'),
(134, 1, 695, 'salida', 120.00, -164283.59, -164403.59, '2026-04-14 00:41:54'),
(135, 2, 696, 'salida', 500.00, 660724.52, 660224.52, '2026-04-14 00:42:14'),
(136, 1, 697, 'salida', 500.00, -164403.59, -164903.59, '2026-04-14 00:42:14'),
(137, 32, 698, 'salida', 16000.00, 1012089.85, 996089.85, '2026-04-14 00:42:38'),
(138, 1, 699, 'salida', 16000.00, -164903.59, -180903.59, '2026-04-14 00:42:38'),
(139, 2, 700, 'salida', 500.00, 660224.52, 659724.52, '2026-04-14 00:43:03'),
(140, 1, 701, 'salida', 500.00, -180903.59, -181403.59, '2026-04-14 00:43:03'),
(141, 2, 702, 'salida', 260.00, 659724.52, 659464.52, '2026-04-14 00:43:49'),
(142, 1, 703, 'salida', 260.00, -181403.59, -181663.59, '2026-04-14 00:43:49');

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
(1, 'USDT', 1500.00000000, 0.00000000, 18.04000000, 38, 311, '2026-01-05 00:00:00', 1),
(2, 'USDT', 70000.00000000, 0.00000000, 17.82390000, 38, 315, '2026-01-09 00:00:00', 1),
(3, 'USDT', 4291.00000000, 0.00000000, 17.80000000, 38, 322, '2026-01-13 00:00:00', 1),
(4, 'USDT', 2000.00000000, 0.00000000, 17.85000000, 38, 324, '2026-01-14 00:00:00', 1),
(5, 'USDT', 15505.70000000, 0.00000000, 17.74000000, 38, 339, '2026-01-19 00:00:00', 1),
(6, 'USDT', 10296.00000000, 0.00000000, 17.65000000, 38, 350, '2026-01-21 00:00:00', 1),
(7, 'USDT', 43149.01000000, 0.00000000, 17.58000000, 38, 357, '2026-01-23 00:00:00', 1),
(8, 'USDT', 14367.00000000, 0.00000000, 17.30000000, 38, 364, '2026-01-28 00:00:00', 1),
(9, 'USDT', 3396.86000000, 0.00000000, 17.25000000, 38, 371, '2026-01-29 00:00:00', 1),
(10, 'USDT', 26983.87000000, 0.00000000, 17.47150000, 38, 375, '2026-01-30 00:00:00', 1),
(11, 'USDT', 2061.56000000, 0.00000000, 17.94000000, 38, 382, '2026-02-03 00:00:00', 1),
(12, 'USDT', 10923.00000000, 0.00000000, 17.41000000, 38, 389, '2026-02-04 00:00:00', 1),
(13, 'USDT', 50889.00000000, 0.00000000, 17.06660000, 38, 399, '2026-02-10 00:00:00', 1),
(14, 'USDT', 29997.00000000, 0.00000000, 17.26000000, 38, 409, '2026-02-13 00:00:00', 1),
(15, 'USDT', 47738.84000000, 0.00000000, 17.27250000, 38, 415, '2026-02-17 00:00:00', 1),
(16, 'USDT', 61048.82000000, 61048.82000000, 17.30000000, 38, 425, '2026-02-24 00:00:00', 0),
(17, 'USDT', 61048.82000000, 0.00000000, 17.30000000, 38, 427, '2026-02-24 00:00:00', 1),
(18, 'USDT', 6000.00000000, 0.00000000, 17.29000000, 38, 434, '2026-02-27 00:00:00', 1),
(19, 'USDT', 39821.00000000, 0.00000000, 17.70000000, 38, 440, '2026-03-03 00:00:00', 1),
(20, 'USDT', 10000.00000000, 0.00000000, 17.50000000, 38, 442, '2026-03-03 00:00:00', 1),
(21, 'USDT', 891.60000000, 0.00000000, 17.50000000, 38, 449, '2026-03-05 00:00:00', 1),
(22, 'USDT', 6953.73000000, 0.00000000, 17.60000000, 38, 451, '2026-03-05 00:00:00', 1),
(23, 'USDT', 7500.00000000, 0.00000000, 17.60000000, 38, 457, '2026-03-06 00:00:00', 1),
(24, 'USDT', 72000.00000000, 72000.00000000, 17.60000000, 38, 467, '2026-03-09 00:00:00', 0),
(25, 'USDT', 72000.00000000, 0.00000000, 17.60000000, 38, 469, '2026-03-09 00:00:00', 1),
(26, 'USDT', 8869.00000000, 0.00000000, 17.50000000, 38, 487, '2026-03-11 00:00:00', 1),
(27, 'USDT', 12100.00000000, 0.00000000, 17.91000000, 38, 499, '2026-03-12 00:00:00', 1),
(28, 'USDT', 10000.00000000, 0.00000000, 17.60000000, 38, 501, '2026-03-12 00:00:00', 1),
(29, 'USDT', 21029.00000000, 0.00000000, 17.97000000, 38, 507, '2026-03-13 00:00:00', 1),
(30, 'USDT', 62682.00000000, 0.00000000, 17.70000000, 38, 513, '2026-03-19 00:00:00', 1),
(31, 'USDT', 40000.00000000, 0.00000000, 17.66000000, 38, 525, '2026-03-23 00:00:00', 1),
(32, 'USDT', 139000.00000000, 0.00000000, 17.60000000, 38, 531, '2026-03-24 00:00:00', 1),
(33, 'USDT', 19600.00000000, 0.00000000, 17.75000000, 38, 544, '2026-03-26 00:00:00', 1),
(34, 'USDT', 5800.00000000, 0.00000000, 17.90000000, 38, 554, '2026-03-27 00:00:00', 1),
(35, 'USDT', 80000.00000000, 0.00000000, 17.90000000, 38, 560, '2026-03-30 00:00:00', 1),
(36, 'USDT', 10000.00000000, 0.00000000, 17.80000000, 38, 572, '2026-03-31 00:00:00', 1),
(37, 'USDT', 60500.00000000, 0.00000000, 17.80000000, 38, 574, '2026-03-31 00:00:00', 1),
(38, 'USDT', 1354.00000000, 0.00000000, 17.69160000, 38, 584, '2026-04-01 00:00:00', 1),
(39, 'USDT', 44782.38000000, 0.00000000, 17.30000000, 38, 590, '2026-04-08 00:00:00', 1),
(40, 'USDT', 105000.00000000, 105000.00000000, 17.15000000, 38, 602, '2026-04-10 00:00:00', 0),
(41, 'USDT', 105000.00000000, 9529.33000000, 17.15000000, 38, 603, '2026-04-10 00:00:00', 1);

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

INSERT INTO `movimientos` (`id`, `grupo_id`, `fecha`, `creado_en`, `servicio_id`, `tipo`, `moneda`, `monto`, `cuenta_origen_id`, `cuenta_destino_id`, `cliente_id`, `promotor_id`, `proveedor_id`, `prestamista_id`, `gasto_id`, `referencia_id`, `referencia_tipo`, `paridad`, `descripcion`, `usuario_id`, `activo`, `estado`, `es_reverso`, `movimiento_original_id`, `motivo_correccion`, `corregido_por_usuario_id`, `corregido_en`) VALUES
(1, 1775082565289, '2026-01-01', '2026-04-01 22:29:25', 10, 'entrada', 'MXN', 1000000.000000, NULL, 32, NULL, NULL, NULL, 3, NULL, NULL, NULL, NULL, 'Préstamo de Diego Alfredo H: Inicio', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(2, 1775082593127, '2026-04-01', '2026-04-01 22:29:53', 10, 'entrada', 'MXN', 1000000.000000, NULL, 2, NULL, NULL, NULL, 4, NULL, NULL, NULL, NULL, 'Préstamo de Hyperion: Inicio', 2, 1, 'anulado', 0, NULL, 'Corrección por préstamo eliminado', NULL, NULL),
(3, 1775082616774, '2026-01-01', '2026-04-01 22:30:16', 10, 'entrada', 'MXN', 1000000.000000, NULL, 2, NULL, NULL, NULL, 4, NULL, NULL, NULL, NULL, 'Préstamo de Hyperion: Inicio', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(4, 1775082724895, '2026-01-01', '2026-04-01 22:32:04', 1, 'entrada', 'MXN', 106500.000000, NULL, 32, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(5, 1775082724895, '2026-01-01', '2026-04-01 22:32:04', 1, 'entrada', 'MXN', 103305.000000, NULL, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(6, 1775082724895, '2026-01-01', '2026-04-01 22:32:04', 1, 'comision', 'MXN', 2130.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(7, 1775082724895, '2026-01-01', '2026-04-01 22:32:04', 1, 'comision', 'MXN', 1065.000000, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(8, 1775082750249, '2026-01-03', '2026-04-01 22:32:30', 13, 'salida', 'MXN', 103305.000000, 32, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 1', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(9, 1775082786009, '2026-01-01', '2026-04-01 22:33:06', 1, 'entrada', 'MXN', 197131.000000, NULL, 32, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(10, 1775082786009, '2026-01-01', '2026-04-01 22:33:06', 1, 'entrada', 'MXN', 191217.070000, NULL, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(11, 1775082786009, '2026-01-01', '2026-04-01 22:33:06', 1, 'comision', 'MXN', 3942.620000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(12, 1775082786009, '2026-01-01', '2026-04-01 22:33:06', 1, 'comision', 'MXN', 1971.310000, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(13, 1775082818061, '2026-01-05', '2026-04-01 22:33:38', 13, 'salida', 'MXN', 191217.070000, 32, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 2', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(14, 1775082836988, '2026-01-05', '2026-04-01 22:33:56', 15, 'salida', 'MXN', 3036.310000, 32, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(15, 1775082913377, '2026-04-01', '2026-04-01 22:35:13', 1, 'entrada', 'MXN', 117150.000000, NULL, 32, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-01 22:35:23'),
(16, 1775082913377, '2026-04-01', '2026-04-01 22:35:13', 1, 'entrada', 'MXN', 113635.500000, NULL, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-01 22:35:23'),
(17, 1775082913377, '2026-04-01', '2026-04-01 22:35:13', 1, 'comision', 'MXN', 2343.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-01 22:35:23'),
(18, 1775082913377, '2026-04-01', '2026-04-01 22:35:13', 1, 'comision', 'MXN', 1171.500000, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-01 22:35:23'),
(19, 1775082913378, '2026-04-01', '2026-04-01 22:35:23', 1, 'ajuste', 'MXN', -117150.000000, 32, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1775082913377 - Venta de Efectivo - dinero recibido del cliente', 2, 1, 'activo', 1, 15, 'Reversión del último movimiento', NULL, NULL),
(20, 1775082913378, '2026-04-01', '2026-04-01 22:35:23', 1, 'ajuste', 'MXN', -113635.500000, 26, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1775082913377 - Venta de Efectivo - efectivo recibido neto', 2, 1, 'activo', 1, 16, 'Reversión del último movimiento', NULL, NULL),
(21, 1775082913378, '2026-04-01', '2026-04-01 22:35:23', 1, 'ajuste', 'MXN', -2343.000000, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1775082913377 - Comisión Amiri - Venta de Efectivo', 2, 1, 'activo', 1, 17, 'Reversión del último movimiento', NULL, NULL),
(22, 1775082913378, '2026-04-01', '2026-04-01 22:35:23', 1, 'ajuste', 'MXN', -1171.500000, 24, NULL, NULL, 6, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1775082913377 - Comisión Promotor - Venta de Efectivo', 2, 1, 'activo', 1, 18, 'Reversión del último movimiento', NULL, NULL),
(23, 1775082998849, '2026-01-06', '2026-04-01 22:36:38', 7, 'traspaso', 'MXN', 120000.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(24, 1775086694177, '2026-04-01', '2026-04-01 23:38:14', 10, 'entrada', 'MXN', 10.000000, NULL, 2, NULL, NULL, NULL, 4, NULL, NULL, NULL, NULL, 'Préstamo de Hyperion: ', 1, 1, 'anulado', 0, NULL, 'Préstamo eliminado manualmente desde el panel', NULL, NULL),
(25, 1775092382856, '2026-01-13', '2026-04-02 01:13:02', 7, 'traspaso', 'MXN', 139000.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(26, 1775092405379, '2026-01-14', '2026-04-02 01:13:25', 1, 'entrada', 'MXN', 117150.000000, NULL, 32, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(27, 1775092405379, '2026-01-14', '2026-04-02 01:13:25', 1, 'entrada', 'MXN', 113635.500000, NULL, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(28, 1775092405379, '2026-01-14', '2026-04-02 01:13:25', 1, 'comision', 'MXN', 2343.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(29, 1775092405379, '2026-01-14', '2026-04-02 01:13:25', 1, 'comision', 'MXN', 1171.500000, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(30, 1775092511408, '2026-01-15', '2026-04-02 01:15:11', 13, 'salida', 'MXN', 113635.500000, 32, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 4', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(31, 1775092562117, '2026-01-15', '2026-04-02 01:16:02', 7, 'traspaso', 'MXN', 133500.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(32, 1775092580144, '2026-01-15', '2026-04-02 01:16:20', 15, 'salida', 'MXN', 2343.000000, 32, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 01:17:42'),
(33, 1775092623336, '2026-01-15', '2026-04-02 01:17:03', 7, 'traspaso', 'MXN', 180290.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 01:17:37'),
(34, 1775092623337, '2026-04-01', '2026-04-02 01:17:37', 7, 'ajuste', 'MXN', -180290.000000, 2, 32, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1775092623336 - Retiro de Efectivo - Sin Tarjeta', 2, 1, 'activo', 1, 33, 'Reversión del último movimiento', NULL, NULL),
(35, 1775092623338, '2026-04-01', '2026-04-02 01:17:42', 15, 'ajuste', 'MXN', -2343.000000, 24, 32, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1775092580144 - Pago de Comisiones a Kike Rizo', 2, 1, 'activo', 1, 32, 'Reversión del último movimiento', NULL, NULL),
(36, 1775092692830, '2026-01-15', '2026-04-02 01:18:12', 15, 'salida', 'MXN', 1171.500000, 32, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(37, 1775092727050, '2026-01-19', '2026-04-02 01:18:47', 1, 'entrada', 'MXN', 1858947.500000, NULL, 32, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(38, 1775092727050, '2026-01-19', '2026-04-02 01:18:47', 1, 'entrada', 'MXN', 1840358.025000, NULL, 27, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(39, 1775092727050, '2026-01-19', '2026-04-02 01:18:47', 1, 'comision', 'MXN', 18589.475000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(40, 1775092781364, '2026-01-19', '2026-04-02 01:19:41', 7, 'traspaso', 'MXN', 494500.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(41, 1775092806490, '2026-01-20', '2026-04-02 01:20:06', 13, 'salida', 'MXN', 1840358.030000, 2, 27, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 5', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(42, 1775092867888, '2026-01-20', '2026-04-02 01:21:07', 1, 'entrada', 'MXN', 1316088.900000, NULL, 32, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(43, 1775092867888, '2026-01-20', '2026-04-02 01:21:07', 1, 'entrada', 'MXN', 1302928.011000, NULL, 27, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(44, 1775092867888, '2026-01-20', '2026-04-02 01:21:07', 1, 'comision', 'MXN', 13160.889000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(45, 1775092918597, '2026-04-01', '2026-04-02 01:21:58', 7, 'traspaso', 'MXN', 2193500.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'cobro_cheque', NULL, 'Retiro de Efectivo - Cobro en Cheque', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 01:22:17'),
(46, 1775092918598, '2026-04-01', '2026-04-02 01:22:17', 7, 'ajuste', 'MXN', -2193500.000000, 2, 32, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1775092918597 - Retiro de Efectivo - Cobro en Cheque', 2, 1, 'activo', 1, 45, 'Reversión del último movimiento', NULL, NULL),
(47, 1775092968921, '2026-01-20', '2026-04-02 01:22:48', 7, 'traspaso', 'MXN', 2193500.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'cobro_cheque', NULL, 'Retiro de Efectivo - Cobro en Cheque', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(48, 1775092994893, '2026-01-20', '2026-04-02 01:23:14', 13, 'salida', 'MXN', 1302928.010000, 2, 27, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 6', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(49, 1775093089970, '2026-04-01', '2026-04-02 01:24:49', 1, 'entrada', 'MXN', 575758.000000, NULL, 32, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 01:25:49'),
(50, 1775093089970, '2026-04-01', '2026-04-02 01:24:49', 1, 'entrada', 'MXN', 570000.420000, NULL, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 01:25:49'),
(51, 1775093089970, '2026-04-01', '2026-04-02 01:24:49', 1, 'comision', 'MXN', 5757.580000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 01:25:49'),
(52, 1775093120355, '2025-12-28', '2026-04-02 01:25:20', 13, 'salida', 'MXN', 570000.420000, 2, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 7', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 01:25:43'),
(53, 1775093120356, '2026-04-01', '2026-04-02 01:25:43', 13, 'ajuste', 'MXN', -570000.420000, 29, 2, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1775093120355 - Pago de venta efectivo ID 7', 2, 1, 'activo', 1, 52, 'Reversión del último movimiento', NULL, NULL),
(54, 1775093120357, '2026-04-01', '2026-04-02 01:25:49', 1, 'ajuste', 'MXN', -575758.000000, 32, NULL, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1775093089970 - Venta de Efectivo - dinero recibido del cliente', 2, 1, 'activo', 1, 49, 'Reversión del último movimiento', NULL, NULL),
(55, 1775093120357, '2026-04-01', '2026-04-02 01:25:49', 1, 'ajuste', 'MXN', -570000.420000, 29, NULL, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1775093089970 - Venta de Efectivo - efectivo recibido neto', 2, 1, 'activo', 1, 50, 'Reversión del último movimiento', NULL, NULL),
(56, 1775093120357, '2026-04-01', '2026-04-02 01:25:49', 1, 'ajuste', 'MXN', -5757.580000, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1775093089970 - Comisión Amiri - Venta de Efectivo', 2, 1, 'activo', 1, 51, 'Reversión del último movimiento', NULL, NULL),
(57, 1775093185287, '2026-01-28', '2026-04-02 01:26:25', 1, 'entrada', 'MXN', 575758.000000, NULL, 32, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(58, 1775093185287, '2026-01-28', '2026-04-02 01:26:25', 1, 'entrada', 'MXN', 570000.420000, NULL, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(59, 1775093185287, '2026-01-28', '2026-04-02 01:26:25', 1, 'comision', 'MXN', 5757.580000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(60, 1775093214746, '2026-01-28', '2026-04-02 01:26:54', 13, 'salida', 'MXN', 570000.420000, 2, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 8', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(61, 1775093290814, '2026-01-28', '2026-04-02 01:28:10', 1, 'entrada', 'MXN', 1552258.800000, NULL, 32, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(62, 1775093290814, '2026-01-28', '2026-04-02 01:28:10', 1, 'entrada', 'MXN', 1536736.212000, NULL, 27, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(63, 1775093290814, '2026-01-28', '2026-04-02 01:28:10', 1, 'comision', 'MXN', 15522.588000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(64, 1775093356798, '2026-01-28', '2026-04-02 01:29:16', 7, 'traspaso', 'MXN', 1500000.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'cobro_cheque', NULL, 'Retiro de Efectivo - Cobro en Cheque', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(65, 1775093375815, '2026-01-29', '2026-04-02 01:29:35', 13, 'salida', 'MXN', 1536736.210000, 2, 27, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 9', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(66, 1775163726632, '2026-02-04', '2026-04-02 21:02:06', 1, 'entrada', 'MXN', 173328.750000, NULL, 32, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(67, 1775163726632, '2026-02-04', '2026-04-02 21:02:06', 1, 'entrada', 'MXN', 168128.887500, NULL, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(68, 1775163726632, '2026-02-04', '2026-04-02 21:02:06', 1, 'comision', 'MXN', 3466.575000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(69, 1775163726632, '2026-02-04', '2026-04-02 21:02:06', 1, 'comision', 'MXN', 1733.287500, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(70, 1775163781081, '2026-02-05', '2026-04-02 21:03:01', 13, 'salida', 'MXN', 168128.890000, 32, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 10', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(71, 1775163893316, '2026-02-05', '2026-04-02 21:04:53', 15, 'salida', 'MXN', 1733.290000, 32, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(72, 1775166865752, '2026-04-02', '2026-04-02 21:54:25', 1, 'entrada', 'MXN', 1010000.000000, NULL, 32, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 21:54:42'),
(73, 1775166865752, '2026-04-02', '2026-04-02 21:54:25', 1, 'entrada', 'MXN', 999900.000000, NULL, 27, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 21:54:42'),
(74, 1775166865752, '2026-04-02', '2026-04-02 21:54:25', 1, 'comision', 'MXN', 10100.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 21:54:42'),
(75, 1775166865753, '2026-04-02', '2026-04-02 21:54:42', 1, 'ajuste', 'MXN', -1010000.000000, 32, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1775166865752 - Venta de Efectivo - dinero recibido del cliente', 2, 1, 'activo', 1, 72, 'Reversión del último movimiento', NULL, NULL),
(76, 1775166865753, '2026-04-02', '2026-04-02 21:54:42', 1, 'ajuste', 'MXN', -999900.000000, 27, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1775166865752 - Venta de Efectivo - efectivo recibido neto', 2, 1, 'activo', 1, 73, 'Reversión del último movimiento', NULL, NULL),
(77, 1775166865753, '2026-04-02', '2026-04-02 21:54:42', 1, 'ajuste', 'MXN', -10100.000000, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1775166865752 - Comisión Amiri - Venta de Efectivo', 2, 1, 'activo', 1, 74, 'Reversión del último movimiento', NULL, NULL),
(78, 1775166950769, '2026-02-07', '2026-04-02 21:55:50', 7, 'traspaso', 'MXN', 670400.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(79, 1775166986613, '2026-02-11', '2026-04-02 21:56:26', 1, 'entrada', 'MXN', 1010000.000000, NULL, 32, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(80, 1775166986613, '2026-02-11', '2026-04-02 21:56:26', 1, 'entrada', 'MXN', 999900.000000, NULL, 27, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(81, 1775166986613, '2026-02-11', '2026-04-02 21:56:26', 1, 'comision', 'MXN', 10100.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(82, 1775167048137, '2026-02-11', '2026-04-02 21:57:28', 1, 'entrada', 'MXN', 237708.000000, NULL, 32, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(83, 1775167048137, '2026-02-11', '2026-04-02 21:57:28', 1, 'entrada', 'MXN', 230576.760000, NULL, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(84, 1775167048137, '2026-02-11', '2026-04-02 21:57:28', 1, 'comision', 'MXN', 4754.160000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(85, 1775167048137, '2026-02-11', '2026-04-02 21:57:28', 1, 'comision', 'MXN', 2377.080000, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(86, 1775167097862, '2026-02-12', '2026-04-02 21:58:17', 13, 'salida', 'MXN', 999900.000000, 2, 27, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 12', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(87, 1775167137643, '2026-02-12', '2026-04-02 21:58:57', 15, 'salida', 'MXN', 2377.080000, 32, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(88, 1775167207989, '2026-02-12', '2026-04-02 22:00:07', 1, 'entrada', 'MXN', 141414.140000, NULL, 32, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(89, 1775167207989, '2026-02-12', '2026-04-02 22:00:08', 1, 'entrada', 'MXN', 139999.998600, NULL, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(90, 1775167207989, '2026-02-12', '2026-04-02 22:00:08', 1, 'comision', 'MXN', 1414.141400, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(91, 1775167321443, '2026-02-12', '2026-04-02 22:02:01', 13, 'salida', 'MXN', 230576.760000, 32, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 13', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(92, 1775167425027, '2026-02-12', '2026-04-02 22:03:45', 1, 'entrada', 'MXN', 111675.120000, NULL, 32, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(93, 1775167425027, '2026-02-12', '2026-04-02 22:03:45', 1, 'entrada', 'MXN', 109999.993200, NULL, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(94, 1775167425027, '2026-02-12', '2026-04-02 22:03:45', 1, 'comision', 'MXN', 1116.751200, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(95, 1775167425027, '2026-02-12', '2026-04-02 22:03:45', 1, 'comision', 'MXN', 558.375600, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(96, 1775167455683, '2026-02-12', '2026-04-02 22:04:15', 15, 'salida', 'MXN', 558.380000, 32, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(97, 1775167514678, '2026-02-12', '2026-04-02 22:05:14', 7, 'traspaso', 'MXN', 868500.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(98, 1775167562641, '2026-02-13', '2026-04-02 22:06:02', 13, 'salida', 'MXN', 109999.990000, 2, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 15', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(99, 1775167578795, '2026-02-13', '2026-04-02 22:06:18', 13, 'salida', 'MXN', 140000.000000, 2, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 14', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(100, 1775167648779, '2026-02-13', '2026-04-02 22:07:28', 1, 'entrada', 'MXN', 50505.050000, NULL, 32, 7, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(101, 1775167648779, '2026-02-13', '2026-04-02 22:07:28', 1, 'entrada', 'MXN', 49999.999500, NULL, 28, 7, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(102, 1775167648779, '2026-02-13', '2026-04-02 22:07:28', 1, 'comision', 'MXN', 505.050500, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(103, 1775167688980, '2026-02-13', '2026-04-02 22:08:08', 13, 'salida', 'MXN', 50000.000000, 2, 28, 7, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 16', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(104, 1775167804912, '2026-02-16', '2026-04-02 22:10:04', 1, 'entrada', 'MXN', 1522842.630000, NULL, 32, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(105, 1775167804912, '2026-02-16', '2026-04-02 22:10:04', 1, 'entrada', 'MXN', 1499999.990550, NULL, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(106, 1775167804912, '2026-02-16', '2026-04-02 22:10:04', 1, 'comision', 'MXN', 15228.426300, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(107, 1775167804912, '2026-02-16', '2026-04-02 22:10:05', 1, 'comision', 'MXN', 7614.213150, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(108, 1775167885583, '2026-02-16', '2026-04-02 22:11:25', 7, 'traspaso', 'MXN', 1006000.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(109, 1775167943198, '2026-02-16', '2026-04-02 22:12:23', 13, 'salida', 'MXN', 1499999.990000, 2, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 17', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(110, 1775167965706, '2026-02-16', '2026-04-02 22:12:45', 15, 'salida', 'MXN', 7774.230000, 2, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 22:12:58'),
(111, 1775167965707, '2026-04-02', '2026-04-02 22:12:58', 15, 'ajuste', 'MXN', -7774.230000, 24, 2, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1775167965706 - Pago de Comisiones a Kike Rizo', 2, 1, 'activo', 1, 110, 'Reversión del último movimiento', NULL, NULL),
(112, 1775168006615, '2026-02-16', '2026-04-02 22:13:26', 15, 'salida', 'MXN', 7614.210000, 2, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(113, 1775168107284, '2026-02-16', '2026-04-02 22:15:07', 1, 'entrada', 'MXN', 1554845.910000, NULL, 32, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(114, 1775168107284, '2026-02-16', '2026-04-02 22:15:07', 1, 'entrada', 'MXN', 1539297.450900, NULL, 27, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(115, 1775168107284, '2026-02-16', '2026-04-02 22:15:07', 1, 'comision', 'MXN', 15548.459100, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(116, 1775168160072, '2026-02-17', '2026-04-02 22:16:00', 7, 'traspaso', 'MXN', 1365700.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(117, 1775168245253, '2026-02-17', '2026-04-02 22:17:25', 7, 'traspaso', 'MXN', 200000.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(118, 1775168265563, '2026-02-17', '2026-04-02 22:17:45', 13, 'salida', 'MXN', 1539297.450000, 2, 27, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 18', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(119, 1775168318987, '2026-02-18', '2026-04-02 22:18:38', 1, 'entrada', 'MXN', 1015228.430000, NULL, 32, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(120, 1775168318987, '2026-02-18', '2026-04-02 22:18:38', 1, 'entrada', 'MXN', 1000000.003550, NULL, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(121, 1775168318987, '2026-02-18', '2026-04-02 22:18:38', 1, 'comision', 'MXN', 10152.284300, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(122, 1775168318987, '2026-02-18', '2026-04-02 22:18:38', 1, 'comision', 'MXN', 5076.142150, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(123, 1775168345916, '2026-02-18', '2026-04-02 22:19:05', 1, 'entrada', 'MXN', 115183.000000, NULL, 32, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(124, 1775168345916, '2026-02-18', '2026-04-02 22:19:05', 1, 'entrada', 'MXN', 111727.510000, NULL, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(125, 1775168345916, '2026-02-18', '2026-04-02 22:19:05', 1, 'comision', 'MXN', 2303.660000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(126, 1775168345916, '2026-02-18', '2026-04-02 22:19:05', 1, 'comision', 'MXN', 1151.830000, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(127, 1775168383650, '2026-02-18', '2026-04-02 22:19:43', 15, 'salida', 'MXN', 2725.770000, 2, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(128, 1775168399692, '2026-02-18', '2026-04-02 22:19:59', 15, 'salida', 'MXN', 3502.200000, 32, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(129, 1775168453797, '2026-02-18', '2026-04-02 22:20:53', 7, 'traspaso', 'MXN', 990000.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(130, 1775168472771, '2026-02-18', '2026-04-02 22:21:12', 13, 'salida', 'MXN', 1000000.000000, 2, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 19', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(131, 1775168522427, '2026-02-19', '2026-04-02 22:22:02', 1, 'entrada', 'MXN', 521829.300000, NULL, 32, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(132, 1775168522427, '2026-02-19', '2026-04-02 22:22:02', 1, 'entrada', 'MXN', 514001.860500, NULL, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(133, 1775168522427, '2026-02-19', '2026-04-02 22:22:02', 1, 'comision', 'MXN', 5218.293000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(134, 1775168522427, '2026-02-19', '2026-04-02 22:22:02', 1, 'comision', 'MXN', 2609.146500, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(135, 1775168551312, '2026-02-19', '2026-04-02 22:22:31', 13, 'salida', 'MXN', 111727.510000, 32, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 20', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(136, 1775168599385, '2026-02-19', '2026-04-02 22:23:19', 7, 'traspaso', 'MXN', 773000.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(137, 1775168633113, '2026-02-20', '2026-04-02 22:23:53', 13, 'salida', 'MXN', 514001.860000, 2, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 21', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(138, 1775168667830, '2026-02-20', '2026-04-02 22:24:27', 15, 'salida', 'MXN', 2609.150000, 2, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(139, 1775168750664, '2026-02-26', '2026-04-02 22:25:50', 1, 'entrada', 'MXN', 634810.000000, NULL, 32, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(140, 1775168750664, '2026-02-26', '2026-04-02 22:25:50', 1, 'entrada', 'MXN', 625287.850000, NULL, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(141, 1775168750664, '2026-02-26', '2026-04-02 22:25:50', 1, 'comision', 'MXN', 6348.100000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(142, 1775168750664, '2026-02-26', '2026-04-02 22:25:50', 1, 'comision', 'MXN', 3174.050000, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(143, 1775168802696, '2026-02-26', '2026-04-02 22:26:42', 7, 'traspaso', 'MXN', 500000.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'cobro_cheque', NULL, 'Retiro de Efectivo - Cobro en Cheque', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(144, 1775168825344, '2026-02-27', '2026-04-02 22:27:05', 13, 'salida', 'MXN', 625287.850000, 2, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 22', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(145, 1775168855471, '2026-02-27', '2026-04-02 22:27:35', 15, 'salida', 'MXN', 3174.050000, 2, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(146, 1775168920638, '2026-02-27', '2026-04-02 22:28:40', 1, 'entrada', 'MXN', 62835.000000, NULL, 32, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(147, 1775168920638, '2026-02-27', '2026-04-02 22:28:40', 1, 'entrada', 'MXN', 60949.950000, NULL, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(148, 1775168920638, '2026-02-27', '2026-04-02 22:28:40', 1, 'comision', 'MXN', 1256.700000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(149, 1775168920638, '2026-02-27', '2026-04-02 22:28:40', 1, 'comision', 'MXN', 628.350000, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(150, 1775168948530, '2026-02-27', '2026-04-02 22:29:08', 15, 'salida', 'MXN', 628.350000, 32, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(151, 1775168985026, '2026-02-27', '2026-04-02 22:29:45', 1, 'entrada', 'MXN', 1015228.420000, NULL, 32, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(152, 1775168985026, '2026-02-27', '2026-04-02 22:29:45', 1, 'entrada', 'MXN', 999999.993700, NULL, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(153, 1775168985026, '2026-02-27', '2026-04-02 22:29:45', 1, 'comision', 'MXN', 10152.284200, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(154, 1775168985026, '2026-02-27', '2026-04-02 22:29:45', 1, 'comision', 'MXN', 5076.142100, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(155, 1775169029408, '2026-02-27', '2026-04-02 22:30:29', 7, 'traspaso', 'MXN', 1001300.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(156, 1775169085978, '2026-02-27', '2026-04-02 22:31:25', 13, 'salida', 'MXN', 999999.990000, 2, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 24', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(157, 1775169130383, '2026-02-27', '2026-04-02 22:32:10', 13, 'salida', 'MXN', 60949.950000, 32, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 23', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(158, 1775169154357, '2026-02-27', '2026-04-02 22:32:34', 15, 'salida', 'MXN', 5076.140000, 32, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(159, 1775169222058, '2026-03-02', '2026-04-02 22:33:42', 1, 'entrada', 'MXN', 507614.210000, NULL, 32, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(160, 1775169222058, '2026-03-02', '2026-04-02 22:33:42', 1, 'entrada', 'MXN', 499999.996850, NULL, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(161, 1775169222058, '2026-03-02', '2026-04-02 22:33:42', 1, 'comision', 'MXN', 5076.142100, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(162, 1775169222058, '2026-03-02', '2026-04-02 22:33:42', 1, 'comision', 'MXN', 2538.071050, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(163, 1775169262769, '2026-03-02', '2026-04-02 22:34:22', 15, 'salida', 'MXN', 2538.070000, 32, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(164, 1775169305962, '2026-03-02', '2026-04-02 22:35:05', 7, 'traspaso', 'MXN', 397500.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(165, 1775169326669, '2026-03-02', '2026-04-02 22:35:26', 13, 'salida', 'MXN', 500000.000000, 2, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 25', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(166, 1775169430637, '2026-03-02', '2026-04-02 22:37:10', 12, 'salida', 'MXN', 6290.000000, 2, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, 'Gasto: Comisiones Rojas - Comisiones Rojas Febrero', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(167, 1775169430637, '2026-03-02', '2026-04-02 22:37:10', 12, 'salida', 'MXN', 6290.000000, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Comisiones Rojas - Comisiones Rojas Febrero', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(168, 1775169658001, '2026-03-02', '2026-04-02 22:40:58', 1, 'entrada', 'MXN', 375959.000000, NULL, 32, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(169, 1775169658001, '2026-03-02', '2026-04-02 22:40:58', 1, 'entrada', 'MXN', 370319.615000, NULL, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(170, 1775169658001, '2026-03-02', '2026-04-02 22:40:58', 1, 'comision', 'MXN', 3759.590000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(171, 1775169658001, '2026-03-02', '2026-04-02 22:40:58', 1, 'comision', 'MXN', 1879.795000, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(172, 1775169723472, '2026-03-03', '2026-04-02 22:42:03', 1, 'entrada', 'MXN', 459183.670000, NULL, 32, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(173, 1775169723472, '2026-03-03', '2026-04-02 22:42:03', 1, 'entrada', 'MXN', 449999.996600, NULL, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(174, 1775169723472, '2026-03-03', '2026-04-02 22:42:03', 1, 'comision', 'MXN', 6887.755050, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(175, 1775169723472, '2026-03-03', '2026-04-02 22:42:03', 1, 'comision', 'MXN', 2295.918350, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(176, 1775169763948, '2026-03-03', '2026-04-02 22:42:43', 13, 'salida', 'MXN', 450000.000000, 32, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 27', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(177, 1775169800202, '2026-03-03', '2026-04-02 22:43:20', 15, 'salida', 'MXN', 2295.920000, 32, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(178, 1775169825756, '2026-03-03', '2026-04-02 22:43:45', 15, 'salida', 'MXN', 1879.790000, 2, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(179, 1775169855723, '2026-03-03', '2026-04-02 22:44:15', 7, 'traspaso', 'MXN', 930000.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(180, 1775169877619, '2026-03-03', '2026-04-02 22:44:37', 13, 'salida', 'MXN', 370319.620000, 2, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 26', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(181, 1775169915115, '2026-03-04', '2026-04-02 22:45:15', 1, 'entrada', 'MXN', 191700.000000, NULL, 32, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(182, 1775169915115, '2026-03-04', '2026-04-02 22:45:15', 1, 'entrada', 'MXN', 185949.000000, NULL, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(183, 1775169915115, '2026-03-04', '2026-04-02 22:45:15', 1, 'comision', 'MXN', 3834.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(184, 1775169915115, '2026-03-04', '2026-04-02 22:45:15', 1, 'comision', 'MXN', 1917.000000, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(185, 1775169940900, '2026-03-05', '2026-04-02 22:45:40', 15, 'salida', 'MXN', 1917.000000, 32, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(186, 1775169978952, '2026-03-05', '2026-04-02 22:46:18', 13, 'salida', 'MXN', 185949.000000, 32, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 28', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(187, 1775170053453, '2026-02-09', '2026-04-02 22:47:33', 1, 'entrada', 'MXN', 688197.300000, NULL, 32, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(188, 1775170053453, '2026-02-09', '2026-04-02 22:47:33', 1, 'entrada', 'MXN', 677874.340500, NULL, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(189, 1775170053453, '2026-02-09', '2026-04-02 22:47:33', 1, 'comision', 'MXN', 6881.973000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(190, 1775170053453, '2026-02-09', '2026-04-02 22:47:33', 1, 'comision', 'MXN', 3440.986500, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(191, 1775170122539, '2026-02-09', '2026-04-02 22:48:42', 7, 'traspaso', 'MXN', 10588570.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 22:48:46'),
(192, 1775170122540, '2026-04-02', '2026-04-02 22:48:46', 7, 'ajuste', 'MXN', -10588570.000000, 2, 32, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1775170122539 - Retiro de Efectivo - Sin Tarjeta', 2, 1, 'activo', 1, 191, 'Reversión del último movimiento', NULL, NULL),
(193, 1775170147322, '2026-03-09', '2026-04-02 22:49:07', 7, 'traspaso', 'MXN', 1058857.070000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(194, 1775170184833, '2026-03-09', '2026-04-02 22:49:44', 13, 'salida', 'MXN', 677874.340000, 2, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 29', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(195, 1775170234477, '2026-03-09', '2026-04-02 22:50:34', 15, 'salida', 'MXN', 3440.990000, 2, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(196, 1775170295499, '2026-03-10', '2026-04-02 22:51:35', 1, 'entrada', 'MXN', 1931830.900000, NULL, 32, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(197, 1775170295499, '2026-03-10', '2026-04-02 22:51:35', 1, 'entrada', 'MXN', 1912512.591000, NULL, 27, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(198, 1775170295499, '2026-03-10', '2026-04-02 22:51:35', 1, 'comision', 'MXN', 19318.309000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(199, 1775170352614, '2026-03-11', '2026-04-02 22:52:32', 7, 'traspaso', 'MXN', 1054500.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(200, 1775170396581, '2026-03-11', '2026-04-02 22:53:16', 1, 'entrada', 'MXN', 150046.100000, NULL, 32, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL);
INSERT INTO `movimientos` (`id`, `grupo_id`, `fecha`, `creado_en`, `servicio_id`, `tipo`, `moneda`, `monto`, `cuenta_origen_id`, `cuenta_destino_id`, `cliente_id`, `promotor_id`, `proveedor_id`, `prestamista_id`, `gasto_id`, `referencia_id`, `referencia_tipo`, `paridad`, `descripcion`, `usuario_id`, `activo`, `estado`, `es_reverso`, `movimiento_original_id`, `motivo_correccion`, `corregido_por_usuario_id`, `corregido_en`) VALUES
(201, 1775170396581, '2026-03-11', '2026-04-02 22:53:16', 1, 'entrada', 'MXN', 148545.639000, NULL, 27, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(202, 1775170396581, '2026-03-11', '2026-04-02 22:53:16', 1, 'comision', 'MXN', 1500.461000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(203, 1775170424130, '2026-03-11', '2026-04-02 22:53:44', 13, 'salida', 'MXN', 1912512.590000, 2, 27, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 30', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(204, 1775170469359, '2026-03-11', '2026-04-02 22:54:29', 13, 'salida', 'MXN', 148545.640000, 2, 27, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 31', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(205, 1775170518996, '2026-03-13', '2026-04-02 22:55:18', 1, 'entrada', 'MXN', 152614.500000, NULL, 32, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 22:55:44'),
(206, 1775170518996, '2026-03-13', '2026-04-02 22:55:18', 1, 'entrada', 'MXN', 148036.065000, NULL, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 22:55:44'),
(207, 1775170518996, '2026-03-13', '2026-04-02 22:55:18', 1, 'comision', 'MXN', 3052.290000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 22:55:44'),
(208, 1775170518996, '2026-03-13', '2026-04-02 22:55:18', 1, 'comision', 'MXN', 1526.145000, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 22:55:44'),
(209, 1775170518997, '2026-04-02', '2026-04-02 22:55:44', 1, 'ajuste', 'MXN', -152614.500000, 32, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1775170518996 - Venta de Efectivo - dinero recibido del cliente', 2, 1, 'activo', 1, 205, 'Reversión del último movimiento', NULL, NULL),
(210, 1775170518997, '2026-04-02', '2026-04-02 22:55:44', 1, 'ajuste', 'MXN', -148036.065000, 26, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1775170518996 - Venta de Efectivo - efectivo recibido neto', 2, 1, 'activo', 1, 206, 'Reversión del último movimiento', NULL, NULL),
(211, 1775170518997, '2026-04-02', '2026-04-02 22:55:44', 1, 'ajuste', 'MXN', -3052.290000, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1775170518996 - Comisión Amiri - Venta de Efectivo', 2, 1, 'activo', 1, 207, 'Reversión del último movimiento', NULL, NULL),
(212, 1775170518997, '2026-04-02', '2026-04-02 22:55:44', 1, 'ajuste', 'MXN', -1526.145000, 24, NULL, NULL, 6, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1775170518996 - Comisión Promotor - Venta de Efectivo', 2, 1, 'activo', 1, 208, 'Reversión del último movimiento', NULL, NULL),
(213, 1775170566588, '2026-03-12', '2026-04-02 22:56:06', 1, 'entrada', 'MXN', 152614.500000, NULL, 32, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(214, 1775170566588, '2026-03-12', '2026-04-02 22:56:06', 1, 'entrada', 'MXN', 148036.065000, NULL, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(215, 1775170566588, '2026-03-12', '2026-04-02 22:56:06', 1, 'comision', 'MXN', 3052.290000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(216, 1775170566588, '2026-03-12', '2026-04-02 22:56:06', 1, 'comision', 'MXN', 1526.145000, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(217, 1775170605402, '2026-03-13', '2026-04-02 22:56:45', 13, 'salida', 'MXN', 148036.070000, 32, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 33', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(218, 1775170632414, '2026-03-13', '2026-04-02 22:57:12', 15, 'salida', 'MXN', 1526.150000, 32, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 22:57:24'),
(219, 1775170632415, '2026-04-02', '2026-04-02 22:57:24', 15, 'ajuste', 'MXN', -1526.150000, 24, 32, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1775170632414 - Pago de Comisiones a Kike Rizo', 2, 1, 'activo', 1, 218, 'Reversión del último movimiento', NULL, NULL),
(220, 1775170705688, '2026-03-13', '2026-04-02 22:58:25', 15, 'salida', 'MXN', 1526.190000, 32, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 22:58:41'),
(221, 1775170705689, '2026-04-02', '2026-04-02 22:58:41', 15, 'ajuste', 'MXN', -1526.190000, 24, 32, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1775170705688 - Pago de Comisiones a Kike Rizo', 2, 1, 'activo', 1, 220, 'Reversión del último movimiento', NULL, NULL),
(222, 1775170737172, '2026-03-13', '2026-04-02 22:58:57', 15, 'salida', 'MXN', 1526.130000, 32, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 22:59:12'),
(223, 1775170737173, '2026-04-02', '2026-04-02 22:59:12', 15, 'ajuste', 'MXN', -1526.130000, 24, 32, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1775170737172 - Pago de Comisiones a Kike Rizo', 2, 1, 'activo', 1, 222, 'Reversión del último movimiento', NULL, NULL),
(224, 1775170766636, '2026-03-13', '2026-04-02 22:59:26', 15, 'salida', 'MXN', 1526.170000, 32, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(225, 1775170848733, '2026-03-13', '2026-04-02 23:00:48', 1, 'entrada', 'MXN', 700000.000000, NULL, 32, 7, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(226, 1775170848733, '2026-03-13', '2026-04-02 23:00:48', 1, 'entrada', 'MXN', 694400.000000, NULL, 28, 7, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(227, 1775170848733, '2026-03-13', '2026-04-02 23:00:48', 1, 'comision', 'MXN', 5600.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(228, 1775170952387, '2026-03-13', '2026-04-02 23:02:32', 7, 'traspaso', 'MXN', 1147500.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(229, 1775170992734, '2026-03-13', '2026-04-02 23:03:12', 13, 'salida', 'MXN', 694400.000000, 2, 28, 7, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 34', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(230, 1775171071311, '2026-03-17', '2026-04-02 23:04:31', 1, 'entrada', 'MXN', 290000.000000, NULL, 32, 7, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(231, 1775171071311, '2026-03-17', '2026-04-02 23:04:31', 1, 'entrada', 'MXN', 287680.000000, NULL, 28, 7, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(232, 1775171071311, '2026-03-17', '2026-04-02 23:04:31', 1, 'comision', 'MXN', 2320.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(233, 1775171093904, '2026-03-17', '2026-04-02 23:04:53', 13, 'salida', 'MXN', 287680.000000, 2, 28, 7, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 35', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(234, 1775171150211, '2026-03-18', '2026-04-02 23:05:50', 1, 'entrada', 'MXN', 387435.000000, NULL, 32, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(235, 1775171150211, '2026-03-18', '2026-04-02 23:05:50', 1, 'entrada', 'MXN', 375811.950000, NULL, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(236, 1775171150211, '2026-03-18', '2026-04-02 23:05:50', 1, 'comision', 'MXN', 7748.700000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(237, 1775171150211, '2026-03-18', '2026-04-02 23:05:50', 1, 'comision', 'MXN', 3874.350000, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(238, 1775171188160, '2026-02-18', '2026-04-02 23:06:28', 15, 'salida', 'MXN', 3874.350000, 32, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 23:07:08'),
(239, 1775171188161, '2026-04-02', '2026-04-02 23:07:08', 15, 'ajuste', 'MXN', -3874.350000, 24, 32, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1775171188160 - Pago de Comisiones a Kike Rizo', 2, 1, 'activo', 1, 238, 'Reversión del último movimiento', NULL, NULL),
(240, 1775171251132, '2026-03-18', '2026-04-02 23:07:31', 15, 'salida', 'MXN', 3874.350000, 32, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(241, 1775171334431, '2026-03-19', '2026-04-02 23:08:54', 13, 'salida', 'MXN', 375811.950000, 32, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 36', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(242, 1775171392071, '2026-03-20', '2026-04-02 23:09:52', 1, 'entrada', 'MXN', 182741.120000, NULL, 32, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(243, 1775171392071, '2026-03-20', '2026-04-02 23:09:52', 1, 'entrada', 'MXN', 180000.003200, NULL, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(244, 1775171392071, '2026-03-20', '2026-04-02 23:09:52', 1, 'comision', 'MXN', 1827.411200, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(245, 1775171392071, '2026-03-20', '2026-04-02 23:09:52', 1, 'comision', 'MXN', 913.705600, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(246, 1775171410472, '2026-03-20', '2026-04-02 23:10:10', 15, 'salida', 'MXN', 913.710000, 32, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(247, 1775171441520, '2026-03-20', '2026-04-02 23:10:41', 13, 'salida', 'MXN', 180000.000000, 2, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 37', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(248, 1775171486038, '2026-03-20', '2026-04-02 23:11:26', 1, 'entrada', 'MXN', 450000.000000, NULL, 32, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(249, 1775171486038, '2026-03-20', '2026-04-02 23:11:26', 1, 'entrada', 'MXN', 441000.000000, NULL, 27, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(250, 1775171486038, '2026-03-20', '2026-04-02 23:11:26', 1, 'comision', 'MXN', 9000.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(251, 1775171508319, '2026-03-20', '2026-04-02 23:11:48', 13, 'salida', 'MXN', 441000.000000, 32, 27, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 38', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(252, 1775171661902, '2026-03-24', '2026-04-02 23:14:21', 1, 'entrada', 'MXN', 78000.000000, NULL, 32, 11, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(253, 1775171661902, '2026-03-24', '2026-04-02 23:14:21', 1, 'entrada', 'MXN', 76830.000000, NULL, 39, 11, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(254, 1775171661902, '2026-03-24', '2026-04-02 23:14:21', 1, 'comision', 'MXN', 1014.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(255, 1775171661902, '2026-03-24', '2026-04-02 23:14:21', 1, 'comision', 'MXN', 156.000000, NULL, 25, NULL, 7, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(256, 1775171738767, '2026-03-24', '2026-04-02 23:15:38', 7, 'traspaso', 'MXN', 1139500.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(257, 1775171758588, '2026-03-24', '2026-04-02 23:15:58', 13, 'salida', 'MXN', 76830.000000, 2, 39, 11, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 39', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(258, 1775171813201, '2026-03-24', '2026-04-02 23:16:53', 15, 'salida', 'MXN', 156.000000, 32, 25, NULL, 7, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Missael Jauregui', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(259, 1775171852087, '2026-03-25', '2026-04-02 23:17:32', 1, 'entrada', 'MXN', 269100.000000, NULL, 32, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(260, 1775171852087, '2026-03-25', '2026-04-02 23:17:32', 1, 'entrada', 'MXN', 266409.000000, NULL, 27, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(261, 1775171852087, '2026-03-25', '2026-04-02 23:17:32', 1, 'comision', 'MXN', 2691.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(262, 1775171876513, '2026-03-25', '2026-04-02 23:17:56', 13, 'salida', 'MXN', 266409.000000, 2, 27, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 40', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(263, 1775171904810, '2026-03-25', '2026-04-02 23:18:24', 1, 'entrada', 'MXN', 103000.000000, NULL, 32, 11, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(264, 1775171904810, '2026-03-25', '2026-04-02 23:18:24', 1, 'entrada', 'MXN', 101455.000000, NULL, 39, 11, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(265, 1775171904810, '2026-03-25', '2026-04-02 23:18:24', 1, 'comision', 'MXN', 1339.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(266, 1775171904810, '2026-03-25', '2026-04-02 23:18:24', 1, 'comision', 'MXN', 206.000000, NULL, 25, NULL, 7, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(267, 1775171921087, '2026-03-25', '2026-04-02 23:18:41', 13, 'salida', 'MXN', 101455.000000, 2, 39, 11, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 41', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(268, 1775172068178, '2026-03-30', '2026-04-02 23:21:08', 1, 'entrada', 'MXN', 90000.000000, NULL, 32, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(269, 1775172068178, '2026-03-30', '2026-04-02 23:21:08', 1, 'entrada', 'MXN', 87750.000000, NULL, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(270, 1775172068178, '2026-03-30', '2026-04-02 23:21:08', 1, 'comision', 'MXN', 900.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(271, 1775172068178, '2026-03-30', '2026-04-02 23:21:08', 1, 'comision', 'MXN', 1350.000000, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(272, 1775172107305, '2026-03-30', '2026-04-02 23:21:47', 13, 'salida', 'MXN', 87750.000000, 2, 29, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 42', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(273, 1775172130519, '2026-03-30', '2026-04-02 23:22:10', 15, 'salida', 'MXN', 1350.000000, 32, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(274, 1775172231541, '2026-04-01', '2026-04-02 23:23:51', 1, 'entrada', 'MXN', 213532.500000, NULL, 32, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 23:29:07'),
(275, 1775172231541, '2026-04-01', '2026-04-02 23:23:51', 1, 'entrada', 'MXN', 207126.525000, NULL, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 23:29:07'),
(276, 1775172231541, '2026-04-01', '2026-04-02 23:23:51', 1, 'comision', 'MXN', 4270.650000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 23:29:07'),
(277, 1775172231541, '2026-04-01', '2026-04-02 23:23:51', 1, 'comision', 'MXN', 2135.325000, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 23:29:07'),
(278, 1775172255416, '2026-04-01', '2026-04-02 23:24:15', 15, 'salida', 'MXN', 3135.330000, 32, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 23:29:03'),
(279, 1775172328111, '2026-03-01', '2026-04-02 23:25:28', 13, 'salida', 'MXN', 100000.000000, 32, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 43', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 23:28:29'),
(280, 1775172355181, '2026-04-01', '2026-04-02 23:25:55', 1, 'entrada', 'MXN', 220000.000000, NULL, 32, 11, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 23:28:05'),
(281, 1775172355181, '2026-04-01', '2026-04-02 23:25:55', 1, 'entrada', 'MXN', 216700.000000, NULL, 39, 11, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 23:28:05'),
(282, 1775172355181, '2026-04-01', '2026-04-02 23:25:55', 1, 'comision', 'MXN', 2860.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 23:28:05'),
(283, 1775172355181, '2026-04-01', '2026-04-02 23:25:55', 1, 'comision', 'MXN', 440.000000, NULL, 25, NULL, 7, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 23:28:05'),
(284, 1775172384684, '2026-04-01', '2026-04-02 23:26:24', 13, 'salida', 'MXN', 216700.000000, 2, 39, 11, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 44', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 23:27:59'),
(285, 1775172402124, '2026-04-02', '2026-04-02 23:26:42', 13, 'salida', 'MXN', 107126.530000, 32, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 43', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 23:27:52'),
(286, 1775172449728, '2026-04-02', '2026-04-02 23:27:29', 15, 'salida', 'MXN', 646.000000, 32, 25, NULL, 7, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Missael Jauregui', NULL, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-02 23:27:47'),
(287, 1775172449729, '2026-04-02', '2026-04-02 23:27:47', 15, 'ajuste', 'MXN', -646.000000, 25, 32, NULL, 7, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1775172449728 - Pago de Comisiones a Missael Jauregui', 2, 1, 'activo', 1, 286, 'Reversión del último movimiento', NULL, NULL),
(288, 1775172449730, '2026-04-02', '2026-04-02 23:27:52', 13, 'ajuste', 'MXN', -107126.530000, 26, 32, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1775172402124 - Pago de venta efectivo ID 43', 2, 1, 'activo', 1, 285, 'Reversión del último movimiento', NULL, NULL),
(289, 1775172449731, '2026-04-02', '2026-04-02 23:27:59', 13, 'ajuste', 'MXN', -216700.000000, 39, 2, 11, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1775172384684 - Pago de venta efectivo ID 44', 2, 1, 'activo', 1, 284, 'Reversión del último movimiento', NULL, NULL),
(290, 1775172449732, '2026-04-02', '2026-04-02 23:28:05', 1, 'ajuste', 'MXN', -220000.000000, 32, NULL, 11, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1775172355181 - Venta de Efectivo - dinero recibido del cliente', 2, 1, 'activo', 1, 280, 'Reversión del último movimiento', NULL, NULL),
(291, 1775172449732, '2026-04-02', '2026-04-02 23:28:05', 1, 'ajuste', 'MXN', -216700.000000, 39, NULL, 11, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1775172355181 - Venta de Efectivo - efectivo recibido neto', 2, 1, 'activo', 1, 281, 'Reversión del último movimiento', NULL, NULL),
(292, 1775172449732, '2026-04-02', '2026-04-02 23:28:05', 1, 'ajuste', 'MXN', -2860.000000, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1775172355181 - Comisión Amiri - Venta de Efectivo', 2, 1, 'activo', 1, 282, 'Reversión del último movimiento', NULL, NULL),
(293, 1775172449732, '2026-04-02', '2026-04-02 23:28:05', 1, 'ajuste', 'MXN', -440.000000, 25, NULL, NULL, 7, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1775172355181 - Comisión Promotor - Venta de Efectivo', 2, 1, 'activo', 1, 283, 'Reversión del último movimiento', NULL, NULL),
(294, 1775172449733, '2026-04-02', '2026-04-02 23:28:29', 13, 'ajuste', 'MXN', -100000.000000, 26, 32, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1775172328111 - Pago de venta efectivo ID 43', 2, 1, 'activo', 1, 279, 'Reversión del último movimiento', NULL, NULL),
(295, 1775172449734, '2026-04-02', '2026-04-02 23:29:03', 15, 'ajuste', 'MXN', -3135.330000, 24, 32, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1775172255416 - Pago de Comisiones a Kike Rizo', 2, 1, 'activo', 1, 278, 'Reversión del último movimiento', NULL, NULL),
(296, 1775172449735, '2026-04-02', '2026-04-02 23:29:07', 1, 'ajuste', 'MXN', -213532.500000, 32, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1775172231541 - Venta de Efectivo - dinero recibido del cliente', 2, 1, 'activo', 1, 274, 'Reversión del último movimiento', NULL, NULL),
(297, 1775172449735, '2026-04-02', '2026-04-02 23:29:07', 1, 'ajuste', 'MXN', -207126.525000, 26, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1775172231541 - Venta de Efectivo - efectivo recibido neto', 2, 1, 'activo', 1, 275, 'Reversión del último movimiento', NULL, NULL),
(298, 1775172449735, '2026-04-02', '2026-04-02 23:29:07', 1, 'ajuste', 'MXN', -4270.650000, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1775172231541 - Comisión Amiri - Venta de Efectivo', 2, 1, 'activo', 1, 276, 'Reversión del último movimiento', NULL, NULL),
(299, 1775172449735, '2026-04-02', '2026-04-02 23:29:07', 1, 'ajuste', 'MXN', -2135.325000, 24, NULL, NULL, 6, NULL, NULL, NULL, NULL, NULL, 1.000000, 'REVERSO: Anulación de grupo #1775172231541 - Comisión Promotor - Venta de Efectivo', 2, 1, 'activo', 1, 277, 'Reversión del último movimiento', NULL, NULL),
(300, 1775172659334, '2026-04-01', '2026-04-02 23:30:59', 1, 'entrada', 'MXN', 213532.500000, NULL, 32, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(301, 1775172659334, '2026-04-01', '2026-04-02 23:30:59', 1, 'entrada', 'MXN', 207126.525000, NULL, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(302, 1775172659334, '2026-04-01', '2026-04-02 23:30:59', 1, 'comision', 'MXN', 4270.650000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(303, 1775172659334, '2026-04-01', '2026-04-02 23:30:59', 1, 'comision', 'MXN', 2135.325000, NULL, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(304, 1775172694938, '2026-04-01', '2026-04-02 23:31:34', 15, 'salida', 'MXN', 2135.330000, 32, 24, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(305, 1775172721955, '2026-04-01', '2026-04-02 23:32:01', 13, 'salida', 'MXN', 207126.530000, 32, 26, 5, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 45', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(306, 1775172770851, '2026-04-01', '2026-04-02 23:32:50', 1, 'entrada', 'MXN', 220000.000000, NULL, 32, 11, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(307, 1775172770851, '2026-04-01', '2026-04-02 23:32:50', 1, 'entrada', 'MXN', 216700.000000, NULL, 39, 11, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(308, 1775172770851, '2026-04-01', '2026-04-02 23:32:50', 1, 'comision', 'MXN', 2860.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(309, 1775172770851, '2026-04-01', '2026-04-02 23:32:50', 1, 'comision', 'MXN', 440.000000, NULL, 25, NULL, 7, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(310, 1775172788697, '2026-04-01', '2026-04-02 23:33:08', 13, 'salida', 'MXN', 216700.000000, 2, 39, 11, NULL, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Pago de venta efectivo ID 46', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(311, 1775257233171, '2026-01-05', '2026-04-03 23:00:33', 6, 'entrada', 'USDT', 1500.000000, NULL, 38, NULL, NULL, 2, NULL, NULL, NULL, NULL, 18.040000, 'Compra de 1500 USDT a TC 18.04', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(312, 1775257233171, '2026-01-05', '2026-04-03 23:00:33', 6, 'salida', 'USDT', 1500.000000, NULL, 19, NULL, NULL, 2, NULL, NULL, NULL, NULL, 18.040000, 'Adeudo a proveedor: 1500 USDT (TC: 18.04)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(313, 1775257328707, '2026-01-05', '2026-04-03 23:02:08', 11, 'salida', 'MXN', 17000.000000, 32, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, 18.040000, 'Pago a proveedor Emilio Agraz — 942.35033259 USDT (TC: 18.04)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(314, 1775257367662, '2026-01-05', '2026-04-03 23:02:47', 11, 'salida', 'MXN', 10060.000000, 2, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, 18.039989, 'Pago a proveedor Emilio Agraz — 557.65000002 USDT (TC: 18.03998924)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(315, 1775257734647, '2026-01-09', '2026-04-03 23:08:54', 6, 'entrada', 'USDT', 70000.000000, NULL, 38, NULL, NULL, 7, NULL, NULL, NULL, NULL, 17.823900, 'Compra de 70000 USDT a TC 17.8239', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(316, 1775257734647, '2026-01-09', '2026-04-03 23:08:54', 6, 'salida', 'USDT', 70000.000000, NULL, 41, NULL, NULL, 7, NULL, NULL, NULL, NULL, 17.823900, 'Adeudo a proveedor: 70000 USDT (TC: 17.8239)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(317, 1775257864572, '2026-01-09', '2026-04-03 23:11:04', 7, 'traspaso', 'MXN', 1000000.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'cobro_cheque', NULL, 'Retiro de Efectivo - Cobro en Cheque', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(318, 1775257904495, '2026-01-09', '2026-04-03 23:11:44', 11, 'salida', 'MXN', 1247673.000000, 2, NULL, NULL, NULL, 7, NULL, NULL, NULL, NULL, 17.823900, 'Pago a proveedor Jorge De La Vega — 70000 USDT (TC: 17.8239)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(319, 1775258341455, '2026-01-12', '2026-04-03 23:19:01', 3, 'entrada', 'MXN', 27060.000000, NULL, 32, 12, NULL, NULL, NULL, NULL, NULL, NULL, 18.040000, 'Venta de 1500 USDT a TC 18.04', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(320, 1775258341455, '2026-01-12', '2026-04-03 23:19:01', 3, 'salida', 'USDT', 1500.000000, 38, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, 18.040000, 'Salida de 1500 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(321, 1775258341455, '2026-01-12', '2026-04-03 23:19:01', 3, 'entrada', 'USDT', 1500.000000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, 18.040000, 'Registro venta: 1500 USDT a TC 18.04 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(322, 1775258450952, '2026-01-13', '2026-04-03 23:20:50', 6, 'entrada', 'USDT', 4291.000000, NULL, 38, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.800000, 'Compra de 4291 USDT a TC 17.8', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(323, 1775258450952, '2026-01-13', '2026-04-03 23:20:50', 6, 'salida', 'USDT', 4291.000000, NULL, 19, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.800000, 'Adeudo a proveedor: 4291 USDT (TC: 17.8)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(324, 1775258492113, '2026-01-14', '2026-04-03 23:21:32', 6, 'entrada', 'USDT', 2000.000000, NULL, 38, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.850000, 'Compra de 2000 USDT a TC 17.85', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(325, 1775258492113, '2026-01-14', '2026-04-03 23:21:32', 6, 'salida', 'USDT', 2000.000000, NULL, 19, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.850000, 'Adeudo a proveedor: 2000 USDT (TC: 17.85)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(326, 1775258583574, '2026-01-14', '2026-04-03 23:23:03', 11, 'salida', 'MXN', 112079.800000, 32, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.815896, 'Pago a proveedor Emilio Agraz — 6291.00000143 USDT (TC: 17.81589572)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(327, 1775258689655, '2026-01-14', '2026-04-03 23:24:49', 3, 'entrada', 'MXN', 63867.300000, NULL, 2, 12, NULL, NULL, NULL, NULL, NULL, NULL, 18.247800, 'Venta de 3500 USDT a TC 18.2478', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(328, 1775258689655, '2026-01-14', '2026-04-03 23:24:49', 3, 'salida', 'USDT', 3500.000000, 38, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, 18.247800, 'Salida de 3500 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(329, 1775258689655, '2026-01-14', '2026-04-03 23:24:49', 3, 'comision', 'MXN', 1483.650000, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, 18.247800, 'Utilidad FIFO por venta de 3500 USDT a TC 18.2478', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(330, 1775258689655, '2026-01-14', '2026-04-03 23:24:49', 3, 'entrada', 'USDT', 3500.000000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, 18.247800, 'Registro venta: 3500 USDT a TC 18.2478 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(331, 1775258875854, '2026-01-15', '2026-04-03 23:27:55', 3, 'entrada', 'MXN', 493241.021449, NULL, 2, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.070700, 'Venta de 27295.07 USDT a TC 18.0707', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(332, 1775258875854, '2026-01-15', '2026-04-03 23:27:55', 3, 'salida', 'USDT', 27295.070000, 38, NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.070700, 'Salida de 27295.07 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(333, 1775258875854, '2026-01-15', '2026-04-03 23:27:55', 3, 'comision', 'MXN', 6736.423276, NULL, 1, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.070700, 'Utilidad FIFO por venta de 27295.07 USDT a TC 18.0707', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(334, 1775258875854, '2026-01-15', '2026-04-03 23:27:55', 3, 'entrada', 'USDT', 27295.070000, NULL, 30, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.070700, 'Registro venta: 27295.07 USDT a TC 18.0707 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(335, 1775259018260, '2026-01-15', '2026-04-03 23:30:18', 3, 'entrada', 'MXN', 620430.761312, NULL, 32, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.972800, 'Venta de 34520.54 USDT a TC 17.9728', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(336, 1775259018260, '2026-01-15', '2026-04-03 23:30:18', 3, 'salida', 'USDT', 34520.540000, 38, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.972800, 'Salida de 34520.54 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(337, 1775259018260, '2026-01-15', '2026-04-03 23:30:18', 3, 'comision', 'MXN', 5140.108406, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.972800, 'Utilidad FIFO por venta de 34520.54 USDT a TC 17.9728', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(338, 1775259018260, '2026-01-15', '2026-04-03 23:30:18', 3, 'entrada', 'USDT', 34520.540000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.972800, 'Registro venta: 34520.54 USDT a TC 17.9728 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(339, 1775259179506, '2026-01-19', '2026-04-03 23:32:59', 6, 'entrada', 'USDT', 15505.700000, NULL, 38, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.740000, 'Compra de 15505.7 USDT a TC 17.74', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(340, 1775259179506, '2026-01-19', '2026-04-03 23:32:59', 6, 'salida', 'USDT', 15505.700000, NULL, 44, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.740000, 'Adeudo a proveedor: 15505.7 USDT (TC: 17.74)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(341, 1775259210744, '2026-01-19', '2026-04-03 23:33:30', 11, 'salida', 'MXN', 275071.120000, 32, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.740000, 'Pago a proveedor Binance — 15505.69999911 USDT (TC: 17.74000013)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(342, 1775259314473, '2026-01-19', '2026-04-03 23:35:14', 3, 'entrada', 'MXN', 472110.022731, NULL, 2, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.880700, 'Venta de 26403.33 USDT a TC 17.8807', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(343, 1775259314473, '2026-01-19', '2026-04-03 23:35:14', 3, 'salida', 'USDT', 26403.330000, 38, NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.880700, 'Salida de 26403.33 USDT del wallet por venta', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(344, 1775259314473, '2026-01-19', '2026-04-03 23:35:14', 3, 'comision', 'MXN', 2844.468210, NULL, 1, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.880700, 'Utilidad FIFO por venta de 26403.33 USDT a TC 17.8807', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(345, 1775259314473, '2026-01-19', '2026-04-03 23:35:14', 3, 'entrada', 'USDT', 26403.330000, NULL, 30, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.880700, 'Registro venta: 26403.33 USDT a TC 17.8807 al cliente', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(346, 1775259379320, '2026-01-19', '2026-04-03 23:36:19', 3, 'entrada', 'MXN', 472110.022731, NULL, 2, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.880700, 'Venta de 26403.33 USDT a TC 17.8807', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(347, 1775259379320, '2026-01-19', '2026-04-03 23:36:19', 3, 'salida', 'USDT', 26403.330000, 38, NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.880700, 'Salida de 26403.33 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(348, 1775259379320, '2026-01-19', '2026-04-03 23:36:19', 3, 'comision', 'MXN', 2844.468210, NULL, 1, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.880700, 'Utilidad FIFO por venta de 26403.33 USDT a TC 17.8807', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(349, 1775259379320, '2026-01-19', '2026-04-03 23:36:19', 3, 'entrada', 'USDT', 26403.330000, NULL, 30, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.880700, 'Registro venta: 26403.33 USDT a TC 17.8807 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(350, 1775259445691, '2026-01-21', '2026-04-03 23:37:25', 6, 'entrada', 'USDT', 10296.000000, NULL, 38, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.650000, 'Compra de 10296 USDT a TC 17.65', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(351, 1775259445691, '2026-01-21', '2026-04-03 23:37:25', 6, 'salida', 'USDT', 10296.000000, NULL, 44, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.650000, 'Adeudo a proveedor: 10296 USDT (TC: 17.65)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(352, 1775259469582, '2026-01-21', '2026-04-03 23:37:49', 11, 'salida', 'MXN', 181724.400000, 32, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.650000, 'Pago a proveedor Binance — 10296 USDT (TC: 17.65)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(353, 1775259590488, '2026-01-21', '2026-04-03 23:39:50', 3, 'entrada', 'MXN', 184497.321600, NULL, 32, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.785000, 'Venta de 10373.76 USDT a TC 17.785', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(354, 1775259590488, '2026-01-21', '2026-04-03 23:39:50', 3, 'salida', 'USDT', 10373.760000, 38, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.785000, 'Salida de 10373.76 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(355, 1775259590488, '2026-01-21', '2026-04-03 23:39:50', 3, 'comision', 'MXN', 1393.459200, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.785000, 'Utilidad FIFO por venta de 10373.76 USDT a TC 17.785', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(356, 1775259590488, '2026-01-21', '2026-04-03 23:39:50', 3, 'entrada', 'USDT', 10373.760000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.785000, 'Registro venta: 10373.76 USDT a TC 17.785 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(357, 1775259648261, '2026-01-23', '2026-04-03 23:40:48', 6, 'entrada', 'USDT', 43149.010000, NULL, 38, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.580000, 'Compra de 43149.01 USDT a TC 17.58', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(358, 1775259648261, '2026-01-23', '2026-04-03 23:40:48', 6, 'salida', 'USDT', 43149.010000, NULL, 44, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.580000, 'Adeudo a proveedor: 43149.01 USDT (TC: 17.58)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(359, 1775259713575, '2026-01-23', '2026-04-03 23:41:53', 11, 'salida', 'MXN', 758559.600000, 32, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.580000, 'Pago a proveedor Binance — 43149.00999346 USDT (TC: 17.5800001)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(360, 1775259804637, '2026-01-23', '2026-04-03 23:43:24', 3, 'entrada', 'MXN', 763060.037543, NULL, 2, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.684300, 'Venta de 43149.01 USDT a TC 17.6843', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(361, 1775259804637, '2026-01-23', '2026-04-03 23:43:24', 3, 'salida', 'USDT', 43149.010000, 38, NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.684300, 'Salida de 43149.01 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(362, 1775259804637, '2026-01-23', '2026-04-03 23:43:24', 3, 'comision', 'MXN', 4500.441743, NULL, 1, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.684300, 'Utilidad FIFO por venta de 43149.01 USDT a TC 17.6843', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(363, 1775259804637, '2026-01-23', '2026-04-03 23:43:24', 3, 'entrada', 'USDT', 43149.010000, NULL, 30, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.684300, 'Registro venta: 43149.01 USDT a TC 17.6843 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(364, 1775259860711, '2026-01-28', '2026-04-03 23:44:20', 6, 'entrada', 'USDT', 14367.000000, NULL, 38, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.300000, 'Compra de 14367 USDT a TC 17.3', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(365, 1775259860711, '2026-01-28', '2026-04-03 23:44:20', 6, 'salida', 'USDT', 14367.000000, NULL, 44, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.300000, 'Adeudo a proveedor: 14367 USDT (TC: 17.3)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(366, 1775259893648, '2026-01-28', '2026-04-03 23:44:53', 11, 'salida', 'MXN', 248549.100000, 32, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.300000, 'Pago a proveedor Binance — 14367 USDT (TC: 17.3)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(367, 1775260006793, '2026-01-28', '2026-04-03 23:46:46', 3, 'entrada', 'MXN', 251400.949500, NULL, 32, 10, NULL, NULL, NULL, NULL, NULL, NULL, 17.498500, 'Venta de 14367 USDT a TC 17.4985', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(368, 1775260006793, '2026-01-28', '2026-04-03 23:46:46', 3, 'salida', 'USDT', 14367.000000, 38, NULL, 10, NULL, NULL, NULL, NULL, NULL, NULL, 17.498500, 'Salida de 14367 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(369, 1775260006793, '2026-01-28', '2026-04-03 23:46:46', 3, 'comision', 'MXN', 2851.849500, NULL, 1, 10, NULL, NULL, NULL, NULL, NULL, NULL, 17.498500, 'Utilidad FIFO por venta de 14367 USDT a TC 17.4985', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(370, 1775260006793, '2026-01-28', '2026-04-03 23:46:46', 3, 'entrada', 'USDT', 14367.000000, NULL, 31, 10, NULL, NULL, NULL, NULL, NULL, NULL, 17.498500, 'Registro venta: 14367 USDT a TC 17.4985 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(371, 1775260149108, '2026-01-29', '2026-04-03 23:49:09', 6, 'entrada', 'USDT', 3396.860000, NULL, 38, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.250000, 'Compra de 3396.86 USDT a TC 17.25', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(372, 1775260149108, '2026-01-29', '2026-04-03 23:49:09', 6, 'salida', 'USDT', 3396.860000, NULL, 19, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.250000, 'Adeudo a proveedor: 3396.86 USDT (TC: 17.25)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(373, 1775260203399, '2026-01-30', '2026-04-03 23:50:03', 11, 'salida', 'MXN', 17000.000000, 32, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.250001, 'Pago a proveedor Emilio Agraz — 985.50716239 USDT (TC: 17.25000147)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(374, 1775260217099, '2026-01-30', '2026-04-03 23:50:17', 11, 'salida', 'MXN', 41595.840000, 2, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.250022, 'Pago a proveedor Emilio Agraz — 2411.35000028 USDT (TC: 17.25002177)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(375, 1775260317308, '2026-01-30', '2026-04-03 23:51:57', 6, 'entrada', 'USDT', 26983.870000, NULL, 38, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.471500, 'Compra de 26983.87 USDT a TC 17.4715', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(376, 1775260317308, '2026-01-30', '2026-04-03 23:51:57', 6, 'salida', 'USDT', 26983.870000, NULL, 44, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.471500, 'Adeudo a proveedor: 26983.87 USDT (TC: 17.4715)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(377, 1775260553717, '2026-01-30', '2026-04-03 23:55:53', 3, 'entrada', 'MXN', 536569.262895, NULL, 32, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.661500, 'Venta de 30380.73 USDT a TC 17.6615', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(378, 1775260553717, '2026-01-30', '2026-04-03 23:55:53', 3, 'salida', 'USDT', 30380.730000, 38, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.661500, 'Salida de 30380.73 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(379, 1775260553717, '2026-01-30', '2026-04-03 23:55:53', 3, 'comision', 'MXN', 6524.743190, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.661500, 'Utilidad FIFO por venta de 30380.73 USDT a TC 17.6615', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(380, 1775260553717, '2026-01-30', '2026-04-03 23:55:53', 3, 'entrada', 'USDT', 30380.730000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.661500, 'Registro venta: 30380.73 USDT a TC 17.6615 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(381, 1775260587413, '2026-01-30', '2026-04-03 23:56:27', 11, 'salida', 'MXN', 471448.680000, 32, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.471500, 'Pago a proveedor Binance — 26983.86999326 USDT (TC: 17.47149983)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(382, 1775260662520, '2026-02-03', '2026-04-03 23:57:42', 6, 'entrada', 'USDT', 2061.560000, NULL, 38, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.940000, 'Compra de 2061.56 USDT a TC 17.94', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(383, 1775260662520, '2026-02-03', '2026-04-03 23:57:42', 6, 'salida', 'USDT', 2061.560000, NULL, 44, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.940000, 'Adeudo a proveedor: 2061.56 USDT (TC: 17.94)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(384, 1775260688581, '2026-02-03', '2026-04-03 23:58:08', 11, 'salida', 'MXN', 36984.390000, 32, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.940002, 'Pago a proveedor Binance — 2061.55999957 USDT (TC: 17.94000175)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(385, 1775260833541, '2026-02-03', '2026-04-04 00:00:33', 3, 'entrada', 'MXN', 37108.080000, NULL, 2, 10, NULL, NULL, NULL, NULL, NULL, NULL, 18.000000, 'Venta de 2061.56 USDT a TC 18', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(386, 1775260833541, '2026-02-03', '2026-04-04 00:00:33', 3, 'salida', 'USDT', 2061.560000, 38, NULL, 10, NULL, NULL, NULL, NULL, NULL, NULL, 18.000000, 'Salida de 2061.56 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(387, 1775260833541, '2026-02-03', '2026-04-04 00:00:33', 3, 'comision', 'MXN', 123.693600, NULL, 1, 10, NULL, NULL, NULL, NULL, NULL, NULL, 18.000000, 'Utilidad FIFO por venta de 2061.56 USDT a TC 18', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(388, 1775260833541, '2026-02-03', '2026-04-04 00:00:33', 3, 'entrada', 'USDT', 2061.560000, NULL, 31, 10, NULL, NULL, NULL, NULL, NULL, NULL, 18.000000, 'Registro venta: 2061.56 USDT a TC 18 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(389, 1775260897650, '2026-02-04', '2026-04-04 00:01:37', 6, 'entrada', 'USDT', 10923.000000, NULL, 38, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.410000, 'Compra de 10923 USDT a TC 17.41', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(390, 1775260897650, '2026-02-04', '2026-04-04 00:01:37', 6, 'salida', 'MXN', 190169.430000, 32, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.410000, 'Pago de contado: $190169.43 MXN por 10923 USDT (TC: 17.41)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(391, 1775260966492, '2026-02-04', '2026-04-04 00:02:46', 3, 'entrada', 'MXN', 104450.125000, NULL, 32, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.591600, 'Venta de 5937.5 USDT a TC 17.5916', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(392, 1775260966492, '2026-02-04', '2026-04-04 00:02:46', 3, 'salida', 'USDT', 5937.500000, 38, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.591600, 'Salida de 5937.5 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(393, 1775260966492, '2026-02-04', '2026-04-04 00:02:46', 3, 'comision', 'MXN', 1078.250000, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.591600, 'Utilidad FIFO por venta de 5937.5 USDT a TC 17.5916', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(394, 1775260966492, '2026-02-04', '2026-04-04 00:02:46', 3, 'entrada', 'USDT', 5937.500000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.591600, 'Registro venta: 5937.5 USDT a TC 17.5916 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(395, 1775261016607, '2026-02-04', '2026-04-04 00:03:36', 3, 'entrada', 'MXN', 87800.139050, NULL, 2, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.611100, 'Venta de 4985.5 USDT a TC 17.6111', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL);
INSERT INTO `movimientos` (`id`, `grupo_id`, `fecha`, `creado_en`, `servicio_id`, `tipo`, `moneda`, `monto`, `cuenta_origen_id`, `cuenta_destino_id`, `cliente_id`, `promotor_id`, `proveedor_id`, `prestamista_id`, `gasto_id`, `referencia_id`, `referencia_tipo`, `paridad`, `descripcion`, `usuario_id`, `activo`, `estado`, `es_reverso`, `movimiento_original_id`, `motivo_correccion`, `corregido_por_usuario_id`, `corregido_en`) VALUES
(396, 1775261016607, '2026-02-04', '2026-04-04 00:03:36', 3, 'salida', 'USDT', 4985.500000, 38, NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.611100, 'Salida de 4985.5 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(397, 1775261016607, '2026-02-04', '2026-04-04 00:03:36', 3, 'comision', 'MXN', 1002.584050, NULL, 1, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.611100, 'Utilidad FIFO por venta de 4985.5 USDT a TC 17.6111', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(398, 1775261016607, '2026-02-04', '2026-04-04 00:03:36', 3, 'entrada', 'USDT', 4985.500000, NULL, 30, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.611100, 'Registro venta: 4985.5 USDT a TC 17.6111 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(399, 1775261785397, '2026-02-10', '2026-04-04 00:16:25', 6, 'entrada', 'USDT', 50889.000000, NULL, 38, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.066600, 'Compra de 50889 USDT a TC 17.0666', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(400, 1775261785397, '2026-02-10', '2026-04-04 00:16:25', 6, 'salida', 'MXN', 868502.207400, 2, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.066600, 'Pago de contado: $868502.2074 MXN por 50889 USDT (TC: 17.0666)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(401, 1775262049043, '2026-02-12', '2026-04-04 00:20:49', 3, 'entrada', 'MXN', 410892.300000, NULL, 32, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.300000, 'Venta de 23751 USDT a TC 17.3', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(402, 1775262049043, '2026-02-12', '2026-04-04 00:20:49', 3, 'salida', 'USDT', 23751.000000, 38, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.300000, 'Salida de 23751 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(403, 1775262049043, '2026-02-12', '2026-04-04 00:20:49', 3, 'comision', 'MXN', 5543.483400, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.300000, 'Utilidad FIFO por venta de 23751 USDT a TC 17.3', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(404, 1775262049043, '2026-02-12', '2026-04-04 00:20:49', 3, 'entrada', 'USDT', 23751.000000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.300000, 'Registro venta: 23751 USDT a TC 17.3 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(405, 1775262118070, '2026-02-12', '2026-04-04 00:21:58', 3, 'entrada', 'MXN', 474939.424200, NULL, 2, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.500900, 'Venta de 27138 USDT a TC 17.5009', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(406, 1775262118070, '2026-02-12', '2026-04-04 00:21:58', 3, 'salida', 'USDT', 27138.000000, 38, NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.500900, 'Salida de 27138 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(407, 1775262118070, '2026-02-12', '2026-04-04 00:21:58', 3, 'comision', 'MXN', 11786.033400, NULL, 1, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.500900, 'Utilidad FIFO por venta de 27138 USDT a TC 17.5009', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(408, 1775262118070, '2026-02-12', '2026-04-04 00:21:58', 3, 'entrada', 'USDT', 27138.000000, NULL, 30, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.500900, 'Registro venta: 27138 USDT a TC 17.5009 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(409, 1775262309022, '2026-02-13', '2026-04-04 00:25:09', 6, 'entrada', 'USDT', 29997.000000, NULL, 38, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.260000, 'Compra de 29997 USDT a TC 17.26', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(410, 1775262309022, '2026-02-13', '2026-04-04 00:25:09', 6, 'salida', 'MXN', 517748.220000, 32, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.260000, 'Pago de contado: $517748.22 MXN por 29997 USDT (TC: 17.26)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(411, 1775262483366, '2026-02-13', '2026-04-04 00:28:03', 3, 'entrada', 'MXN', 521899.804800, NULL, 2, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.398400, 'Venta de 29997 USDT a TC 17.3984', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(412, 1775262483366, '2026-02-13', '2026-04-04 00:28:03', 3, 'salida', 'USDT', 29997.000000, 38, NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.398400, 'Salida de 29997 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(413, 1775262483366, '2026-02-13', '2026-04-04 00:28:03', 3, 'comision', 'MXN', 4151.584800, NULL, 1, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.398400, 'Utilidad FIFO por venta de 29997 USDT a TC 17.3984', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(414, 1775262483366, '2026-02-13', '2026-04-04 00:28:03', 3, 'entrada', 'USDT', 29997.000000, NULL, 30, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.398400, 'Registro venta: 29997 USDT a TC 17.3984 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(415, 1775262770782, '2026-02-17', '2026-04-04 00:32:50', 6, 'entrada', 'USDT', 47738.840000, NULL, 38, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.272500, 'Compra de 47738.84 USDT a TC 17.2725', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(416, 1775262770782, '2026-02-17', '2026-04-04 00:32:50', 6, 'salida', 'MXN', 824569.113900, 2, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.272500, 'Pago de contado: $824569.1139 MXN por 47738.84 USDT (TC: 17.2725)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(417, 1775262947898, '2026-02-19', '2026-04-04 00:35:47', 3, 'entrada', 'MXN', 562593.332447, NULL, 2, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.551900, 'Venta de 32053.13 USDT a TC 17.5519', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(418, 1775262947898, '2026-02-19', '2026-04-04 00:35:47', 3, 'salida', 'USDT', 32053.130000, 38, NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.551900, 'Salida de 32053.13 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(419, 1775262947898, '2026-02-19', '2026-04-04 00:35:47', 3, 'comision', 'MXN', 8955.644522, NULL, 1, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.551900, 'Utilidad FIFO por venta de 32053.13 USDT a TC 17.5519', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(420, 1775262947898, '2026-02-19', '2026-04-04 00:35:47', 3, 'entrada', 'USDT', 32053.130000, NULL, 30, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.551900, 'Registro venta: 32053.13 USDT a TC 17.5519 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(421, 1775263015585, '2026-02-19', '2026-04-04 00:36:55', 3, 'entrada', 'MXN', 274499.925000, NULL, 32, 10, NULL, NULL, NULL, NULL, NULL, NULL, 17.500000, 'Venta de 15685.71 USDT a TC 17.5', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(422, 1775263015585, '2026-02-19', '2026-04-04 00:36:55', 3, 'salida', 'USDT', 15685.710000, 38, NULL, 10, NULL, NULL, NULL, NULL, NULL, NULL, 17.500000, 'Salida de 15685.71 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(423, 1775263015585, '2026-02-19', '2026-04-04 00:36:55', 3, 'comision', 'MXN', 3568.499025, NULL, 1, 10, NULL, NULL, NULL, NULL, NULL, NULL, 17.500000, 'Utilidad FIFO por venta de 15685.71 USDT a TC 17.5', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(424, 1775263015585, '2026-02-19', '2026-04-04 00:36:55', 3, 'entrada', 'USDT', 15685.710000, NULL, 31, 10, NULL, NULL, NULL, NULL, NULL, NULL, 17.500000, 'Registro venta: 15685.71 USDT a TC 17.5 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(425, 1775263091843, '2026-02-24', '2026-04-04 00:38:11', 6, 'entrada', 'USDT', 61048.820000, NULL, 38, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.300000, 'Compra de 61048.82 USDT a TC 17.3', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(426, 1775263091843, '2026-02-24', '2026-04-04 00:38:11', 6, 'salida', 'MXN', 1056144.586000, 2, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.300000, 'Pago de contado: $1056144.586 MXN por 61048.82 USDT (TC: 17.3)', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(427, 1775263178966, '2026-02-24', '2026-04-04 00:39:38', 6, 'entrada', 'USDT', 61048.820000, NULL, 38, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.300000, 'Compra de 61048.82 USDT a TC 17.3', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(428, 1775263178966, '2026-02-24', '2026-04-04 00:39:38', 6, 'salida', 'USDT', 61048.820000, NULL, 44, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.300000, 'Adeudo a proveedor: 61048.82 USDT (TC: 17.3)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(429, 1775263258919, '2026-02-24', '2026-04-04 00:40:58', 3, 'entrada', 'MXN', 1072145.481722, NULL, 32, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.562100, 'Venta de 61048.82 USDT a TC 17.5621', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(430, 1775263258919, '2026-02-24', '2026-04-04 00:40:58', 3, 'salida', 'USDT', 61048.820000, 38, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.562100, 'Salida de 61048.82 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(431, 1775263258919, '2026-02-24', '2026-04-04 00:40:58', 3, 'comision', 'MXN', 16000.895722, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.562100, 'Utilidad FIFO por venta de 61048.82 USDT a TC 17.5621', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(432, 1775263258919, '2026-02-24', '2026-04-04 00:40:58', 3, 'entrada', 'USDT', 61048.820000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.562100, 'Registro venta: 61048.82 USDT a TC 17.5621 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(433, 1775263307049, '2026-02-24', '2026-04-04 00:41:47', 11, 'salida', 'MXN', 1056144.590000, 32, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.300000, 'Pago a proveedor Binance — 61048.8199842 USDT (TC: 17.30000007)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(434, 1775263422934, '2026-02-27', '2026-04-04 00:43:42', 6, 'entrada', 'USDT', 6000.000000, NULL, 38, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.290000, 'Compra de 6000 USDT a TC 17.29', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(435, 1775263422934, '2026-02-27', '2026-04-04 00:43:42', 6, 'salida', 'MXN', 103740.000000, 32, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.290000, 'Pago de contado: $103740 MXN por 6000 USDT (TC: 17.29)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(436, 1775263482895, '2026-02-27', '2026-04-04 00:44:42', 3, 'entrada', 'MXN', 105000.000000, NULL, 32, 10, NULL, NULL, NULL, NULL, NULL, NULL, 17.500000, 'Venta de 6000 USDT a TC 17.5', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(437, 1775263482895, '2026-02-27', '2026-04-04 00:44:42', 3, 'salida', 'USDT', 6000.000000, 38, NULL, 10, NULL, NULL, NULL, NULL, NULL, NULL, 17.500000, 'Salida de 6000 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(438, 1775263482895, '2026-02-27', '2026-04-04 00:44:42', 3, 'comision', 'MXN', 1260.000000, NULL, 1, 10, NULL, NULL, NULL, NULL, NULL, NULL, 17.500000, 'Utilidad FIFO por venta de 6000 USDT a TC 17.5', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(439, 1775263482895, '2026-02-27', '2026-04-04 00:44:42', 3, 'entrada', 'USDT', 6000.000000, NULL, 31, 10, NULL, NULL, NULL, NULL, NULL, NULL, 17.500000, 'Registro venta: 6000 USDT a TC 17.5 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(440, 1775263551334, '2026-03-03', '2026-04-04 00:45:51', 6, 'entrada', 'USDT', 39821.000000, NULL, 38, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.700000, 'Compra de 39821 USDT a TC 17.7', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(441, 1775263551334, '2026-03-03', '2026-04-04 00:45:51', 6, 'salida', 'USDT', 39821.000000, NULL, 19, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.700000, 'Adeudo a proveedor: 39821 USDT (TC: 17.7)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(442, 1775263682403, '2026-03-03', '2026-04-04 00:48:02', 6, 'entrada', 'USDT', 10000.000000, NULL, 38, NULL, NULL, 3, NULL, NULL, NULL, NULL, 17.500000, 'Compra de 10000 USDT a TC 17.5', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(443, 1775263682403, '2026-03-03', '2026-04-04 00:48:02', 6, 'salida', 'MXN', 175000.000000, 2, NULL, NULL, NULL, 3, NULL, NULL, NULL, NULL, 17.500000, 'Pago de contado: $175000 MXN por 10000 USDT (TC: 17.5)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(444, 1775263811542, '2026-03-03', '2026-04-04 00:50:11', 3, 'entrada', 'MXN', 581643.687996, NULL, 2, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.958800, 'Venta de 32387.67 USDT a TC 17.9588', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(445, 1775263811542, '2026-03-03', '2026-04-04 00:50:11', 3, 'salida', 'USDT', 32387.670000, 38, NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.958800, 'Salida de 32387.67 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(446, 1775263811542, '2026-03-03', '2026-04-04 00:50:11', 3, 'comision', 'MXN', 8381.928996, NULL, 1, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.958800, 'Utilidad FIFO por venta de 32387.67 USDT a TC 17.9588', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(447, 1775263811542, '2026-03-03', '2026-04-04 00:50:11', 3, 'entrada', 'USDT', 32387.670000, NULL, 30, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.958800, 'Registro venta: 32387.67 USDT a TC 17.9588 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(448, 1775263862384, '2026-03-04', '2026-04-04 00:51:02', 11, 'salida', 'MXN', 704831.700000, 2, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.700000, 'Pago a proveedor Emilio Agraz — 39821 USDT (TC: 17.7)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(449, 1775263929537, '2026-03-05', '2026-04-04 00:52:09', 6, 'entrada', 'USDT', 891.600000, NULL, 38, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.500000, 'Compra de 891.6 USDT a TC 17.5', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(450, 1775263929537, '2026-03-05', '2026-04-04 00:52:09', 6, 'salida', 'MXN', 15603.000000, 32, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.500000, 'Pago de contado: $15603 MXN por 891.6 USDT (TC: 17.5)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(451, 1775263986490, '2026-03-05', '2026-04-04 00:53:06', 6, 'entrada', 'USDT', 6953.730000, NULL, 38, NULL, NULL, 3, NULL, NULL, NULL, NULL, 17.600000, 'Compra de 6953.73 USDT a TC 17.6', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(452, 1775263986490, '2026-03-05', '2026-04-04 00:53:06', 6, 'salida', 'MXN', 122385.648000, 2, NULL, NULL, NULL, 3, NULL, NULL, NULL, NULL, 17.600000, 'Pago de contado: $122385.648 MXN por 6953.73 USDT (TC: 17.6)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(453, 1775264193096, '2026-03-05', '2026-04-04 00:56:33', 3, 'entrada', 'MXN', 107099.996000, NULL, 32, 10, NULL, NULL, NULL, NULL, NULL, NULL, 17.900000, 'Venta de 5983.24 USDT a TC 17.9', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(454, 1775264193096, '2026-03-05', '2026-04-04 00:56:33', 3, 'salida', 'USDT', 5983.240000, 38, NULL, 10, NULL, NULL, NULL, NULL, NULL, NULL, 17.900000, 'Salida de 5983.24 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(455, 1775264193096, '2026-03-05', '2026-04-04 00:56:33', 3, 'comision', 'MXN', 1196.648000, NULL, 1, 10, NULL, NULL, NULL, NULL, NULL, NULL, 17.900000, 'Utilidad FIFO por venta de 5983.24 USDT a TC 17.9', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(456, 1775264193096, '2026-03-05', '2026-04-04 00:56:33', 3, 'entrada', 'USDT', 5983.240000, NULL, 31, 10, NULL, NULL, NULL, NULL, NULL, NULL, 17.900000, 'Registro venta: 5983.24 USDT a TC 17.9 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(457, 1775264304052, '2026-03-06', '2026-04-04 00:58:24', 6, 'entrada', 'USDT', 7500.000000, NULL, 38, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.600000, 'Compra de 7500 USDT a TC 17.6', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(458, 1775264304052, '2026-03-06', '2026-04-04 00:58:24', 6, 'salida', 'MXN', 132000.000000, 2, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.600000, 'Pago de contado: $132000 MXN por 7500 USDT (TC: 17.6)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(459, 1775264427928, '2026-03-06', '2026-04-04 01:00:27', 3, 'entrada', 'MXN', 111736.725870, NULL, 2, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.111500, 'Venta de 6169.38 USDT a TC 18.1115', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(460, 1775264427928, '2026-03-06', '2026-04-04 01:00:27', 3, 'salida', 'USDT', 6169.380000, 38, NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.111500, 'Salida de 6169.38 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(461, 1775264427928, '2026-03-06', '2026-04-04 01:00:27', 3, 'comision', 'MXN', 3482.557870, NULL, 1, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.111500, 'Utilidad FIFO por venta de 6169.38 USDT a TC 18.1115', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(462, 1775264427928, '2026-03-06', '2026-04-04 01:00:27', 3, 'entrada', 'USDT', 6169.380000, NULL, 30, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.111500, 'Registro venta: 6169.38 USDT a TC 18.1115 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(463, 1775264500331, '2026-03-06', '2026-04-04 01:01:40', 3, 'entrada', 'MXN', 365802.819400, NULL, 32, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.735000, 'Venta de 20626.04 USDT a TC 17.735', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(464, 1775264500331, '2026-03-06', '2026-04-04 01:01:40', 3, 'salida', 'USDT', 20626.040000, 38, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.735000, 'Salida de 20626.04 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(465, 1775264500331, '2026-03-06', '2026-04-04 01:01:40', 3, 'comision', 'MXN', 3401.746400, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.735000, 'Utilidad FIFO por venta de 20626.04 USDT a TC 17.735', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(466, 1775264500331, '2026-03-06', '2026-04-04 01:01:40', 3, 'entrada', 'USDT', 20626.040000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.735000, 'Registro venta: 20626.04 USDT a TC 17.735 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(467, 1775264590492, '2026-03-09', '2026-04-04 01:03:10', 6, 'entrada', 'USDT', 72000.000000, NULL, 38, NULL, NULL, 3, NULL, NULL, NULL, NULL, 17.600000, 'Compra de 72000 USDT a TC 17.6', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(468, 1775264590492, '2026-03-09', '2026-04-04 01:03:10', 6, 'salida', 'MXN', 1267200.000000, 2, NULL, NULL, NULL, 3, NULL, NULL, NULL, NULL, 17.600000, 'Pago de contado: $1267200 MXN por 72000 USDT (TC: 17.6)', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(469, 1775265584567, '2026-03-09', '2026-04-04 01:19:44', 6, 'entrada', 'USDT', 72000.000000, NULL, 38, NULL, NULL, 3, NULL, NULL, NULL, NULL, 17.600000, 'Compra de 72000 USDT a TC 17.6', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(470, 1775265584567, '2026-03-09', '2026-04-04 01:19:44', 6, 'salida', 'USDT', 72000.000000, NULL, 21, NULL, NULL, 3, NULL, NULL, NULL, NULL, 17.600000, 'Adeudo a proveedor: 72000 USDT (TC: 17.6)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(471, 1775265616727, '2026-03-09', '2026-04-04 01:20:16', 11, 'salida', 'MXN', 777200.000000, 2, NULL, NULL, NULL, 3, NULL, NULL, NULL, NULL, 17.600000, 'Pago a proveedor Alejandro Bambas — 44159.09090909 USDT (TC: 17.6)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(472, 1775265684516, '2026-03-09', '2026-04-04 01:21:24', 3, 'entrada', 'MXN', 99174.900000, NULL, 2, 10, NULL, NULL, NULL, NULL, NULL, NULL, 18.031800, 'Venta de 5500 USDT a TC 18.0318', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(473, 1775265684516, '2026-03-09', '2026-04-04 01:21:24', 3, 'salida', 'USDT', 5500.000000, 38, NULL, 10, NULL, NULL, NULL, NULL, NULL, NULL, 18.031800, 'Salida de 5500 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(474, 1775265684516, '2026-03-09', '2026-04-04 01:21:24', 3, 'comision', 'MXN', 2374.900000, NULL, 1, 10, NULL, NULL, NULL, NULL, NULL, NULL, 18.031800, 'Utilidad FIFO por venta de 5500 USDT a TC 18.0318', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(475, 1775265684516, '2026-03-09', '2026-04-04 01:21:24', 3, 'entrada', 'USDT', 5500.000000, NULL, 31, 10, NULL, NULL, NULL, NULL, NULL, NULL, 18.031800, 'Registro venta: 5500 USDT a TC 18.0318 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(476, 1775265747568, '2026-03-09', '2026-04-04 01:22:27', 3, 'entrada', 'MXN', 23775.746684, NULL, 32, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.880400, 'Venta de 1329.71 USDT a TC 17.8804', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(477, 1775265747568, '2026-03-09', '2026-04-04 01:22:27', 3, 'salida', 'USDT', 1329.710000, 38, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.880400, 'Salida de 1329.71 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(478, 1775265747568, '2026-03-09', '2026-04-04 01:22:27', 3, 'comision', 'MXN', 372.850684, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.880400, 'Utilidad FIFO por venta de 1329.71 USDT a TC 17.8804', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(479, 1775265747568, '2026-03-09', '2026-04-04 01:22:27', 3, 'entrada', 'USDT', 1329.710000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.880400, 'Registro venta: 1329.71 USDT a TC 17.8804 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(480, 1775265771084, '2026-03-10', '2026-04-04 01:22:51', 11, 'salida', 'MXN', 490000.000000, 2, NULL, NULL, NULL, 3, NULL, NULL, NULL, NULL, 17.599999, 'Pago a proveedor Alejandro Bambas — 27840.90999257 USDT (TC: 17.59999943)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(481, 1775265893201, '2026-03-10', '2026-04-04 01:24:53', 3, 'entrada', 'MXN', 30339.424000, NULL, 32, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.639200, 'Venta de 1720 USDT a TC 17.6392', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(482, 1775265893201, '2026-03-10', '2026-04-04 01:24:53', 3, 'salida', 'USDT', 1720.000000, 38, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.639200, 'Salida de 1720 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(483, 1775265893201, '2026-03-10', '2026-04-04 01:24:53', 3, 'comision', 'MXN', 67.424000, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.639200, 'Utilidad FIFO por venta de 1720 USDT a TC 17.6392', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(484, 1775265893201, '2026-03-10', '2026-04-04 01:24:53', 3, 'entrada', 'USDT', 1720.000000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.639200, 'Registro venta: 1720 USDT a TC 17.6392 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(487, 1775265970898, '2026-03-11', '2026-04-04 01:26:10', 6, 'entrada', 'USDT', 8869.000000, NULL, 38, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.500000, 'Compra de 8869 USDT a TC 17.5', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(488, 1775265970898, '2026-03-11', '2026-04-04 01:26:10', 6, 'salida', 'USDT', 8869.000000, NULL, 19, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.500000, 'Adeudo a proveedor: 8869 USDT (TC: 17.5)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(489, 1775265996214, '2026-03-11', '2026-04-04 01:26:36', 11, 'salida', 'MXN', 23957.500000, 32, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.500000, 'Pago a proveedor Emilio Agraz — 1369 USDT (TC: 17.5)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(490, 1775266011032, '2026-03-11', '2026-04-04 01:26:51', 11, 'salida', 'MXN', 131250.000000, 2, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.500000, 'Pago a proveedor Emilio Agraz — 7500 USDT (TC: 17.5)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(491, 1775266105925, '2026-03-11', '2026-04-04 01:28:25', 3, 'entrada', 'MXN', 630699.830800, NULL, 2, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.810000, 'Venta de 35412.68 USDT a TC 17.81', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(492, 1775266105925, '2026-03-11', '2026-04-04 01:28:25', 3, 'salida', 'USDT', 35412.680000, 38, NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.810000, 'Salida de 35412.68 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(493, 1775266105925, '2026-03-11', '2026-04-04 01:28:25', 3, 'comision', 'MXN', 7436.662800, NULL, 1, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.810000, 'Utilidad FIFO por venta de 35412.68 USDT a TC 17.81', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(494, 1775266105925, '2026-03-11', '2026-04-04 01:28:25', 3, 'entrada', 'USDT', 35412.680000, NULL, 30, 9, NULL, NULL, NULL, NULL, NULL, NULL, 17.810000, 'Registro venta: 35412.68 USDT a TC 17.81 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(495, 1775266232829, '2026-03-11', '2026-04-04 01:30:32', 3, 'entrada', 'MXN', 394890.335076, NULL, 32, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.715300, 'Venta de 22290.92 USDT a TC 17.7153', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(496, 1775266232829, '2026-03-11', '2026-04-04 01:30:32', 3, 'salida', 'USDT', 22290.920000, 38, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.715300, 'Salida de 22290.92 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(497, 1775266232829, '2026-03-11', '2026-04-04 01:30:32', 3, 'comision', 'MXN', 2570.143076, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.715300, 'Utilidad FIFO por venta de 22290.92 USDT a TC 17.7153', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(498, 1775266232829, '2026-03-11', '2026-04-04 01:30:32', 3, 'entrada', 'USDT', 22290.920000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.715300, 'Registro venta: 22290.92 USDT a TC 17.7153 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(499, 1775266297615, '2026-03-12', '2026-04-04 01:31:37', 6, 'entrada', 'USDT', 12100.000000, NULL, 38, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.910000, 'Compra de 12100 USDT a TC 17.91', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(500, 1775266297615, '2026-03-12', '2026-04-04 01:31:37', 6, 'salida', 'MXN', 216711.000000, 32, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.910000, 'Pago de contado: $216711 MXN por 12100 USDT (TC: 17.91)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(501, 1775266362694, '2026-03-12', '2026-04-04 01:32:42', 6, 'entrada', 'USDT', 10000.000000, NULL, 38, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.600000, 'Compra de 10000 USDT a TC 17.6', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(502, 1775266362694, '2026-03-12', '2026-04-04 01:32:42', 6, 'salida', 'MXN', 176000.000000, 2, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.600000, 'Pago de contado: $176000 MXN por 10000 USDT (TC: 17.6)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(503, 1775266457040, '2026-03-12', '2026-04-04 01:34:17', 3, 'entrada', 'MXN', 658184.247630, NULL, 32, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.927000, 'Venta de 36714.69 USDT a TC 17.927', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(504, 1775266457040, '2026-03-12', '2026-04-04 01:34:17', 3, 'salida', 'USDT', 36714.690000, 38, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.927000, 'Salida de 36714.69 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(505, 1775266457040, '2026-03-12', '2026-04-04 01:34:17', 3, 'comision', 'MXN', 9141.603630, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.927000, 'Utilidad FIFO por venta de 36714.69 USDT a TC 17.927', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(506, 1775266457040, '2026-03-12', '2026-04-04 01:34:17', 3, 'entrada', 'USDT', 36714.690000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.927000, 'Registro venta: 36714.69 USDT a TC 17.927 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(507, 1775266522175, '2026-03-13', '2026-04-04 01:35:22', 6, 'entrada', 'USDT', 21029.000000, NULL, 38, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.970000, 'Compra de 21029 USDT a TC 17.97', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(508, 1775266522175, '2026-03-13', '2026-04-04 01:35:22', 6, 'salida', 'MXN', 377891.130000, 32, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, 17.970000, 'Pago de contado: $377891.13 MXN por 21029 USDT (TC: 17.97)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(509, 1775266587147, '2026-03-13', '2026-04-04 01:36:27', 3, 'entrada', 'MXN', 382806.987000, NULL, 2, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.202900, 'Venta de 21030 USDT a TC 18.2029', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(510, 1775266587147, '2026-03-13', '2026-04-04 01:36:27', 3, 'salida', 'USDT', 21030.000000, 38, NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.202900, 'Salida de 21030 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(511, 1775266587147, '2026-03-13', '2026-04-04 01:36:27', 3, 'comision', 'MXN', 4898.257000, NULL, 1, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.202900, 'Utilidad FIFO por venta de 21030 USDT a TC 18.2029', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(512, 1775266587147, '2026-03-13', '2026-04-04 01:36:27', 3, 'entrada', 'USDT', 21030.000000, NULL, 30, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.202900, 'Registro venta: 21030 USDT a TC 18.2029 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(513, 1775266642082, '2026-03-19', '2026-04-04 01:37:22', 6, 'entrada', 'USDT', 62682.000000, NULL, 38, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.700000, 'Compra de 62682 USDT a TC 17.7', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(514, 1775266642082, '2026-03-19', '2026-04-04 01:37:22', 6, 'salida', 'USDT', 62682.000000, NULL, 19, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.700000, 'Adeudo a proveedor: 62682 USDT (TC: 17.7)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(515, 1775266680634, '2026-03-19', '2026-04-04 01:38:00', 11, 'salida', 'MXN', 897619.400000, 2, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.700000, 'Pago a proveedor Emilio Agraz — 50712.96045198 USDT (TC: 17.7)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(516, 1775266692848, '2026-03-19', '2026-04-04 01:38:12', 11, 'salida', 'MXN', 211852.000000, 32, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.699999, 'Pago a proveedor Emilio Agraz — 11969.04000109 USDT (TC: 17.69999933)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(517, 1775266764297, '2026-03-19', '2026-04-04 01:39:24', 3, 'entrada', 'MXN', 544405.820512, NULL, 32, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.944400, 'Venta de 30338.48 USDT a TC 17.9444', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(518, 1775266764297, '2026-03-19', '2026-04-04 01:39:24', 3, 'salida', 'USDT', 30338.480000, 38, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.944400, 'Salida de 30338.48 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(519, 1775266764297, '2026-03-19', '2026-04-04 01:39:24', 3, 'comision', 'MXN', 7414.724512, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.944400, 'Utilidad FIFO por venta de 30338.48 USDT a TC 17.9444', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(520, 1775266764297, '2026-03-19', '2026-04-04 01:39:24', 3, 'entrada', 'USDT', 30338.480000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.944400, 'Registro venta: 30338.48 USDT a TC 17.9444 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(521, 1775266849266, '2026-03-20', '2026-04-04 01:40:49', 3, 'entrada', 'MXN', 589056.358000, NULL, 2, 12, NULL, NULL, NULL, NULL, NULL, NULL, 18.212500, 'Venta de 32343.52 USDT a TC 18.2125', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(522, 1775266849266, '2026-03-20', '2026-04-04 01:40:49', 3, 'salida', 'USDT', 32343.520000, 38, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, 18.212500, 'Salida de 32343.52 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(523, 1775266849266, '2026-03-20', '2026-04-04 01:40:49', 3, 'comision', 'MXN', 16576.054000, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, 18.212500, 'Utilidad FIFO por venta de 32343.52 USDT a TC 18.2125', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(524, 1775266849266, '2026-03-20', '2026-04-04 01:40:49', 3, 'entrada', 'USDT', 32343.520000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, 18.212500, 'Registro venta: 32343.52 USDT a TC 18.2125 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(525, 1775267361714, '2026-03-23', '2026-04-04 01:49:21', 6, 'entrada', 'USDT', 40000.000000, NULL, 38, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.660000, 'Compra de 40000 USDT a TC 17.66', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(526, 1775267361714, '2026-03-23', '2026-04-04 01:49:21', 6, 'salida', 'MXN', 706400.000000, 2, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.660000, 'Pago de contado: $706400 MXN por 40000 USDT (TC: 17.66)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(527, 1775267568198, '2026-03-23', '2026-04-04 01:52:48', 3, 'entrada', 'MXN', 624608.522760, NULL, 2, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.108500, 'Venta de 34492.56 USDT a TC 18.1085', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(528, 1775267568198, '2026-03-23', '2026-04-04 01:52:48', 3, 'salida', 'USDT', 34492.560000, 38, NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.108500, 'Salida de 34492.56 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(529, 1775267568198, '2026-03-23', '2026-04-04 01:52:48', 3, 'comision', 'MXN', 15469.913160, NULL, 1, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.108500, 'Utilidad FIFO por venta de 34492.56 USDT a TC 18.1085', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(530, 1775267568198, '2026-03-23', '2026-04-04 01:52:48', 3, 'entrada', 'USDT', 34492.560000, NULL, 30, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.108500, 'Registro venta: 34492.56 USDT a TC 18.1085 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(531, 1775267627433, '2026-03-24', '2026-04-04 01:53:47', 6, 'entrada', 'USDT', 139000.000000, NULL, 38, NULL, NULL, 3, NULL, NULL, NULL, NULL, 17.600000, 'Compra de 139000 USDT a TC 17.6', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(532, 1775267627433, '2026-03-24', '2026-04-04 01:53:47', 6, 'salida', 'USDT', 139000.000000, NULL, 21, NULL, NULL, 3, NULL, NULL, NULL, NULL, 17.600000, 'Adeudo a proveedor: 139000 USDT (TC: 17.6)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(533, 1775267652611, '2026-03-24', '2026-04-04 01:54:12', 7, 'traspaso', 'MXN', 1400000.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(534, 1775267814064, '2026-03-24', '2026-04-04 01:56:54', 3, 'entrada', 'MXN', 970824.096416, NULL, 32, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.756800, 'Venta de 54673.37 USDT a TC 17.7568', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(535, 1775267814064, '2026-03-24', '2026-04-04 01:56:54', 3, 'salida', 'USDT', 54673.370000, 38, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.756800, 'Salida de 54673.37 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(536, 1775267814064, '2026-03-24', '2026-04-04 01:56:54', 3, 'comision', 'MXN', 8242.338016, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.756800, 'Utilidad FIFO por venta de 54673.37 USDT a TC 17.7568', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(537, 1775267814064, '2026-03-24', '2026-04-04 01:56:54', 3, 'entrada', 'USDT', 54673.370000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.756800, 'Registro venta: 54673.37 USDT a TC 17.7568 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(538, 1775267848031, '2026-03-24', '2026-04-04 01:57:28', 11, 'salida', 'MXN', 1446400.000000, 2, NULL, NULL, NULL, 3, NULL, NULL, NULL, NULL, 17.600000, 'Pago a proveedor Alejandro Bambas — 82181.81818182 USDT (TC: 17.6)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(539, 1775267906650, '2026-03-25', '2026-04-04 01:58:26', 11, 'salida', 'MXN', 1000000.000000, 32, NULL, NULL, NULL, 3, NULL, NULL, NULL, NULL, 17.600001, 'Pago a proveedor Alejandro Bambas — 56818.18001033 USDT (TC: 17.60000056)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(540, 1775268005747, '2026-03-25', '2026-04-04 02:00:05', 3, 'entrada', 'MXN', 831268.203765, NULL, 32, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.758100, 'Venta de 46810.65 USDT a TC 17.7581', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(541, 1775268005747, '2026-03-25', '2026-04-04 02:00:05', 3, 'salida', 'USDT', 46810.650000, 38, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.758100, 'Salida de 46810.65 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(542, 1775268005747, '2026-03-25', '2026-04-04 02:00:05', 3, 'comision', 'MXN', 7400.763765, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.758100, 'Utilidad FIFO por venta de 46810.65 USDT a TC 17.7581', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(543, 1775268005747, '2026-03-25', '2026-04-04 02:00:05', 3, 'entrada', 'USDT', 46810.650000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.758100, 'Registro venta: 46810.65 USDT a TC 17.7581 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(544, 1775268057714, '2026-03-26', '2026-04-04 02:00:57', 6, 'entrada', 'USDT', 19600.000000, NULL, 38, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.750000, 'Compra de 19600 USDT a TC 17.75', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(545, 1775268057714, '2026-03-26', '2026-04-04 02:00:57', 6, 'salida', 'MXN', 347900.000000, 32, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.750000, 'Pago de contado: $347900 MXN por 19600 USDT (TC: 17.75)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(546, 1775268160975, '2026-03-26', '2026-04-04 02:02:40', 3, 'entrada', 'MXN', 483080.309070, NULL, 2, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.005800, 'Venta de 26829.15 USDT a TC 18.0058', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(547, 1775268160975, '2026-03-26', '2026-04-04 02:02:40', 3, 'salida', 'USDT', 26829.150000, 38, NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.005800, 'Salida de 26829.15 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(548, 1775268160975, '2026-03-26', '2026-04-04 02:02:40', 3, 'comision', 'MXN', 10887.269070, NULL, 1, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.005800, 'Utilidad FIFO por venta de 26829.15 USDT a TC 18.0058', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(549, 1775268160975, '2026-03-26', '2026-04-04 02:02:40', 3, 'entrada', 'USDT', 26829.150000, NULL, 30, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.005800, 'Registro venta: 26829.15 USDT a TC 18.0058 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(550, 1775268229978, '2026-03-26', '2026-04-04 02:03:49', 3, 'entrada', 'MXN', 639303.559335, NULL, 32, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.860500, 'Venta de 35794.27 USDT a TC 17.8605', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(551, 1775268229978, '2026-03-26', '2026-04-04 02:03:49', 3, 'salida', 'USDT', 35794.270000, 38, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.860500, 'Salida de 35794.27 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(552, 1775268229978, '2026-03-26', '2026-04-04 02:03:49', 3, 'comision', 'MXN', 6384.407335, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.860500, 'Utilidad FIFO por venta de 35794.27 USDT a TC 17.8605', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(553, 1775268229978, '2026-03-26', '2026-04-04 02:03:49', 3, 'entrada', 'USDT', 35794.270000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.860500, 'Registro venta: 35794.27 USDT a TC 17.8605 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(554, 1775268350163, '2026-03-27', '2026-04-04 02:05:50', 6, 'entrada', 'USDT', 5800.000000, NULL, 38, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.900000, 'Compra de 5800 USDT a TC 17.9', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(555, 1775268350163, '2026-03-27', '2026-04-04 02:05:50', 6, 'salida', 'MXN', 103820.000000, 2, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.900000, 'Pago de contado: $103820 MXN por 5800 USDT (TC: 17.9)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(556, 1775268434443, '2026-03-27', '2026-04-04 02:07:14', 3, 'entrada', 'MXN', 104978.260000, NULL, 32, 12, NULL, NULL, NULL, NULL, NULL, NULL, 18.099700, 'Venta de 5800 USDT a TC 18.0997', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(557, 1775268434443, '2026-03-27', '2026-04-04 02:07:14', 3, 'salida', 'USDT', 5800.000000, 38, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, 18.099700, 'Salida de 5800 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(558, 1775268434443, '2026-03-27', '2026-04-04 02:07:14', 3, 'comision', 'MXN', 1158.260000, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, 18.099700, 'Utilidad FIFO por venta de 5800 USDT a TC 18.0997', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(559, 1775268434443, '2026-03-27', '2026-04-04 02:07:14', 3, 'entrada', 'USDT', 5800.000000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, 18.099700, 'Registro venta: 5800 USDT a TC 18.0997 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(560, 1775268510351, '2026-03-30', '2026-04-04 02:08:30', 6, 'entrada', 'USDT', 80000.000000, NULL, 38, NULL, NULL, 3, NULL, NULL, NULL, NULL, 17.900000, 'Compra de 80000 USDT a TC 17.9', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(561, 1775268510351, '2026-03-30', '2026-04-04 02:08:30', 6, 'salida', 'MXN', 1432000.000000, 32, NULL, NULL, NULL, 3, NULL, NULL, NULL, NULL, 17.900000, 'Pago de contado: $1432000 MXN por 80000 USDT (TC: 17.9)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(562, 1775268617945, '2026-03-30', '2026-04-04 02:10:17', 3, 'entrada', 'MXN', 471728.640000, NULL, 2, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.426900, 'Venta de 25600 USDT a TC 18.4269', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(563, 1775268617945, '2026-03-30', '2026-04-04 02:10:17', 3, 'salida', 'USDT', 25600.000000, 38, NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.426900, 'Salida de 25600 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(564, 1775268617945, '2026-03-30', '2026-04-04 02:10:17', 3, 'comision', 'MXN', 13488.640000, NULL, 1, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.426900, 'Utilidad FIFO por venta de 25600 USDT a TC 18.4269', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(565, 1775268617945, '2026-03-30', '2026-04-04 02:10:17', 3, 'entrada', 'USDT', 25600.000000, NULL, 30, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.426900, 'Registro venta: 25600 USDT a TC 18.4269 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(566, 1775268677721, '2026-03-30', '2026-04-04 02:11:17', 3, 'entrada', 'MXN', 621707.005986, NULL, 32, 12, NULL, NULL, NULL, NULL, NULL, NULL, 18.143400, 'Venta de 34266.29 USDT a TC 18.1434', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(567, 1775268677721, '2026-03-30', '2026-04-04 02:11:17', 3, 'salida', 'USDT', 34266.290000, 38, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, 18.143400, 'Salida de 34266.29 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(568, 1775268677721, '2026-03-30', '2026-04-04 02:11:17', 3, 'comision', 'MXN', 8340.414986, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, 18.143400, 'Utilidad FIFO por venta de 34266.29 USDT a TC 18.1434', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(569, 1775268677721, '2026-03-30', '2026-04-04 02:11:17', 3, 'entrada', 'USDT', 34266.290000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, 18.143400, 'Registro venta: 34266.29 USDT a TC 18.1434 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(572, 1775268774585, '2026-03-31', '2026-04-04 02:12:54', 6, 'entrada', 'USDT', 10000.000000, NULL, 38, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.800000, 'Compra de 10000 USDT a TC 17.8', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(573, 1775268774585, '2026-03-31', '2026-04-04 02:12:54', 6, 'salida', 'MXN', 178000.000000, 32, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.800000, 'Pago de contado: $178000 MXN por 10000 USDT (TC: 17.8)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(574, 1775268839479, '2026-03-31', '2026-04-04 02:13:59', 6, 'entrada', 'USDT', 60500.000000, NULL, 38, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.800000, 'Compra de 60500 USDT a TC 17.8', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(575, 1775268839479, '2026-03-31', '2026-04-04 02:13:59', 6, 'salida', 'MXN', 1076900.000000, 2, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.800000, 'Pago de contado: $1076900 MXN por 60500 USDT (TC: 17.8)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(576, 1775268903798, '2026-03-31', '2026-04-04 02:15:03', 3, 'entrada', 'MXN', 625198.160460, NULL, 2, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.355600, 'Venta de 34060.35 USDT a TC 18.3556', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(577, 1775268903798, '2026-03-31', '2026-04-04 02:15:03', 3, 'salida', 'USDT', 34060.350000, 38, NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.355600, 'Salida de 34060.35 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(578, 1775268903798, '2026-03-31', '2026-04-04 02:15:03', 3, 'comision', 'MXN', 16910.559460, NULL, 1, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.355600, 'Utilidad FIFO por venta de 34060.35 USDT a TC 18.3556', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(579, 1775268903798, '2026-03-31', '2026-04-04 02:15:03', 3, 'entrada', 'USDT', 34060.350000, NULL, 30, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.355600, 'Registro venta: 34060.35 USDT a TC 18.3556 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(580, 1775268955957, '2026-03-31', '2026-04-04 02:15:55', 3, 'entrada', 'MXN', 893426.252040, NULL, 32, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.923500, 'Venta de 49846.64 USDT a TC 17.9235', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(581, 1775268955957, '2026-03-31', '2026-04-04 02:15:55', 3, 'salida', 'USDT', 49846.640000, 38, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.923500, 'Salida de 49846.64 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(582, 1775268955957, '2026-03-31', '2026-04-04 02:15:55', 3, 'comision', 'MXN', 6156.060040, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.923500, 'Utilidad FIFO por venta de 49846.64 USDT a TC 17.9235', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(583, 1775268955957, '2026-03-31', '2026-04-04 02:15:55', 3, 'entrada', 'USDT', 49846.640000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.923500, 'Registro venta: 49846.64 USDT a TC 17.9235 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(584, 1775269056788, '2026-04-01', '2026-04-04 02:17:36', 6, 'entrada', 'USDT', 1354.000000, NULL, 38, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.691600, 'Compra de 1354 USDT a TC 17.6916', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(585, 1775269056788, '2026-04-01', '2026-04-04 02:17:36', 6, 'salida', 'MXN', 23954.426400, 32, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, 17.691600, 'Pago de contado: $23954.4264 MXN por 1354 USDT (TC: 17.6916)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(586, 1775269154355, '2026-04-01', '2026-04-04 02:19:14', 3, 'entrada', 'MXN', 120237.156432, NULL, 32, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.874400, 'Venta de 6726.78 USDT a TC 17.8744', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(587, 1775269154355, '2026-04-01', '2026-04-04 02:19:14', 3, 'salida', 'USDT', 6726.780000, 38, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.874400, 'Salida de 6726.78 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(588, 1775269154355, '2026-04-01', '2026-04-04 02:19:14', 3, 'comision', 'MXN', 500.478936, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.874400, 'Utilidad FIFO por venta de 6726.78 USDT a TC 17.8744', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(589, 1775269154355, '2026-04-01', '2026-04-04 02:19:14', 3, 'entrada', 'USDT', 6726.780000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.874400, 'Registro venta: 6726.78 USDT a TC 17.8744 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(590, 1775679444014, '2026-04-08', '2026-04-08 20:17:24', 6, 'entrada', 'USDT', 44782.380000, NULL, 38, NULL, NULL, 3, NULL, NULL, NULL, NULL, 17.300000, 'Compra de 44782.38 USDT a TC 17.3', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(591, 1775679444014, '2026-04-08', '2026-04-08 20:17:24', 6, 'salida', 'USDT', 44782.380000, NULL, 21, NULL, NULL, 3, NULL, NULL, NULL, NULL, 17.300000, 'Adeudo a proveedor: 44782.38 USDT (TC: 17.3)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(592, 1776115315337, '2026-04-07', '2026-04-13 21:21:55', 3, 'entrada', 'MXN', 370379.882880, NULL, 2, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.081000, 'Venta de 20484.48 USDT a TC 18.081', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(593, 1776115315337, '2026-04-07', '2026-04-13 21:21:55', 3, 'salida', 'USDT', 20484.480000, 38, NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.081000, 'Salida de 20484.48 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(594, 1776115315337, '2026-04-07', '2026-04-13 21:21:55', 3, 'comision', 'MXN', 15468.175976, NULL, 1, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.081000, 'Utilidad FIFO por venta de 20484.48 USDT a TC 18.081', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(595, 1776115315337, '2026-04-07', '2026-04-13 21:21:55', 3, 'entrada', 'USDT', 20484.480000, NULL, 30, 9, NULL, NULL, NULL, NULL, NULL, NULL, 18.081000, 'Registro venta: 20484.48 USDT a TC 18.081 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(596, 1776115383017, '2026-04-09', '2026-04-13 21:23:03', 3, 'entrada', 'MXN', 368257.080356, NULL, 32, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.426200, 'Venta de 21132.38 USDT a TC 17.4262', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(597, 1776115383017, '2026-04-09', '2026-04-13 21:23:03', 3, 'salida', 'USDT', 21132.380000, 38, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.426200, 'Salida de 21132.38 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(598, 1776115383017, '2026-04-09', '2026-04-13 21:23:03', 3, 'comision', 'MXN', 2666.906356, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.426200, 'Utilidad FIFO por venta de 21132.38 USDT a TC 17.4262', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(599, 1776115383017, '2026-04-09', '2026-04-13 21:23:03', 3, 'entrada', 'USDT', 21132.380000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.426200, 'Registro venta: 21132.38 USDT a TC 17.4262 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL);
INSERT INTO `movimientos` (`id`, `grupo_id`, `fecha`, `creado_en`, `servicio_id`, `tipo`, `moneda`, `monto`, `cuenta_origen_id`, `cuenta_destino_id`, `cliente_id`, `promotor_id`, `proveedor_id`, `prestamista_id`, `gasto_id`, `referencia_id`, `referencia_tipo`, `paridad`, `descripcion`, `usuario_id`, `activo`, `estado`, `es_reverso`, `movimiento_original_id`, `motivo_correccion`, `corregido_por_usuario_id`, `corregido_en`) VALUES
(600, 1776115424408, '2026-04-09', '2026-04-13 21:23:44', 7, 'traspaso', 'MXN', 727400.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(601, 1776115481337, '2026-04-08', '2026-04-13 21:24:41', 11, 'salida', 'MXN', 774735.170000, 2, NULL, NULL, NULL, 3, NULL, NULL, NULL, NULL, 17.300000, 'Pago a proveedor Alejandro Bambas — 44782.38000176 USDT (TC: 17.29999991)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(602, 1776115537542, '2026-04-10', '2026-04-13 21:25:37', 6, 'entrada', 'USDT', 105000.000000, NULL, 38, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17.150000, 'Compra de 105000 USDT a TC 17.15', NULL, 0, 'activo', 0, NULL, NULL, NULL, NULL),
(603, 1776115584563, '2026-04-10', '2026-04-13 21:26:24', 6, 'entrada', 'USDT', 105000.000000, NULL, 38, NULL, NULL, 3, NULL, NULL, NULL, NULL, 17.150000, 'Compra de 105000 USDT a TC 17.15', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(604, 1776115584563, '2026-04-10', '2026-04-13 21:26:24', 6, 'salida', 'USDT', 105000.000000, NULL, 21, NULL, NULL, 3, NULL, NULL, NULL, NULL, 17.150000, 'Adeudo a proveedor: 105000 USDT (TC: 17.15)', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(605, 1776115627324, '2026-04-10', '2026-04-13 21:27:07', 7, 'traspaso', 'MXN', 840500.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(606, 1776115640382, '2026-04-10', '2026-04-13 21:27:20', 11, 'salida', 'MXN', 1800750.000000, 2, NULL, NULL, NULL, 3, NULL, NULL, NULL, NULL, 17.150000, 'Pago a proveedor Alejandro Bambas — 105000 USDT (TC: 17.15)', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(607, 1776115734294, '2026-04-10', '2026-04-13 21:28:54', 3, 'entrada', 'MXN', 771573.388728, NULL, 32, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.371300, 'Venta de 44416.56 USDT a TC 17.3713', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(608, 1776115734294, '2026-04-10', '2026-04-13 21:28:54', 3, 'salida', 'USDT', 44416.560000, 38, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.371300, 'Salida de 44416.56 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(609, 1776115734294, '2026-04-10', '2026-04-13 21:28:54', 3, 'comision', 'MXN', 9151.465728, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.371300, 'Utilidad FIFO por venta de 44416.56 USDT a TC 17.3713', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(610, 1776115734294, '2026-04-10', '2026-04-13 21:28:54', 3, 'entrada', 'USDT', 44416.560000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.371300, 'Registro venta: 44416.56 USDT a TC 17.3713 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(611, 1776116227826, '2026-03-13', '2026-04-13 21:37:07', 3, 'entrada', 'MXN', 966402.152872, NULL, 32, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.389600, 'Venta de 55573.57 USDT a TC 17.3896', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(612, 1776116227826, '2026-03-13', '2026-04-13 21:37:07', 3, 'salida', 'USDT', 55573.570000, 38, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.389600, 'Salida de 55573.57 USDT del wallet por venta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(613, 1776116227826, '2026-03-13', '2026-04-13 21:37:07', 3, 'comision', 'MXN', 13315.427372, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.389600, 'Utilidad FIFO por venta de 55573.57 USDT a TC 17.3896', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(614, 1776116227826, '2026-03-13', '2026-04-13 21:37:07', 3, 'entrada', 'USDT', 55573.570000, NULL, 42, 12, NULL, NULL, NULL, NULL, NULL, NULL, 17.389600, 'Registro venta: 55573.57 USDT a TC 17.3896 al cliente', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(615, 1776125492061, '2026-01-04', '2026-04-14 00:11:32', 12, 'salida', 'MXN', 120000.000000, 32, NULL, NULL, NULL, NULL, NULL, 2, NULL, NULL, NULL, 'Gasto: Pago Amiri - Diego - Diego', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(616, 1776125492061, '2026-01-04', '2026-04-14 00:11:32', 12, 'salida', 'MXN', 120000.000000, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Pago Amiri - Diego - Diego', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(617, 1776125567014, '2026-01-01', '2026-04-14 00:12:47', 12, 'salida', 'MXN', 2000.000000, 2, NULL, NULL, NULL, NULL, NULL, 3, NULL, NULL, NULL, 'Gasto: Pago Alondra - ', 2, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-14 00:14:23'),
(618, 1776125567014, '2026-01-01', '2026-04-14 00:12:47', 12, 'salida', 'MXN', 2000.000000, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Pago Alondra - ', 2, 1, 'anulado', 0, NULL, 'Reversión del último movimiento', 2, '2026-04-14 00:14:23'),
(619, 1776125567015, '2026-04-13', '2026-04-14 00:14:23', 12, 'ajuste', 'MXN', -2000.000000, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1776125567014 - Gasto: Pago Alondra - ', 2, 1, 'activo', 1, 617, 'Reversión del último movimiento', NULL, NULL),
(620, 1776125567015, '2026-04-13', '2026-04-14 00:14:23', 12, 'ajuste', 'MXN', -2000.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'REVERSO: Anulación de grupo #1776125567014 - Gasto (desde Comisiones AMIRI): Pago Alondra - ', 2, 1, 'activo', 1, 618, 'Reversión del último movimiento', NULL, NULL),
(621, 1776125725937, '2026-01-12', '2026-04-14 00:15:25', 12, 'salida', 'MXN', 45000.000000, 2, NULL, NULL, NULL, NULL, NULL, 4, NULL, NULL, NULL, 'Gasto: Pago Amiri - Diego - Se Los Mande A Areli', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(622, 1776125725937, '2026-01-12', '2026-04-14 00:15:25', 12, 'salida', 'MXN', 45000.000000, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Pago Amiri - Diego - Se Los Mande A Areli', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(623, 1776125776595, '2026-01-12', '2026-04-14 00:16:16', 12, 'salida', 'MXN', 25170.040000, 2, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, 'Gasto: Pago Amiri - Emiliano - Emiliano', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(624, 1776125776595, '2026-01-12', '2026-04-14 00:16:16', 12, 'salida', 'MXN', 25170.040000, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Pago Amiri - Emiliano - Emiliano', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(625, 1776125841220, '2026-01-19', '2026-04-14 00:17:21', 12, 'salida', 'MXN', 20932.480000, 2, NULL, NULL, NULL, NULL, NULL, 6, NULL, NULL, NULL, 'Gasto: Pago Amiri - Emiliano - Diego', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(626, 1776125841220, '2026-01-19', '2026-04-14 00:17:21', 12, 'salida', 'MXN', 20932.480000, 1, NULL, NULL, NULL, NULL, NULL, 6, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Pago Amiri - Emiliano - Diego', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(627, 1776125910277, '2026-01-26', '2026-04-14 00:18:30', 12, 'salida', 'MXN', 21050.000000, 2, NULL, NULL, NULL, NULL, NULL, 7, NULL, NULL, NULL, 'Gasto: Cuentas Pro Amiri - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(628, 1776125910277, '2026-01-26', '2026-04-14 00:18:30', 12, 'salida', 'MXN', 21050.000000, 1, NULL, NULL, NULL, NULL, NULL, 7, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Cuentas Pro Amiri - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(629, 1776125910313, '2026-01-26', '2026-04-14 00:18:30', 12, 'salida', 'MXN', 21050.000000, 2, NULL, NULL, NULL, NULL, NULL, 8, NULL, NULL, NULL, 'Gasto: Cuentas Pro Amiri - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(630, 1776125910313, '2026-01-26', '2026-04-14 00:18:30', 12, 'salida', 'MXN', 21050.000000, 1, NULL, NULL, NULL, NULL, NULL, 8, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Cuentas Pro Amiri - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(631, 1776125937717, '2026-01-26', '2026-04-14 00:18:57', 12, 'salida', 'MXN', 4500.000000, 2, NULL, NULL, NULL, NULL, NULL, 9, NULL, NULL, NULL, 'Gasto: Peeter - Kia', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(632, 1776125937717, '2026-01-26', '2026-04-14 00:18:57', 12, 'salida', 'MXN', 4500.000000, 1, NULL, NULL, NULL, NULL, NULL, 9, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Peeter - Kia', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(633, 1776125984791, '2026-01-29', '2026-04-14 00:19:44', 12, 'salida', 'MXN', 1400.000000, 2, NULL, NULL, NULL, NULL, NULL, 10, NULL, NULL, NULL, 'Gasto: Pago Alondra - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(634, 1776125984791, '2026-01-29', '2026-04-14 00:19:44', 12, 'salida', 'MXN', 1400.000000, 1, NULL, NULL, NULL, NULL, NULL, 10, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Pago Alondra - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(635, 1776126054621, '2026-02-03', '2026-04-14 00:20:54', 12, 'salida', 'MXN', 6290.000000, 32, NULL, NULL, NULL, NULL, NULL, 11, NULL, NULL, NULL, 'Gasto: Comisiones Rojas - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(636, 1776126054621, '2026-02-03', '2026-04-14 00:20:54', 12, 'salida', 'MXN', 6290.000000, 1, NULL, NULL, NULL, NULL, NULL, 11, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Comisiones Rojas - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(637, 1776126083415, '2026-02-11', '2026-04-14 00:21:23', 12, 'salida', 'MXN', 1000.000000, 2, NULL, NULL, NULL, NULL, NULL, 12, NULL, NULL, NULL, 'Gasto: Recargas - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(638, 1776126083415, '2026-02-11', '2026-04-14 00:21:23', 12, 'salida', 'MXN', 1000.000000, 1, NULL, NULL, NULL, NULL, NULL, 12, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Recargas - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(639, 1776126118570, '2026-02-04', '2026-04-14 00:21:58', 12, 'salida', 'MXN', 14000.000000, 2, NULL, NULL, NULL, NULL, NULL, 13, NULL, NULL, NULL, 'Gasto: Comisiones Bancarias - Multa', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(640, 1776126118570, '2026-02-04', '2026-04-14 00:21:58', 12, 'salida', 'MXN', 14000.000000, 1, NULL, NULL, NULL, NULL, NULL, 13, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Comisiones Bancarias - Multa', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(641, 1776126160722, '2026-02-16', '2026-04-14 00:22:40', 12, 'salida', 'MXN', 10000.000000, 2, NULL, NULL, NULL, NULL, NULL, 14, NULL, NULL, NULL, 'Gasto: Renta - Apartado Depa Landmark', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(642, 1776126160722, '2026-02-16', '2026-04-14 00:22:40', 12, 'salida', 'MXN', 10000.000000, 1, NULL, NULL, NULL, NULL, NULL, 14, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Renta - Apartado Depa Landmark', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(643, 1776126206981, '2026-02-18', '2026-04-14 00:23:26', 12, 'salida', 'MXN', 3000.000000, 2, NULL, NULL, NULL, NULL, NULL, 15, NULL, NULL, NULL, 'Gasto: Renta - Representante Yudva Eder', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(644, 1776126206981, '2026-02-18', '2026-04-14 00:23:26', 12, 'salida', 'MXN', 3000.000000, 1, NULL, NULL, NULL, NULL, NULL, 15, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Renta - Representante Yudva Eder', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(645, 1776126257331, '2026-02-18', '2026-04-14 00:24:17', 12, 'salida', 'MXN', 50000.000000, 2, NULL, NULL, NULL, NULL, NULL, 16, NULL, NULL, NULL, 'Gasto: Renta - Renta Depa Landmark', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(646, 1776126257331, '2026-02-18', '2026-04-14 00:24:17', 12, 'salida', 'MXN', 50000.000000, 1, NULL, NULL, NULL, NULL, NULL, 16, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Renta - Renta Depa Landmark', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(647, 1776126302805, '2026-02-20', '2026-04-14 00:25:02', 12, 'salida', 'MXN', 18000.000000, 2, NULL, NULL, NULL, NULL, NULL, 17, NULL, NULL, NULL, 'Gasto: Cuentas Pro Amiri - Para Cuentas', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(648, 1776126302805, '2026-02-20', '2026-04-14 00:25:02', 12, 'salida', 'MXN', 18000.000000, 1, NULL, NULL, NULL, NULL, NULL, 17, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Cuentas Pro Amiri - Para Cuentas', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(649, 1776126337979, '2026-02-25', '2026-04-14 00:25:37', 12, 'salida', 'MXN', 5000.000000, 2, NULL, NULL, NULL, NULL, NULL, 18, NULL, NULL, NULL, 'Gasto: Renta - Uso Montacargas Landmark', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(650, 1776126337979, '2026-02-25', '2026-04-14 00:25:37', 12, 'salida', 'MXN', 5000.000000, 1, NULL, NULL, NULL, NULL, NULL, 18, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Renta - Uso Montacargas Landmark', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(651, 1776126411450, '2026-02-27', '2026-04-14 00:26:51', 12, 'salida', 'MXN', 1500.000000, 2, NULL, NULL, NULL, NULL, NULL, 19, NULL, NULL, NULL, 'Gasto: Papeleria - Papel Burbuja Y Emplaye', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(652, 1776126411450, '2026-02-27', '2026-04-14 00:26:51', 12, 'salida', 'MXN', 1500.000000, 1, NULL, NULL, NULL, NULL, NULL, 19, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Papeleria - Papel Burbuja Y Emplaye', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(653, 1776126446073, '2026-02-27', '2026-04-14 00:27:26', 12, 'salida', 'MXN', 500.000000, 2, NULL, NULL, NULL, NULL, NULL, 20, NULL, NULL, NULL, 'Gasto: Renta - Wifi', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(654, 1776126446073, '2026-02-27', '2026-04-14 00:27:26', 12, 'salida', 'MXN', 500.000000, 1, NULL, NULL, NULL, NULL, NULL, 20, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Renta - Wifi', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(655, 1776126490216, '2026-02-27', '2026-04-14 00:28:10', 12, 'salida', 'MXN', 18500.000000, 2, NULL, NULL, NULL, NULL, NULL, 21, NULL, NULL, NULL, 'Gasto: Renta - Gastos De Mudanza', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(656, 1776126490216, '2026-02-27', '2026-04-14 00:28:10', 12, 'salida', 'MXN', 18500.000000, 1, NULL, NULL, NULL, NULL, NULL, 21, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Renta - Gastos De Mudanza', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(657, 1776126539533, '2026-03-02', '2026-04-14 00:28:59', 12, 'salida', 'MXN', 8450.000000, 32, NULL, NULL, NULL, NULL, NULL, 22, NULL, NULL, NULL, 'Gasto: Comisiones Rojas - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(658, 1776126539533, '2026-03-02', '2026-04-14 00:28:59', 12, 'salida', 'MXN', 8450.000000, 1, NULL, NULL, NULL, NULL, NULL, 22, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Comisiones Rojas - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(659, 1776126560593, '2026-03-02', '2026-04-14 00:29:20', 12, 'salida', 'MXN', 24000.000000, 2, NULL, NULL, NULL, NULL, NULL, 23, NULL, NULL, NULL, 'Gasto: Pago Amiri - Emiliano - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(660, 1776126560593, '2026-03-02', '2026-04-14 00:29:20', 12, 'salida', 'MXN', 24000.000000, 1, NULL, NULL, NULL, NULL, NULL, 23, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Pago Amiri - Emiliano - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(661, 1776126609460, '2026-03-05', '2026-04-14 00:30:09', 12, 'salida', 'MXN', 500.000000, 2, NULL, NULL, NULL, NULL, NULL, 24, NULL, NULL, NULL, 'Gasto: Retiros - Gerardo', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(662, 1776126609460, '2026-03-05', '2026-04-14 00:30:09', 12, 'salida', 'MXN', 500.000000, 1, NULL, NULL, NULL, NULL, NULL, 24, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Retiros - Gerardo', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(663, 1776126661217, '2026-03-12', '2026-04-14 00:31:01', 12, 'salida', 'MXN', 1420.000000, 2, NULL, NULL, NULL, NULL, NULL, 25, NULL, NULL, NULL, 'Gasto: Gas Kia - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(664, 1776126661217, '2026-03-12', '2026-04-14 00:31:01', 12, 'salida', 'MXN', 1420.000000, 1, NULL, NULL, NULL, NULL, NULL, 25, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Gas Kia - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(665, 1776126702242, '2026-03-19', '2026-04-14 00:31:42', 12, 'salida', 'MXN', 500.000000, 2, NULL, NULL, NULL, NULL, NULL, 26, NULL, NULL, NULL, 'Gasto: Limpieza - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(666, 1776126702242, '2026-03-19', '2026-04-14 00:31:42', 12, 'salida', 'MXN', 500.000000, 1, NULL, NULL, NULL, NULL, NULL, 26, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Limpieza - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(667, 1776126742824, '2026-03-24', '2026-04-14 00:32:22', 12, 'salida', 'MXN', 6000.000000, 2, NULL, NULL, NULL, NULL, NULL, 27, NULL, NULL, NULL, 'Gasto: Comisiones Bancarias - Socios Amiri', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(668, 1776126742824, '2026-03-24', '2026-04-14 00:32:22', 12, 'salida', 'MXN', 6000.000000, 1, NULL, NULL, NULL, NULL, NULL, 27, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Comisiones Bancarias - Socios Amiri', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(669, 1776126784240, '2026-03-24', '2026-04-14 00:33:04', 12, 'salida', 'MXN', 500.000000, 2, NULL, NULL, NULL, NULL, NULL, 28, NULL, NULL, NULL, 'Gasto: Estacionamiento - Estacionamientos Gerardo', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(670, 1776126784240, '2026-03-24', '2026-04-14 00:33:04', 12, 'salida', 'MXN', 500.000000, 1, NULL, NULL, NULL, NULL, NULL, 28, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Estacionamiento - Estacionamientos Gerardo', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(671, 1776126806971, '2026-03-24', '2026-04-14 00:33:26', 12, 'salida', 'MXN', 570.000000, 2, NULL, NULL, NULL, NULL, NULL, 29, NULL, NULL, NULL, 'Gasto: Gas Kia - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(672, 1776126806971, '2026-03-24', '2026-04-14 00:33:26', 12, 'salida', 'MXN', 570.000000, 1, NULL, NULL, NULL, NULL, NULL, 29, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Gas Kia - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(673, 1776126881146, '2026-03-27', '2026-04-14 00:34:41', 12, 'salida', 'MXN', 3800.000000, 32, NULL, NULL, NULL, NULL, NULL, 30, NULL, NULL, NULL, 'Gasto: Comisiones Bancarias - Arreglada Caja Fuerte', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(674, 1776126881146, '2026-03-27', '2026-04-14 00:34:41', 12, 'salida', 'MXN', 3800.000000, 1, NULL, NULL, NULL, NULL, NULL, 30, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Comisiones Bancarias - Arreglada Caja Fuerte', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(675, 1776126923456, '2026-04-13', '2026-04-14 00:35:23', 7, 'traspaso', 'MXN', 851000.000000, 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, 'Retiro de Efectivo - Sin Tarjeta', NULL, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(676, 1776126955600, '2026-03-17', '2026-04-14 00:35:55', 12, 'salida', 'MXN', 51500.000000, 2, NULL, NULL, NULL, NULL, NULL, 31, NULL, NULL, NULL, 'Gasto: Pago Amiri - Diego - Entregados A Monica Secretaria', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(677, 1776126955600, '2026-03-17', '2026-04-14 00:35:55', 12, 'salida', 'MXN', 51500.000000, 1, NULL, NULL, NULL, NULL, NULL, 31, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Pago Amiri - Diego - Entregados A Monica Secretaria', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(678, 1776126985249, '2026-03-19', '2026-04-14 00:36:25', 12, 'salida', 'MXN', 3500.000000, 32, NULL, NULL, NULL, NULL, NULL, 32, NULL, NULL, NULL, 'Gasto: Pago Amiri - Diego - Mandados A Alfredo', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(679, 1776126985249, '2026-03-19', '2026-04-14 00:36:25', 12, 'salida', 'MXN', 3500.000000, 1, NULL, NULL, NULL, NULL, NULL, 32, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Pago Amiri - Diego - Mandados A Alfredo', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(680, 1776127026605, '2026-03-26', '2026-04-14 00:37:06', 12, 'salida', 'MXN', 122000.000000, 2, NULL, NULL, NULL, NULL, NULL, 33, NULL, NULL, NULL, 'Gasto: Pago Amiri - Diego - Se Los Mande A Carmela', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(681, 1776127026605, '2026-03-26', '2026-04-14 00:37:06', 12, 'salida', 'MXN', 122000.000000, 1, NULL, NULL, NULL, NULL, NULL, 33, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Pago Amiri - Diego - Se Los Mande A Carmela', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(682, 1776127089922, '2026-03-26', '2026-04-14 00:38:09', 12, 'salida', 'MXN', 11541.160000, 2, NULL, NULL, NULL, NULL, NULL, 34, NULL, NULL, NULL, 'Gasto: Perdida - Cuenta Bancaria Perdida', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(683, 1776127089922, '2026-03-26', '2026-04-14 00:38:09', 12, 'salida', 'MXN', 11541.160000, 1, NULL, NULL, NULL, NULL, NULL, 34, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Perdida - Cuenta Bancaria Perdida', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(684, 1776127122971, '2026-04-01', '2026-04-14 00:38:42', 12, 'salida', 'MXN', 3400.000000, 32, NULL, NULL, NULL, NULL, NULL, 35, NULL, NULL, NULL, 'Gasto: Recargas - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(685, 1776127122971, '2026-04-01', '2026-04-14 00:38:42', 12, 'salida', 'MXN', 3400.000000, 1, NULL, NULL, NULL, NULL, NULL, 35, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Recargas - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(686, 1776127146981, '2026-04-01', '2026-04-14 00:39:06', 12, 'salida', 'MXN', 6614.600000, 32, NULL, NULL, NULL, NULL, NULL, 36, NULL, NULL, NULL, 'Gasto: Comisiones Rojas - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(687, 1776127146981, '2026-04-01', '2026-04-14 00:39:06', 12, 'salida', 'MXN', 6614.600000, 1, NULL, NULL, NULL, NULL, NULL, 36, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Comisiones Rojas - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(688, 1776127175686, '2026-04-01', '2026-04-14 00:39:35', 12, 'salida', 'MXN', 29000.000000, 32, NULL, NULL, NULL, NULL, NULL, 37, NULL, NULL, NULL, 'Gasto: Perdida - Bloqueados A Recuperar', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(689, 1776127175686, '2026-04-01', '2026-04-14 00:39:35', 12, 'salida', 'MXN', 29000.000000, 1, NULL, NULL, NULL, NULL, NULL, 37, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Perdida - Bloqueados A Recuperar', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(690, 1776127203473, '2026-04-01', '2026-04-14 00:40:03', 12, 'salida', 'MXN', 24000.000000, 2, NULL, NULL, NULL, NULL, NULL, 38, NULL, NULL, NULL, 'Gasto: Pago Amiri - Emiliano - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(691, 1776127203473, '2026-04-01', '2026-04-14 00:40:03', 12, 'salida', 'MXN', 24000.000000, 1, NULL, NULL, NULL, NULL, NULL, 38, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Pago Amiri - Emiliano - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(692, 1776127278426, '2026-04-02', '2026-04-14 00:41:18', 12, 'salida', 'MXN', 500.000000, 2, NULL, NULL, NULL, NULL, NULL, 39, NULL, NULL, NULL, 'Gasto: Retiros - Daniel', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(693, 1776127278426, '2026-04-02', '2026-04-14 00:41:18', 12, 'salida', 'MXN', 500.000000, 1, NULL, NULL, NULL, NULL, NULL, 39, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Retiros - Daniel', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(694, 1776127314073, '2026-04-07', '2026-04-14 00:41:54', 12, 'salida', 'MXN', 120.000000, 2, NULL, NULL, NULL, NULL, NULL, 40, NULL, NULL, NULL, 'Gasto: Papeleria - Hojas', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(695, 1776127314073, '2026-04-07', '2026-04-14 00:41:54', 12, 'salida', 'MXN', 120.000000, 1, NULL, NULL, NULL, NULL, NULL, 40, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Papeleria - Hojas', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(696, 1776127334733, '2026-04-07', '2026-04-14 00:42:14', 12, 'salida', 'MXN', 500.000000, 2, NULL, NULL, NULL, NULL, NULL, 41, NULL, NULL, NULL, 'Gasto: Gas Kia - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(697, 1776127334733, '2026-04-07', '2026-04-14 00:42:14', 12, 'salida', 'MXN', 500.000000, 1, NULL, NULL, NULL, NULL, NULL, 41, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Gas Kia - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(698, 1776127358788, '2026-04-08', '2026-04-14 00:42:38', 12, 'salida', 'MXN', 16000.000000, 32, NULL, NULL, NULL, NULL, NULL, 42, NULL, NULL, NULL, 'Gasto: Renta - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(699, 1776127358788, '2026-04-08', '2026-04-14 00:42:38', 12, 'salida', 'MXN', 16000.000000, 1, NULL, NULL, NULL, NULL, NULL, 42, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Renta - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(700, 1776127383312, '2026-04-13', '2026-04-14 00:43:03', 12, 'salida', 'MXN', 500.000000, 2, NULL, NULL, NULL, NULL, NULL, 43, NULL, NULL, NULL, 'Gasto: Gas Kia - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(701, 1776127383312, '2026-04-13', '2026-04-14 00:43:03', 12, 'salida', 'MXN', 500.000000, 1, NULL, NULL, NULL, NULL, NULL, 43, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Gas Kia - ', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(702, 1776127429226, '2026-04-13', '2026-04-14 00:43:49', 12, 'salida', 'MXN', 260.000000, 2, NULL, NULL, NULL, NULL, NULL, 44, NULL, NULL, NULL, 'Gasto: Papeleria - Cinta Doble Cara', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL),
(703, 1776127429226, '2026-04-13', '2026-04-14 00:43:49', 12, 'salida', 'MXN', 260.000000, 1, NULL, NULL, NULL, NULL, NULL, 44, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Papeleria - Cinta Doble Cara', 2, 1, 'activo', 0, NULL, NULL, NULL, NULL);

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
(1, 6, 32, 24, 3036.31, '2026-01-05', 14, 'Pago de comisiones pendientes', 1, '2026-04-01 22:33:56'),
(2, 6, 32, 24, 2343.00, '2026-01-15', 32, 'Pago de comisiones pendientes', 0, '2026-04-02 01:16:20'),
(3, 6, 32, 24, 1171.50, '2026-01-15', 36, 'Pago de comisiones pendientes', 1, '2026-04-02 01:18:12'),
(4, 6, 32, 24, 1733.29, '2026-02-05', 71, 'Pago de comisiones pendientes', 1, '2026-04-02 21:04:53'),
(5, 6, 32, 24, 2377.08, '2026-02-12', 87, 'Pago de comisiones pendientes', 1, '2026-04-02 21:58:57'),
(6, 6, 32, 24, 558.38, '2026-02-12', 96, 'Pago de comisiones pendientes', 1, '2026-04-02 22:04:15'),
(7, 6, 2, 24, 7774.23, '2026-02-16', 110, 'Pago de comisiones pendientes', 0, '2026-04-02 22:12:45'),
(8, 6, 2, 24, 7614.21, '2026-02-16', 112, 'Pago de comisiones pendientes', 1, '2026-04-02 22:13:26'),
(9, 6, 2, 24, 2725.77, '2026-02-18', 127, 'Pago de comisiones pendientes', 1, '2026-04-02 22:19:43'),
(10, 6, 32, 24, 3502.20, '2026-02-18', 128, 'Pago de comisiones pendientes', 1, '2026-04-02 22:19:59'),
(11, 6, 2, 24, 2609.15, '2026-02-20', 138, 'Pago de comisiones pendientes', 1, '2026-04-02 22:24:27'),
(12, 6, 2, 24, 3174.05, '2026-02-27', 145, 'Pago de comisiones pendientes', 1, '2026-04-02 22:27:35'),
(13, 6, 32, 24, 628.35, '2026-02-27', 150, 'Pago de comisiones pendientes', 1, '2026-04-02 22:29:08'),
(14, 6, 32, 24, 5076.14, '2026-02-27', 158, 'Pago de comisiones pendientes', 1, '2026-04-02 22:32:34'),
(15, 6, 32, 24, 2538.07, '2026-03-02', 163, 'Pago de comisiones pendientes', 1, '2026-04-02 22:34:22'),
(16, 6, 32, 24, 2295.92, '2026-03-03', 177, 'Pago de comisiones pendientes', 1, '2026-04-02 22:43:20'),
(17, 6, 2, 24, 1879.79, '2026-03-03', 178, 'Pago de comisiones pendientes', 1, '2026-04-02 22:43:45'),
(18, 6, 32, 24, 1917.00, '2026-03-05', 185, 'Pago de comisiones pendientes', 1, '2026-04-02 22:45:40'),
(19, 6, 2, 24, 3440.99, '2026-03-09', 195, 'Pago de comisiones pendientes', 1, '2026-04-02 22:50:34'),
(20, 6, 32, 24, 1526.15, '2026-03-13', 218, 'Pago de comisiones pendientes', 0, '2026-04-02 22:57:12'),
(21, 6, 32, 24, 1526.19, '2026-03-13', 220, 'Pago de comisiones pendientes', 0, '2026-04-02 22:58:25'),
(22, 6, 32, 24, 1526.13, '2026-03-13', 222, 'Pago de comisiones pendientes', 0, '2026-04-02 22:58:57'),
(23, 6, 32, 24, 1526.17, '2026-03-13', 224, 'Pago de comisiones pendientes', 1, '2026-04-02 22:59:26'),
(24, 6, 32, 24, 3874.35, '2026-02-18', 238, 'Pago de comisiones pendientes', 0, '2026-04-02 23:06:28'),
(25, 6, 32, 24, 3874.35, '2026-03-18', 240, 'Pago de comisiones pendientes', 1, '2026-04-02 23:07:31'),
(26, 6, 32, 24, 913.71, '2026-03-20', 246, 'Pago de comisiones pendientes', 1, '2026-04-02 23:10:10'),
(27, 7, 32, 25, 156.00, '2026-03-24', 258, 'Pago de comisiones pendientes', 1, '2026-04-02 23:16:53'),
(28, 6, 32, 24, 1350.00, '2026-03-30', 273, 'Pago de comisiones pendientes', 1, '2026-04-02 23:22:10'),
(29, 6, 32, 24, 3135.33, '2026-04-01', 278, 'Pago de comisiones pendientes', 0, '2026-04-02 23:24:15'),
(30, 7, 32, 25, 646.00, '2026-04-02', 286, 'Pago de comisiones pendientes', 0, '2026-04-02 23:27:29'),
(31, 6, 32, 24, 2135.33, '2026-04-01', 304, 'Pago de comisiones pendientes', 1, '2026-04-02 23:31:34');

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
(4, 'Hyperion', '33 2726 8440', 'hyperion@gmail.com', '', 1, '2026-03-26 23:59:16', NULL, NULL);

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
(7, 'Missael Jauregui', '33 3167 7268', 'misajauregui@gmail.com', 25, 0, 1, '2026-03-27 00:05:33', NULL, NULL);

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
(9, 'Missael Jauregui', 'misajauregui@gmail.com', '33 3167 7268', 'USD', 1, 1, NULL, 45, 46, 1, '2026-04-04 00:42:36', NULL, NULL);

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
(15, 'Pago de Comisión a Promotor', 'Pago de comisión a promotor', 'salida', 1, '2026-03-13 01:37:15', '2026-03-13 01:37:15');

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
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=223;

--
-- AUTO_INCREMENT de la tabla `categorias_gasto`
--
ALTER TABLE `categorias_gasto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `consumos_inventario`
--
ALTER TABLE `consumos_inventario`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT de la tabla `cuentas`
--
ALTER TABLE `cuentas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT de la tabla `deudas_clientes`
--
ALTER TABLE `deudas_clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT de la tabla `deudas_empresa`
--
ALTER TABLE `deudas_empresa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `directorio_depositos`
--
ALTER TABLE `directorio_depositos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `gastos`
--
ALTER TABLE `gastos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT de la tabla `historial_saldos`
--
ALTER TABLE `historial_saldos`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- AUTO_INCREMENT de la tabla `inventario_monedas`
--
ALTER TABLE `inventario_monedas`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de la tabla `movimientos`
--
ALTER TABLE `movimientos`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=704;

--
-- AUTO_INCREMENT de la tabla `pagos_promotores`
--
ALTER TABLE `pagos_promotores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `prestamistas`
--
ALTER TABLE `prestamistas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `promotores`
--
ALTER TABLE `promotores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
