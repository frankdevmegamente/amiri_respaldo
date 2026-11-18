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

---

## 2026-07-07 — Optimización de rendimiento

### 1. Índices compuestos (Prioridad 1)
Se crearon 3 índices para cubrir las consultas más frecuentes sin full table scan:

| Tabla | Índice | Columnas |
|-------|--------|----------|
| `movimientos` | `idx_perf_divisas` | `(servicio_id, tipo, cuenta_destino_id, fecha, activo)` |
| `deudas_clientes` | `idx_fecha_activo` | `(fecha, activo)` |
| `gastos` | `idx_fecha_activo` | `(fecha, activo)` |

### 2. Singleton + conexión persistente en `getPDO()` (Prioridad 2)
- `src/config/db.php`: `getPDO()` ahora usa `static $pdo = null` (singleton por request) + `PDO::ATTR_PERSISTENT => true` (conexión reusable entre requests)
- `src/config/helpers.php`: Eliminado el `getPDO()` duplicado (código muerto — nunca se ejecutaba porque `db.php` registraba su versión en tiempo de compilación)

### 3. Refactorizar N+1 en inicializar (Prioridad 3)

### 4. Endpoint combinado full_dashboard (Prioridad 4)
Se creó `api/reportes.php?tipo=full_dashboard` que devuelve en **1 sola respuesta** todo lo que antes requería **10 llamadas AJAX**:
- resumen, comisiones_diarias, comisiones_por_promotor, ventas_diarias, divisas_diarias
- comisiones (tabla), divisas (tabla + resumen), ventas_efectivo (tabla)
- lista_promotores (dropdown), capital_amiri (resumen)

Frontend refactorizado: cada función (`cargarResumen`, `cargarGraficaX`, etc.) acepta un parámetro `data` opcional. Si se pasa, lo renderiza directo; si no, hace su propia llamada AJAX individual (para recargas por filtro).
El loop `foreach ($meses as $mes)` ejecutaba **3 SELECT + 1 INSERT por mes** (3N queries). Con 24 meses eran ~72 SELECTs.

**Solución:** 3 queries batch con `GROUP BY DATE_FORMAT(fecha, '%Y-%m')` que precargan los datos de todos los meses en arrays asociativos. El loop solo hace lookups en memoria.

### 5. Eliminar llamado duplicado a cierres (Prioridad 5)
`capital-amiri.php` hacía **3 llamadas AJAX** a `api/capital.php?tipo=cierres`:
- `cargarCierres()` (DataTable)
- `cargarHistorialCortes()` (tabla + chart.js)
- `cargarGrafica()` (chart.js línea)

**Solución:** Se creó `_cierresData` (caché JS), `_renderHistorialCortes(data)` y `_renderGrafica(data)` para renderizado directo, y `cargarCierres(data)` que acepta datos opcionales (evita AJAX si se proveen). Una sola función `cargarCierresYGraficas()` hace **1 sola llamada** y distribuye los datos a los 3 consumidores.

### Archivos modificados

| Archivo | Cambio |
|---------|--------|
| `src/migrations/008_add_performance_indexes.sql` | Migración con 3 índices |
| `scripts/migrate-indexes.php` | Script para aplicar migración |
| `src/config/db.php` | Singleton + persistencia en getPDO() |
| `src/config/helpers.php` | Eliminado getPDO() duplicado (dead code) |
| `api/capital.php` | Refactorizado N+1 en inicializar: 3 batch queries en vez de 3N |
| `api/reportes.php` | Nuevo endpoint `full_dashboard` — combina 10 endpoints en 1 |
| `reportes.php` | Frontend refactorizado: `cargarTodo()` usa full_dashboard, funciones aceptan data opcional |
| `capital-amiri.php` | Caché JS para cierres: 1 llamada en vez de 3 |

---

## 2026-07-23

### 1. Retiro de Capital: selector de cuenta

**Problema:** El retiro de capital solo descontaba de Capital Amiri (tipo_cuenta=7). El usuario quería que también se descontara de una cuenta seleccionada (como en gastos).

**Solución aplicada:**

**`capital-amiri.php`:**
- Nuevo `<select id="retiroCuenta">` en el modal de retiro
- JS carga cuentas desde `api/cuentas.php?solo_gastos=1&con_saldo=1&tipo_moneda=MXN` (tipo 1 y 2, solo MXN, auto-selecciona "efectivo")
- Validación client-side: verifica saldo de Capital Amiri Y saldo de la cuenta seleccionada
- POST ahora envía `cuenta_id`

