<?php

/**

 * Verificación de código 2FA (TOTP)

 * Pantalla de autenticación de segundo factor

 */



require_once __DIR__ . '/src/config/session.php';

require_once __DIR__ . '/src/config/db.php';

require_once __DIR__ . '/src/config/logger.php';

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/src/services/TwoFactorService.php';



// Validar que se viene del login

if (!isset($_SESSION['2fa_pending']) || !isset($_SESSION['temp_usuario_id'])) {

    header('Location: login.php');

    exit;

}



// Validar que no haya expirado (5 minutos)

if (time() - $_SESSION['2fa_start_time'] > 300) {

    session_unset();

    session_destroy();

    header('Location: login.php?error=Sesión expirada. Intenta de nuevo.');

    exit;

}



$usuarioId = $_SESSION['temp_usuario_id'];

$error = '';

$success = '';



// Procesar POST

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $code = trim($_POST['code'] ?? '');

    $trustDevice = isset($_POST['trust_device']) && $_POST['trust_device'] === 'on';



    if (!$code) {

        $error = 'Por favor ingresa el código de 6 dígitos.';

    } else {

        try {

            $pdo = getPDO();

            $twoFAService = new TwoFactorService($pdo);



            // Verificar si está bloqueado por muchos intentos

            $blockStatus = $twoFAService->isBlocked($usuarioId, $_SERVER['REMOTE_ADDR'] ?? '');

            if ($blockStatus['blocked']) {

                $minutesLeft = ceil($blockStatus['seconds_left'] / 60);

                $error = "Demasiados intentos fallidos. Intenta en $minutesLeft minutos.";

            } else {

                // Validar código TOTP

                $validation = $twoFAService->verifyCode($usuarioId, $code);



                if ($validation['valid']) {

                    // Código válido - completar login

                    $_SESSION['usuario_id'] = $_SESSION['temp_usuario_id'];

                    $_SESSION['usuario_nombre'] = $_SESSION['temp_usuario_nombre'];

                    $_SESSION['usuario_email'] = $_SESSION['temp_usuario_email'];

                    $_SESSION['usuario_rol'] = $_SESSION['temp_usuario_rol'];

                    $_SESSION['LAST_ACTIVITY'] = time();

                    $_SESSION['2fa_verified'] = true;



                    // Si eligió confiar en el dispositivo, crear token de confianza

                    if ($trustDevice) {

                        $deviceResult = $twoFAService->createTrustedDevice($usuarioId);

                        if ($deviceResult['success']) {

                            // Guardar token de dispositivo en cookie (segura, httponly)

                            setcookie(

                                'trusted_device',

                                $deviceResult['device_token'],

                                [

                                    'expires' => strtotime($deviceResult['expires_at']),

                                    'path' => '/',

                                    'httponly' => true,

                                    'secure' => !empty($_SERVER['HTTPS']),

                                    'samesite' => 'Strict'

                                ]

                            );

                        }

                    }



                    // Limpiar variables temporales

                    unset($_SESSION['temp_usuario_id']);

                    unset($_SESSION['temp_usuario_nombre']);

                    unset($_SESSION['temp_usuario_email']);

                    unset($_SESSION['temp_usuario_rol']);

                    unset($_SESSION['2fa_pending']);

                    unset($_SESSION['2fa_start_time']);



                    // Registrar auditoría

                    $stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ?");

                    $stmt->execute([$usuarioId]);

                    $usuario = $stmt->fetch();



                    Logger::audit('2fa_success', $usuarioId, [

                        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',

                        'trusted_device' => $trustDevice

                    ]);



                    header('Location: index.php');

                    exit;

                } else {

                    $error = $validation['message'] ?? 'Código TOTP inválido.';



                    // Si es código de respaldo, mostrar mensaje especial

                    if (isset($validation['is_backup']) && $validation['is_backup']) {

                        $success = 'Código de respaldo validado. Este código ya no se puede usar de nuevo.';

                    }

                }

            }

        } catch (Exception $e) {

            error_log("Error en verificación 2FA: " . $e->getMessage());

            $error = 'Error interno. Inténtalo más tarde.';

        }

    }

}



