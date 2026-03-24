-- =====================================================
-- Migración 002: Soporte para Divisas (USD/USDT)
-- Fecha: 2026-03-08
-- Descripción: Agrega campos para manejo multi-moneda
-- =====================================================

-- =====================================================
-- 1. TABLA CLIENTES - Agregar campos de monedas operables
-- =====================================================
-- Permite que un cliente opere con múltiples monedas (checkboxes)

ALTER TABLE clientes 
    ADD COLUMN opera_mxn TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Puede operar con MXN (venta de efectivo)',
    ADD COLUMN opera_usd TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Puede operar con USD (compra/venta de dólares)',
    ADD COLUMN opera_usdt TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Puede operar con USDT (compra/venta de USDT)';

-- Permitir promotor_id NULL para clientes USD/USDT (no tienen promotor)
ALTER TABLE clientes 
    MODIFY COLUMN promotor_id INT NULL COMMENT 'Promotor asociado (solo requerido para clientes MXN)';

-- Índices para búsqueda rápida por tipo de moneda
ALTER TABLE clientes 
    ADD INDEX idx_opera_mxn (opera_mxn),
    ADD INDEX idx_opera_usd (opera_usd),
    ADD INDEX idx_opera_usdt (opera_usdt);

-- =====================================================
-- 2. TABLA PROVEEDORES - Agregar tipo de moneda
-- =====================================================
-- Los proveedores son de USD o USDT (no MXN)

ALTER TABLE proveedores 
    ADD COLUMN tipo_moneda ENUM('USD', 'USDT') NOT NULL DEFAULT 'USD' COMMENT 'Tipo de moneda que maneja el proveedor';

-- =====================================================
-- 3. TABLA VENTAS_EFECTIVO_CLIENTES - Agregar tipo de moneda
-- =====================================================
-- Permite diferenciar deudas por moneda

ALTER TABLE ventas_efectivo_clientes 
    ADD COLUMN moneda ENUM('MXN', 'USD', 'USDT') NOT NULL DEFAULT 'MXN' COMMENT 'Moneda de la deuda';

-- Índice para filtrar por moneda
ALTER TABLE ventas_efectivo_clientes 
    ADD INDEX idx_moneda (moneda);

ALTER TABLE proveedores 
    ADD COLUMN tipo_moneda ENUM('USD', 'USDT') NOT NULL DEFAULT 'USD' COMMENT 'Moneda que maneja el proveedor';

-- Índice para filtrar proveedores por moneda
ALTER TABLE proveedores 
    ADD INDEX idx_tipo_moneda (tipo_moneda);

-- =====================================================
-- 3. VERIFICACIÓN
-- =====================================================
-- Ejecutar para verificar que la migración fue exitosa:
-- SELECT 'clientes' AS tabla, COUNT(*) AS registros, 
--        SUM(opera_mxn) AS con_mxn, SUM(opera_usd) AS con_usd, SUM(opera_usdt) AS con_usdt 
-- FROM clientes;
-- 
-- SELECT 'proveedores' AS tabla, tipo_moneda, COUNT(*) AS total 
-- FROM proveedores GROUP BY tipo_moneda;
