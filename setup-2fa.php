<?php

/**

 * Panel de Configuración de 2FA

 * Permite al usuario activar/desactivar 2FA, ver QR y códigos de respaldo

 */



require_once __DIR__ . '/src/config/session.php';

require_once __DIR__ . '/src/config/db.php';

require_once __DIR__ . '/src/config/logger.php';

require_once __DIR__ . '/src/services/TwoFactorService.php';

require_once __DIR__ . '/vendor/autoload.php';



use OTPHP\TOTP;

use Endroid\QrCode\QrCode;

use Endroid\QrCode\Writer\PngWriter;



// Verificar autenticación

if (!isset($_SESSION['usuario_id'])) {

    header('Location: login.php');

    exit;

}



$usuarioId = $_SESSION['usuario_id'];

$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';

$error = '';

$success = '';

$step = $_GET['step'] ?? 'dashboard'; // dashboard, setup, verify, manage, disable

$qrDataUri = null; // Para almacenar el QR generado



try {

    $pdo = getPDO();

    $twoFAService = new TwoFactorService($pdo);



    // Obtener estado actual de 2FA

    $stmt = $pdo->prepare("

        SELECT two_fa_enabled, two_fa_secret, two_fa_backup_codes 

        FROM usuarios 

        WHERE id = ?

    ");

    $stmt->execute([$usuarioId]);

    $userTwoFA = $stmt->fetch();

    $twoFAEnabled = $userTwoFA['two_fa_enabled'];

    $backupCodes = json_decode($userTwoFA['two_fa_backup_codes'], true);



    // PROCESAR ACCIONES POST

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $action = $_POST['action'] ?? '';



        if ($action === 'initiate_setup') {

            // Generar nuevo secret TOTP

            $newSecret = $twoFAService->generateNewSecret($_SESSION['usuario_email']);

            $_SESSION['setup_secret'] = $newSecret['secret'];

            $_SESSION['setup_uri'] = $newSecret['provisioning_uri'];

            

            // Redirect para evitar resubmit del formulario

            header('Location: setup-2fa.php?step=setup');

            exit;



        } elseif ($action === 'confirm_setup') {

            // Verificar código y activar 2FA

            $code = trim($_POST['verification_code'] ?? '');



            if (!$code) {

                $error = 'Por favor ingresa el código TOTP.';

            } else if (!isset($_SESSION['setup_secret'])) {

                $error = 'Sesión expirada. Intenta de nuevo.';

            } else {

                // Verificar que el código sea válido con el secret

                $totp = TOTP::create($_SESSION['setup_secret']);

                if ($totp->verify($code, null, 1)) {

                    // Código válido - habilitar 2FA

                    $backupCodes = $twoFAService->generateBackupCodes();

                    $twoFAService->enableTwoFactor($usuarioId, $_SESSION['setup_secret'], $backupCodes);



                    // Log

                    Logger::audit('2fa_enabled', $usuarioId, [

                        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'

                    ]);



                    $_SESSION['temp_backup_codes'] = $backupCodes;

                    unset($_SESSION['setup_secret']);

                    unset($_SESSION['setup_uri']);



                    $success = '2FA activado exitosamente. Guarda tus códigos de respaldo.';

                    $step = 'backup_codes';

                } else {

                    $error = 'Código TOTP inválido. Intenta de nuevo.';

                    $step = 'setup';

                }

            }



        } elseif ($action === 'disable_2fa') {

            // Deshabilitar 2FA

            $password = $_POST['password'] ?? '';



            if (!$password) {

                $error = 'Por favor ingresa tu contraseña para confirmar.';

                $step = 'disable_confirm';

            } else {

                // Verificar contraseña

                $stmt = $pdo->prepare("SELECT password FROM usuarios WHERE id = ?");

                $stmt->execute([$usuarioId]);

                $user = $stmt->fetch();



                if (!password_verify($password, $user['password'])) {

                    $error = 'Contraseña incorrecta.';

                    $step = 'disable_confirm';

                } else {

                    // Deshabilitar 2FA

                    $twoFAService->disableTwoFactor($usuarioId);



                    Logger::audit('2fa_disabled', $usuarioId, [

                        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'

                    ]);



                    $success = '2FA deshabilitado correctamente.';

                    $twoFAEnabled = false;

                    $step = 'dashboard';

                }

            }



        } elseif ($action === 'acknowledge_backup') {

            // Usuario confirma que guardó los códigos

            unset($_SESSION['temp_backup_codes']);

            $success = '2FA está completamente configurado.';

            $step = 'dashboard';

            $twoFAEnabled = true;



        } elseif ($action === 'revoke_device') {

            $deviceId = $_POST['device_id'] ?? null;

            if ($deviceId) {

                $twoFAService->revokeTrustedDevice($usuarioId, $deviceId);

                Logger::audit('trusted_device_revoked', $usuarioId, [

                    'device_id' => $deviceId,

                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'

                ]);

                $success = 'Dispositivo revocado correctamente.';

            } else {

                $error = 'ID de dispositivo no válido.';

            }

        }

    }



    // Obtener dispositivos de confianza

    $trustedDevices = $twoFAService->getTrustedDevices($usuarioId);



    // Generar QR si estamos en step setup

    if ($step === 'setup' && isset($_SESSION['setup_uri'])) {

        try {

            $qrCode = new QrCode($_SESSION['setup_uri']);

            $writer = new PngWriter();

            $result = $writer->write($qrCode);

            $qrDataUri = 'data:image/png;base64,' . base64_encode($result->getString());

        } catch (Exception $e) {

            error_log("Error generando QR: " . $e->getMessage());

            $error = "Error generando QR: " . $e->getMessage();

        }

    }



} catch (Exception $e) {

    $error = 'Error: ' . $e->getMessage();

    error_log("Error en setup-2fa.php: " . $e->getMessage());

}