// Contar intentos fallidos para mostrar info

$attemptInfo = '';

try {

    $pdo = getPDO();

    $twoFAService = new TwoFactorService($pdo);

    $blockStatus = $twoFAService->isBlocked($usuarioId, $_SERVER['REMOTE_ADDR'] ?? '');



    if (!$blockStatus['blocked'] && isset($blockStatus['attempts'])) {

        $attemptsLeft = 5 - $blockStatus['attempts'];

        if ($attemptsLeft > 0 && $attemptsLeft < 3) {

            $attemptInfo = "Te quedan <strong>$attemptsLeft</strong> intentos.";

        }

    }

} catch (Exception $e) {

    // Silenciar errores

}

?>



<!DOCTYPE html>

<html lang="es">



<head>

    <meta charset="UTF-8" />

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Verificar 2FA - Amiri</title>

    <link rel="stylesheet" href="src/assets/libs/bootstrap/dist/css/bootstrap.min.css" />

    <link rel="stylesheet" href="src/assets/css/styles.min.css" />

    <link rel="icon" type="image/png" href="src/assets/images/logos/favicon.ico" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/vanta@0.5.24/dist/vanta.net.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    <style>

        .verify-header { position: relative; overflow: hidden; }
        .verify-header .bg-pattern { position: absolute; top: -50%; right: -10%; width: 400px; height: 400px; border-radius: 50%; background: rgba(255,255,255,0.08); pointer-events: none; }
        .verify-header .bg-pattern-2 { position: absolute; bottom: -30%; left: 20%; width: 250px; height: 250px; border-radius: 50%; background: rgba(255,255,255,0.06); pointer-events: none; }

        .otp-input {

            width: 100%;

            height: 60px;

            font-size: 32px;

            text-align: center;

            font-weight: bold;

            letter-spacing: 10px;

            border: 2px solid #dee2e6;

            border-radius: 12px;

            transition: all 0.3s ease;

        }

        .otp-input:focus {

            border-color: #6c5ce7;

            box-shadow: 0 0 0 0.2rem rgba(108, 92, 231, 0.25);

            outline: none;

        }

        .otp-input::placeholder { letter-spacing: 0; }

        .authenticator-icon {

            width: 80px; height: 80px;

            background: linear-gradient(135deg, #6c5ce7 0%, #6c5ce7 100%);

            border-radius: 50%;

            display: flex; align-items: center; justify-content: center;

            margin: 0 auto;

            box-shadow: 0 4px 15px rgba(108, 92, 231, 0.3);

        }

        .authenticator-icon i { font-size: 40px; color: white; }

        .code-input-wrapper { margin: 20px 0; }

        .backup-code-hint { font-size: 0.85rem; color: #6c757d; margin-top: 10px; }

        .trust-device-section { border-top: 1px solid #dee2e6; margin-top: 20px; padding-top: 20px; }

        .device-info { font-size: 0.85rem; color: #6c757d; margin-top: 5px; }

        .timer { font-size: 0.9rem; color: #6c757d; text-align: center; margin-top: 15px; }

        [data-bs-theme="dark"] .otp-input {
            background-color: #2a2a2a; border-color: #444; color: #fff;
        }
        [data-bs-theme="dark"] .otp-input:focus {
            border-color: #6c5ce7; background-color: #333;
        }
        [data-bs-theme="dark"] .backup-code-hint { color: #999; }
        [data-bs-theme="dark"] .trust-device-section { border-top-color: #444; }
        [data-bs-theme="dark"] .device-info { color: #999; }
        [data-bs-theme="dark"] .timer { color: #999; }
        [data-bs-theme="dark"] .form-label { color: #e9ecef !important; }
        [data-bs-theme="dark"] .text-muted { color: #8c9ea8 !important; }
        [data-bs-theme="dark"] .form-check-label { color: #e9ecef !important; }
        [data-bs-theme="dark"] .card {
            background: rgba(30, 30, 30, 0.95) !important;
            color: #fff;
        }

    </style>

</head>



<body id="vanta-bg">

    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"

        data-sidebar-position="fixed" data-header-position="fixed">

        <div class="position-relative overflow-hidden radial-gradient min-vh-100 d-flex align-items-center justify-content-center">

            <div class="d-flex align-items-center justify-content-center w-100">

                <!-- Botón Dark Mode -->

                <div class="position-fixed top-0 end-0 m-3" style="z-index: 1050;">

                    <button id="theme-toggle" class="btn btn-light shadow-sm"

                        style="border-radius: 50%; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">

                        <i class="ti ti-moon" id="theme-icon" style="font-size: 24px;"></i>

                    </button>

                </div>



                <div class="row justify-content-center w-100 animate__animated animate__fadeInDown">

                    <div class="col-md-6 col-lg-5 col-xxl-3">

                        <div class="card mb-0 shadow-lg border-0"

                            style="border-radius: 16px; overflow: hidden; backdrop-filter: blur(10px); background: rgba(255, 255, 255, 0.95);">

                            <!-- Header -->

                            <div class="card-header text-center py-3 verify-header"

                                style="background: linear-gradient(135deg, #6c5ce7 0%, #6c5ce7 100%); border: none;">

                                <div class="bg-pattern"></div>
                                <div class="bg-pattern-2"></div>

                                <h6 class="text-white fw-bold mb-0" style="letter-spacing: 1px; font-size: 0.9rem;">

                                    <i class="ti ti-shield-check"></i> VERIFICACIÓN DE SEGURIDAD

                                </h6>

                            </div>



                            <div class="card-body px-4 py-3">

                                <!-- Icono Authenticator -->

                                <div class="authenticator-icon">

                                    <i class="ti ti-device-mobile"></i>

                                </div>



                                <div class="text-center mt-3 mb-2">

                                    <h5 class="fw-bold mb-1">Código de Verificación</h5>

                                    <p class="text-muted mb-0" style="font-size: 0.9rem;">

                                        Ingresa el código de 6 dígitos de tu autenticador

                                    </p>

                                </div>



                                <!-- Alertas -->

                                <?php if ($error): ?>

                                    <div class="alert alert-danger text-center border-0 mt-3" role="alert"

                                        style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%); color: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);">

                                        <i class="ti ti-alert-circle"></i> <?= htmlspecialchars($error) ?>

                                        <?php if ($attemptInfo): ?>

                                            <br><small><?= $attemptInfo ?></small>

                                        <?php endif; ?>

                                    </div>

                                <?php endif; ?>



                                <?php if ($success): ?>

                                    <div class="alert alert-success text-center border-0 mt-3" role="alert"

                                        style="background: linear-gradient(135deg, #51cf66 0%, #37b24d 100%); color: white; border-radius: 12px;">

                                        <i class="ti ti-check"></i> <?= htmlspecialchars($success) ?>

                                    </div>

                                <?php endif; ?>



                                <!-- Formulario -->

                                <form method="POST" id="verifyForm" style="margin-top: 20px;">

                                    <!-- Input OTP -->

                                    <div class="code-input-wrapper">

                                        <label for="code" class="form-label fw-semibold" style="font-size: 0.9rem;">

                                            <i class="ti ti-lock-open"></i> Código TOTP

                                        </label>

                                        <input type="text" class="otp-input" id="code" name="code" placeholder="000000 o código respaldo"

                                            inputmode="text" pattern="[0-9]{6}|[A-Z0-9a-z]{8}" maxlength="8" required autofocus

                                            autocomplete="off" />

                                        <div class="backup-code-hint">

                                            💡 ¿Perdiste tu teléfono? Puedes usar un código de respaldo (8 caracteres)

                                        </div>

                                    </div>



                                    <!-- Confiar en dispositivo -->

                                    <div class="trust-device-section">

                                        <div class="form-check">

                                            <input class="form-check-input" type="checkbox" id="trustDevice"

                                                name="trust_device" />

                                            <label class="form-check-label fw-semibold" for="trustDevice"

                                                style="font-size: 0.9rem;">

                                                <i class="ti ti-device-laptop"></i> Recordar este dispositivo por 24h

                                            </label>

                                        </div>

                                        <div class="device-info">

                                            ✓ No necesitarás ingresar código en este dispositivo durante las próximas 24

                                            horas

                                        </div>

                                    </div>



                                    <!-- Botones -->

                                    <div class="mt-4">

                                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold"

                                            style="background: linear-gradient(135deg, #6c5ce7 0%, #6c5ce7 100%); border: none; border-radius: 10px; box-shadow: 0 4px 15px rgba(108, 92, 231, 0.4); font-size: 0.95rem;"

                                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(108, 92, 231, 0.5)'"

                                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(108, 92, 231, 0.4)'">

                                            <i class="ti ti-check"></i> Verificar Código

                                        </button>



                                        <a href="login.php" class="btn btn-outline-secondary w-100 py-2 mt-2 fw-semibold"

                                            style="border-radius: 10px; font-size: 0.95rem;">

                                            <i class="ti ti-arrow-left"></i> Volver al Login

                                        </a>

                                    </div>

                                </form>



                                <!-- Timer de sesión -->

                                <div class="timer">

                                    <i class="ti ti-clock"></i> La sesión expira en <span id="timer">5:00</span>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>

    <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

    <script>

        // Timer de sesión (5 minutos)

        let timeLeft = 300;

        const timerElement = document.getElementById('timer');



        setInterval(() => {

            timeLeft--;

            const minutes = Math.floor(timeLeft / 60);

            const seconds = timeLeft % 60;

            timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;



            if (timeLeft <= 0) {

                window.location.href = 'login.php?error=Sesión expirada';

            }

        }, 1000);



        // Auto-enviar cuando se ingresa código de 6 dígitos

        document.getElementById('code').addEventListener('input', function(e) {

            if (this.value.length === 6 && /^\d{6}$/.test(this.value)) {

                // document.getElementById('verifyForm').submit();

            }

        });



        // Dark Mode Toggle

        const themeToggle = document.getElementById('theme-toggle');

        const themeIcon = document.getElementById('theme-icon');

        const html = document.documentElement;

        const savedDarkMode = localStorage.getItem('darkMode') === 'true';
        if (savedDarkMode) {
            html.setAttribute('data-bs-theme', 'dark');
            themeIcon.className = 'ti ti-sun';
        }

        themeToggle.addEventListener('click', function() {
            const currentTheme = html.getAttribute('data-bs-theme');
            if (currentTheme === 'dark') {
                html.setAttribute('data-bs-theme', 'light');
                themeIcon.className = 'ti ti-moon';
                localStorage.setItem('darkMode', 'false');
            } else {
                html.setAttribute('data-bs-theme', 'dark');
                themeIcon.className = 'ti ti-sun';
                localStorage.setItem('darkMode', 'true');
            }
        });

    </script>

    <script>

        VANTA.NET({

            el: "#vanta-bg",

            mouseControls: true,

            touchControls: true,

            gyroControls: false,

            minHeight: 200.00,

            minWidth: 200.00,

            scale: 1.00,

            scaleMobile: 1.00,

            color: 0x6c5ce7,

            backgroundColor: 0x0f0f1b,

            points: 10.00,

            maxDistance: 22.00,

            spacing: 15.00

        });

    </script>

</body>



</html>

