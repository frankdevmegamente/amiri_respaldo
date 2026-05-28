(function () {
  'use strict';

  var IDLE_TIMEOUT = 10800000; // 3 horas en ms

  var modalInstance = null;
  var idleTimer = null;
  var isShowing = false;

  function resetIdleTimer() {
    if (idleTimer) clearTimeout(idleTimer);
    if (isShowing) return;
    idleTimer = setTimeout(showSessionModal, IDLE_TIMEOUT);
  }

  function showSessionModal() {
    if (isShowing) return;
    var modalEl = document.getElementById('modalSessionTimeout');
    if (!modalEl) return;

    if (window.bootstrap && window.bootstrap.Modal) {
      isShowing = true;
      modalInstance = new window.bootstrap.Modal(modalEl, {
        backdrop: 'static',
        keyboard: false,
      });
      modalInstance.show();
    }
  }

  function hideSessionModal() {
    if (modalInstance) {
      modalInstance.hide();
      modalInstance = null;
    }
    isShowing = false;
  }

  function extendSession() {
    var btn = document.getElementById('btnExtendSession');
    if (btn) {
      btn.disabled = true;
      btn.innerHTML =
        '<span class="spinner-border spinner-border-sm me-2"></span>Extendiendo...';
    }

    fetch('api/keep-alive.php')
      .then(function (res) {
        if (res.status === 401) {
          window.location.href = 'login.php?error=Sesión expirada';
          return null;
        }
        return res.json();
      })
      .then(function (data) {
        if (data && data.success) {
          hideSessionModal();
          resetIdleTimer();
        } else if (data) {
          window.location.href = 'login.php?error=Sesión expirada';
        }
      })
      .catch(function () {
        window.location.href = 'login.php?error=Sesión expirada';
      });
  }

  function logoutSession() {
    window.location.href = 'logout.php';
  }

  function checkSessionAlive() {
    fetch('api/keep-alive.php')
      .then(function (res) {
        if (res.status === 401) {
          window.location.href = 'login.php?error=Sesi\u00f3n expirada';
        }
      })
      .catch(function () {});
  }

  function init() {
    var modalEl = document.getElementById('modalSessionTimeout');
    if (!modalEl) return;

    var btnExtend = document.getElementById('btnExtendSession');
    var btnLogout = document.getElementById('btnLogoutSession');
    if (!btnExtend || !btnLogout) return;

    btnExtend.addEventListener('click', extendSession);
    btnLogout.addEventListener('click', logoutSession);

    var events = ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'];
    events.forEach(function (ev) {
      document.addEventListener(ev, resetIdleTimer, { passive: true });
    });

    // Detectar cuando el usuario regresa (abrir laptop, cambiar pestaña)
    document.addEventListener('visibilitychange', function () {
      if (document.visibilityState === 'visible') {
        checkSessionAlive();
      }
    });

    resetIdleTimer();
  }

  // Esperar a que Bootstrap esté cargado antes de iniciar
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();