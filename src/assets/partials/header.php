 <!-- App Topstrip -->

 <div class="app-topstrip bg-dark py-2 px-3 d-flex align-items-center justify-content-between">

   <div class="d-flex align-items-center gap-3">

     <!-- Botón de menú (móviles) -->

     <button class="d-lg-none btn btn-link text-white p-0 me-2 sidebartoggler" id="headerCollapse" aria-label="Alternar menú">

       <i class="ti ti-menu-2 fs-5"></i>

     </button>

     <!-- Botón colapsar sidebar (desktop) -->

     <button class="d-none d-xl-block theme-toggle-btn" id="sidebarCollapseBtn" title="Colapsar/expandir menú" aria-label="Colapsar menú">

       <i class="ti ti-layout-sidebar fs-5"></i>

     </button>



     <!-- Logo -->

     <a class="d-flex align-items-center" href="index.php">

       <img src="src/assets/images/logos/dashboard.png" alt="Logo" width="120">

     </a>

   </div>



   <!-- Controles (derecha) -->

   <div class="d-flex align-items-center gap-3">

     <!-- Modo oscuro -->

     <button class="theme-toggle-btn" id="themeToggle" title="Modo oscuro">

       <i class="ti ti-moon"></i>

     </button>



     <!-- Pantalla completa -->

     <button class="fullscreen-btn" id="fullscreenToggle" title="Pantalla completa">

       <i class="ti ti-maximize"></i>

     </button>



     <!-- Nombre del usuario -->

     <div class="nav-item">

       <a class="user-name mb-0 text-white text-decoration-none" href="editar-perfil.php" title="Editar perfil">

         <i class="ti ti-user me-1"></i> <?= htmlspecialchars($nombre_usuario) ?>

       </a>

     </div>

   </div>

 </div>