-- Migración 010: Agregar columna cuenta_id a retiros_capital
-- Fecha: 2026-08-04
-- Propósito: Retiro de capital con selector de cuenta (2026-07-23, BITACORA.md).
--            Permite saber de qué cuenta se descontó cada retiro.

ALTER TABLE `retiros_capital`
  ADD COLUMN `cuenta_id` int(11) DEFAULT NULL AFTER `created_at`;
