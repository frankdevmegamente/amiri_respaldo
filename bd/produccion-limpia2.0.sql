-- =====================================================
-- AMIRI APP - Base de Datos Producción v2.0
-- =====================================================
-- Cambios principales respecto a v1.0:
-- 
-- TABLAS RENOMBRADAS:
-- - flujo_movimientos → movimientos
-- - inventario_divisas → inventario_monedas
-- - ventas_efectivo_clientes → deudas_clientes
-- - prestamos_prestamistas → deudas_empresa
-- - pagos_comisiones_promotores → pagos_promotores
-- - tipos_gasto → categorias_gasto
-- - cuentas_tipo → tipos_cuenta
-- - movimientos_cuenta → historial_saldos
--
-- NUEVAS FUNCIONALIDADES:
-- - Sistema de anulación/corrección de movimientos
-- - Campos de soft-delete estandarizados
-- - Registro de historial de saldos
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- Crear base de datos si no existe
-- --------------------------------------------------------

CREATE DATABASE IF NOT EXISTS `guiaspar_amiri_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `guiaspar_amiri_db`;

-- --------------------------------------------------------
-- TABLA: auditoria
-- Registro de todas las acciones del sistema
-- --------------------------------------------------------

DROP TABLE IF EXISTS `auditoria`;
CREATE TABLE `auditoria` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL COMMENT 'Quién hizo la acción',
  `accion` varchar(50) NOT NULL COMMENT 'INSERT, UPDATE, DELETE, LOGIN, LOGOUT, ANULACION, etc.',
  `modulo` varchar(50) NOT NULL COMMENT 'clientes, gastos, usuarios, movimientos, etc.',
  `tabla` varchar(50) DEFAULT NULL COMMENT 'Tabla afectada',
  `registro_id` int DEFAULT NULL COMMENT 'ID del registro afectado',
  `datos_anteriores` json DEFAULT NULL COMMENT 'Valores antes del cambio',
  `datos_nuevos` json DEFAULT NULL COMMENT 'Valores después del cambio',
  `descripcion` text COMMENT 'Descripción legible de la acción',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP desde donde se hizo',
  `user_agent` varchar(255) DEFAULT NULL COMMENT 'Navegador/dispositivo',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_usuario` (`usuario_id`),
  KEY `idx_accion` (`accion`),
  KEY `idx_modulo` (`modulo`),
  KEY `idx_tabla_registro` (`tabla`,`registro_id`),
  KEY `idx_fecha` (`created_at`),
  KEY `idx_modulo_accion` (`modulo`,`accion`),
  KEY `idx_usuario_fecha` (`usuario_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLA: usuarios
-- Usuarios del sistema
-- --------------------------------------------------------

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','prestamista','usuario') DEFAULT 'usuario',
  `activo` tinyint(1) DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires_at` datetime DEFAULT NULL,
  `two_fa_enabled` tinyint(1) DEFAULT '0' COMMENT 'Indica si 2FA está habilitado',
  `two_fa_secret` varchar(255) DEFAULT NULL COMMENT 'Secret key para TOTP (encriptado)',
  `two_fa_backup_codes` json DEFAULT NULL COMMENT 'Array de códigos de respaldo',
  `two_fa_created_at` timestamp NULL DEFAULT NULL COMMENT 'Cuándo se configuró 2FA',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Usuario administrador por defecto (password: password)
INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol`, `activo`) VALUES
(1, 'Administrador', 'admin@amiri.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);

-- --------------------------------------------------------
-- TABLA: tipos_cuenta (antes: cuentas_tipo)
-- Catálogo de tipos de cuenta
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tipos_cuenta`;
CREATE TABLE `tipos_cuenta` (
  `id` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `descripcion` (`descripcion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tipos_cuenta` (`id`, `descripcion`) VALUES
(1, 'Bancaria'),
(2, 'Caja'),
(3, 'Capital'),
(4, 'Cliente'),
(5, 'Promotor'),
(6, 'Proveedor');

-- --------------------------------------------------------
-- TABLA: cuentas
-- Cuentas bancarias, cajas y wallets
-- --------------------------------------------------------

DROP TABLE IF EXISTS `cuentas`;
CREATE TABLE `cuentas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `numero_cuenta` varchar(50) DEFAULT NULL,
  `banco` varchar(100) DEFAULT NULL,
  `tipo_cuenta` int NOT NULL DEFAULT '1',
  `tipo_moneda` varchar(10) NOT NULL DEFAULT 'MXN',
  `saldo_actual` decimal(12,2) NOT NULL DEFAULT '0.00',
  `es_caja_empresa` tinyint(1) DEFAULT '0',
  `documento_path` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `eliminado_en` timestamp NULL DEFAULT NULL,
  `eliminado_por` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`),
  KEY `fk_tipo_cuenta` (`tipo_cuenta`),
  KEY `idx_activo_tipo` (`activo`,`tipo_cuenta`),
  KEY `idx_tipo_moneda` (`tipo_moneda`),
  KEY `idx_tipo_activo_moneda` (`tipo_cuenta`,`activo`,`tipo_moneda`),
  CONSTRAINT `fk_tipo_cuenta` FOREIGN KEY (`tipo_cuenta`) REFERENCES `tipos_cuenta` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Cuentas esenciales del sistema
INSERT INTO `cuentas` (`id`, `nombre`, `tipo_cuenta`, `tipo_moneda`, `saldo_actual`, `es_caja_empresa`) VALUES
(1, 'Comisiones AMIRI', 3, 'MXN', 0.00, 1),
(2, 'Utilidades AMIRI', 3, 'MXN', 0.00, 1),
(3, 'Caja de Efectivo', 2, 'MXN', 0.00, 1);


-- --------------------------------------------------------
-- TABLA: promotores
-- Promotores que traen clientes
-- --------------------------------------------------------

DROP TABLE IF EXISTS `promotores`;
CREATE TABLE `promotores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `cuenta_id` int DEFAULT NULL COMMENT 'Cuenta donde acumulan comisiones',
  `es_interno` tinyint(1) DEFAULT '0' COMMENT 'Indica si es un promotor interno de Amiri',
  `activo` tinyint(1) DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `eliminado_en` timestamp NULL DEFAULT NULL,
  `eliminado_por` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`),
  KEY `fk_promotor_cuenta` (`cuenta_id`),
  KEY `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Promotor interno por defecto
INSERT INTO `promotores` (`id`, `nombre`, `telefono`, `email`, `cuenta_id`, `es_interno`) VALUES
(1, 'AMIRI DIRECTO', 'N/A', 'interno@amiri.com', 1, 1);

-- --------------------------------------------------------
-- TABLA: clientes
-- Clientes del negocio
-- --------------------------------------------------------

DROP TABLE IF EXISTS `clientes`;
CREATE TABLE `clientes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `promotor_id` int DEFAULT NULL COMMENT 'NULL si no opera MXN',
  `cuenta_id` int DEFAULT NULL COMMENT 'Cuenta virtual del cliente',
  `retencion_total` decimal(5,2) DEFAULT NULL COMMENT 'Porcentaje total de retención',
  `porcentaje_amiri` decimal(5,2) DEFAULT NULL COMMENT 'Comisión para Amiri',
  `porcentaje_promotor` decimal(5,2) DEFAULT NULL COMMENT 'Comisión para el promotor',
  `opera_mxn` tinyint(1) DEFAULT '1' COMMENT 'Opera venta de efectivo MXN',
  `opera_usd` tinyint(1) DEFAULT '0' COMMENT 'Opera con dólares',
  `opera_usdt` tinyint(1) DEFAULT '0' COMMENT 'Opera con USDT',
  `contacto` varchar(100) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `eliminado_en` timestamp NULL DEFAULT NULL,
  `eliminado_por` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_cliente_promotor` (`promotor_id`),
  KEY `fk_cliente_cuenta` (`cuenta_id`),
  KEY `idx_activo` (`activo`),
  CONSTRAINT `fk_cliente_promotor` FOREIGN KEY (`promotor_id`) REFERENCES `promotores` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cliente_cuenta` FOREIGN KEY (`cuenta_id`) REFERENCES `cuentas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLA: proveedores
-- Proveedores de divisas
-- --------------------------------------------------------

DROP TABLE IF EXISTS `proveedores`;
CREATE TABLE `proveedores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `tipo_moneda` enum('USD','USDT') NOT NULL DEFAULT 'USD' COMMENT 'Tipo de moneda del proveedor',
  `cuenta_id` int DEFAULT NULL COMMENT 'Cuenta virtual del proveedor',
  `activo` tinyint(1) DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `eliminado_en` timestamp NULL DEFAULT NULL,
  `eliminado_por` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_proveedor_cuenta` (`cuenta_id`),
  KEY `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLA: prestamistas
-- Prestamistas que dan capital
-- --------------------------------------------------------

DROP TABLE IF EXISTS `prestamistas`;
CREATE TABLE `prestamistas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` text,
  `activo` tinyint(1) DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `eliminado_en` timestamp NULL DEFAULT NULL,
  `eliminado_por` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLA: servicios
-- Catálogo de tipos de operación/servicio
-- --------------------------------------------------------

DROP TABLE IF EXISTS `servicios`;
CREATE TABLE `servicios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `tipo_movimiento` enum('entrada','salida') NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `servicios` (`id`, `nombre`, `descripcion`, `tipo_movimiento`) VALUES
(1, 'Venta de Efectivo', 'Venta de efectivo a clientes', 'entrada'),
(2, 'Venta de Dolares', 'Venta de dólares a clientes', 'entrada'),
(3, 'Venta de USDT', 'Venta de USDT a clientes', 'entrada'),
(4, 'Cambio de Flujo', 'Movimientos entre cuentas sin cambiar capital', 'salida'),
(5, 'Compra de Dolares', 'Compra de dólares a clientes', 'salida'),
(6, 'Compra de USDT', 'Compra de USDT a clientes', 'salida'),
(7, 'Retiro de Efectivo', 'Retiro de efectivo por el cliente', 'salida'),
(10, 'Préstamo de Prestamista', 'Préstamo recibido de prestamista', 'entrada'),
(11, 'Pago a Proveedor', 'Pago realizado a proveedor', 'salida'),
(12, 'Gasto', 'Gasto operativo', 'salida'),
(13, 'Pago a Cliente', 'Pago realizado a cliente', 'salida'),
(14, 'Pago de Préstamo', 'Pago de préstamo a prestamista', 'salida'),
(15, 'Pago de Comisión a Promotor', 'Pago de comisión a promotor', 'salida');

-- --------------------------------------------------------
-- TABLA: categorias_gasto (antes: tipos_gasto)
-- Categorías de gastos operativos
-- --------------------------------------------------------

DROP TABLE IF EXISTS `categorias_gasto`;
CREATE TABLE `categorias_gasto` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLA: movimientos (antes: flujo_movimientos)
-- Registro principal de todas las operaciones financieras
-- --------------------------------------------------------

DROP TABLE IF EXISTS `movimientos`;
CREATE TABLE `movimientos` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `grupo_id` bigint DEFAULT NULL COMMENT 'Agrupa movimientos de una misma operación',
  `fecha` date NOT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `servicio_id` int NOT NULL,
  `tipo` enum('entrada','salida','traspaso','comision','ajuste') NOT NULL,
  `moneda` enum('MXN','USD','USDT') NOT NULL,
  `monto` decimal(18,6) NOT NULL,
  `cuenta_origen_id` int DEFAULT NULL,
  `cuenta_destino_id` int DEFAULT NULL,
  `cliente_id` int DEFAULT NULL,
  `promotor_id` int DEFAULT NULL,
  `proveedor_id` int DEFAULT NULL,
  `prestamista_id` int DEFAULT NULL COMMENT 'Si es pago/préstamo de prestamista',
  `gasto_id` int DEFAULT NULL COMMENT 'Si es un gasto registrado',
  `referencia_id` bigint DEFAULT NULL,
  `referencia_tipo` varchar(50) DEFAULT NULL,
  `paridad` decimal(18,6) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `usuario_id` int DEFAULT NULL COMMENT 'Usuario que registró el movimiento',
  
  -- Campos de estado (soft-delete + correcciones)
  `activo` tinyint DEFAULT '1',
  `estado` enum('activo','anulado','corregido') NOT NULL DEFAULT 'activo' COMMENT 'Estado del movimiento',
  
  -- Campos para movimientos de reverso/corrección
  `es_reverso` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Si es un movimiento de reverso',
  `movimiento_original_id` bigint DEFAULT NULL COMMENT 'ID del movimiento que corrige/anula',
  `motivo_correccion` text COMMENT 'Motivo de la anulación/corrección',
  `corregido_por_usuario_id` int DEFAULT NULL COMMENT 'Usuario que hizo la corrección',
  `corregido_en` timestamp NULL DEFAULT NULL COMMENT 'Fecha/hora de la corrección',
  
  PRIMARY KEY (`id`),
  KEY `idx_fecha` (`fecha`),
  KEY `idx_servicio` (`servicio_id`),
  KEY `idx_moneda` (`moneda`),
  KEY `idx_origen` (`cuenta_origen_id`),
  KEY `idx_destino` (`cuenta_destino_id`),
  KEY `idx_cliente` (`cliente_id`),
  KEY `idx_proveedor` (`proveedor_id`),
  KEY `idx_grupo_id` (`grupo_id`),
  KEY `idx_promotor_tipo` (`promotor_id`,`tipo`),
  KEY `idx_tipo_descripcion` (`tipo`,`descripcion`(20)),
  KEY `idx_usuario` (`usuario_id`),
  KEY `idx_prestamista` (`prestamista_id`),
  KEY `idx_gasto` (`gasto_id`),
  KEY `idx_activo` (`activo`),
  KEY `idx_estado` (`estado`),
  KEY `idx_fecha_activo` (`fecha`,`activo`),
  KEY `idx_tipo_activo` (`tipo`,`activo`),
  KEY `idx_movimiento_original` (`movimiento_original_id`),
  CONSTRAINT `fk_movimiento_servicio` FOREIGN KEY (`servicio_id`) REFERENCES `servicios` (`id`),
  CONSTRAINT `fk_movimiento_original` FOREIGN KEY (`movimiento_original_id`) REFERENCES `movimientos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLA: historial_saldos (antes: movimientos_cuenta)
-- Historial de cambios en saldos de cuentas
-- --------------------------------------------------------

DROP TABLE IF EXISTS `historial_saldos`;
CREATE TABLE `historial_saldos` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `cuenta_id` int NOT NULL,
  `movimiento_id` bigint NOT NULL COMMENT 'Referencia al movimiento que causó el cambio',
  `tipo` enum('entrada','salida') NOT NULL,
  `monto` decimal(14,2) NOT NULL,
  `saldo_anterior` decimal(14,2) NOT NULL,
  `saldo_nuevo` decimal(14,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cuenta` (`cuenta_id`),
  KEY `idx_movimiento` (`movimiento_id`),
  KEY `idx_fecha` (`created_at`),
  KEY `idx_cuenta_fecha` (`cuenta_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLA: inventario_monedas (antes: inventario_divisas)
-- Inventario FIFO de divisas compradas
-- --------------------------------------------------------

DROP TABLE IF EXISTS `inventario_monedas`;
CREATE TABLE `inventario_monedas` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `moneda` varchar(10) NOT NULL,
  `cantidad_original` decimal(20,8) NOT NULL,
  `cantidad_restante` decimal(20,8) NOT NULL,
  `paridad` decimal(18,8) NOT NULL COMMENT 'Precio de compra por unidad',
  `cuenta_id` int NOT NULL,
  `movimiento_id` bigint NOT NULL COMMENT 'Referencia al movimiento de compra',
  `fecha` datetime NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_movimiento` (`movimiento_id`),
  KEY `idx_moneda_activo` (`moneda`, `activo`),
  KEY `idx_cuenta` (`cuenta_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLA: consumos_inventario
-- Tracking de consumos FIFO para calcular ganancias en ventas
-- --------------------------------------------------------

DROP TABLE IF EXISTS `consumos_inventario`;
CREATE TABLE `consumos_inventario` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `movimiento_id` bigint NOT NULL COMMENT 'ID del movimiento de venta',
  `inventario_id` bigint NOT NULL COMMENT 'ID del lote de inventario consumido',
  `cantidad_consumida` decimal(16,8) NOT NULL,
  `paridad_compra` decimal(16,8) NOT NULL COMMENT 'Precio al que se compró',
  `paridad_venta` decimal(16,8) NOT NULL COMMENT 'Precio al que se vendió',
  `ganancia` decimal(16,8) NOT NULL COMMENT 'Ganancia calculada: (venta-compra)*cantidad',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_movimiento` (`movimiento_id`),
  KEY `idx_inventario` (`inventario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLA: gastos
-- Registro de gastos operativos
-- --------------------------------------------------------

DROP TABLE IF EXISTS `gastos`;
CREATE TABLE `gastos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `cuenta_id` int DEFAULT NULL,
  `movimiento_id` bigint DEFAULT NULL COMMENT 'Referencia al movimiento de flujo',
  `categoria_id` int DEFAULT NULL COMMENT 'Tipo/categoría de gasto',
  `monto` decimal(12,2) NOT NULL,
  `concepto` text,
  `activo` tinyint(1) DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_gastos_movimiento` (`movimiento_id`),
  KEY `fk_gastos_categoria` (`categoria_id`),
  KEY `idx_fecha` (`fecha`),
  KEY `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLA: deudas_clientes (antes: ventas_efectivo_clientes)
-- Lo que los clientes nos deben por ventas de efectivo
-- --------------------------------------------------------

DROP TABLE IF EXISTS `deudas_clientes`;
CREATE TABLE `deudas_clientes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cliente_id` int NOT NULL,
  `cuenta_cliente_id` int DEFAULT NULL COMMENT 'Caja del cliente',
  `movimiento_venta_id` bigint DEFAULT NULL COMMENT 'Referencia al grupo de movimientos de la venta',
  `movimiento_pago_id` bigint DEFAULT NULL COMMENT 'Referencia al grupo de movimientos del pago',
  `monto_total` decimal(12,2) NOT NULL COMMENT 'Monto total de la venta',
  `comision_amiri` decimal(12,2) NOT NULL DEFAULT '0.00',
  `comision_promotor` decimal(12,2) NOT NULL DEFAULT '0.00',
  `monto_neto_cliente` decimal(12,2) NOT NULL COMMENT 'Lo que recibe el cliente después de comisiones',
  `moneda` enum('MXN','USD','USDT') NOT NULL DEFAULT 'MXN' COMMENT 'Tipo de moneda de la deuda',
  `fecha` date NOT NULL,
  `descripcion` text,
  `estado` enum('pendiente','pagado','parcial') NOT NULL DEFAULT 'pendiente',
  `monto_pagado` decimal(12,2) NOT NULL DEFAULT '0.00',
  `fecha_pago` date DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cliente` (`cliente_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_fecha` (`fecha`),
  KEY `idx_movimiento_venta` (`movimiento_venta_id`),
  KEY `idx_movimiento_pago` (`movimiento_pago_id`),
  KEY `idx_estado_activo` (`estado`,`activo`),
  KEY `idx_cliente_estado` (`cliente_id`,`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registro de ventas de efectivo pendientes de pago por clientes';

-- --------------------------------------------------------
-- TABLA: deudas_empresa (antes: prestamos_prestamistas)
-- Lo que la empresa debe a los prestamistas
-- --------------------------------------------------------

DROP TABLE IF EXISTS `deudas_empresa`;
CREATE TABLE `deudas_empresa` (
  `id` int NOT NULL AUTO_INCREMENT,
  `prestamista_id` int NOT NULL,
  `cuenta_id` int DEFAULT NULL COMMENT 'Cuenta donde entró el préstamo',
  `monto` decimal(12,2) NOT NULL,
  `fecha` date NOT NULL,
  `descripcion` text,
  `estado` enum('pendiente','pagado','parcial') NOT NULL DEFAULT 'pendiente',
  `monto_pagado` decimal(12,2) NOT NULL DEFAULT '0.00',
  `fecha_pago` date DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_deuda_prestamista` (`prestamista_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_fecha` (`fecha`),
  KEY `idx_estado_activo` (`estado`,`activo`),
  KEY `idx_prestamista_estado` (`prestamista_id`,`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLA: pagos_promotores (antes: pagos_comisiones_promotores)
-- Pagos de comisiones a promotores
-- --------------------------------------------------------

DROP TABLE IF EXISTS `pagos_promotores`;
CREATE TABLE `pagos_promotores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `promotor_id` int NOT NULL,
  `cuenta_pago_id` int NOT NULL COMMENT 'Cuenta desde donde se pagó (caja/banco empresa)',
  `cuenta_promotor_id` int NOT NULL COMMENT 'Cuenta del promotor donde se registra',
  `monto_pagado` decimal(12,2) NOT NULL,
  `fecha_pago` date NOT NULL,
  `movimiento_id` bigint DEFAULT NULL COMMENT 'Referencia al movimiento',
  `descripcion` text,
  `activo` tinyint(1) DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_promotor` (`promotor_id`),
  KEY `idx_cuenta_pago` (`cuenta_pago_id`),
  KEY `idx_cuenta_promotor` (`cuenta_promotor_id`),
  KEY `idx_movimiento` (`movimiento_id`),
  KEY `idx_fecha` (`fecha_pago`),
  KEY `idx_promotor_fecha` (`promotor_id`,`fecha_pago`),
  KEY `idx_activo` (`activo`),
  CONSTRAINT `fk_pago_cuenta_pago` FOREIGN KEY (`cuenta_pago_id`) REFERENCES `cuentas` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_pago_cuenta_promotor` FOREIGN KEY (`cuenta_promotor_id`) REFERENCES `cuentas` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_pago_movimiento` FOREIGN KEY (`movimiento_id`) REFERENCES `movimientos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pago_promotor` FOREIGN KEY (`promotor_id`) REFERENCES `promotores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLA: trusted_devices
-- Dispositivos confiables para 2FA
-- --------------------------------------------------------

DROP TABLE IF EXISTS `trusted_devices`;
CREATE TABLE `trusted_devices` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `device_token` varchar(255) NOT NULL COMMENT 'Token único del dispositivo',
  `device_name` varchar(100) NOT NULL COMMENT 'Nombre del dispositivo (ej: Chrome Windows)',
  `ip_address` varchar(45) NOT NULL COMMENT 'IP de donde se confió',
  `user_agent` varchar(255) NOT NULL COMMENT 'User agent del navegador',
  `trusted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL COMMENT 'Expires 24 horas después',
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_token` (`device_token`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_expires_at` (`expires_at`),
  KEY `idx_usuario_activo` (`usuario_id`,`expires_at`),
  CONSTRAINT `fk_trusted_device_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Dispositivos confiables por 24 horas para saltarse 2FA';

-- --------------------------------------------------------
-- TABLA: two_fa_attempts
-- Intentos fallidos de 2FA
-- --------------------------------------------------------

DROP TABLE IF EXISTS `two_fa_attempts`;
CREATE TABLE `two_fa_attempts` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `failed_attempts` int NOT NULL DEFAULT '1',
  `first_attempt_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_attempt_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `blocked_until` timestamp NULL DEFAULT NULL COMMENT 'Bloqueado hasta esta hora si hay demasiados intentos',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_ip` (`usuario_id`,`ip_address`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_ip_address` (`ip_address`),
  CONSTRAINT `fk_2fa_attempts_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Rastreo de intentos fallidos de 2FA';

-- --------------------------------------------------------
-- VISTAS ÚTILES
-- --------------------------------------------------------

-- Vista: Saldos actuales de cuentas
DROP VIEW IF EXISTS `v_saldos_cuentas`;
CREATE VIEW `v_saldos_cuentas` AS
SELECT 
    c.id,
    c.nombre,
    tc.descripcion AS tipo,
    c.tipo_moneda AS moneda,
    c.saldo_actual,
    c.activo
FROM cuentas c
JOIN tipos_cuenta tc ON c.tipo_cuenta = tc.id
WHERE c.activo = 1;

-- Vista: Resumen de deudas de clientes pendientes
DROP VIEW IF EXISTS `v_deudas_clientes_pendientes`;
CREATE VIEW `v_deudas_clientes_pendientes` AS
SELECT 
    dc.id,
    c.nombre AS cliente,
    dc.monto_total,
    dc.monto_pagado,
    (dc.monto_total - dc.monto_pagado) AS saldo_pendiente,
    dc.estado,
    dc.fecha
FROM deudas_clientes dc
JOIN clientes c ON dc.cliente_id = c.id
WHERE dc.activo = 1 AND dc.estado != 'pagado';

-- Vista: Resumen de deudas de la empresa
DROP VIEW IF EXISTS `v_deudas_empresa_pendientes`;
CREATE VIEW `v_deudas_empresa_pendientes` AS
SELECT 
    de.id,
    p.nombre AS prestamista,
    de.monto,
    de.monto_pagado,
    (de.monto - de.monto_pagado) AS saldo_pendiente,
    de.estado,
    de.fecha
FROM deudas_empresa de
JOIN prestamistas p ON de.prestamista_id = p.id
WHERE de.activo = 1 AND de.estado != 'pagado';

-- Vista: Comisiones pendientes de pago a promotores
DROP VIEW IF EXISTS `v_comisiones_promotores`;
CREATE VIEW `v_comisiones_promotores` AS
SELECT 
    p.id AS promotor_id,
    p.nombre AS promotor,
    c.saldo_actual AS comisiones_acumuladas
FROM promotores p
LEFT JOIN cuentas c ON p.cuenta_id = c.id
WHERE p.activo = 1 AND p.es_interno = 0;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- =====================================================
-- GUÍA DE MIGRACIÓN DESDE v1.0
-- =====================================================
-- Si tienes datos en la BD anterior y quieres migrarlos:
--
-- 1. Exporta tus datos actuales con mysqldump
-- 2. Importa este script en una BD nueva
-- 3. Usa los siguientes comandos para migrar datos:
--
-- INSERT INTO movimientos SELECT 
--     id, grupo_id, fecha, creado_en, servicio_id, tipo, moneda, monto,
--     cuenta_origen_id, cuenta_destino_id, cliente_id, promotor_id,
--     proveedor_id, prestamista_id, gasto_id, referencia_id, referencia_tipo,
--     paridad, descripcion, usuario_id, activo,
--     'activo', 0, NULL, NULL, NULL, NULL
-- FROM old_db.flujo_movimientos;
--
-- INSERT INTO historial_saldos SELECT * FROM old_db.movimientos_cuenta;
-- INSERT INTO inventario_monedas SELECT *, 1 FROM old_db.inventario_divisas;
-- INSERT INTO categorias_gasto SELECT *, 1 FROM old_db.tipos_gasto;
-- INSERT INTO deudas_clientes SELECT * FROM old_db.ventas_efectivo_clientes;
-- INSERT INTO deudas_empresa SELECT * FROM old_db.prestamos_prestamistas;
-- INSERT INTO pagos_promotores SELECT * FROM old_db.pagos_comisiones_promotores;
-- INSERT INTO tipos_cuenta SELECT * FROM old_db.cuentas_tipo;
--
-- =====================================================
