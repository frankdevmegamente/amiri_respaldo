<?php
require_once __DIR__ . '/src/config/db.php';
$db = getPDO();
$r = $db->query('SELECT * FROM tipos_cuenta');
while ($row = $r->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . "\n";
}
