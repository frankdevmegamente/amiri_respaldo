<?php
/**
 * FIX faltante $-989,630.70 MXN — compras a crédito USDT anuladas el 2026-09-18
 *
 * CAUSA (verificada en amiri_reparada):
 *  - Grupos anulados: 1789752654329 (Hyperion 29,021.43 USDT TC 17.00)
 *    y 1789752722710 (Binance 29,021.43 USDT TC 17.10), auditoria ids 1480 y 1482.
 *  - Crear compra a crédito (api/flujo-divisas.php:300-316) hace:
 *      INSERT movimiento tipo='salida' destino=proveedor
 *      + actualizarSaldoCuenta(proveedor, cant, 'entrada')  -> SUBE adeudo (correcto)
 *      + valor_mxn += cant*TC
 *  - Revertir (MovimientoCorreccionService::revertirSaldos, línea ~1020) hace para
 *    destino con tipo original 'salida': actualizarSaldoCuenta(proveedor, cant, 'entrada')
 *    -> VUELVE A SUBIR en vez de bajar. Además nunca revierte valor_mxn.
 *  - Resultado: cta 44 Binance 0 -> 29,021.43 (compra) -> 58,042.86 (reverso malo),
 *    valor_mxn 0 -> 496,266.45 (sin revertir). Igual cta 200 Hyperion 0 -> 58,042.86,
 *    valor 493,364.31. Suma = 989,630.76 = la diferencia TODAS.
 *  - Pagos a proveedor (api/proveedores-pagos.php:148-169) hacen UPDATE directo
 *    sin historial_saldos, por eso el historial solo muestra entradas.
 *  - Inventario y wallet 51 quedaron bien (lotes 94/95 activos=0, wallet en 0).
 *
 * CORRECCIÓN (solo datos, no toca movimientos históricos):
 *  - cta 44: saldo 58,042.86 -> 0.00, valor_mxn 496,266.45 -> 0.00
 *  - cta 200: saldo 58,042.86 -> 0.00, valor_mxn 493,364.31 -> 0.00
 *  - dry-run verificado: proveedores 989,630.76 -> 0.00, diferencia -989,630.70 -> 0.06
 *    (0.06 <= 0.15 lo absorbe flujo-resumen como 0.00).
 *
 * USO:
 *  1. Backup completo de la BD antes (mysqldump).
 *  2. php scripts/fix-faltante-989k-proveedores.php --apply
 *     Sin --apply solo hace dry-run (SELECT, no escribe).
 */

require __DIR__ . '/../src/config/db.php';

$apply = in_array('--apply', $argv ?? []);
$pdo = getPDO();
$f = function ($n) { return number_format((float)$n, 2); };

$esperado = [
    44  => ['saldo' => 58042.86, 'valor' => 496266.45, 'nombre' => 'Binance'],
    200 => ['saldo' => 58042.86, 'valor' => 493364.31, 'nombre' => 'Hyperion'],
];

echo "=== FIX faltante 989k " . ($apply ? "[APPLY]" : "[DRY-RUN]") . " ===\n\n";

// 1. Lectura actual
$actual = [];
foreach ($esperado as $cid => $e) {
    $row = $pdo->query("SELECT id, nombre, saldo_actual, valor_mxn FROM cuentas WHERE id = $cid")->fetch(PDO::FETCH_ASSOC);
    $actual[$cid] = $row;
    echo "cta $cid {$row['nombre']}: saldo=" . $f($row['saldo_actual']) . " valor_mxn=" . $f($row['valor_mxn']) . "\n";
}

// 2. Idempotencia: si ya están en 0, salir
$yaOk = true;
foreach ($esperado as $cid => $e) {
    if (abs((float)$actual[$cid]['saldo_actual']) > 0.01 || abs((float)$actual[$cid]['valor_mxn']) > 0.01) {
        $yaOk = false;
    }
}
if ($yaOk) {
    echo "\nYa corregido (ambas cuentas en 0). Nada que hacer.\n";
    exit(0);
}

