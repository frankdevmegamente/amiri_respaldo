<?php

// api/flujo-lista.php - DataTables Server-Side Processing

require_once __DIR__ . '/../src/config/api-middleware.php';

require_once __DIR__ . '/../src/config/db.php';

require_once __DIR__ . '/../src/config/logger.php';



initApi();

requireAdmin();

$pdo = getPDO();



try {

    // Parámetros de DataTables

    $draw = isset($_GET['draw']) ? (int)$_GET['draw'] : 1;

    $start = isset($_GET['start']) ? (int)$_GET['start'] : 0;

    $length = isset($_GET['length']) ? (int)$_GET['length'] : 10;

    $searchValue = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';

    $orderColumnIndex = isset($_GET['order'][0]['column']) ? (int)$_GET['order'][0]['column'] : 0;

    $orderDir = isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'DESC';

    

    // Filtros

    $moneda = $_GET['moneda'] ?? 'TODAS';

    $anio = $_GET['anio'] ?? '';

    $mes = $_GET['mes'] ?? '';

    $fechaDesde = $_GET['fecha_desde'] ?? '';

    $fechaHasta = $_GET['fecha_hasta'] ?? '';

    $servicio = $_GET['servicio'] ?? '';

    

    // Columnas de ordenamiento (sin prefijo porque son del subquery agrupado)

    $columns = ['fecha', 'servicio', 'moneda', 'total_entradas', 'total_salidas'];

    $orderColumn = isset($columns[$orderColumnIndex]) ? $columns[$orderColumnIndex] : 'fecha';

    

    // Obtener qué pestaña estamos viendo (por defecto 'activos')
    $vista = $_GET['vista'] ?? 'activos';

    // Base query: Solo activos y NUNCA mostrar los reversos ($0.00)
    $whereConditions = [
        "fm.activo = 1",
        "(fm.es_reverso = 0 OR fm.es_reverso IS NULL)"
    ];
    $params = [];

    // Filtro inteligente según la pestaña
    if ($vista === 'activos') {
        $whereConditions[] = "(fm.estado = 'activo' OR fm.estado IS NULL)";
    } else if ($vista === 'anulados') {
        // En la pestaña de anulados, mostramos SOLO el original que fue anulado
        $whereConditions[] = "fm.estado IN ('anulado', 'corregido')";
    }

    

    // Filtro por servicios específicos (para flujo-divisas, incluye MXN de ventas)

    $serviciosIds = $_GET['servicios_ids'] ?? '';

    if (!empty($serviciosIds)) {

        $ids = array_filter(array_map('intval', explode(',', $serviciosIds)));

        if (!empty($ids)) {

            $whereConditions[] = 'fm.servicio_id IN (' . implode(',', $ids) . ')';

        }

    } elseif ($moneda !== 'TODAS') {

        // Filtro de moneda (solo cuando no se usa servicios_ids)

        $whereConditions[] = "fm.moneda = :moneda";

        $params[':moneda'] = $moneda;

    }



    // Filtro por año

    if (!empty($anio)) {

        $whereConditions[] = "YEAR(fm.fecha) = :anio";

        $params[':anio'] = $anio;

    }



    // Filtro por mes

    if (!empty($mes)) {

        $whereConditions[] = "MONTH(fm.fecha) = :mes";

        $params[':mes'] = ltrim($mes, '0'); // Eliminar cero a la izquierda

    }



    // Filtro por rango de fechas

    if (!empty($fechaDesde)) {

        $whereConditions[] = "fm.fecha >= :fecha_desde";

        $params[':fecha_desde'] = $fechaDesde;

    }



    if (!empty($fechaHasta)) {

        $whereConditions[] = "fm.fecha <= :fecha_hasta";

        $params[':fecha_hasta'] = $fechaHasta;

    }



    // Filtro por servicio

    if (!empty($servicio)) {

        $whereConditions[] = "s.nombre = :servicio";

        $params[':servicio'] = $servicio;

    }

    

    // Búsqueda global

    if (!empty($searchValue)) {

        $whereConditions[] = "(

            s.nombre LIKE :search 

            OR cl.nombre LIKE :search 

            OR p.nombre LIKE :search 

            OR pr.nombre LIKE :search

            OR fm.moneda LIKE :search

            OR DATE_FORMAT(fm.fecha, '%d/%m/%Y') LIKE :search

        )";

        $params[':search'] = "%$searchValue%";

    }

    

    $whereClause = implode(' AND ', $whereConditions);

    

    // Contar total de registros
    $sqlTotalQuery = "SELECT COUNT(DISTINCT fm.grupo_id) as total FROM movimientos fm WHERE fm.activo = 1 AND (fm.es_reverso = 0 OR fm.es_reverso IS NULL)";

    if ($vista === 'activos') {
        $sqlTotalQuery .= " AND (fm.estado = 'activo' OR fm.estado IS NULL)";
    } else if ($vista === 'anulados') {
        $sqlTotalQuery .= " AND fm.estado IN ('anulado', 'corregido')";
    }

    if (!empty($ids)) {
        $sqlTotalQuery .= ' AND fm.servicio_id IN (' . implode(',', $ids) . ')';
    } elseif ($moneda !== 'TODAS') {
        $sqlTotalQuery .= " AND fm.moneda = :moneda";
    }

    if (!empty($ids)) {

        $sqlTotalQuery .= ' AND fm.servicio_id IN (' . implode(',', $ids) . ')';

    } elseif ($moneda !== 'TODAS') {

        $sqlTotalQuery .= " AND fm.moneda = :moneda";

    }

    $stmtTotal = $pdo->prepare($sqlTotalQuery);

    if (empty($ids) && $moneda !== 'TODAS') {

        $stmtTotal->bindValue(':moneda', $moneda);

    }

    $stmtTotal->execute();

    $totalRecords = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];

    

    // Subquery para datos agrupados

    $subquery = "

        SELECT 
            MIN(fm.id) as id,
            fm.grupo_id,
            fm.fecha,
            MAX(fm.creado_en) as creado_en,
            MAX(fm.corregido_en) as corregido_en,
            MAX(fm.estado) as estado,
            s.nombre as servicio,

            COUNT(*) as cantidad_movimientos,

            GROUP_CONCAT(DISTINCT fm.tipo ORDER BY fm.tipo) as tipos,

            CASE 

                WHEN SUM(CASE WHEN fm.tipo = 'traspaso' THEN 1 ELSE 0 END) > 0 THEN 'Traspaso'

                WHEN SUM(CASE WHEN fm.tipo = 'entrada' THEN 1 ELSE 0 END) > 0 AND SUM(CASE WHEN fm.tipo = 'salida' THEN 1 ELSE 0 END) > 0 THEN 'Entrada/Salida'

                WHEN SUM(CASE WHEN fm.tipo = 'entrada' THEN 1 ELSE 0 END) > 0 THEN 'Entrada'

                WHEN SUM(CASE WHEN fm.tipo = 'salida' THEN 1 ELSE 0 END) > 0 THEN 'Salida'

                ELSE 'Otro'

            END as tipo_principal,

            fm.moneda,

            SUM(CASE WHEN fm.tipo = 'entrada' THEN fm.monto ELSE 0 END) as total_entradas,

            SUM(CASE WHEN fm.tipo = 'salida' THEN fm.monto ELSE 0 END) as total_salidas,

            SUM(CASE WHEN fm.tipo = 'comision' THEN fm.monto ELSE 0 END) as total_comisiones,

            GREATEST(

                MAX(CASE WHEN fm.tipo = 'entrada' THEN fm.monto ELSE 0 END),

                MAX(CASE WHEN fm.tipo = 'salida' THEN fm.monto ELSE 0 END),

                MAX(CASE WHEN fm.tipo = 'traspaso' THEN fm.monto ELSE 0 END)

            ) as monto_principal,

            GROUP_CONCAT(DISTINCT COALESCE(co.nombre, '') SEPARATOR ', ') as cuentas_origen,

            GROUP_CONCAT(DISTINCT COALESCE(cd.nombre, '') SEPARATOR ', ') as cuentas_destino,

            MAX(COALESCE(cl.nombre, '')) as cliente,

            MAX(COALESCE(p.nombre, '')) as proveedor,

            MAX(COALESCE(pr.nombre, '')) as promotor,

            MAX(fm.paridad) as paridad,

            MAX(fm.descripcion) as descripcion

        FROM movimientos fm

        LEFT JOIN servicios s ON fm.servicio_id = s.id

        LEFT JOIN cuentas co ON fm.cuenta_origen_id = co.id

        LEFT JOIN cuentas cd ON fm.cuenta_destino_id = cd.id

        LEFT JOIN clientes cl ON fm.cliente_id = cl.id

        LEFT JOIN proveedores p ON fm.proveedor_id = p.id

        LEFT JOIN promotores pr ON fm.promotor_id = pr.id

        WHERE $whereClause

        GROUP BY fm.grupo_id, fm.fecha, s.nombre, fm.moneda

    ";

    

    // Si se ordena por fecha, ordenar por creado_en para tener el orden cronológico exacto

    // Ordenar por la fecha de operación seleccionada, y usar la fecha de captura como desempate
    if ($orderColumn === 'fecha') {
        $orderColumn = "fecha $orderDir, creado_en";
        $orderDir = "DESC";
    }

    

    // Query principal con paginación

    $sql = "

        SELECT * FROM ($subquery) as grouped

        ORDER BY $orderColumn $orderDir

        LIMIT :start, :length

    ";

    

    $stmt = $pdo->prepare($sql);

    foreach ($params as $key => $value) {

        $stmt->bindValue($key, $value);

    }

    $stmt->bindValue(':start', $start, PDO::PARAM_INT);

    $stmt->bindValue(':length', $length, PDO::PARAM_INT);

    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    

    // Contar registros filtrados

    $sqlFiltered = "SELECT COUNT(*) as total FROM ($subquery) as grouped";

    $stmtFiltered = $pdo->prepare($sqlFiltered);

    foreach ($params as $key => $value) {

        $stmtFiltered->bindValue($key, $value);

    }

    $stmtFiltered->execute();

    $filteredRecords = $stmtFiltered->fetch(PDO::FETCH_ASSOC)['total'];

    

    // Respuesta para DataTables

    jsonResponse([

        'draw' => $draw,

        'recordsTotal' => $totalRecords,

        'recordsFiltered' => $filteredRecords,

        'data' => $data

    ]);



} catch (Exception $e) {

    Logger::error('Error en flujo-lista', [

        'message' => $e->getMessage(),

        'trace' => $e->getTraceAsString()

    ]);

    jsonError($e->getMessage(), 500);

}



