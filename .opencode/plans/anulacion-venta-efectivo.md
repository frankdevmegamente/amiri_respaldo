# Resumen Sesión - 10/Jun/2026

## Objetivo
Anular una **Venta de Efectivo** (servicio_id=1) del **08/Jun/2026** a las **10:50 a.m.** por $15,000 MXN, cliente **Adriana Hurtado**, afectando cuentas **Tarpen Sa De Cv** y **Utilidades AMIRI**.

## Datos del Grupo Anulado
- **Grupo ID**: 1780937424728
- **Movimientos**:
  - ID:1256 — $14,850.00 → Caja Adriana Hurtado (cuenta 28)
  - ID:1257 — $150.00 → Utilidades AMIRI (cuenta 1)
  - ID:1258 — $15,000.00 → Tarpen Sa De Cv (cuenta 34)
- **Deuda relacionada**: `deudas_clientes` ID:81

## SQL Ejecutado (local — BD: `amiri_reparada`)

```sql
START TRANSACTION;

UPDATE cuentas SET saldo_actual = ROUND(saldo_actual - 14850.00, 2) WHERE id = 28;
UPDATE cuentas SET saldo_actual = ROUND(saldo_actual - 150.00, 2) WHERE id = 1;
UPDATE cuentas SET saldo_actual = ROUND(saldo_actual - 15000.00, 2) WHERE id = 34;

UPDATE movimientos SET activo = 0, estado = 'anulado' WHERE id IN (1256, 1257, 1258);

UPDATE deudas_clientes SET activo = 0 WHERE id = 81;

COMMIT;
```

## Estado Final (después de anulación)

| Cuenta | Saldo anterior | Saldo nuevo |
|:---|---:|---:|
| Utilidades AMIRI (1) | $55,525.31 | $55,375.31 |
| Caja Adriana (28) | $49,500.00 | $34,650.00 |
| Tarpen Sa De Cv (34) | $669,528.55 | $654,528.55 |

- Movs 1256,1257,1258: `estado=anulado`, `activo=0`
- Deuda 81: `activo=0`

## Pendiente para mañana
- ~~Anular el movimiento~~ ✅ Hecho
- **Revisar/ajustar reportes** que se vean afectados por esta anulación (usuario los pasará)
- Aplicar el mismo SQL en BD de producción (verificar IDs primero)
