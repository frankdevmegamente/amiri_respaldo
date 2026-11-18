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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cuentas`
--

LOCK TABLES `cuentas` WRITE;
/*!40000 ALTER TABLE `cuentas` DISABLE KEYS */;
/*!40000 ALTER TABLE `cuentas` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promotores`
--

LOCK TABLES `promotores` WRITE;
/*!40000 ALTER TABLE `promotores` DISABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedores`
--

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
/*!40000 ALTER TABLE `proveedores` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prestamistas`
--

LOCK TABLES `prestamistas` WRITE;
/*!40000 ALTER TABLE `prestamistas` DISABLE KEYS */;
/*!40000 ALTER TABLE `prestamistas` ENABLE KEYS */;
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
INSERT INTO `servicios` VALUES (1,'Venta de Efectivo','Venta de efectivo a clientes','entrada',1,'2026-03-13 13:37:15','2026-03-13 13:37:15'),(2,'Venta de Dolares','Venta de d├ö├Â┬ú├ö├Â├®lares a clientes','entrada',1,'2026-03-13 13:37:15','2026-03-13 13:37:15'),(3,'Venta de USDT','Venta de USDT a clientes','entrada',1,'2026-03-13 13:37:15','2026-03-13 13:37:15'),(4,'Cambio de Flujo','Movimientos entre cuentas sin cambiar capital','salida',1,'2026-03-13 13:37:15','2026-03-13 13:37:15'),(5,'Compra de Dolares','Compra de d├ö├Â┬ú├ö├Â├®lares a clientes','salida',1,'2026-03-13 13:37:15','2026-03-13 13:37:15'),(6,'Compra de USDT','Compra de USDT a clientes','salida',1,'2026-03-13 13:37:15','2026-03-13 13:37:15'),(7,'Retiro de Efectivo','Retiro de efectivo por el cliente','salida',1,'2026-03-13 13:37:15','2026-03-13 13:37:15'),(10,'Pr├ö├Â┬úÔö¼┬½stamo de Prestamista','Pr├ö├Â┬úÔö¼┬½stamo recibido de prestamista','entrada',1,'2026-03-13 13:37:15','2026-03-13 13:37:15'),(11,'Pago a Proveedor','Pago realizado a proveedor','salida',1,'2026-03-13 13:37:15','2026-03-13 13:37:15'),(12,'Gasto','Gasto operativo','salida',1,'2026-03-13 13:37:15','2026-03-13 13:37:15'),(13,'Pago a Cliente','Pago realizado a cliente','salida',1,'2026-03-13 13:37:15','2026-03-13 13:37:15'),(14,'Pago de Pr├ö├Â┬úÔö¼┬½stamo','Pago de pr├ö├Â┬úÔö¼┬½stamo a prestamista','salida',1,'2026-03-13 13:37:15','2026-03-13 13:37:15'),(15,'Pago de Comisi├ö├Â┬ú├ö├Â├®n a Promotor','Pago de comisi├ö├Â┬ú├ö├Â├®n a promotor','salida',1,'2026-03-13 13:37:15','2026-03-13 13:37:15'),(16,'Pr├ö├Â┬úÔö¼┬½stamo Otorgado','Salida de dinero por pr├ö├Â┬úÔö¼┬½stamo a terceros','salida',1,'2026-05-05 10:00:57','2026-05-05 10:00:57'),(17,'Cobro de Pr├ö├Â┬úÔö¼┬½stamo','Entrada de dinero por cobro de pr├ö├Â┬úÔö¼┬½stamo','entrada',1,'2026-05-05 10:00:57','2026-05-05 10:00:57'),(18,'Retiro de Capital',NULL,'entrada',1,'2026-07-04 12:13:57','2026-07-04 12:13:57'),(19,'Cierre de Mes','Cierre mensual de capital y traspaso de utilidades','salida',1,'2026-07-04 12:13:57','2026-07-04 12:13:57'),(20,'Compra de Efectivo',NULL,'entrada',1,'2026-07-04 12:13:57','2026-07-04 12:13:57');
/*!40000 ALTER TABLE `servicios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-04 17:38:58
