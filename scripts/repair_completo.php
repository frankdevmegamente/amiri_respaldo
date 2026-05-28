<?php
$dbName = $argv[1] ?? 'amiri_sandbox_v27';
$pdo = new PDO("mysql:host=localhost;port=3306;dbname=$dbName;charset=utf8mb4", 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$dryRun = in_array('--dry-run', $argv);

echo "=== REPARACIÓN COMPLETA: $dbName ===\n";
if ($dryRun) echo "*** MODO DRY-RUN: no se harán cambios ***\n\n";

// === 1. DELETE orphaned historial #62 ===
echo "\n--- 1. Eliminar historial #62 (movimiento_id=189 inexistente) ---\n";
$h = $pdo->query("SELECT id FROM historial_saldos WHERE id=62")->fetch();
if ($h) {
    echo "  Encontrado: historial #62 (movimiento_id=189, cuenta_id=2)\n";
    if (!$dryRun) {
        $pdo->exec("DELETE FROM historial_saldos WHERE id=62");
        echo "  => Eliminado.\n";
    } else {
        echo "  => [DRY-RUN] Se eliminaría.\n";
    }
} else {
    echo "  No encontrado (ya fue eliminado previamente).\n";
}

// === 2. Re-run historial repair script ===
echo "\n--- 2. Reparar cadena de historial_saldos ---\n";
$cuentas = $pdo->query("SELECT DISTINCT cuenta_id FROM historial_saldos ORDER BY cuenta_id")->fetchAll(PDO::FETCH_COLUMN);
$totalFixed = 0;
$cuentasReparadas = 0;

foreach ($cuentas as $cuentaId) {
    $stmt = $pdo->prepare("SELECT id, tipo, monto, saldo_anterior, saldo_nuevo FROM historial_saldos WHERE cuenta_id = ? ORDER BY created_at ASC, id ASC");
    $stmt->execute([$cuentaId]);
    $rows = $stmt->fetchAll();
    if (empty($rows)) continue;

    $currentSaldo = (float)$rows[0]['saldo_anterior'];
    $fixed = 0;

    foreach ($rows as $r) {
        $tipo = $r['tipo'];
        $monto = (float)$r['monto'];
        $expectedNuevo = round($currentSaldo + ($tipo === 'entrada' ? $monto : -$monto), 2);

        if (abs((float)$r['saldo_anterior'] - $currentSaldo) > 0.01) {
            if (!$dryRun) {
                $upd = $pdo->prepare("UPDATE historial_saldos SET saldo_anterior = ? WHERE id = ?");
                $upd->execute([$currentSaldo, $r['id']]);
            }
            $fixed++;
        }
        if (abs((float)$r['saldo_nuevo'] - $expectedNuevo) > 0.01) {
            if (!$dryRun) {
                $upd = $pdo->prepare("UPDATE historial_saldos SET saldo_nuevo = ? WHERE id = ?");
                $upd->execute([$expectedNuevo, $r['id']]);
            }
            $fixed++;
        }
        $currentSaldo = $expectedNuevo;
    }
    $totalFixed += $fixed;
    if ($fixed > 0) $cuentasReparadas++;
    if ($fixed > 0) echo "  Cuenta #$cuentaId: $fixed correcciones\n";
}
echo "  Total: $totalFixed correcciones en $cuentasReparadas cuenta(s)\n";

// === 3. Fix FIFO oversold lots (set restante to max(0, original - consumido)) ===
echo "\n--- 3. Reparar FIFO (lotes sobrevendidos) ---\n";
$lotes = $pdo->query("SELECT id, moneda, cantidad_original, cantidad_restante FROM inventario_monedas WHERE activo=1 ORDER BY id")->fetchAll();
$fifoFixed = 0;
foreach ($lotes as $l) {
    $s = $pdo->prepare("SELECT COALESCE(SUM(cantidad_consumida),0) FROM consumos_inventario WHERE inventario_id=?");
    $s->execute([$l['id']]);
    $consumido = (float)$s->fetchColumn();
    $esperado = round((float)$l['cantidad_original'] - $consumido, 8);
    $real = (float)$l['cantidad_restante'];
    if (abs($esperado - $real) > 0.00000001) {
        $newRestante = round(max(0, $esperado), 8);
        $diff = round($newRestante - $real, 8);
        if (abs($diff) > 0.00000001) {
            echo "  Lote #{$l['id']} ({$l['moneda']}): restante $real → $newRestante (sobrevendido por " . round(abs($esperado), 2) . ")\n";
            if (!$dryRun) {
                $upd = $pdo->prepare("UPDATE inventario_monedas SET cantidad_restante = ? WHERE id = ?");
                $upd->execute([$newRestante, $l['id']]);
            }
            $fifoFixed++;
        } else {
            echo "  Lote #{$l['id']} ({$l['moneda']}): restante ya es $real, solo sobrevendido (consumido=" . round($consumido, 2) . " > original=" . round($l['cantidad_original'], 2) . ")\n";
        }
    }
}
echo "  $fifoFixed lote(s) ajustado(s)\n";

// === 4. Fix balance discrepancies (sync saldo_actual to historial) ===
echo "\n--- 4. Reparar saldo_actual en cuentas ---\n";
$cuentas = $pdo->query("
    SELECT c.id, c.nombre, c.saldo_actual, c.tipo_moneda, c.tipo_cuenta,
           COALESCE(h.saldo_nuevo, 0) as saldo_historial
    FROM cuentas c
    LEFT JOIN historial_saldos h ON h.id = (SELECT MAX(h2.id) FROM historial_saldos h2 WHERE h2.cuenta_id = c.id)
    WHERE c.activo = 1
    ORDER BY c.id
")->fetchAll();

$balanceFixed = 0;
$balanceTotal = 0;
foreach ($cuentas as $c) {
    $diff = round((float)$c['saldo_actual'] - (float)$c['saldo_historial'], 2);
    if (abs($diff) <= 0.01) continue;
    $balanceTotal++;
    $old = (float)$c['saldo_actual'];
    $new = (float)$c['saldo_historial'];
    echo "  #{$c['id']} {$c['nombre']} ({$c['tipo_moneda']}): " . number_format($old, 2) . " → " . number_format($new, 2) . " (diff=" . number_format($diff, 2) . ")\n";
    if (!$dryRun) {
        $pdo->exec("SET @hyperion_saldo_auth = 1");
        $upd = $pdo->prepare("UPDATE cuentas SET saldo_actual = ? WHERE id = ?");
        $upd->execute([$new, $c['id']]);
        $balanceFixed++;
    }
}
echo "  $balanceFixed de $balanceTotal cuenta(s) corregidas\n";

echo "\n=== REPARACIÓN COMPLETADA ===\n";
