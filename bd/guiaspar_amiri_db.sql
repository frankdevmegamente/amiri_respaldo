-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 27, 2026 at 10:50 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `guiaspar_amiri_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `auditoria`
--

CREATE TABLE `auditoria` (
  `id` bigint NOT NULL,
  `usuario_id` int NOT NULL COMMENT 'Quién hizo la acción',
  `accion` varchar(50) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'INSERT, UPDATE, DELETE, LOGIN, LOGOUT, etc.',
  `modulo` varchar(50) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'clientes, gastos, usuarios, prestamos, etc.',
  `tabla` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Tabla afectada',
  `registro_id` int DEFAULT NULL COMMENT 'ID del registro afectado',
  `datos_anteriores` json DEFAULT NULL COMMENT 'Valores antes del cambio',
  `datos_nuevos` json DEFAULT NULL COMMENT 'Valores después del cambio',
  `descripcion` text COLLATE utf8mb4_general_ci COMMENT 'Descripción legible de la acción',
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'IP desde donde se hizo',
  `user_agent` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Navegador/dispositivo',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `auditoria`
--

INSERT INTO `auditoria` (`id`, `usuario_id`, `accion`, `modulo`, `tabla`, `registro_id`, `datos_anteriores`, `datos_nuevos`, `descripcion`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'UPDATE', 'usuarios', 'usuarios', 1, '{\"id\": 1, \"rol\": \"admin\", \"email\": \"admin@amiri.com\", \"activo\": 1, \"nombre\": \"Admin Amiri\", \"password\": \"$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi\", \"creado_en\": \"2025-10-29 15:18:03\", \"reset_token\": null, \"reset_expires_at\": null}', '{\"rol\": \"admin\", \"email\": \"admin@amiri.com\", \"nombre\": \"Admin Amiriii\"}', 'Actualizó usuario: Admin Amiriii', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 23:29:39'),
(2, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Admin Amiriii\' inició sesión', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 00:34:39'),
(3, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Admin Amiriii\' inició sesión', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 01:39:41'),
(4, 1, 'INSERT', 'usuarios', 'usuarios', 8, NULL, '{\"id\": \"8\", \"rol\": \"admin\", \"email\": \"usuario@prueba.com\", \"nombre\": \"Usuario Prueba\"}', 'Creó usuario: Usuario Prueba (admin)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 01:55:49'),
(5, 1, 'UPDATE', 'usuarios', 'usuarios', 8, '{\"id\": 8, \"rol\": \"admin\", \"email\": \"usuario@prueba.com\", \"activo\": 1, \"nombre\": \"Usuario Prueba\", \"password\": \"$2y$10$lBl5mlqpeXesItK.F6KdQez0sElqBNjgezANikKUvBjyQWrh8wTCm\", \"creado_en\": \"2026-01-25 19:55:49\", \"reset_token\": null, \"reset_expires_at\": null}', '{\"rol\": \"admin\", \"email\": \"usuario@prueba.com\", \"nombre\": \"Usuario Pruebaaaaa\", \"password\": \"[ACTUALIZADA]\"}', 'Actualizó usuario: Usuario Pruebaaaaa (incluyó cambio de contraseña)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 01:55:58'),
(6, 1, 'DELETE', 'usuarios', 'usuarios', 8, '{\"id\": 8, \"rol\": \"admin\", \"email\": \"usuario@prueba.com\", \"activo\": 1, \"nombre\": \"Usuario Pruebaaaaa\", \"password\": \"$2y$10$/955VGz4/pzQCB07JbwJQuF2ZE36NUwSa4Gs0UCqZuj8eR3dB/iXW\", \"creado_en\": \"2026-01-25 19:55:49\", \"reset_token\": null, \"reset_expires_at\": null}', NULL, 'Desactivó usuario: Usuario Pruebaaaaa', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 01:56:01'),
(7, 1, 'INSERT', 'prestamistas', 'prestamistas', 2, NULL, '{\"id\": \"2\", \"email\": \"prestamista@prueba.com\", \"nombre\": \"Prestamista Prueba\", \"telefono\": \"0987654321\", \"direccion\": \"Av Patria\"}', 'Creó prestamista: Prestamista Prueba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 01:58:13'),
(8, 1, 'UPDATE', 'cuentas', 'cuentas', 14, '{\"id\": 14, \"banco\": \"BANREGIO\", \"activo\": 1, \"nombre\": \"Tarpen SA\", \"creado_en\": \"2026-01-19 13:36:05\", \"tipo_cuenta\": 1, \"tipo_moneda\": \"MXN\", \"saldo_actual\": \"99800.00\", \"numero_cuenta\": \"1029384756102938475\", \"documento_path\": \"uploads/cuentas/cuenta_1768851365_696e87a55f683.png\", \"es_caja_empresa\": 0}', '{\"banco\": \"BANREGIO\", \"activo\": 1, \"nombre\": \"Tarpen SAa\", \"tipo_moneda\": \"MXN\", \"numero_cuenta\": \"1029384756102938475\", \"es_caja_empresa\": 0}', 'Actualizó cuenta: Tarpen SAa', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 02:04:08'),
(9, 1, 'UPDATE', 'cuentas', 'cuentas', 14, '{\"id\": 14, \"banco\": \"BANREGIO\", \"activo\": 1, \"nombre\": \"Tarpen SAa\", \"creado_en\": \"2026-01-19 13:36:05\", \"tipo_cuenta\": 1, \"tipo_moneda\": \"MXN\", \"saldo_actual\": \"99800.00\", \"numero_cuenta\": \"1029384756102938475\", \"documento_path\": \"uploads/cuentas/cuenta_1768851365_696e87a55f683.png\", \"es_caja_empresa\": 0}', '{\"banco\": \"BANREGIO\", \"activo\": 1, \"nombre\": \"Tarpen SA\", \"tipo_moneda\": \"MXN\", \"numero_cuenta\": \"1029384756102938475\", \"es_caja_empresa\": 0}', 'Actualizó cuenta: Tarpen SA', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 02:04:20'),
(10, 1, 'UPDATE', 'cuentas', 'cuentas', 18, '{\"id\": 18, \"banco\": null, \"activo\": 1, \"nombre\": \"Caja Cliente - toño hdez\", \"creado_en\": \"2026-01-22 13:04:46\", \"tipo_cuenta\": 4, \"tipo_moneda\": \"MXN\", \"saldo_actual\": \"0.00\", \"numero_cuenta\": null, \"documento_path\": null, \"es_caja_empresa\": 0}', '{\"banco\": null, \"activo\": 1, \"nombre\": \"Caja Cliente - toño hdezz\", \"tipo_moneda\": \"MXN\", \"numero_cuenta\": null, \"es_caja_empresa\": 0}', 'Actualizó cuenta: Caja Cliente - toño hdezz', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 02:04:34'),
(11, 1, 'UPDATE', 'cuentas', 'cuentas', 18, '{\"id\": 18, \"banco\": null, \"activo\": 1, \"nombre\": \"Caja Cliente - toño hdezz\", \"creado_en\": \"2026-01-22 13:04:46\", \"tipo_cuenta\": 4, \"tipo_moneda\": \"MXN\", \"saldo_actual\": \"0.00\", \"numero_cuenta\": null, \"documento_path\": null, \"es_caja_empresa\": 0}', '{\"banco\": null, \"activo\": 1, \"nombre\": \"Caja Cliente - toño hdez\", \"tipo_moneda\": \"MXN\", \"numero_cuenta\": null, \"es_caja_empresa\": 0}', 'Actualizó cuenta: Caja Cliente - toño hdez', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 02:04:38'),
(12, 1, 'INSERT', 'cuentas', 'cuentas', 20, NULL, '{\"id\": \"20\", \"banco\": \"BBVA\", \"activo\": 1, \"nombre\": \"Cuenta Bancaria Prueba\", \"tipo_cuenta\": 1, \"tipo_moneda\": \"MXN\", \"numero_cuenta\": \"102938475612\", \"es_caja_empresa\": 0}', 'Creó cuenta: Cuenta Bancaria Prueba (MXN)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 02:05:10'),
(13, 1, 'DELETE', 'cuentas', 'cuentas', 20, '{\"id\": 20, \"banco\": \"BBVA\", \"activo\": 1, \"nombre\": \"Cuenta Bancaria Prueba\", \"creado_en\": \"2026-01-25 20:05:10\", \"tipo_cuenta\": 1, \"tipo_moneda\": \"MXN\", \"saldo_actual\": \"0.00\", \"numero_cuenta\": \"102938475612\", \"documento_path\": \"uploads/cuentas/cuenta_1769393110_6976cbd6a798d.png\", \"es_caja_empresa\": 0}', NULL, 'Eliminó cuenta: Cuenta Bancaria Prueba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 02:05:16'),
(14, 1, 'INSERT', 'cuentas', 'cuentas', 21, NULL, '{\"id\": \"21\", \"banco\": null, \"activo\": 1, \"nombre\": \"Caja Prueba\", \"tipo_cuenta\": 2, \"tipo_moneda\": \"MXN\", \"numero_cuenta\": null, \"es_caja_empresa\": 0}', 'Creó cuenta: Caja Prueba (MXN)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 02:05:28'),
(15, 1, 'UPDATE', 'cuentas', 'cuentas', 21, '{\"id\": 21, \"banco\": null, \"activo\": 1, \"nombre\": \"Caja Prueba\", \"creado_en\": \"2026-01-25 20:05:28\", \"tipo_cuenta\": 2, \"tipo_moneda\": \"MXN\", \"saldo_actual\": \"0.00\", \"numero_cuenta\": null, \"documento_path\": null, \"es_caja_empresa\": 0}', '{\"banco\": null, \"activo\": 1, \"nombre\": \"Caja Pruebaaaaa\", \"tipo_moneda\": \"MXN\", \"numero_cuenta\": null, \"es_caja_empresa\": 0}', 'Actualizó cuenta: Caja Pruebaaaaa', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 02:05:33'),
(16, 1, 'DELETE', 'cuentas', 'cuentas', 21, '{\"id\": 21, \"banco\": null, \"activo\": 1, \"nombre\": \"Caja Pruebaaaaa\", \"creado_en\": \"2026-01-25 20:05:28\", \"tipo_cuenta\": 2, \"tipo_moneda\": \"MXN\", \"saldo_actual\": \"0.00\", \"numero_cuenta\": null, \"documento_path\": null, \"es_caja_empresa\": 0}', NULL, 'Eliminó cuenta: Caja Pruebaaaaa', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 02:05:36'),
(17, 1, 'INSERT', 'clientes', 'clientes', 8, NULL, '{\"id\": \"8\", \"nombre\": \"Cliente Prueba\", \"contacto\": \"1029384756\", \"promotor_id\": 7, \"tipo_moneda\": \"MXN\", \"retencion_total\": 15, \"porcentaje_amiri\": 10, \"porcentaje_promotor\": 5}', 'Creó cliente: Cliente Prueba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 02:23:35'),
(18, 1, 'INSERT', 'clientes', 'cuentas', 22, NULL, '{\"id\": \"22\", \"nombre\": \"Caja - Cliente Prueba\", \"tipo_moneda\": \"MXN\"}', 'Creó cuenta automática para cliente: Caja - Cliente Prueba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 02:23:35'),
(19, 1, 'DELETE', 'clientes', 'clientes', 8, '{\"id\": 8, \"nombre\": \"Cliente Prueba\", \"contacto\": \"1029384756\", \"promotor\": \"Kike Rizo\", \"cuenta_id\": 22, \"promotor_id\": 7, \"tipo_moneda\": \"MXN\", \"saldo_actual\": \"0.00\", \"cuenta_nombre\": \"Caja - Cliente Prueba\", \"retencion_total\": \"15.00\", \"porcentaje_amiri\": \"10.00\", \"porcentaje_promotor\": \"5.00\"}', NULL, 'Desactivó cliente: Cliente Prueba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 02:23:52'),
(20, 1, 'DELETE', 'clientes', 'clientes', 8, '{\"id\": 8, \"nombre\": \"Cliente Prueba\", \"contacto\": \"1029384756\", \"promotor\": \"Kike Rizo\", \"cuenta_id\": 22, \"promotor_id\": 7, \"tipo_moneda\": \"MXN\", \"saldo_actual\": \"0.00\", \"cuenta_nombre\": \"Caja - Cliente Prueba\", \"retencion_total\": \"15.00\", \"porcentaje_amiri\": \"10.00\", \"porcentaje_promotor\": \"5.00\"}', NULL, 'Desactivó cliente: Cliente Prueba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 02:24:02'),
(21, 1, 'DELETE', 'clientes', 'clientes', 8, '{\"id\": 8, \"nombre\": \"Cliente Prueba\", \"contacto\": \"1029384756\", \"promotor\": \"Kike Rizo\", \"cuenta_id\": 22, \"promotor_id\": 7, \"tipo_moneda\": \"MXN\", \"saldo_actual\": \"0.00\", \"cuenta_nombre\": \"Caja - Cliente Prueba\", \"retencion_total\": \"15.00\", \"porcentaje_amiri\": \"10.00\", \"porcentaje_promotor\": \"5.00\"}', NULL, 'Desactivó cliente: Cliente Prueba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 02:25:40'),
(22, 1, 'DELETE', 'clientes', 'clientes', 8, '{\"id\": 8, \"nombre\": \"Cliente Prueba\", \"contacto\": \"1029384756\", \"promotor\": \"Kike Rizo\", \"cuenta_id\": 22, \"promotor_id\": 7, \"tipo_moneda\": \"MXN\", \"saldo_actual\": \"0.00\", \"cuenta_nombre\": \"Caja - Cliente Prueba\", \"retencion_total\": \"15.00\", \"porcentaje_amiri\": \"10.00\", \"porcentaje_promotor\": \"5.00\"}', NULL, 'Eliminó cliente: Cliente Prueba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 02:30:58'),
(23, 1, 'DELETE', 'clientes', 'cuentas', 22, '{\"id\": 22, \"banco\": null, \"activo\": 1, \"nombre\": \"Caja - Cliente Prueba\", \"creado_en\": \"2026-01-25 20:23:35\", \"tipo_cuenta\": 2, \"tipo_moneda\": \"MXN\", \"saldo_actual\": \"0.00\", \"numero_cuenta\": null, \"documento_path\": null, \"es_caja_empresa\": 0}', NULL, 'Eliminó cuenta asociada a cliente: Caja - Cliente Prueba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 02:30:58'),
(24, 1, 'UPDATE', 'clientes', 'clientes', 4, '{\"id\": 4, \"nombre\": \"toño hdez\", \"contacto\": \"3382829494\", \"cuenta_id\": 18, \"promotor_id\": 7, \"retencion_total\": \"1.00\", \"porcentaje_amiri\": \"0.80\", \"porcentaje_promotor\": \"0.20\"}', '{\"nombre\": \"toño hdez\", \"contacto\": \"3382829494\", \"promotor_id\": 7, \"tipo_moneda\": \"MXN\", \"retencion_total\": 1, \"porcentaje_amiri\": 0.8, \"porcentaje_promotor\": 0.2}', 'Actualizó cliente: toño hdez', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 02:34:04'),
(25, 1, 'INSERT', 'prestamos', 'prestamos_prestamistas', 10, NULL, '{\"id\": \"10\", \"fecha\": \"2026-01-25\", \"monto\": 1000, \"cuenta_id\": \"7\", \"descripcion\": \"test\", \"prestamista_id\": \"2\", \"prestamista_nombre\": \"Prestamista Prueba\"}', 'Registró préstamo: $1,000.00 de Prestamista Prueba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 02:36:06'),
(26, 1, 'UPDATE', 'prestamos', 'prestamos_prestamistas', 10, '{\"estado\": \"pendiente\", \"monto_pagado\": \"0.00\"}', '{\"estado\": \"parcial\", \"monto_pagado\": 500, \"pago_realizado\": 500}', 'Registró pago de $500.00 al préstamo de Prestamista Prueba (Estado: parcial)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 02:36:39'),
(27, 1, 'UPDATE', 'prestamos', 'prestamos_prestamistas', 10, '{\"estado\": \"parcial\", \"monto_pagado\": \"500.00\"}', '{\"estado\": \"pagado\", \"monto_pagado\": 1000, \"pago_realizado\": 500}', 'Registró pago de $500.00 al préstamo de Prestamista Prueba (Estado: pagado)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 02:36:58'),
(28, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Admin Amiriii\' inició sesión', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 02:48:32'),
(29, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Admin Amiriii\' inició sesión', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 04:30:16'),
(30, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Admin Amiriii\' inició sesión', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 15:22:58'),
(31, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 15:29:17'),
(32, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Admin Amiriii\' inició sesión', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 15:29:22'),
(33, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Admin Amiriii\' inició sesión', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 15:42:57'),
(34, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Admin Amiriii\' inició sesión', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 16:44:41'),
(35, 1, 'INSERT', 'gastos', 'gastos', 1, NULL, '{\"id\": 1, \"fecha\": \"2026-01-27\", \"monto\": \"10.00\", \"concepto\": \"Test\", \"flujo_id\": null, \"creado_en\": \"2026-01-27 11:34:41\", \"cuenta_id\": 7, \"cuenta_nombre\": \"Caja de Efectivo\", \"tipo_gasto_id\": 1, \"tipo_gasto_nombre\": \"Pago de nóminas\"}', 'Registró gasto: $10.00 - Pago de nóminas', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 17:34:41'),
(36, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Admin Amiriii\' inició sesión', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 17:45:25'),
(37, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Admin Amiriii\' inició sesión', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 18:45:33'),
(38, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 19:30:56'),
(39, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Admin Amiriii\' inició sesión', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 19:33:44'),
(40, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 19:34:47'),
(41, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Admin Amiriii\' inició sesión', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 19:34:51'),
(42, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 19:35:55'),
(43, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Admin Amiriii\' inició sesión', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 19:35:59'),
(44, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 19:36:19'),
(45, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Admin Amiriii\' inició sesión', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 19:41:37'),
(46, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 19:41:42'),
(47, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Admin Amiriii\' inició sesión', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 19:44:23'),
(48, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 19:45:08'),
(49, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Admin Amiriii\' inició sesión', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 19:49:43'),
(50, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 19:50:04'),
(51, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Admin Amiriii\' inició sesión', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 19:50:17'),
(52, 1, 'LOGOUT', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Desconocido\' cerró sesión', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 20:00:57'),
(53, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Admin Amiriii\' inició sesión', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 20:01:09'),
(54, 1, 'LOGIN', 'auth', 'usuarios', 1, NULL, NULL, 'Usuario \'Admin Amiriii\' inició sesión', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 22:32:33'),
(55, 1, 'INSERT', 'gastos', 'gastos', 2, NULL, '{\"id\": 2, \"fecha\": \"2026-01-27\", \"monto\": \"6.00\", \"concepto\": \"Test\", \"flujo_id\": null, \"creado_en\": \"2026-01-27 16:39:36\", \"cuenta_id\": 7, \"cuenta_nombre\": \"Caja de Efectivo\", \"tipo_gasto_id\": 1, \"tipo_gasto_nombre\": \"Pago de nóminas\"}', 'Registró gasto: $6.00 - Pago de nóminas', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 22:39:36');

-- --------------------------------------------------------

--
-- Table structure for table `clientes`
--

CREATE TABLE `clientes` (
  `id` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `promotor_id` int NOT NULL,
  `retencion_total` decimal(5,2) DEFAULT NULL,
  `porcentaje_amiri` decimal(5,2) DEFAULT NULL,
  `porcentaje_promotor` decimal(5,2) DEFAULT NULL,
  `contacto` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cuenta_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clientes`
