# 2FA (Autenticación de Dos Factores) - Documentación

## 🔐 Descripción General

Sistema de autenticación de dos factores (2FA) basado en **TOTP** (Time-based One-Time Password - RFC 6238), compatible con Google Authenticator, Authy y otras apps de autenticación estándar.

### Características:

✅ **TOTP (RFC 6238)**: Códigos de 6 dígitos cada 30 segundos  
✅ **Google Authenticator Compatible**: Funciona con cualquier app TOTP estándar  
✅ **Códigos de Respaldo**: 10 códigos de respaldo de 8 caracteres  
✅ **Dispositivos de Confianza**: Recordar dispositivo por 24 horas  
✅ **Rate Limiting**: Máximo 5 intentos fallidos, bloqueo de 15 minutos  
✅ **Encriptación**: Secret TOTP encriptado con AES-256-CBC  
✅ **Auditoría**: Registro completo de eventos de 2FA  

---

## 📦 Instalación

### 1. Instalar Dependencia

```bash
composer require spomky-labs/otphp
```

### 2. Ejecutar Migraciones SQL

Ejecutar el archivo SQL de migración:

```bash
mysql -u usuario -p base_datos < src/migrations/001_add_2fa_support.sql
```

O manualmente en phpMyAdmin/MySQL:

```sql
-- Agregar columnas a usuarios
ALTER TABLE `usuarios` ADD COLUMN `two_fa_enabled` TINYINT(1) DEFAULT 0;
ALTER TABLE `usuarios` ADD COLUMN `two_fa_secret` VARCHAR(255) DEFAULT NULL;
ALTER TABLE `usuarios` ADD COLUMN `two_fa_backup_codes` JSON DEFAULT NULL;
ALTER TABLE `usuarios` ADD COLUMN `two_fa_created_at` TIMESTAMP NULL;

-- Crear tablas de soporte
CREATE TABLE `trusted_devices` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `usuario_id` INT NOT NULL,
  `device_token` VARCHAR(255) NOT NULL UNIQUE,
  `device_name` VARCHAR(100) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` VARCHAR(255) NOT NULL,
  `trusted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `expires_at` TIMESTAMP NOT NULL,
  `last_used_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_token` (`device_token`),
  KEY `idx_usuario_id` (`usuario_id`),
  CONSTRAINT `fk_trusted_device_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `two_fa_attempts` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `usuario_id` INT NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `failed_attempts` INT NOT NULL DEFAULT 1,
  `first_attempt_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `last_attempt_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `blocked_until` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_ip` (`usuario_id`, `ip_address`),
  CONSTRAINT `fk_2fa_attempts_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 3. Configurar Clave de Encriptación

En `.env` o `src/config/env.php`, asegúrate de tener `APP_KEY`:

```env
APP_KEY=tu-clave-super-segura-aqui
```

Si no está definida, el sistema usará una clave por defecto insegura (cambiar en producción).

---

## 🚀 Flujo de Funcionamiento

### 1. Login Normal → 2FA

```
┌─────────────┐
│   Login     │
│  (email +   │
│ password)   │
└──────┬──────┘
       │
       ▼
┌─────────────────────┐
│ Credenciales válidas│
│   pero 2FA activo?  │
└──────┬──────────────┘
       │
    SÍ │
       ▼
┌──────────────────────┐
│ Redirigir a          │
│ verify-2fa.php       │
│ (5 minutos timeout)  │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────────┐
│ Ingresar código TOTP     │
│ (o código de respaldo)   │
│ + ¿Recordar dispositivo? │
└──────┬───────────────────┘
       │
       ▼
┌──────────────────────┐
│ Crear sesión         │
│ (+ cookie si confía) │
│ → index.php          │
└──────────────────────┘
```

### 2. Dispositivos de Confianza

- **Primer login**: Usuario se autentica normalmente + 2FA
- **Elige "Recordar dispositivo"**: Se crea `trusted_device` con token en cookie
- **Próximos logins (24h)**: Sistema detecta la cookie, valida el token y **salta el 2FA**
- **Después de 24h**: Cookie expira, requiere 2FA de nuevo

---

## 📁 Archivos Creados/Modificados

### Nuevos Archivos:

| Archivo | Descripción |
|---------|-------------|
| `src/services/TwoFactorService.php` | Servicio principal de 2FA |
| `src/config/trusted-device-middleware.php` | Validación de dispositivos confiables |
| `src/migrations/001_add_2fa_support.sql` | Migraciones de BD |
| `verify-2fa.php` | Pantalla de verificación de código TOTP |
| `setup-2fa.php` | Panel de configuración de 2FA del usuario |
| `api/trusted-devices.php` | API para gestionar dispositivos |

### Archivos Modificados:

| Archivo | Cambios |
|---------|---------|
| `login.php` | Detecta 2FA y redirige a `verify-2fa.php` |
| `composer.json` | Agregó `spomky-labs/otphp` |

---

## 🛠️ API del Servicio TwoFactorService

### Métodos Principales:

