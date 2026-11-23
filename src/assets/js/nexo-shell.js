/* nexo-shell.js — interactividad del shell Helios/Nexo (shared) */
(function () {
  'use strict';

  function $(sel, root) { return (root || document).querySelector(sel); }
  function $all(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }
  function getApp() { return $('#nexoApp') || $('#main-wrapper') || $('.nexo-app'); }

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

  function toggleTheme() { setTheme(getTheme() === 'dark' ? 'light' : 'dark'); }

  function setCollapsed(collapsed) {
    var app = getApp();
    if (!app) return;
    if (window.innerWidth < 992) {
      app.classList.remove('sidebar-collapsed');
      return;
    }
    app.classList.toggle('sidebar-collapsed', !!collapsed);
    app.classList.toggle('sidebar-mini', !!collapsed); // legacy compat
    try {
      localStorage.setItem('nexoSidebarCollapsed', collapsed ? '1' : '0');
      localStorage.setItem('amiri_sidebar_mini', collapsed ? '1' : '0');
    } catch (e) {}
    var btn = $('#sidebarToggle') || $('#sidebarCollapseBtn');
    if (btn) {
      btn.innerHTML = collapsed ? '<i class="ti ti-chevron-right"></i>' : '<i class="ti ti-chevron-left"></i>';
      btn.title = collapsed ? 'Expandir menú' : 'Colapsar menú';
      btn.setAttribute('aria-label', btn.title);
    }
  }

  function toggleSidebar() {
    var app = getApp();
    if (!app) return;
    if (window.innerWidth < 992) {
      app.classList.toggle('sidebar-open');
      app.classList.toggle('show-sidebar'); // legacy compat
    } else {
      setCollapsed(!app.classList.contains('sidebar-collapsed'));
    }
  }

  function closeMobileSidebar() {
    var app = getApp();
    if (app) { app.classList.remove('sidebar-open'); app.classList.remove('show-sidebar'); }
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

  function bindOnce(el, key, fn) {
    if (!el || el.dataset[key] === '1') return;
    el.dataset[key] = '1';
    el.addEventListener('click', fn);
  }

  function init() {
    var app = getApp();
    if (app) app.classList.add('nexo-app');

    var saved = null;
    try { saved = localStorage.getItem('darkMode'); } catch (e) {}
    var dark = saved === null ? true : saved === 'true';
    document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light');
    try { localStorage.setItem('darkMode', dark ? 'true' : 'false'); } catch(e){}
    updateThemeButton();

    bindOnce($('#themeToggle'), 'nxThemeBound', function(e){ e.preventDefault(); toggleTheme(); });

    var collapsed = false;
    try {
      collapsed = localStorage.getItem('nexoSidebarCollapsed') === '1' || localStorage.getItem('amiri_sidebar_mini') === '1';
    } catch (e) {}
    setCollapsed(collapsed && window.innerWidth >= 992);

    bindOnce($('#sidebarToggle') || $('#sidebarCollapseBtn'), 'nxSideBound', function(e){ e.preventDefault(); toggleSidebar(); });
    bindOnce($('#mobileMenuBtn') || $('#headerCollapse'), 'nxMobileBound', function(e){ e.preventDefault(); toggleSidebar(); });
    var ov = $('#nxOverlay') || $('.nx-overlay');
    bindOnce(ov, 'nxOvBound', function(){ closeMobileSidebar(); });

    $all('.sidebartoggler').forEach(function(btn){
      if (btn.id === 'mobileMenuBtn' || btn.id === 'headerCollapse' || btn.id === 'sidebarToggle' || btn.id === 'sidebarCollapseBtn') return;
      bindOnce(btn, 'nxTogBound', function(e){ if (window.innerWidth < 992) { e.preventDefault(); toggleSidebar(); } });
    });

    window.addEventListener('resize', function () {
      if (window.innerWidth >= 992) {
        closeMobileSidebar();
        var c = false;
        try { c = localStorage.getItem('nexoSidebarCollapsed') === '1' || localStorage.getItem('amiri_sidebar_mini') === '1'; } catch (e) {}
        setCollapsed(c);
      } else {
        var a = getApp();
        if (a) a.classList.remove('sidebar-collapsed');
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
