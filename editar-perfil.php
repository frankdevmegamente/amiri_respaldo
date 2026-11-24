<?php

require_once __DIR__ . '/src/config/session.php';

require_once __DIR__ . '/src/config/db.php';



if (!isset($_SESSION['usuario_id'])) {

  header('Location: login.php');

  exit;

}



$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';



// Verificar estado de 2FA

$pdo = getPDO();

$stmt = $pdo->prepare("SELECT two_fa_enabled FROM usuarios WHERE id = ?");

$stmt->execute([$_SESSION['usuario_id']]);

$user2FA = $stmt->fetch();

$twoFAEnabled = $user2FA['two_fa_enabled'] ?? false;

?>



<!DOCTYPE html>

<html lang="es">



<head>

  <meta charset="UTF-8" />

  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>Editar Perfil - Amiri</title>

  <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico" />

  <link rel="stylesheet" href="src/assets/css/styles.min.css" />

  <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />

  <link rel="stylesheet" href="src/assets/css/layout-custom.css" />

    <script>document.documentElement.setAttribute('data-bs-theme',localStorage.getItem('darkMode')==='true'?'dark':'light')</script>
</head>

<style>
        .body-wrapper {
            padding-top: 100px !important;
        }
        
        .body-wrapper-inner, 
        .body-wrapper .container-fluid {
            padding-top: 15px !important;
            margin-top: 0 !important;
        }
     [data-bs-theme="dark"] .logo-modo-claro,
        .dark-mode .logo-modo-claro {
            display: none !important;
        }

        [data-bs-theme="dark"] .logo-modo-oscuro,
        .dark-mode .logo-modo-oscuro {
            display: block !important;
        }

/* =========================================
   🌙 MODO OSCURO PERFIL
   ========================================= */

[data-bs-theme="dark"] h3,
[data-bs-theme="dark"] .page-breadcrumb h3 {
    color: #f5f2f4 !important;
}

[data-bs-theme="dark"] .card { background: var(--nx-surface) !important;
    border-color: rgba(255,255,255,0.12);
}

