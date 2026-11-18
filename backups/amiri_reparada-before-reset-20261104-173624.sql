-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: amiri_reparada
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `auditoria`
--

DROP TABLE IF EXISTS `auditoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auditoria` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL COMMENT 'Qui├®n hizo la acci├│n',
  `accion` varchar(50) NOT NULL COMMENT 'INSERT, UPDATE, DELETE, LOGIN, LOGOUT, ANULACION, etc.',
  `modulo` varchar(50) NOT NULL COMMENT 'clientes, gastos, usuarios, movimientos, etc.',
  `tabla` varchar(50) DEFAULT NULL COMMENT 'Tabla afectada',
  `registro_id` int(11) DEFAULT NULL COMMENT 'ID del registro afectado',
  `datos_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Valores antes del cambio' CHECK (json_valid(`datos_anteriores`)),
  `datos_nuevos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Valores despu├®s del cambio' CHECK (json_valid(`datos_nuevos`)),
  `descripcion` text DEFAULT NULL COMMENT 'Descripci├│n legible de la acci├│n',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP desde donde se hizo',
  `user_agent` varchar(255) DEFAULT NULL COMMENT 'Navegador/dispositivo',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_usuario` (`usuario_id`),
  KEY `idx_accion` (`accion`),
  KEY `idx_modulo` (`modulo`),
  KEY `idx_tabla_registro` (`tabla`,`registro_id`),
  KEY `idx_fecha` (`created_at`),
  KEY `idx_modulo_accion` (`modulo`,`accion`),
  KEY `idx_usuario_fecha` (`usuario_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auditoria`
--

LOCK TABLES `auditoria` WRITE;
/*!40000 ALTER TABLE `auditoria` DISABLE KEYS */;
/*!40000 ALTER TABLE `auditoria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_cuentas_fix989k`
--

DROP TABLE IF EXISTS `backup_cuentas_fix989k`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backup_cuentas_fix989k` (
  `id` int(11) DEFAULT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `saldo_actual` decimal(18,2) DEFAULT NULL,
  `valor_mxn` decimal(18,2) DEFAULT NULL,
  `respaldado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_cuentas_fix989k`
--

LOCK TABLES `backup_cuentas_fix989k` WRITE;
/*!40000 ALTER TABLE `backup_cuentas_fix989k` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_cuentas_fix989k` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categorias_gasto`
--

DROP TABLE IF EXISTS `categorias_gasto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categorias_gasto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias_gasto`
--

LOCK TABLES `categorias_gasto` WRITE;
/*!40000 ALTER TABLE `categorias_gasto` DISABLE KEYS */;
INSERT INTO `categorias_gasto` VALUES (2,'Gas Kia',1,'2026-03-28 05:57:10'),(3,'Renta',1,'2026-03-28 05:57:17'),(4,'Recargas',1,'2026-03-28 05:57:25'),(5,'Estacionamiento',1,'2026-03-28 05:57:33'),(6,'Comisiones Bancarias',1,'2026-03-28 05:57:46'),(7,'Comisiones Rojas',1,'2026-04-03 04:36:20'),(8,'Pago Amiri - Diego',1,'2026-04-14 06:10:35'),(9,'Pago Amiri - Emiliano',1,'2026-04-14 06:10:42'),(10,'Pago Alondra',1,'2026-04-14 06:12:12'),(11,'Cuentas Pro Amiri',1,'2026-04-14 06:17:48'),(12,'Peeter',1,'2026-04-14 06:18:38'),(13,'Papeleria',1,'2026-04-14 06:25:49'),(14,'Retiros',1,'2026-04-14 06:29:44'),(15,'Limpieza',1,'2026-04-14 06:31:25'),(16,'Perdida',1,'2026-04-14 06:37:39'),(17,'TÔö£Ôöéner Impresora',1,'2026-04-24 11:44:04'),(18,'Azul',1,'2026-04-28 23:48:15'),(19,'Amiri Operaciones Integrales',1,'2026-04-28 23:51:35'),(20,'Nomina',1,'2026-05-01 06:38:07'),(21,'Retiro Utilidades',1,'2026-05-01 06:38:19'),(22,'Cfe',1,'2026-05-08 02:43:34'),(23,'Operativo',1,'2026-05-09 05:24:21'),(24,'Chat Gpt',1,'2026-05-20 07:38:53'),(25,'Compra Efectivo',1,'2026-05-20 22:56:46'),(26,'Trx',1,'2026-07-13 23:43:16'),(27,'Inversion Estructura',1,'2026-08-20 01:20:26'),(28,'Portacion Gera',1,'2026-10-06 22:57:30');
/*!40000 ALTER TABLE `categorias_gasto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cierres_mensuales`
--

DROP TABLE IF EXISTS `cierres_mensuales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cierres_mensuales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mes` varchar(7) NOT NULL COMMENT 'YYYY-MM',
  `fecha_cierre` datetime NOT NULL,
  `comisiones_amiri` decimal(14,2) NOT NULL DEFAULT 0.00,
  `ganancia_divisas` decimal(14,2) NOT NULL DEFAULT 0.00,
  `gastos_fijos` decimal(14,2) NOT NULL DEFAULT 0.00,
  `gastos_variables` decimal(14,2) NOT NULL DEFAULT 0.00,
  `gastos_total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `utilidad_neta` decimal(14,2) NOT NULL DEFAULT 0.00,
  `capital_acumulado` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_mes` (`mes`),
  KEY `idx_mes` (`mes`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cierres_mensuales`
--

LOCK TABLES `cierres_mensuales` WRITE;
/*!40000 ALTER TABLE `cierres_mensuales` DISABLE KEYS */;
/*!40000 ALTER TABLE `cierres_mensuales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `promotor_id` int(11) DEFAULT NULL COMMENT 'NULL si no opera MXN',
  `cuenta_id` int(11) DEFAULT NULL COMMENT 'Cuenta virtual del cliente',
  `retencion_total` decimal(5,2) DEFAULT NULL COMMENT 'Porcentaje total de retenci├│n',
  `porcentaje_amiri` decimal(5,2) DEFAULT NULL COMMENT 'Comisi├│n para Amiri',
  `porcentaje_promotor` decimal(5,2) DEFAULT NULL COMMENT 'Comisi├│n para el promotor',
  `opera_mxn` tinyint(1) DEFAULT 1 COMMENT 'Opera venta de efectivo MXN',
  `opera_usd` tinyint(1) DEFAULT 0 COMMENT 'Opera con d├│lares',
  `opera_usdt` tinyint(1) DEFAULT 0 COMMENT 'Opera con USDT',
  `contacto` varchar(100) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  `eliminado_en` timestamp NULL DEFAULT NULL,
  `eliminado_por` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_cliente_promotor` (`promotor_id`),
  KEY `fk_cliente_cuenta` (`cuenta_id`),
  KEY `idx_activo` (`activo`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES (1,'Cliente 1',1,6,0.00,0.00,0.00,1,1,1,'5555550201',1,'2026-11-04 23:35:32',NULL,NULL);
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configuracion`
--

DROP TABLE IF EXISTS `configuracion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_clave` (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuracion`
--

LOCK TABLES `configuracion` WRITE;
/*!40000 ALTER TABLE `configuracion` DISABLE KEYS */;
/*!40000 ALTER TABLE `configuracion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `consumos_inventario`
--

DROP TABLE IF EXISTS `consumos_inventario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consumos_inventario` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `movimiento_id` bigint(20) NOT NULL COMMENT 'ID del movimiento de venta',
  `inventario_id` bigint(20) NOT NULL COMMENT 'ID del lote de inventario consumido',
  `cantidad_consumida` decimal(16,8) NOT NULL,
  `paridad_compra` decimal(16,8) NOT NULL COMMENT 'Precio al que se compr├│',
  `paridad_venta` decimal(16,8) NOT NULL COMMENT 'Precio al que se vendi├│',
  `ganancia` decimal(16,8) NOT NULL COMMENT 'Ganancia calculada: (venta-compra)*cantidad',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_movimiento` (`movimiento_id`),
  KEY `idx_inventario` (`inventario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consumos_inventario`
--

LOCK TABLES `consumos_inventario` WRITE;
/*!40000 ALTER TABLE `consumos_inventario` DISABLE KEYS */;
/*!40000 ALTER TABLE `consumos_inventario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cuentas`
--

DROP TABLE IF EXISTS `cuentas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cuentas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `eliminado_por` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`),
  KEY `fk_tipo_cuenta` (`tipo_cuenta`),
  KEY `idx_activo_tipo` (`activo`,`tipo_cuenta`),
  KEY `idx_tipo_moneda` (`tipo_moneda`),
  KEY `idx_tipo_activo_moneda` (`tipo_cuenta`,`activo`,`tipo_moneda`),
  KEY `idx_tipo_valor` (`tipo_cuenta`,`valor_mxn`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cuentas`
--

LOCK TABLES `cuentas` WRITE;
/*!40000 ALTER TABLE `cuentas` DISABLE KEYS */;
INSERT INTO `cuentas` VALUES (1,'Cuenta Bancaria 1','012345678901234567','BBVA',1,'MXN',10000.00,10000.00,0,NULL,1,'2026-11-04 23:35:32',NULL,NULL),(2,'Cuenta Bancaria USD 1','USD-0123456789','BBVA',1,'USD',1000.00,17000.00,0,NULL,1,'2026-11-04 23:35:32',NULL,NULL),(3,'Caja 1',NULL,NULL,2,'MXN',10000.00,10000.00,0,NULL,1,'2026-11-04 23:35:32',NULL,NULL),(4,'Capital Amiri',NULL,NULL,7,'MXN',10000.00,10000.00,0,NULL,1,'2026-11-04 23:35:32',NULL,NULL),(5,'Cuenta Promotor 1',NULL,NULL,5,'MXN',0.00,0.00,0,NULL,1,'2026-11-04 23:35:32',NULL,NULL),(6,'Caja - Cliente 1',NULL,NULL,4,'MXN',0.00,0.00,1,NULL,1,'2026-11-04 23:35:32',NULL,NULL),(7,'Cuenta Proveedor 1 MXN',NULL,NULL,6,'MXN',0.00,0.00,0,NULL,1,'2026-11-04 23:35:32',NULL,NULL),(8,'Cuenta Proveedor 1 USD',NULL,NULL,6,'USD',0.00,0.00,0,NULL,1,'2026-11-04 23:35:32',NULL,NULL),(9,'Cuenta Proveedor 1 USDT',NULL,NULL,6,'USDT',0.00,0.00,0,NULL,1,'2026-11-04 23:35:32',NULL,NULL);
/*!40000 ALTER TABLE `cuentas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deudas_clientes`
--

DROP TABLE IF EXISTS `deudas_clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deudas_clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `cuenta_cliente_id` int(11) DEFAULT NULL COMMENT 'Caja del cliente',
  `movimiento_venta_id` bigint(20) DEFAULT NULL COMMENT 'Referencia al grupo de movimientos de la venta',
  `movimiento_pago_id` bigint(20) DEFAULT NULL COMMENT 'Referencia al grupo de movimientos del pago',
  `monto_total` decimal(12,2) NOT NULL COMMENT 'Monto total de la venta',
  `comision_amiri` decimal(12,2) NOT NULL DEFAULT 0.00,
  `comision_promotor` decimal(12,2) NOT NULL DEFAULT 0.00,
  `monto_neto_cliente` decimal(12,2) NOT NULL COMMENT 'Lo que recibe el cliente despu├®s de comisiones',
  `moneda` enum('MXN','USD','USDT') NOT NULL DEFAULT 'MXN' COMMENT 'Tipo de moneda de la deuda',
  `fecha` date NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` enum('pendiente','pagado','parcial') NOT NULL DEFAULT 'pendiente',
  `monto_pagado` decimal(12,2) NOT NULL DEFAULT 0.00,
  `fecha_pago` date DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cliente` (`cliente_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_fecha` (`fecha`),
  KEY `idx_movimiento_venta` (`movimiento_venta_id`),
  KEY `idx_movimiento_pago` (`movimiento_pago_id`),
  KEY `idx_estado_activo` (`estado`,`activo`),
  KEY `idx_cliente_estado` (`cliente_id`,`estado`),
  KEY `idx_fecha_activo` (`fecha`,`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registro de ventas de efectivo pendientes de pago por clientes';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deudas_clientes`
--

LOCK TABLES `deudas_clientes` WRITE;
/*!40000 ALTER TABLE `deudas_clientes` DISABLE KEYS */;
/*!40000 ALTER TABLE `deudas_clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deudas_empresa`
--

DROP TABLE IF EXISTS `deudas_empresa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deudas_empresa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prestamista_id` int(11) NOT NULL,
  `cuenta_id` int(11) DEFAULT NULL COMMENT 'Cuenta donde entr├│ el pr├®stamo',
  `monto` decimal(12,2) NOT NULL,
  `fecha` date NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` enum('pendiente','pagado','parcial') NOT NULL DEFAULT 'pendiente',
  `monto_pagado` decimal(12,2) NOT NULL DEFAULT 0.00,
  `fecha_pago` date DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_deuda_prestamista` (`prestamista_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_fecha` (`fecha`),
  KEY `idx_estado_activo` (`estado`,`activo`),
  KEY `idx_prestamista_estado` (`prestamista_id`,`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deudas_empresa`
--

LOCK TABLES `deudas_empresa` WRITE;
/*!40000 ALTER TABLE `deudas_empresa` DISABLE KEYS */;
/*!40000 ALTER TABLE `deudas_empresa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deudores`
--

DROP TABLE IF EXISTS `deudores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deudores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `documento_path` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deudores`
--

LOCK TABLES `deudores` WRITE;
/*!40000 ALTER TABLE `deudores` DISABLE KEYS */;
/*!40000 ALTER TABLE `deudores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `directorio_depositos`
--

DROP TABLE IF EXISTS `directorio_depositos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `directorio_depositos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `banco` varchar(100) DEFAULT NULL,
  `cuenta` varchar(50) DEFAULT NULL,
  `clabe` varchar(50) DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'activo',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `directorio_depositos`
--

LOCK TABLES `directorio_depositos` WRITE;
/*!40000 ALTER TABLE `directorio_depositos` DISABLE KEYS */;
/*!40000 ALTER TABLE `directorio_depositos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gastos`
--

DROP TABLE IF EXISTS `gastos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gastos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `cuenta_id` int(11) DEFAULT NULL,
  `movimiento_id` bigint(20) DEFAULT NULL COMMENT 'Referencia al movimiento de flujo',
  `categoria_id` int(11) DEFAULT NULL COMMENT 'Tipo/categor├¡a de gasto',
  `clasificacion` enum('fijo','variable') NOT NULL DEFAULT 'variable',
  `monto` decimal(12,2) NOT NULL,
  `concepto` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_gastos_movimiento` (`movimiento_id`),
  KEY `fk_gastos_categoria` (`categoria_id`),
  KEY `idx_fecha` (`fecha`),
  KEY `idx_activo` (`activo`),
  KEY `idx_fecha_activo` (`fecha`,`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gastos`
--

LOCK TABLES `gastos` WRITE;
/*!40000 ALTER TABLE `gastos` DISABLE KEYS */;
/*!40000 ALTER TABLE `gastos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `historial_saldos`
--

DROP TABLE IF EXISTS `historial_saldos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `historial_saldos` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cuenta_id` int(11) NOT NULL,
  `movimiento_id` bigint(20) NOT NULL COMMENT 'Referencia al movimiento que caus├│ el cambio',
  `tipo` enum('entrada','salida') NOT NULL,
  `monto` decimal(14,2) NOT NULL,
  `saldo_anterior` decimal(14,2) NOT NULL,
  `saldo_nuevo` decimal(14,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cuenta` (`cuenta_id`),
  KEY `idx_movimiento` (`movimiento_id`),
  KEY `idx_fecha` (`created_at`),
  KEY `idx_cuenta_fecha` (`cuenta_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `historial_saldos`
--

LOCK TABLES `historial_saldos` WRITE;
/*!40000 ALTER TABLE `historial_saldos` DISABLE KEYS */;
/*!40000 ALTER TABLE `historial_saldos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventario_monedas`
--

DROP TABLE IF EXISTS `inventario_monedas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventario_monedas` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `moneda` varchar(10) NOT NULL,
  `cantidad_original` decimal(20,8) NOT NULL,
  `cantidad_restante` decimal(20,8) NOT NULL,
  `paridad` decimal(18,8) NOT NULL COMMENT 'Precio de compra por unidad',
  `cuenta_id` int(11) NOT NULL,
  `movimiento_id` bigint(20) NOT NULL COMMENT 'Referencia al movimiento de compra',
  `fecha` datetime NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_movimiento` (`movimiento_id`),
  KEY `idx_moneda_activo` (`moneda`,`activo`),
  KEY `idx_cuenta` (`cuenta_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventario_monedas`
--

LOCK TABLES `inventario_monedas` WRITE;
/*!40000 ALTER TABLE `inventario_monedas` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventario_monedas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimientos`
--

DROP TABLE IF EXISTS `movimientos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `movimientos` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `grupo_id` bigint(20) DEFAULT NULL COMMENT 'Agrupa movimientos de una misma operaci├│n',
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
  `prestamista_id` int(11) DEFAULT NULL COMMENT 'Si es pago/pr├®stamo de prestamista',
  `gasto_id` int(11) DEFAULT NULL COMMENT 'Si es un gasto registrado',
  `referencia_id` bigint(20) DEFAULT NULL,
  `referencia_tipo` varchar(50) DEFAULT NULL,
  `beneficiario` varchar(150) DEFAULT NULL COMMENT 'Nombre a quien se le presta dinero',
  `paridad` decimal(18,6) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL COMMENT 'Usuario que registr├│ el movimiento',
  `activo` tinyint(4) DEFAULT 1,
  `estado` enum('activo','anulado','corregido') NOT NULL DEFAULT 'activo' COMMENT 'Estado del movimiento',
  `es_reverso` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Si es un movimiento de reverso',
  `movimiento_original_id` bigint(20) DEFAULT NULL COMMENT 'ID del movimiento que corrige/anula',
  `motivo_correccion` text DEFAULT NULL COMMENT 'Motivo de la anulaci├│n/correcci├│n',
  `corregido_por_usuario_id` int(11) DEFAULT NULL COMMENT 'Usuario que hizo la correcci├│n',
  `corregido_en` timestamp NULL DEFAULT NULL COMMENT 'Fecha/hora de la correcci├│n',
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
  KEY `idx_perf_divisas` (`servicio_id`,`tipo`,`cuenta_destino_id`,`fecha`,`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimientos`
--

LOCK TABLES `movimientos` WRITE;
/*!40000 ALTER TABLE `movimientos` DISABLE KEYS */;
/*!40000 ALTER TABLE `movimientos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pagos_promotores`
--

DROP TABLE IF EXISTS `pagos_promotores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pagos_promotores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `promotor_id` int(11) NOT NULL,
  `cuenta_pago_id` int(11) NOT NULL COMMENT 'Cuenta desde donde se pag├│ (caja/banco empresa)',
  `cuenta_promotor_id` int(11) NOT NULL COMMENT 'Cuenta del promotor donde se registra',
  `monto_pagado` decimal(12,2) NOT NULL,
  `fecha_pago` date NOT NULL,
  `movimiento_id` bigint(20) DEFAULT NULL COMMENT 'Referencia al movimiento',
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_promotor` (`promotor_id`),
  KEY `idx_cuenta_pago` (`cuenta_pago_id`),
  KEY `idx_cuenta_promotor` (`cuenta_promotor_id`),
  KEY `idx_movimiento` (`movimiento_id`),
  KEY `idx_fecha` (`fecha_pago`),
  KEY `idx_promotor_fecha` (`promotor_id`,`fecha_pago`),
  KEY `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagos_promotores`
--

LOCK TABLES `pagos_promotores` WRITE;
/*!40000 ALTER TABLE `pagos_promotores` DISABLE KEYS */;
/*!40000 ALTER TABLE `pagos_promotores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prestamistas`
--

DROP TABLE IF EXISTS `prestamistas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prestamistas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  `eliminado_en` timestamp NULL DEFAULT NULL,
  `eliminado_por` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_activo` (`activo`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prestamistas`
--

LOCK TABLES `prestamistas` WRITE;
/*!40000 ALTER TABLE `prestamistas` DISABLE KEYS */;
INSERT INTO `prestamistas` VALUES (1,'Prestamista 1','5555550401','prestamista1@prueba.test','Direcci├│n de prueba',1,'2026-11-04 23:35:32',NULL,NULL);
/*!40000 ALTER TABLE `prestamistas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prestamos_otorgados`
--

DROP TABLE IF EXISTS `prestamos_otorgados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prestamos_otorgados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `persona_nombre` varchar(150) NOT NULL COMMENT 'A qui├®n se le prest├│ el dinero',
  `deudor_id` int(11) DEFAULT NULL,
  `cuenta_id` int(11) DEFAULT NULL COMMENT 'Caja desde donde sali├│ el pr├®stamo',
  `monto_total` decimal(12,2) NOT NULL COMMENT 'Capital original entregado',
  `monto_interes` decimal(12,2) DEFAULT 0.00,
  `monto_total_con_interes` decimal(12,2) DEFAULT 0.00,
  `tipo_interes` enum('simple','compuesto') DEFAULT 'compuesto',
  `tasa_porcentaje` decimal(5,2) DEFAULT 0.00,
  `tasa_periodo` enum('diario','semanal','quincenal','mensual','anual') DEFAULT 'mensual',
  `plazo_valor` int(11) DEFAULT NULL,
  `plazo_unidad` enum('dia','semana','quincena','mes') DEFAULT 'mes',
  `frecuencia_pago` enum('unico','diario','semanal','quincenal','mensual') DEFAULT 'unico',
  `monto_pagado` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Dinero que ya te han devuelto',
  `estado` enum('pendiente','parcial','pagado') NOT NULL DEFAULT 'pendiente',
  `fecha_prestamo` date NOT NULL COMMENT 'Fecha en que se otorg├│ el pr├®stamo',
  `fecha_vencimiento` date DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_deudor` (`deudor_id`),
  KEY `idx_vencimiento` (`fecha_vencimiento`),
  CONSTRAINT `fk_prestamos_deudor` FOREIGN KEY (`deudor_id`) REFERENCES `deudores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prestamos_otorgados`
--

LOCK TABLES `prestamos_otorgados` WRITE;
/*!40000 ALTER TABLE `prestamos_otorgados` DISABLE KEYS */;
/*!40000 ALTER TABLE `prestamos_otorgados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `promotores`
--

DROP TABLE IF EXISTS `promotores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `promotores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `cuenta_id` int(11) DEFAULT NULL COMMENT 'Cuenta donde acumulan comisiones',
  `es_interno` tinyint(1) DEFAULT 0 COMMENT 'Indica si es un promotor interno de Amiri',
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  `eliminado_en` timestamp NULL DEFAULT NULL,
  `eliminado_por` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`),
  KEY `fk_promotor_cuenta` (`cuenta_id`),
  KEY `idx_activo` (`activo`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promotores`
--

LOCK TABLES `promotores` WRITE;
/*!40000 ALTER TABLE `promotores` DISABLE KEYS */;
INSERT INTO `promotores` VALUES (1,'Promotor 1','5555550101','promotor1@prueba.test',5,0,1,'2026-11-04 23:35:32',NULL,NULL);
/*!40000 ALTER TABLE `promotores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proveedores`
--

DROP TABLE IF EXISTS `proveedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `tipo_moneda` enum('USD','USDT') NOT NULL DEFAULT 'USD' COMMENT 'Tipo de moneda principal del proveedor',
  `acepta_usd` tinyint(1) NOT NULL DEFAULT 0,
  `acepta_usdt` tinyint(1) NOT NULL DEFAULT 0,
  `cuenta_id` int(11) DEFAULT NULL COMMENT 'Cuenta original (legacy)',
  `cuenta_usd_id` int(11) DEFAULT NULL,
  `cuenta_usdt_id` int(11) DEFAULT NULL,
  `cuenta_mxn_id` int(11) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  `eliminado_en` timestamp NULL DEFAULT NULL,
  `eliminado_por` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_proveedor_cuenta` (`cuenta_id`),
  KEY `idx_activo` (`activo`),
  KEY `fk_proveedor_cuenta_mxn` (`cuenta_mxn_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedores`
--

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
INSERT INTO `proveedores` VALUES (1,'Proveedor 1','proveedor1@prueba.test','5555550301','USD',1,1,7,8,9,7,1,'2026-11-04 23:35:32',NULL,NULL);
/*!40000 ALTER TABLE `proveedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `retiros_capital`
--

DROP TABLE IF EXISTS `retiros_capital`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `retiros_capital` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `monto` decimal(14,2) NOT NULL,
  `concepto` varchar(255) NOT NULL,
  `fecha` date NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `usuario_nombre` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `cuenta_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_fecha` (`fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `retiros_capital`
--

LOCK TABLES `retiros_capital` WRITE;
/*!40000 ALTER TABLE `retiros_capital` DISABLE KEYS */;
/*!40000 ALTER TABLE `retiros_capital` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `servicios`
--

DROP TABLE IF EXISTS `servicios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `servicios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo_movimiento` enum('entrada','salida') NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `servicios`
--

LOCK TABLES `servicios` WRITE;
/*!40000 ALTER TABLE `servicios` DISABLE KEYS */;
INSERT INTO `servicios` VALUES (1,'Venta de Efectivo','Venta de efectivo a clientes','entrada',1,'2026-03-13 07:37:15','2026-03-13 07:37:15'),(2,'Venta de Dolares','Venta de dÔö£Ôöélares a clientes','entrada',1,'2026-03-13 07:37:15','2026-03-13 07:37:15'),(3,'Venta de USDT','Venta de USDT a clientes','entrada',1,'2026-03-13 07:37:15','2026-03-13 07:37:15'),(4,'Cambio de Flujo','Movimientos entre cuentas sin cambiar capital','salida',1,'2026-03-13 07:37:15','2026-03-13 07:37:15'),(5,'Compra de Dolares','Compra de dÔö£Ôöélares a clientes','salida',1,'2026-03-13 07:37:15','2026-03-13 07:37:15'),(6,'Compra de USDT','Compra de USDT a clientes','salida',1,'2026-03-13 07:37:15','2026-03-13 07:37:15'),(7,'Retiro de Efectivo','Retiro de efectivo por el cliente','salida',1,'2026-03-13 07:37:15','2026-03-13 07:37:15'),(10,'PrÔö£┬«stamo de Prestamista','PrÔö£┬«stamo recibido de prestamista','entrada',1,'2026-03-13 07:37:15','2026-03-13 07:37:15'),(11,'Pago a Proveedor','Pago realizado a proveedor','salida',1,'2026-03-13 07:37:15','2026-03-13 07:37:15'),(12,'Gasto','Gasto operativo','salida',1,'2026-03-13 07:37:15','2026-03-13 07:37:15'),(13,'Pago a Cliente','Pago realizado a cliente','salida',1,'2026-03-13 07:37:15','2026-03-13 07:37:15'),(14,'Pago de PrÔö£┬«stamo','Pago de prÔö£┬«stamo a prestamista','salida',1,'2026-03-13 07:37:15','2026-03-13 07:37:15'),(15,'Pago de ComisiÔö£Ôöén a Promotor','Pago de comisiÔö£Ôöén a promotor','salida',1,'2026-03-13 07:37:15','2026-03-13 07:37:15'),(16,'PrÔö£┬«stamo Otorgado','Salida de dinero por prÔö£┬«stamo a terceros','salida',1,'2026-05-05 04:00:57','2026-05-05 04:00:57'),(17,'Cobro de PrÔö£┬«stamo','Entrada de dinero por cobro de prÔö£┬«stamo','entrada',1,'2026-05-05 04:00:57','2026-05-05 04:00:57'),(18,'Retiro de Capital',NULL,'entrada',1,'2026-07-04 06:13:57','2026-07-04 06:13:57'),(19,'Cierre de Mes','Cierre mensual de capital y traspaso de utilidades','salida',1,'2026-07-04 06:13:57','2026-07-04 06:13:57'),(20,'Compra de Efectivo',NULL,'entrada',1,'2026-07-04 06:13:57','2026-07-04 06:13:57');
/*!40000 ALTER TABLE `servicios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipos_cuenta`
--

DROP TABLE IF EXISTS `tipos_cuenta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tipos_cuenta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `descripcion` (`descripcion`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipos_cuenta`
--

LOCK TABLES `tipos_cuenta` WRITE;
/*!40000 ALTER TABLE `tipos_cuenta` DISABLE KEYS */;
INSERT INTO `tipos_cuenta` VALUES (1,'Bancaria'),(2,'Caja'),(3,'Capital'),(7,'Capital Amiri'),(4,'Cliente'),(5,'Promotor'),(6,'Proveedor');
/*!40000 ALTER TABLE `tipos_cuenta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trusted_devices`
--

DROP TABLE IF EXISTS `trusted_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trusted_devices` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `device_token` varchar(255) NOT NULL COMMENT 'Token ├║nico del dispositivo',
  `device_name` varchar(100) NOT NULL COMMENT 'Nombre del dispositivo (ej: Chrome Windows)',
  `ip_address` varchar(45) NOT NULL COMMENT 'IP de donde se confi├│',
  `user_agent` varchar(255) NOT NULL COMMENT 'User agent del navegador',
  `trusted_at` timestamp NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Expires 24 horas despu├®s',
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_token` (`device_token`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_expires_at` (`expires_at`),
  KEY `idx_usuario_activo` (`usuario_id`,`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Dispositivos confiables por 24 horas para saltarse 2FA';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trusted_devices`
--

LOCK TABLES `trusted_devices` WRITE;
/*!40000 ALTER TABLE `trusted_devices` DISABLE KEYS */;
/*!40000 ALTER TABLE `trusted_devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `two_fa_attempts`
--

DROP TABLE IF EXISTS `two_fa_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `two_fa_attempts` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `failed_attempts` int(11) NOT NULL DEFAULT 1,
  `first_attempt_at` timestamp NULL DEFAULT current_timestamp(),
  `last_attempt_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `blocked_until` timestamp NULL DEFAULT NULL COMMENT 'Bloqueado hasta esta hora si hay demasiados intentos',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_ip` (`usuario_id`,`ip_address`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_ip_address` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Rastreo de intentos fallidos de 2FA';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `two_fa_attempts`
--

LOCK TABLES `two_fa_attempts` WRITE;
/*!40000 ALTER TABLE `two_fa_attempts` DISABLE KEYS */;
/*!40000 ALTER TABLE `two_fa_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','prestamista','usuario','promotor') NOT NULL DEFAULT 'usuario',
  `promotor_id` int(11) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires_at` datetime DEFAULT NULL,
  `two_fa_enabled` tinyint(1) DEFAULT 0 COMMENT 'Indica si 2FA est├í habilitado',
  `two_fa_secret` varchar(255) DEFAULT NULL COMMENT 'Secret key para TOTP (encriptado)',
  `two_fa_backup_codes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array de c├│digos de respaldo' CHECK (json_valid(`two_fa_backup_codes`)),
  `two_fa_created_at` timestamp NULL DEFAULT NULL COMMENT 'Cu├índo se configur├│ 2FA',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_usuarios_promotor_id` (`promotor_id`),
  CONSTRAINT `fk_usuarios_promotor` FOREIGN KEY (`promotor_id`) REFERENCES `promotores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Administrador','admin@amiri.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin',NULL,1,'2026-03-13 01:37:14',NULL,NULL,0,NULL,NULL,NULL),(2,'Emiliano','grupoamiri2@gmail.com','$2y$12$l3qZ.Qso3mGJfXAaOQfmzOQHcdtMAESZvSRvhdhybE55dJ1eUafQG','admin',NULL,1,'2026-03-18 15:35:14',NULL,NULL,0,NULL,NULL,NULL),(3,'Diego','diego@albardi.mx','$2y$12$GHRNNpnBPM8pQ6tyBEOi9er7x2wGwUjgAJpbHShiwTDY2IR1HmoJq','admin',NULL,1,'2026-03-24 16:25:44',NULL,NULL,0,NULL,NULL,NULL),(4,'Ana Maria - Maestros Joyeros','anamaria@gmail.com','$2y$10$80LBjl/vDWHkfjpjNFgf1.0k7KZC7WezPOnEvZ.JofK9cdyFHtNEa','promotor',NULL,1,'2026-09-24 00:07:43',NULL,NULL,0,NULL,NULL,NULL),(5,'Tlaloc','tlaloc.finanzas@albardi.mx','$2y$10$nK81DOxk3aoLKQyk.4cod.sh64o5Kp/lkR3CHPuLKZBwlp4/Lrb82','usuario',NULL,1,'2026-11-04 16:51:48',NULL,NULL,0,NULL,NULL,NULL);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `v_deudas_clientes_pendientes`
--

DROP TABLE IF EXISTS `v_deudas_clientes_pendientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `v_deudas_clientes_pendientes` (
  `id` int(11) DEFAULT NULL,
  `cliente` varchar(100) DEFAULT NULL,
  `monto_total` decimal(12,2) DEFAULT NULL,
  `monto_pagado` decimal(12,2) DEFAULT NULL,
  `saldo_pendiente` decimal(13,2) DEFAULT NULL,
  `estado` enum('pendiente','pagado','parcial') DEFAULT NULL,
  `fecha` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `v_deudas_clientes_pendientes`
--

LOCK TABLES `v_deudas_clientes_pendientes` WRITE;
/*!40000 ALTER TABLE `v_deudas_clientes_pendientes` DISABLE KEYS */;
/*!40000 ALTER TABLE `v_deudas_clientes_pendientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `v_deudas_empresa_pendientes`
--

DROP TABLE IF EXISTS `v_deudas_empresa_pendientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `v_deudas_empresa_pendientes` (
  `id` int(11) DEFAULT NULL,
  `prestamista` varchar(100) DEFAULT NULL,
  `monto` decimal(12,2) DEFAULT NULL,
  `monto_pagado` decimal(12,2) DEFAULT NULL,
  `saldo_pendiente` decimal(13,2) DEFAULT NULL,
  `estado` enum('pendiente','pagado','parcial') DEFAULT NULL,
  `fecha` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `v_deudas_empresa_pendientes`
--

LOCK TABLES `v_deudas_empresa_pendientes` WRITE;
/*!40000 ALTER TABLE `v_deudas_empresa_pendientes` DISABLE KEYS */;
/*!40000 ALTER TABLE `v_deudas_empresa_pendientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `v_saldos_cuentas`
--

DROP TABLE IF EXISTS `v_saldos_cuentas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `v_saldos_cuentas` (
  `id` int(11) DEFAULT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `moneda` varchar(10) DEFAULT NULL,
  `saldo_actual` decimal(12,2) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `v_saldos_cuentas`
--

LOCK TABLES `v_saldos_cuentas` WRITE;
/*!40000 ALTER TABLE `v_saldos_cuentas` DISABLE KEYS */;
/*!40000 ALTER TABLE `v_saldos_cuentas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'amiri_reparada'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-04 17:36:25
