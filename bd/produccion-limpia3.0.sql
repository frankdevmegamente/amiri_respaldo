-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 13, 2026 at 02:09 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

START TRANSACTION;

SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */
;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */
;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */
;
/*!40101 SET NAMES utf8mb4 */
;

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
    `accion` varchar(50) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'INSERT, UPDATE, DELETE, LOGIN, LOGOUT, ANULACION, etc.',
    `modulo` varchar(50) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'clientes, gastos, usuarios, movimientos, etc.',
    `tabla` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Tabla afectada',
    `registro_id` int DEFAULT NULL COMMENT 'ID del registro afectado',
    `datos_anteriores` json DEFAULT NULL COMMENT 'Valores antes del cambio',
    `datos_nuevos` json DEFAULT NULL COMMENT 'Valores después del cambio',
    `descripcion` text COLLATE utf8mb4_general_ci COMMENT 'Descripción legible de la acción',
    `ip_address` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'IP desde donde se hizo',
    `user_agent` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Navegador/dispositivo',
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `auditoria`
--

INSERT INTO
    `auditoria` (
        `id`,
        `usuario_id`,
        `accion`,
        `modulo`,
        `tabla`,
        `registro_id`,
        `datos_anteriores`,
        `datos_nuevos`,
        `descripcion`,
        `ip_address`,
        `user_agent`,
        `created_at`
    )
VALUES (
        1,
        1,
        'LOGIN',
        'auth',
        'usuarios',
        1,
        NULL,
        NULL,
        'Usuario \'Administrador\' inició sesión',
        '::1',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',
        '2026-03-13 01:37:26'
    );

-- --------------------------------------------------------

--
-- Table structure for table `categorias_gasto`
--

