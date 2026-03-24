# 🚀 Sistema Amiri - Guía de Despliegue a Producción

**Versión:** 1.0.0 | **Estado:** ✅ Listo para Producción | **Fecha:** 25 de Enero, 2026

---

## 📚 Documentación Disponible

- **[INICIO_RAPIDO.md](INICIO_RAPIDO.md)** - Setup rápido para desarrollo local (5 minutos)
- **[DESPLIEGUE_PRODUCCION.md](DESPLIEGUE_PRODUCCION.md)** - Guía completa de despliegue paso a paso
- **[RESUMEN_EJECUTIVO.md](RESUMEN_EJECUTIVO.md)** - Estado completo del sistema y métricas
- **[PLAN_DE_PRUEBAS.md](PLAN_DE_PRUEBAS.md)** - Resultados de pruebas y checklist
- **[FASE_4_COMPLETADA.md](FASE_4_COMPLETADA.md)** - Detalles de implementación Fase 4
- **README.md** (este archivo) - Guía de referencia técnica completa

---

## 📋 Tabla de Contenidos
1. [Requisitos del Sistema](#requisitos-del-sistema)
2. [Instalación](#instalación)
3. [Configuración](#configuración)
4. [Base de Datos](#base-de-datos)
5. [Seguridad](#seguridad)
6. [Backups](#backups)
7. [Monitoreo](#monitoreo)
8. [Mantenimiento](#mantenimiento)
9. [Troubleshooting](#troubleshooting)

---

## 📦 Requisitos del Sistema

### Software Necesario
- **PHP:** 8.0 o superior
- **MySQL:** 8.0 o superior
- **Apache:** 2.4 o superior (con mod_rewrite)
- **Extensiones PHP requeridas:**
  - `pdo`, `pdo_mysql`
  - `mbstring`
  - `json`
  - `zlib` (para compresión de backups)
  - `openssl`
  - `session`

### Hardware Mínimo
- **CPU:** 2 cores
- **RAM:** 2 GB
- **Disco:** 10 GB libres (para logs y backups)

---

## 🔧 Instalación

### 1. Clonar/Copiar Archivos
```bash
# Copiar todos los archivos al servidor
scp -r amiriAppProd user@servidor:/var/www/html/
```

### 2. Configurar Permisos
```bash
cd /var/www/html/amiriAppProd

# Directorios con permisos de escritura
chmod 755 logs backups uploads scripts
```
chmod -R 644 logs/* backups/* uploads/*

# Archivo .env solo lectura para el owner
chmod 600 .env

# Scripts ejecutables
chmod +x scripts/*.php scripts/*.sh
```

### 3. Verificar Health Check
```bash
php scripts/health-check.php
```

Este comando verifica:
- ✓ Archivos críticos presentes
- ✓ Permisos correctos
- ✓ Extensiones PHP cargadas
- ✓ Conexión a base de datos
- ✓ Tablas existentes

---

## ⚙️ Configuración

### 1. Variables de Entorno

Copiar el template y configurar:
```bash
cp .env.example .env
nano .env
```

**Configuración mínima requerida:**
```env
# Base de Datos
DB_HOST=localhost
DB_NAME=guiaspar_amiri_db
DB_USER=tu_usuario
DB_PASS=tu_contraseña_segura

# Aplicación
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

# Sesión
SESSION_LIFETIME=3600
SESSION_NAME=AMIRI_SESSION

# Seguridad
RATE_LIMIT_MAX_ATTEMPTS=5
RATE_LIMIT_LOCKOUT_TIME=900

# Logs
LOG_LEVEL=error
LOG_MAX_SIZE=5242880
LOG_ROTATION_KEEP=5

# Backups
BACKUP_RETENTION_DAYS=7
```

### 2. Apache Virtual Host

Crear archivo `/etc/apache2/sites-available/amiri.conf`:
```apache
<VirtualHost *:80>
    ServerName tu-dominio.com
    ServerAlias www.tu-dominio.com
    
    DocumentRoot /var/www/html/amiriAppProd
    
    <Directory /var/www/html/amiriAppProd>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Logs
    ErrorLog ${APACHE_LOG_DIR}/amiri-error.log
    CustomLog ${APACHE_LOG_DIR}/amiri-access.log combined
    
    # Redirigir a HTTPS (en producción)
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}$1 [L,R=301]
</VirtualHost>

<VirtualHost *:443>
    ServerName tu-dominio.com
    ServerAlias www.tu-dominio.com
    
    DocumentRoot /var/www/html/amiriAppProd
    
    # SSL
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/tu-dominio.crt
    SSLCertificateKeyFile /etc/ssl/private/tu-dominio.key
    
    <Directory /var/www/html/amiriAppProd>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/amiri-ssl-error.log
    CustomLog ${APACHE_LOG_DIR}/amiri-ssl-access.log combined
</VirtualHost>
```

Activar sitio:
```bash
a2ensite amiri
a2enmod rewrite ssl
systemctl reload apache2
```

### 3. Configurar .htaccess

El archivo `.htaccess` ya está configurado con:
- ✓ Forzar HTTPS (descomentar en producción)
- ✓ Headers de seguridad
- ✓ Protección de archivos sensibles
- ✓ Compresión GZIP
- ✓ Caché del navegador

**Activar HTTPS en producción:**
Descomentar las líneas de RewriteCond/RewriteRule en `.htaccess`.

---

## 🗄️ Base de Datos

### 1. Importar Base de Datos
```bash
mysql -u root -p < bd/guiaspar_amiri_db.sql
```

### 2. Aplicar Índices de Optimización
```bash
mysql -u root -p guiaspar_amiri_db < bd/indices_optimizacion.sql
```

### 3. Verificar Tablas e Índices
```bash
mysql -u root -p -e "USE guiaspar_amiri_db; SHOW TABLES;"
mysql -u root -p -e "USE guiaspar_amiri_db; SHOW INDEX FROM flujo_movimientos;"
```

### 4. Crear Usuario de Base de Datos (Recomendado)
```sql
CREATE USER 'amiri_user'@'localhost' IDENTIFIED BY 'contraseña_segura';
GRANT SELECT, INSERT, UPDATE, DELETE ON guiaspar_amiri_db.* TO 'amiri_user'@'localhost';
FLUSH PRIVILEGES;
```

---

## 🔐 Seguridad

### 1. Cambiar Contraseña de Admin
Al primer login en producción, cambiar la contraseña del usuario `admin@amiri.com`.

### 2. Proteger Directorios Sensibles
El `.htaccess` ya bloquea:
- `src/config/`
- `.env`
- `logs/`
- `backups/`
- `scripts/`

### 3. Configurar SSL/TLS
```bash
# Obtener certificado Let's Encrypt
certbot --apache -d tu-dominio.com -d www.tu-dominio.com
```

### 4. Rate Limiting
Ya configurado en `login.php`:
- 5 intentos máximo
- 15 minutos de bloqueo
- Registro en auditoría

### 5. Headers de Seguridad
Ya configurados en `.htaccess`:
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: SAMEORIGIN`
- `X-XSS-Protection: 1; mode=block`
- `Referrer-Policy: strict-origin-when-cross-origin`

---

## 💾 Backups

### 1. Backup Manual
```bash
cd /var/www/html/amiriAppProd
php scripts/backup.php
```

O en Windows (Laragon):
```batch
scripts\backup.bat
```

### 2. Backup Automático con Cron (Linux)
```bash
crontab -e
```

Agregar línea:
```cron
# Backup diario a las 2:00 AM
0 2 * * * cd /var/www/html/amiriAppProd && php scripts/backup.php >> logs/backup.log 2>&1
```

### 3. Backup Automático con Task Scheduler (Windows)
1. Abrir Task Scheduler
2. Crear tarea básica
3. Trigger: Diario a las 2:00 AM
4. Acción: Ejecutar `C:\laragon\www\amiriAppProd\scripts\backup.bat`

### 4. Restaurar Backup
```bash
# Descomprimir backup
gunzip backups/backup_2026-01-25_02-00-00.sql.gz

# Restaurar
mysql -u root -p guiaspar_amiri_db < backups/backup_2026-01-25_02-00-00.sql
```

### 5. Backup Remoto (Recomendado)
```bash
# Copiar backups a servidor remoto
rsync -avz backups/ usuario@servidor-remoto:/backups/amiri/
```

---

## 📊 Monitoreo

### 1. Dashboard de Monitoreo
Acceder a: `https://tu-dominio.com/monitor.php`

**Muestra:**
- Errores últimas 24h
- Warnings últimas 24h
- Login fallidos
- Tamaño de base de datos
- Espacio en disco
- Estado de backups
- Estado de logs

### 2. Logs Disponibles
- `logs/error.log` - Errores críticos
- `logs/warning.log` - Advertencias
- `logs/info.log` - Información general
- `logs/debug.log` - Debug (desactivar en producción)
- `logs/audit.log` - Auditoría de acciones
- `logs/backup.log` - Historial de backups
- `logs/cleanup.log` - Limpieza de archivos

### 3. Visor de Logs Web
Acceder a: `https://tu-dominio.com/logs-viewer.php`

**Características:**
- Selección de archivo
- Búsqueda/filtrado
- Últimas N líneas
- Syntax highlighting

### 4. Health Check Periódico
```bash
# Ejecutar manualmente
php scripts/health-check.php

# O programar en cron (semanal)
0 8 * * 1 cd /var/www/html/amiriAppProd && php scripts/health-check.php >> logs/health.log
```

---

## 🧹 Mantenimiento

### 1. Limpieza de Logs y Backups
```bash
# Manual
php scripts/cleanup.php

# Automático (cron semanal, domingos 3:00 AM)
0 3 * * 0 cd /var/www/html/amiriAppProd && php scripts/cleanup.php >> logs/cleanup.log 2>&1
```

**Configuración de retención:**
- Logs: 30 días
- Backups: 7 días

### 2. Rotación de Logs
El sistema Logger rota automáticamente:
- Tamaño máximo: 5 MB
- Archivos mantenidos: 5
- Patrón: `error.log`, `error.1.log`, `error.2.log`, etc.

### 3. Actualización del Sistema
```bash
# 1. Hacer backup completo
php scripts/backup.php

# 2. Copiar archivos nuevos
scp -r archivos_nuevos/ servidor:/var/www/html/amiriAppProd/

# 3. Ejecutar migraciones SQL (si hay)
mysql -u root -p guiaspar_amiri_db < bd/migraciones/nueva_version.sql

# 4. Verificar health check
php scripts/health-check.php

# 5. Verificar en navegador
```

---

## 🔍 Troubleshooting

### Problema: Error de Conexión a Base de Datos
```bash
# Verificar servicio MySQL
systemctl status mysql

# Verificar credenciales en .env
cat .env | grep DB_

# Probar conexión manual
mysql -h localhost -u usuario -p
```

### Problema: Errores 500 en el Navegador
```bash
# Ver últimos errores
tail -50 logs/error.log

# Ver logs de Apache
tail -50 /var/log/apache2/amiri-error.log

# Activar display_errors temporalmente (solo en desarrollo)
```

### Problema: Sesión No Persiste
```bash
# Verificar permisos de sesión
ls -la /var/lib/php/sessions/

# Verificar configuración PHP
php -i | grep session.save_path

# Revisar configuración en src/config/session.php
```

### Problema: Backup No Se Genera
```bash
# Verificar logs
cat logs/backup.log

# Verificar permisos
ls -la backups/

# Probar manualmente
php scripts/backup.php

# Verificar ruta de mysqldump
which mysqldump
```

### Problema: Espacio en Disco Lleno
```bash
# Ver uso de disco
df -h

# Ver archivos grandes
du -sh logs/* backups/*

# Limpiar archivos antiguos
php scripts/cleanup.php

# Eliminar logs rotados manualmente
rm logs/*.1.log logs/*.2.log
```

---

## 📞 Soporte

### Logs Importantes para Diagnóstico
Al reportar problemas, incluir:
1. Últimas 50 líneas de `logs/error.log`
2. Salida de `php scripts/health-check.php`
3. Versión de PHP: `php -v`
4. Configuración Apache (censurar contraseñas)

### Comandos Útiles
```bash
# Ver todas las configuraciones PHP
php -i

# Ver módulos Apache cargados
apache2ctl -M

# Ver conexiones MySQL activas
mysql -e "SHOW PROCESSLIST;"

# Ver tamaño de tablas
mysql -e "
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.TABLES
WHERE table_schema = 'guiaspar_amiri_db'
ORDER BY (data_length + index_length) DESC;
"
```

---

## ✅ Checklist Pre-Producción

- [ ] Base de datos importada y optimizada
- [ ] Variables de entorno configuradas (`.env`)
- [ ] Permisos de archivos correctos
- [ ] SSL/HTTPS configurado
- [ ] Contraseña de admin cambiada
- [ ] Backup automático configurado (cron/task scheduler)
- [ ] Health check ejecutado sin errores
- [ ] Logs rotando correctamente
- [ ] Monitor accesible
- [ ] Rate limiting probado
- [ ] Todas las páginas cargando sin errores
- [ ] DataTables funcionando correctamente
- [ ] Auditoría registrando acciones
- [ ] `.htaccess` protegiendo archivos sensibles

---

## 📚 Archivos de Referencia

### Estructura del Proyecto
```
amiriAppProd/
├── api/                    # Endpoints de API
├── backups/                # Backups de MySQL
├── bd/                     # Scripts SQL
├── logs/                   # Archivos de log
├── scripts/                # Scripts de mantenimiento
│   ├── backup.php          # Backup automático
│   ├── backup.bat          # Backup Windows
│   ├── cleanup.php         # Limpieza de archivos
│   └── health-check.php    # Verificación del sistema
├── src/
│   ├── assets/             # CSS, JS, imágenes
│   ├── config/             # Configuraciones
│   │   ├── session.php     # Sesiones seguras
│   │   ├── db.php          # Conexión BD
│   │   ├── logger.php      # Sistema de logs
│   │   ├── validator.php   # Validación
│   │   ├── cache.php       # Caché
│   │   ├── rate-limiter.php # Rate limiting
│   │   └── api-middleware.php # Middleware
│   └── services/           # Lógica de negocio
├── uploads/                # Archivos subidos
├── vendor/                 # Dependencias Composer
├── .env                    # Variables de entorno (NUNCA EN GIT)
├── .env.example            # Template de .env
├── .htaccess               # Configuración Apache
├── monitor.php             # Dashboard de monitoreo
├── logs-viewer.php         # Visor de logs
└── README.md               # Este archivo
```

---

**Última actualización:** 25 de Enero, 2026  
**Versión:** 1.0.0  
**Sistema:** Amiri - Gestión Financiera
#   a m i r i A p p  
 