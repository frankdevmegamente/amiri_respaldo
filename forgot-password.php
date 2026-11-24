<?php

// error_reporting(E_ALL);

// ini_set('display_errors', 1);

session_start();



$error = '';

$success = '';



if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');



    if (!$email) {

        $error = 'Por favor ingresa tu correo electrónico.';

    } else {

        

        $requireEnv = __DIR__ . '/src/config/env.php';

        if (file_exists($requireEnv)) {

            require_once $requireEnv;

        }

        require_once __DIR__ . '/src/config/db.php';



        try {

            $pdo = getPDO();



            // Verificar si el email existe

            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");

            $stmt->execute([$email]);

            $usuario = $stmt->fetch();



            if ($usuario) {

                // Generar token único

                $token = bin2hex(random_bytes(32));

                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));



                // Guardar token en la base de datos

                $stmt = $pdo->prepare("UPDATE usuarios SET reset_token = ?, reset_expires_at = ? WHERE email = ?");

                $stmt->execute([$token, $expires, $email]);



                // === ENVIAR CORREO CON PHPMAILER ===

                require_once __DIR__ . '/vendor/autoload.php';



                if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {

                    die('❌ Error: PHPMailer no está instalado o no se cargó correctamente.');

                }



                // Verificar que el archivo de configuración exista

                $configPath = __DIR__ . '/src/assets/config/mail.php';

                if (!file_exists($configPath)) {

                    die('❌ Error: Archivo de configuración de correo no encontrado: ' . $configPath);

                }



                $mailConfig = require $configPath;



                $mail = new PHPMailer\PHPMailer\PHPMailer();

                $mail->isSMTP();

                $mail->Host = $mailConfig['host'];

                $mail->Port = $mailConfig['port'];

                if (!empty($mailConfig['smtp_options'])) {
                    $mail->SMTPOptions = $mailConfig['smtp_options'];
                }

                // Set encryption based on config

                if (!empty($mailConfig['encryption'])) {

                    $enc = strtolower($mailConfig['encryption']);

                    if ($enc === 'ssl' || $enc === 'smtps') {

                        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;

                    } elseif ($enc === 'tls' || $enc === 'starttls') {

                        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;

                    }

                }

                $mail->SMTPAuth = (bool) ($mailConfig['smtp_auth'] ?? false);

                $mail->Username = $mailConfig['username'] ?? '';

                $mail->Password = $mailConfig['password'] ?? '';



                $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);

                $mail->addAddress($email);

                $mail->isHTML(true);

                $mail->CharSet = 'UTF-8';

                $mail->Subject = 'Restablece tu contraseña - Amiri';

                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
                $baseUrl = getenv('APP_URL') ?: "$protocol://$host$basePath";
                $reset_link = rtrim($baseUrl, '/') . "/reset-password.php?token=" . urlencode($token);

                $mail->Body = "

                    <h2>¿Olvidaste tu contraseña?</h2>

                    <p>Haz clic en el siguiente enlace para restablecerla:</p>

                    <p><a href='$reset_link' style='background-color:#c58fb8;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Restablecer Contraseña</a></p>

                    <p>El enlace expira en 1 hora.</p>

                    <hr>

                    <small>Si no solicitaste este cambio, ignora este correo.</small>

                ";



                if ($mail->send()) {

                    $success = "Se ha enviado un enlace a tu correo para restablecer tu contraseña.";

                } else {

                    $error = "No se pudo enviar el correo. Detalle: " . htmlspecialchars($mail->ErrorInfo);

                }

            } else {

                // No revelar si el email existe o no (por seguridad)

                $success = "Si tu correo está registrado, recibirás un enlace para restablecer tu contraseña.";

            }

        } catch (PDOException $e) {

            $error = "Error en la base de datos: " . htmlspecialchars($e->getMessage());

        } catch (Exception $e) {

            $error = "Error al enviar el correo: " . htmlspecialchars($e->getMessage());

        }

    }

}