#### 1. Generar Secret TOTP

```php
$secret = $twoFAService->generateNewSecret('usuario@email.com');
// Retorna:
// [
//     'secret' => 'JBSWY3DPEBLW64TMMQ...',  // Para ingresar manualmente
//     'provisioning_uri' => 'otpauth://totp/...'  // Para el QR
// ]
```

#### 2. Habilitar 2FA

```php
$twoFAService->enableTwoFactor($usuarioId, $secret, $backupCodes);
// Guarda el secret encriptado en BD
```

#### 3. Validar Código TOTP

```php
$result = $twoFAService->verifyCode($usuarioId, $code);
// Retorna: ['valid' => true/false, 'message' => '...']
// Valida código actual ±1 ventana de tiempo
```

#### 4. Validar Código de Respaldo

Automático dentro de `verifyCode()`, pero también:

```php
$result = $twoFAService->verifyBackupCode($usuarioId, $code);
// Marca el código como usado después de validarlo
```

#### 5. Crear Dispositivo de Confianza

```php
$device = $twoFAService->createTrustedDevice($usuarioId, 'Chrome Windows');
// Retorna: ['success' => true, 'device_token' => '...', 'expires_at' => '...']
```

#### 6. Validar Dispositivo Confiable

```php
$isValid = $twoFAService->validateTrustedDevice($usuarioId, $deviceToken);
// Retorna: true/false
```

#### 7. Obtener Dispositivos Activos

```php
$devices = $twoFAService->getTrustedDevices($usuarioId);
// Retorna array de dispositivos con fecha de expiración
```

#### 8. Revocar Dispositivo

```php
$twoFAService->revokeTrustedDevice($usuarioId, $deviceId);
```

#### 9. Deshabilitar 2FA

```php
$twoFAService->disableTwoFactor($usuarioId);
// Elimina secret, códigos de respaldo y dispositivos confiables
```

#### 10. Verificar Bloqueo por Intentos Fallidos

```php
$status = $twoFAService->isBlocked($usuarioId, $ipAddress);
// Retorna: ['blocked' => true/false, 'seconds_left' => N, 'attempts' => N]
```

---

## 🌐 Endpoints de API

### GET/POST `/api/trusted-devices.php`

#### Lista dispositivos
```bash
GET /api/trusted-devices.php?action=list
```

**Respuesta:**
```json
{
  "success": true,
  "devices": [
    {
      "id": 1,
      "device_name": "Chrome on Windows",
      "ip_address": "192.168.1.100",
      "trusted_at": "2026-02-03 10:30:00",
      "expires_at": "2026-02-04 10:30:00",
      "last_used_at": "2026-02-03 14:20:00"
    }
  ],
  "count": 1
}
```

#### Revocar dispositivo
```bash
POST /api/trusted-devices.php?action=revoke
Content-Type: application/x-www-form-urlencoded

device_id=1
```

#### Revocar todos los dispositivos
```bash
POST /api/trusted-devices.php?action=revoke_all
```

#### Ver estado de 2FA
```bash
GET /api/trusted-devices.php?action=status
```

---

## 🔐 Seguridad

### Encriptación del Secret

```php
// AES-256-CBC con PBKDF2
openssl_encrypt(secret, 'AES-256-CBC', hash('sha256', APP_KEY), 0, $iv)
```

### Rate Limiting

- **Máximo 5 intentos fallidos** por IP/usuario
- **Bloqueo de 15 minutos** después de exceder el límite
- Reinicia al código válido

### Protección de Sesión

- Timeout de 5 minutos en pantalla de verificación
- Cookies `httponly`, `secure`, `samesite=strict`
- Regeneración de ID de sesión

### Auditoría

Todos los eventos se registran:

```sql
SELECT * FROM auditoria WHERE accion IN ('2fa_enabled', '2fa_success', '2fa_disabled', 'trusted_device_revoked')
```

---

## 📱 Apps Soportadas

Cualquier app que implemente el estándar TOTP (RFC 6238):

- ✅ **Google Authenticator** (iOS, Android)
- ✅ **Authy** (iOS, Android, Desktop)
- ✅ **Microsoft Authenticator** (iOS, Android)
- ✅ **FreeOTP** (iOS, Android)
- ✅ **1Password** (iOS, Android, Desktop)
- ✅ **Bitwarden** (iOS, Android, Desktop)
- ✅ **KeePass** (Windows)
- ✅ Cualquier app TOTP compatible

---

## 🧪 Pruebas

### Test Manual:

1. **Activar 2FA en usuario**:
   - Ir a `setup-2fa.php`
   - Click en "Activar 2FA"
   - Escanear QR con autenticador
   - Ingresar código de 6 dígitos
   - Guardar códigos de respaldo

2. **Hacer logout y login nuevamente**:
   - Ingresar email y password
   - Deberá redirigir a `verify-2fa.php`
   - Ingresar código de autenticador
   - Opcionalmente marcar "Recordar dispositivo"
   - Debe ingresar a la app

