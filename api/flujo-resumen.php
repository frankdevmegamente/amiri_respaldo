<?php

require_once __DIR__ . '/../src/config/api-middleware.php';

require_once __DIR__ . '/../src/config/db.php';



initApi();

requireAdmin();

$pdo = getPDO();



try {

    // Obtener filtro de moneda

    $moneda = $_GET['moneda'] ?? 'TODAS';

    $whereMoneda = '';

    

    // Si no es TODAS, filtrar por tipo_moneda

    if ($moneda !== 'TODAS') {

        $whereMoneda = " AND tipo_moneda = :moneda";

    }

    

    // Si pide diferencia

    if (isset($_GET['tipo']) && $_GET['tipo'] === 'diferencia') {



        // ── TODAS: suma todo en MXN (MXN directo + divisas convertidas FIFO) ──

        if ($moneda === 'TODAS') {



            // SALDOS: cuentas MXN tipo 1,2 + inventario FIFO USD y USDT

            $stmtMxn = $pdo->query("SELECT COALESCE(SUM(saldo_actual),0) AS total FROM cuentas WHERE activo=1 AND tipo_cuenta IN(1,2) AND tipo_moneda='MXN'");

            $totalSaldosMxn = (float)$stmtMxn->fetch(PDO::FETCH_ASSOC)['total'];



            $stmtDiv = $pdo->query("SELECT COALESCE(SUM(im.cantidad_restante * im.paridad),0) AS total FROM inventario_monedas im INNER JOIN cuentas c ON im.cuenta_id=c.id WHERE c.activo=1 AND c.tipo_cuenta IN(1,2) AND im.activo=1");

            $totalSaldosDiv = (float)$stmtDiv->fetch(PDO::FETCH_ASSOC)['total'];

            $totalSaldos = $totalSaldosMxn + $totalSaldosDiv;

            // Sumar préstamos otorgados pendientes al activo (dinero prestado que NOS deben)
            $stmtPrestOtorg = $pdo->query("SELECT COALESCE(SUM(monto_total - monto_pagado), 0) AS total FROM prestamos_otorgados WHERE estado IN ('pendiente', 'parcial') AND activo = 1");
            $totalPrestamosOtorgados = (float)$stmtPrestOtorg->fetch(PDO::FETCH_ASSOC)['total'];
            $totalSaldos += $totalPrestamosOtorgados;

            // Sumar gastos activos que NO descontaron de Comisiones AMIRI
            // (los gastos nuevos solo descuentan de la cuenta seleccionada,
            //  los viejos ya tienen su contraparte en capital, no se duplican)
            $totalGastosSinCapital = 0;
            $stmtGastos = $pdo->query("SELECT COALESCE(SUM(monto), 0) AS total FROM gastos g WHERE g.activo = 1 AND (SELECT COUNT(*) FROM movimientos WHERE gasto_id = g.id AND cuenta_origen_id = (SELECT id FROM cuentas WHERE tipo_cuenta = 3 AND activo = 1 LIMIT 1)) = 0");
            $totalGastosSinCapital = (float)$stmtGastos->fetch(PDO::FETCH_ASSOC)['total'];
            $totalSaldos += $totalGastosSinCapital;

            // CAPITAL: solo tipo 3 (igual que el card de Capital) + deudas_clientes + promotores

            // NO incluir tipo 4 ni 5: esas cuentas se suman via deudas_clientes y promotores para evitar doble conteo

            $stmtCap = $pdo->query("SELECT COALESCE(SUM(saldo_actual),0) AS total FROM cuentas WHERE activo=1 AND tipo_cuenta = 3");

            $totalCapital = (float)$stmtCap->fetch(PDO::FETCH_ASSOC)['total'];



            $stmtCliMxn = $pdo->query("SELECT COALESCE(SUM(monto_neto_cliente-monto_pagado),0) AS total FROM deudas_clientes WHERE estado IN('pendiente','parcial') AND activo=1 AND (moneda='MXN' OR moneda IS NULL)");

            $totalCapital += (float)$stmtCliMxn->fetch(PDO::FETCH_ASSOC)['total'];



            $stmtProm = $pdo->query("SELECT COALESCE(SUM(c.saldo_actual),0) AS total FROM promotores p INNER JOIN cuentas c ON p.cuenta_id=c.id WHERE p.activo=1 AND p.es_interno=0 AND c.saldo_actual>0.01");

            $totalCapital += (float)$stmtProm->fetch(PDO::FETCH_ASSOC)['total'];



            foreach (['USD', 'USDT'] as $div) {

                $stmtP = $pdo->prepare("SELECT COALESCE(SUM(im.cantidad_restante*im.paridad)/NULLIF(SUM(im.cantidad_restante),0),0) AS p FROM inventario_monedas im INNER JOIN cuentas c ON im.cuenta_id=c.id WHERE im.activo=1 AND c.tipo_moneda=:m");

                $stmtP->execute(['m' => $div]);

                $paridad = (float)$stmtP->fetch(PDO::FETCH_ASSOC)['p'];



                $stmtCli = $pdo->prepare("SELECT COALESCE(SUM(monto_neto_cliente-monto_pagado),0) AS total FROM deudas_clientes WHERE estado IN('pendiente','parcial') AND activo=1 AND moneda=:m");

                $stmtCli->execute(['m' => $div]);

                $totalCapital += (float)$stmtCli->fetch(PDO::FETCH_ASSOC)['total'] * $paridad;

            }


            // OBLIGACIONES: prestamistas MXN + proveedores tipo 6 (MXN saldo_actual, USD/USDT valor_mxn)

            $stmtPrest = $pdo->query("SELECT COALESCE(SUM(monto-monto_pagado),0) AS total FROM deudas_empresa WHERE estado IN('pendiente','parcial') AND activo=1");

            $totalPrestamistas = (float)$stmtPrest->fetch(PDO::FETCH_ASSOC)['total'];



            // Buscamos la deuda directamente desde los proveedores enlazados
            $stmtProv = $pdo->query("
                SELECT COALESCE(SUM(c.valor_mxn), 0) AS total 
                FROM proveedores p 
                INNER JOIN cuentas c ON (p.cuenta_usd_id = c.id OR p.cuenta_usdt_id = c.id)
                WHERE p.activo = 1
            ");
            $totalProveedores = (float)$stmtProv->fetch(PDO::FETCH_ASSOC)['total'];

            $totalObligaciones = $totalPrestamistas + $totalProveedores;
            
            // Redondear para evitar basura de flotantes
            $totalSaldos = round($totalSaldos, 2);
            $totalCapital = round($totalCapital, 2);
            $totalObligaciones = round($totalObligaciones, 2);

            $diferencia = round($totalSaldos - $totalCapital - $totalObligaciones, 2);
            
            // Construir explicación de la diferencia
            $explicacion = [];
            $componentes = [];
            if ($totalPrestamosOtorgados > 0) {
                $stmtDet = $pdo->query("SELECT persona_nombre, (monto_total - monto_pagado) AS pendiente FROM prestamos_otorgados WHERE estado IN ('pendiente', 'parcial') AND activo = 1 ORDER BY persona_nombre");
                $detalles = [];
                foreach ($stmtDet as $r) {
                    $detalles[] = $r['persona_nombre'] . ': $' . number_format($r['pendiente'], 2);
                }
                $explicacion[] = [
                    'tipo' => 'prestamos_otorgados',
                    'concepto' => 'Préstamos otorgados pendientes',
                    'monto' => $totalPrestamosOtorgados,
                    'detalle' => implode(', ', $detalles)
                ];
                $componentes[] = 'Préstamos otorgados: $' . number_format($totalPrestamosOtorgados, 2);
            }
            $diferenciaBase = round($diferencia, 2);
            if (abs($diferenciaBase) > 0.05) {
                $explicacion[] = [
                    'tipo' => 'diferencia_neta',
                    'concepto' => 'Diferencia neta sin asignar',
                    'monto' => $diferenciaBase,
                    'detalle' => 'Combinación de saldos de cuentas, capital y obligaciones'
                ];
                $componentes[] = 'Diferencia neta: $' . number_format($diferenciaBase, 2);
            }

            // Absorber discrepancias microscópicas de redondeo de divisas (hasta 5 centavos)
            if (abs($diferencia) <= 0.05) {
                $diferencia = 0.00;
            }

            echo json_encode([

                'diferencia'      => $diferencia,

                'total_saldos'    => $totalSaldos,

                'total_capital'   => $totalCapital,

                'total_prestamos' => $totalObligaciones,

                'total_proveedor' => $totalProveedores,

                'explicacion'     => $explicacion,

                'resumen'         => !empty($componentes) ? implode(' | ', $componentes) : ''

            ]);

            exit;

        }



        // ── Para USD/USDT todo se calcula en MXN ──────────────────────────

        if ($moneda === 'USD' || $moneda === 'USDT') {



            // 1. SALDOS: wallet divisas (FIFO en MXN) + cuentas MXN bancaria/caja (saldo_actual)

            $stmt = $pdo->prepare("

                SELECT COALESCE(SUM(im.cantidad_restante * im.paridad), 0) AS total

                FROM inventario_monedas im

                INNER JOIN cuentas c ON im.cuenta_id = c.id

                WHERE c.activo = 1 AND c.tipo_cuenta IN (1, 2)

                  AND im.activo = 1 AND im.moneda = :moneda

            ");

            $stmt->execute(['moneda' => $moneda]);

            $totalWallet = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];



            $stmtMxn = $pdo->query("

                SELECT COALESCE(SUM(saldo_actual), 0) AS total

                FROM cuentas

                WHERE activo = 1 AND tipo_cuenta IN (1, 2) AND tipo_moneda = 'MXN'

            ");

            $totalMxnCuentas = (float)$stmtMxn->fetch(PDO::FETCH_ASSOC)['total'];



            $totalSaldos = $totalWallet + $totalMxnCuentas;



            // 2. CAPITAL: solo cuentas Capital (tipo 3, Utilidades AMIRI) + deudas_clientes en divisa

            // Las cuentas tipo 3 son en MXN, se suman directo

            $stmt = $pdo->query("

                SELECT COALESCE(SUM(saldo_actual), 0) AS total

                FROM cuentas

                WHERE activo = 1 AND tipo_cuenta = 3

            ");

            $totalCapital = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];



            // Paridad promedio del inventario para convertir divisa -> MXN

            $stmtParidad = $pdo->prepare("

                SELECT COALESCE(

                    SUM(im.cantidad_restante * im.paridad) / NULLIF(SUM(im.cantidad_restante), 0),

                    0

                ) AS paridad_promedio

                FROM inventario_monedas im

                INNER JOIN cuentas c ON im.cuenta_id = c.id

                WHERE im.activo = 1 AND c.tipo_moneda = :moneda

            ");

            $stmtParidad->execute(['moneda' => $moneda]);

            $paridadPromedio = (float)$stmtParidad->fetch(PDO::FETCH_ASSOC)['paridad_promedio'];



            // Sumar deudas de clientes en esta divisa, convertidas a MXN

            $stmt = $pdo->prepare("

                SELECT COALESCE(SUM(monto_neto_cliente - monto_pagado), 0) AS total

                FROM deudas_clientes

                WHERE estado IN ('pendiente', 'parcial') AND activo = 1 AND moneda = :moneda

            ");

            $stmt->execute(['moneda' => $moneda]);

            $totalDeudas = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

            $totalCapital += $totalDeudas * $paridadPromedio; // convertir USD/USDT -> MXN



            // 3. PROVEEDOR en MXN: valor_mxn acumulado conectado directamente al proveedor
            $stmt = $pdo->prepare("
                SELECT COALESCE(SUM(c.valor_mxn), 0) AS total
                FROM proveedores p
                INNER JOIN cuentas c ON (p.cuenta_usd_id = c.id OR p.cuenta_usdt_id = c.id)
                WHERE p.activo = 1 AND c.tipo_moneda = :moneda
            ");
            $stmt->execute(['moneda' => $moneda]);
            $totalProveedoresAdeudos = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];



            // 4. DIFERENCIA = Saldos - Capital - Proveedor
            $totalSaldos = round($totalSaldos, 2);
            $totalCapital = round($totalCapital, 2);
            $totalProveedoresAdeudos = round($totalProveedoresAdeudos, 2);

            $diferencia = round($totalSaldos - $totalCapital - $totalProveedoresAdeudos, 2);

            // Absorber discrepancias de redondeo
            if (abs($diferencia) <= 0.05) {
                $diferencia = 0.00;
            }

            echo json_encode([

                'diferencia'      => $diferencia,

                'total_saldos'    => $totalSaldos,

                'total_capital'   => $totalCapital,

                'total_prestamos' => $totalProveedoresAdeudos,

                'total_proveedor' => $totalProveedoresAdeudos

            ]);

            exit;

        }



        // ── Para MXN / TODAS ──────────────────────────────────────────────

        $sql = "SELECT COALESCE(SUM(saldo_actual), 0) AS total

                FROM cuentas

                WHERE activo = 1 

                AND tipo_cuenta IN (1, 2)" . $whereMoneda;

        

        if ($moneda !== 'TODAS') {

            $stmt = $pdo->prepare($sql);

            $stmt->execute(['moneda' => $moneda]);

        } else {

            $stmt = $pdo->query($sql);

        }

        $totalSaldos = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Sumar préstamos otorgados pendientes al activo
        $totalPrestamosOtorgados = 0;
        if ($moneda === 'MXN' || $moneda === 'TODAS') {
            $stmtPrestOtorg = $pdo->query("SELECT COALESCE(SUM(monto_total - monto_pagado), 0) AS total FROM prestamos_otorgados WHERE estado IN ('pendiente', 'parcial') AND activo = 1");
            $totalPrestamosOtorgados = (float)$stmtPrestOtorg->fetch(PDO::FETCH_ASSOC)['total'];
            $totalSaldos += $totalPrestamosOtorgados;

            // Sumar gastos activos que NO descontaron de Comisiones AMIRI
            $stmtGastos = $pdo->query("SELECT COALESCE(SUM(monto), 0) AS total FROM gastos g WHERE g.activo = 1 AND (SELECT COUNT(*) FROM movimientos WHERE gasto_id = g.id AND cuenta_origen_id = (SELECT id FROM cuentas WHERE tipo_cuenta = 3 AND activo = 1 LIMIT 1)) = 0");
            $totalSaldos += (float)$stmtGastos->fetch(PDO::FETCH_ASSOC)['total'];
        }

        // 2. Total CAPITAL (tipo 3=Capital + tipo 4=Cliente + tipo 5=Promotor)

        // Excluir "Utilidades AMIRI" (id=2): pertenece a la diferencia de flujo-divisas

        $sql = "SELECT COALESCE(SUM(saldo_actual), 0) AS total

                FROM cuentas

                WHERE activo = 1

                AND tipo_cuenta IN (3, 4, 5)

                AND id != 2" . $whereMoneda;

        

        if ($moneda !== 'TODAS') {

            $stmt = $pdo->prepare($sql);

            $stmt->execute(['moneda' => $moneda]);

        } else {

            $stmt = $pdo->query($sql);

        }

        $totalCapital = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        

        // Para USD/USDT: agregar deudas de clientes de deudas_clientes

        if ($moneda === 'USD' || $moneda === 'USDT') {

            $sqlDeudas = "SELECT COALESCE(SUM(monto_neto_cliente - monto_pagado), 0) AS total

                         FROM deudas_clientes

                         WHERE estado IN ('pendiente', 'parcial') 

                         AND activo = 1 

                         AND moneda = :moneda";

            $stmtDeudas = $pdo->prepare($sqlDeudas);

            $stmtDeudas->execute(['moneda' => $moneda]);

            $totalCapital += (float)$stmtDeudas->fetch(PDO::FETCH_ASSOC)['total'];

        }

        

        // 3. Total PRÉSTAMOS (prestamistas + adeudos a proveedores tipo_cuenta 6)

        $sql = "SELECT COALESCE(SUM(pp.monto - pp.monto_pagado), 0) AS total

                FROM deudas_empresa pp

                LEFT JOIN cuentas c ON pp.cuenta_id = c.id

                WHERE pp.estado IN ('pendiente','parcial') AND pp.activo = 1";

        

        if ($moneda !== 'TODAS') {

            $sql .= " AND c.tipo_moneda = :moneda";

            $stmt = $pdo->prepare($sql);

            $stmt->execute(['moneda' => $moneda]);

        } else {

            $stmt = $pdo->query($sql);

        }

        $totalPrestamosActuales = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        

        // Sumar también las cuentas de proveedores que representan adeudos
        $sql = "SELECT COALESCE(SUM(c.valor_mxn), 0) AS total
                FROM proveedores p
                INNER JOIN cuentas c ON (p.cuenta_usd_id = c.id OR p.cuenta_usdt_id = c.id)
                WHERE p.activo = 1";
        
        if ($moneda !== 'TODAS') {
            $sql .= " AND c.tipo_moneda = :moneda";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['moneda' => $moneda]);
        } else {
            $stmt = $pdo->query($sql);
        }
        $totalProveedoresAdeudos = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        

        $totalPrestamos = $totalPrestamosActuales + $totalProveedoresAdeudos;

        

        // Query préstamos otorgados pendientes para explicación
        $totalPrestamosOtorgados = 0;
        if ($moneda === 'MXN' || $moneda === 'TODAS') {
            $stmtPO = $pdo->query("SELECT COALESCE(SUM(monto_total - monto_pagado), 0) AS total FROM prestamos_otorgados WHERE estado IN ('pendiente', 'parcial') AND activo = 1");
            $totalPrestamosOtorgados = (float)$stmtPO->fetch(PDO::FETCH_ASSOC)['total'];
        }

        $totalSaldos = round($totalSaldos, 2);
        $totalCapital = round($totalCapital, 2);
        $totalPrestamos = round($totalPrestamos, 2);
        $totalProveedoresAdeudos = round($totalProveedoresAdeudos, 2);

        // 4. DIFERENCIA
        if ($moneda === 'USD' || $moneda === 'USDT') {
            $diferencia = round($totalSaldos + $totalCapital - $totalProveedoresAdeudos, 2);
        } else {
            $diferencia = round($totalSaldos - ($totalCapital + $totalPrestamos), 2);
        }

        // Construir explicación
        $explicacion = [];
        $componentes = [];
        if ($totalPrestamosOtorgados > 0) {
            $stmtDet = $pdo->query("SELECT persona_nombre, (monto_total - monto_pagado) AS pendiente FROM prestamos_otorgados WHERE estado IN ('pendiente', 'parcial') AND activo = 1 ORDER BY persona_nombre");
            $detalles = [];
            foreach ($stmtDet as $r) {
                $detalles[] = $r['persona_nombre'] . ': $' . number_format($r['pendiente'], 2);
            }
            $explicacion[] = [
                'tipo' => 'prestamos_otorgados',
                'concepto' => 'Préstamos otorgados pendientes',
                'monto' => $totalPrestamosOtorgados,
                'detalle' => implode(', ', $detalles)
            ];
            $componentes[] = 'Préstamos otorgados: $' . number_format($totalPrestamosOtorgados, 2);
        }
        $diferenciaBase = round($diferencia, 2);
        if (abs($diferenciaBase) > 0.05) {
            $explicacion[] = [
                'tipo' => 'diferencia_neta',
                'concepto' => 'Diferencia neta sin asignar',
                'monto' => $diferenciaBase,
                'detalle' => 'Combinación de saldos de cuentas, capital y obligaciones'
            ];
            $componentes[] = 'Diferencia neta: $' . number_format($diferenciaBase, 2);
        }

        // Absorber discrepancias de redondeo
        if (abs($diferencia) <= 0.05) {
            $diferencia = 0.00;
        }
        
        echo json_encode([

            'diferencia' => $diferencia,

            'total_saldos' => $totalSaldos,

            'total_capital' => $totalCapital,

            'total_prestamos' => $totalPrestamos,

            'total_proveedor' => $totalProveedoresAdeudos,

            'explicacion'     => $explicacion,

            'resumen'         => !empty($componentes) ? implode(' | ', $componentes) : ''

        ]);

        exit;

    }

    

    // Si pide prestamos detallados

    if (isset($_GET['tipo']) && $_GET['tipo'] === 'prestamos') {

        

        $resultado = [];

        

        // Si es MXN, retornar prestamistas

        if ($moneda === 'MXN' || $moneda === 'TODAS') {

            $stmt = $pdo->query("

                SELECT p.id, p.nombre, COALESCE(SUM(pp.monto - pp.monto_pagado), 0) AS total_adeudado, 'Prestamista' AS tipo, 'MXN' AS tipo_moneda

                FROM prestamistas p

                LEFT JOIN deudas_empresa pp ON p.id = pp.prestamista_id 

                    AND pp.estado IN ('pendiente', 'parcial')

                    AND pp.activo = 1

                WHERE p.activo = 1

                GROUP BY p.id, p.nombre

                HAVING total_adeudado > 0

                ORDER BY total_adeudado DESC

            ");

            $resultado = array_merge($resultado, $stmt->fetchAll(PDO::FETCH_ASSOC));

        }

        

        // Cajas de proveedores (tipo 6) - para USD/USDT

        if ($moneda !== 'MXN') {

            $sql = "SELECT nombre, saldo_actual AS total_adeudado, 'Proveedor' AS tipo, tipo_moneda

                    FROM cuentas

                    WHERE activo = 1 

                    AND tipo_cuenta = 6

                    AND saldo_actual > 0" . $whereMoneda . "

                    ORDER BY nombre";

            

            if ($moneda !== 'TODAS') {

                $stmt = $pdo->prepare($sql);

                $stmt->execute(['moneda' => $moneda]);

            } else {

                $stmt = $pdo->query($sql);

            }

            $resultado = array_merge($resultado, $stmt->fetchAll(PDO::FETCH_ASSOC));

        }

        

        echo json_encode($resultado);

        exit;

    }

    

    // Si pide proveedores (para divisas)

    if (isset($_GET['tipo']) && $_GET['tipo'] === 'proveedor') {

        $condMonedaUsd  = $moneda === 'TODAS' || $moneda === 'USD'  ? "AND p.acepta_usd = 1"  : "AND 1=0";

        $condMonedaUsdt = $moneda === 'TODAS' || $moneda === 'USDT' ? "AND p.acepta_usdt = 1" : "AND 1=0";



        $sql = "

            SELECT p.id, p.nombre, 'USD' AS tipo_moneda,

                   COALESCE(c.saldo_actual, 0) AS saldo_actual,

                   COALESCE(c.valor_mxn, 0) AS valor_mxn

            FROM proveedores p

            LEFT JOIN cuentas c ON p.cuenta_usd_id = c.id

            WHERE p.activo = 1 $condMonedaUsd AND COALESCE(c.saldo_actual, 0) > 0.001



            UNION ALL



            SELECT p.id, p.nombre, 'USDT' AS tipo_moneda,

                   COALESCE(c.saldo_actual, 0) AS saldo_actual,

                   COALESCE(c.valor_mxn, 0) AS valor_mxn

            FROM proveedores p

            LEFT JOIN cuentas c ON p.cuenta_usdt_id = c.id

            WHERE p.activo = 1 $condMonedaUsdt AND COALESCE(c.saldo_actual, 0) > 0.001



            ORDER BY nombre

        ";



        $stmt = $pdo->query($sql);

        echo json_encode(['proveedores' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);

        exit;

    }

    

    // Si pide saldos (cuentas bancarias/cajas)

    if (isset($_GET['tipo']) && $_GET['tipo'] === 'saldos') {



        // Para USD/USDT: mostrar wallet de divisas (tipo 1,2 USD/USDT) + cuentas MXN (donde entra el pago del cliente)

        if ($moneda === 'USD' || $moneda === 'USDT') {

            $stmt = $pdo->prepare("

                SELECT

                    c.nombre,

                    c.banco,

                    c.saldo_actual,

                    ct.descripcion AS tipo_cuenta_nombre,

                    c.tipo_moneda,

                    COALESCE((

                        SELECT SUM(im.cantidad_restante * im.paridad)

                        FROM inventario_monedas im

                        WHERE im.cuenta_id = c.id AND im.activo = 1

                    ), 0) AS valor_mxn

                FROM cuentas c

                LEFT JOIN tipos_cuenta ct ON c.tipo_cuenta = ct.id

                WHERE c.activo = 1

                AND c.tipo_cuenta IN (1, 2)

                AND (c.tipo_moneda = :moneda OR c.tipo_moneda = 'MXN')

                ORDER BY c.tipo_moneda DESC, c.nombre

            ");

            $stmt->execute(['moneda' => $moneda]);

            echo json_encode(['cuentas' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);

            exit;

        }



        $sql = "SELECT

                    c.nombre,

                    c.banco,

                    c.saldo_actual,

                    ct.descripcion AS tipo_cuenta_nombre,

                    c.tipo_moneda,

                    COALESCE((

                        SELECT SUM(im.cantidad_restante * im.paridad)

                        FROM inventario_monedas im

                        WHERE im.cuenta_id = c.id AND im.activo = 1

                    ), 0) AS valor_mxn

                FROM cuentas c

                LEFT JOIN tipos_cuenta ct ON c.tipo_cuenta = ct.id

                WHERE c.activo = 1

                AND c.tipo_cuenta IN (1, 2)" . $whereMoneda . "

                ORDER BY c.nombre";



        if ($moneda !== 'TODAS') {

            $stmt = $pdo->prepare($sql);

            $stmt->execute(['moneda' => $moneda]);

        } else {

            $stmt = $pdo->query($sql);

        }



        echo json_encode(['cuentas' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);

        exit;

    }

    

    // Si pide capital

    if (isset($_GET['tipo']) && $_GET['tipo'] === 'capital') {



        // ── TODAS: capital unificado (MXN + divisas con valor_mxn) ──────────

        if ($moneda === 'TODAS') {

            $cuentas = [];



            // 1. Cuentas tipo 3 (Capital/Utilidades) — todas las monedas, incluye Utilidades AMIRI (id=2)

            $stmt = $pdo->query("

                SELECT c.nombre, c.saldo_actual, ct.descripcion AS tipo_cuenta_nombre,

                       c.tipo_moneda, c.saldo_actual AS valor_mxn,

                       NULL AS cliente_id, 0 AS es_deuda_cliente,

                       NULL AS promotor_id, 0 AS es_deuda_promotor

                FROM cuentas c

                LEFT JOIN tipos_cuenta ct ON c.tipo_cuenta = ct.id

                WHERE c.activo = 1 AND c.tipo_cuenta = 3

                ORDER BY c.nombre

            ");

            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) {

                $cuentas[] = $c;

            }



            // 2. Clientes MXN con deuda pendiente

            $stmt = $pdo->query("

                SELECT cl.id AS cliente_id, cl.nombre,

                       SUM(dc.monto_neto_cliente - dc.monto_pagado) AS total_pendiente

                FROM deudas_clientes dc

                INNER JOIN clientes cl ON dc.cliente_id = cl.id

                WHERE dc.estado IN ('pendiente','parcial') AND dc.activo = 1

                  AND (dc.moneda = 'MXN' OR dc.moneda IS NULL)

                GROUP BY cl.id, cl.nombre

                HAVING total_pendiente > 0

                ORDER BY total_pendiente DESC

            ");

            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) {

                $cuentas[] = [

                    'nombre'             => $c['nombre'] . ' (Cliente)',

                    'saldo_actual'       => floatval($c['total_pendiente']),

                    'tipo_cuenta_nombre' => 'Cliente',

                    'tipo_moneda'        => 'MXN',

                    'valor_mxn'          => floatval($c['total_pendiente']),

                    'cliente_id'         => $c['cliente_id'],

                    'es_deuda_cliente'   => 1

                ];

            }



            // 3. Promotores externos con comisiones acumuladas

            $stmt = $pdo->query("

                SELECT p.id AS promotor_id, p.nombre,

                       COALESCE(c.saldo_actual, 0) AS saldo

                FROM promotores p

                LEFT JOIN cuentas c ON p.cuenta_id = c.id

                WHERE p.activo = 1 AND p.es_interno = 0 AND c.saldo_actual > 0.01

                ORDER BY saldo DESC

            ");

            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $p) {

                $cuentas[] = [

                    'nombre'             => $p['nombre'] . ' (Promotor)',

                    'saldo_actual'       => floatval($p['saldo']),

                    'tipo_cuenta_nombre' => 'Promotor',

                    'tipo_moneda'        => 'MXN',

                    'valor_mxn'          => floatval($p['saldo']),

                    'promotor_id'        => $p['promotor_id'],

                    'es_deuda_promotor'  => 1

                ];

            }



            // 4. Clientes USD/USDT con deuda pendiente → convertido a MXN por paridad FIFO

            foreach (['USD', 'USDT'] as $div) {

                $stmtP = $pdo->prepare("

                    SELECT COALESCE(SUM(im.cantidad_restante*im.paridad)/NULLIF(SUM(im.cantidad_restante),0),0) AS p

                    FROM inventario_monedas im

                    INNER JOIN cuentas c ON im.cuenta_id = c.id

                    WHERE im.activo = 1 AND c.tipo_moneda = :m

                ");

                $stmtP->execute(['m' => $div]);

                $paridad = (float)$stmtP->fetch(PDO::FETCH_ASSOC)['p'];



                $stmtCli = $pdo->prepare("

                    SELECT cl.id AS cliente_id, cl.nombre,

                           SUM(dc.monto_neto_cliente - dc.monto_pagado) AS total_pendiente

                    FROM deudas_clientes dc

                    INNER JOIN clientes cl ON dc.cliente_id = cl.id

                    WHERE dc.estado IN ('pendiente','parcial') AND dc.activo = 1 AND dc.moneda = :m

                    GROUP BY cl.id, cl.nombre

                    HAVING total_pendiente > 0

                    ORDER BY total_pendiente DESC

                ");

                $stmtCli->execute(['m' => $div]);

                foreach ($stmtCli->fetchAll(PDO::FETCH_ASSOC) as $c) {

                    $pending = floatval($c['total_pendiente']);

                    $cuentas[] = [

                        'nombre'             => $c['nombre'] . ' (Cliente)',

                        'saldo_actual'       => $pending,

                        'tipo_cuenta_nombre' => 'Cliente',

                        'tipo_moneda'        => $div,

                        'valor_mxn'          => $pending * $paridad,

                        'cliente_id'         => $c['cliente_id'],

                        'es_deuda_cliente'   => 1

                    ];

                }

            }



            // Filtrar saldos en cero

            $cuentas = array_values(array_filter($cuentas, fn($c) => abs(floatval($c['saldo_actual'])) >= 0.01));

            echo json_encode(['cuentas' => $cuentas]);

            exit;

        }



        // Para flujo-divisas (USD/USDT): cuentas Capital (tipo 3, Utilidades AMIRI) + deudas_clientes en divisa

        // Las cuentas MXN bancaria/caja aparecen en SALDOS (donde entra el pago del cliente)

        if ($moneda === 'USD' || $moneda === 'USDT') {

            $stmt = $pdo->query("

                SELECT c.nombre, c.saldo_actual, ct.descripcion AS tipo_cuenta_nombre, c.tipo_moneda, NULL AS cliente_id, 0 AS es_deuda_cliente

                FROM cuentas c

                LEFT JOIN tipos_cuenta ct ON c.tipo_cuenta = ct.id

                WHERE c.activo = 1

                AND c.tipo_cuenta = 3

                ORDER BY c.nombre

            ");

            $cuentas = $stmt->fetchAll(PDO::FETCH_ASSOC);



            // Obtener paridad promedio del inventario para convertir divisa -> MXN

            $stmtParidad = $pdo->prepare("

                SELECT COALESCE(

                    SUM(im.cantidad_restante * im.paridad) / NULLIF(SUM(im.cantidad_restante), 0),

                    0

                ) AS paridad_promedio

                FROM inventario_monedas im

                INNER JOIN cuentas c ON im.cuenta_id = c.id

                WHERE im.activo = 1 AND c.tipo_moneda = :moneda

            ");

            $stmtParidad->execute(['moneda' => $moneda]);

            $paridadPromedio = (float)$stmtParidad->fetch(PDO::FETCH_ASSOC)['paridad_promedio'];



            // Agregar deudas de clientes (lo que debemos entregar en USD/USDT)

            $stmtDeudas = $pdo->prepare("

                SELECT

                    cl.id AS cliente_id,

                    cl.nombre,

                    SUM(dc.monto_neto_cliente - dc.monto_pagado) AS total_pendiente

                FROM deudas_clientes dc

                INNER JOIN clientes cl ON dc.cliente_id = cl.id

                WHERE dc.estado IN ('pendiente', 'parcial')

                AND dc.activo = 1

                AND dc.moneda = :moneda

                GROUP BY cl.id, cl.nombre

                HAVING total_pendiente > 0

                ORDER BY total_pendiente DESC

            ");

            $stmtDeudas->execute(['moneda' => $moneda]);

            foreach ($stmtDeudas->fetchAll(PDO::FETCH_ASSOC) as $deuda) {

                $pendiente = floatval($deuda['total_pendiente']);

                $cuentas[] = [

                    'nombre'              => $deuda['nombre'] . ' (Cliente)',

                    'saldo_actual'        => $pendiente,

                    'tipo_cuenta_nombre'  => 'Cliente',

                    'tipo_moneda'         => $moneda,

                    'cliente_id'          => $deuda['cliente_id'],

                    'es_deuda_cliente'    => 1,

                    'valor_mxn'           => $pendiente * $paridadPromedio

                ];

            }



            echo json_encode(['cuentas' => $cuentas]);

            exit;

        }



        // Para MXN/TODAS: comportamiento original (cuentas Capital tipo 3, siempre con Utilidades AMIRI)

        $whereCapital = $moneda !== 'TODAS'

            ? " AND (tipo_moneda = :moneda OR c.id = 2)"

            : "";

        $sql = "SELECT c.nombre, c.saldo_actual, ct.descripcion AS tipo_cuenta_nombre, c.tipo_moneda

                FROM cuentas c

                LEFT JOIN tipos_cuenta ct ON c.tipo_cuenta = ct.id

                WHERE c.activo = 1

                AND c.tipo_cuenta IN (3)" . $whereCapital . "

                ORDER BY c.nombre";



        if ($moneda !== 'TODAS') {

            $stmt = $pdo->prepare($sql);

            $stmt->execute(['moneda' => $moneda]);

        } else {

            $stmt = $pdo->query($sql);

        }



        echo json_encode(['cuentas' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);

        exit;

    }



    // 1️⃣ CAJAS: Solo cuentas tipo 1 (Bancaria) y tipo 2 (Caja)

    $sql = "SELECT c.nombre, c.saldo_actual, ct.descripcion AS tipo, c.tipo_moneda

            FROM cuentas c

            LEFT JOIN tipos_cuenta ct ON c.tipo_cuenta = ct.id

            WHERE c.activo = 1 

            AND c.tipo_cuenta IN (1, 2)" . $whereMoneda . "

            ORDER BY c.nombre";

    

    if ($moneda !== 'TODAS') {

        $stmt = $pdo->prepare($sql);

        $stmt->execute(['moneda' => $moneda]);

    } else {

        $stmt = $pdo->query($sql);

    }

    $saldos_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    

    // Procesar saldos: mostrar montos nativos para USD/USDT, valores para MXN

    // Filtrar saldos en cero

    $saldos = array_filter(array_map(function($cuenta) {

        // Si la cuenta es USD/USDT, el saldo ya es el monto nativo

        // Si es MXN, es el valor en pesos

        return [

            'nombre' => $cuenta['nombre'],

            'saldo_actual' => floatval($cuenta['saldo_actual']),

            'tipo' => $cuenta['tipo'],

            'tipo_moneda' => $cuenta['tipo_moneda']

        ];

    }, $saldos_raw), function($cuenta) {

        return abs($cuenta['saldo_actual']) >= 0.01; // Filtrar saldos menores a 1 centavo

    });



    // 2️⃣ CAPITAL: Comisiones AMIRI + Cajas de clientes (tipo 4) + Cajas de promotores (tipo 5)

    // Ya no se incluyen préstamos en el capital

    $totalPrestamos = 0;

    

    // Obtener IDs de CUENTAS de clientes con ventas de efectivo pendientes (para excluir esas cuentas después)

    $cuentasClientesConVentasPendientes = [];

    if ($moneda === 'MXN' || $moneda === 'TODAS') {

        $stmt = $pdo->query("

            SELECT DISTINCT cl.cuenta_id

            FROM deudas_clientes vec

            INNER JOIN clientes cl ON vec.cliente_id = cl.id

            WHERE vec.estado IN ('pendiente', 'parcial')

            AND vec.activo = 1

            AND cl.cuenta_id IS NOT NULL

        ");

        $cuentasClientesConVentasPendientes = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'cuenta_id');

    }

    

    // Segundo: Cuentas tipo 3 (Capital), 4 (Cliente) y 5 (Promotor)

    // Excluir "Utilidades AMIRI" (id=2): solo aparece en flujo-divisas.php

    $sql = "SELECT c.id as cuenta_id, c.nombre, c.saldo_actual, ct.descripcion AS tipo, c.tipo_moneda

            FROM cuentas c

            LEFT JOIN tipos_cuenta ct ON c.tipo_cuenta = ct.id

            WHERE c.activo = 1

            AND c.tipo_cuenta IN (3, 4, 5)

            AND c.id != 2" . $whereMoneda . "

            ORDER BY 

                CASE c.tipo_cuenta

                    WHEN 3 THEN 1

                    WHEN 5 THEN 2

                    WHEN 4 THEN 3

                END,

                c.nombre";

    

    if ($moneda !== 'TODAS') {

        $stmt = $pdo->prepare($sql);

        $stmt->execute(['moneda' => $moneda]);

    } else {

        $stmt = $pdo->query($sql);

    }

    $capitalCuentas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    

    // Filtrar: excluir cuentas de clientes que tienen ventas de efectivo pendientes

    $capital = array_filter($capitalCuentas, function($cuenta) use ($cuentasClientesConVentasPendientes) {

        // Excluir cuentas de cliente con ventas pendientes

        if ($cuenta['tipo'] === 'Cliente' && in_array($cuenta['cuenta_id'], $cuentasClientesConVentasPendientes)) {

            return false;

        }

        // Excluir cuentas base de promotor; se agregarán como enlace en la sección de comisiones

        if ($cuenta['tipo'] === 'Promotor') {

            return false;

        }

        return true;

    });

    

    // Obtener clientes con ventas de efectivo pendientes y agregarlos como deuda con enlace

    $clientesConDeuda = [];

    

    // Para USD y USDT, las deudas están en deudas_clientes con la moneda correspondiente

    if ($moneda === 'USD' || $moneda === 'USDT') {

        $sql = "SELECT 

                    c.id AS cliente_id,

                    c.nombre,

                    SUM(vec.monto_neto_cliente - vec.monto_pagado) AS total_pendiente,

                    vec.moneda

                FROM deudas_clientes vec

                INNER JOIN clientes c ON vec.cliente_id = c.id

                WHERE vec.estado IN ('pendiente', 'parcial')

                AND vec.activo = 1

                AND vec.moneda = :moneda

                GROUP BY c.id, c.nombre, vec.moneda

                HAVING total_pendiente > 0

                ORDER BY total_pendiente DESC";

        

        $stmt = $pdo->prepare($sql);

        $stmt->execute(['moneda' => $moneda]);

        $clientesConDeuda = $stmt->fetchAll(PDO::FETCH_ASSOC);

        

        // Agregar clientes con deuda al array capital

        foreach ($clientesConDeuda as $cliente) {

            $capital[] = [

                'nombre' => $cliente['nombre'] . ' (Cliente)',

                'saldo_actual' => floatval($cliente['total_pendiente']),

                'tipo' => 'Cliente',

                'tipo_moneda' => $moneda,

                'cliente_id' => $cliente['cliente_id'],

                'es_deuda_cliente' => true

            ];

        }

    } elseif ($moneda === 'MXN' || $moneda === 'TODAS') {

        $sql = "SELECT 

                    c.id AS cliente_id,

                    c.nombre,

                    SUM(vec.monto_neto_cliente - vec.monto_pagado) AS total_pendiente

                FROM deudas_clientes vec

                INNER JOIN clientes c ON vec.cliente_id = c.id

                WHERE vec.estado IN ('pendiente', 'parcial')

                AND vec.activo = 1

                AND (vec.moneda = 'MXN' OR vec.moneda IS NULL)

                GROUP BY c.id, c.nombre

                HAVING total_pendiente > 0

                ORDER BY total_pendiente DESC";

        

        $stmt = $pdo->query($sql);

        $clientesConDeuda = $stmt->fetchAll(PDO::FETCH_ASSOC);

        

        // Agregar clientes con deuda al array capital

        foreach ($clientesConDeuda as $cliente) {

            $capital[] = [

                'nombre' => $cliente['nombre'] . ' (Cliente)',

                'saldo_actual' => floatval($cliente['total_pendiente']),

                'tipo' => 'Cliente',

                'tipo_moneda' => 'MXN',

                'cliente_id' => $cliente['cliente_id'], // Para enlace a detalle-cliente.php

                'es_deuda_cliente' => true // Flag para identificar en el frontend

            ];

        }

    }



    // Obtener promotores con comisiones acumuladas y agregarlos como deuda con enlace

    // EXCLUIR promotores internos (es_interno = 1) ya que sus comisiones van a "Comisiones AMIRI"

    $promotoresConComisiones = [];

    if ($moneda === 'MXN' || $moneda === 'TODAS') {

        $sql = "SELECT 

                    p.id AS promotor_id,

                    p.nombre,

                    COALESCE(c.saldo_actual, 0) AS comisiones_acumuladas

                FROM promotores p

                LEFT JOIN cuentas c ON p.cuenta_id = c.id

                WHERE p.activo = 1

                AND p.es_interno = 0

                AND c.saldo_actual > 0.01

                ORDER BY c.saldo_actual DESC";

        

        $stmt = $pdo->query($sql);

        $promotoresConComisiones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        

        // Agregar promotores con comisiones al array capital

        foreach ($promotoresConComisiones as $promotor) {

            $capital[] = [

                'nombre' => $promotor['nombre'] . ' (Promotor)',

                'saldo_actual' => floatval($promotor['comisiones_acumuladas']),

                'tipo' => 'Promotor',

                'tipo_moneda' => 'MXN',

                'promotor_id' => $promotor['promotor_id'], // Para enlace a detalle-promotor.php

                'es_deuda_promotor' => true // Flag para identificar en el frontend

            ];

        }

    }

    

    // Filtrar capital con saldo en cero y marcar promotores con enlace

    $capital = array_values(array_filter(array_map(function($cuenta) {

        // Si es promotor, marcar para enlace

        if (($cuenta['tipo'] ?? '') === 'Promotor' && !empty($cuenta['promotor_id'])) {

            $cuenta['es_deuda_promotor'] = true;

        }

        return $cuenta;

    }, $capital), function($cuenta) {

        return abs(floatval($cuenta['saldo_actual'])) >= 0.01;

    }));



    // Respuesta

    echo json_encode([

        'saldos' => array_values($saldos), // Reindexar después de filtrar

        'capital' => $capital // Ya reindexado

    ]);



} catch (PDOException $e) {

    error_log("Error en flujo-resumen.php: " . $e->getMessage());

    http_response_code(500);

    echo json_encode(['error' => 'Error interno del servidor']);

}