**`api/capital.php`:**
- Nuevo bloque después del descuento de Capital Amiri: si `cuenta_id` es diferente a la cuenta Capital Amiri, descuenta también de esa cuenta (movimiento salida + historial_saldos)

**Comportamiento nuevo:**
```
Retiro de $1000 desde cuenta "Banco X":
  → Cuenta "Banco X": saldo -= $1000 (movimiento salida)
  → Capital Amiri (tipo_cuenta=7): saldo -= $1000 (movimiento salida)
```

### 2. Fix ecuación contable (flujo-resumen.php)

**Problema:** La ecuación en `api/flujo-resumen.php` restaba `$totalRetiros` para balancear. Con el nuevo comportamiento (retiro afecta ambas cuentas), `$totalRetiros` se restaba doblemente: ya estaba reflejado en la baja de `totalSaldos` (cuenta seleccionada) Y `totalCapital` (Capital Amiri).

**Solución:** Eliminado `- $totalRetiros` de ambas ecuaciones contables:
- Línea ~146: `$diferencia = totalSaldos - totalCapital - totalObligaciones` (sin retiros)
- Línea ~539: `$diferencia = totalSaldos - (totalCapital + $totalPrestamos)` (sin retiros)
- Eliminadas las queries y explicaciones de retiros de ambas ecuaciones

### 3. Columna "Cuenta" en tabla de retiros

**Problema:** La tabla de retiros en capital-amiri.php no mostraba de qué cuenta se descontó.

**Solución:**
- `ALTER TABLE retiros_capital ADD COLUMN cuenta_id INT(11) DEFAULT NULL`
- `api/capital.php`: INSERT guarda `cuenta_id`; query de retiros hace `LEFT JOIN cuentas` para traer `cuenta_nombre`
- `capital-amiri.php`: nueva columna "Cuenta" en thead y DataTable

### Archivos modificados hoy

| Archivo | Cambio |
|---------|--------|
| `capital-amiri.php` | Select de cuenta en modal retiro, JS carga cuentas MXN, columna Cuenta en tabla retiros |
| `api/capital.php` | Guarda cuenta_id en retiro, descuenta de cuenta seleccionada, JOIN cuentas en query retiros |
| `api/flujo-resumen.php` | Eliminado `- totalRetiros` de ambas ecuaciones contables |

### Pendientes para mañana

1. ~~**Compra de Efectivo en flujo.php**~~ — **COMPLETADO 2026-07-24**. Cambio de clientes a proveedores:
   - Migración `009_add_cuenta_mxn_proveedores.sql`: agrega `cuenta_mxn_id` a tabla `proveedores` + crea cuentas MXN para proveedores existentes
   - `api/proveedores.php`: auto-crear cuenta MXN al crear proveedor, JOIN con `cuentas` para traer `saldo_mxn`, incluir `cuenta_mxn_id` en DELETE
   - `flujo.php`: Compra de Efectivo ahora muestra `#proveedorGroup` en vez de `#clienteGroup`, carga solo proveedores (no clientes), bug fix servicios_ids incluye 20
   - `api/flujo.php`: resolución de `cuenta_mxn_id` del proveedor, auto-crear cuenta MXN si no existe, créditos incrementan saldo del proveedor en vez de `deudas_clientes`, movimientos usan `proveedor_id`
   - `proveedores.php`: columna Adeudo muestra MXN junto con USD/USDT, botón "Pagar MXN" con handler `.pagar-mxn`
   - `api/proveedores-pagos.php`: soporte para `moneda_pago = 'MXN'`, usa `cuenta_mxn_id`, solo reduce `saldo_actual` (sin `valor_mxn`)
   - `api/flujo-resumen.php`: query de obligaciones incluye `cuenta_mxn_id` de proveedores en vistas TODAS y MXN
   - `src/services/MovimientoCorreccionService.php`: reversión servicio 20 reduce saldo de cuenta MXN del proveedor en vez de `deudas_clientes`

---

## 2026-08-04 — Restauración BD y sincronización de esquema

