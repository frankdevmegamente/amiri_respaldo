# Bitácora — Capital Amiri

## 2026-06-29

### Problema
El módulo Capital Amiri (página, API, sidebar, reportes) estaba construido, pero la base de datos usada era una versión vieja que NO contenía las tablas `cierres_mensuales` ni `retiros_capital`.

### Solución aplicada
Se creó la migración `src/migrations/006_capital_amiri_tables.sql` con las dos tablas y se ejecutó exitosamente:

- **`cierres_mensuales`**: cortes mensuales con comisiones, divisas, gastos, utilidad neta y capital acumulado.
- **`retiros_capital`**: retiros con monto, concepto, fecha, usuario.

### Archivos involucrados

| Archivo | Propósito |
|---|---|
| `capital-amiri.php` | Página principal (layout, remanente detallado, historial de cortes, tabs, modales) |
| `api/capital.php` | API REST: resumen, cierres, retiros, cerrar_mes, retiro, historial |
| `src/assets/partials/sidebar.php` | Link "Capital Amiri" entre Cuentas y Flujo |
| `reportes.php` | Widget de resumen de Capital Amiri |
| `api/reportes.php` | Endpoint `capital_amiri` |
| `api/exportar-reporte.php` | Exportación PDF/Excel de Capital Amiri |
| `src/migrations/006_capital_amiri_tables.sql` | Creación de tablas faltantes |

### Pendientes para mañana
1. **Verificar cálculos cruzados**: cuando se registre un nuevo cierre de mes, validar que:
   - `capital_actual = capital_cerrado + utilidad_mes - total_retiros`
   - La tabla de Remanente Detallado muestre valores correctos
   - `index.php` (dashboard) refleje los valores esperados (utilidades_acumuladas como Remanente grande)
2. **El usuario dijo que pasará una lista** de ajustes/features adicionales para Capital Amiri.
3. **Probar cierre de un mes** (sugerido: cerrar un mes pasado sin datos) para verificar que la sincronización con `tipo_cuenta = 3` funciona.
4. **Confirmar que `index.php`** usa `utilidades_acumuladas` (saldo de `tipo_cuenta = 3`) como Remanente principal.

### Estado actual
- BD: tablas creadas, sin cortes registrados, `tipo_cuenta = 3` (Utilidades AMIRI) = $332,642.67
- UI: completa con layout, gráficas, DataTables, modales
- API: todos los endpoints funcionales

---

## 2026-06-30

### 1. Cuenta "Capital Amiri" independiente

- Creada nueva caja **Capital Amiri** (ID: 206, `tipo_cuenta=7`) con saldo **$0.00**
- Creado `tipo_cuenta=7` en `tipos_cuenta` con descripción "Capital Amiri"
- Actualizada migración `006_capital_amiri_tables.sql` con el INSERT de `tipos_cuenta`

**Archivos:**
- `api/capital.php` — Cambiadas todas las referencias de `tipo_cuenta = 3` → `tipo_cuenta = 7`
- `api/reportes.php` — Idem en endpoint `capital_amiri`
- `api/exportar-reporte.php` — Idem en exportación

### 2. Capital Amiri en el Flujo

- Agregado `tipo_cuenta=7` a todas las queries de Capital en `api/flujo-resumen.php`
- `ORDER BY` actualizado: Capital Amiri aparece primero (WHEN 7 THEN 1), luego Utilidades AMIRI

### 3. Proveedores movidos a Capital

- Agregado `tipo_cuenta=6` (Proveedores) a las queries de Capital en el flujo
- Eliminada la sección "Proveedores" de la tarjeta "PRÉSTAMOS / PROVEEDORES"
- Renombrada la tarjeta a "PRÉSTAMOS" (solo prestamistas + préstamos otorgados)
- Proveedores ahora aparecen en la tarjeta de CAPITAL

### Archivos modificados hoy

| Archivo | Cambio |
|---------|--------|
| `api/capital.php` | `tipo_cuenta=3` → `tipo_cuenta=7` (nueva caja Capital Amiri) |
| `api/flujo-resumen.php` | Agregados `tipo_cuenta=7` (Capital Amiri) y `tipo_cuenta=6` (Proveedores) a Capital |
| `flujo.php` | Movidos proveedores de Préstamos a Capital, header renombrado |
| `api/reportes.php` | `tipo_cuenta=3` → `tipo_cuenta=7` en endpoint capital_amiri |
| `api/exportar-reporte.php` | `tipo_cuenta=3` → `tipo_cuenta=7` en exportación |
| `src/migrations/006_capital_amiri_tables.sql` | Agregado `INSERT INTO tipos_cuenta (7, 'Capital Amiri')` |

