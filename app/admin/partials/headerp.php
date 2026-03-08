<!DOCTYPE html>

<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>CaliTex lista de pilotos</title>
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
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }

        /* Por defecto: móvil */
        .desktop-only { display: none !important; }
        .mobile-only { display: block !important; }

        /* >= 768px: escritorio */
        @media (min-width: 915px) {
            .desktop-only { display: block !important; }
            .mobile-only { display: none !important; }
        }
    </style>
</head>

<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100 min-h-screen">
    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-50 w-full border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-background-dark px-4 lg:px-10 py-3">
        <div class="max-w-[1280px] mx-auto flex items-center justify-between gap-4">
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-3">
                    <div class="size-8 text-primary">
                        <svg fill="none" viewbox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                            <path d="M36.7273 44C33.9891 44 31.6043 39.8386 30.3636 33.69C29.123 39.8386 26.7382 44 24 44C21.2618 44 18.877 39.8386 17.6364 33.69C16.3957 39.8386 14.0109 44 11.2727 44C7.25611 44 4 35.0457 4 24C4 12.9543 7.25611 4 11.2727 4C14.0109 4 16.3957 8.16144 17.6364 14.31C18.877 8.16144 21.2618 4 24 4C26.7382 4 29.123 8.16144 30.3636 14.31C31.6043 8.16144 33.9891 4 36.7273 4C40.7439 4 44 12.9543 44 24C44 35.0457 40.7439 44 36.7273 44Z" fill="currentColor"></path>
                        </svg>
                    </div>
                    <h1 class="hidden sm:block text-lg font-black tracking-tight">CaliTex <span class="text-primary">Delivery</span></h1>
                </div>
                <div class="hidden md:flex items-center gap-6">
                    <a class="text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-primary" href="dashboard.php">Dashboard</a>
                    <a class="text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-primary" href="paquetes_asignados.php">Tareas</a>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <label class="hidden lg:flex items-center bg-slate-100 dark:bg-slate-800 rounded-lg px-3 py-1.5 w-64 border border-transparent focus-within:border-primary">
                    <span class="material-symbols-outlined text-slate-400 text-xl">search</span>
                    <input class="bg-transparent border-none focus:ring-0 text-sm w-full placeholder:text-slate-500" placeholder="Search pilots..." type="text" />
                </label>
                <div class="flex items-center gap-3">
                    <button class="material-symbols-outlined p-2 text-slate-500 lg:hidden">search</button>
                    <button class="material-symbols-outlined p-2 text-slate-500">notifications</button>
                    <div class="size-9 rounded-full bg-cover bg-center border-2 border-primary" data-alt="User profile avatar circle" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBAmjUdiuwVaXwRfoq5HMyLM0f8hhyR1dxYf-12xqzOMPaWUXJQsz8VmgfaYnC7Af6BK2iAVPR6pA_m59Ehvpp1Orq5obFx1KGQjmgOX4ULaHoEBGugT_XJmosELy4sghqV4epnw1ZgG9XDXadPO8PV-ao8E6KXJy6j_DRGmdW7YGj6xr0Xl-3hdKSzznCCNxsxTlBuTJBHPN1-dDWSdctId25pWyxHGzweA_RTAw1baLI513hwmF9bvReJ--kzcO2rYijuqYeYxZ5Y');"></div>
                </div>
            </div>
        </div>
    </header>
    <main class="max-w-[1280px] mx-auto p-4 lg:p-8 @container">