### Problema
La BD local (`amiri_reparada`) fue restaurada desde un dump de producción y **perdió los cambios de esquema que solo existían en la BD local anterior** (2026-07-23/24). Esto causó:
- **Error 500** en `api/flujo-resumen.php?tipo=diferencia` y `?tipo=proveedor` (columna `proveedores.cuenta_mxn_id` inexistente).
- **"DataTables warning: table id=tablaRetiros - Ajax error"** en `capital-amiri.php` (columna `retiros_capital.cuenta_id` inexistente).

### Solución aplicada
Se aplicó un fix completo en la BD local (documentado y verificado), con respaldo previo:

1. **Respaldo**: `backups/amiri_reparada_pre_fix_2026-08-04_*.sql` (~1.27 MB).
2. **Migración 009** (`proveedores.cuenta_mxn_id`): al ejecutarse devolvió "Duplicate column/key" → ya estaba aplicada. Verificado: columna `INT(11) DEFAULT NULL` + índice `fk_proveedor_cuenta_mxn` presentes.
3. **Cuentas MXN de proveedores**: creadas y vinculadas **8 cuentas** `Caja Proveedor MXN - <nombre>` (ids **217–224**) a los proveedores activos.
4. **`retiros_capital.cuenta_id`**: el ALTER de 2026-07-23 (BITACORA.md:319) **no tenía archivo de migración**; se creó **`src/migrations/010_add_cuenta_id_retiros.sql`** y se probó en una BD temporal (`cuenta_id int(11) DEFAULT NULL` se agrega correctamente). Aplicado a local.
5. **Migración 008** (índices de rendimiento): verificada ya aplicada (`idx_perf_divisas`, `idx_fecha_activo` en deudas_clientes y gastos).
6. **Verificación**: 18 consultas de flujo-resumen (diferencia/proveedor), capital (retiros/resumen) y proveedores ejecutadas vía PHP PDO → TODO OK. HTTP local: `api/capital.php?tipo=retiros` = 200; `api/flujo-resumen.php?tipo=diferencia|proveedor&moneda=TODAS` = 401 (auth requerida, ya sin 500).

### Estado en producción
- `retiros_capital.cuenta_id` **falta** → aplicar la migración 010. Si flujo-resumen también falla, aplicar la 009.
- El resto de requisitos de capital (tablas 006, `tipos_cuenta` id 7, cuenta Capital Amiri 206, tabla `configuracion` 007, `es_caja_empresa`, `historial_saldos`) ya existen en el dump de producción.

### Archivos involucrados

| Archivo | Cambio |
|---------|--------|
| `src/migrations/010_add_cuenta_id_retiros.sql` | **NUEVO** — migración para `retiros_capital.cuenta_id` |
| `src/migrations/009_add_cuenta_mxn_proveedores.sql` | Verificada aplicada en local; pendiente en producción si flujo-resumen falla |
| `src/migrations/008_add_performance_indexes.sql` | Verificada aplicada (índices presentes) |
| `backups/amiri_reparada_pre_fix_2026-08-04_*.sql` | Respaldo local previo al fix |

### Pendientes para mañana
1. **Aplicar en producción** la migración 010: `ALTER TABLE retiros_capital ADD COLUMN cuenta_id INT(11) DEFAULT NULL`.
2. Si `api/flujo-resumen.php` falla en producción, aplicar también la migración 009 (columnas + cuentas MXN de proveedores).
3. Verificar en producción: tabla `tablaRetiros` en `capital-amiri.php` y `api/flujo-resumen.php?tipo=diferencia|proveedor&moneda=TODAS`.

---

## 2026-10-08 — Bug historial_saldos en Cambio de Flujo / Retiro + auditoría Santas 226

### Bug encontrado
- `api/flujo.php` (Cambio de Flujo y Retiro de Efectivo) insertaba 1 `movimientos` que mueve 2 cuentas, pero llamaba `actualizarSaldoCuenta()` **sin `movimiento_id`** → actualizaba `cuentas.saldo_actual` pero **no registraba `historial_saldos`**.
- Efecto: tarjeta SALDOS BANCARIOS (lee `saldo_actual` directo) vs columna Saldo remanente en `movimientos.php` (lee `historial_saldos` + recálculo JS) no cuadraban.

### Fix aplicado (código)
- `api/flujo.php` Cambio de Flujo (~línea 391): `$movId = $pdo->lastInsertId()` y se pasa a ambas cuentas (origen `salida`, destino `entrada`).
- `api/flujo.php` Retiro de Efectivo (~línea 455): igual para `cuenta_bancaria_id` y `caja_efectivo_id`.
- `flujo.php` input cuentas Venta/Compra Efectivo: `cargarCuentasMXN(true)` ahora pide `solo_gastos=1` y filtra tipos 1+2 (bancarias + cajas empresa); labels aclarados.

