<?php
/**
 * Test del sistema de validación
 * Abrir en: http://localhost/amiriAppProd/api/test-validator.php
 */
require_once __DIR__ . '/../src/config/session.php';
require_once __DIR__ . '/../src/config/validator.php';

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    die('⛔ Debes iniciar sesión primero');
}

// Array para resultados
$results = [];

// Test 1: Campos requeridos
try {
    $validator = new Validator();
    $validator->required(['nombre' => 'Juan'], ['nombre', 'email']);
    $results[] = ['test' => 'Campos requeridos (debe fallar)', 'status' => 'error', 'msg' => 'No detectó campo faltante'];
} catch (ValidationException $e) {
    $results[] = ['test' => 'Campos requeridos', 'status' => 'success', 'msg' => $e->getMessage()];
}

// Test 2: Validación numérica con rango
try {
    $validator = new Validator();
    $validator->numeric(150, 0, 100);
    $results[] = ['test' => 'Numérico con rango (debe fallar)', 'status' => 'error', 'msg' => 'No detectó valor fuera de rango'];
} catch (ValidationException $e) {
    $results[] = ['test' => 'Numérico con rango', 'status' => 'success', 'msg' => $e->getMessage()];
}

// Test 3: Email válido
try {
    $validator = new Validator();
    $validator->email('correo@invalido');
    $results[] = ['test' => 'Email inválido (debe fallar)', 'status' => 'error', 'msg' => 'No detectó email inválido'];
} catch (ValidationException $e) {
    $results[] = ['test' => 'Email inválido', 'status' => 'success', 'msg' => $e->getMessage()];
}

// Test 4: Email válido que pase
try {
    $validator = new Validator();
    $validator->email('usuario@example.com');
    $results[] = ['test' => 'Email válido', 'status' => 'success', 'msg' => 'Email válido aceptado correctamente'];
} catch (ValidationException $e) {
    $results[] = ['test' => 'Email válido (no debería fallar)', 'status' => 'error', 'msg' => $e->getMessage()];
}

// Test 5: Porcentaje
try {
    $validator = new Validator();
    $validator->percentage(150);
    $results[] = ['test' => 'Porcentaje > 100 (debe fallar)', 'status' => 'error', 'msg' => 'No detectó porcentaje > 100'];
} catch (ValidationException $e) {
    $results[] = ['test' => 'Porcentaje > 100', 'status' => 'success', 'msg' => $e->getMessage()];
}

// Test 6: Porcentaje válido
try {
    $validator = new Validator();
    $validator->percentage(85);
    $results[] = ['test' => 'Porcentaje válido', 'status' => 'success', 'msg' => 'Porcentaje 85 aceptado'];
} catch (ValidationException $e) {
    $results[] = ['test' => 'Porcentaje válido (no debería fallar)', 'status' => 'error', 'msg' => $e->getMessage()];
}

// Test 7: Enum
try {
    $validator = new Validator();
    $validator->enum('superadmin', ['admin', 'usuario', 'prestamista']);
    $results[] = ['test' => 'Enum valor no permitido (debe fallar)', 'status' => 'error', 'msg' => 'No detectó valor no permitido'];
} catch (ValidationException $e) {
    $results[] = ['test' => 'Enum valor no permitido', 'status' => 'success', 'msg' => $e->getMessage()];
}

// Test 8: Length
try {
    $validator = new Validator();
    $validator->length('AB', 3, 10);
    $results[] = ['test' => 'Longitud mínima (debe fallar)', 'status' => 'error', 'msg' => 'No detectó longitud < mínimo'];
} catch (ValidationException $e) {
    $results[] = ['test' => 'Longitud mínima', 'status' => 'success', 'msg' => $e->getMessage()];
}

// Test 9: Date
try {
    $validator = new Validator();
    $validator->date('2026-13-45'); // Fecha inválida
    $results[] = ['test' => 'Fecha inválida (debe fallar)', 'status' => 'error', 'msg' => 'No detectó fecha inválida'];
} catch (ValidationException $e) {
    $results[] = ['test' => 'Fecha inválida', 'status' => 'success', 'msg' => $e->getMessage()];
}

