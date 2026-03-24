    // Pantalla completa
    const fullscreenToggle = document.getElementById('fullscreenToggle');
    fullscreenToggle.addEventListener('click', () => {
      if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(err => {
          console.error(`Error al entrar en pantalla completa: ${err.message}`);
        });
        fullscreenToggle.innerHTML = '<i class="ti ti-minimize"></i>';
      } else {
        if (document.exitFullscreen) document.exitFullscreen();
        fullscreenToggle.innerHTML = '<i class="ti ti-maximize"></i>';
      }
    });

    document.addEventListener('fullscreenchange', () => {
      if (!document.fullscreenElement) {
        fullscreenToggle.innerHTML = '<i class="ti ti-maximize"></i>';
      }
    });