?>



<!DOCTYPE html>

<html lang="es">



<head>

    <meta charset="UTF-8" />

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Recuperar Contraseña - Amiri</title>

    <link rel="stylesheet" href="src/assets/libs/bootstrap/dist/css/bootstrap.min.css" />

    <link rel="stylesheet" href="src/assets/css/styles.min.css" />

    <link rel="icon" type="image/png" href="src/assets/images/logos/favicon.ico" />

    <!-- Vanta.js -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/vanta@0.5.24/dist/vanta.net.min.js"></script>

      <link

    rel="stylesheet"

    href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    <style>

        /* Dark Mode Styles */

        [data-bs-theme="dark"] .form-label,

        [data-bs-theme="dark"] .text-muted {

          color: #e0e0e0 !important;

        }

        

        [data-bs-theme="dark"] .form-control {

          background-color: #2a2a2a !important;

          border-color: #444 !important;

          color: #fff !important;

        }

        

        [data-bs-theme="dark"] .form-control:focus {

          background-color: #333 !important;

          border-color: #c58fb8 !important;

          color: #fff !important;

        }

        

        [data-bs-theme="dark"] .form-control::placeholder {

          color: #999 !important;

        }

        

        [data-bs-theme="dark"] .input-group-text {

          background-color: #2a2a2a !important;

          border-color: #444 !important;

          color: #e0e0e0 !important;

        }

        

        [data-bs-theme="dark"] .alert-danger {

          background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%) !important;

          color: white !important;

          border: none !important;

        }

        

        [data-bs-theme="dark"] .alert-success {

          background: linear-gradient(135deg, #51cf66 0%, #37b24d 100%) !important;

          color: white !important;

          border: none !important;

        }

        

        [data-bs-theme="dark"] a {

          color: #8b9bff !important;

        }

        

        [data-bs-theme="dark"] a:hover {

          color: #a5b4ff !important;

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

                  <button id="theme-toggle" class="btn btn-light shadow-sm" style="border-radius: 50%; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">

                    <i class="ti ti-moon" id="theme-icon" style="font-size: 24px;"></i>

                  </button>

                </div>

                

                <div class="row justify-content-center w-100 animate__animated animate__fadeInDown">

                    <div class="col-md-6 col-lg-5 col-xxl-3">

                        <div class="card mb-0 shadow-lg border-0" style="border-radius: 16px; overflow: hidden; backdrop-filter: blur(10px); background: rgba(255, 255, 255, 0.95);">

                            <!-- Header con gradiente -->

                            <div class="card-header text-center py-3" style="background: linear-gradient(135deg, #c58fb8 0%, #c58fb8 100%); border: none;">

                                <img src="src/assets/images/logos/logo.png" width="140" alt="Logo" class="mb-1" style="filter: brightness(0) invert(1);" />

                                <h6 class="text-white fw-bold mb-0" style="letter-spacing: 1px; font-size: 0.9rem;">RECUPERAR CONTRASEÑA</h6>

                            </div>

                            

                            <div class="card-body px-4 py-3">

                                <div class="text-center mb-3">

                                  <div class="d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: linear-gradient(135deg, #c58fb8 0%, #c58fb8 100%); border-radius: 50%; box-shadow: 0 4px 15px rgba(197, 143, 184, 0.3);">

                                    <i class="ti ti-lock-open" style="font-size: 24px; color: white;"></i>

                                  </div>

                                  <p class="mt-2 mb-0 text-muted fw-semibold" style="font-size: 0.85rem;">Ingresa tu correo para recuperar tu contraseña</p>

                                </div>



                                <?php if ($error): ?>

                                    <div class="alert alert-danger text-center border-0" role="alert" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%); color: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);">

                                      <i class="ti ti-alert-circle"></i> <?= $error ?>

                                    </div>

                                <?php endif; ?>

                                <?php if ($success): ?>

                                    <div class="alert alert-success text-center border-0" role="alert" style="background: linear-gradient(135deg, #51cf66 0%, #37b24d 100%); color: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(81, 207, 102, 0.3);">

                                      <i class="ti ti-circle-check"></i> <?= htmlspecialchars($success) ?>

                                    </div>

                                <?php endif; ?>



                                <form method="POST">

                                    <div class="mb-3">

                                        <label for="email" class="form-label fw-semibold" style="font-size: 0.9rem;"><i class="ti ti-mail"></i> Correo electrónico</label>

                                        <div class="input-group input-group-sm">

                                          <span class="input-group-text border-end-0" style="background: #f8f9fa; border-color: #dee2e6;">

                                            <i class="ti ti-at"></i>

                                          </span>

                                          <input type="email" class="form-control border-start-0 py-2" id="email" name="email" placeholder="tu@email.com" required autofocus style="font-size: 0.9rem;" />

                                        </div>

                                    </div>

                                    

                                    <button type="submit" class="btn btn-primary w-100 py-2 mb-2 fw-semibold" style="background: linear-gradient(135deg, #c58fb8 0%, #c58fb8 100%); border: none; border-radius: 10px; box-shadow: 0 4px 15px rgba(197, 143, 184, 0.4); transition: all 0.3s ease; font-size: 0.95rem;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(197, 143, 184, 0.5)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(197, 143, 184, 0.4)'">

                                      <i class="ti ti-send"></i> Enviar Enlace

                                    </button>

                                    

                                    <div class="text-center mt-3">

                                        <a href="login.php" class="text-decoration-none fw-semibold" style="color: #c58fb8; transition: all 0.3s ease; font-size: 0.85rem;" onmouseover="this.style.color='#c58fb8'" onmouseout="this.style.color='#c58fb8'">

                                          <i class="ti ti-arrow-left"></i> Volver al login

                                        </a>

                                    </div>

                                </form>

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

        // Dark Mode Toggle

        const themeToggle = document.getElementById('theme-toggle');

        const themeIcon = document.getElementById('theme-icon');

        const html = document.documentElement;

        

        // Cargar tema guardado (sincronizado con sistema interno)

        const savedDarkMode = localStorage.getItem('darkMode') === 'true';

        if (savedDarkMode) {

          html.setAttribute('data-bs-theme', 'dark');

          themeIcon.className = 'ti ti-sun';

          const card = document.querySelector('.card');

          card.style.background = 'rgba(30, 30, 30, 0.95)';

          card.style.color = '#fff';

          // Aplicar estilos a los input-group-text

          document.querySelectorAll('.input-group-text').forEach(el => {

            el.style.backgroundColor = '#2a2a2a';

            el.style.borderColor = '#444';

            el.style.color = '#e0e0e0';

          });

        }

        

        themeToggle.addEventListener('click', function() {

          const currentTheme = html.getAttribute('data-bs-theme');

          const card = document.querySelector('.card');

          

          if (currentTheme === 'dark') {

            html.setAttribute('data-bs-theme', 'light');

            themeIcon.className = 'ti ti-moon';

            localStorage.setItem('darkMode', 'false');

            card.style.background = 'rgba(255, 255, 255, 0.95)';

            card.style.color = '';

            // Restaurar estilos light

            document.querySelectorAll('.input-group-text').forEach(el => {

              el.style.backgroundColor = '';

              el.style.borderColor = '';

              el.style.color = '';

            });

          } else {

            html.setAttribute('data-bs-theme', 'dark');

            themeIcon.className = 'ti ti-sun';

            localStorage.setItem('darkMode', 'true');

            card.style.background = 'rgba(30, 30, 30, 0.95)';

            card.style.color = '#fff';

            // Aplicar estilos dark

            document.querySelectorAll('.input-group-text').forEach(el => {

              el.style.backgroundColor = '#2a2a2a';

              el.style.borderColor = '#444';

              el.style.color = '#e0e0e0';

            });

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
