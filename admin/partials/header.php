<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>CaliTex Courier Dashboard</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&amp;display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&amp;display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
  <script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            "primary": "#1152d4",
            "accent-orange": "#f97316",
            "background-light": "#f6f6f8",
            "background-dark": "#101622",
          },
          fontFamily: {
            "display": ["Inter"]
          },
          borderRadius: {
            "DEFAULT": "0.25rem",
            "lg": "0.5rem",
            "xl": "0.75rem",
            "full": "9999px"
          },
        },
      },
    }
  </script>
  <style type="text/tailwindcss">
    @layer base {
            body { font-family: 'Inter', sans-serif; }
            .material-symbols-outlined {
                font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            }
        }@media (min-width: 1024px) {
            .sidebar-collapsed { width: 80px; }
            .sidebar-collapsed .nav-text, 
            .sidebar-collapsed .profile-text,
            .sidebar-collapsed .logo-text,
            .sidebar-collapsed .support-label { display: none; }
            .sidebar-collapsed .nav-item { justify-content: center; padding-left: 0; padding-right: 0; }
            .sidebar-collapsed .logo-container { justify-content: center; padding-left: 0; padding-right: 0; }
        }@media (max-width: 1023px) {
            .sidebar-mobile-hidden { transform: translateX(-100%); }
            .sidebar-mobile-visible { transform: translateX(0); }
        }
    </style>
</head>

