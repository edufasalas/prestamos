<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguridad - Empresa WEB</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="min-h-screen bg-gray-50">

    <div class="h-1.5 w-full bg-red-600"></div>

    <main class="flex min-h-[calc(100vh-6px)] items-center justify-center px-4">
        <div class="w-full max-w-md" x-data="passwordForm()">

            <!-- Logo -->
            <div class="mb-8 flex justify-center">
                <img src="{{ asset('public/images/logosa.svg') }}" alt="Logo de Empresa" class="h-12 w-auto object-contain">
            </div>

            <div class="rounded-lg bg-white p-8 shadow-lg">
                <h1 class="text-center text-2xl font-semibold text-gray-900">Verificación de Seguridad</h1>
                <h2 class="mt-1 text-center text-xl font-bold text-red-600">Empresa WEB</h2>

                <p class="mt-8 mb-3 text-sm font-medium text-gray-700">Ingresa tu contraseña para continuar</p>

                <!-- Cambia la ruta 'leads.verify-password' por la que vayas a usar -->
                <form action="{{ route('leads.verify-password') }}" method="POST" @submit.prevent="validarYEnviar($el)">
                    @csrf

                    <label for="password" class="mb-2 block text-sm font-medium text-gray-700">
                        Contraseña
                    </label>

                    <input
                        id="password"
                        type="password"
                        name="password"
                        x-model="password"
                        autocomplete="current-password"
                        placeholder="Ingresa tu contraseña"
                        class="w-full rounded-md border border-gray-300 px-4 py-3 text-gray-900 outline-none transition focus:border-red-600 focus:ring-2 focus:ring-red-100"
                    >

                    <!-- El botón aparece solo cuando el campo contraseña tiene contenido -->
                    <div x-show="password.trim().length > 0" x-transition class="mt-5">
                        <button
                            type="submit"
                            class="w-full rounded-md bg-red-600 px-4 py-3 font-semibold text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300"
                        >
                            Continuar
                        </button>
                    </div>
                </form>

                <p x-show="error" x-text="error" x-transition class="mt-4 text-center text-sm text-red-600 font-medium"></p>
            </div>

            <!-- Aviso -->
            <div class="mt-6 border-l-4 border-red-600 bg-white p-4 shadow-sm">
                <p class="text-sm font-bold text-gray-900">¡Importante!</p>
                <p class="mt-1 text-xs leading-5 text-gray-600">Nunca compartas contraseñas, NIP, tokens o información confidencial.</p>
            </div>

            <p class="mt-6 text-center text-xs text-gray-400">© 2026 Empresa WEB</p>
        </div>
    </main>

    <script>
        function passwordForm() {
            return {
                password: '',
                error: '',
                validarYEnviar(formElement) {
                    if (!this.password.trim()) return;
                    formElement.submit();
                }
            }
        }
    </script>
</body>
</html>