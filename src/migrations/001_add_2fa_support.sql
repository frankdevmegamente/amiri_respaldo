-- ============================================
-- MIGRACIÓN: Agregar soporte para 2FA
-- ============================================
-- Agregar columnas a tabla usuarios para soportar TOTP 2FA

ALTER TABLE `usuarios` ADD COLUMN `two_fa_enabled` TINYINT(1) DEFAULT 0 COMMENT 'Indica si 2FA está habilitado';
ALTER TABLE `usuarios` ADD COLUMN `two_fa_secret` VARCHAR(255) DEFAULT NULL COMMENT 'Secret key para TOTP (encriptado)';
ALTER TABLE `usuarios` ADD COLUMN `two_fa_backup_codes` JSON DEFAULT NULL COMMENT 'Array de códigos de respaldo';
ALTER TABLE `usuarios` ADD COLUMN `two_fa_created_at` TIMESTAMP NULL COMMENT 'Cuándo se configuró 2FA';

-- Crear tabla de dispositivos de confianza
CREATE TABLE `trusted_devices` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `usuario_id` INT NOT NULL,
  `device_token` VARCHAR(255) NOT NULL UNIQUE COMMENT 'Token único del dispositivo',
  `device_name` VARCHAR(100) NOT NULL COMMENT 'Nombre del dispositivo (ej: Chrome Windows)',
  `ip_address` VARCHAR(45) NOT NULL COMMENT 'IP de donde se confió',
  `user_agent` VARCHAR(255) NOT NULL COMMENT 'User agent del navegador',
  `trusted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `expires_at` TIMESTAMP NOT NULL COMMENT 'Expires 24 horas después',
  `last_used_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_token` (`device_token`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_expires_at` (`expires_at`),
  KEY `idx_usuario_activo` (`usuario_id`, `expires_at`),
  CONSTRAINT `fk_trusted_device_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Dispositivos confiables por 24 horas para saltarse 2FA';

-- Tabla de intentos fallidos de 2FA (para rate limiting)
CREATE TABLE `two_fa_attempts` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `usuario_id` INT NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `failed_attempts` INT NOT NULL DEFAULT 1,
  `first_attempt_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `last_attempt_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `blocked_until` TIMESTAMP NULL COMMENT 'Bloqueado hasta esta hora si hay demasiados intentos',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_ip` (`usuario_id`, `ip_address`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_ip_address` (`ip_address`),
  CONSTRAINT `fk_2fa_attempts_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Rastreo de intentos fallidos de 2FA';
