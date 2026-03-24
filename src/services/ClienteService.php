<?php
/**
 * ClienteService - Lógica de negocio para clientes
 * Centraliza validaciones y operaciones de clientes
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/validator.php';

class ClienteService {
    private $pdo;
    
    public function __construct($pdo = null) {
        $this->pdo = $pdo ?? getPDO();
    }
    
    /**
     * Crear un nuevo cliente con su cuenta asociada
     * 
     * @param array $data Datos del cliente
     * @return array ['success' => bool, 'cliente_id' => int, 'cuenta_id' => int]
     * @throws ValidationException Si los datos son inválidos
     */
    public function crearCliente(array $data) {
        // Validar datos
        $this->validarDatosCliente($data);
        
        // Validar porcentajes solo si opera MXN
        if (!empty($data['opera_mxn'])) {
            $this->validarPorcentajes($data['porcentaje_amiri'], $data['porcentaje_promotor']);
        }
        
        try {
            $this->pdo->beginTransaction();
            
            // Crear cliente
            $stmt = $this->pdo->prepare("
                INSERT INTO clientes (nombre, contacto, promotor_id, retencion_total, 
                                     porcentaje_amiri, porcentaje_promotor,
                                     opera_mxn, opera_usd, opera_usdt)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['nombre'],
                $data['contacto'] ?? '',
                $data['promotor_id'], // Puede ser NULL si no opera MXN
                $data['retencion_total'] ?? 0,
                $data['porcentaje_amiri'] ?? 0,
                $data['porcentaje_promotor'] ?? 0,
                $data['opera_mxn'] ?? 1,
                $data['opera_usd'] ?? 0,
                $data['opera_usdt'] ?? 0
            ]);
            
            $cliente_id = $this->pdo->lastInsertId();
            
            // Crear cuenta asociada (MXN para todos, luego se crean otras si aplica)
            $cuenta_id = $this->crearCuentaCliente($cliente_id, $data);
            
            // Asociar cuenta al cliente
            $this->asociarCuentaACliente($cliente_id, $cuenta_id);
            
            $this->pdo->commit();
            
            return [
                'success' => true,
                'cliente_id' => $cliente_id,
                'cuenta_id' => $cuenta_id,
                'message' => 'Cliente creado correctamente'
            ];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    /**
     * Actualizar datos de un cliente
     */
    public function actualizarCliente($id, array $data) {
        // Validar datos
        $this->validarDatosCliente($data, $id);
        
        // Validar porcentajes solo si opera MXN
        if (!empty($data['opera_mxn'])) {
            $this->validarPorcentajes($data['porcentaje_amiri'], $data['porcentaje_promotor']);
        }
        
        $stmt = $this->pdo->prepare("
            UPDATE clientes 
            SET nombre = ?, 
                contacto = ?, 
                promotor_id = ?, 
                retencion_total = ?, 
                porcentaje_amiri = ?, 
                porcentaje_promotor = ?,
                opera_mxn = ?,
                opera_usd = ?,
                opera_usdt = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['nombre'],
            $data['contacto'] ?? '',
            $data['promotor_id'], // Puede ser NULL
            $data['retencion_total'] ?? 0,
            $data['porcentaje_amiri'] ?? 0,
            $data['porcentaje_promotor'] ?? 0,
            $data['opera_mxn'] ?? 1,
            $data['opera_usd'] ?? 0,
            $data['opera_usdt'] ?? 0,
            $id
        ]);
    }
    
    /**
     * Validar datos del cliente
     */
    private function validarDatosCliente(array $data, $id = null) {
        $validator = new Validator();
        
        // Nombre siempre requerido
        $validator->required($data, ['nombre']);
        $validator->length($data['nombre'], 2, 100);
        
        // Promotor y porcentajes solo requeridos si opera MXN
        if (!empty($data['opera_mxn'])) {
            if (empty($data['promotor_id'])) {
                throw new ValidationException('El promotor es requerido para clientes MXN', 
                    ['promotor_id' => 'Promotor requerido']);
            }
            
            $validator->positiveInt($data['promotor_id']);
            $validator->percentage($data['retencion_total'] ?? 0);
            $validator->percentage($data['porcentaje_amiri'] ?? 0);
            $validator->percentage($data['porcentaje_promotor'] ?? 0);
            
            // Validar que el promotor existe
            if (!$this->promotorExiste($data['promotor_id'])) {
                throw new ValidationException('El promotor especificado no existe', 
                    ['promotor_id' => 'Promotor no encontrado']);
            }
        }
        
        // Al menos una moneda debe estar seleccionada
        $opera_mxn = $data['opera_mxn'] ?? 0;
        $opera_usd = $data['opera_usd'] ?? 0;
        $opera_usdt = $data['opera_usdt'] ?? 0;
        
        if (!$opera_mxn && !$opera_usd && !$opera_usdt) {
            throw new ValidationException('Debe seleccionar al menos una moneda', 
                ['monedas' => 'Seleccione al menos una moneda']);
        }
    }
    
    /**
     * Validar que la suma de porcentajes no exceda 100%
     */
    private function validarPorcentajes($porcentaje_amiri, $porcentaje_promotor) {
        if ($porcentaje_amiri + $porcentaje_promotor > 100) {
            throw new ValidationException('La suma de porcentajes excede 100%', [
                'porcentaje_amiri' => 'La suma con porcentaje_promotor debe ser ≤ 100',
                'porcentaje_promotor' => 'La suma con porcentaje_amiri debe ser ≤ 100'
            ]);
        }
    }
    
    /**
     * Crear cuenta para el cliente
     */
    private function crearCuentaCliente($cliente_id, $data) {
        $nombre_cuenta = "Caja - " . $data['nombre'];
        $tipo_moneda = $data['tipo_moneda'] ?? 'MXN';
        
        $stmt = $this->pdo->prepare("
            INSERT INTO cuentas (nombre, tipo_cuenta, tipo_moneda, saldo_actual, activo, es_caja_empresa)
            VALUES (?, 4, ?, 0, 1, 0)
        ");
        $stmt->execute([$nombre_cuenta, $tipo_moneda]);
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Asociar cuenta al cliente
     */
    private function asociarCuentaACliente($cliente_id, $cuenta_id) {
        $stmt = $this->pdo->prepare("UPDATE clientes SET cuenta_id = ? WHERE id = ?");
        return $stmt->execute([$cuenta_id, $cliente_id]);
    }
    
    /**
     * Verificar si un promotor existe
     */
    private function promotorExiste($promotor_id) {
        $stmt = $this->pdo->prepare("SELECT id FROM promotores WHERE id = ? AND activo = 1");
        $stmt->execute([$promotor_id]);
        return $stmt->fetch() !== false;
    }
    
    /**
     * Obtener cliente por ID con datos relacionados
     */
    public function obtenerCliente($id) {
        $stmt = $this->pdo->prepare("
            SELECT c.*, 
                   p.nombre AS promotor,
                   cu.nombre AS cuenta_nombre,
                   cu.saldo_actual,
                   cu.tipo_moneda
            FROM clientes c
            LEFT JOIN promotores p ON c.promotor_id = p.id
            LEFT JOIN cuentas cu ON c.cuenta_id = cu.id
            WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
    
    /**
     * Listar todos los clientes activos
     */
    public function listarClientes($activo = true) {
        $where = $activo ? "WHERE c.activo = 1" : "";
        $sql = "
            SELECT c.*,
                   p.nombre AS promotor,
                   cu.nombre AS cuenta_nombre,
                   cu.saldo_actual
            FROM clientes c
            LEFT JOIN promotores p ON c.promotor_id = p.id
            LEFT JOIN cuentas cu ON c.cuenta_id = cu.id
            $where
            ORDER BY c.nombre
        ";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Desactivar cliente (soft delete)
     * NOTA: La tabla clientes no tiene columna 'activo' actualmente
     */
    public function desactivarCliente($id) {
        // Por ahora, solo devolvemos true ya que no hay columna activo
        // En el futuro, se podría agregar esta columna a la tabla
        return true;
        // $stmt = $this->pdo->prepare("UPDATE clientes SET activo = 0 WHERE id = ?");
        // return $stmt->execute([$id]);
    }
    
    /**
     * Obtener estadísticas del cliente
     */
    public function obtenerEstadisticas($cliente_id, $fecha_inicio = null, $fecha_fin = null) {
        $sql = "
            SELECT 
                COUNT(*) as total_operaciones,
                SUM(CASE WHEN tipo = 'entrada' THEN monto ELSE 0 END) as total_entradas,
                SUM(CASE WHEN tipo = 'salida' THEN monto ELSE 0 END) as total_salidas,
                moneda
            FROM movimientos
            WHERE cliente_id = ? AND activo = 1
        ";
        
        $params = [$cliente_id];
        
        if ($fecha_inicio && $fecha_fin) {
            $sql .= " AND fecha BETWEEN ? AND ?";
            $params[] = $fecha_inicio;
            $params[] = $fecha_fin;
        }
        
        $sql .= " GROUP BY moneda";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Calcular comisión para el promotor del cliente
     */
    public function calcularComisionPromotor($cliente_id, $monto) {
        $cliente = $this->obtenerCliente($cliente_id);
        
        if (!$cliente || !$cliente['porcentaje_promotor']) {
            return 0;
        }
        
        return ($monto * $cliente['porcentaje_promotor']) / 100;
    }
    
    /**
     * Calcular comisión para Amiri
     */
    public function calcularComisionAmiri($cliente_id, $monto) {
        $cliente = $this->obtenerCliente($cliente_id);
        
        if (!$cliente || !$cliente['porcentaje_amiri']) {
            return 0;
        }
        
        return ($monto * $cliente['porcentaje_amiri']) / 100;
    }
}
