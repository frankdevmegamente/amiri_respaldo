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

  function normalizeSearch(value) {
    return (value || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  }

  var SEARCH_ALIASES = {
    'index.php': 'dashboard inicio panel principal',
    'flujo.php': 'flujo efectivo operaciones caja',
    'cuentas.php': 'cuentas banco bancaria caja saldos',
    'movimientos.php': 'movimientos transacciones operaciones',
    'capital-amiri.php': 'capital amiri inversion reserva',
    'gastos.php': 'gastos egresos costos',
    'clientes.php': 'clientes contactos',
    'promotores.php': 'promotores comisiones afiliados',
    'proveedores.php': 'proveedores contactos',
    'prestamistas.php': 'prestamistas acreedores',
    'prestamos-otorgados.php': 'prestamos otorgados creditos',
    'reportes.php': 'reportes totales resumen general estadisticas',
    'reportes-utilidades.php': 'utilidades ganancias utilidad reportes',
    'reportes-servicios.php': 'servicios comisiones reportes',
    'generador-recibos.php': 'cotizador divisas calculo ventas recibo cotizacion',
    'generador-depositos.php': 'deposito efectivo depositos generador',
    'generador-transferencias.php': 'deposito transferencia transferencias generador',
    'fifo.php': 'fifo inventario valorizacion',
    'usuarios.php': 'usuarios accesos roles',
    'auditoria.php': 'auditoria logs historial',
    'monitor.php': 'monitor sistema estado',
    'mi-cuenta.php': 'mi cuenta perfil',
    'editar-perfil.php': 'editar perfil configuracion'
  };

  function nearestSection(node, nav) {
    while (node && node !== nav) {
      var sib = node.previousElementSibling;
      while (sib) {
        if (sib.classList.contains('nx-section')) return sib.textContent.trim();
        sib = sib.previousElementSibling;
      }
      node = node.parentElement;
    }
    return '';
  }

  function collectSearchItems() {
    var nav = $('.nx-nav');
    if (!nav) return [];
    return $all('.nx-nav-item[href]', nav).map(function (a) {
      var labelEl = a.querySelector('.nx-nav-label');
      var label = a.getAttribute('data-title') || (labelEl ? labelEl.textContent.trim() : a.textContent.trim());
      var href = a.getAttribute('href') || '';
      var file = href.split('?')[0].split('/').pop().toLowerCase();
      var groupEl = a.closest('.nx-nav-group');
      var groupToggle = groupEl ? groupEl.querySelector('.nx-nav-group-toggle .nx-nav-label') : null;
      var group = groupToggle ? groupToggle.textContent.trim() : '';
      var section = nearestSection(a, nav) || '';
      var path = group || section || 'Páginas';
      var keywords = a.getAttribute('data-search') || SEARCH_ALIASES[file] || '';
      return {
        label: label,
        path: path,
        haystack: normalizeSearch([label, section, group, keywords].join(' ')),
        href: href
      };
    });
  }

  function searchScore(item, query, tokens) {
    var label = normalizeSearch(item.label);
    if (label === query) return 0;
    if (label.indexOf(query) === 0) return 1;
    if (label.indexOf(query) !== -1) return 2;
    return tokens.length > 1 ? 4 : 3;
  }

  function initGlobalSearch() {
    var input = $('#globalSearch');
    if (!input || input.dataset.nxSearchBound === '1') return;
    input.dataset.nxSearchBound = '1';

    var wrapper = input.closest('.nx-search') || input.parentNode;
    var box = $('#globalSearchResults');
    if (!box) {
      box = document.createElement('div');
      box.className = 'nx-search-results';
      box.id = 'globalSearchResults';
      box.setAttribute('role', 'listbox');
      wrapper.appendChild(box);
    }

    var items = collectSearchItems();
    var matches = [];
    var activeIndex = -1;

    function hide() {
      box.hidden = true;
      box.innerHTML = '';
      wrapper.classList.remove('is-open');
      input.setAttribute('aria-expanded', 'false');
      matches = [];
      activeIndex = -1;
    }

    function setActive(index) {
      var nodes = $all('.nx-search-result', box);
      if (!nodes.length) return;
      activeIndex = (index + nodes.length) % nodes.length;
      nodes.forEach(function (node, i) { node.classList.toggle('active', i === activeIndex); });
    }

    function go(index) {
      var item = matches[index];
      if (item && item.href) window.location.href = item.href;
    }

    function render() {
      var query = normalizeSearch(input.value.trim());
      box.innerHTML = '';
      matches = [];
      activeIndex = -1;
      if (!query) { hide(); return; }

      var tokens = query.split(/\s+/).filter(Boolean);
      matches = items.filter(function (item) {
        return tokens.every(function (token) { return item.haystack.indexOf(token) !== -1; });
      }).map(function (item) {
        item.score = searchScore(item, query, tokens);
        return item;
      }).sort(function (a, b) { return a.score - b.score; }).slice(0, 8);

      if (!matches.length) {
        var empty = document.createElement('div');
        empty.className = 'nx-search-empty';
        empty.textContent = 'Sin resultados';
        box.appendChild(empty);
      } else {
        matches.forEach(function (item, index) {
          var result = document.createElement('button');
          result.type = 'button';
          result.className = 'nx-search-result';
          result.setAttribute('role', 'option');
          var label = document.createElement('span');
          label.className = 'nx-search-result-label';
          label.textContent = item.label;
          var path = document.createElement('span');
          path.className = 'nx-search-result-path';
          path.textContent = item.path;
          result.appendChild(label);
          result.appendChild(path);
          result.addEventListener('click', function () { go(index); });
          result.addEventListener('mouseenter', function () { setActive(index); });
          box.appendChild(result);
        });
        setActive(0);
      }

      box.hidden = false;
      wrapper.classList.add('is-open');
      input.setAttribute('aria-expanded', 'true');
    }

    input.addEventListener('input', render);
    input.addEventListener('focus', function () { if (input.value.trim()) render(); });
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        var bar = $('.nx-topbar');
        if (bar) bar.classList.remove('search-open');
        var mb = $('#mobileSearchBtn');
        if (mb) mb.setAttribute('aria-expanded', 'false');
        hide(); input.blur(); return;
      }
      if (e.key === 'ArrowDown') { e.preventDefault(); setActive(activeIndex + 1); return; }
      if (e.key === 'ArrowUp') { e.preventDefault(); setActive(activeIndex - 1); return; }
      if (e.key === 'Enter') {
        e.preventDefault();
        if (!box.hidden && matches.length) go(activeIndex >= 0 ? activeIndex : 0);
      }
    });
    document.addEventListener('click', function (e) {
      if (!wrapper.contains(e.target)) hide();
    });

    var mobileBtn = $('#mobileSearchBtn');
    if (mobileBtn) {
      mobileBtn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var bar = $('.nx-topbar');
        var open = bar ? bar.classList.toggle('search-open') : false;
        mobileBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) { input.focus(); input.select(); } else { hide(); }
      });
      document.addEventListener('click', function (e) {
        var bar = $('.nx-topbar');
        if (!bar || !bar.classList.contains('search-open')) return;
        if (wrapper.contains(e.target) || mobileBtn.contains(e.target)) return;
        bar.classList.remove('search-open');
        mobileBtn.setAttribute('aria-expanded', 'false');
        hide();
      });
    }
  }

  function init() {
    document.body.classList.add('nexo-body');
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
      var bar = $('.nx-topbar');
      if (bar) bar.classList.remove('search-open');
      var mb = $('#mobileSearchBtn');
      if (mb) mb.setAttribute('aria-expanded', 'false');
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
    initGlobalSearch();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
