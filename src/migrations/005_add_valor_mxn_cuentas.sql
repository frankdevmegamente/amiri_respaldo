-- =====================================================
-- Migración 005: Agregar valor_mxn a cuentas
-- Propósito: Almacenar el valor en MXN de las cuentas
-- de proveedor (tipo 6) para mostrar cuánto debemos
-- en pesos, separado del monto en divisa.
-- =====================================================

ALTER TABLE `cuentas`
    ADD COLUMN `valor_mxn` DECIMAL(14,2) NOT NULL DEFAULT 0.00
    AFTER `saldo_actual`;

-- Índice para consultas de resumen
ALTER TABLE `cuentas`
    ADD KEY `idx_tipo_valor` (`tipo_cuenta`, `valor_mxn`);
