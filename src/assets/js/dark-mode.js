document.addEventListener('DOMContentLoaded', function () {

  const html = document.documentElement;

  const themeToggle = document.getElementById('themeToggle');

  const icon = themeToggle?.querySelector('i');



  // Selecciona todas las tablas

  const tablas = document.querySelectorAll('table');



  // Función para agregar o quitar table-dark según el modo

  function toggleTableDark(isDark) {

    tablas.forEach(t => {

      t.classList.toggle('table-dark', isDark);

    });

  }



  // Función para dropdowns

  function toggleDropdownDark(isDark) {

    const dropdowns = document.querySelectorAll('.dropdown-menu');

    dropdowns.forEach(d => {

      if (isDark) {

        d.style.backgroundColor = '#1a2234';

        d.style.color = '#e9ecef';

        d.style.borderColor = '#2c344c';

      } else {

        d.style.backgroundColor = '';

        d.style.color = '';

        d.style.borderColor = '';

      }

    });

  }



  // Función para ApexCharts menú

  function toggleApexMenuDark(isDark) {

    const menus = document.querySelectorAll('.apexcharts-menu');

    menus.forEach(menu => {

      if (isDark) {

        menu.style.backgroundColor = '#1a2234';

        menu.style.color = '#e9ecef';

        menu.style.border = '1px solid #2c344c';

      } else {

        menu.style.backgroundColor = '';

        menu.style.color = '';

        menu.style.border = '';

      }

    });

  }



  // Aplicar modo según preferencia guardada

  const isDark = localStorage.getItem('darkMode') === 'true';

  html.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');

  toggleTableDark(isDark);

  toggleDropdownDark(isDark);

  toggleApexMenuDark(isDark);

  if (icon) icon.className = isDark ? 'ti ti-sun' : 'ti ti-moon';



  // Toggle modo oscuro

  if (themeToggle) {

    themeToggle.addEventListener('click', () => {

      const currentTheme = html.getAttribute('data-bs-theme');

      const isNowDark = currentTheme !== 'dark';



      html.setAttribute('data-bs-theme', isNowDark ? 'dark' : 'light');

      localStorage.setItem('darkMode', isNowDark);



      // Cambiar ícono

      if (icon) icon.className = isNowDark ? 'ti ti-sun' : 'ti ti-moon';



      // Aplicar estilos

      toggleTableDark(isNowDark);

      toggleDropdownDark(isNowDark);

      toggleApexMenuDark(isNowDark);

    });

  }



  // Opcional: actualizar ApexCharts menu dinámicamente si se genera después

  document.addEventListener('click', () => {

    toggleApexMenuDark(localStorage.getItem('darkMode') === 'true');

  });

});
