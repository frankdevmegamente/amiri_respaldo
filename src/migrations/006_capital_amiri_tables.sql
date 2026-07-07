-- Tabla de cortes mensuales de Capital Amiri
CREATE TABLE IF NOT EXISTS `cierres_mensuales` (
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
  KEY `idx_mes` (`mes` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de retiros de capital
CREATE TABLE IF NOT EXISTS `retiros_capital` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `monto` decimal(14,2) NOT NULL,
  `concepto` varchar(255) NOT NULL,
  `fecha` date NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `usuario_nombre` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_fecha` (`fecha` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tipo de cuenta para Capital Amiri
INSERT IGNORE INTO `tipos_cuenta` (`id`, `descripcion`) VALUES (7, 'Capital Amiri');

-- Cuenta de Capital Amiri (saldo inicial $0)
INSERT IGNORE INTO `cuentas` (`nombre`, `tipo_cuenta`, `tipo_moneda`, `saldo_actual`, `valor_mxn`) VALUES ('Capital Amiri', 7, 'MXN', 0, 0);
