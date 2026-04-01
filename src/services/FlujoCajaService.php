<?php

/**

 * FlujoCajaService - Lógica de negocio para flujo de caja

 * Centraliza operaciones complejas de transacciones

 */



require_once __DIR__ . '/../config/db.php';

require_once __DIR__ . '/../config/validator.php';



class FlujoCajaService {

    private $pdo;

    

    public function __construct($pdo = null) {

        $this->pdo = $pdo ?? getPDO();

    }

    

    /**

     * Crear un movimiento de flujo de caja con todas sus validaciones

     * 

     * @param array $data Datos del movimiento

     * @return array ['success' => bool, 'message' => string, 'grupo_id' => int]

     * @throws ValidationException Si los datos son inválidos

     * @throws Exception Si hay error en la transacción

     */

    public function crearMovimiento(array $data) {

        // Validar datos de entrada

        $this->validarDatosMovimiento($data);

        

        // Extraer datos validados

        $fecha = $data['fecha'];

        $servicio = $data['servicio'];

        $monto = floatval($data['monto']);

        $paridad = floatval($data['paridad'] ?? 1);

        $cliente_id = $data['cliente_id'] ?? null;

        $proveedor_id = $data['proveedor_id'] ?? null;

        $cuenta_id = $data['cuenta_id'] ?? null;

        $destino_id = $data['destino_id'] ?? null;

        

        try {

            $this->pdo->beginTransaction();

            

            // Generar ID de grupo único

            $grupo_id = $this->generarGrupoId();

            

            // Obtener ID del servicio

            $servicio_id = $this->obtenerServicioId($servicio);

            

            // Obtener datos del cliente si existe

            $clienteData = $this->obtenerDatosCliente($cliente_id);

            

            // Procesar según tipo de servicio

            $this->procesarServicio(

                $servicio,

                $grupo_id,

                $fecha,

                $servicio_id,

                $monto,

                $paridad,

                $cuenta_id,

                $destino_id,

                $cliente_id,

                $clienteData

            );

            

            $this->pdo->commit();

            

            return [

                'success' => true,

                'message' => 'Movimiento creado correctamente',

                'grupo_id' => $grupo_id

            ];

            

        } catch (Exception $e) {

            $this->pdo->rollBack();

            throw $e;

        }

    }

    

    /**

     * Validar datos del movimiento

     */

    private function validarDatosMovimiento(array $data) {

        $validator = new Validator();

        $validator->required($data, ['fecha', 'servicio', 'monto']);

        $validator->date($data['fecha']);

        $validator->numeric($data['monto'], 0.01);

        

        if (isset($data['paridad'])) {

            $validator->numeric($data['paridad'], 0);

        }

    }

    

    /**

     * Generar ID único de grupo para transacciones relacionadas

     */

    private function generarGrupoId() {

        return round(microtime(true) * 1000);

    }

    

    /**

     * Obtener ID del servicio por nombre

     */