CREATE TABLE `categorias_gasto` (
    `id` int NOT NULL,
    `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
    `activo` tinyint(1) DEFAULT '1',
    `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clientes`
--

CREATE TABLE `clientes` (
    `id` int NOT NULL,
    `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
    `promotor_id` int DEFAULT NULL COMMENT 'NULL si no opera MXN',
    `cuenta_id` int DEFAULT NULL COMMENT 'Cuenta virtual del cliente',
    `retencion_total` decimal(5, 2) DEFAULT NULL COMMENT 'Porcentaje total de retención',
    `porcentaje_amiri` decimal(5, 2) DEFAULT NULL COMMENT 'Comisión para Amiri',
    `porcentaje_promotor` decimal(5, 2) DEFAULT NULL COMMENT 'Comisión para el promotor',
    `opera_mxn` tinyint(1) DEFAULT '1' COMMENT 'Opera venta de efectivo MXN',
    `opera_usd` tinyint(1) DEFAULT '0' COMMENT 'Opera con dólares',
    `opera_usdt` tinyint(1) DEFAULT '0' COMMENT 'Opera con USDT',
    `contacto` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `activo` tinyint(1) DEFAULT '1',
    `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `eliminado_en` timestamp NULL DEFAULT NULL,
    `eliminado_por` int DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `consumos_inventario`
--

CREATE TABLE `consumos_inventario` (
    `id` bigint NOT NULL,
    `movimiento_id` bigint NOT NULL COMMENT 'ID del movimiento de venta',
    `inventario_id` bigint NOT NULL COMMENT 'ID del lote de inventario consumido',
    `cantidad_consumida` decimal(16, 8) NOT NULL,
    `paridad_compra` decimal(16, 8) NOT NULL COMMENT 'Precio al que se compró',
    `paridad_venta` decimal(16, 8) NOT NULL COMMENT 'Precio al que se vendió',
    `ganancia` decimal(16, 8) NOT NULL COMMENT 'Ganancia calculada: (venta-compra)*cantidad',
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cuentas`
--

CREATE TABLE `cuentas` (
    `id` int NOT NULL,
    `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
    `numero_cuenta` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `banco` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `tipo_cuenta` int NOT NULL DEFAULT '1',
    `tipo_moneda` varchar(10) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'MXN',
    `saldo_actual` decimal(12, 2) NOT NULL DEFAULT '0.00',
    `valor_mxn` decimal(14, 2) NOT NULL DEFAULT '0.00',
    `es_caja_empresa` tinyint(1) DEFAULT '0',
    `documento_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `activo` tinyint(1) DEFAULT '1',
    `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `eliminado_en` timestamp NULL DEFAULT NULL,
    `eliminado_por` int DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `cuentas`
--

INSERT INTO
    `cuentas` (
        `id`,
        `nombre`,
        `numero_cuenta`,
        `banco`,
        `tipo_cuenta`,
        `tipo_moneda`,
        `saldo_actual`,
        `valor_mxn`,
        `es_caja_empresa`,
        `documento_path`,
        `activo`,
        `creado_en`,
        `eliminado_en`,
        `eliminado_por`
    )
VALUES (
        1,
        'Utilidades AMIRI',
        NULL,
        NULL,
        3,
        'MXN',
        0.00,
        0.00,
        1,
        NULL,
        1,
        '2026-03-13 01:37:15',
        NULL,
        NULL
    ),
    (
        2,
        'Caja de Efectivo',
        NULL,
        NULL,
        2,
        'MXN',
        0.00,
        0.00,
        1,
        NULL,
        1,
        '2026-03-13 01:37:15',
        NULL,
        NULL
    );

-- --------------------------------------------------------

--
-- Table structure for table `deudas_clientes`
--

CREATE TABLE `deudas_clientes` (
    `id` int NOT NULL,
    `cliente_id` int NOT NULL,
    `cuenta_cliente_id` int DEFAULT NULL COMMENT 'Caja del cliente',
    `movimiento_venta_id` bigint DEFAULT NULL COMMENT 'Referencia al grupo de movimientos de la venta',
    `movimiento_pago_id` bigint DEFAULT NULL COMMENT 'Referencia al grupo de movimientos del pago',
    `monto_total` decimal(12, 2) NOT NULL COMMENT 'Monto total de la venta',
    `comision_amiri` decimal(12, 2) NOT NULL DEFAULT '0.00',
    `comision_promotor` decimal(12, 2) NOT NULL DEFAULT '0.00',
    `monto_neto_cliente` decimal(12, 2) NOT NULL COMMENT 'Lo que recibe el cliente después de comisiones',
    `moneda` enum('MXN', 'USD', 'USDT') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'MXN' COMMENT 'Tipo de moneda de la deuda',
    `fecha` date NOT NULL,
    `descripcion` text COLLATE utf8mb4_general_ci,
    `estado` enum(
        'pendiente',
        'pagado',
        'parcial'
    ) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pendiente',
    `monto_pagado` decimal(12, 2) NOT NULL DEFAULT '0.00',
    `fecha_pago` date DEFAULT NULL,
    `activo` tinyint(1) DEFAULT '1',
    `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = 'Registro de ventas de efectivo pendientes de pago por clientes';

-- --------------------------------------------------------

--
-- Table structure for table `deudas_empresa`
--

CREATE TABLE `deudas_empresa` (
    `id` int NOT NULL,
    `prestamista_id` int NOT NULL,
    `cuenta_id` int DEFAULT NULL COMMENT 'Cuenta donde entró el préstamo',
    `monto` decimal(12, 2) NOT NULL,
    `fecha` date NOT NULL,
    `descripcion` text COLLATE utf8mb4_general_ci,
    `estado` enum(
        'pendiente',
        'pagado',
        'parcial'
    ) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pendiente',
    `monto_pagado` decimal(12, 2) NOT NULL DEFAULT '0.00',
    `fecha_pago` date DEFAULT NULL,
    `activo` tinyint(1) DEFAULT '1',
    `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gastos`
--

CREATE TABLE `gastos` (
    `id` int NOT NULL,
    `fecha` date NOT NULL,
    `cuenta_id` int DEFAULT NULL,
    `movimiento_id` bigint DEFAULT NULL COMMENT 'Referencia al movimiento de flujo',
    `categoria_id` int DEFAULT NULL COMMENT 'Tipo/categoría de gasto',
    `monto` decimal(12, 2) NOT NULL,
    `concepto` text COLLATE utf8mb4_general_ci,
    `activo` tinyint(1) DEFAULT '1',
    `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `historial_saldos`
--

CREATE TABLE `historial_saldos` (
    `id` bigint NOT NULL,
    `cuenta_id` int NOT NULL,
    `movimiento_id` bigint NOT NULL COMMENT 'Referencia al movimiento que causó el cambio',
    `tipo` enum('entrada', 'salida') COLLATE utf8mb4_general_ci NOT NULL,
    `monto` decimal(14, 2) NOT NULL,
    `saldo_anterior` decimal(14, 2) NOT NULL,
    `saldo_nuevo` decimal(14, 2) NOT NULL,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventario_monedas`
--

CREATE TABLE `inventario_monedas` (
    `id` bigint NOT NULL,
    `moneda` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
    `cantidad_original` decimal(20, 8) NOT NULL,
    `cantidad_restante` decimal(20, 8) NOT NULL,
    `paridad` decimal(18, 8) NOT NULL COMMENT 'Precio de compra por unidad',
    `cuenta_id` int NOT NULL,
    `movimiento_id` bigint NOT NULL COMMENT 'Referencia al movimiento de compra',
    `fecha` datetime NOT NULL,
    `activo` tinyint(1) DEFAULT '1'
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `movimientos`
--

CREATE TABLE `movimientos` (
    `id` bigint NOT NULL,
    `grupo_id` bigint DEFAULT NULL COMMENT 'Agrupa movimientos de una misma operación',
    `fecha` date NOT NULL,
    `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `servicio_id` int NOT NULL,
    `tipo` enum(
        'entrada',
        'salida',
        'traspaso',
        'comision',
        'ajuste'
    ) COLLATE utf8mb4_general_ci NOT NULL,
    `moneda` enum('MXN', 'USD', 'USDT') COLLATE utf8mb4_general_ci NOT NULL,
    `monto` decimal(18, 6) NOT NULL,
    `cuenta_origen_id` int DEFAULT NULL,
    `cuenta_destino_id` int DEFAULT NULL,
    `cliente_id` int DEFAULT NULL,
    `promotor_id` int DEFAULT NULL,
    `proveedor_id` int DEFAULT NULL,
    `prestamista_id` int DEFAULT NULL COMMENT 'Si es pago/préstamo de prestamista',
    `gasto_id` int DEFAULT NULL COMMENT 'Si es un gasto registrado',
    `referencia_id` bigint DEFAULT NULL,
    `referencia_tipo` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `paridad` decimal(18, 6) DEFAULT NULL,
    `descripcion` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `usuario_id` int DEFAULT NULL COMMENT 'Usuario que registró el movimiento',
    `activo` tinyint DEFAULT '1',
    `estado` enum(
        'activo',
        'anulado',
        'corregido'
    ) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'activo' COMMENT 'Estado del movimiento',
    `es_reverso` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Si es un movimiento de reverso',
    `movimiento_original_id` bigint DEFAULT NULL COMMENT 'ID del movimiento que corrige/anula',
    `motivo_correccion` text COLLATE utf8mb4_general_ci COMMENT 'Motivo de la anulación/corrección',
    `corregido_por_usuario_id` int DEFAULT NULL COMMENT 'Usuario que hizo la corrección',
    `corregido_en` timestamp NULL DEFAULT NULL COMMENT 'Fecha/hora de la corrección'
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pagos_promotores`
--

CREATE TABLE `pagos_promotores` (
    `id` int NOT NULL,
    `promotor_id` int NOT NULL,
    `cuenta_pago_id` int NOT NULL COMMENT 'Cuenta desde donde se pagó (caja/banco empresa)',
    `cuenta_promotor_id` int NOT NULL COMMENT 'Cuenta del promotor donde se registra',
    `monto_pagado` decimal(12, 2) NOT NULL,
    `fecha_pago` date NOT NULL,
    `movimiento_id` bigint DEFAULT NULL COMMENT 'Referencia al movimiento',
    `descripcion` text COLLATE utf8mb4_general_ci,
    `activo` tinyint(1) DEFAULT '1',
    `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

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
    `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `eliminado_en` timestamp NULL DEFAULT NULL,
    `eliminado_por` int DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promotores`
--

CREATE TABLE `promotores` (
    `id` int NOT NULL,
    `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
    `telefono` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `cuenta_id` int DEFAULT NULL COMMENT 'Cuenta donde acumulan comisiones',
    `es_interno` tinyint(1) DEFAULT '0' COMMENT 'Indica si es un promotor interno de Amiri',
    `activo` tinyint(1) DEFAULT '1',
    `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `eliminado_en` timestamp NULL DEFAULT NULL,
    `eliminado_por` int DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `promotores`
--

INSERT INTO
    `promotores` (
        `id`,
        `nombre`,
        `telefono`,
        `email`,
        `cuenta_id`,
        `es_interno`,
        `activo`,
        `creado_en`,
        `eliminado_en`,
        `eliminado_por`
    )
VALUES (
        1,
        'AMIRI DIRECTO',
        'N/A',
        'interno@amiri.com',
        1,
        1,
        1,
        '2026-03-13 01:37:15',
        NULL,
        NULL
    );

-- --------------------------------------------------------

--
-- Table structure for table `proveedores`
--


CREATE TABLE `proveedores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,

  `tipo_moneda` enum('USD','USDT') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'USD' COMMENT 'Tipo de moneda principal del proveedor',

-- Nuevos campos
`acepta_usd` TINYINT(1) NOT NULL DEFAULT 0,
  `acepta_usdt` TINYINT(1) NOT NULL DEFAULT 0,
  `cuenta_id` int DEFAULT NULL COMMENT 'Cuenta original (legacy)',
  `cuenta_usd_id` INT DEFAULT NULL,
  `cuenta_usdt_id` INT DEFAULT NULL,

  `activo` tinyint(1) DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `eliminado_en` timestamp NULL DEFAULT NULL,
  `eliminado_por` int DEFAULT NULL,

  PRIMARY KEY (`id`)
) ENGINE=InnoDB 
DEFAULT CHARSET=utf8mb4 
COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `servicios`
--

CREATE TABLE `servicios` (
    `id` int NOT NULL,
    `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
    `descripcion` text COLLATE utf8mb4_general_ci,
    `tipo_movimiento` enum('entrada', 'salida') COLLATE utf8mb4_general_ci NOT NULL,
    `activo` tinyint(1) DEFAULT '1',
    `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `servicios`
--

INSERT INTO
    `servicios` (
        `id`,
        `nombre`,
        `descripcion`,
        `tipo_movimiento`,
        `activo`,
        `creado_en`,
        `actualizado_en`
    )
VALUES (
        1,
        'Venta de Efectivo',
        'Venta de efectivo a clientes',
        'entrada',
        1,
        '2026-03-13 01:37:15',
        '2026-03-13 01:37:15'
    ),
    (
        2,
        'Venta de Dolares',
        'Venta de dólares a clientes',
        'entrada',
        1,
        '2026-03-13 01:37:15',
        '2026-03-13 01:37:15'
    ),
    (
        3,
        'Venta de USDT',
        'Venta de USDT a clientes',
        'entrada',
        1,
        '2026-03-13 01:37:15',
        '2026-03-13 01:37:15'
    ),
    (
        4,
        'Cambio de Flujo',
        'Movimientos entre cuentas sin cambiar capital',
        'salida',
        1,
        '2026-03-13 01:37:15',
        '2026-03-13 01:37:15'
    ),
    (
        5,
        'Compra de Dolares',
        'Compra de dólares a clientes',
        'salida',
        1,
        '2026-03-13 01:37:15',
        '2026-03-13 01:37:15'
    ),
    (
        6,
        'Compra de USDT',
        'Compra de USDT a clientes',
        'salida',
        1,
        '2026-03-13 01:37:15',
        '2026-03-13 01:37:15'
    ),
    (
        7,
        'Retiro de Efectivo',
        'Retiro de efectivo por el cliente',
        'salida',
        1,
        '2026-03-13 01:37:15',
        '2026-03-13 01:37:15'
    ),
    (
        10,
        'Préstamo de Prestamista',
        'Préstamo recibido de prestamista',
        'entrada',
        1,
        '2026-03-13 01:37:15',
        '2026-03-13 01:37:15'
    ),
    (
        11,
        'Pago a Proveedor',
        'Pago realizado a proveedor',
        'salida',
        1,
        '2026-03-13 01:37:15',
        '2026-03-13 01:37:15'
    ),
    (
        12,
        'Gasto',
        'Gasto operativo',
        'salida',
        1,
        '2026-03-13 01:37:15',
        '2026-03-13 01:37:15'
    ),
    (
        13,
        'Pago a Cliente',
        'Pago realizado a cliente',
        'salida',
        1,
        '2026-03-13 01:37:15',
        '2026-03-13 01:37:15'
    ),
    (
        14,
        'Pago de Préstamo',
        'Pago de préstamo a prestamista',
        'salida',
        1,
        '2026-03-13 01:37:15',
        '2026-03-13 01:37:15'
    ),
    (
        15,
        'Pago de Comisión a Promotor',
        'Pago de comisión a promotor',
        'salida',
        1,
        '2026-03-13 01:37:15',
        '2026-03-13 01:37:15'
    );

-- --------------------------------------------------------

--
-- Table structure for table `tipos_cuenta`
--

CREATE TABLE `tipos_cuenta` (
    `id` int NOT NULL,
    `descripcion` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `tipos_cuenta`
--

INSERT INTO
    `tipos_cuenta` (`id`, `descripcion`)
VALUES (1, 'Bancaria'),
    (2, 'Caja'),
    (3, 'Capital'),
    (4, 'Cliente'),
    (5, 'Promotor'),
    (6, 'Proveedor');

-- --------------------------------------------------------

--
-- Table structure for table `trusted_devices`
--

CREATE TABLE `trusted_devices` (
    `id` bigint NOT NULL,
    `usuario_id` int NOT NULL,
    `device_token` varchar(255) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Token único del dispositivo',
    `device_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Nombre del dispositivo (ej: Chrome Windows)',
    `ip_address` varchar(45) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'IP de donde se confió',
    `user_agent` varchar(255) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'User agent del navegador',
    `trusted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `expires_at` timestamp NOT NULL COMMENT 'Expires 24 horas después',
    `last_used_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = 'Dispositivos confiables por 24 horas para saltarse 2FA';

-- --------------------------------------------------------

--
-- Table structure for table `two_fa_attempts`
--

CREATE TABLE `two_fa_attempts` (
    `id` bigint NOT NULL,
    `usuario_id` int NOT NULL,
    `ip_address` varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
    `failed_attempts` int NOT NULL DEFAULT '1',
    `first_attempt_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `last_attempt_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `blocked_until` timestamp NULL DEFAULT NULL COMMENT 'Bloqueado hasta esta hora si hay demasiados intentos',
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = 'Rastreo de intentos fallidos de 2FA';

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
    `id` int NOT NULL,
    `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
    `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
    `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
    `rol` enum(
        'admin',
        'prestamista',
        'usuario'
    ) COLLATE utf8mb4_general_ci DEFAULT 'usuario',
    `activo` tinyint(1) DEFAULT '1',
    `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `reset_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `reset_expires_at` datetime DEFAULT NULL,
    `two_fa_enabled` tinyint(1) DEFAULT '0' COMMENT 'Indica si 2FA está habilitado',
    `two_fa_secret` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Secret key para TOTP (encriptado)',
    `two_fa_backup_codes` json DEFAULT NULL COMMENT 'Array de códigos de respaldo',
    `two_fa_created_at` timestamp NULL DEFAULT NULL COMMENT 'Cuándo se configuró 2FA'
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO
    `usuarios` (
        `id`,
        `nombre`,
        `email`,
        `password`,
        `rol`,
        `activo`,
        `creado_en`,
        `reset_token`,
        `reset_expires_at`,
        `two_fa_enabled`,
        `two_fa_secret`,
        `two_fa_backup_codes`,
        `two_fa_created_at`
    )
VALUES (
        1,
        'Administrador',
        'admin@amiri.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'admin',
        1,
        '2026-03-13 01:37:14',
        NULL,
        NULL,
        0,
        NULL,
        NULL,
        NULL
    );

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_comisiones_promotores`
-- (See below for the actual view)
--
CREATE TABLE `v_comisiones_promotores` (
    `comisiones_acumuladas` decimal(12, 2),
    `promotor` varchar(100),
    `promotor_id` int
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_deudas_clientes_pendientes`
-- (See below for the actual view)
--
CREATE TABLE `v_deudas_clientes_pendientes` (
    `cliente` varchar(100),
    `estado` enum(
        'pendiente',
        'pagado',
        'parcial'
    ),
    `fecha` date,
    `id` int,
    `monto_pagado` decimal(12, 2),
    `monto_total` decimal(12, 2),
    `saldo_pendiente` decimal(13, 2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_deudas_empresa_pendientes`
-- (See below for the actual view)
--
CREATE TABLE `v_deudas_empresa_pendientes` (
    `estado` enum(
        'pendiente',
        'pagado',
        'parcial'
    ),
    `fecha` date,
    `id` int,
    `monto` decimal(12, 2),
    `monto_pagado` decimal(12, 2),
    `prestamista` varchar(100),
    `saldo_pendiente` decimal(13, 2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_saldos_cuentas`
-- (See below for the actual view)
--
CREATE TABLE `v_saldos_cuentas` (
    `activo` tinyint(1),
    `id` int,
    `moneda` varchar(10),
    `nombre` varchar(100),
    `saldo_actual` decimal(12, 2),
    `tipo` varchar(50)
);

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
ADD KEY `idx_tabla_registro` (`tabla`, `registro_id`),
ADD KEY `idx_fecha` (`created_at`),
ADD KEY `idx_modulo_accion` (`modulo`, `accion`),
ADD KEY `idx_usuario_fecha` (`usuario_id`, `created_at`);

--
-- Indexes for table `categorias_gasto`
--
ALTER TABLE `categorias_gasto`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indexes for table `clientes`
--
ALTER TABLE `clientes`
ADD PRIMARY KEY (`id`),
ADD KEY `fk_cliente_promotor` (`promotor_id`),
ADD KEY `fk_cliente_cuenta` (`cuenta_id`),
ADD KEY `idx_activo` (`activo`);

--
-- Indexes for table `consumos_inventario`
--
ALTER TABLE `consumos_inventario`
ADD PRIMARY KEY (`id`),
ADD KEY `idx_movimiento` (`movimiento_id`),
ADD KEY `idx_inventario` (`inventario_id`);

--
-- Indexes for table `cuentas`
--
ALTER TABLE `cuentas`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `nombre` (`nombre`),
ADD KEY `fk_tipo_cuenta` (`tipo_cuenta`),
ADD KEY `idx_activo_tipo` (`activo`, `tipo_cuenta`),
ADD KEY `idx_tipo_moneda` (`tipo_moneda`),
ADD KEY `idx_tipo_activo_moneda` (
    `tipo_cuenta`,
    `activo`,
    `tipo_moneda`
),
ADD KEY `idx_tipo_valor` (`tipo_cuenta`, `valor_mxn`);

--
-- Indexes for table `deudas_clientes`
--
ALTER TABLE `deudas_clientes`
ADD PRIMARY KEY (`id`),
ADD KEY `idx_cliente` (`cliente_id`),
ADD KEY `idx_estado` (`estado`),
ADD KEY `idx_fecha` (`fecha`),
ADD KEY `idx_movimiento_venta` (`movimiento_venta_id`),
ADD KEY `idx_movimiento_pago` (`movimiento_pago_id`),
ADD KEY `idx_estado_activo` (`estado`, `activo`),
ADD KEY `idx_cliente_estado` (`cliente_id`, `estado`);

--
-- Indexes for table `deudas_empresa`
--
ALTER TABLE `deudas_empresa`
ADD PRIMARY KEY (`id`),
ADD KEY `fk_deuda_prestamista` (`prestamista_id`),
ADD KEY `idx_estado` (`estado`),
ADD KEY `idx_fecha` (`fecha`),
ADD KEY `idx_estado_activo` (`estado`, `activo`),
ADD KEY `idx_prestamista_estado` (`prestamista_id`, `estado`);

--
-- Indexes for table `gastos`
--
ALTER TABLE `gastos`
ADD PRIMARY KEY (`id`),
ADD KEY `fk_gastos_movimiento` (`movimiento_id`),
ADD KEY `fk_gastos_categoria` (`categoria_id`),
ADD KEY `idx_fecha` (`fecha`),
ADD KEY `idx_activo` (`activo`);

--
-- Indexes for table `historial_saldos`
--
ALTER TABLE `historial_saldos`
ADD PRIMARY KEY (`id`),
ADD KEY `idx_cuenta` (`cuenta_id`),
ADD KEY `idx_movimiento` (`movimiento_id`),
ADD KEY `idx_fecha` (`created_at`),
ADD KEY `idx_cuenta_fecha` (`cuenta_id`, `created_at`);

--
-- Indexes for table `inventario_monedas`
--
ALTER TABLE `inventario_monedas`
ADD PRIMARY KEY (`id`),
ADD KEY `idx_movimiento` (`movimiento_id`),
ADD KEY `idx_moneda_activo` (`moneda`, `activo`),
ADD KEY `idx_cuenta` (`cuenta_id`);

--
-- Indexes for table `movimientos`
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
ADD KEY `idx_promotor_tipo` (`promotor_id`, `tipo`),
ADD KEY `idx_tipo_descripcion` (`tipo`, `descripcion` (20)),
ADD KEY `idx_usuario` (`usuario_id`),
ADD KEY `idx_prestamista` (`prestamista_id`),
ADD KEY `idx_gasto` (`gasto_id`),
ADD KEY `idx_activo` (`activo`),
ADD KEY `idx_estado` (`estado`),
ADD KEY `idx_fecha_activo` (`fecha`, `activo`),
ADD KEY `idx_tipo_activo` (`tipo`, `activo`),
ADD KEY `idx_movimiento_original` (`movimiento_original_id`);

--
-- Indexes for table `pagos_promotores`
--
ALTER TABLE `pagos_promotores`
ADD PRIMARY KEY (`id`),
ADD KEY `idx_promotor` (`promotor_id`),
ADD KEY `idx_cuenta_pago` (`cuenta_pago_id`),
ADD KEY `idx_cuenta_promotor` (`cuenta_promotor_id`),
ADD KEY `idx_movimiento` (`movimiento_id`),
ADD KEY `idx_fecha` (`fecha_pago`),
ADD KEY `idx_promotor_fecha` (`promotor_id`, `fecha_pago`),
ADD KEY `idx_activo` (`activo`);

--
-- Indexes for table `prestamistas`
--
ALTER TABLE `prestamistas`
ADD PRIMARY KEY (`id`),
ADD KEY `idx_activo` (`activo`);

--
-- Indexes for table `promotores`
--
ALTER TABLE `promotores`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `nombre` (`nombre`),
ADD KEY `fk_promotor_cuenta` (`cuenta_id`),
ADD KEY `idx_activo` (`activo`);

--
-- Indexes for table `proveedores`
--
ALTER TABLE `proveedores`
ADD KEY `fk_proveedor_cuenta` (`cuenta_id`),
ADD KEY `idx_activo` (`activo`);

--
-- Indexes for table `servicios`
--
ALTER TABLE `servicios`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indexes for table `tipos_cuenta`
--
ALTER TABLE `tipos_cuenta`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `descripcion` (`descripcion`);

--
-- Indexes for table `trusted_devices`
--
ALTER TABLE `trusted_devices`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `device_token` (`device_token`),
ADD KEY `idx_usuario_id` (`usuario_id`),
ADD KEY `idx_expires_at` (`expires_at`),
ADD KEY `idx_usuario_activo` (`usuario_id`, `expires_at`);

--
-- Indexes for table `two_fa_attempts`
--
ALTER TABLE `two_fa_attempts`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `unique_user_ip` (`usuario_id`, `ip_address`),
ADD KEY `idx_usuario_id` (`usuario_id`),
ADD KEY `idx_ip_address` (`ip_address`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `auditoria`
--
ALTER TABLE `auditoria`
MODIFY `id` bigint NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 2;

--
-- AUTO_INCREMENT for table `categorias_gasto`
--
ALTER TABLE `categorias_gasto`
MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clientes`
--
ALTER TABLE `clientes` MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `consumos_inventario`
--
ALTER TABLE `consumos_inventario`
MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cuentas`
--
ALTER TABLE `cuentas`
MODIFY `id` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 4;

--
-- AUTO_INCREMENT for table `deudas_clientes`
--
ALTER TABLE `deudas_clientes`
MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deudas_empresa`
--
ALTER TABLE `deudas_empresa`
MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gastos`
--
ALTER TABLE `gastos` MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `historial_saldos`
--
ALTER TABLE `historial_saldos`
MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventario_monedas`
--
ALTER TABLE `inventario_monedas`
MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `movimientos`
--
ALTER TABLE `movimientos`
MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pagos_promotores`
--
ALTER TABLE `pagos_promotores`
MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prestamistas`
--
ALTER TABLE `prestamistas` MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promotores`
--
ALTER TABLE `promotores`
MODIFY `id` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 2;

--
-- AUTO_INCREMENT for table `proveedores`
--
ALTER TABLE `proveedores` MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `servicios`
--
ALTER TABLE `servicios`
MODIFY `id` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 16;

--
-- AUTO_INCREMENT for table `tipos_cuenta`
--
ALTER TABLE `tipos_cuenta`
MODIFY `id` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 7;

--
-- AUTO_INCREMENT for table `trusted_devices`
--
ALTER TABLE `trusted_devices`
MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `two_fa_attempts`
--
ALTER TABLE `two_fa_attempts`
MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
MODIFY `id` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 2;

-- --------------------------------------------------------

--
-- Structure for view `v_comisiones_promotores`
--
DROP TABLE IF EXISTS `v_comisiones_promotores`;

CREATE VIEW `v_comisiones_promotores` AS
SELECT
    p.id AS promotor_id,
    p.nombre AS promotor,
    c.saldo_actual AS comisiones_acumuladas
FROM promotores p
    LEFT JOIN cuentas c ON p.cuenta_id = c.id
WHERE
    p.activo = 1
    AND p.es_interno = 0;

-- --------------------------------------------------------

--
-- Structure for view `v_deudas_clientes_pendientes`
--
DROP TABLE IF EXISTS `v_deudas_clientes_pendientes`;

CREATE VIEW `v_deudas_clientes_pendientes` AS
SELECT
    dc.id AS id,
    c.nombre AS cliente,
    dc.monto_total AS monto_total,
    dc.monto_pagado AS monto_pagado,
    (
        dc.monto_total - dc.monto_pagado
    ) AS saldo_pendiente,
    dc.estado AS estado,
    dc.fecha AS fecha
FROM
    deudas_clientes dc
    JOIN clientes c ON dc.cliente_id = c.id
WHERE
    dc.activo = 1
    AND dc.estado <> 'pagado';

-- --------------------------------------------------------

--
-- Structure for view `v_deudas_empresa_pendientes`
--
DROP TABLE IF EXISTS `v_deudas_empresa_pendientes`;

CREATE VIEW `v_deudas_empresa_pendientes` AS
SELECT
    de.id AS id,
    p.nombre AS prestamista,
    de.monto AS monto,
    de.monto_pagado AS monto_pagado,
    (de.monto - de.monto_pagado) AS saldo_pendiente,
    de.estado AS estado,
    de.fecha AS fecha
FROM
    deudas_empresa de
    JOIN prestamistas p ON de.prestamista_id = p.id
WHERE
    de.activo = 1
    AND de.estado <> 'pagado';

-- --------------------------------------------------------

--
-- Structure for view `v_saldos_cuentas`
--
DROP TABLE IF EXISTS `v_saldos_cuentas`;

CREATE VIEW `v_saldos_cuentas` AS
SELECT
    c.id AS id,
    c.nombre AS nombre,
    tc.descripcion AS tipo,
    c.tipo_moneda AS moneda,
    c.saldo_actual AS saldo_actual,
    c.activo AS activo
FROM cuentas c
    JOIN tipos_cuenta tc ON c.tipo_cuenta = tc.id
WHERE
    c.activo = 1;

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
ADD CONSTRAINT `fk_tipo_cuenta` FOREIGN KEY (`tipo_cuenta`) REFERENCES `tipos_cuenta` (`id`);

--
-- Constraints for table `movimientos`
--
ALTER TABLE `movimientos`
ADD CONSTRAINT `fk_movimiento_original` FOREIGN KEY (`movimiento_original_id`) REFERENCES `movimientos` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk_movimiento_servicio` FOREIGN KEY (`servicio_id`) REFERENCES `servicios` (`id`);

--
-- Constraints for table `pagos_promotores`
--
ALTER TABLE `pagos_promotores`
ADD CONSTRAINT `fk_pago_cuenta_pago` FOREIGN KEY (`cuenta_pago_id`) REFERENCES `cuentas` (`id`) ON DELETE RESTRICT,
ADD CONSTRAINT `fk_pago_cuenta_promotor` FOREIGN KEY (`cuenta_promotor_id`) REFERENCES `cuentas` (`id`) ON DELETE RESTRICT,
ADD CONSTRAINT `fk_pago_movimiento` FOREIGN KEY (`movimiento_id`) REFERENCES `movimientos` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk_pago_promotor` FOREIGN KEY (`promotor_id`) REFERENCES `promotores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `trusted_devices`
--
ALTER TABLE `trusted_devices`
ADD CONSTRAINT `fk_trusted_device_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `two_fa_attempts`
--
ALTER TABLE `two_fa_attempts`
ADD CONSTRAINT `fk_2fa_attempts_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */
;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */
;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */
;