// Test 10: Date válida
try {
    $validator = new Validator();
    $validator->date('2026-01-25');
    $results[] = ['test' => 'Fecha válida', 'status' => 'success', 'msg' => 'Fecha 2026-01-25 aceptada'];
} catch (ValidationException $e) {
    $results[] = ['test' => 'Fecha válida (no debería fallar)', 'status' => 'error', 'msg' => $e->getMessage()];
}

// Test 11: Currency
try {
    $validator = new Validator();
    $validator->currency(-100.50);
    $results[] = ['test' => 'Monto negativo (debe fallar)', 'status' => 'error', 'msg' => 'No detectó monto negativo'];
} catch (ValidationException $e) {
    $results[] = ['test' => 'Monto negativo', 'status' => 'success', 'msg' => $e->getMessage()];
}

// Calcular resumen
$passed = count(array_filter($results, fn($r) => $r['status'] === 'success'));
$failed = count($results) - $passed;
$percentage = round(($passed / count($results)) * 100, 1);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Validator - Fase 2</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .test-result { padding: 10px; margin: 5px 0; border-radius: 3px; }
        .test-result.success { background: #d4edda; border-left: 4px solid #28a745; }
        .test-result.error { background: #f8d7da; border-left: 4px solid #dc3545; }
        .summary { font-size: 1.2em; margin: 20px 0; }
        .badge { padding: 5px 10px; border-radius: 3px; color: white; font-weight: bold; }
        .badge-success { background: #28a745; }
        .badge-danger { background: #dc3545; }
        .progress-bar { width: 100%; height: 30px; background: #e9ecef; border-radius: 5px; overflow: hidden; }
        .progress-fill { height: 100%; background: #28a745; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; }
    </style>
</head>
<body>
    <h1>🧪 Test del Sistema de Validación</h1>

    <div class="box">
        <h2>📊 Resumen</h2>
        <div class="summary">
            <span class="badge badge-success">✅ Pasados: <?= $passed ?></span>
            <span class="badge badge-danger">❌ Fallidos: <?= $failed ?></span>
        </div>
        <div class="progress-bar">
            <div class="progress-fill" style="width: <?= $percentage ?>%">
                <?= $percentage ?>%
            </div>
        </div>
    </div>

    <div class="box">
        <h2>🔍 Resultados Detallados</h2>
        <?php foreach ($results as $result): ?>
            <div class="test-result <?= $result['status'] ?>">
                <strong><?= $result['status'] === 'success' ? '✅' : '❌' ?> <?= htmlspecialchars($result['test']) ?></strong>
                <p style="margin: 5px 0 0 0; font-size: 0.9em;"><?= htmlspecialchars($result['msg']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="box">
        <h3>📝 Notas sobre el Validator</h3>
        <ul>
            <li><strong>required():</strong> Valida que campos no estén vacíos</li>
            <li><strong>numeric():</strong> Valida rangos numéricos (min, max)</li>
            <li><strong>email():</strong> Valida formato RFC 5321</li>
            <li><strong>percentage():</strong> Valida que esté entre 0 y 100</li>
            <li><strong>enum():</strong> Valida que el valor esté en lista permitida</li>
            <li><strong>length():</strong> Valida longitud de cadena (min, max)</li>
            <li><strong>date():</strong> Valida formato de fecha (Y-m-d por defecto)</li>
            <li><strong>currency():</strong> Valida montos > 0 con 2 decimales</li>
        </ul>
        <p><strong>Integrado en:</strong> api/flujo.php, api/clientes.php, api/usuarios.php</p>
    </div>

    <?php if ($percentage === 100.0): ?>
        <div class="box" style="background: #d4edda; border: 2px solid #28a745;">
            <h2 style="color: #28a745;">🎉 ¡Todos los tests pasaron!</h2>
            <p>El sistema de validación está funcionando correctamente.</p>
        </div>
    <?php else: ?>
        <div class="box" style="background: #fff3cd; border: 2px solid #ffc107;">
            <h2 style="color: #856404;">⚠️ Algunos tests fallaron</h2>
            <p>Revisa los resultados arriba para ver qué validaciones no están funcionando correctamente.</p>
        </div>
    <?php endif; ?>

    <p><a href="../index.php">← Volver al Dashboard</a> | <a href="test-cache.php">Test de Cache →</a></p>
</body>
</html>
