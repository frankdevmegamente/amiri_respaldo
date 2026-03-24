-- ============================================================================
-- MIGRACIÓN 003: Sistema de Correcciones y Mejoras de Trazabilidad
-- Fecha: 2026-03-09
-- Descripción: Implementa sistema de movimientos correctivos, histórico de 
--              saldos y estandariza soft-delete
-- NOTA: Esta migración usa los nuevos nombres de tablas (v2.0)
--       Si tienes la BD antigua, usa primero 004_renombrar_tablas.sql
-- ============================================================================

-- ============================================================================
-- PARTE 1: MODIFICACIONES A movimientos (antes: flujo_movimientos)
-- ============================================================================

-- Agregar campo estado para reemplazar el simple activo=0/1
ALTER TABLE movimientos 
ADD COLUMN estado ENUM('activo', 'anulado', 'corregido') DEFAULT 'activo' 
COMMENT 'Estado del movimiento: activo=normal, anulado=cancelado, corregido=reemplazado por otro'
AFTER activo;

-- Migrar datos existentes: activo=1 → estado='activo', activo=0 → estado='anulado'
UPDATE movimientos SET estado = 'activo' WHERE activo = 1;
UPDATE movimientos SET estado = 'anulado' WHERE activo = 0;

-- Agregar campo para indicar si es un movimiento de reverso
ALTER TABLE movimientos 
ADD COLUMN es_reverso TINYINT(1) DEFAULT 0 
COMMENT '1 si es un movimiento que revierte otro (corrección)'
AFTER estado;

-- Agregar referencia al movimiento original (si es corrección/reverso)
ALTER TABLE movimientos 
ADD COLUMN movimiento_original_id BIGINT DEFAULT NULL 
COMMENT 'Si es reverso o corrección, apunta al movimiento original'
AFTER es_reverso;

-- Agregar índice para búsquedas de correcciones
ALTER TABLE movimientos 
ADD INDEX idx_movimiento_original (movimiento_original_id);

-- Agregar motivo de la corrección
ALTER TABLE movimientos 
ADD COLUMN motivo_correccion VARCHAR(255) DEFAULT NULL 
COMMENT 'Razón por la cual se hizo la corrección (si aplica)'
AFTER movimiento_original_id;

-- Agregar referencia a quién hizo la corrección
ALTER TABLE movimientos 
ADD COLUMN corregido_por_usuario_id INT DEFAULT NULL 
COMMENT 'Usuario que realizó la corrección'
AFTER motivo_correccion;

-- Agregar timestamp de corrección
ALTER TABLE movimientos 
ADD COLUMN corregido_en TIMESTAMP NULL DEFAULT NULL 
COMMENT 'Fecha/hora de la corrección'
AFTER corregido_por_usuario_id;

-- ============================================================================
-- PARTE 2: ACTIVAR Y MEJORAR historial_saldos (antes: movimientos_cuenta)
-- ============================================================================

-- La tabla ya existe, agregar campos útiles
ALTER TABLE historial_saldos 
ADD COLUMN descripcion VARCHAR(255) DEFAULT NULL 
COMMENT 'Descripción del movimiento que causó el cambio'
AFTER saldo_nuevo;

ALTER TABLE historial_saldos 
ADD COLUMN usuario_id INT DEFAULT NULL 
COMMENT 'Usuario que realizó la operación'
AFTER descripcion;

-- Agregar índices para consultas de histórico
ALTER TABLE historial_saldos 
ADD INDEX idx_cuenta_fecha (cuenta_id, created_at);

ALTER TABLE historial_saldos 
ADD INDEX idx_movimiento (movimiento_id);

-- ============================================================================
-- PARTE 3: ESTANDARIZAR SOFT-DELETE EN TODAS LAS TABLAS
-- ============================================================================

-- clientes: Ya no tiene activo, agregar
ALTER TABLE clientes 
ADD COLUMN activo TINYINT(1) DEFAULT 1 
COMMENT '1=activo, 0=eliminado (soft-delete)';

-- Agregar fecha de eliminación para trazabilidad
ALTER TABLE clientes 
ADD COLUMN eliminado_en TIMESTAMP NULL DEFAULT NULL 
COMMENT 'Fecha de soft-delete';

ALTER TABLE clientes 
ADD COLUMN eliminado_por_usuario_id INT DEFAULT NULL 
COMMENT 'Usuario que eliminó el registro';

-- servicios: Agregar campos de trazabilidad
ALTER TABLE servicios 
ADD COLUMN eliminado_en TIMESTAMP NULL DEFAULT NULL 
COMMENT 'Fecha de soft-delete';

-- gastos: Agregar soft-delete
ALTER TABLE gastos 
ADD COLUMN activo TINYINT(1) DEFAULT 1 
COMMENT '1=activo, 0=eliminado';

ALTER TABLE gastos 
ADD COLUMN eliminado_en TIMESTAMP NULL DEFAULT NULL;

-- tipos_gasto: Agregar soft-delete
ALTER TABLE tipos_gasto 
ADD COLUMN activo TINYINT(1) DEFAULT 1;

ALTER TABLE tipos_gasto 
ADD COLUMN eliminado_en TIMESTAMP NULL DEFAULT NULL;

-- ============================================================================
-- PARTE 4: MEJORAR inventario_divisas PARA FIFO
-- ============================================================================

-- Agregar estado al inventario
ALTER TABLE inventario_divisas 
ADD COLUMN estado ENUM('disponible', 'parcial', 'agotado') DEFAULT 'disponible' 
COMMENT 'disponible=todo disponible, parcial=parcialmente usado, agotado=FIFO completado';

-- Agregar referencia a movimientos de consumo
ALTER TABLE inventario_divisas 
ADD COLUMN consumido_en JSON DEFAULT NULL 
COMMENT 'Array de {flujo_id, cantidad, fecha} que consumieron este lote';

-- ============================================================================
-- PARTE 5: COMENTARIOS DESCRIPTIVOS EN TABLAS PRINCIPALES
-- ============================================================================

ALTER TABLE flujo_movimientos 
COMMENT = 'Registro maestro de TODAS las transacciones financieras del sistema. grupo_id agrupa movimientos de una misma operación.';

ALTER TABLE cuentas 
COMMENT = 'Cuentas bancarias, cajas de efectivo, y cuentas virtuales de clientes/promotores/proveedores.';

ALTER TABLE clientes 
COMMENT = 'Clientes que realizan operaciones de cambio de divisas o venta de efectivo.';

ALTER TABLE promotores 
COMMENT = 'Agentes de ventas que traen clientes. es_interno=1 para operaciones directas de AMIRI.';

ALTER TABLE proveedores 
COMMENT = 'Proveedores de divisas (USD/USDT) para operaciones de compra.';

ALTER TABLE inventario_divisas 
COMMENT = 'Tracking FIFO de divisas compradas. Cada lote tiene paridad de compra para calcular ganancia en venta.';

ALTER TABLE movimientos_cuenta 
COMMENT = 'Histórico de cambios de saldo por cuenta. Permite ver evolución temporal y hacer reconciliaciones.';

ALTER TABLE auditoria 
COMMENT = 'Log de todas las acciones importantes del sistema. Inmutable para cumplimiento y trazabilidad.';

-- ============================================================================
-- VERIFICACIÓN
-- ============================================================================
-- Ejecutar después de la migración para verificar:
-- SHOW COLUMNS FROM flujo_movimientos;
-- SHOW COLUMNS FROM movimientos_cuenta;
-- SHOW COLUMNS FROM clientes;
