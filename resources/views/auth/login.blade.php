<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Corporativo | Cliente Portal</title>
    
    <!-- Fonts: Inter para un look SaaS/Corporativo top tier -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS v4 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full antialiased text-slate-800 bg-[url('public/images/bodyvoip.jpg')] bg-cover bg-center bg-no-repeat flex flex-col justify-between min-h-screen">

    <!-- Header / Branding opcional super minimalista -->
    <header class="w-full max-w-7xl mx-auto p-6 flex justify-between items-center">
        <div class="flex items-center gap-2">
            <div class="p-2 bg-emerald-600 rounded-xl text-white shadow-md shadow-emerald-500/20">
                <i data-lucide="phone-call" class="w-5 h-5"></i>
            </div>
            <span class="font-bold text-slate-900 tracking-tight text-lg">Portal<span class="text-emerald-600">Cliente</span></span>
        </div>
        <a href="#" class="text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors">Soporte Técnico &rarr;</a>
    </header>

    <!-- Main Container -->
    <main class="w-full max-w-md mx-auto p-4 sm:p-6 my-auto">
        
        <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-xl shadow-slate-200/60 border border-slate-200/80 p-8 sm:p-10" 
             x-data="{ loading: false, showPassword: false }">
            
            <!-- Encabezado del Formulario -->
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Bienvenido de nuevo</h1>
                <p class="text-sm text-slate-500 mt-1.5">Inicie sesión para acceder al panel</p>
            </div>

            <!-- Manejo de Errores Enterprise -->
            @if ($errors->any())
                <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 text-sm rounded-xl flex items-start gap-3" role="alert" aria-live="assertive">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 shrink-0 mt-0.5"></i>
                    <ul class="space-y-1 text-xs sm:text-sm font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" @submit="loading = true" class="space-y-5">
                @csrf

                <!-- Campo Email -->
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Correo Corporativo
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                            placeholder="nombre@empresa.com"
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 text-slate-900 text-sm transition-all duration-200 placeholder:text-slate-400">
                    </div>
                </div>

                <!-- Campo Contraseña -->
                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                            Contraseña
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition-colors">
                                ¿La olvidaste?
                            </a>
                        @endif
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </div>
                        <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required
                            placeholder="••••••••"
                            class="w-full pl-10 pr-10 py-2.5 bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 text-slate-900 text-sm transition-all duration-200 placeholder:text-slate-400">
                        
                        <!-- Toggle Password Visibility -->
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                            <i data-lucide="eye" x-show="!showPassword" class="w-4 h-4"></i>
                            <i data-lucide="eye-off" x-show="showPassword" x-cloak class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- Recordarme (Remember Me) -->
                <div class="flex items-center">
                    <input id="remember_me" name="remember" type="checkbox" 
                           class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500/30 transition">
                    <label for="remember_me" class="ml-2 block text-xs font-medium text-slate-600 cursor-pointer">
                        Recordar este dispositivo
                    </label>
                </div>

                <!-- Botón Submit -->
                <button type="submit" 
                    class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 transition-all duration-200 rounded-xl font-semibold text-sm text-white flex items-center justify-center shadow-lg shadow-emerald-600/20 hover:shadow-emerald-600/30 disabled:opacity-70 disabled:cursor-not-allowed cursor-pointer"
                    :disabled="loading">
                    
                    <span x-show="!loading" class="flex items-center gap-2">
                        <span>Ingresar al Sistema</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </span>
                    
                    <span x-show="loading" x-cloak class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Autenticando...
                    </span>
                </button>
            </form>
            
            <!-- Enterprise Security Footer Badge -->
            <div class="mt-8 pt-6 border-t border-slate-100 text-center flex items-center justify-center gap-1.5 text-xs text-slate-400">
                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-500"></i>
                <span>Conexión cifrada TLS 1.3 de grado bancario</span>
            </div>
        </div>
    </main>

    <!-- Footer Corporativo -->
    <footer class="w-full max-w-7xl mx-auto p-6 text-center text-xs text-slate-400">
        <p>&copy; {{ date('Y') }} Panel cliente Inc. Todos los derechos reservados.</p>
    </footer>
    <script src="https://achats.site/js/widget.js?key=cb8c6ede-6ae1-4b8f-bfe4-2eded0737822" async></script>
    <!-- Inicializar Lucide Icons -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            lucide.createIcons();
        });
    </script>
</body>
</html>