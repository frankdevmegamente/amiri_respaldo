/* nexo-shell.js — interactividad del shell nexo (piloto) */
(function () {
  'use strict';

  function $(sel, root) { return (root || document).querySelector(sel); }
  function $all(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }

  function getTheme() {
    var attr = document.documentElement.getAttribute('data-bs-theme');
    if (attr) return attr;
    try { return localStorage.getItem('darkMode') === 'true' ? 'dark' : 'light'; } catch (e) { return 'light'; }
  }

  function setTheme(theme) {
    document.documentElement.setAttribute('data-bs-theme', theme);
    try { localStorage.setItem('darkMode', theme === 'dark' ? 'true' : 'false'); } catch (e) {}
    updateThemeButton();
  }

  function updateThemeButton() {
    var btn = $('#themeToggle');
    if (!btn) return;
    var dark = getTheme() === 'dark';
    btn.innerHTML = dark
      ? '<i class="ti ti-sun"></i><span>Claro</span>'
      : '<i class="ti ti-moon"></i><span>Oscuro</span>';
    btn.setAttribute('aria-label', dark ? 'Cambiar a tema claro' : 'Cambiar a tema oscuro');
    btn.title = dark ? 'Cambiar a tema claro' : 'Cambiar a tema oscuro';
  }

  function toggleTheme() {
    setTheme(getTheme() === 'dark' ? 'light' : 'dark');
  }

  function setCollapsed(collapsed) {
    var app = $('#nexoApp');
    if (!app) return;
    if (window.innerWidth < 992) {
      app.classList.remove('sidebar-collapsed');
      return;
    }
    app.classList.toggle('sidebar-collapsed', !!collapsed);
    try { localStorage.setItem('nexoSidebarCollapsed', collapsed ? '1' : '0'); } catch (e) {}
    var btn = $('#sidebarToggle');
    if (btn) {
      btn.innerHTML = collapsed ? '<i class="ti ti-chevron-right"></i>' : '<i class="ti ti-chevron-left"></i>';
      btn.title = collapsed ? 'Expandir menú' : 'Colapsar menú';
      btn.setAttribute('aria-label', btn.title);
    }
  }

  function toggleSidebar() {
    var app = $('#nexoApp');
    if (!app) return;
    if (window.innerWidth < 992) {
      app.classList.toggle('sidebar-open');
    } else {
      setCollapsed(!app.classList.contains('sidebar-collapsed'));
    }
  }

  function closeMobileSidebar() {
    var app = $('#nexoApp');
    if (app) app.classList.remove('sidebar-open');
  }

  function initNavGroups() {
    $all('.nx-nav-group-toggle').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        var group = btn.closest('.nx-nav-group');
        if (group) group.classList.toggle('open');
      });
    });
  }

  function initActiveNav() {
    var current = (window.location.pathname.split('/').pop() || 'index.php').toLowerCase();
    $all('.nx-nav-item[href]').forEach(function (el) {
      var href = (el.getAttribute('href') || '').toLowerCase();
      if (!href || href === '#' || href.indexOf('javascript') === 0) return;
      var file = href.split('/').pop().split('?')[0];
      if (file === current) {
        el.classList.add('active');
      }
    });
  }

  function init() {
    // Theme
    var saved = null;
    try { saved = localStorage.getItem('darkMode'); } catch (e) {}
    var isDark = saved === 'true';
    document.documentElement.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
    updateThemeButton();
    var tbtn = $('#themeToggle');
    if (tbtn) tbtn.addEventListener('click', toggleTheme);

    // Sidebar collapsed state
    var collapsed = false;
    try { collapsed = localStorage.getItem('nexoSidebarCollapsed') === '1'; } catch (e) {}
    setCollapsed(collapsed && window.innerWidth >= 992);

    var sbtn = $('#sidebarToggle');
    if (sbtn) sbtn.addEventListener('click', toggleSidebar);

    var mbtn = $('#mobileMenuBtn');
    if (mbtn) mbtn.addEventListener('click', function () { toggleSidebar(); });

    var ov = $('#nxOverlay');
    if (ov) ov.addEventListener('click', closeMobileSidebar);

    window.addEventListener('resize', function () {
      if (window.innerWidth >= 992) {
        closeMobileSidebar();
        var c = false;
        try { c = localStorage.getItem('nexoSidebarCollapsed') === '1'; } catch (e) {}
        setCollapsed(c);
      } else {
        var app = $('#nexoApp');
        if (app) app.classList.remove('sidebar-collapsed');
        var btn = $('#sidebarToggle');
        if (btn) btn.innerHTML = '<i class="ti ti-chevron-left"></i>';
      }
    });

    initNavGroups();
    initActiveNav();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