[data-bs-theme="dark"] .form-label {
    color: #f5f2f4 !important;
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

[data-bs-theme="dark"] .text-muted {
    color: #9a9096 !important;
}

[data-bs-theme="dark"] .bg-light {
    background-color: var(--nx-surface-2) !important;
}

/* ─── Page Header ─────────────────────────────── */
.perfil-header { position: relative; overflow: hidden; border-radius: 16px !important; }
.perfil-header .bg-pattern { position: absolute; top: -50%; right: -10%; width: 400px; height: 400px; border-radius: 50%; background: rgba(93, 135, 255, 0.08); pointer-events: none; }
.perfil-header .bg-pattern-2 { position: absolute; bottom: -30%; left: 20%; width: 250px; height: 250px; border-radius: 50%; background: rgba(19, 222, 185, 0.06); pointer-events: none; }
.perfil-header h2 { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem; }
.perfil-header .sub-badge { font-size: 1rem; color: #9a9096; }

[data-bs-theme="dark"] .perfil-header .sub-badge { color: #9a9096 !important; }
</style>

<body>

  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"

    data-sidebar-position="fixed" data-header-position="fixed">



    <?php include 'src/assets/partials/header.php'; ?>

    <?php include 'src/assets/partials/sidebar.php'; ?>



    <div class="body-wrapper">

      <div class="body-wrapper-inner">

        <div class="container-fluid px-4" style="max-width: 100%;">

          <!-- ════════════════════════════════════
               PAGE HEADER
               ════════════════════════════════════ -->
          <div class="card perfil-header shadow-sm border-0 rounded-4 mb-4">
              <div class="bg-pattern"></div>
              <div class="bg-pattern-2"></div>
              <div class="card-body p-4">
                  <div class="row align-items-center g-3">
                      <div class="col-md">
                          <div class="d-flex align-items-center gap-3 mb-1">
                              <div class="d-flex align-items-center justify-content-center rounded-3"
                                   style="width:48px;height:48px;background:rgba(93,135,255,0.12);">
                                  <i class="ti ti-user text-primary" style="font-size:1.6rem;"></i>
                              </div>
                              <div>
                                  <h2 class="mb-0">Editar Perfil</h2>
                                  <span class="sub-badge"><i class="ti ti-circle"></i> Configura tu información personal y seguridad</span>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
          </div>



          <div class="row">

            <div class="col-lg-6">

              <div class="card">

                <div class="card-body">

                  <form id="formPerfil">

                    <div class="mb-3">

                      <label class="form-label">Nombre</label>

                      <input type="text" class="form-control" id="perfilNombre" name="nombre" required />

                    </div>

                    <div class="mb-3">

                      <label class="form-label">Email</label>

                      <input type="email" class="form-control no-capitalize" id="perfilEmail" name="email" required />

                    </div>

                    <div class="mb-3">

                      <label class="form-label">Nueva contraseña (opcional)</label>

                      <input type="password" class="form-control" id="perfilPassword" name="password" />

                    </div>

                    <div class="mb-3">

                      <label class="form-label">Confirmar contraseña</label>

                      <input type="password" class="form-control" id="perfilPasswordConfirm" />

                    </div>

                    <div class="d-flex gap-2">

                      <button type="submit" class="btn btn-primary">

                        <i class="ti ti-device-floppy"></i> Guardar cambios

                      </button>

                      <a href="<?= (($_SESSION['usuario_rol'] ?? '') === 'promotor') ? 'mi-cuenta.php' : 'index.php' ?>" class="btn btn-secondary">

                        <i class="ti ti-arrow-left"></i> Volver

                      </a>

                    </div>

                  </form>

                </div>

              </div>

            </div>



            <!-- Seguridad - 2FA -->

            <div class="col-lg-6">

              <div class="card border-0 shadow-sm">

                <div class="card-body">

                  <h5 class="card-title mb-3">

                    <i class="ti ti-shield-lock text-primary"></i> Seguridad

                  </h5>

                  

                  <div class="security-item d-flex justify-content-between align-items-center">

                    <div>

                      <h6 class="mb-1">Autenticación de Dos Factores (2FA)</h6>

                      <p class="text-muted small mb-0">

                        <?php if ($twoFAEnabled): ?>

                          <span class="badge bg-success">✓ Activado</span>

                          Protege tu cuenta con Google Authenticator

                        <?php else: ?>

                          <span class="badge bg-warning">○ Desactivado</span>

                          Aumenta la seguridad de tu cuenta

                        <?php endif; ?>

                      </p>

                    </div>

                    <div>

                      <a href="setup-2fa.php" class="btn btn-sm btn-outline-primary">

                        <i class="ti ti-settings"></i> 

                        <?php echo $twoFAEnabled ? 'Administrar' : 'Activar'; ?>

                      </a>

                    </div>

                  </div>



                  <hr class="my-3">



                  <div class="alert alert-info small mb-0">

                    <i class="ti ti-info-circle"></i>

                    <strong>¿Qué es 2FA?</strong> Es un código de 6 dígitos que cambia cada 30 segundos. 

                    Junto con tu contraseña, hace tu cuenta mucho más segura.

                  </div>

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

      $(document).ready(function() {

        $.get('api/perfil.php', function(data) {

          $('#perfilNombre').val(data.nombre || '');

          $('#perfilEmail').val(data.email || '');

        });



        $('#formPerfil').on('submit', function(e) {

          e.preventDefault();



          const password = $('#perfilPassword').val();

          const confirm = $('#perfilPasswordConfirm').val();



          if (password && password !== confirm) {

            showToast('Las contraseñas no coinciden', 'error');

            return;

          }



          const formData = new FormData(this);



          $.ajax({

            url: 'api/perfil.php',

            method: 'POST',

            data: formData,

            processData: false,

            contentType: false,

            success: function(res) {

              showToast('Perfil actualizado', 'success');

              if (res?.nombre) {

                $('.user-name').html('<i class="ti ti-user me-1"></i> ' + res.nombre);

              }

              $('#perfilPassword').val('');

              $('#perfilPasswordConfirm').val('');

            },

            error: function(xhr) {

              let message = 'Error al actualizar el perfil';

              try {

                const res = JSON.parse(xhr.responseText);

                message = res.error || message;

              } catch (e) {}

              showToast(message, 'error');

            }

          });

        });

      });

    </script>

  </div>

</body>



</html>

