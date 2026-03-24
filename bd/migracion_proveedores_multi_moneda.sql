-- Migración: Proveedores multi-moneda (USD y/o USDT)
-- Ejecutar una sola vez antes de limpiar/reimportar la BD

-- 1. Agregar nuevas columnas a la tabla proveedores
ALTER TABLE proveedores
    ADD COLUMN acepta_usd  TINYINT(1) NOT NULL DEFAULT 0 AFTER tipo_moneda,
    ADD COLUMN acepta_usdt TINYINT(1) NOT NULL DEFAULT 0 AFTER acepta_usd,
    ADD COLUMN cuenta_usd_id  INT NULL AFTER cuenta_id,
    ADD COLUMN cuenta_usdt_id INT NULL AFTER cuenta_usd_id;

-- 2. Migrar datos existentes basados en tipo_moneda
UPDATE proveedores SET acepta_usd  = 1, cuenta_usd_id  = cuenta_id WHERE tipo_moneda = 'USD';
UPDATE proveedores SET acepta_usdt = 1, cuenta_usdt_id = cuenta_id WHERE tipo_moneda = 'USDT';

-- 3. (Opcional) Agregar FK si lo deseas
-- ALTER TABLE proveedores ADD CONSTRAINT fk_prov_cuenta_usd  FOREIGN KEY (cuenta_usd_id)  REFERENCES cuentas(id);
-- ALTER TABLE proveedores ADD CONSTRAINT fk_prov_cuenta_usdt FOREIGN KEY (cuenta_usdt_id) REFERENCES cuentas(id);