### Estado actual
- **Capital Amiri** (`tipo_cuenta=7`): $0.00 (lista para recibir cortes)
- **Utilidades AMIRI** (`tipo_cuenta=3`): $332,642.67 (histórico, intacto)
- **Dashboard** (index.php): sin cambios, sigue mostrando $332,642.67
- **Proveedores**: ahora en sección Capital del flujo
- **Capital Amiri**: aparece primero en la sección Capital (alfabéticamente)

---

## 2026-07-03

### 1. Reportes-servicios: resumen con divisa, MXN, ganancia FIFO

- **`api/reportes-servicios.php`**:
  - Endpoint `resumen`: agrupa movs por `operation_key`, prefiere divisa (USDT/USD) sobre MXN, excluye `tipo='ajuste'`
  - MXN counterpart excluye `tipo='comision'` (ya se reporta como ganancia)
  - Ganancia FIFO detectada para servicios 2 (Venta USD) y 3 (Venta USDT)
  - Endpoints `operaciones` y `diario_servicio`: excluyen `tipo='ajuste'`
- **`reportes-servicios.php`**:
  - Unificada columna divisa para servicios 1,2,3,5,6 con `tieneBotonDetalle`
  - Summary card eliminado para TODOS los servicios
  - Total footer eliminado de `modalDetalleDia` y `modalDetalleOperacion`
  - Traspasos muestran "Salió de: X → Entró a: Y"
  - "Comisión Amiri" renombrado a "Utilidades Amiri"

### 2. Bugfix: grupo_id único por cobro en préstamos otorgados

- **`api/prestamos-otorgados.php`**: `$g_id` movido dentro del `foreach` en PUT handler para que cada cobro tenga su propio `grupo_id` (antes compartían uno solo por lote)
- Se agregó `validarMesNoCerrado` en POST y PUT

### 3. Unificación de modal de pago en detalle-prestamista.php

- Modal `#modalPagarPrestamo` actualizado con:
  - `#resumenPrestamista` (alerta info con nombre del prestamista)
  - `#alertaDeudaTotalPrestamista` (alerta warning con deuda total)
  - Input monto `#montoPagarFormatted` grande/verde con formato moneda
  - Botón "Confirmar Salida de Efectivo" con spinner de carga
  - Timestamp automático en `fecha_pago`
  - `location.reload()` en éxito (en lugar de redirect a flujo.php)
  - Reset visual al cerrar modal (`hidden.bs.modal`)
- En modo `masivo`: lista de préstamos + **input global único** `#monto_a_pagar_input_multi` (en lugar de inputs individuales por préstamo)
- En modo `individual`: oculta `#grupoMontoGlobal`, muestra input individual
- Handler obsoleto `.input-abono-multiple` eliminado

### Archivos modificados hoy

| Archivo | Cambio |
|---------|--------|
| `api/reportes-servicios.php` | Agrupamiento por operation_key, filtro ajustes/comision, ganancia FIFO |
| `reportes-servicios.php` | tieneBotonDetalle, summary/total eliminados, traspasos, renombre comisión |
| `api/prestamos-otorgados.php` | $g_id dentro del foreach, validarMesNoCerrado |
| `prestamos-otorgados.php` | Quitados alerts resumenDeudor/alertaDeudaTotal del modal (modal simple) |
| `detalle-prestamista.php` | Modal unificado con resumenPrestamista, input global multi-pago, timestamp, location.reload() |

### Pendientes para el lunes

1. **Revisar cálculos de ganancia FIFO** en reportes-servicios — validar que `precio_compra` esté correcto en movimientos de compra de divisa
2. **Verificar multi-pago en prestamistas** — el nuevo flujo con `#monto_a_pagar_input_multi` y distribución automática
3. **Confirmar que prestamos-otorgados.php** funcione correctamente sin los alerts visuales en el modal

---

## 2026-07-06

### 1. Capital Amiri como tipo_cuenta=7 (capital, no caja)

Se revirtió el enfoque anterior: Capital Amiri (ID=206) es `tipo_cuenta=7` (Capital Amiri), no una caja. Aparece en la tarjeta CAPITAL del flujo vía `IN (3,7)` pero NO en SALDOS BANCARIOS.

**DB:**
```sql
UPDATE cuentas SET tipo_cuenta=7 WHERE id=206;
```

**Archivos:**
- `api/capital.php` — Queries de `nombre='Capital Amiri'` → `tipo_cuenta=7`; agregado endpoint `tipo=detalle_mes`
- `api/flujo-resumen.php` — Capital usa `IN (3,7)`, display usa `IN (3,4,5,7)`
- `api/reportes.php` — `nombre='Capital Amiri'` → `tipo_cuenta=7`
- `api/exportar-reporte.php` — `nombre='Capital Amiri'` → `tipo_cuenta=7`
- `_diff.php` — `tipo_cuenta=3` → `tipo_cuenta IN (3,7)`

