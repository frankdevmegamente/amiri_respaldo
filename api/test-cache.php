<?php

/**

 * Test rápido del sistema de caché

 * Abrir en: http://localhost/amiriAppProd/api/test-cache.php

 */

require_once __DIR__ . '/../src/config/session.php';

require_once __DIR__ . '/../src/config/cache.php';



// Verificar autenticación

if (!isset($_SESSION['usuario_id'])) {

    die('⛔ Debes iniciar sesión primero');

}



?>

<!DOCTYPE html>

<html>

<head>

    <title>Test Cache - Fase 2</title>

    <style>

        body { font-family: Arial; padding: 20px; background: #f5f5f5; }

        .box { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }

        .success { color: #28a745; font-weight: bold; }

        .error { color: #dc3545; font-weight: bold; }

        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }

        .badge { display: inline-block; padding: 5px 10px; border-radius: 3px; font-size: 12px; }

        .badge-success { background: #28a745; color: white; }

        .badge-warning { background: #ffc107; color: black; }

        .badge-danger { background: #dc3545; color: white; }

    </style>

</head>

<body>

    <h1>🧪 Test del Sistema de Caché</h1>



    <!-- Info general del cache -->

    <div class="box">

        <h2>📊 Información del Cache</h2>

        <?php

        $info = SimpleCache::getInfo();

        echo "<pre>";

        echo "Total de claves: " . ($info['total_keys'] ?? 0) . "\n";

        echo "Tamaño total: " . ($info['total_size'] ?? 0) . " bytes\n";

        echo "Claves almacenadas: " . count($info['keys'] ?? []) . "\n";

        echo "</pre>";

        

        if (!empty($info['keys'])) {

            echo "<h4>Detalles de cada clave:</h4>";

            echo "<table border='1' cellpadding='5' style='width:100%; border-collapse: collapse;'>";

            echo "<tr><th>Clave</th><th>Expira en</th><th>Antigüedad</th><th>Tamaño</th></tr>";

            foreach ($info['keys'] as $key => $data) {

                $expires = $data['expires_in'] > 0 ? gmdate('i:s', $data['expires_in']) . ' min' : 'Expirado';

                $age = gmdate('i:s', $data['age']) . ' min';

                echo "<tr>";

                echo "<td>" . htmlspecialchars($key) . "</td>";

                echo "<td>{$expires}</td>";

                echo "<td>{$age}</td>";

                echo "<td>{$data['size']} bytes</td>";

                echo "</tr>";

            }

            echo "</table>";

        }

        ?>

    </div>



    <!-- Test de servicios -->

    <div class="box">

        <h2>🔧 Cache de Servicios</h2>

        <?php

        $servicios = SimpleCache::get('servicios_list');

        if ($servicios) {

            echo '<span class="badge badge-success">✅ EN CACHE</span>';

            echo "<p>Se encontraron " . count($servicios) . " servicios en caché</p>";

            echo "<pre>";

            foreach ($servicios as $serv) {

                echo "- " . ($serv['nombre'] ?? 'N/A') . "\n";

            }

            echo "</pre>";

        } else {

            echo '<span class="badge badge-warning">⚠️ NO EN CACHE</span>';

            echo "<p>Los servicios no están en caché. Carga la página de servicios para cachearlos.</p>";

        }

        ?>

        <button onclick="location.href='../servicios.php'">Ir a Servicios</button>

    </div>



    <!-- Test de tipos de gasto -->

    <div class="box">

        <h2>💰 Cache de Tipos de Gasto</h2>

        <?php

        $tipos = SimpleCache::get('categorias_gasto_list');

        if ($tipos) {

            echo '<span class="badge badge-success">✅ EN CACHE</span>';

            echo "<p>Se encontraron " . count($tipos) . " tipos de gasto en caché</p>";

            echo "<pre>";

            foreach ($tipos as $tipo) {

                echo "- " . ($tipo['nombre'] ?? 'N/A') . "\n";

            }

            echo "</pre>";

        } else {

            echo '<span class="badge badge-warning">⚠️ NO EN CACHE</span>';

            echo "<p>Los tipos de gasto no están en caché. Carga la página de gastos para cachearlos.</p>";

        }

        ?>

        <button onclick="location.href='../gastos.php'">Ir a Gastos</button>

    </div>



    <!-- Test manual de set/get -->

    <div class="box">

        <h2>🧪 Test Manual Set/Get</h2>

        <?php

        // Probar set y get

        $testKey = 'test_manual_' . time();

        $testValue = ['mensaje' => 'Hola desde el cache', 'timestamp' => time()];

        

        SimpleCache::set($testKey, $testValue, 300); // 5 minutos

        $retrieved = SimpleCache::get($testKey);

        

        if ($retrieved && $retrieved['mensaje'] === $testValue['mensaje']) {

            echo '<span class="success">✅ Test PASADO</span>';

            echo "<p>Se guardó y recuperó correctamente del cache</p>";

            echo "<pre>" . print_r($retrieved, true) . "</pre>";

        } else {

            echo '<span class="error">❌ Test FALLIDO</span>';

            echo "<p>No se pudo guardar/recuperar del cache</p>";

        }

        ?>

    </div>



    <!-- Test de remember (Cache-aside) -->

    <div class="box">

        <h2>⚡ Test Cache-Aside Pattern (remember)</h2>

        <?php

        $start = microtime(true);

        

        $datos = SimpleCache::remember('test_remember', function() {

            // Simular consulta costosa

            usleep(100000); // 100ms

            return ['resultado' => 'Datos procesados', 'time' => date('H:i:s')];

        }, 300);

        

        $duration = round((microtime(true) - $start) * 1000, 2);

        

        echo "<p>Tiempo de ejecución: <strong>{$duration}ms</strong></p>";

        echo "<p>Si es <100ms = datos del cache ✅<br>Si es >100ms = consultó la función ⚠️</p>";

        echo "<pre>" . print_r($datos, true) . "</pre>";

        echo "<p><small>Recarga la página para ver que la segunda vez es más rápida</small></p>";

        ?>

    </div>



    <!-- Botón para limpiar cache -->

    <div class="box">

        <h2>🗑️ Limpiar Cache</h2>

        <?php

        if (isset($_GET['clear'])) {

            SimpleCache::cleanup();

            echo '<span class="success">✅ Cache limpiado correctamente</span>';

            echo '<meta http-equiv="refresh" content="2;url=test-cache.php">';

        } else {

            echo '<p>Esto eliminará todas las entradas del cache.</p>';

            echo '<button onclick="if(confirm(\'¿Seguro?\')) location.href=\'?clear=1\'">Limpiar Todo el Cache</button>';

        }

        ?>

    </div>



    <div class="box">

        <h3>📝 Notas de Uso</h3>

        <ul>

            <li>El cache se almacena en la sesión PHP ($_SESSION)</li>

            <li>TTL por defecto: 1 hora (3600 segundos)</li>

            <li>Al crear/editar/eliminar servicios o tipos de gasto, el cache se invalida automáticamente</li>

            <li>El cache se pierde al cerrar sesión o cuando expira la sesión</li>

        </ul>

    </div>



    <p><a href="../index.php">← Volver al Dashboard</a></p>

</body>

</html>

