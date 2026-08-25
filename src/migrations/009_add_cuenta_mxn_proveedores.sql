-- Migración 009: Agregar cuenta MXN a proveedores para Compra de Efectivo
-- Fecha: 2026-07-24

-- 1. Agregar columna cuenta_mxn_id
ALTER TABLE `proveedores`
  ADD COLUMN `cuenta_mxn_id` int(11) DEFAULT NULL AFTER `cuenta_usdt_id`;

ALTER TABLE `proveedores`
  ADD KEY `fk_proveedor_cuenta_mxn` (`cuenta_mxn_id`);

-- 2. Crear cuentas MXN para proveedores existentes que no tengan una
-- Primero insertamos las cuentas
INSERT INTO `cuentas` (`nombre`, `tipo_cuenta`, `tipo_moneda`, `saldo_actual`, `valor_mxn`, `activo`)
SELECT CONCAT('Caja Proveedor MXN - ', p.nombre), 6, 'MXN', 0.00, 0.00, 1
FROM `proveedores` p
WHERE p.activo = 1
  AND NOT EXISTS (
    SELECT 1 FROM `cuentas` c WHERE c.nombre = CONCAT('Caja Proveedor MXN - ', p.nombre)
  );

-- Luego vincularlas
UPDATE `proveedores` p
INNER JOIN `cuentas` c ON c.nombre = CONCAT('Caja Proveedor MXN - ', p.nombre)
SET p.cuenta_mxn_id = c.id
WHERE p.activo = 1 AND p.cuenta_mxn_id IS NULL;