3. **Verificar dispositivo de confianza**:
   - Hacer logout
   - Login nuevamente (sin redirigir a 2FA)
   - Debería permitir acceso directo

4. **Rate limiting**:
   - Ingresar 6 códigos incorrectos
   - Debe bloquear por 15 minutos

5. **Códigos de respaldo**:
   - En pantalla de 2FA, ingresar código de respaldo
   - Debe validarse correctamente

### Script de Test (PHP):

```php
<?php
require_once 'src/services/TwoFactorService.php';

$pdo = getPDO();
$twoFAService = new TwoFactorService($pdo);

// 1. Generar secret
$secret = $twoFAService->generateNewSecret('test@example.com');
echo "Secret: " . $secret['secret'] . "\n";
echo "URI: " . $secret['provisioning_uri'] . "\n";

// 2. Habilitar 2FA
$backupCodes = $twoFAService->generateBackupCodes();
$twoFAService->enableTwoFactor(1, $secret['secret'], $backupCodes);
echo "2FA habilitado\n";

// 3. Verificar código (necesita código real del autenticador)
// $result = $twoFAService->verifyCode(1, '123456');
// echo json_encode($result);

// 4. Crear dispositivo
$device = $twoFAService->createTrustedDevice(1);
echo "Dispositivo: " . $device['device_token'] . "\n";

// 5. Validar dispositivo
$valid = $twoFAService->validateTrustedDevice(1, $device['device_token']);
echo "Válido: " . ($valid ? 'sí' : 'no') . "\n";
?>
```

---

## ⚙️ Configuración

### Variables de Entorno

En `.env`:

```env
# Clave de encriptación para secrets TOTP
APP_KEY=tu-clave-segura-minimo-32-caracteres
```

### Constantes del Servicio (en TwoFactorService.php)

```php
const TOTP_PERIOD = 30;              // Segundos por código
const TOTP_DIGITS = 6;               // Dígitos del código
const TOTP_ALGORITHM = 'SHA1';       // Algoritmo de hash
const DEVICE_TRUST_HOURS = 24;       // Horas de confianza
const MAX_2FA_ATTEMPTS = 5;          // Intentos máximos
const BLOCK_DURATION_MINUTES = 15;   // Minutos de bloqueo
const BACKUP_CODES_COUNT = 10;       // Cantidad de códigos
```

---

## 🐛 Troubleshooting

### "Código TOTP inválido" aunque el código es correcto

**Causa**: Sincronización de reloj desajustada
**Solución**: El servicio valida ±1 ventana de tiempo (30 segundos). Sincronizar hora del servidor y teléfono.

### Cookie de dispositivo no funciona

**Causa**: HTTPS no configurado
**Solución**: En producción, descomentar `ini_set('session.cookie_secure', 1)` en `session.php`

### "Sesión expirada" en 2FA

**Causa**: Tomo más de 5 minutos en ingresar el código
**Solución**: El timeout es intencional. Volver a login para obtener nueva sesión.

### Rate limit bloqueando

**Causa**: 5 o más intentos fallidos en la misma IP
**Solución**: Esperar 15 minutos o ejecutar limpiar intentos:

```php
$twoFAService->cleanupOldAttempts();
```

---

## 📊 Monitoreo

### Consultas útiles:

```sql
-- Usuarios con 2FA habilitado
SELECT COUNT(*), SUM(two_fa_enabled) FROM usuarios;

-- Dispositivos activos
SELECT COUNT(*) FROM trusted_devices WHERE expires_at > NOW();

-- Intentos fallidos recientes
SELECT usuario_id, ip_address, failed_attempts, blocked_until 
FROM two_fa_attempts 
WHERE blocked_until > NOW();

-- Últimos eventos de 2FA
SELECT usuario_id, accion, created_at, descripcion 
FROM auditoria 
WHERE accion LIKE '2fa_%' 
ORDER BY created_at DESC LIMIT 20;
```

---

## 📝 Notas Importantes

- ⚠️ **Backup codes**: Los códigos de respaldo son de un solo uso. Después de usarse, no se pueden usar de nuevo.
- ⚠️ **Secret encriptado**: Si cambia `APP_KEY`, los secrets TOTP antiguos no se podrán desencriptar.
- ⚠️ **Dispositivos expirados**: Se limpian automáticamente después de 24h, pero es buena práctica ejecutar cleanup periódicamente.
- ℹ️ **Auditoría**: Todos los eventos se registran, incluyendo logins fallidos para 2FA.

---

## 🔄 Próximas Mejoras Opcionales

- [ ] WebAuthn / FIDO2 (keys de seguridad física)
- [ ] SMS 2FA (además de TOTP)
- [ ] Email 2FA
- [ ] Notificaciones de login inusual
- [ ] Historial de dispositivos (eliminados)
- [ ] QR generado del lado del servidor (sin cdn)
- [ ] APIs de administrador para habilitar/deshabilitar 2FA de usuarios

---

**Última actualización**: 2026-02-03  
**Versión**: 1.0.0  
**Estado**: ✅ Producción lista
