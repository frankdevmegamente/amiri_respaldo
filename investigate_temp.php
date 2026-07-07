<?php
require_once "src/config/db.php";
$pdo = getPDO();
echo "GASTOS RECIENTES:\n";
$q = $pdo->query("SELECT id, fecha, monto, concepto, categoria_id FROM gastos ORDER BY id DESC LIMIT 20");
foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo "ID={$r["id"]} F={$r["fecha"]} M={$r["monto"]} Cat={$r["categoria_id"]} C={$r["concepto"]}\n";
}
echo "\nCATEGORIAS:\n";
$q = $pdo->query("SELECT id, nombre FROM categorias_gasto ORDER BY id");
foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo "ID={$r["id"]}: {$r["nombre"]}\n";
}
echo "\nMOVIMIENTOS ASOCIADOS A GASTOS RECIENTES:\n";
$q = $pdo->query("SELECT m.id, m.grupo_id, m.fecha, m.gasto_id, m.monto, m.descripcion FROM movimientos m WHERE m.gasto_id IS NOT NULL ORDER BY m.id DESC LIMIT 20");
foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo "ID={$r["id"]} G={$r["grupo_id"]} F={$r["fecha"]} GastID={$r["gasto_id"]} M={$r["monto"]} D={$r["descripcion"]}\n";
}