<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 min-h-screen">
  <div class="flex h-screen overflow-hidden relative">
    <aside class="sidebar-mobile-hidden lg:sidebar-mobile-visible fixed inset-y-0 left-0 z-50 w-64 lg:relative flex-shrink-0 border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 flex flex-col transition-all duration-300 ease-in-out" id="sidebar">
      <div class="p-6 logo-container flex items-center gap-3">
        <div class="bg-primary size-10 rounded-lg flex-shrink-0 flex items-center justify-center text-white">
          <span class="material-symbols-outlined">local_shipping</span>
        </div>
        <div class="flex flex-col logo-text">
          <h1 class="text-slate-900 dark:text-white text-base font-bold leading-tight">CaliTex Delivery</h1>
          <p class="text-slate-500 text-xs font-medium uppercase tracking-wider">Courier Portal</p>
        </div>
      </div>
      <nav class="flex-1 px-4 space-y-1 overflow-y-auto">
        <a class="nav-item flex items-center gap-3 px-3 py-2 rounded-lg bg-primary/10 text-primary" href="#">
          <span class="material-symbols-outlined flex-shrink-0">dashboard</span>
          <span class="text-sm font-semibold nav-text">Dashboard</span>
        </a>
        <a class="nav-item flex items-center gap-3 px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" href="pilotos.php">
          <span class="material-symbols-outlined flex-shrink-0">bike_lane</span>
          <span class="text-sm font-medium nav-text">Pilotos</span>
        </a>
        <a class="nav-item flex items-center gap-3 px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" href="auxiliares.php">
          <span class="material-symbols-outlined flex-shrink-0">contact_emergency</span>
          <span class="text-sm font-medium nav-text">Auxiliares de Bodega</span>
        </a>
        <a class="nav-item flex items-center gap-3 px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" href="clientes.php">
          <span class="material-symbols-outlined flex-shrink-0">person</span>
          <span class="text-sm font-medium nav-text">Clientes</span>
        </a>
        <a class="nav-item flex items-center gap-3 px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" href="paquetes.php">
          <span class="material-symbols-outlined flex-shrink-0">package_2</span>
          <span class="text-sm font-medium nav-text">Paquetes</span>
        </a>
        <a class="nav-item flex items-center gap-3 px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" href="#">
          <span class="material-symbols-outlined flex-shrink-0">contacts</span>
          <span class="text-sm font-medium nav-text">Libreta de direcciones</span>
        </a>
        <a class="nav-item flex items-center gap-3 px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" href="#">
          <span class="material-symbols-outlined flex-shrink-0">payments</span>
          <span class="text-sm font-medium nav-text">Pagos</span>
        </a>
        <div class="pt-4 pb-2 px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest support-label">Support</div>
        <a class="nav-item flex items-center gap-3 px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" href="#">
          <span class="material-symbols-outlined flex-shrink-0">help</span>
          <span class="text-sm font-medium nav-text">Centro de ayuda</span>
        </a>
        <a class="nav-item flex items-center gap-3 px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" href="../logout.php">
          <span class="material-symbols-outlined flex-shrink-0">power_settings_circle</span>
          <span class="text-sm font-medium nav-text">Cerrar sesión</span>
        </a>
      </nav>
      <div class="p-4 mt-auto border-t border-slate-200 dark:border-slate-800">
        <div class="flex items-center gap-3 px-2">
          <div class="size-8 rounded-full bg-slate-200 dark:bg-slate-700 bg-cover bg-center flex-shrink-0" style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuBhZ4Deg_bwOqQcP3_-9vyfFmKIWx-m6jdIH9RnyzlS1xHYRuwi63DN2MNUHkMDrtvGFM8JY9zcM8KnOKIFeElwLPa_kpmvZLC3McjobJTgEpDcd3c4-oOFKT2ruwd0ZzTNLbYx6CAnz9KemL14wTcyA8NYEwRPpb4OeNqumZej6B80g0Nh7zHWrjQDgwPUITQIaRmwYwglV8mA-S4-vUoYBbMdYmkuEJdV082n0-WoCbkWvsWIhencE1uhRd4yDSh8YZbmKhLsOmXF");'></div>
          <div class="flex-1 overflow-hidden profile-text">
            <p class="text-sm font-bold text-slate-900 dark:text-white truncate">Geoff</p>
            <p class="text-xs text-slate-500 truncate">HQ Terminal 4</p>
          </div>
        </div>
      </div>
      <button class="lg:hidden absolute top-4 -right-12 bg-white dark:bg-slate-900 p-2 rounded-r-lg border border-l-0 border-slate-200 dark:border-slate-800 shadow-md" onclick="document.getElementById('sidebar').classList.toggle('sidebar-mobile-visible')">
        <span class="material-symbols-outlined text-slate-600 dark:text-slate-400">close</span>
      </button>
    </aside>
    <main class="flex-1 flex flex-col overflow-y-auto bg-background-light dark:bg-background-dark w-full">
      <header class="h-auto lg:h-16 flex flex-col lg:flex-row items-center justify-between px-4 sm:px-8 py-3 lg:py-0 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 sticky top-0 z-40 gap-4">
        <div class="flex items-center w-full lg:w-auto gap-3">
          <button class="lg:hidden p-2 text-slate-600 dark:text-slate-400" onclick="document.getElementById('sidebar').classList.toggle('sidebar-mobile-visible')">
            <span class="material-symbols-outlined">menu</span>
          </button>
          <div class="flex-1 lg:max-w-xl">
            <label class="relative flex items-center w-full">
              <span class="absolute left-3 text-slate-400 material-symbols-outlined text-lg">search</span>
              <input class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-lg py-2 pl-10 pr-4 text-sm placeholder:text-slate-500 focus:ring-2 focus:ring-primary/20" placeholder="Numero de guia..." type="text" />
            </label>
          </div>
        </div>
        <div class="flex items-center justify-between w-full lg:w-auto gap-4">
          <div class="flex items-center gap-2">
            <button class="p-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700">
              <span class="material-symbols-outlined text-[20px]">Notoficaciones</span>
            </button>
            <button class="hidden lg:block p-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700" onclick="document.getElementById('sidebar').classList.toggle('sidebar-collapsed')">
              <span class="material-symbols-outlined text-[20px]">menu_open</span>
            </button>
          </div>
          <button class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-lg text-sm font-bold shadow-lg shadow-primary/20 hover:bg-primary/90 transition-all whitespace-nowrap">
            <span class="material-symbols-outlined text-[18px]">add</span>
            <span class="hidden sm:inline">Crear nuevo envío</span>
            <span class="sm:hidden">New</span>
          </button>
        </div>
      </header>