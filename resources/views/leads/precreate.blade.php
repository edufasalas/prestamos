<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicita tu Préstamo - Empresa WEB</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="min-h-screen bg-gray-50">

    <div class="h-1.5 w-full bg-red-600"></div>

    <main class="flex min-h-[calc(100vh-6px)] items-center justify-center px-4">
        <div class="w-full max-w-md" x-data="preCreateForm()">

            <!-- Logo -->
            <div class="mb-8 flex justify-center">
                <img src="{{ asset('public/images/logosa.svg') }}" alt="Logo de Empresa" class="h-12 w-auto object-contain">
            </div>

            <div class="rounded-lg bg-white p-8 shadow-lg">
                <h1 class="text-center text-2xl font-semibold text-gray-900">Te damos la bienvenida</h1>
                <h2 class="mt-1 text-center text-xl font-bold text-red-600">Solicita tu Préstamo</h2>

                <p class="mt-8 mb-3 text-sm font-medium text-gray-700">Ingresa tu información para continuar</p>

                <form action="{{ route('leads.store.step1') }}" method="POST" @submit.prevent="validarYEnviar($el)">
                    @csrf

                    <label for="referencia" class="mb-2 block text-sm font-medium text-gray-700">
                        Código de referencia / Teléfono / CURP
                    </label>

                    <input
                        id="referencia"
                        type="text"
                        name="referencia"
                        x-model="referencia"
                        autocomplete="off"
                        placeholder="Ingresa tu dato de acceso"
                        class="w-full rounded-md border border-gray-300 px-4 py-3 text-gray-900 outline-none transition focus:border-red-600 focus:ring-2 focus:ring-red-100"
                    >

                    <!-- El botón aparece únicamente cuando 'referencia' tiene texto -->
                    <div x-show="referencia.trim().length > 0" x-transition class="mt-5">
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
        function preCreateForm() {
            return {
                referencia: '',
                error: '',
                validarYEnviar(formElement) {
                    if (!this.referencia.trim()) return;
                    formElement.submit();
                }
            }
        }
    </script>
</body>
</html>