# 🔐 Guía: Dispositivos Confiables 24 Horas

## Resumen Rápido

Cuando activas 2FA y marcas **"Recordar este dispositivo"**, se crea un **token de confianza por 24 horas**. En los próximos 24h, NO necesitas ingresar el código TOTP nuevamente en ese dispositivo.

---

## 📋 Cómo Funciona

### **Primer Login (Nuevo Dispositivo)**

```
1. Abres login.php
2. Ingresas email + contraseña ✅
3. Sistema detecta que tienes 2FA habilitado
4. Te redirige a verify-2fa.php
5. Ingresas código TOTP (6 dígitos) ✅
6. Marcas "✓ Recordar este dispositivo por 24h"
7. Sistema crea:
   - Token único: "abc123xyz456..." (32 caracteres)
   - Guardado en tabla: trusted_devices
   - Cookie en tu navegador: "trusted_device_token"
   - Expira en: HOY + 24 horas
8. Login completado ✅
```

### **Segundo Login (Mismo Dispositivo, <24h)**

```
1. Abres login.php nuevamente
2. Ingresas email + contraseña ✅
3. Sistema revisa si existe cookie: "trusted_device_token"
4. Busca en BD si el token es válido y NO está expirado
5. ✅ Token válido → SALTARSE 2FA
6. Login completado DIRECTO ✅
```

### **Tercer Login (Mismo Dispositivo, >24h)**

```
1. Abres login.php
2. Ingresas email + contraseña ✅
3. Sistema revisa cookie: "trusted_device_token"
4. Busca en BD → Token EXPIRADO ❌
5. Te redirige a verify-2fa.php (solicita TOTP de nuevo)
6. Ingresas código TOTP ✅
7. Opción de "Recordar este dispositivo" nuevamente
```

### **Otro Dispositivo (Laptop, Tablet, etc)**

```
1. Abres login.php en OTRO dispositivo
2. Ingresas email + contraseña ✅
3. Sistema revisa cookie: NO EXISTE en este dispositivo
4. Te redirige a verify-2fa.php (exige TOTP)
5. Ingresas código TOTP ✅
6. Puedes marcar "Recordar" para crear token en THIS dispositivo
```

---

## 🗂️ Flujo en Código

### **login.php (Líneas ~50-85)**

```php
// Validar contraseña
if ($usuario && password_verify($password, $usuario['password'])) {
    
    // ✅ VALIDAR DISPOSITIVO CONFIABLE (NUEVO)
    $deviceToken = $_COOKIE['trusted_device_token'] ?? null;
    $isTrustedDevice = false;
    
    if ($deviceToken && $usuario['two_fa_enabled']) {
        // Buscar en BD si token es válido y no expiró
        $stmt = $pdo->prepare("
          SELECT id FROM trusted_devices 
          WHERE usuario_id = ? AND device_token = ? 
          AND expires_at > NOW()
        ");
        $stmt->execute([$usuario['id'], $deviceToken]);
        
        if ($stmt->fetch()) {
            // ✅ Dispositivo confiable
            $isTrustedDevice = true;
            
            // Actualizar last_used_at
            $stmt = $pdo->prepare("
              UPDATE trusted_devices 
              SET last_used_at = NOW() 
              WHERE usuario_id = ? AND device_token = ?
            ");
            $stmt->execute([$usuario['id'], $deviceToken]);
        }
    }
    
    // Decisión:
    if ($usuario['two_fa_enabled'] && !$isTrustedDevice) {
        // Tiene 2FA Y no es dispositivo confiable
        // → Redirigir a verify-2fa.php
        header('Location: verify-2fa.php');
    } else {
        // No tiene 2FA O dispositivo es confiable
        // → Login directo
        $_SESSION['usuario_id'] = $usuario['id'];
        header('Location: index.php');
    }
}
```

### **verify-2fa.php (Líneas ~150-160)**

```php
if ($twoFAService->verifyCode($usuarioId, $code)['valid']) {
    
    // Si marca "Recordar este dispositivo"
    if (isset($_POST['remember_device'])) {
        
        // Crear dispositivo confiable
        $deviceName = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Device';
        $device = $twoFAService->createTrustedDevice(
            $usuarioId, 
            $deviceName
        );
        
        // Guardar token en COOKIE (24 horas)
        setcookie(
            'trusted_device_token',
            $device['device_token'],
            time() + (24 * 3600), // +24 horas
            '/',
            '',
            true,  // HTTPS only
            true   // httponly
        );
    }
    
    // Completar login
    $_SESSION['usuario_id'] = $usuarioId;
    header('Location: index.php');
}
```

---

## 📊 Tabla: trusted_devices

```sql
CREATE TABLE trusted_devices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    device_token VARCHAR(100) UNIQUE,  -- Token único
    device_name VARCHAR(100),           -- "Chrome Windows", "Safari iPhone", etc
    ip_address VARCHAR(45),             -- 192.168.1.1 o ::1
    user_agent VARCHAR(500),            -- Info del navegador
    trusted_at TIMESTAMP DEFAULT NOW(), -- Cuándo se confió
    last_used_at TIMESTAMP,             -- Último acceso
    expires_at TIMESTAMP,               -- EXPIRA EN 24 HORAS
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);
```

