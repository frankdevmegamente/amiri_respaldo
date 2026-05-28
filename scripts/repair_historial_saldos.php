<?php
$dbName = $argv[1] ?? null;
if ($dbName) {
    $pdo = new PDO("mysql:host=localhost;port=3306;dbname=$dbName;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} else {
    require_once __DIR__ . '/../src/config/db.php';
    $pdo = getPDO();
}

try {
    $cuentas = $pdo->query("SELECT DISTINCT cuenta_id FROM historial_saldos ORDER BY cuenta_id")->fetchAll(PDO::FETCH_COLUMN);
    if (empty($cuentas)) {
        echo "No hay historial_saldos que reparar.\n";
        exit(0);
    }

    $totalFixed = 0;
    $totalRows = 0;
    $cuentasReparadas = 0;

    foreach ($cuentas as $cuentaId) {
        $stmt = $pdo->prepare("
            SELECT id, tipo, monto, saldo_anterior, saldo_nuevo
            FROM historial_saldos
            WHERE cuenta_id = ?
            ORDER BY created_at ASC, id ASC
        ");
        $stmt->execute([$cuentaId]);
        $rows = $stmt->fetchAll();

        if (empty($rows)) continue;

        $numRows = count($rows);
        $totalRows += $numRows;
        $currentSaldo = (float)$rows[0]['saldo_anterior'];
        $fixed = 0;

        foreach ($rows as $i => $r) {
            $rowId = $r['id'];
            $tipo = $r['tipo'];
            $monto = (float)$r['monto'];
            $dbAnterior = (float)$r['saldo_anterior'];
            $dbNuevo = (float)$r['saldo_nuevo'];

            $expectedNuevo = round($currentSaldo + ($tipo === 'entrada' ? $monto : -$monto), 2);

            if (abs($dbAnterior - $currentSaldo) > 0.01) {
                $upd = $pdo->prepare("UPDATE historial_saldos SET saldo_anterior = ? WHERE id = ?");
                $upd->execute([$currentSaldo, $rowId]);
                $fixed++;
            }

            if (abs($dbNuevo - $expectedNuevo) > 0.01) {
                $upd = $pdo->prepare("UPDATE historial_saldos SET saldo_nuevo = ? WHERE id = ?");
                $upd->execute([$expectedNuevo, $rowId]);
                $fixed++;
            }

            $currentSaldo = $expectedNuevo;
        }

        $totalFixed += $fixed;
        if ($fixed > 0) $cuentasReparadas++;

        $stmtSaldo = $pdo->prepare("SELECT saldo_actual FROM cuentas WHERE id = ?");
        $stmtSaldo->execute([$cuentaId]);
        $saldoActual = (float)$stmtSaldo->fetchColumn();

        $diff = round($currentSaldo - $saldoActual, 2);
        $flag = abs($diff) > 0.01 ? " ⚠️ saldo_actual difiere por " . number_format($diff, 2) : "";

        echo "Cuenta #{$cuentaId}: {$numRows} registros, {$fixed} arreglados{$flag}\n";
    }

    echo "\n---\n";
    echo "Total: {$totalRows} registros revisados en " . count($cuentas) . " cuentas, {$totalFixed} correcciones en {$cuentasReparadas} cuenta(s).\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
