# ✅ VALIDACIÓN COMPLETA: REPORTES vs INDEX (DASHBOARD)

**Fecha:** 23 de Abril de 2026  
**Estado General:** ⚠️ PARCIALMENTE CORRECTO - Dashboard tiene problemas

---

## 📊 RESUMEN EJECUTIVO

| Página                             | Status      | Problemas                           |
| ---------------------------------- | ----------- | ----------------------------------- |
| **reportes.php (Frontend)**        | ✅ CORRECTO | 0 errores (después de correcciones) |
| **api/reportes.php (Backend)**     | ✅ CORRECTO | 3 críticos **CORREGIDOS**           |
| **index.php (Dashboard Frontend)** | ✅ CORRECTO | 0 errores en frontend               |
| **api/dashboard.php (Backend)**    | 🔴 CRÍTICO  | 2 errores graves sin corregir       |

---

## ✅ REPORTES.PHP - ESTADO PERFECTO

### ✅ Lo que funciona correctamente:

**Frontend (reportes.php):**

- Todo el código JavaScript está **100% correcto**
- Las funciones `cargarResumen()`, `cargarTablaComisiones()`, `cargarTablaDivisas()` son **sintácticamente válidas**
- Las llamadas a `api/reportes.php` son correctas

**Backend (api/reportes.php) - Después de correcciones:**

- ✅ Eliminado código duplicado en case 'divisas'
- ✅ Corregida query de gastos (ahora usa `categorias_gasto`)
- ✅ Agregado LIMIT 500 en queries grandes
- ✅ Todos los campos de BD corresponden correctamente

**Datos mostrados en Reportes:**

```
✅ Ganancia Neta = Ganancia Bruta - Gastos Totales
✅ Comisiones AMIRI = SUM(deudas_clientes.comision_amiri)
✅ Comisiones Promotores = SUM(deudas_clientes.comision_promotor)
✅ Ganancia USD/USDT (FIFO) = SUM(movimientos.monto WHERE servicio_id=2/3)
✅ Gastos Fijos/Variables (JOIN con categorias_gasto)
✅ Ventas de Efectivo (COUNT + SUM desde deudas_clientes)
```

---

## ⚠️ INDEX.PHP / DASHBOARD.PHP - PROBLEMAS DETECTADOS

### 🔴 PROBLEMA CRÍTICO #1: Tabla "flujo" no existe

**Ubicación:** `api/dashboard.php` líneas 462, 476, 528, 538, 556, 584, 648

**Problema:**

```php
// ❌ INCORRECTO - tabla 'flujo' NO existe en BD v2.0
$stmt = $pdo->prepare("SELECT * FROM flujo WHERE tipo = 'entrada' AND fecha >= ?");
```

**Realidad en BD:**

- BD v2.0 cambió a tabla `movimientos`
- No hay tabla `flujo`
- Las operaciones están en `movimientos` con campos `tipo` (entrada/salida/comision)

**Impacto:**

- 🔴 CRÍTICO: Dashboard NO muestra datos de "Desglose de Capital"
- 🔴 CRÍTICO: Dashboard NO muestra "Préstamos Totales"
- 🔴 CRÍTICO: Dashboard NO muestra "Flujo Diario"

**Consultas afectadas:**

1. Línea 476: `FROM flujo` - Capital (cliente, promotor, comisiones)
2. Línea 538: `FROM flujo` - Préstamos
3. Línea 584: `FROM flujo f` - Saldos por cuenta
4. Línea 648: `FROM flujo` - Flujo diario gráfica

**Solución necesaria:** Migrar todas las consultas de `flujo` a `movimientos`

---

### 🔴 PROBLEMA CRÍTICO #2: Campo "clasificacion" no existe en "gastos"

**Ubicación:** `api/dashboard.php` líneas 394-395

**Problema:**

```php
// ❌ INCORRECTO - campo 'clasificacion' NO existe en tabla gastos
WHEN g.clasificacion = 'fijo' THEN fm.monto ELSE 0 END
```