### Auditoría Santas (id 226, tipo 2, MXN, creada 2026-08-12)
- `saldo_actual = $13,482.44`. 91 movs (89 activos: 36 entradas $3,764,405.44 − 53 salidas $3,762,923.00 = neto +$1,482.44) + 2 anulados 09-07 (264k/276k, excluidos).
- `historial_saldos`: estaba en 10/89 (79 faltantes + 3 corruptos: movs 3684/3696/3697). **Reconstruido a 89/89**: inicial $12,000 → final $13,482.44 OK.
- **Origen de los $12,000**: saldo inicial cargado a mano al crear la cuenta (12/08), sin movimiento de entrada. Evidencia: primer historial `saldo_anterior=12000`, no existe entrada de $12,000 (único mov de $12,000 es salida 4412 del 01/10), auditoría solo tiene el INSERT de la cuenta, sin UPDATEs.
- Primeros movs: 2952/2954 (12/08, 37→226, $4,264 + $62,500), 2975 (226→2 Retiro $62,500)...

### PENDIENTE corroborar con banco real (decide el cierre)
- Si banco dice ~$13,482 → Opción 1: crear movimiento de apertura (entrada $12,000 a Santas, fecha 12/08/2026) + historial. Cierra el caso.
- Si banco dice ~$1,482 → Opción 2: ajuste auditado −$12,000 (Santas → cuenta de correcciones por definir, fecha por definir). Saldo queda $1,482.44.
- Pendiente que el usuario confirme número del banco + (opción 2) cuenta destino y fecha.
- También pendiente: `movimientos.php:528-550` prioriza `saldosCorridos` (recalculado) sobre `historial_saldos`; con filtro de fechas el remanente se desplaza. Evaluar invertir prioridad a historial primero.

---

## 2026-10-08 (cont.) — Origen real de los $12k Santas 226: reversos corruptos 3696/3697

### Hipótesis anterior (descartada)
- Se había anotado "saldo inicial cargado a mano el 12/08". Investigación completa demuestra que NO fue eso.

### Evidencia (solo lectura, BD local `amiri_reparada`, sin modificar nada)
- `cuentas.226` creada 12/08/2026 12:53:48 (`auditoria.id=1179`, usuario 2). `datos_nuevos` sin campo de saldo → default `0.00`. Único registro de Santas en auditoría (1 INSERT, 0 UPDATEs sobre 226).
- IDs `movimientos` 2948–2990 continuos (sin borrados físicos). Ninguna descripción de "saldo inicial / apertura / ajuste de saldo". Ninguna entrada de $12,000 a Santas; único mov de $12,000 es salida 4412 (01/10, 226→37).
- Par anulado del 09-07 (origen ya descuadrado entre sí por $12,000):
  - `movs.3694`: 37→226, +$264,000, `anulado`.
  - `movs.3695`: 226→2, +$276,000, `anulado`.
- Reversos generados corruptos con montos negativos (estado `activo`, sí suman):
  - `movs.3696`: 2→226, **−$276,000** (REVERSO anulación grupo ...628801).
  - `movs.3697`: 226→37, **−$264,000** (REVERSO anulación grupo ...015126).

### Matemática que prueba el origen
- Efecto neto de 3696+3697 sobre Santas: entrada −$276,000 y salida −$264,000 → **−$276,000 − (−$264,000) = −$12,000**.
- Sumas actuales Santas (activos): entradas $3,764,405.44 − salidas $3,762,923.00 = **+$1,482.44**. Sin esos 2 reversos el neto sería $1,482.44 + $276,000 − $264,000 = **$13,482.44** = `saldo_actual` actual.
- Conclusión: `saldo_actual ($13,482.44)` conservó el valor como si la anulación no hubiera restado, pero la suma de movimientos sí absorbió los −$12k. Saldo real conciliado con banco: **$1,482.44**.