?>



<!DOCTYPE html>

<html lang="es">



<head>

    <meta charset="UTF-8" />

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Configuración 2FA - Amiri</title>

    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico" />

    <link rel="stylesheet" href="src/assets/css/styles.min.css" />

    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />

    <link rel="stylesheet" href="src/assets/css/layout-custom.css" />

    <style>

        .qr-container {

            background: white;

            padding: 20px;

            border-radius: 12px;

            display: inline-block;

            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);

        }



        [data-bs-theme="dark"] .qr-container {

            background: #2a2a2a;

            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);

        }



        .backup-code {

            background: #f8f9fa;

            border: 1px solid #dee2e6;

            padding: 10px 15px;

            border-radius: 8px;

            font-family: 'Courier New', monospace;

            font-weight: bold;

            margin-bottom: 8px;

            user-select: all;

            cursor: pointer;

            transition: all 0.3s ease;

        }



        .backup-code:hover {

            background: #e9ecef;

            border-color: #c58fb8;

        }



        [data-bs-theme="dark"] .backup-code {

            background: #333;

            border-color: #444;

            color: #fff;

        }



        [data-bs-theme="dark"] .backup-code:hover {

            background: #3a3a3a;

            border-color: #c58fb8;

        }



        .backup-code.used {

            opacity: 0.5;

            text-decoration: line-through;

            cursor: not-allowed;

        }



        .device-card {

            background: #f8f9fa;

            border-left: 4px solid #c58fb8;

            padding: 15px;

            border-radius: 8px;

            margin-bottom: 10px;

            display: flex;

            justify-content: space-between;

            align-items: center;

        }



        [data-bs-theme="dark"] .device-card {

            background: #333;

            border-left-color: #c58fb8;

        }



        .step-badge {

            display: inline-block;

            background: linear-gradient(135deg, #c58fb8 0%, #c58fb8 100%);

            color: white;

            padding: 5px 15px;

            border-radius: 50px;

            font-size: 0.85rem;

            font-weight: bold;

            margin-bottom: 20px;

        }



        .warning-box {

            background: #fff3cd;

            border-left: 4px solid #ffc107;

            padding: 15px;

            border-radius: 8px;

            margin-bottom: 20px;

        }



        [data-bs-theme="dark"] .warning-box {

            background: #332701;

            border-left-color: #ffc107;

            color: #ffc69f;

        }



        .success-box {

            background: #d4edda;

            border-left: 4px solid #28a745;

            padding: 15px;

            border-radius: 8px;

            margin-bottom: 20px;

        }



        [data-bs-theme="dark"] .success-box {

            background: #022709;

            border-left-color: #28a745;

            color: #85e89d;

        }

    </style>

</head>



<body>

    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"

        data-sidebar-position="fixed" data-header-position="fixed">



        <?php include 'src/assets/partials/header.php'; ?>

        <?php include 'src/assets/partials/sidebar.php'; ?>



        <div class="body-wrapper">

            <div class="body-wrapper-inner">

                <div class="container-fluid">

                    <div class="row">

                        <div class="col-12">

                            <div class="page-breadcrumb d-flex align-items-center mb-3">

                                <h3 class="mb-0"><i class="ti ti-shield-check"></i> Configuración de Seguridad 2FA</h3>

                            </div>

                        </div>

                    </div>



                    <div class="row">

                        <div class="col-lg-10 mx-auto">

                            <div class="card">



                            <div class="card-body p-4">

                                <!-- Alertas -->

                                <?php if ($error): ?>

                                    <div class="alert alert-danger border-0 d-flex align-items-center" role="alert">

                                        <i class="ti ti-alert-circle me-2"></i>

                                        <span><?= htmlspecialchars($error) ?></span>

                                    </div>

                                <?php endif; ?>



                                <?php if ($success): ?>

                                    <div class="alert alert-success border-0 d-flex align-items-center" role="alert">

                                        <i class="ti ti-check me-2"></i>

                                        <span><?= htmlspecialchars($success) ?></span>

                                    </div>

                                <?php endif; ?>



                                <!-- DASHBOARD (Estado General) -->

                                <?php if ($step === 'dashboard'): ?>

                                    <div class="row">

                                        <!-- Estado 2FA -->

                                        <div class="col-md-6 mb-3">

                                            <div class="card bg-light border-0" style="border-radius: 12px;">

                                                <div class="card-body text-center">

                                                    <?php if ($twoFAEnabled): ?>

                                                        <div style="font-size: 48px; color: #28a745; margin-bottom: 10px;">

                                                            <i class="ti ti-shield-check"></i>

                                                        </div>

                                                        <h5 class="fw-bold text-success">2FA Activo</h5>

                                                        <p class="text-muted mb-3">Tu cuenta está protegida con autenticación de dos factores</p>

                                                        <a href="setup-2fa.php?step=disable_confirm" class="btn btn-sm btn-outline-danger">

                                                            <i class="ti ti-lock-open"></i> Desactivar

                                                        </a>

                                                    <?php else: ?>

                                                        <div style="font-size: 48px; color: #dc3545; margin-bottom: 10px;">

                                                            <i class="ti ti-shield-off"></i>

                                                        </div>

                                                        <h5 class="fw-bold text-danger">2FA Inactivo</h5>

                                                        <p class="text-muted mb-3">Activa 2FA para proteger tu cuenta</p>

                                                        <form method="POST">

                                                            <input type="hidden" name="action" value="initiate_setup" />

                                                            <button type="submit" class="btn btn-sm btn-success">

                                                                <i class="ti ti-lock"></i> Activar 2FA

                                                            </button>

                                                        </form>

                                                    <?php endif; ?>

                                                </div>

                                            </div>

                                        </div>



                                        <!-- Dispositivos de Confianza -->

                                        <div class="col-md-6">

                                            <div class="card bg-light border-0" style="border-radius: 12px;">

                                                <div class="card-body">

                                                    <h6 class="fw-bold mb-3">

                                                        <i class="ti ti-devices"></i> Dispositivos Confiables

                                                    </h6>

                                                    <?php if (count($trustedDevices) > 0): ?>

                                                        <p class="text-muted small mb-3">Dispositivos recordados por 24h:</p>

                                                        <?php foreach ($trustedDevices as $device): ?>

                                                            <div class="device-card">

                                                                <div>

                                                                    <div class="fw-semibold"><?= htmlspecialchars($device['device_name']) ?></div>

                                                                    <div class="small text-muted"><?= htmlspecialchars($device['ip_address']) ?></div>

                                                                    <div class="small text-muted">

                                                                        Usado:

                                                                        <?= date('d/m/Y H:i', strtotime($device['last_used_at'] ?? $device['trusted_at'])) ?>

                                                                    </div>

                                                                </div>

                                                                <form method="POST" class="d-inline">

                                                                    <input type="hidden" name="action" value="revoke_device" />

                                                                    <input type="hidden" name="device_id" value="<?= $device['id'] ?>" />

                                                                    <button type="submit" class="btn btn-sm btn-outline-danger"

                                                                        onclick="return confirm('¿Revocar dispositivo?');">

                                                                        <i class="ti ti-trash"></i>

                                                                    </button>

                                                                </form>

                                                            </div>

                                                        <?php endforeach; ?>

                                                    <?php else: ?>

                                                        <p class="text-muted small">No hay dispositivos recordados</p>

                                                    <?php endif; ?>

                                                </div>

                                            </div>

                                        </div>

                                    </div>



                                    <!-- Info sobre 2FA -->

                                    <div class="alert alert-info border-0 mt-4" style="background-color: #d1ecf1; border-left: 4px solid #17a2b8; color: #0c5460;">

                                        <i class="ti ti-info-circle"></i>

                                        <strong>¿Qué es 2FA?</strong><br>

                                        La autenticación de dos factores protege tu cuenta solicitando un código temporal además de tu contraseña. 

                                        Usa Google Authenticator, Authy o cualquier app compatible con TOTP.

                                    </div>



                                <!-- SETUP (Configuración Inicial) -->

                                <?php elseif ($step === 'setup'): ?>

                                    <span class="step-badge">Paso 1 de 3: Escanear Código QR</span>



                                    <div class="row">

                                        <div class="col-md-6">

                                            <h6 class="fw-bold mb-3">1. Descarga una App</h6>

                                            <p class="text-muted small mb-2">Descarga una de estas apps en tu teléfono:</p>

                                            <ul class="small text-muted mb-4">

                                                <li><strong>Google Authenticator</strong> (iOS/Android)</li>

                                                <li><strong>Authy</strong> (iOS/Android)</li>

                                                <li><strong>Microsoft Authenticator</strong> (iOS/Android)</li>

                                                <li><strong>FreeOTP</strong> (iOS/Android)</li>

                                            </ul>



                                            <h6 class="fw-bold mb-3">2. Escanea el QR</h6>

                                            <p class="text-muted small">Abre tu app y escanea el código QR que aparece a la derecha</p>

                                        </div>



                                        <div class="col-md-6">

                                            <div class="text-center">

                                                <?php if ($qrDataUri): ?>

                                                    <img id="qrContainer" 

                                                         src="<?= $qrDataUri ?>" 

                                                         alt="QR Code" 

                                                         class="qr-container"

                                                         style="max-width: 250px; height: auto;" />

                                                <?php elseif (isset($_SESSION['setup_uri'])): ?>

                                                    <div class="alert alert-warning">

                                                        <p>Error generando QR</p>

                                                    </div>

                                                <?php else: ?>

                                                    <div class="alert alert-warning">

                                                        <p>Recarga la página para generar el QR</p>

                                                    </div>

                                                <?php endif; ?>

                                                <p class="text-muted small mt-3">O ingresa manualmente:</p>

                                                <code style="font-size: 0.85rem; word-break: break-all;">

                                                    <?= $_SESSION['setup_secret'] ?>

                                                </code>

                                                <button type="button" class="btn btn-sm btn-outline-primary ms-2"

                                                    onclick="navigator.clipboard.writeText('<?= $_SESSION['setup_secret'] ?>')">

                                                    <i class="ti ti-copy"></i>

                                                </button>

                                            </div>

                                        </div>

                                    </div>



                                    <hr class="my-4" />



                                    <span class="step-badge">Paso 2 de 3: Verificar Código</span>



                                    <form method="POST">

                                        <input type="hidden" name="action" value="confirm_setup" />



                                        <div class="mb-3">

                                            <label for="verification_code" class="form-label fw-semibold">Ingresa el código de 6 dígitos</label>

                                            <input type="text" class="form-control" id="verification_code" name="verification_code"

                                                placeholder="000000" inputmode="numeric" maxlength="6" required autofocus />

                                            <small class="text-muted">Aparecerá en tu app cada 30 segundos</small>

                                        </div>



                                        <button type="submit" class="btn btn-primary">

                                            <i class="ti ti-check"></i> Verificar y Activar 2FA

                                        </button>

                                        <a href="setup-2fa.php" class="btn btn-outline-secondary">

                                            <i class="ti ti-arrow-left"></i> Cancelar

                                        </a>

                                    </form>







                                <!-- BACKUP CODES (Mostrar códigos) -->

                                <?php elseif ($step === 'backup_codes' && isset($_SESSION['temp_backup_codes'])): ?>

                                    <span class="step-badge">Paso 3 de 3: Códigos de Respaldo</span>



                                    <div class="warning-box">

                                        <i class="ti ti-alert-triangle"></i> <strong>¡Importante!</strong><br>

                                        Guarda estos códigos en un lugar seguro. Los necesitarás si pierdes acceso a tu autenticador.

                                    </div>



                                    <h6 class="fw-bold mb-3">Tus 10 códigos de respaldo (8 caracteres cada uno):</h6>

                                    <div class="mb-4" style="max-height: 300px; overflow-y: auto;">

                                        <?php foreach ($_SESSION['temp_backup_codes'] as $code): ?>

                                            <div class="backup-code">

                                                <?= htmlspecialchars($code['code']) ?>

                                            </div>

                                        <?php endforeach; ?>

                                    </div>



                                    <div class="mb-3">

                                        <button type="button" class="btn btn-outline-secondary"

                                            onclick="downloadBackupCodes()">

                                            <i class="ti ti-download"></i> Descargar

                                        </button>

                                        <button type="button" class="btn btn-outline-secondary"

                                            onclick="printBackupCodes()">

                                            <i class="ti ti-printer"></i> Imprimir

                                        </button>

                                    </div>



                                    <form method="POST" onsubmit="return confirm('¿Ya guardaste los códigos?');">

                                        <input type="hidden" name="action" value="acknowledge_backup" />

                                        <button type="submit" class="btn btn-success btn-lg">

                                            <i class="ti ti-check"></i> Confirmar que Guardé los Códigos

                                        </button>

                                    </form>



                                    <script>

                                        function downloadBackupCodes() {

                                            const codes = `<?php echo implode('\n', array_map(function($c) { return $c['code']; }, $_SESSION['temp_backup_codes'])); ?>`;

                                            const element = document.createElement('a');

                                            element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(codes));

                                            element.setAttribute('download', 'amiri-backup-codes.txt');

                                            element.style.display = 'none';

                                            document.body.appendChild(element);

                                            element.click();

                                            document.body.removeChild(element);

                                        }



                                        function printBackupCodes() {

                                            window.print();

                                        }

                                    </script>



                                <!-- DISABLE CONFIRM -->

                                <?php elseif ($step === 'disable_confirm'): ?>

                                    <span class="step-badge">Desactivar 2FA</span>



                                    <div class="warning-box">

                                        <i class="ti ti-alert-triangle"></i> <strong>Advertencia:</strong><br>

                                        Desactivar 2FA hará que tu cuenta sea menos segura. Solo necesitarás tu contraseña para iniciar sesión.

                                    </div>



                                    <form method="POST">

                                        <input type="hidden" name="action" value="disable_2fa" />



                                        <div class="mb-3">

                                            <label for="password" class="form-label fw-semibold">

                                                Ingresa tu contraseña para confirmar

                                            </label>

                                            <input type="password" class="form-control" id="password" name="password" required autofocus />

                                        </div>



                                        <button type="submit" class="btn btn-danger">

                                            <i class="ti ti-lock-open"></i> Desactivar 2FA

                                        </button>

                                        <a href="setup-2fa.php" class="btn btn-outline-secondary">

                                            <i class="ti ti-arrow-left"></i> Cancelar

                                        </a>

                                    </form>

                                <?php endif; ?>

                            </div>

                        </div>





                    </div>

                </div>

            </div>

        </div>

    </div>



    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>



    <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>

    <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

    <script src="src/assets/js/sidebarmenu.js"></script>

    <script src="src/assets/js/pantalla-completa.js"></script>

    <script src="src/assets/js/utils.js"></script>



    <script>

        // Copiar códigos de respaldo

        document.querySelectorAll('.backup-code').forEach(el => {

            el.addEventListener('click', function() {

                const code = this.textContent.trim();

                navigator.clipboard.writeText(code).then(() => {

                    showToast('Código copiado', 'success');

                });

            });

        });



        // Descargar códigos como texto

        function downloadBackupCodes() {

            const codes = Array.from(document.querySelectorAll('.backup-code'))

                .map(el => el.textContent.trim())

                .join('\n');

            

            const blob = new Blob([codes], { type: 'text/plain' });

            const url = window.URL.createObjectURL(blob);

            const a = document.createElement('a');

            a.href = url;

            a.download = 'codigos-respaldo-2fa.txt';

            a.click();

            window.URL.revokeObjectURL(url);

        }



        // Imprimir códigos de respaldo

        function printBackupCodes() {

            window.print();

        }

    </script>

</body>



</html>

</body>



</html>

