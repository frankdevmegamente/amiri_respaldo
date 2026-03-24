@echo off
REM Script de Backup para Windows (Laragon)
REM Ejecutar este archivo .bat manualmente o programarlo en Task Scheduler

echo ========================================
echo  BACKUP DE BASE DE DATOS - Sistema Amiri
echo ========================================
echo.

REM Ruta al PHP de Laragon
set PHP_PATH=C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\php.exe

REM Verificar que PHP existe
if not exist "%PHP_PATH%" (
    echo ERROR: PHP no encontrado en %PHP_PATH%
    echo Por favor ajustar la ruta en este script
    pause
    exit /b 1
)

REM Ejecutar script de backup
"%PHP_PATH%" "%~dp0backup.php"

if %ERRORLEVEL% EQU 0 (
    echo.
    echo *** BACKUP COMPLETADO EXITOSAMENTE ***
) else (
    echo.
    echo *** ERROR EN EL BACKUP ***
)

echo.
echo Presione cualquier tecla para salir...
pause > nul
