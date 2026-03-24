-- ============================================
-- Script: Crear Promotor AMIRI DIRECTO
-- Descripción: Agrega un promotor interno para clientes directos de Amiri
-- Fecha: 2026-01-28
-- ============================================

-- 1. Agregar campo es_interno a la tabla promotores (si no existe)
ALTER TABLE promotores 
ADD COLUMN IF NOT EXISTS es_interno TINYINT(1) DEFAULT 0 COMMENT 'Indica si es un promotor interno de Amiri';

-- 2. Obtener el ID de la cuenta "Comisiones AMIRI" (normalmente ID = 1)
-- Insertar el promotor AMIRI DIRECTO
INSERT INTO promotores (
    nombre, 
    telefono, 
    email, 
    cuenta_id, 
    es_interno,
    activo, 
    creado_en
) VALUES (
    'AMIRI DIRECTO',
    'N/A',
    'interno@amiri.com',
    (SELECT id FROM cuentas WHERE nombre = 'Comisiones AMIRI' OR id = 1 LIMIT 1),
    1,  -- es_interno = true
    1,  -- activo = true
    NOW()
);

-- 3. Verificar la inserción
SELECT 
    id,
    nombre,
    email,
    cuenta_id,
    es_interno,
    activo,
    creado_en
FROM promotores 
WHERE nombre = 'AMIRI DIRECTO';

-- ============================================
-- NOTAS:
-- - Este promotor será usado para clientes directos de Amiri
-- - Las comisiones irán automáticamente a la cuenta "Comisiones AMIRI"
-- - El campo es_interno permite filtrarlo de listados si es necesario
-- ============================================
