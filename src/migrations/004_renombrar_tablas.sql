-- ============================================================================
-- MIGRACIÓN 004: Renombrar Tablas para Mayor Claridad
-- Fecha: 2026-03-09
-- Descripción: Renombra las tablas para que sean más intuitivas
-- 
-- IMPORTANTE: Ejecutar ANTES de 003_sistema_correcciones.sql si vienes de v1.0
-- ============================================================================

-- ============================================================================
-- PARTE 1: RENOMBRAR TABLAS
-- ============================================================================

-- flujo_movimientos → movimientos
RENAME TABLE flujo_movimientos TO movimientos;

-- inventario_divisas → inventario_monedas  
RENAME TABLE inventario_divisas TO inventario_monedas;

-- ventas_efectivo_clientes → deudas_clientes
RENAME TABLE ventas_efectivo_clientes TO deudas_clientes;

-- prestamos_prestamistas → deudas_empresa
RENAME TABLE prestamos_prestamistas TO deudas_empresa;

-- pagos_comisiones_promotores → pagos_promotores
RENAME TABLE pagos_comisiones_promotores TO pagos_promotores;

-- tipos_gasto → categorias_gasto
RENAME TABLE tipos_gasto TO categorias_gasto;

-- cuentas_tipo → tipos_cuenta
RENAME TABLE cuentas_tipo TO tipos_cuenta;

-- movimientos_cuenta → historial_saldos
RENAME TABLE movimientos_cuenta TO historial_saldos;


-- ============================================================================
-- PARTE 2: RENOMBRAR COLUMNAS
-- ============================================================================

-- En historial_saldos: flujo_id → movimiento_id
ALTER TABLE historial_saldos 
CHANGE COLUMN flujo_id movimiento_id BIGINT NOT NULL;

-- En inventario_monedas: flujo_id → movimiento_id
ALTER TABLE inventario_monedas 
CHANGE COLUMN flujo_id movimiento_id BIGINT NOT NULL;

-- En gastos: flujo_id → movimiento_id, tipo_gasto_id → categoria_id
ALTER TABLE gastos 
CHANGE COLUMN flujo_id movimiento_id BIGINT DEFAULT NULL;

ALTER TABLE gastos 
CHANGE COLUMN tipo_gasto_id categoria_id INT DEFAULT NULL;

-- En pagos_promotores: flujo_id → movimiento_id
ALTER TABLE pagos_promotores 
CHANGE COLUMN flujo_id movimiento_id BIGINT DEFAULT NULL;

-- En deudas_clientes: flujo_venta_id → movimiento_venta_id, flujo_pago_id → movimiento_pago_id
ALTER TABLE deudas_clientes 
CHANGE COLUMN flujo_venta_id movimiento_venta_id BIGINT DEFAULT NULL;

ALTER TABLE deudas_clientes 
CHANGE COLUMN flujo_pago_id movimiento_pago_id BIGINT DEFAULT NULL;


-- ============================================================================
-- PARTE 3: ACTUALIZAR FOREIGN KEYS (si existen)
-- ============================================================================

-- Actualizar FK en cuentas que apunta a tipos_cuenta
ALTER TABLE cuentas DROP FOREIGN KEY fk_tipo_cuenta;
ALTER TABLE cuentas ADD CONSTRAINT fk_tipo_cuenta 
    FOREIGN KEY (tipo_cuenta) REFERENCES tipos_cuenta(id);

-- Actualizar FK en movimientos que apunta a servicios
ALTER TABLE movimientos DROP FOREIGN KEY flujo_movimientos_ibfk_1;
ALTER TABLE movimientos ADD CONSTRAINT fk_movimiento_servicio 
    FOREIGN KEY (servicio_id) REFERENCES servicios(id);


-- ============================================================================
-- VERIFICACIÓN
-- ============================================================================
-- Ejecutar estas consultas para verificar que todo está correcto:
-- 
-- SHOW TABLES;
-- 
-- Deberías ver:
-- - auditoria
-- - categorias_gasto (antes tipos_gasto)
-- - clientes
-- - cuentas
-- - deudas_clientes (antes ventas_efectivo_clientes)
-- - deudas_empresa (antes prestamos_prestamistas)
-- - gastos
-- - historial_saldos (antes movimientos_cuenta)
-- - inventario_monedas (antes inventario_divisas)
-- - movimientos (antes flujo_movimientos)
-- - pagos_promotores (antes pagos_comisiones_promotores)
-- - prestamistas
-- - promotores
-- - proveedores
-- - servicios
-- - tipos_cuenta (antes cuentas_tipo)
-- - trusted_devices
-- - two_fa_attempts
-- - usuarios
-- ============================================================================
