<?php
$dbName = $argv[1] ?? null;
if ($dbName) {
    $pdo = new PDO("mysql:host=localhost;port=3306;dbname=$dbName;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} else {
    require_once __DIR__ . '/../src/config/db.php';
    $pdo = getPDO();
}

try {
    $pdo->exec("DROP TRIGGER IF EXISTS trg_cuentas_before_update");
    $pdo->exec("
        CREATE TRIGGER trg_cuentas_before_update BEFORE UPDATE ON cuentas
        FOR EACH ROW
        BEGIN
            IF OLD.saldo_actual != NEW.saldo_actual THEN
                IF @hyperion_saldo_auth IS NULL OR @hyperion_saldo_auth != 1 THEN
                    SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'No puedes modificar saldo_actual directamente. Usa actualizarSaldoCuenta().';
                END IF;
                SET @hyperion_saldo_auth = NULL;
            END IF;
        END
    ");
    echo "Trigger instalado correctamente en amiri_sandbox_v27.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
