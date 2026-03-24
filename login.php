<?php
require_once __DIR__ . '/src/config/session.php';

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['usuario_id'])) {
  header('Location: index.php');
  exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if (!$email || !$password) {
    $error = 'Por favor ingresa email y contraseña.';
  } else {

    $requireEnv = __DIR__ . '/src/config/env.php';
    if (file_exists($requireEnv)) {
      require_once $requireEnv;
    }
    require_once __DIR__ . '/src/config/db.php';
    require_once __DIR__ . '/src/config/auditoria.php';
    require_once __DIR__ . '/src/config/rate-limiter.php';
    require_once __DIR__ . '/src/config/logger.php';
    require_once __DIR__ . '/src/config/trusted-device-middleware.php';
    require_once __DIR__ . '/src/services/TwoFactorService.php';

    // Verificar rate limiting (combinación de IP + email)
    $rateLimitKey = 'login:' . $_SERVER['REMOTE_ADDR'] . ':' . $email;

    if (RateLimiter::tooManyAttempts($rateLimitKey)) {
      $secondsLeft = RateLimiter::availableIn($rateLimitKey);
      $minutesLeft = ceil($secondsLeft / 60);
      $error = "Demasiados intentos fallidos. Intenta nuevamente en $minutesLeft minutos.";
    } else {
      try {
        $pdo = getPDO();

        // Buscar usuario por email
        $stmt = $pdo->prepare("SELECT id, nombre, email, password, rol, two_fa_enabled FROM usuarios WHERE email = ? AND activo = 1");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password'])) {
          // Login exitoso - Limpiar rate limit
          RateLimiter::clear($rateLimitKey);

          // Regenerar ID de sesión por seguridad
          regenerateSessionId();

          // Log de login exitoso
          Logger::audit('login_success', $usuario['id'], [
            'email' => $email,
            'rol' => $usuario['rol']
          ]);

          // ✅ VALIDAR DISPOSITIVO CONFIABLE (24 HORAS)
          $deviceToken = $_COOKIE['trusted_device'] ?? null;
          $isTrustedDevice = false;
          
          if ($deviceToken && $usuario['two_fa_enabled']) {
            // Verificar si el dispositivo es confiable
            $stmt = $pdo->prepare("
              SELECT id FROM trusted_devices 
              WHERE usuario_id = ? AND device_token = ? AND expires_at > NOW()
            ");
            $stmt->execute([$usuario['id'], $deviceToken]);
            
            if ($stmt->fetch()) {
              // Dispositivo confiable - actualizar último uso
              $stmt = $pdo->prepare("
                UPDATE trusted_devices 
                SET last_used_at = NOW() 
                WHERE usuario_id = ? AND device_token = ?
              ");
              $stmt->execute([$usuario['id'], $deviceToken]);
              
              $isTrustedDevice = true;
              
              // Log de acceso con dispositivo confiable
              Logger::audit('login_trusted_device', $usuario['id'], [
                'email' => $email,
                'device_token' => substr($deviceToken, 0, 10) . '...',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
              ]);
            }
          }

          // Si 2FA está habilitado Y no es dispositivo confiable
          if ($usuario['two_fa_enabled'] && !$isTrustedDevice) {
            // Guardar datos temporales en sesión para 2FA
            $_SESSION['temp_usuario_id'] = $usuario['id'];
            $_SESSION['temp_usuario_nombre'] = $usuario['nombre'];
            $_SESSION['temp_usuario_email'] = $usuario['email'];
            $_SESSION['temp_usuario_rol'] = $usuario['rol'];
            $_SESSION['2fa_pending'] = true;
            $_SESSION['2fa_start_time'] = time();

            header('Location: verify-2fa.php');
            exit;
          }

          // Si no tiene 2FA O dispositivo es confiable, iniciar sesión normalmente
          $_SESSION['usuario_id'] = $usuario['id'];
          $_SESSION['usuario_nombre'] = $usuario['nombre'];
          $_SESSION['usuario_rol'] = $usuario['rol'];
          $_SESSION['usuario_email'] = $usuario['email'];
          $_SESSION['LAST_ACTIVITY'] = time();

          // Registrar auditoría de login
          registrarLogin($pdo, $usuario['id'], $usuario['nombre']);

          header('Location: index.php');
          exit;
        } else {
          // Login fallido - Incrementar contador
          RateLimiter::hit($rateLimitKey);

          // Log de intento fallido
          Logger::warning('login_failed', [
            'email' => $email,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'attempts' => RateLimiter::attempts($rateLimitKey)
          ]);

          $retriesLeft = RateLimiter::retriesLeft($rateLimitKey);
          if ($retriesLeft > 0) {
            $error = "Credenciales incorrectas. Te quedan $retriesLeft intentos.";
          } else {
            $error = 'Credenciales incorrectas. Has alcanzado el límite de intentos.';
          }
        }
      } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        $error = 'Error interno. Inténtalo más tarde.';
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - Amiri</title>
  <!-- Bootstrap + Estilos del dashboard -->
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
      border-color: #667eea !important;
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
              <div class="card-header text-center py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                <img src="src/assets/images/logos/logo.png" width="140" alt="Logo" class="mb-1" style="filter: brightness(0) invert(1);" />
                <h6 class="text-white fw-bold mb-0" style="letter-spacing: 1px; font-size: 0.9rem;">SISTEMA AMIRI</h6>
              </div>

              <div class="card-body px-4 py-3">
                <div class="text-center mb-3">
                  <div class="d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);">
                    <i class="ti ti-user" style="font-size: 24px; color: white;"></i>
                  </div>
                  <p class="mt-2 mb-0 text-muted fw-semibold" style="font-size: 0.9rem;">Ingresa tus credenciales</p>
                </div>

                <?php if ($error): ?>
                  <div class="alert alert-danger text-center border-0" role="alert" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%); color: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);">
                    <i class="ti ti-alert-circle"></i> <?= htmlspecialchars($error) ?>
                  </div>
                <?php endif; ?>

                <form method="POST">
                  <div class="mb-3">
                    <label for="email" class="form-label fw-semibold" style="font-size: 0.9rem;"><i class="ti ti-mail"></i> Email</label>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text border-end-0" style="background: #f8f9fa; border-color: #dee2e6;">
                        <i class="ti ti-at"></i>
                      </span>
                      <input type="email" class="form-control border-start-0 py-2" id="email" name="email" placeholder="tu@email.com" required autofocus style="font-size: 0.9rem;" />
                    </div>
                  </div>

                  <div class="mb-3">
                    <label for="password" class="form-label fw-semibold" style="font-size: 0.9rem;"><i class="ti ti-lock"></i> Contraseña</label>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text border-end-0" style="background: #f8f9fa; border-color: #dee2e6;">
                        <i class="ti ti-key"></i>
                      </span>
                      <input type="password" class="form-control border-start-0 border-end-0 py-2" id="password" name="password" placeholder="••••••••" required style="font-size: 0.9rem;" />
                      <button type="button" class="btn input-group-text border-start-0" id="togglePassword" style="background: #f8f9fa; border-color: #dee2e6; cursor: pointer;">
                        <i class="ti ti-eye"></i>
                      </button>
                    </div>
                  </div>

                  <button type="submit" class="btn btn-primary w-100 py-2 mb-2 fw-semibold" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 10px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4); transition: all 0.3s ease; font-size: 0.95rem;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(102, 126, 234, 0.5)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(102, 126, 234, 0.4)'">
                    <i class="ti ti-login"></i> Iniciar Sesión
                  </button>

                  <div class="text-center mt-3">
                    <a href="forgot-password.php" class="text-decoration-none fw-semibold" style="color: #667eea; transition: all 0.3s ease; font-size: 0.85rem;" onmouseover="this.style.color='#764ba2'" onmouseout="this.style.color='#667eea'">
                      <i class="ti ti-help-circle"></i> ¿Olvidaste tu contraseña?
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
  <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>
  <script>
    document.getElementById('togglePassword').addEventListener('click', function() {
      const password = document.getElementById('password');
      const icon = this.querySelector('i');
      if (password.type === 'password') {
        password.type = 'text';
        icon.className = 'ti ti-eye-off';
      } else {
        password.type = 'password';
        icon.className = 'ti ti-eye';
      }
    });

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
  <!-- Inicializar Vanta.js -->
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