### Decisión del usuario
- NO registrar ningún ajuste ni movimiento de corrección (el dinero nunca existió, no debe quedar rastro de salida). Estado queda tal cual: `saldo_actual=13482.44` (falso) vs real $1,482.44, causa identificada.
- Cierre futuro (solo si el usuario lo autoriza): corrección quirúrgica sobre reversos 3696/3697 (anular/monto cero + recalibrar `saldo_actual` a $1,482.44 con su `historial_saldos` de corrección). Sin fecha definida.

---

## 2026-10-08 (cont. 2) — Anulados por defecto + bug remanente en movimientos.php

### Cambio aplicado (único, a petición del usuario)
- `movimientos.php:321`: checkbox `#filtroAnulados` ahora nace con `checked` → al cargar pide `incluir_anulados=1` y muestra anulados/corregidos. Nada más tocado.

### Bug reportado por el usuario (3 capturas, cuenta Santas 226)
- Img 1 (con anulados, recién cargado): primera fila (07/10 Retiro −$200k) con remanente **−$10,517.56**.
- Img 2 (sin anulados): misma fila con **$13,482.44**.
- Img 3 (re-marca anulados): sigue en **$13,482.44** (ya no −$10k). Img 1 e img 3 deberían coincidir y no lo hacen → condición de carrera, no datos.

### Causa raíz (doble, `movimientos.php`)
1. **Carrera async (`:906-916` dataSrc + `:649-661` cargarResumenCuenta):** `dataSrc` corre `calcularResumenCuenta(rows)` inmediato con `cuentaInfo=null` → `actual=null` → `inicial=null` (`:608-609`) → `bal=0` (`:616`) → corrido neto-puro-desde-cero. Medido en BD: neto con anulados = **−$10,517.56** (coincide exacto con img 1). Al terminar `.load()` llega `cuentaInfo` (`api/cuentas.php?id=226`), recalcula con `inicial = 13482.44 − neto` y repinta → final anclado a `saldo_actual` por fórmula (`inicial+neto=actual`, tautología). Img 3 ya tenía `cuentaInfo` cacheado → por eso "se queda" en $13k.
2. **Neto contaminado (`:596-604`):** con anulados conviven original anulado (+$264k 3694 / +$276k 3695) + reverso (−$276k 3696 / −$264k 3697, signo negativo + swap = doble inversión). Suma `parseFloat(monto)` con signo → neto activos **+$1,482.44** (inicial fantasma $12,000) vs neto con anulados **−$10,517.56** (inicial $23,999). El final siempre "cuadra" por construcción; los intermedios no.
- Evidencia extra: `historial_saldos` 3696/226 `entrada −$276,000` → nuevo **−$90,872.56** (saldo negativo imposible); 3697/226 `salida −$264,000` → nuevo $173,127.44.

### Blast radius medido (solo lectura, `saldo_actual` vs neto movs activos, tipos 1+2)
- Tarpen 34: −$3,276,359 | Caja 2: −$850,073 | Albo 198: +$526,661 | Hernández 229: −$312,122 | Makalan 32: +$276,693 | Azules 197: +$236,440 | Tornero 230: −$181,622 | Mantela 37: +$62,200 | Scotias/Banregios/Banamex/Bajios: ±$12k–$32k | **Santas 226: +$12,000** | Rojas 52: −$10,643. Nota: `neto_movs` ingenuo (arrastra reversos negativos; en deuda/proveedor la semántica difiere), pero en bancarias/cajas el dif debería ser ~$0. Problema sistémico, no solo Santas.
- Por qué la contabilidad les cuadra igual: todo flujo (`flujo-resumen.php:51`, dashboard, tarjetas) suma `saldo_actual` en todos lados → autoconsistente flujo-contra-flujo aunque no contra bancos. Restricción del usuario: NO tocar saldos existentes para no descuadrarles lo que tienen.

### Plan acordado para mañana (nada ejecutado)
- **Opción B (cero-riesgo, recomendada):** no actualizar ningún `saldo_actual` (ni Santas). Solo código hacia adelante: (1) reversos con `abs()` en `MovimientoCorreccionService.php` (~121/~340/~680/~1201); (2) migración 012 `CHECK(monto>=0)`; (3) anulación atómica por `grupo_id`; (4) remanente: priorizar `historial_saldos`, `abs(monto)`, excluir no-activos del neto, `inicial` contra historial completo + bandera `cuentaLista` (no pintar hasta tener cuenta+dato, adiós al −$10k de primera carga). (5) `integridad.php` solo reporta, no corrige.
- **Opción A (solo si autorizan Santas):** B + quirúrgico 226 (monto `ABS` en 3696/3697, corregir sus 4 historiales, `saldo_actual`→$1,482.44 + historial de corrección, transacción + auditoría). Toca solo 226 (+37/+2 en esos historiales); resto de cuentas intactas; agregado tipo 1+2 baja $8,096,511.59→$8,084,511.59 (−$12k = fantasma removido).
- Pendiente decisión del usuario: A vs B. Pendiente también: qué debe hacer el botón Limpiar de movimientos (¿mantener anulados marcados?).