    private function obtenerServicioId($nombre) {

        $stmt = $this->pdo->prepare("SELECT id FROM servicios WHERE nombre = ? LIMIT 1");

        $stmt->execute([$nombre]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        

        if (!$result) {

            throw new Exception("Servicio no encontrado: $nombre");

        }

        

        return $result['id'];

    }

    

    /**

     * Obtener datos del cliente (cuenta, promotor)

     */

    private function obtenerDatosCliente($cliente_id) {

        if (!$cliente_id) {

            return null;

        }

        

        $stmt = $this->pdo->prepare("

            SELECT c.cuenta_id, c.promotor_id, p.cuenta_id as promotor_cuenta_id

            FROM clientes c

            LEFT JOIN promotores p ON c.promotor_id = p.id

            WHERE c.id = ?

        ");

        $stmt->execute([$cliente_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);

    }

    

    /**

     * Actualizar saldo de una cuenta

     */

    public function actualizarSaldo($cuenta_id, $monto, $tipo_operacion, $moneda = 'MXN') {

        if (!$cuenta_id) {

            return false;

        }

        

        // Para USD/USDT: guardar monto nativo

        $monto_a_actualizar = $monto;

        

        $operador = ($tipo_operacion === 'entrada') ? '+' : '-';

        $sql = "UPDATE cuentas SET saldo_actual = saldo_actual {$operador} ? WHERE id = ?";

        $stmt = $this->pdo->prepare($sql);

        

        if (!$stmt->execute([$monto_a_actualizar, $cuenta_id])) {

            throw new Exception("Error al actualizar saldo de cuenta $cuenta_id");

        }

        

        return true;

    }

    

    /**

     * Insertar movimiento en movimientos

     */

    public function insertarMovimiento($params) {

        $sql = "

            INSERT INTO movimientos 

            (grupo_id, fecha, servicio_id, tipo, moneda, monto, cuenta_origen_id, cuenta_destino_id, 

             cliente_id, promotor_id, proveedor_id, referencia_id, referencia_tipo, paridad, descripcion, activo) 

            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)

        ";

        

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([

            $params['grupo_id'],

            $params['fecha'],

            $params['servicio_id'],

            $params['tipo'],

            $params['moneda'],

            $params['monto'],

            $params['cuenta_origen_id'] ?? null,

            $params['cuenta_destino_id'] ?? null,

            $params['cliente_id'] ?? null,

            $params['promotor_id'] ?? null,

            $params['proveedor_id'] ?? null,

            $params['referencia_id'] ?? null,

            $params['referencia_tipo'] ?? null,

            $params['paridad'] ?? 1,

            $params['descripcion'] ?? '',

        ]);

    }

    

    /**

     * Obtener inventario FIFO para ventas de divisas

     */

    public function obtenerInventarioFIFO($moneda, $cuenta_id) {

        $stmt = $this->pdo->prepare("

            SELECT id, cantidad_restante 

            FROM inventario_monedas 

            WHERE moneda = ? AND cuenta_id = ? AND cantidad_restante > 0

            ORDER BY fecha ASC

        ");

        $stmt->execute([$moneda, $cuenta_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    

    /**

     * Descontar del inventario usando FIFO

     */

    public function descontarInventarioFIFO($inventario, $cantidadTotal) {

        $cantidadPendiente = $cantidadTotal;

        

        foreach ($inventario as $item) {

            if ($cantidadPendiente <= 0) break;

            

            $cantidadADescontar = min($cantidadPendiente, floatval($item['cantidad_restante']));

            

            $stmt = $this->pdo->prepare("

                UPDATE inventario_monedas 

                SET cantidad_restante = cantidad_restante - ? 

                WHERE id = ?

            ");

            $stmt->execute([$cantidadADescontar, $item['id']]);

            

            $cantidadPendiente -= $cantidadADescontar;

        }

        

        if ($cantidadPendiente > 0) {

            throw new Exception("Inventario insuficiente. Faltaron $cantidadPendiente unidades");

        }

    }

    

    /**

     * Procesar servicio específico (aquí iría la lógica de cada tipo de servicio)

     * Esta es una versión simplificada - en la práctica tendrías métodos específicos

     */

    private function procesarServicio($servicio, $grupo_id, $fecha, $servicio_id, $monto, $paridad, 

                                       $cuenta_id, $destino_id, $cliente_id, $clienteData) {

        // Esta función se puede expandir con switch/case para cada tipo de servicio

        // Por ahora es un placeholder para la estructura

        

        switch ($servicio) {

            case 'Venta de USDT':

            case 'Venta de Dolares':

                $this->procesarVentaDivisas($servicio, $grupo_id, $fecha, $servicio_id, 

                                           $monto, $paridad, $cuenta_id, $clienteData);

                break;

                

            // Otros casos...

            default:

                throw new Exception("Tipo de servicio no implementado: $servicio");

        }

    }

    

    /**

     * Procesar venta de divisas con inventario FIFO

     */

    private function procesarVentaDivisas($servicio, $grupo_id, $fecha, $servicio_id, 

                                          $monto, $paridad, $cuenta_id, $clienteData) {

        $moneda = ($servicio === 'Venta de USDT') ? 'USDT' : 'USD';

        

        if (!$clienteData || !$clienteData['cuenta_id']) {

            throw new Exception('El cliente no tiene cuenta/caja asociada');

        }

        

        // Obtener y descontar inventario FIFO

        $inventario = $this->obtenerInventarioFIFO($moneda, $cuenta_id);

        

        if (empty($inventario)) {

            throw new Exception("No hay inventario de $moneda en esa cuenta");

        }

        

        $this->descontarInventarioFIFO($inventario, $monto);

        

        // Insertar movimientos...

        $total_mxn = $monto * $paridad;

        

        // Entrada MXN a cuenta del cliente

        $this->insertarMovimiento([

            'grupo_id' => $grupo_id,

            'fecha' => $fecha,

            'servicio_id' => $servicio_id,

            'tipo' => 'entrada',

            'moneda' => 'MXN',

            'monto' => $total_mxn,

            'cuenta_destino_id' => $clienteData['cuenta_id'],

            'cliente_id' => $clienteData['cuenta_id'],

            'paridad' => $paridad,

            'descripcion' => "$servicio - pago del cliente"

        ]);

        

        $this->actualizarSaldo($clienteData['cuenta_id'], $total_mxn, 'entrada');

    }

    

    /**

     * Obtener resumen de flujo de caja por rango de fechas

     */

    public function obtenerResumen($fecha_inicio, $fecha_fin, $moneda = null) {

        $sql = "

            SELECT 

                tipo,

                moneda,

                SUM(monto) as total,

                COUNT(*) as cantidad

            FROM movimientos

            WHERE activo = 1 

            AND fecha BETWEEN ? AND ?

        ";

        

        $params = [$fecha_inicio, $fecha_fin];

        

        if ($moneda) {

            $sql .= " AND moneda = ?";

            $params[] = $moneda;

        }

        

        $sql .= " GROUP BY tipo, moneda ORDER BY moneda, tipo";

        

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute($params);

        

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

}