// 3. Validar que coinciden con lo diagnosticado (evita aplicar a ciegas si prod cambió)
foreach ($esperado as $cid => $e) {
    $ds = abs((float)$actual[$cid]['saldo_actual'] - $e['saldo']);
    $dv = abs((float)$actual[$cid]['valor_mxn'] - $e['valor']);
    if ($ds > 0.01 || $dv > 0.01) {
        echo "\nABORTADO: cta $cid no coincide con diagnóstico (esperaba saldo " . $f($e['saldo']) . " valor " . $f($e['valor']) . "). Revisa antes de aplicar.\n";
        exit(1);
    }
}
echo "\nDiagnóstico coincide. Diferencia actual TODAS debería ser -989,630.70.\n";

if (!$apply) {
    echo "\nDRY-RUN: corrección propuesta:\n";
    echo "  cta 44  -> saldo 0.00, valor_mxn 0.00 (-58,042.86 / -496,266.45)\n";
    echo "  cta 200 -> saldo 0.00, valor_mxn 0.00 (-58,042.86 / -493,364.31)\n";
    echo "  proveedores 989,630.76 -> 0.00, diferencia -> ~0.00\n";
    echo "\nPara aplicar en LOCAL primero: php scripts/fix-faltante-989k-proveedores.php --apply\n";
    exit(0);
}

// 4. Apply con transacción + backup de filas
// NOTA: el CREATE TABLE va FUERA de la transacción porque en MySQL el DDL
// hace commit implícito y rompería el rollback.
$pdo->exec("CREATE TABLE IF NOT EXISTS backup_cuentas_fix989k (
    id INT, nombre VARCHAR(255), saldo_actual DECIMAL(18,2),
    valor_mxn DECIMAL(18,2), respaldado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
try {
    $pdo->beginTransaction();

    $pdo->exec("DELETE FROM backup_cuentas_fix989k WHERE id IN (44,200)");
    $pdo->exec("INSERT INTO backup_cuentas_fix989k (id, nombre, saldo_actual, valor_mxn)
                SELECT id, nombre, saldo_actual, valor_mxn FROM cuentas WHERE id IN (44,200)");

    // Trigger de cuentas exige esta variable (ver helpers.php:86)
    $pdo->exec("SET @hyperion_saldo_auth = 1");

    $stmt = $pdo->prepare("UPDATE cuentas SET saldo_actual = 0.00, valor_mxn = 0.00 WHERE id = ?");
    $stmt->execute([44]);
    $stmt->execute([200]);

    // Trazabilidad en historial_saldos (movimiento_id NOT NULL: enlazamos al
    // reverso que duplicó el saldo para dejar rastro auditable).
    $stmtH = $pdo->prepare("INSERT INTO historial_saldos
        (cuenta_id, movimiento_id, tipo, monto, saldo_anterior, saldo_nuevo)
        VALUES (?, ?, 'salida', ?, ?, 0.00)");
    $stmtH->execute([44, 3973, 58042.86, $actual[44]['saldo_actual']]);
    $stmtH->execute([200, 3969, 58042.86, $actual[200]['saldo_actual']]);

    // 5. Verificación post
    $prov = (float)$pdo->query("SELECT COALESCE(SUM(c.valor_mxn),0) FROM proveedores p
        INNER JOIN cuentas c ON (p.cuenta_usd_id=c.id OR p.cuenta_usdt_id=c.id) WHERE p.activo=1")->fetchColumn();
    $s44 = (float)$pdo->query("SELECT saldo_actual FROM cuentas WHERE id=44")->fetchColumn();
    $s200 = (float)$pdo->query("SELECT saldo_actual FROM cuentas WHERE id=200")->fetchColumn();

    echo "\nPost: proveedores valor_mxn=" . $f($prov) . " (esperado 0.00)\n";
    echo "Post: cta44 saldo=" . $f($s44) . " cta200 saldo=" . $f($s200) . "\n";

    if (abs($prov) > 0.15 || abs($s44) > 0.01 || abs($s200) > 0.01) {
        throw new Exception("Verificación post falló, rollback.");
    }

    $pdo->commit();
    echo "\nOK. Commit hecho. Respaldo en backup_cuentas_fix989k.\n";
    echo "Siguiente: abre flujo.php TODAS y verifica_diferencia.php (diferencia debe ser 0.00).\n";
    echo "NO re-anules los grupos 1789752654329 / 1789752722710 (ya están anulados).\n";
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\nERROR: " . $e->getMessage() . " (rollback hecho)\n";
    exit(1);
}