### 2. Mejoras visuales capital-amiri.php

- Filtro de mes (dropdown) junto al botón "Cerrar Mes" para ver datos históricos
- Iconos de info (`ti-info-circle`) con tooltips en las 5 tarjetas de métricas
- Botón "¿Cómo se calcula?" con modal que muestra tabla detallada de comisiones, desglose de divisas y fórmula del Remanente
- Endpoint `tipo=detalle_mes` en `api/capital.php` que devuelve comisiones + divisas para el modal

### 3. Columnas de % en detalle-cliente.php

- Columna `%` después de "Comisión AMIRI" (badge azul fijo, `bg-primary`)
- Columna `%` después de "Comisión Promotor" (badge amarillo fijo, `bg-warning`)
- Sin condicionales para evitar confusión

### 4. "Importar SQL" eliminado del sidebar

- `src/assets/partials/sidebar.php`: bloque `<li>` de Importar SQL removido

### 5. Corrección de porcentajes Venta #129 (Miguel Cisneros)

**Problema:** La venta #129 de Miguel Cisneros (monto_total=$281,000) tenía porcentajes incorrectos:
- AMIRI: 0.90% ($2,529) → debía ser 1.50% ($4,215)
- Promotor: 0.10% ($281) → debía ser 0.50% ($1,405)
- neto_cliente: $278,190 → $275,380

**Actualizado en deudas_clientes:**
```sql
UPDATE deudas_clientes SET comision_amiri=4215, comision_promotor=1405, monto_neto_cliente=275380 WHERE id=129;
```

### 6. Ajuste de movimientos reales para que coincidan

El movimiento de Venta de Efectivo (grupo 1783360733685) ya estaba registrado con los montos originales. Se actualizaron los montos y saldos:

**Movimientos actualizados (grupo 1783360733685):**
| ID | Concepto | Antes | Después |
|----|----------|-------|---------|
| 2051 | Neto → Caja Miguel (cta 181) | $278,190 | **$275,380** |
| 2052 | Comisión AMIRI → Utilidades (cta 1) | $2,529 | **$4,215** |
| 2053 | Comisión Promotor → Alfonso (cta 180) | $281 | **$1,405** |
| 2054 | Ingreso → Mantela (cta 205) | $281,000 | $281,000 (sin cambio) |

**Saldos ajustados:**
```sql
UPDATE cuentas SET saldo_actual = saldo_actual - 2810.00 WHERE id = 181;  -- Caja Miguel
UPDATE cuentas SET saldo_actual = saldo_actual + 1686.00 WHERE id = 1;    -- Utilidades
UPDATE cuentas SET saldo_actual = saldo_actual + 1124.00 WHERE id = 180;  -- Comisiones Alfonso
```

**Nota:** En el primer intento se crearon ajustes extra (IDs 2068-2070, grupo 1783377708210) y se marcaron los movs como `corregido`, lo que duplicó los ajustes en saldos y ocultó los movs originales. Se corrigió con:
```sql
DELETE FROM movimientos WHERE id IN (2068, 2069, 2070);
UPDATE movimientos SET estado='activo', motivo_correccion=NULL, corregido_en=NULL WHERE grupo_id=1783360733685;
UPDATE cuentas SET saldo_actual = saldo_actual + 2810.00 WHERE id = 181;
UPDATE cuentas SET saldo_actual = saldo_actual - 1686.00 WHERE id = 1;
UPDATE cuentas SET saldo_actual = saldo_actual - 1124.00 WHERE id = 180;
```

### Estado actual
- Diferencia en flujo: **$0.05** (error de redondeo, absorbible)
- Movimientos grupo 1783360733685: 4 movs, `activo`, montos correctos
- deudas_clientes #129: neto=$275,380, com_amiri=$4,215, com_promotor=$1,405
- Saldos: Utilidades=$388,829.94 (+$1,686), Caja Miguel=$1,076,484.80 (−$2,810), Comisiones Alfonso=$2,593.80 (+$1,124), Mantela=$330,186.00 (sin cambio)
- Cliente Miguel Cisneros (id=138) en clientes: porcentaje_amiri=0.80%, porcentaje_promotor=0.20% (aún sin actualizar)

### Pendientes para mañana
1. Ejecutar los 3 SQL de corrección en **producción** (verificar IDs primero)
2. Actualizar `clientes.porcentaje_amiri=1.50` y `porcentaje_promotor=0.50` para Miguel Cisneros (id=138)
3. Confirmar que los porcentajes de contrato de Miguel Cisneros efectivamente son 1.50% + 0.50%
4. Limpiar archivos temporales: `_fix.php`, `_verify.php`, `_check.php`, `_investigar.php`
