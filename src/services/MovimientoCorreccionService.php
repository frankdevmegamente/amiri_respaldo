<?php

/**

 * MovimientoCorreccionService

 * 

 * Servicio para manejar correcciones de movimientos financieros.

 * Los movimientos NUNCA se editan directamente - se crean movimientos de reverso

 * y luego movimientos correctivos para mantener la trazabilidad.

 */



class MovimientoCorreccionService {

    private $pdo;

    

    public function __construct(PDO $pdo) {

        $this->pdo = $pdo;

    }

    

    /**

     * Anula un movimiento creando un movimiento de reverso

     * 

     * @param int $movimiento_id ID del movimiento a anular

     * @param string $motivo Razón de la anulación

     * @param int $usuario_id ID del usuario que anula

     * @return array Resultado de la operación

     */

    public function anularMovimiento($movimiento_id, $motivo, $usuario_id) {

        try {

            $this->pdo->beginTransaction();

            

            // Obtener el movimiento original

            $original = $this->obtenerMovimiento($movimiento_id);

            if (!$original) {

                throw new Exception("Movimiento no encontrado");

            }

            

            if ($original['estado'] !== 'activo') {

                throw new Exception("Solo se pueden anular movimientos activos");

            }

            

            // Crear el grupo_id para la corrección

            $stmt = $this->pdo->query("SELECT COALESCE(MAX(grupo_id), 0) + 1 FROM movimientos");

            $nuevo_grupo = $stmt->fetchColumn();

            

            // Crear movimiento de REVERSO (monto negativo para revertir el efecto)

            $stmt = $this->pdo->prepare("

                INSERT INTO movimientos (

                    grupo_id, fecha, servicio_id, tipo, moneda, monto,

                    cuenta_origen_id, cuenta_destino_id, cliente_id, promotor_id,

                    proveedor_id, prestamista_id, paridad, descripcion,

                    es_reverso, movimiento_original_id, motivo_correccion,

                    usuario_id, estado, activo

                ) VALUES (

                    ?, CURDATE(), ?, 'ajuste', ?, ?,

                    ?, ?, ?, ?,

                    ?, ?, ?, ?,

                    1, ?, ?,

                    ?, 'activo', 1

                )

            ");

            

            // El monto del reverso tiene signo contrario

            $monto_reverso = -$original['monto'];

            $descripcion_reverso = "REVERSO: " . ($original['descripcion'] ?? 'Movimiento #' . $movimiento_id);

            

            $stmt->execute([

                $nuevo_grupo,

                $original['servicio_id'],

                $original['moneda'],

                $monto_reverso,

                $original['cuenta_destino_id'],  // Invertir origen/destino

                $original['cuenta_origen_id'],

                $original['cliente_id'],

                $original['promotor_id'],

                $original['proveedor_id'],

                $original['prestamista_id'],

                $original['paridad'],

                $descripcion_reverso,

                $movimiento_id,

                $motivo,

                $usuario_id

            ]);

            

            $reverso_id = $this->pdo->lastInsertId();

            

            // Revertir los saldos de las cuentas afectadas

            $this->revertirSaldos($original, $reverso_id, $descripcion_reverso);

            

            // Marcar el movimiento original como anulado

            $stmt = $this->pdo->prepare("

                UPDATE movimientos 

                SET estado = 'anulado',

                    motivo_correccion = ?,

                    corregido_por_usuario_id = ?,

                    corregido_en = NOW()

                WHERE id = ?

            ");

            $stmt->execute([$motivo, $usuario_id, $movimiento_id]);

            

            // Si hay movimientos relacionados (mismo grupo_id), también anularlos

            if ($original['grupo_id']) {

                $this->anularMovimientosRelacionados($original['grupo_id'], $movimiento_id, $motivo, $usuario_id, $nuevo_grupo);

                // Restaurar inventario FIFO consumido por el grupo (ventas anuladas).
                // Sin esto quedaban consumos huérfanos y la ecuación mostraba faltante.
                $fifoRestaurado = $this->restaurarConsumosFIFO($original['grupo_id']);

            }

            

            // Registrar auditoría

            $this->registrarAuditoria([

                'accion' => 'ANULAR_MOVIMIENTO',

                'modulo' => 'flujo',

                'tabla' => 'movimientos',

                'registro_id' => $movimiento_id,

                'descripcion' => "Anuló movimiento #$movimiento_id: $motivo" . (!empty($fifoRestaurado) ? ". FIFO restaurado: " . json_encode($fifoRestaurado, JSON_UNESCAPED_UNICODE) : ""),

                'datos_anteriores' => $original,

                'datos_nuevos' => ['reverso_id' => $reverso_id, 'motivo' => $motivo, 'fifo_restaurado' => $fifoRestaurado ?? []]

            ]);

            

            $this->pdo->commit();

            

            return [

                'success' => true,

                'message' => 'Movimiento anulado correctamente',

                'reverso_id' => $reverso_id,

                'movimiento_original' => $movimiento_id

            ];

            

        } catch (Exception $e) {

            if ($this->pdo->inTransaction()) {

                $this->pdo->rollBack();

            }

            return [

                'success' => false,

                'error' => $e->getMessage()

            ];

        }

    }

    

    /**

     * Corrige un movimiento: crea reverso del original + nuevo movimiento correcto

     * 

     * @param int $movimiento_id ID del movimiento a corregir

     * @param array $datos_corregidos Nuevos datos correctos

     * @param string $motivo Razón de la corrección

     * @param int $usuario_id ID del usuario que corrige

     * @return array Resultado de la operación

     */

    public function corregirMovimiento($movimiento_id, $datos_corregidos, $motivo, $usuario_id) {

        try {

            $this->pdo->beginTransaction();

            

            // Obtener el movimiento original

            $original = $this->obtenerMovimiento($movimiento_id);

            if (!$original) {

                throw new Exception("Movimiento no encontrado");

            }

            

            if ($original['estado'] !== 'activo') {

                throw new Exception("Solo se pueden corregir movimientos activos");

            }

            // Candado FIFO: corregir monto/cuentas de una pata de compra/venta de divisas
            // rompería el inventario (el reemplazo no consume/produce lotes). Esos casos
            // van por anular grupo + registrar la operación de nuevo.
            $sidCorr = (int)($original['servicio_id'] ?? 0);
            if (in_array($sidCorr, [2, 3, 5, 6], true)) {
                $cambiaMonto = array_key_exists('monto', $datos_corregidos) && (float)$datos_corregidos['monto'] !== (float)$original['monto'];
                $cambiaOrigen = array_key_exists('cuenta_origen_id', $datos_corregidos) && (int)$datos_corregidos['cuenta_origen_id'] !== (int)$original['cuenta_origen_id'];
                $cambiaDestino = array_key_exists('cuenta_destino_id', $datos_corregidos) && (int)$datos_corregidos['cuenta_destino_id'] !== (int)$original['cuenta_destino_id'];
                if ($cambiaMonto || $cambiaOrigen || $cambiaDestino) {
                    throw new Exception("No se puede corregir monto/cuentas de una compra/venta de divisas (mov #$movimiento_id). Anula el grupo #{$original['grupo_id']} y registra la operación de nuevo.");
                }
            }

            

            // Crear nuevo grupo_id

            $stmt = $this->pdo->query("SELECT COALESCE(MAX(grupo_id), 0) + 1 FROM movimientos");

            $nuevo_grupo = $stmt->fetchColumn();

            

            // 1. Crear movimiento de REVERSO

            $monto_reverso = -$original['monto'];

            $descripcion_reverso = "REVERSO para corrección: #$movimiento_id";

            

            $stmt = $this->pdo->prepare("

                INSERT INTO movimientos (

                    grupo_id, fecha, servicio_id, tipo, moneda, monto,

                    cuenta_origen_id, cuenta_destino_id, cliente_id, promotor_id,

                    proveedor_id, prestamista_id, paridad, descripcion,

                    es_reverso, movimiento_original_id, motivo_correccion,

                    usuario_id, estado, activo

                ) VALUES (

                    ?, CURDATE(), ?, 'ajuste', ?, ?,

                    ?, ?, ?, ?,

                    ?, ?, ?, ?,

                    1, ?, ?,

                    ?, 'activo', 1

                )

            ");

            

            $stmt->execute([

                $nuevo_grupo,

                $original['servicio_id'],

                $original['moneda'],

                $monto_reverso,

                $original['cuenta_destino_id'],

                $original['cuenta_origen_id'],

                $original['cliente_id'],

                $original['promotor_id'],

                $original['proveedor_id'],

                $original['prestamista_id'],

                $original['paridad'],

                $descripcion_reverso,

                $movimiento_id,

                $motivo,

                $usuario_id

            ]);

            

            $reverso_id = $this->pdo->lastInsertId();

            

            // Revertir saldos

            $this->revertirSaldos($original, $reverso_id, $descripcion_reverso);

            

            // 2. Crear el movimiento CORRECTO

            $datos_nuevos = array_merge($original, $datos_corregidos);

            $descripcion_nuevo = "CORRECCIÓN de #$movimiento_id: " . ($datos_nuevos['descripcion'] ?? '');

            

            $stmt = $this->pdo->prepare("

                INSERT INTO movimientos (

                    grupo_id, fecha, servicio_id, tipo, moneda, monto,

                    cuenta_origen_id, cuenta_destino_id, cliente_id, promotor_id,

                    proveedor_id, prestamista_id, paridad, descripcion,

                    es_reverso, movimiento_original_id, motivo_correccion,

                    usuario_id, estado, activo

                ) VALUES (

                    ?, ?, ?, ?, ?, ?,

                    ?, ?, ?, ?,

                    ?, ?, ?, ?,

                    0, ?, ?,

                    ?, 'activo', 1

                )

            ");

            

            $stmt->execute([

                $nuevo_grupo,

                $datos_nuevos['fecha'] ?? date('Y-m-d'),

                $datos_nuevos['servicio_id'],

                $datos_nuevos['tipo'],

                $datos_nuevos['moneda'],

                $datos_nuevos['monto'],

                $datos_nuevos['cuenta_origen_id'],

                $datos_nuevos['cuenta_destino_id'],

                $datos_nuevos['cliente_id'],

                $datos_nuevos['promotor_id'],

                $datos_nuevos['proveedor_id'],

                $datos_nuevos['prestamista_id'],

                $datos_nuevos['paridad'],

                $descripcion_nuevo,

                $movimiento_id,

                $motivo,

                $usuario_id

            ]);

            

            $nuevo_id = $this->pdo->lastInsertId();

            

            // Aplicar saldos del nuevo movimiento

            $this->aplicarSaldos($datos_nuevos, $nuevo_id, $descripcion_nuevo);

            

            // 3. Marcar el original como corregido

            $stmt = $this->pdo->prepare("

                UPDATE movimientos 

                SET estado = 'corregido',

                    motivo_correccion = ?,

                    corregido_por_usuario_id = ?,

                    corregido_en = NOW()

                WHERE id = ?

            ");

            $stmt->execute([$motivo, $usuario_id, $movimiento_id]);

            // 3b. Si era parte de una venta de divisas, restaurar sus consumos FIFO
            // (misma protección que en anulación; evita consumos huérfanos).
            $fifoRestaurado = [];
            if (!empty($original['grupo_id'])) {
                $fifoRestaurado = $this->restaurarConsumosFIFO($original['grupo_id']);
            }

            // Registrar auditoría

            $this->registrarAuditoria([

                'accion' => 'CORREGIR_MOVIMIENTO',

                'modulo' => 'flujo',

                'tabla' => 'movimientos',

                'registro_id' => $movimiento_id,

                'descripcion' => "Corrigió movimiento #$movimiento_id: $motivo" . (!empty($fifoRestaurado) ? ". FIFO restaurado: " . json_encode($fifoRestaurado, JSON_UNESCAPED_UNICODE) : ""),

                'datos_anteriores' => $original,

                'datos_nuevos' => array_merge($datos_corregidos, [

                    'reverso_id' => $reverso_id,

                    'nuevo_id' => $nuevo_id,

                    'fifo_restaurado' => $fifoRestaurado

                ])

            ]);

            

            $this->pdo->commit();

            

            return [

                'success' => true,

                'message' => 'Movimiento corregido correctamente',

                'reverso_id' => $reverso_id,

                'nuevo_id' => $nuevo_id,

                'movimiento_original' => $movimiento_id

            ];

            

        } catch (Exception $e) {

            if ($this->pdo->inTransaction()) {

                $this->pdo->rollBack();

            }

            return [

                'success' => false,

                'error' => $e->getMessage()

            ];

        }

    }

    

    /**

     * Obtiene un movimiento por ID

     */

    public function obtenerMovimiento($id) {

        $stmt = $this->pdo->prepare("SELECT * FROM movimientos WHERE id = ?");

        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);

    }

    

    /**

     * Anula todos los movimientos de un grupo

     * 

     * @param int $grupo_id ID del grupo de movimientos

     * @param string $motivo Razón de la anulación

     * @param int $usuario_id ID del usuario que anula

     * @return array Resultado de la operación

     */

    /**
     * Anula todos los movimientos de un grupo
     * * @param int $grupo_id ID del grupo de movimientos
     * @param string $motivo Razón de la anulación
     * @param int $usuario_id ID del usuario que anula
     * @return array Resultado de la operación
     */
    public function anularPorGrupo($grupo_id, $motivo, $usuario_id) {
        try {
            $this->pdo->beginTransaction();
            
            // Obtener todos los movimientos del grupo
            $stmt = $this->pdo->prepare("SELECT * FROM movimientos WHERE grupo_id = ? AND estado = 'activo'");
            $stmt->execute([$grupo_id]);
            $movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($movimientos)) {
                throw new Exception("No se encontraron movimientos activos en el grupo #$grupo_id");
            }

            // ═══════════════════════════════════════════════════════════════════
            // CANDADO DE TITANIO + Validación mes cerrado (centralizado)
            // ═══════════════════════════════════════════════════════════════════
            require_once __DIR__ . '/../config/helpers.php';
            validarGrupoNoCerrado($this->pdo, $grupo_id);
            
            // Crear nuevo grupo para los reversos (centralizado)
            $nuevo_grupo = generarGrupoId($this->pdo);
            
            $reversos = [];
            
            foreach ($movimientos as $mov) {
                // Crear movimiento de REVERSO
                $monto_reverso = -$mov['monto'];
                $descripcion_reverso = "REVERSO: Anulación de grupo #$grupo_id - " . ($mov['descripcion'] ?? '');
                
                $stmt = $this->pdo->prepare("
                    INSERT INTO movimientos (
                        grupo_id, fecha, servicio_id, tipo, moneda, monto,
                        cuenta_origen_id, cuenta_destino_id, cliente_id, promotor_id,
                        proveedor_id, prestamista_id, paridad, descripcion,
                        es_reverso, movimiento_original_id, motivo_correccion,
                        usuario_id, estado, activo
                    ) VALUES (
                        ?, CURDATE(), ?, 'ajuste', ?, ?,
                        ?, ?, ?, ?,
                        ?, ?, ?, ?,
                        1, ?, ?,
                        ?, 'activo', 1
                    )
                ");
                
                $stmt->execute([
                    $nuevo_grupo,
                    $mov['servicio_id'],
                    $mov['moneda'],
                    $monto_reverso,
                    $mov['cuenta_destino_id'],  // Invertir origen/destino
                    $mov['cuenta_origen_id'],
                    $mov['cliente_id'],
                    $mov['promotor_id'],
                    $mov['proveedor_id'],
                    $mov['prestamista_id'],
                    $mov['paridad'],
                    $descripcion_reverso,
                    $mov['id'],
                    $motivo,
                    $usuario_id
                ]);
                
                $reverso_id = $this->pdo->lastInsertId();
                $reversos[] = $reverso_id;
                
                // Revertir saldos
                $this->revertirSaldos($mov, $reverso_id, $descripcion_reverso);
                
                // Marcar original como anulado
                $stmt = $this->pdo->prepare("
                    UPDATE movimientos 
                    SET estado = 'anulado', motivo_correccion = ?, corregido_por_usuario_id = ?, corregido_en = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$motivo, $usuario_id, $mov['id']]);
            }
            
            // ═══════════════════════════════════════════════════════════════════
            // CLEANUP ESPECÍFICO (Restauración Matemática Exacta)
            // ═══════════════════════════════════════════════════════════════════

            // Servicio 1 — Venta de Efectivo
            $this->pdo->prepare("UPDATE deudas_clientes SET activo = 0 WHERE movimiento_venta_id = ? AND activo = 1")->execute([$grupo_id]);

            // Servicio 20 — Compra de Efectivo (ahora con proveedores)
            foreach ($movimientos as $mov) {
                if ((int)$mov['servicio_id'] === 20 && !empty($mov['proveedor_id'])) {
                    // Buscar la cuenta MXN del proveedor y reducir el adeudo
                    $stmtProv = $this->pdo->prepare("SELECT cuenta_mxn_id FROM proveedores WHERE id = ?");
                    $stmtProv->execute([$mov['proveedor_id']]);
                    $cuentaMxnId = $stmtProv->fetchColumn();
                    if ($cuentaMxnId && strtolower($mov['tipo']) === 'salida') {
                        $this->pdo->exec("SET @hyperion_saldo_auth = 1");
                        $this->pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual - ? WHERE id = ?")->execute([abs($mov['monto']), $cuentaMxnId]);
                    }
                }
            }

            // Servicio 10 — Préstamo de Prestamista
            foreach ($movimientos as $mov) {
                if ((int)$mov['servicio_id'] === 10) {
                    if (!empty($mov['referencia_id'])) {
                        $this->pdo->prepare("UPDATE deudas_empresa SET activo = 0 WHERE id = ?")->execute([$mov['referencia_id']]);
                    } elseif (!empty($mov['prestamista_id'])) {
                        $this->pdo->prepare("UPDATE deudas_empresa SET activo = 0 WHERE prestamista_id = ? AND monto = ? AND activo = 1 ORDER BY id DESC LIMIT 1")->execute([$mov['prestamista_id'], abs($mov['monto'])]);
                    }
                }
            }

            // Servicio 11 — Pago a Proveedor
            foreach ($movimientos as $mov) {
                if ((int)$mov['servicio_id'] === 11 && !empty($mov['proveedor_id'])) {
                    $desc = $mov['descripcion'] ?? '';
                    $monedaProv = (stripos($desc, 'USDT') !== false) ? 'USDT' : 'USD';
                    $stmtProv = $this->pdo->prepare("SELECT cuenta_usd_id, cuenta_usdt_id, cuenta_id FROM proveedores WHERE id = ?");
                    $stmtProv->execute([$mov['proveedor_id']]);
                    $prov = $stmtProv->fetch(PDO::FETCH_ASSOC);
                    if ($prov) {
                        $cuentaProvId = ($monedaProv === 'USD') ? ($prov['cuenta_usd_id'] ?? $prov['cuenta_id']) : ($prov['cuenta_usdt_id'] ?? $prov['cuenta_id']);
                        if ($cuentaProvId) {
                            $montoDivisa = ($mov['paridad'] > 0) ? round(abs($mov['monto']) / $mov['paridad'], 8) : 0;
                            $this->pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual + ?, valor_mxn = valor_mxn + ? WHERE id = ?")->execute([$montoDivisa, abs($mov['monto']), $cuentaProvId]);
                        }
                    }
                }
            }

            // Servicio 12 — Gasto
            foreach ($movimientos as $mov) {
                if ((int)$mov['servicio_id'] === 12 && !empty($mov['gasto_id'])) {
                    $this->pdo->prepare("UPDATE gastos SET activo = 0 WHERE id = ?")->execute([$mov['gasto_id']]);
                    break;
                }
            }

            // Servicio 13 — Pago a Cliente
            foreach ($movimientos as $mov) {
                if ((int)$mov['servicio_id'] === 13) {
                    $montoPago = abs($mov['monto']);
                    if (!empty($mov['referencia_id'])) {
                        $this->pdo->prepare("
                            UPDATE deudas_clientes
                            SET monto_pagado = GREATEST(0.00, monto_pagado - ?),
                                estado = CASE WHEN GREATEST(0.00, monto_pagado - ?) <= 0.00 THEN 'pendiente' ELSE 'parcial' END,
                                movimiento_pago_id = CASE WHEN (monto_pagado - ?) <= 0.00 THEN NULL ELSE movimiento_pago_id END,
                                fecha_pago = CASE WHEN (monto_pagado - ?) <= 0.00 THEN NULL ELSE fecha_pago END
                            WHERE id = ?
                        ")->execute([$montoPago, $montoPago, $montoPago, $montoPago, $mov['referencia_id']]);
                    } elseif (!empty($mov['cliente_id'])) {
                        $this->pdo->prepare("
                            UPDATE deudas_clientes
                            SET monto_pagado = GREATEST(0.00, monto_pagado - ?),
                                estado = CASE WHEN GREATEST(0.00, monto_pagado - ?) <= 0.00 THEN 'pendiente' ELSE 'parcial' END,
                                movimiento_pago_id = CASE WHEN (monto_pagado - ?) <= 0.00 THEN NULL ELSE movimiento_pago_id END,
                                fecha_pago = CASE WHEN (monto_pagado - ?) <= 0.00 THEN NULL ELSE fecha_pago END
                            WHERE cliente_id = ? AND activo = 1 AND monto_pagado >= ?
                            ORDER BY id DESC LIMIT 1
                        ")->execute([$montoPago, $montoPago, $montoPago, $montoPago, $mov['cliente_id'], $montoPago]);
                    }
                }
            }

           // Servicio 14 — Pago de Préstamo a Prestamista (Dinero que Amiri paga)
            foreach ($movimientos as $mov) {
                if ((int)$mov['servicio_id'] === 14) {
                    $montoPago = abs($mov['monto']);
                    if (!empty($mov['referencia_id'])) {
                        $this->pdo->prepare("
                            UPDATE deudas_empresa 
                            SET monto_pagado = GREATEST(0.00, monto_pagado - ?), 
                                estado = CASE WHEN GREATEST(0.00, monto_pagado - ?) <= 0.00 THEN 'pendiente' ELSE 'parcial' END,
                                fecha_pago = CASE WHEN (monto_pagado - ?) <= 0.00 THEN NULL ELSE fecha_pago END
                            WHERE id = ?
                        ")->execute([$montoPago, $montoPago, $montoPago, $mov['referencia_id']]);
                    } elseif (!empty($mov['prestamista_id'])) {
                        // Fallback seguro: filtrar también por monto_pagado >= reverso
                        $this->pdo->prepare("
                            UPDATE deudas_empresa 
                            SET monto_pagado = GREATEST(0.00, monto_pagado - ?), 
                                estado = CASE WHEN GREATEST(0.00, monto_pagado - ?) <= 0.00 THEN 'pendiente' ELSE 'parcial' END,
                                fecha_pago = CASE WHEN (monto_pagado - ?) <= 0.00 THEN NULL ELSE fecha_pago END
                            WHERE prestamista_id = ? AND activo = 1 AND monto_pagado >= ?
                            ORDER BY id DESC LIMIT 1
                        ")->execute([$montoPago, $montoPago, $montoPago, $mov['prestamista_id'], $montoPago]);
                    }
                }
            }

            // Servicio 15 — Pago de Comisión a Promotor
            foreach ($movimientos as $mov) {
                if ((int)$mov['servicio_id'] === 15) {
                    $this->pdo->prepare("UPDATE pagos_promotores SET activo = 0 WHERE movimiento_id = ?")->execute([$mov['id']]);
                }
            }

            // Servicio 16 — Préstamo Otorgado (Nuevo Módulo)
            foreach ($movimientos as $mov) {
                if ((int)$mov['servicio_id'] === 16 && !empty($mov['referencia_id'])) {
                    $this->pdo->prepare("UPDATE prestamos_otorgados SET activo = 0 WHERE id = ?")->execute([$mov['referencia_id']]);
                }
            }

            // Servicio 17 — Cobro Préstamo Otorgado (Dinero que Amiri recupera)
            foreach ($movimientos as $mov) {
                if ((int)$mov['servicio_id'] === 17) {
                    $montoPago = abs($mov['monto']);
                    
                    if (!empty($mov['referencia_id'])) {
                        $this->pdo->prepare("
                            UPDATE prestamos_otorgados
                            SET estado = CASE WHEN GREATEST(0.00, monto_pagado - ?) <= 0.00 THEN 'pendiente' ELSE 'parcial' END,
                                monto_pagado = GREATEST(0.00, monto_pagado - ?)
                            WHERE id = ?
                        ")->execute([$montoPago, $montoPago, $mov['referencia_id']]);
                    } 
                    elseif (!empty($mov['beneficiario'])) {
                        $nombres = explode(',', $mov['beneficiario']);
                        $nombre_limpio = trim($nombres[0]);

                        // Fallback seguro: filtrar también por monto_pagado >= reverso
                        $this->pdo->prepare("
                            UPDATE prestamos_otorgados
                            SET estado = CASE WHEN GREATEST(0.00, monto_pagado - ?) <= 0.00 THEN 'pendiente' ELSE 'parcial' END,
                                monto_pagado = GREATEST(0.00, monto_pagado - ?)
                            WHERE persona_nombre = ? AND estado != 'pendiente' AND monto_pagado >= ?
                            ORDER BY id DESC LIMIT 1
                        ")->execute([$montoPago, $montoPago, $nombre_limpio, $montoPago]);
                    }
                }
            }

            // Servicios 5,6 — Compra de divisas: candado + desactivar lote.
            // CANDADO: no anular una compra cuyo lote ya fue consumido por ventas VIGENTES
            // (de otro grupo). Anularla dejaría esos consumos sin respaldo y descuadraría
            // la ecuación (faltante fantasma). Anula primero esas ventas.
            foreach ($movimientos as $mov) {
                $sid = (int)$mov['servicio_id'];
                if (in_array($sid, [5, 6], true)) {
                    $stmtL = $this->pdo->prepare("SELECT id, cantidad_original, cantidad_restante FROM inventario_monedas WHERE movimiento_id = ? AND activo = 1");
                    $stmtL->execute([$mov['id']]);
                    foreach ($stmtL->fetchAll(PDO::FETCH_ASSOC) as $lote) {
                        if ((float)$lote['cantidad_original'] - (float)$lote['cantidad_restante'] > 0.00000001) {
                            $stmtV = $this->pdo->prepare("SELECT COUNT(*) FROM consumos_inventario c INNER JOIN movimientos m ON m.id = c.movimiento_id WHERE c.inventario_id = ? AND m.estado = 'activo' AND m.activo = 1 AND m.grupo_id <> ?");
                            $stmtV->execute([$lote['id'], $grupo_id]);
                            if ((int)$stmtV->fetchColumn() > 0) {
                                throw new Exception("No se puede anular la compra (mov #{$mov['id']}): el lote #{$lote['id']} ya fue consumido parcialmente por ventas vigentes. Anula primero esas ventas.");
                            }
                        }
                    }
                    // Compra: el lote FIFO fue creado con cantidad_restante = monto. Al anular, desactivarlo (no sumarlo).
                    $this->pdo->prepare("UPDATE inventario_monedas SET activo = 0, cantidad_restante = 0 WHERE movimiento_id = ? AND activo = 1")->execute([$mov['id']]);
                }
            }

            // Servicios 2,3 — Venta de divisas: restaurar al inventario lo consumido por este grupo.
            // Sin esto, anular una venta dejaba consumos huérfanos y la ecuación mostraba
            // faltante (ej. grupo #1790197398287: -$242,672.22 por 14,027.3 USDT no restaurados).
            $fifoRestaurado = $this->restaurarConsumosFIFO($grupo_id);

            // Servicio 18 — Retiro de Capital (también captura retiros legacy con servicio=12)
            foreach ($movimientos as $mov) {
                $esRetiro = (int)$mov['servicio_id'] === 18 ||
                    ((int)$mov['servicio_id'] === 12 && strpos($mov['descripcion'] ?? '', 'Retiro de capital:') === 0);
                if ($esRetiro) {
                    $retiroId = !empty($mov['referencia_id']) && $mov['referencia_tipo'] === 'retiro_capital'
                        ? (int)$mov['referencia_id']
                        : null;
                    if ($retiroId) {
                        $this->pdo->prepare("DELETE FROM retiros_capital WHERE id = ?")->execute([$retiroId]);
                    } else {
                        $this->pdo->prepare("DELETE FROM retiros_capital WHERE monto = ? AND fecha = ? ORDER BY id DESC LIMIT 1")->execute([abs($mov['monto']), $mov['fecha']]);
                    }
                }
            }

            // Registrar auditoría
            $this->registrarAuditoria([
                'accion' => 'ANULAR_GRUPO',
                'modulo' => 'flujo',
                'tabla' => 'movimientos',
                'registro_id' => $grupo_id,
                'descripcion' => "Anuló grupo de movimientos #$grupo_id ($motivo). " . count($movimientos) . " movimientos anulados." . (!empty($fifoRestaurado) ? " FIFO restaurado: " . json_encode($fifoRestaurado, JSON_UNESCAPED_UNICODE) : ""),
                'datos_anteriores' => ['grupo_id' => $grupo_id, 'movimientos' => count($movimientos)],
                'datos_nuevos' => ['nuevo_grupo' => $nuevo_grupo, 'reversos' => $reversos, 'fifo_restaurado' => $fifoRestaurado ?? []]
            ]);
            
            $this->pdo->commit();
            
            return [
                'success' => true,
                'message' => 'Grupo de movimientos anulado correctamente',
                'grupo_original' => $grupo_id,
                'grupo_reverso' => $nuevo_grupo,
                'reverso_id' => $reversos[0] ?? null,
                'movimientos_anulados' => count($movimientos)
            ];
            
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    

    /**

     * Obtiene el historial de correcciones de un movimiento

     */

    public function obtenerHistorialCorrecciones($movimiento_id) {

        $stmt = $this->pdo->prepare("

            SELECT 

                fm.*,

                u.nombre as usuario_nombre,

                s.nombre as servicio_nombre

            FROM movimientos fm

            LEFT JOIN usuarios u ON fm.corregido_por_usuario_id = u.id

            LEFT JOIN servicios s ON fm.servicio_id = s.id

            WHERE fm.movimiento_original_id = ? 

               OR fm.id = ?

            ORDER BY fm.creado_en ASC

        ");

        $stmt->execute([$movimiento_id, $movimiento_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    

    /**

     * Revierte los saldos de las cuentas afectadas por un movimiento

     */

    private function revertirSaldos($movimiento, $reverso_id, $descripcion) {

        require_once __DIR__ . '/../config/helpers.php';

        

        // Revertir cuenta origen (si existe)

        if (!empty($movimiento['cuenta_origen_id'])) {

            // Si el original era salida, el reverso es entrada

            $tipo = ($movimiento['tipo'] === 'salida' || $movimiento['tipo'] === 'traspaso') ? 'entrada' : 'salida';

            actualizarSaldoCuenta(

                $this->pdo, 

                $movimiento['cuenta_origen_id'], 

                abs($movimiento['monto']), 

                $tipo, 

                $reverso_id, 

                $descripcion

            );

        }

        

        // Revertir cuenta destino (si existe)

        if (!empty($movimiento['cuenta_destino_id'])) {

            // Detectar adeudo a proveedor por compra a crédito (servicios 5/6):
            // la creación (api/flujo-divisas.php) registra el movimiento como
            // tipo='salida' pero ACREDITA el saldo con actualizarSaldoCuenta(..., 'entrada')
            // y suma valor_mxn += cantidad*TC. El genérico de abajo haría 'entrada'
            // (duplicaría la deuda, como pasó el 2026-09-18 con $-989,630.70).
            // La reversión correcta es 'salida' + restar valor_mxn.
            $esAdeudoProveedor = (
                ($movimiento['tipo'] ?? '') === 'salida'
                && in_array((int)($movimiento['servicio_id'] ?? 0), [5, 6], true)
            );
            if ($esAdeudoProveedor) {
                try {
                    $stmtTipo = $this->pdo->prepare("SELECT tipo_cuenta FROM cuentas WHERE id = ?");
                    $stmtTipo->execute([$movimiento['cuenta_destino_id']]);
                    $esAdeudoProveedor = ((int)$stmtTipo->fetchColumn() === 6);
                } catch (Exception $e) {
                    $esAdeudoProveedor = false;
                }
            }

            if ($esAdeudoProveedor) {
                // entrada/traspaso/comision incrementaron destino → reverso los decrementa
                $tipo = 'salida';
            } else {
                $tipo = ($movimiento['tipo'] === 'entrada' || $movimiento['tipo'] === 'traspaso' || $movimiento['tipo'] === 'comision') ? 'salida' : 'entrada';
            }

            actualizarSaldoCuenta(

                $this->pdo, 

                $movimiento['cuenta_destino_id'], 

                abs($movimiento['monto']), 

                $tipo, 

                $reverso_id, 

                $descripcion

            );

            // Revertir el valor_mxn del adeudo (inverso exacto de flujo-divisas.php:315)
            if ($esAdeudoProveedor) {
                $valorReverso = round(abs($movimiento['monto']) * (float)($movimiento['paridad'] ?? 0), 2);
                if ($valorReverso > 0) {
                    $this->pdo->exec("SET @hyperion_saldo_auth = 1");
                    $stmtV = $this->pdo->prepare("UPDATE cuentas SET valor_mxn = COALESCE(valor_mxn, 0) - ? WHERE id = ?");
                    $stmtV->execute([$valorReverso, $movimiento['cuenta_destino_id']]);
                }
            }

        }

    }

    

    /**

     * Aplica los saldos de un nuevo movimiento

     */

    private function aplicarSaldos($movimiento, $movimiento_id, $descripcion) {

        require_once __DIR__ . '/../config/helpers.php';

        

        // Aplicar a cuenta origen (salida)

        if (!empty($movimiento['cuenta_origen_id'])) {

            $tipo = ($movimiento['tipo'] === 'salida' || $movimiento['tipo'] === 'traspaso') ? 'salida' : 'entrada';

            actualizarSaldoCuenta(

                $this->pdo, 

                $movimiento['cuenta_origen_id'], 

                abs($movimiento['monto']), 

                $tipo, 

                $movimiento_id, 

                $descripcion

            );

        }

        

        // Aplicar a cuenta destino (entrada)
        // NOTA: 'comision' también acredita destino (ver flujo-divisas.php:488,499)

        if (!empty($movimiento['cuenta_destino_id'])) {

            $tipo = ($movimiento['tipo'] === 'entrada' || $movimiento['tipo'] === 'traspaso' || $movimiento['tipo'] === 'comision') ? 'entrada' : 'salida';

            actualizarSaldoCuenta(

                $this->pdo, 

                $movimiento['cuenta_destino_id'], 

                abs($movimiento['monto']), 

                $tipo, 

                $movimiento_id, 

                $descripcion

            );

        }

    }

    

    /**

     * Anula movimientos relacionados por grupo_id

     */

    private function anularMovimientosRelacionados($grupo_id, $movimiento_principal, $motivo, $usuario_id, $nuevo_grupo) {

        $stmt = $this->pdo->prepare("

            SELECT * FROM movimientos 

            WHERE grupo_id = ? AND id != ? AND estado = 'activo'

        ");

        $stmt->execute([$grupo_id, $movimiento_principal]);

        $relacionados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        

        foreach ($relacionados as $mov) {

            // Crear reverso para cada movimiento relacionado

            $monto_reverso = -$mov['monto'];

            $descripcion_reverso = "REVERSO relacionado: #" . $mov['id'];

            

            $stmt = $this->pdo->prepare("

                INSERT INTO movimientos (

                    grupo_id, fecha, servicio_id, tipo, moneda, monto,

                    cuenta_origen_id, cuenta_destino_id, cliente_id, promotor_id,

                    proveedor_id, prestamista_id, paridad, descripcion,

                    es_reverso, movimiento_original_id, motivo_correccion,

                    usuario_id, estado, activo

                ) VALUES (

                    ?, CURDATE(), ?, 'ajuste', ?, ?,

                    ?, ?, ?, ?,

                    ?, ?, ?, ?,

                    1, ?, ?,

                    ?, 'activo', 1

                )

            ");

            

            $stmt->execute([

                $nuevo_grupo,

                $mov['servicio_id'],

                $mov['moneda'],

                $monto_reverso,

                $mov['cuenta_destino_id'],

                $mov['cuenta_origen_id'],

                $mov['cliente_id'],

                $mov['promotor_id'],

                $mov['proveedor_id'],

                $mov['prestamista_id'],

                $mov['paridad'],

                $descripcion_reverso,

                $mov['id'],

                $motivo,

                $usuario_id

            ]);

            

            $reverso_id = $this->pdo->lastInsertId();

            

            // Revertir saldos

            $this->revertirSaldos($mov, $reverso_id, $descripcion_reverso);

            

            // Marcar como anulado

            $stmt = $this->pdo->prepare("

                UPDATE movimientos 

                SET estado = 'anulado',

                    motivo_correccion = ?,

                    corregido_por_usuario_id = ?,

                    corregido_en = NOW()

                WHERE id = ?

            ");

            $stmt->execute([$motivo, $usuario_id, $mov['id']]);

        }

    }

    

    /**
     * Devuelve al inventario FIFO las cantidades consumidas por los movimientos de un grupo
     * y elimina esos registros de consumo (la venta queda anulada por completo: sus costos
     * no deben seguir en el inventario ni en reportes de utilidad).
     *
     * Esquema real: consumos_inventario(movimiento_id, inventario_id, cantidad_consumida).
     * El lote recupera su restante con tope en cantidad_original. La historia queda en
     * auditoría (ANULAR_GRUPO con fifo_restaurado) + movimientos anulados + reversos.
     * Debe llamarse dentro de la misma transacción de la anulación.
     *
     * @param int|string $grupo_id
     * @return array Lista [['lote_id'=>int,'cantidad'=>float], ...] para auditoría
     */
    private function restaurarConsumosFIFO($grupo_id) {
        $restaurado = [];
        $stmt = $this->pdo->prepare("
            SELECT c.inventario_id, COALESCE(SUM(c.cantidad_consumida), 0) AS total
            FROM consumos_inventario c
            INNER JOIN movimientos m ON m.id = c.movimiento_id
            WHERE m.grupo_id = ?
            GROUP BY c.inventario_id
        ");
        $stmt->execute([$grupo_id]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) {
            $total = (float)$c['total'];
            if ($total <= 0) continue;
            $stmtR = $this->pdo->prepare("UPDATE inventario_monedas SET cantidad_restante = LEAST(cantidad_original, cantidad_restante + ?), activo = 1 WHERE id = ?");
            $stmtR->execute([$total, $c['inventario_id']]);
            $restaurado[] = ['lote_id' => (int)$c['inventario_id'], 'cantidad' => $total];
        }
        if (!empty($restaurado)) {
            $stmtD = $this->pdo->prepare("DELETE c FROM consumos_inventario c INNER JOIN movimientos m ON m.id = c.movimiento_id WHERE m.grupo_id = ?");
            $stmtD->execute([$grupo_id]);
        }
        return $restaurado;
    }


    /**

     * Registra una acción en auditoría

     */

    private function registrarAuditoria($datos) {

        require_once __DIR__ . '/../config/auditoria.php';

        registrarAuditoria($this->pdo, $datos);

    }

}

