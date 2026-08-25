-- Índices compuestos para mejorar rendimiento de consultas frecuentes
-- Ver: análisis de rendimiento 2026-07-07

-- 1. movimientos: cubre consultas de comisiones de divisas (servicio_id IN 2,3, tipo='comision', cuenta_destino_id=1)
ALTER TABLE `movimientos` ADD INDEX `idx_perf_divisas` (`servicio_id`, `tipo`, `cuenta_destino_id`, `fecha`, `activo`);

-- 2. deudas_clientes: cubre consultas SUM con rango de fechas + activo
ALTER TABLE `deudas_clientes` ADD INDEX `idx_fecha_activo` (`fecha`, `activo`);

-- 3. gastos: cubre consultas SUM con rango de fechas + activo
ALTER TABLE `gastos` ADD INDEX `idx_fecha_activo` (`fecha`, `activo`);