--

INSERT INTO `clientes` (`id`, `nombre`, `promotor_id`, `retencion_total`, `porcentaje_amiri`, `porcentaje_promotor`, `contacto`, `cuenta_id`) VALUES
(4, 'Toño Hdez', 7, 1.00, 0.80, 0.20, '3382829494', 18);

-- --------------------------------------------------------

--
-- Table structure for table `cuentas`
--

CREATE TABLE `cuentas` (
  `id` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `numero_cuenta` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `banco` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `saldo_actual` decimal(12,2) NOT NULL DEFAULT '0.00',
  `tipo_cuenta` int NOT NULL DEFAULT '1',
  `es_caja_empresa` tinyint(1) DEFAULT '0',
  `tipo_moneda` varchar(10) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'MXN',
  `documento_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cuentas`
--

INSERT INTO `cuentas` (`id`, `nombre`, `numero_cuenta`, `banco`, `activo`, `creado_en`, `saldo_actual`, `tipo_cuenta`, `es_caja_empresa`, `tipo_moneda`, `documento_path`) VALUES
(1, 'Comisiones AMIRI', NULL, NULL, 1, '2026-01-16 18:07:00', 808.00, 3, 0, 'MXN', NULL),
(7, 'Caja de Efectivo', NULL, NULL, 1, '2026-01-17 01:50:42', 100800.00, 2, 1, 'MXN', NULL),
(14, 'Tarpen SA', '1029384756102938475', 'BANREGIO', 1, '2026-01-19 19:36:05', 101000.00, 1, 0, 'MXN', 'uploads/cuentas/cuenta_1768851365_696e87a55f683.png'),
(17, 'Comisiones - Kike Rizo', NULL, NULL, 1, '2026-01-22 19:04:18', 2.00, 5, 0, 'MXN', NULL),
(18, 'Caja Cliente - toño hdez', NULL, NULL, 1, '2026-01-22 19:04:46', 990.00, 4, 0, 'MXN', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cuentas_tipo`
--

CREATE TABLE `cuentas_tipo` (
  `id` int NOT NULL,
  `descripcion` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cuentas_tipo`
--

INSERT INTO `cuentas_tipo` (`id`, `descripcion`) VALUES
(1, 'Bancaria'),
(2, 'Caja'),
(3, 'Capital'),
(4, 'Cliente'),
(5, 'Promotor'),
(6, 'Proveedor');

-- --------------------------------------------------------

--
-- Table structure for table `flujo_movimientos`
--

CREATE TABLE `flujo_movimientos` (
  `id` bigint NOT NULL,
  `grupo_id` bigint DEFAULT NULL,
  `fecha` date NOT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `servicio_id` int NOT NULL,
  `tipo` enum('entrada','salida','traspaso','comision','ajuste') COLLATE utf8mb4_general_ci NOT NULL,
  `moneda` enum('MXN','USD','USDT') COLLATE utf8mb4_general_ci NOT NULL,
  `monto` decimal(18,6) NOT NULL,
  `cuenta_origen_id` int DEFAULT NULL,
  `cuenta_destino_id` int DEFAULT NULL,
  `cliente_id` int DEFAULT NULL,
  `promotor_id` int DEFAULT NULL,
  `proveedor_id` int DEFAULT NULL,
  `referencia_id` bigint DEFAULT NULL,
  `referencia_tipo` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `paridad` decimal(18,6) DEFAULT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `activo` tinyint DEFAULT '1',
  `usuario_id` int DEFAULT NULL COMMENT 'Usuario que registró el movimiento',
  `prestamista_id` int DEFAULT NULL COMMENT 'Si es pago/préstamo de prestamista',
  `gasto_id` int DEFAULT NULL COMMENT 'Si es un gasto registrado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `flujo_movimientos`
--

INSERT INTO `flujo_movimientos` (`id`, `grupo_id`, `fecha`, `creado_en`, `servicio_id`, `tipo`, `moneda`, `monto`, `cuenta_origen_id`, `cuenta_destino_id`, `cliente_id`, `promotor_id`, `proveedor_id`, `referencia_id`, `referencia_tipo`, `paridad`, `descripcion`, `activo`, `usuario_id`, `prestamista_id`, `gasto_id`) VALUES
(1, 1769117265393, '2026-01-22', '2026-01-22 21:27:45', 1, 'entrada', 'MXN', 100000.000000, NULL, 14, 4, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', 1, NULL, NULL, NULL),
(2, 1769117265393, '2026-01-22', '2026-01-22 21:27:45', 1, 'entrada', 'MXN', 99000.000000, NULL, 18, 4, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', 1, NULL, NULL, NULL),
(3, 1769117265393, '2026-01-22', '2026-01-22 21:27:45', 1, 'comision', 'MXN', 800.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', 1, NULL, NULL, NULL),
(4, 1769117265393, '2026-01-22', '2026-01-22 21:27:45', 1, 'comision', 'MXN', 200.000000, NULL, 17, NULL, NULL, 7, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', 1, NULL, NULL, NULL),
(5, 1769372236493, '2026-01-25', '2026-01-25 20:17:16', 15, 'salida', 'MXN', 100.000000, 14, 17, NULL, 7, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', 1, NULL, NULL, NULL),
(6, 1769372259606, '2026-01-25', '2026-01-25 20:17:39', 15, 'salida', 'MXN', 100.000000, 14, 17, NULL, 7, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', 1, NULL, NULL, NULL),
(7, 1769393371875, '2026-01-25', '2026-01-26 02:09:31', 1, 'entrada', 'MXN', 1000.000000, NULL, 14, 4, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', 1, NULL, NULL, NULL),
(8, 1769393371875, '2026-01-25', '2026-01-26 02:09:31', 1, 'entrada', 'MXN', 990.000000, NULL, 18, 4, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', 1, NULL, NULL, NULL),
(9, 1769393371875, '2026-01-25', '2026-01-26 02:09:31', 1, 'comision', 'MXN', 8.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', 1, NULL, NULL, NULL),
(10, 1769393371875, '2026-01-25', '2026-01-26 02:09:31', 1, 'comision', 'MXN', 2.000000, NULL, 17, NULL, 7, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', 1, NULL, NULL, NULL),
(11, 1769393449247, '2026-01-25', '2026-01-26 02:10:49', 15, 'salida', 'MXN', 2.000000, 14, 17, NULL, 7, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', 1, NULL, NULL, NULL),
(12, 1769442538119, '2026-01-25', '2026-01-26 02:36:06', 10, 'entrada', 'MXN', 1000.000000, NULL, 7, NULL, NULL, NULL, NULL, NULL, NULL, 'Préstamo de Prestamista Prueba: test', 1, 1, 2, NULL),
(13, 1769397745863, '2026-01-25', '2026-01-26 03:22:25', 1, 'entrada', 'MXN', 1000.000000, NULL, 14, 4, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', 1, NULL, NULL, NULL),
(14, 1769397745863, '2026-01-25', '2026-01-26 03:22:25', 1, 'entrada', 'MXN', 990.000000, NULL, 18, 4, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', 1, NULL, NULL, NULL),
(15, 1769397745863, '2026-01-25', '2026-01-26 03:22:25', 1, 'comision', 'MXN', 8.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', 1, NULL, NULL, NULL),
(16, 1769397745863, '2026-01-25', '2026-01-26 03:22:25', 1, 'comision', 'MXN', 2.000000, NULL, 17, NULL, 7, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', 1, NULL, NULL, NULL),
(17, 1769397899471, '2026-01-25', '2026-01-26 03:24:59', 15, 'salida', 'MXN', 2.000000, 14, 17, NULL, 7, NULL, NULL, NULL, NULL, 'Pago de Comisiones a Kike Rizo', 1, NULL, NULL, NULL),
(18, 1769529819359, '2026-01-27', '2026-01-27 16:03:39', 1, 'entrada', 'MXN', 1000.000000, NULL, 14, 4, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - dinero recibido del cliente', 1, NULL, NULL, NULL),
(19, 1769529819359, '2026-01-27', '2026-01-27 16:03:39', 1, 'entrada', 'MXN', 990.000000, NULL, 18, 4, NULL, NULL, NULL, NULL, 1.000000, 'Venta de Efectivo - efectivo recibido neto', 1, NULL, NULL, NULL),
(20, 1769529819359, '2026-01-27', '2026-01-27 16:03:39', 1, 'comision', 'MXN', 8.000000, NULL, 1, NULL, NULL, NULL, NULL, NULL, 1.000000, 'Comisión Amiri - Venta de Efectivo', 1, NULL, NULL, NULL),
(21, 1769529819359, '2026-01-27', '2026-01-27 16:03:39', 1, 'comision', 'MXN', 2.000000, NULL, 17, NULL, 7, NULL, NULL, NULL, 1.000000, 'Comisión Promotor - Venta de Efectivo', 1, NULL, NULL, NULL),
(22, 1769535281709, '2026-01-27', '2026-01-27 17:34:41', 12, 'salida', 'MXN', 10.000000, 7, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Gasto: Pago de nóminas - Test', 1, 1, NULL, 1),
(23, 1769537222226, '2026-01-27', '2026-01-27 18:07:02', 4, 'traspaso', 'MXN', 6.000000, 14, 7, NULL, NULL, NULL, NULL, NULL, NULL, 'Cambio de flujo - transferencia entre cuentas', 1, NULL, NULL, NULL),
(24, 1769537876180, '2026-01-27', '2026-01-27 18:17:56', 7, 'traspaso', 'MXN', 300.000000, 14, 7, NULL, NULL, NULL, NULL, 'sin_tarjeta', NULL, 'Retiro de Efectivo - Sin Tarjeta', 1, NULL, NULL, NULL),
(25, 1769553576524, '2026-01-27', '2026-01-27 22:39:36', 12, 'salida', 'MXN', 6.000000, 7, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Gasto: Pago de nóminas - Test', 1, 1, NULL, 2),
(26, 1769553576524, '2026-01-27', '2026-01-27 22:39:36', 12, 'salida', 'MXN', 6.000000, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Gasto (desde Comisiones AMIRI): Pago de nóminas - Test', 1, 1, NULL, 2);

-- --------------------------------------------------------

--
-- Table structure for table `gastos`
--

CREATE TABLE `gastos` (
  `id` int NOT NULL,
  `fecha` date NOT NULL,
  `cuenta_id` int DEFAULT NULL,
  `flujo_id` bigint DEFAULT NULL,
  `tipo_gasto_id` int DEFAULT NULL,
  `monto` decimal(12,2) NOT NULL,
  `concepto` text COLLATE utf8mb4_general_ci,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gastos`
--

INSERT INTO `gastos` (`id`, `fecha`, `cuenta_id`, `flujo_id`, `tipo_gasto_id`, `monto`, `concepto`, `creado_en`) VALUES
(1, '2026-01-27', 7, NULL, 1, 10.00, 'Test', '2026-01-27 17:34:41'),
(2, '2026-01-27', 7, NULL, 1, 6.00, 'Test', '2026-01-27 22:39:36');

-- --------------------------------------------------------

--
-- Table structure for table `inventario_divisas`
--

CREATE TABLE `inventario_divisas` (
  `id` bigint NOT NULL,
  `moneda` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `cantidad_original` decimal(20,8) NOT NULL,
  `cantidad_restante` decimal(20,8) NOT NULL,
  `paridad` decimal(18,8) NOT NULL,
  `cuenta_id` int NOT NULL,
  `flujo_id` bigint NOT NULL,
  `fecha` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `movimientos_cuenta`
--

CREATE TABLE `movimientos_cuenta` (
  `id` bigint NOT NULL,
  `cuenta_id` int NOT NULL,
  `flujo_id` bigint NOT NULL,
  `tipo` enum('entrada','salida') COLLATE utf8mb4_general_ci NOT NULL,
  `monto` decimal(14,2) NOT NULL,
  `saldo_anterior` decimal(14,2) NOT NULL,
  `saldo_nuevo` decimal(14,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `movimientos_cuenta`
--

INSERT INTO `movimientos_cuenta` (`id`, `cuenta_id`, `flujo_id`, `tipo`, `monto`, `saldo_anterior`, `saldo_nuevo`, `created_at`) VALUES
(1, 7, 22, 'salida', 10.00, 100510.00, 100500.00, '2026-01-27 17:34:41'),
(2, 7, 25, 'salida', 6.00, 100806.00, 100800.00, '2026-01-27 22:39:36'),
(3, 1, 26, 'salida', 6.00, 824.00, 818.00, '2026-01-27 22:39:36');

-- --------------------------------------------------------

--
-- Table structure for table `pagos_comisiones_promotores`
--

CREATE TABLE `pagos_comisiones_promotores` (
  `id` int NOT NULL,
  `promotor_id` int NOT NULL,
  `cuenta_pago_id` int NOT NULL COMMENT 'Cuenta desde donde se pagó (caja/banco empresa)',
  `cuenta_promotor_id` int NOT NULL COMMENT 'Cuenta del promotor donde se registra',
  `monto_pagado` decimal(12,2) NOT NULL,
  `fecha_pago` date NOT NULL,
  `flujo_id` bigint DEFAULT NULL COMMENT 'Referencia al flujo_movimientos',
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `activo` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pagos_comisiones_promotores`
--

INSERT INTO `pagos_comisiones_promotores` (`id`, `promotor_id`, `cuenta_pago_id`, `cuenta_promotor_id`, `monto_pagado`, `fecha_pago`, `flujo_id`, `descripcion`, `creado_en`, `activo`) VALUES
(1, 7, 14, 17, 100.00, '2026-01-25', 5, 'Pago de comisiones pendientes', '2026-01-25 20:17:16', 1),
(2, 7, 14, 17, 100.00, '2026-01-25', 6, 'Pago de comisiones pendientes', '2026-01-25 20:17:39', 1),
(3, 7, 14, 17, 2.00, '2026-01-25', 11, 'Pago de comisiones pendientes', '2026-01-26 02:10:49', 1),
(4, 7, 14, 17, 2.00, '2026-01-25', 17, 'Pago de comisiones pendientes', '2026-01-26 03:24:59', 1);

-- --------------------------------------------------------

--
-- Table structure for table `prestamistas`
--

CREATE TABLE `prestamistas` (
  `id` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `direccion` text COLLATE utf8mb4_general_ci,
  `activo` tinyint(1) DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prestamistas`
--

INSERT INTO `prestamistas` (`id`, `nombre`, `telefono`, `email`, `direccion`, `activo`, `creado_en`) VALUES
(1, 'Diego', '01928374', 'diego@gmail.com', 'Av Patria', 1, '2026-01-22 22:25:16'),
(2, 'Prestamista Prueba', '0987654321', 'prestamista@prueba.com', 'Av Patria', 1, '2026-01-26 01:58:13');

-- --------------------------------------------------------

--
-- Table structure for table `prestamos_prestamistas`
--

CREATE TABLE `prestamos_prestamistas` (
  `id` int NOT NULL,
  `prestamista_id` int NOT NULL,
  `cuenta_id` int DEFAULT NULL,
  `monto` decimal(12,2) NOT NULL,
  `fecha` date NOT NULL,
  `descripcion` text COLLATE utf8mb4_general_ci,
  `estado` enum('pendiente','pagado','parcial') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pendiente',
  `monto_pagado` decimal(12,2) NOT NULL DEFAULT '0.00',
  `fecha_pago` date DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `activo` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prestamos_prestamistas`
--

INSERT INTO `prestamos_prestamistas` (`id`, `prestamista_id`, `cuenta_id`, `monto`, `fecha`, `descripcion`, `estado`, `monto_pagado`, `fecha_pago`, `creado_en`, `actualizado_en`, `activo`) VALUES
(1, 1, 7, 200000.00, '2026-01-22', 'Test', 'pendiente', 0.00, NULL, '2026-01-22 22:26:13', '2026-01-22 22:26:13', 1),
(10, 2, 7, 1000.00, '2026-01-25', 'test', 'pagado', 1000.00, '2026-01-25', '2026-01-26 02:36:06', '2026-01-26 02:36:58', 1);

-- --------------------------------------------------------

--
-- Table structure for table `promotores`
--

CREATE TABLE `promotores` (
  `id` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cuenta_id` int DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promotores`
--

INSERT INTO `promotores` (`id`, `nombre`, `telefono`, `email`, `cuenta_id`, `activo`, `creado_en`) VALUES
(7, 'Kike Rizo', '3382828282', 'asf12@gmail.com', 17, 1, '2026-01-22 19:04:18');

-- --------------------------------------------------------

--
-- Table structure for table `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cuenta_id` int DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `servicios`
--

CREATE TABLE `servicios` (
  `id` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_general_ci,
  `tipo_movimiento` enum('entrada','salida') COLLATE utf8mb4_general_ci NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `servicios`
--

INSERT INTO `servicios` (`id`, `nombre`, `descripcion`, `tipo_movimiento`, `activo`, `creado_en`, `actualizado_en`) VALUES
(1, 'Venta de Efectivo', 'Venta de efectivo a clientes', 'entrada', 1, '2025-11-12 21:02:41', '2025-11-12 21:02:41'),
(2, 'Venta de Dolares', 'Venta de dólares a clientes', 'entrada', 1, '2025-11-12 21:02:41', '2025-11-12 21:02:41'),
(3, 'Venta de USDT', 'Venta de USDT a clientes', 'entrada', 1, '2025-11-12 21:02:41', '2025-11-12 21:02:41'),
(4, 'Cambio de Flujo', 'Movimientos entre cuentas sin cambiar capital', 'salida', 1, '2025-11-12 21:02:41', '2025-11-12 21:02:41'),
(5, 'Compra de Dolares', 'Compra de dólares a clientes', 'salida', 1, '2025-11-12 21:02:41', '2025-11-12 21:02:41'),
(6, 'Compra de USDT', 'Compra de USDT a clientes', 'salida', 1, '2025-11-12 21:02:41', '2026-01-16 04:44:26'),
(7, 'Retiro de Efectivo', 'Retiro de efectivo por el cliente', 'salida', 1, '2026-01-27 15:46:26', '2026-01-27 15:46:26'),
(10, 'Préstamo de Prestamista', 'Préstamo recibido de prestamista', 'entrada', 1, '2026-01-26 15:55:10', '2026-01-26 15:55:10'),
(11, 'Pago a Proveedor', 'Pago realizado a proveedor', 'salida', 1, '2026-01-26 15:55:10', '2026-01-26 15:55:10'),
(12, 'Gasto', 'Gasto operativo', 'salida', 1, '2026-01-26 15:55:10', '2026-01-26 15:55:10'),
(13, 'Pago a Cliente', 'Pago realizado a cliente', 'salida', 1, '2026-01-26 15:55:10', '2026-01-26 15:55:10'),
(14, 'Pago de Préstamo', 'Pago de préstamo a prestamista', 'salida', 1, '2026-01-26 15:55:10', '2026-01-26 15:55:10'),
(15, 'Pago de Comisión a Promotor', 'Pago de comisión a promotor', 'salida', 1, '2026-01-26 16:05:14', '2026-01-26 16:05:14');

-- --------------------------------------------------------

--
-- Table structure for table `tipos_gasto`
--

CREATE TABLE `tipos_gasto` (
  `id` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tipos_gasto`
--

INSERT INTO `tipos_gasto` (`id`, `nombre`, `creado_en`) VALUES
(1, 'Pago de nóminas', '2026-01-26 01:58:53'),
(2, 'Pago Nóminas', '2026-01-27 17:26:31');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `rol` enum('admin','prestamista','usuario') COLLATE utf8mb4_general_ci DEFAULT 'usuario',
  `activo` tinyint(1) DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `reset_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reset_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol`, `activo`, `creado_en`, `reset_token`, `reset_expires_at`) VALUES
(1, 'Admin Amiriii', 'admin@amiri.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, '2025-10-29 21:18:03', NULL, NULL),
(8, 'Usuario Pruebaaaaa', 'usuario@prueba.com', '$2y$10$/955VGz4/pzQCB07JbwJQuF2ZE36NUwSa4Gs0UCqZuj8eR3dB/iXW', 'admin', 0, '2026-01-26 01:55:49', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ventas_efectivo_clientes`
--

CREATE TABLE `ventas_efectivo_clientes` (
  `id` int NOT NULL,
  `cliente_id` int NOT NULL,
  `cuenta_cliente_id` int DEFAULT NULL COMMENT 'Caja del cliente',
  `flujo_venta_id` bigint DEFAULT NULL COMMENT 'Referencia al grupo de movimientos de la venta',
  `flujo_pago_id` bigint DEFAULT NULL COMMENT 'Referencia al grupo de movimientos del pago',
  `monto_total` decimal(12,2) NOT NULL COMMENT 'Monto total de la venta',
  `comision_amiri` decimal(12,2) NOT NULL DEFAULT '0.00',
  `comision_promotor` decimal(12,2) NOT NULL DEFAULT '0.00',
  `monto_neto_cliente` decimal(12,2) NOT NULL COMMENT 'Lo que recibe el cliente después de comisiones',
  `fecha` date NOT NULL,
  `descripcion` text COLLATE utf8mb4_general_ci,
  `estado` enum('pendiente','pagado','parcial') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pendiente',
  `monto_pagado` decimal(12,2) NOT NULL DEFAULT '0.00',
  `fecha_pago` date DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `activo` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registro de ventas de efectivo pendientes de pago por clientes';

--
-- Dumping data for table `ventas_efectivo_clientes`
--

INSERT INTO `ventas_efectivo_clientes` (`id`, `cliente_id`, `cuenta_cliente_id`, `flujo_venta_id`, `flujo_pago_id`, `monto_total`, `comision_amiri`, `comision_promotor`, `monto_neto_cliente`, `fecha`, `descripcion`, `estado`, `monto_pagado`, `fecha_pago`, `creado_en`, `actualizado_en`, `activo`) VALUES
(1, 4, 18, 1769117265393, NULL, 100000.00, 800.00, 200.00, 99000.00, '2026-01-22', 'Venta de efectivo - Pendiente de pago', 'pagado', 99000.00, '2026-01-22', '2026-01-22 21:27:45', '2026-01-22 22:27:27', 1),
(2, 4, 18, 1769393371875, NULL, 1000.00, 8.00, 2.00, 990.00, '2026-01-25', 'Venta de efectivo - Pendiente de pago', 'pagado', 990.00, '2026-01-25', '2026-01-26 02:09:31', '2026-01-26 02:10:24', 1),
(3, 4, 18, 1769397745863, NULL, 1000.00, 8.00, 2.00, 990.00, '2026-01-25', 'Venta de efectivo - Pendiente de pago', 'pagado', 990.00, '2026-01-25', '2026-01-26 03:22:25', '2026-01-26 03:24:02', 1),
(4, 4, 18, 1769529819359, NULL, 1000.00, 8.00, 2.00, 990.00, '2026-01-27', 'Venta de efectivo - Pendiente de pago', 'pendiente', 0.00, NULL, '2026-01-27 16:03:39', '2026-01-27 16:03:39', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `auditoria`
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
-- Indexes for table `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cliente_promotor` (`promotor_id`),
  ADD KEY `fk_cliente_cuenta` (`cuenta_id`),
  ADD KEY `idx_promotor` (`promotor_id`),
  ADD KEY `idx_cuenta_cliente` (`cuenta_id`);

--
-- Indexes for table `cuentas`
--
ALTER TABLE `cuentas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `fk_tipo_cuenta` (`tipo_cuenta`),
  ADD KEY `idx_activo_tipo` (`activo`,`tipo_cuenta`),
  ADD KEY `idx_tipo_moneda` (`tipo_moneda`),
  ADD KEY `idx_tipo_activo` (`tipo_cuenta`,`activo`),
  ADD KEY `idx_moneda` (`tipo_moneda`),
  ADD KEY `idx_tipo_activo_moneda` (`tipo_cuenta`,`activo`,`tipo_moneda`);

--
-- Indexes for table `cuentas_tipo`
--
ALTER TABLE `cuentas_tipo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `descripcion` (`descripcion`);

--
-- Indexes for table `flujo_movimientos`
--
ALTER TABLE `flujo_movimientos`
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
  ADD KEY `idx_cuenta_destino` (`cuenta_destino_id`),
  ADD KEY `idx_tipo_descripcion` (`tipo`,`descripcion`(20)),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_prestamista` (`prestamista_id`),
  ADD KEY `idx_gasto` (`gasto_id`),
  ADD KEY `idx_activo` (`activo`),
  ADD KEY `idx_fecha_activo` (`fecha`,`activo`),
  ADD KEY `idx_tipo_activo` (`tipo`,`activo`);

--
-- Indexes for table `gastos`
--
ALTER TABLE `gastos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_gastos_flujo` (`flujo_id`);

--
-- Indexes for table `inventario_divisas`
--
ALTER TABLE `inventario_divisas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `flujo_id` (`flujo_id`);

--
-- Indexes for table `movimientos_cuenta`
--
ALTER TABLE `movimientos_cuenta`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pagos_comisiones_promotores`
--
ALTER TABLE `pagos_comisiones_promotores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_promotor` (`promotor_id`),
  ADD KEY `idx_cuenta_pago` (`cuenta_pago_id`),
  ADD KEY `idx_cuenta_promotor` (`cuenta_promotor_id`),
  ADD KEY `idx_flujo` (`flujo_id`),
  ADD KEY `idx_fecha` (`fecha_pago`),
  ADD KEY `idx_promotor_fecha` (`promotor_id`,`fecha_pago`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indexes for table `prestamistas`
--
ALTER TABLE `prestamistas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `prestamos_prestamistas`
--
ALTER TABLE `prestamos_prestamistas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_prestamo_prestamista` (`prestamista_id`),
  ADD KEY `idx_prestamista` (`prestamista_id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_fecha` (`fecha`),
  ADD KEY `idx_estado_activo` (`estado`,`activo`),
  ADD KEY `idx_prestamista_estado` (`prestamista_id`,`estado`);

--
-- Indexes for table `promotores`
--
ALTER TABLE `promotores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `fk_promotor_cuenta` (`cuenta_id`);

--
-- Indexes for table `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_proveedor_cuenta` (`cuenta_id`);

--
-- Indexes for table `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indexes for table `tipos_gasto`
--
ALTER TABLE `tipos_gasto`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `ventas_efectivo_clientes`
--
ALTER TABLE `ventas_efectivo_clientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cliente` (`cliente_id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_fecha` (`fecha`),
  ADD KEY `idx_flujo_venta` (`flujo_venta_id`),
  ADD KEY `idx_flujo_pago` (`flujo_pago_id`),
  ADD KEY `idx_estado_activo` (`estado`,`activo`),
  ADD KEY `idx_cliente_estado` (`cliente_id`,`estado`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `cuentas`
--
ALTER TABLE `cuentas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `flujo_movimientos`
--
ALTER TABLE `flujo_movimientos`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `gastos`
--
ALTER TABLE `gastos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `inventario_divisas`
--
ALTER TABLE `inventario_divisas`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `movimientos_cuenta`
--
ALTER TABLE `movimientos_cuenta`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pagos_comisiones_promotores`
--
ALTER TABLE `pagos_comisiones_promotores`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `prestamistas`
--
ALTER TABLE `prestamistas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `prestamos_prestamistas`
--
ALTER TABLE `prestamos_prestamistas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `promotores`
--
ALTER TABLE `promotores`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tipos_gasto`
--
ALTER TABLE `tipos_gasto`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `ventas_efectivo_clientes`
--
ALTER TABLE `ventas_efectivo_clientes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `fk_cliente_cuenta` FOREIGN KEY (`cuenta_id`) REFERENCES `cuentas` (`id`),
  ADD CONSTRAINT `fk_cliente_promotor` FOREIGN KEY (`promotor_id`) REFERENCES `promotores` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `cuentas`
--
ALTER TABLE `cuentas`
  ADD CONSTRAINT `fk_tipo_cuenta` FOREIGN KEY (`tipo_cuenta`) REFERENCES `cuentas_tipo` (`id`);

--
-- Constraints for table `flujo_movimientos`
--
ALTER TABLE `flujo_movimientos`
  ADD CONSTRAINT `flujo_movimientos_ibfk_1` FOREIGN KEY (`servicio_id`) REFERENCES `servicios` (`id`);

--
-- Constraints for table `pagos_comisiones_promotores`
--
ALTER TABLE `pagos_comisiones_promotores`
  ADD CONSTRAINT `fk_pago_cuenta_pago` FOREIGN KEY (`cuenta_pago_id`) REFERENCES `cuentas` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_pago_cuenta_promotor` FOREIGN KEY (`cuenta_promotor_id`) REFERENCES `cuentas` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_pago_flujo` FOREIGN KEY (`flujo_id`) REFERENCES `flujo_movimientos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pago_promotor` FOREIGN KEY (`promotor_id`) REFERENCES `promotores` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
