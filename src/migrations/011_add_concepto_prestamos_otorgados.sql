-- Migracion 011: agregar concepto opcional a prestamos_otorgados
SET @col_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'prestamos_otorgados' AND COLUMN_NAME = 'concepto');
SET @ddl := IF(@col_exists = 0, 'ALTER TABLE prestamos_otorgados ADD COLUMN concepto VARCHAR(255) NULL DEFAULT NULL AFTER persona_nombre', 'SELECT 1');
PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
