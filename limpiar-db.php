<?php
require_once __DIR__ . '/src/config/db.php';
$pdo = getPDO();

// Asegurar tablas de capital
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS cierres_mensuales (
        id int(11) AUTO_INCREMENT PRIMARY KEY,
        mes varchar(7) NOT NULL,
        fecha_cierre datetime NOT NULL,
        comisiones_amiri decimal(14,2) DEFAULT 0,
        ganancia_divisas decimal(14,2) DEFAULT 0,
        gastos_fijos decimal(14,2) DEFAULT 0,
        gastos_variables decimal(14,2) DEFAULT 0,
        gastos_total decimal(14,2) DEFAULT 0,
        utilidad_neta decimal(14,2) DEFAULT 0,
        capital_acumulado decimal(14,2) DEFAULT 0,
        created_at timestamp DEFAULT current_timestamp(),
        UNIQUE KEY uk_mes (mes)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS retiros_capital (
        id int(11) AUTO_INCREMENT PRIMARY KEY,
        monto decimal(14,2) NOT NULL,
        concepto varchar(255) NOT NULL,
        fecha date NOT NULL,
        usuario_id int(11) NOT NULL,
        usuario_nombre varchar(100) DEFAULT NULL,
        created_at timestamp DEFAULT current_timestamp()
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("INSERT IGNORE INTO tipos_cuenta (id, descripcion) VALUES (7, 'Capital Amiri')");
    $existe = $pdo->query("SELECT COUNT(*) FROM cuentas WHERE tipo_cuenta = 7 AND activo = 1")->fetchColumn();
    if (!$existe) {
        $pdo->exec("INSERT INTO cuentas (nombre, tipo_cuenta, tipo_moneda, saldo_actual, valor_mxn, activo) VALUES ('Capital Amiri', 7, 'MXN', 0, 0, 1)");
    }
} catch (Exception $e) {}

$confirmar = $_GET['confirmar'] ?? '';
if ($confirmar !== 'si') {
    ?>
    <!DOCTYPE html>
    <html><head><meta charset="UTF-8"><title>Limpiar DB</title>
    <style>body{background:#0f172a;color:#e2e8f0;padding:30px;font-family:sans-serif}h1{color:#f87171}.btn{padding:12px 24px;border:none;border-radius:6px;font-size:1rem;cursor:pointer;margin:5px}.btn-danger{background:#dc2626;color:#fff}.btn-cancel{background:#334155;color:#fff}</style>
    </head><body>
    <h1>Limpiar Base de Datos</h1>
    <p>Esto eliminará: movimientos, deudas, gastos, cierres, inventario, historial, auditoría, retiros, préstamos.</p>
    <p><strong>NO elimina:</strong> usuarios, clientes, promotores, proveedores, cuentas, servicios, categorías.</p>
    <p><strong>Resetea:</strong> todos los saldos de cuentas a $0.</p>
    <p>Sin datos de prueba. Creas movimientos reales desde la app.</p>
    <a href="?confirmar=si" class="btn btn-danger">EJECUTAR LIMPIEZA</a>
    <a href="flujo.php" class="btn btn-cancel">Cancelar</a>
    </body></html>
    <?php
    exit;
}

$tablas = [
    'consumos_inventario',
    'historial_saldos',
    'deudas_clientes',
    'deudas_empresa',
    'pagos_promotores',
    'gastos',
    'inventario_monedas',
    'prestamos_otorgados',
    'movimientos',
    'auditoria',
    'cierres_mensuales',
    'retiros_capital',
    'trusted_devices',
    'two_fa_attempts',
];

$ok = [];
$fail = [];
foreach ($tablas as $t) {
    try {
        $pdo->exec("TRUNCATE TABLE `$t`");
        $ok[] = $t;
    } catch (Exception $e) {
        $fail[] = "$t: " . $e->getMessage();
    }
}

foreach ($tablas as $t) {
    try { $pdo->exec("ALTER TABLE `$t` AUTO_INCREMENT = 1"); } catch (Exception $e) {}
}

$pdo->exec("UPDATE cuentas SET saldo_actual = 0, valor_mxn = 0");
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Limpieza OK</title>
<style>body{background:#0f172a;color:#e2e8f0;padding:30px;font-family:sans-serif}h1{color:#4ade80}.ok{color:#4ade80}.err{color:#f87171}.btn{padding:12px 24px;border:none;border-radius:6px;font-size:1rem;cursor:pointer;margin:5px;background:#6c5ce7;color:#fff;text-decoration:none;display:inline-block}table{width:100%;border-collapse:collapse}td,th{padding:6px;border:1px solid #334155;text-align:left}th{background:#1e293b;color:#a78bfa}</style>
</head><body>
<h1>Limpieza completada</h1>
<h3>Tablas limpiadas (<?= count($ok) ?>):</h3>
<ul><?php foreach($ok as $t) echo "<li class='ok'>$t</li>"; ?></ul>
<?php if($fail): ?>
<h3>Errores:</h3>
<ul><?php foreach($fail as $e) echo "<li class='err'>$e</li>"; ?></ul>
<?php endif; ?>
<h3>Saldos de todas las cuentas reseteados a $0</h3>
<p>Ahora ve a <strong>flujo.php</strong> y crea movimientos reales. Cuando tengas datos, cierra los meses desde <strong>capital-amiri.php</strong>.</p>
<a href="flujo.php" class="btn">Ir a Flujo →</a>
<a href="capital-amiri.php" class="btn" style="background:#16a34a">Ir a Capital →</a>
</body></html>