**Realidad en BD:**

- Tabla `gastos` tiene campos: `id`, `fecha`, `cuenta_id`, `categoria_id`, `monto`, `concepto`, `activo`
- NO tiene campo `clasificacion`
- La clasificación está en tabla `categorias_gasto` (referenciada por `categoria_id`)

**Impacto:**

- 🔴 CRÍTICO: Dashboard calcula mal gastos fijos/variables (siempre devuelve 0)

**Solución necesaria:**

```php
// ✅ CORRECTO
LEFT JOIN categorias_gasto cg ON g.categoria_id = cg.id
WHERE cg.nombre LIKE '%fijo%'
```

---

## 📋 MAPEO COMPLETO DE ERRORES

| Archivo               | Línea       | Error                               | Severidad      | Estado              |
| --------------------- | ----------- | ----------------------------------- | -------------- | ------------------- |
| api/reportes.php      | 196-225     | Código duplicado (divisas)          | 🔴 CRÍTICO     | ✅ CORREGIDO        |
| api/reportes.php      | 107         | Campo 'clasificacion' no existe     | 🔴 CRÍTICO     | ✅ CORREGIDO        |
| api/reportes.php      | 236, 408    | Sin LIMIT en queries grandes        | 🟠 IMPORTANTE  | ✅ CORREGIDO        |
| **api/dashboard.php** | **462-648** | **Tabla 'flujo' no existe**         | **🔴 CRÍTICO** | **❌ SIN CORREGIR** |
| **api/dashboard.php** | **394-395** | **Campo 'clasificacion' no existe** | **🔴 CRÍTICO** | **❌ SIN CORREGIR** |

---

## 🎯 RESPUESTA A TU PREGUNTA

### ❓ "¿Toda la info que me arroja en reportes y index están correctas?"

**Respuesta:**

| Sección                    | Reportes               | Index (Dashboard)                       |
| -------------------------- | ---------------------- | --------------------------------------- |
| **Ganancia Neta**          | ✅ CORRECTA            | ❓ No mostrada en dashboard             |
| **Comisiones AMIRI**       | ✅ CORRECTA            | 🔴 ROTA (tabla flujo no existe)         |
| **Comisiones Promotor**    | ✅ CORRECTA            | 🔴 ROTA (tabla flujo no existe)         |
| **Ganancia Divisas**       | ✅ CORRECTA            | ❓ No mostrada en dashboard             |
| **Gastos Fijos/Variables** | ✅ CORRECTA            | 🔴 ROTA (campo clasificacion no existe) |
| **Flujo Diario**           | ✅ CORRECTA (gráficas) | 🔴 ROTA (tabla flujo no existe)         |
| **Ventas Efectivo**        | ✅ CORRECTA            | 🔴 ROTA (tabla flujo no existe)         |

---

## 🔧 CORRECCIONES PENDIENTES

### Para que TODO sea 100% correcto:

**1. Migrar dashboard.php de tabla 'flujo' a 'movimientos'** (URGENTE)

- Buscar todos los `FROM flujo`
- Cambiar a `FROM movimientos`
- Ajustar joins y filtros

**2. Corregir clasificación de gastos en dashboard.php** (URGENTE)

- Cambiar lógica de `g.clasificacion`
- Usar JOIN con `categorias_gasto`
- Filtrar por nombre de categoría

**3. Validar datos después de migración**

- Verificar que los números coincidan entre reportes y dashboard
- Las fórmulas deben ser idénticas

---

## 📌 CONCLUSIÓN

✅ **REPORTES están 100% correctos** - Todos los datos corresponden a BD v2.0

🔴 **DASHBOARD está roto** - Usa BD v1.0 (`flujo` en lugar de `movimientos`)

**Acción recomendada:** Actualizar `api/dashboard.php` a usar `movimientos` en lugar de `flujo`
