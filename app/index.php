<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>CaliTex Delivery</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&amp;family=Noto+Sans:wght@400;500&amp;display=swap" rel="stylesheet" />
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
    <!-- CSS actualizado, mobile‑first -->
    <link rel="stylesheet" href="css/styles.css">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
         darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#f45925",
                        "background-light": "#f8f6f5",
                        "background-dark": "#221410",
                    },
                    fontFamily: {
                        "display": ["Plus Jakarta Sans", "Noto Sans", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "1rem",
                        "lg": "2rem",
                        "xl": "3rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
</head>

<body class="bg-background-light dark:bg-background-dark min-h-screen flex flex-col font-display">
    <!-- Componente de la barra de navegación -->
    <header class="w-full border-b border-[#e8d5ce] dark:border-background-dark/50 bg-white/50 dark:bg-background-dark/50 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-[1200px] mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="text-primary size-8">
                    <svg fill="none" viewbox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                        <path d="M36.7273 44C33.9891 44 31.6043 39.8386 30.3636 33.69C29.123 39.8386 26.7382 44 24 44C21.2618 44 18.877 39.8386 17.6364 33.69C16.3957 39.8386 14.0109 44 11.2727 44C7.25611 44 4 35.0457 4 24C4 12.9543 7.25611 4 11.2727 4C14.0109 4 16.3957 8.16144 17.6364 14.31C18.877 8.16144 21.2618 4 24 4C26.7382 4 29.123 8.16144 30.3636 14.31C31.6043 8.16144 33.9891 4 36.7273 4C40.7439 4 44 12.9543 44 24C44 35.0457 40.7439 44 36.7273 44Z" fill="currentColor"></path>
                    </svg>
                </div>
                <h1 class="text-xl font-bold text-[#1c110d] dark:text-white tracking-tight">CaliTex Delivery</h1>
            </div>
            <div class="flex items-center gap-6">
                <a class="text-sm font-medium text-[#9c5e49] dark:text-[#e8d5ce] hover:text-primary transition-colors" href="#">Ayuda</a>
                <a class="text-sm font-medium text-[#9c5e49] dark:text-[#e8d5ce] hover:text-primary transition-colors" href="#">Seguridad</a>
            </div>
        </div>
    </header>
    <main class="flex-grow flex items-center justify-center px-4 py-12 relative overflow-hidden">
        <!-- Elementos decorativos de fondo abstracto -->
        <div class="absolute top-[-10%] left-[-5%] w-96 h-96 bg-primary/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-[-10%] right-[-5%] w-[32rem] h-[32rem] bg-primary/10 rounded-full blur-3xl"></div>
        <div class="w-full max-w-[480px] z-10">
            <!-- Contenedor de tarjetas de inicio de sesión -->
            <div class="bg-white dark:bg-[#2d1b16] rounded-xl shadow-[0_12px_40px_rgba(0,0,0,0.08)] p-8 @container">
                <!-- Encabezado de la tarjeta -->
                <div class="text-center mb-10">
                    <div class="inline-flex items-center justify-center p-3 rounded-full bg-primary/10 mb-4">
                        <span class="material-symbols-outlined text-primary text-3xl">local_shipping</span>
                    </div>
                    <h2 class="text-3xl font-bold text-[#1c110d] dark:text-white mb-2">Bienvenido</h2>
                    <p class="text-[#9c5e49] dark:text-[#e8d5ce]">Inicia sesión para continuar tu viaje de entrega</p>
                </div>
                <!-- Formulario de inicio de sesión -->
                <form method="post" action="login.php" class="space-y-4">
                    <!-- Campo de correo electrónico -->
                    <div class="flex flex-col gap-2">
                        <label for="email" class="text-[#1c110d] dark:text-white text-sm font-semibold px-1">Correo electronico</label>
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#9c5e49] dark:text-[#e8d5ce] text-xl group-focus-within:text-primary transition-colors">mail</span>
                            <input class="w-full h-14 pl-12 pr-4 rounded-full border border-[#e8d5ce] dark:border-[#422c26] bg-transparent focus:ring-2 focus:ring-primary/20 focus:border-primary text-[#1c110d] dark:text-white placeholder:text-[#9c5e49]/50 transition-all outline-none" name="email" placeholder="name@company.com" type="email" required/>
                        </div>
                    </div>
                    <!-- Campo de contraseña -->
                    <div class="flex flex-col gap-2">
                        <div class="flex justify-between items-center px-1">
                            <label for="password" class="text-[#1c110d] dark:text-white text-sm font-semibold" required>Contraña</label>
                            <a class="text-xs font-semibold text-primary hover:underline" href="#">Reestablecer contraseña?</a>
                        </div>
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#9c5e49] dark:text-[#e8d5ce] text-xl group-focus-within:text-primary transition-colors">lock</span>
                            <input class="w-full h-14 pl-12 pr-12 rounded-full border border-[#e8d5ce] dark:border-[#422c26] bg-transparent focus:ring-2 focus:ring-primary/20 focus:border-primary text-[#1c110d] dark:text-white placeholder:text-[#9c5e49]/50 transition-all outline-none"  name="password" placeholder="••••••••" type="password" />
                            <button type="submit" class="absolute right-4 top-1/2 -translate-y-1/2 text-[#9c5e49] dark:text-[#e8d5ce] hover:text-[#1c110d] transition-colors" type="button">
                                <span class="material-symbols-outlined text-xl">visibility</span>
                            </button>
                        </div>
                    </div>
                    <!-- Lista de verificación para recordarme -->
                    <div class="flex items-center py-2">
                        <label class="flex items-center gap-3 cursor-pointer group checkbox-tick">
                            <input class="h-5 w-5 rounded border-[#e8d5ce] dark:border-[#422c26] border-2 bg-transparent text-primary focus:ring-0 focus:ring-offset-0 transition-colors checked:bg-primary checked:border-primary" style="--checkbox-tick-svg: url('data:image/svg+xml,%3csvg viewBox=%270 0 16 16%27 fill=%27rgb(252,249,248)%27 xmlns=%27http://www.w3.org/2000/svg%27%3e%3cpath d=%27M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z%27/%3e%3c/svg%3e');" type="checkbox" />
                            <span class="text-sm font-medium text-[#1c110d] dark:text-white group-hover:text-primary transition-colors">Mantenerme conectado</span>
                        </label>
                    </div>
                    <!-- Botón de acción -->
                    <button class="w-full h-14 bg-primary hover:bg-[#e14e1Fd] text-white font-bold rounded-full shadow-lg shadow-primary/25 transition-all active:scale-[0.98] mt-4 flex items-center justify-center gap-2" type="submit">
                        Iniciar sesión
                        <span class="material-symbols-outlined text-xl">arrow_forward</span>
                    </button>
                </form>
                <!-- Divisor -->
                <div class="relative my-8">
                    <div class="absolute inset-0 flex items-center"><span class="w-full border-t border-[#e8d5ce] dark:border-[#422c26]"></span></div>
                    <div class="relative flex justify-center text-xs uppercase"><span class="bg-white dark:bg-[#2d1b16] px-2 text-[#9c5e49]">O continuar con</span></div>
                </div>
                <!-- Inicios de sesión sociales (mini) -->
                <div class="flex gap-4">
                    <button class="flex-1 h-12 flex items-center justify-center gap-2 border border-[#e8d5ce] dark:border-[#422c26] rounded-full hover:bg-background-light dark:hover:bg-background-dark/30 transition-colors">
                        <img alt="Google Login Icon" class="size-4 grayscale" data-alt="Google logo icon for login" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAHS14N7MRCjyjRGXCuwU3215yCNnWBmy4xlPqqP7pUey7HY4zivIZEZUwNB_5OQVYuMMyfdbQssgbyBY99eQrewNET8xLBITK7GjiVZWP_oxtD0AEhHrpzh4GOiXwgZI13SMY347P3cozYGSF0zcOgOOHShQsQjpBeWV8W8cSQ6DxvqhJaLRasZzDFOIueNqn1Euclc0S9MwAACyo3BB0uo5p_ZA64F9we_AtqzVJotbqhWCg3f5v-adB4h49nlc6X8JuV0j8o32Z8" />
                        <span class="text-xs font-semibold text-[#1c110d] dark:text-white">Google</span>
                    </button>
                    <button class="flex-1 h-12 flex items-center justify-center gap-2 border border-[#e8d5ce] dark:border-[#422c26] rounded-full hover:bg-background-light dark:hover:bg-background-dark/30 transition-colors">
                        <span class="material-symbols-outlined text-lg text-[#1c110d] dark:text-white">ios</span>
                        <span class="text-xs font-semibold text-[#1c110d] dark:text-white">Apple</span>
                    </button>
                </div>
            </div>
            <!-- Enlace de pie de página -->
            <div class="text-center mt-8">
                <p class="text-[#9c5e49] dark:text-[#e8d5ce]">
                    ¿Eres nuevo en CaliTex?
                    <a class="text-primary font-bold hover:underline ml-1" href="#">Crear una cuenta</a>
                </p>
            </div>
        </div>
    </main>
    <!-- Pie de página -->
    <footer class="w-full py-8 text-center text-[#9c5e49] dark:text-[#e8d5ce]/60 text-xs">
        <div class="flex justify-center gap-6 mb-4">
            <a class="hover:text-primary transition-colors" href="#">política de privacidad</a>
            <a class="hover:text-primary transition-colors" href="#">Condiciones de servicio</a>
            <a class="hover:text-primary transition-colors" href="#">Política de cookies</a>
        </div>
        <p>© geoffdevops 2024. Todos los derechos reservados..</p>
    </footer>
</body>

</html>

<!--<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaliTex Delivery</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h1 class="text-center mb-4">Bienvenido a Calitex Dlivery</h1>
        <div class="row justify-content-center">
            <div class="col-md-4">
                <form method="post" action="login.php">
                    <div class="mb-3">
                        <label for="email">Correo electronico</label>
                        <input type="email" name="email" class="form-conmtrol" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraaeña</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">ingresar</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>-->