---

## 🔄 Estados del Token

| Estado | Condición | Acción |
|--------|-----------|--------|
| **Válido** | `expires_at > NOW()` | ✅ Saltarse 2FA |
| **Expirado** | `expires_at <= NOW()` | ❌ Pedir 2FA |
| **No existe** | Cookie no en navegador | ❌ Pedir 2FA |
| **Revocado** | Usuario lo eliminó | ❌ Pedir 2FA |

---

## 👤 Gestión de Dispositivos

### **En setup-2fa.php (Dashboard)**

Los usuarios pueden:

1. **Ver dispositivos activos**: Lista de dispositivos recordados
   - Nombre, IP, fecha de uso
   
2. **Revocar un dispositivo**: Elimina token de BD
   - Cookie aún en navegador, pero inútil
   - Próximo login: pide 2FA
   
3. **Revocar todos**: Limpia todos los tokens
   - Todos los dispositivos olvidados

### **API Endpoints**

```bash
# Listar dispositivos del usuario
GET /api/trusted-devices.php?action=list

# Revocar dispositivo específico
POST /api/trusted-devices.php?action=revoke
Body: device_id=1

# Revocar todos los dispositivos
POST /api/trusted-devices.php?action=revoke_all

# Ver estado de 2FA
GET /api/trusted-devices.php?action=status
```

---

## 🔒 Seguridad Implementada

| Medida | Implementación |
|--------|-----------------|
| **Token único** | 32 caracteres aleatorios (random_bytes) |
| **Validación IP** | Se registra IP en cada acceso |
| **User Agent** | Se registra dispositivo/navegador |
| **Expiración** | 24 horas exactas (stored in expires_at) |
| **Cookie segura** | httponly + secure + samesite=strict |
| **Auditoría** | Cada acceso se registra en logs |
| **Revocación** | Usuario puede eliminar en cualquier momento |

---

## 📝 Auditoría & Logs

Cada acción se registra:

```php
// Login con dispositivo confiable
Logger::audit('login_trusted_device', $usuario['id'], [
    'email' => $email,
    'device_token' => 'abc123...',  // Primeros 10 caracteres
    'ip' => '192.168.1.1'
]);

// Dispositivo creado
Logger::audit('2fa_device_trusted', $usuario['id'], [
    'device_name' => 'Chrome Windows',
    'expires_at' => '2026-02-04 22:58:17'
]);

// Dispositivo revocado
Logger::audit('2fa_device_revoked', $usuario['id'], [
    'device_id' => 1,
    'reason' => 'user_revoked'
]);
```

---

## ⚠️ Casos Especiales

### **¿Qué pasa si limpio cookies del navegador?**
→ Cookie `trusted_device_token` se elimina  
→ Próximo login: pide 2FA (token aún en BD pero no hay cookie)  
→ Después de 24h: token en BD expira automáticamente

### **¿Qué pasa si cambio de IP?**
→ Token aún es válido (IP se registra pero no es validada)  
→ Sistema permite acceso desde cualquier IP durante 24h  
→ *Futura mejora: validar rango de IP*

### **¿Qué pasa si me hackean?**
→ Voy a setup-2fa.php  
→ Hago clic "Revocar todos"  
→ Se elimina TODO → Necesita 2FA en cualquier dispositivo  
→ Puedo reconfigurar 2FA con nuevo secret

### **¿Qué pasa si pierdo el teléfono?**
→ Usar códigos de respaldo (8 caracteres)  
→ Si no tengo respaldos → Contactar admin  
→ Admin puede: deshabilitar 2FA en BD

---

## 🎯 Resumen: ¿Cuándo se pide 2FA?

| Condición | ¿2FA? |
|-----------|-------|
| Primer login (sin 2FA configurado) | ❌ |
| Primer login (con 2FA configurado) | ✅ |
| Segundo login, <24h, mismo dispositivo | ❌ |
| Segundo login, >24h, mismo dispositivo | ✅ |
| Segundo login, otro dispositivo | ✅ |
| Después de revocar dispositivo | ✅ |
| Desde IP diferente (si no confía) | ✅ |

---

## 📞 Testing

### **Probar manualmente:**

```bash
# 1. Login con 2FA
Login → Ingresar TOTP → Marcar "Recordar dispositivo"

# 2. Abrir nueva pestaña
→ Ir a logout.php
→ Volver a login.php
→ Ingresar credenciales
→ ✅ NO debe pedir 2FA (si <24h)

# 3. Esperar 24h o cambiar expires_at en BD
UPDATE trusted_devices SET expires_at = NOW() - INTERVAL 1 HOUR;
→ Volver a login.php
→ ✅ DEBE pedir 2FA

# 4. Revocar dispositivo
En setup-2fa.php → Hacer clic en "Revocar"
→ Volver a login.php
→ ✅ DEBE pedir 2FA
```

---

**Última actualización:** 3 de Febrero, 2026  
**Estado:** ✅ Completamente Implementado