---

## 2026-10-20 - Concepto en prestamos (BD nueva de produccion) + ajustes en Generadores

### Contexto
- El usuario importo el dump nuevo de PRODUCCION como BD local (`amiri_reparada`), por tener registros nuevos.
- Ese dump traia el esquema viejo: `prestamos_otorgados` SIN columna `concepto`.
- Sintoma: el modal de "Nuevo Prestamo" si tiene el campo Concepto, pero al guardar no aparecia ni en la tabla hija, ni en `detalle-deudor.php`, ni en Flujo (salia "Sin concepto").

### Causa raiz
- `api/prestamos-otorgados.php` arma el INSERT dinamicamente con `SHOW COLUMNS` + `$has('concepto')`. Sin la columna, el INSERT la omite en silencio -> el concepto NUNCA se guardaba.
- El GET usa `SELECT p.*`, asi que sin columna el JSON tampoco la traia -> UI caia al fallback "Sin concepto".
- El codigo PHP estaba bien; faltaba la columna en la BD. La migracion `src/migrations/011_add_concepto_prestamos_otorgados.sql` ya existia pero nunca se aplico en local.

### Fix aplicado (local `amiri_reparada`)
1. `ALTER TABLE prestamos_otorgados ADD COLUMN concepto VARCHAR(255) NULL DEFAULT NULL AFTER persona_nombre;` (aplicada 2 veces: antes y despues del import del dump).
2. Backfill del historico: 1 solo prestamo con concepto recuperable (id 67, "Prestamo Para Arquitecto") extraido de `prestamos_otorgados.descripcion` (`Prestamo a <nombre> - <concepto>`). Los otros 62 prestamos tienen `descripcion` vacia -> no hay de donde recuperar.
3. Verificado: `SELECT p.*` ya devuelve `concepto`; INSERT de prueba con concepto OK (rollback, 0 filas basura).

### Ajustes en los Generadores (frontend, sin cambios de BD)
- `generador-depositos.php`: oculta la columna CLABE de la tabla; el ticket imprime `CUENTA: <cuenta> - <BANCO>`. El campo CLABE sigue en el modal (no se borra).
- `generador-transferencias.php`: oculta la columna Cuenta (queda solo CLABE); el campo Cuenta del modal pasa a `hidden` para no perder el valor al editar; CLABE ahora es `required`; el ticket imprime `CLABE: <clabe> - <BANCO>`.
- Sin cambios en `api/directorio-depositos.php` (la tabla `directorio_depositos` ya existe con `estado`).

### Estado de la BD nueva (verificado)
- `directorio_depositos`: 113 filas (13 activo / 100 inactivo), 109 con CLABE, 113 con cuenta.
- `prestamos_otorgados`: 67 filas, todas con `deudor_id`; `deudores`: 24 filas.
- ESTADO DE PRESTAMOS: pendiente activo=1: 17 | pendiente activo=0: 4 | parcial: 2 | pagado: 44.

### Pendiente: PRODUCCION (no se ha subido nada)
Paquete listo en `deploy-pendiente-2026-10-20-concepto-y-generadores/` (con `LEEME_SUBIR.txt`):
- SQL: `ALTER TABLE prestamos_otorgados ADD COLUMN concepto VARCHAR(255) NULL DEFAULT NULL AFTER persona_nombre;`
- FTP: `prestamos-otorgados.php`, `detalle-deudor.php`, `generador-depositos.php`, `generador-transferencias.php`, `api/prestamos-otorgados.php`, `api/deudores.php` (nuevo), `api/directorio-depositos.php` (nuevo), `src/assets/js/anular.js` (nuevo), `src/assets/partials/modal-anular.php` (nuevo).
- Ojo: `directorio_depositos` en produccion debe existir (el dump viene de ahi); si no, las paginas la crean solas (CREATE TABLE IF NOT EXISTS).