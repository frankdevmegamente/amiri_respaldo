-- Script para agregar el servicio "Retiro de Efectivo" con ID 7
-- Fecha: 2026-01-27

INSERT INTO `servicios` (`id`, `nombre`, `descripcion`, `tipo_movimiento`, `activo`, `creado_en`, `actualizado_en`) 
VALUES (7, 'Retiro de Efectivo', 'Retiro de efectivo por el cliente', 'salida', 1, NOW(), NOW());
