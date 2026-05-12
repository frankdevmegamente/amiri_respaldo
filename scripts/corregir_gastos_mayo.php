<?php
/**
 * Corrige gastos específicos de mayo 2026 que no descontaron de Utilidades AMIRI
 * 
 * Gastos a corregir:
 *   #24 - CFE Reconexión    07/05 - $500   - Cuenta #2
 *   #25 - Gas Kia           08/05 - $500   - Cuenta #2
 *   #26 - Operativo         08/05 - $3,800 - Cuenta #2
 *   #28 - Comisiones Rojas  08/05 - $1,880 - Cuenta #34
 *
 * Total: $6,680
 */

require_once __DIR__ . '/../src/config/db.php';

$pdo = getPDO();

echo "=== CORRECCIÓN DE GASTOS MAYO 2026 ===\n\n";

// IDs de gastos específicos a corregir
$gastos_ids = [24, 25, 26, 28];

// Obtener cuenta de Capital (Utilidades AMIRI)
$stmtCap = $pdo->query("SELECT id, saldo_actual FROM cuentas WHERE tipo_cuenta = 3 AND activo = 1 LIMIT 1");
$capital = $stmtCap->fetch(PDO::FETCH_ASSOC);

if (!$capital) {
    echo "ERROR: No se encontró cuenta de Capital (tipo_cuenta = 3)\n";
    exit(1);
}

$capital_id = (int)$capital['id'];
echo "Cuenta de Capital: ID #{$capital['id']}, Saldo actual: $" . number_format($capital['saldo_actual'], 2) . "\n\n";

$corregidos = 0;
$omitidos = 0;
$errores = 0;

foreach ($gastos_ids as $gasto_id) {
    echo "--- Gasto #{$gasto_id} ---\n";

    // Obtener datos del gasto
    $stmt = $pdo->prepare("SELECT * FROM gastos WHERE id = ?");
    $stmt->execute([$gasto_id]);
    $gasto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$gasto) {
        echo "  No encontrado\n\n";
        $omitidos++;
        continue;
    }

    if ((int)$gasto['activo'] !== 1) {
        echo "  Inactivo (anulado), se omite\n\n";
        $omitidos++;
        continue;
    }

    // Verificar si ya tiene descuento a Capital
    $check = $pdo->prepare("SELECT COUNT(*) FROM movimientos WHERE gasto_id = ? AND cuenta_origen_id = ? AND activo = 1");
    $check->execute([$gasto_id, $capital_id]);
    $ya_tiene = (int)$check->fetchColumn();

    if ($ya_tiene > 0) {
        echo "  Ya tiene descuento a Capital, se omite\n\n";
        $omitidos++;
        continue;
    }

    $monto = (float)$gasto['monto'];
    $fecha = $gasto['fecha'];
    $cuenta_id = (int)$gasto['cuenta_id'];

    // No descontar de Capital si el gasto ya es de la cuenta Capital
    if ($cuenta_id === $capital_id) {
        echo "  El gasto ya es de la cuenta Capital, no aplica doble descuento\n\n";
        $omitidos++;
        continue;
    }

    try {
        $pdo->beginTransaction();

        // Saldo anterior de Capital
        $stmtSaldo = $pdo->prepare("SELECT saldo_actual FROM cuentas WHERE id = ? FOR UPDATE");
        $stmtSaldo->execute([$capital_id]);
        $saldoAnterior = (float)$stmtSaldo->fetchColumn();
        $saldoNuevo = round($saldoAnterior - $monto, 2);

        // Generar grupo_id único
        $grupo_id = round(microtime(true) * 1000);

        // Obtener categoría del gasto para la descripción
        $stmtCat = $pdo->prepare("SELECT nombre FROM categorias_gasto WHERE id = ?");
        $stmtCat->execute([$gasto['categoria_id']]);
        $cat = $stmtCat->fetchColumn();

        $descripcion = "Gasto (desde Comisiones AMIRI): {$cat} - " . ($gasto['concepto'] ?? 'Sin concepto');

        // Insertar movimiento contra Capital
        $stmtMov = $pdo->prepare("
            INSERT INTO movimientos
            (grupo_id, fecha, tipo, monto, moneda, descripcion, cuenta_origen_id, cuenta_destino_id, gasto_id, servicio_id, activo)
            VALUES (?, ?, 'salida', ?, 'MXN', ?, ?, NULL, ?, 12, 1)
        ");
        $stmtMov->execute([
            $grupo_id,
            $fecha,
            $monto,
            $descripcion,
            $capital_id,
            $gasto_id
        ]);
        $movimiento_id = $pdo->lastInsertId();

        // Actualizar saldo de Capital
        $pdo->prepare("UPDATE cuentas SET saldo_actual = ? WHERE id = ?")
            ->execute([$saldoNuevo, $capital_id]);

        // Insertar historial
        $pdo->prepare("
            INSERT INTO historial_saldos
            (cuenta_id, movimiento_id, tipo, monto, saldo_anterior, saldo_nuevo)
            VALUES (?, ?, 'salida', ?, ?, ?)
        ")->execute([
            $capital_id,
            $movimiento_id,
            $monto,
            $saldoAnterior,
            $saldoNuevo
        ]);

        $pdo->commit();

        echo "  Corregido: \${$monto} - {$descripcion}\n";
        echo "  Capital: $" . number_format($saldoAnterior, 2) . " → $" . number_format($saldoNuevo, 2) . "\n\n";
        $corregidos++;

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "  ERROR: " . $e->getMessage() . "\n\n";
        $errores++;
    }
}

echo "=== RESUMEN ===\n";
echo "Corregidos: {$corregidos}\n";
echo "Omitidos: {$omitidos}\n";
echo "Errores: {$errores}\n";
echo "==============\n";
